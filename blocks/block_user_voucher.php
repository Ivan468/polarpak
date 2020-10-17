<?php

	include_once("./includes/profile_functions.php");

	$default_title = "{VOUCHER_MSG} :: {ACTIVITY_MSG}";

	check_user_security("my_vouchers");

	$user_id = get_session("session_user_id");
	$voucher_id = get_param("voucher_id");

	$voucher_settings = get_settings("user_voucher");
	$voucher_transfer = get_setting_value($voucher_settings, "voucher_transfer", "");

	$sql  = " SELECT * FROM " . $table_prefix . "coupons c ";
	$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
	$sql .= " AND coupon_id=" . $db->tosql($voucher_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$voucher_title = $db->f("coupon_title");
		$voucher_code = $db->f("coupon_code");
	} else {
		header("Location: user_home.php");
		exit;
	}

	$html_template = get_setting_value($block, "html_template", "block_user_voucher.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("user_vouchers_href",  get_custom_friendly_url("user_vouchers.php"));
	$t->set_var("user_voucher_href",   get_custom_friendly_url("user_voucher.php"));
	$t->set_var("user_voucher_send_href",   get_custom_friendly_url("user_voucher_send.php"));
	$t->set_var("user_home_href", get_custom_friendly_url("user_home.php"));

	$events = array();
	$sql  = " SELECT ce.* ";
	$sql .= " FROM " . $table_prefix . "coupons_events ce ";
	$sql .= " WHERE ce.coupon_id=" . $db->tosql($voucher_id, INTEGER);
	$sql .= " ORDER BY ce.event_id ";
	$db->query($sql);
	while ($db->next_record()) {
		$event_id = $db->f("event_id");
		$event_date = $db->f("event_date", DATETIME);
		$events[$event_id] = $db->Record;
		$events[$event_id]["event_date"] = $event_date;
	}

	if (count($events)) {
		$voucher_balance = 0;
		foreach ($events as $event_id => $event_data) {
			$order_id = $event_data["order_id"];
			$payment_id = $event_data["payment_id"];
			$transaction_id = $event_data["transaction_id"];
			$admin_id = $event_data["admin_id"];
			$user_id = $event_data["user_id"];
			$from_user_id = $event_data["from_user_id"];
			$to_user_id = $event_data["to_user_id"];
			$event_type = $event_data["event_type"];
			$event_date = $event_data["event_date"];
			$remote_ip = $event_data["remote_ip"];
			$voucher_amount = $event_data["coupon_amount"];
			$voucher_balance += $voucher_amount;
			if (!strlen($transaction_id)) { $transaction_id = $order_id; }

			if ($event_type == "voucher_added" || $event_type == "voucher_add") {
				$event_desc = va_constant("VOUCHER_ADDED_EVENT");
			} else if ($event_type == "voucher_purchased" || $event_type == "voucher_purchase") {
				$event_desc = va_constant("VOUCHER_PURCHASED_EVENT");
			} else if ($event_type == "voucher_sent") {
				$event_desc = va_constant("VOUCHER_SENT_EVENT");
				$sql = " SELECT * FROM ".$table_prefix."users WHERE user_id=".$db->tosql($to_user_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$receiver_name = get_user_name($db->Record);
				} else {
					$receiver_name = "#".$to_user_id;
				}
				$event_desc = str_replace("{receiver_name}", $receiver_name, $event_desc);
			} else if ($event_type == "voucher_received") {
				$event_desc = va_constant("VOUCHER_RECEIVED_EVENT");
				$sql = " SELECT * FROM ".$table_prefix."users WHERE user_id=".$db->tosql($from_user_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$sender_name = get_user_name($db->Record);
				} else {
					$sender_name = "#".$from_user_id;
				}
				$event_desc = str_replace("{sender_name}", $sender_name, $event_desc);
			} else if ($event_type == "voucher_used") {
				$event_desc = va_constant("VOUCHER_USED_EVENT");
			} else if ($event_type == "voucher_cashed_out" || $event_type == "voucher_cash_out") {
				$event_desc = va_constant("VOUCHER_CASHED_OUT_EVENT");
			} else if ($event_type == "transfer_fee") {
				$event_desc = va_constant("TRANSFER_FEE_MSG");
			} else if ($event_type == "cash_out_fee") {
				$event_desc = va_constant("CASH_OUT_FEE_MSG");
			} else if ($event_type == "subtract_amount") {
				$event_desc = va_constant("VOUCHER_SUBTRACT_AMOUNT_MSG");
			} else if ($event_type == "add_amount") {
				$event_desc = va_constant("VOUCHER_ADD_AMOUNT_MSG");
			} else {
				$event_desc = $event_type;
			}
	  
			$t->set_var("event_date", va_date($datetime_show_format, $event_date));
			$t->set_var("event_desc", htmlspecialchars($event_desc));
			$t->set_var("transaction_id", htmlspecialchars($transaction_id));
			$t->set_var("transaction_number", htmlspecialchars($transaction_id));

			$t->set_var("voucher_amount", currency_format($voucher_amount));
			$t->set_var("voucher_balance", currency_format($voucher_balance));

	  
			$t->parse("events", true);
		}
	}

	$block_parsed = true;
