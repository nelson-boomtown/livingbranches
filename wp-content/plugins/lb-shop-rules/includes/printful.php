<?php
/**
 * Printful: only Merch orders are sent to Printful.
 *
 * The Printful plugin registers WooCommerce webhooks that notify Printful of
 * new orders. Course orders have nothing to fulfill, so skip those deliveries
 * (keeps Printful free of course drafts and course customer data).
 *
 * @package LB_Shop_Rules
 */

defined( 'ABSPATH' ) || exit;

/**
 * Skip Printful order webhooks for non-Merch orders.
 *
 * @param bool       $should_deliver Whether the webhook should be delivered.
 * @param WC_Webhook $webhook        Webhook being delivered.
 * @param mixed      $arg            Resource ID (the order ID for order webhooks).
 * @return bool
 */
function lbsr_skip_printful_course_orders( $should_deliver, $webhook, $arg ) {
	if ( ! $should_deliver || ! $webhook instanceof WC_Webhook ) {
		return $should_deliver;
	}

	$host = (string) wp_parse_url( $webhook->get_delivery_url(), PHP_URL_HOST );

	// Printful webhooks only ("api.printful.com" or any printful.com subdomain).
	if ( 'printful.com' !== $host && ! str_ends_with( $host, '.printful.com' ) ) {
		return $should_deliver;
	}

	// Order webhooks only: "order.*" topics or actions such as "action.woocommerce_order_status_processing".
	$topic = $webhook->get_topic();
	if ( 'order' !== $webhook->get_resource() && ! str_contains( $topic, 'order' ) ) {
		return $should_deliver;
	}

	$order = is_numeric( $arg ) ? wc_get_order( (int) $arg ) : ( $arg instanceof WC_Order ? $arg : false );

	if ( ! $order instanceof WC_Order ) {
		return $should_deliver;
	}

	return LBSR_GROUP_MERCH === lbsr_get_order_group( $order );
}
add_filter( 'woocommerce_webhook_should_deliver', 'lbsr_skip_printful_course_orders', 10, 3 );
