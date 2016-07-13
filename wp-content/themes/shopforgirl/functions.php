<?php
/*if (session_id() == '')
    session_start();
require_once 'src/SPGBarCodeMetaBox.php';
require_once 'src/bill/SPGOrder.php';
require_once 'src/product/SPGProductMod.php';
require_once 'src/product/SPGProductDetail.php';
require_once 'src/customer/SPGCustomerDetail.php';
//require_once 'src/shipping/CustomizeShippingInit.php';

$product_mod = new SPGProductMod();
$product_mod->init_hook();

$barcode_meta_box = new SPGBarCodeMetaBox();
$barcode_meta_box->init();


// order
$order = new SPGOrder();
$order->init_hook();

// product finding
$product_finding = new SPGProductDetail();
$product_finding->init_hook();

// customer finding
$customer_finding = new SPGCustomerDetail();
$customer_finding->init_hook();
// init_shipping
//$customize_shipping = new CustomizeShippingInit();
//$customize_shipping->init_shipping();
add_filter( 'woocommerce_enable_deprecated_additional_flat_rates', '__return_true' );

add_action('wp_loaded',function(){
    global $woocommerce;
});

add_action('wp_enqueue_scripts',function(){
    wp_enqueue_script('spgScript', get_stylesheet_directory_uri()  . '/assets/js/spg_script.js',array('jquery'),'1.0');
});*/

