<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  index.php                                                ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$type = "list";
	$cms_page_code = "index";
	$script_name = "index.php";

	include_once("./includes/common.php");
	include_once("./includes/navigator.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/forum_messages.php");
	include_once("./messages/" . $language_code . "/manuals_messages.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/ads_functions.php");
	include_once("./includes/previews_functions.php");
	include_once("./includes/items_properties.php");

	$cms_key_type = ""; //enterprise|standard|light|cms|helpdesk
	$current_page = get_custom_friendly_url("index.php");
	$tax_rates = get_tax_rates();
	$page_friendly_url = ""; $page_friendly_params = array();

	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", "");

	$canonical_url = $site_url;
	include_once("./includes/page_layout.php");
