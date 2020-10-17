<?php

	function sms_send($recipient, $message, $originator = "", &$errors)
	{
		global $google_voice;
		$google_voice = new VA_GoogleVoice(); 
		$status = $google_voice->sms_send($recipient, $message);
		return $status;
	}

 
Class VA_GoogleVoice {

	var $email = "please specify";   // Google account email address
	var $passwd = "please specify";  // Google account password
	var $_rnr_se = "please specify"; // Google Voice parameter
	// _rnr_se - can be found in the source code 
	// of the inbox page of your Google Voice
	// Click to view the source code and search for '_rnr_se' hidden control
	// there should be a string of about 30 characters 

	var $auth; 								           // The login auth key will be saved here
	var $account_type = "GOOGLE";  // Google account type
	var $service = "grandcentral"; // Google Voice service 
	var $source = "viart.com";		 // source of your site 
 
	var $login_url = "https://www.google.com/accounts/ClientLogin";
	var $sms_send_url = "https://www.google.com/voice/sms/send/";	

	var $inbox_url = "https://www.google.com/voice/inbox/recent/";	
	var $unread_url = "https://www.google.com/voice/inbox/recent/unread/";	

	function VA_GoogleVoice ($email = "", $passwd = "") 
	{
		if ($email) { $this->email = $email; }
		if ($passwd) { $this->passwd = $password; }
		$this->google_login();
	}
 
	function google_login () {
		$post_params  = "accountType=GOOGLE";
		$post_params .= "&Email=" . urlencode($this->email);
		$post_params .= "&Passwd=" . urlencode($this->passwd);
		$post_params .= "&service=" . urlencode($this->service);
		$post_params .= "&source=" . urlencode($this->source);


		$ch = curl_init($this->login_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);

		$response = curl_exec($ch);
		curl_close($ch);

		if (preg_match("/Auth\=([\w\_\-]+)/i", $response, $matches)) {
			$this->auth = $matches[1];
			return $this->auth;
		} else {
			return false;
		}
	}
 
	function sms_send($recipient, $message, $originator = "")
	{
		$post_params  = "id=";
		$post_params .= "&phoneNumber=" . urlencode($recipient);
		$post_params .= "&text=" . urlencode($message);
		$post_params .= "&_rnr_se=" . urlencode($this->_rnr_se);

		$ch = curl_init($this->sms_send_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Authorization: GoogleLogin auth=".$this->auth));

		$status = curl_exec($ch);

		return $status;		
	}


	function sms_list($is_unread = false)
	{
		if ($is_unread) {
			$list_url = $this->inbox_url;
		} else {
			$list_url = $this->unread_url;
		}
 
		$ch = curl_init($list_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
		curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Authorization: GoogleLogin auth=".$this->auth));

		$response = curl_exec($ch);
		return $response;		
	}

}
?>