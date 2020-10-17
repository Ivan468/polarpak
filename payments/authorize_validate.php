<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  authorize_validate.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Authorize.net SIM (www.authorize.net) transaction handler by www.viart.com
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
		$pending_message = "There is no answer from payment gateway. This order will be reviewed manually.";
	}

?>