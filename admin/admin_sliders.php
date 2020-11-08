<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_sliders.php                                        ***
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

	check_admin_security("sliders");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_sliders.html");

	$t->set_var("admin_href", "admin.php");

	$admin_slider_url = new VA_URL("admin_slider.php", true);
	$t->set_var("admin_slider_new_url", $admin_slider_url->get_url());

	$admin_slider_url->add_parameter("slider_id", DB, "slider_id");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_sliders.php");
	$s->set_default_sorting(1,"asc");
	$s->set_sorter(ID_MSG, "sorter_slider_id", "1", "slider_id");
	$s->set_sorter(NAME_MSG, "sorter_slider_name", "2", "slider_name");
	$s->set_sorter(ADMIN_TITLE_MSG, "sorter_slider_title", "3", "slider_title");
	//
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_sliders.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "sliders");
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query("SELECT * FROM " . $table_prefix . "sliders " . $s->order_by); 
	if($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do
		{
			$t->set_var("slider_id", $db->f("slider_id"));
			$slider_name = get_translation($db->f("slider_name"));
			$slider_title = get_translation($db->f("slider_title"));
			$t->set_var("slider_name",  $slider_name);
			$t->set_var("slider_title", $slider_title);


			$t->set_var("admin_slider_url", $admin_slider_url->get_url("admin_slider.php"));
 			$t->set_var("admin_slider_items_url", $admin_slider_url->get_url("admin_slider_items.php"));

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