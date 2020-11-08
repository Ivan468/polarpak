<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  payleap.php                                              ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * PayLeap (http://www.payleap.com) transaction handler by ViArt Ltd. (www.viart.com).
 */

	$ch = curl_init();
	if($ch) {
		
		$header = array("MIME-Version: 1.0","Content-type: application/x-www-form-urlencoded","Contenttransfer-encoding: text"); 

		curl_setopt($ch, CURLOPT_URL, $advanced_url);
		curl_setopt($ch, CURLOPT_VERBOSE, 1); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 10); 
		set_curl_options ($ch, $payment_parameters);
		$payment_response = curl_exec($ch);
		if (curl_error($ch)) {
			$error_message = curl_errno($ch)." - ".curl_error($ch);
			return;
		}
		curl_close($ch);
		$payment_response = trim($payment_response);
		if (strlen($payment_response)) {
			if(preg_match_all("/<PNRef>(.*)\<\/PNRef>/Uis", $payment_response, $matches, PREG_SET_ORDER)){
				$transaction_id = $matches[0][1];
			}
			if(preg_match_all("/<AuthCode>(.*)\<\/AuthCode>/Uis", $payment_response, $matches, PREG_SET_ORDER)){
				$variables["authorization_code"] = $matches[0][1];
			}
			if(preg_match_all("/<GetAVSResult>(.*)\<\/GetAVSResult>/Uis", $payment_response, $matches, PREG_SET_ORDER)){
				$variables["avs_response_code"] = $matches[0][1];
			}
			if(preg_match_all("/<GetAVSResultTXT>(.*)\<\/GetAVSResultTXT>/Uis", $payment_response, $matches, PREG_SET_ORDER)){
				$variables["avs_message"] = $matches[0][1];
			}
			if(preg_match_all("/<GetStreetMatchTXT>(.*)\<\/GetStreetMatchTXT>/Uis", $payment_response, $matches, PREG_SET_ORDER)){
				$variables["avs_address_match"] = $matches[0][1];
			}
			if(preg_match_all("/<GetZipMatchTXT>(.*)\<\/GetZipMatchTXT>/Uis", $payment_response, $matches, PREG_SET_ORDER)){
				$variables["avs_zip_match"] = $matches[0][1];
			}
			if(preg_match_all("/<ExtData>(.*)\<\/ExtData>/Uis", $payment_response, $matches, PREG_SET_ORDER)){
				$content = explode(',', $matches[0][1]);
				$response_content = array();
				foreach ($content as $key_value) {
					list ($key, $value) = explode("=", $key_value);
					$response_content[$key] = urldecode($value);
				}
				if(isset($response_content["CardType"])){
					$variables["cc_type"] = $response_content["CardType"];
				}
			}
			if(preg_match_all("/<Result>(.*)\<\/Result>/Uis", $payment_response, $matches, PREG_SET_ORDER)){
				$result_code = $matches[0][1];
				if($result_code != 0){
						$error_message = "Error code:" . $result_code;
						if(preg_match_all("/<Message>(.*)\<\/Message>/Uis", $payment_response, $matches, PREG_SET_ORDER)){
							$error_message .= " " . $matches[0][1];
						}
						if(preg_match_all("/<RespMSG>(.*)\<\/RespMSG>/Uis", $payment_response, $matches, PREG_SET_ORDER)){
							$error_message .= " " . $matches[0][1];
						}
				}
			}else{
				$error_message = "Not parse response.";
			}
		} else {
			$error_message = "Empty response from gateway. Please check your settings."; 
		}
	} else {
		$error_message = "Can't initialize cURL.";
	}
?>