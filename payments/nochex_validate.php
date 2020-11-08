<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  nochex_validate.php                                      ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * NOCHEX (www.nochex.com) transaction handler by ViArt Ltd. (www.viart.com)
 */

	$sql  = " SELECT transaction_id, error_message, pending_message FROM " . $table_prefix . "orders ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$transaction_id = $db->f("transaction_id");
		$error_message = $db->f("error_message");
		$pending_message = $db->f("pending_message");
	}

	$return_action = get_param("return_action");
	if (strtolower($return_action) == 'cancel') {
		$error_message = "Your payment transaction has been cancelled.";
	}
	if (!strlen($transaction_id) && !strlen($error_message) && !strlen($pending_message)) {
		$pending_message = "'NOCHEX order number' was not received, waiting for approval.";
	}

?>