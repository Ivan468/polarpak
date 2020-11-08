<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  products_search.php                                      ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/
                           

	$type = "list";
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


	$cms_page_code = "products_search_results";
	$script_name = "products_search.php";
	$current_page = "products_search.php";
	$tax_rates = get_tax_rates();

	$tax_rates = get_tax_rates();
	$category_id = get_param("category_id");
	$search_category_id = get_param("search_category_id");
	if (strlen($search_category_id)) { 
		$category_id = $search_category_id; 
	} elseif (!strlen($category_id)) { 
		$category_id = "0"; 
	}
	$manf = get_param("manf");

	$list_template = ""; $current_category = ""; 
	$page_friendly_url = ""; $page_friendly_params = array();
	$show_sub_products = false; $category_path = "";

	// retrieve info about current category
	$sql  = " SELECT * FROM " . $table_prefix . "categories WHERE category_id=" . $db->tosql($category_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$current_category = get_translation($db->f("category_name"));
		$short_description = get_translation($db->f("short_description"));
		$full_description = get_translation($db->f("full_description"));
		$show_sub_products = $db->f("show_sub_products");
		$category_path = $db->f("category_path") . $category_id . ",";

		$list_template = $db->f("list_template");

		$meta_title = get_translation($db->f("meta_title"));
		$meta_description = get_translation($db->f("meta_description"));
		$meta_keywords = get_translation($db->f("meta_keywords"));

		// check if we need to generate auto meta data 
		if (!strlen($meta_title)) { $auto_meta_title = $current_category; }
		if (!strlen($meta_description)) {
			if (strlen($short_description)) {
				$auto_meta_description = $short_description;
			} elseif (strlen($full_description)) {
				$auto_meta_description = $full_description;
			}		
		}

	} elseif (strlen($manf)) {
		$sql = "SELECT manufacturer_name, friendly_url FROM " . $table_prefix . "manufacturers WHERE manufacturer_id=" . $db->tosql($manf, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$manufacturer_name = $db->f("manufacturer_name");
			$manf_friendly_url = $db->f("friendly_url");

			$current_category  = $manufacturer_name;
			$list_template     = "block_products_list.html";
			$auto_meta_title = $current_category; 
		}
	} else {
		$current_category = PRODUCTS_TITLE;
		$list_template    = "block_products_list.html";
		$auto_meta_title = $current_category; 
	}

	include_once("./includes/page_layout.php");

?>