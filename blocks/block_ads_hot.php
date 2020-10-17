<?php

	$default_title = "{top_category_name} &nbsp; {HOT_TITLE}";

	include_once("./includes/ads_functions.php");

	// get global ads settings
	if (!isset($ads_settings)) { $ads_settings = get_settings("ads"); }

	// check ads parameters
	$ad_category_id = 0; $ad_category_name = "";
	if ($cms_page_code == "ads_list" || $cms_page_code == "ad_details") {
		$ad_category_id = get_param("category_id");
		if (!$ad_category_id && isset($category_id)) { $ad_category_id = $category_id; }
		if (isset($category_name)) { $ad_category_name = $category_name; } 
	}
	if (!strlen($ad_category_name)) {
		$ad_category_name = va_message("ADS_TITLE");
	}

	$html_template = get_setting_value($block, "html_template", "block_ads_hot.html"); 
  $t->set_file("block_body", $html_template);

	$t->set_var("hot_rows", "");
	$t->set_var("hot_cols", "");
	$t->set_var("top_category_name", $ad_category_name);

	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	$default_image = get_setting_value($ads_settings, "image_small_default", "");

	if ($friendly_urls && isset($page_friendly_url) && $page_friendly_url) {
		$pass_parameters = get_transfer_params($page_friendly_params);
		$current_page = $page_friendly_url . $friendly_extension;
	} else {
		$pass_parameters = get_transfer_params();
	}
	
	$search_tree = new VA_Tree("category_id", "category_name", "parent_category_id", $table_prefix . "ads_categories", "tree", "");

	$sql_where  = " i.is_approved=1 AND i.is_paid=1 AND i.is_shown=1  ";
	$sql_where .= " AND i.is_hot=1 ";
	$sql_where .= " AND i.hot_date_start<=" . $db->tosql(va_time(), DATETIME);
	$sql_where .= " AND i.hot_date_end>" . $db->tosql(va_time(), DATETIME);
	if ($ad_category_id > 0)	{
		$sql_where .= " AND (c.category_id = " . $db->tosql($ad_category_id, INTEGER);
		$sql_where .= " OR c.category_path LIKE '" . $db->tosql($search_tree->get_path($ad_category_id), TEXT, false) . "%')";
	}
	$items_ids = VA_Ads::find_all_ids($sql_where, VIEW_CATEGORIES_ITEMS_PERM);
	
	if (!$items_ids) {
		return;
	}
	
	$allowed_items_ids = VA_Ads::find_all_ids("i.item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")", VIEW_CATEGORIES_ITEMS_PERM);
	$total_records = count($items_ids);
	
	$records_per_page = get_setting_value($vars, "ads_hot_recs", 10);
	$pages_number = 5;
	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", $current_page);
	$page_number = $n->set_navigator("hot_navigator", "hot_page", SIMPLE, $pages_number, $records_per_page, $total_records, false, $pass_parameters);

	$sql  = " SELECT item_id, item_title, friendly_url, short_description, image_small, hot_description ";
	$sql .= " FROM " . $table_prefix . "ads_items ";
	$sql .= " WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ") ";
	$sql .= " ORDER BY date_start DESC, date_added ";

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query($sql);
	if($db->next_record())
	{
		$hot_columns = get_setting_value($vars, "ads_hot_cols", 1);
		$t->set_var("hot_column", (100 / $hot_columns) . "%");
		$hot_number = 0;
		do
		{
			$hot_number++;
			$item_id = $db->f("item_id");
			$item_title = get_translation($db->f("item_title"));
			$friendly_url = $db->f("friendly_url");
			$hot_description = get_translation($db->f("hot_description"));
			if (!strlen($hot_description)) {
				$hot_description = get_translation($db->f("short_description"));
			}

			$t->set_var("item_id", $item_id);
			$t->set_var("hot_item_name", $item_title);
			$t->set_var("hot_description", $hot_description);
			if ($friendly_urls && $friendly_url) {
				$t->set_var("details_href", $friendly_url . $friendly_extension);
			} else {
				$t->set_var("details_href", "ads_details.php?item_id=" . $item_id);
			}

			$image_small = $db->f("image_small");
			if (!strlen($image_small) || !image_exists($image_small)) {
				$image_exists = false;
				$image_small = $default_image;
			} else {
				$image_exists = true;
			}
			if($image_small) {
				if (preg_match("/^http\:\/\//", $image_small)) {
					$image_size = "";
				} else {
       		$image_size = @GetImageSize($image_small);
					if ($image_exists && isset($restrict_ads_images) && $restrict_ads_images) { 
						$image_small = "image_show.php?ad_id=".$item_id."&type=small"; 
					}
				}
				$t->set_var("alt", htmlspecialchars($item_title));
				$t->set_var("src", htmlspecialchars($image_small));
				if(is_array($image_size)) {
					$t->set_var("width", "width=\"" . $image_size[0] . "\"");
					$t->set_var("height", "height=\"" . $image_size[1] . "\"");
				} else {
					$t->set_var("width", "");
					$t->set_var("height", "");
				}
				$t->parse("image_small", false);
			} else {
				$t->set_var("image_small", "");
			}

			if (!$allowed_items_ids || !in_array($item_id, $allowed_items_ids)) {
				$t->set_var("restricted_class", " restricted ");
			} else {
				$t->set_var("restricted_class", "");
			}
			
			$t->parse("hot_cols");
			if($hot_number % $hot_columns == 0) {
				$t->parse("hot_rows");
				$t->set_var("hot_cols", "");
			}

		} while ($db->next_record());

		if ($hot_number % $hot_columns != 0) {
			$t->parse("hot_rows");
		}

		$block_parsed = true;
	}

