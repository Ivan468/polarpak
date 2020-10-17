<?php

	include_once("./includes/record.php");
	include_once("./includes/tabs_functions.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/profiles_messages.php");

	$default_title = "";

	check_user_security("profiles");

	$user_id = get_session("session_user_id");
	$profile_id = get_param("profile_id");

	if (strlen($profile_id)) {
		// check if selected profiles exists for customer
		$sql  = " SELECT profile_id FROM " . $table_prefix . "profiles ";
		$sql .= " WHERE profile_id=" . $db->tosql($profile_id, INTEGER);
		$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
		$db->query($sql);
		if (!$db->next_record()) {
			header("Location: profiles_user_list.php");
			exit;
		}
	}

	$eol = get_eol();

	$type_settings = array();
	$sql = "SELECT setting_name,setting_value FROM " . $table_prefix . "user_types_settings WHERE type_id=" . $db->tosql(get_session("session_user_type_id"), INTEGER);
	$db->query($sql);
	while($db->next_record()) {
		$type_settings[$db->f("setting_name")] = $db->f("setting_value");
	}

	$profiles_settings = get_settings("profiles_settings");
	$profiles_limit = get_setting_value($type_settings, "profiles_limit", 1);
	$show_terms = get_setting_value($profiles_settings, "show_terms", 0);

	$date_format_msg = str_replace("{date_format}", join("", $date_edit_format), DATE_FORMAT_MSG);

	$html_template = get_setting_value($block, "html_template", "block_profiles_user_edit.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("site_url",        $settings["site_url"]);
	$t->set_var("user_home_href",  get_custom_friendly_url("user_home.php"));
	$t->set_var("profiles_user_list_href",   get_custom_friendly_url("profiles_user_list.php"));
	$t->set_var("profiles_user_edit_href",    get_custom_friendly_url("profiles_user_edit.php"));
	$t->set_var("user_upload_href",get_custom_friendly_url("user_upload.php"));
	$t->set_var("user_select_href",get_custom_friendly_url("user_select.php"));
	$t->set_var("profiles_terms_href", get_custom_friendly_url("profiles_terms.php"));
	$t->set_var("photo_upload_href", get_custom_friendly_url("photo_upload.php"));
	$t->set_var("photos_manager_href", get_custom_friendly_url("photos_manager.php"));

	$t->set_var("date_edit_format",join("", $date_edit_format));
	$t->set_var("date_format_msg", $date_format_msg);

	$states     = get_db_values("SELECT state_id,state_name FROM " . $table_prefix . "states WHERE show_for_user=1 ORDER BY state_name ", array(array("", SELECT_STATE_MSG)));
	$countries  = get_db_values("SELECT country_id,country_name FROM " . $table_prefix . "countries WHERE show_for_user=1 ORDER BY country_order, country_name ", array(array("", SELECT_COUNTRY_MSG)));
	$ethnicities = get_db_values("SELECT ethnicity_id,ethnicity_name FROM " . $table_prefix . "ethnicities ORDER BY sort_order ", array(array("", "")));

	$height_values = array(
		array("", ""),
		array("150", "< 5' 0\" (<= 150 cm)"),
		array("152", "5' 0\" (152 cm)"),
		array("155", "5' 1\" (155 cm)"),
		array("157", "5' 2\" (157 cm)"),
		array("160", "5' 3\" (160 cm)"),
		array("163", "5' 4\" (163 cm)"),
		array("165", "5' 5\" (165 cm)"),
		array("168", "5' 6\" (168 cm)"),
		array("170", "5' 7\" (170 cm)"),
		array("173", "5' 8\" (173 cm)"),
		array("175", "5' 9\" (175 cm)"),
		array("178", "5' 10\" (178 cm)"),
		array("180", "5' 11\" (180 cm)"),
		array("183", "6' 0\" (183 cm)"),
		array("185", "6' 1\" (185 cm)"),
		array("188", "6' 2\" (188 cm)"),
		array("191", "6' 3\" (191 cm)"),
		array("193", "6' 4\" (193 cm)"),
		array("196", "6' 5\" (196 cm)"),
		array("198", "6' 6\" (198 cm)"),
		array("201", "6' 7\" (201 cm)"),
		array("203", "6' 8\" (203 cm)"),
		array("206", "6' 9\" (206 cm)"),
		array("208", "6' 10\" (208 cm)"),
		array("211", "6' 11\" (211 cm)"),
		array("213", "7' 0\" (213 cm)"),
		array("215", "> 7' (> 213 cm)"),
	);

	//prepare weight table
	$weight_values = array(array("", ""));
	$weight_start = 40000; $weight_end = 100000; $weight_step = 500;
	$kg_lb = 453.59237;
	$weight_start = 39916.13; $weight_end = 100000; $weight_step = 453.59237;
	for ($w = $weight_start; $w <= $weight_end; $w = $w + $weight_step) {
		$weight_lb = round($w / $kg_lb, 1);
		$weight_kg = round($w / 1000, 1);
		$weight_desc = $weight_lb."lb (".$weight_kg."kg)";
		$weight_values[] = array(intval($w), $weight_desc);
	}


	$profile_types = get_db_values("SELECT profile_type_id,profile_type_name FROM " . $table_prefix . "profiles_types WHERE show_for_user=1 ORDER BY profile_type_id ", array(array("", "")));
	
	$r = new VA_Record($table_prefix . "profiles");

	// set up html form parameters
	$r->add_where("profile_id", INTEGER);
	$r->change_property("profile_id", USE_IN_INSERT, true);

	$r->add_where("user_id", INTEGER);
	$r->change_property("user_id", USE_IN_INSERT, true);
	$r->add_checkbox("is_shown", INTEGER);
	$r->add_textbox("is_approved", INTEGER);
	$r->add_textbox("photo_id", INTEGER);
	$r->change_property("photo_id", AFTER_REQUEST, "check_photo");
	$r->add_hidden("photos", TEXT);

	$r->add_select("profile_type_id", INTEGER, $profile_types, PROFILE_TYPE_FIELD);
	$r->change_property("profile_type_id", REQUIRED, true);
	$r->add_select("looking_type_id", INTEGER, $profile_types, LOOKING_TYPE_FIELD);
	$r->change_property("looking_type_id", REQUIRED, true);

	$r->add_textbox("profile_name", TEXT, NAME_MSG);
	$r->change_property("profile_name", REQUIRED, true);
	$r->add_select("ethnicity_id", INTEGER, $ethnicities, ETHNICITY_MSG);
	$r->add_select("height", INTEGER, $height_values, HEIGHT_MSG);
	$r->add_select("weight", INTEGER, $weight_values, WEIGHT_MSG);


	// birthday fields
	$months = array_merge (array(array("", "")), $months);
	$r->add_select("birth_month", INTEGER, $months, BIRTH_MONTH_MSG);
	$r->change_property("birth_month", REQUIRED, true);
	$r->add_textbox("birth_day", INTEGER, BIRTH_DAY_MSG);
	$r->change_property("birth_day", REQUIRED, true);
	$r->change_property("birth_day", MIN_VALUE, 1);
	$r->change_property("birth_day", MAX_VALUE, 31);
	$r->add_textbox("birth_year", INTEGER, BIRTH_YEAR_MSG);
	$r->change_property("birth_year", REQUIRED, true);
	$r->change_property("birth_year", MIN_VALUE, 1900);
	$r->change_property("birth_year", MAX_VALUE, date("Y") - 10);
	$r->add_textbox("birth_date", DATETIME, BIRTHDAY_MSG);
	$r->change_property("birth_date", AFTER_VALIDATE, "birth_date_check");

	// location
	$r->add_select("country_id", INTEGER, $countries, COUNTRY_FIELD);
	$r->change_property("country_id", REQUIRED, true);
	$r->add_select("state_id", INTEGER, $states, STATE_FIELD);
	$r->add_textbox("city", TEXT);
	$r->add_textbox("postal_code", ZIP_FIELD);

	// description
	$r->add_textbox("profile_info", TEXT, PERSONAL_INFO_FIELD);
	//$r->change_property("profile_info", REQUIRED, true);
	$r->add_textbox("looking_info", TEXT, LOOKING_INFO_FIELD);
	//$r->change_property("looking_info", REQUIRED, true);

	// internal fields
	$r->add_textbox("date_added",   DATETIME);
	$r->change_property("date_added", USE_IN_UPDATE, false);
	$r->add_textbox("date_updated", DATETIME);
	$r->add_textbox("date_last_visit", DATETIME);

	// terms and conditions
	if ($show_terms) {
		$r->add_checkbox("terms", INTEGER);
		$r->change_property("terms", USE_IN_INSERT, false);
		$r->change_property("terms", USE_IN_UPDATE, false);
		$r->change_property("terms", USE_IN_SELECT, false);
	}


	$r->get_form_values();
	$r->set_value("user_id", get_session("session_user_id"));

	$profile_id = get_param("profile_id");
	$operation = get_param("operation");
	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }
	$return_page = get_custom_friendly_url("profiles_user_list.php");
	$properties = array();
	$features = array();
	$images = array();
	$is_valid = true;

	// check for limits for all new records
	if(!strlen($profile_id) && strlen($profiles_limit)) {
		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "profiles ";
		$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
		$user_profiles = get_db_value($sql);
		if ($user_profiles >= $profiles_limit) {
			$is_valid = false;
			$error_message = str_replace("{profiles_limit}", $profiles_limit, PROFILES_LIMIT_ERROR);
			$r->errors .= $error_message;
		}
	}
	
	if(strlen($operation))
	{
		$tab = "general";
		if($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		}
		else if($operation == "delete" && $profile_id)
		{
			if (!isset($type_settings["delete_profiles"]) || $type_settings["delete_profiles"] != 1) {
				$r->errors = PROFILE_DELETE_ERROR;
			} else {
				// delete all related tables 
				$db->query("DELETE FROM " . $table_prefix . "profiles WHERE profile_id=" . $db->tosql($profile_id, INTEGER));		
		  
				header("Location: " . $return_page);
				exit;
			} 
		} else if ($operation == "save") {
			if ($is_valid) {
				$is_valid = $r->validate();
			}
			if(strlen($profile_id)) {
				if (!isset($type_settings["edit_profiles"]) || $type_settings["edit_profiles"] != 1) {
					$is_valid = false;
					$r->errors = PROFILE_EDIT_ERROR;
				}
			} else {
				if (!isset($type_settings["add_profiles"]) || $type_settings["add_profiles"] != 1) {
					$is_valid = false;
					$r->errors = PROFILE_NEW_ERROR;
				}
			} 

			if ($show_terms) {
				if ($r->get_value("terms") != 1) {
					$is_valid = false;
					$r->errors .= PROFILE_TERMS_ERROR;
				}
			}

			if ($is_valid) {
				// set approve option

				$is_approved = (isset($type_settings["approve_profiles"]) && $type_settings["approve_profiles"] == 1) ? 1 : 0;
				$r->set_value("is_approved", $is_approved);
					  
				if (strlen($profile_id)) {
					$r->set_value("date_updated", va_time());
					$r->set_value("date_last_visit", va_time());
					$record_updated = $r->update_record();
				} else {
					$r->set_value("date_added", va_time());
					$r->set_value("date_last_visit", va_time());
					$r->set_value("date_updated", va_time());

					// check new ID value
					if ($db->DBType == "postgre") {
						$sql = " SELECT NEXTVAL('seq_" . $table_prefix . "profiles ') ";
						$new_profile_id = get_db_value($sql);
						$r->set_value("profile_id", $new_profile_id);
						$r->change_property("profile_id", USE_IN_INSERT, true);
					}
					$record_updated = $r->insert_record();
					if ($db_type == "mysql") {
						$new_profile_id = get_db_value(" SELECT LAST_INSERT_ID() ");
						$r->set_value("profile_id", $new_profile_id);
					} else if ($db_type == "access") {
						$new_profile_id = get_db_value(" SELECT @@IDENTITY ");
						$r->set_value("profile_id", $new_profile_id);
					}
				}

				$photos = $r->get_value("photos");
				$photo_id = $r->get_value("photo_id");
				$profile_id = $r->get_value("profile_id");
				// assign uploaded photos to selected profile
				if ($photos) {
					$sql  = " UPDATE " . $table_prefix . "users_photos ";
					$sql .= " SET key_id=" . $db->tosql($profile_id, INTEGER);
					$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
					$sql .= " AND photo_id IN (" . $db->tosql($photos, INTEGERS_LIST) . ")";
					$db->query($sql);
				} else if ($photo_id) {
					$sql  = " UPDATE " . $table_prefix . "users_photos ";
					$sql .= " SET key_id=" . $db->tosql($profile_id, INTEGER);
					$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
					$sql .= " AND photo_id=" . $db->tosql($photo_id, INTEGER);
					$db->query($sql);
				}



				if ($record_updated) {
					header("Location: " . $return_page);
					exit;
				}
			}
		}
	} else if(strlen($profile_id)) {
		// edit profile
		$r->get_db_values();
	} else {
		// new profile (set default values)
		$r->set_value("is_shown", 1);
	}

	$r->set_form_parameters();
	$photo_id = $r->get_value("photo_id");
	if (strlen($photo_id)) {
		$photo_src = "photo.php?id=".urlencode($photo_id)."&type=large";
		$t->set_var("photo_src", $photo_src);
		$t->parse("profile_photo", false);
	} else {
		$t->sparse("no_profile_photo", false);
	}

	if(strlen($profile_id)) {
		if (isset($type_settings["edit_profiles"]) && $type_settings["edit_profiles"] == 1) {
			$t->set_var("save_button_title", UPDATE_BUTTON);
			$t->global_parse("save_button", false, false, true);
		}
		if (isset($type_settings["delete_profiles"]) && $type_settings["delete_profiles"] == 1) {
			$t->parse("delete", false);	
		}
	} else {
		if (isset($type_settings["add_profiles"]) && $type_settings["add_profiles"] == 1) {
			$t->set_var("save_button_title", ADD_BUTTON);
			$t->global_parse("save_button", false, false, true);
		}
		$t->set_var("delete", "");	
	}

	// set styles for tabs
	$tabs = array(
		"general" => array("title" => GENERAL_TAB, "show" => true), 
		"desc" => array("title" => DESCRIPTION_TAB, "show" => false), 
		"location" => array("title" => LOCATION_TAB, "show" => false), 
		"photos" => array("title" => PHOTOS_TAB, "show" => false), 
	);
	parse_tabs($tabs, $tab);

	$block_parsed = true;

	function birth_date_check()
	{
		global $r;
		$birth_month = $r->get_value("birth_month");
		$birth_day = $r->get_value("birth_day");
		$birth_year = $r->get_value("birth_year");
		if ($birth_month && $birth_day && $birth_year) {
			if (checkdate($birth_month, $birth_day, $birth_year)) {
				$birth_date_ts = mktime(0,0,0, $birth_month, $birth_day, $birth_year);
				$r->set_value("birth_date", $birth_date_ts);
			} else {
				$error_desc = str_replace("{field_name}", BIRTHDAY_MSG, INCORRECT_DATE_MESSAGE);
				$r->change_property("birth_date", IS_VALID, false);
				$r->change_property("birth_date", ERROR_DESC, $error_desc);
			}
		} 
	}

function check_photo()
{
	global $r, $db, $table_prefix;
	$photo_id = $r->get_value("photo_id");
	$user_id = get_session("session_user_id");
	$sql  = " SELECT user_id FROM " . $table_prefix . "users_photos ";
	$sql .= " WHERE photo_id=" . $db->tosql($photo_id, INTEGER);
	$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
	$db->query($sql);
	if (!$db->next_record()) {
		// clear photo value
		$r->set_value("photo_id", "");
	}
}



?>