<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_custom_forms.php                                   ***
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

	check_admin_security("custom_blocks");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_custom_forms.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_custom_forms_sent_href", "admin_custom_forms_sent.php");
	
	$admin_custom_form_url = new VA_URL("admin_custom_form.php", true);
	$t->set_var("admin_custom_form_new_url", $admin_custom_form_url->get_url());

	$admin_custom_form_url->add_parameter("form_id", DB, "form_id");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_custom_forms.php");
	$s->set_sorter(ID_MSG, "sorter_form_id", "1", "form_id");
	$s->set_sorter("Form Name", "sorter_form_name", "2", "form_name");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_custom_forms.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "custom_forms");
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query("SELECT * FROM " . $table_prefix . "custom_forms " . $s->order_by);
	if($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do
		{
			$t->set_var("form_id", $db->f("form_id"));
		
			$form_name = get_translation($db->f("form_name"));

			$t->set_var("form_name",  $form_name);

			$t->set_var("admin_custom_form_url", $admin_custom_form_url->get_url());


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