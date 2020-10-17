<?php

	$sms_api_id = "type_here_your_api_id";
	$sms_user = "type_here_your_user_name";
	$sms_password = "type_here_your_password";
	$session_sms_id = "";

	// Send SMS to N numbers
	function sms_send($recipient, $message, $originator = "", &$errors)
	{
		global $sms_user, $sms_password, $sms_api_id;

		$message = str_replace("\r", "", $message);
		$message = str_replace("\n", " ", $message);
		$message = str_replace("\t", " ", $message);

		if (!$recipient || !$message) {
			$errors = "Recipient or message is empty.";
			return false;
		}

		$sms_url = "https://api.clickatell.com/http/sendmsg";
		$post_params  = "user=".urlencode($sms_user)."&";
		$post_params .= "password=".urlencode($sms_password)."&";
		$post_params .= "api_id=".urlencode($sms_api_id)."&";
		$post_params .= "to=".urlencode($recipient)."&";
		$post_params .= "text=".urlencode($message);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $sms_url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);

		$result = curl_exec($ch);
		curl_close($ch);

		if ($result) {
			if (preg_match("/^\s*ID\s*\:\s*([0-9a-z]+)\s*$/si", $result, $matches)) {
				$result = $matches[1];
			} else if (preg_match("/ERR\s*:(.+)/", $result, $matches)) {
				$errors = $matches[1];
				return false;
			} else {
				$errors = "Bad response: " . $result;
				return false;
			}
		} else {
			return false;
		}

		// return SMS ID in case of success
		return $result;	}

	// Get delivery report of SMS message
	function sms_get_status($sms_id)
	{
		global $sms_user, $sms_password, $sms_api_id;

		$status_array=array("001"=>"Message unknown",
							"002"=>"Message queued",
							"003"=>"Delivered to gateway",
							"004"=>"Received by recipient",
							"005"=>"Error with message",
							"006"=>"User cancelled message delivery",
							"007"=>"Error delivering message",
							"008"=>"OK",
							"009"=>"Routing error",
							"010"=>"Message expired",
							"011"=>"Message queued for later delivery",
							"012"=>"Out of credit");

		$session_sms_id = sms_get_session();
		if ($session_sms_id) {			$url_report = "http://api.clickatell.com/http/querymsg";
			$url = parse_url($url_report);
			$server_name = $url["host"];
			$server_path = $url["path"];
			$send = "";
			$send .= "session_id=".$session_sms_id."&";
			$send .= "apimsgid=".$sms_id."&";

			$result = "";

			$fp = fsockopen($server_name, 80, $errno, $errstr, 30);
			if (!$fp) {
				echo "An error occured while opening remote server: $errstr ($errno)<br />\n";
				return false;
			} else {
				$out = "POST " . $server_path . " HTTP/1.0\r\n";
				$out .= "Host: $server_name\r\n";
				$out .= "User-agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)\r\n";
				$out .= "Connection: Close\r\n";
				$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
				$out .= "Content-Length: " . strlen($send) . "\r\n\r\n";
				$out .= $send;

				fwrite($fp, $out);
				while (!feof($fp)) {
					$result .= fgets($fp, 4096);
				}
				fclose($fp);
			}

			if ($result) {
			    	//echo "<hr>" . nl2br(htmlspecialchars($out)) . "<hr>";
			    	//echo nl2br(htmlspecialchars($result)) . "<hr>";
			    	$result = strip_tags(substr($result, strpos($result, "\r\n\r\n") + strlen("\r\n\r\n")));
					$result_arr = preg_split("/(: )|(, )/",$result);
					$stat = $result_arr[0];
					switch ($stat) {
						case "ERR"	:	$result=$result_arr[2];
										return false;
						case "ID"	:	$result = $result_arr[2];//$status_array[$result_arr[2]]
					}

			} else {
				return false;
			}

			// return SMS status in case of success
			return $result;		} else {
			return false;
		}
	}

	// Ask the user's available credits on server
	function sms_get_balance()
	{
		global $sms_user, $sms_password, $sms_api_id;

		if (!$session_sms_id) { $session_sms_id = sms_get_session();}
		if ($session_sms_id) {			$url_balance = "http://api.clickatell.com/http/getbalance";
			$url = parse_url($url_balance);
			$server_name = $url["host"];
			$server_path = $url["path"];
			$send = "";
			$send .= "session_id=".$session_sms_id."&";

			$result = "";

			$fp = fsockopen($server_name, 80, $errno, $errstr, 30);
			if (!$fp) {
				echo "An error occured while opening remote server: $errstr ($errno)<br />\n";
				return false;
			} else {
				$out = "POST " . $server_path . " HTTP/1.0\r\n";
				$out .= "Host: $server_name\r\n";
				$out .= "User-agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)\r\n";
				$out .= "Connection: Close\r\n";
				$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
				$out .= "Content-Length: " . strlen($send) . "\r\n\r\n";
				$out .= $send;

				fwrite($fp, $out);
				while (!feof($fp)) {
					$result .= fgets($fp, 4096);
				}
				fclose($fp);
			}

			if ($result) {
		    	//echo "<hr>" . nl2br(htmlspecialchars($out)) . "<hr>";
		    	$result = strip_tags(substr($result, strpos($result, "\r\n\r\n") + strlen("\r\n\r\n")));
				$result_arr = preg_split("/(: )|(, )/",$result);
				$stat = $result_arr[0];
				switch ($stat) {
					case "ERR"	:	$result=$result_arr[2];
									return false;
					case "Credit"	:	$result = $result_arr[1];
				}

			} else {
				return false;
			}

			// return Balance
			return $result;		} else {			return false;
		}
	}

	function sms_get_session()
	{		global $sms_user, $sms_password, $sms_api_id, $session_sms_id;
		$url_auth = "http://api.clickatell.com/http/auth";
		$url = parse_url($url_auth);
		$server_name = $url["host"];
		$server_path = $url["path"];
		$send = "";
		$send .= "api_id=".$sms_api_id."&";
		$send .= "user=".$sms_user."&";
		$send .= "password=".$sms_password."&";

		$result = "";

		if (sms_get_ping()) { $result = $session_sms_id;}
		else {
			$fp = fsockopen($server_name, 80, $errno, $errstr, 30);
			if (!$fp) {
				echo "An error occured while opening remote server: $errstr ($errno)<br />\n";
				return false;
			} else {
				$out = "POST " . $server_path . " HTTP/1.0\r\n";
				$out .= "Host: $server_name\r\n";
				$out .= "User-agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)\r\n";
				$out .= "Connection: Close\r\n";
				$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
				$out .= "Content-Length: " . strlen($send) . "\r\n\r\n";
				$out .= $send;

				fwrite($fp, $out);
				while (!feof($fp)) {
					$result .= fgets($fp, 4096);
				}
				fclose($fp);
			}

			if ($result) {
				//echo "<hr>" . nl2br(htmlspecialchars($out)) . "<hr>";
				$result = strip_tags(substr($result, strpos($result, "\r\n\r\n") + strlen("\r\n\r\n")));
				$result_arr = preg_split("/(: )|(, )/",$result);
				$stat = $result_arr[0];
				switch ($stat) {
					case "ERR"	:	return false;  break;
					case "OK"	:	$result = $result_arr[1];  break;
				}
			} else {
				return false;
			}
		}

		// return Session ID from service
		return $result;
	}

	function sms_get_ping()
	{		global $session_sms_id;

		$url_ping = "http://api.clickatell.com/http/ping";
		$url = parse_url($url_ping);
		$server_name = $url["host"];
		$server_path = $url["path"];
		$send = "";
		$send .= "session_id=".$session_sms_id."&";

		$result = false;

		$fp = fsockopen($server_name, 80, $errno, $errstr, 30);
		if (!$fp) {
			echo "An error occured while opening remote server: $errstr ($errno)<br />\n";
			return false;
		} else {
			$out = "POST " . $server_path . " HTTP/1.0\r\n";
			$out .= "Host: $server_name\r\n";
			$out .= "User-agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$out .= "Content-Length: " . strlen($send) . "\r\n\r\n";
			$out .= $send;

			fwrite($fp, $out);
			while (!feof($fp)) {
				$result .= fgets($fp, 4096);
			}
			fclose($fp);
		}

		if ($result) {			//echo "<hr>" . nl2br(htmlspecialchars($out)) . "<hr>";
			$result = strip_tags(substr($result, strpos($result, "\r\n\r\n") + strlen("\r\n\r\n")));
			$result_arr = preg_split("/(: )|(, )/",$result);
			$stat = $result_arr[0];
			switch ($stat) {
				case "ERR"	:	$result = false; break;
				case "OK"	:	$result = true; break;
			}		} else {
			return false;
		}

		return $result;
	}

	/* For future use

	$url_private = "http://212.58.4.8/xmlSms/xmlSmsPrivate.php";
	$url_originator = "http://212.58.4.8/xmlSms/addOriginator.php";

	// Send more than one messages to more than number
	$xml_private = '<?xml version="1.0"' . chr(63) . '><PACKET><user>' . $sms_user . '</user><PASSWORD>' . $sms_password . '</PASSWORD><HEADER>' . $originator . '</HEADER><STARTDATE></STARTDATE><EXPIREDATE></EXPIREDATE><PHONENUMBER>' . $recipient . '</PHONENUMBER><MESSAGE><![CDATA[TEST1@@@TEST2]]></MESSAGE></PACKET>';
	// Define ORIGINATOR
	$xml_origanator = '<?xml version="1.0"' . chr(63) . '><PACKET><user>' . $sms_user . '</user><PASSWORD>' . $sms_password . '</PASSWORD><ORIGINATOR>' . $originator . '</ORIGINATOR></PACKET>';

	*/

?>