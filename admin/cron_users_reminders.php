<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  cron_users_reminders.php                                 ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	@set_time_limit (300);
	chdir (dirname(__FILE__));
	include_once("./admin_config.php");
	include_once("../includes/common.php");
	include_once("../includes/record.php");
	include_once("../includes/parameters.php");
	include_once("../messages/" . $language_code . "/cart_messages.php");
	include_once("./admin_common.php");

	// Database Initialize
	$db = new VA_SQL();
	$db->DBType      = $db_type;
	$db->DBDatabase  = $db_name;
	$db->DBHost      = $db_host;
	$db->DBPort      = $db_port;
	$db->DBUser      = $db_user;
	$db->DBPassword  = $db_password;
	$db->DBPersistent= $db_persistent;

	check_admin_security("site_users");
	
	$weekdays_values = array(
		array(1, MONDAY),
		array(2, TUESDAY),
		array(4, WEDNESDAY),
		array(8, THURSDAY),
		array(16, FRIDAY),
		array(32, SATURDAY),
		array(64, SUNDAY)
	);

	// check all available user types	
	$types = array();
	$sql = " SELECT type_id, type_name FROM " . $table_prefix . "user_types ";
	$db->query($sql);
	while ($db->next_record()) {
		$type_id = $db->f("type_id");
		$type_name = $db->f("type_name");
		$types[$type_id]["name"] = $type_name;
	}

	// get settings for each user group
	foreach ($types as $type_id => $type_info) {
		$setting_type = "user_profile_" . $type_id;
		$user_profile = array();
		$sql = "SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings WHERE setting_type=" . $db->tosql($setting_type, TEXT);
		$db->query($sql);
		while ($db->next_record()) {
			$user_profile[$db->f("setting_name")] = $db->f("setting_value");
		}
		$types[$type_id]["settings"] = $user_profile;
	}

	$eol = get_eol();
	$site_url = get_setting_value($settings, "site_url", "");
	$today_date = va_time();
	$today_ts = va_timestamp();
	$weekday_index = date("w", $today_ts);
	if ($weekday_index > 0) {
		$weekday_number = $weekdays_values[($weekday_index - 1)][0];
	} else {
		$weekday_number = 64;
	}
	
	// initiliaze template if it doesn't exists
	if (!isset($t)) {
		$t = new VA_Template($settings["admin_templates_dir"]);
	}

	$reminders_errors = ""; $reminders_messages = "";
	$users_reminders = 0;
	$emails_sent = 0; $emails_errors = 0;
	$sms_sent = 0; $sms_errors = 0;

	$current_ts = va_timestamp();
	$current_date = va_time();
	$cyear = $current_date[YEAR]; $cmonth = $current_date[MONTH]; $cday = $current_date[DAY]; 
	$today_start_ts = mktime (0, 0, 0, $current_date[MONTH], $current_date[DAY], $current_date[YEAR]);
	$today_end_ts = mktime (23, 59, 59, $current_date[MONTH], $current_date[DAY], $current_date[YEAR]);

	// check if there are any reminders available to send transfer
	$reminders_sql  = " SELECT * FROM " . $table_prefix . "reminders r LEFT JOIN ".$table_prefix."users u ON (u.user_id=r.user_id)";
	$reminders_sql .= " WHERE (r.reminder_year=" . $db->tosql($current_date[YEAR], INTEGER)." OR r.reminder_year=0) ";
	$reminders_sql .= " AND (r.reminder_month=" . $db->tosql($current_date[MONTH], INTEGER)." OR r.reminder_month=0)";
	$reminders_sql .= " AND (r.reminder_day=" . $db->tosql($current_date[DAY], INTEGER)." OR r.reminder_day=0) ";
	$reminders_sql .= " AND (r.reminder_weekdays&".intval($weekday_number).">0 OR r.reminder_weekdays=0)";
	$reminders_sql .= " AND (r.start_date<=". $db->tosql($today_start_ts, DATETIME)  ." OR r.start_date IS NULL)";
	$reminders_sql .= " AND (r.end_date>=". $db->tosql($today_end_ts, DATETIME) ." OR r.end_date IS NULL)";
	$reminders_sql .= " AND (r.date_sent<". $db->tosql($today_start_ts, DATETIME) ." OR r.date_sent IS NULL)";
	//$reminders_sql .= " AND (r.date_sent<". $db->tosql($current_ts - 300, DATETIME) ." OR r.date_sent IS NULL)"; // test every 5 minutes
	$reminders_sql .= " ORDER BY r.reminder_id ";
			
	$db->RecordsPerPage = 20;
	$db->PageNumber = 1;
	$db->query($reminders_sql);
	if ($db->next_record()) {
		do {
			// read all available reminders in array
			$reminders = array();
		  do {
				$reminder_id = $db->f("reminder_id");
				$reminders[$reminder_id] = $db->Record;
				// update some reminder values
				$registration_date = $db->f("registration_date", DATETIME);
				$registration_date_string = va_date($datetime_show_format, $registration_date);
				$reminders[$reminder_id]["registration_date"] = $registration_date_string;
			} while ($db->next_record());


			// sending reminders 
			foreach ($reminders as $reminder_id => $reminder) {
				$users_reminders++;
				$reminder_type = isset($reminder["reminder_type"]) ? $reminder["reminder_type"] : 1;
				$user_type_id = $reminder["user_type_id"];
				$is_sms_allowed = $reminder["is_sms_allowed"];
				$email = $reminder["email"];
				$cell_phone = $reminder["cell_phone"];
				$t->set_vars($reminder);

				if ($reminder_type == 2) {
					$prefix = "user_auto_cart_";
				} else {
					$prefix = "user_reminder_";
				}

				$type_settings = array();
				if (isset($types[$user_type_id])) {
					$type_settings = $types[$user_type_id]["settings"];
				}

				$user_reminder_mail = get_setting_value($type_settings, $prefix."mail", 0);
				$user_reminder_sms = get_setting_value($type_settings, $prefix."sms", 0);

				if ($user_reminder_mail && $email) {					
					$mail_type = get_setting_value($type_settings, $prefix."message_type");
					$user_subject = get_setting_value($type_settings, $prefix."subject", "");
					$user_message = get_setting_value($type_settings, $prefix."message", "");
					$user_subject = get_translation($user_subject);
					$user_message = get_translation($user_message);
					$t->set_block("user_subject", $user_subject);
					$t->set_block("user_message", $user_message);

					if ($reminder_type == 2) {
						// check cart_id for auto cart reminder if it's available
						$cart_id = ""; $cart_name = "";
						$sql  = " SELECT cart_id FROM " . $table_prefix . "saved_carts ";
						$sql .= " WHERE reminder_id=".$db->tosql($reminder_id, INTEGER);
						$db->query($sql);
						if ($db->next_record()) {
							$cart_id = $db->f("cart_id");
							$cart_name = $db->f("cart_name");
						}
						// build retrieve URL
						$retrieve_url = $site_url."cart_retrieve.php?operation=retrieve&cart_id=".$cart_id."&cart_name=".urlencode($cart_name);
						$t->set_var("retrieve_url", htmlspecialchars($retrieve_url));

						set_saved_items_tag($cart_id, $mail_type, $user_message);
					}
					
					$t->parse("user_subject", false);
					$t->parse("user_message", false);

					$email_headers = array();
					$email_headers["from"] = get_setting_value($type_settings, $prefix."mail_from", $settings["admin_email"]);
					$email_headers["cc"] = get_setting_value($type_settings, $prefix."mail_cc");
					$email_headers["bcc"] = get_setting_value($type_settings, $prefix."mail_bcc");
					$email_headers["reply_to"] = get_setting_value($type_settings, $prefix."mail_reply_to");
					$email_headers["return_path"] = get_setting_value($type_settings, $prefix."mail_return_path");
					$email_headers["mail_type"] = get_setting_value($type_settings, $prefix."message_type");

					$user_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("user_message"));
					
					$email_sent = va_mail($email, $t->get_var("user_subject"), $user_message, $email_headers);
					if ($email_sent) {
						$emails_sent++;
					} else {
						$emails_errors++;
					}
				}		 

				if ($user_reminder_sms && $is_sms_allowed) {
					$user_sms_recipient  = get_setting_value($type_settings, $prefix."sms_recipient", $cell_phone);
					$user_sms_originator = get_setting_value($type_settings, $prefix."sms_originator", "");
					$user_sms_message    = get_setting_value($type_settings, $prefix."sms_message", "");

					$t->set_block("user_sms_recipient",  $user_sms_recipient);
					$t->set_block("user_sms_originator", $user_sms_originator);
					$t->set_block("user_sms_message",    $user_sms_message);

					$t->parse("user_sms_recipient", false);
					$t->parse("user_sms_originator", false);
					$t->parse("user_sms_message", false);

					$user_sms_recipient = $t->get_var("user_sms_recipient");
					$message_sent = sms_send($user_sms_recipient, $t->get_var("user_sms_message"), $t->get_var("user_sms_originator"));
					if ($message_sent) {
						$sms_sent++;
					} elseif ($user_sms_recipient) {
						$sms_errors++;
					}
				}		 

				// always update reminder table that we send notification
				$sql  = " UPDATE " . $table_prefix . "reminders ";
				$sql .= " SET date_sent=". $db->tosql($current_ts, DATETIME);
				$sql .= " WHERE reminder_id=" . $db->tosql($reminder_id, INTEGER);
				$db->query($sql);
			}

			// check for next reminders 
			$db->RecordsPerPage = 10;
			$db->PageNumber = 1;
			$db->query($reminders_sql);
		} while ($db->next_record());
	}

	$reminders_messages .= $users_reminders . " " . REMINDERS_AVAIL_TODAY_MSG . "<br>";
	if ($emails_sent) {
		$reminders_messages .= $emails_sent . " " . REMINDERS_SENT_MSG . "<br>";
	} 
	if ($sms_sent) {
		$reminders_messages .= $sms_sent . " " . REMINDERS_SMS_SENT_MSG . "<br>";
	} 
	if ($emails_errors) {
		$reminders_errors  = $emails_errors . " " . EMAIL_ERRORS_OCCURED_MSG . "<br>";
	} 
	if ($sms_errors) {
		$reminders_errors .= $sms_errors . " " . SMS_ERRORS_OCCURED_MSG . "<br>";
	} 


	function set_saved_items_tag($cart_id, $type, $message)
	{
		global $settings, $db, $t, $table_prefix, $is_admin_path;
		if (strpos($message, "{saved_items}") !== false) {
			if ($is_admin_path) {
				$user_template_path = $settings["templates_dir"];
				if (preg_match("/^\.\//", $user_template_path)) {
					$user_template_path = str_replace("./", "../", $user_template_path);
				} elseif (!preg_match("/^\//", $user_template_path)) {
					$user_template_path = "../" . $user_template_path;
				}
				$t->set_template_path($user_template_path);
			}
			// get template for selected mail type
			if ($type) {
				$prefix = "html_"; 				
				if (!$t->block_exists("html_saved_items")) {
					$t->set_file("html_saved_items", "email_saved_items.html");
				}
			} else {
				$prefix = "text_";
				if (!$t->block_exists("text_saved_items")) {
					$t->set_file("text_saved_items", "email_saved_items.txt");
				}
			}

			// parse supplier items
			$goods_total = 0;
			$t->set_var($prefix."items", "");
			$sql  = " SELECT * FROM " . $table_prefix . "saved_items ";
			$sql .= " WHERE cart_id=".$db->tosql($cart_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$item_id = $db->f("item_id");
				$item_name = $db->f("item_name");
				$quantity = $db->f("quantity");
				$price = $db->f("price");

				$item_code = "";
				$manufacturer_code = "";
				$item_total = $price * $quantity;
				$goods_total += $item_total;

				$t->set_var("item_name", $item_name);
				if (strlen($item_code)) {
					$t->set_var("item_code", $item_code);
					$t->sparse($prefix."item_code_block", false);
				} else {
					$t->set_var($prefix."item_code_block", false);
				}
				if (strlen($manufacturer_code)) {
					$t->set_var("manufacturer_code", $manufacturer_code);
					$t->sparse($prefix."manufacturer_code_block", false);
				} else {
					$t->set_var($prefix."manufacturer_code_block", false);
				}
				$t->set_var("price", currency_format($price));
				$t->set_var("quantity", $quantity);
				$t->set_var("item_total", currency_format($item_total));
				$t->sparse($prefix."items", true);
			}
			$t->set_var("goods_total", currency_format($goods_total));

			// set main saved_items tag
			$t->parse($prefix."saved_items", false);
			$t->set_var("saved_items", $t->get_var($prefix."saved_items"));

			if ($is_admin_path) {
				$t->set_template_path($settings["admin_templates_dir"]);
			}
		}
	}

?>