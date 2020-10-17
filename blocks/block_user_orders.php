<?php

	$default_title = "{MY_ORDERS_MSG}";

	check_user_security("my_orders");

	// check user data
	$user_id = get_session("session_user_id");
	$user_info = get_session("session_user_info");
	$user_sites_all = get_setting_value($user_info, "sites_all", 0);
	$user_site_ids = get_setting_value($user_info, "site_ids", ""); 

	$orders_currency = get_setting_value($settings, "orders_currency", 0);
	$show_restore = get_setting_value($vars, "show_restore", 0);
	
	$operation = get_param("operation");
	if ($operation == "restore") {
		include_once("./includes/order_restore.php");
	}

	$html_template = get_setting_value($block, "html_template", "block_user_orders.html"); 
	$t->set_file("block_body", $html_template);
	$t->set_var("user_orders_href", get_custom_friendly_url("user_orders.php"));
	$t->set_var("user_order_href",  get_custom_friendly_url("user_order.php"));
	$t->set_var("user_home_href",   get_custom_friendly_url("user_home.php"));
	$t->set_var("user_order_payment_href", get_custom_friendly_url("user_order_payment.php"));
	$t->set_var("user_invoice_pdf_href",   get_custom_friendly_url("user_invoice_pdf.php"));
	$t->set_var("user_invoice_html_href",  get_custom_friendly_url("user_invoice_html.php"));
	if($show_restore == 1) {
		$t->parse("reorder_header", false);
	}
	else {
		$t->set_var("reorder_header", "");
	}
	
	$s = new VA_Sorter($settings["templates_dir"], "sorter.html", get_custom_friendly_url("user_orders.php"));
	$s->set_default_sorting(1, "desc");
	$s->set_sorter(ORDER_NUMBER_COLUMN, "sorter_id", "1", "order_id");
	$s->set_sorter(ORDER_ADDED_COLUMN, "sorter_date", "2", "order_placed_date");
	$s->set_sorter(STATUS_MSG, "sorter_status", "3", "order_status");
	$s->set_sorter(ORDER_TOTAL_COLUMN, "sorter_total", "4", "order_total");

	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", get_custom_friendly_url("user_orders.php"));

	// set up variables for navigator
	$sql  = " SELECT COUNT(*) FROM (" . $table_prefix . "orders o ";
	$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
	$sql .= " WHERE o.user_id=" . $db->tosql(get_session("session_user_id"), INTEGER);
	$sql .= " AND (os.show_for_user IS NULL OR os.show_for_user=1) "; // IS NULL condition added for back compatibility  
	if (!$user_sites_all) {
		$sql .= " AND o.site_id=0 OR o.site_id IS NULL OR o.site_id IN (" . $db->tosql($user_site_ids, INTEGERS_LIST) . ") ";
	}
	$db->query($sql);
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 5;

	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = " SELECT o.order_id, o.order_placed_date, os.status_name, o.goods_total, o.order_total, o.is_placed, os.payment_allowed, os.paid_status, ";
	$sql .= " o.currency_code, o.currency_rate, c.symbol_right, c.symbol_left, c.decimals_number, c.decimal_point, c.thousands_separator, ";
	$sql .= " os.user_invoice_activation ";
	$sql .= " FROM ((" . $table_prefix . "orders o ";
	$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "currencies c ON o.currency_code=c.currency_code) ";
	$sql .= " WHERE o.user_id=" . $db->tosql(get_session("session_user_id"), INTEGER);
	$sql .= " AND (os.show_for_user IS NULL OR os.show_for_user=1) "; // IS NULL condition added for back compatibility  
	if (!$user_sites_all) {
		$sql .= " AND o.site_id=0 OR o.site_id IS NULL OR o.site_id IN (" . $db->tosql($user_site_ids, INTEGERS_LIST) . ") ";
	}
	$db->query($sql . $s->order_by);
	$orders = array(); // save all orders in array before show
	while ($db->next_record()) {
		$order_id = $db->f("order_id");
		$placed_date = $db->f("order_placed_date", DATETIME);
		$orders[$order_id] = $db->Record;
		$orders[$order_id]["placed_date"] = $placed_date;
	}

	if (sizeof($orders) > 0) {
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		foreach ($orders as $order_id => $order) {
			$is_placed = $order["is_placed"];
			$payment_allowed = $order["payment_allowed"];
			$paid_status = $order["paid_status"];
			$user_invoice_activation = $order["user_invoice_activation"];
			$order_total = $order["order_total"];
			$placed_date = $order["placed_date"];
			$placed_hour = isset($placed_date[3]) ? $placed_date[3] : 0;
			$placed_minute = isset($placed_date[4]) ? $placed_date[4] : 0;
			$placed_second = isset($placed_date[5]) ? $placed_date[5] : 0;
			// get order currency
			$order_currency = array();
			$order_currency_code = $order["currency_code"];
			$order_currency["code"] = $order["currency_code"];
			$order_currency["rate"] = $order["currency_rate"];
			$order_currency["left"] = $order["symbol_left"];
			$order_currency["right"] = $order["symbol_right"];
			$order_currency["decimals"] = $order["decimals_number"];
			$order_currency["point"] = $order["decimal_point"];
			$order_currency["separator"] = $order["thousands_separator"];
			$vc = md5($order_id . $placed_hour.$placed_minute.$placed_second);
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
			$t->set_var("order_id", $order_id);
			$t->set_var("vc", $vc);

			$t->set_var("order_placed_date", va_date($datetime_show_format, $placed_date));

			$t->set_var("order_status", get_translation($order["status_name"]));
			$t->set_var("order_total", currency_format($order_total, $order_currency));
			if ($is_placed || $paid_status || !$payment_allowed) {
				$t->set_var("pay_link", "");
			} else {
				$t->sparse("pay_link", false);
			}

			if ($user_invoice_activation) {
				$t->sparse("invoice_links", false);
			} else {
				$t->set_var("invoice_links", "");
			}
			if($show_restore == 1) {
				$t->parse("reorder_body", false);
			}
			else {
				$t->set_var("reorder_body", "");
			}


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
				if (strlen($item_name) > 20) {
					$item_name = substr($item_name, 0, 20) . "...";
				}
				$quantity = doubleval($db->f("quantity"));
				$price = $db->f("price");
				$total_quantity += $quantity;
				$total_price += ($price * $quantity);

				$t->set_var("item_name", htmlspecialchars($item_name));
				$t->set_var("item_status", htmlspecialchars($item_status));
				$t->set_var("quantity",  $quantity);
				$t->set_var("price", currency_format($price, $order_currency));
				$t->parse("order_items", true);
			}
			$t->set_var("total_quantity", $total_quantity);
			$t->set_var("total_price", currency_format($total_price, $order_currency));


			$t->parse("records", true);
		}
	}
	else
	{
		$t->set_var("sorters", "");
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	$block_parsed = true;

?>