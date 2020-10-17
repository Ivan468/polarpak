<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_upgrade_sqls_5.6.php                               ***
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

	$op_processing_fee = false; 
	$op_processing_tax_free = false; 
	$op_processing_excl_tax = false; 
	$op_processing_tax = false; 
	$op_processing_incl_tax = false; 
	$fields = $db->get_fields($table_prefix."orders_payments");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "processing_fee") {
			$op_processing_fee = true;
		} else if ($field_info["name"] == "processing_tax_free") {
			$op_processing_tax_free = true;
		} else if ($field_info["name"] == "processing_excl_tax") {
			$op_processing_excl_tax = true;
		} else if ($field_info["name"] == "processing_tax") {
			$op_processing_tax = true;
		} else if ($field_info["name"] == "processing_incl_tax") {
			$op_processing_incl_tax = true;
		}
	}

	$newsletters_emails_site_id = false; 
	$fields = $db->get_fields($table_prefix."newsletters_emails");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "site_id") {
			$newsletters_emails_site_id = true;
		}
	}

	$newsletters_users_site_id = false; 
	$fields = $db->get_fields($table_prefix."newsletters_users");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "site_id") {
			$newsletters_users_site_id = true;
		}
	}

	$users_site_id = false; 
	$fields = $db->get_fields($table_prefix."users");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "site_id") {
			$users_site_id = true;
		}
	}


	$fee_min_amount = false; 
	$fee_max_amount = false; 
	$fields = $db->get_fields($table_prefix."payment_systems");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "fee_min_amount") {
			$fee_min_amount = true;
		} else if ($field_info["name"] == "fee_max_amount") {
			$fee_max_amount = true;
		}
	}


	$sites_site_url = false; 
	$sites_admin_url = false; 
	$sites_image_url = false; 
	$fields = $db->get_fields($table_prefix."sites");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "site_url") {
			$sites_site_url = true;
		} else if ($field_info["name"] == "admin_url") {
			$sites_admin_url = true;
		} else if ($field_info["name"] == "image_url") {
			$sites_image_url = true;
		}
	}

	$support_statuses_order = false;
	$support_statuses_keep_assigned = false;
	$fields = $db->get_fields($table_prefix."support_statuses");
	foreach ($fields as $id => $field_info) {
		$field_name = $field_info["name"];
		if ($field_info["name"] == "status_order") {
			$support_statuses_order = true;
		} else if ($field_info["name"] == "is_keep_assigned") {
			$support_statuses_keep_assigned = true;
		}
	}

	if (comp_vers("5.5.1", $current_db_version) == 1)
	{

		if (!$op_processing_fee) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "orders_payments ADD processing_fee DOUBLE(16,2) ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "orders_payments ADD processing_fee FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "orders_payments ADD processing_fee FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "orders_payments ADD processing_fee FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
		}

		if (!$op_processing_tax_free) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "orders_payments ADD processing_tax_free TINYINT DEFAULT '0'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "orders_payments ADD processing_tax_free TINYINT DEFAULT '0'",
				"postgre" => "ALTER TABLE " . $table_prefix . "orders_payments ADD processing_tax_free SMALLINT DEFAULT '0'",
				"access"  => "ALTER TABLE " . $table_prefix . "orders_payments ADD processing_tax_free BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "orders_payments SET processing_tax_free=0 ";
		}

		if (!$op_processing_excl_tax) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "orders_payments ADD processing_excl_tax DOUBLE(16,2) ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "orders_payments ADD processing_excl_tax FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "orders_payments ADD processing_excl_tax FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "orders_payments ADD processing_excl_tax FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
		}

		if (!$op_processing_tax) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "orders_payments ADD processing_tax DOUBLE(16,2) ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "orders_payments ADD processing_tax FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "orders_payments ADD processing_tax FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "orders_payments ADD processing_tax FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
		}

		if (!$op_processing_incl_tax) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "orders_payments ADD processing_incl_tax DOUBLE(16,2) ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "orders_payments ADD processing_incl_tax FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "orders_payments ADD processing_incl_tax FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "orders_payments ADD processing_incl_tax FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.5.1");
	}


	if (comp_vers("5.5.2", $current_db_version) == 1)
	{

		if (!$fee_min_amount) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD fee_min_amount DOUBLE(16,2) ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "payment_systems ADD fee_min_amount FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "payment_systems ADD fee_min_amount FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "payment_systems ADD fee_min_amount FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "payment_systems SET fee_min_amount=fee_min_goods ";
		}

		if (!$fee_max_amount) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD fee_max_amount DOUBLE(16,2) ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "payment_systems ADD fee_max_amount FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "payment_systems ADD fee_max_amount FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "payment_systems ADD fee_max_amount FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "payment_systems SET fee_max_amount=fee_max_goods ";
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.5.2");
	}


	if (comp_vers("5.5.3", $current_db_version) == 1)
	{
		if (!$newsletters_emails_site_id) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD site_id INT(11) DEFAULT '1' ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD site_id INTEGER DEFAULT '1' ",
				"postgre" => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD site_id INT4 DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD site_id INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
		}

		if (!$newsletters_users_site_id) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters_users ADD site_id INT(11) DEFAULT '1' ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "newsletters_users ADD site_id INTEGER DEFAULT '1' ",
				"postgre" => "ALTER TABLE " . $table_prefix . "newsletters_users ADD site_id INT4 DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "newsletters_users ADD site_id INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "newsletters_users SET site_id=0 ";
		}

		if (!$users_site_id) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "users ADD site_id INT(11) DEFAULT '1' ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "users ADD site_id INTEGER DEFAULT '1' ",
				"postgre" => "ALTER TABLE " . $table_prefix . "users ADD site_id INT4 DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "users ADD site_id INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "users SET site_id=0 ";
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.5.3");
	}


	if (comp_vers("5.5.4", $current_db_version) == 1)
	{
		if (!$support_statuses_order) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_statuses ADD status_order TINYINT DEFAULT '1' AFTER status_id ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_statuses ADD status_order TINYINT DEFAULT '1'",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_statuses ADD status_order SMALLINT DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "support_statuses ADD status_order BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "support_statuses SET status_order=1 ";
		}

		if (!$support_statuses_keep_assigned) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_statuses ADD is_keep_assigned TINYINT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_statuses ADD is_keep_assigned TINYINT ",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_statuses ADD is_keep_assigned SMALLINT ",
				"access"  => "ALTER TABLE " . $table_prefix . "support_statuses ADD is_keep_assigned BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.5.4");
	}

	if (comp_vers("5.6", $current_db_version) == 1)
	{
		if (!$sites_site_url) {
			if ($db_type == "mysql") {
				$sqls[] = "ALTER TABLE " . $table_prefix . "sites ADD COLUMN site_url VARCHAR(255) AFTER site_name ";
			} else {
				$sqls[] = "ALTER TABLE " . $table_prefix . "sites ADD COLUMN site_url VARCHAR(255) ";
			}
		}
		if (!$sites_admin_url) {
			if ($db_type == "mysql") {
				$sqls[] = "ALTER TABLE " . $table_prefix . "sites ADD COLUMN admin_url VARCHAR(255) AFTER site_url ";
			} else {
				$sqls[] = "ALTER TABLE " . $table_prefix . "sites ADD COLUMN admin_url VARCHAR(255) ";
			}
		}
		if (!$sites_image_url) {
			if ($db_type == "mysql") {
				$sqls[] = "ALTER TABLE " . $table_prefix . "sites ADD COLUMN image_url VARCHAR(255) AFTER admin_url ";
			} else {
				$sqls[] = "ALTER TABLE " . $table_prefix . "sites ADD COLUMN image_url VARCHAR(255) ";
			}
		}
		if (!$sites_site_url) {
			$db_sites = array();
			$sql = " SELECT * FROM ".$table_prefix."sites ";
			$db->query($sql);
			while ($db->next_record()) {
				$db_site_id = $db->f("site_id");
				$db_sites[$db_site_id] = array("site_url" => "", "admin_url" => "");
			}
			// check site url first
			$sql = " SELECT * FROM ".$table_prefix."global_settings WHERE setting_type='global' AND setting_name='site_url' ";
			$db->query($sql);
			while ($db->next_record()) {
				$db_site_id = $db->f("site_id");
				$db_site_url = trim($db->f("setting_value"));
				if (isset($db_sites[$db_site_id])) {
					$db_sites[$db_site_id]["site_url"] = $db_site_url;
				}
			}
			// check backend site url 
			$sql = " SELECT * FROM ".$table_prefix."global_settings WHERE setting_type='global' AND setting_name='admin_site_url' ";
			$db->query($sql);
			while ($db->next_record()) {
				$db_site_id = $db->f("site_id");
				$db_admin_url = trim($db->f("setting_value"));
				if (isset($db_sites[$db_site_id])) {
					$db_sites[$db_site_id]["admin_url"] = $db_admin_url;
				}
			}
			foreach ($db_sites as $db_site_id => $db_site) {
				$sql  = " UPDATE ".$table_prefix."sites ";
				$sql .= " SET site_url=" . $db->tosql($db_site["site_url"], TEXT);
				$sql .= " , admin_url=" . $db->tosql($db_site["admin_url"], TEXT);
				$sql .= " WHERE site_id=" . $db->tosql($db_site_id, INTEGER);
				$sqls[] = $sql;
			}
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.6");
	}
