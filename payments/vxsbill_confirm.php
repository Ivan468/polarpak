<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  vxsbill_confirm.php                                      ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * VXSBill (www.vxsbill.com) transaction handler by www.viart.com
 */

	$is_admin_path = true;
	$root_folder_path = "../";
	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/record.php");
	include_once ($root_folder_path ."includes/order_links.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path . "payments/vxsbill_functions.php");

	check_admin_security("update_orders");

	$t = new VA_Template("");

	$sql  = " SELECT payment_id FROM " . $table_prefix . "payment_systems";
	$sql .= " WHERE payment_url  LIKE './payments/vxsbill_process.php'";
	$db->query($sql);
	if ($db->num_rows() == 1) {
		$db->next_record();
		$payment_id = $db->f("payment_id");
	} else {
		exit;
	}

	$order_ids = array();
	$sql  = " SELECT * FROM " . $table_prefix . "orders";
	$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER) . " AND order_status=1 ";
	$sql .= " AND order_placed_date <=" . $db->tosql(time() - 600, DATETIME);
	$db->query($sql);
	if ($db->num_rows() != 0){
		while ($db->next_record()) {
			$order_ids[] = array($db->f("order_id"), $db->f("transaction_id"));
		}
	} else {
		exit;
	}

	$error_message = "";
	$pending_message = "";
	$success_message = "";
	$transaction_id = "";
	$status_error = "";

	foreach ($order_ids as $value) {
		$order_id = $value[0];
		$payment_parameters = array();
		$pass_parameters = array();
		$post_parameters = '';
		$pass_data = array();
		$variables = array();
		get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);
		vxsbill_payment_check();

		if(strlen($error_message)) {
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET order_status=" . $db->tosql($variables['failure_status_id'], INTEGER);
			$sql .= " , transaction_id=" . $db->tosql($transaction_id, TEXT);
			$sql .= " , error_message=" . $db->tosql($error_message, TEXT);
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
			update_order_status($order_id, $variables['failure_status_id'], true, "", $status_error);
			echo "| " . $order_id . " | " . $error_message . " | " . $transaction_id . " | status - failure<hr>\n";
		}elseif(strlen($pending_message)){
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET order_status=" . $db->tosql($variables['pending_status_id'], INTEGER);
			$sql .= " , transaction_id=" . $db->tosql($transaction_id, TEXT);
			$sql .= " , pending_message=" . $db->tosql($pending_message, TEXT);
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
			update_order_status($order_id, $variables['pending_status_id'], true, "", $status_error);
			echo "| " . $order_id . " | " . $pending_message . " | " . $transaction_id . " | status - pending<hr>\n";
		}elseif(strlen($transaction_id)){
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET order_status=" . $db->tosql($variables['success_status_id'], INTEGER);
			$sql .= " , transaction_id=" . $db->tosql($transaction_id, TEXT);
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
			update_order_status($order_id, $variables['success_status_id'], true, "", $status_error);
			echo "| " . $order_id . " | " . $success_message . " | " . $transaction_id . " | status - success<hr>\n";
		}
		$error_message = "";
		$pending_message = "";
		$success_message = "";
		$transaction_id = "";
	}

?>