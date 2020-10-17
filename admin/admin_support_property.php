<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_support_property.php                               ***
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

	check_admin_security("support_settings");

	$operation = get_param("operation");
	$property_id = get_param("property_id");

	// start building breadcrumb
	$va_trail = array(
		"admin_menu.php?code=settings" => va_message("SETTINGS_MSG"),
		"admin_menu.php?code=helpdesk-settings" => va_message("HELPDESK_MSG"),
		"admin_support_properties.php" => va_message("CUSTOM_FIELDS_MSG"),
		"admin_support_property.php?property_id=".urlencode($property_id) => va_message("EDIT_CUSTOM_FIELD_MSG"),
	);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_support_property.html");
	$t->set_var("admin_href",              "admin.php");
	$t->set_var("admin_support_property_href",    "admin_support_property.php");
	$t->set_var("admin_support_settings_href",    "admin_support_settings.php");
	
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
			array(2, NON_REGISTERED_USERS_MSG),
			array(3, REGISTERED_USERS_ONLY_MSG)
			);

	// set up html form parameters
	$r = new VA_Record($table_prefix . "support_custom_properties");
	$r->add_where("property_id", INTEGER);
	$r->change_property("property_id", USE_IN_INSERT, true);
	$r->add_textbox("property_order", INTEGER, FIELD_ORDER_MSG);
	$r->change_property("property_order", REQUIRED, true);
	$r->add_textbox("property_name", TEXT, FIELD_NAME_MSG);
	$r->change_property("property_name", REQUIRED, true);
	$r->add_textbox("property_description", TEXT, FIELD_TEXT_MSG);
	$r->add_textbox("default_value", TEXT, DEFAULT_VALUE_MSG);
	$r->add_textbox("property_class", TEXT);
	$r->add_textbox("property_style", TEXT);
	$r->add_textbox("control_style", TEXT);
	$r->add_hidden("section_id", INTEGER); // probably could be removed
	$r->add_select("control_type", TEXT, $controls, FIELD_CONTROL_MSG);
	$r->change_property("control_type", REQUIRED, true);
	$r->add_radio("property_show", INTEGER, $property_show, SHOW_FIELD_MSG);
	$r->change_property("property_show", REQUIRED, true);
	$r->add_checkbox("required", INTEGER);

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

	// field departments 
	$r->add_checkbox("deps_all", INTEGER);
	$r->change_property("deps_all", DEFAULT_VALUE, 1);
	$selected_deps = array();
	if (strlen($operation)) {
		$deps = get_param("deps");
		if ($deps) {
			$selected_deps = explode(",", $deps);
		}
	} elseif ($property_id) {
		$sql  = "SELECT dep_id FROM " . $table_prefix . "support_custom_departments ";
		$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$selected_deps[] = $db->f("dep_id");
		}
	}

	// field types 
	$r->add_checkbox("types_all", INTEGER);
	$r->change_property("types_all", DEFAULT_VALUE, 1);
	$selected_types = array();
	if (strlen($operation)) {
		$types = get_param("types");
		if ($types) {
			$selected_types = explode(",", $types);
		}
	} elseif ($property_id) {
		$sql  = "SELECT type_id FROM " . $table_prefix . "support_custom_types ";
		$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$selected_types[] = $db->f("type_id");
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
		$sql  = "SELECT site_id FROM " . $table_prefix . "support_custom_sites ";
		$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$selected_sites[] = $db->f("site_id");
		}
	}

	$r->get_form_values();
	
	$ipv = new VA_Record($table_prefix . "support_custom_values", "properties");
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
	$return_page = "admin_support_properties.php";

	if (!strlen($operation)){
		$r->set_value("property_show",1);
	}
	
	if (strlen($operation) && !$more_properties)
	{
		if ($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		}
		elseif ($operation == "delete" && $property_id)
		{
			$db->query("DELETE FROM " . $table_prefix . "support_custom_properties WHERE property_id=" . $db->tosql($property_id, INTEGER));		
			$db->query("DELETE FROM " . $table_prefix . "support_custom_values WHERE property_id=" . $db->tosql($property_id, INTEGER));		
			header("Location: " . $return_page);
			exit;
		}

		$is_valid = $r->validate();
		$is_valid = ($eg->validate() && $is_valid); 

		if ($is_valid)
		{
			if (strlen($property_id)) {
				$r->update_record();
				$eg->set_values("property_id", $property_id);
				$eg->update_all($number_properties);
			} else {
				$r->insert_record();
				$property_id = $db->last_insert_id();

				$eg->set_values("property_id", $property_id);
				$eg->insert_all($number_properties);
			}

			// update deps
			$db->query("DELETE FROM " . $table_prefix . "support_custom_departments WHERE property_id=" . $db->tosql($property_id, INTEGER));
			for ($st = 0; $st < sizeof($selected_deps); $st++) {
				$dep_id = $selected_deps[$st];
				if (strlen($dep_id)) {
					$sql  = " INSERT INTO " . $table_prefix . "support_custom_departments (property_id, dep_id) VALUES (";
					$sql .= $db->tosql($property_id, INTEGER) . ", ";
					$sql .= $db->tosql($dep_id, INTEGER) . ") ";
					$db->query($sql);
				}
			}
			// update types
			$db->query("DELETE FROM " . $table_prefix . "support_custom_types WHERE property_id=" . $db->tosql($property_id, INTEGER));
			for ($st = 0; $st < sizeof($selected_types); $st++) {
				$type_id = $selected_types[$st];
				if (strlen($type_id)) {
					$sql  = " INSERT INTO " . $table_prefix . "support_custom_types (property_id, type_id) VALUES (";
					$sql .= $db->tosql($property_id, INTEGER) . ", ";
					$sql .= $db->tosql($type_id, INTEGER) . ") ";
					$db->query($sql);
				}
			}
			// update sites
			$db->query("DELETE FROM " . $table_prefix . "support_custom_sites WHERE property_id=" . $db->tosql($property_id, INTEGER));
			for ($st = 0; $st < sizeof($selected_sites); $st++) {
				$site_id = $selected_sites[$st];
				if (strlen($site_id)) {
					$sql  = " INSERT INTO " . $table_prefix . "support_custom_sites (property_id, site_id) VALUES (";
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
		$sql  = " SELECT MAX(property_order) FROM " . $table_prefix . "support_custom_properties ";
		$property_order = get_db_value($sql);
		$property_order = ($property_order) ? ($property_order + 1) : 1;
		$r->set_value("property_order", $property_order);

		$number_properties = 5;
	}
	
	$t->set_var("number_properties", $number_properties);

	$eg->set_parameters_all($number_properties);
	$r->set_parameters();


	// set field departments
	$sql = " SELECT dep_id, dep_name FROM " . $table_prefix . "support_departments ";
	$db->query($sql);
	while ($db->next_record())	{
		$list_id = $db->f("dep_id");
		$list_name = get_translation($db->f("dep_name"));
		$t->set_var("list_id", $list_id);
		$t->set_var("list_name", $list_name);
		if (in_array($list_id, $selected_deps)) {
			$t->parse("selected_deps", true);
		} else {
			$t->parse("available_deps", true);
		}
	}

	// set field types 
	$sql = " SELECT type_id, type_name FROM " . $table_prefix . "support_types ";
	$db->query($sql);
	while ($db->next_record())	{
		$list_id = $db->f("type_id");
		$list_name = get_translation($db->f("type_name"));
		$t->set_var("list_id", $list_id);
		$t->set_var("list_name", $list_name);
		if (in_array($list_id, $selected_types)) {
			$t->parse("selected_types", true);
		} else {
			$t->parse("available_types", true);
		}
	}

	// set field sites
	$sql = " SELECT site_id, site_name FROM " . $table_prefix . "sites ";
	$db->query($sql);
	while ($db->next_record())	{
		$list_site_id   = $db->f("site_id");
		$site_name = get_translation($db->f("site_name"));
		$t->set_var("site_id", $list_site_id);
		$t->set_var("site_name", $site_name);
		if (in_array($list_site_id, $selected_sites)) {
			$t->parse("selected_sites", true);
		} else {
			$t->parse("available_sites", true);
		}
	}

	if (strlen($property_id))	{
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
		"deps" => array("title" => DEPARTMENTS_MSG),
		"types" => array("title" => TYPES_MSG),
		"sites" => array("title" => ADMIN_SITES_MSG),
	);
	parse_tabs($tabs);


	$t->pparse("main");

