<?php
/**
 * Single product short description
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post;

if ( ! $post->post_excerpt ) {
	return;
}

//CUSTOM
$count = apply_filters( "theshopier_woocommerce_short_description_count", -1 );

$excerpt = $post->post_excerpt;
if( $count > 0 ) {
	$excerpt = wp_strip_all_tags( $excerpt );
	$words = explode(' ', $excerpt, ($count + 1));
	if(count($words) > $count) array_pop($words);
	$excerpt = implode(' ', $words);
}

?>
<div itemprop="description">
	<?php echo apply_filters( 'woocommerce_short_description', $excerpt);?>
</div>
