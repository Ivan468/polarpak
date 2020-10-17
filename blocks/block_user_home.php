<?php
	
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/support_messages.php");
	include_once("./messages/" . $language_code . "/forum_messages.php");
	include_once("./messages/" . $language_code . "/profiles_messages.php");

	$default_title = "{USER_HOME_TITLE}";

	$va_version_code = va_version_code();

	check_user_session();
	
	$operation = get_param("operation");

	if ($operation == "logout") {
		user_logout();

		header("Location: " . get_custom_friendly_url("index.php"));
		exit;
	}

	$user_id = get_session("session_user_id");
	$user_info = get_session("session_user_info");
	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", "");
	$secure_user_profile = get_setting_value($settings, "secure_user_profile", 0);
	$secure_payments = get_setting_value($settings, "secure_payments", 0);
	$user_messages_url = get_setting_value($settings, "secure_payments", 0);
	if ($secure_user_profile) {
		$user_profile_url = $secure_url . get_custom_friendly_url("user_profile.php");
		$user_addresses_url = $secure_url . get_custom_friendly_url("user_addresses.php");
		$user_change_password_url = $secure_url . get_custom_friendly_url("user_change_password.php");
	} else {
		$user_profile_url = $site_url . get_custom_friendly_url("user_profile.php");
		$user_addresses_url = $site_url . get_custom_friendly_url("user_addresses.php");
		$user_change_password_url = $site_url . get_custom_friendly_url("user_change_password.php");
	}
	$user_messages_url = $site_url . get_custom_friendly_url("user_messages.php");
	$secure_user_tickets = get_setting_value($settings, "secure_user_tickets", 0);
	if ($secure_user_tickets) {
		$user_support_url = $secure_url . get_custom_friendly_url("user_support.php");
	} else {
		$user_support_url = $site_url . get_custom_friendly_url("user_support.php");
	}
	if ($secure_payments) {
		$user_psd_list_url = $secure_url . get_custom_friendly_url("user_psd_list.php");
	} else {
		$user_psd_list_url = $site_url . get_custom_friendly_url("user_psd_list.php");
	}

	// points settings
	$points_balance = get_setting_value($user_info, "total_points", 0);
	$points_system = get_setting_value($settings, "points_system", 0);
	$points_decimals = get_setting_value($settings, "points_decimals", 0);
	// credit system settings
	$credit_system = get_setting_value($settings, "credit_system", 0);
	$credits_balance_user_home = get_setting_value($settings, "credits_balance_user_home", 0);
	$credit_balance = get_setting_value($user_info, "credit_balance", 0);

	$cols = get_setting_value($vars, "cols", 2);

	$user_settings = array();
	$sql = "SELECT setting_name,setting_value FROM " . $table_prefix . "user_types_settings WHERE type_id=" . $db->tosql(get_session("session_user_type_id"), INTEGER);
	$db->query($sql);
	while ($db->next_record()) {
		$user_settings[$db->f("setting_name")] = $db->f("setting_value");
	}

	$html_template = get_setting_value($block, "html_template", "block_user_home.html"); 
  $t->set_file("block_body", $html_template);

	$t->set_var("user_profile_href", get_custom_friendly_url("user_profile.php"));
	$t->set_var("user_profile_url",  $user_profile_url);
	$t->set_var("user_addresses_url",  $user_addresses_url);
	$t->set_var("user_messages_url",  $user_messages_url);
	$t->set_var("user_orders_href", get_custom_friendly_url("user_orders.php"));
	$t->set_var("user_change_password_href", get_custom_friendly_url("user_change_password.php"));
	$t->set_var("user_change_password_url",  $user_change_password_url);
	$t->set_var("user_psd_list_url",  $user_psd_list_url);
	$t->set_var("user_support_href", $user_support_url);
	$t->set_var("forum_href", get_custom_friendly_url("forum.php"));
	$t->set_var("user_products_href", get_custom_friendly_url("user_products.php"));
	$t->set_var("user_product_registrations_href", get_custom_friendly_url("user_product_registrations.php"));
	$t->set_var("user_ads_href", get_custom_friendly_url("user_ads.php"));
	$t->set_var("profiles_user_list_href", get_custom_friendly_url("profiles_user_list.php"));
	$t->set_var("user_merchant_orders_href", get_custom_friendly_url("user_merchant_orders.php"));
	$t->set_var("user_merchant_sales_href", get_custom_friendly_url("user_merchant_sales.php"));
	$t->set_var("user_affiliate_sales_href", get_custom_friendly_url("user_affiliate_sales.php"));
	$t->set_var("user_payments_href", get_custom_friendly_url("user_payments.php"));
	$t->set_var("user_carts_href", get_custom_friendly_url("user_carts.php"));
	$t->set_var("user_wishlist_href", get_custom_friendly_url("user_wishlist.php"));
	$t->set_var("user_reminders_href", "user_reminders.php");
	$t->set_var("user_vouchers_href", "user_vouchers.php");
	$t->set_var("user_playlists_href", "user_playlists.php");
	$t->set_var("user_change_type_url", get_custom_friendly_url("user_change_type.php"));

	$upgrade_downgrade = get_setting_value($user_settings, "upgrade_downgrade", 0);
	$sql = "SELECT is_subscription FROM " . $table_prefix . "user_types WHERE type_id=" . $db->tosql(get_session("session_user_type_id"), INTEGER);
	$is_subscription = get_db_value($sql);
	if ($upgrade_downgrade || $is_subscription) {
		$t->sparse("upgrade_downgrade_block", false);
	}
	$edit_pd = get_setting_value($user_settings, "edit_pd", 0);
	if ($edit_pd) {
		$t->sparse("edit_pd_block", false);
	}

	$subscription_periods = 
		array( 
			array("", ""), array(1, DAY_MSG), array(2, WEEK_MSG), array(3, MONTH_MSG), array(4, YEAR_MSG)
		);

	$sql  = " SELECT s.subscription_name, s.subscription_fee,s.subscription_interval,s.subscription_period "; 
	$sql .= " FROM " . $table_prefix . "subscriptions s, " . $table_prefix . "users u ";
	$sql .= " WHERE s.subscription_id=u.subscription_id ";
	$sql .= " AND u.user_id=" . $db->tosql($user_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$t->set_var("subscription_name", get_translation($db->f("subscription_name")));
		$t->set_var("subscription_fee", currency_format($db->f("subscription_fee")));
		$t->set_var("subscription_interval", $db->f("subscription_interval"));
		$subscription_period = "";
		foreach ($subscription_periods as $key => $sub_array) {
			if ($sub_array[0] == $db->f("subscription_period")) {
				$subscription_period = $sub_array[1];
			}
		}
		$t->set_var("subscription_period", $subscription_period);
		$t->parse("current_subscription", false);						
	} else {
		$t->set_var("current_subscription", "");
	}

	if ($points_system) {
		$t->set_var("points_balance", number_format($points_balance, $points_decimals));
		$t->sparse("points_balance_block", false);
	}
	if ($credit_system && $credits_balance_user_home) {
		if ($credit_balance >= 0) {
			$t->set_var("credit_balance", currency_format($credit_balance));
		} else {
			$t->set_var("credit_balance", "-".currency_format(abs($credit_balance)));
		}
		$t->sparse("credit_balance_block", false);
	}

	$user_login = get_setting_value($user_info, "nickname", "");
	if (!$user_login) { 
		$user_login = get_setting_value($user_info, "login", "");
	}
	$t->set_var("user_login", $user_login);
	$t->set_var("user_name", get_session("session_user_name"));

	$blocks = array(
		"orders_block"          => "my_orders",
		"details_block"         => "my_details",
		"user_messages_block"   => "user_messages",
		"user_addresses_block"  => "user_addresses",
		"support_block"         => "my_support",
		"forum_block"           => "my_forum",
		"products_block"        => "access_products",
		"product_registrations_block" => "my_product_registrations",
		"ad_block"              => "add_ad",
		"profiles_block"        => "profiles",
		"merchant_orders_block" => "merchant_orders",
		"merchant_sales_block"  => "merchant_sales",
		"affiliate_sales_block" => "affiliate_sales",
		"payments_block"        => "my_payments",
		"carts_block"           => "my_carts",
		"wishlist_block"        => "my_wishlist",
		"reminders_block"		    => "reminder_service",
		"vouchers_block"        => "my_vouchers",
		"playlists_block"		    => "my_playlists",
	);

	// shop - 1, cms - 2, helpdesk - 4, forum - 8, ads - 16
	if (!($va_version_code & 1)) {
		unset($blocks["orders_block"]);
		unset($blocks["products_block"]);
		unset($blocks["merchant_sales_block"]);
		unset($blocks["merchant_orders_block"]);
		unset($blocks["affiliate_sales_block"]);
		unset($blocks["payments_block"]);
		unset($blocks["carts_block"]);
		unset($blocks["wishlist_block"]);
		unset($blocks["reminders_block"]);
		unset($blocks["vouchers_block"]);
	}
	if (!($va_version_code & 2)) {
		unset($blocks["playlists"]);
	}
	if (!($va_version_code & 4)) {
		unset($blocks["support_block"]);
	}
	if (!($va_version_code & 8)) {
		unset($blocks["forum_block"]);
	}
	if (!($va_version_code & 16)) {
		unset($blocks["ad_block"]);
	}
	$t->set_var("columns_class", "cols-".$cols);
	$block_number = 0;
	foreach ($blocks as $template_block => $permission_name) {
		$permission = get_setting_value($user_settings, $permission_name, 0);
		if ($permission) {
			$block_number++;
			$column_index = ($block_number % $cols) ? ($block_number % $cols) : $cols;
			$t->sparse($template_block, false);
			$t->set_var("column_class", "col-".$column_index);
			$t->parse("cols", true);
			$t->set_var($template_block, "");
			if ($block_number % $cols == 0) {
				$t->parse("rows", true);
				$t->set_var("cols", "");
			}
		}
	}
	if ($block_number > 0 && $block_number % $cols != 0) {
		$t->parse("rows", true);
	}

	$block_parsed = true;

?>