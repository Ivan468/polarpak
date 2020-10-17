<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_support_settings.php                               ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path."includes/record.php");
	include_once($root_folder_path."includes/tabs_functions.php");
	include_once($root_folder_path."includes/support_functions.php");
	include_once($root_folder_path."messages/".$language_code."/support_messages.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("support_settings");

	$setting_type = "support";

	$support_fields = get_support_fields();

	$va_trail = array(
		"admin_menu.php?code=settings" => va_constant("SETTINGS_MSG"),
		"admin_support_settings.php" => va_constant("SUPPORT_SETTINGS_MSG"),
	);

	$show_values = 
		array( 
			array("", ""), 
			array(1, va_message("YES_MSG")),
			array(0, va_message("NO_MSG"))
		);

	$message_types = 
		array( 
			array(1, HTML_MSG), array(0, PLAIN_TEXT_MSG)
		);

	$submit_tickets =
		array(
			array(0, FOR_ALL_USERS_MSG),
			array(1, ONLY_LOGGED_IN_USERS_MSG),
			);

	$validation_types = 
		array( 
			array(2, FOR_ALL_USERS_MSG), array(1, UNREGISTERED_USER_ONLY_MSG), array(0, NOT_USED_MSG)
		);

	$attachments_allowed_values = 
		array(
			array(0, POINTS_NOT_ALLOWED_MSG),
			array(1, ONLY_FOR_LOGGED_IN_USERS_MSG),
			);

	$validation_types = 
		array( 
			array(2, FOR_ALL_USERS_MSG), array(1, UNREGISTERED_USER_ONLY_MSG), array(0, NOT_USED_MSG)
		);

	$stat_groups = 
		array( 
			array("", ""), array("types", SUPPORT_TYPES_MSG), array("statuses", SUPPORT_STATUSES_MSG), 
		);

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_support_settings.html");

	$admin_header_template = "admin_header_wide.html";
	include_once("./admin_header.php");

	$t->set_var("admin_support_href", "admin_support.php");
	$t->set_var("admin_support_property_href", "admin_support_property.php");
	$t->set_var("admin_support_settings_href", "admin_support_settings.php");
	$t->set_var("admin_support_help_href", "admin_support_help.php");
	$t->set_var("admin_email_help_href", "admin_email_help.php");

	$html_editor = get_setting_value($settings, "html_editor_email", get_setting_value($settings, "html_editor", 1));
	$t->set_var("html_editor", $html_editor);
	$editors_list = 'new_am,new_um,user_reply_am,user_reply_um,manager_reply_am,manager_reply_mm,manager_reply_um,assign_am,assign_mm,assign_tm,assign_um';
	add_html_editors($editors_list, $html_editor);

	$r = new VA_Record($table_prefix . "global_settings");
	$r->add_select("submit_tickets", TEXT, $submit_tickets);
	$r->add_radio("use_random_image", TEXT, $validation_types);
	// attachments settings
	$r->add_textbox("attachments_dir", TEXT);
	$r->add_radio("attachments_users_allowed", TEXT, $attachments_allowed_values);
	$r->add_textbox("attachments_users_mask", TEXT);
	$r->add_textbox("intro_text", TEXT);
	// stat settings
	$r->add_select("stat_first_group", TEXT, $stat_groups);
	$r->add_select("stat_second_group", TEXT, $stat_groups);

	// predefined fields
	$r->add_select("dep_field_show", INTEGER, $show_values);
	$r->add_textbox("dep_field_name", TEXT);
	$r->add_textbox("dep_field_order", INTEGER);
	$r->add_select("type_field_show", INTEGER, $show_values);
	$r->add_textbox("type_field_name", TEXT);
	$r->add_select("type_field_required", INTEGER, $show_values);
	$r->add_textbox("type_field_order", INTEGER);
	$r->add_select("user_name_field_show", INTEGER, $show_values);
	$r->add_textbox("user_name_field_name", TEXT);
	$r->add_select("user_name_field_required", INTEGER, $show_values);
	$r->add_textbox("user_name_field_order", INTEGER);
	$r->add_select("user_email_field_show", INTEGER, $show_values);
	$r->add_textbox("user_email_field_name", TEXT);
	$r->add_select("user_email_field_required", INTEGER, $show_values);
	$r->add_textbox("user_email_field_order", INTEGER);
	$r->add_select("identifier_field_show", INTEGER, $show_values);
	$r->add_textbox("identifier_field_name", TEXT);
	$r->add_select("identifier_field_required", INTEGER, $show_values);
	$r->add_textbox("identifier_field_order", INTEGER);
	$r->add_select("environment_field_show", INTEGER, $show_values);
	$r->add_textbox("environment_field_name", TEXT);
	$r->add_select("environment_field_required", INTEGER, $show_values);
	$r->add_textbox("environment_field_order", INTEGER);
	$r->add_select("product_field_show", INTEGER, $show_values);
	$r->add_textbox("product_field_name", TEXT);
	$r->add_select("product_field_required", INTEGER, $show_values);
	$r->add_textbox("product_field_order", INTEGER);
	$r->add_select("summary_field_show", INTEGER, $show_values);
	$r->add_textbox("summary_field_name", TEXT);
	$r->add_select("summary_field_required", INTEGER, $show_values);
	$r->add_textbox("summary_field_order", INTEGER);
	$r->add_select("description_field_show", INTEGER, $show_values);
	$r->add_textbox("description_field_name", TEXT);
	$r->add_select("description_field_required", INTEGER, $show_values);
	$r->add_textbox("description_field_order", INTEGER);
	$r->add_select("attachments_field_show", INTEGER, $show_values);
	$r->add_textbox("attachments_field_name", TEXT);
	$r->add_select("attachments_field_required", INTEGER, $show_values);
	$r->add_textbox("attachments_field_order", INTEGER);
	$r->add_textbox("success_message", TEXT);

	// new ticket notification fields
	$r->add_checkbox("new_admin_notification", INTEGER);
	$r->add_textbox("new_admin_to", TEXT);
	$r->add_textbox("new_admin_from", TEXT);
	$r->add_textbox("new_admin_cc", TEXT);
	$r->add_textbox("new_admin_bcc", TEXT);
	$r->add_textbox("new_admin_reply_to", TEXT);
	$r->add_textbox("new_admin_return_path", TEXT);
	$r->add_textbox("new_admin_subject", TEXT);
	$r->add_radio("new_admin_message_type", TEXT, $message_types);
	$r->add_textbox("new_admin_message", TEXT);

	$r->add_checkbox("new_user_notification", INTEGER);
	$r->add_textbox("new_user_from", TEXT);
	$r->add_textbox("new_user_cc", TEXT);
	$r->add_textbox("new_user_bcc", TEXT);
	$r->add_textbox("new_user_reply_to", TEXT);
	$r->add_textbox("new_user_return_path", TEXT);
	$r->add_textbox("new_user_subject", TEXT);
	$r->add_radio("new_user_message_type", TEXT, $message_types);
	$r->add_textbox("new_user_message", TEXT);
	// end new ticket notification fields

	// user ticket reply notification fields
	$r->add_checkbox("user_reply_admin_notification", INTEGER);
	$r->add_textbox("user_reply_admin_to", TEXT);
	$r->add_textbox("user_reply_admin_from", TEXT);
	$r->add_textbox("user_reply_admin_cc", TEXT);
	$r->add_textbox("user_reply_admin_bcc", TEXT);
	$r->add_textbox("user_reply_admin_reply_to", TEXT);
	$r->add_textbox("user_reply_admin_return_path", TEXT);
	$r->add_textbox("user_reply_admin_subject", TEXT);
	$r->add_radio("user_reply_admin_message_type", TEXT, $message_types);
	$r->add_textbox("user_reply_admin_message", TEXT);

	$r->add_checkbox("user_reply_user_notification", INTEGER);
	$r->add_textbox("user_reply_user_from", TEXT);
	$r->add_textbox("user_reply_user_cc", TEXT);
	$r->add_textbox("user_reply_user_bcc", TEXT);
	$r->add_textbox("user_reply_user_reply_to", TEXT);
	$r->add_textbox("user_reply_user_return_path", TEXT);
	$r->add_textbox("user_reply_user_subject", TEXT);
	$r->add_radio("user_reply_user_message_type", TEXT, $message_types);
	$r->add_textbox("user_reply_user_message", TEXT);
	// end user ticket reply notification fields

	// manager ticket reply notification fields
	$r->add_checkbox("manager_reply_admin_notification", INTEGER);
	$r->add_textbox("manager_reply_admin_to", TEXT);
	$r->add_textbox("manager_reply_admin_from", TEXT);
	$r->add_textbox("manager_reply_admin_cc", TEXT);
	$r->add_textbox("manager_reply_admin_bcc", TEXT);
	$r->add_textbox("manager_reply_admin_reply_to", TEXT);
	$r->add_textbox("manager_reply_admin_return_path", TEXT);
	$r->add_textbox("manager_reply_admin_subject", TEXT);
	$r->add_radio("manager_reply_admin_message_type", TEXT, $message_types);
	$r->add_textbox("manager_reply_admin_message", TEXT);

	$r->add_checkbox("manager_reply_manager_notification", INTEGER);
	$r->add_textbox("manager_reply_manager_from", TEXT);
	$r->add_textbox("manager_reply_manager_cc", TEXT);
	$r->add_textbox("manager_reply_manager_bcc", TEXT);
	$r->add_textbox("manager_reply_manager_reply_to", TEXT);
	$r->add_textbox("manager_reply_manager_return_path", TEXT);
	$r->add_textbox("manager_reply_manager_subject", TEXT);
	$r->add_radio("manager_reply_manager_message_type", TEXT, $message_types);
	$r->add_textbox("manager_reply_manager_message", TEXT);

	$r->add_checkbox("manager_reply_user_notification", INTEGER);
	$r->add_textbox("manager_reply_user_from", TEXT);
	$r->add_textbox("manager_reply_user_cc", TEXT);
	$r->add_textbox("manager_reply_user_bcc", TEXT);
	$r->add_textbox("manager_reply_user_reply_to", TEXT);
	$r->add_textbox("manager_reply_user_return_path", TEXT);
	$r->add_textbox("manager_reply_user_subject", TEXT);
	$r->add_radio("manager_reply_user_message_type", TEXT, $message_types);
	$r->add_textbox("manager_reply_user_message", TEXT);
	// end manager ticket reply notification fields

	// manager assignment notification fields
	$r->add_checkbox("assign_admin_notification", INTEGER);
	$r->add_textbox("assign_admin_to", TEXT);
	$r->add_textbox("assign_admin_from", TEXT);
	$r->add_textbox("assign_admin_cc", TEXT);
	$r->add_textbox("assign_admin_bcc", TEXT);
	$r->add_textbox("assign_admin_reply_to", TEXT);
	$r->add_textbox("assign_admin_return_path", TEXT);
	$r->add_textbox("assign_admin_subject", TEXT);
	$r->add_radio("assign_admin_message_type", TEXT, $message_types);
	$r->add_textbox("assign_admin_message", TEXT);

	$r->add_checkbox("assign_manager_notification", INTEGER);
	$r->add_textbox("assign_manager_from", TEXT);
	$r->add_textbox("assign_manager_cc", TEXT);
	$r->add_textbox("assign_manager_bcc", TEXT);
	$r->add_textbox("assign_manager_reply_to", TEXT);
	$r->add_textbox("assign_manager_return_path", TEXT);
	$r->add_textbox("assign_manager_subject", TEXT);
	$r->add_radio("assign_manager_message_type", TEXT, $message_types);
	$r->add_textbox("assign_manager_message", TEXT);

	$r->add_checkbox("assign_to_notification", INTEGER);
	$r->add_textbox("assign_to_from", TEXT);
	$r->add_textbox("assign_to_cc", TEXT);
	$r->add_textbox("assign_to_bcc", TEXT);
	$r->add_textbox("assign_to_reply_to", TEXT);
	$r->add_textbox("assign_to_return_path", TEXT);
	$r->add_textbox("assign_to_subject", TEXT);
	$r->add_radio("assign_to_message_type", TEXT, $message_types);
	$r->add_textbox("assign_to_message", TEXT);

	$r->add_checkbox("assign_user_notification", INTEGER);
	$r->add_textbox("assign_user_from", TEXT);
	$r->add_textbox("assign_user_cc", TEXT);
	$r->add_textbox("assign_user_bcc", TEXT);
	$r->add_textbox("assign_user_reply_to", TEXT);
	$r->add_textbox("assign_user_return_path", TEXT);
	$r->add_textbox("assign_user_subject", TEXT);
	$r->add_radio("assign_user_message_type", TEXT, $message_types);
	$r->add_textbox("assign_user_message", TEXT);
	// end manager assignment notification fields

	// forward settings 
	$r->add_textbox("forward_mail_to", TEXT);
	$r->add_textbox("forward_mail_from", TEXT);
	$r->add_textbox("forward_mail_cc", TEXT);
	$r->add_textbox("forward_mail_bcc", TEXT);
	$r->add_textbox("forward_mail_reply_to", TEXT);
	$r->add_textbox("forward_mail_return_path", TEXT);
	$r->add_textbox("forward_mail_subject", TEXT);
	$r->add_radio("forward_mail_message_type", TEXT, $message_types);
	$r->add_textbox("forward_mail_message", TEXT);
	// end forward settings 

	$r->get_form_values();
	
	$param_site_id = get_session("session_site_id");
	
	$operation = get_param("operation");
	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }	
	$return_page = get_param("rp");
	if (!strlen($return_page)) {
		$return_page = "admin_support.php";
	}

	if(strlen($operation))
	{
		if($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		}

		if (!function_exists('imagecreate') && (($r->get_value("use_random_image") == 2) || ($r->get_value("use_random_image") == 1 ))) {	
		  $r->errors .= va_message("RANDOM_IMAGE_VALIDATION_ERROR_MSG");
			$r->set_value("use_random_image",0);
		} 

		if (!strlen($r->errors)) {
			$new_settings = array();
			foreach ($r->parameters as $key => $value) {
				$new_settings[$key] = $value[CONTROL_VALUE];
			}
			update_settings($setting_type, $param_site_id, $new_settings);
			set_session("session_settings", "");

			// show success message
			$t->parse("success_block", false);			
		}

	} else {
		// get support_settings settings
		foreach ($r->parameters as $key => $value) {
			$sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT); 
			$sql .= " AND setting_name='" . $key . "'";
			$sql .= " AND ( site_id=1 OR site_id=" . $db->tosql($param_site_id,INTEGER). ") ";
			$sql .= " ORDER BY site_id DESC ";
			$r->set_value($key, get_db_value($sql));
		}
		// check if some predefined fields parameters missed and set them
		foreach ($support_fields as $param_name => $field_data)  {
			$setting_name = $field_data["setting_name"];
			$default_show = $field_data["show"];
			$default_required = $field_data["required"];
			$param_show = $setting_name."_field_show";
			$param_required = $setting_name."_field_required";
			if ($r->parameter_exists($param_show) && $r->is_empty($param_show)) {
				$r->set_value($param_show, $default_show);
			}
			if ($r->parameter_exists($param_required) && $r->is_empty($param_required)) {
				$r->set_value($param_required, $default_required);
			}
		}
	}

	$r->set_parameters();
	$t->set_var("rp", htmlspecialchars($return_page));

	// multi-site settings
	multi_site_settings();

	// set styles for tabs
	$tabs = array(
		"general" => array("title" => va_constant("ADMIN_GENERAL_MSG")), 
		"predefined_fields" => array("title" => va_constant("PREDEFINED_FIELDS_MSG")), 
		"new_notification" => array("title" => va_constant("NEW_TICKET_NOTIFICATION_MSG")), 
		"user_reply_notification" => array("title" => va_constant("USER_TICKET_REPLY_NOTIFICATION_MSG")), 
		"manager_reply_notification" => array("title" => va_constant("MANAGER_TICKET_REPLY_NOTIFICATION_MSG")), 
		"assign_notification" => array("title" => va_constant("MANAGER_TICKET_ASSIGNMENT_NOTIFICATION_MSG")), 
		"forward" => array("title" => va_constant("TICKET_FORWARD_SETTINGS_MSG")), 
	);
	parse_tabs($tabs);

	include_once("./admin_footer.php");

	$t->set_var("admin_href", "admin.php");
	$t->pparse("main");

