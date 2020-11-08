<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  manuals_search.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/navigator.php");
	include_once("./includes/manuals_functions.php");
	include_once("./includes/record.php");
	include_once("./includes/editgrid.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");
	include_once("./messages/" . $language_code . "/support_messages.php");
	include_once("./messages/" . $language_code . "/manuals_messages.php");

	$cms_page_code = "manuals_search_results";
	$script_name   = "manuals_search.php";
	$current_page  = get_custom_friendly_url("manuals_search.php");
	$auto_meta_title = MANUALS_TITLE . ": " . MANUALS_SEARCH_RESULT_MSG;

	include_once("./includes/page_layout.php");
	
?>