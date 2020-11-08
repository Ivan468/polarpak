<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  commweb_checkout.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * CommWeb Checkout handler by http://www.viart.com/
 */

	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");
	include_once ($root_folder_path ."includes/parameters.php");

	$vc = get_session("session_vc");
	$order_id = get_session("session_order_id");

	$order_errors = check_order($order_id, $vc);
	if($order_errors) {
		echo $order_errors;
		exit;
	}
	
	$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables, "");

	$payment_name = get_setting_value($variables, "payment_name", "");
	$user_payment_name = get_setting_value($variables, "user_payment_name", $payment_name);

	$pass_data = array();
	foreach ($payment_parameters as $parameter_name => $parameter_value) {
		if (isset($pass_parameters[$parameter_name]) && $pass_parameters[$parameter_name] == 1) {
			$pass_data[$parameter_name] = $parameter_value;
			if(isset($pass_data[strtolower($parameter_name)])){
				unset($pass_data[strtolower($parameter_name)]);
				$pass_data[$parameter_name] = $parameter_value;
			}
		}
	}

	// check parameters
	$MerchTxnRef = get_setting_value($payment_parameters, "vpc_MerchTxnRef", "");
	$MerchantID = get_setting_value($payment_parameters, "vpc_Merchant", "");
	$OrderInfo = get_setting_value($payment_parameters, "vpc_OrderInfo", "");
	$PurchaseAmount = get_setting_value($payment_parameters, "vpc_Amount", "");
	$Locale = get_setting_value($payment_parameters, "vpc_Locale", "");
	$TicketNo = get_setting_value($payment_parameters, "vpc_TicketNo", "");
	$ReturnURL = get_setting_value($payment_parameters, "vpc_ReturnURL", "");
	$AccessCode = get_setting_value($payment_parameters, "vpc_AccessCode", "");
	$SecureHash = get_setting_value($payment_parameters, "SecureHash", "");

	//When running VPC sample code you may need to know the Payment Server URL:
	//https://migs.mastercard.com.au/vpcpay   for 3-party (CommWeb hosted) transactions.
	//https://migs.mastercard.com.au/vpcdps   for 2-party (merchant hosted) transactions.
	$payment_url = "https://migs.mastercard.com.au/vpcpay";
 
	ksort ($pass_data);
	$key_string = $SecureHash;
	$sha256_data = "";
	foreach ($pass_data as $key => $value) {
		if ($sha256_data) { $sha256_data .= "&"; }
		$key_string .= $value;
		$sha256_data .= $key."=".$value;
	}
	// $vpc_SecureHash = md5($key_string); // old MD5 algorithm
	$vpc_SecureHash = strtoupper(hash_hmac ("SHA256", $sha256_data, pack("H*", $SecureHash)));
	$pass_data["vpc_SecureHash"] = $vpc_SecureHash;
	$pass_data["vpc_SecureHashType"] = "SHA256";

	$t = new VA_Template('.'.$settings["templates_dir"]);
	$t->set_file("main","payment.html");
	$goto_payment_message = str_replace("{payment_system}", $user_payment_name, GOTO_PAYMENT_MSG);
	$goto_payment_message = str_replace("{button_name}", CONTINUE_BUTTON, $goto_payment_message);
	$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);
	$t->set_var("payment_url", $payment_url);
	$t->set_var("submit_method", "post");
	foreach ($pass_data as $parameter_name => $parameter_value) {
		$t->set_var("parameter_name", $parameter_name);
		$t->set_var("parameter_value", $parameter_value);
		$t->parse("parameters", true);
	}
	$t->sparse("submit_payment", false);
	$t->pparse("main");

?>

