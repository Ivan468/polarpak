<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_upgrade_sqls_4.1.php                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	check_admin_security("system_upgrade");

	if (comp_vers("4.0.1", $current_db_version) == 1)
	{
		$sql = " SELECT MAX(property_id) FROM ".$table_prefix."cms_blocks_properties "; 
		$property_id = get_db_value($sql);

		$sql = " SELECT MAX(value_id) FROM ".$table_prefix."cms_blocks_values "; 
		$value_id = get_db_value($sql);

		// adding new options for special offer block
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='products_offers'"; 
		$block_id = get_db_value($sql);

		$sql = " SELECT MAX(property_order) FROM ".$table_prefix."cms_blocks_properties WHERE block_id=" . $db->tosql($block_id, INTEGER); 
		$property_order = get_db_value($sql);

		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";

		$property_id++; $property_order++;
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,end_html) VALUES ($property_id, $block_id, $property_order, 'POPUP_BOX_MSG' , 'CHECKBOX' , NULL , NULL , 'popup_box' , NULL , 0 , 'SHOW_BOX_MOUSE_OVER_IMAGE_MSG')";

		$property_id++; $property_order++;
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,end_html) VALUES ($property_id, $block_id, $property_order, 'BOX_IMAGE_TYPE_MSG' , 'LISTBOX' , NULL , NULL , 'box_image_type' , NULL , 0 , NULL)";
		$value_id++;
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $property_id , 1 , 'DONT_SHOW_IMAGE_MSG' , NULL , '0' , 0 , 0 )";
		$value_id++;
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $property_id , 2 , 'IMAGE_SMALL_MSG' , NULL , '2' , 0 , 0 )";
		$value_id++;
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $property_id , 3 , 'IMAGE_LARGE_MSG' , NULL , '3' , 0 , 1 )";

		$property_id++; $property_order++;
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,end_html) VALUES ($property_id, $block_id, $property_order, 'BOX_DESC_TYPE_MSG' , 'LISTBOX' , NULL , NULL , 'box_desc_type' , NULL , 0 , NULL)";
		$value_id++;
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $property_id , 1 , 'DONT_SHOW_DESC_MSG' , NULL , '0' , 0 , 1 )";
		$value_id++;
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $property_id , 2 , 'SHORT_DESCRIPTION_MSG' , NULL , '1' , 0 , 0 )";
		$value_id++;
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $property_id , 3 , 'FULL_DESCRIPTION_MSG' , NULL , '2' , 0 , 0 )";
		$value_id++;
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $property_id , 4 , 'HIGHLIGHTS_MSG' , NULL , '3' , 0 , 0 )";
		$value_id++;
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $property_id , 5 , 'SPECIAL_OFFER_MSG' , NULL , '4' , 0 , 0 )";

		// adding new options for details block
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='product_details'"; 
		$block_id = get_db_value($sql);

		$sql = " SELECT MAX(property_order) FROM ".$table_prefix."cms_blocks_properties WHERE block_id=" . $db->tosql($block_id, INTEGER); 
		$property_order = get_db_value($sql);

		$sql = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties WHERE variable_name='show_super_image'"; 
		$super_image_property_id = get_db_value($sql);

		$value_id++;
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $super_image_property_id, 3 , 'SUPER_IMAGE_IN_ZOOM_BOX_MSG' , NULL , '2' , 0 , 0 )";
		$super_image_value_id = $value_id;

		$property_id++; $property_order++;
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'ZOOM_WIDTH_MSG' , 'TEXTBOX' , $super_image_property_id, $super_image_value_id, 'zoom_width', '200', 0)";
		$property_id++; $property_order++;
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'ZOOM_HEIGHT_MSG' , 'TEXTBOX' , $super_image_property_id, $super_image_value_id, 'zoom_height', '200', 0)";

		// adding new fields into orders table
		$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_currency_code VARCHAR(4) ";
		$sql_types = array(
			"mysql"  => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_currency_rate DOUBLE(16,8) default '1' ",
			"postgre"=> "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_currency_rate FLOAT4 default '1' ",
			"access" => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN payment_currency_rate FLOAT "
		);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "UPDATE " . $table_prefix . "orders SET payment_currency_code=currency_code, payment_currency_rate=currency_rate ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.1");
	}


	if (comp_vers("4.0.2", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN invoice_copy_number INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN invoice_copy_number INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN invoice_copy_number INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "orders SET invoice_copy_number=0 ";

		$mysql_sql  = "CREATE TABLE " . $table_prefix . "categories_columns (
      `column_id` INT(11) NOT NULL AUTO_INCREMENT,
      `category_id` INT(11) NOT NULL default '0',
      `column_order` TINYINT default '0',
      `column_code` VARCHAR(64),
      `column_title` VARCHAR(255),
      `column_html` TEXT
      ,KEY category_id (category_id)
      ,PRIMARY KEY (column_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_" . $table_prefix . "categories_columns START 1";
		}
		$postgre_sql  = "CREATE TABLE " . $table_prefix . "categories_columns (
      column_id INT4 NOT NULL DEFAULT nextval('seq_" . $table_prefix . "categories_columns'),
      category_id INT4 NOT NULL default '0',
      column_order SMALLINT default '0',
      column_code VARCHAR(64),
      column_title VARCHAR(255),
      column_html TEXT
      ,PRIMARY KEY (column_id))";

		$access_sql  = "CREATE TABLE " . $table_prefix . "categories_columns (
      [column_id]  COUNTER  NOT NULL,
      [category_id] INTEGER,
      [column_order] BYTE,
      [column_code] VARCHAR(64),
      [column_title] VARCHAR(255),
      [column_html] LONGTEXT
      ,PRIMARY KEY (column_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX " . $table_prefix . "categories_columns_categ_19 ON " . $table_prefix . "categories_columns (category_id)";
		}

		$sqls[] = "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN property_code VARCHAR(32) ";
		$sqls[] = "UPDATE " . $table_prefix . "items_properties SET property_code='' ";
		$sqls[] = "CREATE INDEX " . $table_prefix . "items_properties_pc ON " . $table_prefix . "items_properties (property_code) ";

		$sqls[] = "ALTER TABLE " . $table_prefix . "features_default ADD COLUMN feature_code VARCHAR(32) ";
		$sqls[] = "UPDATE " . $table_prefix . "features_default SET feature_code='' ";
		$sqls[] = "CREATE INDEX " . $table_prefix . "features_default_fc ON " . $table_prefix . "features_default (feature_code) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_on_table TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_on_table SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "features ADD COLUMN show_on_table BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "features SET show_on_table=0 ";

		$sqls[] = "ALTER TABLE " . $table_prefix . "features ADD COLUMN feature_code VARCHAR(32) ";
		$sqls[] = "UPDATE " . $table_prefix . "features SET feature_code='' ";
		$sqls[] = "CREATE INDEX " . $table_prefix . "features_feature_code ON " . $table_prefix . "features (feature_code) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN show_on_table TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN show_on_table SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "features_groups ADD COLUMN show_on_table BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "features_groups SET show_on_table=0 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN table_view TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN table_view SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN table_view BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "categories SET table_view=0 ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.2");
	}


	if (comp_vers("4.0.3", $current_db_version) == 1)
	{
		// add default columns for table view
		$sqls[] = "DELETE FROM " . $table_prefix . "categories_columns ";
		$sqls[] = "INSERT INTO " . $table_prefix . "categories_columns (column_id,category_id,column_order,column_code,column_title,column_html) VALUES (1, 0, 1, 'image|compare' , 'IMAGE_MSG' , NULL)";
		$sqls[] = "INSERT INTO " . $table_prefix . "categories_columns (column_id,category_id,column_order,column_code,column_title,column_html) VALUES (2, 0, 2, 'item_name|found_in_category|description' , 'PROD_NAME_MSG' , NULL)";
		$sqls[] = "INSERT INTO " . $table_prefix . "categories_columns (column_id,category_id,column_order,column_code,column_title,column_html) VALUES (3, 0, 3, 'options' , 'OPTIONS_MSG' , NULL)";
		$sqls[] = "INSERT INTO " . $table_prefix . "categories_columns (column_id,category_id,column_order,column_code,column_title,column_html) VALUES (4, 0, 4, 'price' , 'PRICE_MSG' , NULL)";
		$sqls[] = "INSERT INTO " . $table_prefix . "categories_columns (column_id,category_id,column_order,column_code,column_title,column_html) VALUES (5, 0, 5, 'sales_price' , 'OUR_PRICE_MSG' , NULL)";
		$sqls[] = "INSERT INTO " . $table_prefix . "categories_columns (column_id,category_id,column_order,column_code,column_title,column_html) VALUES (6, 0, 6, 'save' , 'YOU_SAVE_MSG' , NULL)";
		$sqls[] = "INSERT INTO " . $table_prefix . "categories_columns (column_id,category_id,column_order,column_code,column_title,column_html) VALUES (7, 0, 7, 'quantity' , 'QTY_MSG' , NULL)";
		$sqls[] = "INSERT INTO " . $table_prefix . "categories_columns (column_id,category_id,column_order,column_code,column_title,column_html) VALUES (8, 0, 8, 'add_button' , '&nbsp;' , NULL)";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.3");
	}

	if (comp_vers("4.0.4", $current_db_version) == 1)
	{
		$sqls[] = "ALTER TABLE " . $table_prefix . "cms_pages_blocks ADD COLUMN tag_name VARCHAR(128) ";
		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.4");
	}

	if (comp_vers("4.0.5", $current_db_version) == 1)
	{
		// add new contact us page
		$sql = " SELECT MAX(page_id) FROM ".$table_prefix."cms_pages "; 
		$page_id = get_db_value($sql) + 1;
		$sql = " SELECT MAX(block_id) FROM ".$table_prefix."cms_blocks "; 
		$block_id = get_db_value($sql) + 1;
		$sql = " SELECT MAX(ps_id) FROM ".$table_prefix."cms_pages_settings "; 
		$ps_id = get_db_value($sql) + 1;

		$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql(1, INTEGER).",";
		$sql.= $db->tosql(12, INTEGER).",";
		$sql.= $db->tosql("contact_us", TEXT).",";
		$sql.= $db->tosql("CONTACT_US_TITLE", TEXT).")";
		$sqls[] = $sql;

		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql(1, INTEGER).",";
		$sql.= $db->tosql(17, INTEGER).",";
		$sql.= $db->tosql("contact_us", TEXT).",";
		$sql.= $db->tosql("CONTACT_US_TITLE", TEXT).",";
		$sql.= $db->tosql("block_contact_us.php", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		// check header and footer blocks
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='header' "; 
		$header_block_id = get_db_value($sql);
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='footer' "; 
		$footer_block_id = get_db_value($sql);

		// added settings for new page 
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

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.5");
	}

	if (comp_vers("4.0.6", $current_db_version) == 1)
	{
		// adding new multi-add option for products listing block
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='products_list'"; 
		$block_id = get_db_value($sql);

		$sql = " SELECT MAX(property_id) FROM ".$table_prefix."cms_blocks_properties "; 
		$property_id = get_db_value($sql);

		$sql = " SELECT MAX(property_order) FROM ".$table_prefix."cms_blocks_properties WHERE block_id=" . $db->tosql($block_id, INTEGER); 
		$property_order = get_db_value($sql);

		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";

		$property_id++; $property_order++;
		$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,after_control_html) VALUES ($property_id, $block_id, $property_order, 'MULTI_ADD_MSG' , 'CHECKBOX' , NULL , NULL , 'multi_add' , NULL , 0 , 'MULTI_ADD_DESC')";
		// end of multi-add option

		// added keywords tables for products
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "items ADD COLUMN is_keywords TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "items ADD COLUMN is_keywords SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "items ADD COLUMN is_keywords BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "CREATE INDEX " . $table_prefix . "items_is_keywords ON " . $table_prefix . "items (is_keywords) ";
		$sqls[] = "UPDATE " . $table_prefix . "items SET is_keywords=0 ";

		$mysql_sql  = "CREATE TABLE ".$table_prefix."keywords (
      `keyword_id` INT(11) NOT NULL AUTO_INCREMENT,
      `keyword_name` VARCHAR(255)
      ,PRIMARY KEY (keyword_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."keywords START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."keywords (
      keyword_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."keywords'),
      keyword_name VARCHAR(255)
      ,PRIMARY KEY (keyword_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."keywords (
      [keyword_id]  COUNTER  NOT NULL,
      [keyword_name] VARCHAR(255)
      ,PRIMARY KEY (keyword_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		$mysql_sql  = "CREATE TABLE ".$table_prefix."keywords_items (
      `item_id` INT(11) default '0',
      `keyword_id` INT(11) default '0',
      `field_id` TINYINT default '0',
      `keyword_position` SMALLINT default '0',
      `keyword_rank` SMALLINT default '0'
      ,KEY field_id (field_id)
      ,KEY item_id (item_id)
      ,KEY keyword_id (keyword_id)
      ,KEY keyword_position (keyword_position)
      ,KEY keyword_rank (keyword_rank))";

		$postgre_sql  = "CREATE TABLE ".$table_prefix."keywords_items (
      item_id INT4 default '0',
      keyword_id INT4 default '0',
      field_id SMALLINT default '0',
      keyword_position SMALLINT default '0',
      keyword_rank SMALLINT default '0')";

		$access_sql  = "CREATE TABLE ".$table_prefix."keywords_items (
      [item_id] INTEGER,
      [keyword_id] INTEGER,
      [field_id] BYTE,
      [keyword_position] INTEGER,
      [keyword_rank] INTEGER)";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX ".$table_prefix."keywords_items_field_id ON ".$table_prefix."keywords_items (field_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."keywords_items_item_id ON ".$table_prefix."keywords_items (item_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."keywords_items_keyword_id ON ".$table_prefix."keywords_items (keyword_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."keywords_items_keyword_p_36 ON ".$table_prefix."keywords_items (keyword_position)";
			$sqls[] = "CREATE INDEX ".$table_prefix."keywords_items_keyword_rank ON ".$table_prefix."keywords_items (keyword_rank)";
		}

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN is_fast_checkout TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN is_fast_checkout SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN is_fast_checkout BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "orders SET is_fast_checkout=0 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN is_paid TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN is_paid SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN is_paid BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "orders SET is_paid=0 ";
		$sqls[] = "CREATE INDEX ".$table_prefix."orders_is_paid ON ".$table_prefix."orders (is_paid) ";

		$sql = " SELECT status_id FROM ".$table_prefix."order_statuses WHERE paid_status=1 ";
		$db->query($sql);
		$paid_statuses = array();
		while ($db->next_record()) {
			$paid_statuses[] = $db->f("status_id");
		}
		if (sizeof($paid_statuses)) {
			$sqls[] = "UPDATE " . $table_prefix . "orders SET is_paid=1 WHERE order_status IN (".$db->tosql($paid_statuses, INTEGERS_LIST).")";
		}

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN order_paid_date DATETIME ",
			"postgre" => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN order_paid_date TIMESTAMP ",
			"access"  => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN order_paid_date DATETIME ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "CREATE INDEX ".$table_prefix."orders_order_paid_date ON ".$table_prefix."orders (order_paid_date)";
		$sqls[] = "UPDATE " . $table_prefix . "orders SET order_paid_date=order_placed_date WHERE is_paid=1 ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.6");
	}

	if (comp_vers("4.0.7", $current_db_version) == 1)
	{
		$sql = " SELECT MAX(property_id) FROM ".$table_prefix."cms_blocks_properties "; 
		$property_id = get_db_value($sql);

		$sql = " SELECT MAX(value_id) FROM ".$table_prefix."cms_blocks_values "; 
		$value_id = get_db_value($sql);

		// adding new options for hot articles block
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='articles_hot'"; 
		$block_id = get_db_value($sql);

		$sql = " SELECT MAX(property_order) FROM ".$table_prefix."cms_blocks_properties WHERE block_id=" . $db->tosql($block_id, INTEGER); 
		$property_order = get_db_value($sql);

		if ($block_id) {
			$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
	  
			$property_id++; $property_order++;
			$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'SLIDER_TYPE_MSG' , 'LISTBOX' , NULL , NULL , 'slider_type' , NULL , 0)";
			$value_id++;
			$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $property_id, 1, 'NONE_MSG' , NULL , '0' , 0 , 0)";
			$value_id++;
			$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $property_id, 2, 'VERTICAL_SLIDER_MSG' , NULL , '1' , 0 , 0)";
			$value_id++;
			$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $property_id, 3, 'HORIZONTAL_SLIDER_MSG' , NULL , '2' , 0 , 0)";

			$property_id++; $property_order++;
			$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'SLIDER_WIDTH_MSG' , 'TEXTBOX' , NULL , NULL , 'slider_width' , '100%', 0)";

			$property_id++; $property_order++;
			$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'SLIDER_HEIGHT_MSG' , 'TEXTBOX' , NULL , NULL , 'slider_height' , '300px', 0)";
		}

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN use_filter TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN use_filter SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN use_filter BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "export_templates SET use_filter=0 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN save_file TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN save_file SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN save_file BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "export_templates SET save_file=0 ";

		$sqls[] = "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN file_path_mask VARCHAR(255) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN is_cronjob TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN is_cronjob SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN is_cronjob BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "export_templates SET is_cronjob=0 ";


		$mysql_sql  = "CREATE TABLE ".$table_prefix."export_filters (
      `filter_id` INT(11) NOT NULL AUTO_INCREMENT,
      `template_id` INT(11) default '0',
      `filter_parameter` VARCHAR(64),
      `filter_value` VARCHAR(255)
      ,PRIMARY KEY (filter_id)
      ,KEY template_id (template_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."export_filters START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."export_filters (
      filter_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."export_filters'),
      template_id INT4 default '0',
      filter_parameter VARCHAR(64),
      filter_value VARCHAR(255)
      ,PRIMARY KEY (filter_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."export_filters (
      [filter_id]  COUNTER  NOT NULL,
      [template_id] INTEGER,
      [filter_parameter] VARCHAR(64),
      [filter_value] VARCHAR(255)
      ,PRIMARY KEY (filter_id))";


		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX ".$table_prefix."export_filters_template_id ON ".$table_prefix."export_filters (template_id)";	
		}

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "tax_rates ADD COLUMN postal_code TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "tax_rates ADD COLUMN postal_code TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "tax_rates ADD COLUMN postal_code LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN property_step INT(11) default '1' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN property_step INT4 default '1' ",
			"access"  => "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN property_step INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "items_properties SET property_step=1 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "cms_blocks ADD COLUMN block_title TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "cms_blocks ADD COLUMN block_title TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "cms_blocks ADD COLUMN block_title LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "cms_pages_blocks ADD COLUMN block_title TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "cms_pages_blocks ADD COLUMN block_title TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "cms_pages_blocks ADD COLUMN block_title LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.7");
	}

	if (comp_vers("4.0.8", $current_db_version) == 1)
	{
		$sql = " SELECT MAX(block_id) FROM ".$table_prefix."cms_blocks "; 
		$block_id = get_db_value($sql) + 1;
		$sql = " SELECT MAX(module_id) FROM ".$table_prefix."cms_modules "; 
		$module_id = get_db_value($sql) + 1;
		
		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql(1, INTEGER).",";
		$sql.= $db->tosql("slider", TEXT).",";
		$sql.= $db->tosql("{slider_name}", TEXT).",";
		$sql.= $db->tosql("block_slider.php", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;
		
		$sql = " SELECT MAX(module_order) FROM ".$table_prefix."cms_modules "; 
		$module_order = get_db_value($sql) + 1;
		
		$sql = "INSERT INTO ".$table_prefix."cms_modules (module_id,module_order,module_code,module_name) VALUES (";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($module_order, INTEGER).",";
		$sql.= $db->tosql("sliders", TEXT).",";
		$sql.= $db->tosql("SLIDERS_MSG", TEXT).")";
		$sqls[] = $sql;
		
		$sql = " SELECT MAX(property_id) FROM ".$table_prefix."cms_blocks_properties "; 
		$property_id = get_db_value($sql);

		$sql = " SELECT MAX(value_id) FROM ".$table_prefix."cms_blocks_values "; 
		$value_id = get_db_value($sql);

		$sql = " SELECT MAX(property_order) FROM ".$table_prefix."cms_blocks_properties WHERE block_id=" . $db->tosql($block_id, INTEGER); 
		$property_order = get_db_value($sql);

		if ($block_id) {
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES ($property_id, $block_id, $property_order, 'SLIDER_TYPE_MSG', 'LISTBOX', NULL, NULL, 'slider_type', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)";
			
			$value_id++;
			$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $property_id, 2, '{VERTICAL_SLIDER_MSG} ({UP_MSG})', NULL, '1', 0, 0)";
			$value_id++;
			$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $property_id, 4, '{HORIZONTAL_SLIDER_MSG} ({LEFT_MSG})', NULL, '2', 0, 0)";
			$value_id++;
			$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $property_id, 3, '{VERTICAL_SLIDER_MSG} ({DOWN_MSG})', NULL, '3', 0, 0)";
			$value_id++;
			$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $property_id, 5, '{HORIZONTAL_SLIDER_MSG} ({RIGHT_MSG})', NULL, '4', 0, 0)";
			$value_id++;
			$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $property_id, 1, 'SLIDESHOW_MSG', NULL, '5', 0, 1)";
			
			$property_id++; $property_order++;
			$sqls[] = "INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES ($property_id, $block_id, $property_order, 'SLIDER_WIDTH_MSG', 'TEXTBOX', NULL, NULL, 'slider_width', '100%', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)";

			$property_id++; $property_order++;
			$sqls[] = "INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES ($property_id, $block_id, $property_order, 'SLIDER_HEIGHT_MSG', 'TEXTBOX', NULL, NULL, 'slider_height', '300', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)";

			$property_id++; $property_order++;
			$sqls[] = "INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required,property_style,control_style,start_html,middle_html,before_control_html,after_control_html,end_html,control_code,onchange_code,onclick_code) VALUES ($property_id, $block_id, $property_order, 'BLOCK_VIEW_TYPE_MSG', 'LISTBOX', NULL, NULL, 'block_view_type', '', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)";
			
			$value_id++;
			$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $property_id, 1, 'DEFAULT_VIEW_TYPE_MSG', '', '1', 0, 0)";
			$value_id++;
			$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $property_id, 2, 'CONTENT_AND_BORDERS_MSG', '', '2', 0, 0)";
			$value_id++;
			$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($value_id, $property_id, 3, 'CONTENT_ONLY_MSG', '', '3', 0, 1)";
		}

		// add unsubscribe block
		$sql = " SELECT module_id FROM ".$table_prefix."cms_modules WHERE module_code='global' "; 
		$global_module_id = get_db_value($sql);

		$sql = "INSERT INTO ".$table_prefix."cms_blocks (module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($global_module_id, INTEGER).",";
		$sql.= $db->tosql(3, INTEGER).",";
		$sql.= $db->tosql("unsubscribe", TEXT).",";
		$sql.= $db->tosql("UNSUBSCRIBE_TITLE", TEXT).",";
		$sql.= $db->tosql("block_unsubscribe.php", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		// slider
		$mysql_sql  = "CREATE TABLE " . $table_prefix . "sliders (
      `slider_id` INT(11) NOT NULL AUTO_INCREMENT,
      `slider_name` VARCHAR(64),
      `slider_title` VARCHAR(255),
      `slider_height` VARCHAR(12),
      `slider_width` VARCHAR(12)
      ,PRIMARY KEY (slider_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_" . $table_prefix . "sliders START 1";
		}
		$postgre_sql  = "CREATE TABLE " . $table_prefix . "sliders (
      slider_id INT4 NOT NULL DEFAULT nextval('seq_" . $table_prefix . "sliders'),
      slider_name VARCHAR(64),
      slider_title VARCHAR(255),
      slider_height VARCHAR(12),
      slider_width VARCHAR(12)
      ,PRIMARY KEY (slider_id))";


		$access_sql  = "CREATE TABLE " . $table_prefix . "sliders (
      [slider_id]  COUNTER  NOT NULL,
      [slider_name] VARCHAR(64),
      [slider_title] VARCHAR(255),
      [slider_height] VARCHAR(12),
      [slider_width] VARCHAR(12)
      ,PRIMARY KEY (slider_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		$mysql_sql  = "CREATE TABLE " . $table_prefix . "sliders_items (
      `item_id` INT(11) NOT NULL AUTO_INCREMENT,
      `slider_id` INT(11) NOT NULL default '0',
      `show_for_user` TINYINT default '1',
      `item_order` INT(11) default '0',
      `item_name` VARCHAR(255),
      `slider_image` VARCHAR(255),
      `slider_link` VARCHAR(255),
      `slider_html` TEXT
      ,PRIMARY KEY (item_id)
      ,KEY slider_id (slider_id))";


		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_" . $table_prefix . "sliders_items START 1";
		}
		$postgre_sql  = "CREATE TABLE " . $table_prefix . "sliders_items (
      item_id INT4 NOT NULL DEFAULT nextval('seq_" . $table_prefix . "sliders_items'),
      slider_id INT4 NOT NULL default '0',
      show_for_user SMALLINT default '1',
      item_order INT4 default '0',
      item_name VARCHAR(255),
      slider_image VARCHAR(255),
      slider_link VARCHAR(255),
      slider_html TEXT
      ,PRIMARY KEY (item_id))";

		$access_sql  = "CREATE TABLE " . $table_prefix . "sliders_items (
      [item_id]  COUNTER  NOT NULL,
      [slider_id] INTEGER,
      [show_for_user] BYTE,
      [item_order] INTEGER,
      [item_name] VARCHAR(255),
      [slider_image] VARCHAR(255),
      [slider_link] VARCHAR(255),
      [slider_html] LONGTEXT
      ,PRIMARY KEY (item_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX " . $table_prefix . "sliders_items_slider_id ON " . $table_prefix . "sliders_items (slider_id)";
		}

		// more export templates fields
		$sqls[] = "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN file_path_mask_copy VARCHAR(255) ";
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN order_status_update INT(11) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN order_status_update INT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN order_status_update INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.8");
	}

	if (comp_vers("4.0.9", $current_db_version) == 1)
	{
		//payment system upgrade
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN item_types_all INT(11) default '1' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN item_types_all INT4 default '1' ",
			"access"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN item_types_all INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "payment_systems SET item_types_all=1 ";
		
		$mysql_sql  = "CREATE TABLE " . $table_prefix . "payment_item_types (
      `payment_id` INT(11) NOT NULL,
      `item_type_id` INT(11) NOT NULL)";

		$postgre_sql  = "CREATE TABLE " . $table_prefix . "payment_item_types (
      payment_id INT4 NOT NULL,
      item_type_id INT4 NOT NULL)";

		$access_sql  = "CREATE TABLE " . $table_prefix . "payment_item_types (
      [payment_id] INTEGER NOT NULL,
      [item_type_id] INTEGER NOT NULL)";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];
		
		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.9");
	}


	if (comp_vers("4.0.10", $current_db_version) == 1)
	{
		$mysql_sql = "CREATE TABLE ".$table_prefix."orders_shipments (
      `order_shipping_id` INT(11) NOT NULL AUTO_INCREMENT,
      `order_id` INT(11) NOT NULL default '0',
      `shipping_id` INT(11) NOT NULL default '0',
      `shipping_code` VARCHAR(64),
      `shipping_desc` VARCHAR(255),
      `shipping_cost` DOUBLE(16,2) default '0',
      `points_cost` DOUBLE(16,4) default '0',
      `tax_free` TINYINT default '0',
      `tracking_id` VARCHAR(64),
      `expecting_date` DATETIME,
      `goods_weight` DOUBLE(16,4) default '0',
      `tare_weight` DOUBLE(16,4) default '0'
      ,KEY order_id (order_id)
      ,PRIMARY KEY (order_shipping_id)
      ,KEY shipping_type_id (shipping_id))";


		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."orders_shipments START 1";
		}
		$postgre_sql = "CREATE TABLE ".$table_prefix."orders_shipments (
      order_shipping_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."orders_shipments'),
      order_id INT4 NOT NULL default '0',
      shipping_id INT4 NOT NULL default '0',
      shipping_code VARCHAR(64),
      shipping_desc VARCHAR(255),
      shipping_cost FLOAT4 default '0',
      points_cost FLOAT4 default '0',
      tax_free SMALLINT default '0',
      tracking_id VARCHAR(64),
      expecting_date TIMESTAMP,
      goods_weight FLOAT4 default '0',
      tare_weight FLOAT4 default '0'
      ,PRIMARY KEY (order_shipping_id))";


		$access_sql = "CREATE TABLE ".$table_prefix."orders_shipments (
      [order_shipping_id]  COUNTER  NOT NULL,
      [order_id] INTEGER,
      [shipping_id] INTEGER,
      [shipping_code] VARCHAR(64),
      [shipping_desc] VARCHAR(255),
      [shipping_cost] FLOAT,
      [points_cost] FLOAT,
      [tax_free] BYTE,
      [tracking_id] VARCHAR(64),
      [expecting_date] DATETIME,
      [goods_weight] FLOAT,
      [tare_weight] FLOAT
      ,PRIMARY KEY (order_shipping_id))";

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX ".$table_prefix."orders_shipments_order_id ON ".$table_prefix."orders_shipments (order_id)";	
			$sqls[] = "CREATE INDEX ".$table_prefix."orders_shipments_shippin_62 ON ".$table_prefix."orders_shipments (shipping_id)";	
		}

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "items ADD COLUMN shipping_modules_default TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "items ADD COLUMN shipping_modules_default SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "items ADD COLUMN shipping_modules_default BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "items SET shipping_modules_default=1 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "items ADD COLUMN shipping_modules_ids TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "items ADD COLUMN shipping_modules_ids TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "items ADD COLUMN shipping_modules_ids LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "items ADD COLUMN is_separate_shipping TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "items ADD COLUMN is_separate_shipping SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "items ADD COLUMN is_separate_shipping BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "items ADD COLUMN is_shipping_required TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "items ADD COLUMN is_shipping_required SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "items ADD COLUMN is_shipping_required BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"  => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN shipping_excl_tax DOUBLE(16,2) default '0' ",
			"postgre"=> "ALTER TABLE " . $table_prefix . "orders ADD COLUMN shipping_excl_tax FLOAT4 default '0' ",
			"access" => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN shipping_excl_tax FLOAT "
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"  => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN shipping_tax DOUBLE(16,2) default '0' ",
			"postgre"=> "ALTER TABLE " . $table_prefix . "orders ADD COLUMN shipping_tax FLOAT4 default '0' ",
			"access" => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN shipping_tax FLOAT "
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"  => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN shipping_incl_tax DOUBLE(16,2) default '0' ",
			"postgre"=> "ALTER TABLE " . $table_prefix . "orders ADD COLUMN shipping_incl_tax FLOAT4 default '0' ",
			"access" => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN shipping_incl_tax FLOAT "
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"  => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN shipping_points_cost DOUBLE(16,4) default '0' ",
			"postgre"=> "ALTER TABLE " . $table_prefix . "orders ADD COLUMN shipping_points_cost FLOAT4 default '0' ",
			"access" => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN shipping_points_cost FLOAT "
		);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "UPDATE " . $table_prefix . "orders SET shipping_excl_tax=NULL, shipping_tax=NULL, shipping_incl_tax=NULL, shipping_points_cost=shipping_points_amount ";
		$sqls[] = "UPDATE " . $table_prefix . "orders SET shipping_points_amount=NULL ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "shipping_modules ADD COLUMN is_default TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "shipping_modules ADD COLUMN is_default SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "shipping_modules ADD COLUMN is_default BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "shipping_modules SET is_default=0 ";
		$sqls[] = "UPDATE " . $table_prefix . "shipping_modules SET is_default=is_active ";
		$sqls[] = "UPDATE " . $table_prefix . "shipping_modules SET is_active=1 WHERE is_call_center=1";

		$sqls[] = "ALTER TABLE " . $table_prefix . "shipping_modules ADD COLUMN user_module_name VARCHAR(255) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN non_logged_users TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN non_logged_users SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN non_logged_users BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "payment_systems SET non_logged_users=user_types_all";

		$mysql_sql = "CREATE TABLE ".$table_prefix."admins_settings (
      `admin_id` INT(11) NOT NULL default '0',
      `setting_name` VARCHAR(64) NOT NULL,
      `setting_value` TEXT
      ,PRIMARY KEY (admin_id,setting_name))";

		$postgre_sql = "CREATE TABLE ".$table_prefix."admins_settings (
      admin_id INT4 NOT NULL default '0',
      setting_name VARCHAR(64) NOT NULL,
      setting_value TEXT
      ,PRIMARY KEY (admin_id,setting_name))";

		$access_sql = "CREATE TABLE ".$table_prefix."admins_settings (
      [admin_id] INTEGER NOT NULL,
      [setting_name] VARCHAR(64) NOT NULL,
      [setting_value] LONGTEXT
      ,PRIMARY KEY (admin_id,setting_name))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.10");
	}


	if (comp_vers("4.0.11", $current_db_version) == 1)
	{
		// new admin access levels
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "admin_privileges ADD COLUMN user_types_all TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "admin_privileges ADD COLUMN user_types_all SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "admin_privileges ADD COLUMN user_types_all BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "admin_privileges SET user_types_all=1 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "admin_privileges ADD COLUMN non_logged_users TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "admin_privileges ADD COLUMN non_logged_users SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "admin_privileges ADD COLUMN non_logged_users BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "UPDATE " . $table_prefix . "admin_privileges SET non_logged_users=1 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "admin_privileges ADD COLUMN user_types_ids TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "admin_privileges ADD COLUMN user_types_ids TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "admin_privileges ADD COLUMN user_types_ids LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN view_order_groups_all TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN view_order_groups_all SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN view_order_groups_all BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN view_order_groups_ids TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN view_order_groups_ids TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN view_order_groups_ids LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN set_status_groups_all TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN set_status_groups_all SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN set_status_groups_all BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN set_status_groups_ids TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN set_status_groups_ids TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN set_status_groups_ids LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN update_order_groups_all TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN update_order_groups_all SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN update_order_groups_all BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN update_order_groups_ids TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN update_order_groups_ids TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN update_order_groups_ids LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN admin_to_groups_ids TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN admin_to_groups_ids TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN admin_to_groups_ids LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		// newsletters structure update
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters_users ADD COLUMN user_id INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters_users ADD COLUMN user_id INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters_users ADD COLUMN user_id INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "CREATE INDEX ".$table_prefix."newsletters_users_user_id ON ".$table_prefix."newsletters_users (user_id)";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters_users ADD COLUMN date_updated DATETIME ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters_users ADD COLUMN date_updated TIMESTAMP ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters_users ADD COLUMN date_updated DATETIME ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN user_id INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN user_id INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN user_id INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "CREATE INDEX ".$table_prefix."newsletters_emails_user_id ON ".$table_prefix."newsletters_emails (user_id)";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN is_opened TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN is_opened SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN is_opened BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN is_clicked TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN is_clicked SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN is_clicked BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN is_bounced TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN is_bounced SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN is_bounced BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN is_unsubscribed TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN is_unsubscribed SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN is_unsubscribed BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN is_ordered TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN is_ordered SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN is_ordered BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN emails_opened INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN emails_opened INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN emails_opened INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN emails_clicked INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN emails_clicked INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN emails_clicked INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN emails_bounced INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN emails_bounced INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN emails_bounced INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN emails_unsubscribed INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN emails_unsubscribed INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN emails_unsubscribed INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$mysql_sql = "CREATE TABLE ".$table_prefix."newsletters_campaigns (
      `campaign_id` INT(11) NOT NULL AUTO_INCREMENT,
      `site_id` INT(11) default '0',
      `is_active` TINYINT default '1',
      `campaign_name` VARCHAR(255),
      `campaign_date_start` DATETIME,
      `campaign_date_end` DATETIME,
      `emails_sent` INT(11) default '0',
      `emails_opened` INT(11) default '0',
      `emails_clicked` INT(11) default '0',
      `emails_bounced` INT(11) default '0',
      `emails_unsubscribed` INT(11) default '0',
      `admin_id_added_by` INT(11) default '0',
      `admin_id_modified_by` INT(11) default '0',
      `date_added` DATETIME,
      `date_modified` DATETIME
      ,PRIMARY KEY (campaign_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."newsletters_campaigns START 1";
		}
		$postgre_sql = "CREATE TABLE ".$table_prefix."newsletters_campaigns (
      campaign_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."newsletters_campaigns'),
      site_id INT4 default '0',
      is_active SMALLINT default '1',
      campaign_name VARCHAR(255),
      campaign_date_start TIMESTAMP,
      campaign_date_end TIMESTAMP,
      emails_sent INT4 default '0',
      emails_opened INT4 default '0',
      emails_clicked INT4 default '0',
      emails_bounced INT4 default '0',
      emails_unsubscribed INT4 default '0',
      admin_id_added_by INT4 default '0',
      admin_id_modified_by INT4 default '0',
      date_added TIMESTAMP,
      date_modified TIMESTAMP
      ,PRIMARY KEY (campaign_id))";

		$access_sql = "CREATE TABLE ".$table_prefix."newsletters_campaigns (
      [campaign_id]  COUNTER  NOT NULL,
      [site_id] INTEGER,
      [is_active] BYTE,
      [campaign_name] VARCHAR(255),
      [campaign_date_start] DATETIME,
      [campaign_date_end] DATETIME,
      [emails_sent] INTEGER,
      [emails_opened] INTEGER,
      [emails_clicked] INTEGER,
      [emails_bounced] INTEGER,
      [emails_unsubscribed] INTEGER,
      [admin_id_added_by] INTEGER,
      [admin_id_modified_by] INTEGER,
      [date_added] DATETIME,
      [date_modified] DATETIME
      ,PRIMARY KEY (campaign_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN campaign_id INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN campaign_id INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN campaign_id INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "INSERT INTO " . $table_prefix . "newsletters_campaigns (campaign_id, site_id, is_active, campaign_name) VALUES (1, 1, 1, 'General') ";
		$sqls[] = "UPDATE " . $table_prefix . "newsletters SET campaign_id=1 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN newsletter_id INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN newsletter_id INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN newsletter_id INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "CREATE INDEX " . $table_prefix . "orders_newsletter_id ON " . $table_prefix . "orders (newsletter_id) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN newsletter_email_id INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN newsletter_email_id INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN newsletter_email_id INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$mysql_sql = "CREATE TABLE ".$table_prefix."newsletters_groups (
      `group_id` INT(11) NOT NULL AUTO_INCREMENT,
      `group_name` VARCHAR(255),
      `group_desc` TEXT,
      `show_for_user` TINYINT default '1',
      `is_default` TINYINT default '1',
      `is_hidden` TINYINT default '0',
      `sites_all` TINYINT default '1'
      ,PRIMARY KEY (group_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."newsletters_groups START 1";
		}
		$postgre_sql = "CREATE TABLE ".$table_prefix."newsletters_groups (
      group_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."newsletters_groups'),
      group_name VARCHAR(255),
      group_desc TEXT,
      show_for_user SMALLINT default '1',
      is_default SMALLINT default '1',
      is_hidden SMALLINT default '0',
      sites_all SMALLINT default '1'
      ,PRIMARY KEY (group_id))";

		$access_sql = "CREATE TABLE ".$table_prefix."newsletters_groups (
      [group_id]  COUNTER  NOT NULL,
      [group_name] VARCHAR(255),
      [group_desc] LONGTEXT,
      [show_for_user] BYTE,
      [is_default] BYTE,
      [is_hidden] BYTE,
      [sites_all] BYTE
      ,PRIMARY KEY (group_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		$mysql_sql = "CREATE TABLE ".$table_prefix."newsletters_groups_sites (
      `group_id` INT(11) NOT NULL default '0',
      `site_id` INT(11) NOT NULL default '0'
      ,PRIMARY KEY (group_id,site_id))";

		$postgre_sql = "CREATE TABLE ".$table_prefix."newsletters_groups_sites (
      group_id INT4 NOT NULL default '0',
      site_id INT4 NOT NULL default '0'
      ,PRIMARY KEY (group_id,site_id))";

		$access_sql = "CREATE TABLE ".$table_prefix."newsletters_groups_sites (
      [group_id] INTEGER NOT NULL,
      [site_id] INTEGER NOT NULL
      ,PRIMARY KEY (group_id,site_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		$mysql_sql = "CREATE TABLE ".$table_prefix."newsletters_users_groups (
      `email_id` INT(11) NOT NULL default '0',
      `group_id` INT(11) NOT NULL default '0'
      ,PRIMARY KEY (email_id,group_id))";

		$postgre_sql = "CREATE TABLE ".$table_prefix."newsletters_users_groups (
      email_id INT4 NOT NULL default '0',
      group_id INT4 NOT NULL default '0'
      ,PRIMARY KEY (email_id,group_id))";

		$access_sql = "CREATE TABLE ".$table_prefix."newsletters_users_groups (
      [email_id] INTEGER NOT NULL,
      [group_id] INTEGER NOT NULL
      ,PRIMARY KEY (email_id,group_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		// newsletter filters
		$mysql_sql  = "CREATE TABLE ".$table_prefix."newsletter_filters (
      `filter_id` INT(11) NOT NULL AUTO_INCREMENT,
      `newsletter_id` INT(11) default '0',
      `filter_parameter` VARCHAR(64),
      `filter_value` VARCHAR(255)
      ,PRIMARY KEY (filter_id)
      ,KEY newsletter_id (newsletter_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."newsletter_filters START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."newsletter_filters (
      filter_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."newsletter_filters'),
      newsletter_id INT4 default '0',
      filter_parameter VARCHAR(64),
      filter_value VARCHAR(255)
      ,PRIMARY KEY (filter_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."newsletter_filters (
      [filter_id]  COUNTER  NOT NULL,
      [newsletter_id] INTEGER,
      [filter_parameter] VARCHAR(64),
      [filter_value] VARCHAR(255)
      ,PRIMARY KEY (filter_id))";


		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX ".$table_prefix."newsletter_filters_newsletter_id ON ".$table_prefix."newsletter_filters (newsletter_id)";	
		}
		// end newsletter filters

		//new languages
		$sqls[] = "INSERT INTO " . $table_prefix . "languages (language_code, language_order, language_name, show_for_user, language_image, language_image_active, currency_code) VALUES ('bg', 1, 'Bulgarian', 0, 'images/flags/ba.gif', NULL, NULL)";
		$sqls[] = "INSERT INTO " . $table_prefix . "languages (language_code, language_order, language_name, show_for_user, language_image, language_image_active, currency_code) VALUES ('da', 1, 'Danish', 0, 'images/flags/da.gif', NULL, NULL)";
		$sqls[] = "INSERT INTO " . $table_prefix . "languages (language_code, language_order, language_name, show_for_user, language_image, language_image_active, currency_code) VALUES ('et', 1, 'Estonian', 0, 'images/flags/et.gif', NULL, NULL)";
		$sqls[] = "INSERT INTO " . $table_prefix . "languages (language_code, language_order, language_name, show_for_user, language_image, language_image_active, currency_code) VALUES ('he', 1, 'Hebrew', 0, 'images/flags/he.gif', NULL, NULL)";
		$sqls[] = "INSERT INTO " . $table_prefix . "languages (language_code, language_order, language_name, show_for_user, language_image, language_image_active, currency_code) VALUES ('hk', 1, 'Cantonese', 0, 'images/flags/hk.gif', NULL, NULL)";
		$sqls[] = "INSERT INTO " . $table_prefix . "languages (language_code, language_order, language_name, show_for_user, language_image, language_image_active, currency_code) VALUES ('hr', 1, 'Croatian', 0, 'images/flags/hr.gif', NULL, NULL)";
		$sqls[] = "INSERT INTO " . $table_prefix . "languages (language_code, language_order, language_name, show_for_user, language_image, language_image_active, currency_code) VALUES ('is', 1, 'Icelandic', 0, 'images/flags/is.gif', NULL, NULL)";
		$sqls[] = "INSERT INTO " . $table_prefix . "languages (language_code, language_order, language_name, show_for_user, language_image, language_image_active, currency_code) VALUES ('ja', 1, 'Japanese', 0, 'images/flags/jp.gif', NULL, NULL)";
		$sqls[] = "INSERT INTO " . $table_prefix . "languages (language_code, language_order, language_name, show_for_user, language_image, language_image_active, currency_code) VALUES ('mk', 1, 'Macedonian', 0, 'images/flags/mk.gif', NULL, NULL)";
		$sqls[] = "INSERT INTO " . $table_prefix . "languages (language_code, language_order, language_name, show_for_user, language_image, language_image_active, currency_code) VALUES ('ro', 1, 'Romanian', 0, 'images/flags/ro.gif', NULL, NULL)";
		$sqls[] = "INSERT INTO " . $table_prefix . "languages (language_code, language_order, language_name, show_for_user, language_image, language_image_active, currency_code) VALUES ('vi', 1, 'Vietnamese', 0, 'images/flags/vi.gif', NULL, NULL)";
		//new languages end

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.11");
	}


	if (comp_vers("4.0.12", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "orders_coupons ADD COLUMN order_item_id INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "orders_coupons ADD COLUMN order_item_id INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "orders_coupons ADD COLUMN order_item_id INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "CREATE INDEX " . $table_prefix . "orders_coupons_item_id ON " . $table_prefix . "orders_coupons (order_item_id) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_newsletter_id TINYINT ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_newsletter_id SMALLINT",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_newsletter_id BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "CREATE INDEX ".$table_prefix."newsletters_template_id ON ".$table_prefix."newsletters (template_newsletter_id)";	

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN newsletter_type TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN newsletter_type SMALLINT DEFAULT '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN newsletter_type BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "CREATE INDEX ".$table_prefix."newsletters_newsletter_type ON ".$table_prefix."newsletters (newsletter_type)";	
		$sqls[] = "UPDATE " . $table_prefix . "newsletters SET newsletter_type=1 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_active TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_active SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_active BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "CREATE INDEX ".$table_prefix."newsletters_template_active ON ".$table_prefix."newsletters (template_active)";	
		$sqls[] = "UPDATE " . $table_prefix . "newsletters SET template_active=0 ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_period TINYINT ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_period SMALLINT ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_period BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_interval INT(11) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_interval INT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_interval INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_newsletters_limit INT(11) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_newsletters_limit INT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_newsletters_limit INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_newsletters_added INT(11) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_newsletters_added INT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_newsletters_added INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_start_date DATETIME ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_start_date TIMESTAMP ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_start_date DATETIME ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_end_date DATETIME ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_end_date TIMESTAMP ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_end_date DATETIME ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_next_date DATETIME ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_next_date TIMESTAMP ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_next_date DATETIME ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_last_date DATETIME ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_last_date TIMESTAMP ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_last_date DATETIME ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_filter_period TINYINT ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_filter_period SMALLINT ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_filter_period BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_filter_interval INT(11) ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_filter_interval INT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN template_filter_interval INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN email_type TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN email_type SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN email_type BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN order_id INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN order_id INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN order_id INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "CREATE INDEX " . $table_prefix . "newsletters_emails_order_id ON " . $table_prefix . "newsletters_emails (order_id) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN admin_id INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN admin_id INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters_emails ADD COLUMN admin_id INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "CREATE INDEX " . $table_prefix . "newsletters_emails_admin_id ON " . $table_prefix . "newsletters_emails (admin_id) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN emails_total INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN emails_total INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN emails_total INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN is_unsubscribed TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN is_unsubscribed SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN is_unsubscribed BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "users ADD COLUMN is_unsubscribed TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "users ADD COLUMN is_unsubscribed SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "users ADD COLUMN is_unsubscribed BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN custom_recipients TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN custom_recipients TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN custom_recipients LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN mail_cc VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "newsletters ADD COLUMN mail_bcc VARCHAR(255) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.12");
	}


	if (comp_vers("4.0.14", $current_db_version) == 1)
	{ 
		// check and add a new social media module
		$sql = " SELECT module_id FROM ".$table_prefix."cms_modules WHERE module_code='social_media' "; 
		$module_id = get_db_value($sql);
		if (!$module_id) {
			$sql = " SELECT MAX(module_id) FROM ".$table_prefix."cms_modules "; 
			$module_id = get_db_value($sql) + 1;
			$sql = " SELECT MAX(module_order) FROM ".$table_prefix."cms_modules "; 
			$module_order = get_db_value($sql) + 1;
		
			$sql = "INSERT INTO ".$table_prefix."cms_modules (module_id,module_order,module_code,module_name) VALUES (";
			$sql.= $db->tosql($module_id, INTEGER).",";
			$sql.= $db->tosql($module_order, INTEGER).",";
			$sql.= $db->tosql("social_media", TEXT).",";
			$sql.= $db->tosql("SOCIAL_MEDIA_MSG", TEXT).")";
			$sqls[] = $sql;
		}

		$sql = " SELECT MAX(block_id) FROM ".$table_prefix."cms_blocks "; 
		$block_id = get_db_value($sql);
		$sql = " SELECT MAX(block_order) FROM ".$table_prefix."cms_blocks WHERE module_id=" . $db->tosql($module_id, INTEGER); 
		$block_order = get_db_value($sql);

		$sql = " SELECT MAX(property_id) FROM ".$table_prefix."cms_blocks_properties "; 
		$property_id = get_db_value($sql);
		$sql = " SELECT MAX(property_order) FROM ".$table_prefix."cms_blocks_properties WHERE block_id=" . $db->tosql($block_id, INTEGER); 
		$property_order = get_db_value($sql);

		// check and add facebook feed block
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='facebook_feed' "; 
		$ff_block_id = get_db_value($sql);
		if (!$ff_block_id) {
			$block_id++; $block_order++;

			$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
			$sql.= $db->tosql($block_id, INTEGER).",";
			$sql.= $db->tosql($module_id, INTEGER).",";
			$sql.= $db->tosql($block_order, INTEGER).",";
			$sql.= $db->tosql("facebook_feed", TEXT).",";
			$sql.= $db->tosql("Facebook Feed", TEXT).",";
			$sql.= $db->tosql("block_facebook_feed.php", TEXT).",";
			$sql.= $db->tosql(1, INTEGER).")";
			$sqls[] = $sql;

			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'RECORDS_PER_PAGE_MSG', 'TEXTBOX', NULL, NULL, 'recs', '5', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'USERNAME_FIELD', 'TEXTBOX', NULL, NULL, 'username', NULL, 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'ACCESS_TOKEN_MSG', 'TEXTBOX', NULL, NULL, 'access_token', NULL, 0)";
		}
		
		// check and add twitter feed block
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='twitter_feed' "; 
		$tf_block_id = get_db_value($sql);
		if (!$tf_block_id) {
			$block_id++; $block_order++;

			$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
			$sql.= $db->tosql($block_id, INTEGER).",";
			$sql.= $db->tosql($module_id, INTEGER).",";
			$sql.= $db->tosql($block_order, INTEGER).",";
			$sql.= $db->tosql("twitter_feed", TEXT).",";
			$sql.= $db->tosql("Twitter Feed", TEXT).",";
			$sql.= $db->tosql("block_twitter_feed.php", TEXT).",";
			$sql.= $db->tosql(1, INTEGER).")";
			$sqls[] = $sql;

			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'RECORDS_PER_PAGE_MSG', 'TEXTBOX', NULL, NULL, 'recs', '5', 0)";
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'USERNAME_FIELD', 'TEXTBOX', NULL, NULL, 'username', NULL, 0)";
		}
		
		$mysql_sql = "CREATE TABLE ".$table_prefix."cms_blocks_periods (
      `period_id` INT(11) NOT NULL AUTO_INCREMENT,
      `ps_id` INT(11) default '0',
      `pb_id` INT(11) default '0',
      `start_date` DATETIME,
      `end_date` DATETIME,
      `start_time` INT(11) default '0',
      `end_time` INT(11) default '0',
      `week_days` INT(11) default '0'
      ,PRIMARY KEY (period_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."cms_blocks_periods START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."cms_blocks_periods (
      period_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."cms_blocks_periods'),
      ps_id INT4 default '0',
      pb_id INT4 default '0',
      start_date TIMESTAMP,
      end_date TIMESTAMP,
      start_time INT4 default '0',
      end_time INT4 default '0',
      week_days INT4 default '0'
      ,PRIMARY KEY (period_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."cms_blocks_periods (
      [period_id]  COUNTER  NOT NULL,
      [ps_id] INTEGER,
      [pb_id] INTEGER,
      [start_date] DATETIME,
      [end_date] DATETIME,
      [start_time] INTEGER,
      [end_time] INTEGER,
      [week_days] INTEGER
      ,PRIMARY KEY (period_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.14");
	}

	if (comp_vers("4.0.15", $current_db_version) == 1)
	{
	
		//reorder settings
		$sql = " SELECT MAX(property_id) FROM " . $table_prefix . "cms_blocks_properties "; 
		$property_id = get_db_value($sql);

		$sql = " SELECT MAX(property_order) FROM " . $table_prefix . "cms_blocks_properties WHERE block_id=" . $db->tosql($block_id, INTEGER); 
		$property_order = get_db_value($sql);

		$sql = " SELECT block_id FROM " . $table_prefix . "cms_blocks WHERE block_code='user_orders'"; 
		$block_id = get_db_value($sql);

		if ($block_id) {
			$property_id++; $property_order++;
			$sqls[] = "INSERT INTO " . $table_prefix . "cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'SHOW_ORDER_RESTORE_MSG' , 'CHECKBOX' , NULL , NULL , 'show_restore' , 1 , 0)";
		}
		
		$sql = " SELECT block_id FROM " . $table_prefix . "cms_blocks WHERE block_code='user_order'"; 
		$block_id = get_db_value($sql);
		
		if ($block_id) {
			$property_id++; $property_order++;
			$sqls[] = "INSERT INTO " . $table_prefix . "cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'SHOW_ORDER_RESTORE_MSG' , 'CHECKBOX' , NULL , NULL , 'show_restore' , 1 , 0)";
		}
		
		//countries gateway settings
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN countries_all TINYINT DEFAULT '1'",
			"postgre" => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN countries_all SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN countries_all BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$mysql_sql = "CREATE TABLE " . $table_prefix . "payment_countries (
      `payment_id` INT(11),
      `country_id` INT(11)
		)";
	
		$postgre_sql  = "CREATE TABLE " . $table_prefix . "payment_countries (
      payment_id INT4,
      country_id INT4
		)";

		$access_sql  = "CREATE TABLE " . $table_prefix . "payment_countries (
      [payment_id] INTEGER,
      [country_id] INTEGER
		)";
		
		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];	
		
		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.15");
	}

	if (comp_vers("4.0.16", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "sites ADD COLUMN parent_site_id TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "sites ADD COLUMN parent_site_id SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "sites ADD COLUMN parent_site_id BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "sites ADD COLUMN is_mobile INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "sites ADD COLUMN is_mobile INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "sites ADD COLUMN is_mobile INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.16");
	}


	if (comp_vers("4.0.17", $current_db_version) == 1)
	{
		// prepare variables to add new user addresses pages
		$sql = " SELECT module_id FROM ".$table_prefix."cms_modules WHERE module_code='user_account' "; 
		$module_id = get_db_value($sql);
		$sql = " SELECT MAX(page_id) FROM ".$table_prefix."cms_pages "; 
		$page_id = get_db_value($sql);
		$sql = " SELECT MAX(page_order) FROM ".$table_prefix."cms_pages "; 
		$page_order = get_db_value($sql);
		$sql = " SELECT MAX(block_id) FROM ".$table_prefix."cms_blocks "; 
		$block_id = get_db_value($sql);
		$sql = " SELECT MAX(block_order) FROM ".$table_prefix."cms_blocks WHERE module_id=" . $db->tosql($module_id, INTEGER); 
		$block_order = get_db_value($sql);
		$sql = " SELECT MAX(ps_id) FROM ".$table_prefix."cms_pages_settings "; 
		$ps_id = get_db_value($sql);

		// check header and footer blocks
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='header' "; 
		$header_block_id = get_db_value($sql);
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='footer' "; 
		$footer_block_id = get_db_value($sql);

		// add addresses list page
		$page_id++; $page_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($page_order, INTEGER).",";
		$sql.= $db->tosql("user_addresses", TEXT).",";
		$sql.= $db->tosql("{MY_ADDRESSES_MSG}: {LIST_MSG}", TEXT).")";
		$sqls[] = $sql;

		// add addresses list block 
		$block_id++; $block_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($block_order, INTEGER).",";
		$sql.= $db->tosql("user_addresses", TEXT).",";
		$sql.= $db->tosql("{MY_ADDRESSES_MSG}: {LIST_MSG}", TEXT).",";
		$sql.= $db->tosql("block_user_addresses.php", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		// added settings for new addresses page to three column layout by default
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

		// add addresses edit page
		$page_id++; $page_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($page_order, INTEGER).",";
		$sql.= $db->tosql("user_address", TEXT).",";
		$sql.= $db->tosql("{MY_ADDRESSES_MSG}: {EDIT_MSG}", TEXT).")";
		$sqls[] = $sql;

		// add addresses edit block 
		$block_id++; $block_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($block_order, INTEGER).",";
		$sql.= $db->tosql("user_addresses", TEXT).",";
		$sql.= $db->tosql("{MY_ADDRESSES_MSG}: {EDIT_MSG}", TEXT).",";
		$sql.= $db->tosql("block_user_address.php", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		// added settings for new addresses page to three column layout by default
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

		$mysql_sql = "CREATE TABLE ".$table_prefix."users_addresses (
      `address_id` INT(11) NOT NULL AUTO_INCREMENT,
      `user_id` INT(11) default '0',
      `address_type` TINYINT default '0',
      `name` VARCHAR(128),
      `first_name` VARCHAR(64),
      `last_name` VARCHAR(64),
      `company_id` INT(11) default '0',
      `company_name` VARCHAR(128),
      `email` VARCHAR(128),
      `address1` VARCHAR(255),
      `address2` VARCHAR(255),
      `city` VARCHAR(128),
      `province` VARCHAR(128),
      `state_id` INT(11) default '0',
      `state_code` VARCHAR(8),
      `postal_code` VARCHAR(16),
      `country_id` INT(11) default '0',
      `country_code` VARCHAR(8),
      `phone` VARCHAR(32),
      `daytime_phone` VARCHAR(32),
      `evening_phone` VARCHAR(32),
      `cell_phone` VARCHAR(32),
      `fax` VARCHAR(32)
      ,PRIMARY KEY (address_id)
      ,KEY user_id (user_id))";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."users_addresses START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."users_addresses (
      address_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."users_addresses'),
      user_id INT4 default '0',
      address_type SMALLINT default '0',
      name VARCHAR(128),
      first_name VARCHAR(64),
      last_name VARCHAR(64),
      company_id INT4 default '0',
      company_name VARCHAR(128),
      email VARCHAR(128),
      address1 VARCHAR(255),
      address2 VARCHAR(255),
      city VARCHAR(128),
      province VARCHAR(128),
      state_id INT4 default '0',
      state_code VARCHAR(8),
      postal_code VARCHAR(16),
      country_id INT4 default '0',
      country_code VARCHAR(8),
      phone VARCHAR(32),
      daytime_phone VARCHAR(32),
      evening_phone VARCHAR(32),
      cell_phone VARCHAR(32),
      fax VARCHAR(32)
      ,PRIMARY KEY (address_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."users_addresses (
      [address_id]  COUNTER  NOT NULL,
      [user_id] INTEGER,
      [address_type] BYTE,
      [name] VARCHAR(128),
      [first_name] VARCHAR(64),
      [last_name] VARCHAR(64),
      [company_id] INTEGER,
      [company_name] VARCHAR(128),
      [email] VARCHAR(128),
      [address1] VARCHAR(255),
      [address2] VARCHAR(255),
      [city] VARCHAR(128),
      [province] VARCHAR(128),
      [state_id] INTEGER,
      [state_code] VARCHAR(8),
      [postal_code] VARCHAR(16),
      [country_id] INTEGER,
      [country_code] VARCHAR(8),
      [phone] VARCHAR(32),
      [daytime_phone] VARCHAR(32),
      [evening_phone] VARCHAR(32),
      [cell_phone] VARCHAR(32),
      [fax] VARCHAR(32)
      ,PRIMARY KEY (address_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX ".$table_prefix."users_addresses_user_id ON ".$table_prefix."users_addresses (user_id)";
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.17");
	}


	if (comp_vers("4.0.18", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN alias_category_id INT(11)  ",
			"postgre" => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN alias_category_id INT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN alias_category_id INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$mysql_sql = "CREATE TABLE ".$table_prefix."file_transfers (
        `transfer_id` INT(11) NOT NULL AUTO_INCREMENT,
        `transfer_type` TINYINT default '0',
        `transfer_status` VARCHAR(16),
        `transfer_date` DATETIME,
        `ftp_host` VARCHAR(255),
        `ftp_port` VARCHAR(16),
        `ftp_login` VARCHAR(128),
        `ftp_password` VARCHAR(255),
			  `ftp_passive_mode` TINYINT default '0',
        `ftp_transfer_mode` VARCHAR(16),
        `ftp_path` VARCHAR(255),
        `file_path` VARCHAR(255),
        `date_added` DATETIME,
        `date_transferred` DATETIME,
        `date_failed` DATETIME,
        `failed_attempts` INT(11) default '0',
        `failed_errors` TEXT
        ,PRIMARY KEY (transfer_id))";


		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."file_transfers START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."file_transfers (
      transfer_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."file_transfers'),
      transfer_type SMALLINT default '0',
      transfer_status VARCHAR(16),
      transfer_date TIMESTAMP,
      ftp_host VARCHAR(255),
      ftp_port VARCHAR(16),
      ftp_login VARCHAR(128),
      ftp_password VARCHAR(255),
		  ftp_passive_mode SMALLINT default '0',
      ftp_transfer_mode VARCHAR(16),
      ftp_path VARCHAR(255),
      file_path VARCHAR(255),
      date_added TIMESTAMP,
      date_transferred TIMESTAMP,
      date_failed TIMESTAMP,
      failed_attempts INT4 default '0',
      failed_errors TEXT
      ,PRIMARY KEY (transfer_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."file_transfers (
      [transfer_id]  COUNTER  NOT NULL,
      [transfer_type] BYTE,
      [transfer_status] VARCHAR(16),
      [transfer_date] DATETIME,
      [ftp_host] VARCHAR(255),
      [ftp_port] VARCHAR(16),
      [ftp_login] VARCHAR(128),
      [ftp_password] VARCHAR(255),
		  [ftp_passive_mode] BYTE,
      [ftp_transfer_mode] VARCHAR(16),
      [ftp_path] VARCHAR(255),
      [file_path] VARCHAR(255),
      [date_added] DATETIME,
      [date_transferred] DATETIME,
      [date_failed] DATETIME,
      [failed_attempts] INTEGER,
      [failed_errors] LONGTEXT
      ,PRIMARY KEY (transfer_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		// export templates 
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN ftp_upload TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN ftp_upload SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN ftp_upload BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN ftp_host VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN ftp_port VARCHAR(16) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN ftp_login VARCHAR(128) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN ftp_password VARCHAR(255) ";
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN ftp_passive_mode TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN ftp_passive_mode SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN ftp_passive_mode BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN ftp_transfer_mode VARCHAR(16) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "export_templates ADD COLUMN ftp_path VARCHAR(255) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.18");
	}


	if (comp_vers("4.0.20", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN items_categories_ids TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN items_categories_ids TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN items_categories_ids LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.20");
	}


	if (comp_vers("4.0.21", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "orders_coupons ADD COLUMN discount_type TINYINT default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "orders_coupons ADD COLUMN discount_type SMALLINT default '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "orders_coupons ADD COLUMN discount_type BYTE",
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.21");
	}


	if (comp_vers("4.0.22", $current_db_version) == 1)
	{
		// add unsubscribe block
		$sql = " SELECT module_id FROM ".$table_prefix."cms_modules WHERE module_code='global' "; 
		$global_module_id = get_db_value($sql);

		$sql = "INSERT INTO ".$table_prefix."cms_blocks (module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($global_module_id, INTEGER).",";
		$sql.= $db->tosql(1, INTEGER).",";
		$sql.= $db->tosql("site_navigation", TEXT).",";
		$sql.= $db->tosql("SITE_NAVIGATION_MSG", TEXT).",";
		$sql.= $db->tosql("block_site_navigation.php", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		$sqls[] = "UPDATE " . $table_prefix . "cms_blocks SET block_order=1 ";

		//new languages
		$sqls[] = "INSERT INTO " . $table_prefix . "languages (language_code, language_order, language_name, show_for_user, language_image, language_image_active, currency_code) VALUES ('fa', 1, 'Persian', 0, 'images/flags/ir.gif', NULL, NULL)";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.22");
	}
	
	if (comp_vers("4.0.23", $current_db_version) == 1)
	{
		// add cookies control block
		$sql = " SELECT module_id FROM ".$table_prefix."cms_modules WHERE module_code='global' "; 
		$global_module_id = get_db_value($sql);

		$sql = "INSERT INTO ".$table_prefix."cms_blocks (module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($global_module_id, INTEGER).",";
		$sql.= $db->tosql(1, INTEGER).",";
		$sql.= $db->tosql("cookies_control", TEXT).",";
		$sql.= $db->tosql("COOKIE_CONTROL_MSG", TEXT).",";
		$sql.= $db->tosql("block_cookies_control.php", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		$sqls[] = "UPDATE " . $table_prefix . "cms_blocks SET block_order=1 ";

		//new custom pages with cookies info
		$sql = "INSERT INTO ".$table_prefix."pages (page_code,page_title,friendly_url,page_order,is_site_map,sites_all,user_types_all,is_showing,link_in_footer,is_html,page_type,page_body) VALUES (";
		$sql.= $db->tosql("use_of_cookies", TEXT).",";
		$sql.= $db->tosql("Use of cookies", TEXT).",";
		$sql.= $db->tosql("use_of_cookies", TEXT).",";
		$sql.= $db->tosql(16, INTEGER).",";
		$sql.= $db->tosql(0, INTEGER).",";
		$sql.= $db->tosql(1, INTEGER).",";
		$sql.= $db->tosql(1, INTEGER).",";
		$sql.= $db->tosql(1, INTEGER).",";
		$sql.= $db->tosql(0, INTEGER).",";
		$sql.= $db->tosql(1, INTEGER).",";
		$sql.= $db->tosql(1, INTEGER).",";
		$sql.= $db->tosql("<p>Cookie is usually a small piece of data sent from a website and stored in a web browser. Cookies were designed to be a reliable mechanism for websites to remember the state of the website or activity the user had taken in the past.</p><p>Any cookies that may be used by this website are used either solely on a per session basis (for example, to add product in the cart, track site visits) or to maintain user preferences (for example save login data, language preference etc.). Please read below a more detailed information about the cookies we use.</p><table border=\"1\" cellpadding=\"1\" cellspacing=\"1\"><thead><tr><th scope=\"col\">Cookies Name</th><th scope=\"col\">Description</th></tr></thead><tbody><tr><td>PHPSESSID</td><td>This is a cookie designed to store and identify your unique session ID on the website. It does not contain any personal information and usually looks something like 1234567890abcdef. PHPSESSID is deleted as soon as you close all browser windows. This cookie is essential to the store because it allows to login, add products to cart and make purchases. It would be impossible to implement a convenient shopping mechanism without cookies or something like them <b>that&#39;s why PHPSESSID is always turned on</b>.</td></tr><tr><td>cookie_lang</td><td>This cookie saves the language selection on the site so the next time you visit it will be automatically selected. The cookie expires in a year.</td></tr><tr><td>cookie_visit</td><td>This is a cookie that records your IP address during the first visit and the number of the visit. It is used for various analytical studies and for anti-fraud purposes.</td></tr><tr><td>cookie_user_login<br />cookie_user_password</td><td>These two cookies store login and password information for a year if you choose an option &quot;remember me&quot; in the login form.</td></tr><tr><td>cookie_af</td><td>This cookie is used to save the data about affiliate visits and calculate appropriate commissions. Usually this cookie expires in 90 days.</td></tr><tr><td>cookie_friend</td><td>This is a cookie used in coupons&#39; Friendly Visits functionality. It saves the data of a friendly visit and calculates possible bonuses.</td></tr><tr><td>__utma<br />__utmb<br />__utmc<br />__utm</td><td>Our website uses Google Analytics cookies to monitor traffic sources and make different reports. These cookies enable Google to determine your IP address (which is stored anonymously on their servers), whether you are a returning visitor to the site and to track the pages that you visit during your session. See <a href=\"https://developers.google.com/analytics/resources/concepts/gaConceptsCookies\" target=\"_blank\">Google Analytics documentation</a> to learn more details. These cookies will be disabled if you turn off cookies.</td></tr></tbody></table><p>&nbsp;</p>", TEXT).")";

		$sqls[] = $sql;
		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.23");
	}


	if (comp_vers("4.0.24", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN show_for_user TINYINT default '1' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN show_for_user SMALLINT default '1'",
			"access"  => "ALTER TABLE " . $table_prefix . "items_properties ADD COLUMN show_for_user BYTE",
		);
		$sqls[] = $sql_types[$db_type];

		$sql  = " UPDATE " . $table_prefix . "items_properties SET show_for_user=1 ";
		$sql .= " WHERE use_on_list=1 OR use_on_details=1 OR use_on_table=1 OR use_on_grid=1 OR use_on_second=1 ";
		$sqls[] = $sql;

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.24");
	}


	if (comp_vers("4.0.25", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "sites ADD COLUMN is_mobile_redirect INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "sites ADD COLUMN is_mobile_redirect INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "sites ADD COLUMN is_mobile_redirect INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.25");
	}

	if (comp_vers("4.0.26", $current_db_version) == 1)
	{
		// add new ajax tree-type for categories list
		$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties cbp ";
		$sql .= " INNER JOIN ".$table_prefix."cms_blocks cb ON cb.block_id=cbp.block_id ";
		$sql .= " WHERE cb.block_code='categories_list' ";
		$sql .= " AND cbp.variable_name='categories_type'  ";
		$db->query($sql);
		if ($db->next_record()) {
			$property_id = $db->f("property_id");
			$sqls[] = "INSERT INTO ".$table_prefix."cms_blocks_values (property_id,value_order,value_name,variable_name,variable_value,hide_value,is_default_value) VALUES ($property_id, 6, 'AJAX_TREE_TYPE_MSG', NULL, '6', 0, 0)";
		}

		$sqls[] = "INSERT INTO ".$table_prefix."layouts (sites_all, show_for_user, layout_name, user_layout_name, top_menu_type, style_name, scheme_name, templates_dir, admin_templates_dir) VALUES (1 , 0 , 'Mobile' , NULL , 1 , 'mobile' , NULL , './templates/mobile' , '../templates/admin' )";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.25");
	}


	if (comp_vers("4.0.26", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "orders_items ADD COLUMN order_shipping_id INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "orders_items ADD COLUMN order_shipping_id INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "orders_items ADD COLUMN order_shipping_id INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.0.26");
	}


	if (comp_vers("4.0.27", $current_db_version) == 1)
	{
		// check and add new subscribe/unsubscribe pages
		$sql = " SELECT MAX(page_id) FROM ".$table_prefix."cms_pages "; 
		$max_page_id = get_db_value($sql);

		$sql = " SELECT module_id FROM ".$table_prefix."cms_modules WHERE module_code='global' "; 
		$global_module_id = get_db_value($sql);

		$sql = " SELECT page_id FROM ".$table_prefix."cms_pages WHERE page_code='subscribe' "; 
		$subscribe_page_id = get_db_value($sql);
		$sql = " SELECT page_id FROM ".$table_prefix."cms_pages WHERE page_code='unsubscribe' "; 
		$unsubscribe_page_id = get_db_value($sql);

		if (!$subscribe_page_id) {
			$max_page_id++;
			$subscribe_page_id = $max_page_id;
			$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
			$sql.= $db->tosql($subscribe_page_id, INTEGER).",";
			$sql.= $db->tosql($global_module_id, INTEGER).",";
			$sql.= $db->tosql(13, INTEGER).",";
			$sql.= $db->tosql("subscribe", TEXT).",";
			$sql.= $db->tosql("SUBSCRIBE_TITLE", TEXT).")";
			$sqls[] = $sql;
		}
		if (!$unsubscribe_page_id) {
			$max_page_id++;
			$unsubscribe_page_id = $max_page_id;
			$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
			$sql.= $db->tosql($unsubscribe_page_id, INTEGER).",";
			$sql.= $db->tosql($global_module_id, INTEGER).",";
			$sql.= $db->tosql(14, INTEGER).",";
			$sql.= $db->tosql("unsubscribe", TEXT).",";
			$sql.= $db->tosql("UNSUBSCRIBE_TITLE", TEXT).")";
			$sqls[] = $sql;
		}

		// check subscribe, unsubscribe, header and footer blocks
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='subscribe' "; 
		$subscribe_block_id = get_db_value($sql);
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='unsubscribe' "; 
		$unsubscribe_block_id = get_db_value($sql);
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='header' "; 
		$header_block_id = get_db_value($sql);
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='footer' "; 
		$footer_block_id = get_db_value($sql);

		// check page settings
		$sql = " SELECT MAX(ps_id) FROM ".$table_prefix."cms_pages_settings "; 
		$max_ps_id = get_db_value($sql);

		$sql = " SELECT page_id FROM ".$table_prefix."cms_pages_settings WHERE page_id=".$db->tosql($subscribe_page_id, INTEGER); 
		$subscribe_ps_id = get_db_value($sql);
		$sql = " SELECT page_id FROM ".$table_prefix."cms_pages_settings WHERE page_id=".$db->tosql($unsubscribe_page_id, INTEGER); 
		$unsubscribe_ps_id = get_db_value($sql);

		// subscribe page settings
		if (!$subscribe_ps_id) {
			$max_ps_id++;
			$subscribe_ps_id = $max_ps_id;

			$sql = "INSERT INTO ".$table_prefix."cms_pages_settings ";
			$sql.= " (ps_id,page_id,key_code,key_type,key_rule,layout_id,site_id) VALUES (";
			$sql.= $db->tosql($subscribe_ps_id, INTEGER).",";
			$sql.= $db->tosql($subscribe_page_id, INTEGER).",";
			$sql.= $db->tosql("", TEXT).",";
			$sql.= $db->tosql("", TEXT).",";
			$sql.= $db->tosql("", TEXT).",";
			$sql.= $db->tosql(1, INTEGER).",";
			$sql.= $db->tosql(1, INTEGER).")";
			$sqls[] = $sql;

			// added blocks for page
			if ($header_block_id) {
				$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
				$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
				$sql.= $db->tosql($subscribe_ps_id, INTEGER).",";
				$sql.= $db->tosql(1, INTEGER).",";
				$sql.= $db->tosql($header_block_id, INTEGER).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
			}
	  
			if ($subscribe_block_id) {
				$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
				$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
				$sql.= $db->tosql($subscribe_ps_id, INTEGER).",";
				$sql.= $db->tosql(3, INTEGER).",";
				$sql.= $db->tosql($subscribe_block_id, INTEGER).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
			}
	  
			if ($footer_block_id) {
				$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
				$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
				$sql.= $db->tosql($subscribe_ps_id, INTEGER).",";
				$sql.= $db->tosql(5, INTEGER).",";
				$sql.= $db->tosql($footer_block_id, INTEGER).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
			}

		}

		// unsubscribe page settings
		if (!$unsubscribe_ps_id) {
			$max_ps_id++;
			$unsubscribe_ps_id = $max_ps_id;

			$sql = "INSERT INTO ".$table_prefix."cms_pages_settings ";
			$sql.= " (ps_id,page_id,key_code,key_type,key_rule,layout_id,site_id) VALUES (";
			$sql.= $db->tosql($unsubscribe_ps_id, INTEGER).",";
			$sql.= $db->tosql($unsubscribe_page_id, INTEGER).",";
			$sql.= $db->tosql("", TEXT).",";
			$sql.= $db->tosql("", TEXT).",";
			$sql.= $db->tosql("", TEXT).",";
			$sql.= $db->tosql(1, INTEGER).",";
			$sql.= $db->tosql(1, INTEGER).")";
			$sqls[] = $sql;

			// added blocks for page
			if ($header_block_id) {
				$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
				$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
				$sql.= $db->tosql($unsubscribe_ps_id, INTEGER).",";
				$sql.= $db->tosql(1, INTEGER).",";
				$sql.= $db->tosql($header_block_id, INTEGER).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
			}
	  
			if ($unsubscribe_block_id) {
				$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
				$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
				$sql.= $db->tosql($unsubscribe_ps_id, INTEGER).",";
				$sql.= $db->tosql(3, INTEGER).",";
				$sql.= $db->tosql($unsubscribe_block_id, INTEGER).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
			}
	  
			if ($footer_block_id) {
				$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
				$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
				$sql.= $db->tosql($unsubscribe_ps_id, INTEGER).",";
				$sql.= $db->tosql(5, INTEGER).",";
				$sql.= $db->tosql($footer_block_id, INTEGER).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
			}
		}


		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.1");
	}




?>