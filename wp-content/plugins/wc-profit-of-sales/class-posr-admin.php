<?php
if ( !class_exists( 'POSR_Admin' ) ) {

/**
 * Profit of Sales Admin Class
 * Class POSR_Admin
 */
class POSR_Admin extends POSR_Base
{
    public function __construct()
    {
        parent::__construct();

        add_action('init', array($this, 'init'));
        add_action('admin_init', array($this, 'admin_init'));

        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Do everything needed on initialization
     */
    public function init()
    {
        add_action('admin_menu', array($this, 'admin_menu'));
    }

    /**
     * Initialiaze everything needed for admin
     */
    public function admin_init()
    {
        // EDIT PRODUCT
        add_action( 'woocommerce_product_options_pricing', array( $this, 'product_options_pricing' ) );
        add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'product_after_variable_attributes'), 10, 3 );

        add_action( 'woocommerce_process_product_meta', array($this, 'save_product_meta') );
        add_action( 'woocommerce_save_product_variation', array($this, 'save_product_variation'), 10, 2 );

        // QUICK EDIT PRODUCT
        add_action( 'woocommerce_product_quick_edit_end',  array( $this, 'quick_edit' ) );
        add_action( 'manage_product_posts_custom_column', array($this, 'products_custom_column') ,2, 50);
        add_action( 'woocommerce_product_quick_edit_save', array( $this, 'quick_edit_save' ) );

        // ORDER DETAILS
        add_action( 'woocommerce_hidden_order_itemmeta', array($this, 'hide_order_itemmeta') );
        add_action( 'woocommerce_admin_order_item_headers', array($this, 'add_order_item_headers') );
        add_action( 'woocommerce_admin_order_item_values', array($this, 'add_order_item_values'), 10, 3 );
        add_action( 'woocommerce_saved_order_items', array($this, 'save_order_items'), 10, 2 );
        add_action( 'woocommerce_admin_order_totals_after_refunded', array($this, 'show_profit') );
        add_action( 'woocommerce_ajax_order_item', array($this, 'ajax_order_item'), 10, 2);

        // REPORTS
        add_action( 'woocommerce_admin_reports', array($this, 'admin_reports') );
    }

    /**
     * Add additional admin menu
     */
    public function admin_menu()
    {
        // Add Calculate COG submenu
        add_submenu_page(
            'woocommerce',
            __('Calculate Cost of Goods of Orders', self::TEXT_DOMAIN),
            __('Calculate COG', self::TEXT_DOMAIN),
            'edit_products',
            'calculate_cog',
            array($this, 'calculate_order_item_cogs')
        );
    }

    /**
     * Enqueue style and script needed by plugin
     */
    public function enqueue_scripts()
    {
        global $wp_scripts;

        $screen = get_current_screen();

        if ($screen->id == 'product' || $screen->id == 'edit-product') {
            wp_enqueue_script( 'posr-admin', plugins_url('assets/js/admin.js', __FILE__), array('jquery') );
        }

    }

    public function ajax_order_item($item, $item_id)
    {
        $cog = ($item['variation_id'] != '') ?
            get_post_meta($item['variation_id'], '_posr_cost_of_good', true) :
            get_post_meta($item['product_id'], '_posr_cost_of_good', true);

        $item['posr_line_cog_total'] = $cog * $item['qty'];

        return $item;
    }

    /**
     * Hide displaying itemmeta in order details
     * @param $hidden_meta
     * @return array
     */
    public function hide_order_itemmeta($hidden_meta)
    {
        $hidden_meta[] = '_posr_line_cog_total';

        return $hidden_meta;
    }

    /**
     * Add header column in order details
     */
    public function add_order_item_headers()
    {
        include POSR_PATH.'views/html-order-item-headers.php';
    }

    /**
     * Add value column in order details
     * @param $product
     * @param $item
     * @param $item_id
     */
    public function add_order_item_values($product, $item, $item_id)
    {
        if ($product) {
            //$cog = wc_get_order_item_meta($item_id, '_posr_line_cog_total', true);
            $cog = isset($item['posr_line_cog_total']) ? $item['posr_line_cog_total'] : 0;
            $cog = (float) $cog / max(1, $item['qty']);

            include POSR_PATH.'views/html-order-item-values.php';
        }
    }

    /**
     * Save order items data
     * @param $order_id
     * @param $items
     */
    public function save_order_items($order_id, $items)
    {
        if ( isset( $items['order_item_id'] ) ) {
            foreach($items['order_item_id'] as $item_id) {
                $item_id = absint( $item_id );

                if (isset($items['item_cog'][$item_id])) {
                    $item_qty = ($items['order_item_qty'][$item_id] > 0)? $items['order_item_qty'][$item_id] : 1;

                    wc_update_order_item_meta($item_id, '_posr_line_cog_total', wc_format_decimal( $items['item_cog'][$item_id] ) * $item_qty );
                }
            }
        }
    }

    /**
     * Show profit of an order
     * @param $order_id
     */
    public function show_profit($order_id)
    {
        $order = wc_get_order($order_id);

        if (!$order) return;

        $line_items = $order->get_items( 'line_item' );

        if (empty($line_items)) return;

        $price_total = $order->get_subtotal();
        $discount_total = $order->get_total_discount();

        $cog_total = 0;
        $total_refunded = 0;
        foreach ($line_items as $item_id => $item) {
            // get cog per item
            $line_cog_total = isset($item['posr_line_cog_total']) ? $item['posr_line_cog_total'] : 0;
            $item_cog = $line_cog_total / max(1, $item['qty']);

            // get refund per item
            $line_qty_refunded = $order->get_qty_refunded_for_item($item_id);
            $total_refunded += $order->get_total_refunded_for_item($item_id);

            $cog_total += ($item_cog * ($item['qty'] - $line_qty_refunded));
        }

        //$total_refunded = $order->get_total_refunded() - $order->get_total_tax_refunded();

        $profit = $price_total - $total_refunded - $cog_total - $discount_total;
        //($profit < 0)? $profit=0 : 0;

        include( POSR_PATH . 'views/html-order-profit.php');
    }

    /**
     * Set COG input in product details
     */
    public function product_options_pricing()
    {
        echo '</div><div class="options_group hide_if_group">';
        woocommerce_wp_text_input( array( 'id' => '_posr_cost_of_good', 'label' => __( 'Cost of Good', self::TEXT_DOMAIN ) . ' (' . get_woocommerce_currency_symbol() . ')', 'data_type' => 'price', 'desc_tip' => 'true', 'description' => __( 'Cost of this product when bought or produced.', self::TEXT_DOMAIN )  ) );
    }

    /**
     * Set COG input for variable product
     * @param $loop
     * @param $variation_data
     */
    public function product_after_variable_attributes($loop, $variation_data, $variation)
    {
        if ( isset( $variation_data['_posr_cost_of_good'][0] ) ) {
            $cog = $variation_data['_posr_cost_of_good'][0];
        } else {
            $cog = get_post_meta($variation->ID, '_posr_cost_of_good', true);
        }

        if ( version_compare( WOOCOMMERCE_VERSION, "2.3.0" ) < 0 ) {
            echo '<tr class="variable_pricing">
                    <td>
                        <label>' . __('Cost of Good - w,p,l,o,c,k,e,r,.,c,o,m', self::TEXT_DOMAIN) . ': (' . get_woocommerce_currency_symbol() . ')' . '</label>
                        <input type="text" size="5" name="variable_cost_of_good[' . $loop . ']" value="' . esc_attr(wc_format_localized_price($cog)) . '" class="wc_input_price" />
                    </td>
                </tr>';
        } else {
            echo '<div class="variable_pricing">
                    <p class="form-row form-row-first">
                        <label>' . __('Cost of Good', self::TEXT_DOMAIN) . ': (' . get_woocommerce_currency_symbol() . ')' . '</label>
                        <input type="text" name="variable_cost_of_good[' . $loop . ']" value="' . esc_attr(wc_format_localized_price($cog)) . '" class="wc_input_price" />
                    </p>
                </div>';
        }
    }

    /**
     * Save COG data
     * @param $product_or_var_id
     * @param $cost_of_good
     */
    protected function save_cost_of_good($product_or_var_id, $cost_of_good)
    {
        if ($cost_of_good >= 0) {
            update_post_meta( $product_or_var_id, '_posr_cost_of_good', wc_format_decimal( $cost_of_good ) );
        }
    }

    /**
     * Save COG data in product details
     * @param $product_id
     */
    public function save_product_meta($product_id)
    {
        $cost_of_good = $_POST['_posr_cost_of_good'];

        $this->save_cost_of_good($product_id, $cost_of_good);
    }

    /**
     * Save COG data of variable product
     * @param $variation_id
     * @param $i
     */
    public function save_product_variation($variation_id, $i)
    {
        $cost_of_good = $_POST['variable_cost_of_good'][ $i ];

        if ( $cost_of_good == 0 ) {
            $default_cog = get_post_meta($_POST['product_id'], '_posr_cost_of_good', true);
            if ( !empty($default_cog) ) {
                $cost_of_good = $default_cog;
            }
        }

        $this->save_cost_of_good($variation_id, $cost_of_good);
    }

    /**
     * Create inline edit form on product list table
     * @param $column_name
     * @param $post_type
     */
    public function quick_edit()
    {
        include( POSR_PATH . 'views/html-quick-edit-cog.php' );
    }

    /**
     * Add custom column info for quick edit
     * @param $column
     * @param $post_id
     */
    public function products_custom_column($column,$post_id)
    {
        if ( $column == 'name' ) {
            $product_cog = get_post_meta($post_id, '_posr_cost_of_good', true);
            $product_cog = $product_cog? wc_format_localized_price($product_cog) : 0;

            echo '
                <div class="hidden posr_inline" id="posr_inline_' . $post_id . '">
                    <div id="product_cog">' . esc_html($product_cog) . '</div>
                </div>
            ';
        }
    }

    /**
     * Save inline edit for product
     * @param $post_id
     * @param $post
     * @return mixed
     */
    public function quick_edit_save( $product )
    {
        if (isset($_REQUEST['_posr_product_cog'])) {
            $this->save_cost_of_good( $product->id, wc_clean($_REQUEST['_posr_product_cog']) );
        }
    }

    /**
     * Handle Calculate COG Form
     */
    public function calculate_order_item_cogs()
    {
        global $wpdb;

        $has_run = false;
        if (isset($_POST['Calculate']) &&
            wp_verify_nonce($_POST['posr_calculate_nonce'], 'posr_calculate')) {

            $order_items = $wpdb->get_results("
                SELECT order_items.order_item_id,
                       meta_product_id.meta_value AS product_id,
                       meta_variation_id.meta_value AS variation_id
                FROM {$wpdb->prefix}woocommerce_order_items AS order_items
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS meta_product_id ON order_items.order_item_id=meta_product_id.order_item_id AND meta_product_id.meta_key='_product_id'
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS meta_variation_id ON order_items.order_item_id=meta_variation_id.order_item_id AND meta_variation_id.meta_key='_variation_id'
                WHERE order_items.order_item_type='line_item'
            ");

            if (empty($order_items))
                return;

            foreach($order_items as $order_item) {
                $cost_of_good = !empty($order_item->variation_id) ?
                    get_post_meta($order_item->variation_id, '_posr_cost_of_good', true) :
                    get_post_meta($order_item->product_id, '_posr_cost_of_good', true);

                $order_item_qty = (int) wc_get_order_item_meta($order_item->order_item_id, '_qty', true);

                wc_update_order_item_meta( $order_item->order_item_id, '_posr_line_cog_total', wc_format_decimal( $cost_of_good ) * $order_item_qty );
            }
        }


        if ( (isset($_POST['Calculate']) || isset($_POST['ClearCache'])) &&
             wp_verify_nonce($_POST['posr_calculate_nonce'], 'posr_calculate') ) {

            delete_transient( 'posr_report_profit_by_date' );
            delete_transient( 'posr_report_profit_by_product' );
            delete_transient( 'posr_report_profit_by_category' );

            $has_run = true;
        }

        include POSR_PATH.'views/html-calculate-cog.php';
    }

    /**
     * Get a report from our reports subfolder
     */
    public static function get_report( $name ) {
        $name  = sanitize_title( str_replace( '_', '-', $name ) );
        $class = 'POSR_Report_' . str_replace( '-', '_', $name );

        include_once( POSR_PATH . '/reports/class-posr-report-' . $name . '.php' );

        if ( ! class_exists( $class ) )
            return;

        $report = new $class();
        $report->output_report();
    }

    /**
     * Handle Profit Reports
     * @param $reports
     * @return mixed
     */
    public function admin_reports($reports)
    {
        $reports['profit'] = array(
            'title'  => __( 'Profit', self::TEXT_DOMAIN ),
            'reports' => array(
                "profit_by_date"    => array(
                    'title'       => __( 'Profit by date', self::TEXT_DOMAIN ),
                    'description' => '',
                    'hide_title'  => true,
                    'callback'    => array( __CLASS__, 'get_report' )
                ),
                "profit_by_product"     => array(
                    'title'       => __( 'Profit by product', self::TEXT_DOMAIN ),
                    'description' => '',
                    'hide_title'  => true,
                    'callback'    => array( __CLASS__, 'get_report' )
                ),
                "profit_by_category" => array(
                    'title'       => __( 'Profit by category', self::TEXT_DOMAIN ),
                    'description' => '',
                    'hide_title'  => true,
                    'callback'    => array( __CLASS__, 'get_report' )
                ),
            )
        );

        return $reports;
    }

}

} // if class_exists