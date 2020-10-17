<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  ads_compare.php                                          ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/navigator.php");
	include_once("./includes/ads_functions.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	$cms_page_code = "ads_compare";
	$script_name = "ads_compare.php";
	$current_page = get_custom_friendly_url("ads_compare.php");
	$tax_rates = get_tax_rates();

	$auto_meta_title = ADS_COMPARE_TITLE;

	// get global ads settings
	$ads_settings = get_settings("ads");

	include_once("./includes/page_layout.php");

?>