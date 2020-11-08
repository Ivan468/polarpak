<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  cybersource_notice.php                                   ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Cybersource Notice handler by http://www.viart.com/
 * Date: 24.Sep.2018
 */

	$is_admin_path = true;
	$root_folder_path = "../";
	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/record.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."includes/shopping_cart.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/order_links.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");

	$cs_params = ""; // all parameters passed by CyberSource
	foreach($_POST as $key => $value) {
		$cs_params .= $key."=".$value."\n";
	}
	foreach($_GET as $key => $value) {
		$cs_params .= $key."=".$value."\n";
	}

	// get data from request
	$signature = get_param("signature");
	$signed_field_names = get_param("signed_field_names");
	$order_id = get_param("req_reference_number");

	if (strlen($order_id)) {

		$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
		get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables, "", "none");

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

		if ($signature == $our_signature) {
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
			$ev->set_value("event_type", NOTIFICATION_SENT_MSG);
			$ev->set_value("event_name", "CyberSource Notification");
			$ev->set_value("event_description", $cs_params);
			$ev->insert_record();
		}
	}
