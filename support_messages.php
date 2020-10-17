<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  support_messages.php                                     ***
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
	include_once ("./messages/" . $language_code . "/support_messages.php");


	$cms_page_code = "ticket_reply";
	$script_name   = "support_messages.php";
	$current_page  = get_custom_friendly_url("support_messages.php");
	$auto_meta_title = SUPPORT_TITLE;

	$tax_rates = get_tax_rates();

	include_once("./includes/page_layout.php");

?>