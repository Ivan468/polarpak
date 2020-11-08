<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  moneybookers_validate.php                                ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * Moneybookers (www.moneybookers.com) transaction handler by ViArt Ltd. (www.viart.com)
 */

	$sql  = " SELECT success_message, error_message, pending_message FROM " . $table_prefix . "orders ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$success_message = $db->f("success_message");
		$error_message = $db->f("error_message");
		$pending_message = $db->f("pending_message");
	}

	if (!strlen($success_message) && !strlen($error_message) && !strlen($pending_message)) {
		$pending_message = "There are no answer from payment gateway. Please waiting this order will be reviewed.";
	}

?>