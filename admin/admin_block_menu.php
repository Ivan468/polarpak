<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_block_menu.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	if (compare_versions(va_version(), "4.4.2") < 0) { return; }

	if (!isset($menu_active_code)) { $menu_active_code = ""; }

	$menu_blocks = array();
	$param_site_id = get_session("session_site_id");
	$sql  = " SELECT m.menu_id, m.block_class, m.menu_class FROM (" . $table_prefix . "menus m";
	$sql .= " LEFT JOIN " . $table_prefix . "menus_sites ms ON m.menu_id=ms.menu_id) ";
	$sql .= " WHERE m.menu_type=5 "; // get only admin menu
	$sql .= " AND (m.sites_all=1 OR ms.site_id=" . $db->tosql($param_site_id, INTEGER) . ")";
	$db->query($sql);
	while ($db->next_record()) {
		$menu_id = $db->f("menu_id");
		$block_class = $db->f("block_class");
		if (!$block_class) { $block_class = "menu"; }
		$menu_class = $db->f("menu_class");
		$menu_blocks[$menu_id] = array(
			"block_class" => $block_class,
			"menu_class" => $menu_class,
		);
	}

	foreach ($menu_blocks as $menu_id => $menu_data) {
		$menu_class = $menu_data["menu_class"];
		$block_class = $menu_data["block_class"];

		// init menus array
		$menus = array(); 
		$sql  = " SELECT * FROM " . $table_prefix . "menus_items ";
		$sql .= " WHERE menu_id=" . $db->tosql($menu_id, INTEGER);
		$sql .= " AND admin_access IS NOT NULL ";
		$sql .= " ORDER BY menu_order ";
		$db->query($sql);
		while ($db->next_record())
		{
			$menu_id = $db->f("menu_item_id");
			$parent_menu_id = $db->f("parent_menu_item_id");
			$menu_order = $db->f("menu_order");
			$menu_url = $db->f("menu_url");
			$menu_code = $db->f("menu_code");
			$menu_class = $db->f("menu_class");
			$menu_title = get_translation($db->f("menu_title"));
			parse_value($menu_title);
			$menu_image = $db->f("menu_image");
			$menu_image_active = $db->f("menu_image_active");
			$match_type = 2; 
			if ($menu_id == $parent_menu_id) { $parent_menu_id = 0; }
 
			if (strlen($menu_title) || $menu_image || $menu_image_active) {
				$menus[$menu_id]["parent"] = $parent_menu_id;
				$menus[$menu_id]["menu_url"] = $menu_url;
				$menus[$menu_id]["menu_title"] = $menu_title;
				$menus[$menu_id]["menu_target"] = $db->f("menu_target");
				$menus[$menu_id]["menu_image"] = $menu_image;
				$menus[$menu_id]["menu_image_active"] = $menu_image_active;
				$menus[$menu_id]["menu_code"] = $menu_code;
				$menus[$menu_id]["menu_class"] = $menu_class;
				$menus[$menu_id]["match_type"] = 2;
				$menus[$parent_menu_id]["subs"][$menu_id] = $menu_order;
			}
		}
		set_menus($menus, 0, 0, $menu_active_code);

		$menu_class = $menu_data["menu_class"];
		$block_class = $menu_data["block_class"];
		$t->set_var("block_menu_class", htmlspecialchars($block_class));
		$t->set_var("top_menu_class", htmlspecialchars($menu_class));
		$t->parse("menu_block");
	}

