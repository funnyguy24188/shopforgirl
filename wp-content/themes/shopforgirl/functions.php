<?php
if (session_id() == '')
    session_start();
require_once 'src/bill/SPGCartGlobalManager.php';
require_once 'src/product/SPGProductMod.php';
require_once 'src/product/SPGProductDetail.php';
require_once 'src/customer/SPGCustomerDetail.php';
require_once 'src/role/SPGRoleBackEnd.php';
require_once 'src/order/SPGOrder.php';
require_once 'src/widget/register-init.php';
require_once 'src/wp_feature/wp-init.php';


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

// order
$order = new SPGOrder();
$order->init_hook();


add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');
function my_theme_enqueue_styles()
{
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('bootstrap', get_stylesheet_directory_uri() . '/assets/css/bootstrap.min.css', array(), '1.0');

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
        userRoles = <?php echo (wp_get_current_user()) ? json_encode(array_values(wp_get_current_user()->roles)) : json_encode(array())?>;
    </script><?php
}

/**
 * Remove login link after user has been login
 */
function exclude_login_link_menu_item($items, $menu, $args)
{
    // Iterate over the items to search and destroy
    foreach ($items as $key => $item) {
        if (is_user_logged_in()) {
            if ($item->object_id == 247) unset($items[$key]);
        }
    }

    return $items;
}

/**
 * Add logout item to  main menu
 * @param $items
 * @param $menu
 * @return string
 */
function add_logout_link_menu_item($items, $menu)
{
    // Iterate over the items to search and destroy
    if ($menu->menu->slug == 'main-menu') {
        if (is_user_logged_in()) {
            $items .= '<li class="menu-linh"><a href="' . wp_logout_url('/') . '"><span>Logout</span></a></li>';
        }
    }

    return $items;
}

/**
 * Redirect custom login page when user go to wp-admin default login page
 */
function redirect_login_page()
{
    $login_page = home_url('/login');
    $page_viewed = basename($_SERVER['REQUEST_URI']);

    if ($page_viewed == "wp-login.php" && $_SERVER['REQUEST_METHOD'] == 'GET') {
        wp_safe_redirect($login_page);
        exit;
    }
}

/* Kiểm tra lỗi đăng nhập */
function login_failed()
{
    $login_page = home_url('/login');
    wp_safe_redirect($login_page . '?login=failed');
    exit;
}

add_action('wp_login_failed', 'login_failed');

function verify_username_password($user, $username, $password)
{
    $login_page = home_url('/login');
    if ($username == "" || $password == "") {
        wp_safe_redirect($login_page . "?login=empty");
        exit;
    }
}

/**
 * Join with post meta to query by barcode field on Product list backend
 * @param string $join
 * @return string
 */
function join_woo_product_list_search_by_barcode($join = '')
{
    global $wp_query, $wpdb;
    // escape if not woocommerce searcg query
    if (empty($wp_query->query_vars['s'])) {
        return $join;
    }

    $join .= " INNER JOIN {$wpdb->prefix}postmeta AS c_postmeta ON (wp_posts.ID = c_postmeta.post_id)";
    return $join;
}

function where_woo_product_list_search_by_barcode($where = '')
{

    global $wp_query, $wpdb;
    // escape if not woocommerce searcg query
    if (empty($wp_query->query_vars['s'])) {
        return $where;
    }

    // prequery first

    $barcode = $wp_query->query_vars['s'];

    $sql = "SELECT ID, post_parent, post_type
            FROM {$wpdb->prefix}posts 
            INNER JOIN  {$wpdb->prefix}postmeta ON {$wpdb->prefix}postmeta.post_id = {$wpdb->prefix}posts.ID             
            WHERE  {$wpdb->prefix}postmeta.meta_key = '_barcode_field' AND {$wpdb->prefix}postmeta.meta_value = '{$barcode}'";

    $result = $wpdb->get_results($sql, ARRAY_A);
    $post_parents = array();
    if (!empty($result)) {
        foreach ($result as $item) {
            if ($item['post_type'] == 'product_variation') {
                if (!in_array($item['post_parent'], $post_parents)) {
                    $post_parents[] = "'" . $item['post_parent'] . "'";
                }

            }
        }
    }
    $where .= " OR ( c_postmeta.meta_key = '_barcode_field' AND c_postmeta.meta_value = '{$barcode}')";
    // incase product is variation product we get the parent product
    if (!empty($post_parents)) {
        $post_parents = implode(',', $post_parents);
        $where .= " OR {$wpdb->prefix}posts.ID IN  ( $post_parents )  ";
    }
    return $where;
}

function distinct_woo_product_list_search_by_barcode($distinct)
{
    return 'DISTINCT';
}

add_filter('authenticate', 'verify_username_password', 1, 3);
add_action('init', 'redirect_login_page');
add_filter('wp_get_nav_menu_items', 'exclude_login_link_menu_item', 10, 3);
add_filter('wp_nav_menu_items', 'add_logout_link_menu_item', 10, 2);

add_action('pre_get_posts', function ($query) {
    $post_type = !empty(get_query_var('post_type')) ? get_query_var('post_type') : '';

    if (is_search() && is_admin() && $post_type == 'product') {
        add_filter('posts_join', 'join_woo_product_list_search_by_barcode');
        add_filter('posts_where', 'where_woo_product_list_search_by_barcode');
        add_filter('posts_distinct', 'distinct_woo_product_list_search_by_barcode');
    }

});


