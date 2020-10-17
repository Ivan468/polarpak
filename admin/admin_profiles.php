<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_profiles.php                                       ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
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

	check_admin_security("");

	$operation = get_param("operation");
	$pid = get_param("pid");

	if (strlen($operation) && $pid) {
		if (strtolower($operation) == "disapprove") {
			$sql  = " UPDATE " . $table_prefix . "profiles SET is_approved=0 ";
			$sql .= " WHERE profile_id=" . $db->tosql($pid, INTEGER);
			$db->query($sql);
		} elseif (strtolower($operation) == "approve") {
			$sql  = " UPDATE " . $table_prefix . "profiles SET is_approved=1 ";
			$sql .= " WHERE profile_id=" . $db->tosql($pid, INTEGER);
			$db->query($sql);
		}
	}

	$permissions = get_permissions();
	$online_time = get_setting_value($settings, "online_time", 5);
	$site_url = get_setting_value($settings, "site_url", "");

	$custom_breadcrumb = array(
		"admin_menu.php?code=dashboard" => DASHBOARD_MSG,
		"admin_profiles.php" => PROFILES_TITLE,
	);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_profiles.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_user_href", "admin_user.php");
	$t->set_var("admin_profiles_href", "admin_profiles.php");
	$t->set_var("profiles_view_href", "profiles_view.php");
	$t->set_var("admin_profile_types_href", "admin_profile_types.php");

	$profiles_approve_url = new VA_URL("admin_profiles.php", false);
	$profiles_approve_url->add_parameter("s_ne", REQUEST, "s_ne");
	$profiles_approve_url->add_parameter("s_c", REQUEST, "s_c");
	$profiles_approve_url->add_parameter("s_pt", REQUEST, "s_pt");
	$profiles_approve_url->add_parameter("s_lt", REQUEST, "s_lt");
	$profiles_approve_url->add_parameter("s_sd", REQUEST, "s_sd");
	$profiles_approve_url->add_parameter("s_ed", REQUEST, "s_ed");
	$profiles_approve_url->add_parameter("s_bsd", REQUEST, "s_bsd");
	$profiles_approve_url->add_parameter("s_bed", REQUEST, "s_bed");
	$profiles_approve_url->add_parameter("s_ap", REQUEST, "s_ap");
	$profiles_approve_url->add_parameter("s_on", REQUEST, "s_on");
	$profiles_approve_url->add_parameter("page", REQUEST, "page");


	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_profiles.php");
	$s->set_parameters(false, true, true, false);
	$s->set_default_sorting(1, "desc");
	$s->set_sorter(ID_MSG, "sorter_id", "1", "p.profile_id");
	$s->set_sorter(NAME_MSG, "sorter_name", "2", "p.profile_name");
	$s->set_sorter(COUNTRY_FIELD, "sorter_country", "3", "c.country_name");
	$s->set_sorter(PROFILE_TYPE_FIELD, "sorter_profile_type", "4", "p.profile_type_name");
	$s->set_sorter(LOOKING_TYPE_FIELD, "sorter_looking_type", "5", "p.looking_type_name");
	$s->set_sorter(BIRTH_DATE_MSG, "sorter_birth_date", "6", "p.birth_date");
	$s->set_sorter(DATE_ADDED_MSG, "sorter_date_added", "7", "p.date_added");
	$s->set_sorter(IS_APPROVED_MSG, "sorter_approved", "8", "p.is_approved");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_profiles.php");

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
	$profiles_ids = get_param("profiles_ids");
	$status_id = get_param("status_id");
	$rnd = get_param("rnd");

	srand((double) microtime() * 1000000);
	$new_rnd = rand();

	$countries = get_db_values("SELECT country_id, country_name FROM " . $table_prefix . "countries ORDER BY country_order, country_name ", array(array("", "")));
	$profile_types = get_db_values("SELECT profile_type_id, profile_type_name FROM " . $table_prefix . "profiles_types ", array(array("", "")));

	$stats = array(
		array("title" => TODAY_MSG, "date_start" => $today_ts, "date_end" => $today_ts),
		array("title" => YESTERDAY_MSG, "date_start" => $yesterday_ts, "date_end" => $yesterday_ts),
		array("title" => LAST_SEVEN_DAYS_MSG, "date_start" => $week_ts, "date_end" => $today_ts),
		array("title" => THIS_MONTH_MSG, "date_start" => $month_ts, "date_end" => $today_ts),
		array("title" => LAST_MONTH_MSG, "date_start" => $last_month_ts, "date_end" => $last_month_end),
	);

	$profiles_total_online = 0; 
	// get profiles stats
	for($i = 1; $i < sizeof($profile_types); $i++) {
		// set general constants
		$type_id = $profile_types[$i][0];
		$type_name = $profile_types[$i][1];

		$t->set_var("type_id",   $type_id);
		$t->set_var("type_name", $type_name);

		// get online stats
		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "profiles p ";
		$sql .= " LEFT JOIN ".$table_prefix."users u ON u.user_id=p.user_id ";
		$sql .= " WHERE p.profile_type_id=" . $db->tosql($type_id, INTEGER);
		$sql .= " AND u.last_visit_date>=" . $db->tosql($online_ts, DATETIME);
		$profiles_online = get_db_value($sql);
		$profiles_total_online += $profiles_online;
		if ($profiles_online > 0) {
			$profiles_online = "<a href=\"admin_profiles.php?s_pt=" . $type_id . "&s_on=1\"><b>" . $profiles_online . "</b></a>";
		}
		$t->set_var("profiles_online", $profiles_online);
		$t->parse("profiles_online_stats", true);

		// get registration stats
		$t->set_var("stats_periods", "");
		foreach($stats as $key => $stat_info) {
			$start_date = $stat_info["date_start"];
			$end_date = va_time($stat_info["date_end"]);
			$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "profiles ";
			$sql .= " WHERE profile_type_id=" . $db->tosql($type_id, INTEGER);
			$sql .= " AND date_added>=" . $db->tosql($start_date, DATE);
			$sql .= " AND date_added<" . $db->tosql($day_after_end, DATE);
			$period_profiles = get_db_value($sql);
			if (isset($stats[$key]["total"])) {
				$stats[$key]["total"] += $period_profiles;
			} else {
				$stats[$key]["total"] = $period_profiles;
			}
			if($period_profiles > 0) {
				$period_profiles = "<a href=\"admin_profiles.php?s_pt=".$type_id."&s_sd=".va_date($date_edit_format, $start_date)."&s_ed=".va_date($date_edit_format, $end_date)."\"><b>" . $period_profiles."</b></a>";
			}
			$t->set_var("period_profiles", $period_profiles);
			$t->parse("stats_periods", true);
		}

		$t->parse("types_stats", true);
	}
	// set total online profiles
	$t->set_var("profiles_total_online", $profiles_total_online);

	foreach($stats as $key => $stat_info) {
		$t->set_var("start_date", va_date($date_edit_format, $stat_info["date_start"]));
		$t->set_var("end_date", va_date($date_edit_format, $stat_info["date_end"]));
		$t->set_var("stat_title", $stat_info["title"]);
		$t->set_var("period_total", $stat_info["total"]);
		$t->parse("stats_titles", true);
		$t->parse("stats_totals", true);
	}

	$profile_types = get_db_values("SELECT profile_type_id, profile_type_name FROM " . $table_prefix . "profiles_types ", array(array("", "")));
	$approved_options = array(array("", ALL_MSG), array("1", IS_APPROVED_MSG), array("0", NOT_APPROVED_MSG));
	$online_options = array(array("", ALL_MSG), array("1", ONLINE_MSG), array("0", OFFLINE_MSG));

	$r = new VA_Record($table_prefix . "profiles");
	$r->add_textbox("s_ne", TEXT);
	$r->change_property("s_ne", TRIM, true);
	$r->add_select("s_c", INTEGER, $countries);
	$r->add_select("s_pt", INTEGER, $profile_types);
	$r->add_select("s_lt", INTEGER, $profile_types);
	$r->add_textbox("s_sd", DATE, FROM_DATE_MSG);
	$r->change_property("s_sd", VALUE_MASK, $date_edit_format);
	$r->change_property("s_sd", TRIM, true);
	$r->add_textbox("s_ed", DATE, END_DATE_MSG);
	$r->change_property("s_ed", VALUE_MASK, $date_edit_format);
	$r->change_property("s_ed", TRIM, true);
	$r->add_textbox("s_bsd", DATE, FROM_DATE_MSG);
	$r->change_property("s_bsd", VALUE_MASK, $date_edit_format);
	$r->change_property("s_bsd", TRIM, true);
	$r->add_textbox("s_bed", DATE, END_DATE_MSG);
	$r->change_property("s_bed", VALUE_MASK, $date_edit_format);
	$r->change_property("s_bed", TRIM, true);
	$r->add_select("s_ap", TEXT, $approved_options);
	$r->add_select("s_on", TEXT, $online_options);
	$r->get_form_parameters();
	$r->validate();
	$approved_options = array(array("", ""), array("1", IS_APPROVED_MSG), array("0", NOT_APPROVED_MSG));
	$r->add_select("status_id", TEXT, $approved_options);
	$r->set_form_parameters();

	if (strlen($operation)) {
		if ($operation == "update_status" && strlen($profiles_ids) && strlen($status_id)){
			$ids = explode(",", $profiles_ids);
			for($i = 0; $i < sizeof($ids); $i++) {
				update_user_status($ids[$i], $status_id);
			}
		}
		if ($operation == "remove_profiles" && strlen($profiles_ids)){
			delete_profiles($profiles_ids);
		}
	}

	$where = "";
	$product_search = false;

	if (!$r->errors) {
		if (!$r->is_empty("s_ne")) {
			$sw = explode(" ", $r->get_value("s_ne"));
			for($si = 0; $si < sizeof($sw); $si++) {
				if (strlen($where)) { $where .= " AND "; }
				$s_ne_sql = $db->tosql($sw[$si], TEXT, false);
				$where .= " p.profile_name LIKE '%" . $s_ne_sql . "%'";
			}
		}

		if (!$r->is_empty("s_c")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " p.country_id=" . $db->tosql($r->get_value("s_c"), INTEGER);
		}

		if (!$r->is_empty("s_sd")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " p.date_added>=" . $db->tosql($r->get_value("s_sd"), DATE);
		}

		if (!$r->is_empty("s_ed")) {
			if (strlen($where)) { $where .= " AND "; }
			$end_date = $r->get_value("s_ed");
			$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
			$where .= " p.date_added<" . $db->tosql($day_after_end, DATE);
		}

		if (!$r->is_empty("s_bsd")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " p.birth_date>=" . $db->tosql($r->get_value("s_bsd"), DATE);
		}

		if (!$r->is_empty("s_bed")) {
			if (strlen($where)) { $where .= " AND "; }
			$end_date = $r->get_value("s_bed");
			$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
			$where .= " p.birth_date<" . $db->tosql($day_after_end, DATE);
		}

		if (!$r->is_empty("s_pt")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " p.profile_type_id=" . $db->tosql($r->get_value("s_pt"), INTEGER);
		}
		if (!$r->is_empty("s_lt")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " p.looking_type_id=" . $db->tosql($r->get_value("s_lt"), INTEGER);
		}

		if (!$r->is_empty("s_ap")) {
			if (strlen($where)) { $where .= " AND "; }
			$s_ap = $r->get_value("s_ap");
			$where .= ($s_ap == 1) ? " p.is_approved=1 " : " p.is_approved=0 ";
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
	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "profiles p ";
	$sql .= " LEFT JOIN ".$table_prefix."users u ON u.user_id=p.user_id ";
	$sql .= $where_sql;
	$total_records = get_db_value($sql);

	$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$profiles = array();

	$sql  = " SELECT p.profile_id, p.profile_name, p.is_approved, p.birth_date,p.date_added, ";
	$sql .= " pt.profile_type_name, lt.profile_type_name AS looking_type_name, c.country_name, ";
	$sql .= " u.name, u.login, u.first_name, u.last_name, u.company_name, u.email, u.last_visit_date ";
	$sql .= " FROM " . $table_prefix . "profiles p ";
	$sql .= " LEFT JOIN ".$table_prefix."users u ON u.user_id=p.user_id ";
	$sql .= " LEFT JOIN " . $table_prefix . "profiles_types pt ON p.profile_type_id=pt.profile_type_id ";
	$sql .= " LEFT JOIN " . $table_prefix . "profiles_types lt ON p.looking_type_id=lt.profile_type_id ";
	$sql .= " LEFT JOIN " . $table_prefix . "countries c ON p.country_id=c.country_id ";
	$sql .= $where_sql;
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query($sql . $s->order_by);
	while ($db->next_record()) {
		// profile data
		$profile_id = $db->f("profile_id");
		$profile_name = $db->f("profile_name");
		$is_approved = $db->f("is_approved");
		$birth_date = $db->f("birth_date", DATETIME);
		$date_added = $db->f("date_added", DATETIME);
		$profile_type_name = $db->f("profile_type_name");
		$looking_type_name = $db->f("looking_type_name");
		$country_name = $db->f("country_name");

		// user data
		$login = $db->f("login");
		$user_name = $db->f("name");
		if (!strlen($user_name)) {
			$user_name = trim($db->f("first_name") . " " . $db->f("last_name"));
		}
		if (!strlen($user_name)) {
			$user_name = $db->f("company_name");
		}
		$email = $db->f("email");
		$last_visit_date = $db->f("last_visit_date", DATETIME);

		$profiles[$profile_id] = array(
			"profile_name" => $profile_name,
			"is_approved" => $is_approved,
			"birth_date" => $birth_date,
			"date_added" => $date_added,
			"profile_type_name" => $profile_type_name,
			"looking_type_name" => $looking_type_name,
			"country_name" => $country_name,
			"user_name" => $user_name,
			"email" => $email,
			"last_visit_date" => $last_visit_date,
		);
	}

	if (count($profiles) > 0) {

		$t->set_var("no_records", "");
		$profile_index = 0;
		foreach ($profiles as $profile_id => $profile) 
		{
			$profile_name = $profile["profile_name"];
			$is_approved = $profile["is_approved"];
			$birth_date = $profile["birth_date"];
			$date_added = $profile["date_added"];
			$profile_type_name = $profile["profile_type_name"];
			$looking_type_name = $profile["looking_type_name"];
			$country_name = $profile["country_name"];
			$user_name = $profile["user_name"];
			$email = $profile["email"];
			$last_visit_date = $profile["last_visit_date"];
			$profiles_view_url = $site_url."profiles_view.php?pid=".urlencode($profile_id);

			$approve_operation = ($is_approved == 1) ? "disapprove" : "approve";
			$is_approved_desc = ($is_approved == 1) ? "<b>".YES_MSG."</b>" : NO_MSG;

			$profiles_approve_url->add_parameter("operation", CONSTANT, $approve_operation);
			$profiles_approve_url->add_parameter("pid", CONSTANT, $profile_id);

			$profile_index++;
			$row_style = ($profile_index % 2 == 0) ? "row2" : "row1";
			$t->set_var("row_style", $row_style);

			$t->set_var("profile_index", $profile_index);
			$t->set_var("profile_id", $profile_id);
			$t->set_var("profile_name", htmlspecialchars($profile_name));
			$t->set_var("country_name", htmlspecialchars($country_name));
			$t->set_var("profile_type_name", htmlspecialchars($profile_type_name));
			$t->set_var("looking_type_name", htmlspecialchars($looking_type_name));
			$t->set_var("is_approved", $is_approved_desc);
			$t->set_var("approve_operation", $approve_operation);

			$t->set_var("birth_date", va_date($date_show_format, $birth_date));
			$t->set_var("date_added", va_date($date_show_format, $date_added));

			$t->set_var("user_name", htmlspecialchars($user_name));
			$t->set_var("email", htmlspecialchars($email));
			$t->set_var("profiles_approve_url", htmlspecialchars($profiles_approve_url->get_url()));
			$t->set_var("profiles_view_url", htmlspecialchars($profiles_view_url));



			$t->parse("records", true);
		}
		$t->set_var("profiles_number", $profile_index);
		$t->parse("update_status", false);
		$t->parse("remove_profiles_button", false);
		$t->parse("sorters", false);
	} else {
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->set_var("update_status", "");
		$t->set_var("remove_profiles_button", "");
		$t->parse("no_records", false);
	}

	$t->set_var("s_c_search", htmlspecialchars($r->get_value("s_c")));
	$t->set_var("s_pt_search", htmlspecialchars($r->get_value("s_pt")));
	$t->set_var("s_lt_search", htmlspecialchars($r->get_value("s_lt")));
	$t->set_var("s_ap_search", htmlspecialchars($r->get_value("s_ap")));
	$t->set_var("s_on_search", htmlspecialchars($r->get_value("s_on")));
	$t->set_var("rnd", $new_rnd);

	$t->pparse("main");

?>