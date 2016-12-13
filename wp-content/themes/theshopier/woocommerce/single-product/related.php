<?php
/**
 * Related Products
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product, $woocommerce_loop;

if ( empty( $product ) || ! $product->exists() ) {
	return;
}

$related = $product->get_related( $posts_per_page );

if ( sizeof( $related ) == 0 ) return;

$args = apply_filters( 'woocommerce_related_products_args', array(
	'post_type'            => 'product',
	'ignore_sticky_posts'  => 1,
	'no_found_rows'        => 1,
	'posts_per_page'       => $posts_per_page,
	'orderby'              => $orderby,
	'post__in'             => $related,
	'post__not_in'         => array( $product->id )
) );

$products = new WP_Query( $args );

$woocommerce_loop['columns'] = 1;

if ( $products->have_posts() ) : ?>

	<?php
	$columns = 3;
	$options = array(
		"items" => $columns,
		"responsive"	=> array(
			0	=> array(
				'items'	=> 2,
				'loop'	=> false
			),
		)
	);
	$options = Theshopier::get_owlResponsive($options);
	?>
	
	<div class="related nth-owlCarousel loading" data-options="<?php echo esc_attr(json_encode($options));?>" data-slider=".products">
		
		<h3 class="heading-title ud-line"><?php esc_html_e( 'Related Products', 'theshopier' );?></h3>
		
		<div class="row">
			
			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>
		
		</div>

	</div>
	
<?php endif;

wp_reset_postdata();
