<?php

	include_once("./includes/record.php");
	include_once("./includes/profile_functions.php");
	include_once("./messages/" . $language_code . "/profiles_messages.php");

	set_script_tag("js/profile.js");

	$default_title = "{search_name} {SEARCH_TITLE}";

	$html_template = get_setting_value($block, "html_template", "block_profiles_search.html"); 
  $t->set_file("block_body", $html_template);

	$t->set_var("profiles_list_href",   "profiles_list.php");
	$t->set_var("search_name",   PROFILES_TITLE);

	$countries = get_db_values("SELECT country_id,country_name FROM " . $table_prefix . "countries WHERE show_for_user=1 ORDER BY country_order, country_name ", array(array("", SELECT_COUNTRY_MSG)));
	$profile_types = get_db_values("SELECT profile_type_id,profile_type_name FROM " . $table_prefix . "profiles_types WHERE show_for_user=1 ORDER BY profile_type_id ", array(array("", "")));

	$profile_default_id = get_db_value("SELECT profile_type_id FROM " . $table_prefix . "profiles_types WHERE is_profile_default=1 ");
	$looking_default_id = get_db_value("SELECT profile_type_id FROM " . $table_prefix . "profiles_types WHERE is_looking_default=1 ");

	$r = new VA_Record($table_prefix . "profiles");
	$r->add_select("profile_type_id", INTEGER, $profile_types, PROFILE_TYPE_FIELD);
	$r->change_property("profile_type_id", REQUIRED, true);
	$r->change_property("profile_type_id", DEFAULT_VALUE, $profile_default_id);
	$r->add_select("looking_type_id", INTEGER, $profile_types, LOOKING_TYPE_FIELD);
	$r->change_property("looking_type_id", REQUIRED, true);
	$r->change_property("looking_type_id", DEFAULT_VALUE, $looking_default_id);
	$r->add_select("country_id", INTEGER, $countries, COUNTRY_FIELD);
	$r->change_property("country_id", REQUIRED, false);
	$r->add_select("state_id", INTEGER, "", STATE_FIELD);
	$r->add_textbox("city", TEXT);
	$r->add_textbox("postal_code", TEXT);

	$operation = get_param("operation");
	$country_id = get_param("country_id");
	
	// get form parameters
	$r->get_form_parameters();

	if ($operation || $country_id) {
		if ($operation) {
			// save selected values in session
			$search_params = array();
			foreach ($r->parameters as $key => $parameter) {
				$control_value = trim($parameter[CONTROL_VALUE]);
				if (strlen($control_value)) {
					$search_params[$key] = $control_value;
				}
			}
			set_session("session_profiles_search", $search_params);
		}

	} else {
		$r->set_default_values();
		// check parameters from session
		$search_params = get_session("session_profiles_search");
		if (is_array($search_params)) {
			foreach ($search_params as $key => $value) {
				$r->set_value($key, $value);
			}
		}
	}
	// get different states lists
	prepare_states($r);
	// set form parameters
	$r->set_parameters();

	$block_parsed = true;

?>