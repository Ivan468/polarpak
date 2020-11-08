<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  invoicebox_postback.php                                  ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Invoicebox postback handler by http://www.viart.com/
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

	$admin_email = get_setting_value($settings, "admin_email", "");

	// validate server IP
	if (!preg_match("/^77\.244\.212\./", $server_ip)) {
		echo "Error: InvoiceBox IP didn't matched.";
		//@mail($admin_email, "Invoicebox Postback", $payment_data);
		return;
	}


	$participantId = get_param("participantId");
	$participantOrderId = get_param("participantOrderId");
	$ucode = get_param("ucode");
	$timetype = get_param("timetype");
	$time = get_param("time");
	$amount = get_param("amount");
	$payment_currency = get_param("currency");
	$agentName = get_param("agentName");
	$agentPointName = get_param("agentPointName");
	$testMode = get_param("testMode");
	$payment_sign = get_param("sign");

	//participantId=633&participantOrderId=TEST17102017042547&ucode=00000-00000-00000-00000&timetype=ATOM&
	//time=2017-10-17T04%3A25%3A47%2B03%3A00&amount=100.00&currency=&agentName=TEST&
	//agentPointName=TEST&testMode=1&sign=19a58dfb2fe7a8db4b8fdf8328fef840 


	$order_id = $participantOrderId;
	// initialize template object
	$t = new VA_Template(".");

	if (strlen($participantOrderId)) {

		// save event
		$sql  = " INSERT INTO " . $table_prefix . "orders_events ";
		$sql .= " (order_id, event_date, event_name, event_description) ";
		$sql .= " VALUES( ";
		$sql .= $db->tosql($order_id, INTEGER).", ";
		$sql .= $db->tosql(va_time(), DATETIME).", ";
		$sql .= $db->tosql("InvoiceBox Postback", TEXT).", ";
		$sql .= $db->tosql(htmlspecialchars($payment_data), TEXT);
		$sql .= " ) ";
		$db->query($sql);


		$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
		get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables, "final");

		// get key and compare returned and calculated signs
		$api_security_key = get_setting_value($payment_parameters, "api_security_key");  // API security key
		$sign_string = $participantId.$participantOrderId.$ucode.$timetype.$time.$amount.$payment_currency.$agentName.$agentPointName.$testMode.$api_security_key;
		$our_sign = md5($sign_string);

		$payment_data .= "\nSign string: $sign_string";
		$payment_data .= "\nOur sign: $our_sign";

		if (strtolower($payment_sign) != strtolower($our_sign)) {
			echo "Error: sign didn't matched $payment_sign <> $our_sign";
			//@mail($admin_email, "Invoicebox Postback", $payment_data);
			return;
		}


		$success_message = "OK";
		$error_message = ""; 
		$pending_message = ""; 
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET error_message=" . $db->tosql($error_message, TEXT);
		$sql .= " , success_message=" . $db->tosql($success_message, TEXT);
		$sql .= " , pending_message=" . $db->tosql($pending_message, TEXT);
		$sql .= " , transaction_id=" . $db->tosql($ucode, TEXT);
		$sql .= " , is_placed=0 ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);//*/

		
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

		// check success status
		$success_status_id = get_setting_value($order_final, "success_status_id", "");

		// update status 
		if ($success_status_id) { update_order_status($order_id, $success_status_id, true, "", $status_error); }

		// send OK response as we've received postback successfully
		echo "OK";
	} else {
		echo "Error: order number wasn't found";
	}


?>