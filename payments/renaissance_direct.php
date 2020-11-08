<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  renaissance_direct.php                                   ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Renaissance Associates - Direct Post API (www.renaissance-associates.com) transaction handler by http://www.viart.com/
 */

	$pass_data = array();
	foreach ($payment_parameters as $parameter_name => $parameter_value) {
		if (isset($pass_parameters[$parameter_name]) && $pass_parameters[$parameter_name] == 1) {
			$pass_data[$parameter_name] = $parameter_value;
		}
	}

	if (!$advanced_url) {
		$advanced_url = "https://secure.rabankcardgateway.com/api/transact.php";
	}

	$ch = curl_init();
	if ($ch) 
	{
		curl_setopt($ch, CURLOPT_URL, $advanced_url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		set_curl_options($ch, $payment_parameters);

		$payment_response = curl_exec($ch);
		curl_close($ch);
		$payment_response = trim($payment_response);

		$t->set_var("payment_response", $payment_response);

		if ($payment_response) {
			$response_parameters = array();
			$response_parts = explode("&", $payment_response);
			if (sizeof($response_parts) == 1) {
				$error_message = "Bad response from gateway: " . $payment_response;
			} else {

				for($i = 0; $i < sizeof($response_parts); $i++) {
					$response_part = explode('=', $response_parts[$i]);
					$response_parameters[$response_part[0]] = urldecode($response_part[1]);
					$response_parameters[strtolower($response_part[0])] = urldecode($response_part[1]);
				}
				foreach ($response_parameters as $parameter_name => $parameter_value) {
					$t->set_var($parameter_name, $parameter_value);
				}

				$response = get_setting_value($response_parameters, "response", ""); // (1 = Transaction Approved,  2 = Transaction Declined, 3 = Error in transaction data or system error)
				$responsetext = get_setting_value($response_parameters, "responsetext", ""); // Textual response
				$variables["authorization_code"] = get_setting_value($response_parameters, "authcode", "");  // Transaction authorization code
				$transaction_id = get_setting_value($response_parameters, "transactionid", ""); // Payment Gateway transaction id
				$variables["avs_response_code"] = get_setting_value($response_parameters, "avsresponse", ""); // AVS Response Code (See Appendix 1)
				$variables["cvv2_match"] = get_setting_value($response_parameters, "cvvresponse", ""); // CVV Response Code (See Appendix 2)
				$orderid = get_setting_value($response_parameters, "orderid", "");  // The original order id passed in the transaction request.
				$response_code = get_setting_value($response_parameters, "response_code", "");  // Numeric mapping of processor responses (See Appendix 3) 

				// check if transaction approved by payment system
				if (!$response) {
					$error_message = "Can't obtain response code.";
				} else if ($response != 1) {
					$error_message = TRANSACTION_DECLINED_MSG;
					if ($responsetext) {
						$error_message .= " <br>" .$responsetext;
					}
				} else {
					// additional checks if necessary
				}
			}

		} else {
			$error_message = EMPTY_GATEWAY_RESPONSE_MSG;
		}
	} else {
		$error_message = CURL_INIT_ERROR_MSG;
	}



/*************************************************************************
	TEST NOTE:
 *************************************************************************	

	The Payment Gateway demo account can also be used for testing at any
	time.  Please use the following username and password for testing with
	this account:
 
	Username: demo
	Password: password 

	Test transactions can be submitted with the following information:
	Visa:             4111111111111111 
	MasterCard:       5431111111111111 
	DiscoverCard:     6011601160116611 
	American Express: 341111111111111 
	Credit Card Expiration: 10/10
	Amount > 1.00 

	To cause a declined message, pass an amount less than 1.00.
	To trigger a fatal error message, pass an invalid card number.
	To simulate an AVS Match, pass 888 in the address1 field, 77777 for zip.
	To simulate a CVV Match, pass 999 in the cvv field. 
*************************************************************************/

?>