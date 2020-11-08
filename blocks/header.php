<?php
	// Save original CMS vars
	$header_vars = $vars;
	$html_template = get_setting_value($block, "html_template", "header.html"); 
 	$t->set_file("block_body", $html_template);
	parse_sub_blocks("block_body");

	// return orginal header vars and clear CMS vars
	$vars = $header_vars;
	$block_css_class = ""; $extra_css_class = ""; // clear before include block

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

