<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_chart.php                                          ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path."includes/chart_functions.php");
	include_once("./admin_common.php");

	if (!strlen(get_session("session_admin_id")) || !strlen(get_session("session_admin_privilege_id"))) {
		echo SESSION_EXPIRED_MSG;
		return;
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("chart_block", "admin_block_chart.html");

	// prepare date for statistics
	$show_days = 28;
	$current_date = va_time();
	$cyear = $current_date[YEAR]; $cmonth = $current_date[MONTH]; $cday = $current_date[DAY]; 
	for ($i = $show_days; $i >= 0; $i--) {
		$cache = ($i != 0) ? true : false;
		$start_date = mktime (0, 0, 0, $cmonth, $cday - $i, $cyear);
		$end_date = mktime (0, 0, 0, $cmonth, $cday - $i + 1, $cyear);
		$chart_dates[] = array(
			"cache" => $cache,
			"date" => $start_date, 
			"start" => $start_date, 
			"end" => $end_date, 
			"x-text" => va_date("D MMM", $start_date),
		);
	}

	$chart = get_param("chart");
	$chart_data = array();
	if ($chart == "users") {
		check_admin_security("site_users");
		foreach ($chart_dates as $id => $chart_date) {
			$cache = $chart_date["cache"];
			$start_date = $chart_date["start"];
			$end_date = $chart_date["end"];
			$x_text = $chart_date["x-text"];
			$y_value = false; 
			if ($cache) { $y_value = get_cache(24,1,"counts_users", "date",date("Y-m-d",$start_date)); }
			if ($y_value === false) { 
				$sql = " SELECT count(user_id) FROM ".$table_prefix."users ";
				$sql.= " WHERE registration_date >= ".$db->tosql($start_date, DATE)." AND registration_date < ".$db->tosql($end_date, DATE)." AND is_approved = 1";
				$y_value = get_db_value($sql);
				if (!$y_value) { $y_value = 0; }
				if ($cache) { set_cache($y_value, "counts_users", "date", date("Y-m-d",$start_date)); }
			}
			$chart_data[] = array(
				"x" => $x_text, "x_text" => $x_text, "y" => $y_value, "y_value" => $y_value,  "y_text" => $y_value, 
			);
		}
		parse_chart(1170,300,$chart_data, "zero", "0");
	} else if ($chart == "orders") {
		check_admin_security("sales_orders");
		foreach ($chart_dates as $id => $chart_date) {
			$cache = $chart_date["cache"];
			$start_date = $chart_date["start"];
			$end_date = $chart_date["end"];
			$x_text = $chart_date["x-text"];
			$y_value = false; 
			if ($cache) { $y_value = get_cache(24,1,"counts_orders", "date",date("Y-m-d",$start_date)); }
			if ($y_value === false) { 
				$sql = " SELECT count(o.order_id) ";
				$sql.= " FROM ".$table_prefix."orders o ";
				//$sql.= " INNER JOIN ".$table_prefix."order_statuses os ON os.paid_status = 1 AND os.status_id = o.order_status ";
				$sql.= " WHERE o.order_placed_date >= ".$db->tosql($start_date, DATE)." AND o.order_placed_date < ".$db->tosql($end_date, DATE);
				$y_value = get_db_value($sql);
				if (!$y_value) { $y_value = 0; }
				if ($cache) { set_cache($y_value, "counts_orders", "date", date("Y-m-d",$start_date)); }
			}
			$chart_data[] = array(
				"x" => $x_text, "x_text" => $x_text, "y" => $y_value, "y_value" => $y_value,  "y_text" => $y_value, 
			);
		}
		parse_chart(1170,300,$chart_data, "zero", "0");
	} else if ($chart == "sales") {
		check_admin_security("sales_orders");
		foreach ($chart_dates as $id => $chart_date) {
			$cache = $chart_date["cache"];
			$start_date = $chart_date["start"];
			$end_date = $chart_date["end"];
			$x_text = $chart_date["x-text"];
			$y_value = false; 
			if ($cache) { $y_value = get_cache(24,1,"counts_sales", "date",date("Y-m-d",$start_date)); }
			if ($y_value === false) { 
				$sql = " SELECT sum(o.order_total) ";
				$sql.= " FROM ".$table_prefix."orders o ";
				//$sql.= " INNER JOIN ".$table_prefix."order_statuses os ON os.paid_status = 1 AND os.status_id = o.order_status ";
				$sql.= " WHERE o.order_placed_date >= ".$db->tosql($start_date, DATE)." AND o.order_placed_date < ".$db->tosql($end_date, DATE);
				$y_value = get_db_value($sql);
				if (!$y_value) { $y_value = 0; }
				if ($cache) { set_cache($y_value, "counts_sales", "date", date("Y-m-d",$start_date)); }
			}
			$chart_data[] = array(
				"x" => $x_text, "x_text" => $x_text, "y" => $y_value, "y_value" => $y_value,  "y_text" => currency_format($y_value), 
			);
		}
		parse_chart(1170,300,$chart_data, "currency", 2);
	} else if ($chart == "visits") {
		check_admin_security("visits_report"); 
		foreach ($chart_dates as $id => $chart_date) {
			$cache = $chart_date["cache"];
			$start_date = $chart_date["start"];
			$end_date = $chart_date["end"];
			$x_text = $chart_date["x-text"];
			$y_value = false; 
			if ($cache) { $y_value = get_cache(24,1,"counts_visits", "date",date("Y-m-d",$start_date)); }
			if ($y_value === false) { 
				$sql = " SELECT count(visit_id) FROM ".$table_prefix."tracking_visits ";
				$sql.= " WHERE date_added >= ".$db->tosql($start_date, DATE)." AND date_added < ".$db->tosql($end_date, DATE);
				$y_value = get_db_value($sql);
				if (!$y_value) { $y_value = 0; }
				if ($cache) { set_cache($y_value, "counts_visits", "date", date("Y-m-d",$start_date)); }
			}
			$chart_data[] = array(
				"x" => $x_text, "x_text" => $x_text, "y" => $y_value, "y_value" => $y_value,  "y_text" => currency_format($y_value), 
			);
		}
		parse_chart(1170,300,$chart_data, "zero", 0);
	} else {
		check_admin_security();
	}
	$t->pparse("chart_block", "chart");

?>