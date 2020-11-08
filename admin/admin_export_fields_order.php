<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_export_fields_order.php                            ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/record.php");

	check_admin_security("static_tables");

	$template_id = get_param("template_id");
	$sql  = " SELECT template_name FROM " . $table_prefix . "export_templates ";
	$sql .= " WHERE template_id=" . $db->tosql($template_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$template_name = $db->f("template_name");
	} else {
		header("Location: admin_export_templates.php");
		exit;
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_export_fields_order.html");

	$t->set_var("admin_export_fields_order_href", "admin_export_fields_order.php");

	$fields = array();

	$operation = get_param("operation");
	$return_page = "admin_export_fields.php?template_id=" . urlencode($template_id);

	if (strlen($operation))
	{
		if ($operation == "cancel") {
			header("Location: " . $return_page);
			exit;
		}
		if ($operation == "save") {
			$fields_list = get_param("fields_list");
			if ($fields_list) {
				$fields_ids = explode(",", $fields_list);
				for ($i = 0; $i < sizeof($fields_ids); $i++) {
					$sql  = " UPDATE " . $table_prefix . "export_fields ";
					$sql .= " SET field_order = " . intval($i + 1);
					$sql .= " WHERE field_id = " . $fields_ids[$i];
					$db->query($sql);
				}
			}
			header("Location: " . $return_page);
			exit;
		}
	} else {
		$sql  = " SELECT f.field_id, f.field_title ";
		$sql .= " FROM " . $table_prefix . "export_fields f ";
		$sql .= " WHERE f.template_id= " . $db->tosql($template_id, INTEGER);
		$sql .= " ORDER BY f.field_order, f.field_id DESC ";
		$db->query($sql);
		while($db->next_record()) {
			$field_id = $db->f("field_id");
			$field_order = $db->f("field_order");
			$field_title = get_translation($db->f("field_title"));
			$fields[] = array($field_id, $field_title);
		}
	}

	set_options($fields, "", "fields");

	$t->set_var("errors", "");
	$t->set_var("template_id", $template_id);

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

?>