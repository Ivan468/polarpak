<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  article.php                                              ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/navigator.php");
	include_once("./includes/articles_functions.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");

	$va_version_code = va_version_code();

	if ($va_version_code & 1) {
		include_once("./includes/products_functions.php");
		include_once("./includes/shopping_cart.php");
		$tax_rates = get_tax_rates();
	}

	$cms_page_code = "article_details";
	$script_name   = "article.php";
	$current_page  = get_custom_friendly_url("article.php");
	$category_id = get_param("category_id");
	$search_category_id = get_param("search_category_id");
	if (strlen($search_category_id)) {
		$category_id = $search_category_id;
	}

	$article_id = get_param("article_id");
	if (!strlen($category_id) && strlen($article_id)) {
		$category_id = VA_Articles::get_category_id($article_id, VIEW_ITEMS_PERM);
	}
	
	if (!$category_id) {
		$top_id = VA_Articles::get_top_id($article_id);
		$category_id = $top_id;
	}
	$current_category_id = $category_id;
	$_GET["category_id"] = $category_id;


	if (!VA_Articles::check_exists($article_id)) {
		// aticle doesn't exists forward to listing page
		if (isset($category_id)) {
			header("Location: articles.php?category_id=".urlencode($category_id));
			exit;
		} else {
			header("Location: index.php");
			exit;
		}
	}

	// retrieve info about current category
	$article_settings_data = ""; $edit_fields_data = "";
	$sql  = " SELECT * ";
	$sql .= " FROM " . $table_prefix . "articles_categories ";
	$sql .= " WHERE category_id = " . $db->tosql($category_id, INTEGER, true, false);	
	$db->query($sql);
	if ($db->next_record()) {
		$category_info = $db->Record;
		$current_category = get_translation($db->f("category_name"));
		$short_description = get_translation($db->f("short_description"));
		$full_description = get_translation($db->f("full_description"));
		$image_small = $db->f("image_small");
		$image_small_alt = $db->f("image_small_alt");
		$image_large = $db->f("image_large");
		$image_large_alt = $db->f("image_large_alt");
		$parent_category_id = $db->f("parent_category_id");
		$category_path = $db->f("category_path") . $category_id;
		if ($db->f("is_rss") && $db->f("rss_on_breadcrumb")){
			$rss_on_breadcrumb = true;
		} else {
			$rss_on_breadcrumb = false;
		}
		if ($parent_category_id == 0) {
			$top_id = $category_id;
			$top_name = $current_category;
			$articles_order_column = $db->f("articles_order_column");
			$articles_order_direction = $db->f("articles_order_direction");
			$list_fields = $db->f("article_list_fields");
			$details_fields = $db->f("article_details_fields");
			$edit_fields_data = $db->f("article_edit_fields");
			$article_settings_data = $db->f("article_settings");
		} else {
			$categories_ids = explode(",", $category_path);
			$top_id = $categories_ids[1];
			$sql  = " SELECT * ";
			$sql .= " FROM " . $table_prefix . "articles_categories ";
			$sql .= " WHERE category_id=" . $db->tosql($top_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$top_name = get_translation($db->f("category_name"));
				$articles_order_column = $db->f("articles_order_column");
				$articles_order_direction = $db->f("articles_order_direction");
				$list_fields = $db->f("article_list_fields");
				$details_fields = $db->f("article_details_fields");
				$edit_fields_data = $db->f("article_edit_fields");
				$article_settings_data = $db->f("article_settings");
			}
		}
	}
	$article_settings = ($article_settings_data) ? json_decode($article_settings_data, true) : array();
	$edit_fields = ($edit_fields_data) ? json_decode($edit_fields_data, true) : array();

	$page_friendly_url = "";
	$page_friendly_params = array("article_id");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	if ($friendly_urls) {
		// retrieve info about friendly url
		$sql  = " SELECT friendly_url FROM " . $table_prefix . "articles WHERE article_id=" . $db->tosql($article_id, INTEGER);
		$db->query($sql);
		if($db->next_record()) {
			$page_friendly_url = $db->f("friendly_url");
			friendly_url_redirect($page_friendly_url, $page_friendly_params);
		}
	}
	if ($friendly_urls && $page_friendly_url) {
		$canonical_url = $page_friendly_url.$friendly_extension;
	} else {
		$canonical_url = "article.php?article_id=".$article_id;
	}
	
	// check if available custom layout for article details page
	$sql  = " SELECT cps.ps_id, cps.key_code, cps.key_rule ";
	$sql .= " FROM (" . $table_prefix . "cms_pages_settings cps ";
	$sql .= " INNER JOIN " . $table_prefix . "cms_pages cp ON cp.page_id=cps.page_id) ";
	$sql .= " WHERE cp.page_code=" . $db->tosql($cms_page_code, TEXT);
	$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER) . ") ";
	$sql .= " AND cps.key_code=" . $db->tosql($article_id, TEXT);
	$sql .= " AND cps.key_rule='custom' ";
	$sql .= " AND cps.key_type='article' ";
	$sql .= " ORDER BY site_id DESC ";
	$cms_ps_id = get_db_value($sql);

	// if there is no custom page then check cms settings for category
	if (!$cms_ps_id) {
		$cms_ps_id = check_category_layout($cms_page_code, $category_path, $category_id);
	}
	include_once("./includes/page_layout.php");

?>