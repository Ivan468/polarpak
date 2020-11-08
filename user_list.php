<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  user_list.php                                            ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/
                           

	$type = "list";
	include_once("./includes/common.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");
	include_once("./includes/navigator.php");
	include_once("./includes/record.php");
	include_once("./includes/sorter.php");
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

	$cms_page_code = "user_products_list";
	$script_name   = "user_list.php";
	$current_page  = get_custom_friendly_url("user_list.php");
	$confirm_add = get_setting_value($settings, "confirm_add", 1);

	$tax_rates = get_tax_rates();
	$user = get_param("user");

	$list_template = ""; $meta_title = "";	$meta_description = "";
	$page_friendly_url = ""; $page_friendly_params = array();
	$current_category = "";  $show_sub_products = false; $category_path = "";
	$merchant_type_id = ""; $merchant_name = ""; $merchant_email = ""; $merchant_info = "";
	// retrieve info about current category
	$sql  = " SELECT u.* FROM ". $table_prefix . "users u "; 
	$sql .= " WHERE u.user_id=" . $db->tosql($user, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$merchant_id = $db->f("user_id");
		$merchant_info = $db->Record;
		$merchant_info["registration_date"] = $db->f("registration_date", DATETIME);
		$merchant_info["last_visit_page"] = $db->f("last_visit_page", DATETIME);
		$last_visit_ts = 0;
		$last_visit_date = $db->f("last_visit_date", DATETIME);
		if (is_array($last_visit_date)) {
			$last_visit_ts = va_timestamp($last_visit_date);
		}
		$merchant_info["last_visit_ts"] = $last_visit_ts;

		$merchant_type_id = $db->f("user_type_id");
		$merchant_name = get_translation($db->f("company_name"));
		if (!strlen($merchant_name)) {
			$merchant_name = get_translation($db->f("name"));
		}
		if (!strlen($merchant_name)) {
			$merchant_name = get_translation($db->f("login"));
		}
		$merchant_email = $db->f("email");
		$current_category = $merchant_name . ": " . PRODUCTS_TITLE;
		$page_friendly_url = $db->f("friendly_url");
		if ($page_friendly_url) {
			$page_friendly_params[] = "user";
			friendly_url_redirect($page_friendly_url, $page_friendly_params);
		}
		$short_description = get_translation($db->f("short_description"));
		$full_description = get_translation($db->f("full_description"));

		// check if we need to generate auto meta data 
		if (!strlen($meta_title)) { $auto_meta_title = $merchant_name; }
		if (!strlen($meta_description)) {
			if (strlen($short_description)) {
				$auto_meta_description = $short_description;
			} elseif (strlen($full_description)) {
				$auto_meta_description = $full_description;
			}		
		}

	} else {
		header("Location: " . get_custom_friendly_url("index.php"));
		exit;
	}

	include_once("./includes/page_layout.php");

?>