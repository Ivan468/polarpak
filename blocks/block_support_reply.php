<?php

	include_once("./includes/record.php");
	include_once("./includes/support_functions.php");
	include_once("./messages/".$language_code."/support_messages.php");

	$default_title = "{SUPPORT_REQUEST_INF_TITLE}";

	$html_template = get_setting_value($block, "html_template", "block_support_reply.html"); 
  $t->set_file("block_body", $html_template);
	set_script_tag("js/attachments.js");

	$eol = get_eol();
	$support_id = get_param("support_id");
	$vc = get_param("vc");
	$action = get_param("action");
	$rnd = get_param("rnd");
	$user_id = get_session("session_user_id");		
	$user_type_id = get_session("session_user_type_id");

	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", "");
	$secure_redirect = get_setting_value($settings, "secure_redirect", 0);
	$secure_user_ticket = get_setting_value($settings, "secure_user_ticket", 0);
	$secure_user_tickets = get_setting_value($settings, "secure_user_tickets", 0);
	if ($secure_user_ticket) {
		$support_url = $secure_url . get_custom_friendly_url("support.php");
		$support_messages_url = $secure_url . get_custom_friendly_url("support_messages.php");
		$support_attachment_url = $secure_url . get_custom_friendly_url("support_attachment.php");
		$user_support_attachments_url = $secure_url . get_custom_friendly_url("user_support_attachments.php");
	} else {
		$support_url = $site_url . get_custom_friendly_url("support.php");
		$support_messages_url = $site_url . get_custom_friendly_url("support_messages.php");
		$support_attachment_url = $site_url . get_custom_friendly_url("support_attachment.php");
		$user_support_attachments_url = $site_url . get_custom_friendly_url("user_support_attachments.php");
	}
	if ($secure_user_tickets) {
		$user_support_url = $secure_url . get_custom_friendly_url("user_support.php");
	} else {
		$user_support_url = $site_url . get_custom_friendly_url("user_support.php");
	}
	$user_home_url = $site_url . get_custom_friendly_url("user_home.php");
	if (!$is_ssl && $secure_user_ticket && $secure_redirect && preg_match("/^https/i", $secure_url)) {
		header("Location: " . $support_messages_url . "?support_id=" . urlencode($support_id) . "&vc=" . urlencode($vc));
		exit;
	}

	$support_settings = array();
	$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type='support'";
	if (isset($site_id)) {
		$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
		$sql .= " ORDER BY site_id ASC ";
	} else {
		$sql .= " AND site_id=1 ";
	}
	$db->query($sql);
	while($db->next_record()) {
		$support_settings[$db->f("setting_name")] = $db->f("setting_value");
	}
	$attachments_users_allowed = get_setting_value($support_settings, "attachments_users_allowed", 0);
	$use_random_image = intval(get_setting_value($support_settings, "use_random_image", 1));

	if (($use_random_image == 2) || ($use_random_image == 1 && !strlen(get_session("session_user_id")))) { 
		$use_validation = true;
	} else {
		$use_validation = false;
	}

	// connection for support attachemnts 
	$dba = new VA_SQL();
	$dba->DBType       = $db->DBType;
	$dba->DBDatabase   = $db->DBDatabase;
	$dba->DBUser       = $db->DBUser;
	$dba->DBPassword   = $db->DBPassword;
	$dba->DBHost       = $db->DBHost;
	$dba->DBPort       = $db->DBPort;
	$dba->DBPersistent = $db->DBPersistent;

	$t->set_var("user_support_href", $user_support_url);
	$t->set_var("user_home_href", $user_home_url);
	$t->set_var("support_messages_href", $support_messages_url);
	$t->set_var("user_support_attachments_url", $user_support_attachments_url);
	$t->set_var("rnd", va_timestamp());

	$errors = "";

	if (!strlen($support_id)) {
		$errors = SUPPORT_MISS_ID_ERROR;
	} else if(!strlen($vc) && !strlen($user_id)) {
		$errors = SUPPORT_MISS_CODE_ERROR;
	}

	$return_page = $support_messages_url . "?support_id=" . $support_id . "&vc=" . $vc;

	$ticket_user_id = "";
	$dep_name = ""; $dep_user_reply_admin_mail = ""; $dep_user_reply_user_mail = "";
	$sql  = " SELECT s.support_id, s.dep_id, s.support_type_id, s.support_product_id, ";
	$sql .= " s.user_id, s.user_name, s.user_email, s.remote_address, s.identifier, ";
	$sql .= " s.environment, p.product_name, st.type_name, s.summary, s.description,   ";
	$sql .= " sd.dep_name, sd.user_reply_admin_mail, sd.user_reply_user_mail,  ";
	$sql .= " ss.status_name, sp.priority_name, s.date_added, s.date_viewed, s.date_modified, ";
	$sql .= " aa.admin_name as assign_to ";
	$sql .= " FROM ((((((" . $table_prefix . "support s ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_departments sd ON s.dep_id=sd.dep_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_products p ON p.product_id=s.support_product_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_statuses ss ON ss.status_id=s.support_status_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_types st ON st.type_id=s.support_type_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_priorities sp ON sp.priority_id=s.support_priority_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "admins aa ON aa.admin_id=s.admin_id_assign_to) ";
	$sql .= " WHERE s.support_id=" . $db->tosql($support_id, INTEGER);
	$db->query($sql);
	if($db->next_record() && !strlen($errors))
	{
		$support_id = $db->f("support_id");
		$ticket_id = $db->f("support_id");
		$dep_id = $db->f("dep_id");
		$ticket_user_id = $db->f("user_id");
		$t->set_var("user_id", $ticket_user_id);
		$user_name = $db->f("user_name");
		$user_email = $db->f("user_email");
		$request_posted_by = $user_name . " <" . $user_email . ">";
		$summary = $db->f("summary");
		$identifier = $db->f("identifier");
		$environment = $db->f("environment");
		$description = $db->f("description");
		$ticket_type = get_translation($db->f("type_name"));

		// get ids for department, type and product
		$ticket_dep_id = $db->f("dep_id");
		$ticket_type_id = $db->f("support_type_id");
		$ticket_product_id = $db->f("support_product_id");

		// department related information
		$dep_name = get_translation($db->f("dep_name"));
		$dep_user_reply_admin_mail = json_decode($db->f("user_reply_admin_mail"), true);
		$dep_user_reply_user_mail = json_decode($db->f("user_reply_user_mail"), true);

		$t->set_var("user_name", htmlspecialchars($user_name));
		$t->set_var("user_email", $user_email);
		$t->set_var("identifier", htmlspecialchars($identifier));
		$t->set_var("environment", htmlspecialchars($environment));
		$t->set_var("dep_name", htmlspecialchars($dep_name));
		$t->set_var("remote_address", $db->f("remote_address"));
		$t->set_var("product_name", $db->f("product_name"));
		$t->set_var("product", $db->f("product_name"));
		$t->set_var("assign_to", $db->f("assign_to"));

		$t->set_var("type", $ticket_type);
		$current_status = get_translation($db->f("status_name"));
		$t->set_var("current_status", $current_status);
		$priority = get_translation($db->f("priority_name"));
		$t->set_var("priority", $priority);
		$date_modified = $db->f("date_modified", DATETIME);
		$date_modified_string = va_date($datetime_show_format, $date_modified);
		$t->set_var("date_modified", $date_modified_string);


		$ticket_date = $db->f("date_added", DATETIME);
		$ticket_date_string = va_date($datetime_show_format, $ticket_date);
		$t->set_var("request_added", $ticket_date_string);

		$request_viewed = $db->f("date_viewed", DATETIME);

		$t->set_var("summary", htmlspecialchars($summary));
		$t->set_var("request_description", nl2br(htmlspecialchars($description)));

		$last_message = $description;

	}	else if(!strlen($errors)) {
		$errors = SUPPORT_WRONG_ID_ERROR;
	}

	if(!strlen($errors) && $vc != md5($support_id . $ticket_date[3].$ticket_date[4].$ticket_date[5]) && (!strlen($user_id) || $user_id != $ticket_user_id)) {
		$errors = SUPPORT_WRONG_CODE_ERROR;
	}

	if(strlen($errors))
	{
		$t->set_var("errors_list", $errors);
		$t->parse("global_errors", false);
		$block_parsed = true;
		return;
	}

	// update date when ticket was viewed by customer
	$sql  = " UPDATE " . $table_prefix . "support_messages SET date_viewed=" . $db->tosql(va_time(), DATETIME);
	$sql .= " WHERE support_id=" . $db->tosql($support_id, INTEGER);
	$sql .= " AND internal=0 AND admin_id_assign_by IS NOT NULL AND admin_id_assign_by<>0 AND date_viewed IS NULL";
	$db->query($sql);

	$r = new VA_Record($table_prefix . "support_messages");
	$r->add_where("message_id", INTEGER);
	$r->add_textbox("support_id", INTEGER);
	$r->add_textbox("dep_id", INTEGER);
	$r->add_textbox("internal", INTEGER);
	$r->add_textbox("support_status_id", INTEGER);
	//$r->add_textbox("admin_id_assign_by", INTEGER);
	$r->add_textbox("message_text", TEXT, SUPPORT_MESSAGE_FIELD);
	$r->change_property("message_text", PARSE_NAME, "response_message");
	$r->change_property("message_text", REQUIRED, true);
	$r->change_property("message_text", TRIM, true);
	$r->add_textbox("date_added", DATETIME);
	$r->add_textbox("validation_number", TEXT, VALIDATION_CODE_FIELD);
	$r->change_property("validation_number", USE_IN_INSERT, false);
	$r->change_property("validation_number", USE_IN_UPDATE, false);
	$r->change_property("validation_number", USE_IN_SELECT, false);
	if ($use_validation) {
		$r->change_property("validation_number", REQUIRED, true);
		$r->change_property("validation_number", SHOW, true);
	} else {
		$r->change_property("validation_number", REQUIRED, false);
		$r->change_property("validation_number", SHOW, false);
	}

	$r->get_form_values();

	$session_rnd = get_session("session_rnd");
	$action = get_param("action");
	$rnd = get_param("rnd");

	if($action && $rnd != $session_rnd)
	{
		$r->validate();
		if ($use_validation) {
			if ($r->is_empty("validation_number")) {
				$r->errors .= str_replace("{field_name}", VALIDATION_CODE_FIELD, VALIDATION_MESSAGE);
			} else {
				$validated_number = check_image_validation($r->get_value("validation_number"));
				if (!$validated_number) {
					$r->errors .= str_replace("{field_name}", VALIDATION_CODE_FIELD, VALIDATION_MESSAGE);
				} elseif ($r->errors) {
					// saved validated number for following submits	
					set_session("session_validation_number", $validated_number);
				}
			} 
		}

		if(!strlen($r->errors))
		{
			// get status for reply message
			$sql = " SELECT status_id,status_name,status_caption FROM " . $table_prefix . "support_statuses WHERE status_type='USER_REPLY' ";
			$db->query($sql);
			if($db->next_record()) {
				$r->set_value("support_status_id", $db->f("status_id"));	
				$status_name = get_translation($db->f("status_name"));
				$status_caption = $db->f("status_caption");
			} else {
				$r->set_value("support_status_id", 0);	
				$status_name = va_message("TICKET_USER_REPLY_MSG");
				$status_caption = va_message("TICKET_USER_REPLY_MSG");
			}

			$date_added = va_time();
			$r->set_value("dep_id", $dep_id);
			$r->set_value("internal", 0);
			$r->set_value("date_added", $date_added);

			if($r->insert_record())
			{ 
				$message_id = $db->last_insert_id();
				$r->set_value("message_id", $message_id);

				// update attachments
				$sql  = " UPDATE " . $table_prefix . "support_attachments ";
				$sql .= " SET message_id=" . $db->tosql($message_id, INTEGER);
				$sql .= " , attachment_status=1 ";
				$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
				$sql .= " AND support_id=" . $db->tosql($support_id, INTEGER);
				$sql .= " AND message_id=0 ";
				$sql .= " AND attachment_status=0 ";
				$db->query($sql);

				// check attachments
				$attachments = array();
				if ($user_id) {
					$sql  = " SELECT attachment_id, file_name, file_path FROM " . $table_prefix . "support_attachments ";
					$sql .= " WHERE support_id=" . $db->tosql($support_id, INTEGER);
					$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
					$sql .= " AND message_id=" . $db->tosql($message_id, INTEGER);;
					$sql .= " AND attachment_status=1 ";
					$db->query($sql);
					while ($db->next_record()) {
						$filename = $db->f("file_name");
						$filepath = $db->f("file_path");
						$attachments[] = array($filename, $filepath);
					}
				}

				// check if outgoing_email could be found 
				$outgoing_email = get_outgoing_email($ticket_dep_id, $ticket_type_id, $ticket_product_id);

				// prepare tags for email
				$mail_tags = array();
				$ticket_date_string = va_date($datetime_show_format, $ticket_date);
				$message_date_string = va_date($datetime_show_format, va_time());
				$site_url = get_setting_value($settings, "site_url", "");
				$admin_site_url = get_setting_value($settings, "admin_site_url", $site_url."admin/");
				$vc = md5($ticket_id . $ticket_date[3].$ticket_date[4].$ticket_date[5]);
				$ticket_url = $site_url . "support_messages.php?support_id=" . $ticket_id. "&vc=" . $vc;
				$admin_ticket_url = $admin_site_url."admin_support_reply.php?support_id=".$support_id;
				$message_text = $r->get_value("message_text");

				$mail_tags = array(
					"ticket_id" => $ticket_id,
					"support_id" => $ticket_id,
					"message_id" => $message_id,
					"vc" => $vc,
	    
					"ticket_added" => $ticket_date_string,
					"request_added" => $ticket_date_string,
					"message_added" => $message_date_string,
					"date_added" => $ticket_date_string,
					"date_modified" => $message_date_string,

					"status" => $status_name,
					"status_name" => $status_name,
					"status_caption" => $status_caption,
					"identifier" => $identifier,
					"environment" => $environment,

					"user_id" => $user_id,
					"user_name" => $user_name,
					"user_email" => $user_email,
					"from_user" => $user_name,
					"from_email" => $user_email,
	    
					"summary" => $summary,
					"subject" => $summary,
	    
					"description" => $description,
					"message_text" => $message_text,
	    
					"dep_name" => $dep_name,
	    
					"site_url" => $site_url,
					"support_url" => $ticket_url,
					"ticket_url" => $ticket_url,
					"user_support_url" => $ticket_url,
					"user_ticket_url" => $ticket_url,
					"admin_support_url" => $admin_ticket_url,
					"admin_ticket_url" => $admin_ticket_url,
				);


				// admin and user notifications for user reply
				// check global admin and user notification 
				$user_reply_admin_notification = get_setting_value($support_settings, "user_reply_admin_notification", 0);
				$user_reply_user_notification = get_setting_value($support_settings, "user_reply_user_notification", 0);

				// check department notification settings
				$admin_dep_notification = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_notification", 0);
				$admin_hp_disable = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_hp_disable", 0);
				if ($admin_hp_disable) { $user_reply_admin_notification = 0; }
				$user_dep_notification = get_setting_value($dep_user_reply_user_mail, "user_reply_user_notification", 0);
				$user_hp_disable = get_setting_value($dep_user_reply_user_mail, "user_reply_user_hp_disable", 0);
				if ($user_hp_disable) { $user_reply_user_notification = 0; }

				// send global email notification to admin
				if ($user_reply_admin_notification) {
					$mail_to = get_setting_value($support_settings, "user_reply_admin_to", $settings["admin_email"]);

					$admin_subject = get_setting_value($support_settings, "user_reply_admin_subject", $summary);
					$admin_message = get_setting_value($support_settings, "user_reply_admin_message", $message_text);
		    
					$email_headers = array();
					if ($outgoing_email) {
						$email_headers["from"] = $outgoing_email;	
					} else {
						$email_headers["from"] = get_setting_value($support_settings, "user_reply_admin_from", $settings["admin_email"]); 
					}
					$email_headers["cc"] = get_setting_value($support_settings, "user_reply_admin_cc");
					$email_headers["bcc"] = get_setting_value($support_settings, "user_reply_admin_bcc");
					$email_headers["reply_to"] = get_setting_value($support_settings, "user_reply_admin_reply_to");
					$email_headers["return_path"] = get_setting_value($support_settings, "user_reply_admin_return_path");
					$email_headers["mail_type"] = get_setting_value($support_settings, "user_reply_admin_message_type");
		    
					va_mail($mail_to, $admin_subject, $admin_message, $email_headers, $attachments, $mail_tags);
				} // end admin global notification

				// send department email notification to admin
				if ($admin_dep_notification) {
					$mail_to = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_to", $settings["admin_email"]);

					$admin_subject = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_subject", $summary);
					$admin_message = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_message", $message_text);
		    
					$email_headers = array();
					if ($outgoing_email) {
						$email_headers["from"] = $outgoing_email;	
					} else {
						$email_headers["from"] = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_from", $settings["admin_email"]); 
					}
					$email_headers["cc"] = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_cc");
					$email_headers["bcc"] = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_bcc");
					$email_headers["reply_to"] = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_reply_to");
					$email_headers["return_path"] = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_return_path");
					$email_headers["mail_type"] = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_message_type");
		    
					va_mail($mail_to, $admin_subject, $admin_message, $email_headers, $attachments, $mail_tags);
				} // end admin department notification

				// send global email notification to user 
				if ($user_reply_user_notification) {
					$user_subject = get_setting_value($support_settings, "user_reply_user_subject", $summary);
					$user_message = get_setting_value($support_settings, "user_reply_user_message", $message_text);
		    
					$email_headers = array();
					if ($outgoing_email) {
						$email_headers["from"] = $outgoing_email;	
					} else {
						$email_headers["from"] = get_setting_value($support_settings, "user_reply_user_from", $settings["admin_email"]);
					}
					$email_headers["cc"] = get_setting_value($support_settings, "user_reply_user_cc");
					$email_headers["bcc"] = get_setting_value($support_settings, "user_reply_user_bcc");
					$email_headers["reply_to"] = get_setting_value($support_settings, "user_reply_user_reply_to");
					$email_headers["return_path"] = get_setting_value($support_settings, "user_reply_user_return_path");
					$email_headers["mail_type"] = get_setting_value($support_settings, "user_reply_user_message_type");
		    
					va_mail($user_email, $user_subject, $user_message, $email_headers, $attachments, $mail_tags);
				} // end user global notification

				// send department email notification to user 
				if ($user_dep_notification) {
					$user_subject = get_setting_value($dep_user_reply_user_mail, "user_reply_user_subject", $summary);
					$user_message = get_setting_value($dep_user_reply_user_mail, "user_reply_user_message", $message_text);
		    
					$email_headers = array();
					if ($outgoing_email) {
						$email_headers["from"] = $outgoing_email;	
					} else {
						$email_headers["from"] = get_setting_value($dep_user_reply_user_mail, "user_reply_user_from", $settings["admin_email"]);
					}
					$email_headers["cc"] = get_setting_value($dep_user_reply_user_mail, "user_reply_user_cc");
					$email_headers["bcc"] = get_setting_value($dep_user_reply_user_mail, "user_reply_user_bcc");
					$email_headers["reply_to"] = get_setting_value($dep_user_reply_user_mail, "user_reply_user_reply_to");
					$email_headers["return_path"] = get_setting_value($dep_user_reply_user_mail, "user_reply_user_return_path");
					$email_headers["mail_type"] = get_setting_value($dep_user_reply_user_mail, "user_reply_user_message_type");

					va_mail($user_email, $user_subject, $user_message, $email_headers, $attachments, $mail_tags);
				} // end user department notification

        // update support request info
				$sql  = " UPDATE " . $table_prefix . "support SET ";
				$sql .= " admin_id_assign_to=0, admin_id_assign_by=0, ";
				$sql .= " support_status_id=" . $db->tosql($r->get_value("support_status_id"), INTEGER);
				$sql .= " , date_modified=" . $db->tosql(va_time(), DATETIME);
				$sql .= " WHERE support_id=" . $db->tosql($support_id, INTEGER);
				$db->query($sql);
			}

			header("Location: " . $return_page);
			exit;
		}
		else
		{
			//$errors .= "Please provide information in the sections with red, italicized headings, then click 'Submit'.<br>";	
			set_session("session_rnd", "");
		}
	}
	else // new page (set default values)
	{
		//$r->set_value("is_showing", "1");
	}

	// set ticket information
	$t->set_var("summary", htmlspecialchars($summary));
	$t->set_var("description", htmlspecialchars($description));
	$t->set_var("user_name", htmlspecialchars($user_name));
	$t->set_var("identifier", htmlspecialchars($identifier));
	$t->set_var("environment", htmlspecialchars($environment));

	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", $support_messages_url);

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "support_messages WHERE support_id=" . $db->tosql($support_id, INTEGER) . " AND internal=0 ");
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 5;

	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = " SELECT sm.message_id, sm.admin_id_assign_by,a.admin_name,ss.status_id,ss.status_name, ";
	$sql .= " sm.message_text, sm.date_added, sm.date_viewed ";
	$sql .= " FROM ((" . $table_prefix . "support_messages sm ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_statuses ss ON ss.status_id=sm.support_status_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "admins a ON a.admin_id=sm.admin_id_assign_by) ";
	$sql .= " WHERE sm.support_id=" . $db->tosql($support_id, INTEGER);
	$sql .= " AND internal=0 ";
	$sql .= " ORDER BY sm.date_added DESC ";

	$db->query($sql);
	if($db->next_record())
	{
		$last_message = $db->f("message_text");

		do
		{
			$message_id = $db->f("message_id");
			$status = get_translation($db->f("status_name"));
			$t->set_var("status", $status);

			if($db->f("admin_id_assign_by"))
			{
				$posted_by = $db->f("admin_name");
				$viewed_by = SUPPORT_VIEWED_BY_USER_MSG;
			}
			else 
			{
				$posted_by = strlen($user_name) ? $user_name . " <" . $user_email . ">" : $user_email;
				$viewed_by = SUPPORT_VIEWED_BY_ADMIN_MSG;
			}
			$t->set_var("posted_by", htmlspecialchars($posted_by));
			$t->set_var("viewed_by", $viewed_by);

			$date_added = $db->f("date_added", DATETIME);
			$date_added_string = va_date($datetime_show_format, $date_added);
			$t->set_var("date_added", $date_added_string);

			$date_viewed = $db->f("date_viewed", DATETIME);
			if(is_array($date_viewed)) {
				$date_viewed_string = va_date($datetime_show_format, $date_viewed);
				$t->set_var("date_viewed", "<font color=\"blue\">" . $date_viewed_string . "</font>");
			} else {
				$t->set_var("date_viewed", "<font color=\"red\">" . SUPPORT_NOT_VIEWED_MSG . "</font>");
			}

			//-- check for attachments
			$attach_no = 0; $attachments_files = ""; 
			$sql  = " SELECT * FROM " . $table_prefix . "support_attachments ";
			$sql .= " WHERE support_id=" . $db->tosql($support_id, INTEGER);
			$sql .= " AND message_id=" . $db->tosql($message_id, INTEGER);
			$sql .= " AND attachment_status=1 ";
			$dba->query($sql);
			if ($dba->next_record()) {
				do {
					$attachment_id = $dba->Record["attachment_id"];
					$attachment_date = $dba->f("date_added", DATETIME);
					$file_name     = $dba->Record["file_name"];
					$file_path     = $dba->Record["file_path"];
					if (file_exists($file_path)) {
						$attach_no++;
						$size	         = get_nice_bytes(filesize($file_path));
						$attachment_vc = md5($attachment_id . $attachment_date[3].$attachment_date[4].$attachment_date[5]);
						$attachments_files  .= $attach_no . ". <a target=\"_blank\" href=\"" . $support_attachment_url . "?atid=" . $attachment_id . "&vc=" . $attachment_vc . "\">" . $file_name . "</a> (" . $size . ")&nbsp;&nbsp;";
					}
				} while ($dba->next_record());
			}
			if ($attach_no > 0) {
				$t->set_var("attachments_files", $attachments_files);
				$t->parse("message_attachments",false);
			} else { 
				$t->set_var("message_attachments","");
			}

			$message_text = $db->f("message_text");
			$message_text = process_message($message_text);
			$t->set_var("message_text", $message_text);

			$t->parse("records", true);
		} while($db->next_record());

	}
	else
	{
		$t->set_var("records", "");
	}


	// parse initial request on the last page
	if ($page_number == ceil($total_records / $records_per_page)) {
  
		$sql = " SELECT status_id,status_name,status_caption FROM " . $table_prefix . "support_statuses WHERE status_type='NEW' ";
		$db->query($sql);
		if ($db->next_record()) {
			$request_new_status = get_translation($db->f("status_name"));
		} else {
			$request_new_status = NEW_MSG;
		}
		$t->set_var("status", $request_new_status);
		$t->set_var("posted_by", htmlspecialchars($request_posted_by));
		$t->set_var("date_added", $ticket_date_string);
		$t->set_var("request_added", $ticket_date_string);

		$viewed_by = SUPPORT_VIEWED_BY_ADMIN_MSG;
		$t->set_var("viewed_by", $viewed_by);
		if(is_array($request_viewed)) {
			$request_viewed_string = va_date($datetime_show_format, $request_viewed);
			$t->set_var("date_viewed", "<font color=\"blue\">" . $request_viewed_string . "</font>");
		} else {
			$t->set_var("date_viewed", "<font color=\"red\">" . SUPPORT_NOT_VIEWED_MSG . "</font>");
		}

		//-- check for attachments
		$attach_no = 0; $attachments_files = ""; 
		$sql  = " SELECT * FROM " . $table_prefix . "support_attachments ";
		$sql .= " WHERE support_id=" . $db->tosql($support_id, INTEGER);
		$sql .= " AND message_id=0 AND attachment_status=1 ";
		$db->query($sql);
		if ($db->next_record()) {
			do {
				$attachment_id = $db->Record["attachment_id"];
				$attachment_date = $db->f("date_added", DATETIME);
				$file_name     = $db->Record["file_name"];
				$file_path     = $db->Record["file_path"];
				if (file_exists($file_path)) {
					$attach_no++;
					$size	         = get_nice_bytes(filesize($file_path));
					$attachment_vc = md5($attachment_id . $attachment_date[3].$attachment_date[4].$attachment_date[5]);
					$attachments_files .= $attach_no . ". <a target=\"_blank\" href=\"" . $support_attachment_url . "?atid=" . $attachment_id . "&vc=" . $attachment_vc . "\">" . $file_name . "</a> (" . $size . ")&nbsp;&nbsp;";
				}
			} while ($db->next_record());
		}
		if ($attach_no > 0) {
			$t->set_var("attachments_files", $attachments_files);
			$t->parse("message_attachments",false);
		} else { 
			$t->set_var("message_attachments","");
		}

  
		$t->set_var("message_text", process_message($description));
  
		$t->parse("records", true);
	}

	if(!strlen($action)) // (set default message text for reply)
	{
		//set last message by default 
		//$last_message = ">" . str_replace("\n", "\n>", $last_message);
		//$r->set_value("message_text", $last_message);
	}


	// check attachments
	$attachments_files = "";
	if ($attachments_users_allowed && $user_id) {
		$sql  = " SELECT attachment_id, file_name, file_path, date_added ";
		$sql .= " FROM " . $table_prefix . "support_attachments ";
		$sql .= " WHERE support_id=" . $db->tosql($support_id, INTEGER);
		$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
		$sql .= " AND message_id=0 ";
		$sql .= " AND attachment_status=0 ";
		$db->query($sql);
		while ($db->next_record()) {
			$attachment_id = $db->f("attachment_id");
			$filename = $db->f("file_name");
			$filepath = $db->f("file_path");
			$date_added = $db->f("date_added", DATETIME);
			$attachment_vc = md5($attachment_id . $date_added[3].$date_added[4].$date_added[5]);
			$filesize = filesize($filepath);
			if ($attachments_files) { $attachments_files .= "; "; }
			$attachments_files .= "<a href=\"support_attachment.php?atid=" .$attachment_id. "&vc=".$attachment_vc."\" target=\"_blank\">" . $filename . "</a> (" . get_nice_bytes($filesize) . ")";
		}
		if ($attachments_files) {
			$t->set_var("attached_files", $attachments_files);
			$t->set_var("attachments_class", "display: block;");
		} else {
			$t->set_var("attachments_class", "display: none;");
		}
		$t->parse("attachments_block", false);
	}

	$r->set_parameters();
	$t->set_var("page", $page_number);
	$t->set_var("vc", $vc);
	$t->parse("reply_form", false);
	$t->parse("request_info", false);

	$block_parsed = true;
