<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_forum.php                                          ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once("./admin_common.php");

	include_once($root_folder_path . "messages/" . $language_code . "/forum_messages.php");

	check_admin_security("forum");
	
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_forum.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_forum_href",          "admin_forum.php");
	$t->set_var("admin_forum_thread_href",   "admin_forum_thread.php");
	$t->set_var("admin_forum_topic_href",    "admin_forum_topic.php");
	$t->set_var("admin_forum_settings_href", "admin_forum_settings.php");
	$t->set_var("admin_forum_edit_href",     "admin_forum_edit.php");
	$t->set_var("admin_forum_category_href", "admin_forum_category.php");
	
	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$forum_id    = (int) get_param("forum_id");
	$category_id = (int) get_param("category_id");
	
	if ($forum_id && !$category_id) {
		$sql = "SELECT category_id FROM " . $table_prefix . "forum_list WHERE forum_id=" . $db->tosql($forum_id, INTEGER);
		$category_id = get_db_value($sql);
	}
	$t->set_var("category_id", $category_id);
	if ($category_id) {
		$sc_options = array(array("", ""));
		$sc         = $forum_id;
		$t->set_var("sc_field_name", "forum_id");
	} else {
		$sc_options = array(array("", ""));
		$sc         = $category_id;
		$t->set_var("sc_field_name", "category_id");
	}	
	
	// Show forum categories in the left
	$sql  = " SELECT fc.category_id, fc.category_name, fl.forum_id, fl.forum_name ";
	$sql .= " FROM (" . $table_prefix . "forum_categories fc ";
	$sql .= " LEFT JOIN " . $table_prefix . "forum_list fl ON fc.category_id=fl.category_id) ";
	if ($category_id) {
		$sql .= " WHERE fc.category_id = " . $db->tosql($category_id, INTEGER);
	}
	$sql .= " ORDER BY fc.category_order, fc.category_id, fl.forum_order ";
	$db->query($sql);
	if ($db->next_record()) {
		$last_category_id = "";
		$current_category = "";
		do {
			$list_forum_id      = $db->f("forum_id");
			$list_category_id   = $db->f("category_id");
			if ($last_category_id != $list_category_id) {
				$last_category_id   = $list_category_id;
				$list_category_name = get_translation($db->f("category_name"));
				if (!$category_id) {
					$sc_options[] = array($list_category_id , $list_category_name);
				}				
				$t->set_var("list_category_id",   $list_category_id);
				$t->set_var("list_category_name", htmlspecialchars($list_category_name));
				$t->parse("list_category", false);
			} else {
				$t->set_var("list_category", "");
			}
			if ($list_forum_id) {				
				$list_forum_name = get_translation($db->f("forum_name"));				
				$t->set_var("list_forum_id",   $list_forum_id);
				$t->set_var("list_forum_name", htmlspecialchars($list_forum_name));
				$t->parse("list_forum", false);
				if ($category_id) {
					$sc_options[] = array($list_forum_id, $list_forum_name);
				}
			} else {
				$t->set_var("list_forum", "");
			}
			$t->parse("list_block", true);
		} while ($db->next_record());
		
		$t->parse("new_forum_link", false);
		$t->set_var("block_no_categories", "");
		set_options($sc_options, $sc, "sc");
		$t->parse("sc_block");
	} else {
		$t->set_var("new_forum_link", "");
		$t->set_var("block_threads", "");
		$t->parse("no_categories", false);
		$t->set_var("sc_block", "");
	}

	include_once "blocks/admin_forums.php";
	admin_forums_block('search_result', array(
		'show_sorter'    => 1, 
		'show_navigator' => 1, 
		'show_new'       => 1,
		'show_empty'     => 1
	));
	
	$t->pparse("main");

?>