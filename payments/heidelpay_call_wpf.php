<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  heidelpay_call_wpf.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * HeidelPay (http://www.heidelpay.de) transaction handler by ViArt Ltd. (www.viart.com).
 */
	$response_code = array(
		"2010" => "Parameter PRESENTATION.AMOUNT missing or not a number",
		"2030" => "Parameter PRESENTATION.CURRENCY missing",
		"2020" => "Parameter PAYMENT.CODE missing or wrong",
		"3010" => "Parameter FRONTEND.MODE missing or wrong",
		"3020" => "Parameter FRONTEND.NEXT_TARGET wrong",
		"3040" => "Parameter FRONTEND.LANGUAGE wrong",
		"3050" => "Parameter FRONTEND. RESPONSE_URL wrong",
		"3070" => "Parameter FRONTEND. POPUP wrong",
		"3090" => "Wrong FRONTEND.LINK parameter combination",
		"4020" => "Parameter SECURITY.IP missing or wrong",
		"4030" => "Parameter SECURITY.SENDER missing or wrong",
		"4040" => "Wrong User/Password combination",
		"4050" => "Parameter USER.LOGIN missing or wrong",
		"4060" => "Parameter USER.PWD missing or wrong",
		"4070" => "Parameter TRANSACTION.CHANNEL missing or wrong",
		"5010" => "Parameter ACCOUNT.COUNTRY is wrong or missing for WPF_LIGHT mode and payment code starts with DD"
	);

	$ch = curl_init();
	if($ch) {
		curl_setopt($ch, CURLOPT_URL, $advanced_url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_USERAGENT, "php ctpepost");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
		set_curl_options ($ch, $payment_parameters);
		$payment_response = curl_exec($ch);
		if (curl_error($ch)) {
			$error_message = curl_errno($ch)." - ".curl_error($ch);
		}
		curl_close($ch);
		$payment_response = trim($payment_response);
		if(!strlen($error_message)){
			if (strlen($payment_response)) {
				$content = explode('&', $payment_response);
				foreach ($content as $key_value) {
					list ($key, $value) = explode("=", $key_value);
					$response[$key] = urldecode($value);
				}
				$debug_mode = isset($payment_parameters['debug_mode'])? $payment_parameters['debug_mode']: false;
				if($debug_mode && isset($response['FRONTEND.REDIRECT_URL'])){
					header('Location: '.urldecode($response['FRONTEND.REDIRECT_URL']));
					exit;
				}
				if(strtoupper($response['POST.VALIDATION']) == "ACK"){
					if(isset($response['PROCESSING.RESULT'])){
						$transaction_id = (isset($response['IDENTIFICATION.UNIQUEID']))? $response['IDENTIFICATION.UNIQUEID']: "";
						if($response['PROCESSING.RESULT'] != "ACK"){
							$error_message = "Status Code: ".$response['PROCESSING.STATUS.CODE'].". ".$response['PROCESSING.REASON'].", ".$response['PROCESSING.RETURN'];
						}else{
							if(strval($response['PROCESSING.STATUS.CODE']) == strval("00")){
								$success_message = "Status Code: ".$response['PROCESSING.STATUS.CODE'].". ".$response['PROCESSING.REASON'].", ".$response['PROCESSING.RETURN'];
							}elseif(strval($response['PROCESSING.STATUS.CODE']) == strval("20") || strval($response['PROCESSING.STATUS.CODE']) == strval("50") || strval($response['PROCESSING.STATUS.CODE']) == strval("60") || strval($response['PROCESSING.STATUS.CODE']) == strval("70")){
								$error_message = "Status Code: ".$response['PROCESSING.STATUS.CODE'].". ".$response['PROCESSING.REASON'].", ".$response['PROCESSING.RETURN'];
							}else{
								$pending_message = "Status Code: ".$response['PROCESSING.STATUS.CODE'].". ".$response['PROCESSING.REASON'].", ".$response['PROCESSING.RETURN'];
							}
						}
					}elseif(isset($response['FRONTEND.REDIRECT_URL'])){
						header('Location: '.urldecode($response['FRONTEND.REDIRECT_URL']));
						exit;
					}else{
						$error_message = 'REDIRECT_URL is not find.';
					}
				}else{
					$error_message = 'VALIDATION is not ok. VALIDATION CODE: '.$response['POST.VALIDATION'];
					if(isset($response_code[$response['POST.VALIDATION']])){
						$error_message .= " " . $response_code[$response['POST.VALIDATION']];
					}
				}
			} else {
				$error_message = "Empty response from gateway. Please check your settings."; 
			}
		}
	} else {
		$error_message = "Can't initialize cURL.";
	}
?>