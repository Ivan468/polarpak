<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  user_product_options.php                                 ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./includes/editgrid.php");
	include_once("./includes/navigator.php");
	include_once("./includes/sorter.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	check_user_security("access_products");

	$cms_page_code = "user_product_options";
	$script_name   = "user_product_options.php";
	$current_page  = get_custom_friendly_url("user_product_options.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = OPTIONS_AND_COMPONENTS_MSG.": ".LIST_MSG;

	include_once("./includes/page_layout.php");

?>