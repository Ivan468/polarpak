<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_support_pipe.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path."messages/".$language_code."/support_messages.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("support_departments");

	$operation = get_param("operation");
	$pipe_id = get_param("pipe_id");

	// start building breadcrumb
	$va_trail = array(
		"admin_menu.php?code=settings" => va_message("SETTINGS_MSG"),
		"admin_menu.php?code=helpdesk-settings" => va_message("HELPDESK_MSG"),
		"admin_support_pipes.php" => va_message("EMAIL_PIPES_MSG"),
		"admin_support_property.php?pipe_id=".urlencode($pipe_id) => va_message("EDIT_MSG"),
	);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_support_pipe.html");

	$t->set_var("admin_support_href", "admin_support.php");
	$t->set_var("admin_support_dep_edit_href", "admin_support_dep_edit.php");
	$t->set_var("admin_support_departments_href", "admin_support_departments.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", va_message("EMAIL_PIPE_MSG"), va_message("CONFIRM_DELETE_MSG")));

	$r = new VA_Record($table_prefix . "support_pipes");
	$r->return_page = "admin_support_pipes.php";

	$r->add_where("pipe_id", INTEGER);

	$r->add_textbox("incoming_email", TEXT, va_message("INCOMING_EMAIL_MSG"));
	$r->change_property("incoming_email", REQUIRED, true);
	$r->change_property("incoming_email", REGEXP_MASK, EMAIL_REGEXP);
	$r->change_property("incoming_email", REGEXP_ERROR, INCORRECT_EMAIL_MESSAGE);

	$r->add_textbox("outgoing_email", TEXT, va_message("OUTGOING_EMAIL_MSG"));
	$r->change_property("outgoing_email", REGEXP_MASK, EMAIL_REGEXP);
	$r->change_property("outgoing_email", REGEXP_ERROR, INCORRECT_EMAIL_MESSAGE);

	$support_products = get_db_values("SELECT dep_id, dep_name FROM " . $table_prefix . "support_departments", array(array("", va_message("SUPPORT_SELECT_DEP_MSG"))));
	$r->add_select("dep_id", INTEGER, $support_products, va_message("SUPPORT_DEPARTMENT_FIELD"));
	$r->change_property("dep_id", REQUIRED, true);

	$support_types = get_db_values("SELECT type_id, type_name FROM " . $table_prefix . "support_types", array(array("", va_message("SELECT_TYPE_MSG"))));
	$r->add_select("support_type_id", INTEGER, $support_types, va_message("SUPPORT_TYPE_FIELD"));
	if (count($support_types) < 2) {
		$r->change_property("support_type_id", SHOW, false);
	}

	$support_products = get_db_values("SELECT product_id, product_name FROM " . $table_prefix . "support_products", array(array("", va_message("SELECT_PRODUCT_MSG"))));
	$r->add_select("support_product_id", INTEGER, $support_products, va_message("SUPPORT_PRODUCT_FIELD"));
	if (count($support_products) < 2) {
		$r->change_property("support_product_id", SHOW, false);
	}

	$r->process();

	
	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_href", "admin.php");
	$t->pparse("main");

