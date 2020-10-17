<?php

	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	$admin_id = get_session("session_admin_id");		

	$user_id = get_session("session_user_id");		
	$user_info = get_session("session_user_info");
	$user_type_id = get_setting_value($user_info, "user_type_id", "");
	
	$t->set_template_path($settings["templates_dir"]);
	$html_template = get_setting_value($block, "html_template", "footer.html"); 
 	$t->set_file("block_body", $html_template);
	$t->set_var("site_url", $settings["site_url"]);

	$t->set_var("index_href", get_custom_friendly_url("index.php"));
	$t->set_var("products_href", get_custom_friendly_url("products_list.php"));
	$t->set_var("basket_href", get_custom_friendly_url("basket.php"));
	$t->set_var("user_profile_href", get_custom_friendly_url("user_profile.php"));
	$t->set_var("admin_href", "admin.php");
	$t->set_var("copy_year", date("Y"));
	// set subscribe message as it could be used in footer
	$subscribe_desc = str_replace("{button_name}", SUBSCRIBE_BUTTON, SUBSCRIBE_FORM_MSG);
	$t->set_var("SUBSCRIBE_FORM_MSG", $subscribe_desc);


	$t->set_var("menu", "");
	$t->set_var("menus", "");
	$t->set_var("menu_block", "");

	$menu_blocks = array();
	if (compare_versions(va_version(), "4.4.2") >= 0) {
		$sql  = " SELECT m.menu_id, m.block_class, m.menu_class FROM (" . $table_prefix . "menus m";
		$sql .= " LEFT JOIN " . $table_prefix . "menus_sites ms ON m.menu_id=ms.menu_id) ";
		$sql .= " WHERE m.menu_type=4 "; // get only footer menu
		$sql .= " AND (m.sites_all=1 OR ms.site_id=" . $db->tosql($site_id, INTEGER) . ")";
		$db->query($sql);
		while ($db->next_record()) {
			$menu_id = $db->f("menu_id");
			$block_menu_class = $db->f("block_class");
			if (!$block_menu_class) { $block_menu_class = "footer-menu"; }
			$top_menu_class = $db->f("menu_class");
			$menu_blocks[$menu_id] = array(
				"block_class" => $block_menu_class,
				"menu_class" => $top_menu_class,
			);
		}
	}

	foreach ($menu_blocks as $menu_id => $menu_data) {
		$top_menu_class = $menu_data["menu_class"];
		$block_menu_class = $menu_data["block_class"];

		// init menus array
		$menus = array(); 
		$top_menu_type = get_setting_value($settings, "top_menu_type", 1);
		$sql  = " SELECT * FROM " . $table_prefix . "menus_items ";
		$sql .= " WHERE menu_id=" . $db->tosql($menu_id, INTEGER);
		$sql .= " ORDER BY menu_order ";
		$db->query($sql);
		while ($db->next_record())
		{
			$menu_id = $db->f("menu_item_id");
			$parent_menu_id = $db->f("parent_menu_item_id");
			$menu_order = $db->f("menu_order");
			$menu_url = $db->f("menu_url");
			$menu_class = $db->f("menu_class");
			$menu_page = $db->f("menu_page");
			$menu_title = get_translation($db->f("menu_title"));
			$menu_image = $db->f("menu_image");
			$menu_image_active = $db->f("menu_image_active");
			$match_type = 2; 

			// check if user can see menu item
			$menu_access = false;
			$admin_access = $db->f("admin_access");
			$user_access = $db->f("user_access");
			$guest_access = $db->f("guest_access");
			if ($admin_id) {
				if ($admin_access == "1" || $admin_access == "all") {
					$menu_access = true;
				} else if ($user_id) {
					$menu_access = ($user_access == "1");
				} else {
					$menu_access = ($guest_access == "1");
				}
			} else if ($user_id) {
				$menu_access = ($user_access == "1");
			} else {
				$menu_access = ($guest_access == "1");
			}
  
			if ($menu_id == $parent_menu_id) {
				$parent_menu_id = 0;
			}
 
			if ($menu_access && strlen($menu_title) || $menu_image || $menu_image_active) {
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
  
		if (count($menus)) {
			set_menus($menus, 0, 0);
		
			$t->set_var("block_menu_class", htmlspecialchars($block_menu_class));
			$t->set_var("top_menu_class", htmlspecialchars($top_menu_class));
			$t->sparse("menu_block");
		}
	}

	$footer_head = get_translation(get_setting_value($settings, "footer_head"));
	$footer_foot = get_translation(get_setting_value($settings, "html_below_footer"));
	$t->set_block("footer_head", $footer_head);
	$t->set_block("footer_foot", $footer_foot);

	// check if we need include language block 
	if ($t->block_exists("block_language", "block_body")) {
		$vars = array("block_type" => "built-in");
		if (file_exists("./blocks_custom/block_language.php")) {
			include("./blocks_custom/block_language.php");
		} else {
			include("./blocks/block_language.php");
		}
		$t->parse_to("block_language", false);	
	}
	// end of header menu check


	$t->parse("footer_head", false);
	$t->parse("footer_foot", false);

	if(!isset($layout_type) || !$layout_type) { $layout_type = "aa"; }
	$block_parsed = true;
