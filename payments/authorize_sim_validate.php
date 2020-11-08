<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  authorize_sim_validate.php                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Authorize.net SIM (www.authorize.net) transaction handler by http://www.viart.com/
 */

	global $is_admin_path, $is_sub_folder;
	$root_folder_path = ((isset($is_admin_path) && $is_admin_path) || (isset($is_sub_folder) && $is_sub_folder)) ? "../" : "./";
	include_once($root_folder_path . "includes/record.php");

	// initialize record to save events
	$oe = new VA_Record($table_prefix . "orders_events");
	$oe->add_textbox("order_id", INTEGER);
	$oe->add_textbox("status_id", INTEGER);
	$oe->add_textbox("admin_id", INTEGER);
	$oe->add_textbox("order_items", TEXT);
	$oe->add_textbox("event_date", DATETIME);
	$oe->add_textbox("event_type", TEXT);
	$oe->add_textbox("event_name", TEXT);
	$oe->add_textbox("event_description", TEXT);

	// get payments parameters for validation
	$x_login = get_setting_value($payment_parameters, "x_login");
	$x_secret = get_setting_value($payment_parameters, "x_secret");
	$x_signature_key = get_setting_value($payment_parameters, "x_signature_key", $x_secret); // use x_secret parameter if x_signature_key wasn't set

	// convert authorize parameters into lowercase
	$x_params = array();
	if (isset($_POST)) {
		foreach ($_POST as $param_name => $param_value) {
			$lower_name = strtolower($param_name);
			$x_params[$lower_name] = $param_value;
			$t->set_var($lower_name, $param_value);
		}
	}

	// get parameters passed from Authorize.net
	$transaction_id  = isset($x_params["x_trans_id"]) ? $x_params["x_trans_id"] : ""; // Authorize.net transaction number
	$order_id        = isset($x_params["x_invoice_num"]) ? $x_params["x_invoice_num"] : ""; // Our order number
	$response_code   = isset($x_params["x_response_code"]) ? $x_params["x_response_code"] : ""; // 1 - Approved, 2 - Declined, 3 - Error, 4 - Held for review
	$reason_code     = isset($x_params["x_response_reason_code"]) ? $x_params["x_response_reason_code"] : ""; // Reason code
	$reason_text     = isset($x_params["x_response_reason_text"]) ? $x_params["x_response_reason_text"] : ""; // Reason text
	$amount          = isset($x_params["x_amount"]) ? $x_params["x_amount"] : ""; // Total purchase amount.
	$x_md5_hash      = isset($x_params["x_md5_hash"]) ? $x_params["x_md5_hash"] : ""; // MD5 Hash from Authorize.net
	$x_sha2_hash     = isset($x_params["x_sha2_hash"]) ? $x_params["x_sha2_hash"] : ""; // SHA2 Hash from Authorize.net

	// calculate OLD MD5 hash
	$our_md5_hash = md5($x_secret.$x_login.$transaction_id.$amount); // Our key

	// calculate NEW HMAC SHA-512 hash
	$hash_params = array(
		"x_trans_id",
		"x_test_request",
		"x_response_code",
		"x_auth_code",
		"x_cvv2_resp_code",
		"x_cavv_response",
		"x_avs_code",
		"x_method",
		"x_account_number",
		"x_amount",
		"x_company",
		"x_first_name",
		"x_last_name",
		"x_address",
		"x_city",
		"x_state",
		"x_zip",
		"x_country",
		"x_phone",
		"x_fax",
		"x_email",
		"x_ship_to_company",
		"x_ship_to_first_name",
		"x_ship_to_last_name",
		"x_ship_to_address",
		"x_ship_to_city",
		"x_ship_to_state",
		"x_ship_to_zip",
		"x_ship_to_country",
		"x_invoice_num",
	);
	$hash_values = array();
	foreach ($hash_params as $hash_param) {
		$param_value = get_setting_value($x_params, $hash_param);
		$hash_values[] = $param_value;
	}
	$hash_data = "^".implode("^", $hash_values)."^";
	$our_sha2_hash = hash_hmac("sha512", $hash_data, hex2bin($x_signature_key));

	// check parameters
	if (!strlen($response_code)) {
		$error_message = str_replace("{param_name}", "response code", CANNOT_OBTAIN_PARAMETER_MSG);
	} elseif (!strlen($order_id)) {
		$error_message = str_replace("{param_name}", "invoice number", CANNOT_OBTAIN_PARAMETER_MSG);
	} elseif (!strlen($amount)) {
		$error_message = str_replace("{param_name}", "amount", CANNOT_OBTAIN_PARAMETER_MSG);
	} elseif (!strlen($x_login)) {
		$error_message = str_replace("{param_name}", "login", CANNOT_OBTAIN_PARAMETER_MSG);
	} elseif (!strlen($x_secret)) {
		$error_message = str_replace("{param_name}", "secret", CANNOT_OBTAIN_PARAMETER_MSG);
	} elseif ($response_code == "2") {
		if ($reason_text) { 
			$error_message = $reason_text; 
		} else { 
			$error_message = TRANSACTION_DECLINED_MSG; 
		}
		if ($reason_code) { $error_message .= " (" . $reason_code . ")"; }
	} elseif ($response_code == "3") {
		if ($reason_text) { 
			$error_message = $reason_text; 
		} else { 
			$error_message = PROCESSING_TRANSACTION_ERROR_MSG;
		}
		if ($reason_code) { $error_message .= " (" . $reason_code . ")"; }
	} elseif ($response_code == "4") {
		$pending_message = "Your transaction is being held for review.";
	} elseif ($response_code != "1") {
		$error_message = "Your transaction has been declined. Wrong response code. ";
	} elseif (strtoupper($our_sha2_hash) != strtoupper($x_sha2_hash)) {
		$error_message = "'Hash' parameter has wrong value: ($our_sha2_hash) - ($x_sha2_hash)";
	} else {
		$error_message = check_payment($order_id, $amount);
	}

	// set available parameters
	$remote_address   = get_ip();
	$t->set_var("remote_address", $remote_address);
	$t->set_var("x_response_code", $response_code);
	$t->set_var("our_md5_hash", $our_md5_hash);
	$t->set_var("x_md5_hash", $x_md5_hash);
	$t->set_var("x_amount", $amount);
	$t->set_var("our_sha2_hash", $our_sha2_hash);
	$t->set_var("x_sha2_hash", $x_sha2_hash);

	// save event
	$event_name = $reason_text;
	if (!$event_name) { $event_name = "Authorize.net SIM response"; }
	$event_description  = "";
	if (isset($_GET) && count($_GET)) {
		$event_description .= var_export($_GET, true);
	}
	if (isset($_POST) && count($_POST)) {
		$event_description .= var_export($_POST, true);
	}
	$event_description .= "\n\nOur SHA2 Hash: ".$our_sha2_hash;

	$oe->set_value("order_id", $order_id);
	$oe->set_value("admin_id", get_session("session_admin_id"));
	$oe->set_value("event_date", va_time());
	$oe->set_value("event_type", "payment_validation");
	$oe->set_value("event_name", $reason_text);
	$oe->set_value("event_description", $event_description);
	$oe->insert_record();
