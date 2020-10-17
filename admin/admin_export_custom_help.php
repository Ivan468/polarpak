<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_export_custom_help.php                             ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once($root_folder_path . "messages/" . $language_code . "/download_messages.php");
	include_once("./admin_common.php");

	check_admin_security("sales_orders");

	$cc     = get_param("cc");
	$links  = get_param("links");
	$status = get_param("status");
	$table  = get_param("table");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_export_custom_help.html");
	$t->show_tags = true;

	if ($table == "items") {
		check_admin_security("products_categories");
		include_once("./admin_table_items.php");
	} elseif ($table == "categories") {
		check_admin_security("products_categories");
		include_once("./admin_table_categories.php");
	} elseif ($table == "users") {
		check_admin_security("site_users");
		include_once("./admin_table_users.php");
	} elseif ($table == "orders" || $table == "orders_items") {
		check_admin_security("sales_orders");
		include_once("./admin_table_orders.php");
	} elseif ($table == "tax_rates") {
		check_admin_security("tax_rates");
		include_once("./admin_table_tax_rates.php");
	} else if ($table == "newsletters_emails") {
		check_admin_security("newsletter");
		include("./admin_table_newsletters_emails.php");
	} else if ($table == "newsletters_users") {
		check_admin_security("export_users");
		include("./admin_table_emails.php");
	}

	$fields = $db_columns;
	$table_alias = "";

	foreach ($fields as $column_name => $column_info) {
		// new and old formats
		$field_type = isset($column_info["title"]) ? $column_info["field_type"] : $column_info[2];
		$field_title = isset($column_info["title"]) ? $column_info["title"] : $column_info[0];
		if ($field_type != HIDE_DB_FIELD && $field_type != RELATED_DB_FIELD 
			&& !preg_match("/^order_item_property_/", $column_name)) {
			$t->set_var("field_name", $table_alias . $column_name);
			$t->set_var("field_title", $field_title);
			$t->parse("fields", true);
		}
	}

	if ($table == "orders" || $table == "orders_items") {
		$fields = $related_columns;
		$table_alias = $related_table_alias . "_";
		foreach ($fields as $column_name => $column_info) {
			// new and old formats
			$field_type = isset($column_info["title"]) ? $column_info["field_type"] : $column_info[2];
			$field_title = isset($column_info["title"]) ? $column_info["title"] : $column_info[0];
			if ($field_type != HIDE_DB_FIELD && $field_type != RELATED_DB_FIELD 
				&& !preg_match("/^order_item_property_/", $column_name)) {
				$t->set_var("field_name", $table_alias . $column_name);
				$t->set_var("field_title", $field_title);
				$t->parse("fields", true);
			}
		}
	}


	$t->pparse("main");

?>