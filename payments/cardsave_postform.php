<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  cardsave_postform.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * CardSave (http://www.cardsave.net/) transaction handler by www.viart.com
 */

 	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");

	$vc = get_session("session_vc");
	$order_id = get_session("session_order_id");

	$order_errors = check_order($order_id, $vc);
	if($order_errors) {
		echo $order_errors;
		exit;
	}

	$payment_url = "https://mms.cardsaveonlinepayments.com/Pages/PublicPages/PaymentForm.aspx";
	$payment_parameters = array();
	$pass_parameters = array();
	$post_parameters = '';
	$pass_data = array();
	$variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);

	$payment_name = get_setting_value($variables, "payment_name", "");
	$user_payment_name = get_setting_value($variables, "user_payment_name", $payment_name);

	// check payment parameters
	$params = array();
	$params["PreSharedKey"] = get_setting_value($payment_parameters, "PreSharedKey", "");
	$params["Password"] = get_setting_value($payment_parameters, "Password", "");

	// check form parameters
	$form = array();
	$form["HashDigest"] = get_setting_value($pass_data, "HashDigest", "");
	$form["MerchantID"] = get_setting_value($pass_data, "MerchantID", "");
	$form["Amount"] = get_setting_value($pass_data, "Amount", "");
	$form["CurrencyCode"] = get_setting_value($pass_data, "CurrencyCode", "");
	$form["EchoAVSCheckResult"] = get_setting_value($pass_data, "EchoAVSCheckResult", "");
	$form["EchoCV2CheckResult"] = get_setting_value($pass_data, "EchoCV2CheckResult", "");
	$form["EchoThreeDSecureAuthenticationCheckResult"] = get_setting_value($pass_data, "EchoThreeDSecureAuthenticationCheckResult", "");
	$form["EchoCardType"] = get_setting_value($pass_data, "EchoCardType", "");
	$form["AVSOverridePolicy"] = get_setting_value($pass_data, "AVSOverridePolicy", "");
	$form["CV2OverridePolicy"] = get_setting_value($pass_data, "CV2OverridePolicy", "");
	$form["ThreeDSecureOverridePolicy"] = get_setting_value($pass_data, "ThreeDSecureOverridePolicy", "");
	$form["OrderID"] = get_setting_value($pass_data, "OrderID", "");
	$form["TransactionType"] = get_setting_value($pass_data, "TransactionType", "");
	$form["TransactionDateTime"] = date("Y-m-d H:i:s P");
	$form["CallbackURL"] = get_setting_value($pass_data, "CallbackURL", "");
	$form["OrderDescription"] = get_setting_value($pass_data, "OrderDescription", "");
	$form["CustomerName"] = get_setting_value($pass_data, "CustomerName", "");
	$form["Address1"] = get_setting_value($pass_data, "Address1", "");
	$form["Address2"] = get_setting_value($pass_data, "Address2", "");
	$form["Address3"] = get_setting_value($pass_data, "Address3", "");
	$form["Address4"] = get_setting_value($pass_data, "Address4", "");
	$form["City"] = get_setting_value($pass_data, "City", "");
	$form["State"] = get_setting_value($pass_data, "State", "");
	$form["PostCode"] = get_setting_value($pass_data, "PostCode", "");
	$form["CountryCode"] = get_setting_value($pass_data, "CountryCode", "");
	$form["EmailAddress"] = get_setting_value($pass_data, "EmailAddress", "");
	$form["PhoneNumber"] = get_setting_value($pass_data, "PhoneNumber", "");
	$form["EmailAddressEditable"] = get_setting_value($pass_data, "EmailAddressEditable", "");
	$form["PhoneNumberEditable"] = get_setting_value($pass_data, "PhoneNumberEditable", "");
	$form["CV2Mandatory"] = get_setting_value($pass_data, "CV2Mandatory", "");
	$form["Address1Mandatory"] = get_setting_value($pass_data, "Address1Mandatory", "");
	$form["CityMandatory"] = get_setting_value($pass_data, "CityMandatory", "");
	$form["PostCodeMandatory"] = get_setting_value($pass_data, "PostCodeMandatory", "");
	$form["StateMandatory"] = get_setting_value($pass_data, "StateMandatory", "");
	$form["CountryMandatory"] = get_setting_value($pass_data, "CountryMandatory", "");
	$form["ResultDeliveryMethod"] = get_setting_value($pass_data, "ResultDeliveryMethod", "");
	$form["ServerResultURL"] = get_setting_value($pass_data, "ServerResultURL", "");
	$form["PaymentFormDisplaysResult"] = get_setting_value($pass_data, "PaymentFormDisplaysResult", "");
	$form["ServerResultURLCookieVariables"] = get_setting_value($pass_data, "ServerResultURLCookieVariables", "");
	$form["ServerResultURLFormVariables"] = get_setting_value($pass_data, "ServerResultURLFormVariables", "");
	$form["ServerResultURLQueryStringVariables"] = get_setting_value($pass_data, "ServerResultURLQueryStringVariables", "");

	// populate params array with form values
	foreach ($form as $param_name => $param_value) {
		$params[$param_name] = $param_value;
	}

	// build HashDigest parameter
	$hash_params = array(
		"PreSharedKey" => true, 
		"MerchantID" => true, 
		"Password" => true, 
		"Amount" => true, 
		"CurrencyCode" => true, 
		"EchoAVSCheckResult" => false, 
		"EchoCV2CheckResult" => false,
		"EchoThreeDSecureAuthenticationCheckResult" => false,
		"EchoCardType" => false,
		"AVSOverridePolicy" => false,
		"CV2OverridePolicy" => false,
		"ThreeDSecureOverridePolicy" => false, 
		"OrderID" => true, 
		"TransactionType" => true, 
		"TransactionDateTime" => true,
		"CallbackURL" => true, 
		"OrderDescription" => true,
		"CustomerName" => true, 
		"Address1" => true,
		"Address2" => true, 
		"Address3" => true,
		"Address4" => true, 
		"City" => true,
		"State" => true, 
		"PostCode" => true,
		"CountryCode" => true, 
		"EmailAddress" => false,
		"PhoneNumber" => false, 
		"EmailAddressEditable" => false,
		"PhoneNumberEditable" => false, 
		"CV2Mandatory" => false,
		"Address1Mandatory" => false, 
		"CityMandatory" => false,
		"PostCodeMandatory" => false, 
		"StateMandatory" => false,
		"CountryMandatory" => false, 
		"ResultDeliveryMethod" => true,
		"ServerResultURL" => false, 
		"PaymentFormDisplaysResult" => false,
		"ServerResultURLCookieVariables" => false, 
		"ServerResultURLFormVariables" => false, 
		"ServerResultURLQueryStringVariables" => false,
	);

	// check if all parameters should be used to build hash
	foreach ($hash_params as $param_name => $param_value) {
		$pass_param = get_setting_value($pass_parameters, $param_name, "");
		if (!$pass_param && !$param_value) {
			unset($hash_params[$param_name]);
		}
	}

	$hash_string = "";
	foreach ($hash_params as $param_name => $param_value) {
		if ($hash_string) { $hash_string .= "&"; }
		$hash_string .= $param_name."=".$params[$param_name];
	}

	$form["HashDigest"] = sha1($hash_string);

	// check if all parameters should be passed
	foreach ($form as $param_name => $param_value) {
		$pass_param = get_setting_value($pass_parameters, $param_name, "");
		if (!$pass_param) {
			unset($form[$param_name]);
		}
	}


/* Debug information 
	echo "Hash String:<hr>";
	echo $hash_string;
	echo "<br><br>HashDigest:<hr>";
	echo $form["HashDigest"];
	echo "<br><br>Form Parameters:<hr>";
	foreach ($form as $param_name => $param_value) {
		echo "$param_name=$param_value<br>";
	} //*/


	$t = new VA_Template('.'.$settings["templates_dir"]);
	$t->set_file("main","payment.html");
	$goto_payment_message = str_replace("{payment_system}", $user_payment_name, GOTO_PAYMENT_MSG);
	$goto_payment_message = str_replace("{button_name}", CONTINUE_BUTTON, $goto_payment_message);
	$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);
	$t->set_var("payment_url", $payment_url);
	$t->set_var("submit_method", "post");
	foreach ($form as $parameter_name => $parameter_value) {
		$t->set_var("parameter_name", $parameter_name);
		$t->set_var("parameter_value", $parameter_value);
		$t->parse("parameters", true);
	}
	$t->sparse("submit_payment", false);
	$t->pparse("main");

	exit;
?>