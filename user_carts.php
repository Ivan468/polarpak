<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  user_carts.php                                           ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
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

	check_user_security("my_carts");

	$cms_page_code = "user_carts";
	$script_name   = "user_carts.php";
	$current_page  = get_custom_friendly_url("user_carts.php");
	$auto_meta_title = MY_SAVED_CARTS_MSG;

	$tax_rates = get_tax_rates();

	include_once("./includes/page_layout.php");

?>