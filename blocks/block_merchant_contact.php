<?php

	include_once("./includes/record.php");

	$default_title = "{CONTACT_MERCHANT_TITLE}";

	$item_id = get_param("item_id");
	$user = get_param("user");
	if(!isset($merchant_id)) {
		$merchant_id = get_param("merchant_id");
	}
	if(!strlen($merchant_id)) {
		if ($cms_page_code == "product_details") {
			$sql  = " SELECT user_id FROM " . $table_prefix . "items ";
			$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
			$merchant_id = get_db_value($sql);
		}
	}
	// list of parameters to set for merchant and user
	$params = array(
		"first_name", "last_name", "company_name", "phone", "daytime_phone", "evening_phone", "cell_phone", 
	);

	// check if data for merchant available
	if (!isset($merchant_info) || !is_array($merchant_info) || !sizeof($merchant_info)) {
		$merchant_info = array();
		$sql  = " SELECT u.user_id, u.user_type_id,u.login,u.company_name,u.name,u.first_name,u.last_name,u.email, ";
		$sql .= " u.friendly_url, u.short_description, u.full_description, u.phone, u.daytime_phone, u.evening_phone, u.cell_phone, ";
		$sql .= " u.personal_image, u.nickname, u.country_code, u.registration_date, u.last_visit_page ";
		$sql .= " FROM (" . $table_prefix . "users u ";
		$sql .= " INNER JOIN " . $table_prefix . "user_types ut ON u.user_type_id=ut.type_id) ";
		$sql .= " WHERE user_id=" . $db->tosql($merchant_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$merchant_info = $db->Record;
			$merchant_info["registration_date"] = $db->f("registration_date", DATETIME);
			$merchant_info["last_visit_page"] = $db->f("last_visit_page", DATETIME);
			$merchant_name = get_translation($db->f("company_name"));
			if (!strlen($merchant_name)) {
				$merchant_name = get_translation($db->f("name"));
			}
			if (!strlen($merchant_name)) {
				$merchant_name = get_translation($db->f("login"));
			}
		} else {
			return;
		}
	}

	$merchant_type_id = get_setting_value($merchant_info, "user_type_id", "");
	$merchant_email = get_setting_value($merchant_info, "email", "");

	$html_template = get_setting_value($block, "html_template", "block_merchant_contact.html"); 
  $t->set_file("block_body", $html_template);
	$errors = false;


	$contact_settings = array();
	$setting_type = "user_contact_" . $merchant_type_id;
	$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
	if (isset($site_id)) {
		$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
		$sql .= " ORDER BY site_id ASC ";
	} else {
		$sql .= " AND site_id=1 ";
	}
	$db->query($sql);
	while ($db->next_record()) {
		$contact_settings[$db->f("setting_name")] = $db->f("setting_value");
	}

	$eol = get_eol();
	$use_random_image = get_setting_value($contact_settings, "use_random_image", 1);

	if (($use_random_image == 2) || ($use_random_image == 1 && !strlen(get_session("session_user_id")))) { 
		$use_validation = true;
	} else {
		$use_validation = false;
	}
	
	$t->set_var("site_url", $settings["site_url"]);

	$provide_info_message = str_replace("{button_name}", SEND_BUTTON, PROVIDE_INFO_MSG);
	$t->set_var("PROVIDE_INFO_MSG", $provide_info_message);

	$t->set_var("contact_href", $current_page);
	$t->set_var("user_home_href", get_custom_friendly_url("user_home.php"));
	$t->set_var("rnd", va_timestamp());
	$t->set_var("user", htmlspecialchars($user));
	$t->set_var("item_id", htmlspecialchars($item_id));

	$r = new VA_Record("", "contact");

	$r->add_textbox("user_name", TEXT, CONTACT_USER_NAME_FIELD);
	$r->change_property("user_name", TRIM, true);
	$r->change_property("user_name", REQUIRED, true);
	$r->add_textbox("user_email", TEXT, CONTACT_USER_EMAIL_FIELD);
	$r->change_property("user_email", REQUIRED, true);
	$r->change_property("user_email", REGEXP_MASK, EMAIL_REGEXP);
	$r->change_property("user_email", TRIM, true);
	$r->add_textbox("summary", TEXT, CONTACT_SUMMARY_FIELD);
	$r->change_property("summary", REQUIRED, true);
	$r->change_property("summary", TRIM, true);
	$r->add_textbox("description", TEXT, CONTACT_DESCRIPTION_FIELD);
	$r->change_property("description", REQUIRED, true);
	$r->change_property("description", TRIM, true);
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

	$user_name_class = "normal"; 
	$user_email_class = "normal"; 
	$summary_class = "normal"; 
	$description_class = "normal"; 	
	$validation_class = "normal"; 

	$operation = get_param("operation");
	$rnd = get_param("rnd");
	$filter = get_param("filter");
	$remote_address = get_ip();

	$session_rnd = get_session("session_rnd");

	if($operation && $rnd != $session_rnd)
	{
		set_session("session_rnd", $rnd);

		$r->get_form_values();

		$r->validate();

		if ($use_validation) {
			if ($r->is_empty("validation_number")) {
				$validation_class = "error"; 
			} else {
				$validated_number = check_image_validation($r->get_value("validation_number"));
				if (!$validated_number) {
					$validation_class = "error"; 
					$r->errors .= str_replace("{field_name}", VALIDATION_CODE_FIELD, VALIDATION_MESSAGE);
				} elseif ($r->errors) {
					// saved validated number for following submits	
					set_session("session_validation_number", $validated_number);
				}
			} 
		}

		if (strlen($r->errors)) {
			$errors = true;
			set_session("session_rnd", "");
		}

		if(!$errors)
		{
			$user_id = get_session("session_user_id");
			$user_email = trim($r->get_value("user_email"));

			$request_sent = va_date($datetime_show_format, va_time());
			$t->set_var("request_sent", $request_sent);
			$t->set_var("remote_address", get_ip());
			$t->set_var("user_id", $user_id);
			// set merchant parameters
			foreach ($params as $param_name) {
				$param_value = get_setting_value($merchant_info, $param_name, "");
				$delivery_value = get_setting_value($merchant_info, "delivery_".$param_name, "");
				$t->set_var("merchant_".$param_name, $param_value);
				$t->set_var("merchant_delivery_".$param_value, $delivery_value);
			}
			$t->set_var("merchant_name", $merchant_name);
			$t->set_var("merchant_email", $merchant_email);

			if (strlen($user_id)) {
				// get some details for registered user
				$sql  = " SELECT * FROM " . $table_prefix . "users ";
				$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					foreach ($params as $param_name) {
						$param_value = $db->f($param_name);
						$delivery_value = $db->f("delivery_".$param_name);
						$t->set_var("user_".$param_name, $param_value);
						$t->set_var("user_delivery_".$param_value, $delivery_value);
					}
				}
			}
				
			// send email notification to admin
			$admin_notification = get_setting_value($contact_settings, "admin_notification", "");
			if ($admin_notification) {
				$admin_subject = get_setting_value($contact_settings, "admin_subject", $r->get_value("summary"));
				$admin_message = get_setting_value($contact_settings, "admin_message", $r->get_value("description"));

				$t->set_block("admin_subject", $admin_subject);
				$t->set_block("admin_message", $admin_message);

				$mail_to = get_setting_value($contact_settings, "admin_email", $settings["admin_email"]);
				$mail_to = str_replace(";", ",", $mail_to);
				$email_headers = array();
				$email_headers["from"] = get_setting_value($contact_settings, "admin_mail_from", $settings["admin_email"]);
				$email_headers["cc"] = get_setting_value($contact_settings, "cc_emails");
				$email_headers["bcc"] = get_setting_value($contact_settings, "admin_mail_bcc");
				$email_headers["reply_to"] = get_setting_value($contact_settings, "admin_mail_reply_to");
				$email_headers["return_path"] = get_setting_value($contact_settings, "admin_mail_return_path");
				$email_headers["mail_type"] = get_setting_value($contact_settings, "admin_message_type");

				$t->set_var("summary", $r->get_value("summary"));
				$t->set_var("description", $r->get_value("description"));
				$t->set_var("message_text", $r->get_value("description"));
				$t->set_var("user_name", $r->get_value("user_name"));
				$t->set_var("user_email", $r->get_value("user_email"));
				$t->parse("admin_subject", false);
				if ($email_headers["mail_type"]) {
					$t->set_var("summary", htmlspecialchars($r->get_value("summary")));
					$t->set_var("description", nl2br(htmlspecialchars($r->get_value("description"))));
					$t->set_var("message_text", nl2br(htmlspecialchars($r->get_value("description"))));
					$t->set_var("user_name", htmlspecialchars($r->get_value("user_name")));
					$t->set_var("user_email", htmlspecialchars($r->get_value("user_email")));
				}
				$t->parse("admin_message", false);
				// parse email header fields
				foreach($email_headers as $key => $value) {
					parse_value($email_headers[$key]);
				}

				$admin_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("admin_message"));
				va_mail($mail_to, $t->get_var("admin_subject"), $admin_message, $email_headers);
			}

			// send email notification to merchant 
			$user_notification = get_setting_value($contact_settings, "user_notification", "");
			if ($user_notification) {
				$user_subject = get_setting_value($contact_settings, "user_subject", $r->get_value("summary"));
				$user_message = get_setting_value($contact_settings, "user_message", $r->get_value("description"));

				$t->set_block("user_subject", $user_subject);
				$t->set_block("user_message", $user_message);

				$email_headers = array();
				$email_headers["from"] = get_setting_value($contact_settings, "user_mail_from", $settings["admin_email"]);
				$email_headers["cc"] = get_setting_value($contact_settings, "user_mail_cc");
				$email_headers["bcc"] = get_setting_value($contact_settings, "user_mail_bcc");
				$email_headers["reply_to"] = get_setting_value($contact_settings, "user_mail_reply_to");
				$email_headers["return_path"] = get_setting_value($contact_settings, "user_mail_return_path");
				$email_headers["mail_type"] = get_setting_value($contact_settings, "user_message_type");

				$t->set_var("summary", $r->get_value("summary"));
				$t->set_var("description", $r->get_value("description"));
				$t->set_var("message_text", $r->get_value("description"));
				$t->set_var("user_name", $r->get_value("user_name"));
				$t->set_var("user_email", $r->get_value("user_email"));
				$t->parse("user_subject", false);
				if ($email_headers["mail_type"]) {
					$t->set_var("summary", htmlspecialchars($r->get_value("summary")));
					$t->set_var("description", nl2br(htmlspecialchars($r->get_value("description"))));
					$t->set_var("message_text", nl2br(htmlspecialchars($r->get_value("description"))));
					$t->set_var("user_name", htmlspecialchars($r->get_value("user_name")));
					$t->set_var("user_email", htmlspecialchars($r->get_value("user_email")));
				}
				$t->parse("user_message", false);
				// parse email header fields
				foreach($email_headers as $key => $value) {
					parse_value($email_headers[$key]);
				}

				$user_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("user_message"));
				va_mail($merchant_email, $t->get_var("user_subject"), $user_message, $email_headers);
			}

			$r->set_value("summary", "");
			$r->set_value("description", "");
			$r->set_value("validation_number", "");
		}
	} else if(strlen(get_session("session_user_id"))) {
		$r->set_value("user_name", get_session("session_user_name"));
		$r->set_value("user_email", get_session("session_user_email"));
	}

	$r->set_parameters();

	if($errors) {
		$t->parse("contact_errors", false);
	}

	if(!$errors && $operation) {
		$t->parse("contact_request_sent", false);
	}

	$block_parsed = true;

?>