<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  argus_payment.php                                        ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	//get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_params, $pass_data, $variables, "final");

	if (!$advanced_url) { $advanced_url = "https://svc.arguspayments.com/payment/pmt_service.cfm"; }

	if (isset($post_parameters) && $post_parameters) {
		$post_params = $post_parameters;
	}

	$ch = @curl_init();
	if ($ch) {
		curl_setopt($ch, CURLOPT_URL, $advanced_url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "ViArt Argus Payment Module");
		set_curl_options($ch, $payment_parameters);

		$payment_response = curl_exec($ch);
		curl_close($ch);

		$trans_status = "";
		if (preg_match("/<trans_status_name>([^<]+)<\/trans_status_name>/isU", $payment_response, $trans_match)) {
			$trans_status = strtoupper($trans_match[1]);
		}
		if ($trans_status == "APPROVED") {
			$success_message = "Approved";
		} else if ($trans_status == "DECLINED") {
			$error_message = "Your transaction was declined";
		} else if (strlen($trans_status)) {
			$error_message = "Your transaction status is " . htmlspecialchars($trans_status);
		} else {
			// check other fields
			if (preg_match("/<api_advice>([^<]+)<\/api_advice>/isU", $payment_response, $api_advice_match)) {
				$error_message = $api_advice_match[1];
			}
			if (preg_match("/<ref_field>([^<]+)<\/ref_field>/isU", $payment_response, $ref_field_match)) {
				if ($error_message) { $error_message .= "<br/>"; }
				$error_message .= "Field Error: " . $ref_field_match[1];
			}
			if (!$error_message) { 
				$error_message = "Can't process this transaction. ";
			}
		}
	} else {
		$error_message = "Can't initialize cURL.";
	}


?>