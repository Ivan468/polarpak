<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  support_chat.php                                         ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./messages/" . $language_code . "/support_messages.php");

	// if user need to be logged in 
	//check_user_session();

	// set headers for chat 
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");
	header("Content-Type: text/html; charset=" . CHARSET);

	// offline support form
	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", "");
	$secure_user_ticket = get_setting_value($settings, "secure_user_ticket", 0);
	if ($secure_user_ticket) {
		$support_url = $secure_url . "support.php";
	} else {
		$support_url = $site_url . "support.php";
	}

	$product_settings   = get_settings("user_product_" .  get_session("session_user_type_id"));
	$can_select_folder  = get_setting_value($product_settings, "can_select_folder", 0);
	$uploads_subfolder  = get_setting_value($product_settings, "uploads_subfolder", "");
	$show_preview_image = get_setting_value($settings, "show_preview_image_client", 0);

	$current_time = va_time();
	$current_ts = va_timestamp();
	$waiting_ts = $current_ts - 1200; // waiting for administrator answer for 20 minutes
	$waiting_admin_ts = $current_ts - 600; // if admin is offline for last 10 minutes in the chat
	$waiting_action_ts = $current_ts - 1200; // if there no any action for 20 minutes
	$time_format = "HH:mm";
	$author_short_length = 18;

	$ajax = get_param("ajax");
	$operation = get_param("operation");
	$chat_id = get_session("session_chat_id");

	$new_chat_status = 0; $chat_status = 0; $user_name = ""; $user_message = "";
	if ($chat_id) {
		$sql  = " SELECT * FROM " . $table_prefix . "chats ";
		$sql .= " WHERE chat_id=" . $db->tosql($chat_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			// check status
			$chat_status = $db->f("chat_status");
			// user data
			$user_name = $db->f("user_name");
			$user_message = $db->f("user_message");
			// stats data
			$chat_added = $db->f("chat_added", DATETIME);
			$user_online = $db->f("user_online", DATETIME);
			$user_last_added = $db->f("user_last_added", DATETIME);
			$admin_online = $db->f("admin_online", DATETIME);
			$admin_last_added = $db->f("admin_last_added", DATETIME);
			$chat_added_ts = va_timestamp($chat_added);
			$user_online_ts = va_timestamp($user_online);
			$user_last_ts = va_timestamp($user_last_added);
			$admin_online_ts = 0;
			if (is_array($admin_online)) {
				$admin_online_ts = va_timestamp($admin_online);	
			}
			$admin_last_ts = 0;
			if (is_array($admin_last_added)) {
				$admin_last_ts = va_timestamp($admin_last_added);
			}
			if ($chat_status == 1) {
				if ($chat_added_ts < $waiting_ts && $admin_last_ts < $waiting_ts) {
					$new_chat_status = 4; // close chat: no answer from admin 
				}
			} else if ($chat_status == 2) {
				if ($admin_online_ts < $waiting_admin_ts) {
					$new_chat_status = 5; // close chat: admin go offline 
				} else if ($user_last_ts < $waiting_action_ts && $admin_last_ts < $waiting_action_ts) {
					$new_chat_status = 6; // close chat: no user and admin action
				}
			}
		}

		if ($operation == "close") {
			$new_chat_status = 3; // user close chat
		}
	}


	// record for chat messages
	$rm = new VA_Record($table_prefix . "chats_messages");
	$rm->add_where("message_id", INTEGER);
	$rm->add_textbox("chat_id", INTEGER);
	$rm->add_textbox("admin_id", INTEGER);
	$rm->add_textbox("is_user_message", INTEGER);
	$rm->add_textbox("message_type", INTEGER);
	$rm->add_textbox("message_text", TEXT);
	$rm->add_textbox("message_added", DATETIME);

	if ($chat_id && $new_chat_status && $chat_status < 3) {

		$sql  = " UPDATE " . $table_prefix . "chats ";
		$sql .= " SET chat_status=" . $db->tosql($new_chat_status, INTEGER);;
		$sql .= " , user_online=" . $db->tosql($current_time, DATETIME);
		$sql .= " , chat_closed=" . $db->tosql($current_time, DATETIME);
		$sql .= " WHERE chat_id=" . $db->tosql($chat_id, INTEGER);
		$sql .= " AND chat_status<3 ";
		$db->query($sql);

		$chat_status = $new_chat_status;

		if ($new_chat_status == 3) {
			$message_text = str_replace("{name}", $user_name, USER_CLOSED_CHAT_MSG);
		} else {
			$message_text = CHAT_AUTO_CLOSED_MSG;
		}

		$rm->set_value("chat_id", $chat_id);
		$rm->set_value("admin_id", 0);
		$rm->set_value("is_user_message", 0);
		$rm->set_value("message_type", 3); // chat was closed
		$rm->set_value("message_text", $message_text);
		$rm->set_value("message_added", va_time());
		$rm->insert_record();
	}

	if ($operation == "close") {
		// when user close chat clear chat variable
		$chat_id = "";
		set_session("session_chat_id", "");
	}



	if ($ajax && $chat_id) {
		// update user_online field that user still online and ready to chat
		$current_time = va_time();
		$sql  = " UPDATE " . $table_prefix . "chats ";
		$sql .= " SET user_online=" . $db->tosql($current_time, DATETIME);
		if ($operation == "new_message") {
			$sql .= " , user_last_added=" . $db->tosql($current_ts, DATETIME);
		}
		$sql .= " WHERE chat_id=" . $db->tosql($chat_id, INTEGER);
		$db->query($sql);

		// check if we need to add a new message
		if ($operation == "new_message") {
			$new_message = trim(get_param("new_message"));
			if (strlen($new_message)) {
				$rm->set_value("chat_id", $chat_id);
				$rm->set_value("admin_id", 0);
				$rm->set_value("is_user_message", 1);
				$rm->set_value("message_type", 1);
				$rm->set_value("message_text", $new_message);
				$rm->set_value("message_added", va_time());
				$rm->insert_record();
			}
		}

		// ajax call for new chat events 
		$events = array();
		$last_message_id = get_param("last_message_id");

		$sql  = " SELECT cm.message_id, cm.admin_id, cm.is_user_message, ";
		$sql .= " cm.message_type, cm.message_text, cm.message_added, ";
		$sql .= " a.admin_name "; 
		$sql .= " FROM " . $table_prefix . "chats_messages cm ";
		$sql .= " LEFT JOIN " . $table_prefix . "admins a ON a.admin_id=cm.admin_id ";
		$sql .= " WHERE cm.chat_id=" . $db->tosql($chat_id, INTEGER);
		if ($last_message_id) {
			$sql .= " AND cm.message_id>" . $db->tosql($last_message_id, INTEGER);
		}
		$sql .= " ORDER BY cm.message_id ";
		$db->query($sql);
		while ($db->next_record()) {
			$message_id = $db->f("message_id");
			$admin_id = $db->f("admin_id");
			$admin_name = $db->f("admin_name");
			$is_user_message = $db->f("is_user_message");
			$message_type = $db->f("message_type");
			$message_text = $db->f("message_text");
			$message_added = $db->f("message_added", DATETIME);
			$message_time = va_date($time_format, $message_added);

			if ($message_type == 2 || $message_type == 3) {
				$author_name = CHAT_SYSTEM_MSG;
			} else if ($is_user_message) {
				$author_name = $user_name;
			} else {
				if ($admin_name) {
					$author_name = $admin_name;
				} else {
					$author_name = "#".$admin_id;
				}
			}
			$author_short = $author_name;
			if (strlen($author_short) > $author_short_length) {
				$author_short = substr($author_short, 0, $author_short_length - 3)."...";
			}
			
			
			$events[] = array(
				"event" => "new_message",
				"id" => $message_id,
				"admin_id" => $admin_id,
				"is_user_message" => $is_user_message,
				"author_name" => htmlspecialchars($author_name),
				"author_short" => htmlspecialchars($author_short),
				"message_type" => $message_type,
				"message_text" => htmlspecialchars($message_text),
				"message_added" => va_date($datetime_show_format, $message_added),
				"message_time" => htmlspecialchars($message_time),
			);
		}

    echo json_encode($events);
		return true;
	}
	
	$t = new VA_Template($settings["templates_dir"]);
	$t->set_file("main", "support_chat.html");
	$css_file = "";
	if (isset($settings["style_name"]) && $settings["style_name"]) {
		$css_file = "styles/" . $settings["style_name"];
		if (isset($settings["scheme_name"]) && $settings["scheme_name"]) {
			$css_file .= "_" . $settings["scheme_name"];
		}
		$css_file .= ".css";
	}
	$t->set_var("css_file", $css_file);
	$t->set_var("support_chat_href", "support_chat.php");
	$t->set_var("support_url", htmlspecialchars($support_url));


	if (!$chat_id) {

		// check if admins still online before show initial chat form
		$admin_online_ts = va_timestamp() - 20;
		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "admins ";
		$sql .= " WHERE support_online_date>=" . $db->tosql($admin_online_ts, DATETIME);
		$admins_online = get_db_value($sql);	
		if ($admins_online > 0) {
			// if chat wasn't started yet show chat register form
			$r = new VA_Record($table_prefix . "chats");
			$r->add_where("chat_id", INTEGER);
			$r->add_textbox("chat_status", INTEGER); // 1 - new , 2 - chatting, 3 - closed
			// general user data
			$r->add_textbox("user_id", INTEGER);
			$r->add_textbox("user_name", TEXT, NAME_MSG);
			$r->change_property("user_name", REQUIRED, true);
			$r->change_property("user_name", TRIM, true);
			$r->change_property("user_name", USE_IN_UPDATE, false);
			$r->add_textbox("user_email", TEXT, EMAIL_FIELD);
			$r->change_property("user_email", REQUIRED, true);
			$r->change_property("user_email", REGEXP_MASK, EMAIL_REGEXP);
			$r->change_property("user_email", TRIM, true);
			$r->change_property("user_email", USE_IN_UPDATE, false);
			$r->add_textbox("user_message", TEXT, CHAT_QUESTION_MSG);
			$r->change_property("user_message", REQUIRED, true);
			$r->change_property("user_message", TRIM, true);
			$r->change_property("user_message", USE_IN_UPDATE, false);
			// stat info
			$r->add_textbox("admin_id", INTEGER);
			$r->add_textbox("chat_added", DATETIME);
			$r->add_textbox("chat_started", DATETIME);
			$r->add_textbox("chat_closed", DATETIME);
			$r->add_textbox("user_online", DATETIME);
			$r->add_textbox("user_last_added", DATETIME);
			$r->add_textbox("admin_online", DATETIME);
			$r->add_textbox("admin_last_added", DATETIME);
    
			$r->get_form_values();
			// check if user registered then hide name and email fields
			$user_id = get_session("session_user_id");
			if (strlen($user_id)) {
				$user_info = get_session("session_user_info");
				$user_name = get_setting_value($user_info, "name");
				if ($user_name) {
					$r->set_value("user_name", $user_name);
					$r->change_property("user_name", SHOW, false);
				}
				$user_email = get_setting_value($user_info, "email");
				if ($user_email) {
					$r->set_value("user_email", $user_email);
					$r->change_property("user_email", SHOW, false);
				}
			}
    
			if ($operation == "new_chat") {
				$r->set_value("chat_status", 1); // 1 - new , 2 - chatting, 3 - closed
				$r->set_value("chat_added", $current_time);
				$r->set_value("user_id", get_session("session_user_id")); 
				$r->set_value("user_online", $current_time);
				$r->set_value("user_last_added", $current_time);
    
				$remote_address = get_ip();
    
				$is_valid = $r->validate();
    
				if(!$r->errors) {
      
					if($r->insert_record()) { 
						// set time when message was post
						set_session("session_message_post", va_timestamp());

						$chat_id = $db->last_insert_id();
						$r->set_value("chat_id", $chat_id);
						set_session("session_chat_id", $chat_id);
					}
				}
			}
			$r->set_parameters();
			if (!$chat_id) {
				$t->parse("chat_register_form", false);
			}
		} else {
			$t->parse("support_offline", false);
    }
	}

	if ($chat_id) {
		// set default template for messages
		$t->set_var("message_id", "template");
		$t->set_var("message_class", "templateMessage");
		$t->set_var("author_name", "{author_name}");
		$t->set_var("author_short", "{author_short}");
		$t->set_var("message_text", "{message_text}");
		$t->set_var("message_time", "{message_time}");
		$t->parse("messages", true);

		// check initial chat message and user name
		$user_name = "";
		$sql  = " SELECT * FROM " . $table_prefix . "chats ";
		$sql .= " WHERE chat_id=" . $db->tosql($chat_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$user_name = $db->f("user_name");
			$user_message = $db->f("user_message");
			$author_short = $user_name;
			if (strlen($author_short) > $author_short_length) {
				$author_short = substr($author_short, 0, $author_short_length - 3)."...";
			}
			$chat_added = $db->f("chat_added", DATETIME);
			$message_time = va_date($time_format, $chat_added);


			$t->set_var("message_id", htmlspecialchars("initial"));
			$t->set_var("message_class", "userMessage");
			$t->set_var("author_name", htmlspecialchars($user_name));
			$t->set_var("author_short", htmlspecialchars($author_short));
			$t->set_var("message_text", htmlspecialchars($user_message));
			$t->set_var("message_time", htmlspecialchars($message_time));

			$t->parse("messages", true);
		}

		// show all message	
		$last_message_id = 0;
		$sql  = " SELECT cm.message_id, cm.admin_id, cm.is_user_message, ";
		$sql .= " cm.message_type, cm.message_text, cm.message_added, ";
		$sql .= " a.admin_name "; 
		$sql .= " FROM " . $table_prefix . "chats_messages cm ";
		$sql .= " LEFT JOIN " . $table_prefix . "admins a ON a.admin_id=cm.admin_id ";
		$sql .= " WHERE cm.chat_id=" . $db->tosql($chat_id, INTEGER);
		$sql .= " ORDER BY cm.message_id ";
		$db->query($sql);
		while ($db->next_record()) {
			$message_id = $db->f("message_id");
			if ($message_id > $last_message_id)  
			{ $last_message_id = $message_id; }
			$admin_id = $db->f("admin_id");
			$admin_name = $db->f("admin_name");
			$is_user_message = $db->f("is_user_message");
			$message_type = $db->f("message_type");
			$message_text = $db->f("message_text");
			$message_added = $db->f("message_added", DATETIME);
			$message_time = va_date($time_format, $message_added);

			if ($message_type == 2 || $message_type == 3) {
				$author_name = CHAT_SYSTEM_MSG;
			} else if ($is_user_message) {
				$author_name = $user_name;
			} else {
				if ($admin_name) {
					$author_name = $admin_name;
				} else {
					$author_name = "#".$admin_id;
				}
			}
			$author_short = $author_name;
			if (strlen($author_short) > $author_short_length) {
				$author_short = substr($author_short, 0, $author_short_length - 3)."...";
			}

			$t->set_var("message_id", htmlspecialchars($message_id));
			if ($message_type == 2 || $message_type == 3) {
				$t->set_var("message_class", "systemMessage");
			} else if ($is_user_message) {
				$t->set_var("message_class", "userMessage");
			} else {
				$t->set_var("message_class", "adminMessage");
			}
			$t->set_var("author_name", htmlspecialchars($author_name));
			$t->set_var("author_short", htmlspecialchars($author_short));
			$t->set_var("message_text", htmlspecialchars($message_text));
			$t->set_var("message_time", htmlspecialchars($message_time));

			$t->parse("messages", true);
		}


		$t->set_var("last_message_id", htmlspecialchars($last_message_id));

		if ($chat_status >= 3) {
			$t->set_var("new_message_disabled", " disabled=\"disabled\" ");
			$t->set_var("send_message_disabled", " disabled=\"disabled\" ");	
		}

		$t->parse("chat_room", false);
	}

	$t->pparse("main");

?>