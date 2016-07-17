<?php
if (session_id() == '')
    session_start();
require_once 'src/SPGBarCodeMetaBox.php';
require_once 'src/bill/SPGCartGlobalManager.php';
require_once 'src/product/SPGProductMod.php';
require_once 'src/product/SPGProductDetail.php';
require_once 'src/customer/SPGCustomerDetail.php';

$product_mod = new SPGProductMod();
$product_mod->init_hook();

$barcode_meta_box = new SPGBarCodeMetaBox();
$barcode_meta_box->init();


// order
$spg_cart = SPGCartGlobal()->get_instance();
$spg_cart->init_hook();


// customer finding
$customer_finding = new SPGCustomerDetail();
$customer_finding->init_hook();
// init_shipping
//$customize_shipping = new CustomizeShippingInit();
//$customize_shipping->init_shipping();


add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('spgScript', get_stylesheet_directory_uri() . '/assets/js/spg_script.js', array('jquery'), '1.0');
});

