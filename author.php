<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  author.php                                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/ads_functions.php");
	include_once("./includes/navigator.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/admin_messages.php");

	$cms_page_code = "author_articles";
	$script_name   = "author_articles.php";
	$current_page  = get_custom_friendly_url("authors_articles.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = "{author_name}: {ARTICLES_TITLE}";
	$request_uri = trim(get_request_uri());
	$site_url = get_setting_value($settings, "site_url");

	$page_friendly_url = ""; 
	$page_friendly_params = array("author_id");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	$author_id = get_param("author_id");
	// check if author available for current site
	$sql  = " SELECT a.*  ";
	$sql .= " FROM (" . $table_prefix . "authors a ";
	$sql .= " LEFT JOIN " . $table_prefix . "authors_sites aus ON a.author_id=aus.author_id) ";
	$sql .= " WHERE a.author_id=" . $db->tosql($author_id, INTEGER);
	$sql .= " AND (a.sites_all=1 OR aus.site_id=" . $db->tosql($site_id, INTEGER) . ") ";
	$db->query($sql);
	if ($db->next_record()) {
		$page_friendly_url = $db->f("friendly_url");
		if ($page_friendly_url) {
			friendly_url_redirect($page_friendly_url, $page_friendly_params);
		}
	} else {
		header("Location: authors_list.php");
		exit;
	}

	// meta data variables
	if ($friendly_urls && $page_friendly_url) {
		$canonical_url = $page_friendly_url.$friendly_extension;
	} else {
		$canonical_url = "author.php?author_id=".$author_id;
	}

	include_once("./includes/page_layout.php");

?>