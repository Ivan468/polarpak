<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  cpsbill_validate.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Capital Payment Solutions handler by http://www.viart.com/
 */

	$check_params = "POST\n";
	foreach ($_POST as $param_name => $param_value) {
		$check_params .= "$param_name = $param_value\n";
	}
	$check_params .= "GET\n";
	foreach ($_GET as $param_name => $param_value) {
		$check_params .= "$param_name = $param_value\n";
	}
	mail("enquiries@viart.com", "CPS Bill Validate", $check_params);

	// get payments parameters for validation
	$MerchantSecurityKey = get_setting_value($payment_parameters, "MerchantSecurityKey", "");

	// get parameters passed from Capital Payment Solutions
	$TransactionId = get_param("TransactionId"); // transaction parameter
	$transaction_id = get_param("TransactionId"); // transaction parameter
	// custom parameters
	$CS1 = get_param("CS1");
	$CS2 = get_param("CS2");
	$CS3 = get_param("CS3");
	$TransactionSign = get_param("TransactionSign"); // transaction sign sent by payment system 

	// calculate our sign value
	$OurSign = md5($TransactionId.$MerchantSecurityKey.$CS1.$CS2.$CS3);

	// use some checks on placed order
	if (!strlen($TransactionId)) {
		$error_message = TRANSACTION_DECLINED_MSG;
	} else if (strtoupper($TransactionSign) != strtoupper($OurSign)) {
		$error_message = str_replace("{param_name}", "'TransactionSign'", PARAMETER_WRONG_VALUE_MSG);
	}

	/*
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
	}//*/

?>