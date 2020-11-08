<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_upgrade_sqls_5.3.php                               ***
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

	// check for new table creation
  $tables = $db->get_tables();
	$support_pipes = false; 
	foreach ($tables as $table_id => $table_name) {
		if ($table_name == $table_prefix."support_pipes") {
			$support_pipes = true;
		}
	}

	// new support statuses fields
	$support_statuses_type = false; 
	$support_statuses_order = false; 
	$support_statuses_default = false;
	$support_statuses_update_status = false;
	$support_statuses_admin_mail = false;
	$support_statuses_manager_mail = false;
	$support_statuses_assign_to_mail = false;
	$support_statuses_user_mail = false;
	$support_statuses_drop = array(	
		"admin_notify" => false,
		"admin_to" => false,
		"admin_from" => false,
		"admin_cc" => false,
		"admin_reply_to" => false,
		"admin_return_path" => false,
		"admin_mail_type" => false,
		"admin_subject" => false,
		"admin_body" => false,

		"manager_by_notify" => false,
		"manager_by_to" => false,
		"manager_by_from" => false,
		"manager_by_cc" => false,
		"manager_by_reply_to" => false,
		"manager_by_return_path" => false,
		"manager_by_mail_type" => false,
		"manager_by_subject" => false,
		"manager_by_body" => false,

		"manager_to_notify" => false,
		"manager_to_to" => false,
		"manager_to_from" => false,
		"manager_to_cc" => false,
		"manager_to_reply_to" => false,
		"manager_to_return_path" => false,
		"manager_to_mail_type" => false,
		"manager_to_subject" => false,
		"manager_to_body" => false,

		"user_notify" => false,
		"user_to" => false,
		"user_from" => false,
		"user_cc" => false,
		"user_reply_to" => false,
		"user_return_path" => false,
		"user_mail_type" => false,
		"user_subject" => false,
		"user_body" => false,
	);

	$fields = $db->get_fields($table_prefix."support_statuses");
	foreach ($fields as $id => $field_info) {
		$field_name = $field_info["name"];
		if ($field_info["name"] == "status_type") {
			$support_statuses_type = true;
		} else if ($field_info["name"] == "status_order") {
			$support_statuses_order = true;
		} else if ($field_info["name"] == "is_default") {
			$support_statuses_default = true;
		} else if ($field_info["name"] == "is_update_status") {
			$support_statuses_update_status = true;
		} else if ($field_info["name"] == "admin_mail") {
			$support_statuses_admin_mail = true;
		} else if ($field_info["name"] == "manager_mail") {
			$support_statuses_manager_mail = true;
		} else if ($field_info["name"] == "assign_to_mail") {
			$support_statuses_assign_to_mail = true;
		} else if ($field_info["name"] == "user_mail") {
			$support_statuses_user_mail = true;
		} else if (isset($support_statuses_drop[$field_name])) {
			$support_statuses_drop[$field_name] = true;
		}
	}


	// new support messages fields
	$support_messages_forward = false; 
	$fields = $db->get_fields($table_prefix."support_messages");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "forward_mail") {
			$support_messages_forward = true;
		} 
	}

	if (comp_vers("5.2.1", $current_db_version) == 1)
	{
		// new CMS frames fields
		$cms_frames_blocks = false; 
		$fields = $db->get_fields($table_prefix."cms_frames");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "blocks_allowed") {
				$cms_frames_blocks = true;
			} 
		}
		if (!$cms_frames_blocks) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "cms_frames ADD blocks_allowed TINYINT DEFAULT '1'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "cms_frames ADD blocks_allowed TINYINT DEFAULT '1'",
				"postgre" => "ALTER TABLE " . $table_prefix . "cms_frames ADD blocks_allowed SMALLINT DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "cms_frames ADD blocks_allowed BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "cms_frames SET blocks_allowed=1 ";
		}
  
		$cms_frames_settings_tag = false; 
		$fields = $db->get_fields($table_prefix."cms_frames_settings");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "tag_name") {
				$cms_frames_settings_tag = true;
			} 
		}
		if (!$cms_frames_settings_tag) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "cms_frames_settings ADD tag_name VARCHAR(128) ";
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.2.1");
	}


	if (comp_vers("5.2.2", $current_db_version) == 1)
	{
		// check menu for user voucher settings, product reviews settings, product questions settings (5.0.12 update which could be missed in 5.2)
		if ($admin_menu_id) {
			$sql  = " SELECT menu_item_id ";
			$sql .= " FROM ".$table_prefix."menus_items ";
			$sql .= " WHERE menu_id=".$db->tosql($admin_menu_id, INTEGER);
			$sql .= " AND menu_code='products-settings' ";
		  $products_settings_id = get_db_value($sql);
    
			$sql = " SELECT MAX(menu_order) FROM ".$table_prefix."menus_items WHERE parent_menu_item_id=".$db->tosql($products_settings_id, INTEGER); 
			$menu_order = get_db_value($sql);
    
			if ($products_settings_id) {
				$sql  = " SELECT menu_item_id ";
				$sql .= " FROM ".$table_prefix."menus_items ";
				$sql .= " WHERE menu_id=".$db->tosql($admin_menu_id, INTEGER);
				$sql .= " AND menu_code='user-voucher-settings' ";
			  $settings_menu_id = get_db_value($sql);
				if (!$settings_menu_id) {
					$menu_order++;
					$sql  = "INSERT INTO " . $table_prefix . "menus_items (menu_id,parent_menu_item_id,menu_order,menu_title,menu_url,menu_code,admin_access) VALUES (";
					$sql .= intval($admin_menu_id).",".intval($products_settings_id).", ".intval($menu_order).", 'USER_VOUCHER_SETTINGS_MSG', 'settings_user_voucher.php', 'user-voucher-settings', 'products_settings') ";
					$sqls[] = $sql;	
				}
    
				$sql  = " SELECT menu_item_id ";
				$sql .= " FROM ".$table_prefix."menus_items ";
				$sql .= " WHERE menu_url='admin_products_reviews_sets.php' ";
			  $settings_menu_id = get_db_value($sql);
				if ($settings_menu_id) {
					$sql  = " UPDATE " . $table_prefix . "menus_items SET menu_url='settings_product_reviews.php' ";
					$sql .= " WHERE menu_item_id=" . $db->tosql($settings_menu_id, INTEGER);
					$sqls[] = $sql;	
				}
    
				$sql  = " SELECT menu_item_id ";
				$sql .= " FROM ".$table_prefix."menus_items ";
				$sql .= " WHERE menu_id=".$db->tosql($admin_menu_id, INTEGER);
				$sql .= " AND menu_code='settings-product-questions' ";
			  $settings_menu_id = get_db_value($sql);
				if (!$settings_menu_id) {
					$menu_order++;
					$sql  = "INSERT INTO " . $table_prefix . "menus_items (menu_id,parent_menu_item_id,menu_order,menu_title,menu_url,menu_code,admin_access) VALUES (";
					$sql .= intval($admin_menu_id).",".intval($products_settings_id).", ".intval($menu_order).", 'QUESTIONS_SETTINGS_MSG', 'settings_product_questions.php', 'settings-product-questions', 'products_reviews_settings') ";
					$sqls[] = $sql;	
				}
			}
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.2.2");
	}


	if (comp_vers("5.2.3", $current_db_version) == 1)
	{
		// add new custom fields menu to User Settings menu section 5.1.3
		$sql  = " SELECT menu_item_id, menu_id FROM ".$table_prefix."menus_items " ;
		$sql .= " WHERE menu_code='user-profile-properties' ";
		$db->query($sql);
		if (!$db->next_record()) {
			$sql  = " SELECT menu_item_id, menu_id FROM ".$table_prefix."menus_items " ;
			$sql .= " WHERE menu_code='users-settings' ";
			$db->query($sql);
			if ($db->next_record()) {
				$menu_id= $db->f("menu_id");
				$menu_item_id= $db->f("menu_item_id");
  
				$sql  = " UPDATE ".$table_prefix."menus_items SET menu_order=menu_order+1 ";
				$sql .= " WHERE parent_menu_item_id=".$db->tosql($menu_item_id, INTEGER);	
				$sql .= " AND menu_order>1 ";
				$sqls[] = $sql;

				$sql  = " INSERT INTO ".$table_prefix."menus_items (menu_id, parent_menu_item_id, menu_order, menu_code, menu_title, menu_url, admin_access) " ;
				$sql .= " VALUES (";
				$sql .= $db->tosql($menu_id, INTEGER) . ", ";
				$sql .= $db->tosql($menu_item_id, INTEGER) . ", ";
				$sql .= $db->tosql(2, INTEGER) . ", ";
				$sql .= $db->tosql('user-profile-properties', TEXT) . ", ";
				$sql .= $db->tosql('CUSTOM_FIELDS_MSG', TEXT) . ", ";
				$sql .= $db->tosql('admin_user_properties.php', TEXT) . ", ";
				$sql .= $db->tosql('site_users', TEXT) . ") ";
				$sqls[] = $sql;
			}
		}


		// add new custom fields menu to Helpdesk Settings menu section
		$sql  = " SELECT menu_item_id, menu_id FROM ".$table_prefix."menus_items " ;
		$sql .= " WHERE menu_code='helpdesk-custom-properties' ";
		$db->query($sql);
		if (!$db->next_record()) {
			$sql  = " SELECT menu_item_id, menu_id FROM ".$table_prefix."menus_items " ;
			$sql .= " WHERE menu_code='helpdesk-settings' ";
			$db->query($sql);
			if ($db->next_record()) {
				$menu_id= $db->f("menu_id");
				$menu_item_id= $db->f("menu_item_id");
  
				$sql  = " UPDATE ".$table_prefix."menus_items SET menu_order=menu_order+1 ";
				$sql .= " WHERE parent_menu_item_id=".$db->tosql($menu_item_id, INTEGER);	
				$sql .= " AND menu_order>1 ";
				$sqls[] = $sql;

				$sql  = " INSERT INTO ".$table_prefix."menus_items (menu_id, parent_menu_item_id, menu_order, menu_code, menu_title, menu_url, admin_access) " ;
				$sql .= " VALUES (";
				$sql .= $db->tosql($menu_id, INTEGER) . ", ";
				$sql .= $db->tosql($menu_item_id, INTEGER) . ", ";
				$sql .= $db->tosql(2, INTEGER) . ", ";
				$sql .= $db->tosql('helpdesk-custom-properties', TEXT) . ", ";
				$sql .= $db->tosql('CUSTOM_FIELDS_MSG', TEXT) . ", ";
				$sql .= $db->tosql('admin_support_properties.php', TEXT) . ", ";
				$sql .= $db->tosql('support_settings', TEXT) . ") ";
				$sqls[] = $sql;
			}
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.2.3");
	}

	if (comp_vers("5.2.4", $current_db_version) == 1)
	{
		// add new email pipes table 
		if (!$support_pipes) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE va_support_pipes (
          `pipe_id` INT(11) NOT NULL AUTO_INCREMENT,
          `incoming_email` VARCHAR(128),
          `outgoing_email` VARCHAR(128),
          `dep_id` INT(11) DEFAULT '0',
          `support_type_id` INT(11) DEFAULT '0',
          `support_product_id` INT(11) DEFAULT '0'
          ,KEY dep_id (dep_id)
          ,KEY incoming_email (incoming_email)
          ,PRIMARY KEY (pipe_id)
					) DEFAULT CHARACTER SET=utf8mb4";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE va_support_pipes (
          pipe_id INTEGER NOT NULL IDENTITY,
          incoming_email VARCHAR(128),
          outgoing_email VARCHAR(128),
          dep_id INTEGER,
          support_type_id INTEGER,
          support_product_id INTEGER
          ,PRIMARY KEY (pipe_id))";
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE SEQUENCE seq_va_support_pipes START 1";
				$sqls[] = "CREATE TABLE va_support_pipes (
          pipe_id INT4 NOT NULL DEFAULT nextval('seq_va_support_pipes'),
          incoming_email VARCHAR(128),
          outgoing_email VARCHAR(128),
          dep_id INT4 default '0',
          support_type_id INT4 default '0',
          support_product_id INT4 default '0'
          ,PRIMARY KEY (pipe_id))";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE va_support_pipes (
          [pipe_id]  COUNTER  NOT NULL,
          [incoming_email] VARCHAR(128),
          [outgoing_email] VARCHAR(128),
          [dep_id] INTEGER,
          [support_type_id] INTEGER,
          [support_product_id] INTEGER
          ,PRIMARY KEY (pipe_id))";
			} 

			if ($db_type != "mysql") {
				$sqls[] = "CREATE INDEX va_support_pipes_dep_id ON va_support_pipes (dep_id)";
				$sqls[] = "CREATE INDEX va_support_pipes_incoming_email ON va_support_pipes (incoming_email)";
			}
		}
		// end new email pipes table creation


		// add new separate email pipes settings instead of department settings
		$sql  = " SELECT menu_item_id, menu_id FROM ".$table_prefix."menus_items " ;
		$sql .= " WHERE menu_code='helpdesk-pipes' ";
		$db->query($sql);
		if (!$db->next_record()) {
			// add new menu item for email pipes
			$sql  = " SELECT menu_item_id, menu_id FROM ".$table_prefix."menus_items " ;
			$sql .= " WHERE menu_code='helpdesk-settings' ";
			$db->query($sql);
			if ($db->next_record()) {
				$menu_id= $db->f("menu_id");
				$menu_item_id= $db->f("menu_item_id");
  
				// check menu_order
				$sql = " SELECT MAX(menu_order) FROM ".$table_prefix."menus_items WHERE parent_menu_item_id=".$db->tosql($menu_item_id, INTEGER); 
				$menu_order = get_db_value($sql);
				$menu_order++;

				$sql  = " INSERT INTO ".$table_prefix."menus_items (menu_id, parent_menu_item_id, menu_order, menu_code, menu_title, menu_url, admin_access) " ;
				$sql .= " VALUES (";
				$sql .= $db->tosql($menu_id, INTEGER) . ", ";
				$sql .= $db->tosql($menu_item_id, INTEGER) . ", ";
				$sql .= $db->tosql($menu_order, INTEGER) . ", ";
				$sql .= $db->tosql('helpdesk-pipes', TEXT) . ", ";
				$sql .= $db->tosql('EMAIL_PIPES_MSG', TEXT) . ", ";
				$sql .= $db->tosql('admin_support_pipes.php', TEXT) . ", ";
				$sql .= $db->tosql('support_settings', TEXT) . ") ";
				$sqls[] = $sql;
			}
			// add a new separate email pipes if they exists
			$sql  = " SELECT * FROM ".$table_prefix."support_departments ";
			$db->query($sql);
			while ($db->next_record()) {
				$dep_id = $db->f("dep_id");
				$support_type_id = $db->f("incoming_type_id");
				$support_product_id = $db->f("incoming_product_id");
				$incoming_account = $db->f("incoming_account");
				$outgoing_email = $db->f("outgoing_account");

				if ($incoming_account) {
					$incoming_emails = explode(",", $incoming_account);
					foreach ($incoming_emails as $incoming_email) {
						$incoming_email = trim($incoming_email);
						if (preg_match(EMAIL_REGEXP, $incoming_email)) {
							$sql  = " INSERT INTO ".$table_prefix."support_pipes (incoming_email, outgoing_email, dep_id, support_type_id, support_product_id) " ;
							$sql .= " VALUES (";
							$sql .= $db->tosql($incoming_email, TEXT) . ", ";
							$sql .= $db->tosql($outgoing_email, TEXT) . ", ";
							$sql .= $db->tosql($dep_id, INTEGER) . ", ";
							$sql .= $db->tosql($support_type_id, INTEGER) . ", ";
							$sql .= $db->tosql($support_product_id, INTEGER) . ") ";
							$sqls[] = $sql;
						}
					}
				}
			}
		}
		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.2.4");
	}

	// add new fields to support departments table 
	if (comp_vers("5.2.5", $current_db_version) == 1)
	{
		$sd_admins_all = false; 
		$sd_new_admin_mail = false; 
		$sd_new_user_mail = false; 
		$sd_user_reply_admin_mail = false; 
		$sd_user_reply_user_mail = false; 
		$sd_manager_reply_admin_mail = false; 
		$sd_manager_reply_manager_mail = false; 
		$sd_manager_reply_user_mail = false; 
		$sd_assign_admin_mail = false; 
		$sd_assign_manager_mail = false; 
		$sd_assign_to_mail = false; 
		$sd_assign_user_mail = false; 
		$fields = $db->get_fields($table_prefix."support_departments");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "admins_all") {
				$sd_admins_all = true;
			} else if ($field_info["name"] == "new_admin_mail") {
				$sd_new_admin_mail = true;
			} else if ($field_info["name"] == "new_user_mail") {
				$sd_new_user_mail = true;
			} else if ($field_info["name"] == "user_reply_admin_mail") {
				$sd_user_reply_admin_mail = true; 
			} else if ($field_info["name"] == "user_reply_user_mail") {
				$sd_user_reply_user_mail = true; 
			} else if ($field_info["name"] == "manager_reply_admin_mail") {
				$sd_manager_reply_admin_mail = true; 
			} else if ($field_info["name"] == "manager_reply_manager_mail") {
				$sd_manager_reply_manager_mail = true; 
			} else if ($field_info["name"] == "manager_reply_user_mail") {
				$sd_manager_reply_user_mail = true; 
			} else if ($field_info["name"] == "assign_admin_mail") {
				$sd_assign_admin_mail = true; 
			} else if ($field_info["name"] == "assign_manager_mail") {
				$sd_assign_manager_mail = true; 
			} else if ($field_info["name"] == "assign_to_mail") {
				$sd_assign_to_mail = true; 
			} else if ($field_info["name"] == "assign_user_mail") {
				$sd_assign_user_mail = true; 
			} 
		}

		if (!$sd_admins_all) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD admins_all TINYINT DEFAULT '1'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD admins_all TINYINT DEFAULT '1'",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD admins_all SMALLINT DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD admins_all BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_new_admin_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD new_admin_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD new_admin_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD new_admin_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD new_admin_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_new_user_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD new_user_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD new_user_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD new_user_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD new_user_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_user_reply_admin_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD user_reply_admin_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD user_reply_admin_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD user_reply_admin_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD user_reply_admin_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_user_reply_user_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD user_reply_user_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD user_reply_user_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD user_reply_user_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD user_reply_user_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_manager_reply_admin_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD manager_reply_admin_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD manager_reply_admin_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD manager_reply_admin_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD manager_reply_admin_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_manager_reply_manager_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD manager_reply_manager_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD manager_reply_manager_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD manager_reply_manager_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD manager_reply_manager_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_manager_reply_user_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD manager_reply_user_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD manager_reply_user_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD manager_reply_user_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD manager_reply_user_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_assign_admin_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD assign_admin_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD assign_admin_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD assign_admin_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD assign_admin_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_assign_manager_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD assign_manager_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD assign_manager_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD assign_manager_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD assign_manager_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_assign_to_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD assign_to_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD assign_to_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD assign_to_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD assign_to_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$sd_assign_user_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD assign_user_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD assign_user_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD assign_user_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD assign_user_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.2.5");
	}

	// add missed fields to menu_items table
	if (comp_vers("5.2.6", $current_db_version) == 1)
	{
		// check menu items field in different tables
		$menu_type_field = false; $menu_html_field = false;
		$fields = $db->get_fields($table_prefix."menus_items");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "menu_type") {
				$menu_type_field = true;
			} else if ($field_info["name"] == "menu_html") {
				$menu_html_field = true;
			}
		}
		if (!$menu_type_field) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "menus_items ADD menu_type VARCHAR(16) ";
		}
		if (!$menu_html_field) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "menus_items ADD menu_html TEXT",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "menus_items ADD menu_html TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "menus_items ADD menu_html TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "menus_items ADD menu_html LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.2.6");
	}

	// move old notifications to the new settings 
	if (comp_vers("5.2.7", $current_db_version) == 1)
	{
		$support_settings = array();
		$sql  = " SELECT * FROM ".$table_prefix."global_settings "; 
		$sql .= " WHERE setting_type='support' "; 
		$db->query($sql);
		while($db->next_record()) {
			$setting_site_id = $db->f("site_id");
			$setting_name = $db->f("setting_name");
			$setting_value = $db->f("setting_value");
			$support_settings[$setting_name] = array(
				"site_id" => $setting_site_id,
				"value" => $setting_value,
			);
		}

		if (!isset($support_settings["user_reply_admin_notification"])) {
			$user_reply_fields = array(
				"admin_notification" => "user_reply_admin_notification", 
				"admin_email" => "user_reply_admin_to", 
				"admin_mail_from" => "user_reply_admin_from", 
				"cc_emails" => "user_reply_admin_cc", 
				"admin_mail_bcc" => "user_reply_admin_bcc", 
				"admin_mail_reply_to" => "user_reply_admin_reply_to", 
				"admin_mail_return_path" => "user_reply_admin_return_path", 
				"admin_subject" => "user_reply_admin_subject", 
				"admin_message_type" => "user_reply_admin_message_type", 
				"admin_message" => "user_reply_admin_message", 
			);
			foreach ($user_reply_fields as $old_name => $new_name) {
				if (isset($support_settings[$old_name])) {
					$sql  = " INSERT INTO ".$table_prefix."global_settings (site_id, setting_type, setting_name, setting_value) VALUES (";
					$sql .= $db->tosql($support_settings[$old_name]["site_id"], INTEGER) .", ";
					$sql .= "'support', ";
					$sql .= $db->tosql($new_name, TEXT) .", ";
					$sql .= $db->tosql($support_settings[$old_name]["value"], TEXT) .") ";
					$sqls[] = $sql;
				}
			}
  
			$new_ticket_fields = array(
				"admin_notification" => "new_admin_notification", 
				"admin_email" => "new_admin_to", 
				"admin_mail_from" => "new_admin_from", 
				"cc_emails" => "new_admin_cc", 
				"admin_mail_bcc" => "new_admin_bcc", 
				"admin_mail_reply_to" => "new_admin_reply_to", 
				"admin_mail_return_path" => "new_admin_return_path", 
				"admin_subject" => "new_admin_subject", 
				"admin_message_type" => "new_admin_message_type", 
				"admin_message" => "new_admin_message", 
			);
			foreach ($new_ticket_fields as $old_name => $new_name) {
				if (isset($support_settings[$old_name])) {
					$sql  = " INSERT INTO ".$table_prefix."global_settings (site_id, setting_type, setting_name, setting_value) VALUES (";
					$sql .= $db->tosql($support_settings[$old_name]["site_id"], INTEGER) .", ";
					$sql .= "'support', ";
					$sql .= $db->tosql($new_name, TEXT) .", ";
					$sql .= $db->tosql($support_settings[$old_name]["value"], TEXT) .") ";
					$sqls[] = $sql;
				}
			}
	  
			$manager_reply_fields = array(
				"manager_reply_user_notification", 
				"user_mail_from" => "manager_reply_user_from", 
				"user_mail_cc" => "manager_reply_user_cc", 
				"user_mail_bcc" => "manager_reply_user_bcc", 
				"user_mail_reply_to" => "manager_reply_user_reply_to", 
				"user_mail_return_path" => "manager_reply_user_return_path", 
				"user_subject" => "manager_reply_user_subject", 
				"user_message_type" => "manager_reply_user_message_type", 
				"user_message" => "manager_reply_user_message", 
			);
			$sql  = " INSERT INTO ".$table_prefix."global_settings (site_id, setting_type, setting_name, setting_value) ";
			$sql .= " VALUES (1, 'support', 'manager_reply_user_notification', '1') ";
			$sqls[] = $sql;
			foreach ($manager_reply_fields as $old_name => $new_name) {
				if (isset($support_settings[$old_name])) {
					$sql  = " INSERT INTO ".$table_prefix."global_settings (site_id, setting_type, setting_name, setting_value) VALUES (";
					$sql .= $db->tosql($support_settings[$old_name]["site_id"], INTEGER) .", ";
					$sql .= "'support', ";
					$sql .= $db->tosql($new_name, TEXT) .", ";
					$sql .= $db->tosql($support_settings[$old_name]["value"], TEXT) .") ";
					$sqls[] = $sql;
				}
			}
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.2.7");
	}


	if (comp_vers("5.3", $current_db_version) == 1)
	{
		// check for new profile fields
		$user_settings = false;
		$fields = $db->get_fields($table_prefix."users");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "user_settings") {
				$user_settings = true;
			}
		}
		if (!$user_settings) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "users ADD user_settings TEXT",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "users ADD user_settings TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "users ADD user_settings TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "users ADD user_settings LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.3");
	}
