<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_menu_edit.php                                      ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once($root_folder_path . "includes/record.php");

	include_once("./admin_common.php");

	check_admin_security("site_navigation");
	
	$operation = get_param("operation");
	$menu_id = get_param("menu_id");
	$menu_active_code = "cms";

	$custom_breadcrumb = array(
		"admin_menu.php?code=cms" => CMS_MSG,
		"admin_menu.php?code=custom-modules" => CUSTOM_MODULES_MSG,
		"admin_menu_list.php" => SITE_NAVIGATION_MSG,
		"admin_menu_edit.php?menu_id=".urlencode($menu_id) => EDIT_MENU_MSG,
	);
	
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_menu_edit.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_menu_edit_href", "admin_menu_edit.php");
	$t->set_var("admin_menu_list_href", "admin_menu_list.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", ADMIN_MENU_ITEM_MSG, CONFIRM_DELETE_MSG));
	
	$r = new VA_Record($table_prefix . "menus");
	$r->return_page = "admin_menu_list.php";

	$menu_types = array(
		array("", ""),
		array("0", HIDDEN_MSG),
		array("1", NAVIGATION_BAR_MSG),
		array("2", HEADER_MENU_MSG),
		array("3", CUSTOM_MENUS_MSG),
		array("4", FOOTER_MENU_MSG),
		array("5", ADMINISTRATIVE_MENU_MSG),
	);

	$r->add_where("menu_id", INTEGER);
	$r->add_select("menu_type", INTEGER, $menu_types, TYPE_MSG);
	$r->change_property("menu_type", REQUIRED, true);
	$r->add_textbox("menu_code", TEXT);
	$r->add_textbox("menu_name", TEXT, NAME_MSG);
	$r->change_property("menu_name", REQUIRED, true);
	$r->change_property("menu_name", PARSE_NAME, "menu_name_edit");
	$r->add_textbox("menu_title", TEXT, MENU_TITLE_MSG);
	$r->change_property("menu_title", PARSE_NAME, "menu_title_edit");
	$r->add_textbox("block_class", TEXT);
	$r->change_property("block_class", PARSE_NAME, "block_class_edit");
	$r->add_textbox("menu_class", TEXT);
	$r->change_property("menu_class", PARSE_NAME, "menu_class_edit");
	$r->add_textbox("menu_notes", TEXT, NOTES_MSG);
	$r->set_event(BEFORE_INSERT, "set_menu_data");
	$r->set_event(BEFORE_UPDATE, "set_menu_data");
	$r->set_event(AFTER_INSERT, "update_menu_data");
	$r->set_event(AFTER_UPDATE, "update_menu_data");
	$r->set_event(BEFORE_DELETE, "delete_menu_data");

	// sites list
	$r->add_checkbox("sites_all", INTEGER);
	$r->change_property("sites_all", DEFAULT_VALUE, 1);
	if ($sitelist) {
		$selected_sites = array();
		if (strlen($operation)) {
			$sites = get_param("sites");
			if ($sites) {
				$selected_sites = explode(",", $sites);
			}
		} elseif ($menu_id) {
			$sql  = "SELECT site_id FROM " . $table_prefix . "menus_sites ";
			$sql .= " WHERE menu_id=" . $db->tosql($menu_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$selected_sites[] = $db->f("site_id");
			}
		}
	}

	$r->process();

	if ($sitelist) {
		$sites = array();
		$sql = " SELECT site_id, site_name FROM " . $table_prefix . "sites ";
		$db->query($sql);
		while ($db->next_record())	{
			$list_site_id   = $db->f("site_id");
			$site_name = $db->f("site_name");
			$sites[$list_site_id] = $site_name;
			$t->set_var("site_id", $list_site_id);
			$t->set_var("site_name", $site_name);
			if (in_array($list_site_id, $selected_sites)) {
				$t->parse("selected_sites", true);
			} else {
				$t->parse("available_sites", true);
			}
		}
	}

	$tabs = array(
			"general" => array("title" => ADMIN_GENERAL_MSG), 
			"sites" => array("title" => ADMIN_SITES_MSG, "show" => $sitelist),
		);
	parse_admin_tabs($tabs);


	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");
	
function set_menu_data($params)  
{
	global $r, $db, $table_prefix, $sitelist;	
	$event = isset($params["event"]) ? $params["event"] : "";
	if ($event == BEFORE_INSERT) {
		if ($db->DBType == "postgre") {
			$menu_id = get_db_value(" SELECT NEXTVAL('seq_" . $table_prefix . "menus ') ");
			$r->change_property("menu_id", USE_IN_INSERT, true);
			$r->set_value("menu_id", $menu_id);
		}
	}
	if (!$sitelist) {
		$r->set_value("sites_all", 1);
	}
}

function update_menu_data($params)
{
	global $r, $t, $db, $table_prefix, $sitelist, $selected_sites;
	$event = isset($params["event"]) ? $params["event"] : "";
	if ($event == AFTER_INSERT) {
		if ($db->DBType == "mysql") {
			$menu_id = get_db_value(" SELECT LAST_INSERT_ID() ");
			$r->set_value("menu_id", $menu_id);
		} elseif ($db->DBType == "access") {
			$menu_id = get_db_value(" SELECT @@IDENTITY ");
			$r->set_value("menu_id", $menu_id);
		} elseif ($db->DBType == "db2") {
			$menu_id = get_db_value(" SELECT PREVVAL FOR seq_" . $table_prefix . "menus FROM " . $table_prefix . "menus ");
			$r->set_value("menu_id", $menu_id);
		}
	}
	$menu_id = $r->get_value("menu_id");
					
	if ($sitelist) {
		$db->query("DELETE FROM " . $table_prefix . "menus_sites WHERE menu_id=" . $db->tosql($menu_id, INTEGER));
		for ($st = 0; $st < sizeof($selected_sites); $st++) {
			$list_site_id = $selected_sites[$st];
			if (strlen($list_site_id)) {
				$sql  = " INSERT INTO " . $table_prefix . "menus_sites (menu_id, site_id) VALUES (";
				$sql .= $db->tosql($menu_id, INTEGER) . ", ";
				$sql .= $db->tosql($list_site_id, INTEGER) . ") ";
				$db->query($sql);
			}
		}
	}

}

function delete_menu_data() 
{
	global $db, $r, $table_prefix;
	$menu_id = $r->get_value("menu_id");
	if (intval($menu_id) > 0) {
		$sql = "DELETE FROM ".$table_prefix."menus_items WHERE menu_id = ".$db->tosql($menu_id, INTEGER);
		$db->query($sql);

		$sql = "DELETE FROM ".$table_prefix."menus_sites WHERE menu_id=" . $db->tosql($menu_id, INTEGER);
		$db->query($sql);
	}
}

?>