<?php

class SPGProductMod
{

    public function init_hook()
    {

        // Add Variation Settings
        add_action('woocommerce_product_after_variable_attributes', array($this, 'add_variable_product_barcode_field'), 10, 3);
        // Save Variation Settings
        add_action('woocommerce_save_product_variation', array($this, 'save_variation_settings_fields'), 10, 2);
        // add barcode field to simple product
        add_action('woocommerce_product_options_general_product_data', array($this, 'add_simple_product_barcode_field'));
        // save the barcode for simple product
        add_action('woocommerce_process_product_meta', array($this, 'save_simple_product_barcode_field'));
        // get barcode automatic
        add_action('wp_ajax_get_barcode_auto', array($this, 'get_barcode_auto'));
        add_action('wp_ajax_no_priv_get_barcode_auto', array($this, 'get_barcode_auto'));

    }

    public function add_simple_product_barcode_field()
    {
        global $woocommerce, $post, $product;
        $product = wc_get_product($post->ID);

        if ($product->is_type('simple')) {
            echo '<div class="options_group">';
            $value = (get_post_meta($post->ID, '_barcode_field', true));
            if ($value == 'Array') {
                $value = '';
            }
            woocommerce_wp_text_input(
                array(
                    'id' => '_barcode_field',
                    'class' => '_barcode_field _barcode-field-simple _barcode-field-' . $post->ID,
                    'label' => __('Barcode', 'woocommerce'),
                    'placeholder' => 'Barcode',
                    'desc_tip' => 'true',
                    'description' => __('Enter the barcode here.', 'woocommerce'),
                    'value' => $value
                )
            );
            echo '</div>';
        }

    }

    public function save_simple_product_barcode_field($post_id)
    {
        // Text Field
        $woocommerce_text_field = $_POST['_barcode_field'];
        if (!empty($woocommerce_text_field))
            update_post_meta($post_id, '_barcode_field', esc_attr($woocommerce_text_field));
    }


    public function add_variable_product_barcode_field($loop, $variation_data, $variation)
    {
        // Text Field

        woocommerce_wp_text_input(
            array(
                'id' => '_barcode_field[' . $variation->ID . ']',
                'class' => '_barcode_field _barcode-field-variation _barcode-field-' . $variation->ID,
                'label' => __('Barcode', 'woocommerce'),
                'placeholder' => 'Barcode',
                'desc_tip' => 'true',
                'description' => __('Enter the barcode value here.', 'woocommerce'),
                'value' => get_post_meta($variation->ID, '_barcode_field', true)
            )
        );
    }

    public function save_variation_settings_fields($post_id)
    {
        // Text Field
        $text_field = $_POST['_barcode_field'][$post_id];
        if (!empty($text_field)) {
            update_post_meta($post_id, '_barcode_field', esc_attr($text_field));
        }

    }

    public function get_barcode_auto()
    {
        global $wpdb;

        if (!empty($_POST['product_id']) && !empty($_POST['product_type'])) {
            $product_type = $_POST['product_type'];
            $product_id = $_POST['product_id'];


            if ($product_type == 'variation') {
                $product = wc_get_product($product_id);
                $product_parent_id = $product->parent->id;

            } else {
                $product_parent_id = $product_id;
            }

            $first_term = wp_get_post_terms($product_parent_id, 'product_cat');
            if (!empty($first_term)) {
                $first_term = $first_term[0];
                $term_id = $first_term->term_taxonomy_id;
                $spg_options = get_option('spg_options');

                if (!empty($spg_options['product_prefix_term'][$term_id]) && !empty($spg_options['barcode_default_length'])) {
                    $prefix = $spg_options['product_prefix_term'][$term_id];
                    $barcode_length = $spg_options['barcode_default_length'] - strlen($prefix);
                    $str_barcode = '';

                    // count product
                    $sql = "select meta_value from {$wpdb->prefix}posts 
                      inner join  {$wpdb->prefix}postmeta on  {$wpdb->prefix}postmeta.post_id  =  {$wpdb->prefix}posts.ID 
                      where post_status = 'publish' and ( post_type = 'product' OR post_type = 'product_variation')
                      and meta_key = '_barcode_field' and meta_value != 'Array' and meta_value REGEXP '^{$prefix}' 
                      ";
                    $ret = $wpdb->get_results($sql, ARRAY_N);
                    $max_barcode_num = 0;
                    if (empty($ret)) {
                        $max_barcode_num = 1;
                    } else {
                        $tmp = array();
                        foreach ($ret as $item) {
                            $tmp[] = (int)substr($item[0], strlen($prefix));
                        }

                        $max_barcode_num = max($tmp);
                        $max_barcode_num++;
                    }
                    $str_barcode = sprintf("%'.0{$barcode_length}d", $max_barcode_num);
                    $str_barcode = $prefix . $str_barcode;
                    update_post_meta($product_id, '_barcode_field', $str_barcode);

                    echo json_encode(array('result' => true,
                        'data' => array('barcode' => $str_barcode),
                        'message' => 'Barcode generated successfully'));

                } else {
                    // SPG setting was set
                    echo json_encode(array('result' => false,
                        'data' => array(),
                        'message' => 'Barcode settings were not set. Please set it up'));
                }
            } else {
                // No category set
                echo json_encode(array('result' => false,
                    'data' => array(),
                    'message' => 'No category was set. Please set it up'));
            }

        }
        die;

    }


}