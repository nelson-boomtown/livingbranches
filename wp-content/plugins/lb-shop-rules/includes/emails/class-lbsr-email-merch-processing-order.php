<?php
/**
 * Merch – Processing order (customer).
 *
 * Same template and triggers as WooCommerce's "Processing order" email; the
 * router in emails.php sends exactly one of the two depending on the order type.
 *
 * @package LB_Shop_Rules
 */

defined( 'ABSPATH' ) || exit;

/**
 * Merch processing order email.
 */
class LBSR_Email_Merch_Processing_Order extends WC_Email_Customer_Processing_Order implements LBSR_Merch_Email {

	/**
	 * Constructor. Mirrors the parent's, with our own ID so settings are stored separately.
	 */
	public function __construct() {
		$this->id             = 'lbsr_merch_processing_order';
		$this->customer_email = true;
		$this->title          = __( 'Merch – Processing order', 'lb-shop-rules' );
		$this->email_group    = 'order-updates';
		$this->template_html  = 'emails/customer-processing-order.php';
		$this->template_plain = 'emails/plain/customer-processing-order.php';
		$this->placeholders   = array(
			'{order_date}'   => '',
			'{order_number}' => '',
		);

		// Same triggers as WC_Email_Customer_Processing_Order.
		foreach ( array( 'cancelled', 'failed', 'on-hold', 'pending' ) as $from ) {
			add_action( "woocommerce_order_status_{$from}_to_processing_notification", array( $this, 'trigger' ), 10, 2 );
		}

		// Skip the parent constructor (it would register settings under the core ID).
		WC_Email::__construct();

		$this->description = __( 'Sent to customers when a Merch order is received and being processed. Course orders use "Processing order".', 'lb-shop-rules' );
	}

	/**
	 * Add the "Content above product table" field.
	 */
	public function init_form_fields() {
		parent::init_form_fields();
		$this->form_fields = lbsr_add_intro_form_field(
			$this->form_fields,
			__( 'Thanks for your order! We’ve received it and it is now being prepared. Here’s a summary of what you ordered:', 'lb-shop-rules' )
		);
	}

	/**
	 * Default subject.
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return __( 'Thank you for your {site_title} merch order', 'lb-shop-rules' );
	}

	/**
	 * Default heading.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'We received your order', 'lb-shop-rules' );
	}

	/**
	 * Default closing text.
	 *
	 * @return string
	 */
	public function get_default_additional_content() {
		return __( 'Thanks again! If you have any questions about your order, please contact us at {store_email}.', 'lb-shop-rules' );
	}
}
