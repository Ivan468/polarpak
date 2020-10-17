<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_cms_blocks.php                                     ***
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
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");
	include_once($root_folder_path."messages/".$language_code."/forum_messages.php");
	include_once($root_folder_path."messages/".$language_code."/manuals_messages.php");
	include_once($root_folder_path."messages/".$language_code."/support_messages.php");
	include_once($root_folder_path."messages/".$language_code."/profiles_messages.php");

	check_admin_security("cms_settings");
	$s_n = trim(get_param("s_n"));

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_cms_blocks.html");
	$t->set_var("admin_href", "admin.php");
	$t->set_var("s_n", htmlspecialchars($s_n));

	$sql_where = "";	
	$sw = array();
	if (strlen($s_n) > 0) {
		$sw = explode(" ", $s_n);
		for($si = 0; $si < sizeof($sw); $si++) {
			$sw[$si] = str_replace("%","\%",$sw[$si]);
			$sql_where .= ($sql_where) ? " AND " : " WHERE ";
			$sql_where .= " (cp.block_name LIKE '%" . $db->tosql($sw[$si], TEXT, false) . "%'";
			$sql_where .= " OR cp.block_code LIKE '%" . $db->tosql($sw[$si], TEXT, false) . "%')";
		}
	}
	
	$admin_cms_block_url = new VA_URL("admin_cms_block.php", true);
	$t->set_var("admin_cms_block_new_url", $admin_cms_block_url->get_url());

	$admin_cms_block_url->add_parameter("block_id", DB, "block_id");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_cms_blocks.php");
	$s->set_sorter(ID_MSG, "sorter_block_id", "1", "cp.block_id", "", "", true);
	$s->set_sorter(NAME_MSG, "sorter_block_name", "2", "cp.block_name");
	$s->set_sorter(MODULE_MSG, "sorter_module", "3", "cm.module_order");
	$s->set_sorter(SORT_ORDER_MSG, "sorter_block_order", "4", "cp.block_order");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_cms_blocks.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "cms_blocks cp " . $sql_where);
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 10;
	$page_number = $n->set_navigator("navigator", "page", CENTERED, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = " SELECT cp.block_id, cp.block_name, cp.block_order, cm.module_name ";
	$sql .= " FROM (" . $table_prefix . "cms_blocks cp ";
	$sql .= " LEFT JOIN " . $table_prefix . "cms_modules cm ON cp.module_id=cm.module_id) ";
	$sql .= $sql_where . $s->order_by;
	$db->query($sql);
	if($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do
		{
			$block_id = $db->f("block_id");
			$block_name = get_translation($db->f("block_name"));
			$block_order = $db->f("block_order");
			$module_name = get_translation($db->f("module_name"));
			parse_value($block_name);
			parse_value($module_name);

			$t->set_var("block_id", $block_id);
			$t->set_var("block_name",  $block_name);
			$t->set_var("block_order",  $block_order);
			$t->set_var("module_name", $module_name);

			$t->set_var("admin_cms_block_properties_url", $admin_cms_block_url->get_url("admin_cms_block_properties.php"));
			$t->set_var("admin_cms_block_url", $admin_cms_block_url->get_url("admin_cms_block.php"));


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