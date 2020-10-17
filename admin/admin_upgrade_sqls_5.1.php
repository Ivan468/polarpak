<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_upgrade_sqls_5.1.php                               ***
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
	$orders_attachments = false; $shipping_companies = false; $authors_languages = false; $conditions_table = false;
	$authors_roles = false; $articles_sites = false; $favorite_lists = false; $favorite_articles = false; 
	$keywords_articles_table = false; $coupons_events_table = false; $reviews_emotions_table = false;
	$order_statuses_sites = false; $categories_tabs_table = false;
	foreach ($tables as $table_id => $table_name) {
		if ($table_name == $table_prefix."orders_attachments") {
			$orders_attachments = true;
		} else if ($table_name == $table_prefix."shipping_companies") {
			$shipping_companies = true;
		} else if ($table_name == $table_prefix."authors_languages") {
			$authors_languages = true;
		} else if ($table_name == $table_prefix."conditions") {
			$conditions_table = true;
		} else if ($table_name == $table_prefix."authors_roles") {
			$authors_roles = true;
		} else if ($table_name == $table_prefix."articles_sites") {
			$articles_sites = true;
		} else if ($table_name == $table_prefix."favorite_lists") {
			$favorite_lists = true;
		} else if ($table_name == $table_prefix."favorite_articles") {
			$favorite_articles = true;
		} else if ($table_name == $table_prefix."order_statuses_sites") {
			$order_statuses_sites = true;
		} else if ($table_name == $table_prefix."keywords_articles") {
			$keywords_articles_table = true;
		} else if ($table_name == $table_prefix."coupons_events") {
			$coupons_events_table = true;
		} else if ($table_name == $table_prefix."reviews_emotions") {
			$reviews_emotions_table = true;
		} else if ($table_name == $table_prefix."categories_tabs") {
			$categories_tabs_table = true;
		}
	}

	// check for field update field
	$php_lib_field = false; $status_attachments = false;
	$show_for_affiliate = false; $show_for_merchant = false;
	$order_statuses_sites_all = false;
	$fields = $db->get_fields($table_prefix."order_statuses");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "status_php_lib") {
			$php_lib_field = true;
		} else if ($field_info["name"] == "mail_status_attachments") {
			$status_attachments = true;
		} else if ($field_info["name"] == "show_for_affiliate") {
			$show_for_affiliate = true;
		} else if ($field_info["name"] == "show_for_merchant") {
			$show_for_merchant = true;
		} else if ($field_info["name"] == "sites_all") {
			$order_statuses_sites_all = true;
		}
	}


	// check for payment system fields
	$active_start_time = false; $active_end_time = false; $active_week_days = false; 
	$ps_users_all = false; $ps_users_ids = false; $ps_users_types_ids = false; 
	$ps_fee_percent = false; $ps_fee_amount = false;
	$fields = $db->get_fields($table_prefix."payment_systems");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "active_start_time") {
			$active_start_time = true;
		} else if ($field_info["name"] == "active_end_time") {
			$active_end_time = true;
		} else if ($field_info["name"] == "active_week_days") {
			$active_week_days = true;
		} else if ($field_info["name"] == "users_all") {
			$ps_users_all = true;
		} else if ($field_info["name"] == "users_ids") {
			$ps_users_ids = true;
		} else if ($field_info["name"] == "users_types_ids") {
			$ps_users_types_ids = true;
		} else if ($field_info["name"] == "fee_percent") {
			$ps_fee_percent = true;
		} else if ($field_info["name"] == "fee_amount") {
			$ps_fee_amount = true;
		}
	}


	$parent_review_id = false; $review_type = false; 
	$reviews_email_notice = false; $reviews_notice_sent = false; $hide_review = false; 
	$reviews_ip_country_code = false; $verified_buyer = false; 
	$fields = $db->get_fields($table_prefix."reviews");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "parent_review_id") {
			$parent_review_id = true;
		} else if ($field_info["name"] == "review_type") {
			$review_type = true;
		} else if ($field_info["name"] == "email_notice") {
			$reviews_email_notice = true;
		} else if ($field_info["name"] == "notice_sent") {
			$reviews_notice_sent = true;
		} else if ($field_info["name"] == "hide_review") {
			$hide_review = true;
		} else if ($field_info["name"] == "ip_country_code") {
			$reviews_ip_country_code = true;
		} else if ($field_info["name"] == "verified_buyer") {
			$verified_buyer = true;
		}
	}

	if (comp_vers("5.0.1", $current_db_version) == 1)
	{
		// reviews block
		if (!$parent_review_id) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "reviews ADD parent_review_id INT(11) ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "reviews ADD parent_review_id INTEGER ",
				"postgre" => "ALTER TABLE " . $table_prefix . "reviews ADD parent_review_id INT4  ",
				"access"  => "ALTER TABLE " . $table_prefix . "reviews ADD parent_review_id INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = "CREATE INDEX ".$table_prefix."reviews_parent_review_id ON ".$table_prefix."reviews (parent_review_id)";
		}
		if (!$review_type) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "reviews ADD review_type TINYINT DEFAULT '1'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "reviews ADD review_type TINYINT DEFAULT '1'",
				"postgre" => "ALTER TABLE " . $table_prefix . "reviews ADD review_type SMALLINT DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "reviews ADD review_type BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "reviews SET review_type=1 ";
			$sqls[] = " CREATE INDEX ".$table_prefix."reviews_review_type ON ".$table_prefix."reviews (review_type)";
		}
		if (!$reviews_email_notice) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "reviews ADD email_notice TINYINT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "reviews ADD email_notice TINYINT ",
				"postgre" => "ALTER TABLE " . $table_prefix . "reviews ADD email_notice SMALLINT ",
				"access"  => "ALTER TABLE " . $table_prefix . "reviews ADD email_notice BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$reviews_notice_sent) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "reviews ADD notice_sent TINYINT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "reviews ADD notice_sent TINYINT ",
				"postgre" => "ALTER TABLE " . $table_prefix . "reviews ADD notice_sent SMALLINT ",
				"access"  => "ALTER TABLE " . $table_prefix . "reviews ADD notice_sent BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$hide_review) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "reviews ADD hide_review TINYINT DEFAULT '0' ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "reviews ADD hide_review TINYINT DEFAULT '0' ",
				"postgre" => "ALTER TABLE " . $table_prefix . "reviews ADD hide_review SMALLINT DEFAULT '0' ",
				"access"  => "ALTER TABLE " . $table_prefix . "reviews ADD hide_review BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " CREATE INDEX ".$table_prefix."reviews_hide_review ON ".$table_prefix."reviews (hide_review)";
		}
		if (!$reviews_ip_country_code) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "reviews ADD ip_country_code VARCHAR(4) ";
		}
		if (!$verified_buyer) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "reviews ADD verified_buyer TINYINT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "reviews ADD verified_buyer TINYINT ",
				"postgre" => "ALTER TABLE " . $table_prefix . "reviews ADD verified_buyer SMALLINT ",
				"access"  => "ALTER TABLE " . $table_prefix . "reviews ADD verified_buyer BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
		}
  
  
		if (!$reviews_emotions_table) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE va_reviews_emotions (
          `emotion_id` INT(11) NOT NULL AUTO_INCREMENT,
          `review_id` INT(11) NOT NULL DEFAULT '0',
          `user_id` INT(11) NOT NULL DEFAULT '0',
          `item_id` INT(11) DEFAULT '0',
          `emotion` TINYINT NOT NULL DEFAULT '0',
          `date_added` DATETIME
          ,KEY emotion (emotion)
          ,KEY item_id (item_id)
          ,PRIMARY KEY (emotion_id)
          ,KEY review_id (review_id)
          ,KEY user_id (user_id)
          ) DEFAULT CHARACTER SET=utf8mb4 ";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE va_reviews_emotions (
          emotion_id INTEGER NOT NULL IDENTITY,
          review_id INTEGER,
          user_id INTEGER,
          item_id INTEGER,
          emotion TINYINT,
          date_added DATETIME
          ,PRIMARY KEY (emotion_id))";
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE SEQUENCE seq_va_reviews_emotions START 1";
				$sqls[] = "CREATE TABLE va_reviews_emotions (
          emotion_id INT4 NOT NULL DEFAULT nextval('seq_va_reviews_emotions'),
          review_id INT4 NOT NULL default '0',
          user_id INT4 NOT NULL default '0',
          item_id INT4 default '0',
          emotion SMALLINT NOT NULL default '0',
          date_added TIMESTAMP
          ,PRIMARY KEY (emotion_id))";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE va_reviews_emotions (
          [emotion_id]  COUNTER  NOT NULL,
          [review_id] INTEGER,
          [user_id] INTEGER,
          [item_id] INTEGER,
          [emotion] BYTE,
          [date_added] DATETIME
          ,PRIMARY KEY (emotion_id))";
			} 
			if ($db_type != "mysql") {
				$sqls[] = "CREATE INDEX va_reviews_emotions_emotion ON va_reviews_emotions (emotion)";
				$sqls[] = "CREATE INDEX va_reviews_emotions_item_id ON va_reviews_emotions (item_id)";
				$sqls[] = "CREATE INDEX va_reviews_emotions_review_id ON va_reviews_emotions (review_id)";
				$sqls[] = "CREATE INDEX va_reviews_emotions_user_id ON va_reviews_emotions (user_id)";
			}
		}


		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.0.1");
	}


	// check allow_payment field in different tables
	$payment_allowed = false; $mail_to_field = false;
	$fields = $db->get_fields($table_prefix."order_statuses");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "payment_allowed") {
			$payment_allowed = true;
		} else if ($field_info["name"] == "mail_to") {
			$mail_to_field = true;
		}
	}

	if (comp_vers("5.0.2", $current_db_version) == 1)
	{
		// new order statuses fields
		if (!$php_lib_field) {
			$sql = "ALTER TABLE " . $table_prefix . "order_statuses ADD status_php_lib VARCHAR(255) ";
			$sqls[] = $sql;
		}
		if (!$show_for_affiliate || !$show_for_merchant) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD show_for_affiliate TINYINT DEFAULT '0'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD show_for_affiliate TINYINT DEFAULT '0'",
				"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD show_for_affiliate SMALLINT DEFAULT '0'",
				"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD show_for_affiliate BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD show_for_merchant TINYINT DEFAULT '0'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD show_for_merchant TINYINT DEFAULT '0'",
				"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD show_for_merchant SMALLINT DEFAULT '0'",
				"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD show_for_merchant BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "order_statuses SET show_for_merchant=show_for_user, show_for_affiliate=show_for_user ";
			$sqls[] = " UPDATE " . $table_prefix . "order_statuses SET show_for_user=1 ";
		}
		if (!$status_attachments) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD mail_status_attachments TINYINT DEFAULT '0'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD mail_status_attachments TINYINT DEFAULT '0'",
				"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD mail_status_attachments SMALLINT DEFAULT '0'",
				"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD mail_status_attachments BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
  
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD admin_status_attachments TINYINT DEFAULT '0'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD admin_status_attachments TINYINT DEFAULT '0'",
				"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD admin_status_attachments SMALLINT DEFAULT '0'",
				"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD admin_status_attachments BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
		}
		// end field check

		if (!$mail_to_field) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "order_statuses ADD mail_to VARCHAR(255) ";
		}
		if (!$payment_allowed) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD payment_allowed TINYINT DEFAULT '0' AFTER allow_user_cancel ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD payment_allowed TINYINT DEFAULT '0' ",
				"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD payment_allowed SMALLINT DEFAULT '0' ",
				"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD payment_allowed BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
  
			$sql = " SELECT status_id FROM " . $table_prefix . "order_statuses WHERE status_type='QUOTE_REQUEST' OR status_type='QUOTE' ";
			$db->query($sql);
			if (!$db->next_record()) {
				$sqls[] = "INSERT INTO ".$table_prefix."order_statuses (status_order,status_name,status_type,status_php_lib,admin_order_class,user_order_class,is_user_cancel,allow_user_cancel,payment_allowed,generate_invoice,user_invoice_activation,is_dispatch,is_list,show_for_user,paid_status,credit_note_action,stock_level_action,points_action,credit_action,commission_action,download_activation,download_notify,item_notify,mail_notify,mail_to,mail_from,mail_cc,mail_bcc,mail_reply_to,mail_return_path,mail_type,mail_pdf_invoice,mail_status_attachments,mail_subject,mail_body,sms_notify,sms_recipient,sms_originator,sms_message,merchant_notify,merchant_to,merchant_from,merchant_cc,merchant_bcc,merchant_reply_to,merchant_return_path,merchant_mail_type,merchant_subject,merchant_body,merchant_sms_notify,merchant_sms_recipient,merchant_sms_originator,merchant_sms_message,supplier_notify,supplier_to,supplier_from,supplier_cc,supplier_bcc,supplier_reply_to,supplier_return_path,supplier_mail_type,supplier_subject,supplier_body,supplier_sms_notify,supplier_sms_recipient,supplier_sms_originator,supplier_sms_message,affiliate_notify,affiliate_to,affiliate_from,affiliate_cc,affiliate_bcc,affiliate_reply_to,affiliate_return_path,affiliate_mail_type,affiliate_subject,affiliate_body,affiliate_sms_notify,affiliate_sms_recipient,affiliate_sms_originator,affiliate_sms_message,admin_notify,admin_to,admin_to_groups_ids,admin_from,admin_cc,admin_bcc,admin_reply_to,admin_return_path,admin_mail_type,admin_pdf_invoice,admin_status_attachments,admin_subject,admin_body,admin_sms_notify,admin_sms_recipient,admin_sms_originator,admin_sms_message,final_title,final_message,is_active,view_order_groups_all,view_order_groups_ids,set_status_groups_all,set_status_groups_ids,update_order_groups_all,update_order_groups_ids) VALUES (18 , 'STATUS_QUOTE_REQUEST_MSG' , 'QUOTE_REQUEST' , NULL , NULL , NULL , 0 , 0 , NULL , 0 , 0 , 0 , 1 , 1 , 0 , 0 , 0 , 0 , 0 , 0 , 0 , 0 , 0 , 0 , NULL , NULL , NULL , NULL , NULL , NULL , 0 , NULL , NULL , NULL , NULL , 0 , NULL , NULL , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , 0 , NULL , NULL , 0 , NULL , NULL , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , 0 , NULL , NULL , 0 , NULL , NULL , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , 0 , NULL , NULL , 0 , NULL , NULL , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , 0 , NULL , NULL , NULL , NULL , 0 , NULL , NULL , NULL , NULL , NULL , 1 , 1 , NULL , 1 , NULL , 1 , NULL )" ;
			}
  
			$sql = " SELECT status_id FROM " . $table_prefix . "order_statuses WHERE status_type='PARTIALLY_PAID'";
			$db->query($sql);
			if (!$db->next_record()) {
				$sqls[] = "INSERT INTO ".$table_prefix."order_statuses (status_order,status_name,status_type,status_php_lib,admin_order_class,user_order_class,is_user_cancel,allow_user_cancel,payment_allowed,generate_invoice,user_invoice_activation,is_dispatch,is_list,show_for_user,paid_status,credit_note_action,stock_level_action,points_action,credit_action,commission_action,download_activation,download_notify,item_notify,mail_notify,mail_to,mail_from,mail_cc,mail_bcc,mail_reply_to,mail_return_path,mail_type,mail_pdf_invoice,mail_status_attachments,mail_subject,mail_body,sms_notify,sms_recipient,sms_originator,sms_message,merchant_notify,merchant_to,merchant_from,merchant_cc,merchant_bcc,merchant_reply_to,merchant_return_path,merchant_mail_type,merchant_subject,merchant_body,merchant_sms_notify,merchant_sms_recipient,merchant_sms_originator,merchant_sms_message,supplier_notify,supplier_to,supplier_from,supplier_cc,supplier_bcc,supplier_reply_to,supplier_return_path,supplier_mail_type,supplier_subject,supplier_body,supplier_sms_notify,supplier_sms_recipient,supplier_sms_originator,supplier_sms_message,affiliate_notify,affiliate_to,affiliate_from,affiliate_cc,affiliate_bcc,affiliate_reply_to,affiliate_return_path,affiliate_mail_type,affiliate_subject,affiliate_body,affiliate_sms_notify,affiliate_sms_recipient,affiliate_sms_originator,affiliate_sms_message,admin_notify,admin_to,admin_to_groups_ids,admin_from,admin_cc,admin_bcc,admin_reply_to,admin_return_path,admin_mail_type,admin_pdf_invoice,admin_status_attachments,admin_subject,admin_body,admin_sms_notify,admin_sms_recipient,admin_sms_originator,admin_sms_message,final_title,final_message,is_active,view_order_groups_all,view_order_groups_ids,set_status_groups_all,set_status_groups_ids,update_order_groups_all,update_order_groups_ids) VALUES (19 , 'STATUS_PARTIALLY_PAID_MSG' , 'PARTIALLY_PAID' , NULL , NULL , NULL , 0 , 0 , NULL , 1 , 1 , 0 , 1 , 1 , 0 , 0 , 1 , 0 , 0 , 0 , 0 , 0 , 0 , 0 , NULL , NULL , NULL , NULL , NULL , NULL , 0 , NULL , NULL , NULL , NULL , 0 , NULL , NULL , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , 0 , NULL , NULL , 0 , NULL , NULL , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , 0 , NULL , NULL , 0 , NULL , NULL , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , 0 , NULL , NULL , 0 , NULL , NULL , NULL , 0 , NULL , NULL , NULL , NULL , NULL , NULL , NULL , 0 , NULL , NULL , NULL , NULL , 0 , NULL , NULL , NULL , NULL , NULL , 1 , 1 , NULL , 1 , NULL , 1 , NULL )";
			}
			$sqls[] = " UPDATE " . $table_prefix . "order_statuses SET payment_allowed=1 WHERE (paid_status=0 OR paid_status IS NULL) AND status_type<>'QUOTE_REQUEST' AND status_type<>'QUOTE' ";
		}
  
		$sql = " SELECT status_id FROM " . $table_prefix . "order_statuses WHERE status_type='QUOTE'";
		$db->query($sql);
		if ($db->next_record()) {
			$sqls[] = " UPDATE " . $table_prefix . "order_statuses SET status_type='QUOTE_REQUEST' WHERE status_type='QUOTE' ";
		}

		// add new orders permissions 
		$sql = " SELECT privilege_id FROM  ".$table_prefix."admin_privileges_settings WHERE block_name='add_order_products' ";
		$db->query($sql);
		if (!$db->next_record()) {
			$permissions = array("add_order_products", "update_order_products", "remove_order_products");
			$sql = " SELECT privilege_id FROM  " . $table_prefix . "admin_privileges_settings WHERE block_name='sales_orders' AND permission=1 ";
			$db->query($sql);
			while ($db->next_record()) {
				$privilege_id = $db->f("privilege_id");
				for ($i = 0; $i < sizeof($permissions); $i++) {
					$sql  = " DELETE FROM " . $table_prefix . "admin_privileges_settings ";
					$sql .= " WHERE privilege_id=" . $db->tosql($privilege_id, INTEGER);
					$sql .= " AND block_name=" . $db->tosql($permissions[$i], TEXT);
					$sqls[] = $sql;
					$sql  = " INSERT INTO " . $table_prefix . "admin_privileges_settings (privilege_id, block_name, permission) VALUES (";
					$sql .= $db->tosql($privilege_id, INTEGER) . ", '" . $permissions[$i] . "',1)";
					$sqls[] = $sql;
				}
			}
		}


		if (!$active_start_time) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "payment_systems ADD active_start_time INT(11) ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD active_start_time INTEGER ",
				"postgre" => "ALTER TABLE " . $table_prefix . "payment_systems ADD active_start_time INT4 ",
				"access"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD active_start_time INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$active_end_time) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "payment_systems ADD active_end_time INT(11) ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD active_end_time INTEGER ",
				"postgre" => "ALTER TABLE " . $table_prefix . "payment_systems ADD active_end_time INT4 ",
				"access"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD active_end_time INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$active_start_time) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "payment_systems ADD active_week_days INT(11) DEFAULT '127' ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD active_week_days INTEGER DEFAULT '127' ",
				"postgre" => "ALTER TABLE " . $table_prefix . "payment_systems ADD active_week_days INT4 DEFAULT '127'",
				"access"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD active_week_days INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "payment_systems SET active_week_days=127 ";
		}
  
		if (!$ps_users_all) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "payment_systems ADD users_all TINYINT DEFAULT '1'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD users_all TINYINT DEFAULT '1'",
				"postgre" => "ALTER TABLE " . $table_prefix . "payment_systems ADD users_all SMALLINT DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD users_all BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "payment_systems SET users_all=user_types_all ";
		}
		if (!$ps_users_ids) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "payment_systems ADD users_ids TEXT",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD users_ids TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "payment_systems ADD users_ids TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD users_ids LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
  
		if (!$ps_users_types_ids) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "payment_systems ADD users_types_ids TEXT",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD users_types_ids TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "payment_systems ADD users_types_ids TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD users_types_ids LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
  
			$ps_user_types = array();
			$sql = " SELECT * FROM " . $table_prefix . "payment_user_types ";			
			$db->query($sql);
			while ($db->next_record()) {
				$payment_id = $db->f("payment_id");
				$user_type_id = $db->f("user_type_id");
				if (!isset($ps_user_types[$payment_id])) { $ps_user_types[$payment_id] = array(); }
				$ps_user_types[$payment_id][] = $user_type_id;
			}
  
			foreach ($ps_user_types as $payment_id => $user_types) {
				$user_types = array_unique($user_types);
				$users_types_ids = implode(",", $user_types);
				$sql  = " UPDATE " . $table_prefix . "payment_systems ";
				$sql .= " SET users_types_ids=".$db->tosql($users_types_ids, TEXT);
				$sql .= " WHERE payment_id=".$db->tosql($payment_id, INTEGER);
				$sqls[] = $sql;
			}
		}

		// add new fields instead of old ones
		if (!$ps_fee_percent) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD fee_percent DOUBLE(16,2) ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "payment_systems ADD fee_percent FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "payment_systems ADD fee_percent FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "payment_systems ADD fee_percent FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$ps_fee_amount) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "payment_systems ADD fee_amount DOUBLE(16,2) ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "payment_systems ADD fee_amount FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "payment_systems ADD fee_amount FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "payment_systems ADD fee_amount FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
		}
  
		// move to the new two separate fields
		$sql = " SELECT payment_id, fee_type, processing_fee FROM ".$table_prefix."payment_systems WHERE processing_fee>0 ";
		$db->query($sql);
		while ($db->next_record()) {
			$payment_id = $db->f("payment_id");
			$fee_type = $db->f("fee_type");
			$processing_fee = $db->f("processing_fee");
			if ($fee_type == 1) {
				$sql = " UPDATE ".$table_prefix."payment_systems SET fee_percent=processing_fee WHERE payment_id=".$db->tosql($payment_id, INTEGER);
			} else {
				$sql = " UPDATE ".$table_prefix."payment_systems SET fee_amount=processing_fee WHERE payment_id=".$db->tosql($payment_id, INTEGER);
			}
			$sqls[] = $sql;
			$sql = " UPDATE ".$table_prefix."payment_systems SET fee_type=0,processing_fee=NULL WHERE payment_id=".$db->tosql($payment_id, INTEGER);
			$sqls[] = $sql;
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.0.2");
	}


	// check for button options for products_latest, products_top_sellers, products_recently_viewed blocks
	$buttons_blocks = array();
	$sql  = " SELECT block_id,block_code FROM ".$table_prefix."cms_blocks "; 
	$sql .= " WHERE block_code='products_latest' "; 
	$sql .= " OR block_code='products_top_sellers' "; 
	$sql .= " OR block_code='products_recently_viewed' "; 
	$sql .= " OR block_code='products_related' "; 
	$sql .= " OR block_code='cart_recommended_products' "; 
	$sql .= " OR block_code='products_offers' "; 
	$db->query($sql);
	while ($db->next_record()) {
		$block_id = $db->f("block_id");
		$block_code = $db->f("block_code");
		$buttons_blocks[$block_code] = $block_id;
	}
  
	// save some ids for further checks
	$top_sellers_block_id = isset($buttons_blocks["products_top_sellers"]) ? $buttons_blocks["products_top_sellers"] : "";
	$products_latest_block_id = isset($buttons_blocks["products_latest"]) ? $buttons_blocks["products_latest"] : "";
	$products_offers_block_id = isset($buttons_blocks["products_offers"]) ? $buttons_blocks["products_offers"] : "";

	// check product details block
	$prod_details_block_id = ""; 
	$sql  = " SELECT block_id FROM ".$table_prefix."cms_blocks "; 
	$sql .= " WHERE block_code='product_details' "; 
	$db->query($sql);
	if ($db->next_record()) {
		$prod_details_block_id = $db->f("block_id");
	}

	if (comp_vers("5.0.3", $current_db_version) == 1)
	{ 
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
			if ($block_code == "products_top_sellers") {
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
			// check if property with different name exists
			$add_property_id = ""; 
			if ($block_code == "products_offers") {
				$sql = " SELECT property_id FROM " .$table_prefix."cms_blocks_properties "; 
				$sql.= " WHERE block_id=".$db->tosql($block_id, INTEGER);
				$sql.= " AND variable_name='prod_offers_add_button' ";
				$db->query($sql);
				if ($db->next_record()) {
					$add_property_id = $db->f("property_id"); 
				}
			}
			if ($add_property_id) {
				$sql  = " UPDATE " .$table_prefix."cms_blocks_properties ";
				$sql .= " SET control_type='RADIOBUTTON', variable_name='bn_add', default_value='1' ";
				$sql .= " WHERE property_id=".$db->tosql($add_property_id, INTEGER);
				$db->query($sql);
  
				$sql  = " UPDATE " .$table_prefix."cms_blocks_settings ";
				$sql .= " SET variable_name='bn_add' ";
				$sql .= " WHERE variable_name='prod_offers_add_button' AND property_id=".$db->tosql($add_property_id, INTEGER);
				$db->query($sql);
  
				$sql  = " DELETE FROM " . $table_prefix . "cms_blocks_values ";
				$sql .= " WHERE property_id=".$db->tosql($add_property_id, INTEGER);
				$db->query($sql);
			} else {
				$property_id++; $property_order++;
				$add_property_id = $property_id;
				$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($add_property_id, $block_id, $property_order, 'ADD_TO_CART_MSG', 'RADIOBUTTON', NULL, NULL, 'bn_add', '1', 0)";
			}
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($add_property_id).", 1, 'YES_MSG', '', '1') ";
			$sqls[] = $sql;	
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($add_property_id).", 2, 'NO_MSG', '', '0') ";
			$sqls[] = $sql;	
  
			// add 'VIEW_CART_MSG' button option
			// check if property with different name exists
			$view_property_id = ""; 
			if ($block_code == "products_offers") {
				$sql = " SELECT property_id FROM " .$table_prefix."cms_blocks_properties "; 
				$sql.= " WHERE block_id=".$db->tosql($block_id, INTEGER);
				$sql.= " AND variable_name='prod_offers_view_button' ";
				$db->query($sql);
				if ($db->next_record()) {
					$view_property_id = $db->f("property_id"); 
				}
			}
			if ($view_property_id) {
				$sql  = " UPDATE " .$table_prefix."cms_blocks_properties ";
				$sql .= " SET control_type='RADIOBUTTON', variable_name='bn_view', default_value='0' ";
				$sql .= " WHERE property_id=".$db->tosql($view_property_id, INTEGER);
				$db->query($sql);
  
				$sql  = " UPDATE " .$table_prefix."cms_blocks_settings ";
				$sql .= " SET variable_name='bn_view' ";
				$sql .= " WHERE variable_name='prod_offers_view_button' AND property_id=".$db->tosql($view_property_id, INTEGER);
				$db->query($sql);
  
				$sql  = " DELETE FROM " . $table_prefix . "cms_blocks_values ";
				$sql .= " WHERE property_id=".$db->tosql($view_property_id, INTEGER);
				$db->query($sql);
			} else {
				$property_id++; $property_order++;
				$view_property_id = $property_id;
				$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'VIEW_CART_MSG', 'RADIOBUTTON', NULL, NULL, 'bn_view', '0', 0)";
			}
  
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($view_property_id).", 1, 'YES_MSG', '', '1') ";
			$sqls[] = $sql;	
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($view_property_id).", 2, 'NO_MSG', '', '0') ";
			$sqls[] = $sql;	
  
			// add 'Checkout' button option
			// check if property with different name exists
			$goto_property_id = ""; 
			if ($block_code == "products_offers") {
				$sql = " SELECT property_id FROM " .$table_prefix."cms_blocks_properties "; 
				$sql.= " WHERE block_id=".$db->tosql($block_id, INTEGER);
				$sql.= " AND variable_name='prod_offers_goto_button' ";
				$db->query($sql);
				if ($db->next_record()) {
					$goto_property_id = $db->f("property_id"); 
				}
			}
			if ($goto_property_id) {
				$sql  = " UPDATE " .$table_prefix."cms_blocks_properties SET control_type='RADIOBUTTON', variable_name='bn_goto', default_value='0' ";
				$sql .= " WHERE property_id=".$db->tosql($goto_property_id, INTEGER);
				$db->query($sql);
				$sql  = " UPDATE " .$table_prefix."cms_blocks_settings ";
				$sql .= " SET variable_name='bn_goto' ";
				$sql .= " WHERE variable_name='prod_offers_goto_button' AND property_id=".$db->tosql($goto_property_id, INTEGER);
				$db->query($sql);
				$sql  = " DELETE FROM " . $table_prefix . "cms_blocks_values WHERE property_id=".$db->tosql($goto_property_id, INTEGER);
				$db->query($sql);
			} else {
				$property_id++; $property_order++;
				$goto_property_id = $property_id;
				$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'GOTO_CHECKOUT_MSG', 'RADIOBUTTON', NULL, NULL, 'bn_goto', '0', 0)";
			}
  
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
			$wish_property_id = ""; 
			if ($block_code == "products_offers") {
				$sql = " SELECT property_id FROM " .$table_prefix."cms_blocks_properties "; 
				$sql.= " WHERE block_id=".$db->tosql($block_id, INTEGER);
				$sql.= " AND variable_name='prod_offers_wish_button' ";
				$db->query($sql);
				if ($db->next_record()) {
					$wish_property_id = $db->f("property_id"); 
				}
			}
			if ($wish_property_id) {
				$sql  = " UPDATE " .$table_prefix."cms_blocks_properties SET control_type='RADIOBUTTON', variable_name='bn_wish', default_value='0' ";
				$sql .= " WHERE property_id=".$db->tosql($wish_property_id, INTEGER);
				$db->query($sql);
				$sql  = " UPDATE " .$table_prefix."cms_blocks_settings ";
				$sql .= " SET variable_name='bn_wish' ";
				$sql .= " WHERE variable_name='prod_offers_wish_button' AND property_id=".$db->tosql($wish_property_id, INTEGER);
				$db->query($sql);
				$sql  = " DELETE FROM " . $table_prefix . "cms_blocks_values WHERE property_id=".$db->tosql($wish_property_id, INTEGER);
				$db->query($sql);
			} else {
				$property_id++; $property_order++;
				$wish_property_id = $property_id;
				$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $block_id, $property_order, 'WISHLIST_MSG', 'RADIOBUTTON', NULL, NULL, 'bn_wish', '0', 0)";
			}
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

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.0.3");
	}


	if (comp_vers("5.0.4", $current_db_version) == 1)
	{
		// check for new max_recs parameter for products latest block
		$latest_block_id = ""; 
		$sql  = " SELECT block_id FROM ".$table_prefix."cms_blocks "; 
		$sql .= " WHERE block_code='products_latest' "; 
		$latest_block_id = get_db_value($sql);
		if ($latest_block_id) {
			$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE variable_name='max_recs' "; 
			$sql .= " AND block_id=" . $db->tosql($latest_block_id, INTEGER); 
			$db->query($sql);
			if (!$db->next_record()) {
				$property_id++; 
				$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $latest_block_id, 1, 'MAXIMUM_RECORDS_MSG', 'TEXTBOX', NULL, NULL, 'max_recs', NULL, 0)";
			}
			// check for nav_pages variable 
			$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE variable_name='nav_pages' "; 
			$sql .= " AND block_id=" . $db->tosql($latest_block_id, INTEGER); 
			$db->query($sql);
			if (!$db->next_record()) {
				$property_id++; 
  
				$sql  = " SELECT MAX(property_order) FROM ".$table_prefix."cms_blocks_properties "; 
				$sql .= " WHERE block_id=".$db->tosql($latest_block_id, INTEGER); 
				$property_order = get_db_value($sql);
				$property_order++;
  
				$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $latest_block_id, $property_order, 'NUMBER_OF_PAGES_NAVIGATOR_MSG', 'TEXTBOX', NULL, NULL, 'nav_pages', '10', 0)";
			}
  
		}
  
		// check for new categories type option
		$prodcat_block_id = ""; $prodcat_property_id = "";
		$sql  = " SELECT block_id,block_code FROM ".$table_prefix."cms_blocks "; 
		$sql .= " WHERE block_code='categories_list' "; 
		$db->query($sql);
		if ($db->next_record()) {
			$prodcat_block_id = $db->f("block_id");
		}
		if ($prodcat_block_id) {
			$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE variable_name='categories_type' "; 
			$sql .= " AND block_id=" . $db->tosql($prodcat_block_id, INTEGER); 
			$db->query($sql);
			if ($db->next_record()) {
				$prodcat_property_id = $db->f("property_id");
			}
		}
		if ($prodcat_property_id) {
			$sql  = " SELECT value_id FROM ".$table_prefix."cms_blocks_values "; 
			$sql .= " WHERE variable_value='7' "; 
			$sql .= " AND property_id=" . $db->tosql($prodcat_property_id, INTEGER); 
			$db->query($sql);
			if (!$db->next_record()) {
				$value_id++;
				$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
				$sql .= intval($value_id).",".intval($prodcat_property_id).", 7, 'SUBCATEGORIES_POPUP_MSG', '', '7') ";
				$sqls[] = $sql;	
			}
		}
  

		// check random image option for products list block
		$prodlist_block_id = ""; $img_rnd_property_id = "";
		$sql  = " SELECT block_id FROM ".$table_prefix."cms_blocks "; 
		$sql .= " WHERE block_code='products_list' "; 
		$db->query($sql);
		if ($db->next_record()) {
			$prodlist_block_id = $db->f("block_id");
		}
		if ($prodlist_block_id) {
			$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE variable_name='random_image' "; 
			$sql .= " AND block_id=" . $db->tosql($prodlist_block_id, INTEGER); 
			$db->query($sql);
			if ($db->next_record()) {
				$img_rnd_property_id = $db->f("property_id");
			}
		}
		if ($prodlist_block_id && !$img_rnd_property_id) {
			// get max property_order for latest block
			$sql  = " SELECT MAX(property_order) FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE block_id=" . $db->tosql($prodlist_block_id, INTEGER); 
			$property_order = get_db_value($sql);
  
			// add 'Read more' button option
			$property_id++; $property_order++;
			$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,required) VALUES ($property_id, $prodlist_block_id, $property_order, 'RANDOM_IMAGE_MSG', 'RADIOBUTTON', NULL, NULL, 'random_image', 0)";
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($property_id).", 1, 'YES_MSG', '', '1') ";
			$sqls[] = $sql;	
			$value_id++;
			$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
			$sql .= intval($value_id).",".intval($property_id).", 2, 'NO_MSG', '', '0') ";
			$sqls[] = $sql;	
		}

		// check accessories_cols  option for products details block
		if ($prod_details_block_id) {
			$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE variable_name='accessories_cols' "; 
			$sql .= " AND block_id=" . $db->tosql($prod_details_block_id, INTEGER); 
			$db->query($sql);
			if (!$db->next_record()) {
				// get max property_order for latest block
				$sql  = " SELECT MAX(property_order) FROM ".$table_prefix."cms_blocks_properties "; 
				$sql .= " WHERE block_id=" . $db->tosql($prod_details_block_id, INTEGER); 
				$property_order = get_db_value($sql);
  
				$property_id++; $property_order++;
				$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,variable_name,required) VALUES ($property_id, $prod_details_block_id, $property_order, '{PROD_ACCESSORIES_MSG}: {NUMBER_OF_COLUMNS_MSG}', 'TEXTBOX', 'accessories_cols', 0)";
  
				$property_id++; $property_order++;
				$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,variable_name,required) VALUES ($property_id, $prod_details_block_id, $property_order, '{PROD_ACCESSORIES_MSG}: {IMAGE_TYPE_MSG}', 'LISTBOX', 'accessories_image_type', 0)";
				$value_id++;
				$sqls[] = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_value) VALUES (".intval($value_id).",".intval($property_id).", 1, 'DONT_SHOW_IMAGE_MSG', 'no') ";
				$value_id++;
				$sqls[] = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_value) VALUES (".intval($value_id).",".intval($property_id).", 2, 'IMAGE_TINY_MSG', 'tiny') ";
				$value_id++;
				$sqls[] = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_value) VALUES (".intval($value_id).",".intval($property_id).", 3, 'IMAGE_SMALL_MSG', 'small') ";
				$value_id++;
				$sqls[] = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_value) VALUES (".intval($value_id).",".intval($property_id).", 4, 'IMAGE_LARGE_MSG', 'large') ";
  
				$property_id++; $property_order++;
				$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,variable_name,required) VALUES ($property_id, $prod_details_block_id, $property_order, '{PROD_ACCESSORIES_MSG}: {DESCRIPTION_MSG}', 'LISTBOX', 'accessories_desc', 0)";
				$value_id++;
				$sqls[] = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_value) VALUES (".intval($value_id).",".intval($property_id).", 1, 'DONT_SHOW_DESC_MSG', 'no') ";
				$value_id++;
				$sqls[] = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_value) VALUES (".intval($value_id).",".intval($property_id).", 2, 'SHORT_DESCRIPTION_MSG', 'short') ";
				$value_id++;
				$sqls[] = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_value) VALUES (".intval($value_id).",".intval($property_id).", 3, 'FULL_DESCRIPTION_MSG', 'full') ";
				$value_id++;
				$sqls[] = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_value) VALUES (".intval($value_id).",".intval($property_id).", 4, 'HIGHLIGHTS_MSG', 'high') ";
				$value_id++;
				$sqls[] = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_value) VALUES (".intval($value_id).",".intval($property_id).", 5, 'SPECIAL_OFFER_MSG', 'spec') ";
  
				// add 'Read more' button option
				$property_id++; $property_order++;
				$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $prod_details_block_id, $property_order, '{PROD_ACCESSORIES_MSG}: {READ_MORE_MSG}', 'RADIOBUTTON', NULL, NULL, 'accessories_more', '0', 0)";
				$value_id++;
				$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
				$sql .= intval($value_id).",".intval($property_id).", 1, 'YES_MSG', '', '1') ";
				$sqls[] = $sql;	
				$value_id++;
				$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
				$sql .= intval($value_id).",".intval($property_id).", 2, 'NO_MSG', '', '0') ";
				$sqls[] = $sql;	
			}
		}

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

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.0.4");
	}


	if (comp_vers("5.0.5", $current_db_version) == 1)
	{
		if (!$orders_attachments) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE ".$table_prefix."orders_attachments (
          `attachment_id` INT(11) NOT NULL AUTO_INCREMENT,
          `order_id` INT(11) default '0',
          `event_id` INT(11) default '0',
          `admin_id` INT(11) default '0',
          `user_id` INT(11) default '0',
          `show_for_user` TINYINT default '1',
          `attachment_type` TINYINT default '1',
          `file_path` VARCHAR(255),
          `file_name` VARCHAR(255),
          `date_added` DATETIME
          ,KEY admin_id (admin_id)
          ,KEY event_id (event_id)
          ,KEY order_id (order_id)
          ,PRIMARY KEY (attachment_id)
          ,KEY user_id (user_id)
					) DEFAULT CHARACTER SET=utf8mb4";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE ".$table_prefix."orders_attachments (
            attachment_id INTEGER IDENTITY PRIMARY KEY,
            order_id INTEGER,
            event_id INTEGER,
            admin_id INTEGER,
            user_id INTEGER,
            show_for_user TINYINT,
            attachment_type TINYINT,
            file_path VARCHAR(255),
            file_name VARCHAR(255),
            date_added DATETIME
            ,PRIMARY KEY (attachment_id))";
  
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."orders_attachments START 1";
				$sqls[] = "CREATE TABLE ".$table_prefix."orders_attachments (
          attachment_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."orders_attachments'),
          order_id INT4 default '0',
          event_id INT4 default '0',
          admin_id INT4 default '0',
          user_id INT4 default '0',
          show_for_user SMALLINT default '1',
          attachment_type SMALLINT default '1',
          file_path VARCHAR(255),
          file_name VARCHAR(255),
          date_added TIMESTAMP
          ,PRIMARY KEY (attachment_id))";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE ".$table_prefix."orders_attachments (
          [attachment_id]  COUNTER  NOT NULL,
          [order_id] INTEGER,
          [event_id] INTEGER,
          [admin_id] INTEGER,
          [user_id] INTEGER,
          [show_for_user] BYTE,
          [attachment_type] BYTE,
          [file_path] VARCHAR(255),
          [file_name] VARCHAR(255),
          [date_added] DATETIME
          ,PRIMARY KEY (attachment_id))";
			} 
			if ($db_type != "mysql") {
				$sqls[] = "CREATE INDEX ".$table_prefix."orders_attachments_admin_id ON ".$table_prefix."orders_attachments (admin_id)";
				$sqls[] = "CREATE INDEX ".$table_prefix."orders_attachments_event_id ON ".$table_prefix."orders_attachments (event_id)";
				$sqls[] = "CREATE INDEX ".$table_prefix."orders_attachments_order_id ON ".$table_prefix."orders_attachments (order_id)";
				$sqls[] = "CREATE INDEX ".$table_prefix."orders_attachments_user_id ON ".$table_prefix."orders_attachments (user_id)";
			}
		}
  
		if (!$shipping_companies) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE ".$table_prefix."shipping_companies (
          `shipping_company_id` INT(11) NOT NULL AUTO_INCREMENT,
          `company_name` VARCHAR(255),
          `company_url` VARCHAR(255),
          `image_small` VARCHAR(255),
          `image_large` VARCHAR(255),
          `short_description` TEXT,
          `full_description` TEXT
          ,PRIMARY KEY (shipping_company_id)
          ) DEFAULT CHARACTER SET=utf8mb4 ";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE ".$table_prefix."shipping_companies (
          shipping_company_id INTEGER IDENTITY PRIMARY KEY,
          company_name VARCHAR(255),
          company_url VARCHAR(255),
          image_small VARCHAR(255),
          image_large VARCHAR(255),
          short_description TEXT,
          full_description TEXT
          ,PRIMARY KEY (shipping_company_id))";
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."shipping_companies START 1";
				$sqls[] = "CREATE TABLE ".$table_prefix."shipping_companies (
          shipping_company_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."shipping_companies'),
          company_name VARCHAR(255),
          company_url VARCHAR(255),
          image_small VARCHAR(255),
          image_large VARCHAR(255),
          short_description TEXT,
          full_description TEXT
          ,PRIMARY KEY (shipping_company_id))";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE ".$table_prefix."shipping_companies (
          [shipping_company_id]  COUNTER  NOT NULL,
          [company_name] VARCHAR(255),
          [company_url] VARCHAR(255),
          [image_small] VARCHAR(255),
          [image_large] VARCHAR(255),
          [short_description] LONGTEXT,
          [full_description] LONGTEXT
          ,PRIMARY KEY (shipping_company_id))";
			} 
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "orders_shipments ADD shipping_company_id INT(11) ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "orders_shipments ADD shipping_company_id INTEGER ",
				"postgre" => "ALTER TABLE " . $table_prefix . "orders_shipments ADD shipping_company_id INT4  ",
				"access"  => "ALTER TABLE " . $table_prefix . "orders_shipments ADD shipping_company_id INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
		}
  
		if (!$conditions_table) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE ".$table_prefix."conditions (
          `condition_id` INT(11) NOT NULL AUTO_INCREMENT,
          `condition_name` VARCHAR(50),
          `sort_order` VARCHAR(50)
          ,PRIMARY KEY (condition_id)
          ) DEFAULT CHARACTER SET=utf8mb4 ";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE ".$table_prefix."conditions (
          condition_id INTEGER IDENTITY PRIMARY KEY,
          condition_name VARCHAR(50),
          sort_order VARCHAR(50)
          ,PRIMARY KEY (condition_id))";
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."conditions START 1";
				$sqls[] = "CREATE TABLE ".$table_prefix."conditions (
          condition_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."conditions'),
          condition_name VARCHAR(50),
          sort_order VARCHAR(50)
          ,PRIMARY KEY (condition_id))";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE ".$table_prefix."conditions (
          [condition_id]  COUNTER  NOT NULL,
          [condition_name] VARCHAR(50),
          [sort_order] VARCHAR(50)
          ,PRIMARY KEY (condition_id))";
			} 
		}
  
		// check orders settings menu
		if ($admin_menu_id) {
			$sql  = " SELECT menu_item_id ";
			$sql .= " FROM ".$table_prefix."menus_items ";
			$sql .= " WHERE menu_id=".$db->tosql($admin_menu_id, INTEGER);
			$sql .= " AND menu_code='orders-settings' ";
		  $orders_settings_id = get_db_value($sql);
  
			if ($orders_settings_id) {
				$sql  = " SELECT menu_item_id ";
				$sql .= " FROM ".$table_prefix."menus_items ";
				$sql .= " WHERE menu_id=".$db->tosql($admin_menu_id, INTEGER);
				$sql .= " AND menu_code='shipping-companies' ";
			  $shipping_companies_id = get_db_value($sql);
				if (!$shipping_companies_id) {
					$sqls[] = " UPDATE " . $table_prefix . "menus_items SET menu_order=menu_order+1 WHERE menu_order>=3 AND parent_menu_item_id=".$db->tosql($orders_settings_id, INTEGER);
  
					$sql  = "INSERT INTO " . $table_prefix . "menus_items (menu_id,parent_menu_item_id,menu_order,menu_title,menu_url,menu_code,admin_access) VALUES (";
					$sql .= intval($admin_menu_id).",".intval($orders_settings_id).", 3, 'SHIPPING_COMPANIES_MSG', 'admin_shipping_companies.php', 'shipping-companies', 'all') ";
					$sqls[] = $sql;	
				}
			}
		}


		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.0.5");
	}


	$ignore_items_shipping_cost = false; 
	$min_discounted_cost_field = false; 
	$max_discounted_cost_field = false;
	$st_image_small = false; $st_image_small_alt = false;  
	$st_image_large = false; $st_image_large_alt = false;
	$fields = $db->get_fields($table_prefix."shipping_types");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "ignore_items_shipping_cost") {
			$ignore_items_shipping_cost = true;
		} else if ($field_info["name"] == "min_discounted_cost") {
			$min_discounted_cost_field = true;
		} else if ($field_info["name"] == "max_discounted_cost") {
			$max_discounted_cost_field = true;
		} else if ($field_info["name"] == "image_small") {
			$st_image_small = true;
		} else if ($field_info["name"] == "image_small_alt") {
			$st_image_small_alt = true;
		} else if ($field_info["name"] == "image_large") {
			$st_image_large = true;
		} else if ($field_info["name"] == "image_large_alt") {
			$st_image_large_alt = true;
		}
	}


	// check fields for categories table
	$categories_items_types_all = false; 
	$categories_items_types_ids = false; 
	$categories_allowed_types_products = false; 
	$fields = $db->get_fields($table_prefix."categories");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "items_types_all") {
			$categories_items_types_all = true;
		} else if ($field_info["name"] == "items_types_ids") {
			$categories_items_types_ids = true;
		} else if ($field_info["name"] == "allowed_types_products") {
			$categories_allowed_types_products = true;
		}
	}


	if (comp_vers("5.0.6", $current_db_version) == 1)
	{
		// check for hide fields update in items table
		$fields = $db->get_fields($table_prefix."items");
		$hide_fields = array(
			"hide_view_list" => false, "hide_view_details" => false, "hide_view_table" => false, "hide_view_grid" => false,
			"hide_checkout_list" => false, "hide_checkout_details" => false, "hide_checkout_table" => false, "hide_checkout_grid" => false,
			"hide_wishlist_list" => false, "hide_wishlist_details" => false, "hide_wishlist_table" => false, "hide_wishlist_grid" => false,
			"hide_more_list" => false, "hide_more_table" => false, "hide_more_grid" => false,
			"hide_free_shipping_list" => false, "hide_free_shipping_table" => false, "hide_free_shipping_grid" => false, "hide_free_shipping_details" => false,
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
					$sql = "ALTER TABLE ".$table_prefix."items ADD ".$field_name." TINYINT ";
				} else if ($db->DBType == "sqlsrv") {
					$sql = "ALTER TABLE ".$table_prefix."items ADD ".$field_name." TINYINT ";
				} else if ($db->DBType == "access") {
					$sql = "ALTER TABLE ".$table_prefix."items ADD ".$field_name." SMALLINT ";
				} else {
					$sql = "ALTER TABLE ".$table_prefix."items ADD ".$field_name." BYTE ";
				}
				$sqls[] = $sql;	
			}
		}
		// end field check

		// check actual_weight field in different tables 
		// check condition, review, question fields in items table
		$actual_weight_field = false; $condition_field = false; 
		$items_reviews_field = false; $items_questions_field = false; 
		$fields = $db->get_fields($table_prefix."items");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "actual_weight") {
				$actual_weight_field = true;
			} else if ($field_info["name"] == "condition_id") {
				$condition_field = true;
			} else if ($field_info["name"] == "allow_reviews") {
				$items_reviews_field = true; 
			} else if ($field_info["name"] == "allow_questions") {
				$items_questions_field = true; 
			}
		}
		if (!$actual_weight_field) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "items ADD actual_weight DOUBLE(16,4) AFTER weight ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "items ADD actual_weight FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "items ADD actual_weight FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "items ADD actual_weight FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$condition_field) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "items ADD condition_id INT(11) ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "items ADD condition_id INTEGER ",
				"postgre" => "ALTER TABLE " . $table_prefix . "items ADD condition_id INT4  ",
				"access"  => "ALTER TABLE " . $table_prefix . "items ADD condition_id INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$items_reviews_field) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "items ADD allow_reviews TINYINT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "items ADD allow_reviews TINYINT ",
				"postgre" => "ALTER TABLE " . $table_prefix . "items ADD allow_reviews SMALLINT ",
				"access"  => "ALTER TABLE " . $table_prefix . "items ADD allow_reviews BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$items_questions_field) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "items ADD allow_questions TINYINT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "items ADD allow_questions TINYINT ",
				"postgre" => "ALTER TABLE " . $table_prefix . "items ADD allow_questions SMALLINT ",
				"access"  => "ALTER TABLE " . $table_prefix . "items ADD allow_questions BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
		}
  
  
		$actual_weight_field = false; 
		$fields = $db->get_fields($table_prefix."items_properties_values");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "actual_weight") {
				$actual_weight_field = true;
			}
		}
		if (!$actual_weight_field) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "items_properties_values ADD actual_weight DOUBLE(16,4) AFTER additional_weight ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "items_properties_values ADD actual_weight FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "items_properties_values ADD actual_weight FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "items_properties_values ADD actual_weight FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
		}
  
		$actual_weight_field = false; 
		$fields = $db->get_fields($table_prefix."order_custom_values");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "actual_weight") {
				$actual_weight_field = true;
			}
		}
		if (!$actual_weight_field) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "order_custom_values ADD actual_weight DOUBLE(16,4) AFTER property_weight ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "order_custom_values ADD actual_weight FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "order_custom_values ADD actual_weight FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "order_custom_values ADD actual_weight FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
		}
  
		$actual_weight_field = false; 
		$orders_middle_name = false; $orders_address3 = false;
		$orders_shipped_date = false; $orders_language_code = false;
		$fields = $db->get_fields($table_prefix."orders");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "actual_weight_total") {
				$actual_weight_field = true;
			} else if ($field_info["name"] == "middle_name") {
				$orders_middle_name = true;
			} else if ($field_info["name"] == "address3") {
				$orders_address3 = true;
			} else if ($field_info["name"] == "order_shipped_date") {
				$orders_shipped_date = true;
			} else if ($field_info["name"] == "language_code") {
				$orders_language_code = true;
			}
		}
		if (!$actual_weight_field) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "orders ADD actual_weight_total DOUBLE(16,4) AFTER weight_total ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "orders ADD actual_weight_total FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "orders ADD actual_weight_total FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "orders ADD actual_weight_total FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$orders_middle_name) {
			if ($db_type == "mysql") {
				$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD middle_name VARCHAR(64) AFTER first_name ";
				$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD delivery_middle_name VARCHAR(64) AFTER delivery_first_name ";
				$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD bill_middle_name VARCHAR(64) AFTER bill_first_name ";
			} else {
				$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD middle_name VARCHAR(64) ";
				$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD delivery_middle_name VARCHAR(64) ";
				$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD bill_middle_name VARCHAR(64) ";
			}
		}
		if (!$orders_address3) {
			if ($db_type == "mysql") {
				$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD address3 VARCHAR(64) AFTER address2 ";
				$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD delivery_address3 VARCHAR(64) AFTER address2 ";
				$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD bill_address3 VARCHAR(64) AFTER address2 ";
			} else {
				$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD address3 VARCHAR(64) ";
				$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD delivery_address3 VARCHAR(64) ";
				$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD bill_address3 VARCHAR(64) ";
			}
		}
		if (!$orders_shipped_date) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "orders ADD order_shipped_date DATETIME AFTER order_paid_date ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "orders ADD order_shipped_date DATETIME ",
				"postgre" => "ALTER TABLE " . $table_prefix . "orders ADD order_shipped_date TIMESTAMP ",
				"access"  => "ALTER TABLE " . $table_prefix . "orders ADD order_shipped_date DATETIME ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = "CREATE INDEX ".$table_prefix."orders_order_shipped_date ON ".$table_prefix."orders (order_shipped_date)";
		}
		if (!$orders_language_code) {
			if ($db_type == "mysql") {
				$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD language_code VARCHAR(2) AFTER site_id ";
			} else {
				$sqls[] = "ALTER TABLE " . $table_prefix . "orders ADD language_code VARCHAR(2) ";
			}
		}
  
		$actual_weight_field = false; 
		$fields = $db->get_fields($table_prefix."orders_items");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "actual_weight") {
				$actual_weight_field = true;
			}
		}
		if (!$actual_weight_field) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "orders_items ADD actual_weight DOUBLE(16,4) AFTER weight ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "orders_items ADD actual_weight FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "orders_items ADD actual_weight FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "orders_items ADD actual_weight FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
		}
  
		$actual_weight_field = false; 
		$fields = $db->get_fields($table_prefix."orders_items_properties");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "actual_weight") {
				$actual_weight_field = true;
			}
		}
		if (!$actual_weight_field) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "orders_items_properties ADD actual_weight DOUBLE(16,4) AFTER additional_weight ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "orders_items_properties ADD actual_weight FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "orders_items_properties ADD actual_weight FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "orders_items_properties ADD actual_weight FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
		}
  
		$actual_weight_field = false; 
		$fields = $db->get_fields($table_prefix."orders_properties");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "actual_weight") {
				$actual_weight_field = true;
			}
		}
		if (!$actual_weight_field) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "orders_properties ADD actual_weight DOUBLE(16,4) AFTER property_weight ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "orders_properties ADD actual_weight FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "orders_properties ADD actual_weight FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "orders_properties ADD actual_weight FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
		}
  
		$actual_weight_field = false; 
		$fields = $db->get_fields($table_prefix."orders_shipments");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "actual_goods_weight") {
				$actual_weight_field = true;
			}
		}
		if (!$actual_weight_field) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "orders_shipments ADD actual_goods_weight DOUBLE(16,4) AFTER goods_weight ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "orders_shipments ADD actual_goods_weight FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "orders_shipments ADD actual_goods_weight FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "orders_shipments ADD actual_goods_weight FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
		}

		// shipping types fields
		if (!$ignore_items_shipping_cost) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "shipping_types ADD ignore_items_shipping_cost TINYINT DEFAULT '0'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "shipping_types ADD ignore_items_shipping_cost TINYINT DEFAULT '0'",
				"postgre" => "ALTER TABLE " . $table_prefix . "shipping_types ADD ignore_items_shipping_cost SMALLINT DEFAULT '0'",
				"access"  => "ALTER TABLE " . $table_prefix . "shipping_types ADD ignore_items_shipping_cost BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$max_discounted_cost_field) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "shipping_types ADD max_discounted_cost DOUBLE(16,2) AFTER max_goods_cost ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "shipping_types ADD max_discounted_cost FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "shipping_types ADD max_discounted_cost FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "shipping_types ADD max_discounted_cost FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$min_discounted_cost_field) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "shipping_types ADD min_discounted_cost DOUBLE(16,2) AFTER max_goods_cost ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "shipping_types ADD min_discounted_cost FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "shipping_types ADD min_discounted_cost FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "shipping_types ADD min_discounted_cost FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$st_image_small) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "shipping_types ADD image_small VARCHAR(255) ";
		}
		if (!$st_image_small_alt) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "shipping_types ADD image_small_alt VARCHAR(255) ";
		}
		if (!$st_image_large) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "shipping_types ADD image_large VARCHAR(255) ";
		}
		if (!$st_image_large_alt) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "shipping_types ADD image_large_alt VARCHAR(255) ";
		}

		// check order field in shipping_modules
		$shipping_module_order_field = false; 
		$fields = $db->get_fields($table_prefix."shipping_modules");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "module_order") {
				$shipping_module_order_field = true;
			}
		}
		if (!$shipping_module_order_field) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "shipping_modules ADD module_order INT(11) DEFAULT '1' ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "shipping_modules ADD module_order INTEGER DEFAULT '1' ",
				"postgre" => "ALTER TABLE " . $table_prefix . "shipping_modules ADD module_order INT4 DEFAULT '1' ",
				"access"  => "ALTER TABLE " . $table_prefix . "shipping_modules ADD module_order INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "shipping_modules SET module_order=1 ";
		}

		// category item types
		if (!$categories_items_types_all) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "categories ADD items_types_all TINYINT DEFAULT '1'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "categories ADD items_types_all TINYINT DEFAULT '1'",
				"postgre" => "ALTER TABLE " . $table_prefix . "categories ADD items_types_all SMALLINT DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "categories ADD items_types_all BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			if ($categories_allowed_types_products) {
				$sqls[] = " UPDATE " . $table_prefix . "categories SET items_types_all=0 ";
				$sqls[] = " UPDATE " . $table_prefix . "categories SET items_types_all=1 WHERE allowed_types_products IS NULL OR allowed_types_products='' ";
			} else {
				$sqls[] = " UPDATE " . $table_prefix . "categories SET items_types_all=1 ";
			}
		}
		if (!$categories_items_types_ids) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "categories ADD items_types_ids TEXT",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "categories ADD items_types_ids TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "categories ADD items_types_ids TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "categories ADD items_types_ids LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
			if ($categories_allowed_types_products) {
				$sqls[] = " UPDATE " . $table_prefix . "categories SET items_types_ids=allowed_types_products ";
			}
		}


		// check for categories_columns fields
		$category_column_class = false; 
		$fields = $db->get_fields($table_prefix."categories_columns");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "column_class") {
				$category_column_class = true;
			} 
		}
		if (!$category_column_class) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "categories_columns ADD column_class VARCHAR(64) ";
		}

		// category tabs
		if (!$categories_tabs_table) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE va_categories_tabs (
          `tab_id` INT(11) NOT NULL AUTO_INCREMENT,
          `category_id` INT(11) DEFAULT '0',
          `tab_order` INT(11) DEFAULT '1',
          `tab_title` VARCHAR(50),
          `tab_desc` TEXT,
          `hide_tab` TINYINT DEFAULT '0'
          ,KEY item_id (category_id)
          ,PRIMARY KEY (tab_id)
          ) DEFAULT CHARACTER SET=utf8mb4 ";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE va_categories_tabs (
          tab_id INTEGER NOT NULL IDENTITY,
          category_id INTEGER,
          tab_order INTEGER,
          tab_title VARCHAR(50),
          tab_desc TEXT,
          hide_tab TINYINT
          ,PRIMARY KEY (tab_id))";
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE SEQUENCE seq_va_categories_tabs START 1";
				$sqls[] = "CREATE TABLE va_categories_tabs (
          tab_id INT4 NOT NULL DEFAULT nextval('seq_va_categories_tabs'),
          category_id INT4 default '0',
          tab_order INT4 default '1',
          tab_title VARCHAR(50),
          tab_desc TEXT,
          hide_tab SMALLINT default '0'
          ,PRIMARY KEY (tab_id))";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE va_categories_tabs (
          [tab_id]  COUNTER  NOT NULL,
          [category_id] INTEGER,
          [tab_order] INTEGER,
          [tab_title] VARCHAR(50),
          [tab_desc] LONGTEXT,
          [hide_tab] BYTE
          ,PRIMARY KEY (tab_id))";
			} 
			if ($db_type != "mysql") {
				$sqls[] = "CREATE INDEX va_categories_tabs_item_id ON va_categories_tabs (category_id)";
			}
		}


		// filter settings
		$list_field_image = false; 
		$fields = $db->get_fields($table_prefix."filters_properties");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "list_field_image") {
				$list_field_image= true;
			}
		}
		if (!$list_field_image) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "filters_properties ADD list_field_image VARCHAR(64) ";
		}
  
		$list_image = false; 
		$fields = $db->get_fields($table_prefix."filters_properties_values");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "list_image") {
				$list_image = true;
			}
		}
		if (!$list_image) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "filters_properties_values ADD list_image VARCHAR(255) ";
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.0.6");
	}


	$articles_authors_role_field = false; 
	$fields = $db->get_fields($table_prefix."articles_authors");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "role_id") {
			$articles_authors_role_field = true;
		} 
	}
	$articles_sites_all_field = false; $articles_is_keywords_field = false; 
	$fields = $db->get_fields($table_prefix."articles");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "sites_all") {
			$articles_sites_all_field = true;
		} else if ($field_info["name"] == "is_keywords") {
			$articles_is_keywords_field = true;
		} 
	}

	if (comp_vers("5.0.7", $current_db_version) == 1)
	{
		// articles related update 
		if (!$authors_languages) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE ".$table_prefix."authors_languages (
          `author_id` INT(11) NOT NULL default '0',
          `language_code` VARCHAR(2) NOT NULL
          ,PRIMARY KEY (author_id,language_code)
					) DEFAULT CHARACTER SET=utf8mb4";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE ".$table_prefix."authors_languages (
          author_id INTEGER,
          language_code VARCHAR(2)
          ,PRIMARY KEY (author_id,language_code))";
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE TABLE ".$table_prefix."authors_languages (
          author_id INT4 NOT NULL default '0',
          language_code VARCHAR(2) NOT NULL
          ,PRIMARY KEY (author_id,language_code))";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE ".$table_prefix."authors_languages (
          [author_id] INTEGER NOT NULL,
          [language_code] VARCHAR(2) NOT NULL
          ,PRIMARY KEY (author_id,language_code))";
			} 
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "authors ADD languages_all TINYINT NOT NULL DEFAULT '1'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "authors ADD languages_all TINYINT NOT NULL DEFAULT '1'",
				"postgre" => "ALTER TABLE " . $table_prefix . "authors ADD languages_all SMALLINT NOT NULL DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "authors ADD languages_all BYTE NOT NULL ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "authors SET languages_all=1 ";
		}
  
		$extra_name_field = false; 
		$fields = $db->get_fields($table_prefix."authors");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "extra_name") {
				$extra_name_field = true;
			}
		}
		
		if (!$extra_name_field) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "authors ADD extra_name VARCHAR(64) ";
			$sqls[] = "ALTER TABLE " . $table_prefix . "authors ADD name_second VARCHAR(2) ";
			$sqls[] = "ALTER TABLE " . $table_prefix . "authors ADD other_second VARCHAR(2) ";
			$sqls[] = "ALTER TABLE " . $table_prefix . "authors ADD extra_first VARCHAR(2) ";
			$sqls[] = "ALTER TABLE " . $table_prefix . "authors ADD extra_second VARCHAR(2) ";
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "authors ADD author_country_id INT(11) ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "authors ADD author_country_id INTEGER ",
				"postgre" => "ALTER TABLE " . $table_prefix . "authors ADD author_country_id INT4  ",
				"access"  => "ALTER TABLE " . $table_prefix . "authors ADD author_country_id INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = "ALTER TABLE " . $table_prefix . "authors ADD author_country_code VARCHAR(4) ";
  
			$sqls[] = "CREATE INDEX ".$table_prefix."authors_name_second ON ".$table_prefix."authors (name_second)";
			$sqls[] = "CREATE INDEX ".$table_prefix."authors_other_second ON ".$table_prefix."authors (other_second)";
			$sqls[] = "CREATE INDEX ".$table_prefix."authors_extra_first ON ".$table_prefix."authors (extra_first)";
			$sqls[] = "CREATE INDEX ".$table_prefix."authors_extra_second ON ".$table_prefix."authors (extra_second)";
			$sqls[] = "CREATE INDEX ".$table_prefix."authors_author_country_id ON ".$table_prefix."authors (author_country_id)";
			$sqls[] = "CREATE INDEX ".$table_prefix."authors_author_country_code ON ".$table_prefix."authors (author_country_code)";
		}

		// check authors roles 
		if (!$authors_roles) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE ".$table_prefix."authors_roles (
          `role_id` INT(11) NOT NULL AUTO_INCREMENT,
          `role_code` VARCHAR(8),
          `role_name` VARCHAR(255)
          ,PRIMARY KEY (role_id)
          ) DEFAULT CHARACTER SET=utf8mb4 ";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE ".$table_prefix."authors_roles (
          role_id INTEGER IDENTITY PRIMARY KEY,
          role_code VARCHAR(8),
          role_name VARCHAR(255)
          ,PRIMARY KEY (role_id))";
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."authors_roles START 1";
				$sqls[] = "CREATE TABLE ".$table_prefix."authors_roles (
          role_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."authors_roles'),
          role_code VARCHAR(8),
          role_name VARCHAR(255)
          ,PRIMARY KEY (role_id))";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE ".$table_prefix."authors_roles (
          [role_id]  COUNTER  NOT NULL,
          [role_code] VARCHAR(8),
          [role_name] VARCHAR(255)
          ,PRIMARY KEY (role_id))";
			} 
		}
		if (!$articles_sites) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE ".$table_prefix."articles_sites (
          `article_id` INT(11) NOT NULL default '0',
          `site_id` INT(11) NOT NULL default '0'
          ,PRIMARY KEY (article_id,site_id)
          ) DEFAULT CHARACTER SET=utf8mb4 ";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE ".$table_prefix."articles_sites (
          article_id INTEGER,
          site_id INTEGER
          ,PRIMARY KEY (article_id,site_id))";
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE TABLE ".$table_prefix."articles_sites (
          article_id INT4 NOT NULL default '0',
          site_id INT4 NOT NULL default '0'
          ,PRIMARY KEY (article_id,site_id))";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE ".$table_prefix."articles_sites (
          [article_id] INTEGER NOT NULL,
          [site_id] INTEGER NOT NULL
          ,PRIMARY KEY (article_id,site_id))";
			} 
		}

		if (!$articles_authors_role_field) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "articles_authors ADD role_id INT(11) ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "articles_authors ADD role_id INTTEGER ",
				"postgre" => "ALTER TABLE " . $table_prefix . "articles_authors ADD role_id INT4 ",
				"access"  => "ALTER TABLE " . $table_prefix . "articles_authors ADD role_id INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$articles_sites_all_field) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "articles ADD sites_all TINYINT DEFAULT '1'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "articles ADD sites_all TINYINT DEFAULT '1'",
				"postgre" => "ALTER TABLE " . $table_prefix . "articles ADD sites_all SMALLINT DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "articles ADD sites_all BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "articles SET sites_all=1 ";
		}
  
		if (!$favorite_lists) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE ".$table_prefix."favorite_lists (
          `list_id` INT(11) NOT NULL AUTO_INCREMENT,
          `user_id` INT(11) default '0',
          `list_type` TINYINT default '0',
          `list_order` INT(11) default '1',
          `list_name` VARCHAR(255),
          `list_desc` TEXT,
          `play_type` TINYINT,
          `lyrics_mode` TINYINT,
          `shuffle_type` TINYINT,
          `end_action` TINYINT 
          ,PRIMARY KEY (list_id)
          ) DEFAULT CHARACTER SET=utf8mb4 ";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE ".$table_prefix."favorite_lists (
          list_id INTEGER IDENTITY PRIMARY KEY,
          user_id INTEGER,
          list_type TINYINT,
          list_order INTEGER,
          list_name VARCHAR(255),
          list_desc TEXT,
          play_type TINYINT,
          lyrics_mode TINYINT,
          shuffle_type TINYINT,
          end_action TINYINT
          ,PRIMARY KEY (list_id))";
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."favorite_lists START 1";
				$sqls[] = "CREATE TABLE ".$table_prefix."favorite_lists (
          list_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."favorite_lists'),
          user_id INT4 default '0',
          list_type SMALLINT default '0',
          list_order INT4 default '1',
          list_name VARCHAR(255),
          list_desc TEXT,
          play_type SMALLINT,
          lyrics_mode SMALLINT,
          shuffle_type SMALLINT,
          end_action SMALLINT 
          ,PRIMARY KEY (list_id))";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE ".$table_prefix."favorite_lists (
          [list_id]  COUNTER  NOT NULL,
          [user_id] INTEGER,
          [list_type] BYTE,
          [list_order] INTEGER,
          [list_name] VARCHAR(255),
          [list_desc] LONGTEXT,
          [play_type] BYTE,
          [lyrics_mode ] BYTE,
          [shuffle_type] BYTE,        
          [end_action] BYTE
          ,PRIMARY KEY (list_id))";
			} 
		}
		if (!$favorite_articles) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE ".$table_prefix."favorite_articles (
          `favorite_id` INT(11) NOT NULL AUTO_INCREMENT,
          `article_id` INT(11) default '0',
          `list_id` INT(11) default '0',
          `user_id` INT(11) default '0',
          `favorite_order` INT(11) default '1',
          `favorite_name` VARCHAR(255),
          `play_type` TINYINT default '0'
          ,PRIMARY KEY (favorite_id)
          ) DEFAULT CHARACTER SET=utf8mb4 ";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE ".$table_prefix."favorite_articles (
          favorite_id INTEGER IDENTITY PRIMARY KEY,
          article_id INTEGER,
          list_id INTEGER,
          user_id INTEGER,
          favorite_order INTEGER,
          favorite_name VARCHAR(255),
          play_type TINYINT
          ,PRIMARY KEY (favorite_id))";
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."favorite_articles START 1";
				$sqls[] = "CREATE TABLE ".$table_prefix."favorite_articles (
          favorite_id INT4 NOT NULL DEFAULT nextval('seq_".$table_prefix."favorite_articles'),
          article_id INT4 default '0',
          list_id INT4 default '0',
          user_id INT4 default '0',
          favorite_order INT4 default '1',
          favorite_name VARCHAR(255),
          play_type SMALLINT default '0'
          ,PRIMARY KEY (favorite_id))";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE ".$table_prefix."favorite_articles (
          [favorite_id]  COUNTER  NOT NULL,
          [article_id] INTEGER,
          [list_id] INTEGER,
          [user_id] INTEGER,
          [favorite_order] INTEGER,
          [favorite_name] VARCHAR(255),
          [play_type] BYTE
          ,PRIMARY KEY (favorite_id))";
			} 
		}

		// check and add settings for user playlist pages
		$sql  = " SELECT page_id FROM ".$table_prefix."cms_pages ";
		$sql .= " WHERE page_code='user_playlists'";
		$page_user_playlists = get_db_value($sql);
  
		$sql  = " SELECT page_id FROM ".$table_prefix."cms_pages ";
		$sql .= " WHERE page_code='user_playlist_edit'";
		$page_user_playlist_edit = get_db_value($sql);
  
		$sql  = " SELECT page_id FROM ".$table_prefix."cms_pages ";
		$sql .= " WHERE page_code='user_playlist_songs'";
		$page_user_playlist_songs = get_db_value($sql);
  
		if (!$page_user_playlists || !$page_user_playlist_edit || !$page_user_playlist_songs) {
			// get account module_id
			$sql = " SELECT MAX(module_id) FROM ".$table_prefix."cms_modules WHERE module_code='user_account' "; 
			$account_module_id = get_db_value($sql);
			// get data for pages
			if (!$new_page_id) {
				$sql = " SELECT MAX(page_id) FROM ".$table_prefix."cms_pages "; 
				$new_page_id = get_db_value($sql);
			}
			$page_order = 1;
			// get data for blocks
			if (!$new_block_id) {
				$sql = " SELECT MAX(block_id) FROM ".$table_prefix."cms_blocks "; 
				$new_block_id = get_db_value($sql);
			}
			$block_order = 1;
  
			// get data for page settings
			if (!$new_ps_id) {
				$sql = " SELECT MAX(ps_id) FROM ".$table_prefix."cms_pages_settings "; 
				$new_ps_id = get_db_value($sql);
			}
			// check header and footer blocks
			$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='header' "; 
			$header_block_id = get_db_value($sql);
			$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='footer' "; 
			$footer_block_id = get_db_value($sql);
			
			// add my playlists page
			if (!$page_user_playlists) {
				$new_page_id++; 
				$playlists_page_id = $new_page_id; 
				$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
				$sql.= $db->tosql($playlists_page_id, INTEGER).",";
				$sql.= $db->tosql($account_module_id, INTEGER).",";
				$sql.= $db->tosql($page_order, INTEGER).",";
				$sql.= $db->tosql("user_playlists", TEXT).",";
				$sql.= $db->tosql("MY_PLAYLISTS_MSG", TEXT).")";
				$sqls[] = $sql;
  
				// add authors list block
				$new_block_id++; 
				$playlists_block_id = $new_block_id;
				$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
				$sql.= $db->tosql($playlists_block_id, INTEGER).",";
				$sql.= $db->tosql($account_module_id, INTEGER).",";
				$sql.= $db->tosql($block_order, INTEGER).",";
				$sql.= $db->tosql("user_playlists", TEXT).",";
				$sql.= $db->tosql("{PLAYLISTS_MSG}", TEXT).",";
				$sql.= $db->tosql("block_user_playlists.php", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
  
				// add settings for authors listing page
				$new_ps_id++;
				$sql = "INSERT INTO ".$table_prefix."cms_pages_settings ";
				$sql.= " (ps_id,page_id,key_code,key_type,key_rule,layout_id,site_id) VALUES (";
				$sql.= $db->tosql($new_ps_id, INTEGER).",";
				$sql.= $db->tosql($playlists_page_id, INTEGER).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
		  
				if ($header_block_id) {
					$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
					$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
					$sql.= $db->tosql($new_ps_id, INTEGER).",";
					$sql.= $db->tosql(1, INTEGER).",";
					$sql.= $db->tosql($header_block_id, INTEGER).",";
					$sql.= $db->tosql("", TEXT).",";
					$sql.= $db->tosql(1, INTEGER).")";
					$sqls[] = $sql;
				}
		  
				$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
				$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
				$sql.= $db->tosql($new_ps_id, INTEGER).",";
				$sql.= $db->tosql(3, INTEGER).",";
				$sql.= $db->tosql($playlists_block_id, INTEGER).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
		  
				if ($footer_block_id) {
					$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
					$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
					$sql.= $db->tosql($new_ps_id, INTEGER).",";
					$sql.= $db->tosql(5, INTEGER).",";
					$sql.= $db->tosql($footer_block_id, INTEGER).",";
					$sql.= $db->tosql("", TEXT).",";
					$sql.= $db->tosql(1, INTEGER).")";
					$sqls[] = $sql;
				}
			}
  
  
			// add playlist edit page
			if (!$page_user_playlist_edit) {
				$new_page_id++; 
				$playlist_page_id = $new_page_id; 
				$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
				$sql.= $db->tosql($playlist_page_id, INTEGER).",";
				$sql.= $db->tosql($account_module_id, INTEGER).",";
				$sql.= $db->tosql($page_order, INTEGER).",";
				$sql.= $db->tosql("user_playlist_edit", TEXT).",";
				$sql.= $db->tosql("{MY_PLAYLIST_MSG} :: {EDIT_MSG}", TEXT).")";
				$sqls[] = $sql;
  
				// add authors list block
				$new_block_id++; 
				$playlist_block_id = $new_block_id;
				$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
				$sql.= $db->tosql($playlist_block_id, INTEGER).",";
				$sql.= $db->tosql($account_module_id, INTEGER).",";
				$sql.= $db->tosql($block_order, INTEGER).",";
				$sql.= $db->tosql("user_playlist_edit", TEXT).",";
				$sql.= $db->tosql("{PLAYLIST_MSG} :: {EDIT_MSG}", TEXT).",";
				$sql.= $db->tosql("block_user_playlist.php", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
  
				// add settings for authors listing page
				$new_ps_id++;
				$sql = "INSERT INTO ".$table_prefix."cms_pages_settings ";
				$sql.= " (ps_id,page_id,key_code,key_type,key_rule,layout_id,site_id) VALUES (";
				$sql.= $db->tosql($new_ps_id, INTEGER).",";
				$sql.= $db->tosql($playlist_page_id, INTEGER).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
		  
				if ($header_block_id) {
					$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
					$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
					$sql.= $db->tosql($new_ps_id, INTEGER).",";
					$sql.= $db->tosql(1, INTEGER).",";
					$sql.= $db->tosql($header_block_id, INTEGER).",";
					$sql.= $db->tosql("", TEXT).",";
					$sql.= $db->tosql(1, INTEGER).")";
					$sqls[] = $sql;
				}
		  
				$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
				$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
				$sql.= $db->tosql($new_ps_id, INTEGER).",";
				$sql.= $db->tosql(3, INTEGER).",";
				$sql.= $db->tosql($playlist_block_id, INTEGER).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
		  
				if ($footer_block_id) {
					$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
					$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
					$sql.= $db->tosql($new_ps_id, INTEGER).",";
					$sql.= $db->tosql(5, INTEGER).",";
					$sql.= $db->tosql($footer_block_id, INTEGER).",";
					$sql.= $db->tosql("", TEXT).",";
					$sql.= $db->tosql(1, INTEGER).")";
					$sqls[] = $sql;
				}
			}
  
			// add playlist songs edit page
			if (!$page_user_playlist_songs) {
				$new_page_id++; 
				$playlist_page_id = $new_page_id; 
				$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
				$sql.= $db->tosql($playlist_page_id, INTEGER).",";
				$sql.= $db->tosql($account_module_id, INTEGER).",";
				$sql.= $db->tosql($page_order, INTEGER).",";
				$sql.= $db->tosql("user_playlist_songs", TEXT).",";
				$sql.= $db->tosql("{MY_PLAYLIST_MSG} :: {SONGS_MSG}", TEXT).")";
				$sqls[] = $sql;
  
				// add authors list block
				$new_block_id++; 
				$playlist_block_id = $new_block_id;
				$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
				$sql.= $db->tosql($playlist_block_id, INTEGER).",";
				$sql.= $db->tosql($account_module_id, INTEGER).",";
				$sql.= $db->tosql($block_order, INTEGER).",";
				$sql.= $db->tosql("user_playlist_songs", TEXT).",";
				$sql.= $db->tosql("{PLAYLIST_MSG} :: {SONGS_MSG}", TEXT).",";
				$sql.= $db->tosql("block_user_playlist_songs.php", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
  
				// add settings for authors listing page
				$new_ps_id++;
				$sql = "INSERT INTO ".$table_prefix."cms_pages_settings ";
				$sql.= " (ps_id,page_id,key_code,key_type,key_rule,layout_id,site_id) VALUES (";
				$sql.= $db->tosql($new_ps_id, INTEGER).",";
				$sql.= $db->tosql($playlist_page_id, INTEGER).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
		  
				if ($header_block_id) {
					$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
					$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
					$sql.= $db->tosql($new_ps_id, INTEGER).",";
					$sql.= $db->tosql(1, INTEGER).",";
					$sql.= $db->tosql($header_block_id, INTEGER).",";
					$sql.= $db->tosql("", TEXT).",";
					$sql.= $db->tosql(1, INTEGER).")";
					$sqls[] = $sql;
				}
		  
				$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
				$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
				$sql.= $db->tosql($new_ps_id, INTEGER).",";
				$sql.= $db->tosql(3, INTEGER).",";
				$sql.= $db->tosql($playlist_block_id, INTEGER).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
		  
				if ($footer_block_id) {
					$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
					$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
					$sql.= $db->tosql($new_ps_id, INTEGER).",";
					$sql.= $db->tosql(5, INTEGER).",";
					$sql.= $db->tosql($footer_block_id, INTEGER).",";
					$sql.= $db->tosql("", TEXT).",";
					$sql.= $db->tosql(1, INTEGER).")";
					$sqls[] = $sql;
				}
			}
		}

		// check keywords search settings for articles
		$sql  = " SELECT menu_item_id, menu_id FROM ".$table_prefix."menus_items " ;
		$sql .= " WHERE menu_code='articles-keywords' ";
		$db->query($sql);
		if (!$db->next_record()) {
  
			$sql  = " SELECT menu_item_id, menu_id FROM ".$table_prefix."menus_items " ;
			$sql .= " WHERE menu_code='articles-settings' ";
			$db->query($sql);
			if ($db->next_record()) {
				$menu_id= $db->f("menu_id");
				$menu_item_id= $db->f("menu_item_id");
  
				$sql  = " INSERT INTO ".$table_prefix."menus_items (menu_id, parent_menu_item_id, menu_order, menu_code, menu_title, menu_url, admin_access) " ;
				$sql .= " VALUES (";
				$sql .= $db->tosql($menu_id, INTEGER) . ", ";
				$sql .= $db->tosql($menu_item_id, INTEGER) . ", ";
				$sql .= $db->tosql(5, INTEGER) . ", ";
				$sql .= $db->tosql('articles-keywords', TEXT) . ", ";
				$sql .= $db->tosql('KEYWORDS_SEARCH_MSG', TEXT) . ", ";
				$sql .= $db->tosql('admin_articles_keywords.php', TEXT) . ", ";
				$sql .= $db->tosql('articles', TEXT) . ") ";
				$sqls[] = $sql;
			}
		}
  
		if (!$articles_is_keywords_field) {
			// added keywords tables for products
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "articles ADD is_keywords TINYINT DEFAULT '0'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "articles ADD is_keywords TINYINT DEFAULT '0'",
				"postgre" => "ALTER TABLE " . $table_prefix . "articles ADD is_keywords SMALLINT DEFAULT '0'",
				"access"  => "ALTER TABLE " . $table_prefix . "articles ADD is_keywords BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = "CREATE INDEX " . $table_prefix . "articles_is_keywords ON " . $table_prefix . "articles (is_keywords) ";
			$sqls[] = "UPDATE " . $table_prefix . "articles SET is_keywords=0 ";
		}
  
		if (!$keywords_articles_table) {
			$mysql_sql  = "CREATE TABLE ".$table_prefix."keywords_articles (
        `article_id` INT(11) default '0',
        `keyword_id` INT(11) default '0',
        `field_id` TINYINT default '0',
        `keyword_position` SMALLINT default '0',
        `keyword_rank` SMALLINT default '0'
        ,KEY field_id (field_id)
        ,KEY article_id (article_id)
        ,KEY keyword_id (keyword_id)
        ,KEY keyword_position (keyword_position)
        ,KEY keyword_rank (keyword_rank))";
  
			$sqlsrv_sql  = "CREATE TABLE ".$table_prefix."keywords_articles (
        article_id INTEGER default '0',
        keyword_id INTEGER default '0',
        field_id SMALLINT default '0',
        keyword_position SMALLINT default '0',
        keyword_rank SMALLINT default '0')";
  
			$postgre_sql  = "CREATE TABLE ".$table_prefix."keywords_articles (
        article_id INT4 default '0',
        keyword_id INT4 default '0',
        field_id SMALLINT default '0',
        keyword_position SMALLINT default '0',
        keyword_rank SMALLINT default '0')";
  
			$access_sql  = "CREATE TABLE ".$table_prefix."keywords_articles (
        [article_id] INTEGER,
        [keyword_id] INTEGER,
        [field_id] BYTE,
        [keyword_position] INTEGER,
        [keyword_rank] INTEGER)";
  
			$sql_types = array("mysql" => $mysql_sql, "sqlsrv" => $sqlsrv_sql, "postgre" => $postgre_sql, "access" => $access_sql);
			$sqls[] = $sql_types[$db_type];
  
			if ($db_type != "mysql") {
				$sqls[] = "CREATE INDEX ".$table_prefix."keywords_articles_field_id ON ".$table_prefix."keywords_articles (field_id)";
				$sqls[] = "CREATE INDEX ".$table_prefix."keywords_articles_article_id ON ".$table_prefix."keywords_articles (article_id)";
				$sqls[] = "CREATE INDEX ".$table_prefix."keywords_articles_keyword_id ON ".$table_prefix."keywords_articles (keyword_id)";
				$sqls[] = "CREATE INDEX ".$table_prefix."keywords_articles_keyword_position ON ".$table_prefix."keywords_articles (keyword_position)";
				$sqls[] = "CREATE INDEX ".$table_prefix."keywords_articles_keyword_rank ON ".$table_prefix."keywords_articles (keyword_rank)";
			}
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.0.7");
	}


	if (comp_vers("5.0.8", $current_db_version) == 1)
	{
		// check for field update field
		$menu_code_field = false; 
		$fields = $db->get_fields($table_prefix."menus");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "menu_code") {
				$menu_code_field = true;
			}
		}
		if (!$menu_code_field) {
			$sql = "ALTER TABLE " . $table_prefix . "menus ADD menu_code VARCHAR(64) ";
			$sqls[] = $sql;
		}
  
		// add right column menu if it wasn't added
		$sql = "SELECT layout_id FROM ".$table_prefix."cms_layouts WHERE layout_template='layout_right.html' ";
		$db->query($sql);
		if (!$db->next_record()) {
  
			$sql = " SELECT MAX(layout_id) FROM ".$table_prefix."cms_layouts "; 
			$layout_id = get_db_value($sql);
			$layout_id++;
  
			$sql  = " INSERT INTO ".$table_prefix."cms_layouts ";
			$sql .= " (layout_id, layout_name, layout_order, layout_template, admin_template) VALUES (".intval($layout_id).", 'RIGHT_COLUMN_LAYOUT_MSG', 6, 'layout_right.html', 'admin_layout_right.html')";
			$sqls[] = $sql;
  
			$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (layout_id,frame_name,tag_name) VALUES (".intval($layout_id).", 'HEADER_MSG' , 'header' )";
			$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (layout_id,frame_name,tag_name) VALUES (".intval($layout_id).", 'MIDDLE_COLUMN_MSG' , 'middle')";
			$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (layout_id,frame_name,tag_name) VALUES (".intval($layout_id).", 'RIGHT_COLUMN_MSG' , 'right')";
			$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (layout_id,frame_name,tag_name) VALUES (".intval($layout_id).", 'FOOTER_MSG' , 'footer')";
		} else {
			$right_layout_id = $db->f("layout_id");
			$sql = "SELECT layout_id FROM ".$table_prefix."cms_frames WHERE tag_name='frame_bar' AND layout_id=" . $db->tosql($right_layout_id, INTEGER);
			$db->query($sql);
			if (!$db->next_record()) {
				$sqls[] = "INSERT INTO ".$table_prefix."cms_frames (layout_id,frame_name,tag_name) VALUES (".intval($right_layout_id).", 'NAVIGATION_BAR_MSG' , 'frame_bar')";
			}
		}
  
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
  
		$sql = " SELECT COUNT(*) FROM ".$table_prefix . "sites WHERE short_name IS NULL OR short_name='' ";
		$empty_short_names = get_db_value($sql);
		if ($empty_short_names) {
			$sqls[] = " UPDATE " . $table_prefix . "sites SET short_name=site_name WHERE short_name IS NULL OR short_name=''  ";
		}

		// new range settings for top seller block if doesn't exists
		if ($top_sellers_block_id) {
			$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE variable_name='categories_range' "; 
			$sql .= " AND block_id=" . $db->tosql($top_sellers_block_id, INTEGER); 
			$db->query($sql);
			if (!$db->next_record()) {
				// get max property_order for latest block
				$sql  = " SELECT MAX(property_order) FROM ".$table_prefix."cms_blocks_properties "; 
				$sql .= " WHERE block_id=" . $db->tosql($top_sellers_block_id, INTEGER); 
				$property_order = get_db_value($sql);
  
				$property_id++; $property_order++;
				$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,variable_name,required) VALUES ($property_id, $top_sellers_block_id, $property_order, 'CATEGORIES_RANGE_MSG', 'LISTBOX', 'categories_range', 0)";
				$value_id++;
				$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_value) VALUES (";
				$sql .= intval($value_id).",".intval($property_id).", 1, 'ALL_CATEGORIES_RANGE_MSG', 'all') ";
				$sqls[] = $sql;	
				$value_id++;
				$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_value) VALUES (";
				$sql .= intval($value_id).",".intval($property_id).", 2, 'ACTIVE_CATEGORY_RANGE_MSG', 'active') ";
				$sqls[] = $sql;	
				$value_id++;
				$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_value) VALUES (";
				$sql .= intval($value_id).",".intval($property_id).", 3, 'CATEGORY_SUBS_RANGE_MSG', 'subs') ";
				$sqls[] = $sql;	
			}
		}
  
		// new settings for products latest block
		if ($products_latest_block_id) {
			$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE variable_name='period_days' "; 
			$sql .= " AND block_id=" . $db->tosql($products_latest_block_id, INTEGER); 
			$db->query($sql);
			if (!$db->next_record()) {
				// get max property_order for latest block
				$sql  = " SELECT MAX(property_order) FROM ".$table_prefix."cms_blocks_properties "; 
				$sql .= " WHERE block_id=" . $db->tosql($products_latest_block_id, INTEGER); 
				$property_order = get_db_value($sql);
  
				$property_id++; $property_order++;
				$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,variable_name,end_html,required) VALUES ($property_id, $products_latest_block_id, $property_order, 'TIME_PERIOD_MSG', 'TEXTBOX', 'period_days', ' {DAYS_MSG}', 0)";
			}
		}
  
		// new range settings for top seller block if doesn't exists
		if ($products_offers_block_id) {
			$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE variable_name='slider_column_width' "; 
			$sql .= " AND block_id=" . $db->tosql($products_offers_block_id, INTEGER); 
			$db->query($sql);
			if (!$db->next_record()) {
				$property_id++; 
				$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,variable_name,required) VALUES ($property_id, $products_offers_block_id, 11, 'COLUMN_WIDTH_MSG', 'TEXTBOX', 'slider_column_width', 0)";
			}
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.0.8");
	}


	// check for new profile fields
	$users_middle_name = false; $users_address3 = false; $user_settings = false;
	$fields = $db->get_fields($table_prefix."users");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "middle_name") {
			$users_middle_name = true;
		} else if ($field_info["name"] == "address3") {
			$users_address3 = true;
		} else if ($field_info["name"] == "user_settings") {
			$user_settings = true;
		}
	}

	if (comp_vers("5.0.9", $current_db_version) == 1)
	{
		if (!$users_middle_name) {
			if ($db_type == "mysql") {
				$sqls[] = "ALTER TABLE " . $table_prefix . "users ADD middle_name VARCHAR(64) AFTER first_name ";
				$sqls[] = "ALTER TABLE " . $table_prefix . "users ADD delivery_middle_name VARCHAR(64) AFTER delivery_first_name ";
				$sqls[] = "ALTER TABLE " . $table_prefix . "users ADD bill_middle_name VARCHAR(64) AFTER bill_first_name ";
			} else {
				$sqls[] = "ALTER TABLE " . $table_prefix . "users ADD middle_name VARCHAR(64) ";
				$sqls[] = "ALTER TABLE " . $table_prefix . "users ADD delivery_middle_name VARCHAR(64) ";
				$sqls[] = "ALTER TABLE " . $table_prefix . "users ADD bill_middle_name VARCHAR(64) ";
			}
		}
		if (!$users_address3) {
			if ($db_type == "mysql") {
				$sqls[] = "ALTER TABLE " . $table_prefix . "users ADD address3 VARCHAR(64) AFTER address2 ";
				$sqls[] = "ALTER TABLE " . $table_prefix . "users ADD delivery_address3 VARCHAR(64) AFTER address2 ";
				$sqls[] = "ALTER TABLE " . $table_prefix . "users ADD bill_address3 VARCHAR(64) AFTER address2 ";
			} else {
				$sqls[] = "ALTER TABLE " . $table_prefix . "users ADD address3 VARCHAR(64) ";
				$sqls[] = "ALTER TABLE " . $table_prefix . "users ADD delivery_address3 VARCHAR(64) ";
				$sqls[] = "ALTER TABLE " . $table_prefix . "users ADD bill_address3 VARCHAR(64) ";
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
  
		// check for new profile fields
		$users_addresses_middle_name = false; $users_addresses_address3 = false;
		$fields = $db->get_fields($table_prefix."users_addresses");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "middle_name") {
				$users_addresses_middle_name = true;
			} else if ($field_info["name"] == "address3") {
				$users_addresses_address3 = true;
			}
		}
		if (!$users_addresses_middle_name) {
			if ($db_type == "mysql") {
				$sqls[] = "ALTER TABLE " . $table_prefix . "users_addresses ADD middle_name VARCHAR(64) AFTER first_name ";
			} else {
				$sqls[] = "ALTER TABLE " . $table_prefix . "users_addresses ADD middle_name VARCHAR(64) ";
			}
		}
		if (!$users_addresses_address3) {
			if ($db_type == "mysql") {
				$sqls[] = "ALTER TABLE " . $table_prefix . "users_addresses ADD address3 VARCHAR(64) AFTER address2 ";
			} else {
				$sqls[] = "ALTER TABLE " . $table_prefix . "users_addresses ADD address3 VARCHAR(64) ";
			}
		}

		$sql  = " SELECT * FROM ".$table_prefix."user_types_settings ";
		$sql .= " WHERE setting_name='approve_profile'";
		$db->query($sql);
		if ($db->next_record()) {
			do {
				$type_id = $db->f("type_id");
				$setting_value = $db->f("setting_value");
  
				$sql  = " INSERT INTO ".$table_prefix."user_types_settings (type_id, setting_name, setting_value) VALUES ";
				$sql .= " (" .$db->tosql($type_id, INTEGER) . ", 'new_account_approve', " . intval($setting_value) . ")";
				$sqls[] = $sql;
  
				$sql  = " INSERT INTO ".$table_prefix."user_types_settings (type_id, setting_name, setting_value) VALUES ";
				$sql .= " (" .$db->tosql($type_id, INTEGER) . ", 'edit_account_approve', " . intval($setting_value) . ")";
				$sqls[] = $sql;
  
			} while ($db->next_record());
  
			$sql  = " DELETE FROM ".$table_prefix."user_types_settings ";
			$sql .= " WHERE setting_name='approve_profile'";
			$sqls[] = $sql;
		}

		// add regular expression fields for countries
		$countries_postal_code_regexp = false; 
		$countries_postal_code_error = false; 
		$fields = $db->get_fields($table_prefix."countries");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "postal_code_regexp") {
				$countries_postal_code_regexp = true;
			} else if ($field_info["name"] == "postal_code_error") {
				$countries_postal_code_error = true;
			}
		}
  
		if (!$countries_postal_code_regexp) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "countries ADD postal_code_regexp TEXT",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "countries ADD postal_code_regexp TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "countries ADD postal_code_regexp TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "countries ADD postal_code_regexp LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$countries_postal_code_regexp) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "countries ADD postal_code_error TEXT",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "countries ADD postal_code_error TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "countries ADD postal_code_error TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "countries ADD postal_code_error LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}

		// check menu items field in different tables
		$ip_rules_field = false; $ip_added_field = false;
		$fields = $db->get_fields($table_prefix."black_ips");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "ip_rules") {
				$ip_rules_field = true;
			} else if ($field_info["name"] == "date_added") {
				$ip_added_field = true;
			}
		}
  
		if (!$ip_rules_field) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "black_ips ADD ip_rules TEXT",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "black_ips ADD ip_rules TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "black_ips ADD ip_rules TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "black_ips ADD ip_rules LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
  
			$sql  = " UPDATE " . $table_prefix . "black_ips SET ";
			$sql .= " ip_rules='{\"log_in\":\"blocked\",\"sign_up\":\"blocked\",\"orders\":\"blocked\",\"support\":\"blocked\",\"forum\":\"blocked\",\"products_reviews\":\"blocked\",\"articles_reviews\":\"blocked\"}'";
			$sqls[] = $sql;
		}
		if (!$ip_added_field) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "black_ips ADD date_added DATETIME ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "black_ips ADD date_added DATETIME ",
				"postgre" => "ALTER TABLE " . $table_prefix . "black_ips ADD date_added TIMESTAMP ",
				"access"  => "ALTER TABLE " . $table_prefix . "black_ips ADD date_added DATETIME ",
			);
			$sqls[] = $sql_types[$db_type];
		}


		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.0.9");
	}


	// saved carts checks 
	$cart_type = false; $cart_updated = false; $cart_reminder_sent = false; $cart_reminders_count = false; $cart_email_field = false; $cart_quantity_field = false;
	$cart_country_id = false; $cart_country_code = false; $cart_user_ip = false; $cart_reminder_status = false;
	$fields = $db->get_fields($table_prefix."saved_carts");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "cart_type") {
			$cart_type = true;
		} else if ($field_info["name"] == "cart_updated") {
			$cart_updated = true;
		} else if ($field_info["name"] == "reminder_sent") {
			$cart_reminder_sent = true;
		} else if ($field_info["name"] == "reminders_count") {
			$cart_reminders_count = true;
		} else if ($field_info["name"] == "cart_email") {
			$cart_email_field = true;
		} else if ($field_info["name"] == "cart_quantity") {
			$cart_quantity_field = true;
		} else if ($field_info["name"] == "country_id") {
			$cart_country_id = true;
		} else if ($field_info["name"] == "country_code") {
			$cart_country_code = true;
		} else if ($field_info["name"] == "user_ip") {
			$cart_user_ip = true;
		} else if ($field_info["name"] == "reminder_status") {
			$cart_reminder_status = true;
		}
	}

	if (comp_vers("5.0.10", $current_db_version) == 1)
	{
		// saved carts update
		if (!$cart_type) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "saved_carts ADD cart_type TINYINT DEFAULT '0' AFTER user_id ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "saved_carts ADD cart_type TINYINT DEFAULT '0' ",
				"postgre" => "ALTER TABLE " . $table_prefix . "saved_carts ADD cart_type SMALLINT DEFAULT '0'",
				"access"  => "ALTER TABLE " . $table_prefix . "saved_carts ADD cart_type BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "saved_carts SET cart_type=1 ";
		}
		if (!$cart_updated) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "saved_carts ADD cart_updated DATETIME ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "saved_carts ADD cart_updated DATETIME ",
				"postgre" => "ALTER TABLE " . $table_prefix . "saved_carts ADD cart_updated TIMESTAMP ",
				"access"  => "ALTER TABLE " . $table_prefix . "saved_carts ADD cart_updated DATETIME ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = "CREATE INDEX ".$table_prefix."saved_carts_cart_updated ON ".$table_prefix."saved_carts (cart_updated) ";
		}
		if (!$cart_reminder_sent) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "saved_carts ADD reminder_sent DATETIME ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "saved_carts ADD reminder_sent DATETIME ",
				"postgre" => "ALTER TABLE " . $table_prefix . "saved_carts ADD reminder_sent TIMESTAMP ",
				"access"  => "ALTER TABLE " . $table_prefix . "saved_carts ADD reminder_sent DATETIME ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = "CREATE INDEX ".$table_prefix."saved_carts_reminder_sent ON ".$table_prefix."saved_carts (reminder_sent) ";
		}
		if (!$cart_reminders_count) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "saved_carts ADD reminders_count INT(11) DEFAULT '0' ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "saved_carts ADD reminders_count INTEGER DEFAULT '0' ",
				"postgre" => "ALTER TABLE " . $table_prefix . "saved_carts ADD reminders_count INT4  DEFAULT '0'",
				"access"  => "ALTER TABLE " . $table_prefix . "saved_carts ADD reminders_count INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$cart_quantity_field) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "saved_carts ADD cart_quantity INT(11) AFTER cart_name ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "saved_carts ADD cart_quantity INTEGER  ",
				"postgre" => "ALTER TABLE " . $table_prefix . "saved_carts ADD cart_quantity INT4  ",
				"access"  => "ALTER TABLE " . $table_prefix . "saved_carts ADD cart_quantity INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$cart_email_field) {
			if ($db_type == "mysql") {
				$sqls[] = "ALTER TABLE " . $table_prefix . "saved_carts ADD cart_email VARCHAR(128) AFTER cart_name ";
			} else {
				$sqls[] = "ALTER TABLE " . $table_prefix . "saved_carts ADD cart_email VARCHAR(128) ";
			}
		}
		if (!$cart_country_id) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "saved_carts ADD country_id INT(11) DEFAULT '0' ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "saved_carts ADD country_id INTEGER DEFAULT '0' ",
				"postgre" => "ALTER TABLE " . $table_prefix . "saved_carts ADD country_id INT4  DEFAULT '0'",
				"access"  => "ALTER TABLE " . $table_prefix . "saved_carts ADD country_id INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$cart_country_code) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "saved_carts ADD country_code VARCHAR(4) ";
		}
		if (!$cart_user_ip) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "saved_carts ADD user_ip VARCHAR(32) ";
		}
		if (!$cart_reminder_status) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "saved_carts ADD reminder_status TINYINT DEFAULT '0' AFTER user_id ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "saved_carts ADD reminder_status TINYINT DEFAULT '0' ",
				"postgre" => "ALTER TABLE " . $table_prefix . "saved_carts ADD reminder_status SMALLINT DEFAULT '0'",
				"access"  => "ALTER TABLE " . $table_prefix . "saved_carts ADD reminder_status BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "saved_carts SET reminder_status=0 ";
		}
  
  
		$saved_items_updated_field = false; 
		$fields = $db->get_fields($table_prefix."saved_items");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "date_updated") {
				$saved_items_updated_field = true;
			} 
		}
		if (!$saved_items_updated_field) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "saved_items ADD date_updated DATETIME ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "saved_items ADD date_updated DATETIME ",
				"postgre" => "ALTER TABLE " . $table_prefix . "saved_items ADD date_updated TIMESTAMP ",
				"access"  => "ALTER TABLE " . $table_prefix . "saved_items ADD date_updated DATETIME ",
			);
			$sqls[] = $sql_types[$db_type];
		}


		$sql  = " SELECT menu_item_id, menu_id FROM ".$table_prefix."menus_items " ;
		$sql .= " WHERE menu_code='orders' ";
		$db->query($sql);
		if ($db->next_record()) {
			$menu_id= $db->f("menu_id");
			$menu_item_id= $db->f("menu_item_id");
  
			$sql  = " SELECT menu_item_id FROM ".$table_prefix."menus_items " ;
			$sql .= " WHERE menu_code='carts' ";
			$db->query($sql);
			if (!$db->next_record()) {
				$sql  = " INSERT INTO ".$table_prefix."menus_items (menu_id, parent_menu_item_id, menu_order, menu_code, menu_title, menu_url, admin_access) " ;
				$sql .= " VALUES (";
				$sql .= $db->tosql($menu_id, INTEGER) . ", ";
				$sql .= $db->tosql($menu_item_id, INTEGER) . ", ";
				$sql .= $db->tosql(5, INTEGER) . ", ";
				$sql .= $db->tosql('carts', TEXT) . ", ";
				$sql .= $db->tosql('CARTS_MSG', TEXT) . ", ";
				$sql .= $db->tosql('admin_carts.php', TEXT) . ", ";
				$sql .= $db->tosql('sales_orders,orders_recover', TEXT) . ") ";
				$sqls[] = $sql;
			}
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.0.10");
	}



	if (comp_vers("5.0.11", $current_db_version) == 1)
	{
		// tax rates: add global order tax amount field 
		$tax_rates_order_fixed_amount = false; 
		$tax_rates_tax_php_lib = false; 
		$fields = $db->get_fields($table_prefix."tax_rates");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "order_fixed_amount") {
				$tax_rates_order_fixed_amount = true;
			} else if ($field_info["name"] == "tax_php_lib") {
				$tax_rates_tax_php_lib = true;
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
		}
		if (!$tax_rates_tax_php_lib) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "tax_rates ADD tax_php_lib VARCHAR(255) ";
		}

		$orders_taxes_order_fixed_amount = false; 
		$fields = $db->get_fields($table_prefix."orders_taxes");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "order_fixed_amount") {
				$orders_taxes_order_fixed_amount = true;
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
		}

		if (!$order_statuses_sites) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE ".$table_prefix."order_statuses_sites (
          `status_id` INT(11) NOT NULL default '0',
          `site_id` INT(11) NOT NULL default '0'
          ,PRIMARY KEY (status_id, site_id)
          ) DEFAULT CHARACTER SET=utf8mb4 ";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE ".$table_prefix."order_statuses_sites (
          status_id INTEGER NOT NULL,
          site_id INTEGER NOT NULL
          ,PRIMARY KEY (status_id, site_id))";
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE TABLE ".$table_prefix."order_statuses_sites (
          status_id INT4 NOT NULL default '0',
          site_id INT4 NOT NULL default '0'
          ,PRIMARY KEY (status_id, site_id))";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE ".$table_prefix."order_statuses_sites (
          [status_id] INTEGER NOT NULL,
          [site_id] INTEGER NOT NULL
          ,PRIMARY KEY (status_id, site_id))";
			} 
		}
		if (!$order_statuses_sites_all) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD sites_all TINYINT DEFAULT '1'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD sites_all TINYINT DEFAULT '1'",
				"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD sites_all SMALLINT DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD sites_all BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "order_statuses SET sites_all=1 ";
		}


		// add new js_type parameter for navigation block and remove old visible_depth_level
		$nav_block_id = ""; 
		$sql  = " SELECT block_id FROM ".$table_prefix."cms_blocks "; 
		$sql .= " WHERE block_code='navigation' "; 
		$nav_block_id = get_db_value($sql);
		if ($nav_block_id) {
			$sql  = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties "; 
			$sql .= " WHERE variable_name='js_type' "; 
			$sql .= " AND block_id=" . $db->tosql($nav_block_id, INTEGER); 
			$db->query($sql);
			if (!$db->next_record()) {
  
				$sql  = " DELETE FROM ".$table_prefix."cms_blocks_properties "; 
				$sql .= " WHERE variable_name='visible_depth_level' "; 
				$sql .= " AND block_id=" . $db->tosql($nav_block_id, INTEGER); 
				$db->query($sql);
  
				$property_id++; 
				$sqls[] ="INSERT INTO " .$table_prefix."cms_blocks_properties (property_id,block_id,property_order,property_name,control_type,parent_property_id,parent_value_id,variable_name,default_value,required) VALUES ($property_id, $nav_block_id, 1, 'JAVASCRIPT_TYPE_MSG', 'LISTBOX', NULL, NULL, 'js_type', NULL, 0)";
  
				$value_id++;
				$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
				$sql .= intval($value_id).",".intval($property_id).", 1, 'NONE_MSG', '', '') ";
				$sqls[] = $sql;	
				$value_id++;
				$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
				$sql .= intval($value_id).",".intval($property_id).", 2, 'ONCLICK_EVENT_MSG', '', 'click') ";
				$sqls[] = $sql;	
				$value_id++;
				$sql  = "INSERT INTO " . $table_prefix . "cms_blocks_values (value_id,property_id,value_order,value_name,variable_name,variable_value) VALUES (";
				$sql .= intval($value_id).",".intval($property_id).", 3, 'ONMOUSEOVER_EVENT_MSG', '', 'hover') ";
				$sqls[] = $sql;	
			}
		}

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

		// check new global meta data field in pages table
		$pages_meta_data_field = false; 
		$fields = $db->get_fields($table_prefix."pages");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "meta_data") {
				$pages_meta_data_field = true;
			}
		}
		if (!$pages_meta_data_field) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "pages ADD meta_data TEXT",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "pages ADD meta_data TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "pages ADD meta_data TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "pages ADD meta_data LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.0.11");
	}

	$coupons_parent_coupon_id = false; $coupons_user_id = false; 
	$coupons_transfer_type = false; $coupons_transfer_code = false;
	$coupons_transfer_date = false; $coupons_transfer_amount = false; $coupons_transfer_user_id = false;
	$coupons_transfer_errors = false; $coupons_transfer_data = false;
	$fields = $db->get_fields($table_prefix."coupons");
	foreach ($fields as $id => $field_info) {

		if ($field_info["name"] == "user_id") {
			$coupons_user_id = true;
		} else if ($field_info["name"] == "parent_coupon_id") {
			$coupons_parent_coupon_id = true;
		} else if ($field_info["name"] == "transfer_type") {
			$coupons_transfer_type = true;
		} else if ($field_info["name"] == "transfer_code") {
			$coupons_transfer_code = true;
		} else if ($field_info["name"] == "transfer_date") {
			$coupons_transfer_date = true;
		} else if ($field_info["name"] == "transfer_amount") {
			$coupons_transfer_amount = true;
		} else if ($field_info["name"] == "transfer_user_id") {
			$coupons_transfer_user_id = true;
		} else if ($field_info["name"] == "transfer_errors") {
			$coupons_transfer_errors = true;
		} else if ($field_info["name"] == "transfer_data") {
			$coupons_transfer_data = true;
		} 
	}

	if (comp_vers("5.0.12", $current_db_version) == 1)
	{
		// vouchers tables and fields 
		$is_user_voucher_field = false; 
		$fields = $db->get_fields($table_prefix."item_types");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "is_user_voucher") {
				$is_user_voucher_field = true;
			} 
		}
		if (!$is_user_voucher_field) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "item_types ADD is_user_voucher TINYINT DEFAULT '0'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "item_types ADD is_user_voucher TINYINT DEFAULT '0'",
				"postgre" => "ALTER TABLE " . $table_prefix . "item_types ADD is_user_voucher SMALLINT DEFAULT '0'",
				"access"  => "ALTER TABLE " . $table_prefix . "item_types ADD is_user_voucher BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "item_types SET is_user_voucher=0 ";
		}

		// va_coupons_events - table to save coupons actions
		if (!$coupons_events_table) {
			if ($db_type == "mysql") {
				$sqls[] = "CREATE TABLE va_coupons_events (
          `event_id` INT(11) NOT NULL AUTO_INCREMENT,
          `coupon_id` INT(11) DEFAULT '0',
          `order_id` INT(11) DEFAULT '0',
          `payment_id` INT(11) DEFAULT '0',
          `transaction_id` VARCHAR(128),
          `admin_id` INT(11) DEFAULT '0',
          `user_id` INT(11) DEFAULT '0',
          `from_user_id` INT(11) DEFAULT '0',
          `to_user_id` INT(11) DEFAULT '0',
          `event_date` DATETIME,
          `event_type` VARCHAR(32),
          `remote_ip` VARCHAR(32),
          `coupon_amount` DOUBLE(16,2) DEFAULT '0'
          ,KEY admin_id (admin_id)
          ,KEY coupon_id (coupon_id)
          ,KEY order_id (order_id)
          ,PRIMARY KEY (event_id)
          ,KEY user_id (user_id)
          ) DEFAULT CHARACTER SET=utf8mb4 ";
			} else if ($db_type == "sqlsrv") {
				$sqls[] = "CREATE TABLE va_coupons_events (
          event_id INTEGER NOT NULL IDENTITY,
          coupon_id INTEGER,
          order_id INTEGER,
          payment_id INTEGER,
          transaction_id VARCHAR(128),
          admin_id INTEGER,
          user_id INTEGER,
          from_user_id INTEGER,
          to_user_id INTEGER,
          event_date DATETIME,
          event_type VARCHAR(32),
          remote_ip VARCHAR(32),
          coupon_amount FLOAT (16)
          ,PRIMARY KEY (event_id))";
			} else if ($db_type == "postgre") {
				$sqls[] = "CREATE SEQUENCE seq_".$table_prefix."coupons_events START 1";
				$sqls[] = "CREATE TABLE va_coupons_events (
          event_id INT4 NOT NULL DEFAULT nextval('seq_va_coupons_events'),
          coupon_id INT4 default '0',
          order_id INT4 default '0',
          payment_id INT4 default '0',
          transaction_id VARCHAR(128),
          admin_id INT4 default '0',
          user_id INT4 default '0',
          from_user_id INT4 default '0',
          to_user_id INT4 default '0',
          event_date TIMESTAMP,
          event_type VARCHAR(32),
          remote_ip VARCHAR(32),
          coupon_amount FLOAT4 default '0'
          ,PRIMARY KEY (event_id))";
			} else if ($db_type == "access") {
				$sqls[] = "CREATE TABLE va_coupons_events (
          [event_id]  COUNTER  NOT NULL,
          [coupon_id] INTEGER,
          [order_id] INTEGER,
          [payment_id] INTEGER,
          [transaction_id] VARCHAR(128),
          [admin_id] INTEGER,
          [user_id] INTEGER,
          [from_user_id] INTEGER,
          [to_user_id] INTEGER,
          [event_date] DATETIME,
          [event_type] VARCHAR(32),
          [remote_ip] VARCHAR(32),
          [coupon_amount] FLOAT
          ,PRIMARY KEY (event_id))";
			} 
			if ($db_type != "mysql") {
				$sqls[] = "CREATE INDEX va_coupons_events_admin_id ON va_coupons_events (admin_id)";
				$sqls[] = "CREATE INDEX va_coupons_events_coupon_id ON va_coupons_events (coupon_id)";
				$sqls[] = "CREATE INDEX va_coupons_events_order_id ON va_coupons_events (order_id)";
				$sqls[] = "CREATE INDEX va_coupons_events_user_id ON va_coupons_events (user_id)";
			}
		}
    
		if (!$coupons_parent_coupon_id) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD parent_coupon_id INT(11) ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "coupons ADD parent_coupon_id INTEGER ",
				"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD parent_coupon_id INT4  ",
				"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD parent_coupon_id INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = "CREATE INDEX ".$table_prefix."coupons_parent_coupon_id ON ".$table_prefix."coupons (parent_coupon_id)";
		}
  
		if (!$coupons_user_id) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD user_id INT(11) ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "coupons ADD user_id INTEGER ",
				"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD user_id INT4  ",
				"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD user_id INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = "CREATE INDEX ".$table_prefix."coupons_user_id ON ".$table_prefix."coupons (user_id)";
		}
  
		if (!$coupons_transfer_type) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "coupons ADD transfer_type VARCHAR(32) ";
		}
		if (!$coupons_transfer_code) {
			$sqls[] = "ALTER TABLE " . $table_prefix . "coupons ADD transfer_code VARCHAR(32) ";
		}
		if (!$coupons_transfer_date) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD transfer_date DATETIME ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "coupons ADD transfer_date DATETIME ",
				"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD transfer_date TIMESTAMP ",
				"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD transfer_date DATETIME ",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$coupons_transfer_amount) {
			$sql_types = array(
				"mysql"  => "ALTER TABLE " . $table_prefix . "coupons ADD transfer_amount DOUBLE(16,2) ",
				"sqlsrv" => "ALTER TABLE " . $table_prefix . "coupons ADD transfer_amount FLOAT(10) ",
				"postgre"=> "ALTER TABLE " . $table_prefix . "coupons ADD transfer_amount FLOAT4 ",
				"access" => "ALTER TABLE " . $table_prefix . "coupons ADD transfer_amount FLOAT "
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$coupons_transfer_user_id) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD transfer_user_id INT(11) ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "coupons ADD transfer_user_id INTEGER ",
				"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD transfer_user_id INT4  ",
				"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD transfer_user_id INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$coupons_transfer_errors) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD transfer_errors INT(11) ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "coupons ADD transfer_errors INTEGER ",
				"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD transfer_errors INT4  ",
				"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD transfer_errors INTEGER ",
			);
			$sqls[] = $sql_types[$db_type];
		}
		if (!$coupons_transfer_data) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "coupons ADD transfer_data TEXT",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "coupons ADD transfer_data TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "coupons ADD transfer_data TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "coupons ADD transfer_data LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}

		// check and add settings for user voucher pages
		$sql  = " SELECT page_id FROM ".$table_prefix."cms_pages ";
		$sql .= " WHERE page_code='user_vouchers'";
		$page_user_vouchers = get_db_value($sql);
  
		$sql  = " SELECT page_id FROM ".$table_prefix."cms_pages ";
		$sql .= " WHERE page_code='user_voucher'";
		$page_user_voucher = get_db_value($sql);
  
		$sql  = " SELECT page_id FROM ".$table_prefix."cms_pages ";
		$sql .= " WHERE page_code='user_voucher_send'";
		$page_user_voucher_send = get_db_value($sql);
  
		$sql  = " SELECT page_id FROM ".$table_prefix."cms_pages ";
		$sql .= " WHERE page_code='user_voucher_cash'";
		$page_user_voucher_cash = get_db_value($sql);
  
		if (!$page_user_vouchers || !$page_user_voucher || !$page_user_voucher_send || !$page_user_voucher_cash) {
			// get account module_id
			$sql = " SELECT MAX(module_id) FROM ".$table_prefix."cms_modules WHERE module_code='user_account' "; 
			$account_module_id = get_db_value($sql);
			// get data for pages
			if (!$new_page_id) {
				$sql = " SELECT MAX(page_id) FROM ".$table_prefix."cms_pages "; 
				$new_page_id = get_db_value($sql);
			}
			$page_order = 1;
			// get data for blocks
			if (!$new_block_id) {
				$sql = " SELECT MAX(block_id) FROM ".$table_prefix."cms_blocks "; 
				$new_block_id = get_db_value($sql);	
			}
			$block_order = 1;
  
			// get data for page settings
			if (!$new_ps_id) {
				$sql = " SELECT MAX(ps_id) FROM ".$table_prefix."cms_pages_settings "; 
				$new_ps_id = get_db_value($sql);
			}
			// check header and footer blocks
			$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='header' "; 
			$header_block_id = get_db_value($sql);
			$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='footer' "; 
			$footer_block_id = get_db_value($sql);
  
			// add vouchers page
			if (!$page_user_vouchers) {
				$new_page_id++; 
				$vouchers_page_id = $new_page_id; 
				$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
				$sql.= $db->tosql($vouchers_page_id, INTEGER).",";
				$sql.= $db->tosql($account_module_id, INTEGER).",";
				$sql.= $db->tosql($page_order, INTEGER).",";
				$sql.= $db->tosql("user_vouchers", TEXT).",";
				$sql.= $db->tosql("MY_VOUCHERS_MSG", TEXT).")";
				$sqls[] = $sql;
  
				// add authors list block
				$new_block_id++; 
				$vouchers_block_id = $new_block_id;
				$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
				$sql.= $db->tosql($vouchers_block_id, INTEGER).",";
				$sql.= $db->tosql($account_module_id, INTEGER).",";
				$sql.= $db->tosql($block_order, INTEGER).",";
				$sql.= $db->tosql("user_vouchers", TEXT).",";
				$sql.= $db->tosql("{VOUCHERS_MSG}", TEXT).",";
				$sql.= $db->tosql("block_user_vouchers.php", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
  
				// add settings for authors listing page
				$new_ps_id++;
				$sql = "INSERT INTO ".$table_prefix."cms_pages_settings ";
				$sql.= " (ps_id,page_id,key_code,key_type,key_rule,layout_id,site_id) VALUES (";
				$sql.= $db->tosql($new_ps_id, INTEGER).",";
				$sql.= $db->tosql($vouchers_page_id, INTEGER).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
		  
				if ($header_block_id) {
					$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
					$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
					$sql.= $db->tosql($new_ps_id, INTEGER).",";
					$sql.= $db->tosql(1, INTEGER).",";
					$sql.= $db->tosql($header_block_id, INTEGER).",";
					$sql.= $db->tosql("", TEXT).",";
					$sql.= $db->tosql(1, INTEGER).")";
					$sqls[] = $sql;
				}
		  
				$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
				$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
				$sql.= $db->tosql($new_ps_id, INTEGER).",";
				$sql.= $db->tosql(3, INTEGER).",";
				$sql.= $db->tosql($vouchers_block_id, INTEGER).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
		  
				if ($footer_block_id) {
					$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
					$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
					$sql.= $db->tosql($new_ps_id, INTEGER).",";
					$sql.= $db->tosql(5, INTEGER).",";
					$sql.= $db->tosql($footer_block_id, INTEGER).",";
					$sql.= $db->tosql("", TEXT).",";
					$sql.= $db->tosql(1, INTEGER).")";
					$sqls[] = $sql;
				}
			}
  
  
			// add page voucher page 
			if (!$page_user_voucher) {
				$new_page_id++; 
				$voucher_page_id = $new_page_id; 
				$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
				$sql.= $db->tosql($voucher_page_id, INTEGER).",";
				$sql.= $db->tosql($account_module_id, INTEGER).",";
				$sql.= $db->tosql($page_order, INTEGER).",";
				$sql.= $db->tosql("user_voucher", TEXT).",";
				$sql.= $db->tosql("{VOUCHER_MSG} :: {VIEW_DETAILS_MSG}", TEXT).")";
				$sqls[] = $sql;
  
				// add authors list block
				$new_block_id++; 
				$voucher_block_id = $new_block_id;
				$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
				$sql.= $db->tosql($voucher_block_id, INTEGER).",";
				$sql.= $db->tosql($account_module_id, INTEGER).",";
				$sql.= $db->tosql($block_order, INTEGER).",";
				$sql.= $db->tosql("user_voucher", TEXT).",";
				$sql.= $db->tosql("{VOUCHER_MSG} :: {VIEW_DETAILS_MSG}", TEXT).",";
				$sql.= $db->tosql("block_user_voucher.php", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
  
				// add settings for authors listing page
				$new_ps_id++;
				$sql = "INSERT INTO ".$table_prefix."cms_pages_settings ";
				$sql.= " (ps_id,page_id,key_code,key_type,key_rule,layout_id,site_id) VALUES (";
				$sql.= $db->tosql($new_ps_id, INTEGER).",";
				$sql.= $db->tosql($voucher_page_id, INTEGER).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
		  
				if ($header_block_id) {
					$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
					$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
					$sql.= $db->tosql($new_ps_id, INTEGER).",";
					$sql.= $db->tosql(1, INTEGER).",";
					$sql.= $db->tosql($header_block_id, INTEGER).",";
					$sql.= $db->tosql("", TEXT).",";
					$sql.= $db->tosql(1, INTEGER).")";
					$sqls[] = $sql;
				}
		  
				$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
				$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
				$sql.= $db->tosql($new_ps_id, INTEGER).",";
				$sql.= $db->tosql(3, INTEGER).",";
				$sql.= $db->tosql($voucher_block_id, INTEGER).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
		  
				if ($footer_block_id) {
					$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
					$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
					$sql.= $db->tosql($new_ps_id, INTEGER).",";
					$sql.= $db->tosql(5, INTEGER).",";
					$sql.= $db->tosql($footer_block_id, INTEGER).",";
					$sql.= $db->tosql("", TEXT).",";
					$sql.= $db->tosql(1, INTEGER).")";
					$sqls[] = $sql;
				}
			}
  
			// add user voucher send page
			if (!$page_user_voucher_send) {
				$new_page_id++; 
				$voucher_send_page_id = $new_page_id; 
				$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
				$sql.= $db->tosql($voucher_send_page_id, INTEGER).",";
				$sql.= $db->tosql($account_module_id, INTEGER).",";
				$sql.= $db->tosql($page_order, INTEGER).",";
				$sql.= $db->tosql("user_voucher_send", TEXT).",";
				$sql.= $db->tosql("{SEND_VOUCHER_MSG}", TEXT).")";
				$sqls[] = $sql;
  
				// add voucher send block
				$new_block_id++; 
				$voucher_send_block_id = $new_block_id;
				$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
				$sql.= $db->tosql($voucher_send_block_id, INTEGER).",";
				$sql.= $db->tosql($account_module_id, INTEGER).",";
				$sql.= $db->tosql($block_order, INTEGER).",";
				$sql.= $db->tosql("user_voucher_send", TEXT).",";
				$sql.= $db->tosql("{SEND_VOUCHER_MSG}", TEXT).",";
				$sql.= $db->tosql("block_user_voucher_send.php", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
  
				// add settings for authors listing page
				$new_ps_id++;
				$sql = "INSERT INTO ".$table_prefix."cms_pages_settings ";
				$sql.= " (ps_id,page_id,key_code,key_type,key_rule,layout_id,site_id) VALUES (";
				$sql.= $db->tosql($new_ps_id, INTEGER).",";
				$sql.= $db->tosql($voucher_send_page_id, INTEGER).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
		  
				if ($header_block_id) {
					$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
					$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
					$sql.= $db->tosql($new_ps_id, INTEGER).",";
					$sql.= $db->tosql(1, INTEGER).",";
					$sql.= $db->tosql($header_block_id, INTEGER).",";
					$sql.= $db->tosql("", TEXT).",";
					$sql.= $db->tosql(1, INTEGER).")";
					$sqls[] = $sql;
				}
		  
				$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
				$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
				$sql.= $db->tosql($new_ps_id, INTEGER).",";
				$sql.= $db->tosql(3, INTEGER).",";
				$sql.= $db->tosql($voucher_send_block_id, INTEGER).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
		  
				if ($footer_block_id) {
					$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
					$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
					$sql.= $db->tosql($new_ps_id, INTEGER).",";
					$sql.= $db->tosql(5, INTEGER).",";
					$sql.= $db->tosql($footer_block_id, INTEGER).",";
					$sql.= $db->tosql("", TEXT).",";
					$sql.= $db->tosql(1, INTEGER).")";
					$sqls[] = $sql;
				}
			}
  
			// add user voucher cash out page
			if (!$page_user_voucher_cash) {
				$new_page_id++; 
				$voucher_cash_page_id = $new_page_id; 
				$sql = "INSERT INTO ".$table_prefix."cms_pages (page_id,module_id,page_order,page_code,page_name) VALUES (";
				$sql.= $db->tosql($voucher_cash_page_id, INTEGER).",";
				$sql.= $db->tosql($account_module_id, INTEGER).",";
				$sql.= $db->tosql($page_order, INTEGER).",";
				$sql.= $db->tosql("user_voucher_cash", TEXT).",";
				$sql.= $db->tosql("{CASH_OUT_VOUCHER_MSG}", TEXT).")";
				$sqls[] = $sql;
  
				$new_block_id++; 
				$voucher_cash_block_id = $new_block_id;
				$sql = "INSERT INTO ".$table_prefix."cms_blocks (block_id,module_id,block_order,block_code,block_name,php_script,pages_all) VALUES (";
				$sql.= $db->tosql($voucher_cash_block_id, INTEGER).",";
				$sql.= $db->tosql($account_module_id, INTEGER).",";
				$sql.= $db->tosql($block_order, INTEGER).",";
				$sql.= $db->tosql("user_voucher_cash", TEXT).",";
				$sql.= $db->tosql("{CASH_OUT_VOUCHER_MSG}", TEXT).",";
				$sql.= $db->tosql("block_user_voucher_cash.php", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
  
				// add settings for authors listing page
				$new_ps_id++;
				$sql = "INSERT INTO ".$table_prefix."cms_pages_settings ";
				$sql.= " (ps_id,page_id,key_code,key_type,key_rule,layout_id,site_id) VALUES (";
				$sql.= $db->tosql($new_ps_id, INTEGER).",";
				$sql.= $db->tosql($voucher_cash_page_id, INTEGER).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
		  
				if ($header_block_id) {
					$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
					$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
					$sql.= $db->tosql($new_ps_id, INTEGER).",";
					$sql.= $db->tosql(1, INTEGER).",";
					$sql.= $db->tosql($header_block_id, INTEGER).",";
					$sql.= $db->tosql("", TEXT).",";
					$sql.= $db->tosql(1, INTEGER).")";
					$sqls[] = $sql;
				}
		  
				$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
				$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
				$sql.= $db->tosql($new_ps_id, INTEGER).",";
				$sql.= $db->tosql(3, INTEGER).",";
				$sql.= $db->tosql($voucher_cash_block_id, INTEGER).",";
				$sql.= $db->tosql("", TEXT).",";
				$sql.= $db->tosql(1, INTEGER).")";
				$sqls[] = $sql;
		  
				if ($footer_block_id) {
					$sql = "INSERT INTO ".$table_prefix."cms_pages_blocks ";
					$sql.= " (ps_id,frame_id,block_id,block_key,block_order) VALUES (";
					$sql.= $db->tosql($new_ps_id, INTEGER).",";
					$sql.= $db->tosql(5, INTEGER).",";
					$sql.= $db->tosql($footer_block_id, INTEGER).",";
					$sql.= $db->tosql("", TEXT).",";
					$sql.= $db->tosql(1, INTEGER).")";
					$sqls[] = $sql;
				}
			}
		}


		// check menu for user voucher settings, product reviews settings, product questions settings
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

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.0.12");
	}



	if (comp_vers("5.1", $current_db_version) == 1)
	{
		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.1");
	}


