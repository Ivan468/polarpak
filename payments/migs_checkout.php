<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  migs_checkout.php                                        ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Mastercard Internet Gateway Service Checkout handler by http://www.viart.com/
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
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables, "", "none");

	$payment_name = get_setting_value($variables, "payment_name", "");
	$user_payment_name = get_setting_value($variables, "user_payment_name", $payment_name);

	// check parameters
	$MerchTxnRef = get_setting_value($payment_parameters, "vpc_MerchTxnRef", "");
	$MerchantID = get_setting_value($payment_parameters, "vpc_Merchant", "");
	$OrderInfo = get_setting_value($payment_parameters, "vpc_OrderInfo", "");
	$PurchaseAmount = get_setting_value($payment_parameters, "vpc_Amount", "");
	$Locale = get_setting_value($payment_parameters, "vpc_Locale", "");
	$TicketNo = get_setting_value($payment_parameters, "vpc_TicketNo", "");
	$ReturnURL = get_setting_value($payment_parameters, "vpc_ReturnURL", "");
	$AccessCode = get_setting_value($payment_parameters, "vpc_AccessCode", "");
	$SecureHashSecret = get_setting_value($payment_parameters, "vpc_SecureHashSecret", "");

	//When running VPC sample code you may need to know the Payment Server URL:
	// 2-Party Payment Model
	// https://migs.mastercard.com.au/vpcdps  
	// 3-Party Payment Model
	// https://migs.mastercard.com.au/vpcpay  
	$payment_url = "https://migs.mastercard.com.au/vpcpay";
 
	ksort ($pass_data);
	$hash_string = "";
	foreach ($pass_data as $param_name => $param_value) {
		if (preg_match("/^(vpc_|user_)/i", $param_name) && !preg_match("/SecureHash/i", $param_name)) {
			if ($hash_string) { $hash_string .= "&"; }
			$hash_string .= $param_name."=".$param_value;
		}
	}
	// generate vpc_SecureHash
	$SecureHashSecret = pack('H*', $SecureHashSecret);
	$vpc_SecureHash = hash_hmac("sha256", $hash_string, $SecureHashSecret); 
	$pass_data["vpc_SecureHash"] = $vpc_SecureHash;
 

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

