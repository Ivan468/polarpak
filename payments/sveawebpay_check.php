<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  sveawebpay_check.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * SveaWebPay (http://www.sveawebpay.se/) transaction handler by www.viart.com
 */

	$md5 = get_param("MD5");
	$success = get_param("Success");
	$return_action = get_param("return_action");
	$url = rtrim($settings["site_url"], "/")."/".ltrim(get_request_uri(), "/");
	$url = str_replace('&MD5='.$md5, '', $url);

	if($return_action == 'cancel'){
		$error_message = "Your transaction has been cancelled.";
		return;
	}

	if(!strlen($md5) && !strlen($success)){
		$pending_message = "There is no answer from payment gateway. This order will be reviewed manually.";
		return;
	}

	$mymd5 = md5($url.$payment_parameters['password']);
	if(strtoupper($mymd5) != strtoupper($md5)){
		$error_message = "'Hash' parameter has wrong value.";
		return;
	}

	if(strtoupper($success) != 'TRUE'){
		$error_message = "Your transaction has been declined.";
		return;
	}

?>