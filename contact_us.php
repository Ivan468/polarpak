<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  contact_us.php                                           ***
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

	$cms_page_code = "contact_us";
	$script_name   = "contact_us.php";
	$current_page  = get_custom_friendly_url("contact_us.php");

	$auto_meta_title = CONTACT_US_MSG;

	include_once("./includes/page_layout.php");

?>