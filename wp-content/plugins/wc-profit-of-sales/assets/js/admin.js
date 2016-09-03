jQuery(function(){
    jQuery('#the-list').on('click', '.editinline', function(){

        /**
         * Extract metadata and put it as the value for the custom field form
         */
        inlineEditPost.revert();

        var post_id = jQuery(this).closest('tr').attr('id');

        post_id = post_id.replace("post-", "");

        var $posr_data = jQuery('#posr_inline_' + post_id),
            $wc_data = jQuery('#woocommerce_inline_' + post_id );

        jQuery('input[name="_posr_product_cog"]', '.inline-edit-row').val($posr_data.find("#product_cog").text());


        /**
         * Only show custom field for appropriate types of products
         */
        var product_type = $wc_data.find('.product_type').text();

        if (product_type != 'grouped') {
            jQuery('.product_cog', '.inline-edit-row').show();
        } else {
            jQuery('.product_cog', '.inline-edit-row').hide();
        }

    });
});