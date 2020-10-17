<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  ogone_validate.php                                       ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Ogone e-Commerce handler by http://www.viart.com/
 */

	// check parameters
	$request_params = count($_POST) ? $_POST : $_GET;

	$SHA_OUT = get_setting_value($payment_parameters, "SHA-OUT", ""); // get SHA-OUT Hash to calculate SHASIGN
	$OGONE_SHASIGN = get_setting_value($request_params, "SHASIGN", ""); // passed from Ogone payment system VPC Secure Hash
	if (isset($request_params["SHASIGN"])) {
		unset($request_params["SHASIGN"]);
	}

	// convert all keys to uppercase
	$ogone_data = array();
	foreach ($request_params as $key => $value ) {
		$ogone_data[strtoupper($key)] = $value;
	}

	ksort ($ogone_data);
	$key_string = "";
	foreach ($ogone_data as $key => $value ) {
		if (strlen($value)) {
			// use variable with values only
			$key_string .= $key."=".$value.$SHA_OUT;
		}
	}
	$OUR_SHASIGN = sha1($key_string);

	if (strtolower($OGONE_SHASIGN) != strtolower($OUR_SHASIGN)) {
		$error_message  = "Invalid hash value Ogone - Shop: " . $OGONE_SHASIGN . " - " . $OUR_SHASIGN."<br/>\n";
		$error_message .= "Key String: " . $key_string;
		return;
	}

/* URL Example:
http://www.example.com/?
orderID=13
&currency=EUR
&amount=124%2E34
&PM=CreditCard
&ACCEPTANCE=test123
&ED=0214
&CN=person+testperson1
&TRXDATE=03%2F08%2F13
&NCERROR=0
&IP=109%2E251%2E200%2E123
&SHASIGN=15A00BDC1EE4A1AE4FBAAF4A1DC3B519AA119164
*/

/*	
0 - Invalid or incomplete
1 - Cancelled by customer
2 - Authorisation declined
5 - Authorised
9 - Payment requested
*/

	$transaction_id = get_param("PAYID");
	$CARDNO = get_param("CARDNO"); // XXXXXXXXXXXX1111
	$ED = get_param("ED"); // 0214
	$BRAND = get_param("BRAND"); // VISA
	$NCERROR = get_param("NCERROR"); // error code
	$STATUS = get_param("STATUS"); 
	$IP = get_param("IP"); 

	if (!$error_message) {
		if ($STATUS == 5) {
			$success_message = "The authorization has been accepted.";
		} else if ($STATUS == 9) {
			$success_message = "The payment has been accepted.";
		} else if ($STATUS == 1) {
			$error_message = "Cancelled by customer.";
		} else if ($STATUS == 2) {
			//$error_message = "The authorization has been declined by the financial institution.";
			$error_message = "Your transaction has been declined.";
		} else if ($STATUS == 51) {
			$pending_message = "The authorization will be processed offline.";
		} else if ($STATUS == 91) {
			$pending_message = "The data capture will be processed offline.";
		} else if ($STATUS == 52 || $STATUS == 92 || $STATUS == 93) {
			$error_message = "A technical problem arose during the authorization/payment process, giving an unpredictable result.";
		} else if ($STATUS == 0) {
			$error_message = "Invalid or incomplete data (error: " . $NCERROR . ")";
		} else {
			$error_message = "Unknown status: " . $STATUS;
		}
	}

?>
