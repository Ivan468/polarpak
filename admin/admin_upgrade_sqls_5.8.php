<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_upgrade_sqls_5.8.php                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
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


	// add new payment fields 
	$orders_payment_encrypt_type = false; 
	$orders_payment_encrypt_data = false; 
	$orders_payment_full_name = false; 
	$orders_payment_first_name = false; 
	$orders_payment_middle_name = false; 
	$orders_payment_last_name = false; 
	$orders_payment_company_name = false; 
	$orders_payment_email = false; 
	$orders_payment_address1 = false; 
	$orders_payment_address2 = false; 
	$orders_payment_address3 = false; 
	$orders_payment_province = false;
	$orders_payment_country_id = false;
	$orders_payment_country_code = false;
	$orders_payment_state_id = false;
	$orders_payment_state_code = false;
	$orders_payment_postal_code = false;
	$orders_payment_phone = false;
	$orders_payment_daytime_phone = false;
	$orders_payment_evening_phone = false;
	$orders_payment_cell_phone = false;
	$orders_payment_card_number = false;
	$orders_payment_card_start_date = false;
	$orders_payment_card_expiry_date= false;
	$orders_payment_card_type_id = false;
	$orders_payment_card_type_code = false;
	$orders_payment_card_issue_number = false;
	$orders_payment_card_security_code = false;
	$orders_payment_token = false;
	$orders_payment_data = false;

	$fields = $db->get_fields($table_prefix."orders");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "payment_encrypt_type") {
			$orders_payment_encrypt_type = true;
		} else if ($field_info["name"] == "payment_encrypt_data") {
			$orders_payment_encrypt_data = true;
		} else if ($field_info["name"] == "payment_full_name") {
			$orders_payment_full_name = true;
		} else if ($field_info["name"] == "payment_first_name") {
			$orders_payment_first_name = true;
		} else if ($field_info["name"] == "payment_middle_name") {
			$orders_payment_middle_name = true;
		} else if ($field_info["name"] == "payment_last_name") {
			$orders_payment_last_name = true;
		} else if ($field_info["name"] == "payment_company_name") {
			$orders_payment_company_name = true;
		} else if ($field_info["name"] == "payment_email") {
			$orders_payment_email = true;
		} else if ($field_info["name"] == "payment_address1") {
			$orders_payment_address1 = true;
		} else if ($field_info["name"] == "payment_address2") {
			$orders_payment_address2 = true;
		} else if ($field_info["name"] == "payment_address3") {
			$orders_payment_address3 = true;
		} else if ($field_info["name"] == "payment_city") {
			$orders_payment_city = true;
		} else if ($field_info["name"] == "payment_province") {
			$orders_payment_province = true;
		} else if ($field_info["name"] == "payment_country_id") {
			$orders_payment_country_id = true;
		} else if ($field_info["name"] == "payment_country_code") {
			$orders_payment_country_code = true;
		} else if ($field_info["name"] == "payment_state_id") {
			$orders_payment_state_id = true;
		} else if ($field_info["name"] == "payment_state_code") {
			$orders_payment_state_code = true;
		} else if ($field_info["name"] == "payment_postal_code") {
			$orders_payment_postal_code = true;
		} else if ($field_info["name"] == "payment_phone") {
			$orders_payment_phone = true;
		} else if ($field_info["name"] == "payment_daytime_phone") {
			$orders_payment_daytime_phone = true;
		} else if ($field_info["name"] == "payment_evening_phone") {
			$orders_payment_evening_phone = true;
		} else if ($field_info["name"] == "payment_cell_phone") {
			$orders_payment_cell_phone = true;
		} else if ($field_info["name"] == "payment_card_number") {
			$orders_payment_card_number = true;
		} else if ($field_info["name"] == "payment_card_start_date") {
			$orders_payment_card_start_date = true;
		} else if ($field_info["name"] == "payment_card_expiry_date") {
			$orders_payment_card_expiry_date= true;
		} else if ($field_info["name"] == "payment_card_type_id") {
			$orders_payment_card_type_id = true;
		} else if ($field_info["name"] == "payment_card_type_code") {
			$orders_payment_card_type_code = true;
		} else if ($field_info["name"] == "payment_card_issue_number") {
			$orders_payment_card_issue_number = true;
		} else if ($field_info["name"] == "payment_card_security_code") {
			$orders_payment_card_security_code = true;
		} else if ($field_info["name"] == "payment_token") {
			$orders_payment_token = true;
		} else if ($field_info["name"] == "payment_data") {
			$orders_payment_data = true;
		} 
	}


	if (comp_vers("5.7.1", $current_db_version) == 1)
	{
		if (!$orders_payment_encrypt_type) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_encrypt_type VARCHAR(64) ";
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "orders ADD payment_encrypt_data TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "orders ADD payment_encrypt_data TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "orders ADD payment_encrypt_data TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "orders ADD payment_encrypt_data LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$orders_payment_token) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_token VARCHAR(255) ";
		}
		if (!$orders_payment_data) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "orders ADD payment_data TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "orders ADD payment_data TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "orders ADD payment_data TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "orders ADD payment_data LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}

		if (!$orders_payment_full_name) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_full_name VARCHAR(255) ";
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_first_name VARCHAR(255) ";
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_middle_name VARCHAR(255) ";
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_last_name VARCHAR(255) ";
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_company_name VARCHAR(255) ";
		}
		if (!$orders_payment_email) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_email VARCHAR(128) ";
		}
		if (!$orders_payment_address1) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_address1 VARCHAR(255) ";
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_address2 VARCHAR(255) ";
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_address3 VARCHAR(255) ";
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_city VARCHAR(128) ";
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_province VARCHAR(128) ";
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "orders ADD payment_country_id INT(11) ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "orders ADD payment_country_id INTEGER ",
				"postgre" => "ALTER TABLE " . $table_prefix . "orders ADD payment_country_id INT4 ",
				"access"  => "ALTER TABLE " . $table_prefix . "orders ADD payment_country_id INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_country_code VARCHAR(8) ";
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "orders ADD payment_state_id INT(11) ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "orders ADD payment_state_id INTEGER ",
				"postgre" => "ALTER TABLE " . $table_prefix . "orders ADD payment_state_id INT4 ",
				"access"  => "ALTER TABLE " . $table_prefix . "orders ADD payment_state_id INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_state_code VARCHAR(8) ";
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_postal_code VARCHAR(32) ";
		}
		if (!$orders_payment_phone) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_phone VARCHAR(32) ";
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_daytime_phone VARCHAR(32) ";
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_evening_phone VARCHAR(32) ";
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_cell_phone VARCHAR(32) ";
		}
		if (!$orders_payment_card_number) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_card_number VARCHAR(64) ";
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_card_number_last4 VARCHAR(64) ";
		}
		if (!$orders_payment_card_start_date) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_card_start_date VARCHAR(64) ";
		}
		if (!$orders_payment_card_expiry_date) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_card_expiry_date VARCHAR(64) ";
		}
		if (!$orders_payment_card_type_id) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "orders ADD payment_card_type_id INT(11) ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "orders ADD payment_card_type_id INTEGER ",
				"postgre" => "ALTER TABLE " . $table_prefix . "orders ADD payment_card_type_id INT4 ",
				"access"  => "ALTER TABLE " . $table_prefix . "orders ADD payment_card_type_id INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$orders_payment_card_type_code) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_card_type_code VARCHAR(32) ";
		}
		if (!$orders_payment_card_issue_number) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_card_issue_number VARCHAR(64) ";
		}
		if (!$orders_payment_card_security_code) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_card_security_code VARCHAR(64) ";
		}
		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.7.1");
	}

	if (comp_vers("5.7.2", $current_db_version) == 1)
	{
		$payment_settings = array(
			"show_cc_name" => "show_payment_full_name",
			"show_cc_first_name" => "show_payment_first_name",
			"show_cc_last_name" => "show_payment_last_name",
			"show_cc_number" => "show_payment_card_number",
			"show_cc_start_date" => "show_payment_card_start_date",
			"show_cc_expiry_date" => "show_payment_card_expiry_date",
			"show_cc_type" => "show_payment_card_type_id",
			"show_cc_issue_number" => "show_payment_issue_number",
			"show_cc_security_code" => "show_payment_security_code",
      
			"cc_name_required" => "payment_full_name_required",
			"cc_first_name_required" => "payment_first_name_required",
			"cc_last_name_required" => "payment_last_name_required",
			"cc_number_required" => "payment_number_required",
			"cc_start_date_required" => "payment_card_start_date_required",
			"cc_expiry_date_required" => "payment_card_expiry_date_required",
			"cc_type_required" => "payment_card_type_id_required",
			"cc_issue_number_required" => "payment_card_issue_number_required",
			"cc_security_code_required" => "payment_card_security_code_required",
    
			"call_center_show_cc_name" => "call_center_show_payment_full_name",
			"call_center_show_cc_first_name" => "call_center_show_payment_first_name",
			"call_center_show_cc_last_name" => "call_center_show_payment_last_name",
			"call_center_show_cc_number" => "call_center_show_payment_card_number",
			"call_center_show_cc_start_date" => "call_center_show_payment_card_start_date",
			"call_center_show_cc_expiry_date" => "call_center_show_payment_card_expiry_date",
			"call_center_show_cc_type" => "call_center_show_payment_card_type_id",
			"call_center_show_cc_issue_number" => "call_center_show_payment_card_issue_number",
			"call_center_show_cc_security_code" => "call_center_show_payment_card_security_code",
      
			"call_center_cc_name_required" => "call_center_payment_full_name_required",
			"call_center_cc_first_name_required" => "call_center_payment_first_name_required",
			"call_center_cc_last_name_required" => "call_center_payment_last_name_required",
			"call_center_cc_number_required" => "call_center_payment_card_number_required",
			"call_center_cc_start_date_required" => "call_center_payment_card_start_date_required",
			"call_center_cc_expiry_date_required" => "call_center_payment_card_expiry_date_required",
			"call_center_cc_type_required" => "call_center_payment_card_type_id_required",
			"call_center_cc_issue_number_required" => "call_center_payment_card_issue_number_required",
			"call_center_cc_security_code_required" => "call_center_payment_card_security_code_required",
		);
		
		foreach ($payment_settings as $old_setting_name => $new_setting_name) {
			$sql  = " UPDATE ".$table_prefix."global_settings ";
			$sql .= " SET setting_name=".$db->tosql($new_setting_name, TEXT);
			$sql .= " WHERE setting_name=".$db->tosql($old_setting_name, TEXT);
			$sqls[] = $sql;
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.7.2");
	}


	// add new fields to support departments table 
	if (comp_vers("5.7.3", $current_db_version) == 1)
	{
		$sd_over_new_admin_mail = false; 
		$sd_over_new_user_mail = false; 
		$sd_over_user_reply_admin_mail = false; 
		$sd_over_user_reply_user_mail = false; 
		$sd_over_manager_reply_admin_mail = false; 
		$sd_over_manager_reply_manager_mail = false; 
		$sd_over_manager_reply_user_mail = false; 
		$sd_over_assign_admin_mail = false; 
		$sd_over_assign_manager_mail = false; 
		$sd_over_assign_to_mail = false; 
		$sd_over_assign_user_mail = false; 
		$fields = $db->get_fields($table_prefix."support_departments");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "over_new_admin_mail") {
				$sd_over_new_admin_mail = true;
			} else if ($field_info["name"] == "over_new_user_mail") {
				$sd_over_new_user_mail = true;
			} else if ($field_info["name"] == "over_user_reply_admin_mail") {
				$sd_over_user_reply_admin_mail = true; 
			} else if ($field_info["name"] == "over_user_reply_user_mail") {
				$sd_over_user_reply_user_mail = true; 
			} else if ($field_info["name"] == "over_manager_reply_admin_mail") {
				$sd_over_manager_reply_admin_mail = true; 
			} else if ($field_info["name"] == "over_manager_reply_manager_mail") {
				$sd_over_manager_reply_manager_mail = true; 
			} else if ($field_info["name"] == "over_manager_reply_user_mail") {
				$sd_over_manager_reply_user_mail = true; 
			} else if ($field_info["name"] == "over_assign_admin_mail") {
				$sd_over_assign_admin_mail = true; 
			} else if ($field_info["name"] == "over_assign_manager_mail") {
				$sd_over_assign_manager_mail = true; 
			} else if ($field_info["name"] == "over_assign_to_mail") {
				$sd_over_assign_to_mail = true; 
			} else if ($field_info["name"] == "over_assign_user_mail") {
				$sd_over_assign_user_mail = true; 
			} 
		}

		if (!$sd_over_new_admin_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD over_new_admin_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_new_admin_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD over_new_admin_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_new_admin_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_over_new_user_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD over_new_user_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_new_user_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD over_new_user_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_new_user_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_over_user_reply_admin_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD over_user_reply_admin_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_user_reply_admin_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD over_user_reply_admin_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_user_reply_admin_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_over_user_reply_user_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD over_user_reply_user_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_user_reply_user_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD over_user_reply_user_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_user_reply_user_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_over_manager_reply_admin_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD over_manager_reply_admin_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_manager_reply_admin_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD over_manager_reply_admin_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_manager_reply_admin_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_over_manager_reply_manager_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD over_manager_reply_manager_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_manager_reply_manager_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD over_manager_reply_manager_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_manager_reply_manager_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_over_manager_reply_user_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD over_manager_reply_user_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_manager_reply_user_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD over_manager_reply_user_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_manager_reply_user_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_over_assign_admin_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD over_assign_admin_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_assign_admin_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD over_assign_admin_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_assign_admin_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_over_assign_manager_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD over_assign_manager_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_assign_manager_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD over_assign_manager_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_assign_manager_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_over_assign_to_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD over_assign_to_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_assign_to_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD over_assign_to_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_assign_to_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_over_assign_user_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD over_assign_user_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_assign_user_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD over_assign_user_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD over_assign_user_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.7.3");
	}


	// add new country_settings field
	if (comp_vers("5.7.4", $current_db_version) == 1)
	{
		$countries_country_settings = false; 
		$fields = $db->get_fields($table_prefix."countries");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "country_settings") {
				$countries_country_settings = true;
			}
		}

		if (!$countries_country_settings) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "countries ADD country_settings TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "countries ADD country_settings TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "countries ADD country_settings TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "countries ADD country_settings LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.7.4");
	}


	// update CMS settings for product categories and add new slideshow settings
	if (comp_vers("5.7.5", $current_db_version) == 1)
	{
		$sql  = " SELECT block_id FROM ".$table_prefix."cms_blocks "; 
		$sql .= " WHERE block_code='categories_list' OR block_code='product_categories' "; 
		$product_categories_block_id = get_db_value($sql);
		if ($product_categories_block_id) {
			$sql  = " UPDATE ".$table_prefix."cms_blocks "; 
			$sql .= " SET block_code='product_categories', ";
			$sql .= " php_script='block_product_categories.php', ";
			$sql .= " block_name='CATEGORIES_TITLE', ";
			$sql .= " css_class='bk-product-categories' ";
			$sql .= " WHERE block_id=".intval($product_categories_block_id);
			$sqls[] = $sql;

			$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE block_id=".intval($product_categories_block_id); 
			$sql .= " AND variable_name='parent_id' "; 
			$cms_parent_id = get_db_value($sql);
			if (!$cms_parent_id) {
				$sql  = " UPDATE ".$table_prefix."cms_blocks_properties "; 
				$sql .= " SET property_order=property_order+1 ";
				$sql .= " WHERE block_id=".intval($product_categories_block_id); 
				$sqls[] = $sql;

				$property_id++; 
				$sqls[] = "INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,after_control_html) VALUES ($property_id, $product_categories_block_id, 1, 'LIST_PARENT_CATEGORY_ID_MSG', 'TEXTBOX', NULL, NULL, 'parent_id', '', 0, NULL, NULL, '<i data-js=\"expand\" data-class=\"help-popup\" class=\"ico-help popup-link\"></i><div class=\"help-popup\"><div class=\"popup-body\">{LIST_PARENT_CATEGORY_ID_DESC}</div></div>')";
			}
		}

		$sql  = " SELECT block_id FROM ".$table_prefix."cms_blocks "; 
		$sql .= " WHERE block_code='sliders' OR  block_code='slider' "; 
		$slider_block_id = get_db_value($sql);
		if ($slider_block_id) {

			$sql = " SELECT MAX(property_order) FROM ".$table_prefix."cms_blocks_properties WHERE block_id=" . $db->tosql($slider_block_id, INTEGER); 
			$property_order = get_db_value($sql);

			$sql  = " UPDATE ".$table_prefix."cms_blocks "; 
			$sql .= " SET block_code='slider', ";
			$sql .= " php_script='block_slider.php', ";
			$sql .= " css_class='bk-slider' ";
			$sql .= " WHERE block_id=".intval($slider_block_id);
			$sqls[] = $sql;

			$value_carousel = 0;
			$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE block_id=".intval($slider_block_id); 
			$sql .= " AND variable_name='slider_type' "; 
			$property_slider_type = get_db_value($sql);
			if ($property_slider_type) {
				$sql  = " SELECT value_id FROM ".$table_prefix."cms_blocks_values "; 
				$sql .= " WHERE property_id=".intval($property_slider_type); 
				$sql .= " AND variable_value='6' "; 
				$value_carousel = get_db_value($sql);
				if (!$value_carousel) {
					$value_id++;
					$value_carousel = $value_id;
					$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $property_slider_type, 6, 'CAROUSEL_MSG', NULL, '6', 0, 0)";
				}
			}

			$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE block_id=".intval($slider_block_id); 
			$sql .= " AND variable_name='scale_off' "; 
			$property_scale_off = get_db_value($sql);
			if (!$property_scale_off && $value_carousel) {
				$property_id++; $property_order++;
				$sqls[] = "INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,after_control_html) VALUES ($property_id, $slider_block_id, $property_order, 'SLIDE_SCALE_OFF_MSG', 'TEXTBOX', $property_slider_type, $value_carousel, 'scale_off', '', 0, NULL, NULL, '<i data-js=\"expand\" data-class=\"help-popup\" class=\"ico-help popup-link\"></i><div class=\"help-popup\"><div class=\"popup-body\">{SLIDE_SCALE_OFF_DESC}</div></div>')";
			}
			$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE block_id=".intval($slider_block_id); 
			$sql .= " AND variable_name='slide_indent' "; 
			$property_slide_indent = get_db_value($sql);
			if (!$property_slide_indent && $value_carousel) {
				$property_id++; $property_order++;
				$sqls[] = "INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,after_control_html) VALUES ($property_id, $slider_block_id, $property_order, 'SLIDE_INDENT_MSG', 'TEXTBOX', $property_slider_type, $value_carousel, 'slide_indent', '', 0, NULL, NULL, '<i data-js=\"expand\" data-class=\"help-popup\" class=\"ico-help popup-link\"></i><div class=\"help-popup\"><div class=\"popup-body\">{SLIDE_INDENT_DESC}</div></div>')";
			}
			$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE block_id=".intval($slider_block_id); 
			$sql .= " AND variable_name='visible_slides' "; 
			$property_visible_slides = get_db_value($sql);
			if (!$property_visible_slides && $value_carousel) {
				$property_id++; $property_order++;
				$sqls[] = "INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,after_control_html) VALUES ($property_id, $slider_block_id, $property_order, 'VISIBLE_SLIDES_MSG', 'TEXTBOX', $property_slider_type, $value_carousel, 'visible_slides', '', 0, NULL, NULL, '<i data-js=\"expand\" data-class=\"help-popup\" class=\"ico-help popup-link\"></i><div class=\"help-popup\"><div class=\"popup-body\">{VISIBLE_SLIDES_DESC}</div></div>')";
			}
		}

		$sql  = " SELECT block_id FROM ".$table_prefix."cms_blocks "; 
		$sql .= " WHERE block_code='category_description' "; 
		$product_category_block_id = get_db_value($sql);
		if ($product_category_block_id) {
			$sql  = " UPDATE ".$table_prefix."cms_blocks "; 
			$sql .= " SET block_code='product_category', ";
			$sql .= " php_script='block_product_category.php', ";
			$sql .= " css_class='bk-product-category' ";
			$sql .= " WHERE block_id=".intval($product_category_block_id);
			$sqls[] = $sql;
		}

		// use only one tree-type option with value 4 and update all values 6 to 4
		$sql  = " SELECT block_id FROM ".$table_prefix."cms_blocks "; 
		$sql .= " WHERE block_code='categories_list' OR block_code='product_categories' "; 
		$product_categories_block_id = get_db_value($sql);
		if ($product_categories_block_id) {
			$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE block_id=".intval($product_categories_block_id); 
			$sql .= " AND variable_name='categories_type' "; 
			$categories_type_property_id = get_db_value($sql);
			if ($categories_type_property_id) {
				$sql  = " SELECT value_id FROM ".$table_prefix."cms_blocks_values "; 
				$sql .= " WHERE property_id=".intval($categories_type_property_id); 
				$sql .= " AND variable_value='4' "; 
				$tree_type_value_id = get_db_value($sql);

				$sql  = " UPDATE ".$table_prefix."cms_blocks_settings"; 
				$sql .= " SET value_id=".intval($tree_type_value_id);
				$sql .= " , variable_value='4' ";
				$sql .= " WHERE property_id=".intval($categories_type_property_id); 
				$sql .= " AND variable_value='6' ";
				$sqls[] = $sql;

				$sql  = " DELETE FROM ".$table_prefix."cms_blocks_values "; 
				$sql .= " WHERE property_id=".intval($categories_type_property_id); 
				$sql .= " AND variable_value='6' "; 
				$sqls[] = $sql;
			}
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.8");
	}

