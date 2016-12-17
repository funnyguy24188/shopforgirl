<?php
require_once(get_home_path() . 'wp-content/plugins/spg-barcode/lib/BarCodeWrap.php');

/**
 * Class SPGProductDetail
 * Handle getting product detail
 */
class SPGProductDetail
{

    public function init_hook()
    {
        add_action('woocommerce_single_product_summary', array($this, 'add_barcode_to_product_detail'));
        add_action('woocommerce_after_shop_loop_item_title', array($this, 'add_barcode_to_product_item'));
    }

    /**
     * Add barcode field to product detail
     */
    public function add_barcode_to_product_detail()
    {
        wc_get_template_part('templates/content-barcode');
    }

    /**
     * Add barcode field to product item in product list
     */

    public function add_barcode_to_product_item()
    {
        wc_get_template_part('templates/content-barcode-loop-item');
    }

    /**
     * Get the main product id
     * @param $product
     * @return bool
     */

    public static function get_product_id($product)
    {
        if (empty($product)) {
            return false;
        }
        if ($product->is_type('variation')) {
            return $product->variation_id;
        }
        return $product->id;
    }

    /**
     * Get product variation data
     * @param $product
     * @return array
     */

    public static function get_variable_product_attributes($product)
    {

        $variation_data = $product->get_variation_attributes();
        $attributes = $product->parent->get_attributes();
        $return = array();

        if (is_array($variation_data)) {


            foreach ($attributes as $attribute) {

                // Only deal with attributes that are variations
                if (!$attribute['is_variation']) {
                    continue;
                }

                $variation_selected_value = isset($variation_data['attribute_' . sanitize_title($attribute['name'])]) ? $variation_data['attribute_' . sanitize_title($attribute['name'])] : '';
                $return[] = esc_html(wc_attribute_label($attribute['name']));


                // Get terms for attribute taxonomy or value if its a custom attribute
                if ($attribute['is_taxonomy']) {

                    $post_terms = wp_get_post_terms($product->id, $attribute['name']);

                    foreach ($post_terms as $term) {
                        if ($variation_selected_value === $term->slug) {
                            $return[] = esc_html(apply_filters('woocommerce_variation_option_name', $term->name));
                        }
                    }

                } else {

                    $options = wc_get_text_attributes($attribute['value']);

                    foreach ($options as $option) {

                        if (sanitize_title($variation_selected_value) === $variation_selected_value) {
                            if ($variation_selected_value !== sanitize_title($option)) {
                                continue;
                            }
                        } else {
                            if ($variation_selected_value !== $option) {
                                continue;
                            }
                        }

                        $return[] = esc_html(apply_filters('woocommerce_variation_option_name', $option));
                    }
                }

            }


        }

        return $return;

    }

    /**
     * Get barcode field
     * @param $product
     */
    public static function get_barcode_field($product)
    {

        $base_url = wp_get_upload_dir()['baseurl'];
        $product_id = self::get_product_id($product);
        $barcode_file_name = "tmp_barcode_$product_id.png";
        $tmp_barcode_file = SPG_UPLOAD_PATH . $barcode_file_name;

        $barcode_url = $base_url . DIRECTORY_SEPARATOR . 'tmp_barcode' . DIRECTORY_SEPARATOR . $barcode_file_name;
        $barcode_image = '<img src={barcode_path}  alt="barcode-image"/>';
        $barcode_engine = new BarCodeWrap();

        $barcode = get_post_meta($product_id, '_barcode_field', true);
        unlink($tmp_barcode_file);
        $args = array('name' => '', 'price' => '');
        $barcode_engine->generate_barcode($tmp_barcode_file, $barcode, 90, 'horizontal', SPG_DEFAULT_BARCODE_TYPE, true, $args);
        $barcode_image = str_replace('{barcode_path}', $barcode_url, $barcode_image);

        return $barcode_image;

    }

    public function get_product_info($barcode = '', $echo = true)
    {
        if (!$barcode) {
            $barcode = trim($_POST['barcode']);
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
                echo json_encode($ret);
                exit;
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
                echo json_encode($ret);
                exit;
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
            'data' => $ret_pattern,
            'raw_post' => $post
        );

        if ($echo) {
            echo json_encode($ret);
            exit;
        } else {
            return $ret;
        }

    }

}