<?php

class SPGUtil
{
    public static function get_product_barcode($product_id)
    {
        return get_post_meta($product_id, '_barcode_field', true);
    }

    public static function convert_vi_to_en($str)
    {
        if (!$str) return false;
        $utf8 = array(
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ|Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'd' => 'đ|Đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ|É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'i' => 'í|ì|ỉ|ĩ|ị|Í|Ì|Ỉ|Ĩ|Ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ|Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự|Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ|Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        );
        foreach ($utf8 as $ascii => $uni) $str = preg_replace("/($uni)/i", $ascii, $str);
        return $str;
    }

    public static function get_product_simple_name($product)
    {
        if (empty($product)) {
            return '';

        }
        if ($product->is_type('variation')) {
            $variation_data = $product->get_variation_attributes();
            $attributes = $product->parent->get_attributes();
            $return = array();

            if (is_array($variation_data)) {

                foreach ($attributes as $attribute) {

                    // Only deal with attributes that are variations
                    if (!$attribute['is_variation']) {
                        continue;
                    }

                    $variation_selected_value = isset($variation_data['attribute_' . sanitize_title($attribute['name'])]) ? $variation_data['attribute_' . sanitize_title($attribute['name'])] : '';
                    $return[] = esc_html(wc_attribute_label($attribute['name']));


                    // Get terms for attribute taxonomy or value if its a custom attribute
                    if ($attribute['is_taxonomy']) {

                        $post_terms = wp_get_post_terms($product->id, $attribute['name']);

                        foreach ($post_terms as $term) {
                            if ($variation_selected_value === $term->slug) {
                                $return[] = esc_html(apply_filters('woocommerce_variation_option_name', $term->name));
                            }
                        }

                    } else {

                        $options = wc_get_text_attributes($attribute['value']);

                        foreach ($options as $option) {

                            if (sanitize_title($variation_selected_value) === $variation_selected_value) {
                                if ($variation_selected_value !== sanitize_title($option)) {
                                    continue;
                                }
                            } else {
                                if ($variation_selected_value !== $option) {
                                    continue;
                                }
                            }

                            $return[] = esc_html(apply_filters('woocommerce_variation_option_name', $option));
                        }
                    }

                }


            }
            return $product->get_title() . '-' . implode(' ', $return);
        } else {
            return $product->get_title();
        }

    }
}