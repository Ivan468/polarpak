<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  sagepay_form_functions.php                               ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Sage Pay Form (www.sagepay.com) transaction handler by www.viart.com
 */

	function encryptAes($string, $key)
	{
		// AES encryption, CBC blocking with PKCS5 padding then HEX encoding.
		// Add PKCS5 padding to the text to be encypted.
		$string = addPKCS5Padding($string);
		// Perform encryption with PHP's MCRYPT module.
		$crypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $string, MCRYPT_MODE_CBC, $key);
		// Perform hex encoding and return.
		return "@" . strtoupper(bin2hex($crypt));
	}

	function decryptAes($crypt, $key)
	{
		// HEX decoding then AES decryption, CBC blocking with PKCS5 padding.
		// Use initialization vector (IV) set from $str_encryption_password.
		// Remove the first char which is @ to flag this is AES encrypted and HEX decoding.
		$hex = substr($crypt, 1);
		// Throw exception if string is malformed
		if (!preg_match('/^[0-9a-fA-F]+$/', $hex)) {
			echo 'Invalid encryption string';
			exit;
		}
		$crypt = pack('H*', $hex);

		// Perform decryption with PHP's MCRYPT module.
		$string = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $crypt, MCRYPT_MODE_CBC, $key);
		return removePKCS5Padding($string);
	}

	function addPKCS5Padding($input)
	{
		$blockSize = 16;
		$padd = "";
		// Pad input to an even block size boundary.
		$length = $blockSize - (strlen($input) % $blockSize);
		for ($i = 1; $i <= $length; $i++) {
			$padd .= chr($length);
		}
		return $input.$padd;
	}

	function removePKCS5Padding($input)
	{
		$blockSize = 16;
		$padChar = ord($input[strlen($input) - 1]);
		// Check for PadChar is less then Block size 
		if ($padChar > $blockSize) {
			echo 'Invalid encryption string';
		}
		// Check by padding by character mask 
		if (strspn($input, chr($padChar), strlen($input) - $padChar) != $padChar) {
			echo 'Invalid encryption string';
		}
		$unpadded = substr($input, 0, (-1) * $padChar);
		// Chech result for printable characters 
		if (preg_match('/[[:^print:]]/', $unpadded)) {
			echo 'Invalid encryption string';
		}
		return $unpadded;
	}


	function simple_xor ($InString, $Key)
	{
		// Initialise key array
		$KeyList = array();
		// Initialise out variable
		$output = "";

		// Convert $Key into array of ASCII values
		for($i = 0; $i < strlen($Key); $i++){
			$KeyList[$i] = ord(substr($Key, $i, 1));
		}

		// Step through string a character at a time
		for($i = 0; $i < strlen($InString); $i++) {
			// Get ASCII code from string, get ASCII code from key (loop through with MOD), XOR the two, get the character from the result
			// % is MOD (modulus), ^ is XOR
			$output.= chr(ord(substr($InString, $i, 1)) ^ ($KeyList[$i % strlen($Key)]));
		}

		// Return the result
		return $output;
	}

	function get_sagepay_crypt($params)
	{
		$crypt_url = "";
		$EncryptionPassword = $params["EncryptionPassword"];

		//** Build the crypt string plaintext **
		$crypt_url .= "VendorTxCode=" . $params["VendorTxCode"];
		$crypt_url .= "&Amount=" . $params["Amount"];
		$crypt_url .= "&Currency=" . $params["Currency"];
		$crypt_url .= "&Description=" . $params["Description"];
		$crypt_url .= "&SuccessURL=" . $params["SuccessURL"];
		$crypt_url .= "&FailureURL=" . $params["FailureURL"];

		if (isset($params["CustomerEmail"]) && strlen($params["CustomerEmail"])) {
			$crypt_url .= "&CustomerEmail=" . $params["CustomerEmail"];
		}
		if (isset($params["VendorEmail"]) && strlen($params["VendorEmail"])) {
			$crypt_url .= "&VendorEmail=" . $params["VendorEmail"];
		}
		if (isset($params["CustomerName"]) && strlen($params["CustomerName"])) {
			$crypt_url .= "&CustomerName=" . $params["CustomerName"];
		}
		if (isset($params["DeliveryAddress"]) && strlen(trim($params["DeliveryAddress"]))) {
			$crypt_url .= "&DeliveryAddress=" . $params["DeliveryAddress"];
		}
		if (isset($params["DeliveryPostCode"]) && strlen($params["DeliveryPostCode"])) {
			$crypt_url .= "&DeliveryPostCode=" . $params["DeliveryPostCode"];
		}
		if (isset($params["BillingAddress"]) && strlen(trim($params["BillingAddress"]))) {
			$crypt_url .= "&BillingAddress=" . $params["BillingAddress"];
		}
		if (isset($params["BillingPostCode"]) && strlen($params["BillingPostCode"])) {
			$crypt_url .= "&BillingPostCode=" . $params["BillingPostCode"];
		}
		// new 2.22 fields
		if (isset($params["ContactNumber"]) && strlen($params["ContactNumber"])) {
			$crypt_url .= "&ContactNumber=" . $params["ContactNumber"];
		}
		if (isset($params["ContactFax"]) && strlen($params["ContactFax"])) {
			$crypt_url .= "&ContactFax=" . $params["ContactFax"];
		}
		if (isset($params["AllowGiftAid"]) && strlen($params["AllowGiftAid"])) {
			$crypt_url .= "&AllowGiftAid=" . $params["AllowGiftAid"];
		}
		if (isset($params["ApplyAVSCV2"]) && strlen($params["ApplyAVSCV2"])) {
			$crypt_url .= "&ApplyAVSCV2=" . $params["ApplyAVSCV2"];
		}
		if (isset($params["Apply3DSecure"]) && strlen($params["Apply3DSecure"])) {
			$crypt_url .= "&Apply3DSecure=" . $params["Apply3DSecure"];
		}
		if (isset($params["Basket"]) && strlen($params["Basket"])) {
			$crypt_url .= "&Basket=" . $params["Basket"];
		}
		if (isset($params["EMailMessage"]) && strlen($params["EMailMessage"])) {
			$crypt_url .= "&EMailMessage=" . $params["EMailMessage"];
		}
		// new 2.23 fields
		if (isset($params["BillingAddress1"]) && strlen($params["BillingAddress1"])) {
			$crypt_url .= "&BillingAddress1=" . $params["BillingAddress1"];
		}
		if (isset($params["BillingAddress2"]) && strlen($params["BillingAddress2"])) {
			$crypt_url .= "&BillingAddress2=" . $params["BillingAddress2"];
		}
		if (isset($params["BillingPostCode"]) && strlen($params["BillingPostCode"])) {
			$crypt_url .= "&BillingPostCode=" . $params["BillingPostCode"];
		}
		if (isset($params["BillingPhone"]) && strlen($params["BillingPhone"])) {
			$crypt_url .= "&BillingPhone=" . $params["BillingPhone"];
		}
		if (isset($params["BillingFirstnames"]) && strlen($params["BillingFirstnames"])) {
			$crypt_url .= "&BillingFirstnames=" . $params["BillingFirstnames"];
		}
		if (isset($params["BillingSurname"]) && strlen($params["BillingSurname"])) {
			$crypt_url .= "&BillingSurname=" . $params["BillingSurname"];
		}
		if (isset($params["BillingCity"]) && strlen($params["BillingCity"])) {
			$crypt_url .= "&BillingCity=" . $params["BillingCity"];
		}
		if (isset($params["BillingCountry"]) && strlen($params["BillingCountry"])) {
			$crypt_url .= "&BillingCountry=" . $params["BillingCountry"];
		}
		if (isset($params["BillingState"]) && strlen($params["BillingState"])) {
			$crypt_url .= "&BillingState=" . $params["BillingState"];
		}
		if (isset($params["DeliveryAddress1"]) && strlen($params["DeliveryAddress1"])) {
			$crypt_url .= "&DeliveryAddress1=" . $params["DeliveryAddress1"];
		}
		if (isset($params["DeliveryPostCode"]) && strlen($params["DeliveryPostCode"])) {
			$crypt_url .= "&DeliveryPostCode=" . $params["DeliveryPostCode"];
		}
		if (isset($params["DeliveryFirstnames"]) && strlen($params["DeliveryFirstnames"])) {
			$crypt_url .= "&DeliveryFirstnames=" . $params["DeliveryFirstnames"];
		}
		if (isset($params["DeliverySurname"]) && strlen($params["DeliverySurname"])) {
			$crypt_url .= "&DeliverySurname=" . $params["DeliverySurname"];
		}
		if (isset($params["DeliveryAddress2"]) && strlen($params["DeliveryAddress2"])) {
			$crypt_url .= "&DeliveryAddress2=" . $params["DeliveryAddress2"];
		}
		if (isset($params["DeliveryCity"]) && strlen($params["DeliveryCity"])) {
			$crypt_url .= "&DeliveryCity=" . $params["DeliveryCity"];
		}
		if (isset($params["DeliveryCountry"]) && strlen($params["DeliveryCountry"])) {
			$crypt_url .= "&DeliveryCountry=" . $params["DeliveryCountry"];
		}
		if (isset($params["DeliveryState"]) && strlen($params["DeliveryState"])) {
			$crypt_url .= "&DeliveryState=" . $params["DeliveryState"];
		}
		if (isset($params["DeliveryPhone"]) && strlen($params["DeliveryPhone"])) {
			$crypt_url .= "&DeliveryPhone=" . $params["DeliveryPhone"];
		}
		
		//$crypt = base64_encode(simple_xor($crypt_url, $EncryptionPassword));
		$crypt = encryptAes($crypt_url, $EncryptionPassword);

		return $crypt;
	}

?>