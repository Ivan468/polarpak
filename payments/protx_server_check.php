<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  protx_server_check.php                                   ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * VSP (www.protx.com) transaction handler by www.viart.com
 */

	$success_message = "";
	$sql  = " SELECT success_message, error_message FROM " . $table_prefix . "orders ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$success_message = $db->f("success_message");
		$error_message = $db->f("error_message");
	}

	if (!strlen($success_message) && !strlen($error_message)) {
		$pending_message = "There is no answer from payment gateway. This order will be reviewed manually.";
	}

?>