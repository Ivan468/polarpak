<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_user_carts.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/
                                   	
		
	@set_time_limit (900);
	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/shopping_cart.php");
	include_once($root_folder_path . "includes/order_items.php");
	include_once($root_folder_path . "includes/order_links.php");
	include_once($root_folder_path . "includes/parameters.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("orders_recover");

	// additional connection 
	$dbs = new VA_SQL();
	$dbs->DBType      = $db_type;
	$dbs->DBDatabase  = $db_name;
	$dbs->DBUser      = $db_user;
	$dbs->DBPassword  = $db_password;
	$dbs->DBHost      = $db_host;
	$dbs->DBPort      = $db_port;
	$dbs->DBPersistent= $db_persistent;

	$operation = get_param("operation");	
	$setting_type = "order_recover";
	$orders_currency = get_setting_value($settings, "orders_currency", 0);
	$online_time = get_setting_value($settings, "online_time", 5);
	
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_orders_recover.html");
	$t->set_var("date_edit_format", join("", $date_edit_format));
	$t->set_var("admin_orders_reminder_href", "admin_orders_recover.php?operation=send");
	$t->set_var("admin_orders_reminder_filtered_href", "admin_orders_recover.php?operation=send_filtered");
	$t->set_var("admin_orders_recover_href", "admin_orders_recover.php");
	$t->set_var("admin_orders_recover_settings_href", "admin_orders_recover_settings.php");
	
	$sql  = "SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= "WHERE (setting_type='" . $setting_type . "') ";
	$recover_settings = array();
	if ($multisites_version) {
		if (isset($site_id) && ($site_id>1) )  {
			$sql .= "AND ( site_id=1 OR site_id = " . $db->tosql($site_id, INTEGER) ." ) ";
			$sql .= "ORDER BY site_id ASC ";
		} else {
			$sql .= "AND site_id=1 ";
		}
	}		
	$db->query($sql);
	while ($db->next_record()) {
		$recover_settings[$db->f("setting_name")] = $db->f("setting_value");
	}
	
	
	$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings WHERE setting_type='order_info' ";		
	if ($multisites_version) {
		$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id,INTEGER) . ") ";
		$sql .= " ORDER BY site_id ASC ";
	}
	$db->query($sql);
	while ($db->next_record()) {
		$order_info[$db->f("setting_name")] = $db->f("setting_value");
	}
	
	$order_types =
		array(
			array("0", ABANDONED_MSG), array("1", FINISHED_MSG), array("", ALL_MSG)
		);
	$periods = 
		array(
			array("", ""), array("today", TODAY_MSG), array("yesterday", YESTERDAY_MSG), array("last7days", LAST_7DAYS_MSG),
			array("thismonth", THIS_MONTH_MSG),	array("lastmonth", LAST_MONTH_MSG),	array("thisquarter", THIS_QUARTER_MSG), 
			array("thisyear", THIS_YEAR_MSG)
		);
	$sql  = " SELECT status_id, status_name FROM " . $table_prefix . "order_statuses ";
	$sql .= " WHERE is_active=1 AND paid_status=0 ORDER BY status_order, status_id";
	$order_statuses = get_db_values($sql, array(array("", "")));
	$yes_no_all = 
		array( 
			array(0, NO_MSG), array(1, YES_MSG), array("", ALL_MSG)
		);
	

	// prepare dates for stats
	$current_date = va_time();
	$cyear = $current_date[YEAR]; $cmonth = $current_date[MONTH]; $cday = $current_date[DAY]; 
	$today_ts = mktime (0, 0, 0, $cmonth, $cday, $cyear);
	$tomorrow_ts = mktime (0, 0, 0, $cmonth, $cday + 1, $cyear);
	$yesterday_ts = mktime (0, 0, 0, $cmonth, $cday - 1, $cyear);
	$week_ts = mktime (0, 0, 0, $cmonth, $cday - 6, $cyear);
	$month_ts = mktime (0, 0, 0, $cmonth, 1, $cyear);
	$last_month_ts = mktime (0, 0, 0, $cmonth - 1, 1, $cyear);
	$last_month_days = date("t", $last_month_ts);
	$last_month_end = mktime (0, 0, 0, $cmonth - 1, $last_month_days, $cyear);
	$today_date = va_date($date_edit_format, $today_ts);

	$stats = array(
		array("title" => TODAY_MSG, "date_start" => $today_ts, "date_end" => $today_ts),
		array("title" => YESTERDAY_MSG, "date_start" => $yesterday_ts, "date_end" => $yesterday_ts),
		array("title" => LAST_SEVEN_DAYS_MSG, "date_start" => $week_ts, "date_end" => $today_ts),
		array("title" => THIS_MONTH_MSG, "date_start" => $month_ts, "date_end" => $today_ts),
		array("title" => LAST_MONTH_MSG, "date_start" => $last_month_ts, "date_end" => $last_month_end),
	);

	// get orders stats
	for ($i = 1; $i < sizeof($order_statuses); $i++) {
		$status_id = $order_statuses[$i][0];
		$status_name = $order_statuses[$i][1];

		$t->set_var("status_id",   $status_id);
		$t->set_var("status_name", $status_name);

		$t->set_var("stats_periods", "");
		foreach ($stats as $key => $stat_info) {
			$start_date = $stat_info["date_start"];
			$end_date = va_time($stat_info["date_end"]);
			$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "orders ";
			$sql .= " WHERE order_status=" . $db->tosql($status_id, INTEGER);
			$sql .= " AND order_placed_date>=" . $db->tosql($start_date, DATE);
			$sql .= " AND order_placed_date<" . $db->tosql($day_after_end, DATE);
			$period_orders = get_db_value($sql);
			if (isset($stats[$key]["total"])) {
				$stats[$key]["total"] += $period_orders;
			} else {
				$stats[$key]["total"] = $period_orders;
			}
			if($period_orders > 0) {
				$period_orders = "<a href=\"admin_orders_recover.php?operation=search&s_os=".$status_id."&s_sd=".va_date($date_edit_format, $start_date)."&s_ed=".va_date($date_edit_format, $end_date)."\"><b>" . $period_orders."</b></a>";
			}
			$t->set_var("period_orders", $period_orders);
			$t->parse("stats_periods", true);
		}

		$t->parse("statuses_stats", true);
	}

	foreach ($stats as $key => $stat_info) {
		$t->set_var("start_date", va_date($date_edit_format, $stat_info["date_start"]));
		$t->set_var("end_date", va_date($date_edit_format, $stat_info["date_end"]));
		$t->set_var("stat_title", $stat_info["title"]);
		$t->set_var("period_total", $stat_info["total"]);
		$t->parse("stats_titles", true);
		$t->parse("stats_totals", true);
	}
	
	$r = new VA_Record($table_prefix . "orders");
	$r->add_hidden("operation", TEXT);
	$r->add_select("s_ot", TEXT, $order_types, ORDER_TYPE_MSG);
	$r->add_select("s_tp", TEXT, $periods);	
	$r->add_textbox("s_sd", DATE, FROM_DATE_MSG);
	$r->change_property("s_sd", VALUE_MASK, $date_edit_format);
	$r->change_property("s_sd", TRIM, true);
	$r->add_textbox("s_ed", DATE, END_DATE_MSG);
	$r->change_property("s_ed", VALUE_MASK, $date_edit_format);
	$r->change_property("s_ed", TRIM, true);
	$r->add_select("s_os", INTEGER, $order_statuses);
	
	$r->add_select("s_rs", TEXT, $yes_no_all);
	$r->change_property("s_rs", DEFAULT_VALUE, 0);
	$r->add_textbox("s_ssd", DATE, SEND_DATE_FROM_MSG);
	$r->change_property("s_ssd", VALUE_MASK, $date_edit_format);
	$r->change_property("s_ssd", TRIM, true);
	$r->add_textbox("s_sed", DATE, SEND_DATE_TO_MSG);
	$r->change_property("s_sed", VALUE_MASK, $date_edit_format);
	$r->change_property("s_sed", TRIM, true);
	
	$r->get_form_parameters();
	$r->set_value("operation", "search");
	$r->validate();
	if (!$operation) {
		// set default values
		$r->set_value("s_ot", 0);
		$r->set_value("s_rs", 0 );
		$lookback_days = get_setting_value($recover_settings, "lookback_days", 0);
		if ($lookback_days) {					
			$s_sd = va_date($date_edit_format, time() - 24*60*60*$lookback_days);
			$r->set_value("s_sd", $s_sd);
		}
	}
	$r->set_form_parameters();

	
	// check passed parameters
	$pass_parameters = array();
	foreach ($r->parameters as $param_name => $param_info) {
		$value_type = $param_info[VALUE_TYPE];
		$param_value = $param_info[CONTROL_VALUE];
		if ($value_type == DATETIME || $value_type == DATE || $value_type == TIMESTAMP || $value_type == TIME) {
			if (is_array($param_value)) {
				$pass_parameters[$param_name] = va_date($param_info[VALUE_MASK], $param_info[CONTROL_VALUE]);
			}
		} else if (strlen($param_value)) {
			$pass_parameters[$param_name] = $param_value;
		}
	}

	$where = "";
	$use_users_table = false;
	
	$operation = get_param("operation");
	$ids = get_param("ids");
	$order_id = get_param("order_id");
	if ($order_id) {
		$ids = $order_id;
	}
	

	// start building where
	$where = " ((o.email IS NOT NULL AND o.email<>'') OR (o.delivery_email IS NOT NULL AND o.delivery_email<>'')) ";

		if (!$r->is_empty("s_ot")) {
			if (strlen($where)) { $where .= " AND "; }
			if ($r->get_value("s_ot")) {
				$where .= " os.paid_status=0 AND o.is_placed=1 ";				
			} else {
				$where .= " os.paid_status=0 AND o.is_placed=0 ";
			}
		}
		
		if (!$r->is_empty("s_sd")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " o.order_placed_date>=" . $db->tosql($r->get_value("s_sd"), DATE);
		}
		
		if (!$r->is_empty("s_ed")) {
			if (strlen($where)) { $where .= " AND "; }
			$end_date = $r->get_value("s_ed");
			$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
			$where .= " o.order_placed_date<" . $db->tosql($day_after_end, DATE);
		}
		
		if (!$r->is_empty("s_os")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " o.order_status=" . $db->tosql($r->get_value("s_os"), INTEGER);
		} else {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " (os.is_list=1 OR os.is_list IS NULL) ";
		}
		
		if (!$r->is_empty("s_rs")) {
			if (strlen($where)) { $where .= " AND "; }
			$email_live_days = get_setting_value($recover_settings, "email_live_days", 0);
			if ($email_live_days) {
				if ($r->get_value("s_rs")) {
					$where .= " o.is_reminder_send=1 " ;
					$where .= " AND o.reminder_send_date>=" . $db->tosql(time() - 60*60*24*$email_live_days, DATETIME) ;
				} else {
					$where .= " (o.is_reminder_send=0 " ;
					$where .= " OR o.reminder_send_date<" . $db->tosql(time() - 60*60*24*$email_live_days, DATETIME) . ")";				
				}
			} else {			
				$where .= " o.is_reminder_send=" . $db->tosql($r->get_value("s_rs"), INTEGER);
			}
		}
		
		if (!$r->is_empty("s_ssd")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " o.reminder_send_date>=" . $db->tosql($r->get_value("s_ssd"), DATE);
		}

		if (!$r->is_empty("s_sed")) {
			if (strlen($where)) { $where .= " AND "; }
			$end_date = $r->get_value("s_sed");
			$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
			$where .= " o.reminder_send_date<" . $db->tosql($day_after_end, DATE);
		}
		
		$ignore_active_customers = get_setting_value($recover_settings, "ignore_active_customers", 0);
		if ($ignore_active_customers) {
			if (strlen($where)) { $where .= " AND "; }				
			$where .= " ( u.last_visit_date<=" . $db->tosql(time() - $online_time * 60, DATETIME) . " ";
			$where .= " OR u.last_visit_date IS NULL ) ";
			$use_users_table = true;
		}
	// end building where				
		
	
	$where_sql = "";
	if ($where) {
		$where_sql = " WHERE " . $where;
	}

	// build orders SQL query
	$orders_sql = " SELECT o.order_id, o.order_placed_date, os.status_name, o.goods_total, o.order_total, o.remote_address, ";
	$orders_sql .= " o.name, o.first_name, o.last_name, o.country_id, o.delivery_country_id, o.state_id, o.delivery_state_id, ";
	$orders_sql .= " o.currency_code, o.currency_rate, o.reminder_send_date, o.is_reminder_send, o.email, o.delivery_email, ";
	$orders_sql .= " c.symbol_right, c.symbol_left, c.decimals_number, c.decimal_point, c.thousands_separator ";
	$orders_sql .= " FROM ((" ;
	if ($use_users_table) {
		$orders_sql .= " ( ";
	}
	$orders_sql .= $table_prefix . "orders o ";
	$orders_sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
	$orders_sql .= " LEFT JOIN " . $table_prefix . "currencies c ON o.currency_code=c.currency_code) ";
	if ($use_users_table) {
		$orders_sql .= " LEFT JOIN " . $table_prefix . "users u ON o.user_id=u.user_id) ";
	}


	$recover_errors = ""; $recover_success = "";
	$recover_user_message = get_setting_value($recover_settings, "user_message");
	if ($operation == "send" && !$recover_user_message) {
		$recover_errors .= PLEASE_SPECIFY_USER_MESSAGE_TEXT_MSG;
	}

	// start sending recover emails
	$emails_sent = 0; $emails_errors = 0;
	if ($operation == "send" && !$recover_errors) {
		$sql = $orders_sql;
		if ($ids) {
			$sql .= " WHERE o.order_id IN (" . $db->tosql($ids, INTEGERS_LIST, false) . ") ";	
		} else if ($where) {
			$sql .= " WHERE " . $where;
		}
		$dbs->query($sql);
		while ($dbs->next_record()) {

			$order_id    = $dbs->f("order_id");
			$order_total = $dbs->f("order_total");
			// get order currency
			$order_currency = array();
			$order_currency_code = $dbs->f("currency_code");
			$order_currency_rate= $dbs->f("currency_rate");
			$order_currency["code"] = $dbs->f("currency_code");
			$order_currency["rate"] = $dbs->f("currency_rate");
			$order_currency["left"] = $dbs->f("symbol_left");
			$order_currency["right"] = $dbs->f("symbol_right");
			$order_currency["decimals"] = $dbs->f("decimals_number");
			$order_currency["point"] = $dbs->f("decimal_point");
			$order_currency["separator"] = $dbs->f("thousands_separator");
	  
			if ($orders_currency != 1) {
				$order_currency["left"] = $currency["left"];
				$order_currency["right"] = $currency["right"];
				$order_currency["decimals"] = $currency["decimals"];
				$order_currency["point"] = $currency["point"];
				$order_currency["separator"] = $currency["separator"];
				if (strtolower($currency["code"]) != strtolower($order_currency_code)) {
					$order_currency["rate"] = $currency["rate"];
				}
			}
			$user_name = $dbs->f("name");
			if(!strlen($user_name)) {
				$user_name = $dbs->f("first_name") . " " . $dbs->f("last_name");
			}
			$order_placed_date = $dbs->f("order_placed_date", DATETIME);
			$order_placed_date = va_date($datetime_show_format, $order_placed_date);
			
			$reminder_send_date = $dbs->f("reminder_send_date", DATETIME);
			$reminder_send_date = va_date($datetime_show_format, $reminder_send_date);
			$is_reminder_send = $dbs->f("is_reminder_send", INTEGER);
			
			if (get_setting_value($order_info, "show_delivery_country_id", 0) == 1) {
				$country_id = $dbs->f("delivery_country_id");
				$state_id = $dbs->f("delivery_state_id");
			} elseif (get_setting_value($order_info, "show_country_id", 0) == 1) {
				$country_id = $dbs->f("country_id");
				$state_id = $dbs->f("state_id");
			} else {
				$country_id = $settings["country_id"];
				$state_id = get_setting_value($settings, "state_id", "");
			}
			$status_name = get_translation($dbs->f("status_name"));
			$remote_address  = $dbs->f("remote_address");
			$email = $dbs->f("email");
			$delivery_email = $dbs->f("delivery_email");

			$t->set_var("order_id", $order_id);
			$t->set_var("user_name", htmlspecialchars($user_name));
			$t->set_var("order_placed_date", $order_placed_date);
			$t->set_var("order_status", $status_name);
			$t->set_var("order_total", currency_format($order_total, $order_currency));
			
			$ship_to = "";
			if ($country_id) {
				$sql = " SELECT country_code FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($country_id, INTEGER);
				$ship_to = get_db_value($sql);
			}
			if ($state_id) {
				$sql = " SELECT state_code FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($state_id, INTEGER);
				$state_code = get_db_value($sql);
				if ($ship_to) {
					$ship_to .= "," . $state_code;
				} else {
					$ship_to  = $state_code;
				}
			}			
			$t->set_var("ship_to", $ship_to);

			$message_type = get_setting_value($recover_settings, "user_message_type", 0);
			$user_subject = get_translation(get_setting_value($recover_settings, "user_subject", ""));
			$user_message = get_translation(get_setting_value($recover_settings, "user_message"));
			$user_message = get_currency_message($user_message);
			$user_mail_pdf_invoice = get_setting_value($recover_settings, "user_mail_pdf_invoice", 0);
			
			// get download links
			$links = get_order_links($order_id);
			$t->set_var("links",      $links["html"]);
			$t->set_var("links_html", $links["html"]);
			$t->set_var("links_txt",  $links["text"]);
	
			// get serial numbers
			$order_serials = get_serial_numbers($order_id);
			$t->set_var("serials", $order_serials["html"]);
			$t->set_var("serial_numbers", $order_serials["html"]);
			
			// get gift vouchers
			$order_vouchers = get_gift_vouchers($order_id);
			$t->set_var("vouchers", $order_vouchers["html"]);
			$t->set_var("gift_vouchers", $order_vouchers["html"]);
	
			$pdf_invoice = "";
			if ($user_mail_pdf_invoice) {
				include_once($root_folder_path . "/includes/invoice_functions.php");
				$pdf_invoice = pdf_invoice($order_id);
			}		
			$attachments = array();
			if ($user_mail_pdf_invoice) {
				$attachments[] = array("Invoice_" . $order_id . ".pdf", $pdf_invoice, "buffer");
			}
			
			if (strpos($user_message, "{basket}") !== false) {
				if ($message_type) {
					$t->set_file("basket_html", "email_basket.html");
					$items_text = show_order_items($order_id, true, "");
					$t->parse("basket_html", false);
				} else {
					$t->set_file("basket_text", "email_basket.txt");
					$items_text = show_order_items($order_id, true, "");
					$t->parse("basket_text", false);
				}
			}		

			$t->set_block("user_subject", $user_subject);
			$t->set_block("user_message", $user_message);
			
			$email_headers = array();
			$email_headers["from"]        = get_setting_value($recover_settings, "user_mail_from", $settings["admin_email"]);
			$email_headers["cc"]          = get_setting_value($recover_settings, "user_mail_cc");
			$email_headers["bcc"]         = get_setting_value($recover_settings, "user_mail_bcc");
			$email_headers["reply_to"]    = get_setting_value($recover_settings, "user_mail_reply_to");
			$email_headers["return_path"] = get_setting_value($recover_settings, "user_mail_return_path");
			$email_headers["mail_type"]   = $message_type;

			if (!$email_headers["mail_type"]) {
				$t->set_var("basket", $t->get_var("basket_text"));
				$t->set_var("links",  $links["text"]);
				$t->set_var("serials", $order_serials["text"]);
				$t->set_var("serial_numbers", $order_serials["text"]);
				$t->set_var("vouchers", $order_vouchers["text"]);
				$t->set_var("gift_vouchers", $order_vouchers["text"]);
			} else {
				$t->set_var("basket", $t->get_var("basket_html"));
			}

			$t->parse("user_subject", false);
			$t->parse("user_message", false);
			$user_email = strlen($email) ? $email : $delivery_email;
			$user_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("user_message"));

			if ($user_email) {
				$message_sent = va_mail($user_email, $t->get_var("user_subject"), $user_message, $email_headers, $attachments);
			} else {
				$message_sent = false;
			}
			if ($message_sent) {
				$emails_sent++; 
				$sql  = " UPDATE " . $table_prefix . "orders ";
				$sql .= " SET reminder_send_date=" . $db->tosql(va_time(), DATETIME) . ", ";
				$sql .= " is_reminder_send=1 ";
				$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
				$db->query($sql);
			} else {
				$emails_errors++;
			}
		}

		// prepare messages
		$recover_success = EMAILS_SENT_MSG . ": " . $emails_sent;
		if ($emails_errors) {
			$recover_errors = SENDING_ERRORS_MSG . ": " . $emails_errors; 
		}
	}
	// end of sending recover emails

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_orders_recover.php", "sort", "", $pass_parameters);
	$s->set_parameters(false, true, true, false);
	$s->set_default_sorting(1, "desc");
	$s->set_sorter(ORDER_NUMBER_COLUMN, "sorter_id", "1", "o.order_id");
	$s->set_sorter(ORDER_ADDED_COLUMN, "sorter_date", "2", "o.order_placed_date");
	$s->set_sorter(STATUS_MSG, "sorter_status", "3", "o.order_status");
	$s->set_sorter(ADMIN_ORDER_TOTAL_MSG, "sorter_total", "4", "o.order_total");
	if (get_setting_value($order_info, "show_delivery_country_id", 0) == 1) {
		$s->set_sorter(EMAIL_TO_MSG, "sorter_ship_to", "5", "o.delivery_country_id");
	} else {
		$s->set_sorter(EMAIL_TO_MSG, "sorter_ship_to", "5", "o.country_id");
	}
	$s->set_sorter(REMINDER_SEND_MSG, "sorter_reminder_send_date", "6", "o.reminder_send_date");
	
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_orders_recover.php");
	
	$sql  = " SELECT COUNT(*) FROM ( ";
	if ($use_users_table) {
		$sql .= " ( ";
	}
	$sql .= $table_prefix . "orders o ";
	$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
	if ($use_users_table) {
		$sql .= " LEFT JOIN " . $table_prefix . "users u ON o.user_id=u.user_id) ";
	}
	$sql .= $where_sql;
	$total_records = get_db_value($sql);
	
	$records_per_page = set_recs_param("admin_orders_recover.php", $pass_parameters);
	$pages_number = 5;
	
	$orders = array();
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false, $pass_parameters);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	
	$sql  = " SELECT o.order_id, o.order_placed_date, os.status_name, o.goods_total, o.order_total, o.remote_address, ";
	$sql .= " o.name, o.first_name, o.last_name, o.country_id, o.delivery_country_id, o.state_id, o.delivery_state_id, ";
	$sql .= " o.currency_code, o.currency_rate, o.reminder_send_date, o.is_reminder_send, o.email, o.delivery_email, ";
	$sql .= " c.symbol_right, c.symbol_left, c.decimals_number, c.decimal_point, c.thousands_separator ";
	$sql .= " FROM ((" ;
	if ($use_users_table) {
		$sql .= " ( ";
	}
	$sql .= $table_prefix . "orders o ";
	$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "currencies c ON o.currency_code=c.currency_code) ";
	if ($use_users_table) {
		$sql .= " LEFT JOIN " . $table_prefix . "users u ON o.user_id=u.user_id) ";
	}
	$sql .= $where_sql;
	$sql .= $s->order_by;	
	$db->query($sql);
	if ($db->next_record())
	{
		$admin_order = new VA_URL($order_details_site_url . "admin_order.php", false);
		$admin_order->add_parameter("s_ot", REQUEST, "s_ot");
		$admin_order->add_parameter("s_tp", REQUEST, "s_tp");
		$admin_order->add_parameter("s_sd", REQUEST, "s_sd");
		$admin_order->add_parameter("s_ed", REQUEST, "s_ed");
		$admin_order->add_parameter("s_os", REQUEST, "s_os");
		$admin_order->add_parameter("s_rs", REQUEST, "s_rs");
		$admin_order->add_parameter("s_ssd", REQUEST, "s_ssd");
		$admin_order->add_parameter("s_sed", REQUEST, "s_sed");
		$admin_order->add_parameter("s_sti", REQUEST, "s_sti");
		$admin_order->add_parameter("page", REQUEST, "page");
		$admin_order->add_parameter("sort_ord", REQUEST, "sort_ord");
		$admin_order->add_parameter("sort_dir", REQUEST, "sort_dir");
		$admin_order->add_parameter("order_id", DB, "order_id");

		$order_index = 0;
		do
		{
			//$order_index++;
			$order_id    = $db->f("order_id");
			$order_total = $db->f("order_total");
			// get order currency
			$order_currency = array();
			$order_currency_code = $db->f("currency_code");
			$order_currency_rate= $db->f("currency_rate");
			$order_currency["code"] = $db->f("currency_code");
			$order_currency["rate"] = $db->f("currency_rate");
			$order_currency["left"] = $db->f("symbol_left");
			$order_currency["right"] = $db->f("symbol_right");
			$order_currency["decimals"] = $db->f("decimals_number");
			$order_currency["point"] = $db->f("decimal_point");
			$order_currency["separator"] = $db->f("thousands_separator");
	  
			if ($orders_currency != 1) {
				$order_currency["left"] = $currency["left"];
				$order_currency["right"] = $currency["right"];
				$order_currency["decimals"] = $currency["decimals"];
				$order_currency["point"] = $currency["point"];
				$order_currency["separator"] = $currency["separator"];
				if (strtolower($currency["code"]) != strtolower($order_currency_code)) {
					$order_currency["rate"] = $currency["rate"];
				}
			}
			$user_name = $db->f("name");
			if(!strlen($user_name)) {
				$user_name = $db->f("first_name") . " " . $db->f("last_name");
			}
			$order_placed_date = $db->f("order_placed_date", DATETIME);
			$order_placed_date = va_date($datetime_show_format, $order_placed_date);
			
			$reminder_send_date = $db->f("reminder_send_date", DATETIME);
			$reminder_send_date = va_date($datetime_show_format, $reminder_send_date);
			$is_reminder_send = $db->f("is_reminder_send", INTEGER);
			
			if (get_setting_value($order_info, "show_delivery_country_id", 0) == 1) {
				$country_id = $db->f("delivery_country_id");
				$state_id = $db->f("delivery_state_id");
			} elseif (get_setting_value($order_info, "show_country_id", 0) == 1) {
				$country_id = $db->f("country_id");
				$state_id = $db->f("state_id");
			} else {
				$country_id = $settings["country_id"];
				$state_id = get_setting_value($settings, "state_id", "");
			}
			$status_name = get_translation($db->f("status_name"));
			$admin_order_url = $admin_order->get_url();
			$remote_address  = $db->f("remote_address");
			$email = $db->f("email");
			$delivery_email = $db->f("delivery_email");
		
			$orders[] = array(
				$order_id, $order_total, $user_name, $order_placed_date, $status_name, $country_id, $state_id, 
				$admin_order_url, $remote_address, $order_currency, $reminder_send_date, $is_reminder_send,
				$email, $delivery_email
			);

		} while ($db->next_record());
	}


	if (sizeof($orders) > 0)
	{
		$order_index = 0;
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		for ($i = 0; $i < sizeof($orders); $i++) {
			$order_index++;

			list($order_id, $order_total, $user_name, $order_placed_date, $status_name, $country_id, $state_id,
				$admin_order_url, $remote_address, $order_currency, $reminder_send_date, $is_reminder_send,
				$email, $delivery_email) = $orders[$i];
			
			$t->set_var("order_index", $order_index);
			$t->set_var("order_id", $order_id);
			$t->set_var("user_name", htmlspecialchars($user_name));
			$t->set_var("order_placed_date", $order_placed_date);
			if ($is_reminder_send) {
				$t->set_var("reminder_send_date", $reminder_send_date);
			} else {
				$t->set_var("reminder_send_date", NEVER_MSG);
			}
			
			$t->set_var("order_status", $status_name);

			$t->set_var("order_total", currency_format($order_total, $order_currency));
			
			$ship_to = "";
			if ($country_id) {
				$sql = " SELECT country_code FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($country_id, INTEGER);
				$ship_to = get_db_value($sql);
			}
			if ($state_id) {
				$sql = " SELECT state_code FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($state_id, INTEGER);
				$state_code = get_db_value($sql);
				if ($ship_to) {
					$ship_to .= "," . $state_code;
				} else {
					$ship_to  = $state_code;
				}
			}			
			$t->set_var("ship_to", $ship_to);
			$t->set_var("admin_order_url", $admin_order_url);
					
			$sql  = "SELECT ip_address FROM " . $table_prefix . "black_ips WHERE ip_address=" . $db->tosql($remote_address, TEXT);
			$db->query($sql);
			if ($db->next_record()) {
				$row_style = "rowWarn";
			} else {
				$row_style = ($order_index % 2 == 0) ? "row1" : "row2";
			}
			$t->set_var("row_style", $row_style);

			$t->set_var("order_items", "");
			$total_quantity = 0;
			$total_price = 0;
			$sql  = " SELECT item_name, quantity, price ";
			$sql .= " FROM " . $table_prefix . "orders_items ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$item_name = get_translation($db->f("item_name"));
				if (strlen($item_name) > 20) {
					$item_name = substr($item_name, 0, 20) . "...";
				}
				$quantity = $db->f("quantity");
				$price = $db->f("price");

				$total_quantity += $quantity;
				$total_price += ($price * $quantity);

				$t->set_var("item_name", $item_name);
				$t->set_var("quantity",  $quantity);
				$t->set_var("price", currency_format($price, $order_currency));
				$t->parse("order_items", true);
			}
			$t->set_var("total_quantity", $total_quantity);
			$t->set_var("total_price", currency_format($total_price, $order_currency));

			$t->parse("records", true);
		} 
		$t->set_var("orders_number", $order_index);
	}
	else
	{
		$t->set_var("sorters", "");
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}
	
	if ($total_records > 0) {
		$admin_order_reminder_url = new VA_URL("admin_order_recover.php", true);
		$admin_order_reminder_url->add_parameter("operation", CONSTANT, "send");
		$t->set_var("admin_order_reminder_filtered_href", $admin_order_reminder_url->get_url());

		// filtered link
		$send_filtered = new VA_URL("admin_orders_recover.php", false);
		$send_filtered->add_parameter("operation", CONSTANT, "send");
		$send_filtered->add_parameter("s_ot", REQUEST, "s_ot");
		$send_filtered->add_parameter("s_tp", REQUEST, "s_tp");
		$send_filtered->add_parameter("s_sd", REQUEST, "s_sd");
		$send_filtered->add_parameter("s_ed", REQUEST, "s_ed");
		$send_filtered->add_parameter("s_os", REQUEST, "s_os");
		$send_filtered->add_parameter("s_rs", REQUEST, "s_rs");
		$send_filtered->add_parameter("s_ssd", REQUEST, "s_ssd");
		$send_filtered->add_parameter("s_sed", REQUEST, "s_sed");
		$send_filtered->add_parameter("s_sti", REQUEST, "s_sti");
		$send_filtered->add_parameter("sort_ord", REQUEST, "sort_ord");
		$send_filtered->add_parameter("sort_dir", REQUEST, "sort_dir");

		$t->set_var("total_filtered", $total_records);
		$t->set_var("send_filtered_url", $send_filtered->get_url());
		$t->parse("send_reminder_filtered", false);
	}
	
	if (strlen($recover_success)) {
		$t->set_var("success_message", $recover_success);
		$t->parse("recover_success", false);
	}
	if (strlen($recover_errors)) {
		$t->set_var("errors_list", $recover_errors);
		$t->parse("recover_errors", false);
	}
  
	
	include_once("./admin_header.php");
	include_once("./admin_footer.php");
	
	$t->pparse("main");
?>