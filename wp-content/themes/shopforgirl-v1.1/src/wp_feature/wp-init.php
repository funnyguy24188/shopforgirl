<?php
add_post_type_support('product', 'excerpt');

add_filter('manage_posts_columns', 'add_barcode_columns_head_product_list', 100, 2);
add_action('manage_posts_custom_column', 'add_barcode_columns_content_product_list', 10, 2);

function add_barcode_columns_head_product_list($defaults, $post_type)
{
    unset($defaults['taxonomy-product_brand']);
    unset($defaults['gadwp_stats']);
    if ($post_type == 'product') {
        $defaults['barcode_field'] = 'BC';
    }
    return $defaults;
}

function add_barcode_columns_content_product_list($column_name, $post_id)
{
    if ($column_name == 'barcode_field') {
        $barcode = get_post_meta($post_id, '_barcode_field', true);
        if ($barcode) {
            echo $barcode;
        }
    }
}
