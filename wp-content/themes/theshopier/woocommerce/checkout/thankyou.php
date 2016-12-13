<?php
/**
 * Thankyou page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

do_action('theshopier_shopping_progress');

if ( $order ) : ?>

	<div class="nth-row-grid">

		<div class="col-sm-24">

			<?php if ( $order->has_status( 'failed' ) ) : ?>

				<p><?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction.', 'theshopier' ); ?></p>

				<p><?php
					if ( is_user_logged_in() )
						esc_html_e( 'Please attempt your purchase again or go to your account page.', 'theshopier' );
					else
						esc_html_e( 'Please attempt your purchase again.', 'theshopier' );
					?></p>

				<p>
					<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php esc_html_e( 'Pay', 'theshopier' ) ?></a>
					<?php if ( is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php esc_html_e( 'My Account', 'theshopier' ); ?></a>
					<?php endif; ?>
				</p>

			<?php else : ?>

				<!--<p><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', esc_html__( 'Thank you. Your order has been received.', 'theshopier' ), $order ); ?></p>-->

				<ul class="order_details">
					<li class="order">
						<span class="nth-label"><?php esc_html_e( 'Order Number:', 'theshopier' ); ?></span>
						<strong><?php echo $order->get_order_number(); ?></strong>
					</li>
					<li class="date">
						<span class="nth-label"><?php esc_html_e( 'Date:', 'theshopier' ); ?></span>
						<strong><?php echo date_i18n( get_option( 'date_format' ), strtotime( $order->order_date ) ); ?></strong>
					</li>
					<li class="total">
						<span class="nth-label"><?php esc_html_e( 'Total:', 'theshopier' ); ?></span>
						<strong><?php echo $order->get_formatted_order_total(); ?></strong>
					</li>
					<?php if ( $order->payment_method_title ) : ?>
						<li class="method">
							<span class="nth-label"><?php esc_html_e( 'Payment Method:', 'theshopier' ); ?></span>
							<strong><?php echo $order->payment_method_title; ?></strong>
						</li>
					<?php endif; ?>
				</ul>
				<div class="clear"></div>

			<?php endif; ?>

			<?php do_action( 'woocommerce_thankyou_' . $order->payment_method, $order->id ); ?>

		</div>

	</div>

	<?php do_action( 'woocommerce_thankyou', $order->id ); ?>

<?php else : ?>

	<p><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', esc_html__( 'Thank you. Your order has been received.', 'theshopier' ), null ); ?></p>

<?php endif; ?>
