<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  authorize_sim_checkout.php                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Authorize.Net SIM Checkout handler by http://www.viart.com/
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

	// set Authorize.net checkout url
	$x_sandbox = get_setting_value($payment_parameters, "x_sandbox");
	$sandbox = get_setting_value($payment_parameters, "sandbox", $x_sandbox);
	$sandbox = strtolower($sandbox);
	if (isset($pass_data["x_sandbox"])) { unset($pass_data["x_sandbox"]); }
	if (isset($pass_data["sandbox"])) { unset($pass_data["sandbox"]); }
	if ($sandbox == "1" || $sandbox == "true" || $sandbox == "yes") {
		$payment_url = "https://test.authorize.net/gateway/transact.dll";
	} else {
		$payment_url = "https://secure.authorize.net/gateway/transact.dll";
	}

	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", $site_url);
	$return_url = $secure_url."order_final.php";

	$payment_name = get_setting_value($variables, "payment_name", "");
	$user_payment_name = get_setting_value($variables, "user_payment_name", $payment_name);

	$x_login = get_setting_value($payment_parameters, "x_login");
	$x_secret = get_setting_value($payment_parameters, "x_secret");
	$x_signature_key = get_setting_value($payment_parameters, "x_signature_key", $x_secret); // use x_secret parameter if x_signature_key wasn't set
	$x_show_form = get_setting_value($payment_parameters, "x_show_form", "PAYMENT_FORM");
	$x_type = get_setting_value($payment_parameters, "x_type", "AUTH_CAPTURE");
	$x_amount = get_setting_value($payment_parameters, "x_amount", $variables["order_total"]); // amount to pay
	$x_currency_code = get_setting_value($payment_parameters, "x_currency_code", "USD");
	// never don't pass security parameters
	if (isset($pass_data["x_secret"])) { unset($pass_data["x_secret"]); }
	if (isset($pass_data["x_signature_key"])) { unset($pass_data["x_signature_key"]); }
	if (isset($pass_data["x_transaction_key"])) { unset($pass_data["x_transaction_key"]); }
	if (isset($pass_data["x_tran_key"])) { unset($pass_data["x_tran_key"]); }

	$x_invoice_num = get_setting_value($payment_parameters, "x_invoice_num", $order_id);
	$x_fp_sequence = get_setting_value($payment_parameters, "x_fp_sequence", $order_id);
	$x_fp_timestamp = time();
	$x_method = get_setting_value($payment_parameters, "x_method", "CC");
	// calculate FP hash
	$x_fp_hash = hash_hmac("sha512", $x_login."^".$x_fp_sequence."^".$x_fp_timestamp."^".$x_amount."^".$x_currency_code, hex2bin($x_signature_key));

	// prepare parameters to pass
	$order_email = ($variables["email"]) ? $variables["email"] : $variables["delivery_email"];
	$pass_data["x_email"] = $order_email;

	// set basic parameters 
	$pass_data["x_version"] = "3.1";
	$pass_data["x_relay_response"] = "TRUE";
	$pass_data["x_relay_url"] = $return_url;
	$pass_data["x_show_form"] = $x_show_form;
	$pass_data["x_type"] = $x_type;
	$pass_data["x_amount"] = $x_amount;
	$pass_data["x_currency_code"] = $x_currency_code;
	$pass_data["x_method"] = $x_method;

	// unset variables if they were set and use only lowercase parameters
	$order_user_id = $variables["user_id"];
	$order_user_ip = $variables["user_ip"];
	if (isset($pass_data["x_Cust_ID"])) { unset($pass_data["x_Cust_ID"]); }
	if (isset($pass_data["x_Customer_IP"])) { unset($pass_data["x_Customer_IP"]);	}
	$pass_data["x_cust_id"] = $order_user_id;
	$pass_data["x_customer_ip"] = $order_user_ip;

	$pass_data["x_invoice_num"] = $x_invoice_num;
	$pass_data["x_fp_sequence"] = $x_fp_sequence;
	$pass_data["x_fp_timestamp"] = $x_fp_timestamp;
	$pass_data["x_fp_hash"] = $x_fp_hash;

	// set billing data
	$pass_data["x_first_name"] = $variables["first_name"];
	$pass_data["x_last_name"] = $variables["last_name"];
	$pass_data["x_address"] = $variables["address"];
	$pass_data["x_city"] = $variables["city"];
	$pass_data["x_state"] = $variables["state"];
	$pass_data["x_zip"] = $variables["zip"];
	$pass_data["x_country"] = $variables["country"];
	if ($variables["phone"]) {
		$pass_data["x_phone"] = $variables["phone"];
	} else if ($variables["daytime_phone"]) {
		$pass_data["x_phone"] = $variables["daytime_phone"];
	} else if ($variables["daytime_phone"]) {
		$pass_data["x_phone"] = $variables["daytime_phone"];
	} else if ($variables["evening_phone"]) {
		$pass_data["x_phone"] = $variables["evening_phone"];
	} else if ($variables["cell_phone"]) {
		$pass_data["x_phone"] = $variables["cell_phone"];
	}
	$pass_data["x_fax"] = $variables["fax"];
	$pass_data["x_company"] = $variables["company_name"];

	// set shipping data
	$pass_data["x_ship_to_first_name"] = $variables["delivery_first_name"];
	$pass_data["x_ship_to_last_name"] = $variables["delivery_last_name"];
	$pass_data["x_ship_to_address"] = $variables["delivery_address"];
	$pass_data["x_ship_to_city"] = $variables["delivery_city"];
	$pass_data["x_ship_to_state"] = $variables["delivery_state"];
	$pass_data["x_ship_to_zip"] = $variables["delivery_zip"];
	$pass_data["x_ship_to_country"] = $variables["delivery_country"];
	$pass_data["x_ship_to_company"] = $variables["delivery_company_name"];

	$t = new VA_Template('.'.$settings["templates_dir"]);
	$t->set_file("main","payment.html");
	$t->set_var("CHARSET", "utf-8");
	$t->set_var("return_url", htmlspecialchars($return_url));

	$goto_payment_message = str_replace("{payment_system}", $user_payment_name, GOTO_PAYMENT_MSG);
	$goto_payment_message = str_replace("{button_name}", CONTINUE_BUTTON, $goto_payment_message);

	$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);
	$t->set_var("payment_url", $payment_url);
	$t->set_var("submit_method", "post");
	foreach ($pass_data as $parameter_name => $parameter_value) {
		$t->set_var("parameter_name", htmlspecialchars($parameter_name));
		$t->set_var("parameter_value", htmlspecialchars($parameter_value));
		$t->parse("parameters", true);
	}

	$t->sparse("submit_payment", false);
	$t->pparse("main");

