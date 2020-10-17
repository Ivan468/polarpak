<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  paypal_payflow_pro_express_checkout.php                  ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * PayPal Payflow Pro Express Checkout (www.paypal.com) transaction handler by http://www.viart.com/
 */

	$invnum = (isset($payment_parameters['INVNUM']))? $payment_parameters['INVNUM']: "";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];

	$post_parameters = "";
	$error_message = "";

	if (isset($payment_parameters['USER'])) {
		$post_parameters .= 'USER='.urlencode($payment_parameters['USER']);
	}
	if (isset($payment_parameters['VENDOR'])) {
		$post_parameters .= (strlen($post_parameters)) ? "&" : "";
		$post_parameters .= 'VENDOR=' . urlencode($payment_parameters['VENDOR']);
	}
	if (isset($payment_parameters['PARTNER'])) {
		$post_parameters .= (strlen($post_parameters)) ? "&" : "";
		$post_parameters .= 'PARTNER=' . urlencode($payment_parameters['PARTNER']);
	}
	if (isset($payment_parameters['PWD'])) {
		$post_parameters .= (strlen($post_parameters))? "&": "";
		$post_parameters .= 'PWD=' . urlencode($payment_parameters['PWD']);
	}
	if (isset($payment_parameters['TENDER'])) {
		$post_parameters .= (strlen($post_parameters))? "&": "";
		$post_parameters .= 'TENDER=' . urlencode($payment_parameters['TENDER']);
	}
	if (isset($payment_parameters['TRXTYPE'])) {
		$post_parameters .= (strlen($post_parameters))? "&": "";
		$post_parameters .= 'TRXTYPE=' . urlencode($payment_parameters['TRXTYPE']);
	}
	$post_parameters .= (strlen($post_parameters)) ? "&" : "";
	$post_parameters .= 'ACTION=D';
	$post_parameters .= (strlen($post_parameters)) ? "&" : "";
	$post_parameters .= 'TOKEN=' . urlencode(get_param('token'));
	$post_parameters .= (strlen($post_parameters)) ? "&" : "";
	$post_parameters .= 'PAYERID=' . urlencode(get_param('PayerID'));
	$post_parameters .= (strlen($post_parameters)) ? "&" : "";
	$post_parameters .= 'IPADDRESS=' . urlencode(get_ip());
	if (isset($payment_parameters['AMT'])) {
		$post_parameters .= (strlen($post_parameters)) ? "&" : "";
		$post_parameters .= 'AMT=' . urlencode($payment_parameters['AMT']);
	}
	if (isset($payment_parameters['CURRENCY'])) {
		$post_parameters .= (strlen($post_parameters)) ? "&" : "";
		$post_parameters .= 'CURRENCY=' . urlencode($payment_parameters['CURRENCY']);
	}
	if (isset($payment_parameters['INVNUM'])) {
		$post_parameters .= (strlen($post_parameters)) ? "&" : "";
		$post_parameters .= 'INVNUM=' . urlencode($payment_parameters['INVNUM']);
	}
	if (isset($payment_parameters['ORDERDESC'])) {
		$post_parameters .= (strlen($post_parameters)) ? "&" : "";
		$post_parameters .= 'ORDERDESC=' . urlencode($payment_parameters['ORDERDESC']);
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
		} elseif (strlen($payment_response)){
			parse_str($payment_response, $response_params);
			$transaction_id = (isset($response_params['PNREF']))? $response_params['PNREF']: "";
			if (strval($response_params["RESULT"]) == strval("0")) {
				$transaction_id .= (strlen($transaction_id)) ? "" : "Is approved.";
				if (isset($response_params['AVSADDR'])) {
					$pending_message .= ($response_params['AVSADDR'] != "Y") ? "Street information does not match." . "<br>\n" : "";
				}
				if (isset($response_params['AVSZIP'])) {
					$pending_message .= ($response_params['AVSZIP'] != "Y") ? "Zip information does not match." . "<br>\n" : "";
				}
				if (isset($response_params['CVV2MATCH'])) {
					$pending_message .= ($response_params['CVV2MATCH'] != "Y") ? "Cvv2 information does not match." . "<br>\n" : "";
				}
			} else {
				if (isset($response_params['RESPMSG']) && strlen($response_params['RESPMSG'])) {
					$error_message .= $response_params['RESPMSG'] . "<br>\n";
				} else {
					$error_message .= "Your transaction was declined!" . "<br>\n";
				}
				if (strlen($response_params['RESULT'])) {
					$error_message .= " Result code:" . $response_params['RESULT'] . "<br>\n";
				}
			}
		} else {
			$error_message .= "Can't obtain data for your transaction." . "<br>\n";
		}
		curl_close($ch);
	} else {
		$error_message .= "Can't initialize cURL." . "<br>\n";
	}

?>