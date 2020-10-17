<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_orders.php                                         ***
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
	include_once($root_folder_path . "includes/shopping_cart.php");
	include_once($root_folder_path . "includes/order_items.php");
	include_once($root_folder_path . "includes/order_links.php");
	include_once($root_folder_path . "includes/parameters.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("sales_orders");

	$va_trail = array(
		"admin_menu.php?code=dashboard" => va_message("DASHBOARD_MSG"),
		"admin_orders.php" => va_message("ORDERS_MSG"),
	);

	$admin_info = get_session("session_admin_info");
	$privilege_id = get_session("session_admin_privilege_id");

	$access_all_user_types = get_setting_value($admin_info, "user_types_all", 0); 
	$access_unreg_users = get_setting_value($admin_info, "non_logged_users", 0); 
	$access_user_types = get_setting_value($admin_info, "user_types_ids", ""); 
	$orders_currency = get_setting_value($settings, "orders_currency", 0);
	$default_country_id = get_setting_value($settings, "country_id", "");
	$default_state_id = get_setting_value($settings, "state_id", "");

	$permissions = get_permissions();
	$operation  = get_param("operation");
	$orders_ids = get_param("orders_ids");
	$status_id	= get_param("status_id");

	$orders_errors = "";
	$recurring_errors = ""; $recurring_success = "";
	if ($operation == "recurring") {
		include_once("./admin_orders_recurring.php");
		if ($recurring_errors) {
			$orders_errors = $recurring_errors;
		}
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_orders.html");
	$t->set_var("date_edit_format", join("", $date_edit_format));

	// check statuses and their access levels
	$see_statuses = array();
	$search_statuses = array();
	$see_statuses_ids = array();
	$set_statuses = array(array("", ""));
	$set_statuses_ids = array();
	$sql = " SELECT * FROM " . $table_prefix . "order_statuses ";
	$sql.= " WHERE is_active=1 ORDER BY status_order, status_id";
	$db->query($sql);
	while ($db->next_record()) {
		$row_status_id = $db->f("status_id");
		$status_name = get_translation($db->f("status_name"));
		$strip_status = strip_tags($status_name);

		// check access levels
		$view_order_groups_all = $db->f("view_order_groups_all");
		$view_order_groups_ids = $db->f("view_order_groups_ids");
		$view_order_groups_ids = explode(",", $view_order_groups_ids);
		$set_status_groups_all = $db->f("set_status_groups_all");
		$set_status_groups_ids = $db->f("set_status_groups_ids");
		$set_status_groups_ids = explode(",", $set_status_groups_ids);
		if ($view_order_groups_all || in_array($privilege_id, $view_order_groups_ids)) {
			$see_statuses[] = array($row_status_id, $status_name);
			$search_statuses[] = array($row_status_id, $strip_status);
			$see_statuses_ids[] = $row_status_id;
		}
		if ($set_status_groups_all || in_array($privilege_id, $set_status_groups_ids)) {
			$set_statuses[] = array($row_status_id, $strip_status);
			$set_statuses_ids[] = $row_status_id;
		}
	}
		

	if ($operation == "update_status") {
		if (isset($permissions["update_orders"]) && $permissions["update_orders"] == 1) {
			if (strlen($orders_ids) && strlen($status_id) && in_array($status_id, $set_statuses_ids)) {
				$ids = explode(",", $orders_ids);
				for ($i = 0; $i < sizeof($ids); $i++) {
					update_order_status($ids[$i], $status_id, true, "", $status_error);
					if ($status_error) {
						$orders_errors .= $status_error . "<br>";
					}
				}
			}
		} else {
			$orders_errors .= va_message("NOT_ALLOWED_UPDATE_ORDERS_MSG");
		}
	} elseif ($operation == "remove_orders") {
		if (isset($permissions["remove_orders"]) && $permissions["remove_orders"] == 1) {
			remove_orders($orders_ids);
		} else {
			$orders_errors .= va_message("NOT_ALLOWED_REMOVE_ORDERS_MSG");
		}
	}

	// prepare list values 
	$countries = get_db_values("SELECT country_id, country_name FROM " . $table_prefix . "countries ORDER BY country_order, country_name ", array(array("", "")));
	$states = get_db_values("SELECT state_id, state_name FROM " . $table_prefix . "states ORDER BY state_name ", array(array("", "")));
	$cc_default_types = array(array("", ""), array("blank", va_message("WITHOUT_CARD_TYPE_MSG")));
	$credit_card_types = get_db_values("SELECT credit_card_id, credit_card_name FROM " . $table_prefix . "credit_cards ORDER BY credit_card_name", $cc_default_types);
	$payment_systems = get_db_values("SELECT payment_id, payment_name FROM " . $table_prefix . "payment_systems WHERE is_active=1 OR is_call_center=1 ORDER BY payment_order ", array(array("", "")));
	$export_options = array(array("", va_message("ALL_MSG")), array("1", va_message("EXPORTED_MSG")), array("0", va_message("NOT_EXPORTED_MSG")));
	$paid_options = array(array("", va_message("ALL_MSG")), array("1", va_message("PAID_MSG")), array("0", va_message("NOT_PAID_MSG")));
	if ($sitelist) {
		$sites = get_db_values("SELECT site_id, short_name FROM " . $table_prefix . "sites ORDER BY site_id ", array(array("", "")));
	}
	$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings WHERE setting_type='order_info' ";
	//$sql .= " AND setting_name LIKE '%country_id%'";		
	if ($multisites_version) {
		$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id,INTEGER) . ") ";
		$sql .= " ORDER BY site_id ASC ";
	}
	$db->query($sql);
	while ($db->next_record()) {
		$order_info[$db->f("setting_name")] = $db->f("setting_value");
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

	$stats = array(
		array("title" => va_message("TODAY_MSG"), "date_start" => $today_ts, "date_end" => $today_ts),
		array("title" => va_message("YESTERDAY_MSG"), "date_start" => $yesterday_ts, "date_end" => $yesterday_ts),
		array("title" => va_message("LAST_SEVEN_DAYS_MSG"), "date_start" => $week_ts, "date_end" => $today_ts),
		array("title" => va_message("THIS_MONTH_MSG"), "date_start" => $month_ts, "date_end" => $today_ts),
		array("title" => va_message("LAST_MONTH_MSG"), "date_start" => $last_month_ts, "date_end" => $last_month_end),
	);

	// get orders stats
	for ($i = 0; $i < sizeof($see_statuses); $i++) {
		$status_id = $see_statuses[$i][0];
		$status_name = $see_statuses[$i][1];

		$t->set_var("status_id",   $status_id);
		$t->set_var("status_name", get_translation($status_name));

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
				$period_orders = "<a href=\"admin_orders.php?s_os=".$status_id."&s_sd=".va_date($date_edit_format, $start_date)."&s_ed=".va_date($date_edit_format, $end_date)."\"><b>" . $period_orders."</b></a>";
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

	$t->set_var("admin_orders_href", "admin_orders.php");
	$t->set_var("admin_order_href",  $order_details_site_url . "admin_order.php");
	$t->set_var("admin_invoice_html_href","admin_invoice_html.php");
	$t->set_var("admin_invoice_pdf_href","admin_invoice_pdf.php");
	$t->set_var("admin_href",        "admin.php");
	$t->set_var("admin_import_href", "admin_import.php");
	$t->set_var("admin_export_href", "admin_export.php");
	$t->set_var("admin_invoice_pdf_href", "admin_invoice_pdf.php");
	$t->set_var("admin_packing_pdf_href", "admin_packing_pdf.php");
	$t->set_var("admin_orders_bom_pdf_href", "admin_orders_bom_pdf.php");
	$t->set_var("admin_orders_shipment_href", "admin_orders_shipment.php");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_orders.php");
	$s->set_parameters(false, true, true, false);
	$s->set_default_sorting(1, "desc");
	$s->set_sorter(va_message("ORDER_NUMBER_COLUMN"), "sorter_id", "1", "o.order_id");
	$s->set_sorter(va_message("ORDER_ADDED_COLUMN"), "sorter_date", "2", "o.order_placed_date");
	$s->set_sorter(va_message("STATUS_MSG"), "sorter_status", "3", "os.status_order", "os.status_order, o.order_status", "os.status_order DESC, o.order_status DESC");
	$s->set_sorter(va_message("ADMIN_ORDER_TOTAL_MSG"), "sorter_total", "4", "o.order_total");
	if (get_setting_value($order_info, "show_delivery_country_id", 0) == 1) {
		$s->set_sorter(va_message("EMAIL_TO_MSG"), "sorter_ship_to", "5", "o.delivery_country_id");
	} else {
		$s->set_sorter(va_message("EMAIL_TO_MSG"), "sorter_ship_to", "5", "o.country_id");
	}
	$s->set_sorter(va_message("SITE_NAME_MSG"), "sorter_site_name", "6", "sti.short_name");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_orders.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$where = "";
	$r = new VA_Record($table_prefix . "orders");
	$r->add_textbox("s_on", TEXT, va_message("ORDER_NUMBER_MSG"));
	$r->change_property("s_on", TRIM, true);
	$r->add_textbox("s_uid", INTEGER);
	$r->change_property("s_uid", TRIM, true);
	$r->add_textbox("s_ne", TEXT);
	$r->change_property("s_ne", TRIM, true);
	$r->add_textbox("s_ph", TEXT);
	$r->change_property("s_ph", TRIM, true);
	$r->add_textbox("s_kw", TEXT);
	$r->change_property("s_kw", TRIM, true);
	$r->add_textbox("s_sd", DATE, va_message("FROM_DATE_MSG"));
	$r->change_property("s_sd", VALUE_MASK, $date_edit_format);
	$r->change_property("s_sd", TRIM, true);
	$r->add_textbox("s_ed", DATE, va_message("END_DATE_MSG"));
	$r->change_property("s_ed", VALUE_MASK, $date_edit_format);
	$r->change_property("s_ed", TRIM, true);		
	$r->add_select("s_os", TEXT, $search_statuses);
	$r->add_select("s_ci", TEXT, $countries);
	$r->add_select("s_si", TEXT, $states);
	if (count($payment_systems) > 2) {
		$r->add_select("s_ps", TEXT, $payment_systems);
	}
	$r->add_select("s_cct", TEXT, $credit_card_types);
	$r->add_select("s_ex", TEXT, $export_options);
	$r->add_select("s_pd", TEXT, $paid_options);
	if ($sitelist) {
		$r->add_select("s_sti", TEXT, $sites);
	}
	$r->get_form_parameters();
	// convert multi values s_os parameter
	$s_os = $r->get_value("s_os");
	if (!is_array($s_os) && strlen($s_os)) {
		$s_os = explode(",", $s_os);
		$r->set_value("s_os", $s_os);
	}
	$r->validate();

	$access_where = ""; $product_search = false;
	// build access where accordingly to administrator access levels
	if (strlen($access_where)) { $access_where.= " AND "; }
	if (is_array($see_statuses_ids) && sizeof($see_statuses_ids) > 0) {
		$access_where .= " (os.status_id IS NULL OR o.order_status IN (" . $db->tosql($see_statuses_ids, INTEGERS_LIST) . ")) ";
	} else {
		$access_where .= " (os.status_id IS NULL OR o.order_status IS NULL) ";
	}
	if (!$access_all_user_types || !$access_unreg_users) {
		if (strlen($access_where)) { $access_where .= " AND "; }
		$users_where = "";
		if ($access_unreg_users) {
			$users_where .= " o.user_type_id=0 OR o.user_type_id IS NULL ";
		} else if ($access_all_user_types) {            
			$users_where .= " o.user_type_id<>0 AND o.user_type_id IS NOT NULL ";
		}
		if (!$access_all_user_types && strlen($access_user_types)) {
			if ($users_where) { $users_where .= " OR "; }
			$users_where .= " o.user_type_id IN (" . $db->tosql($access_user_types, INTEGERS_LIST) . ")";
		}

		if ($users_where) {
			$access_where .= " (".$users_where.")";
		} else {
			$access_where .= " 1<>1 "; // no users groups selected
		}
	}
	$where = $access_where; $sub_join = ""; $sub_where = "";
	if (!$r->errors) {
		if (!$r->is_empty("s_on")) {
			$s_on = $r->get_value("s_on");
			if (strlen($where)) { $where .= " AND "; }
			if (preg_match("/^(\d+)(,\d+)*$/", $s_on))	{
				$where .= " (o.order_id IN (" . $s_on . ") ";
				$where .= " OR o.invoice_number=" . $db->tosql($s_on, TEXT);
				$where .= " OR o.transaction_id=" . $db->tosql($s_on, TEXT) . ") ";
			} else {
				$where .= " (o.invoice_number=" . $db->tosql($s_on, TEXT);
				$where .= " OR o.transaction_id=" . $db->tosql($s_on, TEXT) . ") ";
			}
		}

		if (!$r->is_empty("s_ne") || !$r->is_empty("s_uid")) {
			if (strlen($where)) { $where .= " AND "; }
			$s_ne = $r->get_value("s_ne");
			$s_uid = $r->get_value("s_uid");
			$s_ne_sql = $db->tosql($s_ne, TEXT, false);
			if (preg_match(EMAIL_REGEXP, $s_ne)) {
				$where .= " (o.email=" . $db->tosql($s_ne, TEXT);
				$where .= " OR o.delivery_email=" . $db->tosql($s_ne, TEXT);
				if (strlen($s_uid)) {
					$where .= " OR o.user_id=" . $db->tosql($s_uid, INTEGER);
				}
				$where .= ") ";
			} else if (strlen($s_ne)) {
				$where .= " (o.email LIKE '%" . $s_ne_sql . "%'";
				$where .= " OR o.delivery_email LIKE '%" . $s_ne_sql . "%'"; 
				$where .= " OR o.name LIKE '%" . $s_ne_sql . "%'";
				$where .= " OR o.delivery_name LIKE '%" . $s_ne_sql . "%'";
				if (strlen($s_uid)) {
					$where .= " OR o.user_id=" . $db->tosql($s_uid, INTEGER);
				}
				$name_parts = explode(" ", $s_ne, 2);
				if (sizeof($name_parts) == 1) {
					$where .= " OR o.first_name LIKE '%" . $s_ne_sql . "%'";
					$where .= " OR o.last_name LIKE '%" . $s_ne_sql . "%'";
					$where .= " OR o.delivery_first_name LIKE '%" . $s_ne_sql . "%'";
					$where .= " OR o.delivery_last_name LIKE '%" . $s_ne_sql . "%'";
				} else {
					$where .= " OR (o.first_name LIKE '%" . $db->tosql($name_parts[0], TEXT, false) . "%' ";
					$where .= " AND o.last_name LIKE '%" . $db->tosql($name_parts[1], TEXT, false) . "%') ";
					$where .= " OR (o.delivery_first_name LIKE '%" . $db->tosql($name_parts[0], TEXT, false) . "%' ";
					$where .= " AND o.delivery_last_name LIKE '%" . $db->tosql($name_parts[1], TEXT, false) . "%') ";
				}
				$where .= ") ";
			} else {
				$where .= " o.user_id=" . $db->tosql($s_uid, INTEGER);
			}
		}

		if (!$r->is_empty("s_ph")) {
			if (strlen($where)) { $where .= " AND "; }
			$s_ph = $r->get_value("s_ph");
			$s_ph_sql = $db->tosql($s_ph, TEXT, false);
			$where .= " (o.phone LIKE '%" . $s_ph_sql . "%'";
			$where .= " OR o.daytime_phone LIKE '%" . $s_ph_sql . "%'";
			$where .= " OR o.evening_phone LIKE '%" . $s_ph_sql . "%'";
			$where .= " OR o.cell_phone LIKE '%" . $s_ph_sql . "%'";
			$where .= " OR o.delivery_phone LIKE '%" . $s_ph_sql . "%'";
			$where .= " OR o.delivery_daytime_phone LIKE '%" . $s_ph_sql . "%'";
			$where .= " OR o.delivery_evening_phone LIKE '%" . $s_ph_sql . "%'";
			$where .= " OR o.delivery_cell_phone LIKE '%" . $s_ph_sql . "%'";
			$where .= ") ";
		}


		if (!$r->is_empty("s_kw")) {
			$product_search = true;
			if (strlen($where)) { $where .= " AND "; }
			$where .= " (oi.item_name LIKE '%" . $db->tosql($r->get_value("s_kw"), TEXT, false) . "%'";
			$where .= " OR oi.item_properties LIKE '%" . $db->tosql($r->get_value("s_kw"), TEXT, false) . "%'";
			$where .= " OR ois.serial_number=" . $db->tosql($r->get_value("s_kw"), TEXT);
			$where .= " OR osa.generation_key=" . $db->tosql($r->get_value("s_kw"), TEXT);
			$where .= " OR osa.activation_key=" . $db->tosql($r->get_value("s_kw"), TEXT);
			$where .= " OR oi.item_code=" . $db->tosql($r->get_value("s_kw"), TEXT);
			$where .= " OR oi.manufacturer_code=" . $db->tosql($r->get_value("s_kw"), TEXT);
			$where .= " OR o.shipping_type_desc LIKE '%" . $db->tosql($r->get_value("s_kw"), TEXT, false) . "%')";
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

		$t->set_var("status_select_style", "");
		$t->set_var("status_checkboxes_style", "style='display:none;'");
		if (!$r->is_empty("s_os")) {
			$s_os = $r->get_value("s_os");
			if (strlen($where)) { $where .= " AND "; }			
			$where .= " o.order_status IN (" . $db->tosql($s_os, INTEGERS_LIST) . ")";
		} else if ($r->is_empty("s_on")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " (os.is_list=1 OR os.is_list IS NULL) ";
		}		

		if (!$r->is_empty("s_ci")) {
			if ($order_info["show_delivery_country_id"] == 1) {
				if (strlen($where)) { $where .= " AND "; }
				$where .= " o.delivery_country_id=" . $db->tosql($r->get_value("s_ci"), INTEGER);
			} elseif ($order_info["show_country_id"] == 1) {
				if (strlen($where)) { $where .= " AND "; }
				$where .= " o.country_id=" . $db->tosql($r->get_value("s_ci"), INTEGER);
			} 
		}

		if (!$r->is_empty("s_si")) {
			if ($order_info["show_delivery_state_id"] == 1) {
				if (strlen($where)) { $where .= " AND "; }
				$where .= " o.delivery_state_id=" . $db->tosql($r->get_value("s_si"), INTEGER);
			} elseif ($order_info["show_state_id"] == 1) {
				if (strlen($where)) { $where .= " AND "; }
				$where .= " o.state_id=" . $db->tosql($r->get_value("s_si"), INTEGER);
			} 
		}

		if (!$r->is_empty("s_ps")) {
			if (strlen($where)) { $where .= " AND "; }
			$s_sti = $r->get_value("s_ps");
			$where .= " o.payment_id=" . $db->tosql($r->get_value("s_ps"), INTEGER);
		}

		if (!$r->is_empty("s_cct")) {
			if (strlen($where)) { $where .= " AND "; }
			if ($r->get_value("s_cct") == "blank") {
				$where .= " o.cc_type IS NULL ";
			} else {
				$where .= " o.cc_type=" . $db->tosql($r->get_value("s_cct"), INTEGER);
			}
		}

		if (!$r->is_empty("s_ex")) {
			if (strlen($where)) { $where .= " AND "; }
			$s_ex = $r->get_value("s_ex");
			$where .= ($s_ex == 1) ? " o.is_exported=1 " : " (o.is_exported<>1 OR o.is_exported IS NULL) ";
		}

		if (!$r->is_empty("s_pd")) {
			if (strlen($where)) { $where .= " AND "; }
			$s_pd = $r->get_value("s_pd");
			$where .= ($s_pd == 1) ? " os.paid_status=1 " : " (os.paid_status=0 OR os.paid_status IS NULL) ";
		}

		if (!$r->is_empty("s_sti")) {
			if (strlen($where)) { $where .= " AND "; }
			$s_sti = $r->get_value("s_sti");
			$where .= " o.site_id=" . $db->tosql($r->get_value("s_sti"), INTEGER);
		}
	}
	$r->set_form_parameters();
	if (!$r->is_empty("s_sd") || !$r->is_empty("s_ed")  || !$r->is_empty("s_os") 
		|| !$r->is_empty("s_uid") || !$r->is_empty("s_ph") || !$r->is_empty("s_kw")
		|| !$r->is_empty("s_ci") || !$r->is_empty("s_si") 
		|| !$r->is_empty("s_ps") || !$r->is_empty("s_cct") 
		|| !$r->is_empty("s_ex") || !$r->is_empty("s_pd") || !$r->is_empty("s_sti") 
	) {
		$t->set_var("search_advanced_class", "expand-open");
	}
		
	$where_sql = ""; 
	if (strlen($where)) {
		$where_sql = " WHERE " . $where;
	}

	set_options($set_statuses, "status_id", "status_id");

	// set up variables for navigator
	if ($product_search) {
		$total_records = 0;
		$sql  = " SELECT COUNT(*) FROM (SELECT o.order_id FROM ((((" . $table_prefix . "orders o ";
		$sql .= " INNER JOIN " . $table_prefix . "orders_items oi ON o.order_id=oi.order_id)";
		$sql .= " LEFT JOIN " . $table_prefix . "orders_items_serials ois ON o.order_id=ois.order_id)";
		$sql .= " LEFT JOIN " . $table_prefix . "orders_serials_activations osa ON o.order_id=osa.order_id)";
		$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
		$sql .= $where_sql;
		$sql .= " GROUP BY o.order_id) count_sql ";
		$db->query($sql);
		$db->next_record();
		$total_records = $db->f(0);
	} else {
		$sql  = " SELECT COUNT(*) FROM (" . $table_prefix . "orders o ";
		$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
		$sql .= $where_sql;
		$db->query($sql);
		$db->next_record();
		$total_records = $db->f(0);
	}

	$records_per_page = set_recs_param("admin_orders.php");
	$pages_number = 10;

	$countries_ids = array(); $states_ids = array(); $orders_ips = array(); $black_ips = array();
	if ($default_country_id) { $countries_ids[$default_country_id] = $default_country_id; }
	if ($default_state_id) { $states_ids[$default_state_id] = $default_state_id; }
	$orders = array(); $orders_ids = array(); 

	$n->set_parameters(true, true, false);
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = " SELECT o.order_id, o.order_placed_date,  o.goods_total, o.order_total, o.remote_address, ";
	$sql .= " os.status_name, os.admin_order_class, ";
	$sql .= " o.name, o.first_name, o.last_name, o.country_id, o.state_id, ";
	$sql .= " o.delivery_name, o.delivery_first_name, o.delivery_last_name, o.delivery_country_id, o.delivery_state_id, ";
	$sql .= " o.currency_code, o.currency_rate, c.symbol_right, c.symbol_left, c.decimals_number, c.decimal_point, c.thousands_separator ";
	if($sitelist) {
		$sql .= ", sti.short_name ";
	}
	$sql .= " FROM ((((((" . $table_prefix . "orders o ";
	$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "currencies c ON o.currency_code=c.currency_code) ";
	if ($product_search) {
		$sql .= " INNER JOIN " . $table_prefix . "orders_items oi ON o.order_id=oi.order_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "orders_items_serials ois ON o.order_id=ois.order_id)";
		$sql .= " LEFT JOIN " . $table_prefix . "orders_serials_activations osa ON o.order_id=osa.order_id)";
	} else {
		$sql .= ")))";
	}
	if($sitelist) {
		$sql .= " LEFT JOIN " . $table_prefix . "sites sti ON sti.site_id=o.site_id)";
	} else {
		$sql .= " )";
	}
	$sql .= $where_sql;
	if ($product_search) {
		$sql .= " GROUP BY o.order_id, o.order_placed_date, os.status_name, os.admin_order_class, o.goods_total, o.order_total, o.name, o.delivery_name,o.remote_address, ";
		$sql .= " o.first_name, o.last_name, o.delivery_first_name, o.delivery_last_name, o.country_id, o.delivery_country_id, o.state_id, o.delivery_state_id, ";
		$sql .= " o.currency_code, o.currency_rate, c.symbol_right, c.symbol_left, c.decimals_number, c.decimal_point, c.thousands_separator ";
		if($sitelist) {
			$sql .= ", sti.short_name ";
		}
	}
	$sql .= $s->order_by;
	$db->query($sql);
	if ($db->next_record())
	{
		$admin_order = new VA_URL($order_details_site_url . "admin_order.php", false);
		$admin_order->add_parameter("s_on", REQUEST, "s_on");
		$admin_order->add_parameter("s_uid", REQUEST, "s_uid");
		$admin_order->add_parameter("s_ne", REQUEST, "s_ne");
		$admin_order->add_parameter("s_ph", REQUEST, "s_ph");
		$admin_order->add_parameter("s_kw", REQUEST, "s_kw");
		$admin_order->add_parameter("s_sd", REQUEST, "s_sd");
		$admin_order->add_parameter("s_ed", REQUEST, "s_ed");
		$admin_order->add_parameter("s_os", REQUEST, "s_os");
		$admin_order->add_parameter("s_ci", REQUEST, "s_ci");
		$admin_order->add_parameter("s_si", REQUEST, "s_si");
		$admin_order->add_parameter("s_ps", REQUEST, "s_ps");
		$admin_order->add_parameter("s_cct", REQUEST, "s_cct");
		$admin_order->add_parameter("s_ex", REQUEST, "s_ex");
		$admin_order->add_parameter("s_pd", REQUEST, "s_pd");
		$admin_order->add_parameter("s_sti", REQUEST, "s_sti");
		$admin_order->add_parameter("page", REQUEST, "page");
		$admin_order->add_parameter("sort_ord", REQUEST, "sort_ord");
		$admin_order->add_parameter("sort_dir", REQUEST, "sort_dir");
		$admin_order->add_parameter("order_id", DB, "order_id");

		$order_index = 0;
		do
		{
			// init variables
			$classes = array();
			//$order_index++;
			$order_id    = $db->f("order_id");
			$orders_ids[] = $order_id;
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
			if (!strlen($order_currency["decimals"])) { $order_currency["decimals"] = 2; }
			if (!strlen($order_currency["point"])) { $order_currency["point"] = "."; }
			// check name from personal details
			$user_name = $db->f("name");
			if(!strlen($user_name)) { $user_name = trim($db->f("first_name")." ".$db->f("last_name")); }
			// check name from delivery details
			if(!strlen($user_name)) { $user_name = $db->f("delivery_name"); }
			if(!strlen($user_name)) { $user_name = trim($db->f("delivery_first_name")." ".$db->f("delivery_last_name")); }

			$order_placed_date = $db->f("order_placed_date", DATETIME);
			$order_placed_date = va_date($datetime_show_format, $order_placed_date);

			$delivery_country_id = $db->f("delivery_country_id");
			$delivery_state_id = $db->f("delivery_state_id");
			$country_id = $db->f("country_id");
			$state_id = $db->f("state_id");
			if ($delivery_country_id) { $countries_ids[$delivery_country_id] = $delivery_country_id; }
			if ($country_id) { $countries_ids[$country_id] = $country_id; }
			if ($delivery_state_id) { $states_ids[$delivery_state_id] = $delivery_state_id; }
			if ($state_id) { $states_ids[$state_id] = $state_id; }

			if (get_setting_value($order_info, "show_delivery_country_id", 0) == 1) {
				$country_id = $db->f("delivery_country_id");
				$state_id = $db->f("delivery_state_id");
			} elseif (get_setting_value($order_info, "show_country_id", 0) == 1) {
				$country_id = $db->f("country_id");
				$state_id = $db->f("state_id");
			} else {
				$country_id = $default_country_id;
				$state_id = $default_state_id;
			}
			$status_name = get_translation($db->f("status_name"));
			$admin_order_url   = $admin_order->get_url();
			$remote_address = $db->f("remote_address");
			if ($remote_address) { $orders_ips[$remote_address] = $remote_address; }
			$site_name = $db->f("short_name");
			$admin_order_class = $db->f("admin_order_class");
			if ($admin_order_class) {
				$classes[] = $admin_order_class;
			}

			//$orders[] = array($order_id, $order_total, $user_name, $order_placed_date, $status_name, $country_id, $state_id, $admin_order_url, $remote_address, $order_currency, $site_name);
			$orders[$order_id] = array(
				"total" => $order_total,
				"name" => $user_name, 
				"placed_date" => $order_placed_date, 
				"status" => $status_name, 
				"country_id" => $country_id, 
				"state_id" => $state_id, 
				"order_url" => $admin_order_url, 
				"ip" => $remote_address, 
				"currency" => $order_currency, 
				"site" => $site_name,
				"classes" => $classes,
				"shipments" => array(),
			);
		} while ($db->next_record());
	}

	// check shippments for orders
	$shipping_row_class = "";
	if (is_array($orders_ids) && count($orders_ids)) {
		$sql  = " SELECT os.order_id, os.shipping_desc, os.shipping_cost, ";	
		$sql .= " os.order_items_ids, sm.admin_order_class AS module_class, st.admin_order_class AS method_class ";
		$sql .= " FROM " . $table_prefix . "shipping_modules sm ";
		$sql .= " INNER JOIN " . $table_prefix . "shipping_types st ON sm.shipping_module_id=st.shipping_module_id ";
		$sql .= " INNER JOIN " . $table_prefix . "orders_shipments os ON st.shipping_type_id=os.shipping_id ";
		$sql .= " WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")";
		$db->query($sql);
		while($db->next_record()) {	
			$order_id = $db->f("order_id");
			$shipping_desc = $db->f("shipping_desc");
			$shipping_cost = $db->f("shipping_cost");
			$orders[$order_id]["shipments"][] = array(
				"desc" => $shipping_desc,
				"cost" => $shipping_cost,
			);
			$module_class = $db->f("module_class");
			if ($module_class) {
				$orders[$order_id]["classes"][] = $module_class;
			}
			$method_class = $db->f("method_class");
			if ($method_class) {
				$orders[$order_id]["classes"][] = $method_class;
			}
		}
	}

	// get countries and states for orders
	$countries = array(); $states = array();
	if (count($countries_ids)) {
		$sql  = " SELECT * FROM " . $table_prefix . "countries ";
		$sql .= " WHERE country_id IN (" . $db->tosql(array_keys($countries_ids), INTEGERS_LIST) . ")";
		$db->query($sql);
		while ($db->next_record()) {
			$country_id = $db->f("country_id");
			$country_name = get_translation($db->f("country_name"));
			$country_code = $db->f("country_code");
			$countries[$country_id] = array(
				"code" => $country_code, 
				"name" => $country_name, 
			);
		}
	}

	if (count($states_ids)) {
		$sql  = " SELECT * FROM " . $table_prefix . "states ";
		$sql .= " WHERE state_id IN (" . $db->tosql(array_keys($states_ids), INTEGERS_LIST) . ")";
		$db->query($sql);
		while ($db->next_record()) {
			$state_id = $db->f("state_id");
			$state_name = $db->f("state_name");
			$state_code = $db->f("state_code");
			$states[$state_id] = array(
				"code" => $state_code, 
				"name" => $state_name, 
			);
		}
	}

	$black_ips = blacklist_check("orders", array_keys($orders_ips));

	$colspan = 9;
	if ($sitelist) {
		$colspan++;	
	}
	$t->set_var("colspan", $colspan);
	if (count($orders) > 0)
	{
		$order_index = 0;
		if ($sitelist) {
			$t->parse("site_name_header", false);
		}
 		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		foreach ($orders as $order_id => $order_data) {
		//for ($i = 0; $i < sizeof($orders); $i++) {
			//list($order_id, $order_total, $user_name, $order_placed_date, $status_name, $country_id, $state_id, $admin_order_url, $remote_address, $order_currency, $site_name) = $orders[$i];
			$order_index++;

			$order_total = $order_data["total"];
			$user_name = $order_data["name"];
			$order_placed_date = $order_data["placed_date"];
			$status_name = $order_data["status"];
			$country_id = $order_data["country_id"];
			$state_id = $order_data["state_id"];
			$admin_order_url = $order_data["order_url"];
			$remote_address = $order_data["ip"];
			$order_currency = $order_data["currency"];
			$site_name = $order_data["site"];
			$classes = $order_data["classes"];
			$shipments = $order_data["shipments"];


			$ship_to = "";
			if ($country_id) {
				$ship_to = $countries[$country_id]["code"];
			}
			if ($state_id && isset($states[$state_id])) {
				if ($ship_to) {
					$ship_to .= "," . $states[$state_id]["code"]; ;
				} else {
					$ship_to  = $states[$state_id]["code"];
				}
			}

			$t->set_var("order_index", $order_index);
			$t->set_var("order_id", $order_id);
			$t->set_var("user_name", htmlspecialchars($user_name));
			$t->set_var("order_placed_date", $order_placed_date);

			$t->set_var("order_status", $status_name);

			$t->set_var("order_total", currency_format($order_total, $order_currency));
			$t->set_var("ship_to", $ship_to);
			$t->set_var("admin_order_url", $admin_order_url);
			
			if ($sitelist) {
				$t->set_var("site_name", $site_name);
				$t->parse("site_name_block", false);
			}

			if (isset($black_ips[$remote_address]) && $black_ips[$remote_address]["rule"] == "blocked") {
				$classes[] = "data-blocked";
			} else if (isset($black_ips[$remote_address]) && $black_ips[$remote_address]["rule"] == "warning") {
				$classes[] = "data-warning";
			} else {
				$classes[] = ($order_index % 2 == 0) ? "row1" : "row2";
			}
			$t->set_var("row_style", implode(" ", $classes));

			// start parsing small cart preview
			$t->set_var("order_items", "");
			$total_quantity = 0;
			$total_price = 0;
			$sql  = " SELECT oi.item_name, oi.quantity, os.status_name, oi.price ";
			$sql .= " FROM (" . $table_prefix . "orders_items oi ";
			$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON oi.item_status=os.status_id) ";
			$sql .= " WHERE oi.order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$item_name = get_translation($db->f("item_name"));
				$item_status = get_translation($db->f("status_name"));
				if (function_exists("mb_strlen")) {
					if (mb_strlen($item_name) > 30) {
						$item_name = mb_substr($item_name, 0, 30, "UTF-8") . "...";
					}
				} else {
					if (strlen($item_name) > 30) {
						$item_name = substr($item_name, 0, 30) . "...";
					}
				}
				$quantity = doubleval($db->f("quantity"));
				$price = doubleval($db->f("price"));

				$total_quantity += $quantity;
				$total_price += ($price * $quantity);

				$t->set_var("item_name", htmlspecialchars($item_name));
				$t->set_var("item_status", htmlspecialchars($item_status));
				$t->set_var("quantity",  $quantity);
				$t->set_var("price", currency_format($price, $order_currency));
				$t->parse("order_items", true);
			}
			// start parsing small cart preview
			$t->set_var("order_shipments", "");
			$sql  = " SELECT os.shipping_code, os.shipping_desc, os.shipping_cost ";
			$sql .= " FROM " . $table_prefix . "orders_shipments os ";
			$sql .= " WHERE os.order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$shipping_code = $db->f("shipping_code");
				$shipping_desc = get_translation($db->f("shipping_desc"));
				$shipping_cost = $db->f("shipping_cost");
				if (function_exists("mb_strlen")) {
					if (mb_strlen($shipping_desc) > 30) {
						$shipping_desc = mb_substr($shipping_desc, 0, 30, "UTF-8") . "...";
					}
				} else {
					if (strlen($shipping_desc) > 30) {
						$shipping_desc = substr($shipping_desc, 0, 30) . "...";
					}
				}

				$t->set_var("shipping_code", htmlspecialchars($shipping_code));
				$t->set_var("shipping_desc", htmlspecialchars($shipping_desc));
				$t->set_var("shipping_cost", currency_format($shipping_cost, $order_currency));
				$t->parse("order_shipments", true);
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

	$t->set_var("page", $page_number);
	$s_os = $r->get_value("s_os");
	if (is_array($s_os)) {
		$s_os = implode(",", $s_os);
	}
	$t->set_var("s_os_search", htmlspecialchars($s_os));
	$t->set_var("s_ci_search", htmlspecialchars($r->get_value("s_ci")));
	$t->set_var("s_si_search", htmlspecialchars($r->get_value("s_si")));
	$t->set_var("s_ex_search", htmlspecialchars($r->get_value("s_ex")));
	$t->set_var("s_pd_search", htmlspecialchars($r->get_value("s_pd")));

	if (sizeof($orders) > 0) 
	{
		if (isset($permissions["update_orders"]) && $permissions["update_orders"] == 1) {
			$t->parse("update_status", false);
		}
		if (isset($permissions["remove_orders"]) && $permissions["remove_orders"] == 1) {
			$t->parse("remove_orders_button", false);
		}
	}

	if (strlen($orders_errors)) {
		$t->set_var("errors_list", $orders_errors);
		$t->parse("orders_errors", false);
	}

	if (strlen($recurring_success)) {
		$t->set_var("messages_list", $recurring_success);
		$t->parse("orders_messages", false);
	}


	if (strlen($where) && $total_records > 0) {
		$admin_export_filtered_url = new VA_URL("admin_export.php", true);
		$admin_export_filtered_url->add_parameter("table", CONSTANT, "orders");
		$admin_export_filtered_url->add_parameter("type", CONSTANT, "filtered");
		$t->set_var("admin_export_filtered_url", $admin_export_filtered_url->get_url());
		$t->set_var("total_filtered", $total_records);
		$t->parse("export_filtered", false);
	}
  
	if (isset($permissions["create_orders"]) && $permissions["create_orders"] == 1) {
		$t->parse("generate_recurring", false);
	}
	
	$sql  = " SELECT exported_order_id FROM " . $table_prefix . "admins ";
	$sql .= " WHERE admin_id=" . $db->tosql(get_session("session_admin_id"), INTEGER);
	$exported_order_id = intval(get_db_value($sql));

	$sql  = " SELECT COUNT(*) FROM (" . $table_prefix . "orders o ";
	$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
	$sql .= " WHERE order_id>" . $db->tosql($exported_order_id, INTEGER);
	if ($access_where) { $sql .= " AND " . $access_where; }
	$total_new = get_db_value($sql);
	if ($total_new > 0) {
		$t->set_var("exported_order_id", urlencode($exported_order_id));
		$t->set_var("total_new", $total_new);
		$t->parse("export_new", false);
	}

	$sql  = " SELECT MAX(order_id) FROM (" . $table_prefix . "orders o ";
	$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
	if ($access_where) { $sql .= " WHERE " . $access_where; }
	$max_order_id = get_db_value($sql);

	if ($max_order_id > get_session("session_last_order_id") && $max_order_id > get_session("session_max_order_id")) {
		set_session("session_max_order_id", $max_order_id);
		$sql = " UPDATE " . $table_prefix . "admins SET last_order_id=" . $db->tosql($max_order_id, INTEGER);
		$sql .= " WHERE admin_id=" . $db->tosql(get_session("session_admin_id"), INTEGER);
		$db->query($sql);
	}

	if ($sitelist) {
		$t->parse('sitelist');		
	}
	$t->pparse("main");
