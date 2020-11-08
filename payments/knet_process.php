<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  knet_process.php                                         ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * Knet (http://www.knet.com.kw/) transaction handler by www.viart.com
 */

	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/order_items.php");
    include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");
	include_once ($root_folder_path ."includes/parameters.php");

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

	$payment_url = (isset($payment_parameters['payment_url']))? $payment_parameters['payment_url']: 'https://www.knetpay.com.kw/CGW/servlet/PaymentInitHTTPServlet';
	$post_parameters = "id=".urlencode($payment_parameters['id'])."&password=".urlencode($payment_parameters['password'])."&".$post_parameters; 




	$payment_response = "";
	if (function_exists($ch)) {
		$curl_init = true;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $payment_url);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_parameters);
		set_curl_options ($ch, $payment_parameters);

		// send request string to gateway
		$payment_response = curl_exec($ch);
		if (curl_errno($ch)) {
			$error_message = curl_errno($ch)." - ".curl_error($ch);
			die($error_message);
		}
		curl_close($ch);
	} else {
		// use standard fsockopen function

		$parsed_url = parse_url($payment_url);

		// request header 
		$request_header  = "POST ".$parsed_url["path"]." HTTP/1.0\r\n";
		$request_header .= "Host: " . $parsed_url["host"]. "\r\n";
		$request_header .= "Connection: Close" . $eol;
		$request_header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$request_header .= "Content-Length: " . strlen($request_params) . "\r\n\r\n";

		if($ssl == 1) {
			// If possible, securely post back to paypal using HTTPS
			// Your PHP server will need to be SSL enabled
			$fp = fsockopen ("ssl://" . $paypal_url, 443, $errno, $errstr, 30);
		} else {
			$fp = fsockopen ($paypal_url, 80, $errno, $errstr, 30);
		}

		if (!$fp) {
			// HTTP ERROR
			$error_message = "Can't connect to PayPal.";
		} else {
			fputs ($fp, $request_header . $request_params);
			while (!feof($fp)) {
				$res = fgets ($fp, 1024);
			}
		}


	if ($payment_response) {
		$payment_response = trim($payment_response);
		if ($payment_response) {
			if (eregi('!ERROR!',$payment_response)){
				die('Fatal error has occured. '. $payment_response);
			}else{
				$payment_array=explode (':',$payment_response);
				if ($payment_array[1] && $payment_array[2] && $payment_array[3]){
					$form_action_url = $payment_array[1] .':'. $payment_array[2] . ':' . $payment_array[3] . '?PaymentID=' . $payment_array[0];
					$sql  = " UPDATE " . $table_prefix . "orders SET success_message=" . $db->tosql($payment_array[0], TEXT);
					$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
					$db->query($sql);
					header('Location: '.$form_action_url);
					exit;
				}else{
					$error_message = "Not parse response from gateway.";
					die($error_message);
				}
			}
		} else {
			$error_message = "Empty response from gateway. Please check your settings.";
			die($error_message);
		}
	} else {
		$error_message = "Can't initialize cURL.";
		die($error_message);
	}
?>