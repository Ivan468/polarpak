<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_credit_card_info.php                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");

	check_admin_security("payment_systems");

	$payment_id = get_param("payment_id");
	$setting_type = "credit_card_info_" . $payment_id;
	$sql = " SELECT payment_name FROM " . $table_prefix . "payment_systems WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$payment_name = get_translation($db->f("payment_name"), $language_code);
	} else {
		header ("Location: admin_payment_systems.php");
		exit;
	}

	$message_types = 
		array( 
			array(1, HTML_MSG), array(0, PLAIN_TEXT_MSG)
		);

	$cc_number_options = 
		array( 
			array(0, DONT_SAVE_MSG), 
			array(2, SAVE_ENCRUPTED_MSG)
		);

	$cc_code_options = 
		array( 
			array(0, DONT_SAVE_MSG), 
			array(2, SAVE_ENCRUPTED_MSG)
		);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_credit_card_info.html");
	$t->set_var("admin_credit_card_info_href", "admin_credit_card_info.php");
	$t->set_var("admin_payment_systems_href", "admin_payment_systems.php");
	$t->set_var("admin_payment_system_href", "admin_payment_system.php");
	$t->set_var("admin_order_property_href", "admin_order_property.php");
	$t->set_var("admin_order_help_href", "admin_order_help.php");
	$t->set_var("admin_email_help_href", "admin_email_help.php");
	$t->set_var("payment_id",   $payment_id);
	$t->set_var("payment_name", $payment_name);

	$r = new VA_Record($table_prefix . "credit_card_info");

	$r->add_textbox("intro_text", TEXT);

	// set up html form parameters
	$r->add_checkbox("show_payment_full_name", INTEGER);
	$r->add_checkbox("show_payment_first_name", INTEGER);
	$r->add_checkbox("show_payment_middle_name", INTEGER);
	$r->add_checkbox("show_payment_last_name", INTEGER);
	$r->add_checkbox("show_payment_company_name", INTEGER);
	$r->add_checkbox("show_payment_email", INTEGER);
	$r->add_checkbox("show_payment_address1", INTEGER);
	$r->add_checkbox("show_payment_address2", INTEGER);
	$r->add_checkbox("show_payment_address3", INTEGER);
	$r->add_checkbox("show_payment_city", INTEGER);
	$r->add_checkbox("show_payment_province", INTEGER);
	$r->add_checkbox("show_payment_country_id", INTEGER);
	$r->add_checkbox("show_payment_state_id", INTEGER);
	$r->add_checkbox("show_payment_postal_code", INTEGER);
	$r->add_checkbox("show_payment_phone", INTEGER);
	$r->add_checkbox("show_payment_daytime_phone", INTEGER);
	$r->add_checkbox("show_payment_evening_phone", INTEGER);
	$r->add_checkbox("show_payment_cell_phone", INTEGER);

	$r->add_checkbox("show_payment_card_number", INTEGER);
	$r->add_checkbox("show_payment_card_start_date", INTEGER);
	$r->add_checkbox("show_payment_card_expiry_date", INTEGER);
	$r->add_checkbox("show_payment_card_type_id", INTEGER);
	$r->add_checkbox("show_payment_card_issue_number", INTEGER);
	$r->add_checkbox("show_payment_card_security_code", INTEGER);
   
	$r->add_checkbox("payment_full_name_required", INTEGER);
	$r->add_checkbox("payment_first_name_required", INTEGER);
	$r->add_checkbox("payment_middle_name_required", INTEGER);
	$r->add_checkbox("payment_last_name_required", INTEGER);
	$r->add_checkbox("payment_company_name_required", INTEGER);
	$r->add_checkbox("payment_email_required", INTEGER);
	$r->add_checkbox("payment_address1_required", INTEGER);
	$r->add_checkbox("payment_address2_required", INTEGER);
	$r->add_checkbox("payment_address3_required", INTEGER);
	$r->add_checkbox("payment_city_required", INTEGER);
	$r->add_checkbox("payment_province_required", INTEGER);
	$r->add_checkbox("payment_country_id_required", INTEGER);
	$r->add_checkbox("payment_state_id_required", INTEGER);
	$r->add_checkbox("payment_postal_code_required", INTEGER);
	$r->add_checkbox("payment_phone_required", INTEGER);
	$r->add_checkbox("payment_daytime_phone_required", INTEGER);
	$r->add_checkbox("payment_evening_phone_required", INTEGER);
	$r->add_checkbox("payment_cell_phone_required", INTEGER);

	$r->add_checkbox("payment_card_number_required", INTEGER);
	$r->add_checkbox("payment_card_start_date_required", INTEGER);
	$r->add_checkbox("payment_card_expiry_date_required", INTEGER);
	$r->add_checkbox("payment_card_type_id_required", INTEGER);
	$r->add_checkbox("payment_card_issue_number_required", INTEGER);
	$r->add_checkbox("payment_card_security_code_required", INTEGER);
	
	// add checkboxes for Call Center
	$r->add_checkbox("call_center_show_payment_full_name", INTEGER);
	$r->add_checkbox("call_center_show_payment_first_name", INTEGER);
	$r->add_checkbox("call_center_show_payment_middle_name", INTEGER);
	$r->add_checkbox("call_center_show_payment_last_name", INTEGER);
	$r->add_checkbox("call_center_show_payment_company_name", INTEGER);
	$r->add_checkbox("call_center_show_payment_email", INTEGER);
	$r->add_checkbox("call_center_show_payment_address1", INTEGER);
	$r->add_checkbox("call_center_show_payment_address2", INTEGER);
	$r->add_checkbox("call_center_show_payment_address3", INTEGER);
	$r->add_checkbox("call_center_show_payment_city", INTEGER);
	$r->add_checkbox("call_center_show_payment_province", INTEGER);
	$r->add_checkbox("call_center_show_payment_country_id", INTEGER);
	$r->add_checkbox("call_center_show_payment_state_id", INTEGER);
	$r->add_checkbox("call_center_show_payment_postal_code", INTEGER);
	$r->add_checkbox("call_center_show_payment_phone", INTEGER);
	$r->add_checkbox("call_center_show_payment_daytime_phone", INTEGER);
	$r->add_checkbox("call_center_show_payment_evening_phone", INTEGER);
	$r->add_checkbox("call_center_show_payment_cell_phone", INTEGER);

	$r->add_checkbox("call_center_show_payment_card_number", INTEGER);
	$r->add_checkbox("call_center_show_payment_card_start_date", INTEGER);
	$r->add_checkbox("call_center_show_payment_card_expiry_date", INTEGER);
	$r->add_checkbox("call_center_show_payment_card_type_id", INTEGER);
	$r->add_checkbox("call_center_show_payment_card_issue_number", INTEGER);
	$r->add_checkbox("call_center_show_payment_card_security_code", INTEGER);
   
	$r->add_checkbox("call_center_payment_full_name_required", INTEGER);
	$r->add_checkbox("call_center_payment_first_name_required", INTEGER);
	$r->add_checkbox("call_center_payment_middle_name_required", INTEGER);
	$r->add_checkbox("call_center_payment_last_name_required", INTEGER);
	$r->add_checkbox("call_center_payment_company_name_required", INTEGER);
	$r->add_checkbox("call_center_payment_email_required", INTEGER);
	$r->add_checkbox("call_center_payment_address1_required", INTEGER);
	$r->add_checkbox("call_center_payment_address2_required", INTEGER);
	$r->add_checkbox("call_center_payment_address3_required", INTEGER);
	$r->add_checkbox("call_center_payment_city_required", INTEGER);
	$r->add_checkbox("call_center_payment_province_required", INTEGER);
	$r->add_checkbox("call_center_payment_country_id_required", INTEGER);
	$r->add_checkbox("call_center_payment_state_id_required", INTEGER);
	$r->add_checkbox("call_center_payment_postal_code_required", INTEGER);
	$r->add_checkbox("call_center_payment_phone_required", INTEGER);
	$r->add_checkbox("call_center_payment_daytime_phone_required", INTEGER);
	$r->add_checkbox("call_center_payment_evening_phone_required", INTEGER);
	$r->add_checkbox("call_center_payment_cell_phone_required", INTEGER);

	$r->add_checkbox("call_center_payment_card_number_required", INTEGER);
	$r->add_checkbox("call_center_payment_card_start_date_required", INTEGER);
	$r->add_checkbox("call_center_payment_card_expiry_date_required", INTEGER);
	$r->add_checkbox("call_center_payment_card_type_id_required", INTEGER);
	$r->add_checkbox("call_center_payment_card_issue_number_required", INTEGER);
	$r->add_checkbox("call_center_payment_card_security_code_required", INTEGER);

	$r->add_textbox("cc_allowed", TEXT);
	$r->add_textbox("cc_forbidden", TEXT);

	$r->add_checkbox("cc_number_split", INTEGER);
	$r->add_radio("cc_number_security", INTEGER, $cc_number_options);
	$r->add_radio("cc_code_security", INTEGER, $cc_code_options);

	$r->add_checkbox("admin_notification", INTEGER);
	// PGP enable
	$r->add_checkbox("admin_notification_pgp", INTEGER);
	
	$r->add_textbox("admin_email", TEXT);
	$r->add_textbox("admin_mail_from", TEXT);
	$r->add_textbox("cc_emails", TEXT);
	$r->add_textbox("admin_mail_bcc", TEXT);
	$r->add_textbox("admin_mail_reply_to", TEXT);
	$r->add_textbox("admin_mail_return_path", TEXT);
	$r->add_textbox("admin_subject", TEXT);
	$r->add_radio("admin_message_type", TEXT, $message_types);
	$r->add_textbox("admin_message", TEXT);

	// sms notification settings
	$r->add_checkbox("admin_sms_notification", INTEGER);
	$r->add_textbox("admin_sms_recipient", TEXT, ADMIN_SMS_RECIPIENT_MSG);
	$r->add_textbox("admin_sms_originator", TEXT, ADMIN_SMS_ORIGINATOR_MSG);
	$r->add_textbox("admin_sms_message", TEXT, ADMIN_SMS_MESSAGE_MSG);

	$r->get_form_values();

	$param_site_id = get_session("session_site_id");
	$operation = get_param("operation");
	$return_page = get_param("rp");
	if (!strlen($return_page)) $return_page = "admin_payment_systems.php";

	if (strlen($operation))
	{
		if ($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		}

		if ($r->get_value("admin_sms_notification")) {
			$r->change_property("admin_sms_recipient", REQUIRED, true);
			$r->change_property("admin_sms_message", REQUIRED, true);
		}

		$r->validate();

		if (!strlen($r->errors))
		{
			$sql = "DELETE FROM " . $table_prefix . "global_settings WHERE setting_type=" . $db->tosql($setting_type, TEXT);
			if ($multisites_version) {
				$sql .= " AND site_id=" . $db->tosql($param_site_id,INTEGER);
			}
			$db->query($sql);
			foreach ($r->parameters as $key => $value)
			{
				if ($multisites_version) {
					$sql  = "INSERT INTO " . $table_prefix . "global_settings (setting_type, setting_name, setting_value, site_id) VALUES (";
					$sql .= $db->tosql($setting_type, TEXT) . ", '" . $key . "'," . $db->tosql($value[CONTROL_VALUE], TEXT) . ",";
					$sql .= $db->tosql($param_site_id,INTEGER) . ") ";
				} else {
					$sql  = "INSERT INTO " . $table_prefix . "global_settings (setting_type, setting_name, setting_value) VALUES (";
					$sql .= $db->tosql($setting_type, TEXT) . ", '" . $key . "'," . $db->tosql($value[CONTROL_VALUE], TEXT) . ")";
				}
				$db->query($sql);
			}

			header("Location: " . $return_page);
			exit;
		}
	}
	else // get credit_order_info settings
	{
		foreach ($r->parameters as $key => $value)
		{
			$sql  = "SELECT setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= "WHERE setting_type=" . $db->tosql($setting_type, TEXT) . " AND setting_name='" . $key . "'";
			if ($multisites_version) {
				$sql .= "AND ( site_id=1 OR  site_id=" . $db->tosql($param_site_id,INTEGER). ") ";
				$sql .= "ORDER BY site_id DESC ";
			}
			$r->set_value($key, get_db_value($sql));
		}
	}

	$sql  = " SELECT property_id, property_name, property_type ";
	$sql .= " FROM " . $table_prefix . "order_custom_properties ";
	$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
	$sql .= " ORDER BY property_order, property_id ";
	$db->query($sql);
	if ($db->next_record()) {
		$property_types = array("0" => HIDDEN_MSG, "4" => ACTIVE_MSG);

		do {
			$property_id = $db->f("property_id");
			$property_name = $db->f("property_name");
			$property_type = $property_types[$db->f("property_type")];
			$t->set_var("property_id",   $property_id);
			$t->set_var("property_name", $property_name);
			$t->set_var("property_type", $property_type);

			$t->parse("properties", true);
		} while ($db->next_record());
	} else {
		$t->parse("no_properties", false);
	}


	$r->set_parameters();
	$t->set_var("rp", htmlspecialchars($return_page));
	
	// multisites
	if ($sitelist) {
		$sites = get_db_values("SELECT site_id,site_name FROM " . $table_prefix . "sites ORDER BY site_id ", "");
		set_options($sites, $param_site_id, "param_site_id");
		$t->parse("sitelist");
	}

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

?>