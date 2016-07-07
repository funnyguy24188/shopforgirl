<?php

include(__DIR__ .'/../lib/BarCodeWrap.php');


class BarCodeMetaBox
{
    // post type run with barcode metabox
    private $post_type = 'product';
    private $barcode_engine = null;
    private $product = null;
    private $barcodeFormat = 'PNG';
    private $barcodeType = '';

    public function __construct($barcodeFormat = 'PNG', $barcodeType = BarCodeWrap::CODE_39)
    {
        $this->barcodeFormat = $barcodeFormat;
        $this->barcodeType = $barcodeType;
        $this->barcode_engine = new BarCodeWrap();
    }

    public function init()
    {
        add_action('add_meta_boxes', array($this, 'register_meta_box'), 10, 2);
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
                    $this->create_meta_box($arg);
                } else {
                    $arg['id'] = 1;
                    $arg['title'] = 'Product barcode';
                    $this->create_meta_box($arg);
                }
            }

        }
    }


    public function create_meta_box($arg)
    {

        add_meta_box($arg['id'], $arg['title'], array($this, 'meta_box_content'),$this->post_type, 'side','high');
    }

    public function meta_box_content()
    {
        $uniq = substr(uniqid(),6);
        $wp_dir = wp_upload_dir();
        $upload_url = $wp_dir['baseurl'];
        $tmp_barcode_file = $wp_dir['basedir'] . "/tmp_barcode/tmp_barcode_$uniq.png";
        $this->barcode_engine->generate_barcode($tmp_barcode_file,'DA1718-123456', 40, 'horizontal', $this->barcodeType, true);

        echo '<img src="'. $upload_url . "/tmp_barcode/tmp_barcode_$uniq.png"  .'">';
        echo '<br/>';
        echo '<input type="text" placeholder="A Prefix for barcode">';
        echo '<br/>';
        echo '<a href="#">Create a barcode automatic</a>';
    }

}