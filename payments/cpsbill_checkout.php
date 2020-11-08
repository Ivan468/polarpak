<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  cpsbill_checkout.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Capital Payment Solutions handler by http://www.viart.com/
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
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables, "", "no");

	$payment_url = "https://cpsbill.com/checkout";

	$payment_name = get_setting_value($variables, "payment_name", "");
	$user_payment_name = get_setting_value($variables, "user_payment_name", $payment_name);


	// calculate check parameters
	$MerchantSecurityKey = get_setting_value($payment_parameters, "MerchantSecurityKey", "");
	$MerchantNumber = get_setting_value($payment_parameters, "MerchantNumber", "");
	$Amount = get_setting_value($payment_parameters, "Amount", "");


	$OrderCSum = md5($MerchantSecurityKey . $MerchantNumber . $Amount);

	$pass_data["OrderCSum"] = $OrderCSum;

	$t = new VA_Template('.'.$settings["templates_dir"]);
	$t->set_file("main","payment.html");
	$t->set_var("CHARSET", "utf-8");

	$checkout_payment_title = iconv(CHARSET, "UTF-8", CHECKOUT_PAYMENT_TITLE);

	$goto_payment_message = str_replace("{payment_system}", $user_payment_name, GOTO_PAYMENT_MSG);
	$goto_payment_message = str_replace("{button_name}", CONTINUE_BUTTON, $goto_payment_message);
	$goto_payment_message = iconv(CHARSET, "UTF-8", $goto_payment_message);

	$continue_button = iconv(CHARSET, "UTF-8", CONTINUE_BUTTON);

	$t->set_var("CHECKOUT_PAYMENT_TITLE", $checkout_payment_title);
	$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);
	$t->set_var("CONTINUE_BUTTON", $continue_button);
	$t->set_var("payment_url", $payment_url);
	$t->set_var("submit_method", "post");
	foreach ($pass_data as $parameter_name => $parameter_value) {
		//define("CHARSET", "iso-8859-1");
		$parameter_value = iconv(CHARSET, "UTF-8", $parameter_value);

		$t->set_var("parameter_name", $parameter_name);
		$t->set_var("parameter_value", $parameter_value);
		$t->parse("parameters", true);
	}
	$t->sparse("submit_payment", false);
	$t->pparse("main");

?>

