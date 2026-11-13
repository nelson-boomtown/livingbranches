<?php
/**
 * Merch – New order (admin notification).
 *
 * Same template and triggers as WooCommerce's "New order" email, with its own
 * recipients. The router in emails.php sends exactly one of the two.
 *
 * @package LB_Shop_Rules
 */

defined( 'ABSPATH' ) || exit;

/**
 * Merch new order email.
 */
class LBSR_Email_Merch_New_Order extends WC_Email_New_Order implements LBSR_Merch_Email {

	/**
	 * Constructor. Mirrors the parent's, with our own ID so settings are stored separately.
	 */
	public function __construct() {
		$this->id             = 'lbsr_merch_new_order';
		$this->title          = __( 'Merch – New order', 'lb-shop-rules' );
		$this->email_group    = 'orders';
		$this->template_html  = 'emails/admin-new-order.php';
		$this->template_plain = 'emails/plain/admin-new-order.php';
		$this->placeholders   = array(
			'{order_date}'   => '',
			'{order_number}' => '',
		);

		// Same triggers as WC_Email_New_Order.
		foreach ( array( 'pending', 'failed', 'cancelled' ) as $from ) {
			foreach ( array( 'processing', 'completed', 'on-hold' ) as $to ) {
				add_action( "woocommerce_order_status_{$from}_to_{$to}_notification", array( $this, 'trigger' ), 10, 2 );
			}
		}

		// Skip the parent constructor (it would register settings under the core ID).
		WC_Email::__construct();

		$this->description = __( 'Sent to the chosen recipient(s) when a new Merch order is received. Course orders use "New order".', 'lb-shop-rules' );
		$this->recipient   = $this->get_option( 'recipient', get_option( 'admin_email' ) );
	}

	/**
	 * Default subject.
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return __( '[{site_title}]: New Merch Order #{order_number}', 'lb-shop-rules' );
	}

	/**
	 * Default heading.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'New Merch Order: #{order_number}', 'lb-shop-rules' );
	}
}
