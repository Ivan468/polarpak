<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_upgrade_sqls_5.5.php                               ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	check_admin_security("system_upgrade");

	// vars to add new CMS pages, blocks, and page settings
	$new_page_id = 0; $new_block_id = 0; $new_ps_id = 0;

	// check admin menu
	$admin_menu_id = "";
	$sql  = " SELECT menu_id FROM ".$table_prefix."menus ";
	$sql .= " WHERE menu_type=5 ";
	$admin_menu_id = get_db_value($sql);

	// get property_id and value_id properties for upgrade
	// get max property_id
	$sql = " SELECT MAX(property_id) FROM ".$table_prefix."cms_blocks_properties "; 
	$property_id = get_db_value($sql);
	// get max value_id
	$sql = " SELECT MAX(value_id) FROM ".$table_prefix."cms_blocks_values "; 
	$value_id = get_db_value($sql);

	// check for new table creation
  $tables = $db->get_tables();
	$support_types_departments = false;
	$support_types_sites = false;
	$support_products_departments = false;
	foreach ($tables as $table_id => $table_name) {
		if ($table_name == $table_prefix."support_types_departments") {
			$support_types_departments = true;
		} else if ($table_name == $table_prefix."support_types_sites") {
			$support_types_sites = true;
		} else if ($table_name == $table_prefix."support_products_departments") {
			$support_products_departments = true;
		}
	}

	// two separate class fields for admin and user in categories table 
	$categories_list_class = false; 
	$categories_user_list_class = false; 
	$categories_admin_list_class = false; 
	$fields = $db->get_fields($table_prefix."categories");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "user_list_class") {
			$categories_user_list_class = true;
		} else if ($field_info["name"] == "admin_list_class") {
			$categories_admin_list_class = true;
		} else if ($field_info["name"] == "list_class") {
			$categories_list_class = true;
		} 
	}

	$sd_dep_settings = false; 
	$sd_short_name = false; $sd_dep_name = false;
	$sd_short_title = false; $sd_full_title = false;
	$fields = $db->get_fields($table_prefix."support_departments");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "dep_settings") {
			$sd_dep_settings = true;
		} else if ($field_info["name"] == "short_name") {
			$sd_short_name = true;
		} else if ($field_info["name"] == "dep_name") {
			$sd_dep_name = true;
		} else if ($field_info["name"] == "short_title") {
			$sd_short_title = true;
		} else if ($field_info["name"] == "full_title") {
			$sd_full_title = true;
		}
	}

	if (comp_vers("5.4.1", $current_db_version) == 1)
	{
		if (!$categories_admin_list_class) {
			if ($db_type == "mysql") {
				$sqls[] = "ALTER TABLE " . $table_prefix . "categories ADD COLUMN admin_list_class VARCHAR(32) AFTER list_class ";
			} else {
				$sqls[] = "ALTER TABLE " . $table_prefix . "categories ADD COLUMN admin_list_class VARCHAR(32) ";
			}
			$sqls[] = " UPDATE " . $table_prefix . "categories SET admin_list_class=list_class ";
		}
		if (!$categories_user_list_class) {
			if ($db_type == "mysql") {
				$sqls[] = "ALTER TABLE " . $table_prefix . "categories ADD COLUMN user_list_class VARCHAR(32) AFTER list_class ";
			} else {
				$sqls[] = "ALTER TABLE " . $table_prefix . "categories ADD COLUMN user_list_class VARCHAR(32) ";
			}
			$sqls[] = " UPDATE " . $table_prefix . "categories SET user_list_class=list_class ";
		}
		if ($categories_list_class) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "categories DROP COLUMN list_class ";
		}

		if (!$sd_dep_settings) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD dep_settings TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD dep_settings TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD dep_settings TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD dep_settings LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.4.1");
	}


	if (comp_vers("5.4.2", $current_db_version) == 1)
	{
		// tax rates: add global order fixed tax amount field 
		$tax_rates_order_fixed_amount = false; 
		$tax_rates_order_tax_amount = false;
		$fields = $db->get_fields($table_prefix."tax_rates");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "order_fixed_amount") {
				$tax_rates_order_fixed_amount = true;
			} else if ($field_info["name"] == "order_tax_amount") {
				$tax_rates_order_tax_amount = true;
			}
		}
		if (!$tax_rates_order_fixed_amount) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "tax_rates ADD order_fixed_amount DOUBLE(16,2) AFTER fixed_amount ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "tax_rates ADD order_fixed_amount FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "tax_rates ADD order_fixed_amount FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "tax_rates ADD order_fixed_amount FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
			if ($tax_rates_order_tax_amount) {
				$sqls[] = " UPDATE " . $table_prefix . "tax_rates SET order_fixed_amount=order_tax_amount ";
			}
		}
		if ($tax_rates_order_tax_amount) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "tax_rates DROP COLUMN order_tax_amount ";
		}

		$orders_taxes_order_fixed_amount = false; 
		$orders_taxes_order_tax_amount = false;
		$fields = $db->get_fields($table_prefix."orders_taxes");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "order_fixed_amount") {
				$orders_taxes_order_fixed_amount = true;
			} else if ($field_info["name"] == "order_tax_amount") {
				$orders_taxes_order_tax_amount = true;
			} 
		}

		if (!$orders_taxes_order_fixed_amount) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "orders_taxes ADD order_fixed_amount DOUBLE(16,2) AFTER fixed_amount ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "orders_taxes ADD order_fixed_amount FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "orders_taxes ADD order_fixed_amount FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "orders_taxes ADD order_fixed_amount FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
			if ($orders_taxes_order_tax_amount) {
				$sqls[] = " UPDATE " . $table_prefix . "orders_taxes SET order_fixed_amount=order_tax_amount ";
			}
		}
		if ($orders_taxes_order_tax_amount) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders_taxes DROP COLUMN order_tax_amount ";
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.4.2");
	}


	if (comp_vers("5.4.3", $current_db_version) == 1)
	{
		$shipping_rules_hide_no_ship = false; 
		$shipping_rules_countries_all = false;
		$fields = $db->get_fields($table_prefix."shipping_rules");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "hide_no_ship_item") {
				$shipping_rules_hide_no_ship = true;
			} else if ($field_info["name"] == "countries_all") {
				$shipping_rules_countries_all = true;
			} 
		}

		if (!$shipping_rules_hide_no_ship) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "shipping_rules ADD hide_no_ship_item TINYINT DEFAULT '0'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "shipping_rules ADD hide_no_ship_item TINYINT DEFAULT '0'",
				"postgre" => "ALTER TABLE " . $table_prefix . "shipping_rules ADD hide_no_ship_item SMALLINT DEFAULT '0'",
				"access"  => "ALTER TABLE " . $table_prefix . "shipping_rules ADD hide_no_ship_item BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "shipping_rules SET hide_no_ship_item=0 ";
		}

		if (!$shipping_rules_hide_no_ship) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "shipping_rules ADD countries_all TINYINT DEFAULT '0'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "shipping_rules ADD countries_all TINYINT DEFAULT '0'",
				"postgre" => "ALTER TABLE " . $table_prefix . "shipping_rules ADD countries_all SMALLINT DEFAULT '0'",
				"access"  => "ALTER TABLE " . $table_prefix . "shipping_rules ADD countries_all BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "shipping_rules SET countries_all=1 WHERE is_country_restriction=0 OR is_country_restriction IS NULL ";
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.4.3");
	}

	if (comp_vers("5.4.4", $current_db_version) == 1)
	{
		if (!$support_types_departments) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE va_support_types_departments (
          `type_id` INT(11) NOT NULL DEFAULT '0',
          `dep_id` INT(11) NOT NULL DEFAULT '0'
          ,KEY dep_id (dep_id)
          ,KEY type_id (type_id)
				) DEFAULT CHARACTER SET=utf8mb4 ";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE va_support_types_departments (
          type_id INTEGER,
          dep_id INTEGER
          )";
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE TABLE va_support_types_departments (
          type_id INT4 NOT NULL default '0',
          dep_id INT4 NOT NULL default '0'
          )";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE va_support_types_departments (
          [type_id] INTEGER,
          [dep_id] INTEGER
          )";
			}
			if ($db_type != "mysql") {
				$sqls[] = "CREATE INDEX va_support_types_departments_dep_id ON va_support_types_departments (dep_id)";
				$sqls[] = "CREATE INDEX va_support_types_departments_type_id ON va_support_types_departments (type_id)";
			}
		}

		if (!$support_types_sites) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE va_support_types_sites (
          `type_id` INT(11) NOT NULL DEFAULT '0',
          `site_id` INT(11) NOT NULL DEFAULT '0'
          ,KEY site_id (site_id)
          ,KEY type_id (type_id)
				) DEFAULT CHARACTER SET=utf8mb4 ";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE va_support_types_sites (
          type_id INTEGER,
          site_id INTEGER
          )";
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE TABLE va_support_types_sites (
          type_id INT4 NOT NULL default '0',
          site_id INT4 NOT NULL default '0'
          )";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE va_support_types_sites (
          [type_id] INTEGER,
          [site_id] INTEGER
          )";
			}
			if ($db_type != "mysql") {
				$sqls[] = "CREATE INDEX va_support_types_sites_site_id ON va_support_types_sites (site_id)";
				$sqls[] = "CREATE INDEX va_support_types_sites_type_id ON va_support_types_sites (type_id)";
			}
		}

		if (!$support_products_departments) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE va_support_products_departments (
          `product_id` INT(11) NOT NULL DEFAULT '0',
          `dep_id` INT(11) NOT NULL DEFAULT '0'
          ,KEY dep_id (dep_id)
          ,KEY product_id (product_id)
					) DEFAULT CHARACTER SET=utf8mb4 ";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE va_support_products_departments (
          product_id INTEGER,
          dep_id INTEGER
          )";
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE TABLE va_support_products_departments (
          product_id INT4 NOT NULL default '0',
          dep_id INT4 NOT NULL default '0'
          )";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE va_support_products_departments (
          [product_id] INTEGER,
          [dep_id] INTEGER
          )";
			}
			if ($db_type != "mysql") {
				$sqls[] = "CREATE INDEX va_support_products_departments_dep_id ON va_support_products_departments (dep_id)";
				$sqls[] = "CREATE INDEX va_support_products_departments_product_id ON va_support_products_departments (product_id)";
			}
		}

		$support_departments_dep_order = false; 
		$fields = $db->get_fields($table_prefix."support_departments");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "dep_order") {
				$support_departments_dep_order = true;
			} 
		}

		if (!$support_departments_dep_order) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD dep_order INT(11) DEFAULT '1' ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD dep_order INTEGER DEFAULT '1' ",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD dep_order INT4  DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD dep_order INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "support_departments SET dep_order=1 ";
		}

		$support_types_deps_all = false; 
		$support_types_sites_all = false;
		$fields = $db->get_fields($table_prefix."support_types");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "deps_all") {
				$support_types_deps_all = true;
			} else if ($field_info["name"] == "sites_all") {
				$support_types_sites_all = true;
			} 
		}

		if (!$support_types_deps_all) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_types ADD deps_all TINYINT DEFAULT '1'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_types ADD deps_all TINYINT DEFAULT '1'",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_types ADD deps_all SMALLINT DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "support_types ADD deps_all BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "support_types SET deps_all=1 ";
		}
		if (!$support_types_sites_all) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_types ADD sites_all TINYINT DEFAULT '1'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_types ADD sites_all TINYINT DEFAULT '1'",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_types ADD sites_all SMALLINT DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "support_types ADD sites_all BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "support_types SET sites_all=1 ";
		}


		$support_products_deps_all = false; 
		$support_products_product_order = false; 
		$fields = $db->get_fields($table_prefix."support_products");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "deps_all") {
				$support_products_deps_all = true;
			} else if ($field_info["name"] == "product_order") {
				$support_products_product_order = true;
			} 
		}
		if (!$support_products_deps_all) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_products ADD deps_all TINYINT DEFAULT '1'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_products ADD deps_all TINYINT DEFAULT '1'",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_products ADD deps_all SMALLINT DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "support_products ADD deps_all BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "support_products support_types SET deps_all=1 ";
		}

		if (!$support_products_product_order) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_products ADD product_order INT(11) DEFAULT '1' AFTER product_id ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_products ADD product_order INTEGER DEFAULT '1' ",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_products ADD product_order INT4  DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "support_products ADD product_order INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "support_products SET product_order=1 ";
		}


		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.4.4");
	}


	if (comp_vers("5.4.5", $current_db_version) == 1)
	{
		if (!$sd_short_name) {
			if ($db_type == "mysql") {
				$sqls[] = "ALTER TABLE " . $table_prefix . "support_departments ADD COLUMN short_name VARCHAR(255) AFTER short_title ";
			} else {
				$sqls[] = "ALTER TABLE " . $table_prefix . "support_departments ADD COLUMN short_name VARCHAR(255) ";
			}
			$sqls[] = " UPDATE " . $table_prefix . "support_departments SET short_name=short_title ";

			if ($sd_short_title) {
				$sqls[] = "ALTER TABLE " . $table_prefix . "support_departments DROP COLUMN short_title ";
			}
		}

		if (!$sd_dep_name) {
			if ($db_type == "mysql") {
				$sqls[] = "ALTER TABLE " . $table_prefix . "support_departments ADD COLUMN dep_name VARCHAR(255) AFTER full_title ";
			} else {
				$sqls[] = "ALTER TABLE " . $table_prefix . "support_departments ADD COLUMN dep_name VARCHAR(255) ";
			}
			$sqls[] = " UPDATE " . $table_prefix . "support_departments SET dep_name=full_title ";

			if ($sd_full_title) {
				$sqls[] = "ALTER TABLE " . $table_prefix . "support_departments DROP COLUMN full_title ";
			}
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.4.5");
	}


	if (comp_vers("5.5", $current_db_version) == 1)
	{
		// check for new max_recs parameter for products latest block
		$new_ticket_block_id = ""; 
		$sql  = " SELECT block_id FROM ".$table_prefix."cms_blocks "; 
		$sql .= " WHERE block_code='ticket_new' "; 
		$new_ticket_block_id = get_db_value($sql);
		if ($new_ticket_block_id) {
			$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE variable_name='deps_ids' "; 
			$sql .= " AND block_id=" . $db->tosql($new_ticket_block_id, INTEGER); 
			$db->query($sql);
			if (!$db->next_record()) {
				$property_id++; 
				$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,after_control_html,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $new_ticket_block_id, 1, 'DEPARTMENTS_MSG', '<br>({IDS_DESC})', 'TEXTBOX', NULL, NULL, 'deps_ids', NULL, 0)";
			}

			$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE variable_name='types_ids' "; 
			$sql .= " AND block_id=" . $db->tosql($new_ticket_block_id, INTEGER); 
			$db->query($sql);
			if (!$db->next_record()) {
				$property_id++; 
				$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,after_control_html,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $new_ticket_block_id, 2, 'TYPES_MSG', '<br>({IDS_DESC})', 'TEXTBOX', NULL, NULL, 'types_ids', NULL, 0)";
			}
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.5");
	}

