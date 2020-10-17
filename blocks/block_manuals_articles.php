<?php

	include_once("./includes/manuals_functions.php");

	$default_title = "{manual_title}";

	$manual_id = get_param("manual_id");
	$article_id = get_param("article_id");
	if (!strlen($manual_id) && strlen($article_id)) {
		$sql  = " SELECT manual_id ";
		$sql .= " FROM " . $table_prefix . "manuals_articles ";
		$sql .= " WHERE article_id=".$db->tosql($article_id, INTEGER);			
		$manual_id = get_db_value($sql);
	}
	$manual_id = intval($manual_id);

	$html_template = get_setting_value($block, "html_template", "block_manuals_articles.html"); 
	$t->set_file("block_body", $html_template);
			
	$article_href = "manuals_article_details.php?article_id=";

	if (!VA_Manuals::check_exists($manual_id)) {		
		$default_title = MANUALS_TITLE;
		$t->parse("no_manuals");
		$block_parsed = true;
		return;
	}
	
	if (!VA_Manuals::check_permissions($manual_id, VIEW_CATEGORIES_ITEMS_PERM)) {
		header ("Location: " . get_custom_friendly_url("user_login.php") . "?type_error=2");
		exit;
	}		
	
	
	$allowed_details = VA_Manuals::check_permissions($manual_id, VIEW_ITEMS_PERM);
	
	// Get manual info
	$sql  = " SELECT ml.manual_title, ml.full_description, ml.meta_title, ml.meta_keywords, ";
	$sql .= " ml.meta_description, mc.category_name ";
	$sql .= " FROM (" . $table_prefix . "manuals_list ml ";
	$sql .= " LEFT JOIN " . $table_prefix . "manuals_categories mc ON mc.category_id = ml.category_id )";
	$sql .= " WHERE ml.manual_id = ".$db->tosql($manual_id, INTEGER);
	
	$db->query($sql);
	if ($db->next_record()) {
		$category_name = get_translation($db->f("category_name"));
		$manual_title  = get_translation($db->f("manual_title"));
		$t->set_var("manual_title", $manual_title);
		$manual_full_description = get_translation($db->f("full_description"));
		
		if ($manual_full_description != "") {
			$t->set_var("manual_full_description", $manual_full_description);
			$t->parse("manual_full_description_block", false);
		}
		// meta data
		if ($cms_page_code == "manual_articles") {
			$db_meta_title = get_translation($db->f("meta_title"));
			$db_meta_keywords = get_translation($db->f("meta_keywords"));
			$db_meta_description = get_translation($db->f("meta_description"));
			if ($db_meta_title) { $meta_title = $db_meta_title; }
			if ($db_meta_keywords) { $meta_keywords = $db_meta_keywords; }
			if ($db_meta_description) { $meta_description = $db_meta_description; }

			if (!strlen($meta_title)) { $meta_title = $manual_title; }
		}
	}
	
	$sql  = " SELECT manual_id, allowed_view, article_id, parent_article_id, ";
	$sql .= " article_title, friendly_url, section_number, article_order, short_description ";
	$sql .= " FROM " . $table_prefix . "manuals_articles ";
	$sql .= " WHERE manual_id=" . $db->tosql($manual_id, INTEGER);
	$sql .= " AND allowed_view = 1";	
	$sql .= " ORDER BY article_order";

	$db->query($sql);
	
	$manual_articles_tree = array();
	$manual_full_description = "";
	
	if ($db->next_record()) {
		if (!$allowed_details) {
			$t->set_var("restricted_class", " restricted ");
		} else {
			$t->set_var("restricted_class", "");
		}
				
		do {
			$article_id = $db->f("article_id");
			$parent_id = $db->f("parent_article_id");
			$articles[$article_id] = $db->Record;
			$manual_articles_tree[$parent_id][] = $article_id; 
		} while ($db->next_record());
		
		$t->set_var("no_contents", "");
		show_level_articles(0);
	} else {
		$t->set_var("articles", "");
		if ($manual_full_description != "") {
			$t->set_var("no_contents", "");
		} else {
			$t->parse("no_contents", false);
		}
	}
	
		
	$block_parsed = true;
	
	/**
	 * Arrange articles according to its level
	 *
	 * @param integer $parent_id
	 */
	function show_level_articles($parent_id) {
		global $t;
		global $articles;
		global $article_href;
		global $manual_articles_tree;
		global $settings;
		
		// Global friendly url settings
		$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
		$friendly_extension = get_setting_value($settings, "friendly_extension", "");
		
		
		if (isset($manual_articles_tree[$parent_id]) && is_array($manual_articles_tree[$parent_id])) {
			foreach ($manual_articles_tree[$parent_id] as $article_id) {
				// Parse article
				$article_id = $articles[$article_id]["article_id"];
				$t->set_var("article_title", get_translation($articles[$article_id]["article_title"]));
				$section_number = $articles[$article_id]["section_number"];
				$t->set_var("section_number", $section_number);
				$t->set_var("short_description", get_translation($articles[$article_id]["short_description"]));
				$friendly_url = $articles[$article_id]["friendly_url"];
				if ($friendly_urls && $friendly_url != "") {
					$href = $friendly_url . $friendly_extension;
				} else {
					$href = $article_href.$article_id;
				}
				$level = count(explode(".", $section_number));
				$t->set_var("level", $level);
				$t->set_var("article_href", $href);
				$t->parse("articles", true);
	
				show_level_articles($article_id);
			}

		}
	}
	
	/**
	 * Create link to manual's articles list. If friendly_url is not empty, return it, 
	 * else create direct url
	 *
	 * @param integer $manual_id
	 * @param string $friendly_url
	 * @return string
	 */
	function build_url_manuals_articles($manual_id, $friendly_url) {
		global $settings;

		// Global friendly url settings
		$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
		$friendly_extension = get_setting_value($settings, "friendly_extension", "");
		
		if ($friendly_urls && $friendly_url != "") {
			$url = $friendly_url . $friendly_extension;
		} else {
			$url = "manuals_articles.php?manual_id=".intval($manual_id);
		}
		return $url;
	}
?>