<?php

	$default_title = "{MY_ADDRESSES_MSG}: {EDIT_MSG}";

	check_user_security("user_addresses");

	include_once("./includes/record.php");
	include_once("./includes/parameters.php");
	include_once("./includes/profile_functions.php");

	if (isset($current_page) && $current_page) {
		$address_form_url = $current_page;
	} else {
		$address_form_url = "user_address.php";
	}

	// get user type settings
	$eol = get_eol();
	$phone_code_select = get_setting_value($settings, "phone_code_select", 0);
	$user_info = get_session("session_user_info");
	$user_type_id = get_setting_value($user_info, "user_type_id", "");

	$html_template = get_setting_value($block, "html_template", "block_user_address.html"); 
	$t->set_file("block_body", $html_template);
	$t->set_var("site_url",        $settings["site_url"]);
	$t->set_var("user_home_href",  "user_home.php");
	$t->set_var("user_addresses_href",  "user_addresses.php");
	$t->set_var("user_address_href",   "user_address.php");
	$t->set_var("address_form_url",   htmlspecialchars($address_form_url));

	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", ADDRESS_MSG, CONFIRM_DELETE_MSG));

	$address_id= get_param("address_id");
	$operation = get_param("operation");
	$rp = get_param("rp");
	if (!$rp) { $rp = "user_addresses.php"; }
	$t->set_var("address_id", $address_id);

	$address_types_values = array(
		array("1", PERSONAL_DETAILS_MSG),
		array("2", DELIVERY_DETAILS_MSG),
	);

	// prepare lists for companies, states and countries
	$companies = get_db_values("SELECT company_id,company_name FROM " . $table_prefix . "companies ", array(array("", SELECT_COMPANY_MSG)));
	$states = get_db_values("SELECT state_id,state_name FROM " . $table_prefix . "states WHERE show_for_user=1 ORDER BY state_name ", array(array("", SELECT_STATE_MSG)));
	$countries = get_db_values("SELECT country_id,country_name FROM " . $table_prefix . "countries WHERE show_for_user=1 ORDER BY country_order, country_name ", array(array("", SELECT_COUNTRY_MSG)));
	// get phone codes
	$phone_codes = get_phone_codes();

	$setting_type = "user_profile_" . $user_type_id;
	$user_profile = array();
	$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
	if (isset($site_id)) {
		$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER, true, false) . ") ";
		$sql .= " ORDER BY site_id ASC";
	} else {
		$sql .= " AND site_id=1";
	}
	$db->query($sql);
	while ($db->next_record()) {
		$user_profile[$db->f("setting_name")] = $db->f("setting_value");
	}

	$r = new VA_Record($table_prefix . "users_addresses");
	$r->return_page = $rp;

	// set up html form parameters
	$r->add_where("address_id", INTEGER);
	$r->add_textbox("user_id", INTEGER);
	$r->add_hidden("rp", TEXT);
	$r->add_hidden("select_type", TEXT);
	$r->change_property("user_id", USE_IN_INSERT, true);
	$r->change_property("user_id", USE_IN_UPDATE, false);
	$r->add_textbox("address_type", INTEGER, ADDRESS_TYPE_MSG);
	$r->add_checkboxlist("address_types", INTEGER, $address_types_values, ADDRESS_TYPE_MSG);
	$r->change_property("address_types", REQUIRED, true);
	$r->change_property("address_types", USE_IN_SELECT, false);
	$r->change_property("address_types", USE_IN_INSERT, false);
	$r->change_property("address_types", USE_IN_UPDATE, false);

	// parameters list
	$r->add_textbox("name", TEXT, FULL_NAME_FIELD);
	$r->change_property("name", USE_SQL_NULL, false);
	$r->add_textbox("first_name", TEXT, FIRST_NAME_FIELD);
	$r->change_property("first_name", USE_SQL_NULL, false);
	$r->add_textbox("middle_name", TEXT, MIDDLE_NAME_FIELD);
	$r->change_property("middle_name", USE_SQL_NULL, false);
	$r->add_textbox("last_name", TEXT, LAST_NAME_FIELD);
	$r->change_property("last_name", USE_SQL_NULL, false);
	$r->add_select("company_id", INTEGER, $companies, COMPANY_SELECT_FIELD);
	$r->add_textbox("company_name", TEXT, COMPANY_NAME_FIELD);
	$r->add_textbox("email", TEXT, EMAIL_FIELD);
	$r->change_property("email", USE_SQL_NULL, false);
	$r->change_property("email", REGEXP_MASK, EMAIL_REGEXP);
	$r->add_select("country_id", INTEGER, $countries, COUNTRY_FIELD);
	$r->change_property("country_id", USE_SQL_NULL, false);
	$r->add_textbox("country_code", TEXT);
	$r->change_property("country_code", USE_SQL_NULL, false);
	$r->add_select("state_id", INTEGER, $states, STATE_FIELD);
	$r->change_property("state_id", USE_SQL_NULL, false);
	$r->add_textbox("state_code", TEXT);
	$r->change_property("state_code", USE_SQL_NULL, false);
	$r->add_textbox("address1", TEXT, STREET_FIRST_FIELD);
	$r->add_textbox("address2", TEXT, STREET_SECOND_FIELD);
	$r->add_textbox("address3", TEXT, STREET_THIRD_FIELD);
	$r->add_textbox("city", TEXT, CITY_FIELD);
	$r->add_textbox("province", TEXT, PROVINCE_FIELD);
	$r->add_textbox("postal_code", TEXT, ZIP_FIELD);
	if ($phone_code_select) {
		$r->add_select("phone_code", TEXT, $phone_codes);
		$r->add_select("daytime_phone_code", TEXT, $phone_codes);
		$r->add_select("evening_phone_code", TEXT, $phone_codes);
		$r->add_select("cell_phone_code", TEXT, $phone_codes);
		$r->add_select("fax_code", TEXT, $phone_codes);
		// disable for insert - update operations
		disable_phone_codes();
	}
	$r->add_textbox("phone", TEXT, PHONE_FIELD);
	$r->change_property("phone", REGEXP_MASK, PHONE_REGEXP);
	$r->add_textbox("daytime_phone", TEXT, DAYTIME_PHONE_FIELD);
	$r->change_property("daytime_phone", REGEXP_MASK, PHONE_REGEXP);
	$r->add_textbox("evening_phone", TEXT, EVENING_PHONE_FIELD);
	$r->change_property("evening_phone", REGEXP_MASK, PHONE_REGEXP);
	$r->add_textbox("cell_phone", TEXT, CELL_PHONE_FIELD);
	$r->change_property("cell_phone", REGEXP_MASK, PHONE_REGEXP);
	$r->add_textbox("fax", TEXT, FAX_FIELD);
	$r->change_property("fax", REGEXP_MASK, PHONE_REGEXP);

	$r->set_event(AFTER_REQUEST, "address_states_list");
	$r->set_event(BEFORE_SELECT, "set_additional_where");
	$r->set_event(BEFORE_INSERT, "set_address_params");
	$r->set_event(BEFORE_UPDATE, "set_address_params");
	$r->set_event(AFTER_SELECT, "check_address_phone_codes");
	$r->set_event(AFTER_DEFAULT, "address_default_values");

	$r->set_event(BEFORE_SHOW, "check_address_params");
	$r->set_event(BEFORE_VALIDATE, "check_address_params");

	$r->process();

	$block_parsed = true;


	function set_additional_where()
	{
		global $r;
		$r->change_property("user_id", USE_IN_WHERE, true);
		$r->change_property("user_id", USE_IN_INSERT, true);
		$r->change_property("user_id", USE_IN_UPDATE, false);
		$r->set_value("user_id", get_session("session_user_id"));
	}

	function set_address_params()
	{
		global $r, $db, $table_prefix;
		set_additional_where();
		join_phone_fields();
		// update state and country codes
		if (!$r->is_empty("state_id")) {
			$sql = " SELECT state_code FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($r->get_value("state_id"), INTEGER);
			$r->set_value("state_code", get_db_value($sql));
		}
		if (!$r->is_empty("country_id")) {
			$sql = " SELECT country_code FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($r->get_value("country_id"), INTEGER);
			$r->set_value("country_code", get_db_value($sql));
		}

		if (is_array($r->get_value("address_types"))) {
			$r->set_value("address_type",array_sum($r->get_value("address_types")));
		} else {
			$r->set_value("address_type", "");
		}
	}

	function check_address_phone_codes()
	{
		global $r, $phone_codes;
		// check if phone codes available
		phone_code_checks($phone_codes);
		// get states lists
		$states = prepare_states($r);

		$address_type = $r->get_value("address_type");
		for ($i = 1; $i <= 2; $i = $i*2) {
			if ($i&$address_type) {
				$r->set_value("address_types", $i);
			}
		}
	}

	function address_states_list()
	{
		global $r;
		// get states lists
		$states = prepare_states($r);
	}


	function address_default_values()
	{
		global $r;
		// get states lists
		$states = prepare_states($r);

		$select_type = get_param("select_type");
		if (!$select_type) { $select_type = 2; }
		$r->set_value("select_type", $select_type);
		for ($i = 1; $i <= 2; $i = $i*2) {
			if ($i&$select_type) {
				$r->set_value("address_types", $i);
			}
		}
	}

	function check_address_params()
	{
		global $r, $t, $parameters, $user_profile;

		// check required parameters for address edit
		$address_types = $r->get_value("address_types");

		$address_params = array(); 
		for ($i = 0; $i < sizeof($parameters); $i++)
		{
			$param_name = $parameters[$i];
			$personal_show_name = "show_" . $parameters[$i];
			$personal_required_name = $parameters[$i]."_required";
  
			$delivery_param = "delivery_" . $parameters[$i];
			$delivery_show_name = "show_delivery_" . $parameters[$i];
			$delivery_required_name = "delivery_" . $parameters[$i]."_required";
			if ($param_name == "zip") {
				$param_name = "postal_code"; 
			}
			
			$personal_show = get_setting_value($user_profile, $personal_show_name, 0);
			$delivery_show = get_setting_value($user_profile, $delivery_show_name, 0);
			$personal_required = get_setting_value($user_profile, $personal_required_name, 0);
			$delivery_required = get_setting_value($user_profile, $delivery_required_name, 0);
			$address_params[$param_name] = array(
				"1" => array("show" => $personal_show, "required" => $personal_required),
				"2" => array("show" => $delivery_show, "required" => $delivery_required),
			);
  
			if ($r->parameter_exists($param_name)) {
				$r->change_property($param_name, TRIM, true);
			}
		}

		foreach ($address_params as $param_name => $param_values) {
			$param_show = false; $param_required = false; 
			if (is_array($address_types)) {
				foreach ($address_types as $type_value) { 
					if ($param_values[$type_value]["show"]) {
						$param_show = true;
					}
					if ($param_values[$type_value]["required"]) {
						$param_required = true;
					}
				}
			}
			if ($param_show && $param_required) {
				$r->change_property($param_name, REQUIRED, true);
			}
			if (!$param_show) {
				$t->set_var($param_name."_style", "display: none;");
			}
			if ($param_required) {
				$t->set_var($param_name."_required_style", "display: inline;");
			} else {
				$t->set_var($param_name."_required_style", "display: none;");
			}
		}
  
		$address_json = json_encode($address_params);
		$t->set_var("address_json", $address_json);
	}


?>