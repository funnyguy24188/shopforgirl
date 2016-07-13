<?php

/**
 * Class SPGProductDetail
 * Handle getting product detail
 */
class SPGProductDetail
{
    public function init_hook()
    {
        add_action('wp_ajax_nopriv_get_product_info', array($this, 'get_product_info'));
        add_action('wp_ajax_get_product_info', array($this, 'get_product_info'));
    }

    /**
     * Get product information
     */

    public function get_product_info($barcode = '', $echo = true)
    {
        if (!$barcode) {
            $barcode = $_POST['barcode'];
        }

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
        $ret = array();

        if (empty($barcode)) {

            $ret = array(
                'result' => false,
                'data' => 'Not found'
            );
            if ($echo) {
                echo json_encode($ret);exit;
            } else {
                return $ret;
            }

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
            $ret = array(
                'result' => false,
                'data' => 'Not found'
            );
            if ($echo) {
                echo json_encode($ret);exit;
            } else {
                return $ret;
            }
        }
        // get 1 product each time
        $post = $posts[0];
        $product = wc_get_product($post->ID);
        $post_meta = get_post_meta($product->get_id());
        $image = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()));
        if (empty($image)) {
            // use the image fail back
            $image = get_stylesheet_directory_uri() . '/images/nophoto.jpg';
        } else {
            $image = $image[0];
        }

        // fill up basic inf
        $ret_pattern['id'] = $product->get_id();
        $ret_pattern['name'] = $post->post_title;
        $ret_pattern['barcode'] = !empty($post_meta['_barcode_field']) ? $post_meta['_barcode_field'][0] : '';
        $ret_pattern['image'] = $image;
        $ret_pattern['stock'] = $product->get_stock_quantity();
        $ret_pattern['regular_price'] = $product->get_regular_price();
        $ret_pattern['sale_price'] = $product->get_sale_price();
        $ret_pattern['attributes'] = $product->get_attributes();
        
        $ret = array(
            'result' => true,
            'data' => $ret_pattern
        );

        if ($echo) {
            echo json_encode($ret);exit;
        } else {
            return $ret;
        }

    }

}