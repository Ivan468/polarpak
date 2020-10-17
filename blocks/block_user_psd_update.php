<?php

	$default_title = "";

	check_user_security("my_orders");

	$errors = "";
	$payment_id = get_param("payment_id");
	$psd_id = get_param("psd_id");
	$user_id = get_session("session_user_id");
	$user_type_id = get_session("session_user_type_id");
	if (!isset($site_id)) { $site_id = 1; }
	$operation = get_param("operation");

	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", "");
	$secure_redirect = get_setting_value($settings, "secure_redirect", 0);
	$secure_payments = get_setting_value($settings, "secure_payments", 0);
	$secure_order_profile = get_setting_value($settings, "secure_order_profile", 0);
	if ($secure_payments) {
		$psd_update_link = $secure_url . get_custom_friendly_url("user_psd_update.php");
		$user_psd_list = $secure_url . get_custom_friendly_url("user_psd_list.php");
	} else {
		$psd_update_link = $site_url . get_custom_friendly_url("user_psd_update.php");
		$user_psd_list = $site_url . get_custom_friendly_url("user_psd_list.php");
	}
	if ($psd_id) {
		$psd_update_link .= "?psd_id=" . urlencode($psd_id);
	} else if ($payment_id) {
		$psd_update_link .= "?payment_id=" . urlencode($payment_id);
	}
	if (!$is_ssl && $secure_payments && $secure_redirect && preg_match("/^https/i", $secure_url)) {
		header("Location: " . $psd_update_link);
		exit;
	}

	$html_template = get_setting_value($block, "html_template", "block_user_psd_update.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("user_home_href",   get_custom_friendly_url("user_home.php"));
	$t->set_var("user_orders_href", get_custom_friendly_url("user_orders.php"));
	$t->set_var("user_order_href",  get_custom_friendly_url("user_order.php"));
	$t->set_var("user_order_links_href", get_custom_friendly_url("user_order_links.php"));
	$t->set_var("user_order_note_href",  get_custom_friendly_url("user_order_note.php"));
	$t->set_var("user_order_update_href",  get_custom_friendly_url("user_order_update.php"));
	$confirm_delete_message = str_replace("{record_name}", PAYMENT_DETAILS_MSG, CONFIRM_DELETE_MSG);
	$t->set_var("confirm_delete_message", str_replace("'", "\\'", $confirm_delete_message));

	// check correct payment id parameter
	if ($psd_id) {
		$sql  = " SELECT payment_id FROM " . $table_prefix . "users_ps_details ";
		$sql .= " WHERE psd_id=" . $db->tosql($psd_id, INTEGER);
		$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
	} else if ($payment_id) {
		$sql  = " SELECT ps.payment_id FROM (" . $table_prefix . "payment_systems ps ";
		$sql .= " LEFT JOIN " . $table_prefix . "payment_user_types ut ON ut.payment_id=ps.payment_id)";			
		$sql .= " WHERE ps.payment_id=" . $db->tosql($payment_id, INTEGER);
		$sql .= " AND ps.is_active=1 AND ps.allowed_user_edit=1 ";
		$sql .= " AND (ps.user_types_all=1 OR ut.user_type_id=" . $db->tosql($user_type_id, INTEGER, true, false) . ")";			
	} else {
		$sql  = " SELECT ps.payment_id FROM (" . $table_prefix . "payment_systems ps ";
		$sql .= " LEFT JOIN " . $table_prefix . "payment_user_types ut ON ut.payment_id=ps.payment_id)";			
		$sql .= " WHERE ps.is_default=1 ";
		$sql .= " AND ps.is_active=1 AND ps.allowed_user_edit=1 ";
		$sql .= " AND (ps.user_types_all=1 OR ut.user_type_id=" . $db->tosql($user_type_id, INTEGER, true, false) . ")";			
	}
	$payment_id = get_db_value($sql);

	// check if user has permissions to edit his payment details
	$user_settings = array();
	$sql = "SELECT setting_name,setting_value FROM " . $table_prefix . "user_types_settings WHERE type_id=" . $db->tosql(get_session("session_user_type_id"), INTEGER);
	$db->query($sql);
	while ($db->next_record()) {
		$user_settings[$db->f("setting_name")] = $db->f("setting_value");
	}
	$user_edit_pd = get_setting_value($user_settings, "edit_pd", 0);

	if (!$user_edit_pd || !$payment_id) {
		header("Location: " . $user_psd_list);
		exit;
	}


	$order_profile = array(); $cc_info = array();
	// check payment settings
	$setting_type = "credit_card_info_" . $payment_id;
	$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
	$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
	$sql .= " ORDER BY site_id ASC ";
	$db->query($sql);
	while($db->next_record()) {
		$cc_info[$db->f("setting_name")] = $db->f("setting_value");
	}

	$cc_number_security = get_setting_value($cc_info, "cc_number_security", 0);
	$cc_code_security = get_setting_value($cc_info, "cc_code_security", 0);

	$pp = array(); $pn = 0;
	// prepare custom options for payment details
	$sql  = " SELECT * "; 
	$sql .= " FROM " . $table_prefix . "order_custom_properties "; 
	$sql .= " WHERE property_type = " . $db->tosql(4, INTEGER); 
	$sql .= " AND payment_id=" . $db->tosql($payment_id, INTEGER); 
	$sql .= " AND property_show IN (0,1) "; 
	$sql .= " AND site_id = " . $db->tosql($site_id, INTEGER);
	$sql .= " ORDER BY property_order, property_id "; 	
	$db->query($sql);
	if ($db->next_record()) {
		do {
			$pp[$pn]["property_id"] = $db->f("property_id");
			$pp[$pn]["property_order"] = $db->f("property_order");
			$pp[$pn]["property_name"] = $db->f("property_name");
			$pp[$pn]["payment_id"] = $db->f("payment_id");
			$pp[$pn]["property_description"] = $db->f("property_description");
			$pp[$pn]["default_value"] = $db->f("default_value");
			$pp[$pn]["property_style"] = $db->f("property_style");
			$pp[$pn]["section_id"] = $db->f("property_type");
			$pp[$pn]["control_type"] = $db->f("control_type");
			$pp[$pn]["control_style"] = $db->f("control_style");
			$pp[$pn]["control_code"] = $db->f("control_code");
			$pp[$pn]["onchange_code"] = $db->f("onchange_code");
			$pp[$pn]["onclick_code"] = $db->f("onclick_code");
			$pp[$pn]["required"] = $db->f("required");
			$pp[$pn]["before_name_html"] = $db->f("before_name_html");
			$pp[$pn]["after_name_html"] = $db->f("after_name_html");
			$pp[$pn]["before_control_html"] = $db->f("before_control_html");
			$pp[$pn]["after_control_html"] = $db->f("after_control_html");
			$pp[$pn]["validation_regexp"] = $db->f("validation_regexp");
			$pp[$pn]["regexp_error"] = $db->f("regexp_error");
			$pp[$pn]["options_values_sql"] = $db->f("options_values_sql");

			$pn++;
		} while ($db->next_record());
	}

	$yes_no_messages = 
		array( 
			array(1, YES_MSG),
			array(0, NO_MSG)
			);

	// prepare parameters for record
	$r = new VA_Record($table_prefix . "users_ps_details");
	$r->return_page = $user_psd_list;
	$r->add_where("psd_id", INTEGER);
	//$r->change_property("order_id", USE_IN_SELECT, true);
	//$r->change_property("order_id", USE_IN_UPDATE, false);
	$r->add_where("user_id", INTEGER);
	$r->change_property("user_id", USE_IN_INSERT, true);
	$r->change_property("user_id", USE_IN_WHERE, false);
	$r->add_textbox("payment_id", INTEGER);
	$r->change_property("payment_id", USE_IN_INSERT, true);
	$r->change_property("payment_id", USE_IN_UPDATE, false);
	$r->add_checkbox("is_active", INTEGER);
	$r->change_property("is_active", DEFAULT_VALUE, 1);
	$r->add_checkbox("is_default", INTEGER);
	$r->change_property("is_default", DEFAULT_VALUE, 0);
	$r->add_textbox("date_added", DATETIME);
	$r->change_property("date_added", USE_IN_UPDATE, false);
	$r->add_textbox("admin_id_added_by", INTEGER);
	$r->change_property("admin_id_added_by", USE_IN_UPDATE, false);
	$r->add_textbox("date_modified", DATETIME);
	$r->change_property("date_modified", USE_IN_INSERT, false);
	$r->add_textbox("admin_id_modified_by", INTEGER);
	$r->change_property("admin_id_modified_by", USE_IN_INSERT, false);

	// payment predefined fields
	// prepare array for credit card
	$current_date = va_time();
	$cc_start_years = get_db_values("SELECT start_year AS year_value, start_year AS year_description FROM " . $table_prefix . "cc_start_years", array(array("", YEAR_MSG)));
	if (sizeof($cc_start_years) < 2) {
		$cc_start_years = array(array("", YEAR_MSG));
		for($y = 7; $y >= 0; $y--) {
			$cc_start_years[] = array($current_date[YEAR] - $y, $current_date[YEAR] - $y);
		}
	}
	$cc_expiry_years = get_db_values("SELECT expiry_year AS year_value, expiry_year AS year_description FROM " . $table_prefix . "cc_expiry_years", array(array("", YEAR_MSG)));
	if (sizeof($cc_expiry_years) < 2) {
		$cc_expiry_years = array(array("", YEAR_MSG));
		for($y = 0; $y <= 7; $y++) {
			$cc_expiry_years[] = array($current_date[YEAR] + $y, $current_date[YEAR] + $y);
		}
	}
	$cc_months = array_merge (array(array("", MONTH_MSG)), $months);

	$section_name = PAYMENT_DETAILS_MSG;
	$r->add_textbox("cc_name", TEXT, CC_NAME_FIELD);
	$r->add_textbox("cc_first_name", TEXT, CC_FIRST_NAME_FIELD);
	$r->add_textbox("cc_last_name", TEXT, CC_LAST_NAME_FIELD);
	$r->add_textbox("cc_number", TEXT, CC_NUMBER_FIELD);
	$r->parameters["cc_number"][MIN_LENGTH] = 10;
	if ($cc_number_security != 2){
		$r->change_property("cc_number", SHOW, false);
		$r->change_property("cc_number", REQUIRED, false);
		$r->change_property("cc_number", USE_IN_UPDATE, false);
	} else {
		$r->change_property("cc_number", AFTER_VALIDATE, "validate_cc_number");
	}
	// separate year and month fields first and only then 
	$r->add_select("cc_start_year", INTEGER, $cc_start_years);
	$r->add_select("cc_start_month", INTEGER, $cc_months);
	$r->change_property("cc_start_year", USE_IN_SELECT, false);
	$r->change_property("cc_start_year", USE_IN_INSERT, false);
	$r->change_property("cc_start_year", USE_IN_UPDATE, false);
	$r->change_property("cc_start_month", USE_IN_SELECT, false);
	$r->change_property("cc_start_month", USE_IN_INSERT, false);
	$r->change_property("cc_start_month", USE_IN_UPDATE, false);
	$r->add_textbox("cc_start_date", DATETIME, CC_START_DATE_FIELD);
	$r->change_property("cc_start_date", VALUE_MASK, array("MM", " / ", "YYYY"));
	$r->add_select("cc_expiry_year", INTEGER, $cc_expiry_years);
	$r->add_select("cc_expiry_month", INTEGER, $cc_months);
	$r->change_property("cc_expiry_year", USE_IN_SELECT, false);
	$r->change_property("cc_expiry_year", USE_IN_INSERT, false);
	$r->change_property("cc_expiry_year", USE_IN_UPDATE, false);
	$r->change_property("cc_expiry_month", USE_IN_SELECT, false);
	$r->change_property("cc_expiry_month", USE_IN_INSERT, false);
	$r->change_property("cc_expiry_month", USE_IN_UPDATE, false);
	$r->add_textbox("cc_expiry_date", DATETIME, CC_EXPIRY_DATE_FIELD);
	$r->change_property("cc_expiry_date", VALUE_MASK, array("MM", " / ", "YYYY"));

	$credit_cards = get_db_values("SELECT credit_card_id, credit_card_name FROM " . $table_prefix . "credit_cards", array(array("", PLEASE_CHOOSE_MSG)));
	$r->add_select("cc_type", INTEGER, $credit_cards, CC_TYPE_FIELD);
	$issue_numbers = get_db_values("SELECT issue_number AS issue_value, issue_number AS issue_description FROM " . $table_prefix . "issue_numbers", array(array("", NOT_AVAILABLE_MSG)));
	$r->add_select("cc_issue_number", INTEGER, $issue_numbers, CC_ISSUE_NUMBER_FIELD);
	$r->add_textbox("cc_security_code", TEXT, CC_SECURITY_CODE_FIELD);
	if ($cc_code_security != 2){
		$r->change_property("cc_security_code", SHOW, false);
		$r->change_property("cc_security_code", REQUIRED, false);
		$r->change_property("cc_security_code", USE_IN_UPDATE, false);
	}
	$r->add_hidden("pay_without_cc", TEXT);
	// end of payment predefined fields

	// prepare custom parameters for record
	foreach ($pp as $id => $pp_row) {
		$control_type = $pp_row["control_type"];
		$param_name = "pp_" . $pp_row["property_id"];
		$param_title = $pp_row["property_name"];

		if ($control_type == "CHECKBOXLIST") {
			$r->add_checkboxlist($param_name, TEXT, "", $param_title);
		} elseif ($control_type == "RADIOBUTTON") {
			$r->add_radio($param_name, TEXT, "", $param_title);
		} elseif ($control_type == "LISTBOX") {
			$r->add_select($param_name, TEXT, "", $param_title);
		} else {
			$r->add_textbox($param_name, TEXT, $param_title);
		}
		if ($control_type == "CHECKBOXLIST" || $control_type == "RADIOBUTTON" || $control_type == "LISTBOX") {
			if ($pp_row["options_values_sql"]) {
				$sql = $pp_row["options_values_sql"];
			} else {
				$sql  = " SELECT property_value_id, property_value FROM " . $table_prefix . "order_custom_values ";
				$sql .= " WHERE property_id=" . $db->tosql($pp_row["property_id"], INTEGER) . " AND hide_value=0";
				$sql .= " ORDER BY property_value_id ";
			}
			$r->change_property($param_name, VALUES_LIST, get_db_values($sql, ""));
		}
		if ($pp_row["required"] == 1 && $control_type != "LABEL") {
			$r->change_property($param_name, REQUIRED, true);
		}
		if ($pp_row["validation_regexp"]) {
			$r->change_property($param_name, REGEXP_MASK, $pp_row["validation_regexp"]);
			if ($pp_row["regexp_error"]) {
				$r->change_property($param_name, REGEXP_ERROR, $pp_row["regexp_error"]);
			}
		}
		$r->change_property($param_name, USE_IN_SELECT, false);
		$r->change_property($param_name, USE_IN_INSERT, false);
		$r->change_property($param_name, USE_IN_UPDATE, false);
	}

	// set events for record
	$r->events[BEFORE_INSERT] = "prepare_ps_insert";
	$r->events[AFTER_INSERT] = "insert_ps_properties";
	$r->events[BEFORE_UPDATE] = "prepare_ps_data";
	$r->events[AFTER_UPDATE] = "update_ps_properties";
	$r->events[BEFORE_DELETE] = "remove_ps_properties";
	$r->events[AFTER_REQUEST] = "set_ps_fields";
	$r->events[AFTER_SELECT] = "get_additional_data";
	$r->events[BEFORE_SHOW] = "hide_custom_fields";
	$r->events[AFTER_SHOW] = "parse_fields";

	$r->operations[INSERT_ALLOWED] = true;
	$r->operations[UPDATE_ALLOWED] = true;
	$r->operations[DELETE_ALLOWED] = true;
	$r->operations[SELECT_ALLOWED] = true;

	// check settings what payment parameters to show
	for ($i = 0; $i < sizeof($cc_parameters); $i++) {            
		$show_param = "show_" . $cc_parameters[$i];
		if (isset($cc_info[$show_param]) && $cc_info[$show_param] == 1) {
			if ($cc_info[$cc_parameters[$i] . "_required"] == 1) {
				$r->parameters[$cc_parameters[$i]][REQUIRED] = true;
			}
		} else {
			$r->parameters[$cc_parameters[$i]][SHOW] = false;
		}
	}

	$r->process();

	$block_parsed = true;

	function hide_custom_fields()
	{
		global $r, $pp;

		foreach ($pp as $id => $pp_row) {
			$param_name = "pp_" . $pp_row["property_id"];
			$r->change_property($param_name, SHOW, false);
		}
	}

	function get_additional_data()
	{
		global $r, $pp, $db, $table_prefix, $operation, $cc_number_security, $cc_code_security;
  
		$psd_id = $r->get_value("psd_id");
		// set values for expiry and start dates
		$start_date = $r->get_value("cc_start_date");
		if (is_array($start_date)) {
			$r->set_value("cc_start_year", $start_date[YEAR]);
			$r->set_value("cc_start_month", $start_date[MONTH]);
		}
		$expiry_date = $r->get_value("cc_expiry_date");
		if (is_array($expiry_date)) {
			$r->set_value("cc_expiry_year", $expiry_date[YEAR]);
			$r->set_value("cc_expiry_month", $expiry_date[MONTH]);
		}
  
		$users_ps_properties = array();
		$sql  = " SELECT op.property_id, op.property_type, op.property_name, op.property_value, op.property_value_id, ";
		$sql .= " ocp.control_type ";
		$sql .= " FROM (" . $table_prefix . "users_ps_properties op ";
		$sql .= " INNER JOIN " . $table_prefix . "order_custom_properties ocp ON op.property_id=ocp.property_id)";
		$sql .= " WHERE op.psd_id=" . $db->tosql($psd_id, INTEGER);
		$sql .= " ORDER BY op.property_order, op.property_id ";
		$db->query($sql);
		while ($db->next_record()) {
			$property_id   = $db->f("property_id");
			$property_type = $db->f("property_type");
			$property_name = $db->f("property_name");
			$property_value = $db->f("property_value");
			$property_value_id = $db->f("property_value_id");
			$control_type = $db->f("control_type");
			$param_name = "pp_" . $property_id;

			if ($r->parameter_exists($param_name)) {
				if (($control_type == "CHECKBOXLIST" ||  $control_type == "RADIOBUTTON" || $control_type == "LISTBOX") 
					&& !$property_value_id) {
					$property_value = explode(";", $property_value);
				} else {
					$property_value = array($property_value);
				}
				for ($op = 0; $op < sizeof($property_value); $op++) {
					$option_value = $property_value[$op];
					$users_ps_properties[$property_id][] = array(
						"type" => $property_type, "name" => $property_name, 
						"value" => $option_value, "value_id" => $property_value_id, "control" => $control_type
					);
				}
			}
		}

		foreach ($users_ps_properties as $property_id => $property_values) 
		{
			$param_name = "pp_" . $property_id;
			foreach ($property_values as $option_id => $option_data) {
				$control_type = $option_data["control"];
				$option_value = $option_data["value"];
				$option_value_id = $option_data["value_id"];
				// check value from the description
				if (($control_type == "CHECKBOXLIST" ||  $control_type == "RADIOBUTTON" || $control_type == "LISTBOX") && !$option_value_id) {
					$sql  = " SELECT property_value_id FROM " . $table_prefix . "order_custom_values ";
					$sql .= " WHERE property_value=" . $db->tosql(trim($option_value), TEXT);
					$db->query($sql);
					if ($db->next_record()) {
						$option_value_id = $db->f("property_value_id");
					}
				}
				if ($option_value_id) {
					$r->set_value($param_name, $option_value_id);
				} else {
					$r->set_value($param_name, $option_value);
				}
			}
		}

		if (!strlen($operation)) {
			$cc_number = $r->get_value("cc_number");
			if (!preg_match("/^[\d\s\*\-]+$/", $cc_number)) {
				$cc_number = va_decrypt($cc_number);
			}
			$r->set_value("cc_number", format_cc_number($cc_number));
			$cc_security_code = $r->get_value("cc_security_code");
			if (!preg_match("/^[\d]+$/", $cc_security_code)) {
				$cc_security_code = va_decrypt($cc_security_code);
			}
			$r->set_value("cc_security_code", $cc_security_code);

			// clear security data
			$r->set_value("cc_number", "");
			$r->set_value("cc_security_code", "");
		}
	}

	function remove_ps_properties()
	{
		global $r, $db, $table_prefix;
	
		$psd_id = $r->get_value("psd_id");
		$user_id = get_session("session_user_id");
	
		// delete all properties before insert
		$sql  = " DELETE FROM " . $table_prefix . "users_ps_properties ";
		$sql .= " WHERE psd_id=" . $db->tosql($psd_id, INTEGER);
		$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
		$db->query($sql);
	}

	function update_ps_properties()
	{
		global $r, $pp, $db, $table_prefix;
	
		$psd_id = $r->get_value("psd_id");
		$payment_id = $r->get_value("payment_id");
		$user_id = get_session("session_user_id");

		// check is_default option
		$is_default = $r->get_value("is_default");
		if ($is_default) {
			$sql  = " UPDATE " . $table_prefix . "users_ps_details ";
			$sql .= " SET is_default=0 ";
			$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
			$sql .= " AND payment_id=" . $db->tosql($payment_id, INTEGER);
			$sql .= " AND psd_id<>" . $db->tosql($psd_id, INTEGER);
			$db->query($sql);
		}
	
		// always delete all properties and then insert them to database
		$sql  = " DELETE FROM " . $table_prefix . "users_ps_properties ";
		$sql .= " WHERE psd_id=" . $db->tosql($psd_id, INTEGER);
		$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
		$db->query($sql);

		foreach ($pp as $id => $data) {
			$property_id =$data["property_id"];
			$property_name =$data["property_name"];
			$property_type =$data["section_id"];
			$param_name = "pp_" . $property_id;
			$values = array();
			$control_type = $r->get_property_value($param_name, CONTROL_TYPE);
			if ($control_type == CHECKBOXLIST) {
				$values = $r->get_value($param_name);
			} else {
				$values[] = $r->get_value($param_name);
			}
			if (is_array($values)) {
				for ($i = 0; $i < sizeof($values); $i++) {
					$property_value_id = ""; $property_value = ""; 
					if ($control_type == CHECKBOXLIST || $control_type == RADIOBUTTON || $control_type == LISTBOX) {
						$property_value_id = $values[$i];
						$sql  = " SELECT property_value FROM " . $table_prefix . "order_custom_values ";
						$sql .= " WHERE property_id = ".$db->tosql($property_id,INTEGER,true,false)." ";
						$sql .= " AND property_value_id = ".$db->tosql($property_value_id, INTEGER,true,false);
						$db->query($sql);
						if ($db->next_record()){
							$property_value = $db->f("property_value");
						}
					} else {
						$property_value = $values[$i];
					}

					if (strlen($property_value) || $property_value_id) {
						$sql  = " INSERT INTO " . $table_prefix . "users_ps_properties ";
						$sql .= " (psd_id, user_id, property_id, property_name, property_type, property_value_id, property_value) VALUES (";
						$sql .= $db->tosql($psd_id, INTEGER) . ", ";
						$sql .= $db->tosql($user_id, INTEGER) . ", ";
						$sql .= $db->tosql($property_id, INTEGER) . ", ";
						$sql .= $db->tosql($property_name, TEXT) . ", ";
						$sql .= $db->tosql($property_type, INTEGER) . ", ";
						$sql .= $db->tosql($property_value_id, INTEGER) . ", ";
						$sql .= $db->tosql($property_value, TEXT) . ") ";
						$db->query($sql);
					}
				}
			}
		}
	} 

	function set_ps_fields()
	{
		global $r, $db, $parameters, $payment_id, $table_prefix;

		$current_date = va_time();
		$order_ip = get_ip();

		$r->set_value("admin_id_added_by", get_session("session_admin_id"));
		$r->set_value("date_added", va_time());
		$r->set_value("admin_id_modified_by", get_session("session_admin_id"));
		$r->set_value("date_modified", va_time());
		$r->set_value("user_id", get_session("session_user_id"));
		$r->set_value("payment_id", $payment_id);


		if ($r->parameter_exists("cc_start_date")) {
			$cc_start_year   = get_param("cc_start_year");
			$cc_start_month  = get_param("cc_start_month");
			if (strlen($cc_start_year) && strlen($cc_start_month)) {
				$r->set_value("cc_start_date", array($cc_start_year, $cc_start_month, 1, 0, 0, 0));
			}
		}
		if ($r->parameter_exists("cc_expiry_date")) {
			$cc_expiry_year  = get_param("cc_expiry_year");
			$cc_expiry_month = get_param("cc_expiry_month");
			if (strlen($cc_expiry_year) && strlen($cc_expiry_month)) {
				$r->set_value("cc_expiry_date", array($cc_expiry_year, $cc_expiry_month, 1, 0, 0, 0));
			}
		}
	}

	function prepare_ps_insert()
	{
		global $r, $db, $table_prefix;
		if ($db->DBType == "postgre") {
			$sql = " SELECT NEXTVAL('seq_" . $table_prefix . "users_ps_details') ";
			$new_psd_id = get_db_value($sql);
			$r->set_value("psd_id", $new_psd_id);
			$r->change_property("psd_id", USE_IN_INSERT, true);
		}
		prepare_ps_data();
	}

	function insert_ps_properties()
	{
		global $r, $db, $table_prefix;
		if ($db->DBType == "mysql") {
			$sql = " SELECT LAST_INSERT_ID() ";
			$new_psd_id = get_db_value($sql);
		} else if ($db->DBType  == "access") {
			$sql = " SELECT @@IDENTITY ";
			$new_psd_id = get_db_value($sql);
		} else if ($db->DBType  == "db2") {
			$new_psd_id = get_db_value(" SELECT PREVVAL FOR seq_" . $table_prefix . "users_ps_details FROM " . $table_prefix . "users_ps_details");
		}
		$r->set_value("psd_id", $new_psd_id);
		update_ps_properties();
	}

	function prepare_ps_data()
	{
		global $r, $cc_number_security,	$cc_code_security;
	
		$r->change_property("user_id", USE_IN_WHERE, true); // use user_id as additional condition

		// payment fields update
		if ($r->parameter_exists("cc_number")) {
			$cc_number = $r->get_value("cc_number");
			if (strlen($cc_number)) {
				if ($cc_number_security > 0) {
					$r->set_value("cc_number", va_encrypt(clean_cc_number($cc_number)));
				} else {
					$r->set_value("cc_number", "");
				}
			}
		}
		if ($r->parameter_exists("cc_security_code")) {
			$cc_security_code = $r->get_value("cc_security_code");
			if (strlen($cc_security_code)) {
				if ($cc_code_security > 0) {
					$r->set_value("cc_security_code", va_encrypt($cc_security_code));
				} else {
					$r->set_value("cc_security_code", "");
				}
			}
		}

		if ($r->parameter_exists("cc_start_date")) {
			$cc_start_year   = $r->get_value("cc_start_year");
			$cc_start_month  = $r->get_value("cc_start_month");
			if (strlen($cc_start_year) && strlen($cc_start_month)) {
				$r->set_value("cc_start_date", array($cc_start_year, $cc_start_month, 1, 0, 0, 0));
			}
		}
  
		if ($r->parameter_exists("cc_expiry_datec_security_code")) {
			$cc_expiry_year  = $r->get_value("cc_expiry_year");
			$cc_expiry_month = $r->get_value("cc_expiry_month");
			if (strlen($cc_expiry_year) && strlen($cc_expiry_month)) {
				$r->set_value("cc_expiry_date", array($cc_expiry_year, $cc_expiry_month, 1, 0, 0, 0));
			}
		}
	}

	function parse_fields()
	{
		global $t, $r, $db, $table_prefix, $section_name, $parameters, $cc_parameters, $pp;

		$t->set_var("section_name", $section_name);
		$t->parse_to("form_section", "form_sections");

		$section_properties = 0;
		$psd_id = $r->get_value("psd_id");
		$operation = get_param("operation");
  
		// show active and default options first
		$t->copy_var("is_active_block", "form_sections");
		$t->set_var("is_active_block", "");
		$t->copy_var("is_default_block", "form_sections");
		$t->set_var("is_default_block", "");

		for ($i = 0; $i < sizeof($cc_parameters); $i++) {                                    
			$param_name = $cc_parameters[$i];
			if ($r->get_property_value($param_name, SHOW)) {
				$section_properties++;
				$t->copy_var($param_name . "_block", "form_sections");
				$t->set_var($param_name . "_block", "");
			}
		}
		$t->parse_to("payment", "form_section");
		
		// show custom options 
		$properties_ids = "";
		if (sizeof($pp) > 0) 
		{
			for ($pn = 0; $pn < sizeof($pp); $pn++) {
				$section_properties++;
				$property_id = $pp[$pn]["property_id"];
				$param_name = "pp_" . $property_id;
				$property_order  = $pp[$pn]["property_order"];
				$property_name_initial = $pp[$pn]["property_name"];
				$property_name = get_translation($property_name_initial);
				$property_description = $pp[$pn]["property_description"];
				$default_value = $pp[$pn]["default_value"];
				$property_style = $pp[$pn]["property_style"];
				$control_type = $pp[$pn]["control_type"];
				$control_style = $pp[$pn]["control_style"];
				$property_required = $pp[$pn]["required"];
				$before_name_html = $pp[$pn]["before_name_html"];
				$after_name_html = $pp[$pn]["after_name_html"];
				$before_control_html = $pp[$pn]["before_control_html"];
				$after_control_html = $pp[$pn]["after_control_html"];
				$onchange_code = $pp[$pn]["onchange_code"];
				$onclick_code = $pp[$pn]["onclick_code"];
				$control_code = $pp[$pn]["control_code"];
				$validation_regexp = $pp[$pn]["validation_regexp"];
				$regexp_error = $pp[$pn]["regexp_error"];
				$options_values_sql = $pp[$pn]["options_values_sql"];
        
				if (strlen($properties_ids)) { $properties_ids .= ","; }
				$properties_ids .= $property_id;
        
				$property_control  = "";
				$property_control .= "<input type=\"hidden\" name=\"pp_name_" . $property_id . "\"";
				$property_control .= " value=\"" . strip_tags($property_name) . "\">";
				$property_control .= "<input type=\"hidden\" name=\"pp_required_" . $property_id . "\"";
				$property_control .= " value=\"" . intval($property_required) . "\">";
				$property_control .= "<input type=\"hidden\" name=\"pp_control_" . $property_id . "\"";
				$property_control .= " value=\"" . strtoupper($control_type) . "\">";
				
				if ($options_values_sql) {
					$sql = $options_values_sql;
				} else {
					$sql  = " SELECT * FROM " . $table_prefix . "order_custom_values ";
					$sql .= " WHERE property_id=" . $property_id . " AND hide_value=0";
					$sql .= " ORDER BY property_value_id ";
				}
				if (strtoupper($control_type) == "LISTBOX") 
				{
					$selected_value = $r->get_value($param_name);
					$properties_values = "<option value=\"\">" . SELECT_MSG . " " . $property_name . "</option>" . $eol;
					$db->query($sql);
					while ($db->next_record())
					{
						if ($options_values_sql) {
							$property_value_id = $db->f(0);
							$property_value = get_translation($db->f(1));
						} else {
							$property_value_id = $db->f("property_value_id");
							$property_value = get_translation($db->f("property_value"));
						} 
						$is_default_value = $db->f("is_default_value");
						$property_selected  = "";
						
						if ($selected_value == $property_value_id) {
							$property_selected  = "selected ";
						}
        
						$properties_values .= "<option " . $property_selected . "value=\"" . htmlspecialchars($property_value_id) . "\">";
						$properties_values .= htmlspecialchars($property_value);
						$properties_values .= "</option>" . $eol;
					}
					$property_control .= $before_control_html;
					$property_control .= "<select name=\"pp_" . $property_id . "\" ";
					if ($onchange_code) {	$property_control .= " onchange=\"" . $onchange_code. "\""; }
					if ($onclick_code) {	$property_control .= " onclick=\"" . $onclick_code . "\""; }
					if ($control_code) {	$property_control .= " " . $control_code . " "; }
					if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
					$property_control .= ">" . $properties_values . "</select>";
					$property_control .= $after_control_html;						
				} 
				elseif (strtoupper($control_type) == "RADIOBUTTON" || strtoupper($control_type) == "CHECKBOXLIST") 
				{
					$is_radio = (strtoupper($control_type) == "RADIOBUTTON");
        
					$selected_value = array();
					if ($is_radio) {
						$selected_value[] = $r->get_value($param_name);
					} else {
						$selected_value = $r->get_value($param_name);
					}
        
					$input_type = $is_radio ? "radio" : "checkbox";
					$property_control .= "<span";
					if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
					$property_control .= ">";
					$value_number = 0;
					$db->query($sql);
					while ($db->next_record())
					{
						$value_number++;
						if ($options_values_sql) {
							$property_value_id = $db->f(0);
							$property_value = get_translation($db->f(1));
						} else {
							$property_value_id = $db->f("property_value_id");
							$property_value = get_translation($db->f("property_value"));
						} 
						$is_default_value = $db->f("is_default_value");
						$property_checked = "";
						$property_control .= $before_control_html;
						if (is_array($selected_value) && in_array($property_value_id, $selected_value)) {
							$property_checked = "checked ";
						}
  
						$control_name = ($is_radio) ? ("pp_".$property_id) : ("pp_".$property_id."_".$value_number);
						$property_control .= "<input type=\"" . $input_type . "\" name=\"" . $control_name . "\" ". $property_checked;
						$property_control .= "value=\"" . htmlspecialchars($property_value_id) . "\" ";
						if ($onclick_code) {	
							$control_onclick_code = str_replace("{option_value}", $property_value, $onclick_code);
							$property_control .= " onclick=\"" . $control_onclick_code. "\""; 
						}
						if ($onchange_code) {	$property_control .= " onchange=\"" . $onchange_code . "\""; }
						if ($control_code) {	$property_control .= " " . $control_code . " "; }
						$property_control .= ">";
						$property_control .= $property_value;
						$property_control .= $after_control_html;
					}
					$property_control .= "</span>";
					if (!$is_radio) {
						$property_control .= "<input type=\"hidden\" name=\"pp_".$property_id."\" value=\"".$value_number."\">";
					}
				} 
				elseif (strtoupper($control_type) == "TEXTBOX") 
				{
					if (strlen($operation) || $psd_id) {
						$control_value = $r->get_value($param_name);
					} else {
						$control_value = $default_value;
					}
					$property_control .= $before_control_html;
					$property_control .= "<input class=\"field\" type=\"text\" name=\"pp_" . $property_id . "\"";
					if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
					if ($onclick_code) {	$property_control .= " onclick=\"" . $onclick_code . "\""; }
					if ($onchange_code) {	$property_control .= " onchange=\"" . $onchange_code . "\""; }
					if ($control_code) {	$property_control .= " " . $control_code . " "; }
					$property_control .= " value=\"". htmlspecialchars($control_value) . "\">";
					$property_control .= $after_control_html;
				} 
				elseif (strtoupper($control_type) == "TEXTAREA") 
				{
					if (strlen($operation) || $psd_id) {
						$control_value = $r->get_value($param_name);
					} else {
						$control_value = $default_value;
					}
					$property_control .= $before_control_html;
					$property_control .= "<textarea  class=\"field\" name=\"pp_" . $property_id . "\"";
					if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
					if ($onclick_code) {	$property_control .= " onclick=\"" . $onclick_code . "\""; }
					if ($onchange_code) {	$property_control .= " onchange=\"" . $onchange_code . "\""; }
					if ($control_code) {	$property_control .= " " . $control_code . " "; }
					$property_control .= ">". htmlspecialchars($control_value) ."</textarea>";
					$property_control .= $after_control_html;
				} 
				else 
				{
					$property_control .= $before_control_html;
					if ($property_required) {
						if (!strlen($property_description)){
							$property_description = $default_value;
						}
						$property_control .= "<input type=\"hidden\" name=\"pp_" . $property_id . "\" value=\"" . htmlspecialchars($property_description) . "\">";
					}
					$property_control .= "<span";
					if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
					if ($onclick_code) {	$property_control .= " onclick=\"" . $onclick_code . "\""; }
					if ($onchange_code) {	$property_control .= " onchange=\"" . $onchange_code . "\""; }
					if ($control_code) {	$property_control .= " " . $control_code . " "; }
					$property_control .= ">" . get_translation($default_value) . "</span>";
					$property_control .= $after_control_html;
  
					$property_control .= "<input type=\"hidden\" name=\"pp_".$property_id."\" value='".$default_value."'>";
  
					$custom_options[$property_id] = array($property_order, $property_name_initial, $default_value);
				}
        
				$t->set_var("property_id", $property_id);
				$t->set_var("property_name", $before_name_html . $property_name . $after_name_html);
				$t->set_var("property_style", $property_style);
				$t->set_var("property_control", $property_control);
				if ($property_required) {
					$t->set_var("property_required", "*");
				} else {
					$t->set_var("property_required", "");
				}
        
				$t->parse("order_properties", true);
				
			}
    
			$t->set_var("properties_ids", $properties_ids);
		}
		// end custom options
  
		if ($section_properties) {
			$t->parse("form_sections", true);
		}
	}

	function validate_cc_number()
	{
		global $r;
		$is_valid = $r->get_property_value("cc_number", IS_VALID);
		$cc_number = $r->get_value("cc_number");
		if ($is_valid && $cc_number) {
			if (!check_cc_number($cc_number)) {
				$r->change_property("cc_number", IS_VALID, false);
				$r->change_property("cc_number", ERROR_DESC, CC_NUMBER_ERROR_MSG);
			}
		}
	}

?> 