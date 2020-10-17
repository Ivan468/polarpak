<?php

	include_once("./includes/forums_functions.php");

	$default_title = "{FORUM_TITLE} &nbsp; {TOP_VIEWED_TITLE}";
	
	$user_id = get_session("session_user_id");
	$user_info = get_session("session_user_info");
	$user_type_id = get_setting_value($user_info, "user_type_id", "");			
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	$html_template = get_setting_value($block, "html_template", "block_forum_top_viewed.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("top_viewed_rows", "");
	$t->set_var("top_viewed_cols", "");

	$records_per_page = get_setting_value($vars, "forum_top_viewed_recs", 10);
		
	$forums_ids = VA_Forums::find_all_ids(
		array(
			"where"    => " fl.threads_number > 0",
			"brackets" => " (",
			"join"     => " LEFT JOIN " . $table_prefix . "forum f ON f.forum_id=fl.forum_id)",
			"order"    => " ORDER BY f.views DESC, f.thread_updated DESC "
		),
		 VIEW_TOPICS_PERM,
		 $records_per_page,
		 1
	);	
	if (!$forums_ids) return;	
	$allowed_topic_view = VA_Forums::find_all_ids(" fl.forum_id IN (" . $db->tosql($forums_ids, INTEGERS_LIST) . ") ", VIEW_TOPIC_PERM);
	
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = 1;
	
	$sql  = " SELECT forum_id, thread_id, topic, friendly_url, views ";	
	$sql .= " FROM " . $table_prefix . "forum ";
	$sql .= " WHERE forum_id IN (" . $db->tosql($forums_ids, INTEGERS_LIST) . ")";
	$sql .= " ORDER BY views DESC, thread_updated DESC ";

	$db->query($sql);
	if($db->next_record())
	{
		$top_columns = get_setting_value($vars, "forum_top_viewed_cols", 1);
		$t->set_var("top_viewed_column", (100 / $top_columns) . "%");
		$top_number = 0;
		do
		{
			$top_number++;
					
			$forum_id = $db->f("forum_id");
			$thread_id = $db->f("thread_id");
			$topic_title = get_translation($db->f("topic"));
			$friendly_url = $db->f("friendly_url");
			$total_views = $db->f("views");
			
			if ($friendly_urls && $friendly_url) {
				$t->set_var("forum_topic_url", htmlspecialchars($friendly_url . $friendly_extension));
			} else {
				$t->set_var("forum_topic_url", htmlspecialchars("forum_topic.php?thread_id=" . $thread_id));
			}
			
			if ($allowed_topic_view && in_array($forum_id, $allowed_topic_view)) {
				$t->set_var("block_topic", "");
			} else {
				$t->sparse("block_topic", false);
			}
			
		
			$t->set_var("top_position", $top_number);
			$t->set_var("thread_id", $thread_id);
			$t->set_var("topic_title", htmlspecialchars($topic_title));
			$t->set_var("total_views", $total_views);

			$t->parse("top_viewed_cols");
			if($top_number % $top_columns == 0)
			{
				$t->parse("top_viewed_rows");
				$t->set_var("top_viewed_cols", "");
			}
			
		} while ($db->next_record());              	

		if ($top_number % $top_columns != 0) {
			$t->parse("top_viewed_rows");
		}

		$block_parsed = true;
	}

?>