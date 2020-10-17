<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  profiles_view.php                                        ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./messages/" . $language_code . "/profiles_messages.php");

	$cms_page_code = "profiles_view";
	$script_name   = "profiles_view.php";
	$current_page  = get_custom_friendly_url("profiles_view.php");
	$auto_meta_title = PROFILES_TITLE.": ".VIEW_MSG;

	$sql = " UPDATE ".$table_prefix."cms_blocks SET php_script='block_profiles_view.php' WHERE php_script='block_profiles_details.php' ";
	$db->query($sql);

	include_once("./includes/page_layout.php");

?>