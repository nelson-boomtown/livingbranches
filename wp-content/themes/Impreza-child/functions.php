<?php
/*------------------------------------------------*\
	define custom variables/constants
\*------------------------------------------------*/
//define('TEMPLATEDIR',	get_bloginfo('stylesheet_directory'));
//define('SITENAME',		get_bloginfo('name'));


/*------------------------------------------------*\
	config
\*------------------------------------------------*/
add_filter('wp_is_application_passwords_available', '__return_false');	// disable application passwords
add_filter('user_contactmethods', function(){return array();}, 99);		// remove yoast seo social fields


/*------------------------------------------------*\
	living u reporting
\*------------------------------------------------*/
include(STYLESHEETPATH.'/lb-reporting.php');


/*------------------------------------------------*\
	custom scripts for <head>
\*------------------------------------------------*/
/*add_action('wp_head', 'my_custom_head');
function my_custom_head() {
?>
	<!-- Google Tag Manager JS -->
	<script>
		(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','GTM-MS6Z492');
	</script>
<?
} // function my_custom_head*/


/*------------------------------------------------*\
	add gtm noscript after opening <body>
\*------------------------------------------------*/
add_action('us_before_canvas', 'gtm_noscript');
function gtm_noscript() {
?>
	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MS6Z492" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<?
} // function gtm_noscript


/*--------------------------------------------------------------------------------------------------*\
	remove some of the parent theme features such as custom post types and other elements that we
	want to override in the child theme. also remove stupid emoji scripts and styles
\*--------------------------------------------------------------------------------------------------*/
add_action('init', 'do_my_theme_init');
function do_my_theme_init() {

	// register "lb_floorplan" custom post type
	$arrLabels = array(
		'name'			=> 'Floor Plans',
		'singular_name'	=> 'Floor Plan',
		'all_items'		=> 'All Floor Plans',
		'add_new_item'	=> 'Add New Floor Plan',
		'edit_item'		=> 'Edit Floor Plan',
		'view_item'		=> 'View Floor Plan',
		'search_items'	=> 'Search Floor Plans',
		'not_found'		=> 'No Floor Plans found'
	);
	$arrArgs = array(
		'labels'				=> $arrLabels,
		'public'				=> true,
		'exclude_from_search'	=> true,
		'publicly_queryable'	=> false,
		'supports'				=> array('title', 'thumbnail'),
		'rewrite'				=> false,
		'has_archive'			=> false,
		'hierarchical'			=> true,
		'menu_icon'				=> 'dashicons-grid-view'
	);
	register_post_type('lb_floorplan', $arrArgs);

	// register "lb_floorplan_community" taxonomy
	$arrLabels = array(
		'name'				=> 'Community', 'taxonomy general name',
		'singular_name'		=> 'Community', 'taxonomy singular name',
		'search_items'		=> 'Search Communities',
		'all_items'			=> 'All Communities',
		'parent_item'		=> 'Parent Community',
		'parent_item_colon'	=> 'Parent Community:',
		'edit_item'			=> 'Edit Community', 
		'update_item'		=> 'Update Community',
		'add_new_item'		=> 'Add New Community',
		'new_item_name'		=> 'New Community',
		'menu_name'			=> 'Communities'
	);
	$arrArgs = array(
		'hierarchical'		=> true,
		'labels'			=> $arrLabels,
		'show_ui'			=> true,
		'show_admin_column'	=> true,
		'query_var'			=> true,
		'rewrite'			=> false
	);
	register_taxonomy('lb_floorplan_community', array('lb_floorplan'), $arrArgs);

	// register "lb_floorplan_building" taxonomy
	$arrLabels = array(
		'name'				=> 'Building Type', 'taxonomy general name',
		'singular_name'		=> 'Building Type', 'taxonomy singular name',
		'search_items'		=> 'Search Building Types',
		'all_items'			=> 'All Building Types',
		'parent_item'		=> 'Parent Building Type',
		'parent_item_colon'	=> 'Parent Building Type:',
		'edit_item'			=> 'Edit Building Type', 
		'update_item'		=> 'Update Building Type',
		'add_new_item'		=> 'Add New Building Type',
		'new_item_name'		=> 'New Building Type',
		'menu_name'			=> 'Building Types'
	);
	$arrArgs = array(
		'hierarchical'		=> true,
		'labels'			=> $arrLabels,
		'show_ui'			=> true,
		'show_admin_column'	=> true,
		'query_var'			=> true,
		'rewrite'			=> false
	);
	register_taxonomy('lb_floorplan_building', array('lb_floorplan'), $arrArgs);

	// register "lb_floorplan_care" taxonomy
	$arrLabels = array(
		'name'				=> 'Care Type', 'taxonomy general name',
		'singular_name'		=> 'Care Type', 'taxonomy singular name',
		'search_items'		=> 'Search Care Types',
		'all_items'			=> 'All Care Types',
		'parent_item'		=> 'Parent Care Type',
		'parent_item_colon'	=> 'Parent Care Type:',
		'edit_item'			=> 'Edit Care Type', 
		'update_item'		=> 'Update Care Type',
		'add_new_item'		=> 'Add New Care Type',
		'new_item_name'		=> 'New Care Type',
		'menu_name'			=> 'Care Types'
	);
	$arrArgs = array(
		'hierarchical'		=> true,
		'labels'			=> $arrLabels,
		'show_ui'			=> true,
		'show_admin_column'	=> true,
		'query_var'			=> true,
		'rewrite'			=> false
	);
	register_taxonomy('lb_floorplan_care', array('lb_floorplan'), $arrArgs);

} // function do_my_theme_init


/*------------------------------------------------*\
	insert the current year
\*------------------------------------------------*/
add_shortcode('year', 'shortcode_year');
function shortcode_year() {
	return date('Y');
} // function shortcode_year


/*------------------------------------------------*\
	insert the pricing link for floor plan
\*------------------------------------------------*/
add_shortcode('floorplan_pricing', 'shortcode_floorplan_pricing');
function shortcode_floorplan_pricing() {
	global $post;
	//$arrTerms		= get_the_terms($post->ID, 'lb_floorplan_community');
	//$sPricingPage	= get_field('floor_plan_pricing', $arrTerms[0]);
	//$sPricingPage	= get_field('floor_plan_pricing', $post->ID);
	$sPricingPage	= "https://livingbranches.org/pricing/";
	return do_shortcode('[us_btn label="Pricing" link="url:'.urlencode($sPricingPage).'||" style="11" icon="fas|tag"]');
} // function shortcode_floorplan_pricing


/*------------------------------------------------*\
	insert the pricing link for floor plan
\*------------------------------------------------*/
add_shortcode('floorplan_tour', 'shortcode_floorplan_tour');
function shortcode_floorplan_tour() {
	return '';
} // function shortcode_floorplan_tour


/*--------------------------------------------------------------------------------------------------*\
	dequeue certain plugin scripts on pages where the plugin is not in use, based on shortcode
\*--------------------------------------------------------------------------------------------------*/
add_action('wp_enqueue_scripts', 'boomtown_dequeue_scripts');
function boomtown_dequeue_scripts() {
	global $post;
	if (is_a($post, 'WP_Post')):
		// CF7
		if (!has_shortcode($post->post_content, 'contact-form-7')):
			wp_dequeue_style('contact-form-7');
			wp_dequeue_script('contact-form-7');
			wp_dequeue_script('wpcf7-redirect-script');
			wp_dequeue_script('google-recaptcha');
		endif;
	endif;
} // function boomtown_dequeue_scripts


/*--------------------------------------------------------------------------------------------------*\
	the events calendar: for recurring events, only display first (parent) occurrence in dashboard
	events listing
	from: https://theeventscalendar.com/knowledgebase/k/hide-recurring-event-instances-in-the-admin/
\*--------------------------------------------------------------------------------------------------*/
new Events_Admin_List__Remove_Child_Events;
class Events_Admin_List__Remove_Child_Events {
	public function __construct() {
		// Don't kick in unless we're on the edit.php screen
		add_action('load-edit.php', array($this, 'setup'));
	}
 
	public function setup() {
		// Listen out for the main events query
		if ('tribe_events' === $GLOBALS['typenow']):
			add_action('parse_query', array($this, 'modify'));
		endif;
	}
 
	function modify(WP_Query $query) {
		// Run once, only for the main query
		if (! $query->is_main_query()):
			return;
		endif;
		remove_action('parse_query', array($this, 'modify'));
 
		// Only return top level posts as a means of ignoring child posts
		$query->set('post_parent', 0);
	}
} // class Events_Admin_List__Remove_Child_Events


// Apply now button shortcode
function apply_now_button() {   	
  	if (get_field("in-house_form")) {
		$ap_id = get_field("in-house_form");
		$slug = get_post_field('post_name', $ap_id);
		/*$aplink = get_home_url().'/contact/'.$slug.'/';	*/
		$aplink = get_permalink($ap_id);
		$aplink = do_shortcode('[us_btn label="Apply Now" link="url:'.$aplink.'|target:_blank"]');
  	} else { 
  		$apbutton = get_field("external_application"); 
  		$aplink = do_shortcode('[us_btn label="Apply Now" link="url:'.$apbutton.'|target:_blank"]');
  	}  	
	return $aplink;
} 
// register shortcode
add_shortcode('apply_now', 'apply_now_button');


/**
 * Remove living-u category from Events page
 */
function tribe_exclude_events_category($wp_query) {
	
	if (!defined('Tribe__Events__Main::TAXONOMY')){
		return $wp_query;
	}
	if (!class_exists('Tribe__Events__Main')) {
		return $wp_query;
	}

	
	
// only filter the query if the user cannot edit pages (< editor)
	if (!current_user_can('edit_pages')) {
		$exclude_cats = array('living-u'); 

		// Join with current tax query if set
		if (is_array($wp_query->get('tax_query')))
			{$tax_query =$wp_query->get('tax_query');}
		else
			{$tax_query = array();}

		// Setup an exclude from the tribe_events_cat taxonomy
		$tax_query[] = array(
			'taxonomy'  => Tribe__Events__Main::TAXONOMY,   // sidebar ist: "tribe_events_cat"
			'field'     => 'slug',
			'terms'     => $exclude_cats,
			'operator'  => 'NOT IN'
		);

		if (!is_single() && !is_tax()) {
			$wp_query->set('tax_query', $tax_query);
		}
	}
	return $wp_query;
}
	
add_action('pre_get_posts', 'tribe_exclude_events_category', 100, 1);



/*------------------------------------------------*\
	configure userway accessibility plugin/widget
\*------------------------------------------------*/
add_action('wp_footer','btig_custom_scripts');
function btig_custom_scripts() {
?>
	<script type="text/javascript">
		var _userway_config = {
			<?if (0):?>
				/* uncomment the following line to override default position*/
				/* position: '3', */
				/* uncomment the following line to override default size (values: small, large)*/
				/* size: 'large', */
				/* uncomment the following line to override default language (e.g., fr, de, es, he, nl, etc.)*/
				/* language: 'null', */
				/* uncomment the following line to override color set via widget (e.g., #053f67)*/
				/* color: '#2d68ff', */
				/* uncomment the following line to override type set via widget (1=person, 2=chair, 3=eye, 4=text)*/
				/* type: '1', */
				/* uncomment the following lines to override the accessibility statement*/
				/* statement_text: "Our Accessibility Statement", */
				/* statement_url: "www.anserve.com/accessiblity", */
				/* uncomment the following line to override support on mobile devices*/
				/* mobile: true, */
			<?endif;?>
			size: 'small',
			position: '2',
			//statement_text: "Anserve's Accessibility Statement",
			//statement_url: "https://www.anserve.com/accessibility/",
			account: 'lwWmXyiFuO'
		};
	</script>
	<script type="text/javascript" src="https://cdn.userway.org/widget.js"></script>
<?php
} // function btig_custom_scripts


/**
 * Modify the number of words on the event excerpt
 *
 */
add_filter('excerpt_length', 'tec_custom_excerpt_length', 999);
function tec_custom_excerpt_length($words) {
	$words = 500; // change this value to set the number of words
	return $words;
}



/**
 * Add schema to various pages
 * @link https://developers.google.com/search/docs/data-types/local-businesses
 * @link https://schema.org/LocalBusiness
 * @link https://schema.org/Service/
 * @link https://schema.org/Person
 * @link https://schema.org/FAQPage
 * 
 */
 function btigAddCustomStructuredData() {
    global $post;
	// Set variables
	$siteName = esc_html( get_bloginfo('name') );
	$siteURL = esc_html( get_bloginfo('url') );
	$sitePhone = '+12679037115';
	$siteImage = 'https://livingbranches.org/wp-content/uploads/logo.png';

    
    if ( is_page( array(17,65,83,85,87,89,91,30,1636,1317) ) ) { // Services
		$schema = array();
		$serviceType = get_the_title( $post->ID );
		$serviceDescription = strip_tags( get_the_excerpt( $post->ID ) );
		if ($serviceDescription == '' ) {
			$serviceDescription = get_post_meta($post->ID, '_yoast_wpseo_metadesc', true);
		}
		if ($serviceDescription == '' ) {
			$serviceDescription = $serviceType . ' by ' . $siteName;
		}
		$schema["@context"] = "http://schema.org/";
		$schema["@type"] = "Service";
		$schema["serviceType"] = $serviceType;
		$schema["description"] = $serviceDescription;
		$schema["provider"] = array(
			"@type" => "Organization",
			"name" => $siteName,
			"sameAs" => $siteURL,
			"contactPoint" => array(
				"@type" => "ContactPoint",
				"telephone" => $sitePhone,
				"contactType" => "sales"
			)
		);
		$schema["availableChannel"] = array(
			"@type" => "ServiceChannel",
			"name" => $serviceType,
			"serviceUrl" => get_permalink( $post->ID )
		);
		echo '
		<script type="application/ld+json">' .
		json_encode($schema, JSON_UNESCAPED_UNICODE) .
		'</script>';
	} elseif(is_page(77)) { // Residence
		$schema = array();
		$schema["@context"] = "http://schema.org/";
		$schema["@type"] = "Residence";
		$schema['name'] = get_the_title( $post->ID );
		$schema['telephone'] = '+12158220688';
		$schema['url'] = get_permalink( $post->ID );
		$schema["hasMap"] = 'https://g.page/the-willows-of-living-branches?share';
		$schema["address"] = array(
			"@type" => "PostalAddress",
			"streetAddress" => '2343 Bethlehem Pike',
			"addressLocality" => 'Hatfield',
			"addressRegion" => 'PA',
			"postalCode" => '19440',
			"addressCountry" => 'US'
		);
		$schema["geo"] = array(
			"@type" => "GeoCoordinates",
			"latitude" => '40.2993307',
			"longitude" => '-75.2865523'
		);
		echo '
		<script type="application/ld+json">' .
		json_encode($schema, JSON_UNESCAPED_UNICODE) .
		'</script>';
	}  elseif(is_page(79)) { // Residence
		$schema = array();
		$schema["@context"] = "http://schema.org/";
		$schema["@type"] = "Residence";
		$schema['name'] = get_the_title( $post->ID );
		$schema['telephone'] = '+12157239881';
		$schema['url'] = get_permalink( $post->ID );
		$schema["hasMap"] = 'https://g.page/SoudertonMennoniteHomes?share';
		$schema["address"] = array(
			"@type" => "PostalAddress",
			"streetAddress" => '207 West Summit Street',
			"addressLocality" => 'Souderton',
			"addressRegion" => 'PA',
			"postalCode" => '18964',
			"addressCountry" => 'US'
		);
		$schema["geo"] = array(
			"@type" => "GeoCoordinates",
			"latitude" => '40.3112602',
			"longitude" => '-75.3343443'
		);
		echo '
		<script type="application/ld+json">' .
		json_encode($schema, JSON_UNESCAPED_UNICODE) .
		'</script>';
	}  elseif(is_page(1296)) { // Residence
		$schema = array();
		$schema["@context"] = "http://schema.org/";
		$schema["@type"] = "Residence";
		$schema['name'] = get_the_title( $post->ID );
		$schema['telephone'] = '+12153684438';
		$schema['url'] = get_permalink( $post->ID );
		$schema["hasMap"] = 'https://g.page/dock-woods--living-branches?share';
		$schema["address"] = array(
			"@type" => "PostalAddress",
			"streetAddress" => '275 Dock Drive',
			"addressLocality" => 'Lansdale',
			"addressRegion" => 'PA',
			"postalCode" => '19446',
			"addressCountry" => 'US'
		);
		$schema["geo"] = array(
			"@type" => "GeoCoordinates",
			"latitude" => '40.2565297',
			"longitude" => '-75.3397823'
		);
		echo '
		<script type="application/ld+json">' .
		json_encode($schema, JSON_UNESCAPED_UNICODE) .
		'</script>';
	} elseif (is_page(1237)) { // FAQ Page
?>
		<script>
		(function(){
			var dataRows = {
				"@context": "http://www.schema.org",
				"@type": "FAQPage",
				"name": "Frequently Asked Questions",
				"mainEntity": []
			}
			jQuery('.w-tabs-section').each(function(){
				var rowQuestion = {
					"@type": "Question",
					"name": jQuery(this).find('.w-tabs-section-header-h > .w-tabs-section-title').text(),
					"answerCount": 1,
					"acceptedAnswer": {
						"@type": "Answer",
						"text": jQuery(this).find('.w-tabs-section-content-h > .wpb_text_column').text()
					}
				}
				dataRows.mainEntity.push(rowQuestion);
			});
			 var script = document.createElement('script');
			 script.type = "application/ld+json";
			 script.innerHTML = JSON.stringify(dataRows);
			 document.getElementsByTagName('head')[0].appendChild(script);
		})(document);
		</script>
<?php
	}
}
add_action('wp_footer','btigAddCustomStructuredData', 100);

/**
 * Enqueue Scripts
 */
function load_scripts() {
    if( is_page(8392) ) {
		wp_enqueue_script('job-posting', get_stylesheet_directory_uri() . '/js/jobposting.js', array('jquery'), '', true);
    } 
}
//add_action('wp_enqueue_scripts', 'load_scripts');


/**
 *	remove quatity by forcing all products to sell individually
 */
function custom_remove_all_quantity_fields( $return, $product ) {return true;}
add_filter( 'woocommerce_is_sold_individually','custom_remove_all_quantity_fields', 10, 2 );


/**
 * Apply Registration Fees to the cart, based on Tiered Pricing
 */
add_action( 'woocommerce_cart_calculate_fees','bigAddRegistrationFees', 10, 1 );
function bigAddRegistrationFees( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}

	$paid_count = $regFees = 0;
	// count the number of paid courses
    foreach ( $cart->get_cart() as $cart_item ) {
        if ( has_term( 'paid-course', 'product_cat', $cart_item['product_id'] ) ) {
            $paid_count++;
        }
    }

	if ($paid_count > 0 && is100PCouponApplied($cart) == false) {
		// Get Tiered Pricing
		$rows = get_field('lb_pricing', 'options');
		if ($rows) {
			foreach ($rows as $row) {
				$max = $row['lb_max'];
				$regFees = $row['lb_price'];
				
				if ($paid_count <= $max ) {
					break;
				}
			}
		}
		
		$cart->add_fee( __('Registration fee', 'woocommerce'), $regFees, true );
	}
}

/**
 * Check if 100% coupon code is found
 */
function is100PCouponApplied($cart) {
	$coupon_id = '7603'; //$coupon_code = 's8w38vyb';
	$coupon_code = get_the_title($coupon_id);
	$coupons = $cart->get_applied_coupons();
	foreach($cart->get_applied_coupons() as $key => $value) {
		if (strtolower($value) == strtolower($coupon_code)) {
			return true;
		}
	}
	return false;
}


/**
 *	show alternate pricing verbiage for some products
 */
add_filter('woocommerce_get_price_html', 'show_free_not_zero', 9999, 2);
function show_free_not_zero($price, $product) {
	
	if (has_term( 'free-course', 'product_cat', $product->get_id())) {
		return 'FREE';
	} else if (has_term( 'paid-course', 'product_cat', $product->get_id())) {
		return '';
	}
	
	if (('' === $product->get_price()) || (0 == $product->get_price())) {
		//$price = 'FREE';
	}
	return $price;
} // function show_free_not_zero


/**
 * show alternate pricing verbiage for some products
 */
function sv_change_product_price_display( $price, $item ) {
	
	if (has_term( 'free-course', 'product_cat', $item['product_id'])) {
			return 'FREE';
	} else if (has_term( 'paid-course', 'product_cat', $item['product_id'])) {
		return '';
	}
	return $price;
}
//add_filter( 'woocommerce_get_price_html', 'sv_change_product_price_display', 10, 2 );
add_filter( 'woocommerce_cart_item_price', 'sv_change_product_price_display', 10, 2 );


/**
 * add our new custom options page for pricing
 */
if( function_exists('acf_add_options_page') ) {
	
	acf_add_options_page(array(
		'page_title' 	=> 'Tiered Pricing',
		'menu_title'	=> 'Tiered Pricing',
		'menu_slug' 	=> 'lb-tiered-pricing',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
}
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );


/**
 * Change number of products that are displayed per page (shop page)
 */
add_filter( 'loop_shop_per_page', 'new_loop_shop_per_page', 20 );

function new_loop_shop_per_page( $cols ) {
  // $cols contains the current number of products per page based on the value stored on Options –> Reading
  // Return the number of products you wanna show per page.
  $cols = 30;
  return $cols;
}


/*------------------------------------------------*\
	add gtm main script to <head> via enqueue
\*------------------------------------------------*/
add_action('wp_enqueue_scripts', 'bt_enqueue_gtm');
function bt_enqueue_gtm() {
    wp_enqueue_script(
        'gtm-script', 
        get_stylesheet_directory_uri() . '/gtm.js', 
        array('jquery'), 
        '2026.03.03.01', // Version number
        false // Load in the footer (true) or header (false)
    );
}


/**
 * default price to 0
 */
function set_product_default_price( $post_id, $post ) {
	$product     = wc_get_product( $post_id );
	$already_set = get_post_meta( $post_id, '_set_default_price', true );
	$price       = $product->get_price();

	if ( 'yes' !== $already_set && empty( $price ) ) {
		$product->set_regular_price( '0' );
		$product->save();

		update_post_meta( $post_id, '_set_default_price', 'yes' );
	}
}
add_action( 'woocommerce_process_product_meta', 'set_product_default_price', 999, 2 );












/**
 * check for the 100% off coupon and apply it
 */
//add_action('woocommerce_applied_coupon', 'apply_product_on_coupon');
function apply_product_on_coupon( ) {
    global $woocommerce;
    $coupon_id = '7603';

    if(in_array($coupon_id, $woocommerce->cart->get_applied_coupons())){
        //$woocommerce->cart->add_to_cart($free_product_id, 1);
    }
}









/*------------------------------------------------*\
	shortcode for the new custom breadcrumbs for the job opening page
	https://livingbranches.org/job-opening/director-of-residential-living/
	JM 2021.10.26
\*------------------------------------------------*/
add_shortcode('lb_breadcrumbs', 'shortcode_lb_breadcrumbs');
function shortcode_lb_breadcrumbs() {
	
	global $post;
	$post_title = get_the_title($post->ID);
	$post_link = get_the_permalink($post->ID);
	
	return '<div class="lb_jo_breadcrumbs"><a href="https://livingbranches.org/">Home</a><li class="g-breadcrumbs-separator"><i class="far fa-angle-right"></i></li><a href="https://livingbranches.org/careers/">Careers</a><li class="g-breadcrumbs-separator"><i class="far fa-angle-right"></i></li><a href="https://livingbranches.org/careers/job-openings/">Job Openings</a><li class="g-breadcrumbs-separator"><i class="far fa-angle-right"></i></li><a href="' . $post_link . '">' . $post_title . '</a></div>';
} // function shortcode_lb_breadcrumbs



/* code not working, scrap for now look for other solution 
add_filter('woe_get_order_value__product_addons', function ($value, $order, $fieldname) {
	
	return 'test';
	
	$data = maybe_unserialize($value);
	if( is_array($data) ) {
		$value = $data['Extra Cost'];
	}
	return $value;
}, 10, 3);
*/


/*------------------------------------------------*\
	disable email notification for password changes
\*------------------------------------------------*/
if ( !function_exists( 'wp_password_change_notification' ) ) {
	function wp_password_change_notification() {}
}


/*--------------------------------------------------------------------------------------------------*\
	remove flamingo menu and add submenu to CF7 instead
\*--------------------------------------------------------------------------------------------------*/
add_action('admin_menu', 'btig_custom_admin_menu');
function btig_custom_admin_menu() {
	global $menu;
	
	// new submenu for Flamingo Inbound Messages under CF7 menu item
	add_submenu_page('wpcf7', 'Submissions', 'Submissions', 'manage_options', 'admin.php?page=flamingo_inbound');
	
	// define menu items that should be removed for all roles
	$arrRestricted = array('toplevel_page_flamingo');
	
	foreach ($menu as $key => $item):
		// remove restricted menu items
		if (in_array($item[5], $arrRestricted)):
			unset($menu[$key]);
		endif;
	endforeach;
} // function btig_custom_admin_menu






//add_filter( 'woocommerce_product_single_add_to_cart_text', 'woocommerce_custom_product_add_to_cart_text' );
add_filter( 'woocommerce_product_add_to_cart_text', 'woocommerce_custom_product_add_to_cart_text' );
function woocommerce_custom_product_add_to_cart_text() {
    return __( 'Select options', 'woocommerce' );
}

/**
 * Change reCAPTCHA’s threshold
 * 
 * The default value of the threshold is 0.50.
 * @link https://contactform7.com/faq-about-recaptcha-v3/#how-to-change-threshold
 */
add_filter( 'wpcf7_recaptcha_threshold', function( $threshold ) {
    $threshold = 0.3; // decrease threshold to 0.3
    
    return $threshold;
}, 10, 1 );






//add_filter( 'woocommerce_checkout_fields', 'lb_disable_autocomplete_checkout_fields' );
function lb_disable_autocomplete_checkout_fields( $fields ) {
	
    $fields['billing']['billing_first_name']['autocomplete'] = false;
	$fields['billing']['billing_last_name']['autocomplete'] = false;
	$fields['billing']['billing_company']['autocomplete'] = false;
	$fields['billing']['billing_address_1']['autocomplete'] = false;
	$fields['billing']['billing_address_2']['autocomplete'] = false;
	$fields['billing']['billing_city']['autocomplete'] = false;
	$fields['billing']['billing_postcode']['autocomplete'] = false;
	$fields['billing']['billing_postcode']['autocomplete'] = false;
	$fields['billing']['billing_country']['autocomplete'] = false;
	$fields['billing']['billing_email']['autocomplete'] = false;
	$fields['billing']['billing_phone']['autocomplete'] = false;
	
	$fields['shipping']['shipping_first_name']['autocomplete'] = false;
	$fields['shipping']['shipping_last_name']['autocomplete'] = false;
	$fields['shipping']['shipping_company']['autocomplete'] = false;
	$fields['shipping']['shipping_address_1']['autocomplete'] = false;
	$fields['shipping']['shipping_address_2']['autocomplete'] = false;
	$fields['shipping']['shipping_city']['autocomplete'] = false;
	$fields['shipping']['shipping_postcode']['autocomplete'] = false;
	$fields['shipping']['shipping_country']['autocomplete'] = false;
	$fields['shipping']['shipping_state']['autocomplete'] = false;
	
    return $fields;
}










add_filter( 'woocommerce_checkout_get_value', 'bks_remove_values', 10, 2 );
function bks_remove_values( $value, $input ) {
    $item_to_set_null = array(
            'billing_first_name',
            'billing_last_name',
            'billing_company',
            'billing_address_1',
            'billing_address_2',
            'billing_city',
            'billing_postcode',
            'billing_country',
            'billing_state',
            'billing_email',
            'billing_phone',
            'shipping_first_name',
            'shipping_last_name',
            'shipping_company',
            'shipping_address_1',
            'shipping_address_2',
            'shipping_city',
            'shipping_postcode',
            'shipping_country',
            'shipping_state',
        ); // All the fields in this array will be set as empty string, add or remove as required.

    if (in_array($input, $item_to_set_null)) {
        $value = '';
    }

    return $value;
}


/*--------------------------------------------------------------------------------------------------*\
	JAMES HOFF	2/20/2025

	filter out cf7 submissions containing disallowed strings
\*--------------------------------------------------------------------------------------------------*/
add_action('wpcf7_before_send_mail', function($contact_form, $abort, $submission) {
	$sEmail = strtolower($submission->get_posted_data('your-email'));
	//$sMessage		= strtolower($submission->get_posted_data('your-message'));
	$arrDisallowed	= array('armyspy.com');
	foreach ($arrDisallowed as $badstring):
		if (strpos($sEmail, $badstring) !== false):
			$abort = true;
			exit();
		endif;
	endforeach;
}, 10, 3);

/**
 * GUS - 6/26/2025
 * @see https://boomtownig.teamwork.com/app/tasks/40280980
 * 
 * Filter function to remove product images from WooCommerce order emails.
 *
 * This function modifies the arguments used in WooCommerce email templates to prevent 
 * product images from being displayed in order confirmation emails sent to customers.
 *
 * @param array $args The existing arguments for displaying order items in emails.
 * @return array The modified arguments with product images removed.
 */
add_filter( 'woocommerce_email_order_items_args', 'big_remove_product_image_from_order_email' );
function big_remove_product_image_from_order_email( $args ) {
    $args['show_image'] = false;
    return $args;
}
