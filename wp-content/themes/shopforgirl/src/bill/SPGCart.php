<?php

if (!function_exists('is_order_product_form')) {
    function is_order_product_form()
    {
        return 1;
    }
}

if (class_exists('WC_Cart')) {

    class SPGCart extends WC_Cart
    {
        private function reset($unset_session = false)
        {
            foreach ($this->cart_session_data as $key => $default) {
                $this->$key = $default;
                if ($unset_session) {
                    unset(WC()->session->$key);
                }
            }
            do_action('woocommerce_cart_reset', $this, $unset_session);
        }
        

        /**
         * Set cart hash cookie and items in cart.
         *
         * @access private
         * @param bool $set (default: true)
         */
        private function set_cart_cookies($set = true)
        {
            if ($set) {
                wc_setcookie('woocommerce_items_in_cart', 1);
                wc_setcookie('woocommerce_cart_hash', md5(json_encode($this->get_cart_for_session())));
            } elseif (isset($_COOKIE['woocommerce_items_in_cart'])) {
                wc_setcookie('woocommerce_items_in_cart', 0, time() - HOUR_IN_SECONDS);
                wc_setcookie('woocommerce_cart_hash', '', time() - HOUR_IN_SECONDS);
            }
            do_action('woocommerce_set_cart_cookies', $set);
        }


        public function add_to_cart($product_id = 0, $quantity = 1, $variation_id = 0, $variation = array(), $cart_item_data = array())
        {
            // Wrap in try catch so plugins can throw an exception to prevent adding to cart
            try {
                $product_id = absint($product_id);
                $variation_id = absint($variation_id);

                // Ensure we don't add a variation to the cart directly by variation ID
                if ('product_variation' == get_post_type($product_id)) {
                    $variation_id = $product_id;
                    $product_id = wp_get_post_parent_id($variation_id);
                }

                // Get the product
                $product_data = wc_get_product($variation_id ? $variation_id : $product_id);

                // Sanity check
                if ($quantity <= 0 || !$product_data || 'trash' === $product_data->post->post_status) {
                    throw new Exception();
                }

                // Load cart item data - may be added by other plugins
                $cart_item_data = (array)apply_filters('woocommerce_add_cart_item_data', $cart_item_data, $product_id, $variation_id);

                // Generate a ID based on product ID, variation ID, variation data, and other cart item data
                $cart_id = $this->generate_cart_id($product_id, $variation_id, $variation, $cart_item_data);

                // Find the cart item key in the existing cart
                $cart_item_key = $this->find_product_in_cart($cart_id);

                // Force quantity to 1 if sold individually and check for existing item in cart
                if ($product_data->is_sold_individually()) {
                    $quantity = apply_filters('woocommerce_add_to_cart_sold_individually_quantity', 1, $quantity, $product_id, $variation_id, $cart_item_data);
                    $in_cart_quantity = $cart_item_key ? $this->cart_contents[$cart_item_key]['quantity'] : 0;

                    if ($in_cart_quantity > 0) {
                        throw new Exception(sprintf('<a href="%s" class="button wc-forward">%s</a> %s', wc_get_cart_url(), __('View Cart', 'woocommerce'), sprintf(__('You cannot add another &quot;%s&quot; to your cart.', 'woocommerce'), $product_data->get_title())));
                    }
                }

                // Check product is_purchasable
                if (!$product_data->is_purchasable()) {
                    throw new Exception(__('Sorry, this product cannot be purchased.', 'woocommerce'));
                }

                // Stock check - only check if we're managing stock and backorders are not allowed
                if (!$product_data->is_in_stock()) {
                    throw new Exception(sprintf(__('You cannot add &quot;%s&quot; to the cart because the product is out of stock.', 'woocommerce'), $product_data->get_title()));
                }

                if (!$product_data->has_enough_stock($quantity)) {
                    throw new Exception(sprintf(__('You cannot add that amount of &quot;%s&quot; to the cart because there is not enough stock (%s remaining).', 'woocommerce'), $product_data->get_title(), $product_data->get_stock_quantity()));
                }

                // Stock check - this time accounting for whats already in-cart
                if ($managing_stock = $product_data->managing_stock()) {
                    $products_qty_in_cart = $this->get_cart_item_quantities();

                    if ($product_data->is_type('variation') && true === $managing_stock) {
                        $check_qty = isset($products_qty_in_cart[$variation_id]) ? $products_qty_in_cart[$variation_id] : 0;
                    } else {
                        $check_qty = isset($products_qty_in_cart[$product_id]) ? $products_qty_in_cart[$product_id] : 0;
                    }

                    /**
                     * Check stock based on all items in the cart.
                     */
                    if (!$product_data->has_enough_stock($check_qty + $quantity)) {
                        throw new Exception(sprintf(
                            '<a href="%s" class="button wc-forward">%s</a> %s',
                            wc_get_cart_url(),
                            __('View Cart', 'woocommerce'),
                            sprintf(__('You cannot add that amount to the cart &mdash; we have %s in stock and you already have %s in your cart.', 'woocommerce'), $product_data->get_stock_quantity(), $check_qty)
                        ));
                    }
                }

                // If cart_item_key is set, the item is already in the cart
                if ($cart_item_key) {
                    $new_quantity = $quantity + $this->cart_contents[$cart_item_key]['quantity'];
                    $this->set_quantity($cart_item_key, $new_quantity, false);
                } else {
                    $cart_item_key = $cart_id;

                    // Add item after merging with $cart_item_data - hook to allow plugins to modify cart item
                    $this->cart_contents[$cart_item_key] = apply_filters('woocommerce_add_cart_item', array_merge($cart_item_data, array(
                        'product_id' => $product_id,
                        'variation_id' => $variation_id,
                        'variation' => $variation,
                        'quantity' => $quantity,
                        'data' => $product_data
                    )), $cart_item_key);
                }

                if (did_action('wp')) {
                    $this->set_cart_cookies(!$this->is_empty());
                }

                do_action('woocommerce_add_to_cart', $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data);
                return $cart_item_key;

            } catch (Exception $e) {

                if ($e->getMessage()) {
                    wc_add_notice($e->getMessage(), 'error');
                }
                return false;
            }
        }

        public function calculate_totals()
        {
            $this->reset();
            $this->coupons = $this->get_coupons();

            do_action('woocommerce_before_calculate_totals', $this);

            if ($this->is_empty()) {
                $this->set_session();
                return;
            }

            $tax_rates = array();
            $shop_tax_rates = array();
            $cart = $this->get_cart();

            /**
             * Calculate subtotals for items. This is done first so that discount logic can use the values.
             */
            foreach ($cart as $cart_item_key => $values) {
                $_product = $values['data'];
                $line_price = $_product->get_price() * $values['quantity'];
                $line_subtotal = 0;
                $line_subtotal_tax = 0;

                /**
                 * No tax to calculate.
                 */
                if (!$_product->is_taxable()) {

                    // Subtotal is the undiscounted price
                    $this->subtotal += $line_price;
                    $this->subtotal_ex_tax += $line_price;

                    /**
                     * Prices include tax.
                     *
                     * To prevent rounding issues we need to work with the inclusive price where possible.
                     * otherwise we'll see errors such as when working with a 9.99 inc price, 20% VAT which would.
                     * be 8.325 leading to totals being 1p off.
                     *
                     * Pre tax coupons come off the price the customer thinks they are paying - tax is calculated.
                     * afterwards.
                     *
                     * e.g. $100 bike with $10 coupon = customer pays $90 and tax worked backwards from that.
                     */
                } elseif ($this->prices_include_tax) {

                    // Get base tax rates
                    if (empty($shop_tax_rates[$_product->tax_class])) {
                        $shop_tax_rates[$_product->tax_class] = WC_Tax::get_base_tax_rates($_product->tax_class);
                    }

                    // Get item tax rates
                    if (empty($tax_rates[$_product->get_tax_class()])) {
                        $tax_rates[$_product->get_tax_class()] = WC_Tax::get_rates($_product->get_tax_class());
                    }

                    $base_tax_rates = $shop_tax_rates[$_product->tax_class];
                    $item_tax_rates = $tax_rates[$_product->get_tax_class()];

                    /**
                     * ADJUST TAX - Calculations when base tax is not equal to the item tax.
                     *
                     * The woocommerce_adjust_non_base_location_prices filter can stop base taxes being taken off when dealing with out of base locations.
                     * e.g. If a product costs 10 including tax, all users will pay 10 regardless of location and taxes.
                     * This feature is experimental @since 2.4.7 and may change in the future. Use at your risk.
                     */
                    if ($item_tax_rates !== $base_tax_rates && apply_filters('woocommerce_adjust_non_base_location_prices', true)) {

                        // Work out a new base price without the shop's base tax
                        $taxes = WC_Tax::calc_tax($line_price, $base_tax_rates, true, true);

                        // Now we have a new item price (excluding TAX)
                        $line_subtotal = $line_price - array_sum($taxes);

                        // Now add modified taxes
                        $tax_result = WC_Tax::calc_tax($line_subtotal, $item_tax_rates);
                        $line_subtotal_tax = array_sum($tax_result);

                        /**
                         * Regular tax calculation (customer inside base and the tax class is unmodified.
                         */
                    } else {

                        // Calc tax normally
                        $taxes = WC_Tax::calc_tax($line_price, $item_tax_rates, true);
                        $line_subtotal_tax = array_sum($taxes);
                        $line_subtotal = $line_price - array_sum($taxes);
                    }

                    /**
                     * Prices exclude tax.
                     *
                     * This calculation is simpler - work with the base, untaxed price.
                     */
                } else {

                    // Get item tax rates
                    if (empty($tax_rates[$_product->get_tax_class()])) {
                        $tax_rates[$_product->get_tax_class()] = WC_Tax::get_rates($_product->get_tax_class());
                    }

                    $item_tax_rates = $tax_rates[$_product->get_tax_class()];

                    // Base tax for line before discount - we will store this in the order data
                    $taxes = WC_Tax::calc_tax($line_price, $item_tax_rates);
                    $line_subtotal_tax = array_sum($taxes);

                    $line_subtotal = $line_price;
                }

                // Add to main subtotal
                $this->subtotal += $line_subtotal + $line_subtotal_tax;
                $this->subtotal_ex_tax += $line_subtotal;
            }

            // Order cart items by price so coupon logic is 'fair' for customers and not based on order added to cart.
            uasort($cart, array($this, 'sort_by_subtotal'));

            /**
             * Calculate totals for items.
             */
            foreach ($cart as $cart_item_key => $values) {

                $_product = $values['data'];

                // Prices
                $base_price = $_product->get_price();
                $line_price = $_product->get_price() * $values['quantity'];

                // Tax data
                $taxes = array();
                $discounted_taxes = array();

                /**
                 * No tax to calculate.
                 */
                if (!$_product->is_taxable()) {

                    // Discounted Price (price with any pre-tax discounts applied)
                    $discounted_price = $this->get_discounted_price($values, $base_price, true);
                    $line_subtotal_tax = 0;
                    $line_subtotal = $line_price;
                    $line_tax = 0;
                    $line_total = round($discounted_price * $values['quantity'], WC_ROUNDING_PRECISION);

                    /**
                     * Prices include tax.
                     */
                } elseif ($this->prices_include_tax) {

                    $base_tax_rates = $shop_tax_rates[$_product->tax_class];
                    $item_tax_rates = $tax_rates[$_product->get_tax_class()];

                    /**
                     * ADJUST TAX - Calculations when base tax is not equal to the item tax.
                     *
                     * The woocommerce_adjust_non_base_location_prices filter can stop base taxes being taken off when dealing with out of base locations.
                     * e.g. If a product costs 10 including tax, all users will pay 10 regardless of location and taxes.
                     * This feature is experimental @since 2.4.7 and may change in the future. Use at your risk.
                     */
                    if ($item_tax_rates !== $base_tax_rates && apply_filters('woocommerce_adjust_non_base_location_prices', true)) {

                        // Work out a new base price without the shop's base tax
                        $taxes = WC_Tax::calc_tax($line_price, $base_tax_rates, true, true);

                        // Now we have a new item price (excluding TAX)
                        $line_subtotal = round($line_price - array_sum($taxes), WC_ROUNDING_PRECISION);
                        $taxes = WC_Tax::calc_tax($line_subtotal, $item_tax_rates);
                        $line_subtotal_tax = array_sum($taxes);

                        // Adjusted price (this is the price including the new tax rate)
                        $adjusted_price = ($line_subtotal + $line_subtotal_tax) / $values['quantity'];

                        // Apply discounts and get the discounted price FOR A SINGLE ITEM
                        $discounted_price = $this->get_discounted_price($values, $adjusted_price, true);

                        // Convert back to line price and round nicely
                        $discounted_line_price = round($discounted_price * $values['quantity'], $this->dp);

                        // Now use rounded line price to get taxes.
                        $discounted_taxes = WC_Tax::calc_tax($discounted_line_price, $item_tax_rates, true);
                        $line_tax = array_sum($discounted_taxes);
                        $line_total = $discounted_line_price - $line_tax;

                        /**
                         * Regular tax calculation (customer inside base and the tax class is unmodified.
                         */
                    } else {

                        // Work out a new base price without the item tax
                        $taxes = WC_Tax::calc_tax($line_price, $item_tax_rates, true);

                        // Now we have a new item price (excluding TAX)
                        $line_subtotal = $line_price - array_sum($taxes);
                        $line_subtotal_tax = array_sum($taxes);

                        // Calc prices and tax (discounted)
                        $discounted_price = $this->get_discounted_price($values, $base_price, true);

                        // Convert back to line price and round nicely
                        $discounted_line_price = round($discounted_price * $values['quantity'], $this->dp);

                        // Now use rounded line price to get taxes.
                        $discounted_taxes = WC_Tax::calc_tax($discounted_line_price, $item_tax_rates, true);
                        $line_tax = array_sum($discounted_taxes);
                        $line_total = $discounted_line_price - $line_tax;
                    }

                    // Tax rows - merge the totals we just got
                    foreach (array_keys($this->taxes + $discounted_taxes) as $key) {
                        $this->taxes[$key] = (isset($discounted_taxes[$key]) ? $discounted_taxes[$key] : 0) + (isset($this->taxes[$key]) ? $this->taxes[$key] : 0);
                    }

                    /**
                     * Prices exclude tax.
                     */
                } else {

                    $item_tax_rates = $tax_rates[$_product->get_tax_class()];

                    // Work out a new base price without the shop's base tax
                    $taxes = WC_Tax::calc_tax($line_price, $item_tax_rates);

                    // Now we have the item price (excluding TAX)
                    $line_subtotal = $line_price;
                    $line_subtotal_tax = array_sum($taxes);

                    // Now calc product rates
                    $discounted_price = $this->get_discounted_price($values, $base_price, true);
                    $discounted_taxes = WC_Tax::calc_tax($discounted_price * $values['quantity'], $item_tax_rates);
                    $discounted_tax_amount = array_sum($discounted_taxes);
                    $line_tax = $discounted_tax_amount;
                    $line_total = $discounted_price * $values['quantity'];

                    // Tax rows - merge the totals we just got
                    foreach (array_keys($this->taxes + $discounted_taxes) as $key) {
                        $this->taxes[$key] = (isset($discounted_taxes[$key]) ? $discounted_taxes[$key] : 0) + (isset($this->taxes[$key]) ? $this->taxes[$key] : 0);
                    }
                }

                // Cart contents total is based on discounted prices and is used for the final total calculation
                $this->cart_contents_total += $line_total;

                // Store costs + taxes for lines
                $this->cart_contents[$cart_item_key]['line_total'] = $line_total;
                $this->cart_contents[$cart_item_key]['line_tax'] = $line_tax;
                $this->cart_contents[$cart_item_key]['line_subtotal'] = $line_subtotal;
                $this->cart_contents[$cart_item_key]['line_subtotal_tax'] = $line_subtotal_tax;

                // Store rates ID and costs - Since 2.2
                $this->cart_contents[$cart_item_key]['line_tax_data'] = array('total' => $discounted_taxes, 'subtotal' => $taxes);
            }

            // Only calculate the grand total + shipping if on the cart/checkout
            if (is_checkout() || is_cart() || defined('WOOCOMMERCE_CHECKOUT') || defined('WOOCOMMERCE_CART') || is_order_product_form()) {

                // Calculate the Shipping
                $this->calculate_shipping();

                // Trigger the fees API where developers can add fees to the cart
                $this->calculate_fees();

                // Total up/round taxes and shipping taxes
                if ($this->round_at_subtotal) {
                    $this->tax_total = WC_Tax::get_tax_total($this->taxes);
                    $this->shipping_tax_total = WC_Tax::get_tax_total($this->shipping_taxes);
                    $this->taxes = array_map(array('WC_Tax', 'round'), $this->taxes);
                    $this->shipping_taxes = array_map(array('WC_Tax', 'round'), $this->shipping_taxes);
                } else {
                    $this->tax_total = array_sum($this->taxes);
                    $this->shipping_tax_total = array_sum($this->shipping_taxes);
                }

                // VAT exemption done at this point - so all totals are correct before exemption
                if (WC()->customer->is_vat_exempt()) {
                    $this->remove_taxes();
                }

                // Allow plugins to hook and alter totals before final total is calculated
                do_action('woocommerce_calculate_totals', $this);

                // Grand Total - Discounted product prices, discounted tax, shipping cost + tax
                $this->total = max(0, apply_filters('woocommerce_calculated_total', round($this->cart_contents_total + $this->tax_total + $this->shipping_tax_total + $this->shipping_total + $this->fee_total, $this->dp), $this));

            } else {

                // Set tax total to sum of all tax rows
                $this->tax_total = WC_Tax::get_tax_total($this->taxes);

                // VAT exemption done at this point - so all totals are correct before exemption
                if (WC()->customer->is_vat_exempt()) {
                    $this->remove_taxes();
                }
            }

            do_action('woocommerce_after_calculate_totals', $this);

            $this->set_session();
        }
    }
}