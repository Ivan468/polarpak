<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  stripe_v3_checkout.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Stripe v3 Checkout module for Viart Shop http://www.viart.com/
 */	
	$language_code = "";
	$is_admin_path = true;
	$is_sub_folder = true;
	include_once(__DIR__."/../includes/common.php");
	include_once(__DIR__."/../includes/order_items.php");
	include_once(__DIR__."/../includes/parameters.php");
	include_once(__DIR__."/../includes/profile_functions.php");
	include_once(__DIR__."/../messages/".$language_code."/cart_messages.php");

	if (file_exists(__DIR__."/stripe_v3/init.php")) {
		require(__DIR__.'/stripe_v3/init.php');
	} else if (file_exists(__DIR__."/stripe/init.php")) {
		require(__DIR__.'/stripe/init.php');
	} else {
		die("Please download Stripe module from https://github.com/stripe/stripe-php/releases and uploaded unzip files to payments/stripe/ folder.");
	}

	$vc = get_session("session_vc");
	$order_id = get_session("session_order_id");

	$order_errors = check_order($order_id, $vc);
	if($order_errors) {
		echo $order_errors;
		exit;
	}

	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", $site_url);

	// get payment data
	$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_params, $pass_data, $variables);

	// general payment data
	$publishable_key = get_setting_value($payment_parameters, "publishable_key"); 
	$secret_key = get_setting_value($payment_parameters, "secret_key"); 
	$currency_code = get_setting_value($payment_parameters, "currency_code", "USD"); 
	$order_total = get_setting_value($variables, "order_total"); 
	$order_total_100 = get_setting_value($variables, "order_total_100"); 

	// required request parameters 
	$success_url = get_setting_value($pass_data, "success_url", $secure_url."order_final.php"); 
	$cancel_url = get_setting_value($pass_data, "cancel_url", $secure_url."order_final.php?va_status=cancel"); 
	$payment_method_types = get_setting_value($payment_parameters, "payment_method_types", "card");

	// additional request parameters
	$billing_address_collection = get_setting_value($payment_parameters, "billing_address_collection");
	$client_reference_id = get_setting_value($payment_parameters, "client_reference_id", $order_id);
	$order_description = get_setting_value($payment_parameters, "order_description");
	if (!$order_description) {
		$order_description = "Order #".$order_id;
		$customer_name = get_user_name($variables, "full");
		if ($customer_name) {
			$order_description .= " :: ".$customer_name;
		}
		$customer_email = get_setting_value($variables, "email");
	}

	$customer = get_setting_value($variables, "user_id");
	$customer_email = get_setting_value($variables, "email");
	$locale = get_setting_value($payment_parameters, "locale");
	$mode = get_setting_value($payment_parameters, "mode", "payment");
	$submit_type = get_setting_value($payment_parameters, "submit_type", "pay");

	// prepare product list
	$line_items = array(); $line_item = 0;
	$payment_items = $variables["payment_items"];

	// check if there are any zero and negative products available to use a summary order product
	$summary_product = false;
	foreach ($payment_items as $items_index => $item_info) {
		$item_price = $item_info["price"];
		if ($item_price <= 0) {
			$summary_product = true;
			break;
		}
	}

	if ($summary_product) {
		$line_items[$line_item] = array(
			"name" => $order_description,
			"amount" => intval($order_total_100),
			"currency" => $currency_code,
			"quantity" => 1,
		);
	} else {
		foreach ($payment_items as $items_index => $item_info) {
			$item_price = intval(round($item_info["price"] * 100));
  
			$item_id = $item_info["id"];
			$item_type = $item_info["type"];
			$item_name = $item_info["name"];
			$item_quantity = $item_info["quantity"];
			$item_desc = "";
			$item_image = "";
  
			$line_items[$line_item] = array(
				"name" => $item_name,
				"amount" => $item_price,
				"currency" => $currency_code,
				"quantity" => $item_quantity,
			);
			if ($item_desc) {
				$line_items[$line_item]["description"] = $item_desc;
			}
			if ($item_image) {
				$line_items[$line_item]["images"] = array($item_image);
			}
			$line_item++;
		}
	}

	// prepare and send request
	$request = array(
		"payment_method_types" => array($payment_method_types),
		"line_items" => $line_items,
		"success_url" => $success_url,
		"cancel_url" => $cancel_url,
	);

	if ($order_description) {
		$request["payment_intent_data"] = array("description" => $order_description);
	}
	if ($billing_address_collection) {
		$request["billing_address_collection"] = $billing_address_collection;
	}
	if ($client_reference_id) {
		$request["client_reference_id"] = $client_reference_id;
	}
	if ($customer_email) {
		$request["customer_email"] = $customer_email;
	}
	if ($locale) {
		$request["locale"] = $locale;
	}
	if ($mode) {
		$request["mode"] = $mode;
	}
	if ($submit_type) {
		$request["submit_type"] = $submit_type;
	}

	try {
		$stripe_session_id = "";
		$stripe_payment_intent = "";
		\Stripe\Stripe::setApiKey($secret_key);
		$session = \Stripe\Checkout\Session::create($request);
  
		$stripe_session_id = $session->id;
		$stripe_payment_intent = $session->payment_intent;
  
		// save Stripe session_id and payment_intent data for the order
		$data = array(
			"session_id" => $stripe_session_id,
			"payment_intent" => $stripe_payment_intent,
		);
		$sql  = " UPDATE ".$table_prefix."orders ";
		$sql .= " SET success_message=" . $db->tosql(json_encode($data), TEXT);
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
  
		$t = new VA_Template('.'.$settings["templates_dir"]);
		$t->set_file("main","payment.html");
		$t->set_var("CHARSET", "utf-8");
		$t->set_var("publishable_key", htmlspecialchars($publishable_key));
		$t->set_var("stripe_session_id", htmlspecialchars($session->id));
  
		$payment_name = get_setting_value($variables, "payment_name", "");
		$user_payment_name = get_setting_value($variables, "user_payment_name", $payment_name);
		$user_payment_name = get_translation($user_payment_name);
  
		$goto_payment_message = va_message("GOTO_PAYMENT_MSG");	
		if (($pos = strpos($goto_payment_message, "{payment_system}."))) {
			$goto_payment_message = substr($goto_payment_message, 0, $pos + 17);
		}
		$goto_payment_message = str_replace("{payment_system}", $user_payment_name, $goto_payment_message);
  
		$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);
		$t->parse("stripe_v3_payment", false);
		$t->pparse("main");
	} catch(Exception $e) {
		$error_message = $e->getMessage();
		echo "ERROR: " . $error_message;
		echo "<pre>";
		echo "REQUEST<hr>";
		var_export($request);
		echo "<hr></pre>";
	}
