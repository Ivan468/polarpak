<?php

	$default_title = "{category_name} &nbsp; {CONTENT_TITLE}";

	$search_string = get_param("search_string");
	if (strlen($search_string)) {
		return;
	}

	$top_id = $block["block_key"];
	$page_param = "ap".$top_id;

	$friendly_urls      = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	// TODO: require a new settings for content block
	$records_per_page   = get_setting_value($vars, "articles_content_recs", 10);
	$selected_page      = get_param($page_param);
	if (!$selected_page) {
		$selected_page = 1;
	}
	
	
	if ($friendly_urls && isset($page_friendly_url) && $page_friendly_url) {
		$articles_page = $page_friendly_url . $friendly_extension . "?";
	} else if (isset($current_page) && $current_page) {
		$articles_page = $current_page;
		$query_string = transfer_params("");
		if ($query_string) { 
			$articles_page .= $query_string."&"; 
		} else { 
			$articles_page .= "?"; 
		}
	} else {
		$articles_page = "articles.php?category_id=" . $current_category_id . "&";
	}
	
	if (strlen($articles_order_column)) {
		if ($articles_order_column == "article_order") {
			$articles_order = " ORDER BY aa." . $articles_order_column;
		} else {
			$articles_order = " ORDER BY a." . $articles_order_column;
		}
		$articles_order .= " " . $articles_order_direction;
	} else {
		$articles_order_column = "article_order";
		$articles_order = " ORDER BY aa.article_order ";
	}

	$html_template = get_setting_value($block, "html_template", "block_content.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("category_name", $current_category);

	$sql  = " SELECT a.article_id, a.article_title ";
	$sql .= " FROM " . $table_prefix . "articles a ";
	$sql .= " , " . $table_prefix . "articles_statuses st ";
	$sql .= " , " . $table_prefix . "articles_assigned aa ";
	$sql .= " WHERE a.status_id=st.status_id AND a.article_id=aa.article_id ";
	$sql .= " AND aa.category_id=" . $db->tosql($current_category_id, INTEGER);
	$sql .= " AND st.allowed_view=1 ";
	$sql .= " GROUP BY a.article_id, a.article_title ";
	if ($articles_order_column && $articles_order_column != "article_title") {
		if ($articles_order_column == "article_order") {
			$sql .= ", aa.article_order ";
		} else {
			$sql .= ", a." . $articles_order_column;
		}
		$sql .= $articles_order;
	}

	$db->query($sql);
	if($db->next_record())
	{
		$item_number = 0;
		$page_number = 1;
		do
		{
			$item_number++;
			$article_id = $db->f("article_id");
			$a_name = "#a".$article_id;
			if (!($selected_page == $page_number)) {
				$a_name = $articles_page . $page_param."=".$page_number . $a_name;
			} else {
				$a_name = get_request_uri() . $a_name;
			}
			$t->set_var("a_name", $a_name);
			$t->set_var("content_item_name", get_translation($db->f("article_title")));

			$t->parse("content_items", true);
			if (($item_number % $records_per_page) == 0) {
				$page_number++;
			}
		} while ($db->next_record());              	

		$block_parsed = true;
	}

?>