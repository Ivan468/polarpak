<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  forum.php                                                ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/navigator.php");
	include_once("./includes/sorter.php");
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

	$cms_page_code = "forum_topics";
	$script_name   = "forum.php";
	$current_page  = get_custom_friendly_url("forum.php");
	
	$currency = get_currency();
	$category_id = get_param("category_id");
	if (strlen($category_id) && !VA_Forum_Categories::check_exists($category_id)) {
		header ("Location: " . get_custom_friendly_url("forums.php"));
		exit;
	}
	$forum_id = get_param("forum_id");
	
	$sf = get_param("sf");
	$sw = trim(get_param("sw"));
	$u = get_param("u");
	if (!$forum_id && preg_match("/^f(\d+)$/i", $sf, $match)) {
		$forum_id = $match[1];
	} elseif (!$category_id && preg_match("/^c(\d+)$/i", $sf, $match)) {
		$category_id = $match[1];
	}

	// if there are no parameters redirect to forums list
	if (!$forum_id && !$category_id && !$sf && !$sw && !$u) {
		header("Location: " . get_custom_friendly_url("forums.php"));
		exit;
	}

	$page_friendly_url = ""; 
	$page_friendly_params = array("forum_id");
	$forum_name = ""; $full_description = ""; 
	$forum_image = ""; $forum_description = "";
	$forum_rss_breadcrumb = false;
		
	// retrieve info about current category
	if (strlen($forum_id)) {

		$sql  = " SELECT fl.* ";
		$sql .= " FROM " . $table_prefix . "forum_list fl ";
		$sql .= " WHERE fl.forum_id=" . $db->tosql($forum_id, INTEGER);		
		
		$db->query($sql);
		if ($db->next_record()) {
			$forum_info = $db->Record;
			$category_id = $db->f("category_id");
			$forum_name  = get_translation($db->f("forum_name"));
			$page_friendly_url = $db->f("friendly_url");
			friendly_url_redirect($page_friendly_url, $page_friendly_params);
			$short_description = get_translation($db->f("short_description"));
			$full_description = get_translation($db->f("full_description"));
			$is_rss = $db->f("is_rss");
			$rss_on_breadcrumb = $db->f("rss_on_breadcrumb");
			if ($is_rss && $rss_on_breadcrumb){
				$forum_rss_breadcrumb = true;
			}

			$auto_meta_title = $forum_name;
			if (strlen($short_description)) {
				$auto_meta_description = $short_description;
			} elseif (strlen($full_description)) {
				$auto_meta_description = $full_description;
			}
			if (!VA_Forums::check_permissions($forum_id, VIEW_FORUM_PERM)
				|| !VA_Forums::check_permissions($forum_id, VIEW_TOPICS_PERM)) {
				header ("Location: " . get_custom_friendly_url("user_login.php") . "?type_error=2");
				exit;
			}
		} else {
			header("Location: " . get_custom_friendly_url("forums.php"));
			exit;
		}
	}

	include_once("./includes/page_layout.php");
	
?>