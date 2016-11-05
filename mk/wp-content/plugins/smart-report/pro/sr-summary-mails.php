<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $wpdb, $sr_text_domain;

if ( empty( $wpdb ) || !is_object( $wpdb ) ) {
    require_once ABSPATH . 'wp-includes/wp-db.php';
}

$sr_text_domain = ( defined('SR_TEXT_DOMAIN') ) ? SR_TEXT_DOMAIN : 'smart-reporter-for-wp-e-commerce';

include_once (WP_PLUGIN_DIR . "/smart-reporter-for-wp-e-commerce/sr/json-woo.php");

function extra_reccurences() {
	$curr_time_gmt = date('H:i:s',time()- date("Z"));
	$new_date = date('Y-m-d') ." " . $curr_time_gmt;
	$today = date('Y-m-d',((int)strtotime($new_date)) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS )) ;
	$days_in_this_month = date('t', mktime(0, 0, 0, date('m', strtotime($today)), 1, date('Y', strtotime($today))));
	$month_interval = 60*60*24*$days_in_this_month;

	return array(
		'weekly' => array('interval' => 604800, 'display' => 'Once Weekly'),
		'monthly' => array('interval' => $month_interval, 'display' => 'Once Monthly'),
	);
}

if (get_option('sr_send_summary_mails') == "yes") {

	add_filter('cron_schedules', 'extra_reccurences'); // for adding the occurance in set_timeout

	$curr_time_gmt = date('H:i:s',time()- date("Z"));

	if ( ! wp_next_scheduled( 'sr_send_summary_mails' ) ) {

		if (get_option('sr_summary_mail_interval') == 'monthly') {

			$new_date = date('Y-m-d' , strtotime(date('Y-m-d') .' +1 month')) ." " . $curr_time_gmt;
			$today = date('Y-m-d',((int)strtotime($new_date)) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS )) ;
			$monthly_start = date("Y-m-d H:i:s", mktime(0,0,0,date('m', strtotime($today)),get_option('sr_summary_month_start_day'),date('Y', strtotime($today))));
			wp_schedule_event( (strtotime( $monthly_start ) - (get_option( 'gmt_offset' ) * HOUR_IN_SECONDS)), 'monthly', 'sr_send_summary_mails');

		} else if (get_option('sr_summary_mail_interval') == 'weekly') {

			$weekly_start = date('Y-m-d H:i:s',strtotime("next ". get_option('sr_summary_week_start_day'), strtotime('tomorrow',strtotime(date('Y-m-d')))));
			wp_schedule_event( (strtotime( $weekly_start ) - (get_option( 'gmt_offset' ) * HOUR_IN_SECONDS)), 'weekly', 'sr_send_summary_mails');

		} else {
			wp_schedule_event( (strtotime( 'tomorrow ') - (get_option( 'gmt_offset' ) * HOUR_IN_SECONDS)), 'daily', 'sr_send_summary_mails');
		}
		
	}

	if (has_action('sr_send_summary_mails') === false ) {
		add_action( 'sr_send_summary_mails', 'sr_send_summary_mails');
	}
}

function sr_send_summary_mails() {

	$sr_nonce = defined('SR_NONCE') ? SR_NONCE : '';

	if ( ! wp_verify_nonce( $sr_nonce, 'smart-reporter-security' ) ) {
 		die( 'Security check' );
 	}

	global $wpdb, $sr_text_domain;

	$sr_currency_symbol = get_woocommerce_currency_symbol();
	$sr_decimal_places = get_option( 'woocommerce_price_num_decimals' );
	
	$_POST['params']['security'] = $sr_nonce;
	$_POST['cmd'] = 'sr_summary';

	$l_date = current_time( 'Y-m-d' ); //get date as per site timezone
	$date = date('Y-m-d', strtotime($l_date .' -1 day'));

	if (get_option('sr_summary_mail_interval') == 'monthly') {
		$_POST['start_date'] = date('Y-m-d', strtotime($date .' -1 month'));
	} else if (get_option('sr_summary_mail_interval') == 'weekly') {
		$_POST['start_date'] = date('Y-m-d', strtotime($date .' -1 week'));
	} else {
		$_POST['start_date'] = $date;
	}

	$_POST['end_date'] = $date;

	$today_arr 		    = getdate();
	$this_month_start   = date("Y-m-d H:i:s", mktime(0,0,0,$today_arr['mon'],1,$today_arr['year']));
	$days_in_this_month = date('t', mktime(0, 0, 0, $today_arr['mon'], 1, $today_arr['year']));


	$cumm_sales_data = json_decode(sr_get_cumm_stats(),true);
	$daily_kpi_data = json_decode(sr_get_daily_kpi_data($sr_nonce, '', $date),true);

	//code for forming the array for payment gateways

	$pm_data = array();

	if (!empty($cumm_sales_data['pm'])) {

		foreach ( $cumm_sales_data['pm'] as $pm ) {

			if( $pm['sales'] > 0 || $pm['orders'] > 0 ) {
				$pm_data [] = "<tr>". 
								"<td style='padding-left: 20px;'>" . $pm['title'] . "</td>". 
								"<td>" . $sr_currency_symbol . sr_number_format($pm['sales'],$sr_decimal_places) . " • " . 
										(($cumm_sales_data['sales'] > 0) ? ( sr_number_format( ($pm['sales']/$cumm_sales_data['sales'])*100, $sr_decimal_places ) . '%') : 'NA') . " • " . 
										$pm['orders'] . "</td> </tr>";
			}
		}

	}
	 
	//code for forming the array for abandoned products

	$aprod_data = array();

	if (!empty($cumm_sales_data['top_aprod'])) {

		foreach ( $cumm_sales_data['top_aprod'] as $aprod ) {		
			$aprod_data [] = "<tr>".
								"<td>" . $aprod['title'] . "</td>".
								"<td style='padding-left: 10px;'>" . $sr_currency_symbol . sr_number_format($aprod['sales'],$sr_decimal_places) . " • " . 
								sr_number_format($aprod['arate'],$sr_decimal_places) . "%  • " . 
								$aprod['aqty'] . "</td> </tr>";
		}
	}

	//code for forming the array for Top Coupons

	$tc_data = array();

	if (!empty($cumm_sales_data['top_coupons'])) {

		foreach ( $cumm_sales_data['top_coupons'] as $tc ) {
			$tc_data [] = "<tr>".
							"<td style='padding-left: 20px;'>" . $tc['title'] . "</td>".
							"<td>" . $sr_currency_symbol . sr_number_format($tc['sales'],$sr_decimal_places) . " • " . 
							$tc['count'] . "</td> </tr>";
		}
	}

	//code for forming the array for Top Products

	$tp_data = array();

	if (!empty($cumm_sales_data['top_prod']['sales'])) {

		foreach ( $cumm_sales_data['top_prod']['sales'] as $tp ) {
			$tp_data [] = "<tr>".
							"<td style='padding-left: 20px;'>" . $tp['title'] . "</td>".
							"<td>" . $sr_currency_symbol . sr_number_format($tp['sales'],$sr_decimal_places) . " • " .
							$tp['qty'] . "</td> </tr>";
		}
	}

	// $heading = 'Daily Summary Report - ' . date('F d, Y');
	if (get_option('sr_summary_mail_interval') == 'monthly') {
		$heading = __('Monthly Summary', $sr_text_domain) .' | ' . date('M d', strtotime($_POST['start_date'])) .' - '.date('M d', strtotime($_POST['end_date']));
	} else if (get_option('sr_summary_mail_interval') == 'weekly') {
		$heading = __('Weekly Summary', $sr_text_domain) .' | ' . date('M d', strtotime($_POST['start_date'])) .' - '.date('M d', strtotime($_POST['end_date']));
	} else {
		$heading = __('Daily Summary', $sr_text_domain) .' - ' . date('F d, Y', strtotime($_POST['end_date']));
	}
	
	ob_start();

	woocommerce_get_template('emails/email-header.php', array( 'email_heading' => $heading ));

	// <div class='average_order_total_amt'>
	// 	            <div class='sr_cumm_small_widget_content sr_cumm_avg_order_value'>

	echo "
			<table style='width: 120px;height: 55px;border: 2px solid #557da1;float: right;text-align: center;color: #7C7C86;margin-right: 50px'>
				<tr> <td>
				        <p style='margin-top: 5px;font-size: 24px;margin-bottom: 1px;'> ". $sr_currency_symbol . sr_number_format($daily_kpi_data['month_to_date_sales']['c'],$sr_decimal_places) ." </p>
			            <p style='font-size: 10px;font-weight: 500;'> ". __('Month To Date Sales', $sr_text_domain) ."</p>
					    
			    </td> </tr>
			</table>
			    
			<table >
				<tr> 
					<td> ". __('Sales', $sr_text_domain) ." </td> 
					<td style='padding-left: 50px'> <b>". $sr_currency_symbol . sr_number_format($cumm_sales_data['sales'],$sr_decimal_places) ." </b> </td>
				</tr>"  . 
				"<tr> 
					<td> ". __('Discounts', $sr_text_domain) ." </td> 
					<td style='padding-left: 50px'> ". $sr_currency_symbol . sr_number_format($cumm_sales_data['discount'],$sr_decimal_places) ." </td>
				</tr>" . 
				"<tr> 
					<td> ". __('Tax', $sr_text_domain) ."   </td> 
					<td style='padding-left: 50px'> ". $sr_currency_symbol . sr_number_format( ($cumm_sales_data['tax'] + $cumm_sales_data['shipping_tax']) ,$sr_decimal_places) ." </td>
				</tr>" . 
				"<tr> 
					<td> ". __('Shipping', $sr_text_domain) ."   </td> 
					<td style='padding-left: 50px'> ". $sr_currency_symbol . sr_number_format( $cumm_sales_data['shipping'] ,$sr_decimal_places) ." </td>
				</tr>" . 
				"<tr> 
					<td> ". __('Refunds', $sr_text_domain) ."   </td> 
					<td style='padding-left: 50px'> ". $sr_currency_symbol . sr_number_format($daily_kpi_data['refund_today']['c'],$sr_decimal_places) ." </td>
				</tr>" .
				"<tr> 
					<td> ". __('Net Sales', $sr_text_domain) ."   </td> 
					<td style='padding-left: 50px'> <b>". $sr_currency_symbol . sr_number_format( ($cumm_sales_data['sales'] - ($cumm_sales_data['tax'] + $cumm_sales_data['shipping_tax'] + $cumm_sales_data['shipping'])) ,$sr_decimal_places) ."</b> </td>
				</tr>" . 
			"</table> 


			<table style='color: #7C7C86;width: 120px;height: 55px;border: 2px solid #557da1;float: right;text-align: center;margin-right: 50px;margin-top: -25px'>
				<tr> <td>
			        <p style='margin-top: 5px;font-size: 24px;margin-bottom: 1px;'> ". $sr_currency_symbol . sr_number_format($daily_kpi_data['forecasted_sales']['c'],$sr_decimal_places) ." </p>
		            <p style='font-size: 10px;font-weight: 500;'> ". __('Forecasted Sales', $sr_text_domain) ." </p>
		    	</td> </tr>
			</table>

		    <br />

			<table>

				<tr> 
					<td> ". __('New Customers', $sr_text_domain) ."   </td> 
					<td style='padding-left: 10px'> <b>". $daily_kpi_data['new_customers_today']['c'] ." </b> </td>
				</tr>"  . 
				"<tr> 
					<td> ". __('Avg. Order Total', $sr_text_domain) ."   </td> 
					<td style='padding-left: 10px'> ". $sr_currency_symbol . sr_number_format( ( ($cumm_sales_data['orders'] > 0) ? $cumm_sales_data['sales']/$cumm_sales_data['orders'] : 0 ) ,$sr_decimal_places) ." </td>
				</tr>" . 
				"<tr> 
					<td> ". __('Avg. Items Per Customer', $sr_text_domain) ."   </td> 
					<td style='padding-left: 10px'> ". $cumm_sales_data['aipc'] ." </td>
				</tr>" . 
				"<tr> 
					<td> ". __('Unfullfilled Orders', $sr_text_domain) ."   </td> 
					<td style='padding-left: 10px'> <b>". $daily_kpi_data['orders_to_fulfill']['c'] ." </b> </td>
				</tr>" .

			"</table> 

			<span style='float:right'>
				<h3> ". __('Abandoned Products', $sr_text_domain) ." </h3>
					";

				if ( !empty($aprod_data) ) {

					echo "<table style='margin-top:-15px;'>";

					foreach ( $aprod_data as $val ) {
						echo $val;
					}	

					echo "</table>";

				} else {
					echo "<span style='margin-top:-15px;text-align: center;font-size: 15px;font-weight: 700;color: #DBDBDB;margin-top: 2.37em;font-family: Helvetica, Arial, sans-serif;'> 
							No Data
						</span>";
				}

				echo "
			</span>

			<h3> ". __('Abandonment Statistics', $sr_text_domain) ." </h3>
			<table style='margin-top:-15px;'>
				<tr> 
					<td> ". __('Add To Cart', $sr_text_domain) ." </td> 
					<td style='padding-left: 20px'> ". sr_number_format($cumm_sales_data['carts_prod'],$sr_decimal_places) ." </td>
				</tr>"  . 
				"<tr> 
					<td> ". __('Orders Placed', $sr_text_domain) ." </td> 
					<td style='padding-left: 20px'> ". sr_number_format($cumm_sales_data['orders_prod'],$sr_decimal_places) ." </td>
					</tr>" . 
				"<tr> 
					<td> ". __('Abandonment Rate', $sr_text_domain) ." </td> 
					<td style='padding-left: 20px'> ". sr_number_format($cumm_sales_data['car'],$sr_decimal_places) ."% </td>
				</tr>" . 
			"</table>

			<table width=100% style='margin-top:-15px;'>
			<tr><th width=60%></th><th width=40% style='text-align: right;'></th>

			<tr><th style='padding-bottom: 5px;padding-top: 20px;text-align: left;color: #7C7C86;'> ". __('Payment Gateways', $sr_text_domain) ." </th> </tr>

			";

			// style='margin-top:-15px;'

				if ( !empty($pm_data) ) {

					// echo "<table >";

					foreach ( $pm_data as $val ) {
						echo $val;
					}	

					// echo "</table>";

				} else {
					echo "<tr><td style='padding-left: 20px;><span style='margin-top:-15px;text-align: center;font-size: 15px;font-weight: 700;color: #DBDBDB;margin-top: 2.37em;font-family: Helvetica, Arial, sans-serif;'> 
							". __('No Data', $sr_text_domain) ."
						</span></td></tr>";
				}

			echo "

				<tr><th style='padding-bottom: 5px;padding-top: 20px;text-align: left;color: #7C7C86;'> ". __('Top Coupons', $sr_text_domain) ." </th> </tr>

				";

				if ( !empty($tc_data) ) {

					// echo "<table style='margin-top:-15px;'>";

					foreach ( $tc_data as $val ) {
						echo $val;
					}	

					// echo "</table>";

				} else {
					echo "<tr><td style='padding-left: 20px;><span style='margin-top:-15px;text-align: center;font-size: 15px;font-weight: 700;color: #DBDBDB;margin-top: 2.37em;font-family: Helvetica, Arial, sans-serif;'> 
							". __('No Data', $sr_text_domain) ."
						</span></td></tr>";
				}

			echo "

				<tr><th style='padding-bottom: 5px;padding-top: 20px;text-align: left;color: #7C7C86;'> ". __('Top Products', $sr_text_domain) ." </th> </tr>
			";

				if ( !empty($tp_data) ) {

					// echo "<table style='margin-top:-15px;'>";

					foreach ( $tp_data as $val ) {
						echo $val;
					}	

					// echo "</table>";

				} else {
					echo "<tr><td style='padding-left: 20px;><span style='margin-top:-15px;text-align: center;font-size: 15px;font-weight: 700;color: #DBDBDB;margin-top: 2.37em;font-family: Helvetica, Arial, sans-serif;'> 
							". __('No Data', $sr_text_domain) ."
						</span></td></tr>";
				}
				
			echo "</table>";


	$message = ob_get_clean();

	$sr_send_summary_mails_email = get_option('sr_send_summary_mails_email');
	$email = (!empty($sr_send_summary_mails_email)) ? $sr_send_summary_mails_email : get_option('admin_email');

	if (get_option('sr_summary_mail_interval') == 'monthly') {
		$subject = __('Monthly Summary Report for', $sr_text_domain) .' '. sanitize_title(get_bloginfo( 'name' )) .' | ' . date('M d', strtotime($_POST['start_date'])) .' - '.date('M d', strtotime($_POST['end_date']));
	} else if (get_option('sr_summary_mail_interval') == 'weekly') {
		$subject = __('Weekly Summary Report for', $sr_text_domain) .' '. sanitize_title(get_bloginfo( 'name' )) .' | ' . date('M d', strtotime($_POST['start_date'])) .' - '.date('M d', strtotime($_POST['end_date']));
	} else {
		$subject = __('Daily Summary Report for', $sr_text_domain) .' '. sanitize_title(get_bloginfo( 'name' )) .' | '. date('F d, Y', strtotime($_POST['end_date']));
	}

	woocommerce_mail( $email, $subject, $message );
}


//Abandoned Products Export CSV Function

function sr_export_csv_woo ( $columns_header, $data, $widget ) {

	$getfield = '';

	foreach ( $columns_header as $key => $value ) {
		$getfield .= $value . ',';
	}

	$fields = substr_replace($getfield, '', -1);
	$each_field = array_keys( $columns_header );
	
	$csv_file_name = sanitize_title(get_bloginfo( 'name' )) . '_' . $widget . '_' . gmdate('d-M-Y_H:i:s') . ".csv";

	foreach( (array) $data as $row ){
		for($i = 0; $i < count ( $columns_header ); $i++){
			if($i == 0) $fields .= "\n";
            $row_each_field = $row[$each_field[$i]];
            $array_temp = str_replace(array("\n", "\n\r", "\r\n", "\r"), "\t", $row_each_field); 
			$array = str_replace("<br>", "\n", $array_temp); 
			$array = str_getcsv ( $array , ",", "\"" , "\\");
			$str = ( $array && is_array( $array ) ) ? implode( ', ', $array ) : '';
			$fields .= '"'. $str . '",'; 
		}			
		$fields = substr_replace($fields, '', -1); 
	}
	$upload_dir = wp_upload_dir();
	$file_data = array();
	$file_data['wp_upload_dir'] = $upload_dir['path'] . '/';
	$file_data['file_name'] = $csv_file_name;
	$file_data['file_content'] = $fields;
	return $file_data;
}

// Abandoned Products Export

// if (isset ( $_GET ['cmd'] ) && (($_GET ['cmd'] == 'top_ababdoned_products_export') )) {
function sr_top_ababdoned_products_export() {


	$params = (!empty($_GET['params'])) ? json_decode(stripslashes(urldecode($_GET['params'])), true) : array(); 

	if ( ! wp_verify_nonce( $params['security'], 'smart-reporter-security' ) ) {
 		die( 'Security check' );
 	}

	global $wpdb, $sr_text_domain;

	$_POST['params']['security'] = $params['security'];
	$_POST['cmd'] = 'aprod_export';
	$_POST['start_date'] = $_GET['start_date'];
	$_POST['end_date'] = $_GET['end_date'];
    
    $abandoned_prod_data = json_decode(sr_get_cumm_stats(),true);

    $columns_header = array();
    $columns_header['title'] 				= __('Name', $sr_text_domain);
	$columns_header['aqty'] 			= __('Add To Cart', $sr_text_domain);
	$columns_header['arate'] 		    = __('Abandoment Rate', $sr_text_domain);
	$columns_header['sales'] 				    = __('Price', $sr_text_domain);
	$columns_header['lod'] 			= __('Last Order Date', $sr_text_domain);

	$file_data = sr_export_csv_woo ( $columns_header, $abandoned_prod_data, 'abandoned_products' );

	ob_clean();
    header("Content-type: text/x-csv; charset=UTF-8");
	header("Content-Transfer-Encoding: binary");
	header("Content-Disposition: attachment; filename=".$file_data['file_name']); 
	header("Pragma: no-cache");
	header("Expires: 0");
	
	echo $file_data['file_content'];	
	exit;
}


