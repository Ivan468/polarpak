<?php

	$sms_accountno = "type_here_your_account_number";
	$sms_pwd = "type_here_your_password";

	// send SMS to mobile phone
	function sms_send($recipient, $message, $originator = "", &$errors)
	{
		global $sms_accountno, $sms_pwd;
		// remove any non-digits symbols from number
		$recipient = preg_replace("/[^\d]/", "", $recipient);
		// clear message from return carriage and tab symbols
		$message = str_replace("\r", "", $message);
		$message = str_replace("\t", " ", $message);

		if (!$recipient || !$message) {
			$errors = "Recipient or message is empty.";
			return false;
		}


		$sms_url  = "https://api.accessyou.com/sms/sendsms-utf8.php?";
		$sms_url .= "accountno=".urlencode($sms_accountno);
		$sms_url .= "&pwd=".urlencode($sms_pwd);
		$sms_url .= "&msg=".urlencode($message);
		$sms_url .= "&phone=".urlencode($recipient);
		if (strlen($message) > 160) {
			$sms_url .= "&size=".urlencode("|");
		}
		if (strlen($originator)) {
			$sms_url .= "&from=".urlencode($originator);
		}


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $sms_url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		//curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);

		$result = trim(curl_exec($ch));
		curl_close($ch);

		if (!is_numeric($result)) {
			$errors = sms_error_desc($result);
			return false;
		}

		// return SMS ID in case of success
		return $result;	}


	// get delivery report of SMS message
	function sms_get_status($sms_id)
	{
		global $sms_accountno, $sms_pwd;

		$status_url  = "https://api.accessyou.com/sms/receivestatus.php?";
		$status_url .= "accountno=".urlencode($sms_accountno);
		$status_url .= "&pwd=".urlencode($sms_pwd);
		$status_url .= "&id=".urlencode($sms_id);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $status_url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);

		$result = curl_exec($ch);
		curl_close($ch);

		if (!preg_match("/DELIVERED|UNDELIVERED|UNKNOWN|FAILED|REJECTED|nostatus/i", $result)) {
			$result = "Error: " . $result;
		}
		return $result;
	}


	// get delivery to mobile operator 
	function sms_gate_status($sms_id)
	{
		global $sms_accountno, $sms_pwd;

		$status_url  = "https://api.accessyou.com/sms/getstatus.php?";
		$status_url .= "accountno=".urlencode($sms_accountno);
		$status_url .= "&pwd=".urlencode($sms_pwd);
		$status_url .= "&id=".urlencode($sms_id);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $status_url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);

		$result = curl_exec($ch);
		curl_close($ch);

		if (!preg_match("/pending|queue|ready|finish/i", $result)) {
			$result = "Error: " . $result;
		}
		return $result;
	}

	function sms_error_desc($error_code)
	{
		$errors = array(
			"login_failure" => "Your account login failure",
			"message_empty" => "Message is empty",
			"phoneno_empty" => "Phone no is empty",
			"system_error" => "Our System generated error",
			"ip_authentication_failure" => "Your IP is forbidden to use API",
			"no_balance" => "Your account balance is not enough",
			"reject_dnc" => "Phone No is found in OFCA DNC list",
			"invalid_phoneno" => "Phone No format is incorrect Additional error status for custom SenderID API",
			"senderid_empty" => "SenderID is empty",
			"invalid_senderid" => "SenderID format is invalid",
			"num_senderid_not_more_than_16" => "Numeric SenderID not more than 16 digits",
			"alpha_senderid_not_more_than_11" => "Alphanumeric SenderID not more than 11 digits",
			"senderid_not_support" => "Your account doesn't support SenderID plan",
		);
		$error_code = strtolower($error_code);
		$error_code = str_replace(" ", "_", $error_code);
		$error_desc = isset($errors[$error_code]) ? $errors[$error_code] : "Error code: " . $error_code;

		return $error_desc;
	}

?>