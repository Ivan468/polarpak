<?php

	include_once("./includes/profile_functions.php");
	include_once("./includes/order_functions.php");
	include_once("./includes/order_items.php");
	include_once("./includes/order_links.php");
	include_once("./includes/parameters.php");

	$default_title = "{CASH_OUT_VOUCHER_MSG}";

	check_user_security("my_vouchers");

	// check and get default currency to post ads 
	$default_currency_code = ""; $default_currency_title = "";
	$sql = "SELECT currency_code, currency_title  FROM ".$table_prefix."currencies WHERE is_default=1";
	$db->query($sql);
	if ($db->next_record()) {
		$default_currency_code = $db->f("currency_code");
		$default_currency_title = $db->f("currency_title");
	}
	$currency = get_currency($default_currency_code);

	$order_info = get_settings("order_info");

	$user_id = get_session("session_user_id");
	$user_type_id = get_session("session_user_type_id");
	$user_info = get_session("session_user_info");
	$sender_name = get_setting_value($user_info, "name", "");
	$sender_email = get_setting_value($user_info, "email", "");
	$sender_cell_phone = get_setting_value($user_info, "cell_phone", "");
	$phone_code_select = get_setting_value($settings, "phone_code_select", 0);

	$is_valid = true;
	$voucher_id = get_param("voucher_id");
	$operation = get_param("operation");
	$confirm_code = get_param("confirm_code");

	// check default voucher settings
	$voucher_settings = get_settings("user_voucher");
	$voucher_settings = get_settings("user_voucher");
	$default_cash_out_voucher = get_setting_value($voucher_settings, "cash_out_voucher", 0);
	$default_cash_out_time_limit = get_setting_value($voucher_settings, "cash_out_time_limit", 30); // 30 minutes by default
	$default_cash_out_fee_percent = get_setting_value($voucher_settings, "cash_out_fee_percent", 0); 
	$default_cash_out_fee_amount = get_setting_value($voucher_settings, "cash_out_fee_amount", 0); 
	$default_cash_out_status_id = get_setting_value($voucher_settings, "cash_out_status_id", 0); 
	$cash_out_attempts = get_setting_value($voucher_settings, "cash_out_attempts", 5); // how many attempts allowed for user

	// check user settings
	$user_settings = user_settings($user_id);
	$cash_out_voucher = get_setting_value($user_settings, "cash_out_voucher", $default_cash_out_voucher);
	$cash_out_time_limit = get_setting_value($user_settings, "cash_out_time_limit", $default_cash_out_time_limit); 
	$cash_out_fee_percent = get_setting_value($user_settings, "cash_out_fee_percent", $default_cash_out_fee_percent); 
	$cash_out_fee_amount = get_setting_value($user_settings, "cash_out_fee_amount", $default_cash_out_fee_amount); 
	$cash_out_status_id = get_setting_value($user_settings, "cash_out_status_id", $default_cash_out_status_id); 

	$valid_time = ($cash_out_time_limit * 60);
	$current_ts = va_timestamp();
	if ($cash_out_fee_percent) {
		$cash_out_fee_desc = $cash_out_fee_percent."%";
	}
	if ($cash_out_fee_amount) {
		if ($cash_out_fee_desc) { $cash_out_fee_desc .= " + "; }
		$cash_out_fee_desc .= currency_format($cash_out_fee_amount);
	}

	if (!$cash_out_voucher) {
		header("Location: user_home.php");
		exit;
	}

	$sql  = " SELECT c.* FROM " . $table_prefix . "coupons c ";
	$sql .= " WHERE c.user_id=" . $db->tosql($user_id, INTEGER);
	$sql .= " AND c.coupon_id=" . $db->tosql($voucher_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$voucher_active = $db->f("is_active");
		$order_id = $db->f("order_id");
		$order_item_id = $db->f("order_item_id");

		$voucher_id = $db->f("coupon_id");
		$voucher_type = $db->f("discount_type");
		$voucher_title = $db->f("coupon_title");
		$voucher_code = $db->f("coupon_code");
		$voucher_amount = $db->f("discount_amount");
		$transfer_user_id = $db->f("transfer_user_id");
		$transfer_type = $db->f("transfer_type");
		$transfer_code = $db->f("transfer_code");
		$transfer_date = $db->f("transfer_date", DATETIME);
		$transfer_ts = is_array($transfer_date) ? va_timestamp($transfer_date) : 0;
		$transfer_expiration = $transfer_ts + $valid_time;
		$transfer_amount = $db->f("transfer_amount");
		$transfer_data = $db->f("transfer_data");
		$transfer_errors = $db->f("transfer_errors");
		$attempts_error = "";
		if ($transfer_type != "CASH_OUT" && $transfer_type != "CASHOUT") {
			$transfer_code = "";
		}

		if ($current_ts > ($transfer_ts + $valid_time) || $transfer_amount > $voucher_amount) {
			// clear transfer data if transfer time expired or transfer amount become greater than voucher amount left
			$transfer_errors = 0; $transfer_code = ""; $transfer_data = "";
			$sql  = " UPDATE " . $table_prefix . "coupons ";
			$sql .= " SET transfer_user_id=NULL ";
			$sql .= " , transfer_type=NULL ";
			$sql .= " , transfer_amount=NULL ";
			$sql .= " , transfer_code=NULL ";
			$sql .= " , transfer_date=NULL ";
			$sql .= " , transfer_data=NULL ";
			$sql .= " , transfer_errors=0 ";
			$sql .= " WHERE coupon_id=" . $db->tosql($voucher_id, INTEGER);
			$db->query($sql);
		} else if ($transfer_errors >= $cash_out_attempts) {
			$is_valid = false;
			$time_left = ($transfer_ts + $valid_time) - $current_ts;
			if ($time_left > 60) {
				$time_left = str_replace("{quantity}", intval($time_left/60), va_constant("MINUTES_QTY_MSG"));
			} else {
				$time_left = str_replace("{quantity}", $time_left, va_constant("SECONDS_QTY_MSG"));
			}
			$attempts_error = str_replace("{interval_time}", $time_left, va_constant("TRANSFER_ATTEMPTS_ERROR"));
			// clear transfer data but keep transfer date and number of errors till it expired
			if ($transfer_code) {
				$transfer_code = "";
				$sql  = " UPDATE " . $table_prefix . "coupons ";
				$sql .= " SET transfer_user_id=NULL ";
				$sql .= " , transfer_type=NULL ";
				$sql .= " , transfer_amount=NULL ";
				$sql .= " , transfer_code=NULL ";
				$sql .= " , transfer_data=NULL ";
				$sql .= " WHERE coupon_id=" . $db->tosql($voucher_id, INTEGER);
				$db->query($sql);
			}
		}
	} else {
		header("Location: user_home.php");
		exit;
	}

	$html_template = get_setting_value($block, "html_template", "block_user_voucher_cash.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("user_vouchers_href",  get_custom_friendly_url("user_vouchers.php"));
	$t->set_var("user_voucher_href",   get_custom_friendly_url("user_voucher.php"));
	$t->set_var("user_voucher_send_href",   get_custom_friendly_url("user_voucher_send.php"));
	$t->set_var("user_home_href", get_custom_friendly_url("user_home.php"));
	$t->set_var("voucher_amount", currency_format($voucher_amount));
	$t->set_var("default_currency_code", htmlspecialchars($default_currency_code));
	if ($cash_out_fee_desc) {
		$t->set_var("cash_out_fee_desc", htmlspecialchars($cash_out_fee_desc));
		$t->parse("cash_out_fee_desc_block", false);
	}

	set_script_tag("js/profile.js");

	$max_voucher_amount = $voucher_amount - $cash_out_fee_amount;
 	$max_voucher_amount = ($max_voucher_amount * 100) / (100 + $cash_out_fee_percent);
	$max_voucher_amount = round($max_voucher_amount, 2);

	$r = new VA_Record($table_prefix . "orders");
	// voucher related parameters
	$r->add_hidden("voucher_id", INTEGER);
	$r->add_hidden("confirm_code", INTEGER, TRANSFER_CODE_MSG);
	$r->change_property("confirm_code", CONTROL_DESC, TRANSFER_CODE_MSG);
	$r->add_hidden("transfer_amount", NUMBER, TRANSFER_AMOUNT_MSG);
	$r->change_property("transfer_amount", CONTROL_DESC, TRANSFER_AMOUNT_MSG);
	$r->change_property("transfer_amount", REQUIRED, true);
	$r->change_property("transfer_amount", MIN_VALUE, 0.01);
	$r->change_property("transfer_amount", MAX_VALUE, $max_voucher_amount);
	// order related parameters
	$r->add_where("order_id", INTEGER);
	$r->add_textbox("invoice_number", TEXT);
	$r->change_property("invoice_number", USE_SQL_NULL, false);
	$r->change_property("invoice_number", USE_IN_UPDATE, false);

	// prepare arrays for countries and states which we need to show and check additional data
	$va_countries = va_countries(); 
	$va_states = va_states();

	$companies = get_db_values("SELECT company_id,company_name FROM " . $table_prefix . "companies ", array(array("", va_constant("SELECT_COMPANY_MSG"))));
	//$states = get_db_values("SELECT state_id,state_name FROM " . $table_prefix . "states WHERE show_for_user=1 ORDER BY state_name ", array(array("", va_constant("SELECT_STATE_MSG"))));
	$countries = get_db_values("SELECT country_id,country_name FROM " . $table_prefix . "countries WHERE show_for_user=1 ORDER BY country_order, country_name ", array(array("", va_constant("SELECT_COUNTRY_MSG"))));
	// get phone codes
	$phone_codes = get_phone_codes();

	$r->add_textbox("name", TEXT, va_constant("FULL_NAME_FIELD"));
	$r->change_property("name", USE_SQL_NULL, false);
	$r->add_textbox("first_name", TEXT, va_constant("FIRST_NAME_FIELD"));
	$r->change_property("first_name", USE_SQL_NULL, false);
	$r->add_textbox("middle_name", TEXT, va_constant("MIDDLE_NAME_FIELD"));
	$r->change_property("middle_name", USE_SQL_NULL, false);
	$r->add_textbox("last_name", TEXT, va_constant("LAST_NAME_FIELD"));
	$r->change_property("last_name", USE_SQL_NULL, false);
	$r->add_select("company_id", INTEGER, $companies, va_constant("COMPANY_SELECT_FIELD"));
	$r->add_textbox("company_name", TEXT, va_constant("COMPANY_NAME_FIELD"));
	$r->add_textbox("email", TEXT, va_constant("EMAIL_FIELD"));
	$r->change_property("email", USE_SQL_NULL, false);
	$r->change_property("email", REGEXP_MASK, EMAIL_REGEXP);
	$r->add_textbox("address1", TEXT, va_constant("STREET_FIRST_FIELD"));
	$r->add_textbox("address2", TEXT, va_constant("STREET_SECOND_FIELD"));
	$r->add_textbox("address3", TEXT, va_constant("STREET_THIRD_FIELD"));
	$r->add_textbox("city", TEXT, CITY_FIELD);
	$r->add_textbox("province", TEXT, PROVINCE_FIELD);
	$r->add_select("state_id", INTEGER, "", va_constant("STATE_FIELD"));
	$r->change_property("state_id", USE_SQL_NULL, false);
	$r->add_textbox("state_code", TEXT);
	$r->add_textbox("zip", TEXT, ZIP_FIELD);
	$r->change_property("zip", TRIM, true);
	$r->add_select("country_id", INTEGER, $countries, va_constant("COUNTRY_FIELD"));
	$r->change_property("country_id", USE_SQL_NULL, false);
	$r->add_textbox("country_code", TEXT);
	if ($phone_code_select) {
		$r->add_select("phone_code", TEXT, $phone_codes);
		$r->add_select("daytime_phone_code", TEXT, $phone_codes);
		$r->add_select("evening_phone_code", TEXT, $phone_codes);
		$r->add_select("cell_phone_code", TEXT, $phone_codes);
		$r->add_select("fax_code", TEXT, $phone_codes);
	}
	$r->add_textbox("phone", TEXT, va_constant("PHONE_FIELD"));
	$r->change_property("phone", REGEXP_MASK, PHONE_REGEXP);
	$r->add_textbox("daytime_phone", TEXT, va_constant("DAYTIME_PHONE_FIELD"));
	$r->change_property("daytime_phone", REGEXP_MASK, PHONE_REGEXP);
	$r->add_textbox("evening_phone", TEXT, va_constant("EVENING_PHONE_FIELD"));
	$r->change_property("evening_phone", REGEXP_MASK, PHONE_REGEXP);
	$r->add_textbox("cell_phone", TEXT, va_constant("CELL_PHONE_FIELD"));
	$r->change_property("cell_phone", REGEXP_MASK, PHONE_REGEXP);
	$r->add_textbox("fax", TEXT, va_constant("FAX_FIELD"));
	$r->change_property("fax", REGEXP_MASK, PHONE_REGEXP);

	$personal_fields = array(); $personal_number = 0;
	for ($i = 0; $i < sizeof($parameters); $i++) {
		$param_prefix = ""; 
		$param_name = $parameters[$i];
		$r->change_property($param_name, TRIM, true);

		$personal_param = $param_prefix."show_" . $param_name;
		$personal_required = $param_prefix.$param_name."_required";

		if (isset($order_info[$personal_param]) && $order_info[$personal_param] == 1) {
			$personal_number++;
			if ($order_info[$parameters[$i] . "_required"] == 1) {
				$r->parameters[$parameters[$i]][REQUIRED] = true;
			}
			$field_name = $r->get_property_value($param_name, CONTROL_DESC);
			$personal_fields[$param_name] = array(
				"field_name" => $field_name,
				"required" => $order_info[$personal_required],
				"required_message" => strip_tags(str_replace("{field_name}", $field_name, va_constant("REQUIRED_MESSAGE"))),
			);
		} else {
			$r->parameters[$parameters[$i]][SHOW] = false;
		}
	}
	$t->set_var("personal_fields", json_encode($personal_fields));

	// voucher record
	$vr = new VA_Record($table_prefix . "coupons");
	$vr->add_textbox("parent_coupon_id", INTEGER);
	$vr->add_textbox("order_id", INTEGER);
	$vr->add_textbox("order_item_id", INTEGER);
	$vr->add_textbox("user_id", INTEGER);
	$vr->add_textbox("coupon_code", TEXT);
	$vr->add_textbox("coupon_title", TEXT);
	$vr->add_textbox("is_active", INTEGER);
	$vr->add_textbox("discount_type", INTEGER);
	$vr->add_textbox("discount_amount", NUMBER);
	$vr->add_textbox("quantity_limit", INTEGER);
	$vr->add_textbox("coupon_uses", INTEGER);

	$ce = new VA_Record($table_prefix . "coupons_events");
	$ce->add_textbox("coupon_id", INTEGER);
	$ce->add_textbox("order_id", INTEGER);
	$ce->add_textbox("payment_id", INTEGER);
	$ce->add_textbox("transaction_id", TEXT);

	$ce->add_textbox("admin_id", INTEGER);
	$ce->add_textbox("user_id", INTEGER);
	$ce->add_textbox("from_user_id", INTEGER);
	$ce->add_textbox("to_user_id", INTEGER);
	$ce->add_textbox("event_date", DATETIME);
	$ce->add_textbox("event_type", TEXT);
	$ce->add_textbox("remote_ip", TEXT);
	$ce->add_textbox("coupon_amount", NUMBER);

	if ($operation == "cash") {
		$r->get_form_parameters();
		// check if we need to set some phone code required before validate record
		prepare_phone_codes();

		// validate postal code for correct use
		$country_id = $r->get_value("country_id");
		if ($country_id) {
			$country_code = $va_countries[$country_id]["country_code"];
			if (strtoupper($country_code) == "GB") {
				$r->change_property("zip", BEFORE_VALIDATE, "format_zip");
				$r->change_property("zip", REGEXP_MASK, UK_POSTCODE_REGEXP);
			}
		}

		$is_valid = true;
		if (!$voucher_active) {
			$is_valid = false;
			$r->errors = va_constant("COUPON_NON_ACTIVE_MSG");
		}
		if ($attempts_error) {
			$is_valid = false;
			$r->errors = $attempts_error;
		}
  	if ($is_valid) {
			$is_valid = $r->validate();
		}

		if ($is_valid) {
			// join phone code and phone number fields
			join_phone_fields();
			$transfer_data = array();
			for ($i = 0; $i < sizeof($parameters); $i++) {
				$param_name = $parameters[$i];
				$transfer_data[$param_name] = $r->get_value($param_name);
			}

			$transfer_amount = $r->get_value("transfer_amount");
			$transfer_code = mt_rand (100000, 999999);
			$transfer_date = va_time();
			$transfer_ts = va_timestamp($transfer_date);
			$transfer_expiration = $transfer_ts + $valid_time;

			$sql  = " UPDATE " . $table_prefix . "coupons ";
			$sql .= " SET transfer_user_id=NULL ";
			$sql .= " , transfer_type='CASH_OUT' ";
			$sql .= " , transfer_amount=" . $db->tosql($transfer_amount, NUMBER);
			$sql .= " , transfer_code=" . $db->tosql($transfer_code, TEXT);
			$sql .= " , transfer_date=" . $db->tosql($transfer_date, DATETIME);
			$sql .= " , transfer_data=" . $db->tosql(json_encode($transfer_data), TEXT);
			$sql .= " , transfer_errors=" . $db->tosql($transfer_errors, INTEGER);
			$sql .= " WHERE coupon_id=" . $db->tosql($voucher_id, INTEGER);
			$db->query($sql);

			// send transfer code to user
			$cash_request_notify = get_setting_value($voucher_settings, "cash_request_notify", "");
			$cash_request_sms_notify = get_setting_value($voucher_settings, "cash_request_sms_notify", "");

			$t->set_var("transfer_code", htmlspecialchars($transfer_code));
			$t->set_var("transfer_expiration", va_date($datetime_show_format, $transfer_expiration)); 
			$t->set_var("transfer_amount", currency_format($transfer_amount));
			$t->set_var("voucher_amount", currency_format($voucher_amount));
			$t->set_var("voucher_code", htmlspecialchars($voucher_code));

			if ($cash_request_notify && $sender_email)
			{
				$cash_request_subject = get_setting_value($voucher_settings, "cash_request_subject", va_constant("TRANSFER_CODE_MSG"));
				$cash_request_message = get_setting_value($voucher_settings, "cash_request_message", $transfer_code);

				$t->set_block("cash_request_subject", $cash_request_subject);
				$t->set_block("cash_request_message", $cash_request_message);
      
				$mail_to = $sender_email;
				$mail_from = get_setting_value($voucher_settings, "cash_request_from", $settings["admin_email"]);
				$email_headers = array();
				$email_headers["from"] = parse_value($mail_from);
				$email_headers["cc"] = get_setting_value($voucher_settings, "cash_request_cc");
				$email_headers["bcc"] = get_setting_value($voucher_settings, "cash_request_bcc");
				$email_headers["reply_to"] = get_setting_value($voucher_settings, "cash_request_reply_to");
				$email_headers["return_path"] = get_setting_value($voucher_settings, "cash_request_return_path");
				$email_headers["mail_type"] = get_setting_value($voucher_settings, "cash_request_message_type");
      
				$t->parse("cash_request_subject", false);
				$t->parse("cash_request_message", false);
				$cash_request_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("cash_request_message"));
				va_mail($mail_to, $t->get_var("cash_request_subject"), $cash_request_message, $email_headers);
			}

			if ($cash_request_sms_notify)
			{
				$cash_request_sms_recipient  = get_setting_value($voucher_settings, "cash_request_sms_recipient", $sender_cell_phone);
				$cash_request_sms_originator = get_setting_value($voucher_settings, "cash_request_sms_originator", "");
				$cash_request_sms_message    = get_setting_value($voucher_settings, "cash_request_sms_message", $access_code);
			
				$t->set_block("cash_request_sms_recipient",  $cash_request_sms_recipient);
				$t->set_block("cash_request_sms_originator", $cash_request_sms_originator);
				$t->set_block("cash_request_sms_message",    $cash_request_sms_message);
			
				$t->parse("cash_request_sms_recipient", false);
				$t->parse("cash_request_sms_originator", false);
				$t->parse("cash_request_sms_message", false);

				$cash_request_sms_recipient = $t->get_var("cash_request_sms_recipient");
			
				if ($cash_request_sms_recipient) {
					$sms_sent = sms_send($cash_request_sms_recipient, $t->get_var("cash_request_sms_message"), $t->get_var("cash_request_sms_originator"), $sms_errors);
					if (!$sms_sent) {
						$is_valid = false;
						$r->errors .= "SMS Gateway Error: " . $sms_errors . "<br>";
					}
				}
			}
			// end transfer code send  
		}
	} else if ($operation == "cancel") {
		// clear transfer data but exclude errors field and date to prevent following hack attempts
		$sql  = " UPDATE " . $table_prefix . "coupons ";
		$sql .= " SET transfer_user_id=NULL ";
		$sql .= " , transfer_type=NULL ";
		$sql .= " , transfer_amount=NULL ";
		$sql .= " , transfer_code=NULL ";
		$sql .= " , transfer_data=NULL ";
		$sql .= " WHERE coupon_id=" . $db->tosql($voucher_id, INTEGER);
		$db->query($sql);

		header("Location: user_vouchers.php");
		exit;
	} else if ($transfer_data) {
		$r->set_value("voucher_id", $voucher_id);
		$r->set_value("transfer_amount", $transfer_amount);
		$r->set_value("confirm_code", $confirm_code);

		$transfer_data = json_decode($transfer_data, true);
		for ($i = 0; $i < sizeof($parameters); $i++) {
			$param_name = $parameters[$i];
			$param_value = get_setting_value($transfer_data, $param_name);
			$r->set_value($param_name, $param_value);
		}
	} else if (!$operation) {
		$r->set_value("voucher_id", $voucher_id);
		$sql  = " SELECT * FROM ".$table_prefix."users ";
		$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			for ($i = 0; $i < sizeof($parameters); $i++) {
				$param_name = $parameters[$i];
				$param_value = $db->f($param_name);
				$r->set_value($param_name, $param_value);
			}
		}
	}

	// get different states lists
	$states = prepare_states($r);
	// check if phone codes available to set controls for them
	phone_code_checks($phone_codes);
	$r->set_form_parameters();


	if ($transfer_code) {
		// calculate cash out fee if available
		$cash_out_fee  = $cash_out_fee_amount;
		$cash_out_fee += round(($transfer_amount* $cash_out_fee_percent) / 100, 2);

		if (is_array($transfer_data)) {
			foreach ($transfer_data as $param_name => $param_value) {
				if (strlen($param_value)) {
					if ($param_name == "country_id") {
						$country_code = $va_countries[$param_value]["country_code"];
						$country_name = $va_countries[$param_value]["country_name"];
						$t->set_var("country_code", htmlspecialchars($country_code));
						$t->set_var("country_name", htmlspecialchars($country_name));
						$transfer_data["country_code"] = $country_code;
					} else if ($param_name == "state_id") {
						$state_code = $va_states[$param_value]["state_code"];
						$state_name = $va_states[$param_value]["state_name"];
						$t->set_var("state_code", htmlspecialchars($state_code));
						$t->set_var("state_name", htmlspecialchars($state_name));
						$transfer_data["state_code"] = $state_code;
					}

					$t->set_var($param_name, htmlspecialchars($param_value));
					$t->parse($param_name."_preview", false);
				}
			}
		}

		$transfer_done = false; // if cash out transfer was succefull
		$t->set_var("transfer_amount", currency_format($transfer_amount));
		$t->set_var("transfer_amount_currency", currency_format($transfer_amount));
		$t->set_var("transfer_expiration", va_date($datetime_show_format, $transfer_expiration)); 
		$t->set_var("code_valid_till", va_date($datetime_show_format, $transfer_expiration)); 
		if ($cash_out_fee) {
			$t->set_var("cash_out_fee", currency_format($cash_out_fee)); 
			$t->parse("cash_out_fee_block", false); 
		}

		if ($operation == "confirm") {
			$r->change_property("confirm_code", REQUIRED, true);
			$is_valid = $r->validate();
			if ($attempts_error) {
				$is_valid = false;
				$r->errors = $attempts_error;
			}

			if ($is_valid) {
				$confirm_code = $r->get_value("confirm_code");
				if ($confirm_code == $transfer_code) {
					// check product and it's type 
					$item_id = ""; $item_type_id = "";
					$sql  = " SELECT item_id, item_type_id FROM ".$table_prefix."orders_items ";
					$sql .= " WHERE order_item_id=".$db->tosql($order_item_id, INTEGER);
					$db->query($sql);
					if ($db->next_record()) {
						$item_id = $db->f("item_id");
						$item_type_id = $db->f("item_type_id");
					}

					// start voucher transfer 
					// 1. deduct transfer amount and fee from main coupon
					$sql  = " UPDATE " . $table_prefix . "coupons SET ";
					$sql .= " coupon_uses=coupon_uses+1";
					$sql .= ", discount_amount=discount_amount-".$db->tosql($transfer_amount+$cash_out_fee, NUMBER);
					$sql .= " WHERE coupon_id=" . $db->tosql($voucher_id, INTEGER);
					$db->query($sql);
			
					// 2. create a new credit order
					$order_data = array();
					foreach ($transfer_data as $param_name => $param_value) {
						$delivery_param = "show_delivery_" . $param_name;
						$delivery_param_value = get_setting_value($order_info, $delivery_param);
						$order_data[$param_name] = $param_value;
						if ($delivery_param_value == 1) {
							$order_data["delivery_".$param_name] = $param_value;
						}
					}
					// set general order parameters
					$order_data["user_id"] = $user_id;
					$order_data["user_type_id"] = $user_type_id;
					$order_data["goods_total"] = -($transfer_amount+$cash_out_fee);
					$order_data["order_total"] = -($transfer_amount+$cash_out_fee);
					$order_data["order_status"] = $cash_out_status_id;
					// set currency parameters
					$order_data["default_currency_code"] = $default_currency_code;
					$order_data["currency_code"] = $default_currency_code;
					$order_data["currency_rate"] = 1;
					$order_data["payment_currency_code"] = $default_currency_code;
					$order_data["payment_currency_rate"] = 1;

					$item_coupon = array(
						"id" => $voucher_id,
						"code" => $voucher_code,
						"title" => $voucher_title,
						"type" => $voucher_type,
						"discount" => $transfer_amount,
					);
					$order_item = array(
						"item_id" => $item_id,
						"item_type_id" => $item_type_id,
						"item_name" => CASH_OUT_MSG,
						"item_code" => $voucher_code,
						"full_buying_price" => 0,
						"full_real_price" => -$transfer_amount,
						"full_price" => -$transfer_amount,
						"price_incl_tax" => -$transfer_amount,
						"quantity" => 1,
						"is_shipping_free" => 1,
						"item_code" => $voucher_code,
						"coupons_ids" => $voucher_id,
						"coupons" => array($item_coupon),
					);
					$order_data["items"] = array($order_item);
					if ($cash_out_fee > 0) {
						// add fee item
						$item_coupon = array(
							"id" => $voucher_id, "code" => $voucher_code, "title" => $voucher_title, "type" => $voucher_type,
							"discount" => $cash_out_fee,
						);
						$order_item = array(
							"item_id" => $item_id,
							"item_type_id" => $item_type_id,
							"item_name" => CASH_OUT_FEE_MSG,
							"item_code" => $voucher_code,
							"full_buying_price" => 0,
							"full_real_price" => -$cash_out_fee,
							"full_price" => -$cash_out_fee,
							"price_incl_tax" => -$cash_out_fee,
							"quantity" => 1,
							"is_shipping_free" => 1,
							"item_code" => $voucher_code,
							"coupons_ids" => $voucher_id,
							"coupons" => array($item_coupon),
						);
						$order_data["items"][] = $order_item;
					}

					$order_id = create_order($order_data);

					// 3. add appropriate event 
					$ce->set_value("coupon_id", $voucher_id);
					$ce->set_value("order_id", $order_id);
					$ce->set_value("payment_id", "");
					$ce->set_value("transaction_id", "");
					$ce->set_value("admin_id", get_session("session_admin_id"));
					$ce->set_value("user_id", $user_id);
					$ce->set_value("from_user_id", $user_id);
					$ce->set_value("to_user_id", $user_id);
					$ce->set_value("event_date", va_time());
					$ce->set_value("event_type", "voucher_cashed_out");
					$ce->set_value("remote_ip", get_ip());
					$ce->set_value("coupon_amount", -$transfer_amount);
					$ce->insert_record();
					if ($cash_out_fee > 0) {
						$ce->set_value("event_type", "cash_out_fee");
						$ce->set_value("coupon_amount", -$cash_out_fee);
						$ce->insert_record();
					}

					// 5. clear transfer data
					$sql  = " UPDATE " . $table_prefix . "coupons ";
					$sql .= " SET transfer_user_id=NULL ";
					$sql .= " , transfer_amount=NULL ";
					$sql .= " , transfer_type=NULL ";
					$sql .= " , transfer_code=NULL ";
					$sql .= " , transfer_date=NULL ";
					$sql .= " , transfer_errors=0 ";
					$sql .= " WHERE coupon_id=" . $db->tosql($voucher_id, INTEGER);
					$db->query($sql);

					// send confirm message to user
					$cash_confirm_notify = get_setting_value($voucher_settings, "cash_confirm_notify", "");
					$cash_confirm_sms_notify = get_setting_value($voucher_settings, "cash_confirm_sms_notify", "");
		    
					$t->set_var("transfer_code", htmlspecialchars($transfer_code));
					$t->set_var("transfer_expiration", va_date($datetime_show_format, $transfer_expiration)); 
					$t->set_var("transfer_amount", currency_format($transfer_amount));
					$t->set_var("voucher_amount", currency_format($voucher_amount));
					$t->set_var("voucher_code", htmlspecialchars($voucher_code));
		    
					if ($cash_confirm_notify && $sender_email)
					{
						$cash_confirm_subject = get_setting_value($voucher_settings, "cash_confirm_subject", va_constant("CONFIRMATION_MSG"));
						$cash_confirm_message = get_setting_value($voucher_settings, "cash_confirm_message", $transfer_code);
		    
						$t->set_block("cash_confirm_subject", $cash_confirm_subject);
						$t->set_block("cash_confirm_message", $cash_confirm_message);
          
						$mail_to = $sender_email;
						$mail_from = get_setting_value($voucher_settings, "cash_confirm_from", $settings["admin_email"]);
						$email_headers = array();
						$email_headers["from"] = parse_value($mail_from);
						$email_headers["cc"] = get_setting_value($voucher_settings, "cash_confirm_cc");
						$email_headers["bcc"] = get_setting_value($voucher_settings, "cash_confirm_bcc");
						$email_headers["reply_to"] = get_setting_value($voucher_settings, "cash_confirm_reply_to");
						$email_headers["return_path"] = get_setting_value($voucher_settings, "cash_confirm_return_path");
						$email_headers["mail_type"] = get_setting_value($voucher_settings, "cash_confirm_message_type");
          
						$t->parse("cash_confirm_subject", false);
						$t->parse("cash_confirm_message", false);
						$cash_confirm_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("cash_confirm_message"));
						va_mail($mail_to, $t->get_var("cash_confirm_subject"), $cash_confirm_message, $email_headers);
					}

					// show message for user
					$transfer_done = true;
					$transfer_message = va_constant("CASH_OUT_SENT_DESC");
					$transfer_message = str_replace("{transfer_amount}", currency_format($transfer_amount), $transfer_message);
				} else {
					// increase number of errors 
					$is_valid = false;
					$r->errors = va_constant("TRANSFER_CODE_ERROR");
					$transfer_errors++;
					$sql  = " UPDATE " . $table_prefix . "coupons ";
					$sql .= " SET transfer_errors=" . $db->tosql($transfer_errors, INTEGER);
					$sql .= " WHERE coupon_id=" . $db->tosql($voucher_id, INTEGER);
					$db->query($sql);
					$r->set_value("confirm_code", "");
				}
			}
		}
		$r->set_parameters();
		if ($transfer_done) {
			$t->set_var("transfer_message", $transfer_message);
			$t->parse("transfer_finished", false);
		} else {
			$t->parse("confirm_form", false);
		}
	} else {
		if (!$voucher_active) {
			$r->errors = va_constant("COUPON_NON_ACTIVE_MSG");
		} else if ($attempts_error) {
			$r->errors = $attempts_error;
		}

		$r->set_parameters();
		$t->parse("transfer_form", false);
	}

	$block_parsed = true;

