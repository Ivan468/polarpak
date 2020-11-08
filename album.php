<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  album.php                                                ***
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
	include_once("./messages/" . $language_code . "/admin_messages.php");

	$cms_page_code = "album_details";
	$script_name   = "album.php";
	$current_page  = get_custom_friendly_url("album.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = "{author_name} - {album_name}";
	$request_uri = trim(get_request_uri());
	$site_url = get_setting_value($settings, "site_url");

	$page_friendly_url = "";
	$page_friendly_params = array("album_id");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	if ($friendly_urls) {
		// retrieve info about friendly url
		$sql  = " SELECT friendly_url FROM " . $table_prefix . "albums WHERE album_id=" . $db->tosql($album_id, INTEGER);
		$db->query($sql);
		if($db->next_record()) {
			$page_friendly_url = $db->f("friendly_url");
			friendly_url_redirect($page_friendly_url, $page_friendly_params);
		}
	}
	if ($friendly_urls && $page_friendly_url) {
		$canonical_url = $page_friendly_url.$friendly_extension;
	} else {
		$canonical_url = "album.php?album_id=".$album_id;
	}

	include_once("./includes/page_layout.php");

?>