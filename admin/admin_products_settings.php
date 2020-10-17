<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_products_settings.php                              ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/editgrid.php");
	include_once($root_folder_path . "includes/tabs_functions.php");
	include_once($root_folder_path . "includes/shopping_functions.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once($root_folder_path . "messages/" . $language_code . "/download_messages.php");
	include_once($root_folder_path . "messages/" . $language_code . "/reviews_messages.php");
	include_once("./admin_common.php");

	check_admin_security("products_settings");

	$va_trail = array(
		"admin_menu.php?code=settings" => va_message("SETTINGS_MSG"),
		"admin_menu.php?code=products-settings" => va_message("PRODUCTS_MSG"),
		"admin_products_settings.php" => va_message("PRODUCTS_SETTINGS_MSG"),
	);

	$tax_rates = get_tax_rates(true); 
	$tax_available = false; $tax_names = ""; $tax_column_names = "";
	if (sizeof($tax_rates) > 0) {
		$tax_available = true;
		foreach ($tax_rates as $tax_id => $tax_info) {
			$show_type = $tax_info["show_type"];
			$tax_type = $tax_info["tax_type"];
			if ($show_type&1) {
				if ($tax_column_names) { $tax_column_names .= " & "; }
				$tax_column_names .= get_translation($tax_info["tax_name"]);
			}
			if ($tax_names) { $tax_names .= " & "; }
			$tax_names .= get_translation($tax_info["tax_name"]);
		}
	}

	// additional connection
	$dbs = new VA_SQL();
	$dbs->DBType      = $db_type;
	$dbs->DBDatabase  = $db_name;
	$dbs->DBUser      = $db_user;
	$dbs->DBPassword  = $db_password;
	$dbs->DBHost      = $db_host;
	$dbs->DBPort      = $db_port;
	$dbs->DBPersistent= $db_persistent;

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_products_settings.html");

	include_once("./admin_header.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_items_list_href", "admin_items_list.php");
	$t->set_var("admin_products_settings_href", "admin_products_settings.php");
	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");
	$t->set_var("admin_tax_rates_href", "admin_tax_rates.php");
	$t->set_var("admin_column_code_href", "admin_column_code.php");

	$t->set_var("hide_add_message", str_replace("{button_name}", va_message("ADD_TO_CART_MSG"), va_message("HIDE_BUTTON_MSG")));
	$t->set_var("hide_view_message", str_replace("{button_name}", va_message("VIEW_CART_MSG"), va_message("HIDE_BUTTON_MSG")));
	$t->set_var("hide_goto_message", str_replace("{button_name}", va_message("GOTO_CHECKOUT_MSG"), va_message("HIDE_BUTTON_MSG")));
	$t->set_var("hide_wish_message", str_replace("{button_name}", va_message("ADD_TO_WISHLIST_MSG"), va_message("HIDE_BUTTON_MSG")));
	$t->set_var("hide_more_message", str_replace("{button_name}", va_message("READ_MORE_MSG"), va_message("HIDE_BUTTON_MSG")));
	$t->set_var("hide_shipping_message", str_replace("{button_name}", va_message("SHIPPING_CALCULATOR_MSG"), va_message("HIDE_BUTTON_MSG")));
	$t->set_var("hide_free_shipping_message", str_replace("{button_name}", va_message("FREE_SHIPPING_MSG"), va_message("HIDE_BUTTON_MSG")));

	$t->set_var("date_edit_format", join("", $date_edit_format));

	// set default tax column names
	$t->set_var("tax_percent_default_name", get_translation($tax_column_names)." (%)");
	$t->set_var("tax_default_name", get_translation($tax_column_names));
	$t->set_var("tax_total_default_name", va_constant("PROD_TAX_TOTAL_COLUMN")." ".get_translation($tax_column_names));

	$full_image_url = get_setting_value($settings, "full_image_url", 0);
	$site_url_path = get_setting_value($settings, "site_url", "");
	if ($full_image_url){
		$t->set_var("site_url", $site_url_path);					
	} else {
		$t->set_var("site_url", "");					
	}

	$r = new VA_Record($table_prefix . "global_settings");

	// load data to listbox
	$countries = get_db_values("SELECT country_id,country_name FROM " . $table_prefix . "countries ORDER BY country_order ", array(array("", "")));
	$admin_templates_dir_values = get_db_values("SELECT layout_id,layout_name FROM " . $table_prefix . "layouts", "");

	$records_per_page =
		array(
			array(5, 5), array(10, 10), array(15, 15),
			array(20, 20), array(25, 25), array(50, 50),
			array(75, 75), array(100, 100)
			);

	$product_controls =
		array(
			array("NONE",    va_message("NONE_MSG")),
			array("LABEL",   va_message("LABEL_MSG")),
			array("LISTBOX", va_message("LISTBOX_MSG")),
			array("TEXTBOX", va_message("TEXTBOX_MSG"))
			);

	$controls =
		array(
			array("NONE",  va_message("NONE_MSG")),
			array("LISTBOX", va_message("LISTBOX_MSG")),
			array("TEXTBOX", va_message("TEXTBOX_MSG"))
			);

	$yes_no =
		array(
			array(1, va_message("YES_MSG")), array(0, va_message("NO_MSG"))
			);

	$confirm_add =
		array(
			array(0, va_message("ADD_TO_CART_WITHOUT_CONFIRM_MSG")),
			array(1, va_message("ADD_TO_CART_SHOW_JS_CONFIRM_MSG"))
			);

	$basket_actions =
		array(
			array(0, va_message("REMAIN_ON_THE_SAME_PAGE_MSG")),
			array(1, va_message("GOTO_BASKET_PAGE_MSG")),
			array(2, va_message("GOTO_CHECKOUT_PAGE_MSG")),
			array(3, va_message("USE_AJAX_TO_ADD_PRODUCTS_MSG")),
			array("popup", va_message("SHOW_POPUP_FRAME_MSG")),
			);

	$user_registration =
		array(
			array(0, va_message("USER_CAN_BUY_WITHOUT_REGISTRATION_MSG")),
			array(1, va_message("USER_MUST_HAVE_ACCOUNT_TO_BUY_MSG"))
			);

	$subscription_page =
		array(
			array(0, va_message("SUBSCRIPTION_WITHOUT_REGISTRATION_MSG")),
			array(1, va_message("SUBSCRIPTION_REQUIRE_REGISTRATION_MSG"))
			);

	$display_products =
		array(
			array(0, va_message("FOR_ALL_USERS_MSG")),
			array(1, va_message("ONLY_FOR_LOGGED_IN_USERS_MSG")),
			array(2, va_message("WITHOUT_PRICES_FOR_NON_LOGGED_MSG"))
			);

	$show_currency =
		array(
			array(0, va_message("USE_ACTIVE_CURRENCY_MSG")),
			array(1, va_message("USE_ORDER_CURRENCY_MSG"))
			);

	$new_product_ranges =
		array(			
			array(0, va_message("LAST_7DAYS_MSG")),
			array(1, va_message("LAST_MONTH_MSG")),
			array(2, va_message("LAST_PAGE_MSG") . " X " . va_message("DAYS_MSG")),			
			array(3, va_message("FROM_DATE_MSG"))
		);
		
	$new_product_orders =
		array(			
			array(0, va_message("PROD_ISSUE_DATE_MSG")),
			array(1, va_message("DATE_ADDED_MSG")),
			array(2, va_message("DATE_MSG") . " " .va_message("ADMIN_MODIFIED_MSG"))
		);
		
	$tax_prices_types =
		array(
			array(0, va_message("PRICE_EXCL_TAX_MSG")),
			array(1, va_message("PRICE_INCL_TAX_MSG"))
			);

	$tax_types =
		array(
			array(0, va_message("PRICE_EXCL_TAX_MSG")),
			array(1, va_message("PRICE_EXCL_INCL_TAX_MSG")),
			array(2, va_message("PRICE_INCL_EXCL_TAX_MSG")),
			array(3, va_message("PRICE_INCL_TAX_MSG"))
			);

	$commission_types = array(
		array("", ""), array(0, va_message("NOT_AVAILABLE_MSG")), array(1, va_message("PERCENT_PER_PROD_FULL_PRICE_MSG")),
		array(2, va_message("FIXED_AMOUNT_PER_PROD_MSG")), array(3, va_message("PERCENT_PER_PROD_SELL_PRICE_MSG")),
		array(4, va_message("PERCENT_PER_PROD_SELL_BUY_MSG"))
	);

	$active_values = array(
		array(1, va_message("ACTIVE_MSG")), array(0, va_message("INACTIVE_MSG")), 
	);

	$points_price_types = array(
		array("", ""), array(0, va_message("POINTS_NOT_ALLOWED_MSG")), array(1, va_message("POINTS_ALLOWED_MSG")), 
	);

	$zero_price_types = array(
		array(0, va_message("SHOW_ZERO_PRICE_MSG")), 
		array(1, va_message("HIDE_ZERO_PRICE_MSG")), 
		array(2, va_message("SHOW_ZERO_PRICE_MESSAGE_MSG")), 
	);
	
	$zero_product_actions = array(
		array(1, va_message("ALLOW_ADD_ZERO_PRODUCTS_MSG")), 
		array(2, va_message("SHOW_WARNING_FOR_ZERO_PRODUCTS_MSG")), 
	);
	
	$show_reward_credits = array(
		array(0, va_message("FOR_ALL_USERS_MSG")),
		array(1, va_message("ONLY_FOR_LOGGED_IN_USERS_MSG")),
	);


	$open_large_image = array(
		array(0, va_message("IN_POPUP_WINDOW_MSG")),
		array(1, va_message("IN_ACTIVE_WINDOW_MSG"))
	);
	$watermark_positions = array(
		array("", ""),
		array("TL", va_message("TOP_LEFT_MSG")),
		array("TC", va_message("TOP_CENTER_MSG")),
		array("TR", va_message("TOP_RIGHT_MSG")),
		array("ML", va_message("MIDDLE_LEFT_MSG")),
		array("C",  va_message("CENTER_OF_IMAGE_MSG")),
		array("MR", va_message("MIDDLE_RIGHT_MSG")),
		array("BL", va_message("BOTTOM_LEFT_MSG")),
		array("BC", va_message("BOTTOM_CENTER_MSG")),
		array("BR", va_message("BOTTOM_RIGHT_MSG")),
		array("RND", va_message("RANDOM_POSITION_MSG")),
	);

	$google_base_export_types =
		array(
			array(0, va_message("MANUALLY_DOWNLOAD_XML_FILE_MSG")),
			array(1, va_message("USE_FTP_TO_UPLOAD_TO_GOOGLE_MSG"))
		);
		
	$google_base_country =
		array(
			array(0, va_message("GMC_OTHER_COUNTRIES_MSG")),
			array(1, va_message("GMC_US_MSG")),
			array(2, va_message("GMC_UK_DE_FR_MSG")),
			array(3, va_message("GMC_JP_MSG")),
			array(4, va_message("GMC_ALL_COUNTRIES_MSG"))
		);

	$prod_image_types =
		array(
			array(0, va_message("NO_IMAGE_MSG")),
			array(1, va_message("IMAGE_TINY_MSG")),
			array(2, va_message("IMAGE_SMALL_MSG")),
			array(3, va_message("IMAGE_LARGE_MSG"))
		);

	$resize_types =
		array(
			array("ratio", va_message("RESIZE_KEEP_RATIO_MSG")),
			array("canvas", va_message("RESIZE_FIT_CANVAS_MSG")),
		);


	// set up parameters
	$r->add_select("quantity_control_list", TEXT, $product_controls);
	$r->add_select("quantity_control_table", TEXT, $product_controls);
	$r->add_select("quantity_control_grid", TEXT, $product_controls);
	$r->add_select("quantity_control_details", TEXT, $product_controls);
	$r->add_select("quantity_control_basket", TEXT, $controls);
	$r->add_radio("confirm_add", TEXT, $confirm_add);
	$r->add_radio("redirect_to_cart", TEXT, $basket_actions);
	$r->add_checkbox("hide_add_limit", INTEGER);
	$r->add_checkbox("show_in_cart", INTEGER);
	$r->add_checkbox("cart_added_popup", INTEGER);
	$r->add_checkbox("cart_popup_view", INTEGER);
	$r->add_checkbox("cart_popup_checkout", INTEGER);

	$r->add_checkbox("coupons_enable", INTEGER);

	$r->add_select("user_registration", TEXT, $user_registration);
	$r->add_select("subscription_page", TEXT, $subscription_page);
	$r->add_select("display_products", TEXT, $display_products);
	$r->add_checkbox("access_out_stock", INTEGER);
	$r->add_checkbox("logout_cart_clear", INTEGER);
	$r->add_radio("orders_currency", TEXT, $show_currency);

	// run php code
	$r->add_checkbox("php_in_products_short_desc", INTEGER);
	$r->add_checkbox("php_in_products_full_desc", INTEGER);
	$r->add_checkbox("php_in_products_highlights", INTEGER);
	$r->add_checkbox("php_in_products_hot_desc", INTEGER);
	$r->add_checkbox("php_in_products_notes", INTEGER);
	$r->add_checkbox("php_in_products_download_terms", INTEGER);

	//New Product Functionality
	$r->add_checkbox("new_product_enable", INTEGER);
	$r->add_select("new_product_order", INTEGER, $new_product_orders);
	$r->add_select("new_product_range", INTEGER, $new_product_ranges);
	$r->add_textbox("new_product_from_date", TEXT);
	$r->change_property("new_product_from_date", VALUE_MASK, $date_edit_format);
	$r->add_textbox("new_product_x_days", INTEGER);
	
	// Tax
	$r->add_select("tax_prices_type", TEXT, $tax_prices_types);
	$r->add_select("tax_prices", TEXT, $tax_types);
	$r->add_textbox("tax_note", TEXT);
	$r->add_textbox("tax_note_excl", TEXT);

	// commissions
	$r->add_select("merchant_fee_type", INTEGER, $commission_types);
	$r->add_textbox("merchant_fee_amount", NUMBER, va_message("MERCHANT_FEE_AMOUNT_MSG"));
	$r->add_select("affiliate_commission_type", INTEGER, $commission_types);
	$r->add_textbox("affiliate_commission_amount", NUMBER, va_message("AFFILIATE_COMMISSION_AMOUNT_MSG"));
	$r->add_checkbox("affiliate_commission_deduct", NUMBER);
	$r->add_textbox("affiliate_cookie_expire", NUMBER, va_message("AFFILIATE_COOKIE_EXPIRES_MSG"));
	$r->add_textbox("min_payment_amount", NUMBER, va_message("MINIMUM_PAYMENT_AMOUNT_MSG"));
	$r->add_checkbox("tell_friend_param", NUMBER, va_message("TELL_FRIEND_PARAM_MSG"));
	$r->add_textbox("friend_cookie_expire", NUMBER, va_message("FRIEND_COOKIE_EXPIRES_MSG"));

	// Appearance
	$r->add_radio("zero_price_type", INTEGER, $zero_price_types);
	$r->add_textbox("zero_price_message", TEXT);
	$r->add_radio("zero_product_action", INTEGER, $zero_product_actions);
	$r->add_textbox("zero_product_warn", TEXT);
	
	$r->add_checkbox("price_matrix_list", INTEGER);
	$r->add_checkbox("price_matrix_details", INTEGER);

	$r->add_checkbox("item_code_list", INTEGER);
	$r->add_checkbox("item_code_table", INTEGER);
	$r->add_checkbox("item_code_grid", INTEGER);
	$r->add_checkbox("item_code_details", INTEGER);
	$r->add_checkbox("item_code_basket", INTEGER);
	$r->add_checkbox("item_code_checkout", INTEGER);
	$r->add_checkbox("item_code_invoice", INTEGER);
	$r->add_checkbox("item_code_email", INTEGER);
	$r->add_checkbox("item_code_reports", INTEGER);

	$r->add_checkbox("manufacturer_code_list", INTEGER);
	$r->add_checkbox("manufacturer_code_table", INTEGER);
	$r->add_checkbox("manufacturer_code_grid", INTEGER);
	$r->add_checkbox("manufacturer_code_details", INTEGER);
	$r->add_checkbox("manufacturer_code_basket", INTEGER);
	$r->add_checkbox("manufacturer_code_checkout", INTEGER);
	$r->add_checkbox("manufacturer_code_invoice", INTEGER);
	$r->add_checkbox("manufacturer_code_email", INTEGER);
	$r->add_checkbox("manufacturer_code_reports", INTEGER);

	$r->add_checkbox("stock_level_list", INTEGER);
	$r->add_checkbox("stock_level_table", INTEGER);
	$r->add_checkbox("stock_level_grid", INTEGER);
	$r->add_checkbox("stock_level_details", INTEGER);

	$r->add_checkbox("hide_add_list", INTEGER);
	$r->add_checkbox("hide_add_table", INTEGER);
	$r->add_checkbox("hide_add_grid", INTEGER);
	$r->add_checkbox("hide_add_details", INTEGER);
	$r->add_checkbox("hide_view_list", INTEGER);
	$r->add_checkbox("hide_view_table", INTEGER);
	$r->add_checkbox("hide_view_grid", INTEGER);
	$r->add_checkbox("hide_view_details", INTEGER);
	$r->add_checkbox("hide_checkout_list", INTEGER);
	$r->add_checkbox("hide_checkout_table", INTEGER);
	$r->add_checkbox("hide_checkout_grid", INTEGER);
	$r->add_checkbox("hide_checkout_details", INTEGER);
	$r->add_checkbox("hide_wishlist_list", INTEGER);
	$r->add_checkbox("hide_wishlist_table", INTEGER);
	$r->add_checkbox("hide_wishlist_grid", INTEGER);
	$r->add_checkbox("hide_wishlist_details", INTEGER);
	$r->add_checkbox("hide_more_list", INTEGER);
	$r->add_checkbox("hide_more_table", INTEGER);
	$r->add_checkbox("hide_more_grid", INTEGER);
	$r->add_checkbox("hide_shipping_details", INTEGER);
	$r->add_checkbox("hide_shipping_basket", INTEGER);
	$r->add_checkbox("hide_free_shipping_list", INTEGER);
	$r->add_checkbox("hide_free_shipping_table", INTEGER);
	$r->add_checkbox("hide_free_shipping_grid", INTEGER);
	$r->add_checkbox("hide_free_shipping_details", INTEGER);
	$r->add_checkbox("hide_weight_details", INTEGER);

	// options price appearance
	$r->add_textbox("option_positive_price_right", TEXT);
	$r->add_textbox("option_positive_price_left", TEXT);
	$r->add_textbox("option_negative_price_right", TEXT);
	$r->add_textbox("option_negative_price_left", TEXT);

	// rss settings
	$r->add_checkbox("is_rss", INTEGER);

	// override column names
	$r->add_textbox("ordinal_number_col_name", TEXT);
	$r->add_textbox("image_col_name", TEXT);
	$r->add_textbox("name_col_name", TEXT);
	$r->add_textbox("price_excl_col_name", TEXT);
	$r->add_textbox("tax_percent_col_name", TEXT);
	$r->add_textbox("tax_col_name", TEXT);
	$r->add_textbox("price_incl_col_name", TEXT);
	$r->add_textbox("quantity_col_name", TEXT);
	$r->add_textbox("total_excl_col_name", TEXT);
	$r->add_textbox("tax_total_col_name", TEXT);
	$r->add_textbox("total_incl_col_name", TEXT);

	// columns for basket page
	$r->add_checkbox("basket_ordinal_number", INTEGER);
	$r->add_checkbox("basket_item_name", INTEGER);
	$r->add_checkbox("basket_item_price", INTEGER);
	$r->add_checkbox("basket_item_tax_percent", INTEGER);
	$r->add_checkbox("basket_item_tax", INTEGER);
	$r->add_checkbox("basket_item_price_incl_tax", INTEGER);
	$r->add_checkbox("basket_item_quantity", INTEGER);
	$r->add_checkbox("basket_item_price_total", INTEGER);
	$r->add_checkbox("basket_item_tax_total", INTEGER);
	$r->add_checkbox("basket_item_price_incl_tax_total", INTEGER);
	$r->add_select("basket_item_image", INTEGER, $prod_image_types);

	// columns for basket page
	$r->add_checkbox("checkout_ordinal_number", INTEGER);
	$r->add_checkbox("checkout_item_name", INTEGER);
	$r->add_checkbox("checkout_item_price", INTEGER);
	$r->add_checkbox("checkout_item_tax_percent", INTEGER);
	$r->add_checkbox("checkout_item_tax", INTEGER);
	$r->add_checkbox("checkout_item_price_incl_tax", INTEGER);
	$r->add_checkbox("checkout_item_quantity", INTEGER);
	$r->add_checkbox("checkout_item_price_total", INTEGER);
	$r->add_checkbox("checkout_item_tax_total", INTEGER);
	$r->add_checkbox("checkout_item_price_incl_tax_total", INTEGER);
	$r->add_select("checkout_item_image", INTEGER, $prod_image_types);

	// columns for invoice page
	$r->add_checkbox("invoice_ordinal_number", INTEGER);
	$r->add_checkbox("invoice_item_name", INTEGER);
	$r->add_checkbox("invoice_item_price", INTEGER);
	$r->add_checkbox("invoice_item_tax_percent", INTEGER);
	$r->add_checkbox("invoice_item_tax", INTEGER);
	$r->add_checkbox("invoice_item_price_incl_tax", INTEGER);
	$r->add_checkbox("invoice_item_quantity", INTEGER);
	$r->add_checkbox("invoice_item_price_total", INTEGER);
	$r->add_checkbox("invoice_item_tax_total", INTEGER);
	$r->add_checkbox("invoice_item_price_incl_tax_total", INTEGER);
	$r->add_select("invoice_item_image", INTEGER, $prod_image_types);

	// columns for email page
	$r->add_checkbox("email_ordinal_number", INTEGER);
	$r->add_checkbox("email_item_name", INTEGER);
	$r->add_checkbox("email_item_price", INTEGER);
	$r->add_checkbox("email_item_tax_percent", INTEGER);
	$r->add_checkbox("email_item_tax", INTEGER);
	$r->add_checkbox("email_item_price_incl_tax", INTEGER);
	$r->add_checkbox("email_item_quantity", INTEGER);
	$r->add_checkbox("email_item_price_total", INTEGER);
	$r->add_checkbox("email_item_tax_total", INTEGER);
	$r->add_checkbox("email_item_price_incl_tax_total", INTEGER);
	$r->add_select("email_item_image", INTEGER, $prod_image_types);

	// points
	$r->add_radio("points_system", INTEGER, $active_values);
	$r->add_textbox("points_conversion_rate", NUMBER, va_message("POINTS_CONVERSION_RATE_MSG"));
	$r->add_textbox("points_decimals", INTEGER, va_message("POINTS_DECIMALS_MSG"));
	$r->add_checkbox("points_price_list", INTEGER);
	$r->add_checkbox("points_price_details", INTEGER);
	$r->add_checkbox("points_price_basket", INTEGER);
	$r->add_checkbox("points_price_checkout", INTEGER);
	$r->add_checkbox("points_price_invoice", INTEGER);
	$r->add_checkbox("points_price_email", INTEGER);
	$r->add_select("points_prices", INTEGER, $points_price_types);
	$r->add_select("points_shipping", INTEGER, $points_price_types);
	$r->add_select("points_orders_options", INTEGER, $points_price_types);
	$r->add_select("reward_type", INTEGER, $commission_types, va_message("REWARD_POINTS_TYPE_MSG"));
	$r->add_textbox("reward_amount", NUMBER, va_message("REWARD_POINTS_AMOUNT_MSG"));
	$r->add_checkbox("reward_points_list", INTEGER);
	$r->add_checkbox("reward_points_details", INTEGER);
	$r->add_checkbox("reward_points_basket", INTEGER);
	$r->add_checkbox("reward_points_checkout", INTEGER);
	$r->add_checkbox("reward_points_invoice", INTEGER);
	$r->add_checkbox("reward_points_email", INTEGER);
	$r->add_select("credit_reward_type", INTEGER, $commission_types, va_message("REWARD_CREDITS_TYPE_MSG"));
	$r->add_textbox("credit_reward_amount", NUMBER, va_message("REWARD_CREDITS_AMOUNT_MSG"));
	$r->add_checkbox("credits_balance_user_home", INTEGER);
	$r->add_checkbox("credits_balance_order_profile", INTEGER);
	$r->add_radio("reward_credits_users", INTEGER, $show_reward_credits);
	$r->add_checkbox("reward_credits_list", INTEGER);
	$r->add_checkbox("reward_credits_details", INTEGER);
	$r->add_checkbox("reward_credits_basket", INTEGER);
	$r->add_checkbox("reward_credits_checkout", INTEGER);
	$r->add_checkbox("reward_credits_invoice", INTEGER);
	$r->add_checkbox("reward_credits_email", INTEGER);
	$r->add_checkbox("points_for_points", INTEGER);
	$r->add_checkbox("credits_for_points", INTEGER);

	// credit system
	$r->add_radio("credit_system", INTEGER, $active_values);

	// Image settings
	$r->add_textbox("product_no_image_large", TEXT);
	$r->add_textbox("product_no_image", TEXT);
	$r->add_textbox("product_no_image_tiny", TEXT);
	$r->add_radio("open_large_image", TEXT, $open_large_image);
	$r->add_textbox("jpeg_quality", NUMBER);
	$r->change_property("jpeg_quality", MIN_VALUE, 0);
	$r->change_property("jpeg_quality", MAX_VALUE, 100);
	$r->add_textbox("canvas_bg", TEXT);

	$r->add_checkbox("resize_tiny_image", INTEGER);
	$r->add_checkbox("resize_small_image", INTEGER);
	$r->add_checkbox("resize_big_image", INTEGER);
	$r->add_checkbox("resize_super_image", INTEGER);
	$r->add_checkbox("show_preview_image", INTEGER);
	$r->add_textbox("tiny_image_max_width", INTEGER);
	$r->add_textbox("tiny_image_max_height", INTEGER);
	$r->add_radio("tiny_image_resize_type", TEXT, $resize_types);
	$r->add_textbox("tiny_image_resize_bg", TEXT);

	$r->add_textbox("small_image_max_width", INTEGER);
	$r->add_textbox("small_image_max_height", INTEGER);
	$r->add_radio("small_image_resize_type", TEXT, $resize_types);
	$r->add_textbox("small_image_resize_bg", TEXT);

	$r->add_textbox("big_image_max_width", INTEGER);
	$r->add_textbox("big_image_max_height", INTEGER);
	$r->add_radio("big_image_resize_type", TEXT, $resize_types);
	$r->add_textbox("big_image_resize_bg", TEXT);

	$r->add_textbox("super_image_max_width", INTEGER);
	$r->add_textbox("super_image_max_height", INTEGER);
	$r->add_radio("super_image_resize_type", TEXT, $resize_types);
	$r->add_textbox("super_image_resize_bg", TEXT);

	// customer images restrictions
	$r->add_textbox("user_tiny_image_width", INTEGER);
	$r->add_textbox("user_tiny_image_height", INTEGER);
	$r->add_textbox("user_tiny_image_size", INTEGER);
	$r->add_checkbox("user_resize_tiny_image", INTEGER);
	$r->add_checkbox("user_generate_tiny_image", INTEGER);
	$r->add_radio("user_tiny_resize_type", TEXT, $resize_types);

	$r->add_textbox("user_small_image_width", INTEGER);
	$r->add_textbox("user_small_image_height", INTEGER);
	$r->add_textbox("user_small_image_size", INTEGER);
	$r->add_checkbox("user_resize_small_image", INTEGER);
	$r->add_checkbox("user_generate_small_image", INTEGER);
	$r->add_radio("user_small_resize_type", TEXT, $resize_types);

	$r->add_textbox("user_large_image_width", INTEGER);
	$r->add_textbox("user_large_image_height", INTEGER);
	$r->add_textbox("user_large_image_size", INTEGER);
	$r->add_checkbox("user_resize_large_image", INTEGER);
	$r->add_checkbox("user_generate_large_image", INTEGER);
	$r->add_radio("user_large_resize_type", TEXT, $resize_types);

	$r->add_textbox("user_super_image_width", INTEGER);
	$r->add_textbox("user_super_image_height", INTEGER);
	$r->add_textbox("user_super_image_size", INTEGER);
	$r->add_checkbox("user_resize_super_image", INTEGER);
	$r->add_checkbox("user_generate_super_image", INTEGER);
	$r->add_radio("user_super_resize_type", TEXT, $resize_types);

	// watermark settings
	$r->add_textbox("watermark_image", TEXT);
	$r->add_select("watermark_image_pos", TEXT, $watermark_positions);
	$r->add_textbox("watermark_image_pct", INTEGER, va_message("IMAGE_TRANSPARENCY_MSG"));
	$r->change_property("watermark_image_pct", MIN_VALUE, 0);
	$r->change_property("watermark_image_pct", MAX_VALUE, 100);
	$r->add_checkbox("watermark_is_transparent", INTEGER);

	$r->add_textbox("watermark_text", TEXT);
	$r->add_select("watermark_text_pos", TEXT, $watermark_positions);
	$r->add_textbox("watermark_text_size", INTEGER);
	$r->add_textbox("watermark_text_color", TEXT);
	$r->add_textbox("watermark_text_angle", INTEGER);
	$r->add_textbox("watermark_text_pct", INTEGER, va_message("TEXT_TRANSPARENCY_MSG"));
	$r->change_property("watermark_text_pct", MIN_VALUE, 0);
	$r->change_property("watermark_text_pct", MAX_VALUE, 100);

	$r->add_checkbox("watermark_tiny_image", INTEGER);
	$r->add_checkbox("watermark_small_image", INTEGER);
	$r->add_checkbox("watermark_big_image", INTEGER);
	$r->add_checkbox("watermark_super_image", INTEGER);

	// custom product tabs
	$r->add_textbox("desc_order", INTEGER);
	$r->add_textbox("spec_order", INTEGER);
	$r->add_textbox("previews_order", INTEGER);
	$r->add_textbox("images_order", INTEGER);
	$r->add_textbox("accessories_order", INTEGER);
	$r->add_textbox("reviews_order", INTEGER);
	$r->add_textbox("questions_order", INTEGER);
	
	// google base settings
	$r->add_textbox("google_base_ftp_login", TEXT);
	$r->add_textbox("google_base_ftp_password", TEXT);
	$r->add_textbox("google_base_filename", TEXT);
	$r->add_textbox("google_base_title", TEXT);
	$r->add_textbox("google_base_description", TEXT);
	$google_base_encodings = 
		array(
			array("UTF-8", "UTF-8"),
			array("ISO-8859-1", "Latin-1 (ISO-8859-1)")
		);
	$r->add_select("google_base_encoding", TEXT, $google_base_encodings);
	$r->add_select("google_base_export_type", TEXT, $google_base_export_types );
	$r->add_textbox("google_base_save_path", TEXT);
	$r->add_checkbox("google_base_tax", INTEGER);
	$r->add_textbox("google_base_days_expiry", INTEGER);
	$r->add_select("google_base_country", TEXT, $google_base_country);
	$r->add_checkbox("google_base_show_stats", INTEGER);
	
	$google_base_product_conditions = 
		array(
			array("new",  va_message("NEW_MSG")),
			array("used", va_message("USED_MSG")),
			array("refurbished",  va_message("REFURBISHED_MSG"))
		);	
	
	$r->add_select("google_base_product_condition", TEXT, $google_base_product_conditions);

	
	// import/export options
	$r->add_checkbox("match_item_code", INTEGER);
	$r->add_checkbox("match_manufacturer_code", INTEGER);

	// fast checkout settings
	$r->add_checkbox("fast_checkout_country_show", INTEGER);
	$r->add_checkbox("fast_checkout_country_required", INTEGER);
	$r->add_checkbox("fast_checkout_state_show", INTEGER);
	$r->add_checkbox("fast_checkout_state_required", INTEGER);
	$r->add_checkbox("fast_checkout_postcode_show", INTEGER);
	$r->add_checkbox("fast_checkout_postcode_required", INTEGER);

	// keywords settings
	$keywords_types = array(array("1", va_message("PER_KEYWORD_MSG")), array("2", va_message("PER_FIELD_MSG")));
	$r->add_radio("keywords_search", INTEGER, $yes_no);
	$r->add_checkbox("item_name_index", INTEGER);
	$r->add_textbox("item_name_rank", INTEGER);
	$r->add_select("item_name_type", INTEGER, $keywords_types);
	$r->add_checkbox("item_code_index", INTEGER);
	$r->add_textbox("item_code_rank", INTEGER);
	$r->add_select("item_code_type", INTEGER, $keywords_types);
	$r->add_checkbox("manufacturer_code_index", INTEGER);
	$r->add_textbox("manufacturer_code_rank", INTEGER);
	$r->add_select("manufacturer_code_type", INTEGER, $keywords_types);
	$r->add_checkbox("short_description_index", INTEGER);
	$r->add_textbox("short_description_rank", INTEGER);
	$r->add_select("short_description_type", INTEGER, $keywords_types);
	$r->add_checkbox("full_description_index", INTEGER);
	$r->add_textbox("full_description_rank", INTEGER);
	$r->add_select("full_description_type", INTEGER, $keywords_types);
	$r->add_checkbox("highlights_index", INTEGER);
	$r->add_textbox("highlights_rank", INTEGER);
	$r->add_select("highlights_type", INTEGER, $keywords_types);
	$r->add_checkbox("special_offer_index", INTEGER);
	$r->add_textbox("special_offer_rank", INTEGER);
	$r->add_select("special_offer_type", INTEGER, $keywords_types);
	$r->add_checkbox("notes_index", INTEGER);
	$r->add_textbox("notes_rank", INTEGER);
	$r->add_select("notes_type", INTEGER, $keywords_types);
	$r->add_checkbox("meta_title_index", INTEGER);
	$r->add_textbox("meta_title_rank", INTEGER);
	$r->add_select("meta_title_type", INTEGER, $keywords_types);
	$r->add_checkbox("meta_description_index", INTEGER);
	$r->add_textbox("meta_description_rank", INTEGER);
	$r->add_select("meta_description_type", INTEGER, $keywords_types);
	$r->add_checkbox("meta_keywords_index", INTEGER);
	$r->add_textbox("meta_keywords_rank", INTEGER);
	$r->add_select("meta_keywords_type", INTEGER, $keywords_types);
	
	$r->get_form_values();

	// categories columns
	$ip = new VA_Record($table_prefix . "categories_columns", "categories_columns");
	$ip->add_where("column_id", INTEGER);
	$ip->add_hidden("category_id", INTEGER);
	$ip->change_property("category_id", USE_IN_INSERT, true);

	$ip->add_textbox("column_order", INTEGER, va_message("ADMIN_ORDER_MSG"));
	$ip->change_property("column_order", REQUIRED, true);
	$ip->add_textbox("column_class", TEXT, va_message("BLOCK_CSS_CLASS_MSG"));
	$ip->add_textbox("column_code", TEXT, va_message("CODE_MSG"));
	$ip->change_property("column_code", REQUIRED, true);
	$ip->change_property("column_code", MAX_LENGTH, 64);
	$ip->add_textbox("column_title", TEXT, va_message("TITLE_MSG"));
	$ip->change_property("column_title", REQUIRED, true);
	$ip->change_property("column_title", MAX_LENGTH, 255);
	$ip->add_textbox("column_html", TEXT, va_message("HTML_MSG"));

	$columns_number = get_param("cc_number");
	$cc_eg = new VA_EditGrid($ip, "categories_columns");
	$cc_eg->order_by = " ORDER BY column_order ";
	$cc_eg->get_form_values($columns_number);


	$param_site_id = get_session("session_site_id");
	$tab = get_param("tab");

	if (!$tab) { $tab = "general"; }
	$operation = get_param("operation");
	$return_page = get_param("rp");
	if (!strlen($return_page)) $return_page = "admin.php";
	if (strlen($operation))
	{
		if ($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		} elseif ($operation == "more_categories_columns") {
			$columns_number += 5;
		} else {

			$is_valid = $r->validate();
			$cc_valid = $cc_eg->validate();
	  
			if (!$is_valid) {
				$tab = "general";
			} else if (!$cc_valid) {
				$tab = "categories_columns";
			}
	  
			if ($is_valid && $cc_valid)
			{
				// update product settings
				$new_settings = array();
				foreach ($r->parameters as $key => $value) {
					$new_settings[$key] = $value[CONTROL_VALUE];
				}
				update_settings('products', $param_site_id, $new_settings);
	  
				// update/add categories columns
				$cc_eg->set_values("category_id", 0);
				$cc_eg->update_all($columns_number);

				//after update get data from database
				$cc_eg->set_value("category_id", 0);
				$cc_eg->change_property("column_id", USE_IN_SELECT, true);
				$cc_eg->change_property("column_id", USE_IN_WHERE, false);
				$cc_eg->change_property("category_id", USE_IN_WHERE, true);
				$cc_eg->change_property("category_id", USE_IN_SELECT, true);
				$columns_number = $cc_eg->get_db_values();
				// clear delete parameters from request
				for($i = 1; $i <= $columns_number; $i++) {
					$delete_param = "categories_columns_delete_".$i;
					if (isset($_GET[$delete_param])) {
						unset($_GET[$delete_param]);
					}
					if (isset($_POST[$delete_param])) {
						unset($_POST[$delete_param]);
					}
				}

				set_session("session_settings", "");
	  
				// show success message
				$t->parse("success_block", false);			
			}
		}
	}
	else // get products settings
	{
		foreach ($r->parameters as $key => $value)
		{
			$sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type='products' AND setting_name='" . $key . "'";
			$sql .= " AND ( site_id=1 OR  site_id=" . $db->tosql($param_site_id,INTEGER). ") ";
			$sql .= " ORDER BY site_id DESC ";
			$r->set_value($key, get_db_value($sql));
		}

		// check data for categories columns
		$cc_eg->set_value("category_id", 0);
		$cc_eg->change_property("column_id", USE_IN_SELECT, true);
		$cc_eg->change_property("column_id", USE_IN_WHERE, false);
		$cc_eg->change_property("category_id", USE_IN_WHERE, true);
		$cc_eg->change_property("category_id", USE_IN_SELECT, true);
		$columns_number = $cc_eg->get_db_values();
	}

	if ($columns_number == 0) {
		$columns_number = 5;
	}

	// set parameters
	$r->set_parameters();
	$t->set_var("rp", htmlspecialchars($return_page));
	// set categories columns
	$t->set_var("cc_number", $columns_number);
	$cc_eg->set_parameters_all($columns_number);

	$cart_added_popup = $r->get_value("cart_added_popup");
	if (!$cart_added_popup) {
		$t->set_var("popup_buttons_class", " disabled");
		$t->set_var("cart_popup_view_disabled", " disabled=\"disabled\" ");
		$t->set_var("cart_popup_checkout_disabled", " disabled=\"disabled\" ");
	}

	// multi-site settings
	multi_site_settings();

	// set styles for tabs
	$tabs = array(
		"general" => array("title" => va_message("ADMIN_GENERAL_MSG")), 
		"tax" => array("title" => va_message("TAX_SETTINGS_MSG")), 
		"appearance" => array("title" => va_message("PROD_APPEARANCE_MSG")), 
		"cart_columns" => array("title" => va_message("CART_COLUMNS_MSG")), 
		"merchants_affiliates" => array("title" => va_message("MERCHANT_MSG")."/".va_message("AFFILIATE_MSG")), 
		"points" => array("title" => va_message("POINTS_AND_CREDITS_MSG")), 
		"images" => array("title" => va_message("IMAGES_MSG")),
		"items_tabs" => array("title" => va_message("PRODUCT_TABS_MSG")),
		"google_base" => array("title" => va_message("GOOGLE_BASE_SETTINGS_MSG")),
		"import_export" => array("title" => va_message("IMPORT_EXPORT_MSG")),
		"fast_checkout" => array("title" => va_message("FAST_CHECKOUT_MSG")),
		"table_view" => array("title" => va_message("TABLE_VIEW_MSG")),
		"keywords" => array("title" => va_message("KEYWORDS_SEARCH_MSG")),
	);

	parse_tabs($tabs, $tab, "tab-twelve");

	include_once("./admin_footer.php");
	
	$t->pparse("main");

