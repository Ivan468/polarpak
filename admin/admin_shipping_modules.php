<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_shipping_modules.php                               ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once("./admin_common.php");

	check_admin_security("shipping_methods");

	$operation = get_param("operation");
	$module_id = get_param("module_id");
	
	if (strlen($operation) && $module_id) {
		if (strtolower($operation) == "off") {
			$sql  = " UPDATE " . $table_prefix . "shipping_modules SET is_active=0 ";
			$sql .= " WHERE shipping_module_id=" . $db->tosql($module_id, INTEGER);
			$db->query($sql);
		} elseif (strtolower($operation) == "on") {
			$sql  = " UPDATE " . $table_prefix . "shipping_modules SET is_active=1 ";
			$sql .= " WHERE shipping_module_id=" . $db->tosql($module_id, INTEGER);
			$db->query($sql);
		} elseif (strtolower($operation) == "def_off") {
			$sql  = " UPDATE " . $table_prefix . "shipping_modules SET is_default=0 ";
			$sql .= " WHERE shipping_module_id=" . $db->tosql($module_id, INTEGER);
			$db->query($sql);
		} elseif (strtolower($operation) == "def_on") {
			$sql  = " UPDATE " . $table_prefix . "shipping_modules SET is_default=1 ";
			$sql .= " WHERE shipping_module_id=" . $db->tosql($module_id, INTEGER);
			$db->query($sql);
		} elseif (strtolower($operation) == "cc_off") {
			$sql  = " UPDATE " . $table_prefix . "shipping_modules SET is_call_center=0 ";
			$sql .= " WHERE shipping_module_id=" . $db->tosql($module_id, INTEGER);
			$db->query($sql);
		} elseif (strtolower($operation) == "cc_on") {
			$sql  = " UPDATE " . $table_prefix . "shipping_modules SET is_call_center=1 ";
			$sql .= " WHERE shipping_module_id=" . $db->tosql($module_id, INTEGER);
			$db->query($sql);
		}
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_shipping_modules.html");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_lookup_tables_href", "admin_lookup_tables.php");
	$t->set_var("admin_shipping_module_href", "admin_shipping_module.php");
	$t->set_var("admin_shipping_types_href", "admin_shipping_types.php");
	$t->set_var("admin_order_properties_href", "admin_order_properties.php");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_shipping_modules.php");
	$s->set_sorter(ID_MSG, "sorter_shipping_module_id", "1", "shipping_module_id", "", "", true);
	$s->set_sorter(SHIPPING_MODULE_MSG, "sorter_shipping_module_name", "2", "shipping_module_name");
	$s->set_sorter(ACTIVE_MSG, "sorter_is_active", "3", "is_active");
	$s->set_sorter(DEFAULT_MSG, "sorter_is_default", "4", "is_default");
	$s->set_sorter(CALL_CENTER_MSG, "sorter_is_call_center", "5", "is_call_center");
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_shipping_modules.php");

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "shipping_modules");
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = " SELECT shipping_module_id, shipping_module_name, is_active, is_default, is_call_center ";
	$sql .= " FROM " . $table_prefix . "shipping_modules" . $s->order_by;
	$db->query($sql);
	if ($db->next_record())
	{
		$shipping_update_url = new VA_URL("admin_shipping_modules.php", true, array("module_id", "operation"));
		$shipping_update_url->add_parameter("module_id", DB, "shipping_module_id");

		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do {
			$is_active = ($db->f("is_active") == 1) ? "<b>" . YES_MSG . "</b>" : NO_MSG;
			$operation = ($db->f("is_active") == 1) ? "off" : "on";
			$is_default = ($db->f("is_default") == 1) ? "<b>" . YES_MSG . "</b>" : NO_MSG;
			$operation_default = ($db->f("is_default") == 1) ? "def_off" : "def_on";
			$is_call_center = ($db->f("is_call_center") == 1) ? "<b>" . YES_MSG . "</b>" : NO_MSG;
			$operation_call = ($db->f("is_call_center") == 1) ? "cc_off" : "cc_on";
			$t->set_var("is_active", $is_active);
			$t->set_var("is_default", $is_default);
			$t->set_var("is_call_center", $is_call_center);
			$t->set_var("shipping_module_id", $db->f("shipping_module_id"));
			$t->set_var("shipping_module_name", $db->f("shipping_module_name"));		

			$shipping_update_url->add_parameter("operation", CONSTANT, $operation);
			$t->set_var("shipping_active_url", $shipping_update_url->get_url());

			$shipping_update_url->add_parameter("operation", CONSTANT, $operation_default);
			$t->set_var("shipping_default_url", $shipping_update_url->get_url());

			$shipping_update_url->add_parameter("operation", CONSTANT, $operation_call);
			$t->set_var("shipping_call_url", $shipping_update_url->get_url());

			$t->parse("records", true);
		} while ($db->next_record());
	}
	else
	{
		$t->set_var("sorters", "");
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	$t->pparse("main");

?>