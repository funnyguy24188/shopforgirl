<?php
require_once ABSPATH . 'wp-content/plugins/woocommerce/includes/abstracts/abstract-wc-order.php';

/**
 *
 * Class SPGOrder
 * Use for get order information
 */
class SPGOrder
{

    public function init_hook()
    {
        add_action('wp_ajax_nopriv_ajax_get_order_info', array($this, 'ajax_get_order_info'));
        add_action('wp_ajax_ajax_get_order_info', array($this, 'ajax_get_order_info'));

    }


    public static function get_order_status()
    {
        $statuses = wc_get_order_statuses();
        // we dont need the on-hold and refund
        unset($statuses['wc-on-hold']);
        unset($statuses['wc-refunded']);
        return $statuses;
    }

    /**
     * @param $order_id
     * @return WC_Order
     */

    protected function check_valid_order($order_id)
    {
        if (!$order_id) {
            echo json_encode(
                array(
                    'result' => false,
                    'message' => 'Số hóa đơn không được rỗng',
                    'data' => array()
                )
            );
            die;
        }

        // load order from database
        $order = new WC_Order($order_id);
        if (empty($order)) {
            echo json_encode(
                array(
                    'result' => false,
                    'message' => 'Không tìm thấy order',
                    'data' => array()
                )
            );
            die;
        }
        return $order;

    }

    /**
     * Get order detail items info
     */
    protected function get_order_items($order = null)
    {
        $ret = array();
        if (null == $order) {
            return false;
        }
        $ret = $order->get_items();
        return $ret;
    }

    /**
     * Get order detail data in pattern for reuse
     * @param $order
     */
    protected function get_order_detail($order)
    {
        $order_detail_items = $this->get_order_items($order);
        $product_data = array();
        // Refactor the product line for return
        foreach ($order_detail_items as $item_id => $line_item) {
            $item_data = array(
                'item_id' => 0,
                'product_id' => 0,
                'product_name' => '',
                'barcode' => '',
                'quantity' => 0,
                'price' => 0
            );

            if (0 == $line_item['variation_id']) {
                $product = wc_get_product($line_item['product_id']);
                $item_data['barcode'] = get_post_meta($line_item['product_id'], '_barcode_field', true);
                $item_data['product_id'] = $line_item['product_id'];
            } else {

                $item_data['barcode'] = get_post_meta($line_item['variation_id'], '_barcode_field', true);
                $product = wc_get_product($line_item['variation_id']);
                $item_data['product_id'] = $line_item['variation_id'];
            }

            if ($product) {
                $item_data['item_id'] = $item_id;
                $item_data['product_name'] = SPGUtil::get_product_simple_name($product);
                // price
                $item_data['price'] = $product->get_price();
                $item_data['quantity'] = $line_item['qty'];
                $product_data[] = $item_data;
            }

        }
        return $product_data;
    }

    public function ajax_get_order_info()
    {
        $order_id = !empty($_POST['order_id']) ? $_POST['order_id'] : '';
        // check and return the order if ok
        $order = $this->check_valid_order($order_id);
        $product_data = $this->get_order_detail($order);
        echo json_encode(
            array(
                'result' => true,
                'message' => '',
                'data' => $product_data
            )
        );
        exit;

    }

}
