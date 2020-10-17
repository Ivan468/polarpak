<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  ads_details.php                                          ***
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
	include_once("./includes/record.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	$cms_page_code = "ad_details";
	$script_name   = "ads_details.php";
	$current_page  = get_custom_friendly_url("ads_details.php");

	$currency = get_currency();
	$item_id      = get_param("item_id");	
	$category_id  = get_param("category_id");
	if (!strlen($category_id) && strlen($item_id)) {
		$category_id = VA_Ads::get_category_id($item_id, VIEW_ITEMS_PERM);		
		$_GET["category_id"] = $category_id;
	}
	$ad_category_id = $category_id;
	
	// get global ads settings
	$ads_settings = get_settings("ads");

	$sql  = " SELECT category_name, short_description, full_description, image_small, image_large ";
	$sql .= " FROM " . $table_prefix . "ads_categories ";
	$sql .= " WHERE category_id = " . $db->tosql($category_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$va_vars["ads_c"][$category_id] = $db->Record;
		$category_id = $db->f("category_id");
		$current_category = get_translation($db->f("category_name"));

	} else {
		$current_category = "";
		$category_image   = "";
	}

	$page_friendly_url = ""; 
	$page_friendly_params = array("item_id");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	if ($friendly_urls) {
		// retrieve info about friendly url
		$sql  = " SELECT friendly_url FROM " . $table_prefix . "ads_items WHERE item_id=" . $db->tosql($item_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$page_friendly_url = $db->f("friendly_url");
			friendly_url_redirect($page_friendly_url, $page_friendly_params);
		}
	}

	include_once("./includes/page_layout.php");
 
?>