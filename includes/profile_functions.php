<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  profile_functions.php                                    ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


function get_user_name($params, $type = "full")
{
	if ($type == "first") {
		$name = get_setting_value($params, "first_name");
		if (!strlen($name)) { 
			$name = trim(get_setting_value($params, "name")); 
			$name = preg_replace("/\s.+$/i", "", $name);
		}
		if (!strlen($name)) { $name = trim(get_setting_value($params, "delivery_first_name")); }
		if (!strlen($name)) { 
			$name = trim(get_setting_value($params, "delivery_name"));
			$name = preg_replace("/\s.+$/i", "", $name);
		}
		if (!strlen($name)) { $name = trim(get_setting_value($params, "nickname"));}
		if (!strlen($name)) { 
			$name = trim(get_setting_value($params, "login"));
			$name = preg_replace("/@.+$/i", "", $name);
		}
	} else if ($type == "last") {
		$name = get_setting_value($params, "last_name");
		if (!strlen($name)) { 
			$name = trim(get_setting_value($params, "name")); 
			$name = preg_replace("/^.+\s/i", "", $name);
		}
		if (!strlen($name)) { $name = trim(get_setting_value($params, "delivery_last_name")); }
		if (!strlen($name)) { 
			$name = trim(get_setting_value($params, "delivery_name"));
			$name = preg_replace("/^.+\s$/i", "", $name);
		}
		if (!strlen($name)) { $name = trim(get_setting_value($params, "nickname"));}
		if (!strlen($name)) { 
			$name = trim(get_setting_value($params, "login"));
			$name = preg_replace("/@.+$/i", "", $name);
		}
	} else if ($type == "nick" || $type == "nickname") {
		$name = trim(get_setting_value($params, "nickname"));
		if (!strlen($name)) { 
			$name = trim(get_setting_value($params, "login"));
			$name = preg_replace("/@.+$/i", "", $name);
		}
	} else {
		$name = trim(get_setting_value($params, "first_name")." ".get_setting_value($params, "last_name"));
		if (!strlen($name)) { $name = trim(get_setting_value($params, "name")); }
		if (!strlen($name)) { $name = trim(get_setting_value($params, "delivery_first_name")." ".get_setting_value($params, "delivery_last_name"));}
		if (!strlen($name)) { $name = trim(get_setting_value($params, "delivery_name"));}
		if (!strlen($name)) { $name = trim(get_setting_value($params, "nickname"));}
		if (!strlen($name)) { 
			$name = trim(get_setting_value($params, "login"));
			$name = preg_replace("/@+$/i", "", $name);
		}
	}
	return $name;
}

function get_phone_codes()
{
	global $db, $table_prefix, $settings, $js_settings;

	$phone_codes = array(); 
	$phone_code_select = get_setting_value($settings, "phone_code_select", 1);
	if ($phone_code_select) {
		$phone_codes = array(array("", "")); 
		$va_countries = va_countries(); $js_phone_codes = array(); $js_country_codes = array();
		foreach ($va_countries as $country_id => $country_data) {
			$show_for_user = $country_data["show_for_user"];
			$country_code = $country_data["country_code"];
			$phone_code = trim($country_data["phone_code"]);
			if ($show_for_user && strlen($phone_code)) {
				$phone_codes[] = array($country_code.":".$phone_code, $country_code.": (".$phone_code.") ");
				$js_phone_codes[$country_id] = $phone_code;
				$js_country_codes[$country_id] = $country_code;
			}
		}
		$js_settings["phone_codes"] = $js_phone_codes;
		$js_settings["country_codes"] = $js_country_codes;
	}
	return $phone_codes;
}

function phone_code_checks($phone_codes)
{
	global $settings, $r, $phone_parameters;
	$phone_code_select = get_setting_value($settings, "phone_code_select", 1);
	if ($phone_code_select) {
		$va_countries = va_countries(); 
		foreach ($phone_parameters as $id => $field_name) {
			$show_option = $r->get_property_value($field_name, SHOW);
			if (preg_match("/^delivery/", $field_name)) {
				$country_control = "delivery_country_id";
			} else {
				$country_control = "country_id";
			}
			$country_id = $r->parameter_exists($country_control) ? $r->get_value($country_control) : get_setting_value($settings, "country_id");
			$country_code = "";
			$country_phone_code = "";
			if (isset($va_countries[$country_id])) {
				$country_code = $va_countries[$country_id]["country_code"];
				$country_phone_code = $va_countries[$country_id]["phone_code"];
				$country_pcode_check = preg_replace("/[^\d]/", "", $country_phone_code);
			}
			if ($show_option) {
				$phone_number = $r->get_value($field_name);
				if (preg_match("/^\s*\(([^\)]+)\)\s*(.*)$/", $phone_number, $matches)) {
					$phone_code = $matches[1];
					$pcode_check = preg_replace("/[^\d]/", "", $phone_code);
					$phone_number = $matches[2];
					$code_value = "";
					if ($pcode_check) {
						if ($pcode_check == $country_pcode_check) {
							$code_value = $country_code.":".$country_phone_code;
						} else {
							// check code in other countries
							foreach ($va_countries as $country_id => $country_data) {
								$list_country_code = $country_data["country_code"];
								$list_phone_code = $country_data["phone_code"];
								$list_pcode_check = preg_replace("/[^\d]/", "", $list_phone_code);
								if ($pcode_check == $list_pcode_check) {
									$code_value = $list_country_code.":".$list_phone_code;
									break;
								}
							}
						}
					}
					if (strlen($code_value)) {
						$r->set_value($field_name."_code", $code_value);
						$r->set_value($field_name, $phone_number);
					}
				}
			}
		}
	}
}

function disable_phone_codes()
{
	global $phone_parameters, $r;
	foreach ($phone_parameters as $id => $field_name) {
		$code_field = $field_name."_code";
		if ($r->parameter_exists($code_field)) {
			$r->change_property($code_field, USE_IN_INSERT, false);
			$r->change_property($code_field, USE_IN_UPDATE, false);
			$r->change_property($code_field, USE_IN_SELECT, false);
		}
	}
}

function prepare_phone_codes()
{
	global $phone_parameters, $r;
	foreach ($phone_parameters as $id => $field_name) {
		$code_field = $field_name."_code";
		if ($r->parameter_exists($field_name) && $r->parameter_exists($code_field) 
			&& $r->get_property_value($field_name, SHOW) && !$r->is_empty($field_name)) {
			$r->change_property($code_field, CONTROL_DESC, $r->get_property_value($field_name, CONTROL_DESC)." - ".CODE_MSG);
			$r->change_property($code_field, REQUIRED, true);
		} else if ($r->parameter_exists($code_field)) {
			$r->change_property($code_field, REQUIRED, false);
		}
	}
}

function join_phone_fields()
{
	global $phone_parameters, $r;
	foreach ($phone_parameters as $id => $field_name) {
		$code_field = $field_name."_code";
		if ($r->parameter_exists($code_field) && !$r->is_empty($code_field)) {
			$phone_code = trim($r->get_value($code_field));
			$phone_number = trim($r->get_value($field_name));
			if (strlen($phone_code) && strlen($phone_number)) {
				$phone_code = trim(preg_replace("/[^\+\s\d]/", "", $phone_code));
				$r->set_value($field_name, "(".$phone_code.") ".$phone_number);
			}
		}
	}
}

function va_countries()
{
	global $va_data, $t, $db, $table_prefix;
	if(!isset($va_data)) { $va_data = array(); }
	if(isset($va_data["countries"])) { 
		$countries = $va_data["countries"];
	} else {
		$countries = array();
		$admin_id = get_session("session_admin_id");		
		$sql  = " SELECT * FROM ".$table_prefix."countries ";
		$sql .= " WHERE show_for_user=1 ";
		if ($admin_id) {
			$sql .= " OR show_for_admin=1 ";
		}
		$sql .= " ORDER BY country_order, country_name ";
		$db->query($sql);
		while ($db->next_record()) {
			$country_id = $db->f("country_id");
			$country_name = trim(get_translation($db->f("country_name")));
			$countries[$country_id]=$db->Record;
			$countries[$country_id]["country_name"] = $country_name;
		}
		$va_data["countries"] = $countries;
	}
	return $countries;
}

function va_delivery_countries()
{
	global $va_data, $t, $db, $table_prefix;
	if(!isset($va_data)) { $va_data = array(); }
	if(isset($va_data["delivery_countries"])) { 
		$countries = $va_data["delivery_countries"];
	} else {
		$countries = array();
		$admin_id = get_session("session_admin_id");		
		$sql  = " SELECT * FROM ".$table_prefix."countries ";
		$sql .= " WHERE delivery_for_user=1 ";
		if ($admin_id) {
			$sql .= " OR delivery_for_admin=1 ";
		}
		$sql .= " ORDER BY country_order, country_name ";
		$db->query($sql);
		while ($db->next_record()) {
			$country_id = $db->f("country_id");
			$country_name = trim(get_translation($db->f("country_name")));
			$countries[$country_id]=$db->Record;
			$countries[$country_id]["country_name"] = $country_name;
		}
		$va_data["delivery_countries"] = $countries;
	}
	return $countries;
}

function va_states()
{
	global $va_data, $t, $db, $table_prefix;
	if(!isset($va_data)) { $va_data = array(); }
	if(isset($va_data["states"])) { 
		$states = $va_data["states"];
	} else {
		$states = array();
		$sql = " SELECT * FROM ".$table_prefix."states WHERE show_for_user=1 ORDER BY state_name ";
		$db->query($sql);
		while ($db->next_record()) {
			$state_id = $db->f("state_id");
			$states[$state_id]=$db->Record;
		}
	}
	return $states;
}

function prepare_states(&$r)
{
	global $t, $db, $table_prefix;

	$params = array(
		"personal" => array("prefix" => "", "states" => "", "country_id" => "", "province" => false),
		"del" => array("prefix" => "delivery_", "states" => "", "country_id" => "", "province" => false), 
		"bill" => array("prefix" => "bill_", "states" => "", "country_id" => "", "province" => false), 
		"fc" => array("prefix" => "fast_checkout_", "states" => "", "country_id" => "", "province" => false),
	);

	foreach ($params as $code => $param) {
		$prefix = $param["prefix"];
		// initialize states array
		$params[$code]["states"] = array(array("", SELECT_STATE_MSG));
		// get country data from record
		if ($r->parameter_exists($prefix."country_id")) {
			$params[$code]["country_id"] = $r->get_value($prefix."country_id");
		}
		$params[$code]["province"] = $r->get_property_value($prefix."province", SHOW);
	}
	// prepare state names 
	$state_names = array(); 
	$sql = "SELECT country_id, state_field_name FROM " . $table_prefix . "countries WHERE show_for_user=1 AND state_field_name IS NOT NULL AND state_field_name<>'' ";
	$db->query($sql);
	while ($db->next_record()) {
		$country_id = $db->f("country_id");
		$state_field_name = trim(get_translation($db->f("state_field_name")));
		if ($state_field_name && $state_field_name !== "") {
			$state_names[$country_id] = $state_field_name;
		}
	}
	$state_names_json = json_encode($state_names);
	$t->set_var("state_names_json", $state_names_json);
	$t->sparse("state_names_json_block", false);

	$states = array(); 
	$sql = "SELECT country_id, state_id, state_name FROM " . $table_prefix . "states WHERE show_for_user=1 ORDER BY state_name ";
	$db->query($sql);
	while ($db->next_record()) {
		$country_id = $db->f("country_id");
		$state_id = $db->f("state_id");
		$state_name = get_translation($db->f("state_name"));
		// populates states for controls
		foreach ($params as $code => $param) {
			$param_country_id = $param["country_id"];
			if ($param_country_id == $country_id) {
				$params[$code]["states"][] = array($state_id, $state_name);
			}
		}
		if (!isset($states[$country_id])) { $states[$country_id] = array(); }
		$states[$country_id][$state_id] = $state_name;
	}
	$states_json = json_encode($states);
	$t->set_var("states_json", $states_json);
	$t->sparse("states_json_block", false);

	// set states list for controls
	foreach ($params as $code => $param) {
		$prefix = $param["prefix"];
		$param_states = $param["states"];
		if ($r->parameter_exists($prefix."state_id")) {
			$r->change_property($prefix."state_id", VALUES_LIST, $param_states);
			if ( sizeof($param_states) <= 1) {
				$r->change_property($prefix."state_id", VALIDATION, false);
			}
		}
	}
	
	// set special tags
	foreach ($params as $code => $param) {
		$prefix = $param["prefix"];
		$param_country_id = $param["country_id"];
		$param_states = $param["states"];
		$province_show = $param["province"];
		if ($param_country_id && sizeof($param_states) > 1) {
			$t->set_var($prefix."state_id_control_style", "display: inline;");
			$t->set_var($prefix."state_id_required_style", "display: inline;");
			$t->set_var($prefix."state_id_comments_style", "display: none;");
			$t->set_var($prefix."province_style", "display: none;");
		} else {
			$t->set_var($prefix."state_id_control_style", "display: none;");
			if ($param_country_id) {
				$t->set_var($prefix."state_id_comments", NO_STATES_FOR_COUNTRY_MSG);
			} else {
				$t->set_var($prefix."state_id_comments", SELECT_COUNTRY_FIRST_MSG);
			}
			$t->set_var($prefix."state_id_comments_style", "display: inline;");
			$t->set_var($prefix."state_id_required_style", "display: none;");
			$t->set_var($prefix."province_style", "display: none;");
		}
	}

	return $states;
}

