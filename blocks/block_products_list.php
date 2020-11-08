<?php

	include_once("./includes/sorter.php");
	include_once("./includes/navigator.php");
	include_once("./includes/items_properties.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/table_view_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/filter_functions.php");
	include_once("./includes/previews_functions.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");
	include_once("./messages/" . $language_code . "/download_messages.php");

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

	// set necessary scripts
	set_script_tag("js/shopping.js");
	set_script_tag("js/ajax.js");
	set_script_tag("js/blocks.js");
	set_script_tag("js/images.js");

	$default_title = "{current_category_name}";

	// global array to use in different blocks
	if(!isset($va_data)) { $va_data = array(); }
	if(!isset($va_data["products_index"])) { $va_data["products_index"] = 0; }
	$start_index = $va_data["products_index"] + 1;

	// in case block was added on different than products page check if all vars was set
	if (!isset($current_category)) { $current_category = PRODUCTS_TITLE; }
	if (!isset($show_sub_products)) { $show_sub_products = false; }

	// clear all block vars
	$t->set_var("sorter_block", "");
	$t->set_var("navigator_block", "");
	$t->set_var("category_items", "");
	$t->set_var("items_category_name", "");
	$t->set_var("items_category_desc", "");
	$t->set_var("short_description", "");
	$t->set_var("full_description", "");
	$t->set_var("items_rows", "");
	$t->set_var("items_cols", "");

	$shopping_cart = get_session("shopping_cart");
	$records_per_page = get_setting_value($vars, "products_per_page", 10);
	$columns = get_setting_value($vars, "products_columns", 1);
	$products_default_view = get_setting_value($vars, "products_default_view", "list");
	$products_group_by_cats = get_setting_value($vars, "products_group_by_cats", 0);
	$products_sortings = get_setting_value($vars, "products_sortings", 0);
	$products_category_desc = get_setting_value($vars, "category_desc", 0);	
	$random_image = get_setting_value($vars, "random_image", ""); 
	$confirm_add   = get_setting_value($settings, "confirm_add", 1);
	$redirect_to_cart = get_setting_value($settings, "redirect_to_cart", ""); 
	$hide_add_limit = get_setting_value($settings, "hide_add_limit", ""); 
	$show_in_cart = get_setting_value($settings, "show_in_cart", ""); 
	$category_ids = get_setting_value($vars, "category_ids", "");
	$parent_table_view = 	get_setting_value($vars, "parent_table_view", "");

	$multi_add = get_setting_value($vars, "multi_add", 0);

	if ($products_default_view == "table") {
		$html_template = get_setting_value($block, "html_template", "block_products_table_view.html"); 
		$hide_add_column = "hide_add_table";
		$hide_view_column = "hide_view_table";
		$hide_checkout_column = "hide_checkout_table";
		$hide_wishlist_column = "hide_wishlist_table";
		$hide_free_shipping_column = "hide_free_shipping_table";
		$hide_more_column = "hide_more_table";

		$options_type = "table";
		$shop_hide_add_button = get_setting_value($settings, "hide_add_table", 0);
		$shop_hide_view_list = get_setting_value($settings, "hide_view_table", 0);
		$shop_hide_checkout_list = get_setting_value($settings, "hide_checkout_table", 0);
		$shop_hide_wishlist_list = get_setting_value($settings, "hide_wishlist_table", 0);
		$shop_hide_free_shipping = get_setting_value($settings, "hide_free_shipping_table", 0);
		$shop_hide_more_list = get_setting_value($settings, "hide_more_table", 0);
		$show_item_code = get_setting_value($settings, "item_code_table", 0);
		$show_manufacturer_code = get_setting_value($settings, "manufacturer_code_table", 0);
		$quantity_control = get_setting_value($settings, "quantity_control_table", "");
		$stock_level_list = get_setting_value($settings, "stock_level_table", 0);
		$columns = 1;
	} elseif ($products_default_view == "grid") {
		$html_template = get_setting_value($block, "html_template", "block_products_grid_view.html"); 
		$hide_add_column = "hide_add_grid";
		$hide_view_column = "hide_view_grid";
		$hide_checkout_column = "hide_checkout_grid";
		$hide_wishlist_column = "hide_wishlist_grid";
		$hide_free_shipping_column = "hide_free_shipping_grid";
		$hide_more_column = "hide_more_grid";

		$options_type = "grid";
		$shop_hide_add_button = get_setting_value($settings, "hide_add_grid", 0);
		$shop_hide_view_list = get_setting_value($settings, "hide_view_grid", 0);
		$shop_hide_checkout_list = get_setting_value($settings, "hide_checkout_grid", 0);
		$shop_hide_wishlist_list = get_setting_value($settings, "hide_wishlist_grid", 0);
		$shop_hide_free_shipping = get_setting_value($settings, "hide_free_shipping_grid", 0);
		$shop_hide_more_list = get_setting_value($settings, "hide_more_grid", 0);
		$show_item_code = get_setting_value($settings, "item_code_grid", 0);
		$show_manufacturer_code = get_setting_value($settings, "manufacturer_code_grid", 0);
		$quantity_control = get_setting_value($settings, "quantity_control_grid", "");
		$stock_level_list = get_setting_value($settings, "stock_level_grid", 0);
	} else {
		if (isset($list_template) && strlen($list_template)) {
			$html_template = $list_template; 
		} else {
			$html_template = get_setting_value($block, "html_template", "block_products_list.html"); 
		}
		$hide_add_column = "hide_add_list";
		$hide_view_column = "hide_view_list";
		$hide_checkout_column = "hide_checkout_list";
		$hide_wishlist_column = "hide_wishlist_list";
		$hide_free_shipping_column = "hide_free_shipping_list";
		$hide_more_column = "hide_more_list";

		$options_type = "list";
		$shop_hide_add_button = get_setting_value($settings, "hide_add_list", 0);
		$shop_hide_view_list = get_setting_value($settings, "hide_view_list", 0);
		$shop_hide_checkout_list = get_setting_value($settings, "hide_checkout_list", 0);
		$shop_hide_wishlist_list = get_setting_value($settings, "hide_wishlist_list", 0);
		$shop_hide_free_shipping = get_setting_value($settings, "hide_free_shipping_list", 0);
		$shop_hide_more_list = get_setting_value($settings, "hide_more_list", 0);
		$show_item_code = get_setting_value($settings, "item_code_list", 0);
		$show_manufacturer_code = get_setting_value($settings, "manufacturer_code_list", 0);
		$quantity_control = get_setting_value($settings, "quantity_control_list", "");
		$stock_level_list = get_setting_value($settings, "stock_level_list", 0);
	}
	$zero_quantity = $multi_add;

	$t->set_file("block_body",      $html_template);
	$t->set_var("items_cols",       "");
	$t->set_var("items_rows",       "");
	$t->set_var("PRODUCT_OUT_STOCK_MSG", htmlspecialchars(va_constant("PRODUCT_OUT_STOCK_MSG")));
	$t->set_var("out_stock_alert",       str_replace("'", "\\'", htmlspecialchars(va_constant("PRODUCT_OUT_STOCK_MSG"))));
	$t->set_var("confirm_add", $confirm_add);
	$t->set_var("redirect_to_cart", $redirect_to_cart);
	$t->set_var("multi_add", $multi_add);
	$t->set_var("columns_class", "cols-".$columns);
	$t->set_var("sc_params", htmlspecialchars(json_encode($sc_params)));

	$user_info = get_session("session_user_info");
	$user_tax_free = get_setting_value($user_info, "tax_free", 0);
	$discount_type = get_setting_value($user_info, "discount_type", "");
	$discount_amount = get_setting_value($user_info, "discount_amount", "");

	$tax_prices_type = get_setting_value($settings, "tax_prices_type", 0);
	$display_products = get_setting_value($settings, "display_products", 0);
	$php_in_short_desc = get_setting_value($settings, "php_in_products_short_desc", 0);
	$php_in_features = get_setting_value($settings, "php_in_products_features", 0);

	$weight_measure = get_setting_value($settings, "weight_measure", "");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	$points_system = get_setting_value($settings, "points_system", 0);
	$points_conversion_rate = get_setting_value($settings, "points_conversion_rate", 1);
	$points_decimals = get_setting_value($settings, "points_decimals", 0);
	$points_price_list = get_setting_value($settings, "points_price_list", 0);
	$reward_points_list = get_setting_value($settings, "reward_points_list", 0);
	$points_prices = get_setting_value($settings, "points_prices", 0);

	// credit settings
	$credit_system = get_setting_value($settings, "credit_system", 0);
	$reward_credits_users = get_setting_value($settings, "reward_credits_users", 0);
	$reward_credits_list = get_setting_value($settings, "reward_credits_list", 0);
	
	// new product settings	
	$new_product_enable = get_setting_value($settings, "new_product_enable", 0);	
	$new_product_order  = get_setting_value($settings, "new_product_order", 0);	
	
	// get products reviews settings
	$reviews_settings = get_settings("products_reviews");
	$reviews_allowed_view = get_setting_value($reviews_settings, "allowed_view", 0);
	$reviews_allowed_post = get_setting_value($reviews_settings, "allowed_post", 0);

	$product_params = prepare_product_params();

	$user_id = get_session("session_user_id");
	$user_type_id = get_session("session_user_type_id");
	$price_type = get_session("session_price_type");
	if ($price_type == 1) {
		$price_field = "trade_price";
		$sales_field = "trade_sales";
		$properties_field = "trade_properties_price";
	} else {
		$price_field = "price";
		$sales_field = "sales_price";
		$properties_field = "properties_price";
	}

	$watermark = false;
	$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");
	if ($products_default_view == "table") {
		$price_matrix_list = false;
		$product_no_image = get_setting_value($settings, "product_no_image_tiny", "");
		$image_type = get_setting_value($vars, "table_image_type", "tiny");	
	} else if ($products_default_view == "grid") {
		$price_matrix_list = false;
		$product_no_image = get_setting_value($settings, "product_no_image", "");
		$image_type = get_setting_value($vars, "grid_image_type", "small");	
	} else {
		$price_matrix_list = get_setting_value($settings, "price_matrix_list", 0);
		$product_no_image = get_setting_value($settings, "product_no_image", "");
		$image_type = get_setting_value($vars, "list_image_type", "small");	
	}
	if (!preg_match("/tiny|small|big|large|super/", $image_type)) {
		$image_type = "small";
		$image_field_type = "small";
	} else if ($image_type == "large") {
		$image_field_type = "big";
	} else {
		$image_field_type = $image_type;
	}
	$watermark = get_setting_value($settings, "watermark_".$image_field_type."_image", 0);
	$image_field = $image_field_type."_image";
	$image_field_alt = $image_field_type."_image_alt";

	srand((double) microtime() * 1000000);
	$random_value = rand();
	$current_ts = va_timestamp();

	$param_pb_id = get_param("pb_id");
	$category_id = get_param("category_id");
	$page_item_id = get_param("item_id");
	$search_string = trim(get_param("search_string"));
	$sq = trim(get_param("sq"));
	$pq = get_param("pq");
	$fq = get_param("fq");
	$s_tit = get_param("s_tit");
	$s_des = get_param("s_des");
	$manf = get_param("manf");
	$user = get_param("user");
	if ($display_products != 2 || strlen($user_id)) {
		$lprice = get_param("lprice");
		$hprice = get_param("hprice");
	} else {
		$lprice = ""; $hprice = "";
	}
	$lweight = get_param("lweight");
	$hweight = get_param("hweight");
	$pn_pr = get_param("pn_pr");
	$is_search = (strlen($search_string) || strlen($sq) || ($pq > 0) || ($fq > 0) || strlen($lprice) || strlen($hprice) || strlen($lweight) || strlen($hweight));
	$is_manufacturer = strlen($manf);
	$is_user = strlen($user);
	$sort = get_param("sort");
	if (!$sort) { $sort = "default"; } // use default sorting if other wasn't specified
	$sort_ord = get_param("sort_ord");
	$sort_dir = get_param("sort_dir");
	$filter = get_param("filter");
	// keywords parameters
	$keywords_search = get_setting_value($settings, "keywords_search", 0);
	$kw_no_records = false;
	$kw_rank = ""; $kw_join = ""; 


	if (!strlen($category_id)) $category_id = "0";

	if ($friendly_urls && isset($page_friendly_url) && $page_friendly_url) {
		$products_page = $page_friendly_url . $friendly_extension;
	} elseif ($is_search) {
		$products_page = get_custom_friendly_url("products_search.php");
	} else {
		$products_page = get_custom_friendly_url($script_name);
	}
	if ($is_search) {
		$products_form_url = "products_search.php";
	} else {
		$products_form_url = $script_name;
	}
	$t->set_var("products_href", $products_page);
	$t->set_var("products_form_url", $products_form_url);
	$t->set_var("product_details_href", get_custom_friendly_url("product_details.php"));
	$t->set_var("basket_href",   get_custom_friendly_url("basket.php"));
	$t->set_var("checkout_href", get_custom_friendly_url("checkout.php"));
	$t->set_var("reviews_href", get_custom_friendly_url("reviews.php"));
	$t->set_var("compare_href", get_custom_friendly_url("compare.php"));
	$t->set_var("cl", $currency["left"]);
	$t->set_var("cr", $currency["right"]);
	$t->set_var("category_id", htmlspecialchars($category_id));
	$t->set_var("tax_prices_type", $tax_prices_type);
	$t->set_var("current_category_name", $current_category);
	if ($param_pb_id == $pb_id) {
		// show message about added products 
		if ($sc_errors) {
			$t->set_var("errors_list", $sc_errors);
			$t->parse("sc_errors", false);
		} 
		if ($sc_message) {
			$t->set_var("added_message", $sc_message);
			$t->parse("item_added", false);
		}
	}

	$pass_parameters = array(
		"item_id" => $page_item_id, "category_id" => $category_id, 
		"search_string" => $search_string, "sq" => $sq,
		"pq" => $pq, "fq" => $fq, 
		"s_tit" => $s_tit, "s_des" => $s_des,
		"manf" => $manf, "user" => $user, "lprice" => $lprice, "hprice" => $hprice,
		"lweight" => $lweight, "hweight" => $hweight,
		"sort_ord" => $sort_ord, "sort_dir" => $sort_dir, "filter" => $filter,
		"page" => get_param("page"), 
	);


	$pr_where = ""; $pr_brackets = ""; $pr_join = "";
	if ($pq > 0) {
		for ($pi = 1; $pi <= $pq; $pi++) {
			$property_name = get_param("pn_" . $pi);
			$property_value = get_param("pv_" . $pi);
			if (strlen($property_name) && strlen($property_value)) {
				$pass_parameters["pn_" . $pi] = $property_name;
				$pass_parameters["pv_" . $pi] = $property_value;

				$pr_join .= " INNER JOIN ( ";
				$pr_join .= " SELECT ip.usage_type, ip.item_id AS ip_item_id, ipa.item_id AS ipa_item_id, ip.item_type_id AS ip_item_type_id, iva.item_id AS iva_item_id ";
				$pr_join .= " FROM ".$table_prefix."items_properties ip ";
				$pr_join .= " LEFT JOIN ".$table_prefix."items_properties_values ipv ON ipv.property_id=ip.property_id ";
				$pr_join .= " LEFT JOIN ".$table_prefix."items_properties_assigned ipa ON ipa.property_id=ip.property_id ";
				$pr_join .= " LEFT JOIN ".$table_prefix."items_values_assigned iva ON ipv.property_id=ip.property_id ";
				$pr_join .= " WHERE ip.property_name=".$db->tosql($property_name, TEXT);
				$pr_join .= " AND ( ";
				$pr_join .= " 	(ip.usage_type=1 AND (ip.property_description LIKE '%".$db->tosql($property_value, TEXT, false)."%' OR ipv.property_value LIKE '%".$db->tosql($property_value, TEXT, false)."%')) ";
				$pr_join .= " 	OR ";
				$pr_join .= " 	(ip.item_id=0 AND ip.usage_type=2 AND iva.property_value_id=ipv.item_property_id AND (ipa.property_description LIKE '%".$db->tosql($property_value, TEXT, false)."%' OR ipv.property_value LIKE '%".$db->tosql($property_value, TEXT, false)."%')) ";
				$pr_join .= " 	OR ";
				$pr_join .= " 	(ip.item_id=0 AND ip.usage_type=3 AND (ipa.property_description LIKE '%".$db->tosql($property_value, TEXT, false)."%' OR ipv.property_value LIKE '%".$db->tosql($property_value, TEXT, false)."%' )) ";
				$pr_join .= " ) ";
				$pr_join .= " ) ip$pi ON (((i.item_id=ip$pi.ip_item_id OR i.item_type_id=ip$pi.ip_item_type_id) AND ip$pi.usage_type=1) OR (i.item_id=ip$pi.ipa_item_id AND ip$pi.usage_type=3) OR (i.item_id=ip$pi.iva_item_id AND ip$pi.usage_type=2)) ";
			}
		}
	}
	if ($fq > 0) {
		for ($fi = 1; $fi <= $fq; $fi++) {
			$feature_name = get_param("fn_" . $fi);
			$feature_value = get_param("fv_" . $fi);
			if (strlen($feature_name) && strlen($feature_value)) {
				$pass_parameters["fn_" . $fi] = $feature_name;
				$pass_parameters["fv_" . $fi] = $feature_value;

				if (strlen($pr_where)) $pr_where .= " AND ";
				$pr_where .= " f_".$fi.".feature_name=" . $db->tosql($feature_name, TEXT);
				$pr_where .= " AND f_".$fi.".feature_value LIKE '%" . $db->tosql($feature_value, TEXT, false) . "%' ";
				$pr_brackets .= "(";
				$pr_join  .= " LEFT JOIN " . $table_prefix . "features f_".$fi." ON i.item_id = f_".$fi.".item_id) ";
			}
		}
	}
	filter_sqls($pr_brackets, $pr_join, $pr_where);
	
	$sql_params = array();
	//$sql_params["brackets"] = $pr_brackets . "((";		
	$sql_params["join"][]     = " INNER JOIN " . $table_prefix . "items_categories ic ON i.item_id=ic.item_id  ";		
	if (($is_search || $is_manufacturer || $show_sub_products) && $category_id != 0)	{
		$sql_params["join"][] = "INNER JOIN " . $table_prefix . "categories c ON c.category_id = ic.category_id ";
	} else {
		//$sql_params["join"] .= ")";
	}
	$sql_params["join"][] = $pr_join;

	$sql_where = "";
	if ($category_ids)	{
		$sql_where .= " ic.category_id IN (" . $db->tosql($category_ids, INTEGERS_LIST) . ")";
	} else if (($is_search || $is_manufacturer || $show_sub_products) && $category_id != 0)	{
		if (strlen($sql_where)) $sql_where .= " AND ";
		$sql_where .= " (ic.category_id = " . $db->tosql($category_id, INTEGER);
		$sql_where .= " OR c.category_path LIKE '" . $db->tosql($category_path, TEXT, false) . "%')";
	} elseif (!$is_search && !$is_manufacturer && !$is_user) {
		if (strlen($sql_where)) $sql_where .= " AND ";
		$sql_where .= " ic.category_id = " . $db->tosql($category_id, INTEGER);
	}
	if (strlen($manf)) {
		if (strlen($sql_where)) $sql_where .= " AND ";
		$sql_where .= " i.manufacturer_id= " . $db->tosql($manf, INTEGER);
	}
	if (strlen($user)) {
		if (strlen($sql_where)) $sql_where .= " AND ";
		$sql_where .= " i.user_id= " . $db->tosql($user, INTEGER);
	}
	if (strlen($lprice)) {
		if (strlen($sql_where)) $sql_where .= " AND ";
		$conv_price = $lprice / $currency["rate"];
		$sql_where .= " ( ";
		$sql_where .= " (i.is_sales=1 AND (i." . $sales_field . "+i.".$properties_field.")>=" . $db->tosql($conv_price, NUMBER) . ") ";
		$sql_where .= " OR ((i.is_sales<>1 OR i.is_sales IS NULL) AND (i." . $price_field . "+i.".$properties_field.")>= " . $db->tosql($conv_price, NUMBER) . ") ";
		$sql_where .= ") ";
	}
	if (strlen($hprice)) {
		if (strlen($sql_where)) $sql_where .= " AND ";
		$conv_price = $hprice / $currency["rate"];
		$sql_where .= " ( ";
		$sql_where .= " (i.is_sales=1 AND (i." . $sales_field . "+i.".$properties_field.")<=" . $db->tosql($conv_price, NUMBER) . ") ";
		$sql_where .= " OR ((i.is_sales<>1 OR i.is_sales IS NULL) AND (i." . $price_field . "+i.".$properties_field.")<= " . $db->tosql($conv_price, NUMBER) . ") ";
		$sql_where .= ") ";
	}
	if (strlen($lweight)) {
		if (strlen($sql_where)) $sql_where .= " AND ";
		$sql_where .= " i.weight>=" . $db->tosql($lweight, NUMBER);
	}
	if (strlen($hweight)) {
		if (strlen($sql_where)) $sql_where .= " AND ";
		$sql_where .= " i.weight<=" . $db->tosql($hweight, NUMBER);
	}
	if (strlen($search_string) || strlen($sq)) {
		if (strlen($sq)) {
			VA_Products::keywords_sql($sq, $kw_no_records, $kw_rank, $kw_join, $kw_where);
		} else {
			VA_Products::keywords_sql($search_string, $kw_no_records, $kw_rank, $kw_join, $kw_where);
		}

		$sql_params["join"][] = $kw_join;
		if ($kw_where && $sql_where) { $sql_where .= " AND ";	}
		$sql_where .= $kw_where;
	}
	if (strlen($sql_where) && strlen($pr_where)) { $sql_where .= " AND "; }
	$sql_where .= $pr_where;
	$sql_params["where"] = $sql_where;
	if ($products_group_by_cats) {
		if ($db_type != 'postgre') {
			$sql_params["distinct"] = " ic.category_id, i.item_id";
		}
	} else {
		$sql_params["distinct"] = " i.item_id";
	}
	
	if ($keywords_search && $kw_no_records) {
		$total_records = 0;
	} else {
		if ($products_group_by_cats) {
			$sql_params["select"] = " ic.category_id, i.item_id ";
			$sql_params["group"] = " ic.category_id, i.item_id ";
		} else {
			$sql_params["select"] = "i.item_id ";
			$sql_params["group"] = " i.item_id";
		}
		$sql = VA_Products::sql($sql_params, VIEW_CATEGORIES_ITEMS_PERM);
	  $count_sql = "SELECT COUNT(*) FROM (".$sql.") count_sql";
		$total_records = get_db_value($count_sql);
	}
	$sql_params["distinct"] = "";

	// prepare url for  sorters
	$sort_remove_params = array();
	$details_parameters = $pass_parameters; // use all parameters for details page
	if (isset($details_parameters["category_id"])) { unset($details_parameters["category_id"]); } // unset category_id parameter
	if ($friendly_urls && isset($page_friendly_url) && $page_friendly_url) {
		$sort_remove_params = $page_friendly_params;
		for ($fp = 0; $fp < sizeof($page_friendly_params); $fp++) {
			unset($pass_parameters[$page_friendly_params[$fp]]);
		}
	}
	$sort_remove_params = array_merge($sort_remove_params, array("sort", "sort_ord", "sort_dir"));
	$sort_query = get_query_string(get_transfer_params($sort_remove_params));
	$sort_page = ($sort_query) ? $products_page.$sort_query."&sort=" : $products_page."?sort=";

	$order_group_columns = "";
	$s = new VA_Sorter($settings["templates_dir"], "sorter.html", $products_page, "sort", "", $pass_parameters);
	// use products order for category only if results grouped by categories or it is only one category products available
	$category_order = ($products_group_by_cats || (!$show_sub_products && ($category_id || (!$is_search && !$is_manufacturer && !$is_user))));
	if ($products_sortings) {
		$s->set_parameters(false, true, true, false);
		$s->set_default_sorting(1, "asc");
		$table_column = "";
		if ($category_order) {
			$table_column = "ic.item_order, i.item_order, i.item_id";
			$column_asc = "ic.item_order, i.item_order, i.item_id";
			$column_desc = "ic.item_order DESC, i.item_order, i.item_id";
		} else {
			$table_column = "i.item_order, i.item_id"; 
			$column_asc = "i.item_order, i.item_id";
			$column_desc = "i.item_order DESC, i.item_id";
		}
		if ($keywords_search && $is_search && (strlen($search_string) || strlen($sq))) {
			$table_column = "keywords_rank, ".$table_column;
			$column_asc = "keywords_rank DESC, ".$column_asc;
			$column_desc = "keywords_rank ASC, ".$column_desc;
		}

		if ($db_type == "mysql") {
			$price_asc  = "IF(i.is_sales=1, i.sales_price + COALESCE(i.properties_price,0), i.price + COALESCE(i.properties_price,0) )";
			$price_desc = "IF(i.is_sales=1, i.sales_price + COALESCE(i.properties_price,0), i.price + COALESCE(i.properties_price,0) ) DESC";
		} elseif ($db_type == "access") {
			$price_asc  = "IIF(i.is_sales=1, (i.sales_price + IIF(ISNULL(i.properties_price),0,i.properties_price)), (i.price + IIF(ISNULL(i.properties_price),0,i.properties_price)) )"; 
			$price_desc = "IIF(i.is_sales=1, (i.sales_price + IIF(ISNULL(i.properties_price),0,i.properties_price)), (i.price + IIF(ISNULL(i.properties_price),0,i.properties_price)) ) DESC";
		} elseif ($db_type == "sqlsrv") {
			$price_asc  = "IIF(i.is_sales=1, (i.sales_price + ISNULL(i.properties_price, 0)), (i.price + ISNULL(i.properties_price,0)) )"; 
			$price_desc = "IIF(i.is_sales=1, (i.sales_price + ISNULL(i.properties_price, 0)), (i.price + ISNULL(i.properties_price,0)) ) DESC";
		} elseif ($db_type == "postgre") {
			$price_asc  = "(CASE WHEN i.is_sales=1 THEN i.sales_price + COALESCE(i.properties_price,0) ELSE i.price + COALESCE(i.properties_price,0) END)";
			$price_desc = "(CASE WHEN i.is_sales=1 THEN i.sales_price + COALESCE(i.properties_price,0) ELSE i.price + COALESCE(i.properties_price,0) END) DESC";
		}
		/*
		$s->set_sorter(PROD_SORT_DEFAULT_MSG, "sorter_default", "1", $table_column, $column_asc, $column_desc);
		$s->set_sorter(PRICE_MSG, "sorter_price", "2", "i.price");
		$s->set_sorter(PROD_SORT_MANUFACTURER_MSG, "sorter_manufacturer", "3", "m.manufacturer_name, i.item_id", "m.manufacturer_name, i.item_id", "m.manufacturer_name DESC, i.item_id");
		$s->set_sorter(NAME_MSG, "sorter_name", "4", "i.item_name, i.item_id");
		if ($show_manufacturer_code) {
			$s->set_sorter(PROD_SORT_CODE_MSG, "sorter_code", "5", "i.manufacturer_code, i.item_id", "i.manufacturer_code, i.item_id", "i.manufacturer_code DESC, i.item_id");
		} else {
			$s->set_sorter(PROD_SORT_CODE_MSG, "sorter_code", "5", "i.item_code, i.item_id", "i.item_code, i.item_id", "i.item_code DESC, i.item_id");
		}//*/
		$sort_items = array(
			"default" => array(
				"class" => "",
				"name" => va_constant("DEFAULT_MSG"),
				"sql" => $column_asc,
				"col" => $table_column,
			),
			"price-asc" => array(
				"class" => "fa number-asc",
				"name" => va_constant("PRICE_MSG"),
				"sql" => $price_asc,
				"col" => "i.item_id, i.is_sales, i.sales_price, i.properties_price, i.price ",
			),
			"price-desc" => array(
				"class" => "fa number-desc",
				"name" => va_constant("PRICE_MSG"),
				"sql" => $price_desc,
				"col" => "i.item_id, i.is_sales, i.sales_price, i.properties_price, i.price ",
			),
			"name-asc" => array(
				"class" => "fa name-asc",
				"name" => va_constant("NAME_MSG"),
				"sql" => "i.item_name, i.item_id",
				"col" => "i.item_name, i.item_id",
			),
			"name-desc" => array(
				"class" => "fa name-desc",
				"name" => va_constant("NAME_MSG"),
				"sql" => "i.item_name DESC, i.item_id",
				"col" => "i.item_name, i.item_id",
			),
		);
		foreach ($sort_items as $sort_code => $sort_data) {
			$sorter_class = isset($sort_data["class"]) ? $sort_data["class"] : "";
			if ($sort == $sort_code) {
				$order_group_columns = $sort_data["col"];
				$t->set_var("active_sorter_class", htmlspecialchars($sorter_class));
				$t->set_var("active_sorter_name", htmlspecialchars($sort_data["name"]));
				$sql_order_by = " ORDER BY " . $sort_data["sql"];
			} else {
				$t->set_var("sorter_class", htmlspecialchars($sorter_class));
				$t->set_var("sorter_name", htmlspecialchars($sort_data["name"]));
				$t->set_var("sorter_url", htmlspecialchars($sort_page.$sort_code));
				$t->sparse("sort_items", true);
			}
		}
		$t->sparse("sorter_block", false);
	} else {
		if ($category_order) {
			$sql_order_by = " ORDER BY ic.item_order, i.item_order ";
		} else {
			$sql_order_by = " ORDER BY i.item_order ";
		}
		if ($keywords_search && $is_search && (strlen($search_string) || strlen($sq))) {
			$sql_order_by = " ORDER BY keywords_rank DESC, ";
			if ($category_order) {
				$sql_order_by .= "ic.item_order, ";
			}
			$sql_order_by .= "i.item_order ";
		}
	}

	if ($products_group_by_cats) {
		// when we are grouping by categories we should always have order by categories first
		if (($is_search || $is_manufacturer || $show_sub_products) && $category_id != 0)	{
			$sql_order_by = str_replace("ORDER BY", "ORDER BY c.category_order, ic.category_id,", $sql_order_by);
		} else {
			$sql_order_by = str_replace("ORDER BY", "ORDER BY ic.category_id,", $sql_order_by);
		}
	}

	// set up variables for navigator
	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", $products_page);

	$products_nav_type = get_setting_value($vars, "products_nav_type", 1);
	$products_nav_pages = get_setting_value($vars, "products_nav_pages", 5);
	$products_nav_first_last = get_setting_value($vars, "products_nav_first_last", 0);
	$products_nav_prev_next = get_setting_value($vars, "products_nav_prev_next", 1);
	$inactive_links = false;

	$n->set_parameters($products_nav_first_last, $products_nav_prev_next, $inactive_links);
	$page_number = $n->set_navigator("navigator", "pn_pr", $products_nav_type, $products_nav_pages, $records_per_page, $total_records, false, $pass_parameters);
	$page_number = $n->set_navigator("navigator_top", "pn_pr", $products_nav_type, $products_nav_pages, $records_per_page, $total_records, false, $pass_parameters);
	$page_number = $n->set_navigator("navigator_bottom", "pn_pr", $products_nav_type, $products_nav_pages, $records_per_page, $total_records, false, $pass_parameters);
	$total_pages = ceil($total_records / $records_per_page);

	// generate page link with query parameters
	$pass_parameters["pn_pr"] = $pn_pr;
	$query_string = get_query_string($pass_parameters, "", "", false);
	$rp  = $products_page;
	$rp	.= $query_string;
	$cart_link  = $rp;
	$cart_link .= strlen($query_string) ? "&" : "?";
	$cart_link .= "rnd=" . $random_value . "&";

	// set hidden parameter with category_id parameter
	$hidden_parameters = $pass_parameters;
	$hidden_parameters["category_id"] = $category_id;
	get_query_string($hidden_parameters, "", "", true);

	// remove page and sorting parameters from url
	$details_query = get_query_string($details_parameters, array("pn_pr", "sort_ord", "sort_dir"), "", false);
	$product_link  = get_custom_friendly_url("product_details.php") . $details_query;
	$product_link .= strlen($details_query) ? "&" : "?";
	$product_link .= "item_id=";
	$reviews_link  = get_custom_friendly_url("reviews.php") . $details_query;
	$reviews_link .= strlen($details_query) ? "&" : "?";
	$reviews_link .= "item_id=";

	$t->set_var("rnd", $random_value);
	$t->set_var("rp_url", urlencode($rp));
	$t->set_var("rp", htmlspecialchars($rp));
	$t->set_var("total_records", $total_records);

	$items_indexes = array();
	if ($total_records)	{
		if ($products_group_by_cats) {
			if ($order_group_columns && $sort != "price-desc" && $sort != "price-asc") { 
				$group_by = "ic.category_id, i.item_id, " . $order_group_columns; 
			} else {
				$group_by = "ic.category_id, i.item_id, i.is_sales, i.sales_price, i.properties_price, i.price";
			}
			if (($is_search || $is_manufacturer || $show_sub_products) && $category_id != 0)	{
				$group_by .= ", c.category_order"; 
			}

			$sql_params["select"] = " i.item_id, ic.category_id";
			$sql_params["group"] = $group_by;
			$sql_params["order"] = $sql_order_by;
		} else {
			if ($order_group_columns) { 
				$group_by = $order_group_columns; 
			} else {
				$group_by = " ic.item_order, i.item_id, i.is_sales, i.sales_price, i.properties_price, i.price ";
			}
			$sql_params["select"] = " i.item_id ";
			$sql_params["group"] = $group_by;
			$sql_params["order"] = $sql_order_by;
		}


		// added keywords_rank field for search
		if ($keywords_search && $is_search && (strlen($search_string) || strlen($sq))) {
			$sql_params["select"] .= ", " . $kw_rank . " AS keywords_rank";
		}
		if (preg_match("/m\.manufacturer_name/", $sql_order_by)) {
			// join manufacturer table to order by manufacturer_name
			//$sql_params["brackets"] .= "(";		
			$sql_params["join"] .= " LEFT JOIN " . $table_prefix . "manufacturers m ON i.manufacturer_id=m.manufacturer_id ";
		}

		$ids = VA_Products::data($sql_params, VIEW_CATEGORIES_ITEMS_PERM, $records_per_page, $page_number);

		$items_where = ""; $items_ids = array(); 
		$categories_ids = array();
		if ($category_id) {
			$categories_ids[] = $category_id;
		}
		for($id = 0; $id < sizeof($ids); $id++) {
			$items_ids[] = $ids[$id]["item_id"];
			if ($products_group_by_cats) {
				if ($items_where) { $items_where .= " OR "; }
				$items_where .= "(ic.item_id=" . $db->tosql($ids[$id]["item_id"], INTEGER);
				$items_where .= " AND ic.category_id=" . $db->tosql($ids[$id]["category_id"], INTEGER);
				$items_where .= ")";
				if ($category_id) {
					$categories_ids[] = $ids[$id]["category_id"];
				}
			}
		}

		// get different table view for different categories
		$table_columns = array();
		if ($products_default_view == "table") {
			// check table view columns
			$sql  = " SELECT cc.* FROM (" . $table_prefix . "categories_columns cc ";
			$sql .= " LEFT JOIN " . $table_prefix . "categories c ON c.category_id=cc.category_id) ";
			$sql .= " WHERE cc.category_id=0 ";
			if (sizeof($categories_ids)) {
				$sql .= " OR (c.table_view=1 AND cc.category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . "))";
			}
			$sql .= " ORDER BY cc.category_id,cc.column_order ";
			$db->query($sql);
			while ($db->next_record()) {
				$table_category_id = $db->f("category_id");
				$column_id = $db->f("column_id");
				$column_class = $db->f("column_class");
				$original_code = $db->f("column_code");
				$column_codes = preg_split("/[\s,\|\#\&]+/", $original_code);
				$table_columns[$table_category_id]["cols"][$column_id] = array(
					"code" => $original_code,
					"codes" => $column_codes,
					"class" => $column_class,
					"title" => $db->f("column_title"),
					"html" => $db->f("column_html"),
				);
				for($ci = 0; $ci < sizeof($column_codes); $ci++) {
					$check_code = $column_codes[$ci];
					if (preg_match("/^option_(.+)$/", $check_code, $matches)) {
						$table_columns[$table_category_id]["options"][] = $matches[1];
					} else if (preg_match("/^feature_(.+)$/", $check_code, $matches)) {
						$table_columns[$table_category_id]["features"][] = $matches[1];
					}
				}
			}
		}

		$allowed_items_ids = VA_Products::find_all_ids("i.item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")", VIEW_ITEMS_PERM);

		$items_categories = array();
		if ($is_search || $is_manufacturer) {
			$sql  = " SELECT ic.item_id, ic.category_id, c.is_showing, c.category_name, c.friendly_url ";
			$sql .= " FROM (" . $table_prefix . "items_categories ic ";
			$sql .= " LEFT JOIN " . $table_prefix . "categories c ON ic.category_id=c.category_id) ";
			$sql .= " WHERE ic.item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ") ";
			$db->query($sql);
			while ($db->next_record()) {
				$item_id = $db->f("item_id");
				$ic_id   = $db->f("category_id");
				$ic_friendly_url = $db->f("friendly_url");
				if ($friendly_urls && strlen($ic_friendly_url)) {
					$ic_url = $ic_friendly_url.$friendly_extension;
				} else {
					$ic_url = "products_list.php?category_id=".$ic_id;
				}
				$ic_showing = $db->f("is_showing");
				$ic_name = get_translation($db->f("category_name"));
				if (!$ic_showing || !strlen($ic_name)) { $ic_name = PRODUCTS_TITLE; }
				$items_categories[$item_id][$ic_id] = array("name" => $ic_name, "url" => $ic_url);
			}
		}
				
		$sql  = " SELECT i.item_id, i.item_type_id, i.item_code, i.item_name, i.a_title, i.friendly_url, i.short_description, i.features, i.is_compared, ";
		$sql .= " i.tiny_image, i.tiny_image_alt, i.small_image, i.small_image_alt, i.big_image, i.big_image_alt, super_image, ";
		$sql .= " i.buying_price, i." . $price_field . ", i.is_price_edit, i." . $sales_field . ", i.discount_percent, ";
		$sql .= " i.is_points_price, i.points_price, i.reward_type, i.reward_amount, i.credit_reward_type, i.credit_reward_amount, ";
		$sql .= " it.reward_type AS type_bonus_reward, it.reward_amount AS type_bonus_amount, ";
		$sql .= " it.credit_reward_type AS type_credit_reward, it.credit_reward_amount AS type_credit_amount, ";
		$sql .= " i.tax_id, i.tax_free, i.weight, i.buy_link, i.total_views, i.votes, i.points, i.is_sales, ";
		$sql .= " i.manufacturer_code, m.manufacturer_name, m.affiliate_code, ";
		$sql .= " i.issue_date, i.stock_level, i.use_stock_level, i.disable_out_of_stock, i.min_quantity, i.max_quantity, quantity_increment, ";
		$sql .= " i.hide_out_of_stock, i.".$hide_add_column.", i.".$hide_view_column.", i.".$hide_checkout_column.", i.".$hide_wishlist_column.", ";
		$sql .= " i.".$hide_free_shipping_column.", i.".$hide_more_column.", ";
		$sql .= " i.is_shipping_free, st_in.shipping_time_desc AS in_stock_message, st_out.shipping_time_desc AS out_stock_message ";
		// new product db
		if ($new_product_enable) {
			switch ($new_product_order) {
				case 0:
					$sql .= ", i.issue_date AS new_product_date ";
				break;
				case 1:
					$sql .= ", i.date_added AS new_product_date ";
				break;
				case 2:
					$sql .= ", i.date_modified AS new_product_date ";
				break;
			}		
		}
		if ($products_group_by_cats) {
			$sql .= " , ic.category_id, c.is_showing, c.category_name, c.short_description AS category_short_description, c.full_description AS category_full_description ";
		}
		if ($keywords_search && $is_search && (strlen($search_string) || strlen($sq))) {
			$sql .= ", " . $kw_rank . " AS keywords_rank";
		}
		$sql .= " FROM ((((";
		if ($products_group_by_cats) {
			$sql .= "((";
		} else if ($category_order) {
			$sql .= "(";
		}
		$sql .= $table_prefix . "items i ";
		if ($products_group_by_cats) {
			$sql .= " INNER JOIN " . $table_prefix . "items_categories ic ON i.item_id=ic.item_id) ";
			$sql .= " LEFT JOIN " . $table_prefix . "categories c ON c.category_id = ic.category_id) ";
		} else if ($category_order) {
			$sql .= " INNER JOIN " . $table_prefix . "items_categories ic ON i.item_id=ic.item_id) ";
		}
		$sql .= $kw_join;
		$sql .= " LEFT JOIN " . $table_prefix . "item_types it ON i.item_type_id=it.item_type_id) ";
 		$sql .= " LEFT JOIN " . $table_prefix . "manufacturers m ON i.manufacturer_id=m.manufacturer_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "shipping_times st_in ON i.shipping_in_stock=st_in.shipping_time_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "shipping_times st_out ON i.shipping_out_stock=st_out.shipping_time_id) ";
		if ($items_where) {
			$sql .= " WHERE (" . $items_where . ") ";
		} else {
			$sql .= " WHERE i.item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ") ";
		}
		if (!$category_ids && !$is_search && !$is_manufacturer && !$products_group_by_cats && $category_order) {
			// if products should be shown from one category 
			$sql .= " AND ic.category_id=" . $db->tosql($category_id, INTEGER);
		}
		$sql .= $sql_order_by;
		$t->set_var("category_id", htmlspecialchars($category_id));
		$db->query($sql);
		if ($db->next_record())
		{
			$last_category_id = $db->f("category_id");
			$last_category_name = $db->f("category_name");
			$t->set_var("item_column", (100 / $columns) . "%");
			$t->set_var("col_style", "width: " . (100 / $columns) . "%;");

			$t->set_var("total_columns", $columns);
			$item_number = 0;
			
			// item previews 
			$previews = new VA_Previews();
			$previews->preview_type     = array(1,2);
			$previews->preview_position = 3;
			do
			{
				$item_number++;
				$va_data["products_index"]++;
				$items_indexes[] = $va_data["products_index"];
				$item_id = $db->f("item_id");
				$item_category_id = $db->f("category_id");
				$item_category_name = get_translation($db->f("category_name"));
				$category_is_showing = $db->f("is_showing");
				$category_short_description = trim(get_translation($db->f("category_short_description")));
				$category_full_description = trim(get_translation($db->f("category_full_description")));
				$item_category_desc = "";
				if ($category_is_showing) {
					if ($products_category_desc == 1) {
						$item_category_desc = $category_short_description;
					} elseif ($products_category_desc == 2) {
						$item_category_desc = $category_full_description;
					}
				}

				if (!$category_is_showing || strval($item_category_name) == "") {
					$item_category_name = PRODUCTS_TITLE;
				}

				if ($products_default_view == "table") {
					$columns_category_id = ($products_group_by_cats && !$parent_table_view) ? $item_category_id : $category_id;
					set_tv_cols($table_columns, $columns_category_id);
				} 

				$item_type_id = $db->f("item_type_id");
				$item_code = $db->f("item_code");
				$form_id = $va_data["products_index"];

				$product_params["form_id"] = $form_id;
				$item_name = get_translation($db->f("item_name"));
				$product_params["item_name"] = strip_tags($item_name);
				$a_title = get_translation($db->f("a_title"));
				$highlights = get_translation($db->f("features"));
				//eval_php_code($highlights);
				$friendly_url = $db->f("friendly_url");
				$is_compared = $db->f("is_compared");
				$manufacturer_code = $db->f("manufacturer_code");
				$manufacturer_name = $db->f("manufacturer_name");
				$issue_date_ts = 0;
				$issue_date = $db->f("issue_date", DATETIME);
				if (is_array($issue_date)) {
					$issue_date_ts = va_timestamp($issue_date);
				}

				$price = $db->f($price_field);
				$is_price_edit = $db->f("is_price_edit");
				$is_sales = $db->f("is_sales");
				$sales_price = $db->f($sales_field);
				$min_quantity = $db->f("min_quantity");
				$max_quantity = $db->f("max_quantity");
				$quantity_increment = $db->f("quantity_increment");
				$coupons_ids = ""; $coupons_discount = ""; $coupons_applied = array();
				get_sales_price($price, $is_sales, $sales_price, $item_id, $item_type_id, "", "", $coupons_ids, $coupons_discount, $coupons_applied);
				
				// special prices
				$discount_applicable = 1;
				$initial_quantity = ($min_quantity) ? $min_quantity : 1;
				$q_prices   = get_quantity_price($item_id, 1);
				// calcalutate quantity price
				if ($q_prices) {
					$user_price          = $q_prices[0];
					$discount_applicable = $q_prices[2];
					if ($is_sales) {
						$sales_price = $user_price;
					} else {
						$price = $user_price;
					}
				}
				
				$buying_price = $db->f("buying_price");					
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
				if (!strlen($is_points_price)) {
					$is_points_price = $points_prices;
				}

				$weight = $db->f("weight");
				$total_views = $db->f("total_views");
				$tax_id = $db->f("tax_id");
				$tax_free = $db->f("tax_free");
				if ($user_tax_free) { $tax_free = $user_tax_free; }
				$stock_level = $db->f("stock_level");
				$use_stock_level = $db->f("use_stock_level");
				$disable_out_of_stock = $db->f("disable_out_of_stock");
				$hide_out_of_stock = $db->f("hide_out_of_stock");
				$hide_add_button = $db->f($hide_add_column);
				$hide_view_button = $db->f($hide_view_column);
				$hide_checkout_button = $db->f($hide_checkout_column);
				$hide_wishlist_button = $db->f($hide_wishlist_column);
				$hide_free_shipping = $db->f($hide_free_shipping_column);
				$hide_more_button = $db->f($hide_more_column);

				$quantity_limit = ($use_stock_level && ($disable_out_of_stock || $hide_out_of_stock));
				$is_shipping_free = $db->f("is_shipping_free");
				$in_stock_message = get_translation($db->f("in_stock_message"));
				$out_stock_message = get_translation($db->f("out_stock_message"));
				$min_quantity = $db->f("min_quantity");
				$max_quantity = $db->f("max_quantity");

				$short_description = trim(get_translation($db->f("short_description")));

				$product_params["sl"] = $stock_level;
				$product_params["use_sl"] = $use_stock_level;
				$product_params["in_sm"] = $in_stock_message;
				$product_params["out_sm"] = $out_stock_message;
				$product_params["min_qty"] = $min_quantity;
				$product_params["max_qty"] = $max_quantity;
				$product_params["image_type"] = $image_type;
				$product_params["random_image"] = $random_image;

				if ($new_product_enable) {
					$new_product_date = $db->f("new_product_date");
					$is_new_product   = is_new_product ($new_product_date);
				} else {
					$is_new_product = false;
				}
				if ($is_new_product) {
					$t->set_var("product_new_class", " ico-new ");
				} else {
					$t->set_var("product_new_class", "");
				}

				if (!$allowed_items_ids || !in_array($item_id, $allowed_items_ids)) {
					$t->set_var("restricted_class", " restricted ");
					$hide_add_button = true;
				} else {
					$t->set_var("restricted_class", "");
				}
				
				if ($discount_applicable) {
					if ($discount_type == 1 || $discount_type == 3) {
						$price -= round(($price * $discount_amount) / 100, 2);
						$sales_price -= round(($sales_price * $discount_amount) / 100, 2);
					} elseif ($discount_type == 2) {
						$price -= round($discount_amount, 2);
						$sales_price -= round($discount_amount, 2);
					} elseif ($discount_type == 4) {
						$price -= round((($price - $buying_price) * $discount_amount) / 100, 2);
						$sales_price -= round((($sales_price - $buying_price) * $discount_amount) / 100, 2);
					}
				}
				$item_price = calculate_price($price, $is_sales, $sales_price);

				$parse_template = ($products_default_view == "table") ? false : true;
				$data = show_items_properties("products_".$pb_id, $form_id, $item_id, $item_type_id, $item_price, $tax_id, $tax_free, $options_type, $product_params, $parse_template, $price_matrix_list);

				$is_properties  = $data["params"]["is_any"];
				$properties_ids = $data["params"]["ids"];
				$selected_price = $data["params"]["price"];
				$components_price = $data["params"]["components_price"];
				$components_tax_price = $data["params"]["components_tax_price"];
				$components_points_price = $data["params"]["components_points_price"];
				$components_reward_points = $data["params"]["components_reward_points"];
				$components_reward_credits = $data["params"]["components_reward_credits"];
				$json_data = isset($data["json"]) ? $data["json"] : array(); // for compatability with older version
				$json_data["currency"] = $currency;

				$t->set_var("item_id", $item_id);

				if ($friendly_urls && strlen($friendly_url)) {
					$t->set_var("product_details_url", htmlspecialchars($friendly_url.$friendly_extension . $details_query));
				} else {
					$t->set_var("product_details_url", htmlspecialchars($product_link.$item_id));
				}
				$t->set_var("reviews_url", htmlspecialchars($reviews_link.$item_id));
				if (($is_search || $is_manufacturer) && isset($items_categories[$item_id]) && $items_categories[$item_id]) {
					$item_categories  = $items_categories[$item_id];
					$total_categories = sizeof($item_categories);
					$t->set_var("found_categories", "");
					$i = 0;
					$ic_separator = ",";
					foreach ($item_categories AS $ic_id => $ic_data) {
						if ($i == $total_categories - 1)
							$ic_separator = "";
						$t->set_var("ic_id", $ic_id);
						$t->set_var("item_category", $ic_data["name"]);
						$t->set_var("found_in_url", htmlspecialchars($ic_data["url"]));
						$t->set_var("ic_separator", $ic_separator);
						$t->sparse("found_categories", true);
						$i++;
					}
					$t->global_parse("found_in_category", false, false, true);
				} else {
					$t->set_var("found_in_category", "");
				}
				$t->set_var("form_id", $form_id);
				$t->set_var("item_name", $item_name);
				$t->set_var("a_title", htmlspecialchars($a_title));
				$t->set_var("highlights", $highlights);
				$t->set_var("manufacturer_code", htmlspecialchars($manufacturer_code));
				$t->set_var("manufacturer_name", htmlspecialchars($manufacturer_name));
				$t->set_var("total_views", $total_views);
				
				$t->set_var("tax_price", "");
				$t->set_var("tax_sales", "");
				// show item code
				if ($show_item_code && $item_code) {
					$t->set_var("item_code", htmlspecialchars($item_code));
					$t->sparse("item_code_block", false);
				} else {
					$t->set_var("item_code_block", "");
				}
				// show manufacturer code
				if ($show_manufacturer_code && $manufacturer_code) {
					$t->set_var("manufacturer_code", htmlspecialchars($manufacturer_code));
					$t->sparse("manufacturer_code_block", false);
				} else {
					$t->set_var("manufacturer_code_block", "");
					$t->set_var("product_code", "");
				}

				$t->set_var("stock_level", $stock_level);
				if ($stock_level_list) {
					if ($use_stock_level) {
						$t->set_var("sl_style", "");
					} else {
						$t->set_var("sl_style", "display: none;");
					}
					$t->set_var("stock_level", $stock_level);
					$t->sparse("stock_level_block", false);
				} else {
					$t->set_var("stock_level_block", "");
				}

				if (!$use_stock_level || $stock_level > 0) {
					$shipping_time_desc = $in_stock_message;
				} else {
					$shipping_time_desc = $out_stock_message;
				}
				if (strlen($shipping_time_desc)) {
					$t->set_var("shipping_time_desc", get_translation($shipping_time_desc));
					$t->set_var("sm_style", "");
				} else {
					$t->set_var("sm_style", "display: none;");
				}
				$t->sparse("availability", false);

				if ($is_shipping_free && !$shop_hide_free_shipping && !$hide_free_shipping) {
					$t->sparse("shipping_free", false);
				} else {
					$t->set_var("shipping_free", "");
				}

				$product_image = $db->f($image_field);
				$product_image_alt = get_translation($db->f($image_field_alt));
				if ($random_image && $data["random_image_src"]) { 
					$product_image = $data["random_image_src"];
					$product_image_alt = $data["random_image_alt"];
				}
				if (($watermark || $restrict_products_images) && $product_image) {
					$product_image = "image_show.php?item_id=".$item_id."&type=".$image_field_type."&vc=".md5($product_image);
				}
				if (!$product_image) { $product_image = $product_no_image; } 
				if (strlen($product_image)) {
					if (!strlen($product_image_alt)) { $product_image_alt = $item_name; }
					$t->set_var("alt", htmlspecialchars($product_image_alt));
					$t->set_var("src", htmlspecialchars($product_image));
					$t->parse("product_image", false);
				} else {
					$t->set_var("product_image", "");
				}

				// clear and show desc fields
				$desc_block = false;
				$t->set_var("desc_block", "");
				$t->set_var("short_description", "");
				$t->set_var("full_description", "");

				if ($short_description) {
					$desc_block = true;
					$t->set_var("desc_text", $short_description);
					$t->sparse("short_description", false);
				}
				if ($desc_block) {
					$t->sparse("desc_block", false);
				}

				// show/hide 'more' button
				if ($shop_hide_more_list || $hide_more_button) {
					$t->set_var("more_button", "");
				} else {
					$t->sparse("more_button", false);
				}

				if ($weight > 0) {
					if (strpos ($weight, ".") !== false) {
						while (substr($weight, strlen($weight) - 1) == "0")
							$weight = substr($weight, 0, strlen($weight) - 1);
					}
					if (substr($weight, strlen($weight) - 1) == ".")
						$weight = substr($weight, 0, strlen($weight) - 1);
					$t->set_var("weight", $weight . " " . $weight_measure);
					$t->global_parse("weight_block", false, false, true);
				}

				if ($is_compared) {
					$t->global_parse("compare", false, false, true);
				} else {
					$t->set_var("compare", "");
				}
				
				// show products previews
				$previews->item_id = $item_id;
				$previews->showAll("product_previews");

				// show points price
				if ($points_system && $points_price_list) {
					if ($points_price <= 0) {
						$points_price = $item_price * $points_conversion_rate;
					}
					$points_price += $components_points_price;
					$selected_points_price = $selected_price * $points_conversion_rate;
					$product_params["base_points_price"] = $points_price;
					if ($is_points_price) {
						$t->set_var("points_rate", $points_conversion_rate);
						$t->set_var("points_decimals", $points_decimals);
						$t->set_var("points_price", number_format($points_price + $selected_points_price, $points_decimals));
						$t->sparse("points_price_block", false);
					} else {
						$t->set_var("points_price_block", "");
					}
				}

				// show reward points
				if ($points_system && $reward_points_list) {
					$reward_points = calculate_reward_points($reward_type, $reward_amount, $item_price, $buying_price, $points_conversion_rate, $points_decimals);
					$reward_points += $components_reward_points;

					$product_params["reward_type"] = $reward_type;
					$product_params["reward_amount"] = $reward_amount;
					$product_params["base_reward_points"] = $reward_points;
					if ($reward_type) {
						$t->set_var("reward_points", number_format($reward_points, $points_decimals));
						$t->sparse("reward_points_block", false);
					} else {
						$t->set_var("reward_points_block", "");
					}
				}

				// show reward credits
				if ($credit_system && $reward_credits_list && ($reward_credits_users == 0 || ($reward_credits_users == 1 && $user_id))) {
					$reward_credits = calculate_reward_credits($credit_reward_type, $credit_reward_amount, $item_price, $buying_price);
					$reward_credits += $components_reward_credits;

					$product_params["base_reward_credits"] = $reward_credits;
					if ($credit_reward_type) {
						$t->set_var("reward_credits", currency_format($reward_credits));
						$t->sparse("reward_credits_block", false);
					} else {
						$t->set_var("reward_credits_block", "");
					}
				}

				$product_params["pe"] = 0;
				if ($display_products != 2 || strlen($user_id))
				{
					$item_qty_control = ($hide_add_button || $shop_hide_add_button) ? "DISABLED" : $quantity_control;
					set_quantity_control($quantity_limit, $stock_level, $item_qty_control, "products_".$pb_id, $form_id, $zero_quantity, $min_quantity, $max_quantity, $quantity_increment);

					$base_price = calculate_price($price, $is_sales, $sales_price);
					$product_params["base_price"] = $base_price;
					if ($is_price_edit) {
						$product_params["pe"] = 1;
						$t->set_var("price_block_class", "price-edit");
						if ($price > 0) {
							$control_price = number_format($price, 2);
						} else {
							$control_price = "";
						}

						$t->set_var("price", $control_price);
						$t->set_var("price_control", "<input name=\"price".$form_id."\" type=\"text\" class=\"price\" value=\"" . $control_price . "\">");
						$t->sparse("price_block", false);
						$t->set_var("sales", "");
						$t->set_var("save", "");
					} elseif ($sales_price != $price && $is_sales) {
						$discount_percent = round($db->f("discount_percent"), 0);
						if (!$discount_percent && $price > 0) {
							$discount_percent = round(($price - $sales_price) / ($price / 100), 0);
						}

						$t->set_var("discount_percent", $discount_percent);
						set_tax_price($form_id, $item_type_id, $price + $selected_price, 1, $sales_price + $selected_price, $tax_id, $tax_free, "price", "sales_price", "tax_sales", true, $components_price, $components_tax_price);

						$t->sparse("price_block", false);
						$t->sparse("sales", false);
						$t->sparse("save", false);
					} else {
						$product_params["pe"] = 0;
						set_tax_price($form_id, $item_type_id, $price + $selected_price, 1, 0, $tax_id, $tax_free, "price", "", "tax_price", true, $components_price, $components_tax_price);

						$t->sparse("price_block", false);
						$t->set_var("sales", "");
						$t->set_var("save", "");
					}

					$internal_buy_link = "";
					$external_buy_link = $db->f("buy_link");
					if (strlen($external_buy_link)) {
						$external_buy_link .= $db->f("affiliate_code");
					} elseif ($is_properties || $quantity_control == "LISTBOX" || $quantity_control == "TEXTBOX" || $is_price_edit) {
						$t->set_var("wishlist_href", "javascript:document.products_" . $pb_id. ".submit();");
					} else {
						$internal_buy_link = $cart_link."cart=ADD&add_id=" . $item_id . "&rp=". urlencode($rp). "#p" . $pb_id;
						$t->set_var("wishlist_href", htmlspecialchars($cart_link."cart=WISHLIST&add_id=" . $item_id . "&rp=". urlencode($rp). "#p" . $pb_id));
					}
					set_buy_button($pb_id, $va_data["products_index"], $internal_buy_link, $external_buy_link);

					$items_in_cart = 0;
					if (($hide_add_limit || $show_in_cart) && is_array($shopping_cart) && count($shopping_cart) > 0) {
						foreach ($shopping_cart as $cart_id => $cart_data) {
							if ($cart_data["ITEM_ID"] == $item_id) {
								$items_in_cart += $cart_data["QUANTITY"];
							}
						}
					}
					if ($hide_add_limit && $max_quantity && $items_in_cart == $max_quantity) {
						// if maximum allowed quantity is already added to cart hide 'add to cart' button
						$hide_add_button = true;
					}

					$t->set_var("in_cart", "");
					if ($show_in_cart) {
						if ($items_in_cart) {
							$t->set_var("hidden_class", "");
						} else {
							$t->set_var("hidden_class", "hidden-block");
						}
						$t->sparse("in_cart", false);
					} 
					$t->set_var("buy_button", "");
					$t->set_var("cart_add_button", "");
					$t->set_var("cart_add_disabled", "");
					$t->set_var("add_button", "");
					$t->set_var("add_button_disabled", "");

					if (!$hide_add_button && !$shop_hide_add_button) {
						if ($use_stock_level && $stock_level < 1 && $disable_out_of_stock) {
							if ($t->block_exists("cart_add_disabled")) {
								$t->sparse("cart_add_disabled", false);
							} else {
								$t->sparse("add_button_disabled", false);
							}
						} else {
							if ($external_buy_link && $t->block_exists("buy_button")) {
								$t->sparse("buy_button", false);
							} else {
								if (($use_stock_level && $stock_level < 1) || $issue_date_ts > $current_ts) {
									$t->set_var("ADD_TO_CART_MSG", va_constant("PRE_ORDER_MSG"));
								} else {
									$t->set_var("ADD_TO_CART_MSG", va_constant("ADD_TO_CART_MSG"));
								}
								if ($t->block_exists("cart_add_button")) {
									$t->sparse("cart_add_button", false);
								} else {
									$t->sparse("add_button", false);
								}
							}
						}
					}

					if ($shop_hide_view_list || $hide_view_button) {
						$t->set_var("view_button", "");
					} else {
						$t->sparse("view_button", false);
					}
					if ($shop_hide_checkout_list || $hide_checkout_button || !is_array($shopping_cart)) {
						$t->set_var("checkout_button", "");
					} else {
						$t->sparse("checkout_button", false);
					}
					if (!$user_id || $external_buy_link || $shop_hide_wishlist_list || $hide_wishlist_button) {
						$t->set_var("wishlist_button", "");
					} else {
						$t->sparse("wishlist_button", false);
					}
				}
				set_product_params($product_params);
				$json_data = array_merge($json_data, $product_params);
				$t->set_var("product_data", htmlspecialchars(json_encode($json_data)));


				if ($reviews_allowed_view == 1 || ($reviews_allowed_view == 2 && strlen($user_id))
					|| $reviews_allowed_post == 1 || ($reviews_allowed_post == 2 && strlen($user_id))) {
					$votes = $db->f("votes");
					$points = $db->f("points");

					$rating_float = $votes ? round($points / $votes, 2) : 0;
					$rating_int = round($rating_float, 0);
					if ($rating_int)
					{
						$rating_alt = $rating_float;
						$rating_image = "rating-" . $rating_int;
					}
					else
					{
						$rating_alt = RATE_IT_BUTTON;
						$rating_image = "not-rated";
					}

					$t->set_var("rating_votes", intval($votes));
					$t->set_var("rating_image", $rating_image);
					$t->set_var("rating_alt", $rating_alt);
					$t->sparse("reviews", false);
				}

				// parse table view data columns
				if ($products_default_view == "table") {
					$columns_category_id = ($products_group_by_cats && !$parent_table_view) ? $item_category_id : $category_id;
					parse_data_cols($table_columns, $columns_category_id, $data);
				} 

				$is_next_record = $db->next_record();

				$column_index = ($item_number % $columns) ? ($item_number % $columns) : $columns;
				$t->set_var("column_class", "col-".$column_index);
				$t->parse("items_cols");
				
				if ($is_next_record) {
					$new_category_id = $db->f("category_id");
				} else {
					$new_category_id = "";
				}

				if ($item_number % $columns == 0) {
					$t->parse("items_rows");
					$t->set_var("items_cols", "");
				}
				if ($is_next_record && $products_group_by_cats) {
					if ($item_category_id != $new_category_id) {
						if ($item_number % $columns != 0) {
							$t->parse("items_rows");
						}

						// parse table view title columns
						if ($products_default_view == "table") {
							$columns_category_id = ($products_group_by_cats && !$parent_table_view) ? $item_category_id : $category_id;
							parse_title_cols($table_columns, $columns_category_id);
						}

						$t->set_var("category_name", $item_category_name);
						$t->set_var("category_short_description", $category_short_description);
						$t->set_var("category_full_description", $category_full_description);
						if(strlen($item_category_desc))	{
							$t->set_var("category_desc", $item_category_desc);
							$t->sparse("items_category_desc", false);
						} else {
							$t->set_var("items_category_desc", "");
						}

						$t->parse("items_category_name", false);
						if ($multi_add) {
							$t->parse("multi_add_button", false);
						} else {
							$t->set_var("multi_add_button", false);
						}
						$t->parse("category_items", true);
						$t->set_var("items_rows", "");
						$t->set_var("items_cols", "");
						$item_number = 0; // start from zero for new category
					}
				}	
			} while ($is_next_record);
	
			if ($item_number % $columns != 0) {
				$t->parse("items_rows");
			}
			if ($products_group_by_cats) {
				if(strlen($item_category_desc))	{
					$t->set_var("category_desc", $item_category_desc);
					$t->sparse("items_category_desc", false);
				} else {
					$t->set_var("items_category_desc", "");
				}
				$t->set_var("category_name", $item_category_name);
				$t->parse("items_category_name", false);
			}

			// parse table view title columns
			if ($products_default_view == "table") {
				$columns_category_id = ($products_group_by_cats && !$parent_table_view) ? $item_category_id : $category_id;
				parse_title_cols($table_columns, $columns_category_id);
			} 
			if ($multi_add) {
				$t->parse("multi_add_button", false);
			} else {
				$t->set_var("multi_add_button", false);
			}
			$t->parse("category_items", true);

			$t->set_var("items_indexes", implode(",", $items_indexes));
			$t->set_var("start_index", $start_index);
			$block_parsed = true;
			$t->set_var("no_items", "");
		}
	} else {
		// show 'no articles' message only if there are no subcategories exists
		$where = " c.parent_category_id=" . $db->tosql($category_id, INTEGER);
		$sub_categories_ids = VA_Categories::find_all_ids($where, VIEW_CATEGORIES_PERM);
		if (count($sub_categories_ids) == 0) {
			$t->set_var("items_rows", "");
			$t->parse("no_items", false);
			$block_parsed = true;
		}
	}


	// show search results information
	if ($is_search) {
		$found_message = str_replace("{found_records}", $total_records, va_constant("FOUND_PRODUCTS_MSG"));
		if ($sq) {
			$found_message = str_replace("{search_string}", htmlspecialchars($sq), $found_message);
		} else {
			$found_message = str_replace("{search_string}", htmlspecialchars($search_string), $found_message);
		}
		$t->set_var("FOUND_PRODUCTS_MSG", $found_message);
		$t->parse("search_results", false);
		$block_parsed = true;
	}

	// check if we need to parse hidden block for wishlist types
	if ($user_id && !$shop_hide_wishlist_list) {
		include_once("./blocks/block_wishlist_types.php");
	}

?>