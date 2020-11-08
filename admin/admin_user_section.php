<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_user_section.php                                   ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ($root_folder_path . "includes/record.php");

	include_once("./admin_common.php");

	check_admin_security("static_tables");

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_user_section.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_lookup_tables_href", "admin_lookup_tables.php");
	$t->set_var("admin_user_sections_href", "admin_user_sections.php");
	$t->set_var("admin_user_section_href", "admin_user_section.php");

	$section_types =
		array(
			array("", ""),
			array("login", LOGIN_INFO_MSG),
			array("personal", PERSONAL_DETAILS_MSG),
			array("delivery", DELIVERY_DETAILS_MSG),
			array("additional", ADDITIONAL_DETAILS_MSG),
			array("custom", CUSTOM_SECTION_MSG),
			);

	$r = new VA_Record($table_prefix . "user_profile_sections");
	$r->return_page = "admin_user_sections.php";
	
	$r->add_where("section_id", INTEGER);
	$r->add_checkbox("is_active", INTEGER);
	$r->change_property("is_active", DEFAULT_VALUE, 1);
	$r->add_textbox("step_number", INTEGER, ADMIN_STEP_NUMBER_MSG);
	$r->change_property("step_number", MIN_VALUE, 1);
	$r->change_property("step_number", REQUIRED, true);
	$r->change_property("step_number", DEFAULT_VALUE, 1);
	$r->add_textbox("section_order", INTEGER, SECTION_ORDER_MSG);
	$r->change_property("section_order", REQUIRED, true);
	$r->add_select("section_code", TEXT, $section_types, SECTION_TYPE_MSG);
	$r->change_property("section_code", REQUIRED, true);
	$r->add_textbox("section_name", TEXT, SECTION_NAME_MSG);
	$r->change_property("section_name", REQUIRED, true);
	$r->add_checkbox("user_types_all", INTEGER);
	$r->change_property("user_types_all", DEFAULT_VALUE, 1);

	$r->events[BEFORE_INSERT] = "set_section_id";
	$r->events[AFTER_INSERT] = "update_section_types";
	$r->events[AFTER_UPDATE] = "update_section_types";
	$r->events[AFTER_DELETE] = "delete_section_types";
	$r->events[AFTER_SELECT] = "check_db_data";
	$r->events[AFTER_REQUEST] = "check_request_data";
	$r->events[AFTER_SHOW] = "show_section_types";

	$r->process();

	// parse tabs
	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }

	$tabs = array(
		"general" => array("title" => ADMIN_GENERAL_MSG), 
		"user_types" => array("title" => USERS_TYPES_MSG), 
	);

	parse_admin_tabs($tabs, $tab, 7);

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

	function set_section_id()  {
		global $db, $table_prefix, $r;

		$sql = "SELECT MAX(section_id) FROM " . $table_prefix . "user_profile_sections";
		$db->query($sql);
		if($db->next_record()) {
			$section_id = $db->f(0) + 1;
			$r->change_property("section_id", USE_IN_INSERT, true);
			$r->set_value("section_id", $section_id);
		}	
	}

	function check_db_data()
	{
		global $r, $db, $table_prefix, $selected_user_types;

		$section_id = $r->get_value("section_id");
		$selected_user_types = array();
		$sql  = "SELECT user_type_id FROM " . $table_prefix . "user_profile_sections_types ";
		$sql .= " WHERE section_id=" . $db->tosql($section_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$selected_user_types[] = $db->f("user_type_id");
		}
		check_delete_allowed();
	}

	function check_request_data()
	{
		global $r, $db, $table_prefix, $selected_user_types;

		$selected_user_types = array();
		$user_types = get_param("user_types");
		if ($user_types) {
			$selected_user_types = explode(",", $user_types);
		}
		check_delete_allowed();
	}

	function check_delete_allowed()
	{
		global $r;
		if ($r->get_value("section_code") == "login") {
			$r->change_property("step_number", MAX_VALUE, 1);
		}
	}

	function update_section_types()
	{
		global $r, $db, $table_prefix, $selected_user_types;

		$section_id = $r->get_value("section_id");
		$db->query("DELETE FROM " . $table_prefix . "user_profile_sections_types WHERE section_id=" . $db->tosql($section_id, INTEGER));
		for ($ut = 0; $ut < sizeof($selected_user_types); $ut++) {
			$type_id = $selected_user_types[$ut];
			if (strlen($type_id)) {
				$sql  = " INSERT INTO " . $table_prefix . "user_profile_sections_types (section_id, user_type_id) VALUES (";
				$sql .= $db->tosql($section_id, INTEGER) . ", ";
				$sql .= $db->tosql($type_id, INTEGER) . ") ";
				$db->query($sql);
			}
		}
	}

	function delete_section_types()
	{
		global $r, $db, $table_prefix;

		$section_id = $r->get_value("section_id");
		$db->query("DELETE FROM " . $table_prefix . "user_profile_sections_types WHERE section_id=" . $db->tosql($section_id, INTEGER));
	}

	function show_section_types()
	{
		global $r, $t, $db, $table_prefix, $selected_user_types;

		$user_types = array();
		$sql = " SELECT type_id, type_name FROM " . $table_prefix . "user_types ";
		$db->query($sql);
		while ($db->next_record())	{
			$type_id = $db->f("type_id");
			$type_name = get_translation($db->f("type_name"));
			$user_types[$type_id] = $type_name;
		}
  
		foreach($user_types as $type_id => $type_name) {
			$t->set_var("type_id", $type_id);
			$t->set_var("type_name", $type_name);
			if (is_array($selected_user_types) && in_array($type_id, $selected_user_types)) {
				$t->parse("selected_user_types", true);
			} else {
				$t->parse("available_user_types", true);
			}
		}
	}

?>