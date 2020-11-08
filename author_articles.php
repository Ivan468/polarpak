<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  author_articles.php                                      ***
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

	$author_friendly_url = ""; $articles_prefix = "";
	if (preg_match("/\/([0-9a-z\-_]+)\-(lyrics|articles).html/i", $request_uri, $matches)) {
		// check author_id value from friendly url
		$author_friendly_url = $matches[1];
		$articles_prefix = $matches[2];
		$sql  = " SELECT author_id FROM " . $table_prefix . "authors ";
		$sql .= " WHERE friendly_url=" . $db->tosql($author_friendly_url, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$author_id = $db->f("author_id");
			$_GET["author_id"] = $author_id;
		} else {
			header("Location: authors_list.php");
			exit;
		}
	}

	$author_id = get_param("author_id");
	// check if author available for current site
	$sql  = " SELECT a.*  ";
	$sql .= " FROM (" . $table_prefix . "authors a ";
	$sql .= " LEFT JOIN " . $table_prefix . "authors_sites aus ON a.author_id=aus.author_id) ";
	$sql .= " WHERE a.author_id=" . $db->tosql($author_id, INTEGER);
	$sql .= " AND (a.sites_all=1 OR aus.site_id=" . $db->tosql($site_id, INTEGER) . ") ";
	$db->query($sql);
	if (!$db->next_record()) {
		header("Location: authors_list.php");
		exit;
	}

	// meta data variables
	if ($author_friendly_url) {
		$canonical_url = $site_url . strtolower($author_friendly_url)."-".$articles_prefix.".html";
	} else {
		$canonical_url = $site_url . "author_articles.php";
	}

	include_once("./includes/page_layout.php");

?>