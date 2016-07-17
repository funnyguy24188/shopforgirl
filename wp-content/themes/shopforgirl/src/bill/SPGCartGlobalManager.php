<?php

require_once(__DIR__ . '/../customer/SPGCustomerDetail.php');
require_once(__DIR__ . '/../product/SPGProductDetail.php');
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
                $id = $product_object->product_id;
            }

            if ($id == $product_id) {
                WC()->cart->remove_cart_item($key);
                break;
            }
        }
    }

    public function empty_cart($cart_index)
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
            // Product is variation

            $product_id = $product_object->variation_id;
            $product_name = $product_object->post->post_title;
            $regular_price = $product_object->get_price();
            $sale_price = $product_object->get_sale_price();
            $stock = $product_object->get_stock_quantity();


            if ($product_object->get_type() == 'variation') {
                $variation_attributes = SPGProductDetail::get_variable_product_attributes($product_object);
                $product_name .= ' - ( ' . implode(' ', $variation_attributes) . ' ) ';
            }

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
       /* WC()->cart->calculate_shipping();
        WC()->cart->calculate_fees();*/
    }

    public function init_hook()
    {
        add_action('wp_ajax_nopriv_ajax_add_product_to_cart', array($this, 'ajax_add_product_to_cart'));
        add_action('wp_ajax_ajax_add_product_to_cart', array($this, 'ajax_add_product_to_cart'));
        add_action('wp_ajax_nopriv_ajax_remove_product_to_cart', array($this, 'ajax_remove_product_to_cart'));
        add_action('wp_ajax_ajax_remove_product_to_cart', array($this, 'ajax_remove_product_to_cart'));
        add_action('wp_ajax_nopriv_ajax_add_shipping_method', array($this, 'ajax_add_shipping_method'));
        add_action('wp_ajax_ajax_add_shipping_method', array($this, 'ajax_add_shipping_method'));
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
        if ($ret['result']) {
            $data = $ret['data'];
            $result = true;
            $this->add_to_cart($data['id'], $quantity);
            $data = $this->parse_products_data();
        }

        echo json_encode(array('result' => $result, 'data' => $data));
        exit;
    }


    public function ajax_remove_product_to_cart()
    {
        $product_finding = new SPGProductDetail();
        $barcode = (!empty($_POST['barcode'])) ? $_POST['barcode'] : null;
        $ret = $product_finding->get_product_info($barcode, false);
        $result = false;
        $data = array();
        if ($ret['result']) {
            $data = $ret['data'];
            $result = true;
            $this->remove_product_on_cart($data['id']);
            $data = $this->parse_products_data();
        }
        echo json_encode(array('result' => $result, 'data' => $data));
        exit;
    }

    public function ajax_add_shipping_method() {
        $shipping_method =  (!empty($_POST['shipping'])) ? $_POST['shipping'] : null;
        $result = false;
        $data = array();
        if($shipping_method) {
            $result = true;
            WC()->session->set( 'chosen_shipping_methods', $shipping_method );
            $this->calculate_totals();
            $data = $this->parse_products_data();
        }
        echo json_encode(array('result' => $result, 'data' => $data));
        exit;
    }


    public function handle_order_form()
    {
        $customer_finding = new SPGCustomerDetail();
        $product_finding = new SPGProductDetail();
        if (isset($_POST['order'])) {
            $customer_info = $_POST['order']['customer'];
            $current_user_id = get_current_user_id();
            // find customer
            $phone = $customer_info['phone'];
            $customer_saved = $customer_finding->get_customer_info($phone, false);
            $customer_id = 0;


            if (empty($customer_saved)) {
                // create new customer info to database
                $customer_id = $customer_finding->add_new_customer($customer_info);
            } else {
                $customer_id = $customer_saved['data']['id'];
            }

            // save the product
            if (!empty($_POST['order']['product'])) {
                $product_list = $_POST['order']['product'];
                $arg = array(
                    'post_status' => 'wc-completed',
                    'post_author' => $current_user_id
                );

                $order = wc_create_order($arg);
                // set meta customer data to order
                $order->billing_address_1 = $customer_info['address'];
                $order->billing_phone = $customer_info['phone'];
                $order->billing_email = $customer_info['email'];

                // $order->shipping_address_1($customer_info['email']);
                // add products to order


                foreach ($product_list as $barcode => $quantity) {
                    $product_info = $product_finding->get_product_info($barcode, false);

                    if ($product_info['result']) {
                        $product_id = $product_info['data']['id'];
                        $full_product_object = wc_get_product($product_id);
                        $order->add_product($full_product_object, $quantity);
                    }

                }
                $order->calculate_totals();
                exit;
            }

        }

    }

    public function load_order($order_id)
    {

    }

    public function create_order()
    {

    }

    public function print_order()
    {

    }

    private function convert_order_pdf()
    {

    }
}

function SPGCartGlobal()
{
    return SPGCartGlobalManager::get_instance();
}

$GLOBALS['SPGCart'] = SPGCartGlobal();