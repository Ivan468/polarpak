<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  user_order_update.php                                    ***
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
	include_once("./includes/order_items.php");
	include_once("./includes/order_links.php");
	include_once("./includes/parameters.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	check_user_security("my_orders");

	$cms_page_code = "user_order_update";
	$script_name   = "user_order_update.php";
	$current_page  = get_custom_friendly_url("user_order_update.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = MY_ORDERS_MSG.": ".EDIT_MSG;

	include_once("./includes/page_layout.php");

?>