<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  common.php                                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	@ini_set("display_errors", "1");
	error_reporting(E_ALL);

	@ini_set("magic_quotes_runtime", 0);
	header("Content-Type: text/html; charset=utf-8");

	// version information
	define("VA_PRODUCT","shop");
	define("VA_TYPE","enterprise");
	define("VA_RELEASE","5.8");
	define("VA_BUILD", "05-Nov-2020");

	// global arrays to use in different blocks and functions
	$va_data = array(); $va_messages = array(); $js_settings = array();
	// add cookie data to global va_data array 
	if (isset($_COOKIE["_va_data"])) {
		$cookie_data = json_decode($_COOKIE["_va_data"], true);
		if (is_array($cookie_data)) {
			foreach ($cookie_data as $key => $value) {	
				$va_data[$key] = $value;
			}
		}
	}

	$root_folder_path = ((isset($is_admin_path) && $is_admin_path) || (isset($is_sub_folder) && $is_sub_folder)) ? "../" : "./";
	$db_lib = "mysql"; $language_code = "en"; // default MySQL library and English language - override in var_definition.php
	$site_id = 1; // default site ID - override in var_definition.php
	$layout_site_id = ""; // which site layout should be used - by default the same as site_id
	$menu_site_id = ""; // which site menu should be used - by default the same as site_id
	@include_once($root_folder_path . "includes/var_definition.php");
	if (!defined("INSTALLED") || !INSTALLED) {
		header("Location: " . $root_folder_path . "install.php");
		exit;	
	}

	// start session
	session_start();

	include_once($root_folder_path . "includes/constants.php");
	include_once($root_folder_path . "includes/common_functions.php");
	include_once($root_folder_path . "includes/va_functions.php");
	include_once($root_folder_path . "includes/cms_functions.php");
	include_once($root_folder_path . "includes/db_query.php");
	include_once($root_folder_path . "includes/db_$db_lib.php");

	// DB Init
	$db = new VA_SQL($db_host, $db_user, $db_password, $db_name, $db_port, $db_persistent, $db_type); 

	include_once($root_folder_path . "includes/sms_functions.php");
	$language_code = get_language(); // when $va_browser_language = true detect language from browser settings
	include_once($root_folder_path ."messages/" . $language_code . "/messages.php");
	include_once($root_folder_path ."messages/" . $language_code . "/cart_messages.php");
	include_once($root_folder_path ."messages/" . $language_code . "/forum_messages.php");
	include_once($root_folder_path ."messages/" . $language_code . "/reviews_messages.php");
	include_once($root_folder_path ."messages/" . $language_code . "/support_messages.php");
	include_once($root_folder_path ."messages/" . $language_code . "/download_messages.php");
	foreach ($va_messages as $constant_name => $constant_value) {
		if(!defined($constant_name)) {
			define($constant_name, $constant_value);
		}
	}
	include_once($root_folder_path . "includes/date_functions.php");
	include_once($root_folder_path . "includes/url.php");
	include_once($root_folder_path . "includes/template.php");
	include_once($root_folder_path . "includes/tree.php");
	if (file_exists($root_folder_path . "includes/license.php") ) {
		include_once($root_folder_path . "includes/license.php");
	}
	if (file_exists($root_folder_path ."messages/".$language_code."/custom_messages.php")) {
		include_once($root_folder_path ."messages/".$language_code."/custom_messages.php");
	}
	if (file_exists($root_folder_path ."includes/custom_functions.php")) {
		include_once($root_folder_path ."includes/custom_functions.php");
	}

	// load currency before main config as we may need currency to retrieve the cart for auto-login
	$currency = get_currency();
	// get user and site configuration 
	$settings = va_config();
	// updates stats about email campaigns clicks
	$eid = get_param("eid");
  if (strlen($eid) && is_numeric($eid)) {
	  $sql  = " UPDATE ".$table_prefix."newsletters_emails ";
		$sql .= " SET is_clicked=1 ";
		$sql .= " WHERE email_id=" . $db->tosql($eid, INTEGER);
  	$db->query($sql);
		set_session("session_eid", $eid);
  }

	$custom_friendly_urls = prepare_custom_friendly_urls();

	// check ssl connection
	$server_https = isset($_SERVER["HTTPS"]) ? strtoupper($_SERVER["HTTPS"]) : "";
	$server_port = isset($_SERVER["SERVER_PORT"]) ? $_SERVER["SERVER_PORT"] : "";
	$server_ssl = isset($_SERVER["SSL"]) ? $_SERVER["SSL"] : "";
	$is_ssl = ($server_https == "ON" || $server_port == 443 || $server_ssl == 1);
