<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  ajax_event.php                                           ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	error_reporting (E_ALL);
	session_start();

	// include common files
	include_once("./includes/var_definition.php");
	include_once("./includes/constants.php");
	include_once("./includes/common_functions.php");
	include_once("./includes/va_functions.php");
	include_once("./includes/db_$db_lib.php");
	$language_code = get_language("messages.php");
	include_once("./messages/".$language_code."/messages.php");
	include_once("./includes/date_functions.php");

	header("Pragma: no-cache");
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");
	header("Content-Type: text/html; charset=" . CHARSET);

	// Database Initialize
	$db = new VA_SQL();
	$db->DBType      = $db_type;
	$db->DBDatabase  = $db_name;
	$db->DBHost      = $db_host;
	$db->DBPort      = $db_port;
	$db->DBUser      = $db_user;
	$db->DBPassword  = $db_password;
	$db->DBPersistent= $db_persistent;

	$event = get_param("event");
	if ($event == "welcome-popup") {
		$code = get_param("code");
		// save popup code in track cookie
		$va_track = json_decode(get_cookie("_va_track"), true);
		$cookie_popups = get_setting_value($va_track, "popups");
		if (!is_array($cookie_popups)) { $cookie_popups = array(); }
		if (count($cookie_popups) > 9) { 
			$cookie_popups = array_shift ($cookie_popups);
		}
		$cookie_popups[] = $code;
		$va_track["popups"] = $cookie_popups;
		setCookie("_va_track", json_encode($va_track), time() + (3600 * 24 * 366));

		$session_popups = get_session("popups");
		if (!is_array($session_popups)) { $session_popups = array(); }
		$session_popups[] = $code;
		set_session("popups", $session_popups);
	}
