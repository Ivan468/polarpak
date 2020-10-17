<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_product.php                                        ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/editgrid.php");
	include_once($root_folder_path . "includes/shopping_cart.php");
	include_once($root_folder_path . "includes/friendly_functions.php");
	include_once($root_folder_path . "includes/keywords_functions.php");
	include_once($root_folder_path . "includes/sites_table.php");
	include_once($root_folder_path . "includes/access_table.php");	
	include_once($root_folder_path . "includes/tabs_functions.php");	
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once($root_folder_path . "messages/" . $language_code . "/download_messages.php");
	include_once($root_folder_path . "messages/" . $language_code . "/reviews_messages.php");
	include_once("./admin_common.php");

	// check for fields update 
	$fields = $db->get_fields($table_prefix."items");
	$hide_fields = array(
		"hide_view_list" => false, "hide_view_details" => false, "hide_view_table" => false, "hide_view_grid" => false,
		"hide_checkout_list" => false, "hide_checkout_details" => false, "hide_checkout_table" => false, "hide_checkout_grid" => false,
		"hide_wishlist_list" => false, "hide_wishlist_details" => false, "hide_wishlist_table" => false, "hide_wishlist_grid" => false,
		"hide_more_list" => false, "hide_more_table" => false, "hide_more_grid" => false,
		"hide_free_shipping_list" => false, "hide_free_shipping_table" => false, "hide_free_shipping_grid" => false, "hide_free_shipping_details" => false,
		"hide_shipping_details" => false,
	);
	foreach ($fields as $id => $field_info) {
		$field_name = $field_info["name"];
		if (isset($hide_fields[$field_name])) {
			$hide_fields[$field_name] = true;
		}
	}
	foreach ($hide_fields as $field_name => $field_exists) {
		if (!$field_exists) {
			if ($db->DBType == "mysql") {
				$sql = "ALTER TABLE ".$table_prefix."items ADD COLUMN ".$field_name." TINYINT ";
			} else if ($db->DBType == "access") {
				$sql = "ALTER TABLE ".$table_prefix."items ADD COLUMN ".$field_name." SMALLINT ";
			} else {
				$sql = "ALTER TABLE ".$table_prefix."items ADD COLUMN ".$field_name." BYTE ";
			}
			$db->query($sql);
		}
	}
	// end field check

	check_admin_security("products_categories");
	$permissions = get_permissions();
	$add_products = get_setting_value($permissions, "add_products", 0);
	$update_products = get_setting_value($permissions, "update_products", 0);
	$remove_products = get_setting_value($permissions, "remove_products", 0);
	$duplicate_products = get_setting_value($permissions, "duplicate_products", 0);
	$approve_products = get_setting_value($permissions, "approve_products", 0);
	$html_editor = get_setting_value($settings, "html_editor", 1);
	$weight_measure = get_translation(get_setting_value($settings, "weight_measure", ""));
	$tax_price_type = get_setting_value($settings, "tax_prices_type", 0);
	$keywords_search = get_setting_value($settings, "keywords_search", 0);

	$category_id = get_param("category_id");
	if (!strlen($category_id)) { $category_id = "0"; }

	$content_types =
		array(
			array(1, HTML_MSG), array(0, PLAIN_TEXT_MSG)
		);

	$generate_serial_values =
		array(
			array(0, SERIAL_DONT_GENERATE_MSG), array(1, SERIAL_RANDOM_GENERATE_MSG), array(2, SERIAL_PREDEFINED_MSG)
		);

	$time_periods =
		array(
			array("", ""), array(1, DAY_MSG), array(2, WEEK_MSG), array(3, MONTH_MSG), array(4, YEAR_MSG)
		);

	$points_price_types =
		array(
			array("", ""), array(0, POINTS_NOT_ALLOWED_MSG), array(1, POINTS_ALLOWED_MSG),
		);

	$approve_values = array(array(1, YES_MSG), array(0, NO_MSG));

	$commission_types = array(
		array("", ""), array(0, NOT_AVAILABLE_MSG), array(1, PERCENT_PER_PROD_FULL_PRICE_MSG),
		array(2, FIXED_AMOUNT_PER_PROD_MSG), array(3, PERCENT_PER_PROD_SELL_PRICE_MSG),
		array(4, PERCENT_PER_PROD_SELL_BUY_MSG)
	);

	$download_types = array(
		array("", ""), 
		array(0, INACTIVE_MSG), 
		array(1, ACTIVE_MSG),
		array(2, USE_WITH_OPTIONS_MSG), 
	);

	$preview_types = array(
		array("", ""), 
		array(0, NOT_AVAILABLE_MSG), 
		array(1, PREVIEW_AS_DOWNLOAD_MSG),
		array(2, PREVIEW_USE_PLAYER_MSG), 
	);
	
	$preview_positions = array(
		array("", ""), 
		array(0, NOT_AVAILABLE_MSG), 
		array(1, PREVIEW_IN_SEPARATE_SECTION_MSG),
		array(2, PREVIEW_BELOW_DETAILS_IMAGE_MSG),
		array(3, PREVIEW_BELOW_LIST_IMAGE_MSG),
	);

	
	$discount_actions = array(
		array("", ""),
		array(0, DONT_USE_PRICE_DISCOUNT_MSG),
		array(1, DONT_APPLY_DISCOUNT_PRICE_MSG),
		array(2, APPLY_DISCOUNT_PRICE_MSG)
	);
	
	$date_format_msg = str_replace("{date_format}", join("", $date_edit_format), DATE_FORMAT_MSG);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$site_url_path = $settings["site_url"] ? $settings["site_url"] : "../";
	$t->set_var("css_file", $site_url_path . "styles/" . $settings["style_name"] . ".css?".VA_BUILD);
	$t->set_file("main", "admin_product.html");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("DISABLE_OUT_STOCK_DESC",      str_replace("{ADD_TO_CART_MSG}", ADD_TO_CART_MSG, DISABLE_OUT_STOCK_DESC));
	$t->set_var("hide_add_message", str_replace("{button_name}", ADD_TO_CART_MSG, HIDE_BUTTON_MSG));
	$t->set_var("hide_view_message", str_replace("{button_name}", VIEW_CART_MSG, HIDE_BUTTON_MSG));
	$t->set_var("hide_goto_message", str_replace("{button_name}", GOTO_CHECKOUT_MSG, HIDE_BUTTON_MSG));
	$t->set_var("hide_wish_message", str_replace("{button_name}", ADD_TO_WISHLIST_MSG, HIDE_BUTTON_MSG));
	$t->set_var("hide_more_message", str_replace("{button_name}", READ_MORE_MSG, HIDE_BUTTON_MSG));
	$t->set_var("hide_free_shipping_message", str_replace("{button_name}", FREE_SHIPPING_MSG, HIDE_BUTTON_MSG));
	$t->set_var("hide_shipping_message", str_replace("{button_name}", SHIPPING_CALCULATOR_MSG, HIDE_BUTTON_MSG));

	$item_id = get_param("item_id");

	$t->set_var("admin_product_href",         "admin_product.php");
	$t->set_var("admin_upload_href",          "admin_upload.php");
	$t->set_var("admin_select_href",          "admin_select.php");
	$t->set_var("admin_user_href",            "admin_user.php");
	$t->set_var("admin_items_list_href",      "admin_items_list.php");
	$t->set_var("admin_order_help_href",      "admin_order_help.php");
	$t->set_var("admin_order_statuses_href",	"admin_order_statuses.php");
	$t->set_var("admin_price_codes_href", 		"admin_price_codes.php");
	$t->set_var("admin_users_select_href",    "admin_users_select.php");
	$t->set_var("admin_products_settings_href",	"admin_products_settings.php");
	$t->set_var("admin_shipping_module_select_href",	"admin_shipping_module_select.php");
	$t->set_var("settings_product_reviews_href",	"settings_product_reviews.php");
	$t->set_var("settings_product_questions_href",	"settings_product_questions.php");
	$t->set_var("date_edit_format", join("", $date_edit_format));
	$t->set_var("date_format_msg", 	$date_format_msg);
	$t->set_var("html_editor", 			$html_editor);		
	$t->set_var("weight_measure", 	$weight_measure);
	if ($tax_price_type) {
		$t->set_var("tax_price_type_note", PRICES_INCLUDING_TAX_MSG);
	} else {
		$t->set_var("tax_price_type_note", PRICES_EXCLUDING_TAX_MSG);
	}
	
	$sql = "SELECT price_id, price_title, price_amount, price_description FROM " . $table_prefix . "prices ORDER BY price_id ";
	$db->query($sql);
	$price_codes = array(array("0", NONE_MSG));
	$price_codes_js = "";
	while ($db->next_record()) {
		$price_code_id          = $db->f("price_id");
		$price_code_title       = $db->f("price_title");
		$price_code_amount      = $db->f("price_amount");
		$price_code_description = $db->f("price_description");
		$price_codes[] = array($price_code_id, $price_code_title);
		$price_codes_js .= "price_codes[$price_code_id] = '$price_code_amount'; ";
	}	
	if ($price_codes_js) {
		$t->set_var("price_codes_js", $price_codes_js);
	}
	
	if (intval($item_id)) {
		$t->set_var("product_details_href", $root_folder_path."product_details.php?item_id=$item_id");
		$t->parse("view_live_product", false);
	} else {
		$t->set_var("view_live_product", "");
	}

	$t->set_var("currency_left", $currency["left"]);
	$t->set_var("currency_right", $currency["right"]);
	$t->set_var("currency_rate", $currency["rate"]);

	$duplicate_properties = get_param("duplicate_properties");
	$duplicate_specification = get_param("duplicate_specification");
	$duplicate_related = get_param("duplicate_related");
	$duplicate_categories = get_param("duplicate_categories");
	$duplicate_images = get_param("duplicate_images");
	$duplicate_accessories = get_param("duplicate_accessories");
	$duplicate_releases = get_param("duplicate_releases");
	$duplicate_user_types = get_param("duplicate_user_types");
	$duplicate_sites = get_param("duplicate_sites");
	
	$r = new VA_Record($table_prefix . "items");
	$r->add_hidden("rp", TEXT);

	// set up html form parameters
	$r->add_where("item_id", INTEGER);
	$r->add_textbox("is_draft", INTEGER);
	$r->add_textbox("user_id", INTEGER);
	$r->change_property("user_id", USE_SQL_NULL, false);
	$r->add_hidden("category_id", INTEGER);

	$r->add_checkbox("is_showing", INTEGER);
	$r->add_radio("is_approved", INTEGER, $approve_values, IS_APPROVED_MSG);
	if (!$approve_products) {
		$r->change_property("is_approved", SHOW, false);
		$r->change_property("is_approved", USE_IN_UPDATE, false);
	}
	$r->add_textbox("is_keywords", INTEGER);
	$r->change_property("is_keywords", USE_SQL_NULL, false);
/*
 *build bg type-default values-exoport values here
 */
	$sql = "SELECT item_type_id, google_base_type_id FROM " . $table_prefix . "item_types WHERE google_base_type_id > 0";
	$db->query($sql);
	$allowed_gb_ids = array();
	$allowed_gb_ids_js_obj = array();
	while ($db->next_record()) {
		$gb_id = $db->f("item_type_id");
		$gb_def_type = $db->f("google_base_type_id");
		$allowed_gb_ids[] = $gb_id;
		$allowed_gb_ids_js_obj[] = "defTypeFor_" . $gb_id . " : " . $gb_def_type;
	}
	$allowed_gb_ids = implode(",", $allowed_gb_ids);
	$allowed_gb_ids_js_obj = implode(",", $allowed_gb_ids_js_obj);
	$t->set_var("exported_ids", $allowed_gb_ids);
	$t->set_var("gb_default_values", $allowed_gb_ids_js_obj);

	// check allowed types for selected product categories
	$items_types_all = 0; $items_types_ids = array();
	$sql  = " SELECT c.items_types_all, c.items_types_ids ";
	if ($item_id) {
		$sql .= " FROM (".$table_prefix."categories c ";
		$sql .= " INNER JOIN ".$table_prefix."items_categories ic ON c.category_id=ic.category_id) ";
		$sql .= " WHERE ic.item_id=" . $db->tosql($item_id, INTEGER);
	} else {
		$sql .= " FROM ".$table_prefix."categories c ";
		$sql .= " WHERE c.category_id=" . $db->tosql($category_id, INTEGER);
	}
	$db->query($sql);
	if ($db->next_record())	 {
		do {
			$category_items_types_all = $db->f("items_types_all");
			if ($category_items_types_all) {
				$items_types_all = 1;
				break;
			}
			$category_items_types_ids = $db->f("items_types_ids");
			if (strlen($category_items_types_ids)) {
				$items_types_ids = array_merge($items_types_ids, explode(",", $category_items_types_ids));
			}
		} while ($db->next_record());
	} 

	$sql = "SELECT item_type_id, item_type_name FROM " . $table_prefix . "item_types";
	if (!$items_types_all && count($items_types_ids) > 0) {
		$sql .= " WHERE item_type_id IN (" . $db->tosql($items_types_ids, INTEGERS_LIST) . ")";
	}
	$item_types = get_db_values($sql, array(array("", "")));
	$r->add_select("item_type_id", INTEGER, $item_types, va_constant("PROD_TYPE_MSG"));
	$r->parameters["item_type_id"][REQUIRED] = true;	
	$conditions = get_db_values("SELECT condition_id, condition_name FROM " . $table_prefix . "conditions ORDER BY sort_order, condition_id ", array(array("", "")));
	$r->add_select("condition_id", INTEGER, $conditions, va_constant("CONDITION_MSG"));
	if (sizeof($conditions) <= 1) {
		$r->change_property("condition_id", SHOW, false);
	}
	$r->add_textbox("item_code", TEXT);
	$r->change_property("item_code", USE_SQL_NULL, false);
	$r->add_textbox("item_name", TEXT, PROD_NAME_MSG);
	$r->parameters["item_name"][REQUIRED] = true;
	$r->add_checkbox("is_new", INTEGER);
	$r->add_textbox("friendly_url", TEXT, FRIENDLY_URL_MSG);
	$r->change_property("friendly_url", USE_SQL_NULL, false);
	$r->change_property("friendly_url", BEFORE_VALIDATE, "validate_friendly_url");
	$r->change_property("friendly_url", REGEXP_MASK, FRIENDLY_URL_REGEXP);
	$r->change_property("friendly_url", REGEXP_ERROR, ALPHANUMERIC_ALLOWED_ERROR);
	$r->add_textbox("item_order", INTEGER, PROD_ORDER_MSG);
	$r->parameters["item_order"][REQUIRED] = true;
	$manufacturers = get_db_values("SELECT manufacturer_id,manufacturer_name FROM " . $table_prefix . "manufacturers ORDER BY manufacturer_name", array(array("", "")));
	$r->add_select("manufacturer_id", INTEGER, $manufacturers);
	$r->add_textbox("manufacturer_code", TEXT);
	$suppliers = get_db_values("SELECT supplier_id,supplier_name FROM " . $table_prefix . "suppliers ORDER BY supplier_order, supplier_name", array(array("", "")));
	$r->add_select("supplier_id", INTEGER, $suppliers, SUPPLIER_MSG);
	$r->change_property("supplier_id", USE_SQL_NULL, false);
	if (sizeof($suppliers) <= 1) {
		$r->change_property("supplier_id", SHOW, false);
	}
	$r->add_textbox("issue_date", DATETIME, PROD_ISSUE_DATE_MSG);
	$r->change_property("issue_date", VALUE_MASK, $date_edit_format);
	$r->add_checkbox("is_compared", INTEGER);
	$r->add_checkbox("tax_free", INTEGER);

	$languages = get_db_values("SELECT language_code, language_name FROM " . $table_prefix . "languages ORDER BY language_order, language_name ", array(array("", "")));
	$r->add_select("language_code", TEXT, $languages);
	$r->change_property("language_code", USE_SQL_NULL, false);

	$google_base_product_types = get_db_values ("SELECT type_id, type_name FROM " . $table_prefix . "google_base_types ORDER BY type_name", array(array(0, NOT_EXPORTED_MSG)));
	$r->add_select("google_base_type_id", INTEGER, $google_base_product_types);

	$tax_types = get_db_values ("SELECT tax_id, tax_name FROM " . $table_prefix . "tax_rates ", array(array("", "")));
	$r->add_select("tax_id", INTEGER, $tax_types);
	
	$r->add_checkbox("is_price_edit", NUMBER);
	$r->add_textbox("price", NUMBER, PROD_LIST_PRICE_MSG);
	$r->add_select("price_id", INTEGER, $price_codes, PROD_LIST_PRICE_MSG);
	$r->parameters["price"][REQUIRED] = true;
	$r->add_textbox("trade_price", NUMBER, PROD_TRADE_PRICE_MSG);
	$r->add_select("trade_price_id", INTEGER, $price_codes, PROD_LIST_PRICE_MSG);
	$r->add_checkbox("is_sales", INTEGER);
	$r->add_textbox("sales_price", NUMBER, PROD_DISCOUNT_PRICE_MSG);
	$r->add_select("sales_price_id", INTEGER, $price_codes, PROD_LIST_PRICE_MSG);
	$r->add_textbox("trade_sales", NUMBER, PROD_DISCOUNT_TRADE_MSG);
	$r->add_select("trade_sales_id", INTEGER, $price_codes, PROD_LIST_PRICE_MSG);
	$r->add_textbox("discount_percent", NUMBER);
	$r->add_textbox("buying_price", NUMBER, PROD_BUYING_PRICE_MSG);
	$r->add_select("buying_price_id", INTEGER, $price_codes, PROD_LIST_PRICE_MSG);	
	$r->add_textbox("properties_price", NUMBER, PROD_OPTIONS_PRICE_MSG);
	$r->add_select("properties_price_id", INTEGER, $price_codes, PROD_LIST_PRICE_MSG);
	$r->add_textbox("trade_properties_price", NUMBER, OPTIONS_TRADE_PRICE_MSG);
	$r->add_select("trade_properties_price_id", INTEGER, $price_codes, PROD_LIST_PRICE_MSG);

	// commissions
	$r->add_select("merchant_fee_type", INTEGER, $commission_types);
	$r->add_textbox("merchant_fee_amount", NUMBER, MERCHANT_FEE_AMOUNT_MSG);
	$r->add_select("affiliate_commission_type", INTEGER, $commission_types);
	$r->add_textbox("affiliate_commission_amount", NUMBER, AFFILIATE_COMMISSION_AMOUNT_MSG);

	// appearance
	$r->add_textbox("template_name", TEXT);
	$r->add_checkbox("hide_add_list", INTEGER);
	$r->add_checkbox("hide_add_details", INTEGER);
	$r->add_checkbox("hide_add_table", INTEGER);
	$r->add_checkbox("hide_add_grid", INTEGER);

	// new hide buttons
	$r->add_checkbox("hide_view_list", INTEGER);
	$r->add_checkbox("hide_view_details", INTEGER);
	$r->add_checkbox("hide_view_table", INTEGER);
	$r->add_checkbox("hide_view_grid", INTEGER);

	$r->add_checkbox("hide_checkout_list", INTEGER);
	$r->add_checkbox("hide_checkout_details", INTEGER);
	$r->add_checkbox("hide_checkout_table", INTEGER);
	$r->add_checkbox("hide_checkout_grid", INTEGER);

	$r->add_checkbox("hide_wishlist_list", INTEGER);
	$r->add_checkbox("hide_wishlist_details", INTEGER);
	$r->add_checkbox("hide_wishlist_table", INTEGER);
	$r->add_checkbox("hide_wishlist_grid", INTEGER);

	$r->add_checkbox("hide_more_list", INTEGER);
	$r->add_checkbox("hide_more_table", INTEGER);
	$r->add_checkbox("hide_more_grid", INTEGER);

	$r->add_checkbox("hide_free_shipping_list", INTEGER);
	$r->add_checkbox("hide_free_shipping_table", INTEGER);
	$r->add_checkbox("hide_free_shipping_grid", INTEGER);
	$r->add_checkbox("hide_free_shipping_details", INTEGER);

	$r->add_checkbox("hide_shipping_details", INTEGER);

	$r->add_textbox("preview_url", TEXT);
	$r->add_textbox("preview_width", INTEGER, WIDTH_MSG);
	$r->add_textbox("preview_height", INTEGER, HEIGHT_MSG);

	$r->add_textbox("highlights", TEXT);
	$r->add_textbox("short_description", TEXT);
	$r->add_radio("full_desc_type", INTEGER, $content_types);
	if ($html_editor){
		$r->change_property("full_desc_type", SHOW, false);
	}
	$r->add_textbox("full_description", TEXT);
	$r->add_textbox("a_title", TEXT);
	$r->add_textbox("meta_title", TEXT);
	$r->add_textbox("meta_keywords", TEXT);
	$r->add_textbox("meta_description", TEXT);

	$r->add_checkbox("is_special_offer", INTEGER);
	$r->add_textbox("special_order", INTEGER, SPECIAL_OFFER_TITLE.": ".ADMIN_ORDER_MSG);
	$r->add_textbox("special_offer", TEXT);
	// update those fields automatically only when default image set
	$r->add_textbox("tiny_image", TEXT);
	$r->change_property("tiny_image", USE_IN_UPDATE, false);
	$r->add_textbox("tiny_image_alt", TEXT);
	$r->change_property("tiny_image_alt", USE_IN_UPDATE, false);
	$r->add_textbox("small_image", TEXT);
	$r->change_property("small_image", USE_IN_UPDATE, false);
	$r->add_textbox("small_image_alt", TEXT);
	$r->change_property("small_image_alt", USE_IN_UPDATE, false);
	$r->add_textbox("big_image", TEXT);
	$r->change_property("big_image", USE_IN_UPDATE, false);
	$r->add_textbox("big_image_alt", TEXT);
	$r->change_property("big_image_alt", USE_IN_UPDATE, false);
	$r->add_textbox("super_image", TEXT);
	$r->change_property("super_image", USE_IN_UPDATE, false);
	$r->add_textbox("super_image_alt", TEXT);
	$r->change_property("super_image_alt", USE_IN_UPDATE, false);

	// recurring options
	$r->add_checkbox("is_recurring", INTEGER);
	$r->add_textbox("recurring_price", NUMBER, RECURRING_PRICE_MSG);
	$r->add_select("recurring_period", INTEGER, $time_periods, RECURRING_PERIOD_MSG);
	$r->add_textbox("recurring_interval", INTEGER, RECURRING_INTERVAL_MSG);
	$r->add_textbox("recurring_payments_total", INTEGER, RECURRING_PAYMENTS_TOTAL_MSG);
	$r->add_textbox("recurring_start_date", DATETIME, RECURRING_START_DATE_MSG);
	$r->change_property("recurring_start_date", VALUE_MASK, $date_edit_format);
	$r->add_textbox("recurring_end_date", DATETIME, RECURRING_END_DATE_MSG);
	$r->change_property("recurring_end_date", VALUE_MASK, $date_edit_format);

	// points fields
	$r->add_select("is_points_price", INTEGER, $points_price_types, PROD_PAY_POINTS_MSG);
	$r->add_textbox("points_price", NUMBER, POINTS_PRICE_MSG);
	$r->add_select("reward_type", INTEGER, $commission_types, REWARD_POINTS_TYPE_MSG);
	$r->add_textbox("reward_amount", NUMBER, REWARD_POINTS_AMOUNT_MSG);
	$r->add_select("credit_reward_type", INTEGER, $commission_types, REWARD_CREDITS_TYPE_MSG);
	$r->add_textbox("credit_reward_amount", NUMBER, REWARD_CREDITS_AMOUNT_MSG);

	// package parameters
	$r->add_textbox("packages_number", NUMBER, PACKAGES_NUMBER_MSG);
	$r->add_textbox("weight", NUMBER, WEIGHT_MSG);
	$r->add_textbox("actual_weight", NUMBER, ACTUAL_WEIGHT_MSG);
	$r->add_textbox("width", NUMBER, WIDTH_MSG);
	$r->change_property("width", MIN_VALUE, 0);
	$r->add_textbox("height", NUMBER, HEIGHT_MSG);
	$r->change_property("height", MIN_VALUE, 0);
	$r->add_textbox("length", NUMBER, LENGTH_MSG);
	$r->change_property("length", MIN_VALUE, 0);

	// stock level
	$r->add_textbox("stock_level", NUMBER);
	$r->add_checkbox("use_stock_level", INTEGER);
	$r->add_checkbox("hide_out_of_stock", INTEGER);
	$r->add_checkbox("disable_out_of_stock", INTEGER);
	$r->add_textbox("min_quantity", INTEGER, MINIMUM_ITEMS_QTY_MSG);
	$r->add_textbox("max_quantity", INTEGER, MAXIMUM_ITEMS_QTY_MSG);
	$r->add_textbox("quantity_increment", INTEGER, QTY_INCREMENT_MSG);

	// shipping
	$r->add_checkbox("downloadable", INTEGER);
	$r->add_checkbox("is_shipping_free", INTEGER);
	$r->add_textbox("shipping_cost", NUMBER, SHIPPING_COST_MSG);
	$times = get_db_values("SELECT shipping_time_id,shipping_time_desc FROM " . $table_prefix . "shipping_times", array(array("", NONE_MSG)), 90);
	$r->add_select("shipping_in_stock", INTEGER, $times);
	$r->add_select("shipping_out_stock", INTEGER, $times);

	$rules = get_db_values("SELECT shipping_rule_id,shipping_rule_desc FROM " . $table_prefix . "shipping_rules", array(array("", NONE_MSG)), 90);
	$r->add_select("shipping_rule_id", INTEGER, $rules);
	$r->add_checkbox("shipping_modules_default", INTEGER);
	$r->change_property("shipping_modules_default", DEFAULT_VALUE, 1);
	$r->add_textbox("shipping_modules_ids", TEXT);

	// Other settings tab
	$allow_values = 
		array( 
			array("", va_constant("DEFAULT_SETTINGS_MSG")), array(-1, va_constant("DISABLED_MSG")), 
		);

	$r->add_textbox("notes", TEXT);
	$r->add_textbox("buy_link", TEXT);
	$r->add_textbox("total_views", INTEGER);
	$r->change_property("total_views", USE_IN_INSERT, false);
	$r->change_property("total_views", USE_IN_UPDATE, false);
	$r->add_textbox("votes", INTEGER);
	$r->add_textbox("points", INTEGER);
	$r->add_radio("allow_reviews", INTEGER, $allow_values);
	$r->add_radio("allow_questions", INTEGER, $allow_values);

	/*
	// TODO: icecat fields
	$icecat_statuses = array(
		array(0, "ICECat N/A"),
		array(1, "Ready for Import"),
		array(2, "Import Error"),
		array(3, "Data Imported"),
	);
	$r->add_textbox("icecat_updated", DATETIME, "Date Updated");
	$r->change_property("icecat_updated", VALUE_MASK, $date_edit_format);
	$r->add_select("icecat_status_id", INTEGER, $icecat_statuses);
	$r->add_textbox("icecat_error", TEXT);
	// END TODO*/

	// quantity prices
	$ip = new VA_Record($table_prefix . "items_prices", "prices");
	$ip->add_where("quantity_price_id", INTEGER);
	$ip->change_property("quantity_price_id", COLUMN_NAME, "price_id");
	$ip->add_hidden("item_id", INTEGER);
	$ip->change_property("item_id", USE_IN_INSERT, true);
	$ip->change_property("item_id", USE_IN_INSERT, true);
	$ip->change_property("item_id", PARSE_NAME, "price_item_id");

	$ip->add_checkbox("is_active", INTEGER, ACTIVE_MSG);
	$ip->add_textbox("ip_min_quantity", INTEGER, MIN_QTY_MSG);
	$ip->change_property("ip_min_quantity", REQUIRED, true);
	$ip->change_property("ip_min_quantity", MIN_VALUE, 1);
	$ip->change_property("ip_min_quantity", BEFORE_VALIDATE, "check_item_quantity");
	$ip->change_property("ip_min_quantity", COLUMN_NAME, "min_quantity");
	$ip->add_textbox("ip_max_quantity", INTEGER, MAX_QTY_MSG);
	$ip->change_property("ip_max_quantity", REQUIRED, true);
	$ip->change_property("ip_max_quantity", MIN_VALUE, 1);
	$ip->change_property("ip_max_quantity", BEFORE_SHOW, "check_max_quantity");
	$ip->change_property("ip_max_quantity", COLUMN_NAME, "max_quantity");
	$ip->add_textbox("quantity_price", NUMBER, INDIVIDUAL_PRICE_MSG);
	//$ip->change_property("price", PARSE_NAME, "quantity_price");
	$ip->change_property("quantity_price", REQUIRED, "quantity_price");
	$ip->change_property("quantity_price", COLUMN_NAME, "price");
	$ip->add_textbox("properties_discount", NUMBER, OPTIONS_DISCOUNT_MSG);

	$user_types = get_db_values("SELECT type_id, type_name FROM " . $table_prefix . "user_types ", array(array("", ""), array("0", FOR_ALL_USERS_MSG)));
	$ip->add_select("user_type_id", INTEGER, $user_types, USER_TYPE_MSG);
	
	if ($sitelist) {
		$error_colspan = 8;
		$total_colspan = 10;
		$sites = get_db_values("SELECT site_id, site_name FROM " . $table_prefix . "sites ORDER BY site_id ", array(array("", ""), array("0", "All Sites")));
		$ip->add_select("site_id", INTEGER, $sites, ADMIN_SITE_MSG);
		$ip->change_property("site_id", USE_SQL_NULL, false);
		$t->parse("site_column", false);
	} else {
		$error_colspan = 7;
		$total_colspan = 9;
		$ip->add_textbox("site_id", INTEGER);
		$ip->change_property("site_id", SHOW, false);
		$ip->change_property("site_id", USE_SQL_NULL, false);
	}
	$t->set_var("error_colspan", $error_colspan);
	$t->set_var("total_colspan", $total_colspan);

	$ip->add_radio("discount_action", NUMBER, $discount_actions, DISCOUNT_SETTINGS_MSG);
	$ip->parameters["discount_action"][REQUIRED] = true;

	$number_prices = get_param("number_prices");
	$ip_eg = new VA_EditGrid($ip, "prices");
	$ip_eg->get_form_values($number_prices);
	$ip_eg->set_event(BEFORE_INSERT, "check_site_id");
	$ip_eg->set_event(BEFORE_UPDATE, "check_site_id");


	// custom tabs
	$itab  = new VA_Record($table_prefix . "items_tabs", "items_tabs");
	$itab->add_where("tab_id", INTEGER);
	//$itab->change_property("quantity_price_id", COLUMN_NAME, "price_id");
	$itab->add_hidden("item_id", INTEGER);
	$itab->change_property("item_id", USE_IN_INSERT, true);
	$itab->change_property("item_id", USE_IN_INSERT, true);
	$itab->change_property("item_id", PARSE_NAME, "tab_item_id");

	$itab->add_textbox("tab_order", INTEGER, ADMIN_ORDER_MSG);
	$itab->change_property("tab_order", REQUIRED, true);
	$itab->add_textbox("tab_title", TEXT, TITLE_MSG);
	$itab->change_property("tab_title", REQUIRED, true);
	$itab->add_textbox("tab_desc", TEXT, INDIVIDUAL_PRICE_MSG);
	$itab->add_checkbox("hide_tab", INTEGER, HIDE_MSG);

	$number_items_tabs = get_param("number_items_tabs");
	$itab_eg = new VA_EditGrid($itab, "items_tabs");
	$itab_eg->get_form_values($number_items_tabs);
	// end customer tabs

	// downloadable options
	$r->add_checkbox("download_show_terms", INTEGER);
	$r->add_textbox("download_terms_text", TEXT, TERMS_MSG);

	// set up html form parameters
	$itf = new VA_Record($table_prefix . "items_files", "files");
	$itf->add_where("file_id", INTEGER);
	$itf->add_hidden("item_id", INTEGER);
	$itf->change_property("item_id", USE_IN_INSERT, true);
	$itf->change_property("item_id", PARSE_NAME, "download_item_id");
	$itf->add_hidden("item_type_id", INTEGER);
	$itf->change_property("item_type_id", USE_IN_INSERT, true);
	$itf->change_property("item_type_id", PARSE_NAME, "download_item_type_id");

	$itf->add_select("download_type", INTEGER, $download_types);
	$itf->add_textbox("download_title", TEXT, DOWNLOAD_TITLE_MSG);
	$itf->change_property("download_title", REQUIRED, true);
	$itf->add_textbox("download_path", TEXT, DOWNLOAD_PATH_MSG);
	$itf->add_select("download_period", INTEGER, $time_periods, DOWNLOAD_PERIOD_MSG);
	$itf->add_textbox("download_interval", INTEGER, DOWNLOAD_INTERVAL_MSG);
	$itf->add_textbox("download_limit", INTEGER, DOWNLOAD_LIMIT_MSG);
	$itf->add_select("preview_type", INTEGER, $preview_types, PREVIEW_TYPE_MSG);
	$itf->add_select("preview_position", INTEGER, $preview_positions, PREVIEW_POSITION_MSG);
	$itf->add_textbox("preview_title", TEXT, PREVIEW_TITLE_MSG);
	$itf->add_textbox("preview_path", TEXT, PREVIEW_PATH_MSG);
	$itf->add_textbox("preview_image", TEXT, PREVIEW_IMAGE_MSG);
	$itf->set_event(BEFORE_VALIDATE, "items_files_properties");

	$number_files = get_param("number_files");
	$itf_eg = new VA_EditGrid($itf, "files");
	$itf_eg->get_form_values($number_files);

	// serial number
	$r->add_radio("generate_serial", INTEGER, $generate_serial_values);
	$r->add_textbox("serial_period", INTEGER, SERIAL_PERIOD_MSG);
	$r->add_textbox("activations_number", INTEGER, ACTIVATION_MAX_NUMBER_MSG);

	// set up serial numbers parameters
	$is = new VA_Record($table_prefix . "items_serials", "serials");
	$is->add_where("serial_id", INTEGER);
	$is->add_hidden("item_id", INTEGER);
	$is->change_property("item_id", USE_IN_INSERT, true);
	$is->change_property("item_id", PARSE_NAME, "serial_item_id");

	$is->add_textbox("serial_number", TEXT, SERIAL_NUMBER_COLUMN);
	$is->parameters["serial_number"][REQUIRED] = true;
	$is->add_checkbox("used", INTEGER);

	$number_serials = get_param("number_serials");
	$serials_page = get_param("serials_page");
	if (!$serials_page) { $serials_page = 1; }
	$serials_per_page = 100;
	$is_eg = new VA_EditGrid($is, "serials");
	$is_eg->order_by = " ORDER BY used, serial_id ";
	$is_eg->get_form_values($number_serials);

	// calculate number of serial numbers
	$serials_recs = 0;
	if ($item_id) {
		$sql = " SELECT COUNT(*) FROM " . $table_prefix . "items_serials ";
		$sql.= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
		$serials_recs = get_db_value($sql);
	}

	if ($serials_recs > $serials_per_page) {
		$page_index = 0;
		for ($i = 1; $i <= ceil($serials_recs / $serials_per_page); $i++) {
			if ($serials_page == $i) {
				$t->set_var("page_style", "border: 1px solid silver; background: #f0f0f0; font-weight: bold;");
			} else {
				$t->set_var("page_style", "border: 1px solid white; ");
			}
			$t->set_var("page_number", $i);
			$t->parse("serials_navigator", true);
		}
	}



	// notification fields
	$r->add_checkbox("mail_notify", INTEGER);
	$r->add_textbox("mail_to", TEXT);
	$r->add_textbox("mail_from", TEXT);
	$r->add_textbox("mail_cc", TEXT);
	$r->add_textbox("mail_bcc", TEXT);
	$r->add_textbox("mail_reply_to", TEXT);
	$r->add_textbox("mail_return_path", TEXT);
	$r->add_textbox("mail_subject", TEXT);
	$r->add_radio("mail_type", INTEGER, $content_types);
	$r->parameters["mail_type"][DEFAULT_VALUE] = 0;
	$r->add_textbox("mail_body", TEXT);

	$r->add_checkbox("sms_notify", INTEGER);
	$r->add_textbox("sms_recipient", TEXT, SMS_RECIPIENT_MSG);
	$r->add_textbox("sms_originator",TEXT, SMS_ORIGINATOR_MSG);
	$r->add_textbox("sms_message",   TEXT, SMS_MESSAGE_MSG);

	// editing information
	$r->add_textbox("admin_id_added_by", INTEGER);
	$r->change_property("admin_id_added_by", USE_IN_UPDATE, false);
	$r->add_textbox("admin_id_modified_by", INTEGER);

	$r->add_textbox("date_added", DATETIME);
	$r->change_property("date_added", USE_IN_INSERT, true);
	$r->change_property("date_added", USE_IN_UPDATE, false);
	$r->add_textbox("date_modified", DATETIME);
	$r->change_property("date_modified", USE_IN_INSERT, true);
	$r->change_property("date_modified", USE_IN_UPDATE, true);

	$r->add_hidden("default_properties", TEXT);

	$r->add_checkbox("sites_all", INTEGER);	
	$r->add_textbox("access_level", INTEGER);
	$r->add_textbox("guest_access_level", INTEGER);
	$r->add_textbox("admin_access_level", INTEGER);

	$r->get_form_values();
	if ($html_editor){
		$r->set_value("full_desc_type", 1);
	}
	if (!$sitelist) {
		$r->set_value("sites_all", 1);
	}

	$operation = get_param("operation");
	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }
	$return_page = $r->get_value("rp");
	if (!$return_page) {
		$return_page = "admin_items_list.php?category_id=" . $category_id;
	}
	$downloads_errors = ""; $recurring_errors = "";

	$access_table = new VA_Access_Table($settings["admin_templates_dir"], "access_table.html");
	$access_table->set_access_levels(
		array(
			VIEW_CATEGORIES_ITEMS_PERM => array(VIEW_MSG, VIEW_ITEM_IN_THE_LIST_MSG), 
			VIEW_ITEMS_PERM => array(ACCESS_DETAILS_MSG, ACCESS_ITEMS_DETAILS_MSG)
		)
	);

	$access_table->set_tables("items", "items_user_types",  "items_subscriptions", "item_id", false, $item_id);

	$sites_table = new VA_Sites_Table($settings["admin_templates_dir"], "sites_table.html");
	$sites_table->set_tables("items", "items_sites", "item_id", false, $item_id);
	
	// assign properties
	if (strlen($operation)) {
		$properties_assigned = explode(",", get_param("properties_assigned"));
		$properties_descriptions = array();
		foreach ($properties_assigned AS $property_id) {
			$properties_descriptions[$property_id] = get_param("properties_descriptions_" . $property_id); 
		}
		$properties_assigned_values = array_unique(explode(",", get_param("properties_assigned_values")));	
		$properties_default_values  = array_unique(explode(",", get_param("properties_default_values")));		
	} else {
		$sql  = " SELECT property_id, property_description FROM " . $table_prefix. "items_properties_assigned ";
		$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER, true, false);
		$db->query($sql);
		$properties_assigned = array();
		$properties_descriptions = array();
		while ($db->next_record()) {			
			$properties_assigned[] = $db->f(0);
			$properties_descriptions[$db->f(0)] = $db->f(1);
		}
		$sql  = " SELECT property_value_id, is_default_value FROM " . $table_prefix. "items_values_assigned ";
		$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER, true, false);
		$db->query($sql);
		$properties_assigned_values = array();
		$properties_default_values = array();
		while ($db->next_record()) {
			$properties_assigned_values[] = $db->f(0);
			if ($db->f(1)) {
				$properties_default_values[] = $db->f(0);
			}
		}
	}

	if (strlen($operation))
	{
		$r->set_value("access_level", $access_table->all_selected_access_level);
		$r->set_value("guest_access_level", $access_table->guest_selected_access_level);
		$r->set_value("admin_access_level", $access_table->admin_selected_access_level);
		if ($operation == "cancel") {
			header("Location: " . $return_page);
			exit;
		} elseif ($operation == "delete" && $item_id) {
			delete_products($item_id);
			header("Location: " . $return_page);
			exit;
		} elseif ($operation == "more_prices") {
			$number_prices += 5;
		} elseif ($operation == "more_items_tabs") {
			$number_items_tabs += 5;
		} elseif ($operation == "more_serials") {
			$number_serials += 5;
		} elseif ($operation == "serials_page") {
			// check data for serial numbers for selected page
			$is_eg->set_value("item_id", $item_id);
			$is_eg->change_property("serial_id", USE_IN_SELECT, true);
			$is_eg->change_property("serial_id", USE_IN_WHERE, false);
			$is_eg->change_property("item_id", USE_IN_WHERE, true);
			$is_eg->change_property("item_id", USE_IN_SELECT, true);
			$db->PageNumber     = $serials_page;
			$db->RecordsPerPage = $serials_per_page;
			$number_serials = $is_eg->get_db_values();
		} elseif ($operation == "more_files") {
			$number_files += 4;
		} else {				
			$is_valid = $r->validate();
			$ip_valid = $ip_eg->validate();
			$itab_valid = $itab_eg->validate();
			$itf_valid = $itf_eg->validate();
			$serials_valid = $is_eg->validate();
			if ($is_valid) {
				if (!$ip_valid) {
					$tab = "quantity_prices";
				} else if (!$itab_valid) {
					$tab = "items_tabs";
				} else if (!$itf_valid || !$serials_valid) {
					$tab = "downloads";
				}
			} else {
				$tab = "general";
			}

			if ($is_valid && $ip_valid && $itab_valid && $itf_valid && $serials_valid) {
				if ($r->get_value("is_recurring")) {
					$r->change_property("recurring_period", REQUIRED, true);
					$r->change_property("recurring_interval", REQUIRED, true);
					$r->change_property("recurring_interval", MIN_VALUE, 1);
					$r->change_property("recurring_payments_total", MIN_VALUE, 1);
				}
				$is_valid = $r->validate();
				if (!$is_valid) {
					$tab = "recurring";
					$recurring_errors = $r->errors;
					$r->errors = "";
				}
			}

			if ($is_valid && $ip_valid && $itab_valid && $itf_valid && $serials_valid) {		
				// set admin data
				$r->set_value("is_draft", 0); // set it always 0 after admin update
				$r->set_value("date_added", va_time());
				$r->set_value("date_modified", va_time());
				$r->set_value("admin_id_added_by", get_session("session_admin_id"));
				$r->set_value("admin_id_modified_by", get_session("session_admin_id"));

				if ($r->is_empty("trade_price")) {
					$r->set_value("trade_price", 0);
				}
				if ($r->is_empty("sales_price")) {
					$r->set_value("sales_price", 0);
				}
				if ($r->is_empty("trade_sales")) {
					$r->set_value("trade_sales", 0);
				}
				if ($r->is_empty("properties_price")) {
					$r->set_value("properties_price", 0);
				}
				if ($r->is_empty("trade_properties_price")) {
					$r->set_value("trade_properties_price", 0);
				}

				if ($operation == "duplicate" && $item_id) {
					// check if frindly url wasn't changed
					$sql  = " SELECT friendly_url FROM " . $table_prefix . "items ";
					$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
					$current_friendly_url = get_db_value($sql);
					if ($current_friendly_url == $r->get_value("friendly_url")) {
						$r->set_value("friendly_url", "");
					}
					// duplicate product with new id
					$r->set_value("is_draft", 1);
					set_friendly_url();
					$record_updated = $r->insert_record();
					if (!$record_updated) {
						$r->set_value("item_id", "");
					} else {
						$new_item_id = $db->last_insert_id();
						$r->set_value("item_id", $new_item_id);
					}
					if ($record_updated && $keywords_search) {
						generate_keywords($r->get_data());
					}
					// duplicate product type options
					update_properties_assigned($new_item_id, $properties_default_values);

					// duplicate product features
					if ($duplicate_specification == 1 && $record_updated) {
						$item_features = array();
						$sql  = " SELECT group_id, feature_name, feature_value, google_base_attribute_id FROM " . $table_prefix . "features ";
						$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
						$sql .= " ORDER BY feature_id ";
						$db->query($sql);
						while ($db->next_record()) {
							$item_features[] = array($db->f("group_id"), $db->f("feature_name"), $db->f("feature_value"), $db->f("google_base_attribute_id"));
						}
						for ($i = 0; $i < sizeof($item_features); $i++) {
							$group_id = $item_features[$i][0];
							$feature_name = $item_features[$i][1];
							$feature_value = $item_features[$i][2];
							$google_base_attribute_id = $item_features[$i][3];
							$sql  = " INSERT INTO " . $table_prefix . "features (item_id, group_id, feature_name, feature_value, google_base_attribute_id) VALUES (";
							$sql .= $db->tosql($new_item_id, INTEGER) . "," . $db->tosql($group_id, INTEGER) . "," . $db->tosql($feature_name, TEXT) . "," . $db->tosql($feature_value, TEXT) . "," . $db->tosql($google_base_attribute_id, INTEGER, true, false) . ")";
							$db->query($sql);
						}
					}

					// duplicate related products
					if ($duplicate_related == 1 && $record_updated) {
						$item_related = array();
						$sql  = " SELECT related_id, related_order FROM " . $table_prefix . "items_related ";
						$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
						$sql .= " ORDER BY related_order ";
						$db->query($sql);
						while ($db->next_record()) {
							$item_related[] = array($db->f("related_id"), $db->f("related_order"));
						}
						for ($i = 0; $i < sizeof($item_related); $i++) {
							$item_related_id = $item_related[$i][0];
							$item_related_order = $item_related[$i][1];
							$sql  = " INSERT INTO " . $table_prefix . "items_related (item_id, related_id, related_order) VALUES (";
							$sql .= $db->tosql($new_item_id, INTEGER) . "," . $db->tosql($item_related_id, INTEGER) . "," . $db->tosql($item_related_order, INTEGER) . ")";
							$db->query($sql);
						}
					}

					// duplicate product categories
					if ($duplicate_categories == 1 && $record_updated) {
						$item_categories = array();
						$sql  = " SELECT category_id FROM " . $table_prefix . "items_categories ";
						$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
						$db->query($sql);
						while ($db->next_record()) {
							$item_categories[] = $db->f("category_id");
						}
						for ($i = 0; $i < sizeof($item_categories); $i++) {
							$item_category_id = $item_categories[$i];
							$sql  = " SELECT MAX(item_order) FROM " . $table_prefix . "items_categories";
							$sql .= " WHERE category_id=" . $db->tosql($item_category_id, INTEGER);
							$item_category_order = get_db_value($sql) + 1;
							$sql  = " INSERT INTO " . $table_prefix . "items_categories (item_id, category_id, item_order) VALUES (";
							$sql .= $db->tosql($new_item_id, INTEGER) . ",";
							$sql .= $db->tosql($item_category_id, INTEGER) . ",";
							$sql .= $db->tosql($item_category_order, INTEGER) . ")";
							$db->query($sql);
						}
					} else if ($record_updated) {
						$sql  = " SELECT MAX(item_order) FROM " . $table_prefix . "items_categories";
						$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
						$item_category_order = get_db_value($sql) + 1;
						
						$sql  = " INSERT INTO " . $table_prefix . "items_categories (item_id,category_id,item_order) VALUES (";
						$sql .= $db->tosql($new_item_id, INTEGER) . ",";
						$sql .= $db->tosql($category_id, INTEGER) . ",";
						$sql .= $db->tosql($item_category_order, INTEGER) . ")";
						$db->query($sql);
					}

					// duplicate product images
					if ($duplicate_images == 1 && $record_updated) {
						$item_images = array();
						$sql  = " SELECT * ";
						$sql .= " FROM " . $table_prefix . "items_images ";
						$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
						$db->query($sql);
						while ($db->next_record()) {
							$item_images[] = array(
								"is_default" => $db->f("is_default"), "image_order" => $db->f("image_order"), "image_position" => $db->f("image_position"),
								"image_title" => $db->f("image_title"), "image_tiny" => $db->f("image_tiny"), "image_tiny_alt" => $db->f("image_tiny_alt"),
								"image_small" => $db->f("image_small"), "image_small_alt" => $db->f("image_small_alt"), 
								"image_large" => $db->f("image_large"), "image_large_alt" => $db->f("image_large_alt"), 
								"image_super" => $db->f("image_super"), "image_super_alt" => $db->f("image_super_alt"), 
								"image_description" => $db->f("image_description"), 
							);
						}
						for ($i = 0; $i < sizeof($item_images); $i++) {
							$image = $item_images[$i];
							$sql  = " INSERT INTO " . $table_prefix . "items_images ";
							$sql .= " (item_id, is_default, image_order, image_position, image_title, image_tiny, image_tiny_alt, ";
							$sql .= " image_small, image_small_alt, image_large, image_large_alt, image_super, image_super_alt, image_description) VALUES (";
							$sql .= $db->tosql($new_item_id, INTEGER).",".$db->tosql($image["is_default"], INTEGER).",";
							$sql .= $db->tosql($image["image_order"], INTEGER).",".$db->tosql($image["image_position"], INTEGER).",";
							$sql .= $db->tosql($image["image_title"], TEXT).",".$db->tosql($image["image_tiny"], TEXT).",".$db->tosql($image["image_tiny_alt"], TEXT).",";
							$sql .= $db->tosql($image["image_small"], TEXT).",".$db->tosql($image["image_small_alt"], TEXT).",";
							$sql .= $db->tosql($image["image_large"], TEXT).",".$db->tosql($image["image_large_alt"], TEXT).",";
							$sql .= $db->tosql($image["image_super"], TEXT).",".$db->tosql($image["image_super_alt"], TEXT).",";
							$sql .= $db->tosql($image["image_description"], TEXT).")";
							$db->query($sql);
						}
					}


					// duplicate product accessories
					if ($duplicate_accessories == 1 && $record_updated) {
						$item_accessories = array();
						$sql  = " SELECT accessory_id, accessory_order FROM " . $table_prefix . "items_accessories ";
						$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
						$sql .= " ORDER BY accessory_order ";
						$db->query($sql);
						while ($db->next_record()) {
							$item_accessories[] = array($db->f("accessory_id"), $db->f("accessory_order"));
						}
						for ($i = 0; $i < sizeof($item_accessories); $i++) {
							$item_accessories_id = $item_accessories[$i][0];
							$item_accessories_order = $item_accessories[$i][1];
							$sql  = " INSERT INTO " . $table_prefix . "items_accessories (item_id, accessory_id, accessory_order) VALUES (";
							$sql .= $db->tosql($new_item_id, INTEGER) . "," . $db->tosql($item_accessories_id, INTEGER) . "," . $db->tosql($item_accessories_order, INTEGER) . ")";
							$db->query($sql);
						}
					}

					// duplicate all properties
					if ($duplicate_properties == 1 && $record_updated) {
						$parent_properties = array(); // array to update fields - parent_property_id, parent_value_id
						$percentage_properties = array(); // array to update field - percentage_property_id
						$updated_properties = array(); $updated_values = array(); // arrays to save old and new ids for properties and their values
						$item_properties = array();
						$sql  = " SELECT * FROM " . $table_prefix . "items_properties ";
						$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
						$sql .= " ORDER BY property_order, property_id ";
						$db->query($sql);

						if ($db->next_record()) {
							do {
								$item_properties[] = array(
									"property_id" => $db->f("property_id"), 
									"item_type_id" => $db->f("item_type_id"), 
									"parent_property_id" => $db->f("parent_property_id"), 
									"parent_value_id" => $db->f("parent_value_id"), 
									"show_for_user" => $db->f("show_for_user"), 
									"usage_type" => $db->f("usage_type"), 
									"property_order" => $db->f("property_order"),
									"property_code" => $db->f("property_code"), 
									"property_name" => $db->f("property_name"), 
									"hide_name" => $db->f("hide_name"), 
									"property_hint" => $db->f("property_hint"), 

									"property_description" => $db->f("property_description"), 
									"property_price_type" => $db->f("property_price_type"), 
									"percentage_price_type" => $db->f("percentage_price_type"), 
									"percentage_property_id" => $db->f("percentage_property_id"), 
									"free_price_type" => $db->f("free_price_type"), 
									"free_price_amount" => $db->f("free_price_amount"), 
									"max_limit_type" => $db->f("max_limit_type"), 
									"max_limit_length" => $db->f("max_limit_length"), 
									"length_units" => $db->f("length_units"),

									"control_type" => $db->f("control_type"), 
									"control_style" => $db->f("control_style"), 
									"required" => $db->f("required"),
									"use_on_list" => $db->f("use_on_list"), "use_on_details" => $db->f("use_on_details"), 
									"use_on_table" => $db->f("use_on_table"), "use_on_grid" => $db->f("use_on_grid"), 
									"use_on_checkout" => $db->f("use_on_checkout"), "use_on_second" => $db->f("use_on_second"),
									"start_html" => $db->f("start_html"), "middle_html" => $db->f("middle_html"), "end_html" => $db->f("end_html"),
									"control_code" => $db->f("control_code"), "onchange_code" => $db->f("onchange_code"), "onclick_code" => $db->f("onclick_code"),
									"property_type_id" => $db->f("property_type_id"), "sub_item_id" => $db->f("sub_item_id"), 
									"additional_price" => $db->f("additional_price"), "trade_additional_price" => $db->f("trade_additional_price"), 
									"quantity" => $db->f("quantity"), "quantity_action" => $db->f("quantity_action"),
									"property_class" => $db->f("property_class"), "property_style" => $db->f("property_style"),
									"before_control_html" => $db->f("before_control_html"), 
									"after_control_html" => $db->f("after_control_html"),
									"sites_all" => $db->f("sites_all"),
								);
							} while ($db->next_record());

							$ip = new VA_Record($table_prefix . "items_properties");
							$ip->add_textbox("property_id", INTEGER);
							$ip->add_textbox("parent_property_id", INTEGER); 
							$ip->add_textbox("parent_value_id", INTEGER); 
							$ip->add_textbox("item_id", INTEGER);
							$ip->add_textbox("item_type_id", INTEGER);
							$ip->add_textbox("show_for_user", INTEGER);
							$ip->add_textbox("usage_type", INTEGER);
							$ip->add_textbox("property_code", TEXT);
							$ip->add_textbox("property_name", TEXT);
							$ip->add_textbox("hide_name", INTEGER);
							$ip->add_textbox("property_hint", TEXT);
							$ip->add_textbox("property_description", TEXT);
							$ip->add_textbox("property_price_type", INTEGER); 
							$ip->add_textbox("percentage_price_type", INTEGER);
							$ip->add_textbox("percentage_property_id", INTEGER);
							$ip->add_textbox("free_price_type", INTEGER); 
							$ip->add_textbox("free_price_amount", FLOAT);
							$ip->add_textbox("max_limit_type", INTEGER);
							$ip->add_textbox("max_limit_length", INTEGER);
							$ip->add_textbox("length_units", TEXT);

							$ip->add_textbox("quantity", INTEGER);
							$ip->add_textbox("quantity_action", INTEGER);
							$ip->add_textbox("control_type", TEXT);
							$ip->change_property("control_type", USE_SQL_NULL, false);
							$ip->add_textbox("control_style", TEXT);
							$ip->add_textbox("required", INTEGER);
							$ip->add_textbox("use_on_list", INTEGER);
							$ip->add_textbox("use_on_details", INTEGER);
							$ip->add_textbox("use_on_table", INTEGER);
							$ip->add_textbox("use_on_grid", INTEGER);
							$ip->add_textbox("use_on_checkout", INTEGER);
							$ip->add_textbox("start_html", TEXT);
							$ip->add_textbox("middle_html", TEXT);
							$ip->add_textbox("end_html", TEXT);
							$ip->add_textbox("control_code", TEXT);
							$ip->add_textbox("onchange_code", TEXT);
							$ip->add_textbox("onclick_code", TEXT);
							$ip->add_textbox("property_type_id", INTEGER);
							$ip->add_textbox("sub_item_id", INTEGER);
							$ip->add_textbox("additional_price", FLOAT);
							$ip->add_textbox("trade_additional_price", FLOAT);
							$ip->add_textbox("use_on_second", INTEGER);
							$ip->add_textbox("property_order", INTEGER);
							$ip->add_textbox("property_class", TEXT);
							$ip->add_textbox("property_style", TEXT);
							$ip->add_textbox("before_control_html", TEXT);
							$ip->add_textbox("after_control_html", TEXT);
							$ip->add_textbox("sites_all", INTEGER);
							$ip->set_value("item_id", $new_item_id);

							$ipv = new VA_Record($table_prefix . "items_properties_values");
							if ($db_type == "postgre") {
								$ipv->add_textbox("item_property_id", INTEGER);
							} 
							$ipv->add_textbox("property_id", INTEGER);
							$ipv->add_textbox("value_order", INTEGER);
							$ipv->add_textbox("property_value", TEXT);
							$ipv->add_textbox("additional_price", NUMBER);
							$ipv->add_textbox("trade_additional_price", NUMBER);
							$ipv->add_textbox("percentage_price", NUMBER);
							$ipv->add_textbox("buying_price", NUMBER);
							$ipv->add_textbox("quantity", INTEGER);
							$ipv->add_textbox("additional_weight", NUMBER);
							$ipv->add_textbox("actual_weight", NUMBER);
							$ipv->add_textbox("hide_value", INTEGER);
							$ipv->add_textbox("is_default_value", INTEGER);
							$ipv->add_textbox("item_code", TEXT);
							$ipv->add_textbox("manufacturer_code", TEXT);
							$ipv->add_textbox("stock_level", INTEGER);
							$ipv->add_textbox("use_stock_level", INTEGER);
							$ipv->add_textbox("hide_out_of_stock", INTEGER);
							$ipv->add_textbox("download_files_ids", TEXT);
							$ipv->add_textbox("download_path", TEXT);
							$ipv->add_textbox("download_period", INTEGER);
							$ipv->add_textbox("sub_item_id", INTEGER);

							$ipv->add_textbox("image_tiny", TEXT);
							$ipv->add_textbox("image_small", TEXT);
							$ipv->add_textbox("image_large", TEXT);
							$ipv->add_textbox("image_super", TEXT);

							$ips = new VA_Record($table_prefix . "items_properties_sites");
							$ips->add_textbox("property_id", INTEGER);
							$ips->add_textbox("site_id", INTEGER);

							$ipz = new VA_Record($table_prefix . "items_properties_sizes");
							$ipz->add_textbox("property_id", INTEGER);
							$ipz->add_textbox("width", NUMBER);
							$ipz->add_textbox("height", NUMBER);
							$ipz->add_textbox("price", NUMBER);

							for ($i = 0; $i < sizeof($item_properties); $i++) {
								$property_id = $item_properties[$i]["property_id"];
								$db->query("SELECT MAX(property_id) FROM " . $table_prefix . "items_properties ");
								$db->next_record();
								$new_property_id = $db->f(0) + 1;
								$parent_property_id = $item_properties[$i]["parent_property_id"];
								$parent_value_id = $item_properties[$i]["parent_value_id"];
								$percentage_property_id = $item_properties[$i]["percentage_property_id"];

								$ip->set_value("property_id", $new_property_id);
								$ip->set_value("item_type_id", $item_properties[$i]["item_type_id"]);
								$ip->set_value("show_for_user", $item_properties[$i]["show_for_user"]);
								$ip->set_value("usage_type", $item_properties[$i]["usage_type"]);
								$ip->set_value("parent_property_id", $item_properties[$i]["parent_property_id"]);
								$ip->set_value("parent_value_id", $item_properties[$i]["parent_value_id"]);
								$ip->set_value("property_code", $item_properties[$i]["property_code"]);
								$ip->set_value("property_name", $item_properties[$i]["property_name"]);
								$ip->set_value("hide_name", $item_properties[$i]["hide_name"]);
								$ip->set_value("property_hint", $item_properties[$i]["property_hint"]);
								$ip->set_value("property_description", $item_properties[$i]["property_description"]);
								$ip->set_value("property_price_type", $item_properties[$i]["property_price_type"]);
								$ip->set_value("percentage_price_type", $item_properties[$i]["percentage_price_type"]);
								$ip->set_value("percentage_property_id", "");
								$ip->set_value("free_price_type", $item_properties[$i]["free_price_type"]);
								$ip->set_value("free_price_amount", $item_properties[$i]["free_price_amount"]);
								$ip->set_value("max_limit_type", $item_properties[$i]["max_limit_type"]);
								$ip->set_value("max_limit_length", $item_properties[$i]["max_limit_length"]);
								$ip->set_value("length_units", $item_properties[$i]["length_units"]);
								$ip->set_value("quantity_action", $item_properties[$i]["quantity_action"]);
								$ip->set_value("control_type", $item_properties[$i]["control_type"]);
								$ip->set_value("control_style", $item_properties[$i]["control_style"]);
								$ip->set_value("required", $item_properties[$i]["required"]);
								$ip->set_value("use_on_list", $item_properties[$i]["use_on_list"]);
								$ip->set_value("use_on_details", $item_properties[$i]["use_on_details"]);
								$ip->set_value("use_on_table", $item_properties[$i]["use_on_table"]);
								$ip->set_value("use_on_grid", $item_properties[$i]["use_on_grid"]);
								$ip->set_value("use_on_checkout", $item_properties[$i]["use_on_checkout"]);
								$ip->set_value("start_html", $item_properties[$i]["start_html"]);
								$ip->set_value("middle_html", $item_properties[$i]["middle_html"]);
								$ip->set_value("end_html", $item_properties[$i]["end_html"]);
								$ip->set_value("control_code", $item_properties[$i]["control_code"]);
								$ip->set_value("onchange_code", $item_properties[$i]["onchange_code"]);
								$ip->set_value("onclick_code", $item_properties[$i]["onclick_code"]);
								$ip->set_value("property_type_id", $item_properties[$i]["property_type_id"]);
								$ip->set_value("sub_item_id", $item_properties[$i]["sub_item_id"]);
								$ip->set_value("additional_price", $item_properties[$i]["additional_price"]);
								$ip->set_value("trade_additional_price", $item_properties[$i]["trade_additional_price"]);
								$ip->set_value("quantity", $item_properties[$i]["quantity"]);
								$ip->set_value("use_on_second", $item_properties[$i]["use_on_second"]);
								$ip->set_value("property_order", $item_properties[$i]["property_order"]);
								$ip->set_value("property_class", $item_properties[$i]["property_class"]);
								$ip->set_value("property_style", $item_properties[$i]["property_style"]);
								$ip->set_value("before_control_html", $item_properties[$i]["before_control_html"]);
								$ip->set_value("after_control_html", $item_properties[$i]["after_control_html"]);
								$ip->set_value("sites_all", $item_properties[$i]["sites_all"]);

								$ip->insert_record();

								// save parent fields
								if (strlen($parent_property_id)) { 
									$parent_properties[$new_property_id] = array(
										"old_property_id" => $parent_property_id, 
										"old_value_id" => $parent_value_id,
									);
								}
								// save percentage fields
								if (strlen($percentage_property_id)) { 
									$percentage_properties[$new_property_id] = array(
										"old_property_id" => $percentage_property_id, 
									);
								}
								$updated_properties[$property_id] = $new_property_id;

								// duplicate property values
								$property_values = array();
								$sql  = " SELECT * FROM " . $table_prefix . "items_properties_values ";
								$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
								$sql .= " ORDER BY property_value, item_property_id ";
								$db->query($sql);
								while ($db->next_record()) {
									$property_values[] = array(
										"item_property_id" => $db->f("item_property_id"), "value_order" => $db->f("value_order"), "property_value" => $db->f("property_value"), "additional_price" => $db->f("additional_price"),
										"trade_additional_price" => $db->f("trade_additional_price"),
										"quantity" => $db->f("quantity"), "additional_weight" => $db->f("additional_weight"),"actual_weight" => $db->f("actual_weight"),
										"hide_value" => $db->f("hide_value"), "is_default_value" => $db->f("is_default_value"),
										"item_code" => $db->f("item_code"), "manufacturer_code" => $db->f("manufacturer_code"), "buying_price" => $db->f("buying_price"),
										"stock_level" => $db->f("stock_level"), "use_stock_level" => $db->f("use_stock_level"),
										"hide_out_of_stock" => $db->f("hide_out_of_stock"), "download_path" => $db->f("download_path"),
										"download_period" => $db->f("download_period"), 
										"download_files_ids" => $db->f("download_files_ids"), 
										"sub_item_id" => $db->f("sub_item_id"),
										"percentage_price" => $db->f("percentage_price"),
										"image_tiny" => $db->f("image_tiny"),
										"image_small" => $db->f("image_small"),
										"image_large" => $db->f("image_large"),
										"image_super" => $db->f("image_super"),
									);
								}
								for ($pi = 0; $pi < sizeof($property_values); $pi++) {
									$item_property_id = $property_values[$pi]["item_property_id"];
									$ipv->set_value("property_id", $new_property_id);
									$ipv->set_value("value_order", $property_values[$pi]["value_order"]);
									$ipv->set_value("property_value", $property_values[$pi]["property_value"]);
									$ipv->set_value("additional_price", $property_values[$pi]["additional_price"]);
									$ipv->set_value("trade_additional_price", $property_values[$pi]["trade_additional_price"]);
									$ipv->set_value("quantity", $property_values[$pi]["quantity"]);
									$ipv->set_value("additional_weight", $property_values[$pi]["additional_weight"]);
									$ipv->set_value("actual_weight", $property_values[$pi]["actual_weight"]);
									$ipv->set_value("hide_value", $property_values[$pi]["hide_value"]);
									$ipv->set_value("is_default_value", $property_values[$pi]["is_default_value"]);

									$ipv->set_value("item_code", $property_values[$pi]["item_code"]);
									$ipv->set_value("manufacturer_code", $property_values[$pi]["manufacturer_code"]);
									$ipv->set_value("buying_price", $property_values[$pi]["buying_price"]);
									$ipv->set_value("stock_level", $property_values[$pi]["stock_level"]);
									$ipv->set_value("use_stock_level", $property_values[$pi]["use_stock_level"]);
									$ipv->set_value("hide_out_of_stock", $property_values[$pi]["hide_out_of_stock"]);
									$ipv->set_value("download_path", $property_values[$pi]["download_path"]);
									$ipv->set_value("download_period", $property_values[$pi]["download_period"]);
									$ipv->set_value("download_files_ids", $property_values[$pi]["download_files_ids"]);
									$ipv->set_value("sub_item_id", $property_values[$pi]["sub_item_id"]);
									$ipv->set_value("percentage_price", $property_values[$pi]["percentage_price"]);
									$ipv->set_value("image_tiny", $property_values[$pi]["image_tiny"]);
									$ipv->set_value("image_small", $property_values[$pi]["image_small"]);
									$ipv->set_value("image_large", $property_values[$pi]["image_large"]);
									$ipv->set_value("image_super", $property_values[$pi]["image_super"]);

									$ipv->insert_record();
									$new_item_property_id = $db->last_insert_id();
									$updated_values[$item_property_id] = $new_item_property_id;
								}
								// end values duplicating

								// duplicate property sites
								$property_sites = array();
								$sql  = " SELECT * FROM " . $table_prefix . "items_properties_sites ";
								$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
								$sql .= " ORDER BY site_id ";
								$db->query($sql);
								while ($db->next_record()) {
									$property_sites[] = array(
										"site_id" => $db->f("site_id"), 
									);
								}
								for ($pi = 0; $pi < sizeof($property_sites); $pi++) {
									$ips->set_value("property_id", $new_property_id);
									$ips->set_value("site_id", $property_sites[$pi]["site_id"]);

									$ips->insert_record();
								}
								// end sites duplicating

								// duplicate property sizes
								$property_sizes = array();
								$sql  = " SELECT * FROM " . $table_prefix . "items_properties_sizes ";
								$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
								$sql .= " ORDER BY height, width ";
								$db->query($sql);
								while ($db->next_record()) {
									$property_sizes[] = array(
										"width" => $db->f("width"), 
										"height" => $db->f("height"), 
										"price" => $db->f("price"), 
									);
								}
								for ($pi = 0; $pi < sizeof($property_sizes); $pi++) {
									$ipz->set_value("property_id", $new_property_id);
									$ipz->set_value("width", $property_sizes[$pi]["width"]);
									$ipz->set_value("height", $property_sizes[$pi]["height"]);
									$ipz->set_value("price", $property_sizes[$pi]["price"]);

									$ipz->insert_record();
								}
								// end sizes duplicating

							}
						}
						// update parent options
						if (sizeof($parent_properties)) {
							foreach ($parent_properties as $property_id => $parent_data) {
								$old_property_id = $parent_data["old_property_id"];
								$old_value_id = $parent_data["old_value_id"];
								if (isset($updated_properties[$old_property_id])) {
									$sql  = " UPDATE " . $table_prefix . "items_properties ";
									$sql .= " SET parent_property_id=" . $db->tosql($updated_properties[$old_property_id], INTEGER);
									if (strlen($old_value_id) && isset($updated_values[$old_value_id])) {
										$sql .= " , parent_value_id=" . $db->tosql($updated_values[$old_value_id], INTEGER);
									}
									$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
									$db->query($sql);
								}
							}
						} // end update parent
						// update percentage fields 
						if (sizeof($percentage_properties)) {
							foreach ($percentage_properties as $property_id => $parent_data) {
								$old_property_id = $parent_data["old_property_id"];
								if (isset($updated_properties[$old_property_id])) {
									$sql  = " UPDATE " . $table_prefix . "items_properties ";
									$sql .= " SET percentage_property_id=" . $db->tosql($updated_properties[$old_property_id], INTEGER);
									$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
									$db->query($sql);
								}
							}
						} // end update percentage

					}
					// end of saving properties

					// duplicate all releases
					if ($duplicate_releases == 1 && $record_updated) {
						$item_releases = array();
						$sql  = " SELECT * FROM " . $table_prefix . "releases ";
						$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
						$db->query($sql);
						if ($db->next_record()) {
							do {
								$item_releases[] = array($db->f("release_id"), $db->f("release_type_id"), $db->f("release_date", DATETIME),
									$db->f("release_title"), $db->f("version"), $db->f("release_desc"), $db->f("download_type"),
									$db->f("path_to_file"), $db->f("is_showing"), $db->f("show_on_index"));
							} while ($db->next_record());

							$ir = new VA_Record($table_prefix . "releases");
							$ir->add_textbox("release_id", INTEGER);
							$ir->add_textbox("item_id", INTEGER);
							$ir->add_textbox("release_type_id", INTEGER);
							$ir->add_textbox("release_date", DATETIME);
							$ir->add_textbox("release_title", TEXT);
							$ir->add_textbox("version", TEXT);
							$ir->add_textbox("release_desc", TEXT);
							$ir->add_textbox("download_type", INTEGER);
							$ir->add_textbox("path_to_file", TEXT);
							$ir->add_textbox("is_showing", INTEGER);
							$ir->add_textbox("show_on_index", INTEGER);
							$ir->set_value("item_id", $new_item_id);

							$irv = new VA_Record($table_prefix . "release_changes");
							$irv->add_textbox("release_id", INTEGER);
							$irv->add_textbox("change_type_id", INTEGER);
							$irv->add_textbox("change_date", DATETIME);
							$irv->add_textbox("change_desc", TEXT);
							$irv->add_textbox("is_showing", INTEGER);

							for ($i = 0; $i < sizeof($item_releases); $i++) {
								$release_id = $item_releases[$i][0];
								$db->query("SELECT MAX(release_id) FROM " . $table_prefix . "releases ");
								$db->next_record();
								$new_release_id = $db->f(0) + 1;
								$ir->set_value("release_id", $new_release_id);
								$ir->set_value("release_type_id", $item_releases[$i][1]);
								$ir->set_value("release_date", $item_releases[$i][2]);
								$ir->set_value("release_title", $item_releases[$i][3]);
								$ir->set_value("version", $item_releases[$i][4]);
								$ir->set_value("release_desc", $item_releases[$i][5]);
								$ir->set_value("download_type", $item_releases[$i][6]);
								$ir->set_value("path_to_file", $item_releases[$i][7]);
								$ir->set_value("is_showing", $item_releases[$i][8]);
								$ir->set_value("show_on_index", $item_releases[$i][9]);
								$ir->insert_record();
								// duplicate releases changes
								$release_changes = array();
								$sql  = " SELECT * FROM " . $table_prefix . "release_changes ";
								$sql .= " WHERE release_id=" . $db->tosql($release_id, INTEGER);
								$db->query($sql);
								while ($db->next_record()) {
									$release_changes[] = array($db->f("change_type_id"), $db->f("change_date", DATETIME),
										$db->f("change_desc"), $db->f("is_showing"));
								}
								for ($pi = 0; $pi < sizeof($release_changes); $pi++) {
									$irv->set_value("release_id", $new_release_id);
									$irv->set_value("change_type_id", $release_changes[$pi][0]);
									$irv->set_value("change_date", $release_changes[$pi][1]);
									$irv->set_value("change_desc", $release_changes[$pi][2]);
									$irv->set_value("is_showing", $release_changes[$pi][3]);
									$irv->insert_record();
								}
							}
						}
					}
					// end of saving releases

					// duplicate sites and user types
					if ($duplicate_sites == 1 && $record_updated) {						
						$sites = array();
						$sql  = " SELECT site_id FROM " . $table_prefix . "items_sites ";
						$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
						$db->query($sql);
						while ($db->next_record()) {
							$sites[] = $db->f("site_id");
						}
						$sql  = " DELETE FROM " . $table_prefix . "items_sites ";
						$sql .= " WHERE item_id = " . $db->tosql($new_item_id, INTEGER);
						$db->query($sql);
						foreach ($sites AS $site_id) {
							$sql  = " INSERT INTO " . $table_prefix . "items_sites ";
							$sql .= " (item_id, site_id) VALUES (";
							$sql .= $db->tosql($new_item_id, INTEGER) . ",";
							$sql .= $db->tosql($site_id, INTEGER) . ")";
							$db->query($sql);
						}
					}
					if ($duplicate_user_types == 1 && $record_updated) {						
						$subscriptions = array();
						$sql  = " SELECT subscription_id, access_level FROM " . $table_prefix . "items_subscriptions ";
						$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
						$db->query($sql);
						while ($db->next_record()) {
							$subscriptions[$db->f("subscription_id")] = $db->f("access_level");
						}
						foreach ($subscriptions AS $subscription_id => $access_level) {
							$sql  = " INSERT INTO " . $table_prefix . "items_subscriptions ";
							$sql .= " (item_id, subscription_id, access_level) VALUES (";
							$sql .= $db->tosql($new_item_id, INTEGER) . ",";
							$sql .= $db->tosql($subscription_id, INTEGER) . ",";
							$sql .= $db->tosql($access_level, INTEGER) . ")";
							$db->query($sql);
						}
						
						$user_types = array();
						$sql  = " SELECT user_type_id, access_level FROM " . $table_prefix . "items_user_types ";
						$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
						$db->query($sql);
						while ($db->next_record()) {
							$user_types[$db->f("user_type_id")] = $db->f("access_level");
						}
						foreach ($user_types AS $user_type_id => $access_level) {
							$sql  = " INSERT INTO " . $table_prefix . "items_user_types ";
							$sql .= " (item_id, user_type_id, access_level) VALUES (";
							$sql .= $db->tosql($new_item_id, INTEGER) . ",";
							$sql .= $db->tosql($user_type_id, INTEGER) . ",";
							$sql .= $db->tosql($access_level, INTEGER) . ")";
							$db->query($sql);
						}
					}


				} elseif (strlen($item_id)) {
					set_friendly_url();
					update_product_by_categories($item_id, false, false);
					$record_updated = $r->update_record();
					if ($record_updated) {
						update_properties_assigned($item_id, $properties_default_values);						
						if ($keywords_search) {
							generate_keywords($r->get_data());
						}
					}
				} else {
					set_friendly_url();
					//approve priviliges changes
					if (!$approve_products) {
						$r->set_value("is_approved", 0);
					}
					update_product_by_categories($item_id, false, false);
					$record_updated = $r->insert_record();

					if ($record_updated) {
						$item_id = $db->last_insert_id();
						$r->set_value("item_id", $item_id);
						
						if ($keywords_search) {
							generate_keywords($r->get_data());
						}

						$item_order = $r->get_value("item_order");
						
						$sql  = " SELECT MAX(item_order) FROM " . $table_prefix . "items_categories";
						$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
						$item_category_order = get_db_value($sql) + 1;
						
						if ($item_category_order > $item_order) {
							$sql  = " UPDATE " . $table_prefix . "items_categories";
							$sql .= " SET item_order=item_order+1";
							$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
							$sql .= " AND item_order>=" . $item_order;
							$db->query($sql);
							
							$item_category_order = $item_order;
						}
						
						$sql  = " INSERT INTO " . $table_prefix . "items_categories (item_id,category_id,item_order) VALUES (";
						$sql .= $db->tosql($item_id, INTEGER) . ",";
						$sql .= $db->tosql($category_id, INTEGER) . ",";
						$sql .= $db->tosql($item_category_order, INTEGER) . ")";
						$db->query($sql);						
						
						update_properties_assigned($item_id, $properties_default_values);
						// set default properties if exists
						if ($r->get_value("default_properties") > 0) {
							$return_page = "admin_properties_add.php?category_id=" . $category_id . "&item_id=" . urlencode($item_id);
							header("Location: " . $return_page);
							exit;
						}
					} else {
						$item_id = "";
						$r->set_value("item_id", "");
					}
				}

				if ($operation == "save" || $operation == "apply" || $operation == "duplicate") {
					// update/add quantity prices
					$ip_eg->set_values("item_id", $r->get_value("item_id"));
					if ($operation == "duplicate") {
						$ip_eg->set_values("quantity_price_id", "");
						$ip_eg->insert_all($number_prices);
					} else {
						$ip_eg->update_all($number_prices);
					}

					// update/add custom tabs 
					$itab_eg->set_values("item_id", $r->get_value("item_id"));
					if ($operation == "duplicate") {
						$itab_eg->set_values("tab_id", "");
						$itab_eg->insert_all($number_items_tabs);
					} else {
						$itab_eg->update_all($number_items_tabs);
					}

					// update downloadable files
					$itf_eg->set_values("item_id", $r->get_value("item_id"));
					$itf_eg->set_values("item_type_id", 0);
					if ($operation == "duplicate") {
						$itf_eg->set_values("file_id", "");
						$itf_eg->insert_all($number_files);
					} else {
						$itf_eg->update_all($number_files);
					}

					// update serial numbers
					$is_eg->set_values("item_id", $r->get_value("item_id"));
					if ($operation == "duplicate") {
						$is_eg->set_values("serial_id", "");
						$is_eg->insert_all($number_serials);
					} else {
						$is_eg->update_all($number_serials);
					}
				}


				if ($record_updated) {
					if ($operation == "apply") {
						// show success message and clear operation to get updated data from database
						$operation = "";
						$t->set_var("success_message", RECORD_UPDATED_MSG);
						$t->sparse("success_block", false);
					} else {
						header("Location: " . $return_page);
						exit;	
					}
				}
			}
		}
	} 

	// get record data for existed product
	if (strlen($item_id) && !strlen($operation)) {
		$r->get_db_values();

		$is_draft = $r->get_value("is_draft");

		if ($is_draft && $r->get_value("price") == 0) { $r->set_value("price", ""); }
		if ($r->get_value("trade_price") == 0) { $r->set_value("trade_price", ""); }
		if ($r->get_value("is_sales") == 0 && $r->get_value("sales_price") == 0) { $r->set_value("sales_price", ""); }
		if ($r->get_value("trade_sales") == 0) { $r->set_value("trade_sales", ""); }
		if ($r->get_value("properties_price") == 0) { $r->set_value("properties_price", ""); }
		if ($r->get_value("trade_properties_price") == 0) { $r->set_value("trade_properties_price", ""); }

		// convert packages number value
		$packages_number = $r->get_value("packages_number");
		if ($packages_number > 0) { 
			$packages_number = preg_replace("/^(\d+)\.0+$/", "\\1", $packages_number);
			$packages_number = preg_replace("/^(\d+\.\d*[1-9])0+$/", "\\1", $packages_number);
			$r->set_value("packages_number", $packages_number);
		}

		// check data for quantity prices
		$ip_eg->set_value("item_id", $item_id);
		$ip_eg->change_property("quantity_price_id", USE_IN_SELECT, true);
		$ip_eg->change_property("quantity_price_id", USE_IN_WHERE, false);
		$ip_eg->change_property("item_id", USE_IN_WHERE, true);
		$ip_eg->change_property("item_id", USE_IN_SELECT, true);
		$number_prices = $ip_eg->get_db_values();
		if ($number_prices == 0) {
			$number_prices = 5;
		}

		// check data for items tabs
		$itab_eg->set_value("item_id", $item_id);
		$itab_eg->change_property("tab_id", USE_IN_SELECT, true);
		$itab_eg->change_property("tab_id", USE_IN_WHERE, false);
		$itab_eg->change_property("item_id", USE_IN_WHERE, true);
		$itab_eg->change_property("item_id", USE_IN_SELECT, true);
		$number_items_tabs = $itab_eg->get_db_values();
		if ($number_items_tabs == 0) {
			$number_items_tabs = 5;
		}

		// check data for downloadable files
		$itf_eg->set_value("item_id", $item_id);
		$itf_eg->change_property("file_id", USE_IN_SELECT, true);
		$itf_eg->change_property("file_id", USE_IN_WHERE, false);
		$itf_eg->change_property("item_id", USE_IN_WHERE, true);
		$itf_eg->change_property("item_id", USE_IN_SELECT, true);
		$number_files = $itf_eg->get_db_values();
		if ($number_files == 0) {
			$number_files = 1;
		}

		// check data for serial numbers
		$is_eg->set_value("item_id", $item_id);
		$is_eg->change_property("serial_id", USE_IN_SELECT, true);
		$is_eg->change_property("serial_id", USE_IN_WHERE, false);
		$is_eg->change_property("item_id", USE_IN_WHERE, true);
		$is_eg->change_property("item_id", USE_IN_SELECT, true);
		$db->PageNumber     = 1;
		$db->RecordsPerPage = $serials_per_page;
		$number_serials = $is_eg->get_db_values();
		if ($number_serials == 0) {
			$number_serials = 5;
		}

		// add default image for new structure if there is no default image yet
		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "items_images ";
		$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
		$sql .= " AND is_default=1 ";
		$is_default_image = get_db_value($sql);
		if (!$is_default_image) {
			$tiny_image = $r->get_value("tiny_image");
			$small_image = $r->get_value("small_image");
			$big_image = $r->get_value("big_image");
			$super_image = $r->get_value("super_image");
			// check if there is any images exists
			if ($tiny_image || $small_image || $big_image || $super_image) {
				$sql  = " SELECT image_id FROM " . $table_prefix . "items_images ";
				$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
				$sql .= " AND image_tiny=" . $db->tosql($tiny_image, TEXT);
				$sql .= " AND image_small=" . $db->tosql($small_image, TEXT);
				$sql .= " AND image_large=" . $db->tosql($big_image, TEXT);
				$sql .= " AND image_super=" . $db->tosql($super_image, TEXT);
				$db->query($sql);
				if ($db->next_record()) {
					$image_id = $db->f("image_id");
					// set default option for found image
					$sql  = " UPDATE " . $table_prefix . "items_images ";
					$sql .= " SET is_default=1 ";
					$sql .= " WHERE image_id=" . $db->tosql($image_id, INTEGER);
					$db->query($sql);
				} else {
					// add new default image
					$tiny_image_alt = $r->get_value("tiny_image_alt");
					$small_image_alt = $r->get_value("small_image_alt");
					$big_image_alt = $r->get_value("big_image_alt");
					$super_image_alt = $r->get_value("super_image_alt");

					$ii = new VA_Record($table_prefix . "items_images");
					$ii->add_where("image_id", INTEGER);
					$ii->add_textbox("item_id", INTEGER);
					$ii->add_textbox("is_default", INTEGER);
					$ii->add_textbox("image_order", INTEGER);
					$ii->add_textbox("image_position", INTEGER);
					$ii->add_textbox("image_title", TEXT);
					$ii->add_textbox("image_description", TEXT);
					$ii->add_textbox("image_tiny", TEXT);
					$ii->add_textbox("image_small", TEXT);
					$ii->change_property("image_small", USE_SQL_NULL, false);
					$ii->add_textbox("image_large", TEXT);
					$ii->change_property("image_large", USE_SQL_NULL, false);
					$ii->add_textbox("image_super", TEXT);
					$ii->add_textbox("image_tiny_alt", TEXT);
					$ii->add_textbox("image_small_alt", TEXT);
					$ii->add_textbox("image_large_alt", TEXT);
					$ii->add_textbox("image_super_alt", TEXT);

					if ($super_image) {
						$filename = $super_image;
					} else if ($big_image) {
						$filename = $big_image;
					} else if ($small_image) {
						$filename = $small_image;
					} else {
						$filename = $tiny_image;
					}

					// set values
					$ii->set_value("item_id", $item_id);
					$ii->set_value("is_default", 1);
					$ii->set_value("image_order", 1);
					$ii->set_value("image_position", 2);
					$ii->set_value("image_title", basename($filename));
					$ii->set_value("image_description", "");
					$ii->set_value("image_tiny", $tiny_image);
					$ii->set_value("image_small", $small_image);
					$ii->set_value("image_large", $big_image);
					$ii->set_value("image_super", $super_image);
					$ii->set_value("image_tiny_alt", $tiny_image_alt);
					$ii->set_value("image_small_alt", $small_image_alt);
					$ii->set_value("image_large_alt", $big_image_alt);
					$ii->set_value("image_super_alt", $super_image_alt);
					$ii->insert_record();
				}
			}
		} // end if default image checks

	}

	if (!strlen($operation) && !strlen($item_id)) {
		/*
		// check for existed draft in selected category
		$sql  = " SELECT i.item_id FROM " . $table_prefix ."items i ";
		$sql .= " INNER JOIN " . $table_prefix ."items_categories ic ON i.item_id=ic.item_id ";
		$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
		$sql .= " AND is_draft=1 ";
		$db->query($sql);
		if ($db->next_record()) {
			$item_id = $db->f("item_id");
		}//*/

		if (!strlen($item_id)) {
			// new record (set default values and save draft)
			// check default product order
			$sql  = " SELECT MAX(ic.item_order) AS category_order,MAX(i.item_order) AS product_order ";
			$sql .= " FROM " . $table_prefix . "items i, " . $table_prefix . "items_categories ic ";
			$sql .= " WHERE i.item_id=ic.item_id ";
			$sql .= " AND ic.category_id=" . $db->tosql($category_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				if ($category_id) {
					$item_order = $db->f("category_order");
				} else {
					$item_order = $db->f("product_order");
				}
			} else {
				$item_order = 0;
			}
			$item_order++;
			$r->set_value("item_order", $item_order);
			//$r->set_value("use_stock_level", 1);
			//$r->set_value("hide_out_of_stock", 1);
			$r->set_value("is_showing", 1);
			$r->set_value("is_approved", 1);
			$r->set_value("shipping_modules_default", 1);
			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type='default_property'";
			$db->query($sql);
			$db->next_record();
			$default_properties = $db->f(0);
			$r->set_value("default_properties", $default_properties);
			update_product_by_categories($item_id, $category_id, true);		
			// quantity prices
			$number_prices = 5;
			// quantity items tabs
			$number_items_tabs = 5;
			// downloadable files 
			$number_files = 5;
			// serial numbers
			$number_serials = 5;
			// to save draft need to set some values as not null
			$r->change_property("item_type_id", USE_SQL_NULL, false);
			$r->change_property("item_name", USE_SQL_NULL, false);
			$r->change_property("price", USE_SQL_NULL, false);
			$r->change_property("sales_price", USE_SQL_NULL, false);
			$r->change_property("trade_price", USE_SQL_NULL, false);
			$r->change_property("trade_sales", USE_SQL_NULL, false);
	  
			$r->set_value("is_draft", 1); // set value for draft
			$r->set_value("date_added", va_time());
			$r->set_value("date_modified", va_time());
			$draft_added = $r->insert_record();
			if ($draft_added) {
				$item_id = $db->last_insert_id();
		  	$r->set_value("item_id", $item_id);
	  	
				// assigned to selected category
				$sql  = " SELECT MAX(item_order) FROM " . $table_prefix . "items_categories";
				$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
				$item_category_order = get_db_value($sql) + 1;
				
				if ($item_category_order > $item_order) {
					$sql  = " UPDATE " . $table_prefix . "items_categories";
					$sql .= " SET item_order=item_order+1";
					$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
					$sql .= " AND item_order>=" . $item_order;
					$db->query($sql);
					$item_category_order = $item_order;
				}
				
				$sql  = " INSERT INTO " . $table_prefix . "items_categories (item_id,category_id,item_order) VALUES (";
				$sql .= $db->tosql($item_id, INTEGER) . ",";
				$sql .= $db->tosql($category_id, INTEGER) . ",";
				$sql .= $db->tosql($item_category_order, INTEGER) . ")";
				$db->query($sql);						
			} else {
				$r->errors  = DATABASE_ERROR_MSG."<br/>";
				$r->errors .= "Check your Viart license.<br/>";
			}

		}

		if ($item_id) {
			// redirect user to new draft 
			$request_uri = get_request_uri();
			$request_uri .= (strpos($request_uri, "?")) ? "&" : "?";
			$request_uri .= "item_id=".urlencode($item_id);

			header("Location: " .$request_uri);
			exit;
		}
	}

	// round price for two digits
	if (!$r->is_empty("price")) {
		$r->set_value("price", round($r->get_value("price"), 2));
	}
	if (!$r->is_empty("sales_price")) {
		$r->set_value("sales_price", round($r->get_value("sales_price"), 2));
	}
	if (!$r->is_empty("trade_price")) {
		$r->set_value("trade_price", round($r->get_value("trade_price"), 2));
	}
	if (!$r->is_empty("trade_sales")) {
		$r->set_value("trade_sales", round($r->get_value("trade_sales"), 2));
	}
	if (!$r->is_empty("buying_price")) {
		$r->set_value("buying_price", round($r->get_value("buying_price"), 2));
	}
	if (!$r->is_empty("properties_price")) {
		$r->set_value("properties_price", round($r->get_value("properties_price"), 2));
	}
	if (!$r->is_empty("trade_properties_price")) {
		$r->set_value("trade_properties_price", round($r->get_value("trade_properties_price"), 2));
	}
	$r->set_form_parameters();
	// check if merchant information available
	$merchant_id = $r->get_value("user_id");
	if ($merchant_id) {
		$sql  = " SELECT user_id, login, email, name, first_name, last_name, nickname, company_name ";
		$sql .= " FROM " . $table_prefix . "users u ";
		$sql .= " WHERE user_id=" . $db->tosql($merchant_id, INTEGER);
		$db->query($sql);
		if ($db->next_record())
		{
			$user_name = $db->f("name");
			if (!strlen($user_name)) { $user_name = trim($db->f("first_name") . " " . $db->f("last_name")); }
			if (!strlen($user_name)) { $user_name = trim($db->f("nickname")); }
			if (!strlen($user_name)) { $user_name = $db->f("company_name"); }
			$t->set_var("user_id", $merchant_id);
			$t->set_var("user_name", $user_name);
			$t->parse("selected_user", false);
		}
	}

	// set downloads errors
	if ($downloads_errors) {
		$t->set_var("errors_list", $downloads_errors);
		$t->parse("downloads_errors", false);
	}

	// set recurring errors
	if ($recurring_errors) {
		$t->set_var("errors_list", $recurring_errors);
		$t->parse("recurring_errors", false);
	}

	// set quantity prices
	$t->set_var("number_prices", $number_prices);
	$ip_eg->set_parameters_all($number_prices);

	// set quantity prices
	$t->set_var("number_items_tabs", $number_items_tabs);
	$itab_eg->set_parameters_all($number_items_tabs);

	// set downloadable files
	$t->set_var("number_files", $number_files);
	$itf_eg->set_parameters_all($number_files);

	// set serial numbers
	$t->set_var("serials_page", $serials_page);
	$t->set_var("number_serials", $number_serials);
	$is_eg->set_parameters_all($number_serials);

	$sites_table->message = USE_ITEM_ALL_SITES_MSG;
	$sites_table->parse("sites_table", $r->get_value("sites_all"));

	$has_any_subscriptions = $access_table->parse("subscriptions_table", $r->get_value("access_level"), $r->get_value("guest_access_level"), $r->get_value("admin_access_level"));
	
	// assign properties
	$selected_item_type_id = $r->get_value("item_type_id");
	$t->set_var("selected_item_type_id", $selected_item_type_id);
	
	$availiable_properties = array();
	for ($it = 1; $it < count($item_types); $it++) {
		if ($item_types[$it]) {
			$sql  = " SELECT property_id, property_name, property_type_id, property_description, control_type, usage_type ";
			$sql .= " FROM " . $table_prefix . "items_properties ";
			$sql .= " WHERE (usage_type=2 OR usage_type=3) AND item_type_id=" . $db->tosql($item_types[$it][0], INTEGER);			
			$sql .= " ORDER BY property_order";
			$db->query($sql);
			
			while ($db->next_record()) {
				$availiable_properties[$it][] = array (
					"property_id" => $db->f("property_id"),
					"property_name" => get_translation($db->f("property_name")),
					"property_type_id" => $db->f("property_type_id"),
					"property_description" => $db->f("property_description"),
					"control_type" => $db->f("control_type"),					
					"usage_type" => $db->f("usage_type")					
				);			
			}
		}
	}

	// get shipping methods 
	$shipping_modules_ids = $r->get_value("shipping_modules_ids");
	if ($shipping_modules_ids) {
		$sql  = " SELECT sm.shipping_module_id, sm.shipping_module_name ";
		$sql .= " FROM " . $table_prefix . "shipping_modules sm ";
		$sql .= " WHERE sm.shipping_module_id IN (" . $db->tosql($shipping_modules_ids, INTEGERS_LIST) . ") ";
		$sql .= " ORDER BY sm.shipping_module_id";
		$db->query($sql);
		while($db->next_record())
		{
			$row_module_id = $db->f("shipping_module_id");
			$module_name = $db->f("shipping_module_name");
	
			$t->set_var("module_id", $row_module_id);
			$t->set_var("module_name", $module_name);
			$t->set_var("module_name_js", str_replace("\"", "&quot;", $module_name));
	
			$t->parse("selected_shipping_modules", true);
			$t->parse("selected_shipping_modules_js", true);
		}
	}
	
	$open_large_image = get_setting_value($settings, "open_large_image", 0);
	$open_large_image_function = ($open_large_image) ? "popupImage(this); return false;" : "openImage(this); return false;";

	for ($it = 1, $itc = count($item_types); $it<$itc; $it++) {		
		if (isset($availiable_properties[$it]) && count ($availiable_properties[$it])) {
			$div_item_type_id = $item_types[$it][0];
			$t->set_var("item_type_property", "");
			$t->set_var("item_type_property_default_input", "");
			$t->set_var("item_type_property_value_input", "");
			$t->set_var("item_type_property_value_header", "");
			$t->set_var("item_type_property_value_assigned_value", "");
			$t->set_var("item_type_property_value_assigned_header ", "");
				
			$t->set_var("div_item_type_id", $div_item_type_id);

			for ($itp = 0, $itpc = count ($availiable_properties[$it]); $itp < $itpc; $itp++) {
				$property_id      = $availiable_properties[$it][$itp]["property_id"];
				$property_name    = $availiable_properties[$it][$itp]["property_name"];
				$property_type_id = $availiable_properties[$it][$itp]["property_type_id"];
				$property_description = $availiable_properties[$it][$itp]["property_description"];				
				$control_type     = $availiable_properties[$it][$itp]["control_type"];
				$usage_type       = $availiable_properties[$it][$itp]["usage_type"];
				
				$is_property_selected = in_array($property_id, $properties_assigned);
				
				$item_type_property_value   = "";
				$item_type_property_default = "";
				$item_type_property_values_count = 0;
				$item_type_property_default_values = "";
				$item_type_property_default_type   = "";
				if ($property_type_id == 3) {
					// subcomponents select
					$sql  = " SELECT ipv.item_property_id, ipv.property_value, i.item_name, i.big_image, i.small_image, ipv.sub_item_id, i.is_showing, ";
					$sql .= " ipv.hide_value, ipv.use_stock_level, ipv.stock_level, ipv.hide_out_of_stock, ipv.is_default_value ";
					$sql .= " FROM (" . $table_prefix. "items_properties_values ipv ";
					$sql .= " INNER JOIN " . $table_prefix . "items i ON i.item_id=ipv.sub_item_id) ";
					$sql .= " WHERE ipv.property_id=" . $db->tosql($property_id, INTEGER, true, false);
					$sql .= " ORDER BY ipv.value_order";
					$db->query($sql);
					
					$default_selected_image = "";
					$hidden_images = "";
						
					while($db->next_record()) {
						$item_type_property_values_count ++;
						
						$item_type_property_value_assigned = "";
						$item_type_property_value_default  = "";
						$item_type_property_value          = "";
											
						$sub_item_id      = $db->f("sub_item_id");
						$item_property_id = $db->f("item_property_id");
						$property_value   = strip_tags(get_translation($db->f("property_value")));
						$item_name        = strip_tags(get_translation($db->f("item_name")));
						$image            = $db->f("big_image");
						$is_showing       = $db->f("is_showing");
						$hide_value       = $db->f("hide_value");
						$use_stock_level  = $db->f("use_stock_level");
						$stock_level      = $db->f("stock_level");
						$hide_out_of_stock = $db->f("hide_out_of_stock");
						$is_default_value = $db->f("is_default_value");
						
						$property_text    = strlen($property_value) > 60 ? substr($property_value, 0, 60) . "... (ID: $sub_item_id)" : $property_value;
						if (!$is_showing || $hide_value || ($use_stock_level && $hide_out_of_stock && ($stock_level<0))) {
							$property_text = "<span class='gray'>" . $property_text . "</span>";
						}
						if (!$image) {
							$image        = $db->f("small_image");
						}						
						if ($usage_type == 2) {							
							$item_type_property_value_assigned = "<input type='checkbox'  ";
							$item_type_property_value_assigned .= " value='$item_property_id' name='properties_assigned_values_" . $div_item_type_id . "' ";
							if (in_array($item_property_id, $properties_assigned_values)) {
								$item_type_property_value_assigned .= " checked ";
							}
							$item_type_property_value_assigned .= " onClick='changeItemAssigned(this, $div_item_type_id, $property_id, $item_property_id)'> ";
						}
						
						// default values
						$default_selected = false;
						if ($control_type == "CHECKBOXLIST" || ($usage_type == 2)) {
							$type = ($control_type == "CHECKBOXLIST") ? "checkbox" : "radio";
							$item_type_property_value_default = "<input type='$type' value='$item_property_id' name='properties_default_values_" . $div_item_type_id . "_" . $property_id ."' ";
							if (in_array($item_property_id, $properties_default_values)) {
								$item_type_property_value_default .= " checked ";
							}
							$item_type_property_value_default .= " onClick='changeItemDefault(this, $div_item_type_id, $item_property_id)'> ";
						} elseif ($control_type == "LISTBOX" || $control_type == "RADIOBUTTON") {							
							$item_type_property_default .= "<option value='$item_property_id' ";					
							if (in_array($item_property_id, $properties_default_values)) {
								$item_type_property_default .= " selected ";
								$default_selected = true;
							}										
							$item_type_property_default .= "> ". $property_text . "</option>" . $eol;						
						}
						if ($is_default_value) {
							if (strlen($item_type_property_default_values)) $item_type_property_default_values .= ", ";
							$item_type_property_default_values .= $item_property_id;		
						}	
						$item_type_property_function = "return false;";
						$item_type_property_value_image = "";
						if ($image) {
							$item_type_property_function = $open_large_image_function;
							if (!preg_match("/^([a-zA-Z]*):\/\/(.*)/i", $image)) {
								$image = $root_folder_path . $image;								
								if (!$open_large_image) {
									$image_size = @getimagesize($image);
									if (is_array($image_size)) {
										$item_type_property_function =  "openImage(this, " . $image_size[0]  . ", " . $image_size[1]  . "); return false;";
									}
								}
							}
							$item_type_property_value_image  = "<a href='" . $image .  "' ";
							$item_type_property_value_image .= " title=\"" . htmlspecialchars($property_value) . "\" id='option_image_action_" ;
							$item_type_property_value_image .= $item_property_id . "' onClick='$item_type_property_function' >";
							$item_type_property_value_image .= "<img src='../images/icons/view_page.gif' alt='View' border='0'></a><br/>";
							if ($default_selected) {
								$default_selected_image  = "<a href='" . $image .  "' ";
								$default_selected_image .= " title=\"" . htmlspecialchars($property_value) . "\" id='option_image_action_" ;
								$default_selected_image .= $property_id . "' onClick='$item_type_property_function' >";
								$default_selected_image .= "<img src='../images/icons/view_page.gif' alt='View' border='0'></a><br/>";
							}
							if ($control_type == "LISTBOX" || $control_type == "RADIOBUTTON") {
								$hidden_images .= "<input type='hidden' id='option_image_$item_property_id' value='$image' title=\"" . htmlspecialchars($property_value) ."\" onClick='$item_type_property_function'>";
							}
						}	
						if ($control_type == "CHECKBOXLIST" || $usage_type == 2) {
							$t->set_var("item_type_property_value_assigned", $item_type_property_value_assigned);
							if ($item_type_property_value_assigned) {
								$t->parse("item_type_property_value_assigned_value");
							}
							$t->set_var("item_type_property_value_default", $item_type_property_value_default);												
							$t->set_var("item_type_property_value", $property_text . $item_type_property_value_image);	
							$t->parse("item_type_property_value_input", true);
							$t->set_var("item_type_property_value_assigned_value", "");							
						}							
					}
						
					if (($control_type == "LISTBOX" || $control_type == "RADIOBUTTON") && $item_type_property_default) {
						$item_type_property_default = "<select class='select' name='properties_default_values_" . $div_item_type_id . "_" . $property_id ."' " 
												. "onChange='changeItemDefault(this, $div_item_type_id, this.value); changeItemSubComponent(this, " . $property_id . ", this.value)'>"
												. "<option value='0'>No Default Value</option>"
												. $item_type_property_default . "</select>";
						if ($default_selected_image) {
							$item_type_property_default .= $default_selected_image;
						} else {
							$item_type_property_default .= "<a id='option_image_action_$property_id' style='visibility:hidden'>";
							$item_type_property_default .= "<img src='../images/icons/view_page.gif' alt='View' border='0'></a><br/>";
						}
						$item_type_property_default .= $hidden_images ;
						$item_type_property_default_type = "select";
					}
					
					
				} elseif ($control_type == "CHECKBOXLIST" || $control_type == "LISTBOX" || $control_type == "RADIOBUTTON") {
					$sql  = " SELECT item_property_id, property_value, hide_value, use_stock_level, stock_level, hide_out_of_stock, is_default_value ";
					$sql .= " FROM " . $table_prefix. "items_properties_values";
					$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER, true, false);
					$sql .= " ORDER BY value_order";
					$db->query($sql);
					while ($db->next_record()) {
						$item_type_property_values_count ++;
						
						$item_type_property_value_assigned = "";
						$item_type_property_value_default  = "";
						$item_type_property_value          = "";
						$item_property_id = $db->f("item_property_id");
						$property_value   = strip_tags(get_translation($db->f("property_value")));
						$hide_value       = $db->f("hide_value");
						$use_stock_level  = $db->f("use_stock_level");
						$stock_level      = $db->f("stock_level");
						$hide_out_of_stock = $db->f("hide_out_of_stock");
						$is_default_value = $db->f("is_default_value");

						if ($hide_value || ($use_stock_level && $hide_out_of_stock && ($stock_level<0))) {
							$property_value = "<span class='gray'>" . $property_value . "</span>";
						}
						if ($usage_type == 2) {
							$item_type_property_value_assigned = "<input type='checkbox' value='$item_property_id' name='properties_assigned_values_" . $div_item_type_id . "' ";
							if (in_array($item_property_id, $properties_assigned_values)) {
								$item_type_property_value_assigned .= " checked ";
							}							
							$item_type_property_value_assigned .= " onClick='changeItemAssigned(this, $div_item_type_id, $property_id, $item_property_id)'> ";
						}
						// default values						
						if ($control_type == "CHECKBOXLIST" || $usage_type == 2 ) {
							$type = ($control_type == "CHECKBOXLIST") ? "checkbox" : "radio"; 
							$item_type_property_value_default = "<input type='$type' value='$item_property_id' name='properties_default_values_" . $div_item_type_id . "_" . $property_id ."' ";
							if (in_array($item_property_id, $properties_default_values)) {
								$item_type_property_value_default .= " checked ";
							}							
							$item_type_property_value_default .= " onClick='changeItemDefault(this, $div_item_type_id, $item_property_id)'> ";	
						} elseif ($control_type == "LISTBOX" || $control_type == "RADIOBUTTON") {
							$item_type_property_default .= "<option value='$item_property_id' ";
							if (in_array($item_property_id, $properties_default_values)) {
								$item_type_property_default .= " selected ";
							}
							$item_type_property_default .= "> ". $property_value . "</option>" . $eol;
						}
						if ($is_default_value) {
							if (strlen($item_type_property_default_values)) $item_type_property_default_values .= ", ";
							$item_type_property_default_values .= $item_property_id;		
						}
						if ($control_type == "CHECKBOXLIST" || $usage_type == 2) {							
							$t->set_var("item_type_property_value_assigned", $item_type_property_value_assigned);
							if ($item_type_property_value_assigned) {
								$t->parse("item_type_property_value_assigned_value");								
							}										
							$t->set_var("item_type_property_value_default", $item_type_property_value_default);
							$t->set_var("item_type_property_value", $property_value);
							$t->parse("item_type_property_value_input", true);
							$t->set_var("item_type_property_value_assigned_value", "");
						}
						
					}
					if (($control_type == "LISTBOX" || $control_type == "RADIOBUTTON") && $item_type_property_default) {
						$item_type_property_default = "<select class='select' name='properties_default_values_" . $div_item_type_id . "_" . $property_id ."' "
												. " onChange='changeItemDefault(this, $div_item_type_id, this.value);'>"
												. "<option value='0'>No Default Value</option>"
												. $item_type_property_default . "</select>";
						$item_type_property_default_type = "select";
					}						
				} elseif ($control_type == "LABEL") {
					if (isset($properties_descriptions[$property_id])) {
						$default_text = $properties_descriptions[$property_id];
					} elseif (!$is_property_selected) {
						$default_text = $property_description;
					} else {
						$default_text = "";
					}
					$item_type_property_default = "<input type='text' class='field' name='properties_descriptions_$property_id' value='". $default_text . "'>";			
				}

				if ($item_type_property_values_count >= 8) {
					$property_class = "_overflowed";
				} else {
					$property_class = "";
				}
				$t->set_var("property_class", $property_class);

				if ($t->get_var("item_type_property_value_input")) {
					$t->set_var("item_type_property_default", $item_type_property_default);	
					if ($item_type_property_value_assigned)
						$t->parse("item_type_property_value_assigned_header");
					$t->parse("item_type_property_value_header");
					$t->set_var("item_type_property_value_assigned_header", "");
				} elseif ($item_type_property_default) {
					if ($control_type == "LABEL") {
						$t->set_var("property_default_title", VALUE_MSG);	
					} else {
						$t->set_var("property_default_title", DEFAULT_MSG);	
					}
					$t->set_var("item_type_property_default", $item_type_property_default);	
					$t->parse("item_type_property_default_input", true);
				}				
							
				$t->set_var("property_id", $property_id);
				$t->set_var("property_name", $property_name);
				if ($item_type_property_default_values) {
					$t->set_var("item_type_property_default_values", "new Array(" . $item_type_property_default_values . ")");
					$t->set_var("item_type_property_default_type", $item_type_property_default_type);				
				} else {
					$t->set_var("item_type_property_default_values", "false");
				}
				if ($is_property_selected) {
					$t->set_var("is_property_selected", "checked");
				} else {
					$t->set_var("is_property_selected", "");
				}
				$t->parse("item_type_property", true);				
				$t->set_var("item_type_property_default_input", "");
				$t->set_var("item_type_property_value_input", "");
				$t->set_var("item_type_property_value_header", "");
			}			
			$t->parse("item_type_properties", true);
		}
	}

	// images
	$block = "all";
	include("./admin_block_images.php");

	//parse html wysiwyg
	$editors_list = 'sd,f,fd,spO,ed_notes';
	add_html_editors($editors_list, $html_editor);
	
	// set styles for tabs
	$tabs = array(
		"general" => array("title" => PROD_GENERAL_TAB), 
		"options" => array("title" => OPTIONS_MSG), 
		"desc" => array("title" => DESCRIPTION_MSG), 
		"quantity_prices" => array("title" => QUANTITY_PRICES_MSG), 
		"items_tabs" => array("title" => CUSTOM_TABS_MSG), 
		"images" => array("title" => IMAGES_MSG), 
		"appear" => array("title" => APPEARANCE_MSG), 
		"stock" => array("title" => STOCK_AND_SHIPPING_MSG), 
		"downloads" => array("title" => ADMIN_DOWNLOADABLE_MSG), 
		"special_offer" => array("title" => PROD_SPECIAL_OFFER_TAB),
		"recurring" => array("title" => PROD_RECURRING_TAB), 
		"fees" => array("title" => REWARDS_MSG." & ".FEES_MSG), 
		"notifications" => array("title" => PROD_NOTIFICATION_TAB), 
		"other" => array("title" => OTHER_MSG),
		"sites" => array("title" => ADMIN_SITES_MSG, "show" => $sitelist),
		"subscriptions" => array( "title" => ACCESS_LEVELS_MSG, "show" => $has_any_subscriptions),
		// "icecat" => array( "title" => "ICECat", "show" => true), // TODO: icecat
	);
	parse_tabs($tabs, $tab, "tab-product");

	if (strlen($item_id))
	{
		$duplicate_properties    = ($duplicate_properties == 1) ? " checked " : "";
		$duplicate_specification = ($duplicate_specification == 1) ? " checked " : "";
		$duplicate_related       = ($duplicate_related == 1) ? " checked " : "";
		$duplicate_categories    = ($duplicate_categories == 1) ? " checked " : "";
		$duplicate_images        = ($duplicate_images == 1) ? " checked " : "";
		$duplicate_accessories   = ($duplicate_accessories == 1) ? " checked " : "";
		$duplicate_releases      = ($duplicate_releases == 1) ? " checked " : "";
		$duplicate_user_types    = ($duplicate_user_types == 1) ? " checked " : "";
		if ($sitelist) {
			$duplicate_sites     = ($duplicate_sites == 1) ? " checked " : "";
		} else {
			$duplicate_sites     = " disabled readonly ";
		}
		
		$t->set_var("duplicate_properties",    $duplicate_properties);
		$t->set_var("duplicate_specification", $duplicate_specification);
		$t->set_var("duplicate_related",       $duplicate_related);
		$t->set_var("duplicate_categories",    $duplicate_categories);
		$t->set_var("duplicate_images",        $duplicate_images);
		$t->set_var("duplicate_accessories",   $duplicate_accessories);
		$t->set_var("duplicate_releases",      $duplicate_releases);
		$t->set_var("duplicate_user_types",    $duplicate_user_types);
		$t->set_var("duplicate_sites",         $duplicate_sites);

		if ($update_products) {
			$t->set_var("save_button", SAVE_BUTTON);
			$t->set_var("apply_button", APPLY_BUTTON);
			$t->parse("save", false);
		}
		if ($remove_products) {
			$t->parse("delete", false);
		}
		if ($duplicate_products) {
			$t->parse("duplicate", false);
		}
	}
	else
	{
		if ($add_products) {
			$t->set_var("save_button", ADD_BUTTON);
			$t->set_var("apply_button", APPLY_BUTTON);
			$t->parse("save", false);
		}
		$t->set_var("delete", "");
		$t->set_var("duplicate", "");
	}
	
	$t->pparse("main");
	
	function update_properties_assigned($item_id, $properties_default_values) 
	{
		global $db, $table_prefix, $properties_assigned, $properties_descriptions,  $properties_assigned_values;
		
		$sql  = " DELETE FROM " . $table_prefix. "items_properties_assigned WHERE item_id=" . $db->tosql($item_id, INTEGER);
		$db->query($sql);
		$sql  = " DELETE FROM " . $table_prefix. "items_values_assigned WHERE item_id=" . $db->tosql($item_id, INTEGER);
		$db->query($sql);

		for ($ip=0, $ipc = count($properties_assigned); $ip<$ipc; $ip++ ) 
		{
			if (isset($properties_descriptions[$properties_assigned[$ip]])) {
				$sql  = " INSERT INTO " . $table_prefix. "items_properties_assigned (item_id, property_id, property_description) VALUES ";
				$sql .= " (" . $db->tosql($item_id, INTEGER, true, false) ." , " . $db->tosql($properties_assigned[$ip], INTEGER, true, false) . ",";	
				$sql .= $db->tosql($properties_descriptions[$properties_assigned[$ip]], TEXT) . ")";
			} else {
				$sql  = " INSERT INTO " . $table_prefix. "items_properties_assigned (item_id, property_id) VALUES ";
				$sql .= " (" . $db->tosql($item_id, INTEGER, true, false) ." , " . $db->tosql($properties_assigned[$ip], INTEGER, true, false) . ")";
			}
			$db->query($sql);
		}

		for ($ipv=0, $ipvc = count($properties_assigned_values); $ipv<$ipvc; $ipv++ ) {
			$sql  = " INSERT INTO " . $table_prefix. "items_values_assigned (item_id, property_value_id, is_default_value) VALUES ";
			$sql .= " (" . $db->tosql($item_id, INTEGER, true, false) ." , " . $db->tosql($properties_assigned_values[$ipv], INTEGER, true, false) . ",";
			$key_search = array_search($properties_assigned_values[$ipv], $properties_default_values);
			if ($key_search !== false) {
				$sql .= $db->tosql(1, INTEGER) . ")";
				unset($properties_default_values[$key_search]);	
			} else {
				$sql .=  $db->tosql(0, INTEGER) . ")";
			}
			$db->query($sql);
		}
		
		if (isset($properties_default_values) && count($properties_default_values)) {			
			$sql  = " SELECT ipv.item_property_id FROM " . $table_prefix . "items_properties_values ipv ";
			$sql .= " INNER JOIN " . $table_prefix . "items_properties ip ON ip.property_id = ipv.property_id ";
			$sql .= " WHERE ip.usage_type=3 ";
			$sql .= " AND ipv.item_property_id IN (" . $db->tosql(array_values($properties_default_values), INTEGERS_LIST) . ") ";			
			$db->query($sql);						
			$ipvs = array();
			while ($db->next_record()) {
				$ipvs[] = $db->f("item_property_id");
			}			
			foreach ($ipvs As $ipv) {
				$sql  = " INSERT INTO " . $table_prefix. "items_values_assigned (item_id, property_value_id, is_default_value) VALUES ";
				$sql .= " (" . $db->tosql($item_id, INTEGER, true, false) ." , " . $db->tosql($ipv, INTEGER, true, false) . ", 1)";
				$db->query($sql);
			}
		}
	}

	// function for quantity prices

	function check_item_quantity()
	{
		global $ip_eg;
		$user_type_id = $ip_eg->record->get_value("user_type_id");
		$min_quantity = $ip_eg->record->get_value("ip_min_quantity");
		$max_quantity = $ip_eg->record->get_value("ip_max_quantity");
		if (preg_match("/^[\+\s]+$/", $max_quantity)) {
			$ip_eg->set_value("ip_max_quantity", MAX_INTEGER);
		}
		if ($min_quantity) {
			$ip_eg->record->change_property("ip_max_quantity", MIN_VALUE, $min_quantity);
		} else {
			$ip_eg->record->change_property("ip_max_quantity", MIN_VALUE, 1);
		}
	}

	function check_max_quantity()
	{
		global $ip_eg;
		$max_quantity = $ip_eg->get_value("ip_max_quantity");
		if ($max_quantity == MAX_INTEGER) {
			$ip_eg->set_value("ip_max_quantity", "+");
		}
	}

	function check_site_id()
	{
		global $ip_eg, $sitelist;
		if ($ip_eg->record->is_empty("site_id")) {
			$ip_eg->record->set_value("site_id", 0);
		}
		if ($ip_eg->record->is_empty("user_type_id")) {
			$ip_eg->record->set_value("user_type_id", 0);
		}
	}
	
	function update_product_by_categories($item_id, $category_id = false, $only_display = false) {
		global $table_prefix, $db, $r, $access_table, $sites_table;
		$save_subscriptions_by_category = get_param("save_subscriptions_by_category");
		$save_sites_by_category         = get_param("save_sites_by_category");
		$is_in_top = false;
		
		if ($only_display || $save_subscriptions_by_category || $save_sites_by_category) {
			if ($only_display && $category_id == 0) {
				$is_in_top = true;
			} elseif ($category_id) {
				$categories_ids[] = $category_id;
			} else {
				$sql  = " SELECT category_id FROM " . $table_prefix . "items_categories ";
				$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
				$categories_ids = array();
				$db->query($sql);
				while($db->next_record()) {
					$category_id      = $db->f(0);
					$categories_ids[] = $category_id;
					if ($category_id === 0) {
						$is_in_top = true;
					}
				}
			}
						
			if ($is_in_top) {
				if ($only_display || $save_subscriptions_by_category) {
					$r->set_value("access_level", VIEW_ITEMS_PERM + SEARCH_ITEMS_PERM + VIEW_CATEGORIES_ITEMS_PERM);
					$r->set_value("guest_access_level", VIEW_ITEMS_PERM + SEARCH_ITEMS_PERM + VIEW_CATEGORIES_ITEMS_PERM);
					$r->set_value("admin_access_level", VIEW_ITEMS_PERM + SEARCH_ITEMS_PERM + VIEW_CATEGORIES_ITEMS_PERM);
				}
				if ($only_display || $save_sites_by_category) {
					$r->set_value("sites_all", 1);
				}
			} elseif ($categories_ids) {
				if ($only_display || $save_subscriptions_by_category) {
					$access_level       = 0;
					$guest_access_level = 0;					
					$admin_access_level = 0;					
					
					foreach ($access_table->access_levels_keys AS $value) {
						$sql  = " SELECT category_id FROM " . $table_prefix . "categories ";
						$sql .= " WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")";
						$sql .= " AND " . format_binary_for_sql("access_level", $value);
						$db->query($sql);
						if ($db->next_record()) {
							$access_level += $value;
						}
						$sql  = " SELECT category_id FROM " . $table_prefix . "categories ";
						$sql .= " WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")";
						$sql .= " AND " . format_binary_for_sql("guest_access_level", $value);
						$db->query($sql);
						if ($db->next_record()) {
							$guest_access_level += $value;
						}						
						$sql  = " SELECT category_id FROM " . $table_prefix . "categories ";
						$sql .= " WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")";
						$sql .= " AND " . format_binary_for_sql("admin_access_level", $value);
						$db->query($sql);
						if ($db->next_record()) {
							$admin_access_level += $value;
						}						
					}
					$r->set_value("access_level", $access_level);
					$r->set_value("guest_access_level", $guest_access_level);
					$r->set_value("admin_access_level", $admin_access_level);
					
					$access_table->selected_user_access_levels = array();
					$sql  = " SELECT user_type_id, access_level FROM " . $table_prefix . "categories_user_types";
					$sql .= " WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")";
					$db->query($sql);
					while ($db->next_record()) {
						$user_type = $db->f("user_type_id");
						$value     = $db->f("access_level");
						if (! ($access_level&$value)) {
							if (isset($access_table->selected_user_access_levels[$user_type])) {
								if (!($access_table->selected_user_access_levels[$user_type]&$value))
									$access_table->selected_user_access_levels[$user_type] += $value;
							} else {
								$access_table->selected_user_access_levels[$user_type] = $value;
							}
						}
					}
					
					$access_table->selected_access_levels = array();
					$sql  = " SELECT subscription_id, access_level FROM " . $table_prefix . "categories_subscriptions";
					$sql .= " WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")";
					$db->query($sql);
					while ($db->next_record()) {
						$subscription_id = $db->f("subscription_id");
						$value     = $db->f("access_level");
						if (! ($access_level&$value)) {
							if (isset($access_table->selected_access_levels[$subscription_id])) {
								if (!($access_table->selected_access_levels[$subscription_id]&$value))
									$access_table->selected_access_levels[$subscription_id] += $value;
							} else {
								$access_table->selected_access_levels[$subscription_id] = $value;
							}
						}
					}
				}
				
				if($only_display || $save_sites_by_category) {
					$sites_all = 0;
					$sql  = " SELECT category_id FROM " . $table_prefix . "categories ";
					$sql .= " WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")";
					$sql .= " AND sites_all = 1";
					$db->query($sql);
					if ($db->next_record()) {
						$sites_all = 1;
					}
					$r->set_value("sites_all", $sites_all);
					$sites_table->selected_sites = array();
					$sql  = " SELECT site_id FROM " . $table_prefix . "categories_sites";
					$sql .= " WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")";
					$db->query($sql);
					while ($db->next_record()) {
						$site_id = $db->f("site_id");
						$sites_table->selected_sites[] = $site_id;
					}
				}
			}
		}
		if (!$only_display) {
			$access_table->save_values($item_id);
			$sites_table->save_values($item_id);
		}
	}

	function items_files_properties()
	{
		global $itf;

		if (!$itf->is_empty("download_type") || !$itf->is_empty("download_title")
			|| !$itf->is_empty("download_path") || !$itf->is_empty("download_period")
			|| !$itf->is_empty("download_interval") || !$itf->is_empty("download_limit")) {
			$itf->change_property("download_type", REQUIRED, true);
			$itf->change_property("download_title", REQUIRED, true);
			$itf->change_property("download_path", REQUIRED, true);
		} else {
			$itf->change_property("download_type", REQUIRED, false);
			$itf->change_property("download_title", REQUIRED, false);
			$itf->change_property("download_path", REQUIRED, false);
		}

		if (!$itf->is_empty("preview_type") || !$itf->is_empty("preview_position")
			|| !$itf->is_empty("preview_title") || !$itf->is_empty("preview_path")
			|| !$itf->is_empty("preview_image")) {
			$itf->change_property("preview_type", REQUIRED, true);
			$itf->change_property("preview_title", REQUIRED, true);
			$itf->change_property("preview_path", REQUIRED, true);
		} else {
			$itf->change_property("preview_type", REQUIRED, false);
			$itf->change_property("preview_title", REQUIRED, false);
			$itf->change_property("preview_path", REQUIRED, false);
		}
	}


?>