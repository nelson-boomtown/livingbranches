<?php
/**
 * Catalog: hide Merch from the main Shop page; hide "View All Courses" in Merch contexts.
 *
 * Merch stays visible on its category page, product pages and product search.
 * (WooCommerce's "Catalog visibility" setting can't do this: it hides products
 * from the Shop page and category pages alike.)
 *
 * @package LB_Shop_Rules
 */

defined( 'ABSPATH' ) || exit;

/**
 * Exclude Merch (and its subcategories) from the Shop page query.
 *
 * @param WP_Query $query Main product query.
 */
function lbsr_hide_merch_from_shop( $query ) {
	if ( ! is_shop() || is_search() ) {
		return;
	}

	$term_ids = lbsr_merch_term_ids();

	if ( empty( $term_ids ) ) {
		return;
	}

	$tax_query   = (array) $query->get( 'tax_query' );
	$tax_query[] = array(
		'taxonomy' => 'product_cat',
		'field'    => 'term_id',
		'terms'    => $term_ids,
		'operator' => 'NOT IN',
	);

	$query->set( 'tax_query', $tax_query );
}
add_action( 'woocommerce_product_query', 'lbsr_hide_merch_from_shop' );

/**
 * Whether the current request is a Merch context: the Merch category (or one
 * of its subcategories) or the Cart page holding Merch.
 *
 * @return bool
 */
function lbsr_is_merch_context(): bool {
	if ( is_product_category() ) {
		return in_array( (int) get_queried_object_id(), lbsr_merch_term_ids(), true );
	}

	if ( is_cart() && function_exists( 'WC' ) && WC()->cart ) {
		return LBSR_GROUP_MERCH === lbsr_get_cart_group( WC()->cart );
	}

	return false;
}

/**
 * Remove the "View All Courses" button (Courses Sidebar text widget) in Merch
 * contexts. Other buttons in the widget, like "View Shopping Cart", stay.
 *
 * Runs before shortcodes are rendered, so it strips the [us_btn] shortcode.
 *
 * @param string $text Text widget content.
 * @return string
 */
function lbsr_hide_courses_button_for_merch( $text ) {
	if ( ! is_string( $text ) || false === stripos( $text, 'View All Courses' ) || ! lbsr_is_merch_context() ) {
		return $text;
	}

	return (string) preg_replace( '/\[us_btn\b[^\]]*label="View All Courses"[^\]]*\]/i', '', $text );
}
add_filter( 'widget_text', 'lbsr_hide_courses_button_for_merch', 1 );
