<?php

	include_once("./includes/articles_functions.php");

	$default_title = "{top_category_name}";

	// check if top_id is a parent of category_id parameter 
	$top_id = $block["block_key"];
	$category_id = get_param("category_id");
	$articles_category_id = ""; $articles_top_name = ""; $active_category_path = "0,".$top_id;
	if (($cms_page_code == "articles_list" || $cms_page_code == "article_details" || $cms_page_code == "article_reviews") 
		&& $category_id && $top_id != $category_id) {
		$sql  = " SELECT category_path FROM " . $table_prefix . "articles_categories ";
		$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
		$category_path = get_db_value($sql);
		$category_ids = explode(",", $category_path);
		if (in_array($top_id, $category_ids)) {
			$articles_category_id = $category_id;
			$articles_top_name = $top_name;
			$active_category_path = $category_path.$category_id;
		}
	}

	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	$columns = get_setting_value($vars, "articles_categories_cols", 1);
	$categories_type = get_setting_value($vars, "articles_categories_type");
	$categories_image = get_setting_value($vars, "articles_categories_image");
	$desc_type = get_setting_value($vars, "desc_type", 1);
	$t->set_var("columns_class", "cols-".$columns);

	if (!strlen($articles_top_name) && VA_Articles_Categories::check_permissions($top_id, VIEW_CATEGORIES_PERM)) {
		$sql  = " SELECT category_name, article_list_fields, articles_order_column, articles_order_direction ";
		$sql .= " FROM " . $table_prefix . "articles_categories ";				
		$sql .= " WHERE category_id=" . $db->tosql($top_id, INTEGER);			
		$db->query($sql);
		if ($db->next_record()) {
			$articles_top_name = get_translation($db->f("category_name"));
		} else {
			return false;
		}
	}

	$t->set_var("articles_href","articles.php");
	$t->set_var("list_href",    "articles.php");
	$t->set_var("details_href", "article.php");
	$t->set_var("rss_href",     "articles_rss.php");
	$t->set_var("top_category_name",$articles_top_name);

	$list_page = "articles.php";
	$list_url = new VA_URL("articles.php");

	if (($categories_type == 2)||($categories_type == 1)) {
		$html_template = get_setting_value($block, "html_template", "block_categories_catalog.html"); 
	  $t->set_file("block_body", $html_template);
		$t->set_var("catalog_rows",        "");
		$t->set_var("catalog_top",         "");
		$t->set_var("category_title_block","");
		$t->set_var("category_rss ",       "");
		$t->set_var("image_small_block",   "");
		$t->set_var("image_large_block",   "");
		$t->set_var("short_description","");
		$t->set_var("full_description", "");
		$t->set_var("catalog_sub",         "");
		$t->set_var("sub_categories",      "");
		$t->set_var("sub_categories_more", "");
		
		if ($articles_category_id > 0) {
			$where = " c.parent_category_id = " . $db->tosql($articles_category_id, INTEGER);
		} else {
			$where = " c.parent_category_id = " . $db->tosql($top_id, INTEGER);
		}
			
		$categories_ids = VA_Articles_Categories::find_all_ids($where, VIEW_CATEGORIES_PERM);
		if (!$categories_ids) return;
		$allowed_categories_ids = VA_Articles_Categories::find_all_ids($where, VIEW_CATEGORIES_ITEMS_PERM);
		
		if ($categories_type == 2) {
			$sub_categories_ids = VA_Articles_Categories::find_all_ids("c.parent_category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")", VIEW_CATEGORIES_PERM);
			if (!$sub_categories_ids)
				$categories_type = 1;
		}
		
		if ($categories_type == 1) {
			$sql  = " SELECT category_id AS top_category_id, category_name AS top_category_name, friendly_url AS top_friendly_url, ";
			$sql .= " short_description, image_small, image_small_alt, image_large, image_large_alt, is_rss ";
			$sql .= " FROM " . $table_prefix . "articles_categories";
			$sql .= " WHERE category_id IN ( " . $db->tosql($categories_ids, INTEGERS_LIST) . ")";
			$sql .= " ORDER BY category_order, category_name ";
		} else {
			// show categories as catalog
			$allowed_sub_categories_ids = VA_Articles_Categories::find_all_ids("c.parent_category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")", VIEW_CATEGORIES_ITEMS_PERM);
			
			$sql = $table_prefix . "articles_categories ac ";
			$sql = " (" . $sql . " LEFT JOIN " . $table_prefix . "articles_categories sc ON ac.category_id=sc.parent_category_id) ";
			
			$sql  = " SELECT c.category_id AS top_category_id, c.category_name AS top_category_name, c.friendly_url AS top_friendly_url,";
			$sql .= " c.image_small, c.image_small_alt, c.image_large, c.image_large_alt, ";
			$sql .= " s.category_id AS sub_category_id, s.category_name AS sub_category_name, ";
			$sql .= " s.friendly_url AS sub_friendly_url, c.is_rss ";
			$sql .= " FROM ( " . $table_prefix . "articles_categories c ";
			$sql .= " LEFT JOIN " . $table_prefix . "articles_categories s ON c.category_id=s.parent_category_id) ";
			$sql .= " WHERE (s.category_id IN (" . $db->tosql($sub_categories_ids, INTEGERS_LIST) . ")";
			$sql .= " OR s.category_id IS NULL)";				
			$sql .= " AND c.category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")";
			$sql .= " ORDER BY c.category_order, c.category_name, c.category_id, s.category_order, s.category_name ";		
		}
		$db->query($sql);
		if($db->next_record())
		{
			$category_number = 0;
			$is_subcategories = true;
			$shown_sub_categories = get_setting_value($vars, "articles_categories_subs");
			$catalog_top_number = 0;
			$catalog_sub_number = 0;
			$column_width = intval(90 / $columns);
			$t->set_var("column_width", $column_width . "%");
			do
			{
				$category_number++;
				$top_category_id = $db->f("top_category_id");
				$top_category_name = get_translation($db->f("top_category_name"));
				$top_friendly_url = $db->f("top_friendly_url");
				$sub_category_id = $db->f("sub_category_id");
				$sub_category_name = get_translation($db->f("sub_category_name"));
				$sub_friendly_url = $db->f("sub_friendly_url");
				if ($sub_category_id) { $catalog_sub_number++; }
				$top_a_title = ""; $sub_a_title = "";

				$image_default = ""; // some default image for categories
				$image_small = $db->f("image_small");
				$image_small_alt = get_translation($db->f("image_small_alt"));
				$image_large = $db->f("image_large");
				$image_large_alt = get_translation($db->f("image_large_alt"));
	
				$t->set_var("catalog_top_id", $top_category_id);
				$t->set_var("catalog_top_name", htmlspecialchars($top_category_name));
				$t->set_var("top_a_title", htmlspecialchars($top_a_title));
				if ($categories_type == 2) {
					$t->set_var("catalog_sub_id",   $sub_category_id);
					$t->set_var("catalog_sub_name", ($sub_category_name));
					$t->set_var("sub_a_title", htmlspecialchars($sub_a_title));
					if ($desc_type == 1 && $short_description) {
						$t->set_var("desc_text", $short_description);
						$t->parse("short_description", false);
					} else if ($desc_type == 2 && $full_description) {
						$t->set_var("desc_text", $full_description);
						$t->parse("full_description", false);
					}

				} else {
	  			if (strlen($db->f("short_description"))) {
						$t->set_var("desc_text", get_translation($db->f("short_description")));
						$t->parse("short_description", false);
					} else {
						$t->set_var("short_description", "");
					}
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

			if($catalog_top_number % $columns != 0) {
				$t->parse("catalog_rows");
			}
  
			$block_parsed = true;
		}

	} else { // list type 

		$columns = 1;
		$html_template = get_setting_value($block, "html_template", "block_categories_list.html"); 
	  $t->set_file("block_body", $html_template);
		$t->set_var("nodes",      "");

		$categories_image = get_setting_value($vars, "articles_category_image");
		if (!$articles_category_id) { $articles_category_id = $top_id; }
		$category_path = "0," . $articles_category_id;
		if ($categories_type == 4) { // Tree-type structure
			$sql  = " SELECT category_path ";
			$sql .= " FROM " . $table_prefix . "articles_categories ";
			$sql .= " WHERE category_id=" . $db->tosql($articles_category_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$category_path  = $db->f("category_path");
				$category_path .= $articles_category_id;
			}
			$categories_ids = VA_Articles_Categories::find_all_ids("c.parent_category_id IN (" . $db->tosql($category_path, INTEGERS_LIST) . ")", VIEW_CATEGORIES_PERM);
			$allowed_categories_ids = VA_Articles_Categories::find_all_ids("c.parent_category_id IN (" . $db->tosql($category_path, INTEGERS_LIST) . ")", VIEW_CATEGORIES_ITEMS_PERM);
		} else {
			$categories_ids = VA_Articles_Categories::find_all_ids("", VIEW_CATEGORIES_PERM);
			$allowed_categories_ids = VA_Articles_Categories::find_all_ids("", VIEW_CATEGORIES_ITEMS_PERM);
		}

		if (!$categories_ids) return;
		
		$categories = array();
		$sql  = " SELECT category_id, category_order, category_name, friendly_url, short_description, parent_category_id, ";
		$sql .= " image_small, image_small_alt, image_large, image_large_alt, is_rss ";
		$sql .= " FROM " . $table_prefix . "articles_categories ";	
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
			$image_small_alt = $db->f("image_small_alt");
			$image_large = $db->f("image_large");
			$image_large_alt = $db->f("image_large_alt");

			if ($friendly_urls && $friendly_url) {
				$category_url = $friendly_url.$friendly_extension;
			} else {
				$category_url = $list_page."?category_id=".$cur_category_id;
			}

			$parent_category_id = $db->f("parent_category_id");
			$categories[$cur_category_id]["parent_id"] = $parent_category_id;
			$categories[$cur_category_id]["title"] = $category_name;
			$categories[$cur_category_id]["a_title"] = $a_title;
			$categories[$cur_category_id]["url"] = $category_url;
			$categories[$cur_category_id]["short_description"] = $short_description;
			$categories[$cur_category_id]["image_small"] = $image_small;
			$categories[$cur_category_id]["image_small_alt"] = $image_small_alt;
			$categories[$cur_category_id]["image_large"] = $image_large;
			$categories[$cur_category_id]["image_large_alt"] = $image_large_alt;
			if (!$allowed_categories_ids || !in_array($cur_category_id, $allowed_categories_ids)) {
				$categories[$cur_category_id]["allowed"] = false;
			} else {
				$categories[$cur_category_id]["allowed"] = true;
			}
			$categories[$parent_category_id]["subs"][$cur_category_id] = $category_order;
		}
  
		if (sizeof($categories) > 0 && isset($categories[$top_id]))
		{
			$category_number = 0;
			$column_width = intval(100 / $columns);
			$t->set_var("column_width", $column_width . "%");

			set_tree($categories, $top_id, 0, $articles_category_id, $categories_image);

			$block_parsed = true;
		}
	}
