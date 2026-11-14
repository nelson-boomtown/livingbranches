<?php
/**
 * Catalog: hide Merch from the main Shop page.
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
