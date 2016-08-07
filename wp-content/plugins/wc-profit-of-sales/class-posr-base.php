<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'POSR_Base' ) ) :

/**
 * Profit of Sales Base Class
 * Class BARE_Base
 */
class POSR_Base
{
    public $default_params;
    const TEXT_DOMAIN = 'wc-posr';

    public function __construct()
    {
        //$this->set_default_params();

        add_action('init', array($this, 'init'));
        //add_action('woocommerce_loaded', array($this, 'woo_loaded'));
    }

    /**
     * Set default parameters from options
     */
    protected function set_default_params()
    {
        global $posr_config;

        $this->default_params = array(
            //'enabled' => get_option('posr_enabled', 'no'),
        );
    }

    /**
     * Do everything needed on initialization
     */
    public function init()
    {
        load_plugin_textdomain(self::TEXT_DOMAIN, false, basename(POSR_PATH) . '/languages/');

        if ( version_compare( WOOCOMMERCE_VERSION, "2.3.0" ) < 0 && !function_exists('wc_get_price_decimals') ) {
            function wc_get_price_decimals()
            {
                return absint(get_option('woocommerce_price_num_decimals', 2));
            }
        }
    }

}

endif; // if class_exists