<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_shipping_companies.php                             ***
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

	$custom_breadcrumb = array(
		"admin_global_settings.php" => SETTINGS_MSG,
		$orders_pages_site_url."admin_order_info.php" => ORDERS_MSG,
		"admin_shipping_companies.php" => SHIPPING_COMPANIES_MSG,
	);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_shipping_companies.html");

	$t->set_var("admin_shipping_company_href", "admin_shipping_company.php");
	$t->set_var("admin_shipping_companies_href", "admin_shipping_companies.php");
	$t->set_var("admin_lookup_tables_href", "admin_lookup_tables.php");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_shipping_companies.php");
	$s->set_sorter(ID_MSG, "sorter_shipping_company_id", "1", "shipping_company_id", "", "", true);
	$s->set_sorter(COMPANY_NAME_FIELD, "sorter_company_name", "2", "company_name");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_shipping_companies.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "shipping_companies ");
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query("SELECT * FROM " . $table_prefix . "shipping_companies " . $s->order_by);
	if($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do
		{
			$t->set_var("shipping_company_id", $db->f("shipping_company_id"));
			$t->set_var("company_name", htmlspecialchars($db->f("company_name")));

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