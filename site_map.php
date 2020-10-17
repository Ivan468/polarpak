<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  site_map.php                                             ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	@set_time_limit(900);
	include_once("./includes/common.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/forum_messages.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/ads_functions.php");
	include_once("./includes/navigator.php");
	
	$cms_page_code = "site_map";
	$script_name   = "site_map.php";
	$current_page  = get_custom_friendly_url("site_map.php");
	$auto_meta_title = SITE_MAP_TITLE;

	include_once("./includes/page_layout.php");

?>