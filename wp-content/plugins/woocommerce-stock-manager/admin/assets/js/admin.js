(function ( $ ) {
	"use strict";

	$(function () {

		/**
		 * Save single product line in stock table
		 *
		 */              
    jQuery('.save-product').on('click', function(){
       jQuery('.lineloader').css('display','block');
       var product = jQuery(this).data('product');
       
       
       var manage_stock  = jQuery('.manage_stock_' + product).val();
       var stock_status  = jQuery('.stock_status_' + product).val();
       var backorders    = jQuery('.backorders_' + product).val();
       var stock         = jQuery('.stock_' + product).val();
       var regular_price = jQuery('.regular_price_' + product).val();
       var weight        = jQuery('.weight_' + product).val();
       var secure        = jQuery('.wsm-ajax-nonce_' + product).val();
       
       var data = {
            action       : 'save_one_product',
            product      : product,
            manage_stock : manage_stock,
            stock_status : stock_status,
            backorders   : backorders,
            stock        : stock,
            regular_price: regular_price,
            weight       : weight,
            secure       : secure
       };
        jQuery.post(ajaxurl, data, function(response){
           
          jQuery('.lineloader').css('display','none'); 
        
        });
       
    });
    
    
    /**
     * Show variations of selected product
     *
     */ 
    jQuery('.show-variable').on('click', function(){
       var variable = jQuery(this).data('variable');
       jQuery('.variation-item-' + variable).toggleClass('show-variations');
              
    });                 
    
    
    /**
     * Navigation
     *
     */          
    jQuery('.stock-manager-navigation li span').on('click', function(){
        jQuery('.stock-manager-navigation li span').removeClass('activ');
        jQuery(this).addClass('activ');
    });
    jQuery('.stock-manager-navigation li span.navigation-filter-default').on('click', function(){
        jQuery('.filter-block').removeClass('active-filter');
        jQuery('.stock-filter').addClass('active-filter');
    });
    jQuery('.stock-manager-navigation li span.navigation-filter-by-sku').on('click', function(){
        jQuery('.filter-block').removeClass('active-filter');
        jQuery('.filter-by-sku').addClass('active-filter');
    });
    jQuery('.stock-manager-navigation li span.navigation-filter-display').on('click', function(){
        jQuery('.filter-block').removeClass('active-filter');
        jQuery('.filter-display').addClass('active-filter');
    });
    

	});

}(jQuery));