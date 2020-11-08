<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  paypal_direct_3d_check.php                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * PayPal Direct (www.paypal.com) transaction handler by http://www.viart.com/
 */

	if(!strlen($variables["secure_3d_check"])){
		$update_order_data = false;
		$update_order_status = false;
		$sql  = " SELECT transaction_id, error_message, pending_message FROM " . $table_prefix . "orders ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$transaction_id = $db->f("transaction_id");
			$error_message = $db->f("error_message");
			$pending_message = $db->f("pending_message");
		}
		return;
	}

	global $is_admin_path, $is_sub_folder;
	$root_folder_path = ((isset($is_admin_path) && $is_admin_path) || (isset($is_sub_folder) && $is_sub_folder)) ? "../" : "./";
	include_once ($root_folder_path . "payments/paypal_functions.php");

	// get some variables from our payment settings
	$sandbox    = isset($payment_parameters["sandbox"]) ? $payment_parameters["sandbox"] : 0;
	$shorterror  = isset($payment_parameters["shorterror"]) ? $payment_parameters["shorterror"] : 1;
	$longerror  = isset($payment_parameters["longerror"]) ? $payment_parameters["longerror"] : 1;
	$sslcert    = isset($payment_parameters["sslcert"]) ? $payment_parameters["sslcert"] : "";

	if ($sandbox == 1) {
		$paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
		$api_url = "https://api-aa.sandbox.paypal.com/2.0/";
		$api_url = "https://api.sandbox.paypal.com/2.0/";
	} else {
		$paypal_url = "https://www.paypal.com/cgi-bin/webscr";
		$api_url = "https://api-aa.paypal.com/2.0/";
	}

	$pass_data = array();
	foreach ($payment_parameters as $parameter_name => $parameter_value) {
		if(isset($pass_parameters[$parameter_name]) && $pass_parameters[$parameter_name] == 1) {
			$pass_data[$parameter_name] = $parameter_value;
		}
	}
	$pass_data["ButtonSource"] = "Viart_ShoppingCart_DP";
	$pass_data["buttonsource"] = "Viart_ShoppingCart_DP";

	$PaRes = get_param("PaRes");
	$MD = get_param("MD");
	$cmpi_lookup_transactionid = get_session("session_cmpi_lookup_transactionid");
	$cmpi_lookup_enrolled = get_session("session_cmpi_lookup_enrolled");

	$xml  = '<CardinalMPI>';
	$xml .= '<MsgType>cmpi_authenticate</MsgType>';
	$xml .= '<Version>1.7</Version>';
	$xml .= '<ProcessorId>'.$payment_parameters['ProcessorId'].'</ProcessorId>';
	$xml .= '<MerchantId>'.$payment_parameters['MerchantId'].'</MerchantId>';
	$xml .= '<TransactionPwd>'.$payment_parameters['TransactionPwd'].'</TransactionPwd>';
	$xml .= '<TransactionType>C</TransactionType>';
	$xml .= '<TransactionId>'.$cmpi_lookup_transactionid.'</TransactionId>';
	$xml .= '<PAResPayload>'.$PaRes.'</PAResPayload>';
	$xml .= '</CardinalMPI>';
	
	$cmpi_msg = "cmpi_msg=".urlencode($xml);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $payment_parameters['MAPS_URL']);
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $cmpi_msg);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);  
	set_curl_options ($ch, $payment_parameters);
						  
	$response = curl_exec($ch);
	if (curl_error($ch)) {
		$error_message = curl_errno($ch) . " " . curl_error($ch);
		return;
	}
	curl_close($ch);

	$match = array();
	$cmpi_authenticate_errorno = '';
	$cmpi_authenticate_errdesc = '';
	$cmpi_authenticate_paresstatus = '';
	$cmpi_authenticate_cavv = '';
	$cmpi_authenticate_signatureverification = '';
	$cmpi_authenticate_eciflag = '';
	$cmpi_authenticate_xid = '';
	if (strlen($response)) {
		if (preg_match("/<ErrorNo>(.*)<\/ErrorNo>/i", $response, $match)) {
			$cmpi_authenticate_errorno = $match[1];
		}
		if (preg_match("/<ErrDesc>(.*)<\/ErrDesc>/i", $response, $match)) {
			$cmpi_authenticate_errdesc = $match[1];
		}
		if (preg_match("/<PAResStatus>(.*)<\/PAResStatus>/i", $response, $match)) {
			$cmpi_authenticate_paresstatus = $match[1];
		}
		if (preg_match("/<Cavv>(.*)<\/Cavv>/i", $response, $match)) {
			$cmpi_authenticate_cavv = $match[1];
		}
		if (preg_match("/<SignatureVerification>(.*)<\/SignatureVerification>/i", $response, $match)) {
			$cmpi_authenticate_signatureverification = $match[1];
		}
		if (preg_match("/<EciFlag>(.*)<\/EciFlag>/i", $response, $match)) {
			$cmpi_authenticate_eciflag = $match[1];
		}
		if (preg_match("/<Xid>(.*)<\/Xid>/i", $response, $match)) {
			$cmpi_authenticate_xid = $match[1];
		}
		if($cmpi_authenticate_errorno != "0"){
			$error_message = $cmpi_authenticate_errorno.": ".$cmpi_authenticate_errdesc;
			return;
		}
	} else {
		$error_message = "Empty response from Cardinal, please check that your payment parameters: ProcessorId, MerchantId and TransactionPwd were set correctly.";
		return;
	}
	$pass_data["AuthStatus3ds"] = $cmpi_authenticate_paresstatus;
	$pass_data["MpiVendor3ds"] = $cmpi_lookup_enrolled;
	$pass_data["Cavv"] = $cmpi_authenticate_cavv;
	$pass_data["Eci3ds"] = $cmpi_authenticate_eciflag;
	$pass_data["Xid"] = $cmpi_authenticate_xid;

	$soap = paypal_direct_payment($pass_data);

	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $api_url);
	curl_setopt ($ch, CURLOPT_POST, 1);
	curl_setopt ($ch, CURLOPT_POSTFIELDS, $soap);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_SSLCERT, $sslcert);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
	set_curl_options ($ch, $payment_parameters);

	$paypal_response = curl_exec ($ch);
	curl_close ($ch);

	if (strlen($paypal_response)) {
		if (preg_match("/<SOAP-ENV:Fault>/i", $paypal_response)) {
			$faultcode = ""; $faultstring = "";
			if (preg_match("/<faultcode>(.*)<\/faultcode>/i", $paypal_response, $match)) {
				$faultcode = $match[1];
			}
			if (preg_match("/<faultstring>(.*)<\/faultstring>/i", $paypal_response, $match)) {
				$faultstring = $match[1];
			}
			$error_message  = "Some errors occurred during handling your transaction:<br>";
			$error_message .= $faultcode . ": " . $faultstring;
			return;
		} 

		if (preg_match_all("/<Errors[^>]*>.*<\\/Errors>/Uis", $paypal_response, $matches)) {
			for($m = 0; $m < sizeof($matches[0]); $m++) {
				$errors_block = $matches[0][$m];
				$errorcode = ""; $shortmessage = ""; $longmessage = ""; $severitycode = "";
				if (preg_match("/<ErrorCode[^>]*>(.*)<\/ErrorCode>/i", $errors_block, $match)) {
					$errorcode = $match[1];
				}
				if (preg_match("/<ShortMessage[^>]*>(.*)<\/ShortMessage>/i", $errors_block, $match)) {
					$shortmessage = $match[1];
				}
				if (preg_match("/<LongMessage[^>]*>(.*)<\/LongMessage>/i", $errors_block, $match)) {
					$longmessage = $match[1];
				}
				if (preg_match("/<SeverityCode[^>]*>(.*)<\/SeverityCode>/i", $errors_block, $match)) {
					$severitycode = $match[1];
				}

				// show only errors 
				if (preg_match("/Error/i", $severitycode)) {
					$error_message .= $errorcode . ":";
					if ($shorterror && !($longerror && $shortmessage == $longmessage)) {
						$error_message .= " " . $shortmessage;
						if ($longerror && !preg_match("/\.$/", trim($shortmessage))) {
							$error_message .= ".";
						}
					} 
					if ($longerror) {
						$error_message .= " " . $longmessage;
					}
					$error_message .= "<br>";
				}
			}
			if ($error_message) {
				return;
			}
		} 

		if (preg_match("/<TransactionID[^>]*>(.*)<\/TransactionID>/i", $paypal_response, $match)) {
			$transaction_id = $match[1];

			$ack = ""; $avs_code = ""; $cvv_code = "";
			if (preg_match("/<Ack[^>]*>(.*)<\/Ack>/i", $paypal_response, $match)) {
				$ack = $match[1];
			}
			if (preg_match("/<AVSCode[^>]*>(.*)<\/AVSCode>/i", $paypal_response, $match)) {
				$avs_code = $match[1];
			}
			if (preg_match("/<CVV2Code[^>]*>(.*)<\/CVV2Code>/i", $paypal_response, $match)) {
				$cvv_code = $match[1];
			}
			if (!strlen($ack)) {
				$error_message = "Can't obtain transaction status.";
			} else if (strtolower($ack) != "success" && strtolower($ack) != "successwithwarning") {
				$error_message = "Your transaction status is " . $ack;
			}

		} else {
			$error_message  = "Can't obtain transaction information from PayPal.";
			return;
		}

	} else {
		if (!$sslcert) {
			$error_message = "SSLCert parameter is required for PayPal Express Checkout.";
		} else if (!file_exists($sslcert)) {
			$error_message = "Can't find PayPal SSL certificate, please use absolute path like '/home/user_name/cert/cert_key_pem.txt' for SSLCert payment parameter.";
		} else if (!@fopen($sslcert, "r")) {
			$error_message = "Can't read PayPal SSL certificate, please check read permissions to the file.";
		} else {
			$error_message = "Empty response from PayPal, please check that your payment parameters: Username, Password and SSLCert were set correctly.";
		}
		return;
	}

?>