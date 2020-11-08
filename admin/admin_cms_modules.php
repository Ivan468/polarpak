<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_cms_modules.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path."includes/sorter.php");
	include_once($root_folder_path."includes/navigator.php");
	include_once($root_folder_path."messages/".$language_code."/forum_messages.php");
	include_once($root_folder_path."messages/".$language_code."/manuals_messages.php");
	include_once($root_folder_path."messages/".$language_code."/profiles_messages.php");

	check_admin_security("cms_settings");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_cms_modules.html");

	$t->set_var("admin_href", "admin.php");
	
	$admin_cms_module_url = new VA_URL("admin_cms_module.php", true);
	$t->set_var("admin_cms_module_new_url", $admin_cms_module_url->get_url());

	$admin_cms_module_url->add_parameter("module_id", DB, "module_id");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_cms_modules.php");
	$s->set_sorter(ID_MSG, "sorter_module_id", "1", "module_id", "", "", true);
	$s->set_sorter(NAME_MSG, "sorter_module_name", "2", "module_name");
	$s->set_sorter(ADMIN_ORDER_MSG, "sorter_module_order", "3", "module_order");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_cms_modules.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "cms_modules ");
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query("SELECT * FROM " . $table_prefix . "cms_modules " . $s->order_by);
	if($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do
		{
			$t->set_var("module_id", $db->f("module_id"));
		
			$module_name = get_translation($db->f("module_name"));
			$module_order = $db->f("module_order");
			$t->set_block("module_name", $module_name);
			$t->parse("module_name", false);

			$t->set_var("module_order", $module_order);

			$t->set_var("admin_cms_module_url", $admin_cms_module_url->get_url());


			$t->parse("records", true);
		} while($db->next_record());
	}
	else
	{
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	$t->pparse("main");

?>