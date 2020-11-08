<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  ogone_direct_l.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Ogone DireckLink (www.ogone.com) transaction handler by www.viart.com
 */

	$ch = curl_init();
	if ($ch)
	{
		if(preg_match_all("/ED=\d{2}%2F\d{2}/Uis", $post_params, $matches, PREG_SET_ORDER)){
			$expiry_date = str_replace('%2F', '/', $matches[0][0]);
			$post_params = str_replace($matches[0][0], $expiry_date, $post_params);
		}
		$SHASign = sha1($payment_parameters["orderID"].$payment_parameters["amount"].$payment_parameters["currency"].$payment_parameters["CARDNO"].$payment_parameters["PSPID"].$payment_parameters["operation"].$payment_parameters["Signature"]);
		$post_params .= "&SHASign=".$SHASign;
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_URL, $advanced_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
		set_curl_options ($ch, $payment_parameters);

		$payment_response = curl_exec($ch);

		if (curl_error($ch)) {
			$error_message = curl_error($ch);
		}

		curl_close($ch);
		$t->set_var("payment_response", $payment_response);
		if ($payment_response) {
			$response_parameters = array();
			$response_parts = explode(chr(10), $payment_response);
			if (sizeof($response_parts) == 1) {
				$error_message = "Bad response from gateway: " . $payment_response;
			} else {

				for($i = 0; $i < sizeof($response_parts); $i++) {
					$response_part = explode('=', $response_parts[$i], 2);
					if(isset($response_part[1])){
						$response_parameters[trim($response_part[0])] = trim(trim($response_part[1]),'"');
						$response_parameters[strtolower(trim($response_part[0]))] = trim(trim($response_part[1]),'"');
					}
				}
				foreach ($response_parameters as $parameter_name => $parameter_value) {
					$t->set_var($parameter_name, $parameter_value);
				}
				set_session("session_payment_response", $response_parameters);

				// check if transaction approved by payment system
				$transaction_id = isset($response_parameters["payid"]) ? $response_parameters["payid"] : "";
				$ncstatus = isset($response_parameters["ncstatus"]) ? $response_parameters["ncstatus"] : "";
				$ncerror = isset($response_parameters["ncerror"]) ? $response_parameters["ncerror"] : "";
				$ncerrorplus = isset($response_parameters["ncerrorplus"]) ? $response_parameters["ncerrorplus"] : "";
				$acceptance = isset($response_parameters["acceptance"]) ? $response_parameters["acceptance"] : "";
				$status = isset($response_parameters["status"]) ? $response_parameters["status"] : "";
				$cvccheck = isset($response_parameters["cvccheck"]) ? $response_parameters["cvccheck"] : "";
				$aavcheck = isset($response_parameters["aavcheck"]) ? $response_parameters["aavcheck"] : "";
				$alias = isset($response_parameters["alias"]) ? $response_parameters["alias"] : "";
				$variables["authorization_code"] = $acceptance;
				$variables["avs_address_match"] = $aavcheck;
				$variables["cvv2_match"] = $cvccheck;

				if(!strlen($status) && !strlen($ncstatus) && !strlen($ncerror)){
					$error_message = "Can't obtain status authorization parameter.";
				}elseif ($status == "9" && $ncstatus == "0" && $ncerror == "0") {
					if(!strlen($transaction_id)){
						$pending_message = "Can't obtain PAYID parameter. This order will be reviewed manually. STATUS:".$status." NCERROR:".$ncerror;
						$pending_message .= (strlen($ncerrorplus)) ? " ".$ncerrorplus : "";
					}
				}elseif ($status == "5" && $ncstatus == "0" && $ncerror == "0") {
					$pending_message = "This order is authorized. STATUS:".$status." NCERROR:".$ncerror;
					$pending_message .= (strlen($ncerrorplus)) ? " ".$ncerrorplus : "";
					if(!strlen($transaction_id)){
						$pending_message = "Can't obtain PAYID parameter. This order will be reviewed manually. STATUS:".$status." NCERROR:".$ncerror;
						$pending_message .= (strlen($ncerrorplus)) ? " ".$ncerrorplus : "";
					}
				}elseif ($status == "51") {
					$pending_message = "Waiting for authorization. This order will be reviewed manually. STATUS:".$status." NCERROR:".$ncerror;
					$pending_message .= (strlen($ncerrorplus)) ? " ".$ncerrorplus : "";
				}elseif ($status == "52" || $status == "92") {
					$pending_message = "Authorization or payment uncertain. This order will be reviewed manually. STATUS:".$status." NCERROR:".$ncerror;
					$pending_message .= (strlen($ncerrorplus)) ? " ".$ncerrorplus : "";
				}else{
					$error_message = "STATUS:".$status." NCERROR:".$ncerror;
					$error_message .= (strlen($ncerrorplus)) ? " ".$ncerrorplus : " Some errors occurred during handling your transaction.";
				}
			}

		} else {
			$error_message = "Can't obtain data for your transaction.";
		}
	} else {
		$error_message = "Can't initialize cURL.";
	}

?>