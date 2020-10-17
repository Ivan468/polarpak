<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_privileges_select.php                              ***
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

	check_admin_security("admins_groups");

	$sw = trim(get_param("sw"));
	$form_name = get_param("form_name");
	$field_name = get_param("field_name");
	$id_name = get_param("id_name");
	$selection_type = get_param("selection_type");

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_privileges_select.html");
	$t->set_var("admin_privileges_select_href", "admin_privileges_select.php");
	$t->set_var("sw", htmlspecialchars($sw));
	$t->set_var("form_name", htmlspecialchars($form_name));
	$t->set_var("field_name", htmlspecialchars($field_name));
	$t->set_var("id_name", htmlspecialchars($id_name));
	$t->set_var("selection_type", htmlspecialchars($selection_type));

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_privileges_select.php");
	$s->set_parameters(false, true, true, false);
	$s->set_sorter(ID_MSG, "sorter_privilege_id", "1", "privilege_id");
	$s->set_sorter(GROUP_NAME_MSG, "sorter_privilege_name", "2", "privilege_name");

	$where = "";
	$sa = array();
	if ($sw) {
		$sa = explode(" ", $sw);
		for($si = 0; $si < sizeof($sa); $si++) {
			if ($where) { $where .= " AND "; }
			else { $where .= " WHERE "; }

			$sw_sql = $db->tosql($sa[$si], TEXT, false);
			$where .= " (ap.privilege_name LIKE '%" . $sw_sql . "%')";
		}
	}

	$sql = " SELECT COUNT(*) FROM " . $table_prefix . "admin_privileges ap " . $where;
	$db->query($sql);
	$db->next_record();
	$total_records = $db->f(0);

	// set up variables for navigator
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_privileges_select.php");
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);

	$sql  = " SELECT privilege_id, privilege_name ";
	$sql .= "	FROM " . $table_prefix . "admin_privileges ap ";
	$sql .= $where;
	$sql .= $s->order_by;
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query($sql);
	if ($db->next_record()) {
		$t->parse("sorters", false);
		do {
			$privilege_id = $db->f("privilege_id");
			$privilege_name = get_translation($db->f("privilege_name"));
			$privilege_name_js = str_replace("'", "\\'", htmlspecialchars($privilege_name));

			if(is_array($sa)) {
				for($si = 0; $si < sizeof($sa); $si++) {
					$privilege_name = preg_replace ("/(" . $sa[$si] . ")/i", "<font color=blue><b>\\1</b></font>", $privilege_name);					
				}
			}

			$t->set_var("privilege_id", $privilege_id);
			$t->set_var("privilege_name", $privilege_name);
			$t->set_var("privilege_name_js", $privilege_name_js);

			$t->parse("records", true);
		} while ($db->next_record());
	}

	if (strlen($sw)) {
		$found_message = str_replace("{found_records}", $total_records, FOUND_RECORDS_MSG);
		$found_message = str_replace("{search_string}", htmlspecialchars($sw), $found_message);
		$t->set_var("found_message", $found_message);
		$t->parse("search_results", false);
	}

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");


?>