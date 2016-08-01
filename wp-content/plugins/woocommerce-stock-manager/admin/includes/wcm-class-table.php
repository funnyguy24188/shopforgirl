<?php
/**
 * @package   WooCommerce Stock Manager
 * @author    Vladislav Musílek
 * @license   GPL-2.0+
 * @link      http:/toret.cz
 * @copyright 2015 Toret.cz
 */

class WCM_Table {

  /**
	 * Instance of this class.
	 *
	 * @since    1.0.5
	 *
	 * @var      object
	 */
	protected static $instance = null;
  
  
	/**
	 * Constructor for the stock class.
	 *
	 * @since     1.0.5
	 */
	private function __construct() {

		
    
	}
  
  /**
	 * Return an instance of this class.
	 *
	 * @since     1.0.5
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
  
  
  /**
   * Price box
   *
   * @since     1.0.5
   */           
  public static function price_box($product_meta, $id){
  ?>
  <td>
    <input class="line-price regular_price_<?php echo $id; ?>" name="regular_price[<?php echo $id; ?>]" type="number" min="<?php echo wsm_get_step(); ?>" step="<?php echo wsm_get_step(); ?>" <?php if(!empty($product_meta['_regular_price'][0])){ echo 'value="'.$product_meta['_regular_price'][0].'"'; } ?> />
  </td>
  <?php
  }  

  /**
   * Weight box
   *
   * @since     1.0.5
   */           
  public static function weight_box($product_meta, $id){
  ?>
  <td>
    <input class="line-price weight_<?php echo $id; ?> wc_input_decimal" name="weight[<?php echo $id; ?>]" <?php if(!empty($product_meta['_weight'][0])){ echo 'value="'.$product_meta['_weight'][0].'"'; } ?> />
  </td>
  <?php
  }  
  
  
  
}//End class  