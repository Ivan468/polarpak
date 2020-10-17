<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  paypal_payflow_pro_express.php                           ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * PayPal Payflow Pro Express (www.paypal.com) transaction handler by http://www.viart.com/
 */

	$is_admin_path = true;
	$root_folder_path = "../";
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/order_items.php");
	include_once($root_folder_path . "includes/parameters.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");

	$vc = get_session("session_vc");
	$order_id = get_session("session_order_id");

	$order_errors = check_order($order_id, $vc);
	if ($order_errors) {
		echo $order_errors;
		exit;
	}

	$payment_parameters = array();
	$pass_parameters = array();
	$post_parameters = '';
	$pass_data = array();
	$variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);

	$invnum = (isset($payment_parameters['INVNUM']))? $payment_parameters['INVNUM']: "";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];

	$post_parameters = "";
	$error_message = "";

	if (isset($payment_parameters['USER'])){
		$post_parameters .= 'USER=' . urlencode($payment_parameters['USER']);
	}
	if (isset($payment_parameters['VENDOR'])){
		$post_parameters .= (strlen($post_parameters))? "&": "";
		$post_parameters .= 'VENDOR=' . urlencode($payment_parameters['VENDOR']);
	}
	if (isset($payment_parameters['PARTNER'])){
		$post_parameters .= (strlen($post_parameters))? "&": "";
		$post_parameters .= 'PARTNER=' . urlencode($payment_parameters['PARTNER']);
	}
	if (isset($payment_parameters['PWD'])){
		$post_parameters .= (strlen($post_parameters))? "&": "";
		$post_parameters .= 'PWD=' . urlencode($payment_parameters['PWD']);
	}
	$last_key = "";
	foreach ($pass_data as $key => $value) {
		if (strtoupper($key) != strtoupper($last_key)){
			$post_parameters .= (strlen($post_parameters))? "&": "";
			$post_parameters .= strtoupper($key) . '=' . ($value);
			$last_key = $key;
		}
	}

	$ch = curl_init();
	if ($ch) {
		curl_setopt($ch, CURLOPT_URL, $payment_parameters['Advanced_URL']);
		curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 90);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_parameters);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE);
		curl_setopt($ch, CURLOPT_POST, 1);
		set_curl_options($ch, $payment_parameters);
	
		$payment_response = curl_exec($ch);
		if (curl_errno($ch)) {
			$error_message .= curl_errno($ch) . " - " . curl_error($ch) . "<br>\n";
		} elseif (strlen($payment_response)) {
			parse_str($payment_response, $response_params);
			if (isset($response_params['RESPMSG'])) {
				if (strtolower($response_params['RESPMSG']) == 'approved') {
					if (isset($response_params['TOKEN'])) {
						if (isset($response_params['PayPal_URL']) && strlen($response_params['PayPal_URL'])) {
							$PayPal_URL = $response_params['PayPal_URL'] . urldecode($response_params['TOKEN']);
						} else {
							$PayPal_URL = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=" . urldecode($response_params['TOKEN']);
						}
						header("Location: " . $PayPal_URL);
						exit;
					} else {
						$error_message .= "Can't obtain data for your transaction. Parameter 'TOKEN' is not found." . "<br>\n";
					}
				} else {
					$error_message .= (strlen($response_params['RESPMSG']))? $response_params['RESPMSG'] : "Your transaction was declined!" . "<br>\n";
					$error_message .= (strlen($response_params['RESULT'])) ? " Result code: " . $response_params['RESULT'] . "<br>\n" : "";
					echo $error_message;
				}
			} else {
				$error_message .= "Can't obtain data for your transaction. Parameter 'RESPMSG' is not found." . "<br>\n";
			}
		} else {
			$error_message .= "Can't obtain data for your transaction." . "<br>\n";
		}
		curl_close($ch);
	} else {
		$error_message .= "Can't initialize cURL." . "<br>\n";
	}

?>