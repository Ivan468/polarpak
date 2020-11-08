<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  order_info.php                                           ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$cms_page_code = "order_info";

	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/ads_functions.php");
	include_once("./includes/order_items.php");
	include_once("./includes/order_links.php");
	include_once("./includes/order_items_properties.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/profile_functions.php");
	include_once("./includes/parameters.php");
	include_once("./includes/navigator.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	$script_name   = "order_info.php";
	$current_page  = get_custom_friendly_url("order_info.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = CHECKOUT_INFO_TITLE;

	include_once("./includes/page_layout.php");

?>