<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'POSR_Front' ) ) :

/**
 * Profit of Sales Front-end Class
 * Class POSR_Front
 */
class POSR_Front extends POSR_Base
{
    private static $instance = null;
    protected $saved_filters;

    /**
     * Singleton
     */
    public static function get_instance()
    {
        if (!self::$instance)
            self::$instance = new POSR_Front();

        return self::$instance;
    }

    public function __construct()
    {
        parent::__construct();

        add_action( 'woocommerce_add_order_item_meta', array($this, 'save_cog_on_checkout'), 10, 2 );
    }

    /**
     * Save cost of good per order line on checkout
     * @param $item_id
     * @param $values
     */
    public function save_cog_on_checkout($item_id, $values)
    {
        $cost_of_good = ($values['variation_id'] != '') ?
                            get_post_meta($values['variation_id'], '_posr_cost_of_good', true) :
                            get_post_meta($values['product_id'], '_posr_cost_of_good', true);

        wc_add_order_item_meta( $item_id, '_posr_line_cog_total', wc_format_decimal( $cost_of_good * $values['quantity'] ) );
    }
}

endif;