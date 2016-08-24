<?php

require_once 'class-spg-barcode-admin-list.php';
require_once __DIR__ . '/../lib/BarCodeWrap.php';
require_once __DIR__ . '/../lib/SPGUtil.php';
require_once __DIR__ . '/../lib/TCPDF/tcpdf.php';
require_once __DIR__ . '/../lib/SPGPrinterBarcode.php';
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       shopforgirl.net
 * @since      1.0.0
 *
 * @package    Spg_Barcode
 * @subpackage Spg_Barcode/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Spg_Barcode
 * @subpackage Spg_Barcode/admin
 * @author     Le <lebaotriet@gmail.com>
 */
class Spg_Barcode_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $plugin_name The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Spg_Barcode_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Spg_Barcode_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/spg-barcode-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Spg_Barcode_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Spg_Barcode_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/spg-barcode-admin.js', array('jquery'), $this->version, false);

    }

    public function register_barcode_page()
    {
        add_menu_page('SPG Barcode', 'SPG Barcode', 'manage_options', 'spg_barcode', array($this, 'render_barcode_print_queue_page'));
        add_submenu_page('spg_barcode', 'SPG Barcode Print queue', 'SPG Barcode Print queue', 'manage_options', 'spg_barcode_queue_print', array($this, 'render_barcode_print_queue_page'));
        add_submenu_page('spg_barcode', 'SPG Product Barcode', 'SPG Product Barcode', 'manage_options', 'spg_barcode_product', array($this, 'render_barcode_product_page'));
    }

    public function render_barcode_print_queue_page()
    {
        $barcode_print = array();
        $url_full_pdf_print = '';
        if (!empty($_SESSION['barcode_print'])) {
            foreach ($_SESSION['barcode_print'] as $product_id => $number) {
                $barcode = SPGUtil::get_product_barcode($product_id);
                $product = wc_get_product($product_id);
                if (!empty($barcode)) {
                    $formatted_name = '';
                    $barcode_engine = new BarCodeWrap();
                    $barcode_file_name = "tmp_barcode_$product_id.png";
                    $tmp_barcode_file = SPG_UPLOAD_PATH . $barcode_file_name;
                    unlink($tmp_barcode_file);

                    $name = ucfirst(SPGUtil::convert_vi_to_en(SPGUtil::get_product_simple_name($product)));
                    // price for print on barcode
                    $price = $product->get_price();
                    $arg = array('name'=>$name, 'price'=> $price);

                    $barcode_engine->generate_barcode($tmp_barcode_file, $barcode, 80, 'horizontal', SPG_DEFAULT_BARCODE_TYPE, true,$arg, 1);
                    $url = SPG_UPLOAD_URL . $barcode_file_name;
                    if (!empty($product)) {
                        $formatted_name = $product->get_formatted_name();
                    }
                    $barcode_print['items'][$product_id] = array($url, $number, $formatted_name);

                }


            }

            if (!empty($barcode_print)) {


                // engine pdf
                $pdf_engine = new SPGPrinterBarcode($barcode_print);
                $full_path = $pdf_engine->get_full_path();
                // clear all item pdf first
                $files = glob($full_path . '/*'); // get all file names
                foreach($files as $file){ // iterate files
                    if(is_file($file))
                        unlink($file); // delete file
                }

                $url_full_pdf_print = $pdf_engine->print_data();
                $url_full_pdf_print = SPG_TMP_PDF_URL . $url_full_pdf_print;
            }

        }

        include 'partials/spg-barcode-print-queue-display.php';
    }

    public function clear_all_print_queue()
    {

        if (!empty($_POST['clear-all-print-queue'])) {
            $_SESSION['barcode_print'] = array();
            unset($_POST['clear-all-print-queue']);
            wp_safe_redirect(wp_get_referer());
        }
    }

    public function render_barcode_product_page()
    {
        include 'partials/spg-barcode-product-display.php';
    }


}
