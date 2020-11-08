<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  cpsbill_callback.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Capital Payment Solutions callback handler by http://www.viart.com/
 */

	$check_params = "POST\n";
	foreach ($_POST as $param_name => $param_value) {
		$check_params .= "$param_name = $param_value\n";
	}
	$check_params .= "GET\n";
	foreach ($_GET as $param_name => $param_value) {
		$check_params .= "$param_name = $param_value\n";
	}
	mail("enquiries@viart.com", "CPS Bill Callback", $check_params);


/*

	$order_status = 0;
	if (strlen($order_id)) {
		$failure_status_id = 0;
		$success_status_id = 0;
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
		}
	
		$payment_parameters = array();
		$pass_parameters = array();
		$post_parameters = '';
		$pass_data = array();
		$variables = array();
		get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);
	
		$x_login  = isset($payment_parameters["x_login"]) ? $payment_parameters["x_login"] : "";
		$x_secret = isset($payment_parameters["x_secret"]) ? $payment_parameters["x_secret"] : "";
		$x_amount = isset($payment_parameters["x_amount"]) ? $payment_parameters["x_amount"] : "";

		$our_md5_hash = md5($x_secret.$x_login.$transaction_id.$amount); // Our key
		// check parameters
		$error_message = "";
		if (!strlen($response_code)) {
			$error_message = "Can't obtain response code parameter.";
		} else if (!strlen($order_id)) {
			$error_message .= " Can't obtain invoice number parameter.";
		} else if (!strlen($amount)) {
			$error_message .= " Can't obtain amount parameter.";
		} else if ($response_code == "2") {
			if ($reason_text) { 
				$error_message .= " ".$reason_text; 
			} else { 
				$error_message .= " Your transaction has been declined."; 
			}
			if ($reason_code) { $error_message .= " (" . $reason_code . ")"; }
		} else if ($response_code == "3") {
			if ($reason_text) { 
				$error_message .= " ".$reason_text; 
			} else { 
				$error_message .= " There has been an error processing this transaction.";
			}
			if ($reason_code) { $error_message .= " (" . $reason_code . ")"; }
		} else if ($response_code == "4") {
			$pending_message .= " Your transaction is being held for review.";
		} else if ($response_code != "1") {
			$error_message .= " Your transaction has been declined. Wrong response code. ";
		} else if (strtoupper($our_md5_hash) != strtoupper($x_md5_hash)) {
			$error_message .= " 'Hash' parameter has wrong value.";
		} else {
			$error_message .= check_payment($order_id, $amount);
		}

		// update transaction information
		$sql  = " UPDATE " . $table_prefix . "orders SET transaction_id=" . $db->tosql($transaction_id, TEXT);
		if (!strlen($error_message)) {
			$sql .= ", success_message='OK'";
			if ($success_status_id){
				$order_status = $success_status_id;
			}
		}else{
			$sql .= ", error_message=" . $db->tosql($error_message, TEXT);
			if ($failure_status_id){
				$order_status = $failure_status_id;
			}
		}
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
	
	}

	// update credit card information returned from Authorize.net
	$card_type = get_param("TransactionCardType"); 
	$cc_number = get_param("Card_Number"); // ############1111
	$cc_number = str_replace("#", "*", $cc_number); // convert to viart format

	if (!$error_message && $cc_number) {

		$exp_ts = 0;
		$exp_date = get_param("Expiry_Date");
		if (preg_match("/(\d{2})(\d{2})/", $exp_date, $matches)) {
			$exp_month = $matches[1]; // MM
			$exp_year = 2000 + $matches[2]; // YY
		}
		if ($exp_year && $exp_month) {
			$exp_ts =	mktime (0, 0, 0, $exp_month, 1, $exp_year);
		}

		$cc_name = get_param("CardHoldersName"); // John Marshall

		// check viart cc_type
		$cc_type = "";
		$sql  = " SELECT credit_card_id FROM " . $table_prefix . "credit_cards ";
		$sql .= " WHERE credit_card_code=" . $db->tosql($card_type, TEXT);
		$sql .= " OR credit_card_name=" . $db->tosql($card_type, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$cc_type = $db->f("credit_card_id");
		}

		// update information
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET cc_number=" . $db->tosql($cc_number, TEXT);
		if (strlen($cc_type)) {
			$sql .= " , cc_type=" . $db->tosql($cc_type, INTEGER);
		}
		if (strlen($cc_name)) {
			$sql .= " , cc_name=" . $db->tosql($cc_name, TEXT);
		}
		if ($exp_ts > 0) {
			$sql .= " , cc_expiry_date=" . $db->tosql($exp_ts, DATETIME);
		}
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
		$db->query($sql);
	}


	if ($order_status) {
		update_order_status($order_id, $order_status, true, "", $status_error);
	}

*/

?>