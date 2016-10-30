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
        $default_password = substr(uniqid(), 7);
        $username = '';
        if (!empty($customer_info['phone'])) {
            $username = $customer_info['phone'];
        } else {
            $username = $customer_info['email'];
        }
        // no username
        if (empty($username)) {
            return false;
        }
        // create new user account with phone or email
        $new_customer_id = wp_create_user($username, $default_password, $customer_info['email']);

        if (!$new_customer_id) {
            return false;
        }


        // update meta data
        update_user_meta($new_customer_id, 'customer-password', $default_password);
        if (!empty($customer_info['name'])) {
            $pos = strpos($customer_info['name'], ' ');
            $first_name = substr($customer_info['name'], 0, $pos);
            $last_name = substr($customer_info['name'], $pos + 1);
            wp_update_user(array('ID' => $new_customer_id,
                    'user_nicename' => $customer_info['name'],
                    'display_name' => $customer_info['name'],
                    'first_name' => $first_name,
                    'last_name' => $last_name
                )
            );
        }
        return $new_customer_id;

    }

    /**
     * Get customer information via phone
     */

    public function get_customer_info($phone = '', $echo = true)
    {
        $ret = false;
        if (!$phone) {
            $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
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