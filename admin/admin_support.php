<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_support.php                                        ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once($root_folder_path."messages/".$language_code."/support_messages.php");
	include_once("./admin_common.php");

	check_admin_security("support");

	$va_trail = array(
		"admin_menu.php?code=dashboard" => va_message("DASHBOARD_MSG"),
		"admin_support.php" => va_message("SUPPORT_TICKETS_MSG"),
	);

	$support_settings = get_settings("support");

	$permissions = get_permissions();
	$allow_close = get_setting_value($permissions, "support_ticket_close", 0); 
	$admin_id    = get_session("session_admin_id");

	$admin_support_close_url = new VA_URL("admin_support_reply.php", true);
	$admin_support_close_url->add_parameter("support_id", DB, "support_id");
	$admin_support_close_url->add_parameter("operation", CONSTANT, "close");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_support.html");
	$t->set_var("date_edit_format", join("", $date_edit_format));

	///deleting tickets
	$operation = get_param("operation");
	$items_ids = get_param("items_ids");
	if ($operation == "delete_items" && strlen($items_ids)) {
		$items_for_del = explode(",", $items_ids);
		if (isset($permissions["support_ticket_edit"]) && $permissions["support_ticket_edit"] == 1) {
			foreach($items_for_del as $item_for_del) {
	 			delete_tickets($item_for_del); 
			}
		} else {
		  $t->set_var("error_delete","<font color=red>". va_message("REMOVE_TICKET_NOT_ALLOWED_MSG") ."<br></font>");
		}
	
	}

	$t->set_var("admin_support_href", "admin_support.php");
	$t->set_var("admin_support_reply_href", "admin_support_reply.php");
	$t->set_var("admin_support_request_href", "admin_support_request.php");
	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_support_priorities_href", "admin_support_priorities.php?rp=admin_support.php");
	$t->set_var("admin_support_statuses_href", "admin_support_statuses.php?rp=admin_support.php");
	$t->set_var("admin_support_products_href", "admin_support_products.php?rp=admin_support.php");
	$t->set_var("admin_support_types_href", "admin_support_types.php?rp=admin_support.php");
	$t->set_var("admin_support_settings_href", "admin_support_settings.php");
	$t->set_var("admin_support_prereplies_href", "admin_support_prereplies.php");        
	$t->set_var("admin_support_departments_href", "admin_support_departments.php");        
	$t->set_var("admin_support_admins_href", "admin_support_admins.php");     
	$t->set_var("admin_support_static_tables_href", "admin_support_departments.php");        

	
	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_support.php");
	$s->set_parameters(false, true, true, false);
	$s->set_sorter(va_message("NO_MSG"), "sorter_id", "1", "s.support_id");
	$s->set_sorter(va_message("SUPPORT_SUMMARY_COLUMN"), "sorter_summary", "2", "s.summary");
	$s->set_sorter(va_message("SUPPORT_DEPARTMENT_FIELD"), "sorter_dep", "3", "s.dep_id");
	$s->set_sorter(va_message("TYPE_MSG"), "sorter_type", "4", "st.type_name");
	$s->set_sorter(va_message("STATUS_MSG"), "sorter_status", "5", "ss.status_name");
	$s->set_sorter(va_message("EMAIL_FIELD"), "sorter_user", "6", "s.user_email");
	$s->set_sorter(va_message("ASSIGNED_MSG"), "sorter_admin_alias", "7", "a.admin_alias");
	$s->set_sorter(va_message("LAST_UPDATED_MSG"), "sorter_modified", "8", "s.date_modified");
	$s->set_sorter(va_message("SITE_NAME_MSG"), "sorter_site", "9", "s.site_id");
	if (!$s->order_by) {
		$s->order_by = " ORDER BY sp.priority_rank, s.date_modified DESC ";
	}

	$s_am = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_support.php", "sort_am");
	$s_am->set_parameters(false, true, true, false);
	$s_am->set_sorter(va_message("NO_MSG"), "sorter_id_am", "1", "s.support_id");
	$s_am->set_sorter(va_message("SUPPORT_SUMMARY_COLUMN"), "sorter_summary_am", "2", "s.summary");
	$s_am->set_sorter(va_message("SUPPORT_DEPARTMENT_FIELD"), "sorter_dep_am", "3", "s.dep_id");
	$s_am->set_sorter(va_message("TYPE_MSG"), "sorter_type_am", "4", "st.type_name");
	$s_am->set_sorter(va_message("STATUS_MSG"), "sorter_status_am", "5", "ss.status_name");
	$s_am->set_sorter(va_message("EMAIL_FIELD"), "sorter_user_am", "6", "s.user_email");
	$s_am->set_sorter(va_message("LAST_UPDATED_MSG"), "sorter_modified_am", "7", "s.date_modified");
	$s->set_sorter(va_message("SITE_NAME_MSG"), "sorter_site_am", "8", "s.site_id");
	if (!$s_am->order_by) {
		$s_am->order_by = " ORDER BY sp.priority_rank, s.date_modified DESC ";
	}

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_support.php");

	$admin_header_template = "admin_header_wide.html";
	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// arrays for helpdesk departments, types and statuses
	$support_deps =  array(); $support_types = array(); $support_statuses = array();
	// check departments available for administrator
	$admin_departments_ids = ""; $admin_departments = array(); $departments_values = array(array("",""));
	$types_values = array(array("",""));
	$sql  = " SELECT sd.dep_id,sd.short_name,sd.dep_name ";
	$sql .= " FROM (" . $table_prefix . "support_users_departments sud ";
	$sql .= " INNER JOIN " .$table_prefix. "support_departments sd ON sud.dep_id=sd.dep_id) ";
	$sql .= " WHERE sud.admin_id=" . $db->tosql($admin_id, INTEGER);
	$sql .= " OR sd.admins_all=1 ";
	$db->query($sql);
	while ($db->next_record()) {
		if (strlen($admin_departments_ids)) { $admin_departments_ids .= ","; }
		$admin_dep_id = $db->f("dep_id");
		$short_name = get_translation($db->f("short_name"));
		$dep_name = get_translation($db->f("dep_name"));
		$admin_departments_ids .= $admin_dep_id;
		$support_deps[$admin_dep_id] = array("title" => $dep_name, "short_name" => $short_name, "dep_name" => $dep_name);
		$admin_departments[$admin_dep_id] = array("title" => $short_name);
		$departments_values[] = array($admin_dep_id, $dep_name);
	}

	// get helpdesk types 
	$support_types[0] = array("id" => 0, "name" => "[without type]");
	$sql  = " SELECT st.type_id,st.type_name ";
	$sql .= " FROM " . $table_prefix . "support_types st ";
	$sql .= " ORDER BY st.type_order, st.type_name ";
	$db->query($sql);
	while ($db->next_record()) {
		$type_id = $db->f("type_id");
		$type_name = get_translation($db->f("type_name"));
		$support_types[$type_id] = array("id" => $type_id, "name" => $type_name);
		$types_values[] = array($type_id, $type_name);
	}

	// get helpdesk statuses
	$support_statuses[0] = array("id" => 0, "name" => "[without status]");
	$sql  = " SELECT ss.status_id, ss.status_name, ss.is_internal ";
	$sql .= " FROM " . $table_prefix . "support_statuses ss ";
	$sql .= " ORDER BY ss.status_name ASC ";
	$statuses_values = array(array("",""));
	$statuses_stats = array();
	$db->query($sql);
	while($db->next_record()) {
		$status_id = $db->f("status_id");
		$status_name = get_translation($db->f("status_name"));
		$statuses_stats[$status_id] = $status_name;

		$support_statuses[$status_id] = array("id" => $status_id, "name" => $status_name);
		if ($db->f("is_internal") == "1") {
			$statuses_values[] = array($db->f("status_id"), $status_name . " (Internal)");
		} else {
			$statuses_values[] = array($db->f("status_id"), $status_name);
		}
	}

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

/*
//TEST DATA
$today_ts = mktime (0, 0, 0, $cmonth, $cday, $cyear);
$tomorrow_ts = mktime (0, 0, 0, $cmonth, $cday + 1, $cyear);
$yesterday_ts = mktime (0, 0, 0, $cmonth, $cday - 1, $cyear);
$week_ts = mktime (0, 0, 0, $cmonth, $cday - 6, $cyear - 1);
$month_ts = mktime (0, 0, 0, $cmonth, 1, $cyear - 3);
$last_month_ts = mktime (0, 0, 0, $cmonth - 1, 1, $cyear - 6);
$last_month_days = date("t", $last_month_ts);
$last_month_end = mktime (0, 0, 0, $cmonth - 1, 1, $cyear);
$today_date = va_date($date_edit_format, $today_ts);
*/

	$stat_dates = array(
		"today" => array("title" => va_message("TODAY_MSG"), "date_start" => $today_ts, "date_end" => $today_ts, "total" => 0),
		"yeste" => array("title" => va_message("YESTERDAY_MSG"), "date_start" => $yesterday_ts, "date_end" => $yesterday_ts, "total" => 0),
		"last7" => array("title" => va_message("LAST_SEVEN_DAYS_MSG"), "date_start" => $week_ts, "date_end" => $today_ts, "total" => 0),
		"thism" => array("title" => va_message("THIS_MONTH_MSG"), "date_start" => $month_ts, "date_end" => $today_ts, "total" => 0),
		"lastm" => array("title" => va_message("LAST_MONTH_MSG"), "date_start" => $last_month_ts, "date_end" => $last_month_end, "total" => 0),
	);

	// get orders stats
	$first_group = get_setting_value($support_settings, "stat_first_group");
	$second_group = get_setting_value($support_settings, "stat_second_group"); 
	$first_values = array(); $second_values = array();
	if ($first_group == "types") {
		$first_values = $support_types;
		$first_field = "support_type_id";
		$first_param = "s_tp";
	} else if ($first_group == "statuses") {
		$first_values = $support_statuses;
		$first_field = "support_status_id";
		$first_param = "s_st";
	}
	if ($second_group == "types") {
		$second_values = $support_types;
		$second_field = "support_type_id";
		$second_param = "s_tp";
	} else if ($second_group == "statuses") {
		$second_values = $support_statuses;
		$second_field = "support_status_id";
		$second_param = "s_st";
	}
	

	$support_stats = array();
	foreach ($support_deps as $dep_id => $dep_data) {
		$support_stats[$dep_id] = array();
		foreach ($stat_dates as $date_key => $date_data) {
			$support_stats[$dep_id][$date_key] = 0;
		}
		if ($first_group) {
			$support_stats[$dep_id][$first_group] = array();
			foreach ($first_values as $first_id => $first_data) {
				$support_stats[$dep_id][$first_group][$first_id] = array();
				foreach ($stat_dates as $date_key => $date_data) {
					$support_stats[$dep_id][$first_group][$first_id][$date_key] = 0;
				}
				if ($second_group) {
					$support_stats[$dep_id][$first_group][$first_id][$second_group] = array();
					foreach ($second_values as $second_id => $second_data) {
						$support_stats[$dep_id][$first_group][$first_id][$second_group][$second_id] = array();
						foreach ($stat_dates as $date_key => $date_data) {
							$support_stats[$dep_id][$first_group][$first_id][$second_group][$second_id][$date_key] = 0;
						}
					}
				}
			}
		}
	}

	foreach ($stat_dates as $date_key => $date_data) {
		$start_date = $date_data["date_start"];
		$end_date = va_time($date_data["date_end"]);
		$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
		$sql  = " SELECT dep_id, support_type_id, support_status_id, COUNT(*) as tickets FROM " . $table_prefix . "support ";
		$sql .= " WHERE date_modified>=" . $db->tosql($start_date, DATE);
		$sql .= " AND date_modified<" . $db->tosql($day_after_end, DATE);
		$sql .= " AND dep_id IN (" . $db->tosql($admin_departments_ids, INTEGERS_LIST) . ")";
		$sql .= " GROUP BY dep_id, support_type_id, support_status_id ";
		$db->query($sql); 
		while ($db->next_record()) {
			$dep_id = $db->f("dep_id");
			$tickets = $db->f("tickets");
			$stat_dates[$date_key]["total"] += $tickets;
			$support_stats[$dep_id][$date_key] += $tickets;
			if ($first_group) {
				$first_id = $db->f($first_field);
				$support_stats[$dep_id][$first_group][$first_id][$date_key] += $tickets;
				if ($second_group) {
					$second_id = $db->f($second_field);
					$support_stats[$dep_id][$first_group][$first_id][$second_group][$second_id][$date_key] += $tickets;
				}
			}
		}
	}

	// url for statistics
	$stat_url = new VA_URL("admin_support.php", false);

	// show titles for stats 
	foreach ($stat_dates as $date_key => $date_data) {
		$stat_url->add_parameter("s_sd", CONSTANT, va_date($date_edit_format, $date_data["date_start"])); 
		$stat_url->add_parameter("s_ed", CONSTANT, va_date($date_edit_format, $date_data["date_end"])); 
		$t->set_var("stat_url", $stat_url->get_url());
		$t->set_var("stat_title", $date_data["title"]);
		$t->parse("stats_titles", true);
	}

	// show main stats data
	foreach ($support_stats as $dep_id => $dep_data) {
		$t->set_var("stats_data", "");
		foreach ($stat_dates as $date_key => $date_data) {
			$stat_value = $support_stats[$dep_id][$date_key];
			$stat_url->add_parameter("s_dp", CONSTANT, $dep_id); 
			$stat_url->add_parameter("s_sd", CONSTANT, va_date($date_edit_format, $date_data["date_start"])); 
			$stat_url->add_parameter("s_ed", CONSTANT, va_date($date_edit_format, $date_data["date_end"])); 
			$t->set_var("stat_url", $stat_url->get_url());
			$t->set_var("stat_value", $stat_value);
			$t->parse("stats_data", true);
		}
		$stat_url->remove_parameter("s_sd"); 
		$stat_url->remove_parameter("s_ed"); 
		$t->set_var("stat_url", $stat_url->get_url());
		$t->set_var("stat_name", $support_deps[$dep_id]["dep_name"]);
		$t->set_var("stat_class", "top-stat");
		$t->parse("stats_rows", true);

		if ($first_group) {
			foreach ($support_stats[$dep_id][$first_group] as $first_id => $first_data) {
				$t->set_var("stats_data", "");
				$row_total = 0;
				foreach ($stat_dates as $date_key => $date_data) {
					$stat_value = $support_stats[$dep_id][$first_group][$first_id][$date_key];
					$row_total += $stat_value;
					$stat_url->add_parameter($first_param, CONSTANT, $first_id); 
					$stat_url->add_parameter("s_sd", CONSTANT, va_date($date_edit_format, $date_data["date_start"])); 
					$stat_url->add_parameter("s_ed", CONSTANT, va_date($date_edit_format, $date_data["date_end"])); 
					$t->set_var("stat_url", $stat_url->get_url());
					$t->set_var("stat_value", $stat_value);
					$t->parse("stats_data", true);
				}
				// show row only if it has non-zero data
				if ($row_total) {
					$stat_url->remove_parameter("s_sd"); 
					$stat_url->remove_parameter("s_ed"); 
					$t->set_var("stat_url", $stat_url->get_url());
					$t->set_var("stat_name", $first_values[$first_id]["name"]);
					$t->set_var("stat_class", "sub-stat");
					$t->parse("stats_rows", true);
				}

				if ($second_group) {
					foreach ($support_stats[$dep_id][$first_group][$first_id][$second_group] as $second_id => $second_data) {
						$t->set_var("stats_data", "");
						$row_total = 0;
						foreach ($stat_dates as $date_key => $date_data) {
							$stat_value = $support_stats[$dep_id][$first_group][$first_id][$second_group][$second_id][$date_key];
							$row_total += $stat_value;
							$stat_url->add_parameter($second_param, CONSTANT, $second_id); 
							$stat_url->add_parameter("s_sd", CONSTANT, va_date($date_edit_format, $date_data["date_start"])); 
							$stat_url->add_parameter("s_ed", CONSTANT, va_date($date_edit_format, $date_data["date_end"])); 
							$t->set_var("stat_url", $stat_url->get_url());
							$t->set_var("stat_value", $stat_value);
							$t->parse("stats_data", true);
						}
						// show row only if it has non-zero data
						if ($row_total) {
							$stat_url->remove_parameter("s_sd"); 
							$stat_url->remove_parameter("s_ed"); 
							$t->set_var("stat_url", $stat_url->get_url());
							$t->set_var("stat_name", $second_values[$second_id]["name"]);
							$t->set_var("stat_class", "sub-sub-stat");
							$t->parse("stats_rows", true);
						}
					}
					$stat_url->remove_parameter($second_param); 
				}
				$stat_url->remove_parameter($first_param); 
			}
		}
	}
	// show total for stats 
	foreach ($stat_dates as $date_key => $date_data) {
		$t->set_var("stat_total", $date_data["total"]);
		$t->parse("stats_totals", true);
	}
	
	$search_options = array(array(0, va_message("ACTIVE_MSG")), array(1, va_message("HIDDEN_MSG")), array(2, va_message("ALL_MSG")));
	$s_at_values = array();
	if ($admin_departments_ids) {
		$sql  = " SELECT a.admin_id, a.admin_name ";
		$sql .= " FROM (" . $table_prefix . "admins a ";
		$sql .= " INNER JOIN " . $table_prefix . "support_users_departments sud ON a.admin_id=sud.admin_id) ";
		$sql .= " WHERE sud.dep_id IN (" . $db->tosql($admin_departments_ids, INTEGERS_LIST) . ") ";
		$sql .= " GROUP BY a.admin_id, a.admin_name ";
		$s_at_values = get_db_values($sql, array(array("", "")));
	}
	if ($sitelist) {
		$sites = get_db_values("SELECT site_id, site_name FROM " . $table_prefix . "sites ORDER BY site_id ", array(array("", "")));
	}

	$r = new VA_Record("");
	$r->add_textbox("s_tn", TEXT, va_message("BY_TICKET_NO_MSG"));
	$r->change_property("s_tn", TRIM, true);
	$r->add_textbox("s_ne", TEXT, va_message("BY_NAME_EMAIL_MSG"));
	$r->change_property("s_ne", TRIM, true);
	$r->add_textbox("s_sm", TEXT, va_message("BY_SUMMARY_MSG"));
	$r->change_property("s_sm", TRIM, true);
	$r->add_textbox("s_kw", TEXT, va_message("BY_KEYWORD_MSG"));
	$r->change_property("s_kw", TRIM, true);
	$r->add_textbox("s_sd", DATE, va_message("FROM_DATE_MSG"));
	$r->change_property("s_sd", VALUE_MASK, $date_edit_format);
	$r->change_property("s_sd", TRIM, true);
	$r->add_textbox("s_ed", DATE, va_message("END_DATE_MSG"));
	$r->change_property("s_ed", VALUE_MASK, $date_edit_format);
	$r->change_property("s_ed", TRIM, true);		
	if (sizeof($s_at_values) > 1) {
		$r->add_select("s_at", INTEGER, $s_at_values, va_message("ASSIGN_TO_MSG"));
	}
	if (sizeof($departments_values) > 2) {
		$r->add_select("s_dp", INTEGER, $departments_values, va_message("SUPPORT_DEPARTMENT_FIELD"));
	}
	if (sizeof($types_values) > 2) {
		$r->add_select("s_tp", INTEGER, $types_values, va_message("SUPPORT_TYPE_FIELD"));
	}
	$r->add_select("s_st", INTEGER, $statuses_values, va_message("STATUS_MSG"));
	$r->add_radio("s_in", TEXT, $search_options, va_message("SEARCH_IN_MSG"));
	$r->change_property("s_in", DEFAULT_VALUE, 0);
	if ($sitelist) {
		$r->add_select("s_sti", TEXT, $sites, va_message("ADMIN_SITE_MSG"));
	}
	$r->get_form_parameters();
	if (!$r->is_empty("s_dp")) {
		// check if administrator assigned to selected department 
		$s_dp = $r->get_value("s_dp");
		if (!isset($support_deps[$s_dp])) {
			$r->set_value("s_dp", "");
		}
	}
	if ($r->get_value("s_in") == "") {$r->set_value("s_in", 0);}
	// set classes to show/hide fields and calculate visible fields
	$search_fields = array(
		"s_tn" => array("class" => "s_tn_hide_class", "control" => "s_tn", "default_field" => false),
		"s_ne" => array("class" => "s_ne_hide_class", "control" => "s_ne", "default_field" => true),
		"s_sm" => array("class" => "s_sm_hide_class", "control" => "s_sm"),
		"s_kw" => array("class" => "s_kw_hide_class", "control" => "s_kw"),
		"s_sd_ed" => array("class" => "s_sd_ed_hide_class", "control" => "s_sd,s_ed"),
		"s_at" => array("class" => "s_at_hide_class", "control" => "s_at"),
		"s_dp" => array("class" => "s_dp_hide_class", "control" => "s_dp"),
		"s_tp" => array("class" => "s_tp_hide_class", "control" => "s_tp"),
		"s_st" => array("class" => "s_st_hide_class", "control" => "s_st"),
		"s_in" => array("class" => "s_in_hide_class", "control" => "s_in", "default_value" => 0),
		"s_sti" => array("class" => "s_sti_hide_class", "control" => "s_sti"),
	);

	$filter_url = new VA_URL("admin_support.php", false);
	$filter_url->add_parameter("sort_ord", REQUEST, "sort_ord");
	$filter_url->add_parameter("sort_dir", REQUEST, "sort_dir");        
	$active_fields = set_search_fields($search_fields, $r, $filter_url, true);
	$r->set_form_parameters();

	$admin_settings = get_admin_settings(array("support-search"));
	$search_form_class = get_setting_value($admin_settings, "support-search");
	$t->set_var("search_form_class", htmlspecialchars($search_form_class));

	// create new ticket link
	if (isset($permissions["support_ticket_new"]) && $permissions["support_ticket_new"] == 1) {
		$t->parse("create_ticket_link", false);
	}


	$statuses_is_list = get_db_values("SELECT status_id, is_list FROM " . $table_prefix . "support_statuses", "");
	$s_in = $r->get_value("s_in");
	$where = ""; $search = "";
	if (!$r->is_empty("s_dp")) {
		if (strlen($where)) $where .= " AND ";
		$where .= " s.dep_id= " . $db->tosql($r->get_value("s_dp"), INTEGER);
	} else {
		if (strlen($where)) $where .= " AND ";
		$where .= " s.dep_id IN (" . $db->tosql($admin_departments_ids, INTEGERS_LIST) . ")";
	}	
	if (!$r->is_empty("s_tp")) {
		if (strlen($where)) $where .= " AND ";
		$where .= " s.support_type_id= " . $db->tosql($r->get_value("s_tp"), INTEGER);
	}	

	if (!$r->is_empty("s_st")) {
		if (strlen($where)) $where .= " AND ";
		$where .= " s.support_status_id=" . $db->tosql($r->get_value("s_st"), INTEGER);
	}	else {
		if ($s_in == 1) {	
			if (strlen($where)) $where .= " AND ";
			$where .= " ss.is_list=0 ";
		} else if ($s_in == 2) {
			// no condition required for all tickets
		} else {
			if (strlen($where)) $where .= " AND ";
			$where .= " (ss.is_list=1 OR ss.is_list IS NULL) ";
		}
	}
	if (!$r->is_empty("s_sm")) {
		if (strlen($where)) $where .= " AND ";
		$where .= " (s.summary LIKE '%" . $db->tosql($r->get_value("s_sm"), TEXT, false) . "%')";
		if ($search) $search .= " and ";
		$search .= va_message("BY_SUMMARY_MSG") . ": '<b>" . htmlspecialchars($r->get_value("s_sm")) . "</b>'";
	}
	if (!$r->is_empty("s_at")) {
		if (strlen($where)) $where .= " AND ";
		if ($search) $search .= " and ";
		$where .= " s.admin_id_assign_to=" . $db->tosql($r->get_value("s_at"), INTEGER);
		$search .= " " . va_message("ASSIGN_TO_MSG") . ": '<b>" . get_array_value($r->get_value("s_at"), $s_at_values) . "</b>'";
	}

	if (!$r->is_empty("s_ne")) {
		if (strlen($where)) $where .= " AND ";
		$where .= " (s.user_email LIKE '%" . $db->tosql($r->get_value("s_ne"), TEXT, false) . "%'";
		$where .= " OR s.user_name LIKE '%" . $db->tosql($r->get_value("s_ne"), TEXT, false) . "%')";
		if ($search) $search .= " and ";
		$search .= va_message("BY_NAME_EMAIL_MSG") . ": '<b>" . htmlspecialchars($r->get_value("s_ne")) . "</b>'";
	}

	if (!$r->is_empty("s_sd")) {
		if (strlen($where)) { $where .= " AND "; }
		$where .= " s.date_modified>=" . $db->tosql($r->get_value("s_sd"), DATE);
	}

	if (!$r->is_empty("s_ed")) {
		if (strlen($where)) { $where .= " AND "; }
		$end_date = $r->get_value("s_ed");
		$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
		$where .= " s.date_modified<" . $db->tosql($day_after_end, DATE);
	}
	
	if (!$r->is_empty("s_sti")) {
		if (strlen($where)) { $where .= " AND "; }
		$s_sti = $r->get_value("s_sti");
		$where .= " s.site_id=" . $db->tosql($r->get_value("s_sti"), INTEGER);
	}
		
	$sql_join_b = "";
	$sql_join_e = "";
	if (!$r->is_empty("s_kw")) {
		if (strlen($where)) $where .= " AND ";
		$where .= " (s.summary LIKE '%" . $db->tosql($r->get_value("s_kw"), TEXT, false) . "%'";
		$where .= " OR s.description LIKE '%" . $db->tosql($r->get_value("s_kw"), TEXT, false) . "%'";
		$where .= " OR sm.message_text LIKE '%" . $db->tosql($r->get_value("s_kw"), TEXT, false) . "%')";
		$sql_join_b = "(";
		$sql_join_e = "LEFT JOIN " . $table_prefix . "support_messages sm ON sm.support_id = s.support_id) ";
		if ($search) $search .= " and ";
		$search .= va_message("BY_KEYWORD_MSG") . ": '<b>" . htmlspecialchars($r->get_value("s_kw")) . "</b>'";
	}
	if (!$r->is_empty("s_tn")) {
		if (strlen($where)) $where .= " AND ";
		$where .= " s.support_id = " . $db->tosql($r->get_value("s_tn"), INTEGER);
		if ($search) $search .= " and ";
		$search .= va_message("BY_TICKET_NO_MSG") . ": '<b>" . htmlspecialchars($r->get_value("s_tn")) . "</b>'";
	}
	

	if ($search && ($s_in == 0)) {$search = "'<b>".va_message("ACTIVE_TICKETS_MSG")."</b>' and " . $search;}
	
	$t->set_var("search", $search);

	// generate where condition to show tickets allocated to administrator
	if (strlen($where)) {
		$where_am =  "WHERE s.admin_id_assign_to = " . $db->tosql($admin_id, INTEGER) . " AND " . $where; 
	} else {
		$where_am =  "WHERE s.admin_id_assign_to = " . $db->tosql($admin_id, INTEGER); 
	}

	// don't show in the main list my tickets
	if (strlen($where)) {
		$where =  "WHERE s.admin_id_assign_to<>" . $db->tosql($admin_id, INTEGER) . " AND " . $where; 
	} else {
		$where =  "WHERE s.admin_id_assign_to<>" . $db->tosql($admin_id, INTEGER); 
	}
	//if (strlen($where)) { $where = " WHERE " . $where; }

	// get status_id where is_closed = 1
	$close_status_id = "";
	$sql  = "SELECT status_id FROM " . $table_prefix . "support_statuses WHERE is_closed = 1";
	$db->query($sql);
	if ($db->next_record()) {
		$close_status_id = $db->f("status_id");
	}

	// set array html
	$html_status = array();
	$sql = "SELECT status_id, status_icon, html_start, html_end FROM " . $table_prefix . "support_statuses";
	$db->query($sql);
	if ($db->num_rows($sql)) {
		while ($db->next_record()) {
			$html_status[$db->f("status_id")] = array($db->f("status_icon"), $db->f("html_start"), $db->f("html_end"));
		}
	}

	// set up variables for navigator allocated to me
	$admin_records = 0;
	$sql  = " SELECT COUNT(*)  ";
	$sql .= " FROM " . $sql_join_b . "(" . $table_prefix . "support s ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_statuses ss ON ss.status_id = s.support_status_id) ";
	$sql .= $sql_join_e;
	$sql .= $where_am;
	$sql .= " GROUP BY s.support_id ";
	$db->query($sql);
	while ($db->next_record()) {
		$admin_records++;
	}
	$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
	$pages_number = 5;

	$page_number = $n->set_navigator("navigator_am", "page_am", SIMPLE, $pages_number, $records_per_page, $admin_records, false);
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	
	$t->set_var("allocated_me", "");
	$t->set_var("records_am", "");
	$t->set_var("navigator_block_am", "");

	if (strlen($admin_departments_ids)) {
		$support_ids = array();
		$sql  = " SELECT s.support_id ";
		$sql .= " FROM " . $sql_join_b . "(((((";
		$sql .= $table_prefix . "support s ";
		$sql .= " LEFT JOIN " . $table_prefix . "support_statuses ss ON ss.status_id = s.support_status_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "support_types st ON st.type_id = s.support_type_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "support_priorities sp ON sp.priority_id = s.support_priority_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "support_departments sd ON sd.dep_id = s.dep_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "sites sti ON sti.site_id = s.site_id) ";
		$sql .= $sql_join_e;
		$sql .= $where_am;
		$sql .= " GROUP BY s.support_id, s.summary, ss.status_name, st.type_name, s.user_email, s.date_modified, s.site_id, sp.priority_rank, s.date_modified ";
		$sql .= $s_am->order_by;
		$db->query($sql);
		while ($db->next_record()) {
			$support_id = $db->f("support_id");
			$support_ids[] = $support_id;
		}

		$sql  = " SELECT s.support_id, s.summary, ss.status_name, ss.status_id, st.type_name, s.user_email, s.dep_id, ";
		$sql .= " sp.priority_name, sp.admin_html, s.date_modified, sd.short_name, sti.short_name ";
		$sql .= " FROM (((((";
		$sql .= $table_prefix . "support s ";
		$sql .= " LEFT JOIN " . $table_prefix . "support_statuses ss ON ss.status_id = s.support_status_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "support_types st ON st.type_id = s.support_type_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "support_priorities sp ON sp.priority_id = s.support_priority_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "support_departments sd ON sd.dep_id = s.dep_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "sites sti ON sti.site_id = s.site_id) ";
		$sql .= " WHERE support_id IN (".$db->tosql($support_ids, INTEGERS_LIST).")";
		$sql .= $s_am->order_by;
		$db->query($sql);
		if ($db->next_record()) {
			$admin_support_reply_url = new VA_URL("admin_support_reply.php", true);
			$admin_support_reply_url->add_parameter("support_id", DB, "support_id");
		
			if ($sitelist) {
				$t->parse("site_name_header_am");
			}
			$t->parse("sorters_am", false);
			$next = false;
			do {
				$status_id = $db->f("status_id");
				$dep_id = $db->f("dep_id");
				$t->set_var("support_id_am", $db->f("support_id"));
		
				$t->set_var("admin_support_reply_url_am", $admin_support_reply_url->get_url());
		
				$t->set_var("summary_am", htmlspecialchars($db->f("summary")));
				if (isset($html_status[$status_id]) && $html_status[$status_id][1]) {
					$t->set_var("html_start_am", $html_status[$db->f("status_id")][1]);
					$t->set_var("html_end_am", $html_status[$db->f("status_id")][2]);
				} else {
					$t->set_var("html_start_am", "");
					$t->set_var("html_end_am", "");
				}

				$dep_am = isset($support_deps[$dep_id]) ? $support_deps[$dep_id]["short_name"] : "";
				$t->set_var("dep_am", $dep_am);

				$status = strlen($db->f("status_name")) ? $db->f("status_name") : "";
				$status = get_translation($status);
				$t->set_var("status_am", $status);
				if (isset($html_status[$status_id]) && $html_status[$status_id][0]) {
					$t->set_var("status_icon_am", $html_status[$db->f("status_id")][0]);
					$t->parse("status_ico_am", false);
				} else {
					$t->set_var("status_ico_am", "");
				}

				$t->set_var("type_am", get_translation($db->f("type_name")));
				$t->set_var("user_email_am", $db->f("user_email"));
		
				$priority = "";
				$priority_name = get_translation($db->f("priority_name"));
				$priority_html = $db->f("admin_html");
				$t->set_var("priority_html", $priority_html);
		
				if ($next) {
					$t->set_var("style_am","row1");
				} else {
					$t->set_var("style_am","row2");
				}
				$next = !$next;
		
				$date_modified = $db->f("date_modified", DATETIME);
				$date_modified_string = va_date($datetime_show_format, $date_modified);
				$t->set_var("date_modified_am", $date_modified_string);
				if ($sitelist) {
					$t->set_var("site_name", $db->f("short_name"));
					$t->parse("site_name_am", false);
				}
				if ($db->f("status_id") != $close_status_id) {
					if ($allow_close) {
						$t->set_var("close_ticket_am", $admin_support_close_url->get_url());
						$t->set_var("close_summary_am", va_message("CLOSE_TICKET_MSG"));
						$t->parse("close_ticket_enable_am", false);
					}
					$t->set_var("close_ticket_disable_am", "");
				} else {
					$t->set_var("close_summary_am", va_message("TICKET_IS_CLOSED_MSG"));
					$t->set_var("close_ticket_enable_am", "");
					$t->parse("close_ticket_disable_am", false);
				}

				$t->parse("records_am", true);
			} while($db->next_record());
			$t->parse("allocated_me", true);
		}
	}

	// set up variables for navigator
	$main_records = 0;
	$sql  = " SELECT COUNT(*)  ";
	$sql .= " FROM " . $sql_join_b . "(" . $table_prefix . "support s ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_statuses ss ON ss.status_id = s.support_status_id) ";
	$sql .= $sql_join_e;
	$sql .= $where;
	$sql .= " GROUP BY s.support_id ";
	$db->query($sql);
	while ($db->next_record()) {
		$main_records++;
	}
	$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
	$pages_number = 5;

	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $main_records, false);
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	
	// main tickets list
	$item_index = 0;
	$short_name = "";
	if (strlen($admin_departments_ids)) {
		$support_ids = array(); $support_ips = array();
		$sql  = " SELECT s.support_id, s.remote_address ";
		$sql .= " FROM " . $sql_join_b . "((((((";
		$sql .= $table_prefix . "support s ";
		$sql .= " LEFT JOIN " . $table_prefix . "support_statuses ss ON ss.status_id = s.support_status_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "support_types st ON st.type_id = s.support_type_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "support_priorities sp ON sp.priority_id = s.support_priority_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "support_departments sd ON sd.dep_id = s.dep_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "admins a ON a.admin_id = s.admin_id_assign_to) ";
		$sql .= " LEFT JOIN " . $table_prefix . "sites sti ON sti.site_id = s.site_id) ";
		$sql .= $sql_join_e;
		$sql .= $where;
		$sql .= " GROUP BY s.support_id, s.remote_address, s.summary, ss.status_name, st.type_name, s.user_email, s.date_modified, s.site_id, sp.priority_rank, s.date_modified ";
		$sql .= $s->order_by;
		$db->query($sql);
		while ($db->next_record()) {
			$support_id = $db->f("support_id");
			$support_ids[] = $support_id;
			$remote_address = $db->f("remote_address");
			if ($remote_address) { $support_ips[$remote_address] = $remote_address; }
		}

		// check black ips
		$black_ips = blacklist_check("support", array_keys($support_ips));

		$sql  = " SELECT s.support_id, s.summary, ss.status_name, ss.status_id, st.type_name, s.user_email, a.admin_alias, s.dep_id, ";
		$sql .= " s.date_modified, s.remote_address, sd.short_name, a.login, ";
		$sql .= " sp.admin_html, sp.priority_name, sti.short_name ";
		$sql .= " FROM ((((((";
		$sql .= $table_prefix . "support s ";
		$sql .= " LEFT JOIN " . $table_prefix . "support_statuses ss ON ss.status_id = s.support_status_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "support_types st ON st.type_id = s.support_type_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "support_priorities sp ON sp.priority_id = s.support_priority_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "support_departments sd ON sd.dep_id = s.dep_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "admins a ON a.admin_id = s.admin_id_assign_to) ";
		$sql .= " LEFT JOIN " . $table_prefix . "sites sti ON sti.site_id = s.site_id) ";
		$sql .= " WHERE support_id IN (".$db->tosql($support_ids, INTEGERS_LIST).")";
		$sql .= $s->order_by;
		$db->query($sql);
		if ($db->next_record()) {				
		  if (isset($permissions["support_ticket_edit"]) && $permissions["support_ticket_edit"] == 1) {
		  	$t->parse("delete_tickets_link",false);
			}
			$admin_support_reply_url = new VA_URL("admin_support_reply.php", true);
			$admin_support_reply_url->add_parameter("support_id", DB, "support_id");
			$admin_support_request_url = new VA_URL("admin_support_request.php", true);
			$admin_support_request_url->add_parameter("support_id", DB, "support_id");
		
			$colspan = 10;
			if ($sitelist) {
				$t->parse("site_name_header");
				$colspan ++;
			}
			$t->parse("sorters", false);
			$t->set_var("items_number", $item_index);
			$t->set_var("colspan", $colspan);
			$next = false;
			do {
		  	$item_index++;
		  	$t->set_var("item_index", $item_index);
				$status_id = $db->f("status_id");
				$dep_id = $db->f("dep_id");
				$remote_address = $db->f("remote_address");


				$t->set_var("support_id", $db->f("support_id"));
		
				$t->set_var("admin_support_reply_url", $admin_support_reply_url->get_url());
				$t->set_var("admin_support_request_url", $admin_support_request_url->get_url());
		
				$t->set_var("summary", htmlspecialchars($db->f("summary")));
				if (isset($html_status[$status_id]) && $html_status[$status_id][1]) {
					$t->set_var("html_start", $html_status[$status_id][1]);
					$t->set_var("html_end", $html_status[$status_id][2]);
				} else {
					$t->set_var("html_start", "");
					$t->set_var("html_end", "");
				}

				$dep = isset($support_deps[$dep_id]) ? $support_deps[$dep_id]["short_name"] : "";
				$t->set_var("dep", $dep);

				$status = get_translation($db->f("status_name"));
				$t->set_var("status", $status);
				if (isset($html_status[$status_id]) && $html_status[$status_id][0]) {
					$t->set_var("status_icon", $html_status[$db->f("status_id")][0]);
					$t->parse("status_ico", false);
				} else {
					$t->set_var("status_ico", "");
				}

				$t->set_var("type", get_translation($db->f("type_name")));
				$t->set_var("user_email", $db->f("user_email"));
				if ($db->f("admin_alias") != "") {$t->set_var("admin_alias", $db->f("admin_alias"));
				} else {
				$t->set_var("admin_alias", $db->f("login"));
				}
		
				$priority = "";
				$priority_name = get_translation($db->f("priority_name"));
				$priority_html = $db->f("admin_html");
				$t->set_var("priority_html", $priority_html);
		
				$classes = array();
				if (isset($black_ips[$remote_address]) && $black_ips[$remote_address]["rule"] == "blocked") {
					$classes[] = "data-blocked";
				} else if (isset($black_ips[$remote_address]) && $black_ips[$remote_address]["rule"] == "warning") {
					$classes[] = "data-warning";
				} else {
					$classes[] = ($item_index % 2 == 0) ? "row1" : "row2";
				}
				$t->set_var("style", implode(" ", $classes));
		
				$date_modified = $db->f("date_modified", DATETIME);
				$date_modified_string = va_date($datetime_show_format, $date_modified);
				$t->set_var("date_modified", $date_modified_string);
				
				if ($sitelist) {
					$t->set_var("site_name", $db->f("short_name"));
					$t->parse("site_name_block", false);
				}
				if ($db->f("status_id") != $close_status_id) {
					if ($allow_close) {
						$t->set_var("close_ticket", $admin_support_close_url->get_url());
						$t->set_var("close_summary", va_message("CLOSE_TICKET_MSG"));
						$t->parse("close_ticket_enable", false);
					}
					$t->set_var("close_ticket_disable", "");
				} else {
					$t->set_var("close_summary", va_message("TICKET_IS_CLOSED_MSG"));
					$t->set_var("close_ticket_enable", "");
					$t->parse("close_ticket_disable", false);
				}
				
				$short_name = get_translation($db->f("short_name"));
				$t->parse("records", true);

			} while($db->next_record());

			$t->set_var("items_number", $item_index);
			$t->parse("tickets_block", true);
		} else {
			$t->set_var("tickets_block", "");
			$t->set_var("records", "");
			$t->set_var("navigator_block", "");
			if (!$admin_records) {
				if ($search) {
					$t->set_var("tickets_block", "<p><font color='red'><b>".va_message("NO_TICKETS_FOUND_MSG")."</b></font>");
				} else {
					$t->set_var("tickets_block", "<p><font color='red'><b>".va_message("NO_TICKETS_FOUND_MSG")."</b></font>");
				}
			}
		}
	}	else {
		$t->set_var("tickets_block", "<p><font color='red'><b>".va_message("NOT_ASSIGNED_ANY_DEP_MSG")."</b></font>");
		$t->set_var("records", "");
		$t->set_var("navigator_block", "");
	}

	if ($sitelist) {
		$t->parse("sitelist");
	}
	$t->pparse("main");
