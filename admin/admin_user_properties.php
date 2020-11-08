<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_user_properties.php                                ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ($root_folder_path . "includes/record.php");
	include_once ("../messages/".$language_code."/cart_messages.php");

	include_once("./admin_common.php");

	check_admin_security("users_groups");

	// start building breadcrumb
	$va_trail = array(
		"admin_menu.php?code=settings" => va_message("SETTINGS_MSG"),
		"admin_menu.php?code=users-settings" => va_message("CUSTOMERS_MSG"),
		"admin_user_properties.php" => va_message("CUSTOM_FIELDS_MSG"),
	);

	$operation = get_param("operation");
	$s_ut = get_param("s_ut");
	$property_id = get_param("property_id");

	if ($operation == "required-yes") {
		$sql = " UPDATE ".$table_prefix."user_profile_properties ";
		$sql.= " SET required=1 ";
		$sql.= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
		$db->query($sql);
	} else if ($operation == "required-no") {
		$sql = " UPDATE ".$table_prefix."user_profile_properties ";
		$sql.= " SET required=0 ";
		$sql.= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
		$db->query($sql);
	}


	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_user_properties.html");

	$arps = new VA_URL("admin_user_properties.php", false);
	$arps->add_parameter("s_ut", REQUEST, "s_ut");

	$arp = new VA_URL("admin_user_property.php", false);
	$arp->add_parameter("s_ut", REQUEST, "s_ut");

	$t->set_var("admin_user_property_new_url", $arp->get_url());

	$arp = new VA_URL("admin_user_property.php", false);
	$arp->add_parameter("property_id", DB, "property_id");

	// get properties first
	$user_properties = array(); $property_ids = array();
	$sql  = " SELECT upp.property_id, upp.property_name, upp.property_order, ups.section_name, upp.property_show, upp.required, upp.control_type, ";
	$sql .= " upp.user_types_all, upp.sites_all ";
	$sql .= " FROM (" . $table_prefix . "user_profile_properties upp ";
	$sql .= " LEFT JOIN " . $table_prefix . "user_profile_sections ups ON upp.section_id=ups.section_id) ";
	if ($s_ut) {
		$sql .= " WHERE user_type_id=" . $db->tosql($s_ut, INTEGER);
	} 
	$sql .= " ORDER BY property_order, property_id ";
	$db->query($sql);
	while ($db->next_record()) {
		$property_id = $db->f("property_id");
		$property_ids[] = $property_id;
		$user_properties[$property_id] = $db->Record;
		$user_properties[$property_id]["sites"] = array();
		$user_properties[$property_id]["user_types"] = array();
	}

	// get user types for properties
	$sql  = " SELECT uppt.property_id, ut.type_name FROM " . $table_prefix . "user_profile_properties_types uppt ";
	$sql .= " INNER JOIN " . $table_prefix . "user_types ut ON ut.type_id=uppt.user_type_id ";
	$sql .= " WHERE property_id IN (" . $db->tosql($property_ids, INTEGER_LIST) . ")";
	$db->query($sql);
	while ($db->next_record()) {
		$property_id = $db->f("property_id");
		$user_type_name = $db->f("type_name");
		$user_properties[$property_id]["user_types"][] = $user_type_name;
	}

	// get sites for properties
	$sql  = " SELECT upps.property_id, s.site_name FROM " . $table_prefix . "user_profile_properties_sites upps ";
	$sql .= " INNER JOIN " . $table_prefix . "sites s ON s.site_id=upps.site_id ";
	$sql .= " WHERE property_id IN (" . $db->tosql($property_ids, INTEGER_LIST) . ")";
	$db->query($sql);
	while ($db->next_record()) {
		$property_id = $db->f("property_id");
		$site_name = $db->f("site_name");
		$user_properties[$property_id]["sites"][] = $site_name;
	}

	if (count($user_properties)) {
		$property_show_values = array(0 => DONT_SHOW_MSG, 1 => FOR_ALL_USERS_MSG, 2 => NEW_USERS_ONLY_MSG, 3 => REGISTERED_USERS_ONLY_MSG);

		$controls = array(
			"CHECKBOXLIST" => CHECKBOXLIST_MSG, "LABEL" => LABEL_MSG, "LISTBOX" => LISTBOX_MSG,
			"RADIOBUTTON" => RADIOBUTTON_MSG, "TEXTAREA" => TEXTAREA_MSG, "TEXTBOX" => TEXTBOX_MSG, "HIDDEN" => HIDDEN_MSG);

		$t->parse("name_properties", false);
		foreach ($user_properties as $property_id => $data) {
			$property_name = get_translation($data["property_name"]);
			$section_name = get_translation($data["section_name"]);
			$property_order = $data["property_order"];

			$property_show = get_setting_value($property_show_values, $data["property_show"], "");
			$control_type = $data["control_type"];
			$control_name = isset($controls[$control_type]) ? $controls[$control_type] : $control_type;
			$property_required = $data["required"];
			$user_types_all = $data["user_types_all"];
			$user_types = $data["user_types"];
			$sites_all = $data["sites_all"];
			$sites = $data["sites"];
			if ($property_required) {
				$property_required_desc = YES_MSG;
				$property_required_class= "required-yes";
				$arps->add_parameter("operation", CONSTANT, "required-no");
			} else {
				$property_required_desc = NO_MSG;
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
			$t->set_var("admin_user_property_url", $arp->get_url());

			$t->set_var("section_name", $section_name);
			$t->set_var("property_show", $property_show);
			$t->set_var("control_type",  $control_name);

			// parse user types
			$t->set_var("extra_user_types", "");
			$t->set_var("user_type_list", "");
			$t->set_var("user_type_single", "");
			if ($user_types_all || count($user_types) <= 1) {
				if ($user_types_all) {
					$t->set_var("user_type_name", va_message("ALL_MSG"));
				} else if (count($user_types) == 1) {
					$t->set_var("user_type_name", htmlspecialchars($user_types[0]));
				} else {
					$t->set_var("user_type_name", "&ndash;");
				}
				$t->sparse("user_type_single", false);
			} else {

				$t->set_var("user_type_summary", count($user_types)." ".va_message("TYPES_MSG"));
				foreach ($user_types as $user_type_name) {
					$t->set_var("user_type_name", htmlspecialchars($user_type_name));
					$t->sparse("extra_user_types", true);
				}
				$t->sparse("user_type_list", false);
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

