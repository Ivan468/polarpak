<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  epdq_cpi_response.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * ePDQ CPI (www.tele-pro.co.uk/epdq/) transaction handler by http://www.viart.com/
 */

	$is_admin_path = true;
	$root_folder_path = "../";
	include_once ("../includes/common.php");
	include_once ("../includes/record.php");
	include_once ("../includes/order_items.php");
	include_once ("../includes/order_links.php");
	include_once ("../includes/shopping_cart.php");
	include_once ("../messages/".$language_code."/cart_messages.php");

	$status_error = '';

	// initialize template object
	$t = new VA_Template(".");

	$method     = get_var("REQUEST_METHOD");
	$order_id   = get_param("oid");
	$status     = get_param("transactionstatus");
	$total      = get_param("total");
	$client_id  = get_param("clientid");
	$datetime   = get_param("datetime");
	$chargetype = get_param("chargetype");

	if (strtoupper($method) != "POST" || !strlen($order_id)) {
		return;
	}

	$sql  = " SELECT is_placed,payment_id FROM " . $table_prefix . "orders ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$is_placed  = $db->f("is_placed");
		$payment_id = $db->f("payment_id");
	}

	if ($is_placed && !$payment_id) {
		return;
	}

	$sql  = " SELECT parameter_source FROM " . $table_prefix . "payment_parameters ";
	$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
	$sql .= " AND parameter_name='clientid'";
	$payment_client_id = get_db_value($sql);

	if ($payment_client_id != $client_id) {
		$error_message = "Mismatched client id parameter.";
	} else if (strtoupper($status) == "DECLINED") {
		$error_message = "Your transaction has been rejected.";
	} else if (strtoupper($status) != "SUCCESS") {
		if (strlen($status)) {
			$error_message = "Unknown transaction status (" . $status . ").";
		} else {
			$error_message = "Unknown transaction status.";
		}
	} else {
		$success_message = "Ok";
	}

	$order_final = array();
	$setting_type = "order_final_" . $payment_id;
	$sql = "SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings WHERE setting_type=" . $db->tosql($setting_type, TEXT);
	$db->query($sql);
	while($db->next_record()) {
		$order_final[$db->f("setting_name")] = $db->f("setting_value");
	}

	// get statuses
	$success_status_id = get_setting_value($order_final, "success_status_id", "");
	$failure_status_id = get_setting_value($order_final, "failure_status_id", "");

	// update order status
	$order_status = 0;
	if (strlen($error_message)) {
		$order_status = $failure_status_id;
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET error_message=" . $db->tosql($error_message, TEXT);
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
	} else {
		$order_status = $success_status_id;
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET success_message=" . $db->tosql($success_message, TEXT);
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
	}
	$db->query($sql);
	if ($order_status) {
		update_order_status($order_id, $order_status, true, "", $status_error);
	}

?>