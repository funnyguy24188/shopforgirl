<?php

require_once(__DIR__ . '/../customer/SPGCustomerDetail.php');
require_once(ABSPATH . 'wp-content/plugins/spg-barcode/lib/SPGUtil.php');
require_once(__DIR__ . '/../product/SPGProductDetail.php');
require_once(__DIR__ . '/../printer/SPGPrinterOrder.php');
require_once('SPGCart.php');


class SPGCartGlobalManager
{
    public $cart_array = array();
    public $shipping_array = array();
    public static $_instance;

    public $shipping_total = array();
    public $shipping_taxes = array();

    private $current_cart_index = 0;

    public static function get_instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    private function __construct()
    {

    }

    public function get_cart()
    {
        return WC()->cart->get_cart();

    }

    public function add_to_cart($product_id, $quantity = 1)
    {
        WC()->cart->add_to_cart($product_id, $quantity);
    }

    public function remove_product_on_cart($product_id)
    {
        $active_cart = WC()->cart->get_cart();
        $id = 0;
        foreach ($active_cart as $key => $item) {
            $product_object = $item['data'];
            // check the product id in cart is need to deleted
            if ($product_object->is_type('variation')) {
                $id = $product_object->variation_id;
            } else {
                $id = $product_object->id;
            }

            if ($id == $product_id) {
                WC()->cart->remove_cart_item($key);
                break;
            }
        }
    }

    public function empty_cart()
    {
        WC()->cart->empty_cart();
    }

    /**
     * Parse product data for display in order list
     */
    public function parse_products_data()
    {
        $current_cart = $this->get_cart();

        $ret = array();
        foreach ($current_cart as $cart_key => $item) {
            $quantity = $item['quantity'];
            $product_object = $item['data'];


            $product_id = $product_object->id;
            $regular_price = $product_object->get_price();
            $sale_price = $product_object->get_sale_price();
            $stock = $product_object->get_stock_quantity();

            if ($product_object->is_type('variation')) {
                $product_id = $product_object->variation_id;
            }

            $product_name = SPGUtil::get_product_simple_name($product_object);

            $barcode = get_post_meta($product_id, '_barcode_field', true);

            $ret[] = array(
                'id' => $product_id,
                'barcode' => $barcode,
                'name' => $product_name,
                'stock' => $stock,
                'image' => '',
                'attributes' => '',
                'regular_price' => $regular_price,
                'sale_price' => $sale_price,
                'quantity' => $quantity,
            );
        }

        return $ret;

    }

    public function calculate_totals()
    {
        WC()->cart->calculate_totals();
        WC()->cart->calculate_shipping();
        WC()->cart->calculate_fees();
    }

    public function is_cart_empty()
    {
        return WC()->cart->is_empty();
    }

    public function init_hook()
    {
        add_action('wp_ajax_nopriv_ajax_add_product_to_cart', array($this, 'ajax_add_product_to_cart'));
        add_action('wp_ajax_ajax_add_product_to_cart', array($this, 'ajax_add_product_to_cart'));
        add_action('wp_ajax_nopriv_ajax_remove_product_to_cart', array($this, 'ajax_remove_product_to_cart'));
        add_action('wp_ajax_ajax_remove_product_to_cart', array($this, 'ajax_remove_product_to_cart'));
        add_action('wp_ajax_nopriv_ajax_add_shipping_method', array($this, 'ajax_add_shipping_method'));
        add_action('wp_ajax_ajax_add_shipping_method', array($this, 'ajax_add_shipping_method'));
        add_action('init', array($this, 'handle_order_form'));
    }


    /**
     * Get a product info and  add to cart
     */

    public function ajax_add_product_to_cart()
    {
        $product_finding = new SPGProductDetail();
        $barcode = (!empty($_POST['barcode'])) ? $_POST['barcode'] : null;
        $quantity = (!empty($_POST['quantity'])) ? $_POST['quantity'] : 1;
        $ret = $product_finding->get_product_info($barcode, false);
        $result = false;
        $data = array();
        $shipping_block = '<span id="shipping_method">chưa tính ship</span>';
        if ($ret['result']) {
            $result = true;
            $this->add_to_cart($ret['data']['id'], $quantity);
            $this->calculate_totals();
            $data['cart_items'] = $this->parse_products_data();
            // init first shiping block
            if (!$this->is_cart_empty()) {
                ob_start();
                wc_cart_totals_shipping_html();
                $shipping_block = ob_get_clean();
            }
        }
        $data['shipping_block'] = $shipping_block;
        $data['cart_total'] = $this->get_total();

        echo json_encode(array('result' => $result, 'data' => $data));
        exit;
    }


    public function ajax_remove_product_to_cart()
    {
        $product_finding = new SPGProductDetail();
        $barcode = (!empty($_POST['barcode'])) ? trim($_POST['barcode']) : null;
        $ret = $product_finding->get_product_info($barcode, false);
        $result = false;
        $data = array();
        $shipping_block = '<span id="shipping_method">chưa tính ship</span>';
        if ($ret['result']) {
            $result = true;
            $this->remove_product_on_cart($ret['data']['id']);
            $this->calculate_totals();
            $data['cart_items'] = $this->parse_products_data();
            if (!$this->is_cart_empty()) {
                ob_start();
                wc_cart_totals_shipping_html();
                $shipping_block = ob_get_clean();
            }
        }
        $data['shipping_block'] = $shipping_block;
        $data['cart_total'] = $this->get_total();
        echo json_encode(array('result' => $result, 'data' => $data));
        exit;
    }

    public function ajax_add_shipping_method()
    {
        $shipping_method = (!empty($_POST['shipping_method'])) ? $_POST['shipping_method'] : null;
        $result = false;
        if ($shipping_method) {
            $result = true;

            $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');

            if (isset($_POST['shipping_method']) && is_array($_POST['shipping_method'])) {
                foreach ($_POST['shipping_method'] as $i => $value) {
                    $chosen_shipping_methods[$i] = wc_clean($value);
                }
            }

            WC()->session->set('chosen_shipping_methods', $chosen_shipping_methods);
            WC()->cart->calculate_shipping();
            WC()->cart->calculate_fees();

        }
        echo json_encode(array('result' => $result, 'data' => $this->get_total()));
        exit;
    }

    /**
     * Get total amount of cart
     */
    public function get_total()
    {
        return WC()->cart->subtotal + WC()->cart->shipping_total + WC()->cart->get_taxes_total(false, false);
    }


    public function handle_order_form()
    {

        $customer_finding = new SPGCustomerDetail();

        if (!empty($_POST['order'])) {
            $order_status = isset($_POST['order']['status']) ? $_POST['order']['status'] : 'pending';
            $print_order = isset($_POST['order']['print_order']) ? 1 : 0;

            $customer_info = $_POST['order']['customer'];
            $current_user_id = get_current_user_id();
            // find customer
            $phone = !empty($customer_info['phone']) ? $customer_info['phone'] : '';
            $customer_saved = $customer_finding->get_customer_info($phone, false);
            $address = !empty($customer_info['address']) ? $customer_info['address'] : '';
            $shipping_address = !empty($customer_info['shipping_address']) ? $customer_info['shipping_address'] : '';

            $billing_info = array(
                'first_name' => $customer_info['name'],
                'phone' => $phone,
                'address_1' => $address,
            );

            $shipping_info = array(
                'first_name' => $customer_info['name'],
                'address_1' => $shipping_address,
            );


            $customer_id = 0;

            if (empty($customer_saved)) {
                // create new customer info to database
                $customer_id = $customer_finding->add_new_customer($customer_info);
            } else {
                $customer_id = $customer_saved['data']['id'];
            }

            // save the product

            $order_data = array(
                'status' => apply_filters('woocommerce_default_order_status', $order_status),
                'customer_id' => $current_user_id,
                'customer_note' => '',
                'cart_hash' => md5(json_encode(wc_clean(WC()->cart->get_cart_for_session())) . WC()->cart->total),
                'created_via' => 'checkout',
            );

            try {
                wc_transaction_query('start');

                $cart = $this->get_cart();
                $order = wc_create_order($order_data);
                // set meta customer data to order

                $cart = $this->get_cart();

                foreach ($cart as $cart_item_key => $values) {
                    $item_id = $order->add_product(
                        $values['data'],
                        $values['quantity'],
                        array(
                            'variation' => $values['variation'],
                            'totals' => array(
                                'subtotal' => $values['line_subtotal'],
                                'subtotal_tax' => $values['line_subtotal_tax'],
                                'total' => $values['line_total'],
                                'tax' => $values['line_tax'],
                                'tax_data' => $values['line_tax_data'] // Since 2.2
                            )
                        )
                    );

                    if (!$item_id) {
                        throw new Exception(sprintf(__('Error %d: Unable to create order. Please try again.', 'woocommerce'), 525));
                    }

                }

                // set stock and notify

                $order_items = $order->get_items();
                if ($order && !empty($order_items)) {
                    foreach ($order_items as $item_id => $order_item) {
                        $_product = $order->get_product_from_item($order_item);
                        if ($_product->exists() && $_product->managing_stock()) {
                            $stock_change = apply_filters('woocommerce_reduce_order_stock_quantity', $order_item['qty'], $item_id);
                            $new_stock = $_product->reduce_stock($stock_change);
                            $item_name = $_product->get_sku() ? $_product->get_sku() : $order_item['product_id'];
                            $note = sprintf(__('Item %s stock reduced from %s to %s.', 'woocommerce'), $item_name, $new_stock + $stock_change, $new_stock);
                            $return[] = $note;
                            $order->add_order_note($note);
                            $order->send_stock_notifications($_product, $new_stock, $order_item['qty']);
                        }
                    }

                }

                // calculate total and set total to order
                $this->calculate_totals();


                $order->set_address($billing_info, 'billing');
                $order->set_address($shipping_info, 'shipping');
                // $order->set_payment_method( $this->payment_method );
                $order->set_total(WC()->cart->shipping_total, 'shipping');
                $order->set_total(WC()->cart->get_cart_discount_total(), 'cart_discount');
                $order->set_total(WC()->cart->get_cart_discount_tax_total(), 'cart_discount_tax');
                $order->set_total(WC()->cart->tax_total, 'tax');
                $order->set_total(WC()->cart->shipping_tax_total, 'shipping_tax');
                $order->set_total($this->get_total());

                wc_transaction_query('commit');

                // empty cart after order  saved
                $this->empty_cart();
                // print the order
                if ($print_order) {
                    $this->call_print_order($order->id);
                }

                // redirec
                wp_redirect('order-product', 301);

            } catch (Exception $e) {
                // There was an error adding order data!
                wc_transaction_query('rollback');
                return new WP_Error('checkout-error', $e->getMessage());
            }

        }

    }


    public function call_print_order($order_id)
    {
        $order = wc_get_order($order_id);

        $order_data = array(
            'items' => array(),
            'shipping' => 0,
            'total' => 0
        );

        $order_items = $order->get_items();

        foreach ($order_items as $item_key => $item) {
            $product_id = 0;
            $barcode = 0;
            if (!empty($item['variation_id'])) {
                $product_id = $item['variation_id'];
            } else {
                $product_id = $item['product_id'];
            }

            $product_object = wc_get_product($product_id);
            $product_name = SPGUtil::get_product_simple_name($product_object);


            if (!empty($product_id)) {
                $barcode = get_post_meta($product_id, '_barcode_field', true);
            }


            $order_data['items'][] = array('name' => $product_name,
                'quantity' => $item['qty'],
                'price' => $item['line_total'],
                'barcode' => $barcode,
            );

        }

        $order_data['total'] = $order->get_total();
        $order_data['shipping'] = $order->get_total_shipping();
        $order_data['order_id'] = $order_id;
        $order_data['customer_money'] = !empty($_POST['order']['customer_money']) ? $_POST['order']['customer_money'] : 0;
        $printer = new SPGPrinterOrder($order_data);
        // make the pdf file
        $pdf_file = $printer->print_data();
        if ($pdf_file) {
            $_SESSION['order_pdf_link'] = wp_upload_dir()['baseurl'] . DIRECTORY_SEPARATOR . 'order_tmp' . DIRECTORY_SEPARATOR . $pdf_file;
        }

    }


}

function SPGCartGlobal()
{
    return SPGCartGlobalManager::get_instance();
}

$GLOBALS['SPGCart'] = SPGCartGlobal();

