<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  albums_list.php                                          ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/navigator.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	$cms_page_code = "albums_list";
	$script_name   = "albums_list.php";
	$current_page  = get_custom_friendly_url("albums_list.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = ALBUMS_MSG;
	$request_uri = trim(get_request_uri());
	$site_url = get_setting_value($settings, "site_url");

	$albums_filter = "";
	if (preg_match("/\/([0-9a-z\-]{1,7})\-albums.html/i", $request_uri, $matches)) {
		$albums_filter = $matches[1];
	}
	// meta data variables
	if ($albums_filter) {
		$canonical_url = $site_url . strtolower($albums_filter)."-albums.html";
	} else {
		$canonical_url = $site_url . "albums_list.php";
	}
	$albums_filter_upper = strtoupper($albums_filter);
	$auto_meta_title = strtoupper($albums_filter) . " " . ALBUMS_MSG;

	include_once("./includes/page_layout.php");

?>