<?php
/**
 * Payment rules: Merch cannot be paid with WooCommerce's offline methods
 * ("Take offline payments": bank transfer, check, cash on delivery).
 *
 * @package LB_Shop_Rules
 */

defined( 'ABSPATH' ) || exit;

/**
 * Gateway IDs that are not offered for Merch.
 *
 * @return string[]
 */
function lbsr_merch_disallowed_gateways(): array {
	return (array) apply_filters( 'lbsr_merch_disallowed_gateways', array( 'bacs', 'cheque', 'cod' ) );
}

/**
 * Whether the purchase currently being paid for contains Merch.
 *
 * Covers the cart/checkout (classic and block, via the Store API) and the
 * "Pay for order" page, where the cart is not used.
 *
 * @return bool
 */
function lbsr_paying_for_merch(): bool {
	if ( is_wc_endpoint_url( 'order-pay' ) ) {
		$order = wc_get_order( absint( get_query_var( 'order-pay' ) ) );
		return $order && LBSR_GROUP_MERCH === lbsr_get_order_group( $order );
	}

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return false;
	}

	return in_array( lbsr_get_cart_group( WC()->cart ), array( LBSR_GROUP_MERCH, LBSR_GROUP_MIXED ), true );
}

/**
 * Remove offline gateways when paying for Merch.
 *
 * @param WC_Payment_Gateway[] $gateways Available gateways keyed by ID.
 * @return WC_Payment_Gateway[]
 */
function lbsr_filter_merch_gateways( $gateways ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $gateways;
	}

	if ( ! is_array( $gateways ) || ! lbsr_paying_for_merch() ) {
		return $gateways;
	}

	return array_diff_key( $gateways, array_flip( lbsr_merch_disallowed_gateways() ) );
}
add_filter( 'woocommerce_available_payment_gateways', 'lbsr_filter_merch_gateways' );
