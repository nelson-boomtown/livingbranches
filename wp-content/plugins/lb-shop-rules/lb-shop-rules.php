<?php
/**
 * Plugin Name:       Living Branches Shop Rules
 * Description:       Separates Printful Merch from Living U courses: hides Merch from the Shop page, prevents mixed carts, adds Merch order emails to WooCommerce email settings and sends only Merch orders to Printful.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Requires Plugins:  woocommerce
 * Author:            Boomtown
 * Text Domain:       lb-shop-rules
 * License:           GPL-2.0-or-later
 *
 * @package LB_Shop_Rules
 */

defined( 'ABSPATH' ) || exit;

define( 'LBSR_VERSION', '1.0.0' );
define( 'LBSR_PATH', plugin_dir_path( __FILE__ ) );

// Declare compatibility with WooCommerce High-Performance Order Storage.
add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

require_once LBSR_PATH . 'includes/product-groups.php';
require_once LBSR_PATH . 'includes/cart-rules.php';
require_once LBSR_PATH . 'includes/catalog.php';
require_once LBSR_PATH . 'includes/emails.php';
require_once LBSR_PATH . 'includes/printful.php';
