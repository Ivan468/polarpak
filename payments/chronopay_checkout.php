<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  chronopay_checkout.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Chronopay checkout handler by http://www.viart.com/
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
	$product_id = get_setting_value($payment_parameters, "product_id", "");
	$product_price = get_setting_value($payment_parameters, "product_price", "");
	$order_id = get_setting_value($payment_parameters, "order_id", "");
	$sharedsec = get_setting_value($payment_parameters, "sharedsec", "");

	// Chronpay URL
	$payment_url = "https://payments.chronopay.com/";

	// pass sign as one of the parameter 
	$sign = md5($product_id."-".$product_price."-".$sharedsec); // if payment uniqueness used
	//$sign = md5($product_id."-".$product_price."-".$order_id."-".$sharedsec); // if payment uniqueness used
	$pass_data["sign"] = $sign;
 
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

