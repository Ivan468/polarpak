<?php

	$default_title = "{ADS_TITLE} &nbsp; {TOP_VIEWED_TITLE}";

	include_once("./includes/ads_functions.php");

	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	$html_template = get_setting_value($block, "html_template", "block_ads_top_viewed.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("top_viewed_cols", "");
	$t->set_var("top_viewed_rows", "");

	$records_per_page = get_setting_value($vars, "ads_top_viewed_recs", 10);
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = 1;
	
	$items_ids = VA_Ads::find_all_ids(
		array(
			"order" => " ORDER BY i.total_views DESC, i.date_start DESC, i.item_title "
		),
		VIEW_CATEGORIES_ITEMS_PERM
	);
	if (!$items_ids) return;
	
	$allowed_items_ids = VA_Ads::find_all_ids("i.item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")", VIEW_ITEMS_PERM);
	
	$sql  = " SELECT item_id, item_title, friendly_url, total_views, short_description ";
	$sql .= " FROM " . $table_prefix . "ads_items ";
	$sql .= " WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ") ";	
	$sql .= " ORDER BY total_views DESC, date_start DESC, item_title ";
	
	$db->query($sql);
	if($db->next_record())
	{
		$top_columns = get_setting_value($vars, "ads_top_viewed_cols", 1);
		$t->set_var("top_viewed_column", (100 / $top_columns) . "%");
		$top_number = 0;
		do
		{
			$top_number++;
			$item_id = $db->f("item_id");
			$item_title = get_translation($db->f("item_title"));
			$friendly_url = $db->f("friendly_url");
			$total_views = $db->f("total_views");
			$short_description = get_translation($db->f("short_description"));
			if ($friendly_urls && $friendly_url) {
				$t->set_var("ads_details_url", $friendly_url . $friendly_extension);
			} else {
				$t->set_var("ads_details_url", "ads_details.php?item_id=" . $item_id);
			}
		
			$t->set_var("top_position", $top_number);
			$t->set_var("item_id", $item_id);
			$t->set_var("item_title", $item_title);
			$t->set_var("total_views", $total_views);
			$t->set_var("short_description", $short_description);

			if (!$allowed_items_ids || !in_array($item_id, $allowed_items_ids)) {
				$t->set_var("restricted_class", " restricted ");
			} else {
				$t->set_var("restricted_class", "");
			}
			
			$t->parse("top_viewed_cols");
			if($top_number % $top_columns == 0)
			{
				$t->parse("top_viewed_rows");
				$t->set_var("top_viewed_cols", "");
			}
			
		} while ($db->next_record());              	

		if ($top_number % $top_columns != 0) {
			$t->parse("top_viewed_rows");
		}

		$block_parsed = true;
	}

