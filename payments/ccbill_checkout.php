<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  ccbill_checkout.php                                      ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * CCBill (http://ccbill.com/) transaction handler by www.viart.com
 */
	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."includes/date_functions.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");
	include_once ($root_folder_path ."payments/google_functions.php");

	$vc = get_session("session_vc");
	$order_id = get_session("session_order_id");

	$order_errors = check_order($order_id, $vc);
	if($order_errors) {
		echo $order_errors;
		exit;
	}
	
	$payment_parameters = array();
	$pass_parameters = array();
	$post_parameters = '';
	$pass_data = array();
	$variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);
	
	$payment_url = get_setting_value($payment_parameters, "payment_url", "https://bill.ccbill.com/jpost/signup.cgi");
	$salt = (isset($payment_parameters['salt']))? $payment_parameters['salt']: "";
	$formPeriod = (isset($payment_parameters['formPeriod']))? $payment_parameters['formPeriod']: "";
	$currencyCode = (isset($payment_parameters['currencyCode']))? $payment_parameters['currencyCode']: "";
	$formPrice = (isset($payment_parameters['formPrice']))? $payment_parameters['formPrice']: "";
	$formRecurringPrice = (isset($payment_parameters['formRecurringPrice']))? $payment_parameters['formRecurringPrice']: "";
	$formRecurringPeriod = (isset($payment_parameters['formRecurringPeriod']))? $payment_parameters['formRecurringPeriod']: "";
	$formRebills = (isset($payment_parameters['formRebills']))? $payment_parameters['formRebills']: "";
	
	$str = $formPrice.$formPeriod.$currencyCode.$salt;
	$formDigest = md5($str);
	$pass_data["formDigest"] = $formDigest;

	$t = new VA_Template('.'.$settings["templates_dir"]);
	$t->set_file("main","payment.html");
	$t->set_var("CHARSET", "utf-8");

	$payment_name = get_setting_value($variables, "payment_name", "");
	$user_payment_name = get_setting_value($variables, "user_payment_name", $payment_name);
	$goto_payment_message = str_replace("{payment_system}", $user_payment_name, GOTO_PAYMENT_MSG);
	$goto_payment_message = str_replace("{button_name}", CONTINUE_BUTTON, $goto_payment_message);

	$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);
	$t->set_var("payment_url", $payment_url);
	$t->set_var("submit_method", "post");
	foreach ($pass_data as $parameter_name => $parameter_value) {
		$t->set_var("parameter_name", htmlspecialchars($parameter_name));
		$t->set_var("parameter_value", htmlspecialchars($parameter_value));
		$t->parse("parameters", true);
	}

	$t->sparse("submit_payment", false);
	$t->pparse("main");
