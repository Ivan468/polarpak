<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  db_pdo.php                                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/



$db_host = 'MD-SQL-A1\WEBSITE';
$db_user = 'msftdyn\viart';
$db_password = 'ViArt23!';
$db_user = '';
$db_password = '';

$db_name = "";
$db_port = "";
$db_persistent = "";
$db_type = "sqlsrv";


echo "start";
	$db = new VA_SQL($db_host, $db_user, $db_password, $db_name, $db_port, $db_persistent, $db_type); 
	$db->db_connect();
echo "end";


class VA_SQL extends PDO {  

	var $DBHost       = "";
	var $DBPort       = "";
	var $DBDatabase   = "";
	var $DBUser       = "";
	var $DBPassword   = "";
	var $DBPersistent = false;
	var $DBConnect    = false;
	var $DBType       = "mysql";
	var $DSN          = "";

	/* 
	dates formats 
	*/
	var $DatetimeMask   = array("YYYY", "-", "MM", "-", "DD", " ", "HH", ":", "mm", ":", "ss");
	var $DateMask       = array("YYYY", "-", "MM", "-", "DD");
	var $TimeMask       = array("HH", ":", "mm", ":", "ss");
	var $TimestampMask  = array("YYYY", "MM", "DD", "HH", "mm", "ss");

	var $AutoFree       = 0;     
	var $LinkID         = 0;
	var $result        = 0;
	var $Offset         = 0;
	var $PageNumber     = 0;
	var $RecordsPerPage = 0;
	var $Record         = array();
	var $Row            = 0;

	// debug variables
	var $Debug          = 1;
	var $DebugError     = 1;
	var $DebugScript    = "";
	var $MaxQueryTime   = 1;

	var $Errno       = 0;
	var $Error       = "";
	var $HaltOnError = "yes"; // "yes", "no", "report"

	public function __construct($dbdata = "", $user = "", $pass = "", $dbname = "", $port = "", $persistent = false, $dbtype = "mysql") {
		if (is_object($dbdata)) {
			$host = $dbdata->DBHost;
			$user = $dbdata->DBUser;
			$pass = $dbdata->DBPassword;
			$dbname = $dbdata->DBDatabase;
			$port   = $dbdata->DBPort;
			$dbtype = $dbdata->DBType;
			$persistent = $dbdata->DBPersistent;
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

		/*
		if (get_session("session_admin_id")) {
			$this->DebugError = 1;
		}//*/
	}

	function check_lib() 
	{
		//return function_exists("mysqli_connect");
	}

	function db_connect($new_connect = false) 
	{
/*
		if ($new_connect && $this->DBConnect) {
			parent::close();
			$this->DBConnect = false;
		} //*/
/*
$conn = new PDO('sqlsrv:Server=localhost\\SQLEXPRESS;Database=MyDatabase', 'MyUsername', 'MyPassword');
$dbh = new PDO("sqlsrv:Server=foo-sql,1433;Database=mydb", $user , $pass);

$dbh = new PDO('mysql:host=localhost;dbname=test', $user, $pass, array(
    PDO::ATTR_PERSISTENT => true
));
*/
		$dsn = "";

		if ($this->DBType == "sqlsrv") {
			$dsn = "sqlsrv:Server=".$this->DBHost;
			if ($this->DBDatabase) { $dsn .= ";Database=".$this->DBDatabase; }
		} else {
    	$dsn = "mysql:host=".$this->DBHost;
			if ($this->DBDatabase) { $dsn .= ";dbname=".$this->DBDatabase; }
		}

echo ":".$dsn;

		try {
    	parent::__construct($dsn, $this->DBUser, $this->DBPassword);
	    foreach(parent::query("select * from sys.databases") as $row) {
  	      print_r($row);
    	}
		} catch (PDOException $e) {
    	print "Error!: " . $e->getMessage() . "<br/>";
	    die();
		}

		/*
		// if data set could try to connect
		if (!$this->DBConnect) {
			if ($this->DBPort) {
				@parent::__construct($this->DBHost, $this->DBUser, $this->DBPassword, $this->DBDatabase, $this->DBPort);
			} else {
				@parent::__construct($this->DBHost, $this->DBUser, $this->DBPassword, $this->DBDatabase);
			}
			if ($this->connect_error) {
				$this->halt("Connect Error: (" . $this->connect_errno . ") " . $this->connect_error);
				return false;
			} else {
				$this->DBConnect = true; 
			}
		}

		parent::query("set names 'utf8'");
		parent::set_charset('utf8');
		*/
		return true;		
	}


	function my_free_result()
	{
		if ($this->result && !is_bool($this->result)) {
			$this->result->free_result();
			//@mysql_free_result($this->result);
			$this->result = 0;
		}
	}

	function close() 
	{
		if ($this->result) {
			$this->my_free_result();
		}
		parent::close();
	}

/*
	function query($query_string) 
	{
		global $is_admin_path;
		if ($query_string == "") {
			return 0;
		}
		if (!$this->DBConnect) {
			$this->db_connect();
		}
	
		if ($this->result) {
			$this->my_free_result();
		}
	
		if ($this->RecordsPerPage && !is_numeric($this->RecordsPerPage)) {
			$this->RecordsPerPage = 10;
		}

		if ($this->RecordsPerPage && $this->PageNumber) {
			$query_string .= " LIMIT " . (($this->PageNumber - 1) * $this->RecordsPerPage) . ", " . $this->RecordsPerPage;
			$this->RecordsPerPage = 0;
			$this->PageNumber = 0;
		} else if ($this->RecordsPerPage) {
			$query_string .= " LIMIT " . $this->Offset . ", " . $this->RecordsPerPage;
			$this->Offset = 0;
			$this->RecordsPerPage = 0;
		}
	
		
		$start_query = microtime(true);
		$this->result = parent::query($query_string);

		$end_query = microtime(true);
		$query_time = $end_query - $start_query;
		$this->Row   = 0;
		$this->Errno = $this->errno;
		$this->Error = $this->error;
		if (!$this->result) {
			if ($this->DebugError) {
				$this->halt("Invalid SQL: " . $query_string);
			} else {
				$this->halt("Invalid SQL.");
			}
			return false;
		} else if ($this->Debug && $query_time > $this->MaxQueryTime) {
			if (isset($is_admin_path) && $is_admin_path) {
				$log_file = "../../logs/slow_queries.log";
			} else {
				$log_file = "../logs/slow_queries.log";
			}
			save_log_file($log_file, "Query Time: ".$query_time, $query_string);
		}
		
		return true;
	}
*/

	function info() 
	{
		return $this->info;
	}

	function next_record() 
	{
		if (!$this->result) {
			$this->halt("next_record called with no query pending.");
			return 0;
		}
		if ($this->result === true) {
			// update, delete, insert has true value as mysql result
			$stat = false;
		} else {
			$this->Record = $this->result->fetch_array();
			$this->Row   += 1;
			$this->Errno = $this->errno;
			$this->Error = $this->error;
			
			$stat = is_array($this->Record);
			if (!$stat && $this->AutoFree) {
				$this->my_free_result();
			}
		}
		return $stat;
	}

	function seek($pos = 0) 
	{
		$status = @mysql_data_seek($this->result, $pos);
		if ($status) {
			$this->Row = $pos;
		} else {
			$this->halt("seek($pos) failed: result has " . $this->num_rows() . " rows");
		
			@mysql_data_seek($this->result, $this->num_rows());
			$this->Row = $this->num_rows;
			return 0;
		}
		
		return 1;
	}

	function affected_rows() 
	{
		return mysqli_affected_rows($this->LinkID);
	}

	function num_rows() 
	{
		return mysqli_num_rows($this->result);
	}

	function num_fields() 
	{
		return mysqli_num_fields($this->result);
	}

	function f($Name, $field_type = TEXT) 
	{
		if (isset($this->Record[$Name])) {
			$value = $this->Record[$Name];
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
	
		if (!$this->Error) {
			$this->Error = $message;
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
		if ($this->DebugError) {
			$error_message .= "<b>MySQL Error:</b> " . htmlspecialchars($this->Error) . "<br>" . $eol;
		}
		
		// to get notification about errors change email address and uncomment mail line below
		$recipients     = "db_error_email@domain_name";
		$subject        = "DB ERROR " . $this->Errno;
		$message        = strip_tags($error_message);
		$email_headers = array();
		$email_headers["from"] = "db_error_email@domain_name";
		$email_headers["mail_type"] = 0;
		//va_mail($recipients, $subject, $message, $email_headers);
		
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
			$t->set_var("error_number", $this->Errno);
			
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
					return (is_array($sql_values)) ? implode(",", $sql_values) : "NULL";
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
				|| $value_type == NUMBERS_LIST || $value_type == FLOATS_LIST || $value_type == INTEGERS_LIST) {
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
		$sql = "SHOW COLUMNS FROM `" . $table_name . "`";
		$this->query($sql);
		if (!$this->result) {
			$this->halt("next_record called with no query pending.");
			return 0;
		}
	
		$fields = array();
		while ($this->next_record()) {
			$row = $this->Record;
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

		if ($this->DBPort) {
			parent::__construct($this->DBHost, $this->DBUser, $this->DBPassword, "", $this->DBPort);
		} else {
			parent::__construct($this->DBHost, $this->DBUser, $this->DBPassword, "");
		}
		if ($this->connect_error) {
			$this->halt('Connect Error (' . $this->connect_errno . ') ' . $this->connect_error);
			return false;
		} else {
			$this->DBConnect = true; 
		}

		return $this->query("CREATE DATABASE `$db_name` CHARACTER SET utf8 COLLATE utf8_general_ci");
	}

	function __destructor()
	{
		parent::close();
	}
}

?>