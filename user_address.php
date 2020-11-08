<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  user_address.php                                         ***
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

	check_user_security("user_addresses");

	$cms_page_code = "user_address";
	$script_name   = "user_address.php";
	$current_page  = get_custom_friendly_url("user_address.php");
	$auto_meta_title = MY_ADDRESSES_MSG.": ".EDIT_MSG;

	include_once("./includes/page_layout.php");

?>