<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  forum_rss.php                                            ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/forums_functions.php");
	include_once("./messages/".$language_code."/forum_messages.php");

	// get general settings
	$site_url = get_setting_value($settings, "site_url", "");
	$display_forums = get_setting_value($settings, "display_forums", 0);
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	$rss_date_format = "D, d M Y H:i:s O";

	if ($display_forums == 1) {
		// user need to be logged in before viewing forum
		check_user_session();
	}
	$user_id = get_session("session_user_id");		
	$user_info = get_session("session_user_info");
	$user_type_id = get_setting_value($user_info, "user_type_id", "");

	$forum_id = get_param("forum_id");
	if (!VA_Forums::check_permissions($forum_id, VIEW_FORUM_PERM)
		|| !VA_Forums::check_permissions($forum_id, VIEW_TOPICS_PERM)) {
		echo NO_RECORDS_MSG;
		exit;
	}

	// retrieve info about forum 
	$sql  = " SELECT fl.* ";
	$sql .= " FROM " . $table_prefix . "forum_list fl ";
	$sql .= " WHERE fl.forum_id=" . $db->tosql($forum_id, INTEGER);		
	$db->query($sql);
	if ($db->next_record()) {
		$category_id = $db->f("category_id");
		$rss_title = get_translation($db->f("forum_name"));
		$forum_friendly_url = $db->f("friendly_url");
		if ($friendly_urls && $forum_friendly_url) {
			$rss_link = $site_url.$forum_friendly_url . $friendly_extension;
		} else {
			$rss_link = $site_url."forum.php?forum_id=" . urlencode($forum_id);
		}

		$rss_description = get_translation($db->f("short_description"));
		if (!$rss_description) {
			$rss_description = get_translation($db->f("full_description"));
		}
		$last_post_added = $db->f("last_post_added", DATETIME);
		$rss_last_build_date = va_timestamp($last_post_added);

		$is_rss = $db->f("is_rss");
		$rss_limit = $db->f("rss_limit");
		if (!$rss_limit) { $rss_limit = 25; }
	}
	
	// initialize template class
	$t = new VA_Template($settings["templates_dir"]);
	$t->set_file("body", "forum_rss.xml");
	// parse main RSS channel data
	$t->set_var("rss_title", htmlspecialchars($rss_title, ENT_QUOTES));
	$t->set_var("rss_link", htmlspecialchars($rss_link, ENT_QUOTES));
	$t->set_var("rss_description", htmlspecialchars($rss_description, ENT_QUOTES));
	$t->set_var("rss_last_build_date", date($rss_date_format, $rss_last_build_date));

	// get forum topics 
	$topics = array();
	$sql  = " SELECT f.thread_id, f.friendly_url, f.topic, f.description, f.user_id, f.admin_id_added_by, ";
	$sql .= " f.user_name, f.views, f.replies, f.thread_updated ";
	$sql .= " FROM " . $table_prefix . "forum f ";
	$sql .= " WHERE f.forum_id=" . $db->tosql($forum_id, INTEGER);		
	$sql .= " ORDER BY f.thread_updated DESC ";
	$db->RecordsPerPage = $rss_limit;
	$db->PageNumber     = 1;
	$db->query($sql);
	while ($db->next_record()) {
		$thread_id = $db->f("thread_id");
		$topics[$thread_id] = $db->Record;
		$topics[$thread_id]["thread_updated"] = $db->f("thread_updated", DATETIME);
	}

	foreach ($topics as $thread_id => $topic_data) {
		$item_title = $topic_data["topic"];
		$t->set_var("item_title", htmlspecialchars($item_title, ENT_QUOTES));
		$t->parse("item_title_block", false);

		$friendly_url = $topic_data["friendly_url"];
		if ($friendly_urls && $friendly_url) {
			$item_link = $site_url . $friendly_url . $friendly_extension;
		} else {
			$item_link = $site_url . get_custom_friendly_url("forum_topic.php") . "?thread_id=" . $thread_id;
		}
		$t->set_var("item_link", htmlspecialchars($item_link, ENT_QUOTES));
		$t->parse("item_link_block", false);

		$item_description = $topic_data["description"];
		$t->set_var("item_description", htmlspecialchars($item_description, ENT_QUOTES));
		$t->parse("item_description_block", false);

		// author name
		$user_id = $topic_data["user_id"];
		$admin_id_added_by = $topic_data["admin_id_added_by"];
		$user_name = $topic_data["user_name"];

		$item_author = $user_name . " (" . GUEST_MSG . ")";
		if ($user_id) {
			$sql  = " SELECT login, nickname FROM " . $table_prefix . "users ";
			$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$item_author = $db->f("nickname");
				if (!strlen($item_author)) { $item_author = $db->f("login"); }
			}
		} else if ($admin_id_added_by) {
			$sql  = " SELECT admin_name, nickname FROM " . $table_prefix . "admins ";
			$sql .= " WHERE admin_id=" . $db->tosql($admin_id_added_by, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$item_author = $db->f("nickname");
				if (!strlen($item_author)) { $item_author = $db->f("admin_name"); }
			}
		}
		$t->set_var("item_author", htmlspecialchars($item_author, ENT_QUOTES));
		$t->parse("item_author_block", false);

		$thread_updated = $topic_data["thread_updated"];
		$item_date = va_timestamp($thread_updated);
		$t->set_var("item_date", date($rss_date_format, $item_date));
		$t->parse("item_date_block", false);

		$t->parse("rss_items", true);
	}

	$t->pparse("body", false);
	
?>