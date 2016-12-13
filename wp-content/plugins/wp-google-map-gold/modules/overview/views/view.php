
<?php
/**
 * Plugin Overviews.
 * @package Maps
 * @author Flipper Code <flippercode>
 **/

//Setup Product Overview Page
    
    $productInfo = array('productName' => __('WP Google Map Pro',WPGMP_TEXT_DOMAIN),
                        'productSlug' => 'wp-google-map-gold',
                        'productTagLine' => 'worlds most advanced google map plugin',
                        'productTextDomain' => WPGMP_TEXT_DOMAIN,
                        'productIconImage' => WPGMP_URL.'core/core-assets/images/wp-poet.png',
                        'productVersion' => WPGMP_VERSION,
                        'docURL' => 'http://www.flippercode.com/documentations/wp-google-map-gold/',
                        'demoURL' => 'http://www.flippercode.com/product/wp-google-map-pro/',
                        'productImagePath' => WPGMP_URL.'core/core-assets/product-images/',
                        'productSaleURL' => 'codecanyon.net/item/advanced-google-maps-plugin-for-wordpress/5211638',
                        'multisiteLicence' => 'http://codecanyon.net/item/advanced-google-maps-plugin-for-wordpress/5211638?license=extended&open_purchase_for_item_id=5211638&purchasable=source'
    );

    $productOverviewObj = new Flippercode_Product_Overview($productInfo);

?>

