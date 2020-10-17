<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  settings_two_factor.php                                  ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once($root_folder_path . "messages/" . $language_code . "/reviews_messages.php");
	include_once("./admin_common.php");

	check_admin_security("admins_groups");

	$validation_types = 
		array( 
			array(2, FOR_ALL_USERS_MSG), array(1, UNREGISTERED_USER_ONLY_MSG), array(0, NOT_USED_MSG)
		);

	$message_types =
		array(
			array(1, HTML_MSG), array(0, PLAIN_TEXT_MSG)
		);

	$time_periods =
		array(
			array("", ""), array(1, SECOND_MSG), array(2, MINUTE_MSG), array(3, HOUR_MSG),
		);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "settings_two_factor.html");

	include_once("./admin_header.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("settings_two_factor_href", "settings_two_factor.php");
	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");
	$t->set_var("admin_email_help_href", "admin_email_help.php");

	$r = new VA_Record($table_prefix . "global_settings");
	// global reviews settings
	$r->add_checkbox("admin_two_factor", INTEGER);

	// notification fields
	$r->add_checkbox("admin_notification", INTEGER, SEND_NOTIFICATION_ADMIN_MSG);
	$r->add_textbox("admin_mail_from", TEXT);
	$r->add_textbox("admin_mail_cc", TEXT);
	$r->add_textbox("admin_mail_bcc", TEXT);
	$r->add_textbox("admin_mail_reply_to", TEXT);
	$r->add_textbox("admin_mail_return_path", TEXT);
	$r->add_textbox("admin_subject", TEXT);
	$r->add_radio("admin_message_type", TEXT, $message_types);
	$r->add_textbox("admin_message", TEXT);

	// sms notification settings
	$r->add_checkbox("admin_sms_notification", INTEGER, SMS_NOTIFICATION_ADMIN_MSG);
	$r->add_textbox("admin_sms_recipient", TEXT, ADMIN_SMS_RECIPIENT_MSG);
	$r->add_textbox("admin_sms_originator", TEXT, ADMIN_SMS_ORIGINATOR_MSG);
	$r->add_textbox("admin_sms_message", TEXT, ADMIN_SMS_MESSAGE_MSG);

	$r->get_form_values();

	$param_site_id = get_session("session_site_id");
	$tab = get_param("tab");
	if (!$tab) { $tab = "admin"; }
	$operation = get_param("operation");
	$return_page = get_param("rp");
	if (!strlen($return_page)) $return_page = "admin.php";
	if (strlen($operation))
	{
		$tab = "admin";
		if ($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		}

		$is_valid = $r->validate();

		if ($is_valid && $r->get_value("admin_two_factor")) {
			// check if one notification selected
			if (!$r->get_value("admin_notification") && !$r->get_value("admin_sms_notification")) {
				$error_message  = str_replace("{field_name}", SEND_NOTIFICATION_ADMIN_MSG, REQUIRED_MESSAGE)."<br/>";
				$error_message .= OR_MSG."<br/>";
				$error_message .= str_replace("{field_name}", SMS_NOTIFICATION_ADMIN_MSG, REQUIRED_MESSAGE)."<br/>";
				$r->errors = $error_message;
			}
		}

		if (!strlen($r->errors))
		{
			$sql  = " DELETE FROM " . $table_prefix . "global_settings WHERE setting_type='two_factor'";
			$sql .= " AND site_id=" . $db->tosql($param_site_id,INTEGER);
			$db->query($sql);
			foreach ($r->parameters as $key => $value)
			{
				$sql  = "INSERT INTO " . $table_prefix . "global_settings (setting_type, setting_name, setting_value, site_id) VALUES (";
				$sql .= "'two_factor', '" . $key . "'," . $db->tosql($value[CONTROL_VALUE], TEXT) . ",";
				$sql .= $db->tosql($param_site_id,INTEGER) . ") ";
				$db->query($sql);
			}
			set_session("session_settings", "");

			header("Location: " . $return_page);
			exit;
		}
	}
	else // get two_factor settings
	{
		foreach ($r->parameters as $key => $value)
		{
			$sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type='two_factor' AND setting_name='" . $key . "'";
			$sql .= " AND ( site_id=1 OR site_id=" . $db->tosql($param_site_id,INTEGER). ") ";
			$sql .= " ORDER BY site_id DESC ";
			$r->set_value($key, get_db_value($sql));
		}
	}

	$r->set_parameters();
	$t->set_var("rp", htmlspecialchars($return_page));

	// set styles for tabs
	$tabs = array(
		"admin" => array("title" => ADMIN_MSG), 
	);
	parse_admin_tabs($tabs, $tab, 6);

	// multisites
	if ($sitelist) {
		$sites   = get_db_values("SELECT site_id,site_name FROM " . $table_prefix . "sites ORDER BY site_id ", "");
		set_options($sites, $param_site_id, "param_site_id");
		$t->parse("sitelist", false);
	}	

	include_once("./admin_footer.php");
	
	$t->pparse("main");

?>