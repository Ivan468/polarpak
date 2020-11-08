<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_custom_forms_sent.php                              ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
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
	$t->set_file("main","admin_custom_forms_sent.html");

	$t->set_var("admin_href", "admin.php");

	$form_id = get_param("form_id");
	
	$admin_custom_form_url = new VA_URL("admin_custom_form_sent.php", true);
	$admin_custom_form_url->add_parameter("sent_form_id", DB, "sent_form_id");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_custom_forms_sent.php");
	$s->set_sorter("Form Name", "sorter_form_name", "1", "cf.form_name");
	$s->set_sorter("Remote Address", "sorter_remote_address", "2", "cfs.remote_address");
	$s->set_sorter("date Sent", "sorter_date_sent", "3", "cfs.date_sent");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_custom_forms_sent.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "custom_forms_sent");
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql =  " SELECT cf.form_name,cfs.sent_form_id,cfs.remote_address,cfs.date_sent";
	$sql .= " FROM " . $table_prefix . "custom_forms_sent cfs ";
	$sql .= " LEFT JOIN " . $table_prefix . "custom_forms cf ON cfs.form_id=cf.form_id";
	if (strlen($form_id)) $sql .- " WHERE form_id=" . $db->tosql($form_id,INTEGER);
	$sql .= $s->order_by;
	$db->query($sql);
	if($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do
		{
			$t->set_var("sent_form_id", $db->f("sent_form_id"));
			
			//echo $db->f("form_name"). " - " . $db->f("form_title") . "<br>";
		
			$form_name = get_translation($db->f("form_name"));

			$t->set_var("form_name",  $form_name);
			$t->set_var("remote_address",  $db->f("remote_address"));
			$t->set_var("dete_sent",  $db->f("date_sent"));

			$t->set_var("admin_custom_form_sent_url", $admin_custom_form_url->get_url());


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