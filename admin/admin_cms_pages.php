<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_cms_pages.php                                      ***
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
	include_once($root_folder_path."messages/".$language_code."/support_messages.php");
	include_once($root_folder_path."messages/".$language_code."/profiles_messages.php");

	check_admin_security("cms_settings");
	$s_n = trim(get_param("s_n"));

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_cms_pages.html");
	$t->set_var("admin_href", "admin.php");
	$t->set_var("s_n", htmlspecialchars($s_n));

	$sql_where = "";	
	$sw = array();
	if (strlen($s_n) > 0) {
		$sw = preg_split("/[\s,]+/", $s_n);
	}

	$admin_cms_page_url = new VA_URL("admin_cms_page.php", true);
	$t->set_var("admin_cms_page_new_url", $admin_cms_page_url->get_url());

	$admin_cms_page_url->add_parameter("page_id", DB, "page_id");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_cms_pages.php");
	$s->set_sorter(ID_MSG, "sorter_page_id", "1", "cp.page_id", "", "", true);
	$s->set_sorter(NAME_MSG, "sorter_page_name", "2", "cp.page_name");
	$s->set_sorter(SORT_ORDER_MSG, "sorter_page_order", "3", "cp.page_order");
	$s->set_sorter(MODULE_MSG, "sorter_module", "4", "cm.module_order");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_cms_pages.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// check all records one by one as by default we use language constants
	if (is_array($sw) && sizeof($sw)) {
		$ids = array();
		$sql  = " SELECT cp.page_id, cp.page_code, cp.page_name ";
		$sql .= " FROM " . $table_prefix . "cms_pages cp ";
		$db->query($sql); 
		while ($db->next_record()) {
			$page_id = $db->f("page_id");
			$page_code = $db->f("page_code");
			$page_name = $db->f("page_name");
			parse_value($page_name);
			$page_found = true;
			foreach($sw as $word) {
				if (!preg_match("/".preg_quote($word, "/")."/i", $page_name) && !preg_match("/".preg_quote($word, "/")."/i", $page_code)) {
					$page_found = false;
					break;
				}
			}
			if ($page_found) {
				$ids[] = $page_id;
			}
		}
		if (sizeof($ids)) {
			$sql_where = " WHERE cp.page_id IN (" . $db->tosql($ids, INTEGERS_LIST, false) . ")";
		} else {
			$sql_where = " WHERE cp.page_id <> cp.page_id ";
		}
	}

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "cms_pages cp " . $sql_where);
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = " SELECT cp.page_id, cp.page_name, cp.page_order, cm.module_name ";
	$sql .= " FROM (" . $table_prefix . "cms_pages cp ";
	$sql .= " LEFT JOIN " . $table_prefix . "cms_modules cm ON cp.module_id=cm.module_id) ";
	$sql .= $sql_where . $s->order_by;
	$db->query($sql);
	if($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do
		{
			$page_id = $db->f("page_id");
			$page_name = get_translation($db->f("page_name"));
			$page_order = $db->f("page_order");
			$module_name = get_translation($db->f("module_name"));
			parse_value($page_name);
			parse_value($module_name);

			$t->set_var("page_id", $page_id);
			$t->set_var("page_name",  $page_name);
			$t->set_var("page_order",  $page_order);
			$t->set_block("module_name", $module_name);
			$t->parse("module_name", false);

			$t->set_var("admin_cms_page_url", $admin_cms_page_url->get_url("admin_cms_page.php"));
			$t->set_var("admin_cms_page_layout_url", $admin_cms_page_url->get_url("admin_cms_page_layout.php"));


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