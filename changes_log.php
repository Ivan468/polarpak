<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  changes_log.php                                          ***
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
	include_once("./messages/".$language_code."/cart_messages.php");

	$cms_page_code = "products_changes_log";
	$script_name   = "changes_log.php";
	$current_page  = get_custom_friendly_url("changes_log.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = CHANGES_LOG_TITLE;

	include_once("./includes/page_layout.php");

?>