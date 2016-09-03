jQuery(document).ready(function () {
    /**
     * Hide COG by JS
     */
    function hide_cog_price(roles) {
        if (typeof userRoles != 'undefined') {
            for (var i = 0; i < roles.length; i++) {
                var r = roles[i];
                if (userRoles.indexOf(r) != -1) {
                    jQuery('input[name="_posr_cost_of_good"]').closest('p').css('display', 'none');
                    jQuery('input[name*="variable_cost_of_good"]').closest('p').css('display', 'none');
                }
            }

        }
    }


    jQuery(document).on('click', '.barcode-generator', function (e) {
        e.preventDefault();
        get_barcode_auto(this);
    });

    // add barcode generator link auto matic
    // normal product
    var barcode_field = jQuery('#general_product_data').find('._barcode_field');
    add_auto_barcode_generator(barcode_field);

    // variation product
    jQuery('.variations_options').click(function () {

        jQuery(document).ajaxComplete(function () {
            jQuery('.woocommerce_variations').find('._barcode_field').each(function (k, item) {
                add_auto_barcode_generator(item);
                hide_cog_price(['staff']);
            });
            jQuery(this).unbind('ajaxComplete');
        });

    });

    function add_auto_barcode_generator(barcode_field) {
        // check next in case of no barcode generator link
        var parent = jQuery(barcode_field).closest('p');
        if (!jQuery(parent).find('.barcode-generator').length) {
            var generate_barcode = jQuery('<a>').addClass('barcode-generator')
                .attr('href', '#')
                .text('Barcode Generator');
            var bc_message = jQuery('<p>').addClass('barcode-generator-message')
                .attr('style', 'color:#0073aa');
            // post_id
            jQuery(barcode_field).parent().append(bc_message).append(generate_barcode);
        }
    }


    function get_barcode_auto(bg) {
        var barcode_field = jQuery(bg).closest('p').find('._barcode_field');
        var product_type = 'simple';
        var product_id = '';
        jQuery(jQuery(barcode_field).attr('class').split(' ')).each(function (k, item) {
            var pattern = /_barcode-field-(\w+)/g;
            var ret = pattern.exec(item);
            if (ret != null) {
                if (ret[1] == 'simple' || ret[1] == 'variation') {
                    product_type = ret[1];
                } else {
                    product_id = ret[1];
                }

            }

        });

        jQuery.ajax({
            method: 'POST',
            dataType: 'json',
            url: window.location.origin + '/wp-admin/admin-ajax.php',
            data: {action: 'get_barcode_auto', product_id: product_id, product_type: product_type},
            success: function (rep) {
                if (rep.result) {
                    var bc = rep.data.barcode;
                    jQuery(barcode_field).val(bc);

                }
                // add message
                jQuery(bg).closest('p').find('.barcode-generator-message').text(rep.message);
            }
        });
    }

    // hide cog price for user roles
    hide_cog_price(['staff']);

});