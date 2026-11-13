<?php
/**
 * Merch – Completed order (customer).
 *
 * Same template and trigger as WooCommerce's "Completed order" email; the
 * router in emails.php sends exactly one of the two depending on the order type.
 *
 * @package LB_Shop_Rules
 */

defined( 'ABSPATH' ) || exit;

/**
 * Merch completed order email.
 */
class LBSR_Email_Merch_Completed_Order extends WC_Email_Customer_Completed_Order implements LBSR_Merch_Email {

	/**
	 * Constructor. Mirrors the parent's, with our own ID so settings are stored separately.
	 */
	public function __construct() {
		$this->id             = 'lbsr_merch_completed_order';
		$this->customer_email = true;
		$this->title          = __( 'Merch – Completed order', 'lb-shop-rules' );
		$this->email_group    = 'order-updates';
		$this->template_html  = 'emails/customer-completed-order.php';
		$this->template_plain = 'emails/plain/customer-completed-order.php';
		$this->placeholders   = array(
			'{order_date}'   => '',
			'{order_number}' => '',
		);

		// Same trigger as WC_Email_Customer_Completed_Order.
		add_action( 'woocommerce_order_status_completed_notification', array( $this, 'trigger' ), 10, 2 );

		// Skip the parent constructor (it would register settings under the core ID).
		WC_Email::__construct();

		$this->description = __( 'Sent to customers when a Merch order is marked completed. Course orders use "Completed order".', 'lb-shop-rules' );
	}

	/**
	 * Add the "Content above product table" field.
	 */
	public function init_form_fields() {
		parent::init_form_fields();
		$this->form_fields = lbsr_add_intro_form_field(
			$this->form_fields,
			__( 'Good news — your order has been fulfilled. Here’s a summary of what you ordered:', 'lb-shop-rules' )
		);
	}

	/**
	 * Default subject.
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return __( 'Your {site_title} merch order is complete', 'lb-shop-rules' );
	}

	/**
	 * Default heading.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'Your order is complete', 'lb-shop-rules' );
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
