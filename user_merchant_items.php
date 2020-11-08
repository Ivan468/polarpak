<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  user_merchant_items.php                                  ***
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

	check_user_security("merchant_sales");

	$cms_page_code = "user_merchant_items";
	$script_name   = "user_merchant_items.php";
	$current_page  = get_custom_friendly_url("user_merchant_items.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = MERCHANT_SALES_MSG.": ".MERCHANT_SALES_MSG;

	include_once("./includes/page_layout.php");

?>