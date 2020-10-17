<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_menu.php                                           ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/editgrid.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once("./admin_common.php");

	check_admin_security();

	$code = get_param("code");
	if (!$code) {
		header("Location: admin.php");
		exit;
	}

	$param_site_id = get_session("session_site_id");
	$sql  = " SELECT m.menu_id, m.block_class, m.menu_class FROM (" . $table_prefix . "menus m";
	$sql .= " LEFT JOIN " . $table_prefix . "menus_sites ms ON m.menu_id=ms.menu_id) ";
	$sql .= " WHERE m.menu_type=5 "; // get only admin menu
	$sql .= " AND (m.sites_all=1 OR ms.site_id=" . $db->tosql($param_site_id, INTEGER) . ")";
	$db->query($sql);
	if ($db->next_record()) {
		$menu_id = $db->f("menu_id");
	} else {
		header("Location: admin.php");
		exit;
	}
	

	$sql  = " SELECT * FROM " . $table_prefix . "menus_items ";
	$sql .= " WHERE menu_id=" . $db->tosql($menu_id, INTEGER);
	$sql .= " AND admin_access IS NOT NULL ";
	$sql .= " AND (menu_code=" . $db->tosql($code, TEXT);
	$sql .= " OR menu_class=" . $db->tosql($code, TEXT).")";
	$sql .= " ORDER BY menu_order ";
	$db->query($sql);
	if ($db->next_record()) {
		$parent_sql_id = $db->f("menu_item_id");
		$parent_menu_title = get_translation($db->f("menu_title"));
	} else {
		header("Location: admin.php");
		exit;
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_menu.html");
	$t->set_var("admin_href", "admin.php");
	$t->set_var("parent_menu_title", htmlspecialchars($parent_menu_title));

	include_once("./admin_header.php");
	include_once("./admin_footer.php");


	$sql  = " SELECT * FROM " . $table_prefix . "menus_items ";
	$sql .= " WHERE parent_menu_item_id=" . $db->tosql($parent_sql_id, INTEGER);
	$sql .= " AND admin_access IS NOT NULL ";
	$sql .= " ORDER BY menu_order ";
	$db->query($sql);
	while ($db->next_record()) {
		$menu_item_id = $db->f("menu_item_id");
		$menu_url = $db->f("menu_url");
		$menu_title = $db->f("menu_title");
		parse_value($menu_title);

		$t->set_var("menu_item_url", htmlspecialchars($menu_url));
		$t->set_var("menu_item_title", htmlspecialchars($menu_title));

		$t->parse("menu_items", true);
	}

	$t->pparse("main");

?>
