<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  user_messages.php                                        ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./includes/navigator.php");
	include_once("./includes/sorter.php");
	include_once("./messages/" . $language_code . "/download_messages.php");

	check_user_security("user_messages");

	$cms_page_code = "user_messages";
	$script_name   = "user_messages.php";
	$current_page  = get_custom_friendly_url("user_messages.php");
	$auto_meta_title = MY_MESSAGES_MSG;

	include_once("./includes/page_layout.php");

?>