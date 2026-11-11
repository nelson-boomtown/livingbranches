<?
/*------------------------------------------------*\
	create admin menu for "LB Reporting"
\*------------------------------------------------*/
add_action('admin_menu', 'lb_reporting_page_menu');
function lb_reporting_page_menu() {
	add_submenu_page('woocommerce', 'LB Reporting', 'LB Reporting', 'manage_woocommerce', 'lb-reporting', 'lb_reporting_page_callback');
}

function lb_reporting_page_callback() {
	include(STYLESHEETPATH.'/lb-reporting-page.php');
}
