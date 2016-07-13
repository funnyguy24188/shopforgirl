<?php

require_once(__DIR__ . '/../customer/SPGCustomerDetail.php');
require_once(__DIR__ . '/../product/SPGProductDetail.php');


class SPGOrder
{


    public function __construct()
    {

    }

    public function init_hook()
    {
        add_action('init', array($this, 'handle_order_form'));
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