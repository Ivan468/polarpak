<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  payex_check.php                                          ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * PayEx (http://payex.com/) transaction handler by www.viart.com
 */

	$return_action  = get_param("return_action");
	if (strtolower($return_action) == 'cancel') {
		$error_message = "Your transaction has been cancelled.";
	}
	
	$accountNumber = (isset($payment_parameters['accountNumber']))? $payment_parameters['accountNumber']: "";
	$orderRef = get_param("orderRef");
	$encryptionkey = (isset($payment_parameters['encryptionkey']))? $payment_parameters['encryptionkey']: "";

	$params = array(
		'accountNumber' => $accountNumber,
		'orderRef' => $orderRef,
	);
	$hash_params = trim(implode("", $params));
	$hash = md5($hash_params.$encryptionkey);

	$request  = '<?xml version="1.0" encoding="UTF-8"?>'; //<?
	$request .= '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://external.payex.com/PxOrder/">';
	$request .= '<SOAP-ENV:Body>';
	$request .= '<ns1:Complete>';
	$request .= '<ns1:accountNumber>'.xml_escape_string($params['accountNumber']).'</ns1:accountNumber>';
	$request .= '<ns1:orderRef>'.xml_escape_string($orderRef).'</ns1:orderRef>';
	$request .= '<ns1:hash>'.xml_escape_string($hash).'</ns1:hash>';
	$request .= '</ns1:Complete>';
	$request .= '</SOAP-ENV:Body>';
	$request .= '</SOAP-ENV:Envelope>';

	$ch = curl_init();
	if ($ch)
	{
		$header[] = "Content-type: text/xml";
		$header[] = "Accept: text/xml";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_URL, $payment_parameters['payment_url']);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		set_curl_options ($ch, $payment_parameters);
	
		$response = curl_exec ($ch);
		if (curl_errno($ch)){
			$error_message = curl_errno($ch)." - ".curl_error($ch);
			return;
		}
		curl_close ($ch);
	} else {
		$error_message = "Can't initialize cURL.";
		return;
	}
	

	$decode_response = html_entity_decode($response);
	preg_match_all("/<code>(.*)\<\/code>/Uis", $decode_response, $matches, PREG_SET_ORDER);
	$code = (isset($matches[0][1]))?$matches[0][1]:"";
	preg_match_all("/<description>(.*)\<\/description>/Uis", $decode_response, $matches, PREG_SET_ORDER);
	$description = (isset($matches[0][1]))?$matches[0][1]:"";
	preg_match_all("/<errorCode>(.*)\<\/errorCode>/Uis", $decode_response, $matches, PREG_SET_ORDER);
	$errorcode = (isset($matches[0][1]))?$matches[0][1]:"";
	preg_match_all("/<transactionStatus>(.*)\<\/transactionStatus>/Uis", $decode_response, $matches, PREG_SET_ORDER);
	$transactionStatus = (isset($matches[0][1]))?$matches[0][1]:"";
	preg_match_all("/<transactionNumber>(.*)\<\/transactionNumber>/Uis", $decode_response, $matches, PREG_SET_ORDER);
	$transaction_id = (isset($matches[0][1]))?$matches[0][1]:"";
	preg_match_all("/<orderStatus>(.*)\<\/orderStatus>/Uis", $decode_response, $matches, PREG_SET_ORDER);
	$orderStatus = (isset($matches[0][1]))?$matches[0][1]:"";
	preg_match_all("/<transactionErrorCode>(.*)\<\/transactionErrorCode>/Uis", $decode_response, $matches, PREG_SET_ORDER);
	$transactionErrorCode = (isset($matches[0][1]))?$matches[0][1]:"";
	preg_match_all("/<transactionErrorDescription>(.*)\<\/transactionErrorDescription>/Uis", $decode_response, $matches, PREG_SET_ORDER);
	$transactionErrorDescription = (isset($matches[0][1]))?$matches[0][1]:"";
	
	if(strtoupper($code) == "OK" && strtoupper($description) == "OK" && strtoupper($errorcode) == "OK"){
		if(strval($orderStatus)=='0'){
			if(strval($transactionStatus)=='0' || strval($transactionStatus)=='6'){
				if(!strlen($transaction_id)){
					$error_message = "Can't obtain transaction number.";
				}
			}elseif(strval($transactionStatus)=='1' || strval($transactionStatus)=='2' || strval($transactionStatus)=='3'){
				if(strval($transactionStatus)=='1'){ $transaction_description = "Initialize";}
				if(strval($transactionStatus)=='2'){ $transaction_description = "Credit";}
				if(strval($transactionStatus)=='3'){ $transaction_description = "Authorize";}
				$pending_message = "Please waiting this order will be reviewed."." Transaction Status: ".$transactionStatus." (".$transaction_description.")";
			}else{
				$error_message  = '';
				$error_message .= (strlen($transactionErrorDescription))? $transactionErrorDescription: 'No order or transaction is found.';
				$error_message .= " Order Status: ".$orderStatus." Transaction Status: ".$transactionStatus;;
			}
		}elseif(strval($orderStatus)=='1'){
			$pending_message = "Please waiting this order will be reviewed."." Transaction Status: ".$transactionStatus;
		}else{
			$error_message  = '';
			$error_message .= (strlen($transactionErrorDescription))? $transactionErrorDescription: 'No order or transaction is found.';
			$error_message .= " Order Status: ".$orderStatus." Transaction Status: ".$transactionStatus;;
		}
	}else{
		$error_message  = 'code: '.$code.' description: '.$description.' errorcode: '.$errorcode;
		$error_message .= (strlen($transactionErrorDescription))? ' Error Description: '.$transactionErrorDescription: '';
	}
?>