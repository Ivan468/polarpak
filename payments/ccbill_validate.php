<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  ccbill_validate.php                                      ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * CCBill (http://ccbill.com/) transaction handler by www.viart.com
 */

	$sql  = " SELECT transaction_id, error_message, pending_message FROM " . $table_prefix . "orders ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$transaction_id = $db->f("transaction_id");
		$error_message = $db->f("error_message");
		$pending_message = $db->f("pending_message");
	}

	if (!strlen($transaction_id) && !strlen($error_message) && !strlen($pending_message)) {
		$pending_message = "CCBill response wasn't received, waiting for manual approval.";
		// try to wait the CCBill response additional time
		if (!isset($va_data["payment_data"])) { $va_data["payment_data"] = array(); }
		$waiting_attempt = get_setting_value($va_data["payment_data"], "waiting_attempt", 0); // previous attempt number to wait payment response
		$va_data["payment_waiting"] = ($waiting_attempt + 1) * 10; // 10, 20, 30, 40, 50 seconds for next attempt
		$va_data["payment_data"]["waiting_attempts"] = 5; // 5 attemtps to wait and receive response from CCBill
	}

