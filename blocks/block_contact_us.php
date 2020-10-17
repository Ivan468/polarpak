<?php                           

	include_once("./includes/record.php");

	$default_title = "{CONTACT_US_MSG}";

	$html_template = get_setting_value($block, "html_template", "block_contact_us.html"); 
  $t->set_file("block_body", $html_template);
	
	// get contact us settings
	$contact_settings = get_settings("contact_us");

	$user_id = get_session("session_user_id");
	$allowed_send = get_setting_value($contact_settings, "allowed_send", 0);
	$message_random_image = get_setting_value($contact_settings, "message_random_image", 1);

	if (($message_random_image == 2) || ($message_random_image == 1 && !strlen(get_session("session_user_id")))) { 
		$use_validation = true;
	} else {
		$use_validation = false;
	}

	$rr = new VA_Record("");
	// global data
	$rr->operations[INSERT_ALLOWED] = false;
	$rr->operations[UPDATE_ALLOWED] = false;
	$rr->operations[DELETE_ALLOWED] = false;
	$rr->operations[SELECT_ALLOWED] = false;
	$rr->redirect = false;
	$rr->success_messages[INSERT_SUCCESS] = MESSAGE_SENT_MSG;

	// predefined fields
	$rr->add_textbox("user_name", TEXT, NAME_MSG);
	$rr->change_property("user_name", REQUIRED, true);
	$rr->change_property("user_name", REGEXP_MASK, NAME_REGEXP);
	$rr->change_property("user_name", REGEXP_ERROR, ALPHANUMERIC_ALLOWED_ERROR);
	$rr->set_control_event("user_name", AFTER_VALIDATE, "check_content");
	$rr->add_textbox("user_email", TEXT, EMAIL_FIELD);
	$rr->change_property("user_email", REQUIRED, true);
	$rr->change_property("user_email", REGEXP_MASK, EMAIL_REGEXP);
	$rr->set_control_event("user_email", AFTER_VALIDATE, "check_content");
	$rr->add_textbox("summary", TEXT, ONE_LINE_SUMMARY_MSG);
	$rr->set_control_event("summary", AFTER_VALIDATE, "check_content");
	$rr->add_textbox("comments", TEXT, YOUR_MESSAGE_MSG);
	$rr->set_control_event("comments", AFTER_VALIDATE, "check_content");

	// check parameters properties
	$default_params = array(
		1 => "user_name", 2 => "user_email", 
		3 => "summary", 4 => "comments");

	foreach ($default_params as $param_order => $param_name) {
		$param_order = get_setting_value($contact_settings, $param_name . "_order", $param_order);
		$show_param = get_setting_value($contact_settings, "show_".$param_name, $param_order);
		$param_required = get_setting_value($contact_settings, $param_name . "_required", $param_order);
		$rr->change_property($param_name, SHOW, $show_param);
		$rr->change_property($param_name, CONTROL_ORDER, $param_order);
		$rr->change_property($param_name, REQUIRED, $param_required);
		$rr->change_property($param_name, TRIM, true);
	}
	if ($user_id) {	
		$user_info = get_session("session_user_info");
		$user_nickname = get_setting_value($user_info, "nickname", "");
		$user_email = get_setting_value($user_info, "email", "");
		if (strlen($user_nickname)) {
			$rr->change_property("user_name", SHOW, false);
		}
		if (strlen($user_email)) {
			$rr->change_property("user_email", SHOW, false);
		}
	}

	$rr->add_textbox("validation_number", TEXT, VALIDATION_CODE_FIELD);
	$rr->change_property("validation_number", USE_IN_INSERT, false);
	$rr->change_property("validation_number", USE_IN_UPDATE, false);
	$rr->change_property("validation_number", USE_IN_SELECT, false);
	if ($use_validation) {
		$rr->change_property("validation_number", REQUIRED, true);
		$rr->change_property("validation_number", SHOW, true);
		$rr->change_property("validation_number", AFTER_VALIDATE, "check_validation_number");
	} else {
		$rr->change_property("validation_number", SHOW, false);
	}

	// set events
	$rr->set_event(ON_CUSTOM_OPERATION, "check_send_message");
	$rr->set_event(ON_DOUBLE_SAVE, "message_double_send");
	$rr->set_event(BEFORE_SHOW, "message_form_check");

	$t->set_var("rnd",           va_timestamp());
	$t->set_var("current_href",  $current_page);
	// set hidden parameters 
	transfer_params("", true);

	$remote_address = get_ip();

	$rr->process();

	$block_parsed = true;


function check_send_message()
{
	global $rr, $t, $settings, $contact_settings, $datetime_show_format;

	// run this function only if send operation run
	if ($rr->operation != "send") {
		return;
	}

	$allowed_send = get_setting_value($contact_settings, "allowed_send", 0);

	if (!$allowed_send) {
		$rr->errors = NOT_ALLOWED_SEND_MESSAGES_MSG;
	} else if ($allowed_send == 2 && !get_session("session_user_id")) {
		$rr->errors = REGISTERED_USERS_ALLOWED_MESSAGES_MSG;
	} else if (blacklist_check("support") == "blocked") {
		$rr->errors = BLACK_IP_MSG."<br>";	
	}

	if (!$rr->errors) {
		$rr->validate();
	}
	// check if user can send message
	if (!$rr->errors) {
		if (!check_message_interval($time_left)) {
			$time_left = str_replace("{quantity}", $time_left, SECONDS_QTY_MSG);
			$interval_error = str_replace("{interval_time}", $time_left, MESSAGE_INTERVAL_ERROR);
			$rr->errors = $interval_error."<br>";
		}
	}

	if (!$rr->errors) {
		// there are no errors then operation is ok
		$rnd = get_param("rnd");
		set_session("session_rnd", $rnd);
		// record was added clear validation variable
		set_session("session_validation_number", "");
		// set time when message was sent
		set_session("session_message_sent", va_timestamp());

		// get some values to send notifications
		$eol = get_eol();
		$ip = get_ip();
		$user_id = get_session("session_user_id");
		if ($user_id) {	
			// check user name and email
			$user_info = get_session("session_user_info");
			$user_nickname = get_setting_value($user_info, "nickname", "");
			$user_email = get_setting_value($user_info, "email", "");
			if (strlen($user_nickname)) {
				$rr->set_value("user_name", $user_nickname);
			}
			if (strlen($user_email)) {
				$rr->set_value("user_email", $user_email);
			}
		}

		$user_info = get_session("session_user_info");
		$admin_notification = get_setting_value($contact_settings, "admin_notification", 0);
		$user_email = $rr->get_value("user_email");
		$user_notification = get_setting_value($contact_settings, "user_notification", 0);
		if ($admin_notification || ($user_notification && $user_email)) {
			// set variables for email notifications
			$t->set_vars($user_info);
  
			$date_added_formatted = va_date($datetime_show_format, va_time());
			$t->set_var("date_added", $date_added_formatted);
  
			$t->set_var("ip", $ip);
			$t->set_var("remote_address", $ip);
			$t->set_var("name", $rr->get_value("user_name"));
			$t->set_var("email", $rr->get_value("user_email"));
			$t->set_var("user_name", $rr->get_value("user_name"));
			$t->set_var("user_email", $rr->get_value("user_email"));
			$t->set_var("summary", $rr->get_value("summary"));
			$t->set_var("comments", $rr->get_value("comments"));
		}
  
		// send email notification to admin
		if ($admin_notification)
		{
			$t->set_block("admin_subject", $contact_settings["admin_subject"]);
			$t->set_block("admin_message", $contact_settings["admin_message"]);
  
			$mail_to = get_setting_value($contact_settings, "admin_email", $settings["admin_email"]);
			$mail_to = str_replace(";", ",", $mail_to);
			$mail_from = get_setting_value($contact_settings, "admin_mail_from", $settings["admin_email"]);
			$email_headers = array();
			$email_headers["from"] = parse_value($mail_from);
			$email_headers["cc"] = get_setting_value($contact_settings, "admin_mail_cc");
			$email_headers["bcc"] = get_setting_value($contact_settings, "admin_mail_bcc");
			$email_headers["reply_to"] = get_setting_value($contact_settings, "admin_mail_reply_to");
			$email_headers["return_path"] = get_setting_value($contact_settings, "admin_mail_return_path");
			$email_headers["mail_type"] = get_setting_value($contact_settings, "admin_message_type");
  
			$t->parse("admin_subject", false);
			$t->parse("admin_message", false);
			$admin_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("admin_message"));
			va_mail($mail_to, $t->get_var("admin_subject"), $admin_message, $email_headers);
		}
  
		// send email notification to user
		if ($user_notification && $user_email)
		{
			$t->set_block("user_subject", $contact_settings["user_subject"]);
			$t->set_block("user_message", $contact_settings["user_message"]);
  
			$mail_from = get_setting_value($contact_settings, "user_mail_from", $settings["admin_email"]); 
			$email_headers = array();
			$email_headers["from"] = parse_value($mail_from);
			$email_headers["cc"] = get_setting_value($contact_settings, "user_mail_cc");
			$email_headers["bcc"] = get_setting_value($contact_settings, "user_mail_bcc");
			$email_headers["reply_to"] = get_setting_value($contact_settings, "user_mail_reply_to");
			$email_headers["return_path"] = get_setting_value($contact_settings, "user_mail_return_path");
			$email_headers["mail_type"] = get_setting_value($contact_settings, "user_message_type");
  
			$t->parse("user_subject", false);
			$t->parse("user_message", false);
  
			$user_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("user_message"));
			va_mail($user_email, $t->get_var("user_subject"), $user_message, $email_headers);
		}
  
		// clear values and set default
		$rr->success_message = MESSAGE_SENT_MSG;
		$rr->empty_values();
		$rr->set_default_values();
	} else {
		set_session("session_rnd", "");
	}

}

function check_content($parameter)
{
	global $rr;
	$control_name = $parameter[CONTROL_NAME];
	if ($parameter[IS_VALID] && check_banned_content($parameter[CONTROL_VALUE])) {
		$rr->parameters[$control_name][IS_VALID] = false;
		$rr->parameters[$control_name][ERROR_DESC] = "<b>".$parameter[CONTROL_DESC]."</b>: ".BANNED_CONTENT_MSG;
	}
}

function message_form_check()
{
	global $rr, $contact_settings;
	$allowed_send = get_setting_value($contact_settings, "allowed_send", 0);

	if (!$allowed_send) {
		$rr->record_show = false;	
		$rr->errors = NOT_ALLOWED_SEND_MESSAGES_MSG;
	} else if ($allowed_send == 2 && !get_session("session_user_id")) {
		$rr->record_show = false;	
		$rr->errors = REGISTERED_USERS_ALLOWED_MESSAGES_MSG;
	} else if (blacklist_check("support") == "blocked") {
		$rr->record_show = false;	
		$rr->errors = BLACK_IP_MSG;	
	}
}

function check_message_interval(&$time_left)
{
	global $contact_settings;
	$user_id = get_session("session_user_id");
	$message_sent = get_session("session_message_sent");
	$periods = array(0, 1, 60, 3600);
	if (!$user_id && !$message_sent) {
		// unregistered users should always wait for 1 minute before they can send message to prevent spam
		$messages_interval = 1;
		$messages_period = 2;
		$message_sent = get_session("session_start_ts");
	} else if ($message_sent) {
		$messages_interval = get_setting_value($contact_settings, "messages_interval", 1);
		$messages_period = get_setting_value($contact_settings, "messages_period", 2);
	} else {
		$messages_interval = 0; $messages_period = 0;
	}
	$time_interval = $messages_interval * $periods[$messages_period];
	$current_time = va_timestamp();
	// check if user can send a new message
	if (($message_sent + $time_interval) > $current_time) {
		$time_left = $message_sent + $time_interval - $current_time;
		return false;
	} else {
		return true;
	}
}

function message_double_send()
{
	global $rr;
	$rr->operation = "double";
	$rr->success_message = MESSAGE_SENT_MSG;
	$rr->empty_values();
	$rr->set_default_values();
}

function check_validation_number()
{
	global $db, $rr;
	if($rr->get_property_value("validation_number", IS_VALID)) {
		$validated_number = check_image_validation($rr->get_value("validation_number"));
		if (!$validated_number) {
			$error_message = str_replace("{field_name}", VALIDATION_CODE_FIELD, VALIDATION_MESSAGE);
			$rr->change_property("validation_number", IS_VALID, false);
			$rr->change_property("validation_number", ERROR_DESC, $error_message);
		} else {
			// saved validated number for following submits	and delete this value in case of success
			set_session("session_validation_number", $validated_number);
		}
	}
}

?>