<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_cms_layouts.php                                    ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");

	check_admin_security("cms_settings");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_cms_layouts.html");

	$t->set_var("admin_href", "admin.php");
	
	$admin_cms_layout_url = new VA_URL("admin_cms_layout.php", true);
	$t->set_var("admin_cms_layout_new_url", $admin_cms_layout_url->get_url());

	$admin_cms_layout_url->add_parameter("layout_id", DB, "layout_id");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_cms_layouts.php");
	$s->set_sorter(ID_MSG, "sorter_layout_id", "1", "layout_id", "", "", true);
	$s->set_sorter(NAME_MSG, "sorter_layout_name", "2", "layout_name");
	$s->set_sorter(ADMIN_ORDER_MSG, "sorter_layout_order", "3", "layout_order");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_cms_layouts.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "cms_layouts ");
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query("SELECT * FROM " . $table_prefix . "cms_layouts " . $s->order_by);
	if($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do
		{
			$t->set_var("layout_id", $db->f("layout_id"));
		
			$layout_name = get_translation($db->f("layout_name"));
			$layout_order = $db->f("layout_order");

			$t->set_var("layout_name",  $layout_name);
			$t->set_var("layout_order", $layout_order);

			$t->set_var("admin_cms_layout_url", $admin_cms_layout_url->get_url());


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