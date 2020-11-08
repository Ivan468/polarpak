<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  argus_validate.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Argus Payment Solutions transaction handler by http://www.viart.com/  
 */

	$success_message = "";
	$sql  = " SELECT transaction_id, success_message, error_message FROM " . $table_prefix . "orders ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$success_message = $db->f("success_message");
		$transaction_id = $db->f("transaction_id");
		$error_message = $db->f("error_message");
	}

	if (!strlen($success_message) && !strlen($error_message)) {
		$pending_message = "Waits for approval from payment gateway.";
		// check for transaction id
		$transaction_id = get_param("TRANS_ID");
		if ($transaction_id) {
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET transaction_id=" . $db->tosql($transaction_id, TEXT);
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
		}
	}

?>