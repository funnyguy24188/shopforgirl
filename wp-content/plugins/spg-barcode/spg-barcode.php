<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              shopforgirl.net
 * @since             1.0.0
 * @package           Spg_Barcode
 *
 * @wordpress-plugin
 * Plugin Name:       SPGBarcode
 * Plugin URI:        shopforgirl.net
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Le
 * Author URI:        shopforgirl.net
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       spg-barcode
 * Domain Path:       /languages
 */


require_once __DIR__ . '/lib/BarCodeWrap.php';
$wp_dir = wp_upload_dir();
// make directory
if (!is_dir($wp_dir['basedir'] . "/tmp_barcode")) {
    mkdir($wp_dir['basedir'] . "/tmp_barcode");
    chmod($wp_dir['basedir'] . "/tmp_barcode", 777);
}

if (!is_dir($wp_dir['basedir'] . "/tmp_pdf")) {
    mkdir($wp_dir['basedir'] . "/tmp_pdf");
    chmod($wp_dir['basedir'] . "/tmp_pdf", 777);
}


// define PATH
if (!defined('SPG_UPLOAD_PATH')) {
    define('SPG_UPLOAD_PATH', $wp_dir['basedir'] . "/tmp_barcode/");
}

if (!defined('SPG_UPLOAD_URL')) {
    define('SPG_UPLOAD_URL', $wp_dir['baseurl'] . "/tmp_barcode/");
}

// define PATH
if (!defined('SPG_TMP_PDF_PATH')) {
    define('SPG_TMP_PDF_PATH', $wp_dir['basedir'] . "/tmp_pdf/");
}

// define PATH
if (!defined('SPG_TMP_PDF_URL')) {
    define('SPG_TMP_PDF_URL', $wp_dir['baseurl'] . "/tmp_pdf/");
}

// default barcode type
if (!defined('SPG_DEFAULT_BARCODE_TYPE')) {
    define('SPG_DEFAULT_BARCODE_TYPE', BarCodeWrap::CODE_39);
}


// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-spg-barcode-activator.php
 */
function activate_spg_barcode()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-spg-barcode-activator.php';
    Spg_Barcode_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-spg-barcode-deactivator.php
 */
function deactivate_spg_barcode()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-spg-barcode-deactivator.php';
    Spg_Barcode_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_spg_barcode');
register_deactivation_hook(__FILE__, 'deactivate_spg_barcode');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-spg-barcode.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_spg_barcode()
{

    $plugin = new Spg_Barcode();
    $plugin->run();

}

run_spg_barcode();
