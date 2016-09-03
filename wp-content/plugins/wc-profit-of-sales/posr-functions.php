<?php
/**********************************
 * Profit of Sales Misc Functions *
 **********************************/

if (!function_exists('POSRFront')) {
/**
 * Shortcut to POSR_Front object
 * @return POSR_Front
 */
function POSRFront()
{
    return POSR_Front::get_instance();
}
}

if (!function_exists('posr_activation')) {
/**
 * Do this when plugin is activated
 */
function posr_activation()
{
    global $wpdb;
}
}

if (!function_exists('posr_deactivation')) {
/**
 * Do this when plugin is deactivated
 */
function posr_deactivation()
{
    global $wpdb;

}
}

