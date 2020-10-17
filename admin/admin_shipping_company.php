<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_shipping_company.php                               ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/record.php");

	check_admin_security("static_tables");

	$custom_breadcrumb = array(
		"admin_global_settings.php" => SETTINGS_MSG,
		$orders_pages_site_url."admin_order_info.php" => ORDERS_MSG,
		"admin_shipping_companies.php" => SHIPPING_COMPANIES_MSG,
		"admin_shipping_company.php" => EDIT_MSG,
	);

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_shipping_company.html");
	$t->set_var("admin_shipping_company_href",   "admin_shipping_company.php");
	$t->set_var("admin_shipping_companies_href", "admin_shipping_companies.php");
	$t->set_var("admin_upload_href",    "admin_upload.php");
	$t->set_var("admin_select_href",    "admin_select.php");
	$full_image_url = get_setting_value($settings, "full_image_url", 0);
	$site_url_path = get_setting_value($settings, "site_url", "");
	if ($full_image_url){
		$t->set_var("image_site_url", $site_url_path);					
	} else {
		$t->set_var("image_site_url", "");					
	}


	$t->set_var("admin_lookup_tables_href", "admin_lookup_tables.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", COMPANY_SELECT_FIELD, CONFIRM_DELETE_MSG));

	$r = new VA_Record($table_prefix . "shipping_companies");
	$r->return_page  = "admin_shipping_companies.php";

	$yes_no = 
		array( 
			array(1, YES_MSG), array(0, NO_MSG)
		);

	$r->add_where("shipping_company_id", INTEGER);
	$r->add_textbox("company_name", TEXT, COMPANY_NAME_FIELD);
	$r->change_property("company_name", REQUIRED, true);
	$r->add_textbox("company_url", TEXT);
	$r->add_textbox("image_small", TEXT);
	$r->add_textbox("image_large", TEXT);
	$r->add_textbox("short_description", TEXT);
	$r->add_textbox("full_description", TEXT);
	$r->process();

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_shipping_companies_href", "admin_shipping_companies.php");
	$t->pparse("main");

?>