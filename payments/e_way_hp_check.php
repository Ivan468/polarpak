<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  e_way_hp_check.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * eWay (www.eway.co.uk) transaction handler by http://www.viart.com/
 */

	$AccessPaymentCode = get_param('AccessPaymentCode');

	$request  = $payment_parameters['response_url'];
	$request .= '?CustomerID='.str_replace(' ', '+', htmlspecialchars($payment_parameters['CustomerID']));
	$request .= '&UserName='.str_replace(' ', '+', htmlspecialchars($payment_parameters['UserName']));
	$request .= '&AccessPaymentCode='.str_replace(' ', '+', $AccessPaymentCode);

	$ch = curl_init();
	if ($ch){
		
		curl_setopt ($ch, CURLOPT_URL, $request);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($ch, CURLOPT_TIMEOUT,30);
		set_curl_options ($ch, $payment_parameters);
			
		$payment_response = curl_exec ($ch);
		if (curl_errno($ch)){
			$error_message .= curl_errno($ch)." - ".curl_error($ch);
			return;
		}
		curl_close($ch);
		
		if(strlen($payment_response)){
			$matches = array();
			if (preg_match('/\<TransactionResponse\>(.*)\<\/TransactionResponse\>/Uis', $payment_response, $matches)){
				$AuthCode = "";
				$ResponseCode = "";
				$ReturnAmount = "";
				$TrxnNumber = "";
				$TrxnStatus = "";
				$TrxnResponseMessage = "";
				$MerchantOption1 = "";
				$MerchantOption2 = "";
				$MerchantOption3 = "";
				$MerchantReference = "";
				$MerchantInvoice = "";
				$ErrorMessage = "";
				$matches = array();
				if (preg_match('/\<AuthCode\>(.*)\<\/AuthCode\>/Uis', $payment_response, $matches)){
					$AuthCode = $matches[1];
			        $variables["authorization_code"] = $AuthCode;
				}
				$matches = array();
				if (preg_match('/\<ResponseCode\>(.*)\<\/ResponseCode\>/Uis', $payment_response, $matches)){
					$ResponseCode = $matches[1];
				}
				$matches = array();
				if (preg_match('/\<ReturnAmount\>(.*)\<\/ReturnAmount\>/Uis', $payment_response, $matches)){
					$ReturnAmount = $matches[1];
				}
				$matches = array();
				if (preg_match('/\<TrxnNumber\>(.*)\<\/TrxnNumber\>/Uis', $payment_response, $matches)){
					$TrxnNumber = $matches[1];
				}
				$matches = array();
				if (preg_match('/\<TrxnStatus\>(.*)\<\/TrxnStatus\>/Uis', $payment_response, $matches)){
					$TrxnStatus = $matches[1];
				}
				$matches = array();
				if (preg_match('/\<TrxnResponseMessage\>(.*)\<\/TrxnResponseMessage\>/Uis', $payment_response, $matches)){
					$TrxnResponseMessage = $matches[1];
				}
				$matches = array();
				if (preg_match('/\<MerchantOption1\>(.*)\<\/MerchantOption1\>/Uis', $payment_response, $matches)){
					$MerchantOption1 = $matches[1];
				}
				$matches = array();
				if (preg_match('/\<MerchantOption2\>(.*)\<\/MerchantOption2\>/Uis', $payment_response, $matches)){
					$MerchantOption2 = $matches[1];
				}
				$matches = array();
				if (preg_match('/\<MerchantOption3\>(.*)\<\/MerchantOption3\>/Uis', $payment_response, $matches)){
					$MerchantOption3 = $matches[1];
				}
				$matches = array();
				if (preg_match('/\<MerchantReference\>(.*)\<\/MerchantReference\>/Uis', $payment_response, $matches)){
					$MerchantReference = $matches[1];
				}
				$matches = array();
				if (preg_match('/\<MerchantReference\>(.*)\<\/MerchantReference\>/Uis', $payment_response, $matches)){
					$MerchantReference = $matches[1];
				}
				$matches = array();
				if (preg_match('/\<ErrorMessage\>(.*)\<\/ErrorMessage\>/Uis', $payment_response, $matches)){
					$ErrorMessage = $matches[1];
				}
				$transaction_id = $TrxnNumber . " ResponseCode: ".$ResponseCode;
				if((strtoupper($TrxnStatus) != 'TRUE') or strlen($ErrorMessage)){
					$error_message = "Transaction status is: ".$TrxnStatus;
					$error_message .= (strlen($TrxnResponseMessage))? " ".$TrxnResponseMessage: "";
					$error_message .= (strlen($ErrorMessage))? " ".$ErrorMessage: "";
				}else{
					if(!strlen($TrxnNumber)){
						$error_message  = "Can't obtain transaction number. ResponseCode: ".$ResponseCode;
					}
				}
			}else{
				$error_message  = "Can't obtain transaction response from eWay.";
			}
		}else{
			$error_message .= "Can't obtain data for your transaction.";
		}
	}else{
		$error_message .= "Can't initialize cURL.";
	}

?>