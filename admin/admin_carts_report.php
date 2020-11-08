<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_carts_report.php                                   ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

	
	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ($root_folder_path . "includes/sorter.php");
	include_once ($root_folder_path . "includes/record.php");
	include_once ($root_folder_path . "includes/shopping_cart.php");
	include_once ($root_folder_path . "includes/order_items.php");
	include_once ($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("orders_stats");

	$reminders_report = false;
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_carts_report.html");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_orders_href", "admin_orders.php");
	$t->set_var("admin_orders_tax_report_href", "admin_orders_tax_report.php");
	$t->set_var("admin_orders_products_report_href", "admin_orders_products_report.php");

	// prepare list values
	$groupby = array(array("1", YEAR_MSG), array("2", MONTH_MSG), array("3", WEEK_MSG), array("4", DAY_MSG));
	$periods = array(array("", ""), array("1", TODAY_MSG), array("2", YESTERDAY_MSG), array("3", LAST_7DAYS_MSG), array("4", THIS_MONTH_MSG), array("5", LAST_MONTH_MSG), array("6", THIS_QUARTER_MSG), array("7", THIS_YEAR_MSG));

	// prepare dates for stats
	$current_date = va_time();
	$cyear = $current_date[YEAR];
	$cmonth = $current_date[MONTH];
	$cday = $current_date[DAY];
	$today_ts = mktime(0, 0, 0, $cmonth, $cday, $cyear);
	$tomorrow_ts = mktime(0, 0, 0, $cmonth, $cday + 1, $cyear);
	$yesterday_ts = mktime(0, 0, 0, $cmonth, $cday - 1, $cyear);
	$week_ts = mktime(0, 0, 0, $cmonth, $cday - 6, $cyear);
	$month_ts = mktime(0, 0, 0, $cmonth, 1, $cyear);
	$last_month_start_ts = mktime(0, 0, 0, $cmonth - 1, 1, $cyear);
	$last_month_end_ts = mktime(0, 0, 0, $cmonth, 0, $cyear);
	$quarter_ts = mktime(0, 0, 0, intval(($cmonth - 1) / 3) * 3 + 1, 1, $cyear);
	$year_ts = mktime(0, 0, 0, 1, 1, $cyear);
	$today_date = va_date($date_edit_format, $today_ts);
	$tomorrow_date = va_date($date_edit_format, $tomorrow_ts);
	$yesterday_date = va_date($date_edit_format, $yesterday_ts);
	$week_start_date = va_date($date_edit_format, $week_ts);
	$month_start_date = va_date($date_edit_format, $month_ts);
	$last_month_start_date = va_date($date_edit_format, $last_month_start_ts);
	$last_month_end_date = va_date($date_edit_format, $last_month_end_ts);
	$quarter_start_date = va_date($date_edit_format, $quarter_ts);
	$year_start_date = va_date($date_edit_format, $year_ts);
	$yes_no_all = 
		array( 
			array(1, YES_MSG), array(0, NO_MSG), array("", ALL_MSG)
		);

	$sql  = " SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE (setting_type='order_recover') ";
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
		
	$t->set_var("date_edit_format", join("", $date_edit_format));
	$t->set_var("today_date", $today_date);
	$t->set_var("yesterday_date", $yesterday_date);
	$t->set_var("week_start_date", $week_start_date);
	$t->set_var("month_start_date", $month_start_date);
	$t->set_var("last_month_start_date", $last_month_start_date);
	$t->set_var("last_month_end_date", $last_month_end_date);
	$t->set_var("quarter_start_date", $quarter_start_date);
	$t->set_var("year_start_date", $year_start_date);

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_carts_report.php");
	$s->set_default_sorting(1, "asc");
	$s->set_sorter(PERIOD_MSG, "sorter_time", "1", "order_placed_date");

	$r = new VA_Record("");
	$r->add_hidden("s_form", INTEGER);
	$r->add_select("s_gr", INTEGER, $groupby);
	$r->add_select("s_tp", INTEGER, $periods);
	$r->add_textbox("s_sd", DATE, FROM_DATE_MSG);
	$r->change_property("s_sd", VALUE_MASK, $date_edit_format);
	$r->add_textbox("s_ed", DATE, END_DATE_MSG);
	$r->change_property("s_ed", VALUE_MASK, $date_edit_format);
	$r->get_form_parameters();
	$r->validate();
	if (!($r->get_value("s_form"))) {
		$r->set_value("s_gr", 2);
		if ($r->is_empty("s_sd") && $r->is_empty("s_ed")) {
			$r->set_value("s_tp", 7);
			$r->set_value("s_sd", va_time($year_ts));
			$r->set_value("s_ed", va_time($today_ts));
		}
	}
	$r->set_form_parameters();
	$s_gr = $r->get_value("s_gr");

	if (!strlen(get_param("filter")) && $r->get_value("s_form")) {
		$t->set_var("search_results", "");
		$t->pparse("main");
		exit;
	}

	$saved_where = ""; $retry_where = "";
	if (!$r->errors) 
	{
		if (!$r->is_empty("s_sd")) {
			if (strlen($saved_where)) { 
				$saved_where .= " AND "; 
				$retry_where .= " AND "; 
			}
			$saved_where .= " sc.cart_added >= " . $db->tosql($r->get_value("s_sd"), DATE);
			$retry_where .= " o.order_placed_date >= " . $db->tosql($r->get_value("s_sd"), DATE);
		}

		if (!$r->is_empty("s_ed")) {
			if (strlen($saved_where)) { 
				$saved_where .= " AND "; 
				$retry_where .= " AND "; 
			}
			$end_date = $r->get_value("s_ed");
			$day_after_end = mktime(0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
			$saved_where .= " sc.cart_added < " . $db->tosql($day_after_end, DATE);
			$retry_where .= " o.order_placed_date < " . $db->tosql($day_after_end, DATE);
		}
	}
	if (strlen($saved_where)) {
		$saved_where = " WHERE " . $saved_where;
		$retry_where = " WHERE " . $retry_where;
	}

	$carts = array(); $carts_sort = array();
	$sql  = " SELECT sc.cart_total, sc.cart_added, SUM(si.quantity) AS cart_quantity ";
	if ($reminders_report) {
		$sql .= " , sc.reminder_id ";
	}
	$sql .= " FROM " . $table_prefix . "saved_carts sc ";
	$sql .= " INNER JOIN " . $table_prefix . "saved_items si ON sc.cart_id=si.cart_id ";
	$sql .= $saved_where; 
	$sql .= " GROUP BY sc.cart_id, sc.cart_total, sc.cart_added ";
	if ($reminders_report) {
		$sql .= " , sc.reminder_id ";
	}
	$db->query($sql);
	while ($db->next_record()) {
		// Auto Carts 
		$reminder_id = $db->f("reminder_id");
		$cart_added = $db->f("cart_added", DATETIME);
		if ($reminder_id) {
			$cart_no = 0; $cart_total = 0; $cart_quantity = 0;
			$auto_cart_no = 1;
			$auto_cart_total = $db->f("cart_total");
			$auto_cart_quantity = $db->f("cart_quantity");
		} else {
			$auto_cart_no = 0; $auto_cart_total = 0; $auto_cart_quantity = 0;
			$cart_no = 1;
			$cart_total = $db->f("cart_total");
			$cart_quantity = $db->f("cart_quantity");
		}

		period_data($cart_added, $s_gr, $period_key, $period_sd, $period_ed);

		$carts_sort[$period_key] = $period_key;

		if (isset($carts[$period_key])) {
			$carts[$period_key]["saved_number"] += $cart_no;
			$carts[$period_key]["saved_quantity"] += $cart_quantity;
			$carts[$period_key]["saved_total"] += $cart_total;
			$carts[$period_key]["auto_saved_number"] += $auto_cart_no;
			$carts[$period_key]["auto_saved_quantity"] += $auto_cart_quantity;
			$carts[$period_key]["auto_saved_total"] += $auto_cart_total;
		} else {
			$carts[$period_key] = array(
				"start" => $period_sd,
				"end" => $period_ed,
				"saved_number" => $cart_no,
				"saved_total" => $cart_total,
				"saved_quantity" => $cart_quantity,
				"auto_saved_number" => $auto_cart_no,
				"auto_saved_total" => $auto_cart_total,
				"auto_saved_quantity" => $auto_cart_quantity,
				"orders_number" => 0,
				"orders_total" => 0,
				"orders_quantity" => 0,
				"auto_orders_number" => 0,
				"auto_orders_total" => 0,
				"auto_orders_quantity" => 0,
			);
		}
	}


	$sql  = " SELECT SUM(oi.price * oi.quantity) AS order_total, SUM(oi.quantity) AS order_quantity, o.order_placed_date ";
	if ($reminders_report) {
		$sql .= " , si.reminder_id ";
	}
	$sql .= " FROM " . $table_prefix . "orders_items oi ";
	$sql .= " INNER JOIN " . $table_prefix . "orders o ON o.order_id=oi.order_id ";
	$sql .= " INNER JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id ";
	$sql .= " INNER JOIN " . $table_prefix . "saved_items si ON si.cart_item_id=oi.cart_item_id ";
	$sql .= $retry_where; 
	$sql .= " AND oi.cart_item_id IS NOT NULL AND oi.cart_item_id > 0";
	$sql .= " AND os.paid_status=1 ";
	$sql .= " GROUP BY o.order_id, o.order_placed_date ";
	if ($reminders_report) {
		$sql .= " , si.reminder_id ";
	}
	$db->query($sql);
	while ($db->next_record()) {
		$reminder_id = $db->f("reminder_id");
		$order_added = $db->f("order_placed_date", DATETIME);
		if ($reminder_id) {
			$order_no = 0; $order_quantity = 0; $order_total = 0; 
			$auto_order_no = 1;
			$auto_order_quantity = $db->f("order_quantity");
			$auto_order_total = round($db->f("order_total"), 2);
		} else {
			$auto_order_no = 0; $auto_order_total = 0; $auto_order_quantity = 0; 
			$order_no = 1;
			$order_quantity = $db->f("order_quantity");
			$order_total = round($db->f("order_total"), 2);
		}


		period_data($order_added, $s_gr, $period_key, $period_sd, $period_ed);

		$carts_sort[$period_key] = $period_key;

		if (isset($carts[$period_key])) {
			$carts[$period_key]["orders_number"] += $order_no;
			$carts[$period_key]["orders_quantity"] += $order_quantity;
			$carts[$period_key]["orders_total"] += $order_total;
			$carts[$period_key]["auto_orders_number"] += $auto_order_no;
			$carts[$period_key]["auto_orders_quantity"] += $auto_order_quantity;
			$carts[$period_key]["auto_orders_total"] += $auto_order_total;
		} else {
			$carts[$period_key] = array(
				"start" => $period_sd,
				"end" => $period_ed,
				"saved_number" => 0,
				"saved_total" => 0,
				"saved_quantity" => 0,
				"auto_saved_number" => 0,
				"auto_saved_total" => 0,
				"auto_saved_quantity" => 0,
				"orders_number" => $order_no,
				"orders_quantity" => $order_quantity,
				"orders_total" => $order_total,
				"auto_orders_number" => $auto_order_no,
				"auto_orders_quantity" => $auto_order_quantity,
				"auto_orders_total" => $auto_order_total,
			);
		}
	}

	// sort carts data 
	array_multisort($carts_sort, SORT_ASC, $carts);

	if (sizeof($carts)) {
		$index = 0;

		$sum_saved_number = 0;
		$sum_saved_qty = 0;
		$sum_saved_total = 0;
		$sum_auto_saved_number = 0;
		$sum_auto_saved_qty = 0;
		$sum_auto_saved_total = 0;
		$sum_orders_number = 0;
		$sum_orders_qty = 0;
		$sum_orders_total = 0;
		$sum_auto_orders_number = 0;
		$sum_auto_orders_qty = 0;
		$sum_auto_orders_total = 0;

		foreach ($carts as $key => $cart_data) {
			$index++;
			$row_style = ($index % 2 == 0) ? "row1" : "row2";

			$period_sd = $cart_data["start"];
			$period_ed = $cart_data["end"];

			if ($s_gr == 1) { // year
				$time_period = va_date("YYYY", $period_sd);;
			} elseif ($s_gr == 2) { // month
				$time_period = va_date("MMMM, YYYY", $period_sd);
			} elseif ($s_gr == 3) { // week
				$time_period  = va_date("D MMM YYYY - ", $period_sd);
				$time_period .= va_date("D MMM YYYY", $period_ed);
			} elseif ($s_gr == 4) { // day
				$time_period = va_date("D MMM YYYY", $period_ed);
			}

			$saved_number = $cart_data["saved_number"];
			$saved_quantity = $cart_data["saved_quantity"];
			$saved_total = $cart_data["saved_total"];
			$auto_saved_number = $cart_data["auto_saved_number"];
			$auto_saved_quantity = $cart_data["auto_saved_quantity"];
			$auto_saved_total = $cart_data["auto_saved_total"];
			$orders_number = $cart_data["orders_number"];
			$orders_quantity = $cart_data["orders_quantity"];
			$orders_total = $cart_data["orders_total"];
			$auto_orders_number = $cart_data["auto_orders_number"];
			$auto_orders_quantity = $cart_data["auto_orders_quantity"];
			$auto_orders_total = $cart_data["auto_orders_total"];

			$sum_saved_number += $saved_number;
			$sum_saved_total += $saved_total;
			$sum_saved_qty += $saved_quantity;
			$sum_auto_saved_number += $auto_saved_number;
			$sum_auto_saved_total += $auto_saved_total;
			$sum_auto_saved_qty += $auto_saved_quantity;
			$sum_orders_number += $orders_number;
			$sum_orders_qty += $orders_quantity;
			$sum_orders_total += $orders_total;
			$sum_auto_orders_number += $auto_orders_number;
			$sum_auto_orders_qty += $auto_orders_quantity;
			$sum_auto_orders_total += $auto_orders_total;

			$t->set_var("row_style", $row_style);
			$t->set_var("time_period", $time_period);
			if ($reminders_report) {
				$t->set_var("saved_number", $saved_number . " / " . $auto_saved_number);
				$t->set_var("saved_quantity", $saved_quantity . " / " . $auto_saved_quantity);
				$t->set_var("saved_total", currency_format($saved_total) . " / " . currency_format($auto_saved_total));
				$t->set_var("orders_number", $orders_number . " / " . $auto_orders_number);
				$t->set_var("orders_quantity", $orders_quantity . " / " . $auto_orders_quantity);
				$t->set_var("orders_total", currency_format($orders_total) . " / " . currency_format($auto_orders_total));
			} else {
				$t->set_var("saved_number", $saved_number);
				$t->set_var("saved_quantity", $saved_quantity);
				$t->set_var("saved_total", currency_format($saved_total));
				$t->set_var("orders_number", $orders_number);
				$t->set_var("orders_quantity", $orders_quantity);
				$t->set_var("orders_total", currency_format($orders_total));
			}

			$t->parse("records", true);
		}

		if ($reminders_report) {
			$t->set_var("sum_saved_number", $sum_saved_number . " / " . $sum_auto_saved_number);
			$t->set_var("sum_saved_qty", $sum_saved_qty . " / " . $sum_auto_saved_qty);
			$t->set_var("sum_saved_total", currency_format($sum_saved_total) . " / " . currency_format($sum_auto_saved_total));
			$t->set_var("sum_orders_number", $sum_orders_number . " / " . $sum_auto_orders_number);
			$t->set_var("sum_orders_qty", $sum_orders_qty . " / " . $sum_auto_orders_qty);
			$t->set_var("sum_orders_total", currency_format($sum_orders_total) . " / " . currency_format($sum_auto_orders_total));
		} else {
			$t->set_var("sum_saved_number", $sum_saved_number);
			$t->set_var("sum_saved_qty", $sum_saved_qty);
			$t->set_var("sum_saved_total", currency_format($sum_saved_total));
			$t->set_var("sum_orders_number", $sum_orders_number);
			$t->set_var("sum_orders_qty", $sum_orders_qty);
			$t->set_var("sum_orders_total", currency_format($sum_orders_total));
		}
		$t->parse("summary", false);
		$t->parse("summary_bottom", false);

		$t->parse("titles", false);
		$t->parse("search_results", false);
	} else {
		$t->parse("no_records", false);
	}



/*

	$sql .= " COUNT(o.order_id) AS orders_qty, SUM(o.total_quantity) AS products_qty, SUM(o.goods_total) AS goods, ";
	$sql .= " SUM(o.order_total) AS sales, SUM(o.tax_total) AS tax, SUM(o.shipping_cost) AS shipping, ";
	$sql .= " SUM(o.shipping_excl_tax) AS sum_shipping_excl_tax,  ";
	$sql .= " SUM(o.total_discount) AS discount, SUM(o.total_buying) AS buying, ";
	$sql .= " SUM(o.goods_total - o.total_buying - o.total_discount) AS margin ";
	$sql .= " FROM " . $table_prefix . "orders o ";
	$sql .= $saved_where;
	$sql .= " GROUP BY ";
	$sql .= $s->order_by;
	$db->query($sql);
	if ($db->next_record()) {
		$order_index = 0;
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do {
			$order_index++;
			$year = intval($db->f("year"));
			if ($s_gr == 2) {
				$month = intval($db->f("month"));
			}
			if ($s_gr == 3) {
				$week = intval($db->f("week"));
			} elseif ($s_gr == 4) {
				$month = intval($db->f("month"));
				$day = intval($db->f("day"));
			}
			$orders_qty = intval($db->f("orders_qty"));
			$products_qty = intval($db->f("products_qty"));
			$goods = doubleval($db->f("goods"));
			$sales = doubleval($db->f("sales"));
			$tax = doubleval($db->f("tax"));
			$discount = doubleval($db->f("discount"));
			$shipping  = doubleval($db->f("sum_shipping_excl_tax"));
			$shipping += doubleval($db->f("shipping"));
			$buying = doubleval($db->f("buying"));
			$margin = doubleval($db->f("margin"));
			$margin_percent = ($goods != 0) ? number_format($margin / $goods * 100, 2) : 0;
			if ($s_gr == 1) {
				$t->set_var("time_period", $year . " " . YEAR_MSG);
			} elseif ($s_gr == 2) {
				$t->set_var("time_period", get_array_value($month, $months) . ", " . $year);
			} elseif ($s_gr == 3) {
				$t->set_var("time_period", $year . " " . YEAR_MSG . ", " . $week . WEEK_MSG);
			} elseif ($s_gr == 4) {
				$t->set_var("time_period", va_date($date_show_format_custom, mktime(0, 0, 0, $month, $day, $year)));
			}
			$t->set_var("orders_qty", $orders_qty);
			$t->set_var("products_qty", $products_qty);
			$t->set_var("goods", currency_format($goods));
			$t->set_var("sales", currency_format($sales));
			$t->set_var("tax", currency_format($tax));
			$t->set_var("discount", currency_format($discount));
			$t->set_var("shipping", currency_format($shipping));
			$t->set_var("buying", currency_format($buying));
			$t->set_var("margin", currency_format($margin));
			$t->set_var("margin_percent", $margin_percent);

			$row_style = ($order_index % 2 == 0) ? "row1" : "row2";
			$t->set_var("row_style", $row_style);
			$t->set_var("order_index", $order_index);

			if ($s_gr == 1) {
				$sd_period_ts = mktime(0, 0, 0, 1, 1, $year); // year start timestamp
				$ed_period_ts = mktime(0, 0, 0, 1, 0, $year + 1); // year end timestamp
			}
			else if ($s_gr == 2) {
				$sd_period_ts = mktime(0, 0, 0, $month, 1, $year); // month start timestamp
				$ed_period_ts = mktime(0, 0, 0, $month + 1, 0, $year); // month end timestamp
			}
			else if ($s_gr == 3) {
				$year_start_weekday = date("w", mktime(0, 0, 0, 1, 1, $year));
				if ($db_type == "postgre") $year_start_weekday--; // in Postgre week always starts from Monday
				if ($year_start_weekday >= 4) {
					$day_number = 7 * ($week + 1) - $year_start_weekday;
				}
				else {
					$day_number = 7 * $week - $year_start_weekday;
				}
				$sd_period_ts = mktime(0, 0, 0, 1, $day_number - 6, $year); // week start timestamp
				$ed_period_ts = mktime(0, 0, 0, 1, $day_number, $year); // week end timestamp
			}
			else if ($s_gr == 4) {
				$sd_period_ts = mktime(0, 0, 0, $month, $day, $year); // day start timestamp
				$ed_period_ts = $sd_period_ts; // day end timestamp
			}
			$t->set_var("s_sd_m", va_date($date_edit_format, $sd_period_ts));
			$t->set_var("s_ed_m", va_date($date_edit_format, $ed_period_ts));

			$date_period_start = va_date($date_show_format_custom, $sd_period_ts);
			$date_period_end = va_date($date_show_format_custom, $ed_period_ts);
			$period_start_time = va_time($sd_period_ts);
			$period_end_time = va_time($ed_period_ts);

			if ($s_gr == 3) {
				if ($period_start_time[YEAR] == $period_end_time[YEAR]) {
					if ($period_start_time[MONTH] != $period_end_time[MONTH]) {
						$t->set_var("time_period", intval($period_start_time[DAY]) . " " . $short_months[intval($period_start_time[MONTH]) - 1][1] . " - " . $date_period_end);
					}
					else{
						$t->set_var("time_period", intval($period_start_time[DAY]) . " - " . $date_period_end);
					}
				}
				else {
					$t->set_var("time_period", $date_period_start . " - " . $date_period_end);
				}
			}

			if (!$r->is_empty("s_sd")) {
				$start_date = $r->get_value("s_sd");
				$s_sd = va_date($date_show_format_custom, $start_date); // start date search param
				if ($s_sd != $date_period_start && $start_date[YEAR] == $year) {
					if ($s_gr == 1) {
						$t->set_var("time_period", $s_sd . " - " . $date_period_end);
						$t->set_var("s_sd_m", va_date($date_edit_format, $start_date));
					}
					else if ($s_gr == 2 && $start_date[MONTH] == $month) {
						$t->set_var("time_period", intval($start_date[DAY]) . " - " . $date_period_end);
						$t->set_var("s_sd_m", va_date($date_edit_format, $start_date));
					}
				}
			}
			if (!$r->is_empty("s_ed")) {
				$end_date = $r->get_value("s_ed");
				$s_ed = va_date($date_show_format_custom, $end_date); // end date search param
				if ($s_ed != $date_period_end && $end_date[YEAR] == $year &&
					(($s_gr == 1) || ($s_gr == 2 && $end_date[MONTH] == $month)))
				{
					if ($s_gr == 1) {
						if ($period_start_time[MONTH] == $end_date[MONTH]) {
							$t->set_var("time_period", intval($period_start_time[DAY]) . " - " . $s_ed);
						}
						else {
							$t->set_var("time_period", $date_period_start . " - " . $s_ed);
						}
					}
					else if ($s_gr == 2 && $end_date[MONTH] == $month) {
						$t->set_var("time_period", intval($period_start_time[DAY]) . " - " . $s_ed);
					}
					if (!$r->is_empty("s_sd")) {
						if ($s_sd != $date_period_start && $start_date[YEAR] == $year) {
							if ($s_gr == 1) {
								if ($start_date[MONTH] == $end_date[MONTH]) {
									$t->set_var("time_period", intval($start_date[DAY]) . " - " . $s_ed);
								} else {
									$t->set_var("time_period", $s_sd . " - " . $s_ed);
								}
							} elseif ($s_gr == 2 && $start_date[MONTH] == $month) {
								$t->set_var("time_period", intval($start_date[DAY]) . " - " . $s_ed);
							}
						}
					}
					$t->set_var("s_ed_m", va_date($date_edit_format, $end_date));
				}
			}

			$t->parse("records", true);

			$sum_orders_qty += $orders_qty;
			$sum_products_qty += $products_qty;
			$sum_goods += $goods;
			$sum_buying += $buying;
			$sum_sales += $sales;
			$sum_tax += $tax;
			$sum_discount += $discount;
			$sum_shipping += $shipping;
			$sum_margin += $margin;
			$sum_margin_percent = ($sum_goods != 0) ? number_format($sum_margin / $sum_goods * 100, 2) : 0;
		} while ($db->next_record());

		$t->set_var("sum_orders_qty", $sum_orders_qty);
		$t->set_var("sum_products_qty", $sum_products_qty);
		$t->set_var("sum_goods", currency_format($sum_goods));
		$t->set_var("sum_sales", currency_format($sum_sales));
		$t->set_var("sum_tax", currency_format($sum_tax));
		$t->set_var("sum_discount", currency_format($sum_discount));
		$t->set_var("sum_shipping", currency_format($sum_shipping));
		$t->set_var("sum_buying", currency_format($sum_buying));
		$t->set_var("sum_margin", currency_format($sum_margin));
		$t->set_var("sum_margin_percent", $sum_margin_percent);
		$t->parse("summary", false);
		$t->parse("summary_bottom", false);
	}
	else
	{
		$t->set_var("sorters", "");
		$t->set_var("records", "");
		$t->set_var("summary", "");
		$t->set_var("summary_bottom", "");
		$t->parse("no_records", false);
	}
*/

	$t->parse("search_results", false);
	$t->pparse("main");


	function period_data($date_added, $period_group, &$period_key, &$period_sd, &$period_ed)
	{
		$date_added_ts = va_timestamp($date_added);

		$year = $date_added[YEAR];
		$month = $date_added[MONTH];
		$day = $date_added[DAY];
		if ($period_group == 1) { // year
			$period_key = date("Y", $date_added_ts);
			$period_sd = mktime(0, 0, 0, 1, 1, $year); // year start timestamp
			$period_ed = mktime(0, 0, 0, 12, 31, $year); // year end timestamp
		} elseif ($period_group == 2) { // month
			$period_key = date("Ym", $date_added_ts);
			$period_sd = mktime(0, 0, 0, $month, 1, $year); // month start timestamp
			$period_ed = mktime(0, 0, 0, $month + 1, 0, $year); // month end timestamp
		} elseif ($period_group == 3) { // week
			$week_day = date("N", $date_added_ts);
			$period_sd = mktime(0, 0, 0, $month, $day-$week_day+1, $year); // week start timestamp
			$period_ed = mktime(0, 0, 0, $month, $day-$week_day+7, $year); // week end start timestamp
			$week_number = date("W", $date_added_ts);
			if ($week_number < 9) { $week_number = "0".$week_number; }
			$period_key = $year.$week_number;
		} elseif ($period_group == 4) { // day
			$period_key = date("Ymd", $date_added_ts);
			$period_sd = mktime(0, 0, 0, $month, $day, $year); // day start timestamp
			$period_ed = $period_sd; // day end timestamp
		}
	}

?>