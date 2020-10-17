<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  user_profile.php                                         ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$cms_page_code = "user_profile";
	$script_name   = "user_profile.php";

	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./includes/parameters.php");
	include_once("./includes/friendly_functions.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/profile_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/navigator.php");
	include_once("./messages/" . $language_code . "/download_messages.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/forum_messages.php");

	$user_id = get_session("session_user_id");		

	if (strlen($user_id)) {
		$cms_page_code = "user_account_profile";
		$auto_meta_title = EDIT_PROFILE_MSG;
	} else {
		$cms_page_code = "user_profile";
		$auto_meta_title = PROFILE_TITLE;
	}
	$script_name  = "user_profile.php";
	$current_page = get_custom_friendly_url("user_profile.php");
	$tax_rates = get_tax_rates();

	include_once("./includes/page_layout.php");

?>