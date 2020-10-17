<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  google_check.php                                         ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * Google Checkout (https://checkout.google.com/) transaction handler by www.viart.com
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
		$pending_message = "'Google order number' was not received, waiting for approval.";
	}

?>