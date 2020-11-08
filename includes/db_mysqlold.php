<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  db_mysqlold.php                                          ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


// This class which use old MySQL functions

class VA_SQL
{  
	var $DBHost         = "";
	var $DBPort         = "";
	var $DBDatabase     = "";
	var $DBUser         = "";
	var $DBPassword     = "";
	var $DBPersistent   = false;
	var $DBType         = "mysql";

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
	var $HaltOnError = "yes"; // "yes", "no", "report"

	/* public: constructor */
	public function __construct($dbdata = "", $user = "", $pass = "", $dbname = "", $port = "", $persistent = false, $dbtype = "mysql") 
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
		return function_exists("mysql_connect");
	}

	function db_connect($new_link = false) 
	{
		if (!$this->LinkID) {
			$server = ($this->DBPort != "") ? $this->DBHost . ":" . $this->DBPort : $this->DBHost;
		
			if ($this->DBPersistent) {
				$this->LinkID = @mysql_pconnect($server, $this->DBUser, $this->DBPassword);
			} else {
				$this->LinkID = @mysql_connect($server, $this->DBUser, $this->DBPassword, $new_link);
			}

			if (!$this->LinkID) {		
				if ($this->DebugError) {
					$this->halt("Connect failed: " . $this->describe_error(mysql_errno(), mysql_error()));
				} else {
					$this->halt("Connect failed.");
				}
				return 0;
			}
		
			if (!mysql_select_db($this->DBDatabase, $this->LinkID)) {
				$this->LinkID = 0;
				if ($this->DebugError) {
					$this->halt($this->describe_error(mysql_errno(), mysql_error()));
				} else {
					$this->halt("DB select failed.");
				}
				return 0;
			}
		}
		mysql_query("set names 'utf8'", $this->LinkID);
		mysql_set_charset('utf8',$this->LinkID);
		
		return $this->LinkID;
	}

	function free_result()
	{
		$rsi = $this->rsi;
		if ($this->results[$rsi] && !is_bool($this->results[$rsi])) {
			@mysql_free_result($this->results[$rsi]);
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
			@mysql_close($this->LinkID);
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

		if ($this->RecordsPerPage && !is_numeric($this->RecordsPerPage)) {
			$this->RecordsPerPage = 10;
		}
	
		if ($this->RecordsPerPage && $this->PageNumber) {
			$query_string .= " LIMIT " . intval(($this->PageNumber - 1) * $this->RecordsPerPage) . ", " . $this->RecordsPerPage;
			$this->RecordsPerPage = 0;
			$this->PageNumber = 0;
		} else if ($this->RecordsPerPage) {
			$query_string .= " LIMIT " . intval($this->Offset) . ", " . $this->RecordsPerPage;
			$this->Offset = 0;
			$this->RecordsPerPage = 0;
		}
	
		
		$start_query = microtime(true);
		$this->results[$rsi] = @mysql_query($query_string, $this->LinkID);
		$end_query = microtime(true);
		$query_time = $end_query - $start_query;
		$this->Row   = 0;
		$this->error_code = mysql_errno();
		$this->error_desc = mysql_error();
		if (!$this->results[$rsi]) {
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
		
		return $this->results[$rsi];
	}

	function info() 
	{
		return mysql_info();
	}

	function next_record() 
	{
		$rsi = $this->rsi;
		if (!$this->results[$rsi]) {
			$this->error_state = 1;
			$this->halt("next_record called with no query pending.");
			return 0;
		}
	
		if ($this->results[$rsi] === true) {
			// update, delete, insert has true value as mysql result
			$stat = false;
		} else {
			$this->records[$rsi] = @mysql_fetch_array($this->results[$rsi]);
			$this->Record = $this->records[$rsi];
			
			$stat = is_array($this->records[$rsi]);
			if (!$stat && $this->AutoFree) {
				$this->free_result();
			}
		}
		return $stat;
	}

	function last_insert_id()
	{
		$rsi = $this->rsi;
		$this->query("SELECT LAST_INSERT_ID()");
		$this->next_record();
		return $this->f(0);
	}

	function affected_rows() 
	{
		return @mysql_affected_rows($this->LinkID);
	}

	function num_rows() 
	{
		$rsi = $this->rsi;
		return @mysql_num_rows($this->results[$rsi]);
	}

	function num_fields() 
	{
		$rsi = $this->rsi;
		return @mysql_num_fields($this->results[$rsi]);
	}

	function f($Name, $field_type = TEXT) 
	{
		$rsi = $this->rsi;
		if (isset($this->records[$rsi][$Name])) {
			$value = $this->records[$rsi][$Name];
			switch($field_type) {
				case DATETIME:
					$value = parse_date($value, $this->DatetimeMask, $date_errors);
					break;
				case DATE:
					$value = parse_date($value, $this->DateMask, $date_errors);
					break;
				case TIME:
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
		$mail_message .= "<b>MySQL Error:</b> " . htmlspecialchars($this->Error) . "<br>" . $eol;
		if ($this->DebugError) {
			$error_message .= "<b>MySQL Error:</b> " . htmlspecialchars($this->Error) . "<br>" . $eol;
		}
		
		// to get notification about errors change email address and uncomment mail line below
		$recipients     = "db_error_email@domain_name";
		$subject        = "DB ERROR " . $this->error_code;
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
			$t->set_var("error_number", $this->error_code);
			
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
				case FLOAT_LIST:
				case FLOATS_LIST:
				case NUMBER_LIST:
				case NUMBERS_LIST:
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
				case INTEGER_LIST:
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
						$values[$v] = "'".addslashes($values[$v])."'";
					}
					return implode(",", $values);
					break;
				default:
					$value = addslashes($value);
					break;
			}
			if ($is_delimiters) {
				$value = "'" . $value . "'";
			}
		} elseif ($use_null) {
			$value = "NULL";
		} else {
			if ($value_type == INTEGER || $value_type == FLOAT || $value_type == NUMBER 
				|| $value_type == NUMBERS_LIST || $value_type == FLOATS_LIST || $value_type == INTEGERS_LIST
				|| $value_type == NUMBER_LIST || $value_type == FLOAT_LIST || $value_type == INTEGER_LIST) {
				$value = 0;
			} elseif ($is_delimiters) {
				$value = "''";
			}
		} 
		return $value;
	}
	
	function describe_error($error_code, $error_msg) 
	{
		if (!$error_msg) {
			if ($error_code == 2005) {
				// Unknown MySQL Server Host '...' (11001)
				$error_msg = DB_HOST_ERROR;
			} else if ($error_code == 2003) {
				// Can't connect to MySQL server on '...' (10061)
				$error_msg = DB_PORT_ERROR;
			} else if ($error_code == 1044) {
				// Access denied for user: '...' to database '...'
				$error_msg = DB_USER_PASS_ERROR;
			} else if ($error_code == 1045) {
				// Access denied for user: '...' (Using password: YES)
				$error_msg = DB_USER_PASS_ERROR;
			} else if ($error_code == 1049) {
				// Unknown database '...'
				$error_msg = str_replace('{db_name}', $this->DBDatabase, DB_NAME_ERROR);
			}
		}
		return $error_msg;
	}

	function get_fields($table_name)
	{
		$rsi = $this->rsi;
		$sql = "SHOW COLUMNS FROM `" . $table_name . "`";
		$this->query($sql);
		if (!$this->results[$rsi]) {
			$this->error_state = 1;
			$this->halt("next_record called with no query pending.");
			return 0;
		}
		$fields = array();
		while ($this->next_record()) {
			$row = $this->records[$rsi];
			if (isset($row['Field'])){
				$name = $row['Field'];
			} else {
				$name = '';
			}
			if (isset($row['Type'])){
				$type = strtoupper($row['Type']);
			} else {
				$type = '';
			}
			if (isset($row['Null']) && (strtoupper($row['Null']) == 'YES')){
				$null = true;
			} else {
				$null = false;
			}
			if (isset($row['Key']) && (strtoupper($row['Key']) == 'PRI')){
				$primary = true;
			} else {
				$primary = false;
			}
			if (isset($row['Key']) && (strtoupper($row['Key']) == 'MUL')){
				$index = true;
			} else {
				$index = false;
			}
			if (isset($row['Key']) && (strtoupper($row['Key']) == 'UNI')){
				$unique = true;
			} else {
				$unique = false;
			}
			if (isset($row['Extra']) && (strtolower($row['Extra']) == 'auto_increment')){
				$auto_increment = true;
			} else {
				$auto_increment = false;
			}
			if (isset($row['Default'])){
				$default = $row['Default'];
			} else {
				$default = '';
			}
			$field = array('name' => $name, 'type' => $type, 'null' => $null, 'primary' => $primary, 'auto_increment' => $auto_increment, 'default' => $default, 'index' => $index, 'unique' => $unique);
			$fields[] = $field;
		}
		return $fields;
	}

	function get_tables()
	{
		$tables = array();
		$sql  = "SHOW TABLES";
		$this->query($sql);
		while ($this->next_record()){
			$tables[] = $this->f(0);
		}
		return $tables;		
	}
	
	function create_database($db_name = "")
	{
		$resource_id = 0;
		if (strlen($db_name) == 0) {
			$db_name = $this->DBDatabase;
		}

		$server = ($this->DBPort != "") ? $this->DBHost . ":" . $this->DBPort : $this->DBHost;
	
		if ($this->DBPersistent) {
			$resource_id = @mysql_pconnect($server, $this->DBUser, $this->DBPassword);
		} else {
			$resource_id = @mysql_connect($server, $this->DBUser, $this->DBPassword);
		}

		if (!$resource_id) {		
			$this->halt("Connect failed: " . $this->describe_error(mysql_errno(), mysql_error()));
			return 0;
		} else {
			if (mysql_query("CREATE DATABASE `$db_name` CHARACTER SET utf8 COLLATE utf8_general_ci", $resource_id)) {
				return 1;
			} else {
				$this->halt($this->describe_error(mysql_errno(), mysql_error()));
				return 0;
			}
		}
	}

	function set_rsi($new_rsi)
	{
		$current_rsi = $this->rsi;
		$this->rsi = $new_rsi;
		return $current_rsi;
	}

}
