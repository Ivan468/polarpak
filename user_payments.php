<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  user_payments.php                                        ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./includes/navigator.php");
	include_once("./includes/sorter.php");
	include_once("./includes/shopping_cart.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	check_user_security("my_payments");

	$cms_page_code = "user_payments";
	$script_name   = "user_payments.php";
	$current_page  = get_custom_friendly_url("user_payments.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = COMMISSION_PAYMENTS_MSG.": ".LIST_MSG;

	include_once("./includes/page_layout.php");

?>