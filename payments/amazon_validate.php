<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  amazon_validate.php                                      ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Amazon Checkout handler by http://www.viart.com/
 */

	$operation = get_param("operation");
	$amznPmtsOrderIds = get_param("amznPmtsOrderIds");

	if (strtolower($operation) == "cancel") {
		// check if user has cancelled the order
		$error_message = "Your transaction has been cancelled.";
	} else {
		// check if we receive IPN answer from Amazon
		$success_message = "";
		$sql  = " SELECT transaction_id, success_message, error_message FROM " . $table_prefix . "orders ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$success_message = $db->f("success_message");
			$transaction_id = $db->f("transaction_id");
			$error_message = $db->f("error_message");
			$pending_message = $db->f("pending_message");
		}

		if (!strlen($pending_message) && !strlen($error_message)) {
			if (strtolower($success_message) == "newordernotification") {
				$pending_message = "We've received confirmation for new order from Amazon.";
			} else if (strtolower($success_message) == "orderreadytoshipnotification") {
				// the order is ready to be shipped according to Checkout by Amazon order pipeline. 
			} else if (strtolower($success_message) == "ordercancellednotification") {
				$error_message = "Your transaction has been cancelled.";
			} else if ($amznPmtsOrderIds) {
				$pending_message = "Thank you for submitting your order.";
			} else {
				$pending_message = "There is no answer from payment gateway. This order will be reviewed manually.";
			}
		}

	}

?>