<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  stripe_checkout.php                                      ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Stripe Payment Gateway handler by http://www.viart.com/
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

	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", $site_url);
	$return_url = $secure_url."order_final.php";

	$payment_url = get_setting_value($variables, "payment_url", "");
	$payment_name = get_setting_value($variables, "payment_name", "");
	$user_payment_name = get_setting_value($variables, "user_payment_name", $payment_name);
	$user_payment_name = get_translation($user_payment_name);
	$button_label = get_setting_value($variables, "data-label", "Pay with Card");


	$t = new VA_Template('.'.$settings["templates_dir"]);
	$t->set_file("main","payment.html");
	$t->set_var("CHARSET", "utf-8");
	$t->set_var("return_url", htmlspecialchars($return_url));

	$goto_payment_message = str_replace("{payment_system}", $user_payment_name, GOTO_PAYMENT_MSG);
	$goto_payment_message = str_replace("{button_name}", $button_label, $goto_payment_message);

	//$t->set_var("CHECKOUT_PAYMENT_TITLE", CHECKOUT_PAYMENT_TITLE);
	//$t->set_var("CONTINUE_BUTTON", CONTINUE_BUTTON);
	$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);
	$t->set_var("payment_url", $payment_url);
	$t->set_var("submit_method", "post");
	foreach ($pass_data as $parameter_name => $parameter_value) {
		if ($parameter_name != "data-secret-key") {
			$t->set_var("parameter_name", htmlspecialchars($parameter_name));
			$t->set_var("parameter_value", htmlspecialchars($parameter_value));
			$t->parse("stripe_parameters", true);
		}
	}

	$t->parse("stripe_payment", false);
	$t->pparse("main");

