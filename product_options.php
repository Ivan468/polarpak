<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  product_options.php                                      ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$type = "options";
	include_once("./includes/common.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/download_messages.php");
	include_once("./includes/navigator.php");
	include_once("./includes/items_properties.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/ads_functions.php");

	$display_products = get_setting_value($settings, "display_products", 0);
	if ($display_products == 1) {
		// user need to be logged in before viewing products
		check_user_session();
	}

	$cms_page_code = "product_options";
	$script_name   = "product_options.php";
	$current_page  = get_custom_friendly_url("product_options.php");
	$tax_rates     = get_tax_rates();
	$auto_meta_title = OPTIONS_MSG;

	include_once("./includes/page_layout.php");

?>