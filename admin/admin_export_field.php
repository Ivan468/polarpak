<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_export_field.php                                   ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/record.php");

	check_admin_security("static_tables");

	$field_id = get_param("field_id");
	$template_id = get_param("template_id");
	if ($field_id) {
		$sql  = " SELECT et.template_id, et.template_name, et.table_name ";
		$sql .= " FROM (" . $table_prefix . "export_fields ef ";
		$sql .= " INNER JOIN " . $table_prefix . "export_templates et ON ef.template_id=et.template_id) ";
		$sql .= " WHERE ef.field_id=" . $db->tosql($field_id, INTEGER);
	} else {
		$sql  = " SELECT template_id, template_name, table_name FROM " . $table_prefix . "export_templates ";
		$sql .= " WHERE template_id=" . $db->tosql($template_id, INTEGER);
	}
	$db->query($sql);
	if ($db->next_record()) {
		$template_id = $db->f("template_id");
		$template_name = $db->f("template_name");
		$table_name = $db->f("table_name");
	} else {
		header("Location: admin_export_templates.php");
		exit;
	}

	$default_field_order = 0;
	if (!$field_id) {
		$sql  = " SELECT MAX(field_order) AS max_order FROM " . $table_prefix . "export_fields ";
		$sql .= " WHERE template_id=" . $db->tosql($template_id, INTEGER);
		$default_field_order = get_db_value($sql) + 1;
	}


  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_export_field.html");
	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_export_template_href",   "admin_export_template.php");
	$t->set_var("admin_export_templates_href", "admin_export_templates.php");
	$t->set_var("admin_export_field_href",   "admin_export_field.php");
	$t->set_var("admin_export_fields_href", "admin_export_fields.php");
	$t->set_var("admin_export_custom_help_href", "admin_export_custom_help.php");
	$t->set_var("table_name", $table_name);
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", EXPORT_FIELD_MSG, CONFIRM_DELETE_MSG));

	$r = new VA_Record($table_prefix . "export_fields");
	$r->return_page  = "admin_export_fields.php";
	$r->add_hidden("page", INTEGER);

	$r->add_where("field_id", INTEGER);
	$r->add_hidden("template_id", INTEGER);
	$r->change_property("template_id", USE_IN_INSERT, true);
	$r->change_property("template_id", DEFAULT_VALUE, $template_id);
	$r->add_textbox("field_title", TEXT, FIELD_TITLE_MSG);
	$r->change_property("field_title", REQUIRED, true);
	$r->add_textbox("field_order", TEXT, FIELD_ORDER_MSG);
	$r->change_property("field_order", REQUIRED, true);
	$r->change_property("field_order", DEFAULT_VALUE, $default_field_order);
	$r->add_textbox("field_source", TEXT, FIELD_SOURCE_MSG);


	$r->process();

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

?>