<?php

	define("INSTALLED", true); // set to false if you want run install.php
	define("DEBUG",     true); // debug mode - set false on live site

	// database parameters
	$db_lib        = "mysql"; // mysql | mysqli | postgre | sqlsrv | odbc
	$db_type       = "mysql"; // mysql | postgre | sqlsrv | access | db2
	$db_name       = "pak";
	$db_host       = "localhost";
	$db_port       = "3306";
	$db_user       = "root";
	$db_password   = "bima2011";
	$db_persistent = true;

	$table_prefix  = "va_";

	$default_language = "en";

	$va_browser_language = false; // change this value to true if you like set language accordingly to user browser settings

	// date parameters
	$datetime_show_format = array("D", " ", "MMM", " ", "YYYY", ", ", "h", ":", "mm", " ", "AM");
	$date_show_format     = array("D", " ", "MMM", " ", "YYYY");
	$datetime_edit_format = array("YYYY", "-", "MM", "-", "DD", " ", "HH", ":", "mm", ":", "ss");
	$date_edit_format     = array("YYYY", "-", "MM", "-", "DD");

	// session settings
	$session_prefix = "pak";

	// if you use multi-site functionality uncomment the following line and specify appropriate id
	//$site_id = 1;

	// if you use VAT validation uncomment the following line
	//$vat_validation = true;
	// array of country codes for which VAT check is obligatory
	//$vat_obligatory_countries = array("GB");
	// array of country codes for which remote VAT check won't be run
	//$vat_remote_exception_countries = array("NL");

?>