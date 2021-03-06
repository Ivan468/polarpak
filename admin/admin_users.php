<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_users.php                                          ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("site_users");

	$permissions = get_permissions();
	$add_users_allowed = get_setting_value($permissions, "add_users", 0);
	$update_users_allowed = get_setting_value($permissions, "update_users", 0);
	$remove_users_allowed = get_setting_value($permissions, "remove_users", 0);

	$points_decimals = get_setting_value($settings, "points_decimals", 0);
	$online_time = get_setting_value($settings, "online_time", 5);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_users.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_user_href", "admin_user.php");
	$t->set_var("admin_users_href", "admin_users.php");
	$t->set_var("admin_user_login_href", "admin_user_login.php");
	$t->set_var("admin_import_href", "admin_import.php");
	$t->set_var("admin_export_href", "admin_export.php");
	$t->set_var("admin_user_types_href", "admin_user_types.php");
	$t->set_var("confirm_delete_user", str_replace("{record_name}", va_message("ADMIN_USER_MSG"), va_message("CONFIRM_DELETE_MSG")));

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_users.php", "", array("operation", "users_ids", "status_id"));
	$s->set_parameters(false, true, true, false);
	$s->set_default_sorting(1, "desc");
	$s->set_sorter(va_message("ID_MSG"), "sorter_user_id", "1", "user_id");
	$s->set_sorter(va_message("EMAIL_MSG"), "sorter_email", "3", "email");
	$s->set_sorter(va_message("TYPE_MSG"), "sorter_user_type", "4", "ut.type_name");
	$s->set_sorter(va_message("IS_APPROVED_MSG"), "sorter_is_approved", "5", "is_approved");
	$s->set_sorter(va_message("POINTS_MSG"), "sorter_total_points", "6", "total_points");
	$s->set_sorter(va_message("CREDIT_MSG"), "sorter_credit_balance", "7", "credit_balance");
	$s->set_sorter(va_message("REGISTRATION_DATE_MSG"), "sorter_registration_date", "8", "registration_date");
	$s->set_sorter(va_message("LAST_ACTIVITY_MSG"), "sorter_last_visit_date", "9", "last_visit_date");
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_users.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// prepare dates for stats
	$current_date = va_time();
	$cyear = $current_date[YEAR]; $cmonth = $current_date[MONTH]; $cday = $current_date[DAY];
	$online_ts = mktime ($current_date[HOUR], $current_date[MINUTE] - $online_time, $current_date[SECOND], $cmonth, $cday, $cyear);
	$today_ts = mktime (0, 0, 0, $cmonth, $cday, $cyear);
	$tomorrow_ts = mktime (0, 0, 0, $cmonth, $cday + 1, $cyear);
	$yesterday_ts = mktime (0, 0, 0, $cmonth, $cday - 1, $cyear);
	$week_ts = mktime (0, 0, 0, $cmonth, $cday - 6, $cyear);
	$month_ts = mktime (0, 0, 0, $cmonth, 1, $cyear);
	$last_month_ts = mktime (0, 0, 0, $cmonth - 1, 1, $cyear);
	$last_month_days = date("t", $last_month_ts);
	$last_month_end = mktime (0, 0, 0, $cmonth - 1, $last_month_days, $cyear);

	$t->set_var("date_edit_format", join("", $date_edit_format));

	$operation = get_param("operation");
	$users_ids = get_param("users_ids");
	$status_id = get_param("status_id");
	$rnd = get_param("rnd");

	srand((double) microtime() * 1000000);
	$new_rnd = rand();

	$users_messages = ""; $users_errors = "";
	$birth_messages = ""; $birth_errors = ""; 
	if ($operation == "birth_greetings") {
		$rnd_values = get_session("session_birth_rnd");
		if (!is_array($rnd_values)) { $rnd_values = array(); }
		if (!in_array($rnd, $rnd_values)) {
			$rnd_values[] = $rnd;
			set_session("session_birth_rnd", $rnd_values);

			include_once("./admin_users_birth_greetings.php");
			if ($birth_messages) {
				$users_messages .= $birth_messages;
			}
			if ($birth_errors) {
				$users_errors .= $birth_errors;
			}
		}
	}
	$reminders_errors = ""; $reminders_messages = "";
	if ($operation == "send_reminders") {
		include_once("./cron_users_reminders.php");
		if ($reminders_messages) {
			$users_messages .= $reminders_messages;
		}
		if ($reminders_errors) {
			$users_errors .= $reminders_errors;
		}
	}
	
	$user_types = get_db_values("SELECT type_id, type_name FROM " . $table_prefix . "user_types", array(array("", "")));

	$stats = array(
		array("title" => va_message("TODAY_MSG"), "date_start" => $today_ts, "date_end" => $today_ts),
		array("title" => va_message("YESTERDAY_MSG"), "date_start" => $yesterday_ts, "date_end" => $yesterday_ts),
		array("title" => va_message("LAST_SEVEN_DAYS_MSG"), "date_start" => $week_ts, "date_end" => $today_ts),
		array("title" => va_message("THIS_MONTH_MSG"), "date_start" => $month_ts, "date_end" => $today_ts),
		array("title" => va_message("LAST_MONTH_MSG"), "date_start" => $last_month_ts, "date_end" => $last_month_end),
	);

	$users_total_online = 0; 
	// get users stats
	for($i = 1; $i < sizeof($user_types); $i++) {
		// set general constants
		$type_id = $user_types[$i][0];
		$type_name = $user_types[$i][1];

		$t->set_var("type_id",   $type_id);
		$t->set_var("type_name", $type_name);

		// get online stats
		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "users ";
		$sql .= " WHERE user_type_id=" . $db->tosql($type_id, INTEGER);
		$sql .= " AND last_visit_date>=" . $db->tosql($online_ts, DATETIME);
		$users_online = get_db_value($sql);
		$users_total_online += $users_online;
		if ($users_online > 0) {
			$users_online = "<a href=\"admin_users.php?s_ut=" . $type_id . "&s_on=1\"><b>" . $users_online . "</b></a>";
		}
		$t->set_var("users_online", $users_online);
		$t->parse("users_online_stats", true);

		// get registration stats
		$t->set_var("stats_periods", "");
		foreach($stats as $key => $stat_info) {
			$start_date = $stat_info["date_start"];
			$end_date = va_time($stat_info["date_end"]);
			$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "users ";
			$sql .= " WHERE user_type_id=" . $db->tosql($type_id, INTEGER);
			$sql .= " AND registration_date>=" . $db->tosql($start_date, DATE);
			$sql .= " AND registration_date<" . $db->tosql($day_after_end, DATE);
			$period_users = get_db_value($sql);
			if (isset($stats[$key]["total"])) {
				$stats[$key]["total"] += $period_users;
			} else {
				$stats[$key]["total"] = $period_users;
			}
			if($period_users > 0) {
				$period_users = "<a href=\"admin_users.php?s_ut=".$type_id."&s_sd=".va_date($date_edit_format, $start_date)."&s_ed=".va_date($date_edit_format, $end_date)."\"><b>" . $period_users."</b></a>";
			}
			$t->set_var("period_users", $period_users);
			$t->parse("stats_periods", true);
		}

		$t->parse("types_stats", true);
	}
	// set total online users
	$t->set_var("users_total_online", $users_total_online);

	foreach($stats as $key => $stat_info) {
		$t->set_var("start_date", va_date($date_edit_format, $stat_info["date_start"]));
		$t->set_var("end_date", va_date($date_edit_format, $stat_info["date_end"]));
		$t->set_var("stat_title", $stat_info["title"]);
		$t->set_var("period_total", $stat_info["total"]);
		$t->parse("stats_titles", true);
		$t->parse("stats_totals", true);
	}

	$user_type = get_db_values("SELECT type_id, type_name FROM " . $table_prefix . "user_types", array(array("", "")));
	$approved_options = array(array("", va_message("ALL_MSG")), array("1", va_message("IS_APPROVED_MSG")), array("0", va_message("NOT_APPROVED_MSG")));
	$online_options = array(array("", va_message("ALL_MSG")), array("1", va_message("ONLINE_MSG")), array("0", va_message("OFFLINE_MSG")));

	$r = new VA_Record($table_prefix . "users");
	$r->add_textbox("s_ne", TEXT);
	$r->change_property("s_ne", TRIM, true);
	$r->add_textbox("s_ad", TEXT);
	$r->change_property("s_ad", TRIM, true);
	$r->add_textbox("s_sd", DATE, va_message("REGISTRATION_DATE_MSG").": ".va_message("FROM_DATE_MSG"));
	$r->change_property("s_sd", VALUE_MASK, $date_edit_format);
	$r->change_property("s_sd", TRIM, true);
	$r->add_textbox("s_ed", DATE, va_message("REGISTRATION_DATE_MSG").": ".va_message("END_DATE_MSG"));
	$r->change_property("s_ed", VALUE_MASK, $date_edit_format);
	$r->change_property("s_ed", TRIM, true);
	$r->add_textbox("a_sd", DATE, va_message("LAST_ACTIVITY_MSG").": ".va_message("FROM_DATE_MSG"));
	$r->change_property("a_sd", VALUE_MASK, $date_edit_format);
	$r->change_property("a_sd", TRIM, true);
	$r->add_textbox("a_ed", DATE, va_message("LAST_ACTIVITY_MSG").": ".va_message("END_DATE_MSG"));
	$r->change_property("a_ed", VALUE_MASK, $date_edit_format);
	$r->change_property("a_ed", TRIM, true);
	$r->add_select("s_ut", INTEGER, $user_type);
	$r->add_select("s_ap", TEXT, $approved_options);
	$r->add_select("s_on", TEXT, $online_options);
	$r->get_form_parameters();
	$r->validate();
	$approved_options = array(array("", ""), array("1", va_message("IS_APPROVED_MSG")), array("0", va_message("NOT_APPROVED_MSG")));
	$r->add_select("status_id", TEXT, $approved_options);
	$r->set_form_parameters();

	if (!$r->is_empty("s_ad") || !$r->is_empty("s_sd") || !$r->is_empty("s_ed") 
		|| !$r->is_empty("a_ad") || !$r->is_empty("a_ed") || !$r->is_empty("s_ut")
		|| !$r->is_empty("s_ap") || !$r->is_empty("s_on") 
	) {
		$t->set_var("search_advanced_class", "expand-open");
	}

	if (strlen($operation)) {
		if ($operation == "update_status" && strlen($users_ids) && strlen($status_id)){
			$ids = explode(",", $users_ids);
			for($i = 0; $i < sizeof($ids); $i++) {
				update_user_status($ids[$i], $status_id);
			}
		}
		if ($operation == "remove_users" && strlen($users_ids)){
			if ($remove_users_allowed) {
				delete_users($users_ids);	
			}
		}
	}

	$where = "";
	$from_b = "";
	$from = "";
	$product_search = false;

	if (!$r->errors) {
		if (!$r->is_empty("s_ne")) {
			$sw = explode(" ", $r->get_value("s_ne"));
			for($si = 0; $si < sizeof($sw); $si++) {
				if (strlen($where)) { $where .= " AND "; }
				$s_ne_sql = $db->tosql($sw[$si], TEXT, false);
				$where .= " (u.email LIKE '%" . $s_ne_sql . "%'";
				$where .= " OR u.delivery_email LIKE '%" . $s_ne_sql . "%'";
				$where .= " OR u.login LIKE '%" . $s_ne_sql . "%'";
				$where .= " OR u.name LIKE '%" . $s_ne_sql . "%'";
				$where .= " OR u.first_name LIKE '%" . $s_ne_sql . "%'";
				$where .= " OR u.last_name LIKE '%" . $s_ne_sql . "%'";
				$where .= " OR u.company_name LIKE '%" . $s_ne_sql . "%')";
			}
		}

		if (!$r->is_empty("s_ad")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " (u.address1 LIKE '%" . $db->tosql($r->get_value("s_ad"), TEXT, false) . "%'";
			$where .= " OR u.address2 LIKE '%" . $db->tosql($r->get_value("s_ad"), TEXT, false) . "%'";
			$where .= " OR u.address3 LIKE '%" . $db->tosql($r->get_value("s_ad"), TEXT, false) . "%'";
			$where .= " OR u.city LIKE '%" . $db->tosql($r->get_value("s_ad"), TEXT, false) . "%'";
			$where .= " OR u.province LIKE '%" . $db->tosql($r->get_value("s_ad"), TEXT, false) . "%'";
			$where .= " OR u.state_id LIKE '%" . $db->tosql($r->get_value("s_ad"), TEXT, false) . "%'";
			$where .= " OR u.zip LIKE '%" . $db->tosql($r->get_value("s_ad"), TEXT, false) . "%'";
			$where .= " OR u.country_id LIKE '%" . $db->tosql($r->get_value("s_ad"), TEXT, false) . "%'";
			$where .= " OR s.state_name LIKE '%" . $db->tosql($r->get_value("s_ad"), TEXT, false) . "%'";
			$where .= " OR c.country_name LIKE '%" . $db->tosql($r->get_value("s_ad"), TEXT, false) . "%')";
			$from_b .= "((";
			$from   = "LEFT JOIN " . $table_prefix . "countries c ON u.country_id=c.country_id) ";
			$from  .= "LEFT JOIN " . $table_prefix . "states s ON u.state_id=s.state_id)";
		}

		if (!$r->is_empty("s_sd")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " u.registration_date>=" . $db->tosql($r->get_value("s_sd"), DATE);
		}
		if (!$r->is_empty("s_ed")) {
			if (strlen($where)) { $where .= " AND "; }
			$end_date = $r->get_value("s_ed");
			$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
			$where .= " u.registration_date<" . $db->tosql($day_after_end, DATE);
		}
		if (!$r->is_empty("a_sd")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " u.last_visit_date>=" . $db->tosql($r->get_value("a_sd"), DATE);
		}
		if (!$r->is_empty("a_ed")) {
			if (strlen($where)) { $where .= " AND "; }
			$end_date = $r->get_value("a_ed");
			$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
			$where .= " u.last_visit_date<" . $db->tosql($day_after_end, DATE);
		}

		if (!$r->is_empty("s_ut")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " u.user_type_id=" . $db->tosql($r->get_value("s_ut"), INTEGER);
		}

		if (!$r->is_empty("s_ap")) {
			if (strlen($where)) { $where .= " AND "; }
			$s_ap = $r->get_value("s_ap");
			$where .= ($s_ap == 1) ? " u.is_approved=1 " : " u.is_approved=0 ";
		}

		if (!$r->is_empty("s_on")) {
			if (strlen($where)) { $where .= " AND "; }
			if ($r->get_value("s_on") == 1) {
				$where .= " u.last_visit_date>=" . $db->tosql($online_ts, DATETIME);
			} else {
				$where .= " u.last_visit_date<" . $db->tosql($online_ts, DATETIME);
			}
		}
	}

	$where_sql = ""; $where_and_sql = "";
	if (strlen($where)) {
		$where_sql = " WHERE " . $where;
		$where_and_sql = " AND " . $where;
	}

	// set up variables for navigator
	$sql  = "SELECT COUNT(*) FROM " . $from_b . $table_prefix . "users u ".$from;
	$sql .= $where_sql;
	$total_records = get_db_value($sql);

	$records_per_page = set_recs_param("admin_users.php");
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$users = array();

	$sql  = " SELECT u.user_id, u.name, u.login, u.first_name, u.last_name, u.company_name, u.email, u.delivery_email, u.is_approved, ";
	$sql .= " u.total_points, u.credit_balance, ut.type_name, ";
	$sql .= " u.registration_date, u.registration_ip, u.modified_ip, u.last_visit_date, u.last_visit_ip ";
	$sql .= " FROM (" . $from_b . $table_prefix . "users u LEFT JOIN " . $table_prefix . "user_types ut ON u.user_type_id=ut.type_id) ".$from;
	$sql .= $where_sql;
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query($sql . $s->order_by);
	while ($db->next_record()) {
		$user_id = $db->f("user_id");
		$login = $db->f("login");
		$user_name = $db->f("name");
		if (!strlen($user_name)) {
			$user_name = trim($db->f("first_name") . " " . $db->f("last_name"));
		}
		if (!strlen($user_name)) {
			$user_name = $db->f("company_name");
		}
		$email = $db->f("email");
		$delivery_email = $db->f("delivery_email");
		$user_type = get_translation($db->f("type_name"));
		$is_approved = $db->f("is_approved");
		$total_points = $db->f("total_points");
		$credit_balance = doubleval($db->f("credit_balance"));
		$registration_ip = $db->f("registration_ip");
		$modified_ip = $db->f("modified_ip");
		$last_visit_ip = $db->f("last_visit_ip");
		$registration_date = $db->f("registration_date", DATETIME);
		$last_visit_date = $db->f("last_visit_date", DATETIME);

		$users[$user_id] = array(
			"login" =>           $login, 
			"user_name" =>       $user_name, 
			"email" =>           $email, 
			"delivery_email" =>  $delivery_email, 
			"user_type" =>       $user_type, 
			"is_approved" =>     $is_approved, 
			"total_points" =>    $total_points, 
			"credit_balance" =>  $credit_balance, 
			"registration_ip" => $registration_ip, 
			"modified_ip" =>     $modified_ip, 
			"last_visit_ip" =>   $last_visit_ip,
			"registration_date" => $registration_date,
			"last_visit_date" => $last_visit_date,
		);
	}

	if (sizeof($users) > 0)
	{

		$admin_user_url = new VA_URL("admin_user_points.php", false);
		$admin_user_url->add_parameter("s_ne", REQUEST, "s_ne");
		$admin_user_url->add_parameter("s_ad", REQUEST, "s_ad");
		$admin_user_url->add_parameter("s_sd", REQUEST, "s_sd");
		$admin_user_url->add_parameter("s_ed", REQUEST, "s_ed");
		$admin_user_url->add_parameter("a_sd", REQUEST, "a_sd");
		$admin_user_url->add_parameter("a_ed", REQUEST, "a_ed");
		$admin_user_url->add_parameter("s_ut", REQUEST, "s_ut");
		$admin_user_url->add_parameter("s_ap", REQUEST, "s_ap");
		$admin_user_url->add_parameter("s_on", REQUEST, "s_on");
		$admin_user_url->add_parameter("page", REQUEST, "page");
		$admin_user_url->add_parameter("sort_ord", REQUEST, "sort_ord");
		$admin_user_url->add_parameter("sort_dir", REQUEST, "sort_dir");

		$t->set_var("no_records", "");
		$user_index = 0;
		foreach ($users as $user_id => $user_info) {

			$login =          $user_info["login"];           
			$user_name =      $user_info["user_name"];       
			$email =          $user_info["email"];           
			$delivery_email = $user_info["delivery_email"];
			$user_type =      $user_info["user_type"];       
			$is_approved =    $user_info["is_approved"];     
			$total_points =   $user_info["total_points"];    
			$credit_balance = $user_info["credit_balance"];   
			$registration_ip =$user_info["registration_ip"];  
			$modified_ip =    $user_info["modified_ip"];     
			$last_visit_ip =  $user_info["last_visit_ip"];   
			$registration_date = $user_info["registration_date"];   
			$last_visit_date   = $user_info["last_visit_date"];   

			$is_approved = ($is_approved == 1) ? va_message("YES_MSG") : va_message("NO_MSG");

			$user_index++;
			$sql  = " SELECT ip_address FROM " . $table_prefix . "black_ips ";
			$sql .= " WHERE ip_address=" . $db->tosql($registration_ip, TEXT, true, false);
			if ($modified_ip) {
				$sql .= " OR ip_address=" . $db->tosql($modified_ip, TEXT);
			}
			if ($last_visit_ip) {
				$sql .= " OR ip_address=" . $db->tosql($last_visit_ip, TEXT);
			}
			$db->query($sql);
			if ($db->next_record()) {
				$row_style = "rowWarn";
			} else {
				$row_style = ($user_index % 2 == 0) ? "row1" : "row2";
			}
			$t->set_var("row_style", $row_style);

			// calculate amount of submitted orders
			$orders_amount = 0;
			$sql  = " SELECT SUM(order_total) AS orders_amount FROM ".$table_prefix."orders "; 
			$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER); 
			$db->query($sql);
			if ($db->next_record()) {
				$orders_amount = doubleval($db->f("orders_amount"));
			}			

			$t->set_var("user_index", $user_index);
			$t->set_var("user_id", $user_id);
			$t->set_var("name", htmlspecialchars($user_name));
			$t->set_var("login", htmlspecialchars($login));
			if ($email) {
				$t->set_var("email", htmlspecialchars($email));	
				$t->sparse("email_block", false);	
			} else if ($delivery_email) {
				$t->set_var("email", htmlspecialchars($delivery_email));	
				$t->sparse("email_block", false);	
			} else {
				$t->set_var("email_block", "");	
			}
			$t->set_var("user_type", $user_type);
			$t->set_var("is_approved", $is_approved);
			$t->set_var("total_points", number_format(doubleval($total_points), $points_decimals));
			$t->set_var("credit_balance", currency_format($credit_balance));
			$t->set_var("orders_amount", currency_format($orders_amount));
			$t->set_var("registration_date", va_date($datetime_show_format, $registration_date));
			if (is_array($last_visit_date)) {
				$t->set_var("last_visit_date", va_date($datetime_show_format, $last_visit_date));
			} else {
				$t->set_var("last_visit_date", "");
			}

			$admin_user_url->remove_parameter("operation");
			$admin_user_url->remove_parameter("users_ids");
			$admin_user_url->add_parameter("user_id", CONSTANT, $user_id);
			$t->set_var("admin_user_change_type_url", $admin_user_url->get_url("admin_user_change_type.php"));
			$t->set_var("admin_user_points_url", $admin_user_url->get_url("admin_user_points.php"));
			$t->set_var("admin_user_credits_url", $admin_user_url->get_url("admin_user_credits.php"));
			$t->set_var("admin_user_edit_url", $admin_user_url->get_url("admin_user.php"));
			$t->set_var("admin_user_login_url", $admin_user_url->get_url("admin_user_login.php"));



			if ($remove_users_allowed) {
				$admin_user_url->remove_parameter("user_id");
				$admin_user_url->add_parameter("operation", CONSTANT, "remove_users");
				$admin_user_url->add_parameter("users_ids", CONSTANT, $user_id);
				$t->set_var("admin_user_delete_url", $admin_user_url->get_url("admin_users.php"));
				$t->sparse("user_delete_button", false);
			} else {
				$t->set_var("user_delete_button", "");
			}


			$t->parse("records", true);
		}
		$t->set_var("users_number", $user_index);
		$t->parse("update_status", false);
		if ($remove_users_allowed) {
			$t->parse("remove_users_button", false);
		}
		$t->parse("sorters", false);
	}
	else
	{
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->set_var("update_status", "");
		$t->set_var("remove_users_button", "");
		$t->parse("no_records", false);
	}

	if ($add_users_allowed) {
		$type_index = 0;
		$sql = " SELECT type_id,type_name FROM " . $table_prefix . "user_types ";
		$db->query($sql);
		while ($db->next_record()) {
			$type_index++;
			$delimiter = ($type_index == 1) ? "" : " | ";
			$t->set_var("delimiter", $delimiter);
			$t->set_var("type_id", $db->f("type_id"));
			$t->set_var("type_name", get_translation($db->f("type_name")));
			$t->parse("user_types");
		}
	}

	$t->set_var("s_ut_search", htmlspecialchars($r->get_value("s_ut")));
	$t->set_var("s_ap_search", htmlspecialchars($r->get_value("s_ap")));
	$t->set_var("s_on_search", htmlspecialchars($r->get_value("s_on")));
	$t->set_var("rnd", $new_rnd);

	if (strlen($users_errors)) {
		$t->set_var("errors_list", $users_errors);
		$t->parse("users_errors", false);
	}

	if (strlen($users_messages)) {
		$t->set_var("messages_list", $users_messages);
		$t->parse("users_messages", false);
	}

	if (strlen($where) && $total_records > 0) {
		$admin_export_filtered_url = new VA_URL("admin_export.php", true);
		$admin_export_filtered_url->add_parameter("table", CONSTANT, "users");
		$t->set_var("admin_export_filtered_url", $admin_export_filtered_url->get_url());
		$t->set_var("total_filtered", $total_records);
		$t->parse("export_filtered", false);
	}

	$sql  = " SELECT exported_user_id FROM " . $table_prefix . "admins ";
	$sql .= " WHERE admin_id=" . $db->tosql(get_session("session_admin_id"), INTEGER);
	$exported_user_id = intval(get_db_value($sql));

	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "users ";
	$sql .= " WHERE user_id>" . $db->tosql($exported_user_id, INTEGER);
	$total_new = get_db_value($sql);
	if ($total_new > 0) {
		$t->set_var("exported_user_id", urlencode($exported_user_id));
		$t->set_var("total_new", $total_new);
		$t->parse("export_new", false);
	}

	$sql  = " SELECT MAX(user_id) FROM " . $table_prefix . "users ";
	$max_user_id = get_db_value($sql);

	if ($max_user_id > get_session("session_last_user_id") && $max_user_id > get_session("session_max_user_id")) {
		set_session("session_max_user_id", $max_user_id);
		$sql = " UPDATE " . $table_prefix . "admins SET last_user_id=" . $db->tosql($max_user_id, INTEGER);
		$sql .= " WHERE admin_id=" . $db->tosql(get_session("session_admin_id"), INTEGER);
		$db->query($sql);
	}

	$t->pparse("main");

?>