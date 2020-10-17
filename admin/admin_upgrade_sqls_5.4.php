<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_upgrade_sqls_5.4.php                               ***
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

	// new support statuses fields
	$support_statuses_type = false; 
	$support_statuses_order = false; 
	$support_statuses_default = false;
	$support_statuses_update_status = false;
	$support_statuses_keep_assigned = false;
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
		} else if ($field_info["name"] == "is_keep_assigned") {
			$support_statuses_keep_assigned = true;
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

	if (comp_vers("5.3.1", $current_db_version) == 1)
	{
		if (!$support_statuses_default) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_statuses ADD is_default TINYINT DEFAULT '0'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_statuses ADD is_default TINYINT DEFAULT '0'",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_statuses ADD is_default SMALLINT DEFAULT '0'",
				"access"  => "ALTER TABLE " . $table_prefix . "support_statuses ADD is_default BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "support_statuses SET is_default=0 ";
		}

		if (!$support_statuses_update_status) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_statuses ADD is_update_status TINYINT DEFAULT '1'",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_statuses ADD is_update_status TINYINT DEFAULT '1'",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_statuses ADD is_update_status SMALLINT DEFAULT '1'",
				"access"  => "ALTER TABLE " . $table_prefix . "support_statuses ADD is_update_status BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
			$sqls[] = " UPDATE " . $table_prefix . "support_statuses SET is_update_status=1 ";
		}

		// add new status type field and update it
		if (!$support_statuses_type) {
			if ($db_type == "mysql") {
				$sqls[] = "ALTER TABLE " . $table_prefix . "support_statuses ADD COLUMN status_type VARCHAR(32) AFTER status_name ";
			} else {
				$sqls[] = "ALTER TABLE " . $table_prefix . "support_statuses ADD COLUMN status_type VARCHAR(32) ";
			}

			$sqls[] = " UPDATE " . $table_prefix . "support_statuses SET status_type='NEW',is_default=1 WHERE is_user_new=1 ";

			// check user status for reply message
			$user_reply_status_id = "";
			$sql = " SELECT status_id FROM " . $table_prefix . "support_statuses WHERE is_user_reply=1 ";
			$db->query($sql);
			if($db->next_record()) {
				$user_reply_status_id = $db->f("status_id");
			} else {
				$sql = " SELECT status_id FROM " . $table_prefix . "support_statuses WHERE status_name='TICKET_CUSOMER_REPLY_MSG' ";
				$db->query($sql);
				if($db->next_record()) {
					$user_reply_status_id = $db->f("status_id");
				}
			}
			if ($user_reply_status_id) {
				$sqls[] = " UPDATE " . $table_prefix . "support_statuses SET status_type='USER_REPLY', is_default=1 WHERE status_type IS NULL AND status_id=".intval($user_reply_status_id);
				$sqls[] = " UPDATE " . $table_prefix . "support_statuses SET status_type='TICKET_USER_REPLY_MSG' WHERE status_name='TICKET_CUSOMER_REPLY_MSG' ";
			}

			// check admin status for reply message
			$admin_reply_status_id = "";
			$sql = " SELECT status_id FROM " . $table_prefix . "support_statuses WHERE is_admin_reply=1 ";
			$db->query($sql);
			if($db->next_record()) {
				$admin_reply_status_id = $db->f("status_id");
			} else {
				$sql = " SELECT status_id FROM " . $table_prefix . "support_statuses WHERE status_name='TICKET_ANSWERED_MSG' ";
				$db->query($sql);
				if($db->next_record()) {
					$admin_reply_status_id = $db->f("status_id");
				}
			}
			if ($admin_reply_status_id) {
				$sqls[] = " UPDATE " . $table_prefix . "support_statuses SET status_type='ADMIN_REPLY', is_default=1 WHERE status_type IS NULL AND status_id=".intval($admin_reply_status_id);
			}

			// check admin assign status for reply message
			$assign_status_id = "";
			$sql = " SELECT status_id FROM " . $table_prefix . "support_statuses WHERE is_reassign=1 ";
			$db->query($sql);
			if($db->next_record()) {
				$assign_status_id= $db->f("status_id");
			} else {
				$sql = " SELECT status_id FROM " . $table_prefix . "support_statuses WHERE status_name='TICKET_ASSIGNED_MANAGER_MSG' ";
				$db->query($sql);
				if($db->next_record()) {
					$assign_status_id= $db->f("status_id");
				}
			}
			if ($assign_status_id) {
				$sqls[] = " UPDATE " . $table_prefix . "support_statuses SET status_type='ADMIN_ASSIGNMENT', is_default=1 WHERE status_type IS NULL AND status_id=".intval($assign_status_id);
			}

			// check close status for reply message
			$close_status_id = "";
			$sql = " SELECT status_id FROM " . $table_prefix . "support_statuses WHERE is_closed=1 ";
			$db->query($sql);
			if($db->next_record()) {
				$close_status_id = $db->f("status_id");
			} else {
				$sql = " SELECT status_id FROM " . $table_prefix . "support_statuses WHERE status_name='TICKET_CLOSED_MSG' ";
				$db->query($sql);
				if($db->next_record()) {
					$close_status_id = $db->f("status_id");
				}
			}
			if ($close_status_id) {
				$sqls[] = " UPDATE " . $table_prefix . "support_statuses SET status_type='CLOSE', is_default=1 WHERE status_type IS NULL AND status_id=".intval($close_status_id);
			}

			// set status type if there are more statuses with the same type
			$sqls[] = " UPDATE " . $table_prefix . "support_statuses SET status_type='USER_REPLY' WHERE status_type IS NULL AND is_user_reply=1 ";
			$sqls[] = " UPDATE " . $table_prefix . "support_statuses SET status_type='ADMIN_REPLY' WHERE status_type IS NULL AND is_admin_reply=1 ";
			$sqls[] = " UPDATE " . $table_prefix . "support_statuses SET status_type='ADMIN_REPLY' WHERE status_type IS NULL AND (status_name LIKE '%REQUEST%' OR status_name LIKE '%Awaiting%') ";
			$sqls[] = " UPDATE " . $table_prefix . "support_statuses SET status_type='ADMIN_ASSIGNMENT' WHERE status_type IS NULL AND is_reassign=1 ";
			$sqls[] = " UPDATE " . $table_prefix . "support_statuses SET status_type='CLOSE' WHERE status_type IS NULL AND is_closed=1 ";
			$sqls[] = " UPDATE " . $table_prefix . "support_statuses SET status_type='CLOSE' WHERE status_type IS NULL AND status_name LIKE '%Close%' ";

			// set other action for all other statuses
			$sqls[] = " UPDATE " . $table_prefix . "support_statuses SET status_type='OTHER_ACTION' WHERE status_type IS NULL ";
		}

		// delete old support status
		$sqls[] = " DELETE FROM " . $table_prefix . "support_statuses WHERE is_add_knowledge=1 AND is_user_new<>1 AND is_user_reply<>1 AND is_admin_reply<>1 AND is_reassign<>1 AND is_closed<>1 ";

		// add new Forward status
		$sql = " SELECT status_id FROM " . $table_prefix . "support_statuses WHERE status_name LIKE '%FORWARD%' ";
		$db->query($sql);
		if (!$db->next_record()) {
			$sqls[] = "INSERT INTO ".$table_prefix."support_statuses (status_name, status_type, show_for_user, is_internal, is_default, is_update_status) VALUES ('TICKET_FORWARDED_MSG', 'FORWARD', 1, 1, 1, 0)" ;
		}

		if (!$support_messages_forward) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_messages ADD forward_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_messages ADD forward_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_messages ADD forward_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_messages ADD forward_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}

		if (!$support_statuses_admin_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_statuses ADD admin_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_statuses ADD admin_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_statuses ADD admin_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_statuses ADD admin_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}

		if (!$support_statuses_manager_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_statuses ADD manager_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_statuses ADD manager_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_statuses ADD manager_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_statuses ADD manager_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}

		if (!$support_statuses_assign_to_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_statuses ADD assign_to_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_statuses ADD assign_to_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_statuses ADD assign_to_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_statuses ADD assign_to_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}

		if (!$support_statuses_user_mail) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_statuses ADD user_mail TEXT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_statuses ADD user_mail TEXT",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_statuses ADD user_mail TEXT",
				"access"  => "ALTER TABLE " . $table_prefix . "support_statuses ADD user_mail LONGTEXT",
			);
			$sqls[] = $sql_types[$db_type];
		}

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.3.1");
	}


	if (comp_vers("5.3.2", $current_db_version) == 1)
	{
		// drop some old never used fields in support_statuses table
		foreach ($support_statuses_drop as $column_name => $column_exists) {
			if ($column_exists) {
				$sqls[] = "ALTER TABLE " . $table_prefix . "support_statuses DROP COLUMN ".$column_name;
			}
		}

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

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.3.2");
	}


	if (comp_vers("5.3.3", $current_db_version) == 1)
	{
		if (!$support_statuses_keep_assigned) {
			$sql_types = array(
				"mysql"   => "ALTER TABLE " . $table_prefix . "support_statuses ADD is_keep_assigned TINYINT ",
				"sqlsrv"  => "ALTER TABLE " . $table_prefix . "support_statuses ADD is_keep_assigned TINYINT ",
				"postgre" => "ALTER TABLE " . $table_prefix . "support_statuses ADD is_keep_assigned SMALLINT ",
				"access"  => "ALTER TABLE " . $table_prefix . "support_statuses ADD is_keep_assigned BYTE ",
			);
			$sqls[] = $sql_types[$db_type];
		}
		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.3.3");
	}

	if (comp_vers("5.4", $current_db_version) == 1)
	{
		// add parent_coupon_id field to coupons table if it was missed
		$coupons_parent_coupon_id = false; 
		$fields = $db->get_fields($table_prefix."coupons");
		foreach ($fields as $id => $field_info) {
			if ($field_info["name"] == "parent_coupon_id") {
				$coupons_parent_coupon_id = true;
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

		run_queries($sqls, $queries_success, $queries_failed, $errors, "5.4");
	}
