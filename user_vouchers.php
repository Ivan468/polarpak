<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  user_vouchers.php                                        ***
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

	check_user_security("my_vouchers");

	$cms_page_code = "user_vouchers";
	$script_name   = "user_vouchers.php";
	$current_page  = get_custom_friendly_url("user_vouchers.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = MY_VOUCHERS_MSG;

	include_once("./includes/page_layout.php");

?>