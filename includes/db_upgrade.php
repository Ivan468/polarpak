<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  db_upgrade.php                                           ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	@set_time_limit(300);

	$root_folder_path = ((isset($is_admin_path) && $is_admin_path) || (isset($is_sub_folder) && $is_sub_folder)) ? "../" : "./";
	include_once($root_folder_path."messages/".$language_code."/install_messages.php");

	// check if we already run this script during session
	$session_upgrade = get_session("session_upgrade");
	if ($session_upgrade) { return; }

	// run additional upgrade only for version 5.2
	if (comp_vers(va_version(), "5.2") != 0) { return; }

	if (!isset($message)) { $message = ""; } // save upgrade message here

	$sqls = array(); // array to save all queries to run

	// tables to delete: header_links

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

	// Execute SQL queries
	if (count($sqls)) {
		if ($db_type == "sqlsrv") {
			$db->IdentityInsert = true;
		}
		$queries_success = 0; $queries_failed = 0;
		foreach ($sqls as $sql) {
			$query_result = $db->query($sql);
			if ($query_result) {
				$queries_success++;
			} else {
				$queries_failed++;
			}
		}
		if ($db_type == "sqlsrv") {
			$db->IdentityInsert = false;
		}
		$message  .= va_constant("UPGRADE_RESULTS_MSG").":<br/>";
		if ($queries_success) {
			$message .= va_constant("SQL_SUCCESS_MSG").": ".$queries_success."<br/>";
		}
		if ($queries_failed) {
			$message .= "<div class=error>".va_constant("SQL_FAILED_MSG").": ".$queries_failed."</div>";
		}
	}

	//set_session("session_upgrade", 1);
