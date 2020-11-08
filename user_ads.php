<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  user_ads.php                                             ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./includes/navigator.php");
	include_once("./includes/sorter.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/friendly_functions.php");
	include_once("./includes/ads_functions.php");
	include_once("./messages/" . $language_code . "/download_messages.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	check_user_security("add_ad");

	$cms_page_code = "user_ads";
	$script_name   = "user_ads.php";
	$current_page  = get_custom_friendly_url("user_ads.php");
	$user_name = get_session("session_user_name");
	$auto_meta_title = MY_ADS_MSG.": ".LIST_MSG;

	$tax_rates = get_tax_rates();

	include_once("./includes/page_layout.php");

?>