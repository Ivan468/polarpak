<?php
	// Save original CMS vars
	$header_vars = $vars;
	$html_template = get_setting_value($block, "html_template", "header.html"); 
 	$t->set_file("block_body", $html_template);

	// check ajax call for sub menu block
	$ajax = get_param("ajax");
	$pb_type = get_param("pb_type");
	if ($ajax && $pb_type == "cart") {
		$vars = array("block_type" => "header");
		if (file_exists("./blocks_custom/block_cart.php")) {
			include("./blocks_custom/block_cart.php");
		} else {
			include("./blocks/block_cart.php");
		}
		$t->parse("block_cart", false);
		$ajax_data = array(
			"pb_id" => $pb_id,	
			"html_id" => "cart_".$pb_id,	
			"pb_type" => "cart",
			"block" => $t->get_var("block_cart"),
		);	
		echo json_encode($ajax_data);	
		exit; // don't parse the main block for sub cart ajax call
	}

	// check block we need to parse
	$block_vars = $t->get_block("block_body");
	foreach ($block_vars as $block_var) {
		if (preg_match("/^block_custom_(\d+)$/", $block_var, $matches)) {
			$block_name = $matches[0]; $block_key = $matches[1];
			$vars = array("block_type" => "header", "block_key" => $block_key, "block_name" => $block_name);
			if (file_exists("./blocks_custom/block_custom.php")) {
				include("./blocks_custom/block_custom.php");
			} else {
				include("./blocks/block_custom.php");
			}
			$t->parse($vars["block_name"], false);
		} else if (preg_match("/^block_menu_(\w+)$/", $block_var, $matches)) {
			$menu_id = ""; $block_name = $matches[0]; $block_key = $matches[1];
			$vars = array("block_type" => "built-in", "block_key" => $block_key, "block_name" => $block_name);
			if (file_exists("./blocks_custom/block_navigation.php")) {
				include("./blocks_custom/block_navigation.php");
			} else {
				include("./blocks/block_navigation.php");
			}
			// parse menu block if it's exists
			if (strlen($menu_id)) {
				$t->parse($vars["block_name"], false);
			}
		}
	}

	// check if we need include header menu in old way
	if ($t->block_exists("header_menu", "block_body")) {
		$vars = array("block_key" => "header", "tag_name" => "header_menu");
		if (file_exists("./blocks_custom/block_navigation.php")) {
			include("./blocks_custom/block_navigation.php");
		} else {
			include("./blocks/block_navigation.php");
		}
		$t->parse_to("header_menu", false);	
	}
	// end of header menu check

	// check sub blocks in main header block 
	if ($t->block_exists("block_login", "block_body")) {
		$vars = array("block_type" => "header");
		if (file_exists("./blocks_custom/block_login.php")) {
			include("./blocks_custom/block_login.php");
		} else {
			include("./blocks/block_login.php");
		}
		$t->parse("block_login", false);
	}
	if ($t->block_exists("block_cart", "block_body")) {
		$vars = array("block_type" => "header");
		if (file_exists("./blocks_custom/block_cart.php")) {
			include("./blocks_custom/block_cart.php");
		} else {
			include("./blocks/block_cart.php");
		}
		$t->parse("block_cart", false);
	}

	if ($t->block_exists("block_currency", "block_body")) {
		$vars = array("currency_selection" => "header");
		if (file_exists("./blocks_custom/block_currency.php")) {
			include("./blocks_custom/block_currency.php");
		} else {
			include("./blocks/block_currency.php");
		}
		$t->parse("block_currency", false);
	}

	if ($t->block_exists("block_language", "block_body")) {
		$vars = array("language_selection" => "header");
		if (file_exists("./blocks_custom/block_language.php")) {
			include("./blocks_custom/block_language.php");
		} else {
			include("./blocks/block_language.php");
		}
		$t->parse("block_language", false);
	}
	if ($t->block_exists("block_search", "block_body")) {
		$vars = array("block_type" => "header");
		if (file_exists("./blocks_custom/block_search.php")) {
			include("./blocks_custom/block_search.php");
		} else {
			include("./blocks/block_search.php");
		}
		$t->parse("block_search", false);
	}
	if ($t->block_exists("block_site_search", "block_body")) {
		$vars = array("block_type" => "header");
		if (file_exists("./blocks_custom/block_site_search_form.php")) {
			include("./blocks_custom/block_site_search_form.php");
		} else {
			include("./blocks/block_site_search_form.php");
		}
		$t->parse("block_site_search", false);
	}
	// end sub blocks check 
	// return orginal header vars and clear CMS vars
	$vars = $header_vars;
	$block_css_class = ""; $var_css_class = ""; $extra_css_class = ""; // clear before include block

	$request_uri_path = get_request_path();
	$request_uri_base = basename($request_uri_path);
	// set site logo

	$logo_image = get_translation(get_setting_value($settings, "logo_image", "images/tr.gif"));
	//if (isset($cms_key_type) && $logo_image == "images/tr.gif") { $logo_image = "images/logo-shop.png"; }
	$logo_image_alt = get_translation(get_setting_value($settings, "logo_image_alt", HOME_PAGE_TITLE));
	$logo_width = get_setting_value($settings, "logo_image_width", "");
	$logo_height = get_setting_value($settings, "logo_image_height", "");
	$logo_size = "";
	if ($logo_width || $logo_height) {
		if ($logo_width) { $logo_size = "width=\"".$logo_width."\""; }
		if ($logo_height) { $logo_size .= " height=\"".$logo_height."\""; }
	} elseif ($logo_image && !preg_match("/^http\:\/\//", $logo_image)) {
		//$logo_image = $absolute_url . $logo_image;
		$image_size = @GetImageSize($logo_image);
		if (is_array($image_size)) {
			$logo_size = $image_size[3];
		}
	}
	
	$t->set_var("logo_alt", htmlspecialchars($logo_image_alt));
	$t->set_var("logo_src", htmlspecialchars($logo_image));
	$t->set_var("logo_size", $logo_size);

	$user_id = get_session("session_user_id");
	if ($user_id) {
		$account_url = get_custom_friendly_url("user_login.php");
	} else {
		$account_url = get_custom_friendly_url("user_home.php");
	}
	$cart_url = get_custom_friendly_url("basket.php");
	$t->set_var("index_href", get_custom_friendly_url("index.php"));
	$t->set_var("products_href", get_custom_friendly_url("products_list.php"));
	$t->set_var("basket_href", get_custom_friendly_url("basket.php"));
	$t->set_var("user_profile_href", get_custom_friendly_url("user_profile.php"));
	$t->set_var("user_login_url", get_custom_friendly_url("user_profile.php"));
	$t->set_var("account_url", htmlspecialchars($account_url));
	$t->set_var("cart_url", htmlspecialchars($cart_url));
	$t->set_var("admin_href", "admin.php");

	// check secure settings and set appropriate tags
	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", "");
	$secure_user_login = get_setting_value($settings, "secure_user_login", 0);
	$secure_user_profile = get_setting_value($settings, "secure_user_profile", 0);
	if ($secure_user_login) {
		$user_login_url = $secure_url . get_custom_friendly_url("user_login.php");
	} else {
		$user_login_url = $site_url . get_custom_friendly_url("user_login.php");
	}
	if ($secure_user_profile) {
		$user_profile_url = $secure_url . get_custom_friendly_url("user_profile.php");
	} else {
		$user_profile_url = $site_url . get_custom_friendly_url("user_profile.php");
	}
	$user_home_url = $site_url . get_custom_friendly_url("user_home.php");
	$t->set_var("user_login_url", htmlspecialchars($user_login_url));
	$t->set_var("user_profile_url", htmlspecialchars($user_profile_url));
	$t->set_var("user_home_url", htmlspecialchars($user_home_url));
	$t->set_var("user_logout_url", htmlspecialchars($user_home_url."?operation=logout"));

	$user_id = get_session("session_user_id");
	if ($user_id) {
		$user_info = get_session("session_user_info");
		$user_name = get_setting_value($user_info, "name", "");
		$t->set_var("user_name", htmlspecialchars($user_name));
		$t->sparse("logout_link", false);
		$t->sparse("user_links", false);
		$t->sparse("user_data", false);
	} else {
		$t->sparse("guest_links", false);
		$t->sparse("guest_data", false);
	}

	if(!$layout_type) { $layout_type = "aa"; }
	$block_parsed = true;

