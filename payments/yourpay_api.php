<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  yourpay_api.php                                          ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * YourPay (www.yourpay.com) transaction handler by www.viart.com
 */

	global $is_admin_path, $is_sub_folder;
	$root_folder_path = ((isset($is_admin_path) && $is_admin_path) || (isset($is_sub_folder) && $is_sub_folder)) ? "../" : "./";
	include_once($root_folder_path . "payments/linkpoint_functions.php");

	$pass_data = array();
	foreach ($payment_parameters as $parameter_name => $parameter_value) {
		if(isset($pass_parameters[$parameter_name]) && $pass_parameters[$parameter_name] == 1) {
			$pass_data[$parameter_name] = $parameter_value;
		}
	}
	if (isset($pass_data["cvmvalue"]))  {
		if (strlen($pass_data["cvmvalue"])) {
			$pass_data["cvmindicator"] = "provided";
		} else {
			$pass_data["cvmindicator"] = "not_provided";
		}
	} else {
		$pass_data["cvmindicator"] = "not_provided";
	}

	$keyfile    = isset($payment_parameters["keyfile"]) ? $payment_parameters["keyfile"] : "";

	$xml = linkpoint_order_xml($pass_data);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $advanced_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml); // the string we built above
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSLCERT, $keyfile);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	set_curl_options($ch, $payment_parameters);

	// send the string to LSGS
	$payment_response = curl_exec($ch);
	curl_close($ch);
	$payment_response = trim($payment_response);
	$t->set_var("payment_response", $payment_response);
	if ($payment_response) {
		$response_parameters = array();
		// convert xml into array
		preg_match_all ("/<([^>]*?)>([^<]*?)\<\/[^>]*>/", $payment_response, $matches, PREG_SET_ORDER);
		for ($i = 0; $i < sizeof($matches); $i++) {
			$response_parameters[$matches[$i][1]] = ($matches[$i][2]);
		}

		foreach ($response_parameters as $parameter_name => $parameter_value) {
			$t->set_var($parameter_name, $parameter_value);
		}

		// check if transaction approved by payment system
		if (!isset($response_parameters["r_approved"])) {
			$error_message = "Can't obtain authorization parameter.";
		} elseif (strtoupper($response_parameters["r_approved"]) != "APPROVED") {
			if (isset($response_parameters["r_error"]) && strlen($response_parameters["r_error"])) {
				$error_message = $response_parameters["r_error"];
			} else {
				$error_message = "Your transaction has been declined.";
			}
		} else {
			$transaction_id = $response_parameters["r_code"];
		}

	} else {
		if (!$keyfile) {
			$error_message = "'keyfile' parameter is required for YourPay API.";
		} else if (!file_exists($keyfile) || preg_match("/^https?:/", $keyfile)) {
			$error_message = "Can't find YourPay SSL certificate, please use absolute path like '/home/user_name/keys/1234567.pem' for keyfile payment parameter.";
		} else if (!@fopen($keyfile, "r")) {
			$error_message = "Can't read YourPay SSL certificate, please check read permissions to the keyfile.";
		} else {
			$error_message = "Empty response from YourPay, please check that your payment parameters: configfile and keyfile were set correctly and outgoing connections are not blocked by firewall.";
		}
	}

?>