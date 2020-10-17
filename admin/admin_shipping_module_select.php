<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_shipping_module_select.php                         ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once("../messages/".$language_code."/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security();

	$sw = trim(get_param("sw"));
	$form_name = get_param("form_name");
	$field_name = get_param("field_name");
	$selection_type = get_param("selection_type");

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_shipping_module_select.html");
	$t->set_var("admin_shipping_module_select_href", "admin_shipping_module_select.php");
	$t->set_var("sw", htmlspecialchars($sw));
	$t->set_var("form_name", htmlspecialchars($form_name));
	$t->set_var("field_name", htmlspecialchars($field_name));
	$t->set_var("selection_type", htmlspecialchars($selection_type));

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_shippings_select.php");
	$s->set_parameters(false, true, true, false);
	$s->set_sorter(ID_MSG, "sorter_module_id", "1", "sm.shipping_module_id");
	$s->set_sorter(SHIPPING_MODULE_NAME_MSG, "sorter_module_name", "2", "sm.shipping_module_name");

	$where = "";
	$sa = array();
	if ($sw) {
		$sa = explode(" ", $sw);
		for($si = 0; $si < sizeof($sa); $si++) {
			if ($where) { $where .= " AND "; }
			else { $where .= " WHERE "; }

			$sw_sql = $db->tosql($sa[$si], TEXT, false);
			$where .= " (sm.shipping_module_name LIKE '%" . $sw_sql . "%'";
			$where .= " OR sm.shipping_module_name LIKE '%" . $sw_sql . "%')";
		}
	}

	$sql  = " SELECT COUNT(*) ";
	$sql .= " FROM " . $table_prefix . "shipping_modules sm ";
	$sql .= $where;
	$db->query($sql);
	$db->next_record();
	$total_records = $db->f(0);

	// set up variables for navigator
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_shippings_select.php");
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);

	$sql  = " SELECT sm.shipping_module_id, sm.shipping_module_name, ";
	$sql .= " sm.is_active, sm.is_default ";
	$sql .= " FROM " . $table_prefix . "shipping_modules sm ";
	$sql .= $where;
	$sql .= $s->order_by;
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query($sql);
	if ($db->next_record()) {
		$t->parse("shippings_sorters", false);
		do {
			$shipping_module_id = $db->f("shipping_module_id");
			$shipping_module = $db->f("shipping_module_name");
			$is_active = $db->f("is_active");
			$is_default = $db->f("is_default");
			if ($is_active) {
				$module_active = "<font color=\"blue\">" . YES_MSG . "</font>";
			} else {
				$module_active = "<font color=\"silver\">" . NO_MSG . "</font>";
			} 
			if ($is_default) {
				$module_default = "<font color=\"blue\">" . YES_MSG . "</font>";
			} else {
				$module_default = "<font color=\"silver\">" . NO_MSG . "</font>";
			} 

			$shipping_module_js = str_replace("'", "\\'", htmlspecialchars($shipping_module));

			if(is_array($sa)) {
				for($si = 0; $si < sizeof($sa); $si++) {
					$shipping_module = preg_replace ("/(" . $sa[$si] . ")/i", "<font color=blue><b>\\1</b></font>", $shipping_module);					
				}
			}

			$t->set_var("module_id", $shipping_module_id);
			$t->set_var("shipping_module_id", $shipping_module_id);
			$t->set_var("shipping_module", $shipping_module);
			$t->set_var("shipping_module_js", $shipping_module_js);
			$t->set_var("module_active", $module_active);
			$t->set_var("module_default", $module_default);


			$t->parse("shippings", true);
		} while ($db->next_record());
	}

	if (strlen($sw)) {
		$found_message = str_replace("{found_records}", $total_records, FOUND_SHIPPINGS_MSG);
		$found_message = str_replace("{search_string}", htmlspecialchars($sw), $found_message);
		$t->set_var("found_message", $found_message);
		$t->parse("search_results", false);
	}

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");


?>