<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  checkout.php                                             ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/ads_functions.php");
	include_once("./includes/navigator.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	$cms_page_code = "checkout";
	$script_name   = "checkout.php";
	$current_page  = get_custom_friendly_url("checkout.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = CHECKOUT_LOGIN_TITLE;

	include_once("./includes/page_layout.php");

?>