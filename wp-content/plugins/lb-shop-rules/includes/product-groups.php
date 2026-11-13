<?php
/**
 * Classifies products, carts and orders as "merch" or "courses".
 *
 * A product is Merch when it belongs to the Merch product category (or any
 * of its child categories). Everything else is treated as a Living U course,
 * which matches how the store worked before Merch existed.
 *
 * @package LB_Shop_Rules
 */

defined( 'ABSPATH' ) || exit;

const LBSR_GROUP_MERCH   = 'merch';
const LBSR_GROUP_COURSES = 'courses';
const LBSR_GROUP_MIXED   = 'mixed';

/**
 * Slug of the product category that identifies Merch products.
 *
 * @return string
 */
function lbsr_merch_category_slug(): string {
	return (string) apply_filters( 'lbsr_merch_category_slug', 'merch' );
}

/**
 * Term IDs of the Merch category and all of its descendants.
 *
 * @return int[]
 */
function lbsr_merch_term_ids(): array {
	static $ids = null;

	if ( null === $ids ) {
		$ids  = array();
		$term = get_term_by( 'slug', lbsr_merch_category_slug(), 'product_cat' );

		if ( $term instanceof WP_Term ) {
			$children = get_term_children( $term->term_id, 'product_cat' );
			$ids      = array_map( 'intval', array_merge( array( $term->term_id ), is_array( $children ) ? $children : array() ) );
		}
	}

	return $ids;
}

/**
 * Group a product belongs to.
 *
 * @param int|WC_Product $product Product, variation, or their ID.
 * @return string LBSR_GROUP_MERCH or LBSR_GROUP_COURSES.
 */
function lbsr_get_product_group( $product ): string {
	static $cache = array();

	if ( ! $product instanceof WC_Product ) {
		$product = wc_get_product( absint( $product ) );
	}

	if ( ! $product ) {
		return LBSR_GROUP_COURSES;
	}

	// Categories live on the parent product, not on variations.
	$product_id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();

	if ( ! isset( $cache[ $product_id ] ) ) {
		$merch_ids            = lbsr_merch_term_ids();
		$is_merch             = $merch_ids && has_term( $merch_ids, 'product_cat', $product_id );
		$cache[ $product_id ] = $is_merch ? LBSR_GROUP_MERCH : LBSR_GROUP_COURSES;
	}

	return $cache[ $product_id ];
}

/**
 * Group of the current cart contents.
 *
 * @param WC_Cart|null $cart Cart; defaults to the session cart.
 * @return string|null LBSR_GROUP_MERCH, LBSR_GROUP_COURSES, LBSR_GROUP_MIXED, or null if empty.
 */
function lbsr_get_cart_group( $cart = null ): ?string {
	$cart = $cart instanceof WC_Cart ? $cart : ( function_exists( 'WC' ) ? WC()->cart : null );

	if ( ! $cart || $cart->is_empty() ) {
		return null;
	}

	$groups = array();
	foreach ( $cart->get_cart() as $item ) {
		$groups[ lbsr_get_product_group( $item['data'] ?? $item['product_id'] ) ] = true;
	}

	return count( $groups ) > 1 ? LBSR_GROUP_MIXED : array_key_first( $groups );
}

/**
 * Group of an order. Any Merch line item makes it a Merch order.
 *
 * @param WC_Order $order Order.
 * @return string LBSR_GROUP_MERCH or LBSR_GROUP_COURSES.
 */
function lbsr_get_order_group( WC_Order $order ): string {
	foreach ( $order->get_items() as $item ) {
		if ( $item instanceof WC_Order_Item_Product && LBSR_GROUP_MERCH === lbsr_get_product_group( $item->get_product_id() ) ) {
			return LBSR_GROUP_MERCH;
		}
	}

	return LBSR_GROUP_COURSES;
}
