<?php
require_once 'SPGOrder.php';

class SPGOrderReturn extends SPGOrder
{

    public function init_hook()
    {
        parent::init_hook();
        add_action('wp_ajax_nopriv_ajax_save_return_order', array($this, 'ajax_save_return_order'));
        add_action('wp_ajax_ajax_save_return_order', array($this, 'ajax_save_return_order'));

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
                            $note = 'Customer return product: ' . $line_item['product_name']
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