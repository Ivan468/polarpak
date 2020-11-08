<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  ccbill_process.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
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
	
	$payment_url = (isset($payment_parameters['payment_url']))? $payment_parameters['payment_url']: "";
	$salt = (isset($payment_parameters['salt']))? $payment_parameters['salt']: "";
	$formPeriod = (isset($payment_parameters['formPeriod']))? $payment_parameters['formPeriod']: "";
	$currencyCode = (isset($payment_parameters['currencyCode']))? $payment_parameters['currencyCode']: "";
	$formPrice = (isset($payment_parameters['formPrice']))? $payment_parameters['formPrice']: "";
	$formRecurringPrice = (isset($payment_parameters['formRecurringPrice']))? $payment_parameters['formRecurringPrice']: "";
	$formRecurringPeriod = (isset($payment_parameters['formRecurringPeriod']))? $payment_parameters['formRecurringPeriod']: "";
	$formRebills = (isset($payment_parameters['formRebills']))? $payment_parameters['formRebills']: "";
	
	$str = $formPrice.$formPeriod.$currencyCode.$salt;
	$formDigest = md5($str);

	$payment_url .= "?" . $post_parameters . "&formDigest=" . $formDigest;
	
	header("Location: " . $payment_url);
	exit;
