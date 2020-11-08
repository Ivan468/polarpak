<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  db_utf8.php                                              ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	error_reporting (E_ALL);
	ini_set("display_errors", "1");
	include_once("./includes/var_definition.php");
	include_once("./includes/constants.php");
	include_once("./includes/db_$db_lib.php");
	include_once("./includes/common_functions.php");
	include_once("./includes/va_functions.php");

	// global array to use in different blocks and functions
	$va_data = array(); $va_messages = array();

	// check language
	$language_code = $default_language;
	if (!(strlen($language_code) == 2 && file_exists("./messages/".$language_code."/messages.php"))) {
		$language_code = "en";
	}
	include_once("./messages/" . $language_code . "/messages.php");
	foreach ($va_messages as $constant_name => $constant_value) {
		if(!defined($constant_name)) {
			define($constant_name, $constant_value);
		}
	}

	// Database Initialize
	$db = new VA_SQL();
	$db->DBType      = $db_type;
	$db->DBDatabase  = $db_name;
	$db->DBHost      = $db_host;
	$db->DBPort      = $db_port;
	$db->DBUser      = $db_user;
	$db->DBPassword  = $db_password;
	$db->DBPersistent= $db_persistent;
	$db->DebugError  = true;
	$db_charset = "utf8";
	$db_collate = "utf8_general_ci";
	$db_collate = "utf8_unicode_ci";

	$db_charset = "utf8mb4"; // character-set-server - utf8mb4
	$db_collate = "utf8mb4_unicode_ci"; // collation-server - utf8mb4_unicode_ci - case-insensitive 


	// ALTER TABLE external_songs CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci ";
	/*
	// check original character set settings
	$character_set_settings = array();
	$sql = "SHOW VARIABLES LIKE 'character_set_%'";
	$db->query($sql);
	while ($db->next_record()) {
		$var_name = $db->f("Variable_name");
		$var_value = $db->f("Value");
		$character_set_settings[$var_name] = $var_value;
	}
	// save character set data
	$character_set_client = $character_set_settings["character_set_client"];
	$character_set_results = $character_set_settings["character_set_results"];
	$character_set_connection = $character_set_settings["character_set_connection"];//*/
	


	// additional connection
	$dbs = new VA_SQL();
	$dbs->DBType       = $db->DBType;
	$dbs->DBDatabase   = $db->DBDatabase;
	$dbs->DBUser       = $db->DBUser;
	$dbs->DBPassword   = $db->DBPassword;
	$dbs->DBHost       = $db->DBHost;
	$dbs->DBPort       = $db->DBPort;
	$dbs->DBPersistent = $db->DBPersistent;
	$dbs->DebugError   = true;

	// check utf8 upgrade settings
	$setting_type = $db_charset;
	$upgrade_settings = array();

	// lock table global_settings
	$sql = "LOCK TABLES ".$table_prefix."global_settings WRITE ";
	$db->query($sql);

	$sql  = " SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type=".$db->tosql($setting_type, TEXT);
	$db->query($sql);
	while ($db->next_record()) {
		$upgrade_settings[$db->f("setting_name")] = $db->f("setting_value");
	}

	$utf8_upgrade = get_setting_value($upgrade_settings, "upgrade", 0);

	// check AJAX submit
	$ajax = get_param("ajax");
	$operation = get_param("operation");

	// check old charset
	if ($operation == "charset") {
		$old_charset = get_param("old_charset");
		set_session("session_old_charset", $old_charset);
	} else {
		$old_charset = get_session("session_old_charset");
	}



	if ($ajax) {
		if ($operation == "upgraded" && $utf8_upgrade != "done") {
			// unlock tables
			$db->query("UNLOCK TABLES");

			$sql = " ALTER DATABASE `".$db_name."` CHARACTER SET ".$db_charset." COLLATE " . $db_collate;
			$db->query($sql);

			$sql  = " UPDATE " . $table_prefix . "global_settings ";
			$sql .= " SET setting_value=" . $db->tosql("done", TEXT);
			$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
			$sql .= " AND setting_name=" . $db->tosql("upgrade", TEXT);
			$db->query($sql);

			echo "Your database character set was successfully changed to UTF-8";
			return;
		} else if ($utf8_upgrade == "done") {
			echo "Your database already updated";
			return;
		}

	  if (!$utf8_upgrade) {
			$response = array(
				"error" => 1,
				"table_name" => $table_name,
				"table_status" => "upgrade wasn't started",
				"updated_records" => "",
			);
			echo json_encode($response);
			return;
		} else if ($utf8_upgrade == "done")  {
			$response = array(
				"error" => 1,
				"table_name" => $table_name,
				"table_status" => "database already upgraded",
				"updated_records" => "",
			);
			echo json_encode($response);
			return;
		}
		
		$table_name = get_param("table_name");
		if (!preg_match("/global_settings/", $table_name)) {
			$sql = "LOCK TABLES ".$table_prefix."global_settings WRITE, " . $table_name . " WRITE ";
		}
		$db->query($sql);

		// check parameters
		$pk_field = get_setting_value($upgrade_settings, $table_name."_pk", "");
		$pk_id = get_setting_value($upgrade_settings, $table_name."_pk_id", "");
		$table_status = get_setting_value($upgrade_settings, $table_name."_status", "");

		// check total records to update
		$sql = " SELECT COUNT(*) FROM " . $table_name;
		$total_records = get_db_value($sql);

		$updated_records = $total_records; // ignoring any manual conversion
		if ($total_records > $updated_records) {

			// get table fields
			$fields = $db->get_fields($table_name);

			$dbs->RecordsPerPage = 100;
			$dbs->PageNumber = 1;
			$sql  = " SELECT * FROM " . $table_name;
			if (strlen($pk_id)) {
				$sql .= " WHERE " . $pk_field . ">" . $db->tosql($pk_id, INTEGER);
			}
			$sql .= " ORDER BY " . $pk_field;
      $dbs->query($sql);
			while ($dbs->next_record()) {
				$pk_id = $dbs->f($pk_field);

				// build sql to update data
				$field_number = 0;
				$sql  = " UPDATE " . $table_name;
				foreach ($fields as $field_id => $field_info) {
					$field_name = $field_info["name"];
					$field_type = $field_info["type"];
					$primary_key = $field_info["primary"];
					$field_value = $dbs->f($field_name);
					if (strlen($field_value) && preg_match("/^TEXT|VARCHAR|CHAR/", $field_type)) {
						$field_number++;
						if ($field_number == 1) { $sql .= " SET "; }
						else { $sql .= ","; }
						// convert value to utf-8
						$field_value = iconv($old_charset, "UTF-8//TRANSLIT//IGNORE", $field_value);
						$sql .= $field_name . "=" . $db->tosql($field_value, TEXT);
					}
				}
				$sql .= " WHERE " . $pk_field . "=" . $db->tosql($pk_id, INTEGER);
				if ($field_number > 0) {
					// update data
					$db->query($sql);
				}

				$sql  = " UPDATE " . $table_prefix . "global_settings ";
				$sql .= " SET setting_value=" . $db->tosql($pk_id, TEXT);
				$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
				$sql .= " AND setting_name=" . $db->tosql($table_name."_pk_id", TEXT);
				$db->query($sql);

				$updated_records++;
			}
		}

		if ($total_records == 0 || $total_records == $updated_records) {
			// upgrade table to utf
			$sql = "ALTER TABLE ".$table_name." CONVERT TO CHARACTER SET ".$db_charset." COLLATE " . $db_collate;

			$db->query($sql);

			$table_status = "upgraded";
			$sql  = " UPDATE " . $table_prefix . "global_settings ";
			$sql .= " SET setting_value=" . $db->tosql($table_status, TEXT);
			$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
			$sql .= " AND setting_name=" . $db->tosql($table_name."_status", TEXT);
			$db->query($sql);
		}

		$sqls = array();
		$response = array(
			"table_name" => $table_name,
			"table_status" => $table_status,
			"updated_records" => $updated_records,
			"sqls" => $sqls,
		);

		echo json_encode($response);

		// unlock tables
		$db->query("UNLOCK TABLES");

		return;
	}

	if (!$old_charset) {
		// show form to select old charset before proceed
		$charsets = array(
			"iso-8859-1" => array("en", "de", "br", "fi", "it", "no", "pt", "ro",),
			"iso-8859-2" => array("hr", "hu", "pl",),
			"iso-8859-15" => array("et",),
			"windows-1250" => array("cs", "sk",),
			"windows-1251" => array("mk", "ru", "uk",),
			"windows-1252" => array("nl", "sv", "es", "fr", ),
			"windows-1253" => array("el",),
			"windows-1254" => array("tr",),
			"windows-1256" => array("ar", "fa",),
			"windows-1257" => array("lt", "lv",),
		);

		$options = "<option value=\"\">Select Charset</option>";
		foreach ($charsets as $charset_name => $languages) {
		  // check if we can select charset as default
			$selected = "";
			if (strtolower(CHARSET) != "utf-8" && strtolower(CHARSET) == strtolower($charset_name)) {
				$selected = " selected=\"selected\" ";
			} else if (strtolower(CHARSET) == "utf-8" && in_array($language_code, $languages)) {
				$selected = " selected=\"selected\" ";
			}
			$options .= "<option $selected value=\"$charset_name\">$charset_name</option>\n";
		}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Database upgrade to UTF-8</title>
<script language="JavaScript" type="text/javascript">

function checkCharset()
{
	var charsetObj = document.charset.old_charset;
	var oldCharset = charsetObj.options[charsetObj.selectedIndex].value;
	if (oldCharset == "") {
		alert("Please select encoding before proceed.")
		return false;
	} else {
		return true;
	}
}

</script>
</head>
<body>
<h1>Database upgrade to UTF-8</h1>

Please select your old shop/database encoding:
<form name="charset" action="db_utf8.php" onsubmit="return checkCharset();">
<input type="hidden" name="operation" value="charset" />

<select name="old_charset">
<?php echo $options; ?>
</select>

<input type="submit" value="Start Database Upgrade" />

</form>
</body></html>
<?php
		return;
	} else if (!$utf8_upgrade) {
		// utf8 upgrade process is not yet started
		$sql  = " DELETE FROM " . $table_prefix . "global_settings ";
		$sql .= " WHERE setting_type=".$db->tosql($setting_type, TEXT);
		$db->query($sql);

		// prepare tables data to upgrade
		$check_tables = $db->get_tables();
		
		$text_tables = array(); // tables with text data need upgrade to utf8
		$tables_index = 0;
		foreach ($check_tables as $table_name) {
			$text_fields = 0; $pk_fields = 0; $pk_field = ""; $pk_int_type = false;

			if (!preg_match("/global_settings/", $table_name)) {
				$sql = "LOCK TABLES ".$table_prefix."global_settings WRITE, " . $table_name . " WRITE ";
			}
			$db->query($sql);

			$fields = $db->get_fields($table_name);
			foreach ($fields as $field_id => $field_info) {
				$field_name = $field_info["name"];
				$field_type = $field_info["type"];
				$primary_key = $field_info["primary"];
				if (preg_match("/^TEXT|VARCHAR|CHAR/", $field_type)) {
					$text_fields++;
				}
				if ($primary_key) {
					if ($pk_field) { $pk_field .= ","; }
					$pk_field = $field_name;
					$pk_fields++;
					if (preg_match("/^TINYINT|INT/", $field_type)) {
						$pk_int_type = true;
					}
				}
			}

			// update all tables which has text field and only 1 integer primary key
			if ($text_fields > 0 && $pk_fields == 1 && $pk_int_type) {
				if (!preg_match("/tracking_visit|tracking_page|caches/", $table_name)) {
					$text_tables[$table_name] = $pk_field;
				}
			}
		}

		foreach ($text_tables as $table_name => $pk_field) {
			$sql  = " INSERT INTO " . $table_prefix . "global_settings ";
			$sql .= " (setting_type, setting_name, setting_value) VALUES (";
			$sql .= $db->tosql($setting_type, TEXT) . ",";
			$sql .= $db->tosql($table_name, TEXT) . ",";
			$sql .= $db->tosql("table", TEXT) . ")";
			$db->query($sql);

			$sql  = " INSERT INTO " . $table_prefix . "global_settings ";
			$sql .= " (setting_type, setting_name, setting_value) VALUES (";
			$sql .= $db->tosql($setting_type, TEXT) . ",";
			$sql .= $db->tosql($table_name."_status", TEXT) . ",";
			$sql .= $db->tosql("", TEXT) . ")";
			$db->query($sql);

			$sql  = " INSERT INTO " . $table_prefix . "global_settings ";
			$sql .= " (setting_type, setting_name, setting_value) VALUES (";
			$sql .= $db->tosql($setting_type, TEXT) . ",";
			$sql .= $db->tosql($table_name."_pk", TEXT) . ",";
			$sql .= $db->tosql($pk_field, TEXT) . ")";
			$db->query($sql);

			$sql  = " INSERT INTO " . $table_prefix . "global_settings ";
			$sql .= " (setting_type, setting_name, setting_value) VALUES (";
			$sql .= $db->tosql($setting_type, TEXT) . ",";
			$sql .= $db->tosql($table_name."_pk_id", TEXT) . ",";
			$sql .= $db->tosql("", TEXT) . ")";
			$db->query($sql);
		}

		// add upgrade parameter that we start it 
		$sql  = " INSERT INTO " . $table_prefix . "global_settings ";
		$sql .= " (setting_type, setting_name, setting_value) VALUES (";
		$sql .= $db->tosql($setting_type, TEXT) . ",";
		$sql .= $db->tosql("upgrade", TEXT) . ",";
		$sql .= $db->tosql("1", TEXT) . ")";
		$db->query($sql);
	}


	// re-check settings to upgrade 
	$upgrade_settings = array(); 
	$sql  = " SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type=".$db->tosql($setting_type, TEXT);
	$db->query($sql);
	while ($db->next_record()) {
		$upgrade_settings[$db->f("setting_name")] = $db->f("setting_value");
	}

	// unlock tables
	$db->query("UNLOCK TABLES");

	$utf8_upgrade = get_setting_value($upgrade_settings, "upgrade", 0);

	// prepare tables list
	$tables = array();
	foreach ($upgrade_settings as $setting_name => $setting_value) {
		if ($setting_value == "table") {
			$table_name = $setting_name;
			$table_status = get_setting_value($upgrade_settings, $table_name."_status", "");
			$tables[$table_name] = $table_status;
		}
	}


	// generate tables list to upgrade
	$table  = '<table border="1" cellspacing="0" cellpadding="3">';
	$table .= "<tr><th width='225'>Table Name</th><th width='125'>Status</th><th>Total Records</th><th>Updated Records</th></tr>";
	foreach ($tables as $table_name => $table_status) {
		$pk_field = get_setting_value($upgrade_settings, $table_name."_pk", "");
		$pk_id = get_setting_value($upgrade_settings, $table_name."_pk_id", "");
		$table_status = get_setting_value($upgrade_settings, $table_name."_status", "");
		$status_desc = $table_status;
		if ($status_desc == "upgraded") { $status_desc = "<b>".$status_desc."</b>"; }

		// check total records to update
		$sql = " SELECT COUNT(*) FROM " . $table_name;
		$total_records = get_db_value($sql);

		$updated_records = 0;
		if (strlen($pk_id)) {
			$sql  = " SELECT COUNT(*) FROM " . $table_name;
			$sql .= " WHERE " . $pk_field . "<=" . $db->tosql($pk_id, INTEGER);
			$updated_records = intval(get_db_value($sql));
		}

		$table .= "<tr>";
		$table .= "<td>$table_name</td><td align='center' id='".$table_name."_status'>".$status_desc."</td>";
		$table .= "<td>$total_records</td><td id='".$table_name."_updated'>$updated_records</td>";
		$table .= "</tr>";
	}
	$table .= "</table>";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Database upgrade to UTF-8</title>
<script src="js/ajax.js" language="JavaScript" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
	var tables = <?php echo json_encode($tables); ?>;
	function checkTable()
	{
		var tableFound = false;
		for (var tableName in tables) {
			var tableStatus = tables[tableName];
			if (tableStatus == "" || tableStatus == "new" || tableStatus == "upgrading") {
				// upgrade table
				var postParams = {
					"ajax": "1", 
					"operation": "upgrade", 
					"table_name": tableName, 
					"table_status": tableStatus, 
				};
				postAjax("db_utf8.php", tableResponse, "", "", postParams);
				tableFound = true;
				break;
			}
		}
		if (!tableFound) {
			// all tables was upgraded 
			var postParams = {
				"ajax": "1", 
				"operation": "upgraded", 
			};
			postAjax("db_utf8.php", dbResponse, "", "", postParams);
		}

	}

	function tableResponse(response)
	{
		var data;
		try {
			data = JSON.parse(response);
		} catch(e) {
			alert(e + "\n" + response); 
			return;
		}

		var tableName = data.table_name;
		var tableStatus = data.table_status;
		var updatedRecords = data.updated_records;
		var sqls = data.sqls;

		// update table status
		tables[tableName] = tableStatus;

		// show information
		var statusObj = document.getElementById(tableName + "_status");
		var updatedObj = document.getElementById(tableName + "_updated");
		if (tableStatus == "upgrading") {
			tableStatus = "<i>"+tableStatus+"...</i>";
		} else if (tableStatus == "upgraded") {
			tableStatus = "<b>"+tableStatus+"</b>";
		}
		statusObj.innerHTML = tableStatus;
		updatedObj.innerHTML = updatedRecords;

		// check for next table
		checkTable();
	}

	function dbResponse(response) {
		alert(response);
	}

</script>
</head>
<body onload="checkTable();">
<h1>Database upgrade to UTF-8</h1>
<?php echo $table; ?>
</body></html>