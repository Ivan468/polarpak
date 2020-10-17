<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  easypay.php                                              ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * easypay (http://www.easypay.de) transaction handler by ViArt Ltd. (www.viart.com).
 */


	$post_params = '';
	$response_code = array(
		"000" => "Approved or completed succesfully",
		"002" => "Call Voice-authorization number",
		"003" => "Invalid merchant number",
		"004" => "Retain card, card is not permitted",
		"005" => "Authorization declined",
		"006" => "Wrong Filetransfer",
		"012" => "Invalid transaction",
		"013" => "Limit exceeded",
		"014" => "Invalid card",
		"021" => "Reversal not processed because the referring authorization has not been found",
		"030" => "Format Error",
		"031" => "Card issuer not permitted",
		"033" => "Card expired",
		"034" => "Suspicion of Manipulation",
		"040" => "requested function not supported",
		"043" => "Stolen Card, pick up",
		"050" => "Duplicate Authorisation",
		"051" => "Limit exceeded",
		"056" => "Card not in authorizer’s database",
		"057" => "referencing transaction was not carried out with the card which was used for the original transaction",
		"058" => "Terminal-ID unknown",
		"059" => "Manipulation suspicion",
		"061" => "Restricted Card",
		"062" => "Restricted Card",
		"064" => "The transaction amount of the referencing transaction is higher than the transaction amount of the original transaction",
		"065" => "Limit exceeded",
		"080" => "Amount no longer available",
		"085" => "rejection from credit card institution",
		"086" => "The merchant is unknown",
		"087" => "Unknown Terminal (PIN-Pad)",
		"089" => "Wrong CRC",
		"091" => "Card issuer temporarily not reachable",
		"092" => "The card type is not processed by the authorization center",
		"096" => "Processing temporarily not possible",
		"097" => "Security breach - MAC check indicates error condition",
		"098" => "Date and time not plausible, trace number not in ascending order",
		"101" => "Unable to find the AID-File for the Capture/Reversal Request",
		"102" => "Unable to read the AID-File for the Capture/Reversal Request",
		"103" => "Unable to write the AID-File for the Capture/Reversal Request",
		"200" => "Can’t read „Beleg-Nummer“ for the virtual POS-Terminal",
		"201" => "Can’t read „Trace-Nummer“ for the virtual POS-Terminal",
		"203" => "No free Terminal for this merchant",
		"204" => "IPS System Error",
		"207" => "Authorization interrupted due to a time-out",
		"209" => "No connection to the Routing-System",
		"210" => "Error in the connection to the Routing-System",
		"212" => "The „Trace-Nummer“ in the answer differs from the one in the request",
		"213" => "The „Terminal-ID“ in the answer differs from the one in the request",
		"214" => "Can’t convert the incoming message to the ZVT-Protocol",
		"215" => "Can’t convert the outgoing payment message from the ZVT-Protocol",
		"216" => "The Message-Number in the answer doesn’t match with the requested function",
		"217" => "This Message-Numer is not supported by the IPS",
		"218" => "Invalid Amount for a Capture or Reversal-Request",
		"219" => "The reserved amount is smaller",
		"230" => "Temporary response to a 3DSecure request",
		"231" => "Invalid Currency Code",
		"240" => "3DSecure payment not possible, SSL payment permitted",
		"255" => "An unspecified error has appeared",
		"496" => "Duplicate booking, card number",
		"497" => "Duplicate booking, invoice number",
		"601" => "3DSecure Session cannot be started",
		"602" => "Internal error",
		"603" => "Verification error with the Visa Directory. Temporary error, try again",
		"604" => "MPI communication error with Visa Directory",
		"605" => "Cardholder not enrolled (Code U) Standard transaction possible",
		"606" => "Cardholder not using the 3DSecure process Standard transaction possible",
		"607" => "Invalid session, transaction refused. Carry out payment again.",
		"608" => "Error in communication with IPS server",
		"609" => "Cardholder not enrolled (Code U), transaction rejected, a automatic standard transaction is permitted.",
		"610" => "Merchant unknown",
		"611" => "Credit card unknown",
		"612" => "Invalid credit card (3DSecure payment not possible)",
		"613" => "Field „MD“ missing with the AUTH2 call",
		"614" => "Field „PaRes“ missing with the AUTH2 call",
		"615" => "3DSecure authorisation rejected by the ACS. Refuse purchase.",
		"616" => "Communication with e-retail system failed, but authentication OK.",
		"705" => "The POS-Terminal Table has not been created before",
		"706" => "Unable to attach to a semaphore for the POS-Terminal administration",
		"707" => "Unable to free a semaphore for the POS-Terminal administration",
		"719" => "The indicated POS-Terminal can’t be found for this merchant",
		"850" => "IPS Error",
		"851" => "IPS Error",
		"852" => "IPS Error",
		"860" => "IPS Error",
		"861" => "IPS Error",
		"862" => "IPS Error",
		"863" => "invalid field msgnr",
		"864" => "field msgnr missing",
		"865" => "field nrofgoods missing",
		"866" => "missing mandatory field",
		"867" => "IPS Error",
		"868" => "IPS Error",
		"869" => "IPS Error",
		"870" => "Merchant unknown",
		"871" => "IPS Error",
		"872" => "IPS Error",
		"873" => "Wrong merchant password",
		"874" => "Wrong system password",
		"875" => "Transaction not found",
		"876" => "IPS Error",
		"877" => "invalid Luhn Check",
		"878" => "wrong creditcard length",
		"879" => "length invalid",
		"880" => "length 0",
		"881" => "missing mandatory field",
		"882" => "IPS Error. Payment Server not available",
		"883" => "Account number wrong",
		"884" => "Bankcode wrong",
		"885" => "Sysntax error",
		"890" => "Timer error",
		"891" => "The card type is not permitted by the system. Refuse purchase.",
		"892" => "The card is not permitted for this merchant. Refuse purchase.",
		"894" => "The transaction is not permitted for this merchant or corresponding transactions are invalid.",
		"897" => "Transactions belonging to each other do not have the same card number.",
		"898" => "Transactions belonging to each other do not have the same account number.",
		"899" => "Transactions belonging to each other do not have the same bank code.",
		"900" => "Transactions belonging to each other do not have the same expiry data.",
		"901" => "The card number is invalid.",
		"902" => "The transaction type is not permitted by the system.",
		"999" => "Only for diagnosis: Transaction could not be processed successfully"
	);

	foreach ($payment_parameters as $parameter_name => $parameter_value) {
		if(strtolower($parameter_name) == 'cctyp'){
			switch (strtoupper($parameter_value)) {
				case 'VISA':
					$parameter_value = 0;
					break;
				case 'MC':
					$parameter_value = 1;
					break;
				case 'MASTERCARD':
					$parameter_value = 1;
					break;
				case 'AMEX':
					$parameter_value = 2;
					break;
				case 'DINERS':
					$parameter_value = 3;
					break;
				case 'JCB':
					$parameter_value = 4;
					break;
				default:
					$parameter_value = '';
			}
		}
		if(strtolower($parameter_name) == 'amount'){
			$parameter_value=sprintf('%012s',$parameter_value); 
		}
		$post_params .=(strlen($post_params))? '&': '';
		$post_params .= $parameter_name . "=" . urlencode($parameter_value);
	}

	$error_message = "";
	$ch = curl_init();
	if($ch) {
		
		curl_setopt ($ch, CURLOPT_URL, $advanced_url);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);//?
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($ch, CURLOPT_HEADER, 0);
		curl_setopt ($ch, CURLOPT_POST, 1);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $post_params);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		set_curl_options ($ch, $payment_parameters);
		$payment_response = curl_exec($ch);

		if (curl_error($ch)) {
			$error_message = curl_errno($ch)." - ".curl_error($ch);
		}

		curl_close($ch);
		$payment_response = trim($payment_response);

		$t->set_var("payment_response", $payment_response);
		if(!strlen($error_message)){
			if (strlen($payment_response)) {
				$content = explode('&', $payment_response);
				foreach ($content as $key_value) {
					list ($key, $value) = explode("=", $key_value);
					$response[$key] = $value;
				}
				$transaction_id = isset($response["retrefnr"]) ? $response["retrefnr"] : "";
				if (isset($response['rc'])) {
					if (strval($response['rc']) != strval('000')) {
						$error_message = "error code: ".$response['rc'];
						if(isset($response_code[$response['rc']])){
							$error_message .= " " . $response_code[$response['rc']];
						}
					}
				} else {
					$error_message = "Can't obtain data for your transaction.";
				}
			} else {
				$error_message = "Empty response from gateway. Please check your settings."; 
			}
		}
	} else {
		$error_message = "Can't initialize cURL.";
	}
?>