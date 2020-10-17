<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  user_wishlist.php                                        ***
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

	check_user_security("my_wishlist");

	$cms_page_code = "user_wishlist";
	$script_name   = "user_wishlist.php";
	$current_page  = get_custom_friendly_url("user_wishlist.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = MY_WISHLIST_MSG;

	include_once("./includes/page_layout.php");

?>