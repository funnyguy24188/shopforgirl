<?php

require_once "UrbanMethod.php";

class CustomizeShippingInit
{

    public function init_shipping()
    {
        // urban
        $urban = new UrbanMethod();
        add_action('woocommerce_shipping_init', array($urban, 'init'));
        add_filter('woocommerce_shipping_methods', array($this, 'add_urban_method_shipping'));
    }

    public function add_urban_method_shipping($methods)
    {
        $methods[] = 'UrbanMethod';
        return $methods;
    }

}