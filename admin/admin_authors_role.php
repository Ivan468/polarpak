<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_authors_role.php                                   ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/friendly_functions.php");
	include_once($root_folder_path . "includes/tabs_functions.php");

	check_admin_security("");

	$win_type = get_param("win_type");
	$operation = get_param("operation");
	$sites = get_param("sites");
	$languages = get_param("languages");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_authors_role.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_lookup_tables_href", "admin_lookup_tables.php");
	$t->set_var("admin_authors_href", "admin_authors.php");
	$t->set_var("admin_author_href", "admin_author.php");
	$t->set_var("admin_authors_role_href", "admin_authors_role.php");
	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", va_constant("AUTHOR_ROLE_MSG"), CONFIRM_DELETE_MSG));

	$full_image_url = get_setting_value($settings, "full_image_url", 0);
	$site_url_path = get_setting_value($settings, "site_url", "");
	if ($full_image_url){
		$t->set_var("site_url", $site_url_path);					
	} else {
		$t->set_var("site_url", "");					
	}

	$r = new VA_Record($table_prefix . "authors_roles");
	$r->return_page = "admin_authors_roles.php";
	$r->add_where("role_id", INTEGER);

	$r->add_textbox("role_code", TEXT, CODE_MSG);
	$r->change_property("role_code", REQUIRED, true);
	$r->add_textbox("role_name", TEXT, NAME_MSG);
	$r->change_property("role_name", REQUIRED, true);

	$r->add_hidden("sw", TEXT);
	$r->add_hidden("form_name", TEXT);
	$r->add_hidden("items_field", TEXT);
	$r->add_hidden("items_object", TEXT);
	$r->add_hidden("item_template", TEXT);
	$r->add_hidden("selection_type", TEXT);
	$r->add_hidden("win_type", TEXT);
	$r->add_hidden("sort_ord", TEXT);
	$r->add_hidden("sort_dir", TEXT);
	$r->add_hidden("page", TEXT);

	$r->process();

	if ($win_type != "popup") {

		$custom_breadcrumb = array(
			"admin_global_settings.php" => SETTINGS_MSG,
			"admin_lookup_tables.php" => STATIC_TABLES_MSG,
			$r->get_return_url() => va_constant("AUTHOR_ROLE_MSG"),
			"admin_author.php" => EDIT_MSG,
		);

		include_once("./admin_header.php");
		include_once("./admin_footer.php");
	}

	$tabs = array(
		"general" => array("title" => ADMIN_GENERAL_MSG),
	);
	parse_tabs($tabs);


	$t->pparse("main");

?>