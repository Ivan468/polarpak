<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  chronopay_confirm.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Chronopay (www.chronopay.com) transaction handler by http://www.viart.com/
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


	$order_id         = get_param("order_id");
	// check cs1 parameter if order_id wasn't passed
	if (!strlen($order_id)) { $order_id = get_param("cs1"); }

	$cb_params  = "";
	foreach($_POST as $key => $value) {
		$cb_params .= $key."=".$value."\n";
	}
	foreach($_GET as $key => $value) {
		$cb_params .= $key."=".$value."\n";
	}
	$cb_params .= "IP: " . get_ip();

	$customer_id      = get_param("customer_id");
	$transaction_id   = get_param("transaction_id");
	$transaction_type = get_param("transaction_type");
	$total            = get_param("total");
	$sign             = get_param("sign");
	$payment_currency = get_param("currency");

	if (strlen($order_id)) {

		$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
		get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables, "");

		$cb_ip = get_setting_value($payment_parameters, "cb_ip", "");
		$sharedsec = get_setting_value($payment_parameters, "sharedsec", "");
		$our_sign = md5($sharedsec.$customer_id.$transaction_id.$transaction_type.$total);
		$tt = strtolower($transaction_type);
		// check if we get response from correct server
		$error_message = "";
		if ($tt != "preauth" && $tt != "rebill" && $tt != "purchase" && $tt != "onetime") {
			$error_message = "Your transaction has been declined. ";
		} else if (strlen($cb_ip) && $cb_ip != get_ip()) {
			$error_message = "Callback IP (".get_ip().") has wrong value. ";
		} else if ($our_sign != $sign) {
			$error_message = "Checksum $sign didn't matched $our_sign. ";
		} else {
			$error_message = check_payment($order_id, $total, $payment_currency);
		}

		// update order with success status or error 
		if (strlen($error_message)) {
			$event_name = $error_message;
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET error_message=" . $db->tosql($error_message, TEXT);
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
		} else {
			$event_name = $transaction_type.": ".$transaction_id;
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET transaction_id=" . $db->tosql($transaction_id, TEXT);
			$sql .= " , error_message='', success_message=" . $db->tosql($transaction_type, TEXT);
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
		}
		$db->query($sql);

		// save event
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
		$ev->set_value("event_type", "Chronopay Callback");
		$ev->set_value("event_name", $event_name);
		if (strlen($error_message)) {
			$ev->set_value("event_description", $cb_params);
		}
		$ev->insert_record();


		if (!$error_message) {
			// initialize template object to use in update_order_status() function
			$t = new VA_Template(".");

			// check settings to update status
			$payment_id = "";
			$sql  = " SELECT payment_id FROM " . $table_prefix . "orders ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$payment_id = $db->f("payment_id");
			}

			$setting_type = "order_final_" . $payment_id;
			$order_final = get_settings($setting_type);
			$success_status_id = get_setting_value($order_final, "success_status_id", "");

			// update order status
			if ($success_status_id) {
				update_order_status($order_id, $success_status_id, true, "", $status_error);
			}
		}

	}
?>