<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  site_search.php                                          ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/forum_messages.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/ads_functions.php");
	include_once("./includes/navigator.php");

	$cms_page_code = "site_search";
	$script_name   = "site_search.php";
	$current_page  = get_custom_friendly_url("site_search.php");
	$auto_meta_title = FULL_SITE_SEARCH_MSG;
	$tax_rates = get_tax_rates();

	include_once("./includes/page_layout.php");

?>