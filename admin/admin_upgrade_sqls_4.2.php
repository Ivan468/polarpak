<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_upgrade_sqls_4.2.php                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	check_admin_security("system_upgrade");

	if (comp_vers("4.1.1", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN postal_codes TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN postal_codes TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN postal_codes LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.1.1");
	}

	if (comp_vers("4.1.2", $current_db_version) == 1)
	{
		// order_statuses affiliate notification fields
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_notify TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_notify SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_notify BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_to VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_from VARCHAR(64) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_cc VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_bcc VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_reply_to VARCHAR(64) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_return_path VARCHAR(64) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_mail_type TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_mail_type SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_mail_type BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_subject VARCHAR(255) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_body TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_body TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_body LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_sms_notify TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_sms_notify SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_sms_notify BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_sms_recipient VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_sms_originator VARCHAR(255) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_sms_message TEXT",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_sms_message TEXT",
			"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN affiliate_sms_message LONGTEXT",
		);
		$sqls[] = $sql_types[$db_type];

		// order_statuses affiliate notification fields
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN use_friends_code TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN use_friends_code SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD COLUMN use_friends_code BYTE ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = "CREATE INDEX " . $table_prefix . "coupons_use_friends_code ON " . $table_prefix . "coupons (use_friends_code) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.1.2");
	}


	if (comp_vers("4.1.3", $current_db_version) == 1)
	{
		// check products module_id
		$sql = " SELECT module_id FROM ".$table_prefix."cms_modules WHERE module_code='cart' "; 
		$module_id = get_db_value($sql);
		// get new block_id and block_order
		$sql = " SELECT MAX(block_id) FROM ".$table_prefix."cms_blocks "; 
		$block_id = get_db_value($sql) + 1;
		$sql = " SELECT block_order FROM ".$table_prefix."cms_blocks WHERE block_code='coupon_form' "; 
		$block_order = get_db_value($sql);

		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($block_order, INTEGER).",";
		$sql.= $db->tosql("friend_form", TEXT).",";
		$sql.= $db->tosql("FRIEND_AFFILIATE_FORM_MSG", TEXT).",";
		$sql.= $db->tosql("block_friend_form.php", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.1.3");
	}


	if (comp_vers("4.1.4", $current_db_version) == 1)
	{
		// check products module_id
		$sql = " SELECT module_id FROM ".$table_prefix."cms_modules WHERE module_code='helpdesk' "; 
		$module_id = get_db_value($sql);
		// get new block_id and block_order
		$sql = " SELECT MAX(block_id) FROM ".$table_prefix."cms_blocks "; 
		$block_id = get_db_value($sql) + 1;
		$block_order = 1;

		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($block_order, INTEGER).",";
		$sql.= $db->tosql("support_live", TEXT).",";
		$sql.= $db->tosql("SUPPORT_LIVE_MSG", TEXT).",";
		$sql.= $db->tosql("block_support_live.php", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "admins ADD COLUMN support_online_date DATETIME ",
			"postgre" => "ALTER TABLE " . $table_prefix . "admins ADD COLUMN support_online_date TIMESTAMP ",
			"access"  => "ALTER TABLE " . $table_prefix . "admins ADD COLUMN support_online_date DATETIME ",
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.1.4");
	}


	if (comp_vers("4.1.5", $current_db_version) == 1)
	{
		// get new layout_id and frame_id for popup layout
		$sql = " SELECT MAX(layout_id) FROM ".$table_prefix."cms_layouts "; 
		$layout_id = get_db_value($sql) + 1;
		$sql = " SELECT MAX(frame_id) FROM ".$table_prefix."cms_frames "; 
		$frame_id = get_db_value($sql) + 1;

		$sql = "INSERT INTO ".$table_prefix."cms_layouts (layout_id,layout_name,layout_order,layout_template,admin_template) VALUES (";
		$sql.= $db->tosql($layout_id, INTEGER).",";
		$sql.= $db->tosql("POPUP_FRAME_LAYOUT_MSG", TEXT).",";
		$sql.= $db->tosql(5, INTEGER).",";
		$sql.= $db->tosql("layout_popup_frame.html", TEXT).",";
		$sql.= $db->tosql("admin_layout_popup_frame.html", TEXT).")";
		$sqls[] = $sql;

		$sql = "INSERT INTO ".$table_prefix."cms_frames (frame_id, layout_id, frame_name, tag_name) VALUES (";
		$sql.= $db->tosql($frame_id, INTEGER).",";
		$sql.= $db->tosql($layout_id, INTEGER).",";
		$sql.= $db->tosql("MIDDLE_COLUMN_MSG", TEXT).",";
		$sql.= $db->tosql("middle", TEXT).")";
		$sqls[] = $sql;

		// add new popup page to show after adding to cart 
		$sql = " SELECT MAX(page_id) FROM ".$table_prefix."cms_pages "; 
		$page_id = get_db_value($sql) + 1;
		$sql = " SELECT MAX(page_order) FROM ".$table_prefix."cms_pages WHERE module_id=2 "; 
		$page_order = get_db_value($sql) + 1;

		$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql(2, INTEGER).",";
		$sql.= $db->tosql($page_order, INTEGER).",";
		$sql.= $db->tosql("add_to_cart_frame", TEXT).",";
		$sql.= $db->tosql("ADD_TO_CART_FRAME_MSG", TEXT).")";
		$sqls[] = $sql;

		// added settings for new add to cart page 
		$sql = " SELECT MAX(ps_id) FROM ".$table_prefix."cms_pages_settings "; 
		$ps_id = get_db_value($sql) + 1;

		$sql = "INSERT INTO ".$table_prefix."cms_pages_settings ";
		$sql.= " (ps_id,page_id,key_code,key_type,key_rule,layout_id,site_id) VALUES (";
		$sql.= $db->tosql($ps_id, INTEGER).",";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql($layout_id, INTEGER).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		// added shopping cart block to new page
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='shopping_cart'"; 
		$cart_block_id = get_db_value($sql);

		$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
		$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
		$sql.= $db->tosql($ps_id, INTEGER).",";
		$sql.= $db->tosql($frame_id, INTEGER).",";
		$sql.= $db->tosql($cart_block_id, INTEGER).",";
		$sql.= $db->tosql("", TEXT).",";
		$sql.= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.1.5");
	}


	if (comp_vers("4.1.6", $current_db_version) == 1)
	{
		// admin reply status support_statuses
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "support_statuses ADD COLUMN is_admin_reply TINYINT DEFAULT '0' AFTER is_user_reply",
			"postgre" => "ALTER TABLE " . $table_prefix . "support_statuses ADD COLUMN is_admin_reply SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "support_statuses ADD COLUMN is_admin_reply BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.1.6");
	}

	if (comp_vers("4.1.7", $current_db_version) == 1)
	{

		$mysql_sql  = "CREATE TABLE ".$table_prefix."messages (
	  `message_id` INT(11) NOT NULL AUTO_INCREMENT,
	  `parent_message_id` INT(11) default '0',
	  `reply_message_id` INT(11) default '0',
	  `forward_message_id` INT(11) default '0',
	  `admin_id` INT(11) default '0',
	  `user_id` INT(11) default '0',
	  `system_folder_id` TINYINT default '1',
	  `user_folder_id` INT(11) default '0',
	  `from_admin_id` INT(11) default '0',
	  `from_user_id` INT(11) default '0',
	  `message_from` VARCHAR(128),
	  `message_type` TINYINT default '0',
	  `message_key_id` INT(11) default '0',
	  `message_to` VARCHAR(255),
	  `message_cc` VARCHAR(255),
	  `message_bcc` VARCHAR(255),
	  `message_subject` VARCHAR(255),
	  `message_text` TEXT,
	  `date_added` DATETIME,
	  `date_read` DATETIME,
	  `date_replied` DATETIME,
	  `date_forwarded` DATETIME,
	  `date_sent` DATETIME,
	  `date_modified` DATETIME,
	  `date_trashed` DATETIME,
	  `date_deleted` DATETIME
	  ,KEY date_added (date_added)
	  ,KEY date_trashed (date_trashed)
	  ,KEY date_deleted (date_deleted)
	  ,KEY message_key_id (message_key_id)
	  ,KEY message_type (message_type)
	  ,KEY parent_message_id (parent_message_id)
	  ,PRIMARY KEY (message_id)
	  ,KEY admin_id (admin_id)
	  ,KEY user_id (user_id)
	  ,KEY from_admin_id (from_admin_id)
	  ,KEY from_user_id (from_user_id)
	  ,KEY system_folder_id (system_folder_id)
	  ,KEY user_folder_id (user_folder_id)) DEFAULT CHARACTER SET=utf8mb4 ";


		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."messages START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."messages (
	  message_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."messages'),
	  parent_message_id INT4 default '0',
	  reply_message_id INT4 default '0',
	  forward_message_id INT4 default '0',
	  admin_id INT4 default '0',
	  user_id INT4 default '0',
	  system_folder_id SMALLINT default '1',
	  user_folder_id INT4 default '0',
	  from_admin_id INT4 default '0',
	  from_user_id INT4 default '0',
	  message_from VARCHAR(128),
	  message_type SMALLINT default '0',
	  message_key_id INT4 default '0',
	  message_to VARCHAR(255),
	  message_cc VARCHAR(255),
	  message_bcc VARCHAR(255),
	  message_subject VARCHAR(255),
	  message_text TEXT,
	  date_added TIMESTAMP,
	  date_read TIMESTAMP,
	  date_replied TIMESTAMP,
	  date_forwarded TIMESTAMP,
	  date_sent TIMESTAMP,
	  date_modified TIMESTAMP,
	  date_trashed TIMESTAMP,
	  date_deleted TIMESTAMP
	  ,PRIMARY KEY (message_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."messages (
	  [message_id]  COUNTER  NOT NULL,
	  [parent_message_id] INTEGER,
	  [reply_message_id] INTEGER,
	  [forward_message_id] INTEGER,
	  [admin_id] INTEGER,
	  [user_id] INTEGER,
	  [system_folder_id] BYTE,
	  [user_folder_id] INTEGER,
	  [from_admin_id] INTEGER,
	  [from_user_id] INTEGER,
	  [message_from] VARCHAR(128),
	  [message_type] BYTE,
	  [message_key_id] INTEGER,
	  [message_to] VARCHAR(255),
	  [message_cc] VARCHAR(255),
	  [message_bcc] VARCHAR(255),
	  [message_subject] VARCHAR(255),
	  [message_text] LONGTEXT,
	  [date_added] DATETIME,
	  [date_read] DATETIME,
	  [date_replied] DATETIME,
	  [date_forwarded] DATETIME,
	  [date_sent] DATETIME,
	  [date_modified] DATETIME,
	  [date_trashed] DATETIME,
	  [date_deleted] DATETIME
	  ,PRIMARY KEY (message_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX ".$table_prefix."messages_admin_id ON ".$table_prefix."messages (admin_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."messages_user_id ON ".$table_prefix."messages (user_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."messages_date_added ON ".$table_prefix."messages (date_added)";
			$sqls[] = "CREATE INDEX ".$table_prefix."messages_date_trashed ON ".$table_prefix."messages (date_trashed)";
			$sqls[] = "CREATE INDEX ".$table_prefix."messages_date_deleted ON ".$table_prefix."messages (date_deleted)";
			$sqls[] = "CREATE INDEX ".$table_prefix."messages_message_key_id ON ".$table_prefix."messages (message_key_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."messages_message_type ON ".$table_prefix."messages (message_type)";
			$sqls[] = "CREATE INDEX ".$table_prefix."messages_parent_message_id ON ".$table_prefix."messages (parent_message_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."messages_from_admin_id ON ".$table_prefix."messages (from_admin_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."messages_from_user_id ON ".$table_prefix."messages (from_user_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."messages_system_folder_id ON ".$table_prefix."messages (system_folder_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."messages_user_folder_id ON ".$table_prefix."messages (user_folder_id)";
		}

		// add new page for internal message system
		$sql = " SELECT module_id FROM ".$table_prefix."cms_modules WHERE module_code='user_account' "; 
		$module_id = get_db_value($sql);
		$sql = " SELECT MAX(page_id) FROM ".$table_prefix."cms_pages "; 
		$page_id = get_db_value($sql) + 1;
		$sql = " SELECT MAX(page_order) FROM ".$table_prefix."cms_pages WHERE module_id=" . $db->tosql($module_id, INTEGER); 
		$page_order = get_db_value($sql) + 1;
		$sql = " SELECT MAX(block_id) FROM ".$table_prefix."cms_blocks "; 
		$block_id = get_db_value($sql) + 1;
		$sql = " SELECT MAX(block_order) FROM ".$table_prefix."cms_blocks WHERE module_id=" . $db->tosql($module_id, INTEGER); 
		$block_order = get_db_value($sql) + 1;
		$sql = " SELECT MAX(ps_id) FROM ".$table_prefix."cms_pages_settings "; 
		$ps_id = get_db_value($sql) + 1;

		$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($page_order, INTEGER).",";
		$sql.= $db->tosql("user_messages", TEXT).",";
		$sql.= $db->tosql("MY_MESSAGES_MSG", TEXT).")";
		$sqls[] = $sql;

		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($block_order, INTEGER).",";
		$sql.= $db->tosql("user_messages", TEXT).",";
		$sql.= $db->tosql("MY_MESSAGES_MSG", TEXT).",";
		$sql.= $db->tosql("block_user_messages.php", TEXT).",";
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
		
		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.1.7");
	}


	if (comp_vers("4.1.8", $current_db_version) == 1)
	{
		// reminder fix for missing field
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "reminders ADD COLUMN date_sent DATETIME ",
			"postgre" => "ALTER TABLE " . $table_prefix . "reminders ADD COLUMN date_sent TIMESTAMP ",
			"access"  => "ALTER TABLE " . $table_prefix . "reminders ADD COLUMN date_sent DATETIME ",
		);
		$sqls[] = $sql_types[$db_type];

		// new chats tables

		$mysql_sql  = "CREATE TABLE ".$table_prefix."chats (
	  `chat_id` INT(11) NOT NULL AUTO_INCREMENT,
	  `chat_status` TINYINT default '1',
	  `user_id` INT(11) default '0',
	  `user_name` VARCHAR(128),
	  `user_email` VARCHAR(128),
	  `user_message` TEXT,
	  `admin_id` INT(11) default '0',
	  `chat_added` DATETIME,
	  `chat_started` DATETIME,
	  `chat_closed` DATETIME,
	  `user_online` DATETIME,
	  `user_last_added` DATETIME,
	  `admin_online` DATETIME,
	  `admin_last_added` DATETIME
	  ,PRIMARY KEY (chat_id)) DEFAULT CHARACTER SET=utf8mb4 ";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."chats START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."chats (
	  chat_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."chats'),
	  chat_status SMALLINT default '1',
	  user_id INT4 default '0',
	  user_name VARCHAR(128),
	  user_email VARCHAR(128),
	  user_message TEXT,
	  admin_id INT4 default '0',
	  chat_added TIMESTAMP,
	  chat_started TIMESTAMP,
	  chat_closed TIMESTAMP,
	  user_online TIMESTAMP,
	  user_last_added TIMESTAMP,
	  admin_online TIMESTAMP,
	  admin_last_added TIMESTAMP
	  ,PRIMARY KEY (chat_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."chats (
	  [chat_id]  COUNTER  NOT NULL,
	  [chat_status] BYTE,
	  [user_id] INTEGER,
	  [user_name] VARCHAR(128),
	  [user_email] VARCHAR(128),
	  [user_message] LONGTEXT,
	  [admin_id] INTEGER,
	  [chat_added] DATETIME,
	  [chat_started] DATETIME,
	  [chat_closed] DATETIME,
	  [user_online] DATETIME,
	  [user_last_added] DATETIME,
	  [admin_online] DATETIME,
	  [admin_last_added] DATETIME
	  ,PRIMARY KEY (chat_id))";


		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		$mysql_sql  = "CREATE TABLE ".$table_prefix."chats_messages (
	  `message_id` INT(11) NOT NULL AUTO_INCREMENT,
	  `chat_id` INT(11) default '0',
	  `admin_id` INT(11) default '0',
	  `is_user_message` TINYINT default '0',
	  `message_type` TINYINT default '1',
	  `message_text` TEXT,
	  `message_added` DATETIME
	  ,KEY admin_id (admin_id)
	  ,KEY chat_id (chat_id)
	  ,KEY message_type (message_type)
	  ,PRIMARY KEY (message_id)) DEFAULT CHARACTER SET=utf8mb4 ";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."chats_messages START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."chats_messages (
	  message_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."chats_messages'),
	  chat_id INT4 default '0',
	  admin_id INT4 default '0',
	  is_user_message SMALLINT default '0',
	  message_type SMALLINT default '1',
	  message_text TEXT,
	  message_added TIMESTAMP
	  ,PRIMARY KEY (message_id))";

		$access_sql  = "CREATE TABLE ".$table_prefix."chats_messages (
	  [message_id]  COUNTER  NOT NULL,
	  [chat_id] INTEGER,
	  [admin_id] INTEGER,
	  [is_user_message] BYTE,
	  [message_type] BYTE,
	  [message_text] LONGTEXT,
	  [message_added] DATETIME
	  ,PRIMARY KEY (message_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX ".$table_prefix."chats_messages_admin_id ON ".$table_prefix."chats_messages (admin_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."chats_messages_chat_id ON ".$table_prefix."chats_messages (chat_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."chats_messages_message_type ON ".$table_prefix."chats_messages (message_type)";
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.1.8");
	}

	if (comp_vers("4.1.9", $current_db_version) == 1)
	{
		$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='twitter_feed' "; 
		$twitter_block_id = get_db_value($sql);

		$sql = " SELECT MAX(property_id) FROM ".$table_prefix."cms_blocks_properties "; 
		$property_id = get_db_value($sql);

		$sql = " SELECT MAX(property_order) FROM ".$table_prefix."cms_blocks_properties WHERE block_id=" . $db->tosql($twitter_block_id, INTEGER); 
		$property_order = get_db_value($sql);

		$property_id++; $property_order++;
		$sql = "INSERT INTO " . $table_prefix . "cms_blocks_properties (";
		$sql .= "property_id,block_id,property_order,property_name,control_type,variable_name,required) VALUES (";
		$sql .= $db->tosql($property_id, INTEGER).",";
		$sql .= $db->tosql($twitter_block_id, INTEGER).",";
		$sql .= $db->tosql($property_order, INTEGER).",";
		$sql .= $db->tosql("TWITTER_SHOW_ICON_MSG", TEXT).",";
		$sql .= $db->tosql("CHECKBOX", TEXT).",";
		$sql .= $db->tosql("tf_show_icon", TEXT).",";
		$sql .= $db->tosql(0, INTEGER).")";
		$sqls[] = $sql;

		$property_id++; $property_order++;
		$sql = "INSERT INTO " . $table_prefix . "cms_blocks_properties (";
		$sql .= "property_id,block_id,property_order,property_name,control_type,variable_name,required) VALUES (";
		$sql .= $db->tosql($property_id, INTEGER).",";
		$sql .= $db->tosql($twitter_block_id, INTEGER).",";
		$sql .= $db->tosql($property_order, INTEGER).",";
		$sql .= $db->tosql("TWITTER_CONSUMER_KEY_MSG", TEXT).",";
		$sql .= $db->tosql("TEXTBOX", TEXT).",";
		$sql .= $db->tosql("consumer_key", TEXT).",";
		$sql .= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		$property_id++; $property_order++;
		$sql = "INSERT INTO " . $table_prefix . "cms_blocks_properties (";
		$sql .= "property_id,block_id,property_order,property_name,control_type,variable_name,required) VALUES (";
		$sql .= $db->tosql($property_id, INTEGER).",";
		$sql .= $db->tosql($twitter_block_id, INTEGER).",";
		$sql .= $db->tosql($property_order, INTEGER).",";
		$sql .= $db->tosql("TWITTER_CONSUMER_SECRET_MSG", TEXT).",";
		$sql .= $db->tosql("TEXTBOX", TEXT).",";
		$sql .= $db->tosql("consumer_secret", TEXT).",";
		$sql .= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		$property_id++; $property_order++;
		$sql = "INSERT INTO " . $table_prefix . "cms_blocks_properties (";
		$sql .= "property_id,block_id,property_order,property_name,control_type,variable_name,required) VALUES (";
		$sql .= $db->tosql($property_id, INTEGER).",";
		$sql .= $db->tosql($twitter_block_id, INTEGER).",";
		$sql .= $db->tosql($property_order, INTEGER).",";
		$sql .= $db->tosql("TWITTER_ACCESS_TOKEN_MSG", TEXT).",";
		$sql .= $db->tosql("TEXTBOX", TEXT).",";
		$sql .= $db->tosql("oauth_access_token", TEXT).",";
		$sql .= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		$property_id++; $property_order++;
		$sql = "INSERT INTO " . $table_prefix . "cms_blocks_properties (";
		$sql .= "property_id,block_id,property_order,property_name,control_type,variable_name,required) VALUES (";
		$sql .= $db->tosql($property_id, INTEGER).",";
		$sql .= $db->tosql($twitter_block_id, INTEGER).",";
		$sql .= $db->tosql($property_order, INTEGER).",";
		$sql .= $db->tosql("TWITTER_ACCESS_TOKEN_SECRET_MSG", TEXT).",";
		$sql .= $db->tosql("TEXTBOX", TEXT).",";
		$sql .= $db->tosql("oauth_access_token_secret", TEXT).",";
		$sql .= $db->tosql(1, INTEGER).")";
		$sqls[] = $sql;

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.1.9");
	}

	if (comp_vers("4.1.10", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "items_images ADD COLUMN image_order INT(11) default '1' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "items_images ADD COLUMN image_order INT4 default '1' ",
			"access"  => "ALTER TABLE " . $table_prefix . "items_images ADD COLUMN image_order INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "ALTER TABLE " . $table_prefix . "items_images ADD COLUMN image_tiny VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "items_images ADD COLUMN image_tiny_alt VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "items_images ADD COLUMN image_small_alt VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "items_images ADD COLUMN image_large_alt VARCHAR(255) ";
		$sqls[] = "ALTER TABLE " . $table_prefix . "items_images ADD COLUMN image_super_alt VARCHAR(255) ";

		$sqls[] = "ALTER TABLE " . $table_prefix . "items ADD COLUMN super_image_alt VARCHAR(255) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.1.10");
	}

	if (comp_vers("4.1.11", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN processing_tax_free INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN processing_tax_free INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD COLUMN processing_tax_free INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];
		// old versions are tax free for processing fees
		$sqls[] = "UPDATE " . $table_prefix . "payment_systems SET processing_tax_free=1 ";

		$sql_types = array(
			"mysql"  => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN processing_excl_tax DOUBLE(16,2) default '0' ",
			"postgre"=> "ALTER TABLE " . $table_prefix . "orders ADD COLUMN processing_excl_tax FLOAT4 default '0' ",
			"access" => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN processing_excl_tax FLOAT "
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"  => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN processing_tax DOUBLE(16,2) default '0' ",
			"postgre"=> "ALTER TABLE " . $table_prefix . "orders ADD COLUMN processing_tax FLOAT4 default '0' ",
			"access" => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN processing_tax FLOAT "
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"  => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN processing_incl_tax DOUBLE(16,2) default '0' ",
			"postgre"=> "ALTER TABLE " . $table_prefix . "orders ADD COLUMN processing_incl_tax FLOAT4 default '0' ",
			"access" => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN processing_incl_tax FLOAT "
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"  => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN processing_tax_free INT(11) default '0' ",
			"postgre"=> "ALTER TABLE " . $table_prefix . "orders ADD COLUMN processing_tax_free INT4 default '0' ",
			"access" => "ALTER TABLE " . $table_prefix . "orders ADD COLUMN processing_tax_free INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];
		// update old orders to use new processing fields
		$sqls[] = "UPDATE " . $table_prefix . "orders SET processing_tax_free=1,processing_excl_tax=processing_fee,processing_tax=0,processing_incl_tax=processing_fee ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.1.11");
	}


	if (comp_vers("4.1.12", $current_db_version) == 1)
	{
		// order_statuses affiliate notification fields
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "items_images ADD COLUMN is_default TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "items_images ADD COLUMN is_default SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "items_images ADD COLUMN is_default BYTE ",
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.1.12");
	}

	if (comp_vers("4.1.13", $current_db_version) == 1)
	{
		$mysql_sql  = "CREATE TABLE ".$table_prefix."items_tabs (
		  `tab_id` INT(11) NOT NULL AUTO_INCREMENT,
      `item_id` INT(11) default '0',
      `tab_order` INT(11) default '1',
      `tab_title` VARCHAR(50),
      `tab_desc` TEXT,
      `hide_tab` TINYINT default '0'
      ,KEY item_id (item_id)
      ,PRIMARY KEY (tab_id)) DEFAULT CHARACTER SET=utf8mb4 ";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."items_tabs START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."items_tabs (
      tab_id INT4 NOT NULL default '0',
      item_id INT4 default '0',
      tab_order INT4 default '1',
      tab_title VARCHAR(50),
      tab_desc TEXT,
      hide_tab SMALLINT default '0'
      ,PRIMARY KEY (tab_id)) DEFAULT CHARACTER SET=utf8mb4 ";

		$access_sql  = "CREATE TABLE ".$table_prefix."items_tabs (
      [tab_id] COUNTER NOT NULL,
      [item_id] INTEGER,
      [tab_order] INTEGER,
      [tab_title] VARCHAR(50),
      [tab_desc] LONGTEXT,
      [hide_tab] BYTE
      ,PRIMARY KEY (tab_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX ".$table_prefix."items_tabs_item_id ON ".$table_prefix."items_tabs (item_id)";
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.1.13");
	}


	if (comp_vers("4.1.14", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "items ADD COLUMN admin_access_level TINYINT UNSIGNED NOT NULL DEFAULT '7'",
			"postgre" => "ALTER TABLE " . $table_prefix . "items ADD COLUMN admin_access_level SMALLINT NOT NULL DEFAULT '7'",
			"access"  => "ALTER TABLE " . $table_prefix . "items ADD COLUMN admin_access_level BYTE NOT NULL ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "items SET admin_access_level=7 ";
		$sqls[] = " CREATE INDEX " . $table_prefix . "items_admin_access ON " . $table_prefix . "items (admin_access_level) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN admin_access_level TINYINT UNSIGNED NOT NULL DEFAULT '7'",
			"postgre" => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN admin_access_level SMALLINT NOT NULL DEFAULT '7'",
			"access"  => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN admin_access_level BYTE NOT NULL ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "categories SET admin_access_level=7 ";
		$sqls[] = " CREATE INDEX " . $table_prefix . "categories_admin_access ON " . $table_prefix . "categories (admin_access_level) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.1.14");
	}


	if (comp_vers("4.1.15", $current_db_version) == 1)
	{
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "ads_categories ADD COLUMN admin_access_level TINYINT UNSIGNED NOT NULL DEFAULT '7'",
			"postgre" => "ALTER TABLE " . $table_prefix . "ads_categories ADD COLUMN admin_access_level SMALLINT NOT NULL DEFAULT '7'",
			"access"  => "ALTER TABLE " . $table_prefix . "ads_categories ADD COLUMN admin_access_level BYTE NOT NULL ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "ads_categories SET admin_access_level=7 ";
		$sqls[] = " CREATE INDEX " . $table_prefix . "ads_categories_admin ON " . $table_prefix . "ads_categories (admin_access_level) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "articles_categories ADD COLUMN admin_access_level TINYINT UNSIGNED NOT NULL DEFAULT '7'",
			"postgre" => "ALTER TABLE " . $table_prefix . "articles_categories ADD COLUMN admin_access_level SMALLINT NOT NULL DEFAULT '7'",
			"access"  => "ALTER TABLE " . $table_prefix . "articles_categories ADD COLUMN admin_access_level BYTE NOT NULL ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "articles_categories SET admin_access_level=7 ";
		$sqls[] = " CREATE INDEX " . $table_prefix . "articles_categories_admin ON " . $table_prefix . "articles_categories (admin_access_level) ";


		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "forum_list ADD COLUMN admin_access_level TINYINT UNSIGNED NOT NULL DEFAULT '7'",
			"postgre" => "ALTER TABLE " . $table_prefix . "forum_list ADD COLUMN admin_access_level SMALLINT NOT NULL DEFAULT '7'",
			"access"  => "ALTER TABLE " . $table_prefix . "forum_list ADD COLUMN admin_access_level BYTE NOT NULL ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "forum_list SET admin_access_level=7 ";
		$sqls[] = " CREATE INDEX " . $table_prefix . "forum_list_admin ON " . $table_prefix . "forum_list (admin_access_level) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "manuals_categories ADD COLUMN admin_access_level TINYINT UNSIGNED NOT NULL DEFAULT '7'",
			"postgre" => "ALTER TABLE " . $table_prefix . "manuals_categories ADD COLUMN admin_access_level SMALLINT NOT NULL DEFAULT '7'",
			"access"  => "ALTER TABLE " . $table_prefix . "manuals_categories ADD COLUMN admin_access_level BYTE NOT NULL ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "manuals_categories SET admin_access_level=7 ";
		$sqls[] = " CREATE INDEX " . $table_prefix . "manuals_categories_admin ON " . $table_prefix . "manuals_categories (admin_access_level) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "footer_links ADD COLUMN admin_access_level TINYINT UNSIGNED NOT NULL DEFAULT '7'",
			"postgre" => "ALTER TABLE " . $table_prefix . "footer_links ADD COLUMN admin_access_level SMALLINT NOT NULL DEFAULT '7'",
			"access"  => "ALTER TABLE " . $table_prefix . "footer_links ADD COLUMN admin_access_level BYTE NOT NULL ",
		);
		$sqls[] = $sql_types[$db_type];
		$sqls[] = " UPDATE " . $table_prefix . "footer_links SET admin_access_level=7 ";
		$sqls[] = " CREATE INDEX " . $table_prefix . "footer_links_admin ON " . $table_prefix . "footer_links (admin_access_level) ";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.1.15");
	}


	if (comp_vers("4.1.16", $current_db_version) == 1)
	{
		$sqls[] = "ALTER TABLE " . $table_prefix . "admins ADD COLUMN cell_phone VARCHAR(16) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "admins ADD COLUMN login_attempts INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "admins ADD COLUMN login_attempts INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "admins ADD COLUMN login_attempts INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "admins ADD COLUMN login_failed DATETIME ",
			"postgre" => "ALTER TABLE " . $table_prefix . "admins ADD COLUMN login_failed TIMESTAMP ",
			"access"  => "ALTER TABLE " . $table_prefix . "admins ADD COLUMN login_failed DATETIME ",
		);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "ALTER TABLE " . $table_prefix . "admins ADD COLUMN access_code VARCHAR(16) ";

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "admins ADD COLUMN access_added DATETIME ",
			"postgre" => "ALTER TABLE " . $table_prefix . "admins ADD COLUMN access_added TIMESTAMP ",
			"access"  => "ALTER TABLE " . $table_prefix . "admins ADD COLUMN access_added DATETIME ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "admins ADD COLUMN access_attempts INT(11) default '0' ",
			"postgre" => "ALTER TABLE " . $table_prefix . "admins ADD COLUMN access_attempts INT4 default '0' ",
			"access"  => "ALTER TABLE " . $table_prefix . "admins ADD COLUMN access_attempts INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "admins ADD COLUMN access_failed DATETIME ",
			"postgre" => "ALTER TABLE " . $table_prefix . "admins ADD COLUMN access_failed TIMESTAMP ",
			"access"  => "ALTER TABLE " . $table_prefix . "admins ADD COLUMN access_failed DATETIME ",
		);
		$sqls[] = $sql_types[$db_type];

		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN redirect_category_id INT(11)  ",
			"postgre" => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN redirect_category_id INT4 ",
			"access"  => "ALTER TABLE " . $table_prefix . "categories ADD COLUMN redirect_category_id INTEGER ",
		);
		$sqls[] = $sql_types[$db_type];

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.1.16");
	}


	if (comp_vers("4.1.17", $current_db_version) == 1)
	{
		$mysql_sql  = "CREATE TABLE ".$table_prefix."profiles (
      `profile_id` INT(11) NOT NULL AUTO_INCREMENT,
      `user_id` INT(11) default '0',
      `is_shown` TINYINT default '1',
      `is_approved` TINYINT default '1',
      `profile_type_id` INT(11) default '0',
      `looking_type_id` INT(11) default '0',
      `profile_name` VARCHAR(128),
      `birth_year` SMALLINT default '0',
      `birth_month` TINYINT,
      `birth_day` TINYINT,
      `birth_date` DATETIME,
      `country_id` INT(11) default '0',
      `state_id` INT(11) default '0',
      `postal_code` VARCHAR(16),
      `city` VARCHAR(128),
      `photo_id` INT(11) default '0',
      `ethnicity_id` INT(11) default '0',
      `height` SMALLINT default '0',
      `weight` SMALLINT default '0',
      `profile_info` TEXT,
      `looking_info` TEXT,
      `date_added` DATETIME,
      `date_updated` DATETIME,
      `date_last_visit` DATETIME
      ,KEY is_approved (is_approved)
      ,KEY is_shown (is_shown)
      ,KEY looking_type_id (looking_type_id)
      ,KEY photo_id (photo_id)
      ,PRIMARY KEY (profile_id)
      ,KEY profile_type_id (profile_type_id)
      ,KEY user_id (user_id)) DEFAULT CHARACTER SET=utf8mb4 ";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."profiles START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."profiles (
      profile_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."profiles'),
      user_id INT4 default '0',
      is_shown SMALLINT default '1',
      is_approved SMALLINT default '1',
      profile_type_id INT4 default '0',
      looking_type_id INT4 default '0',
      profile_name VARCHAR(128),
      birth_year SMALLINT default '0',
      birth_month SMALLINT,
      birth_day SMALLINT,
      birth_date TIMESTAMP,
      country_id INT4 default '0',
      state_id INT4 default '0',
      postal_code VARCHAR(16),
      city VARCHAR(128),
      photo_id INT4 default '0',
      ethnicity_id INT4 default '0',
      height SMALLINT default '0',
      weight SMALLINT default '0',
      profile_info TEXT,
      looking_info TEXT,
      date_added TIMESTAMP,
      date_updated TIMESTAMP,
      date_last_visit TIMESTAMP
      ,PRIMARY KEY (profile_id)) DEFAULT CHARACTER SET=utf8mb4 ";


		$access_sql  = "CREATE TABLE ".$table_prefix."profiles (
      [profile_id]  COUNTER  NOT NULL,
      [user_id] INTEGER,
      [is_shown] BYTE,
      [is_approved] BYTE,
      [profile_type_id] INTEGER,
      [looking_type_id] INTEGER,
      [profile_name] VARCHAR(128),
      [birth_year] INTEGER,
      [birth_month] BYTE,
      [birth_day] BYTE,
      [birth_date] DATETIME,
      [country_id] INTEGER,
      [state_id] INTEGER,
      [postal_code] VARCHAR(16),
      [city] VARCHAR(128),
      [photo_id] INTEGER,
      [ethnicity_id] INTEGER,
      [height] INTEGER,
      [weight] INTEGER,
      [profile_info] LONGTEXT,
      [looking_info] LONGTEXT,
      [date_added] DATETIME,
      [date_updated] DATETIME,
      [date_last_visit] DATETIME
      ,PRIMARY KEY (profile_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX ".$table_prefix."profiles_is_approved ON ".$table_prefix."profiles (is_approved)";
			$sqls[] = "CREATE INDEX ".$table_prefix."profiles_is_shown ON ".$table_prefix."profiles (is_shown)";
			$sqls[] = "CREATE INDEX ".$table_prefix."profiles_looking_type_id ON ".$table_prefix."profiles (looking_type_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."profiles_photo_id ON ".$table_prefix."profiles (photo_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."profiles_profile_type_id ON ".$table_prefix."profiles (profile_type_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."profiles_user_id ON ".$table_prefix."profiles (user_id)";
		}

		$mysql_sql  = "CREATE TABLE ".$table_prefix."profiles_types (
      `profile_type_id` INT(11) NOT NULL AUTO_INCREMENT,
      `profile_type_name` VARCHAR(255),
      `show_for_user` TINYINT default '1',
      `is_profile_default` TINYINT default '0',
      `is_looking_default` TINYINT default '0'
      ,PRIMARY KEY (profile_type_id)) DEFAULT CHARACTER SET=utf8mb4 ";


		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."profiles_types START 3";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."profiles_types (
      profile_type_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."profiles_types'),
      profile_type_name VARCHAR(255),
      show_for_user SMALLINT default '1',
      is_profile_default SMALLINT default '0',
      is_looking_default SMALLINT default '0'
      ,PRIMARY KEY (profile_type_id))";


		$access_sql  = "CREATE TABLE ".$table_prefix."profiles_types (
      [profile_type_id]  COUNTER  NOT NULL,
      [profile_type_name] VARCHAR(255),
      [show_for_user] BYTE,
      [is_profile_default] BYTE,
      [is_looking_default] BYTE
      ,PRIMARY KEY (profile_type_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "INSERT INTO ".$table_prefix."profiles_types (profile_type_id,profile_type_name,show_for_user,is_profile_default,is_looking_default) VALUES (1 , 'Male' , 1 , 1 , 0 )";
		$sqls[] = "INSERT INTO ".$table_prefix."profiles_types (profile_type_id,profile_type_name,show_for_user,is_profile_default,is_looking_default) VALUES (2 , 'Female' , 1 , 0 , 1 )";

		$mysql_sql  = "CREATE TABLE ".$table_prefix."users_photos (
      `photo_id` INT(11) NOT NULL AUTO_INCREMENT,
      `user_id` INT(11) default '0',
      `key_id` INT(11) default '0',
      `photo_type` TINYINT default '1',
      `is_shown` TINYINT default '1',
      `is_approved` TINYINT default '1',
      `photo_name` VARCHAR(255),
      `photo_desc` TEXT,
      `tiny_photo` VARCHAR(255),
      `small_photo` VARCHAR(255),
      `large_photo` VARCHAR(255),
      `super_photo` VARCHAR(255),
      `date_added` DATETIME,
      `date_updated` DATETIME
      ,PRIMARY KEY (photo_id)
      ,KEY user_id (user_id)
      ,KEY photo_type (photo_type)
      ,KEY key_id (key_id)) DEFAULT CHARACTER SET=utf8mb4 ";

		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."users_photos START 1";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."users_photos (
      photo_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."users_photos'),
      user_id INT4 default '0',
      key_id INT4 default '0',
      photo_type INT4 default '1',
      is_shown SMALLINT default '1',
      is_approved SMALLINT default '1',
      photo_name VARCHAR(255),
      photo_desc TEXT,
      tiny_photo VARCHAR(255),
      small_photo VARCHAR(255),
      large_photo VARCHAR(255),
      super_photo VARCHAR(255),
      date_added TIMESTAMP,
      date_updated TIMESTAMP
      ,PRIMARY KEY (photo_id))";
    

		$access_sql  = "CREATE TABLE ".$table_prefix."users_photos (
      [photo_id]  COUNTER  NOT NULL,
      [user_id] INTEGER,
      [key_id] INTEGER,
      [photo_type] BYTE,
      [is_shown] BYTE,
      [is_approved] BYTE,
      [photo_name] VARCHAR(255),
      [photo_desc] LONGTEXT,
      [tiny_photo] VARCHAR(255),
      [small_photo] VARCHAR(255),
      [large_photo] VARCHAR(255),
      [super_photo] VARCHAR(255),
      [date_added] DATETIME,
      [date_updated] DATETIME
      ,PRIMARY KEY (photo_id))";
    

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		if ($db_type != "mysql") {
			$sqls[] = "CREATE INDEX ".$table_prefix."users_photos_user_id ON ".$table_prefix."users_photos (user_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."users_photos_key_id ON ".$table_prefix."users_photos (key_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."users_photos_photo_type ON ".$table_prefix."users_photos (photo_type)";
		}

		$mysql_sql  = "CREATE TABLE ".$table_prefix."ethnicities (
      `ethnicity_id` INT(11) NOT NULL AUTO_INCREMENT,
      `ethnicity_name` VARCHAR(255),
      `sort_order` INT(11) default '1',
      `show_for_user` TINYINT default '1'
      ,PRIMARY KEY (ethnicity_id)) DEFAULT CHARACTER SET=utf8mb4 ";


		if ($db_type == "postgre") {
			$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."ethnicities START 8";
		}
		$postgre_sql  = "CREATE TABLE ".$table_prefix."ethnicities (
      ethnicity_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."ethnicities'),
      ethnicity_name VARCHAR(255),
      sort_order INT4 default '1',
      show_for_user SMALLINT default '1'
      ,PRIMARY KEY (ethnicity_id))";


		$access_sql  = "CREATE TABLE ".$table_prefix."ethnicities (
      [ethnicity_id]  COUNTER  NOT NULL,
      [ethnicity_name] VARCHAR(255),
      [sort_order] INTEGER,
      [show_for_user] BYTE
      ,PRIMARY KEY (ethnicity_id))";

		$sql_types = array("mysql" => $mysql_sql, "postgre" => $postgre_sql, "access" => $access_sql);
		$sqls[] = $sql_types[$db_type];

		$sqls[] = "INSERT INTO ".$table_prefix."ethnicities (ethnicity_id,ethnicity_name,sort_order,show_for_user) VALUES (1 , 'Caucasian/White' , 1 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."ethnicities (ethnicity_id,ethnicity_name,sort_order,show_for_user) VALUES (2 , 'Black' , 2 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."ethnicities (ethnicity_id,ethnicity_name,sort_order,show_for_user) VALUES (3 , 'Hispanic' , 3 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."ethnicities (ethnicity_id,ethnicity_name,sort_order,show_for_user) VALUES (4 , 'Middle Eastern' , 4 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."ethnicities (ethnicity_id,ethnicity_name,sort_order,show_for_user) VALUES (5 , 'Asian' , 5 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."ethnicities (ethnicity_id,ethnicity_name,sort_order,show_for_user) VALUES (6 , 'Indian' , 6 , 1 )";
		$sqls[] = "INSERT INTO ".$table_prefix."ethnicities (ethnicity_id,ethnicity_name,sort_order,show_for_user) VALUES (7 , 'Other Ethnicity' , 7 , 1 )";

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.1.17");
	}


	if (comp_vers("4.1.18", $current_db_version) == 1)
	{
		// add new Profiles module, pages, blocks and settings for it
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
		$sql.= $db->tosql("profiles", TEXT).",";
		$sql.= $db->tosql("PROFILES_TITLE", TEXT).")";
		$sqls[] = $sql;

		// add new blocks, pages and settings
		//-----------------------------------
		// add profiles home page
		$page_id++; $page_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($page_order, INTEGER).",";
		$sql.= $db->tosql("profiles_home", TEXT).",";
		$sql.= $db->tosql("{PROFILES_TITLE}: {HOME_PAGE_TITLE}", TEXT).")";
		$sqls[] = $sql;

		// add profiles search block
		$block_id++; $block_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($block_order, INTEGER).",";
		$sql.= $db->tosql("profiles_search", TEXT).",";
		$sql.= $db->tosql("SEARCH_FORM_MSG", TEXT).",";
		$sql.= $db->tosql("block_profiles_search.php", TEXT).",";
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


		//-----------------------------------
		// add profiles listing page
		$page_id++; $page_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($page_order, INTEGER).",";
		$sql.= $db->tosql("profiles_list", TEXT).",";
		$sql.= $db->tosql("{LISTING_PAGE_MSG}", TEXT).")";
		$sqls[] = $sql;

		// add profiles search block
		$block_id++; $block_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($block_order, INTEGER).",";
		$sql.= $db->tosql("profiles_list", TEXT).",";
		$sql.= $db->tosql("{LIST_MSG}", TEXT).",";
		$sql.= $db->tosql("block_profiles_list.php", TEXT).",";
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

		//-----------------------------------
		// add profiles view page
		$page_id++; $page_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($page_order, INTEGER).",";
		$sql.= $db->tosql("profiles_view", TEXT).",";
		$sql.= $db->tosql("{DETAILS_PAGE_MSG}", TEXT).")";
		$sqls[] = $sql;

		// add profiles search block
		$block_id++; $block_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($block_order, INTEGER).",";
		$sql.= $db->tosql("profiles_view", TEXT).",";
		$sql.= $db->tosql("{VIEW_MSG}", TEXT).",";
		$sql.= $db->tosql("block_profiles_view.php", TEXT).",";
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


		//-----------------------------------
		// add user profiles list page
		$page_id++; $page_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($page_order, INTEGER).",";
		$sql.= $db->tosql("profiles_user_list", TEXT).",";
		$sql.= $db->tosql("{MY_PROFILES_MSG}: {LIST_MSG}", TEXT).")";
		$sqls[] = $sql;

		// add profiles search block
		$block_id++; $block_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($block_order, INTEGER).",";
		$sql.= $db->tosql("profiles_user_list", TEXT).",";
		$sql.= $db->tosql("{MY_PROFILES_MSG}: {LIST_MSG}", TEXT).",";
		$sql.= $db->tosql("block_profiles_user_list.php", TEXT).",";
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


		//-----------------------------------
		// add user profiles edit page
		$page_id++; $page_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
		$sql.= $db->tosql($page_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($page_order, INTEGER).",";
		$sql.= $db->tosql("profiles_user_edit", TEXT).",";
		$sql.= $db->tosql("{MY_PROFILES_MSG}: {EDIT_MSG}", TEXT).")";
		$sqls[] = $sql;

		// add profiles search block
		$block_id++; $block_order++;
		$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
		$sql.= $db->tosql($block_id, INTEGER).",";
		$sql.= $db->tosql($module_id, INTEGER).",";
		$sql.= $db->tosql($block_order, INTEGER).",";
		$sql.= $db->tosql("profiles_user_edit", TEXT).",";
		$sql.= $db->tosql("{MY_PROFILES_MSG}: {EDIT_MSG}", TEXT).",";
		$sql.= $db->tosql("block_profiles_user_edit.php", TEXT).",";
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

		run_queries($sqls, $queries_success, $queries_failed, $errors, "4.2");
	}


?>