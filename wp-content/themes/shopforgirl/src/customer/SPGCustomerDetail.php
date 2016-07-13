<?php

class SPGCustomerDetail
{
    public function init_hook()
    {
        add_action('wp_ajax_nopriv_get_customer_info', array($this, 'get_customer_info'));
        add_action('wp_ajax_get_customer_info', array($this, 'get_customer_info'));
    }

    public function add_new_customer($customer_info)
    {
        $arg = array('post_type' => 'customer',
            'post_title' => $customer_info['name'],
        );
        $new_customer_id = wp_insert_post($arg);

        if (!$new_customer_id) {
            return false;
        }
        // update meta data
        update_post_meta($new_customer_id, 'wpcf-customer-phone', $customer_info['phone']);
        update_post_meta($new_customer_id, 'wpcf-customer-email', $customer_info['email']);
        update_post_meta($new_customer_id, 'wpcf-customer-address', $customer_info['address']);
        return $new_customer_id;

    }

    /**
     * Get customer information via phone
     */

    public function get_customer_info($phone = '', $echo = true)
    {
        $ret = false;
        if (!$phone) {
            $phone = $_POST['phone'];
        }

        $ret_pattern = array(
            'id' => '',
            'name' => '',
            'email' => '',
            'address' => ''
        );

        if (empty($phone)) {
            $ret = array(
                'result' => false,
                'data' => 'Not found'
            );
            if ($echo) {
                echo json_encode($ret);
                exit;
            }
            return $ret;
        }

        $arg = array(
            'post_type' => 'customer',
            'meta_query' => array(
                array(
                    'key' => 'wpcf-customer-phone',
                    'value' => trim($phone)
                )
            )
        );
        $customer = get_posts($arg);
        if (!empty($customer)) {
            $customer = $customer[0];
            $ret_pattern['id'] = $customer->ID;
            $ret_pattern['name'] = $customer->post_title;
            $ret_pattern['email'] = types_render_field('customer-email', array('output' => 'raw', 'post_id' => $customer->ID));
            $ret_pattern['address'] = types_render_field('customer-address', array('output' => 'raw', 'post_id' => $customer->ID));
        }

        $ret = array(
            'result' => true,
            'data' => $ret_pattern
        );

        if ($echo) {
            echo json_encode($ret);
            exit;
        } else {
            return $ret;
        }

    }
}