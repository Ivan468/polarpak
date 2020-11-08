<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_support_chat.php                                   ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path."messages/".$language_code."/support_messages.php");
	include_once("./admin_common.php");

	check_admin_security("support");

	// set headers for chat 
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");
	header("Content-Type: text/html; charset=" . CHARSET);

	
	$current_time = va_time();
	$current_ts = va_timestamp();
	$waiting_ts = $current_ts - 1200; // waiting for user answer for 20 minutes
	$waiting_user_ts = $current_ts - 600; // if user is offline for last 10 minutes in the chat
	$waiting_action_ts = $current_ts - 1200; // if there no any action for 20 minutes
	$time_format = "HH:mm";
	$author_short_length = 18;

	$chat_id = get_param("chat_id");
	$ajax = get_param("ajax");
	$operation = get_param("operation");
	$admin_id = get_session("session_admin_id");
	$admin_name = get_session("session_admin_name");

	// if this window run update admin chat online status
	$sql  = " UPDATE " . $table_prefix . "admins ";
	$sql .= " SET support_online_date=" . $db->tosql($current_time, DATETIME);
	$sql .= " WHERE admin_id=" . $db->tosql($admin_id, INTEGER);
	$db->query($sql);

	// check current chat status and other chat parameters if we probably need to close chat automatically
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
			if ($chat_status == 2) {
				if ($user_online_ts < $waiting_user_ts) {
					$new_chat_status = 5; // close chat: user go offline 
				} else if ($user_last_ts < $waiting_action_ts && $admin_last_ts < $waiting_action_ts) {
					$new_chat_status = 6; // close chat: no user and admin action
				}
			}
		}

		if ($operation == "close") {
			$new_chat_status = 3; // admin close chat
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
	// set default values
	$rm->set_value("chat_id", $chat_id);
	$rm->set_value("admin_id", $admin_id);
	$rm->set_value("is_user_message", 0);

/*
CREATE TABLE va_chats (
  chat_id INT(11) NOT NULL AUTO_INCREMENT,
  chat_status TINYINT default 1,
  user_id INT(11) default 0,
  user_name VARCHAR(128),
  user_email VARCHAR(128),
	user_message TEXT,
  admin_id INT(11) default 0,
  chat_added DATETIME,
  chat_started DATETIME,
  chat_closed DATETIME,
	user_online DATETIME,
	user_last_added DATETIME,
	admin_online DATETIME,
	admin_last_added DATETIME,
  PRIMARY KEY (chat_id)
);//*/

	if ($chat_id && $operation == "exit" && $chat_status == 2) {
		// if admin exit chat change it status back to New
		$sql  = " UPDATE " . $table_prefix . "chats ";
		$sql .= " SET chat_status=1 ";
		$sql .= " WHERE chat_id=" . $db->tosql($chat_id, INTEGER);
		$db->query($sql);
	}


	if ($chat_id && $operation == "start" && $chat_status < 3) {

		$sql  = " UPDATE " . $table_prefix . "chats ";
		$sql .= " SET chat_status=2 ";
		if ($chat_status == 1) {
			$sql .= " , chat_started=" . $db->tosql($current_time, DATETIME);
			$sql .= " , admin_online=" . $db->tosql($current_time, DATETIME);
			$sql .= " , admin_id=" . $db->tosql($admin_id, INTEGER);
		}
		$sql .= " WHERE chat_id=" . $db->tosql($chat_id, INTEGER);
		$db->query($sql);
	  
		$chat_status = 2; // chat started
	  
		// save event only if administrator wasn't joined before
		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "chats_messages ";
		$sql .= " WHERE chat_id=" . $db->tosql($chat_id, INTEGER);
		$sql .= " AND admin_id=" . $db->tosql($admin_id, INTEGER);
		$sql .= " AND message_type=2 ";
		$admin_join = get_db_value($sql);

		if (!$admin_join) {
			$message_text = str_replace("{name}", $admin_name, USER_JOINED_CHAT_MSG);
	  
			// admin has joined chat
			$rm->set_value("message_type", 2); 
			$rm->set_value("message_text", $message_text);
			$rm->set_value("message_added", va_time());
			$rm->insert_record();
		}
	}

	if ($chat_id && $new_chat_status > 2 && $chat_status < 3) {

		$sql  = " UPDATE " . $table_prefix . "chats ";
		$sql .= " SET chat_status=" . $db->tosql($new_chat_status, INTEGER);;
		$sql .= " , admin_online=" . $db->tosql($current_time, DATETIME);
		$sql .= " , chat_closed=" . $db->tosql($current_time, DATETIME);
		$sql .= " WHERE chat_id=" . $db->tosql($chat_id, INTEGER);
		$sql .= " AND chat_status<3 ";
		$db->query($sql);

		$chat_status = $new_chat_status; // chat closed

		if ($new_chat_status == 3) {
			$message_text = str_replace("{name}", $admin_name, USER_CLOSED_CHAT_MSG);
		} else {
			$message_text = CHAT_AUTO_CLOSED_MSG;
		}

		// chat has been closed
		$rm->set_value("message_type", 3); 
		$rm->set_value("message_text", $message_text);
		$rm->set_value("message_added", va_time());
		$rm->insert_record();
	}

	if ($operation == "exit" || $operation == "close") {
		// when admin close or exit chat move him to main chat waiting page
		header("Location: admin_support_chat.php");
		exit;
	}


	if ($ajax && $chat_id) {
		// update admin_online field that admin still online and ready to chat
		$current_time = va_time();
		$sql  = " UPDATE " . $table_prefix . "chats ";
		$sql .= " SET admin_online=" . $db->tosql($current_time, DATETIME);
		if ($operation == "new_message") {
			$sql .= " , chat_status=2";
			$sql .= " , admin_id=" . $db->tosql($admin_id, INTEGER);
			$sql .= " , admin_last_added=" . $db->tosql($current_ts, DATETIME);
		}
		$sql .= " WHERE chat_id=" . $db->tosql($chat_id, INTEGER);
		$db->query($sql);

		if ($operation == "new_message") {
			$new_message = trim(get_param("new_message"));
			if (strlen($new_message)) {
				$rm->set_value("chat_id", $chat_id);
				$rm->set_value("admin_id", $admin_id);
				$rm->set_value("is_user_message", 0);
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

	$chats_waiting_msg = strip_tags(va_constant("CHATS_WAITING_MSG"));
	$support_live_msg = strip_tags(va_constant("SUPPORT_LIVE_MSG"));
	
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_support_chat.html");
	$t->set_var("chats_waiting_msg", htmlspecialchars($chats_waiting_msg));
	$t->set_var("support_live_msg", htmlspecialchars($support_live_msg));

	$t->set_var("admin_support_chat_href", "admin_support_chat.php");
	$t->set_var("admin_status", "<span class=\"onlineStatus\">".ONLINE_MSG."</span>"); // use always online

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


		$t->set_var("chat_id", htmlspecialchars($chat_id));
		$t->set_var("last_message_id", htmlspecialchars($last_message_id));

		if ($chat_status >= 3) {
			$t->set_var("new_message_disabled", " disabled=\"disabled\" ");
			$t->set_var("send_message_disabled", " disabled=\"disabled\" ");	
		}

		$t->parse("chat_room", false);

	} else {
		// show list of waiting chats
		$current_ts = va_timestamp();
		$active_chat_ts = $current_ts - 120; // check only chats where users online
		$sql  = " SELECT * FROM " . $table_prefix . "chats ";
		$sql .= " WHERE chat_status=1 "; // only new chats show
		$sql .= " AND user_online >= ".$db->tosql($active_chat_ts, DATETIME);
		$db->query($sql);
		if ($db->next_record()) {
			$chat_number = 0;
			do {
				$chat_number++;
				$chat_id = $db->f("chat_id");
				$chat_added = $db->f("chat_added", DATETIME);
				$user_name = $db->f("user_name");
				$user_message = $db->f("user_message");
				$chat_added_ts = va_timestamp($chat_added);
				$waiting_ts = $current_ts - $chat_added_ts;
				if ($waiting_ts < 60) {
					$waiting_message = str_replace("{quantity}", $waiting_ts, SECONDS_QTY_MSG);
				} else {
					$waiting_message = str_replace("{quantity}", intval($waiting_ts/60), MINUTES_QTY_MSG);
				}
				if ($chat_number % 2 == 0) {
					$row_class = "row2";
				} else {
					$row_class = "row1";
				}

				$t->set_var("row_class", $row_class);
				$t->set_var("chat_id", htmlspecialchars($chat_id));
				$t->set_var("user_name", htmlspecialchars($user_name));
				$t->set_var("user_message", htmlspecialchars($user_message));
				$t->set_var("waiting", $waiting_message);

				$t->parse("chats", true);
			} while ($db->next_record());

			$t->set_var("chats_number", $chat_number);
			$t->parse("chats_table", true);
		} else {
			$t->set_var("chats_number", 0);
			$t->parse("no_chats", true);

		}
		if ($ajax) {
			$t->pparse("chats_form", true); 
			return;
		} else {
			$t->parse("chats_form", true); 
		}

		$t->parse("chats_waiting", false);

	}

	$t->pparse("main");

?>