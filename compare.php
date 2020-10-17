<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  compare.php                                              ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/navigator.php");
	include_once("./includes/items_properties.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	$cms_page_code = "products_compare";
	$script_name   = "compare.php";
	$current_page  = get_custom_friendly_url("compare.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = COMPARE_TITLE;

	include_once("./includes/page_layout.php");

?>