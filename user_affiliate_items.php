<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  user_affiliate_items.php                                 ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./includes/navigator.php");
	include_once("./includes/sorter.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	check_user_security("affiliate_sales");

	$cms_page_code = "user_affiliate_items";
	$script_name   = "user_affiliate_items.php";
	$current_page  = get_custom_friendly_url("user_affiliate_items.php");
	$auto_meta_title = AFFILIATE_SALES_MSG.": ".PRODUCTS_TITLE;

	$tax_rates = get_tax_rates();

	include_once("./includes/page_layout.php");

?>