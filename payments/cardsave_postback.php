<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  cardsave_postback.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * CardSave postback handler (http://www.cardsave.net/) transaction handler by www.viart.com
 */
	/* DEBUG information
	$check_params = "";
	foreach ($_POST as $param_name => $param_value) {
		$check_params .= "$param_name = $param_value\n";
	}
	mail ("enquries@viart.com", "CardSave Postback", $check_params);//*/


	$HashDigest = get_param("HashDigest");
	$StatusCode = get_param("StatusCode");
	$Message = get_param("Message");
	$CrossReference = get_param("CrossReference");

	$post_params = array(
		"HashDigest" => "",
		"MerchantID" => "",
		"StatusCode" => "",
		"Message" => "",
		"PreviousStatusCode" => "",
		"PreviousMessage" => "",
		"CrossReference" => "",
		"AddressNumericCheckResult" => "",
		"PostCodeCheckResult" => "",
		"CV2CheckResult" => "",
		"ThreeDSecureAuthenticationCheckResult" => "",
  
		"CardType" => "",
		"CardClass" => "",
		"CardIssuer" => "",
		"CardIssuerCountryCode" => "",
		"Amount" => "",
		"CurrencyCode" => "",
		"OrderID" => "",
		"TransactionType" => "",
		"TransactionDateTime" => "",
		"OrderDescription" => "",
  
		"CustomerName" => "",
		"Address1" => "",
		"Address2" => "",
		"Address3" => "",
		"Address4" => "",
		"City" => "",
		"State" => "",
		"PostCode" => "",
		"CountryCode" => "",
		"EmailAddress" => "",
		"PhoneNumber" => "",
	);

	// build HashDigest parameter
	$hash_params = array(
		"PreSharedKey" => true, 
		"MerchantID" => true, 
		"Password" => true, 
		"StatusCode" => true, 
		"Message" => true, 
		"PreviousStatusCode" => true, 
		"PreviousMessage" => true, 
		"CrossReference" => true, 


		"AddressNumericCheckResult" => false, 
		"PostCodeCheckResult" => false, 
		"CV2CheckResult" => false, 
		"ThreeDSecureCheckResult" => false, 
		"ThreeDSecureAuthenticationCheckResult" => false, 

		"CardType" => false, 
		"CardClass" => false, 
		"CardIssuer" => false, 
		"CardIssuerCountryCode" => false, 

		"Amount" => true, 
		"CurrencyCode" => true, 
		"OrderID" => true, 
		"TransactionType" => true, 
		"TransactionDateTime" => true,
		"OrderDescription" => true,
		"CustomerName" => true, 
		"Address1" => true,
		"Address2" => true, 
		"Address3" => true,
		"Address4" => true, 
		"City" => true,
		"State" => true, 
		"PostCode" => true,
		"CountryCode" => true, 
		"EmailAddress" => false,
		"PhoneNumber" => false, 
	);

	// payment params
	$PreSharedKey = get_setting_value($payment_parameters, "PreSharedKey", "");
	$Password = get_setting_value($payment_parameters, "Password", "");

	// prepare params from settings and POST
	$params = array(
		"PreSharedKey" => $PreSharedKey,
		"Password" => $Password,
	);
	foreach ($_POST as $param_name => $param_value) {
		$params[$param_name] = $param_value;
	}

	// check if all parameters should be used to build hash
	foreach ($hash_params as $param_name => $param_value) {
		$param_exists = isset($params[$param_name]);
		if (!$param_exists && !$param_value) {
			unset($hash_params[$param_name]);
		} else if (!$param_exists) {
			$params[$param_name] = "";
		}
	}

	// build hash string
	$hash_string = "";
	foreach ($hash_params as $param_name => $param_value) {
		if ($hash_string) { $hash_string .= "&"; }
		$hash_string .= $param_name."=".$params[$param_name];
	}
	$OurHashDigest = sha1($hash_string);
	mail ("vitaliy@viart.com", "CardSave Hash String", $hash_string);

	$transaction_id = $CrossReference;
	if ($HashDigest != $OurHashDigest) {
		$error_message = "HASH is corrupted: $HashDigest <> $OurHashDigest";
	} else if ($StatusCode != 0) {
		if (strlen($Message)) {
			$error_message = $Message;
		} else {
			$error_message = "Error (" . $StatusCode . ")";
		}
	}

?>