<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  sisow_notify.php                                         ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Sisow notify script transaction handler by http://www.viart.com/
 */

	$is_admin_path = true;
	$root_folder_path = "../";
	ini_set("display_errors", "1");
	error_reporting(E_ALL);
	include_once ($root_folder_path . "includes/common.php");
	include_once ($root_folder_path . "includes/record.php");
	include_once ($root_folder_path . "includes/parameters.php");
	include_once ($root_folder_path . "includes/order_items.php");
	include_once ($root_folder_path . "includes/order_links.php");
	include_once ($root_folder_path . "includes/shopping_cart.php");
	include_once ($root_folder_path . "includes/date_functions.php");
	include_once ($root_folder_path . "messages/".$language_code."/cart_messages.php");
	include_once ($root_folder_path . "payments/sisow_functions.php");

	$order_id = get_param("order_id");
	$trxid = get_param("trxid");
	$ec = get_param("ec");
	$status = get_param("status");
	$sha1_param = get_param("sha1");

	if (!$order_id || !$trxid || !$status) {
		// there is no required parameters just exit
		exit;
	}


	$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables, "final");

	$merchant_id = get_setting_value($payment_parameters, "merchant_id", "");
	$merchant_key = get_setting_value($payment_parameters, "merchant_key", "");
	$shop_id = get_setting_value($payment_parameters, "shop_id", "");
	$sha1_calc = sha1($trxid.$ec.$status.$merchant_id.$merchant_key);

	if ($sha1_calc != $sha1_param) {
		// request can't be validated exit
		exit;
	}

	// initialize template object
	$t = new VA_Template(".");

	// initialize record to save events
	$r = new VA_Record($table_prefix . "orders_events");
	$r->add_textbox("order_id", INTEGER);
	$r->add_textbox("status_id", INTEGER);
	$r->add_textbox("admin_id", INTEGER);
	$r->add_textbox("order_items", TEXT);
	$r->add_textbox("event_date", DATETIME);
	$r->add_textbox("event_type", TEXT);
	$r->add_textbox("event_name", TEXT);
	$r->add_textbox("event_description", TEXT);

	$payment_id = ""; $is_placed = 0; $current_status = 0; $order_site_id = "";
	$sql  = " SELECT o.payment_id, o.site_id, o.is_placed, o.order_status ";
	$sql .= " FROM " . $table_prefix . "orders o ";
	$sql .= " WHERE o.order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$payment_id     = $db->f("payment_id");
		$order_site_id  = $db->f("site_id");
		$is_placed      = $db->f("is_placed");
		$current_status = $db->f("order_status");
	} else {
		exit;
	}

	if ($is_placed) {
		// order already placed there is nothing to do then
		return;
	}
	
	$order_final = array();
	$setting_type = "order_final_" . $payment_id;
	$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
	if ($order_site_id) {
		$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($order_site_id, INTEGER, true, false) . ")";
		$sql .= " ORDER BY site_id ASC ";
	} else {
		$sql .= " AND site_id=1 ";
	}
	$db->query($sql);
	while ($db->next_record()) {
		$order_final[$db->f("setting_name")] = $db->f("setting_value");
	}

	// get statuses
	$success_status_id = get_setting_value($order_final, "success_status_id", "");
	$pending_status_id = get_setting_value($order_final, "pending_status_id", "");
	$failure_status_id = get_setting_value($order_final, "failure_status_id", "");

	$error_message = "";
	// check status parameter first
	if (strtolower($status) == "expired") {
		$error_message = "Your session has been expired.";
	} else if (strtolower($status) == "cancelled") {
		$error_message = "Your transaction has been cancelled.";
	} else if (strtolower($status) == "failure") {
		$error_message = "Your transaction has been failed.";
	} else {

		$sisow = new Sisow($merchant_id, $merchant_key, $shop_id);
		$sisow->entranceCode = $ec;
		$status_code = $sisow->StatusRequest($trxid);

		if ($sisow->status != Sisow::statusSuccess) {
			if ($sisow->errorMessage) {
				$error_message = $sisow->errorMessage;
			} else if ($sisow->errorCode) {
				$error_message = "Sisow error code: ".$sisow->errorCode;
			} else if ($status_code) {
				$error_message = "Sisow request code: ".$sisow->errorCode;
			} else {
				$error_message = "Sisow status: ".$sisow->status;
			}
		}

	}


	if (strlen($error_message)) {
		$order_status = $failure_status_id;
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET error_message=" . $db->tosql($error_message, TEXT);
		$sql .= " , transaction_id=" . $db->tosql($trxid, TEXT);
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
	} else {
		$order_status = $success_status_id;
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET error_message=''";
		$sql .= " , pending_message=''";
		$sql .= " , transaction_id=" . $db->tosql($trxid, TEXT);
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
	}
	$db->query($sql);

	if ($order_status) {
		// update order status
		update_order_status($order_id, $order_status, true, "", $status_error);
	}

?>