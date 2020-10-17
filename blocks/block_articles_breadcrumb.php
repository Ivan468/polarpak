<?php

	include_once("./includes/articles_functions.php");

	$article_id = get_param("article_id");
	$rss_on_breadcrumb = isset($rss_on_breadcrumb)?$rss_on_breadcrumb:false;
	$erase_tags = false;

	$site_url = get_setting_value($settings, "site_url", "");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	$html_template = get_setting_value($block, "html_template", "block_articles_breadcrumb.html"); 
  $t->set_file("block_body", $html_template);

	$breadcrumbs_tree_array = array();
	$t->set_var("index_href", $site_url);

	$category_id = get_param("category_id");
	$search_category_id = get_param("search_category_id");
	if (strlen($search_category_id)) {
		$category_id = $search_category_id;
	}
	$item_id = get_param("item_id");
	if (!strlen($category_id) && strlen($item_id)) {
		$category_id = VA_Articles::get_category_id($item_id);
	}

	if ($category_id) {
		$current_id = $category_id;		
		while ($current_id) {
			$category_values = VA_Articles_Categories::find_all(false, 
				array("c.category_name", "c.friendly_url", "c.parent_category_id"),
				"c.category_id=" . $db->tosql($current_id, INTEGER), VIEW_CATEGORIES_PERM);
			if ($category_values) {
				$category_name = $category_values[0]["c.category_name"];
				$category_name = get_translation($category_name);
				$friendly_url  = $category_values[0]["c.friendly_url"];;
				if ($friendly_urls && $friendly_url) {
					$tree_url = $friendly_url . $friendly_extension;
				} else {
					$tree_url = "articles.php?category_id=". $current_id;
				}
				$tree_title = $category_name;
				if ($erase_tags) { $tree_title = strip_tags($tree_title); }
				array_unshift($breadcrumbs_tree_array, array($tree_url, $tree_title));
				$current_id=  $category_values[0]["c.parent_category_id"];
			} else {
				$current_id = "0";
			}
		}
	}

	// check search
	$ps_parameters = array();
	$search_params = array(
		"search_string"
	);
	for ($si = 0; $si < sizeof($search_params); $si++) {
		$search_param = $search_params[$si];
		$param_value  = get_param($search_param);
		if (strlen($param_value)) {
			$ps_parameters[$search_param] = $param_value;
		}
	}

	// Proceed products search parameters
	if (sizeof($ps_parameters) > 0) {
		$ps_parameters["s_tit"] = get_param("s_tit");
		$ps_parameters["s_des"] = get_param("s_des");
		$ps_parameters["category_id"] = $category_id;
		$query_string = get_query_string($ps_parameters, "", "", false);
		$tree_url = "articles.php" . $query_string;
		$tree_title = SEARCH_RESULTS_MSG;
		if ($erase_tags) { $tree_title = strip_tags($tree_title); }		
		$breadcrumbs_tree_array[] = array($tree_url, $tree_title);
	}

	if (isset($article_id) && strlen($article_id) && VA_Articles::check_permissions($article_id, false, VIEW_ITEMS_PERM)) {
		$ps_parameters["category_id"] = $category_id;
		$sql = "SELECT article_title, friendly_url FROM " . $table_prefix . "articles WHERE article_id=" . $db->tosql($article_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$article_title = get_translation($db->f("article_title"));
			$friendly_url = $db->f("friendly_url");
			if ($friendly_urls && $friendly_url) {
				$query_string = get_query_string($ps_parameters, "", "", false);
				$tree_url = $friendly_url . $friendly_extension . $query_string;
			} else {
				$ps_parameters["article_id"] = $article_id;
				$query_string = get_query_string($ps_parameters, "", "", false);
				$tree_url = "article.php" . $query_string;
			}

			$tree_title = $article_title;
			if ($erase_tags) { $tree_title = strip_tags($tree_title); }
			$breadcrumbs_tree_array[] = array($tree_url, $tree_title);
		}
	}

	if (isset($is_reviews) && $is_reviews) {
		$tree_url = "articles_reviews.php?category_id=" . urlencode($category_id) . "&article_id=" . urlencode($article_id);
		$tree_title = REVIEWS_MSG;
		if ($erase_tags) { $tree_title = strip_tags($tree_title); }
		$breadcrumbs_tree_array[] = array($tree_url, $tree_title);
	}

	if ($rss_on_breadcrumb) {
		$t->set_var("tree_current_id", $category_id);
		$t->set_var("rss_href","articles_rss.php");
		$t->set_var("rss_url","articles_rss.php?category_id=" . urlencode($category_id));
		$t->parse("rss", false);
	}
	
		
	$ic = count($breadcrumbs_tree_array) - 1;
	for ($i=0; $i<$ic; $i++) {
		$t->set_var("tree_url", htmlspecialchars($breadcrumbs_tree_array[$i][0]));
		$t->set_var("tree_title", htmlspecialchars($breadcrumbs_tree_array[$i][1]));
		$t->set_var("tree_class", "");
		$t->parse("tree", true);
	}
	if ($ic>=0) {
		$t->set_var("tree_url", htmlspecialchars($breadcrumbs_tree_array[$ic][0]));
		$t->set_var("tree_title", htmlspecialchars($breadcrumbs_tree_array[$ic][1]));
		$t->set_var("tree_class", "treeItemLast");
		$t->parse("tree", true);
	}

	if ($cms_page_code == "article_details") {
		$t->sparse("fb_like", true);
	}
	
	if(!$layout_type) { $layout_type = "bb"; }
	$block_parsed = true;

?>