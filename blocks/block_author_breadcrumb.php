<?php

	include_once("./includes/articles_functions.php");
	include_once("./includes/friendly_functions.php");

	$article_id = get_param("article_id");
	$author_id = get_param("author_id");
	$album_id = get_param("album_id");
	$rss_on_breadcrumb = isset($rss_on_breadcrumb)?$rss_on_breadcrumb:false;
	$erase_tags = false;

	$site_url = get_setting_value($settings, "site_url", "");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	$html_template = get_setting_value($block, "html_template", "block_author_breadcrumb.html"); 
  $t->set_file("block_body", $html_template);

	$breadcrumb_trail = array();
	$t->set_var("index_href", $site_url);
	$t->set_var("index_url", $site_url);

	if (strlen($article_id) && !strlen($author_id)) { 
		$sql = " SELECT author_id FROM " . $table_prefix . "articles_authors WHERE article_id=" . $db->tosql($article_id, INTEGER);
		$author_id = get_db_value($sql);
	}

	if (strlen($album_id) && !strlen($author_id)) { 
		$sql = " SELECT author_id FROM " . $table_prefix . "albums_authors WHERE album_id=" . $db->tosql($album_id, INTEGER);
		$author_id = get_db_value($sql);
	}

	if (strlen($article_id) && !strlen($album_id)) { 
		$sql = " SELECT album_id FROM " . $table_prefix . "articles_albums WHERE article_id=" . $db->tosql($article_id, INTEGER);
		$album_id = get_db_value($sql);
	}

	if (strlen($author_id)) {
		$sql = "SELECT author_name, name_first, friendly_url FROM " . $table_prefix . "authors WHERE author_id=" . $db->tosql($author_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$author_name = get_translation($db->f("author_name"));
			$name_first = $db->f("name_first");
			$friendly_url = $db->f("friendly_url");
			if ($friendly_urls && $friendly_url) {
				$author_url = $friendly_url . $friendly_extension;
				if ($default_language != "en") {
					$first_url = $default_language."-".english_translit(strtolower($name_first))."-authors" . $friendly_extension;
				} else {
					$first_url = strtolower($name_first)."-authors" . $friendly_extension;
				}
			} else {
				$author_url = "author_articles.php?author_id=" . $author_id;
				$first_url = "author_articles.php?filter=" . urlencode($name_first);
			}
		}
		$breadcrumb_trail[$first_url] = strtoupper($name_first);
		$breadcrumb_trail[$author_url] = $author_name;
	} else if (isset($authors_filter) && $authors_filter) {
		if ($friendly_urls) {
			$first_url = strtolower($authors_filter)."-authors" . $friendly_extension;
		} else {
			$first_url = "author_articles.php?filter=" . urlencode($authors_filter);
		}
		$breadcrumb_trail[$first_url] = mb_convert_case($authors_first, MB_CASE_UPPER, "UTF-8");  
	}

	if (strlen($album_id)) {
		$sql = "SELECT album_name, friendly_url FROM " . $table_prefix . "albums WHERE album_id=" . $db->tosql($album_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$album_name = get_translation($db->f("album_name"));
			$friendly_url = $db->f("friendly_url");
			if ($friendly_urls && $friendly_url) {
				$album_url = $friendly_url . $friendly_extension;
			} else {
				$album_url = "album.php?album_id=" . $album_id;
			}
		}
		$breadcrumb_trail[$album_url] = $album_name;
	}

	if (strlen($article_id) && VA_Articles::check_permissions($article_id, false, VIEW_ITEMS_PERM)) {
		$sql = "SELECT article_title, friendly_url FROM " . $table_prefix . "articles WHERE article_id=" . $db->tosql($article_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$article_title = get_translation($db->f("article_title"));
			$friendly_url = $db->f("friendly_url");
			if ($friendly_urls && $friendly_url) {
				$tree_url = $friendly_url . $friendly_extension;
			} else {
				$tree_url = "article.php?article_id=" . $article_id;
			}
			$tree_title = $article_title;
			if ($erase_tags) { $tree_title = strip_tags($tree_title); }
			$breadcrumb_trail[$tree_url] = $tree_title;
		}
	}

	/*
	if (isset($is_reviews) && $is_reviews) {
		$tree_url = "articles_reviews.php?category_id=" . urlencode($category_id) . "&article_id=" . urlencode($article_id);
		$tree_title = REVIEWS_MSG;
		if ($erase_tags) { $tree_title = strip_tags($tree_title); }
		$breadcrumb_trail[] = array($tree_url, $tree_title);
	}//*/

	/*
	if ($rss_on_breadcrumb) {
		$t->set_var("rss_href","articles_rss.php");
		$t->set_var("rss_url","articles_rss.php?author_id=" . urlencode($author_id));
		$t->parse("rss", false);
	}//*/
	
	foreach ($breadcrumb_trail as $tree_url => $tree_title) {
		$t->set_var("tree_url", htmlspecialchars($tree_url));
		$t->set_var("tree_title", htmlspecialchars($tree_title));
		$t->parse("tree", true);
	}
	
	if(!$layout_type) { $layout_type = "bb"; }
	$block_parsed = true;

?>