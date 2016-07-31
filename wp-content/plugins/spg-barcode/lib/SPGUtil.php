<?php

class SPGUtil
{
    public static function get_product_barcode($product_id) {
        return get_post_meta($product_id, '_barcode_field',true);
    }
}