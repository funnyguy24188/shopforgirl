<?php

require_once 'SPGOrder.php';

class SPGOrderList extends SPGOrder
{
    public function init_hook()
    {
        parent::init_hook();
        add_action('wp_ajax_nopriv_ajax_change_order_status', array($this, 'ajax_change_order_status'));
        add_action('wp_ajax_ajax_change_order_status', array($this, 'ajax_change_order_status'));
    }

    public function ajax_change_order_status()
    {
        $order_id = !empty($_POST['order_id']) ? $_POST['order_id'] : '';
        $order_status = !empty($_POST['order_status']) ? $_POST['order_status'] : '';

        $order = wc_get_order($order_id);

        $this->check_valid_order($order_id);

        if (!$order->update_status($order_status)) {
            echo json_encode(
                array(
                    'result' => false,
                    'message' => 'Cập nhật hóa đơn không thành công',
                    'data' => array()
                )
            );
            die;
        }

        echo json_encode(
            array(
                'result' => true,
                'message' => 'Cập nhật hóa đơn thành công',
                'data' => array()
            )
        );
        die;

    }

    /**
     * Return a list of order
     */

    public function get_order_list($term = '', $order_statuses = '', $start_date = '', $end_date = '', $page = 1 , $user_id = '')
    {

        $posts_per_page = get_option('posts_per_page');
        $start_date = !empty($start_date) ? explode('/', $start_date) : '';
        $end_date = !empty($end_date) ? explode('/', $end_date) : explode('/', date('d/m/Y', time()));
        $date_query = array();
        $ret = array();
        $post_ids = array();
        $all_order_status = self::get_order_status();

        if (empty($order_statuses)) {
            $order_statuses = array_keys(wc_get_order_statuses());
        }

        if (!empty($start_date) && !empty($end_date)) {
            $date_query['after'] = array(
                'year' => $start_date[2],
                'month' => $start_date[1],
                'day' => $start_date[0]
            );

            $date_query['before'] = array(
                'year' => $end_date[2],
                'month' => $end_date[1],
                'day' => $end_date[0]
            );


        } else {
            if (!empty($start_date)) {
                $date_query['after'] = array(
                    'year' => $start_date[2],
                    'month' => $start_date[1],
                    'day' => $start_date[0]
                );
            } else {
                $date_query['before'] = array(
                    'year' => $end_date[2],
                    'month' => $end_date[1],
                    'day' => $end_date[0]
                );
            }
        }


        if (!empty($term)) {
            $post_ids = wc_order_search($term);
        }

        $order_arg = array(
            'paged' => $page
        );

        $date_query['inclusive'] = true;
        $order_arg['date_query'] = $date_query;
        $order_arg['post_type'] = 'shop_order';
        $order_arg['post_status'] = $order_statuses;
        $order_arg['posts_per_page'] = $posts_per_page;
        if (!empty($post_ids)) {
            $order_arg['post__in'] = $post_ids;
        }
        $query = new WP_Query($order_arg);
        /**
         * @TODO: Need optimize perfomance here. we can get by one query for all
         */
        if ($query->found_posts) {
            $ret['query_object'] = $query;
            foreach ($query->posts as $post) {
                $order = wc_get_order($post->ID);
                $customer_name = $order->billing_first_name . ' ' . $order->billing_last_name;
                $address = $order->billing_address_1;
                $phone = $order->billing_phone;
                $email = $order->billing_email;
                $product_data = $this->get_order_detail($order);
                $ret[$order->id] = array(
                    'order_short_info' => array(
                        'order_id' => $order->id,
                        'order_date' => date('d/m/Y H:i:s', strtotime($order->order_date)),
                        'order_status' => $order->post_status,
                        'order_status_text' => $all_order_status[$order->post_status],
                        'order_total' => $order->get_total(),
                        'order_quantity' => 0,
                        'user_id' => '',
                    ),
                    'customer_short_info' => array(
                        'customer_name' => $customer_name,
                        'address' => $address,
                        'phone' => $phone,
                        'email' => $email
                    ),
                    'order_detail' => array(),
                );


                foreach ($product_data as $data) {
                    $ret[$order->id]['order_detail'][] = $data;
                    $ret[$order->id]['order_short_info']['order_quantity'] += absint($data['quantity']);
                }
            }
            return $ret;
        }

        return false;

    }


}