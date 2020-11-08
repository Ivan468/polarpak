<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_export_templates.php                               ***
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
	include_once ($root_folder_path . "messages/".$language_code."/download_messages.php");

	check_admin_security("static_tables");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_export_templates.html");

	$t->set_var("admin_export_templates_href", "admin_export_templates.php");
	$t->set_var("admin_export_template_href", "admin_export_template.php");
	$t->set_var("admin_export_fields_href", "admin_export_fields.php");
	$t->set_var("admin_lookup_tables_href", "admin_lookup_tables.php");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_export_templates.php");
	$s->set_parameters(false, true, true, false);
	$s->set_sorter(va_message("ID_MSG"), "sorter_template_id", "1", "template_id", "", "", true);
	$s->set_sorter(va_message("TEMPLATE_NAME_MSG"), "sorter_template_name", "2", "template_name");
	$s->set_sorter(va_message("DATABASE_TABLE_MSG"), "sorter_table_name", "3", "table_name");
	$s->set_sorter(va_message("CRON_JOB_MSG"), "sorter_is_cronjob", "4", "is_cronjob");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_export_templates.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "export_templates ");
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query("SELECT * FROM " . $table_prefix . "export_templates " . $s->order_by);
	if($db->next_record())
	{
		$table_names = array(
			"items" => va_message("PRODUCTS_MSG"), 
			"categories" => va_message("PRODUCT_CATEGORIES_MSG"),
			"orders" => va_message("ORDERS_MSG"),
			"items_files" => va_message("DOWNLOADABLE_FILES_MSG"),
			"items_prices" => va_message("QUANTITY_PRICES_MSG"),
			"items_properties_values" => va_message("OPTIONS_VALUES_MSG"),
			"users" => va_message("USERS_MSG"),
			"newsletters_users" => va_message("NEWSLETTER_USERS_MSG"),
			"registration_list" => va_message("REGISTERED_PRODUCTS_MSG"),
		);

		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do
		{
			$table_name = $db->f("table_name");
			if (isset($table_names[$table_name])) {
				$table_name = $table_names[$table_name];
			}
			$cronjob_val = $db->f("is_cronjob");
			$is_cronjob = ($cronjob_val == 1) ? "<b>".va_message("YES_MSG")."</b>" : va_message("NO_MSG");
			$t->set_var("template_id", $db->f("template_id"));
			$t->set_var("template_name", htmlspecialchars($db->f("template_name")));
			$t->set_var("is_cronjob", $is_cronjob);

			$t->set_var("table_name", $table_name);

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
