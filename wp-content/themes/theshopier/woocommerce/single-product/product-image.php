<?php
/**
 * Single Product Image
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-image.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.6.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post, $woocommerce, $product;

?>
<div class="images">

	<?php
	if ( has_post_thumbnail() ) {
		$attachment_ids = $product->get_gallery_attachment_ids();

		$options = array(
			"items"			=> 1,
			"loop"			=> false,
			"autoHeight"		=> true,
			"video"				=> true,
			"videoHeight"		=> 300
		);

		$_class = array('p_image');
		if(count($attachment_ids) > 1 || (class_exists('Nexthemes_WooProductOptions') && Nexthemes_WooProductOptions::has_video())) {
			$_class[] = 'nth-owlCarousel loading';
		}
		printf('<div class="%1$s" data-options="%2$s">', esc_attr(implode(' ', $_class)), esc_attr(json_encode($options)));
		$i = 1;
		foreach( $attachment_ids as $attachment_id ){
			$image_link  	= wp_get_attachment_url( $attachment_id );
			$image_title 	= esc_attr( get_the_title( $attachment_id ) );
			$image       	= wp_get_attachment_image( $attachment_id, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ), 0, array(
				'title'	=> $image_title,
				'alt'	=> $image_title,
				'data-zoom-image'	=> $image_link
			) );

			echo apply_filters( 'woocommerce_single_product_image_html', sprintf( '<div data-hash="%s" class="item" style="overflow: hidden;"><a href="%s" itemprop="image" class="woocommerce-main-image zoom1" title="%s" >%s</a><a href="%s" class="zoom icon-nth-search btn_zoom" data-rel="prettyPhoto[product-gallery]"></a></div>', $i++, $image_link, $image_title, $image, $image_link ), $attachment_id );
		}

		do_action('theshopier_after_woocommerce_product_image');

		echo "</div><!--close image .nth-owlCarousel-->";

	} else {
		printf ('<a href="%s" class="zoom icon-nth-search  btn_zoom"> </a>', wc_placeholder_img_src() );
		echo apply_filters( 'woocommerce_single_product_image_html', sprintf( '<img src="%s" alt="%s" />', wc_placeholder_img_src(), esc_attr__( 'Placeholder', 'theshopier' ) ), $post->ID );

	}
	?>

	<?php do_action( 'woocommerce_product_thumbnails' ); ?>

</div>
