<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  nmi_check.php                                            ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * NMI (http://www.nmi.com) transaction handler by ViArt Ltd. (www.viart.com).
 */
	
	$token_id = get_param("token-id");

	if(!strlen($token_id)){
		$pending_message = "Can't obtain transaction token parameter. This order will be reviewed manually.";
		return;
	}

	$xml  = '<?xml version="1.0" encoding="UTF-8"?>'."\n"; //<?
	$xml .= '<complete-action>'."\n";
	$xml .= '	<api-key>'.xml_escape_string($payment_parameters['api-key']).'</api-key>'."\n";
	$xml .= '	<token-id>'.xml_escape_string($token_id).'</token-id>'."\n";
	$xml .= '</complete-action>'."\n";

	$ch = curl_init();
	if($ch) {
		$headers = array();
		$headers[] = "Content-type: text/xml";
		curl_setopt($ch, CURLOPT_URL, $variables['advanced_url']);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_PORT, 443);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		set_curl_options ($ch, $payment_parameters);
		$payment_response = curl_exec($ch);
		if (curl_error($ch)) {
			$error_message = curl_errno($ch)." - ".curl_error($ch);
		}
		curl_close($ch);
		$payment_response = trim($payment_response);
		if(!strlen($error_message)){
			if (strlen($payment_response)) {
				if(preg_match_all("/<response>(.*)\<\/response>/Uis", $payment_response, $matches, PREG_SET_ORDER)){
					$result = (preg_match_all("/<result>(.*)\<\/result>/Uis", $matches[0][1], $value, PREG_SET_ORDER))? $value[0][1]: "";
					$result_text = (preg_match_all("/<result-text>(.*)\<\/result-text>/Uis", $matches[0][1], $value, PREG_SET_ORDER))? $value[0][1]: "";
					$transaction_id = (preg_match_all("/<transaction-id>(.*)\<\/transaction-id>/Uis", $matches[0][1], $value, PREG_SET_ORDER))? $value[0][1]: "";
					$result_code = (preg_match_all("/<result-code>(.*)\<\/result-code>/Uis", $matches[0][1], $value, PREG_SET_ORDER))? $value[0][1]: "";
					$payment_amount = (preg_match_all("/<amount>(.*)\<\/amount>/Uis", $matches[0][1], $value, PREG_SET_ORDER))? $value[0][1]: "";
					$payment_currency = (preg_match_all("/<currency>(.*)\<\/currency>/Uis", $matches[0][1], $value, PREG_SET_ORDER))? $value[0][1]: "";
					$return_order_id = (preg_match_all("/<order-id>(.*)\<\/order-id>/Uis", $matches[0][1], $value, PREG_SET_ORDER))? $value[0][1]: "";
					$variables["authorization_code"] = (preg_match_all("/<authorization-code>(.*)\<\/authorization-code>/Uis", $matches[0][1], $value, PREG_SET_ORDER))? $value[0][1]: "";
					$variables["avs_message"] = (preg_match_all("/<avs-result>(.*)\<\/avs-result>/Uis", $matches[0][1], $value, PREG_SET_ORDER))? $value[0][1]: "";
					$variables["cvv2_match"] = (preg_match_all("/<cvv-result>(.*)\<\/cvv-result>/Uis", $matches[0][1], $value, PREG_SET_ORDER))? $value[0][1]: "";
					$variables["secure_3d_status"] = (preg_match_all("/<cardholder-auth>(.*)\<\/cardholder-auth>/Uis", $matches[0][1], $value, PREG_SET_ORDER))? $value[0][1]: "";
					$variables["secure_3d_cavv"] = (preg_match_all("/<cavv>(.*)\<\/cavv>/Uis", $matches[0][1], $value, PREG_SET_ORDER))? $value[0][1]: "";

					$error_message = check_payment($variables['order_id'], $payment_amount, $payment_currency); 
					if($return_order_id != $variables['order_id']){
						$error_message = "order-id does not match.";
					}
					if($result != 1){
						$error_message = "result-code: " . $result_code . ". " . $result_text;
					}
				}else{
					$error_message = "Not parse response.";
				}
			} else {
				$error_message = "Empty response from gateway. Please check your settings."; 
			}
		}
	} else {
		$error_message = "Can't initialize cURL.";
	}
?>