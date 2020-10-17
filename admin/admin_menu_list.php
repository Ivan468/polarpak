<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_menu_list.php                                      ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");

	check_admin_security("site_navigation");

	$custom_breadcrumb = array(
		"admin_menu.php?code=cms" => CMS_MSG,
		"admin_menu.php?code=custom-modules" => CUSTOM_MODULES_MSG,
		"admin_menu_list.php" => SITE_NAVIGATION_MSG,
	);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_menu_list.html");
	$t->set_var("admin_href", "admin.php");

	$admin_menu_edit_url = new VA_URL("admin_menu_edit.php", true);
	$t->set_var("admin_menu_edit_new_url", $admin_menu_edit_url->get_url());

	$admin_menu_edit_url->add_parameter("menu_id", DB, "menu_id");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_menu_list.php");
	$s->set_sorter(ID_MSG, "sorter_menu_id", "1", "menu_id", "", "", true);
	$s->set_sorter(NAME_MSG, "sorter_menu_name", "2", "menu_name");
	$s->set_sorter(NOTES_MSG, "sorter_menu_notes", "3", "menu_notes");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_menu_list.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$sites = array();
	$sql  = " SELECT ms.menu_id, s.site_id, s.site_name ";
	$sql .= " FROM (".$table_prefix."sites s ";
	$sql .= " INNER JOIN ".$table_prefix."menus_sites ms ON s.site_id=ms.site_id) ";
	$db->query($sql);
	while($db->next_record()) {
		$menu_id = $db->f("menu_id");
		$site_name = $db->f("site_name");
		if (isset($sites[$menu_id])) {
			$sites[$menu_id] .= ", ".$site_name;
		} else {
			$sites[$menu_id] = $site_name;
		}
	}

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "menus");
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query("SELECT * FROM " . $table_prefix . "menus " . $s->order_by);
	if($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do
		{
			$menu_id = $db->f("menu_id");
			$sites_all = $db->f("sites_all");

			$menu_sites = "";
			if ($sites_all) {
				$menu_sites = ALL_MSG;
			} else if (isset($sites[$menu_id])) {
				$menu_sites = $sites[$menu_id];
			}
		
			$menu_name = get_translation($db->f("menu_name"));
			$menu_title = get_translation($db->f("menu_title"));

			$menu_notes = get_translation($db->f("menu_notes"));
			if (!$menu_notes) {
				$menu_notes = strip_tags(get_translation($db->f("menu_desc")));
			}
			$words = explode(" ", $menu_notes);
			if(sizeof($words) > 9) {
				$menu_notes = "";
				for ($i = 0; $i < 9; $i++) {
					$menu_notes .= $words[$i] . " ";
				}
				$menu_notes .= " ...";
			} 

			$t->set_var("menu_id", $menu_id);
			$t->set_var("menu_name",  $menu_name);
			$t->set_var("menu_title", $menu_title);
			$t->set_var("menu_notes", $menu_notes);
			$t->set_var("menu_sites", htmlspecialchars($menu_sites));


			$t->set_var("admin_menu_edit_url", $admin_menu_edit_url->get_url("admin_menu_edit.php"));
 			$t->set_var("admin_menu_items_url", $admin_menu_edit_url->get_url("admin_menu_items.php"));

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