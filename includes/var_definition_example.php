<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  var_definition_example.php                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	//date_default_timezone_set("America/Los_Angeles"); // set your timezone 

	define("INSTALLED", true); // set to false if you want run install.php
	define("DEBUG",     true); // debug mode - set false on live site

	// database parameters
	$db_lib        = "mysql"; // mysql | postgre | odbc
	$db_type       = "mysql"; // mysql | postgre | access | db2
	$db_name       = "viartshop";
	$db_host       = "localhost";
	$db_port       = "";
	$db_user       = "root";
	$db_password   = "";
	$db_persistent = false;

	$table_prefix = "va_";

	$default_language = "en";
	$va_browser_language = false; // change this value to true if you like set language accordingly to user browser settings

	// date parameters
	$datetime_show_format = array("M", "/", "D", "/", "YY", " ", "h", ":", "mm", " ", "AM");
	$date_show_format     = array("D", " ", "MMM", " ", "YYYY");
	$datetime_edit_format = array("M", "/", "D", "/", "YY", " ", "H", ":", "mm");
	$date_edit_format     = array("DD", ".", "MM", ".", "YYYY");
	// save new date with time shift in seconds (3600 - 1 hour)
	//$va_time_shift = 0; 

	// session settings
	$session_prefix = "viartshop";

	// if you use multi-site functionality uncomment the following line and specify appropriate id for current site
	//$site_id = 1;

	// if you use VAT validation uncomment the following line
	//$vat_validation = true;
	// array of country codes for which VAT check is obligatory
	//$vat_obligatory_countries = array("GB");
	// array of country codes for which remote VAT check won't be run
	//$vat_remote_exception_countries = array("NL");

	// if you like to increase/decrease rates from floatrates.com
	// $va_rate_multiplier = 1;

	// if you like to use LIKE operator when search in keywords 
	// $va_keyword_like = 1;

	// if you like to detect and apply user language accordignly to his browser settings
	// $va_browser_language = 1;

	// for security purpose you can hide payment details on admin pages
	// $va_hide_payment_details = 1;
	// if you like to export credit card data uncomment the following line
	// $va_cc_data_export = 1;
	// if you need to encrypt your export file 
	// $va_export_encrypt = 1;

	// how to group items when select shipping methods 
	// 1 - group only products with the same modules type; 2 - group products by the same modules
	$va_shipment_grouping = 2; 

?>