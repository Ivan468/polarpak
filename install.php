<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  install.php                                              ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$script_start_time = time();	
	$script_max_time = 3; // how many seconds script could run before re-call
	error_reporting(E_ALL);

	@set_time_limit(900);
	@ini_set("magic_quotes_runtime", 0);

	// version information
	$version_name = "shop";
	$version_type = "enterprise";
	$version_number = "5.6";

	session_start();
	
	include_once("./includes/constants.php");
	include_once("./includes/common_functions.php");
	include_once("./includes/va_functions.php");
	$va_messages = array();
	$language_code = get_language(); 
	include_once("./messages/" . $language_code . "/messages.php");
	include_once("./messages/" . $language_code . "/install_messages.php");
	include_once("./messages/" . $language_code . "/admin_messages.php");
	include_once("./includes/date_functions.php");
	include_once("./includes/record.php");
	include_once("./includes/template.php");
	if (file_exists("./includes/var_definition.php") ) {
		include_once("./includes/var_definition.php");
	} 

	$eol = get_eol();
	$step = get_param("step");
	$is_install = get_session("session_install");
	$install_finished = get_session("session_install_finished");
	$db_library = get_param("db_library"); $db_type = "";
	if ($db_library) {
		$db_file = get_db_file($db_library);
		if ($db_file) {
			include_once("./includes/".$db_file);
			$db_host       = get_param("db_host");
			$db_user       = get_param("db_user");
			$db_password   = get_param("db_password");
			$db_name       = get_param("db_name");
			$db_port       = get_param("db_port");
			$db_persistent = get_param("db_persistent");
			$db_type       = get_param("db_type");
			if (!$db_type) {
				if ($db_library == "mysql" || $db_library == "mysqli" || $db_library == "mysqlold") {
					$db_type = "mysql";
				} else {
					$db_type = $db_library;
				}
			}
			$table_prefix   = "va_";
			$db = new VA_SQL($db_host, $db_user, $db_password, $db_name, $db_port, $db_persistent, $db_type); 
		}
	} elseif (($install_finished || $step >= 3) && isset($db_lib) && strlen($db_lib)) {
		$db_file = get_db_file($db_lib);
		if ($db_file) {
			include_once("./includes/".$db_file);
			$db = new VA_SQL($db_host, $db_user, $db_password, $db_name, $db_port, $db_persistent, $db_type); 
		}
	}

	// check ajax call
	$ajax = get_param("ajax");
	$type = get_param("type");
	if ($ajax && $type == "populate") {
		$errors = "";
		$dump_line = get_param("dump_line");
		$db_test_data = get_param("db_test_data");
		$db->HaltOnError = "no"; // don't show errors

		// build path to dump file
		$is_access = ($db_type == "access");
		$is_db2 = ($db_type == "db2");
		$is_postgre = ($db_type == "postgre");
		$db_filename = $db_type . "_viart_$version_name.sql";
		$dump_file = "./db/" . $db_filename;
		if (!file_exists($dump_file)) {
			$dump_file_error = str_replace("{file_name}", $dump_file, va_message("DUMP_FILE_ERROR"));
			$errors = $dump_file_error;
		}
		if (!$errors) {
			$fp = @fopen($dump_file, "rb");
			if ($fp) {
				// pass already processed lines
				$line_position = 0;
				$sql_identity_on = "";
				while ($line_position < $dump_line && !feof($fp)) {
					$line_position++;
					$sql_string = fgets($fp);
					// check if we need to activate identity for SQL Server
					if (preg_match("/SET\s+IDENTITY_INSERT\s+[\w\-]+\s+ON/i", $sql_string)) {
						$sql_identity_on = preg_replace("/;\s*$/i", "", $sql_string);
					} else if (preg_match("/SET\s+IDENTITY_INSERT\s+[\w\-]+\s+OFF/i", $sql_string)) {
						$sql_identity_on = "";
					}
				}
				// run identity sql
				if ($sql_identity_on) {
					$db->query($sql_identity_on);
				}

				// vars to lock tables and disable keys
				$table_locked = ""; $table_disabled_keys = ""; $table_insert = "";
				$sql = ""; $success_sqls = 0; $failed_sqls = 0; $ignored_sqls = 0; $stop_script = false;
				while (!$stop_script && !feof($fp)) {
					$line_position++; $dump_line++;
					$sql_string = fgets($fp);
					if (preg_match ("/\;\s*$/i", $sql_string)) {
						$sql_string = preg_replace("/;\s*$/i", "", $sql_string);
						$sql .= $sql_string;
						if (preg_match("/^\s*DROP\s+/i", $sql)) {
							$drop_table_syntax = true;
						} else {
							$drop_table_syntax = false;
						}
						$sql = trim($sql);
						if ($is_access or $is_db2) {
							$sql = str_replace("\\n", "\n", $sql);
							$sql = str_replace("\\t", "\t", $sql);
							$sql = str_replace("\\r", "\r", $sql);
						}
						// check insert query
						$table_insert = "";
						if (preg_match("/^insert\s+into\s+`([^`]+)`\s+/i", $sql, $match)) {
							$table_insert = $match[1];
						} elseif (preg_match("/^insert\s+into\s+\[([^\]]+)\]\s+/i", $sql, $match)) {
							$table_insert = $match[1];
						} elseif (preg_match("/^insert\s+into\s+([^\s]+)\s+/i", $sql, $match)) {
							$table_insert = $match[1];
						}

						// check for test data
						$execute_query = true;
						if (!$db_test_data) {
							for ($td = 0; $td < sizeof($test_data_tables); $td++) {
								$test_data_table = $test_data_tables[$td];
								if (preg_match("/^insert\s+into\s+va_" .$test_data_table. "\s+/i", $sql)) {
									$execute_query = false;
									break;
								}
							}
						}
						if ($execute_query) {
							// block to run special queries to lock table and disable keys for faster insert
							if ($db_type == "mysql" || $db_type == "mysqli" || $db_type == "mysqlold") {
								if ($table_insert != $table_locked) {
									if ($table_disabled_keys) {
										$db->query("ALTER TABLE ".$table_disabled_keys." ENABLE KEYS");
									}
									if ($table_locked) {
										$db->query("UNLOCK TABLES");
									}
									if ($table_insert) {
										$db->query("LOCK TABLES ".$table_insert." WRITE");
										$table_locked = $table_insert;
										$db->query("ALTER TABLE ".$table_insert." DISABLE KEYS");
										$table_disabled_keys = $table_insert;
									}
								}
							}

							$db->query($sql);
							if ($db->error_desc) {
								if (!$drop_table_syntax) {
									$failed_sqls++;
									//$r->errors .= $db->error_desc . "<br/>";
								}
							} else {
								$success_sqls++;
							}
						} else {
							$ignored_sqls++;
						}
						
						$sql = "";
						// check time script already run
						$script_time = time();
						if (($script_time - $script_start_time) > $script_max_time) {
							// after $script_max_time seconds send script response to recall script again
							$stop_script = true;
							// check if we need to enable keys and unlock tables for MySQL
							if ($db_type == "mysql" || $db_type == "mysqli" || $db_type == "mysqlold") {
								if ($table_disabled_keys) {
									$db->query("ALTER TABLE ".$table_disabled_keys." ENABLE KEYS");
								}
								if ($table_locked) {
									$db->query("UNLOCK TABLES");
								}
							}
						}
					} else {
						$sql .= $sql_string;
					}

				}
				fclose($fp);
				$eof = ($stop_script) ? 0 : 1;
				echo json_encode(array(
					"result" => "ok",
					"eof" => intval($eof),
					"dump_line" => $dump_line,
					"failed_sqls" => $failed_sqls,
					"success_sqls" => $success_sqls,
					"ignored_sqls" => $ignored_sqls,
				));
				return;
			} else {
				$errors = "Can't read the file: " . $dump_file;
			}
		}

		echo json_encode(array(
			"result" => "error",
			"errors" => $errors,
		));
		return;
	}

	
	$t = new VA_Template("./templates/user/");
	$t->set_file("main", "install.html");
	$t->set_var("CHARSET", va_message("CHARSET"));
	$t->set_var("install_href", "install.php");
	$t->set_var("eula_url", "eula.php");
	$t->set_var("select_date_format_href", "select_date_format.php");
	$t->set_var("css_file", "styles/installation.css");
	$t->set_var("step_1", "");
	$t->set_var("step_2", "");
	$t->set_var("step_3", "");
	$t->set_var("step_4", "");

	$operation = get_param("operation");
	if (!$step) $step = 1;

	if ($operation == "back" && $step > 1) {
		$step--;
	}
	
	$r = new VA_Record("", "step_" . $step);
	if ($install_finished) {
		$step = 4;
		$operation = "";
	} elseif (defined("INSTALLED") && INSTALLED && !$is_install) {
		$step = 1;
		$r->set_record_name("step_1");
		$r->errors = va_message("INSTALL_FINISHED_ERROR") . "<br/>";
	} elseif (!$is_install) {
		set_session("session_install", 1);
		$is_install = 1;
	}

	$r->add_hidden("operation", TEXT);

	if ( file_exists("./includes/var_definition.php") ) {
		if ( !is_writable ("./includes/var_definition.php") ) {
			$r->errors = va_message("WRITE_FILE_ERROR") . "<br/>";
		}
	} elseif ( !is_writable ("./includes/") ) {
		$r->errors = va_message("WRITE_DIR_ERROR") . "<br/>";
	}

	$db_libraries = array (
		array("", va_message("SELECT_PHP_LIB_MSG")),
		array("mysql",  "MySQLi"),
		array("mysqlold",   "MySQL OLD (Deprecated)"),
		array("postgre", "Postgre"),
		array("sqlsrv",  "SQL Server"),
		array("odbc",    "ODBC"),
	);

	$db_types = array (
		array("", va_message("SELECT_DB_TYPE_MSG")),
		array("mysql",   "MySQL"),
		array("postgre", "Postgre"),
		array("sqlsrv",  "SQL Server"),
		array("access",  "Access"),
	);

	$test_data_tables = array(
		"ads_features", "ads_images", "ads_items", "ads_properties",

		"articles", "articles_assigned", "articles_images", "articles_related", "articles_reviews",

		"forum", "forum_messages",

		"categories", "features", "items", "items_accessories", "items_categories", "items_downloads", "items_downloads_statistic",
		"items_images", "items_prices", "items_properties", "items_properties_values", "items_relates", "items_serials",
		"releases",  "release_changes", "reviews",

		"support", "support_attachments", "support_messages",
		
		"users"
	);


	// step 1 parameters
	$r->add_select("db_library", TEXT, $db_libraries, va_message("DB_PHP_LIB_FIELD"));
	$r->change_property("db_library", REQUIRED, true);
	$r->add_select("db_type", TEXT, $db_types, va_message("DB_TYPE_FIELD"));
	$r->add_textbox("db_host", TEXT, va_message("DB_HOST_FIELD"));
	$r->change_property("db_host", DEFAULT_VALUE, "localhost");
	$r->change_property("db_host", REQUIRED, true);
	$r->add_textbox("db_name", TEXT, va_message("DB_NAME_FIELD"));
	$r->change_property("db_name", REQUIRED, true);
	$r->add_textbox("db_port", TEXT);
	$r->add_textbox("db_user", TEXT);
	$r->add_textbox("db_password", TEXT);
	$r->add_checkbox("db_persistent", INTEGER);
	$r->add_checkbox("db_create_db", INTEGER);
	$r->add_checkbox("db_populate", INTEGER);
	$r->change_property("db_populate", DEFAULT_VALUE, 1);
	$r->add_checkbox("db_test_data", INTEGER);
	$r->change_property("db_test_data", DEFAULT_VALUE, 1);
	$r->add_checkbox("agreed_to_eula", INTEGER);
	if ($step == 1) {
		$r->change_property("agreed_to_eula", BEFORE_VALIDATE, "check_eula");
	}

	// step 2 parameters
	$r->add_textbox("site_name", TEXT, va_message("SITE_NAME_MSG"));
	$r->add_textbox("site_url", TEXT, va_message("SITE_URL_MSG"));
	$r->add_textbox("admin_email", TEXT, va_message("ADMIN_EMAIL_FIELD"));
	$r->add_textbox("admin_login", TEXT, va_message("ADMIN_LOGIN_FIELD"));
	$r->add_textbox("admin_password", TEXT, va_message("ADMIN_PASS_FIELD"));
	$r->add_textbox("admin_password_confirm", TEXT, va_message("ADMIN_CONF_FIELD"));

	$r->add_textbox("datetime_show_format", TEXT, va_message("DATETIME_SHOWN_FIELD"));
	$r->add_textbox("date_show_format",     TEXT, va_message("DATE_SHOWN_FIELD"));
	$r->add_textbox("datetime_edit_format", TEXT, va_message("DATETIME_EDIT_FIELD"));
	$r->add_textbox("date_edit_format",     TEXT, va_message("DATE_EDIT_FIELD"));

	if ($step == "1" && !$r->errors)
	{
		$r->get_form_values();
		$db_library = $r->get_value("db_library");
		if ($db_library == "odbc") {
			$r->change_property("db_type", REQUIRED, true);
		} else if ($db_library == "mysql" || $db_library == "mysqli") {
			$r->set_value("db_type", "mysql");
		} else if ($db_library == "postgre") {
			$r->set_value("db_type", "postgre");
		} else if ($db_library == "sqlsrv") {
			$r->set_value("db_type", "sqlsrv");
		}
		$db_type = $r->get_value("db_type");
		if ($operation == "save") {
			$is_valid = $r->validate();
			if ($r->get_value("db_test_data") && !$r->get_value("db_populate")) {
				$r->errors .= str_replace("{POPULATE_DB_FIELD}", va_message("POPULATE_DB_FIELD"), va_message("TEST_DATA_ERROR"));
			}
			if (!$r->errors) {
				$db->HaltOnError = "no";
				if (!$db->check_lib()) {
					$library_error = str_replace("{db_library}", $r->get_value("db_library"), va_message("DB_LIBRARY_ERROR"));
					$r->errors  = $library_error . "<br/>";
				} 
			}
			// try create database if appropriate option was selected
			if (!$r->errors && $r->get_value("db_create_db") && strlen($db->DBDatabase)) {
				if (!$db->create_database()) {
					if (strlen($db->Error)) {
						$r->errors = $db->error_desc . "<br/>";
					} else {
						$r->errors = "Create Database: unknown error occured.<br/>";
					}
				}
			}
			// try connect to database
			if (!$r->errors) {
				if (!$db->db_connect(true)) {
					if (strlen($db->error_desc)) {
						$r->errors = $db->error_desc . "<br/>";
					} else {
						$r->errors = va_message("DB_CONNECT_ERROR") . "<br/>";
					}
				} 
			}
			// get file to populate data if appropriate option selected
			$dump_file = ""; $dump_size = 0; $dump_line = 0; $dump_lines = 0; $dump_sqls = 0;
			$success_sqls = 0; $failed_sqls = 0;
			if (!$r->errors && $r->get_value("db_populate")) {
				$is_access = ($db_type == "access");
				$is_db2 = ($db_type == "db2");
				$is_postgre = ($r->get_value("db_library") == "postgre");
				$db_filename = $db_type."_viart_$version_name.sql";
				$dump_file = "./db/" . $db_filename;
				if (!file_exists($dump_file)) {
					$dump_file_error = str_replace("{file_name}", $dump_file, va_message("DUMP_FILE_ERROR"));
					$r->errors = $dump_file_error;
				} else {
					$fp = @fopen($dump_file, "rb");
					if ($fp) {
						$dump_size = filesize($dump_file);
						while (!feof($fp)) {
							$sql_string = fgets($fp);
							if (preg_match ("/\;\s*$/i", $sql_string)) {
								$dump_sqls++;
							}
							$dump_lines++;
						}
						fclose($fp);
					} else {
						$r->errors = "Can't read the file: " . $dump_file;
					}
				}
			}

			// final check for errors
			if (!$r->errors) { // move to step 2
				$operation = "";
				$step = 2;

				$t->set_var("dump_file", htmlspecialchars($dump_file));
				$t->set_var("dump_size", htmlspecialchars($dump_size));
				$t->set_var("dump_sqls", htmlspecialchars($dump_sqls));
				$t->set_var("dump_line", htmlspecialchars($dump_line));
				$t->set_var("dump_lines", htmlspecialchars($dump_lines));
				$t->set_var("success_sqls", htmlspecialchars($success_sqls));
				$t->set_var("failed_sqls", htmlspecialchars($failed_sqls));
				$t->set_var("db_test_data", htmlspecialchars($r->get_value("db_test_data")));

				if ($dump_line < $dump_lines) {
					$t->set_var("settings_class", "hidden-object");
					$t->set_var("populate_class", "populate-table");
				} else {	
					$t->set_var("settings_class", "settings-table");
					$t->set_var("populate_class", "hidden-object");
				}

				$r->set_record_name("step_" . $step);
			}
		} elseif (!$operation) {
			$r->set_default_values();
		}
	}


	if ($step == "2")
	{
		$r->change_property("db_library",    CONTROL_TYPE, HIDDEN);
		$r->change_property("db_type",       CONTROL_TYPE, HIDDEN);
		$r->remove_property("db_host",       DEFAULT_VALUE);
		$r->change_property("db_persistent", CONTROL_TYPE, HIDDEN);
		$r->change_property("db_populate",   CONTROL_TYPE, HIDDEN);

		$http_host = getenv("HTTP_HOST");
		$server_port = getenv("SERVER_PORT");
		$request_uri = getenv("REQUEST_URI");
		$script_name = getenv("SCRIPT_NAME");
		
		$path = ($request_uri) ? $request_uri : $script_name;
		$path = dirname($path);
		$path = str_replace("\\", "/", $path);
		if ($path != "/") { $path .= "/"; }

		$site_url  = "http://";
		$site_url .= ($server_port == 80 || !$server_port) ?  $http_host : $http_host . ":" . $server_port;
		$site_url .= $path;

		$r->change_property("site_name",              REQUIRED, true);
		$r->change_property("site_url",               REQUIRED, true);
		$r->change_property("site_url",               DEFAULT_VALUE, $site_url);
		$r->change_property("admin_email",            REQUIRED, true);
		$r->change_property("admin_email",            REGEXP_MASK, EMAIL_REGEXP);
		$r->change_property("admin_login",            REQUIRED, true);
		$r->change_property("admin_login",            MIN_LENGTH, 3);
		$r->change_property("admin_password",         REQUIRED, true);
		$r->change_property("admin_password",         MIN_LENGTH, 3);
		$r->change_property("admin_password",         MATCHED, "admin_password_confirm");

		$r->change_property("datetime_show_format",   REQUIRED, true);
		$r->change_property("datetime_show_format",   DEFAULT_VALUE, "D MMM YYYY, h:mm AM");
		$r->change_property("date_show_format",       REQUIRED, true);
		$r->change_property("date_show_format",       DEFAULT_VALUE, "D MMM YYYY");
		$r->change_property("datetime_edit_format",   REQUIRED, true);
		$r->change_property("datetime_edit_format",   DEFAULT_VALUE, "YYYY-MM-DD HH:mm:ss");
		$r->change_property("date_edit_format",       REQUIRED, true);
		$r->change_property("date_edit_format",       DEFAULT_VALUE, "YYYY-MM-DD");

		$r->get_form_values();

		if ($operation == "save" && !$r->errors) {
			// check connect and for existings tables
			$db->HaltOnError = "no";
			if (!$db->db_connect()) {
				$r->errors  = va_message("DB_CONNECT_ERROR") . "<br/>";
				if ($db->error_desc) {
					$r->errors .= $db->error_desc;
				}
			} else {
				if ( !$db->query("SELECT * FROM " . $table_prefix . "global_settings") ) {
					$db_table_error = str_replace("{table_name}", "global_settings", va_message("DB_TABLE_ERROR"));
					$r->errors .= $db_table_error . "<br/>";
					if ($db->error_desc) {
						$r->errors .= "<b>" . va_message("DATABASE_ERROR_MSG") . "</b>: " . $db->error_desc. ".<br/>";
					}
				}
				if ( !$db->query("SELECT * FROM " . $table_prefix . "admins") ) {
					$db_table_error = str_replace("{table_name}", "admins", va_message("DB_TABLE_ERROR"));
					$r->errors .= $db_table_error . "<br/>";
					if ($db->error_desc) {
						$r->errors .= "<b>" . va_message("DATABASE_ERROR_MSG") . "</b>: " . $db->error_desc. ".<br/>";
					}
				}
			}
		}

		if ($operation == "save" && !$r->errors) {
			$is_valid = $r->validate();

			if ($is_valid) {
				//$db->HaltOnError = "yes";

				// build var_definition.php file
				build_config();

				// set site name 
				$sql = "DELETE FROM ".$table_prefix."sites ";
				$db->query($sql);

				if ($db_library == "sqlsrv") {
					$sql = "SET IDENTITY_INSERT ".$table_prefix."sites ON";
					$db->query($sql);
				}
				$sql  = " INSERT INTO " . $table_prefix . "sites (site_id, short_name, site_name) VALUES (";
				$sql .= "1, ";
				$sql .= $db->tosql($r->get_value("site_name"), TEXT) . ", ";
				$sql .= $db->tosql($r->get_value("site_name"), TEXT) . ") ";
				$db->query($sql);
				if ($db_library == "sqlsrv") {
					$sql = "SET IDENTITY_INSERT ".$table_prefix."sites OFF";
					$db->query($sql);
				}

				// set site url
				$site_url = $r->get_value("site_url");
				if (strlen($site_url) && substr($site_url, strlen($site_url) - 1) != "/") {
					$site_url .= "/";
					$r->set_value("site_url", $site_url);
				}

				$sql = "DELETE FROM " . $table_prefix . "global_settings WHERE setting_type='global' AND setting_name='site_url'";
				$db->query($sql);
				$sql  = " INSERT INTO " . $table_prefix . "global_settings (site_id, setting_type, setting_name, setting_value) VALUES ";
				$sql .= " (1, 'global', 'site_url', " . $db->tosql($r->get_value("site_url"), TEXT) . ")";
				$db->query($sql);

				// set admin email
				$sql = "DELETE FROM " . $table_prefix . "global_settings WHERE setting_type='global' AND setting_name='admin_email'";
				$db->query($sql);
				$sql  = " INSERT INTO " . $table_prefix . "global_settings (site_id, setting_type, setting_name, setting_value) VALUES ";
				$sql .= " (1, 'global', 'admin_email', " . $db->tosql($r->get_value("admin_email"), TEXT) . ")";
				$db->query($sql);

				// set version information
				$sql = "DELETE FROM " . $table_prefix . "global_settings WHERE setting_type='version' ";
				$db->query($sql);
				$sql  = " INSERT INTO " . $table_prefix . "global_settings (site_id, setting_type, setting_name, setting_value) VALUES ";
				$sql .= " (1, 'version', 'version', " . $db->tosql($version_name, TEXT) . ")";
				$db->query($sql);
				$sql  = " INSERT INTO " . $table_prefix . "global_settings (site_id, setting_type, setting_name, setting_value) VALUES ";
				$sql .= " (1, 'version', 'type', " . $db->tosql($version_type, TEXT) . ")";
				$db->query($sql);
				$sql  = " INSERT INTO " . $table_prefix . "global_settings (site_id, setting_type, setting_name, setting_value) VALUES ";
				$sql .= " (1, 'version', 'number', " . $db->tosql($version_number, TEXT) . ")";
				$db->query($sql);
				$sql  = " INSERT INTO " . $table_prefix . "global_settings (site_id, setting_type, setting_name, setting_value) VALUES ";
				$sql .= " (1, 'version', 'installed', " . $db->tosql(va_timestamp(), TEXT) . ")";
				$db->query($sql);

				// set administrator
				$sql = "DELETE FROM " . $table_prefix . "admins";
				$db->query($sql);
				if ($db_library == "sqlsrv") {
					$sql = "SET IDENTITY_INSERT ".$table_prefix."admins ON";
					$db->query($sql);
				}
				$sql  = " INSERT INTO " . $table_prefix . "admins (admin_id, admin_name, privilege_id, email, login, password) VALUES (";
				$sql .= "1, ";
				$sql .= $db->tosql($r->get_value("admin_login"), TEXT) . ", ";
				$sql .= "1, ";
				$sql .= $db->tosql($r->get_value("admin_email"), TEXT) . ", ";
				$sql .= $db->tosql($r->get_value("admin_login"), TEXT) . ", ";
				$sql .= $db->tosql($r->get_value("admin_password"), TEXT) . ") ";
				$db->query($sql);
				if ($db_library == "sqlsrv") {
					$sql = "SET IDENTITY_INSERT ".$table_prefix."admins OFF";
					$db->query($sql);
				}

				$sql = "DELETE FROM ". $table_prefix . "support_users_departments ";
				$db->query($sql);
				$sql = "INSERT INTO ". $table_prefix . "support_users_departments (admin_id,dep_id,is_default_dep)  VALUES(1,1,1)";
				$db->query($sql);

				if ($version_name == "helpdesk") {
					$sql = "INSERT INTO ". $table_prefix . "bookmarks (title,admin_id,is_start_page,url)  VALUES('Support Home',1,1,'admin_support.php')";
					$db->query($sql);
				}

				$session_prefix = $r->get_value("db_name");
				set_session("session_admin_id", "1");
				set_session("session_admin_privilege_id", "1");
				set_session("session_admin_name", $r->get_value("admin_login"));
				set_session("session_install", 1);
				set_session("session_language_code", $language_code);
				        
				$operation = "";
				$step = 3;
				$r->set_record_name("step_" . $step);
			}
		} else {
			$r->set_default_values();
		}
	}

	if ($step == "3" && !$r->errors)
	{
		if ($operation == "save") {
			$layout = get_param("layout");
			if ($layout) {
				$sql  = " SELECT layout_id FROM " . $table_prefix . "layouts ";
				$sql .= " WHERE layout_name LIKE '%" . $db->tosql($layout, TEXT, false) . "%'";
				$sql .= " OR style_name=" . $db->tosql($layout, TEXT);
				$db->query($sql);
				if ($db->next_record()) {
					$layout_id = $db->f("layout_id");
					$sql  = " UPDATE " . $table_prefix . "global_settings SET setting_value=" . $db->tosql($layout_id, TEXT);
					$sql .= " WHERE setting_type='global' AND setting_name='layout_id'";
					$db->query($sql);
				}
			}
	  
			set_session("session_install", "");
			set_session("session_install_finished", 1);
			$install_finished = true;
			$operation = "";
			$step = 4;
			$r->set_record_name("step_" . $step);
		}
	}

	if ($step == "4" && $install_finished)
	{
		$sql = "SELECT setting_value FROM " . $table_prefix . "global_settings WHERE setting_type='global' AND setting_name='site_url'";
		$db->query($sql);
		if ($db->next_record()) {
			$r->set_value("site_url", $db->f("setting_value"));
		}

		$r->set_record_name("step_" . $step);
	}

	$r->set_parameters();

	$t->set_var("step", $step);
	$t->parse("step_" . $step, $step);
	$t->pparse("main", false);


	function build_config()
	{
		global $r, $language_code;

		$db_type = $r->get_value("db_type");
		$db_library = $r->get_value("db_library");
		if (!$db_type) { 
			if ($db_library == "mysql" || $db_library == "mysqli") {
				$db_type = "mysql";
			} else {
				$db_type = $db_library;
			}
		}
		$db_persistent = $r->get_value("db_persistent") ? "true" : "false";

		$config_file = "./includes/var_definition.php";
		$fp = fopen($config_file, "w");
		fwrite($fp, "<?php\n\n");
		fwrite($fp, "\tdefine(\"INSTALLED\", true); // set to false if you want run install.php\n");
		fwrite($fp, "\tdefine(\"DEBUG\",     true); // debug mode - set false on live site\n\n");

		fwrite($fp, "\t// database parameters\n");
		fwrite($fp, "\t\$db_lib        = \"" . escape_var_value($r->get_value("db_library")) . "\"; // mysql | mysqli | postgre | sqlsrv | odbc\n");
		fwrite($fp, "\t\$db_type       = \"" . escape_var_value($db_type) . "\"; // mysql | postgre | sqlsrv | access | db2\n");
		fwrite($fp, "\t\$db_name       = \"" . escape_var_value($r->get_value("db_name")) . "\";\n");
		fwrite($fp, "\t\$db_host       = \"" . escape_var_value($r->get_value("db_host")) . "\";\n");
		fwrite($fp, "\t\$db_port       = \"" . escape_var_value($r->get_value("db_port")) . "\";\n");
		fwrite($fp, "\t\$db_user       = \"" . escape_var_value($r->get_value("db_user")) . "\";\n");
		fwrite($fp, "\t\$db_password   = \"" . escape_var_value($r->get_value("db_password")) . "\";\n");
		fwrite($fp, "\t\$db_persistent = " . $db_persistent .";\n\n");

		fwrite($fp, "\t\$table_prefix  = \"va_\";\n\n");

		fwrite($fp, "\t\$default_language = \"" . $language_code . "\";\n\n");
		fwrite($fp, "\t\$va_browser_language = false; // change this value to true if you like set language accordingly to user browser settings\n\n");

		$datetime_show_format = parse_date_format($r->get_value("datetime_show_format"));
		$date_show_format     = parse_date_format($r->get_value("date_show_format"));
		$datetime_edit_format = parse_date_format($r->get_value("datetime_edit_format"));
		$date_edit_format     = parse_date_format($r->get_value("date_edit_format"));
		fwrite($fp, "\t// date parameters\n");
		fwrite($fp, "\t\$datetime_show_format = " . build_date_array($datetime_show_format) . ";\n");
		fwrite($fp, "\t\$date_show_format     = " . build_date_array($date_show_format) . ";\n");
		fwrite($fp, "\t\$datetime_edit_format = " . build_date_array($datetime_edit_format) . ";\n");
		fwrite($fp, "\t\$date_edit_format     = " . build_date_array($date_edit_format) . ";\n\n");

		fwrite($fp, "\t// session settings\n");
		fwrite($fp, "\t\$session_prefix = \"" . escape_var_value($r->get_value("db_name")) . "\";\n\n");

		fwrite($fp, "\t// if you use multi-site functionality uncomment the following line and specify appropriate id\n");
		fwrite($fp, "\t//\$site_id = 1;\n\n");

		fwrite($fp, "\t// if you use VAT validation uncomment the following line\n");
		fwrite($fp, "\t//\$vat_validation = true;\n");
		fwrite($fp, "\t// array of country codes for which VAT check is obligatory\n");
		fwrite($fp, "\t//\$vat_obligatory_countries = array(\"GB\");\n");
		fwrite($fp, "\t// array of country codes for which remote VAT check won't be run\n");
		fwrite($fp, "\t//\$vat_remote_exception_countries = array(\"NL\");\n\n");

		fwrite($fp, "?>");
		fclose($fp);
		chmod($config_file, 0777);
	}

function build_date_array($mask)
{
	$mask_array = "";
	for ($i = 0; $i < sizeof($mask); $i++) {
		$mask_array .= ($i) ? ", " : "array(";
		$mask_array .= "\"" . addslashes($mask[$i]) . "\"";
	}
	$mask_array .= ")";
	return $mask_array;
}

function escape_var_value($value)
{
	return str_replace(array("\\", "\"", "\$"), array("\\\\", "\\\"", "\\\$"), $value);
}

function get_db_file($db_lib) 
{
	$db_file = "";
	if ($db_lib == "mysql") {
		$db_file = "db_mysql.php";
	} else if ($db_lib == "mysqli") {
		$db_file = "db_mysqli.php";
	} else if ($db_lib == "postgre") {
		$db_file = "db_postgre.php";
	} else if ($db_lib == "sqlsrv") {
		$db_file = "db_sqlsrv.php";
	} else if ($db_lib == "odbc") {
		$db_file = "db_odbc.php";
	} else {
		$error = str_replace("{db_library}", $db_lib, va_message("DB_LIBRARY_ERROR"));
		echo $error;
		exit;
	}
	return $db_file;
}

function check_eula($parameter)
{
	global $r, $step;
	$control_name = $parameter[CONTROL_NAME];
	if ($step == 1 && !$parameter[CONTROL_VALUE]) {
		$r->parameters[$control_name][IS_VALID] = false;
		$r->parameters[$control_name][ERROR_DESC] = va_message("LICENSE_AGREEMENT_ERROR");
	}
}

