<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  authorize_confirm.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Authorize.net SIM (www.authorize.net) transaction handler by www.viart.com
 */

	$x_params = array();
	if (isset($_POST)) {
		foreach ($_POST as $param_name => $param_value) {
			$lower_name = strtolower($param_name);
			$x_params[$lower_name] = $param_value;
		}
	} else {
		foreach ($HTTP_POST_VARS as $param_name => $param_value) {
			$lower_name = strtolower($param_name);
			$x_params[$lower_name] = $param_value;
		}
	}

	// get parameters passed from Authorize.net
	$transaction_id  = isset($x_params["x_trans_id"]) ? $x_params["x_trans_id"] : ""; // Authorize.net transaction number
	$order_id        = isset($x_params["x_invoice_num"]) ? $x_params["x_invoice_num"] : ""; // Our order number
	$response_code   = isset($x_params["x_response_code"]) ? $x_params["x_response_code"] : ""; // 1 - Approved, 2 - Declined, 3 - Error, 4 - Held for review
	$reason_code     = isset($x_params["x_response_reason_code"]) ? $x_params["x_response_reason_code"] : ""; // Reason code
	$reason_text     = isset($x_params["x_response_reason_text"]) ? $x_params["x_response_reason_text"] : ""; // Reason text
	$amount          = isset($x_params["x_amount"]) ? $x_params["x_amount"] : ""; // Total purchase amount.
	$x_md5_hash      = isset($x_params["x_md5_hash"]) ? $x_params["x_md5_hash"] : ""; // Hash from Authorize.net


	$is_admin_path = true;
	$root_folder_path = "../";
	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/order_links.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/record.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");

	$status_error = '';

	$t = new VA_Template('.'.$settings["templates_dir"]);
	$t->set_file("main","payment.html");

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


	$goto_payment_message = str_replace("{payment_system}", $settings["site_url"], GOTO_PAYMENT_MSG);
	$goto_payment_message = str_replace("{button_name}", CONTINUE_BUTTON, $goto_payment_message);
	$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);
	$t->set_var("payment_url",$settings["site_url"]."order_final.php");
	$t->set_var("submit_method", "post");
	$t->sparse("submit_payment", false);
	$t->pparse("main");

?>