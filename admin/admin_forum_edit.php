<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_forum_edit.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ($root_folder_path . "includes/record.php");
	include_once ($root_folder_path . "includes/access_table.php");	
	include_once ($root_folder_path . "includes/friendly_functions.php");
	include_once("./admin_common.php");
	include_once($root_folder_path."messages/".$language_code."/forum_messages.php");

	check_admin_security("forum");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_forum_edit.html");

	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");
	$t->set_var("admin_forum_href", "admin_forum.php");
	$t->set_var("admin_forum_edit_href", "admin_forum_edit.php");
	$full_image_url = get_setting_value($settings, "full_image_url", 0);
	$site_url_path = get_setting_value($settings, "site_url", "");
	if ($full_image_url){
		$t->set_var("site_url", $site_url_path);					
	} else {
		$t->set_var("site_url", "");					
	}

	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", TYPE_MSG, CONFIRM_DELETE_MSG));

	$forum_id = get_param("forum_id");
	$tab = get_param("current_tab");
	if (!$tab) { $tab = "general"; }
	
	$r = new VA_Record($table_prefix . "forum_list");
	$r->return_page = "admin_forum.php";
	if (get_param("apply")) {
		$r->redirect = false;
	}

	$r->add_where("forum_id", INTEGER);
	$r->add_textbox("forum_order", TEXT, FORUM_ORDER_MSG);
	$r->change_property("forum_order", REQUIRED, true);
	$r->add_textbox("forum_name", TEXT, FORUM_NAME_MSG);
	$r->change_property("forum_name", REQUIRED, true);
	$r->change_property("forum_name", MAX_LENGTH, 255);
	$r->add_textbox("friendly_url", TEXT, FRIENDLY_URL_MSG);
	$r->change_property("friendly_url", USE_SQL_NULL, false);
	$r->change_property("friendly_url", BEFORE_VALIDATE, "validate_friendly_url");
	$r->change_property("friendly_url", REGEXP_MASK, FRIENDLY_URL_REGEXP);
	$r->change_property("friendly_url", REGEXP_ERROR, ALPHANUMERIC_ALLOWED_ERROR);
	$r->add_textbox("short_description", TEXT);
	$r->add_textbox("full_description", TEXT);
	$r->add_textbox("small_image", TEXT);
	$r->add_textbox("large_image", TEXT);

	$sql = "SELECT category_id, category_name FROM " . $table_prefix . "forum_categories";
	$categories = get_db_values($sql, array(array("", SELECT_CATEGORY_MSG)));
	if (sizeof($categories) == 1) {
		$categories = array(array("", FORUM_CATEGORIES_ERROR_MSG));
	}
	$r->add_select("category_id", TEXT, $categories, CATEGORY_MSG);
	$r->change_property("category_id", REQUIRED, true);	

	// RSS settings
	$r->add_checkbox("is_rss", INTEGER);
	$r->add_checkbox("rss_on_breadcrumb", INTEGER);
	$r->add_checkbox("rss_on_list", INTEGER);
	$r->add_textbox("rss_limit", INTEGER);

	$r->add_textbox("date_added", DATETIME, DATE_ADDED_MSG);
	$r->change_property("date_added", USE_IN_UPDATE, false);
	$r->add_textbox("threads_number", TEXT);
	$r->change_property("threads_number", USE_IN_UPDATE, false);
	$r->add_textbox("messages_number", TEXT);
	$r->change_property("messages_number", USE_IN_UPDATE, false);
	$r->add_textbox("last_post_added", DATETIME, LAST_POST_ADDED_MSG);
	$r->change_property("last_post_added", USE_IN_UPDATE, false);
	$r->add_textbox("last_post_user_id", INTEGER);
	$r->change_property("last_post_user_id", USE_IN_UPDATE, false);
	$r->add_textbox("last_post_thread_id", INTEGER);
	$r->change_property("last_post_thread_id", USE_IN_UPDATE, false);
	
	$r->add_textbox("access_level", INTEGER);
	$r->add_textbox("guest_access_level", INTEGER);
	$r->add_textbox("admin_access_level", INTEGER);
	
	$access_table = new VA_Access_Table($settings["admin_templates_dir"], "access_table.html");
	$access_table->set_access_levels(
		array(
			1 => array(FORUM_VIEW_MSG, ALLOW_VIEW_FORUM_MSG),
			2 => array(VIEW_TOPICS_MSG, ALLOW_VIEW_FORUM_TOPICS_LIST_MSG),
			4 => array(VIEW_TOPIC_MSG, ALLOW_VIEW_FORUM_TOPICS_DETAILS_MSG),
			8 => array(POST_TOPICS_MSG, ALLOW_POST_NEW_TOPICS_MSG),
			16 => array(FORUM_REPLIES_COLUMN, ALLOW_POST_REPLIES_MSG),
			32 => array(ATTACHMENTS_MSG, ALLOW_POST_ATTACHMENTS_MSG)
		)
	);
	$access_table->set_tables("forum", "forum_user_types",  "forum_subscriptions", "forum_id", false, $forum_id);
	

	// check all available administrators	
	$admins = array();
	$sql = " SELECT admin_id, admin_name FROM " . $table_prefix . "admins ORDER BY admin_id ASC";
	$db->query($sql);
	if ($db->next_record()) {
		do {
			$moderator_id = $db->f("admin_id");
			$moderator_name = $db->f("admin_name");
			$admins[$moderator_id] = array(
				"name" => $moderator_name,
				"checked" => false,
			);
		}
		while($db->next_record());
	}

	$r->set_event(BEFORE_INSERT,  "before_insert_forum");
	$r->set_event(AFTER_VALIDATE, "after_validate_forum");
	$r->set_event(AFTER_SELECT,   "check_assigned_moderators");
	$r->set_event(AFTER_REQUEST,  "check_request_moderators");
	$r->set_event(AFTER_INSERT,   "after_update_forum");
	$r->set_event(AFTER_UPDATE,   "after_update_forum");
	$r->set_event(AFTER_DELETE,   "delete_forum");
	$r->set_event(AFTER_DEFAULT,  "default_forum");
	$r->process();
	
	$has_any_subscriptions = $access_table->parse("subscriptions_table", $r->get_value("access_level"), $r->get_value("guest_access_level"), $r->get_value("admin_access_level"));
	
	include_once("./admin_header.php");
	include_once("./admin_footer.php");
	
	foreach($admins as $admin_id => $admin_info) {
		$param_name = "admin_" . $admin_id;
		$admin_checked = $admin_info["checked"] ? "checked" : "";
		$admin_checkbox = "<input type=\"checkbox\" name=\"".$param_name."\" ".$admin_checked." value=\"".$admin_id."\">";
		$t->set_var("admin_name", $admin_info["name"]);
		$t->set_var("admin_checkbox", $admin_checkbox);
		$t->parse("admin_rows", true);
	}
	
	if($r->get_value("forum_id")) {
		$t->set_var("save_button", UPDATE_BUTTON);
		$t->parse("delete", false);	
	}
	else {
		$t->set_var("save_button", ADD_NEW_MSG);
		$t->set_var("delete", "");	
	}
	
	$tabs = array(
		"general"     => array( "title" => ADMIN_GENERAL_MSG),
		"admin"			  => array( "title" => ASSIGN_MODERATORS_MSG),
		"rss"			    => array( "title" => RSS_SETTINGS_MSG),
		"subscriptions" => array( "title" => ACCESS_LEVELS_MSG, "show" => $has_any_subscriptions)
	);
	parse_admin_tabs($tabs, $tab);
	
	
	$t->set_var("admin_href", "admin.php");
	$t->pparse("main");
	
	
	function before_insert_forum() {
		global $r, $table_prefix;
		$forum_id = get_db_value("SELECT MAX(forum_id) FROM " . $table_prefix . "forum_list") + 1;
		$r->set_value("forum_id", $forum_id);
		$r->set_value("date_added", va_time());
		$r->set_value("last_post_user_id", 0);
		$r->set_value("last_post_thread_id", 0);
		$r->set_value("last_post_added", va_time());
		return true;
	}
	
	function after_validate_forum() {
		global $r, $access_table;
		set_friendly_url();
		$r->set_value("access_level", $access_table->all_selected_access_level);
		$r->set_value("guest_access_level", $access_table->guest_selected_access_level);
		$r->set_value("admin_access_level", $access_table->admin_selected_access_level);
	}
	
	function after_update_forum() {
		global $r, $access_table, $admins, $db, $table_prefix;
		$forum_id = $r->get_value("forum_id"); 
		$access_table->save_values($forum_id, get_param("save_nested_subscriptions"));
		
    // delete all available moderators before assigning new
		$sql  = " DELETE FROM " . $table_prefix . "forum_moderators ";
		$sql .= " WHERE forum_id = " . $db->tosql($forum_id, INTEGER);
		$db->query($sql);
		// assign moderators
		foreach($admins as $moderator_id => $admin_info) {
			if ($admin_info["checked"]) {
				$sql  = " INSERT INTO " . $table_prefix . "forum_moderators (admin_id, forum_id) VALUES (";
				$sql .= $db->tosql($moderator_id, INTEGER) . ", ";
				$sql .= $db->tosql($forum_id, INTEGER) . ")";
				$db->query($sql);
			}
		}
	}
	
	function delete_forum() {
		global $db, $table_prefix, $r;
		
		$forum_id = $r->get_value("forum_id");
		// delete topics' messages
		$sql = "SELECT thread_id FROM " . $table_prefix . "forum WHERE forum_id = " . $db->tosql($forum_id, INTEGER);
		$db->query($sql);
		$thread_ids = array();
		while ($db->next_record()) {
			$thread_ids[] = $db->f("thread_id");
		}
		if (sizeof($thread_ids)) {
			$db->query("DELETE FROM " . $table_prefix . "forum_messages WHERE thread_id IN (" . $db->tosql($thread_ids, INTEGERS_LIST) . ")");
		}
		
		// delete attachments
		$sql = "SELECT file_path FROM " . $table_prefix . "forum_attachments WHERE forum_id=" . $db->tosql($r->get_value("forum_id"), INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$file_path = $db->f("file_path");
			$is_file_exists = file_exists($file_path);
			if (!$is_file_exists && file_exists("../" . $file_path)) {
				$file_path = "../" . $file_path;
			}
			@unlink($file_path);
		}	  
		$sql = "DELETE FROM " . $table_prefix . "forum_attachments WHERE forum_id=" . $db->tosql($forum_id, INTEGER);
		$db->query($sql);
		
		$db->query("DELETE FROM " . $table_prefix . "forum WHERE forum_id = " . $db->tosql($forum_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "forum_moderators WHERE forum_id = " . $db->tosql($forum_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "forum_user_types WHERE forum_id = " . $db->tosql($forum_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "forum_subscriptions WHERE forum_id = " . $db->tosql($forum_id, INTEGER));
	}
	
	function show_forum() {
		global $r, $table_prefix, $db, $usr, $admins, $datetime_show_format;
		
		$r->set_value("date_added", va_date($datetime_show_format, $r->get_value("date_added")));
		$r->set_value("last_post_added", va_date($datetime_show_format, $r->get_value("last_post_added")));
	}
	
	function default_forum() {
		global $r, $table_prefix;
		$forum_order = get_db_value("SELECT MAX(forum_order) FROM " . $table_prefix . "forum_list");
		$forum_order++;
		$r->set_value("forum_order", $forum_order);
		
		$r->set_value("threads_number", 0);
		$r->set_value("messages_number", 0);
		$r->set_value("date_added", va_time());
		$r->set_value("last_post_user_id", 0);
		$r->set_value("last_post_thread_id", 0);
		$r->set_value("last_post_added", va_time());
		
		$r->set_value("access_level", 255);
		$r->set_value("guest_access_level", 255);		
		$r->set_value("admin_access_level", 255);		
	}

	function check_assigned_moderators()
	{
		global $r, $db, $table_prefix, $admins;
		$forum_id = $r->get_value("forum_id");

    $sql  = " SELECT admin_id FROM " . $table_prefix . "forum_moderators ";
		$sql .= " WHERE forum_id = " . $db->tosql($forum_id, INTEGER);
		$db->query($sql);
		while($db->next_record()) {
			$admin_id = $db->f("admin_id");
			if (isset($admins[$admin_id])) {
				$admins[$admin_id]["checked"] = true;
			}
		}
	}

	function check_request_moderators()
	{
		global $admins;
		foreach ($admins as $admin_id => $admin_info) {
			$param_name = "admin_" . $admin_id;
			$moderator_checked = get_param($param_name);
			if (strlen($moderator_checked)) {
				$admins[$admin_id]["checked"] = true;
			}
		}
	}

?>