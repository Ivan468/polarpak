<?php
		
	$default_title = "{MANUALS_TITLE}";

	$category_id = get_param("category_id");
	
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");		
	
	$manual_categories_href = get_custom_friendly_url("manuals.php") . "?category_id=";
	$manual_articles_href   = get_custom_friendly_url("manuals_articles.php") . "?manual_id=";
	
	$html_template = get_setting_value($block, "html_template", "block_manuals_list.html"); 
	$t->set_file("block_body", $html_template);

	$where = "";
	if ($category_id) { $where = "c.category_id=" . $db->tosql($category_id, INTEGER); }
	//$manuals_ids = VA_Manuals::find_all_ids($where, VIEW_CATEGORIES_PERM);
	$categories_ids = VA_Manuals_Categories::find_all_ids($where, VIEW_CATEGORIES_PERM);
			
	if ($category_id) {
		$sql  = " SELECT category_name, meta_title, meta_keywords, meta_description ";
		$sql .= " FROM " . $table_prefix . "manuals_categories ";
		$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
			
		$db->query($sql);
		if ($db->next_record()) {
			$category_name = get_translation($db->f("category_name"));
			
			// meta data
			if ($cms_page_code == "manuals_list") {
				$db_meta_title = get_translation($db->f("meta_title"));
				$db_meta_keywords = get_translation($db->f("meta_keywords"));
				$db_meta_description = get_translation($db->f("meta_description"));
				if ($db_meta_title) { $meta_title = $db_meta_title; }
				if ($db_meta_keywords) { $meta_keywords = $db_meta_keywords; }
				if ($db_meta_description) { $meta_description = $db_meta_description; }

				if (!strlen($meta_title)) { $meta_title = $category_name; }
			}
		}
	}
	
	if ($categories_ids) {

		// get manual categories first
		$sql  = " SELECT mc.short_description, mc.category_id, mc.category_name, mc.friendly_url ";
		$sql .= " FROM " . $table_prefix . "manuals_categories mc ";
		$sql .= " WHERE mc.category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")";
		$sql .= " ORDER BY mc.category_order, mc.category_id ";
		$db->query($sql);
		while ($db->next_record()) {
			$category_id = $db->f("category_id");
			$categories[$category_id] = $db->Record;
		}

		foreach ($categories as $category_id => $category_info) {
			$t->set_var("manuals", "");
			$where = " ml.category_id=" . $db->tosql($category_id, INTEGER);
			$manuals_ids = VA_Manuals::find_all_ids($where, VIEW_CATEGORIES_ITEMS_PERM);
			$allowed_manuals_ids = $manuals_ids; // at this moment 
			
			if (is_array($manuals_ids) && count($manuals_ids) > 0) {
				$sql  = " SELECT ml.manual_id, ml.manual_title, ml.short_description, ml.friendly_url ";
				$sql .= " FROM " . $table_prefix . "manuals_list ml ";
				$sql .= " WHERE ml.manual_id IN (" . $db->tosql($manuals_ids, INTEGERS_LIST) . ")";
				$sql .= " ORDER BY ml.manual_order, ml.manual_id ";
				$db->query($sql);
				while ($db->next_record()) {
					$manual_id = $db->f("manual_id");
					
					// Parse manual
					$t->set_var("manual_title", get_translation($db->f("manual_title")));
					$t->set_var("short_description", get_translation($db->f("short_description")));
					$friendly_url = $db->f("friendly_url");
					
					if ($friendly_urls && $friendly_url != "") {
						$manual_href = $friendly_url . $friendly_extension;
					} else {
						$manual_href = $manual_articles_href . $manual_id;
					}
					
					if (!$allowed_manuals_ids || !in_array($manual_id, $allowed_manuals_ids)) {
						$t->set_var("restricted_class", " restricted ");
					} else {
						$t->set_var("restricted_class", "");
					}
					
					$t->set_var("manual_href", $manual_href);
					$t->parse("manuals", true);
					
				}
			}
			

			// parse category
			$cat_friendly_url = $category_info["friendly_url"];
			$t->set_var("cat_name", get_translation($category_info["category_name"]));
			$t->set_var("category_short_description", get_translation($category_info["short_description"]));
			if ($friendly_urls && $cat_friendly_url != "") {
				$category_href = $cat_friendly_url . $friendly_extension;
			} else {
				$category_href = $manual_categories_href . $category_id;
			}
			$t->set_var("category_href", $category_href);

			$t->parse("categories", true);
		}
	} else {
		$t->parse("no_manuals", false);
		$t->set_var("manuals", "");
	}
	
	$block_parsed = true;

?>