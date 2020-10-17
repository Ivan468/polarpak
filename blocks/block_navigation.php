<?php

	$default_title = "{parent_menu_title}";

	$block_key  = $vars["block_key"];
	$block_type = get_setting_value($vars, "block_type", "");
	$tag_name = get_setting_value($vars, "tag_name", "");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	$js_type = get_setting_value($vars, "js_type", "");
	$data_js = "";

	if ($tag_name == "header_menu") { $js_type = "hover"; }
	if ($js_type) { $data_js = "nav"; }
	if (!$menu_site_id) { $menu_site_id = $site_id; } // check if menu_site_id parameter was set
	
  $t->set_var("data_js_nav", "");
	$html_template = get_setting_value($block, "html_template", "block_navigation.html"); 
	if ($block_type != "built-in") {
		if ($tag_name == "header_menu") {
		  $t->set_file($tag_name, $html_template);
		} else {
	  	$t->set_file("block_body", $html_template);
		}
	}
  $t->set_var("data_js_nav", $data_js);
  $t->set_var("js_nav", $data_js);
  $t->set_var("data_js_type", $js_type);
  $t->set_var("js_type", $js_type);
	$t->set_var("item_block", "");

	$menu_id = ""; $menu_title = ""; $block_css_class = ""; $parent_menu_class = ""; $show_categories = true;
	if ($block_key == "header") {
		$sql  = " SELECT m.menu_id, m.menu_code, m.block_class, m.menu_class FROM (" . $table_prefix . "menus m";
		$sql .= " LEFT JOIN " . $table_prefix . "menus_sites ms ON m.menu_id=ms.menu_id) ";
		$sql .= " WHERE m.menu_type=2 "; // get only header menu
		$sql .= " AND (m.sites_all=1 OR ms.site_id=" . $db->tosql($menu_site_id, INTEGER) . ")";
	} else {
		$sql = "SELECT * FROM " . $table_prefix . "menus WHERE menu_id = " . $db->tosql($block_key, INTEGER);
	}
	$db->query($sql);
	if ($db->next_record()) {
		$menu_id = $db->f("menu_id");
		$block_css_class = get_translation($db->f("block_class"));
		$parent_menu_code = strtolower(trim($db->f("menu_code")));
		if ($parent_menu_code == "no-categories" || $parent_menu_code == "disable-categories") { $show_categories = false; }
		$parent_menu_title = get_translation($db->f("menu_title"));
		$parent_menu_class = get_translation($db->f("menu_class"));
	} else {
		$block_parsed = false; 
		return;
	}
	if (!$parent_menu_class) { $parent_menu_class = "basic-menu"; }

	$t->set_var("parent_menu_title", $parent_menu_title);
	$t->set_var("parent_menu_class", $parent_menu_class);
	if ($tag_name) {
		$t->set_var($tag_name."_class", $block_css_class);
	}
	if ($block_type == "built-in") {
		$t->set_var("block_menu_".$block_key."_class", $block_css_class);
	}
	
	$html_template = get_setting_value($block, "html_template", "block_navigation.html"); 
	if ($block_type != "built-in") {
		if ($tag_name == "header_menu") {
		  $t->set_file($tag_name, $html_template);
		} else {
	  	$t->set_file("block_body", $html_template);
		}
	}
	$t->set_var("item_block", "");

	$menus = array();	$categories_menu_id = 0; $manufacturers_menu_id = 0; $sub_menus = array();
	$sql  = " SELECT * FROM " . $table_prefix . "menus_items ";
	$sql .= " WHERE menu_id = " . $db->tosql($menu_id, INTEGER);
	if (strlen(get_session("session_user_id"))) {
		$sql .= " AND user_access=1 ";
	} else {
		$sql .= " AND guest_access=1 ";
	}
	$sql .= " ORDER BY menu_order";
	$db->query($sql);
	while ($db->next_record()) {
		$menu_item_id = $db->f("menu_item_id");
		$parent_id  = $db->f("parent_menu_item_id");
		$menu_type = strtolower($db->f("menu_type"));
		$item_url   = $db->f("menu_url");
		$menu_target = $db->f("menu_target");
		$item_title = get_translation($db->f("menu_title"));
		$item_order = $db->f("menu_order");
		$menu_code = $db->f("menu_code");
		$menu_type = $db->f("menu_type");
		$item_class = $db->f("menu_class");
		$menu_image = $db->f("menu_image");
		$menu_image_active = $db->f("menu_image_active");
		$menu_html = get_translation($db->f("menu_html"));
		if ($menu_item_id == $parent_id) {
			$parent_id = 0;
		}
		if ($menu_code == "categories" || $menu_type == "categories") {
			$categories_menu_id = $menu_item_id;
		} else if ($menu_code == "manufacturers" || $menu_type == "manufacturers") { 
			$manufacturers_menu_id = $menu_item_id;
		} else if (preg_match("/(navigation|menu)[\-\_](\d+)/", $menu_code, $match)) {
			// for this menu item we need to show custom menu
			$sub_menus[$match[2]] = $menu_item_id;
		} 


		if (!isset($menus[$menu_item_id])) {
			$menus[$menu_item_id] = array();
		}
		$menus[$menu_item_id]["parent"] = $parent_id;
		$menus[$menu_item_id]["menu_type"] = $menu_type;
		$menus[$menu_item_id]["menu_url"] = $item_url;
		$menus[$menu_item_id]["menu_title"] = $item_title;
		$menus[$menu_item_id]["menu_target"] = $menu_target;
		$menus[$menu_item_id]["menu_image"] = $menu_image;
		$menus[$menu_item_id]["menu_image_active"] = $menu_image_active;
		$menus[$menu_item_id]["menu_class"] = $item_class;
		$menus[$menu_item_id]["menu_html"] = $menu_html;
		$menus[$menu_item_id]["match_type"] = 2;
		if (!isset($menus[$parent_id])) {
			$menus[$parent_id] = array("subs" => array());
		}
		$menus[$parent_id]["subs"][$menu_item_id] = $item_order;
	}

	// check categories menu only for first menu
	if ($show_categories && $block_key == "header") {
		include_once("./includes/products_functions.php");
		$sql_params = array("select" => "c.*", "where" => "header_menu_show=1");
		$sql = VA_Categories::sql($sql_params);
		$db->query($sql);
		while ($db->next_record()) {
			$category_id = $db->f("category_id");
			$menu_id = "c".$category_id;
			$parent_category_id = $db->f("parent_category_id");
			$parent_menu_id = ($parent_category_id) ? "c".$parent_category_id : $menu_id; 
			$parent_menu_id = ($parent_category_id) ? "c".$parent_category_id : $categories_menu_id; 

			$menu_order = $db->f("header_menu_order");
			if (!$menu_order) {
				$menu_order = $db->f("category_order");
			}
			$friendly_url = $db->f("friendly_url");
			if ($friendly_urls && $friendly_url) {
				$menu_url = $friendly_url.$friendly_extension;
			} else {
				$menu_url = "products_list.php?category_id=".urlencode($category_id);
			}
			$menu_class = $db->f("header_menu_class");
			$category_name = get_translation($db->f("category_name"));
			$menu_title = $category_name;
			$image_small = $db->f("image");
			$image_large = $db->f("image_large");
			$menu_image = "";
			$menu_image_active = "";
			$match_type = 2;
  
			if ($menu_id == $parent_menu_id) {
				$parent_menu_id = 0;
			}
  
			if (strlen($menu_title) || $menu_image || $menu_image_active) {
				$menus[$menu_id]["parent"] = $parent_menu_id;
				$menus[$menu_id]["menu_url"] = $menu_url;
				$menus[$menu_id]["menu_title"] = $menu_title;
				$menus[$menu_id]["menu_target"] = $db->f("menu_target");
				$menus[$menu_id]["menu_image"] = $menu_image;
				$menus[$menu_id]["menu_image_active"] = $menu_image_active;
				$menus[$menu_id]["menu_class"] = $menu_class;
				$menus[$menu_id]["match_type"] = 2;
				$menus[$parent_menu_id]["subs"][$menu_id] = $menu_order;
			}
		}
	}

	// check for manufacturers 
	if ($manufacturers_menu_id > 0) {
		$sql  = " SELECT m.manufacturer_id, m.manufacturer_order, m.friendly_url, m.manufacturer_name ";
		$sql .= " FROM " . $table_prefix . "manufacturers m ";
		$sql .= " ORDER BY m.manufacturer_order, m.manufacturer_name ";		
		$db->query($sql);
		while ($db->next_record()) {

			$manufacturer_id = $db->f("manufacturer_id");
			$menu_item_id = "m".$manufacturer_id;
			$parent_category_id = $db->f("parent_category_id");
			$parent_menu_id = $manufacturers_menu_id;
			$menu_order = $db->f("manufacturer_order");
			$menu_title = get_translation($db->f("manufacturer_name"));

			$friendly_url = $db->f("friendly_url");
			if ($friendly_urls && $friendly_url) {
				$menu_url = $friendly_url.$friendly_extension;
			} else {
				$menu_url = "products_list.php?manf=".urlencode($manufacturer_id);
			}
			$menu_class = "";
			$menu_image = "";
			$menu_image_active = "";
			$menu_target = "";
			$match_type = 2;

			$menus[$menu_item_id]["parent"] = $parent_menu_id;
			$menus[$menu_item_id]["menu_url"] = $menu_url;
			$menus[$menu_item_id]["menu_title"] = $menu_title;
			$menus[$menu_item_id]["menu_target"] = $menu_target;
			$menus[$menu_item_id]["menu_image"] = $menu_image;
			$menus[$menu_item_id]["menu_image_active"] = $menu_image_active;
			$menus[$menu_item_id]["menu_class"] = $menu_class;
			$menus[$menu_item_id]["match_type"] = $match_type;
			$menus[$parent_menu_id]["subs"][$menu_item_id] = $menu_order;
		}
	}


	// check for submenus
	if (count($sub_menus) > 0) {
		$menu_ids = array_keys($sub_menus);
		$sql  = " SELECT * FROM " . $table_prefix . "menus_items ";
		$sql .= " WHERE menu_id IN (" . $db->tosql($menu_ids, INTEGERS_LIST) . ")";
		if (strlen(get_session("session_user_id"))) {
			$sql .= " AND user_access=1 ";
		} else {
			$sql .= " AND guest_access=1 ";
		}
		$sql .= " ORDER BY menu_order";
		$db->query($sql);
		while ($db->next_record()) {
			$menu_id = $db->f("menu_id"); // custom menu id used to check parent menu items for top elements
			$menu_item_id = $db->f("menu_item_id");
			$parent_id  = $db->f("parent_menu_item_id");
			$item_url   = $db->f("menu_url");
			$menu_target = $db->f("menu_target");
			$item_title = get_translation($db->f("menu_title"));
			$item_order = $db->f("menu_order");
			$menu_code = $db->f("menu_code");
			$item_class = $db->f("menu_class");
			$menu_image = $db->f("menu_image");
			$menu_image_active = $db->f("menu_image_active");
			if ($parent_id == 0 || $menu_item_id == $parent_id) {
				$parent_id = $sub_menus[$menu_id];
			}
 
			$menus[$menu_item_id]["parent"] = $parent_id;
			$menus[$menu_item_id]["menu_url"] = $item_url;
			$menus[$menu_item_id]["menu_title"] = $item_title;
			$menus[$menu_item_id]["menu_target"] = $menu_target;
			$menus[$menu_item_id]["menu_image"] = $menu_image;
			$menus[$menu_item_id]["menu_image_active"] = $menu_image_active;
			$menus[$menu_item_id]["menu_class"] = $item_class;
			$menus[$menu_item_id]["match_type"] = 2;
			$menus[$parent_id]["subs"][$menu_item_id] = $item_order;
		}
	}


	set_menus($menus, 0, 0);

	$block_parsed = true;

