<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  commweb_validate.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * CommWeb Checkout handler by http://www.viart.com/
 */

	$vc = get_session("session_vc");
	$order_id = get_session("session_order_id");

	$order_errors = check_order($order_id, $vc);
	if($order_errors) {
		echo $order_errors;
		exit;
	}
	
	$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables, "");
	$SecureHash = get_setting_value($payment_parameters, "SecureHash", ""); // get Secure Hash

	$vpc_SecureHash = get_setting_value($_GET, "vpc_SecureHash", ""); // passed from payment system VPC Secure Hash
	$vpc_SecureHashType = get_setting_value($_GET, "vpc_SecureHashType", ""); // passed from payment system VPC Secure Hash Type
	if (isset($_GET["vpc_SecureHash"])) { unset($_GET["vpc_SecureHash"]); }
	if (isset($_GET["vpc_SecureHashType"])) { unset($_GET["vpc_SecureHashType"]); }
	ksort ($_GET);
	$key_string = $SecureHash;
	$sha256_data = "";
	foreach ($_GET as $key => $value) {
		if ($sha256_data) { $sha256_data .= "&"; }
		$key_string .= $value;
		$sha256_data .= $key."=".$value;
	}
	if (strtoupper($vpc_SecureHashType) == "SHA256") {
		$our_SecureHash = hash_hmac ("SHA256", $sha256_data, pack("H*", $SecureHash));
	} else {
		$our_SecureHash = md5($key_string);
	}

	if (strtoupper($vpc_SecureHash) != strtoupper($our_SecureHash)) {
		$error_message = "Invalid hash value";
	}

	$txnResponseCode = get_param("vpc_TxnResponseCode");
	$transaction_id  = get_param("vpc_TransactionNo");
	$message         = get_param("vpc_Message");
  $cardType        = get_param("vpc_CardType");
	$variables["authorization_code"] = get_param("vpc_AuthorizeId");


	// 3D fields
	$verType         = get_param("vpc_VerType");
	$verStatus       = get_param("vpc_VerStatus");
	$verSecurLevel   = get_param("vpc_VerSecurityLevel");
	$token           = get_param("vpc_VerToken");
	$variables["secure_3d_check"] = get_param("vpc_3DSenrolled");
	$variables["secure_3d_xid"] = get_param("vpc_3DSXID");
	$variables["secure_3d_eci"] = get_param("vpc_3DSECI");
  $variables["secure_3d_status"] = get_param("vpc_3DSstatus");

	if (!$error_message) {
		if ($txnResponseCode != "0") {
			$error_message = $message;
			if (!$error_message) {
				$error_message = "Your transaction has been declined.";
			}
		}
	}


?>