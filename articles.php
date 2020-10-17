<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  articles.php                                             ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/navigator.php");
	include_once("./includes/articles_functions.php");
	include_once("./includes/cms_functions.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");

	$va_version_code = va_version_code();

	if ($va_version_code & 1) {
		include_once("./includes/products_functions.php");
		include_once("./includes/shopping_cart.php");
		$tax_rates = get_tax_rates();
	}

	$cms_page_code = "articles_list";
	$script_name   = "articles.php";
	$current_page  = get_custom_friendly_url("articles.php");

	$category_id = get_param("category_id");
	$search_category_id = get_param("search_category_id");
	if (strlen($search_category_id)) {
		$category_id = $search_category_id;
	}
	$current_category_id = $category_id;
	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", "");
	
	if ($category_id) {
		if (VA_Articles_Categories::check_exists($category_id)) {
			if (!VA_Articles_Categories::check_permissions($category_id, VIEW_CATEGORIES_ITEMS_PERM)) {
				$secure_user_login = get_setting_value($settings, "secure_user_login", 0);
				if ($secure_user_login) {
					$user_login_url = $secure_url . get_custom_friendly_url("user_login.php");
				} else {
					$user_login_url = $site_url . get_custom_friendly_url("user_login.php");
				}
				$return_page = get_request_uri();
				header ("Location: " . $user_login_url . "?return_page=" . urlencode($return_page) . "&type_error=2&ssl=".intval($is_ssl));
				exit;
			}
		} else {
			header("Location: " . $site_url);
			exit;
		}
	} else {
		header("Location: " . $site_url);
		exit;
	}

	$page_friendly_url = "";
	$page_friendly_params = array("category_id");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	
	// retrieve info about current category
	$sql  = " SELECT category_name,friendly_url,category_path, parent_category_id, alias_category_id, ";
	$sql .= " articles_order_column, articles_order_direction, article_list_fields, article_details_fields, ";
	$sql .= " image_small, image_small_alt, image_large, image_large_alt, ";
	$sql .= " short_description, full_description, meta_title, meta_keywords, meta_description, total_views, ";
	$sql .= " is_rss, rss_on_breadcrumb, is_remote_rss, remote_rss_url, remote_rss_date_updated, remote_rss_ttl, remote_rss_refresh_rate";
	$sql .= " FROM " . $table_prefix . "articles_categories";
	$sql .= " WHERE category_id = " . $db->tosql($category_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$category_info = $db->Record;
		$alias_category_id = $category_info["alias_category_id"];
		$remote_rss_date_updated = $db->f("remote_rss_date_updated", DATETIME);
		// check if we need to redirect user to different category
		if ($alias_category_id) {
			$sql  = " SELECT friendly_url FROM " . $table_prefix . "articles_categories ";	
			$sql .= " WHERE category_id=" . $db->tosql($alias_category_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$friendly_url = $db->f("friendly_url");
				if ($friendly_urls && $friendly_url) {
					$redirect_page = $friendly_url . $friendly_extension;
				} else {
					$redirect_page = "articles.php?category_id=".urlencode($alias_category_id);
				}
				if ($is_ssl) {
					$redirect_url = $secure_url . $redirect_page;
				} else {
					$redirect_url = $site_url . $redirect_page;
				}
				header("Location: " . $redirect_url);
				exit;
			}
		}

		$current_category = get_translation($category_info["category_name"]);
		$page_friendly_url = $category_info["friendly_url"];
		friendly_url_redirect($page_friendly_url, $page_friendly_params);
		$short_description = get_translation($category_info["short_description"]);
		$full_description = get_translation($category_info["full_description"]);
		$image_small = $category_info["image_small"];
		$image_small_alt = $category_info["image_small_alt"];
		$image_large = $category_info["image_large"];
		$image_large_alt = $category_info["image_large_alt"];
		$parent_category_id = $category_info["parent_category_id"];
		$category_path = $category_info["category_path"] . $category_id;
		$total_views = $category_info["total_views"];
		$is_remote_rss = $category_info["is_remote_rss"];
		$remote_rss_url = $category_info["remote_rss_url"];
		$remote_rss_refresh_rate = $category_info["remote_rss_refresh_rate"];
		$remote_rss_ttl = $category_info["remote_rss_ttl"];

		if ($category_info["is_rss"] && $category_info["rss_on_breadcrumb"]){
			$rss_on_breadcrumb = true;
		} else {
			$rss_on_breadcrumb = false;
		}
		// meta data
		$meta_title = get_translation($category_info["meta_title"]);
		$meta_description = get_translation($category_info["meta_description"]);
		$meta_keywords = get_translation($category_info["meta_keywords"]);

		// check if we need to generate auto meta data 
		if (!strlen($meta_title)) { $auto_meta_title = $current_category; }
		if (!strlen($meta_description)) {
			if (strlen($short_description)) {
				$auto_meta_description = $short_description;
			} elseif (strlen($full_description)) {
				$auto_meta_description = $full_description;
			}		
		}
		
		if ($parent_category_id == 0) {
			$top_id   = $category_id;
			$top_name = $current_category;
			$articles_order_column = $category_info["articles_order_column"];
			$articles_order_direction = $category_info["articles_order_direction"];
			$list_fields = $category_info["article_list_fields"];
			$list_fields = $category_info["article_details_fields"];
		} else {
			$categories_ids = explode(",", $category_path);
			$top_id = $categories_ids[1];
			$sql  = " SELECT category_name, articles_order_column,articles_order_direction, article_list_fields, article_details_fields ";
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
				
		// check for remote RSS links
		if ($is_remote_rss == 1) {
			$articles_imported = articles_import_rss($is_remote_rss, $remote_rss_url, $remote_rss_date_updated, $remote_rss_refresh_rate, $remote_rss_ttl);
		}

		// update total views for articles categories
		$articles_cats_viewed = get_session("session_articles_cats_viewed");
		if (!isset($articles_cats_viewed[$category_id])) {
			$sql  = " UPDATE " . $table_prefix . "articles_categories SET total_views=" . $db->tosql(($total_views + 1), INTEGER);
			$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
			$db->query($sql);

			$articles_cats_viewed[$category_id] = true;
			set_session("session_articles_cats_viewed", $articles_cats_viewed);
		}
	}
	if ($friendly_urls && $page_friendly_url) {
		$canonical_url = $page_friendly_url.$friendly_extension;
	} else {
		$canonical_url = "articles.php?category_id=".$category_id;
	}

	// check individual page layout settings 
	$cms_ps_id = check_category_layout($cms_page_code, $category_path, $category_id);
	include_once("./includes/page_layout.php");

?>