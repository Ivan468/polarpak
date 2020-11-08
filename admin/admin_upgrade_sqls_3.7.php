<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_upgrade_sqls_3.7.php                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	check_admin_security("system_upgrade");

	if (comp_vers("3.6.1", $current_db_version) == 1)
	{
		$sqls[] = "ALTER TABLE " . $table_prefix . "ads_items ADD COLUMN currency_code VARCHAR(4) ";
		$sqls[] = "UPDATE " . $table_prefix . "ads_items SET currency_code='' ";
		$sqls[] = "CREATE INDEX " . $table_prefix . "ads_items_currency_code ON " . $table_prefix . "ads_items (currency_code) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.1");
	}

	if (comp_vers("3.6.2", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "items MODIFY COLUMN packages_number DOUBLE(16,4) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "items ALTER COLUMN packages_number TYPE FLOAT4",
			"access"  => "ALTER TABLE " . $table_prefix . "items ALTER COLUMN packages_number FLOAT",
			"db2"     => "ALTER TABLE " . $table_prefix . "items ALTER COLUMN packages_number SET DATA TYPE DOUBLE"
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.2");
	}


	if (comp_vers("3.6.3", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "items ADD COLUMN tax_id INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "items ADD COLUMN tax_id INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "items ADD COLUMN tax_id INTEGER ",
			"db2"     => "ALTER TABLE " . $table_prefix . "items ADD COLUMN tax_id INTEGER DEFAULT 0"
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "tax_rates ADD COLUMN tax_type TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "tax_rates ADD COLUMN tax_type SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "tax_rates ADD COLUMN tax_type BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "tax_rates ADD COLUMN tax_type SMALLINT DEFAULT 1"
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "tax_rates SET tax_type=1 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "tax_rates ADD COLUMN show_type TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "tax_rates ADD COLUMN show_type SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "tax_rates ADD COLUMN show_type BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "tax_rates ADD COLUMN show_type SMALLINT DEFAULT 0"
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.3");
	}

	if (comp_vers("3.6.4", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "orders_items ADD COLUMN tax_id INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "orders_items ADD COLUMN tax_id INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "orders_items ADD COLUMN tax_id INTEGER ",
			"db2"     => "ALTER TABLE " . $table_prefix . "orders_items ADD COLUMN tax_id INTEGER DEFAULT 0"
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.4");
	}

	if (comp_vers("3.6.5", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "tax_rates ADD COLUMN fixed_amount DOUBLE(16,2) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "tax_rates ADD COLUMN fixed_amount FLOAT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "tax_rates ADD COLUMN fixed_amount FLOAT",
			"db2"     => "ALTER TABLE " . $table_prefix . "tax_rates ADD COLUMN fixed_amount DOUBLE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "tax_rates ADD COLUMN shipping_fixed_amount DOUBLE(16,2) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "tax_rates ADD COLUMN shipping_fixed_amount FLOAT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "tax_rates ADD COLUMN shipping_fixed_amount FLOAT",
			"db2"     => "ALTER TABLE " . $table_prefix . "tax_rates ADD COLUMN shipping_fixed_amount DOUBLE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "tax_rates_items ADD COLUMN fixed_amount DOUBLE(16,2) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "tax_rates_items ADD COLUMN fixed_amount FLOAT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "tax_rates_items ADD COLUMN fixed_amount FLOAT",
			"db2"     => "ALTER TABLE " . $table_prefix . "tax_rates_items ADD COLUMN fixed_amount DOUBLE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "orders_taxes ADD COLUMN tax_type TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "orders_taxes ADD COLUMN tax_type SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "orders_taxes ADD COLUMN tax_type BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "orders_taxes ADD COLUMN tax_type SMALLINT DEFAULT 1"
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "orders_taxes SET tax_type=1 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "orders_taxes ADD COLUMN show_type TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "orders_taxes ADD COLUMN show_type SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "orders_taxes ADD COLUMN show_type BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "orders_taxes ADD COLUMN show_type SMALLINT DEFAULT 0"
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "orders_taxes ADD COLUMN fixed_amount DOUBLE(16,2) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "orders_taxes ADD COLUMN fixed_amount FLOAT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "orders_taxes ADD COLUMN fixed_amount FLOAT",
			"db2"     => "ALTER TABLE " . $table_prefix . "orders_taxes ADD COLUMN fixed_amount DOUBLE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "orders_taxes ADD COLUMN shipping_fixed_amount DOUBLE(16,2) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "orders_taxes ADD COLUMN shipping_fixed_amount FLOAT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "orders_taxes ADD COLUMN shipping_fixed_amount FLOAT",
			"db2"     => "ALTER TABLE " . $table_prefix . "orders_taxes ADD COLUMN shipping_fixed_amount DOUBLE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "orders_items_taxes ADD COLUMN fixed_amount DOUBLE(16,2) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "orders_items_taxes ADD COLUMN fixed_amount FLOAT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "orders_items_taxes ADD COLUMN fixed_amount FLOAT",
			"db2"     => "ALTER TABLE " . $table_prefix . "orders_items_taxes ADD COLUMN fixed_amount DOUBLE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN credit_note_action TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN credit_note_action SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN credit_note_action BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN credit_note_action SMALLINT DEFAULT 0"
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "order_statuses SET credit_note_action=0 ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.5");
	}

	if (comp_vers("3.6.6", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "orders_items MODIFY COLUMN packages_number DOUBLE(16,4) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "orders_items ALTER COLUMN packages_number TYPE FLOAT4",
			"access"  => "ALTER TABLE " . $table_prefix . "orders_items ALTER COLUMN packages_number FLOAT",
			"db2"     => "ALTER TABLE " . $table_prefix . "orders_items ALTER COLUMN packages_number SET DATA TYPE DOUBLE"
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.6");
	}

	if (comp_vers("3.6.7", $current_db_version) == 1)
	{
		$sqls[] = "UPDATE " . $table_prefix . "tax_rates SET show_type=1 ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.7");
	}

	if (comp_vers("3.6.8", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "ads_items ADD COLUMN publish_price DOUBLE(16,2) DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "ads_items ADD COLUMN publish_price FLOAT4 DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "ads_items ADD COLUMN publish_price FLOAT",
			"db2"     => "ALTER TABLE " . $table_prefix . "ads_items ADD COLUMN publish_price DOUBLE DEFAULT 0",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "ads_items ADD COLUMN is_paid TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "ads_items ADD COLUMN is_paid SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "ads_items ADD COLUMN is_paid BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "ads_items ADD COLUMN is_paid SMALLINT DEFAULT 0"
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "ads_items SET is_paid=1 ";
		$sqls[] = "CREATE INDEX " . $table_prefix . "ads_items_is_paid ON " . $table_prefix . "ads_items (is_paid) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.8");
	}

	if (comp_vers("3.6.9", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "footer_links MODIFY COLUMN menu_url VARCHAR(255) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "footer_links ALTER COLUMN menu_url TYPE VARCHAR(255) ",
			"access"  => "ALTER TABLE " . $table_prefix . "footer_links ALTER COLUMN menu_url VARCHAR(255) ",
			"db2"     => "ALTER TABLE " . $table_prefix . "footer_links ALTER COLUMN menu_url SET DATA TYPE VARCHAR(255) "
		);
		$sqls[] = $sql_types[$db_type];
		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.9");
	}

	if (comp_vers("3.6.10", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN show_on_details TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN show_on_details SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN show_on_details BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN show_on_details SMALLINT DEFAULT 1"
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "features_groups SET show_on_details=1 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN show_on_basket TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN show_on_basket SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN show_on_basket BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN show_on_basket SMALLINT DEFAULT 0"
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN show_on_checkout TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN show_on_checkout SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN show_on_checkout BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN show_on_checkout SMALLINT DEFAULT 0"
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN show_on_invoice TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN show_on_invoice SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN show_on_invoice BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN show_on_invoice SMALLINT DEFAULT 0"
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_as_group TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_as_group SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_as_group BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_as_group SMALLINT DEFAULT 1"
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "features SET show_as_group=1 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_on_details TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_on_details SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_on_details BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_on_details SMALLINT DEFAULT 0"
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_on_basket TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_on_basket SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_on_basket BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_on_basket SMALLINT DEFAULT 0"
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_on_checkout TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_on_checkout SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_on_checkout BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_on_checkout SMALLINT DEFAULT 0"
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_on_invoice TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_on_invoice SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_on_invoice BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_on_invoice SMALLINT DEFAULT 0"
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.10");
	}


	if (comp_vers("3.6.11", $current_db_version) == 1)
	{
		// new va_users_ps_details table
		$mysql_sql  = "CREATE TABLE " . $table_prefix . "users_ps_details (
      `psd_id` INT(11) NOT NULL AUTO_INCREMENT,
      `user_id` INT(11) default '0',
      `payment_id` INT(11) default '0',
      `is_default` TINYINT default '0',
      `is_active` INT(11) default '0',
      `cc_encrypt_type` TINYINT default '1',
      `cc_name` VARCHAR(128),
      `cc_first_name` VARCHAR(64),
      `cc_last_name` VARCHAR(64),
      `cc_number` VARCHAR(64),
      `cc_start_date` DATETIME,
      `cc_expiry_date` DATETIME,
      `cc_type` INT(11),
      `cc_issue_number` INT(11),
      `cc_security_code` VARCHAR(32),
      `success_payments` INT(11) default '0',
      `failed_payments` INT(11) default '0',
      `last_payment_date` DATETIME,
      `admin_id_added_by` INT(11) default '0',
      `admin_id_modified_by` INT(11) default '0',
      `date_added` DATETIME,
      `date_modified` DATETIME
      ,KEY cc_security_code (cc_security_code)
      ,KEY payment_id (payment_id)
      ,PRIMARY KEY (psd_id)
      ,KEY user_id (user_id)
		)";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_" . $table_prefix . "users_ps_details START 1";
		}
		$postgre_sql  = "CREATE TABLE " . $table_prefix . "users_ps_details (
      psd_id INT4 NOT NULL DEFAULT nextval('seq_" . $table_prefix . "users_ps_details'),
      user_id INT4 default '0',
      payment_id INT4 default '0',
      is_default SMALLINT default '0',
      is_active INT4 default '0',
      cc_encrypt_type SMALLINT default '1',
      cc_name VARCHAR(128),
      cc_first_name VARCHAR(64),
      cc_last_name VARCHAR(64),
      cc_number VARCHAR(64),
      cc_start_date TIMESTAMP,
      cc_expiry_date TIMESTAMP,
      cc_type INT4,
      cc_issue_number INT4,
      cc_security_code VARCHAR(32),
      success_payments INT4 default '0',
      failed_payments INT4 default '0',
      last_payment_date TIMESTAMP,
      admin_id_added_by INT4 default '0',
      admin_id_modified_by INT4 default '0',
      date_added TIMESTAMP,
      date_modified TIMESTAMP
      ,PRIMARY KEY (psd_id))";

		$access_sql  = "CREATE TABLE " . $table_prefix . "users_ps_details (
      [psd_id]  COUNTER  NOT NULL,
      [user_id] INTEGER,
      [payment_id] INTEGER,
      [is_default] BYTE,
      [is_active] INTEGER,
      [cc_encrypt_type] BYTE,
      [cc_name] VARCHAR(128),
      [cc_first_name] VARCHAR(64),
      [cc_last_name] VARCHAR(64),
      [cc_number] VARCHAR(64),
      [cc_start_date] DATETIME,
      [cc_expiry_date] DATETIME,
      [cc_type] INTEGER,
      [cc_issue_number] INTEGER,
      [cc_security_code] VARCHAR(32),
      [success_payments] INTEGER,
      [failed_payments] INTEGER,
      [last_payment_date] DATETIME,
      [admin_id_added_by] INTEGER,
      [admin_id_modified_by] INTEGER,
      [date_added] DATETIME,
      [date_modified] DATETIME
      ,PRIMARY KEY (psd_id))";

		$db2_sql  = "CREATE TABLE " . $table_prefix . "users_ps_details (
      psd_id INTEGER NOT NULL,
      user_id INTEGER default 0,
      payment_id INTEGER default 0,
      is_default SMALLINT default 0,
      is_active INTEGER default 0,
      cc_encrypt_type SMALLINT default 1,
      cc_name VARCHAR(128),
      cc_first_name VARCHAR(64),
      cc_last_name VARCHAR(64),
      cc_number VARCHAR(64),
      cc_start_date TIMESTAMP,
      cc_expiry_date TIMESTAMP,
      cc_type INTEGER,
      cc_issue_number INTEGER,
      cc_security_code VARCHAR(32),
      success_payments INTEGER default 0,
      failed_payments INTEGER default 0,
      last_payment_date TIMESTAMP,
      admin_id_added_by INTEGER default 0,
      admin_id_modified_by INTEGER default 0,
      date_added TIMESTAMP,
      date_modified TIMESTAMP
      ,PRIMARY KEY (psd_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX " . $table_prefix . "users_ps_details_payment_id ON " . $table_prefix . "users_ps_details (payment_id)";
			$sqls[] = "CREATE INDEX " . $table_prefix . "users_ps_details_user_id ON " . $table_prefix . "users_ps_details (user_id)";
		}

		if ($db_type == "db2") {
			$sqls[] = "CREATE SEQUENCE seq_" . $table_prefix . "users_ps_details AS INTEGER START WITH 1 INCREMENT BY 1 NO CACHE NO CYCLE";
			$sqls[] = "CREATE TRIGGER tr_" . $table_prefix . "users_ps_145 NO CASCADE BEFORE INSERT ON " . $table_prefix . "users_ps_details REFERENCING NEW AS newr_" . $table_prefix . "users_ps_details FOR EACH ROW MODE DB2SQL WHEN (newr_" . $table_prefix . "users_ps_details.psd_id IS NULL ) begin atomic set newr_" . $table_prefix . "users_ps_details.psd_id = nextval for seq_" . $table_prefix . "users_ps_details; end";
		}

		// new va_users_ps_properties table
		$mysql_sql  = "CREATE TABLE " . $table_prefix . "users_ps_properties (
      `user_property_id` INT(11) NOT NULL AUTO_INCREMENT,
      `psd_id` INT(11) default '0',
      `user_id` INT(11) default '0',
      `property_id` INT(11) default '0',
      `property_order` INT(11) default '1',
      `property_type` INT(11) default '0',
      `property_name` VARCHAR(255) NOT NULL,
      `property_value_id` INT(11),
      `property_value` TEXT
      ,KEY psd_id (psd_id)
      ,KEY user_id (user_id)
      ,PRIMARY KEY (user_property_id)
      ,KEY property_id (property_id)
      ,KEY property_name (property_name)
		)";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_" . $table_prefix . "users_ps_properties START 1";
		}
		$postgre_sql  = "CREATE TABLE " . $table_prefix . "users_ps_properties (
      user_property_id INT4 NOT NULL DEFAULT nextval('seq_" . $table_prefix . "users_ps_properties'),
      psd_id INT4 default '0',
      user_id INT4 default '0',
      property_id INT4 default '0',
      property_order INT4 default '1',
      property_type INT4 default '0',
      property_name VARCHAR(255) NOT NULL,
      property_value_id INT4,
      property_value TEXT
      ,PRIMARY KEY (user_property_id))";

		$access_sql  = "CREATE TABLE " . $table_prefix . "users_ps_properties (
      [user_property_id]  COUNTER  NOT NULL,
      [psd_id] INTEGER,
      [user_id] INTEGER,
      [property_id] INTEGER,
      [property_order] INTEGER,
      [property_type] INTEGER,
      [property_name] VARCHAR(255),
      [property_value_id] INTEGER,
      [property_value] LONGTEXT
      ,PRIMARY KEY (user_property_id))";

		$db2_sql  = "CREATE TABLE " . $table_prefix . "users_ps_properties (
      user_property_id INTEGER NOT NULL,
      psd_id INTEGER default 0,
      user_id INTEGER default 0,
      property_id INTEGER default 0,
      property_order INTEGER default 1,
      property_type INTEGER default 0,
      property_name VARCHAR(255) NOT NULL,
      property_value_id INTEGER,
      property_value LONG VARCHAR
      ,PRIMARY KEY (user_property_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX " . $table_prefix . "users_ps_properties_psd_id ON " . $table_prefix . "users_ps_properties (psd_id)";
			$sqls[] = "CREATE INDEX " . $table_prefix . "users_ps_properties_user_id ON " . $table_prefix . "users_ps_properties (user_id)";
			$sqls[] = "CREATE INDEX " . $table_prefix . "users_ps_properties_prop_107 ON " . $table_prefix . "users_ps_properties (property_id)";
			$sqls[] = "CREATE INDEX " . $table_prefix . "users_ps_properties_prop_108 ON " . $table_prefix . "users_ps_properties (property_name)";
		}

		if ($db_type == "db2") {
			$sqls[] = "CREATE SEQUENCE seq_" . $table_prefix . "users_ps_properties AS INTEGER START WITH 1 INCREMENT BY 1 NO CACHE NO CYCLE";
			$sqls[] = "CREATE TRIGGER tr_" . $table_prefix . "users_ps_146 NO CASCADE BEFORE INSERT ON " . $table_prefix . "users_ps_properties REFERENCING NEW AS newr_" . $table_prefix . "users_ps_properties FOR EACH ROW MODE DB2SQL WHEN (newr_" . $table_prefix . "users_ps_properties.user_property_id IS NULL ) begin atomic set newr_" . $table_prefix . "users_ps_properties.user_property_id = nextval for seq_" . $table_prefix . "users_ps_properties; end";
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.11");
	}


	if (comp_vers("3.6.12", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN free_postage_all TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN free_postage_all SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN free_postage_all BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN free_postage_all SMALLINT DEFAULT 1"
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "coupons SET free_postage_all=1 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN free_postage_ids TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN free_postage_ids TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN free_postage_ids LONGTEXT",
			"db2"     => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN free_postage_idsLONG VARCHAR"
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.12");
	}

	if (comp_vers("3.6.13", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN order_total_min DOUBLE(16,2) ",       
			"postgre" => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN order_total_min FLOAT4",         
			"access"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN order_total_min FLOAT",               
			"db2"     => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN order_total_min DOUBLE" 
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN order_total_max DOUBLE(16,2) ",       
			"postgre" => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN order_total_max FLOAT4",         
			"access"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN order_total_max FLOAT",               
			"db2"     => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN order_total_max DOUBLE" 
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.13");
	}

	if (comp_vers("3.6.14", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "user_profile_sections ADD COLUMN user_types_all TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "user_profile_sections ADD COLUMN user_types_all SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "user_profile_sections ADD COLUMN user_types_all BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "user_profile_sections ADD COLUMN user_types_all SMALLINT DEFAULT 1"
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "user_profile_sections SET user_types_all=1 ";

		$sqls[] = "ALTER TABLE " . $table_prefix . "user_profile_properties ADD COLUMN property_code VARCHAR(64) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "order_custom_properties ADD COLUMN property_code VARCHAR(64) ";

		$mysql_sql  = "CREATE TABLE " . $table_prefix . "user_profile_sections_types (";
		$mysql_sql .= "  `section_id` INT(11) NOT NULL default '0',";
		$mysql_sql .= "  `user_type_id` INT(11) NOT NULL default '0'";
		$mysql_sql .= "  ,PRIMARY KEY (section_id,user_type_id))";

		$postgre_sql  = "CREATE TABLE " . $table_prefix . "user_profile_sections_types (";
		$postgre_sql .= "  section_id INT4 NOT NULL default '0',";
		$postgre_sql .= "  user_type_id INT4 NOT NULL default '0'";
		$postgre_sql .= "  ,PRIMARY KEY (section_id,user_type_id))";

		$access_sql  = "CREATE TABLE " . $table_prefix . "user_profile_sections_types (";
		$access_sql .= "  [section_id] INTEGER NOT NULL,";
		$access_sql .= "  [user_type_id] INTEGER NOT NULL";
		$access_sql .= "  ,PRIMARY KEY (section_id,user_type_id))";

		$db2_sql  = "CREATE TABLE " . $table_prefix . "user_profile_sections_types (";
		$db2_sql .= "  section_id INTEGER NOT NULL default 0,";
		$db2_sql .= "  user_type_id INTEGER NOT NULL default 0";
		$db2_sql .= "  ,PRIMARY KEY (section_id,user_type_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];


		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.14");
	}

	if (comp_vers("3.6.15", $current_db_version) == 1)
	{
		$sqls[] = "ALTER TABLE " . $table_prefix . "user_profile_sections ADD COLUMN section_code VARCHAR(16) ";
		$sqls[] = "UPDATE " . $table_prefix . "user_profile_sections SET section_code='custom' ";
		$sqls[] = "UPDATE " . $table_prefix . "user_profile_sections SET section_code='login' WHERE section_id=1 ";
		$sqls[] = "UPDATE " . $table_prefix . "user_profile_sections SET section_code='personal' WHERE section_id=2 ";
		$sqls[] = "UPDATE " . $table_prefix . "user_profile_sections SET section_code='delivery' WHERE section_id=3 ";
		$sqls[] = "UPDATE " . $table_prefix . "user_profile_sections SET section_code='additional' WHERE section_id=4 ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.15");
	}

	if (comp_vers("3.6.16", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN allowed_user_edit TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN allowed_user_edit SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN allowed_user_edit BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN allowed_user_edit SMALLINT DEFAULT 0"
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "payment_systems SET allowed_user_edit=0 ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.16");
	}

	if (comp_vers("3.6.17", $current_db_version) == 1)
	{
		$sqls[] = "ALTER TABLE " . $table_prefix . "countries ADD COLUMN phone_code VARCHAR(16) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.17");
	}


	if (comp_vers("3.6.18", $current_db_version) == 1)
	{
		$order_min_goods_cost_field = false;
		$order_max_goods_cost_field = false;
		$order_min_weight_field = false;
		$order_max_weight_field = false;
		$fields = $db->get_fields($table_prefix."coupons");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "order_min_goods_cost") {
				$order_min_goods_cost_field = true;
			} else if ($field_info["name"] == "order_max_goods_cost") {
				$order_max_goods_cost_field = true;
			} else if ($field_info["name"] == "order_min_weight") {
				$order_min_weight_field = true;
			} else if ($field_info["name"] == "order_max_weight") {
				$order_max_weight_field = true;
			} 
		}

		if (!$order_min_goods_cost_field) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN order_min_goods_cost DOUBLE(16,2) ",
				"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN order_min_goods_cost FLOAT4 ",
				"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN order_min_goods_cost FLOAT",
				"db2"     => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN order_min_goods_cost DOUBLE ",
			);
			$sqls[] = $sql_types[$db_type];
		}

		if (!$order_max_goods_cost_field) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN order_max_goods_cost DOUBLE(16,2) ",
				"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN order_max_goods_cost FLOAT4 ",
				"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN order_max_goods_cost FLOAT",
				"db2"     => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN order_max_goods_cost DOUBLE ",
			);
			$sqls[] = $sql_types[$db_type];
		}

		if (!$order_min_weight_field) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN order_min_weight DOUBLE(16,4) ",
				"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN order_min_weight FLOAT4 ",
				"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN order_min_weight FLOAT",
				"db2"     => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN order_min_weight DOUBLE ",
			);
			$sqls[] = $sql_types[$db_type];
		}

		if (!$order_max_weight_field) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN order_max_weight DOUBLE(16,4) ",
				"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN order_max_weight FLOAT4 ",
				"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN order_max_weight FLOAT",
				"db2"     => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN order_max_weight DOUBLE ",
			);
			$sqls[] = $sql_types[$db_type];
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.18");
	}

	if (comp_vers("3.6.19", $current_db_version) == 1)
	{
		// currencies sites 
		$mysql_sql  = "CREATE TABLE " . $table_prefix . "currencies_sites (";
		$mysql_sql .= "  `currency_id` INT(11) NOT NULL default '0',";
		$mysql_sql .= "  `site_id` INT(11) NOT NULL default '0'";
		$mysql_sql .= "  ,PRIMARY KEY (currency_id,site_id))";

		$postgre_sql  = "CREATE TABLE " . $table_prefix . "currencies_sites (";
		$postgre_sql .= "  currency_id INT4 NOT NULL default '0',";
		$postgre_sql .= "  site_id INT4 NOT NULL default '0'";
		$postgre_sql .= "  ,PRIMARY KEY (currency_id,site_id))";

		$access_sql  = "CREATE TABLE " . $table_prefix . "currencies_sites (";
		$access_sql .= "  [currency_id] INTEGER NOT NULL,";
		$access_sql .= "  [site_id] INTEGER NOT NULL";
		$access_sql .= "  ,PRIMARY KEY (currency_id,site_id))";

		$db2_sql  = "CREATE TABLE " . $table_prefix . "currencies_sites (";
		$db2_sql .= "  currency_id INTEGER NOT NULL default 0,";
		$db2_sql .= "  site_id INTEGER NOT NULL default 0";
		$db2_sql .= "  ,PRIMARY KEY (currency_id,site_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "currencies ADD COLUMN sites_all TINYINT NOT NULL DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "currencies ADD COLUMN sites_all SMALLINT NOT NULL DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "currencies ADD COLUMN sites_all BYTE NOT NULL ",
			"db2"     => "ALTER TABLE " . $table_prefix . "currencies ADD COLUMN sites_all SMALLINT NOT NULL DEFAULT 1"
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "currencies SET sites_all=1 ";


		// items properties 
		$mysql_sql  = "CREATE TABLE " . $table_prefix . "items_properties_sites (";
		$mysql_sql .= "  `property_id` INT(11) NOT NULL default '0',";
		$mysql_sql .= "  `site_id` INT(11) NOT NULL default '0'";
		$mysql_sql .= "  ,PRIMARY KEY (property_id,site_id))";

		$postgre_sql  = "CREATE TABLE " . $table_prefix . "items_properties_sites (";
		$postgre_sql .= "  property_id INT4 NOT NULL default '0',";
		$postgre_sql .= "  site_id INT4 NOT NULL default '0'";
		$postgre_sql .= "  ,PRIMARY KEY (property_id,site_id))";

		$access_sql  = "CREATE TABLE " . $table_prefix . "items_properties_sites (";
		$access_sql .= "  [property_id] INTEGER NOT NULL,";
		$access_sql .= "  [site_id] INTEGER NOT NULL";
		$access_sql .= "  ,PRIMARY KEY (property_id,site_id))";

		$db2_sql  = "CREATE TABLE " . $table_prefix . "items_properties_sites (";
		$db2_sql .= "  property_id INTEGER NOT NULL default 0,";
		$db2_sql .= "  site_id INTEGER NOT NULL default 0";
		$db2_sql .= "  ,PRIMARY KEY (property_id,site_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN sites_all TINYINT NOT NULL DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN sites_all SMALLINT NOT NULL DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN sites_all BYTE NOT NULL ",
			"db2"     => "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN sites_all SMALLINT NOT NULL DEFAULT 1"
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "items_properties SET sites_all=1 ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.19");
	}

	if (comp_vers("3.6.20", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN generate_invoice TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN generate_invoice SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN generate_invoice BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN generate_invoice SMALLINT DEFAULT 0"
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "order_statuses SET generate_invoice=1 ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.20");
	}

	if (comp_vers("3.6.21", $current_db_version) == 1)
	{
		$sql = " SELECT MAX(order_id) FROM " . $table_prefix . "orders ";
		$invoice_sequence_number = get_db_value($sql) + 1;
		$sqls[] = "INSERT INTO " . $table_prefix . "global_settings (site_id, setting_type, setting_name, setting_value) VALUES (1, 'order_info', 'invoice_sequence_number', ".$db->tosql($invoice_sequence_number, TEXT).")";
		$sqls[] = "INSERT INTO " . $table_prefix . "global_settings (site_id, setting_type, setting_name, setting_value) VALUES (1, 'order_info', 'invoice_number_mask', '#')";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.20");
	}

	if (comp_vers("3.6.23", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN min_cart_quantity INT(11) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN min_cart_quantity INT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN min_cart_quantity INTEGER ",
			"db2"     => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN min_cart_quantity INTEGER "
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN max_cart_quantity INT(11) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN max_cart_quantity INT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN max_cart_quantity INTEGER ",
			"db2"     => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN max_cart_quantity INTEGER "
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN min_cart_cost DOUBLE(16,2) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN min_cart_cost FLOAT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN min_cart_cost FLOAT",
			"db2"     => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN min_cart_cost DOUBLE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN max_cart_cost DOUBLE(16,2) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN max_cart_cost FLOAT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN max_cart_cost FLOAT",
			"db2"     => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN max_cart_cost DOUBLE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql  = " UPDATE " . $table_prefix . "coupons ";
		$sql .= " SET min_cart_quantity=min_quantity, max_cart_quantity=max_quantity, ";
		$sql .= " min_cart_cost=minimum_amount, max_cart_cost=maximum_amount ";
		$sql .= " WHERE discount_type=1 OR discount_type=2 ";
		$sqls[] = $sql;

		$sqls[] = " UPDATE " . $table_prefix . "coupons SET cart_items_all=1 ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.23");
	}

	if (comp_vers("3.6.24", $current_db_version) == 1)
	{
		// new CMS tables 
		$mysql_sql  = "CREATE TABLE ".$table_prefix."cms_blocks (
      `block_id` INT(11) NOT NULL AUTO_INCREMENT,
      `module_id` INT(11) default '0',
      `block_order` INT(11) default '1',
      `block_code` VARCHAR(64),
      `block_name` VARCHAR(128),
      `php_script` VARCHAR(128),
      `html_template` VARCHAR(50),
      `pages_all` TINYINT default '0'
      ,PRIMARY KEY (block_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_blocks START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."cms_blocks (
      block_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."cms_blocks'),
      module_id INT4 default '0',
      block_order INT4 default '1',
      block_code VARCHAR(64),
      block_name VARCHAR(128),
      php_script VARCHAR(128),
      html_template VARCHAR(50),
      pages_all SMALLINT default '0'
      ,PRIMARY KEY (block_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."cms_blocks (
      [block_id]  COUNTER  NOT NULL,
      [module_id] INTEGER,
      [block_order] INTEGER,
      [block_code] VARCHAR(64),
      [block_name] VARCHAR(128),
      [php_script] VARCHAR(128),
      [html_template] VARCHAR(50),
      [pages_all] BYTE
      ,PRIMARY KEY (block_id))";

		$db2_sql  = "CREATE TABLE ".$table_prefix."cms_blocks (
      block_id INTEGER NOT NULL,
      module_id INTEGER default 0,
      block_order INTEGER default 1,
      block_code VARCHAR(64),
      block_name VARCHAR(128),
      php_script VARCHAR(128),
      html_template VARCHAR(50),
      pages_all SMALLINT default 0
      ,PRIMARY KEY (block_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type == "db2") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_blocks AS INTEGER START WITH 1 INCREMENT BY 1 NO CACHE NO CYCLE";
			$sqls[] = "CREATE TRIGGER tr_".$table_prefix."cms_blocks NO CASCADE BEFORE INSERT ON ".$table_prefix."cms_blocks REFERENCING NEW AS newr_".$table_prefix."cms_blocks FOR EACH ROW MODE DB2SQL WHEN (newr_".$table_prefix."cms_blocks.block_id IS NULL ) begin atomic set newr_".$table_prefix."cms_blocks.block_id = nextval for seq_".$table_prefix."cms_blocks; end";
		}

		$mysql_sql  = "CREATE TABLE ".$table_prefix."cms_blocks_pages (
		  `block_id` INT(11) NOT NULL default '0',
		  `page_id` INT(11) NOT NULL default '0'
		  ,PRIMARY KEY (block_id,page_id))";

		$postgre_sql  = "CREATE TABLE ".$table_prefix."cms_blocks_pages (
      block_id INT4 NOT NULL default '0',
      page_id INT4 NOT NULL default '0'
      ,PRIMARY KEY (block_id,page_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."cms_blocks_pages (
      [block_id] INTEGER NOT NULL,
      [page_id] INTEGER NOT NULL
      ,PRIMARY KEY (block_id,page_id))";

		$db2_sql  = "CREATE TABLE ".$table_prefix."cms_blocks_pages (
      block_id INTEGER NOT NULL default 0,
      page_id INTEGER NOT NULL default 0
      ,PRIMARY KEY (block_id,page_id))";


		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		$mysql_sql  = "CREATE TABLE ".$table_prefix."cms_blocks_properties (
      `property_id` INT(11) NOT NULL AUTO_INCREMENT,
      `block_id` INT(11) default '0',
      `property_order` INT(11) default '1',
      `property_name` VARCHAR(255),
      `control_type` VARCHAR(16),
      `parent_property_id` INT(11),
      `parent_value_id` INT(11),
      `variable_name` VARCHAR(64),
      `default_value` VARCHAR(255),
      `required` TINYINT default '0',
      `property_style` TEXT,
      `control_style` TEXT,
      `start_html` TEXT,
      `middle_html` TEXT,
      `before_control_html` TEXT,
      `after_control_html` TEXT,
      `end_html` TEXT,
      `control_code` TEXT,
      `onchange_code` TEXT,
      `onclick_code` TEXT
      ,PRIMARY KEY (property_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_blocks_properties START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."cms_blocks_properties (
      property_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."cms_blocks_properties'),
      block_id INT4 default '0',
      property_order INT4 default '1',
      property_name VARCHAR(255),
      control_type VARCHAR(16),
      parent_property_id INT4,
      parent_value_id INT4,
      variable_name VARCHAR(64),
      default_value VARCHAR(255),
      required SMALLINT default '0',
      property_style TEXT,
      control_style TEXT,
      start_html TEXT,
      middle_html TEXT,
      before_control_html TEXT,
      after_control_html TEXT,
      end_html TEXT,
      control_code TEXT,
      onchange_code TEXT,
      onclick_code TEXT
      ,PRIMARY KEY (property_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."cms_blocks_properties (
      [property_id]  COUNTER  NOT NULL,
      [block_id] INTEGER,
      [property_order] INTEGER,
      [property_name] VARCHAR(255),
      [control_type] VARCHAR(16),
      [parent_property_id] INTEGER,
      [parent_value_id] INTEGER,
      [variable_name] VARCHAR(64),
      [default_value] VARCHAR(255),
      [required] BYTE,
      [property_style] LONGTEXT,
      [control_style] LONGTEXT,
      [start_html] LONGTEXT,
      [middle_html] LONGTEXT,
      [before_control_html] LONGTEXT,
      [after_control_html] LONGTEXT,
      [end_html] LONGTEXT,
      [control_code] LONGTEXT,
      [onchange_code] LONGTEXT,
      [onclick_code] LONGTEXT
      ,PRIMARY KEY (property_id))";

		$db2_sql  = "CREATE TABLE ".$table_prefix."cms_blocks_properties (
      property_id INTEGER NOT NULL,
      block_id INTEGER default 0,
      property_order INTEGER default 1,
      property_name VARCHAR(255),
      control_type VARCHAR(16),
      parent_property_id INTEGER,
      parent_value_id INTEGER,
      variable_name VARCHAR(64),
      default_value VARCHAR(255),
      required SMALLINT default 0,
      property_style LONG VARCHAR,
      control_style LONG VARCHAR,
      start_html LONG VARCHAR,
      middle_html LONG VARCHAR,
      before_control_html LONG VARCHAR,
      after_control_html LONG VARCHAR,
      end_html LONG VARCHAR,
      control_code LONG VARCHAR,
      onchange_code LONG VARCHAR,
      onclick_code LONG VARCHAR
      ,PRIMARY KEY (property_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type == "db2") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_blocks_properties AS INTEGER START WITH 1 INCREMENT BY 1 NO CACHE NO CYCLE";
			$sqls[] = "CREATE TRIGGER tr_".$table_prefix."cms_bloc_35 NO CASCADE BEFORE INSERT ON ".$table_prefix."cms_blocks_properties REFERENCING NEW AS newr_".$table_prefix."cms_blocks_properties FOR EACH ROW MODE DB2SQL WHEN (newr_".$table_prefix."cms_blocks_properties.property_id IS NULL ) begin atomic set newr_".$table_prefix."cms_blocks_properties.property_id = nextval for seq_".$table_prefix."cms_blocks_properties; end";
		}

		$mysql_sql  = "CREATE TABLE ".$table_prefix."cms_blocks_settings (
      `bs_id` INT(11) NOT NULL AUTO_INCREMENT,
      `pb_id` INT(11) default '0',
      `property_id` INT(11) default '0',
      `value_id` INT(11) default '0',
      `variable_name` VARCHAR(64),
      `variable_value` VARCHAR(255)
      ,PRIMARY KEY (bs_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_blocks_settings START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."cms_blocks_settings (
      bs_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."cms_blocks_settings'),
      pb_id INT4 default '0',
      property_id INT4 default '0',
      value_id INT4 default '0',
      variable_name VARCHAR(64),
      variable_value VARCHAR(255)
      ,PRIMARY KEY (bs_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."cms_blocks_settings (
      [bs_id]  COUNTER  NOT NULL,
      [pb_id] INTEGER,
      [property_id] INTEGER,
      [value_id] INTEGER,
      [variable_name] VARCHAR(64),
      [variable_value] VARCHAR(255)
      ,PRIMARY KEY (bs_id))";

		$db2_sql  = "CREATE TABLE ".$table_prefix."cms_blocks_settings (
      bs_id INTEGER NOT NULL,
      pb_id INTEGER default 0,
      property_id INTEGER default 0,
      value_id INTEGER default 0,
      variable_name VARCHAR(64),
      variable_value VARCHAR(255)
      ,PRIMARY KEY (bs_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type == "db2") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_blocks_settings AS INTEGER START WITH 1 INCREMENT BY 1 NO CACHE NO CYCLE";
			$sqls[] = "CREATE TRIGGER tr_".$table_prefix."cms_bloc_36 NO CASCADE BEFORE INSERT ON ".$table_prefix."cms_blocks_settings REFERENCING NEW AS newr_".$table_prefix."cms_blocks_settings FOR EACH ROW MODE DB2SQL WHEN (newr_".$table_prefix."cms_blocks_settings.bs_id IS NULL ) begin atomic set newr_".$table_prefix."cms_blocks_settings.bs_id = nextval for seq_".$table_prefix."cms_blocks_settings; end";
		}


		$mysql_sql  = "CREATE TABLE ".$table_prefix."cms_blocks_values (
      `value_id` INT(11) NOT NULL AUTO_INCREMENT,
      `property_id` INT(11) default '0',
      `value_order` INT(11) default '1',
      `value_name` VARCHAR(255),
      `variable_name` VARCHAR(64),
      `variable_value` VARCHAR(255),
      `hide_value` TINYINT default '0',
      `is_default_value` TINYINT default '0'
      ,PRIMARY KEY (value_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_blocks_values START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."cms_blocks_values (
      value_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."cms_blocks_values'),
      property_id INT4 default '0',
      value_order INT4 default '1',
      value_name VARCHAR(255),
      variable_name VARCHAR(64),
      variable_value VARCHAR(255),
      hide_value SMALLINT default '0',
      is_default_value SMALLINT default '0'
      ,PRIMARY KEY (value_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."cms_blocks_values (
      [value_id]  COUNTER  NOT NULL,
      [property_id] INTEGER,
      [value_order] INTEGER,
      [value_name] VARCHAR(255),
      [variable_name] VARCHAR(64),
      [variable_value] VARCHAR(255),
      [hide_value] BYTE,
      [is_default_value] BYTE
      ,PRIMARY KEY (value_id))";

		$db2_sql  = "CREATE TABLE ".$table_prefix."cms_blocks_values (
      value_id INTEGER NOT NULL,
      property_id INTEGER default 0,
      value_order INTEGER default 1,
      value_name VARCHAR(255),
      variable_name VARCHAR(64),
      variable_value VARCHAR(255),
      hide_value SMALLINT default 0,
      is_default_value SMALLINT default 0
      ,PRIMARY KEY (value_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type == "db2") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_blocks_values AS INTEGER START WITH 1 INCREMENT BY 1 NO CACHE NO CYCLE";
			$sqls[] = "CREATE TRIGGER tr_".$table_prefix."cms_bloc_37 NO CASCADE BEFORE INSERT ON ".$table_prefix."cms_blocks_values REFERENCING NEW AS newr_".$table_prefix."cms_blocks_values FOR EACH ROW MODE DB2SQL WHEN (newr_".$table_prefix."cms_blocks_values.value_id IS NULL ) begin atomic set newr_".$table_prefix."cms_blocks_values.value_id = nextval for seq_".$table_prefix."cms_blocks_values; end";
		}

		$mysql_sql  = "CREATE TABLE ".$table_prefix."cms_frames (
      `frame_id` INT(11) NOT NULL AUTO_INCREMENT,
      `layout_id` INT(11) default '0',
      `frame_name` VARCHAR(128),
      `tag_name` VARCHAR(128)
      ,PRIMARY KEY (frame_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_frames START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."cms_frames (
      frame_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."cms_frames'),
      layout_id INT4 default '0',
      frame_name VARCHAR(128),
      tag_name VARCHAR(128)
      ,PRIMARY KEY (frame_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."cms_frames (
      [frame_id]  COUNTER  NOT NULL,
      [layout_id] INTEGER,
      [frame_name] VARCHAR(128),
      [tag_name] VARCHAR(128)
      ,PRIMARY KEY (frame_id))";

		$db2_sql  = "CREATE TABLE ".$table_prefix."cms_frames (
      frame_id INTEGER NOT NULL,
      layout_id INTEGER default 0,
      frame_name VARCHAR(128),
      tag_name VARCHAR(128)
      ,PRIMARY KEY (frame_id))";
    
		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type == "db2") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_frames AS INTEGER START WITH 1 INCREMENT BY 1 NO CACHE NO CYCLE";
			$sqls[] = "CREATE TRIGGER tr_".$table_prefix."cms_frames NO CASCADE BEFORE INSERT ON ".$table_prefix."cms_frames REFERENCING NEW AS newr_".$table_prefix."cms_frames FOR EACH ROW MODE DB2SQL WHEN (newr_".$table_prefix."cms_frames.frame_id IS NULL ) begin atomic set newr_".$table_prefix."cms_frames.frame_id = nextval for seq_".$table_prefix."cms_frames; end";
		}

		$mysql_sql  = "CREATE TABLE ".$table_prefix."cms_frames_settings (
      `fs_id` INT(11) NOT NULL AUTO_INCREMENT,
      `ps_id` INT(11) default '0',
      `frame_id` INT(11) default '0',
      `frame_style` TEXT,
      `frame_class` VARCHAR(64),
      `frame_code` TEXT,
      `html_frame_start` TEXT,
      `html_before_block` TEXT,
      `html_after_block` TEXT,
      `html_frame_end` TEXT,
      `html_between_blocks` TEXT
      ,KEY frame_id (frame_id)
      ,PRIMARY KEY (fs_id)
      ,KEY ps_id (ps_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_frames_settings START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."cms_frames_settings (
      fs_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."cms_frames_settings'),
      ps_id INT4 default '0',
      frame_id INT4 default '0',
      frame_style TEXT,
      frame_class VARCHAR(64),
      frame_code TEXT,
      html_frame_start TEXT,
      html_before_block TEXT,
      html_after_block TEXT,
      html_frame_end TEXT,
      html_between_blocks TEXT
      ,PRIMARY KEY (fs_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."cms_frames_settings (
      [fs_id]  COUNTER  NOT NULL,
      [ps_id] INTEGER,
      [frame_id] INTEGER,
      [frame_style] LONGTEXT,
      [frame_class] VARCHAR(64),
      [frame_code] LONGTEXT,
      [html_frame_start] LONGTEXT,
      [html_before_block] LONGTEXT,
      [html_after_block] LONGTEXT,
      [html_frame_end] LONGTEXT,
      [html_between_blocks] LONGTEXT
      ,PRIMARY KEY (fs_id))";

		$db2_sql  = "CREATE TABLE ".$table_prefix."cms_frames_settings (
      fs_id INTEGER NOT NULL,
      ps_id INTEGER default 0,
      frame_id INTEGER default 0,
      frame_style LONG VARCHAR,
      frame_class VARCHAR(64),
      frame_code LONG VARCHAR,
      html_frame_start LONG VARCHAR,
      html_before_block LONG VARCHAR,
      html_after_block LONG VARCHAR,
      html_frame_end LONG VARCHAR,
      html_between_blocks LONG VARCHAR
      ,PRIMARY KEY (fs_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX ".$table_prefix."cms_frames_settings_frame_id ON ".$table_prefix."cms_frames_settings (frame_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."cms_frames_settings_ps_id ON ".$table_prefix."cms_frames_settings (ps_id)";
		}

		if ($db_type == "db2") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_frames_settings AS INTEGER START WITH 1 INCREMENT BY 1 NO CACHE NO CYCLE";
			$sqls[] = "CREATE TRIGGER tr_".$table_prefix."cms_fram_38 NO CASCADE BEFORE INSERT ON ".$table_prefix."cms_frames_settings REFERENCING NEW AS newr_".$table_prefix."cms_frames_settings FOR EACH ROW MODE DB2SQL WHEN (newr_".$table_prefix."cms_frames_settings.fs_id IS NULL ) begin atomic set newr_".$table_prefix."cms_frames_settings.fs_id = nextval for seq_".$table_prefix."cms_frames_settings; end";
		}

		$mysql_sql  = "CREATE TABLE ".$table_prefix."cms_layouts (
      `layout_id` INT(11) NOT NULL AUTO_INCREMENT,
      `layout_name` VARCHAR(255),
      `layout_order` INT(11) default '0',
      `layout_template` VARCHAR(255),
      `admin_template` VARCHAR(255)
      ,PRIMARY KEY (layout_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_layouts START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."cms_layouts (
      layout_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."cms_layouts'),
      layout_name VARCHAR(255),
      layout_order INT4 default '0',
      layout_template VARCHAR(255),
      admin_template VARCHAR(255)
      ,PRIMARY KEY (layout_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."cms_layouts (
      [layout_id]  COUNTER  NOT NULL,
      [layout_name] VARCHAR(255),
      [layout_order] INTEGER,
      [layout_template] VARCHAR(255),
      [admin_template] VARCHAR(255)
      ,PRIMARY KEY (layout_id))";

		$db2_sql  = "CREATE TABLE ".$table_prefix."cms_layouts (
      layout_id INTEGER NOT NULL,
      layout_name VARCHAR(255),
      layout_order INTEGER default 0,
      layout_template VARCHAR(255),
      admin_template VARCHAR(255)
      ,PRIMARY KEY (layout_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type == "db2") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_layouts AS INTEGER START WITH 1 INCREMENT BY 1 NO CACHE NO CYCLE";
			$sqls[] = "CREATE TRIGGER tr_".$table_prefix."cms_layouts NO CASCADE BEFORE INSERT ON ".$table_prefix."cms_layouts REFERENCING NEW AS newr_".$table_prefix."cms_layouts FOR EACH ROW MODE DB2SQL WHEN (newr_".$table_prefix."cms_layouts.layout_id IS NULL ) begin atomic set newr_".$table_prefix."cms_layouts.layout_id = nextval for seq_".$table_prefix."cms_layouts; end";
		}

		$mysql_sql  = "CREATE TABLE ".$table_prefix."cms_modules (
      `module_id` INT(11) NOT NULL AUTO_INCREMENT,
      `module_order` INT(11) default '0',
      `module_code` VARCHAR(32),
      `module_name` VARCHAR(255)
      ,PRIMARY KEY (module_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_modules START 8";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."cms_modules (
      module_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."cms_modules'),
      module_order INT4 default '0',
      module_code VARCHAR(32),
      module_name VARCHAR(255)
      ,PRIMARY KEY (module_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."cms_modules (
      [module_id]  COUNTER  NOT NULL,
      [module_order] INTEGER,
      [module_code] VARCHAR(32),
      [module_name] VARCHAR(255)
      ,PRIMARY KEY (module_id))";

		$db2_sql  = "CREATE TABLE ".$table_prefix."cms_modules (
      module_id INTEGER NOT NULL,
      module_order INTEGER default 0,
      module_code VARCHAR(32),
      module_name VARCHAR(255)
      ,PRIMARY KEY (module_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type == "db2") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_modules AS INTEGER START WITH 8 INCREMENT BY 1 NO CACHE NO CYCLE";
			$sqls[] = "CREATE TRIGGER tr_".$table_prefix."cms_modules NO CASCADE BEFORE INSERT ON ".$table_prefix."cms_modules REFERENCING NEW AS newr_".$table_prefix."cms_modules FOR EACH ROW MODE DB2SQL WHEN (newr_".$table_prefix."cms_modules.module_id IS NULL ) begin atomic set newr_".$table_prefix."cms_modules.module_id = nextval for seq_".$table_prefix."cms_modules; end";
		}

		$mysql_sql  = "CREATE TABLE ".$table_prefix."cms_pages (
      `page_id` INT(11) NOT NULL AUTO_INCREMENT,
      `module_id` INT(11) default '0',
      `page_order` INT(11) default '0',
      `page_code` VARCHAR(32),
      `page_name` VARCHAR(128)
      ,KEY page_code (page_code)
      ,PRIMARY KEY (page_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_pages START 45";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."cms_pages (
      page_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."cms_pages'),
      module_id INT4 default '0',
      page_order INT4 default '0',
      page_code VARCHAR(32),
      page_name VARCHAR(128)
      ,PRIMARY KEY (page_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."cms_pages (
      [page_id]  COUNTER  NOT NULL,
      [module_id] INTEGER,
      [page_order] INTEGER,
      [page_code] VARCHAR(32),
      [page_name] VARCHAR(128)
      ,PRIMARY KEY (page_id))";

		$db2_sql  = "CREATE TABLE ".$table_prefix."cms_pages (
      page_id INTEGER NOT NULL,
      module_id INTEGER default 0,
      page_order INTEGER default 0,
      page_code VARCHAR(32),
      page_name VARCHAR(128)
      ,PRIMARY KEY (page_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX ".$table_prefix."cms_pages_page_code ON ".$table_prefix."cms_pages (page_code)";
		}

		if ($db_type == "db2") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_pages AS INTEGER START WITH 45 INCREMENT BY 1 NO CACHE NO CYCLE";
			$sqls[] = "CREATE TRIGGER tr_".$table_prefix."cms_pages NO CASCADE BEFORE INSERT ON ".$table_prefix."cms_pages REFERENCING NEW AS newr_".$table_prefix."cms_pages FOR EACH ROW MODE DB2SQL WHEN (newr_".$table_prefix."cms_pages.page_id IS NULL ) begin atomic set newr_".$table_prefix."cms_pages.page_id = nextval for seq_".$table_prefix."cms_pages; end";
		}


		$mysql_sql  = "CREATE TABLE ".$table_prefix."cms_pages_blocks (
      `pb_id` INT(11) NOT NULL AUTO_INCREMENT,
      `ps_id` INT(11) default '0',
      `block_id` INT(11) default '0',
      `frame_id` INT(11) default '0',
      `block_order` INT(11) default '0',
      `css_class` VARCHAR(128),
      `html_template` VARCHAR(128),
      `block_style` TEXT
      ,PRIMARY KEY (pb_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_pages_blocks START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."cms_pages_blocks (
      pb_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."cms_pages_blocks'),
      ps_id INT4 default '0',
      block_id INT4 default '0',
      frame_id INT4 default '0',
      block_order INT4 default '0',
      css_class VARCHAR(128),
      html_template VARCHAR(128),
      block_style TEXT
      ,PRIMARY KEY (pb_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."cms_pages_blocks (
      [pb_id]  COUNTER  NOT NULL,
      [ps_id] INTEGER,
      [block_id] INTEGER,
      [frame_id] INTEGER,
      [block_order] INTEGER,
      [css_class] VARCHAR(128),
      [html_template] VARCHAR(128),
      [block_style] LONGTEXT
      ,PRIMARY KEY (pb_id))";

		$db2_sql  = "CREATE TABLE ".$table_prefix."cms_pages_blocks (
      pb_id INTEGER NOT NULL,
      ps_id INTEGER default 0,
      block_id INTEGER default 0,
      frame_id INTEGER default 0,
      block_order INTEGER default 0,
      css_class VARCHAR(128),
      html_template VARCHAR(128),
      block_style LONG VARCHAR
      ,PRIMARY KEY (pb_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type == "db2") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_pages_blocks AS INTEGER START WITH 1 INCREMENT BY 1 NO CACHE NO CYCLE";
			$sqls[] = "CREATE TRIGGER tr_".$table_prefix."cms_page_39 NO CASCADE BEFORE INSERT ON ".$table_prefix."cms_pages_blocks REFERENCING NEW AS newr_".$table_prefix."cms_pages_blocks FOR EACH ROW MODE DB2SQL WHEN (newr_".$table_prefix."cms_pages_blocks.pb_id IS NULL ) begin atomic set newr_".$table_prefix."cms_pages_blocks.pb_id = nextval for seq_".$table_prefix."cms_pages_blocks; end";
		}

		$mysql_sql  = "CREATE TABLE ".$table_prefix."cms_pages_settings (
      `ps_id` INT(11) NOT NULL AUTO_INCREMENT,
      `page_id` INT(11),
      `key_code` VARCHAR(32),
      `key_type` VARCHAR(16),
      `key_rule` VARCHAR(16),
      `layout_id` INT(11) default '0',
      `site_id` INT(11) default '0',
      `meta_title` VARCHAR(255),
      `meta_keywords` VARCHAR(255),
      `meta_description` VARCHAR(255)
      ,KEY key_code (key_code)
      ,KEY page_id (page_id)
      ,PRIMARY KEY (ps_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_pages_settings START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."cms_pages_settings (
      ps_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."cms_pages_settings'),
      page_id INT4,
      key_code VARCHAR(32),
      key_type VARCHAR(16),
      key_rule VARCHAR(16),
      layout_id INT4 default '0',
      site_id INT4 default '0',
      meta_title VARCHAR(255),
      meta_keywords VARCHAR(255),
      meta_description VARCHAR(255)
      ,PRIMARY KEY (ps_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."cms_pages_settings (
      [ps_id]  COUNTER  NOT NULL,
      [page_id] INTEGER,
      [key_code] VARCHAR(32),
      [key_type] VARCHAR(16),
      [key_rule] VARCHAR(16),
      [layout_id] INTEGER,
      [site_id] INTEGER,
      [meta_title] VARCHAR(255),
      [meta_keywords] VARCHAR(255),
      [meta_description] VARCHAR(255)
      ,PRIMARY KEY (ps_id))";

		$db2_sql  = "CREATE TABLE ".$table_prefix."cms_pages_settings (
      ps_id INTEGER NOT NULL,
      page_id INTEGER,
      key_code VARCHAR(32),
      key_type VARCHAR(16),
      key_rule VARCHAR(16),
      layout_id INTEGER default 0,
      site_id INTEGER default 0,
      meta_title VARCHAR(255),
      meta_keywords VARCHAR(255),
      meta_description VARCHAR(255)
      ,PRIMARY KEY (ps_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX ".$table_prefix."cms_pages_settings_key_code ON ".$table_prefix."cms_pages_settings (key_code)";
			$sqls[] = "CREATE INDEX ".$table_prefix."cms_pages_settings_page_id ON ".$table_prefix."cms_pages_settings (page_id)";
		}

		if ($db_type == "db2") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_pages_settings AS INTEGER START WITH 1 INCREMENT BY 1 NO CACHE NO CYCLE";
			$sqls[] = "CREATE TRIGGER tr_".$table_prefix."cms_page_40 NO CASCADE BEFORE INSERT ON ".$table_prefix."cms_pages_settings REFERENCING NEW AS newr_".$table_prefix."cms_pages_settings FOR EACH ROW MODE DB2SQL WHEN (newr_".$table_prefix."cms_pages_settings.ps_id IS NULL ) begin atomic set newr_".$table_prefix."cms_pages_settings.ps_id = nextval for seq_".$table_prefix."cms_pages_settings; end";
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.24");
	}
	
	if (comp_vers("3.6.26", $current_db_version) == 1)
	{
		$mysql_sql  = "CREATE TABLE ".$table_prefix."ips_countries (
      `ip_start` INT(11) NOT NULL default '0',
      `ip_end` INT(11) NOT NULL default '0',
      `country_code` VARCHAR(4)
      ,PRIMARY KEY (ip_start,ip_end))";

		$postgre_sql = "CREATE TABLE ".$table_prefix."ips_countries (
      ip_start INT4 NOT NULL default '0',
      ip_end INT4 NOT NULL default '0',
      country_code VARCHAR(4)
      ,PRIMARY KEY (ip_start,ip_end))";

		$access_sql = " CREATE TABLE ".$table_prefix."ips_countries (
      [ip_start] INTEGER NOT NULL,
      [ip_end] INTEGER NOT NULL,
      [country_code] VARCHAR(4)
      ,PRIMARY KEY (ip_start,ip_end))";

		$db2_sql = " CREATE TABLE ".$table_prefix."ips_countries (
      ip_start INTEGER NOT NULL default 0,
      ip_end INTEGER NOT NULL default 0,
      country_code VARCHAR(4)
      ,PRIMARY KEY (ip_start,ip_end))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.26");
	}

	if (comp_vers("3.6.27", $current_db_version) == 1)
	{
		$mysql_sql  = "CREATE TABLE ".$table_prefix."user_types_countries (
      `type_id` INT(11) NOT NULL default '0',
      `country_id` INT(11) NOT NULL default '0'
      ,PRIMARY KEY (type_id,country_id))";

		$postgre_sql = "CREATE TABLE ".$table_prefix."user_types_countries (
      type_id INT4 NOT NULL default '0',
      country_id INT4 NOT NULL default '0'
      ,PRIMARY KEY (type_id,country_id))";

		$access_sql = " CREATE TABLE ".$table_prefix."user_types_countries (
      [type_id] INTEGER NOT NULL,
      [country_id] INTEGER NOT NULL
      ,PRIMARY KEY (type_id,country_id))";

		$db2_sql = " CREATE TABLE ".$table_prefix."user_types_countries (
      type_id INTEGER NOT NULL default 0,
      country_id INTEGER NOT NULL default 0
      ,PRIMARY KEY (type_id,country_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "user_types ADD COLUMN countries_all TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "user_types ADD COLUMN countries_all SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "user_types ADD COLUMN countries_all BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "user_types ADD COLUMN countries_all SMALLINT DEFAULT 1"
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "user_types SET countries_all=1 ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.27");
	}

	if (comp_vers("3.6.29", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "cms_blocks_settings ADD COLUMN ps_id INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "cms_blocks_settings ADD COLUMN ps_id INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "cms_blocks_settings ADD COLUMN ps_id INTEGER ",
			"db2"     => "ALTER TABLE " . $table_prefix . "cms_blocks_settings ADD COLUMN ps_id INTEGER DEFAULT 0"
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.29");
	}

	if (comp_vers("3.6.30", $current_db_version) == 1)
	{
		$sqls[] = "ALTER TABLE " . $table_prefix . "cms_pages_blocks ADD COLUMN block_key VARCHAR(32) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.30");
	}

	if (comp_vers("3.6.31", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "user_types ADD COLUMN order_min_goods_cost DOUBLE(16,2) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "user_types ADD COLUMN order_min_goods_cost FLOAT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "user_types ADD COLUMN order_min_goods_cost FLOAT",
			"db2"     => "ALTER TABLE " . $table_prefix . "user_types ADD COLUMN order_min_goods_cost DOUBLE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "user_types ADD COLUMN order_max_goods_cost DOUBLE(16,2) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "user_types ADD COLUMN order_max_goods_cost FLOAT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "user_types ADD COLUMN order_max_goods_cost FLOAT",
			"db2"     => "ALTER TABLE " . $table_prefix . "user_types ADD COLUMN order_max_goods_cost DOUBLE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "users ADD COLUMN order_min_goods_cost DOUBLE(16,2) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "users ADD COLUMN order_min_goods_cost FLOAT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "users ADD COLUMN order_min_goods_cost FLOAT",
			"db2"     => "ALTER TABLE " . $table_prefix . "users ADD COLUMN order_min_goods_cost DOUBLE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "users ADD COLUMN order_max_goods_cost DOUBLE(16,2) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "users ADD COLUMN order_max_goods_cost FLOAT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "users ADD COLUMN order_max_goods_cost FLOAT",
			"db2"     => "ALTER TABLE " . $table_prefix . "users ADD COLUMN order_max_goods_cost DOUBLE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN currencies_all TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN currencies_all SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN currencies_all BYTE ",
			"db2"     => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN currencies_all SMALLINT DEFAULT 1"
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "payment_systems SET currencies_all=1 ";

		$mysql_sql  = "CREATE TABLE ".$table_prefix."payment_currencies (
      `payment_id` INT(11) NOT NULL default '0',
      `currency_id` INT(11) NOT NULL default '0'
      ,PRIMARY KEY (payment_id,currency_id))";

		$postgre_sql  = "CREATE TABLE ".$table_prefix."payment_currencies (
      payment_id INT4 NOT NULL default '0',
      currency_id INT4 NOT NULL default '0'
      ,PRIMARY KEY (payment_id,currency_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."payment_currencies (
      [payment_id] INTEGER NOT NULL,
      [currency_id] INTEGER NOT NULL
      ,PRIMARY KEY (payment_id,currency_id))";

		$db2_sql  = "CREATE TABLE ".$table_prefix."payment_currencies (
      payment_id INTEGER NOT NULL default 0,
      currency_id INTEGER NOT NULL default 0
      ,PRIMARY KEY (payment_id,currency_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		$mysql_sql  = "CREATE TABLE ".$table_prefix."caches (
      `cache_id` INT(11) NOT NULL AUTO_INCREMENT,
      `cache_type` VARCHAR(32),
      `cache_name` VARCHAR(32),
      `cache_parameter` VARCHAR(32),
      `cache_date` DATETIME NOT NULL,
      `cache_data` TEXT NOT NULL
      ,KEY cache_name (cache_name)
      ,KEY cache_parameter (cache_parameter)
      ,KEY cache_type (cache_type)
      ,PRIMARY KEY (cache_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."caches START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."caches (
      cache_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."caches'),
      cache_type VARCHAR(32),
      cache_name VARCHAR(32),
      cache_parameter VARCHAR(32),
      cache_date TIMESTAMP NOT NULL,
      cache_data TEXT NOT NULL
      ,PRIMARY KEY (cache_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."caches (
      [cache_id]  COUNTER  NOT NULL,
      [cache_type] VARCHAR(32),
      [cache_name] VARCHAR(32),
      [cache_parameter] VARCHAR(32),
      [cache_date] DATETIME,
      [cache_data] LONGTEXT
      ,PRIMARY KEY (cache_id))";

		$db2_sql  = "CREATE TABLE ".$table_prefix."caches (
      cache_id INTEGER NOT NULL,
      cache_type VARCHAR(32),
      cache_name VARCHAR(32),
      cache_parameter VARCHAR(32),
      cache_date TIMESTAMP NOT NULL,
      cache_data LONG VARCHAR NOT NULL
      ,PRIMARY KEY (cache_id))";

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX ".$table_prefix."caches_cache_name ON ".$table_prefix."caches (cache_name)";
			$sqls[] = "CREATE INDEX ".$table_prefix."caches_cache_parameter ON ".$table_prefix."caches (cache_parameter)";
			$sqls[] = "CREATE INDEX ".$table_prefix."caches_cache_type ON ".$table_prefix."caches (cache_type)";
		}

		if ($db_type == "db2") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."caches AS INTEGER START WITH 1 INCREMENT BY 1 NO CACHE NO CYCLE";
			$sqls[] = "CREATE TRIGGER tr_".$table_prefix."caches NO CASCADE BEFORE INSERT ON ".$table_prefix."caches REFERENCING NEW AS newr_".$table_prefix."caches FOR EACH ROW MODE DB2SQL WHEN (newr_".$table_prefix."caches.cache_id IS NULL ) begin atomic set newr_".$table_prefix."caches.cache_id = nextval for seq_".$table_prefix."caches; end";
		}

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.31");
	}


	if (comp_vers("3.6.32", $current_db_version) == 1)
	{
		$mysql_sql  = "CREATE TABLE ".$table_prefix."export_fields (
      `field_id` INT(11) NOT NULL AUTO_INCREMENT,
      `template_id` INT(11) default '0',
      `field_order` INT(11) default '0',
      `field_title` VARCHAR(128),
      `field_source` TEXT
      ,PRIMARY KEY (field_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."export_fields START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."export_fields (
      field_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."export_fields'),
      template_id INT4 default '0',
      field_order INT4 default '0',
      field_title VARCHAR(128),
      field_source TEXT
      ,PRIMARY KEY (field_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."export_fields (
      [field_id]  COUNTER  NOT NULL,
      [template_id] INTEGER,
      [field_order] INTEGER,
      [field_title] VARCHAR(128),
      [field_source] LONGTEXT
      ,PRIMARY KEY (field_id))";

		$db2_sql  = "CREATE TABLE ".$table_prefix."export_fields (
      field_id INTEGER NOT NULL,
      template_id INTEGER default 0,
      field_order INTEGER default 0,
      field_title VARCHAR(128),
      field_source LONG VARCHAR
      ,PRIMARY KEY (field_id))";

		if ($db_type == "db2") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."export_fields AS INTEGER START WITH 1 INCREMENT BY 1 NO CACHE NO CYCLE";
			$sqls[] = "CREATE TRIGGER tr_".$table_prefix."export_f_44 NO CASCADE BEFORE INSERT ON ".$table_prefix."export_fields REFERENCING NEW AS newr_".$table_prefix."export_fields FOR EACH ROW MODE DB2SQL WHEN (newr_".$table_prefix."export_fields.field_id IS NULL ) begin atomic set newr_".$table_prefix."export_fields.field_id = nextval for seq_".$table_prefix."export_fields; end";
		}

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		$mysql_sql  = "CREATE TABLE ".$table_prefix."export_templates (
      `template_id` INT(11) NOT NULL AUTO_INCREMENT,
      `template_name` VARCHAR(255),
      `table_name` VARCHAR(64),
      `admin_id_added_by` INT(11) default '0',
      `admin_id_modified_by` INT(11) default '0',
      `date_added` DATETIME,
      `date_modified` DATETIME
      ,PRIMARY KEY (template_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."export_templates START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."export_templates (
      template_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."export_templates'),
      template_name VARCHAR(255),
      table_name VARCHAR(64),
      admin_id_added_by INT4 default '0',
      admin_id_modified_by INT4 default '0',
      date_added TIMESTAMP,
      date_modified TIMESTAMP
      ,PRIMARY KEY (template_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."export_templates (
      [template_id]  COUNTER  NOT NULL,
      [template_name] VARCHAR(255),
      [table_name] VARCHAR(64),
      [admin_id_added_by] INTEGER,
      [admin_id_modified_by] INTEGER,
      [date_added] DATETIME,
      [date_modified] DATETIME
      ,PRIMARY KEY (template_id))";

		$db2_sql  = "CREATE TABLE ".$table_prefix."export_templates (
      template_id INTEGER NOT NULL,
      template_name VARCHAR(255),
      table_name VARCHAR(64),
      admin_id_added_by INTEGER default 0,
      admin_id_modified_by INTEGER default 0,
      date_added TIMESTAMP,
      date_modified TIMESTAMP
      ,PRIMARY KEY (template_id))";

		if ($db_type == "db2") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."export_templates AS INTEGER START WITH 1 INCREMENT BY 1 NO CACHE NO CYCLE";
			$sqls[] = "CREATE TRIGGER tr_".$table_prefix."export_t_45 NO CASCADE BEFORE INSERT ON ".$table_prefix."export_templates REFERENCING NEW AS newr_".$table_prefix."export_templates FOR EACH ROW MODE DB2SQL WHEN (newr_".$table_prefix."export_templates.template_id IS NULL ) begin atomic set newr_".$table_prefix."export_templates.template_id = nextval for seq_".$table_prefix."export_templates; end";
		}

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "3.6.32");
	}

	if (comp_vers("4.0", $current_db_version) == 1)
	{
		$sqls[] = "DROP TABLE " . $table_prefix . "cms_pages_blocks ";

		$mysql_sql  = "CREATE TABLE ".$table_prefix."cms_pages_blocks (
      `pb_id` INT(11) NOT NULL AUTO_INCREMENT,
      `ps_id` INT(11) default '0',
      `block_id` INT(11) default '0',
      `block_key` VARCHAR(32),
      `frame_id` INT(11) default '0',
      `block_order` INT(11) default '0',
      `css_class` VARCHAR(128),
      `html_template` VARCHAR(128),
      `block_style` TEXT
      ,PRIMARY KEY (pb_id))";

		if ($db_type == "postgre") {
			$sqls[] = "DROP SEQUENCE seq_".$table_prefix."cms_pages_blocks ";
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_pages_blocks START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."cms_pages_blocks (
      pb_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."cms_pages_blocks'),
      ps_id INT4 default '0',
      block_id INT4 default '0',
      block_key VARCHAR(32),
      frame_id INT4 default '0',
      block_order INT4 default '0',
      css_class VARCHAR(128),
      html_template VARCHAR(128),
      block_style TEXT
      ,PRIMARY KEY (pb_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."cms_pages_blocks (
      [pb_id]  COUNTER  NOT NULL,
      [ps_id] INTEGER,
      [block_id] INTEGER,
      [block_key] VARCHAR(32),
      [frame_id] INTEGER,
      [block_order] INTEGER,
      [css_class] VARCHAR(128),
      [html_template] VARCHAR(128),
      [block_style] LONGTEXT
      ,PRIMARY KEY (pb_id))";

		$db2_sql  = "CREATE TABLE ".$table_prefix."cms_pages_blocks (
      pb_id INTEGER NOT NULL,
      ps_id INTEGER default 0,
      block_id INTEGER default 0,
      block_key VARCHAR(32),
      frame_id INTEGER default 0,
      block_order INTEGER default 0,
      css_class VARCHAR(128),
      html_template VARCHAR(128),
      block_style LONG VARCHAR
      ,PRIMARY KEY (pb_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "DROP TABLE " . $table_prefix . "cms_pages_settings ";

		$mysql_sql  = "CREATE TABLE ".$table_prefix."cms_pages_settings (
      `ps_id` INT(11) NOT NULL AUTO_INCREMENT,
      `page_id` INT(11),
      `key_code` VARCHAR(32),
      `key_type` VARCHAR(16),
      `key_rule` VARCHAR(16),
      `layout_id` INT(11) default '0',
      `site_id` INT(11) default '0',
      `meta_title` VARCHAR(255),
      `meta_keywords` VARCHAR(255),
      `meta_description` VARCHAR(255)
      ,KEY key_code (key_code)
      ,KEY page_id (page_id)
      ,PRIMARY KEY (ps_id))";

		if ($db_type == "postgre") {
			$sqls[] = "DROP SEQUENCE seq_".$table_prefix."cms_pages_settings ";
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_pages_settings START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."cms_pages_settings (
      ps_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."cms_pages_settings'),
      page_id INT4,
      key_code VARCHAR(32),
      key_type VARCHAR(16),
      key_rule VARCHAR(16),
      layout_id INT4 default '0',
      site_id INT4 default '0',
      meta_title VARCHAR(255),
      meta_keywords VARCHAR(255),
      meta_description VARCHAR(255)
      ,PRIMARY KEY (ps_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."cms_pages_settings (
      [ps_id]  COUNTER  NOT NULL,
      [page_id] INTEGER,
      [key_code] VARCHAR(32),
      [key_type] VARCHAR(16),
      [key_rule] VARCHAR(16),
      [layout_id] INTEGER,
      [site_id] INTEGER,
      [meta_title] VARCHAR(255),
      [meta_keywords] VARCHAR(255),
      [meta_description] VARCHAR(255)
      ,PRIMARY KEY (ps_id))";

		$db2_sql  = "CREATE TABLE ".$table_prefix."cms_pages_settings (
      ps_id INTEGER NOT NULL,
      page_id INTEGER,
      key_code VARCHAR(32),
      key_type VARCHAR(16),
      key_rule VARCHAR(16),
      layout_id INTEGER default 0,
      site_id INTEGER default 0,
      meta_title VARCHAR(255),
      meta_keywords VARCHAR(255),
      meta_description VARCHAR(255)
      ,PRIMARY KEY (ps_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql, "db2" => $db2_sql);
		$sqls[] = $sql_types[$db_type];

		// add default layouts 
		$sqls[] = "DELETE FROM ".$table_prefix."cms_layouts ";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_layouts (layout_id,layout_name,layout_order,layout_template,admin_template) VALUES (1 , 'THREE_COLUMNS_MSG' , 1 , 'layout_three_columns.html' , 'admin_cms_columns3.html' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_layouts (layout_id,layout_name,layout_order,layout_template,admin_template) VALUES (2 , 'TWO_COLUMNS_MSG' , 2 , 'layout_two_columns.html' , 'admin_cms_columns2.html' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_layouts (layout_id,layout_name,layout_order,layout_template,admin_template) VALUES (3 , 'ONE_COLUMN_MSG' , 3 , 'layout_one_column.html' , 'admin_cms_column1.html' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_layouts (layout_id,layout_name,layout_order,layout_template,admin_template) VALUES (4 , 'HOT_LAYOUT_MSG' , 4 , 'layout_hot.html' , 'admin_cms_hot_layout.html' )";

		// add frame structure for layouts
		$sqls[] = "DELETE FROM ".$table_prefix."cms_frames ";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (frame_id,layout_id,frame_name,tag_name) VALUES (1 , 1 , 'HEADER_MSG' , 'header' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (frame_id,layout_id,frame_name,tag_name) VALUES (2 , 1 , 'LEFT_COLUMN_MSG' , 'left' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (frame_id,layout_id,frame_name,tag_name) VALUES (3 , 1 , 'MIDDLE_COLUMN_MSG' , 'middle' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (frame_id,layout_id,frame_name,tag_name) VALUES (4 , 1 , 'RIGHT_COLUMN_MSG' , 'right' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (frame_id,layout_id,frame_name,tag_name) VALUES (5 , 1 , 'FOOTER_MSG' , 'footer' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (frame_id,layout_id,frame_name,tag_name) VALUES (6 , 2 , 'HEADER_MSG' , 'header' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (frame_id,layout_id,frame_name,tag_name) VALUES (7 , 2 , 'LEFT_COLUMN_MSG' , 'left' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (frame_id,layout_id,frame_name,tag_name) VALUES (8 , 2 , 'MIDDLE_COLUMN_MSG' , 'middle' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (frame_id,layout_id,frame_name,tag_name) VALUES (9 , 2 , 'FOOTER_MSG' , 'footer' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (frame_id,layout_id,frame_name,tag_name) VALUES (10 , 3 , 'HEADER_MSG' , 'header' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (frame_id,layout_id,frame_name,tag_name) VALUES (11 , 3 , 'MIDDLE_COLUMN_MSG' , 'middle' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (frame_id,layout_id,frame_name,tag_name) VALUES (12 , 3 , 'FOOTER_MSG' , 'footer' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (frame_id,layout_id,frame_name,tag_name) VALUES (13 , 4 , 'HEADER_MSG' , 'header' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (frame_id,layout_id,frame_name,tag_name) VALUES (14 , 4 , 'HOT_TITLE' , 'hot' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (frame_id,layout_id,frame_name,tag_name) VALUES (15 , 4 , 'LEFT_COLUMN_MSG' , 'left' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (frame_id,layout_id,frame_name,tag_name) VALUES (16 , 4 , 'MIDDLE_COLUMN_MSG' , 'middle' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (frame_id,layout_id,frame_name,tag_name) VALUES (17 , 4 , 'RIGHT_COLUMN_MSG' , 'right' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (frame_id,layout_id,frame_name,tag_name) VALUES (18 , 4 , 'FOOTER_MSG' , 'footer' )";

		// frame settings
		$frame_settings = array(
			"left" => array("tag" => "left", "id" => "2"),
			"left_column_hide" => array("tag" => "left", "id" => "2", "style" => "display: none;", "type" => "bool"),
			"left_column_width" => array("tag" => "left", "id" => "2", "style" => "width: ", "type" => "text"),
			"middle" => array("tag" => "middle", "id" => "3"),
			"middle_column_hide" => array("tag" => "middle","id" => "3", "style" => "display: none;", "type" => "bool"),
			"middle_column_width" => array("tag" => "middle","id" => "3", "style" => "width: ", "type" => "text"),
			"right" => array("tag" => "right", "id" => "4"),
			"right_column_hide" => array("tag" => "right", "id" => "4", "style" => "display: none;", "type" => "bool"),
			"right_column_width" => array("tag" => "right", "id" => "4", "style" => "width: ", "type" => "text"),
		);

		// add basic layouts
		$sqls[] = "DELETE FROM ".$table_prefix."cms_modules ";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_modules (module_id,module_order,module_code,module_name) VALUES (1 , 1 , 'global' , 'GLOBAL_MSG' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_modules (module_id,module_order,module_code,module_name) VALUES (2 , 2 , 'cart' , 'PRODUCTS_TITLE' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_modules (module_id,module_order,module_code,module_name) VALUES (3 , 3 , 'articles' , '{ARTICLES_TITLE}: {category_name}' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_modules (module_id,module_order,module_code,module_name) VALUES (4 , 4 , 'helpdesk' , 'HELPDESK_MSG' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_modules (module_id,module_order,module_code,module_name) VALUES (5 , 5 , 'forum' , 'FORUM_TITLE' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_modules (module_id,module_order,module_code,module_name) VALUES (6 , 6 , 'ads' , 'ADS_TITLE' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_modules (module_id,module_order,module_code,module_name) VALUES (7 , 7 , 'manuals' , 'MANUALS_TITLE' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_modules (module_id,module_order,module_code,module_name) VALUES (8 , 8 , 'filters' , 'FILTERS_MSG' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_modules (module_id,module_order,module_code,module_name) VALUES (9 , 9 , 'custom_blocks' , 'CUSTOM_BLOCKS_MSG' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_modules (module_id,module_order,module_code,module_name) VALUES (10 , 10 , 'custom_menus' , 'CUSTOM_MENUS_MSG' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_modules (module_id,module_order,module_code,module_name) VALUES (11 , 11 , 'banners' , 'BANNERS_MSG' )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_modules (module_id,module_order,module_code,module_name) VALUES (12 , 12 , 'user_account' , 'USER_ACCOUNT_MSG' )";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "");


		// pages structure
		$cms_pages = array(
			"index" => array("id" => 1,"code" => "index", "module" => 1, "order" => 1, "name" => "HOME_PAGE_LAYOUT_MSG", "keys" => false),
			"site_map" => array("id" => 2, "code" => "site_map", "module" => 1, "order" => 2, "name" => "SITE_MAP_TITLE", "keys" => false),
			"site_search" => array("id" => 3, "code" => "site_search", "module" => 1, "order" => 3, "name" => "FULL_SITE_SEARCH_MSG", "keys" => false),
			"custom_page" => array("id" => 4, "code" => "custom_page", "module" => 1, "order" => 4, "name" => "CUSTOM_PAGES_MSG", "keys" => false),
			"forgot_password" => array("id" => 5, "code" => "forgot_password", "module" => 1, "order" => 5, "name" => "FORGOTTEN_PASSWORD_MSG", "keys" => false),
			"reset_password" => array("id" => 6, "code" => "reset_password", "module" => 1, "order" => 6, "name" => "RESET_PASSWORD_INFO_MSG", "keys" => false),
			"polls_previous" => array("id" => 7, "code" => "polls", "module" => 1, "order" => 7, "name" => "PREVIOUS_POLLS_MSG", "keys" => false),

			"products_list" => array("id" => 8, "code" => "products_list", "module" => 2, "order" => 1, "name" => "PRODUCTS_LISTING_PAGE_MSG", "keys" => false),
			"products_search" => array("id" => 9, "code" => "products_search_results", "module" => 2, "order" => 2, "name" => "PRODUCTS_SEARCH_RESULTS_MSG", "keys" => false),
			"products_details" => array("id" => 10, "code" => "product_details", "module" => 2, "order" => 3, "name" => "PRODUCTS_DETAILS_PAGE_MSG", "keys" => false),
			"products_options" => array("id" => 11, "code" => "product_options", "module" => 2, "order" => 4, "name" => "PRODUCT_OPTIONS_MSG", "keys" => false),
			"products_compare" => array("id" => 12, "code" => "products_compare", "module" => 2, "order" => 6, "name" => "PRODUCTS_COMPARE_RESULTS_MSG", "keys" => false),
			"products_advanced_search" => array("id" => 13, "code" => "products_search_advanced", "module" => 2, "order" => 7, "name" => "ADVANCED_SEARCH_TITLE", "keys" => false),
			"wishlist" => array("id" => 14, "code" => "wishlist", "module" => 2, "order" => 8, "name" => "WISHLIST_MSG", "keys" => false),
			"products_releases" => array("id" => 15, "code" => "products_releases", "module" => 2, "order" => 9, "name" => "RELEASES_TITLE", "keys" => false),
			"products_changes_log" => array("id" => 16, "code" => "products_changes_log", "module" => 2, "order" => 10, "name" => "CHANGES_LOG_TITLE", "keys" => false),

			"basket" => array("id" => 17, "code" => "cart", "module" => 2, "order" => 10, "name" => "ADMIN_BASKET_MSG", "keys" => false),
			"cart_save" => array("id" => 18, "code" => "cart_save", "module" => 2, "order" => 11, "name" => "SAVE_CART_BUTTON", "keys" => false),
			"cart_retrieve" => array("id" => 19, "code" => "cart_retrieve", "module" => 2, "order" => 12, "name" => "RETRIEVE_CART_BUTTON", "keys" => false),
			"subscriptions" => array("id" => 20, "code" => "subscriptions", "module" => 2, "order" => 13, "name" => "SUBSCRIPTIONS_MSG", "keys" => false),

			"checkout_login" => array("id" => 21, "code" => "checkout", "module" => 2, "order" => 14, "name" => "CHECKOUT_LOGIN_TITLE", "keys" => false),
			"order_info" => array("id" => 22, "code" => "order_info", "module" => 2, "order" => 15, "name" => "CHECKOUT_INFO_TITLE", "keys" => false),
			"order_payment_details" => array("id" => 23, "code" => "order_payment_details", "module" => 2, "order" => 16, "name" => "CHECKOUT_PAYMENT_TITLE", "keys" => false),
			"order_confirmation" => array("id" => 24, "code" => "order_confirmation", "module" => 2, "order" => 17, "name" => "CHECKOUT_CONFIRM_TITLE", "keys" => false),
			"order_final" => array("id" => 25, "code" => "order_final", "module" => 2, "order" => 18, "name" => "FINAL_CHECKOUT_PAGE_MSG", "keys" => false),

			"user_login" => array("id" => 26, "code" => "user_login", "module" => 1, "order" => 8, "name" => "LOGIN_TITLE", "keys" => false),
			"user_profile" => array("id" => 27, "code" => "user_profile", "module" => 1, "order" => 9, "name" => "PROFILE_SETTINGS_INFO_MSG", "keys" => false),
			"userhome_pages" => array("id" => 28, "code" => "user_home", "module" => 12, "order" => 1, "name" => "USER_HOME_TITLE", "keys" => false),
			"user_list" => array("id" => 29, "code" => "user_products_list", "module" => 1, "order" => 11, "name" => "USER_LISTING_PAGE_MSG", "keys" => false),

			"a_list" => array("id" => 30, "code" => "articles_list", "module" => 3, "order" => 1, "name" => "LISTING_PAGE_MSG", "keys" => false),
			"a_details" => array("id" => 31, "code" => "article_details", "module" => 3, "order" => 2, "name" => "DETAILS_PAGE_MSG", "keys" => false),

			"support_new" => array("id" => 32, "code" => "ticket_new", "module" => 4, "order" => 1, "name" => "NEW_TICKET_PAGE_MSG", "keys" => false),
			"support_reply" => array("id" => 33, "code" => "ticket_reply", "module" => 4, "order" => 2, "name" => "REPLYING_TICKETS_MSG", "keys" => false),

			"forum_list" => array("id" => 34, "code" => "forum_list", "module" => 5, "order" => 1, "name" => "FORUMS_LIST_PAGE_MSG", "keys" => false),
			"forum_topics" => array("id" => 35, "code" => "forum_topics", "module" => 5, "order" => 2, "name" => "FORUM_TOPICS_PAGE_MSG", "keys" => false),
			"forum_topic" => array("id" => 36, "code" => "forum_topic", "module" => 5, "order" => 3, "name" => "FORUM_TOPICS_THREAD_MSG", "keys" => false),

			"ads_list" => array("id" => 37, "code" => "ads_list", "module" => 6, "order" => 1, "name" => "LISTING_PAGE_MSG", "keys" => false),
			"ads_details" => array("id" => 38, "code" => "ad_details", "module" => 6, "order" => 2, "name" => "DETAILS_PAGE_MSG", "keys" => false),
			"ads_compare" => array("id" => 39, "code" => "ads_compare", "module" => 6, "order" => 3, "name" => "ADS_COMPARE_TITLE", "keys" => false),
			"ads_search" => array("id" => 40, "code" => "ads_search_advanced", "module" => 6, "order" => 4, "name" => "ADS_ADVANCED_SEARCH_MSG", "keys" => false),

			"manuals_list" => array("id" => 41, "code" => "manuals_list", "module" => 7, "order" => 1, "name" => "MANUALS_LIST_PAGE_MSG", "keys" => false),
			"manuals_articles" => array("id" => 42, "code" => "manual_articles", "module" => 7, "order" => 2, "name" => "MANUAL_ARTICLES_PAGE_MSG", "keys" => false),
			"manuals_article_details" => array("id" => 43, "code" => "manual_article_details", "module" => 7, "order" => 3, "name" => "MANUAL_ARTICLE_DETAILS_MSG", "keys" => false),
			"manuals_search" => array("id" => 44, "code" => "manuals_search_results", "module" => 7, "order" => 4, "name" => "MANUAL_SEARCH_MSG", "keys" => false),

			// new pages
			"product_reviews" => array("new" => true, "block_id" => 120, "id" => 45, "code" => "product_reviews", "module" => 2, "order" => 5, "name" => "PRODUCTS_REVIEWS_MSG", "keys" => false),
			"article_reviews" => array("new" => true, "block_id" => 121, "id" => 46, "code" => "article_reviews", "module" => 3, "order" => 3, "name" => "ARTICLES_REVIEWS_MSG", "keys" => false),
			"download" => array("new" => true, "block_id" => 122, "id" => 47, "code" => "download", "module" => 1, "order" => 8, "name" => "DOWNLOAD_TITLE", "keys" => false),
			"forum_topic_new" => array("new" => true, "block_id" => 123, "id" => 48, "code" => "forum_topic_new", "module" => 5, "order" => 4, "name" => "NEW_TOPIC_MSG", "keys" => false),

			// new account pages
			"user_account_profile" => array("new" => true, "block_id" => 124, "id" => 49, "code" => "user_account_profile", "module" => 12, "order" => 2, "name" => "EDIT_PROFILE_MSG", "keys" => false),
			"user_change_password" => array("new" => true, "block_id" => 125, "id" => 50, "code" => "user_change_password", "module" => 12, "order" => 3, "name" => "CHANGE_PASSWORD_MSG", "keys" => false),
			"user_change_type" => array("new" => true, "block_id" => 126, "id" => 51, "code" => "user_change_type", "module" => 12, "order" => 4, "name" => "UPGRADE_DOWNGRADE_MSG", "keys" => false),
			"user_carts" => array("new" => true, "block_id" => 127, "id" => 52, "code" => "user_carts", "module" => 12, "order" => 5, "name" => "MY_SAVED_CARTS_MSG", "keys" => false),
			"user_wishlist" => array("new" => true, "block_id" => 128, "id" => 53, "code" => "user_wishlist", "module" => 12, "order" => 6, "name" => "MY_WISHLIST_MSG", "keys" => false),
			"user_orders" => array("new" => true, "block_id" => 129, "id" => 54, "code" => "user_orders", "module" => 12, "order" => 7, "name" => "{MY_ORDERS_MSG}: {LIST_MSG}", "keys" => false),
			"user_order" => array("new" => true, "block_id" => 130, "id" => 55, "code" => "user_order", "module" => 12, "order" => 8, "name" => "{MY_ORDERS_MSG}: {VIEW_DETAILS_MSG}", "keys" => false),
			"user_order_update" => array("new" => true, "block_id" => 131, "id" => 56, "code" => "user_order_update", "module" => 12, "order" => 9, "name" => "{MY_ORDERS_MSG}: {EDIT_MSG}", "keys" => false),
			"user_support" => array("new" => true, "block_id" => 132, "id" => 57, "code" => "user_support", "module" => 12, "order" => 10, "name" => "MY_SUPPORT_ISSUES_MSG", "keys" => false),
			"user_ads" => array("new" => true, "block_id" => 133, "id" => 58, "code" => "user_ads", "module" => 12, "order" => 11, "name" => "{MY_ADS_MSG}: {LIST_MSG}", "keys" => false),
			"user_ad" => array("new" => true, "block_id" => 134, "id" => 59, "code" => "user_ad", "module" => 12, "order" => 12, "name" => "{MY_ADS_MSG}: {EDIT_MSG}", "keys" => false),
			"user_affiliate_sales" => array("new" => true, "block_id" => 135, "id" => 60, "code" => "user_affiliate_sales", "module" => 12, "order" => 13, "name" => "AFFILIATE_SALES_MSG", "keys" => false),
			"user_affiliate_items" => array("new" => true, "block_id" => 136, "id" => 61, "code" => "user_affiliate_items", "module" => 12, "order" => 14, "name" => "{AFFILIATE_SALES_MSG}: {PRODUCTS_LIST_MSG}", "keys" => false),
			"user_products" => array("new" => true, "block_id" => 137, "id" => 62, "code" => "user_products", "module" => 12, "order" => 15, "name" => "{MY_PRODUCTS_MSG}: {LIST_MSG}", "keys" => false),
			"user_product" => array("new" => true, "block_id" => 138, "id" => 63, "code" => "user_product", "module" => 12, "order" => 16, "name" => "{MY_PRODUCTS_MSG}: {EDIT_MSG}", "keys" => false),
			"user_product_options" => array("new" => true, "block_id" => 139, "id" => 64, "code" => "user_product_options", "module" => 12, "order" => 17, "name" => "{OPTIONS_AND_COMPONENTS_MSG}: {LIST_MSG}", "keys" => false),
			"user_product_option" => array("new" => true, "block_id" => 140, "id" => 65, "code" => "user_product_option", "module" => 12, "order" => 18, "name" => "{OPTIONS_AND_COMPONENTS_MSG}: {EDIT_OPTION_MSG}", "keys" => false),
			"user_product_subcomponent" => array("new" => true, "block_id" => 141, "id" => 66, "code" => "user_product_subcomponent", "module" => 12, "order" => 19, "name" => "{OPTIONS_AND_COMPONENTS_MSG}: {EDIT_SUBCOMP_MSG}", "keys" => false),
			"user_product_subcomponents" => array("new" => true, "block_id" => 142, "id" => 67, "code" => "user_product_subcomponents", "module" => 12, "order" => 20, "name" => "{OPTIONS_AND_COMPONENTS_MSG}: {EDIT_SUBCOMP_SELECTION_MSG}", "keys" => false),
			"user_payments" => array("new" => true, "block_id" => 143, "id" => 68, "code" => "user_payments", "module" => 12, "order" => 21, "name" => "{COMMISSION_PAYMENTS_MSG}: {LIST_MSG}", "keys" => false),
			"user_payment" => array("new" => true, "block_id" => 144, "id" => 69, "code" => "user_payment", "module" => 12, "order" => 22, "name" => "{COMMISSION_PAYMENTS_MSG}: {VIEW_DETAILS_MSG}", "keys" => false),
			"user_merchant_orders" => array("new" => true, "block_id" => 145, "id" => 70, "code" => "user_merchant_orders", "module" => 12, "order" => 23, "name" => "{MY_SALES_ORDERS_MSG}: {LIST_MSG}", "keys" => false),
			"user_merchant_order" => array("new" => true, "block_id" => 146, "id" => 71, "code" => "user_merchant_order", "module" => 12, "order" => 24, "name" => "{MY_SALES_ORDERS_MSG}: {VIEW_DETAILS_MSG}", "keys" => false),
			"user_merchant_sales" => array("new" => true, "block_id" => 147, "id" => 72, "code" => "user_merchant_sales", "module" => 12, "order" => 25, "name" => "MERCHANT_SALES_MSG", "keys" => false),
			"user_merchant_items" => array("new" => true, "block_id" => 148, "id" => 73, "code" => "user_merchant_items", "module" => 12, "order" => 26, "name" => "{MERCHANT_SALES_MSG}: {PRODUCTS_LIST_MSG}", "keys" => false),
			"user_reminders" => array("new" => true, "block_id" => 149, "id" => 74, "code" => "user_reminders", "module" => 12, "order" => 27, "name" => "{MY_REMINDERS_MSG}: {LIST_MSG}", "keys" => false),
			"user_reminder" => array("new" => true, "block_id" => 150, "id" => 75, "code" => "user_reminder", "module" => 12, "order" => 28, "name" => "{MY_REMINDERS_MSG}: {EDIT_MSG}", "keys" => false),
			"user_psd_list" => array("new" => true, "block_id" => 151, "id" => 76, "code" => "user_psd_list", "module" => 12, "order" => 29, "name" => "{PAYMENT_DETAILS_MSG}: {LIST_MSG}", "keys" => false),
			"user_psd_update" => array("new" => true, "block_id" => 152, "id" => 77, "code" => "user_psd_update", "module" => 12, "order" => 30, "name" => "{PAYMENT_DETAILS_MSG}: {EDIT_MSG}", "keys" => false),
			"user_product_registrations" => array("new" => true, "block_id" => 153, "id" => 78, "code" => "user_product_registrations", "module" => 12, "order" => 31, "name" => "MY_PRODUCT_REGISTRATIONS_MSG", "keys" => false),
			"user_product_registration" => array("new" => true, "block_id" => 154, "id" => 79, "code" => "user_product_registration", "module" => 12, "order" => 32, "name" => "REGISTER_PRODUCT_MSG", "keys" => false),
		);

		// add pages
		$sqls[] = "DELETE FROM ".$table_prefix."cms_pages ";
		foreach ($cms_pages as $old_code => $page) {
			$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
			$sql.= $db->tosql($page["id"], INTEGER).",";
			$sql.= $db->tosql($page["module"], INTEGER).",";
			$sql.= $db->tosql($page["order"], INTEGER).",";
			$sql.= $db->tosql($page["code"], TEXT).",";
			$sql.= $db->tosql($page["name"], TEXT).")";
			$sqls[] = $sql;
		}
		run_queries($sqls, $queries_success, $queries_failed, $errors, "");

		// blocks structure
		$cms_blocks = array(
			"language_block" => array("id" => "1", "module" => "1", "asc" => "1", "code" => "language", "name" => "LANGUAGE_TITLE", "script" => "block_language.php", "pages" => "1"),
			"user_profile_form" => array("id" => "2", "module" => "1", "asc" => "2", "code" => "user_profile", "name" => "PROFILE_TITLE", "script" => "block_user_profile.php", "pages" => "1"),
			"subscribe_block" => array("id" => "3", "module" => "1", "asc" => "3", "code" => "subscribe", "name" => "NEWSLETTER_SUBSRIPTION_MSG", "script" => "block_subscribe.php", "pages" => "1"),
			"login_block" => array("id" => "4", "module" => "1", "asc" => "4", "code" => "login", "name" => "LOGIN_TITLE", "script" => "block_login.php", "pages" => "1"),
			"advanced_login" => array("id" => "5", "module" => "1", "asc" => "5", "code" => "login_advanced", "name" => "{LOGIN_TITLE}: {ADVANCED_MSG}", "script" => "block_login_advanced.php", "pages" => "1"),
			"forgot_password" => array("id" => "6", "module" => "1", "asc" => "6", "code" => "forgot_password", "name" => "FORGOTTEN_PASSWORD_SETTINGS_MSG", "script" => "block_password_forgot.php", "pages" => "1"),
			"reset_password" => array("id" => "7", "module" => "1", "asc" => "7", "code" => "reset_password", "name" => "RESET_PASSWORD_INFO_MSG", "script" => "block_password_reset.php", "pages" => "1"),
			"custom_page_body" => array("id" => "8", "module" => "1", "asc" => "8", "code" => "custom_page_body", "name" => "CUSTOM_PAGE_BODY_MSG", "script" => "block_custom_page_body.php", "pages" => "1"),
			"layouts_block" => array("id" => "9", "module" => "1", "asc" => "9", "code" => "designs", "name" => "DESIGNS_MSG", "script" => "block_designs.php", "pages" => "1"),
			"poll_block" => array("id" => "10", "module" => "1", "asc" => "10", "code" => "poll", "name" => "POLL_TITLE", "script" => "block_poll.php", "pages" => "1"),
			"polls_previous_list" => array("id" => "11", "module" => "1", "asc" => "11", "code" => "polls_previous_list", "name" => "PREVIOUS_POLLS_MSG", "script" => "block_polls_previous_list.php", "pages" => "1"),
			"site_map_block" => array("id" => "12", "module" => "1", "asc" => "12", "code" => "site_map", "name" => "SITE_MAP_TITLE", "script" => "block_site_map.php", "pages" => "1"),
			"sms_test_block" => array("id" => "13", "module" => "1", "asc" => "13", "code" => "sms_test", "name" => "SMS_TEST_TITLE", "script" => "block_sms_test.php", "pages" => "1"),
			"userhome_breadcrumb" => array("id" => "14", "module" => "12", "asc" => "1", "code" => "user_account_breadcrumb", "name" => "{USER_ACCOUNT_MSG}: {BREADCRUMB_MSG}", "script" => "block_user_account_breadcrumb.php", "pages" => "1"),
			"userhome_main_block" => array("id" => "15", "module" => "12", "asc" => "2", "code" => "user_home", "name" => "{USER_ACCOUNT_MSG}: {USER_HOME_TITLE}", "script" => "block_user_home.php", "pages" => "1"),
			"site_search_form" => array("id" => "16", "module" => "1", "asc" => "16", "code" => "site_search_form", "name" => "{ADMIN_SITE_MSG}: {SEARCH_FORM_MSG}", "script" => "block_site_search_form.php", "pages" => "1"),
			"site_search_results" => array("id" => "17", "module" => "1", "asc" => "17", "code" => "site_search_results", "name" => "{ADMIN_SITE_MSG}: {SEARCH_RESULTS_MSG}", "script" => "block_site_search_results.php", "pages" => "1"),

			"products_block" => array("id" => "18", "module" => "2", "asc" => "1", "code" => "products_list", "name" => "PRODUCTS_LIST_MSG", "script" => "block_products_list.php", "pages" => "1"),
			"details_block" => array("id" => "19", "module" => "2", "asc" => "2", "code" => "product_details", "name" => "PRODUCT_DETAILS_MSG", "script" => "block_product_details.php", "pages" => "1"),
			"categories_block" => array("id" => "20", "module" => "2", "asc" => "3", "code" => "categories_list", "name" => "CATEGORIES_LIST_MSG", "script" => "block_categories_list.php", "pages" => "1"),
			"subcategories_block" => array("parent" => "categories_block", "id" => "20", "module" => "2", "asc" => "3", "code" => "categories_list", "name" => "CATEGORIES_LIST_MSG", "script" => "block_categories_list.php", "pages" => "1"),
			"chained_menu" => array("parent" => "categories_block", "id" => "20", "module" => "2", "asc" => "3", "code" => "categories_list", "name" => "CATEGORIES_LIST_MSG", "script" => "block_categories_list.php", "pages" => "1"),
			"category_description_block" => array("id" => "21", "module" => "2", "asc" => "4", "code" => "category_description", "name" => "CATEGORY_INFO_MSG", "script" => "block_category_description.php", "pages" => "1"),
			"basket_block" => array("id" => "22", "module" => "2", "asc" => "5", "code" => "shopping_cart", "name" => "CART_TITLE", "script" => "block_basket.php", "pages" => "1"),
			"basket_recommended_block" => array("id" => "23", "module" => "2", "asc" => "6", "code" => "cart_recommended_products", "name" => "PRODUCTS_RECOMMENDED_TITLE", "script" => "block_basket_recommended.php", "pages" => "1"),
			"cart_block" => array("id" => "24", "module" => "2", "asc" => "7", "code" => "mini_cart", "name" => "SMALL_CART_MSG", "script" => "block_cart.php", "pages" => "1"),
			"cart_retrieve" => array("id" => "25", "module" => "2", "asc" => "8", "code" => "cart_retrieve", "name" => "RETRIEVE_CART_BUTTON", "script" => "block_cart_retrieve.php", "pages" => "1"),
			"cart_save" => array("id" => "26", "module" => "2", "asc" => "9", "code" => "cart_save.php", "name" => "SAVE_CART_BUTTON", "script" => "block_cart_save.php", "pages" => "1"),
			"search_block" => array("id" => "27", "module" => "2", "asc" => "10", "code" => "products_search", "name" => "{PRODUCTS_TITLE} {SEARCH_FORM_MSG}", "script" => "block_search.php", "pages" => "1"),
			"products_advanced_search" => array("id" => "28", "module" => "2", "asc" => "11", "code" => "products_advanced_search", "name" => "PRODUCTS_SEARCH_ADVANCED_MSG", "script" => "block_products_search_advanced.php", "pages" => "1"),
			"manufacturers_block" => array("id" => "29", "module" => "2", "asc" => "12", "code" => "manufacturers_list", "name" => "MANUFACTURERS_TITLE", "script" => "block_manufacturers.php", "pages" => "1"),
			"manufacturer_info_block" => array("id" => "30", "module" => "2", "asc" => "13", "code" => "manufacturer_info", "name" => "MANUFACTURER_INFO_MSG", "script" => "block_manufacturer_info.php", "pages" => "1"),

			"merchants_block" => array("id" => "31", "module" => "2", "asc" => "14", "code" => "merchants_list", "name" => "MERCHANTS_TITLE", "script" => "block_merchants.php", "pages" => "1"),
			"merchant_info_block" => array("id" => "32", "module" => "2", "asc" => "15", "code" => "merchant_info", "name" => "MERCHANT_INFO_MSG", "script" => "block_merchant_info.php", "pages" => "1"),
			"merchant_contact_block" => array("id" => "33", "module" => "2", "asc" => "16", "code" => "merchant_contact", "name" => "CONTACT_MERCHANT_TITLE", "script" => "block_merchant_contact.php", "pages" => "1"),
			"offers_block" => array("id" => "34", "module" => "2", "asc" => "17", "code" => "products_offers", "name" => "SPECIAL_OFFER_MSG", "script" => "block_offers.php", "pages" => "1"),
			"products_breadcrumb" => array("id" => "35", "module" => "2", "asc" => "17", "code" => "products_breadcrumb", "name" => "{PRODUCTS_TITLE}: {BREADCRUMB_MSG}", "script" => "block_products_breadcrumb.php", "pages" => "1"),
			"products_changes_log" => array("id" => "36", "module" => "2", "asc" => "19", "code" => "products_changes_log", "name" => "CHANGES_LOG_TITLE", "script" => "block_products_changes_log.php", "pages" => "1"),
			"products_compare" => array("id" => "37", "module" => "2", "asc" => "20", "code" => "products_compare", "name" => "PRODUCTS_COMPARE_RESULTS_MSG", "script" => "block_products_compare.php", "pages" => "1"),
			"products_fast_add" => array("id" => "38", "module" => "2", "asc" => "21", "code" => "products_fast_add", "name" => "FAST_PRODUCT_ADDING_MSG", "script" => "block_products_fast_add.php", "pages" => "1"),
			"products_latest" => array("id" => "39", "module" => "2", "asc" => "22", "code" => "products_latest", "name" => "{PRODUCTS_TITLE} {LATEST_TITLE}", "script" => "block_products_latest.php", "pages" => "1"),
			"products_options" => array("id" => "40", "module" => "2", "asc" => "23", "code" => "products_options", "name" => "PRODUCT_OPTIONS_MSG", "script" => "block_product_options.php", "pages" => "1"),
			"products_recently_viewed" => array("id" => "41", "module" => "2", "asc" => "24", "code" => "products_recently_viewed", "name" => "{PRODUCTS_TITLE} {RECENTLY_VIEWED_MSG}", "script" => "block_products_recently.php", "pages" => "1"),
			"products_recommended" => array("id" => "42", "module" => "2", "asc" => "25", "code" => "products_recommended", "name" => "PRODUCTS_RECOMMENDED_TITLE", "script" => "block_products_recommended.php", "pages" => "1"),
			"products_releases" => array("id" => "43", "module" => "2", "asc" => "26", "code" => "products_releases", "name" => "{PRODUCTS_TITLE} {RELEASES_TITLE}", "script" => "block_products_releases.php", "pages" => "1"),
			"products_releases_hot" => array("id" => "44", "module" => "2", "asc" => "27", "code" => "products_releases_hot", "name" => "{PRODUCTS_TITLE} {HOT_RELEASES_MSG}", "script" => "block_products_releases_hot.php", "pages" => "1"),
			"products_top_sellers" => array("id" => "45", "module" => "2", "asc" => "28", "code" => "products_top_sellers", "name" => "TOP_SELLERS_TITLE", "script" => "block_products_top_sellers.php", "pages" => "1"),
			"products_top_viewed" => array("id" => "46", "module" => "2", "asc" => "29", "code" => "products_top_viewed", "name" => "TOP_VIEWED_TITLE", "script" => "block_products_top_viewed.php", "pages" => "1"),
			"top_products_block" => array("id" => "47", "module" => "2", "asc" => "30", "code" => "products_top_rated", "name" => "TOP_RATED_TITLE", "script" => "block_products_top_rated.php", "pages" => "1"),
			"related_block" => array("id" => "48", "module" => "2", "asc" => "31", "code" => "products_related", "name" => "{PRODUCTS_TITLE} {RELATED_MSG}", "script" => "block_products_related.php", "pages" => "1"),
			"related_purchase" => array("id" => "49", "module" => "2", "asc" => "32", "code" => "products_related_purchase", "name" => "WHO_BOUGHT_THIS_SHORT_MSG", "script" => "block_products_related_purchase.php", "pages" => "1"),
			"checkout_breadcrumb" => array("id" => "50", "module" => "2", "asc" => "33", "code" => "checkout_breadcrumb", "name" => "{ADMIN_CHECKOUT_MSG} {BREADCRUMB_MSG}", "script" => "block_checkout_breadcrumb.php", "pages" => "1"),
			"checkout_final" => array("id" => "51", "module" => "2", "asc" => "34", "code" => "checkout_final", "name" => "FINAL_CHECKOUT_MSG", "script" => "block_checkout_final.php", "pages" => "1"),
			"checkout_login" => array("id" => "52", "module" => "2", "asc" => "35", "code" => "checkout_login", "name" => "CHECKOUT_LOGIN_TITLE", "script" => "block_checkout_login.php", "pages" => "1"),
			"order_cart" => array("id" => "53", "module" => "2", "asc" => "36", "code" => "order_cart", "name" => "ORDER_CART_MSG", "script" => "block_order_cart.php", "pages" => "1"),
			"order_data_form" => array("id" => "54", "module" => "2", "asc" => "37", "code" => "order_form", "name" => "ORDER_FORM_MSG", "script" => "block_order_info.php", "pages" => "1"),
			"order_data_preview" => array("id" => "55", "module" => "2", "asc" => "38", "code" => "order_preview", "name" => "ORDER_PREVIEW_MSG", "script" => "block_order_data_preview.php", "pages" => "1"),
			"order_payment_details_form" => array("id" => "56", "module" => "2", "asc" => "39", "code" => "order_payment_details", "name" => "PAYMENT_DETAILS_MSG", "script" => "block_order_payment_form.php", "pages" => "1"),
			"coupon_form" => array("id" => "57", "module" => "2", "asc" => "40", "code" => "coupon_form", "name" => "COUPON_INFO_MSG", "script" => "block_coupon_form.php", "pages" => "1"),
			"currency_block" => array("id" => "58", "module" => "2", "asc" => "41", "code" => "currencies_list", "name" => "CURRENCIES_MSG", "script" => "block_currency.php", "pages" => "1"),
			"subscriptions" => array("id" => "59", "module" => "2", "asc" => "42", "code" => "subscriptions", "name" => "SUBSCRIPTIONS_MSG", "script" => "block_subscriptions.php", "pages" => "1"),
			"subscriptions_breadcrumb" => array("id" => "60", "module" => "2", "asc" => "43", "code" => "subscriptions_breadcrumb", "name" => "{SUBSCRIPTIONS_MSG} {BREADCRUMB_MSG}", "script" => "block_subscriptions_breadcrumb.php", "pages" => "1"),
			"users_bought_item" => array("id" => "61", "module" => "2", "asc" => "44", "code" => "users_bought_item", "name" => "CUSTOMERS_LIST_BOUGHT_ITEM_TITLE", "script" => "block_products_users_bought.php", "pages" => "1"),
			"wishlist_items" => array("id" => "62", "module" => "2", "asc" => "45", "code" => "wishlist_items", "name" => "WISHLIST_ITEMS_SETTINGS_MSG", "script" => "block_wishlist_items.php", "pages" => "1"),
			"wishlist_search" => array("id" => "63", "module" => "2", "asc" => "46", "code" => "wishlist_search", "name" => "WISHLIST_MSG SEARCH_TITLE", "script" => "block_wishlist_search.php", "pages" => "1"),
			"articles_related_block" => array("id" => "64", "module" => "2", "asc" => "47", "code" => "product_related_articles", "name" => "RELATED_ARTICLES_MSG", "script" => "block_articles_related.php", "pages" => "1"),
			"forums_related_block" => array("id" => "65", "module" => "2", "asc" => "48", "code" => "product_related_forums", "name" => "RELATED_FORUMS_MSG", "script" => "block_forums_related.php", "pages" => "1"),

			"a_breadcrumb" => array("id" => "66", "module" => "3", "asc" => "1", "code" => "articles_breadcrumb", "name" => "BREADCRUMB_MSG", "script" => "block_articles_breadcrumb.php", "pages" => "1"),
			"a_list" => array("id" => "67", "module" => "3", "asc" => "2", "code" => "articles_list", "name" => "ARTICLES_LIST_MSG", "script" => "block_articles_list.php", "pages" => "1"),
			"a_details" => array("id" => "68", "module" => "3", "asc" => "3", "code" => "article_details", "name" => "ARTICLE_MSG", "script" => "block_articles_details.php", "pages" => "1"),
			"a_cats" => array("id" => "69", "module" => "3", "asc" => "4", "code" => "articles_categories", "name" => "CATEGORIES_LIST_MSG", "script" => "block_articles_categories.php", "pages" => "1"),
			"a_subcats" => array("parent" => "a_cats", "id" => "69", "module" => "3", "asc" => "4", "code" => "articles_categories", "name" => "CATEGORIES_LIST_MSG", "script" => "block_articles_categories.php", "pages" => "1"),
			"a_cat_desc" => array("id" => "70", "module" => "3", "asc" => "5", "code" => "articles_category_description", "name" => "CATEGORY_INFO_MSG", "script" => "block_articles_category.php", "pages" => "1"),
			"a_content" => array("id" => "71", "module" => "3", "asc" => "6", "code" => "articles_content", "name" => "CONTENT_TITLE", "script" => "block_articles_content.php", "pages" => "1"),
			"a_hot" => array("id" => "72", "module" => "3", "asc" => "7", "code" => "articles_hot", "name" => "HOT_TITLE", "script" => "block_articles_hot.php", "pages" => "1"),
			"a_related" => array("id" => "73", "module" => "3", "asc" => "8", "code" => "articles_related", "name" => "RELATED_ARTICLES_MSG", "script" => "block_articles_related.php", "pages" => "1"),
			"a_forums_related" => array("id" => "74", "module" => "3", "asc" => "9", "code" => "articles_related_forums", "name" => "RELATED_FORUMS_MSG", "script" => "block_forums_related.php", "pages" => "1"),
			"a_item_related" => array("id" => "75", "module" => "3", "asc" => "10", "code" => "articles_related_products", "name" => "ARTICLE_RELATED_PRODUCTS_TITLE", "script" => "block_products_related.php", "pages" => "1"),
			"a_cat_item_related" => array("id" => "76", "module" => "3", "asc" => "11", "code" => "articles_category_products_related", "name" => "CATEGORY_RELATED_PRODUCTS_TITLE", "script" => "block_products_related.php", "pages" => "1"),
			"a_latest" => array("id" => "77", "module" => "3", "asc" => "12", "code" => "articles_latest", "name" => "LATEST_TITLE", "script" => "block_articles_latest.php", "pages" => "1"),
			"a_search" => array("id" => "78", "module" => "3", "asc" => "13", "code" => "articles_search", "name" => "SEARCH_TITLE", "script" => "block_articles_search.php", "pages" => "1"),
			"a_top_rated" => array("id" => "79", "module" => "3", "asc" => "14", "code" => "articles_top_rated", "name" => "TOP_RATED_TITLE", "script" => "block_articles_top_rated.php", "pages" => "1"),
			"a_top_viewed" => array("id" => "80", "module" => "3", "asc" => "15", "code" => "articles_top_viewed", "name" => "TOP_VIEWED_TITLE", "script" => "block_articles_top_viewed.php", "pages" => "1"),

			"support_block" => array("id" => "81", "module" => "4", "asc" => "1", "code" => "ticket_new", "name" => "SUBMIT_TICKET_MSG", "script" => "block_support.php", "pages" => "1"),
			"support_reply" => array("id" => "82", "module" => "4", "asc" => "2", "code" => "ticket_reply", "name" => "REPLYING_TICKETS_MSG", "script" => "block_support_reply.php", "pages" => "1"),
			"forum_breadcrumb" => array("id" => "83", "module" => "5", "asc" => "1", "code" => "forum_breadcrumb", "name" => "BREADCRUMB_MSG", "script" => "block_forum_breadcrumb.php", "pages" => "1"),
			"forum_list" => array("id" => "84", "module" => "5", "asc" => "2", "code" => "forum_list", "name" => "FORUMS_LIST_PAGE_MSG", "script" => "block_forum_list.php", "pages" => "1"),
			"forum_description" => array("id" => "85", "module" => "5", "asc" => "3", "code" => "forum_description", "name" => "DESCRIPTION_MSG", "script" => "block_forum_description.php", "pages" => "1"),
			"forum_topics_block" => array("id" => "86", "module" => "5", "asc" => "4", "code" => "forum_topics", "name" => "TOPICS_LIST_MSG", "script" => "block_forum_topics.php", "pages" => "1"),
			"forum_view_topic" => array("id" => "87", "module" => "5", "asc" => "5", "code" => "forum_topic", "name" => "FORUM_TOPICS_THREAD_MSG", "script" => "block_forum_topic.php", "pages" => "1"),
			"forum_top_viewed" => array("id" => "88", "module" => "5", "asc" => "6", "code" => "forum_top_viewed", "name" => "TOP_VIEWED_TITLE", "script" => "block_forum_top_viewed.php", "pages" => "1"),
			"forum_latest" => array("id" => "89", "module" => "5", "asc" => "7", "code" => "forum_latest", "name" => "LATEST_TITLE", "script" => "block_forum_latest.php", "pages" => "1"),
			"forum_articles_related_block" => array("id" => "90", "module" => "5", "asc" => "8", "code" => "forum_related_articles", "name" => "RELATED_ARTICLES_MSG", "script" => "block_articles_related.php", "pages" => "1"),
			"forum_item_related_block" => array("id" => "91", "module" => "5", "asc" => "9", "code" => "forum_related_products", "name" => "RELATED_PRODUCTS_TITLE", "script" => "block_products_related.php", "pages" => "1"),
			"forum_search_block" => array("id" => "92", "module" => "5", "asc" => "10", "code" => "forum_search", "name" => "SEARCH_TITLE", "script" => "block_forum_search.php", "pages" => "1"),
			"ads_breadcrumb" => array("id" => "93", "module" => "6", "asc" => "1", "code" => "ads_breadcrumb", "name" => "BREADCRUMB_MSG", "script" => "block_ads_breadcrumb.php", "pages" => "1"),
			"ads_categories" => array("id" => "94", "module" => "6", "asc" => "2", "code" => "ads_categories", "name" => "CATEGORIES_LIST_MSG", "script" => "block_ads_categories.php", "pages" => "1"),
			"ads_subcategories" => array("parent" => "ads_categories", "id" => "94", "module" => "6", "asc" => "2", "code" => "ads_categories", "name" => "CATEGORIES_LIST_MSG", "script" => "block_ads_categories.php", "pages" => "1"),
			"ads_category_info" => array("id" => "95", "module" => "6", "asc" => "3", "code" => "ads_category_info", "name" => "CATEGORY_INFO_MSG", "script" => "block_ads_category.php", "pages" => "1"),
			"ads_list" => array("id" => "96", "module" => "6", "asc" => "4", "code" => "ads_list", "name" => "ADS_LISTING_MSG", "script" => "block_ads_list.php", "pages" => "1"),
			"ads_details" => array("id" => "97", "module" => "6", "asc" => "5", "code" => "ads_details", "name" => "ADS_DETAILS_MSG", "script" => "block_ads_details.php", "pages" => "1"),
			"ads_compare" => array("id" => "98", "module" => "6", "asc" => "6", "code" => "ads_compare", "name" => "PRODUCTS_COMPARE_RESULTS_MSG", "script" => "block_ads_compare.php", "pages" => "1"),
			"ads_add" => array("id" => "99", "module" => "6", "asc" => "7", "code" => "ads_add", "name" => "NEW_AD_MSG", "script" => "block_ads_add.php", "pages" => "1"),
			"ads_hot" => array("id" => "100", "module" => "6", "asc" => "8", "code" => "ads_hot", "name" => "HOT_OFFERS_MSG", "script" => "block_ads_hot.php", "pages" => "1"),
			"ads_special" => array("id" => "101", "module" => "6", "asc" => "9", "code" => "ads_special", "name" => "SPECIAL_OFFER_MSG", "script" => "block_ads_special.php", "pages" => "1"),
			"ads_latest" => array("id" => "102", "module" => "6", "asc" => "10", "code" => "ads_latest", "name" => "LATEST_TITLE", "script" => "block_ads_latest.php", "pages" => "1"),
			"ads_top_viewed" => array("id" => "103", "module" => "6", "asc" => "11", "code" => "ads_top_viewed", "name" => "TOP_VIEWED_TITLE", "script" => "block_ads_top_viewed.php", "pages" => "1"),
			"ads_recently_viewed" => array("id" => "104", "module" => "6", "asc" => "12", "code" => "ads_recently_viewed", "name" => "RECENTLY_VIEWED_MSG", "script" => "block_ads_recently.php", "pages" => "1"),
			"ads_sellers" => array("id" => "105", "module" => "6", "asc" => "13", "code" => "ads_sellers", "name" => "SELLERS_MSG", "script" => "block_ads_sellers.php", "pages" => "1"),
			"ads_search" => array("id" => "106", "module" => "6", "asc" => "14", "code" => "ads_search", "name" => "SEARCH_FORM_MSG", "script" => "block_ads_search.php", "pages" => "1"),
			"ads_search_advanced" => array("id" => "107", "module" => "6", "asc" => "15", "code" => "ads_search_advanced", "name" => "ADVANCED_SEARCH_TITLE", "script" => "block_ads_search_advanced.php", "pages" => "1"),
			"manuals_breadcrumb" => array("id" => "108", "module" => "7", "asc" => "1", "code" => "manuals_breadcrumb", "name" => "BREADCRUMB_MSG", "script" => "block_manuals_breadcrumb.php", "pages" => "1"),
			"manuals_list" => array("id" => "109", "module" => "7", "asc" => "2", "code" => "manuals_list", "name" => "MANUAL_LIST_MSG", "script" => "block_manuals_list.php", "pages" => "1"),
			"manuals_articles" => array("id" => "110", "module" => "7", "asc" => "3", "code" => "manuals_articles", "name" => "MANUAL_ARTICLES_MSG", "script" => "block_manuals_articles.php", "pages" => "1"),
			"manuals_article_details" => array("id" => "111", "module" => "7", "asc" => "4", "code" => "manuals_article_details", "name" => "MANUAL_ARTICLE_DETAILS_MSG", "script" => "block_manuals_article_details.php", "pages" => "1"),
			"manuals_search" => array("id" => "112", "module" => "7", "asc" => "5", "code" => "manuals_search", "name" => "SEARCH_TITLE", "script" => "block_manuals_search.php", "pages" => "1"),
			"manuals_search_results" => array("id" => "113", "module" => "7", "asc" => "6", "code" => "manuals_search_results", "name" => "MANUAL_SEARCH_RESULTS_MSG", "script" => "block_manuals_search_results.php", "pages" => "1"),
			"filter" => array("id" => "114", "module" => "8", "asc" => "1", "code" => "filter", "name" => "{filter_name}", "script" => "block_filter.php", "pages" => "1"),
			"custom_block" => array("id" => "115", "module" => "9", "asc" => "1", "code" => "custom_block", "name" => "{block_name}", "script" => "block_custom.php", "pages" => "1"),
			"navigation_block" => array("id" => "116", "module" => "10", "asc" => "1", "code" => "navigation", "name" => "{menu_title}", "script" => "block_navigation.php", "pages" => "1"),
			"banners_group" => array("id" => "117", "module" => "11", "asc" => "1", "code" => "banners", "name" => "{group_name}", "script" => "block_banners.php", "pages" => "1"),

			"header" => array("id" => "118", "module" => "1", "asc" => "18", "code" => "header", "name" => "HEADER_MSG", "script" => "header.php", "pages" => "1"),
			"footer" => array("id" => "119", "module" => "1", "asc" => "19", "code" => "footer", "name" => "FOOTER_MSG", "script" => "footer.php", "pages" => "1"),

			// new blocks
			"product_reviews" => array("id" => "120", "module" => "2", "asc" => "3", "code" => "product_reviews", "name" => "PRODUCTS_REVIEWS_MSG", "script" => "block_reviews.php", "pages" => "1"),
			"article_reviews" => array("id" => "121", "module" => "3", "asc" => "4", "code" => "article_reviews", "name" => "ARTICLES_REVIEWS_MSG", "script" => "block_articles_reviews.php", "pages" => "1"),
			"download" => array("id" => "122", "module" => "1", "asc" => "18", "code" => "download", "name" => "DOWNLOAD_TITLE", "script" => "block_download.php", "pages" => "1"),
			"forum_topic_new" => array("id" => "123", "module" => "5", "asc" => "6", "code" => "forum_topic_new", "name" => "NEW_TOPIC_MSG", "script" => "block_forum_topic_new.php", "pages" => "1"),

			// new account blocks
			"user_account_profile" => array("id" => 124, "pid" => 49, "code" => "user_account_profile", "module" => 12, "asc" => 3, "name" => "EDIT_PROFILE_MSG", "script" => "block_user_profile.php", "pages" => "1"),
			"user_change_password" => array("id" => 125, "pid" => 50, "code" => "user_change_password", "module" => 12, "asc" => 4, "name" => "CHANGE_PASSWORD_MSG", "script" => "block_user_change_password.php", "pages" => "1"),
			"user_change_type" => array("id" => 126, "pid" => 51, "code" => "user_change_type", "module" => 12, "asc" => 5, "name" => "UPGRADE_DOWNGRADE_MSG", "script" => "block_user_change_type.php", "pages" => "1"),
			"user_carts" => array("id" => 127, "pid" => 52, "code" => "user_carts", "module" => 12, "asc" => 6, "name" => "MY_SAVED_CARTS_MSG", "script" => "block_user_carts.php", "pages" => "1"),
			"user_wishlist" => array("id" => 128, "pid" => 53, "code" => "user_wishlist", "module" => 12, "asc" => 7, "name" => "MY_WISHLIST_MSG", "script" => "block_user_wishlist.php", "pages" => "1"),
			"user_orders" => array("id" => 129, "pid" => 54, "code" => "user_orders", "module" => 12, "asc" => 8, "name" => "{MY_ORDERS_MSG}: {LIST_MSG}", "script" => "block_user_orders.php", "pages" => "1"),
			"user_order" => array("id" => 130, "pid" => 55, "code" => "user_order", "module" => 12, "asc" => 9, "name" => "{MY_ORDERS_MSG}: {VIEW_DETAILS_MSG}", "script" => "block_user_order.php", "pages" => "1"),
			"user_order_update" => array("id" => 131, "pid" => 56, "code" => "user_order_update", "module" => 12, "asc" => 10, "name" => "{MY_ORDERS_MSG}:  {EDIT_MSG}", "script" => "block_user_order_update.php", "pages" => "1"),
			"user_support" => array("id" => 132, "pid" => 57, "code" => "user_support", "module" => 12, "asc" => 11, "name" => "MY_SUPPORT_ISSUES_MSG", "script" => "block_user_support.php", "pages" => "1"),
			"user_ads" => array("id" => 133, "pid" => 58, "code" => "user_ads", "module" => 12, "asc" => 12, "name" => "{MY_ADS_MSG}: {LIST_MSG}", "script" => "block_user_ads.php", "pages" => "1"),
			"user_ad" => array("id" => 134, "pid" => 59, "code" => "user_ad", "module" => 12, "asc" => 13, "name" => "{MY_ADS_MSG}: {EDIT_MSG}", "script" => "block_user_ad.php", "pages" => "1"),
			"user_affiliate_sales" => array("id" => 135, "pid" => 60, "code" => "user_affiliate_sales", "module" => 12, "asc" => 14, "name" => "AFFILIATE_SALES_MSG", "script" => "block_user_affiliate_sales.php", "pages" => "1"),
			"user_affiliate_items" => array("id" => 136, "pid" => 61, "code" => "user_affiliate_items", "module" => 12, "asc" => 15, "name" => "{AFFILIATE_SALES_MSG}: {PRODUCTS_LIST_MSG}", "script" => "block_user_affiliate_items.php", "pages" => "1"),
			"user_products" => array("id" => 137, "pid" => 62, "code" => "user_products", "module" => 12, "asc" => 16, "name" => "{MY_PRODUCTS_MSG}: {LIST_MSG}", "script" => "block_user_products.php", "pages" => "1"),
			"user_product" => array("id" => 138, "pid" => 63, "code" => "user_product", "module" => 12, "asc" => 17, "name" => "{MY_PRODUCTS_MSG}: {EDIT_MSG}", "script" => "block_user_product.php", "pages" => "1"),
			"user_product_options" => array("id" => 139, "pid" => 64, "code" => "user_product_options", "module" => 12, "asc" => 18, "name" => "{OPTIONS_AND_COMPONENTS_MSG}: {LIST_MSG}", "script" => "block_user_product_options.php", "pages" => "1"),
			"user_product_option" => array("id" => 140, "pid" => 65, "code" => "user_product_option", "module" => 12, "asc" => 19, "name" => "{OPTIONS_AND_COMPONENTS_MSG}: {EDIT_OPTION_MSG}", "script" => "block_user_product_option.php", "pages" => "1"),
			"user_product_subcomponent" => array("id" => 141, "pid" => 66, "code" => "user_product_subcomponent", "module" => 12, "asc" => 20, "name" => "{OPTIONS_AND_COMPONENTS_MSG}: {EDIT_SUBCOMP_MSG}", "script" => "block_user_product_subcomponent.php", "pages" => "1"),
			"user_product_subcomponents" => array("id" => 142, "pid" => 67, "code" => "user_product_subcomponents", "module" => 12, "asc" => 21, "name" => "{OPTIONS_AND_COMPONENTS_MSG}: {EDIT_SUBCOMP_SELECTION_MSG}", "script" => "block_user_product_subcomponents.php", "pages" => "1"),
			"user_payments" => array("id" => 143, "pid" => 68, "code" => "user_payments", "module" => 12, "asc" => 22, "name" => "{COMMISSION_PAYMENTS_MSG}: {LIST_MSG}", "script" => "block_user_payments.php", "pages" => "1"),
			"user_payment" => array("id" => 144, "pid" => 69, "code" => "user_payments", "module" => 12, "asc" => 23, "name" => "{COMMISSION_PAYMENTS_MSG}: {VIEW_DETAILS_MSG}", "script" => "block_user_payment.php", "pages" => "1"),
			"user_merchant_orders" => array("id" => 145, "pid" => 70, "code" => "user_merchant_orders", "module" => 12, "asc" => 24, "name" => "{MY_SALES_ORDERS_MSG}: {LIST_MSG}", "script" => "block_user_merchant_orders.php", "pages" => "1"),
			"user_merchant_order" => array("id" => 146, "pid" => 71, "code" => "user_merchant_order", "module" => 12, "asc" => 25, "name" => "{MY_SALES_ORDERS_MSG}: {VIEW_DETAILS_MSG}", "script" => "block_user_merchant_order.php", "pages" => "1"),
			"user_merchant_sales" => array("id" => 147, "pid" => 72, "code" => "user_merchant_sales", "module" => 12, "asc" => 26, "name" => "MERCHANT_SALES_MSG", "script" => "block_user_merchant_sales.php", "pages" => "1"),
			"user_merchant_items" => array("id" => 148, "pid" => 73, "code" => "user_merchant_items", "module" => 12, "asc" => 27, "name" => "{MERCHANT_SALES_MSG}: {PRODUCTS_LIST_MSG}", "script" => "block_user_merchant_items.php", "pages" => "1"),
			"user_reminders" => array("id" => 149, "pid" => 74, "code" => "user_reminders", "module" => 12, "asc" => 28, "name" => "{MY_REMINDERS_MSG}: {LIST_MSG}", "script" => "block_user_reminders.php", "pages" => "1"),
			"user_reminder" => array("id" => 150, "pid" => 75, "code" => "user_reminder", "module" => 12, "asc" => 29, "name" => "{MY_REMINDERS_MSG}: {EDIT_MSG}", "script" => "block_user_reminder.php", "pages" => "1"),
			"user_psd_list" => array("id" => 151, "pid" => 76, "code" => "user_psd_list", "module" => 12, "asc" => 30, "name" => "{PAYMENT_DETAILS_MSG}: {LIST_MSG}", "script" => "block_user_psd_list.php", "pages" => "1"),
			"user_psd_update" => array("id" => 152, "pid" => 77, "code" => "user_psd_update", "module" => 12, "asc" => 31, "name" => "{PAYMENT_DETAILS_MSG}: {EDIT_MSG}", "script" => "block_user_psd_update.php", "pages" => "1"),
			"user_product_registrations" => array("id" => 153, "pid" => 78, "code" => "user_product_registrations", "module" => 12, "asc" => 32, "name" => "MY_PRODUCT_REGISTRATIONS_MSG", "script" => "block_user_product_registrations.php", "pages" => "1"),
			"user_product_registration" => array("id" => 154, "pid" => 79, "code" => "user_product_registration", "module" => 12, "asc" => 33, "name" => "REGISTER_PRODUCT_MSG", "script" => "block_user_product_registration.php", "pages" => "1"),
		);

		// add blocks 
		$sqls[] = "DELETE FROM ".$table_prefix."cms_blocks ";
		foreach ($cms_blocks as $old_code => $block) {
			// check if the block doesn't has parent block
			if (!isset($block["parent"])) {
				$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
				$sql.= $db->tosql($block["id"], INTEGER).",";
				$sql.= $db->tosql($block["module"], INTEGER).",";
				$sql.= $db->tosql($block["asc"], INTEGER).",";
				$sql.= $db->tosql($block["code"], TEXT).",";
				$sql.= $db->tosql($block["name"], TEXT).",";
				$sql.= $db->tosql($block["script"], TEXT).",";
				$sql.= $db->tosql($block["pages"], INTEGER).")";
				$sqls[] = $sql;
			}
		}
		run_queries($sqls, $queries_success, $queries_failed, $errors, "");

		// add properties and their values to blocks
		$sqls[] = "DELETE FROM ".$table_prefix."cms_blocks_properties ";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (1 , 1 , 1 , 'SELECT_TYPE_MSG' , 'RADIOBUTTON' , NULL , NULL , 'language_selection' , NULL , 0 , NULL , NULL , NULL , NULL , '<br>' , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (2 , 9 , 1 , 'SELECT_TYPE_MSG' , 'RADIOBUTTON' , NULL , NULL , 'design_selection' , NULL , 0 , NULL , NULL , NULL , NULL , '<br>' , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (3 , 13 , 1 , 'ORIGINATOR_MSG' , 'TEXTBOX' , NULL , NULL , 'sms_originator' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (4 , 13 , 2 , 'SMS_TEST_MESSAGE_MSG' , 'TEXTAREA' , NULL , NULL , 'sms_test_message' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (5 , 18 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'products_per_page' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (6 , 18 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'products_columns' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (7 , 18 , 3 , 'NAVIGATOR_SORTING_SETTINGS_MSG' , 'LISTBOX' , NULL , NULL , 'products_nav_type' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (8 , 18 , 4 , 'NAVIGATOR_FIRST_LAST_PAGES_MSG' , 'CHECKBOX' , NULL , NULL , 'products_nav_first_last' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (9 , 18 , 5 , 'NAVIGATOR_PREV_NEXT_PAGES_MSG' , 'CHECKBOX' , NULL , NULL , 'products_nav_prev_next' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (10 , 18 , 6 , 'NUMBER_OF_PAGES_NAVIGATOR_MSG' , 'TEXTBOX' , NULL , NULL , 'products_nav_pages' , '5' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (11 , 18 , 7 , 'SHOW_PRODUCTS_SORTING_MSG' , 'CHECKBOX' , NULL , NULL , 'products_sortings' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (12 , 18 , 8 , 'GROUP_PRODUCTS_BY_CATEGORIES_MSG' , 'CHECKBOX' , NULL , NULL , 'products_group_by_cats' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (13 , 18 , 9 , 'DEFAULT_VIEW_TYPE_MSG' , 'LISTBOX' , NULL , NULL , 'products_default_view' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (14 , 19 , 1 , 'SHOW_PRODUCTS_SECTION_MSG' , 'LISTBOX' , NULL , NULL , 'use_tabs' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (15 , 19 , 2 , 'SHOW_SUPER_IMAGE_MSG' , 'LISTBOX' , NULL , NULL , 'show_super_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (16 , 19 , 3 , 'MANUFACTURER_IMAGE_MSG' , 'LISTBOX' , NULL , NULL , 'show_manufacturer_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (17 , 20 , 1 , 'CATEGORIES_TYPE_VIEW_MSG' , 'LISTBOX' , NULL , NULL , 'categories_type' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (18 , 20 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'categories_columns' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (19 , 20 , 3 , 'NUMBER_OF_SHOWN_SUBS_MSG' , 'TEXTBOX' , NULL , NULL , 'categories_subs' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (20 , 20 , 4 , 'IMAGE_TYPE_FOR_TOP_CAT_MSG' , 'LISTBOX' , NULL , NULL , 'categories_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (21 , 21 , 1 , 'IMAGE_MSG' , 'LISTBOX' , NULL , NULL , 'category_description_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (22 , 21 , 2 , 'DESCRIPTION_MSG' , 'LISTBOX' , NULL , NULL , 'category_description_type' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (23 , 22 , 1 , 'IMAGE_TYPE_MSG' , 'LISTBOX' , NULL , NULL , 'image_type' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (25 , 29 , 1 , 'SELECT_TYPE_MSG' , 'RADIOBUTTON' , NULL , NULL , 'manufacturers_selection' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (26 , 29 , 2 , 'IMAGE_TYPE_MSG' , 'LISTBOX' , NULL , NULL , 'manufacturers_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (27 , 29 , 3 , 'DESCRIPTION_MSG' , 'LISTBOX' , NULL , NULL , 'manufacturers_desc' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (28 , 29 , 4 , 'SORT_ORDER_MSG' , 'LISTBOX' , NULL , NULL , 'manufacturers_order' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (29 , 29 , 5 , 'SORT_DIRECTION_MSG' , 'LISTBOX' , NULL , NULL , 'manufacturers_direction' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (30 , 30 , 1 , 'IMAGE_TYPE_MSG' , 'LISTBOX' , NULL , NULL , 'manufacturer_info_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (31 , 30 , 2 , 'DESCRIPTION_MSG' , 'LISTBOX' , NULL , NULL , 'manufacturer_info_type' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (32 , 31 , 1 , 'SELECT_TYPE_MSG' , 'RADIOBUTTON' , NULL , NULL , 'merchants_selection' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (33 , 32 , 1 , 'PERSONAL_IMAGE_FIELD' , 'CHECKBOX' , NULL , NULL , 'merchant_info_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (34 , 32 , 2 , 'NICKNAME_FIELD' , 'CHECKBOX' , NULL , NULL , 'merchant_info_nick' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (35 , 32 , 3 , 'MERCHANT_COUNTRY_FLAG_MSG' , 'CHECKBOX' , NULL , NULL , 'merchant_info_country_flag' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (36 , 32 , 4 , '{ONLINE_MSG} / {OFFLINE_MSG}' , 'CHECKBOX' , NULL , NULL , 'merchant_info_online' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (37 , 32 , 5 , 'MEMBER_SINCE_MSG' , 'CHECKBOX' , NULL , NULL , 'merchant_info_member' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (38 , 32 , 6 , 'MERCHANT_PRODUCTS_LINK_MSG' , 'CHECKBOX' , NULL , NULL , 'merchant_info_prod_link' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (39 , 32 , 7 , 'DESCRIPTION_MSG' , 'LISTBOX' , NULL , NULL , 'merchant_info_desc' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (40 , 34 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'prod_offers_recs' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (41 , 34 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'prod_offers_cols' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (42 , 34 , 3 , 'SHOW_POINTS_PRICE_MSG' , 'CHECKBOX' , NULL , NULL , 'prod_offers_points_price' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (43 , 34 , 4 , 'SHOW_REWARD_POINTS_MSG' , 'CHECKBOX' , NULL , NULL , 'prod_offers_reward_points' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (44 , 34 , 5 , 'SHOW_REWARD_CREDITS_MSG' , 'CHECKBOX' , NULL , NULL , 'prod_offers_reward_credits' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (45 , 34 , 6 , 'ADD_TO_CART_MSG' , 'CHECKBOX' , NULL , NULL , 'prod_offers_add_button' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (46 , 34 , 7 , 'VIEW_CART_MSG' , 'CHECKBOX' , NULL , NULL , 'prod_offers_view_button' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (47 , 34 , 8 , 'GOTO_CHECKOUT_MSG' , 'CHECKBOX' , NULL , NULL , 'prod_offers_goto_button' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (48 , 34 , 9 , 'ADD_TO_WISHLIST_MSG' , 'CHECKBOX' , NULL , NULL , 'prod_offers_wish_button' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (49 , 34 , 10 , 'QUANTITY_CONTROL_MSG' , 'LISTBOX' , NULL , NULL , 'prod_offers_quantity_control' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (50 , 34 , 11 , 'SLIDER_TYPE_MSG' , 'LISTBOX' , NULL , NULL , 'prod_slider_type' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (51 , 34 , 12 , 'SLIDER_WIDTH_MSG' , 'TEXTBOX' , NULL , NULL , 'prod_slider_width' , '300' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (52 , 34 , 13 , 'SLIDER_HEIGHT_MSG' , 'TEXTBOX' , NULL , NULL , 'prod_slider_height' , '300' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (53 , 34 , 14 , 'SLIDER_STYLE_MSG' , 'TEXTAREA' , NULL , NULL , 'prod_slider_style' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , 'SLIDER_EXAMPLE_MSG' , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (54 , 38 , 1 , 'SHOW_POINTS_PRICE_MSG' , 'CHECKBOX' , NULL , NULL , 'prod_fast_add_points_price' , NULL , 0 , NULL , NULL , '{FAST_PRODUCT_ADDING_DESC_MSG}\r\n<br><br>' , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (55 , 38 , 2 , 'SHOW_REWARD_POINTS_MSG' , 'CHECKBOX' , NULL , NULL , 'prod_fast_add_reward_points' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (56 , 38 , 3 , 'SHOW_REWARD_CREDITS_MSG' , 'CHECKBOX' , NULL , NULL , 'prod_fast_add_reward_credits' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (57 , 38 , 4 , 'ADD_TO_CART_MSG' , 'CHECKBOX' , NULL , NULL , 'prod_fast_add_add_button' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (58 , 38 , 5 , 'VIEW_CART_MSG' , 'CHECKBOX' , NULL , NULL , 'prod_fast_add_view_button' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (59 , 38 , 6 , 'GOTO_CHECKOUT_MSG' , 'CHECKBOX' , NULL , NULL , 'prod_fast_add_goto_button' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (60 , 38 , 7 , 'ADD_TO_WISHLIST_MSG' , 'CHECKBOX' , NULL , NULL , 'prod_fast_add_wish_button' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (61 , 39 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'products_latest_recs' , '10' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (62 , 39 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'products_latest_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (63 , 39 , 3 , 'IMAGE_TYPE_MSG' , 'LISTBOX' , NULL , NULL , 'prod_latest_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (64 , 39 , 4 , 'DESCRIPTION_MSG' , 'LISTBOX' , NULL , NULL , 'prod_latest_desc' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (65 , 39 , 5 , 'SORT_ORDER_MSG' , 'LISTBOX' , NULL , NULL , 'prod_latest_order' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (66 , 41 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'products_recent_records' , '5' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (67 , 41 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'products_recent_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (68 , 41 , 3 , 'IMAGE_TYPE_MSG' , 'LISTBOX' , NULL , NULL , 'recent_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (69 , 41 , 4 , 'DESCRIPTION_MSG' , 'LISTBOX' , NULL , NULL , 'recent_desc' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (70 , 42 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'prod_recom_per_page' , '10' , 0 , NULL , NULL , '{RECOMMENDED_PRODUCTS_DESC}\r\n<br><br>' , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (71 , 42 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'prod_recom_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (72 , 45 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'bestsellers_records' , '10' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (73 , 45 , 2 , 'RECENT_PERIOD_PRODUCTS_BOUGHT_MSG' , 'TEXTBOX' , NULL , NULL , 'bestsellers_days' , '90' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , 'DAYS_MSG' , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (74 , 45 , 3 , 'ORDER_STATUS_MSG' , 'LISTBOX' , NULL , NULL , 'bestsellers_status' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (75 , 45 , 4 , 'IMAGE_TYPE_MSG' , 'LISTBOX' , NULL , NULL , 'bestsellers_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (76 , 45 , 5 , 'DESCRIPTION_MSG' , 'LISTBOX' , NULL , NULL , 'bestsellers_desc' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (77 , 46 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'prod_top_viewed_recs' , '10' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (78 , 46 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'prod_top_viewed_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (79 , 46 , 3 , 'IMAGE_TYPE_MSG' , 'LISTBOX' , NULL , NULL , 'top_viewed_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (80 , 46 , 4 , 'DESCRIPTION_MSG' , 'LISTBOX' , NULL , NULL , 'top_viewed_desc' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (81 , 47 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'top_rates_recs' , '10' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (82 , 47 , 2 , 'IMAGE_TYPE_MSG' , 'LISTBOX' , NULL , NULL , 'top_rated_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (83 , 47 , 3 , 'DESCRIPTION_MSG' , 'LISTBOX' , NULL , NULL , 'top_rated_desc' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (84 , 48 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'related_per_page' , '4' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (85 , 48 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'related_columns' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (86 , 49 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'related_purchase_recs' , '10' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (87 , 49 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'related_purchase_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (88 , 49 , 3 , 'RECENT_PERIOD_PRODUCTS_BOUGHT_MSG' , 'TEXTBOX' , NULL , NULL , 'related_purchase_days' , '90' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (89 , 49 , 4 , 'ORDER_STATUS_MSG' , 'LISTBOX' , NULL , NULL , 'related_purchase_status' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (90 , 49 , 5 , 'IMAGE_TYPE_MSG' , 'LISTBOX' , NULL , NULL , 'related_purchase_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (91 , 49 , 6 , 'DESCRIPTION_MSG' , 'LISTBOX' , NULL , NULL , 'related_purchase_desc' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (92 , 58 , 1 , 'SELECT_TYPE_MSG' , 'RADIOBUTTON' , NULL , NULL , 'currency_selection' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (93 , 61 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'users_bought_item_recs' , '10' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (94 , 61 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'users_bought_item_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (95 , 61 , 3 , 'USERS_BOUGHT_DURING_MSG' , 'TEXTBOX' , NULL , NULL , 'users_bought_item_days' , '90' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (96 , 61 , 4 , 'ORDER_STATUS_MSG' , 'LISTBOX' , NULL , NULL , 'users_bought_status' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (97 , 61 , 5 , 'USER_TYPE_MSG' , 'LISTBOX' , NULL , NULL , 'users_bought_type' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (98 , 61 , 6 , 'SELECT_FIELDS_TO_SHOW_MSG' , 'CHECKBOXLIST' , NULL , NULL , 'users_bought_item_fields' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (99 , 62 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'wl_recs' , '20' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (100 , 62 , 2 , 'IMAGE_TYPE_MSG' , 'LISTBOX' , NULL , NULL , 'wl_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (101 , 64 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_related_per_page' , '4' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (102 , 64 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_related_columns' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (103 , 64 , 3 , 'IMAGE_TYPE_MSG' , 'LISTBOX' , NULL , NULL , 'articles_related_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (104 , 64 , 4 , 'DESCRIPTION_MSG' , 'LISTBOX' , NULL , NULL , 'articles_related_desc' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (105 , 64 , 5 , 'DATE_MSG' , 'LISTBOX' , NULL , NULL , 'articles_related_date' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (106 , 65 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'forums_related_per_page' , '4' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (107 , 65 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'forums_related_columns' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (108 , 65 , 3 , 'DESCRIPTION_MSG' , 'LISTBOX' , NULL , NULL , 'forums_related_desc' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (109 , 65 , 4 , 'AUTHOR_INFO_MSG' , 'CHECKBOX' , NULL , NULL , 'forums_related_user_info' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (110 , 67 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_recs' , '10' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (111 , 67 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (112 , 69 , 1 , 'CATEGORIES_TYPE_VIEW_MSG' , 'LISTBOX' , NULL , NULL , 'articles_categories_type' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (113 , 69 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_categories_cols' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (114 , 69 , 3 , 'NUMBER_OF_SHOWN_SUBS_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_categories_subs' , '0' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (115 , 69 , 4 , 'IMAGE_TYPE_FOR_TOP_CAT_MSG' , 'LISTBOX' , NULL , NULL , 'articles_categories_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (116 , 70 , 1 , 'IMAGE_MSG' , 'LISTBOX' , NULL , NULL , 'articles_category_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (117 , 70 , 2 , 'DESCRIPTION_MSG' , 'LISTBOX' , NULL , NULL , 'articles_category_desc_type' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (118 , 72 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_hot_recs' , '10' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (119 , 72 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_hot_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (120 , 73 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_related_recs' , '4' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (121 , 73 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_related_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (122 , 73 , 3 , 'IMAGE_TYPE_MSG' , 'LISTBOX' , NULL , NULL , 'articles_related_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (123 , 73 , 4 , 'DESCRIPTION_MSG' , 'LISTBOX' , NULL , NULL , 'articles_related_desc' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (124 , 73 , 5 , 'DATE_MSG' , 'LISTBOX' , NULL , NULL , 'articles_related_date' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (125 , 74 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_related_forums_recs' , '4' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (126 , 74 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_related_forums_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (127 , 74 , 3 , 'DESCRIPTION_MSG' , 'LISTBOX' , NULL , NULL , 'articles_related_forums_desc' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (128 , 74 , 4 , 'AUTHOR_INFO_MSG' , 'CHECKBOX' , NULL , NULL , 'articles_related_forums_author' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (129 , 75 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_related_products_recs' , '5' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (130 , 75 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_related_products_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (131 , 76 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_products_cats_recs' , '5' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (132 , 76 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_products_cats_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (133 , 77 , 1 , 'GROUP_BY_MSG' , 'LISTBOX' , NULL , NULL , 'articles_latest_group_by' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (134 , 77 , 2 , 'CATEGORIES_IDS_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_latest_cats' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (135 , 77 , 3 , 'INCLUDE_ARTICLES_FROM_SUBCAT_MSG' , 'CHECKBOX' , NULL , NULL , 'articles_latest_subcats' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (136 , 77 , 4 , 'TOP_ARTICLES_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_latest_recs' , '10' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (137 , 77 , 5 , 'ARTICLES_LINKS_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_latest_subrecs' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (138 , 77 , 6 , 'TOP_ARTICLE_IMAGE_TYPE_MSG' , 'LISTBOX' , NULL , NULL , 'articles_latest_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (139 , 77 , 7 , 'TOP_ARTICLE_DESCRIPTION_MSG' , 'LISTBOX' , NULL , NULL , 'articles_latest_desc' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (140 , 77 , 8 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_latest_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (141 , 80 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_top_viewed_recs' , '10' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (142 , 80 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'articles_top_viewed_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (143 , 85 , 1 , 'IMAGE_MSG' , 'LISTBOX' , NULL , NULL , 'forum_description_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (144 , 85 , 2 , 'DESCRIPTION_MSG' , 'LISTBOX' , NULL , NULL , 'forum_description_type' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (145 , 88 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'forum_top_viewed_recs' , '10' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (146 , 88 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'forum_top_viewed_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (147 , 89 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'forum_latest_recs' , '10' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (148 , 89 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'forum_latest_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (149 , 90 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'forum_articles_related_recs' , '4' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (150 , 90 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'forum_articles_related_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (151 , 90 , 3 , 'IMAGE_TYPE_MSG' , 'LISTBOX' , NULL , NULL , 'forum_articles_related_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (152 , 90 , 4 , 'DESCRIPTION_MSG' , 'LISTBOX' , NULL , NULL , 'forum_articles_related_desc' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (153 , 90 , 5 , 'DATE_MSG' , 'LISTBOX' , NULL , NULL , 'forum_articles_related_date' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (154 , 91 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'forum_item_related_recs' , '4' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (155 , 91 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'forum_item_related_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (156 , 94 , 1 , 'CATEGORIES_TYPE_VIEW_MSG' , 'LISTBOX' , NULL , NULL , 'ads_categories_type' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (157 , 94 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'ads_categories_cols' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (158 , 94 , 3 , 'NUMBER_OF_SHOWN_SUBS_MSG' , 'TEXTBOX' , NULL , NULL , 'ads_categories_subs' , '0' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (159 , 94 , 4 , 'IMAGE_TYPE_FOR_TOP_CAT_MSG' , 'LISTBOX' , NULL , NULL , 'ads_categories_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (160 , 95 , 1 , 'IMAGE_MSG' , 'LISTBOX' , NULL , NULL , 'ads_cat_desc_image' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (161 , 95 , 2 , 'DESCRIPTION_MSG' , 'LISTBOX' , NULL , NULL , 'ads_cat_desc_type' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (162 , 96 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'ads_list_recs' , '10' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (163 , 96 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'ads_list_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (164 , 97 , 1 , 'SHOW_PRODUCTS_SECTION_MSG' , 'RADIOBUTTON' , NULL , NULL , 'ads_details_tabs' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (165 , 100 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'ads_hot_recs' , '10' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (166 , 100 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'ads_hot_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (167 , 101 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'ads_special_recs' , '10' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (168 , 101 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'ads_special_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (169 , 102 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'ads_latest_recs' , '10' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (170 , 102 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'ads_latest_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (171 , 103 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'ads_top_viewed_recs' , '10' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (172 , 103 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'ads_top_viewed_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (173 , 104 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'ads_recent_recs' , '5' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (174 , 114 , 1 , 'NUMBER_DEFAULT_VALUES_MSG' , 'TEXTBOX' , NULL , NULL , 'filter_values_limit' , '10' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (175 , 115 , 1 , 'BLOCK_CSS_CLASS_MSG' , 'TEXTBOX' , NULL , NULL , 'cb_css_class' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (176 , 115 , 2 , 'CUSTOMERS_MSG' , 'LISTBOX' , NULL , NULL , 'cb_user_type' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (177 , 115 , 3 , 'ADMINISTRATORS_MSG' , 'LISTBOX' , NULL , NULL , 'cb_admin_type' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (178 , 115 , 4 , 'PARAMETERS_MSG' , 'TEXTBOX' , NULL , NULL , 'cb_params' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (179 , 117 , 1 , 'NUMBER_OF_SHOWN_BANNERS_MSG' , 'TEXTBOX' , NULL , NULL , 'bg_limit' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (180 , 117 , 2 , 'PARAMETERS_MSG' , 'TEXTBOX' , NULL , NULL , 'bg_params' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (181 , 116 , 1 , 'NAVIGATION_VISIBLE_DEPTH_MSG' , 'LISTBOX' , NULL , NULL , 'visible_depth_level' , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (182 , 23 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'basket_prod_recom_recs' , '10' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (183 , 23 , 2 , 'NUMBER_OF_COLUMNS_MSG' , 'TEXTBOX' , NULL , NULL , 'basket_prod_recom_cols' , '1' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (184 , 17 , 1 , 'RECORDS_PER_PAGE_MSG' , 'TEXTBOX' , NULL , NULL , 'ss_recs' , '10' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES (185 , 18 , 10 , 'CATEGORY_DESC_MSG' , 'LISTBOX' , 12, NULL , 'category_desc' , '' , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL )";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "");

		$sqls[] = "DELETE FROM ".$table_prefix."cms_blocks_values ";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (1 , 1 , 1 , 'SHOW_IMAGES_LANGUAGE_MSG' , NULL , '1' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (2 , 1 , 2 , 'SHOW_LISTBOX_LANGUAGE_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (3 , 2 , 1 , 'SHOW_DESIGNS_PER_ROW_MSG' , NULL , '1' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (4 , 2 , 2 , 'USE_LISTBOX_DESIGNS_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (5 , 7 , 1 , 'SIMPLE_CURRENT_PAGE_ONLY_MSG' , NULL , '1' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (6 , 7 , 2 , 'CURRENT_PAGE_SHOWN_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (7 , 7 , 3 , 'NAVIGATOR_SPLITS_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (8 , 7 , 4 , 'SHOWS_LINKS_PAGES_MSG' , NULL , '4' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (9 , 13 , 1 , 'DETAILED_LISTING_MSG' , NULL , 'list' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (10 , 13 , 2 , 'TABLE_VIEW_MSG' , NULL , 'table' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (11 , 13 , 3 , 'GRID_MSG' , NULL , 'grid' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (12 , 14 , 1 , 'ON_DIFFERENT_PAGES_MSG' , NULL , '1' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (13 , 14 , 2 , 'ON_ONE_PAGE_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (14 , 15 , 1 , 'SUPER_IMAGE_ON_CLICK_MSG' , NULL , '0' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (15 , 15 , 2 , 'SUPER_IMAGE_ON_MOVE_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (16 , 16 , 1 , 'DONT_SHOW_IMAGE_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (17 , 16 , 2 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (18 , 16 , 3 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (19 , 17 , 1 , 'ONELEVEL_LIST_MSG' , NULL , '1' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (20 , 17 , 2 , 'TWOLEVEL_LIST_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (21 , 17 , 3 , 'MULTILEVEL_LIST_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (22 , 17 , 4 , 'TREETYPE_STRUCTURE_MSG' , NULL , '4' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (23 , 20 , 1 , 'DEFAULT_IMAGE_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (24 , 20 , 2 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (25 , 20 , 3 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (26 , 21 , 1 , 'DONT_SHOW_IMAGE_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (27 , 21 , 2 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (28 , 21 , 3 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (29 , 22 , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (30 , 22 , 2 , 'SHORT_DESCRIPTION_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (31 , 22 , 3 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (32 , 17 , 5 , 'CHAINED_MENU_TITLE' , NULL , '5' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (33 , 23 , 1 , 'DONT_SHOW_IMAGE_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (34 , 23 , 2 , 'IMAGE_TINY_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (35 , 23 , 3 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (36 , 23 , 4 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (43 , 25 , 1 , 'SHOW_MANUFACTURERS_MSG' , NULL , '1' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (44 , 25 , 2 , 'USE_LISTBOX_MANUFACTURERS_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (45 , 26 , 1 , 'DONT_SHOW_IMAGE_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (46 , 26 , 2 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (47 , 26 , 3 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (48 , 27 , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (49 , 27 , 2 , 'SHORT_DESCRIPTION_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (50 , 27 , 3 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (51 , 28 , 1 , 'MANUFACTURER_NAME_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (52 , 28 , 2 , 'MANUFACTURER_ORDER_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (53 , 29 , 1 , 'ASC_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (54 , 29 , 2 , 'DESC_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (55 , 30 , 1 , 'DONT_SHOW_IMAGE_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (56 , 30 , 2 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (57 , 30 , 3 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (58 , 31 , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (59 , 31 , 2 , 'SHORT_DESCRIPTION_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (60 , 31 , 3 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (61 , 32 , 1 , 'SHOW_MERCHANTS_PER_ROW_MSG' , NULL , '1' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (62 , 32 , 2 , 'USE_LISTBOX_MERCHANTS_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (63 , 39 , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (64 , 39 , 2 , 'SHORT_DESCRIPTION_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (65 , 39 , 3 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (66 , 49 , 1 , 'NONE_MSG' , NULL , 'NONE' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (67 , 49 , 2 , 'LABEL_MSG' , NULL , 'LABEL' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (68 , 49 , 3 , 'LISTBOX_MSG' , NULL , 'LISTBOX' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (69 , 49 , 4 , 'TEXTBOX_MSG' , NULL , 'TEXTBOX' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (70 , 50 , 1 , 'NONE_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (71 , 50 , 2 , 'VERTICAL_SLIDER_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (72 , 50 , 3 , 'HORIZONTAL_SLIDER_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (73 , 63 , 1 , 'DONT_SHOW_IMAGE_MSG' , NULL , '0' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (74 , 63 , 2 , 'IMAGE_TINY_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (75 , 63 , 3 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (76 , 63 , 4 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (77 , 64 , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (78 , 64 , 2 , 'SHORT_DESCRIPTION_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (79 , 64 , 3 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (80 , 64 , 4 , 'HIGHLIGHTS_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (81 , 64 , 5 , 'SPECIAL_OFFER_MSG' , NULL , '4' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (82 , 65 , 1 , 'PROD_ISSUE_DATE_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (83 , 65 , 2 , 'DATE_ADDED_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (84 , 65 , 3 , 'DATE_MODIFIED_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (85 , 68 , 1 , 'DONT_SHOW_IMAGE_MSG' , NULL , '0' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (86 , 68 , 2 , 'IMAGE_TINY_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (87 , 68 , 3 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (88 , 68 , 4 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (89 , 69 , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (90 , 69 , 2 , 'SHORT_DESCRIPTION_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (91 , 69 , 3 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (92 , 69 , 4 , 'HIGHLIGHTS_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (93 , 69 , 5 , 'SPECIAL_OFFER_MSG' , NULL , '4' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (94 , 74 , 1 , 'ANY_STATUS_MSG' , NULL , 'ANY' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (95 , 74 , 2 , 'ANY_PAID_STATUS_MSG' , NULL , 'PAID' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (96 , 75 , 1 , 'DONT_SHOW_IMAGE_MSG' , NULL , '0' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (97 , 75 , 2 , 'IMAGE_TINY_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (98 , 75 , 3 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (99 , 75 , 4 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (100 , 76 , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (101 , 76 , 2 , 'SHORT_DESCRIPTION_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (102 , 76 , 3 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (103 , 76 , 4 , 'HIGHLIGHTS_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (104 , 76 , 5 , 'SPECIAL_OFFER_MSG' , NULL , '4' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (105 , 79 , 1 , 'DONT_SHOW_IMAGE_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (106 , 79 , 2 , 'IMAGE_TINY_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (107 , 79 , 3 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (108 , 79 , 4 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (109 , 80 , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (110 , 80 , 2 , 'SHORT_DESCRIPTION_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (111 , 80 , 3 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (112 , 80 , 4 , 'HIGHLIGHTS_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (113 , 80 , 5 , 'SPECIAL_OFFER_MSG' , NULL , '4' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (114 , 82 , 1 , 'DONT_SHOW_IMAGE_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (115 , 82 , 2 , 'IMAGE_TINY_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (116 , 82 , 3 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (117 , 82 , 4 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (118 , 83 , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (119 , 83 , 2 , 'SHORT_DESCRIPTION_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (120 , 83 , 3 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (121 , 83 , 4 , 'HIGHLIGHTS_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (122 , 83 , 5 , 'SPECIAL_OFFER_MSG' , NULL , '4' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (123 , 89 , 1 , 'ANY_STATUS_MSG' , NULL , 'ANY' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (124 , 89 , 2 , 'ANY_PAID_STATUS_MSG' , NULL , 'PAID' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (125 , 90 , 1 , 'DONT_SHOW_IMAGE_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (126 , 90 , 2 , 'IMAGE_TINY_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (127 , 90 , 3 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (128 , 90 , 4 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (129 , 91 , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (130 , 91 , 2 , 'SHORT_DESCRIPTION_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (131 , 91 , 3 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (132 , 91 , 4 , 'HIGHLIGHTS_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (133 , 91 , 5 , 'SPECIAL_OFFER_MSG' , NULL , '4' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (134 , 92 , 1 , 'SHOW_IMAGES_CURRENCY_MSG' , NULL , '1' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (135 , 92 , 2 , 'SHOW_LISTBOX_CURRENCY_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (136 , 96 , 1 , 'ANY_STATUS_MSG' , NULL , 'ANY' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (137 , 96 , 2 , 'ANY_PAID_STATUS_MSG' , NULL , 'PAID' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (138 , 97 , 1 , 'ALL_USERS_INCLUDING_UNREGISTERED_MSG' , NULL , 'ALL' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (139 , 97 , 2 , 'UNREGISTERED_USER_ONLY_MSG' , NULL , 'NON' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (140 , 97 , 3 , 'ANY_REGISTERED_USERS_MSG' , NULL , 'REG' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (141 , 98 , 1 , 'FIRST_NAME_FIELD' , 'users_bought_item_fn' , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (142 , 98 , 2 , 'LAST_NAME_FIELD' , 'users_bought_item_ln' , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (143 , 98 , 3 , 'NAME_MSG' , 'users_bought_item_name' , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (144 , 98 , 4 , 'NICKNAME_FIELD' , 'users_bought_item_nickname' , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (145 , 98 , 5 , 'COMPANY_NAME_FIELD' , 'users_bought_item_cn' , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (146 , 98 , 6 , 'EMAIL_FIELD' , 'users_bought_item_email' , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (147 , 98 , 7 , 'COUNTRY_FIELD' , 'users_bought_item_country' , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (148 , 98 , 8 , 'STATE_FIELD' , 'users_bought_item_state' , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (149 , 98 , 9 , 'DATE_OF_PURCHASE_MSG' , 'users_bought_item_od' , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (150 , 100 , 1 , 'DONT_SHOW_IMAGE_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (151 , 100 , 2 , 'IMAGE_TINY_MSG' , NULL , '1' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (152 , 100 , 3 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (153 , 100 , 4 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (154 , 103 , 1 , 'DONT_SHOW_IMAGE_MSG' , NULL , '0' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (155 , 103 , 2 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (156 , 103 , 3 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (157 , 104 , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (158 , 104 , 2 , 'SHORT_DESCRIPTION_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (159 , 104 , 3 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (160 , 104 , 4 , 'HOT_DESCRIPTION_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (161 , 105 , 1 , 'DONT_SHOW_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (162 , 105 , 2 , 'DATE_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (163 , 105 , 3 , 'DATE_END_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (164 , 108 , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (165 , 108 , 2 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (166 , 108 , 3 , 'LAST_POST_ADDED_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (167 , 112 , 1 , 'ONELEVEL_LIST_MSG' , NULL , '1' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (168 , 112 , 2 , 'TWOLEVEL_LIST_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (169 , 112 , 3 , 'MULTILEVEL_LIST_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (170 , 112 , 4 , 'TREETYPE_STRUCTURE_MSG' , NULL , '4' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (171 , 115 , 1 , 'DEFAULT_IMAGE_MSG' , NULL , '1' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (172 , 115 , 2 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (173 , 115 , 3 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (174 , 116 , 1 , 'DONT_SHOW_IMAGE_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (175 , 116 , 2 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (176 , 116 , 3 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (177 , 117 , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (178 , 117 , 2 , 'SHORT_DESCRIPTION_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (179 , 117 , 3 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (180 , 122 , 1 , 'DONT_SHOW_IMAGE_MSG' , NULL , '0' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (181 , 122 , 2 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (182 , 122 , 3 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (183 , 123 , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (184 , 123 , 2 , 'SHORT_DESCRIPTION_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (185 , 123 , 3 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (186 , 123 , 4 , 'HOT_DESCRIPTION_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (187 , 124 , 1 , 'DONT_SHOW_MSG' , NULL , '0' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (188 , 124 , 2 , 'DATE_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (189 , 124 , 3 , 'DATE_END_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (190 , 127 , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (191 , 127 , 2 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (192 , 127 , 3 , 'LAST_POST_ADDED_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (193 , 133 , 1 , 'NO_GROUPING_MSG' , NULL , '0' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (194 , 133 , 2 , 'TOP_CATEGORIES_ONLY_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (195 , 133 , 3 , 'ALL_AVAILABLE_CATEGORIES_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (196 , 133 , 4 , 'SELECTED_CATEGORIES_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (197 , 138 , 1 , 'DONT_SHOW_IMAGE_MSG' , NULL , '0' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (198 , 138 , 2 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (199 , 138 , 3 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (200 , 139 , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (201 , 139 , 2 , 'SHORT_DESCRIPTION_MSG' , NULL , '1' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (202 , 139 , 3 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (203 , 139 , 4 , 'HOT_DESCRIPTION_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (204 , 143 , 1 , 'DONT_SHOW_IMAGE_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (205 , 143 , 2 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (206 , 143 , 3 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (207 , 144 , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (208 , 144 , 2 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (209 , 144 , 3 , 'LAST_POST_ADDED_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (210 , 151 , 1 , 'DONT_SHOW_IMAGE_MSG' , NULL , '0' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (211 , 151 , 2 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (212 , 151 , 3 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (213 , 152 , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (214 , 152 , 2 , 'SHORT_DESCRIPTION_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (215 , 152 , 3 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (216 , 152 , 4 , 'HOT_DESCRIPTION_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (217 , 153 , 1 , 'DONT_SHOW_MSG' , NULL , '0' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (218 , 153 , 2 , 'DATE_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (219 , 153 , 3 , 'DATE_END_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (220 , 156 , 1 , 'ONELEVEL_LIST_MSG' , NULL , '1' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (221 , 156 , 2 , 'TWOLEVEL_LIST_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (222 , 156 , 3 , 'MULTILEVEL_LIST_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (223 , 156 , 4 , 'TREETYPE_STRUCTURE_MSG' , NULL , '4' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (224 , 159 , 1 , 'DEFAULT_IMAGE_MSG' , NULL , '1' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (225 , 159 , 2 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (226 , 159 , 3 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (227 , 160 , 1 , 'DONT_SHOW_IMAGE_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (228 , 160 , 2 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (229 , 160 , 3 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (230 , 161 , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (231 , 161 , 2 , 'SHORT_DESCRIPTION_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (232 , 161 , 3 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (233 , 164 , 1 , 'ON_DIFFERENT_PAGES_MSG' , NULL , '1' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (234 , 164 , 2 , 'ON_ONE_PAGE_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (235 , 176 , 1 , 'ALL_USERS_INCLUDING_UNREGISTERED_MSG' , NULL , 'ALL' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (236 , 176 , 2 , 'NON_REGISTERED_USERS_MSG' , NULL , 'NON' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (237 , 176 , 3 , 'ANY_REGISTERED_USERS_MSG' , NULL , 'ANY' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (238 , 177 , 1 , '---' , NULL , NULL , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (239 , 177 , 2 , 'ANY_REGISTERED_ADMIN_MSG' , NULL , 'ANY' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (240 , 181 , 1 , 'ALL_MSG' , NULL , '0' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (241 , 181 , 2 , '1' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (242 , 181 , 3 , '2' , NULL , '2' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (243 , 181 , 4 , '3' , NULL , '3' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (244 , 181 , 5 , '4' , NULL , '4' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (245 , 181 , 6 , '5' , NULL , '5' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (246 , 181 , 7 , '6' , NULL , '6' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (247 , 181 , 8 , '7' , NULL , '7' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (248 , 185 , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (249 , 185 , 2 , 'SHORT_DESCRIPTION_MSG' , NULL , '1' , 0 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES (250 , 185 , 3 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 0 )";


		run_queries($sqls, $queries_success, $queries_failed, $errors, "");

		// options structure
		$cms_options = array(
			"language_selection" => array("name" => "language_selection", "block" => "language_block", "type" => "list"),
			"layouts_selection" => array("name" => "design_selection", "block" => "layouts_block", "type" => "list"),
			"sms_originator" => array("name" => "sms_originator", "block" => "sms_test_block", "type" => "text"),
			"sms_test_message" => array("name" => "sms_test_message", "block" => "sms_test_block", "type" => "text"),
			"products_per_page" => array("name" => "products_per_page", "block" => "products_block", "type" => "text"),
			"products_columns" => array("name" => "products_columns", "block" => "products_block", "type" => "text"),
			"products_nav_type" => array("name" => "products_nav_type", "block" => "products_block", "type" => "list"),
			"products_nav_first_last" => array("name" => "products_nav_first_last", "block" => "products_block", "type" => "check"),
			"products_nav_prev_next" => array("name" => "products_nav_prev_next", "block" => "products_block", "type" => "check"),
			"products_nav_pages" => array("name" => "products_nav_pages", "block" => "products_block", "type" => "text"),
			"products_sortings" => array("name" => "products_sortings", "block" => "products_block", "type" => "check"),
			"products_group_by_cats" => array("name" => "products_group_by_cats", "block" => "products_block", "type" => "check"),
			"products_default_view" => array("name" => "products_default_view", "block" => "products_block", "type" => "list"),
			"use_tabs" => array("name" => "use_tabs", "block" => "details_block", "type" => "list"),
			"details_supersize_image" => array("name" => "show_super_image", "block" => "details_block", "type" => "list"),
			"details_manufacturer_image" => array("name" => "show_manufacturer_image", "block" => "details_block", "type" => "list"),
			"categories_type" => array("name" => "categories_type", "block" => "categories_block", "type" => "list"),
			"subcategories_type" => array("name" => "categories_type", "block" => "subcategories_block", "type" => "list"),
			"categories_columns" => array("name" => "categories_columns", "block" => "categories_block", "type" => "text"),
			"subcategories_columns" => array("name" => "categories_columns", "block" => "subcategories_block", "type" => "text"),
			"categories_subs" => array("name" => "categories_subs", "block" => "categories_block", "type" => "text"),
			"subcategories_subs" => array("name" => "categories_subs", "block" => "subcategories_block", "type" => "text"),
			"categories_image" => array("name" => "categories_image", "block" => "categories_block", "type" => "text"),
			"subcategories_image" => array("name" => "categories_image", "block" => "subcategories_block", "type" => "text"),
			"category_description_type" => array("name" => "category_description_type", "block" => "category_description_block", "type" => "list"),
			"category_description_image" => array("name" => "category_description_image", "block" => "category_description_block", "type" => "list"),
			"shopping_cart_preview" => array("name" => "image_type", "block" => "basket_block", "type" => "list"),
			"basket_prod_recom_per_page" => array("name" => "basket_prod_recom_recs", "block" => "basket_recommended_block", "type" => "text"),
			"basket_prod_recom_cols" => array("name" => "basket_prod_recom_cols", "block" => "basket_recommended_block", "type" => "text"),
			"manufacturers_selection" => array("name" => "manufacturers_selection", "block" => "manufacturers_block", "type" => "list"),
			"manufacturers_image" => array("name" => "manufacturers_image", "block" => "manufacturers_block", "type" => "list"),
			"manufacturers_desc" => array("name" => "manufacturers_desc", "block" => "manufacturers_block", "type" => "list"),
			"manufacturers_order" => array("name" => "manufacturers_order", "block" => "manufacturers_block", "type" => "list"),
			"manufacturers_direction" => array("name" => "manufacturers_direction", "block" => "manufacturers_block", "type" => "list"),
			"manufacturer_info_type" => array("name" => "manufacturer_info_type", "block" => "manufacturer_info_block", "type" => "list"),
			"manufacturer_info_image" => array("name" => "manufacturer_info_image", "block" => "manufacturer_info_block", "type" => "list"),
			"merchants_selection" => array("name" => "merchants_selection", "block" => "merchants_block", "type" => "list"),
			"merchant_info_desc" => array("name" => "merchant_info_desc", "block" => "merchant_info_block", "type" => "text"),
			"merchant_info_image" => array("name" => "merchant_info_image", "block" => "merchant_info_block", "type" => "text"),
			"merchant_info_nick" => array("name" => "merchant_info_nick", "block" => "merchant_info_block", "type" => "text"),
			"merchant_info_country_flag" => array("name" => "merchant_info_country_flag", "block" => "merchant_info_block", "type" => "text"),
			"merchant_info_online" => array("name" => "merchant_info_online", "block" => "merchant_info_block", "type" => "text"),
			"merchant_info_member" => array("name" => "merchant_info_member", "block" => "merchant_info_block", "type" => "text"),
			"merchant_info_prod_link" => array("name" => "merchant_info_prod_link", "block" => "merchant_info_block", "type" => "text"),
			"prod_offers_cols" => array("name" => "prod_offers_cols", "block" => "offers_block", "type" => "test"),
			"prod_offers_recs" => array("name" => "prod_offers_recs", "block" => "offers_block", "type" => "test"),
			"prod_offers_points_price" => array("name" => "prod_offers_points_price", "block" => "offers_block", "type" => "check"),
			"prod_offers_reward_points" => array("name" => "prod_offers_reward_points", "block" => "offers_block", "type" => "check"),
			"prod_offers_reward_credits" => array("name" => "prod_offers_reward_credits", "block" => "offers_block", "type" => "v"),
			"prod_offers_add_button" => array("name" => "prod_offers_add_button", "block" => "offers_block", "type" => "check"),
			"prod_offers_view_button" => array("name" => "prod_offers_view_button", "block" => "offers_block", "type" => "check"),
			"prod_offers_goto_button" => array("name" => "prod_offers_goto_button", "block" => "offers_block", "type" => "check"),
			"prod_offers_wish_button" => array("name" => "prod_offers_wish_button", "block" => "offers_block", "type" => "check"),
			"prod_offers_quantity_control" => array("name" => "prod_offers_quantity_control", "block" => "offers_block", "type" => "listbox"),
			"prod_slider_type" => array("name" => "prod_slider_type", "block" => "offers_block", "type" => "listbox"),
			"prod_slider_width" => array("name" => "prod_slider_width", "block" => "offers_block", "type" => "text"),
			"prod_slider_height" => array("name" => "prod_slider_height", "block" => "offers_block", "type" => "text"),
			"prod_slider_styles" => array("name" => "prod_slider_style", "block" => "offers_block", "type" => "text"),
			"prod_fast_add_points_price" => array("name" => "prod_fast_add_points_price", "block" => "products_fast_add", "type" => "check"),
			"prod_fast_add_reward_points" => array("name" => "prod_fast_add_reward_points", "block" => "products_fast_add", "type" => "check"),
			"prod_fast_add_reward_credits" => array("name" => "prod_fast_add_reward_credits", "block" => "products_fast_add", "type" => "check"),
			"prod_fast_add_add_button" => array("name" => "prod_fast_add_add_button", "block" => "products_fast_add", "type" => "check"),
			"prod_fast_add_view_button" => array("name" => "prod_fast_add_view_button", "block" => "products_fast_add", "type" => "check"),
			"prod_fast_add_goto_button" => array("name" => "prod_fast_add_goto_button", "block" => "products_fast_add", "type" => "check"),
			"prod_fast_add_wish_button" => array("name" => "prod_fast_add_wish_button", "block" => "products_fast_add", "type" => "check"),
			"products_latest_cols" => array("name" => "products_latest_cols", "block" => "products_latest", "type" => "text"),
			"products_latest_recs" => array("name" => "products_latest_recs", "block" => "products_latest", "type" => "text"),
			"prod_latest_image" => array("name" => "prod_latest_image", "block" => "products_latest", "type" => "list"),
			"prod_latest_desc" => array("name" => "prod_latest_desc", "block" => "products_latest", "type" => "list"),
			"prod_latest_order" => array("name" => "prod_latest_order", "block" => "products_latest", "type" => "list"),
			"products_recent_records" => array("name" => "products_recent_records", "block" => "products_recently_viewed", "type" => ""),
			"products_recent_cols" => array("name" => "products_recent_cols", "block" => "products_recently_viewed", "type" => ""),
			"recent_image" => array("name" => "recent_image", "block" => "products_recently_viewed", "type" => ""),
			"recent_desc" => array("name" => "recent_desc", "block" => "products_recently_viewed", "type" => ""),
			"prod_recom_cols" => array("name" => "prod_recom_cols", "block" => "products_recommended", "type" => ""),
			"prod_recom_per_page" => array("name" => "prod_recom_per_page", "block" => "products_recommended", "type" => ""),
			"bestsellers_records" => array("name" => "bestsellers_records", "block" => "products_top_sellers", "type" => ""),
			"bestsellers_days" => array("name" => "bestsellers_days", "block" => "products_top_sellers", "type" => ""),
			"bestsellers_status" => array("name" => "bestsellers_status", "block" => "products_top_sellers", "type" => ""),
			"bestsellers_image" => array("name" => "bestsellers_image", "block" => "products_top_sellers", "type" => ""),
			"bestsellers_desc" => array("name" => "bestsellers_desc", "block" => "products_top_sellers", "type" => ""),
			"top_viewed_image" => array("name" => "top_viewed_image", "block" => "products_top_viewed", "type" => ""),
			"top_viewed_desc" => array("name" => "top_viewed_desc", "block" => "products_top_viewed", "type" => ""),
			"prod_top_viewed_cols" => array("name" => "prod_top_viewed_cols", "block" => "products_top_viewed", "type" => ""),
			"prod_top_viewed_recs" => array("name" => "prod_top_viewed_recs", "block" => "products_top_viewed", "type" => ""),
			"top_rated_image" => array("name" => "top_rated_image", "block" => "top_products_block", "type" => ""),
			"top_rated_desc" => array("name" => "top_rated_desc", "block" => "top_products_block", "type" => ""),
			"related_per_page" => array("name" => "related_per_page", "block" => "related_block", "type" => ""),
			"related_columns" => array("name" => "related_columns", "block" => "related_block", "type" => ""),
			"related_purchase_recs" => array("name" => "related_purchase_recs", "block" => "related_purchase", "type" => ""),
			"related_purchase_cols" => array("name" => "related_purchase_cols", "block" => "related_purchase", "type" => ""),
			"related_purchase_days" => array("name" => "related_purchase_days", "block" => "related_purchase", "type" => ""),
			"related_purchase_status" => array("name" => "related_purchase_status", "block" => "related_purchase", "type" => ""),
			"related_purchase_image" => array("name" => "related_purchase_image", "block" => "related_purchase", "type" => ""),
			"related_purchase_desc" => array("name" => "related_purchase_desc", "block" => "related_purchase", "type" => ""),
			"currency_selection" => array("name" => "currency_selection", "block" => "currency_block", "type" => ""),

			"users_bought_item_recs" => array("name" => "users_bought_item_recs", "block" => "users_bought_item", "type" => ""),
			"users_bought_item_cols" => array("name" => "users_bought_item_cols", "block" => "users_bought_item", "type" => ""),
			"users_bought_item_days" => array("name" => "users_bought_item_days", "block" => "users_bought_item", "type" => ""),
			"users_bought_status" => array("name" => "users_bought_status", "block" => "users_bought_item", "type" => ""),
			"users_bought_type" => array("name" => "users_bought_type", "block" => "users_bought_item", "type" => ""),
			"users_bought_item_fn" => array("name" => "users_bought_item_fn", "block" => "users_bought_item", "type" => ""),
			"users_bought_item_ln" => array("name" => "users_bought_item_ln", "block" => "users_bought_item", "type" => ""),
			"users_bought_item_name" => array("name" => "users_bought_item_name", "block" => "users_bought_item", "type" => ""),
			"users_bought_item_nickname" => array("name" => "users_bought_item_nickname", "block" => "users_bought_item", "type" => ""),
			"users_bought_item_cn" => array("name" => "users_bought_item_cn", "block" => "users_bought_item", "type" => ""),
			"users_bought_item_email" => array("name" => "users_bought_item_email", "block" => "users_bought_item", "type" => ""),
			"users_bought_item_country" => array("name" => "users_bought_item_country", "block" => "users_bought_item", "type" => ""),
			"users_bought_item_state" => array("name" => "users_bought_item_state", "block" => "users_bought_item", "type" => ""),
			"users_bought_item_od" => array("name" => "users_bought_item_od", "block" => "users_bought_item", "type" => ""),
			"wl_recs" => array("name" => "wl_recs", "block" => "wishlist_items", "type" => ""),
			"wl_image" => array("name" => "wl_image", "block" => "wishlist_items", "type" => ""),
			"articles_related_per_page" => array("name" => "articles_related_per_page", "block" => "articles_related_block", "type" => ""),
			"articles_related_columns" => array("name" => "articles_related_columns", "block" => "articles_related_block", "type" => ""),
			"articles_related_image" => array("name" => "articles_related_image", "block" => "articles_related_block", "type" => ""),
			"articles_related_desc" => array("name" => "articles_related_desc", "block" => "articles_related_block", "type" => ""),
			"articles_related_date" => array("name" => "articles_related_date", "block" => "articles_related_block", "type" => ""),
			"forums_related_per_page" => array("name" => "forums_related_per_page", "block" => "forums_related_block", "type" => ""),
			"forums_related_columns" => array("name" => "forums_related_columns", "block" => "forums_related_block", "type" => ""),
			"forums_related_desc" => array("name" => "forums_related_desc", "block" => "forums_related_block", "type" => ""),
			"forums_related_user_info" => array("name" => "forums_related_user_info", "block" => "forums_related_block", "type" => ""),
			"a_list_recs" => array("name" => "articles_recs", "block" => "a_list", "type" => ""),
			"a_list_cols" => array("name" => "articles_cols", "block" => "a_list", "type" => ""),
			"a_cats_type" => array("name" => "articles_categories_type", "block" => "a_cats", "type" => ""),
			"a_subcats_type" => array("name" => "articles_categories_type", "block" => "a_subcats", "type" => ""),
			"a_cats_cols" => array("name" => "articles_categories_cols", "block" => "a_cats", "type" => ""),
			"a_subcats_cols" => array("name" => "articles_categories_cols", "block" => "a_subcats", "type" => ""),
			"a_cats_subs" => array("name" => "articles_categories_subs", "block" => "a_cats", "type" => ""),
			"a_subcats_subs" => array("name" => "articles_categories_subs", "block" => "a_subcats", "type" => ""),
			"a_cats_image" => array("name" => "articles_categories_image", "block" => "a_cats", "type" => ""),
			"a_cat_desc_image" => array("name" => "articles_category_image", "block" => "a_cat_desc", "type" => ""),
			"a_cat_desc_type" => array("name" => "articles_category_desc_type", "block" => "a_cat_desc", "type" => ""),
			"a_hot_recs" => array("name" => "articles_hot_recs", "block" => "a_hot", "type" => ""),
			"a_hot_cols" => array("name" => "articles_hot_cols", "block" => "a_hot", "type" => ""),
			"a_related_per_page" => array("name" => "articles_related_recs", "block" => "a_related", "type" => ""),
			"a_related_columns" => array("name" => "articles_related_cols", "block" => "a_related", "type" => ""),
			"a_related_image" => array("name" => "articles_related_image", "block" => "a_related", "type" => ""),
			"a_related_desc" => array("name" => "articles_related_desc", "block" => "a_related", "type" => ""),
			"a_related_date" => array("name" => "articles_related_date", "block" => "a_related", "type" => ""),
			"a_forums_related_per_page" => array("name" => "articles_related_forums_recs", "block" => "a_forums_related", "type" => ""),
			"a_forums_related_columns" => array("name" => "articles_related_forums_cols", "block" => "a_forums_related", "type" => ""),
			"a_forums_related_desc" => array("name" => "articles_related_forums_desc", "block" => "a_forums_related", "type" => ""),
			"a_forums_related_user_info" => array("name" => "articles_related_forums_author", "block" => "a_forums_related", "type" => ""),
			"a_item_related_recs" => array("name" => "articles_related_products_recs", "block" => "a_item_related", "type" => ""),
			"a_item_related_cols" => array("name" => "articles_related_products_cols", "block" => "a_item_related", "type" => ""),
			"a_cat_item_related_recs" => array("name" => "articles_products_cats_recs", "block" => "a_cat_item_related", "type" => ""),
			"a_cat_item_related_cols" => array("name" => "articles_products_cats_cols", "block" => "a_cat_item_related", "type" => ""),
			"a_latest_group_by" => array("name" => "articles_latest_group_by", "block" => "a_latest", "type" => ""),
			"a_latest_cats" => array("name" => "articles_latest_cats", "block" => "a_latest", "type" => ""),
			"a_latest_subcats" => array("name" => "articles_latest_subcats", "block" => "a_latest", "type" => ""),
			"a_latest_recs" => array("name" => "articles_latest_recs", "block" => "a_latest", "type" => ""),
			"a_latest_subrecs" => array("name" => "articles_latest_subrecs", "block" => "a_latest", "type" => ""),
			"a_latest_cols" => array("name" => "articles_latest_cols", "block" => "a_latest", "type" => ""),
			"a_latest_image" => array("name" => "articles_latest_image", "block" => "a_latest", "type" => ""),
			"a_latest_desc" => array("name" => "articles_latest_desc", "block" => "a_latest", "type" => ""),
			"a_top_viewed_recs" => array("name" => "articles_top_viewed_recs", "block" => "a_top_viewed", "type" => ""),
			"a_top_viewed_cols" => array("name" => "articles_top_viewed_cols", "block" => "a_top_viewed", "type" => ""),

			"forum_description_image" => array("name" => "forum_description_image", "block" => "forum_description", "type" => ""),
			"forum_description_type" => array("name" => "forum_description_type", "block" => "forum_description", "type" => ""),
			"forum_top_viewed_recs" => array("name" => "forum_top_viewed_recs", "block" => "forum_top_viewed", "type" => ""),
			"forum_top_viewed_cols" => array("name" => "forum_top_viewed_cols", "block" => "forum_top_viewed", "type" => ""),
			"forum_latest_recs" => array("name" => "forum_latest_recs", "block" => "forum_latest", "type" => ""),
			"forum_latest_cols" => array("name" => "forum_latest_cols", "block" => "forum_latest", "type" => ""),
			"forum_articles_related_per_page" => array("name" => "forum_articles_related_recs", "block" => "forum_articles_related_block", "type" => ""),
			"forum_articles_related_columns" => array("name" => "forum_articles_related_cols", "block" => "forum_articles_related_block", "type" => ""),
			"forum_articles_related_image" => array("name" => "forum_articles_related_image", "block" => "forum_articles_related_block", "type" => ""),
			"forum_articles_related_desc" => array("name" => "forum_articles_related_desc", "block" => "forum_articles_related_block", "type" => ""),
			"forum_articles_related_date" => array("name" => "forum_articles_related_date", "block" => "forum_articles_related_block", "type" => ""),
			"forum_item_related_per_page" => array("name" => "forum_item_related_recs", "block" => "forum_item_related_block", "type" => ""),
			"forum_item_related_columns" => array("name" => "forum_item_related_cols", "block" => "forum_item_related_block", "type" => ""),

			"ads_categories_type" => array("name" => "ads_categories_type", "block" => "ads_categories", "type" => ""),
			"ads_subcategories_type" => array("name" => "ads_categories_type", "block" => "ads_subcategories", "type" => ""),
			"ads_categories_columns" => array("name" => "ads_categories_cols", "block" => "ads_categories", "type" => ""),
			"ads_subcategories_columns" => array("name" => "ads_categories_cols", "block" => "ads_subcategories", "type" => ""),
			"ads_categories_subs" => array("name" => "ads_categories_subs", "block" => "ads_categories", "type" => ""),
			"ads_subcategories_subs" => array("name" => "ads_categories_subs", "block" => "ads_subcategories", "type" => ""),
			"ads_categories_image" => array("name" => "ads_categories_image", "block" => "ads_categories", "type" => ""),
			"ads_cat_desc_image" => array("name" => "ads_cat_desc_image", "block" => "ads_category_info", "type" => ""),
			"ads_cat_desc_type" => array("name" => "ads_cat_desc_type", "block" => "ads_category_info", "type" => ""),
			"ads_list_per_page" => array("name" => "ads_list_recs", "block" => "ads_list", "type" => ""),
			"ads_list_columns" => array("name" => "ads_list_cols", "block" => "ads_list", "type" => ""),
			"ads_details_tabs" => array("name" => "ads_details_tabs", "block" => "ads_details", "type" => ""),
			"ads_hot_recs" => array("name" => "ads_hot_recs", "block" => "ads_hot", "type" => ""),
			"ads_hot_cols" => array("name" => "ads_hot_cols", "block" => "ads_hot", "type" => ""),
			"ads_special_recs" => array("name" => "ads_special_recs", "block" => "ads_special", "type" => ""),
			"ads_special_cols" => array("name" => "ads_special_cols", "block" => "ads_special", "type" => ""),
			"ads_latest_recs" => array("name" => "ads_latest_recs", "block" => "ads_latest", "type" => ""),
			"ads_latest_cols" => array("name" => "ads_latest_cols", "block" => "ads_latest", "type" => ""),
			"ads_top_viewed_recs" => array("name" => "ads_top_viewed_recs", "block" => "ads_top_viewed", "type" => ""),
			"ads_top_viewed_cols" => array("name" => "ads_top_viewed_cols", "block" => "ads_top_viewed", "type" => ""),
			"ads_recent_records" => array("name" => "ads_recent_recs", "block" => "ads_recently_viewed", "type" => ""),
			"filter_values_limit" => array("name" => "filter_values_limit", "block" => "filter", "type" => ""),
			"cb_css_class" => array("name" => "cb_css_class", "block" => "custom_block", "type" => ""),
			"cb_user_type" => array("name" => "cb_user_type", "block" => "custom_block", "type" => ""),
			"cb_admin_type" => array("name" => "cb_admin_type", "block" => "custom_block", "type" => ""),
			"cb_params" => array("name" => "cb_params", "block" => "custom_block", "type" => ""),
			"bg_limit" => array("name" => "bg_limit", "block" => "banners_group", "type" => ""),
			"bg_params" => array("name" => "bg_params", "block" => "banners_group", "type" => ""),
			"navigation_visible_depth_level" => array("name" => "visible_depth_level", "block" => "navigation_block", "type" => ""),
		);

		// delete all settings before start
		$sqls[] = "DELETE FROM ".$table_prefix."cms_pages_settings";
		$sqls[] = "DELETE FROM ".$table_prefix."cms_frames_settings";
		$sqls[] = "DELETE FROM ".$table_prefix."cms_pages_blocks";
		$sqls[] = "DELETE FROM ".$table_prefix."cms_blocks_settings";
		run_queries($sqls, $queries_success, $queries_failed, $errors, "");

		// initialize objects
		$ps = new VA_Record($table_prefix . "cms_pages_settings");
		$ps->add_where("ps_id", INTEGER);
		$ps->add_textbox("page_id", INTEGER);
		$ps->add_textbox("key_code", TEXT);
		$ps->change_property("key_code", USE_SQL_NULL, false);
		$ps->add_textbox("key_type", TEXT);
		$ps->change_property("key_type", USE_SQL_NULL, false);
		$ps->add_textbox("key_rule", TEXT);
		$ps->add_textbox("layout_id", INTEGER);
		$ps->add_textbox("site_id", INTEGER);
		$ps->add_textbox("meta_title", TEXT);
		$ps->add_textbox("meta_keywords", TEXT);
		$ps->add_textbox("meta_description", TEXT);

		$pf = new VA_Record($table_prefix . "cms_frames_settings");
		$pf->add_textbox("ps_id", INTEGER);
		$pf->add_textbox("frame_id", INTEGER);
		$pf->add_textbox("frame_style", TEXT);

		$pb = new VA_Record($table_prefix . "cms_pages_blocks");
		$pb->add_where("pb_id", INTEGER);
		$pb->add_textbox("ps_id", INTEGER);
		$pb->add_textbox("frame_id", INTEGER);
		$pb->add_textbox("block_id", INTEGER);
		$pb->add_textbox("block_key", TEXT);
		$pb->add_textbox("block_order", INTEGER);

		$bs = new VA_Record($table_prefix . "cms_blocks_settings");
		$bs->add_textbox("pb_id", INTEGER);
		$bs->add_textbox("ps_id", INTEGER);
		$bs->add_textbox("property_id", INTEGER);
		$bs->add_textbox("value_id", INTEGER);
		$bs->add_textbox("variable_name", TEXT);
		$bs->add_textbox("variable_value", TEXT);

		$ignore = array(
			"ads_user_type_id" => 1,
			"recent_records" => 1,
			"basket_fast_checkout" => 1,
			"details_manufacture_image" => 1,
			"merchant_info_link" => 1,
			"prod_slider_border" => 1,
			"usehome_pages" => 1,
			"layout_type" => 1,
			"image_effect" => 1,
			"basket_google_checkout" => 1,
			"google_checkout_country_required" => 1,
			"google_checkout_country_show" => 1,
			"google_checkout_postcode_required" => 1,
			"google_checkout_postcode_show" => 1,
			"google_checkout_state_required" => 1,
			"google_checkout_state_show" => 1,
			"site_map_ad_categories" => 1,
			"site_map_ads" => 1,
			"site_map_articles" => 1,
			"site_map_articles_categories" => 1,
			"site_map_categories" => 1,
			"site_map_forum_categories" => 1,
			"site_map_forums" => 1,
			"site_map_items" => 1,
			"site_map_manual_categories" => 1,
			"site_map_manuals" => 1,
		);

		$ignore_pages = array(
			"categories" => 1,
			"detailed" => 1,
			"details" => 1,
			"products" => 1,
			"usehome_pages" => 1,
			"support_list" => 1,
		);

		$fast_options = array();
		$fast_checkout = array(
			"fast_checkout_country_show" => 1, 
			"fast_checkout_country_required" => 1,
			"fast_checkout_state_show" => 1,
			"fast_checkout_state_required" => 1,
			"fast_checkout_postcode_show" => 1,
			"fast_checkout_postcode_required" => 1,
		);

		// check all available settings for the pages
		$convert_pages = array();
		$sql  = " SELECT site_id, page_name FROM " . $table_prefix . "page_settings ";
		$sql .= " GROUP BY site_id, page_name ";
		$db->query($sql);
		while ($db->next_record()) {
			$page_site_id = $db->f("site_id");
			$page_name = $db->f("page_name");
			$convert_pages[] = array(
				"site_id" => $page_site_id,
				"page_name" => $page_name,
			);
		}
		// check new pages which we need to add
		foreach ($cms_pages as $page_code => $page_info) {
			if (isset($page_info["new"]) && $page_info["new"]) {
				if ($page_code == "article_reviews") {
					$sql  = " SELECT category_id FROM " . $table_prefix . "articles_categories ";	
					$sql .= " WHERE parent_category_id=0 ";
					$db->query($sql);
					while ($db->next_record()) {
						$category_id = $db->f("category_id");
						$convert_pages[] = array(
							"site_id" => 1,
							"page_name" => $page_code."_".$category_id,
						);
					}
				} else {
					$convert_pages[] = array(
						"site_id" => 1,
						"page_name" => $page_code,
					);
				}
			}
		}
		
		
		$ignored_options = 0;
		$found_frames = 0;
		$found_blocks = 0;
		$found_options = 0;
		$missed_options = 0;
		// transfer pages settings
		foreach ($convert_pages as $id => $old_page) {
			$page_site_id = $old_page["site_id"];
			$db_page_name = $old_page["page_name"];
			$page_key_code = ""; $page_key_type = ""; $page_key_rule = "";
			$meta_title = ""; $meta_keywords = ""; $meta_description = "";
			if (preg_match("/^([\w_]+)_(\d+)$/", $db_page_name, $matches)) {
				$page_name = $matches[1];
				$page_key_code = $matches[2];
			} else {
				$page_name = $db_page_name;
			}
			//echo "<br>DB: $db_page_name Page: $page_name Key: $key_code";

			if (isset($cms_pages[$page_name])) {
				$is_new = isset($cms_pages[$page_name]["new"]) ? $cms_pages[$page_name]["new"] : false;
				// get all page settings
				$page_settings = array(); $block_orders = array();
				if (!$is_new) {
					$sql  = " SELECT setting_name,setting_value,setting_order FROM " . $table_prefix . "page_settings ";
					$sql .= " WHERE page_name=" . $db->tosql($db_page_name, TEXT);
					$sql .= " AND site_id=" . $db->tosql($page_site_id, INTEGER);
					$sql .= " AND layout_id=0 ";
					$db->query($sql);
					while ($db->next_record()) {
						$setting_name = $db->f("setting_name");
						$setting_value = $db->f("setting_value");
						$setting_order = $db->f("setting_order");
						$page_settings[$setting_name] = $setting_value;
						$block_orders[$setting_name] = $setting_order;
					}
				}
				// check layout_type for some pages
				if (strlen($page_key_code)) {
					if ($page_name == "custom_page") {
						$page_key_rule = get_setting_value($page_settings, "layout_type", "default");
					} else if ($page_name == "products_list") {
						$page_key_type = "category";
						$page_key_rule = get_setting_value($page_settings, "layout_type", "default");
					} else if ($page_name == "a_list" || $page_name == "a_details" || $page_name == "article_reviews" ) {
						$page_key_type = "category";
						$page_key_rule = "all"; 
					}
				}
				// check and correct columns width 
				$current_percent = 0; $new_percent = 0;
				$width_columns = array("left" => 0, "middle" => 0, "right" => 0);
				foreach ($width_columns as $column_name => $column_width) {
					$column_width = get_setting_value($page_settings, $column_name."_column_width", "");
					if (preg_match("/(\d+)\%/", $column_width , $matches)) {
						$width_percent = $matches[1];
					} else {
						$width_percent = 0;
					}
					$width_columns[$column_name] = $width_percent;
					$current_percent += $width_percent;
				}
				if ($current_percent > 100) {
					// decrease values
					foreach ($width_columns as $column_name => $column_width) {
						$column_width = intval($column_width / $current_percent * 100);
						$width_columns[$column_name] = $column_width;
						$new_percent += $column_width;
					}
					// save new values
					foreach ($width_columns as $column_name => $column_width) {
						if ($new_percent< 100) {
							$column_width++;
							$new_percent++;
						}
						$page_settings[$column_name."_column_width"] = $column_width . "%";
					}
				}


				if (!$is_new) {
					$layout_id = 1;
					$page_frames = array(
						"header" => array("id" => "1", "tag" => "header", "style" => ""),
						"left"   => array("id" => "2", "tag" => "left"  , "style" => ""),
						"middle" => array("id" => "3", "tag" => "middle", "style" => ""),
						"right"  => array("id" => "4", "tag" => "right" , "style" => ""),
						"footer" => array("id" => "5", "tag" => "footer", "style" => ""),
					);
				} else {
					$layout_id = 3;
					$page_frames = array(
						"header" => array("id" => "10", "tag" => "header", "style" => ""),
						"middle" => array("id" => "11", "tag" => "middle", "style" => ""),
						"footer" => array("id" => "12", "tag" => "footer", "style" => ""),
					);
				}

				$page_blocks = array(
					"header" => array("block_id" => 118, "key" => "", "frame_tag" => "header", "block_order" => 1),
					"footer" => array("block_id" => 119, "key" => "", "frame_tag" => "footer", "block_order" => 1),
				); 

				if ($is_new) {
					if (preg_match("/^user_/", $page_name)) {
						// added breadcrumb block first
						$page_blocks["user_account_breadcrumb"] = array("block_id" => 14, "key" => "userhome_breadcrumb", "frame_tag" => "middle", "block_order" => 1);
						$main_block_order = 2;
					} else {
						$main_block_order = 1;
					}
					$block_id = isset($cms_pages[$page_name]["block_id"]) ? $cms_pages[$page_name]["block_id"] : "";
					if ($block_id) {
						$page_blocks["middle_".$block_id] = array("block_id" => $block_id, "key" => $page_key_code, "frame_tag" => "middle", "block_order" => $main_block_order);
					}
				}

				if ($page_name == "index") {
					// check greetings for index page
					$greeting_html = trim($settings["html_on_index"]);
					if (strlen($greeting_html)) {
						// TODO: remove query to delete greetings blocks
						$sql  = " DELETE FROM " . $table_prefix . "custom_blocks ";
						$sql .= " WHERE block_name=". $db->tosql(GREETINGS_INTRODUCTION_MSG, TEXT);
						$db->query($sql);

						$sql  = " INSERT INTO " . $table_prefix . "custom_blocks ";
						$sql .= "(block_name, block_desc) VALUES (";
						$sql .= $db->tosql(GREETINGS_INTRODUCTION_MSG, TEXT) . ", ";
						$sql .= $db->tosql($greeting_html, TEXT) . ") ";
						$db->query($sql);
	
						$new_id = get_db_value(" SELECT MAX(block_id) FROM " . $table_prefix . "custom_blocks");
						$page_blocks["custom_block_".$new_id] = array("block_id" => 115, "key" => $new_id, "frame_tag" => "header", "block_order" => 2);
					}

					// check meta data for index page
					$meta_title = get_setting_value($settings, "index_title");
					$meta_description = get_setting_value($settings, "index_description");
					$meta_keywords = get_setting_value($settings, "index_keywords");
				} else if ($page_name == "products_list") {
					// check meta data for products page
					$meta_title = get_setting_value($settings, "products_title");
					$meta_description = get_setting_value($settings, "products_description");
					$meta_keywords = get_setting_value($settings, "products_keywords");
				}

				$blocks_options = array();
				// match CMS blocks and their options
				foreach ($page_settings as $db_setting_name => $setting_value) {
					if (preg_match("/^([\w_]+)_(\d+)$/", $db_setting_name, $matches)) {
						$setting_name = $matches[1];
						$key_code = $matches[2];
					} else {
						$setting_name = $db_setting_name;
						$key_code = "";
					}

					if (isset($ignore[$setting_name])) {
						$ignored_options++;
					} else if (isset($fast_checkout[$setting_name])) {
						if ($setting_value) {
							$fast_options[$setting_name.$page_site_id] = array(
								"name" => $setting_name, "value" => $setting_value, "site" => $page_site_id);
						}
					} else if (isset($frame_settings[$setting_name])) {
						$found_frames++;
						$frame_id = $frame_settings[$setting_name]["id"];
						$frame_tag = $frame_settings[$setting_name]["tag"];
						$style_type = $frame_settings[$setting_name]["type"];
						$frame_style = $frame_settings[$setting_name]["style"];
						if ($style_type == "bool" && $setting_value) {
							$page_frames[$frame_tag]["style"] .= $frame_style;
						} else if ($style_type == "text" && strlen($setting_value)) {
							// ignoring old width values for new design
							/*
							if ($setting_name == "left_column_width" || $setting_name == "right_column_width" || $setting_name == "middle_column_width") {
								if (preg_match("/(\d+)\%/", $setting_value, $matches)) {
									$setting_value = (floatval($matches[1]) - 1) . "%";
								}
							}
							$page_frames[$frame_tag]["style"] .= $frame_style . $setting_value . "; ";
							//*/
						}
					} else if (isset($cms_blocks[$setting_name])) {
						$found_blocks++;
						$block_id = $cms_blocks[$setting_name]["id"];
						$frame_id = $page_frames[$setting_value]["id"];
						$frame_tag = $page_frames[$setting_value]["tag"];
						//$frame_id = $frame_settings[$setting_value]["id"];
						$block_order = $block_orders[$db_setting_name];
						// use original db setting_name as an array key
						$page_blocks[$db_setting_name] = array(
							"block_id" => $block_id,
							"frame_tag" => $frame_tag,
							"block_order" => $block_order,
							"key" => $key_code,

						);
					} else if (isset($cms_options[$setting_name])) {
						$found_options++;
						$var_name = $cms_options[$setting_name]["name"];
						$block_name = $cms_options[$setting_name]["block"];
						$block_id = $cms_blocks[$block_name]["id"];
						$db_block_name = $block_name;
						if (strlen($key_code)) {
							$db_block_name .= "_".$key_code;
						}
						// TODO: get property_id and value_id data
						$property_id = ""; $value_id = "";
						$sql  = " SELECT cbp.property_id, cbv.value_id ";
						$sql .= " FROM (" . $table_prefix . "cms_blocks_values cbv ";
						$sql .= " INNER JOIN " . $table_prefix . "cms_blocks_properties cbp ON cbv.property_id=cbp.property_id) ";
						$sql .= " WHERE cbp.block_id=" . $db->tosql($block_id, INTEGER);
						$sql .= " AND cbv.variable_name=" . $db->tosql($var_name, TEXT);
						$db->query($sql);
						if ($db->next_record()) {
							$property_id = $db->f("property_id");
							$value_id = $db->f("value_id");
						} else {
							$sql  = " SELECT cbp.property_id, cbv.value_id ";
							$sql .= " FROM (" . $table_prefix . "cms_blocks_values cbv ";
							$sql .= " INNER JOIN " . $table_prefix . "cms_blocks_properties cbp ON cbv.property_id=cbp.property_id) ";
							$sql .= " WHERE cbp.block_id=" . $db->tosql($block_id, INTEGER);
							$sql .= " AND cbp.variable_name=" . $db->tosql($var_name, TEXT);
							$sql .= " AND cbv.variable_value=" . $db->tosql($setting_value, TEXT);
							$db->query($sql);
							if ($db->next_record()) {
								$property_id = $db->f("property_id");
								$value_id = $db->f("value_id");
							} else {
								$sql  = " SELECT cbp.property_id ";
								$sql .= " FROM " . $table_prefix . "cms_blocks_properties cbp ";
								$sql .= " WHERE cbp.block_id=" . $db->tosql($block_id, INTEGER);
								$sql .= " AND cbp.variable_name=" . $db->tosql($var_name, TEXT);
								$db->query($sql);
								if ($db->next_record()) {
									$property_id = $db->f("property_id");
								}
							}
						}
						//if (!$property_id && !$value_id) { echo "<br><b>Error</b>: $var_name  $sql"; }

						$blocks_options[$db_block_name][] = array(
							"variable_name" => $var_name,
							"variable_value" => $setting_value,
							"property_id" => $property_id,
							"value_id" => $value_id,
						);
					} else {
						$missed_options++;
						// page existed so we can proceed
						$errors .= "Can't match block/option: " . $setting_name . "<br>\r\n";
						output_block_info($errors, "queriesErrors");
					}
				}

				// add settings to page
				$page_id = $cms_pages[$page_name]["id"];
  			//$ps->set_value("ps_id", $ps_id);
				$ps->set_value("page_id", $page_id);
				$ps->set_value("key_code", $page_key_code);
				$ps->set_value("key_type", $page_key_type);
				$ps->set_value("key_rule", $page_key_rule);

				$ps->set_value("meta_title", $meta_title);
				$ps->set_value("meta_keywords", $meta_keywords);
				$ps->set_value("meta_description", $meta_description);

				$ps->set_value("layout_id", $layout_id);
				$ps->set_value("site_id", $page_site_id);
				$ps->insert_record();

				if($db->DBType == "mysql") {
					$ps_id = get_db_value(" SELECT LAST_INSERT_ID() ");
				} else {
					$ps_id = get_db_value(" SELECT MAX(ps_id) FROM " . $table_prefix . "cms_pages_settings");
				}

				foreach($page_frames as $frame_tag => $frame) {
					$pf->set_value("ps_id", $ps_id);
					$pf->set_value("frame_id", $frame["id"]);
					$pf->set_value("frame_style", $frame["style"]);
					$pf->insert_record();
				}

				// adding page blocks
				foreach($page_blocks as $code => $block) {
					$frame_tag = $block["frame_tag"];
					$frame_id = $page_frames[$frame_tag]["id"];
					//$pb->add_where("pb_id", INTEGER);
					$pb->set_value("ps_id", $ps_id);
					$pb->set_value("block_id", $block["block_id"]);
					$pb->set_value("frame_id", $frame_id);
					$pb->set_value("block_key", $block["key"]);
					$pb->set_value("block_order", $block["block_order"]);
					$pb->insert_record();

					if($db->DBType == "mysql") {
						$pb_id = get_db_value(" SELECT LAST_INSERT_ID() ");
					} else {
						$pb_id = get_db_value(" SELECT MAX(pb_id) FROM " . $table_prefix . "cms_pages_blocks");
					}

					$page_blocks[$code]["pb_id"] = $pb_id;
				}

				// adding blocks properties
				foreach($blocks_options as $block_name => $properties) {
					if (isset($page_blocks[$block_name])) {
						$pb_id = $page_blocks[$block_name]["pb_id"];
						$bs->set_value("pb_id", $pb_id);
						$bs->set_value("ps_id", $ps_id);
						foreach($properties as $id => $property) {
							$bs->set_value("property_id", $property["property_id"]);
							$bs->set_value("value_id", $property["value_id"]);
							$bs->set_value("variable_name", $property["variable_name"]);
							$bs->set_value("variable_value", $property["variable_value"]);
							$bs->insert_record();
						}
					}
				}

			} else if (!isset($ignore_pages[$page_name])) {
				// page existed so we can proceed
				$errors .= "Can't match the page: " . $page_name . "<br>\r\n";
				output_block_info($errors, "queriesErrors");
			}
		}

		// move fast checkout options to global settings
		if (sizeof($fast_options) > 0) {
			$sql = "DELETE FROM " . $table_prefix . "global_settings WHERE setting_name LIKE 'fast_checkout_%'";
			$db->query($sql);
			foreach($fast_options as $id => $option) {
				$setting_name = $option["name"];
				$setting_value = $option["value"];
				$setting_site_id = $option["site"];

				$sql  = "INSERT INTO " . $table_prefix . "global_settings (setting_type, setting_name, setting_value, site_id) VALUES (";
				$sql .= "'products', " . $db->tosql($setting_name, TEXT). "," . $db->tosql($setting_value, TEXT) . ",";
				$sql .= $db->tosql($setting_site_id,INTEGER) . ") ";
				$db->query($sql);
			}
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0");
	}


?>