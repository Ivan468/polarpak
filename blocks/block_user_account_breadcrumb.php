<?php                           

	$default_title = "";

	$user_id = get_session("session_user_id");		

	if(!$user_id) {
		return;
	}

	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", "");

	$html_template = get_setting_value($block, "html_template", "block_user_account_breadcrumb.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("user_home_href", $site_url . get_custom_friendly_url("user_home.php"));

	$links = array();
	$links[] = array($site_url . get_custom_friendly_url("user_home.php"), va_constant("MY_ACCOUNT_MSG"));
	if ($current_page == "user_profile.php") {
		$links[] = array("user_profile.php", va_constant("EDIT_PROFILE_MSG"), "");
	} else if ($current_page == "support.php") {
		$links[] = array("user_support.php", va_constant("MY_SUPPORT_ISSUES_MSG"), "");
		$links[] = array("support.php", va_constant("NEW_SUPPORT_REQUEST_MSG"), "");
	} else if ($current_page == "support_messages.php") {
		$links[] = array("user_support.php", va_constant("MY_SUPPORT_ISSUES_MSG"), "");
		$links[] = array("support_messages.php", va_constant("VIEW_MSG"), array("support_id", "vc"));
	} else if ($current_page == "user_ads.php") {
		$links[] = array("user_ads.php", va_constant("MY_ADS_MSG"), "");
	} else if ($current_page == "user_ad.php") {
		$links[] = array("user_ads.php", va_constant("MY_ADS_MSG"), "");
		$link_url = "user_ad.php";
		$item_id = get_param("item_id");
		if ($item_id) {
			$params = array("item_id");
		} else {
			$params = array("type_id");
		}
		$links[] = array($link_url, va_constant("EDIT_MSG"), $params);
	} else if ($current_page == "user_affiliate_sales.php") {
		$links[] = array("user_affiliate_sales.php", va_constant("AFFILIATE_SALES_MSG"), array("s_tp", "s_sd", "s_ed", "s_os"));
	} else if ($current_page == "user_affiliate_items.php") {
		$links[] = array("user_affiliate_sales.php", va_constant("AFFILIATE_SALES_MSG"), array("s_tp", "s_sd", "s_ed", "s_os"));
		$links[] = array("user_affiliate_items.php", va_constant("PRODUCTS_REPORT_MSG"), array("s_tp", "s_sd", "s_ed", "s_os"));
	} else if ($current_page == "user_change_password.php") {
		$links[] = array("user_change_password.php", va_constant("CHANGE_PASSWORD_MSG"), "");
	} else if ($current_page == "user_change_type.php") {
		$links[] = array("user_change_type.php", va_constant("UPGRADE_DOWNGRADE_MSG"), "");
	} else if ($current_page == "user_carts.php") {
		$links[] = array("user_carts.php", va_constant("MY_SAVED_CARTS_MSG"), "");
	} else if ($current_page == "user_merchant_sales.php") {
		$links[] = array("user_merchant_sales.php", va_constant("MERCHANT_SALES_MSG"), array("s_tp", "s_sd", "s_ed", "s_os"));
	} else if ($current_page == "user_merchant_items.php") {
		$links[] = array("user_merchant_sales.php", va_constant("MERCHANT_SALES_MSG"), array("s_tp", "s_sd", "s_ed", "s_os"));
		$links[] = array("user_merchant_items.php", va_constant("PRODUCTS_REPORT_MSG"), array("s_tp", "s_sd", "s_ed", "s_os"));
	} else if ($current_page == "user_merchant_orders.php") {
		$links[] = array("user_merchant_orders.php", va_constant("MY_SALES_ORDERS_MSG"), "");
	} else if ($current_page == "user_merchant_order.php") {
		$links[] = array("user_merchant_orders.php", va_constant("MY_SALES_ORDERS_MSG"), "");
		$links[] = array("user_merchant_order.php", va_constant("ORDER_DETAILS_MSG"), array("order_id"));
	} else if ($current_page == "user_reminders.php") {
		$links[] = array("user_reminders.php", va_constant("MY_REMINDERS_MSG"), "");
	} else if ($current_page == "user_reminder.php") {
		$links[] = array("user_reminders.php", va_constant("MY_REMINDERS_MSG"), "");
		$links[] = array("user_reminder.php", va_constant("EDIT_REMINDER_MSG"), array("reminder_id"));
	} else if ($current_page == "user_orders.php") {
		$links[] = array("user_orders.php", va_constant("MY_ORDERS_MSG"), "");
	} else if ($current_page == "user_order.php") {
		$links[] = array("user_orders.php", va_constant("MY_ORDERS_MSG"), "");
		$links[] = array("user_order.php", va_constant("ORDER_DETAILS_MSG"), array("order_id"));
	} else if ($current_page == "user_payments.php") {
		$links[] = array("user_payments.php", va_constant("COMMISSION_PAYMENTS_MSG"), "");
	} else if ($current_page == "user_payment.php") {
		$links[] = array("user_payments.php", va_constant("COMMISSION_PAYMENTS_MSG"), "");
		$links[] = array("user_payment.php", va_constant("COMMISSIONS_MSG"), array("payment_id"));
	} else if ($current_page == "user_wishlist.php") {
		$links[] = array("user_wishlist.php", va_constant("MY_WISHLIST_MSG"), "");
	} else if ($current_page == "user_support.php") {
		$links[] = array("user_support.php", va_constant("MY_SUPPORT_ISSUES_MSG"), "");
	} else if ($current_page == "user_products.php") {
		$links[] = array("user_products.php", va_constant("MY_PRODUCTS_MSG"), "");
	} else if ($current_page == "user_product.php") {
		$links[] = array("user_products.php", va_constant("MY_PRODUCTS_MSG"), "");
		$links[] = array("user_product.php", va_constant("EDIT_PRODUCT_MSG"), array("item_id"));
	} else if ($current_page == "user_product_options.php") {
		$item_id = get_param("item_id");
		$item_name = get_db_value("SELECT item_name FROM " . $table_prefix . "items WHERE item_id=" . $db->tosql($item_id, INTEGER));
		$links[] = array("user_products.php", va_constant("MY_PRODUCTS_MSG"), "");
		$links[] = array("user_product.php", $item_name, array("item_id"));
		$links[] = array("user_product_options.php", va_constant("OPTIONS_AND_COMPONENTS_MSG"), array("item_id"));
	} else if ($current_page == "user_product_option.php") {
		$item_id = get_param("item_id");
		$item_name = get_db_value("SELECT item_name FROM " . $table_prefix . "items WHERE item_id=" . $db->tosql($item_id, INTEGER));
		$links[] = array("user_products.php", va_constant("MY_PRODUCTS_MSG"), "");
		$links[] = array("user_product.php", $item_name, array("item_id"));
		$links[] = array("user_product_options.php", va_constant("OPTIONS_AND_COMPONENTS_MSG"), array("item_id"));
		$links[] = array("user_product_option.php", va_constant("EDIT_OPTION_MSG"), array("item_id", "property_id"));
	} else if ($current_page == "user_product_subcomponent.php") {
		$item_id = get_param("item_id");
		$item_name = get_db_value("SELECT item_name FROM " . $table_prefix . "items WHERE item_id=" . $db->tosql($item_id, INTEGER));
		$links[] = array("user_products.php", va_constant("MY_PRODUCTS_MSG"), "");
		$links[] = array("user_product.php", $item_name, array("item_id"));
		$links[] = array("user_product_options.php", va_constant("OPTIONS_AND_COMPONENTS_MSG"), array("item_id"));
		$links[] = array("user_product_option.php", va_constant("EDIT_SUBCOMP_MSG"), array("item_id", "property_id"));
	} else if ($current_page == "user_product_subcomponents.php") {
		$item_id = get_param("item_id");
		$item_name = get_db_value("SELECT item_name FROM " . $table_prefix . "items WHERE item_id=" . $db->tosql($item_id, INTEGER));
		$links[] = array("user_products.php", va_constant("MY_PRODUCTS_MSG"), "");
		$links[] = array("user_product.php", $item_name, array("item_id"));
		$links[] = array("user_product_options.php", va_constant("OPTIONS_AND_COMPONENTS_MSG"), array("item_id"));
		$links[] = array("user_product_option.php", va_constant("EDIT_SUBCOMP_SELECTION_MSG"), array("item_id", "property_id"));
	} else if ($current_page == "user_product_registrations.php") {
		$links[] = array("user_product_registrations.php", va_constant("MY_PRODUCT_REGISTRATIONS_MSG"), "");
	} else if ($current_page == "user_product_registration.php") {
		$links[] = array("user_product_registrations.php", va_constant("MY_PRODUCT_REGISTRATIONS_MSG"), "");
		$links[] = array("user_product_registration.php", va_constant("REGISTER_PRODUCT_MSG"), array("registration_id"));
	} else if ($current_page == "user_addresses.php") {
		$links[] = array("user_addresses.php", va_constant("MY_ADDRESSES_MSG"), "");
	} else if ($current_page == "user_address.php") {
		$links[] = array("user_addresses.php", va_constant("MY_ADDRESSES_MSG"), "");
		$links[] = array("user_address.php", va_constant("EDIT_MSG"), array("address_id"));
	} else if ($current_page == "user_psd_list.php") {
		$links[] = array("user_psd_list.php", va_constant("PAYMENT_DETAILS_MSG"), "");
	} else if ($current_page == "user_psd_update.php") {
		$links[] = array("user_psd_list.php", va_constant("PAYMENT_DETAILS_MSG"), "");
		$links[] = array("user_psd_update.php", va_constant("EDIT_MSG"), array("psd_id"));
	} else if ($current_page == "user_datings.php") {
		$links[] = array("user_datings.php", va_constant("MY_DATING_MSG"), "");
	} else if ($current_page == "user_playlists.php") {
		$links[] = array("user_playlists.php", va_constant("MY_PLAYLISTS_MSG"), "");
	} else if ($current_page == "user_playlist.php") {
		$list_id = get_param("list_id");
		$links[] = array("user_playlists.php", va_constant("MY_PLAYLISTS_MSG"), "");
		if ($list_id) {
			$list_name = get_db_value("SELECT list_name FROM " . $table_prefix . "favorite_lists WHERE list_id=" . $db->tosql($list_id, INTEGER));
			$links[] = array("user_playlist_songs.php", $list_name, array("list_id"));
		}
		$links[] = array("user_playlist.php", va_constant("EDIT_MSG"), array("list_id"));
	} else if ($current_page == "user_playlist_songs.php") {
		$list_id = get_param("list_id");
		$list_name = get_db_value("SELECT list_name FROM " . $table_prefix . "favorite_lists WHERE list_id=" . $db->tosql($list_id, INTEGER));
		$links[] = array("user_playlists.php", va_constant("MY_PLAYLISTS_MSG"), "");
		$links[] = array("user_playlist_songs.php", $list_name, array("list_id"));
	} else if ($current_page == "user_dating.php") {
		$links[] = array("user_datings.php", va_constant("MY_DATING_MSG"), "");
		$link_url = "user_dating.php";
		$dating_id = get_param("dating_id");
		$params = array();
		if ($dating_id) { $params = array("dating_id"); }
		$links[] = array($link_url, va_constant("PROFILE_TITLE"), $params);
	}

	$lc = count($links);
	for ($l = 0; $l < $lc; $l++) {
		$link = $links[$l];
		$url = new VA_URL(get_custom_friendly_url($link[0]));
		$params = isset($link[2]) ? $link[2] : "";
		if (is_array($params)) {
			for ($p = 0; $p < sizeof($params); $p++) {
				$url->add_parameter($params[$p], REQUEST, $params[$p]);
			}
		}
		$t->set_var("tree_url", $url->get_url());
		$t->set_var("tree_title", $link[1]);
		$t->parse("tree", true);
	}


	if(!$layout_type) { $layout_type = "bb"; }
	$block_parsed = true;

?>