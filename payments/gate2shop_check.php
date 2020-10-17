<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  gate2shop_check.php                                      ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	ini_set("display_errors", "1");
	error_reporting(E_ALL);

	$par = array("nameOnCard", "cardNumber", "cvv2", "expMonth", "expYear", "first_name", "last_name", "address1", "address2", "city", 
		"country", "email", "state", "zip", "phone1", "phone2", "phone3", "currency", "customField1", "customField2", "customField3", 
		"customField4", "customField5", "merchant_unique_id", "merchant_site_id", "merchant_id", "requestVersion", "PPP_TransactionID", 
		"productId", "userid", "message", "Error", "Status", "ClientUniqueID", "ExErrCode", "ErrCode", "AuthCode", "Reason", "ReasonCode", 
		"Token", "responsechecksum", "totalAmount", "TransactionID", "ppp_status", "invoice_id", "payment_method", "unknownParameters", 
		"merchantLocale", "customData", "return_action");
	
	for ($i=0;$i < count($par);$i++) {
		$answer[$par[$i]] = get_param($par[$i]);
	}
	
	$error_message = "";
	$post_parameters = ""; 
	$payment_params = array(); 
	$pass_parameters = array(); 
	$pass_data = array(); 
	$variables = array();
	get_payment_parameters($order_id, $payment_params, $pass_parameters, $post_parameters, $pass_data, $variables, "");
	
	$transaction_id = $answer["TransactionID"];

	$checksum = $answer["TransactionID"].$answer["ErrCode"].$answer["ExErrCode"].$answer["Status"];
	if(!isset($payment_parameters["secret"])){
		$error_message = "Secret Code is required.";
		return;
	}else{
		$secret = $payment_parameters["secret"];
		$checksum = $secret.$checksum;
	}
	
	//echo "<!-- ".$answer["Status"]." -->";
	
	$checksum = md5($checksum);
	
	if (strtolower($answer["return_action"]) == 'cancel') {
		$error_message = "Your transaction has been cancelled.";
		return;
	}

	// check errors
	$ErrCode = $answer["ErrCode"];
	$ExErrCode = $answer["ExErrCode"];
	if (strval($ErrCode) == '0' && strval($ExErrCode) == '-2') {
		$pending_message = "Your order will be reviewed manually.";
	} else if (strval($ErrCode) != '0' || strval($ExErrCode) != '0') {
		$error_message = $answer["Error"];
		if (!$error_message) {
			if ($ErrCode == -1) {
				$error_message = $answer["Reason"];
			}
		}
		if (!$error_message) {
			$error_message = "Your transaction has been declined.";
			if (strlen($answer["ErrCode"])) {
				$error_message .= " Error Code (" .$answer["ErrCode"].")";
			}
			if (strlen($answer["ExErrCode"])) {
				$error_message .= " ExErrCode Code (" .$answer["ExErrCode"].")";
			}
		}
		return;
	} else if (strtoupper($answer["Status"]) == "PENDING") {
		$pending_message = "Your order will be reviewed manually.";
	} else if (strtoupper($answer["Status"]) != "APPROVED") {
		$error_message = "Your transaction has been declined. (" . $answer["Status"] . ")";
	}

	if (!strlen($answer["responsechecksum"]) || $checksum != $answer["responsechecksum"]){
		$error_message = "Can't obtain Checksum parameter.";
		return;
	}
	if (!strlen($answer["TransactionID"])) {
		$error_message = "Can't obtain Transaction ID parameter.";
		return;
	}

?>