<?php
/**
 * Required "Accept Terms & Conditions" checkbox on the classic checkout,
 * shown only when the cart contains Merch. It replaces WooCommerce's native
 * terms checkbox for Merch; other carts keep the native one (if enabled).
 *
 * The checkbox text is editable under
 * WooCommerce → Settings → Advanced → Checkout terms.
 *
 * @package LB_Shop_Rules
 */

defined( 'ABSPATH' ) || exit;

define( 'LBSR_TERMS_TEXT_OPTION', 'lbsr_terms_checkbox_text' );

/**
 * Default checkbox text.
 *
 * @return string
 */
function lbsr_terms_default_text(): string {
	return __( 'I have read and accept the Terms & Conditions', 'lb-shop-rules' );
}

/**
 * Add a "Checkout terms" section to WooCommerce → Settings → Advanced.
 *
 * @param array $sections Section labels keyed by ID.
 * @return array
 */
function lbsr_terms_add_settings_section( $sections ) {
	$sections['lbsr_terms'] = __( 'Checkout terms', 'lb-shop-rules' );
	return $sections;
}
add_filter( 'woocommerce_get_sections_advanced', 'lbsr_terms_add_settings_section' );

/**
 * Settings fields for the "Checkout terms" section.
 *
 * @param array  $settings        Settings for the current section.
 * @param string $current_section Current section ID.
 * @return array
 */
function lbsr_terms_settings( $settings, $current_section ) {
	if ( 'lbsr_terms' !== $current_section ) {
		return $settings;
	}

	return array(
		array(
			'id'    => 'lbsr_terms_options',
			'type'  => 'title',
			'title' => __( 'Checkout terms', 'lb-shop-rules' ),
			'desc'  => __( 'A required checkbox shown above the Place order button.', 'lb-shop-rules' ),
		),
		array(
			'id'          => LBSR_TERMS_TEXT_OPTION,
			'type'        => 'textarea',
			'title'       => __( 'Checkbox text', 'lb-shop-rules' ),
			'desc_tip'    => __( 'Basic HTML is allowed, so you can add a link to your terms page.', 'lb-shop-rules' ),
			'placeholder' => lbsr_terms_default_text(),
			'default'     => lbsr_terms_default_text(),
			'css'         => 'min-width:300px;height:75px;',
			'autoload'    => false,
		),
		array(
			'id'   => 'lbsr_terms_options',
			'type' => 'sectionend',
		),
	);
}
add_filter( 'woocommerce_get_settings_advanced', 'lbsr_terms_settings', 10, 2 );

/**
 * Checkbox label from the saved setting.
 *
 * @return string Sanitized HTML.
 */
function lbsr_terms_checkbox_label(): string {
	$text = trim( (string) get_option( LBSR_TERMS_TEXT_OPTION, '' ) );

	return wp_kses_post( '' === $text ? lbsr_terms_default_text() : $text );
}

/**
 * Whether the cart being checked out contains Merch.
 *
 * @return bool
 */
function lbsr_terms_cart_has_merch(): bool {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return false;
	}

	return in_array( lbsr_get_cart_group( WC()->cart ), array( LBSR_GROUP_MERCH, LBSR_GROUP_MIXED ), true );
}

/**
 * Hide WooCommerce's native terms checkbox when the custom one is shown.
 *
 * The "Pay for order" page has no custom checkbox, so it keeps the native one.
 *
 * @param bool $show Whether to show the native terms checkbox.
 * @return bool
 */
function lbsr_terms_hide_native_checkbox( $show ) {
	if ( is_wc_endpoint_url( 'order-pay' ) ) {
		return $show;
	}

	return lbsr_terms_cart_has_merch() ? false : $show;
}
add_filter( 'woocommerce_checkout_show_terms', 'lbsr_terms_hide_native_checkbox' );

/**
 * Render the checkbox above the Place order button (Merch carts only).
 */
function lbsr_terms_render_checkbox() {
	if ( ! lbsr_terms_cart_has_merch() ) {
		return;
	}

	woocommerce_form_field(
		'lbsr_accept_terms',
		array(
			'type'     => 'checkbox',
			'class'    => array( 'form-row', 'validate-required' ),
			'label'    => lbsr_terms_checkbox_label(),
			'required' => true,
		),
		WC()->checkout()->get_value( 'lbsr_accept_terms' )
	);
}
add_action( 'woocommerce_review_order_before_submit', 'lbsr_terms_render_checkbox' );

/**
 * Block the order if the checkbox isn't ticked.
 *
 * The error carries the checkbox's field ID, like WooCommerce's own
 * required-field errors, so the checkout script styles it the same way,
 * links it to the checkbox and shows it inline under the field.
 *
 * @param array    $data   Posted checkout data.
 * @param WP_Error $errors Validation errors.
 */
function lbsr_terms_validate( $data, $errors ) {
	if ( ! lbsr_terms_cart_has_merch() ) {
		return;
	}

	// Nonce is verified by WooCommerce before this hook runs.
	if ( empty( $_POST['lbsr_accept_terms'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$errors->add(
			'lbsr_accept_terms_required',
			__( 'Please accept the Terms & Conditions to continue.', 'lb-shop-rules' ),
			array( 'id' => 'lbsr_accept_terms' )
		);
	}
}
add_action( 'woocommerce_after_checkout_validation', 'lbsr_terms_validate', 10, 2 );

/**
 * Record when the terms were accepted on the order.
 *
 * @param WC_Order $order Order being created.
 */
function lbsr_terms_record_acceptance( $order ) {
	if ( ! lbsr_terms_cart_has_merch() ) {
		return;
	}

	$order->update_meta_data( '_lbsr_terms_accepted', current_time( 'mysql' ) );
}
add_action( 'woocommerce_checkout_create_order', 'lbsr_terms_record_acceptance' );
