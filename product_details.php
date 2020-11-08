<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  product_details.php                                      ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$type = "details";
	$cms_page_code = "product_details";
	$script_name   = "product_details.php";

	include_once("./includes/common.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");
	include_once("./messages/" . $language_code . "/download_messages.php");
	include_once("./includes/navigator.php");
	include_once("./includes/items_properties.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/previews_functions.php");
	
	$access_out_stock = get_setting_value($settings, "access_out_stock", 0);
	$display_products = get_setting_value($settings, "display_products", 0);
	if ($display_products == 1) {
		// user need to be logged in before viewing products
		check_user_session();
	}

	$current_page  = get_custom_friendly_url("product_details.php");
	$tax_rates     = get_tax_rates();

	$current_category = "";
	$page_friendly_url = ""; 
	$page_friendly_params = array("item_id");
	$item_id = get_param("item_id");
	if (!strlen($item_id)) {
		// check item_id by code
		$item_code = get_param("item_code");
		$manufacturer_code = get_param("manufacturer_code");
		if (strlen($item_code)) {
			$sql = " SELECT item_id FROM " . $table_prefix . "items WHERE item_code=" . $db->tosql($item_code, TEXT);
			$item_id = get_db_value($sql);
		} elseif (strlen($manufacturer_code)) {
			$sql = " SELECT item_id FROM " . $table_prefix . "items WHERE manufacturer_code=" . $db->tosql($manufacturer_code, TEXT);
			$item_id = get_db_value($sql);
		}
		if ($item_id) {
			$_GET["item_id"] = $item_id;
		}
	}

	if (!VA_Products::check_exists($item_id, $access_out_stock)) {
		header("HTTP/1.0 404 Not Found");
		exit;
	}

	$category_id = get_param("category_id");
	$session_category_id = get_session("products_category_id");
	if (!strlen($category_id) && $session_category_id) {
		// check if product assigned to this category
		$sql  = " SELECT category_id FROM " . $table_prefix ."items_categories ";
		$sql .= " WHERE category_id=".$db->tosql($session_category_id, INTEGER);
		$sql .= " AND item_id=".$db->tosql($item_id, INTEGER);
		$category_id = get_db_value($sql);
	}
	if (!strlen($category_id) && strlen($item_id)) {		
		$category_id = VA_Products::get_category_id($item_id, VIEW_ITEMS_PERM);
		$_GET["category_id"] = $category_id;
	}
	// retrieve info about current category
	$sql  = " SELECT * ";
	$sql .= " FROM " . $table_prefix . "categories ";
	$sql .= " WHERE category_id = " . $db->tosql($category_id, INTEGER);	
	$db->query($sql);
	if ($db->next_record()) {
		// global array to use in different blocks
		if(!isset($va_data)) { $va_data = array(); }
		$va_data["product_category"] = $db->Record;
		$current_category = get_translation($db->f("category_name"));
	}

	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	if ($friendly_urls) {
		// retrieve info about friendly url
		$sql  = " SELECT friendly_url FROM " . $table_prefix . "items WHERE item_id=" . $db->tosql($item_id, INTEGER);
		$db->query($sql);
		if($db->next_record()) {
			$page_friendly_url = $db->f("friendly_url");
			friendly_url_redirect($page_friendly_url, $page_friendly_params);
		}
	}
	if ($friendly_urls && $page_friendly_url) {
		$canonical_url = $page_friendly_url.$friendly_extension;
	} else {
		$canonical_url = "product_details.php?item_id=".urlencode($item_id);
	}

	include_once("./includes/page_layout.php");
