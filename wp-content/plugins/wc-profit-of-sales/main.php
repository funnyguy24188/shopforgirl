<?php
/*
Plugin Name: WooCommerce Profit of Sales Report
Plugin URI: http://indowebkreasi.com/posr
Description: This plugin provides Profit of Sales Report based on Cost of Goods
Author: IndoWebKreasi
Version: 1.3.0
Author URI: http://indowebkreasi.com
License: Commercial
*/

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// check if WooCommerce active
if (
    !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) &&
    !in_array( 'woocommerce/woocommerce.php', array_keys( (array) get_site_option('active_sitewide_plugins') ) )
)
    return;

define('POSR_PATH', plugin_dir_path(__FILE__));

require POSR_PATH . 'posr-functions.php';
require POSR_PATH . 'class-posr-base.php';
require POSR_PATH . 'class-posr-admin.php';
require POSR_PATH . 'class-posr-front.php';

//register_activation_hook(__FILE__, 'posr_activation');
//register_deactivation_hook(__FILE__, 'posr_deactivation');

//add_filter('woocommerce_get_settings_pages', 'posr_add_settings');

if (is_admin()) {
    new POSR_Admin();
}

POSRFront();