<?php

	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/profiles_messages.php");

	$default_title = "{MY_MESSAGES_MSG}";

	check_user_security("user_messages");

	// assign default title
	$content_title = MY_MESSAGES_MSG;
	// get main parameters
	$user_id = get_session("session_user_id");
	$user_info = get_session("session_user_info");
	$user_name = get_setting_value($user_info, "name", "");
	$user_email = get_setting_value($user_info, "email", "");
	$user_nickname = get_setting_value($user_info, "nickname", "");
	$messages_check = intval(get_setting_value($user_info, "messages_check", ""));

	$operation = get_param("operation");
	$mid = get_param("mid"); // in reply to message id
	$rmid = get_param("rmid"); // in reply to message id
	$fmid = get_param("fmid"); // forward for message id
	$mfid = get_param("mfid"); // system folder id
	$mtid = get_param("mtid"); // message type id
	$mkid = get_param("mkid"); // message key id
	$time_interval = 30; // use 30 second message interval
	// some random and time parameters
	$rnd = get_param("rnd");
	$session_rnd = get_session("session_rnd");
	$current_date = va_time();
	$current_time = va_timestamp();
	$ip = get_ip();
	$eol = get_eol();

	// date filters
	$current_date = va_time();
	$cyear = $current_date[YEAR]; 
	$cmonth = $current_date[MONTH]; 
	$cday = $current_date[DAY];
	$today_start = mktime (0, 0, 0, $cmonth, $cday, $cyear);
	$today_end = mktime (23, 59, 59, $cmonth, $cday, $cyear);
	$trash_date = mktime (0, 0, 0, $cmonth, $cday - 30, $cyear); // when messages will be permanently deleted from trash folder
	$delete_date = mktime (0, 0, 0, $cmonth, $cday - 7, $cyear); // when messages will be permanently deleted from database 


	// delete old messages from trash or database once per login or per day
	if ($current_time > $messages_check + (60*60*24)) {
		// delete from trash
		$sql  = " UPDATE " . $table_prefix . "messages ";
		$sql .= " SET date_deleted=". $db->tosql(va_time(), DATETIME);
		$sql .= " WHERE system_folder_id=4 ";
		$sql .= " AND date_trashed>=". $db->tosql($trash_date, DATETIME);
		$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
		$db->query($sql);

		// delete from DB without possibility to restore data
		$sql  = " DELETE FROM " . $table_prefix . "messages ";
		$sql .= " WHERE system_folder_id=4 ";
		$sql .= " AND date_deleted>=". $db->tosql($delete_date, DATETIME);
		$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
		$db->query($sql);

		$user_info["messages_check"] = $current_time;
		set_session("session_user_info", $user_info);
	}

	// get messages settings
	$messages_settings = get_settings("messages");
	$box_limit = get_setting_value($messages_settings, "box_limit", "");
	$day_limit = get_setting_value($messages_settings, "day_limit", "");

	// by default if no other operation are not selected use Inbox folder
	if (!$mfid && !$operation) {
		$mfid = 1;
	}

	$system_folders = array(
		"1" => array("unread" => 0, "name" => FOLDER_INBOX_MSG),
		"2" => array("unread" => 0, "name" => FOLDER_SENT_MSG),
		"3" => array("unread" => 0, "name" => FOLDER_DRAFT_MSG),
		"4" => array("unread" => 0, "name" => FOLDER_TRASH_MSG),
		//"5" => array("unread" => 0, "title" => FOLDER_SPAM_MSG), // 
	);

	if ($operation == "edit") {
		$content_title = EDIT_MSG;
	} else if ($operation == "new") {
		$content_title = NEW_MSG;
	} else if ($operation == "read") {
		$content_title = READ_MSG;
		if ($mid) {
			$sql  = " UPDATE " . $table_prefix . "messages ";
			$sql .= " SET date_read=" . $db->tosql(va_time(), DATETIME);
			$sql .= " WHERE message_id=" . $db->tosql($mid, INTEGER);
			$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
			$db->query($sql);
		}
	} else if ($mfid && isset($system_folders[$mfid])) {
		$content_title = $system_folders[$mfid]["name"];
	}

	// check some trash and undel operations
	if ($operation == "trash") {
		// move message to trash folder before delete
		if ($mid) {
			$sql  = " UPDATE " . $table_prefix . "messages SET system_folder_id=4, ";
			$sql .= " date_trashed=". $db->tosql(va_time(), DATETIME);
			$sql .= " WHERE message_id=" . $db->tosql($mid, INTEGER);
			$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
			$db->query($sql);
		}
		header("Location: user_messages.php?mfid=".urlencode($mfid));
		exit;
	} else if ($operation == "remove" || $operation == "delete") {
		// finally delete message
		if ($mid) {
			$sql  = " UPDATE " . $table_prefix . "messages SET system_folder_id=4, ";
			$sql .= " date_deleted=". $db->tosql(va_time(), DATETIME);
			$sql .= " WHERE message_id=" . $db->tosql($mid, INTEGER);
			$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
			$db->query($sql);
		}
		header("Location: user_messages.php?mfid=".urlencode($mfid));
		exit;
	} else if ($operation == "undel") {
		if ($mid) {
			// check from_user_id and date_sent fields to know where to move message
			$from_user_id = ""; $date_sent = "";
			$sql  = " SELECT from_user_id,date_sent FROM " . $table_prefix . "messages ";
			$sql .= " WHERE message_id=" . $db->tosql($mid, INTEGER);
			$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$from_user_id = $db->f("from_user_id");
				$date_sent = $db->f("date_sent", DATETIME);
			}
			if ($from_user_id == $user_id) {
				if (is_array($date_sent) && isset($date_sent[YEAR])) {
					$system_folder_id = 2; // move to sent folder
				} else {
					$system_folder_id = 3; // move to draft folder
				}
			} else {
				$system_folder_id = 1; // move to Inbox
			}

			$sql  = " UPDATE " . $table_prefix . "messages ";
			$sql .= " SET system_folder_id=" . $db->tosql($system_folder_id, INTEGER);
			$sql .= " , date_trashed=NULL ";
			$sql .= " , date_deleted=NULL ";
			$sql .= " WHERE message_id=" . $db->tosql($mid, INTEGER);
			$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
			$db->query($sql);
		}
		header("Location: user_messages.php?mfid=".urlencode($mfid));
		exit;
	}

	$html_template = get_setting_value($block, "html_template", "block_user_messages.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("user_messages_href",  "user_messages.php");
	$t->set_var("user_home_href", "user_home.php");
	$t->set_var("rnd", va_timestamp());
	$t->set_var("content_title", htmlspecialchars($content_title));

	// count unread message for every folder
	$sql  = " SELECT m.system_folder_id, COUNT(*) AS unread_messages FROM " . $table_prefix . "messages m ";
	$sql .= " WHERE m.user_id=" . $db->tosql($user_id, INTEGER);
	$sql .= " AND m.date_read IS NULL ";
	$sql .= " AND m.date_deleted IS NULL ";
	$sql .= " GROUP BY m.system_folder_id ";
	$db->query($sql);
	while ($db->next_record()) {
		$system_folder_id = $db->f("system_folder_id");
		$unread_messages = $db->f("unread_messages");
		$system_folders[$system_folder_id]["unread"] = $unread_messages;
	}

	// parse folders
	foreach ($system_folders as $id => $folder_info) {
		$unread_messages = $folder_info["unread"];
		$folder_name = $folder_info["name"];
		$folder_url = "user_messages.php?mfid=".urlencode($id);

		if ($unread_messages) {
			$folder_class = "unreadFolder";
			$folder_name = $folder_name." (".$unread_messages.")";
		} else {
			$folder_class = "folder";
		}

		$t->set_var("folder_class", $folder_class);
		$t->set_var("folder_name", $folder_name);
		$t->set_var("folder_url", $folder_url);

		$t->parse("folders", true);
	}

	// initialize record for some operations
	if ($operation == "new" || $operation == "send" || $operation == "save" || $operation == "edit"
		|| $operation == "reply" || $operation == "forward")  {
		$r = new VA_Record($table_prefix."messages");
		$r->add_where("mid", INTEGER);
		$r->change_property("mid", COLUMN_NAME, "message_id");
		$r->add_textbox("rmid", INTEGER);
		$r->change_property("rmid", COLUMN_NAME, "reply_message_id");
		$r->change_property("rmid", USE_IN_UPDATE, false);
		$r->add_textbox("fmid", INTEGER);
		$r->change_property("fmid", COLUMN_NAME, "forward_message_id");
		$r->change_property("fmid", USE_IN_UPDATE, false);

		$r->add_textbox("parent_message_id", INTEGER);
		$r->add_textbox("user_id", INTEGER);
		$r->add_textbox("from_user_id", INTEGER);
		$r->add_textbox("system_folder_id", INTEGER);
		$r->add_textbox("mtid", INTEGER);
		$r->change_property("mtid", COLUMN_NAME, "message_type");
		$r->change_property("mtid", USE_IN_UPDATE, false);
		$r->add_textbox("mkid", INTEGER);
		$r->change_property("mkid", COLUMN_NAME, "message_key_id");
		$r->change_property("mkid", USE_IN_UPDATE, false);
		$r->add_textbox("message_from", TEXTBOX);
		// message field
		$r->add_textbox("message_to", TEXT, EMAIL_TO_MSG);
		$r->change_property("message_to", REQUIRED, true);
		$r->set_control_event("message_to", AFTER_VALIDATE, "check_receiver");
		$r->add_textbox("message_subject", TEXT, EMAIL_SUBJECT_MSG);
		$r->change_property("message_subject", REQUIRED, true);
		$r->add_textbox("message_text", TEXT, EMAIL_MESSAGE_MSG);
		$r->change_property("message_text", REQUIRED, true);
		// date field when message was add
		$r->add_textbox("date_added", DATETIME);
		$r->change_property("date_added", USE_IN_UPDATE, false);
		$r->add_textbox("date_sent", DATETIME);
		$r->change_property("date_sent", USE_IN_INSERT, false);
		$r->change_property("date_sent", USE_IN_UPDATE, false);
		$r->add_textbox("date_modified", DATETIME);
		$r->change_property("date_modified", USE_IN_INSERT, false);
		$r->add_textbox("date_read", DATETIME);
		$r->change_property("date_read", USE_IN_UPDATE, false);
	}

	
	// check operations
	if ($operation == "new" || $operation == "send" || $operation == "save") {

		// get data from request  
		$r->get_form_values();

		if(($operation == "send" || $operation == "save")) {
			// check if it was double click to redirect customer to success page
			if ($rnd == $session_rnd) {
				if ($operation == "save") {
					header("Location: user_messages.php?mfid=3&operation=saved");	
					exit;
				} else if ($operation == "send") {
					header("Location: user_messages.php?mfid=2&operation=sent");	
					exit;
				}
			}

			$receivers = array(); // save here all receivers 
			$remote_address = get_ip();
  
			// set some fields
			$r->set_value("user_id", $user_id);
			$r->set_value("from_user_id", $user_id);
			$r->set_value("message_from", $user_name);
			$r->set_value("date_added", va_time());
			$r->set_value("date_sent", va_time()); // save only if we sent this message 
			$r->set_value("date_read", va_time()); // for messages user created they automatically marked as read
			$r->set_value("date_modified", va_time());
			// validate record
			$r->validate();

			// check if user hasn't reach box limit
			if (strlen($box_limit) && !$r->get_value("mid")) {
				$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "messages ";
				$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
				$sql .= " AND date_deleted IS NULL ";
				$box_messages = get_db_value($sql);
				if ($box_messages >= $box_limit) {
					$r->data_valid = false;
					$r->errors .= str_replace("{box_limit}", $box_limit, REACH_BOX_LIMIT_MSG)."<br/>";
				}
			}

			if (strlen($day_limit) && $operation == "send") {
				$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "messages ";
				$sql .= " WHERE from_user_id=" . $db->tosql($user_id, INTEGER);
				$sql .= " AND user_id<>" . $db->tosql($user_id, INTEGER);
				$sql .= " AND date_added>=".$db->tosql($today_start, DATETIME);
				$sql .= " AND date_added<=".$db->tosql($today_end, DATETIME);
				$messages_sent = get_db_value($sql);
				if ($messages_sent >= $day_limit) {
					$r->data_valid = false;
					$r->errors .= str_replace("{day_limit}", $day_limit, MESSAGES_DAY_LIMIT_ERROR)."<br/>";
				}
			}

			// additional check if user can sent next message
			$message_sent = get_session("session_message_sent");
			if (!$message_sent) {
				$message_sent = get_session("session_start_ts");
			}
			$time_left = $message_sent + $time_interval - $current_time;

			if ($time_left > 0) {
				$time_left = str_replace("{quantity}", $time_left, SECONDS_QTY_MSG);
				$interval_error = str_replace("{interval_time}", $time_left, MESSAGE_INTERVAL_ERROR);
				$r->errors = $interval_error."<br/>";
			}
  
			if(!$r->errors) {

				$record_saved = false;

				if ($operation == "send") {
					// if we send message save date when it was sent
					$r->change_property("date_sent", USE_IN_INSERT, true);
					$r->change_property("date_sent", USE_IN_UPDATE, true);
				}

				if ($r->get_value("mid")) {
					// update current message
					$r->change_property("user_id", USE_IN_WHERE, true); // user can edit message assigned to him
					$r->change_property("from_user_id", USE_IN_WHERE, true); // user can edit only messages he has created

					if ($operation == "save") {
						$r->change_property("system_folder_id", USE_IN_UPDATE, false);
					} else if ($operation == "send") {
						// move message to sent folder
						$r->set_value("system_folder_id", 2);
					}

					if($r->update_record()) {
						$record_saved = true;
					}
				} else {
					// add new message
					if ($operation == "save") {
						// save new message in draft folder
						$r->set_value("system_folder_id", 3);
					} else if ($operation == "send") {
						// save new message in sent folder
						$r->set_value("system_folder_id", 2);
					}

					if($r->insert_record()) {
						$mid = $db->last_insert_id();
						$r->set_value("mid", $mid);
						$record_saved = true;
					}
				}
				// after saving message in sent folder save message for all receivers
				if ($operation == "send" && $record_saved) {
					// check if email notification activated 
					$admin_notification = get_setting_value($messages_settings, "admin_notification", 0);
					$user_notification = get_setting_value($messages_settings, "user_notification", 0);

					// set general tags
					$date_added_formatted = va_date($datetime_show_format, va_time());
					$t->set_var("date_added", $date_added_formatted);        
					$t->set_var("ip", $ip);
					$t->set_var("remote_address", $ip);

					// send email notification to admin
					if ($admin_notification) {
						// check full receivers data
						$to_email = ""; $to_name = "";
						foreach ($receivers as $id => $receiver) {
							if ($to_email) { $to_email .= ", "; }
							if ($to_name) { $to_name .= ", "; }
							$to_email .= $receiver["email"];
							$to_name .= $receiver["name"];
						}

						$admin_subject = get_setting_value($messages_settings, "admin_subject");
						$admin_message = get_setting_value($messages_settings, "admin_message");
						$t->set_block("admin_subject", $admin_subject);
						$t->set_block("admin_message", $admin_message);
        
						$mail_to = get_setting_value($messages_settings, "admin_email", $settings["admin_email"]);
						$mail_to = str_replace(";", ",", $mail_to);
						$mail_from = get_setting_value($messages_settings, "admin_mail_from", $settings["admin_email"]);
						$email_headers = array();
						$email_headers["from"] = parse_value($mail_from);
						$email_headers["cc"] = get_setting_value($messages_settings, "admin_mail_cc");
						$email_headers["bcc"] = get_setting_value($messages_settings, "admin_mail_bcc");
						$email_headers["reply_to"] = get_setting_value($messages_settings, "admin_mail_reply_to");
						$email_headers["return_path"] = get_setting_value($messages_settings, "admin_mail_return_path");
						$email_headers["mail_type"] = get_setting_value($messages_settings, "admin_message_type");

						if ($email_headers["mail_type"]) {
							$t->set_var("message_subject", htmlspecialchars($r->get_value("message_subject")));
							$t->set_var("message_text", nl2br(htmlspecialchars($r->get_value("message_text"))));
							$t->set_var("from_name", htmlspecialchars($user_name));
							$t->set_var("from_email", htmlspecialchars($user_email));
							$t->set_var("to_name", htmlspecialchars($to_name));
							$t->set_var("to_email", htmlspecialchars($to_email));
						} else {
							$t->set_var("from_name", $user_name);
							$t->set_var("from_email", $user_email);
							$t->set_var("to_name", $to_name);
							$t->set_var("to_email", $to_email);
							$t->set_var("message_subject", $r->get_value("message_subject"));
							$t->set_var("message_text", $r->get_value("message_text"));
						}

        
						$t->parse("admin_subject", false);
						$t->parse("admin_message", false);
						$admin_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("admin_message"));
						va_mail($mail_to, $t->get_var("admin_subject"), $admin_message, $email_headers);
					}

					foreach ($receivers as $id => $receiver) {
						$receiver_nickname = $receiver["nickname"];
						$receiver_name = $receiver["name"];
						$receiver_email = $receiver["email"];

						$parent_message_id = $r->get_value("mid");
						$r->change_property("mid", USE_IN_INSERT, false);
						$r->change_property("date_added", USE_IN_INSERT, true);
						$r->change_property("date_read", USE_IN_INSERT, false);
						$r->change_property("date_sent", USE_IN_INSERT, false);

						$r->set_value("date_added", va_time());
						$r->set_value("system_folder_id", 1); // all message should go to inbox folder
						$r->set_value("parent_message_id", $parent_message_id);
						$r->set_value("message_from", $user_name);
						$r->set_value("user_id", $id);
						if ($r->insert_record()) {
							// send notification to user if it's activated
							if ($user_notification && $receiver_email)
							{
								$user_subject = get_setting_value($messages_settings, "user_subject");
								$user_message = get_setting_value($messages_settings, "user_message");
								$t->set_block("user_subject", $user_subject);
								$t->set_block("user_message", $user_message);
              
								$mail_from = get_setting_value($messages_settings, "user_mail_from", $settings["admin_email"]); 
								$email_headers = array();
								$email_headers["from"] = parse_value($mail_from);
								$email_headers["cc"] = get_setting_value($messages_settings, "user_mail_cc");
								$email_headers["bcc"] = get_setting_value($messages_settings, "user_mail_bcc");
								$email_headers["reply_to"] = get_setting_value($messages_settings, "user_mail_reply_to");
								$email_headers["return_path"] = get_setting_value($messages_settings, "user_mail_return_path");
								$email_headers["mail_type"] = get_setting_value($messages_settings, "user_message_type");
				    
								if ($email_headers["mail_type"]) {
									$t->set_var("message_subject", htmlspecialchars($r->get_value("message_subject")));
									$t->set_var("message_text", nl2br(htmlspecialchars($r->get_value("message_text"))));
									$t->set_var("from_name", htmlspecialchars($user_name));
									$t->set_var("from_email", htmlspecialchars($user_email));
									$t->set_var("to_name", htmlspecialchars($receiver_name));
									$t->set_var("to_email", htmlspecialchars($receiver_email));
								} else {
									$t->set_var("from_name", $user_name);
									$t->set_var("from_email", $user_email);
									$t->set_var("to_name", $receiver_name);
									$t->set_var("to_email", $receiver_email);
									$t->set_var("message_subject", $r->get_value("message_subject"));
									$t->set_var("message_text", $r->get_value("message_text"));
								}
				    
								$t->parse("user_subject", false);
								$t->parse("user_message", false);
            
								$user_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("user_message"));
								va_mail($receiver_email, $t->get_var("user_subject"), $user_message, $email_headers);
							}
							// end sending user notification

						} else {
							$record_saved = false;
						}
					}
				}

				if ($record_saved) {
					// if message was saved or sent successfully save random param to prevent double click
					set_session("session_rnd", $rnd);
					if ($operation == "save") {
						header("Location: user_messages.php?mfid=3&operation=saved");	
						exit;
					} else if ($operation == "send") {
						header("Location: user_messages.php?mfid=2&operation=sent");	
						exit;
					}
				} else {
					$r->errors = "DB errors ocurred.";
				}
			}
		}
		$r->set_form_parameters();

		show_message_type($r->get_value("mtid"), $r->get_value("mkid"));

		$t->parse("message_form_block", true);

		$block_parsed = true;
		return;
	} else if ($operation == "edit") {
		$r->change_property("user_id", USE_IN_WHERE, true); // user can edit only messages assigned to him
		$r->change_property("from_user_id", USE_IN_WHERE, true); // user can edit only messages he has created
		$r->set_value("mid", $mid); 
		$r->set_value("user_id", $user_id); 
		$r->set_value("from_user_id", $user_id); 
		$is_exists = $r->get_db_values();
		if (!$is_exists) {
			// clear mid parameter as user can edit this message
			$r->set_value("mid", ""); 
		}
		$r->set_form_parameters();
		$t->parse("message_form_block", true);
		$block_parsed = true;
		return;
	} else if ($operation == "read") {

		$sql  = " SELECT * FROM " . $table_prefix . "messages ";
		$sql .= " WHERE message_id=" . $db->tosql($mid, INTEGER);
		$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			// show message
			$message_to = $db->f("message_to");
			$message_from = $db->f("message_from");
			$message_type = $db->f("message_type");
			$message_key_id = $db->f("message_key_id");
			$message_subject = $db->f("message_subject");
			$message_text = $db->f("message_text");
			$from_user_id = $db->f("from_user_id");

			$t->set_var("message_to", htmlspecialchars($message_to));
			$t->set_var("message_from", htmlspecialchars($message_from));
			$t->set_var("message_subject", htmlspecialchars($message_subject));
			$t->set_var("message_text", nl2br(htmlspecialchars($message_text)));
			if ($message_type) {
				show_message_type($message_type, $message_key_id);
			}
			// check if for user available profile to show link 
			$sql  = " SELECT profile_id FROM " . $table_prefix . "profiles ";
			$sql .= " WHERE user_id=" . $db->tosql($from_user_id, INTEGER);
			$sql .= " AND is_shown=1 AND is_approved=1 ";
			$db->query($sql);
			if ($db->next_record()) {
				$profile_id = $db->f("profile_id");
				$profile_url = "profiles_view.php?pid=".urlencode($profile_id);
				$t->set_var("profile_url", htmlspecialchars($profile_url));
				$t->sparse("profile_from", false);
			}

			// show buttons
			$reply_url = new VA_URL("user_messages.php");
			$reply_url->add_parameter("rmid", CONSTANT, $mid);
			$reply_url->add_parameter("mfid", REQUEST, "mfid");
			$reply_url->add_parameter("operation", CONSTANT, "reply");

			// show buttons
			$forward_url = new VA_URL("user_messages.php");
			$forward_url->add_parameter("fmid", CONSTANT, $mid);
			$forward_url->add_parameter("mfid", REQUEST, "mfid");
			$forward_url->add_parameter("operation", CONSTANT, "forward");

			// show buttons
			$trash_url = new VA_URL("user_messages.php");
			$trash_url->add_parameter("mid", CONSTANT, $mid);
			$trash_url->add_parameter("operation", CONSTANT, "trash");

			$t->set_var("reply_url", $reply_url->get_url());
			$t->set_var("forward_url", $forward_url->get_url());
			$t->set_var("trash_url", $trash_url->get_url());

			$t->parse("message_view_block", true);
		}
		$block_parsed = true;
		return;
	} else if ($operation == "reply" || $operation == "forward") {
		$r->change_property("user_id", USE_IN_WHERE, true); 
		if ($operation == "reply") {
			$r->set_value("mid", $rmid); 
		} else {
			$r->set_value("mid", $fmid); 
		}
		$r->set_value("user_id", $user_id); 
		$is_exists = $r->get_db_values();
		if ($is_exists) {
			// clear some parameters before we go
			$r->set_value("mid", ""); 
			$r->set_value("rmid", ""); 
			$r->set_value("fmid", ""); 
			$r->set_value("message_to", ""); 
			if ($operation == "reply") {
				$r->set_value("rmid", $rmid); 
				// check nickname to set automatically it in message_to field
				$from_user_id = $r->get_value("from_user_id"); 
				$sql  = " SELECT nickname FROM " . $table_prefix . "users ";
				$sql .= " WHERE user_id=" . $db->tosql($from_user_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$nickname = $db->f("nickname"); 
					$r->set_value("message_to", $nickname); 
				}
			} else {
				$r->set_value("fmid", $fmid); 
			}
			// make some transformes with subject and message text 
			$message_subject = $r->get_value("message_subject");
			$message_subject = preg_replace("/^(re|fe)\:+\s*/i", "", $message_subject);
			if ($operation == "reply") {
				$message_subject = "RE: ".$message_subject;
			} else {
				$message_subject = "FW: ".$message_subject;
			}
			$message_text = $r->get_value("message_text");
			$message_text = "\n\n\n>" . str_replace("\n", "\n>", $message_text);

			$r->set_value("message_subject", $message_subject); 
			$r->set_value("message_text", $message_text); 
		} else {
			$r->set_value("mid", ""); 
		}
		$r->set_form_parameters();

		show_message_type($r->get_value("mtid"), $r->get_value("mkid"));

		$t->parse("message_form_block", true);
		$block_parsed = true;
		return;
	}

	if ($operation == "sent") {
		$t->parse("message_sent_block", false);
	} else if ($operation == "saved") {
		$t->parse("message_saved_block", false);
	}


	$s = new VA_Sorter($settings["templates_dir"], "sorter.html", "user_messages.php");
	$s->set_default_sorting(1, "desc");
	$s->set_sorter(DATE_MSG, "sorter_date", "1", "date_added");

	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", "user_messages.php");

	$where = "";
	if ($mfid && isset($system_folders[$mfid])) {
		$where .= " AND m.system_folder_id=" . $db->tosql($mfid, INTEGER);
	} else {
		$where .= " AND m.system_folder_id=1 ";
	}
	$where .= " AND m.date_deleted IS NULL ";

	// show messages if any other operations are not selected 
	if (strlen($mfid)) {
		// set up variables for navigator
		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "messages m ";
		$sql .= " WHERE m.user_id=" . $db->tosql($user_id, INTEGER);
		$sql .= $where;
		$db->query($sql);
		$db->next_record();
		$total_records = $db->f(0);
  
		$records_per_page = 25;
		$pages_number = 5;
		$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);
			
		$sql  = " SELECT m.* ";
		$sql .= "	FROM " . $table_prefix . "messages m ";
		$sql .= " WHERE m.user_id=" . $db->tosql($user_id, INTEGER);
		$sql .= $where;
		$sql .= $s->order_by;
		$db->RecordsPerPage = $records_per_page;
		$db->PageNumber = $page_number;
		$db->query($sql);
		if ($db->next_record()) {

			$trash_url = new VA_URL("user_messages.php");
			$trash_url->add_parameter("mfid", REQUEST, "mfid");
			$trash_url->add_parameter("operation", CONSTANT, "trash");

			$delete_url = new VA_URL("user_messages.php");
			$delete_url->add_parameter("mfid", REQUEST, "mfid");
			$delete_url->add_parameter("operation", CONSTANT, "delete");

			$undel_url = new VA_URL("user_messages.php");
			$undel_url->add_parameter("mfid", REQUEST, "mfid");
			$undel_url->add_parameter("operation", CONSTANT, "undel");

			$msg_url = new VA_URL("user_messages.php");
			$msg_url->add_parameter("mfid", REQUEST, "mfid");
			if ($mfid == 3) {
				// user can edit messages only in draft folder
				$msg_url->add_parameter("operation", CONSTANT, "edit");
			} else {
				$msg_url->add_parameter("operation", CONSTANT, "read");
			}

			if ($mfid == 1) {
				$t->parse("message_from_header", false);
			} else if ($mfid == 2 || $mfid == 3) {
				$t->parse("message_to_header", false);
			} else {
				$t->parse("message_from_header", false);
				$t->parse("message_to_header", false);
			}

			$t->parse("sorters", false);
			$t->set_var("no_messages", "");
			do {
				$mid = $db->f("message_id");
				$message_id = $db->f("message_id");
				$message_to = $db->f("message_to");
				$message_from = $db->f("message_from");
				$message_subject = $db->f("message_subject");
				$message_type = $db->f("message_type");
				$date_added = $db->f("date_added", DATETIME);
				$date_read = $db->f("date_read", DATETIME);

				$date_formatted = va_date($datetime_show_format, $date_added);

				if ($message_type == 0 || $message_type == 1) {
					$message_type_desc = EMAIL_MESSAGE_MSG;
				} else if ($message_type == 2) {
					$message_type_desc = PRODUCT_MSG;
				} else if ($message_type == 15) {
					$message_type_desc = PROFILE_TITLE;
				} else {
					$message_type_desc = EMAIL_MESSAGE_MSG;
				}

				if (is_array($date_read) && isset($date_read[YEAR])) {
					$message_class = "messageRead";
				} else {
					$message_class = "messageUnread";
				}

				// prepare address
				$t->set_var("mid", $message_id);
				$t->set_var("message_id", $message_id);
				$t->set_var("message_subject", htmlspecialchars($message_subject));
				$t->set_var("message_type", htmlspecialchars($message_type_desc));
				$t->set_var("date_added", htmlspecialchars($date_formatted));
				$t->set_var("message_class", htmlspecialchars($message_class));
				$t->set_var("message_to", htmlspecialchars($message_to));
				$t->set_var("message_from", htmlspecialchars($message_from));

				if ($mfid == 4) {
					// for Trash folder show delete and untrash icon
					$delete_url->add_parameter("mid", CONSTANT, $message_id);
					$t->set_var("delete_url", htmlspecialchars($delete_url->get_url()));
					$t->parse("delete_operation", false);

					$undel_url->add_parameter("mid", CONSTANT, $message_id);
					$t->set_var("undel_url", htmlspecialchars($undel_url->get_url()));
					$t->parse("undel_operation", false);
				} else {
					$trash_url->add_parameter("mid", CONSTANT, $message_id);
					$t->set_var("trash_url", htmlspecialchars($trash_url->get_url()));
					$t->parse("trash_operation", false);
				}
				// set view/edit url
				$msg_url->add_parameter("mid", CONSTANT, $message_id);
				$t->set_var("msg_url", htmlspecialchars($msg_url->get_url()));

				if ($mfid == 1) {
					$t->parse("message_from_data", false);
				} else if ($mfid == 2 || $mfid == 3) {
					$t->parse("message_to_data", false);
				} else {
					$t->parse("message_from_data", false);
					$t->parse("message_to_data", false);
				}
  
				$t->parse("messages",true);
			
			} while($db->next_record());
			
		} else {
			$t->set_var("sorters", "");
			$t->set_var("messages", "");
			$t->set_var("navigator", "");
			$t->parse("no_messages", false);
		}

		if ($mfid == 1 || $mfid == 3) {
			// for Inbox folder show link to write a new message after messages list
			$t->parse("new_message_bottom", false);
		}

		$t->parse("messages_block", false);
	}

	
	$block_parsed = true;

/*
      `message_id` INT(11) NOT NULL AUTO_INCREMENT,
      `parent_message_id` INT(11) default '0',
      `reply_message_id` INT(11) default '0',
      `forward_message_id` INT(11) default '0',
      `admin_id` INT(11) default '0',
      `user_id` INT(11) default '0',
      `system_folder_id` TINYINT default '1',
      `user_folder_id` INT(11) default '0',
      `from_admin_id` INT(11) default '0',
      `from_user_id` INT(11) default '0',
      `message_from` VARCHAR(128),
      `message_type` TINYINT default '0',
      `message_key_id` INT(11) default '0',
      `message_to` VARCHAR(255),
      `message_cc` VARCHAR(255),
      `message_bcc` VARCHAR(255),
      `message_subject` VARCHAR(255),
      `message_text` TEXT,
      `date_added` DATETIME,
      `date_sent` DATETIME,
      `date_read` DATETIME,
      `date_replied` DATETIME,
      `date_modified` DATETIME,
      `date_deleted` DATETIME
      ,KEY date_added (date_added)
      ,KEY message_key_id (message_key_id)
      ,KEY message_type (message_type)
      ,KEY parent_message_id (parent_message_id)
      ,PRIMARY KEY (message_id)
      ,KEY admin_id (admin_id)
      ,KEY user_id (user_id)
      ,KEY from_admin_id (from_admin_id)
      ,KEY from_user_id (from_user_id)
      ,KEY system_folder_id (system_folder_id)
      ,KEY user_folder_id (user_folder_id))";
*/

function check_receiver($parameter)
{
	global $r, $db, $table_prefix, $receivers;
	$control_name = $parameter[CONTROL_NAME];
	if ($parameter[IS_VALID]) {
		$control_value = $parameter[CONTROL_VALUE];
		$sql  = " SELECT * FROM ".$table_prefix."users ";
		$sql .= " WHERE nickname=" . $db->tosql($control_value, TEXT);
		$sql .= " AND is_approved=1 ";
		$db->query($sql);
		if ($db->next_record()) {
			$to_user_id = $db->f("user_id");
			$to_name = $control_value;
			$to_email = $db->f("email");
			if (strlen($db->f("name"))) {
				$to_name = $db->f("name");
			} elseif (strlen($db->f("first_name")) || strlen($db->f("last_name"))) {
				$to_name = $db->f("first_name") . " " . $db->f("last_name");
			}
			$receivers[$to_user_id] = array(
				"nickname" => $control_value,
				"name" => $to_name,
				"email" => $to_email,
			);
		} else {
			$r->parameters[$control_name][IS_VALID] = false;
			$r->parameters[$control_name][ERROR_DESC] = str_replace("{username}", "<b>".$control_value."</b>", USER_NOT_FOUND_MSG);
		}
	}
}

function show_message_type($mtid, $mkid)
{
	global $r, $t, $db, $table_prefix, $language_code, $current_date;

	if ($mtid && $mkid) {
	  $type_name = ""; $type_url = ""; $type_desc = "";
		if ($mtid == 2) {
			include("./includes/products_functions.php");
			$psql = new VA_Products();
			$sql_params = array(
				"select" => "item_name", 
				"where" => "WHERE item_id=" . $db->tosq($mkid, INTEGER),
			);
			$sql = $psql->_sql($sql_params, VIEW_CATEGORIES_ITEMS_PERM);
			$db->query($sql);
			if ($db->next_record()) {
				$type_name = PRODUCT_MSG;
				$type_desc = $db->f("item_name");
				$type_url = "product_details.php?item_id=".urlencode($mkid);
			}
		} else if ($mtid == 15) {
			$sql  = " SELECT p.profile_id, p.profile_name, p.birth_date, p.city, ";
			$sql .= " p.photo_id, up.tiny_photo, up.small_photo, up.large_photo, ";
			$sql .= " c.country_name,s.state_name ";
			$sql .= " FROM " . $table_prefix . "profiles p ";
			$sql .= " LEFT JOIN " . $table_prefix . "users_photos up ON up.photo_id=p.photo_id ";
			$sql .= " LEFT JOIN " . $table_prefix . "countries c ON c.country_id=p.country_id ";
			$sql .= " LEFT JOIN " . $table_prefix . "states s ON s.state_id=p.state_id ";
			$sql .= " WHERE profile_id=" . $db->tosql($mkid, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$type_name = PROFILE_TITLE;
				$type_desc = $db->f("profile_name");
				$type_url = "profiles_view.php?pid=".urlencode($mkid);
				// check age
				$birth_date = $db->f("birth_date", DATETIME);
				$birth_date_ts = va_timestamp($birth_date);
				$age = $current_date[YEAR] - $birth_date[YEAR];
				if ($birth_date[MONTH] < $current_date[MONTH] || ($birth_date[MONTH] == $current_date[MONTH] && $birth_date[DAY] < $current_date[DAY])) {
					$age--;
				}
				$type_desc .= ", " . $age;

				$country_name = get_translation($db->f("country_name"));
				if ($country_name) {
					$type_desc .= ", " . $country_name;
				}
				$state_name = get_translation($db->f("state_name"));
				if ($state_name) {
					$type_desc .= ", " . $state_name;
				}

			}

		}


		if (strlen($type_name) && strlen($type_desc)) {
			$t->set_var("type_name", $type_name);
			$t->set_var("type_url",  $type_url);
			$t->set_var("type_desc", $type_desc);

			$t->sparse("form_message_type", false);
			$t->sparse("view_message_type", false);
		}
	}

}

?>