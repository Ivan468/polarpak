<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  paypal_pdt.php                                           ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
* PayPal PDT (www.paypal.com) transaction handler by http://www.viart.com/
*/

/*
	tx - transaction token
	st - status
	amt - transaction amount
	cc - currency code
	cm - Custom message
	sig - signature
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
	$auth_token     = isset($payment_params["at"]) ? $payment_params["at"] : "";
	$tx_token       = get_param("tx");
	$return_action  = get_param("return_action");
	$va_status      = get_param("va_status");
	$business_email = isset($payment_params["business"]) ? $payment_params["business"] : 0;
	$sandbox        = isset($payment_params["sandbox"]) ? $payment_params["sandbox"] : 0;
	$ssl            = isset($payment_params["ssl"]) ? $payment_params["ssl"] : 0;


	// check parameters
	if (strtolower($return_action) == 'cancel' || strtolower($va_status) == 'cancel') {
		$error_message = "Your transaction has been cancelled.";
	}else if (!strlen($auth_token)) {
		$error_message = "Can't obtain your identity token parameter.";
	} else if (!strlen($tx_token)) {
		$error_message = "Can't obtain transaction token parameter.";
	}

	if (strlen($error_message)) {
		return;
	}

	if ($sandbox == 1) {
		$paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
	} else {
		$paypal_url = "https://www.paypal.com/cgi-bin/webscr";
	}

	// request params for sending to paypal
	$request_params = "cmd=_notify-synch&tx=" . $tx_token . "&at=" . $auth_token;

	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $paypal_url);
	curl_setopt ($ch, CURLOPT_POST, 1);
	curl_setopt ($ch, CURLOPT_POSTFIELDS, $request_params);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
	set_curl_options ($ch, $payment_parameters);
	$paypal_response = curl_exec ($ch);
	$paypal_lines = explode("\n", $paypal_response);
	$paypal_status = $paypal_lines[0];
	$connect_errno = ""; $connect_error = "";
	if ($paypal_response === false) {
		$connect_errno = curl_errno($ch);
		$connect_error = curl_error($ch);
	}
	curl_close ($ch);

	$event_description  = "";
	if (isset($_GET) && count($_GET)) {
		$event_description .= var_export($_GET, true);
	}
	if (isset($_POST) && count($_POST)) {
		$event_description .= var_export($_POST, true);
	}
	$event_description .= "\n\nPayPal Request: ".$request_params;
	$event_description .= "\n\nPayPal Response: ".$paypal_response;

	$oe->set_value("order_id", $order_id);
	$oe->set_value("admin_id", get_session("session_admin_id"));
	$oe->set_value("event_date", va_time());
	$oe->set_value("event_type", "payment_notification");
	$oe->set_value("event_description", $event_description);

	if (!$paypal_response) {
		// Connect Error
		$pending_message = "Can't connect to PayPal. This order will be reviewed manually. ";
		$event_description .= "\n\nConnect Error: ".$connect_error;
		$event_description .= "\n\nPayPal URL: ".$paypal_url;
		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "orders_events ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$sql .= " AND event_type='payment_notification' ";
		$sql .= " AND event_name<>'VERIFIED' ";
		$failed_ipn = get_db_value($sql);
		if ($failed_ipn <= 3) {
			$oe->set_value("event_description", $event_description);
			$oe->set_value("event_name", "Connect Error (".$connect_errno.")");
			$oe->insert_record();
		}
		return;
	} else if (!preg_match("/SUCCESS/i", $paypal_status)) {
		$error_message = "PayPal response: " . $paypal_status;
		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "orders_events ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$sql .= " AND event_type='payment_notification' ";
		$sql .= " AND event_name<>'VERIFIED' ";
		$failed_ipn = get_db_value($sql);
		if ($failed_ipn <= 3) {
			$oe->set_value("event_name", $paypal_status);
			$oe->insert_record();
		}
		return;
	}

	// save all information to event
	$oe->set_value("event_name", $paypal_status);
	$oe->insert_record();

	$paypal_params = array();
	for ($i = 1; $i < sizeof($paypal_lines); $i++) {
		$param_line = trim($paypal_lines[$i]);
		if (strlen($param_line)) {
			list($param_name, $param_value) = explode("=", $param_line);
			$param_name = urldecode($param_name);
			$param_value = urldecode($param_value);
			$paypal_params[$param_name] = $param_value;
			$t->set_var($param_name, $param_value);
		}
	}

	$transaction_id   = $paypal_params["txn_id"];
	$payment_status   = $paypal_params["payment_status"];
	$payment_currency = $paypal_params["mc_currency"];
	$payment_amount   = $paypal_params["mc_gross"];
	$receiver_email   = $paypal_params["receiver_email"];
	if (!$receiver_email && isset($paypal_params["business"])) {
		$receiver_email = $paypal_params["business"];
	}
	$pending_reason   = isset($paypal_params["pending_reason"]) ? $paypal_params["pending_reason"] : "Pending";

	if (strtolower($payment_status) == "pending") {
		$pending_message = $pending_reason;
	}

	if (strtolower($payment_status) != "completed" && strtolower($payment_status) != "pending") {	// check the payment_status is Completed
		$error_message = "Your payment status is " . $payment_status;
	} else if (strtolower(trim($business_email)) != strtolower(trim($receiver_email))) {	// check that receiver_email is your Primary PayPal email
		$error_message = "Wrong receiver email - " . $receiver_email;
	} else {
		// check that payment_amount/payment_currency are correct
		$error_message = check_payment($order_id, $payment_amount, $payment_currency);
	}

?>