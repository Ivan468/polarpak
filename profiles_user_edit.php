<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  profiles_user_edit.php                                   ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./messages/" . $language_code . "/profiles_messages.php");

	check_user_security("profiles");

	$cms_page_code = "profiles_user_edit";
	$script_name   = "profiles_user_edit.php";
	$current_page  = get_custom_friendly_url("profiles_user_edit.php");
	$auto_meta_title = MY_PROFILES_MSG.": ".EDIT_MSG;

	include_once("./includes/page_layout.php");

?>