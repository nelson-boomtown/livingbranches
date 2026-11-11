<?
/*--------------------------------------------------------------------------------------------------*\
	hoff	1/26/2023

	KLUDGE: I seriously underbid this job so coding had to be rushed. It's functional but kind of a
	procedural mess. Could stand to be optimized one day.
\*--------------------------------------------------------------------------------------------------*/


/*------------------------------------------------*\
	initialize variables
\*------------------------------------------------*/
global $wpdb;
$sThisPage		= $_GET['page'];
$sSemester		= $_GET['sem'];
$sList			= $_GET['list'];
$sClass			= $_GET['class'] ? $_GET['class'] : -1;
$flAllClasses	= $sClass == -1;
$flShowResults	= $_GET['sem'] && $_GET['list'];
$flResults		= false;
$arrClassLists	= array('simple', 'detailed', 'payment');
$arrPayOrders	= array();


/*------------------------------------------------*\
	get products for a given semester
\*------------------------------------------------*/
if ($sSemester && $sList):
	$objProducts = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_type = 'product' and post_title LIKE '".$sSemester."%'");
	//$objProducts = $wpdb->query($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = 'product' and post_title LIKE %s", $wpdb->esc_like($sSemester).'%'));
	//echo '<pre style="text-align:left;background:#fff;">ALL PRODUCT IDs<br>';print_r($objProducts);echo '</pre><hr />';
	//echo $wpdb->last_query;
	$flResults = !empty($objProducts);
endif;



/*--------------------------------------------------------------------------------------------------*\
	get all orders that contain a specific product ordered - JH version
\*--------------------------------------------------------------------------------------------------*/
function get_order_ids_by_product_id($product_id, $order_status = array('wc-completed', 'wc-processing')) {
    global $wpdb;
    $results = $wpdb->get_col("
        SELECT order_items.order_id
        FROM {$wpdb->prefix}woocommerce_order_items as order_items
			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
			LEFT JOIN $wpdb->posts AS posts ON order_items.order_id = posts.ID
        WHERE posts.post_type = 'shop_order'
			AND posts.post_status IN ('" . implode("','", $order_status) . "')
			AND order_items.order_item_type = 'line_item'
			AND order_item_meta.meta_key = '_product_id'
			AND order_item_meta.meta_value = '$product_id'
    ");
    return $results;
}




/*--------------------------------------------------------------------------------------------------*\
	get all orders that contain a specific product ordered - JM version
\*--------------------------------------------------------------------------------------------------*/
function jm_get_order_ids_by_product_id($product_id, $sSemester) {
    global $wpdb;

    # Get all defined Orders IDs for a defined product ID (or variation ID)
    return $wpdb->get_col( "
        SELECT DISTINCT woi.order_id
        FROM {$wpdb->prefix}woocommerce_order_itemmeta as woim, 
             {$wpdb->prefix}woocommerce_order_items as woi, 
             {$wpdb->prefix}posts as p
        WHERE  woi.order_item_id = woim.order_item_id
			AND woi.order_id = p.ID
			AND p.post_status IN ( 'wc-completed', 'wc-processing'  )
			AND woim.meta_key IN ( '_product_id', '_variation_id' )
			AND woim.meta_value LIKE '$product_id'
			AND woi.order_item_name LIKE '".$sSemester."%'
        ORDER BY woi.order_item_id DESC"
    );
}
?>

<!--------------------------------------------------
	DataTables
-------------------------------------------------->
<script src="//cdn.datatables.net/1.13.2/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="//cdn.datatables.net/1.13.2/css/jquery.dataTables.min.css" media="screen" />

<script type="text/javascript">
	jQuery(function($) {
		
		$('.notice.notice-error.is-dismissible').hide();
		
		// submit the search form onchange
		$('#class-list, #classes<?=$flShowResults ? ', #semester' : '';?>').change(function() {
			$('#frm-reporting').submit();
		});

		// config DataTables
		$('.wp-list-table').DataTable({
			'paging': false,
			'bInfo' : false,
			'language': {
				'search': '',
				'searchPlaceholder': 'Search',
			},
			<?if ($sList == 'payment'):?>
				'order': [[2, 'asc'], [3, 'asc']],
			<?else:?>
				'order': [[0, 'asc'], [1, 'asc']],
			<?endif;?>
		});

	});
</script>

<style type="text/css">
	#frm-reporting th {text-align:right;}

	.dataTables_wrapper {margin-top:-2.5rem;}

	.dataTables_wrapper .dataTables_filter input {background:#fff;}

	.checkmark {font-size:18px;}

	/* PRINT STYLES */
	@media print {
		@page {size:landscape;}

		h1,
		h1 + br,
		hr,
		.dataTables_filter,
		#frm-reporting,
		#adminmenumain,
		#wpfooter {display:none;}

		#wpcontent,
		.wrap,
		html.wp-toolbar,
		.dataTables_wrapper {
			margin:0;
			padding:0;
		}

		a {
			text-decoration:none;
			color:#000;
		}

		table.wp-list-table,
		table.wp-list-table th,
		table.wp-list-table td {
			width:100%;
			border-collapse:collapse;
			border:1px solid #000;
			color:#000;
		}

		table.wp-list-table th,
		table.wp-list-table td {
			padding:5px;
			page-break-inside:avoid;
		}

		table.wp-list-table th {
			-webkit-print-color-adjust:exact !important;
			print-color-adjust:exact !important;
			background-color:#eee;
		}

		.shuttle-col {width:45px !important;}

		.print-page-break {page-break-after:always;}
		
		.notice.notice-error.is-dismissible {
			display: none !important;
		}
	}
</style>


<div class="wrap lb_reporting">
	<h1>Living U Reporting</h1><br />

	<form action="<?=$_SERVER['REQUEST_URI'];?>" method="get" id="frm-reporting">
		<input type="hidden" name="page" value="<?=$sThisPage;?>" />
		<table>
			<tr>
				<th>Semester:</th>
				<td>
					<select name="sem" id="semester">
						<option value="">--</option>
						<?
						// current year backwards to 2023
						for ($i = date('Y'); $i >= 2023; $i--):
							$i2DigitYear = substr($i, -2);

							if ($sSemester == "S$i2DigitYear"):
								$sFullSemYear = 'Spring '. $i;
							elseif ($sSemester == "F$i2DigitYear"):
								$sFullSemYear = 'Fall '. $i;
							endif;
						?>
							<option value="S<?=$i2DigitYear;?>"<?=($sSemester == "S$i2DigitYear") ? ' selected' : '';?>>Spring <?=$i;?></option>
							<option value="F<?=$i2DigitYear;?>"<?=($sSemester == "F$i2DigitYear") ? ' selected' : '';?>>Fall <?=$i;?></option>
						<?endfor;?>
					</select>
				</td>
			</tr>
			<tr>
				<th>Class List:</th>
				<td>
					<select name="list" id="class-list">
						<option value="">--</option>
						<?foreach ($arrClassLists as $list):?>
							<option value="<?=$list;?>"<?=($sList == $list) ? ' selected' : '';?>><?=ucfirst($list);?></option>
						<?endforeach;?>
					</select>
				</td>
			</tr>
			<?if ($flResults && ($sList != 'payment')):?>
				<tr>
					<th>Class:</th>
					<td>
						<select name="class" id="classes">
							<option value="">--</option>
							<option value="-1"<?=($sClass == -1) ? ' selected' : '';?>>ALL CLASSES</option>
							<?foreach ($objProducts as $product_id):?>
								<option value="<?=$product_id;?>"<?=($sClass == $product_id) ? ' selected' : '';?>><?=get_the_title($product_id);?></option>
							<?endforeach;?>
						</select>
					</td>
				</tr>
			<?endif;?>
			<tr>
				<th></th>
				<td><a href="?page=<?=$sThisPage;?>">Clear</a></td>
			</tr>
		</table>
	</form>

	<?
	
	
	
	
	
	
	//echo $sSemester; exit();
	
	
	
	
	
	
	
	
	
	
	/*------------------------------------------------*\
		SHOW RESULTS
	\*------------------------------------------------*/
	if ($flResults):

		// by default, loop through all products
		foreach ($objProducts as $prod_id):

			// sometimes we want to view a specific class, not ALL CLASSES. skip over classes not selected
			if (!$flAllClasses && $sClass != $prod_id):
				continue;
			endif;

			/*------------------------------------------------*\
				GET ORDERS FOR THIS PRODUCT
			\*------------------------------------------------*/
			if ($arrOrders = jm_get_order_ids_by_product_id($prod_id, $sSemester)):
				
				// if "payment" list is selected, just grab the order ids for now. we'll print out the payment list table later
				if ($sList == 'payment'):
					foreach ($arrOrders as $order_id):
						$arrPayOrders[$order_id] = 1;
					endforeach;
					continue;
				endif;

				//echo '<pre style="text-align:left;background:#fff;">ALL ORDERS CONTAINING PRODUCT ID = '.$prod_id.'<br />';print_r($arrOrders);echo '</pre><hr />';
				echo '<hr /><h2><a href="'.get_permalink($prod_id).'" target="_blank">',get_the_title($prod_id),'</a></h2>';

				/*------------------------------------------------*\
					LIST: SIMPLE
				\*------------------------------------------------*/
				if ($sList == 'simple'):
				?>
					<table class="wp-list-table widefat fixed striped posts list-simple">
						<?
						$sTableHead = '	<tr>
											<th>Last Name</th>
											<th>First Name</th>
											<th class="shuttle-col" style="text-align:center;">Shuttle</th>
											<!-- <th>Lunch</th> -->
											<th>Attendance Record</th>
										</tr>';
						?>
						<thead><?=$sTableHead;?></thead>
						<tbody>
							<?
							$iShuttle = 0;

							foreach ($arrOrders as $order_id):
								$objOrder = wc_get_order($order_id);
								//echo '<pre style="text-align:left;background:#fff;">';print_r($objOrder);echo '</pre><hr />';
								$arrOrderData = $objOrder->get_data();
								$arrItems = $objOrder->get_items();
								//echo '<pre style="text-align:left;background:#fff;">';print_r($arrItems);echo '</pre><hr />';

								foreach ($arrItems as $item):
									if ($item->get_product_id() == $prod_id):
										$iItemID = $item->get_ID();
										break;
									endif;
								endforeach;

								// determine shuttle service value
								$sShuttle = wc_get_order_item_meta($iItemID, 'I need shuttle service', true);
								if ($sShuttle == 'Yes'):
									$sShuttle = '<b class="checkmark">✓</b>';
									$iShuttle++;
								else:
									$sShuttle = '';
								endif;
							?>
								<tr>
									<td><?=$arrOrderData['billing']['last_name'];?></td>
									<td><?=$arrOrderData['billing']['first_name'];?></td>
									<td style="text-align:center;"><?=$sShuttle;?></td>
									<!-- <td>Lunch</td> -->
									<td>&nbsp;</td>
								</tr>
							<?endforeach;?>
						</tbody>
						<tfoot><?=$sTableHead;?></tfoot>
					</table>
					<h2>Total Registrations = <?=count($arrOrders);?></h2>
					<h2>Total Shuttle = <?=$iShuttle;?></h2>
					<div class="print-page-break"></div>

				<?
				/*------------------------------------------------*\
					LIST: DETAILED
				\*------------------------------------------------*/
				elseif ($sList == 'detailed'):
				?>
					<table class="wp-list-table widefat fixed striped posts list-detailed">
						<?
						$sTableHead = '	<tr>
											<th>Last Name</th>
											<th>First Name</th>
											<th>Address Line 1</th>
											<th>Address Line 2</th>
											<th>City</th>
											<th>State</th>
											<th>Zip</th>
											<th>Phone</th>
											<th>Email</th>
											<th class="shuttle-col" style="text-align:center;">Shuttle</th>
											<!-- <th>Lunch</th> -->
										</tr>';
						?>
						<thead><?=$sTableHead;?></thead>
						<tbody>
							<?
							$iShuttle = 0;

							foreach ($arrOrders as $order_id):
								$objOrder = wc_get_order($order_id);
								//echo '<pre style="text-align:left;background:#fff;">';print_r($objOrder);echo '</pre><hr />';
								$arrOrderData = $objOrder->get_data();
								$arrItems = $objOrder->get_items();
								//echo '<pre style="text-align:left;background:#fff;">';print_r($arrItems);echo '</pre><hr />';

								foreach ($arrItems as $item):
									if ($item->get_product_id() == $prod_id):
										$iItemID = $item->get_ID();
										break;
									endif;
								endforeach;

								// determine shuttle service value
								$sShuttle = wc_get_order_item_meta($iItemID, 'I need shuttle service', true);
								if ($sShuttle == 'Yes'):
									$sShuttle = '<b class="checkmark">✓</b>';
									$iShuttle++;
								else:
									$sShuttle = '';
								endif;
							?>
								<tr>
									<td><?=$arrOrderData['billing']['last_name'];?></td>
									<td><?=$arrOrderData['billing']['first_name'];?></td>
									<td><?=$arrOrderData['billing']['address_1'];?></td>
									<td><?=$arrOrderData['billing']['address_2'];?></td>
									<td><?=$arrOrderData['billing']['city'];?></td>
									<td><?=$arrOrderData['billing']['state'];?></td>
									<td><?=$arrOrderData['billing']['postcode'];?></td>
									<td><?=$arrOrderData['billing']['phone'];?></td>
									<td><a href="mailto:<?=$arrOrderData['billing']['email'];?>"><?=$arrOrderData['billing']['email'];?></a></td>
									<td style="text-align:center;"><?=$sShuttle;?></td>
								</tr>
							<?endforeach;?>
						</tbody>
						<tfoot><?=$sTableHead;?></tfoot>
					</table>
					<h2>Total Registrations = <?=count($arrOrders);?></h2>
					<h2>Total Shuttle = <?=$iShuttle;?></h2>
					<div class="print-page-break"></div>

				<?
				endif; // LIST SIMPLE, LIST DETAILED
		
			/*------------------------------------------------*\
				ERROR: NO ORDERS FOUND
			\*------------------------------------------------*/
			else:
			?>
				<div class="notice notice-error is-dismissible">
					<p>No orders found.</p>
					<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
				</div>
			<?
			endif; // GET ORDERS FOR THIS PRODUCT
		endforeach; // loop through all products


		/*------------------------------------------------*\
			LIST: PAYMENT
		\*------------------------------------------------*/
		if ($sList == 'payment'):

			echo "<hr /><h2>All Orders: $sFullSemYear</h2>";
		?>
			<table class="wp-list-table widefat fixed striped posts list-payment">
				<?
				$sTableHead = '	<tr>
									<th>Date</th>
									<th>Order #</th>
									<th>Last Name</th>
									<th>First Name</th>
									<th>Addr Line 1</th>
									<th>Addr Line 2</th>
									<th>Addr Line N</th>
									<th>Phone</th>
									<th>Email</th>
									<th>Customer</th>
									<th>Products</th>
									<th>Cost</th>
									<th>Payment Method</th>
									<th>Payment Status</th>
									<!-- <th>Lunch</th> -->
								</tr>';
				?>
				<thead><?=$sTableHead;?></thead>
				<tbody>
					<?
					//$order_id = 21640;
					foreach ($arrPayOrders as $order_id => $nothing):
						$objOrder = wc_get_order($order_id);
						//echo '<pre style="text-align:left;background:#fff;">';print_r($objOrder);echo '</pre><hr />';
						$arrOrderData = $objOrder->get_data();
						//echo '<pre style="text-align:left;background:#fff;">';print_r($arrOrderData);echo '</pre><hr />';
						$arrItems = $objOrder->get_items();
						//echo '<pre style="text-align:left;background:#fff;">';print_r($arrItems);echo '</pre><hr />';

						foreach ($arrItems as $item):
							if ($item->get_product_id() == $prod_id):
								$iItemID = $item->get_ID();
								break;
							endif;
						endforeach;

						if ($arrOrderData['payment_method'] == 'cod'):
							$sPayMethod = 'LB Statement';
							$sPayStatus	= 'Pending';
						else:
							$sPayMethod = 'Credit Card';
							$sPayStatus	= 'Paid';
						endif;

						$sOrderDate		= date('m/d/Y', strtotime($arrOrderData['date_created'])).'<br />'.date('g:i A', strtotime($arrOrderData['date_created']));
						$sCustomer		= $arrOrderData['customer_id'] ? 'Returning' : 'New';
						$sCityStateZip	= $arrOrderData['billing']['city'].', '.$arrOrderData['billing']['state'].' '.$arrOrderData['billing']['postcode'];
					?>
						<tr>
							<td><?=$sOrderDate;?></td>
							<td><a href="post.php?post=<?=$arrOrderData['id'];?>&action=edit" target="_blank">#<?=$arrOrderData['id'];?></a></td>
							<td><?=$arrOrderData['billing']['last_name'];?></td>
							<td><?=$arrOrderData['billing']['first_name'];?></td>
							<td><?=$arrOrderData['billing']['address_1'];?></td>
							<td><?=$arrOrderData['billing']['address_2'];?></td>
							<td><?=$sCityStateZip;?></td>
							<td><?=$arrOrderData['billing']['phone'];?></td>
							<td><a href="mailto:<?=$arrOrderData['billing']['email'];?>"><?=$arrOrderData['billing']['email'];?></a></td>
							<td><?=$sCustomer;?></td>
							<td><?=count($arrItems);?></td>
							<td>$<?=$arrOrderData['total'];?></td>
							<td><?=$sPayMethod;?></td>
							<td><?=$sPayStatus;?></td>
						</tr>
					<?endforeach;?>
				</tbody>
				<tfoot><?=$sTableHead;?></tfoot>
			</table>
			<h2>Total <?=$sFullSemYear;?> Orders = <?=count($arrPayOrders);?></h2>
			<div class="print-page-break"></div>

		<?
		endif; // LIST PAYMENT
	
	/*------------------------------------------------*\
		ERROR: NO PRODUCTS FOUND
	\*------------------------------------------------*/
	elseif ($flShowResults && !$flResults):
	?>
		<div class="notice notice-error is-dismissible">
			<p>No products found for <?=$sFullSemYear?>.</p>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
		</div>
	<?
	endif; // SHOW RESULTS
	?>
</div><!--.wrap -->

<?php
wp_reset_query();
wp_reset_postdata()
?>
