<?php

	if (isset($sub_block) && $sub_block) {
		$file_block = $sub_block;
	} else {
		$file_block = "block_body";
	}

	include_once("./includes/products_functions.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");
	include_once("./messages/" . $language_code . "/download_messages.php");

	header("Content-Type: text/html; charset=" . CHARSET);

	$default_title = "{CATEGORIES_TITLE}";

	$user_id = get_session("session_user_id");		
	$user_info = get_session("session_user_info");
	$user_type_id = get_setting_value($user_info, "user_type_id", "");

	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	$columns = get_setting_value($vars, "categories_columns", 1);
	$categories_type = get_setting_value($vars, "categories_type", 1);
	$desc_type = get_setting_value($vars, "desc_type", 0);
	$js_over_stop = get_setting_value($vars, "js_over_stop", "");
	$t->set_var("columns_class", "cols-".$columns);
	$t->set_var("js_over_stop", htmlspecialchars($js_over_stop));

	if ($categories_type == 7)  {
		$t->set_var("js_type", "popup");
	} else {
		$t->set_var("js_type", "");
	}

	$category_id = 0;
	// check category_id parameter only for product pages
	if ($cms_page_code == "products_list" || $cms_page_code == "product_details" 
		|| $cms_page_code == "product_options" || $cms_page_code == "product_reviews") {
		$category_id = get_param("category_id");
		$search_category_id = get_param("search_category_id");
		$search_string = get_param("search_string");
		$is_search = strlen($search_string);
		if ($is_search && $search_category_id) { $category_id = $search_category_id; }
	}


	$item_id = get_param("item_id"); 
	if (!strlen($category_id)) {
		if (strlen($item_id)) {
			$category_id = get_db_value("SELECT category_id FROM " . $table_prefix . "items_categories where item_id=".$db->tosql($item_id, INTEGER));
		} else {
			$category_id = "0";
		}
	}
	$category_id = intval($category_id);
	$ajax = get_param("ajax");
	if (!isset($current_category)) { $current_category = PRODUCTS_TITLE; }

	$t->set_var("products_href",    get_custom_friendly_url("products_list.php"));
	$t->set_var("list_href",        get_custom_friendly_url("products_list.php"));
	$t->set_var("details_href",     get_custom_friendly_url("product_details.php"));
	$t->set_var("current_category", htmlspecialchars($current_category));
	$t->set_var("top_category_name",PRODUCTS_TITLE);
	$t->set_var("category_rss", "");
	if (!$ajax) {
		set_script_tag("js/ajax.js");
		set_script_tag("js/blocks.js");
	}

	$list_page = get_custom_friendly_url("products_list.php");
	$list_url = new VA_URL($list_page);

	$categories_image = get_setting_value($vars, "categories_image");
	$image_default = "images/category_image.gif";
	
	if ($categories_type == 1 || $categories_type == 2) {
		if ($category_id && isset($current_category) && $current_category) { 
			$default_title = $current_category; 
		}
		$html_template = get_setting_value($block, "html_template", "block_categories_catalog.html"); 
		$cms_css_class = "bk-categories-catalog";
	  $t->set_file($file_block, $html_template);
		$t->set_var("catalog_rows",        "");
		$t->set_var("catalog_top",         "");
		$t->set_var("category_title_block","");
		$t->set_var("category_rss ",       "");
		$t->set_var("image_small_block",   "");
		$t->set_var("image_large_block",   "");
		$t->set_var("image_before_small",   "");
		$t->set_var("image_before_large",   "");
		$t->set_var("image_after_small",   "");
		$t->set_var("image_after_large",   "");
		$t->set_var("short_description","");
		$t->set_var("full_description", "");
		$t->set_var("catalog_sub",         "");
		$t->set_var("sub_categories",      "");
		$t->set_var("sub_categories_more", "");

		$categories_ids = VA_Categories::find_all_ids("c.parent_category_id=" . $db->tosql($category_id, INTEGER), VIEW_CATEGORIES_PERM);
		if (!$categories_ids) return;
		$allowed_categories_ids = VA_Categories::find_all_ids("c.parent_category_id=" . $db->tosql($category_id, INTEGER), VIEW_CATEGORIES_ITEMS_PERM);
				
		if ($categories_type == 2) {
			$sub_categories_ids = VA_Categories::find_all_ids("c.parent_category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")", VIEW_CATEGORIES_PERM);
			if (!$sub_categories_ids)
				$categories_type = 1;
		}

		if ($categories_type == 1) {
			$sql  = " SELECT category_id AS top_category_id, category_name AS top_category_name, user_list_class AS top_list_class, ";
			$sql .= " a_title AS top_a_title, ";
			$sql .= " short_description, full_description, friendly_url AS top_friendly_url, ";
			$sql .= " image, image_alt, image_large, image_large_alt ";
			$sql .= " FROM " . $table_prefix . "categories ";
			$sql .= " WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")";	
			$sql .= " ORDER BY category_order, category_name ";
		} else {
			// show categories as catalog
			$allowed_sub_categories_ids = VA_Categories::find_all_ids("c.parent_category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")", VIEW_CATEGORIES_ITEMS_PERM);						
			$sql  = " SELECT c.category_id AS top_category_id,c.category_name AS top_category_name, c.user_list_class AS top_list_class, ";
			$sql .= " c.friendly_url AS top_friendly_url, c.a_title AS top_a_title, ";
			$sql .= " c.image, c.image_alt, c.image_large, c.image_large_alt, ";
			$sql .= " c.short_description, c.full_description, ";
			$sql .= " s.category_id AS sub_category_id,s.category_name AS sub_category_name, s.user_list_class AS sub_list_class, ";
			$sql .= " s.friendly_url AS sub_friendly_url, s.a_title AS sub_a_title ";
			$sql .= " FROM (" . $table_prefix . "categories c ";
			$sql .= " LEFT JOIN " . $table_prefix . "categories s ";			
			if ($sub_categories_ids) {
				$sql .= " ON (c.category_id=s.parent_category_id ";			
				$sql .= " AND s.category_id IN (" . $db->tosql($sub_categories_ids, INTEGERS_LIST) . ")))";
			} else {
				$sql .= " ON c.category_id=s.parent_category_id) ";			
			}
			$sql .= " WHERE c.category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")";
			$sql .= " ORDER BY c.category_order, c.category_name, c.category_id, s.category_order, s.category_name ";
		}
		$db->query($sql);
		if ($db->next_record()) {
			$category_number = 0;
			$is_subcategories = true;
			$shown_sub_categories = get_setting_value($vars, "categories_subs");
			$catalog_top_number = 0;
			$catalog_sub_number = 0;
			$column_width = intval(100 / $columns);
			$t->set_var("column_width", $column_width . "%");
			$t->set_var("col_style", "width: ".$column_width . "%;");

			do {
				$category_number++;
				$top_category_id = $db->f("top_category_id");
				$top_category_name  =  get_translation($db->f("top_category_name"));
				$top_list_class =  $db->f("top_list_class");
				$top_a_title =  get_translation($db->f("top_a_title"));
				$top_friendly_url = $db->f("top_friendly_url");
				$sub_category_id = $db->f("sub_category_id");
				$sub_category_name = get_translation($db->f("sub_category_name"));
				$sub_list_class =  $db->f("sub_list_class");
				$sub_a_title =  get_translation($db->f("sub_a_title"));
				$sub_friendly_url = $db->f("sub_friendly_url");
				if ($sub_category_id) { $catalog_sub_number++; }

				$image_small = $db->f("image");
				$image_small_alt = get_translation($db->f("image_alt"));
				$image_large = $db->f("image_large");
				$image_large_alt = get_translation($db->f("image_large_alt"));

				$short_description = get_translation($db->f("short_description"));
				$full_description = get_translation($db->f("full_description"));
	
				$t->set_var("catalog_top_id", $top_category_id);
				$t->set_var("catalog_top_name", ($top_category_name));
				$t->set_var("catalog_top_class", htmlspecialchars($top_list_class));
				$t->set_var("top_a_title", htmlspecialchars($top_a_title));
				$t->set_var("short_description", "");
				$t->set_var("full_description", "");

				if ($desc_type == 1 && $short_description) {
					$t->set_var("desc_text", $short_description);
					$t->parse("short_description", false);
				} else if ($desc_type == 2 && $full_description) {
					$t->set_var("desc_text", $full_description);
					$t->parse("full_description", false);
				}

				if ($categories_type == 2){
					$t->set_var("catalog_sub_id",   $sub_category_id);
					$t->set_var("catalog_sub_name", ($sub_category_name));
					$t->set_var("catalog_sub_class", htmlspecialchars($sub_list_class));
					$t->set_var("sub_a_title", htmlspecialchars($sub_a_title));
				}
	
				$is_next_record = $db->next_record();
				$is_new_top = ($top_category_id != $db->f("top_category_id"));
	
				if ($categories_type == 2){
					if (intval($shown_sub_categories) >= $catalog_sub_number || $shown_sub_categories == 0)
					{
						if ($sub_category_id && (!$allowed_sub_categories_ids || !in_array($sub_category_id, $allowed_sub_categories_ids))) {
							$t->set_var("restricted_class", " restricted ");
						} else {
							$t->set_var("restricted_class", "");
						}

						if ($friendly_urls && $sub_friendly_url) {
							$list_url->remove_parameter("category_id");
							$t->set_var("sub_url", htmlspecialchars($list_url->get_url($sub_friendly_url. $friendly_extension)));
						} else {
							$list_url->add_parameter("category_id", CONSTANT, $sub_category_id);
							$t->set_var("sub_url", htmlspecialchars($list_url->get_url($list_page)));
						}
						$t->parse("sub_categories", true);
					} elseif (($shown_sub_categories + 1) == $catalog_sub_number) {
						if ($friendly_urls && $top_friendly_url) {
							$list_url->remove_parameter("category_id");
							$t->set_var("list_url", htmlspecialchars($list_url->get_url($top_friendly_url . $friendly_extension)));
						} else {
							$list_url->add_parameter("category_id", CONSTANT, $top_category_id);
							$t->set_var("list_url", htmlspecialchars($list_url->get_url($list_page)));
						}
						$t->parse("sub_categories_more", false);
					}
				}
	
				if ($is_new_top) {
					$catalog_top_number++;
	
					if ($friendly_urls && $top_friendly_url) {
						$list_url->remove_parameter("category_id");
						$t->set_var("list_url", htmlspecialchars($list_url->get_url($top_friendly_url . $friendly_extension)));
					} else {
						$list_url->add_parameter("category_id", CONSTANT, $top_category_id);
						$t->set_var("list_url", htmlspecialchars($list_url->get_url($list_page)));
					}

					$images_total = 0;
					if ($categories_image == 2 && $image_small) {
						// show small image
						$images_total++;
						if (isset($restrict_categories_images) && $restrict_categories_images) { 
							$image_small = "image_show.php?type=small&category_id=".$top_category_id; 
						}
						if (!strlen($image_small_alt)) { $image_small_alt = $top_category_name; }
						$t->set_var("alt", htmlspecialchars($image_small_alt));
						$t->set_var("src", htmlspecialchars($image_small));
						$t->sparse("image_before_small", false);
						$t->sparse("image_after_small", false);
						$t->sparse("image_small_block", false);
					} 
					if ($categories_image == 3 && $image_large) {
						// show large image
						$images_total++;
						if (isset($restrict_categories_images) && $restrict_categories_images) { 
							$image_small = "image_show.php?type=large&category_id=".$top_category_id; 
						}
						if (!strlen($image_large_alt)) { $image_large_alt = $top_category_name; }
						$t->set_var("alt", htmlspecialchars($image_large_alt));
						$t->set_var("src", htmlspecialchars($image_large));
						$t->sparse("image_before_large", false);
						$t->sparse("image_after_large", false);
						$t->sparse("image_large_block", false);
					} 	
	
					if ($images_total) {
						$t->sparse("image_before", false);
						$t->sparse("image_after", false);
						$t->sparse("image_block", false);
					} else {
						$t->set_var("image_before", "");
						$t->set_var("image_after", "");
						$t->set_var("image_block", "");
					}
	
					if (!$allowed_categories_ids || !in_array($top_category_id, $allowed_categories_ids)) {
						$t->set_var("restricted_class", " restricted ");
					} else {
						$t->set_var("restricted_class", "");
					}
					$t->parse("top_title_block", false);
						
					if ($catalog_sub_number) {
						// parse catalog sub categories block if they exists
						$t->parse("catalog_sub", false);
					}

					$column_index = ($catalog_top_number % $columns) ? ($catalog_top_number % $columns) : $columns;
					$t->set_var("column_class", "col-".$column_index);
					$t->parse("catalog_top");
					$t->set_var("catalog_sub", "");
					$t->set_var("sub_categories", "");
					$t->set_var("sub_categories_more", "");
					if ($catalog_top_number % $columns == 0) {
						$t->parse("catalog_rows");
						$t->set_var("catalog_top", "");
					}
					$catalog_sub_number = 0;
				}
	
			} while ($is_next_record);
	
			if ($catalog_top_number % $columns != 0) {
				$t->parse("catalog_rows");
			}
	
			$block_parsed = true;
		}
	} else if ($categories_type == 5) {
		// chained list type
		$is_ajax = get_param("is_ajax");
		$level = get_param("level");
		$pcategory = get_param("pcategory");
		if (!$pcategory) { $pcategory = 0; }

		$categories = array();

		$categories_ids = VA_Categories::find_all_ids("c.parent_category_id=" . $db->tosql($pcategory, INTEGER), VIEW_CATEGORIES_PERM);
		if ($categories_ids) {
			$sql  = " SELECT category_id, category_name FROM " . $table_prefix . "categories ";
			$sql .= " WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")";	
			$sql .= " ORDER BY category_order, category_name ";
			$db->query($sql);
			if ($db->next_record())	{
				do {
					$category_id = $db->f("category_id");
					$categories[$category_id] = get_translation($db->f("category_name"));
				} while ($db->next_record());
			}
		}
		
		if ($is_ajax) {
			// json_encode for PHP4
			if (sizeof($categories) > 0) {
				$json_categories = array();
				foreach($categories as $category_id => $category_name) {
					$json_categories[] = array("id" => $category_id, "name" => $category_name);
				}
				echo json_encode($json_categories);
			}
			exit;
		} else {
		
			$html_template = get_setting_value($block, "html_template", "block_categories_chained_menu.html"); 
			$cms_css_class = "bk-categories-chained-menu";
		  $t->set_file($file_block, $html_template);
			$t->set_var("products_href",  get_custom_friendly_url("products_list.php"));
		
			foreach($categories as $category_id => $category_name) {
				$t->set_var("category_id", $category_id);	
				$t->set_var("category_name", ($category_name));
				$t->parse("category_option");
			}
  
			$block_parsed = true;
		}
	} else {// list type
		$html_template = get_setting_value($block, "html_template", "block_categories_list.html"); 
		$cms_css_class = "bk-categories-list";
	  $t->set_file($file_block, $html_template);
		$t->set_var("nodes", "");
		$ajax = get_param("ajax");

		// check active categories
		$active_category_path = "0";
		$sql  = " SELECT category_path ";
		$sql .= " FROM " . $table_prefix . "categories ";
		$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$active_category_path  = $db->f("category_path");
			$active_category_path .= $category_id;
		}

		if ($ajax) { // Ajax call for tree branch
			$category_id = get_param("category_id");
			if (!$category_id) { $category_id = get_param("id"); }
			// check level number
			$start_level = 1; // use 1 by default
			$sql  = " SELECT category_path ";
			$sql .= " FROM " . $table_prefix . "categories ";
			$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$category_path = trim($db->f("category_path"), ",");
				$ids = explode(",", $category_path);
				$start_level = sizeof($ids);
			}
			$categories_ids = VA_Categories::find_all_ids("c.parent_category_id=" . $db->tosql($category_id, INTEGER), VIEW_CATEGORIES_PERM);
			$allowed_categories_ids = VA_Categories::find_all_ids("c.parent_category_id=" . $db->tosql($category_id, INTEGER), VIEW_CATEGORIES_ITEMS_PERM);
		} else if ($categories_type == 4 || $categories_type == 6) { // Tree-type structure
			$categories_ids = VA_Categories::find_all_ids("c.parent_category_id IN (" . $db->tosql($active_category_path, INTEGERS_LIST) . ")", VIEW_CATEGORIES_PERM);
			$allowed_categories_ids = VA_Categories::find_all_ids("c.parent_category_id IN (" . $db->tosql($active_category_path, INTEGERS_LIST) . ")", VIEW_CATEGORIES_ITEMS_PERM);
		} else {
			$categories_ids         = VA_Categories::find_all_ids("", VIEW_CATEGORIES_PERM);
			$allowed_categories_ids = VA_Categories::find_all_ids("", VIEW_CATEGORIES_ITEMS_PERM);
		}

		if (!$categories_ids) return;
		
		$categories = array();
		$sql  = " SELECT c.category_id, c.category_order, c.category_name, c.user_list_class, c.a_title, c.friendly_url, ";
		$sql .= " c.short_description, c.image, c.image_alt, c.image_large, c.image_large_alt, c.parent_category_id ";		
		if ($categories_type == 6) { // Tree-type structure
			$sql .= ", sc.subs_number ";
		}
		$sql .= " FROM " . $table_prefix . "categories c ";
		if ($categories_type == 6) { // Tree-type structure
			$sql .= " LEFT JOIN (";
			$sql .= " SELECT parent_category_id, COUNT(*) AS subs_number ";
			$sql .= " FROM " . $table_prefix . "categories ";
			$sql .= " WHERE parent_category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ") ";
			$sql .= " GROUP BY parent_category_id ";
			$sql .= " ) sc ON sc.parent_category_id=c.category_id ";
		}
		$sql .= " WHERE c.category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ") ";
		$sql .= " ORDER BY c.category_order, c.category_name ";
		$db->query($sql);
		while ($db->next_record()) {
			$cur_category_id = $db->f("category_id");
			$category_order = $db->f("category_order");
			$category_name = get_translation($db->f("category_name"));
			$list_class = $db->f("user_list_class");
			$a_title = get_translation($db->f("a_title"));
			$friendly_url = $db->f("friendly_url");
			$short_description = get_translation($db->f("short_description"));
			$subs_number = $db->f("subs_number");
			$image_small = $db->f("image");
			$image_small_alt = get_translation($db->f("image_alt"));

			if ($friendly_urls && $friendly_url) {
				$category_url = $friendly_url.$friendly_extension;
			} else {
				$category_url = $list_page."?category_id=".$cur_category_id;
			}

			$parent_category_id = $db->f("parent_category_id");
			$categories[$cur_category_id]["parent_id"] = $parent_category_id;
			$categories[$cur_category_id]["title"] = $category_name;
			$categories[$cur_category_id]["class"] = $list_class;
			$categories[$cur_category_id]["a_title"] = $a_title;
			$categories[$cur_category_id]["url"] = $category_url;
			$categories[$cur_category_id]["short_description"] = $short_description;
			$categories[$cur_category_id]["subs_number"] = $subs_number;

			$categories[$cur_category_id]["image_small"] = $image_small;
			$categories[$cur_category_id]["image_small_alt"] = $image_small_alt;
			$categories[$cur_category_id]["image_large"] = $db->f("image_large");
			$categories[$cur_category_id]["image_large_alt"] = get_translation($db->f("image_large_alt"));

			if (!$allowed_categories_ids || !in_array($cur_category_id, $allowed_categories_ids)) {
				$categories[$cur_category_id]["allowed"] = false;
			} else {
				$categories[$cur_category_id]["allowed"] = true;
			}
			$categories[$parent_category_id]["subs"][$cur_category_id] = $category_order;
		}
                                    
		if ($ajax) { // Ajax call for tree branch
			$top_id = $category_id;
			set_tree($categories, $category_id, $start_level, explode(",", $active_category_path), $categories_image, $categories_type);
			$t->set_var("category_id", $top_id);
			$t->set_var("subnodes", $t->get_var("subnodes_" .$start_level));
			$t->pparse("subnodes_block");
			exit;
		} else if (sizeof($categories) > 0 && isset($categories[0])) {
			$category_number = 0;
			$column_width = intval(100 / $columns);
			$t->set_var("column_width", $column_width . "%");

			set_tree($categories, 0, 0, explode(",", $active_category_path), $categories_image, $categories_type);

			$block_parsed = true;
		}
	}


?>
