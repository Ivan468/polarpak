<?php
	// save block vars as bar_vars to use vars for sub blocks
	$bar_vars = $vars;
	$bar_cms_css_class = $cms_css_class;

	// clear all template blocks first as they could be parsed already in different php files
	$t->set_var("menu_block", "");
	$t->set_var("home_block", "");
	$t->set_var("account_block", "");
	$t->set_var("wishlist_block", "");
	$t->set_var("compare_block", "");
	$t->set_var("products_block", "");
	$t->set_var("languages_block", "");
	$t->set_var("currencies_block", "");
	$t->set_var("products_search_block", "");
	$t->set_var("site_search_block", "");
	$t->set_var("music_search_block", "");
	$t->set_var("cart_block", "");
	$t->set_var("menus", "");
	$t->set_var("after_block", "");

	// get global settings
	$site_url = get_setting_value($settings, "site_url", "");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	// load navigation bar template
	$html_template = get_setting_value($block, "html_template", "block_navigation_bar.html"); 
  $t->set_file("block_body", $html_template);

	$t->set_var("site_url", $site_url);
	$t->set_var("user_home_href", "user_home.php");
	$t->set_var("user_wishlist_href", "user_wishlist.php");
	$t->set_var("compare_href", "compare.php");
	$t->set_var("products_href", "products_list.php");
	$t->set_var("products_list_href", "products_list.php");
	$t->set_var("site_search_href", "site_search.php");
	$t->set_var("music_search_href", "music_search.php");

	// check ajax call for sub menu block
	$ajax = get_param("ajax");
	$pb_type = get_param("pb_type");
	if ($ajax && $pb_type) {
		$ajax_data = array();
		$ajax_data["pb_id"] = $pb_id;	
		$ajax_data["pb_type"] = $pb_type;
		if ($pb_type == "cart") {
			$vars = array("block_type" => "bar");
			include("./blocks/block_cart.php");
			$cart_pos = get_setting_value($bar_vars, "cart_pos", "left");
			if ($cart_pos == "right") {
				$t->set_var("menu_class", "nav-right");
			}
			$t->parse("cart_block", false);
			$ajax_data["html_id"] = "cart_".$pb_id;	
			$ajax_data["block"] = $t->get_var("cart_block");	
			echo json_encode($ajax_data);	
		}
		$layout_type = "no";
		$block_parsed = false; // don't parse the main navigation block 
		return;
	}



	// get block settings
	$home_show = get_setting_value($vars, "home_show", 0);
	$home_order = get_setting_value($vars, "home_order", 1);
	$home_pos = get_setting_value($vars, "home_pos", "left");
	$language_show = get_setting_value($vars, "language_show", 0);
	$language_order = get_setting_value($vars, "language_order", 2);
	$language_pos = get_setting_value($vars, "language_pos", "left");
	$currency_show = get_setting_value($vars, "currency_show", 0);
	$currency_order = get_setting_value($vars, "currency_order", 3);
	$currency_pos = get_setting_value($vars, "currency_pos", "left");
	$account_show = get_setting_value($vars, "account_show", 0);
	$account_order = get_setting_value($vars, "account_order", 4);
	$account_pos = get_setting_value($vars, "account_pos", "left");
	$wishlist_show = get_setting_value($vars, "wishlist_show", 0);
	$wishlist_order = get_setting_value($vars, "wishlist_order", 5);
	$wishlist_pos = get_setting_value($vars, "wishlist_pos", "left");
	$compare_show = get_setting_value($vars, "compare_show", 0);
	$compare_order = get_setting_value($vars, "compare_order", 6);
	$compare_pos = get_setting_value($vars, "compare_pos", "left");
	$products_show = get_setting_value($vars, "products_show", 0);
	$products_order = get_setting_value($vars, "products_order", 7);
	$products_pos = get_setting_value($vars, "products_pos", "left");
	$products_search_show = get_setting_value($vars, "products_search_show", 0);
	$products_search_order = get_setting_value($vars, "products_search_order", 8);
	$products_search_pos = get_setting_value($vars, "products_search_pos", "left");
	$site_search_show = get_setting_value($vars, "site_search_show", 0);
	$site_search_order = get_setting_value($vars, "site_search_order", 9);
	$site_search_pos = get_setting_value($vars, "site_search_pos", "left");
	$music_search_show = get_setting_value($vars, "music_search_show", 0);
	$music_search_order = get_setting_value($vars, "music_search_order", 9);
	$music_search_pos = get_setting_value($vars, "music_search_pos", "left");
	$cart_show = get_setting_value($vars, "cart_show", 0);
	$cart_order = get_setting_value($vars, "cart_order", 10);
	$cart_pos = get_setting_value($vars, "cart_pos", "left");

	// initialize menu 
	$menus = array("0" => array("subs" => array()));

	// parse home 
	if ($home_show) {
		$menus["home"]["block"] = "home_block";
		$menus["home"]["menu_pos"] = $home_pos;
		$menus[0]["subs"]["home"] = $home_order;
	}
	// parse languages
	if ($language_show) {
		$vars = array("language_selection" => "bar");
		include("./blocks/block_language.php");
		$menus["language"]["block"] = "languages_block";
		$menus["language"]["menu_pos"] = $language_pos;
		$menus[0]["subs"]["language"] = $language_order;
	}
	// parse currencies
	if ($currency_show) {
		$vars = array("currency_selection" => "bar");
		include("./blocks/block_currency.php");
		$menus["currency"]["block"] = "currencies_block";
		$menus["currency"]["menu_pos"] = $currency_pos;
		$menus[0]["subs"]["currency"] = $currency_order;
	}
	if ($products_search_show) {
		$vars = array("block_type" => "bar");
		include("./blocks/block_search.php");
		$menus["products_search"]["block"] = "products_search_block";
		$menus["products_search"]["menu_pos"] = $products_search_pos;
		$menus[0]["subs"]["products_search"] = $products_search_order;
	}
	if ($account_show) {
		$menus["account"]["block"] = "account_block";
		$menus["account"]["menu_pos"] = $account_pos;
		$menus[0]["subs"]["account"] = $account_order;
	}
	if ($wishlist_show) {
		$menus["wishlist"]["block"] = "wishlist_block";
		$menus["wishlist"]["menu_pos"] = $wishlist_pos;
		$menus[0]["subs"]["wishlist"] = $wishlist_order;
	}
	if ($compare_show) {
		$menus["compare"]["block"] = "compare_block";
		$menus["compare"]["menu_pos"] = $compare_pos;
		$menus[0]["subs"]["compare"] = $compare_order;
	}
	if ($products_show) {
		// check categories menu for navigation bar
		include_once("./includes/products_functions.php");
		$categories = array();		
		$sql_params = array("select" => "c.*", "where" => "nav_bar_show=1");
		$sql = VA_Categories::sql($sql_params);
		$db->query($sql);
		while ($db->next_record()) {
			$category_id = $db->f("category_id");
			$parent_category_id = $db->f("parent_category_id");
			$nav_bar_order = $db->f("nav_bar_order");
			if (!$nav_bar_order) {
				$nav_bar_order = $db->f("category_order");
			}
			$friendly_url = $db->f("friendly_url");
			if ($friendly_urls && $friendly_url) {
				$category_url = $friendly_url.$friendly_extension;
			} else {
				$category_url = "products_list.php?category_id=".urlencode($category_id);
			}
			$category_class = $db->f("nav_bar_class");
			$category_name = get_translation($db->f("category_name"));
			$image_small = $db->f("image");
			$image_large = $db->f("image_large");
  
			if ($category_id == $parent_category_id) { $parent_category_id = 0; }
  
			$categories[$category_id]["category_url"] = $category_url;
			$categories[$category_id]["category_name"] = $category_name;
			$categories[$category_id]["image_small"] = $image_small;
			$categories[$category_id]["image_large"] = $image_large;
			$categories[$category_id]["category_class"] = $category_class;
			$categories[$parent_category_id]["subs"][$category_id] = $nav_bar_order;
		}
		$active_category_id = get_param("category_id");
		show_categories($categories, 0, 0, $active_category_id);

		$menus["products"]["block"] = "products_block";
		$menus["products"]["menu_pos"] = $products_pos;
		$menus[0]["subs"]["products"] = $products_order;
	}
	if ($site_search_show) {
		$vars = array("block_type" => "bar");
		include("./blocks/block_site_search_form.php");
		$menus["site_search"]["block"] = "site_search_block";
		$menus["site_search"]["menu_pos"] = $site_search_pos;
		$menus[0]["subs"]["site_search"] = $site_search_order;
	}
	if ($music_search_show) {
		$vars = array("block_type" => "bar");
		include("./blocks/block_music_search_form.php");
		$menus["music_search"]["block"] = "music_search_block";
		$menus["music_search"]["menu_pos"] = $music_search_pos;
		$menus[0]["subs"]["music_search"] = $music_search_order;
	}
	if ($cart_show) {
		$vars = array("block_type" => "bar");
		include("./blocks/block_cart.php");
		$menus["cart"]["block"] = "cart_block";
		$menus["cart"]["menu_pos"] = $cart_pos;
		$menus[0]["subs"]["cart"] = $cart_order;
	}

	$t->set_var("menu", "");
	$t->set_var("menu_block", "");

	$menu_blocks = array();
	$sql  = " SELECT m.menu_id, m.block_class, m.menu_class FROM (" . $table_prefix . "menus m";
	$sql .= " LEFT JOIN " . $table_prefix . "menus_sites ms ON m.menu_id=ms.menu_id) ";
	$sql .= " WHERE m.menu_type=1 "; // get navigation menu
	$sql .= " AND (m.sites_all=1 OR ms.site_id=" . $db->tosql($site_id, INTEGER) . ")";
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

	if (count($menu_blocks) == 0) { // if Navigation Bar menu wasn't set in Site Navigation use default bar and block settings
		$menu_blocks["bar"] = array( 
			"block_class" => "",
			"menu_class" => "nav-bar",
		);
	}

	$menu_index = 0;
	foreach ($menu_blocks as $menu_id => $menu_data) {
		if ($menu_index > 0) {
			// clear menus array for 
			$menus = array("0" => array("subs" => array()));
		}
		$categories_menu_id = ""; // save here menu to parse categories
		$manufacturers_menu_id = ""; // save here menu to parse manufacturers 

		$top_menu_type = get_setting_value($settings, "top_menu_type", 1);
		$sql  = " SELECT * FROM " . $table_prefix . "menus_items ";
		$sql .= " WHERE menu_id=" . $db->tosql($menu_id, INTEGER);
		if (get_session("session_user_id")) {
			$sql .= " AND user_access=1 ";
		} else {
			$sql .= " AND guest_access=1 ";
		}
		if (get_session("session_admin_id")) {
			$sql .= " AND admin_access IS NOT NULL ";
		}
		$sql .= " ORDER BY menu_order ";
		$db->query($sql);
		while ($db->next_record())
		{
			$menu_id = $db->f("menu_item_id");
			$parent_menu_id = $db->f("parent_menu_item_id");
			$menu_order = $db->f("menu_order");
			$menu_type = strtolower($db->f("menu_type"));
			$menu_url = $db->f("menu_url");
			$menu_class = $db->f("menu_class");
			$menu_page = $db->f("menu_page");
			$menu_title = get_translation($db->f("menu_title"));
			$menu_image = $db->f("menu_image");
			$menu_image_active = $db->f("menu_image_active");
			$menu_html = get_translation($db->f("menu_html"));
			$match_type = 2; 
  
			if ($menu_id == $parent_menu_id) { $parent_menu_id = 0; }
			if ($menu_type == "categories") { $categories_menu_id = $menu_id; }
			else if ($menu_type == "manufacturers") { $manufacturers_menu_id = $menu_id; }
 
			if (strlen($menu_title) || $menu_image || $menu_image_active || $menu_type == "html" || $menu_type == "custom") {
				$menus[$menu_id]["parent"] = $parent_menu_id;
				$menus[$menu_id]["menu_type"] = $menu_type;
				$menus[$menu_id]["menu_url"] = $menu_url;
				$menus[$menu_id]["menu_title"] = $menu_title;
				$menus[$menu_id]["menu_target"] = $db->f("menu_target");
				$menus[$menu_id]["menu_image"] = $menu_image;
				$menus[$menu_id]["menu_image_active"] = $menu_image_active;
				$menus[$menu_id]["menu_class"] = $menu_class;
				$menus[$menu_id]["menu_html"] = $menu_html;
				$menus[$menu_id]["match_type"] = 2;

				$menus[$parent_menu_id]["subs"][$menu_id] = $menu_order;
			}
		}

		// check if we need to parse categories
		if ($categories_menu_id) {
			include_once("./includes/products_functions.php");
			$sql_params = array("select" => "c.*", "where" => "nav_bar_show=1");
			$sql = VA_Categories::sql($sql_params);
			$db->query($sql);
			while ($db->next_record()) {
				$category_id = $db->f("category_id");
				$menu_id = "c".$category_id;
				$parent_category_id = $db->f("parent_category_id");
				$parent_menu_id = ($parent_category_id && $parent_category_id != $category_id) ? "c".$parent_category_id : $categories_menu_id; 
  
				$menu_order = $db->f("nav_bar_order");
				if (!$menu_order) { $menu_order = $db->f("category_order"); }

				$friendly_url = $db->f("friendly_url");
				if ($friendly_urls && $friendly_url) {
					$menu_url = $friendly_url.$friendly_extension;
				} else {
					$menu_url = "products_list.php?category_id=".urlencode($category_id);
				}
				$menu_class = $db->f("nav_bar_class");
				$menu_title = get_translation($db->f("category_name"));
				$menu_target = ""; $menu_image = ""; $menu_image_active = "";
    
				$menus[$menu_id]["parent"] = $parent_menu_id;
				$menus[$menu_id]["menu_url"] = $menu_url;
				$menus[$menu_id]["menu_title"] = $menu_title;
				$menus[$menu_id]["menu_target"] = $menu_target;
				$menus[$menu_id]["menu_image"] = $menu_image;
				$menus[$menu_id]["menu_image_active"] = $menu_image_active;
				$menus[$menu_id]["menu_class"] = $menu_class;
				$menus[$parent_menu_id]["subs"][$menu_id] = $menu_order;
			}
		}
		// end categories parse

		// check if we need to parse manufacturers 
		if ($manufacturers_menu_id) {
			$sql  = " SELECT * FROM ".$table_prefix."manufacturers ";
			$sql .= " ORDER BY manufacturer_order, manufacturer_name ";
			$db->query($sql);
			while ($db->next_record()) {
				$manufacturer_id = $db->f("manufacturer_id");
				$menu_id = "m".$manufacturer_id;
				$parent_menu_id = $manufacturers_menu_id;
				$menu_order = $db->f("manufacturer_order");

				$friendly_url = $db->f("friendly_url");
				if ($friendly_urls && $friendly_url) {
					$menu_url = $friendly_url.$friendly_extension;
				} else {
					$menu_url = "products_list.php?manf=".urlencode($manufacturer_id);
				}
				$menu_class = ""; $menu_target = ""; $menu_image = ""; $menu_image_active = "";
				$menu_title = get_translation($db->f("manufacturer_name"));
				
    
				$menus[$menu_id]["parent"] = $parent_menu_id;
				$menus[$menu_id]["menu_url"] = $menu_url;
				$menus[$menu_id]["menu_title"] = $menu_title;
				$menus[$menu_id]["menu_target"] = $menu_target;
				$menus[$menu_id]["menu_image"] = $menu_image;
				$menus[$menu_id]["menu_image_active"] = $menu_image_active;
				$menus[$menu_id]["menu_class"] = $menu_class;
				$menus[$parent_menu_id]["subs"][$menu_id] = $menu_order;
			}
		}
		// end categories parse
		set_menus($menus, 0, 0, "");
		$menu_class = $menu_data["menu_class"];
		$block_class = $menu_data["block_class"];
		$t->set_var("block_menu_class", htmlspecialchars($block_class));
		$t->set_var("top_menu_class", htmlspecialchars($menu_class));
		$t->parse("menu_block");
	}

	if(!$layout_type) { $layout_type = "aa"; }
	$cms_css_class = $bar_cms_css_class;
	$extra_css_class = get_setting_value($bar_vars, "block_position", "");
	$t->set_var("block_position", htmlspecialchars($extra_css_class));

	$block_parsed = true;

?>