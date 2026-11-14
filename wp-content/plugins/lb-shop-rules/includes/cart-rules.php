<?php
/**
 * Cart rules: no mixed Merch/Courses carts; courses never need shipping.
 *
 * @package LB_Shop_Rules
 */

defined( 'ABSPATH' ) || exit;

/**
 * Customer-facing message shown when Merch and Courses would be mixed.
 *
 * @return string
 */
function lbsr_mixed_cart_message(): string {
	$message = sprintf(
		'%s <a href="%s" class="button wc-forward">%s</a>',
		esc_html__( 'Merch and Living U courses must be purchased separately. Please complete or empty your current cart first.', 'lb-shop-rules' ),
		esc_url( wc_get_cart_url() ),
		esc_html__( 'View cart', 'lb-shop-rules' )
	);

	return (string) apply_filters( 'lbsr_mixed_cart_message', $message );
}

/**
 * Block adding a product whose group differs from what is already in the cart.
 *
 * @param bool $passed     Whether validation passed so far.
 * @param int  $product_id Product being added (parent ID for variations).
 * @return bool
 */
function lbsr_block_mixed_cart_on_add( $passed, $product_id ) {
	if ( ! $passed || ! function_exists( 'WC' ) || ! WC()->cart ) {
		return $passed;
	}

	$cart_group = lbsr_get_cart_group( WC()->cart );

	if ( null !== $cart_group && lbsr_get_product_group( $product_id ) !== $cart_group ) {
		wc_add_notice( lbsr_mixed_cart_message(), 'error' );
		return false;
	}

	return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'lbsr_block_mixed_cart_on_add', 10, 2 );

/**
 * Safety net for carts that became mixed some other way (restored sessions,
 * products re-categorized while in a cart, etc.). Blocks checkout until fixed.
 */
function lbsr_validate_cart_contents() {
	if ( LBSR_GROUP_MIXED === lbsr_get_cart_group() ) {
		wc_add_notice( lbsr_mixed_cart_message(), 'error' );
	}
}
add_action( 'woocommerce_check_cart_items', 'lbsr_validate_cart_contents' );

/**
 * Courses never need shipping: treat every non-Merch product as virtual.
 *
 * Applies wherever WooCommerce checks is_virtual() (cart, checkout shipping
 * fields, orders). The "Virtual" checkbox on the product screen is not changed.
 *
 * @param bool       $is_virtual Saved "Virtual" setting.
 * @param WC_Product $product    Product.
 * @return bool
 */
function lbsr_courses_are_virtual( $is_virtual, $product ) {
	if ( $is_virtual || ! $product instanceof WC_Product ) {
		return $is_virtual;
	}

	return LBSR_GROUP_MERCH !== lbsr_get_product_group( $product );
}
add_filter( 'woocommerce_is_virtual', 'lbsr_courses_are_virtual', 10, 2 );
