<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  articles_reviews.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/navigator.php");
	include_once("./includes/record.php");
	include_once("./includes/reviews_functions.php");
	include_once("./includes/articles_functions.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");

	$va_version_code = va_version_code();
	
	$cms_page_code = "article_reviews";
	$script_name   = "articles_reviews.php";
	$current_page  = get_custom_friendly_url("articles_reviews.php");

	$user_id = get_session("session_user_id");		
	$user_info = get_session("session_user_info");
	$user_type_id = get_setting_value($user_info, "user_type_id", "");

	if ($va_version_code & 1) {
		include_once("./includes/products_functions.php");
		include_once("./includes/shopping_cart.php");
		$tax_rates = get_tax_rates();
	}

	$is_reviews = true;
	$category_id = get_param("category_id");
	$article_id = get_param("article_id");
	if (!strlen($category_id) && strlen($article_id)) {
		$category_id = VA_Articles::get_category_id($article_id, VIEW_ITEMS_PERM);
	}
	if (!$category_id) {
		$top_id = VA_Articles::get_top_id($article_id);
		$category_id = $top_id;
	}
	$current_category_id = $category_id;

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
	$sql  = " SELECT category_name,short_description,full_description, category_path, parent_category_id, ";
	$sql .= " articles_order_column,articles_order_direction, article_details_fields, ";
	$sql .= " article_list_fields, image_small, image_small_alt, image_large, image_large_alt, ";
	$sql .= " is_rss, rss_on_breadcrumb ";
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
		if ($db->f("is_rss") and $db->f("rss_on_breadcrumb")){
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
		} else {
			$categories_ids = explode(",", $category_path);
			$top_id = $categories_ids[1];
			$sql  = " SELECT category_name, articles_order_column, ";
			$sql .= " articles_order_direction, article_list_fields, ";
			$sql .= " article_details_fields ";
			$sql .= " FROM " . $table_prefix . "articles_categories ";
			$sql .= " WHERE category_id=" . $db->tosql($top_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$top_name = get_translation($db->f("category_name"));
				$articles_order_column = $db->f("articles_order_column");
				$articles_order_direction = $db->f("articles_order_direction");
				$list_fields = $db->f("article_list_fields");
				$details_fields = $db->f("article_details_fields");
			}
		}
	}

	$articles_reviews_settings = get_settings("articles_reviews");

	// check individual page layout settings 
	$cms_ps_id = check_category_layout($cms_page_code, $category_path, $category_id);
	include_once("./includes/page_layout.php");

?>