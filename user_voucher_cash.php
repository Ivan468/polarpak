<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  user_voucher_cash.php                                    ***
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

	check_user_security("my_vouchers");

	$cms_page_code = "user_voucher_cash";
	$script_name   = "user_voucher_cash.php";
	$current_page  = get_custom_friendly_url("user_voucher_cash.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = va_constant("CASH_OUT_VOUCHER_MSG");

	include_once("./includes/page_layout.php");

?>