<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_upgrade_sqls_4.3.php                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	check_admin_security("system_upgrade");

	if (comp_vers("4.2.1", $current_db_version) == 1)
	{
		$sqls[] = "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN length_units VARCHAR(8) ";
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN hide_name TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN hide_name SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN hide_name BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "ALTER TABLE " . $table_prefix . "orders_items_properties ADD COLUMN length_units VARCHAR(8) ";
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "orders_items_properties ADD COLUMN hide_name TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "orders_items_properties ADD COLUMN hide_name SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "orders_items_properties ADD COLUMN hide_name BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$mysql_sql  = "CREATE TABLE ".$table_prefix."items_properties_sizes (
      `size_id` INT(11) NOT NULL AUTO_INCREMENT,
      `property_id` INT(11) default '0',
      `width` DOUBLE(16,4) default '0',
      `height` DOUBLE(16,4) default '0',
      `price` DOUBLE(16,2) default '0'
      ,PRIMARY KEY (size_id)
      ,KEY property_id (property_id)
      ) DEFAULT CHARACTER SET=utf8mb4 ";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."items_properties_sizes START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."items_properties_sizes (
      size_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."items_properties_sizes'),
      property_id INT4 default '0',
      width FLOAT4 default '0',
      height FLOAT4 default '0',
      price FLOAT4 default '0'
      ,PRIMARY KEY (size_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."items_properties_sizes (
      [size_id]  COUNTER  NOT NULL,
      [property_id] INTEGER,
      [width] FLOAT,
      [height] FLOAT,
      [price] FLOAT
      ,PRIMARY KEY (size_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX ".$table_prefix."items_properties_sizes_p_35 ON ".$table_prefix."items_properties_sizes (property_id)";
		}

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "forum_list ADD COLUMN is_rss TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "forum_list ADD COLUMN is_rss SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "forum_list ADD COLUMN is_rss BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "forum_list ADD COLUMN rss_limit INT(11) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "forum_list ADD COLUMN rss_limit INT4  ",
			"access"  => "ALTER TABLE " . $table_prefix . "forum_list ADD COLUMN rss_limit INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "forum_list ADD COLUMN rss_on_breadcrumb TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "forum_list ADD COLUMN rss_on_breadcrumb SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "forum_list ADD COLUMN rss_on_breadcrumb BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "forum_list ADD COLUMN rss_on_list TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "forum_list ADD COLUMN rss_on_list SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "forum_list ADD COLUMN rss_on_list BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN orders_min_quantity INT(11) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN orders_min_quantity INT4  ",
			"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN orders_min_quantity INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN orders_max_quantity INT(11) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN orders_max_quantity INT4  ",
			"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN orders_max_quantity INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN orders_items_type TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN orders_items_type SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN orders_items_type BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN orders_items_ids TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN orders_items_ids TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN orders_items_ids LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN orders_types_ids TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN orders_types_ids TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN orders_types_ids LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.2.1");
	}

	if (comp_vers("4.2.2", $current_db_version) == 1)
	{
		$sqls[] = "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN final_title VARCHAR(255) ";

		$mysql_sql  = "CREATE TABLE ".$table_prefix."orders_payments (
      `order_payment_id` INT(11) NOT NULL AUTO_INCREMENT,
      `order_id` INT(11) default '0',
      `payment_id` INT(11) default '0',
      `payment_index` INT(11) default '1',
      `payment_amount` DOUBLE(16,2) default '0',
      `transaction_id` VARCHAR(128),
      `success_message` VARCHAR(255),
      `pending_message` VARCHAR(255),
      `error_message` VARCHAR(255),
      `remote_ip` VARCHAR(32),
      `payment_currency_code` VARCHAR(4),
      `payment_currency_rate` DOUBLE(16,8) default '1',
      `payment_status` INT(11) default '0',
      `payment_paid` TINYINT default '0'
      ,KEY order_id (order_id)
      ,KEY payment_id (payment_id)
      ,PRIMARY KEY (order_payment_id)
      ,KEY transaction_id (transaction_id)
    ) DEFAULT CHARACTER SET=utf8mb4";


		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."orders_payments START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."orders_payments (
      order_payment_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."orders_payments'),
      order_id INT4 default '0',
      payment_id INT4 default '0',
      payment_index INT4 default '1',
      payment_amount FLOAT4 default '0',
      transaction_id VARCHAR(128),
      success_message VARCHAR(255),
      pending_message VARCHAR(255),
      error_message VARCHAR(255),
      remote_ip VARCHAR(32),
      payment_currency_code VARCHAR(4),
      payment_currency_rate FLOAT4 default '1',
      payment_status INT4 default '0',
      payment_paid SMALLINT default '0'
      ,PRIMARY KEY (order_payment_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."orders_payments (
      [order_payment_id]  COUNTER  NOT NULL,
      [order_id] INTEGER,
      [payment_id] INTEGER,
      [payment_index] INTEGER,
      [payment_amount] FLOAT,
      [transaction_id] VARCHAR(128),
      [success_message] VARCHAR(255),
      [pending_message] VARCHAR(255),
      [error_message] VARCHAR(255),
      [remote_ip] VARCHAR(32),
      [payment_currency_code] VARCHAR(4),
      [payment_currency_rate] FLOAT,
      [payment_status] INTEGER,
      [payment_paid] BYTE
      ,PRIMARY KEY (order_payment_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX ".$table_prefix."orders_payments_order_id ON ".$table_prefix."orders_payments (order_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."orders_payments_payment_id ON ".$table_prefix."orders_payments (payment_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."orders_payments_transact_65 ON ".$table_prefix."orders_payments (transaction_id)";
		}

		$sql_types = array(
			"mysql"  => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_amount DOUBLE(16,2) ",
			"postgre"=> "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_amount FLOAT4 ",
			"access" => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_amount FLOAT "
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"  => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN paid_total DOUBLE(16,2) default '0' ",
			"postgre"=> "ALTER TABLE " . $table_prefix . "orders ADD COLUMN paid_total FLOAT4 default '0' ",
			"access" => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN paid_total FLOAT "
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN order_payment_id INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN order_payment_id INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN order_payment_id INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"  => "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN percentage_price_type TINYINT default '1' ",
			"postgre"=> "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN percentage_price_type SMALLINT default '1' ",
			"access" => "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN percentage_price_type BYTE "
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "items_properties SET percentage_price_type=1 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN percentage_property_id INT(11) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN percentage_property_id INT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN percentage_property_id INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN property_hint VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "items_properties_values ADD COLUMN image_tiny VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "items_properties_values ADD COLUMN image_small VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "items_properties_values ADD COLUMN image_large VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "items_properties_values ADD COLUMN image_super VARCHAR(255) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.2.2");
	}

	if (comp_vers("4.2.3", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "shipping_types MODIFY COLUMN shipping_type_code VARCHAR(128) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "shipping_types ALTER COLUMN shipping_type_code TYPE VARCHAR(128) ",
			"access"  => "ALTER TABLE " . $table_prefix . "shipping_types ALTER COLUMN shipping_type_code VARCHAR(128) ",
		);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "ALTER TABLE " . $table_prefix . "custom_blocks ADD COLUMN block_class VARCHAR(128) ";

		$sqls[] = "ALTER TABLE " . $table_prefix . "countries ADD COLUMN state_field_name VARCHAR(64) ";

		$sqls[] = "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN payment_code VARCHAR(64) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN payment_type VARCHAR(32) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN payment_php_lib VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN validation_php_lib VARCHAR(255) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.2.3");
	}

	if (comp_vers("4.2.4", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN mail_pdf_invoice TINYINT ",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN mail_pdf_invoice SMALLINT ",
			"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN mail_pdf_invoice BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN admin_pdf_invoice TINYINT ",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN admin_pdf_invoice SMALLINT ",
			"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN admin_pdf_invoice BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.2.4");
	}


	if (comp_vers("4.2.5", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN postal_match_type TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN postal_match_type SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN postal_match_type BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "shipping_types SET postal_match_type=1 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN category_id INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN category_id INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN category_id INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "articles_images SET category_id=0 ";
		$sqls[] = "CREATE INDEX ".$table_prefix."articles_images_category_id ON ".$table_prefix."articles_images (category_id)";

		$sqls[] = "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN image_small VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN image_small_alt VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN image_large VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN image_large_alt VARCHAR(255) ";
		$sqls[] = "UPDATE " . $table_prefix . "articles_images SET image_small=image_name, image_small_alt=image_alt ";

		$sqls[] = "ALTER TABLE " . $table_prefix . "articles_images DROP COLUMN image_width ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "articles_images DROP COLUMN image_height ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "articles_images DROP COLUMN image_name ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "articles_images DROP COLUMN image_alt";
		$sqls[] = "ALTER TABLE " . $table_prefix . "articles_images DROP COLUMN image_align ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN date_modified DATETIME ",
			"postgre" => "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN date_modified TIMESTAMP ",
			"access"  => "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN date_modified DATETIME ",
		);
		$sqls[] = $sql_types[$db_type];

		$mysql_sql  = "CREATE TABLE ".$table_prefix."articles_links (
      `link_id` INT(11) NOT NULL AUTO_INCREMENT,
      `article_id` INT(11) default '0',
      `category_id` INT(11) default '0',
      `link_title` VARCHAR(255),
      `link_url` VARCHAR(255),
      `date_added` DATETIME,
      `date_modified` DATETIME
      ,KEY article_id (article_id)
      ,KEY category_id (category_id)
      ,PRIMARY KEY (link_id)
			) DEFAULT CHARACTER SET=utf8mb4 ";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."articles_links START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."articles_links (
      link_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."articles_links'),
      article_id INT4 default '0',
      category_id INT4 default '0',
      link_title VARCHAR(255),
      link_url VARCHAR(255),
      date_added TIMESTAMP,
      date_modified TIMESTAMP
      ,PRIMARY KEY (link_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."articles_links (
      [link_id]  COUNTER  NOT NULL,
      [article_id] INTEGER,
      [category_id] INTEGER,
      [link_title] VARCHAR(255),
      [link_url] VARCHAR(255),
      [date_added] DATETIME,
      [date_modified] DATETIME
      ,PRIMARY KEY (link_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];


		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.2.5");
	}


	if (comp_vers("4.2.6", $current_db_version) == 1)
	{
		$sqls[] = "ALTER TABLE " . $table_prefix . "menus ADD COLUMN block_class VARCHAR(128) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "menus ADD COLUMN menu_class VARCHAR(128) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.2.6");
	}

	if (comp_vers("4.2.7", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "countries ADD COLUMN sites_all TINYINT NOT NULL DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "countries ADD COLUMN sites_all SMALLINT NOT NULL DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "countries ADD COLUMN sites_all BYTE NOT NULL ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "countries SET sites_all=1 ";

		$mysql_sql  = "CREATE TABLE " . $table_prefix . "countries_sites (";
		$mysql_sql .= "  `country_id` INT(11) NOT NULL default '0',";
		$mysql_sql .= "  `site_id` INT(11) NOT NULL default '0'";
		$mysql_sql .= "  ,PRIMARY KEY (country_id,site_id))";

		$postgre_sql  = "CREATE TABLE " . $table_prefix . "countries_sites (";
		$postgre_sql .= "  country_id INT4 NOT NULL default '0',";
		$postgre_sql .= "  site_id INT4 NOT NULL default '0'";
		$postgre_sql .= "  ,PRIMARY KEY (country_id,site_id))";

		$access_sql  = "CREATE TABLE " . $table_prefix . "countries_sites (";
		$access_sql .= "  [country_id] INTEGER NOT NULL,";
		$access_sql .= "  [site_id] INTEGER NOT NULL";
		$access_sql .= "  ,PRIMARY KEY (country_id,site_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.2.7");
	}


	if (comp_vers("4.2.8", $current_db_version) == 1)
	{
		$sqls[] = "ALTER TABLE " . $table_prefix . "cms_blocks_properties ADD COLUMN property_class VARCHAR(128) ";

		// check products module_id
		$sql = " SELECT module_id FROM ".$table_prefix."cms_modules WHERE module_code='global' "; 
		$module_id = get_db_value($sql);
		// get new block_id and block_order
		$sql = " SELECT MAX(block_id) FROM ".$table_prefix."cms_blocks "; 
		$block_id = get_db_value($sql) + 1;
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='navigation_bar' "; 
		$navigation_block_id = get_db_value($sql);

		if (!$navigation_block_id) {
			$navigation_block_id = $block_id;
			$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
			$sql.= $db->tosql($block_id, INTEGER).",";
			$sql.= $db->tosql($module_id, INTEGER).",";
			$sql.= $db->tosql(1, INTEGER).",";
			$sql.= $db->tosql("navigation_bar", TEXT).",";
			$sql.= $db->tosql("NAVIGATION_BAR_MSG", TEXT).",";
			$sql.= $db->tosql("block_navigation_bar.php", TEXT).",";
			$sql.= $db->tosql(1, INTEGER).")";
			$sqls[] = $sql;

			// add settings to new block
			$sql = " SELECT MAX(property_id) FROM ".$table_prefix."cms_blocks_properties "; 
			$property_id = get_db_value($sql);
			$property_order = 0;
			$sql = " SELECT MAX(value_id) FROM ".$table_prefix."cms_blocks_values "; 
			$value_id = get_db_value($sql);

			// global property how show navigation bar
			$property_id++; $property_order++;
			$sql = "INSERT INTO " . $table_prefix . "cms_blocks_properties (";
			$sql .= "property_id,block_id,property_order,property_name,property_class,control_type,variable_name,required) VALUES (";
			$sql .= $db->tosql($property_id, INTEGER).",";
			$sql .= $db->tosql($navigation_block_id, INTEGER).",";
			$sql .= $db->tosql($property_order, INTEGER).",";
			$sql .= $db->tosql("BLOCK_POSITION_MSG", TEXT).",";
			$sql .= $db->tosql("property-bottom", TEXT).",";
			$sql .= $db->tosql("LISTBOX", TEXT).",";
			$sql .= $db->tosql("block_position", TEXT).",";
			$sql .= $db->tosql(0, INTEGER).")";
			$sqls[] = $sql;

			$value_id++;
			$sql = "INSERT INTO " . $table_prefix . "cms_blocks_values ";
			$sql .= "(value_id,property_id,value_order,value_name,variable_value) VALUES (";
			$sql .= $db->tosql($value_id, INTEGER).",";
			$sql .= $db->tosql($property_id, INTEGER).",";
			$sql .= $db->tosql(1, INTEGER).",";
			$sql .= $db->tosql("DEFAULT_POSITION_MSG", TEXT).",";
			$sql .= $db->tosql("default", TEXT).") ";
			$sqls[] = $sql;	

			$value_id++;
			$sql = "INSERT INTO " . $table_prefix . "cms_blocks_values ";
			$sql .= "(value_id,property_id,value_order,value_name,variable_value) VALUES (";
			$sql .= $db->tosql($value_id, INTEGER).",";
			$sql .= $db->tosql($property_id, INTEGER).",";
			$sql .= $db->tosql(2, INTEGER).",";
			$sql .= $db->tosql("FIXED_POSITION_MSG", TEXT).",";
			$sql .= $db->tosql("fixed", TEXT).") ";
			$sqls[] = $sql;	


			$bar_settings = array(
				"column_name" => array("name" => "NAME_MSG", "class" => "cell-name", "type" => "LABEL"),
				"column_show" => array("name" => "ADMIN_SHOW_MSG", "class" => "cell-short", "type" => "LABEL", "default" => "ADMIN_SHOW_MSG"),
				"column_order" => array("name" => "ADMIN_ORDER_MSG", "class" => "cell-short", "type" => "LABEL", "default" => "ADMIN_ORDER_MSG"),
				"column_pos" => array("name" => "POSITION_MSG", "class" => "cell-short", "type" => "LABEL", "default" => "POSITION_MSG"),
				"home_name" => array("name" => "MENU_HOME", "class" => "cell-name", "type" => "LABEL"),
				"home_show" => array("name" => "ADMIN_SHOW_MSG", "class" => "cell-short", "type" => "CHECKBOX"),
				"home_order" => array("name" => "ADMIN_ORDER_MSG", "class" => "cell-short", "type" => "TEXTBOX"),
				"home_pos" => array("name" => "POSITION_MSG", "class" => "cell-short", "type" => "LISTBOX", "values" => array("left" => "LEFT_MSG", "right" => "RIGHT_MSG")),
				"language_name" => array("name" => "LANGUAGE_TITLE", "class" => "cell-name", "type" => "LABEL"),
				"language_show" => array("name" => "ADMIN_SHOW_MSG", "class" => "cell-short", "type" => "CHECKBOX"),
				"language_order" => array("name" => "ADMIN_ORDER_MSG", "class" => "cell-short", "type" => "TEXTBOX"),
				"language_pos" => array("name" => "POSITION_MSG", "class" => "cell-short", "type" => "LISTBOX", "values" => array("left" => "LEFT_MSG", "right" => "RIGHT_MSG")),
				"currency_name" => array("name" => "CURRENCY_TITLE", "class" => "cell-name", "type" => "LABEL"),
				"currency_show" => array("name" => "ADMIN_SHOW_MSG", "class" => "cell-short", "type" => "CHECKBOX"),
				"currency_order" => array("name" => "ADMIN_ORDER_MSG", "class" => "cell-short", "type" => "TEXTBOX"),
				"currency_pos" => array("name" => "POSITION_MSG", "class" => "cell-short", "type" => "LISTBOX", "values" => array("left" => "LEFT_MSG", "right" => "RIGHT_MSG")),
				"account_name" => array("name" => "MY_ACCOUNT_MSG", "class" => "cell-name", "type" => "LABEL"),
				"account_show" => array("name" => "ADMIN_SHOW_MSG", "class" => "cell-short", "type" => "CHECKBOX"),
				"account_order" => array("name" => "ADMIN_ORDER_MSG", "class" => "cell-short", "type" => "TEXTBOX"),
				"account_pos" => array("name" => "POSITION_MSG", "class" => "cell-short", "type" => "LISTBOX", "values" => array("left" => "LEFT_MSG", "right" => "RIGHT_MSG")),
				"wishlist_name" => array("name" => "MY_WISHLIST_MSG", "class" => "cell-name", "type" => "LABEL"),
				"wishlist_show" => array("name" => "ADMIN_SHOW_MSG", "class" => "cell-short", "type" => "CHECKBOX"),
				"wishlist_order" => array("name" => "ADMIN_ORDER_MSG", "class" => "cell-short", "type" => "TEXTBOX"),
				"wishlist_pos" => array("name" => "POSITION_MSG", "class" => "cell-short", "type" => "LISTBOX", "values" => array("left" => "LEFT_MSG", "right" => "RIGHT_MSG")),
				"compare_name" => array("name" => "COMPARE_MSG", "class" => "cell-name", "type" => "LABEL"),
				"compare_show" => array("name" => "ADMIN_SHOW_MSG", "class" => "cell-short", "type" => "CHECKBOX"),
				"compare_order" => array("name" => "ADMIN_ORDER_MSG", "class" => "cell-short", "type" => "TEXTBOX"),
				"compare_pos" => array("name" => "POSITION_MSG", "class" => "cell-short", "type" => "LISTBOX", "values" => array("left" => "LEFT_MSG", "right" => "RIGHT_MSG")),
				"products_name" => array("name" => "{PRODUCTS_TITLE} ({CATEGORIES_TITLE})", "class" => "cell-name", "type" => "LABEL"),
				"products_show" => array("name" => "ADMIN_SHOW_MSG", "class" => "cell-short", "type" => "CHECKBOX"),
				"products_order" => array("name" => "ADMIN_ORDER_MSG", "class" => "cell-short", "type" => "TEXTBOX"),
				"products_pos" => array("name" => "POSITION_MSG", "class" => "cell-short", "type" => "LISTBOX", "values" => array("left" => "LEFT_MSG", "right" => "RIGHT_MSG")),
				"products_search_name" => array("name" => "{PRODUCTS_TITLE} ({SEARCH_TITLE})", "class" => "cell-name", "type" => "LABEL"),
				"products_search_show" => array("name" => "ADMIN_SHOW_MSG", "class" => "cell-short", "type" => "CHECKBOX"),
				"products_search_order" => array("name" => "ADMIN_ORDER_MSG", "class" => "cell-short", "type" => "TEXTBOX"),
				"products_search_pos" => array("name" => "POSITION_MSG", "class" => "cell-short", "type" => "LISTBOX", "values" => array("left" => "LEFT_MSG", "right" => "RIGHT_MSG")),
				"site_search_name" => array("name" => "FULL_SITE_SEARCH_MSG", "class" => "cell-name", "type" => "LABEL"),
				"site_search_show" => array("name" => "ADMIN_SHOW_MSG", "class" => "cell-short", "type" => "CHECKBOX"),
				"site_search_order" => array("name" => "ADMIN_ORDER_MSG", "class" => "cell-short", "type" => "TEXTBOX"),
				"site_search_pos" => array("name" => "POSITION_MSG", "class" => "cell-short", "type" => "LISTBOX", "values" => array("left" => "LEFT_MSG", "right" => "RIGHT_MSG")),
				"cart_name" => array("name" => "CART_TITLE", "class" => "cell-name", "type" => "LABEL"),
				"cart_show" => array("name" => "ADMIN_SHOW_MSG", "class" => "cell-short", "type" => "CHECKBOX"),
				"cart_order" => array("name" => "ADMIN_ORDER_MSG", "class" => "cell-short", "type" => "TEXTBOX"),
				"cart_pos" => array("name" => "POSITION_MSG", "class" => "cell-short", "type" => "LISTBOX", "values" => array("left" => "LEFT_MSG", "right" => "RIGHT_MSG")),
			);
	  
			foreach ($bar_settings as $variable_name => $property_data) {
				$property_id++; $property_order++; 
				$default_value = isset($property_data["default"]) ? $property_data["default"] : "";
				$sql = "INSERT INTO " . $table_prefix . "cms_blocks_properties (";
				$sql .= "property_id,block_id,property_order,property_name,default_value,property_class,control_type,variable_name,required) VALUES (";
				$sql .= $db->tosql($property_id, INTEGER).",";
				$sql .= $db->tosql($navigation_block_id, INTEGER).",";
				$sql .= $db->tosql($property_order, INTEGER).",";
				$sql .= $db->tosql($property_data["name"], TEXT).",";
				$sql .= $db->tosql($default_value, TEXT).",";
				$sql .= $db->tosql($property_data["class"], TEXT).",";
				$sql .= $db->tosql($property_data["type"], TEXT).",";
				$sql .= $db->tosql($variable_name, TEXT).",";
				$sql .= $db->tosql(0, INTEGER).")";
				$sqls[] = $sql;
				if (isset($property_data["values"])) {
					$value_order = 0;
					foreach ($property_data["values"] as $variable_value => $value_name) {
						$value_id++; $value_order++;
						$sql = "INSERT INTO " . $table_prefix . "cms_blocks_values ";
						$sql .= "(value_id,property_id,value_order,value_name,variable_value) VALUES (";
						$sql .= $db->tosql($value_id, INTEGER).",";
						$sql .= $db->tosql($property_id, INTEGER).",";
						$sql .= $db->tosql($value_order, INTEGER).",";
						$sql .= $db->tosql($value_name, TEXT).",";
						$sql .= $db->tosql($variable_value, TEXT).") ";
						$sqls[] = $sql;	
					}
				}
			}
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.2.8");
	}


	if (comp_vers("4.2.9", $current_db_version) == 1)
	{
		$sqls[] = "ALTER TABLE " . $table_prefix . "header_links ADD COLUMN menu_class VARCHAR(128) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.2.9");
	}

	if (comp_vers("4.2.10", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN is_default TINYINT NOT NULL DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN is_default SMALLINT NOT NULL DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN is_default BYTE NOT NULL ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "articles_images SET is_default=0 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN image_order INT(11) default '1' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN image_order INT4 default '1' ",
			"access"  => "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN image_order INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN image_position INT(11) default '1' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN image_position INT4 default '1' ",
			"access"  => "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN image_position INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "ALTER TABLE " . $table_prefix . "articles ADD COLUMN image_tiny VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "articles ADD COLUMN image_tiny_alt VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "articles ADD COLUMN image_super VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "articles ADD COLUMN image_super_alt VARCHAR(255) ";

		$sqls[] = "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN image_tiny VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN image_tiny_alt VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN image_super VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN image_super_alt VARCHAR(255) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN image_description TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN image_description TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "articles_images ADD COLUMN image_description LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "articles ADD COLUMN is_draft TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "articles ADD COLUMN is_draft SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "articles ADD COLUMN is_draft BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "articles SET is_draft=0 ";
		$sqls[] = "CREATE INDEX ".$table_prefix."articles_is_draft ON ".$table_prefix."articles (is_draft)";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "articles ADD COLUMN draft_parent_id INT(11) default '1' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "articles ADD COLUMN draft_parent_id INT4 default '1' ",
			"access"  => "ALTER TABLE " . $table_prefix . "articles ADD COLUMN draft_parent_id INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "CREATE INDEX ".$table_prefix."articles_draft_parent_id ON ".$table_prefix."articles (draft_parent_id)";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "items ADD COLUMN is_draft TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "items ADD COLUMN is_draft SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "items ADD COLUMN is_draft BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "items SET is_draft=0 ";
		$sqls[] = "CREATE INDEX ".$table_prefix."items_is_draft ON ".$table_prefix."items (is_draft)";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "items ADD COLUMN draft_parent_id INT(11) default '1' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "items ADD COLUMN draft_parent_id INT4 default '1' ",
			"access"  => "ALTER TABLE " . $table_prefix . "items ADD COLUMN draft_parent_id INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "CREATE INDEX ".$table_prefix."items_draft_parent_id ON ".$table_prefix."items (draft_parent_id)";

		if ($db_type == "mysql") {
			$sqls[] = "ALTER TABLE ".$table_prefix."items MODIFY google_base_type_id INT(11) default '0'";
			$sqls[] = "ALTER TABLE ".$table_prefix."items MODIFY price_id INT(11) default '0'";
			$sqls[] = "ALTER TABLE ".$table_prefix."items MODIFY trade_price_id INT(11) default '0'";
			$sqls[] = "ALTER TABLE ".$table_prefix."items MODIFY sales_price_id INT(11) default '0'";
			$sqls[] = "ALTER TABLE ".$table_prefix."items MODIFY trade_sales_id INT(11) default '0'";
			$sqls[] = "ALTER TABLE ".$table_prefix."items MODIFY buying_price_id INT(11) default '0'";
			$sqls[] = "ALTER TABLE ".$table_prefix."items MODIFY properties_price_id INT(11) default '0'";
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.2.10");
	}


	if (comp_vers("4.2.10.1", $current_db_version) == 1)
	{
		// check social media module
		$sql = " SELECT module_id FROM ".$table_prefix."cms_modules WHERE module_code='social_media' "; 
		$social_module_id = get_db_value($sql);
		// check articles module
		$sql = " SELECT module_id FROM ".$table_prefix."cms_modules WHERE module_code='articles' "; 
		$articles_module_id = get_db_value($sql);
		// get max block_id
		$sql = " SELECT MAX(block_id) FROM ".$table_prefix."cms_blocks "; 
		$block_id = get_db_value($sql);
		// get max property_id
		$sql = " SELECT MAX(property_id) FROM ".$table_prefix."cms_blocks_properties "; 
		$property_id = get_db_value($sql);
		// check if random module wasn't added
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='articles_random' "; 
		$random_block_id = get_db_value($sql);
		$sql = " SELECT MAX(value_id) FROM ".$table_prefix."cms_blocks_values "; 
		$value_id = get_db_value($sql);

		if ($articles_module_id && !$random_block_id) {
			$block_id++; 
			$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
			$sql.= $db->tosql($block_id, INTEGER).",";
			$sql.= $db->tosql($articles_module_id, INTEGER).",";
			$sql.= $db->tosql(1, INTEGER).",";
			$sql.= $db->tosql("articles_random", TEXT).",";
			$sql.= $db->tosql("RANDOM_TITLE", TEXT).",";
			$sql.= $db->tosql("block_articles_random.php", TEXT).",";
			$sql.= $db->tosql(1, INTEGER).")";
			$sqls[] = $sql;

			$property_order = 0;
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'RECORDS_PER_PAGE_MSG', 'TEXTBOX', NULL, NULL, 'recs', '1', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'NUMBER_OF_COLUMNS_MSG', 'TEXTBOX', NULL, NULL, 'cols', '1', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'RANDOM_IMAGE_MSG', 'TEXTBOX', NULL, NULL, 'random_image', '', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,after_control_html,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'ADMIN_SHOW_MSG', '<br/>', 'CHECKBOXLIST', NULL, NULL, 'show', NULL, 0)";
			// add fields values to show
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($property_id).", 1, 'ARTICLE_TITLE_MSG', 'article_title', '1') ";
			$sqls[] = $sql;	
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($property_id).", 1, 'AUTHOR_INFO_MSG', 'author', '1') ";
			$sqls[] = $sql;	
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($property_id).", 1, 'ARTICLE_DATE_MSG', 'article_date', '1') ";
			$sqls[] = $sql;	
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($property_id).", 1, 'IMAGE_TINY_MSG', 'image_tiny', '1') ";
			$sqls[] = $sql;	
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($property_id).", 1, 'IMAGE_SMALL_MSG', 'image_small', '1') ";
			$sqls[] = $sql;	
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($property_id).", 1, 'IMAGE_LARGE_MSG', 'image_large', '1') ";
			$sqls[] = $sql;	
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($property_id).", 1, 'IMAGE_SUPER_MSG', 'image_super', '1') ";
			$sqls[] = $sql;	
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($property_id).", 1, 'HOT_DESCRIPTION_MSG', 'hot_description', '1') ";
			$sqls[] = $sql;	
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($property_id).", 1, 'HIGHLIGHTS_MSG', 'highlights', '1') ";
			$sqls[] = $sql;	
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($property_id).", 1, 'SHORT_DESCRIPTION_MSG', 'short_description', '1') ";
			$sqls[] = $sql;	
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($property_id).", 1, 'FULL_DESCRIPTION_MSG', 'full_description', '1') ";
			$sqls[] = $sql;	
		}

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "articles ADD COLUMN highlights TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "articles ADD COLUMN highlights TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "articles ADD COLUMN highlights LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.2.10.1");
	}


	if (comp_vers("4.2.10.2", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "articles_categories ADD COLUMN article_edit_fields TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "articles_categories ADD COLUMN article_edit_fields TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "articles_categories ADD COLUMN article_edit_fields LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.2.10.2");
	}


	if (comp_vers("4.2.10.3", $current_db_version) == 1)
	{
		$mysql_sql  = "CREATE TABLE ".$table_prefix."albums (
      `album_id` INT(11) NOT NULL AUTO_INCREMENT,
      `album_name` VARCHAR(255),
      `album_date` DATETIME,
      `friendly_url` VARCHAR(255),
      `image_small` VARCHAR(255),
      `image_large` VARCHAR(255),
      `short_description` TEXT,
      `full_description` TEXT
      ,PRIMARY KEY (album_id)
		) DEFAULT CHARACTER SET=utf8mb4";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."albums START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."albums (
      album_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."albums'),
      album_name VARCHAR(255),
      album_date TIMESTAMP,
      friendly_url VARCHAR(255),
      image_small VARCHAR(255),
      image_large VARCHAR(255),
      short_description TEXT,
      full_description TEXT
      ,PRIMARY KEY (album_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."albums (
      [album_id] COUNTER  NOT NULL,
      [album_name] VARCHAR(255),
      [album_date] DATETIME,
      [friendly_url] VARCHAR(255),
      [image_small] VARCHAR(255),
      [image_large] VARCHAR(255),
      [short_description] LONGTEXT,
      [full_description] LONGTEXT
      ,PRIMARY KEY (album_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];


		$mysql_sql  = "CREATE TABLE ".$table_prefix."albums_authors (
      `album_id` INT(11) NOT NULL default '0',
      `author_id` INT(11) NOT NULL default '0'
      ,PRIMARY KEY (album_id,author_id)
			) DEFAULT CHARACTER SET=utf8mb4 ";


		$postgre_sql  = "CREATE TABLE ".$table_prefix."albums_authors (
		  album_id INT4 NOT NULL default '0',
		  author_id INT4 NOT NULL default '0'
		  ,PRIMARY KEY (album_id,author_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."albums_authors (
      [album_id] INTEGER NOT NULL,
      [author_id] INTEGER NOT NULL
      ,PRIMARY KEY (album_id,author_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		$mysql_sql  = "CREATE TABLE ".$table_prefix."articles_albums (
      `article_id` INT(11) NOT NULL default '0',
      `album_id` INT(11) NOT NULL default '0'
      ,PRIMARY KEY (article_id,album_id)
		) DEFAULT CHARACTER SET=utf8mb4 ";

		$postgre_sql  = "CREATE TABLE ".$table_prefix."articles_albums (
      article_id INT4 NOT NULL default '0',
      album_id INT4 NOT NULL default '0'
      ,PRIMARY KEY (article_id,album_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."articles_albums (
      [article_id] INTEGER NOT NULL,
      [album_id] INTEGER NOT NULL
      ,PRIMARY KEY (article_id,album_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		$mysql_sql  = "CREATE TABLE ".$table_prefix."articles_authors (
      `article_id` INT(11) NOT NULL default '0',
      `author_id` INT(11) NOT NULL default '0'
      ,PRIMARY KEY (article_id,author_id)
		) DEFAULT CHARACTER SET=utf8mb4 ";

		$postgre_sql  = "CREATE TABLE ".$table_prefix."articles_authors (
      article_id INT4 NOT NULL default '0',
      author_id INT4 NOT NULL default '0'
      ,PRIMARY KEY (article_id,author_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."articles_authors (
      [article_id] INTEGER NOT NULL,
      [author_id] INTEGER NOT NULL
      ,PRIMARY KEY (article_id,author_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.2.10.3");
	}


	if (comp_vers("4.2.11", $current_db_version) == 1)
	{
		$sqls[] = "ALTER TABLE " . $table_prefix . "articles ADD COLUMN youtube_video VARCHAR(255) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "articles ADD COLUMN youtube_video_width INT(11) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "articles ADD COLUMN youtube_video_width INT4  ",
			"access"  => "ALTER TABLE " . $table_prefix . "articles ADD COLUMN youtube_video_width INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "articles ADD COLUMN youtube_video_height INT(11) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "articles ADD COLUMN youtube_video_height INT4  ",
			"access"  => "ALTER TABLE " . $table_prefix . "articles ADD COLUMN youtube_video_height INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.2.11");
	}

	if (comp_vers("4.2.12", $current_db_version) == 1)
	{
		$sqls[] = "ALTER TABLE " . $table_prefix . "articles ADD COLUMN article_comment VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "articles ADD COLUMN title_first VARCHAR(2) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "articles ADD COLUMN link_title VARCHAR(255) ";
		$sqls[] = "CREATE INDEX " . $table_prefix . "articles_title_first ON " . $table_prefix . "articles (title_first)";

		$sqls[] = "ALTER TABLE " . $table_prefix . "authors ADD COLUMN other_name VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "authors ADD COLUMN name_first VARCHAR(2) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "authors ADD COLUMN middle_first VARCHAR(2) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "authors ADD COLUMN last_first VARCHAR(2) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "authors ADD COLUMN other_first VARCHAR(2) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "authors ADD COLUMN image_small VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "authors ADD COLUMN image_large VARCHAR(255) ";
		$sqls[] = "CREATE INDEX " . $table_prefix . "authors_name_first ON " . $table_prefix . "authors (name_first)";
		$sqls[] = "CREATE INDEX " . $table_prefix . "authors_middle_first ON " . $table_prefix . "authors (middle_first)";
		$sqls[] = "CREATE INDEX " . $table_prefix . "authors_last_first ON " . $table_prefix . "authors (last_first)";
		$sqls[] = "CREATE INDEX " . $table_prefix . "authors_other_first ON " . $table_prefix . "authors (other_first)";

		$sqls[] = "ALTER TABLE " . $table_prefix . "albums ADD COLUMN album_type VARCHAR(32) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "albums ADD COLUMN name_first VARCHAR(2) ";
		$sqls[] = "CREATE INDEX " . $table_prefix . "albums_name_first ON " . $table_prefix . "albums (name_first)";

		$mysql_sql  = "CREATE TABLE ".$table_prefix."tags (
      `tag_id` INT(11) NOT NULL AUTO_INCREMENT,
      `tag_name` VARCHAR(128),
      `name_first` VARCHAR(2),
      `friendly_url` VARCHAR(255)
      ,KEY friendly_url (friendly_url)
      ,KEY name_first (name_first)
      ,PRIMARY KEY (tag_id)
      ,KEY tag_name (tag_name)
      ) DEFAULT CHARACTER SET=utf8mb4 ";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."tags START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."tags (
      tag_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."tags'),
      tag_name VARCHAR(128),
      name_first VARCHAR(2),
      friendly_url VARCHAR(255)
      ,PRIMARY KEY (tag_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."tags (
      [tag_id]  COUNTER  NOT NULL,
      [tag_name] VARCHAR(128),
      [name_first] VARCHAR(2),
      [friendly_url] VARCHAR(255)
      ,PRIMARY KEY (tag_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX ".$table_prefix."tags_friendly_url ON ".$table_prefix."tags (friendly_url)";
			$sqls[] = "CREATE INDEX ".$table_prefix."tags_name_first ON ".$table_prefix."tags (name_first)";
			$sqls[] = "CREATE INDEX ".$table_prefix."tags_tag_name ON ".$table_prefix."tags (tag_name)";
		}

		$mysql_sql  = "CREATE TABLE ".$table_prefix."articles_tags (
      `article_id` INT(11) NOT NULL default '0',
      `tag_id` INT(11) NOT NULL default '0'
      ,PRIMARY KEY (article_id,tag_id)
			) DEFAULT CHARACTER SET=utf8mb4 ";

		$postgre_sql  = "CREATE TABLE ".$table_prefix."articles_tags (
		  article_id INT4 NOT NULL default '0',
		  tag_id INT4 NOT NULL default '0'
		  ,PRIMARY KEY (article_id,tag_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."articles_tags (
      [article_id] INTEGER NOT NULL,
      [tag_id] INTEGER NOT NULL
      ,PRIMARY KEY (article_id,tag_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.2.12");
	}

	if (comp_vers("4.3", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN header_menu_show TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN header_menu_show SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN header_menu_show BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "categories SET header_menu_show=0 ";
		$sqls[] = "CREATE INDEX ".$table_prefix."categories_menu_show ON ".$table_prefix."categories (header_menu_show)";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN header_menu_order INT(11) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN header_menu_order INT4  ",
			"access"  => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN header_menu_order INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "ALTER TABLE " . $table_prefix . "categories ADD COLUMN header_menu_class VARCHAR(64) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN nav_bar_show TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN nav_bar_show SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN nav_bar_show BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "categories SET nav_bar_show=0 ";
		$sqls[] = "CREATE INDEX ".$table_prefix."categories_nav_show ON ".$table_prefix."categories (nav_bar_show)";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN nav_bar_order INT(11) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN nav_bar_order INT4  ",
			"access"  => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN nav_bar_order INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "ALTER TABLE " . $table_prefix . "categories ADD COLUMN nav_bar_class VARCHAR(64) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "orders_shipments ADD COLUMN order_items_ids TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "orders_shipments ADD COLUMN order_items_ids TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "orders_shipments ADD COLUMN order_items_ids LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "header_links ADD COLUMN show_for_admin TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "header_links ADD COLUMN show_for_admin SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "header_links ADD COLUMN show_for_admin BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "header_links SET show_for_admin=0 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "header_links ADD COLUMN head_content TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "header_links ADD COLUMN head_content TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "header_links ADD COLUMN head_content LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "header_links ADD COLUMN foot_content TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "header_links ADD COLUMN foot_content TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "header_links ADD COLUMN foot_content LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.3");
	}

	if (comp_vers("4.3.1", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "menus ADD COLUMN menu_type TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "menus ADD COLUMN menu_type SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "menus ADD COLUMN menu_type BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "menus SET menu_type=3 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "menus ADD COLUMN sites_all TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "menus ADD COLUMN sites_all SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "menus ADD COLUMN sites_all BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "menus SET sites_all=1 ";

		$mysql_sql  = "CREATE TABLE ".$table_prefix."menus_sites (
      `menu_id` INT(11) NOT NULL default '0',
      `site_id` INT(11) NOT NULL default '0'
      ,PRIMARY KEY (menu_id,site_id)
			) DEFAULT CHARACTER SET=utf8mb4 ";

		$postgre_sql  = "CREATE TABLE ".$table_prefix."menus_sites (
		  menu_id INT4 NOT NULL default '0',
		  site_id INT4 NOT NULL default '0'
		  ,PRIMARY KEY (menu_id,site_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."menus_sites (
      [menu_id] INTEGER NOT NULL,
      [site_id] INTEGER NOT NULL
      ,PRIMARY KEY (menu_id,site_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "ALTER TABLE " . $table_prefix . "menus_items ADD COLUMN menu_code VARCHAR(64) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "menus_items ADD COLUMN menu_class VARCHAR(64) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "menus_items ADD COLUMN head_content TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "menus_items ADD COLUMN head_content TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "menus_items ADD COLUMN head_content LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "menus_items ADD COLUMN foot_content TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "menus_items ADD COLUMN foot_content TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "menus_items ADD COLUMN foot_content LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "menus_items ADD COLUMN guest_access TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "menus_items ADD COLUMN guest_access SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "menus_items ADD COLUMN guest_access BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "menus_items SET guest_access=show_non_logged ";

		$sqls[] = "ALTER TABLE " . $table_prefix . "menus_items ADD COLUMN user_access VARCHAR(255) ";
		$sqls[] = " UPDATE " . $table_prefix . "menus_items SET user_access=show_logged";

		$sqls[] = "ALTER TABLE " . $table_prefix . "menus_items ADD COLUMN admin_access VARCHAR(255) ";
		$sqls[] = " UPDATE " . $table_prefix . "menus_items SET admin_access=show_logged";


		$sqls[] = "ALTER TABLE " . $table_prefix . "albums ADD COLUMN image_tiny VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "albums ADD COLUMN image_super VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "authors ADD COLUMN image_tiny VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "authors ADD COLUMN image_super VARCHAR(255) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.3.1");
	}

	if (comp_vers("4.4.2", $current_db_version) == 1)
	{
		$sql = " SELECT MAX(menu_id) FROM ".$table_prefix."menus "; 
		$max_menu_id = get_db_value($sql);

		$sql = " SELECT MAX(menu_item_id) FROM ".$table_prefix."menus_items "; 
		$max_menu_item_id = get_db_value($sql);
		$shift_menu_item = $max_menu_item_id;

		$bar_menu_id = $max_menu_id + 1;
		$sql  = " INSERT INTO " . $table_prefix . "menus (menu_id, menu_type, menu_name, menu_class, sites_all) VALUES (";
		$sql .= $bar_menu_id.", 1, 'NAVIGATION_BAR_MSG', 'nav-bar', 1) ";
		$sqls[] = $sql;
		$header_menu_id = $bar_menu_id + 1;
		$sql  = " INSERT INTO " . $table_prefix . "menus (menu_id, menu_type, menu_name, menu_class, sites_all) VALUES (";
		$sql .= $header_menu_id.", 2, 'HEADER_MENU_MSG', 'nav-header', 1) ";
		$sqls[] = $sql;
		$footer_menu_id = $header_menu_id+ 1;
		$sql  = " INSERT INTO " . $table_prefix . "menus (menu_id, menu_type, menu_name, menu_class, sites_all) VALUES (";
		$sql .= $footer_menu_id.", 4, 'FOOTER_MENU_MSG', 'nav-footer', 1) ";
		$sqls[] = $sql;
		$admin_menu_id = $footer_menu_id+ 1;
		$sql  = " INSERT INTO " . $table_prefix . "menus (menu_id, menu_type, menu_name, menu_class, sites_all) VALUES (";
		$sql .= $admin_menu_id.", 5, 'ADMINISTRATIVE_MENU_MSG', 'nav-admin', 1) ";
		$sqls[] = $sql;

		// get default site design
		$sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
		$sql .= " WHERE setting_type='global' AND setting_name='layout_id' AND site_id=1 ";
		$default_layout_id = get_db_value($sql);

		// populate header menu
		$sql  = " SELECT * FROM " . $table_prefix . "header_links ";
		$sql .= " WHERE layout_id=" . $db->tosql($default_layout_id, INTEGER);
		$sql .= " OR layout_id=0 ";
		$db->query($sql);
		while ($db->next_record()) {
			$menu_id = $db->f("menu_id");
			$parent_menu_id = $db->f("parent_menu_id");
			$menu_item_id = $menu_id + $shift_menu_item;
			if ($parent_menu_id) {
				$parent_menu_item_id = $parent_menu_id + $shift_menu_item;
			} else {
				$parent_menu_item_id = 0;
			}
			$menu_title = $db->f("menu_title");
			$menu_url = $db->f("menu_url");
			$menu_class = $db->f("menu_class");
			$menu_target = $db->f("menu_target");
			$menu_image = $db->f("menu_image");
			$menu_image_active = $db->f("menu_image_active");
			$menu_order = $db->f("menu_order");
			$show_non_logged = $db->f("show_non_logged");
			$show_logged = $db->f("show_logged");
			$sql  = " INSERT INTO " . $table_prefix . "menus_items ";
			$sql .= " (menu_item_id, menu_id, parent_menu_item_id, menu_title, menu_url, menu_class, menu_target, menu_image, menu_image_active, menu_order, guest_access, user_access, admin_access) VALUES (";
			$sql .= $db->tosql($menu_item_id, INTEGER) . ", ";
			$sql .= $db->tosql($header_menu_id, INTEGER) . ", ";
			$sql .= $db->tosql($parent_menu_item_id, INTEGER) . ", ";
			$sql .= $db->tosql($menu_title, TEXT) . ", ";
			$sql .= $db->tosql($menu_url, TEXT) . ", ";
			$sql .= $db->tosql($menu_class, TEXT) . ", ";
			$sql .= $db->tosql($menu_target, TEXT) . ", ";
			$sql .= $db->tosql($menu_image, TEXT) . ", ";
			$sql .= $db->tosql($menu_image_active, TEXT) . ", ";
			$sql .= $db->tosql($menu_order, INTEGER) . ", ";

			$sql .= $db->tosql($show_non_logged, INTEGER) . ", ";
			$sql .= $db->tosql($show_logged, TEXT) . ", ";
			$sql .= $db->tosql("all", TEXT) . ") ";
			$sqls[] = $sql;

			if ($menu_item_id > $max_menu_item_id) { $max_menu_item_id = $menu_item_id; }
		}

		// populate footer menu
		$menu_item_id = $max_menu_item_id;
		$sql = " SELECT * FROM " . $table_prefix . "footer_links ";
		$db->query($sql);
		while ($db->next_record()) {
			$menu_item_id++;
			$menu_title = $db->f("menu_title");
			$menu_url = $db->f("menu_url");
			$menu_target = $db->f("menu_target");
			$menu_order = $db->f("menu_order");
			$guest_access_level = $db->f("guest_access_level");
			$access_level = $db->f("access_level");
			$sql  = " INSERT INTO " . $table_prefix . "menus_items ";
			$sql .= " (menu_item_id, menu_id, parent_menu_item_id, menu_title, menu_url, menu_target, menu_order, guest_access, user_access, admin_access) VALUES (";
			$sql .= $db->tosql($menu_item_id, INTEGER) . ", ";
			$sql .= $db->tosql($footer_menu_id, INTEGER) . ", ";
			$sql .= $db->tosql(0, INTEGER) . ", ";
			$sql .= $db->tosql($menu_title, TEXT) . ", ";
			$sql .= $db->tosql($menu_url, TEXT) . ", ";
			$sql .= $db->tosql($menu_target, TEXT) . ", ";
			$sql .= $db->tosql($menu_order, INTEGER) . ", ";
			$sql .= $db->tosql($guest_access_level, INTEGER) . ", ";
			$sql .= $db->tosql($access_level, TEXT) . ", ";
			$sql .= $db->tosql("all", TEXT) . ") ";
			$sqls[] = $sql;
		}
		$max_menu_item_id = $menu_item_id;

		// populate admin menu
		$admin_menu = array(
			"1" => array("code" => "dashboard", "name" => "DASHBOARD_MSG", "url" => "admin_menu.php?code=dashboard", "class" => "dashboard", "order" => 1),
				"2" => array("code" => "products", "parent" => 1, "name" => "PRODUCTS_MSG", "url" => "admin_items_list.php", "class" => "section js-no", "order" => 1, "access" => "products_categories,products_reviews,products_report"),
					"3" => array("parent" => 2, "name" => "PRODUCTS_CATEGORIES_MSG", "url" => "admin_items_list.php", "code" => "products-categories", "order" => 1, "access" => "products_categories"),
					"4" => array("parent" => 2, "name" => "PRODUCTS_REVIEWS_MSG", "url" => "admin_reviews.php", "class" => "", "order" => 5, "access" => "products_reviews"),
					"5" => array("parent" => 2, "name" => "PRODUCTS_REPORT", "url" => "admin_products_report.php", "class" => "", "order" => 8, "access" => "products_report"),
				"6" => array("code" => "orders", "parent" => 1, "name" => "ORDERS_MSG", "url" => "admin_orders.php", "class" => "section js-no", "order" => 2, "access" => "sales_orders,orders_recover,orders_stats"),
					"7" => array("parent" => 6, "name" => "SALES_ORDERS_MSG", "url" => "admin_orders.php", "class" => "", "order" => 1, "access" => "sales_orders"),
					"8" => array("parent" => 6, "name" => "ORDERS_RECOVER_MSG", "url" => "admin_orders_recover.php", "class" => "", "order" => 2, "access" => "orders_recover"),
					"9" => array("parent" => 6, "name" => "ORDERS_REPORTS_MSG", "url" => "admin_orders_report.php", "class" => "", "order" => 3, "access" => "orders_stats"),
					"10" => array("parent" => 6, "name" => "CARTS_REPORT_MSG", "url" => "admin_carts_report.php", "class" => "", "order" => 4, "access" => "orders_stats"),
				"11" => array("code" => "articles", "parent" => 1, "name" => "ARTICLES_TITLE", "url" => "admin_articles_all.php", "class" => "section js-no", "order" => 3, "access" => "articles,articles_reviews"),
					"12" => array("code" => "articles-categories", "parent" => 11, "name" => "ARTICLES_TITLE", "url" => "admin_articles_all.php", "order" => 1, "access" => "articles"),
					"13" => array("parent" => 11, "name" => "ARTICLES_REVIEWS_MSG", "url" => "admin_articles_reviews.php", "class" => "", "order" => 2, "access" => "articles_reviews"),
				"14" => array("code" => "helpdesk", "parent" => 1, "name" => "HELPDESK_MSG", "url" => "admin_support.php", "class" => "section js-no", "order" => 4, "access" => "support,support_users_stats"),
					"15" => array("parent" => 14, "name" => "SUPPORT_TICKETS_MSG", "url" => "admin_support.php", "class" => "", "order" => 1, "access" => "support"),
					"16" => array("parent" => 14, "name" => "CHATS_MSG", "url" => "admin_support_chats.php", "class" => "", "order" => 2, "access" => "support"),
					"17" => array("parent" => 14, "name" => "OPERATORS_REPORT_MSG", "url" => "admin_support_users_report.php", "class" => "", "order" => 3, "access" => "support_users_stats"),
				"19" => array("code" => "forum", "parent" => 1, "name" => "ADMIN_FORUM_TITLE", "url" => "admin_forum.php", "class" => "section js-no", "order" => 5, "access" => "forum"),
					"20" => array("parent" => 19, "name" => "CATEGORIES_TITLE", "url" => "admin_forum.php", "code" => "forum-categories", "order" => 1, "access" => "forum"),
				"21" => array("code" => "manual", "parent" => 1, "name" => "MANUAL_MSG", "url" => "admin_manual.php", "class" => "section js-no", "order" => 6, "access" => "manual"),
					"22" => array("parent" => 21, "name" => "MANUAL_MSG", "url" => "admin_manual.php", "code" => "manual-categories", "order" => 1, "access" => "manual"),
				"23" => array("code" => "ads", "parent" => 1, "name" => "ADS_TITLE", "url" => "admin_ads.php", "class" => "section js-no", "order" => 7, "access" => "ads"),
					"24" => array("parent" => 23, "name" => "ADS_TITLE", "url" => "admin_ads.php", "code" => "ads-categories", "order" => 1, "access" => "ads"),
				"25" => array("code" => "registration", "parent" => 1, "name" => "PRODUCT_REGISTRATION_MSG", "url" => "admin_registrations.php", "class" => "section js-no", "order" => 8, "access" => "admin_registration"),
					"26" => array("parent" => 25, "name" => "PRODUCT_REGISTRATION_MSG", "url" => "admin_registrations.php", "class" => "", "order" => 1, "access" => "admin_registration"),
					"27" => array("parent" => 25, "name" => "REGISTRATION_PRODUCTS_MSG", "url" => "admin_registration_products.php", "class" => "", "order" => 2, "access" => "admin_registration"),
				"28" => array("code" => "users", "parent" => 1, "name" => "CUSTOMERS_MSG", "url" => "admin_users.php", "class" => "section js-no", "order" => 9, "access" => "site_users,newsletter"),
					"29" => array("parent" => 28, "name" => "ACCOUNTS_MSG", "url" => "admin_users.php", "class" => "", "order" => 1, "access" => "site_users"),
					"30" => array("parent" => 28, "name" => "NEWSLETTER_USERS_MSG", "url" => "admin_newsletter_users.php", "class" => "", "order" => 2, "access" => "newsletter"),
				"31" => array("code" => "profiles","parent" => 1, "name" => "PROFILES_TITLE", "url" => "admin_profiles.php", "class" => "section js-no", "order" => 10, "access" => "profiles"),
			"32" => array("code" => "cms", "name" => "CMS_MSG", "url" => "admin_cms.php", "class" => "cms", "order" => 2),
				"33" => array("code" => "layouts", "parent" => 32, "name" => "CMS_MSG", "url" => "admin_cms.php", "class" => "section js-no", "order" => 1, "access" => "cms_settings"),
					"34" => array("parent" => 33, "name" => "PAGES_LAYOUTS_MSG", "url" => "admin_cms.php", "class" => "", "order" => 1, "access" => "cms_settings"),
					"35" => array("parent" => 33, "name" => "MULTI_EDIT_MSG", "url" => "admin_cms_multi_edit.php", "class" => "", "order" => 2, "access" => "cms_settings"),
					"36" => array("parent" => 33, "name" => "CMS_LAYOUTS_MSG", "url" => "admin_cms_layouts.php", "class" => "", "order" => 3, "access" => "cms_settings"),
					"37" => array("parent" => 33, "name" => "CMS_MODULES_MSG", "url" => "admin_cms_modules.php", "class" => "", "order" => 4, "access" => "cms_settings"),
					"38" => array("parent" => 33, "name" => "CMS_PAGES_MSG", "url" => "admin_cms_pages.php", "class" => "", "order" => 5, "access" => "cms_settings"),
					"39" => array("parent" => 33, "name" => "CMS_BLOCKS_MSG", "url" => "admin_cms_blocks.php", "class" => "", "order" => 6, "access" => "cms_settings"),
					"40" => array("parent" => 33, "name" => "DESIGNS_MSG", "url" => "admin_designs.php", "class" => "", "order" => 7, "access" => "cms_settings"),
				"41" => array("code" => "custom-modules", "parent" => 32, "name" => "CUSTOM_MODULES_MSG", "url" => "admin_menu.php?code=custom-modules", "class" => "section js-no", "order" => 2, "access" => "site_navigation,custom_blocks,web_pages,custom_friendly_urls,polls,filters,sliders"),
					"42" => array("parent" => 41, "name" => "SITE_NAVIGATION_MSG", "url" => "admin_menu_list.php", "order" => 1, "access" => "site_navigation"),
					"43" => array("parent" => 41, "name" => "CUSTOM_BLOCKS_MSG", "url" => "admin_custom_blocks.php", "order" => 2, "access" => "custom_blocks"),
					"44" => array("parent" => 41, "name" => "CUSTOM_PAGES_MSG", "url" => "admin_pages.php", "order" => 3, "access" => "web_pages"),
					"45" => array("parent" => 41, "name" => "CUSTOM_FRIENDLY_URLS_MSG", "url" => "admin_friendly_urls.php", "order" => 4, "access" => "custom_friendly_urls"),
					"48" => array("parent" => 41, "name" => "OPINION_POLLS_MSG", "url" => "admin_polls.php", "order" => 7, "access" => "polls"),
					"49" => array("parent" => 41, "name" => "FILTERS_MSG", "url" => "admin_filters.php", "order" => 8, "access" => "filters"),
					"50" => array("parent" => 41, "name" => "SLIDERS_MSG", "url" => "admin_sliders.php", "order" => 9, "access" => "sliders"),
				"57" => array("code" => "banners", "parent" => 32, "name" => "BANNERS_MSG", "url" => "admin_banners.php", "class" => "section js-no", "order" => 3, "access" => "banners"),
					"46" => array("parent" => 57, "name" => "BANNERS_MSG", "url" => "admin_banners.php", "order" => 1, "access" => "banners"),
					"47" => array("parent" => 57, "name" => "BANNERS_GROUPS_MSG", "url" => "admin_banners_groups.php", "order" => 1, "access" => "banners"),
			"62" => array("code" => "tools", "name" => "TOOLS_MSG", "url" => "admin_menu.php?code=tools", "class" => "tools", "order" => 3),
				"63" => array("parent" => 62, "name" => "DATABASE_MANAGEMENT_MSG", "url" => "admin_dump.php", "class" => "section js-no", "order" => 1, "access" => "db_management,system_upgrade"),
					"64" => array("parent" => 63, "name" => "APPLY_DUMP_MSG", "url" => "admin_dump.php", "order" => 1, "access" => "db_management"),
					"65" => array("parent" => 63, "name" => "CREATE_NEW_DUMP_MSG", "url" => "admin_dump_create.php", "order" => 2, "access" => "db_management"),
					"66" => array("parent" => 63, "name" => "RUN_SQL_QUERY_MSG", "url" => "admin_db_query.php", "order" => 3, "access" => "db_management"),
					"67" => array("parent" => 63, "name" => "SYSTEM_UPGRADE_MSG", "url" => "admin_upgrade.php", "order" => 4, "access" => "system_upgrade"),
				"68" => array("code" => "export-tools", "parent" => 62, "name" => "EXPORT_MSG", "url" => "admin_export_templates.php", "class" => "section js-no", "order" => 5, "access" => "products_export,export_users,sales_orders"),
					"69" => array("parent" => 68, "name" => "EXPORT_TEMPLATES_MSG", "url" => "admin_export_templates.php", "order" => 1, "access" => "products_export,export_users,sales_orders"),
				"70" => array("code" => "email-campaigns", "parent" => 62, "name" => "EMAIL_CAMPAIGNS_MSG", "url" => "admin_newsletter_campaigns.php", "class" => "section js-no", "order" => 4, "access" => "newsletter"),
					"71" => array("parent" => 70, "name" => "EMAIL_CAMPAIGNS_MSG", "url" => "admin_newsletter_campaigns.php", "class" => "", "order" => 1, "access" => "newsletter"),
					"72" => array("parent" => 70, "name" => "NEWSLETTER_USERS_MSG", "url" => "admin_newsletter_users.php", "class" => "", "order" => 2, "access" => "newsletter"),
				"73" => array("code" => "content-tools", "parent" => 62, "name" => "TOOLS_MSG", "url" => "admin_fm.php", "class" => "section js-no", "order" => 2, "access" => "filemanager,black_ips,static_tables,banned_contents,visits_report,all"),
					"74" => array("parent" => 73, "name" => "FILE_MANAGER_MSG", "url" => "admin_fm.php", "class" => "", "order" => 1, "access" => "filemanager"),
					"75" => array("parent" => 73, "name" => "FILE_TRANSFERS_MSG", "url" => "admin_file_transfers.php", "class" => "", "order" => 2, "access" => "filemanager"),
					"76" => array("parent" => 73, "name" => "RESIZE_IMAGES_MSG", "url" => "admin_images_resize.php", "class" => "", "order" => 3, "access" => "filemanager"),
					"77" => array("parent" => 73, "name" => "BLACK_IPS_MSG", "url" => "admin_black_ips.php", "class" => "", "order" => 4, "access" => "black_ips"),
					"78" => array("parent" => 73, "name" => "COUNTRIES_IPS_MSG", "url" => "admin_ips_countries.php", "class" => "", "order" => 5, "access" => "static_tables"),
					"79" => array("parent" => 73, "name" => "BANNED_CONTENT", "url" => "admin_banned_contents.php", "class" => "", "order" => 6, "access" => "banned_contents"),
					"81" => array("parent" => 73, "name" => "BOOKMARKS_MSG", "url" => "admin_bookmarks.php", "class" => "", "order" => 8, "access" => "all"),
				"170" => array("code" => "reports", "parent" => 62, "name" => "REPORTS_MSG", "url" => "admin_menu.php?code=reports", "class" => "section js-no", "order" => 3, "access" => "products_report,visits_report,support_users_stats"),
					"171" => array("parent" => 170, "name" => "PRODUCTS_REPORT", "url" => "admin_products_report.php", "class" => "", "order" => 8, "access" => "products_report"),
					"172" => array("parent" => 170, "name" => "ORDERS_REPORTS_MSG", "url" => "admin_orders_report.php", "class" => "", "order" => 3, "access" => "orders_stats"),
					"173" => array("parent" => 170, "name" => "CARTS_REPORT_MSG", "url" => "admin_carts_report.php", "class" => "", "order" => 4, "access" => "orders_stats"),
					"174" => array("parent" => 170, "name" => "{HELPDESK_MSG} {OPERATORS_REPORT_MSG}", "url" => "admin_support_users_report.php", "class" => "", "order" => 3, "access" => "support_users_stats"),
					"175" => array("parent" => 170, "name" => "TRACKING_VISITS_REPORT_MSG", "url" => "admin_visits_report.php", "class" => "", "order" => 7, "access" => "visits_report"),
			"52" => array("code" => "settings", "name" => "SETTINGS_MSG", "url" => "admin_menu.php?code=settings", "class" => "settings", "order" => 4),
				"83" => array("code" => "system-settings", "parent" => 52, "name" => "SYSTEM_MSG", "url" => "admin_global_settings.php", "class" => "section js-no", "order" => 1, "access" => "site_settings,admin_sites,admin_users,admins_groups,static_tables,static_messages"),
					"84" => array("parent" => 83, "name" => "GLOBAL_SETTINGS_MSG", "url" => "admin_global_settings.php", "order" => 1, "access" => "site_settings"),
					"85" => array("parent" => 83, "name" => "ADMIN_SITES_MSG", "url" => "admin_sites.php", "order" => 2, "access" => "admin_sites"),
					"86" => array("parent" => 83, "name" => "ADMINISTRATORS_MSG", "url" => "admin_admins.php", "order" => 3, "access" => "admin_users"),
					"87" => array("parent" => 83, "name" => "PRIVILEGE_GROUPS_MSG", "url" => "admin_privileges.php", "order" => 4, "access" => "admins_groups"),
					"88" => array("parent" => 83, "name" => "TWO_FACTOR_AUTH_MSG", "url" => "settings_two_factor.php", "order" => 5, "access" => "admins_groups"),
					"89" => array("parent" => 83, "name" => "STATIC_TABLES_MSG", "url" => "admin_static_tables.php", "order" => 6, "access" => "static_tables"),
					"90" => array("parent" => 83, "name" => "SYSTEM_STATIC_MESSAGES_MSG", "url" => "admin_messages.php", "order" => 7, "access" => "static_messages"),
					"91" => array("parent" => 83, "name" => "CONTACT_US_MSG", "url" => "admin_contact_us.php", "order" => 8, "access" => "site_settings"),
					"92" => array("parent" => 83, "name" => "INTERNAL_MESSAGES_MSG", "url" => "admin_messages_settings.php", "order" => 9, "access" => "site_settings"),
				"93" => array("code" => "products-settings","parent" => 52, "name" => "PRODUCTS_MSG", "url" => "admin_products_settings.php", "class" => "section js-no", "order" => 1, "access" => "products_settings,product_types,manufacturers,suppliers,features_groups,coupons,update_products,downloadable_products,products_reviews_settings,tell_friend"),
					"94" => array("parent" => 93, "name" => "PRODUCTS_SETTINGS_MSG", "url" => "admin_products_settings.php", "order" => 1, "access" => "products_settings"),
					"95" => array("parent" => 93, "name" => "PRODUCTS_TYPES_MSG", "url" => "admin_item_types.php", "class" => "", "order" => 2, "access" => "product_types"),
					"96" => array("parent" => 93, "name" => "MANUFACTURERS_TITLE", "url" => "admin_manufacturers.php", "class" => "", "order" => 3, "access" => "manufacturers"),
					"97" => array("parent" => 93, "name" => "SUPPLIERS_MSG", "url" => "admin_suppliers.php", "class" => "", "order" => 4, "access" => "suppliers"),
					"98" => array("parent" => 93, "name" => "SPECIFICATION_GROUPS_MSG", "url" => "admin_features_groups.php", "order" => 5, "access" => "features_groups"),
					"99" => array("parent" => 93, "name" => "WISHLIST_TYPES_MSG", "url" => "admin_saved_types.php", "class" => "", "order" => 6, "access" => "saved_types"),
					"100" => array("parent" => 93, "name" => "COUPONS_MSG", "url" => "admin_coupons.php", "class" => "", "order" => 7, "access" => "coupons"),
					"101" => array("parent" => 93, "name" => "KEYWORDS_MSG", "url" => "admin_keywords.php", "class" => "", "order" => 8, "access" => "update_products"),
					"102" => array("parent" => 93, "name" => "DOWNLOADABLE_PRODUCTS_MSG", "url" => "admin_download_info.php", "order" => 9, "access" => "downloadable_products"),
					"103" => array("parent" => 93, "name" => "ADVANCED_SEARCH_TITLE", "url" => "admin_search.php", "order" => 10, "access" => "advanced_search"),
					"104" => array("parent" => 93, "name" => "REVIEWS_SETTINGS_MSG", "url" => "admin_products_reviews_sets.php", "order" => 11, "access" => "products_reviews_settings"),
					"105" => array("parent" => 93, "name" => "TELL_FRIEND", "url" => "admin_tell_friend.php?type=products", "order" => 12, "access" => "tell_friend"),
					"106" => array("parent" => 93, "name" => "SAVED_CART_NOTIFICATION_MSG", "url" => "admin_saved_cart_notify.php", "order" => 13, "access" => "products_categories"),
				"107" => array("code" => "orders-settings", "parent" => 52, "name" => "ORDERS_MSG", "url" => "admin_menu.php?code=orders-settings", "class" => "section js-no", "order" => 3, "access" => "sales_orders,order_profile,shipping_methods,shipping_times,shipping_rules,payment_systems,order_statuses,tax_rates"),
					"108" => array("parent" => 107, "name" => "ORDER_PROFILE_PAGE_MSG", "url" => "admin_order_info.php", "order" => 1, "access" => "order_profile"),
					"109" => array("parent" => 107, "name" => "SHIPPING_METHODS_MSG", "url" => "admin_shipping_modules.php", "order" => 2, "access" => "shipping_methods"),
					"110" => array("parent" => 107, "name" => "SHIPPING_TIMES_MSG", "url" => "admin_shipping_times.php", "order" => 3, "access" => "shipping_times"),
					"111" => array("parent" => 107, "name" => "SHIPPING_RULES_MSG", "url" => "admin_shipping_rules.php", "order" => 4, "access" => "shipping_rules"),
					"112" => array("parent" => 107, "name" => "PAYMENT_SYSTEMS_MSG", "url" => "admin_payment_systems.php", "order" => 5, "access" => "payment_systems"),
					"114" => array("parent" => 107, "name" => "ORDERS_STATUSES_MSG", "url" => "admin_order_statuses.php", "order" => 6, "access" => "order_statuses"),
					"115" => array("parent" => 107, "name" => "TAX_RATES_MSG", "url" => "admin_tax_rates.php", "order" => 7, "access" => "tax_rates"),
					"116" => array("parent" => 107, "name" => "CURRENCIES_MSG", "url" => "admin_currencies.php", "order" => 8, "access" => "static_tables"),
					"117" => array("parent" => 107, "name" => "PRINTABLE_PAGE_SETTINGS_MSG", "url" => "admin_order_printable.php", "order" => 9, "access" => "order_profile"),
					"118" => array("parent" => 107, "name" => "BOM_SETTINGS_MSG", "url" => "admin_orders_bom_settings.php", "order" => 10, "access" => "order_profile"),
					"119" => array("parent" => 107, "name" => "ORDERS_RECOVER_MSG", "url" => "admin_orders_recover_settings.php", "order" => 11, "access" => "orders_recover"),
				"54" => array("code" => "articles-forum-settings","parent" => 52, "name" => "{ARTICLES_TITLE} & {ADMIN_FORUM_TITLE}", "url" => "admin_articles_top.php", "class" => "section-group js-no", "order" => 4, "access" => "articles,articles_statuses,articles_reviews_settings,forum"),
					"120" => array("code" => "articles-settings","parent" => 54, "name" => "ARTICLES_TITLE", "url" => "admin_articles_top.php", "class" => "section js-no", "order" => 1, "access" => "articles,articles_statuses,articles_reviews_settings"),
						"121" => array("parent" => 120, "name" => "ARTICLES_SETTINGS_MSG", "url" => "admin_articles_top.php", "order" => 1, "access" => "articles"),
						"122" => array("parent" => 120, "name" => "ARTICLES_STATUSES_MSG", "url" => "admin_articles_statuses.php", "order" => 2, "access" => "articles_statuses"),
						"123" => array("parent" => 120, "name" => "REVIEWS_SETTINGS_MSG", "url" => "admin_articles_reviews_sets.php", "order" => 3, "access" => "articles_reviews_settings"),
						"124" => array("parent" => 120, "name" => "ARTICLES_LOST_MSG", "url" => "admin_articles_lost.php", "class" => "", "order" => 4, "access" => "articles"),
					"136" => array("code" => "forum-settings", "parent" => 54, "name" => "ADMIN_FORUM_TITLE", "url" => "admin_forum_settings.php", "class" => "section js-no", "order" => 2, "access" => "forum,static_tables"),
						"137" => array("parent" => 136, "name" => "FORUM_SETTINGS_MSG", "url" => "admin_forum_settings.php", "order" => 1, "access" => "forum"),
						"138" => array("parent" => 136, "name" => "FORUM_PRIORITIES_MSG", "url" => "admin_forum_priorities.php", "order" => 2, "access" => "forum"),
						"139" => array("parent" => 136, "name" => "EMOTION_ICONS_MSG", "url" => "admin_icons.php", "order" => 3, "access" => "static_tables"),
				"125" => array("code" => "helpdesk-settings","parent" => 52, "name" => "HELPDESK_MSG", "url" => "admin_support_settings.php", "class" => "section js-no", "order" => 5, "access" => "support_settings,support_users,support_users_priorities,support_departments,support_static_data,support_predefined_reply"),
					"126" => array("parent" => 125, "name" => "HELPDESK_SETTINGS_MSG", "url" => "admin_support_settings.php", "order" => 1, "access" => "support_settings"),
					"127" => array("parent" => 125, "name" => "SUPPORT_USERS_MSG", "url" => "admin_support_admins.php", "order" => 2, "access" => "support_users"),
					"128" => array("parent" => 125, "name" => "CUSTOMERS_RANKS_MSG", "url" => "admin_support_ranks.php", "order" => 3, "access" => "support_users_priorities"),
					"129" => array("parent" => 125, "name" => "DEPARTMENTS_MSG", "url" => "admin_support_departments.php", "order" => 4, "access" => "support_departments"),
					"130" => array("parent" => 125, "name" => "SUPPORT_TYPES_MSG", "url" => "admin_support_types.php", "order" => 5, "access" => "support_static_data"),
					"131" => array("parent" => 125, "name" => "SUPPORT_PRODUCTS_MSG", "url" => "admin_support_products.php", "order" => 6, "access" => "support_static_data"),
					"132" => array("parent" => 125, "name" => "SUPPORT_PRIORITIES_MSG", "url" => "admin_support_priorities.php", "order" => 7, "access" => "support_static_data"),
					"133" => array("parent" => 125, "name" => "SUPPORT_STATUSES_MSG", "url" => "admin_support_statuses.php", "order" => 8, "access" => "support_static_data"),
					"134" => array("parent" => 125, "name" => "PREDEFINED_REPLIES_MSG", "url" => "admin_support_prereplies.php", "order" => 9, "access" => "support_predefined_reply"),
					"135" => array("parent" => 125, "name" => "PREDEFINED_TYPES_MSG", "url" => "admin_support_pretypes.php", "order" => 10, "access" => "support_predefined_reply"),
				"140" => array("code" => "ads-settings", "parent" => 52, "name" => "ADS_TITLE", "url" => "admin_ads_settings.php", "class" => "section js-no", "order" => 6, "access" => "ads"),
					"141" => array("parent" => 140, "name" => "ADS_SETTINGS_MSG", "url" => "admin_ads_settings.php", "order" => 1, "access" => "ads"),
					"142" => array("parent" => 140, "name" => "ADS_TYPES_MSG", "url" => "admin_ads_types.php", "order" => 2, "access" => "ads"),
					"143" => array("parent" => 140, "name" => "ADVERT_NOTIFICATION_MSG", "url" => "admin_ads_notify.php", "order" => 3, "access" => "ads"),
					"144" => array("parent" => 140, "name" => "ADVERT_REQUEST_MSG", "url" => "admin_ads_request.php", "order" => 4, "access" => "ads"),
					"145" => array("parent" => 140, "name" => "ADVANCED_SEARCH_TITLE", "url" => "admin_ads_search.php", "order" => 5, "access" => "ads"),
					"146" => array("parent" => 140, "name" => "TELL_FRIEND", "url" => "admin_tell_friend.php?type=ads", "order" => 6, "access" => "ads"),
					"147" => array("parent" => 140, "name" => "SPECIFICATION_GROUPS_MSG", "url" => "admin_ads_features_groups.php", "order" => 7, "access" => "ads"),
					"148" => array("parent" => 140, "name" => "ADS_DAYS_MSG", "url" => "admin_ads_days.php", "order" => 8, "access" => "ads"),
					"149" => array("parent" => 140, "name" => "ADS_HOT_DAYS_MSG", "url" => "admin_ads_hot_days.php", "order" => 9, "access" => "ads"),
					"150" => array("parent" => 140, "name" => "ADS_SPECIAL_DAYS_MSG", "url" => "admin_ads_special_days.php", "order" => 10, "access" => "ads"),
				"55" => array("code" => "users-profiles-settings","parent" => 52, "name" => "{CUSTOMERS_MSG} & {PROFILES_TITLE}", "url" => "admin_user_types.php", "class" => "section-group js-no", "order" => 7, "access" => "users_groups,static_tables,subscriptions_groups,subscriptions,users_forgot,users_payments,profiles"),
					"153" => array("code" => "users-settings","parent" => 55, "name" => "CUSTOMERS_MSG", "url" => "admin_user_types.php", "class" => "section js-no", "order" => 1, "access" => "users_groups,static_tables,subscriptions_groups,subscriptions,users_forgot,users_payments"),
						"154" => array("parent" => 153, "name" => "USERS_TYPES_MSG", "url" => "admin_user_types.php", "order" => 1, "access" => "users_groups"),
						"155" => array("parent" => 153, "name" => "FORM_SECTIONS_MSG", "url" => "admin_user_sections.php", "order" => 2, "access" => "static_tables"),
						"156" => array("parent" => 153, "name" => "SUBSCRIPTIONS_GROUPS_MSG", "url" => "admin_subscriptions_groups.php", "order" => 3, "access" => "subscriptions_groups"),
						"157" => array("parent" => 153, "name" => "SUBSCRIPTIONS_MSG", "url" => "admin_subscriptions.php", "order" => 4, "access" => "subscriptions"),
						"158" => array("parent" => 153, "name" => "FORGOTTEN_PASSWORD_MSG", "url" => "admin_forgotten_password.php", "order" => 5, "access" => "users_forgot"),
						"159" => array("parent" => 153, "name" => "CUSTOMERS_COMISSIONS_MSG", "url" => "admin_user_commissions.php", "order" => 6, "access" => "users_payments"),
						"160" => array("parent" => 153, "name" => "COMMISSION_PAYMENTS", "url" => "admin_user_payments.php", "order" => 7, "access" => "users_payments"),
					"161" => array("code" => "profiles-settings","parent" => 55, "name" => "PROFILES_TITLE", "url" => "admin_profiles_settings.php", "class" => "section js-no", "order" => 2, "access" => "profiles"),
						"162" => array("parent" => 161, "name" => "SETTINGS_MSG", "url" => "admin_profiles_settings.php", "order" => 1, "access" => "profiles"),
					"151" => array("code" => "registration-settings","parent" => 55, "name" => "PRODUCT_REGISTRATION_MSG", "url" => "admin_registration_settings.php", "class" => "section js-no", "order" => 3, "access" => "admin_registration"),
						"152" => array("parent" => 151, "name" => "SETTINGS_MSG", "url" => "admin_registration_settings.php", "order" => 1, "access" => "admin_registration"),
		);

		// populate footer menu
		foreach ($admin_menu as $menu_item_id => $menu_data) {
			$menu_item_id = $menu_item_id + $max_menu_item_id;
			$parent_menu_item_id = isset($menu_data["parent"]) ? $menu_data["parent"] : 0;
			if ($parent_menu_item_id) {
				$parent_menu_item_id = $parent_menu_item_id + $max_menu_item_id;
			}

			$menu_title = $menu_data["name"];
			$menu_url = $menu_data["url"];
			$menu_code = isset($menu_data["code"]) ? $menu_data["code"] : "";
			$menu_class = isset($menu_data["class"]) ? $menu_data["class"] : "";
			$menu_order = isset($menu_data["order"]) ? $menu_data["order"] : 1;
			$menu_access = isset($menu_data["access"]) ? $menu_data["access"] : "all";

			$sql  = " INSERT INTO " . $table_prefix . "menus_items ";
			$sql .= " (menu_item_id, menu_id, parent_menu_item_id, menu_code, menu_title, menu_url, menu_class, menu_order, admin_access) VALUES (";
			$sql .= $db->tosql($menu_item_id, INTEGER) . ", ";
			$sql .= $db->tosql($admin_menu_id, INTEGER) . ", ";
			$sql .= $db->tosql($parent_menu_item_id, INTEGER) . ", ";
			$sql .= $db->tosql($menu_code, TEXT) . ", ";
			$sql .= $db->tosql($menu_title, TEXT) . ", ";
			$sql .= $db->tosql($menu_url, TEXT) . ", ";
			$sql .= $db->tosql($menu_class, TEXT) . ", ";
			$sql .= $db->tosql($menu_order, INTEGER) . ", ";
			$sql .= $db->tosql($menu_access, TEXT) . ") ";
			$sqls[] = $sql;
		}
		$max_menu_item_id = $menu_item_id;

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.4.2");
	}


	if (comp_vers("4.4.3", $current_db_version) == 1)
	{
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='sliders' "; 
		$slider_block_id = get_db_value($sql);

		if ($slider_block_id) {
			// add settings to new block
			$sql = " SELECT MAX(property_id) FROM ".$table_prefix."cms_blocks_properties "; 
			$property_id = get_db_value($sql);
			$sql = " SELECT MAX(property_order) FROM ".$table_prefix."cms_blocks_properties "; 
			$property_order = get_db_value($sql);

			$property_id++; $property_order++;
			$sql = "INSERT INTO " . $table_prefix . "cms_blocks_properties (";
			$sql .= "property_id,block_id,property_order,property_name,after_control_html,property_class,control_type,variable_name,required) VALUES (";
			$sql .= $db->tosql($property_id, INTEGER).",";
			$sql .= $db->tosql($slider_block_id, INTEGER).",";
			$sql .= $db->tosql($property_order, INTEGER).",";
			$sql .= $db->tosql("TRANSITION_DELAY_MSG", TEXT).",";
			$sql .= $db->tosql("<br/>{TRANSITION_DELAY_DESC}", TEXT).",";
			$sql .= $db->tosql("", TEXT).",";
			$sql .= $db->tosql("TEXTBOX", TEXT).",";
			$sql .= $db->tosql("transition_delay", TEXT).",";
			$sql .= $db->tosql(0, INTEGER).")";
			$sqls[] = $sql;

			$property_id++; $property_order++;
			$sql = "INSERT INTO " . $table_prefix . "cms_blocks_properties (";
			$sql .= "property_id,block_id,property_order,property_name,after_control_html,property_class,control_type,variable_name,required) VALUES (";
			$sql .= $db->tosql($property_id, INTEGER).",";
			$sql .= $db->tosql($slider_block_id, INTEGER).",";
			$sql .= $db->tosql($property_order, INTEGER).",";
			$sql .= $db->tosql("TRANSITION_DURATION_MSG", TEXT).",";
			$sql .= $db->tosql("<br/>{TRANSITION_DURATION_DESC}", TEXT).",";
			$sql .= $db->tosql("", TEXT).",";
			$sql .= $db->tosql("TEXTBOX", TEXT).",";
			$sql .= $db->tosql("transition_duration", TEXT).",";
			$sql .= $db->tosql(0, INTEGER).")";
			$sqls[] = $sql;
		}

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN guest_access TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN guest_access SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN guest_access BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "shipping_types SET guest_access=user_types_all ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.4.3");
	}


	if (comp_vers("4.4.4", $current_db_version) == 1)
	{
		// add new authors module, pages, blocks and settings for it
		// get data for module
		$sql = " SELECT MAX(module_id) FROM ".$table_prefix."cms_modules "; 
		$module_id = get_db_value($sql);
		$sql = " SELECT MAX(module_order) FROM ".$table_prefix."cms_modules "; 
		$module_order = get_db_value($sql);

		// get data for pages
		$sql = " SELECT MAX(page_id) FROM ".$table_prefix."cms_pages "; 
		$page_id = get_db_value($sql);
		$page_order = 0;
		// get data for blocks
		$sql = " SELECT MAX(block_id) FROM ".$table_prefix."cms_blocks "; 
		$block_id = get_db_value($sql);
		$block_order = 0;

		// get data for page settings
		$sql = " SELECT MAX(ps_id) FROM ".$table_prefix."cms_pages_settings "; 
		$ps_id = get_db_value($sql);
		// check header and footer blocks
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='header' "; 
		$header_block_id = get_db_value($sql);
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='footer' "; 
		$footer_block_id = get_db_value($sql);

		// add new module
		$module_id++; $module_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_modules (module_id,module_order,module_code,module_name) VALUES (";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($module_order, INTEGER).",";
		$sql.= $db->tosql("authors", TEXT).",";
		$sql.= $db->tosql("AUTHORS_MSG", TEXT).")";
		$sqls[] = $sql;

		// add new blocks, pages and settings
		//-----------------------------------
		// add authors list page
		$page_id++; $page_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($page_order, INTEGER).",";
		$sql.= $db->tosql("authors_list", TEXT).",";
		$sql.= $db->tosql("{AUTHORS_MSG}: {LISTING_PAGE_MSG}", TEXT).")";
		$sqls[] = $sql;


		// add authors list block
		$block_id++; $block_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($block_order, INTEGER).",";
		$sql.= $db->tosql("authors_list", TEXT).",";
		$sql.= $db->tosql("{AUTHORS_MSG}: {LIST_MSG}", TEXT).",";
		$sql.= $db->tosql("block_authors_list.php", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		// add settings for authors listing page
		$ps_id++;
		$sql = "INSERT INTO ".$table_prefix."cms_pages_settings ";
		$sql.= " (ps_id,page_id,key_code,key_type,key_rule,layout_id,site_id) VALUES (";
		$sql.= $db->tosql($ps_id, INTEGER).",";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		if ($header_block_id) {
			$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
			$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
			$sql.= $db->tosql($ps_id, INTEGER).",";
			$sql.= $db->tosql(1, INTEGER).",";
			$sql.= $db->tosql($header_block_id, INTEGER).",";
			$sql.= $db->tosql("", TEXT).",";
			$sql.= $db->tosql(1, INTEGER).")";
			$sqls[] = $sql;
		}

		$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
		$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
		$sql.= $db->tosql($ps_id, INTEGER).",";
		$sql.= $db->tosql(3, INTEGER).",";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		if ($footer_block_id) {
			$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
			$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
			$sql.= $db->tosql($ps_id, INTEGER).",";
			$sql.= $db->tosql(5, INTEGER).",";
			$sql.= $db->tosql($footer_block_id, INTEGER).",";
			$sql.= $db->tosql("", TEXT).",";
			$sql.= $db->tosql(1, INTEGER).")";
			$sqls[] = $sql;
		}

		//-----------------------------------
		// add author details page
		$page_id++; $page_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($page_order, INTEGER).",";
		$sql.= $db->tosql("author_details", TEXT).",";
		$sql.= $db->tosql("{AUTHORS_MSG}: {DETAILS_PAGE_MSG}", TEXT).")";
		$sqls[] = $sql;


		// add author details block
		$block_id++; $block_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($block_order, INTEGER).",";
		$sql.= $db->tosql("author_details", TEXT).",";
		$sql.= $db->tosql("{AUTHOR_MSG}: {DETAILED_DESCRIPTION_MSG}", TEXT).",";
		$sql.= $db->tosql("block_author_details.php", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		// add settings for author details page
		$ps_id++;
		$sql = "INSERT INTO ".$table_prefix."cms_pages_settings ";
		$sql.= " (ps_id,page_id,key_code,key_type,key_rule,layout_id,site_id) VALUES (";
		$sql.= $db->tosql($ps_id, INTEGER).",";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		if ($header_block_id) {
			$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
			$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
			$sql.= $db->tosql($ps_id, INTEGER).",";
			$sql.= $db->tosql(1, INTEGER).",";
			$sql.= $db->tosql($header_block_id, INTEGER).",";
			$sql.= $db->tosql("", TEXT).",";
			$sql.= $db->tosql(1, INTEGER).")";
			$sqls[] = $sql;
		}

		$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
		$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
		$sql.= $db->tosql($ps_id, INTEGER).",";
		$sql.= $db->tosql(3, INTEGER).",";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		if ($footer_block_id) {
			$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
			$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
			$sql.= $db->tosql($ps_id, INTEGER).",";
			$sql.= $db->tosql(5, INTEGER).",";
			$sql.= $db->tosql($footer_block_id, INTEGER).",";
			$sql.= $db->tosql("", TEXT).",";
			$sql.= $db->tosql(1, INTEGER).")";
			$sqls[] = $sql;
		}

		//-----------------------------------
		// add author articles page
		$page_id++; $page_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($page_order, INTEGER).",";
		$sql.= $db->tosql("author_articles", TEXT).",";
		$sql.= $db->tosql("{AUTHOR_MSG}: {ARTICLES_TITLE}", TEXT).")";
		$sqls[] = $sql;

		// add author articles block
		$block_id++; $block_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($block_order, INTEGER).",";
		$sql.= $db->tosql("author_articles", TEXT).",";
		$sql.= $db->tosql("{AUTHOR_MSG}: {ARTICLES_TITLE}", TEXT).",";
		$sql.= $db->tosql("block_author_articles.php", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		// add settings for profiles home page
		$ps_id++;
		$sql = "INSERT INTO ".$table_prefix."cms_pages_settings ";
		$sql.= " (ps_id,page_id,key_code,key_type,key_rule,layout_id,site_id) VALUES (";
		$sql.= $db->tosql($ps_id, INTEGER).",";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		if ($header_block_id) {
			$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
			$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
			$sql.= $db->tosql($ps_id, INTEGER).",";
			$sql.= $db->tosql(1, INTEGER).",";
			$sql.= $db->tosql($header_block_id, INTEGER).",";
			$sql.= $db->tosql("", TEXT).",";
			$sql.= $db->tosql(1, INTEGER).")";
			$sqls[] = $sql;
		}

		$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
		$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
		$sql.= $db->tosql($ps_id, INTEGER).",";
		$sql.= $db->tosql(3, INTEGER).",";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		if ($footer_block_id) {
			$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
			$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
			$sql.= $db->tosql($ps_id, INTEGER).",";
			$sql.= $db->tosql(5, INTEGER).",";
			$sql.= $db->tosql($footer_block_id, INTEGER).",";
			$sql.= $db->tosql("", TEXT).",";
			$sql.= $db->tosql(1, INTEGER).")";
			$sqls[] = $sql;
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.4.4");
	}


	if (comp_vers("4.4.5", $current_db_version) == 1)
	{
		// adding properties tp authors_list and author_articles blocks
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='authors_list'"; 
		$authors_list_block_id = get_db_value($sql);
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='author_articles'"; 
		$author_articles_block_id = get_db_value($sql);
		$sql = " SELECT MAX(property_id) FROM ".$table_prefix."cms_blocks_properties "; 
		$property_id = get_db_value($sql);

		if ($authors_list_block_id) {
			$property_order = 0;
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $authors_list_block_id, $property_order, 'RECORDS_PER_PAGE_MSG', 'TEXTBOX', NULL, NULL, 'recs', '50', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $authors_list_block_id, $property_order, 'NUMBER_OF_COLUMNS_MSG', 'TEXTBOX', NULL, NULL, 'cols', '2', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $authors_list_block_id, $property_order, 'IMAGE_TINY_MSG', 'CHECKBOX', NULL, NULL, 'image_tiny', '0', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $authors_list_block_id, $property_order, 'IMAGE_SMALL_MSG', 'CHECKBOX', NULL, NULL, 'image_small', '0', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $authors_list_block_id, $property_order, 'IMAGE_LARGE_MSG', 'CHECKBOX', NULL, NULL, 'image_large', '0', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $authors_list_block_id, $property_order, 'IMAGE_SUPER_MSG', 'CHECKBOX', NULL, NULL, 'image_super', '0', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $authors_list_block_id, $property_order, 'SHORT_DESCRIPTION_MSG', 'CHECKBOX', NULL, NULL, 'short_description', '0', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $authors_list_block_id, $property_order, 'FULL_DESCRIPTION_MSG', 'CHECKBOX', NULL, NULL, 'full_description', '0', 0)";
		}

		if ($author_articles_block_id) {
			$property_order = 0;
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $author_articles_block_id, $property_order, 'RECORDS_PER_PAGE_MSG', 'TEXTBOX', NULL, NULL, 'recs', '50', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $author_articles_block_id, $property_order, 'NUMBER_OF_COLUMNS_MSG', 'TEXTBOX', NULL, NULL, 'cols', '2', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $author_articles_block_id, $property_order, 'IMAGE_TINY_MSG', 'CHECKBOX', NULL, NULL, 'image_tiny', '0', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $author_articles_block_id, $property_order, 'IMAGE_SMALL_MSG', 'CHECKBOX', NULL, NULL, 'image_small', '0', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $author_articles_block_id, $property_order, 'IMAGE_LARGE_MSG', 'CHECKBOX', NULL, NULL, 'image_large', '0', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $author_articles_block_id, $property_order, 'IMAGE_SUPER_MSG', 'CHECKBOX', NULL, NULL, 'image_super', '0', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $author_articles_block_id, $property_order, 'SHORT_DESCRIPTION_MSG', 'CHECKBOX', NULL, NULL, 'short_description', '0', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $author_articles_block_id, $property_order, 'FULL_DESCRIPTION_MSG', 'CHECKBOX', NULL, NULL, 'full_description', '0', 0)";
		}

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "authors ADD COLUMN date_added DATETIME ",
			"postgre" => "ALTER TABLE " . $table_prefix . "authors ADD COLUMN date_added TIMESTAMP ",
			"access"  => "ALTER TABLE " . $table_prefix . "authors ADD COLUMN date_added DATETIME ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "authors ADD COLUMN date_updated DATETIME ",
			"postgre" => "ALTER TABLE " . $table_prefix . "authors ADD COLUMN date_updated TIMESTAMP ",
			"access"  => "ALTER TABLE " . $table_prefix . "authors ADD COLUMN date_updated DATETIME ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "authors ADD COLUMN sites_all TINYINT NOT NULL DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "authors ADD COLUMN sites_all SMALLINT NOT NULL DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "authors ADD COLUMN sites_all BYTE NOT NULL ",
		);
		$sqls[] = $sql_types[$db_type];
		$sql  = " UPDATE " . $table_prefix . "authors SET sites_all=1, ";
		$sql .= " date_added=" . $db->tosql(va_time(), DATETIME) . ",";
		$sql .= " date_updated=" . $db->tosql(va_time(), DATETIME);
		$sqls[] = $sql;

		$mysql_sql  = "CREATE TABLE " . $table_prefix . "authors_sites (";
		$mysql_sql .= "  `author_id` INT(11) NOT NULL default '0',";
		$mysql_sql .= "  `site_id` INT(11) NOT NULL default '0'";
		$mysql_sql .= "  ,PRIMARY KEY (author_id,site_id))";

		$postgre_sql  = "CREATE TABLE " . $table_prefix . "authors_sites (";
		$postgre_sql .= "  author_id INT4 NOT NULL default '0',";
		$postgre_sql .= "  site_id INT4 NOT NULL default '0'";
		$postgre_sql .= "  ,PRIMARY KEY (author_id,site_id))";

		$access_sql  = "CREATE TABLE " . $table_prefix . "authors_sites (";
		$access_sql .= "  [author_id] INTEGER NOT NULL,";
		$access_sql .= "  [site_id] INTEGER NOT NULL";
		$access_sql .= "  ,PRIMARY KEY (author_id,site_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.4.5");
	}

	if (comp_vers("4.4.6", $current_db_version) == 1)
	{
		// add new albums module, pages, blocks and settings for it
		// get authors module to add albums page and blocks
		$sql = " SELECT module_id FROM ".$table_prefix."cms_modules WHERE module_code='authors' "; 
		$module_id = get_db_value($sql);

		// start from 3 for page order
		$page_order = 3;
		// get data for blocks
		$sql = " SELECT MAX(block_id) FROM ".$table_prefix."cms_blocks "; 
		$block_id = get_db_value($sql);
		$block_order = 0;

		// get data for page settings
		$sql = " SELECT MAX(ps_id) FROM ".$table_prefix."cms_pages_settings "; 
		$ps_id = get_db_value($sql);
		// check header and footer blocks
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='header' "; 
		$header_block_id = get_db_value($sql);
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='footer' "; 
		$footer_block_id = get_db_value($sql);

		// add new blocks, pages and settings
		//-----------------------------------
		// add albums list page
		$page_id++; $page_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($page_order, INTEGER).",";
		$sql.= $db->tosql("albums_list", TEXT).",";
		$sql.= $db->tosql("{ALBUMS_MSG}: {LISTING_PAGE_MSG}", TEXT).")";
		$sqls[] = $sql;


		// add albums list block
		$block_id++; $block_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($block_order, INTEGER).",";
		$sql.= $db->tosql("albums_list", TEXT).",";
		$sql.= $db->tosql("{ALBUMS_MSG}: {LIST_MSG}", TEXT).",";
		$sql.= $db->tosql("block_albums_list.php", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		// add settings for albums listing page
		$ps_id++;
		$sql = "INSERT INTO ".$table_prefix."cms_pages_settings ";
		$sql.= " (ps_id,page_id,key_code,key_type,key_rule,layout_id,site_id) VALUES (";
		$sql.= $db->tosql($ps_id, INTEGER).",";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		if ($header_block_id) {
			$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
			$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
			$sql.= $db->tosql($ps_id, INTEGER).",";
			$sql.= $db->tosql(1, INTEGER).",";
			$sql.= $db->tosql($header_block_id, INTEGER).",";
			$sql.= $db->tosql("", TEXT).",";
			$sql.= $db->tosql(1, INTEGER).")";
			$sqls[] = $sql;
		}

		$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
		$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
		$sql.= $db->tosql($ps_id, INTEGER).",";
		$sql.= $db->tosql(3, INTEGER).",";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		if ($footer_block_id) {
			$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
			$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
			$sql.= $db->tosql($ps_id, INTEGER).",";
			$sql.= $db->tosql(5, INTEGER).",";
			$sql.= $db->tosql($footer_block_id, INTEGER).",";
			$sql.= $db->tosql("", TEXT).",";
			$sql.= $db->tosql(1, INTEGER).")";
			$sqls[] = $sql;
		}

		//-----------------------------------
		// add album details page
		$page_id++; $page_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($page_order, INTEGER).",";
		$sql.= $db->tosql("album_details", TEXT).",";
		$sql.= $db->tosql("{ALBUMS_MSG}: {DETAILS_PAGE_MSG}", TEXT).")";
		$sqls[] = $sql;


		// add album details block
		$block_id++; $block_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($block_order, INTEGER).",";
		$sql.= $db->tosql("album_details", TEXT).",";
		$sql.= $db->tosql("{ALBUM_MSG}: {DETAILED_DESCRIPTION_MSG}", TEXT).",";
		$sql.= $db->tosql("block_album_details.php", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;
		$album_details_block_id = $block_id;

		// add album articles block
		$block_id++; $block_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($block_order, INTEGER).",";
		$sql.= $db->tosql("album_articles", TEXT).",";
		$sql.= $db->tosql("{ALBUM_MSG}: {ARTICLES_TITLE}", TEXT).",";
		$sql.= $db->tosql("block_album_articles.php", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;
		$album_articles_block_id = $block_id;

		// add settings for album details page
		$ps_id++;
		$sql = "INSERT INTO ".$table_prefix."cms_pages_settings ";
		$sql.= " (ps_id,page_id,key_code,key_type,key_rule,layout_id,site_id) VALUES (";
		$sql.= $db->tosql($ps_id, INTEGER).",";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		if ($header_block_id) {
			$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
			$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
			$sql.= $db->tosql($ps_id, INTEGER).",";
			$sql.= $db->tosql(1, INTEGER).",";
			$sql.= $db->tosql($header_block_id, INTEGER).",";
			$sql.= $db->tosql("", TEXT).",";
			$sql.= $db->tosql(1, INTEGER).")";
			$sqls[] = $sql;
		}

		$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
		$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
		$sql.= $db->tosql($ps_id, INTEGER).",";
		$sql.= $db->tosql(3, INTEGER).",";
		$sql.= $db->tosql($album_details_block_id, INTEGER).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
		$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
		$sql.= $db->tosql($ps_id, INTEGER).",";
		$sql.= $db->tosql(3, INTEGER).",";
		$sql.= $db->tosql($album_articles_block_id, INTEGER).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql(2, INTEGER).")";
		$sqls[] = $sql;

		if ($footer_block_id) {
			$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
			$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
			$sql.= $db->tosql($ps_id, INTEGER).",";
			$sql.= $db->tosql(5, INTEGER).",";
			$sql.= $db->tosql($footer_block_id, INTEGER).",";
			$sql.= $db->tosql("", TEXT).",";
			$sql.= $db->tosql(1, INTEGER).")";
			$sqls[] = $sql;
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.4.6");
	}


	if (comp_vers("4.4.7", $current_db_version) == 1)
	{
		// adding properties tp albums_list and album_articles blocks
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='album_list'"; 
		$albums_list_block_id = get_db_value($sql);
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='album_articles'"; 
		$album_articles_block_id = get_db_value($sql);
		$sql = " SELECT MAX(property_id) FROM ".$table_prefix."cms_blocks_properties "; 
		$property_id = get_db_value($sql);

		if ($albums_list_block_id) {
			$property_order = 0;
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $albums_list_block_id, $property_order, 'RECORDS_PER_PAGE_MSG', 'TEXTBOX', NULL, NULL, 'recs', '50', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $albums_list_block_id, $property_order, 'NUMBER_OF_COLUMNS_MSG', 'TEXTBOX', NULL, NULL, 'cols', '2', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $albums_list_block_id, $property_order, 'IMAGE_TINY_MSG', 'CHECKBOX', NULL, NULL, 'image_tiny', '0', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $albums_list_block_id, $property_order, 'IMAGE_SMALL_MSG', 'CHECKBOX', NULL, NULL, 'image_small', '0', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $albums_list_block_id, $property_order, 'IMAGE_LARGE_MSG', 'CHECKBOX', NULL, NULL, 'image_large', '0', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $albums_list_block_id, $property_order, 'IMAGE_SUPER_MSG', 'CHECKBOX', NULL, NULL, 'image_super', '0', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $albums_list_block_id, $property_order, 'SHORT_DESCRIPTION_MSG', 'CHECKBOX', NULL, NULL, 'short_description', '0', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $albums_list_block_id, $property_order, 'FULL_DESCRIPTION_MSG', 'CHECKBOX', NULL, NULL, 'full_description', '0', 0)";
		}

		if ($album_articles_block_id) {
			$property_order = 0;
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $album_articles_block_id, $property_order, 'RECORDS_PER_PAGE_MSG', 'TEXTBOX', NULL, NULL, 'recs', '50', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $album_articles_block_id, $property_order, 'NUMBER_OF_COLUMNS_MSG', 'TEXTBOX', NULL, NULL, 'cols', '2', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $album_articles_block_id, $property_order, 'IMAGE_TINY_MSG', 'CHECKBOX', NULL, NULL, 'image_tiny', '0', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $album_articles_block_id, $property_order, 'IMAGE_SMALL_MSG', 'CHECKBOX', NULL, NULL, 'image_small', '0', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $album_articles_block_id, $property_order, 'IMAGE_LARGE_MSG', 'CHECKBOX', NULL, NULL, 'image_large', '0', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $album_articles_block_id, $property_order, 'IMAGE_SUPER_MSG', 'CHECKBOX', NULL, NULL, 'image_super', '0', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $album_articles_block_id, $property_order, 'SHORT_DESCRIPTION_MSG', 'CHECKBOX', NULL, NULL, 'short_description', '0', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $album_articles_block_id, $property_order, 'FULL_DESCRIPTION_MSG', 'CHECKBOX', NULL, NULL, 'full_description', '0', 0)";
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.4.7");
	}


	if (comp_vers("4.4.8", $current_db_version) == 1)
	{
		// get authors module to add author breadcrumb block 
		$sql = " SELECT module_id FROM ".$table_prefix."cms_modules WHERE module_code='authors' "; 
		$module_id = get_db_value($sql);

		$sql = " SELECT MAX(block_id) FROM ".$table_prefix."cms_blocks "; 
		$block_id = get_db_value($sql);

		$block_id++; 
		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql(1, INTEGER).",";
		$sql.= $db->tosql("author_breadcrumb", TEXT).",";
		$sql.= $db->tosql("{AUTHOR_MSG}: {BREADCRUMB_MSG}", TEXT).",";
		$sql.= $db->tosql("block_author_breadcrumb.php", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.4.8");
	}


	if (comp_vers("4.4.9", $current_db_version) == 1)
	{
		$sql = " SELECT layout_id FROM " . $table_prefix . "cms_layouts WHERE layout_id>=1 AND layout_id<=4";
		$db->query($sql);
		while($db->next_record()) {
			$layout_id = $db->f("layout_id");
			$sqls[] ="INSERT INTO " .$table_prefix."cms_frames (layout_id,frame_name,tag_name) VALUES ($layout_id, 'NAVIGATION_BAR_MSG', 'frame_bar')";
		}
		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.4.9");
	}

	if (comp_vers("4.5", $current_db_version) == 1)
	{
		$sqls[] = " ALTER TABLE " . $table_prefix . "sites ADD COLUMN short_name VARCHAR(16) ";
		$sqls[] = " ALTER TABLE " . $table_prefix . "cms_pages_settings ADD COLUMN page_class VARCHAR(64) ";
		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.5");
	}

	if (comp_vers("4.5.1", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "items ADD COLUMN highlights TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "items ADD COLUMN highlights TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "items ADD COLUMN highlights LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "items SET highlights=features ";
		$sqls[] = "UPDATE " . $table_prefix . "items SET features=NULL ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.5.1");
	}

	if (comp_vers("4.5.2", $current_db_version) == 1)
	{
		// check if we need add html_template field to cms_blocks table
		$html_template_field = false;
		$fields = $db->get_fields($table_prefix."cms_blocks");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "html_template") {
				$html_template_field = true;
			}
		}
		// end field check

		$sqls[] = "ALTER TABLE " . $table_prefix . "cms_blocks ADD COLUMN layout_type VARCHAR(32) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "cms_blocks ADD COLUMN layout_template VARCHAR(64) ";
		if (!$html_template_field) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "cms_blocks ADD COLUMN html_template VARCHAR(64) ";
		}

		$sqls[] = "ALTER TABLE " . $table_prefix . "cms_pages_blocks ADD COLUMN layout_type VARCHAR(32) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "cms_pages_blocks ADD COLUMN layout_template VARCHAR(64) ";

		$sqls[] = "ALTER TABLE " . $table_prefix . "layouts ADD COLUMN block_default_template VARCHAR(64) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "layouts ADD COLUMN block_area_template VARCHAR(64) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "layouts ADD COLUMN block_breadcrumb_template VARCHAR(64) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.5.2");
	}


	if (comp_vers("4.5.3", $current_db_version) == 1)
	{

		$sqls[] = "ALTER TABLE " . $table_prefix . "cms_blocks ADD COLUMN css_class VARCHAR(128) ";

		$sql_types = array(
			"mysql"   => "UPDATE ".$table_prefix."cms_blocks SET css_class=CONCAT('bk-', REPLACE(block_code, '_', '-'))",
			"postgre"   => "UPDATE ".$table_prefix."cms_blocks SET css_class='bk-' || REPLACE(block_code, '_', '-')",
			"access"   => "UPDATE ".$table_prefix."cms_blocks SET css_class='bk-' & REPLACE(block_code, '_', '-')",
		);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "ALTER TABLE " . $table_prefix . "countries ADD COLUMN full_name VARCHAR(64) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.5.3");
	}

	if (comp_vers("4.5.4", $current_db_version) == 1)
	{
		$sqls[] = "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN property_class VARCHAR(64) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "order_custom_properties ADD COLUMN property_class VARCHAR(64) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "user_profile_properties ADD COLUMN property_class VARCHAR(64) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "registration_custom_properties ADD COLUMN property_class VARCHAR(64) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "support_custom_properties ADD COLUMN property_class VARCHAR(64) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.5.4");
	}

	if (comp_vers("4.5.5", $current_db_version) == 1)
	{
		$sqls[] = " DELETE FROM ".$table_prefix."cms_blocks_properties where variable_name='use_tabs' ";
		$sqls[] = " UPDATE ".$table_prefix."menus_items SET menu_title='FORM_SECTIONS_MSG' WHERE menu_title='PROFILE_SECTION_MENU_MSG' ";
		$sqls[] = " UPDATE ".$table_prefix."menus_items SET menu_url='admin_profiles.php' WHERE menu_url='admin_profiles_list.php' ";

		$sqls[] = "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN admin_order_class VARCHAR(64) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN user_order_class VARCHAR(64) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "support_statuses ADD COLUMN admin_ticket_class VARCHAR(64) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "support_statuses ADD COLUMN user_ticket_class VARCHAR(64) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN admin_order_class VARCHAR(64) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN user_order_class VARCHAR(64) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.5.5");
	}

	if (comp_vers("4.5.6", $current_db_version) == 1)
	{
		$sqls[] = "ALTER TABLE " . $table_prefix . "shipping_modules ADD COLUMN admin_order_class VARCHAR(64) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "shipping_modules ADD COLUMN user_order_class VARCHAR(64) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.5.6");
	}

	if (comp_vers("4.6", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "items ADD COLUMN is_new TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "items ADD COLUMN is_new SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "items ADD COLUMN is_new BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "items SET is_new=0 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "items ADD COLUMN special_order INT(11) DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "items ADD COLUMN special_order INT4 DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "items ADD COLUMN special_order INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "items SET special_order=item_order ";
		$sqls[] = " CREATE INDEX ".$table_prefix."items_special_order ON ".$table_prefix."items (special_order) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "articles ADD COLUMN hot_order INT(11) DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "articles ADD COLUMN hot_order INT4 DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "articles ADD COLUMN hot_order INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "articles SET hot_order=article_order ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.6");
	}


	if (comp_vers("4.6.1", $current_db_version) == 1)
	{
		$fields = $db->get_fields($table_prefix."layouts");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "block_detault_template") {
				if ($db->DBType == "mysql") {
					$sql = "ALTER TABLE ".$table_prefix."layouts CHANGE COLUMN block_detault_template block_default_template VARCHAR(64)";
				} else if ($db->DBType == "access") {
					$sql = "ALTER TABLE ".$table_prefix."layouts RENAME COLUMN block_detault_template TO block_default_template";
				} else {
					$sql = "ALTER TABLE ".$table_prefix."layouts RENAME COLUMN block_detault_template TO block_default_template";
				}
				$sqls[] = $sql;
			}
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.6.1");
	}

	if (comp_vers("4.6.2", $current_db_version) == 1)
	{
		$sqls[] = "UPDATE " . $table_prefix . "menus_items SET menu_url='products_list.php' WHERE menu_url='products.php' ";
		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.6.2");
	}


	if (comp_vers("4.6.3", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_custom_properties ADD COLUMN sites_all TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_custom_properties ADD COLUMN sites_all SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "order_custom_properties ADD COLUMN sites_all BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "order_custom_properties SET sites_all=0 ";

		$mysql_sql  = "CREATE TABLE " . $table_prefix . "order_custom_sites (";
		$mysql_sql .= "  `property_id` INT(11) NOT NULL default '0',";
		$mysql_sql .= "  `site_id` INT(11) NOT NULL default '0'";
		$mysql_sql .= "  ,PRIMARY KEY (property_id,site_id))";

		$postgre_sql  = "CREATE TABLE " . $table_prefix . "order_custom_sites (";
		$postgre_sql .= "  property_id INT4 NOT NULL default '0',";
		$postgre_sql .= "  site_id INT4 NOT NULL default '0'";
		$postgre_sql .= "  ,PRIMARY KEY (property_id,site_id))";

		$access_sql  = "CREATE TABLE " . $table_prefix . "order_custom_sites (";
		$access_sql .= "  [property_id] INTEGER NOT NULL,";
		$access_sql .= "  [site_id] INTEGER NOT NULL";
		$access_sql .= "  ,PRIMARY KEY (property_id,site_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		$sql = " SELECT property_id, site_id FROM " . $table_prefix . "order_custom_properties ";
		$db->query($sql);
		while ($db->next_record()) {
			$property_id = $db->f("property_id");
			$site_id = $db->f("site_id");
			$sql  = " INSERT INTO " . $table_prefix . "order_custom_sites (property_id, site_id) VALUES (";
			$sql .= $db->tosql($property_id, INTEGER) . ", ";
			$sql .= $db->tosql($site_id, INTEGER) . ") ";
			$sqls[] = $sql;
		}

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_custom_properties ADD COLUMN shipping_module_id INT(11) DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_custom_properties ADD COLUMN shipping_module_id INT4 DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "order_custom_properties ADD COLUMN shipping_module_id INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_custom_properties ADD COLUMN shipping_type_id INT(11) DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_custom_properties ADD COLUMN shipping_type_id INT4 DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "order_custom_properties ADD COLUMN shipping_type_id INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.6.3");
	}


	if (comp_vers("4.6.4", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN items_rule TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN items_rule SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN items_rule BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "coupons SET items_rule=1 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN zero_cost_rule TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN zero_cost_rule SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN zero_cost_rule BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "shipping_types SET zero_cost_rule=0 ";

		$sqls[] = "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN zero_cost_desc VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN zero_cost_text VARCHAR(255) ";

		$sqls[] = "ALTER TABLE " . $table_prefix . "categories ADD COLUMN list_class VARCHAR(64) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.6.4");
	}


	if (comp_vers("5.0", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN item_types_all TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN item_types_all SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN item_types_all BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "features_groups SET item_types_all=1 ";

		$mysql_sql  = "CREATE TABLE " . $table_prefix . "features_item_types (
      `group_id` INT(11) NOT NULL,
      `item_type_id` INT(11) NOT NULL,
      PRIMARY KEY (group_id,item_type_id))";

		$postgre_sql  = "CREATE TABLE " . $table_prefix . "features_item_types (
      group_id INT4 NOT NULL,
      item_type_id INT4 NOT NULL,
      PRIMARY KEY (group_id,item_type_id))";

		$access_sql  = "CREATE TABLE " . $table_prefix . "features_item_types (
      [group_id] INTEGER NOT NULL,
      [item_type_id] INTEGER NOT NULL,
      PRIMARY KEY (group_id,item_type_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN feature_order INT(11) DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN feature_order INT4 DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN feature_order INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN control_type VARCHAR(16) ";
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN required TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN required SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN required BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "features_default SET required=0 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN show_as_group TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN show_as_group SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN show_as_group BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "features_default SET show_as_group=1 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN show_on_details TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN show_on_details SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN show_on_details BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "features_default SET show_on_details=0 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN show_on_basket TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN show_on_basket SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN show_on_basket BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "features_default SET show_on_basket=0 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN show_on_checkout TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN show_on_checkout SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN show_on_checkout BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "features_default SET show_on_checkout=0 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN show_on_invoice TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN show_on_invoice SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN show_on_invoice BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "features_default SET show_on_invoice=0 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features ADD COLUMN feature_order INT(11) DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features ADD COLUMN feature_order INT4 DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "features ADD COLUMN feature_order INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features ADD COLUMN default_feature_id INT(11) DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features ADD COLUMN default_feature_id INT4 DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "features ADD COLUMN default_feature_id INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "CREATE INDEX ".$table_prefix."features_default_feature_id ON ".$table_prefix."features (default_feature_id)";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features ADD COLUMN value_id INT(11) DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features ADD COLUMN value_id INT4 DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "features ADD COLUMN value_id INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "CREATE INDEX ".$table_prefix."features_value_id ON ".$table_prefix."features (value_id)";

		$sqls[] = "ALTER TABLE " . $table_prefix . "features ADD COLUMN value_code VARCHAR(16) ";
		$sqls[] = "CREATE INDEX ".$table_prefix."features_value_code ON ".$table_prefix."features (value_code)";

		$mysql_sql  = "CREATE TABLE ".$table_prefix."features_values (
      `value_id` INT(11) NOT NULL AUTO_INCREMENT,
      `feature_id` INT(11) default '0',
      `value_order` INT(11) default '1',
      `value_code` VARCHAR(32),
      `value_desc` TEXT,
      `hide_value` TINYINT default '1',
      `is_default_value` TINYINT default '0'
      ,PRIMARY KEY (value_id)
      ) DEFAULT CHARACTER SET=utf8mb4 ";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."features_values START 1";
		}
		$postgre_sql = "CREATE TABLE ".$table_prefix."features_values (
      value_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."features_values'),
      feature_id INT4 default '0',
      value_order INT4 default '1',
      value_code VARCHAR(32),
      value_desc TEXT,
      hide_value SMALLINT default '1',
      is_default_value SMALLINT default '0'
      ,PRIMARY KEY (value_id))";

		$access_sql = "CREATE TABLE ".$table_prefix."features_values (
      [value_id]  COUNTER  NOT NULL,
      [feature_id] INTEGER,
      [value_order] INTEGER,
      [value_code] VARCHAR(32),
      [value_desc] LONGTEXT,
      [hide_value] BYTE,
      [is_default_value] BYTE
      ,PRIMARY KEY (value_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		// hide buttons fields
		$fields = $db->get_fields($table_prefix."items");
		$hide_fields = array(
			"hide_view_list" => false, "hide_view_details" => false, "hide_view_table" => false, "hide_view_grid" => false,
			"hide_checkout_list" => false, "hide_checkout_details" => false, "hide_checkout_table" => false, "hide_checkout_grid" => false,
			"hide_wishlist_list" => false, "hide_wishlist_details" => false, "hide_wishlist_table" => false, "hide_wishlist_grid" => false,
			"hide_more_list" => false, "hide_more_table" => false, "hide_more_grid" => false,
			"hide_shipping_details" => false,
		);
		foreach ($fields as $id => $field_info) {
			$field_name = $field_info["name"];
			if (isset($hide_fields[$field_name])) {
				$hide_fields[$field_name] = true;
			}
		}
		foreach ($hide_fields as $field_name => $field_exists) {
			if (!$field_exists) {
				if ($db->DBType == "mysql") {
					$sql = "ALTER TABLE ".$table_prefix."items ADD COLUMN ".$field_name." TINYINT ";
				} else if ($db->DBType == "access") {
					$sql = "ALTER TABLE ".$table_prefix."items ADD COLUMN ".$field_name." SMALLINT ";
				} else {
					$sql = "ALTER TABLE ".$table_prefix."items ADD COLUMN ".$field_name." BYTE ";
				}
				$sqls[] = $sql;
			}
		}
		// end field check

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.0");
	}

?> 