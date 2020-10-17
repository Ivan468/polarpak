<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  order_confirmation.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$cms_page_code = "order_confirmation";

	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/ads_functions.php");
	include_once("./includes/order_items.php");
	include_once("./includes/order_links.php");
	include_once("./includes/parameters.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	$script_name   = "order_confirmation.php";
	$current_page  = get_custom_friendly_url("order_confirmation.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = CHECKOUT_CONFIRM_TITLE;

	include_once("./includes/page_layout.php");

?>