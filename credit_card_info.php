<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  credit_card_info.php                                     ***
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
	include_once("./includes/order_items.php");
	include_once("./includes/order_links.php");
	include_once("./includes/parameters.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	$cms_page_code = "order_payment_details";
	$script_name   = "credit_card_info.php";
	$current_page  = get_custom_friendly_url("credit_card_info.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = CHECKOUT_PAYMENT_TITLE;

	include_once("./includes/page_layout.php");

?>
