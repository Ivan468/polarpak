<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  cart_save.php                                            ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./includes/items_properties.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/ads_functions.php");
	include_once("./includes/navigator.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	$cms_page_code = "cart_save";
	$script_name   = "cart_save.php";
	$current_page  = get_custom_friendly_url("cart_save.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = SAVE_CART_TITLE;

	include_once("./includes/page_layout.php");

?>