<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  basket.php                                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$type = "list";
	include_once("./includes/common.php");
	include_once("./includes/navigator.php");
	include_once("./includes/items_properties.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/download_messages.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/ads_functions.php");
	include_once("./includes/order_items.php");

	$cms_page_code = "cart";
	$script_name   = "basket.php";
	$current_page   = get_custom_friendly_url("basket.php");
	$page_friendly_url = get_custom_friendly_url("basket.php");
	$page_friendly_params = "";
	$page_name = "basket";
	$tax_rates = get_tax_rates();
	$auto_meta_title = CART_TITLE;

	include_once("./includes/page_layout.php");

?>