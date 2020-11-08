<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  music_search.php                                         ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
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

	$cms_page_code = "music_search";
	$script_name   = "music_search.php";
	$current_page  = get_custom_friendly_url("music_search.php");
	$auto_meta_title = SEARCH_TITLE;
	$tax_rates = get_tax_rates();

	include_once("./includes/page_layout.php");

?>