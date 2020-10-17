<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  user_playlists.php                                       ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./includes/navigator.php");
	include_once("./includes/sorter.php");
	include_once("./messages/" . $language_code . "/download_messages.php");

	check_user_session();

	$cms_page_code = "user_playlists";
	$script_name   = "user_playlists.php";
	$current_page  = get_custom_friendly_url("user_playlists.php");
	$auto_meta_title = MY_PLAYLISTS_MSG;

	include_once("./includes/page_layout.php");

?>