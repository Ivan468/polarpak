<?php

	include_once("./includes/profile_functions.php");

	$default_title = "{MY_VOUCHERS_MSG}";

	check_user_security("my_vouchers");

	// check user settings
	$user_id = get_session("session_user_id");
	$user_settings = user_settings($user_id);
	
	$voucher_settings = get_settings("user_voucher");
	$default_voucher_transfer = get_setting_value($voucher_settings, "voucher_transfer", 0);
	$default_cash_out_voucher = get_setting_value($voucher_settings, "cash_out_voucher", 0);
	$voucher_transfer = get_setting_value($user_settings, "voucher_transfer", $default_voucher_transfer);
	$cash_out_voucher = get_setting_value($user_settings, "cash_out_voucher", $default_cash_out_voucher);

	$html_template = get_setting_value($block, "html_template", "block_user_vouchers.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("user_vouchers_href",  get_custom_friendly_url("user_vouchers.php"));
	$t->set_var("user_voucher_href",   get_custom_friendly_url("user_voucher.php"));
	$t->set_var("user_voucher_send_href",   get_custom_friendly_url("user_voucher_send.php"));
	$t->set_var("user_voucher_cash_href",   get_custom_friendly_url("user_voucher_cash.php"));
	$t->set_var("user_home_href", get_custom_friendly_url("user_home.php"));

	$s = new VA_Sorter($settings["templates_dir"], "sorter.html", get_custom_friendly_url("user_vouchers.php"));
	$s->set_parameters(false, true, true, false);
	$s->set_sorter(CODE_MSG, "sorter_code", "1", "c.coupon_code", "", "", true);
	$s->set_sorter(BALANCE_MSG, "sorter_balance", "2", "c.coupon_amount");
	$s->set_sorter(DATE_MSG, "sorter_date", "3", "c.date_added");
	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", get_custom_friendly_url("user_vouchers.php"));

	// set up variables for navigator
	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "coupons c ";
	$sql .= " WHERE user_id=" . $db->tosql(get_session("session_user_id"), INTEGER);
	$db->query($sql);
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 5;

	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	
	$sql  = " SELECT * FROM " . $table_prefix . "coupons c ";
	$sql .= " WHERE user_id=" . $db->tosql(get_session("session_user_id"), INTEGER);
	$db->query($sql . $s->order_by);
	if($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do
		{
			$coupon_id = $db->f("coupon_id");
			$voucher_title = $db->f("coupon_title");
			$voucher_code = $db->f("coupon_code");
			$voucher_amount = $db->f("discount_amount");
			$voucher_date = $db->f("date_added", DATETIME);

			$start_date = "";
			$is_upcoming = false;
			$start_date_db = $db->f("start_date", DATETIME);
			if(is_array($start_date_db)) {
				$start_date = va_date($date_show_format, $start_date_db);
				$start_date_ts = mktime (0,0,0, $start_date_db[MONTH], $start_date_db[DAY], $start_date_db[YEAR]);
				$current_date_ts = va_timestamp();
				if($current_date_ts < $start_date_ts) {
					$is_upcoming = true;
				}
			} 
			$t->set_var("start_date", $start_date);
			$expiry_date = "";
			$is_expired = false;
			$expiry_date_db = $db->f("expiry_date", DATETIME);
			if(is_array($expiry_date_db)) {
				$expiry_date = va_date($date_show_format, $expiry_date_db);
				$expiry_date_ts = mktime (0,0,0, $expiry_date_db[MONTH], $expiry_date_db[DAY], $expiry_date_db[YEAR]);
				$current_date_ts = va_timestamp();
				if($current_date_ts > $expiry_date_ts) {
					$is_expired = true;
				}
			} 
			$t->set_var("expiry_date", $expiry_date);

			$t->set_var("voucher_id", $coupon_id);
			$t->set_var("coupon_id", $coupon_id);
			$t->set_var("voucher_code", htmlspecialchars($voucher_code));
			$t->set_var("voucher_title", htmlspecialchars($voucher_title));
			$t->set_var("voucher_name", htmlspecialchars($voucher_title));
			$t->set_var("voucher_amount", currency_format($voucher_amount));
			$t->set_var("voucher_balance", currency_format($voucher_amount));

			$t->set_var("voucher_date", va_date($date_show_format, $voucher_date));

			$is_active = $db->f("is_active");
			if (!$is_active) {
				$voucher_class = "st-inactive";
				$voucher_status = INACTIVE_MSG;
			} else if ($voucher_amount == 0) {
				$voucher_class = "st-used";
				$voucher_status = USED_MSG;
			} else if ($is_expired) {
				$voucher_class = "st-expired";
				$voucher_status = EXPIRED_MSG;
			} else if ($is_upcoming) {
				$voucher_class = "st-upcoming";
				$voucher_status = UPCOMING_MSG;
			} else {
				$voucher_class = "st-active";
				$voucher_status = ACTIVE_MSG;
			}
			$t->set_var("voucher_class", $voucher_class);
			$t->set_var("voucher_status", $voucher_status);
			if ($voucher_transfer) {
				$t->sparse("send_voucher_link", false);
			} else {
				$t->set_var("send_voucher_link", "");
			}
			if ($cash_out_voucher) {
				$t->sparse("cash_out_voucher_link", false);
			} else {
				$t->set_var("cash_out_voucher_link", "");
			}

			$t->parse("records", true);
		} while($db->next_record());
	}
	else
	{
		$t->set_var("sorters", "");
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	$block_parsed = true;
