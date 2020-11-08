<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  certitrade_confirm.php                                   ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * CertiTrade (http://www.certitrade.net/) transaction handler by www.viart.com
 */

	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/record.php");
	include_once ($root_folder_path ."includes/order_links.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");

	$r_md5code       = get_param('md5code');
	$r_merchantid    = get_param('merchantid');
	$r_order_id      = get_param('order_id');
	$r_amount        = get_param('amount');
	$r_currency      = get_param('currency');
	$r_result        = get_param('result');
	$r_result_code   = strval(get_param('result_code'));
	$r_bank_code     = get_param('bank_code');
	$transaction_id  = get_param('trnumber');
	$r_authcode      = get_param('authcode');
	$r_lang          = get_param('lang');
	$r_ch_name       = get_param('ch_name');
	$r_ch_address1   = get_param('ch_address1');
	$r_ch_address2   = get_param('ch_address2');
	$r_ch_address3   = get_param('ch_address3');
	$r_ch_zip        = get_param('ch_zip');
	$r_ch_city       = get_param('ch_city');
	$r_ch_phone      = get_param('ch_phone');
	$r_ch_email      = get_param('ch_email');
	$r_cc_descr      = get_param('cc_descr');
	$r_transp1       = get_param('transp1');
	$r_transp2       = get_param('transp2');
	$va_status       = strtolower(get_param('va_status'));

	$sql  = " SELECT setting_type FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_value LIKE '%certitrade_check.php'";
	$order_final = get_db_value($sql);
   	$idx = strrpos($order_final, "_");
   	$payment_id = substr($order_final, $idx+1);

	$sql  = " SELECT parameter_source FROM " . $table_prefix . "payment_parameters ";
	$sql .= " WHERE payment_id=".$db->tosql($payment_id, INTEGER)." and parameter_name='orderid'";
	$orderid_mask = get_db_value($sql);

   	$prefix_length = strpos($orderid_mask, "{order_id}");
   	$order_id_length = strlen($r_order_id) - strlen($orderid_mask) + strlen("{order_id}");
   	$order_id = substr($r_order_id, $prefix_length, $order_id_length);

	$status_error = '';

	$payment_parameters = array();
	$pass_parameters = array();
	$post_parameters = '';
	$pass_data = array();
	$variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);

	$md5str  = $payment_parameters['md5key'];
	$md5str .= $r_merchantid;
	$md5str .= $r_order_id;
	$md5str .= $r_amount;
	$md5str .= $r_currency;
	$md5str .= $r_result;
	$md5str .= $r_result_code;
	$md5str .= $r_bank_code;
	$md5str .= $transaction_id;
	$md5str .= $r_authcode;
	$md5str .= $r_lang;
	$md5str .= $r_ch_name;
	$md5str .= $r_ch_address1;
	$md5str .= $r_ch_address2;
	$md5str .= $r_ch_address3;
	$md5str .= $r_ch_zip;
	$md5str .= $r_ch_city;
	$md5str .= $r_ch_phone;
	$md5str .= $r_ch_email;

	$calculated_md5code = md5($md5str);

	if ($r_md5code == $calculated_md5code){
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET name=" . $db->tosql($r_ch_name, TEXT) ;
		$sql .= ", email=" . $db->tosql($r_ch_email, TEXT) ;
		$sql .= ", address1=" . $db->tosql($r_ch_address1, TEXT) ;
		$sql .= ", address2=" . $db->tosql($r_ch_address2, TEXT) ;
		$sql .= ", city=" . $db->tosql($r_ch_city, TEXT) ;
		$sql .= ", zip=" . $db->tosql($r_ch_zip, TEXT) ;
		$sql .= ", authorization_code=" . $db->tosql($r_authcode, TEXT) ;
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
		$db->query($sql);
		$order_status_id = 0;
		$failure_status_id = 0;
		$success_status_id = 0;
		$pending_status_id = 0;
		$sql  = " SELECT * ";
		$sql .= " FROM " . $table_prefix . "orders ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$payment_id = $db->f("payment_id");
			$sql = "SELECT setting_value FROM " . $table_prefix . "global_settings WHERE setting_type=" . $db->tosql("order_final_".$payment_id, TEXT) . " AND setting_name='failure_status_id'";
			$db->query($sql);
			if ($db->next_record()) {
				$failure_status_id = $db->f("setting_value");
			}
			$sql = "SELECT setting_value FROM " . $table_prefix . "global_settings WHERE setting_type=" . $db->tosql("order_final_".$payment_id, TEXT) . " AND setting_name='success_status_id'";
			$db->query($sql);
			if ($db->next_record()) {
				$success_status_id = $db->f("setting_value");
			}
			$sql = "SELECT setting_value FROM " . $table_prefix . "global_settings WHERE setting_type=" . $db->tosql("order_final_".$payment_id, TEXT) . " AND setting_name='pending_status_id'";
			$db->query($sql);
			if ($db->next_record()) {
				$pending_status_id = $db->f("setting_value");
			}
		}
		if($r_result_code == strval("00")){
			$response_message = 'Approved.';
		}elseif($r_result_code == strval("01")){
			$response_message = 'Declined by the bank.';
		}elseif($r_result_code == strval("02")){
			$response_message = 'No contact with the bank.';
		}elseif($r_result_code == strval("03")){
			$response_message = 'Other technical fault.';
		}elseif($r_result_code == strval("04")){
			$response_message = 'Cancelled by the buyer.';
 		}else{
			$response_message = "result_code: '".$r_result_code."'";
		}
		if($va_status=="approve"){
			if($r_result == "OK" && $r_result_code == "00"){
				$order_status_id = $success_status_id;
				$sql  = " UPDATE " . $table_prefix . "orders ";
				$sql .= " SET transaction_id=" . $db->tosql($transaction_id, TEXT) ;
				$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
				$db->query($sql);
			}else{
				$order_status_id = $failure_status_id;
				$error_message  = "Your transaction has not been approved. " . $response_message;
				$sql  = " UPDATE " . $table_prefix . "orders ";
				$sql .= " SET transaction_id=" . $db->tosql($transaction_id, TEXT) ;
				$sql .= ", error_message=" . $db->tosql($error_message, TEXT) ;
				$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
				$db->query($sql);
			}
		}elseif($va_status=="decline"){
			$order_status_id = $failure_status_id;
			$error_message = "Your transaction has been declined. " . $response_message;
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET transaction_id=" . $db->tosql($transaction_id, TEXT) ;
			$sql .= ", error_message=" . $db->tosql($error_message, TEXT) ;
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
			$db->query($sql);
		}elseif($va_status=="cancel"){
			$order_status_id = $failure_status_id;
			$error_message = "Your transaction has been cancelled. " . $response_message;
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET transaction_id=" . $db->tosql($transaction_id, TEXT) ;
			$sql .= ", error_message=" . $db->tosql($error_message, TEXT) ;
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
			$db->query($sql);
		}else{
			$order_status_id = $pending_status_id;
			$pending_message = "This order will be reviewed manually. " . $response_message;
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET transaction_id=" . $db->tosql($transaction_id, TEXT) ;
			$sql .= ", pending_message=" . $db->tosql($pending_message, TEXT) ;
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
			$db->query($sql);
		}
		if($order_status_id){
			$sql  = " INSERT INTO " . $table_prefix . "orders_events ";
			$sql .= " (order_id, status_id, event_date, event_name, event_description) ";
			$sql .= " VALUES( ";
			$sql .= $db->tosql($order_id, INTEGER).", ";
			$sql .= $db->tosql($order_status_id, INTEGER).", ";
			$sql .= $db->tosql(va_time(), DATETIME).", ";
			$sql .= $db->tosql('CertiTrade Status Updated', TEXT).", ";
			$sql .= $db->tosql($response_message, TEXT);
			$sql .= " ) ";
			$db->query($sql);
			$t = new VA_Template('.'.$settings["templates_dir"]);
			update_order_status($order_id, $order_status_id, true, "", $status_error);
		}
	}
?>