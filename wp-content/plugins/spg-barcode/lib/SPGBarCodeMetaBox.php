<?php

require_once 'BarCodeWrap.php';


class SPGBarCodeMetaBox
{
// post type run with barcode metabox
    private $post_type = 'product';
    private $barcode_engine = null;
    private $product = null;
    private $barcodeFormat = 'PNG';
    private $barcodeType = '';

    public function __construct($barcodeFormat = 'PNG', $barcodeType = SPG_DEFAULT_BARCODE_TYPE)
    {
        $this->barcodeFormat = $barcodeFormat;
        $this->barcodeType = $barcodeType;
        $this->barcode_engine = new BarCodeWrap();
    }

    /**
     * Init the meta box for product
     * @param $product
     */
    public function register_meta_box($post_type, $post)
    {
        if ($post_type == $this->post_type) {
            $this->product = wc_get_product($post->ID);
            if (!empty($this->product)) {
                if ($this->product->is_type('simple')) {
                    $arg['id'] = 1;
                    $arg['title'] = 'Product barcode';
                    $arg['post'] = $post;
                    $this->create_meta_box($arg);
                } else {
                    $arg['id'] = 1;
                    $arg['title'] = 'Product barcode';
                    $arg['post'] = $post;
                    $this->create_meta_box($arg);
                }
            }

        }
    }


    public function create_meta_box($arg)
    {
        add_meta_box($arg['id'], $arg['title'], array($this, 'meta_box_content'), $this->post_type, 'side', 'high', $arg['post']);
    }

    public function ajax_add_queue_print_barcode()
    {
        if (!empty($_POST['product_id']) && !empty($_POST['product_number'])) {
            if (!isset($_SESSION['barcode_print'])) {
                $_SESSION['barcode_print'] = array();
            }

            $_SESSION['barcode_print'][$_POST['product_id']] = $_POST['product_number'];
            echo json_encode(array(
                    'result' => true,
                    'data' => 'Success add to list')
            );
        } else {
            echo json_encode(array(
                    'result' => false,
                    'data' => 'Can not add to list')
            );
        }
        die;

    }

    public function meta_box_content($post)
    {
        $product = wc_get_product($post->ID);
        ?>
        <div class="spg-barcode-message"></div>
        <?php
        if (!empty($product)) {

            if ($product->is_type('variable')) {
                $variations = $product->get_available_variations();
                foreach ($variations as $item) {
                    $barcode = SPGUtil::get_product_barcode($item['variation_id']);
                    $this->render_metabox($barcode, $item['variation_id']);
                }
            } else {
                $barcode = SPGUtil::get_product_barcode($product->id);
                $this->render_metabox($barcode, $product->id);
            }

        }
    }

    /**
     * Get the name attribute for variation product
     * @param $product
     * @return array
     */
    private function get_variation_product_name($product)
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
     * Render the meta box for product
     * @param string $barcode
     * @param $product_id
     */
    private function render_metabox($barcode = '', $product_id)
    {

        // delete old file
        $tmp_barcode_file = SPG_UPLOAD_PATH . "tmp_barcode_$product_id.png";
        unlink($tmp_barcode_file);
        $product = wc_get_product($product_id);
        // name to print on  barcode
        $name = ucfirst(SPGUtil::convert_vi_to_en(SPGUtil::get_product_simple_name($product)));
        // price for print on barcode
        $price = $product->get_price();

        $arg = array('name'=>$name, 'price'=> $price);

        $this->barcode_engine->generate_barcode($tmp_barcode_file, $barcode, 90, 'horizontal', $this->barcodeType, true, $arg);
        $name = $product->get_formatted_name();
        if (!empty($barcode)):
            ?>

            <div class="product-barcode-metabox">
                <img src="<?php echo SPG_UPLOAD_URL . "tmp_barcode_$product_id.png" ?>"><br/>
                <p>Product name: <?php echo $name; ?></p>
                <!-- <input type="text" placeholder="Barcode prefix"><br/>-->
                <!--<a href="#">Create product barcode automatic</a>-->
                <div class="barcode-add-queue">
                    <input type="hidden" class="barcode-sm-product-id" name="product_id"
                           value="<?php echo $product_id ?>">
                    <div class="add-queue-controls">
                        <input type="text" min="1" max="100" class="barcode-sm-number" name="number_barcode"
                               placeholder="Barcode number">
                        <input type="button" class="button button-primary button-large barcode-sm-print"
                               value="Add to Print">
                    </div>
                </div>
            </div>


        <?php else:

            ?>
            <div class="product-barcode-metabox">
                <p>No barcode for product: <?php echo $name; ?></p>
            </div>

            <?php
        endif;

    }

}