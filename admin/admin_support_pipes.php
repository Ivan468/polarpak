<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_support_pipes.php                                  ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");

	include_once($root_folder_path."messages/".$language_code."/support_messages.php");
	include_once("./admin_common.php");

	check_admin_security("support_departments");

	// start building breadcrumb
	$va_trail = array(
		"admin_menu.php?code=settings" => va_message("SETTINGS_MSG"),
		"admin_menu.php?code=helpdesk-settings" => va_message("HELPDESK_MSG"),
		"admin_support_pipes.php" => va_message("EMAIL_PIPES_MSG"),
	);

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_support_pipes.html");
	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_support_href", "admin_support.php");
	$t->set_var("admin_support_pipes_href", "admin_support_pipes.php");
	$t->set_var("admin_support_pipe_href", "admin_support_pipe.php");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_support_pipes.php");
	$s->set_parameters(false, true, true, false);
	$s->set_sorter(va_message("ID_MSG"), "sorter_pipe_id", "1", "sp.pipe_id", "", "", true);
	$s->set_sorter(va_message("INCOMING_EMAIL_MSG"), "sorter_incoming_email", "2", "sp.incoming_email");
	$s->set_sorter(va_message("SUPPORT_DEPARTMENT_FIELD"), "sorter_dep_name", "3", "sd.dep_name");
	$s->set_sorter(va_message("SUPPORT_TYPE_FIELD"), "sorter_type_name", "4", "st.type_name");
	$s->set_sorter(va_message("SUPPORT_PRODUCT_FIELD"), "sorter_product_name", "5", "pr.product_name");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_support_pipes.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "support_pipes");
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = " SELECT sp.pipe_id, sp.incoming_email, sp.outgoing_email, sd.short_name, sd.dep_name, st.type_name, pr.product_name ";
	$sql .= " FROM (((" . $table_prefix . "support_pipes sp ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_departments sd ON sp.dep_id=sd.dep_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_types st ON sp.support_type_id=st.type_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_products pr ON sp.support_product_id=pr.product_id) ";
	$sql .= $s->order_by;
	$db->query($sql);
	if($db->next_record())
	{
		$t->set_var("no_records", "");
		do {
			$t->set_var("pipe_id", $db->f("pipe_id"));
			$t->set_var("short_name", get_translation($db->f("short_name")));
			$t->set_var("dep_name", get_translation($db->f("dep_name")));
			$t->set_var("type_name", get_translation($db->f("type_name")));
			$t->set_var("incoming_email", $db->f("incoming_email"));
			$t->set_var("outgoing_email", $db->f("outgoing_email"));
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
