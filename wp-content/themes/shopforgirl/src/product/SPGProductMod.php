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


    


}