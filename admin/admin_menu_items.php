<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_menu_items.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");	
	include_once("./admin_common.php");

	check_admin_security("site_navigation");

	$nav_menu_id = get_param("menu_id");
	$menu_active_code = "cms";
	
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_menu_items.html");

	// Get menu_id and check if it exists
	$sql  = " SELECT * ";
	$sql .= " FROM ".$table_prefix."menus ";
	$sql .= " WHERE menu_id = ".$db->tosql($nav_menu_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$nav_menu_name = $db->f("menu_name");
		parse_value($nav_menu_name);
	} else {
		header("Location: admin_menu_list.php");
		exit;
	}

	$custom_breadcrumb = array(
		"admin_menu.php?code=cms" => CMS_MSG,
		"admin_menu.php?code=custom-modules" => CUSTOM_MODULES_MSG,
		"admin_menu_list.php" => SITE_NAVIGATION_MSG,
		"admin_menu_edit.php?menu_id=".urlencode($nav_menu_id) => $nav_menu_name,
		"admin_menu_items.php?menu_id=".urlencode($nav_menu_id) => MENU_ITEMS_MSG,
	);

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_menu_submenus_href","admin_menu_submenus.php");
	$t->set_var("admin_header_menus_order_href", "admin_header_menus_order.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_layout_href", "admin_layout.php");

	$admin_menu_item_url = new VA_URL("admin_menu_item.php");
	$admin_menu_item_url->add_parameter("menu_id", CONSTANT, $nav_menu_id);
	$t->set_var("admin_menu_item_new_url", $admin_menu_item_url->get_url());



	$sql  = " SELECT mi.* ";
	$sql .= " FROM " . $table_prefix . "menus_items mi ";
	$sql .= " WHERE menu_id=" . $db->tosql($nav_menu_id, INTEGER);
	$sql .= " ORDER BY mi.menu_order ";
	$db->query($sql);
	while ($db->next_record()) {
		$menu_item_id = $db->f("menu_item_id");
		$parent_menu_item_id = $db->f("parent_menu_item_id");
		if ($menu_item_id == $parent_menu_item_id) {
			$parent_menu_item_id = 0;

		}
		$menu[$menu_item_id]["menu_item_id"] = $menu_item_id;
		$menu[$menu_item_id]["menu_url"] = $db->f("menu_url");
		$menu[$menu_item_id]["menu_title"] = $db->f("menu_title");
		$menu[$menu_item_id]["menu_code"] = $db->f("menu_code");
		$menu[$menu_item_id]["menu_url"] = $db->f("menu_url");
		$menu[$menu_item_id]["menu_html"] = $db->f("menu_html");
		$menu[$parent_menu_item_id]["subs"][] = $menu_item_id;
	}


	$menu_count = 0;
	show_menu(0, 0);

	$t->pparse("main");

	function show_menu($parent_id, $level) 
	{
		global $t, $menu, $menu_count, $admin_menu_item_url, $admin_menu_submenus;

		$subs = $menu[$parent_id]["subs"];
		for ($m = 0; $m < sizeof($subs); $m++) {
			$menu_count++;
			$menu_item_id = $subs[$m];
			$menu_code = $menu[$menu_item_id]["menu_code"];
			$menu_title = $menu[$menu_item_id]["menu_title"];
			parse_value($menu_title);
			if (!strlen($menu_title)) { $menu_title = $menu_code; }
			$menu_url = $menu[$menu_item_id]["menu_url"];
			$spaces = spaces_level($level);
			$admin_menu_item_url->add_parameter("menu_item_id", CONSTANT, $menu_item_id);

			$t->set_var("menu_count", $menu_count);
			$t->set_var("menu_item_id", $menu_item_id);
			$t->set_var("menu_title", $spaces . $menu_title);
			$t->set_var("menu_url"  , htmlspecialchars($menu_url));
			$t->set_var("admin_menu_item_url", $admin_menu_item_url->get_url());

			$t->parse("records", true);

			if (isset($menu[$menu_item_id]["subs"])) {
				show_menu($menu_item_id, $level+1);
			}
		}
	}

    
	function spaces_level($level)
	{
		$spaces = "";
		for ($i = 0; $i < $level; $i++) {
			$spaces .= " &nbsp; &nbsp; &nbsp; ";
		}
		$spaces .= "<font style='font-size:".(14-$level)."px'>";
		return $spaces;
	}

?>