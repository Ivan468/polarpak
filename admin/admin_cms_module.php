<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_cms_module.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/record.php");

	check_admin_security("cms_settings");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$site_url_path = $settings["site_url"] ? $settings["site_url"] : "../";
	$t->set_var("css_file", $site_url_path . "styles/" . $settings["style_name"] . ".css");
	$t->set_var("html_editor", get_setting_value($settings, "html_editor", 1));
	$t->set_file("main","admin_cms_module.html");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", MODULE_MSG, CONFIRM_DELETE_MSG));

	$admin_cms_modules_url = new VA_URL("admin_cms_modules.php", false);
	$admin_cms_modules_url->add_parameter("sort_ord", REQUEST, "sort_ord");
	$admin_cms_modules_url->add_parameter("sort_dir", REQUEST, "sort_dir");
	$admin_cms_modules_url->add_parameter("page", REQUEST, "page");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_cms_modules_href", "admin_cms_modules.php");
	$t->set_var("admin_cms_module_href", "admin_cms_module.php");
	$t->set_var("admin_cms_modules_url", $admin_cms_modules_url->get_url());

	$r = new VA_Record($table_prefix . "cms_modules");
	$r->return_page = "admin_cms_modules.php";

	$r->add_where("module_id", INTEGER);
	$r->add_textbox("module_order", INTEGER, SORT_ORDER_MSG);
	$r->change_property("module_order", REQUIRED, true);
	$r->add_textbox("module_code", TEXT, CODE_MSG);
	$r->change_property("module_code", REQUIRED, true);
	$r->add_textbox("module_name", TEXT, NAME_MSG);
	$r->change_property("module_name", REQUIRED, true);
	$r->add_hidden("sort_ord", TEXT);
	$r->add_hidden("sort_dir", TEXT);
	$r->set_event(AFTER_DELETE, "remove_module_data");

	$r->process();

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

	function remove_module_data()
	{
		global $r, $db, $table_prefix;
		$module_id = $r->get_value("module_id");
	}

?>