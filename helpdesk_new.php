<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  helpdesk_new.php                                         ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/navigator.php");
	include_once("./includes/record.php");
	include_once("./includes/items_properties.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");
	include_once("./messages/" . $language_code . "/support_messages.php");

	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", "");
	$secure_redirect = get_setting_value($settings, "secure_redirect", 0);
	$secure_user_ticket = get_setting_value($settings, "secure_user_ticket", 0);
	if ($secure_user_ticket) {
		$support_url = $secure_url . "helpdesk_new.php";
	} else {
		$support_url = $site_url . "helpdesk_new.php";
	}
	if (!$is_ssl && $secure_user_ticket && $secure_redirect && preg_match("/^https/i", $secure_url)) {
		// move to SSL if secure option enabled
		header("Location: " . $support_url);
		exit;
	}

	$cms_page_code = "ticket_new";
	$script_name   = "helpdesk_new.php";
	$current_page  = get_custom_friendly_url("helpdesk_new.php");
	$page_friendly_url = get_custom_friendly_url("helpdesk_new.php");
	$page_friendly_params = "";
	$auto_meta_title = NEW_SUPPORT_REQUEST_MSG;

	include_once("./includes/page_layout.php");

?>