<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_support_chats.php                                  ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once($root_folder_path."messages/".$language_code."/support_messages.php");
	include_once("./admin_common.php");

	check_admin_security("support");

	$permissions = get_permissions();
	$allow_close = get_setting_value($permissions, "support_ticket_close", 0); 
	$admin_id    = get_session("session_admin_id");

	$admin_chat_close_url = new VA_URL("admin_support_chat.php", true);
	$admin_chat_close_url->add_parameter("chat_id", DB, "chat_id");
	$admin_chat_close_url->add_parameter("operation", CONSTANT, "close");


	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_support_chats.html");

	$t->set_var("admin_support_chat_href", "admin_support_chat.php");
	$t->set_var("admin_support_chats_href", "admin_support_chats.php");
	$t->set_var("date_edit_format", join("", $date_edit_format));

	///deleting tickets
	$operation = get_param("operation");
	$items_ids = get_param("items_ids");
	if ($operation == "delete_items" && strlen($items_ids)) {
		$items_for_del = explode(",",$items_ids);
		if (isset($permissions["support_ticket_edit"]) && $permissions["support_ticket_edit"] == 1) {
			foreach($items_for_del as $item_for_del) {
	 			delete_chats($item_for_del); 
			}
		} else {
		  $t->set_var("error_delete","<font color=red>".REMOVE_TICKET_NOT_ALLOWED_MSG."<br></font>");
		}
	}


	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_support_href", "admin_support.php");
	$t->set_var("admin_support_settings_href", "admin_support_settings.php");
	$t->set_var("admin_support_chat_href", "admin_support_chat.php");        


	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_support_chats.php");
	$s->set_parameters(false, true, true, false);
	$s->set_default_sorting(1, "desc");
	$s->set_sorter(ID_MSG, "sorter_id", "1", "c.chat_id");
	$s->set_sorter(NAME_MSG, "sorter_name", "2", "c.user_name");
	$s->set_sorter(EMAIL_FIELD, "sorter_email", "3", "c.user_email");
	$s->set_sorter(CHAT_QUESTION_MSG, "sorter_question", "4", "c.user_message");
	$s->set_sorter(DATE_ADDED_MSG, "sorter_added", "5", "c.chat_added");


	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_support_chats.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$r = new VA_Record("");
	$r->add_textbox("s_id", TEXT);
	$r->add_textbox("s_ne", TEXT);
	$r->change_property("s_ne", TRIM, true);
	$r->add_textbox("s_sd", DATE, FROM_DATE_MSG);
	$r->change_property("s_sd", VALUE_MASK, $date_edit_format);
	$r->change_property("s_sd", TRIM, true);
	$r->add_textbox("s_ed", DATE, END_DATE_MSG);
	$r->change_property("s_ed", VALUE_MASK, $date_edit_format);
	$r->change_property("s_ed", TRIM, true);		
	$r->add_textbox("keyword_search", TEXT);
	$r->change_property("keyword_search", TRIM, true);
	$r->get_form_parameters();
	$r->set_form_parameters();


	$where = ""; $search = "";

	if (!$r->is_empty("s_id")) {
		if (strlen($where)) { $where .= " AND "; }
		$where .= " c.chat_id=" . $db->tosql($r->get_value("s_id"), INTEGER);
		$search .= ID_MSG . ": '<b>" . $r->get_value("s_id") . "</b>'";
	}

	if (!$r->is_empty("s_ne")) {
		if (strlen($where)) $where .= " AND ";
		$where .= " (c.user_email LIKE '%" . $db->tosql($r->get_value("s_ne"), TEXT, false) . "%'";
		$where .= " OR c.user_name LIKE '%" . $db->tosql($r->get_value("s_ne"), TEXT, false) . "%')";
		if ($search) $search .= " and ";
		$search .= BY_NAME_EMAIL_MSG . ": '<b>" . $r->get_value("s_ne") . "</b>'";
	}

	if (!$r->is_empty("s_sd")) {
		if (strlen($where)) { $where .= " AND "; }
		$where .= " c.chat_added>=" . $db->tosql($r->get_value("s_sd"), DATE);
	}

	if (!$r->is_empty("s_ed")) {
		if (strlen($where)) { $where .= " AND "; }
		$end_date = $r->get_value("s_ed");
		$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
		$where .= " c.chat_added<" . $db->tosql($day_after_end, DATE);
	}
	
	if (!$r->is_empty("keyword_search")) {
		if (strlen($where)) $where .= " AND ";
		$where .= " (";
		$where .= " c.user_message LIKE '%" . $db->tosql($r->get_value("keyword_search"), TEXT, false) . "%'";
		//$where .= " OR sm.message_text LIKE '%" . $db->tosql($r->get_value("keyword_search"), TEXT, false) . "%'";
		$where .= " )";
		if ($search) $search .= " and ";
		$search .= BY_KEYWORD_MSG . ": '<b>" . $r->get_value("keyword_search") . "</b>'";
	}
	if ($where) { $where = " WHERE " . $where; }
	
	$t->set_var("search", $search);
	if ($search) $t->parse("search_results", false);

	// set up variables for navigator
	$main_records = 0;
	$sql  = " SELECT COUNT(*)  ";
	$sql .= " FROM " . $table_prefix . "chats c ";
	$sql .= $where;
	$db->query($sql);
	if ($db->next_record()) {
		$main_records = $db->f(0);
	}
	$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
	$pages_number = 5;

	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $main_records, false);
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;

// chat statuses: 
// 1 - new 2 - chatting 3 - closed
// 4 - chat closed: no admins has joined to chat  didn'
// 5 - chat closed: admin go offline 
// 6 - chat closed: no user and admin action for a long time

/*
		if ($new_chat_status == 3) {
			$message_text = str_replace("{name}", $user_name, USER_CLOSED_CHAT_MSG);
		} else {
			$message_text = CHAT_AUTO_CLOSED_MSG;
		}*/

	$chat_statuses = array(
		"1" => WAITING_MSG,
		"2" => CHATTING_MSG,
		"3" => CLOSED_MSG,
		"4" => CLOSED_MSG,
		"5" => CLOSED_MSG,
		"6" => CLOSED_MSG,
	);
	
	// main tickets list
	$item_index = 0;
	$sql  = " SELECT * ";
	$sql .= " FROM ".$table_prefix . "chats c ";
	$sql .= $where;
	$sql .= $s->order_by;
	$db->query($sql);
	if ($db->next_record()) {				
	
		$t->parse("sorters", false);
		$t->set_var("items_number", $item_index);
		$next = false;
		do {
		 	$item_index++;
			// get db values
			$chat_id = $db->f("chat_id");
			$chat_status= $db->f("chat_status");
			$chat_added = $db->f("chat_added", DATETIME);
			$chat_added_ts = va_timestamp($chat_added);
			$user_name = $db->f("user_name");
			$user_email = $db->f("user_email");
			
	  	$t->set_var("item_index", $item_index);

			$t->set_var("chat_id", $chat_id);
			$t->set_var("chat_added", va_date($datetime_show_format, $chat_added));
	
			$t->set_var("user_message", htmlspecialchars($db->f("user_message")));

			$t->set_var("user_name", htmlspecialchars($user_name));
			$t->set_var("user_email", htmlspecialchars($user_email));

			$chat_status_desc = (isset($chat_statuses[$chat_status])) ? $chat_statuses[$chat_status] : NOT_AVAILABLE_MSG;
			$t->set_var("chat_status", htmlspecialchars($chat_status_desc));

			if ($chat_status == 1 || $chat_status == 2) {
				$t->parse("start_chat_link", false);
			} else {
				$t->set_var("start_chat_link", "");
			}

			if ($item_index % 2 == 1) { 
				$t->set_var("style","row1"); 
			} else {
				$t->set_var("style","row2");	
			}
	
			$t->parse("records", true);

		} while($db->next_record());

		// link to delete chats
		if (isset($permissions["support_ticket_edit"]) && $permissions["support_ticket_edit"] == 1) {
			$t->parse("delete_chats_link",false);
		}

		$t->set_var("items_number", $item_index);
	} else {
		$t->set_var("records", "");
		$t->set_var("navigator_block", "");
	}

	$t->pparse("main");

?>