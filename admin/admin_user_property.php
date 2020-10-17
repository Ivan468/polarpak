<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_user_property.php                                  ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/editgrid.php");
	include_once($root_folder_path . "includes/tabs_functions.php");
	include_once($root_folder_path . "messages/".$language_code."/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("users_groups");

	$operation = get_param("operation");
	$property_id = get_param("property_id");
	$user_type_id = get_param("user_type_id");

	// start building breadcrumb
	$va_trail = array(
		"admin_menu.php?code=settings" => va_message("SETTINGS_MSG"),
		"admin_menu.php?code=users-settings" => va_message("CUSTOMERS_MSG"),
		"admin_user_properties.php" => va_message("CUSTOM_FIELDS_MSG"),
		"admin_user_property.php?property_id=".urlencode($property_id) => va_message("EDIT_CUSTOM_FIELD_MSG"),
	);
	
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_user_property.html");
	$t->set_var("admin_href",              "admin.php");
	$t->set_var("admin_users_href",        "admin_users.php");
	$t->set_var("admin_user_types_href",   "admin_user_types.php");
	$t->set_var("admin_user_type_href",    "admin_user_type.php");
	$t->set_var("admin_user_property_href","admin_user_property.php");
	$t->set_var("admin_user_profile_href", "admin_user_profile.php");
	$t->set_var("user_type_id",   $user_type_id);

	$sections = get_db_values("SELECT section_id, section_name FROM " . $table_prefix . "user_profile_sections ORDER BY section_order ", array(array("", "")));
	
	$controls = 
		array(			
			array("", ""),  
			array("CHECKBOXLIST", CHECKBOXLIST_MSG),
			array("LABEL",        LABEL_MSG),
			array("LISTBOX",      LISTBOX_MSG),
			array("RADIOBUTTON",  RADIOBUTTON_MSG),
			array("TEXTAREA",     TEXTAREA_MSG),
			array("TEXTBOX",      TEXTBOX_MSG)
			);

	$property_show =
		array(
			array(0, DONT_SHOW_MSG),
			array(1, FOR_ALL_USERS_MSG),
			array(2, NEW_USERS_ONLY_MSG),
			array(3, REGISTERED_USERS_ONLY_MSG)
			);

	// set up html form parameters
	$r = new VA_Record($table_prefix . "user_profile_properties");
	$r->add_where("property_id", INTEGER);
	// TODO: remove old field
	//$r->add_textbox("user_type_id", INTEGER);
	//$r->change_property("user_type_id", REQUIRED, true);
	$r->add_textbox("property_order", INTEGER, FIELD_ORDER_MSG);
	$r->change_property("property_order", REQUIRED, true);
	$r->add_textbox("property_code", TEXT, FIELD_CODE_MSG);
	$r->change_property("property_code", MAX_LENGTH, 64);
	$r->add_textbox("property_name", TEXT, FIELD_NAME_MSG);
	$r->change_property("property_name", REQUIRED, true);
	$r->add_textbox("property_description", TEXT, FIELD_TEXT_MSG);
	$r->add_textbox("default_value", TEXT, DEFAULT_VALUE_MSG);
	$r->add_textbox("property_class", TEXT);
	$r->add_textbox("property_style", TEXT);
	$r->add_textbox("control_style", TEXT);
	$r->add_select("section_id", INTEGER, $sections, FIELD_SECTION_MSG);
	$r->change_property("section_id", REQUIRED, true);
	$r->add_select("control_type", TEXT, $controls, FIELD_CONTROL_MSG);
	$r->change_property("control_type", REQUIRED, true);
	$r->add_radio("property_show", TEXT, $property_show, SHOW_FIELD_MSG);
	$r->change_property("property_show", REQUIRED, true);
	$r->add_checkbox("required", INTEGER);
	// multisites
	/*if ($sitelist) {
		$sites   = get_db_values("SELECT site_id,site_name FROM " . $table_prefix . "sites ORDER BY site_id ",null);
		$r->add_select("site_id", INTEGER, $sites, ADMIN_SITE_MSG);
		$r->change_property("site_id", REQUIRED, true);
	} else {
		$r->add_textbox("site_id", INTEGER);
	}*/
	$r->add_textbox("before_name_html", TEXT);
	$r->add_textbox("after_name_html", TEXT);
	$r->add_textbox("before_control_html", TEXT);
	$r->add_textbox("after_control_html", TEXT);
	$r->add_textbox("control_code", TEXT);
	$r->add_textbox("onchange_code", TEXT);
	$r->add_textbox("onclick_code", TEXT);

	$r->add_textbox("validation_regexp", TEXT);
	$r->add_textbox("regexp_error", TEXT);
	$r->add_textbox("options_values_sql", TEXT);

	// user types 
	$r->add_checkbox("user_types_all", INTEGER);
	$r->change_property("user_types_all", DEFAULT_VALUE, 1);
	$selected_user_types = array();
	if (strlen($operation)) {
		$user_types = get_param("user_types");
		if ($user_types) {
			$selected_user_types = explode(",", $user_types);
		}
	} elseif ($property_id) {
		$sql  = "SELECT user_type_id FROM " . $table_prefix . "user_profile_properties_types ";
		$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$selected_user_types[] = $db->f("user_type_id");
		}
	}

	// sites list
	$r->add_checkbox("sites_all", INTEGER);
	$r->change_property("sites_all", DEFAULT_VALUE, 1);
	$selected_sites = array();
	if (strlen($operation)) {
		$sites = get_param("sites");
		if ($sites) {
			$selected_sites = explode(",", $sites);
		}
	} elseif ($property_id) {
		$sql  = "SELECT site_id FROM " . $table_prefix . "user_profile_properties_sites ";
		$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$selected_sites[] = $db->f("site_id");
		}
	}


	$r->get_form_values();
	
	$ipv = new VA_Record($table_prefix . "user_profile_values", "properties");
	$ipv->add_where("property_value_id", INTEGER);
	$ipv->add_hidden("property_id", INTEGER);
	$ipv->change_property("property_id", USE_IN_INSERT, true);
	$ipv->add_textbox("property_value", TEXT, OPTION_VALUE_MSG);
	$ipv->change_property("property_value", REQUIRED, true);
	$ipv->add_checkbox("hide_value", INTEGER);
	$ipv->add_checkbox("is_default_value", INTEGER);
	
	$property_id = get_param("property_id");

	$more_properties = get_param("more_properties");
	$number_properties = get_param("number_properties");

	$eg = new VA_EditGrid($ipv, "properties");
	$eg->get_form_values($number_properties);

	$operation = get_param("operation");
	$return_page = "admin_user_properties.php";

	if (strlen($operation) && !$more_properties)
	{
		if ($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		}
		elseif ($operation == "delete" && $property_id)
		{
			$db->query("DELETE FROM " . $table_prefix . "user_profile_properties WHERE property_id=" . $db->tosql($property_id, INTEGER));		
			$db->query("DELETE FROM " . $table_prefix . "user_profile_values WHERE property_id=" . $db->tosql($property_id, INTEGER));		
			header("Location: " . $return_page);
			exit;
		}

		$is_valid = $r->validate();
		$is_valid = ($eg->validate() && $is_valid); 

		if ($is_valid)
		{
			if (strlen($property_id))
			{
				$r->update_record();
				$eg->set_values("property_id", $property_id);
				$eg->update_all($number_properties);
			}
			else
			{
				$r->insert_record();
				$property_id = $db->last_insert_id();

				$eg->set_values("property_id", $property_id);
				$eg->insert_all($number_properties);
			}

			// update user types
			$db->query("DELETE FROM " . $table_prefix . "user_profile_properties_types WHERE property_id=" . $db->tosql($property_id, INTEGER));
			for ($st = 0; $st < sizeof($selected_user_types); $st++) {
				$user_type_id = $selected_user_types[$st];
				if (strlen($user_type_id)) {
					$sql  = " INSERT INTO " . $table_prefix . "user_profile_properties_types (property_id, user_type_id) VALUES (";
					$sql .= $db->tosql($property_id, INTEGER) . ", ";
					$sql .= $db->tosql($user_type_id, INTEGER) . ") ";
					$db->query($sql);
				}
			}
			// update sites
			$db->query("DELETE FROM " . $table_prefix . "user_profile_properties_sites WHERE property_id=" . $db->tosql($property_id, INTEGER));
			for ($st = 0; $st < sizeof($selected_sites); $st++) {
				$site_id = $selected_sites[$st];
				if (strlen($site_id)) {
					$sql  = " INSERT INTO " . $table_prefix . "user_profile_properties_sites (property_id, site_id) VALUES (";
					$sql .= $db->tosql($property_id, INTEGER) . ", ";
					$sql .= $db->tosql($site_id, INTEGER) . ") ";
					$db->query($sql);
				}
			}

			header("Location: " . $return_page);
			exit;
		}
	}
	elseif (strlen($property_id) && !$more_properties)
	{
		$r->get_db_values();
		$eg->set_value("property_id", $property_id);
		$eg->change_property("property_value_id", USE_IN_SELECT, true);
		$eg->change_property("property_value_id", USE_IN_WHERE, false);
		$eg->change_property("property_id", USE_IN_WHERE, true);
		$eg->change_property("property_id", USE_IN_SELECT, true);
		$number_properties = $eg->get_db_values();
		if ($number_properties == 0)
			$number_properties = 5;
	}
	elseif ($more_properties)
	{
		$number_properties += 5;
	}
	else // set default values
	{
		$r->set_default_values();
		$sql  = " SELECT MAX(property_order) FROM " . $table_prefix . "user_profile_properties ";
		$property_order = get_db_value($sql);
		$property_order = ($property_order) ? ($property_order + 1) : 1;
		$r->set_value("property_order", $property_order);

		$number_properties = 5;
	}

	$t->set_var("number_properties", $number_properties);

	$eg->set_parameters_all($number_properties);
	$r->set_parameters();

	// set user types 
	$user_types = array();
	$sql = " SELECT type_id, type_name FROM " . $table_prefix . "user_types ";
	$db->query($sql);
	while ($db->next_record())	{
		$list_id = $db->f("type_id");
		$list_name = $db->f("type_name");
		$user_types[$list_id] = $list_name;
		$t->set_var("list_id", $list_id);
		$t->set_var("list_name", $list_name);
		if (in_array($list_id, $selected_user_types)) {
			$t->parse("selected_user_types", true);
		} else {
			$t->parse("available_user_types", true);
		}
	}

	// set sites
	$sites = array();
	$sql = " SELECT site_id, site_name FROM " . $table_prefix . "sites ";
	$db->query($sql);
	while ($db->next_record())	{
		$list_site_id   = $db->f("site_id");
		$site_name = $db->f("site_name");
		$sites[$list_site_id] = $site_name;
		$t->set_var("site_id", $list_site_id);
		$t->set_var("site_name", $site_name);
		if (in_array($list_site_id, $selected_sites)) {
			$t->parse("selected_sites", true);
		} else {
			$t->parse("available_sites", true);
		}
	}


	if (strlen($property_id))	 {
		$t->set_var("save_button", UPDATE_BUTTON);
		$t->parse("delete", false);	
	} else {
		$t->set_var("save_button", ADD_NEW_MSG);
		$t->set_var("delete", "");	
	}

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$tabs = array(
		"general" => array("title" => ADMIN_GENERAL_MSG), 
		"appearance" => array("title" => APPEARANCE_MSG), 
		"javascript" => array("title" => JAVASCRIPT_SETTINGS_MSG), 
		"regexp" => array("title" => "Regular Expression"),
		"user_types" => array("title" => USERS_TYPES_MSG),
		"sites" => array("title" => ADMIN_SITES_MSG),
	);
	parse_tabs($tabs);

	$t->pparse("main");

