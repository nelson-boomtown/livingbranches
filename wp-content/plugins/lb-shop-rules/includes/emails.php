<?php
/**
 * Merch order emails, managed in WooCommerce → Settings → Emails.
 *
 * - Registers three Merch emails (Processing, Completed, New order) that reuse
 *   the core templates and triggers but have their own settings.
 * - Routes each order to exactly one email of each pair: Merch orders get the
 *   Merch emails, Course orders get the core emails (unchanged).
 * - Adds a "Content above product table" field to the customer Processing and
 *   Completed emails (core and Merch), printed by the theme's email templates.
 *
 * @package LB_Shop_Rules
 */

defined( 'ABSPATH' ) || exit;

/**
 * Marker for Merch email classes.
 */
interface LBSR_Merch_Email {}

/**
 * Core email ID => Merch email ID. Each pair shares triggers and templates.
 *
 * @return array<string, string>
 */
function lbsr_email_pairs(): array {
	return array(
		'customer_processing_order' => 'lbsr_merch_processing_order',
		'customer_completed_order'  => 'lbsr_merch_completed_order',
		'new_order'                 => 'lbsr_merch_new_order',
	);
}

/**
 * Register the Merch email classes.
 *
 * @param WC_Email[] $emails Registered emails.
 * @return WC_Email[]
 */
function lbsr_register_merch_emails( $emails ) {
	require_once LBSR_PATH . 'includes/emails/class-lbsr-email-merch-processing-order.php';
	require_once LBSR_PATH . 'includes/emails/class-lbsr-email-merch-completed-order.php';
	require_once LBSR_PATH . 'includes/emails/class-lbsr-email-merch-new-order.php';

	$emails['LBSR_Email_Merch_Processing_Order'] = new LBSR_Email_Merch_Processing_Order();
	$emails['LBSR_Email_Merch_Completed_Order']  = new LBSR_Email_Merch_Completed_Order();
	$emails['LBSR_Email_Merch_New_Order']        = new LBSR_Email_Merch_New_Order();

	return $emails;
}
add_filter( 'woocommerce_email_classes', 'lbsr_register_merch_emails' );

/**
 * Send each order only the email that matches its type.
 *
 * Runs inside WC_Email::is_enabled() at send time, so it covers automatic
 * sends and resends from the order screen (which re-fire the same triggers).
 * Without an order (e.g. the settings list), the saved setting is returned as-is.
 *
 * @param bool     $enabled Whether the email is enabled in its settings.
 * @param mixed    $order   Email object.
 * @param WC_Email $email   Email instance.
 * @return bool
 */
function lbsr_route_order_email( $enabled, $order, $email = null ) {
	if ( ! $enabled || ! $order instanceof WC_Order || ! $email instanceof WC_Email ) {
		return $enabled;
	}

	$is_merch_order = LBSR_GROUP_MERCH === lbsr_get_order_group( $order );

	return $email instanceof LBSR_Merch_Email ? $is_merch_order : ! $is_merch_order;
}
foreach ( lbsr_email_pairs() as $lbsr_core_id => $lbsr_merch_id ) {
	add_filter( "woocommerce_email_enabled_{$lbsr_core_id}", 'lbsr_route_order_email', 10, 3 );
	add_filter( "woocommerce_email_enabled_{$lbsr_merch_id}", 'lbsr_route_order_email', 10, 3 );
}
unset( $lbsr_core_id, $lbsr_merch_id );

/**
 * "Resend new order notification" (order actions) calls the core New order
 * email directly, which the router skips for Merch orders. Send the Merch one instead.
 *
 * @param WC_Order $order Order.
 * @param string   $type  Email type being resent.
 */
function lbsr_resend_merch_new_order( $order, $type ) {
	if ( 'new_order' !== $type || ! $order instanceof WC_Order || LBSR_GROUP_MERCH !== lbsr_get_order_group( $order ) ) {
		return;
	}

	$emails = WC()->mailer()->get_emails();

	if ( isset( $emails['LBSR_Email_Merch_New_Order'] ) ) {
		add_filter( 'woocommerce_new_order_email_allows_resend', '__return_true' );
		$emails['LBSR_Email_Merch_New_Order']->trigger( $order->get_id(), $order );
		remove_filter( 'woocommerce_new_order_email_allows_resend', '__return_true' );
	}
}
add_action( 'woocommerce_after_resend_order_email', 'lbsr_resend_merch_new_order', 10, 2 );

/**
 * Insert the "Content above product table" field after the email heading.
 *
 * @param array  $fields  Email form fields.
 * @param string $default Default content.
 * @return array
 */
function lbsr_add_intro_form_field( array $fields, string $default = '' ): array {
	$description = __( 'Shown below the "Hi {first name}," greeting, above the product table. Leave empty to use the WooCommerce default text. Available placeholders: {site_title}, {order_number}, {order_date}, {store_email}.', 'lb-shop-rules' );

	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) && \Automattic\WooCommerce\Utilities\FeaturesUtil::feature_is_enabled( 'block_email_editor' ) ) {
		$description .= ' ' . __( 'Note: not used while the WooCommerce block email editor is enabled.', 'lb-shop-rules' );
	}

	$field = array(
		'lbsr_intro' => array(
			'title'       => __( 'Content above product table', 'lb-shop-rules' ),
			'type'        => 'textarea',
			'description' => $description,
			'desc_tip'    => true,
			'css'         => 'width:400px; height: 90px;',
			'default'     => $default,
		),
	);

	$position = array_search( 'heading', array_keys( $fields ), true );

	return false === $position
		? $fields + $field
		: array_slice( $fields, 0, $position + 1, true ) + $field + array_slice( $fields, $position + 1, null, true );
}

/**
 * Add the intro field to the core customer emails (Merch emails add it themselves).
 *
 * @param array $fields Email form fields.
 * @return array
 */
function lbsr_add_intro_field_to_core_email( $fields ) {
	return lbsr_add_intro_form_field( (array) $fields );
}
add_filter( 'woocommerce_settings_api_form_fields_customer_processing_order', 'lbsr_add_intro_field_to_core_email' );
add_filter( 'woocommerce_settings_api_form_fields_customer_completed_order', 'lbsr_add_intro_field_to_core_email' );

/**
 * Intro HTML for the email being rendered, or '' to keep the default text.
 *
 * Called from the theme templates woocommerce/emails/customer-processing-order.php
 * and customer-completed-order.php.
 *
 * @param WC_Order|mixed $order Order (kept for template compatibility).
 * @param WC_Email|mixed $email Email being rendered.
 * @return string Sanitized HTML.
 */
function lbsr_email_intro( $order, $email ): string {
	unset( $order );

	if ( ! $email instanceof WC_Email ) {
		return '';
	}

	$text = trim( (string) $email->get_option( 'lbsr_intro', '' ) );

	return '' === $text ? '' : wp_kses_post( wpautop( wptexturize( $email->format_string( $text ) ) ) );
}
