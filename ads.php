<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  ads.php                                                  ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/
                           

	include_once("./includes/common.php");
	include_once("./includes/navigator.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/ads_properties.php");
	include_once("./includes/ads_functions.php");
	include_once("./includes/cms_functions.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	$cms_page_code      = "ads_list";
	$script_name        = "ads.php";
	$current_page       = get_custom_friendly_url("ads.php");
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
		if (VA_Ads_Categories::check_exists($category_id)) {
			if (!VA_Ads_Categories::check_permissions($category_id, VIEW_CATEGORIES_ITEMS_PERM)) {
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
	}

	// get global ads settings
	$ads_settings = get_settings("ads");
	$ad_category_id = $category_id;

	$page_friendly_url = ""; 
	$page_friendly_params = array("category_id");
	$category_description = ""; $category_image = "";

	// retrieve info about current category
	$sql  = " SELECT category_name, friendly_url, short_description, full_description,";
	$sql .= " category_path, parent_category_id, image_small, image_large, total_views ";
	$sql .= " FROM " . $table_prefix . "ads_categories ";
	$sql .= " WHERE category_id = " . $db->tosql($category_id, INTEGER, true, false);
	$db->query($sql);
	if ($db->next_record()) {
		$va_vars["ads_c"][$category_id] = $db->Record;
		$category_path = $db->f("category_path") . $category_id;
		$current_category = get_translation($db->f("category_name"));
		$page_friendly_url = $db->f("friendly_url");
		friendly_url_redirect($page_friendly_url, $page_friendly_params);
		$ad_category_short_description = get_translation($db->f("short_description"));
		$ad_category_full_description = get_translation($db->f("full_description"));
		$parent_category_id = $db->f("parent_category_id");
		$total_views = $db->f("total_views");

		$auto_meta_title = $current_category;

		if (strlen($ad_category_short_description)) {
			$auto_meta_description = $ad_category_short_description;
		} elseif (strlen($ad_category_full_description)) {
			$auto_meta_description = $ad_category_full_description;
		}		
		// update total views for ads categories
		$ads_cats_viewed = get_session("session_ads_cats_viewed");
		if (!isset($ads_cats_viewed[$category_id])) {
			$sql  = " UPDATE " . $table_prefix . "ads_categories SET total_views=" . $db->tosql(($total_views + 1), INTEGER);
			$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
			$db->query($sql);

			$ads_cats_viewed[$category_id] = true;
			set_session("session_ads_cats_viewed", $ads_cats_viewed);
		}
	} else {
		$category_path = "0";
		$auto_meta_title = ADS_TITLE;
		if ($category_id) {
			header ("Location: " . get_custom_friendly_url("ads.php"));
			exit;
		}
	}

	// check individual page layout settings 
	$cms_ps_id = check_category_layout($cms_page_code, $category_path, $category_id);
	include_once("./includes/page_layout.php");

?>