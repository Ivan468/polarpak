<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  payex_process.php                                        ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * PayEx (http://payex.com/) transaction handler by www.viart.com
 */

	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");

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

	$accountNumber = (isset($payment_parameters['accountNumber']))? $payment_parameters['accountNumber']: "";
	$purchaseOperation = (isset($payment_parameters['purchaseOperation']))? $payment_parameters['purchaseOperation']: "";
	$price = (isset($payment_parameters['price']))? $payment_parameters['price']: "";
	$priceArgList = (isset($payment_parameters['priceArgList']))? $payment_parameters['priceArgList']: "";
	$currency = (isset($payment_parameters['currency']))? $payment_parameters['currency']: "";
	$vat = (isset($payment_parameters['vat']))? $payment_parameters['vat']: 0;
	$orderID = (isset($payment_parameters['orderID']))? $payment_parameters['orderID']: "";
	$productNumber = (isset($payment_parameters['productNumber']))? $payment_parameters['productNumber']: "";
	$description = (isset($payment_parameters['description']))? $payment_parameters['description']: "";
	$clientIPAddress = (isset($payment_parameters['clientIPAddress']))? $payment_parameters['clientIPAddress']: "";
	$clientIdentifier = "USERAGENT=".$_SERVER['HTTP_USER_AGENT'];
	$additionalValues = (isset($payment_parameters['additionalValues']))? $payment_parameters['additionalValues']: "";
	$externalID = (isset($payment_parameters['externalID']))? $payment_parameters['externalID']: "";
	$returnUrl = (isset($payment_parameters['returnUrl']))? $payment_parameters['returnUrl']: "";
	$view = (isset($payment_parameters['view']))? $payment_parameters['view']: "";
	$agreementRef = (isset($payment_parameters['agreementRef']))? $payment_parameters['agreementRef']: "";
	$cancelUrl = (isset($payment_parameters['cancelUrl']))? $payment_parameters['cancelUrl']: "";
	$clientLanguage = (isset($payment_parameters['clientLanguage']))? $payment_parameters['clientLanguage']: "";
	$encryptionkey = (isset($payment_parameters['encryptionkey']))? $payment_parameters['encryptionkey']: "";

	$params = array(
		'accountNumber' => $accountNumber,
		'purchaseOperation' => $purchaseOperation,
		'price' => $price,
		'priceArgList' => $priceArgList,
		'currency' => $currency,
		'vat' => $vat,
		'orderID' => $orderID,
		'productNumber' => $productNumber,
		'description' => $description,
		'clientIPAddress' => $clientIPAddress,
		'clientIdentifier' => $clientIdentifier,
		'additionalValues' => $additionalValues,
		'externalID' => $externalID,
		'returnUrl' => $returnUrl,
		'view' => $view,
		'agreementRef' => $agreementRef,
		'cancelUrl' => $cancelUrl,
		'clientLanguage' => $clientLanguage
	);
	$hash_params = trim(implode("", $params));
	$hash = md5($hash_params.$encryptionkey);

	$request  = '<?xml version="1.0" encoding="UTF-8"?>'; //<?
	$request .= '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://external.payex.com/PxOrder/">';
	$request .= '<SOAP-ENV:Body><ns1:Initialize7>';
	$request .= '<ns1:accountNumber>'.xml_escape_string($params['accountNumber']).'</ns1:accountNumber>';
	$request .= '<ns1:purchaseOperation>'.xml_escape_string($params['purchaseOperation']).'</ns1:purchaseOperation>';
	$request .= '<ns1:price>'.xml_escape_string($params['price']).'</ns1:price>';
	$request .= '<ns1:priceArgList>'.xml_escape_string($params['priceArgList']).'</ns1:priceArgList>';
	$request .= '<ns1:currency>'.xml_escape_string($params['currency']).'</ns1:currency>';
	$request .= '<ns1:vat>'.xml_escape_string($params['vat']).'</ns1:vat>';
	$request .= '<ns1:orderID>'.xml_escape_string($params['orderID']).'</ns1:orderID>';
	$request .= '<ns1:productNumber>'.xml_escape_string($params['productNumber']).'</ns1:productNumber>';
	$request .= '<ns1:description>'.xml_escape_string($params['description']).'</ns1:description>';
	$request .= '<ns1:clientIPAddress>'.xml_escape_string($params['clientIPAddress']).'</ns1:clientIPAddress>';
	$request .= '<ns1:clientIdentifier>'.xml_escape_string($params['clientIdentifier']).'</ns1:clientIdentifier>';
	$request .= '<ns1:additionalValues>'.xml_escape_string($params['additionalValues']).'</ns1:additionalValues>';
	$request .= '<ns1:externalID>'.xml_escape_string($params['externalID']).'</ns1:externalID>';
	$request .= '<ns1:returnUrl>'.xml_escape_string($params['returnUrl']).'</ns1:returnUrl>';
	$request .= '<ns1:view>'.xml_escape_string($params['view']).'</ns1:view>';
	$request .= '<ns1:agreementRef>'.xml_escape_string($params['agreementRef']).'</ns1:agreementRef>';
	$request .= '<ns1:cancelUrl>'.xml_escape_string($params['cancelUrl']).'</ns1:cancelUrl>';
	$request .= '<ns1:clientLanguage>'.xml_escape_string($params['clientLanguage']).'</ns1:clientLanguage>';
	$request .= '<ns1:hash>'.xml_escape_string($hash).'</ns1:hash>';
	$request .= '</ns1:Initialize7>';
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
			echo curl_errno($ch)." - ".curl_error($ch);
			exit;
		}
		curl_close ($ch);
	} else {
		echo "Can't initialize cURL.";
		exit;
	}
	
	$decode_response = html_entity_decode($response);
	preg_match_all("/<code>(.*)\<\/code>/Uis", $decode_response, $matches, PREG_SET_ORDER);
	$code = (isset($matches[0][1]))?$matches[0][1]:"";
	preg_match_all("/<description>(.*)\<\/description>/Uis", $decode_response, $matches, PREG_SET_ORDER);
	$description = (isset($matches[0][1]))?$matches[0][1]:"";
	preg_match_all("/<errorCode>(.*)\<\/errorCode>/Uis", $decode_response, $matches, PREG_SET_ORDER);
	$errorcode = (isset($matches[0][1]))?$matches[0][1]:"";
	preg_match_all("/<redirectUrl>(.*)\<\/redirectUrl>/Uis", $decode_response, $matches, PREG_SET_ORDER);
	$redirecturl = (isset($matches[0][1]))?$matches[0][1]:"";
	
	if(strtoupper($code) == "OK" && strtoupper($description) == "OK" && strtoupper($errorcode) == "OK"){
		header('Location: '.$redirecturl);
	}else{
		echo 'code: '.$code."<br>\n"; 
		echo 'description: '.$description."<br>\n"; 
		echo 'errorCode: '.$errorcode."<br>\n"; 
	}
	exit;
?>