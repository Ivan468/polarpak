<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  dhd_process.php                                          ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * Segpay DHD (http://dhdmedia.com/) transaction handler by www.viart.com
 */

	$cust_email = (isset($payment_parameters['cust_email']))? $payment_parameters['cust_email']: "";
	$cust_id_ext = (isset($payment_parameters['cust_id_ext']))? $payment_parameters['cust_id_ext']: "";
	$httl_timestamp = (isset($payment_parameters['httl']))? $payment_parameters['httl']: "";
	$hver = (isset($payment_parameters['hver']))? $payment_parameters['hver']: "";
	$inv_id_ext = (isset($payment_parameters['inv_id_ext']))? $payment_parameters['inv_id_ext']: "";
	$secret = (isset($payment_parameters['secret']))? $payment_parameters['secret']: "";
	$inv_value_requested = (isset($payment_parameters['inv_value_requested']))? $payment_parameters['inv_value_requested']: "";
	$sub_username = (isset($payment_parameters['sub_username']))? $payment_parameters['sub_username']: "";
	$curr_id = (isset($payment_parameters['curr_id']))? $payment_parameters['curr_id']: "";
	$curr_id_requested = (isset($payment_parameters['curr_id_requested']))? $payment_parameters['curr_id_requested']: "";

	$httl_mask = array("YYYY", "-", "MM", "-", "DD", "T", "HH", ":", "mm", ":", "ss", "Z");
	$httl  = va_date($httl_mask,$httl_timestamp);

	$post_params = str_replace("httl=".$httl_timestamp, "httl=".$httl, $post_params);
	$payment_url = $advanced_url . "?" . $post_params;
	$secret = sha1("$cust_email||$cust_id_ext||$httl||$hver||$inv_id_ext||$inv_value_requested||$sub_username||$curr_id_requested||$secret"); 
	$payment_url .= "&secret=" . $secret;

	header("Location: " . $payment_url);
	exit;
?>