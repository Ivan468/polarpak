<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_export_fields.php                                  ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
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
	$t->set_file("main","admin_export_fields.html");

	$t->set_var("admin_export_templates_href", "admin_export_templates.php");
	$t->set_var("admin_export_template_href", "admin_export_template.php");
	$t->set_var("admin_export_fields_href", "admin_export_fields.php");
	$t->set_var("admin_export_field_href", "admin_export_field.php");
	$t->set_var("admin_export_fields_order_href", "admin_export_fields_order.php");

	$t->set_var("admin_lookup_tables_href", "admin_lookup_tables.php");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_export_fields.php");
	$s->set_default_sorting(3, "asc");
	$s->set_sorter(ID_MSG, "sorter_field_id", "1", "field_id");
	$s->set_sorter(FIELD_TITLE_MSG, "sorter_field_title", "2", "field_title");
	$s->set_sorter(FIELD_ORDER_MSG, "sorter_field_order", "3", "field_order");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_export_fields.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set up variables for navigator
	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "export_fields ";
	$sql .= " WHERE template_id=" . $db->tosql($template_id, INTEGER);
	$db->query($sql);
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$admin_field_url = new VA_URL("admin_export_field.php", false);
	$admin_field_url->add_parameter("template_id", CONSTANT, $template_id);
	$admin_field_url->add_parameter("page", REQUEST, "page");
	$t->set_var("admin_export_fields_order_url", $admin_field_url->get_url("admin_export_fields_order.php"));
	$t->set_var("admin_export_field_new_url", $admin_field_url->get_url("admin_export_field.php"));

	$sql  = " SELECT * FROM " . $table_prefix . "export_fields ";
	$sql .= " WHERE template_id=" . $db->tosql($template_id, INTEGER);
	$sql .= $s->order_by;

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query($sql);
	if($db->next_record())
	{

		$admin_field_url->add_parameter("field_id", DB, "field_id");
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do
		{
			$t->set_var("field_id", $db->f("field_id"));
			$t->set_var("field_title", htmlspecialchars($db->f("field_title")));
			$t->set_var("field_order", $db->f("field_order"));
			$t->set_var("admin_export_field_url", $admin_field_url->get_url());

			$t->parse("records", true);
		} while($db->next_record());
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