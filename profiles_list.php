<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  profiles_list.php                                        ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./messages/" . $language_code . "/profiles_messages.php");

	$cms_page_code = "profiles_list";
	$script_name   = "profiles_list.php";
	$current_page  = get_custom_friendly_url("profiles_list.php");
	$auto_meta_title = PROFILES_TITLE;

	include_once("./includes/page_layout.php");

?>