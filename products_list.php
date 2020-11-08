<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  products_list.php                                        ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$type = "list";
	$cms_page_code = "products_list";
	$script_name   = "products_list.php";

	include_once("./includes/common.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");
	include_once("./messages/" . $language_code . "/download_messages.php");
	include_once("./includes/sorter.php");
	include_once("./includes/navigator.php");
	include_once("./includes/items_properties.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/filter_functions.php");
	include_once("./includes/previews_functions.php");

	$display_products = get_setting_value($settings, "display_products", 0);
	if ($display_products == 1) {
		// user need to be logged in before viewing products
		check_user_session();
	}

	$tax_rates = get_tax_rates();
	$current_page  = get_custom_friendly_url("products_list.php");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	
	$manf = get_param("manf");
	$pn_pr = get_param("pn_pr");
	$category_id        = get_param("category_id");
	$search_category_id = get_param("search_category_id");
	if (strlen($search_category_id)) {
		$category_id = $search_category_id;
	} elseif (!strlen($category_id)) {
		$category_id = 0;
	}

	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", "");

	if ($category_id) {
		if (VA_Categories::check_exists($category_id)) {
			if (!VA_Categories::check_permissions($category_id, VIEW_CATEGORIES_ITEMS_PERM)) {

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
		set_session("products_category_id", $category_id);
	}
		

	$list_template = ""; $current_category = "";   
	$page_friendly_url = ""; $page_friendly_params = array();
	$show_sub_products = false; $category_path = "";

	// retrieve info about current category
	$sql  = " SELECT * FROM " . $table_prefix . "categories ";	
	$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$category_data = $db->Record;
		$category_id = $category_data["category_id"];
		$redirect_category_id = $category_data["redirect_category_id"];
		// check if we need to redirect user to different category
		if ($redirect_category_id) {
			$sql  = " SELECT * FROM " . $table_prefix . "categories ";	
			$sql .= " WHERE category_id=" . $db->tosql($redirect_category_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$redirect_friendly_url = $db->f("friendly_url");
				if ($friendly_urls && $redirect_friendly_url) {
					$redirect_friendly_url .= $friendly_extension;
				} else {
					$redirect_friendly_url = "products_list.php?category_id=".urlencode($redirect_category_id);
				}
				header("HTTP/1.1 302 Found");
				header("Location: " . $redirect_friendly_url);
				exit;
			}
		}
		$current_category = get_translation($category_data["category_name"]);
		$page_friendly_url = $category_data["friendly_url"];
		if ($page_friendly_url) {
			$page_friendly_params[] = "category_id";
			friendly_url_redirect($page_friendly_url, $page_friendly_params);
		}
		$short_description = get_translation($category_data["short_description"]);
		$full_description = get_translation($category_data["full_description"]);
		$show_sub_products = $category_data["show_sub_products"];
		$category_path = $category_data["category_path"] . $category_id . ",";

		$list_template = $category_data["list_template"];
		if (!@file_exists($list_template)) { 
			if (!@file_exists($settings["templates_dir"]."/".$list_template) && !@file_exists("templates/user/".$list_template)) { $list_template = ""; }
		}

		$meta_title = get_translation($category_data["meta_title"]);
		$meta_description = get_translation($category_data["meta_description"]);
		$meta_keywords = get_translation($category_data["meta_keywords"]);
		$total_views = $category_data["total_views"];

		// check if we need to generate auto meta data 
		if (!strlen($meta_title)) { $auto_meta_title = $current_category; }
		if (!strlen($meta_description)) {
			if (strlen($short_description)) {
				$auto_meta_description = $short_description;
			} elseif (strlen($full_description)) {
				$auto_meta_description = $full_description;
			}		
		}

		// build canonical url
		if ($friendly_urls && $page_friendly_url) {
			$canonical_url = $page_friendly_url.$friendly_extension;
		} else {
			$canonical_url = "products_list.php?category_id=".urlencode($category_id);
		}

		// update total views for categories
		$products_cats_viewed = get_session("session_products_cats_viewed");
		if (!isset($products_cats_viewed[$category_id])) {
			$sql  = " UPDATE " . $table_prefix . "categories SET total_views=" . $db->tosql(($total_views + 1), INTEGER);
			$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
			$db->query($sql);

			$products_cats_viewed[$category_id] = true;
			set_session("session_products_cats_viewed", $products_cats_viewed);
		}
	} elseif (strlen($manf)) {
		$sql = "SELECT manufacturer_name, friendly_url FROM " . $table_prefix . "manufacturers WHERE manufacturer_id=" . $db->tosql($manf, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$manufacturer_name = $db->f("manufacturer_name");
			$page_friendly_url = $db->f("friendly_url");
			if ($page_friendly_url) {
				$page_friendly_params[] = "manf";
				friendly_url_redirect($page_friendly_url, $page_friendly_params);
			}
			$current_category  = $manufacturer_name;
			$list_template     = "block_products_list.html";
			$auto_meta_title   = $current_category; 
			// build canonical url
			if ($friendly_urls && $page_friendly_url) {
				$canonical_url = $page_friendly_url.$friendly_extension;
			} else {
				$canonical_url = "products_list.php?manf=".urlencode($manf);
			}
		}
	} else {
		$category_path = "0";
		$current_category = PRODUCTS_TITLE;
		$list_template    = "block_products_list.html";
		$auto_meta_title = $current_category; 
		$canonical_url   = get_custom_friendly_url("products_list.php");
	}

	
	// add page parameter to canonical url if it's greater than 1
	if ($pn_pr > 1) {
		if(strpos($canonical_url, "?")) {
			$canonical_url .= "&";
		} else {
			$canonical_url .= "?";
		}
		$canonical_url .= "pn_pr=".urlencode($pn_pr);
	}

	// check individual page layout settings 
	$cms_ps_id = check_category_layout($cms_page_code, $category_path, $category_id);
	include_once("./includes/page_layout.php");

?>