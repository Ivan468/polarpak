<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  user_address_edit.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/sorter.php");
	include_once("./includes/navigator.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	check_user_security("user_addresses");

	$script_name   = "user_address_edit.php";
	$current_page  = get_custom_friendly_url("user_address_edit.php");

	$pb_id = 1; // we use only one block for page without layout
	$sw = trim(get_param("sw"));
	$select_type = get_param("select_type");

	$t = new VA_Template($settings["templates_dir"]);
	$t->set_file("main","user_address_edit.html");
	$t->set_var("pb_id", $pb_id);
	$t->set_var("user_address_select_href", "user_address_select.php");
	$t->set_var("select_type", htmlspecialchars($select_type));
	$css_file = "";
	if (isset($settings["style_name"]) && $settings["style_name"]) {
		$css_file = "styles/" . $settings["style_name"];
		if (isset($settings["scheme_name"]) && $settings["scheme_name"]) {
			$css_file .= "_" . $settings["scheme_name"];
		}
		$css_file .= ".css";
	}
	$t->set_var("css_file", $css_file);

	$block = array();
	$php_script = "block_user_address.php";
	if (file_exists("./blocks_custom/".$php_script)) {
		include("./blocks_custom/".$php_script);
	} else {
		include("./blocks/".$php_script);
	}

	// set final block title
	$t->set_block("block_title", get_translation($default_title));
	$t->parse("block_title", false);
	$t->parse_to("block_body", "block_user_address", false);

	$t->pparse("main");

?>