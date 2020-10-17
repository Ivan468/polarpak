<?php

	$default_title = "{top_category_name} &nbsp; {CATEGORIES_TITLE}";

	include_once("./includes/ads_functions.php");

	$friendly_urls      = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	$columns            = get_setting_value($vars, "ads_categories_cols", 1);
	$categories_type    = get_setting_value($vars, "ads_categories_type");

	$search_string = get_param("search_string");
	$category_id = get_param("category_id");
	if (!$category_id) { $category_id = 0; } 
	$is_search     = strlen($search_string);
  
	$t->set_var("list_href",        "ads.php");
	$t->set_var("details_href",     "ads_details.php");
	$t->set_var("top_category_name", va_message("ADS_TITLE"));

	$list_page = "ads.php";
	$list_url = new VA_URL("ads.php");


	if (($categories_type == 2)||($categories_type == 1)) 
	{
		$html_template = get_setting_value($block, "html_template", "block_categories_catalog.html"); 
	  $t->set_file("block_body", $html_template);
		$t->set_var("catalog_sub",      "");
		$t->set_var("catalog_sub_more", "");
		$t->set_var("catalog_rows",     "");
		$t->set_var("catalog_top",      "");
		$t->set_var("catalog_description", "");
  
		$categories_ids = VA_Ads_Categories::find_all_ids("c.parent_category_id=" . $db->tosql($category_id, INTEGER), VIEW_CATEGORIES_PERM);		
		if (!$categories_ids) return;
		$allowed_categories_ids = VA_Ads_Categories::find_all_ids("c.parent_category_id=" . $db->tosql($category_id, INTEGER), VIEW_CATEGORIES_ITEMS_PERM);
				
		if ($categories_type == 2) {
			$sub_categories_ids = VA_Ads_Categories::find_all_ids("c.parent_category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")", VIEW_CATEGORIES_PERM);
			if (!$sub_categories_ids)
				$categories_type = 1;
		}

		if ($categories_type == 1) {
			$sql  = " SELECT category_id as top_category_id, category_name as top_category_name, friendly_url AS top_friendly_url, ";
			$sql .= " short_description, image_small ";	
			$sql .= " FROM " . $table_prefix . "ads_categories ";
			$sql .= " WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ") ";
			$sql .= " ORDER BY category_order ";
		} else {
			// show categories as catalog
			$allowed_sub_categories_ids = VA_Ads_Categories::find_all_ids("c.parent_category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")", VIEW_CATEGORIES_ITEMS_PERM);			
			$sql  = " SELECT c.category_id as top_category_id,c.category_name as top_category_name, c.friendly_url AS top_friendly_url, c.image_small, ";
			$sql .= " s.category_id as sub_category_id,s.category_name as sub_category_name, s.friendly_url AS sub_friendly_url ";
			$sql .= " FROM (" . $table_prefix . "ads_categories c ";
			$sql .= " LEFT JOIN " . $table_prefix . "ads_categories s ON c.category_id=s.parent_category_id) ";	
			$sql .= " WHERE c.category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . " ) ";
			$sql .= " AND (s.category_id IS NULL OR s.category_id IN (" . $db->tosql($sub_categories_ids, INTEGERS_LIST) . ")) ";
			$sql .= " ORDER BY c.category_order, c.category_id, s.category_order ";
		}
		$db->query($sql);
		if($db->next_record())
		{
			$ads_category_number = 0;
			$is_subcategories = true;
			$shown_sub_categories = intval(get_setting_value($vars, "ads_categories_subs")); 
			$catalog_top_number = 0;
			$catalog_sub_number = 0;
			$column_width = intval(100 / $columns);
			$t->set_var("column_width", $column_width . "%");
			do
			{
				$ads_category_number++;
				$catalog_sub_number++;
				$top_category_id = $db->f("top_category_id");
				$top_category_name = get_translation($db->f("top_category_name"));
				$top_friendly_url = $db->f("top_friendly_url");
				$sub_category_id = $db->f("sub_category_id");
				$sub_category_name = get_translation($db->f("sub_category_name"));
				$sub_friendly_url = $db->f("sub_friendly_url");
				$t->set_var("catalog_top_id", $top_category_id);
				$t->set_var("catalog_top_name", $top_category_name);
  				if ($categories_type == 2){
					$t->set_var("catalog_sub_id",   $sub_category_id);
					$t->set_var("catalog_sub_name", $sub_category_name);
				} else {
	  				if (strlen($db->f("short_description"))) {
						$t->set_var("short_description", get_translation($db->f("short_description")));
						$t->parse("catalog_description", false);
					} else {
						$t->set_var("catalog_description", "");
					}
				}

				$category_image = $db->f("image_small");
				$image_default = ""; // some default image for categories
				$image_small = $db->f("image_small");
				$image_small_alt = get_translation($db->f("image_small_alt"));
				$image_large = $db->f("image_large");
				$image_large_alt = get_translation($db->f("image_large_alt"));

				$top_category_name = $db->f("top_category_name");
				$is_next_record = $db->next_record();

				$is_new_top = ($top_category_id != $db->f("top_category_id"));
  
				if ($categories_type == 2){
					if($shown_sub_categories >= $catalog_sub_number || !$shown_sub_categories)
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

					} else if(($shown_sub_categories + 1) == $catalog_sub_number) {
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
  
				if($is_new_top)
				{
					$catalog_top_number++;

					if ($friendly_urls && $top_friendly_url) {
						$list_url->remove_parameter("category_id");
						$t->set_var("list_url", htmlspecialchars($list_url->get_url($top_friendly_url . $friendly_extension)));
					} else {
						$list_url->add_parameter("category_id", CONSTANT, $top_category_id);
						$t->set_var("list_url", htmlspecialchars($list_url->get_url($list_page)));
					}


					$images_total = 0;
					if ($categories_image == 1 && $image_default) {
						// show default image
						$images_total++;
						$t->set_var("alt", htmlspecialchars($top_category_name));
						$t->set_var("src", htmlspecialchars($image_default));
						$t->parse("image_default_block", false);
					}

					$images_total = 0;
					if ($categories_image == 2 && $image_small) {
						// show small image
						$images_total++;
						if (isset($restrict_categories_images) && $restrict_categories_images) { 
							$image_small = "image_show.php?type=small&art_cat_id=".$top_category_id; 
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
							$image_small = "image_show.php?type=large&art_cat_id=".$top_category_id; 
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

			if($catalog_top_number % $columns != 0)
			{
				$t->parse("catalog_rows");
			}
  
			$block_parsed = true;
		}

	} else { // list type 

		$html_template = get_setting_value($block, "html_template", "block_categories_list.html"); 
	  $t->set_file("block_body", $html_template);
		$t->set_var("nodes",      "");

		$categories_image = get_setting_value($vars, "ads_categories_image");
		$current_category_path = "0";
		if ($categories_type == 4) { // Tree-type structure
			$sql  = " SELECT category_path ";
			$sql .= " FROM " . $table_prefix . "ads_categories ";
			$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$current_category_path  = $db->f("category_path");
				$current_category_path .= $category_id;
			}
			$categories_ids = VA_Ads_Categories::find_all_ids("c.parent_category_id IN (" . $db->tosql($current_category_path, INTEGERS_LIST) . ")", VIEW_CATEGORIES_PERM);
			$allowed_categories_ids = VA_Ads_Categories::find_all_ids("c.parent_category_id IN (" . $db->tosql($current_category_path, INTEGERS_LIST) . ")", VIEW_CATEGORIES_ITEMS_PERM);
		} else {
			$categories_ids = VA_Ads_Categories::find_all_ids("", VIEW_CATEGORIES_PERM);
			$allowed_categories_ids = VA_Ads_Categories::find_all_ids("", VIEW_CATEGORIES_ITEMS_PERM);
		}
		
		if (!$categories_ids) return;
		
		$categories = array();
		$sql  = " SELECT category_id, category_order, category_name, friendly_url, short_description, parent_category_id, image_small ";		
		$sql .= " FROM " . $table_prefix . "ads_categories ";
		$sql .= " WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ") ";
		$sql .= " ORDER BY category_order, category_name ";
		$db->query($sql);
		while ($db->next_record()) {
			$cur_category_id = $db->f("category_id");
			$category_order = $db->f("category_order");
			$category_name = get_translation($db->f("category_name"));
			$a_title = get_translation($db->f("a_title"));
			$friendly_url = $db->f("friendly_url");
			$short_description = get_translation($db->f("short_description"));
			$image_small = $db->f("image_small");
			$image_large = $db->f("image_large");
			$parent_category_id = $db->f("parent_category_id");

			if ($friendly_urls && $friendly_url) {
				$category_url = $friendly_url.$friendly_extension;
			} else {
				$category_url = $list_page."?category_id=".$cur_category_id;
			}

			$categories[$cur_category_id]["parent_id"] = $parent_category_id;
			$categories[$cur_category_id]["title"] = $category_name;
			$categories[$cur_category_id]["a_title"] = $a_title;
			$categories[$cur_category_id]["url"] = $category_url;
			$categories[$cur_category_id]["short_description"] = $short_description;
			$categories[$cur_category_id]["image_small"] = $image_small;
			$categories[$cur_category_id]["image_small_alt"] = "";
			$categories[$cur_category_id]["image_large"] = $image_large;
			$categories[$cur_category_id]["image_large_alt"] = "";
			if (!$allowed_categories_ids || !in_array($cur_category_id, $allowed_categories_ids)) {
				$categories[$cur_category_id]["allowed"] = false;
			} else {
				$categories[$cur_category_id]["allowed"] = true;
			}
			$categories[$parent_category_id]["subs"][$cur_category_id] = $category_order;
		}

		
		if (sizeof($categories) > 0 && isset($categories[0]))
		{
			$category_number = 0;
			$column_width = intval(100 / $columns);
			$t->set_var("column_width", $column_width . "%");

			set_tree($categories, 0, 0, $category_id, $categories_image);

			$block_parsed = true;
		}
	}
