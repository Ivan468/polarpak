<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_contact_us.php                                     ***
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

	check_admin_security("site_settings");

	$allowed_options = 
		array( 
			array(0, NOBODY_MSG), array(1, FOR_ALL_USERS_MSG), array(2, REGISTERED_CUSTOMERS_MSG), 
		);

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
	$t->set_file("main", "admin_contact_us.html");

	include_once("./admin_header.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_contact_us_href", "admin_contact_us.php");
	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");
	$t->set_var("admin_email_help_href", "admin_email_help.php");

	$r = new VA_Record($table_prefix . "global_settings");
	// global reviews settings
	$r->add_radio("allowed_send", INTEGER, $allowed_options);
	$r->add_radio("message_random_image", TEXT, $validation_types);		
	// reviews restrictions
	$r->add_textbox("messages_interval", INTEGER);		
	$r->add_select("messages_period", INTEGER, $time_periods);		

	// predefined fields
	$r->add_checkbox("show_user_name", INTEGER);
	$r->add_checkbox("user_name_required", INTEGER);
	$r->add_textbox("user_name_order", INTEGER, NAME_ALIAS_MSG);
	$r->add_checkbox("show_user_email", INTEGER);
	$r->add_checkbox("user_email_required", INTEGER);
	$r->add_textbox("user_email_order", INTEGER, EMAIL_MSG);
	$r->add_checkbox("show_summary", INTEGER);
	$r->add_checkbox("summary_required", INTEGER);
	$r->add_textbox("summary_order", INTEGER, ONE_LINE_SUMMARY_MSG);
	$r->add_checkbox("show_comments", INTEGER);
	$r->add_checkbox("comments_required", INTEGER);
	$r->add_textbox("comments_order", INTEGER, YOUR_QUESTION_MSG);

	// notification fields
	$r->add_checkbox("admin_notification", INTEGER);
	$r->add_textbox("admin_email", TEXT);
	$r->add_textbox("admin_mail_from", TEXT);
	$r->add_textbox("admin_mail_cc", TEXT);
	$r->add_textbox("admin_mail_bcc", TEXT);
	$r->add_textbox("admin_mail_reply_to", TEXT);
	$r->add_textbox("admin_mail_return_path", TEXT);
	$r->add_textbox("admin_subject", TEXT);
	$r->add_radio("admin_message_type", TEXT, $message_types);
	$r->add_textbox("admin_message", TEXT);

	$r->add_checkbox("user_notification", INTEGER);
	$r->add_textbox("user_mail_from", TEXT);
	$r->add_textbox("user_mail_cc", TEXT);
	$r->add_textbox("user_mail_bcc", TEXT);
	$r->add_textbox("user_mail_reply_to", TEXT);
	$r->add_textbox("user_mail_return_path", TEXT);
	$r->add_textbox("user_subject", TEXT);
	$r->add_radio("user_message_type", TEXT, $message_types);
	$r->add_textbox("user_message", TEXT);
	
	$r->get_form_values();

	$param_site_id = get_session("session_site_id");
	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }
	$operation = get_param("operation");
	$return_page = get_param("rp");
	if (!strlen($return_page)) $return_page = "admin.php";
	if (strlen($operation))
	{
		$tab = "general";
		if ($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		}

		$is_valid = $r->validate();

		if (!strlen($r->errors))
		{
			$sql  = " DELETE FROM " . $table_prefix . "global_settings WHERE setting_type='contact_us'";
			$sql .= " AND site_id=" . $db->tosql($param_site_id,INTEGER);
			$db->query($sql);
			foreach ($r->parameters as $key => $value)
			{
				$sql  = "INSERT INTO " . $table_prefix . "global_settings (setting_type, setting_name, setting_value, site_id) VALUES (";
				$sql .= "'contact_us', '" . $key . "'," . $db->tosql($value[CONTROL_VALUE], TEXT) . ",";
				$sql .= $db->tosql($param_site_id,INTEGER) . ") ";
				$db->query($sql);
			}
			set_session("session_settings", "");

			header("Location: " . $return_page);
			exit;
		}
	}
	else // get contact_us settings
	{
		foreach ($r->parameters as $key => $value)
		{
			$sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type='contact_us' AND setting_name='" . $key . "'";
			$sql .= " AND ( site_id=1 OR site_id=" . $db->tosql($param_site_id,INTEGER). ") ";
			$sql .= " ORDER BY site_id DESC ";
			$r->set_value($key, get_db_value($sql));
		}
	}

	$r->set_parameters();
	$t->set_var("rp", htmlspecialchars($return_page));

	// set styles for tabs
	$tabs = array(
		"general" => array("title" => ADMIN_GENERAL_MSG), 
		"predefined_fields" => array("title" => PREDEFINED_FIELDS_MSG), 
		"notification_email" => array("title" => NOTIFICATION_EMAIL_MSG), 
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