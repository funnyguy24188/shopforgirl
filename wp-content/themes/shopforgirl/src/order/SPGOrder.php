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

        add_action('wp_ajax_nopriv_ajax_save_return_order', array($this, 'ajax_save_return_order'));
        add_action('wp_ajax_ajax_save_return_order', array($this, 'ajax_save_return_order'));
    }


    private function check_valid_order($order_id)
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
                // set quantity
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

    /**
     * Get customer order info
     */
    protected function get_order_customer_info()
    {

    }


    /**
     * Save the return order to system
     */

    public function ajax_save_return_order()
    {
        $order_id = !empty($_POST['order_id']) ? $_POST['order_id'] : '';
        // check and return the order if ok
        $order = $this->check_valid_order($order_id);
        $product_data = $this->get_order_detail($order);
        $return_quantity = !empty($_POST['return_data']) ? $_POST['return_data'] : '';
        $line_item_count = 0;
        $data_return = array();
        $result = true;

        if (!empty($return_quantity) && !empty($order)) {
            foreach ($product_data as $line_item) {
                foreach ($return_quantity as $return_line_item) {
                    if (absint($line_item['item_id']) == absint($return_line_item['item_id'])) {
                        if (absint($line_item['quantity']) >= absint($return_line_item['quantity'])) {

                            $product = wc_get_product($line_item['product_id']);
                            $current_stock = $product->get_stock_quantity();
                            $args = array(
                                'qty' => $line_item['quantity'] - $return_line_item['quantity']
                            );
                            WC_Abstract_Order::update_product($line_item['item_id'], $product, $args);
                            $new_stock = $current_stock + $return_line_item['quantity'];
                            wc_update_product_stock($line_item['product_id'], $new_stock);
                            // add note to order
                            $note = 'Customer return product:' . $line_item['product_name']
                                . '. ProductID: ' . $line_item['product_id']
                                . '. Quantity: ' . $return_line_item['quantity'];
                            $order->add_order_note($note);

                            $data_return[] = $line_item;
                        } else {
                            $result = false;
                        }
                    }
                }
                $line_item_count++;
            }
            // calculate total
            $order->calculate_totals();
        }

        echo json_encode(
            array(
                'result' => $result,
                'message' => ($result) ? 'Cập nhật hóa đơn thành công' : 'Có lỗi xảy ra vui lòng kiểm tra lại số lượng nhập',
                'data' => $data_return
            )
        );
        exit;

    }

}
