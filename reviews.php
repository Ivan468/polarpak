<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  reviews.php                                              ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/navigator.php");
	include_once("./includes/record.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/reviews_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");

	$display_products = get_setting_value($settings, "display_products", 0);
	if ($display_products == 1) {
		// user need to be logged in before viewing products
		check_user_session();
	}
	$item_id = get_param("item_id");
	if (!VA_Products::check_permissions($item_id, VIEW_ITEMS_PERM)) {
		header ("Location: " . get_custom_friendly_url("user_login.php") . "?type_error=2");
		exit;
	}

	$cms_page_code = "product_reviews";
	$script_name   = "reviews.php";
	$current_page  = get_custom_friendly_url("reviews.php");
	$tax_rates     = get_tax_rates();
	$auto_meta_title = REVIEWS_MSG;
	$is_reviews = true;

	$canonical_url = "reviews.php?item_id=".urlencode($item_id);
		
	include_once("./includes/page_layout.php");

?>