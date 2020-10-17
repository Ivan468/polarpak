<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  migs_validate.php                                        ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
http://localhost/c2s/order_final.php?vpc_Amount=0
&vpc_BatchNo=0&vpc_Command=pay&vpc_Locale=en_AU
&vpc_Merchant=TESTBBL539536
&vpc_Message=E5015%3A+Merchant+[Everything+Earthmoving]+does+not+support+currency+[USD]
&vpc_OrderInfo=687
&vpc_TransactionNo=0
&vpc_TxnResponseCode=7
*/

/*
 * Mastercard Internet Gateway Service Checkout handler by http://www.viart.com/
 */

	$vc = get_session("session_vc");
	$order_id = get_session("session_order_id");

	$order_errors = check_order($order_id, $vc);
	if($order_errors) {
		echo $order_errors;
		exit;
	}
	
	$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables, "", "none");
	$SecureHashSecret = get_setting_value($payment_parameters, "vpc_SecureHashSecret", ""); // get Secure Hash Secret

	$txnResponseCode = get_param("vpc_TxnResponseCode");
	$transaction_id  = get_param("vpc_TransactionNo");
	$message         = get_param("vpc_Message");
  $cardType        = get_param("vpc_CardType");
  $AuthorizeId     = get_param("vpc_AuthorizeId");
	$vpc_SecureHash = get_setting_value($_GET, "vpc_SecureHash", ""); // passed from payment system VPC Secure Hash


	ksort ($_GET);
	$hash_string = "";
	foreach ($_GET as $param_name => $param_value) {
		if (preg_match("/^(vpc_|user_)/i", $param_name) && !preg_match("/SecureHash/i", $param_name)) {
			if ($hash_string) { $hash_string .= "&"; }
			$hash_string .= $param_name."=".$param_value;
		}
	}
	// generate vpc_SecureHash
	$SecureHashSecret = pack('H*', $SecureHashSecret);
	$our_SecureHash = hash_hmac("sha256", $hash_string, $SecureHashSecret); 

	if ($txnResponseCode != "0") {
		$error_message = $message;
		if (!$error_message) {
			$error_message = "Your transaction has been declined.";
		}
	} else if (strtolower($vpc_SecureHash) != strtolower($our_SecureHash)) {
		$error_message = "Invalid hash value <br />";
		$error_message .= "Hash String: " . $hash_string . "<br />";
		$error_message .= "Our Hash: ". $our_SecureHash . "<br />";
		$error_message .= "Rec Hash: ". $vpc_SecureHash;
	}

	if (!$error_message) {
		// authorization and 3D fields
		$verType         = get_param("vpc_VerType");
		$verStatus       = get_param("vpc_VerStatus");
		$verSecurLevel   = get_param("vpc_VerSecurityLevel");
		$token           = get_param("vpc_VerToken");
		$variables["authorization_code"] = $AuthorizeId;
		$variables["secure_3d_check"] = get_param("vpc_3DSenrolled");
		$variables["secure_3d_xid"] = get_param("vpc_3DSXID");
		$variables["secure_3d_eci"] = get_param("vpc_3DSECI");
    $variables["secure_3d_status"] = get_param("vpc_3DSstatus");
  }


?>