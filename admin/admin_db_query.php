<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_db_query.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	@set_time_limit(900);

	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/record.php");

	check_admin_security("db_management");

	$operation = get_param("operation", POST);
	$sql_query = get_param("sql_query", POST);
	$param_site_id = get_session("session_site_id");

	$errors = "";
	$win_save_expand = "";

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_db_query.html");
	$t->set_var("admin_db_query_href", "admin_db_query.php");
	$t->set_var("admin_dump_href", "admin_dump.php");

	$t->set_var("sql_query", htmlspecialchars($sql_query));

	$r = new VA_Record($table_prefix . "admins");
	$r->return_page = "admin_admins.php";
	$r->add_textbox("sql_id", INTEGER);
	$r->add_textbox("sql_name", TEXT, va_constant("NAME_MSG"));
	$r->change_property("sql_name", REQUIRED, true);
	$r->add_textbox("sql_query", TEXT, "SQL");
	$r->change_property("sql_query", REQUIRED, true);
	$r->get_form_parameters();

	$recent_queries = get_admin_settings("recent_queries");
	if ($recent_queries) { $recent_queries = json_decode($recent_queries, true); }

	if ($operation == "run" && $sql_query) {
		update_recent_queries($recent_queries, $sql_query);

		if (!$errors) {
			$db->HaltOnError = "no";
			$time_start = microtime_float();
			$db->query($sql_query);
			$time_end = microtime_float();
			if(strlen($db->error_desc)) {
				$r->errors = $db->error_desc. "<br>";
			} 
		}

		if (!$errors) {
			$execution_time = ($time_end - $time_start);
			$execution_time = round($execution_time, 2);
			if ($execution_time == 0) {
				$t->set_var("execution_time", "0.00");
			} else {
				$t->set_var("execution_time", $execution_time);
			}
			$query_info = $db->info();
			if ($query_info) {
				$t->set_var("query_info", $query_info);
				$t->parse("query_info_block", true);
			}

			if ($db->next_record()) {
				$titles = array();
				foreach ($db->Record as $column_title => $column_value) {
					if (!is_numeric($column_title)) {
						$titles[] = $column_title;
						$t->set_var("column_title", $column_title);
						$t->parse("titles", true);
					}
				}
				do {
					for ($c = 0; $c < sizeof($titles); $c++) {
						$column_value = $db->f($titles[$c]);
						if ($column_value instanceof DateTime) {
							$t->set_var("column_value", va_date($db->DatetimeMask, $column_value));
						} else if (gettype($column_value) == "object") {
							$t->set_var("column_value", "[OBJECT]");
						} else if (strlen($column_value)) {
							$t->set_var("column_value", htmlspecialchars($column_value));
						} else {
							$t->set_var("column_value", "&nbsp;");
						}
						$t->parse("cols", true);
					}
					$t->parse("rows", true);
					$t->set_var("cols", "");
				} while ($db->next_record());
				$t->parse("query_data", false);
			}
			$t->parse("query_result", false);
		}
		if ($errors) {
			$t->set_var("errors_list", $errors);
			$t->parse("errors", false);
		}
	} else if ($operation == "save") {
		$is_valid = $r->validate();
		$sql_id = $r->get_value("sql_id");

		if ($is_valid) {
			// save query
			$sql_data = array(
				"name" => $r->get_value("sql_name"),
				"query" => $r->get_value("sql_query"),
			);

			if ($sql_id) {
				$sql_data["admin_id_modified_by"] = get_session("session_admin_id");
				$sql_data["date_modified"] = time();

				$sql  = " UPDATE ".$table_prefix."global_settings ";
				$sql .= " SET setting_value=".$db->tosql(json_encode($sql_data), TEXT);
				$sql .= " WHERE setting_type='sql' ";
				$sql .= " AND setting_name=".$db->tosql($sql_id, TEXT);
				$updated = $db->query($sql);
				if ($updated) {
					$r->success_message = RECORD_UPDATED_MSG;
				} else {
					$r->errors = DATABASE_ERROR_MSG;
				}
			} else {
				$sql_id = time();
				$sql_data["admin_id_added_by"] = get_session("session_admin_id");
				$sql_data["date_added"] = time();

				$sql  = " INSERT INTO ".$table_prefix."global_settings (site_id, setting_type, setting_name, setting_value) VALUES (";
				$sql .= $db->tosql($site_id, TEXT) .", ";
				$sql .= "'sql', ";
				$sql .= $db->tosql($sql_id, TEXT) .", ";
				$sql .= $db->tosql(json_encode($sql_data), TEXT) .") ";
				$inserted = $db->query($sql);
				if ($inserted) {
					$r->success_message = RECORD_ADDED_MSG;
					$r->set_value("sql_id", $sql_id);
				} else {
					$r->errors = DATABASE_ERROR_MSG;
				}
			}
		} else {
			$win_save_expand = " expand-open ";
			if ($sql_id) {
				$win_save_expand .= " sql-exists ";
			} else {
				$win_save_expand .= " sql-new ";
			}
		}

	} else if ($operation == "delete") {
		$sql_id = $r->get_value("sql_id");
		$sql  = " DELETE FROM ".$table_prefix."global_settings ";
		$sql .= " WHERE setting_type='sql' ";
		$sql .= " AND setting_name=".$db->tosql($sql_id, TEXT);
		$deleted = $db->query($sql);
		if ($deleted) {
			$r->success_message = RECORD_DELETED_MSG;
		} else {
			$r->errors = DATABASE_ERROR_MSG;
		}
	}
	// get and parsed saved quries
	$sql  = " SELECT setting_name, setting_value FROM ".$table_prefix."global_settings ";
	$sql .= " WHERE setting_type='sql' ORDER BY setting_name ";
	$db->query($sql);
	if ($db->next_record()) {
		do {
			$list_id = $db->f("setting_name");
			$list_data = $db->f("setting_value");
			$sql_data = json_decode($list_data, true);
			$list_name = $sql_data["name"];
			$t->set_var("list_id", htmlspecialchars($list_id));
			$t->set_var("list_data", htmlspecialchars($list_data));
			$t->set_var("list_name", htmlspecialchars($list_name));

			$t->parse("saved_queries", true);	
		} while ($db->next_record());
	} else {
		$t->parse("no_saved_queries", false);
	}


	$r->set_form_parameters();
	$t->set_var("win_save_expand", $win_save_expand);

	if (is_array($recent_queries)) {
		foreach($recent_queries as $query_id => $recent_query) {
			$recent_query = str_replace("\\", "\\\\", $recent_query);
			$recent_query = str_replace("\"", "\\\"", $recent_query);
			$recent_query = str_replace("\r", "\\r", $recent_query);
			$recent_query = str_replace("\n", "\\n", $recent_query);
			$t->set_var("query_id", $query_id);
			$t->set_var("recent_query", $recent_query);
			$t->parse("queries", true);
		}
		if ($sql_query) {
			$t->set_var("current_query_id", $query_id);
		} else {
			$t->set_var("current_query_id", ++$query_id);
		}
	} else {
		$t->set_var("query_id", 0);
		$t->set_var("current_query_id", 0);
		$t->set_var("prev_disabled", "disabled");
	}

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");


function microtime_float()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

function update_recent_queries(&$recent_queries, $sql_query)
{
	$recent_records = 20;
	if (!is_array($recent_queries)) {
		$recent_queries = array();
	} 
	foreach ($recent_queries as $key => $recent_query) {
		if ($recent_query == $sql_query) {
			unset($recent_queries[$key]);
			$recent_queries = array_values($recent_queries);
		}
	}
	while (sizeof($recent_queries) >= $recent_records) {
		array_shift($recent_queries);
	}
	array_push($recent_queries, $sql_query);
	update_admin_settings(array("recent_queries" => json_encode($recent_queries)));
}

?>