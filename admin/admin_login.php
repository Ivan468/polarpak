<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_login.php                                          ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ("./admin_common.php");

	$secure_admin_login = get_setting_value($settings, "secure_admin_login", 0);
	$secure_url = get_setting_value($settings, "secure_url", "");
	$site_url = get_setting_value($settings, "site_url", "");
	$eol = get_eol();

	$login_settings = get_settings("two_factor");
	$admin_two_factor = get_setting_value($login_settings, "admin_two_factor", "");
	
	$current_version = va_version();

	if ($secure_admin_login && $secure_url) {
		$admin_login_url = $admin_secure_url . "admin_login.php";
	} else {
		$admin_login_url = "admin_login.php";
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	if (!file_exists($t->get_template_path() . "/admin_layouts.html")) {
		$t->set_template_path("../templates/admin");
	}
	$t->set_file("main","admin_login.html");
	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_login_href", "admin_login.php");
	$t->set_var("admin_login_url", $admin_login_url);
	$t->set_var("admin_privileges_href", "admin_privileges.php");

	$return_page = get_param("return_page");
	if (!strlen($return_page)) { $return_page = "index.php"; }
	// for security purposes re-generate return page and remove operation parameter
	$parsed_url = parse_url($return_page);
	$return_page = $parsed_url["path"];
	$query = isset($parsed_url["query"]) ? $parsed_url["query"] : "";
	if ($query) {
		$new_params = 0;
		$query_params = explode("&", $query);
		for ($qp = 0; $qp < sizeof($query_params); $qp++) {
			$query_param = $query_params[$qp];
			if (preg_match("/^([^=]+)=(.*)$/", $query_param, $matches)) {
				$param_name = $matches[1]; $param_value = $matches[2];
			} else {
				$param_name = $query_param; $param_value = "";
			}
			if ($param_name == "operation") {
				$param_value = "reload";
			}
			$new_params++;
			$return_page .= ($new_params == 1) ? "?" : "&";
			$return_page .= $param_name."=".$param_value;
		}
	}

	if ($secure_admin_login && $secure_url) {
		$slash_position = strrpos ($return_page, "/");
		$redirect_page = ($slash_position === false) ? $return_page : substr($return_page, $slash_position + 1);
		//$redirect_url = $site_url . $admin_folder . $redirect_page;
		$redirect_url = $admin_secure_url . $redirect_page;
	} else {
		$redirect_url = $return_page;
	}

	$operation = get_param("operation");
	$errors = false; $errors_list = ""; $login = ""; $post_data = "";
	if (strlen($operation))
	{
		if ($operation == "cancel_login")
		{
			header("Location: " . $site_url . "index.php");
			exit;
		}
		elseif ($operation == "logout")
		{
			set_session("session_admin_id", "");
			set_session("session_admin_privilege_id", "");
			set_session("session_admin_name", "");
			set_session("session_admin_permissions", "");
			set_session("session_last_order_id", "");
			set_session("session_last_user_id", "");
			set_session("session_warn_permission", "");
		} elseif ($operation == "access") {
			$access_admin_id = get_session("session_access_admin_id");
			$access_code = get_param("access_code");
			$post_data = get_param("post_data");
			
			if (!strlen($access_admin_id)) {
				$operation = "login";
			} else {
				if (!strlen($access_code)) {
					$error_message = str_replace("{field_name}", ACCESS_CODE_MSG, REQUIRED_MESSAGE);
					$errors_list .= $error_message . "<br>";
					$errors = true;
				}

				$access_attempts = 0;
				if (!$errors) {
					// check access parameters 
					$sql  = " SELECT * FROM " . $table_prefix . "admins ";
					$sql .= " WHERE admin_id=" . $db->tosql($access_admin_id, INTEGER);
					$db->query($sql);
					if ($db->next_record()) {
						$access_attempts = $db->f("access_attempts");
						$access_failed = $db->f("access_failed", DATETIME);
						$access_failed_ts = va_timestamp($access_failed);
						$current_ts = va_timestamp();
						if ($access_attempts) {
							// check time for next attempt
							$next_attempt = $access_failed_ts + pow(2, $access_attempts);
							$time_left = $next_attempt - $current_ts;
							if ($time_left > 0) {
								$time_left = str_replace("{quantity}", $time_left, SECONDS_QTY_MSG);
								$interval_error = str_replace("{interval_time}", $time_left, LOGIN_INTERVAL_ERROR);
								$errors_list .= $interval_error . "<br>";
								$errors = true;
							}
						}
					}
				} // end check access parameters

				if (!$errors) {
					$sql  = " SELECT * FROM " . $table_prefix . "admins ";
					$sql .= " WHERE admin_id=" . $db->tosql($access_admin_id, INTEGER);
					$sql .= " AND access_code=" . $db->tosql($access_code, TEXT);
					$db->query($sql);
					if ($db->next_record()) {
						$admin_data = $db->Record;
						$admin_id = $db->f("admin_id");
						// login administrator
						$include_page = login_admin($admin_data);
						if ($include_page) {
							include($include_page);
							exit;
						} else {
							header("Location: " . $redirect_url); 
							exit;
						}
					} else {
						// save information about access error
						$access_attempts++;
						$sql  = " UPDATE " . $table_prefix . "admins ";
						$sql .= " SET access_attempts=" . $db->tosql($access_attempts, INTEGER);
						$sql .= " , access_failed=" . $db->tosql(va_time(), DATETIME);
						$sql .= " WHERE admin_id=" . $db->tosql($access_admin_id, INTEGER);
						$db->query($sql);
					}
				}
			}
			
			
		} elseif ($operation == "login") {
			$login     = get_param("login");
			$password  = get_param("password");
			$post_data = get_param("post_data");
			$ip_address = get_ip();
			
			if (!strlen($login)) {
				$error_message = str_replace("{field_name}", LOGIN_FIELD, REQUIRED_MESSAGE);
				$errors_list .= $error_message . "<br>";
				$errors = true;
			}
	  
			if (!strlen($password)) {
				$error_message = str_replace("{field_name}", PASSWORD_FIELD, REQUIRED_MESSAGE);
				$errors_list .= $error_message . "<br>";
				$errors = true;
			}

			/* check for black ips
			if (!$errors && check_black_ip()) {
				$errors_list = BLACK_IP_MSG;
				$errors = true;
			}//*/

			if (!$errors) {
				// check unsuccessful logins with wrong login for user IP address for last hour
				$check_date_ts = va_timestamp() - 3600;
				$sql  = " SELECT COUNT(*) AS wrong_logins, MAX(date_added) AS last_date FROM " . $table_prefix . "admins_login_stats ";
				$sql .= " WHERE ip_address=" . $db->tosql($ip_address, TEXT);
				$sql .= " AND login_status=0 ";
				$sql .= " AND admin_id=0 ";
				$sql .= " AND date_added>".$db->tosql($check_date_ts, DATETIME);
				$db->query($sql);
				if ($db->next_record()) {
					$wrong_logins = $db->f("wrong_logins");
					$last_date = $db->f("last_date", DATETIME);
					$last_date_ts = va_timestamp($last_date);
					if ($wrong_logins) {
						$current_ts = va_timestamp();
						$next_attempt = $last_date_ts + pow(2, $wrong_logins);
						$time_left = $next_attempt - $current_ts;
						if ($time_left > 0) {
							$time_left = str_replace("{quantity}", $time_left, SECONDS_QTY_MSG);
							$interval_error = str_replace("{interval_time}", $time_left, LOGIN_INTERVAL_ERROR);
							$errors_list .= $interval_error . "<br>";
							$errors = true;
						}
					}
				}
			}

			if (!$errors) {
				// check login parameters 
				$sql  = " SELECT * FROM " . $table_prefix . "admins ";
				$sql .= " WHERE login=" . $db->tosql($login, TEXT);
				$db->query($sql);
				if ($db->next_record()) {
					$login_attempts = $db->f("login_attempts");
					$login_failed = $db->f("login_failed", DATETIME);
					$login_failed_ts = va_timestamp($login_failed);
					$current_ts = va_timestamp();
					if ($login_attempts) {
						// check time for next attempt
						$next_attempt = $login_failed_ts + pow(2, ($login_attempts % 6));
						$time_left = $next_attempt - $current_ts;
						if ($time_left > 0) {
							$time_left = str_replace("{quantity}", $time_left, SECONDS_QTY_MSG);
							$interval_error = str_replace("{interval_time}", $time_left, LOGIN_INTERVAL_ERROR);
							$errors_list .= $interval_error . "<br>";
							$errors = true;
						}
					}
				}
			}


			if (!$errors)
			{
				$password_encrypt = get_setting_value($settings, "password_encrypt", 0);
				$admin_password_encrypt = get_setting_value($settings, "admin_password_encrypt", $password_encrypt);
				if ($admin_password_encrypt == 1) {
					$password_match = md5($password);
				} else {
					$password_match = $password;
				}

				// prepare information for statistics
				$ip_address = get_ip();
				$forwarded_ips = get_var("HTTP_X_FORWARDED_FOR");
				$date_added = va_time();

				$sql  = " SELECT * FROM " . $table_prefix . "admins ";
				$sql .= " WHERE login=" . $db->tosql($login, TEXT);
				$sql .= " AND password=" . $db->tosql($password_match, TEXT);
				$db->query($sql);
				if ($db->next_record()) {
					
					$admin_data = $db->Record;
					$admin_id = $db->f("admin_id");
					$admin_email = $db->f("email");
					$admin_cell_phone = $db->f("cell_phone");

					if ($admin_two_factor) {
						$access_code = $db->f("access_code");
						// check if it expired
						if ($access_code) {
							$access_added = $db->f("access_added", DATETIME);
							$current_ts = va_timestamp();
							$access_added_ts = va_timestamp($access_added);
							$access_expiration_ts = $access_added_ts + 300; 
							if ($current_ts > $access_expiration_ts) {
								$access_code = "";
							}
						}

						// generate and save access code
						if (!$access_code) {
							$access_code = mt_rand (100000, 999999);

							$sql  = " UPDATE " . $table_prefix . "admins ";
							$sql .= " SET access_code=" . $db->tosql($access_code, TEXT);
							$sql .= " , access_added=" . $db->tosql(va_time(), DATETIME);
							$sql .= " WHERE admin_id=" . $db->tosql($admin_id, INTEGER);
							$db->query($sql);
						}

						// set access_code for notification
						$t->set_var("access_code", $access_code);
						// set access operation and save admin_id 
						$operation = "access";
						set_session("session_access_admin_id", $admin_id);

						$admin_notification = get_setting_value($login_settings, "admin_notification", "");
						$admin_sms_notification = get_setting_value($login_settings, "admin_sms_notification", "");

						if ($admin_notification && $admin_email)
						{
							$admin_subject = get_setting_value($login_settings, "admin_subject", ACCESS_CODE_MSG);
							$admin_message = get_setting_value($login_settings, "admin_message", $access_code);

							$t->set_block("admin_subject", $admin_subject);
							$t->set_block("admin_message", $admin_message);
          
							$mail_to = $admin_email;
							$mail_from = get_setting_value($login_settings, "admin_mail_from", $settings["admin_email"]);
							$email_headers = array();
							$email_headers["from"] = parse_value($mail_from);
							$email_headers["cc"] = get_setting_value($login_settings, "admin_mail_cc");
							$email_headers["bcc"] = get_setting_value($login_settings, "admin_mail_bcc");
							$email_headers["reply_to"] = get_setting_value($login_settings, "admin_mail_reply_to");
							$email_headers["return_path"] = get_setting_value($login_settings, "admin_mail_return_path");
							$email_headers["mail_type"] = get_setting_value($login_settings, "admin_message_type");
          
							$t->parse("admin_subject", false);
							$t->parse("admin_message", false);
							$admin_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("admin_message"));
							va_mail($mail_to, $t->get_var("admin_subject"), $admin_message, $email_headers);
						}

						if ($admin_sms_notification)
						{
							$admin_sms_recipient  = get_setting_value($login_settings, "admin_sms_recipient", $admin_cell_phone);
							$admin_sms_originator = get_setting_value($login_settings, "admin_sms_originator", "");
							$admin_sms_message    = get_setting_value($login_settings, "admin_sms_message", $access_code);
			    
							$t->set_block("admin_sms_recipient",  $admin_sms_recipient);
							$t->set_block("admin_sms_originator", $admin_sms_originator);
							$t->set_block("admin_sms_message",    $admin_sms_message);
			    
							$t->parse("admin_sms_recipient", false);
							$t->parse("admin_sms_originator", false);
							$t->parse("admin_sms_message", false);

							$admin_sms_recipient = $t->get_var("admin_sms_recipient");
			    
							if ($admin_sms_recipient) {
								$sms_sent = sms_send($admin_sms_recipient, $t->get_var("admin_sms_message"), $t->get_var("admin_sms_originator"), $sms_errors);
								if (!$sms_sent) {
									$errors_list .= "SMS Gateway Error: " . $sms_errors . "<br>";
									$errors = true;
								}
							}
						}
						// clear access_code for template
						$t->set_var("access_code", "");



					} else {
						// login administrator
						login_admin($admin_data);
						$include_page = login_admin($admin_data);
						if ($include_page) {
							include($include_page);
							exit;
						} else {
							header("Location: " . $redirect_url); 
							exit;
						}
					}

				} else {
					$errors_list .= LOGIN_PASSWORD_ERROR . "<br>";
					$errors = true;

					if (comp_vers(va_version(), "3.5.24") == 1) {
						$stat_admin_id = 0;
						$login_attempts = 0;

						// check if we can save statistics
						$sql  = " SELECT *  ";
						$sql .= " FROM " . $table_prefix . "admins WHERE ";
						$sql .= " login = " . $db->tosql($login, TEXT);
						$db->query($sql);
						if ($db->next_record()) {
							// save login statistics
							$stat_admin_id = $db->f("admin_id");
							$login_attempts = $db->f("login_attempts");
						}

						$sql  = " INSERT INTO " . $table_prefix . "admins_login_stats ";
						$sql .= " (admin_id, login_status, ip_address, forwarded_ips, date_added) VALUES (";
						$sql .= $db->tosql($stat_admin_id, INTEGER) . ", ";
						$sql .= $db->tosql(0, INTEGER) . ", ";
						$sql .= $db->tosql($ip_address, TEXT) . ", ";
						$sql .= $db->tosql($forwarded_ips, TEXT) . ", ";
						$sql .= $db->tosql($date_added, DATETIME) . ") ";
						$db->query($sql);

						if ($stat_admin_id && comp_vers(va_version(), "4.1.15") == 1) {
							$login_attempts++;
							$sql  = " UPDATE " . $table_prefix . "admins ";
							$sql .= " SET login_attempts=" . $db->tosql($login_attempts, INTEGER);
							$sql .= " , login_failed=" . $db->tosql(va_time(), DATETIME);
							$sql .= " WHERE admin_id=" . $db->tosql($stat_admin_id, INTEGER);
							$db->query($sql);
						}
					}

					// make a small delay to prevent automatic passwords checks
					sleep(1);
				}
			}
		}
	}

	if ($operation == "access")	{

		$t->set_var("return_page", htmlspecialchars($return_page));
		$t->set_var("post_data", htmlspecialchars($post_data));
		$t->set_var("operation", "access");
		$t->parse("access_form", false);

	} else if (get_session("session_admin_id")) {
		$t->set_var("LOGIN_AS_NAME", str_replace("{user_name}", "'<b>".get_session("session_admin_name")."</b>'", LOGIN_AS_MSG));
		$t->set_var("operation", "logout");
		$t->set_var("login_form", "");
		$t->parse("logout_form", false);
	}	else {
		$t->set_var("return_page", htmlspecialchars($return_page));
		$t->set_var("login", htmlspecialchars($login));
		$t->set_var("operation", "login");
		$t->set_var("logout_form", "");
		$t->parse("login_form", false);
	}

	$type_error = get_param("type_error");
	if ($type_error == 1) {
		$t->parse("session_expired", false);
		$errors = true;
		// check if post data available to save and pass it
		if (is_array($_POST) && sizeof($_POST) > 0) {
			// disable automatic repost to run SQL query
			if (isset($_POST["operation"])) {
				$_POST["operation"] = "reload";
			}

			foreach($_POST as $key => $value) {
				if ($post_data) { $post_data .= "&"; }
				$post_data .= urlencode($key)."=".urlencode($value);
			}
		}
	} else if ($type_error == 2) {
		$t->parse("access_error", false);
		$errors = true;
	}
	// set post data if available
	$t->set_var("post_data", htmlspecialchars($post_data));
	if ($errors) {
		$t->set_var("errors_list", $errors_list);
		$t->parse("errors", false);
	}	else {
		$t->set_var("errors", "");
	}

	$t->pparse("main");


function login_admin($admin_data)
{
	global $db, $table_prefix, $redirect_url;

	// get post data
	$post_data = get_param("post_data");

	// prepare information for statistics
	$ip_address = get_ip();
	$forwarded_ips = get_var("HTTP_X_FORWARDED_FOR");
	$date_added = va_time();

	$admin_id = $admin_data["admin_id"];
	$privilege_id = $admin_data["privilege_id"];

	set_session("session_admin_id", $admin_data["admin_id"]);
	set_session("session_admin_privilege_id", $admin_data["privilege_id"]);
	set_session("session_admin_name", $admin_data["admin_name"]);
	set_session("session_last_order_id", $admin_data["last_order_id"]);
	set_session("session_last_user_id", $admin_data["last_user_id"]);
	set_session("session_access_admin_id", "");


	if (comp_vers(va_version(), "4.1.16") == 1) {
		// clear access data after successfull login
		$sql  = " UPDATE " . $table_prefix . "admins ";
		$sql .= " SET login_attempts=0";
		$sql .= " , login_failed=NULL";
		$sql .= " , access_code=NULL";
		$sql .= " , access_added=NULL";
		$sql .= " , access_attempts=0";
		$sql .= " , access_failed=NULL";
		$sql .= " WHERE admin_id=" . $db->tosql($admin_id, INTEGER);
		$db->query($sql);
	}
	
	// save login statistics
	if (comp_vers(va_version(), "3.5.24") == 1) {
		$sql  = " INSERT INTO " . $table_prefix . "admins_login_stats ";
		$sql .= " (admin_id, login_status, ip_address, forwarded_ips, date_added) VALUES (";
		$sql .= $db->tosql($admin_id, INTEGER) . ", ";
		$sql .= $db->tosql(1, INTEGER) . ", ";
		$sql .= $db->tosql($ip_address, TEXT) . ", ";
		$sql .= $db->tosql($forwarded_ips, TEXT) . ", ";
		$sql .= $db->tosql($date_added, DATETIME) . ") ";
		$db->query($sql);
	}

	$admin_info = array();
	$sql  = " SELECT * FROM " . $table_prefix . "admin_privileges ";
	$sql .= " WHERE privilege_id=" . $db->tosql($privilege_id, INTEGER, true, false);
	$db->query($sql);
	if ($db->next_record()) {
		$admin_info = $db->Record;
	}
	set_session("session_admin_info", $admin_info);

	$permissions = array();
	$sql  = " SELECT block_name, permission FROM " . $table_prefix . "admin_privileges_settings ";
	$sql .= " WHERE privilege_id=" . $db->tosql($privilege_id, INTEGER, true, false);
	$db->query($sql);
	while ($db->next_record()) {
		$block_name = $db->f("block_name");
		$permissions[$block_name] = $db->f("permission");
	}
	set_session("session_admin_permissions", $permissions);	
	
	if ((comp_vers(va_version(), "2.8.2") == 1) && (strpos($redirect_url, "admin.php"))) {
		$sql  = " SELECT url ";
		$sql .= " FROM " . $table_prefix . "bookmarks ";
		$sql .= " WHERE is_start_page=1 AND admin_id=" . $db->tosql($admin_id, INTEGER);
		$start_url = get_db_value($sql);
		if ($start_url) {
			$redirect_url = $start_url;
		}
	}

	if ($post_data && $return_page) {
		// clear all data and re-submit post data
		foreach ($_GET as $key => $value) {
			unset($_GET[$key]);
		}
		foreach ($_POST as $key => $value) {
			unset($_POST[$key]);
		}
		$admin_page = basename($return_page);
		if (preg_match("/\?(.*)$/", $admin_page, $matches)) {
			$get_data = $matches[1];
			$admin_page = preg_replace("/\?.*$/", "", $admin_page);
			if ($get_data) {
				// set GET data
				$query_params = explode("&", $get_data);
				for ($qp = 0; $qp < sizeof($query_params); $qp++) {
					$query_param = $query_params[$qp];
					if (preg_match("/^([^=]+)=(.*)$/", $query_param, $matches)) {
						$_GET[urldecode($matches[1])] = urldecode(($matches[2]));
					} else {
						$_GET[$query_param] = "";
					}
				}
			}
		}
		// set POST data
		$query_params = explode("&", $post_data);
		for ($qp = 0; $qp < sizeof($query_params); $qp++) {
			$query_param = $query_params[$qp];
			if (preg_match("/^([^=]+)=(.*)$/", $query_param, $matches)) {
				$_POST[urldecode($matches[1])] = urldecode(($matches[2]));
			} else {
				$_POST[$query_param] = "";
			}
		}

		return $admin_page;
	}
	return "";
}

?>