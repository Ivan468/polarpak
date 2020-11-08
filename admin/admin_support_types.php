<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_support_types.php                                  ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");

	include_once($root_folder_path."messages/".$language_code."/support_messages.php");
	include_once("./admin_common.php");

	check_admin_security("support_static_data");

	// start building breadcrumb
	$va_trail = array(
		"admin_menu.php?code=settings" => va_message("SETTINGS_MSG"),
		"admin_menu.php?code=helpdesk-settings" => va_message("HELPDESK_MSG"),
		"admin_support_types.php" => va_message("SUPPORT_TYPES_MSG"),
	);

	$operation = get_param("operation");
	$type_id = get_param("type_id");

	// update show_for_user field
	if ($operation == "show-yes") {
		$sql = " UPDATE ".$table_prefix."support_types ";
		$sql.= " SET show_for_user=1 ";
		$sql.= " WHERE type_id=" . $db->tosql($type_id, INTEGER);
		$db->query($sql);
	} else if ($operation == "show-no") {
		$sql = " UPDATE ".$table_prefix."support_types ";
		$sql.= " SET show_for_user=0 ";
		$sql.= " WHERE type_id=" . $db->tosql($type_id, INTEGER);
		$db->query($sql);
	} else if ($operation == "default-yes") {
		$sql = " UPDATE ".$table_prefix."support_types ";
		$sql.= " SET is_default=1 ";
		$sql.= " WHERE type_id=" . $db->tosql($type_id, INTEGER);
		$db->query($sql);
	} else if ($operation == "default-no") {
		$sql = " UPDATE ".$table_prefix."support_types ";
		$sql.= " SET is_default=0 ";
		$sql.= " WHERE type_id=" . $db->tosql($type_id, INTEGER);
		$db->query($sql);
	}

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_support_types.html");

	$t->set_var("admin_support_href", "admin_support.php");
	$t->set_var("admin_support_type_href", "admin_support_type.php");
	$t->set_var("admin_support_types_href", "admin_support_types.php");

	$asts = new VA_URL("admin_support_types.php", false);
	$asts->add_parameter("sort_ord", REQUEST, "sort_ord");
	$asts->add_parameter("sort_dir", REQUEST, "sort_dir");
	$asts->add_parameter("page", REQUEST, "page");

	$ast = new VA_URL("admin_support_type.php", false);
	$ast->add_parameter("sort_ord", REQUEST, "sort_ord");
	$ast->add_parameter("sort_dir", REQUEST, "sort_dir");
	$ast->add_parameter("page", REQUEST, "page");
	$t->set_var("admin_support_type_new_url", $ast->get_url());

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_support_types.php");
	$s->set_parameters(false, true, true, false);
	$s->set_default_sorting("5", "asc");
	$s->set_sorter(va_message("ID_MSG"), "sorter_type_id", "1", "type_id", "", "");
	$s->set_sorter(va_message("TYPE_NAME_MSG"), "sorter_type_name", "2", "type_name");
	$s->set_sorter(va_message("SHOW_FOR_USER_MSG"), "sorter_show_for_user", "3", "show_for_user");
	$s->set_sorter(va_message("DEFAULT_MSG"), "sorter_is_default", "4", "is_default");
	$s->set_sorter(va_message("SORT_ORDER_MSG"), "sorter_type_order", "5", "type_order", "type_order, type_name", "type_order DESC, type_name DESC", true);

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_support_types.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "support_types");
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = set_recs_param("admin_support_types.php");
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	// get types first
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = "SELECT * FROM " . $table_prefix . "support_types";
	$sql .= $s->order_by;
	$support_types = array(); $types_ids = array();
	$db->query($sql);
	while ($db->next_record()) {
		$type_id = $db->f("type_id");
		$types_ids[] = $type_id;
		$support_types[$type_id] = $db->Record;
		$support_types[$type_id]["deps"] = array();
		$support_types[$type_id]["sites"] = array();
	}

	// get departments for types 
	$sql  = " SELECT std.type_id, sd.short_name, sd.dep_name FROM " . $table_prefix . "support_types_departments std ";
	$sql .= " INNER JOIN " . $table_prefix . "support_departments sd ON sd.dep_id=std.dep_id ";
	$sql .= " WHERE std.type_id IN (" . $db->tosql($types_ids, INTEGER_LIST) . ")";
	$db->query($sql);
	while ($db->next_record()) {
		$type_id = $db->f("type_id");
		$short_name = get_translation($db->f("short_name"));
		$dep_name = get_translation($db->f("dep_name"));
		$support_types[$type_id]["deps"][] = array("short_name" => $short_name, "dep_name" => $dep_name);
	}

	// get sites for types 
	$sql  = " SELECT sts.type_id, s.site_name FROM " . $table_prefix . "support_types_sites sts ";
	$sql .= " INNER JOIN " . $table_prefix . "sites s ON s.site_id=sts.site_id ";
	$sql .= " WHERE sts.type_id IN (" . $db->tosql($types_ids, INTEGER_LIST) . ")";
	$db->query($sql);
	while ($db->next_record()) {
		$type_id = $db->f("type_id");
		$site_name = get_translation($db->f("site_name"));
		$support_types[$type_id]["sites"][] = $site_name;
	}


	if (count($support_types)) {

		$t->set_var("no_records", "");
		foreach ($support_types as $type_id => $data) {

			$is_default = $data["is_default"];
			$show_for_user = $data["show_for_user"];
			$type_order = $data["type_order"];
			$type_name = get_translation($data["type_name"]);

			$deps_all = $data["deps_all"];
			$deps = $data["deps"];
			$sites_all = $data["sites_all"];
			$sites = $data["sites"];

			$asts->add_parameter("type_id", CONSTANT, $type_id);
			$ast->add_parameter("type_id", CONSTANT, $type_id);
			if ($show_for_user) {
				$show_for_user_desc = va_message("YES_MSG");
				$show_for_user_class= "yes-option";
				$asts->add_parameter("operation", CONSTANT, "show-no");
			} else {
				$show_for_user_desc = va_message("NO_MSG");
				$show_for_user_class= "no-option";
				$asts->add_parameter("operation", CONSTANT, "show-yes");
			}
			$t->set_var("type_id", $type_id);
			$t->set_var("type_name", htmlspecialchars($type_name));
			$t->set_var("type_order", $type_order);
			$t->set_var("show_for_user_desc", $show_for_user_desc);
			$t->set_var("show_for_user_class", $show_for_user_class);
			$t->set_var("show_for_user_url", $asts->get_url());

			if ($is_default) {
				$is_default_desc = va_message("YES_MSG");
				$is_default_class= "yes-option";
				$asts->add_parameter("operation", CONSTANT, "default-no");
			} else {
				$is_default_desc = va_message("NO_MSG");
				$is_default_class= "no-option";
				$asts->add_parameter("operation", CONSTANT, "default-yes");
			}
			$t->set_var("is_default_desc", $is_default_desc);
			$t->set_var("is_default_class", $is_default_class);
			$t->set_var("is_default_url", $asts->get_url());

			$t->set_var("admin_support_type_url", $ast->get_url());

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

			$t->parse("records", true);
		} 
	} else {
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	$t->set_var("admin_href", "admin.php");
	$t->pparse("main");
