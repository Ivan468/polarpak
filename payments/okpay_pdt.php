<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  okpay_pdt.php                                            ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$return_action  = get_param("return_action");
	
	if (strtolower($return_action) == 'fail') {

		$error_message = "Your transaction has been cancelled.";

	}
	
	if (strlen($error_message)) {

		return;

	}
	
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