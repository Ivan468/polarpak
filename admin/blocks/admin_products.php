<?php
function admin_products_block($block_name, $params = array()) {
	global $t, $db, $table_prefix, $db_type, $settings;
	global $root_folder_path, $language_code;
	
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	
	$t->set_file("block_body", "admin_block_products.html");

	$t->set_var("admin_items_list_href",       "admin_items_list.php");
	$t->set_var("admin_layout_page_href",      "admin_layout_page.php");
	$t->set_var("admin_reviews_href",          "admin_reviews.php");
	$t->set_var("admin_category_edit_href",    "admin_category_edit.php");
	$t->set_var("admin_product_href",          "admin_product.php");
	$t->set_var("admin_properties_href",       "admin_properties.php");
	$t->set_var("admin_releases_href",         "admin_releases.php");
	$t->set_var("admin_item_related_href",     "admin_item_related.php");
	$t->set_var("admin_item_categories_href",  "admin_item_categories.php");
	$t->set_var("admin_category_items_href",  "admin_category_items.php");
	$t->set_var("admin_categories_order_href", "admin_categories_order.php");
	$t->set_var("admin_products_order_href",   "admin_products_order.php");
	$t->set_var("admin_item_types_href",       "admin_item_types.php");
	$t->set_var("admin_features_groups_href",  "admin_features_groups.php");
	$t->set_var("admin_item_prices_href",      "admin_item_prices.php");
	$t->set_var("admin_item_features_href",    "admin_item_features.php");
	$t->set_var("admin_item_images_href",      "admin_item_images.php");
	$t->set_var("admin_item_accessories_href", "admin_item_accessories.php");
	$t->set_var("admin_export_google_base_href", "admin_export_google_base.php");
	$t->set_var("admin_search_href",             "admin_search.php");
	$t->set_var("admin_tell_friend_href",        "admin_tell_friend.php");
	$t->set_var("admin_products_edit_href",      "admin_products_edit.php");
	
	$permissions = get_permissions();
	$products_categories = get_setting_value($permissions, "products_categories", 0);
	if (!$products_categories) return;
	$products_settings = get_setting_value($permissions, "products_settings", 0);
	$product_types = get_setting_value($permissions, "product_types", 0);
	$manufacturers = get_setting_value($permissions, "manufacturers", 0);
	$products_reviews = get_setting_value($permissions, "products_reviews", 0);
	$shipping_methods = get_setting_value($permissions, "shipping_methods", 0);
	$shipping_times = get_setting_value($permissions, "shipping_times", 0);
	$shipping_rules = get_setting_value($permissions, "shipping_rules", 0);
	$downloadable_products = get_setting_value($permissions, "downloadable_products", 0);
	$coupons = get_setting_value($permissions, "coupons", 0);
	$advanced_search = get_setting_value($permissions, "advanced_search", 0);
	$products_report = get_setting_value($permissions, "products_report", 0);
	$product_prices = get_setting_value($permissions, "product_prices", 0);
	$product_images = get_setting_value($permissions, "product_images", 0);
	$product_properties = get_setting_value($permissions, "product_properties", 0);
	$product_features = get_setting_value($permissions, "product_features", 0);
	$product_related = get_setting_value($permissions, "product_related", 0);
	$product_categories = get_setting_value($permissions, "product_categories", 0);
	$product_accessories = get_setting_value($permissions, "product_accessories", 0);
	$product_releases = get_setting_value($permissions, "product_releases", 0);
	$products_order = get_setting_value($permissions, "products_order", 0);
	$products_export = get_setting_value($permissions, "products_export", 0);
	$products_import = get_setting_value($permissions, "products_import", 0);
	$products_export_google_base = get_setting_value($permissions, "products_export_google_base", 0);
	$features_groups = get_setting_value($permissions, "features_groups", 0);
	$tell_friend = get_setting_value($permissions, "tell_friend", 0);
	$categories_export = get_setting_value($permissions, "categories_export", 0);
	$categories_import = get_setting_value($permissions, "categories_import", 0);
	$categories_order = get_setting_value($permissions, "categories_order", 0);
	$view_categories = get_setting_value($permissions, "view_categories", 0);
	$view_products = get_setting_value($permissions, "view_products", 0);
	$add_categories = get_setting_value($permissions, "add_categories", 0);
	$update_categories = get_setting_value($permissions, "update_categories", 0);
	$remove_categories = get_setting_value($permissions, "remove_categories", 0);
	$add_products = get_setting_value($permissions, "add_products", 0);
	$update_products = get_setting_value($permissions, "update_products", 0);
	$remove_products = get_setting_value($permissions, "remove_products", 0);
	$approve_products = get_setting_value($permissions, "approve_products", 0);
	$view_only_products = !$update_products && $view_products;
	$read_only_products = !$update_products && !$view_products;
	$view_only_categories = !$update_categories && !$remove_categories && $view_categories;
	$read_only_categories = !$update_categories && !$remove_categories && !$view_categories;
	$empty_select_block = !$add_products && !$update_products && !$products_order;
	$empty_export_block = !$products_export && !$products_import && !$products_export_google_base;
	$empty_export_approve_block = $empty_export_block && !$approve_products;
	$empty_first_category_block = !$add_categories && !$categories_order;
	$empty_second_category_block = !$categories_export && !$categories_import;
	
	//BEGIN product privileges changes
	$set_delimiter = false;
	if ($product_prices) {
		$set_delimiter = true;
	}
	if ($product_images && $set_delimiter) {
		$t->set_var("product_images_delimiter", " | ");
	} elseif ($product_images) {
		$set_delimiter = true;
	}
	if ($product_properties && $set_delimiter) {
		$t->set_var("product_properties_delimiter", " | ");
	} elseif ($product_properties) {
		$set_delimiter = true;
	}
	if ($product_features && $set_delimiter) {
		$t->set_var("product_features_delimiter", " | ");
	} elseif ($product_features) {
		$set_delimiter = true;
	}
	if ($product_related && $set_delimiter) {
		$t->set_var("product_related_delimiter", " | ");
	} elseif ($product_related) {
		$set_delimiter = true;
	}
	if ($product_categories && $set_delimiter) {
		$t->set_var("product_categories_delimiter", " | ");
	} elseif ($product_categories) {
		$set_delimiter = true;
	}
	if ($product_accessories && $set_delimiter) {
		$t->set_var("product_accessories_delimiter", " | ");
	} elseif ($product_accessories) {
		$set_delimiter = true;
	}
	if ($product_releases && $set_delimiter) {
		$t->set_var("product_releases_delimiter", " | ");
	}
	//END product privileges changes
	
	$category_id = get_param("category_id");
	$s           = strip_tags(rtrim(trim(get_param("s"))));
	$search      = (strlen($s)) ? true : false;
	
	$t->set_var("s", $s);
	if ($s) {
		$t->parse("s_title", false);
	}
	$t->set_var("product_category_name", va_message("SEARCH_IN_ALL_MSG"));
		
	$where = "";
	if ($s) {
		$sa = explode(" ", $s);
		for($si = 0; $si < sizeof($sa); $si++) {
			$sa[$si] = str_replace("%","\%",$sa[$si]);
			$where .= ($where) ? " AND " : " WHERE ";
			$where .= " (i.item_name LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
			$where .= " OR i.item_code LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%' ";
			if (sizeof($sa) == 1 && preg_match("/^\d+$/", $sa[0])) {
				$where .= " OR i.item_id =" . $db->tosql($sa[0], INTEGER);
			}
			$where .= " OR i.manufacturer_code LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%')";
		}
	}
	
	$total_records = 0;
	$sql  = " SELECT COUNT(*) ";
	$sql .= " FROM " . $table_prefix . "items i ";
	$sql .= $where;
	$db->query($sql);
	$db->next_record();
	$total_records = $db->f(0);
	
	if(!$total_records) return;
	$t->set_var("total_records", $total_records);
	
	$sql  = " SELECT i.item_id, i.item_code, i.manufacturer_code, i.item_name, i.price, i.sales_price, i.is_sales, i.stock_level";
	$sql .= " FROM " . $table_prefix . "items i ";
	$sql .= $where;
	$sql .= " ORDER BY i.item_id ";
	$db->RecordsPerPage = isset($params['records_per_page']) ? $params['records_per_page'] : 5;
	$db->query($sql);
	$item_index = 1;
	while ($db->next_record()) {
		$item_index++;
		$item_id = $db->f("item_id");
		
		$item_code         = $db->f("item_code");
		$manufacturer_code = $db->f("manufacturer_code");
		$item_name         = get_translation($db->f("item_name"));
		
		$price       = $db->f("price");
		$is_sales    = $db->f("is_sales");
		$sales_price = $db->f("sales_price");
		$stock_level = $db->f("stock_level");
		if ($is_sales) {
			$price = $sales_price;
		}

		
		$item_codes  = "";
		if ($item_code && $manufacturer_code) {
			$item_codes = "(" . $item_code . ", " . $manufacturer_code . ")";
		} elseif ($item_code) {
			$item_codes = "(" . $item_code . ")";
		} elseif ($manufacturer_code) {
			$item_codes = "(" . $manufacturer_code . ")";
		}

		$t->set_var("item_id",     $item_id);
		$t->set_var("item_index",  $item_index);
		$t->set_var("category_id", 0);
		$t->set_var("item_code",         htmlspecialchars($item_code));
		$t->set_var("manufacturer_code", htmlspecialchars($manufacturer_code));
		$t->set_var("item_codes",        htmlspecialchars($item_codes));

		$item_name = htmlspecialchars($item_name);
		if (is_array($sa)) {
			for ($si = 0; $si < sizeof($sa); $si++) {
				$regexp = "";
				for ($si = 0; $si < sizeof($sa); $si++) {
					if (strlen($regexp)) $regexp .= "|";
					$regexp .= htmlspecialchars(str_replace(
					array( "/", "|",  "$", "^", "?", ".", "{", "}", "[", "]", "(", ")", "*"),
					array("\/","\|","\\$","\^","\?","\.","\{","\}","\[","\]","\(","\)","\*"),$sa[$si]));
				}
				if (strlen($regexp)) {
					$item_name = preg_replace ("/(" . $regexp . ")/i", "<font color=\"blue\">\\1</font>", $item_name);
				}
			}
		}
		$t->set_var("item_name", $item_name);
		$t->set_var("price", currency_format($price));
		if ($stock_level < 0) {
			$stock_level = "<font color=red>" . $stock_level . "</font>";
		}
		$t->set_var("stock_level", $stock_level);

		// BEGIN product privileges changes
				if ($product_prices) {
					$t->parse("product_prices_priv", false);
				} else {
					$t->set_var("product_prices_priv", "");
				}
				if ($product_images) {
					$t->parse("product_images_priv", false);
				} else {
					$t->set_var("product_images_priv", "");
				}
				if ($product_properties) {
					$t->parse("product_properties_priv", false);
				} else {
					$t->set_var("product_properties_priv", "");
				}
				if ($product_features) {
					$t->parse("product_features_priv", false);
				} else {
					$t->set_var("product_features_priv", "");
				}
				if ($product_related) {
					$t->parse("product_related_priv", false);
				} else {
					$t->set_var("product_related_priv", "");
				}
				if ($product_categories) {
					$t->parse("product_categories_priv", false);
				} else {
					$t->set_var("product_categories_priv", "");
				}
				if ($product_accessories) {
					$t->parse("product_accessories_priv", false);
				} else {
					$t->set_var("product_accessories_priv", "");
				}
				if ($product_releases) {
					$t->parse("product_releases_priv", false);
				} else {
					$t->set_var("product_releases_priv", "");
				}
				if ($read_only_products) {
					$t->parse("read_only_products_priv", false);
					$t->set_var("update_products_priv", "");
				} elseif ($view_only_products) {
					$t->set_var("product_edit_msg", VIEW_MSG);
					$t->parse("update_products_priv", false);
					$t->set_var("read_only_products_priv", "");
				} else {
					$t->set_var("product_edit_msg", EDIT_MSG);
					$t->parse("update_products_priv", false);
					$t->set_var("read_only_products_priv", "");
				}
				
				$row_style = ($item_index % 2 == 0) ? "row1" : "row2";
				$t->set_var("row_style", $row_style);
		// END product privileges changes
		$t->parse("items_list");
	}
	$t->parse("block_body", false);
	$t->parse_to("block_body", $block_name, true);
	
	return $total_records;
}
