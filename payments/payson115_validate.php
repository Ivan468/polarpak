<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  payson115_validate.php                                   ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
  ****************************************************************************
  ***                                                                      ***
  ***      ViArt SHOP 4.2.1                                                ***
  ***      File:  payson115_validate.php                                   ***
  ***      Built: Wed Sep 10 17:26:40 2014                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/




/*

 * Payson validation handler by http://www.viart.com/

 */



	$operation = get_param("operation");


	if (strtolower($operation) == "cancel") {

		// check if user has cancelled the order

		$error_message = "Your transaction has been cancelled.";

	} else {

		// check if we receive IPN answer from Payson

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

			if (strtolower($success_message) == "processing" || strtolower($success_message) == "pending") {

				$pending_message = "We've received confirmation for new order from Payson.";

			} else if (strtolower($success_message) == "completed") {

				// the order is payed. 

			} else if (strtolower($success_message) == "aborted" || strtolower($success_message) == "error") {

				$error_message = "Your transaction has been cancelled.";

			} else {

				$pending_message = "There is no answer from payment gateway. This order will be reviewed manually.";

			}

		}


	}



?>