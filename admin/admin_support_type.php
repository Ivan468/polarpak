<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_support_type.php                                   ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path."includes/record.php");
	include_once($root_folder_path."includes/tabs_functions.php");
	include_once($root_folder_path."messages/".$language_code."/support_messages.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("support_settings");
	//check_admin_security("support_static_data");

	$operation = get_param("operation");
	$type_id = get_param("type_id");

	// start building breadcrumb
	$va_trail = array(
		"admin_menu.php?code=settings" => va_message("SETTINGS_MSG"),
		"admin_menu.php?code=helpdesk-settings" => va_message("HELPDESK_MSG"),
		"admin_support_properties.php" => va_message("SUPPORT_TYPES_MSG"),
		"admin_support_type.php?type_id=".urlencode($type_id) => va_message("EDIT_SUPPORT_TYPE_MSG"),
	);

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_support_type.html");
	$t->set_var("admin_href",              "admin.php");
	$t->set_var("admin_support_settings_href",    "admin_support_settings.php");
	$t->set_var("admin_support_href", "admin_support.php");
	$t->set_var("admin_support_type_href", "admin_support_type.php");
	$t->set_var("admin_support_types_href", "admin_support_types.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", va_message("TYPE_MSG"), va_message("CONFIRM_DELETE_MSG")));

	$r = new VA_Record($table_prefix . "support_types");
	$r->return_page = "admin_support_types.php";

	$r->add_where("type_id", INTEGER);
	$r->add_checkbox("show_for_user", INTEGER);
	$r->add_checkbox("is_default", INTEGER);
	$r->add_textbox("type_order", INTEGER, va_message("ADMIN_ORDER_MSG"));
	$r->change_property("type_order", REQUIRED, true);
	$r->add_textbox("type_name", TEXT, TYPE_NAME_MSG );
	$r->change_property("type_name", REQUIRED, true);
	$r->add_textbox("intro_text", TEXT, va_message("INTRO_TEXT_MSG"));

	// field departments 
	$r->add_checkbox("deps_all", INTEGER);
	$r->change_property("deps_all", DEFAULT_VALUE, 1);
	$selected_deps = array();
	if (strlen($operation)) {
		$deps = get_param("deps");
		if ($deps) {
			$selected_deps = explode(",", $deps);
		}
	} elseif ($type_id) {
		$sql  = "SELECT dep_id FROM " . $table_prefix . "support_types_departments ";
		$sql .= " WHERE type_id=" . $db->tosql($type_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$selected_deps[] = $db->f("dep_id");
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
	} elseif ($type_id) {
		$sql  = "SELECT site_id FROM " . $table_prefix . "support_types_sites ";
		$sql .= " WHERE type_id=" . $db->tosql($type_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$selected_sites[] = $db->f("site_id");
		}
	}

	$r->set_event(BEFORE_DELETE, "before_delete_type");    
	$r->set_event(AFTER_INSERT, "update_type_data");    
	$r->set_event(AFTER_UPDATE, "update_type_data");    
	$r->process();

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

	$tabs = array(
		"general" => array("title" => va_message("ADMIN_GENERAL_MSG")), 
		"deps" => array("title" => va_message("DEPARTMENTS_MSG")),
		"sites" => array("title" => va_message("ADMIN_SITES_MSG")),
	);
	parse_tabs($tabs);

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_href", "admin.php");
	$t->pparse("main");


function update_type_data($params)
{
	global $r, $db, $table_prefix, $selected_deps, $selected_sites;

	$event = isset($params["event"]) ? $params["event"] : "";
	if ($event == AFTER_INSERT) {
		$type_id = $db->last_insert_id();
		$r->set_value("type_id", $type_id);
	} else {
		$type_id = $r->get_value("type_id");
	}

	// update deps
	$db->query("DELETE FROM " . $table_prefix . "support_types_departments WHERE type_id=" . $db->tosql($type_id, INTEGER));
	for ($st = 0; $st < sizeof($selected_deps); $st++) {
		$dep_id = $selected_deps[$st];
		if (strlen($dep_id)) {
			$sql  = " INSERT INTO " . $table_prefix . "support_types_departments (type_id, dep_id) VALUES (";
			$sql .= $db->tosql($type_id, INTEGER) . ", ";
			$sql .= $db->tosql($dep_id, INTEGER) . ") ";
			$db->query($sql);
		}
	}
	// update sites
	$db->query("DELETE FROM " . $table_prefix . "support_types_sites WHERE type_id=" . $db->tosql($type_id, INTEGER));
	for ($st = 0; $st < sizeof($selected_sites); $st++) {
		$site_id = $selected_sites[$st];
		if (strlen($site_id)) {
			$sql  = " INSERT INTO " . $table_prefix . "support_types_sites (type_id, site_id) VALUES (";
			$sql .= $db->tosql($type_id, INTEGER) . ", ";
			$sql .= $db->tosql($site_id, INTEGER) . ") ";
			$db->query($sql);
		}
	}
}

function before_delete_type()
{
	global $r, $db, $table_prefix;
	$type_id = $r->get_value("type_id");
	$sql = " DELETE FROM " . $table_prefix . "support_types_sites WHERE type_id=" . $db->tosql($type_id, INTEGER);		
	$db->query($sql);
	$sql = " DELETE FROM " . $table_prefix . "support_types_departments WHERE type_id=" . $db->tosql($type_id, INTEGER);		
	$db->query($sql);
	//va_support (support_type_id)
	//va_support_pipes (support_type_id)
	//va_support_custom_types (type_id)
}
