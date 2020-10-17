<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  db_sqlsrv.php                                            ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


class VA_SQL
{  
	var $DBHost         = "";
	var $DBPort         = "";
	var $DBDatabase     = "";
	var $DBUser         = "";
	var $DBPassword     = "";
	var $DBPersistent   = false;
	var $DBType         = "sqlsrv";

	/* 
	dates formats 
	*/
	var $DatetimeMask   = array("YYYY", "-", "MM", "-", "DD", " ", "HH", ":", "mm", ":", "ss");
	var $DateMask       = array("YYYY", "-", "MM", "-", "DD");
	var $TimeMask       = array("HH", ":", "mm", ":", "ss");
	var $TimestampMask  = array("YYYY", "MM", "DD", "HH", "mm", "ss");

	var $AutoFree       = 0;     
	var $LinkID         = 0;
	var $Offset         = 0;
	var $PageNumber     = 0;
	var $RecordsPerPage = 0;
	var $rsi            = 0;
	var $results        = array();
	var $records        = array();
	var $Record         = array();
	var $IdentityInsert = 0;

	// debug variables
	var $Debug          = 0;
	var $DebugError     = 0;
	var $DebugScript    = "";
	var $MaxQueryTime   = 1;

	// information about last error
	var $error_state    = 0;
	var $error_code     = 0;
	var $error_desc     = "";

	var $Errno       = 0;
	var $Error       = "";
	var $Errors      = "";
	var $HaltOnError = "yes"; // "yes", "no", "report"

	/* public: constructor */
	public function __construct($dbdata = "", $user = "", $pass = "", $dbname = "", $port = "", $persistent = false, $dbtype = "sqlsrv") 
	{	
		if (is_object($dbdata)) {
			$host = $dbdata->DBHost;
			$user = $dbdata->DBUser;
			$pass = $dbdata->DBPassword;
			$dbname = $dbdata->DBDatabase;
			$port = $dbdata->DBPort;
			$persistent = $dbdata->DBPersistent;
			$dbtype = $dbdata->DBType;
		} else {
			$host = $dbdata;
		}

		$this->DBHost = $host;
		$this->DBUser = $user;
		$this->DBPassword = $pass; 
		$this->DBDatabase = $dbname; 
		$this->DBPort = $port; 
		$this->DBPersistent = $persistent;
		$this->DBType = $dbtype;
		$this->RecordsPerPage = 0;
		if (get_session("session_admin_id")) {
			$this->DebugError = 1;
		}
	}

	function check_lib() 
	{
		return function_exists("sqlsrv_connect");
	}

	function db_connect($new_link = false) 
	{
		if (!$this->LinkID) {
			$serverName = ($this->DBPort != "") ? $this->DBHost . ", " . $this->DBPort : $this->DBHost;
			$connectionInfo = array("Database" => $this->DBDatabase);
			if ($this->DBUser) { $connectionInfo["UID"] = $this->DBUser; }
			if ($this->DBPassword) { $connectionInfo["PWD"] = $this->DBPassword; }
		
			$this->LinkID = @sqlsrv_connect($serverName, $connectionInfo);

			if (!$this->LinkID) {		
				$this->error_state = 1;
				$this->Errors = sqlsrv_errors();
				if ($this->DebugError) {

					$this->halt("Connect failed: " . $this->describe_error());
				} else {
					$this->halt("Connect failed.");
				}
				return 0;
			}
		}
		sqlsrv_query($this->LinkID, "SET ANSI_WARNINGS OFF"); // No warning when NULL used in arithmetic operations. Data is truncated to the size of the column. Devide-by-zero return NULL.
		return $this->LinkID;
	}

	function free_result()
	{
		$rsi = $this->rsi;
		if ($this->results[$rsi] && !is_bool($this->results[$rsi])) {
			@sqlsrv_free_stmt($this->results[$rsi]);
			$this->results[$rsi] = 0;
		}
	}

	function close() 
	{
		foreach ($this->results as $rsi => $result) {
			$this->rsi = $rsi;
			if ($this->results[$rsi]) {
				$this->free_result();
			}
		}
		if ($this->LinkID != 0 && !$this->DBPersistent) {
			@sqlsrv_close($this->LinkID);
			$this->LinkID = 0;
		}
	}

	function query($query_string) 
	{
		global $is_admin_path;
		$rsi = $this->rsi; 

		if ($query_string == "") {
			return 0;
		}
	
		if (!$this->db_connect()) {
			return 0; 
		};
	
		if (!isset($this->results[$rsi])) {
			$this->results[$rsi] = false;
		} else if ($this->results[$rsi]) {
			$this->free_result();
		}

		$IdentityInsertTable = "";
		if ($this->IdentityInsert && preg_match("/^\s*INSERT\s+INTO\s+([a-z0-9_]+)\s/is", $query_string, $matches)) {
			$table_name = $matches[1];
			// check identity column
			$identity_column = "";
			$sql  = " SELECT name FROM sys.identity_columns ";
			$sql .= " WHERE OBJECT_NAME(object_id)=" . $this->tosql($table_name, TEXT);
			$stmt  = sqlsrv_query($this->LinkID, $sql);
			if ($data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
				$identity_column = $data["name"];
			}

			if ($identity_column && preg_match("/^\s*INSERT\s+INTO\s+([a-z0-9_]+)\s+\(\s*".$identity_column."\s*[,\)]/is", $query_string, $matches)) {
				$IdentityInsertTable = $matches[1];
				sqlsrv_query($this->LinkID, "SET IDENTITY_INSERT ".$IdentityInsertTable." ON");
			}
		}

		if ($this->RecordsPerPage && !is_numeric($this->RecordsPerPage)) {
			$this->RecordsPerPage = 10;
		}

		if ($this->RecordsPerPage && $this->PageNumber) {
			$query_string .= " OFFSET " . intval(($this->PageNumber - 1) * $this->RecordsPerPage) . " ROWS ";
			$query_string .= " FETCH NEXT " . $this->RecordsPerPage. " ROWS ONLY ";
			$this->RecordsPerPage = 0;
			$this->PageNumber = 0;
		} else if ($this->RecordsPerPage) {
			$query_string .= " OFFSET " . intval($this->Offset) . " ROWS ";
			$query_string .= " FETCH NEXT " . $this->RecordsPerPage. " ROWS ONLY ";
			$this->Offset = 0;
			$this->RecordsPerPage = 0;
		}
	
		
		$start_query = microtime(true);
		$this->results[$rsi] = @sqlsrv_query($this->LinkID, $query_string); 

		$end_query = microtime(true);
		$query_time = $end_query - $start_query;
		if ($this->results[$rsi] === false) {
			$this->error_state = 1;
			$this->Errors = sqlsrv_errors();
			if ($this->DebugError) {
				$this->halt("Invalid SQL: " . $query_string);
			} else {
				$this->halt("Invalid SQL.");
			}
		} else if ($this->Debug && $query_time > $this->MaxQueryTime) {
			if (isset($is_admin_path) && $is_admin_path) {
				$log_file = "../../logs/slow_queries.log";
			} else {
				$log_file = "../logs/slow_queries.log";
			}
			save_log_file($log_file, "Query Time: ".$query_time, $query_string);
		}
		if ($IdentityInsertTable) {
			sqlsrv_query($this->LinkID, "SET IDENTITY_INSERT ".$IdentityInsertTable." OFF");
		}
		
		return true;
	}

	function last_insert_id($sequence_name = "")
	{
		$rsi = $this->rsi;
		$this->query("SELECT @@IDENTITY");
		$this->next_record();
		return $this->f(0);
	}

	function info() 
	{
		return "";
	}

	function next_record() 
	{
		$rsi = $this->rsi;
		if (!$this->results[$rsi]) {
			$this->error_state = 1;
			$this->halt("next_record called with no query pending.");
			return 0;
		}
	
		$this->records[$rsi] = @sqlsrv_fetch_array($this->results[$rsi]); 
		$this->Record = $this->records[$rsi];
			
		if ($this->Record === false) {
			$this->Errors = sqlsrv_errors();
		}
		
		$stat = is_array($this->Record);
		if (!$stat && $this->AutoFree) {
			$this->free_result();
		}
		return $stat;
	}

	function affected_rows() 
	{
		return @sqlsrv_rows_affected($this->LinkID);
	}

	function num_rows() 
	{
		$rsi = $this->rsi;
		return @sqlsrv_num_rows($this->results[$rsi]);
	}

	function num_fields() 
	{
		$rsi = $this->rsi;
		return @sqlsrv_num_fields($this->results[$rsi]);
	}

	function f($Name, $field_type = TEXT) 
	{
		$rsi = $this->rsi;
		if (isset($this->records[$rsi][$Name])) {
			$value = $this->records[$rsi][$Name];
			switch($field_type) {
				case DATETIME:
					if (is_object($value)) {
						$value = $value->format("Y-m-d H:i:s");
					}
					$value = parse_date($value, $this->DatetimeMask, $date_errors);
					break;
				case DATE:
					if (is_object($value)) {
						$value = $value->format("Y-m-d"); 
					}
					$value = parse_date($value, $this->DateMask, $date_errors);
					break;
				case TIME:
					if (is_object($value)) {
						$value = $value->format("H:i:s"); 
					}
					$value = parse_date($value, $this->TimeMask, $date_errors);
					break;
			}
			return $value; 
		} else {
			return "";
		}
	}

	function halt($message) 
	{
		global $t, $is_admin_path, $settings;
	
		$this->error_state = 1;
		if (!$this->error_desc) {
			$this->error_desc  = $message;
		}
		if (!$this->error_code) {
			$this->error_code = 1;
		}

		if ($this->HaltOnError == "no") {
			return;
		}
	
		$eol = get_eol();
		$request_uri = get_var("REQUEST_URI");
		$http_host = get_var("HTTP_HOST");
		$http_referer = get_var("HTTP_REFERER");
		
		$protocol = (strtoupper(get_var("HTTPS")) == "ON") ? "https://" : "http://";
		$page_url = $protocol . $http_host . $request_uri;
		
		$error_message  = "<b>Page URL:</b> <a href=\"" . htmlspecialchars($page_url) . "\">" . htmlspecialchars($page_url) . "</a><br>" . $eol;
		if ($http_referer) {
			$error_message .= "<b>Referrer URL:</b> <a href=\"" . htmlspecialchars($http_referer) . "\">" . htmlspecialchars($http_referer) . "</a><br>" . $eol;
		}
		if ($this->DebugScript) {
			$error_message .= "<b>Script Info:</b> " . htmlspecialchars($this->DebugScript) . "<br>" . $eol;
		}
		$error_message .= "<b>Database error:</b> " . htmlspecialchars($message) . "<br>" . $eol;
		// prepare full mail notification message 
		$mail_message  = $error_message;
		$mail_message .= "<b>SQL Server Error:</b> " . $this->describe_error() . "<br>" . $eol;
		if ($this->DebugError) {
			$error_message .= "<b>SQL Server Error:</b> " . $this->describe_error() . "<br>" . $eol;
		}
		
		// to get notification about errors change email address and uncomment mail line below
		$recipients     = "db_error_email@domain_name";
		$subject        = "DB ERROR";
		$mail_message = strip_tags($mail_message);
		$email_headers = array();
		$email_headers["from"] = "db_error_email@domain_name";
		$email_headers["mail_type"] = 0;
		//va_mail($recipients, $subject, $mail_message, $email_headers);
		
		// print warning page 
		if (!isset($t)) {
			if ($is_admin_path) {
				$templates_dir = isset($settings["admin_templates_dir"]) ? $settings["admin_templates_dir"] : "../templates/admin";
			} else {
				$templates_dir = isset($settings["templates_dir"]) ? $settings["templates_dir"] : "./templates/user";
			}
			if (class_exists("VA_Template")) {
				$t = new VA_Template($templates_dir);
			}
		} else {
			$templates_dir = $t->get_template_path();
		}
			
		if ($is_admin_path) {
			$template_exists = file_exists($templates_dir . "/" . "admin_error_db.html");
		} else {
			$template_exists = file_exists($templates_dir . "/" . "error_db.html");
		}
		if (isset($t) && $template_exists) {
			if ($is_admin_path) {
				$t->set_file("header",   "admin_header.html");
				$t->set_file("footer",   "admin_footer.html");
				$t->set_file("error_db", "admin_error_db.html");
			} else {
				$t->set_file("header",   "header.html");
				$t->set_file("footer",   "footer.html");
				$t->set_file("error_db", "error_db.html");
			}
			$t->set_var("error_message", $error_message);
			$t->set_var("error_number", "");
			
			$subject = str_replace("+", "%20", urlencode($subject));
			$message = str_replace("+", "%20", urlencode($message));
			$t->set_var("subject", $subject);
			$t->set_var("body", $message);
			
			
			$t->parse("header", false);
			$t->parse("footer", false);
			$t->pparse("error_db", false);
		} else {
			echo $error_message;
		}
			
		if ($this->HaltOnError != "report") {
			exit;
		}
	}

	function tosql($value, $value_type, $is_delimiters = true, $use_null = true) 
	{
		if (is_array($value) || strlen($value)) {
			switch ($value_type) {
				case NUMBER:
				case FLOAT:
					$value = preg_replace(array("/,/", "/[^0-9\.,\-]/"), array(".", ""), $value);
					if (!is_numeric($value)) {
						$value = 0;
					}
					return $value;
					break;
				case DATETIME:
					if (!is_array($value) && is_int($value)) { $value = va_time($value); }
					if (is_array($value)) { $value = va_date($this->DatetimeMask, $value); } 
					else { return "NULL"; }
					break;
				case INTEGER:
					return intval($value);
					break;
				case DATE:
					if (!is_array($value) && is_int($value)) { $value = va_time($value); }
					if (is_array($value)) { $value = va_date($this->DateMask, $value); }
					else { return "NULL"; }
					break;
				case TIME:
					if (!is_array($value) && is_int($value)) { $value = va_time($value); }
					if (is_array($value)) { $value = va_date($this->TimeMask, $value); }
					else { return "NULL"; }
					break;
				case TIMESTAMP:
					if (!is_array($value) && is_int($value)) { $value = va_time($value); }
					if (is_array($value)) { $value = va_date($this->TimestampMask, $value); }
					else { return "NULL"; }
					break;
				case NUMBERS_LIST:
				case FLOATS_LIST:
					$values = (is_array($value)) ? $value : explode(",", $value);
					for ($v = 0; $v < sizeof($values); $v++) {
						$value = $values[$v];
						$value = preg_replace(array("/,/", "/[^0-9\.,\-]/"), array(".", ""), $value);
						if (!is_numeric($value)) {
							$value = 0;
						}
						$values[$v] = $value;
					}
					return implode(",", $values);
					break;
				case INTEGERS_LIST:
					$sql_values = array();
					$values = (is_array($value)) ? $value : explode(",", $value);
					foreach ($values as $array_value) {
						$array_value = trim($array_value);
						if (preg_match("/^\d+$/", $array_value)) {
							$sql_values[] = $array_value;
						}
					}
					return (is_array($sql_values) && count($sql_values)) ? implode(",", $sql_values) : "NULL";
					break;
				case TEXT_LIST:
					$values = (is_array($value)) ? $value : explode(",", $value);
					for ($v = 0; $v < sizeof($values); $v++) {
						$values[$v] = "'".str_replace("'","''",$values[$v])."'";
					}
					return implode(",", $values);
					break;
				default:
					$value = str_replace("'", "''", $value);
					break;
			}
			if ($is_delimiters) {
				$value = "'" . $value . "'";
			}
		} elseif ($use_null) {
			$value = "NULL";
		} else {
			if ($value_type == INTEGER || $value_type == FLOAT || $value_type == NUMBER 
				|| $value_type == NUMBERS_LIST || $value_type == FLOATS_LIST || $value_type == INTEGERS_LIST) {
				$value = 0;
			} elseif ($is_delimiters) {
				$value = "''";
			}
		} 
		return $value;
	}
	
	function describe_error() 
	{
		$error_msg = "";
		$errors = $this->Errors;
		foreach( $errors as $error ) {
			$error_msg .= "<br/>SQLSTATE: ".$error["SQLSTATE"];
			$error_msg .= "<br/>code: ".$error["code"];
			$error_msg .= "<br/>message: ".$error["message"];
		}
		return $error_msg;
	}

	function get_fields($table_name)
	{
		$rsi = $this->rsi;
		$fields = array();
		$sql  = " SELECT * FROM INFORMATION_SCHEMA.COLUMNS ";
		$sql .= " WHERE TABLE_NAME=" . $this->tosql($table_name, TEXT);
		$this->query($sql);
		while ($this->next_record()) {
			$name = $this->f("COLUMN_NAME");
			$type = $this->f("DATA_TYPE");
			$is_nullable = $this->f("IS_NULLABLE");
			$null = ($is_nullable == "YES") ? true : false;
			$defalut_value = $this->f("COLUMN_DEFAULT");
			$field = array(
				'name' => $name, "type" => $type, "null" => $null, "primary" => false, "auto_increment" => false, 
				"default" => $defalut_value, "index" => false, "unique" => false, "idenity" => false);
			$fields[$name] = $field;
		}
		// check primary key(s)
		$sql  = " SELECT COLUMN_NAME ";
		$sql .= " FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE ";
		$sql .= " WHERE OBJECTPROPERTY(OBJECT_ID(CONSTRAINT_SCHEMA + '.' + QUOTENAME(CONSTRAINT_NAME)), 'IsPrimaryKey') = 1 ";
		$sql .= " AND TABLE_NAME=" . $this->tosql($table_name, TEXT);
		$this->query($sql);
		while ($this->next_record()) {
			$pk_column = $this->f("COLUMN_NAME");
			$fields[$pk_column]["primary"] = true;
		}
		// check identity column
		$sql  = " SELECT name FROM sys.identity_columns ";
		$sql .= " WHERE OBJECT_NAME(object_id)=" . $this->tosql($table_name, TEXT);
		$this->query($sql);
		while ($this->next_record()) {
			$identity_column = $this->f("name");
			$fields[$identity_column]["identity"] = true;
		}
		return $fields;
	}

	function get_tables()
	{
		$tables = array();
		$sql  = " SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES ORDER BY TABLE_NAME";
		$this->query($sql);
		while ($this->next_record()){
			$tables[] = $this->f(0);
		}
		return $tables;		
	}
	
	function create_database($db_name = "")
	{
		echo "Create Database function is not available for SQL Server module.";
		exit;
	}

	function set_rsi($new_rsi)
	{
		$current_rsi = $this->rsi;
		$this->rsi = $new_rsi;
		return $current_rsi;
	}
}

?>