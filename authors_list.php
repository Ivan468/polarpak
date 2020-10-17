<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  authors_list.php                                         ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/friendly_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/ads_functions.php");
	include_once("./includes/navigator.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/admin_messages.php");

	$cms_page_code = "authors_list";
	$script_name   = "authors_list.php";
	$current_page  = get_custom_friendly_url("authors_list.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = AUTHORS_MSG;
	$request_uri = trim(get_request_uri());
	$site_url = get_setting_value($settings, "site_url");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	$authors_filter = ""; $authors_first = "";
	if (preg_match("/\/([0-9a-z\-]{1,7})\-authors(.html)?/i", $request_uri, $matches)) {
		$authors_filter = $matches[1];
	}
	if ($authors_filter == "0-9" || mb_strlen($authors_filter, "UTF-8") == 1) {
		$authors_first = $authors_filter; 
	} else if (preg_match("/^([a-z]{2})\-([a-z]+)$/i", $authors_filter, $matches)) {
		$authors_first = decode_translit($matches[2], $matches[1]);
	}
 
	// meta data variables
	if ($authors_filter) {
		$canonical_url = $site_url . strtolower($authors_filter)."-authors" . $friendly_extension;
	} else {
		$canonical_url = $site_url . "authors_list.php";
	}

	$authors_first_upper = mb_convert_case($authors_first, MB_CASE_UPPER, "UTF-8"); 
	$auto_meta_title = strtoupper($authors_first_upper) . " " . AUTHORS_MSG;
	//$auto_meta_description = "";

	include_once("./includes/page_layout.php");

?>