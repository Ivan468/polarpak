<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  argus_postback.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Argus Payment Solutions postback handler by http://www.viart.com/
 */


	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path . "includes/common.php");
	include_once ($root_folder_path . "includes/record.php");
	include_once ($root_folder_path . "includes/parameters.php");
	include_once ($root_folder_path . "includes/order_items.php");
	include_once ($root_folder_path . "includes/order_links.php");
	include_once ($root_folder_path . "includes/shopping_cart.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");

	$server_ip = get_ip();
	$success_message = "";
	$error_message = "";
	$pending_message = "";
	$payment_data  = file_get_contents('php://input');
	$payment_data .= " IP: " . $server_ip;

	//@mail("enquiries@viart.com", "Argus Postback", $payment_data);

	// validate server IP
	if ($server_ip != "69.165.111.144" && $server_ip != "109.251.200.123" && $server_ip != "127.0.0.1") {
		return;
	}

	// initialize template object
	$t = new VA_Template(".");

	// check transaction status and other related data
	$event_type = ""; $status_name = ""; $order_id = ""; $transaction_id = "";
	if (preg_match("/<EVENT[^>]+TYPE=\"([\w\s]+)\">/i", $payment_data, $match)) {
		$event_type = strtoupper($match[1]);
	}
	if (preg_match("/<TRANS_STATUS_NAME>(\w+)<\/TRANS_STATUS_NAME>/i", $payment_data, $match)) {
		$status_name = strtoupper($match[1]);
	}
	if (preg_match("/<XTL_UDF01>(\d+)<\/XTL_UDF01>/i", $payment_data, $match)) {
		$order_id = $match[1];
	}
	if (preg_match("/<TRANS_ID>(\w+)<\/TRANS_ID>/i", $payment_data, $match)) {
		$transaction_id = $match[1];
	}


	if (strlen($order_id)) {

		// save event
		$sql  = " INSERT INTO " . $table_prefix . "orders_events ";
		$sql .= " (order_id, event_date, event_name, event_description) ";
		$sql .= " VALUES( ";
		$sql .= $db->tosql($order_id, INTEGER).", ";
		$sql .= $db->tosql(va_time(), DATETIME).", ";
		$sql .= $db->tosql("Connected.to Postback", TEXT).", ";
		$sql .= $db->tosql(htmlspecialchars($payment_data), TEXT);
		$sql .= " ) ";
		$db->query($sql);


		if (!$event_type) {
			$error_message = "Can't get event type";
		} else if ($event_type != "PURCHASE" && $status_name != "APPROVED") {
			if ($status_name) {
				$error_message = "Your transaction status is " . htmlspecialchars($status_name);
			} else {
				$error_message = "Your transaction type is " . htmlspecialchars($event_type);
			}
		} else {
			// transaction type is PURCHASE or status APPROVED
			$success_message = "OK";
		}

		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET error_message=" . $db->tosql($error_message, TEXT);
		$sql .= " , success_message=" . $db->tosql($success_message, TEXT);
		$sql .= " , pending_message=" . $db->tosql($pending_message, TEXT);
		$sql .= " , transaction_id=" . $db->tosql($transaction_id, TEXT);
		$sql .= " , is_placed=0 ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);

		
		// get payment settings
		$payment_id = ""; $order_site_id = "";
		$sql  = " SELECT o.payment_id, o.site_id ";
		$sql .= " FROM " . $table_prefix . "orders o ";
		$sql .= " WHERE o.order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$payment_id     = $db->f("payment_id");
			$order_site_id  = $db->f("site_id");
		}
		
		$order_final = array();
		$setting_type = "order_final_" . $payment_id;
		$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
		$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
		$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($order_site_id, INTEGER, true, false) . ")";
		$sql .= " ORDER BY site_id ASC ";
		$db->query($sql);
		while ($db->next_record()) {
			$order_final[$db->f("setting_name")] = $db->f("setting_value");
		}

		// check order status
		$order_status = "";
		if ($success_message) {
			$order_status = get_setting_value($order_final, "success_status_id", "");
		} else if ($error_message) {
			$order_status = get_setting_value($order_final, "failure_status_id", "");
		}

		// update status 
		if ($order_status) { update_order_status($order_id, $order_status, true, "", $status_error); }
	}

	// send 100 response as we've received postback successfully
	echo 100;

?>