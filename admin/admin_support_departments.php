<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_support_departments.php                            ***
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

	check_admin_security("support_departments");

	// start building breadcrumb
	$va_trail = array(
		"admin_menu.php?code=settings" => va_message("SETTINGS_MSG"),
		"admin_menu.php?code=helpdesk-settings" => va_message("HELPDESK_MSG"),
		"admin_support_departments.php" => va_message("SUPPORT_DEPARTMENTS_MSG"),
	);

	$operation = get_param("operation");
	$dep_id = get_param("dep_id");

	// update show_for_user field
	if ($operation == "show-yes") {
		$sql = " UPDATE ".$table_prefix."support_departments ";
		$sql.= " SET show_for_user=1 ";
		$sql.= " WHERE dep_id=" . $db->tosql($dep_id, INTEGER);
		$db->query($sql);
	} else if ($operation == "show-no") {
		$sql = " UPDATE ".$table_prefix."support_departments ";
		$sql.= " SET show_for_user=0 ";
		$sql.= " WHERE dep_id=" . $db->tosql($dep_id, INTEGER);
		$db->query($sql);
	} else if ($operation == "default-yes") {
		$sql = " UPDATE ".$table_prefix."support_departments ";
		$sql.= " SET is_default=1 ";
		$sql.= " WHERE dep_id=" . $db->tosql($dep_id, INTEGER);
		$db->query($sql);
	} else if ($operation == "default-no") {
		$sql = " UPDATE ".$table_prefix."support_departments ";
		$sql.= " SET is_default=0 ";
		$sql.= " WHERE dep_id=" . $db->tosql($dep_id, INTEGER);
		$db->query($sql);
	}

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_support_departments.html");

	$t->set_var("admin_support_href", "admin_support.php");
	$t->set_var("admin_support_department_href", "admin_support_department.php");
	$t->set_var("admin_support_departments_href", "admin_support_departments.php");

	$asds = new VA_URL("admin_support_departments.php", false);
	$asds->add_parameter("sort_ord", REQUEST, "sort_ord");
	$asds->add_parameter("sort_dir", REQUEST, "sort_dir");
	$asds->add_parameter("page", REQUEST, "page");

	$asd = new VA_URL("admin_support_department.php", false);
	$asd->add_parameter("sort_ord", REQUEST, "sort_ord");
	$asd->add_parameter("sort_dir", REQUEST, "sort_dir");
	$asd->add_parameter("page", REQUEST, "page");
	$t->set_var("admin_support_dep_new_url", $asd->get_url());


	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_support_departments.php");
	$s->set_parameters(false, true, true, false);
	$s->set_sorter(va_message("ID_MSG"), "sorter_dep_id", "1", "dep_id", "", "", true);
	$s->set_sorter(va_message("SUPPORT_DEPARTMENT_FIELD"), "sorter_dep_name", "2", "dep_name");
	$s->set_sorter(va_message("SHOW_FOR_USER_MSG"), "sorter_show_for_user", "3", "show_for_user");
	$s->set_sorter(va_message("DEFAULT_MSG"), "sorter_is_default", "4", "is_default");
	$s->set_sorter(va_message("SORT_ORDER_MSG"), "sorter_dep_order", "5", "dep_order", "dep_order, dep_name", "dep_order DESC, dep_name DESC", true);

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_support_departments.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "support_departments");
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	// get types first
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = "SELECT * FROM " . $table_prefix . "support_departments ";
	$sql .= $s->order_by;
	$support_deps = array(); $deps_ids = array();
	$db->query($sql);
	while ($db->next_record()) {
		$dep_id = $db->f("dep_id");
		$deps_ids[] = $dep_id;
		$support_deps[$dep_id] = $db->Record;
		$support_deps[$dep_id]["admins"] = array();
		$support_deps[$dep_id]["sites"] = array();
	}

	// get admins for departments 
	$sql  = " SELECT sud.dep_id , a.admin_name FROM " . $table_prefix . "support_users_departments sud ";
	$sql .= " INNER JOIN " . $table_prefix . "admins a ON a.admin_id=sud.admin_id ";
	$sql .= " WHERE sud.dep_id IN (" . $db->tosql($deps_ids, INTEGER_LIST) . ")";
	$db->query($sql);
	while ($db->next_record()) {
		$dep_id = $db->f("dep_id");
		$admin_name = $db->f("admin_name");
		$support_deps[$dep_id]["admins"][] = $admin_name;
	}

	// get sites for departments 
	$sql  = " SELECT sds.dep_id, s.site_name FROM " . $table_prefix . "support_departments_sites sds ";
	$sql .= " INNER JOIN " . $table_prefix . "sites s ON s.site_id=sds.site_id ";
	$sql .= " WHERE sds.dep_id IN (" . $db->tosql($deps_ids, INTEGER_LIST) . ")";
	$db->query($sql);
	while ($db->next_record()) {
		$dep_id = $db->f("dep_id");
		$site_name = get_translation($db->f("site_name"));
		$support_deps[$dep_id]["sites"][] = $site_name;
	}


	if (count($support_deps)) {

		$t->set_var("no_records", "");
		foreach ($support_deps as $dep_id => $data) {

			$is_default = $data["is_default"];
			$show_for_user = $data["show_for_user"];
			$dep_order = $data["dep_order"];
			$dep_name = get_translation($data["dep_name"]);

			$admins_all = $data["admins_all"];
			$admins = $data["admins"];
			$sites_all = $data["sites_all"];
			$sites = $data["sites"];

			$asds->add_parameter("dep_id", CONSTANT, $dep_id);
			$asd->add_parameter("dep_id", CONSTANT, $dep_id);
			if ($show_for_user) {
				$show_for_user_desc = va_message("YES_MSG");
				$show_for_user_class= "yes-option";
				$asds->add_parameter("operation", CONSTANT, "show-no");
			} else {
				$show_for_user_desc = va_message("NO_MSG");
				$show_for_user_class= "no-option";
				$asds->add_parameter("operation", CONSTANT, "show-yes");
			}
			$t->set_var("dep_id", $dep_id);
			$t->set_var("dep_name", htmlspecialchars($dep_name));
			$t->set_var("dep_order", $dep_order);
			$t->set_var("show_for_user_desc", $show_for_user_desc);
			$t->set_var("show_for_user_class", $show_for_user_class);
			$t->set_var("show_for_user_url", $asds->get_url());

			if ($is_default) {
				$is_default_desc = va_message("YES_MSG");
				$is_default_class= "yes-option";
				$asds->add_parameter("operation", CONSTANT, "default-no");
			} else {
				$is_default_desc = va_message("NO_MSG");
				$is_default_class= "no-option";
				$asds->add_parameter("operation", CONSTANT, "default-yes");
			}
			$t->set_var("is_default_desc", $is_default_desc);
			$t->set_var("is_default_class", $is_default_class);
			$t->set_var("is_default_url", $asds->get_url());

			$t->set_var("admin_support_dep_url", $asd->get_url());

			// parse department managers 
			$t->set_var("extra_admins", "");
			$t->set_var("admin_list", "");
			$t->set_var("admin_single", "");
			if ($admins_all || count($admins) <= 1) {
				if ($admins_all) {
					$t->set_var("admin_name", va_message("ALL_MSG"));
				} else if (count($admins) == 1) {
					$t->set_var("admin_name", htmlspecialchars($admins[0]));
				} else {
					$t->set_var("admin_name", "&ndash;");
				}
				$t->sparse("admin_single", false);
			} else {
				$t->set_var("admin_summary", count($admins)." ".va_message("ADMINS_MSG"));
				foreach ($admins as $admin_name) {
					$t->set_var("admin_name", htmlspecialchars($admin_name));
					$t->sparse("extra_admins", true);
				}
				$t->sparse("admin_list", false);
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

	$t->pparse("main");

