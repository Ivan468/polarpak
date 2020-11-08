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
	$subscribe_desc = str_replace("{button_name}", va_message("SUBSCRIBE_BUTTON"), va_message("SUBSCRIBE_FORM_MSG"));
	$t->set_var("SUBSCRIBE_FORM_MSG", $subscribe_desc);

	// parse sub blocks if they were set
	parse_sub_blocks("block_body");

	$footer_head = get_translation(get_setting_value($settings, "footer_head"));
	$footer_foot = get_translation(get_setting_value($settings, "html_below_footer"));
	$t->set_block("footer_head", $footer_head);
	$t->set_block("footer_foot", $footer_foot);

	parse_sub_blocks("footer_head");
	parse_sub_blocks("footer_foot");

	$t->parse("footer_head", false);
	$t->parse("footer_foot", false);

	if(!isset($layout_type) || !$layout_type) { $layout_type = "aa"; }
	$block_parsed = true;
