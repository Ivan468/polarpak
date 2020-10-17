<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_support_properties.php                             ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ($root_folder_path . "includes/record.php");
	include_once ("../messages/".$language_code."/cart_messages.php");

	include_once("./admin_common.php");

	check_admin_security("support_settings");

	// start building breadcrumb
	$va_trail = array(
		"admin_menu.php?code=settings" => va_message("SETTINGS_MSG"),
		"admin_menu.php?code=helpdesk-settings" => va_message("HELPDESK_MSG"),
		"admin_support_properties.php" => va_message("CUSTOM_FIELDS_MSG"),
	);

	$operation = get_param("operation");
	$s_ut = get_param("s_ut");
	$property_id = get_param("property_id");

	if ($operation == "required-yes") {
		$sql = " UPDATE ".$table_prefix."support_custom_properties ";
		$sql.= " SET required=1 ";
		$sql.= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
		$db->query($sql);
	} else if ($operation == "required-no") {
		$sql = " UPDATE ".$table_prefix."support_custom_properties ";
		$sql.= " SET required=0 ";
		$sql.= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
		$db->query($sql);
	}


	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_support_properties.html");

	$arps = new VA_URL("admin_support_properties.php", false);
	$arps->add_parameter("s_ut", REQUEST, "s_ut");

	$arp = new VA_URL("admin_support_property.php", false);
	$arp->add_parameter("s_ut", REQUEST, "s_ut");

	$t->set_var("admin_support_property_new_url", $arp->get_url());

	$arp = new VA_URL("admin_support_property.php", false);
	$arp->add_parameter("property_id", DB, "property_id");

	// get properties first
	$support_properties = array(); $property_ids = array();
	$sql  = " SELECT scp.property_id, scp.property_name, scp.property_order, scp.property_show, scp.required, scp.control_type, ";
	$sql .= " scp.deps_all, scp.types_all, scp.sites_all ";
	$sql .= " FROM " . $table_prefix . "support_custom_properties scp ";
	$sql .= " ORDER BY scp.property_order, scp.property_id ";
	$db->query($sql);
	while ($db->next_record()) {
		$property_id = $db->f("property_id");
		$property_ids[] = $property_id;
		$support_properties[$property_id] = $db->Record;
		$support_properties[$property_id]["deps"] = array();
		$support_properties[$property_id]["types"] = array();
		$support_properties[$property_id]["sites"] = array();
	}

	// get departments for properties
	$sql  = " SELECT scd.property_id, sd.short_name, sd.dep_name FROM " . $table_prefix . "support_custom_departments scd ";
	$sql .= " INNER JOIN " . $table_prefix . "support_departments sd ON sd.dep_id=scd.dep_id ";
	$sql .= " WHERE property_id IN (" . $db->tosql($property_ids, INTEGER_LIST) . ")";
	$db->query($sql);
	while ($db->next_record()) {
		$property_id = $db->f("property_id");
		$short_name = get_translation($db->f("short_name"));
		$dep_name = get_translation($db->f("dep_name"));
		$support_properties[$property_id]["deps"][] = array("short_name" => $short_name, "dep_name" => $dep_name);
	}

	// get helpdesk types for properties
	$sql  = " SELECT sct.property_id, st.type_name FROM " . $table_prefix . "support_custom_types sct ";
	$sql .= " INNER JOIN " . $table_prefix . "support_types st ON st.type_id=sct.type_id ";
	$sql .= " WHERE property_id IN (" . $db->tosql($property_ids, INTEGER_LIST) . ")";
	$db->query($sql);
	while ($db->next_record()) {
		$property_id = $db->f("property_id");
		$type_name = get_translation($db->f("type_name"));
		$support_properties[$property_id]["types"][] = $type_name;
	}

	// get sites for properties
	$sql  = " SELECT scs.property_id, s.site_name FROM " . $table_prefix . "support_custom_sites scs ";
	$sql .= " INNER JOIN " . $table_prefix . "sites s ON s.site_id=scs.site_id ";
	$sql .= " WHERE property_id IN (" . $db->tosql($property_ids, INTEGER_LIST) . ")";
	$db->query($sql);
	while ($db->next_record()) {
		$property_id = $db->f("property_id");
		$site_name = get_translation($db->f("site_name"));
		$support_properties[$property_id]["sites"][] = $site_name;
	}

	if (count($support_properties)) {
		$property_show_values = array(
			0 => va_message("DONT_SHOW_MSG"), 1 => va_message("FOR_ALL_USERS_MSG"), 
			2 => va_message("NEW_USERS_ONLY_MSG"), 3 => va_message("REGISTERED_USERS_ONLY_MSG"));

		$controls = array(
			"CHECKBOXLIST" => va_message("CHECKBOXLIST_MSG"), "LABEL" => va_message("LABEL_MSG"), "LISTBOX" => va_message("LISTBOX_MSG"),
			"RADIOBUTTON" => va_message("RADIOBUTTON_MSG"), "TEXTAREA" => va_message("TEXTAREA_MSG"), "TEXTBOX" => va_message("TEXTBOX_MSG"), 
			"HIDDEN" => va_message("HIDDEN_MSG"));

		$t->parse("name_properties", false);
		foreach ($support_properties as $property_id => $data) {
			$property_name = get_translation($data["property_name"]);
			$property_order = $data["property_order"];
			$property_show = get_setting_value($property_show_values, $data["property_show"], "");
			$control_type = $data["control_type"];
			$control_name = isset($controls[$control_type]) ? $controls[$control_type] : $control_type;
			$property_required = $data["required"];
			$deps_all = $data["deps_all"];
			$deps = $data["deps"];
			$types_all = $data["types_all"];
			$types = $data["types"];
			$sites_all = $data["sites_all"];
			$sites = $data["sites"];
			if ($property_required) {
				$property_required_desc = va_message("YES_MSG");
				$property_required_class= "required-yes";
				$arps->add_parameter("operation", CONSTANT, "required-no");
			} else {
				$property_required_desc = va_message("NO_MSG");
				$property_required_class= "required-no";
				$arps->add_parameter("operation", CONSTANT, "required-yes");
			}
			$arps->add_parameter("property_id", CONSTANT, $property_id);
			$arp->add_parameter("property_id", CONSTANT, $property_id);

			$t->set_var("property_id",   $property_id);
			$t->set_var("property_name", $property_name);
			$t->set_var("property_order", $property_order);
			$t->set_var("property_required_desc", $property_required_desc);
			$t->set_var("property_required_class", $property_required_class);
			$t->set_var("property_required_url", $arps->get_url());
			$t->set_var("admin_support_property_url", $arp->get_url());

			$t->set_var("property_show", $property_show);
			$t->set_var("control_type",  $control_name);

			// parse field deps
			$t->set_var("extra_deps", "");
			$t->set_var("dep_list", "");
			$t->set_var("dep_single", "");
			if ($deps_all || count($deps) <= 1) {
				if ($deps_all) {
					$t->set_var("dep_name", va_message("ALL_MSG"));
				} else if (count($deps) == 1) {
					$t->set_var("dep_name", htmlspecialchars($deps[0]["short_name"]));
				} else {
					$t->set_var("dep_name", "&ndash;");
				}
				$t->sparse("dep_single", false);
			} else {

				$t->set_var("dep_summary", count($deps)." ".va_message("DEPARTMENTS_MSG"));
				foreach ($deps as $dep_data) {
					$t->set_var("dep_name", htmlspecialchars($dep_data["dep_name"]));
					$t->sparse("extra_deps", true);
				}
				$t->sparse("dep_list", false);
			}

			// parse field types
			$t->set_var("extra_types", "");
			$t->set_var("type_list", "");
			$t->set_var("type_single", "");
			if ($types_all || count($types) <= 1) {
				if ($types_all) {
					$t->set_var("type_name", va_message("ALL_MSG"));
				} else if (count($types) == 1) {
					$t->set_var("type_name", htmlspecialchars($types[0]));
				} else {
					$t->set_var("type_name", "&ndash;");
				}
				$t->sparse("type_single", false);
			} else {

				$t->set_var("type_summary", count($types)." ".va_message("TYPES_MSG"));
				foreach ($types as $type_name) {
					$t->set_var("type_name", htmlspecialchars($type_name));
					$t->sparse("extra_types", true);
				}
				$t->sparse("type_list", false);
			}

			// parse sites
			$t->set_var("extra_sites", "");
			$t->set_var("site_list", "");
			$t->set_var("site_single", "");
			if ($sites_all || count($sites) <= 1) {
				if ($sites_all) {
					$t->set_var("site_name", va_message("ALL_MSG"));
				} else if (count($sites) == 1) {
					$t->set_var("site_name", htmlspecialchars($sites[0]));
				} else {
					$t->set_var("site_name", "&ndash;");
				}
				$t->sparse("site_single", false);
			} else {
				$t->set_var("site_summary", count($sites)." ".va_message("SITES_MSG"));
				foreach ($sites as $site_name) {
					$t->set_var("site_name", htmlspecialchars($site_name));
					$t->sparse("extra_sites", true);
				}
				$t->sparse("site_list", false);
			}


			$t->parse("properties", true);
		} 
	} else {
		$t->parse("no_properties", false);
	}

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_href", "admin.php");
	
	$t->pparse("main");

