<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  user_playlist.php                                        ***
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

	check_user_session();

	$cms_page_code = "user_playlist_edit";
	$script_name   = "user_playlist.php";
	$current_page  = get_custom_friendly_url("user_playlist.php");
	$auto_meta_title = "{MY_PLAYLIST_MSG} :: {EDIT_MSG}";

	include_once("./includes/page_layout.php");

?>