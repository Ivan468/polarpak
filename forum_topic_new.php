<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  forum_topic_new.php                                      ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/navigator.php");
	include_once("./includes/record.php");
	include_once("./includes/sorter.php");
	include_once("./includes/friendly_functions.php");
	include_once("./includes/icons_functions.php");
	include_once("./includes/forums_functions.php");
	include_once("./messages/" . $language_code . "/forum_messages.php");
	
	$display_forums = get_setting_value($settings, "display_forums", 0);
	if ($display_forums == 1) {
		// user need to be logged in before viewing forum 
		check_user_session();
	}
	$user_id = get_session("session_user_id");
	$user_info = get_session("session_user_info");
	$user_type_id = get_setting_value($user_info, "user_type_id", "");
	
	$cms_page_code = "forum_topic_new";
	$script_name   = "forum_topic_new.php";
	$current_page  = get_custom_friendly_url("forum_topic_new.php");
	$auto_meta_title = NEW_TOPIC_MSG;

	$currency = get_currency();
	$forum_id = get_param("forum_id");
	$search_string = get_param("search");
	
	// if there are no parameters redirect to forums list
	if (!$forum_id) {
		header("Location: " . get_custom_friendly_url("forums.php"));
		exit;
	}

	$forum_name = ""; $forum_description = ""; $forum_image = "";
	
	// retrieve info about current forum
	$sql  = " SELECT forum_id, category_id, forum_name, short_description, full_description, small_image, large_image ";
	$sql .= " FROM " . $table_prefix . "forum_list ";
	$sql .= " WHERE forum_id=" . $db->tosql($forum_id, INTEGER);
		
	$db->query($sql);
	if ($db->next_record()) {
		$forum_info = $db->Record;
		$category_id = $db->f("category_id");
		$forum_name = get_translation($db->f("forum_name"));
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
	
	if (!VA_Forums::check_permissions($forum_id, VIEW_FORUM_PERM)) {
		header ("Location: " . get_custom_friendly_url("user_login.php") . "?type_error=2");
		exit;
	}
	
	// prepare icons to replace in the text
	prepare_icons($icons, $icons_codes, $icons_tags);

	include_once("./includes/page_layout.php");
	
?>