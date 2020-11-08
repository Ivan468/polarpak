<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  cybersource_validation.php                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * CyberSource validation module by http://www.viart.com/
 * Date: 18.Dec.2018
 */

	global $is_admin_path, $is_sub_folder;
	$root_folder_path = ((isset($is_admin_path) && $is_admin_path) || (isset($is_sub_folder) && $is_sub_folder)) ? "../" : "./";
	include_once($root_folder_path."payments/cybersource_functions.php");
	include_once($root_folder_path."includes/record.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");

/*
	$check_params = "POST\n";
	foreach ($_POST as $param_name => $param_value) {
		$check_params .= "$param_name = $param_value\n";
	}
	$check_params .= "GET\n";
	foreach ($_GET as $param_name => $param_value) {
		$check_params .= "$param_name = $param_value\n";
	}
	mail("enquiries@viart.com", "Cybersource Validate", $check_params);
//*/
	// get data
	$signature = get_param("signature");
	$signed_field_names = get_param("signed_field_names");
	$decision = strtoupper(get_param("decision"));
	$operation = strtolower(get_param("operation"));
	$reason_code = get_param("reason_code");
	$order_id = get_param("req_reference_number");
	$transaction_id = get_param("transaction_id");
	$cc_first_name = get_param("req_bill_to_forename")
	$cc_last_name = get_param("req_bill_to_surname")
	$cc_name = trim($cc_first_name." ".$cc_last_name);
	$card_number = get_param("req_card_number"); // 411111xxxxxx1111
	$card_expiry_date = get_param("req_card_expiry_date"); // 01-2017
	$card_type = get_param("req_card_type"); // 001 - Visa
	$variables["authorization_code"] = get_param("auth_code");
	$variables["auth_trans_ref_no"] = get_param("auth_trans_ref_no");
	$variables["payment_token"] = get_param("payment_token");

	// get payments parameters for validation
	$secret_key = get_setting_value($payment_parameters, "secret_key", "");

	// calculate our signature
	$signature_fields = explode(",", $signed_field_names);
	$signature_data = "";
	foreach ($signature_fields as $field_name) {
		$field_value = get_param($field_name);
		if ($signature_data) { $signature_data .= ","; }
		$signature_data .= $field_name."=".$field_value;
	}

	$our_signature = base64_encode(hash_hmac("sha256", $signature_data, $secret_key, true));

	if ($signature != $our_signature) {
		$error_message = "Signature has a wrong value $signature - $our_signature.";
	} else if ($decision == "ERROR" || $decision == "DECLINE") {
		$error_default = get_param("message");
		$error_message = get_cybersource_error($reason_code, $error_default);
		if (!$error_message) { $error_message = $decision; }
		$required_fields = get_param("required_fields");
		if ($required_fields) { $error_message .= " (".$required_fields.")"; }
	} else if ($decision == "REVIEW") {
		$pending_message = get_param("message");
		if (!$pending_message) { $pending_message = $decision; }
	} else if ($operation == "cancel" || $operation == "cancelled") {
		$error_message = "Your transaction has been cancelled.";
	} else if ($decision != "ACCEPT") {
		$error_message = "Unknown payment status: " .$decision;
	}

	if (!$error_message && ($card_type || $card_number)) {
		$cc_number = str_replace("x", "*", $card_number);
		$cc_number_last4 = substr($cc_number, -4);

		$cc_expiry_date = 0;
		if (preg_match("/(\d{2})\-(\d{4})/", $card_expiry_date, $matches)) {
			$exp_month = $matches[1]; // MM
			$exp_year = $matches[2]; // YYYY
			if ($exp_year && $exp_month) {
				$cc_expiry_date =	mktime (0, 0, 0, $exp_month, 1, $exp_year);
			}
		}

		// check viart cc_type
		$cc_type = "";
		$card_type = cybersource_cc_type($card_type, true); // get viart code to check credit card type
		$sql  = " SELECT credit_card_id FROM " . $table_prefix . "credit_cards ";
		$sql .= " WHERE credit_card_code=" . $db->tosql($card_type, TEXT);
		$sql .= " OR credit_card_name=" . $db->tosql($card_type, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$cc_type = $db->f("credit_card_id");
		}

		// update information
		if ($cc_type) {
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET cc_type=" . $db->tosql($cc_type, TEXT);
			$sql .= " , cc_name=" . $db->tosql($cc_name, TEXT);
			$sql .= " , cc_first_name=" . $db->tosql($cc_first_name, TEXT);
			$sql .= " , cc_last_name=" . $db->tosql($cc_last_name, TEXT);
			if (strlen($cc_number)) {
				$sql .= " , cc_number=" . $db->tosql($cc_number, TEXT);
			}
			if (strlen($cc_number_last4)) {
				$sql .= " , cc_number_last4=" . $db->tosql($cc_number_last4, TEXT);
			}
			if ($cc_expiry_date > 0) {
				$sql .= " , cc_expiry_date=" . $db->tosql($cc_expiry_date, DATETIME);
			}
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
			$db->query($sql);
		}
	}

	if ($error_message && $signature == $our_signature) {
		// save event if signature matched
		$ev = new VA_Record($table_prefix . "orders_events");
		$ev->add_textbox("order_id", INTEGER);
		$ev->add_textbox("admin_id", INTEGER);
		$ev->add_textbox("event_date", DATETIME);
		$ev->add_textbox("event_type", TEXT);
		$ev->add_textbox("event_name", TEXT);
		$ev->add_textbox("event_description", TEXT);
		$ev->set_value("order_id", $order_id);
		$ev->set_value("admin_id", get_session("session_admin_id"));
		$ev->set_value("event_date", va_time());
		$ev->set_value("event_type", "error");
		$ev->set_value("event_name", "CyberSource Error: ".$error_message);
		$ev->set_value("event_description", "");
		$ev->insert_record();
	}
