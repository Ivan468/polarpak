<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  ads_search.php                                           ***
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
	$current_page = "ads_search.php";
	$tax_rates = get_tax_rates();

	$cms_page_code = "ads_search_advanced";
	$script_name   = "ads_search.php";
	$current_page  = get_custom_friendly_url("ads_search.php");
	$auto_meta_title = ADVANCED_SEARCH_TITLE;

	// get global ads settings
	$ads_settings = get_settings("ads");

	include_once("./includes/page_layout.php");

?>