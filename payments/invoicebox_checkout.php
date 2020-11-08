<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  invoicebox_checkout.php                                  ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * InvoiceBox Checkout handler by Viart LLC http://www.viart.com/
 */

	ini_set("display_errors", "1");
	error_reporting(E_ALL & ~E_STRICT);

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
		//echo $order_errors;
		//exit;
	}

	$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables, "", "no");

	$payment_name = get_setting_value($variables, "payment_name", "");
	$user_payment_name = get_setting_value($variables, "user_payment_name", $payment_name);

	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", $site_url);
	$sandbox = get_setting_value($pass_data, "sandbox");  
	$sandbox = strval($sandbox);
	$payment_url = get_setting_value($payment_parameters, "payment_url"); // Payment URL
	if (!$payment_url) {
		if ($sandbox == "1" || $sandbox == "true" || $sandbox == "yes") {
			$payment_url = "https://go-dev.invoicebox.ru/module_inbox_auto.u";
		} else {
			$payment_url = "https://go.invoicebox.ru/module_inbox_auto.u";
		}
	}
	

	$itransfer_language_ident = get_setting_value($payment_parameters, "itransfer_language_ident");  // ENG - English, RUS - Russian etc.
	$itransfer_participant_id = get_setting_value($payment_parameters, "itransfer_participant_id");  // Merchant ID 
	$itransfer_participant_ident = get_setting_value($payment_parameters, "itransfer_participant_ident");  // Merchant regional code 
	$api_security_key = get_setting_value($payment_parameters, "api_security_key");  // API security key

	$order_id = get_setting_value($variables, "order_id");  
	$order_total = get_setting_value($variables, "order_total", 0);  
	$tax_total = get_setting_value($variables, "tax_total", 0);  
	$total_quantity = get_setting_value($variables, "total_quantity", 0);  

	$itransfer_order_id = get_setting_value($payment_parameters, "itransfer_order_id", $order_id); // Order identifier assigned to an order in merchant's system
	$itransfer_order_amount = get_setting_value($payment_parameters, "itransfer_order_amount", $order_total); // Order total amount
	$itransfer_order_amount_vat = get_setting_value($payment_parameters, "itransfer_order_amount_vat", $tax_total); // Order total VAT amount

	$itransfer_order_currency_ident = get_setting_value($payment_parameters, "itransfer_order_currency_ident"); // Currency code
	$itransfer_order_description = get_setting_value($payment_parameters, "itransfer_order_description", "Order #".$itransfer_order_id); 
	$itransfer_body_type = get_setting_value($payment_parameters, "itransfer_body_type"); // PRIVATE - private person, LEGAL - company
	$itransfer_timelimit = get_setting_value($payment_parameters, "itransfer_timelimit"); // Payment time limit. After the specified time, the unpaid bill will be cancelled.
	/*
	if (!strlen($itransfer_timelimit)) { 
		$itransfer_timelimit = date("Y-m-d\TH:i:sP", time() + 24*3600); // 1 hour forward
	}//*/

	// calculate sign
	$itransfer_participant_sign = md5($itransfer_participant_id.$itransfer_order_id.$itransfer_order_amount.$itransfer_order_currency_ident.$api_security_key);


	// customer data

	$person_name = get_setting_value($variables, "name"); // Customer Name 
	$first_name = get_setting_value($variables, "first_name"); // Customer First Name 
	$last_name = get_setting_value($variables, "last_name"); // Customer Last Name
	$person_email = get_setting_value($variables, "email"); // Email Address
	$person_phone = get_setting_value($variables, "phone"); 
	$person_country = get_setting_value($variables, "country_code"); // Billing 2-letter Country Code 
	$person_address = get_setting_value($variables, "address"); // Billing Street Address
	$person_zip = get_setting_value($variables, "zip"); // Billing ZIP or Postal Code
	$person_city = get_setting_value($variables, "city"); // Billing City
	$person_state = get_setting_value($variables, "state_code"); // Billing 2-letter State Code

	$itransfer_person_name = get_setting_value($payment_parameters, "itransfer_person_name", $person_name); 
	$itransfer_person_email = get_setting_value($payment_parameters, "itransfer_person_email", $person_email); 
	$itransfer_person_phone = get_setting_value($payment_parameters, "itransfer_person_phone", $person_phone); 
	$itransfer_person_country = get_setting_value($payment_parameters, "itransfer_person_country", $person_country); 
	$itransfer_person_address = get_setting_value($payment_parameters, "itransfer_person_address", $person_address); 
	$itransfer_person_zip = get_setting_value($payment_parameters, "itransfer_person_zip", $person_zip); 
	$itransfer_person_city = get_setting_value($payment_parameters, "itransfer_person_city", $person_city); 
	$itransfer_person_state = get_setting_value($payment_parameters, "itransfer_person_state", $person_state); 
	$itransfer_url_returnsuccess = get_setting_value($payment_parameters, "itransfer_url_returnsuccess"); // The URL of the page to which the customer will be redirected upon completion of the payment
	if (!$itransfer_url_returnsuccess) {
		$itransfer_url_returnsuccess = $secure_url."order_final.php";
	}
	$itransfer_url_return = get_setting_value($payment_parameters, "itransfer_url_return");  // The URL of the page to which the customer will be redirected upon cancellation of the payment
	if (!$itransfer_url_return) {
		$itransfer_url_return = $secure_url."order_final.php?va_status=cancel";
	}
	$itransfer_testmode = get_setting_value($pass_data, "itransfer_testmode"); 
	
	$invoicebox_params = array(
		"itransfer_language_ident" => $itransfer_language_ident,
		"itransfer_participant_id" => $itransfer_participant_id,
		"itransfer_participant_ident" => $itransfer_participant_ident,
		"itransfer_participant_sign" => $itransfer_participant_sign,

		"itransfer_order_id" => $itransfer_order_id,
		"itransfer_order_amount" => $itransfer_order_amount,
		"itransfer_order_amount_vat" => $itransfer_order_amount_vat,
		"itransfer_order_currency_ident" => $itransfer_order_currency_ident,

		"itransfer_order_description" => $itransfer_order_description,
		"itransfer_body_type" => $itransfer_body_type,
		"itransfer_timelimit" => $itransfer_timelimit,

		"itransfer_person_name" => $itransfer_person_name,
		"itransfer_person_email" => $itransfer_person_email,
		"itransfer_person_phone" => $itransfer_person_phone,
		"itransfer_person_country" => $itransfer_person_country,
  
		"itransfer_person_address" => $itransfer_person_address,
		"itransfer_person_zip" => $itransfer_person_zip,
		"itransfer_person_city" => $itransfer_person_city,
		"itransfer_person_state" => $itransfer_person_state,
		"itransfer_url_returnsuccess" => $itransfer_url_returnsuccess,
		"itransfer_url_return" => $itransfer_url_return,
		"itransfer_testmode" => $itransfer_testmode,
	);

		/*
		$invoicebox_params["itransfer_item1_name"] = "Product";
		$invoicebox_params["itransfer_item1_quantity"] = 1;
		$invoicebox_params["itransfer_item1_measure"] = "pc.";
		$invoicebox_params["itransfer_item1_price"] = $order_total;
		$invoicebox_params["itransfer_item1_vat"] = 0;//*/
//*
	$index = 0;
	$payment_items = $variables["payment_items"];
	foreach ($payment_items as $items_index => $item_info) {
		//number_format($item_info["price_excl_tax"], 2, ".", "");
		$price_excl_tax = $item_info["price_excl_tax"];
		$price_incl_tax = $item_info["price_incl_tax"];
		$price_tax = $price_incl_tax - $price_excl_tax;

		if ($price_incl_tax > 0) {
			$index++;
			$invoicebox_params["itransfer_item".$index."_name"] = $item_info["name"];
			$invoicebox_params["itransfer_item".$index."_quantity"] = $item_info["quantity"];
			$invoicebox_params["itransfer_item".$index."_measure"] = "pc.";
			$invoicebox_params["itransfer_item".$index."_price"] = $price_incl_tax;
			$invoicebox_params["itransfer_item".$index."_vat"] = $price_tax;
		}

	}//*/

	$t = new VA_Template('.'.$settings["templates_dir"]);
	$t->set_file("main","payment.html");
	$t->set_var("CHARSET", "utf-8");

	$payment_name = get_setting_value($variables, "payment_name", "");
	$user_payment_name = get_setting_value($variables, "user_payment_name", $payment_name);
	$goto_payment_message = str_replace("{payment_system}", $user_payment_name, GOTO_PAYMENT_MSG);
	$goto_payment_message = str_replace("{button_name}", CONTINUE_BUTTON, $goto_payment_message);
	$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);
	$t->set_var("payment_url",$payment_url);
	$t->set_var("submit_method", "post");

	foreach ($invoicebox_params as $parameter_name => $parameter_value) {
		if (strlen($parameter_value)) {
			$t->set_var("parameter_name", $parameter_name);
			$t->set_var("parameter_value", $parameter_value);
			$t->parse("parameters", true);
		}
	}

	$t->sparse("submit_payment", false);
	$t->pparse("main");

?>