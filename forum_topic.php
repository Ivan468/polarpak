<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  forum_topic.php                                          ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/sorter.php");
	include_once("./includes/navigator.php");
	include_once("./includes/record.php");
	include_once("./includes/icons_functions.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/forums_functions.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/forum_messages.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");

	$display_forums = get_setting_value($settings, "display_forums", 0);
	if ($display_forums == 1) {
		// user need to be logged in before viewing forum 
		check_user_session();
	}
	$user_id = get_session("session_user_id");
	$user_info = get_session("session_user_info");
	$user_type_id = get_setting_value($user_info, "user_type_id", "");
	
	$cms_page_code = "forum_topic";
	$script_name   = "forum_topic.php";
	$current_page  = get_custom_friendly_url("forum_topic.php");
	$tax_rates = get_tax_rates();
	$currency = get_currency();

	$thread_id = get_param("thread_id");

	$page_friendly_url = ""; 
	$page_friendly_params = array("thread_id");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	$forum_image = ""; $forum_description = "";
	
	// retrieve info about current forum
	// if forum is hidden, thread is hidden too
	$sql  = " SELECT fl.category_id, fl.forum_id, fl.forum_name,fl.short_description, fl.full_description, ";
	$sql .= " fl.small_image, fl.large_image, f.friendly_url ";
	$sql .= " FROM (" . $table_prefix . "forum_list fl";
	$sql .= " INNER JOIN " . $table_prefix . "forum f ON  f.forum_id=fl.forum_id)";
	$sql .= " WHERE f.thread_id=" . $db->tosql($thread_id, INTEGER);
		
	$db->query($sql);
	if ($db->next_record()) {
		$forum_info = $db->Record;
		$category_id = $db->f("category_id");
		$forum_id    = $db->f("forum_id");
		$forum_name  = get_translation($db->f("forum_name"));
		$page_friendly_url = $db->f("friendly_url");
		
		if (!VA_Forum_Categories::check_exists($category_id)) {
			header ("Location: " . get_custom_friendly_url("forums.php"));
			exit;
		}
			
		if (!VA_Forums::check_permissions($forum_id, VIEW_FORUM_PERM)) {
			header ("Location: " . get_custom_friendly_url("user_login.php") . "?type_error=2");
			exit;
		}
	} else {
		header ("Location: " . get_custom_friendly_url("forums.php"));
		exit;
	}
	
	if (!VA_Forums::check_permissions($forum_id, VIEW_TOPIC_PERM)) {
		header ("Location: " . get_custom_friendly_url("user_login.php") . "?type_error=2");
		exit;
	}

	// prepare icons to replace in the text
	prepare_icons($icons, $icons_codes, $icons_tags);

	if ($friendly_urls && $page_friendly_url) {
		$canonical_url = $page_friendly_url.$friendly_extension;
	} else {
		$canonical_url = "forum_topic.php?thread_id=".urlencode($thread_id);
	}

	include_once("./includes/page_layout.php");
	
?>