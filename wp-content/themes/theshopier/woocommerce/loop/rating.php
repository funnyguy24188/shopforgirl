<?php
/**
 * Loop Rating
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

if ( get_option( 'woocommerce_enable_review_rating' ) === 'no' )
	return;
?>

<?php if ( $product->get_rating_html() ) : ?>
	<?php echo $product->get_rating_html(); ?>
<?php else: ?>
	<div class="no-rating_html"><?php esc_html_e('No rating', 'theshopier');?></div>
<?php endif; ?>
