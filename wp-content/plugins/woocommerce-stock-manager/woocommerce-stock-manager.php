<?php
/**
 * Plugin Name:       WooCommerce Stock Manager
 * Plugin URI:        http:/toret.cz
 * Description:       WooCommerce Stock Manager
 * Version:           1.0.9
 * Author:            Vladislav Musílek
 * Author URI:        http://toret.cz
 * Text Domain:       stock-manager
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
define( 'STOCKDIR', plugin_dir_path( __FILE__ ) );
define( 'STOCKURL', plugin_dir_url( __FILE__ ) );
/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/
require_once( plugin_dir_path( __FILE__ ) . 'public/class-stock-manager.php' );

register_activation_hook( __FILE__, array( 'Stock_Manager', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Stock_Manager', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Stock_Manager', 'get_instance' ) );

if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-stock-manager-admin.php' );
	add_action( 'plugins_loaded', array( 'Stock_Manager_Admin', 'get_instance' ) );

}




 
 

  add_action( 'wp_ajax_save_one_product', 'stock_manager_save_one_product_stock_data' ); 

  /**
   * Save one product stock data 
   *
   */        
  function stock_manager_save_one_product_stock_data(){
	
    if( current_user_can('manage_woocommerce') ){

    $product_id   = sanitize_text_field($_POST['product']);

    check_ajax_referer( 'wsm-ajax-nonce-'.$product_id, 'secure' );

      $manage_stock = sanitize_text_field($_POST['manage_stock']);
      $stock_status = sanitize_text_field($_POST['stock_status']);
      $backorders   = sanitize_text_field($_POST['backorders']);
      $stock        = sanitize_text_field($_POST['stock']);
      $price        = sanitize_text_field($_POST['regular_price']);
      $weight       = sanitize_text_field($_POST['weight']);
  
      update_post_meta($product_id, '_manage_stock', $manage_stock);
      update_post_meta($product_id, '_stock_status', $stock_status);
      update_post_meta($product_id, '_backorders', $backorders);
      update_post_meta($product_id, '_stock', $stock);

      _wc_save_product_price( $product_id, $price );
      update_post_meta($product_id, '_weight', $weight);
     
    }

     exit();
  }  
  
  
  /**
   * Get WooCommerce setting for number field step
   *
   */        
  function wsm_get_step(){
      $number = get_option('woocommerce_price_num_decimals');
      if( $number == '0' ){ $step = '1'; }
      if( $number == '1' ){ $step = '0.1'; }
      if( $number == '2' ){ $step = '0.01'; }
      if( $number == '3' ){ $step = '0.001'; }
      if( $number == '4' ){ $step = '0.0001'; }
      if( $number == '5' ){ $step = '0.00001'; }
      if( $number == '6' ){ $step = '0.000001'; }
  
      return $step;
  
  }  