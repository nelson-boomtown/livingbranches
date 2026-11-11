<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * The template for displaying the 404 page
 *
 * Do not overload this file directly. Instead have a look at templates/404.php file in us-core plugin folder:
 * you should find all the needed hooks there.
 */


/**
 * Gagandeep Bhullar	1/6/2025
 * 
 * Redirect to login page if visitor accesses the Perfmatters customer login URL,
 * but Perfmatters plugin is deactivated or not installed.
 * 
 * Contrary to Impreza's guidance that we should NOT overload this file, this functionality
 * works best when overloading this file or else we'd have to fire the code on every page.
 */
function perfmatters_login_404_redirect() {
	$perfmatters_options = get_option('perfmatters_options');
	$url_slug = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

	if ($url_slug === $perfmatters_options['login_url']) {
		wp_safe_redirect(wp_login_url());
		exit;
	}
}
perfmatters_login_404_redirect();
// endBhullar


if ( function_exists( 'us_load_template' ) ) {

	us_load_template( 'templates/404' );

} else {
	get_header();
	?>
	<main id="page-content" class="l-main">
		<section class="l-section">
			<div class="l-section-h i-cf">
				<div class="page-404 align_center">
					<?php
					$the_content = '<h1>' . us_translate( 'Page not found' ) . '</h1>';
					$the_content .= '<p>' . __( 'The link you followed may be broken, or the page may have been removed.', 'us' ) . '</p>';
					echo apply_filters( 'us_404_content', $the_content );
					?>
				</div>
			</div>
		</section>
	</main>
	<?php
	get_footer();
}
