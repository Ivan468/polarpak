<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  settings_fedex.php                                       ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/



	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/tabs_functions.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once($root_folder_path . "messages/" . $language_code . "/reviews_messages.php");
	include_once("./admin_common.php");

	check_admin_security("shipping_methods");

	$setting_type = "fedex";

	$va_trail = array(
		"admin_global_settings.php" => va_constant("SETTINGS_MSG"),
		"admin_order_info.php" => va_constant("ORDERS_MSG"),
		"settings_fedex.php" => va_constant("FedEx Settings"),
	);

	$account_types =
		array(
			array("live", va_message("Live")), 
			array("test", va_message("Test")),
			);

	$address_validation_values =
		array(
			array("1", va_message("Yes")),
			array("0", va_message("No")),
			);

	$error_actions =
		array(
			array("ignore", va_message("Ignore errors (in case of any errors user won't see them and can submit order without any restrictions)")), 
			array("warning", va_message("Show warning message (a warning message will be shown but user can submit his order)")),
			array("error", va_message("Show error message (user can't submit his order until errors will be corrected)")),
			);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "settings_fedex.html");

	include_once("./admin_header.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("settings_fedex_href", "settings_fedex.php");
	$t->set_var("date_edit_format", join("", $date_edit_format));
	
	$r = new VA_Record($table_prefix . "global_settings");
	// global reviews settings
	$r->add_textbox("live_key", TEXT);
	$r->add_textbox("test_key", TEXT);
	$r->add_textbox("live_password", TEXT);
	$r->add_textbox("test_password", TEXT);
	$r->add_textbox("live_account_no", TEXT);
	$r->add_textbox("test_account_no", TEXT);
	$r->add_textbox("live_meter_no", TEXT);
	$r->add_textbox("test_meter_no", TEXT);
	$r->add_radio("account_type", TEXT, $account_types);

	$r->add_radio("address_validation", TEXT, $address_validation_values);
	$r->add_radio("address_system_errors", TEXT, $error_actions);
	$r->add_radio("address_validation_errors", TEXT, $error_actions);

	$r->add_checkbox("address_suggest_zip9", TEXT);
	$r->add_checkbox("address_suggest_street", TEXT);

	$r->add_checkbox("address_validation_sites_all", TEXT);
	$r->add_textbox("address_validation_sites_ids", TEXT);
	$r->add_checkbox("address_validation_users_all", TEXT);
	$r->add_textbox("address_validation_user_types_ids", TEXT);

	
	$r->get_form_values();

	$param_site_id = get_session("session_site_id");
	//$tab = get_param("tab");
	//if (!$tab) { $tab = "general"; }
	$operation = get_param("operation");
	$return_page = get_param("rp");
	if (!strlen($return_page)) $return_page = "admin.php";
	if (strlen($operation))
	{
		if ($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		}

		$is_valid = $r->validate();

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
		// get settings
		foreach ($r->parameters as $key => $value) {
			$sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT); 
			$sql .= " AND setting_name='" . $key . "'";
			$sql .= " AND ( site_id=1 OR site_id=" . $db->tosql($param_site_id,INTEGER). ") ";
			$sql .= " ORDER BY site_id DESC ";
			$r->set_value($key, get_db_value($sql));
		}
	}

	$r->set_parameters();
	$t->set_var("rp", htmlspecialchars($return_page));

	// multi-site settings
	multi_site_settings();

	// set styles for tabs
	$tabs = array(
		"general" => array("title" => va_message("ADMIN_GENERAL_MSG")), 
		"address_validation" => array("title" => va_message("Address Validation")), 
	);
	parse_tabs($tabs);

	include_once("./admin_footer.php");
	
	$t->pparse("main");

