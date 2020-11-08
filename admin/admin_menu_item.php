<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_menu_item.php                                      ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path."includes/record.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");
	include_once($root_folder_path."messages/".$language_code."/support_messages.php");

	include_once("./admin_common.php");

	check_admin_security("site_navigation");

	$menu_active_code = "cms";
	$nav_menu_id = get_param("menu_id");
	$nav_menu_item_id = get_param("menu_item_id");
	$menu_type = get_param("menu_type");

	if (!$nav_menu_id && $nav_menu_item_id) {
		$sql  = " SELECT menu_id ";
		$sql .= " FROM ".$table_prefix."menus_items ";
		$sql .= " WHERE menu_item_id=".$db->tosql($nav_menu_item_id, INTEGER);
		$nav_menu_id = get_db_value($sql);
	}
	
	// check if menu exists
	$sql  = " SELECT * ";
	$sql .= " FROM ".$table_prefix."menus ";
	$sql .= " WHERE menu_id=".$db->tosql($nav_menu_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$nav_menu_name = $db->f("menu_name");
		$nav_menu_type = $db->f("menu_type");
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
		"admin_menu_item.php?menu_item_id=".urlencode($nav_menu_item_id) => EDIT_MSG,
	);

	$return_page = get_param("return_page");
	$operation = get_param("operation");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_menu_item.html");

	$t->set_var("admin_href"      , "admin.php");
	$t->set_var("admin_pages_href", "admin_header_menus.php");
	$t->set_var("admin_page_href" , "admin_menu_item.php");
	$t->set_var("admin_layout_href", "admin_layout.php");
	$t->set_var("admin_menu_href"  , "admin_header_menus.php");
	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");

	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", ADMIN_MENU_ITEM_MSG, CONFIRM_DELETE_MSG));

	if (!$nav_menu_item_id) {
		$sql  = " SELECT MAX(menu_order) FROM " . $table_prefix . "menus_items ";
		$sql .= " WHERE menu_id = ".$db->tosql($nav_menu_id, INTEGER);
		$menu_order = get_db_value($sql);
		$menu_order++;
	} else {
		$menu_order = 1;
	}

	$menu_types = 
		array( 
			array("default", va_message("DEFAULT_MSG")), 
			array("categories", va_message("PRODUCT_CATEGORIES_MSG")), 
			array("manufacturers", va_message("MANUFACTURERS_TITLE")), 
			array("html", va_message("MENU_HTML_MSG"))
		);

	$r = new VA_Record($table_prefix . "menus_items ");
	$r->return_page = "admin_menu_items.php";

	$r->add_where("menu_item_id", INTEGER);
	$r->add_textbox("menu_id", INTEGER);
	$r->change_property("menu_id", TRANSFER, true);
	$r->change_property("menu_id", USE_IN_UPDATE, false);
	$r->change_property("menu_id", DEFAULT_VALUE, $nav_menu_id);

	$r->add_checkbox("guest_access", INTEGER);
	$r->parameters["guest_access"][DEFAULT_VALUE] = 1;
	$r->add_checkbox("user_access", INTEGER);
	$r->parameters["user_access"][DEFAULT_VALUE] = 1;
	$r->add_checkbox("admin_access", INTEGER);
	$r->parameters["admin_access"][DEFAULT_VALUE] = 1;

	$r->add_textbox("menu_order", INTEGER, MENU_ORDER_MSG);
	$r->change_property("menu_order", DEFAULT_VALUE, $menu_order);
	$r->add_textbox("menu_code", TEXT, CODE_MSG);
	$r->add_radio("menu_type", TEXT, $menu_types);
	$r->change_property("menu_type", DEFAULT_VALUE, "default");
	$r->add_textbox("menu_title", TEXT, MENU_TITLE_MSG);
	$r->change_property("menu_title", PARSE_NAME, "menu_title_edit");
	$r->add_textbox("menu_url", TEXT, ADMIN_URL_SHORT_MSG);
	$r->change_property("menu_url", PARSE_NAME, "menu_url_edit");
	$r->add_textbox("menu_target", TEXT, ADMIN_TARGET_MSG);
	$r->add_textbox("menu_class", TEXT);
	$r->change_property("menu_class", PARSE_NAME, "menu_class_edit");
	$r->add_textbox("menu_html", TEXT, MENU_HTML_MSG);
	$r->change_property("menu_html", TRIM, true);

	$r->add_textbox("menu_image", TEXT, MENU_IMAGE_MSG);
	$r->change_property("menu_image", PARSE_NAME, "edit_menu_image");
	$r->add_textbox("menu_image_active", TEXT, MENU_IMAGE_ACTIVE_MSG);
	$r->change_property("menu_image_active", PARSE_NAME, "edit_menu_image_active");

	if ($menu_type == "html" || $menu_type == "custom") {
		$r->change_property("menu_code", REQUIRED, true);
		$r->change_property("menu_html", REQUIRED, true);
	} else {
		$r->change_property("menu_url", REQUIRED, true);
	}

	$r->set_event(BEFORE_INSERT, "set_menu_item_data");
	$r->set_event(BEFORE_UPDATE, "set_menu_item_data");
	$r->set_event(BEFORE_DELETE, "delete_menu_item_data");

	//-- parent items
	$sql  = " SELECT mi.* ";
	$sql .= " FROM " . $table_prefix . "menus_items mi ";
	$sql .= " WHERE mi.menu_id=".$db->tosql($nav_menu_id, INTEGER);
	$sql .= " ORDER BY mi.menu_order ";
	$db->query($sql);
	while($db->next_record()) {
		$list_item_id = $db->f("menu_item_id");
		$parent_menu_item_id = $db->f("parent_menu_item_id");
		if ($parent_menu_item_id == $list_item_id) {
			$parent_menu_item_id = 0;
		}
		$list_title = $db->f("menu_title");
		parse_value($list_title);

		$menu[$list_item_id]["menu_item_id"] = $list_item_id;
		$menu[$list_item_id]["menu_title"] = $list_title;
		$menu[$list_item_id]["menu_url"] = $db->f("menu_url");
		$menu[$list_item_id]["menu_path"] = $db->f("menu_path");
		$menu[$list_item_id]["parent"] = $parent_menu_item_id;

		$menu[$parent_menu_item_id]["subs"][] = $list_item_id;
	}

	$items = array();
	build_menu(0, 0);
	$r->add_select("parent_menu_item_id", TEXT, $items, PARENT_ITEM_MSG);

	$r->process();
	if ($nav_menu_type != 5) {
		$t->parse("user_guest_access", false);
	}

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

	function spaces_level($level)
	{
		$spaces = "";
		for ($i =1; $i <= $level; $i++) {
			$spaces .= "---";
		}
		return $spaces . " ";
	}

	function build_menu($parent_id, $level) {
		global $t, $menu, $items, $menu_id;
		if (isset($menu[$parent_id])) {
			$subs = $menu[$parent_id]["subs"];
			for ($m = 0; $m < sizeof($subs); $m++) {
				$item_id = $subs[$m];
				if ($menu_id != $item_id) {
					$menu_path = $menu[$item_id]["menu_path"];
					$menu_title = $menu[$item_id]["menu_title"];
					if (!$menu_title) {
						$menu_title = $menu[$item_id]["menu_url"];
					}
					$spaces = spaces_level($level);
					$items[] = array($item_id, $spaces.$menu_title);
			
					if (isset($menu[$item_id]["subs"])) {
						build_menu($item_id, $level+1);
					}
				}
			}
		}
	}
	
	function set_menu_item_data()
	{
		global $r;
		$parent_menu_item_id = $r->get_value("parent_menu_item_id");
		if (!$parent_menu_item_id) {
			$r->set_value("parent_menu_item_id", 0);
		}
	}


function delete_menu_item_data()
{
	global $r, $db, $table_prefix;
	$menu_item_id = $r->get_value("menu_item_id");
	menu_sub_childs($menu_item_id, $child_ids);
	if ($child_ids) {
		$sql = "DELETE FROM ".$table_prefix."menus_items WHERE menu_item_id IN (".$db->tosql($child_ids,INTEGERS_LIST).")";
		$db->query($sql);
	}
}

function menu_sub_childs($menu_item_id, &$child_ids)
{
	global $menu;
	$subs = (isset($menu[$menu_item_id]["subs"])) ? $menu[$menu_item_id]["subs"] : array();
	foreach ($subs as $sub_id) {
		if ($child_ids) { $child_ids .= ","; }
		$child_ids .= $sub_id;
		menu_sub_childs($sub_id, $child_ids);
	}
}



?>