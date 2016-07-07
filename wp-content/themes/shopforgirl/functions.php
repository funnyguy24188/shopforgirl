<?php
session_start();
require_once  'src/BarCodeMetaBox.php';

function add_simple_product_barcode_field()
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

function save_simple_product_barcode_field($post_id)
{
    // Text Field
    $woocommerce_text_field = $_POST['_barcode_field'];
    if (!empty($woocommerce_text_field))
        update_post_meta($post_id, '_barcode_field', esc_attr($woocommerce_text_field));
}

// add barcode field to simple product
add_action('woocommerce_product_options_general_product_data', 'add_simple_product_barcode_field');

// save the barcode for simple product
add_action('woocommerce_process_product_meta', 'save_simple_product_barcode_field');

function add_variable_product_barcode_field($loop, $variation_data, $variation)
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

function save_variation_settings_fields($post_id)
{
    // Text Field
    $text_field = $_POST['_barcode_field'][$post_id];
    if (!empty($text_field)) {
        update_post_meta($post_id, '_barcode_field', esc_attr($text_field));
    }

}

// Add Variation Settings
add_action('woocommerce_product_after_variable_attributes', 'add_variable_product_barcode_field', 10, 3);
// Save Variation Settings
add_action('woocommerce_save_product_variation', 'save_variation_settings_fields', 10, 2);


/**
 * Get product information
 */

function get_product_info()
{
    $barcode = $_POST['barcode'];
    $ret_pattern = array(
        'id' => '',
        'barcode' => '',
        'name' => '',
        'stock' => '',
        'image' => '',
        'attributes' => '',
        'regular_price' => '',
        'sale_price' => ''
    );

    if (empty($barcode)) {
        return json_encode(
            array(
                'result' => false,
                'data' => 'Not found'
            )
        );
    }


    $arg = array(
        'post_type' => 'product',
        'meta_query' => array(
            array(
                'key' => '_barcode_field',
                'value' => $barcode
            )
        )
    );
    $posts = get_posts($arg);

    // in case the post is not available
    if (empty($posts)) {
        $arg = array(
            'post_type' => 'product_variation',
            'meta_query' => array(
                array(
                    'key' => '_barcode_field',
                    'value' => $barcode
                )
            )
        );
        $posts = get_posts($arg);
    }
    if (empty($posts)) {
        return json_encode(
            array(
                'result' => false,
                'data' => 'Not found'
            )
        );
    }
    // get 1 product each time
    $post = $posts[0];
    $product = wc_get_product($post->ID);
    $post_meta = get_post_meta($product->get_id());
    $image  = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_id() ));
    if(empty($image)) {
        // use the image fail back
        $image = get_stylesheet_directory_uri() .'/images/nophoto.jpg';
    } else {
        $image = $image[0];
    }

    // fill up basic inf
    $ret_pattern['id'] = $product->get_id();
    $ret_pattern['name'] = $post->post_title;
    $ret_pattern['barcode'] = !empty($post_meta['_barcode_field'])?$post_meta['_barcode_field'][0]:'';
    $ret_pattern['image'] = $image;
    $ret_pattern['stock'] = $product->get_stock_quantity();
    $ret_pattern['regular_price'] = $product->get_regular_price();
    $ret_pattern['sale_price'] = $product->get_sale_price();
    $ret_pattern['attributes'] = $product->get_attributes();

    echo json_encode(
        array(
            'result' => true,
            'data' => $ret_pattern
        )
    );
    exit;

}

add_action('wp_ajax_nopriv_get_product_info', 'get_product_info');
add_action('wp_ajax_get_product_info', 'get_product_info');

//add_action('admin_head',function (){
    $barcode_meta_box = new BarCodeMetaBox();
    $barcode_meta_box->init();

//});

