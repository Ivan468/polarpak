<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_item_types_select.php                              ***
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

	check_admin_security("product_types");

	$sw = trim(get_param("sw"));
	$form_name = get_param("form_name");
	$field_name = get_param("field_name");
	$id_name = get_param("id_name");
	$selection_type = get_param("selection_type");

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_item_types_select.html");
	$t->set_var("admin_item_types_select_href", "admin_item_types_select.php");
	$t->set_var("sw", htmlspecialchars($sw));
	$t->set_var("form_name", htmlspecialchars($form_name));
	$t->set_var("field_name", htmlspecialchars($field_name));
	$t->set_var("id_name", htmlspecialchars($id_name));
	$t->set_var("selection_type", htmlspecialchars($selection_type));

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_item_types_select.php");
	$s->set_parameters(false, true, true, false);
	$s->set_sorter(ID_MSG, "sorter_type_id", "1", "type_id");
	$s->set_sorter(PROD_TYPE_MSG, "sorter_type_name", "2", "type_name");

	$where = "";
	$sa = array();
	if ($sw) {
		$sa = explode(" ", $sw);
		for($si = 0; $si < sizeof($sa); $si++) {
			if ($where) { $where .= " AND "; }
			else { $where .= " WHERE "; }

			$sw_sql = $db->tosql($sa[$si], TEXT, false);
			$where .= " (it.item_type_name LIKE '%" . $sw_sql . "%')";
		}
	}

	$sql = " SELECT COUNT(*) FROM " . $table_prefix . "item_types it " . $where;
	$db->query($sql);
	$db->next_record();
	$total_records = $db->f(0);

	// set up variables for navigator
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_item_types_select.php");
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);

	$sql  = " SELECT it.item_type_id, it.item_type_name ";
	$sql .= "	FROM " . $table_prefix . "item_types it ";
	$sql .= $where;
	$sql .= $s->order_by;
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query($sql);
	if ($db->next_record()) {
		$t->parse("sorters", false);
		do {
			$type_id = $db->f("item_type_id");
			$type_name = get_translation($db->f("item_type_name"));
			$type_name_js = str_replace("'", "\\'", htmlspecialchars($type_name));

			if(is_array($sa)) {
				for($si = 0; $si < sizeof($sa); $si++) {
					$type_name = preg_replace ("/(" . $sa[$si] . ")/i", "<font color=blue><b>\\1</b></font>", $type_name);					
				}
			}

			$t->set_var("type_id", $type_id);
			$t->set_var("type_name", $type_name);
			$t->set_var("type_name_js", $type_name_js);

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