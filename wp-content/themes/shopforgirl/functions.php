<?php
if (session_id() == '')
    session_start();
require_once 'src/bill/SPGCartGlobalManager.php';
require_once 'src/product/SPGProductMod.php';
require_once 'src/product/SPGProductDetail.php';
require_once 'src/customer/SPGCustomerDetail.php';




$product_mod = new SPGProductMod();
$product_mod->init_hook();


// order
$spg_cart = SPGCartGlobal()->get_instance();
$spg_cart->init_hook();


// customer finding
$customer_finding = new SPGCustomerDetail();
$customer_finding->init_hook();
// init_shipping


add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('spgScript', get_stylesheet_directory_uri() . '/assets/js/spg_script.js', array('jquery'), '1.0');
});

// remove the media from s3 when delete from localhost
add_action('delete_post',function ($post_id){
    global $as3cf;
    $post = get_post($post_id);
    if(!empty($post) && !empty($as3cf)) {
        if($post->post_type == 'attachment') {
            $as3cf->delete_attachment($post_id);
        }
    }
});
