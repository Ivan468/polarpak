#!/usr/bin/php -q
<?php

	@set_time_limit(300);
	chdir(dirname(__FILE__));
	$root_folder_path = "../";
	include_once($root_folder_path . "includes/var_definition.php");
	include_once($root_folder_path . "includes/constants.php");
	include_once($root_folder_path . "includes/common_functions.php");
	include_once($root_folder_path . "includes/va_functions.php");
	$language_code = get_language("messages.php");
	include_once($root_folder_path . "messages/".$language_code."/messages.php");
	include_once($root_folder_path . "includes/db_$db_lib.php");
	include_once($root_folder_path . "includes/date_functions.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/template.php");

	// Database Initialize
	$db = new VA_SQL();
	$db->DBType      = $db_type;
	$db->DBDatabase  = $db_name;
	$db->DBHost      = $db_host;
	$db->DBPort      = $db_port;
	$db->DBUser      = $db_user;
	$db->DBPassword  = $db_password;
	$db->DBPersistent= $db_persistent;

	// get site properties
	$settings = get_settings("global");

	$is_sub_folder = true; // to correctly handle relative attachments folder
	$save_email_copy = false; // is email copy should be saved
	$emails_folder = "./"; // in this folder can be saved all emails copies received by email parser
	include($root_folder_path . "includes/support_parser.php");

