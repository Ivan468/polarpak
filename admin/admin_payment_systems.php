<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_payment_systems.php                                ***
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

	check_admin_security("payment_systems");

	$operation = strtolower(get_param("operation"));
	$payment_id = get_param("payment_id");
	$sw = trim(get_param("sw")); // search words
	
	if (strlen($operation) && $payment_id) {
		if (($operation) == "off") {
			$sql  = " UPDATE " . $table_prefix . "payment_systems SET is_active=0 ";
			$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
			$db->query($sql);
		} elseif (($operation) == "on") {
			$sql  = " UPDATE " . $table_prefix . "payment_systems SET is_active=1 ";
			$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
			$db->query($sql);
		}
		if (($operation) == "call-off") {
			$sql  = " UPDATE " . $table_prefix . "payment_systems SET is_call_center=0 ";
			$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
			$db->query($sql);
		} elseif (($operation) == "call-on") {
			$sql  = " UPDATE " . $table_prefix . "payment_systems SET is_call_center=1 ";
			$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
			$db->query($sql);
		}
		if ($operation == "default-on" && strlen($payment_id)) {
			$sql  = " UPDATE " . $table_prefix . "payment_systems SET is_default=0 ";
			$sql .= " WHERE payment_id<>" . $db->tosql($payment_id, INTEGER);
			$db->query($sql);
			$sql  = " UPDATE " . $table_prefix . "payment_systems SET is_default=1 ";
			$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
			$db->query($sql);
		} else if ($operation == "default-off" && strlen($payment_id)) {
			$sql  = " UPDATE " . $table_prefix . "payment_systems SET is_default=0 ";
			$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
			$db->query($sql);
		}
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_payment_systems.html");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_main", "admin.php");
	$t->set_var("admin_payment_system_href",   "admin_payment_system.php");
	$t->set_var("admin_payment_predefined_href", "admin_payment_predefined.php");
	$t->set_var("admin_import_payment_system_href", "admin_import_payment_system.php");
	$t->set_var("admin_credit_card_info_href", "admin_credit_card_info.php");
	$t->set_var("admin_order_final_href",      "admin_order_final.php");
	$t->set_var("admin_recurring_settings_href", "admin_recurring_settings.php");
	$t->set_var("sw", htmlspecialchars($sw));


	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_payment_systems.php");
	$s->set_parameters(false, true, true, false);
	$s->set_default_sorting(4, "asc");
	$s->set_sorter(ID_MSG, "sorter_payment_id", "1", "payment_id");
	$s->set_sorter(PAYMENT_SYSTEM_NAME_MSG, "sorter_payment_name", "2", "payment_name");
	$s->set_sorter(ADMIN_ORDER_MSG, "sorter_payment_order", "3", "payment_order, payment_id");
	$s->set_sorter(ACTIVE_MSG, "sorter_is_active", "4", "is_active", "is_active DESC, payment_order, payment_id ", "is_active ASC");
	$s->set_sorter(CALL_CENTER_MSG, "sorter_is_call_center", "5", "is_call_center", "is_call_center DESC, payment_order, payment_id ", "is_call_center ASC");
	$s->set_sorter(DEFAULT_MSG, "sorter_is_default", "6", "is_default");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_payment_systems.php");

	$where = "";
	if ($sw) {
		$sa = explode(" ", $sw);
		for($si = 0; $si < sizeof($sa); $si++) {
			$kw = trim($sa[$si]);
			$kw = str_replace("%","\%",$kw);
			if ($kw) {
				if ($where) {
					$where .= " AND ";
				} else {
					$where .= " WHERE ";
				}
				$where .= " (ps.payment_name LIKE '%" . $db->tosql($kw, TEXT, false) . "%'";
				$where .= " OR ps.user_payment_name LIKE '%" . $db->tosql($kw, TEXT, false) . "%' ";
				$where .= " OR ps.payment_url LIKE '%" . $db->tosql($kw, TEXT, false) . "%' ";
				$where .= " OR ps.advanced_php_lib LIKE '%" . $db->tosql($kw, TEXT, false) . "%')";
			}
		}
	}

	// set up variables for navigator
	$sql = "SELECT COUNT(*) FROM " . $table_prefix . "payment_systems ps ";
	$db->query($sql . $where);
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 10;
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);

	$sql = "SELECT * FROM " . $table_prefix . "payment_systems ps ";
	$sql.= $where;
	$sql.= $s->order_by;
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query($sql);
	if ($db->next_record())
	{
		$t->set_var("no_records", "");
		do
		{
			$payment_id = $db->f("payment_id");
			$is_active = $db->f("is_active");
			$is_default = $db->f("is_default");
			$is_call_center = $db->f("is_call_center");
			$active = ($is_active == 1) ? "<b>".YES_MSG."</b>" : NO_MSG;
			$default_desc = ($is_default == 1) ? "<b>".YES_MSG."</b>" : NO_MSG;
			$call_center_desc = ($is_call_center == 1) ? "<b>".YES_MSG."</b>" : NO_MSG;
			$operation = ($is_active == 1) ? "Off" : "On";
			$call_operation = ($is_call_center == 1) ? "call-off" : "call-on";
			$default_operation = ($is_default == 1) ? "default-off" : "default-on";
			$t->set_var("payment_id", $payment_id);
			$t->set_var("payment_name", get_translation($db->f("payment_name")));
			$t->set_var("payment_order", $db->f("payment_order"));
			$t->set_var("active", $active);
			$t->set_var("default_desc", $default_desc);
			$t->set_var("call_center_desc", $call_center_desc);
			$t->set_var("operation", $operation);
			$t->set_var("default_operation", $default_operation);
			$t->set_var("call_operation", $call_operation);

			$t->parse("records", true);
		} while ($db->next_record());
	}
	else
	{
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	$t->set_var("admin_href", "admin.php");
	$t->pparse("main");

?>