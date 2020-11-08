<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  profiles.php                                             ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./messages/" . $language_code . "/profiles_messages.php");

	$cms_page_code = "profiles_home";
	$script_name   = "profiles.php";
	$current_page  = get_custom_friendly_url("profiles.php");
	$auto_meta_title = PROFILES_TITLE;

	include_once("./includes/page_layout.php");

?>