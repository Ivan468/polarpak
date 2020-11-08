<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_support_department.php                             ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/tabs_functions.php");
	include_once($root_folder_path."messages/".$language_code."/support_messages.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("support_departments");

	$ajax = get_param("ajax");
	$dep_id = get_param("dep_id");
	$operation = get_param("operation");

	if ($ajax) {
		if ($operation == "override") {
			$override_field = get_param("override_field");
			$override_value = get_param("override_value");
			if ($dep_id && preg_match("/^[0-9a-z_]+$/i", $override_field)) {	
				$sql  = " UPDATE ".$table_prefix . "support_departments SET ";
				$sql .= $override_field."=".$db->tosql($override_value, TEXT);
				$sql .= " WHERE dep_id=".$db->tosql($dep_id, INTEGER);
				$db->query($sql);
			}
		}
		exit;
	}

	// global JS parameters for messages
	$number_rules_msg = va_message("NUMBER_RULES_MSG");
	$number_rules_msg = str_replace("{number_rules}", "[number_rules]", $number_rules_msg);
	if (!isset($js_settings["messages"])) {
		$js_settings["messages"] = array();
	}
	$js_settings["messages"]["NUMBER_RULES_MSG"] = $number_rules_msg;
	$js_settings["messages"]["NO_RULES_MSG"] = va_message("NO_RULES_MSG");

	// start building breadcrumb
	$va_trail = array(
		"admin_menu.php?code=settings" => va_message("SETTINGS_MSG"),
		"admin_menu.php?code=helpdesk-settings" => va_message("HELPDESK_MSG"),
		"admin_support_departments.php" => va_message("SUPPORT_DEPARTMENTS_MSG"),
		"admin_support_department.php?dep_id=".urlencode($dep_id) => va_message("EDIT_MSG"),
	);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_support_department.html");

	set_script_tag("../js/list_fields.js", true);
	set_script_tag("../js/rule.js", true);

	$t->set_var("admin_support_href", "admin_support.php");
	$t->set_var("admin_support_dep_edit_href", "admin_support_dep_edit.php");
	$t->set_var("admin_support_departments_href", "admin_support_departments.php");
	$t->set_var("admin_support_help_href", "admin_support_help.php");
	$t->set_var("admin_support_params_href", "admin_support_params.php");
	$t->set_var("admin_email_help_href", "admin_email_help.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", va_message("SUPPORT_DEPARTMENT_FIELD"), va_message("CONFIRM_DELETE_MSG")));

	$message_types = 
		array( 
			array(1, va_message("HTML_MSG")), array(0, va_message("PLAIN_TEXT_MSG"))
		);

	$show_values = 
		array( 
			array("", va_message("USE_GLOBAL_MSG")), 
			array(1, va_message("YES_MSG")),
			array(0, va_message("NO_MSG"))
		);

	$sql  = " SELECT status_id, status_name ";
	$sql .= " FROM " . $table_prefix . "support_statuses ";
	$sql .= " ORDER BY status_order, status_name ASC";
	$support_statuses = get_db_values($sql, array(array("", "")));

	$r = new VA_Record($table_prefix . "support_departments");
	$r->return_page = "admin_support_departments.php";

	$admins =array();
	$sql = " SELECT admin_id,admin_name FROM ".$table_prefix ."admins ORDER BY admin_id ASC";
	$db->query($sql);
	if ($db->next_record()) {
		$usr = new VA_Record($table_prefix . "admins");
		do {
			$admins[$db->f("admin_id")] = $db->f("admin_name");
			$usr->add_checkbox("admin_".$db->f("admin_id"), INTEGER);
		}
		while($db->next_record());
	}	

	$r->get_form_values();
	$usr->get_form_values();

	$r->add_where("dep_id", INTEGER);
	$r->add_checkbox("show_for_user", INTEGER);
	$r->add_checkbox("is_default", INTEGER);
	$r->add_textbox("short_name", TEXT, va_message("SHORT_NAME_MSG"));
	$r->parameters["short_name"][REQUIRED] = true;
	$r->add_textbox("dep_name", TEXT, va_message("DEPARTMENT_NAME_MSG"));
	$r->parameters["dep_name"][REQUIRED] = true;
	$r->add_textbox("intro_text", TEXT, va_message("INTRO_TEXT_MSG"));
	$r->add_textbox("attachments_dir", TEXT, va_message("ATTACHMENTS_DIRECTORY_MSG"));
	$r->add_textbox("attachments_mask", TEXT, va_message("FILES_ALLOWED_MSG"));

	$r->add_textbox("signature", TEXT, va_message("SIGNATURE_MSG"));
	$r->add_checkbox("admins_all", INTEGER);	
	$r->add_checkbox("sites_all", INTEGER);	

	// predefined fields and other department settings
	$r->add_textbox("dep_settings", TEXT);

	// mail fields and their fields for override rules 
	$r->add_textbox("new_admin_mail", TEXT);
	$r->add_textbox("over_new_admin_mail", TEXT);
	$r->add_textbox("new_user_mail", TEXT);
	$r->add_textbox("over_new_user_mail", TEXT);
	$r->add_textbox("user_reply_admin_mail", TEXT);
	$r->add_textbox("over_user_reply_admin_mail", TEXT);
	$r->add_textbox("user_reply_user_mail", TEXT);
	$r->add_textbox("over_user_reply_user_mail", TEXT);
	$r->add_textbox("manager_reply_admin_mail", TEXT);
	$r->add_textbox("over_manager_reply_admin_mail", TEXT);
	$r->add_textbox("manager_reply_manager_mail", TEXT);
	$r->add_textbox("over_manager_reply_manager_mail", TEXT);
	$r->add_textbox("manager_reply_user_mail", TEXT);
	$r->add_textbox("over_manager_reply_user_mail", TEXT);
	$r->add_textbox("assign_admin_mail", TEXT);
	$r->add_textbox("over_assign_admin_mail", TEXT);
	$r->add_textbox("assign_manager_mail", TEXT);
	$r->add_textbox("over_assign_manager_mail", TEXT);
	$r->add_textbox("assign_to_mail", TEXT);
	$r->add_textbox("over_assign_to_mail", TEXT);
	$r->add_textbox("assign_user_mail", TEXT);
	$r->add_textbox("over_assign_user_mail", TEXT);

	// predefined controls and mail fields with related controls
	$json_fields = array(
		// predefined fields and other department settings 
		"dep_settings" => array(
			"dep_field_show" => array(CONTROL_TYPE => LISTBOX, VALUE_TYPE => INTEGER, VALUES_LIST => $show_values), 
			"dep_field_name" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"dep_field_order" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => INTEGER),  
			"type_field_show" => array(CONTROL_TYPE => LISTBOX, VALUE_TYPE => INTEGER, VALUES_LIST => $show_values), 
			"type_field_name" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"type_field_required" => array(CONTROL_TYPE => LISTBOX, VALUE_TYPE => TEXT, VALUES_LIST => $show_values),   
			"type_field_order" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => INTEGER),  
			"user_name_field_show" => array(CONTROL_TYPE => LISTBOX, VALUE_TYPE => INTEGER, VALUES_LIST => $show_values), 
			"user_name_field_name" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"user_name_field_required" => array(CONTROL_TYPE => LISTBOX, VALUE_TYPE => TEXT, VALUES_LIST => $show_values),   
			"user_name_field_order" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => INTEGER),  
			"user_email_field_show" => array(CONTROL_TYPE => LISTBOX, VALUE_TYPE => INTEGER, VALUES_LIST => $show_values), 
			"user_email_field_name" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"user_email_field_required" => array(CONTROL_TYPE => LISTBOX, VALUE_TYPE => TEXT, VALUES_LIST => $show_values),   
			"user_email_field_order" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => INTEGER),  
			"identifier_field_show" => array(CONTROL_TYPE => LISTBOX, VALUE_TYPE => INTEGER, VALUES_LIST => $show_values), 
			"identifier_field_name" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"identifier_field_required" => array(CONTROL_TYPE => LISTBOX, VALUE_TYPE => TEXT, VALUES_LIST => $show_values),   
			"identifier_field_order" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => INTEGER),  
			"environment_field_show" => array(CONTROL_TYPE => LISTBOX, VALUE_TYPE => INTEGER, VALUES_LIST => $show_values), 
			"environment_field_name" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"environment_field_required" => array(CONTROL_TYPE => LISTBOX, VALUE_TYPE => TEXT, VALUES_LIST => $show_values),   
			"environment_field_order" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => INTEGER),  
			"product_field_show" => array(CONTROL_TYPE => LISTBOX, VALUE_TYPE => INTEGER, VALUES_LIST => $show_values), 
			"product_field_name" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"product_field_required" => array(CONTROL_TYPE => LISTBOX, VALUE_TYPE => TEXT, VALUES_LIST => $show_values),   
			"product_field_order" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => INTEGER),  
			"summary_field_show" => array(CONTROL_TYPE => LISTBOX, VALUE_TYPE => INTEGER, VALUES_LIST => $show_values), 
			"summary_field_name" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"summary_field_required" => array(CONTROL_TYPE => LISTBOX, VALUE_TYPE => TEXT, VALUES_LIST => $show_values),   
			"summary_field_order" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => INTEGER),  
			"description_field_show" => array(CONTROL_TYPE => LISTBOX, VALUE_TYPE => INTEGER, VALUES_LIST => $show_values), 
			"description_field_name" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"description_field_required" => array(CONTROL_TYPE => LISTBOX, VALUE_TYPE => TEXT, VALUES_LIST => $show_values),   
			"description_field_order" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => INTEGER),  
			"attachments_field_show" => array(CONTROL_TYPE => LISTBOX, VALUE_TYPE => INTEGER, VALUES_LIST => $show_values), 
			"attachments_field_name" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"attachments_field_required" => array(CONTROL_TYPE => LISTBOX, VALUE_TYPE => TEXT, VALUES_LIST => $show_values),   
			"attachments_field_order" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => INTEGER),  
			"success_message" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"new_status_id" => array(CONTROL_TYPE => LISTBOX, VALUE_TYPE => INTEGER, VALUES_LIST => $support_statuses),   
		),
		// new ticket notification fields
		"new_admin_mail" => array(
			"new_admin_notification" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER), 
			"new_admin_hp_disable" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"new_admin_to" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"new_admin_from" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"new_admin_cc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"new_admin_bcc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"new_admin_reply_to" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"new_admin_return_path" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"new_admin_subject" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"new_admin_message_type" => array(CONTROL_TYPE => RADIOBUTTON, VALUE_TYPE => TEXT, VALUES_LIST => $message_types),
			"new_admin_message" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
		),
		"new_user_mail" => array(
			"new_user_notification" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"new_user_hp_disable" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"new_user_from" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"new_user_cc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"new_user_bcc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"new_user_reply_to" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"new_user_return_path" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"new_user_subject" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"new_user_message_type" => array(CONTROL_TYPE => RADIOBUTTON, VALUE_TYPE => TEXT, VALUES_LIST => $message_types),
			"new_user_message" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
		),
		// end new ticket notification fields
		// user ticket reply notification fields
		"user_reply_admin_mail" => array(
			"user_reply_admin_notification" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"user_reply_admin_hp_disable" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"user_reply_admin_to" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"user_reply_admin_from" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"user_reply_admin_cc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"user_reply_admin_bcc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"user_reply_admin_reply_to" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"user_reply_admin_return_path" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"user_reply_admin_subject" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"user_reply_admin_message_type" => array(CONTROL_TYPE => RADIOBUTTON, VALUE_TYPE => TEXT, VALUES_LIST => $message_types),
			"user_reply_admin_message" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
		),
		"user_reply_user_mail" => array(
			"user_reply_user_notification" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"user_reply_user_hp_disable" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"user_reply_user_from" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"user_reply_user_cc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"user_reply_user_bcc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"user_reply_user_reply_to" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"user_reply_user_return_path" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"user_reply_user_subject" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"user_reply_user_message_type" => array(CONTROL_TYPE => RADIOBUTTON, VALUE_TYPE => TEXT, VALUES_LIST => $message_types),
			"user_reply_user_message" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
		),
		// end user ticket reply notification fields
		// manager ticket reply notification fields
		"manager_reply_admin_mail" => array(
			"manager_reply_admin_notification" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"manager_reply_admin_hp_disable" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"manager_reply_admin_to" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"manager_reply_admin_from" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"manager_reply_admin_cc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"manager_reply_admin_bcc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"manager_reply_admin_reply_to" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"manager_reply_admin_return_path" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"manager_reply_admin_subject" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"manager_reply_admin_message_type" => array(CONTROL_TYPE => RADIOBUTTON, VALUE_TYPE => TEXT, VALUES_LIST => $message_types),
			"manager_reply_admin_message" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
		),
		"manager_reply_manager_mail" => array(
			"manager_reply_manager_notification" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"manager_reply_manager_hp_disable" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"manager_reply_manager_from" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"manager_reply_manager_cc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"manager_reply_manager_bcc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"manager_reply_manager_reply_to" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"manager_reply_manager_return_path" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"manager_reply_manager_subject" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"manager_reply_manager_message_type" => array(CONTROL_TYPE => RADIOBUTTON, VALUE_TYPE => TEXT, VALUES_LIST => $message_types),
			"manager_reply_manager_message" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
		),
		"manager_reply_user_mail" => array(
			"manager_reply_user_notification" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"manager_reply_user_hp_disable" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"manager_reply_user_from" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"manager_reply_user_cc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"manager_reply_user_bcc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"manager_reply_user_reply_to" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"manager_reply_user_return_path" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"manager_reply_user_subject" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"manager_reply_user_message_type" => array(CONTROL_TYPE => RADIOBUTTON, VALUE_TYPE => TEXT, VALUES_LIST => $message_types),
			"manager_reply_user_message" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
		),
		// end manager ticket reply notification fields
		// manager assignment notification fields
		"assign_admin_mail" => array(
			"assign_admin_notification" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"assign_admin_hp_disable" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"assign_admin_to" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_admin_from" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_admin_cc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_admin_bcc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_admin_reply_to" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_admin_return_path" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_admin_subject" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_admin_message_type" => array(CONTROL_TYPE => RADIOBUTTON, VALUE_TYPE => TEXT, VALUES_LIST => $message_types),
			"assign_admin_message" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
		),
		"assign_manager_mail" => array(
			"assign_manager_notification" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"assign_manager_hp_disable" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"assign_manager_from" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_manager_cc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_manager_bcc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_manager_reply_to" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_manager_return_path" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_manager_subject" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_manager_message_type" => array(CONTROL_TYPE => RADIOBUTTON, VALUE_TYPE => TEXT, VALUES_LIST => $message_types),
			"assign_manager_message" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
		),
		"assign_to_mail" => array(
			"assign_to_notification" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"assign_to_hp_disable" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"assign_to_from" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_to_cc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_to_bcc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_to_reply_to" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_to_return_path" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_to_subject" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_to_message_type" => array(CONTROL_TYPE => RADIOBUTTON, VALUE_TYPE => TEXT, VALUES_LIST => $message_types),
			"assign_to_message" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
		),
		"assign_user_mail" => array(
			"assign_user_notification" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"assign_user_hp_disable" => array(CONTROL_TYPE => CHECKBOX, VALUE_TYPE => INTEGER),  
			"assign_user_from" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_user_cc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_user_bcc" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_user_reply_to" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_user_return_path" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_user_subject" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
			"assign_user_message_type" => array(CONTROL_TYPE => RADIOBUTTON, VALUE_TYPE => TEXT, VALUES_LIST => $message_types),
			"assign_user_message" => array(CONTROL_TYPE => TEXTBOX, VALUE_TYPE => TEXT),  
		),
		// end manager assignment notification fields
	);

	// set main predefined controls and mail fields controls and their related controls
	$mr = new VA_Record("");
	foreach ($json_fields as $mail_field => $form_fields) {
		$r->add_textbox($mail_field, TEXT);
		foreach ($form_fields as $field_name => $field_data) {
			$control_type = get_setting_value($field_data, CONTROL_TYPE, TEXTBOX);
			$value_type = get_setting_value($field_data, VALUE_TYPE, TEXT);
			$values_list = get_setting_value($field_data, VALUES_LIST, array());
			if ($control_type == CHECKBOX) {
				$mr->add_checkbox($field_name, $value_type);
			} else if ($control_type == LISTBOX) {
				$mr->add_select($field_name, $value_type, $values_list);
			} else if ($control_type == RADIOBUTTON) {
				$mr->add_radio($field_name, $value_type, $values_list);
			} else {
				$mr->add_textbox($field_name, $value_type);
			}
		}
	}

	$r->get_form_values();
	$mr->get_form_values();

	$operation = get_param("operation");
	$dep_id = get_param("dep_id");
	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }
	
	$selected_sites = array();
	if (strlen($operation)) {
		$sites = get_param("sites");
		if ($sites) {
			$selected_sites = explode(",", $sites);
		}
	} elseif ($dep_id) {
		$sql  = "SELECT site_id FROM " . $table_prefix . "support_departments_sites  ";
		$sql .= " WHERE dep_id=" . $db->tosql($dep_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$selected_sites[] = $db->f("site_id");
		}
	}
	
	if(strlen($operation))	{
		$is_valid=true;
		if($operation == "cancel")		{
			header("Location: " . $r->return_page);
			exit;
		}
		else if($operation == "delete" && $dep_id)    // deleting department
		{
			$r->delete_record();
			$db->query("DELETE FROM ".$table_prefix ."support_users_departments WHERE dep_id=".$db->tosql($dep_id, INTEGER));
			$db->query("DELETE FROM ".$table_prefix ."support_departments_sites WHERE dep_id=".$db->tosql($dep_id, INTEGER));	
			header("Location: " . $r->return_page);
			exit;
		}	
		if($is_valid) {
			$is_valid = $r->validate();
			$mr_valid = $mr->validate();
		}
		if($is_valid)	{
			// prepare predefined controls and mail fields
			foreach ($json_fields as $mail_field => $form_fields) {
				$mail_data = array();
				foreach ($form_fields as $field_name => $field_data) {
					$field_value = $mr->get_value($field_name);
					$mail_data[$field_name] = $field_value;
				}
				$r->set_value($mail_field, json_encode($mail_data));
			}

			if(strlen($r->get_value("dep_id"))) {   // insert existing department
				$record_updated = $r->update_record();
			} else {
				$record_updated = $r->insert_record(); // insert new department
				if ($record_updated) {
					$dep_id = $db->last_insert_id();
					$r->set_value("dep_id", $dep_id);
				}
			}
			if ($record_updated) {
				
				// update sites
				$db->query("DELETE FROM " . $table_prefix . "support_departments_sites WHERE dep_id=" . $db->tosql($dep_id, INTEGER));
				for ($st = 0; $st < sizeof($selected_sites); $st++) {
					$site_id = $selected_sites[$st];
					if (strlen($site_id)) {
						$sql  = " INSERT INTO " . $table_prefix . "support_departments_sites (dep_id, site_id) VALUES (";
						$sql .= $db->tosql($dep_id, INTEGER) . ", ";
						$sql .= $db->tosql($site_id, INTEGER) . ") ";
						$db->query($sql);
					}
				}
				
				foreach($admins as $admin_id => $admin_name)
				{
					$admin="admin_".$admin_id;
					if ($usr->get_value($admin)) {
						$sql  = " SELECT dep_id FROM ".$table_prefix ."support_users_departments ";
						$sql .= " WHERE dep_id=" . $db->tosql($dep_id, INTEGER);
						$sql .= " AND admin_id=" . $db->tosql($admin_id, INTEGER);
						$db->query($sql);
						if (!$db->next_record()) {
							$sql = " SELECT COUNT(*) FROM ".$table_prefix ."support_users_departments WHERE admin_id=" . $db->tosql($admin_id, INTEGER);
							$admin_departments = get_db_value($sql);
							$sql  = " INSERT INTO ".$table_prefix ."support_users_departments (admin_id, dep_id, is_default_dep) VALUES (";
							$sql .= $db->tosql($admin_id, INTEGER) . ", ";
							$sql .= $db->tosql($dep_id, INTEGER) . ", ";
							if ($admin_departments) {
								$sql .= "0) ";
							} else {
								$sql .= "1) ";
							}
							$db->query($sql);
						}
					} else {
						$sql  = " DELETE FROM ".$table_prefix ."support_users_departments ";
						$sql .= " WHERE dep_id=" . $db->tosql($dep_id, INTEGER);
						$sql .= " AND admin_id=" . $db->tosql($admin_id, INTEGER);
						$db->query($sql);
					}
				}
 				header("Location: " . $r->return_page);
				exit;
			}
		}
	}
	else if(strlen($r->get_value("dep_id")))   	// show existing values 
	{
		$r->get_db_values();
		$admin_checked = array();
    $sql  = " SELECT admin_id FROM ".$table_prefix ."support_users_departments ";
		$sql .= " WHERE dep_id = " . $db->tosql($dep_id, INTEGER);
		$db->query($sql);
		while($db->next_record()) {
			$admin_checked[$db->f("admin_id")] = 1;
		}
		foreach ($admins as $admin_id => $admin_name){
			$admin="admin_".$admin_id;
			if (isset($admin_checked[$admin_id])) {
				if($admin_checked[$admin_id]) $usr->set_value($admin, 1);
				else $usr->set_value($admin, 0);
			}
			else $usr->set_value($admin, 0);
		}	

		// prepare predefined controls and mail fields
		foreach ($json_fields as $mail_field => $form_fields) {
			$json_data = $r->get_value($mail_field);
			$mail_data = json_decode($json_data, true);
			if (is_array($mail_data)) {
				foreach ($form_fields as $field_name => $field_data) {
					$field_value = isset($mail_data[$field_name]) ? $mail_data[$field_name] : "";
					$mr->set_value($field_name, $field_value);
					$mail_data[$field_name] = $field_value;
				}
			}
			$r->set_value($mail_field, "");
		}

	} else {
		$r->set_value("show_for_user", 1);
		$r->set_value("admins_all", 1);
		$r->set_value("sites_all", 1);
	}

	foreach($admins as $admin_id => $admin_name) {
		$admin="admin_".$admin_id;
		$admin_name_checked = $usr->get_value($admin) ? "checked" : "";
		$admin_checkbox = "<input type=\"checkbox\" name=\"$admin\" $admin_name_checked value=\"$admin_id\">";
		$t->set_var("admin_name", $admin_name);
		$t->set_var("admin_checkbox", $admin_checkbox);
		$t->parse ("admin_rows", true);
	}

	$over_fields = array(
		"over_new_admin_mail", 
		"over_new_user_mail", 
		"over_user_reply_admin_mail", 
		"over_user_reply_user_mail", 
		"over_manager_reply_admin_mail", 
		"over_manager_reply_manager_mail", 
		"over_manager_reply_user_mail", 
		"over_assign_admin_mail", 
		"over_assign_manager_mail", 
		"over_assign_to_mail", 
		"over_assign_user_mail", 
	);
	foreach ($over_fields as $fi => $over_field) {
		$rules_number = 0;
		$over_value = $r->get_value($over_field);
		if ($over_value) {
			$over_value = json_decode($over_value, true);
			if (is_array($over_value)) {
				$rules_number = count($over_value);;
			}
		}
		if ($rules_number > 0) {
			$over_message = str_replace("{number_rules}", $rules_number, va_message("NUMBER_RULES_MSG"));
		} else {
			$over_message = va_message("NO_RULES_MSG");
		}
		$t->set_var($over_field."_message", $over_message);
	}

	$r->set_parameters();
	$mr->set_parameters();

	if($r->get_value("dep_id")) {
		$t->set_var("save_button", va_message("UPDATE_BUTTON"));
		$t->parse("delete", false);	
	}	else {
		$t->set_var("save_button", va_message("ADD_NEW_MSG"));
		$t->set_var("delete", "");	
	}

	$sites = array();
	$sql = " SELECT site_id, site_name FROM " . $table_prefix . "sites ";
	$db->query($sql);
	while ($db->next_record())	{
		$site_id   = $db->f("site_id");
		$site_name = $db->f("site_name");
		$sites[$site_id] = $site_name;
		$t->set_var("site_id", $site_id);
		$t->set_var("site_name", $site_name);
		if (in_array($site_id, $selected_sites)) {
			$t->parse("selected_sites", true);
		} else {
			$t->parse("available_sites", true);
		}
	}


	// set styles for tabs
	$tabs = array(
		"general" => array("title" => va_constant("ADMIN_GENERAL_MSG")), 
		"predefined_fields" => array("title" => va_constant("PREDEFINED_FIELDS_MSG")), 
		"managers" => array("title" => va_constant("ASSIGNED_MANAGERS_MSG")), 
		"new_notification" => array("title" => va_constant("NEW_TICKET_NOTIFICATION_MSG")), 
		"user_reply_notification" => array("title" => va_constant("USER_TICKET_REPLY_NOTIFICATION_MSG")), 
		"manager_reply_notification" => array("title" => va_constant("MANAGER_TICKET_REPLY_NOTIFICATION_MSG")), 
		"assign_notification" => array("title" => va_constant("MANAGER_TICKET_ASSIGNMENT_NOTIFICATION_MSG")), 
		"sites" => array("title" => va_constant("ADMIN_SITES_MSG")),
	);
	parse_tabs($tabs);

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_href", "admin.php");
	$t->pparse("main");
