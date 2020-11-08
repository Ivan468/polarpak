<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  amazon_pay_validate.php                                  ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Amazon Pay Checkout handler by http://www.viart.com/
 */

	$va_status = get_param("va_status");
	if (strtolower($va_status) == "cancel") {
		// check if user has cancelled the order
		$error_message = "Your transaction has been cancelled.";
	} else {

		$sellerId = get_param("sellerId");
		$resultCode = get_param("resultCode");
		$failureCode = get_param("failureCode");
		$transaction_id = get_param("orderReferenceId");
		$amount = get_param("amount");
		$currencyCode = get_param("currencyCode");
		$paymentAction = get_param("paymentAction");
		$order_id = get_param("sellerOrderId");
		$accessKey = get_param("accessKey");
		$amazon_signature = get_param("signature");

		$resultCode = strtolower($resultCode);
		if ($resultCode == "success") {
			// validate response
			$payment_parameters = array(); $pass_parameters = array(); $post_parameters = ""; $pass_data = array(); $variables = array();
			get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);

			$site_url = get_setting_value($settings, "site_url", "");
			$secure_url = get_setting_value($settings, "secure_url", $site_url);
			$secretAccessKey = get_setting_value($payment_parameters, "secretAccessKey"); 
			$returnURL = get_setting_value($payment_parameters, "returnURL", $secure_url."order_final.php"); 

			$parsed_url = parse_url($returnURL);
			$host = $parsed_url["host"];
			$path = $parsed_url["path"];

			$amazon_params = $_GET;
			unset($amazon_params["signature"]);
			$sign_lines = array();
			uksort($amazon_params, "strcmp");
			foreach ($amazon_params as $key => $value) {
				$sign_lines[] = $key . '=' . rawurlencode($value);
			}
			$data  = "GET\n$host\n$path\n";
			$data .= str_replace("%7E", "~", implode("&", $sign_lines));
    
			$viart_signature = rawurlencode(base64_encode(hash_hmac("sha256", $data, $secretAccessKey, true)));
			// check if amazon and viart signature matched
			if (strtolower($viart_signature) != strtolower($amazon_signature)) {
				$error_message = "Signature didn't matched $amazon_signature <> $viart_signature.";
			}

		} else if ($resultCode == "failure") {
			$error_message = "Your transaction has been declined ($failureCode).";
		} else {
			$error_message = "Unknown response from gateway: $resultCode";
		}

	}

?>