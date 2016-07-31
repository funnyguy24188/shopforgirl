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
     * Render the meta box for product
     * @param string $barcode
     * @param $product_id
     */
    private function render_metabox($barcode = '', $product_id)
    {

        // delete old file
        $tmp_barcode_file = SPG_UPLOAD_PATH . "tmp_barcode_$product_id.png";
        unlink($tmp_barcode_file);
        $this->barcode_engine->generate_barcode($tmp_barcode_file, $barcode, 40, 'horizontal', $this->barcodeType, true);
        $product = wc_get_product($product_id);
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
                        <input type="text" min="1" max="100" class="barcode-sm-number" name="number_barcode" placeholder="Barcode number">
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