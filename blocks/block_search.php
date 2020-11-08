<?php

	global $cms_page_code;
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./includes/products_functions.php");

	$default_title = "{search_name} &nbsp; {SEARCH_TITLE}";

	$tag_name = get_setting_value($vars, "tag_name");
	$block_type = get_setting_value($vars, "block_type");
	$template_type = get_setting_value($vars, "template_type");

	if ($block_type != "bar" && $block_type != "header" && $template_type != "built-in") {
		if ($template_type == "default") {
			$html_template = "block_search.html"; 
		} else {
			$html_template = get_setting_value($block, "html_template", "block_search.html"); 
		}
		if ($block_type == "sub-block") {
		  $t->set_file($vars["tag_name"], $html_template);
		} else {
		  $t->set_file("block_body", $html_template);
		}
	}

	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	$ajax = get_param("ajax");
	$param_pb_id = get_param("pb_id");
	if ($ajax && $pb_id == $param_pb_id) {
		$data = array();
		$sw = get_param("sw");
		$data["sw"] = $sw;

		if ($sw) {
			$sql  = " SELECT * FROM ".$table_prefix."items "; 
			$sw = trim(preg_replace("/\s+/", " ", $sw));
			$search_values = explode(" ", $sw);

			$sql_params = array();
			$sql_params["select"] = " * ";
			for ($si = 0; $si < sizeof($search_values); $si++) {
				$sql_params["where"][] = " (i.item_name LIKE '%" . $db->tosql($search_values[$si], TEXT, false) . "%') ";
			}
			$sql_params["group"] = " i.item_id ";

			$sql = VA_Products::sql($sql_params, VIEW_ITEMS_PERM);
			$db->RecordsPerPage = 10;
			$db->query($sql);
			if ($db->next_record()) {
				do {
					$item_id = $db->f("item_id");
					$item_name = $db->f("item_name");
					$tiny_image = $db->f("tiny_image");
					if (!$tiny_image) { $tiny_image = $db->f("small_image"); }
					$item_desc = $db->f("short_description");
					if (strlen($item_desc) > 60) {
						$item_desc = substr($item_desc, 0, 60)."...";
					}
					$price = $db->f("price");
					$is_sales = $db->f("is_sales");
					$sales_price = $db->f("sales_price");
					$properties_price = $db->f("properties_price");
					$friendly_url = $db->f("friendly_url");
					if ($is_sales && $sales_price) {
						$price = $sales_price;
					}
					$price += $properties_price;
					if ($friendly_urls && $friendly_url) {
						$item_url = $friendly_url.$friendly_extension;
					} else {
						$item_url = get_custom_friendly_url("product_details.php") . "?item_id=" . $item_id; 
					}

					$t->set_var("item_id", $item_id);
					$t->set_var("tiny_image", $tiny_image);
					$t->set_var("item_name", $item_name);
					$t->set_var("item_desc", strip_tags($item_desc));
					$t->set_var("item_url", htmlspecialchars($item_url));

					$t->set_var("item_price", currency_format($price));
					$t->parse("found_products", true);			
				} while ($db->next_record());
	  
				$data["products"] = $t->get_var("found_products");;
			}
		}

		echo json_encode($data);
		exit;
	}

	if (!$ajax) {
		set_script_tag("js/ajax.js");
	}

	$t->set_var("search_href", get_custom_friendly_url("products_search.php"));
	$t->set_var("products_search_href", get_custom_friendly_url("products_search.php"));
	$t->set_var("search_name", PRODUCTS_TITLE);
	// clear tags before parse
	$t->set_var("search_categories", "");
	$t->set_var("category_id", "");
	$t->set_var("no_search_categories", "");
	$t->set_var("advanced_search", "");
	
	$query_string = transfer_params("", false);

	$category_id = 0; $sq = "";
	// check category_id and search parameter only for product pages
	if ($cms_page_code == "products_list" || $cms_page_code == "product_details" 
		|| $cms_page_code == "product_options" || $cms_page_code == "product_reviews" 
		|| $cms_page_code == "products_search_results") {
		$category_id = get_param("category_id");
		$sq = trim(get_param("sq"));
		if (strlen($sq)) {
			$sq = trim(get_param("search_string"));
		}
	}

	$t->set_var("advanced_search_href", htmlspecialchars(get_custom_friendly_url("search.php").$query_string));
	$t->global_parse("advanced_search", false, false, true);
	
	$t->set_var("sq", htmlspecialchars($sq));
	$t->set_var("search_string", htmlspecialchars($sq));

	if ($t->block_exists("category_id")) {	
		$search_categories = array();
		$search_categories[] = array(0, SEARCH_IN_ALL_MSG);  
		if($category_id != 0) {
			$search_categories[] = array($category_id, SEARCH_IN_CURRENT_MSG);
		}

		if (!strlen($category_id)) { $category_id = 0; }
		$categories_ids = VA_Categories::find_all_ids("c.parent_category_id = " . $db->tosql($category_id, INTEGER), VIEW_CATEGORIES_ITEMS_PERM);
		if ($categories_ids) {
			$sql  = " SELECT category_id, category_name ";
			$sql .= " FROM " . $table_prefix . "categories ";
			$sql .= " WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ") ";
			$sql .= " ORDER BY category_order ";
			$db->query($sql);
			while($db->next_record()) {
				$list_category_id = $db->f("category_id");
				$list_category_name = strip_tags(get_translation($db->f("category_name")));
				$search_categories[] = array($list_category_id, $list_category_name);
			}
		}
		// set up search form parameters
		$t->set_var("no_search_categories", "");
		if (sizeof($search_categories) > 1) {
			set_options($search_categories, $category_id, "category_id");
			$t->global_parse("search_categories", false, false, true);
		} else {
			$t->set_var("search_categories", "");
		}

		$t->set_var("current_category_id", htmlspecialchars($category_id));	
	} else {
		$t->set_var("current_category_id", "");
	}

	
	$block_parsed = true;

