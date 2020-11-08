<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_shippings_select.php                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
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
  $t->set_file("main","admin_shippings_select.html");
	$t->set_var("admin_shippings_select_href", "admin_shippings_select.php");
	$t->set_var("sw", htmlspecialchars($sw));
	$t->set_var("form_name", htmlspecialchars($form_name));
	$t->set_var("field_name", htmlspecialchars($field_name));
	$t->set_var("selection_type", htmlspecialchars($selection_type));

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_shippings_select.php");
	$s->set_parameters(false, true, true, false);
	$s->set_sorter(ID_MSG, "sorter_shipping_id", "1", "st.shipping_type_id");
	$s->set_sorter(SHIPPING_MODULE_NAME_MSG, "sorter_module_name", "2", "sm.shipping_module_name");
	$s->set_sorter(SHIPPING_TYPE_MSG, "sorter_shipping_name", "3", "st.shipping_type_desc");

	$where = "";
	$sa = array();
	if ($sw) {
		$sa = explode(" ", $sw);
		for($si = 0; $si < sizeof($sa); $si++) {
			if ($where) { $where .= " AND "; }
			else { $where .= " WHERE "; }

			$sw_sql = $db->tosql($sa[$si], TEXT, false);
			$where .= " (st.shipping_type_code LIKE '%" . $sw_sql . "%'";
			$where .= " OR st.shipping_type_desc LIKE '%" . $sw_sql . "%')";
		}
	}

	$sql  = " SELECT COUNT(*) ";
	$sql .= " FROM (" . $table_prefix . "shipping_types st ";
	$sql .= " INNER JOIN " . $table_prefix . "shipping_modules sm ON st.shipping_module_id=sm.shipping_module_id) ";
	$sql .= $where;
	$db->query($sql);
	$db->next_record();
	$total_records = $db->f(0);

	// set up variables for navigator
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_shippings_select.php");
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);

	$sql  = " SELECT st.shipping_type_id, sm.shipping_module_name, st.shipping_type_desc, ";
	$sql .= " sm.is_active AS module_active, st.is_active AS type_active ";
	$sql .= " FROM (" . $table_prefix . "shipping_types st ";
	$sql .= " INNER JOIN " . $table_prefix . "shipping_modules sm ON st.shipping_module_id=sm.shipping_module_id) ";
	$sql .= $where;
	$sql .= $s->order_by;
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query($sql);
	if ($db->next_record()) {
		$t->parse("shippings_sorters", false);
		do {
			$shipping_id = $db->f("shipping_type_id");
			$shipping_module = $db->f("shipping_module_name");
			$shipping_name = $db->f("shipping_type_desc");
			$module_active = $db->f("module_active");
			$type_active = $db->f("type_active");
			$shipping_active = ($module_active && $type_active);
			if ($shipping_active) {
				$shipping_active = "<font color=\"blue\">" . YES_MSG . "</font>";
			} else {
				$shipping_active = "<font color=\"silver\">" . NO_MSG . "</font>";
			} 

			$shipping_name_js = str_replace("'", "\\'", htmlspecialchars($shipping_module . " &gt; " . $shipping_name));

			if(is_array($sa)) {
				for($si = 0; $si < sizeof($sa); $si++) {
					$shipping_name = preg_replace ("/(" . $sa[$si] . ")/i", "<font color=blue><b>\\1</b></font>", $shipping_name);					
				}
			}

			$t->set_var("shipping_id", $shipping_id);
			$t->set_var("shipping_name", $shipping_name);
			$t->set_var("shipping_name_js", $shipping_name_js);
			$t->set_var("shipping_module", $shipping_module);
			$t->set_var("shipping_active", $shipping_active);


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