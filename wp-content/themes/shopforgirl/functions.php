<?php
if (session_id() == '')
    session_start();
require_once 'src/bill/SPGCartGlobalManager.php';
require_once 'src/product/SPGProductMod.php';
require_once 'src/product/SPGProductDetail.php';
require_once 'src/customer/SPGCustomerDetail.php';
require_once 'src/role/SPGRoleBackEnd.php';


$product_mod = new SPGProductMod();
$product_mod->init_hook();


// order
$spg_cart = SPGCartGlobal()->get_instance();
$spg_cart->init_hook();


// customer finding
$customer_finding = new SPGCustomerDetail();
$customer_finding->init_hook();
// init_shipping


// role
$role_back_end = new SPGRoleBackEnd();
$role_back_end->init_hook();

add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );

}

add_action('wp_enqueue_scripts', function () {

    wp_enqueue_script('spgScript', get_stylesheet_directory_uri() . '/assets/js/spg_script.js', array('jquery'), '1.0');

});


add_action('admin_enqueue_scripts', function () {
    // admin script
    wp_enqueue_script('spgAdminScript', get_stylesheet_directory_uri() . '/assets/js/admin.js', array('jquery'), '1.0');

});


// remove the media from s3 when delete from localhost
add_action('delete_post', function ($post_id) {
    global $as3cf;
    $post = get_post($post_id);
    if (!empty($post) && !empty($as3cf)) {
        if ($post->post_type == 'attachment') {
            $as3cf->delete_attachment($post_id);
        }
    }
});


add_filter('woocommerce_product_tabs', 'sb_woo_remove_reviews_tab', 98);
function sb_woo_remove_reviews_tab($tabs)
{
    unset($tabs['reviews']);
    return $tabs;
}

add_action('wp_head', function () {
    global $post;
    if ($post->post_type == 'page' && $post->post_name == 'product-finding') {
        remove_action('woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30);
        remove_action('woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30);
        remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
    }
});

/**
 * Add User Role on JS for check on JS code
 */

add_action('admin_head', 'server_attach_script');
function server_attach_script()

{
    ?>
    <script type="text/javascript">
        userRoles = <?php echo (wp_get_current_user())?json_encode(array_values(wp_get_current_user()->roles)):json_encode(array() )?>;
    </script><?php
}