<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_ads.php                                            ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/
                           

	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once($root_folder_path . "includes/shopping_cart.php");
	include_once($root_folder_path . "includes/navigator.php");

	check_admin_security("ads");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_ads.html");

	// set files names
	$t->set_var("admin_ads_href",          "admin_ads.php");
	$t->set_var("admin_layout_page_href",  "admin_layout_page.php");
	$t->set_var("admin_ads_category_href", "admin_ads_category.php");
	$t->set_var("admin_ads_edit_href",     "admin_ads_edit.php");
	$t->set_var("admin_ads_properties_href",   "admin_ads_properties.php");
	$t->set_var("admin_ads_assign_href",       "admin_ads_assign.php");
	$t->set_var("admin_ads_categories_href",   "admin_ads_categories.php");
	$t->set_var("admin_ads_order_href",        "admin_ads_order.php");
	$t->set_var("admin_ads_types_href",        "admin_ads_types.php");
	$t->set_var("admin_ads_features_groups_href", "admin_ads_features_groups.php");
	$t->set_var("admin_ads_features_href",        "admin_ads_features.php");
	$t->set_var("admin_ads_images_href",          "admin_ads_images.php");
	$t->set_var("admin_ads_images_settings_href", "admin_ads_images_settings.php");
	$t->set_var("admin_ads_notify_href",          "admin_ads_notify.php");
	$t->set_var("admin_ads_search_href",          "admin_ads_search.php");
	$t->set_var("admin_ads_request_href",         "admin_ads_request.php");
	$t->set_var("admin_tell_friend_href",         "admin_tell_friend.php");
	$t->set_var("admin_cms_page_layout_href",     "admin_cms_page_layout.php");
	$t->set_var("admin_import_href", "admin_import.php");
	$t->set_var("admin_export_href", "admin_export.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$category_id = get_param("category_id");
	if (!strlen($category_id)) { $category_id = "0"; }
	// get search parameters
	$s = trim(get_param("s"));
	$sc = get_param("sc");
	$aa = get_param("aa");
	$search = get_param("search");

	$search = (strlen($search)) ? true : false;
	if (strlen($sc)) { 
		$category_id = $sc; 
	} else { 
		$sc = $category_id; 
	}
	$sa = "";

	$rp = new VA_URL("admin_ads.php", false);
	$rp->add_parameter("category_id", REQUEST, "category_id");
	$rp->add_parameter("sc", GET, "sc");
	$rp->add_parameter("aa", GET, "aa");
	$rp->add_parameter("page", GET, "page");
	$rp->add_parameter("s", GET, "s");
	$t->set_var("rp_url", urlencode($rp->get_url()));

	if ($category_id) {
		$sql  = " SELECT category_name ";
		$sql .= " FROM " . $table_prefix . "ads_categories ";
		$sql .= " WHERE category_id = " . $db->tosql($category_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$t->set_var("current_category_name", get_translation($db->f("category_name")));
		} else {
			$category_id = 0;
		}
	}
	if (!$category_id) {
		if (strlen($s)) {
			$t->set_var("current_category_name", SEARCH_IN_ALL_MSG);
		} else {
			$t->set_var("current_category_name", TOP_CATEGORY_MSG);
		}
	}


	$t->set_var("parent_category_id", $category_id);
	
	$sql  = " SELECT category_id, category_name ";
	$sql .= " FROM " . $table_prefix . "ads_categories ";
	$sql .= " WHERE parent_category_id = " . $db->tosql($category_id, INTEGER);
	$sql .= " ORDER BY category_order ";
	$db->query($sql);
	if ($db->next_record())
	{
		$t->parse("categories_order_link", false);
		$t->set_var("no_categories", "");
		$t->set_var("no_top_categories", "");
		do
		{
			$t->set_var("category_id", $db->f("category_id"));
			$t->set_var("category_name", htmlspecialchars(get_translation($db->f("category_name"))));
			$t->parse("categories");
		} while ($db->next_record());
		$t->parse("categories_header", false);
	} else {
		$t->set_var("categories", "");
		$t->set_var("categories_order_link", "");
		if ($category_id > 0) {
			$t->parse("no_categories");
		} else {
			$t->parse("no_parent_categories");
		}
	}

	$tree = new VA_Tree("category_id", "category_name", "parent_category_id", $table_prefix . "ads_categories", "");

	// build SQL for WHERE condition
	if ($search) {
		$where  = " WHERE (aa.category_id = " . $db->tosql($category_id, INTEGER);
		$where .= " OR ac.category_path LIKE '" . $db->tosql($tree->get_path($category_id), TEXT, false) . "%')";
	} else {
		$where  = " WHERE aa.category_id = " . $db->tosql($category_id, INTEGER);
	}
	if ($s) {
		$sa = explode(" ", $s);
		for ($si = 0; $si < sizeof($sa); $si++) {
			$where .= " AND a.item_title LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
		}
	}
	if (strlen($aa)) {
		if ($aa == 1) {
			$where .= " AND a.is_approved=1 ";
		} else {
			$where .= " AND a.is_approved<>1 ";
		}
	}

	// count unique ads records
	$total_records = 0;
	if (strtolower($db->DBType) == "mysql") {
		$sql  = " SELECT COUNT(DISTINCT a.item_id) ";
	} else {
		$sql  = " SELECT COUNT(*) ";
	}
	$sql .= " FROM ((" .   $table_prefix . "ads_items a ";
	$sql .= " LEFT JOIN " . $table_prefix . "ads_assigned aa ON a.item_id=aa.item_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "ads_categories ac ON ac.category_id=aa.category_id)";
	$sql .= $where;
	if (strtolower($db->DBType) == "mysql") {
		$db->query($sql);
		$db->next_record();
		$total_records = $db->f(0);
	} else {
		$sql .= " GROUP BY a.item_id";
		$db->query($sql);
		while ($db->next_record()) {
			$total_records++;
		}
	}

	// set up variables for navigator
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_ads.php");
	$records_per_page = 20;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;              
	
	$sql  = " SELECT a.item_id, a.item_title, aa.category_id, a.is_approved, a.is_paid, a.date_start, a.date_end, ";
	$sql .= " a.price, a.quantity, a.publish_price, ";
	$sql .= " a.currency_code, c.exchange_rate, c.symbol_right, c.symbol_left, c.decimals_number, c.decimal_point, c.thousands_separator ";
	$sql .= " FROM (((" .   $table_prefix . "ads_items a ";
	$sql .= " LEFT JOIN " . $table_prefix . "ads_assigned aa ON a.item_id=aa.item_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "ads_categories ac ON ac.category_id=aa.category_id)";
	$sql .= " LEFT JOIN " . $table_prefix . "currencies c ON c.currency_code=a.currency_code)";
	$sql .= $where;
	$sql .= " GROUP BY a.item_id, a.item_title, aa.category_id, a.item_order, a.price, a.quantity, a.is_approved, a.is_paid, a.date_start, a.date_end, a.date_added, a.date_updated, ";
	$sql .= " a.currency_code, a.publish_price, c.exchange_rate, c.symbol_right, c.symbol_left, c.decimals_number, c.decimal_point, c.thousands_separator ";
	$sql .= " ORDER BY a.date_updated DESC ";

	$db->query($sql);
	if ($db->next_record())
	{
		//$t->parse("ads_order_link", false);
		$t->set_var("ads_order_link", "");
		$t->set_var("category_id", $category_id);
		do
		{
			$item_title = get_translation($db->f("item_title"));
			$price = $db->f("price");
			$quantity = $db->f("quantity");

			// get ad currency
			$ad_currency = array();
			$ad_currency_code = $db->f("currency_code");
			$ad_currency["code"] = $db->f("currency_code");
			$ad_currency["rate"] = 1;
			$ad_currency["left"] = $db->f("symbol_left");
			$ad_currency["right"] = $db->f("symbol_right");
			$ad_currency["decimals"] = $db->f("decimals_number");
			$ad_currency["point"] = $db->f("decimal_point");
			$ad_currency["separator"] = $db->f("thousands_separator");
			if (!strlen($ad_currency_code)) {
				// use default currency in case currency wasn't selected for this ad
				$ad_currency = $currency;
			}

			$is_approved = $db->f("is_approved");
			$is_paid = $db->f("is_paid");
			$date_start = $db->f("date_start", DATETIME);
			$date_end = $db->f("date_end", DATETIME);
			$date_start_ts = mktime(0,0,0, $date_start[MONTH], $date_start[DAY], $date_start[YEAR]);
			$date_end_ts = mktime(0,0,0, $date_end[MONTH], $date_end[DAY], $date_end[YEAR]);
			$date_now_ts = va_timestamp();
			if ($is_paid != 1) {
				$status = "<font color=red>".NOT_PAID_MSG."</font>";
			} else if ($is_approved != 1) {
				$status = "<font color=red>".NOT_APPROVED_MSG."</font>";
			} elseif ($date_now_ts >= $date_start_ts && $date_now_ts < $date_end_ts) {
				$status = "<font color=blue>".AD_RUNNING_MSG."</font>";
			} elseif ($date_start_ts == $date_end_ts) {
				$status = "<font color=silver>".AD_CLOSED_MSG."</font>";
			} elseif ($date_now_ts >= $date_end_ts) {
				$status = "<font color=silver>".EXPIRED_MSG."</font>";
			}	elseif ($date_now_ts < $date_start_ts) {
				$status = AD_NOT_STARTED_MSG;
			}

			$t->set_var("item_id", $db->f("item_id"));
			$t->set_var("ad_category_id", $db->f("category_id"));
			if (is_array($sa)) {
				for ($si = 0; $si < sizeof($sa); $si++) {
					$item_title = preg_replace ("/(" . $sa[$si] . ")/i", "<font color=blue><b>\\1</b></font>", $item_title);					
				}
			}
			$t->set_var("item_title", $item_title);
			$t->set_var("price", currency_format($price, $ad_currency));
			if ($quantity < 0) {
				$quantity = "<font color=red>" . $quantity . "</font>";
			}
			$t->set_var("quantity", $quantity);
			$t->set_var("status", $status);

			$t->parse("items_list");
		} while ($db->next_record());
		$t->parse("items_header", false);
	} else {
		$t->set_var("ads_order_link", "");
		$t->set_var("items_list", "");
		if ($category_id && !strlen($s)) {
			$t->parse("no_items", false);
		}
	}

	// set up search form parameters
	$approve_params = 
		array( 
			array("", ALL_ADS_MSG), array(0, NOT_APPROVED_MSG), array(1, IS_APPROVED_MSG)
		);
	set_options($approve_params, $aa, "aa");

	$values_before[] = array("0", SEARCH_IN_ALL_MSG);
	if ($category_id != 0) {
		$values_before[] = array($category_id, SEARCH_IN_CURRENT_MSG);
	}

	$sql  = " SELECT category_id,category_name ";
	$sql .= " FROM " . $table_prefix . "ads_categories WHERE parent_category_id = " . $db->tosql($category_id, INTEGER);
	$sql .= " ORDER BY category_order ";
	$sc_values = get_db_values($sql, $values_before);

	$t->set_var("s", htmlspecialchars($s));
	set_options($sc_values, $sc, "sc");

	// show search results message
	if (strlen($s)) {
		$found_ads_message = FOUND_ADS_MSG;
		$found_ads_message = str_replace("{found_records}", $total_records, $found_ads_message);
		$found_ads_message = str_replace("{search_string}", htmlspecialchars($s), $found_ads_message);
		$t->set_var("found_ads_message", $found_ads_message);
		$t->parse("s_d", false);
	}

	if ($category_id || strlen($s)) {
		// show items block only if category was selected or search is used
		$t->parse("items_block", false);
	} 
	if ($category_id || !strlen($s)) {
		// link to change page layout
		$t->parse("custom_layout_link", false);
	} 

	

	$t->pparse("main");

?>