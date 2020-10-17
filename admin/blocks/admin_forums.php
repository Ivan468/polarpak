<?php
function admin_forums_block($block_name, $params = array()) {
	global $t, $db, $table_prefix, $db_type, $datetime_show_format, $settings;
	
	$t->set_file("block_body", "admin_block_forums.html");
	$t->set_var("admin_forum_href",                  "admin_forum.php");
	$t->set_var("admin_forum_topic_href",            "admin_forum_topic.php");
	
	$t->set_var("admin_forum_thread_href",           "admin_forum_thread.php");	
	$t->set_var("admin_forum_items_related_href",    "admin_forum_items_related.php");	
	$t->set_var("admin_forum_articles_related_href", "admin_forum_articles_related.php");	
	
	$permissions = get_permissions();
	if (!get_permissions($permissions, "forums", 0)) return;
	$products_categories = get_setting_value($permissions, "products_categories", 0);
	$product_related     = get_setting_value($permissions, "product_related", 0);
	$related_articles    = get_setting_value($permissions, "articles", 0);
	
	$forum_id    = get_param("forum_id");
	$category_id = get_param("category_id");
		
	$s           = strip_tags(rtrim(trim(get_param("s"))));
	$search      = (strlen($s)) ? true : false;
	
	if ($forum_id) {
		$sql  = " SELECT forum_name FROM " . $table_prefix . "forum_list";
		$sql .= " WHERE forum_id=" . $db->tosql($forum_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$forum_name = get_translation($db->f("forum_name"));
		} else {
			$forum_id = "";
		}
	}
	if (!$forum_id) {
		if (strlen($category_id)) {
			if ($category_id) {
				$sql  = " SELECT category_name FROM " . $table_prefix . "forum_categories";
				$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$forum_name = get_translation($db->f("category_name"));
				} else {
					$category_id = "";
				}
			}
		} else {
			$forum_name = FORUM_TITLE;
		}
	}
	$t->set_var("forum_name", $forum_name);
	
	// build sqls
	$where    = "";
	if ($s) {
		$sa = explode(" ", $s);
		for($si = 0; $si < sizeof($sa); $si++) {
			$sa[$si] = str_replace("%","\%",$sa[$si]);
			if ($where) { $where .= " AND "; }
			$where .= " (f.topic LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
			$where .= " OR f.friendly_url LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%')";
		}
	}
	
	$sql_brackets = $sql_joins = "";
	if ($forum_id) {
		if ($where) { $where .= " AND "; }
		$where .= " f.forum_id=" . $db->tosql($forum_id, INTEGER);
	} elseif ($category_id) {
		$sql_brackets = "(";
		$sql_joins    = " LEFT JOIN " . $table_prefix . "forum_list fl ON f.forum_id = fl.forum_id)";		
		if ($where) { $where .= " AND "; }
		$where .= " fl.category_id=" . $db->tosql($category_id, INTEGER);
	}
	
	
	// select count
	$sql  = " SELECT COUNT(*) FROM " . $sql_brackets . $table_prefix . "forum f " . $sql_joins;
	if ($where) {
		$sql .= " WHERE " . $where;
	}
	$total_records = get_db_value($sql);
	
	if (!$total_records) {
		if (isset($params['show_empty'])) {
			if (isset($params['show_new'])) {
				$t->parse("show_new_block");
			}
			if ($forum_id) {
				$t->set_var("message_list", NO_TOPICS_MSG);
			} elseif ($category_id) {
				$t->set_var("message_list", NO_FORUMS_SELECTED_MSG);
			} else {
				$t->set_var("message_list", NO_FORUMS_CATEGORY_MSG);
			}
			$t->parse("block_message", false);
			$t->parse("block_body", false);
			$t->parse_to("block_body", $block_name, true);
		}
		return;
	}
	$t->set_var("total_records", $total_records);

	if (strlen($s)) {
		$matching_message = FOUND_RECORDS_MSG;
		$matching_message = str_replace("{found_records}", $total_records, $matching_message);
		$matching_message = str_replace("{search_string}", htmlspecialchars($s), $matching_message);
		$t->set_var("matching_message", $matching_message);
		$t->parse("matching_block", false);
	}

	
	// display items 
	if ($product_related && $products_categories && $related_articles) {
		$t->set_var("delimiter", "| ");
	}
	
	if (isset($params['show_all'])) {
		$t->parse("show_all_block");
	} else {
		$t->set_var("show_all_block", "");
	}
	
	if (isset($params['show_new'])) {
		$t->parse("show_new_block");
	} else {
		$t->set_var("show_new_block", "");
	}
	
	if (isset($params['show_navigator']) && $total_records) {
		$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_forum.php");
		$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
		$pages_number     = 5;
		$page_number      = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);
	
		$db->RecordsPerPage = $records_per_page;
		$db->PageNumber     = $page_number;
	} else {
		$records_per_page = get_param("q") > 0 ? get_param("q") : 5;
		$db->RecordsPerPage = $records_per_page;
		$db->PageNumber     = 1;
		$t->set_var("navigator_block", "");
	}
	
	$order_by = " ORDER BY fp.priority_rank, f.thread_updated DESC ";
	if (isset($params['show_sorter'])) {
		$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_forum.php");
		$s->set_parameters(false, true, true, false);
		$s->set_sorter(NO_MSG, "sorter_id", "1", "thread_id");
		$s->set_sorter(TOPIC_NAME_COLUMN, "sorter_topic", "2", "topic");
		$s->set_sorter(OWNER_MSG, "sorter_owner", "3", "user_name");
		$s->set_sorter(TOPIC_REPLIES_COLUMN, "sorter_replies", "4", "replies");
		$s->set_sorter(UPDATED_MSG, "sorter_updated", "5", "thread_updated");		
		if ($s->order_by) {
			$order_by = $s->order_by;
		}
		$t->set_var("no_sorters_block", "");
		$t->parse("sorters_block");
	} else {
		$t->set_var("sorters_block", "");
		$t->parse("no_sorters_block");
	}
	
	$sql  = " SELECT f.thread_id, f.topic, f.user_name, f.user_email, f.replies, f.thread_updated, ";
	$sql .= " fp.html_before_title, fp.html_after_title ";
	$sql .= " FROM ("     . $sql_brackets . $table_prefix . "forum f " . $sql_joins;
	$sql .= " LEFT JOIN " . $table_prefix . "forum_priorities fp ON f.priority_id=fp.priority_id) ";
	if ($where) {
		$sql .= " WHERE " . $where;
	}
	$sql .= $order_by;
	
	$db->query($sql);
	$item_index = 1;
	$t->set_var("items_list", "");
	while ($db->next_record()) {
		$item_index++;
		$t->set_var("thread_id",    $db->f("thread_id"));		
		$title = htmlspecialchars($db->f("topic"));
		if (isset($sa) && is_array($sa)) {
			for ($si = 0; $si < sizeof($sa); $si++) {
				$regexp = "";
				for ($si = 0; $si < sizeof($sa); $si++) {
					if (strlen($regexp)) $regexp .= "|";
					$regexp .= htmlspecialchars(str_replace(
					array( "/", "|",  "$", "^", "?", ".", "{", "}", "[", "]", "(", ")", "*"),
					array("\/","\|","\\$","\^","\?","\.","\{","\}","\[","\]","\(","\)","\*"),$sa[$si]));
				}
				if (strlen($regexp)) {
					$title = preg_replace ("/(" . $regexp . ")/i", "<font color=\"blue\">\\1</font>", $title);
				}
			}
		}
		$t->set_var("title",  $title);	
		
		$html_before_title = get_translation($db->f("html_before_title"));
		$html_after_title = get_translation($db->f("html_after_title"));
		$t->set_var("html_before_title", $html_before_title);
		$t->set_var("html_after_title", $html_after_title);
		$t->set_var("user_name", htmlspecialchars($db->f("user_name")));
		$t->set_var("user_email", htmlspecialchars($db->f("user_email")));
		$t->set_var("replies", htmlspecialchars($db->f("replies")));
		$t->set_var("thread_updated", va_date($datetime_show_format, $db->f("thread_updated", DATETIME)));
				
		if ($product_related && $products_categories) {
			$t->parse("product_related_priv", false);
		} else {
			$t->set_var("product_related_priv", "");
		}				
		if ($related_articles) {
			$t->parse("related_articles_priv", false);
		} else {
			$t->set_var("related_articles_priv", "");
		}
		
		$row_style = ($item_index % 2 == 0) ? "row1" : "row2";
		$t->set_var("row_style", $row_style);
		$t->parse("items_list");
	}
	
	
	$t->parse("block_body", false);
	$t->parse_to("block_body", $block_name, true);
	
	return $total_records;
}
?>