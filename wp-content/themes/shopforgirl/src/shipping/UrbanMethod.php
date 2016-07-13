<?php

class UrbanMethod extends WC_Shipping_Method
{

    private $default_cost = 20000;

    /**
     * Constructor for UrbanMethod class
     *
     * @access public
     * @return void
     */
    public function __construct($instance_id = 0)
    {
        $this->id = 'urban_method';
        $this->instance_id = absint($instance_id);
        $this->title = __('Ship nội thành');
        $this->method_description = __('Description of your shipping method'); //
        $this->enabled = "yes"; // This can be added as an setting but for this example its forced enabled
        $this->supports = array(
            'shipping-zones',
            'instance-settings',
            'instance-settings-modal',
        );
        $this->init();
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    /**
     * Init your settings
     *
     * @access public
     * @return void
     */
    function init()
    {
        // Load the settings API
        $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
        $this->init_settings(); // This is part of the settings API. Loads settings you previously init.

        // Save settings in admin if you have any defined
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    /**
     * calculate_shipping function.
     *
     * @access public
     * @param mixed $package
     * @return void
     */
    public function calculate_shipping($package = array())
    {

        $rate = array(
            'id' => $this->id,
            'label' => $this->title,
            'cost' => $this->default_cost,
            'tax' => false,
        );

        // Register the rate
        $this->add_rate($rate);
    }
}

