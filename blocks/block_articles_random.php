<?php

	include_once("./includes/articles_functions.php");

	$default_title = "{top_category_name} :: {RANDOM_TITLE}";

	$top_id = $block["block_key"];
	$top_name = "";

	if (VA_Articles_Categories::check_permissions($top_id, VIEW_CATEGORIES_ITEMS_PERM)) {
		$sql  = " SELECT category_name ";
		$sql .= " FROM " . $table_prefix . "articles_categories ";				
		$sql .= " WHERE category_id=" . $db->tosql($top_id, INTEGER);			
		$db->query($sql);
		if ($db->next_record()) {
			$top_name = get_translation($db->f("category_name"));
		} else {
			return false;
		}
	}
	
	// friendly url settings
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	// block settings
	$articles_recs = get_setting_value($vars, "recs", 1);
	$articles_cols = get_setting_value($vars, "cols", 1);
	if ($articles_cols < 0) { $articles_cols = 1; }
	$random_image = get_setting_value($vars, "random_image", 0); // show also random image for article
	$show_article_title = get_setting_value($vars, "article_title", 0);
	$show_article_date = get_setting_value($vars, "article_date", 0);
	$show_author = get_setting_value($vars, "author", 0);
	$show_image_tiny = get_setting_value($vars, "image_tiny", 0);
	$show_image_small = get_setting_value($vars, "image_small", 0);
	$show_image_large = get_setting_value($vars, "image_large", 0);
	$show_image_super = get_setting_value($vars, "image_super", 0);
	$show_highlights = get_setting_value($vars, "highlights", 0);
	$show_hot_desc = get_setting_value($vars, "hot_description", 0);
	$show_short_desc = get_setting_value($vars, "short_description", 0);
	$show_full_desc = get_setting_value($vars, "full_description", 0);

	$fields = array(
		"article_title", "article_date", "author",
		"image_tiny", "image_small", "image_large", "image_super",
		"highlights", "hot_description", "short_description", "full_description",
	);

	$html_template = get_setting_value($block, "html_template", "block_articles_random.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("top_category_name",$top_name);
	$t->set_var("articles_rows",  "");
	$t->set_var("articles_cols",  "");
	$t->set_var("columns_class", "cols-".$articles_cols);

	$db->RecordsPerPage = $articles_recs;
	$db->PageNumber = 1;
	
	$params = array();
	$params["where"]  = " (c.category_id = " . $db->tosql($top_id, INTEGER);
	$params["where"] .= " OR c.category_path LIKE '0," . $top_id . ",%') ";
	if ($db->DBType == "mysql") {
		$params["order"] = " GROUP BY a.article_id ORDER BY RAND() ";	
	} else if ($db->DBType == "postgre") {
		$params["order"] = " GROUP BY a.article_id ORDER BY RANDOM() ";	
	} else if ($db->DBType == "access") {
		$params["order"] = " GROUP BY a.article_id ORDER BY rnd(article_id) ";	
	}
	
	$articles_ids = VA_Articles::find_all_ids($params, VIEW_CATEGORIES_ITEMS_PERM);
	if (!$articles_ids) { return false; }

	// check articles restricted to view
	$allowed_articles_ids = VA_Articles::find_all_ids("a.article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ")", VIEW_ITEMS_PERM);

	$articles = array();		
	$sql  = " SELECT *  ";
	$sql .= " FROM " . $table_prefix . "articles";
	$sql .= " WHERE article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ")";
	$sql .= " ORDER BY rating DESC, article_order, article_title ";
	$db->query($sql);
	while ($db->next_record()) {
		$article_id = $db->f("article_id");
		$article_date = $db->f("date_added", DATETIME);
		if (!is_array($article_date)) { $article_date = $db->f("date_added", DATETIME); }
		$friendly_url = $db->f("friendly_url");
		$is_remote_rss = $db->f("is_remote_rss");
		$details_remote_url = $db->f("details_remote_url");
		if (!$is_remote_rss){
			if ($friendly_urls && $friendly_url) {
				$article_url = $friendly_url . $friendly_extension;
			} else {
				$article_url = "article.php?article_id=" . $article_id;
			}
		} else {
			$article_url = $details_remote_url;
		}
		$articles[$article_id] = $db->Record;
		$articles[$article_id]["article_date"] = $article_date;
		$articles[$article_id]["article_url"] = $article_url;
	}

	if (count($articles) > 0) {
		$position = 0; $col_style = "";
		if ($articles_cols > 1) {
			$col_style = "width: ".round(100 / $articles_cols, 2)."%;";
		}
		$t->set_var("col_style", $col_style);
		foreach ($articles as $article_id => $article_data)  {
			// get data
			$position++;
			$article_date = $article_data["article_date"];
			$article_title = get_translation($article_data["article_title"]);
			$article_url = $article_data["article_url"];
			$rating = $article_data["rating"];
			$total_views = $article_data["total_views"];

			// check author data
			$article_data["author"] = 0;
			$author_name = $article_data["author_name"];
			$author_email = $article_data["author_email"];
			$author_url = $article_data["author_url"];
			$t->set_var("author_name_block", "");
			$t->set_var("author_email_block", "");
			$t->set_var("author_url_block", "");
			if ($author_name || $author_name || $author_name) {
				$article_data["author"] = 1;
				if ($author_name) {
					$t->set_var("author_name", $author_name);
					$t->parse("author_name_block", false);
				}
				if ($author_email) {
					$t->set_var("author_email", $author_email);
					$t->parse("author_email_block", false);
				}
				if ($author_url) {
					$t->set_var("author_url", $author_url);
					$t->parse("author_url_block", false);
				}
			}


			$use_default_image = true;
			if ($random_image) {
				$sql  = " SELECT * FROM ".$table_prefix."articles_images ";
				$sql .= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
				$sql .= " AND (image_position=1 OR image_position=2) ";
				if ($db->DBType == "mysql") {
					$sql .= " ORDER BY RAND() ";	
				} else if ($db->DBType == "postgre") {
					$sql .= " ORDER BY RANDOM() ";	
				} else if ($db->DBType == "access") {
					$sql .= " ORDER BY rnd(article_id) ";	
				}
				$db->RecordsPerPage = 1;
				$db->PageNumber = 1;
				$db->query($sql);
				if ($db->next_record()) {
					$use_default_image = false;
					$image_tiny = $db->f("image_tiny");
					$image_tiny_alt = $db->f("image_tiny_alt");
					$image_small = $db->f("image_small");
					$image_small_alt = $db->f("image_small_alt");
					$image_large = $db->f("image_large");
					$image_large_alt = $db->f("image_large_alt");
					$image_super = $db->f("image_super");
					$image_super_alt = $db->f("image_super_alt");
				}
			}
			if ($use_default_image) {
				$image_tiny = $article_data["image_tiny"];
				$image_tiny_alt = $article_data["image_tiny_alt"];
				$image_small = $article_data["image_small"];
				$image_small_alt = $article_data["image_small_alt"];
				$image_large = $article_data["image_large"];
				$image_large_alt = $article_data["image_large_alt"];
				$image_super = $article_data["image_super"];
				$image_super_alt = $article_data["image_super_alt"];
			}

			$is_html = $article_data["is_html"];
			$highlights = get_translation($article_data["highlights"]);
			$hot_description = get_translation($article_data["hot_description"]);
			$short_description = get_translation($article_data["short_description"]);
			$full_description = get_translation($article_data["full_description"]);

			$t->set_var("article_title", $article_title);
			$t->set_var("rating", number_format($rating, 2));
			$t->set_var("total_views", $total_views);
			if (is_array($article_date)) {
				$t->set_var("article_date", va_date($datetime_show_format, $article_date));
			} else {
				$t->set_var("article_date", "");
			}
			$t->set_var("article_url", $article_url);

			$t->set_var("image_tiny_src", $image_tiny);
			$t->set_var("image_tiny_alt", $image_tiny_alt);
			$t->set_var("image_small_src", $image_small);
			$t->set_var("image_small_alt", $image_small_alt);
			$t->set_var("image_large_src", $image_large);
			$t->set_var("image_large_alt", $image_large_alt);
			$t->set_var("image_super_src", $image_super);
			$t->set_var("image_super_alt", $image_super_alt);
			$t->set_var("highlights", $highlights);
			$t->set_var("hot_description", $hot_description);
			$t->set_var("short_description", $short_description);
			if ($is_html) {
				$t->set_var("full_description", $full_description);
			} else {
				$t->set_var("full_description", nl2br(htmlspecialchars($full_description)));
			}

			if (!$allowed_articles_ids || !in_array($article_id, $allowed_articles_ids)) {
				$t->set_var("restricted_class", " restricted ");
			} else {
				$t->set_var("restricted_class", "");
			}

			foreach ($fields as $field_name) {
				$show_block = get_setting_value($vars, $field_name, 0);
				$field_value = get_setting_value($article_data, $field_name, "");
				if ($show_block && $field_value) { 
					$t->sparse($field_name."_block", false); 
				} else {
					$t->set_var($field_name."_block", ""); 
				}
			}
				
			$t->parse("articles_cols", true);
			if ($position % $articles_cols == 0) {
				$t->parse("articles_rows", true);
				$t->set_var("articles_cols", "");
			}
		} 
		if ($position % $articles_cols) {
			$t->parse("articles_rows", true);
		}
		$block_parsed = true;
	}


?>