<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_cms_page.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path."includes/record.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");
	include_once($root_folder_path."messages/".$language_code."/forum_messages.php");
	include_once($root_folder_path."messages/".$language_code."/manuals_messages.php");
	include_once($root_folder_path."messages/".$language_code."/profiles_messages.php");

	check_admin_security("cms_settings");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$site_url_path = $settings["site_url"] ? $settings["site_url"] : "../";
	$t->set_var("css_file", $site_url_path . "styles/" . $settings["style_name"] . ".css");
	$t->set_var("html_editor", get_setting_value($settings, "html_editor", 1));
	$t->set_file("main","admin_cms_page.html");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", PAGE_MSG, CONFIRM_DELETE_MSG));

	$admin_cms_pages_url = new VA_URL("admin_cms_pages.php", false);
	$admin_cms_pages_url->add_parameter("sort_ord", REQUEST, "sort_ord");
	$admin_cms_pages_url->add_parameter("sort_dir", REQUEST, "sort_dir");
	$admin_cms_pages_url->add_parameter("page", REQUEST, "page");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_cms_pages_href", "admin_cms_pages.php");
	$t->set_var("admin_cms_page_href", "admin_cms_page.php");
	$t->set_var("admin_cms_pages_url", $admin_cms_pages_url->get_url());

	$r = new VA_Record($table_prefix . "cms_pages");
	$r->return_page = "admin_cms_pages.php";

	$r->add_where("page_id", INTEGER);
	$modules = get_db_values("SELECT module_id, module_name FROM " . $table_prefix . "cms_modules ORDER BY module_order, module_name ", array(array("", "")));
	$r->add_select("module_id", INTEGER, $modules, MODULE_MSG);
	$r->change_property("module_id", REQUIRED, true);
	$r->change_property("module_id", REQUIRED, true);
	$r->add_textbox("page_order", INTEGER, SORT_ORDER_MSG);
	$r->change_property("page_order", REQUIRED, true);
	$r->add_textbox("page_code", TEXT, CODE_MSG);
	$r->change_property("page_code", REQUIRED, true);
	$r->add_textbox("page_name", TEXT, NAME_MSG);
	$r->change_property("page_name", REQUIRED, true);
	$r->add_hidden("page", TEXT);
	$r->add_hidden("sort_ord", TEXT);
	$r->add_hidden("sort_dir", TEXT);
	$r->set_event(AFTER_DELETE, "remove_page_data");

	$r->process();

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

	function remove_page_data()
	{
		global $r, $db, $table_prefix;
		$page_id = $r->get_value("page_id");
	}

?>