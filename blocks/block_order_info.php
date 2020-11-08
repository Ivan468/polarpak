<?php

	// TODO: check default voucher settings
	$voucher_settings = get_settings("user_voucher");
	$default_voucher_purchase = get_setting_value($voucher_settings, "voucher_purchase", 0);
	$default_purchase_fee_percent = get_setting_value($voucher_settings, "purchase_fee_percent", 0); 
	$default_purchase_fee_amount = get_setting_value($voucher_settings, "purchase_fee_amount", 0); 

	// check user settings
	$user_id = get_session("session_user_id");
	$user_settings = user_settings($user_id);
	$voucher_purchase = get_setting_value($user_settings, "voucher_purchase", $default_voucher_purchase);
	$purchase_fee_percent = get_setting_value($user_settings, "purchase_fee_percent", $default_purchase_fee_percent); 
	$purchase_fee_amount = get_setting_value($user_settings, "purchase_fee_amount", $default_purchase_fee_amount); 

	// include libraries for Ajax call
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./includes/record.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/order_items.php");
	include_once("./includes/order_links.php");
	include_once("./includes/order_items_properties.php");
	include_once("./includes/order_payment.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/profile_functions.php");
	include_once("./includes/parameters.php");
	include_once("./includes/navigator.php");

	// set necessary scripts
	if(!isset($is_block_reload)) { $is_block_reload = false; }
	if (!$is_block_reload) {
		set_script_tag("js/shopping.js");
		set_script_tag("js/ajax.js");
		set_script_tag("js/blocks.js");
		set_script_tag("js/images.js");
	}

	$default_title = "";

 	// redirect to secure page 
	$ajax = get_param("ajax");
	$operation = get_param("operation");
	$is_mobile = get_setting_value($settings, "is_mobile", 0);
	$secure_redirect = get_setting_value($settings, "secure_redirect", 0);
	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", "");
	$secure_order_profile = get_setting_value($settings, "secure_order_profile", 0);
	$secure_payments = get_setting_value($settings, "secure_payments", 0);
	if ($secure_order_profile) {
		$order_info_url = $secure_url . get_custom_friendly_url("order_info.php");
	} else {
		$order_info_url = $site_url . get_custom_friendly_url("order_info.php");
	}
	if (!$is_ssl && ($secure_order_profile || $secure_payments) && $secure_redirect && preg_match("/^https/i", $secure_url)) {
		if ($ajax) {
			echo json_encode(array("location" => $order_info_url));
			exit;
		} else {
			header("Location: " . $order_info_url);
			exit;
		}
	}

	// use always by default site_id = 1 
	if (!isset($site_id)) { $site_id = 1; }
	if (!$site_id) { $site_id = 1; }

	$steps = array(
		"cart" => array("order" => 1, "show" => true, "errors" => "", "next" => "", ),
		"user" => array("order" => 2, "show" => true,  "errors" => "", "next" => "",),
		"shipping" => array("order" => 3, "show" => true, "errors" => "", "next" => "",),
		"payment" => array("order" => 4, "show" => true,  "errors" => "", "next" => "",),
	);

	// check admin call center permissions
	$cc_order = false;
	$param_prefix = "";
	$admin_permissions = get_admin_permissions();
	$call_center = get_setting_value($admin_permissions, "create_orders", 0);
	if ($call_center && ($operation == "fast_order" || $operation == "cc_order")) { $cc_order = true; }
	if ($call_center) { $param_prefix = "call_center_"; } 
	$cc_user_id = get_param("cc_user_id");
	$session_user_id = get_session("session_user_id");
	if ($call_center && $cc_user_id && $cc_user_id != $session_user_id) {
		user_login("", "", $cc_user_id, false, "", false, $errors);
	}

	// set ordering script
	$t->set_template_path("./js");
	$t->set_file("ordering_js","ordering.js");
	$required_delivery_js = va_constant("REQUIRED_DELIVERY_MSG");
	$required_delivery_js = str_replace(array("\n", "\r"), array("\\n", "\\r"), $required_delivery_js);
  $t->set_var("REQUIRED_DELIVERY_JS", $required_delivery_js);
	$t->parse("ordering_js", false);
	$t->set_template_path($settings["templates_dir"]);

	$html_template = get_setting_value($block, "html_template", "block_order_info.html"); 
  $t->set_file("block_body", $html_template);
	if ($call_center) {
		$t->parse("call_center_mode", false);
	}
	// parse empty payment form 
	$t->set_var("form_name", "payment");
	$t->set_var("form_method", "post");
	$t->set_var("form_url", "");
	$t->set_var("form_class", "");
	$t->set_var("form_params", "");
	$t->set_var("form_html", "");
	$t->parse("form_html_block", false);
	$t->parse("payment_form", false);


	$sc_errors = ""; $delivery_errors = ""; $profile_errors = ""; $shipping_errors = ""; $payment_errors = "";
	$session_user_id = get_session("session_user_id");
	$user_id = $session_user_id;
	if (!$user_id) { $user_id = get_session("session_new_user_id"); }
	$user_type_id = get_session("session_user_type_id");
	if (!$user_type_id) { $user_type_id = get_session("session_new_user_type_id"); }
	// check if order was just placed or it was places before
	$order_id = get_session("session_order_id");
	$user_order_id = get_session("session_user_order_id"); 
	if ($user_order_id) { $order_id = $user_order_id; }
	$operation = get_param("operation");

	$ajax = get_param("ajax");
	$active_step = get_param("active_step");
	if (!$active_step) { 
		if ($user_order_id) {
			$active_step = "payment"; 
		} else {
			$active_step = "cart"; 
		}
	} 
	$next_step = get_param("next_step");
	$remove_coupon_id = get_param("remove_coupon_id");
	$form_coupon_code = get_param("form_coupon_code");
	// for call center check selected user
	$cc_user_login = ""; 
	$cc_user_id = get_param("cc_user_id");
	if ($call_center && $cc_user_id) {
		$sql  = " SELECT login, user_type_id FROM " . $table_prefix . "users ";
		$sql .= " WHERE user_id=" . $db->tosql($cc_user_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$cc_user_login = $db->f("login");
			$user_id = $cc_user_id;
			$user_type_id = $db->f("user_type_id");
		} else {
			$cc_user_id = "";
		}
	}

	// check if any actions is allowed from current IP
	if (blacklist_check("orders") == "blocked") {
		$sc_errors = BLACK_IP_MSG;
	}

	// check email campaign parameters
	$newsletter_id = "";
	$newsletter_email_id = get_session("session_eid");
	if ($newsletter_email_id) {
		$sql = " SELECT newsletter_id FROM " . $table_prefix . "newsletters_emails WHERE email_id=" . $db->tosql($newsletter_email_id, INTEGER);
		$newsletter_id = get_db_value($sql);
	}

	// check user settings 
	$user_settings = array();
	if ($user_id) {
		$sql = "SELECT setting_name,setting_value FROM " . $table_prefix . "user_types_settings WHERE type_id=" . $db->tosql(get_session("session_user_type_id"), INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$user_settings[$db->f("setting_name")] = $db->f("setting_value");
		}
	}

	$eol = get_eol();
	$sess_currency = get_currency();
	$currency = get_currency($sess_currency["code"]);
	$currency_code = $currency["code"];
	$default_currency_code = get_db_value("SELECT currency_code FROM ".$table_prefix."currencies WHERE is_default=1");

	// get current date value to check payment activity parameters
	$current_date = va_time();
	$current_ts = va_timestamp();
	$check_time = $current_date[HOUR] * 60 + $current_date[MINUTE];
	$week_values = array("1" => 1, "2" => 2, "3" => 4, "4" => 8, "5" => 16, "6" => 32, "0" => 64);
	$day_value = $week_values[date("w", $current_ts)];

	$is_fast_checkout = 0;
	if ($operation == "fast_checkout") {
		$is_fast_checkout = 1;
		$fast_payment_id = get_param("fast_payment_id");
		$sql  = " SELECT ps.payment_id FROM (((";
		if (isset($site_id)) {
			$sql .= "(";
		}
		if (strlen($user_type_id)) {
			$sql .= "(";
		}
		$sql .= $table_prefix . "payment_systems ps";
		$sql .= " LEFT JOIN " . $table_prefix . "payment_currencies pcr ON pcr.payment_id=ps.payment_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "currencies cr ON pcr.currency_id=cr.currency_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "payment_countries pc ON pc.payment_id=ps.payment_id)";			
		if (isset($site_id)) {
			$sql .= " LEFT JOIN " . $table_prefix . "payment_systems_sites s ON s.payment_id=ps.payment_id)";			
		}
		if (strlen($user_type_id)) {
			$sql .= " LEFT JOIN " . $table_prefix . "payment_user_types ut ON ut.payment_id=ps.payment_id)";			
		}
		$sql .= " WHERE ps.payment_id=" . $db->tosql($fast_payment_id, INTEGER);
		$sql .= " AND ps.is_active=1 AND ps.fast_checkout_active=1 ";
		$sql .= " AND (ps.currencies_all=1 OR cr.currency_code=" . $db->tosql($currency_code, TEXT) . ")";
		//$sql .= " AND (ps.countries_all=1 OR pc.country_id=" . $db->tosql($country_id, INTEGER) . ")";
		if (isset($site_id)) {
			$sql .= " AND (ps.sites_all=1 OR s.site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";			
		} else {
			$sql .= " AND ps.sites_all=1";
		}
		if (strlen($user_type_id)) {
			$sql .= " AND (ps.user_types_all = 1 OR ut.user_type_id=" . $db->tosql($user_type_id, INTEGER, true, false) . ")";
		} else {
			$sql .= " AND ps.non_logged_users=1";
		}
		$sql .= " AND (ps.active_week_days&".intval($day_value)."<>0)";
		$sql .= " AND (ps.active_start_time IS NULL OR ps.active_start_time<=".$db->tosql($check_time, INTEGER).")";
		$sql .= " AND (ps.active_end_time IS NULL OR ps.active_end_time>=".$db->tosql($check_time, INTEGER).")";

		$fast_payment_id = get_db_value($sql);
		if (!$fast_payment_id) {
			$sc_errors .= "Can't find Fast Checkout payment module.";
			$operation  = "";
		}
	} else if ($operation == "refresh") {
		if (strlen($remove_coupon_id)) {
			$form_coupon_code = "";
			remove_coupon($remove_coupon_id);
		}
		if (strlen($form_coupon_code)) {
			check_add_coupons(false, $form_coupon_code, $sc_errors);
			if ($sc_errors) {
				$t->set_var("form_coupon_code", htmlspecialchars($form_coupon_code));
			}
		}
	} 

	$user_registration = get_setting_value($settings, "user_registration", 0);
	if ($user_registration == 1 && !strlen($user_id) && $operation != "fast_checkout" && !$call_center && !$user_order_id) {
		// user need to be logged in before proceed
		$location_url = get_custom_friendly_url("checkout.php");
		if ($ajax) {
			echo json_encode(array("location" => $location_url));
			exit;
		} else {
			header("Location: " . $location_url);
			exit;
		}
	}

	$order_data = array(); // data for javascript
	$user_order_placed_date = "";
	$shopping_cart = get_session("shopping_cart");
	if ($user_order_id) {
		// don't use shopping cart when user pay for already saved order
		$shopping_cart = array();

		$t->set_var("user_order", "1");
		// check if user can pay for the order
		$sql  = " SELECT o.is_placed, o.order_status, o.order_placed_date, os.paid_status ";
		$sql .= " FROM (" . $table_prefix . "orders o ";
		$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
		$sql .= " WHERE o.order_id=" . $db->tosql($user_order_id, INTEGER);
		$sql .= " AND o.site_id=" . $db->tosql($site_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$is_placed = $db->f("is_placed");
			$paid_status = $db->f("paid_status");
			$transaction_id = $db->f("transaction_id");
			$user_order_placed_date = $db->f("order_placed_date", DATETIME);
			if ($is_placed || $paid_status) {
				$sc_errors .= ORDER_PLACED_ERROR. "<br/>";
			}
		} else {
			$sc_errors .= ORDER_EXISTS_ERROR . "<br/>";
		}
		// show order cart for retrieved order
		$order_data = show_order_items($user_order_id, true, "order_info");
	} else if (!is_array($shopping_cart) || sizeof($shopping_cart) < 1) {
		$location_url = get_custom_friendly_url("basket.php");
		if ($ajax) {
			echo json_encode(array("errors" => va_constant("NO_PRODUCTS"), "location" => $location_url));
			exit;
		} else {
			header("Location: " . $location_url);
			exit;
		}
	} else {
		// check if all necessary options where selected and there are no any errors
		foreach ($shopping_cart as $cart_id => $cart_info) {
			$properties_required = $cart_info["PROPERTIES_REQUIRED"];
			$item_error = isset($cart_info["ERROR"]) ? $cart_info["ERROR"] : "";
			$location_url = ""; 
			if ($properties_required) {
				$cart_errors = va_constant("OPTION_REQUIRED_MSG");
				$location_url = get_custom_friendly_url("basket.php")."?operation=required_options";
			} else if (strlen($item_error)) {
				$cart_errors = $item_error;
				$location_url = get_custom_friendly_url("basket.php")."?operation=item_error";
			}
			if ($location_url) {
				if ($ajax) {
					echo json_encode(array("location" => $location_url, "errors" => $cart_errors));
					exit;
				} else {
					header("Location: " . $location_url);
					exit;
				}
			}
		}
	}

	// get order profile settings
	$sql  = "SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= "WHERE setting_type='order_info'";
	if (isset($site_id)) {
		$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
		$sql .= " ORDER BY site_id ASC ";
	} else {
		$sql .= " AND site_id=1 ";
	}
	$db->query($sql);
	while ($db->next_record()) {
		$order_info[$db->f("setting_name")] = $db->f("setting_value");
	}
	// check if order was paid already
	$sql  = " SELECT paid_total FROM " . $table_prefix . "orders ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$paid_total = doubleval(get_db_value($sql));
	
	$opc_type = get_setting_value($order_info, "opc_type", "steps");
	$extra_css_class = ($opc_type == "single") ? "opc-single" : "opc-steps";
	$shipping_block = get_setting_value($order_info, "shipping_block", 0);
	$checkout_flow = strtolower(get_setting_value($order_info, "checkout_flow"));
	$payment_allowed = get_setting_value($order_data, "payment_allowed", 0);
	$part_payments = get_setting_value($order_info, "part_payments", 0);
	$allow_partial_payment = get_setting_value($order_info, "allow_partial_payment", 0);
	$partial_payment_options = get_setting_value($order_info, "partial_payment_options");

	$user_auto_add = false;

	if ($payment_allowed) {
		// payment allowed use default checkout flow
		$checkout_flow = "";
	} else if ($checkout_flow == "quote" || $checkout_flow == "quote_request") {
		$steps["shipping"]["show"] = false;
		$steps["payment"]["show"] = false;
	}

	$user_info = get_session("session_user_info");
	$user_tax_free = get_setting_value($user_info, "tax_free", 0);
	$points_balance = get_setting_value($user_info, "total_points", 0);
	$order_min_goods_cost = get_setting_value($user_info, "order_min_goods_cost", "");
	$order_max_goods_cost = get_setting_value($user_info, "order_max_goods_cost", "");
	$order_min_weight = ""; $order_max_weight = "";

	// check if credit system active 
	$credit_system = get_setting_value($settings, "credit_system", 0);
	$credits_balance_order_profile = get_setting_value($settings, "credits_balance_order_profile", 0);
	$credit_balance = 0; $credit_amount = 0;
	if ($credit_system && $user_id) {
		// check user credit balance
		$sql = " SELECT credit_balance FROM " . $table_prefix . "users WHERE user_id=" . $db->tosql($user_id, INTEGER);
		$credit_balance = doubleval(get_db_value($sql));
		// check if user decide to pay with credits
		$credit_amount = abs(get_param("credit_amount"));
		if ($credit_amount > $credit_balance) {
			$credit_amount = $credit_balance;
		}
	}
	$user_discount_type = get_session("session_discount_type");
	$user_discount_amount = get_session("session_discount_amount");
	$user_ip = get_ip();
	$referer = get_session("session_referer");
	$initial_ip = get_session("session_initial_ip");
	$cookie_ip = get_session("session_cookie_ip");
	$visit_id = get_session("session_visit_id");
	$visit_number = get_session("session_visit_number");
	if (!$visit_id) { $visit_id = 0; }
	$keywords = get_session("session_kw");
	$affiliate_code = get_session("session_af");
	$affiliate_user_id = 0;
	if (strlen($affiliate_code)) {
		$sql  = " SELECT u.user_id FROM (";
		if (isset($site_id)) { $sql .= "("; }
		$sql .= $table_prefix . "users u";
		$sql .= " LEFT JOIN " . $table_prefix . "user_types ut ON ut.type_id=u.user_type_id)";
		if (isset($site_id)) {
			$sql .= " LEFT JOIN " . $table_prefix . "user_types_sites s ON s.type_id=ut.type_id)";
		}
		$sql .= " WHERE u.affiliate_code=" . $db->tosql($affiliate_code, TEXT);
		if (isset($site_id)) {
			$sql .= " AND (ut.sites_all=1 OR s.site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";			
		} else {
			$sql .= " AND ut.sites_all=1";
		}
		$affiliate_user_id = get_db_value($sql);
	}
	$friend_code = get_session("session_friend");
	$friend_user_id = get_friend_info();

	$secure_payments = get_setting_value($settings, "secure_payments", 0);
	$show_item_code = get_setting_value($settings, "item_code_checkout", 0);
	$show_manufacturer_code = get_setting_value($settings, "manufacturer_code_checkout", 0);
	$subscribe_block = get_setting_value($order_info, "subscribe_block", 0);
	if ($user_order_id) { $subscribe_block = 0; }
	$subcomponents_show_type = get_setting_value($order_info, "subcomponents_show_type", 0);
	$phone_code_select = get_setting_value($settings, "phone_code_select", 0);
	
	$ordinal_number_column = get_setting_value($settings, "checkout_ordinal_number", 0);
	$item_name_column = get_setting_value($settings, "checkout_item_name", 1);
	$item_price_column = get_setting_value($settings, "checkout_item_price", 1);
	$item_tax_percent_column = get_setting_value($settings, "checkout_item_tax_percent", 0);
	$item_tax_column = get_setting_value($settings, "checkout_item_tax", 0);
	$item_price_incl_tax_column = get_setting_value($settings, "checkout_item_price_incl_tax", 0);
	$item_quantity_column = get_setting_value($settings, "checkout_item_quantity", 1);
	$item_price_total_column = get_setting_value($settings, "checkout_item_price_total", 1);
	$item_tax_total_column = get_setting_value($settings, "checkout_item_tax_total", 1);
	$item_price_incl_tax_total_column = get_setting_value($settings, "checkout_item_price_incl_tax_total", 1);
	$item_image_column = get_setting_value($settings, "checkout_item_image", 0);
	$item_remove_column = get_setting_value($settings, "checkout_item_remove", 1);
	$quantity_control_checkout = get_setting_value($settings, "quantity_control_checkout");
	$cart_subitem_name = get_setting_value($settings, "cart_subitem_name");
	
	// image settings
	$product_no_image = get_setting_value($settings, "product_no_image", "");
	$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");
	product_image_fields($item_image_column, $image_type_name, $image_field, $image_alt_field, $watermark, $product_no_image);
	
	$tax_prices_type = get_setting_value($settings, "tax_prices_type", 0);
	$tax_prices = get_setting_value($settings, "tax_prices", 0);
	$tax_note = get_translation(get_setting_value($settings, "tax_note", ""));
	$tax_note_excl = get_translation(get_setting_value($settings, "tax_note_excl", ""));

	// merchant and affiliate settings
	$affiliate_commission_deduct = get_setting_value($settings, "affiliate_commission_deduct", 0);

	// points settings
	$points_system = get_setting_value($settings, "points_system", 0);
	$points_conversion_rate = get_setting_value($settings, "points_conversion_rate", 1);
	$points_decimals = get_setting_value($settings, "points_decimals", 0);
	$reward_points_checkout = get_setting_value($settings, "reward_points_checkout", 0);
	$points_prices = get_setting_value($settings, "points_prices", 0);
	$points_orders_options = get_setting_value($settings, "points_orders_options", 0);
	$points_shipping = get_setting_value($settings, "points_shipping", 0);
	$points_for_points = get_setting_value($settings, "points_for_points", 0);
	$credits_for_points = get_setting_value($settings, "credits_for_points", 0);

	// credit settings
	$reward_credits_users = get_setting_value($settings, "reward_credits_users", 0);
	$reward_credits_checkout = get_setting_value($settings, "reward_credits_checkout", 0);

	// option price options
	$option_positive_price_right = get_setting_value($settings, "option_positive_price_right", ""); 
	$option_positive_price_left = get_setting_value($settings, "option_positive_price_left", ""); 
	$option_negative_price_right = get_setting_value($settings, "option_negative_price_right", ""); 
	$option_negative_price_left = get_setting_value($settings, "option_negative_price_left", "");

	$price_type = get_session("session_price_type");
	if ($price_type == 1) {
		$price_field = "trade_price";
		$sales_field = "trade_sales";
		$additional_price_field = "trade_additional_price";
		$properties_field = "trade_properties_price";
	} else {
		$price_field = "price";
		$sales_field = "sales_price";
		$additional_price_field = "additional_price";
		$properties_field = "properties_price";
	}

	$is_update = ($operation == "save");
	$same_as_personal = get_param("same_as_personal");

	// calculate number of predefined billing and shipping fields
	$bill_fields = 0; $ship_fields = 0;
	foreach ($parameters as $param_key => $param_name) {
		$bill_field = $param_prefix."show_".$param_name;
		$ship_field = $param_prefix."show_delivery_".$param_name;
		if (get_setting_value($order_info, $bill_field)) { $bill_fields++; }
		if (get_setting_value($order_info, $ship_field)) { $ship_fields++; }
	}
	// prepare shipping and billing data along with other parameters - state_id, postal_code, city and country_id for use
	$bill_data = array("country_code"=>"","country_name"=>"","state_code"=>"","state_name"=>""); 
	$ship_data = array("country_code"=>"","country_name"=>"","state_code"=>"","state_name"=>""); 
	$state_id = ""; $postal_code = ""; $country_id = ""; $city = ""; $bill_country_id = "";
	if ($operation == "save" || $operation == "next" || $operation == "refresh") {
		foreach ($parameters as $param_key => $param_name) {
			$bill_field = $param_prefix."show_".$param_name;
			$ship_field = $param_prefix."show_delivery_".$param_name;
			$bill_value = get_param($param_name);
			$ship_value = get_param("delivery_".$param_name);
			$show_bill_field = get_setting_value($order_info, $bill_field);
			// save bill data
			$bill_data[$param_name] = ($bill_fields) ? $bill_value : $ship_value;
			// save ship data
			if ($ship_fields) {
				$ship_data[$param_name] = ($same_as_personal && $show_bill_field) ? $bill_value : $ship_value;
			} else {
				$ship_data[$param_name] = $bill_value;
			}
		}
		// if country fields disabled use default settings
		$personal_country_show = get_setting_value($order_info, $param_prefix."show_country_id");
		$delivery_country_show = get_setting_value($order_info, $param_prefix."show_delivery_country_id");
		$personal_state_show = get_setting_value($order_info, $param_prefix."show_state_id");
		$delivery_state_show = get_setting_value($order_info, $param_prefix."show_delivery_state_id");
		if ($personal_country_show != 1 && $delivery_country_show != 1) {
			$bill_data["country_id"] = get_setting_value($settings, "country_id");
			$ship_data["country_id"] = get_setting_value($settings, "country_id");
		}

		$state_id = $ship_data["state_id"];
		$postal_code = $ship_data["zip"];
		$city = $ship_data["city"];
		$country_id = $ship_data["country_id"];
		$bill_country_id = $bill_data["country_id"];

	} elseif ($operation == "fast_checkout") {
		$fast_checkout_country_show = get_setting_value($settings, "fast_checkout_country_show", 0);
		$fast_checkout_country_required = get_setting_value($settings, "fast_checkout_country_required", 0);
		$fast_checkout_state_show = get_setting_value($settings, "fast_checkout_state_show", 0);
		$fast_checkout_state_required = get_setting_value($settings, "fast_checkout_state_required", 0);
		$fast_checkout_postcode_show = get_setting_value($settings, "fast_checkout_postcode_show", 0);
		$fast_checkout_postcode_required = get_setting_value($settings, "fast_checkout_postcode_required", 0);
		$country_id = get_param("fast_checkout_country_id");
		$bill_country_id = get_param("fast_checkout_country_id");
		$state_id = get_param("fast_checkout_state_id");
		$postal_code = get_param("fast_checkout_postcode");

		if ($fast_checkout_country_show && $fast_checkout_country_required && !strlen($country_id)) {
			$sc_errors .= str_replace("{field_name}", va_constant("COUNTRY_FIELD"), va_constant("REQUIRED_MESSAGE")) . "<br>\n";
		}
		if ($fast_checkout_state_show && $fast_checkout_state_required && !strlen($state_id)) {
			// check number of states for selected country
			$sql = "SELECT COUNT(*) FROM " . $table_prefix . "states WHERE show_for_user=1 ";
			if ($country_id) { $sql .= " AND country_id=" . $db->tosql($country_id, INTEGER); }
			$states_number = get_db_value($sql);
			if ($states_number) {
				$sc_errors .= str_replace("{field_name}", va_constant("STATE_FIELD"), va_constant("REQUIRED_MESSAGE")) . "<br>\n";
			}
		}
		if ($fast_checkout_postcode_show && $fast_checkout_postcode_required && !strlen($postal_code)) {
			$sc_errors .= str_replace("{field_name}", va_constant("ZIP_FIELD"), va_constant("REQUIRED_MESSAGE")) . "<br>\n";
		}
		// populate billing and shipping arrays
		foreach ($parameters as $param_key => $param_name) {
			$bill_data[$param_name] = get_param("fast_checkout_".$param_name);
			$ship_data[$param_name] = get_param("fast_checkout_".$param_name);
		}
		$bill_data["zip"] = $postal_code; 
		$ship_data["zip"] = $postal_code;
	} elseif ($operation == "load") {
		$session_order_id = get_session("session_order_id"); 
		$sql  = " SELECT * FROM " . $table_prefix ."orders ";
		$sql .= " WHERE order_id=". $db->tosql($session_order_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$record_data = $db->Record;
			$order_data = array_merge($order_data, $record_data);
		}

		$shipments_data = array(); $shipment_key = 0;
		$sql  = " SELECT * FROM " . $table_prefix ."orders_shipments ";
		$sql .= " WHERE order_id=". $db->tosql($session_order_id, INTEGER);
		$sql .= " ORDER BY order_shipping_id ";
		$db->query($sql);
		if ($db->next_record()) {
			$shipment_key++;
			$shipments_data["shipping_id_".$shipment_key] = $db->f("shipping_id");
			$shipments_data["points_cost_".$shipment_key] = $db->f("points_cost");
		}

		$checkout_details = get_checkout_details($order_info, $operation);
		foreach ($parameters as $param_key => $param_name) {
			$bill_data[$param_name] = get_setting_value($checkout_details, "bill_".$param_name); 
			$ship_data[$param_name] = get_setting_value($checkout_details, "ship_".$param_name);
		}

		$state_id = $checkout_details["ship_state_id"];
		$postal_code = $checkout_details["ship_zip"];
		$city = $checkout_details["ship_city"];
		$country_id = $checkout_details["ship_country_id"];
		$bill_country_id = $checkout_details["bill_country_id"];
	} else {
		// check settings from shipping calculator first
		$shipping_info = get_session("session_shipping_info");
		if (is_array($shipping_info) && sizeof($shipping_info)) {
			foreach ($parameters as $param_key => $param_name) {
				$param_value = get_setting_value($shipping_info, $param_name);
				$ship_data[$param_name] = $param_value;
			}
		}
		// get delivery details from global settings
		$checkout_details = get_checkout_details($order_info);
		foreach ($parameters as $param_key => $param_name) {
			$bill_value = get_setting_value($checkout_details, "bill_".$param_name);
			$ship_value = get_setting_value($checkout_details, "ship_".$param_name);
			$bill_data[$param_name] = $bill_value;
			if (!get_setting_value($ship_data, $param_name)) {
				$ship_data[$param_name] = $ship_value;
			}
		}

		$state_id = $ship_data["state_id"];
		$postal_code = $ship_data["zip"];
		$city = $ship_data["city"];
		$country_id = $ship_data["country_id"];
		$bill_country_id = $bill_data["country_id"];
	}
	if (!$bill_country_id) { $bill_country_id = $country_id; }

	// prepare arrays for countries and states which we need to show and check additional data
	$va_countries = va_countries(); 
	$va_delivery_countries = va_delivery_countries(); 
	$va_states = va_states();
	// set country and state codes by their ids
	if ($bill_data["country_id"] && isset($va_countries[$bill_data["country_id"]])) {
		$bill_data["country_code"] = $va_countries[$bill_data["country_id"]]["country_code"];
		$bill_data["country_name"] = $va_countries[$bill_data["country_id"]]["country_name"];
	}
	if ($bill_data["state_id"] && isset($va_states[$bill_data["state_id"]])) {
		$bill_data["state_code"] = $va_states[$bill_data["state_id"]]["state_code"];
		$bill_data["state_name"] = $va_states[$bill_data["state_id"]]["state_name"];
	}
	if ($ship_data["country_id"] && isset($va_delivery_countries[$ship_data["country_id"]])) {
		$ship_data["country_code"] = $va_delivery_countries[$ship_data["country_id"]]["country_code"];
		$ship_data["country_name"] = $va_delivery_countries[$ship_data["country_id"]]["country_name"];
	}
	if ($ship_data["state_id"] && isset($va_states[$ship_data["state_id"]])) {
		$ship_data["state_code"] = $va_states[$ship_data["state_id"]]["state_code"];
		$ship_data["state_name"] = $va_states[$ship_data["state_id"]]["state_name"];
	}
	$country_code = $ship_data["country_code"];
	$state_code = $ship_data["state_code"];

	// format postal code for correct use
	$postal_code = preg_replace("/\s{2,}/", " ", trim($postal_code));
	if ($country_code == "GB" && $postal_code && preg_match(UK_POSTCODE_REGEXP, $postal_code)) {
		if (!preg_match("/\s\d[a-z]{2}$/i", $postal_code)) {
			$postal_code = substr($postal_code , 0, strlen($postal_code) - 3)." ".substr($postal_code,-3);
		}
	}

	
	$variables = array();
	$variables["charset"] = CHARSET;
	$variables["site_url"] = $settings["site_url"];
	$variables["secure_url"] = $secure_url;
	$variables["http_host"] = get_var("HTTP_HOST");
	$variables["session_id"] = session_id();
	$variables["user_ip"] = $user_ip;
	$variables["order_ip"] = $user_ip;
	$variables["initial_ip"] = $initial_ip;
	$variables["cookie_ip"] = $cookie_ip;

	$t->set_var("order_info_href", get_custom_friendly_url("order_info.php"));
	$t->set_var("user_address_select_href", get_custom_friendly_url("user_address_select.php"));
	$t->set_var("call_center_users_href", "call_center_users.php");
	$t->set_var("cc_security_code_help_href", "cc_security_code_help.php");
	$t->set_var("current_href",  get_custom_friendly_url("order_info.php"));
	$t->set_var("order_info_url",  $order_info_url);
	$t->set_var("currency_left", $currency["left"]);
	$t->set_var("currency_right", $currency["right"]);
	$t->set_var("currency_rate", htmlspecialchars($currency["rate"]));
	$t->set_var("currency_decimals", htmlspecialchars($currency["decimals"]));
	$t->set_var("currency_point", htmlspecialchars($currency["point"]));
	$t->set_var("currency_separator", htmlspecialchars($currency["separator"]));

	$t->set_var("opc_type", htmlspecialchars($opc_type));
	$t->set_var("tax_prices_type", $tax_prices_type);
	$t->set_var("is_mobile", $is_mobile);
	$t->set_var("referer", $referer);
	$t->set_var("referrer", $referer);
	$t->set_var("HTTP_REFERER", $referer);
	$t->set_var("initial_ip", $initial_ip);
	$t->set_var("cookie_ip", $cookie_ip);
	$t->set_var("user_ip", $user_ip);
	$t->set_var("remote_address", $user_ip);
	$t->set_var("visit_number", $visit_number);
	$t->set_var("points_msg", strtolower(POINTS_MSG));
	$t->set_var("points_balance_value", $points_balance);
	$t->set_var("points_rate", $points_conversion_rate);
	$t->set_var("points_decimals", $points_decimals);
	$t->set_var("credit_balance_value", $credit_balance);
	$t->set_var("default_country_id", htmlspecialchars($settings["country_id"]));
	$t->set_var("default_state_id", htmlspecialchars($settings["state_id"]));

	// prepare custom options
	$properties_fields = array(); $personal_fields = array();
	$options_errors = "";
	$properties_total = 0; $properties_taxable = 0; $properties_points_amount = 0; 
	$properties_incl_tax = 0;
	$order_properties = ""; $op_rows = array(); $pn = 0;
	$custom_options = array();

	$sql  = " SELECT ocp.* ";
	$sql .= " FROM (" . $table_prefix . "order_custom_properties ocp ";
	$sql .= " LEFT JOIN " . $table_prefix . "order_custom_sites ocs ON ocp.property_id=ocs.property_id) ";
	if ($user_order_id) {
		// get only payment properties for user saved order
		$sql .= " WHERE ocp.property_type IN (4) AND property_show IN (0,1) "; 
	} else if ($call_center) {
		// show properties for call center 
		$sql .= " WHERE ocp.property_type IN (1,2,3,4,5,6) AND property_show IN (0,2) "; 
	} else {
		// show properties for customer 
		$sql .= " WHERE ocp.property_type IN (1,2,3,4,5,6) AND property_show IN (0,1) "; 
	}
	$sql .= " AND (ocp.sites_all=1 OR ocs.site_id=" . $db->tosql($site_id, INTEGER, true, false) . ") ";
	$sql .= " ORDER BY ocp.property_order, ocp.property_id ";
	$db->query($sql);
	if ($db->next_record()) {
		do {
			$op_rows[$pn]["property_id"] = $db->f("property_id");
			$op_rows[$pn]["payment_id"] = $db->f("payment_id");
			$op_rows[$pn]["shipping_type_id"] = $db->f("shipping_type_id");
			$op_rows[$pn]["shipping_module_id"] = $db->f("shipping_module_id");
			$op_rows[$pn]["property_order"] = $db->f("property_order");
			$op_rows[$pn]["property_code"] = $db->f("property_code");
			$op_rows[$pn]["property_name"] = $db->f("property_name");
			$op_rows[$pn]["property_description"] = get_translation($db->f("property_description"));
			$op_rows[$pn]["property_notes"] = "";
			$op_rows[$pn]["default_value"] = get_translation($db->f("default_value"));
			$op_rows[$pn]["property_type"] = $db->f("property_type");
			$op_rows[$pn]["property_class"] = $db->f("property_class");
			$op_rows[$pn]["property_style"] = $db->f("property_style");
			$op_rows[$pn]["control_type"] = $db->f("control_type");
			$op_rows[$pn]["control_style"] = $db->f("control_style");
			$op_rows[$pn]["required"] = $db->f("required");
			$op_rows[$pn]["tax_free"] = $db->f("tax_free");
			$op_rows[$pn]["before_name_html"] = get_translation($db->f("before_name_html"));
			$op_rows[$pn]["after_name_html"] = get_translation($db->f("after_name_html"));
			$op_rows[$pn]["before_control_html"] = get_translation($db->f("before_control_html"));
			$op_rows[$pn]["after_control_html"] = get_translation($db->f("after_control_html"));
			$op_rows[$pn]["onchange_code"] = get_translation($db->f("onchange_code"));
			$op_rows[$pn]["onclick_code"] = get_translation($db->f("onclick_code"));
			$op_rows[$pn]["control_code"] = get_translation($db->f("control_code"));
			$op_rows[$pn]["validation_regexp"] = $db->f("validation_regexp");
			$op_rows[$pn]["regexp_error"] = ($db->f("regexp_error")) ? get_translation($db->f("regexp_error")) : INCORRECT_VALUE_MESSAGE;
			$pn++;
		} while ($db->next_record());
	}

	// VAT validation
	$tax_free = 0; $vat_parameter = ""; $vat_number = ""; $is_vat_valid = false; 
	// add $vat_validation = true; into includes/var_definition.php to activate this validation
	if (isset($vat_validation) && $vat_validation) {
		// check vat_parameter
		if (sizeof($op_rows) > 0) {
			for ($pn = 0; $pn < sizeof($op_rows); $pn++) {
				$property_id = $op_rows[$pn]["property_id"];
				$property_code = $op_rows[$pn]["property_code"];
				$property_name = $op_rows[$pn]["property_name"];

				if ($property_code == "vat_number" || preg_match("/vat/i", $property_name)) {
					$vat_parameter = "op_" . $property_id;
					break;
				}
			}
		}
		if ($vat_parameter) {
			include("./includes/vat_check.php");
			$vat_number = get_param($vat_parameter);
			
			if ($vat_number) {
				$is_vat_valid = vat_check($vat_number, $country_code, $vat_response);
				// save full VAT response as JSON object in property notes field
				$op_rows[$pn]["property_notes"] = json_encode($vat_response);
				if ($is_vat_valid) {
					if (!isset($vat_obligatory_countries) || !is_array($vat_obligatory_countries)
					|| !in_array(strtoupper($country_code), $vat_obligatory_countries)) {
						$tax_free = 1; // use numeric values 0 and 1 only as true/false will generate validation error
					}
				} else {
					$sc_errors .= "Your VAT Number is invalid. Please check it and try again.<br>";
				}
			}
		}
	}
	/////////////////////TODO

	// get taxes rates
	$tax_available = false; $tax_percent_sum = 0; $tax_names = ""; $tax_column_names = ""; $taxes_total = 0; 
	$default_tax_rates = get_tax_rates(true); 
	if ($user_order_id) {
		$tax_rates = $order_data["tax_rates"];
	} else {
		$tax_rates = get_tax_rates(true, $country_id, $state_id, $postal_code);
	}
	if (sizeof($tax_rates) > 0) {
		$tax_available = true;
		foreach ($tax_rates as $tax_id => $tax_info) {
			$show_type = $tax_info["show_type"];
			$tax_type = $tax_info["tax_type"];
			if ($tax_type == 1) {
				// sum only general tax 
				$tax_percent_sum += $tax_info["tax_percent"];
			}
			if ($show_type&1) {
				if ($tax_column_names) { $tax_column_names .= " & "; }
				$tax_column_names .= get_translation($tax_info["tax_name"]);
			}
			if ($tax_names) { $tax_names .= " & "; }
			$tax_names .= get_translation($tax_info["tax_name"]);
		}
	}

	$t->set_var("tax_name", $tax_column_names);
	$t->set_var("tax_note", $tax_note);
	$t->set_var("tax_note_incl", $tax_note);
	$t->set_var("tax_note_excl", $tax_note_excl);
	// set titles for cart columns
	set_cart_titles();

	$properties_colspan = 0; $total_columns = 0;
	if ($ordinal_number_column) {
		$properties_colspan++;
		$total_columns++;
		$t->parse("ordinal_number_header", false);
	}
	if ($item_image_column) {
		$properties_colspan++;
		$total_columns++;
		$t->parse("item_image_header", false);
	}
	if ($item_name_column) {
		$properties_colspan++;
		$total_columns++;
		$t->parse("item_name_header", false);
	}
	if ($item_price_column || ($item_price_incl_tax_column && !$tax_available)) {
		$item_price_column = true;
		$properties_colspan++;
		$total_columns++;
		$t->parse("item_price_header", false);
	}
	if ($item_tax_percent_column && $tax_available) {
		$properties_colspan++;
		$total_columns++;
		$t->parse("item_tax_percent_header", false);
	} else {
		$item_tax_percent_column = false;
	}
	if ($item_tax_column && $tax_available) {
		$properties_colspan++;
		$total_columns++;
		$t->parse("item_tax_header", false);
	} else {
		$item_tax_column = false;
	}
	if ($item_price_incl_tax_column && $tax_available) {
		$properties_colspan++;
		$total_columns++;
		$t->parse("item_price_incl_tax_header", false);
	} else {
		$item_price_incl_tax_column = false;
	}
	$goods_colspan = $properties_colspan;
	if ($item_quantity_column) {
		$properties_colspan++;
		$total_columns++;
		$t->parse("item_quantity_header", false);
	}
	if ($item_price_total_column || ($item_price_incl_tax_total_column && !$tax_available)) {
		$item_price_total_column = true;
		$total_columns++;
		$t->parse("item_price_total_header", false);
	}
	if ($item_tax_total_column && $tax_available) {
		$total_columns++;
		$t->parse("item_tax_total_header", false);
	} else {
		$item_tax_total_column = false;
	}
	if ($item_price_incl_tax_total_column && $tax_available) {
		$total_columns++;
		$t->parse("item_price_incl_tax_total_header", false);
	} else {
		$item_price_incl_tax_total_column = false;
	}

	$sc_colspan = $total_columns - 1;
	$t->set_var("goods_colspan", $goods_colspan);
	$t->set_var("properties_colspan", $properties_colspan);
	$t->set_var("sc_colspan", $sc_colspan);
	$t->set_var("total_columns", $total_columns);

	$items_text = ""; $order_coupons_ids = ""; $vouchers_ids = ""; $gift_vouchers = array();

	$total_buying = 0; $total_buying_tax = 0; 
	$goods_total_full = 0; $goods_total = 0; $goods_tax_total = 0; 
	$goods_total_excl_tax = 0; $goods_total_incl_tax = 0;
	$goods_points_amount = 0; 
	$total_items = ($user_order_id) ? $order_data["total_items"] : 0;
	$total_discount = 0; $total_discount_excl_tax = 0; $total_discount_tax = 0; $total_discount_incl_tax = 0;
	$vouchers_amount = 0; $order_total = 0;
	$max_availability_time = 0; $shipping_time = 0;
	$free_postage = false; $free_postage_all = false; $free_postage_ids = array();
	if ($user_tax_free) { $tax_free = $user_tax_free; }
	$recurring_items = false;

	// check data for custom order fields from user details
	if($user_id && (!strlen($operation) || $operation == "fast_order")) {
		// get user details from users table
		$user_db = array();
		$sql = " SELECT * FROM " . $table_prefix . "users WHERE user_id=" . $db->tosql($user_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			// check if user_info is array as for new user this array doesn't exists
			if (is_array($user_info)) {
				$user_info = array_merge($db->Record, $user_info);
			} else {
				$user_info = $db->Record;
			}
		}

		// get user custom fields data
		$user_properties = array();
		$sql  = " SELECT upp.property_code, upp.control_type, up.property_value ";
		$sql .= " FROM (" . $table_prefix . "users_properties up ";
		$sql .= " INNER JOIN " . $table_prefix . "user_profile_properties upp ON up.property_id=upp.property_id) ";
		$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$property_code = $db->f("property_code");
			$property_value = $db->f("property_value");
			$control_type = $db->f("control_type");
			if (strlen($property_code)) {
				if (isset($user_properties[$property_code])) {
					$user_properties[$property_code]["value"][] = $property_value;
				} else {
					$user_properties[$property_code] = array("type" => $control_type, "value" => array($property_value));
				}
			}
		}
		// check real values
		foreach ($user_properties as $field_code => $property_info) {
			$control_type = $property_info["type"];
			$values = $property_info["value"];
			if ($control_type == "RADIOBUTTON" || $control_type == "CHECKBOXLIST" || $control_type == "LISTBOX") {
				foreach ($values as $vi => $value_id) {
					$sql  = " SELECT property_value FROM " . $table_prefix . "user_profile_values ";
					$sql .= " WHERE property_value_id=" . $db->tosql($value_id, INTEGER);
					$db->query($sql);
					if ($db->next_record()) {
						$values[$vi] = $db->f("property_value");
					}
				}
			}
			$user_info[$field_code] = $values;
		}
	}

	// show custom options
	$shipping_properties = array(); // array to save shipping custom properties
	$payment_properties = array(); // array to save payment custom properties
	$personal_number = 0;
	$delivery_number = 0;
	if (sizeof($op_rows) > 0)
	{
		for ($pn = 0; $pn < sizeof($op_rows); $pn++) {
			$property_id = $op_rows[$pn]["property_id"];
			$op_payment_id = $op_rows[$pn]["payment_id"];
			$op_shipping_type_id = $op_rows[$pn]["shipping_type_id"];
			$op_shipping_module_id = $op_rows[$pn]["shipping_module_id"];
			$property_order  = $op_rows[$pn]["property_order"];
			$property_code = $op_rows[$pn]["property_code"];
			$property_name_initial = $op_rows[$pn]["property_name"];
			$property_name = get_translation($property_name_initial);
			$property_description = $op_rows[$pn]["property_description"];
			$property_notes = $op_rows[$pn]["property_notes"];
			$default_value = $op_rows[$pn]["default_value"];
			$property_type = $op_rows[$pn]["property_type"];
			$property_class = $op_rows[$pn]["property_class"];
			$property_style = $op_rows[$pn]["property_style"];
			$control_type = $op_rows[$pn]["control_type"];
			$control_style = $op_rows[$pn]["control_style"];
			$property_required = $op_rows[$pn]["required"];
			$property_tax_id = 0;
			$property_tax_free = $op_rows[$pn]["tax_free"];
			if ($tax_free) { $property_tax_free = $tax_free; }
			$before_name_html = $op_rows[$pn]["before_name_html"];
			$after_name_html = $op_rows[$pn]["after_name_html"];
			$before_control_html = $op_rows[$pn]["before_control_html"];
			$after_control_html = $op_rows[$pn]["after_control_html"];
			$onchange_code = $op_rows[$pn]["onchange_code"];
			$onclick_code = $op_rows[$pn]["onclick_code"];
			$control_code = $op_rows[$pn]["control_code"];
			$validation_regexp = $op_rows[$pn]["validation_regexp"];
			$regexp_error = $op_rows[$pn]["regexp_error"];

			if ($property_type > 0 && $property_type < 4) {
				// populate all ids properties except payment
				if (strlen($order_properties)) { $order_properties .= ","; }
				$order_properties .= $property_id;
				if ($property_type == 2 || $property_type == 3) {
					$properties_fields["op_".$property_id] = array(
						"field_name" => strip_tags($property_name),
						"block_id" => "op_block_".$property_id,
						"required" => $property_required,
						"type" => strtolower($control_type),
						"required_message" => strip_tags(str_replace("{field_name}", $property_name, REQUIRED_MESSAGE)),
						"regexp" => $validation_regexp,
						"regexp_error" => $regexp_error,
					);
				}
			}

			$selected_price = 0; $selected_points_price = 0; $property_prices = 0; $property_pay_points = 0;
			$property_control  = "";
			$property_control .= "<input type=\"hidden\" name=\"op_name_" . $property_id . "\"";
			$property_control .= " value=\"" . strip_tags($property_name) . "\">";
			$property_control .= "<input type=\"hidden\" name=\"op_required_" . $property_id . "\"";
			$property_control .= " value=\"" . intval($property_required) . "\">";
			$property_control .= "<input type=\"hidden\" name=\"op_control_" . $property_id . "\"";
			$property_control .= " value=\"" . strtoupper($control_type) . "\">";
			$property_control .= "<input type=\"hidden\" name=\"op_tax_free_" . $property_id . "\"";
			$property_control .= " value=\"" . intval($property_tax_free) . "\">";

			$sql  = " SELECT * FROM " . $table_prefix . "order_custom_values ";
			$sql .= " WHERE property_id=" . $property_id . " AND hide_value=0";
			$sql .= " ORDER BY property_value_id ";
			if (strtoupper($control_type) == "LISTBOX") {
				// check selected value from database or request
				$selected_value = "";
				if ($operation == "load") {
					$pr_sql  = " SELECT property_value_id FROM " . $table_prefix . "orders_properties ";
					$pr_sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
					$pr_sql .= " AND property_id=" . $db->tosql($property_id, INTEGER);
					$selected_value = get_db_value($pr_sql);
				} else {
					$selected_value = get_param("op_" . $property_id);
				}
				$property_pay_points = get_param("property_pay_points_" . $property_id);
				$properties_prices = "";
				$properties_values = "<option value=\"\">" . SELECT_MSG . " " . $property_name . "</option>" . $eol;
				$db->query($sql);
				while ($db->next_record())
				{
					$property_value_original = $db->f("property_value");
					$property_value = get_translation($property_value_original);
					$property_price = doubleval($db->f("property_price"));
					if ($property_price != 0) {
						$property_prices = 1;
					}
					$property_value_id = $db->f("property_value_id");
					$is_default_value = $db->f("is_default_value");
					$property_selected  = "";
					$properties_prices .= "<input type=\"hidden\" name=\"op_option_price_" . $property_value_id . "\"";
					$properties_prices .= " value=\"" . $property_price . "\">";
					if (strlen($operation)) {
						if ($selected_value == $property_value_id) {
							$property_selected  = "selected ";
							$selected_price    += $property_price;
							$selected_points_price = round($selected_price * $points_conversion_rate, $points_decimals);
							if (!$points_system || !$points_orders_options || $selected_points_price > $points_balance) {
								$selected_points_price = 0; $property_pay_points = 0;
							}
							$custom_options[$property_id][] = array(
								"type" => $property_type, "payment_id" => $op_payment_id, "order" => $property_order, 
								"code" => $property_code, "name" => $property_name_initial, "notes" => $property_notes, 
								"value_id" => $property_value_id, "value" => $property_value_original, "price" => $selected_price, "tax_free" => $property_tax_free,
								"points_price" => $selected_points_price, "pay_points" => $property_pay_points,
							);
						}
					} elseif ($property_code && isset($user_info[$property_code])) {
						// field code used to match it with profile
						$user_values = $user_info[$property_code];
						if (!is_array($user_values)) { $user_values = array($user_values); }
						if (in_array($property_value_original, $user_values)) {
							$property_selected = "selected ";
							$selected_price  += $property_price;
						}
					} elseif ($is_default_value) {
						$property_selected  = "selected ";
						$selected_price    += $property_price;
					}

					$properties_values .= "<option " . $property_selected . "value=\"" . htmlspecialchars($property_value_id) . "\">";
					$properties_values .= htmlspecialchars($property_value);

					$property_tax_percent = $tax_percent_sum;
					// get tax to show price
					$property_tax = get_tax_amount($tax_rates, 0, $property_price, 1, $property_tax_id, $property_tax_free, $property_tax_percent, $default_tax_rates);
					if ($tax_prices_type == 1) {
						$property_price_incl = $property_price;
						$property_price_excl = $property_price - $property_tax;
					} else {
						$property_price_incl = $property_price + $property_tax;
						$property_price_excl = $property_price;
					}
					if ($tax_prices == 2 || $tax_prices == 3) {
						// show property with tax
						$shown_price = $property_price_incl;
					} else {
						$shown_price = $property_price_excl;
					}

					if ($property_price > 0) {
						$properties_values .= $option_positive_price_right . currency_format($shown_price) . $option_positive_price_left;
					} elseif ($property_price < 0) {
						$properties_values .= $option_negative_price_right . currency_format(abs($shown_price)) . $option_negative_price_left;
					}
					$properties_values .= "</option>" . $eol;
				}
				$property_control .= $before_control_html;
				$property_control .= "<select name=\"op_" . $property_id . "\" onchange=\"changeOrderProperty();";
				if ($onchange_code) {	$property_control .= $onchange_code; }
				$property_control .= "\"";
				if ($onclick_code) {	$property_control .= " onclick=\"" . $onclick_code . "\""; }
				if ($control_code) {	$property_control .= " " . $control_code . " "; }
				if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
				$property_control .= ">" . $properties_values . "</select>";
				$property_control .= $properties_prices;
				$property_control .= $after_control_html;
			} elseif (strtoupper($control_type) == "RADIOBUTTON" || strtoupper($control_type) == "CHECKBOXLIST") {
				$is_radio = (strtoupper($control_type) == "RADIOBUTTON");
				$property_pay_points = get_param("property_pay_points_" . $property_id);

				$selected_value = array();
				if ($operation == "load") {
					$pr_sql  = " SELECT property_value_id FROM " . $table_prefix . "orders_properties ";
					$pr_sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
					$pr_sql .= " AND property_id=" . $db->tosql($property_id, INTEGER);
					$db->query($pr_sql);
					while ($db->next_record()) {
						$selected_value[] = $db->f("property_value_id");
					}
				} else if (strlen($operation)) {
					if ($is_radio) {
						$selected_value[] = get_param("op_" . $property_id);
					} else {
						$total_options = get_param("op_total_" . $property_id);
						for ($op = 1; $op <= $total_options; $op++) {
							$selected_value[] = get_param("op_" . $property_id . "_" . $op);
						}
					}
				}

				$input_type = $is_radio ? "radio" : "checkbox";
				$property_control .= "<span";
				if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
				$property_control .= ">";
				$value_number = 0; 
				$db->query($sql);
				while ($db->next_record())
				{
					$value_number++;
					$property_price = doubleval($db->f("property_price"));
					if ($property_price != 0) {
						$property_prices = 1;
					}
					$property_value_id = $db->f("property_value_id");
					$item_code = $db->f("item_code");
					$manufacturer_code = $db->f("manufacturer_code");
					$is_default_value = $db->f("is_default_value");
					$property_value_original = $db->f("property_value");
					$property_value = get_translation($property_value_original);
					$property_checked = "";
					$property_control .= $before_control_html;
					$property_control .= "<input type=\"hidden\" name=\"op_option_price_" . $property_value_id . "\"";
					$property_control .= " value=\"" . $property_price . "\">";
					if (strlen($operation)) {
						if (in_array($property_value_id, $selected_value)) {
							$property_checked = "checked ";
							$selected_price  += $property_price;
							$property_points_price = round($property_price * $points_conversion_rate, $points_decimals);
							$selected_points_price += $property_points_price;
							if (!$points_system || !$points_orders_options || $selected_points_price > $points_balance) {
								$selected_points_price = 0; $property_pay_points = 0; $property_points_price = 0;
							}
							$custom_options[$property_id][] = array(
								"type" => $property_type, "payment_id" => $op_payment_id, "order" => $property_order, 
								"code" => $property_code, "name" => $property_name_initial, "notes" => $property_notes, 
								"value_id" => $property_value_id, "value" => $property_value_original, "price" => $property_price, "tax_free" => $property_tax_free,
								"points_price" => $property_points_price, "pay_points" => $property_pay_points
							);
						}
					} elseif ($property_code && isset($user_info[$property_code])) {
						// field code used to match it with user profile
						$user_values = $user_info[$property_code];
						if (!is_array($user_values)) { $user_values = array($user_values); }
						if (in_array($property_value_original, $user_values)) {
							$property_checked = "checked ";
							$selected_price  += $property_price;
						}
					} elseif ($is_default_value) {
						$property_checked = "checked ";
						$selected_price  += $property_price;
					}

					$control_name = ($is_radio) ? ("op_".$property_id) : ("op_".$property_id."_".$value_number);
					$property_control .= "<input type=\"" . $input_type . "\" name=\"" . $control_name . "\" ". $property_checked;
					$property_control .= "value=\"" . htmlspecialchars($property_value_id) . "\" onclick=\"changeOrderProperty(); ";
					if ($onclick_code) {
						$control_onclick_code = str_replace("{option_value}", $property_value, $onclick_code);
						$property_control .= $control_onclick_code;
					}
					$property_control .= "\"";
					if ($onchange_code) {	$property_control .= " onchange=\"" . $onchange_code . "\""; }
					if ($control_code) {	$property_control .= " " . $control_code . " "; }
					$property_control .= ">";
					$property_control .= $property_value;

					$property_tax_percent = $tax_percent_sum;
					// get tax to show price
					$property_tax = get_tax_amount($tax_rates, 0, $property_price, 1, $property_tax_id, $property_tax_free, $property_tax_percent, $default_tax_rates);
					if ($tax_prices_type == 1) {
						$property_price_incl = $property_price;
						$property_price_excl = $property_price - $property_tax;
					} else {
						$property_price_incl = $property_price + $property_tax;
						$property_price_excl = $property_price;
					}
					if ($tax_prices == 2 || $tax_prices == 3) {
						// show property with tax
						$shown_price = $property_price_incl;
					} else {
						$shown_price = $property_price_excl;
					}

					if ($property_price > 0) {
						$property_control .= $option_positive_price_right . currency_format($shown_price) . $option_positive_price_left;
					} elseif ($property_price < 0) {
						$property_control .= $option_negative_price_right . currency_format(abs($shown_price)) . $option_negative_price_left;
					}
					$property_control .= $after_control_html;
				}
				$property_control .= "</span>";
				$property_control .= "<input type=\"hidden\" name=\"op_total_".$property_id."\" value=\"".$value_number."\">";
			} elseif (strtoupper($control_type) == "TEXTBOX") {
				if (strlen($operation)) {
					if ($operation == "load") {
						$pr_sql  = " SELECT property_value FROM " . $table_prefix . "orders_properties ";
						$pr_sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
						$pr_sql .= " AND property_id=" . $db->tosql($property_id, INTEGER);
						$control_value = get_db_value($pr_sql);
					} else {
						$control_value = get_param("op_" . $property_id);
					}
					if (strlen($control_value)) {
						$custom_options[$property_id][] = array(
							"type" => $property_type, "payment_id" => $op_payment_id, "order" => $property_order, 
							"code" => $property_code, "name" => $property_name_initial, "notes" => $property_notes, 
							"value_id" => "", "value" => $control_value, "price" => 0, "tax_free" => 0,
							"points_price" => 0, "pay_points" => 0
						);
					}
				} elseif ($property_code && isset($user_info[$property_code])) {
					// field code used to match it with user profile
					$user_value = $user_info[$property_code];
					if (is_array($user_value)) { $user_value = implode("; ", $user_value); }
					$control_value = $user_value;
				} else {
					$control_value = $default_value;
				}
				$property_control .= $before_control_html;
				$property_control .= "<input type=\"text\" name=\"op_" . $property_id . "\"";
				if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
				if ($onclick_code) {	$property_control .= " onclick=\"" . $onclick_code . "\""; }
				if ($onchange_code) {	$property_control .= " onchange=\"" . $onchange_code . "\""; }
				if ($control_code) {	$property_control .= " " . $control_code . " "; }
				$property_control .= " value=\"". htmlspecialchars($control_value) . "\">";
				$property_control .= $after_control_html;
			} elseif (strtoupper($control_type) == "TEXTAREA") {
				if (strlen($operation)) {
					if ($operation == "load") {
						$pr_sql  = " SELECT property_value FROM " . $table_prefix . "orders_properties ";
						$pr_sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
						$pr_sql .= " AND property_id=" . $db->tosql($property_id, INTEGER);
						$control_value = get_db_value($pr_sql);
					} else {
						$control_value = get_param("op_" . $property_id);
					}
					if (strlen($control_value)) {
						$custom_options[$property_id][] = array(
							"type" => $property_type, "payment_id" => $op_payment_id, "order" => $property_order, 
							"code" => $property_code, "name" => $property_name_initial, "notes" => $property_notes, 
							"value_id" => "", "value" => $control_value, "price" => 0, "tax_free" => 0,
							"points_price" => 0, "pay_points" => 0
						);
					}
				} elseif ($property_code && isset($user_info[$property_code])) {
					// field code used to match it with user profile
					$user_value = $user_info[$property_code];
					if (is_array($user_value)) { $user_value = implode("; ", $user_value); }
					$control_value = $user_value;
				} else {
					$control_value = $default_value;
				}
				$property_control .= $before_control_html;
				$property_control .= "<textarea name=\"op_" . $property_id . "\"";
				if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
				if ($onclick_code) {	$property_control .= " onclick=\"" . $onclick_code . "\""; }
				if ($onchange_code) {	$property_control .= " onchange=\"" . $onchange_code . "\""; }
				if ($control_code) {	$property_control .= " " . $control_code . " "; }
				$property_control .= ">". htmlspecialchars($control_value) ."</textarea>";
				$property_control .= $after_control_html;
			} elseif (strtoupper($control_type) == "HIDDEN") {
				if (strlen($operation)) {
					if ($operation == "load") {
						$pr_sql  = " SELECT property_value FROM " . $table_prefix . "orders_properties ";
						$pr_sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
						$pr_sql .= " AND property_id=" . $db->tosql($property_id, INTEGER);
						$control_value = get_db_value($pr_sql);
					} else {
						$control_value = get_param("op_" . $property_id);
					}
					if (strlen($control_value)) {
						$custom_options[$property_id][] = array(
							"type" => $property_type, "payment_id" => $op_payment_id, "order" => $property_order, 
							"code" => $property_code, "name" => $property_name_initial, "notes" => $property_notes, 
							"value_id" => "", "value" => $control_value, "price" => 0, "tax_free" => 0,
							"points_price" => 0, "pay_points" => 0
						);
					}
				} elseif ($property_code && isset($user_info[$property_code])) {
					// field code used to match it with user profile
					$user_value = $user_info[$property_code];
					if (is_array($user_value)) { $user_value = implode("; ", $user_value); }
					$control_value = $user_value;
				} else {
					$control_value = $default_value;
				}
				$property_control .= $before_control_html;
				$property_control .= "<input type=\"hidden\" name=\"op_" . $property_id . "\"";
				$property_control .= " value=\"". htmlspecialchars($control_value) . "\">";
				$property_control .= $after_control_html;
			} else {
				$property_control .= $before_control_html;
				if ($property_required) {
					$property_control .= "<input type=\"hidden\" name=\"op_" . $property_id . "\" value=\"" . htmlspecialchars($default_value) . "\">";
				}
				if ($property_required && strlen($default_value)) {
					$custom_options[$property_id][] = array(
						"type" => $property_type, "payment_id" => $op_payment_id, "order" => $property_order, 
						"code" => $property_code, "name" => $property_name_initial, "notes" => $property_notes, 
						"value_id" => "", "value" => $default_value, "price" => 0, "tax_free" => 0,
						"points_price" => 0, "pay_points" => 0
					);
				}
				$property_control .= "<span";
				if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
				if ($onclick_code) {	$property_control .= " onclick=\"" . $onclick_code . "\""; }
				if ($onchange_code) {	$property_control .= " onchange=\"" . $onchange_code . "\""; }
				if ($control_code) {	$property_control .= " " . $control_code . " "; }
				$property_control .= ">" . get_translation($default_value) . "</span>";
				$property_control .= $after_control_html;
			}

			// get taxes for selected properties and add it to total values 
			$selected_tax_amount = get_tax_amount($tax_rates, 0, $selected_price, 1, $property_tax_id, $property_tax_free, $property_tax_percent, $default_tax_rates);
			$selected_tax_values = get_tax_amount($tax_rates, 0, $selected_price, 1, $property_tax_id, $property_tax_free, $property_tax_percent, $default_tax_rates, 2);
			if (!$property_pay_points) {
				add_tax_values($tax_rates, $selected_tax_values, "properties");
			}
			// check price with and without tax
			if ($tax_prices_type == 1) {
				$selected_price_excl_tax = $selected_price - $selected_tax_amount;
				$selected_price_incl_tax = $selected_price;
			} else {
				$selected_price_excl_tax = $selected_price;
				$selected_price_incl_tax = $selected_price + $selected_tax_amount;
			}

			if ($property_pay_points) {
				$properties_points_amount += $selected_points_price;
			} else {
				$properties_total += $selected_price;
				if ($property_tax_free != 1) {
					$properties_taxable += $selected_price;
				}
				$properties_incl_tax += $selected_price_incl_tax;
			}
			$t->set_var("property_id", $property_id);
			//$t->set_var("property_block_id", $property_block_id);
			$t->set_var("property_name", $before_name_html . $property_name . $after_name_html);
			if ($selected_price == 0 || $property_pay_points) {
				$t->set_var("op_price_excl_tax", "");
				$t->set_var("op_tax", "");
				$t->set_var("op_price_incl_tax", "");
			} else {
				if ($tax_prices_type == 1) {
					$op_price_excl_tax = $selected_price - $selected_tax_amount;
					$op_price_incl_tax = $selected_price;
				} else {
					$op_price_excl_tax = $selected_price;
					$op_price_incl_tax = $selected_price + $selected_tax_amount;
				}
				$t->set_var("op_price_excl_tax", currency_format($op_price_excl_tax));
				$t->set_var("op_tax", currency_format($selected_tax_amount));
				$t->set_var("op_price_incl_tax", currency_format($op_price_incl_tax));
			}
			$t->set_var("property_class", $property_class);
			$t->set_var("property_style", $property_style);
			$t->set_var("property_control", $property_control);
			$t->sparse("property_control_block", false);
			$t->set_var("property_value_block", "");
			if ($property_required) {
				$t->set_var("property_required", "*");
			} else {
				$t->set_var("property_required", "");
			}

			$property_message = ""; // variable to save custom fields errors
			if (($operation == "save" || $operation == "next" || $operation == "load") && $property_required && !isset($custom_options[$property_id])) {
				$property_message = str_replace("{field_name}", $property_name, REQUIRED_MESSAGE) . "<br/>\n";
				if ($property_type == 1) {
					$sc_errors .= $property_message;
				} elseif ($property_type == 2 || $property_type == 3) {
					$options_errors .= $property_message;
				}
			}

			// check option with regexp
			$regexp_valid = true;
			if (($operation == "save" || $operation == "next" || $operation == "load") && isset($custom_options[$property_id]) && strlen($validation_regexp)) {
				$validation_value = "";
				foreach ($custom_options[$property_id] as $option_id => $option_data) {
					if (strval($validation_value) != "") { $validation_value .= ","; }
					$validation_value .= $option_data["value"];
				}
				if (!preg_match($validation_regexp, $validation_value)) {
					$regexp_valid = false;
				}
			}
			if (!$regexp_valid) {
				$property_message = str_replace("{field_name}", $property_name, $regexp_error) . "<br>";
				if ($property_type == 1) {
					$sc_errors .= $property_message;
				} elseif ($property_type == 2 || $property_type == 3) {
					$options_errors .= $property_message;
				}
			}

			if ($points_system && $points_orders_options && $property_prices && $points_balance > 0) {
				if ($property_pay_points) {
					$t->set_var("property_pay_points_checked", "checked");
				} else {
					$t->set_var("property_pay_points_checked", "");
				}
				$t->parse("property_points_pay_block", false);
			} else {
				$t->set_var("property_points_pay_block", "");
			}

			if (strtoupper($control_type) == "HIDDEN") {
				$t->sparse("hidden_properties", true);
			} else if ($property_type == 1) {
				if ($item_price_total_column) {
					$t->parse("property_price_excl_tax_column", false);
				}
				if ($item_tax_total_column) {
					$t->parse("property_tax_column", false);
				}
				if ($item_price_incl_tax_total_column) {
					$t->parse("property_price_incl_tax_column", false);
				}
				$t->parse("cart_properties", true);
			} elseif ($property_type == 2) {
				$personal_number++; 
				$t->parse("personal_properties", true);
			} elseif ($property_type == 3) {
				$delivery_number++;
				$t->parse("delivery_properties", true);
			} elseif ($property_type == 4) {
				// save payment properties to use below in payment settings section
				$payment_properties[$property_id] = array(
					"id" => $property_id,
					"payment_id" => $op_payment_id,
					"name" => $before_name_html.$property_name.$after_name_html,
					"class" => $property_class,
					"style" => $property_style,
					"control" => $property_control,
					"required" => $property_required,
					"error" => $property_message,
				);
				// end of saving payment properties
			} elseif ($property_type == 5 || $property_type == 6) {
				// save shipping properties to use below in shipping section
				$shipping_properties[$property_id] = array(
					"id" => $property_id,
					"shipping_type_id" => $op_shipping_type_id,
					"shipping_module_id" => $op_shipping_module_id,
					"name" => $before_name_html.$property_name.$after_name_html,
					"class" => $property_class,
					"style" => $property_style,
					"control" => $property_control,
					"required" => $property_required,
					"error" => $property_message,
				);
				// end of saving shipping properties
			}
		}

		$t->set_var("order_properties", $order_properties);
		$t->set_var("properties_total", $properties_total);
		$t->set_var("properties_taxable", $properties_taxable);

	}
	// end custom options

	$coupons = get_session("session_coupons"); $quantities_discounts = array();
	$order_coupons = array();
	$cart_items = array(); $cart_ids = array(); $stock_levels = array(); $options_stock_levels = array();
	if (!strlen($user_id)) $user_id = 0;
	// variables calculate on step #6
	$shipping_items_total = 0; $total_quantity = 0; $weight_total = 0; $actual_weight_total = 0; $shipping_weight = 0; $shipping_actual_weight = 0; 
	$shipping_quantity = ($user_order_id) ? $order_data["shipping_quantity"] : 0;

	if (is_array($shopping_cart))
	{
		$properties_ids = "";
		// #1 - prepare cart items
		foreach ($shopping_cart as $cart_id => $item)
		{
			$item_id = $item["ITEM_ID"];
			$cc_price = isset($item["CC_PRICE"]) ? $item["CC_PRICE"] : "";
			$wishlist_item_id = isset($item["WISH_ITEM_ID"]) ? $item["WISH_ITEM_ID"] : "";
			$quantity = $item["QUANTITY"];
			$subscription_id = isset($item["SUBSCRIPTION_ID"]) ? $item["SUBSCRIPTION_ID"] : "";
			// check subscription
			if ($subscription_id) {
				$sql  = " SELECT is_subscription_recurring, user_type_id, subscription_name, subscription_fee, ";
				$sql .= " subscription_period, subscription_interval, subscription_suspend, ";
				$sql .= " subscription_affiliate_type, subscription_affiliate_amount, subscription_points_type, ";
				$sql .= " subscription_points_amount, subscription_credits_type, subscription_credits_amount ";
				$sql .= " FROM " . $table_prefix . "subscriptions ";
				$sql .= " WHERE subscription_id=" . $db->tosql($subscription_id, INTEGER) . " AND is_active=1 ";
				$db->query($sql);
				if ($db->next_record()) {
					$total_items++;
					$is_recurring = $db->f("is_subscription_recurring");
					$subscription_type_id = $db->f("user_type_id");
					$is_account_subscription = ($subscription_type_id) ? 1 : 0;
					$subscription_fee = $db->f("subscription_fee");
					$subscription_name_initial = $db->f("subscription_name");
					$subscription_name = get_translation($subscription_name_initial);
					$subscription_period = $db->f("subscription_period");
					$subscription_interval = $db->f("subscription_interval");
					$subscription_suspend = $db->f("subscription_suspend");

					$subscription_affiliate_type = $db->f("subscription_affiliate_type");
					$subscription_affiliate_amount = $db->f("subscription_affiliate_amount");
					$subscription_points_type = $db->f("subscription_points_type");
					$subscription_points_amount = $db->f("subscription_points_amount");
					$subscription_credits_type = $db->f("subscription_credits_type");
					$subscription_credits_amount = $db->f("subscription_credits_amount");

					if ($is_recurring) {
						$recurring_period = $subscription_period;
						$recurring_interval = $subscription_interval;
					} else {
						$recurring_period = ""; $recurring_interval = "";
					}

					$subscription_tax_id = 0;
					// re-calculate price in case if prices include some default tax rate 
					$subscription_item_tax = get_tax_amount($tax_rates, 0, $subscription_fee, 1, $subscription_tax_id, $tax_free, $subscription_tax_percent, $default_tax_rates);

					$cart_item_id = $cart_id;
					$cart_items[$cart_item_id] = array(
						"parent_cart_id" => "", "is_bundle" => 0, "top_order_item_id" => 0,
						"item_id" => 0, "id" => 0, "product_id" => 0, "parent_item_id" => 0,
						"item_user_id" => 0, "item_type_id" => 0, "supplier_id" => 0, "wishlist_item_id" => $wishlist_item_id,
						"item_code" => "", "manufacturer_code" => "", 
						"selection_name" => "", "selection_order" => "", "component_name" => "",
						"item_image" => "", "item_image_alt" => "",
						"packages_number" => 0, "width" => 0, "height" => 0, "length" => 0,
						"weight" => 0, "actual_weight" => 0, "shipping_cost" => 0, "is_shipping_free" => 1, 
						"countries_all" => "", "shipping_rule_id" => "", 
						"shipping_modules_default" => 1, "shipping_modules_ids" => "",
						"price" => $subscription_fee, "quantity" => $quantity, 
						"tax_id" => $subscription_tax_id, "tax_free" => $tax_free, "tax_percent" => $subscription_tax_percent,
						"real_price" => $subscription_fee, "discount_amount" => 0, 
						"coupons" => "", "coupons_ids" => "",
						"affiliate_type" => $subscription_affiliate_type, "affiliate_amount" => $subscription_affiliate_amount,
						"merchant_type" => 0, "merchant_amount" => 0,
						"is_points_price" => 0, "points_price" => 0, 
						"reward_type" => $subscription_points_type, "reward_amount" => $subscription_points_amount, 
						"credit_reward_type" => $subscription_credits_type, "credit_reward_amount" => $subscription_credits_amount, 
						"coupons" => "", "coupons_ids" => "", "coupons_discount" => 0, 
						"buying_price" => 0, "item_name" => $subscription_name_initial, "product_name" => $subscription_name_initial,
						"product_title" => $subscription_name_initial, "item_title" => $subscription_name_initial, 
						"discount_applicable" => 0, "properties_discount" => 0, 
						"properties_info" => "", "properties_html" => "", "properties_text" => "",
						"downloadable" => 1, "downloads" => "", 
						"stock_level" => "", "availability_time" => "",
						"short_description" => "", "description" => "", "full_description" => "",
						"generate_serial" => 0, "serial_period" => "", "activations_number" => "", "is_gift_voucher" => 0, "is_user_voucher" => 0,
						"is_recurring" => $is_recurring, "recurring_price" => "", "recurring_period" => $recurring_period,
						"recurring_interval" => $recurring_interval, "recurring_payments_total" => "",
						"recurring_start_date" => "", "recurring_end_date" => "",
						"is_subscription" => 1, "is_account_subscription" => $is_account_subscription, 
						"subscription_id" => $subscription_id, "subscription_period" => $subscription_period,
						"subscription_interval" => $subscription_interval, "subscription_suspend" => $subscription_suspend,
					);
				}
				continue;
			}

			$properties = $item["PROPERTIES"];
			$components = $item["COMPONENTS"];
			$item_coupons = isset($item["COUPONS"]) ? $item["COUPONS"] : "";
			if ($cc_order || VA_Products::check_permissions($item_id, VIEW_ITEMS_PERM)) {
				$sql  = " SELECT i.parent_item_id, i.item_code, i.manufacturer_code, i.item_type_id, i.supplier_id, i.user_id, ";
				$sql .= " i.item_name, i.cart_item_name, i.short_description, i.full_description, ";
				$sql .= " i.buying_price, i." . $price_field . ", i.is_price_edit, i.is_sales, i." . $sales_field . ", i.tax_id, i.tax_free, ";
				$sql .= " i.is_points_price, i.points_price, i.reward_type, i.reward_amount, i.credit_reward_type, i.credit_reward_amount, ";
				$sql .= " it.reward_type AS type_bonus_reward, it.reward_amount AS type_bonus_amount, ";
				$sql .= " it.credit_reward_type AS type_credit_reward, it.credit_reward_amount AS type_credit_amount, ";
				$sql .= " i.packages_number, i.width, i.height, i.length, ";
				$sql .= " i.stock_level, i.use_stock_level, i.hide_out_of_stock, i.disable_out_of_stock, i.weight, i.actual_weight, ";
				$sql .= " i.merchant_fee_type AS item_merchant_type, i.merchant_fee_amount AS item_merchant_amount, i.affiliate_commission_type AS item_affiliate_type, i.affiliate_commission_amount AS item_affiliate_amount, ";
				$sql .= " it.merchant_fee_type AS type_merchant_type, it.merchant_fee_amount AS type_merchant_amount, it.affiliate_commission_type AS type_affiliate_type, it.affiliate_commission_amount AS type_affiliate_amount, ";
				$sql .= " i.downloadable, i.download_period, i.download_path, i.generate_serial, i.serial_period, i.activations_number, ";
				$sql .= " st_in.availability_time AS in_stock_availability , st_out.availability_time AS out_stock_availability, ";
				$sql .= " sr.shipping_rule_id, sr.countries_all, i.shipping_cost, i.is_shipping_free, ";
				$sql .= " i.shipping_modules_default, i.shipping_modules_ids, ";
				$sql .= " i.is_recurring, i.recurring_price, i.recurring_period, i.recurring_interval, ";
				$sql .= " i.recurring_payments_total, i.recurring_start_date, i.recurring_end_date, ";
				$sql .= " it.is_gift_voucher, it.is_user_voucher, it.is_bundle, ";
				$sql .= " i.min_quantity, i.max_quantity, i.quantity_increment, ";
				$sql .= " i.tiny_image, i.tiny_image_alt, i.small_image, i.small_image_alt, i.big_image, i.big_image_alt, i.super_image ";
				$sql .= " FROM ((((" . $table_prefix . "items i ";			
				$sql .= " LEFT JOIN " . $table_prefix . "item_types it ON i.item_type_id=it.item_type_id) ";
				$sql .= " LEFT JOIN " . $table_prefix . "shipping_times st_in ON i.shipping_in_stock=st_in.shipping_time_id) ";
				$sql .= " LEFT JOIN " . $table_prefix . "shipping_times st_out ON i.shipping_out_stock=st_out.shipping_time_id) ";
				$sql .= " LEFT JOIN " . $table_prefix . "shipping_rules sr ON i.shipping_rule_id=sr.shipping_rule_id) ";
				$sql .= " WHERE i.item_id=" . $db->tosql($item_id, INTEGER);
							
				$db->query($sql);
				if ($db->next_record())
				{
					$total_items++;
	
					$parent_item_id = $db->f("parent_item_id");
					$price = $db->f($price_field);
					$is_price_edit = $db->f("is_price_edit");
					if (strlen($cc_price)) {
						$price = $cc_price;
					} else if ($is_price_edit) {
						$price = $item["PRICE"];
					}
					$item_type_id = $db->f("item_type_id");
					$items_type_ids[] = $item_type_id;
					$supplier_id = $db->f("supplier_id");
					$item_user_id = $db->f("user_id");
					$is_sales = $db->f("is_sales");
					$sales_price = $db->f($sales_field);
					$coupons_ids = ""; $coupons_discount = ""; $coupons_applied = array();
					get_sales_price($price, $is_sales, $sales_price, $item_id, $item_type_id, "", "", $coupons_ids, $coupons_discount, $coupons_applied, "coupon");
					$item_code = $db->f("item_code");
					$manufacturer_code = $db->f("manufacturer_code");
					$buying_price = doubleval($db->f("buying_price"));
					// points data
					$is_points_price = $db->f("is_points_price");
					$points_price = $db->f("points_price");
					$reward_type = $db->f("reward_type");
					$reward_amount = $db->f("reward_amount");
					$credit_reward_type = $db->f("credit_reward_type");
					$credit_reward_amount = $db->f("credit_reward_amount");
					if (!strlen($reward_type)) {
						$reward_type = $db->f("type_bonus_reward");
						$reward_amount = $db->f("type_bonus_amount");
					}
					if (!strlen($credit_reward_type)) {
						$credit_reward_type = $db->f("type_credit_reward");
						$credit_reward_amount = $db->f("type_credit_amount");
					}
	
					$item_name = $db->f("item_name");
					$cart_item_name = $db->f("cart_item_name");

					$downloadable = $db->f("downloadable");
					$download_period = $db->f("download_period");
					$download_path = $db->f("download_path");
					$generate_serial = $db->f("generate_serial");
					$serial_period = $db->f("serial_period");
					$activations_number = $db->f("activations_number");
					$is_gift_voucher = $db->f("is_gift_voucher");
					$is_user_voucher = $db->f("is_user_voucher");
					$is_bundle = $db->f("is_bundle");
					$stock_level = $db->f("stock_level");
					$use_stock_level = $db->f("use_stock_level");
					$hide_out_of_stock = $db->f("hide_out_of_stock");
					$disable_out_of_stock = $db->f("disable_out_of_stock");
					if ($stock_level > 0) {
						$availability_time = $db->f("in_stock_availability");
					} else {
						$availability_time = $db->f("out_stock_availability");
					}
					if ($availability_time > $max_availability_time) {
						$max_availability_time = $availability_time;
					}
					$min_quantity = $db->f("min_quantity");
					$max_quantity = $db->f("max_quantity");
					$quantity_increment = $db->f("quantity_increment");

					$shipping_rule_id = $db->f("shipping_rule_id");
					$countries_all = $db->f("countries_all");
	
					$packages_number = $db->f("packages_number");
					$weight = doubleval($db->f("weight"));
					$actual_weight = doubleval($db->f("actual_weight"));
					$width = $db->f("width");
					$height = $db->f("height");
					$length = $db->f("length");
					$is_shipping_free = $db->f("is_shipping_free");
					$shipping_cost = doubleval($db->f("shipping_cost"));
					$shipping_modules_default = $db->f("shipping_modules_default");
					$shipping_modules_ids= $db->f("shipping_modules_ids");
					if ($is_shipping_free) { $shipping_cost = 0; }
					$item_tax_id = $db->f("tax_id");
					$item_tax_free = $db->f("tax_free");
					if ($tax_free) { $item_tax_free = $tax_free; }
					$short_description = strip_tags($db->f("short_description"));
					$full_description = strip_tags($db->f("full_description"));
					// get commission fields
					$item_merchant_type = $db->f("item_merchant_type");
					$item_merchant_amount = $db->f("item_merchant_amount");
					$item_affiliate_type = $db->f("item_affiliate_type");
					$item_affiliate_amount = $db->f("item_affiliate_amount");
					if (!strlen($item_merchant_type)) {
						$item_merchant_type = $db->f("type_merchant_type");
						$item_merchant_amount = $db->f("type_merchant_amount");
					}
					if (!strlen($item_affiliate_type)) {
						$item_affiliate_type = $db->f("type_affiliate_type");
						$item_affiliate_amount = $db->f("type_affiliate_amount");
					}
					$is_recurring = $db->f("is_recurring");
					$recurring_items = ($is_recurring || $recurring_items);
					$recurring_price = $db->f("recurring_price");
					$recurring_period = $db->f("recurring_period");
					$recurring_interval = $db->f("recurring_interval");
					$recurring_payments_total = $db->f("recurring_payments_total");
					$recurring_start_date = $db->f("recurring_start_date", DATETIME);
					$recurring_end_date = $db->f("recurring_end_date", DATETIME);
	
					// item image
					$item_image = ""; $item_image_alt = ""; 
					if ($image_field) {
						$item_image = $db->f($image_field);	
						$item_image_alt = get_translation($db->f($image_alt_field));	
					}
					$big_image = $db->f("big_image");	
					$super_image = $db->f("super_image");	

					// check image and parent product name which could be shown in the cart
					if ($parent_item_id) {
						$sql  = " SELECT i.item_type_id,i.item_name,i.".$price_field.",i.is_price_edit,i.is_sales,i.".$sales_field.",i.buying_price,";
						$sql .= " i.tax_id,i.tax_free,i.stock_level, i.min_quantity,i.max_quantity,i.quantity_increment, ";
						$sql .= " i.use_stock_level,i.hide_out_of_stock,i.disable_out_of_stock, ";
						$sql .= " i.tiny_image, i.tiny_image_alt, i.small_image, i.small_image_alt, i.big_image, i.big_image_alt, i.super_image, i.super_image_alt ";
						$sql .= " FROM " . $table_prefix . "items i ";
						$sql .= " WHERE i.item_id=" . $db->tosql($parent_item_id, INTEGER);
						$db->query($sql);
						if ($db->next_record()) {
							$parent_item_name = $db->f("item_name");
							if (!$item_image && $image_field) {
								$item_image = $db->f($image_field);	
								$item_image_alt = get_translation($db->f($image_alt_field));	
								$big_image = $db->f("big_image");	
								$super_image = $db->f("super_image");	
							}
							if (!strlen($cart_item_name) && $cart_subitem_name) {	
								$search_tags = array("{parent_name}", "{parent_item_name}", "{item_name}", "{product_name}", "{sub_name}", "{subitem_name}", "{sub_item_name}", "{subproduct_name}", "{sub_product_name}");
								$replace_values = array($parent_item_name, $parent_item_name, $item_name, $item_name, $item_name, $item_name, $item_name, $item_name, $item_name);
								$cart_item_name = str_replace($search_tags, $replace_values, $cart_subitem_name);
							}
						} else {	
							$parent_item_id = "";
						}
					}
					if (strlen($cart_item_name)) { $item_name = $cart_item_name; }
					
					// some price calculation
					$real_price = $price;
					$properties_discount = 0;
					$discount_applicable = 1;
					if (!strlen($cc_price) && !$is_price_edit) {
						$price = calculate_price($price, $is_sales, $sales_price);
						$real_price = $price;
						$quantity_price = get_quantity_price($item_id, $quantity);
						if (sizeof($quantity_price)) {
							$price = $quantity_price[0];
							$real_price = $price;
							$properties_discount = $quantity_price[1];
							$discount_applicable = $quantity_price[2];
						}
						if ($discount_applicable) {
							if ($user_discount_type == 1 || $user_discount_type == 3) {
								$price -= round(($price * $user_discount_amount) / 100, 2);
							} elseif ($user_discount_type == 2) {
								$price -= round($user_discount_amount, 2);
							} elseif ($user_discount_type == 4) {
								$price -= round((($price - $buying_price) * $user_discount_amount) / 100, 2);
							}
						}
					}
	
					// re-calculate price in case if prices include some default tax rate
					$item_tax = get_tax_amount($tax_rates, $item_type_id, $price, 1, $item_tax_id, $item_tax_free, $item_tax_percent, $default_tax_rates);
					$item_real_tax = get_tax_amount($tax_rates, $item_type_id, $real_price, 1, $item_tax_id, $item_tax_free, $item_tax_percent, $default_tax_rates);
					//$item_buying_tax = get_tax_amount($tax_rates, $item_type_id, $buying_price, $item_tax_free, $item_tax_percent, $default_tax_rates);
	
					$cart_item_id = $cart_id;
					$cart_items[$cart_item_id] = array(
						"parent_cart_id" => "", "top_order_item_id" => 0, "is_bundle" => $is_bundle,
						"item_id" => $item_id, "id" => $item_id, "product_id" => $item_id, "parent_item_id" => $parent_item_id,						
						"selection_name" => "", "selection_order" => "", "component_name" => "",
						"item_user_id" => $item_user_id, "item_type_id" => $item_type_id, 
						"supplier_id" => $supplier_id, "wishlist_item_id" => $wishlist_item_id,
						"item_code" => $item_code, "manufacturer_code" => $manufacturer_code, 
						"item_image" => $item_image, "item_image_alt" => $item_image_alt, 
						"big_image" => $big_image, "super_image" => $super_image, 
						"packages_number" => $packages_number, "width" => $width, "height" => $height, "length" => $length,
						"weight" => $weight, "actual_weight" => $actual_weight, "price" => $price, "quantity" => $quantity,
						"shipping_cost" => $shipping_cost, "is_shipping_free" => $is_shipping_free, 
						"shipping_modules_default" => $shipping_modules_default, "shipping_modules_ids" => $shipping_modules_ids,
						"countries_all" => $countries_all, "shipping_rule_id" => $shipping_rule_id,
						"tax_id" => $item_tax_id, "tax_free" => $item_tax_free, "tax_percent" => $item_tax_percent,
						"real_price" => $real_price, "discount_amount" => ($real_price - $price),  
						"coupons" => $item_coupons, "coupons_applied" => $coupons_applied, 
						"coupons_ids" => $coupons_ids, "coupons_discount" => $coupons_discount, 
						"affiliate_type" => $item_affiliate_type, "affiliate_amount" => $item_affiliate_amount,
						"merchant_type" => $item_merchant_type, "merchant_amount" => $item_merchant_amount,
						"is_points_price" => $is_points_price, "points_price" => $points_price, 
						"reward_type" => $reward_type, "reward_amount" => $reward_amount, 
						"credit_reward_type" => $credit_reward_type, "credit_reward_amount" => $credit_reward_amount, 
						"buying_price" => $buying_price, "item_name" => $item_name, "product_name" => $item_name,
						"product_title" => $item_name, "item_title" => $item_name, 
						"discount_applicable" => $discount_applicable, "properties_discount" => $properties_discount, 
						"downloadable" => $downloadable, "downloads" => "", 
						"stock_level" => $stock_level, "availability_time" => $availability_time, 
						"min_quantity" => $min_quantity, "max_quantity" => $max_quantity, "quantity_increment" => $quantity_increment, 
						"short_description" => $short_description, "description" => $short_description, "full_description" => $full_description,
						"generate_serial" => $generate_serial, "serial_period" => $serial_period, "activations_number" => $activations_number,
						"is_gift_voucher" => $is_gift_voucher, "is_user_voucher" => $is_user_voucher,
						"is_recurring" => $is_recurring, "recurring_price" => $recurring_price, "recurring_period" => $recurring_period,
						"recurring_interval" => $recurring_interval, "recurring_payments_total" => $recurring_payments_total,
						"recurring_start_date" => $recurring_start_date, "recurring_end_date" => $recurring_end_date,
						"is_subscription" => 0, "is_account_subscription" => 0, "subscription_period" => "", 
						"subscription_interval" => "", "subscription_suspend" => "",
					);
	
					$cart_ids[] = $cart_id;
					// update stock level information
					if (isset($stock_levels[$item_id])) {
						$stock_levels[$item_id]["quantity"] += $quantity;
						$stock_levels[$item_id]["stock_level"] = $stock_level;
					} else {
						$stock_levels[$item_id] = array(
							"item_name" => $item_name, "quantity" => $quantity, "stock_level" => $stock_level, 
							"use_stock_level" => $use_stock_level, "hide_out_of_stock" => $hide_out_of_stock, "disable_out_of_stock" => $disable_out_of_stock, 
						);
					}
	
					// check components for parent product
					if (is_array($components) && count($components) > 0) {
						// check for bundle components
						$parent_item_id = $item_id;
						$component_number = 0;

						$components_ids = array();
						$components_price = 0; $components_base_price = 0; $components_points_price = 0; $components_reward_points = 0; $components_reward_credits = 0;
						foreach ($components as $property_id => $component_values) {
							foreach ($component_values as $item_property_id => $component) {
								
								$property_type_id = $component["type_id"];
								$sub_item_id = $component["sub_item_id"];
								$sub_quantity = $component["quantity"];
								if ($sub_quantity < 1) { $sub_quantity = 1; }
								if ($property_type_id == 2) {
									$sql  = " SELECT i.item_id, i.item_code, i.manufacturer_code, i.user_id, i.item_type_id, i.supplier_id, i.item_name, i.short_description, i.full_description, ";
									$sql .= " i.downloadable, i.download_period, i.download_path, i.generate_serial, i.serial_period, i.activations_number, ";
									$sql .= " st_in.availability_time AS in_stock_availability , st_out.availability_time AS out_stock_availability, ";
									$sql .= " pr.quantity_action, i.packages_number, i.width, i.height, i.length, ";
									$sql .= " i.weight, i.actual_weight, i.shipping_cost, i.is_shipping_free, it.is_gift_voucher, it.is_user_voucher, ";
									$sql .= " i.shipping_modules_default, i.shipping_modules_ids, ";
									$sql .= " i.merchant_fee_type AS item_merchant_type, i.merchant_fee_amount AS item_merchant_amount, i.affiliate_commission_type AS item_affiliate_type, i.affiliate_commission_amount AS item_affiliate_amount, ";
									$sql .= " it.merchant_fee_type AS type_merchant_type, it.merchant_fee_amount AS type_merchant_amount, it.affiliate_commission_type AS type_affiliate_type, it.affiliate_commission_amount AS type_affiliate_amount, ";
									$sql .= " pr.property_name AS component_name, pr.".$additional_price_field." AS component_price, ";
									$sql .= " i.buying_price, i." . $price_field . ", i.is_sales, i." . $sales_field . ", i.tax_id, i.tax_free, ";
									$sql .= " i.is_points_price, i.points_price, i.reward_type, i.reward_amount, i.credit_reward_type, i.credit_reward_amount, ";
									$sql .= " it.reward_type AS type_bonus_reward, it.reward_amount AS type_bonus_amount, ";
									$sql .= " it.credit_reward_type AS type_credit_reward, it.credit_reward_amount AS type_credit_amount, ";
									$sql .= " i.stock_level, i.use_stock_level, i.hide_out_of_stock, i.disable_out_of_stock, sr.shipping_rule_id, sr.countries_all, ";
									$sql .= " i.is_recurring, i.recurring_price, i.recurring_period, i.recurring_interval, ";
									$sql .= " i.recurring_payments_total, i.recurring_start_date, i.recurring_end_date, ";
									$sql .= " i.tiny_image, i.tiny_image_alt, i.small_image, i.small_image_alt, i.big_image, i.big_image_alt, i.super_image ";
									$sql .= " FROM (((((" . $table_prefix . "items_properties pr ";								
									$sql .= " INNER JOIN  " . $table_prefix . "items i ON pr.sub_item_id=i.item_id)";
									$sql .= " LEFT JOIN " . $table_prefix . "item_types it ON i.item_type_id=it.item_type_id) ";
									$sql .= " LEFT JOIN " . $table_prefix . "shipping_times st_in ON i.shipping_in_stock=st_in.shipping_time_id) ";
									$sql .= " LEFT JOIN " . $table_prefix . "shipping_times st_out ON i.shipping_out_stock=st_out.shipping_time_id) ";
									$sql .= " LEFT JOIN " . $table_prefix . "shipping_rules sr ON i.shipping_rule_id=sr.shipping_rule_id) ";
									$sql .= " WHERE pr.property_id=" . $db->tosql($property_id, INTEGER);									
									//$sql .= " ORDER BY ip.user_type_id DESC ";
									$component_property_id = $property_id ."_0";
								} else {
									$sql  = " SELECT i.item_id, i.item_code, i.manufacturer_code, i.user_id, i.item_type_id, i.supplier_id, i.item_name, i.short_description, i.full_description, ";
									$sql .= " i.downloadable, i.download_period, i.download_path, i.generate_serial, i.serial_period, i.activations_number, ";
									$sql .= " st_in.availability_time AS in_stock_availability , st_out.availability_time AS out_stock_availability, ";
									$sql .= " i.packages_number, i.width, i.height, i.length, ";
									$sql .= " pr.quantity_action, i.weight, i.actual_weight, i.shipping_cost, i.is_shipping_free, it.is_gift_voucher, it.is_user_voucher, ";
									$sql .= " i.shipping_modules_default, i.shipping_modules_ids, ";
									$sql .= " i.merchant_fee_type AS item_merchant_type, i.merchant_fee_amount AS item_merchant_amount, i.affiliate_commission_type AS item_affiliate_type, i.affiliate_commission_amount AS item_affiliate_amount, ";
									$sql .= " it.merchant_fee_type AS type_merchant_type, it.merchant_fee_amount AS type_merchant_amount, it.affiliate_commission_type AS type_affiliate_type, it.affiliate_commission_amount AS type_affiliate_amount, ";
									$sql .= " ipv.property_value AS component_name, ipv.".$additional_price_field." AS component_price, ";
									$sql .= " i.buying_price, i." . $price_field . ", i.is_sales, i." . $sales_field . ", i.tax_id, i.tax_free, ";
									$sql .= " i.is_points_price, i.points_price, i.reward_type, i.reward_amount, i.credit_reward_type, i.credit_reward_amount, ";
									$sql .= " it.reward_type AS type_bonus_reward, it.reward_amount AS type_bonus_amount, ";
									$sql .= " it.credit_reward_type AS type_credit_reward, it.credit_reward_amount AS type_credit_amount, ";
									$sql .= " i.stock_level, i.use_stock_level, i.hide_out_of_stock, i.disable_out_of_stock, sr.shipping_rule_id, sr.countries_all, ";
									$sql .= " i.is_recurring, i.recurring_price, i.recurring_period, i.recurring_interval, ";
									$sql .= " i.recurring_payments_total, i.recurring_start_date, i.recurring_end_date, ";
									$sql .= " i.tiny_image, i.tiny_image_alt, i.small_image, i.small_image_alt, i.big_image, i.big_image_alt, i.super_image ";
									$sql .= " FROM (((((( " . $table_prefix . "items_properties_values ipv ";
									$sql .= " INNER JOIN " . $table_prefix . "items_properties pr ON pr.property_id=ipv.property_id)";
									$sql .= " INNER JOIN " . $table_prefix . "items i ON ipv.sub_item_id=i.item_id)";
									$sql .= " LEFT JOIN " . $table_prefix . "item_types it ON i.item_type_id=it.item_type_id) ";
									$sql .= " LEFT JOIN " . $table_prefix . "shipping_times st_in ON i.shipping_in_stock=st_in.shipping_time_id) ";
									$sql .= " LEFT JOIN " . $table_prefix . "shipping_times st_out ON i.shipping_out_stock=st_out.shipping_time_id) ";
									$sql .= " LEFT JOIN " . $table_prefix . "shipping_rules sr ON i.shipping_rule_id=sr.shipping_rule_id) ";
									$sql .= " WHERE ipv.item_property_id=" . $db->tosql($item_property_id, INTEGER);
									//$sql .= " ORDER BY ip.user_type_id DESC ";
									$component_property_id = $property_id ."_".$item_property_id;
								}
								$db->query($sql);
								if ($db->next_record()) {
									$component_number++;
									// price calculation
									$sub_item_id = $db->f("item_id");							
									
									$quantity_action = $db->f("quantity_action");
									$item_user_id = $db->f("user_id");
									$item_type_id = $db->f("item_type_id");
									$supplier_id = $db->f("supplier_id");
									$component_price = $db->f("component_price");
									$buying_price = doubleval($db->f("buying_price"));
									$item_price = $db->f($price_field);
									$is_sales = $db->f("is_sales");
									$sales_price = $db->f($sales_field);
									$coupons_ids = ""; $coupons_discount = ""; $coupons_applied = array();
									get_sales_price($item_price, $is_sales, $sales_price, $sub_item_id, $item_type_id, "", "", $coupons_ids, $coupons_discount, $coupons_applied, "coupon");
									if ($quantity_action == 2) {
										$component_quantity = $sub_quantity;
									} else {
										$component_quantity = $quantity * $sub_quantity;
									}
									
									$user_price          = ""; 
									$properties_discount = 0;
									$discount_applicable = 1;								
									$quantity_prices     = get_quantity_price($sub_item_id, $component_quantity);
									if (sizeof($quantity_prices)) {
										$user_price          = $quantity_prices[0];
										$properties_discount = $quantity_prices[1];
										$discount_applicable = $quantity_prices[2];
									}
									
									// points data
									$is_points_price = $db->f("is_points_price");
									$points_price = $db->f("points_price");
									$reward_type = $db->f("reward_type");
									$reward_amount = $db->f("reward_amount");
									$credit_reward_type = $db->f("credit_reward_type");
									$credit_reward_amount = $db->f("credit_reward_amount");
									if (!strlen($reward_type)) {
										$reward_type = $db->f("type_bonus_reward");
										$reward_amount = $db->f("type_bonus_amount");
									}
									if (!strlen($credit_reward_type)) {
										$credit_reward_type = $db->f("type_credit_reward");
										$credit_reward_amount = $db->f("type_credit_amount");
									}
	
									$prices = get_product_price($sub_item_id, $item_price, $buying_price, $is_sales, $sales_price, $user_price, $discount_applicable, $user_discount_type, $user_discount_amount);
									$base_price = $prices["base"];
									$real_price = $prices["real"];
									if (strlen($component_price)) {
										$price = $component_price;
									} else {
										$price = $base_price;
									}
	
									// re-calculate price in case if prices include some default tax rate
									$item_tax = get_tax_amount($tax_rates, $item_type_id, $price, 1, $item_tax_id, $item_tax_free, $item_tax_percent, $default_tax_rates);
									$item_real_tax = get_tax_amount($tax_rates, $item_type_id, $real_price, 1, $item_tax_id, $item_tax_free, $item_tax_percent, $default_tax_rates);
									//$item_buying_tax = get_tax_amount($tax_rates, $item_type_id, $buying_price, $item_tax_free, $item_tax_percent, $default_tax_rates);
	
									$item_code = $db->f("item_code");
									$manufacturer_code = $db->f("manufacturer_code");
									$item_name = $db->f("item_name");
									$component_name = $db->f("component_name");
									$downloadable = $db->f("downloadable");
									$download_period = $db->f("download_period");
									$download_path = $db->f("download_path");
									$generate_serial = $db->f("generate_serial");
									$serial_period = $db->f("serial_period");
									$activations_number = $db->f("activations_number");
									$is_gift_voucher = $db->f("is_gift_voucher");
									$is_user_voucher = $db->f("is_user_voucher");
									$stock_level = $db->f("stock_level");
									$use_stock_level = $db->f("use_stock_level");
									$hide_out_of_stock = $db->f("hide_out_of_stock");
									$disable_out_of_stock = $db->f("disable_out_of_stock");
									if ($stock_level > 0) {
										$availability_time = $db->f("in_stock_availability");
									} else {
										$availability_time = $db->f("out_stock_availability");
									}
									if ($availability_time > $max_availability_time) {
										$max_availability_time = $availability_time;
									}
									$shipping_rule_id = $db->f("shipping_rule_id");
									$countries_all = $db->f("countries_all");
									$packages_number = $db->f("packages_number");
									$weight = doubleval($db->f("weight"));
									$actual_weight = doubleval($db->f("actual_weight"));
									$width = $db->f("width");
									$height = $db->f("height");
									$length = $db->f("length");
									$shipping_cost = doubleval($db->f("shipping_cost"));
									$is_shipping_free = $db->f("is_shipping_free");
									if ($is_shipping_free) { $shipping_cost = 0; }
									$shipping_modules_default = $db->f("shipping_modules_default");
									$shipping_modules_ids = $db->f("shipping_modules_ids");

									$item_tax_id = $db->f("tax_id");
									$item_tax_free = $db->f("tax_free");
									if ($tax_free) { $item_tax_free = $tax_free; }
									$short_description = strip_tags($db->f("short_description"));
									$full_description = strip_tags($db->f("full_description"));
									// get commission fields
									$item_merchant_type = $db->f("item_merchant_type");
									$item_merchant_amount = $db->f("item_merchant_amount");
									$item_affiliate_type = $db->f("item_affiliate_type");
									$item_affiliate_amount = $db->f("item_affiliate_amount");
									if (!strlen($item_merchant_type)) {
										$item_merchant_type = $db->f("type_merchant_type");
										$item_merchant_amount = $db->f("type_merchant_amount");
									}
									if (!strlen($item_affiliate_type)) {
										$item_affiliate_type = $db->f("type_affiliate_type");
										$item_affiliate_amount = $db->f("type_affiliate_amount");
									}
									$is_recurring = $db->f("is_recurring");
									$recurring_items = ($is_recurring || $recurring_items);
									$recurring_price = $db->f("recurring_price");
									$recurring_period = $db->f("recurring_period");
									$recurring_interval = $db->f("recurring_interval");
									$recurring_payments_total = $db->f("recurring_payments_total");
									$recurring_start_date = $db->f("recurring_start_date", DATETIME);
									$recurring_end_date = $db->f("recurring_end_date", DATETIME);
	
									// item image
									$item_image = ""; $item_image_alt = ""; 
									if ($image_field) {
										$item_image = $db->f($image_field);	
										$item_image_alt = get_translation($db->f($image_alt_field));	
									}
									$big_image = $db->f("big_image");	
									$super_image = $db->f("super_image");	
									$selection_name = ""; $selection_order = 1;
									if (isset($item["PROPERTIES_INFO"][$property_id])) {
										$selection_name = $item["PROPERTIES_INFO"][$property_id]["NAME"];
										$selection_order = $item["PROPERTIES_INFO"][$property_id]["ORDER"];
									} 
	
									$cart_item_id = $cart_id."_".$component_property_id;
									$cart_items[$cart_item_id] = array(
										"parent_cart_id" => $cart_id, "top_order_item_id" => 0, "is_bundle" => 0,
										"item_id" => $sub_item_id, "id" => $sub_item_id, "product_id" => $sub_item_id, 
										"item_user_id" => $item_user_id, "item_type_id" => $item_type_id, 
										"supplier_id" => $supplier_id, "wishlist_item_id" => $wishlist_item_id,
										"parent_item_id" => $parent_item_id, "component_property_id" => $component_property_id,
										"selection_name" => $selection_name, "selection_order" => $selection_order, "component_name" => $component_name,
										"item_image" => $item_image, "item_image_alt" => $item_image_alt, 
										"big_image" => $big_image, "super_image" => $super_image, 
										"item_code" => $item_code, "manufacturer_code" => $manufacturer_code, 
										"price" => $price, 
										"quantity" => $component_quantity, "parent_quantity" => $quantity,
										"quantity_action" => $quantity_action, "sub_quantity" => $sub_quantity,
										"packages_number" => $packages_number, "width" => $width, "height" => $height, "length" => $length,
										"weight" => $weight, "actual_weight" => $actual_weight, "shipping_cost" => $shipping_cost, "is_shipping_free" => $is_shipping_free, 
										"countries_all" => $countries_all, "shipping_rule_id" => $shipping_rule_id,
										"shipping_modules_default" => $shipping_modules_default, "shipping_modules_ids" => $shipping_modules_ids,
										"tax_id" => $item_tax_id, "tax_free" => $item_tax_free, "tax_percent" => $item_tax_percent,
										"affiliate_type" => $item_affiliate_type, "affiliate_amount" => $item_affiliate_amount,
										"merchant_type" => $item_merchant_type, "merchant_amount" => $item_merchant_amount,
										"item_merchant_type" => $item_merchant_type, "item_merchant_amount" => $item_merchant_amount,
										"item_affiliate_type" => $item_affiliate_type, "item_affiliate_amount" => $item_affiliate_amount,
										"base_price" => $base_price, "real_price" => $real_price, "discount_amount" => 0,  
										"coupons" => "", "coupons_applied" => $coupons_applied, 
										"coupons_ids" => $coupons_ids, "coupons_discount" => $coupons_discount, 
										"discount_applicable" => $discount_applicable, "properties_discount" => $properties_discount,
										"is_points_price" => $is_points_price, "points_price" => $points_price, 
										"reward_type" => $reward_type, "reward_amount" => $reward_amount, 
										"credit_reward_type" => $credit_reward_type, "credit_reward_amount" => $credit_reward_amount, 
										"buying_price" => $buying_price, "item_name" => $item_name, "product_name" => $item_name,
										"product_title" => $item_name, "item_title" => $item_name, 
										"properties_info" => "", 
										"properties_html" => "", "properties_text" => "",
										"downloadable" => $downloadable, "downloads" => "", 
										"stock_level" => $stock_level, "availability_time" => $availability_time, 
										"min_quantity" => "", "max_quantity" => "", "quantity_increment" => "", 
										"short_description" => $short_description, "description" => $short_description, "full_description" => $full_description,
										"generate_serial" => $generate_serial, "serial_period" => $serial_period, "activations_number" => $activations_number,
										"is_gift_voucher" => $is_gift_voucher, "is_user_voucher" => $is_user_voucher,
										"is_recurring" => $is_recurring, "recurring_price" => $recurring_price, "recurring_period" => $recurring_period,
										"recurring_interval" => $recurring_interval, "recurring_payments_total" => $recurring_payments_total,
										"recurring_start_date" => $recurring_start_date, "recurring_end_date" => $recurring_end_date,
										"is_subscription" => 0, "is_account_subscription" => 0, "subscription_period" => "",
										"subscription_interval" => "", "subscription_suspend" => "",
									);
									// associate components with parent product
									$cart_items[$cart_id]["components"][] = $cart_item_id; 
									if (isset($cart_items[$cart_id]["components_price"])) {
										$cart_items[$cart_id]["components_price"] += ($price * $sub_quantity); 
										$cart_items[$cart_id]["components_base_price"] += ($base_price * $sub_quantity); 
									} else {
										$cart_items[$cart_id]["components_price"] = ($price * $sub_quantity); 
										$cart_items[$cart_id]["components_base_price"] = ($base_price * $sub_quantity); 
									}
									$components_ids[] = $cart_item_id;
	
									// update stock level information for subcomponents
									if (isset($stock_levels[$sub_item_id])) {
										$stock_levels[$sub_item_id]["quantity"] += ($quantity * $sub_quantity);
										$stock_levels[$sub_item_id]["stock_level"] = $stock_level;
									} else {
										$stock_levels[$sub_item_id] = array(
											"item_name" => $item_name, "quantity" => ($quantity * $sub_quantity), "stock_level" => $stock_level, 
											"use_stock_level" => $use_stock_level, "hide_out_of_stock" => $hide_out_of_stock, "disable_out_of_stock" => $disable_out_of_stock, 
										);
									}
								} else {
									// if some basket items were missed remove from the cart and move user to the basket page
									unset($shopping_cart[$cart_id]);
									set_session("shopping_cart", $shopping_cart);
									$location_url = get_custom_friendly_url("basket.php");
									if ($ajax) {
										echo json_encode(array("errors" => "Missed products", "location" => $location_url));
										exit;
									} else {
										header("Location: " . $location_url);
										exit;
									}
								}
							}
						}
					} // end components checks
				}
			}
		}

		// #2 - prepare items options, check delivery rules
		foreach ($cart_items as $id => $item) {
			$is_bundle = $item["is_bundle"];
			$shipping_rule_id = $item["shipping_rule_id"];
			$countries_all = $item["countries_all"];
			// check properties if there are any
			$parent_properties_info = isset($cart_items[$id]["parent_properties_info"]) ? $cart_items[$id]["parent_properties_info"] : "";
			$downloads = array(); $properties_info = array(); $options_code = ""; $options_manufacturer_code = "";
			$properties_values = ""; $properties_values_text = ""; $properties_values_html = "";
			$additional_price = 0; $additional_real_price = 0; $options_buying_price = 0; $additional_weight = 0; $additional_actual_weight = 0;

			if ($item["item_id"]) {
				$parent_cart_id = $item["parent_cart_id"];
				order_items_properties($id, $item, $parent_cart_id, $item["is_bundle"], $item["discount_applicable"], $item["properties_discount"], $parent_properties_info);
			}
			// re-calculate additional options price in case if prices include some default tax rate
			$options_tax = get_tax_amount($tax_rates, $item["item_type_id"], $additional_price, 1, $item["tax_id"], $item["tax_free"], $item_tax_percent, $default_tax_rates);
			$cart_items[$id]["options_price"] = $additional_price;
			$cart_items[$id]["options_buying_price"] = $options_buying_price;
			$cart_items[$id]["options_real_price"] = $additional_real_price;
			$cart_items[$id]["options_weight"] = $additional_weight;
			$cart_items[$id]["options_actual_weight"] = $additional_actual_weight;
			$cart_items[$id]["properties_html"] = $properties_values_html;
			$cart_items[$id]["properties_text"] = $properties_values_text;
			$cart_items[$id]["properties_info"] = $properties_info;
			$cart_items[$id]["downloads"] = $downloads;
			// add options code to the main product codes 
			$cart_items[$id]["item_code"] .= $options_code;
			$cart_items[$id]["manufacturer_code"] .= $options_manufacturer_code;

			if ($is_bundle && isset($item["components"])) {
				// reassign parent options to first subcomponent
				$component_cart_id = $item["components"][0];
				$cart_items[$component_cart_id]["parent_downloads"] = $downloads;
				$cart_items[$component_cart_id]["parent_properties_info"] = $properties_info;
			}
			// end of properties

			// check delivery rules
			if ($shipping_rule_id && !$countries_all) {
				$sql  = " SELECT shipping_rule_id FROM " . $table_prefix . "shipping_rules_countries ";
				$sql .= " WHERE shipping_rule_id=" . $db->tosql($shipping_rule_id, INTEGER);
				$sql .= " AND country_id=" . $db->tosql($country_id, INTEGER);
				$db->query($sql);
				if (!$db->next_record()) {
					$item_name = $item["item_name"];
					$delivery_errors .= str_replace("{product_name}", get_translation($item_name), PROD_RESTRICTED_DELIVERY_MSG) . "<br>";
				}
			} // end of delivery check 

		}

		// #3 - check items coupons for basic prices and calculate quantity for parent products
		$parent_quantity = 0; 
		foreach ($cart_items as $id => $item) {
			$parent_item_id = $item["parent_item_id"];
			if (!$parent_item_id) {
				$price = $item["price"];
				$quantity = $item["quantity"];
				$parent_quantity += $quantity;
				$item_coupons = isset($item["coupons"]) ? $item["coupons"] : "";
				$options_price = isset($item["options_price"]) ? $item["options_price"] : 0;
				$components = isset($item["components"]) ? $item["components"] : "";
				$components_price = isset($item["components_price"]) ? $item["components_price"] : 0;
				// calculate total product price with options and components
				$item_total_price = $price + $options_price + $components_price;
				$max_item_discount = $item_total_price;

				// show product coupons if available and total price greater than zero
				if (is_array($item_coupons) && $item_total_price)
				{
					foreach ($item_coupons as $coupon_id => $coupon_info)
					{
						$sql  = " SELECT c.* ";
						$sql .= " FROM ";
						if (isset($site_id)) {
							$sql .= "(";						
						}
						$sql .= $table_prefix . "coupons c ";
						if (isset($site_id)) {
							$sql .= " LEFT JOIN " . $table_prefix . "coupons_sites s ON s.coupon_id=c.coupon_id) ";
						}
						$sql .= " WHERE c.coupon_id=" . $db->tosql($coupon_id, INTEGER);
						if (isset($site_id)) {
							$sql .= " AND (c.sites_all = 1 OR s.site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
						} else {
							$sql .= " AND c.sites_all = 1 ";
						}
						$db->query($sql);
						if ($db->next_record()) {
							$is_active = $db->f("is_active");
							$coupon_code = $db->f("coupon_code");
							$coupon_title = $db->f("coupon_title");
							$discount_type = $db->f("discount_type");
							$coupon_discount_quantity = $db->f("discount_quantity");
							$coupon_discount = $db->f("discount_amount");
							$min_quantity = $db->f("min_quantity");
							$max_quantity = $db->f("max_quantity");
							$minimum_amount = $db->f("minimum_amount");
							$maximum_amount = $db->f("maximum_amount");
							$quantity_limit = $db->f("quantity_limit");
							$coupon_uses = $db->f("coupon_uses");

							// additional checks if coupon could be used
							// TODO: add cart totals checks
							if (!$is_active) {
								remove_coupon($coupon_id);
							} elseif ($quantity_limit > 0 && $coupon_uses >= $quantity_limit) {
								remove_coupon($coupon_id);
							} elseif ($item_total_price < $minimum_amount) {
								remove_coupon($coupon_id);
							} elseif ($maximum_amount && $item_total_price > $maximum_amount) {
								remove_coupon($coupon_id);
							} elseif ($quantity < $min_quantity) {
								remove_coupon($coupon_id);
							} elseif ($max_quantity && $quantity > $max_quantity) {
								remove_coupon($coupon_id);
							} else {
								if ($discount_type == 3) {
									$discount_amount = round(($item_total_price / 100) * $coupon_discount, 2);
								} else {
									$discount_amount = $coupon_discount;
								}
								if ($discount_amount > $max_item_discount) {
									$discount_amount = $max_item_discount;
								}
								$max_item_discount -= $discount_amount;

								if ($coupon_discount_quantity > 1) {
									$discount_number = intval($quantity / $coupon_discount_quantity) * $coupon_discount_quantity;
								} else {
									$discount_number = $quantity;
								}

								if ($discount_number != $quantity) {
									if ($discount_number) {
										$quantities_discounts[] = array(
											"COUPON_ID" => $coupon_id, "COUPON_CODE" => $coupon_code, "COUPON_TITLE" => $coupon_title, "ITEM_NAME" => $item_name, 
											"ITEM_TYPE_ID" => $item_type_id, "TAX_FREE" => $item_tax_free, 
											"DISCOUNT_NUMBER" => $discount_number, "DISCOUNT_PER_ITEM" => $discount_amount, "DISCOUNT_AMOUNT" => ($discount_amount * $discount_number));
									}
								} else {
									// calculate discount only for parent product
									$item_price = $price + $options_price;
									if ($item_total_price) {
										$item_discount = round(($item_price * $discount_amount) / ($item_total_price), 2);
									} else {
										$item_discount = 0;
									}
									$discount_amount_left = $discount_amount - $item_discount;
									//if ($item_discount) {
									// Show zero-value coupons
										if (strlen($cart_items[$id]["coupons_ids"])) { 
											$cart_items[$id]["coupons_ids"] .= ","; 
										}
										$cart_items[$id]["coupons_ids"] .= $coupon_id;
										$cart_items[$id]["coupons_discount"] += $item_discount;
										$cart_items[$id]["coupons_applied"][$coupon_id] = array(
											"id" => $coupon_id, "type" => $discount_type, "code" => $coupon_code, "title" => $coupon_title, "discount" => $item_discount);
									//}

									// calculate discounts for subcomponents if available
									if ($discount_amount_left && is_array($components) && sizeof($components) > 0) {
										for ($c = 0; $c < sizeof($components); $c++) {
											$cc_id = $components[$c];
											$component = $cart_items[$cc_id];
											$component_price = $component["price"];
											$sub_quantity = $component["sub_quantity"];
											if (($c + 1) == sizeof($components)) {
												$component_discount = round($discount_amount_left / $sub_quantity, 2);
											} else {
												$component_discount = round(($component_price * $discount_amount) / ($item_total_price * $sub_quantity), 2);
												$discount_amount_left -= ($component_discount * $sub_quantity);
											}
											if ($component_discount) {
												if (strlen($cart_items[$cc_id]["coupons_ids"])) { 
													$cart_items[$cc_id]["coupons_ids"] .= ","; 
												}
												$cart_items[$cc_id]["coupons_ids"] .= $coupon_id;
												$cart_items[$cc_id]["coupons_discount"] += $component_discount;
												$cart_items[$cc_id]["coupons_applied"][$coupon_id] = array(
													"id" => $coupon_id, "type" => $discount_type, "code" => $coupon_code, "title" => $coupon_title, "discount" => $component_discount);
											}
										}
									} // end subcomponents discount calculations
								} // end simple coupons applying
							}
						}
					} // end coupons checks for item
				}
			}
		} // end items checks

		// #4 - calculate points, credits and commissions 
		foreach ($cart_items as $id => $item) {
			$price = $item["price"];
			$quantity = $item["quantity"];
			$buying_price = $item["buying_price"];
			$options_price = $item["options_price"];
			$options_buying_price = $item["options_buying_price"];
			$coupons_discount = $item["coupons_discount"];
			$is_points_price = $item["is_points_price"];
			$wishlist_item_id = $item["wishlist_item_id"];
			if (!strlen($is_points_price)) {
				$is_points_price = $points_prices;
			}
			$points_price = $item["points_price"];

			// calculate points price
			if (!strlen($points_price)) {
				$points_price = ($price - $coupons_discount) * $points_conversion_rate;
			}
			$points_price += ($options_price * $points_conversion_rate);
			if (!$points_system || !$is_points_price || ($points_price * $quantity) > $points_balance) {
				$is_points_price = 0; $points_price = 0;
			}
			// get pay points parameter
			if ($points_system && $is_points_price) {
				$pay_points = get_param("pay_points_" . $id);
			} else {
				$pay_points = 0;
			}

			$cart_items[$id]["is_points_price"] = $is_points_price;
			$cart_items[$id]["points_price"] = $points_price;
			$cart_items[$id]["pay_points"] = $pay_points;

			// calculate reward points
			$reward_type = $item["reward_type"];
			$reward_amount= $item["reward_amount"];
			if ($points_system) {
				$reward_points = calculate_reward_points($reward_type, $reward_amount, $price, $buying_price, $points_conversion_rate, $points_decimals);
				if ($reward_type == 1 || $reward_type == 4) {
					$properties_reward_points = calculate_reward_points($reward_type, $reward_amount, $options_price, $options_buying_price, $points_conversion_rate, $points_decimals);
					$reward_points += $properties_reward_points;
				}
			} else {
				$reward_points = 0; $reward_type = 0;
			}
			$cart_items[$id]["reward_points"] = $reward_points;
			$cart_items[$id]["reward_type"] = $reward_type;
			// end reward points calculations

			// calculate reward credits
			$credit_reward_type = $item["credit_reward_type"];
			$credit_reward_amount = $item["credit_reward_amount"];
			if ($credit_system) {
				$reward_credits = calculate_reward_credits($credit_reward_type, $credit_reward_amount, $price, $buying_price);
				if ($credit_reward_type == 1 || $credit_reward_type == 4) {
					$properties_reward_credits = calculate_reward_credits($credit_reward_type, $credit_reward_amount, $options_price, $options_buying_price);
					$reward_credits += $properties_reward_credits;
				}
			} else {
				$reward_credits = 0; $credit_reward_type = 0;
			}	
			$cart_items[$id]["reward_credits"] = $reward_credits;
			$cart_items[$id]["credit_reward_type"] = $credit_reward_type;
			// end reward credits calculations

			// calculate commissions
			$item_user_id = $item["item_user_id"];
			$merchant_type = $item["merchant_type"];
			$merchant_amount = $item["merchant_amount"];
			$affiliate_type = $item["affiliate_type"];
			$affiliate_amount = $item["affiliate_amount"];

			$merchant_commission = get_merchant_commission($item_user_id, $price - $coupons_discount, $options_price, $buying_price + $options_buying_price, $merchant_type, $merchant_amount);
			$affiliate_commission = get_affiliate_commission($affiliate_user_id, $price - $coupons_discount, $options_price, $buying_price + $options_buying_price, $affiliate_type, $affiliate_amount);
			if ($merchant_commission && $affiliate_commission) {
				if ($affiliate_commission_deduct) {
					$merchant_fee = ($price - $coupons_discount + $options_price) - $merchant_commission;
					if ($merchant_fee < $affiliate_commission) {
						$merchant_commission -= ($affiliate_commission - $merchant_fee);
					}
				} else {
					$merchant_commission -= $affiliate_commission;
				}
			}

			$cart_items[$id]["merchant_commission"] = $merchant_commission;
			$cart_items[$id]["affiliate_commission"] = $affiliate_commission;
			// end commissions calculations
		}

		// list of fields to share bundle values
		$fields = array(
			"price" => 2,
			"buying_price" => 2,
			"coupons_discount" => 2,
			"points_price" => $points_decimals,
			"reward_points" => $points_decimals,
			"reward_credits" => $points_decimals,
			"affiliate_commission" => 2,
			"merchant_commission" => 2,
			"weight" => 4,
			"actual_weight" => 4,
			"real_price" => 2,
			"shipping_cost" => 2,
		);
		// #5 - share parent bundle values among it subcomponents and check quantity for top elements
		$components_items = $cart_items; // temporary table to obain original values for components
		foreach ($cart_items as $id => $item) {
			$is_bundle = $item["is_bundle"];
			$parent_cart_id = $item["parent_cart_id"];
			$components = isset($item["components"]) ? $item["components"] : "";
			if ($is_bundle) {
				$components_price = isset($item["components_price"]) ? $item["components_price"] : 0;
				$components_base_price = isset($item["components_base_price"]) ? $item["components_base_price"] : 0;
				if (is_array($components) && sizeof($components) > 0) {
					if ($components_price > 0) {
						$check_field = "price"; $total_check_value = $components_price;
					} else {
						$check_field = "base_price"; $total_check_value = $components_base_price;
					}
					// added options prices to main price
					$cart_items[$id]["price"] += $cart_items[$id]["options_price"];
					$cart_items[$id]["buying_price"] += $cart_items[$id]["options_buying_price"];
					$cart_items[$id]["weight"] += $cart_items[$id]["options_weight"];
					$cart_items[$id]["actual_weight"] += $cart_items[$id]["options_actual_weight"];
					$cart_items[$id]["real_price"] += $cart_items[$id]["options_real_price"];

					foreach($fields as $field_name => $decimals) {
						$parent_value = $cart_items[$id][$field_name];
						if ($parent_value) {
							$parent_value_left = $parent_value;
							for ($c = 0; $c < sizeof($components); $c++) {
								$cc_id = $components[$c];
								$component = $components_items[$cc_id];
								$sub_quantity = $component["sub_quantity"];
								$component_check_value = $component[$check_field];
								if (($c + 1) == sizeof($components)) {
									$parent_sub_value = round($parent_value_left / $sub_quantity, $decimals);
								} else {
									$parent_sub_value = round(($component_check_value * $parent_value) / ($total_check_value * $sub_quantity), $decimals);
									$parent_value_left -= ($parent_sub_value * $sub_quantity);
								}
								$cart_items[$cc_id][$field_name] += $parent_sub_value; // added parent product value to subcomponent
							}
						}
					}
				}
				// delete bundle product from the final list
				unset($cart_items[$id]);
			} else if ($subcomponents_show_type == 1 && !strlen($parent_cart_id)) {
				// share pay points value among subcomponents if they exists
				$pay_points = $item["pay_points"];
				if ($pay_points && is_array($components) && sizeof($components) > 0) {
					for ($c = 0; $c < sizeof($components); $c++) {
						$cc_id = $components[$c];
						$cart_items[$cc_id]["pay_points"] = $pay_points;
					}
				}
			}
		}

		// #6 - calculate products total values
		foreach ($cart_items as $id => $item) {
			$price = $item["price"];
			$real_price = $item["real_price"];
			$options_price = $item["options_price"];
			$buying_price = $item["buying_price"];
			$options_buying_price = $item["options_buying_price"];
			$options_real_price = $item["options_real_price"];

			$coupons_discount = $item["coupons_discount"];
			$quantity = $item["quantity"];
			$full_price = $price + $options_price - $coupons_discount;
			$full_buying_price = $buying_price + $options_buying_price;
			$full_real_price = $real_price + $options_real_price;
			$cart_items[$id]["full_price"] = $full_price;
			$cart_items[$id]["full_buying_price"] = $full_buying_price;
			$cart_items[$id]["full_real_price"] = $full_real_price;

			$item_total = $full_price * $quantity;
			$item_buying_total = $buying_price * $quantity;
			$item_type_id = $item["item_type_id"];
			$item_tax_id = $item["tax_id"];
			$item_tax_free = $item["tax_free"];
			$item_tax_percent = $item["tax_percent"];
			$pay_points = $item["pay_points"];
			$packages_number = $item["packages_number"];
			if ($packages_number <= 0) { $packages_number = 1; }
			$weight = $item["weight"];
			$actual_weight = $item["actual_weight"];
			$options_weight = $item["options_weight"];
			$options_actual_weight = $item["options_actual_weight"];
			$full_weight = $weight + $options_weight;
			$full_actual_weight = $actual_weight + $options_actual_weight;
			$cart_items[$id]["full_weight"] = $full_weight;
			$cart_items[$id]["full_actual_weight"] = $full_actual_weight;

			$downloadable = $item["downloadable"];
			$is_shipping_free = $item["is_shipping_free"];
			$shipping_cost = $item["shipping_cost"];

			// get taxes for products and add it to total values 
			$item_tax = get_tax_amount($tax_rates, $item_type_id, $full_price, 1, $item_tax_id, $item_tax_free, $item_tax_percent);
			$item_tax_total = get_tax_amount($tax_rates, $item_type_id, $item_total, 1, $item_tax_id, $item_tax_free, $item_tax_percent);
			$item_tax_values = get_tax_amount($tax_rates, $item_type_id, $full_price, 1, $item_tax_id, $item_tax_free, $item_tax_percent, "", 2);
			$item_tax_total_values = get_tax_amount($tax_rates, $item_type_id, $item_total, $quantity, $item_tax_id, $item_tax_free, $item_tax_percent, "", 2);
			$item_buying_tax = get_tax_amount($tax_rates, $item_type_id, $item_buying_total, $quantity, $item_tax_id, $item_tax_free, $item_tax_percent);
			if (!$pay_points) {
				add_tax_values($tax_rates, $item_tax_total_values, "products");
			}

			if ($tax_prices_type == 1) {
				$price_excl_tax = $full_price - $item_tax;
				$price_incl_tax = $full_price;
				$price_excl_tax_total = $item_total - $item_tax_total;
				$price_incl_tax_total = $item_total;
			} else {
				$price_excl_tax = $full_price;
				$price_incl_tax = $full_price + $item_tax;
				$price_excl_tax_total = $item_total;
				$price_incl_tax_total = $item_total + $item_tax_total;
			}
			$cart_items[$id]["price_excl_tax"] = $price_excl_tax;
			$cart_items[$id]["price_incl_tax"] = $price_incl_tax;
			$cart_items[$id]["price_excl_tax_total"] = $price_excl_tax_total;
			$cart_items[$id]["price_incl_tax_total"] = $price_incl_tax_total;
			$cart_items[$id]["item_tax"] = $item_tax;
			$cart_items[$id]["item_tax_values"] = $item_tax_values;
			$cart_items[$id]["item_tax_total"] = $item_tax_total;
			$cart_items[$id]["item_tax_total_values"] = $item_tax_total_values;
			$cart_items[$id]["item_taxes"] = $item_tax_total_values;

			$weight_total += (($weight + $options_weight) * $quantity);
			$actual_weight_total += (($actual_weight + $options_actual_weight) * $quantity);
			$total_quantity += $quantity;
			$goods_total_full += $item_total;
			$total_buying += $item_buying_total;
			$total_buying_tax += $item_buying_tax;
			if (!$pay_points) {
				$goods_total_excl_tax += $price_excl_tax_total;
				$goods_total_incl_tax += $price_incl_tax_total;
				$goods_tax_total += $item_tax_total;
				$goods_total += $item_total;
			}
			if (!$is_shipping_free && !$downloadable) {
				$shipping_quantity += $quantity;
				$shipping_items_total += ($shipping_cost * $quantity); 
				$shipping_weight += ($weight + $options_weight) * $quantity;
				$shipping_actual_weight += ($actual_weight + $options_actual_weight) * $quantity;
			}
		}

		// #6-shipping
		// if there are no individual products shipping cost and there are no items for shipping hide shipping step
		if ($shipping_items_total == 0 && $shipping_quantity == 0) {
			$steps["shipping"]["show"] = false;
		}

		// #7 - show information about quantities coupons and order coupons 
		$max_discount = $goods_total; $max_tax_discount = $goods_tax_total; $coupons_param = ""; $vouchers_param = "";
		// check quantities discount coupons
		if (is_array($quantities_discounts) && sizeof($quantities_discounts) > 0) {
			foreach ($quantities_discounts as $coupon_number => $coupon_info) {
				if (strlen($order_coupons_ids)) { $order_coupons_ids .= ","; }
				$order_coupons_ids .= $coupon_id;
				$order_coupons++;
				$coupon_id = $coupon_info["COUPON_ID"];
				$coupon_code = $coupon_info["COUPON_CODE"];
				$coupon_title = $coupon_info["COUPON_TITLE"];
				$item_name = $coupon_info["ITEM_NAME"];
				$discount_number = $coupon_info["DISCOUNT_NUMBER"];
				$discount_per_item = $coupon_info["DISCOUNT_PER_ITEM"];
				$discount_amount = $coupon_info["DISCOUNT_AMOUNT"];
				$item_type_id = $coupon_info["ITEM_TYPE_ID"];
				$item_tax_free = $coupon_info["TAX_FREE"];
				$max_discount -= $discount_amount;

				// check discount tax  TODO
				$discount_tax_amount = get_tax_amount($tax_rates, $item_type_id, $discount_amount, 1, 0, $item_tax_free, $item_tax_percent, $default_tax_rates);
				$max_tax_discount -= $discount_tax_amount;

				if ($tax_prices_type == 1) {
					$discount_amount_excl_tax = $discount_amount - $discount_tax_amount;
					$discount_amount_incl_tax = $discount_amount;
				} else {
					$discount_amount_excl_tax = $discount_amount;
					$discount_amount_incl_tax = $discount_amount + $discount_tax_amount;
				}

				$coupon_title .= " (". $item_name . ")";
				$coupon_title .= " - " . currency_format($discount_per_item) . " x " . $discount_number . "";

				$t->set_var("coupon_id", $coupon_id);
				$t->set_var("coupon_title", $coupon_title);
				$t->set_var("coupon_amount_excl_tax", "- " . currency_format($discount_amount_excl_tax));
				$t->set_var("coupon_tax", "- " . currency_format($discount_tax_amount));
				$t->set_var("coupon_amount_incl_tax", "- " . currency_format($discount_amount_incl_tax));

				if ($goods_colspan > 0) {
					$t->parse("coupon_name_column", false);
				}
				if ($item_price_total_column) {
					$t->parse("coupon_amount_excl_tax_column", false);
				}
				if ($item_tax_total_column) {
					$t->parse("coupon_tax_column", false);
				}
				if ($item_price_incl_tax_total_column) {
					$t->parse("coupon_amount_incl_tax_column", false);
				}

				$total_discount_excl_tax += $discount_amount_excl_tax; 
				$total_discount_incl_tax += $discount_amount_incl_tax;
				$total_discount_tax += $discount_tax_amount;
				$total_discount += $discount_amount;

				$order_coupons[] = array("coupon_id" => $coupon_id, "coupon_code" => $coupon_code, "coupon_title" => $coupon_title, 
					"discount_amount" => $discount_amount, "discount_tax_amount" => $discount_tax_amount);

				$t->parse("coupons", true);
				
				// generate html parameter with all coupons
				if ($coupons_param) { $coupons_param .= "&"; }
				$coupons_param .= "coupon_id=".$coupon_id;
				$coupons_param .= "&title=".prepare_js_value($coupon_title);
				$coupons_param .= "&type=2"; // use amount per order type
				$coupons_param .= "&amount=".prepare_js_value($discount_amount);
				$coupons_param .= "&tax_free=".intval($item_tax_free);
			}
		}

		// #8 - show order coupons and check vouchers
		if (is_array($coupons)) {
			foreach ($coupons as $coupon_id => $coupon_info) {
				$coupon_id = $coupon_info["COUPON_ID"];
				$sql  = " SELECT c.* FROM ";
				if (isset($site_id)) {
					$sql .= "(";
				}
				$sql .= $table_prefix . "coupons c";
				if (isset($site_id)) {
					$sql .= " LEFT JOIN " .  $table_prefix . "coupons_sites s ON s.coupon_id=c.coupon_id) ";
				}
				$sql .= " WHERE c.coupon_id=" . $db->tosql($coupon_id, INTEGER);
				if (isset($site_id)) {
					$sql .= " AND (c.sites_all=1 OR s.site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
				} else {
					$sql .= " AND c.sites_all=1 ";
				}
				$db->query($sql);
				if ($db->next_record()) {
					$is_active = $db->f("is_active");
					$coupon_code = $db->f("coupon_code");
					$coupon_title = $db->f("coupon_title");
					$discount_type = $db->f("discount_type");
					$coupon_discount = $db->f("discount_amount");
					$min_cart_quantity = $db->f("min_cart_quantity");
					$max_cart_quantity = $db->f("max_cart_quantity");
					$min_cart_cost = $db->f("min_cart_cost");
					$max_cart_cost = $db->f("max_cart_cost");

					$quantity_limit = $db->f("quantity_limit");
					$coupon_uses = $db->f("coupon_uses");
					$coupon_free_postage = $db->f("free_postage");
					$coupon_free_postage_all = $db->f("free_postage_all");
					$coupon_free_postage_ids = $db->f("free_postage_ids");
					$coupon_tax_free = $db->f("coupon_tax_free");
					$coupon_order_tax_free = $db->f("order_tax_free");

					// check if coupon override global order restrictions
					$coupon_order_min_gc = $db->f("order_min_goods_cost");
					if (strlen($coupon_order_min_gc)) { $order_min_goods_cost = $coupon_order_min_gc; }
					$coupon_order_max_gc = $db->f("order_max_goods_cost");
					if (strlen($coupon_order_max_gc)) { $order_max_goods_cost = $coupon_order_max_gc; }
					$coupon_order_min_wt = $db->f("order_min_weight");
					if (strlen($coupon_order_min_wt)) { $order_min_weight = $coupon_order_min_wt; }
					$coupon_order_max_wt = $db->f("order_max_weight");
					if (strlen($coupon_order_max_wt)) { $order_max_weight = $coupon_order_max_wt; }
					

					if (!$is_active) {
						remove_coupon($coupon_id);
					} elseif ($quantity_limit > 0 && $coupon_uses >= $quantity_limit) {
						remove_coupon($coupon_id);
					} elseif ($goods_total_full < $min_cart_cost) {
						remove_coupon($coupon_id);
					} elseif ($max_cart_cost && $goods_total_full > $max_cart_cost) {
						remove_coupon($coupon_id);
					} elseif ($parent_quantity < $min_cart_quantity) {
						remove_coupon($coupon_id);
					} elseif ($max_cart_quantity && $parent_quantity > $max_cart_quantity) {
						remove_coupon($coupon_id);
					} else {
						if ($discount_type == 5 || $discount_type == 8) {
							// add voucher to vouchers array to use later after all order calculations 
							$gift_vouchers[$coupon_id] = array(
								"code" => $coupon_code,
								"title" => $coupon_title,
								"max_amount" => $coupon_discount,
								"discount_type" => $discount_type,
							);
							// generate html parameter with all coupons
							if ($vouchers_param) { $vouchers_param .= "&"; }
							$vouchers_param .= "voucher_id=".$coupon_id;
							$vouchers_param .= "&title=".prepare_js_value($coupon_title);
							$vouchers_param .= "&max_amount=".prepare_js_value($coupon_discount);
						} else {
							// show coupon information if no errors occurred
							if ($coupon_free_postage) { $free_postage = true; }
							if ($coupon_free_postage_all) { $free_postage_all = true; }
							if (!$free_postage_all && $coupon_free_postage_ids) {
								$ids_values = explode(",", $coupon_free_postage_ids);
								foreach ($ids_values as $free_shipping_id)  {
									$free_postage_ids[$free_shipping_id] = true;
								}
							}
							if ($coupon_order_tax_free) { $tax_free = true; }
							if (strlen($order_coupons_ids)) { $order_coupons_ids .= ","; }
							$order_coupons_ids .= $coupon_id;
							if ($discount_type == 1) {
								$discount_amount = round(($goods_total / 100) * $coupon_discount, 2);
							} else {
								$discount_amount = $coupon_discount;
							}
							if ($discount_amount > $max_discount) {
								$discount_amount = $max_discount;
							}
							$max_discount -= $discount_amount;
							$discount_tax_amount = 0;
							if ($tax_available && !$coupon_tax_free) {
								$discount_tax_amount = round(($discount_amount * $goods_tax_total) / $goods_total, 2);
								if ($discount_tax_amount > $max_tax_discount) {
									$discount_tax_amount = $max_tax_discount;
								}
								$max_tax_discount -= $discount_tax_amount;
							}
							if ($tax_prices_type == 1) {
								$discount_amount_excl_tax = $discount_amount - $discount_tax_amount;
								$discount_amount_incl_tax = $discount_amount;
							} else {
								$discount_amount_excl_tax = $discount_amount;
								$discount_amount_incl_tax = $discount_amount + $discount_tax_amount;
							}
				  
							$t->set_var("coupon_id", $coupon_id);
							$t->set_var("coupon_title", $db->f("coupon_title"));
							if ($discount_amount_excl_tax) {
								$t->set_var("coupon_amount_excl_tax", "- " . currency_format($discount_amount_excl_tax));
								$t->set_var("coupon_tax", "- " . currency_format($discount_tax_amount));
								$t->set_var("coupon_amount_incl_tax", "- " . currency_format($discount_amount_incl_tax));
							} else {
								$t->set_var("coupon_amount_excl_tax", "");
								$t->set_var("coupon_tax", "");
								$t->set_var("coupon_amount_incl_tax", "");
							}
				  
							if ($goods_colspan > 0) {
								$t->parse("coupon_name_column", false);
							}
							if ($item_price_total_column) {
								$t->parse("coupon_amount_excl_tax_column", false);
							}
							if ($item_tax_total_column) {
								$t->parse("coupon_tax_column", false);
							}
							if ($item_price_incl_tax_total_column) {
								$t->parse("coupon_amount_incl_tax_column", false);
							}
				  
							$total_discount_excl_tax += $discount_amount_excl_tax; 
							$total_discount_incl_tax += $discount_amount_incl_tax;
							$total_discount_tax += $discount_tax_amount;
							$total_discount += $discount_amount;
				  
							$order_coupons[] = array("coupon_id" => $coupon_id, "coupon_code" => $coupon_code, "coupon_title" => $coupon_title, 
								"discount_amount" => $discount_amount, "discount_tax_amount" => $discount_tax_amount);
				  
							$t->parse("coupons", true);
				  
							// generate html parameter with all coupons
							if ($coupons_param) { $coupons_param .= "&"; }
							$coupons_param .= "coupon_id=".$coupon_id;
							$coupons_param .= "&title=".prepare_js_value($coupon_title);
							$coupons_param .= "&type=".prepare_js_value($discount_type); 
							$coupons_param .= "&amount=".prepare_js_value($coupon_discount);
							$coupons_param .= "&tax_free=".intval($coupon_tax_free);
						}
					}
				}
			}
		}
		$t->set_var("order_coupons", htmlspecialchars($coupons_param));
		$t->set_var("order_vouchers", htmlspecialchars($vouchers_param));

		// value for goods with applied discount
		$goods_value = $goods_total - $total_discount;
		$goods_tax_value = $goods_tax_total - $total_discount_tax;

		// #9 - recalculate commissions and other rewards values if global order discount available and calculate sum for points and credits
		$total_reward_points = 0; $total_reward_credits = 0; 					
		$total_merchants_commission = 0; $total_affiliate_commission = 0; // apply only if user pay with real money
		foreach ($cart_items as $id => $item) {
			$quantity = $item["quantity"];
			$pay_points = $item["pay_points"];
			$points_price = $item["points_price"];
			$affiliate_commission = $item["affiliate_commission"];
			$merchant_commission = $item["merchant_commission"];
			$reward_points = $item["reward_points"];
			$reward_credits = $item["reward_credits"];
			if ($total_discount) {
				$affiliate_commission = round($affiliate_commission * (1 - $total_discount / $goods_total), 2);
				$merchant_commission = round($merchant_commission * (1 - $total_discount / $goods_total), 2);
				$reward_points = round($reward_points * (1 - $total_discount / $goods_total), $points_decimals);
				$reward_credits = round($reward_credits * (1 - $total_discount / $goods_total), 2);
				$cart_items[$id]["affiliate_commission"] = $affiliate_commission;
				$cart_items[$id]["merchant_commission"] = $merchant_commission;
				$cart_items[$id]["reward_points"] = $reward_points;
				$cart_items[$id]["reward_credits"] = $reward_credits;
			}
			if ($pay_points) { 
				$goods_points_amount += $points_price * $quantity;
				if ($points_for_points) {
					$total_reward_points += $reward_points * $quantity;
				}
				if ($credits_for_points) {
					$total_reward_credits += $reward_credits * $quantity;
				}
			} else {
				$total_reward_points += $reward_points * $quantity;
				$total_reward_credits += $reward_credits * $quantity;
				$total_merchants_commission += ($merchant_commission * $quantity);
				$total_affiliate_commission += ($affiliate_commission * $quantity);
			}
		}		

		// #10 - parse order items in one place
		$ordinal_number = 0;		
		$order_items = ""; // generate html parameter
		foreach ($cart_items as $cart_item_id => $cart_item) {

			$sub_item_id = $cart_item["item_id"];
			$parent_cart_id = $cart_item["parent_cart_id"];
			$wishlist_item_id = $cart_item["wishlist_item_id"];
			$item_user_id = $cart_item["item_user_id"];
			$item_type_id = $cart_item["item_type_id"];
			$parent_item_id = $cart_item["parent_item_id"];
			$item_name_initial = $cart_item["item_name"];
			$item_name = get_translation($item_name_initial);
			$item_tax_id = $cart_item["tax_id"];
			$item_tax_values = $cart_item["item_tax_values"];
			$item_tax_total_values = $cart_item["item_tax_total_values"];

			$item_tax_free = $cart_item["tax_free"];
 			$quantity = $cart_item["quantity"];
			$price = $cart_item["full_price"];
			$item_total = $price * $quantity;
			// data to generate quantity control
 			$min_quantity = get_setting_value($cart_item, "min_quantity");
 			$max_quantity = get_setting_value($cart_item, "max_quantity");
 			$quantity_increment = get_setting_value($cart_item, "quantity_increment");
			if (!$quantity_increment) { $quantity_increment = 1; }
			if (!$min_quantity) { $min_quantity = $quantity_increment; }

			// get points data
			$is_points_price = $cart_item["is_points_price"];
			$points_price = $cart_item["points_price"];
			$pay_points = $cart_item["pay_points"];

			// generate html parameter with all order items 
			if ($order_items) { $order_items.= "&"; }
			$order_items .= "cart_item_id=".prepare_js_value($cart_item_id);
			$order_items .= "&item_id=".$sub_item_id;
			$order_items .= "&parent_cart_id=".prepare_js_value($parent_cart_id);
			$order_items .= "&item_type_id=".prepare_js_value($item_type_id);
			$order_items .= "&name=".prepare_js_value($item_name);
			$order_items .= "&tax_id=".intval($item_tax_id);
			$order_items .= "&tax_free=".intval($item_tax_free);
			$order_items .= "&price=".prepare_js_value($price);
			$order_items .= "&quantity=".prepare_js_value($quantity);
			$order_items .= "&points_price=".prepare_js_value($points_price);
			$order_items .= "&subcomponents_show_type=".intval($subcomponents_show_type);

			if ($subcomponents_show_type == 1 && $parent_item_id && strlen($parent_cart_id) && isset($cart_items[$parent_cart_id])) {
				// component already shown with parent product
				continue;
			}

			$ordinal_number++;
			//$component_property_id = $cart_item["component_property_id"];
			$item_code = $cart_item["item_code"];
			$manufacturer_code = $cart_item["manufacturer_code"];
			$short_description = get_translation($cart_item["short_description"]);
			$item_image = $cart_item["item_image"];
			$item_image_alt = $cart_item["item_image_alt"];

			$price_excl_tax = $cart_item["price_excl_tax"];
			$price_incl_tax = $cart_item["price_incl_tax"];
			$price_excl_tax_total = $cart_item["price_excl_tax_total"];
			$price_incl_tax_total = $cart_item["price_incl_tax_total"];

			$item_tax_percent = $cart_item["tax_percent"];
			$item_tax = $cart_item["item_tax"];
			$item_tax_total = $cart_item["item_tax_total"];

			$buying_price = $cart_item["buying_price"];
			$weight = $cart_item["weight"];
			$actual_weight = $cart_item["actual_weight"];

			$coupons_applied = isset($cart_item["coupons_applied"]) ? $cart_item["coupons_applied"] : "";
			$properties_html = $cart_item["properties_html"];
			$properties_text = $cart_item["properties_text"];

			// points & credits fields
			$pay_points = $cart_item["pay_points"];
			$points_price = $cart_item["points_price"];
			$reward_points = $cart_item["reward_points"];
			$reward_credits = $cart_item["reward_credits"];

			$components = isset($cart_item["components"]) ? $cart_item["components"] : "";
			if ($subcomponents_show_type == 1 && is_array($components) && sizeof($components) > 0) {
				$t->set_var("components", "");
				for ($c = 0; $c < sizeof($components); $c++) {
					$t->set_var("component_codes", "");
					$t->set_var("component_item_code_block", "");
					$t->set_var("component_man_code_block", "");
					$cc_id = $components[$c];
					$component = $cart_items[$cc_id];
					$component_id = $component["item_id"];
					if ($subcomponents_show_type == 1) {
						$component_name = get_translation($component["component_name"]);
					} else {
						$component_name = get_translation($component["item_name"]);	
					}
					$component_price = $component["full_price"];
					$component_quantity = $component["quantity"];
					$component_sub_quantity = $component["sub_quantity"];
					$quantity_action = isset($component["quantity_action"]) ? $component["quantity_action"] : 1;
					$parent_quantity = isset($component["parent_quantity"]) ? $component["parent_quantity"] : $component_quantity;
					$component_item_code = $component["item_code"];
					$component_manufacturer_code = $component["manufacturer_code"];
					$selection_name = "";
					if (isset($component["selection_name"]) && $component["selection_name"]) {
						$selection_name = $component["selection_name"] . ": ";
					}
					// add coupons to parent product
					$component_coupons = isset($component["coupons_applied"]) ? $component["coupons_applied"] : "";
					if (is_array($component_coupons)) {
						foreach($component_coupons as $coupon_id => $coupon_info) {
							if (isset($coupons_applied[$coupon_id])) {
								$coupons_applied[$coupon_id]["discount"] += $coupon_info["discount"];
							} else {
								$coupons_applied[$coupon_id] = $coupon_info;
							}
						}
					}

					$t->set_var("component_order_item_id", $cc_id);
					$t->set_var("component_quantity", $component_quantity);
					$t->set_var("selection_name", $selection_name);
					$t->set_var("component_name", $component_name);
					if ($component_price > 0) {
						$t->set_var("component_price", $option_positive_price_right . currency_format($component_price*$component_quantity) . $option_positive_price_left);
					} elseif ($component_price < 0) {
						$t->set_var("component_price", $option_negative_price_right . currency_format(abs($component_price*$component_quantity)) . $option_negative_price_left);
					} else {
						$t->set_var("component_price", "");
					}
					if (($show_item_code && strlen($component_item_code)) || ($show_manufacturer_code && strlen($component_manufacturer_code))) {
						if ($show_item_code && strlen($component_item_code)) {
							$t->set_var("component_item_code", $component_item_code);
							$t->sparse("component_item_code_block", false);
						}
						if ($show_manufacturer_code && strlen($component_manufacturer_code)) {
							$t->set_var("component_manufacturer_code", $component_manufacturer_code);
							$t->sparse("component_man_code_block", false);
						}
						$t->sparse("component_codes", false);
					}

					$component_image = $component["super_image"];
					$image_type = 4;
					if (!$component_image) { 
						$component_image = $component["big_image"];
						$image_type = 3;
					}
					if ($component_image) {
						$component_icon = product_image_icon($component_id, $component_name, $component_image, $image_type);
					} else {
						$component_icon = "";
					}
					$t->set_var("component_icon", $component_icon);

					// get tax values for component and added them to parent product information
					$component_tax_values = $component["item_tax_values"];
					$component_tax_total_values = $component["item_tax_total_values"];
					foreach ($component_tax_total_values as $tax_id => $tax_info) {
						if (!isset($item_tax_values[$tax_id])) {
							$item_tax_values[$tax_id] = $component_tax_values[$tax_id];
							$item_tax_total_values[$tax_id] = $component_tax_total_values[$tax_id];
							$item_tax_values[$tax_id]["tax_amount"] = 0;     	
							$item_tax_total_values[$tax_id]["tax_amount"] = 0;
						}
						$item_tax_values[$tax_id]["tax_amount"] += $component_tax_values[$tax_id]["tax_amount"] * $component_sub_quantity;
						$item_tax_total_values[$tax_id]["tax_amount"] += $component_tax_total_values[$tax_id]["tax_amount"];
					}

					if ($quantity_action == 2) {
						$price += ($component["full_price"] * $component_sub_quantity/ $parent_quantity);
						$price_excl_tax += ($component["price_excl_tax"] * $component_sub_quantity/ $parent_quantity);
						$item_tax += ($component["item_tax"] * $component_sub_quantity/ $parent_quantity);
						$price_incl_tax += ($component["price_incl_tax"] * $component_sub_quantity/ $parent_quantity);
						$price_excl_tax_total += ($component["price_excl_tax_total"] );
						$item_tax_total += ($component["item_tax_total"] );
						$price_incl_tax_total += ($component["price_incl_tax_total"] );
				  
						$points_price += ($component["points_price"] * $component_sub_quantity/ $parent_quantity);
						$reward_points += ($component["reward_points"] * $component_sub_quantity / $parent_quantity);
						$reward_credits += ($component["reward_credits"] * $component_sub_quantity / $parent_quantity);
					} else {
						$price += ($component["full_price"] * $component_sub_quantity);
						$price_excl_tax += ($component["price_excl_tax"] * $component_sub_quantity);
						$item_tax += ($component["item_tax"] * $component_sub_quantity);
						$price_incl_tax += ($component["price_incl_tax"] * $component_sub_quantity);
						$price_excl_tax_total += ($component["price_excl_tax_total"] );
						$item_tax_total += ($component["item_tax_total"] );
						$price_incl_tax_total += ($component["price_incl_tax_total"] );
				  
						$points_price += ($component["points_price"] * $component_sub_quantity);
						$reward_points += ($component["reward_points"] * $component_sub_quantity);
						$reward_credits += ($component["reward_credits"] * $component_sub_quantity);
					}
					$item_total = $price * $quantity;

					$t->parse("components", true);
				}
				$t->parse("components_block", false);
			} else {
				$t->set_var("components_block", "");
			}
			// new-spec begin
			show_item_features($sub_item_id, "checkout");
			// new-spec end

			// generate products description in text format
			$item_text  = $item_name;
			if (strlen($properties_text)) {
				$item_text .= " (" .$properties_text. ")";
			}
			$item_text .= " " . PROD_QTY_COLUMN . ":" . $quantity . " " . currency_format($item_total);
			$items_text .= $item_text . $eol;

			$coupons_html = "";
			$t->set_var("item_coupons", "");
			if (is_array($coupons_applied)) {
				foreach($coupons_applied as $coupon_id => $coupon_info) {
					$discount_type = $coupon_info["type"];
					if ($discount_type != 6 && $discount_type != 7) {
						$coupons_html .= "<br>" . $coupon_info["title"] . " (- " . currency_format($coupon_info["discount"]) . ")";
						$t->set_var("coupon_id", $coupon_id);
						$t->set_var("coupon_title", $coupon_info["title"]);
						$t->set_var("discount_amount", "-".currency_format($coupon_info["discount"]));
						$t->parse("item_coupons", true);
					}
				}
			}

			$t->set_var("cart_id", $cart_item_id);
			$t->set_var("cart_item_id", $cart_item_id);
			$t->set_var("ordinal_number", $ordinal_number);
			$t->set_var("item_name", $item_name);
			$t->set_var("item_name_strip", htmlspecialchars(strip_tags($item_name)));
			$t->set_var("short_description", $short_description);
			$t->set_var("coupons_list", $coupons_html);
			$t->set_var("properties_values", $properties_html);

			// parse quantity control
			$t->set_var("quantity", htmlspecialchars($quantity));
			$t->set_var("quantity_select", "");
			$t->set_var("quantity_textbox", "");
			$t->set_var("quantity_label", "");
			if ($parent_cart_id) {
				$t->sparse("quantity_label", false);
			} else {
				if ($quantity_control_checkout == "LISTBOX") {
					$increment_limit = intval($quantity / $quantity_increment) + 8;
					$show_max_quantity = $min_quantity + ($quantity_increment * $increment_limit);
					if ($max_quantity > 0 && $show_max_quantity > $max_quantity) {
						$show_max_quantity = $max_quantity;
					}
					if (($disable_out_of_stock || $hide_out_of_stock) && $show_max_quantity > $stock_level && $use_stock_level) {
						$show_max_quantity = $stock_level;
					}
					// load data for listbox
					$t->set_var("quantity_options", "");
					for ($i = $min_quantity; $i <= $show_max_quantity; $i = $i + $quantity_increment) {
						$quantity_selected = ($i == $quantity) ? " selected " : "";
						$t->set_var("quantity_selected", $quantity_selected);
						$t->set_var("quantity_value", $i);
						$t->set_var("quantity_description", $i);
						$t->sparse("quantity_options", true);
						$quantity_description = $i;
					}
					$t->sparse("quantity_select", false);
				} elseif ($quantity_control_checkout == "TEXTBOX") {
					$t->sparse("quantity_textbox", false);
				} else {
					$t->sparse("quantity_label", false);
				}
			}


			// show tax below product if such option set
			$t->set_var("item_taxes", "");
			foreach ($item_tax_total_values as $tax_id => $tax_info) {
				$show_type = $tax_info["show_type"];
				if ($show_type & 2) {
					$t->set_var("tax_name", $tax_info["tax_name"]);
					$t->set_var("tax_amount", currency_format($item_tax_values[$tax_id]["tax_amount"]));
					$t->set_var("tax_amount_total", currency_format($tax_info["tax_amount"]));
					$t->parse("item_taxes", true);
				}
			}

			// item image
			$image_exists = false;
			if ($image_field) {
				if (!strlen($item_image)) {
					$item_image = $product_no_image;
				} else {
					$image_exists = true;
				}
			}

			// item image display
			if ($item_image) {
				if (preg_match("/^http\:\/\//", $item_image)) {
					$image_size = "";
				} else {
					$image_size = @getimagesize($item_image);
					if ($image_exists && ($watermark || $restrict_products_images)) {
						$item_image = "image_show.php?item_id=".$sub_item_id."&type=".$image_type_name."&vc=".md5($item_image);
					}
				}
				if (!strlen($item_image_alt)) { $item_image_alt = $item_name; }
				$t->set_var("alt", htmlspecialchars($item_image_alt));
				$t->set_var("src", htmlspecialchars($item_image));
				if (is_array($image_size)) {
					$t->set_var("width", "width=\"" . $image_size[0] . "\"");
					$t->set_var("height", "height=\"" . $image_size[1] . "\"");
				} else {
					$t->set_var("width", "");
					$t->set_var("height", "");
				}
					
				$t->sparse("image_preview", false);
			} else {
				$t->set_var("image_preview", "");
			}	

			// show product code
			$t->set_var("item_code", $item_code);
			$t->set_var("manufacturer_code", $manufacturer_code);
			if ($show_item_code && $item_code) {
				$t->sparse("item_code_block", false);
			} else {
				$t->set_var("item_code_block", "");
			}
			if ($show_manufacturer_code && $manufacturer_code) {
				$t->sparse("manufacturer_code_block", false);
			} else {
				$t->set_var("manufacturer_code_block", "");
			}							

			// show points price
			if ($points_system && $is_points_price) {
				if ($pay_points) {
					$t->set_var("pay_points_checked", "checked");
				} else {
					$t->set_var("pay_points_checked", "");
				}
				$t->set_var("points_price", number_format($points_price * $quantity, $points_decimals));
				$t->parse("points_price_block", false);
			} else {
				$t->set_var("points_price_block", "");
			}
			
			// show reward points
			$t->set_var("reward_points_block", "");
			if ($points_system && $reward_type && $reward_points_checkout) {
				$t->set_var("reward_points", number_format($reward_points * $quantity, $points_decimals));
				$t->parse("reward_points_block", false);
			}
			// show reward credits
			$t->set_var("reward_credits_block", "");
			if ($credit_system && $credit_reward_type) {
				if ($reward_credits_checkout && ($reward_credits_users == 0 || ($reward_credits_users == 1 && $user_id))) {
					$t->set_var("reward_credits", currency_format($reward_credits *  $quantity));
					$t->parse("reward_credits_block", false);
				}
			}

			// show prices
			$t->set_var("price_excl_tax", currency_format($price_excl_tax));
			$t->set_var("price_incl_tax", currency_format($price_incl_tax));
			$t->set_var("price_excl_tax_total", currency_format($price_excl_tax_total));
			$t->set_var("price_incl_tax_total", currency_format($price_incl_tax_total));

			// show tax information if column option selected
			$show_percentage = 0; $show_tax = 0; $show_tax_total = 0;
			foreach ($item_tax_values as $tax_id => $tax) {
				$show_type = $tax["show_type"];
				if ($show_type&1) {
					$show_percentage += $tax["tax_percent"];
					$show_tax += $tax["tax_amount"];
					$show_tax_total += $item_tax_total_values[$tax_id]["tax_amount"];
				}
			}
			$t->set_var("item_tax_percent",  $show_percentage . "%");
			$t->set_var("item_tax", currency_format($show_tax));
			$t->set_var("item_tax_total", currency_format($show_tax_total));
			if ($parent_cart_id) {
				$t->set_var("item_remove_cell", "");
			} else {
				$t->set_var("cart_item_id", $cart_item_id);
				$t->sparse("item_remove_cell", false);
			}



			parse_cart_columns($item_name_column, $item_price_column, $item_tax_percent_column, $item_tax_column, $item_price_incl_tax_column, $item_quantity_column, $item_price_total_column, $item_tax_total_column, $item_price_incl_tax_total_column, $item_image_column, $ordinal_number_column);
			$t->parse("items", true);
		}
		$t->set_var("cart_ids", implode(",", $cart_ids));

		// show total reward credits
		if ($credit_system && $reward_credits_checkout && $total_reward_credits && ($reward_credits_users == 0 || ($reward_credits_users == 1 && $user_id))) {
			$t->set_var("reward_credits_total", currency_format($total_reward_credits));
			$t->sparse("reward_credits_total_block", false);
		}
		// show total reward points 
		if ($points_system && $reward_points_checkout && $total_reward_points) {
			$t->set_var("reward_points_total", number_format($total_reward_points, $points_decimals));
			$t->sparse("reward_points_total_block", false);
		}

		if ($is_update) {
			set_session("shopping_cart", $shopping_cart);
		}
		$t->set_var("properties_ids", $properties_ids);

		$t->set_var("total_quantity", $total_quantity);
		$variables["total_quantity"] = $total_quantity;
		$t->set_var("total_items", $total_items);
		$variables["total_items"] = $total_items;

		$t->set_var("goods_value", number_format($goods_value,2));

		$t->set_var("goods_total_excl_tax", currency_format($goods_total_excl_tax));
		$t->set_var("goods_tax_total", currency_format($goods_tax_total));
		$t->set_var("goods_total_incl_tax", currency_format($goods_total_incl_tax));

		if ($item_quantity_column) {
			$t->parse("goods_total_quantity_column", false);
		}
		if ($item_price_total_column) {
			$t->parse("goods_total_excl_tax_column", false);
		}
		if ($item_tax_total_column) {
			$t->parse("goods_tax_total_column", false);
		}
		if ($item_price_incl_tax_total_column) {
			$t->parse("goods_total_incl_tax_column", false);
		}
		if ($goods_colspan > 0) {
			$t->parse("goods_name_column", false);
		}

		$items_text .= GOODS_TOTAL_MSG . ": " . currency_format($goods_total) . $eol;

		if ($total_discount > 0) {
			$items_text .= TOTAL_DISCOUNT_MSG . ": -" . currency_format($total_discount) . $eol;
			$items_text .= GOODS_WITH_DISCOUNT_MSG. ": " . currency_format(($goods_total - $total_discount)) . $eol;
			$t->set_var("total_discount_excl_tax", "- " . currency_format($total_discount_excl_tax));
			$t->set_var("total_discount_tax", "- " . currency_format($total_discount_tax));
			$t->set_var("total_discount_incl_tax", "- " . currency_format($total_discount_incl_tax));
			$t->set_var("discounted_amount_excl_tax", currency_format(($goods_total_excl_tax - $total_discount_excl_tax)));
			$t->set_var("discounted_tax_amount", currency_format(($goods_tax_total - $total_discount_tax)));
			$t->set_var("discounted_amount_incl_tax", currency_format(($goods_total_incl_tax - $total_discount_incl_tax)));
			if ($goods_colspan > 0) {
				$t->parse("total_discount_name_column", false);
				$t->parse("discounted_name_column", false);
			}
			if ($item_price_total_column) {
				$t->parse("total_discount_amount_excl_tax_column", false);
				$t->parse("discounted_amount_excl_tax_column", false);
			}
			if ($item_tax_total_column) {
				$t->parse("total_discount_tax_column", false);
				$t->parse("discounted_tax_column", false);
			}
			if ($item_price_incl_tax_total_column) {
				$t->parse("total_discount_amount_incl_tax_column", false);
				$t->parse("discounted_amount_incl_tax_column", false);
			}

			$t->parse("discount", false);
		} else {
			$t->set_var("discount", "");
		}
	} 
	if (!$user_order_id) {
		$t->set_var("order_items", $order_items);
	}

	// group taxes by percentage value
	$items_taxes = array();
	foreach ($cart_items as $ci => $cart_item) {
		$item_taxes = $cart_item["item_taxes"];
		$price = $cart_item["full_price"];
		$item_total = $price * $quantity;
		if (is_array($item_taxes) && sizeof($item_taxes) > 0) {
			foreach ($item_taxes as $tax_id => $tax_values) {
				$item_tax_percent	= $tax_values["tax_percent"];
				$item_tax_amount = $tax_values["tax_amount"];
				if (strlen($item_tax_percent)) {
					$item_tax_text = str_replace(".", "_", strval(round($item_tax_percent, 4)));
					if (isset($items_taxes[$item_tax_text])) {
						$items_taxes[$item_tax_text]["goods_total"] += $item_total;
						$items_taxes[$item_tax_text]["goods_tax"] += $item_tax_amount;
					} else {
						$items_taxes[$item_tax_text] = array(
							"goods_total" => $item_total, "goods_tax" => $item_tax_amount, "tax_percent" => $item_tax_percent,
						);
					}
				}
			}
		} else {
			if (isset($items_taxes["0"])) {
				$items_taxes["0"]["goods_total"] += $item_total;
			} else {
				$items_taxes["0"] = array("goods_total" => $item_total, "goods_tax" => 0, "tax_percent" => 0);
			}
		}
	}

	foreach ($items_taxes as $items_tax_text => $items_tax_data) {
		$t->set_var("goods_total_" . $items_tax_text, currency_format($items_tax_data["goods_total"]));
		$t->set_var("goods_tax_total_" . $items_tax_text, currency_format($items_tax_data["goods_tax"]));
		$t->set_var("goods_with_tax_total_" . $items_tax_text, currency_format(($items_tax_data["goods_total"] + $items_tax_data["goods_tax"])));
	}

	// check stock level restrictions for live order only
	if (!$cc_order) {
		foreach ($stock_levels as $item_id => $item_info) {
			$item_name = $item_info["item_name"];
			$quantity = $item_info["quantity"];
			$stock_level = $item_info["stock_level"];
			$use_stock_level = $item_info["use_stock_level"];
			$hide_out_of_stock = $item_info["hide_out_of_stock"];
			$disable_out_of_stock = $item_info["disable_out_of_stock"];
  
			if (($disable_out_of_stock || $hide_out_of_stock) && $quantity > $stock_level) {
				$stock_error = str_replace("{limit_quantity}", $stock_level, PRODUCT_LIMIT_MSG);
				$stock_error = str_replace("{product_name}", get_translation($item_name), $stock_error);
				$sc_errors .= $stock_error . "<br>";
			}
		}
	}

	// sum of options stock levels
	foreach ($cart_items as $id => $cart_item) {
		$item_name = $cart_item["item_name"]; 
		$quantity = $cart_item["quantity"]; 
		$properties_info = $cart_item["properties_info"]; 
		if (is_array($properties_info) && sizeof($properties_info) > 0) {
			for ($pi = 0; $pi < sizeof($properties_info); $pi++) {
				list($property_id, $control_type, $property_name, $hide_name, $property_value, $pr_add_price, $pr_add_weight, $pr_actual_weight, $pr_values, $property_order, $length_units) = $properties_info[$pi];
				if ($control_type != "WIDTH_HEIGHT") {
					for ($pv = 0; $pv < sizeof($pr_values); $pv++) {
						list($item_property_id, $pr_value, $pr_value_text, $pr_use_stock, $pr_hide_out_stock, $pr_stock_level) = $pr_values[$pv];
						if ($pr_hide_out_stock) {
							if (isset($options_stock_levels[$item_property_id])) {
								$options_stock_levels[$item_property_id]["quantity"] += $quantity;
								$options_stock_levels[$item_property_id]["stock_level"] = $pr_stock_level;
							} else {
								$options_stock_levels[$item_property_id] = array(
									"item_name" => $item_name, "property_name" => $property_name, "property_value" => $pr_value,
									"quantity" => $quantity, "stock_level" => $pr_stock_level,  "hide_out_of_stock" => $pr_hide_out_stock,
								);
							}
						}
					}
				}
			}
		}
	}

	// check options stock level restrictions for live order only
	if (!$cc_order) {
		foreach ($options_stock_levels as $item_property_id => $option_info) {
			$item_name = get_translation($option_info["item_name"]);
			$property_name = $option_info["property_name"];
			$property_value = $option_info["property_value"];
			$quantity = $option_info["quantity"];
			$stock_level = $option_info["stock_level"];
			$hide_out_of_stock = $option_info["hide_out_of_stock"];
			if ($hide_out_of_stock && $quantity > $stock_level) {
				$limit_product = get_translation($item_name);
				$limit_product .= " (" . get_translation($property_name) . ": " . get_translation($property_value) . ")";
				$limit_error = str_replace("{limit_quantity}", $stock_level, PRODUCT_LIMIT_MSG);
				$limit_error = str_replace("{product_name}", $limit_product, $limit_error);
				$sc_errors .= $limit_error . "<br>";
			}
		}
	}

	// check order restrictions
	if ($user_order_id) {
		// for loaded order get goods and weight data from order_data array
		$goods_total_full = $order_data["goods_total"];
		$weight_total = $order_data["weight_total"];
	}
	if (!strlen($order_min_goods_cost)) { $order_min_goods_cost = get_setting_value($order_info, "order_min_goods_cost");	}
	if (!strlen($order_max_goods_cost)) { $order_max_goods_cost = get_setting_value($order_info, "order_max_goods_cost");	}
	if (!strlen($order_min_weight)) { $order_min_weight = get_setting_value($order_info, "order_min_weight");	}
	if (!strlen($order_max_weight)) {	$order_max_weight = get_setting_value($order_info, "order_max_weight");	}
	$weight_measure = get_setting_value($settings, "weight_measure", "");
	$prevent_repurchase = get_setting_value($order_info, "prevent_repurchase", 0);
	$repurchase_period = get_setting_value($order_info, "repurchase_period", "");
	if ($order_min_goods_cost > 0 && $goods_total_full < $order_min_goods_cost) {
		$sc_errors .= str_replace("{min_cost}", currency_format($order_min_goods_cost), ORDER_MIN_PRODUCTS_COST_ERROR) . "<br>";
	}
	if ($order_max_goods_cost > 0 && $goods_total_full > $order_max_goods_cost) {
		$sc_errors .= str_replace("{max_cost}", currency_format($order_max_goods_cost), ORDER_MAX_PRODUCTS_COST_ERROR) . "<br>";
	}
	if ($order_min_weight > 0 && $weight_total < $order_min_weight) {
		$sc_errors .= str_replace("{min_weight}", $order_min_weight." ".$weight_measure, ORDER_MIN_WEIGHT_ERROR) . "<br>";
	}
	if ($order_max_weight > 0 && $weight_total > $order_max_weight) {
		$sc_errors .= str_replace("{max_weight}", $order_max_weight." ".$weight_measure, ORDER_MAX_WEIGHT_ERROR) . "<br>";
	}
	if ($credit_system && $credit_amount > $credit_balance) {
	}

	$order_email = get_param("email");
	if ($prevent_repurchase && ($user_id || $order_email)) {
		$current_ts = va_timestamp();
		$repurchase_ts = $current_ts - ($repurchase_period * 86400);
		foreach ($cart_items as $id => $cart_item) {
			$item_id = $cart_item["item_id"];
			$item_name = get_translation($cart_item["item_name"]);
			if ($item_id > 0) {
				$sql  = " SELECT o.order_placed_date ";
				$sql .= " FROM ((" . $table_prefix . "orders_items oi ";
				$sql .= " INNER JOIN " . $table_prefix . "orders o ON o.order_id=oi.order_id) ";
				$sql .= " INNER JOIN " . $table_prefix . "order_statuses os ON os.status_id=oi.item_status) ";
				$sql .= " WHERE oi.item_id=" . $db->tosql($item_id, INTEGER);
				$sql .= " AND os.paid_status=1 ";
				if ($repurchase_period > 0) {
					$sql .= " AND o.order_placed_date>" . $db->tosql($repurchase_ts, DATETIME);
				}
				$sql .= " AND (";
				if ($user_id) {
					$sql .= " o.user_id=" . $db->tosql($user_id, INTEGER);
				}
				if ($order_email) {
					if ($user_id) { $sql .= " OR "; }
					$sql .= " o.email=" . $db->tosql($order_email, TEXT);
				}
				$sql .= ") ";
				$sql .= " ORDER BY o.order_placed_date DESC ";
				$db->RecordsPerPage = 1; $db->PageNumber = 1;
				$db->query($sql);
				if ($db->next_record()) {
					if ($repurchase_period > 0) {
						$item_purchased = $db->f("order_placed_date", DATETIME);
						$item_purchased_ts = va_timestamp($item_purchased);
						$days_number = ceil($repurchase_period - (($current_ts - $item_purchased_ts) / 86400));
						$sc_error = str_replace("{product_name}", $item_name, PURCHASED_PRODUCT_DAYS_ERROR);
						$sc_error = str_replace("{days_number}", $days_number, $sc_error);
						$sc_errors .= $sc_error."<br>".$eol;
					} else {
						$sc_error = str_replace("{product_name}", $item_name, PURCHASED_PRODUCT_ERROR);
						$sc_errors .= $sc_error."<br>".$eol;
					}
				}
			}
		}
	}


	if (!$total_items) {
		$location_url = get_custom_friendly_url("basket.php");
		if ($ajax) {
			echo json_encode(array("errors" => "0 ".va_constant("PRODUCTS_TITLE"),"location" => $location_url));
			exit;
		} else {
			header("Location: " . $location_url);
			exit;
		}
	}

	$r = new VA_Record($table_prefix . "orders");

	// #11 - prepare shipping
	$total_shipping_types = 0; $shipping_type_ids = array(); $shipping_module_ids = array();
	$shipping_type_id = ""; $shipping_type_code = ""; $shipping_type_desc = ""; $tare_weight = 0; 
	$shipping_cost = 0; $shipping_taxable = 0; 
	$shipping_tax = 0; $shipping_points_amount = 0;

	// check all available shipping groups
	$shipping_groups = array();
	$total_shipping_cost = 0;
	$total_shipping_excl_tax = 0; 
	$total_shipping_tax = 0;
	$total_shipping_incl_tax = 0;
	$total_shipping_points_cost = 0;

	// check for saved shipping types 
	$saved_shipping_types = get_session("session_shipping_types");
	if (!is_array($saved_shipping_types))  { $saved_shipping_types = array(); }
	if (!$user_order_id && $shipping_quantity) {
		// get shipping settings
		$shipping_intro = get_translation(get_setting_value($order_info, "shipping_intro", ""));
		$shipping_image = get_setting_value($order_info, "shipping_image", "");
		$delivery_site_id = (isset($site_id)) ? $site_id : "";

		include("./includes/shipping_functions.php");
		$shipping_groups = get_shipping_types($country_id, $state_id, $postal_code, $delivery_site_id, $user_type_id, $cart_items, $call_center);

		if ($shipping_intro && count($shipping_groups) > 0) {
			$t->set_var("shipping_intro", $shipping_intro);
			$t->parse("shipping_intro_block", false);
		}

		$gi = 0;
		// start looking shipping groups
		foreach ($shipping_groups as $group_id => $shipping_group) {
			$gi++;
			// get all available shipping modules
			$shipping_modules= $shipping_group["modules"];
			foreach ($shipping_modules as $sm_key => $sm_id) {
				$shipping_module_ids[] = $sm_id;
			}

			// check selected params
			if ($operation == "load") {
				if (get_setting_value($shipments_data, "points_cost_".$gi, "") > 0) {
					$shipping_pay_points = 1;                                    
				} else {
					$shipping_pay_points = 0;
				}
				$selected_type_id = get_setting_value($shipments_data, "shipping_id_".$gi, "");
				$saved_shipping_types["shipping_type_id_".$gi] = $selected_type_id;
				$saved_shipping_types["shipping_pay_points_".$gi] = $shipping_pay_points;
			} else if (strlen($operation)) {
				$shipping_pay_points = get_param("shipping_pay_points_".$gi);
				$selected_type_id = get_param("shipping_type_id_".$gi);
				$saved_shipping_types["shipping_type_id_".$gi] = $selected_type_id;
				$saved_shipping_types["shipping_pay_points_".$gi] = $shipping_pay_points;
			} else {
				$shipping_pay_points = get_setting_value($saved_shipping_types, "shipping_pay_points_".$gi, "");
				$selected_type_id = get_setting_value($saved_shipping_types, "shipping_type_id_".$gi, "");
			}
			// get all available shipping types
			$shipping_types = $shipping_group["types"];
			$total_shipping_types = sizeof($shipping_types);
			if ($total_shipping_types == 0) {
				$t->sparse("no_shipping", false);
				$t->set_var("shipping_groups", "");
				break; 
			}
			if ($total_shipping_types == 1 || ($operation == "fast_checkout" && $total_shipping_types > 0)) {
				// get first shipping type when there is only one method or fast checkout selected 
				$selected_type_id = $shipping_types[0]["id"];
			}

			if ($total_shipping_types == 1) {
				$shipping_control = "HIDDEN";
			} else if ($shipping_block == 1) {
				$shipping_control = "LISTBOX";
			} else {
				$shipping_control = "RADIO";
			}

			// initialize vars for shipping types
			$shipping_control = ""; 
			$shipping_methods = ""; // variable for HTML form
			$shipping_cost = 0; $shipping_tax = 0; $shipping_cost_excl_tax = 0; $shipping_cost_incl_tax = 0;
			$t->set_var("index", $gi);

			// start checking shipping types
			for ($st = 0; $st < $total_shipping_types; $st++) {
				$shipment_data = $shipping_types[$st];
				$row_shipping_type_id = $shipment_data["id"];
				$row_shipping_module_id = $shipment_data["module_id"];
				$row_shipping_type_code = $shipment_data["code"];
				$row_shipping_type_desc = $shipment_data["desc"];
				$row_shipping_cost = $shipment_data["cost"];
				$row_tare_weight = $shipment_data["tare_weight"];
				$row_shipping_taxable = $shipment_data["taxable"];
				$row_shipping_time = $shipment_data["shipping_time"];
				$row_image_src = ""; $row_image_alt = "";
				if ($shipping_image == 1) {
					$row_image_src = $shipment_data["image_small"];
					$row_image_alt = $shipment_data["image_small_alt"];
				} else if ($shipping_image == 2) {
					$row_image_src = $shipment_data["image_large"];
					$row_image_alt = $shipment_data["image_large_alt"];
				}

				$shipping_type_ids[] = $row_shipping_type_id;
	  
				if ($tax_free) { $row_shipping_taxable = 0; }
				if ($shipping_methods) { $shipping_methods .= "&"; }
				if ($free_postage && ($free_postage_all || isset($free_postage_ids[$row_shipping_type_id]))) { $row_shipping_cost = 0; }
	  
				$shipping_methods .= "shipping_id=".$row_shipping_type_id;
				$shipping_methods .= "&module_id=".prepare_js_value($row_shipping_module_id);
				$shipping_methods .= "&code=".prepare_js_value($row_shipping_type_code);
				$shipping_methods .= "&desc=".prepare_js_value($row_shipping_type_desc);
				$shipping_methods .= "&cost=".prepare_js_value($row_shipping_cost);
				$shipping_methods .= "&tare=".prepare_js_value($row_tare_weight);
				$shipping_methods .= "&taxable=".intval($row_shipping_taxable);
				$shipping_methods .= "&tax_free=".intval(!$row_shipping_taxable);
				$shipping_methods .= "&time=".prepare_js_value($row_shipping_time);
	  
				$row_shipping_tax_id = 0;
				$row_shipping_tax_free = (!$row_shipping_taxable);
				// re-calculate shipping cost in case if it include some default tax rate 
				$row_shipping_tax = get_tax_amount($tax_rates, "shipping", $row_shipping_cost, 1, $row_shipping_tax_id, $row_shipping_tax_free, $shipping_tax_percent, $default_tax_rates);
				$shipping_tax_values = get_tax_amount($tax_rates, "shipping", $row_shipping_cost, 1, $row_shipping_tax_id, $row_shipping_tax_free, $shipping_tax_percent, $default_tax_rates, 2);

				if ($tax_prices_type == 1) {
					$row_shipping_cost_excl_tax = $row_shipping_cost - $row_shipping_tax;
					$row_shipping_cost_incl_tax = $row_shipping_cost;
				} else {
					$row_shipping_cost_excl_tax = $row_shipping_cost;
					$row_shipping_cost_incl_tax = $row_shipping_cost + $row_shipping_tax;
				}
	  
				if ($row_shipping_type_id == $selected_type_id) {
					// save shipping 
					$shipping_groups[$group_id]["selected_type_id"] = $selected_type_id;
					$shipping_groups[$group_id]["selected_type_key"] = $st;
					$shipping_type_id = $row_shipping_type_id;
					$shipping_type_code = $row_shipping_type_code;
					$shipping_type_desc = $row_shipping_type_desc;
					$shipping_time = $row_shipping_time;
					$tare_weight = $row_tare_weight;
					if ($points_system && $shipping_pay_points) {
						$shipping_points_amount = round($row_shipping_cost * $points_conversion_rate, $points_decimals);
						$shipping_cost = 0;
						$shipping_cost_excl_tax = 0;
						$shipping_cost_incl_tax = 0;
						$shipping_tax = 0;
					} else {
						$shipping_cost = $row_shipping_cost;
						$shipping_tax = $row_shipping_tax;
						$shipping_cost_excl_tax = $row_shipping_cost_excl_tax;
						$shipping_cost_incl_tax = $row_shipping_cost_incl_tax;
						$shipping_taxable = $row_shipping_taxable;
						// add taxes for selected shipping to total values 
						$row_shipping_tax = add_tax_values($tax_rates, $shipping_tax_values, "shipping");
					}
					$shipping_groups[$group_id]["selected_type"] = array(
						"shipping_id" => $row_shipping_type_id, 
						"shipping_module_id" => $row_shipping_module_id, 
						"shipping_code" => $row_shipping_type_code, 
						"shipping_desc" => $row_shipping_type_desc, 
						"shipping_cost" => $shipping_cost, 
						"tare_weight" => $row_tare_weight, 
						"tax_free" => $row_shipping_tax_free, 
						"points_cost" => $shipping_points_amount, 
					);
					// calculate total shipments
					$total_shipping_cost += $shipping_cost; 
					$total_shipping_excl_tax += $shipping_cost_excl_tax; 
					$total_shipping_incl_tax += $shipping_cost_incl_tax; 
					$total_shipping_tax += $shipping_tax; 
					$total_shipping_points_cost += $shipping_points_amount; 
	  
					$items_text .= $shipping_type_desc . ": " . currency_format($row_shipping_cost) . $eol;
					$t->set_var("shipping_type_checked", "checked");
					$t->set_var("shipping_type_selected", "selected");
				} else {
					$t->set_var("shipping_type_checked", "");
					$t->set_var("shipping_type_selected", "");
				}
				$t->set_var("shipping_type_id", $row_shipping_type_id);
				$t->set_var("shipping_module_id", $row_shipping_module_id);
				$t->set_var("shipping_type_code", $row_shipping_type_code);
				$t->set_var("shipping_value", round($row_shipping_cost, 2));
				if ($row_image_src) {
					$t->set_var("src", htmlspecialchars($row_image_src));
					$t->set_var("alt", htmlspecialchars($row_image_alt));
					$t->sparse("shipping_image", false);
				} else {
					$t->set_var("shipping_image", "");
				}


				// show shipping cost including taxes
				$shipping_cost_desc = " (".currency_format($row_shipping_cost_incl_tax).")";

				/* show shipping cost excluding and including taxes
				$shipping_cost_desc = currency_format($row_shipping_cost_excl_tax);
				if (is_array($tax_rates)) {
					$shipping_cost_desc .= " (".currency_format($row_shipping_cost_incl_tax);
					if ($tax_note) {
						$shipping_cost_desc .= " ".$tax_note;
					}
					$shipping_cost_desc .= ")";
				}//*/
	  
				$t->set_var("shipping_cost_desc", $shipping_cost_desc);
				$t->set_var("shipping_type_desc", $row_shipping_type_desc);

				if ($total_shipping_types == 1) {
					$t->parse("shipping_single", true);
				} else if ($shipping_block == 1) {
					$t->parse("shipping_option", true);
				} else {
					$t->parse("shipping_radio", true);
				}
			}
			// end check shipping types
			//if ($points_system && $points_shipping && $points_balance > 0 && $st == 0) {
			if ($points_system && $points_shipping && $points_balance > 0) {
				if ($shipping_pay_points) {
					$t->set_var("shipping_pay_points_checked", "checked");
				} else {
					$t->set_var("shipping_pay_points_checked", "");
				}
				$t->parse("shipping_points_block", false);
			} else {
				$t->set_var("shipping_points_block", "");
			}

			$t->set_var("shipping_control", $shipping_control); // todo: use JS to check control type
			$t->set_var("shipping_methods", $shipping_methods);

			if ($total_shipping_types > 1 && $shipping_block == 1) {
				$t->parse("shipping_list", false);
			}

			$t->set_var("group_name", implode(" / ", $shipping_group["group_name"]));
			$t->parse("shipping_groups", true);
			$t->set_var("shipping_single", "");
			$t->set_var("shipping_radio", "");
			$t->set_var("shipping_option", "");
			$t->set_var("shipping_list", "");
		} // end looking shipping groups
	}
	// save selected shipping types 
	set_session("session_shipping_types", $saved_shipping_types);

	// check for shipping methods for fast checkout
	if ($shipping_quantity && $operation == "fast_checkout") {
		$required_delivery = false; $items_ids = "";
		if (is_array($shipping_groups) && count($shipping_groups) > 0) {
			foreach ($shipping_groups as $group_id => $group_info) {
				if (!isset($group_info["selected_type_id"]) || !strlen($group_info["selected_type_id"])) {
					$required_delivery = true;
					if ($items_ids) { $items_ids .= ","; }
					$items_ids .= implode(",", $group_info["items_ids"]);
				}
			}
		} else {
			$required_delivery = true;
		}
		if ($required_delivery) {
			$location_url = get_custom_friendly_url("basket.php")."?operation=required_delivery";
			if ($items_ids) { $location_url .= "&items_ids=" . urlencode($items_ids); }
			if ($country_id) { $location_url .= "&country_id=" . urlencode($country_id); }
			if ($state_id) { $location_url .= "&state_id=" . urlencode($state_id); }
			if ($postal_code) { $location_url .= "&postal_code=" . urlencode($postal_code); }
			if ($ajax) {
				echo json_encode(array("location" => $location_url));
				exit;
			} else {
				header("Location: " . $location_url);
				exit;
			}
		}
	}

	// #11/2 parse shipping custom fields and check for possible errors
	$shipping_prerrors = ""; $shipping_fields = array();
	foreach ($shipping_properties as $property_id => $property_info) {
		$param_name = "op_".$property_id;
		$op_shipping_type_id = $property_info["shipping_type_id"];
		$op_shipping_module_id = $property_info["shipping_module_id"];
		$op_error = $property_info["error"];
		if (in_array($op_shipping_type_id, $shipping_type_ids) || in_array($op_shipping_module_id, $shipping_module_ids)) {
			$shipping_fields[$param_name]["show"] = 1;
			$shipping_fields[$param_name]["shipping_type_id"] = $op_shipping_type_id;
			$shipping_fields[$param_name]["shipping_module_id"] = $op_shipping_module_id;
			if ($property_info["required"]) {
				$shipping_fields[$param_name]["required"] = 1;
			}

			// add custom properties to shipping settings
			$t->set_var("property_id", $property_id);
			$t->set_var("property_name", $property_info["name"]);
			$t->set_var("property_class", $property_info["class"]);
			$t->set_var("property_style", $property_info["style"]);
			$t->set_var("property_control", $property_info["control"]);
			if ($property_info["required"]) {
				$t->set_var("property_required", "*");
			} else {
				$t->set_var("property_required", "");
			}

			$property_show = false;
			foreach ($shipping_groups as $group_id => $shipping_group) {
				$selected_type = get_setting_value($shipping_group, "selected_type");
				$selected_type_id = get_setting_value($selected_type, "shipping_id");
				$selected_module_id = get_setting_value($selected_type, "shipping_module_id");
				if (($op_shipping_type_id && $op_shipping_type_id == $selected_type_id) || 
					($op_shipping_module_id && $op_shipping_module_id == $selected_module_id)) {
					$property_show = true;
				}
			}

			if ($property_show) {
				if ($property_info["error"]) {
					$shipping_prerrors .= $property_info["error"];
				}
				$t->set_var("op_style", "");
			} else {
				$t->set_var("op_style", "display: none;");
			}
			$t->parse("shipping_properties", true);
		}
	}
	// #11/3 set shipping settings
	$t->set_var("shipping_fields", htmlspecialchars(json_encode($shipping_fields)));
	$t->set_var("shipping_properties_ids", json_encode(array_keys($shipping_properties)));

	// #12/1 - calculate the tax
	$order_fixed_tax = 0; // calculate all order fixed taxes here
	if ($tax_available) {
		// check external tax libraries for order_fixed_amound calculations
		foreach($tax_rates as $tax_id => $tax_data) {
			$tax_php_lib  = isset($tax_data["tax_php_lib"]) ? trim($tax_data["tax_php_lib"]) : "";
			if ($tax_php_lib && !preg_match("/^http/", $tax_php_lib)) {
				$taxable_amount = round($goods_total, 2) - round($total_discount, 2) + round($properties_taxable, 2);
				if ($shipping_taxable) {
					$taxable_amount += round($shipping_cost, 2);
				}
				$order_fixed_amount = 0; // set value to zero before run library
				$tax_error = ""; // clear error var before include library
				include_once ($tax_php_lib);
				if ($tax_error) { $sc_errors .= $tax_error."\n<br/>"; }
				// update tax value
				if ($order_fixed_amount > 0) {
					$tax_data["order_fixed_amount"] = $order_fixed_amount;
				}
				$tax_rates[$tax_id] = $tax_data;
			}
		}

		// get taxes sums for further calculations
		$taxes_sum = 0; $discount_tax_sum = $total_discount_tax;
		foreach($tax_rates as $tax_id => $tax_data) {
			$tax_cost  = isset($tax_data["tax_total"]) ? $tax_data["tax_total"] : 0;
			$order_fixed_tax += doubleval($tax_data["order_fixed_amount"]);
			$taxes_sum += va_round($tax_cost, $currency["decimals"]);
		}

		$taxes_param = ""; $tax_number = 0;
		foreach($tax_rates as $tax_id => $tax_info) {
			$tax_number++;
			$tax_name = get_translation($tax_info["tax_name"]);
			$tax_type = $tax_info["tax_type"];
			$current_tax_free = isset($tax_info["tax_free"]) ? $tax_info["tax_free"] : 0;
			if ($tax_free) { $current_tax_free = true; }
			$tax_percent = $tax_info["tax_percent"];
			$fixed_amount = $tax_info["fixed_amount"];
			$order_fixed_amount = $tax_info["order_fixed_amount"];
			$tax_types = $tax_info["types"];
			$tax_cost  = isset($tax_info["tax_total"]) ? $tax_info["tax_total"] : 0;
			if ($total_discount_tax) {
				// in case of order coupons decrease taxes value 
				if ($tax_number == sizeof($tax_rates)) {
					$tax_discount = $discount_tax_sum;
				} else {
					$tax_discount = round(($tax_cost * $total_discount_tax) / $taxes_sum, 2);
				}
				$discount_tax_sum -= $tax_discount;
				$tax_cost -= $tax_discount;
			}

			$tax_cost += doubleval($tax_info["order_fixed_amount"]); // after deduct discounts apply order fixed tax amount
			$taxes_total += va_round($tax_cost, $currency["decimals"]);

			// hide tax if it has zero value
			if ($tax_cost != 0) {
				$t->set_var("tax_id", $tax_id);
				$t->set_var("tax_percent", $tax_percent);
				$t->set_var("tax_name", $tax_name);
				$t->set_var("tax_cost", currency_format($tax_cost));
			
				$t->parse("taxes", true);
			}

			// build param
			if ($taxes_param) { $taxes_param .= "&"; }
			$taxes_param .= "tax_id=".$tax_id;
			$taxes_param .= "&tax_type=".prepare_js_value($tax_type);
			$taxes_param .= "&tax_name=".prepare_js_value($tax_name);
			$taxes_param .= "&tax_free=".prepare_js_value($current_tax_free);
			$taxes_param .= "&tax_percent=".prepare_js_value($tax_percent);
			$taxes_param .= "&fixed_amount=".prepare_js_value($fixed_amount);
			$taxes_param .= "&order_fixed_amount=".prepare_js_value($order_fixed_amount);
			if (is_array($tax_types) && sizeof($tax_types) > 0) {
				foreach($tax_types as $item_type_id => $item_type_info) {
					$taxes_param .= "&item_type_percent_".$item_type_id."=".prepare_js_value($item_type_info["tax_percent"]);
					$taxes_param .= "&item_type_fixed_".$item_type_id."=".prepare_js_value($item_type_info["fixed_amount"]);
				}
			}
		}
		$t->set_var("tax_rates", $taxes_param);
	}

	// #12/2 - calculate order total  
	$order_total = round($goods_total, 2) - round($total_discount, 2) + round($properties_total, 2) + round($total_shipping_cost, 2);
	if ($tax_prices_type != 1) {
		$order_total += round($taxes_total, 2);
	}

	// #13 - check if vouchers avaialable for this order and deduct them from order total value
	if (is_array($gift_vouchers) && sizeof($gift_vouchers) > 0) {
		foreach ($gift_vouchers as $voucher_id => $voucher_info) {
			$voucher_title = $voucher_info["title"];
			$voucher_max_amount = $voucher_info["max_amount"];
			if ($voucher_max_amount > $order_total) {
				$voucher_amount = $order_total;
			} else {
				$voucher_amount = $voucher_max_amount;
			}
			$order_total -= $voucher_amount;
			$vouchers_amount += $voucher_amount; // calculate total amount for vouchers
			$gift_vouchers[$voucher_id]["amount"] = $voucher_amount;

			$t->set_var("voucher_id", $voucher_id);
			$t->set_var("voucher_title", $voucher_title);
			$t->set_var("voucher_max_amount", $voucher_max_amount);
			if ($voucher_amount > 0) {
				if (strlen($vouchers_ids)) { $vouchers_ids .= ","; }
				$vouchers_ids .= $voucher_id;
				$t->set_var("voucher_amount", "- ".currency_format($voucher_amount));
			} else {
				$t->set_var("voucher_amount", "");
			}
			$t->parse("used_vouchers", true);
		}
		$t->parse("vouchers_block", false);
	}

	// #14 calculate step totals
	$paid_processing_fee = 0; $paid_processing_excl_tax = 0; $paid_processing_tax = 0; $paid_processing_incl_tax = 0;
	if ($user_order_id) {
    $goods_total_incl_tax = $order_data["goods_total_incl_tax"];
    $total_discount_incl_tax = $order_data["total_discount_incl_tax"];
		$vouchers_amount = 0; // already included in discount field above for saved orders
    $properties_incl_tax = $order_data["properties_incl_tax"];
    $total_shipping_incl_tax = $order_data["shipments_incl_tax"];
		$paid_processing_fee = $order_data["paid_processing_fee"];
		$paid_processing_excl_tax = $order_data["paid_processing_excl_tax"];
		$paid_processing_tax = $order_data["paid_processing_tax"];
		$paid_processing_incl_tax = $order_data["paid_processing_incl_tax"];
		$total_processing_tax = $order_data["processing_tax"];
		$unpaid_processing_tax = $total_processing_tax - $paid_processing_tax;

		$order_total = round($goods_total_incl_tax, 2) - round($total_discount_incl_tax, 2) + round($properties_incl_tax, 2) + round($total_shipping_incl_tax, 2);
    $taxes_total = $order_data["taxes_total"] - $unpaid_processing_tax; // for saved order deduct processing taxes as it will be added later 

		// total points, credits, commissions
		$total_reward_points = $order_data["total_reward_points"]; 
		$total_reward_credits = $order_data["total_reward_credits"];
		$total_merchants_commission = $order_data["total_merchants_commission"]; 
		$total_affiliate_commission = $order_data["total_affiliate_commission"]; 
	}
	$step_cart_total = $goods_total_incl_tax + $properties_incl_tax - $total_discount_incl_tax - $vouchers_amount;
	$step_user_total = $goods_total_incl_tax + $properties_incl_tax - $total_discount_incl_tax - $vouchers_amount;
	$step_shipping_total = $goods_total_incl_tax + $properties_incl_tax - $total_discount_incl_tax + $total_shipping_incl_tax - $vouchers_amount;
	if ($tax_prices_type != 1) {
		$step_cart_total += round($order_fixed_tax, 2);
		$step_user_total += round($order_fixed_tax, 2);
		$step_shipping_total += round($order_fixed_tax, 2);
	} 
	$partial_order_total = $order_total; // amount which is used to calculate partial payments
	// add previously paid processing fee to order total and taxes total
	$order_total += $paid_processing_incl_tax;
	$taxes_total += $paid_processing_tax;
	$left_total = $order_total - $paid_total;

	// #15 - deduct user credit amount from order before applying payment fees
	$order_credit_amount = 0; $credit_amount_left = $credit_amount;
	if ($credit_amount_left > 0 && $left_total > 0) {
		if ($credit_amount_left > $left_total) {
			$order_credit_amount = $left_total;
		} else {
			$order_credit_amount = $credit_amount_left;
		}
		$left_total -= $order_credit_amount;
		$order_total -= $order_credit_amount;
		$credit_amount_left -= $order_credit_amount;
	}

	$total_points_amount = $goods_points_amount + $properties_points_amount + $total_shipping_points_cost;
	if ($total_points_amount > 0) {
		// check if user has enough points to pay for goods
		$sql  = " SELECT SUM(points_action * points_amount) ";
		$sql .= " FROM " . $table_prefix . "users_points ";
		$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
		$total_points_sum = get_db_value($sql);

		// update points information in users table if it's has a wrong value
		if ($total_points_sum != $points_balance) {
			$sql  = " UPDATE " . $table_prefix . "users ";
			$sql .= " SET total_points=" . $db->tosql($total_points_sum, NUMBER);
			$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
			$db->query($sql);
			$user_info["total_points"] = $total_points_sum;
			set_session("session_user_info", $user_info);
		}

		if ($total_points_amount > $points_balance) {
			$error_message = str_replace("{points_amount}", number_format($total_points_amount, $points_decimals), POINTS_ENOUGH_ERROR);
			$sc_errors .= $error_message;
		}
	}

	// #16 - check partial payment options and amount user will pay
	$payment_amount = $left_total;
	$payment_percentage = 0;
	$user_payment_percentage = get_param("payment_percentage");
	if ($allow_partial_payment) {
		// check available percentage options for current order
		$order_percentages = array();
		$payment_options = json_decode($partial_payment_options, true);
		foreach ($payment_options as $pi => $payment_option) {
			$option_description = $payment_option["description"];
			$option_percentage = doubleval($payment_option["percentage"]);
			if (100 == $option_percentage) { $option_percentage = 100; }
			$option_amount = round(($partial_order_total * $option_percentage) / 100, 2);
			$option_min_order = $payment_option["min_order"];
			$option_max_order = $payment_option["max_order"];
			if (!strlen($option_max_order)) { $option_max_order = $partial_order_total; }
			if ($partial_order_total >= doubleval($option_min_order) && $partial_order_total <= $partial_order_total && $option_amount < $left_total) {
				$order_percentages[$option_percentage] = $option_description;
			}
		}
		
		if (count($order_percentages) == 0 || (count($order_percentages) && isset($order_percentages[100]))) {
			$allow_partial_payment = false;
		}

		// prepare array to show partial options
		if ($allow_partial_payment) {
			// add 100% option if it isn't available 
			if (!isset($order_percentages[100])) {
				$order_percentages[100] = "100%";
			}
			$pp_percentages = array();
			foreach ($order_percentages as $option_percentage => $option_description) {
				if ($user_payment_percentage == $option_percentage) {
					$payment_percentage = $user_payment_percentage;
				}
				$pp_percentages[] = array($option_percentage, $option_description);
			}
			set_options($pp_percentages, $payment_percentage, "payment_percentage"); 
			$t->sparse("partial_payment_block", false);
		}
	}
	// show amount user need to pay
	if ($payment_percentage > 0)	{
		$payment_amount = round(($partial_order_total * $payment_percentage) / 100, 2);
		if ($payment_amount > $left_total) { $payment_amount = $left_total; }
	}
	if (!$payment_amount) { $payment_amount = $left_total; }

	// #17/1 - get general payment systems list
	$total_payments = 0; $payments_ids = array(); $rem_ids = array(); 
	$payment_systems = array();
	$order_data["payment_systems"] = array(); // for JS use
	$sql  = " SELECT ps.payment_id, ps.is_default, pit.item_type_id, ps.item_types_all ";
	$sql .= " FROM (((((( " . $table_prefix . "payment_systems ps ";
	$sql .= " LEFT JOIN " . $table_prefix . "payment_currencies pcr ON pcr.payment_id=ps.payment_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "currencies cr ON pcr.currency_id=cr.currency_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "payment_countries pc ON pc.payment_id=ps.payment_id)";			
	$sql .= " LEFT JOIN " . $table_prefix . "payment_item_types pit ON ps.payment_id = pit.payment_id) ";
	if (isset($site_id)) {
		$sql .= " LEFT JOIN " . $table_prefix . "payment_systems_sites s ON s.payment_id=ps.payment_id) ";
	} else {
		$sql .= ")";
	}
	if (strlen($user_type_id)) {
		$sql .= " LEFT JOIN " . $table_prefix . "payment_user_types ut ON ut.payment_id=ps.payment_id) ";
	} else {
		$sql .= ")";
	}
	$sql .= " WHERE ((ps.is_active=1 ";
	if (strlen($user_type_id)) {
		$sql .= " AND (ps.user_types_all = 1 OR ut.user_type_id=" . $db->tosql($user_type_id, INTEGER, true, false) . ")";
	} else {
		$sql .= " AND ps.non_logged_users=1";
	}
	$sql .= ") ";
	if ($call_center) {
		$sql .= " OR ps.is_call_center=1 " ;
	}
	$sql .= ") ";
	if (isset($site_id)) {
		$sql .= " AND (ps.sites_all = 1 OR s.site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
	} else {
		$sql .= " AND ps.sites_all = 1 ";
	}
	$sql .= " AND (ps.currencies_all=1 OR cr.currency_code=" . $db->tosql($currency_code, TEXT) . ")";
	$sql .= " AND (ps.countries_all=1 OR pc.country_id=" . $db->tosql($bill_country_id, INTEGER) . ")";
	$sql .= " AND (ps.order_total_min IS NULL OR ps.order_total_min<=" . $db->tosql($left_total, NUMBER) . ")";
	$sql .= " AND (ps.order_total_max IS NULL OR ps.order_total_max>=" . $db->tosql($left_total, NUMBER) . ")";
	$sql .= " AND (ps.active_week_days&".intval($day_value)."<>0)";
	$sql .= " AND (ps.active_start_time IS NULL OR ps.active_start_time<=".$db->tosql($check_time, INTEGER).")";
	$sql .= " AND (ps.active_end_time IS NULL OR ps.active_end_time>=".$db->tosql($check_time, INTEGER).")";
	$sql .= " ORDER BY ps.payment_order, ps.payment_id ";
	$db->query($sql);
	while ($db->next_record()) {
		$row_payment_id = $db->f("payment_id");
		$is_default = $db->f("is_default");
		$row_item_type_id  = $db->f("item_type_id");
		$row_item_types_all = $db->f("item_types_all");
		if (isset($payment_systems[$row_payment_id])) {
			$payment_systems[$row_payment_id]["item_types_ids"][] = $row_item_type_id;
		} else {
			$payment_systems[$row_payment_id] = array(
				"is_default" => $is_default,
				"item_types_all" => $row_item_types_all,
				"item_types_ids" => array($row_item_type_id),
			);
		}
	}
	// #17/2 - check if payment systems allowed for items added to the cart
	foreach ($payment_systems as $payment_id => $payment_data) {
		if ($payment_data["item_types_all"] != 1) {
			for($i = 0; $i < sizeof($items_type_ids); $i++) {
				if (!in_array($items_type_ids[$i], $payment_data["item_types_ids"])) {
					unset($payment_systems[$payment_id]);
					break;
				}
			}
		}
	}
	$payments_ids = array_keys($payment_systems);
	$total_payments = count($payments_ids);
	// #17/3 - check default payment system if it's available
	$default_payment_id = "";
	foreach ($payment_systems as $payment_id => $payment_data) {
		if ($payment_data["is_default"]) {
			$default_payment_id = $payment_id; break;
		}
	}

	// #17/4 - check if any payment system was selected
	$payment_id = ""; $is_processing_fee = false; $processing_fees = ""; 
	$processing_fee = 0; $processing_tax_free = 0; $processing_tax_id = 0; $processing_time = 0;
	$processing_excl_tax =0; $processing_tax = 0; $processing_incl_tax = 0;
	$payment_url = ""; $payment_method = "GET"; $payment_advanced = 0;
	if ($total_payments == 1) {
		$sql  = " SELECT payment_id,payment_name,user_payment_name,payment_info,recurring_method,";
		$sql .= " processing_tax_free,fee_percent,fee_amount,fee_min_amount,fee_max_amount,processing_time, ";
		$sql .= " order_total_min, order_total_max, ";
		$sql .= " payment_url, submit_method, is_advanced ";
		$sql .= " FROM " . $table_prefix . "payment_systems ";
		$sql .= " WHERE payment_id IN (" . $db->tosql($payments_ids, INTEGERS_LIST) . ") ";
		$db->query($sql);
		if ($db->next_record()) {
			$payment_id = $db->f("payment_id");
			$payment_name = get_translation($db->f("payment_name"));
			$user_payment_name = get_translation($db->f("user_payment_name"));
			if ($user_payment_name) {
				$payment_name = $user_payment_name;
			}
			$payment_info = get_translation($db->f("payment_info"));
			$payment_url = $db->f("payment_url");
			$payment_method = $db->f("submit_method");
			$payment_advanced = $db->f("is_advanced");

			$order_total_min = $db->f("order_total_min"); 
			$order_total_max = $db->f("order_total_max");

			$recurring_method = $db->f("recurring_method");
			if ($recurring_items && !$recurring_method) {
				$payment_errors = str_replace("{payment_name}", $payment_name, RECURRING_NOT_ALLOWED_ERROR) . "<br/>";
			}
			$processing_tax_free = $db->f("processing_tax_free");
			if ($tax_free) { $processing_tax_free = $tax_free; }
			$fee_percent = doubleval($db->f("fee_percent"));
			$fee_amount = doubleval($db->f("fee_amount"));
			$processing_fee = $fee_amount + round(($payment_amount * $fee_percent) / 100, 2);

			$fee_min_amount = $db->f("fee_min_amount");
			$fee_max_amount = $db->f("fee_max_amount");
			if ((strlen($fee_max_amount) && $payment_amount > $fee_max_amount) || $payment_amount < $fee_min_amount) {
				$processing_fee = 0;
			}

			// calculate taxes for processing fee
			$processing_tax = get_tax_amount($tax_rates, 0, $processing_fee, 1, $processing_tax_id, $processing_tax_free, $processing_tax_percent, $default_tax_rates);
			if ($tax_prices_type == 1) {
				$processing_incl_tax = $processing_fee;
				$processing_excl_tax = $processing_fee - $processing_tax;
			} else {
				$processing_incl_tax = $processing_fee + $processing_tax;
				$processing_excl_tax = $processing_fee;
			}

			$processing_time = doubleval($db->f("processing_time"));
			$original_payment_name = $payment_name;
			if ($processing_fee != 0) {
				$is_processing_fee = true;
				if ($processing_fee > 0) {
					$payment_name .= " (+ " . currency_format($processing_incl_tax) . ")";
				} elseif ($processing_fee < 0) {
					$payment_name .= " (- " . currency_format(abs($processing_incl_tax)) . ")";
				}
			}
			$processing_fees = $payment_id . "," . intval($processing_tax_free) . "," . round($processing_fee, 2);

			$payment_systems[$row_payment_id] = array_merge($db->Record, $payment_systems[$row_payment_id]);
			$payment_systems[$row_payment_id]["payment_name"] = $original_payment_name;
			$payment_systems[$row_payment_id]["name_and_fee"] = $payment_name;
			$payment_systems[$row_payment_id]["info"] = $payment_info;

			$t->set_var("payment_hidden_id", $payment_id);
			$t->set_var("payment_name", $payment_name);
			$t->set_var("payment_info", $payment_info);
			$t->parse("payment_gateway_single", false);
		}
	} elseif ($total_payments > 1) {
		// validate select payment_id or check default
		$sql  = " SELECT ps.payment_id FROM (((((";
		$sql .= $table_prefix . "payment_systems ps ";
		$sql .= " LEFT JOIN " . $table_prefix . "payment_currencies pcr ON pcr.payment_id=ps.payment_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "currencies cr ON pcr.currency_id=cr.currency_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "payment_countries pc ON pc.payment_id=ps.payment_id)";			
		$sql .= " LEFT JOIN " . $table_prefix . "payment_systems_sites s ON s.payment_id=ps.payment_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "payment_user_types ut ON ut.payment_id=ps.payment_id) ";
		$sql .= " WHERE ps.payment_id IN (" . $db->tosql($payments_ids, INTEGERS_LIST) . ") ";
		$sql .= " AND (ps.sites_all = 1 OR s.site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
		if (strlen($user_type_id)) {
			$sql .= " AND (ps.user_types_all = 1 OR ut.user_type_id=" . $db->tosql($user_type_id, INTEGER, true, false) . ")";
		} else {
			$sql .= " AND ps.non_logged_users=1";
		}
		$sql .= " AND (ps.currencies_all=1 OR cr.currency_code=" . $db->tosql($currency_code, TEXT) . ")";
		$sql .= " AND (ps.countries_all=1 OR pc.country_id=" . $db->tosql($bill_country_id, INTEGER) . ")";
		$sql .= " AND (ps.active_week_days&".intval($day_value)."<>0)";
		$sql .= " AND (ps.active_start_time IS NULL OR ps.active_start_time<=".$db->tosql($check_time, INTEGER).")";
		$sql .= " AND (ps.active_end_time IS NULL OR ps.active_end_time>=".$db->tosql($check_time, INTEGER).")";
		if ($operation == "fast_checkout") {
			$sql .= " AND ps.payment_id=".$db->tosql($fast_payment_id, INTEGER);
		} elseif ($operation == "load") {
			$load_payment_id = get_setting_value($order_data, "payment_id", "");
			$sql .= " AND ps.payment_id=".$db->tosql($load_payment_id, INTEGER);
		} elseif ($operation == "save" || $operation == "next" || $operation == "refresh" || $operation == "fast_order") {
			$param_payment_id = get_param("payment_id");
			$sql .= " AND ps.payment_id=".$db->tosql($param_payment_id, INTEGER);
		} else {
			// check default payment system
			$sql .= " AND ps.is_default=1 ";
		}
		$payment_id = get_db_value($sql);

		$payment_image = get_setting_value($order_info, "payment_image", "");
		$payment_select_values = array(array("", ""));
		$sql  = " SELECT payment_id,payment_name,user_payment_name,payment_info,";
		$sql .= " recurring_method,processing_tax_free,fee_percent,fee_amount,fee_min_amount,fee_max_amount,processing_time,";
		$sql .= " image_small, image_small_alt, image_large, image_large_alt,";
		$sql .= " order_total_min, order_total_max, ";
		$sql .= " payment_url, submit_method, is_advanced ";
		$sql .= " FROM " . $table_prefix . "payment_systems ";
		$sql .= " WHERE payment_id IN (" . $db->tosql($payments_ids, INTEGERS_LIST) . ") ";
		$sql .= " ORDER BY payment_order, payment_id ";
		$db->query($sql);
		while ($db->next_record()) {
			$row_payment_id = $db->f("payment_id");
			$row_recurring_method = $db->f("recurring_method");
			$row_payment_name = get_translation($db->f("payment_name"));
			$user_payment_name = get_translation($db->f("user_payment_name"));
			if ($user_payment_name) {
				$row_payment_name = $user_payment_name;
			}
			$row_payment_info = get_translation($db->f("payment_info"));

			$order_total_min = $db->f("order_total_min");
			$order_total_max = $db->f("order_total_max");

			$row_processing_tax_free = $db->f("processing_tax_free");
			$row_fee_percent = doubleval($db->f("fee_percent"));
			$row_fee_amount = doubleval($db->f("fee_amount"));
			$row_processing_fee = $row_fee_amount + round(($payment_amount * $row_fee_percent) / 100, 2);

			// calculate taxes for processing fee
			$row_processing_tax = get_tax_amount($tax_rates, 0, $row_processing_fee, 1, $processing_tax_id, $row_processing_tax_free, $row_processing_tax_percent, $default_tax_rates);
			if ($tax_prices_type == 1) {
				$row_processing_incl_tax = $row_processing_fee;
				$row_processing_excl_tax = $row_processing_fee - $row_processing_tax;
			} else {
				$row_processing_incl_tax = $row_processing_fee + $row_processing_tax;
				$row_processing_excl_tax = $row_processing_fee;
			}

			$fee_min_amount = $db->f("fee_min_amount");
			$fee_max_amount = $db->f("fee_max_amount");

			$row_image = ""; $row_image_alt = "";
			if ($payment_image == 1) {
				$row_image = $db->f("image_small");
				$row_image_alt = $db->f("image_small_alt");
			} elseif ($payment_image == 2) {
				$row_image = $db->f("image_large");
				$row_image_alt = $db->f("image_large_alt");
			}

			if ((strlen($fee_max_amount) && $payment_amount > $fee_max_amount) || $payment_amount < $fee_min_amount) {
				$row_processing_fee = 0;
			}
			$row_processing_time = doubleval($db->f("processing_time"));
			if ($processing_fees) { $processing_fees .= ","; }
			$processing_fees .= $row_payment_id . "," . intval($row_processing_tax_free) . "," . round($row_processing_fee, 2);
			if ($row_payment_id == $payment_id) {
				$payment_url = $db->f("payment_url");
				$payment_method = $db->f("submit_method");
				$payment_advanced = $db->f("is_advanced");
				if ($recurring_items && !$row_recurring_method) {
					$payment_errors = str_replace("{payment_name}", $row_payment_name, RECURRING_NOT_ALLOWED_ERROR) . "<br/>";
				}
				$processing_fee = $row_processing_fee;
				$processing_tax = $row_processing_tax;
				$processing_tax_free = $row_processing_tax_free;
				$processing_time = $row_processing_time;
			}
			$original_payment_name = $row_payment_name;
			if ($row_processing_fee > 0) {
				$is_processing_fee = true;
				$row_payment_name .= " (+ " . currency_format($row_processing_incl_tax) . ")";
			} elseif ($row_processing_fee < 0) {
				$is_processing_fee = true;
				$row_payment_name .= " (- " . currency_format(abs($row_processing_incl_tax)) . ")";
			}

			$payment_systems[$row_payment_id] = array_merge($db->Record, $payment_systems[$row_payment_id]);
			$payment_systems[$row_payment_id]["payment_name"] = $original_payment_name;
			$payment_systems[$row_payment_id]["name_and_fee"] = $row_payment_name;
			$payment_systems[$row_payment_id]["info"] = $row_payment_info;
			$payment_systems[$row_payment_id]["image"] = $row_image;
			$payment_systems[$row_payment_id]["image_alt"] = $row_image_alt;
			$payment_systems[$row_payment_id]["order_total_min"] = $order_total_min;
			$payment_systems[$row_payment_id]["order_total_max"] = $order_total_max;
			$payment_select_values[] = array($row_payment_id, $row_payment_name);
		}

		$payment_control = get_setting_value($order_info, "payment_control_type", 0);

		if ($payment_control == 1) {
			$t->set_var("payment_radio_id", "");
			$t->set_var("pmt_control_type", "radio");

			foreach ($payment_systems as $row_payment_id => $payment_data)
			{
				$row_payment_name = $payment_data["name_and_fee"];
				$row_image = $payment_data["image"];
				$row_image_alt = $payment_data["image_alt"];
				$order_total_min = $payment_data["order_total_min"];
				$order_total_max = $payment_data["order_total_max"];
				$checked = ""; $selected = ""; $disabled = "";
				if (strval($row_payment_id) == strval($payment_id)) {
					$checked = "checked"; $selected = "selected";
				}

				$t->set_var("payment_radio_id_disabled", $disabled);
				$t->set_var("payment_radio_id_checked", $checked);   
				$t->set_var("payment_radio_id_value", $row_payment_id);
				$t->set_var("payment_radio_id_description", $row_payment_name);

				if ($row_image) {
					$t->set_var("src_image", $row_image);
					$t->set_var("alt_image", $row_image_alt);
					$t->sparse("image_option", false);
				}	 else {
					$t->set_var("image_option", "");
				}

				$t->parse("payment_radio_id", true);
			}
			$t->parse("payment_gateways_radio", false);
		} else {
			$t->set_var("pmt_control_type", "select");
			set_options($payment_select_values, $payment_id, "payment_select_id");
			$t->parse("payment_gateways_select", false);
		}

		// check if payment was selected
		if ($active_step == "payment" && ($operation == "save" || $operation == "fast_order" || $operation == "next") && !strlen($payment_id)) {
			$payment_error = str_replace("{field_name}", PAYMENT_GATEWAY_MSG, REQUIRED_MESSAGE);
			$payment_errors .= $payment_error;
		}

	} else {
		$payment_id = "";
		$payment_errors = NO_ACTIVE_PS_MSG;
	}

	// #17/5 - get payment fields for all available payment systems and get payment settings only for selected payment system
	$payment_settings = array(); 
	foreach ($payments_ids as $pid) {
		$payment_systems[$pid]["fields"] = array();
		foreach ($cc_parameters as $key => $param_name) {
			$payment_systems[$pid]["fields"][$param_name] = array("show" => 0, "required" => 0);
		}
	}
	if (is_array($payments_ids) && sizeof($payments_ids) > 0) {
		$where = "";
		for ($pid = 0; $pid < sizeof($payments_ids); $pid++) {
			if ($where) { $where .= " OR "; }
			$where .= " setting_type LIKE 'credit_card_info_" . intval($payments_ids[$pid]) . "' ";
		}
		$sql  = " SELECT setting_type,setting_name,setting_value FROM " . $table_prefix . "global_settings ";
		$sql .= " WHERE (" . $where . ") ";
		$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
		$sql .= " ORDER BY site_id ASC ";
		$db->query($sql);
		while ($db->next_record()) {
			//$setting_type = "credit_card_info_" . $pid;
			$setting_type = $db->f("setting_type");
			if (preg_match("/^credit_card_info_(\d+)$/is", $setting_type, $matches)) {
				// get parameters
				$pid = $matches[1];
				$setting_name = $db->f("setting_name");
				$setting_value = $db->f("setting_value");
				if ($payment_id == $pid) {
					// save settings for selected payment system
					$payment_settings[$setting_name] = $setting_value;
				}
				if ($call_center) {
					if (preg_match("/^call_center_show_(\w+)$/", $setting_name, $matches)) {
						$param_name = $matches[1];
						$payment_systems[$pid]["fields"][$param_name]["show"] = $setting_value;
					} else if (preg_match("/^call_center_(\w+)_required$/", $setting_name, $matches)) {
						$param_name = $matches[1];
						$payment_systems[$pid]["fields"][$param_name]["required"] = $setting_value;
					}
				} else {
					if (!preg_match("/^call_center_/", $setting_name)) {
						if (preg_match("/^show_(\w+)$/", $setting_name, $matches)) {
							$param_name = $matches[1];
							$payment_systems[$pid]["fields"][$param_name]["show"] = $setting_value;
						} else if (preg_match("/^(\w+)_required$/", $setting_name, $matches)) {
							$param_name = $matches[1];
							$payment_systems[$pid]["fields"][$param_name]["required"] = $setting_value;
						}	
					}
				}
			}
		}
	}

	// set styles and required option for default fields
	$payment_info = "";
	if ($payment_id) { 
		$payment_info = trim($payment_systems[$payment_id]["info"]);
	}
	if ($payment_info) {
		$t->set_var("payment_info", $payment_info);
		$t->set_var("payment_info_style", "");
	} else {
		$t->set_var("payment_info_style", "display: none;");
	}
	foreach ($cc_parameters as $key => $param_name) {
		if ($payment_id && $payment_systems[$payment_id]["fields"][$param_name]["show"]) {
			$t->set_var($param_name."_style", "");
		} else {
			$t->set_var($param_name."_style", "display: none;");
		}
		if ($payment_id && $payment_systems[$payment_id]["fields"][$param_name]["required"]) {
			$t->set_var($param_name."_required_style", "");
		} else {
			$t->set_var($param_name."_required_style", "display: none;");
		}
	}

	// parse payment custom fields and check for possible errors
	$payment_prerrors = "";
	foreach ($payment_properties as $property_id => $property_info) {
		$param_name = "op_".$property_id;
		$op_payment_id = $property_info["payment_id"];
		$op_error = $property_info["error"];
		if (in_array($op_payment_id, $payments_ids)) {
			// add parameter to payment field list
			for ($p = 0; $p < sizeof($payments_ids); $p++) {
				$pid = $payments_ids[$p];
				$payment_systems[$pid]["fields"][$param_name] = array("show" => 0, "required" => 0);
			}
			$payment_systems[$op_payment_id]["fields"][$param_name]["show"] = 1;
			if ($property_info["required"]) {
				$payment_systems[$op_payment_id]["fields"][$param_name]["required"] = 1;
			}

			// add custom properties to payment settings
			$t->set_var("property_id", $property_id);
			$t->set_var("property_name", $property_info["name"]);
			$t->set_var("property_class", $property_info["class"]);
			$t->set_var("property_style", $property_info["style"]);
			$t->set_var("property_control", $property_info["control"]);
			if ($property_info["required"]) {
				$t->set_var("property_required", "*");
			} else {
				$t->set_var("property_required", "");
			}
			if ($op_payment_id == $payment_id) {
				if ($property_info["error"]) {
					$payment_prerrors .= $property_info["error"];
				}
				$t->set_var("op_style", "");
			} else {
				$t->set_var("op_style", "display: none;");
			}
			$t->parse("payment_properties", true);
		}
	}

	// #17/6 set payment settings
	$order_data["payment_systems"] = $payment_systems;
	$t->set_var("payment_data", htmlspecialchars(json_encode($payment_systems)));
	$t->set_var("payment_properties_ids", json_encode(array_keys($payment_properties)));

	if ($is_processing_fee) {
		// calculate taxes for processing fee
		$processing_tax = get_tax_amount($tax_rates, 0, $processing_fee, 1, $processing_tax_id, $processing_tax_free, $processing_tax_percent, $default_tax_rates);
		$processing_tax_values = get_tax_amount($tax_rates, 0, $processing_fee, 1, $processing_tax_id, $processing_tax_free, $processing_tax_percent, $default_tax_rates, 2);
		// add taxes for processing to total values 
		add_tax_values($tax_rates, $processing_tax_values, "processing");

		if ($tax_prices_type == 1) {
			$processing_incl_tax = $processing_fee;
			$processing_excl_tax = $processing_fee - $processing_tax;
		} else {
			$processing_incl_tax = $processing_fee + $processing_tax;
			$processing_excl_tax = $processing_fee;
		}

		$items_text .= PROCESSING_FEE_MSG . ": " . currency_format($processing_fee);
		// update order total, payment amount and order tax value
		$order_total += $processing_incl_tax;
		$payment_amount += $processing_incl_tax;
		$left_total += $processing_incl_tax;
		$taxes_total += $processing_tax;
	}

	$t->set_var("processing_fees", $processing_fees);
	// check credit amount for order after applying fee
	if ($credit_amount_left > 0 && $left_total > 0) {
		if ($credit_amount_left > $left_total) {
			$order_credit_amount += $left_total;
			$order_total = 0;
			$left_total = 0;
		} else {
			$order_credit_amount += $credit_amount_left;
			$order_total -= $credit_amount_left;
			$left_total -= $credit_amount_left;
		}
	}


	// order total string
	$items_text .= CART_TOTAL_MSG . ": " . currency_format($order_total);
	$t->set_var("order_total_desc", currency_format($left_total));

	$t->set_var("step_cart_total", currency_format($step_cart_total ));
	$t->set_var("step_user_total", currency_format($step_user_total));
	$t->set_var("step_shipping_total", currency_format($step_shipping_total));

	$t->set_var("total_points_amount", number_format($total_points_amount, $points_decimals));
	$t->set_var("goods_points_value", round($goods_points_amount, $points_decimals));
	$t->set_var("properties_points_value", round($properties_points_amount, $points_decimals));
	$t->set_var("shipping_points_value", round($total_shipping_points_cost, $points_decimals)); // TODO: check what to do with this JS value
	$t->set_var("order_data", htmlspecialchars(json_encode($order_data))); // set order data for javascript

	if ($points_system && $user_id) {
		$t->set_var("points_balance", number_format($points_balance, $points_decimals));
		$t->set_var("remaining_points", number_format($points_balance - $total_points_amount, $points_decimals));
		$t->sparse("points_balance_block", false);
		if ($points_balance > 0) {
			$t->sparse("total_points_block", false);
		}
	}
	if ($paid_total > 0) {
		$t->set_var("paid_total_desc", currency_format(-$paid_total));
		$t->sparse("paid_total_block", false);
	}
	if ($credit_system && $user_id && $credits_balance_order_profile) {
		$t->set_var("credit_balance", currency_format($credit_balance));
		$t->sparse("credit_balance_block", false);
		if ($credit_balance > 0) {
			if ($credit_amount) {
				$t->set_var("credit_amount", htmlspecialchars($credit_amount));
			} else {
				$t->set_var("credit_amount", "");
			}
			$t->sparse("credit_amount_block", false);
		}
	}
	$t->set_var("payment_amount", number_format($payment_amount, 2, ".", ""));
	$t->set_var("payment_amount_desc", currency_format($payment_amount));

	$r->add_where("order_id", INTEGER);
	$r->add_textbox("invoice_number", TEXT);
	$r->change_property("invoice_number", USE_SQL_NULL, false);
	$r->change_property("invoice_number", USE_IN_UPDATE, false);
	$r->add_textbox("session_id", TEXT);
	$r->add_textbox("site_id", INTEGER);
	$r->change_property("site_id", USE_SQL_NULL, false);
	$r->add_textbox("language_code", TEXT);
	$r->add_textbox("user_id", INTEGER);
	$r->add_textbox("user_type_id", INTEGER);
	$r->add_textbox("newsletter_id", INTEGER);
	$r->add_textbox("newsletter_email_id", INTEGER);
	$r->add_textbox("admin_id_added_by", INTEGER);
	$r->add_textbox("payment_id", INTEGER, PAYMENT_GATEWAY_MSG);
	if ($checkout_flow == "quote" || $checkout_flow == "quote_request") {
		$r->change_property("payment_id", USE_SQL_NULL, false);
	}
	$r->add_textbox("order_payment_id", INTEGER);
	$r->add_textbox("payment_amount", FLOAT);
	$r->add_textbox("success_message", TEXT);
	$r->add_textbox("error_message", TEXT);
	$r->add_textbox("pending_message", TEXT);
	$r->add_textbox("remote_address", TEXT);
	$r->add_textbox("initial_ip", TEXT);
	$r->add_textbox("cookie_ip", TEXT);
	$r->add_textbox("visit_id", INTEGER);
	$r->change_property("visit_id", USE_SQL_NULL, false);
	$r->add_textbox("affiliate_code", TEXT);
	$r->change_property("affiliate_code", USE_SQL_NULL, false);
	$r->add_textbox("affiliate_user_id", INTEGER);
	$r->change_property("affiliate_user_id", USE_SQL_NULL, false);
	$r->add_textbox("friend_code", TEXT);
	$r->change_property("friend_code", USE_SQL_NULL, false);
	$r->add_textbox("friend_user_id", INTEGER);
	$r->change_property("friend_user_id", USE_SQL_NULL, false);
	$r->add_textbox("keywords", TEXT);
	$r->change_property("keywords", USE_SQL_NULL, false);
	$r->add_textbox("coupons_ids", TEXT);
	$r->add_textbox("vouchers_ids", TEXT);
	$r->add_textbox("default_currency_code", TEXT);
	$r->add_textbox("currency_code", TEXT);
	$r->add_textbox("currency_rate", FLOAT);
	$r->add_textbox("payment_currency_code", TEXT);
	$r->add_textbox("payment_currency_rate", FLOAT);
	$r->add_textbox("order_status", INTEGER);
	$r->add_textbox("total_buying", NUMBER);
	$r->add_textbox("total_buying_tax", NUMBER);
	$r->add_textbox("total_merchants_commission", NUMBER);
	$r->add_textbox("total_affiliate_commission", NUMBER);
	$r->add_textbox("goods_total", NUMBER);
	$r->add_textbox("goods_tax", NUMBER);
	$r->add_textbox("goods_incl_tax", NUMBER);
	$r->add_textbox("goods_points_amount", NUMBER);
	$r->add_textbox("total_quantity", NUMBER);
	$r->add_textbox("weight_total", NUMBER);
	$r->add_textbox("actual_weight_total", NUMBER);
	$r->add_textbox("total_discount", NUMBER);
	$r->add_textbox("total_discount_tax", NUMBER);
	$r->add_textbox("properties_total", NUMBER);
	$r->add_textbox("properties_taxable", NUMBER);
	$r->add_textbox("properties_points_amount", NUMBER);
/* 
	TODO: Delete old fields
	$r->add_textbox("shipping_type_id", INTEGER);
	$r->add_textbox("shipping_type_code", TEXT);
	$r->add_textbox("shipping_type_desc", TEXT);
	$r->add_textbox("shipping_cost", NUMBER);
	$r->add_textbox("shipping_taxable", INTEGER);
	$r->add_textbox("shipping_points_amount", NUMBER);
	$r->add_textbox("shipping_expecting_date", DATETIME);
*/

	$r->add_textbox("shipping_excl_tax", NUMBER);
	$r->add_textbox("shipping_tax", NUMBER);
	$r->add_textbox("shipping_incl_tax", NUMBER);
	$r->add_textbox("shipping_points_cost", NUMBER);

	$r->add_textbox("tax_name", TEXT);
	$r->add_textbox("tax_percent", NUMBER);
	$r->add_textbox("tax_total", NUMBER);
	$r->add_textbox("tax_prices_type", NUMBER);
	$r->add_textbox("vouchers_amount", NUMBER);
	$r->add_textbox("credit_amount", NUMBER);
	if (!$credit_system || !$user_id || !$credits_balance_order_profile || $credit_balance <= 0) {
		$r->change_property("credit_amount", SHOW, false);
	}

	$r->add_textbox("processing_fee", NUMBER);
	$r->add_textbox("processing_tax_free", NUMBER);
	$r->add_textbox("processing_excl_tax", NUMBER);
	$r->add_textbox("processing_tax", NUMBER);
	$r->add_textbox("processing_incl_tax", NUMBER);

	$r->add_textbox("order_total", NUMBER);
	$r->add_textbox("total_points_amount", NUMBER);
	$r->add_textbox("total_reward_points", NUMBER);
	$r->add_textbox("total_reward_credits", NUMBER);
	$r->add_textbox("order_placed_date", DATETIME);
	$r->add_textbox("is_fast_checkout", INTEGER);
	$r->add_textbox("is_paid", INTEGER);

	$companies = get_db_values("SELECT company_id,company_name FROM " . $table_prefix . "companies ", array(array("", va_constant("SELECT_COMPANY_MSG"))));
	//$states = get_db_values("SELECT state_id,state_name FROM " . $table_prefix . "states WHERE show_for_user=1 ORDER BY state_name ", array(array("", va_constant("SELECT_STATE_MSG"))));

	$va_countries = va_countries(); 
	$va_delivery_countries = va_delivery_countries(); 

	$select_countries = array(array("", va_message("SELECT_COUNTRY_MSG")));
	foreach ($va_countries as $va_country_id => $va_country_data) {
		$select_countries[] = array($va_country_id, $va_country_data["country_name"]);
	}
	$select_delivery_countries = array(array("", va_message("SELECT_COUNTRY_MSG")));
	foreach ($va_delivery_countries as $va_country_id => $va_country_data) {
		$select_delivery_countries[] = array($va_country_id, $va_country_data["country_name"]);
	}

	$countries = get_db_values("SELECT country_id,country_name FROM " . $table_prefix . "countries WHERE show_for_user=1 ORDER BY country_order, country_name ", array(array("", va_constant("SELECT_COUNTRY_MSG"))));
	// get phone codes
	$phone_codes = get_phone_codes();

	$r->add_textbox("name", TEXT, va_constant("FULL_NAME_FIELD"));
	$r->change_property("name", USE_SQL_NULL, false);
	$r->add_textbox("first_name", TEXT, va_constant("FIRST_NAME_FIELD"));
	$r->change_property("first_name", USE_SQL_NULL, false);
	$r->add_textbox("middle_name", TEXT, va_constant("MIDDLE_NAME_FIELD"));
	$r->change_property("middle_name", USE_SQL_NULL, false);
	$r->add_textbox("last_name", TEXT, va_constant("LAST_NAME_FIELD"));
	$r->change_property("last_name", USE_SQL_NULL, false);
	$r->add_select("company_id", INTEGER, $companies, va_constant("COMPANY_SELECT_FIELD"));
	$r->add_textbox("company_name", TEXT, va_constant("COMPANY_NAME_FIELD"));
	$r->add_textbox("email", TEXT, va_constant("EMAIL_FIELD"));
	$r->change_property("email", USE_SQL_NULL, false);
	$r->change_property("email", REGEXP_MASK, EMAIL_REGEXP);
	$r->add_textbox("address1", TEXT, va_constant("STREET_FIRST_FIELD"));
	$r->add_textbox("address2", TEXT, va_constant("STREET_SECOND_FIELD"));
	$r->add_textbox("address3", TEXT, va_constant("STREET_THIRD_FIELD"));
	$r->add_textbox("city", TEXT, CITY_FIELD);
	$r->add_textbox("province", TEXT, PROVINCE_FIELD);
	$r->add_select("state_id", INTEGER, "", va_constant("STATE_FIELD"));
	$r->change_property("state_id", USE_SQL_NULL, false);
	$r->add_textbox("state_code", TEXT);
	$r->add_textbox("zip", TEXT, ZIP_FIELD);
	$r->change_property("zip", TRIM, true);
	$r->add_select("country_id", INTEGER, $select_countries, va_constant("COUNTRY_FIELD"));
	$r->change_property("country_id", USE_SQL_NULL, false);
	$r->add_textbox("country_code", TEXT);
	if ($phone_code_select) {
		$r->add_select("phone_code", TEXT, $phone_codes);
		$r->add_select("daytime_phone_code", TEXT, $phone_codes);
		$r->add_select("evening_phone_code", TEXT, $phone_codes);
		$r->add_select("cell_phone_code", TEXT, $phone_codes);
		$r->add_select("fax_code", TEXT, $phone_codes);
	}
	$r->add_textbox("phone", TEXT, va_constant("PHONE_FIELD"));
	$r->change_property("phone", REGEXP_MASK, PHONE_REGEXP);
	$r->add_textbox("daytime_phone", TEXT, va_constant("DAYTIME_PHONE_FIELD"));
	$r->change_property("daytime_phone", REGEXP_MASK, PHONE_REGEXP);
	$r->add_textbox("evening_phone", TEXT, va_constant("EVENING_PHONE_FIELD"));
	$r->change_property("evening_phone", REGEXP_MASK, PHONE_REGEXP);
	$r->add_textbox("cell_phone", TEXT, va_constant("CELL_PHONE_FIELD"));
	$r->change_property("cell_phone", REGEXP_MASK, PHONE_REGEXP);
	$r->add_textbox("fax", TEXT, va_constant("FAX_FIELD"));
	$r->change_property("fax", REGEXP_MASK, PHONE_REGEXP);

	$r->add_textbox("delivery_name", TEXT, va_constant("DELIVERY_MSG")." ".va_constant("FULL_NAME_FIELD"));
	$r->add_textbox("delivery_first_name", TEXT, va_constant("DELIVERY_MSG")." ".va_constant("FIRST_NAME_FIELD"));
	$r->add_textbox("delivery_middle_name", TEXT, va_constant("DELIVERY_MSG")." ".va_constant("MIDDLE_NAME_FIELD"));
	$r->add_textbox("delivery_last_name", TEXT, va_constant("DELIVERY_MSG")." ".va_constant("LAST_NAME_FIELD"));
	$r->add_select("delivery_company_id", INTEGER, $companies, va_constant("DELIVERY_MSG")." ".va_constant("COMPANY_SELECT_FIELD"));
	$r->add_textbox("delivery_company_name", TEXT, va_constant("DELIVERY_MSG")." ".va_constant("COMPANY_NAME_FIELD"));
	$r->add_textbox("delivery_email", TEXT, va_constant("DELIVERY_MSG")." ".va_constant("EMAIL_FIELD"));
	$r->change_property("delivery_email", REGEXP_MASK, EMAIL_REGEXP);
	$r->add_textbox("delivery_address1", TEXT, va_constant("DELIVERY_MSG")." ".va_constant("STREET_FIRST_FIELD"));
	$r->add_textbox("delivery_address2", TEXT, va_constant("DELIVERY_MSG")." ".va_constant("STREET_SECOND_FIELD"));
	$r->add_textbox("delivery_address3", TEXT, va_constant("DELIVERY_MSG")." ".va_constant("STREET_THIRD_FIELD"));
	$r->add_textbox("delivery_city", TEXT, va_constant("DELIVERY_MSG")." ".va_constant("CITY_FIELD"));
	$r->add_textbox("delivery_province", TEXT, va_constant("DELIVERY_MSG")." ".va_constant("PROVINCE_FIELD"));
	$r->add_select("delivery_state_id", INTEGER, "", va_constant("DELIVERY_MSG")." ".va_constant("STATE_FIELD"));
	$r->change_property("delivery_state_id", USE_SQL_NULL, false);
	$r->add_textbox("delivery_state_code", TEXT);
	$r->add_textbox("delivery_zip", TEXT, va_constant("DELIVERY_MSG")." ".va_constant("ZIP_FIELD"));
	$r->change_property("delivery_zip", TRIM, true);
	$r->add_select("delivery_country_id", INTEGER, $select_delivery_countries, va_constant("DELIVERY_MSG")." ".va_constant("COUNTRY_FIELD"));
	$r->add_textbox("delivery_country_code", TEXT);
	$r->change_property("delivery_country_id", USE_SQL_NULL, false);
	if ($phone_code_select) {
		$r->add_select("delivery_phone_code", TEXT, $phone_codes);
		$r->add_select("delivery_daytime_phone_code", TEXT, $phone_codes);
		$r->add_select("delivery_evening_phone_code", TEXT, $phone_codes);
		$r->add_select("delivery_cell_phone_code", TEXT, $phone_codes);
		$r->add_select("delivery_fax_code", TEXT, $phone_codes);
	}
	$r->add_textbox("delivery_phone", TEXT, va_constant("DELIVERY_MSG")." ".va_constant("PHONE_FIELD"));
	$r->change_property("delivery_phone", REGEXP_MASK, PHONE_REGEXP);
	$r->add_textbox("delivery_daytime_phone", TEXT, va_constant("DELIVERY_MSG")." ".va_constant("DAYTIME_PHONE_FIELD"));
	$r->change_property("delivery_daytime_phone", REGEXP_MASK, PHONE_REGEXP);
	$r->add_textbox("delivery_evening_phone", TEXT, va_constant("DELIVERY_MSG")." ".va_constant("EVENING_PHONE_FIELD"));
	$r->change_property("delivery_evening_phone", REGEXP_MASK, PHONE_REGEXP);
	$r->add_textbox("delivery_cell_phone", TEXT, va_constant("DELIVERY_MSG")." ".va_constant("CELL_PHONE_FIELD"));
	$r->change_property("delivery_cell_phone", REGEXP_MASK, PHONE_REGEXP);
	$r->add_textbox("delivery_fax", TEXT, va_constant("DELIVERY_MSG")." ".va_constant("FAX_FIELD"));
	$r->change_property("delivery_fax", REGEXP_MASK, PHONE_REGEXP);

	// disable phone fields for SQL's
	disable_phone_codes();
	if ($user_order_id && $phone_code_select) {
		foreach  ($phone_parameters as $param_name) {
			$r->change_property($param_name."_code", DISABLED, true);
		}
	}

	for ($i = 0; $i < sizeof($parameters); $i++)
	{
		$param_name = $parameters[$i];
		$r->change_property($param_name, TRIM, true);
		$r->change_property("delivery_".$param_name, TRIM, true);
		if ($user_order_id) {
			$r->change_property($param_name, DISABLED, true);
			$r->change_property("delivery_".$param_name, DISABLED, true);
			$r->change_property($param_name, USE_IN_UPDATE, false);
			$r->change_property("delivery_".$param_name, USE_IN_UPDATE, false);
		}

		$personal_param = $param_prefix."show_" . $parameters[$i];
		$delivery_param = $param_prefix."show_delivery_" . $parameters[$i];
		$personal_required = $param_prefix.$parameters[$i]."_required";
		$delivery_required = $param_prefix."delivery_" . $parameters[$i]."_required";

		if (isset($order_info[$personal_param]) && $order_info[$personal_param] == 1) {
			$personal_number++;
			if ($order_info[$parameters[$i] . "_required"] == 1) {
				$r->parameters[$parameters[$i]][REQUIRED] = true;
			}
			$field_name = $r->get_property_value($param_name, CONTROL_DESC);
			$personal_fields[$param_name] = array(
				"field_name" => $field_name,
				"required" => $order_info[$personal_required],
				"required_message" => strip_tags(str_replace("{field_name}", $field_name, va_constant("REQUIRED_MESSAGE"))),
			);
		} else {
			$r->parameters[$parameters[$i]][SHOW] = false;
		}
		if (isset($order_info[$delivery_param]) && $order_info[$delivery_param] == 1) {
			$delivery_number++;
			if ($order_info["delivery_" . $parameters[$i] . "_required"] == 1) {
				$r->parameters["delivery_" . $parameters[$i]][REQUIRED] = true;
			}
			$field_name = $r->get_property_value("delivery_".$param_name, CONTROL_DESC);
			$personal_fields["delivery_".$param_name] = array(
				"field_name" => $field_name,
				"required" => $order_info[$delivery_required],
				"required_message" => strip_tags(str_replace("{field_name}", $field_name, va_constant("REQUIRED_MESSAGE"))),
			);
		} else {
			$r->parameters["delivery_" . $parameters[$i]][SHOW] = false;
		}
	}
	$personal_fields = array_merge($personal_fields, $properties_fields);
	$t->set_var("personal_fields", json_encode($personal_fields));

	$r->add_checkbox("same_as_personal", INTEGER);
	$r->change_property("same_as_personal", USE_IN_SELECT, false);
	$r->change_property("same_as_personal", USE_IN_INSERT, false);
	$r->change_property("same_as_personal", USE_IN_UPDATE, false);
	if ($personal_number < 1 || $delivery_number < 1 || $user_order_id) {
		$r->parameters["same_as_personal"][SHOW] = false;
	}
	$r->add_checkbox("subscribe", INTEGER);
	$r->change_property("subscribe", USE_IN_SELECT, false);
	$r->change_property("subscribe", USE_IN_INSERT, false);
	$r->change_property("subscribe", USE_IN_UPDATE, false);
	if (!$subscribe_block) {
		$r->parameters["subscribe"][SHOW] = false;
	}
	// payment fields
	$r->add_textbox("cc_name", TEXT, CC_NAME_FIELD);
	$r->add_textbox("cc_first_name", TEXT, CC_FIRST_NAME_FIELD);
	$r->add_textbox("cc_last_name", TEXT, CC_LAST_NAME_FIELD);
	$r->add_textbox("cc_number", TEXT, CC_NUMBER_FIELD);
	$r->add_textbox("cc_start_date", DATETIME, CC_START_DATE_FIELD);
	$r->change_property("cc_start_date", VALUE_MASK, array("MM", " / ", "YYYY"));
	$r->add_textbox("cc_expiry_date", DATETIME, CC_EXPIRY_DATE_FIELD);
	$r->change_property("cc_expiry_date", VALUE_MASK, array("MM", " / ", "YYYY"));
	$credit_cards = get_db_values("SELECT credit_card_id, credit_card_name FROM " . $table_prefix . "credit_cards", array(array("", PLEASE_CHOOSE_MSG)));
	$r->add_select("cc_type", INTEGER, $credit_cards, CC_TYPE_FIELD);
	$issue_numbers = get_db_values("SELECT issue_number AS issue_value, issue_number AS issue_description FROM " . $table_prefix . "issue_numbers", array(array("", NOT_AVAILABLE_MSG)));
	$r->add_select("cc_issue_number", INTEGER, $issue_numbers, CC_ISSUE_NUMBER_FIELD);
	$r->add_textbox("cc_security_code", TEXT, CC_SECURITY_CODE_FIELD);

	if ($operation == "load") {
		$r->set_value("order_id", $order_id);
		$r->get_db_values();
		$r->set_value("cc_number", "");
		$r->set_value("cc_security_code", "");
		$payment_errors = get_setting_value($order_data, "error_message", "");
	} else if ($operation == "fast_order") {
		// set user details from user info
		$user_login = $user_info["login"];
		for ($i = 0; $i < sizeof($parameters); $i++) {
			$r->set_value($parameters[$i], get_setting_value($user_info, $parameters[$i]));
			$r->set_value("delivery_" . $parameters[$i], get_setting_value($user_info, "delivery_".$parameters[$i]));
		}
		$show_email = get_setting_value($order_info, $param_prefix."show_email", 0);
		$show_delivery_email = get_setting_value($order_info, $param_prefix."show_delivery_email", 0);
		if ($show_email && $r->is_empty("email") && preg_match(EMAIL_REGEXP, $user_login)) { 
			$r->set_value("email", $user_login);
		}
		if (!$show_email && $show_delivery_email && $r->is_empty("delivery_email") && preg_match(EMAIL_REGEXP, $user_login)) { 
			$r->set_value("delivery_email", $user_login);
		}
	} else {
		$r->get_form_values();
	}
	// get some additional parameters
	$cc_start_year   = get_param("cc_start_year");
	$cc_start_month  = get_param("cc_start_month");
	$cc_expiry_year  = get_param("cc_expiry_year");
	$cc_expiry_month = get_param("cc_expiry_month");

	$r->set_value("session_id", session_id());
	$r->set_value("user_id", $user_id);
	$r->set_value("user_type_id", $user_type_id);
	$r->set_value("newsletter_id", $newsletter_id);
	$r->set_value("newsletter_email_id", $newsletter_email_id);
	$r->set_value("admin_id_added_by", get_session("session_admin_id"));
	if ($checkout_flow != "quote" && $checkout_flow != "quote_request") {
		// set payment data only for usual checkout flow
		$r->set_value("payment_id", $payment_id);
	}
	$r->set_value("remote_address", $user_ip);
	$r->set_value("initial_ip", $initial_ip);
	$r->set_value("cookie_ip", $cookie_ip);
	$r->set_value("visit_id", $visit_id);
	$r->set_value("affiliate_code", $affiliate_code);
	$r->set_value("affiliate_user_id", $affiliate_user_id);
	$r->set_value("friend_code", $friend_code);
	$r->set_value("friend_user_id", $friend_user_id);
	$r->set_value("keywords", $keywords);
	$r->set_value("properties_total", $properties_total);
	$r->set_value("properties_taxable", $properties_taxable);
	$r->set_value("properties_points_amount", $properties_points_amount);
	$r->set_value("tax_name", $tax_names);
	$r->set_value("tax_percent", $tax_percent_sum);
	$r->set_value("tax_total", $taxes_total);
	$r->set_value("tax_prices_type", $tax_prices_type);
	$r->set_value("credit_amount", $order_credit_amount);
	$r->set_value("processing_fee", $processing_fee + $paid_processing_fee);
	$r->set_value("processing_tax_free", $processing_tax_free);
	$r->set_value("processing_excl_tax", $processing_excl_tax + $paid_processing_excl_tax);
	$r->set_value("processing_tax", $processing_tax + $paid_processing_tax);
	$r->set_value("processing_incl_tax", $processing_incl_tax + $paid_processing_incl_tax);

	$r->set_value("default_currency_code", $default_currency_code);
	$r->set_value("currency_code", $currency["code"]);
	$r->set_value("currency_rate", $currency["rate"]);
	$r->set_value("is_fast_checkout", $is_fast_checkout);
	$r->set_value("is_paid", 0);

	if ($user_order_id) {
		// disable fields to update for previously saved order
		$r->change_property("site_id", USE_IN_UPDATE, false);
		$r->change_property("user_id", USE_IN_UPDATE, false);
		$r->change_property("user_type_id", USE_IN_UPDATE, false);
		$r->change_property("newsletter_id", USE_IN_UPDATE, false);
		$r->change_property("newsletter_email_id", USE_IN_UPDATE, false);
		$r->change_property("admin_id_added_by", USE_IN_UPDATE, false);
		$r->change_property("cookie_ip", USE_IN_UPDATE, false);
		$r->change_property("affiliate_code", USE_IN_UPDATE, false);
		$r->change_property("affiliate_user_id", USE_IN_UPDATE, false);
		$r->change_property("friend_code", USE_IN_UPDATE, false);
		$r->change_property("friend_user_id", USE_IN_UPDATE, false);
		$r->change_property("keywords", USE_IN_UPDATE, false);
		$r->change_property("coupons_ids", USE_IN_UPDATE, false);
		$r->change_property("vouchers_ids", USE_IN_UPDATE, false);
		/*
		$r->add_textbox("default_currency_code", TEXT);
		$r->add_textbox("currency_code", TEXT);
		$r->add_textbox("currency_rate", FLOAT);
		$r->add_textbox("payment_currency_code", TEXT);
		$r->add_textbox("payment_currency_rate", FLOAT);//*/

		$r->change_property("order_status", USE_IN_UPDATE, false);

		$r->change_property("total_buying", USE_IN_UPDATE, false);
		$r->change_property("total_buying_tax", USE_IN_UPDATE, false);
		$r->change_property("total_merchants_commission", USE_IN_UPDATE, false);
		$r->change_property("total_affiliate_commission", USE_IN_UPDATE, false);

		$r->change_property("goods_total", USE_IN_UPDATE, false);
		$r->change_property("goods_tax", USE_IN_UPDATE, false);
		$r->change_property("goods_incl_tax", USE_IN_UPDATE, false);
		$r->change_property("goods_points_amount", USE_IN_UPDATE, false);


		$r->change_property("total_quantity", USE_IN_UPDATE, false);
		$r->change_property("weight_total", USE_IN_UPDATE, false);
		$r->change_property("actual_weight_total", USE_IN_UPDATE, false);
		$r->change_property("total_discount", USE_IN_UPDATE, false);
		$r->change_property("total_discount_tax", USE_IN_UPDATE, false);
		$r->change_property("properties_total", USE_IN_UPDATE, false);
		$r->change_property("properties_taxable", USE_IN_UPDATE, false);
		$r->change_property("properties_points_amount", USE_IN_UPDATE, false);
  
		$r->change_property("shipping_excl_tax", USE_IN_UPDATE, false);
		$r->change_property("shipping_tax", USE_IN_UPDATE, false);
		$r->change_property("shipping_incl_tax", USE_IN_UPDATE, false);
		$r->change_property("shipping_points_cost", USE_IN_UPDATE, false);
  
		$r->change_property("tax_name", USE_IN_UPDATE, false);
		$r->change_property("tax_percent", USE_IN_UPDATE, false);
		//$r->change_property("tax_total", NUMBER); // as processing fee could contain tax we have to updat this field
		$r->change_property("tax_prices_type", USE_IN_UPDATE, false);
		$r->change_property("vouchers_amount", USE_IN_UPDATE, false);
		$r->change_property("credit_amount", USE_IN_UPDATE, false);
  
		//$r->add_textbox("order_total", USE_IN_UPDATE, false); // as processing fee could be added we need to update total value for order
		$r->change_property("total_points_amount", USE_IN_UPDATE, false);
		$r->change_property("total_reward_points", USE_IN_UPDATE, false);
		$r->change_property("total_reward_credits", USE_IN_UPDATE, false);
		$r->change_property("order_placed_date", USE_IN_UPDATE, false);
	}

	// personal and delivery country show
	$personal_country_show = $r->get_property_value("country_id", SHOW);
	$delivery_country_show = $r->get_property_value("delivery_country_id", SHOW);
	$personal_state_show = $r->get_property_value("state_id", SHOW);
	$delivery_state_show = $r->get_property_value("delivery_state_id", SHOW);

	if ($personal_country_show != 1 && $delivery_country_show != 1) {
		$r->set_value("country_id", $country_id);
		$r->set_value("delivery_country_id", $country_id);
	} else if ($personal_country_show == 1 && $delivery_country_show != 1 && $delivery_state_show == 1) {
		$r->set_value("delivery_country_id", $r->get_value("country_id"));
	}

	if ($operation == "fast_checkout") {
		if ($order_info["show_delivery_country_id"] == 1) {
			$r->set_value("delivery_country_id", $country_id);
		} else {
			$r->set_value("country_id", $country_id);
		}
		if ($order_info["show_delivery_state_id"] == 1) {
			$r->set_value("delivery_state_id", $state_id);
		} else {
			$r->set_value("state_id", $state_id);
		}
		if ($order_info["show_delivery_zip"] == 1) {
			$r->set_value("delivery_zip", $postal_code);
		} else {
			$r->set_value("zip", $postal_code);
		}
	}
	// check personal and delivery country codes
	$personal_country_code = ""; $delivery_country_code = "";
	$personal_country_id = $r->get_value("country_id");
	$delivery_country_id = $r->get_value("delivery_country_id");
	if (strlen($personal_country_id)) {
		$sql = "SELECT country_code FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($personal_country_id, INTEGER);
		$personal_country_code = get_db_value($sql);
		if (strtoupper($personal_country_code) == "GB") {
			$r->change_property("zip", BEFORE_VALIDATE, "format_zip");
			$r->change_property("zip", REGEXP_MASK, UK_POSTCODE_REGEXP);
		}
	}
	if (strlen($delivery_country_id)) {
		$sql = "SELECT country_code FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($delivery_country_id, INTEGER);
		$delivery_country_code = get_db_value($sql);
		if (strtoupper($delivery_country_code) == "GB") {
			$r->change_property("delivery_zip", BEFORE_VALIDATE, "format_delivery_zip");
			$r->change_property("delivery_zip", REGEXP_MASK, UK_POSTCODE_REGEXP);
		}
	}
	$r->set_value("country_code", $country_code);
	$r->set_value("delivery_country_code", $delivery_country_code);

	$variables["user_id"] = $r->get_value("user_id");
	$variables["tax_name"] = $tax_names;
	$variables["tax_percent"] = $tax_percent_sum;

	if ($delivery_errors) {
		if ($country_id) {
			$delivery_errors = str_replace("{country_name}", $va_delivery_countries[$country_id]["country_name"], $delivery_errors);
		} else {
			$delivery_errors = str_replace("{field_name}", va_constant("COUNTRY_FIELD"), va_constant("REQUIRED_MESSAGE"));
		}
		$profile_errors .= $delivery_errors;
	}

	if (strlen($operation))	{
		// get different states lists
		$states = prepare_states($r);

		if ($operation == "save" || $operation == "next") {
			if ($steps["shipping"]["show"]) {
				if (is_array($shipping_groups) && sizeof($shipping_groups) > 0) {
					foreach ($shipping_groups as $group_id => $group_info) {
						if (!isset($group_info["selected_type_id"]) || !strlen($group_info["selected_type_id"])) {
							$shipping_errors .= va_constant("REQUIRED_DELIVERY_MSG") . "<br>";
							break;
						}
					}
				}
				$shipping_errors .= $shipping_prerrors;
			}

			if ($r->get_value("same_as_personal")) {
				for ($i = 0; $i < sizeof($parameters); $i++) {
					$personal_param = "show_" . $parameters[$i];
					$delivery_param = "show_delivery_" . $parameters[$i];
					if (isset($order_info[$delivery_param]) && isset($order_info[$personal_param]) &&
					$order_info[$delivery_param] == 1 && $order_info[$personal_param] == 1) {
						$r->set_value("delivery_" . $parameters[$i], $r->get_value($parameters[$i]));
					}
				}
			}

			if (!$user_order_id) {
				// don't validate order for previously saved order
				$r->validate();
				$r->errors .= $options_errors;	
			}

			// prepare and check errors for payment fields
			if ($part_payments && $left_total > 0) {
				$payment_amount_min = 0.01;
				$payment_amount_max = $left_total;
				if ($payment_amount < $payment_amount_min) {
					$error_message = str_replace("{field_name}", PAYMENT_AMOUNT_MSG, MIN_VALUE_MESSAGE);
					$error_message = str_replace("{min_value}", currency_format($payment_amount_min), $error_message);
					$payment_errors .= $error_message;
				}
				if ($payment_amount > $payment_amount_max) {
					$error_message = str_replace("{field_name}", PAYMENT_AMOUNT_MSG, MAX_VALUE_MESSAGE);
					$error_message = str_replace("{max_value}", currency_format($payment_amount_max), $error_message);
					$payment_errors .= $error_message;
				}
			}

			// clear all data from payment fields if field is not active for selected payment
			foreach ($cc_parameters as $key => $param_name) {
				if ($payment_id && !$payment_systems[$payment_id]["fields"][$param_name]["show"]) {
					if ($r->parameter_exists($param_name)) {
						$r->set_value($param_name, "");
					}
				}
			}

			$cc_number = $r->get_value("cc_number");
			if (strlen($cc_number) >= 10) {
				$ss = array("\\","^","\$",".","[","]","|","(",")","+","{","}");
				$rs = array("\\\\","\\^","\\\$","\\.","\\[","\\]","\\|","\\(","\\)","\\+","\\{","\\}");
				$cc_allowed_regexp = get_setting_value($payment_settings, "cc_allowed", "");
				$cc_allowed_regexp = preg_replace("/\s/", "", $cc_allowed_regexp);
				if (strlen($cc_allowed_regexp)) {
					$cc_allowed_regexp = str_replace($ss, $rs, $cc_allowed_regexp);
					$cc_allowed_regexp = str_replace(array(",", ";", "*", "?"), array(")|(", ")|(", ".*", "."), $cc_allowed_regexp);
					$cc_allowed_regexp = "/^((" . $cc_allowed_regexp. "))$/i";
				}
				$cc_forbidden_regexp = get_setting_value($payment_settings, "cc_forbidden", "");
				$cc_forbidden_regexp = preg_replace("/\s/", "", $cc_forbidden_regexp);
				if (strlen($cc_forbidden_regexp)) {
					$cc_forbidden_regexp = str_replace($ss, $rs, $cc_forbidden_regexp);
					$cc_forbidden_regexp = str_replace(array(",", ";", "*", "?"), array(")|(", ")|(", ".*", "."), $cc_forbidden_regexp);
					$cc_forbidden_regexp = "/^((" . $cc_forbidden_regexp. "))$/i";
				}
				if (strlen($cc_allowed_regexp) && !preg_match($cc_allowed_regexp, $cc_number)) {
					$payment_errors .= CC_NUMBER_ALLOWED_MSG . "<br>" . $eol;
				} elseif (strlen($cc_forbidden_regexp) && preg_match($cc_forbidden_regexp, $cc_number)) {
					$payment_errors .= CC_NUMBER_ALLOWED_MSG . "<br>" . $eol;
				} elseif (!check_cc_number($cc_number)) {
					$payment_errors .= CC_NUMBER_ERROR_MSG . "<br>" . $eol;
				}
			}

			if (strlen($cc_start_year) && strlen($cc_start_month)) {
				$r->set_value("cc_start_date", array($cc_start_year, $cc_start_month, 1, 0, 0, 0));
			}
			if (strlen($cc_expiry_year) && strlen($cc_expiry_month)) {
				$r->set_value("cc_expiry_date", array($cc_expiry_year, $cc_expiry_month, 1, 0, 0, 0));
			}
			if ($payment_id) {
				// check payment fields only if payment was selected
				foreach ($cc_parameters as $key => $param_name) {
					$control_value = $r->get_value($param_name);
					if ($payment_systems[$payment_id]["fields"][$param_name]["required"]) {
						if (!is_array($control_value) && !strlen($control_value)) {
							$field_error = str_replace("{field_name}", $r->get_property_value($param_name, CONTROL_DESC), REQUIRED_MESSAGE) . "<br/>\n";
							$payment_errors .= $field_error;
						}
					}
					// some additional validation rules for cc number and code
					if ($param_name == "cc_number" && strlen($control_value) && strlen($control_value) < 10) {
						$field_error = str_replace("{field_name}", $r->get_property_value("cc_number", CONTROL_DESC), va_constant("MIN_LENGTH_MESSAGE"));
						$field_error = str_replace("{min_length}", 10, $field_error);
						$payment_errors .= $field_error. "<br/>\n";;
					} else if ($param_name == "cc_security_code" && strlen($control_value) && strlen($control_value) < 3) {
						$field_error = str_replace("{field_name}", $r->get_property_value("cc_security_code", CONTROL_DESC), va_constant("MIN_LENGTH_MESSAGE"));
						$field_error = str_replace("{min_length}", 3, $field_error);
						$payment_errors .= $field_error. "<br/>\n";;
					} else if ($param_name == "cc_security_code" && strlen($control_value) && strlen($control_value) > 4) {
						$field_error = str_replace("{field_name}", $r->get_property_value("cc_security_code", CONTROL_DESC), va_constant("MAX_LENGTH_MESSAGE"));
						$field_error = str_replace("{min_length}", 4, $field_error);
						$payment_errors .= $field_error. "<br/>\n";;
					}
				}
			}
			$payment_errors .= $payment_prerrors; // add payment properties errors
			// end payment fields checks

			if (strlen($r->errors) || strlen($sc_errors) || $shipping_errors || $payment_errors) {
				$is_valid = false;
			} else {
				$is_valid = true;
			}
		} elseif ($operation == "fast_checkout") {
			if ($sc_errors) {
				header ("Location: basket.php?fc_errors=".urlencode($sc_errors));
				exit;
			} else if ($delivery_errors) {
				// if some items has delivery restrictions redirect user back to basket page with appropriatove error message
				header ("Location: basket.php?fc_errors=".urlencode($delivery_errors));
				exit;
			} else if ($shipping_errors) {
				header ("Location: basket.php?fc_errors=".urlencode($shipping_errors));
				exit;
			}
			$is_valid = true;
		} elseif ($operation == "fast_order") {
			$r->validate();
			if (strlen($r->errors) || strlen($sc_errors) || $shipping_errors || $payment_errors) {
				$is_valid = false;
			} else {
				$is_valid = true;
			}
		} else {
			$is_valid = false;
		}

		if ($is_valid && ($operation == "save" || $operation == "fast_order" || $operation == "fast_checkout"))
		{
			// get payment rate for the selected gateway
			$payment_currency = get_payment_rate($payment_id, $currency);
			$payment_decimals = $payment_currency["decimals"];
			$payment_rate = $payment_currency["rate"];
			if (!$payment_amount) { $payment_amount = $left_total; }
			$r->set_value("payment_currency_code", $payment_currency["code"]);
			$r->set_value("payment_currency_rate", $payment_currency["rate"]);
			$r->set_value("payment_amount", $payment_amount);

			$variables["tax_cost"] = number_format($taxes_total * $payment_rate, $payment_decimals, ".", "");
			$variables["tax_total"] = number_format($taxes_total * $payment_rate, $payment_decimals, ".", "");
			$variables["processing_fee"] = number_format($processing_fee * $payment_rate, $payment_decimals, ".", "");

			$new_order_status = 1;
			// set status to zero when adding order
			$r->set_value("order_status", 0);

			$variables["total_buying"] = number_format($total_buying * $payment_rate, $payment_decimals, ".", "");
			$variables["total_buying_tax"] = number_format($total_buying_tax * $payment_rate, $payment_decimals, ".", "");
			$variables["total_merchants_commission"] = number_format($total_merchants_commission * $payment_rate, $payment_decimals, ".", "");
			$variables["total_affiliate_commission"] = number_format($total_affiliate_commission * $payment_rate, $payment_decimals, ".", "");
			$variables["goods_total"] = number_format($goods_total * $payment_rate, $payment_decimals, ".", "");
			$variables["weight_total"] = ($weight_total + $tare_weight);
			$variables["actual_weight_total"] = ($actual_weight_total + $tare_weight);

			$variables["coupons_ids"] = $order_coupons_ids;
			$variables["vouchers_ids"] = $vouchers_ids;
			$variables["vouchers_amount"] = $vouchers_amount;
			$variables["default_currency_code"] = $default_currency_code;
			$variables["currency_code"] = $currency["code"];
			$variables["currency_value"] = $currency["value"];
			$variables["currency_rate"] = $currency["rate"];
			$variables["total_discount"] = number_format($total_discount * $payment_rate, $payment_decimals, ".", "");
			$variables["total_discount_tax"] = number_format($total_discount_tax * $payment_rate, $payment_decimals, ".", "");
			$goods_with_discount = $goods_total - $total_discount;
			$variables["goods_with_discount"] = number_format($goods_with_discount * $payment_rate, $payment_decimals, ".", "");
			$variables["properties_total"] = number_format($properties_total * $payment_rate, $payment_decimals, ".", "");
			$variables["properties_taxable"] = number_format($properties_taxable * $payment_rate, $payment_decimals, ".", "");
			$variables["properties_points_amount"] = number_format($properties_points_amount, $points_decimals);
			$variables["shipping_type_id"] = $shipping_type_id;
			$variables["shipping_type_code"] = $shipping_type_code;
			$variables["shipping_type"] = $shipping_type_desc;
			$variables["shipping_cost"] = number_format($total_shipping_cost * $payment_rate, $payment_decimals, ".", "");
			$variables["shipping_points_amount"] = $total_shipping_points_cost;

			// calculate shipping expecting date excluding sundays
			$handle_hours = $max_availability_time + $shipping_time + $processing_time;
			$shipping_expecting_date = get_expecting_date($handle_hours);
			$variables["shipping_expecting_date"] = $shipping_expecting_date;

			$r->set_value("total_buying", $total_buying);
			$r->set_value("total_buying_tax", $total_buying_tax);
			$r->set_value("total_merchants_commission", $total_merchants_commission);
			$r->set_value("total_affiliate_commission", $total_affiliate_commission);
			$r->set_value("goods_total",  $goods_total);
			$r->set_value("goods_incl_tax",  $goods_total_incl_tax); 
			$r->set_value("goods_tax",  $goods_tax_total);
			$r->set_value("goods_points_amount",  $goods_points_amount);
			$r->set_value("total_quantity", $total_quantity);
			$r->set_value("weight_total",  ($weight_total + $tare_weight));
			$r->set_value("actual_weight_total",  ($actual_weight_total + $tare_weight));

			$r->set_value("coupons_ids", $order_coupons_ids);
			$r->set_value("total_discount", $total_discount);
			$r->set_value("total_discount_tax", $total_discount_tax);
			$r->set_value("vouchers_ids", $vouchers_ids);
			$r->set_value("vouchers_amount", $vouchers_amount);
/*
			TODO: Delete old fields
			TODO: Check shipping expecting date for each shipping method
			$r->set_value("shipping_type_id", $shipping_type_id);
			$r->set_value("shipping_type_code", $shipping_type_code);
			$r->set_value("shipping_type_desc", $shipping_type_desc);
			$r->set_value("shipping_cost", $shipping_cost);
			$r->set_value("shipping_taxable", $shipping_taxable);
			$r->set_value("shipping_points_amount", $shipping_points_amount);
			$r->set_value("shipping_expecting_date", va_time($shipping_expecting_date)); 
*/

			$r->set_value("shipping_excl_tax", $total_shipping_excl_tax);
			$r->set_value("shipping_tax", $total_shipping_tax);
			$r->set_value("shipping_incl_tax", $total_shipping_incl_tax);
			$r->set_value("shipping_points_cost", $total_shipping_points_cost);

			// set active site_id and language_code
			$r->set_value("site_id", $site_id);
			$r->set_value("language_code", $language_code);

			for ($i = 0; $i < sizeof($parameters); $i++) {
				$variables[$parameters[$i]] = $r->get_value($parameters[$i]);
				$variables["delivery_" . $parameters[$i]] = $r->get_value("delivery_" . $parameters[$i]);
			}

			// prepare user name variables
			if (strlen($variables["name"]) && !strlen($variables["first_name"]) && !strlen($variables["middle_name"]) && !strlen($variables["last_name"])) {
				$name = $variables["name"];
				$name_parts = explode(" ", $name, 2);
				if (sizeof($name_parts) == 3) {
					$variables["first_name"] = $name_parts[0];
					$variables["middle_name"] = $name_parts[1];
					$variables["last_name"] = $name_parts[2];
				} else if (sizeof($name_parts) == 2) {
					$variables["first_name"] = $name_parts[0];
					$variables["middle_name"] = "";
					$variables["last_name"] = $name_parts[1];
				} else {
					$variables["first_name"] = $name_parts[0];
					$variables["middle_name"] = "";
					$variables["last_name"] = "";
				}
			} elseif (!strlen($variables["name"]) && (strlen($variables["first_name"]) || strlen($variables["middle_name"]) || strlen($variables["last_name"]))) {
				$full_name = $variables["first_name"];
				if ($variables["middle_name"]) { $full_name .= " ".$variables["middle_name"];}
				if ($variables["last_name"]) { $full_name .= " ".$variables["last_name"];}
				$variables["name"] = trim($full_name);
			}

			$address = $r->get_value("address2") ? ($r->get_value("address1") . " " . $r->get_value("address2")) : $r->get_value("address1");
			if ($r->get_value("address3")) { $address .= " ".$r->get_value("address3"); }
			$delivery_address = $r->get_value("delivery_address2") ? ($r->get_value("delivery_address1") . " " . $r->get_value("delivery_address2")) : $r->get_value("delivery_address1");
			if ($r->get_value("delivery_address3")) { $delivery_address .= " ".$r->get_value("delivery_address3"); }
			$variables["address"] = $address;
			$variables["delivery_address"] = $delivery_address;
			$variables["company_select"] = get_array_value($r->get_value("company_id"), $companies);
			$variables["state_code"] = ""; 
			$sql = "SELECT * FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($variables["state_id"], INTEGER, true, false);
			$db->query($sql);
			if ($db->next_record()) {
				$variables["state_code"] = $db->f("state_code");
				$r->set_value("state_code", $variables["state_code"]);
			}
			$variables["state"] = $r->get_value("state_code");
			if (strlen($variables["state_code"])) {
				$variables["state_code_or_province"] = $variables["state_code"];
				$variables["state_or_province"] = $variables["state"];
			} else {
				$variables["state_code_or_province"] = $variables["province"];
				$variables["state_or_province"] = $variables["province"];
			}
			$variables["country"] = $bill_data["country_name"];

			$country_code = ""; $country_number = "";
			$sql = "SELECT * FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($variables["country_id"], INTEGER, true, false);
			$db->query($sql);
			if ($db->next_record()) {
				$country_code = $db->f("country_code");
				$country_number = $db->f("country_iso_number");
				$r->set_value("country_code", $country_code);
			}
			$variables["country_code"] = $country_code;
			$variables["country_number"] = $country_number;
			$variables["delivery_company_select"] = get_array_value($r->get_value("delivery_company_id"), $companies);
			$variables["delivery_state_code"] = ""; 
			$sql = "SELECT * FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($variables["delivery_state_id"], INTEGER, true, false);
			$db->query($sql);
			if ($db->next_record()) {
				$variables["delivery_state_code"] = $db->f("state_code");
				$r->set_value("delivery_state_code", $variables["delivery_state_code"]);
			}
			$variables["delivery_state"] = $variables["delivery_state_code"];

			if (strlen($variables["delivery_state_code"])) {
				$variables["delivery_state_code_or_province"] = $variables["delivery_state_code"];
				$variables["delivery_state_or_province"] = $variables["delivery_state"];
			} else {
				$variables["delivery_state_code_or_province"] = $variables["delivery_province"];
				$variables["delivery_state_or_province"] = $variables["delivery_province"];
			}
			$variables["delivery_country"] = $ship_data["country_name"]; 

			$delivery_country_code = ""; $delivery_country_number = "";
			$sql = "SELECT * FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($variables["delivery_country_id"], INTEGER, true, false);
			$db->query($sql);
			if ($db->next_record()) {
				$delivery_country_code = $db->f("country_code");
				$delivery_country_number = $db->f("country_iso_number");
				$r->set_value("delivery_country_code", $delivery_country_code);
			}
			$variables["delivery_country_code"] = $delivery_country_code;
			$variables["delivery_country_number"] = $delivery_country_number;
			$t->set_var("company_select", $variables["company_select"]);
			$t->set_var("state", $variables["state"]);
			$t->set_var("country", $variables["country"]);
			$t->set_var("delivery_company_select", $variables["delivery_company_select"]);
			$t->set_var("delivery_state", $variables["delivery_state"]);
			$t->set_var("delivery_country", $variables["delivery_country"]);

			// join phone code and phone number fields
			join_phone_fields();

			// set payment fields
			$cc_number = $r->get_value("cc_number");
			$cc_number_security = get_setting_value($payment_settings, "cc_number_security", 0);
			$cc_code_security = get_setting_value($payment_settings, "cc_code_security", 0);
			$cc_number_split = get_setting_value($payment_settings, "cc_number_split", 0);

			$cc_number = clean_cc_number($cc_number);
			$cc_number_len = strlen($cc_number);
			$cc_security_code = $r->get_value("cc_security_code");
			$r->set_value("cc_number", $cc_number);
			set_session("session_cc_number", $cc_number);
			set_session("session_cc_code",   $cc_security_code);
			if ($cc_number_len > 6) {
				$cc_number_first = substr($cc_number, 0, 6);
			} else {
				$cc_number_first = $cc_number;
			}
			if ($cc_number_len > 4) {
				$cc_number_last = substr($cc_number, $cc_number_len - 4);
				if ($cc_number_split) {
					$r->set_value("cc_number", substr($cc_number, 0, $cc_number_len - 4) . "****");
				}
			} else {
				$cc_number_last = $cc_number;
			}
			set_session("session_cc_number_first", $cc_number_first);
			set_session("session_cc_number_last", $cc_number_last);

			if ($cc_number_security == 0) {
				$r->set_value("cc_number", "");
			} elseif ($cc_number_security > 0) {
				$r->set_value("cc_number", va_encrypt($r->get_value("cc_number")));
			}

			if ($cc_code_security == 0) {
				$r->set_value("cc_security_code", "");
			} elseif ($cc_code_security > 0) {
				$r->set_value("cc_security_code", va_encrypt($cc_security_code));
			}
			// end of payment fields

			$r->set_value("order_total",  $order_total);
			$r->set_value("total_points_amount",  $total_points_amount);
			$r->set_value("total_reward_points",  $total_reward_points);
			$r->set_value("total_reward_credits",  $total_reward_credits);

			$variables["order_total"] = number_format($left_total * $payment_rate, $payment_decimals, ".", "");
			$variables["order_total_100"] = round($left_total * $payment_rate * 100, 0);
			$variables["total_points_amount"] = $total_points_amount;
			$variables["total_reward_points"] = $total_reward_points;
			$variables["total_reward_credits"] = $total_reward_credits;

			$variables["items"] = $items_text;
			$variables["basket"] = $items_text;
			$variables["description"] = $items_text;

			if ($user_order_id) {
				$order_placed_date = $user_order_placed_date;
			} else {
				$order_placed_date = va_time();
			}
			$order_placed_date_string = va_date($datetime_show_format, $order_placed_date);
			$order_placed_ts = mktime ($order_placed_date[HOUR], $order_placed_date[MINUTE], $order_placed_date[SECOND], $order_placed_date[MONTH], $order_placed_date[DAY], $order_placed_date[YEAR]);
			$timestamp = time();
			$va_timestamp = va_timestamp();

			$variables["timestamp"] = $timestamp;
			$variables["order_placed_timestamp"] = $order_placed_ts;
			$variables["order_placed_date"] = $order_placed_date;
			$r->set_value("order_placed_date", $order_placed_date);
			$order_added = true;
			// check if order was already placed
			if ($order_id) {
				$sql  = " SELECT o.transaction_id, o.is_placed, o.order_status, os.paid_status ";
				$sql .= " FROM (" . $table_prefix . "orders o ";
				$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
				$sql .= " WHERE o.order_id=" . $db->tosql($order_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$is_placed = $db->f("is_placed");
					$paid_status = $db->f("paid_status");
					$transaction_id = $db->f("transaction_id");
					if ($is_placed || $paid_status || strlen($transaction_id)) {
						$order_id = "";
						$user_order_id = "";
					}
				} else {
					$order_id = "";
					$user_order_id = "";
				}
			}

			// add new user
			if ($user_auto_add && !$user_id) {
				// get default user type
				$user_type_id = "";
				$sql  = " SELECT ut.type_id ";
				$sql .= " FROM (" . $table_prefix . "user_types ut"; 
				$sql .= " LEFT JOIN " . $table_prefix . "user_types_sites uts ON uts.type_id=ut.type_id)";
				$sql .= " WHERE (ut.sites_all=1 OR uts.site_id=". $db->tosql($site_id, INTEGER, true, false) . ")";
				$sql .= " AND ut.is_default=1 AND ut.is_active=1";
				$db->query($sql);
				if ($db->next_record()) {
					$user_type_id = $db->f("type_id");
				}

				// login generation
				$user_login = ""; $user_password = "";
				/*
				while ($user_login == "") {
					$random_value = mt_rand();
					$random_hash = strtoupper(md5($random_value.va_timestamp()));
					$user_login = substr($random_hash,0,8);
					$user_password = substr($random_hash,8,8);
					$sql = " SELECT user_id FROM " .$table_prefix. "users WHERE login=" . $db->tosql($user_login, TEXT);
					$db->query($sql);
					if ($db->next_record()) {
						$user_login = "";
					}
				}//*/

				$u = new VA_Record($table_prefix . "users");
				$u->add_where("user_id", INTEGER);
				$u->add_textbox("user_type_id", INTEGER);
				$u->add_textbox("is_approved", INTEGER);
				$u->add_textbox("registration_last_step", INTEGER);
				$u->add_textbox("registration_total_steps", INTEGER);
				$u->add_textbox("login", TEXT);
				$u->add_textbox("password", TEXT);
				// set values
				$u->set_value("user_type_id", $user_type_id);
				$u->set_value("is_approved", 1);
				$u->set_value("registration_last_step", 1);
				$u->set_value("registration_total_steps", 1);
				$u->set_value("login", $user_login);
				$u->set_value("password", $user_password);
				for ($i = 0; $i < sizeof($parameters); $i++) {	
					$param_name = $parameters[$i];
					$delivery_param = "delivery_" . $parameters[$i];
					$u->add_textbox($param_name, TEXT);
					$u->add_textbox($delivery_param, TEXT);
					$u->set_value($param_name, $r->get_value($param_name));
					$u->set_value($delivery_param, $r->get_value($delivery_param));
				}
				// update some property
				$u->change_property("login", USE_SQL_NULL, false);
				$u->change_property("password", USE_SQL_NULL, false);
				$u->change_property("name", USE_SQL_NULL, false);
				$u->change_property("first_name", USE_SQL_NULL, false);
				$u->change_property("middle_name", USE_SQL_NULL, false);
				$u->change_property("last_name", USE_SQL_NULL, false);
				$u->change_property("email", USE_SQL_NULL, false);
				$u->change_property("country_id", VALUE_TYPE, INTEGER);
				$u->change_property("state_id", VALUE_TYPE, INTEGER);
				$u->change_property("delivery_country_id", VALUE_TYPE, INTEGER);
				$u->change_property("delivery_state_id", VALUE_TYPE, INTEGER);

				$u->insert_record();
				$user_id = $db->last_insert_id();

				// save a new user id for order and in the session
				set_session("session_new_user_id", $user_id);
				$r->set_value("user_id", $user_id);
				$r->set_value("user_type_id", $user_type_id);
			}

			if ($order_id) {
				$variables["order_id"] = $order_id;
				$r->set_value("order_id", $order_id);
				if ($user_order_id) {
					// delete only payment related data for previously saved orders
					$sql  = " DELETE FROM " . $table_prefix . "orders_payments WHERE order_id=".$db->tosql($user_order_id, INTEGER);
					$sql .= " AND payment_status=0 AND payment_paid=0 ";
					$db->query($sql);
					$sql  = " DELETE FROM " . $table_prefix . "orders_properties WHERE order_id=".$db->tosql($user_order_id, INTEGER);
					$sql .= " AND property_type=4 ";
					$db->query($sql);
					$order_added = $r->update_record();
				} else {
					// remove all related order data for new orders with active shopping cart
					remove_orders($order_id, false);
					$order_added = $r->update_record();
				}
			} else {
				$order_added = $r->insert_record();
			}

			if ($order_added)
			{
				if (!$order_id) {
					$order_id = $db->last_insert_id();
					$r->set_value("order_id", $order_id);
					$variables["order_id"] = $order_id;
				}

				// increment used order coupons by one if they exists
				if (strlen($order_coupons_ids)) {
					$sql  = " UPDATE " . $table_prefix . "coupons SET coupon_uses=coupon_uses+1 ";
					$sql .= " WHERE coupon_id IN (" . $db->tosql($order_coupons_ids, INTEGERS_LIST) . ") ";
					$db->query($sql);
				}
				if (is_array($gift_vouchers) && count($gift_vouchers) > 0) {
					$ce = new VA_Record($table_prefix . "coupons_events");
					$ce->add_textbox("coupon_id", INTEGER);
					$ce->add_textbox("order_id", INTEGER);
					$ce->add_textbox("payment_id", INTEGER);
					$ce->add_textbox("transaction_id", TEXT);
					$ce->add_textbox("admin_id", INTEGER);
					$ce->add_textbox("user_id", INTEGER);
					$ce->add_textbox("from_user_id", INTEGER);
					$ce->add_textbox("to_user_id", INTEGER);
					$ce->add_textbox("event_date", DATETIME);
					$ce->add_textbox("event_type", TEXT);
					$ce->add_textbox("remote_ip", TEXT);
					$ce->add_textbox("coupon_amount", NUMBER);
	      
					foreach ($gift_vouchers as $voucher_id => $voucher_info) {
						$voucher_amount = $voucher_info["amount"];
						if ($voucher_amount > 0) {
							$sql  = " UPDATE " . $table_prefix . "coupons ";
							$sql .= " SET coupon_uses=coupon_uses+1, discount_amount=discount_amount-" . $db->tosql($voucher_amount, NUMBER);
							$sql .= " WHERE coupon_id=" . $db->tosql($voucher_id, INTEGER);
							$db->query($sql);

							$ce->set_value("coupon_id", $coupon_id);
							$ce->set_value("order_id", $order_id);
							$ce->set_value("payment_id", "");
							$ce->set_value("transaction_id", "");
							$ce->set_value("admin_id", get_session("session_admin_id"));
							$ce->set_value("user_id", get_session("session_user_id"));
							$ce->set_value("event_date", va_time());
							$ce->set_value("event_type", "voucher_used");
							$ce->set_value("remote_ip", get_ip());
							$ce->set_value("coupon_amount", -$voucher_amount);
							$ce->insert_record();
						}
					}
				}

				// save payment information 
				$payment_id = $r->get_value("payment_id");
				if ($payment_id) {
					$ops = new VA_Record($table_prefix . "orders_payments");
					$ops->add_where("order_payment_id", INTEGER);
					$ops->add_textbox("order_id", INTEGER);
					$ops->add_textbox("payment_id", INTEGER);
					$ops->add_textbox("payment_index", INTEGER);
					$ops->add_textbox("payment_amount", FLOAT);
					$ops->add_textbox("transaction_id", TEXT);
					$ops->add_textbox("success_message", TEXT);
					$ops->add_textbox("pending_message", TEXT);
					$ops->add_textbox("error_message", TEXT);
					$ops->add_textbox("remote_ip", TEXT);
					$ops->add_textbox("payment_currency_code", TEXT);
					$ops->add_textbox("payment_currency_rate", FLOAT);
					$ops->add_textbox("payment_status", INTEGER);
					$ops->add_textbox("payment_paid", INTEGER);
					$ops->add_textbox("processing_fee", FLOAT);
					$ops->add_textbox("processing_tax_free", INTEGER);
					$ops->add_textbox("processing_excl_tax", FLOAT);
					$ops->add_textbox("processing_tax", FLOAT);
					$ops->add_textbox("processing_incl_tax", FLOAT);
					// set values
					$sql  = " SELECT MAX(payment_index) FROM ".$table_prefix."orders_payments "; 
					$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER); 
					$payment_index = get_db_value($sql);
					$payment_index++;
					
					$ops->set_value("order_id", $order_id);
					$ops->set_value("payment_id", $r->get_value("payment_id"));
					$ops->set_value("payment_index", $payment_index);
					$ops->set_value("payment_amount", $r->get_value("payment_amount"));
					$ops->set_value("remote_ip", get_ip());
					$ops->set_value("payment_currency_code", $r->get_value("payment_currency_code"));
					$ops->set_value("payment_currency_rate", $r->get_value("payment_currency_rate"));
					$ops->set_value("payment_status", 0);
					$ops->set_value("payment_paid", 0);
					$ops->set_value("processing_fee", $processing_fee );
					$ops->set_value("processing_tax_free", $processing_tax_free);
					$ops->set_value("processing_excl_tax", $processing_excl_tax );
					$ops->set_value("processing_tax", $processing_tax );
					$ops->set_value("processing_incl_tax", $processing_incl_tax );

					$ops->insert_record();
					$order_payment_id = $db->last_insert_id();

					$sql  = " UPDATE " . $table_prefix . "orders ";
					$sql .= " SET order_payment_id=" . $db->tosql($order_payment_id, INTEGER);
					$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
					$db->query($sql);
				}

				// save tax rates only for new submitted order
				if (!$user_order_id && $tax_available && is_array($tax_rates)) {
					$ot = new VA_Record($table_prefix . "orders_taxes");
					$ot->add_where("order_tax_id", INTEGER);
					$ot->add_textbox("order_id", INTEGER);
					$ot->set_value("order_id", $order_id);
					$ot->add_textbox("tax_id", INTEGER);
					$ot->add_textbox("tax_type", INTEGER);
					$ot->add_textbox("show_type", INTEGER);
					$ot->add_textbox("tax_name", TEXT);
					$ot->add_textbox("tax_percent", FLOAT);
					$ot->add_textbox("fixed_amount", FLOAT);
					$ot->add_textbox("order_fixed_amount", FLOAT);
					$ot->add_textbox("shipping_tax_percent", FLOAT);
					$ot->add_textbox("shipping_fixed_amount", FLOAT);

					$oit = new VA_Record($table_prefix . "orders_items_taxes");
					$oit->add_textbox("order_tax_id", INTEGER);
					$oit->add_textbox("item_type_id", INTEGER);
					$oit->add_textbox("tax_percent", FLOAT);
					$oit->add_textbox("fixed_amount", FLOAT);

					foreach ($tax_rates as $tax_id => $tax_rate) {
						$ot->set_value("tax_id", $tax_id);
						$ot->set_value("tax_type", $tax_rate["tax_type"]);
						$ot->set_value("show_type", $tax_rate["show_type"]);
						$ot->set_value("tax_name", $tax_rate["tax_name"]);
						$ot->set_value("tax_percent", $tax_rate["tax_percent"]);
						$ot->set_value("fixed_amount", $tax_rate["fixed_amount"]);
						$ot->set_value("order_fixed_amount", $tax_rate["order_fixed_amount"]);
						$ot->set_value("shipping_tax_percent", $tax_rate["types"]["shipping"]["tax_percent"]);
						$ot->set_value("shipping_fixed_amount", $tax_rate["types"]["shipping"]["fixed_amount"]);
						if ($ot->insert_record()) {
							// save taxes for item types if they available
							$tax_types = isset($tax_rate["types"]) ? $tax_rate["types"] : "";
							if (is_array($tax_types)) {
								$order_tax_id = $db->last_insert_id();
								$oit->set_value("order_tax_id", $order_tax_id);
								foreach ($tax_types as $item_type => $tax_type) {
									if (is_numeric($item_type)) {
										$oit->set_value("item_type_id", $item_type);
										$oit->set_value("tax_percent", $tax_type["tax_percent"]);
										$oit->set_value("fixed_amount", $tax_type["fixed_amount"]);
										$oit->insert_record();
									}
								}
							}
						}
					} 
				} // end of saving order taxes rules

				// save selected shipping methods only for new orders
				if (!$user_order_id && is_array($shipping_groups) && sizeof($shipping_groups) > 0) {
					$os = new VA_Record($table_prefix . "orders_shipments");
					$os->add_where("order_shipping_id", INTEGER);
					$os->add_textbox("order_id", INTEGER);
					$os->set_value("order_id", $order_id);
					$os->add_textbox("shipping_id", INTEGER);
					$os->add_textbox("shipping_code", TEXT);
  				$os->add_textbox("shipping_desc", TEXT);
					$os->add_textbox("shipping_cost", FLOAT);
					$os->add_textbox("points_cost", FLOAT);
					$os->add_textbox("tax_free", INTEGER);
					$os->add_textbox("tracking_id", TEXT);
					$os->add_textbox("expecting_date", DATETIME);
					$os->add_textbox("goods_weight", FLOAT);
					$os->add_textbox("actual_goods_weight", FLOAT);
					$os->add_textbox("tare_weight", FLOAT);
					foreach ($shipping_groups as $group_id => $group_info) {
						if (isset($group_info["selected_type"]) && is_array($group_info["selected_type"])) {
							$shipping_type = $group_info["selected_type"];
							$items_ids = $group_info["items_ids"];

							$os->set_value("shipping_id", $shipping_type["shipping_id"]);
							$os->set_value("shipping_code", $shipping_type["shipping_code"]);
  						$os->set_value("shipping_desc", $shipping_type["shipping_desc"]);
							$os->set_value("shipping_cost", $shipping_type["shipping_cost"]);
							$os->set_value("points_cost", $shipping_type["points_cost"]);
							$os->set_value("tax_free", $shipping_type["tax_free"]);
							$os->set_value("tracking_id", "");
							$os->set_value("expecting_date", "");
							$os->set_value("goods_weight", $group_info["goods_weight"]);
							$os->set_value("actual_goods_weight", $group_info["actual_goods_weight"]);
							$os->set_value("tare_weight", $shipping_type["tare_weight"]);

							if ($os->insert_record()) {
								$order_shipping_id = $db->last_insert_id();
								// save order_shipping_id for shipping group to update order_items_ids field
								$shipping_groups[$group_id]["order_shipping_id"] = $order_shipping_id;
								$shipping_groups[$group_id]["items_ids"] = $items_ids;
								// check and update order_shipping_id information for cart items
								foreach ($cart_items as $id => $cart_item) {
									$item_id = $cart_item["item_id"];
									if (in_array($item_id, $items_ids)) {
										$cart_items[$id]["order_shipping_id"] = $order_shipping_id;
									}
								}
							}
						}
					}	
				}

				// subscribe/unsubscribe user from newsletter
				if ($subscribe_block) {
					$subscribe_email = $r->get_value("email");
					if (!$subscribe_email && $r->get_value("delivery_email")) {
						$subscribe_email = $r->get_value("delivery_email");
					}
					if ($subscribe_email) {
						if ($r->get_value("subscribe") == 1) {
							$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "newsletters_users ";
							$sql .= " WHERE email=" . $db->tosql($subscribe_email, TEXT);
							$sql .= " AND site_id=" . $db->tosql($site_id, INTEGER);
							$db->query($sql);
							$db->next_record();
							$email_count = $db->f(0);
							if ($email_count < 1) {
								$sql  = " INSERT INTO " . $table_prefix . "newsletters_users (site_id, email, date_added) ";
								$sql .= " VALUES (";
								$sql .= $db->tosql($site_id, INTEGER) . ", ";
								$sql .= $db->tosql($subscribe_email, TEXT) . ", ";
								$sql .= $db->tosql(va_time(), DATETIME) . ") ";
								$db->query($sql);
							}
						} else {
							$sql  = " DELETE FROM " . $table_prefix . "newsletters_users ";
							$sql .= " WHERE email=" . $db->tosql($subscribe_email, TEXT);
							$sql .= " AND site_id=" . $db->tosql($site_id, INTEGER);
							$db->query($sql);
						}
					}
				}

				$op = new VA_Record($table_prefix . "orders_properties");
				$op->add_textbox("order_id", INTEGER);
				$op->set_value("order_id", $order_id);
				$op->add_textbox("property_id", INTEGER);
				$op->add_textbox("property_order", INTEGER);
				$op->add_textbox("property_type", INTEGER);
				$op->add_textbox("property_code", TEXT);
				$op->add_textbox("property_name", TEXT);
				$op->add_textbox("property_value_id", INTEGER);
				$op->add_textbox("property_value", TEXT);
				$op->add_textbox("property_notes", TEXT);
				$op->add_textbox("property_price", FLOAT);
				$op->add_textbox("property_points_amount", FLOAT);
				$op->add_textbox("property_weight", FLOAT);
				$op->add_textbox("actual_weight", FLOAT);
				$op->add_textbox("tax_free", INTEGER);
				foreach ($custom_options as $property_id => $property_values) {
          $property_full_desc = ""; $property_total_price = 0;
					foreach ($property_values as $value_id => $value_data) {
						$property_type = $value_data["type"];
						$op_payment_id = $value_data["payment_id"];
						if ($op_payment_id && $op_payment_id != $payment_id) {
							// ignore option and move to the next
							continue;
						}
						$property_order = $value_data["order"];
						$property_code = $value_data["code"];
						$property_name = $value_data["name"];
						$property_notes = $value_data["notes"];
						$property_value_id = $value_data["value_id"];
						$property_value = $value_data["value"];
						$property_price = $value_data["price"];
						$property_tax_free = $value_data["tax_free"];
						$property_points_price = $value_data["points_price"];
						$property_pay_points = $value_data["pay_points"];
						if ($property_pay_points) {
							$property_price = 0;
						} else {
							$property_points_price = 0;
						}
						if ($property_full_desc) { $property_full_desc .= "; "; }
						$property_full_desc .= $property_value;
						$property_total_price += $property_price;
				  
						$op->set_value("property_id", $property_id);
						$op->set_value("property_order", $property_order);
						$op->set_value("property_type", $property_type);
						$op->set_value("property_code", $property_code);
						$op->set_value("property_name", $property_name);
						$op->set_value("property_value_id", $property_value_id);
						$op->set_value("property_value", $property_value);
						$op->set_value("property_price", $property_price);
						$op->set_value("property_notes", $property_notes);
						$op->set_value("property_points_amount", $property_points_price);
						$op->set_value("property_weight", 0);
						$op->set_value("actual_weight", 0);
						$op->set_value("tax_free", $property_tax_free);
				  
						$op->insert_record();
					}
					$t->set_var("field_name_" . $property_id, $property_name);
					$t->set_var("field_value_" . $property_id, $property_full_desc);
					$t->set_var("field_price_" . $property_id, $property_total_price);
					$t->set_var("field_" . $property_id, $property_full_desc);
				}

				// save order coupons
				$oc = new VA_Record($table_prefix . "orders_coupons");
				$oc->add_textbox("order_id", INTEGER);
				$oc->set_value("order_id", $order_id);
				$oc->add_textbox("order_item_id", INTEGER);
				$oc->set_value("order_item_id", 0);
				$oc->add_textbox("coupon_id", INTEGER);
				$oc->add_textbox("coupon_code", TEXT);
				$oc->add_textbox("coupon_title", TEXT);
				$oc->add_textbox("discount_type", INTEGER);
				$oc->add_textbox("discount_amount", FLOAT);
				$oc->add_textbox("discount_tax_amount", FLOAT);
				for ($i = 0; $i < sizeof($order_coupons); $i++)
				{
					$order_coupon = $order_coupons[$i];
					$oc->set_value("coupon_id", $order_coupon["coupon_id"]);
					$oc->set_value("coupon_code", $order_coupon["coupon_code"]);
					$oc->set_value("coupon_title", $order_coupon["coupon_title"]);
					$oc->set_value("discount_type", 2);
					$oc->set_value("discount_amount", $order_coupon["discount_amount"]);
					$oc->set_value("discount_tax_amount", $order_coupon["discount_tax_amount"]);
					$oc->insert_record();
				}
				foreach ($gift_vouchers as $voucher_id => $voucher_info)
				{
					if (isset($voucher_info["amount"]) && $voucher_info["amount"] > 0) {
						$oc->set_value("coupon_id", $voucher_id);
						$oc->set_value("coupon_code", $voucher_info["code"]);
						$oc->set_value("coupon_title", $voucher_info["title"]);
						$oc->set_value("discount_type", 5);
						$oc->set_value("discount_amount", $voucher_info["amount"]);
						$oc->set_value("discount_tax_amount", 0);
						$oc->insert_record();
					}
				}


				$oi = new VA_Record($table_prefix . "orders_items");
				$oi->add_where("order_item_id", INTEGER);
				$oi->add_textbox("order_id", INTEGER);
				$oi->set_value("order_id", $order_id);

				$oi->add_textbox("site_id", INTEGER);
				$oi->change_property("site_id", USE_SQL_NULL, false);
				if (isset($site_id)) {
					$oi->set_value("site_id", $site_id);
				} else {
					$oi->set_value("site_id", 1);
				}

				$oi->add_textbox("top_order_item_id", INTEGER);
				$oi->change_property("top_order_item_id", USE_SQL_NULL, false);
				$oi->add_textbox("user_id", INTEGER);
				$oi->set_value("user_id", $user_id);
				$oi->add_textbox("user_type_id", INTEGER);
				$oi->set_value("user_type_id", $user_type_id);
				$oi->add_textbox("item_id", INTEGER);
				$oi->add_textbox("parent_item_id", INTEGER);
				$oi->add_textbox("cart_item_id", INTEGER);
				$oi->change_property("cart_item_id", USE_SQL_NULL, false);
				$oi->add_textbox("item_user_id", INTEGER);
				$oi->change_property("item_user_id", USE_SQL_NULL, false);
				$oi->add_textbox("affiliate_user_id", INTEGER);
				$oi->change_property("affiliate_user_id", USE_SQL_NULL, false);
				$oi->add_textbox("friend_user_id", INTEGER);
				$oi->change_property("friend_user_id", USE_SQL_NULL, false);
				$oi->add_textbox("item_type_id", INTEGER);
				$oi->add_textbox("supplier_id", INTEGER);
				$oi->add_textbox("item_code", TEXT);
				$oi->add_textbox("manufacturer_code", TEXT);
				$oi->add_textbox("coupons_ids", TEXT);
				$oi->add_textbox("item_status", INTEGER);
				$oi->set_value("item_status", 0);
				$oi->add_textbox("component_order", INTEGER);
				$oi->add_textbox("component_name", TEXT);
				$oi->add_textbox("item_name", TEXT);
				$oi->add_textbox("item_properties", TEXT);
				$oi->add_textbox("buying_price", NUMBER);
				$oi->add_textbox("real_price", NUMBER);
				$oi->add_textbox("discount_amount", NUMBER);
				$oi->add_textbox("price", NUMBER);
				$oi->add_textbox("tax_id", INTEGER);
				$oi->add_textbox("tax_free", INTEGER);
				$oi->add_textbox("tax_percent", NUMBER);
				$oi->add_textbox("points_price", NUMBER);
				$oi->add_textbox("reward_points", NUMBER);
				$oi->add_textbox("reward_credits", NUMBER);
				$oi->add_textbox("merchant_commission", NUMBER);
				$oi->add_textbox("affiliate_commission", NUMBER);
				$oi->add_textbox("packages_number", NUMBER);
				$oi->add_textbox("weight", NUMBER);
				$oi->add_textbox("actual_weight", NUMBER);
				$oi->add_textbox("width", NUMBER);
				$oi->add_textbox("height", NUMBER);
				$oi->add_textbox("length", NUMBER);
				$oi->add_textbox("quantity", NUMBER);
				$oi->add_textbox("downloadable", NUMBER);
				$oi->add_textbox("is_shipping_free", INTEGER);
				$oi->add_textbox("shipping_cost", NUMBER);
				$oi->add_textbox("order_shipping_id", NUMBER);
				// recurring fields
				$oi->add_textbox("is_recurring", INTEGER);
				$oi->add_textbox("recurring_price", NUMBER);
				$oi->add_textbox("recurring_period", INTEGER);
				$oi->add_textbox("recurring_interval", INTEGER);
				$oi->add_textbox("recurring_payments_total", INTEGER);
				$oi->add_textbox("recurring_payments_made", INTEGER);
				$oi->add_textbox("recurring_payments_failed", INTEGER);
				$oi->add_textbox("recurring_end_date", DATETIME);
				$oi->add_textbox("recurring_last_payment", DATETIME);
				$oi->add_textbox("recurring_next_payment", DATETIME);
				$oi->add_textbox("recurring_plan_payment", DATETIME);
				// recurring fields
				$oi->add_textbox("is_subscription", INTEGER);
				$oi->add_textbox("is_account_subscription", INTEGER);
				$oi->add_textbox("subscription_id", INTEGER);
				$oi->change_property("subscription_id", USE_SQL_NULL, false);
				$oi->add_textbox("subscription_period",   INTEGER);
				$oi->add_textbox("subscription_interval", INTEGER);
				$oi->add_textbox("subscription_suspend",  INTEGER);
				
				$oip = new VA_Record($table_prefix . "orders_items_properties");
				$oip->add_textbox("order_id", INTEGER);
				$oip->set_value("order_id", $order_id);
				$oip->add_textbox("order_item_id", INTEGER);
				$oip->add_textbox("property_id", INTEGER);
				$oip->add_textbox("property_order", INTEGER);
				$oip->add_textbox("property_name", TEXT);
				$oip->add_textbox("hide_name", TEXT);
				$oip->add_textbox("property_value", TEXT);
				$oip->add_textbox("property_values_ids", TEXT);
				$oip->add_textbox("additional_price", NUMBER);
				$oip->add_textbox("additional_weight", NUMBER);
				$oip->add_textbox("actual_weight", NUMBER);
				$oip->add_textbox("length_units", TEXT);

				$r_id = new VA_Record($table_prefix . "items_downloads");
				$r_id->add_textbox("order_id", INTEGER);
				$r_id->set_value("order_id", $order_id);
				$r_id->add_textbox("user_id", INTEGER);
				$r_id->set_value("user_id", $user_id);
				$r_id->add_textbox("order_item_id", INTEGER);
				$r_id->add_textbox("item_id", INTEGER);
				$r_id->add_textbox("download_path", TEXT);
				$r_id->add_textbox("activated", INTEGER);
				$r_id->add_textbox("max_downloads", INTEGER); // how many times from different IPs user can download product during the month
				$r_id->add_textbox("download_added", DATETIME);
				$r_id->add_textbox("download_expiry", DATETIME);
				$r_id->add_textbox("download_limit", INTEGER); // how many times user can download product

				$ois = new VA_Record($table_prefix . "orders_items_serials");
				$ois->add_textbox("order_id", INTEGER);
				$ois->set_value("order_id", $order_id);
				$ois->add_textbox("user_id", INTEGER);
				$ois->set_value("user_id", $user_id);
				$ois->add_textbox("order_item_id", INTEGER);
				$ois->add_textbox("item_id", INTEGER);
				$ois->add_textbox("serial_number", TEXT);
				$ois->add_textbox("activated", INTEGER);
				$ois->add_textbox("activations_number", INTEGER);
				$ois->add_textbox("serial_added", DATETIME);
				$ois->add_textbox("serial_expiry", DATETIME);

				$sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
				$sql .= " WHERE setting_type='download_info' ";
				$sql .= " AND setting_name='max_downloads' ";
				if (isset($site_id)) {
					$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
					$sql .= " ORDER BY site_id DESC ";
				} else {
					$sql .= " AND site_id=1 ";
				}
				$max_downloads = get_db_value($sql);

				foreach ($cart_items as $id => $cart_item) {
					$cart_item = $cart_items[$id]; // get array value again as it could be updated by previous records
					$item_id = $cart_item["item_id"];
					$parent_item_id = $cart_item["parent_item_id"];
					$top_order_item_id = isset($cart_items[$id]["top_order_item_id"]) ? $cart_items[$id]["top_order_item_id"] : 0;
					$wishlist_item_id = $cart_item["wishlist_item_id"];
					$item_user_id = $cart_item["item_user_id"];
					$item_type_id = $cart_item["item_type_id"];
					$supplier_id = $cart_item["supplier_id"];
					$item_code = $cart_item["item_code"];
					$manufacturer_code = $cart_item["manufacturer_code"];
					$item_coupons_ids = $cart_item["coupons_ids"];
					$coupons_applied = isset($cart_item["coupons_applied"]) ? $cart_item["coupons_applied"] : "";
					$component_order = $cart_item["selection_order"];
					$component_name = $cart_item["selection_name"];
					$item_name = $cart_item["item_name"];
					$properties_info = $cart_item["properties_info"];
					$buying_price = $cart_item["full_buying_price"];
					$price = $cart_item["full_price"];
					$price_incl_tax = $cart_item["price_incl_tax"];
					$real_price = $cart_item["full_real_price"];
					$item_discount = $real_price - $price;

					// check points options
					$pay_points = $cart_item["pay_points"];
					$reward_points = $cart_item["reward_points"];
					$reward_credits = $cart_item["reward_credits"];
					$points_price = $cart_item["points_price"];
					$merchant_commission = $cart_item["merchant_commission"];
					$affiliate_commission = $cart_item["affiliate_commission"];
					if ($pay_points) {
						$price = 0;
						$merchant_commission = 0; $affiliate_commission = 0;
						if (!$points_for_points) { $reward_points = 0; }
						if (!$credits_for_points) { $reward_credits = 0; }
					} else {
						$points_price = 0;
					}

					$item_tax_id = $cart_item["tax_id"];
					$item_tax_free = $cart_item["tax_free"];
					$item_tax_percent = $cart_item["tax_percent"];
					if ($tax_free) {
						$item_tax_percent = 0;
					}
					$packages_number = $cart_item["packages_number"];
					$weight = $cart_item["full_weight"];
					$actual_weight = $cart_item["full_actual_weight"];
					$width = $cart_item["width"];
					$height = $cart_item["height"];
					$length = $cart_item["length"];
					$quantity = $cart_item["quantity"];
					$stock_level = $cart_item["stock_level"];
					$availability_time = $cart_item["availability_time"];
					$downloads = $cart_item["downloads"];
					$generate_serial = $cart_item["generate_serial"];
					$serial_period = $cart_item["serial_period"];
					$activations_number = $cart_item["activations_number"];
					$is_gift_voucher = $cart_item["is_gift_voucher"];
					$is_user_voucher = $cart_item["is_user_voucher"];
					$downloadable = $cart_item["downloadable"];
					$is_shipping_free = $cart_item["is_shipping_free"];
					$shipping_cost = $cart_item["shipping_cost"];
					$order_shipping_id = isset($cart_item["order_shipping_id"]) ? $cart_item["order_shipping_id"] : "";

					//recurring fields
					$is_recurring = $cart_item["is_recurring"];
					$recurring_price = $cart_item["recurring_price"];
					$recurring_period = $cart_item["recurring_period"];
					$recurring_interval = $cart_item["recurring_interval"];
					$recurring_payments_total = $cart_item["recurring_payments_total"];
					$recurring_start_date = $cart_item["recurring_start_date"];
					$recurring_end_date = $cart_item["recurring_end_date"];

					$is_subscription = $cart_item["is_subscription"];
					$is_account_subscription = $cart_item["is_account_subscription"];
					$subscription_id = isset($cart_item["subscription_id"]) ? $cart_item["subscription_id"] : "";
					$subscription_period = $cart_item["subscription_period"];
					$subscription_interval = $cart_item["subscription_interval"];
					$subscription_suspend = $cart_item["subscription_suspend"];

					$components = isset($cart_item["components"]) ? $cart_item["components"] : "";

					$oi->set_value("top_order_item_id", $top_order_item_id);
					$oi->set_value("item_id", $item_id);
					$oi->set_value("parent_item_id", $parent_item_id);
					$oi->set_value("cart_item_id", $wishlist_item_id);
					$oi->set_value("item_user_id", $item_user_id);
					$oi->set_value("affiliate_user_id", $affiliate_user_id);
					$oi->set_value("friend_user_id", $friend_user_id);
					$oi->set_value("item_type_id", $item_type_id);
					$oi->set_value("supplier_id", $supplier_id);
					$oi->set_value("item_code", $item_code);
					$oi->set_value("manufacturer_code", $manufacturer_code);
					$oi->set_value("coupons_ids", $item_coupons_ids);
					$oi->set_value("component_order", $component_order);
					$oi->set_value("component_name", $component_name);
					$oi->set_value("item_name", $item_name);
					$oi->set_value("buying_price", $buying_price);
					$oi->set_value("real_price", $real_price);
					$oi->set_value("discount_amount", $item_discount);
					$oi->set_value("price", $price);
					$oi->set_value("tax_id", $item_tax_id);
					$oi->set_value("tax_free", $item_tax_free);
					$oi->set_value("tax_percent", $item_tax_percent);
					$oi->set_value("points_price", $points_price);
					$oi->set_value("reward_points", $reward_points);
					$oi->set_value("reward_credits", $reward_credits);

					$oi->set_value("merchant_commission", $merchant_commission);
					$oi->set_value("affiliate_commission", $affiliate_commission);
					$oi->set_value("packages_number", $packages_number);
					$oi->set_value("weight", $weight);
					$oi->set_value("actual_weight", $actual_weight);
					$oi->set_value("width", $width);
					$oi->set_value("height", $height);
					$oi->set_value("length", $length);
					$oi->set_value("quantity", $quantity);
					$oi->set_value("downloadable", $downloadable);
					$oi->set_value("is_shipping_free", $is_shipping_free);
					$oi->set_value("shipping_cost", $shipping_cost);
					$oi->set_value("order_shipping_id", $order_shipping_id);

					// set subscription fields
					$oi->set_value("is_subscription", $is_subscription);
					$oi->set_value("is_account_subscription", $is_account_subscription);
					$oi->set_value("subscription_id", $subscription_id);
					$oi->set_value("subscription_period",   $subscription_period);
					$oi->set_value("subscription_interval", $subscription_interval);
					$oi->set_value("subscription_suspend",  $subscription_suspend);

					// set recurring payments
					$oi->set_value("is_recurring", $is_recurring);
					$oi->set_value("recurring_price", $recurring_price);
					$oi->set_value("recurring_payments_made", 0);
					if ($is_recurring) {
						$current_date = va_time();
						$current_ts = mktime (0, 0, 0, $current_date[MONTH], $current_date[DAY], $current_date[YEAR]);
						$recurring_next_payment = 0; $recurring_end_ts = 0;
						if (is_array($recurring_start_date)) {
							$recurring_start_ts = mktime (0, 0, 0, $recurring_start_date[MONTH], $recurring_start_date[DAY], $recurring_start_date[YEAR]);
							if ($recurring_start_ts > $current_ts) {
								$recurring_next_payment = $recurring_start_ts;
							}
						}
						if (!$recurring_next_payment) {
							if ($recurring_period == 1) {
								$recurring_next_payment = mktime (0, 0, 0, $current_date[MONTH], $current_date[DAY] + $recurring_interval, $current_date[YEAR]);
							} elseif ($recurring_period == 2) {
								$recurring_next_payment = mktime (0, 0, 0, $current_date[MONTH], $current_date[DAY] + ($recurring_interval * 7), $current_date[YEAR]);
							} elseif ($recurring_period == 3) {
								$recurring_next_payment = mktime (0, 0, 0, $current_date[MONTH] + $recurring_interval, $current_date[DAY], $current_date[YEAR]);
							} else {
								$recurring_next_payment = mktime (0, 0, 0, $current_date[MONTH], $current_date[DAY], $current_date[YEAR] + $recurring_interval);
							}
						}
						if (is_array($recurring_end_date)) {
							$recurring_end_ts = mktime (0, 0, 0, $recurring_end_date[MONTH], $recurring_end_date[DAY], $recurring_end_date[YEAR]);
							if ($recurring_next_payment > $recurring_end_ts) {
								$recurring_next_payment = 0;
							}
						}

						$oi->set_value("recurring_period", $recurring_period);
						$oi->set_value("recurring_interval", $recurring_interval);
						$oi->set_value("recurring_payments_total", $recurring_payments_total);
						$oi->set_value("recurring_end_date", $recurring_end_date);
						if ($recurring_next_payment) {
							$oi->set_value("recurring_next_payment", $recurring_next_payment);
							$oi->set_value("recurring_plan_payment", $recurring_next_payment);
						}
					}

					// add products and their options
					if ($oi->insert_record())
					{
						$order_item_id = $db->last_insert_id();
						$oi->set_value("order_item_id", $order_item_id);
						$cart_items[$id]["order_item_id"] = $order_item_id;

						// increment used product coupons by one
						if (strlen($item_coupons_ids)) {
							$sql  = " UPDATE " . $table_prefix . "coupons SET coupon_uses=coupon_uses+1 ";
							$sql .= " WHERE coupon_id IN (" . $db->tosql($item_coupons_ids, INTEGERS_LIST) . ") ";
							$db->query($sql);
						}
						// add items coupons
						
						if (is_array($coupons_applied) && sizeof($coupons_applied) > 0) {
							foreach ($coupons_applied as $coupon_id => $coupon_info) {
								$oc->set_value("order_item_id", $order_item_id);
								$oc->set_value("coupon_id", $coupon_info["id"]);
								$oc->set_value("coupon_code", $coupon_info["code"]);
								$oc->set_value("coupon_title", $coupon_info["title"]);
								$oc->set_value("discount_type", $coupon_info["type"]);
								$oc->set_value("discount_amount", $coupon_info["discount"]);
								$oc->set_value("discount_tax_amount", $coupon_info["discount"]);
								$oc->insert_record();
							}
						}

						// update components with order_item_id for their main product
						if (is_array($components) && sizeof($components) > 0) {
							for ($c = 0; $c < sizeof($components); $c++) {
								$cc_id = $components[$c];
								$cart_items[$cc_id]["top_order_item_id"] = $order_item_id;
							}
						}

						// add download link
						if (is_array($downloads) && sizeof($downloads) > 0) {
							$current_date = va_time();
							foreach ($downloads as $file_id => $download) {
								$download_path = $download["download_path"];
								if ($download_path) {
									$r_id->set_value("order_item_id", $order_item_id);
									$r_id->set_value("item_id", $item_id);
									$r_id->set_value("download_path", $download_path);
									$r_id->set_value("activated", 0);

									$download_period = $download["download_period"];
									$download_interval = $download["download_interval"];
									$download_limit = $download["download_limit"];
									$r_id->set_value("max_downloads", $max_downloads * $quantity);
									$r_id->set_value("download_added", va_time());
									if ($download_interval) {
										if ($download_period == 1) {
											$download_expiry = mktime (0, 0, 0, $current_date[MONTH], $current_date[DAY] + $download_interval, $current_date[YEAR]);
										} elseif ($download_period == 2) {
											$download_expiry = mktime (0, 0, 0, $current_date[MONTH], $current_date[DAY] + ($download_interval * 7), $current_date[YEAR]);
										} elseif ($download_period == 3) {
											$download_expiry = mktime (0, 0, 0, $current_date[MONTH] + $download_interval, $current_date[DAY], $current_date[YEAR]);
										} else {
											$download_expiry = mktime (0, 0, 0, $current_date[MONTH], $current_date[DAY], $current_date[YEAR] + $download_interval);
										}
										$r_id->set_value("download_expiry", $download_expiry);
									}
									if (strlen($download_limit)) {
										$r_id->set_value("download_limit", $download_limit * $quantity);
									} else {
										$r_id->set_value("download_limit", "");
									}
									$r_id->insert_record();
								}
							}
						}

						if ($generate_serial) {
							for ($sn = $quantity; $sn > 0; $sn--) {
								$serial_number = generate_serial($order_item_id, $sn, $cart_item, $generate_serial);
								if ($serial_number) {
									$ois->set_value("order_item_id", $order_item_id);
									$ois->set_value("item_id", $item_id);
									$ois->set_value("serial_number", $serial_number);
									$ois->set_value("activated", 0);
									$ois->set_value("activations_number", $activations_number);
									$ois->set_value("serial_added", va_time());
									if (strlen($serial_period)) {
										$serial_expiry =  va_timestamp() + (intval($serial_period) * 86400);
										$ois->set_value("serial_expiry", va_time($serial_expiry));
									}
									$ois->insert_record();
								}
							}
						}

						if ($is_gift_voucher) {
							for ($gf = $quantity; $gf > 0; $gf--) {
								$gift_voucher = generate_voucher($order_id, $order_item_id, $user_id, $item_name, $price_incl_tax, 5);
							}
						} else if ($is_user_voucher) {
							for ($gf = $quantity; $gf > 0; $gf--) {
								$gift_voucher = generate_voucher($order_id, $order_item_id, $user_id, $item_name, $price_incl_tax, 8);
							}
						}
				
						// add properties
						if (is_array($properties_info) && sizeof($properties_info) > 0) {
							$oip->set_value("order_item_id", $order_item_id);
							for ($pi = 0; $pi < sizeof($properties_info); $pi++) {
								list($property_id, $control_type, $property_name, $hide_name, $property_value, $pr_add_price, $pr_add_weight, $pr_actual_weight, $pr_values, $property_order, $length_units) = $properties_info[$pi];
								if ($control_type == "WIDTH_HEIGHT") {
									// add main property
									$oip->set_value("property_id", $property_id);
									$oip->set_value("property_order", $property_order);
									$oip->set_value("property_name", $property_name);
									$oip->set_value("hide_name", $hide_name);
									$oip->set_value("property_value", $property_value);
									$oip->set_value("property_values_ids", "");
									$oip->set_value("additional_price", $pr_add_price);
									$oip->set_value("additional_weight", $pr_add_weight);
									$oip->set_value("actual_weight", $pr_actual_weight);
									$oip->set_value("length_units", $length_units);
									$oip->insert_record();
									// add width and height as subproperties 
									$oip->set_value("hide_name", 0);
									$oip->set_value("property_values_ids", "");
									$oip->set_value("additional_price", "");
									$oip->set_value("additional_weight", "");
									$oip->set_value("actual_weight", "");
									$oip->set_value("property_id", $property_id);
									$oip->set_value("property_order", $property_order);
									$oip->set_value("property_name", WIDTH_MSG);
									$oip->set_value("property_value", $pr_values["width"]);
									$oip->insert_record();
									$oip->set_value("property_id", $property_id);
									$oip->set_value("property_order", $property_order);
									$oip->set_value("property_name", HEIGHT_MSG);
									$oip->set_value("property_value", $pr_values["height"]);
									$oip->insert_record();
								} else {
									if ($control_type == "TEXTBOXLIST") {
										// for text boxes list save all data in property value 
										$property_values_ids = ""; $property_values_text = "";
										for ($pv = 0; $pv < sizeof($pr_values); $pv++) {
											list($item_property_id, $pr_value, $pr_value_text, $pr_use_stock, $pr_hide_out_stock, $pr_stock_level) = $pr_values[$pv];
											if ($property_values_ids) { 
												$property_values_ids .= ","; 
											}
											$property_values_text .= "<br>" . $pr_value . ": " . $pr_value_text;
											$property_values_ids .= $item_property_id;
										}
										$property_value = $property_values_text;
									} else {
										// get all property values ids
										$property_values_ids = "";
										for ($pv = 0; $pv < sizeof($pr_values); $pv++) {
											list($item_property_id, $pr_value, $pr_value_text, $pr_use_stock, $pr_hide_out_stock, $pr_stock_level) = $pr_values[$pv];
											if ($property_values_ids) { $property_values_ids .= ","; }
											$property_values_ids .= $item_property_id;
										}
							    
									}
									$oip->set_value("property_id", $property_id);
									$oip->set_value("property_order", $property_order);
									$oip->set_value("property_name", $property_name);
									$oip->set_value("hide_name", $hide_name);
									$oip->set_value("property_value", $property_value);
									$oip->set_value("property_values_ids", $property_values_ids);
									$oip->set_value("additional_price", $pr_add_price);
									$oip->set_value("additional_weight", $pr_add_weight);
									$oip->set_value("actual_weight", $pr_actual_weight);
									$oip->set_value("length_units", $length_units);
									$oip->insert_record();
								}
							}
						}
					} // end of adding products
				}

				// update order_items_ids field for order shipment
				foreach ($shipping_groups as $group_id => $group_info) {
					if (isset($group_info["order_shipping_id"])) {
						$order_shipping_id = $group_info["order_shipping_id"];
						$items_ids = $group_info["items_ids"];
						$order_items_ids = "";
						// check and update order_shipping_id information for cart items
						foreach ($cart_items as $id => $cart_item) {
							$item_id = $cart_item["item_id"];
							if (in_array($item_id, $items_ids)) {
								if ($order_items_ids) { $order_items_ids .= ","; }
								$order_items_ids .= $cart_item["order_item_id"];
							}
						}
						$sql  = " UPDATE " . $table_prefix . "orders_shipments ";
						$sql .= " SET order_items_ids=" . $db->tosql($order_items_ids, TEXT);
						$sql .= " WHERE order_shipping_id=" . $db->tosql($order_shipping_id, INTEGER);
						$db->query($sql);
					}
				}


				// clear credit card data before sending emails
				$t->set_var("cc_number", "");
				$t->set_var("cc_number_first", "");
				$t->set_var("cc_number_last", "");
				$t->set_var("cc_security_code", "");
				$t->set_var("cc_type", "");
				$t->set_var("cc_start_date", "");
				$t->set_var("cc_expiry_date", "");
				$t->set_var("cc_issue_number", "");

				// set initial order status NEW or QUOTE
				$sql = " SELECT status_id FROM " . $table_prefix . "order_statuses ";
				if ($checkout_flow == "quote" || $checkout_flow == "quote_request") {
					$sql .= " WHERE status_type='QUOTE_REQUEST' ";
				} else {
					$sql .= " WHERE status_type='NEW' ";
				}
				$initial_order_status = get_db_value($sql);
				// update status for new orders only and if appropriate status exists
				if ($initial_order_status) {
					if ($checkout_flow == "quote" || $checkout_flow == "quote_request" || !$user_order_id) {
						update_order_status($order_id, $initial_order_status, true, "", $status_error);
					}
				}

				// check different order notification settings
				$order_admin_email = $order_info["admin_email"] ? $order_info["admin_email"] : $settings["admin_email"];
				$admin_notification = get_setting_value($order_info, "admin_notification", 0);
				$user_notification  = get_setting_value($order_info, "user_notification", 0);
				$admin_sms = get_setting_value($order_info, "admin_sms_notification", 0);
				$user_sms  = get_setting_value($order_info, "user_sms_notification", 0);
				$admin_payment_notification = get_setting_value($payment_settings, "admin_notification", 0);
				$admin_payment_sms = get_setting_value($payment_settings, "admin_sms_notification", 0);

				if ($admin_notification || $user_notification || $admin_sms || $user_sms || $admin_payment_notification)
				{
					$r->set_parameters();
					// clear cc parameters
					$t->set_var("cc_name", "");
					$t->set_var("cc_first_name", "");
					$t->set_var("cc_last_name", "");
					$t->set_var("cc_number", "");
					$t->set_var("cc_start_date", "");
					$t->set_var("cc_expiry_date", "");
					$t->set_var("cc_type", "");
					$t->set_var("cc_issue_number", "");
					$t->set_var("cc_security_code", "");

					$t->set_var("goods_total", currency_format($goods_total));
					$t->set_var("goods_tax_total", currency_format($goods_tax_total));
					$t->set_var("total_discount", " -" . currency_format($total_discount));
					$t->set_var("shipping_cost", currency_format($total_shipping_cost));
					$t->set_var("shipping_points_amount", number_format($total_shipping_points_cost, $points_decimals));

					$t->set_var("tax_percent", number_format($tax_percent_sum, 3) . "%");
					$t->set_var("order_total", currency_format($order_total));
					$t->set_var("total_points_amount", number_format($total_points_amount, $points_decimals));
					$t->set_var("total_reward_points", number_format($total_reward_points, $points_decimals));
					$t->set_var("total_reward_credits", currency_format($total_reward_credits));

					$t->set_var("order_placed_date", $order_placed_date_string);

					$admin_message = get_setting_value($order_info, "admin_message", "");
					$admin_mail_type = get_setting_value($order_info, "admin_message_type");
					$user_message = get_setting_value($order_info, "user_message", "");
					$user_mail_type = get_setting_value($order_info, "user_message_type");

					$admin_payment_mail_type = get_setting_value($payment_settings, "admin_message_type");
					$admin_payment_message = get_setting_value($payment_settings, "admin_message", "");
					$admin_payment_sms_message    = get_setting_value($payment_settings, "admin_sms_message", "");

					// parse basket template
					if (($admin_notification && $admin_mail_type && strpos($admin_message, "{basket}") !== false) 
						|| ($user_notification && $user_mail_type && strpos($user_message, "{basket}") !== false)
						|| ($admin_payment_notification && $admin_payment_mail_type && strpos($admin_payment_message, "{basket}") !== false))
					{
						$t->set_file("basket_html", "email_basket.html");
						show_order_items($order_id, true, "email");
						$t->parse("basket_html", false);
					}
					if (($admin_notification && !$admin_mail_type && strpos($admin_message, "{basket}") !== false) 
						|| ($user_notification && !$user_mail_type && strpos($user_message, "{basket}") !== false)
						|| ($admin_payment_notification && !$admin_payment_mail_type && strpos($admin_payment_message, "{basket}") !== false))
					{
						$t->set_file("basket_text", "email_basket.txt");
						show_order_items($order_id, true, "email");
						$t->parse("basket_text", false);
					}
					// preparing downloadable data
					// get download links
					$links = get_order_links($order_id);
					// get serial numbers
					$order_serials = get_serial_numbers($order_id);
					// get gift vouchers
					$order_vouchers = get_gift_vouchers($order_id);
				}

				if ($admin_notification)
				{
					$admin_subject = get_setting_value($order_info, "admin_subject", "");
					$admin_subject = get_translation($admin_subject);
					$admin_message = get_currency_message(get_translation($admin_message), $currency);

					$t->set_block("admin_subject", $admin_subject);
					$t->set_block("admin_message", $admin_message);

					$mail_to = get_setting_value($order_info, "admin_email", $settings["admin_email"]);
					$mail_to = str_replace(";", ",", $mail_to);
					$email_headers = array();
					$email_headers["from"] = get_setting_value($order_info, "admin_mail_from", $settings["admin_email"]);
					$email_headers["cc"] = get_setting_value($order_info, "cc_emails");
					$email_headers["bcc"] = get_setting_value($order_info, "admin_mail_bcc");
					$email_headers["reply_to"] = get_setting_value($order_info, "admin_mail_reply_to");
					$email_headers["return_path"] = get_setting_value($order_info, "admin_mail_return_path");
					$email_headers["mail_type"] = $admin_mail_type;

					if (!$admin_mail_type) {
						$t->set_var("basket", $t->get_var("basket_text"));
					} else {
						$t->set_var("basket", $t->get_var("basket_html"));
					}
					// set download links
					if ($admin_mail_type) {
						$t->set_var("links", $links["html"]);
					} else {
						$t->set_var("links", $links["text"]);
					}
					// set serial numbers
					if ($admin_mail_type) {
						$t->set_var("serials", $order_serials["html"]);
						$t->set_var("serial_numbers", $order_serials["html"]);
					} else {
						$t->set_var("serials", $order_serials["text"]);
						$t->set_var("serial_numbers", $order_serials["text"]);
					}
					// set serial numbers
					if ($admin_mail_type) {
						$t->set_var("vouchers", $order_vouchers["html"]);
						$t->set_var("gift_vouchers", $order_vouchers["html"]);
					} else {
						$t->set_var("vouchers", $order_vouchers["text"]);
						$t->set_var("gift_vouchers", $order_vouchers["text"]);
					}

					$t->parse("admin_subject", false);
					$t->parse("admin_message", false);

					$admin_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("admin_message"));
					va_mail($mail_to, $t->get_var("admin_subject"), $admin_message, $email_headers);
				}

				if ($user_notification)
				{
					$user_subject = get_setting_value($order_info, "user_subject", "");
					$user_subject = get_translation($user_subject);
					$user_message = get_currency_message(get_translation($user_message), $currency);

					$t->set_block("user_subject", $user_subject);
					$t->set_block("user_message", $user_message);

					$email_headers = array();
					$email_headers["from"] = get_setting_value($order_info, "user_mail_from", $settings["admin_email"]);
					$email_headers["cc"] = get_setting_value($order_info, "user_mail_cc");
					$email_headers["bcc"] = get_setting_value($order_info, "user_mail_bcc");
					$email_headers["reply_to"] = get_setting_value($order_info, "user_mail_reply_to");
					$email_headers["return_path"] = get_setting_value($order_info, "user_mail_return_path");
					$email_headers["mail_type"] = $user_mail_type;

					if (!$user_mail_type) {
						$t->set_var("basket", $t->get_var("basket_text"));
					} else {
						$t->set_var("basket", $t->get_var("basket_html"));
					}
					// set download links
					if ($user_mail_type) {
						$t->set_var("links", $links["html"]);
					} else {
						$t->set_var("links", $links["text"]);
					}
					// set serial numbers
					if ($user_mail_type) {
						$t->set_var("serials", $order_serials["html"]);
						$t->set_var("serial_numbers", $order_serials["html"]);
					} else {
						$t->set_var("serials", $order_serials["text"]);
						$t->set_var("serial_numbers", $order_serials["text"]);
					}
					// set serial numbers
					if ($user_mail_type) {
						$t->set_var("vouchers", $order_vouchers["html"]);
						$t->set_var("gift_vouchers", $order_vouchers["html"]);
					} else {
						$t->set_var("vouchers", $order_vouchers["text"]);
						$t->set_var("gift_vouchers", $order_vouchers["text"]);
					}

					$t->parse("user_subject", false);
					$t->parse("user_message", false);

					$user_email = strlen($r->get_value("email")) ? $r->get_value("email") : $r->get_value("delivery_email");
					$user_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("user_message"));
					va_mail($user_email, $t->get_var("user_subject"), $user_message, $email_headers);
				}
				if ($admin_sms)
				{
					$admin_sms_recipient  = get_setting_value($order_info, "admin_sms_recipient", "");
					$admin_sms_originator = get_setting_value($order_info, "admin_sms_originator", "");
					$admin_sms_message    = get_currency_message(get_translation(get_setting_value($order_info, "admin_sms_message", "")), $currency);

					$t->set_block("admin_sms_recipient",  $admin_sms_recipient);
					$t->set_block("admin_sms_originator", $admin_sms_originator);
					$t->set_block("admin_sms_message",    $admin_sms_message);

					$t->set_var("basket", $items_text);
					$t->set_var("items", $items_text);
					// set download links
					$t->set_var("links",    $links["text"]);
					// set serial numbers
					$t->set_var("serials", $order_serials["text"]);
					$t->set_var("serial_numbers", $order_serials["text"]);
					// set serial numbers
					$t->set_var("vouchers", $order_vouchers["text"]);
					$t->set_var("gift_vouchers", $order_vouchers["text"]);

					$t->parse("admin_sms_recipient", false);
					$t->parse("admin_sms_originator", false);
					$t->parse("admin_sms_message", false);

					sms_send($t->get_var("admin_sms_recipient"), $t->get_var("admin_sms_message"), $t->get_var("admin_sms_originator"));
				}

				if ($user_sms)
				{
					$user_sms_recipient  = get_setting_value($order_info, "user_sms_recipient", $r->get_value("cell_phone"));
					$user_sms_originator = get_setting_value($order_info, "user_sms_originator", "");
					$user_sms_message    = get_currency_message(get_translation(get_setting_value($order_info, "user_sms_message", "")), $currency);

					$t->set_block("user_sms_recipient",  $user_sms_recipient);
					$t->set_block("user_sms_originator", $user_sms_originator);
					$t->set_block("user_sms_message",    $user_sms_message);

					$t->set_var("basket", $items_text);
					$t->set_var("items", $items_text);
					// set download links
					$t->set_var("links",    $links["text"]);
					// set serial numbers
					$t->set_var("serials", $order_serials["text"]);
					$t->set_var("serial_numbers", $order_serials["text"]);
					// set serial numbers
					$t->set_var("vouchers", $order_vouchers["text"]);
					$t->set_var("gift_vouchers", $order_vouchers["text"]);

					$t->parse("user_sms_recipient", false);
					$t->parse("user_sms_originator", false);
					$t->parse("user_sms_message", false);

					if (sms_send_allowed($t->get_var("user_sms_recipient"))) {
						sms_send($t->get_var("user_sms_recipient"), $t->get_var("user_sms_message"), $t->get_var("user_sms_originator"));
					}
				}

				// payment system specific notifications
				if (isset($va_cc_tags) && $va_cc_tags && ($admin_payment_notification || $admin_payment_sms)) {
					// set cc payment data
					$t->set_var("cc_number", get_session("session_cc_number"));
					$t->set_var("cc_number_first", get_session("session_cc_number_first"));
					$t->set_var("cc_number_last", get_session("session_cc_number_last"));
					$t->set_var("cc_security_code", get_session("session_cc_code"));
					$cc_type = get_array_value($r->get_value("cc_type"), $credit_cards); 
					$t->set_var("cc_type", $cc_type);
					$cc_start_date = $r->get_value("cc_start_date");
					$cc_expiry_date = $r->get_value("cc_expiry_date");
					if (is_array($cc_start_date)) {
						$cc_start = va_date(array("MM", " / ", "YYYY"), $cc_start_date);
						$t->set_var("cc_start_date", $cc_start);
					}
					if (is_array($cc_expiry_date)) {
						$cc_expiry = va_date(array("MM", " / ", "YYYY"), $cc_expiry_date);
						$t->set_var("cc_expiry_date", $cc_expiry);
					}
					$t->set_var("cc_issue_number", $r->get_value("cc_issue_number"));
				}

				if ($admin_payment_notification)
				{
					$admin_mail_type = get_setting_value($payment_settings, "admin_message_type");
					$admin_subject = get_setting_value($payment_settings, "admin_subject", "");
					$admin_subject = get_translation($admin_subject);
					$admin_message = get_setting_value($payment_settings, "admin_message", "");
					$admin_message = get_currency_message(get_translation($admin_message), $currency);
					// PGP enable
					$admin_notification_pgp = get_setting_value($payment_settings, "admin_notification_pgp",   0);

					$t->set_block("admin_subject", $admin_subject);
					$t->set_block("admin_message", $admin_message);

					if (!$admin_mail_type) {
						$t->set_var("basket", $t->get_var("basket_text"));
					} else {
						$t->set_var("basket", $t->get_var("basket_html"));
					}

					$mail_to = get_setting_value($payment_settings, "admin_email", $settings["admin_email"]);
					$mail_to = str_replace(";", ",", $mail_to);
					$email_headers = array();
					$email_headers["from"] = get_setting_value($payment_settings, "admin_mail_from", $settings["admin_email"]);
					$email_headers["cc"] = get_setting_value($payment_settings, "cc_emails");
					$email_headers["bcc"] = get_setting_value($payment_settings, "admin_mail_bcc");
					$email_headers["reply_to"] = get_setting_value($payment_settings, "admin_mail_reply_to");
					$email_headers["return_path"] = get_setting_value($payment_settings, "admin_mail_return_path");
					$email_headers["mail_type"] = get_setting_value($payment_settings, "admin_message_type");

					$t->parse("admin_subject", false);
					$t->parse("admin_message", false);
					$admin_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("admin_message"));
					
					// PGP encryption			
					if ( $admin_notification_pgp && $admin_message) {	
						include_once ("./includes/pgp_functions.php");
						if (pgp_test()) {
							$tmp_admin_emails = explode(',',$mail_to);
							foreach ($tmp_admin_emails AS $tmp_admin_email) {
								$admin_message = pgp_encrypt($admin_message, $tmp_admin_email);
								if ($admin_message){
									va_mail($tmp_admin_email, $t->get_var("admin_subject"), $admin_message, $email_headers);
								}
							}
						}
					} else {
						va_mail($mail_to, $t->get_var("admin_subject"), $admin_message, $email_headers);		
					}					
				}		 

				if ($admin_payment_sms) 
				{
					$admin_sms_recipient  = get_setting_value($payment_settings, "admin_sms_recipient", "");
					$admin_sms_originator = get_setting_value($payment_settings, "admin_sms_originator", "");
					$admin_sms_message    = get_setting_value($payment_settings, "admin_sms_message", "");

					$t->set_block("admin_sms_recipient",  $admin_sms_recipient);
					$t->set_block("admin_sms_originator", $admin_sms_originator);
					$t->set_block("admin_sms_message",    $admin_sms_message);

					$t->set_var("basket", $items_text);
					$t->set_var("items", $items_text);

					$t->parse("admin_sms_recipient", false);
					$t->parse("admin_sms_originator", false);
					$t->parse("admin_sms_message", false);

					sms_send($t->get_var("admin_sms_recipient"), $t->get_var("admin_sms_message"), $t->get_var("admin_sms_originator"));
				}		 

	
			}

			$vc = md5($order_id . $order_placed_date[3].$order_placed_date[4].$order_placed_date[5]);
			set_session("session_order_id", $order_id);
			set_session("session_vc", $vc);
			set_session("session_payment_id", $payment_id);
			if (!$user_id) { // set session with user info for non-registered users
				$session_order_info = "";
				for ($i = 0; $i < sizeof($parameters); $i++) {
					$session_order_info .= $parameters[$i] . "=" . $r->get_value($parameters[$i]) . "|";
					$session_order_info .= "delivery_" . $parameters[$i] . "=" . $r->get_value("delivery_" . $parameters[$i]) . "|";
				}
				set_session("session_order_info", $session_order_info);
			}

			// try to charge credit card or redirect to payment system 
			$payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
			$post_params = ""; $post_parameters = "";
			get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_params, $pass_data, $variables, "opc");

			// general payment parameters
			$payment_type = get_setting_value($variables, "payment_type", "");
			$payment_type = strtoupper($payment_type); // convert to upper symbols to compare
			$payment_php_lib = get_setting_value($variables, "payment_php_lib", "");
			$payment_url = get_setting_value($variables, "payment_url", "");
			$submit_method = get_setting_value($variables, "submit_method", "");
			$payment_name = get_setting_value($variables, "payment_name", "");
			$user_payment_name = get_setting_value($variables, "user_payment_name", "");

			// advanced payment parameters
			$is_advanced = get_setting_value($variables, "is_advanced", 0);
			$advanced_url = get_setting_value($variables, "advanced_url", "");
			$advanced_php_lib = get_setting_value($variables, "advanced_php_lib", "");
			$failure_action = get_setting_value($variables, "failure_action", "");
			$success_status_id = get_setting_value($variables, "success_status_id", "");
			$pending_status_id = get_setting_value($variables, "pending_status_id", "");
			$failure_status_id = get_setting_value($variables, "failure_status_id", "");

			// check for direct charge method
			if ($is_advanced && strlen($advanced_php_lib))  {
				$payment_params = $payment_parameters;
				$error_message = ""; $pending_message = ""; $transaction_id = "";

				$va_payment = new VA_Payment();
				$va_payment->set_failure_action($failure_action);

				// flag to update order status when using foreign library
				$update_order_status = true; 
				// include payment module only if total order value greater than zero
				if ($variables["order_total"] > 0) {
					// use foreign php library to handle transaction
					$order_step = "opc";
					if (file_exists($advanced_php_lib)) {
						include_once ($advanced_php_lib);
					} else {
						$error_message = "Can't find appropriative php library: " . $advanced_php_lib;
					}
				}

				// convert old advanced data to class variables
				$va_payment->convert_advanced_data($error_message, $pending_message, $transaction_id, $variables, "opc");

				// initialize orders record to update payment data
				$pso = new VA_Record($table_prefix . "orders");
				$pso->add_where("order_id", INTEGER);
				$pso->set_value("order_id", $order_id);
				// field to check if order payment information was submitted and we need forward user to order_final.php page
				$pso->add_textbox("is_confirmed", INTEGER);
				$pso->change_property("is_confirmed", USE_IN_UPDATE, false);
				$pso->add_textbox("error_message", TEXT);
				$pso->add_textbox("pending_message", TEXT);
				$pso->add_textbox("transaction_id", TEXT);
				$pso->change_property("transaction_id", USE_IN_UPDATE, false);
				$pso->add_textbox("authorization_code", TEXT);
				// AVS fields
				$pso->add_textbox("avs_response_code", TEXT);
				$pso->add_textbox("avs_message", TEXT);
				$pso->add_textbox("avs_address_match", TEXT);
				$pso->add_textbox("avs_zip_match", TEXT);
				$pso->add_textbox("cvv2_match", TEXT);
				// 3D fields 
				$pso->add_textbox("secure_3d_check", TEXT);
				$pso->add_textbox("secure_3d_status", TEXT);
				$pso->add_textbox("secure_3d_md", TEXT);
				$pso->add_textbox("secure_3d_xid", TEXT);

				// update order data	
				$pso->set_value("error_message", $error_message);
				$pso->set_value("pending_message", $pending_message);
				if (strlen($transaction_id)) {
					$pso->set_value("transaction_id", $transaction_id);
					$pso->change_property("transaction_id", USE_IN_UPDATE, true);
				}
				if (!strlen($error_message) && !strlen($pending_message)) {
					$pso->set_value("is_confirmed", 1);
					$pso->change_property("is_confirmed", USE_IN_UPDATE, true);
				}
				$pso->set_value("authorization_code", $variables["authorization_code"]);
				// set AVS data
				$pso->set_value("avs_response_code", $variables["avs_response_code"]);
				$pso->set_value("avs_message", $variables["avs_message"]);
				$pso->set_value("avs_address_match", $variables["avs_address_match"]);
				$pso->set_value("avs_zip_match", $variables["avs_zip_match"]);
				$pso->set_value("cvv2_match", $variables["cvv2_match"]);
				// set 3D data
				$pso->set_value("secure_3d_check", $variables["secure_3d_check"]);
				$pso->set_value("secure_3d_status", $variables["secure_3d_status"]);
				$pso->set_value("secure_3d_md", $variables["secure_3d_md"]);
				$pso->set_value("secure_3d_xid", $variables["secure_3d_xid"]);
				$pso->update_record();

				// update order status				
				if ($update_order_status) {
					if (strlen($error_message)) {
						$order_status = $failure_status_id;
					} elseif (strlen($pending_message)) {
						$order_status = $pending_status_id; 
					} else {
						$order_status = $success_status_id; 
					}
					// update order status for payment
					update_order_status($order_id, $order_status, true, "", $status_error);
				}

				if ($ajax) {
					$ajax_data = $va_payment->get_ajax_data();
					echo json_encode($ajax_data);
					exit;
				} else if (strlen($error_message) && $failure_action == 1) {
					$steps["payment"]["errors"] = $error_message;
				} else {
					header("Location: order_final.php");
					exit;
				}

			} else if ($payment_php_lib) {
				// new payment library settings 
				$va_payment = new VA_Payment();
				$va_payment->set_failure_action($failure_action);

				if ($variables["order_total"] > 0) {
					if (file_exists($payment_php_lib)) {
						include_once ($payment_php_lib);
					} else {
						$va_payment->status = "error";
						$va_payment->error_desc = "Can't find appropriative php library: " . $advanced_php_lib;
					}
				} else {
					$va_payment->status = "error";
					$va_payment->action = "redirect";
					$va_payment->error_desc = "Can't find appropriative php library: " . $advanced_php_lib;
					$va_payment->url = "order_final.php";
				}

				if ($ajax) {
					$ajax_data = $va_payment->get_ajax_data();
					echo json_encode($ajax_data);
					exit;
				} else if (strlen($error_message) && $failure_action == 1) {
					$steps["payment"]["errors"] = $error_message;
				} else {
					header("Location: ".$va_payment->url);
					exit;
				}

			} else {
				// redirect user for payment
				if (!$payment_url) { $payment_url = "order_final.php"; }
				if ($left_total == 0 || preg_match("/order_confirmation\.php|order_final\.php|credit_card_info\.php/i", $payment_url)) {
					$payment_url = "order_final.php";
				} else if (strtoupper($submit_method) == "POST") {
					$payment_url = "payment.php";
				}
				if (strtoupper($submit_method) == "GET" && $payment_url != "payment.php" && $payment_url != "order_final.php") {
					$payment_url .= strpos($payment_url,"?") ? "&" : "?";
					$payment_url .= $post_params;
				}
				if ($ajax) {
					$ajax_response = array(
						"order_id" => $order_id,
						"operation" => "redirect",
						"location" => $payment_url,
					);
					echo json_encode($ajax_response);
					exit;
				} else {
					header("Location: " . $payment_url);
					exit;
				}
			}
			// end of payment

		}
	} elseif ($user_id) {
		// set user details from user info
		$user_login = $user_info["login"];
		for ($i = 0; $i < sizeof($parameters); $i++) {
			$r->set_value($parameters[$i], get_setting_value($user_info, $parameters[$i]));
			$r->set_value("delivery_" . $parameters[$i], get_setting_value($user_info, "delivery_".$parameters[$i]));
		}
		$show_email = get_setting_value($order_info, $param_prefix."show_email", 0);
		$show_delivery_email = get_setting_value($order_info, $param_prefix."show_delivery_email", 0);
		if ($show_email && $r->is_empty("email") && preg_match(EMAIL_REGEXP, $user_login)) { 
			$r->set_value("email", $user_login);
		}
		if (!$show_email && $show_delivery_email && $r->is_empty("delivery_email") && preg_match(EMAIL_REGEXP, $user_login)) { 
			$r->set_value("delivery_email", $user_login);
		}
		// check if phone codes available
		phone_code_checks($phone_codes);
		// get states lists
		$states = prepare_states($r);
	} else { // set default values from cookies
		$session_order_info = trim(get_session("session_order_info"));
		if (strlen($session_order_info)) {
			$param_pairs = explode("|", $session_order_info);
			for ($i = 0; $i < sizeof($param_pairs); $i++) {
				$param_line = trim($param_pairs[$i]);
				if (strlen($param_line)) {
					$param_values = explode("=", $param_line, 2);
					if (isset($r->parameters[$param_values[0]])) {
						$r->set_value($param_values[0], $param_values[1]);
					}
				}
			}
		}
		// update peronal and delivery data only if this information weren't set
		if ($r->is_empty("country_id") && $r->is_empty("delivery_country_id") && 
			$r->is_empty("state_id") && $r->is_empty("delivery_state_id") &&
			$r->is_empty("zip") && $r->is_empty("delivery_zip")) {
			// check settings from shipping calculator first
			$shipping_info = get_session("session_shipping_info");
			if (is_array($shipping_info) && sizeof($shipping_info)) {
				$r->set_value("country_id", get_setting_value($shipping_info, "country_id"));
				$r->set_value("delivery_country_id", get_setting_value($shipping_info, "country_id"));
				$r->set_value("state_id", get_setting_value($shipping_info, "state_id"));
				$r->set_value("delivery_state_id", get_setting_value($shipping_info, "state_id"));
				$r->set_value("zip", get_setting_value($shipping_info, "postal_code"));
				$r->set_value("delivery_zip", get_setting_value($shipping_info, "postal_code"));
			} else {
				// get default country from settings
				$r->set_value("country_id", $settings["country_id"]);
				$r->set_value("delivery_country_id", $settings["country_id"]);
			}
		}

		// check if phone codes available
		phone_code_checks($phone_codes);
		// get states lists
		$states = prepare_states($r);
	}

	if (!strlen($operation)) {
		// check subscribe option
		if ($subscribe_block) {
			$subscribe_email = $r->get_value("email");
			if (!$subscribe_email && $r->get_value("delivery_email")) {
				$subscribe_email = $r->get_value("delivery_email");
			}
			if ($subscribe_email) {
				$sql  = " SELECT email_id,site_id FROM " . $table_prefix . "newsletters_users ";
				$sql .= " WHERE email=" . $db->tosql($subscribe_email, TEXT);
				$sql .= " AND (site_id=0 OR site_id=" . $db->tosql($site_id, INTEGER).") ";
				$db->query($sql);
				if ($db->next_record()) {
					$email_id = $db->f("email_id");
					$email_site_id = $db->f("site_id");
					if (!$email_site_id) {
						$sql  = " UPDATE " . $table_prefix . "newsletters_users ";
						$sql .= " SET site_id=" . $db->tosql($site_id, INTEGER);
						$sql .= " WHERE email_id=" . $db->tosql($email_id, INTEGER);
						$db->query($sql);
					}
					$r->set_value("subscribe", 1);
				}
			}
		}
	}

	// set payment fields list values
	$current_date = va_time();
	$cc_start_years = get_db_values("SELECT start_year AS year_value, start_year AS year_description FROM " . $table_prefix . "cc_start_years", array(array("", YEAR_MSG)));
	if (sizeof($cc_start_years) < 2) {
		$cc_start_years = array(array("", YEAR_MSG));
		for($y = 7; $y >= 0; $y--) {
			$cc_start_years[] = array($current_date[YEAR] - $y, $current_date[YEAR] - $y);
		}
	}
	$cc_expiry_years = get_db_values("SELECT expiry_year AS year_value, expiry_year AS year_description FROM " . $table_prefix . "cc_expiry_years", array(array("", YEAR_MSG)));
	if (sizeof($cc_expiry_years) < 2) {
		$cc_expiry_years = array(array("", YEAR_MSG));
		for($y = 0; $y <= 7; $y++) {
			$cc_expiry_years[] = array($current_date[YEAR] + $y, $current_date[YEAR] + $y);
		}
	}
	set_options($cc_start_years, $cc_start_year, "cc_start_year");
	set_options($cc_expiry_years, $cc_expiry_year, "cc_expiry_year");

	$cc_months = array_merge (array(array("", MONTH_MSG)), $months);
	set_options($cc_months, $cc_start_month, "cc_start_month");
	set_options($cc_months, $cc_expiry_month, "cc_expiry_month");

	// check profile errors only for new orders
	$steps["user"]["errors"] = $profile_errors.$r->errors; // save record errors for profile step
	$r->errors = ""; // clear errors for record class
	$r->set_parameters();

	/*
	if ($sc_errors) {
		$t->set_var("errors_list", $sc_errors);
		$t->parse("sc_errors", false);
	}//*/

	$steps["cart"]["errors"] = $sc_errors;
	if ($shipping_errors) {
		$steps["shipping"]["errors"] = $shipping_errors;
	}
	if ($payment_errors) {
		$steps["payment"]["errors"] = $payment_errors;
	}

	// check if user can use address book
	$user_addresses = get_setting_value($user_settings, "user_addresses", 0);

	if ($call_center && !$session_user_id) {
		if ($cc_user_id) {
			$t->set_var("cc_user_id", htmlspecialchars($cc_user_id));	
			$t->set_var("cc_user_login", htmlspecialchars($cc_user_login));	
			$t->set_var("cc_remove_user_style", "display: inline;");	
		} else {
			$t->set_var("cc_remove_user_style", "display: none;");	
		}
		$t->parse("call_center_user_block", false);
	}

	$t->set_var("personal_number", $personal_number);
	$t->set_var("delivery_number", $delivery_number);
	if ($personal_number > 0) {
		if ($user_addresses && !$user_order_id) {
			$t->sparse("personal_select_address", false);
		}
		$t->parse("personal", false);
	}

	if ($delivery_number > 0) {
		if ($user_addresses && !$user_order_id) {
			$t->sparse("delivery_select_address", false);
		}
		$t->parse("delivery", false);
	}


	// save names for next active step
	$first_step = ""; $last_step = ""; $step_number = 0;
	foreach($steps as $step_name => $step_info) {
		$step_show = $step_info["show"];
		if ($step_show) {
			$step_number++;
			$steps[$step_name]["number"] = $step_number;
			if (!$first_step) {
				$first_step = $step_name;
			}
			if ($last_step) {
				$steps[$last_step]["next"] = $step_name;
			}
			// save name of last step
			$last_step = $step_name;
		}
	}
	if ($last_step) {
		$steps[$last_step]["next"] = "final";
	}

	if ($active_step == "final") {
		$active_step = $last_step;
	} else if (!isset($steps[$active_step])) {
		$active_step = $first_step;
	}
	// check what blocks we will need to parse
	$t->set_var("active_step", htmlspecialchars($active_step));

	if ($ajax && ($operation == "save" || $operation == "next")) {	
		$ajax_response = array();
		// always update processing fees information
		$ajax_response["processing_fees"] = $processing_fees;
		$ajax_response["payment_systems"] = $payment_systems;

		// check errors for all previous steps
		$active_order = $steps[$active_step]["order"];

		// check all steps before and including active step for errors
		$errors = "";
		foreach($steps as $step_name => $step_info) {
			$step_order = $step_info["order"];
			if ($active_order >= $step_order) {
				$errors = $step_info["errors"];
				if ($errors) {
					$active_step = $step_name;
					break;
				}
			}
		}

		if ($errors) {
			$ajax_response["errors"] = $errors;
			$ajax_response["step"] = $active_step;

			$next_step_name = $steps[$active_step]["next"];
			$t->set_var("errors_class", "errors");
			$t->set_var("errors_list", $errors);
			$t->set_var("step_number", $steps[$active_step]["number"]);
			$t->set_var("step_name", $active_step);
			$t->set_var("next_step", $next_step_name);
			if ($next_step_name == "final") {
				if ($checkout_flow == "quote" || $checkout_flow == "quote_request") {
					$t->set_var("button_name", REQUEST_QUOTE_BUTTON);
				} else {
					$t->set_var("button_name", PLACE_ORDER_BUTTON);
				}
			} else {
				$t->set_var("button_name", CONTINUE_BUTTON);
			}
			$t->parse($active_step."_block", false);
			$ajax_response["block"] = $t->get_var($active_step."_block");

			echo json_encode($ajax_response);
			exit;
		} else {
			if (!$next_step) {
				$next_step = $steps[$active_step]["next"];
			}
			$next_step_name = $steps[$next_step]["next"];
			$t->set_var("errors_class", "hidden");
			$t->set_var("errors_list", "");
			$t->set_var("step_number", $steps[$next_step]["number"]);
			$t->set_var("step_name", $next_step);
			$t->set_var("next_step", $next_step_name);
			if ($next_step_name == "final") {
				if ($checkout_flow == "quote" || $checkout_flow == "quote_request") {
					$t->set_var("button_name", REQUEST_QUOTE_BUTTON);
				} else {
					$t->set_var("button_name", PLACE_ORDER_BUTTON);
				}
			} else {
				$t->set_var("button_name", CONTINUE_BUTTON);
			}

			$t->parse($next_step."_block", false);
			$ajax_response["block"] = $t->get_var($next_step."_block");
			echo json_encode($ajax_response);
			return;

			$block_parsed = false;
		}
	} else {
		// check active step order 
		$active_order = $steps[$active_step]["order"];

		// check all steps before and including active step for errors
		$is_errors = false;
		foreach($steps as $step_name => $step_info) {
			$errors = $step_info["errors"];
			$step_order = $step_info["order"];
			if ($is_errors || $step_order > $active_order) {
				// show only errors for checkout step with highest order
				$steps[$step_name]["errors"] = "";
			} else if ($errors) {
				$is_errors = true;
				$active_step = $step_name;
			}
		}
		if (!$is_errors && $next_step) {
			$active_step = $next_step;
		}

		// check new active step order or use last step order if step like 'final' wasn't found
		$active_order = isset($steps[$active_step]["order"]) ? $steps[$active_step]["order"] : $steps[$last_step]["order"];

		// parse all available checkout blocks
		$step_number = 0;
		foreach($steps as $step_name => $step_info) {
			$errors = $step_info["errors"];
			$step_show = $step_info["show"];
			$step_order = $step_info["order"];
			$next_step_name = $step_info["next"];

			if ($step_show) {
				$step_number++;
				$t->set_var("step_link_onclick", "");
				if ($opc_type == "single") {
					// for sigle form all steps active
					$t->set_var("step_class", "active");
				} else if ($active_order > $step_order) {	
					$t->set_var("step_class", "closed");
					$t->set_var("step_link_onclick", "reopenStep('".$step_name."')");
				} else if ($active_order == $step_order) {	
					$t->set_var("step_class", "active");
				} else {
					$t->set_var("step_class", "inactive");
				}
				if ($errors) {
					$t->set_var("errors_class", "errors");
					$t->set_var("errors_list", $errors);
				} else {
					$t->set_var("errors_class", "hidden");
					$t->set_var("errors_list", "");
				}
				$t->set_var("step_number", $step_number);
				$t->set_var("step_name", $step_name);
				$t->set_var("next_step", $next_step_name);
				if ($next_step_name == "final") {
					if ($checkout_flow == "quote" || $checkout_flow == "quote_request") {
						$t->set_var("button_name", REQUEST_QUOTE_BUTTON);
					} else {
						$t->set_var("button_name", PLACE_ORDER_BUTTON);
					}
				} else {
					$t->set_var("button_name", CONTINUE_BUTTON);
				}

				$t->parse($step_name."_block", false);
			}
		}

		$block_parsed = true;
	}

function disabled_values_function($params) {
	global $t;
	$disabled = "";		
	$current_value = $params["current_value"];
	$disabled_array = $params["disabled_array"];
	if (in_array($current_value, $disabled_array)) {
		$disabled = "disabled";
	}
	$t->set_var("payment_select_id" . "_disabled", $disabled);
}

function format_zip()
{
	global $r;
	// check zip
	$country_code = strtoupper($r->get_value("country_code"));
	$zip = trim($r->get_value("zip"));
	$is_valid = $r->get_property_value("zip", IS_VALID);
	if ($country_code == "GB" && $zip && preg_match(UK_POSTCODE_REGEXP, $zip)) {
		$zip = preg_replace("/\s{2,}/", " ", $zip);
		if (!preg_match("/\s\d[a-z]{2}$/i", $zip)) {
			$zip = substr($zip, 0, strlen($zip) - 3)." ".substr($zip,-3);
		}
		$r->set_value("zip", $zip);
	}
}
function format_delivery_zip()
{
	global $r;
	// check delivery zip
	$delivery_country_code = strtoupper($r->get_value("delivery_country_code"));
	$delivery_zip = trim($r->get_value("delivery_zip"));
	if ($delivery_country_code == "GB" && $delivery_zip && preg_match(UK_POSTCODE_REGEXP, $delivery_zip)) {
		$delivery_zip = preg_replace("/\s{2,}/", " ", $delivery_zip);
		if (!preg_match("/\s\d[a-z]{2}$/i", $delivery_zip)) {
			$delivery_zip = substr($delivery_zip, 0, strlen($delivery_zip) - 3)." ".substr($delivery_zip,-3);
		}
		$r->set_value("delivery_zip", $delivery_zip);
	}
}

