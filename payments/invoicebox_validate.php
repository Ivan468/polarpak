<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  invoicebox_validate.php                                  ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * InvoiceBox transaction handler by http://www.viart.com/  
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
		$va_status = get_param("va_status");
		if (strtolower($va_status) == "cancel") {
			// check if user has cancelled the order
			$error_message = "Your transaction has been cancelled.";
		} else {
			$pending_message = "Waits for approval from payment gateway.";
		}
	}

?>