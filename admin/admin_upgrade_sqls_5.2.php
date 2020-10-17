<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_upgrade_sqls_5.2.php                               ***
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
	$user_profile_properties_types = false; $user_profile_properties_sites = false; 
	$support_custom_sites = false; $support_custom_types = false; $support_custom_departments = false;
	foreach ($tables as $table_id => $table_name) {
		if ($table_name == $table_prefix."user_profile_properties_types") {
			$user_profile_properties_types = true;
		} else if ($table_name == $table_prefix."user_profile_properties_sites") {
			$user_profile_properties_sites = true;
		} else if ($table_name == $table_prefix."support_custom_sites") {
			$support_custom_sites= true;
		} else if ($table_name == $table_prefix."support_custom_types") {
			$support_custom_types = true;
		} else if ($table_name == $table_prefix."support_custom_departments") {
			$support_custom_departments = true;
		}
	}

	if (comp_vers("5.1.1", $current_db_version) == 1)
	{
		// check for button options for products_latest, products_top_sellers, products_recently_viewed blocks
		$buttons_blocks = array();
		$sql  = " SELECT block_id,block_code FROM ".$table_prefix."cms_blocks "; 
		$sql .= " WHERE block_code='products_recommended' "; 
		$sql .= " OR block_code='products_top_rated' "; 
		$sql .= " OR block_code='products_top_viewed' "; 
		$sql .= " OR block_code='products_related_purchase' "; 
  
		$db->query($sql);
		while ($db->next_record()) {
			$block_id = $db->f("block_id");
			$block_code = $db->f("block_code");
			$buttons_blocks[$block_code] = $block_id;
		}
    
		// save some ids for further checks
		$top_rated_block_id = isset($buttons_blocks["products_top_rated"]) ? $buttons_blocks["products_top_rated"] : "";
		$top_viewed_block_id = isset($buttons_blocks["products_top_viewed"]) ? $buttons_blocks["products_top_viewed"] : "";
  
		// check if button was added
		foreach ($buttons_blocks as $block_code => $block_id) {
			$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE variable_name='bn_add' "; 
			$sql .= " AND block_id=" . $db->tosql($block_id, INTEGER); 
			$db->query($sql);
			if ($db->next_record()) {
				unset($buttons_blocks[$block_code]);
			}
			// check number of columns for top sellers block
			if ($block_code == "products_top_rated") {
				$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties "; 
				$sql .= " WHERE variable_name='cols' "; 
				$sql .= " AND block_id=" . $db->tosql($block_id, INTEGER); 
				$db->query($sql);
				if (!$db->next_record()) {
					$property_id++; 
					$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, 1, 'NUMBER_OF_COLUMNS_MSG', 'TEXTBOX', NULL, NULL, 'cols', '1', 0)";
				}
			}
		}

		// add buttons where is there is no any properties yet
		foreach ($buttons_blocks as $block_code => $block_id) {
			$sql  = " DELETE FROM ".$table_prefix."cms_blocks_properties ";
			$sql .= " WHERE variable_name IN ('bn_add','bn_view','bn_goto','bn_wish','bn_more') "; 
			$sql .= " AND block_id=" . $db->tosql($block_id, INTEGER); 
			
			// get max property_order for latest block
			$sql  = " SELECT MAX(property_order) FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE block_id=" . $db->tosql($block_id, INTEGER); 
			$property_order = get_db_value($sql);
  
			// add 'add to cart' button option
			$property_id++; $property_order++;
			$add_property_id = $property_id;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($add_property_id, $block_id, $property_order, 'ADD_TO_CART_MSG', 'RADIOBUTTON', NULL, NULL, 'bn_add', '1', 0)";

			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($add_property_id).", 1, 'YES_MSG', '', '1') ";
			$sqls[] = $sql;	
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($add_property_id).", 2, 'NO_MSG', '', '0') ";
			$sqls[] = $sql;	
  
			// add 'VIEW_CART_MSG' button option
			$property_id++; $property_order++;
			$view_property_id = $property_id;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'VIEW_CART_MSG', 'RADIOBUTTON', NULL, NULL, 'bn_view', '0', 0)";
  
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($view_property_id).", 1, 'YES_MSG', '', '1') ";
			$sqls[] = $sql;	
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($view_property_id).", 2, 'NO_MSG', '', '0') ";
			$sqls[] = $sql;	
  
			// add 'Checkout' button option
			$property_id++; $property_order++;
			$goto_property_id = $property_id;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'GOTO_CHECKOUT_MSG', 'RADIOBUTTON', NULL, NULL, 'bn_goto', '0', 0)";
  
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($goto_property_id).", 1, 'YES_MSG', '', '1') ";
			$sqls[] = $sql;	
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($goto_property_id).", 2, 'NO_MSG', '', '0') ";
			$sqls[] = $sql;	
  
			// add 'Wishlist' button option
			// check if property with different name exists
			$property_id++; $property_order++;
			$wish_property_id = $property_id;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'WISHLIST_MSG', 'RADIOBUTTON', NULL, NULL, 'bn_wish', '0', 0)";

			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($wish_property_id).", 1, 'YES_MSG', '', '1') ";
			$sqls[] = $sql;	
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($wish_property_id).", 2, 'NO_MSG', '', '0') ";
			$sqls[] = $sql;	
  
			// add 'Read more' button option
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'READ_MORE_MSG', 'RADIOBUTTON', NULL, NULL, 'bn_more', '0', 0)";
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($property_id).", 1, 'YES_MSG', '', '1') ";
			$sqls[] = $sql;	
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($property_id).", 2, 'NO_MSG', '', '0') ";
			$sqls[] = $sql;	
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.1.1");
	}


	if (comp_vers("5.1.2", $current_db_version) == 1)
	{
		// add regular expression fields for countries
		$countries_delivery_for_user = false; 
		$countries_show_for_admin = false; 
		$countries_delivery_for_admin = false; 
		$fields = $db->get_fields($table_prefix."countries");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "delivery_for_user") {
				$countries_delivery_for_user = true;
			} else if ($field_info["name"] == "show_for_admin") {
				$countries_show_for_admin = true;
			} else if ($field_info["name"] == "delivery_for_admin") {
				$countries_delivery_for_admin = true;
			}
		}

		if (!$countries_delivery_for_user) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "countries ADD delivery_for_user TINYINT DEFAULT '1'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "countries ADD delivery_for_user TINYINT DEFAULT '1'",
				"postgre" => "ALTER TABLE " . $table_prefix . "countries ADD delivery_for_user SMALLINT DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "countries ADD delivery_for_user BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
		}

		if (!$countries_show_for_admin) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "countries ADD show_for_admin TINYINT DEFAULT '1'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "countries ADD show_for_admin TINYINT DEFAULT '1'",
				"postgre" => "ALTER TABLE " . $table_prefix . "countries ADD show_for_admin SMALLINT DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "countries ADD show_for_admin BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
		}

		if (!$countries_delivery_for_user) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "countries ADD delivery_for_admin TINYINT DEFAULT '1'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "countries ADD delivery_for_admin TINYINT DEFAULT '1'",
				"postgre" => "ALTER TABLE " . $table_prefix . "countries ADD delivery_for_admin SMALLINT DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "countries ADD delivery_for_admin BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
		}
		$sqls[] = " UPDATE " . $table_prefix . "countries SET delivery_for_user=show_for_user, show_for_admin=show_for_user, delivery_for_admin=show_for_user ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.1.2");
	}

	if (comp_vers("5.1.3", $current_db_version) == 1)
	{
		// add new custom fields menu to User Settings menu section
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

		// add new option to use user profile properties for different user types and sites
		if (!$user_profile_properties_types) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE va_user_profile_properties_types (
          `property_id` INT(11) NOT NULL DEFAULT '0',
          `user_type_id` INT(11) NOT NULL DEFAULT '0'
          ,KEY property_id (property_id)
          ,KEY user_type_id (user_type_id)
          ) DEFAULT CHARACTER SET=utf8mb4 ";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE va_user_profile_properties_types (
          property_id INTEGER,
          user_type_id INTEGER)";
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE TABLE va_user_profile_properties_types (
          property_id INT4 NOT NULL default '0',
          user_type_id INT4 NOT NULL default '0')";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE va_user_profile_properties_types (
          [property_id] INTEGER,
          [user_type_id] INTEGER)";
			} 

			if ($db_type != "mysql") {
				$sqls[] = "CREATE INDEX va_user_profile_properties_types_property_id ON va_user_profile_properties_types (property_id)";
				$sqls[] = "CREATE INDEX va_user_profile_properties_types_user_type_id ON va_user_profile_properties_types (user_type_id)";
			}
		}


		if (!$user_profile_properties_sites) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE va_user_profile_properties_sites (
          `property_id` INT(11) DEFAULT '0',
          `site_id` INT(11) DEFAULT '0'
          ,KEY property_id (property_id)
          ,KEY site_id (site_id)
					) DEFAULT CHARACTER SET=utf8mb4 ";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE va_user_profile_properties_sites (
          property_id INTEGER,
          site_id INTEGER)";
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE TABLE va_user_profile_properties_sites (
          property_id INT4 default '0',
          site_id INT4 default '0')";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE va_user_profile_properties_sites (
          [property_id] INTEGER,
          [site_id] INTEGER)";
			} 

			if ($db_type != "mysql") {
				$sqls[] = "CREATE INDEX va_user_profile_properties_sites_property_id ON va_user_profile_properties_sites (property_id)";
				$sqls[] = "CREATE INDEX va_user_profile_properties_sites_site_id ON va_user_profile_properties_sites (site_id)";
			}
		}

		$user_profile_properties_types_all = false; $user_profile_properties_sites_all = false;
		$fields = $db->get_fields($table_prefix."user_profile_properties");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "user_types_all") {
				$user_profile_properties_types_all = true;
			} else if ($field_info["name"] == "sites_all") {
				$user_profile_properties_sites_all = true;
			}
		}

		if (!$user_profile_properties_types_all) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "user_profile_properties ADD user_types_all TINYINT DEFAULT '1' AFTER user_type_id ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "user_profile_properties ADD user_types_all TINYINT DEFAULT '1' ",
				"postgre" => "ALTER TABLE " . $table_prefix . "user_profile_properties ADD user_types_all SMALLINT DEFAULT '1' ",
				"access"  => "ALTER TABLE " . $table_prefix . "user_profile_properties ADD user_types_all BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "user_profile_properties SET user_types_all=0 ";

			$sql = " SELECT property_id, user_type_id FROM " . $table_prefix . "user_profile_properties ";
			$db->query($sql);
			while ($db->next_record()) {
				$property_id = $db->f("property_id");
				$user_type_id = $db->f("user_type_id");
				$sql  = " INSERT INTO " . $table_prefix . "user_profile_properties_types (property_id, user_type_id) VALUES (";
				$sql .= $db->tosql($property_id, INTEGER) . ", ";
				$sql .= $db->tosql($user_type_id, INTEGER) . ") ";
				$sqls[] = $sql;
			}
		}

		if (!$user_profile_properties_sites_all) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "user_profile_properties ADD sites_all TINYINT DEFAULT '1' AFTER property_id ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "user_profile_properties ADD sites_all TINYINT DEFAULT '1' ",
				"postgre" => "ALTER TABLE " . $table_prefix . "user_profile_properties ADD sites_all SMALLINT DEFAULT '1' ",
				"access"  => "ALTER TABLE " . $table_prefix . "user_profile_properties ADD sites_all BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "user_profile_properties SET sites_all=1 ";
		}
		// end user profile properties update

		// add new custom fields menu to User Settings menu section
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

		// add new option to use support properties for different types, deps and sites
		if (!$support_custom_sites) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE va_support_custom_sites (
          `property_id` INT(11) DEFAULT '0',
          `site_id` INT(11) DEFAULT '0'
          ,KEY property_id (property_id)
          ,KEY site_id (site_id)
          ) DEFAULT CHARACTER SET=utf8mb4 ";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE va_support_custom_sites (
          property_id INTEGER,
          site_id INTEGER)";
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE TABLE va_support_custom_sites (
          property_id INT4 default '0',
          site_id INT4 default '0')";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE va_support_custom_sites (
          [property_id] INTEGER,
          [site_id] INTEGER)";
			} 

			if ($db_type != "mysql") {
				$sqls[] = "CREATE INDEX va_support_custom_sites_property_id ON va_support_custom_sites (property_id)";
				$sqls[] = "CREATE INDEX va_support_custom_sites_site_id ON va_support_custom_sites (site_id)";
			}
		}

		if (!$support_custom_types) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE va_support_custom_types (
          `property_id` INT(11) DEFAULT '0',
          `type_id` INT(11) DEFAULT '0'
          ,KEY property_id (property_id)
          ,KEY type_id (type_id)
          ) DEFAULT CHARACTER SET=utf8mb4 ";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE va_support_custom_types (
          property_id INTEGER,
          type_id INTEGER)";
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE TABLE va_support_custom_types (
          property_id INT4 default '0',
          type_id INT4 default '0')";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE va_support_custom_types (
          [property_id] INTEGER,
          [type_id] INTEGER) ";
			} 

			if ($db_type != "mysql") {
				$sqls[] = "CREATE INDEX va_support_custom_types_property_id ON va_support_custom_types (property_id);";
				$sqls[] = "CREATE INDEX va_support_custom_types_type_id ON va_support_custom_types (type_id);";
			}
		}

		if (!$support_custom_departments) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE va_support_custom_departments (
          `property_id` INT(11) DEFAULT '0',
          `dep_id` INT(11) DEFAULT '0'
          ,KEY dep_id (dep_id)
          ,KEY property_id (property_id)
          ) DEFAULT CHARACTER SET=utf8mb4 ";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE va_support_custom_departments (
          property_id INTEGER,
          dep_id INTEGER)";
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE TABLE va_support_custom_departments (
          property_id INT4 default '0',
          dep_id INT4 default '0')";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE va_support_custom_departments (
          [property_id] INTEGER,
          [dep_id] INTEGER)";
			} 

			if ($db_type != "mysql") {
				$sqls[] = "CREATE INDEX va_support_custom_departments_dep_id ON va_support_custom_departments (dep_id)";
				$sqls[] = "CREATE INDEX va_support_custom_departments_property_id ON va_support_custom_departments (property_id)";
			}
		}

		$support_custom_properties_sites_all = false; 
		$support_custom_properties_types_all = false; 
		$support_custom_properties_deps_all = false; 
		$fields = $db->get_fields($table_prefix."support_custom_properties");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "sites_all") {
				$support_custom_properties_sites_all = true;
			} else if ($field_info["name"] == "types_all") {
				$support_custom_properties_types_all = true;
			} else if ($field_info["name"] == "deps_all") {
				$support_custom_properties_deps_all = true;
			}
		}

		if (!$support_custom_properties_sites_all) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_custom_properties ADD sites_all TINYINT DEFAULT '1' AFTER site_id ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_custom_properties ADD sites_all TINYINT DEFAULT '1' ",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_custom_properties ADD sites_all SMALLINT DEFAULT '1' ",
				"access"  => "ALTER TABLE " . $table_prefix . "support_custom_properties ADD sites_all BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "support_custom_properties SET sites_all=0 ";

			$sql = " SELECT property_id, site_id FROM " . $table_prefix . "support_custom_properties ";
			$db->query($sql);
			while ($db->next_record()) {
				$property_id = $db->f("property_id");
				$site_id = $db->f("site_id");
				$sql  = " INSERT INTO " . $table_prefix . "support_custom_sites (property_id, site_id) VALUES (";
				$sql .= $db->tosql($property_id, INTEGER) . ", ";
				$sql .= $db->tosql($site_id, INTEGER) . ") ";
				$sqls[] = $sql;
			}
		}

		if (!$support_custom_properties_types_all) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_custom_properties ADD types_all TINYINT DEFAULT '1' ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_custom_properties ADD types_all TINYINT DEFAULT '1' ",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_custom_properties ADD types_all SMALLINT DEFAULT '1' ",
				"access"  => "ALTER TABLE " . $table_prefix . "support_custom_properties ADD types_all BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "support_custom_properties SET types_all=1 ";
		}

		if (!$support_custom_properties_deps_all) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_custom_properties ADD deps_all TINYINT DEFAULT '1' ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_custom_properties ADD deps_all TINYINT DEFAULT '1' ",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_custom_properties ADD deps_all SMALLINT DEFAULT '1' ",
				"access"  => "ALTER TABLE " . $table_prefix . "support_custom_properties ADD deps_all BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "support_custom_properties SET deps_all=1 ";
		}

		// add new fields to support departments table 
		$support_departments_intro = false; 
		$support_departments_default = false; 
		$fields = $db->get_fields($table_prefix."support_departments");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "intro_text") {
				$support_departments_intro = true;
			} else if ($field_info["name"] == "is_default") {
				$support_departments_default = true;
			} 
		}

		if (!$support_departments_intro) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD intro_text TEXT AFTER full_title ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD intro_text TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD intro_text TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD intro_text LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}

		if (!$support_departments_default) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_departments ADD is_default TINYINT DEFAULT '0'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_departments ADD is_default TINYINT DEFAULT '0'",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_departments ADD is_default SMALLINT DEFAULT '0'",
				"access"  => "ALTER TABLE " . $table_prefix . "support_departments ADD is_default BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "support_departments SET is_default=0 ";
		}


		// add new fields to support departments table 
		$support_types_intro = false; 
		$support_types_default = false; 
		$support_types_type_order = false;
		$fields = $db->get_fields($table_prefix."support_types");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "intro_text") {
				$support_types_intro = true;
			} else if ($field_info["name"] == "is_default") {
				$support_types_default = true;
			} else if ($field_info["name"] == "type_order") {
				$support_types_type_order = true;
			} 
		}

		if (!$support_types_default) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_types ADD is_default TINYINT DEFAULT '0'  ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_types ADD is_default TINYINT DEFAULT '0'",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_types ADD is_default SMALLINT DEFAULT '0'",
				"access"  => "ALTER TABLE " . $table_prefix . "support_types ADD is_default BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "support_types SET is_default=0 ";
		}

		if (!$support_types_intro) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_types ADD intro_text TEXT  ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_types ADD intro_text TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_types ADD intro_text TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_types ADD intro_text LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}

		if (!$support_types_type_order) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_types ADD type_order INT(11) DEFAULT '1' ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_types ADD type_order INTEGER DEFAULT '1' ",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_types ADD type_order INT4 DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "support_types ADD type_order INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "support_types SET type_order=type_id ";
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.1.3");
	}

	if (comp_vers("5.2", $current_db_version) == 1)
	{
		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.2");
	}
