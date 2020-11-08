<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_upgrade_sqls_5.7.php                               ***
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


	// new support fields
	$support_mail_notices = false; 
	$fields = $db->get_fields($table_prefix."support");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "mail_notices") {
			$support_mail_notices = true;
		} 
	}
	// new support messages fields
	$support_messages_mail_notices = false; 
	$fields = $db->get_fields($table_prefix."support_messages");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "mail_notices") {
			$support_messages_mail_notices = true;
		} 
	}
	// new product fields
	$items_parent_item_id = false; $items_cart_item_name = false; 
	$fields = $db->get_fields($table_prefix."items");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "parent_item_id") {
			$items_parent_item_id = true;
		} else if ($field_info["name"] == "cart_item_name") {
			$items_cart_item_name = true;
		}
	}
	// new categories fields
	$categories_image_tiny = false; $categories_image_tiny_alt = false; 
	$categories_image_small = false; $categories_image_small_alt = false; 
	$fields = $db->get_fields($table_prefix."categories");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "image_tiny") {
			$categories_image_tiny = true;
		} else if ($field_info["name"] == "image_tiny_alt") {
			$categories_image_tiny_alt = true;
		} else if ($field_info["name"] == "image_small") {
			$categories_image_small = true;
		} else if ($field_info["name"] == "image_small_alt") {
			$categories_image_small_alt = true;
		}
	}

	if (comp_vers("5.6.1", $current_db_version) == 1)
	{
		// check for sites fields
		$sites_site_class = false; 
		$fields = $db->get_fields($table_prefix."sites");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "site_class") {
				$sites_site_class = true;
			} 
		}
		if (!$sites_site_class) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "sites ADD site_class VARCHAR(64) ";
		}
		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.6.1");
	}

	if (comp_vers("5.6.2", $current_db_version) == 1)
	{
		// check for orders properties fields
		$op_property_code = false; 
		$op_property_notes = false; 
		$fields = $db->get_fields($table_prefix."orders_properties");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "property_code") {
				$op_property_code = true;
			} else if ($field_info["name"] == "property_notes") {
				$op_property_notes = true;
			} 
		}
		if (!$op_property_code) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "orders_properties ADD property_code VARCHAR(32) AFTER property_type ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "orders_properties ADD property_code VARCHAR(32) ",
				"postgre" => "ALTER TABLE " . $table_prefix . "orders_properties ADD property_code VARCHAR(32) ",
				"access"  => "ALTER TABLE " . $table_prefix . "orders_properties ADD property_code VARCHAR(32) ",
			);
			$sqls[] = $sql_types[$db_type];
		}

		if (!$op_property_notes) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "orders_properties ADD property_notes TEXT AFTER property_value ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "orders_properties ADD property_notes TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "orders_properties ADD property_notes TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "orders_properties ADD property_notes LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.6.2");
	}


	if (comp_vers("5.6.3", $current_db_version) == 1)
	{
		if (!$support_mail_notices) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support ADD mail_notices TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support ADD mail_notices TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support ADD mail_notices TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support ADD mail_notices LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}


		if (!$support_messages_mail_notices) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_messages ADD mail_notices TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_messages ADD mail_notices TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_messages ADD mail_notices TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_messages ADD mail_notices LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.6.3");
	}


	if (comp_vers("5.6.4", $current_db_version) == 1)
	{
		if (!$items_parent_item_id) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "items ADD parent_item_id INT(11) ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "items ADD parent_item_id INTEGER ",
				"postgre" => "ALTER TABLE " . $table_prefix . "items ADD parent_item_id INT4 ",
				"access"  => "ALTER TABLE " . $table_prefix . "items ADD parent_item_id INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " CREATE INDEX ".$table_prefix."items_parent_item_id ON ".$table_prefix."items (parent_item_id)";
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.6.4");
	}


	if (comp_vers("5.6.5", $current_db_version) == 1)
	{
		if (!$items_cart_item_name) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "items ADD cart_item_name VARCHAR(255) AFTER item_name ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "items ADD cart_item_name VARCHAR(255) ",
				"postgre" => "ALTER TABLE " . $table_prefix . "items ADD cart_item_name VARCHAR(255) ",
				"access"  => "ALTER TABLE " . $table_prefix . "items ADD cart_item_name VARCHAR(255) ",
			);
			$sqls[] = $sql_types[$db_type];
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.6.5");
	}



	if (comp_vers("5.6.6", $current_db_version) == 1)
	{
		if (!$categories_image_tiny) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "categories ADD image_tiny VARCHAR(255) AFTER image_alt ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "categories ADD image_tiny VARCHAR(255) ",
				"postgre" => "ALTER TABLE " . $table_prefix . "categories ADD image_tiny VARCHAR(255) ",
				"access"  => "ALTER TABLE " . $table_prefix . "categories ADD image_tiny VARCHAR(255) ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE ".$table_prefix."categories SET image_tiny=image ";
		}
		if (!$categories_image_tiny_alt) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "categories ADD image_tiny_alt VARCHAR(255) AFTER image_tiny ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "categories ADD image_tiny_alt VARCHAR(255) ",
				"postgre" => "ALTER TABLE " . $table_prefix . "categories ADD image_tiny_alt VARCHAR(255) ",
				"access"  => "ALTER TABLE " . $table_prefix . "categories ADD image_tiny_alt VARCHAR(255) ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE ".$table_prefix."categories SET image_tiny_alt=image_alt ";
		}
		if (!$categories_image_small) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "categories ADD image_small VARCHAR(255) AFTER image_tiny_alt ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "categories ADD image_small VARCHAR(255) ",
				"postgre" => "ALTER TABLE " . $table_prefix . "categories ADD image_small VARCHAR(255) ",
				"access"  => "ALTER TABLE " . $table_prefix . "categories ADD image_small VARCHAR(255) ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE ".$table_prefix."categories SET image_small=image ";
		}
		if (!$categories_image_small_alt) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "categories ADD image_small_alt VARCHAR(255) AFTER image_small ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "categories ADD image_small_alt VARCHAR(255) ",
				"postgre" => "ALTER TABLE " . $table_prefix . "categories ADD image_small_alt VARCHAR(255) ",
				"access"  => "ALTER TABLE " . $table_prefix . "categories ADD image_small_alt VARCHAR(255) ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE ".$table_prefix."categories SET image_small_alt=image_alt ";
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.6.6");
	}



	if (comp_vers("5.6.7", $current_db_version) == 1)
	{
		// add new tiny image option for categories and update '1' - default image
		$sql  = " SELECT block_id FROM ".$table_prefix."cms_blocks "; 
		$sql .= " WHERE block_code='categories_list' "; 
		$categories_list_block_id = get_db_value($sql);
		if ($categories_list_block_id) {
			$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE variable_name='categories_image' "; 
			$sql .= " AND block_id=" . $db->tosql($categories_list_block_id, INTEGER); 
			$categories_image_property_id = get_db_value($sql);
			if ($categories_image_property_id) {
				$sql  = " SELECT value_id, value_name FROM ".$table_prefix."cms_blocks_values "; 
				$sql .= " WHERE variable_value='1'"; 
				$sql .= " AND property_id=" . $db->tosql($categories_image_property_id, INTEGER); 
				$db->query($sql);
				if ($db->next_record()) {
					$tiny_value_id = $db->f("value_id");
					$value_name = $db->f("value_name");
					if (preg_match("/DEFAULT/i", $value_name)) {
						// delete default settings
						$sql  = " DELETE FROM ".$table_prefix."cms_blocks_settings ";
						$sql .= " WHERE property_id=" . $db->tosql($categories_image_property_id, INTEGER); 
						$sql .= " AND variable_value='1'"; 
						$sqls[] = $sql;	
					}
					$sql  = " UPDATE ".$table_prefix."cms_blocks_values ";
					$sql .= " SET value_name='IMAGE_TINY_MSG' ";
					$sql .= " WHERE value_id=" . $db->tosql($tiny_value_id, INTEGER); 
					$sqls[] = $sql;	
				} else {
					$value_id++;
					$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
					$sql .= intval($value_id).",".intval($categories_image_property_id).", 1, 'IMAGE_TINY_MSG', '', '1') ";
					$sqls[] = $sql;	
				}

				// check if we need to add 'Don't show image' option value
				$sql  = " SELECT value_id, value_name FROM ".$table_prefix."cms_blocks_values "; 
				$sql .= " WHERE variable_value='0'"; 
				$sql .= " AND property_id=" . $db->tosql($categories_image_property_id, INTEGER); 
				$db->query($sql);
				if (!$db->next_record()) {
					$value_id++;
					$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
					$sql .= intval($value_id).",".intval($categories_image_property_id).", 0, 'DONT_SHOW_IMAGE_MSG', '', '0') ";
					$sqls[] = $sql;	
				}
			}
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.7");
	}


