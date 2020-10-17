<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_ads_features_groups.php                            ***
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

	check_admin_security("ads");

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_ads_features_groups.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_ads_href", "admin_ads.php");
	$t->set_var("admin_ads_features_group_href", "admin_ads_features_group.php");
	$t->set_var("admin_default_features_href", "admin_default_features.php");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_ads_features_groups.php");
	$s->set_sorter(ID_MSG, "sorter_group_id", "1", "group_id", "", "", true);
	$s->set_sorter(GROUP_NAME_MSG, "sorter_group_name", "2", "group_name");
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_ads_features_groups.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "ads_features_groups");
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query("SELECT * FROM " . $table_prefix . "ads_features_groups " . $s->order_by);
	if($db->next_record())
	{
		$t->set_var("no_records", "");
		do {
			$t->set_var("group_id", $db->f("group_id"));
			$t->set_var("group_name", $db->f("group_name"));
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
