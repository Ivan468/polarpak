<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  chronopay_check.php                                      ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Chronopay (www.chronopay.com) transaction handler by http://www.viart.com/
 */
	
	$operation = get_param("operation");
	if (strtolower($operation) == "declined" || strtolower($operation) == "decline") {
		$error_message = "Your transaction has been declined.";
		return;
	}

	$success_message = "";
	$sql  = " SELECT transaction_id, success_message, error_message FROM " . $table_prefix . "orders ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$transaction_id = $db->f("transaction_id");
		$success_message = $db->f("success_message");
		$error_message = $db->f("error_message");
	}

	if (!strlen($success_message) && !strlen($error_message)) {
		$pending_message = "There is no answer from payment gateway or this order was declined by payment gateway. This order will be reviewed manually.";
	}

?>