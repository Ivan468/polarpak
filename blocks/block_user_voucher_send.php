<?php

	include_once("./includes/profile_functions.php");

	$default_title = "{SEND_VOUCHER_MSG}";

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

	$eol = get_eol();
	$user_id = get_session("session_user_id");
	$user_info = get_session("session_user_info");
	$sender_name = get_setting_value($user_info, "name", "");
	$sender_email = get_setting_value($user_info, "email", "");
	$sender_cell_phone = get_setting_value($user_info, "cell_phone", "");

	$is_valid = true;
	$voucher_id = get_param("voucher_id");
	$operation = get_param("operation");

	// check default voucher settings
	$voucher_settings = get_settings("user_voucher");
	$default_voucher_transfer = get_setting_value($voucher_settings, "voucher_transfer", "");
	$default_transfer_time_limit = get_setting_value($voucher_settings, "transfer_time_limit", 30); // 30 minutes by default
	$default_transfer_fee_percent = get_setting_value($voucher_settings, "transfer_fee_percent", 0); 
	$default_transfer_fee_amount = get_setting_value($voucher_settings, "transfer_fee_amount", 0); 
	$transfer_attempts = get_setting_value($voucher_settings, "transfer_attempts", 5); // how many attempts allowed for user

	// check user settings
	$user_settings = user_settings($user_id);
	$voucher_transfer = get_setting_value($user_settings, "voucher_transfer", $default_voucher_transfer);
	$transfer_time_limit = get_setting_value($user_settings, "transfer_time_limit", $default_transfer_time_limit);
	$transfer_fee_percent = get_setting_value($user_settings, "transfer_fee_percent", $default_transfer_fee_percent); 
	$transfer_fee_amount = get_setting_value($user_settings, "transfer_fee_amount", $default_transfer_fee_amount); 

	$valid_time = ($transfer_time_limit * 60);
	$current_ts = va_timestamp();
	$transfer_fee_desc = "";
	if ($transfer_fee_percent) {
		$transfer_fee_desc = $transfer_fee_percent."%";
	}
	if ($transfer_fee_amount) {
		if ($transfer_fee_desc) { $transfer_fee_desc .= " + "; }
		$transfer_fee_desc .= currency_format($transfer_fee_amount);
	}

	if (!$voucher_transfer) {
		header("Location: user_home.php");
		exit;
	}

	$sql  = " SELECT * FROM " . $table_prefix . "coupons c ";
	$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
	$sql .= " AND coupon_id=" . $db->tosql($voucher_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$voucher_active = $db->f("is_active");
		$order_id = $db->f("order_id");
		$order_item_id = $db->f("order_item_id");

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
		$transfer_errors = $db->f("transfer_errors");
		$attempts_error = "";
		if ($transfer_type != "TRANSFER") {
			$transfer_code = "";
		}

		if ($current_ts > ($transfer_ts + $valid_time) || $transfer_amount > $voucher_amount) {
			// clear transfer data if transfer time expired or transfer amount become greater than voucher amount left
			$transfer_errors = 0; $transfer_code = "";
			$sql  = " UPDATE " . $table_prefix . "coupons ";
			$sql .= " SET transfer_user_id=NULL ";
			$sql .= " , transfer_amount=NULL ";
			$sql .= " , transfer_type=NULL ";
			$sql .= " , transfer_code=NULL ";
			$sql .= " , transfer_date=NULL ";
			$sql .= " , transfer_data=NULL ";
			$sql .= " , transfer_errors=0 ";
			$sql .= " WHERE coupon_id=" . $db->tosql($voucher_id, INTEGER);
			$db->query($sql);
		} else if ($transfer_errors >= $transfer_attempts) {
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
				$sql .= " , transfer_amount=NULL ";
				$sql .= " , transfer_type=NULL ";
				$sql .= " , transfer_data=NULL ";
				$sql .= " , transfer_code=NULL ";
				$sql .= " WHERE coupon_id=" . $db->tosql($voucher_id, INTEGER);
				$db->query($sql);
			}
		}
	} else {
		header("Location: user_home.php");
		exit;
	}

	$html_template = get_setting_value($block, "html_template", "block_user_voucher_send.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("user_vouchers_href",  get_custom_friendly_url("user_vouchers.php"));
	$t->set_var("user_voucher_href",   get_custom_friendly_url("user_voucher.php"));
	$t->set_var("user_voucher_send_href",   get_custom_friendly_url("user_voucher_send.php"));
	$t->set_var("user_home_href", get_custom_friendly_url("user_home.php"));
	$t->set_var("voucher_amount", currency_format($voucher_amount));
	$t->set_var("default_currency_code", htmlspecialchars($default_currency_code));
	if ($transfer_fee_desc) {
		$t->set_var("transfer_fee_desc", htmlspecialchars($transfer_fee_desc));
		$t->parse("transfer_fee_desc_block", false);
	}


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

	$max_voucher_amount = $voucher_amount - $transfer_fee_amount;
 	$max_voucher_amount = ($max_voucher_amount * 100) / (100 + $transfer_fee_percent);
	$max_voucher_amount = round($max_voucher_amount, 2);

	$r = new VA_Record("");
	$r->add_hidden("voucher_id", INTEGER);
	$r->add_textbox("confirm_code", INTEGER, TRANSFER_CODE_MSG);
	$r->add_textbox("transfer_amount", NUMBER, TRANSFER_AMOUNT_MSG);
	$r->add_textbox("transfer_email", TEXT, RECEIVER_EMAIL_MSG);

	$r->get_form_parameters();

	if ($operation == "send") {
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
			$r->change_property("transfer_amount", REQUIRED, true);
			$r->change_property("transfer_amount", MIN_VALUE, 0.01);
			$r->change_property("transfer_amount", MAX_VALUE, $max_voucher_amount);
			$r->change_property("transfer_email", REQUIRED, true);
			$r->change_property("transfer_email", REGEXP_MASK, EMAIL_REGEXP);
			$r->change_property("transfer_email", REGEXP_ERROR, INCORRECT_EMAIL_MESSAGE);
			$is_valid = $r->validate();
		}
		if ($is_valid) {
			$transfer_email = $r->get_value("transfer_email");
			$sql  = " SELECT user_id FROM ".$table_prefix."users ";
			$sql .= " WHERE email=" . $db->tosql($transfer_email, TEXT);
			$db->query($sql);
			if ($db->next_record()) {
				$transfer_user_id = $db->f("user_id");
				if ($user_id == $transfer_user_id) {
					$is_valid = false;
					$r->errors = va_constant("RECEIVER_OWNER_ERROR");
				}
			} else {
				$is_valid = false;
				$r->errors = va_constant("RECEIVER_NOT_FOUND_ERROR");
			}
			if ($is_valid) {
				$transfer_amount = $r->get_value("transfer_amount");
				$transfer_code = mt_rand (100000, 999999);
				$transfer_date = va_time();
				$transfer_ts = va_timestamp($transfer_date);
				$transfer_expiration = $transfer_ts + $valid_time;

				$sql  = " UPDATE " . $table_prefix . "coupons ";
				$sql .= " SET transfer_user_id=" . $db->tosql($transfer_user_id, INTEGER);
				$sql .= " , transfer_type='TRANSFER' ";
				$sql .= " , transfer_amount=" . $db->tosql($transfer_amount, NUMBER);
				$sql .= " , transfer_code=" . $db->tosql($transfer_code, TEXT);
				$sql .= " , transfer_date=" . $db->tosql($transfer_date, DATETIME);
				$sql .= " , transfer_errors=" . $db->tosql($transfer_errors, INTEGER);
				$sql .= " WHERE coupon_id=" . $db->tosql($voucher_id, INTEGER);
				$db->query($sql);

				// send transfer code to user
				$request_notify = get_setting_value($voucher_settings, "request_notify", "");
				$request_sms_notify = get_setting_value($voucher_settings, "request_sms_notify", "");

				$t->set_var("transfer_code", htmlspecialchars($transfer_code));
				$t->set_var("transfer_expiration", va_date($datetime_show_format, $transfer_expiration)); 
				$t->set_var("transfer_amount", currency_format($transfer_amount));
				$t->set_var("voucher_amount", currency_format($voucher_amount));
				$t->set_var("voucher_code", htmlspecialchars($voucher_code));

				if ($request_notify && $sender_email)
				{
					$request_subject = get_setting_value($voucher_settings, "request_subject", va_constant("TRANSFER_CODE_MSG"));
					$request_message = get_setting_value($voucher_settings, "request_message", $transfer_code);

					$t->set_block("request_subject", $request_subject);
					$t->set_block("request_message", $request_message);
        
					$mail_to = $sender_email;
					$mail_from = get_setting_value($voucher_settings, "request_from", $settings["admin_email"]);
					$email_headers = array();
					$email_headers["from"] = parse_value($mail_from);
					$email_headers["cc"] = get_setting_value($voucher_settings, "request_cc");
					$email_headers["bcc"] = get_setting_value($voucher_settings, "request_bcc");
					$email_headers["reply_to"] = get_setting_value($voucher_settings, "request_reply_to");
					$email_headers["return_path"] = get_setting_value($voucher_settings, "request_return_path");
					$email_headers["mail_type"] = get_setting_value($voucher_settings, "request_message_type");
        
					$t->parse("request_subject", false);
					$t->parse("request_message", false);
					$request_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("request_message"));
					va_mail($mail_to, $t->get_var("request_subject"), $request_message, $email_headers);
				}

				if ($request_sms_notify)
				{
					$request_sms_recipient  = get_setting_value($voucher_settings, "request_sms_recipient", $sender_cell_phone);
					$request_sms_originator = get_setting_value($voucher_settings, "request_sms_originator", "");
					$request_sms_message    = get_setting_value($voucher_settings, "request_sms_message", $access_code);
			  
					$t->set_block("request_sms_recipient",  $request_sms_recipient);
					$t->set_block("request_sms_originator", $request_sms_originator);
					$t->set_block("request_sms_message",    $request_sms_message);
			  
					$t->parse("request_sms_recipient", false);
					$t->parse("request_sms_originator", false);
					$t->parse("request_sms_message", false);

					$request_sms_recipient = $t->get_var("request_sms_recipient");
			  
					if ($request_sms_recipient) {
						$sms_sent = sms_send($request_sms_recipient, $t->get_var("request_sms_message"), $t->get_var("request_sms_originator"), $sms_errors);
						if (!$sms_sent) {
							$is_valid = false;
							$r->errors .= "SMS Gateway Error: " . $sms_errors . "<br>";
						}
					}
				}
				// end transfer code send  
			}
		}
	} else if ($operation == "cancel") {
		// clear transfer data but exclude errors field and date to prevent following hack attempts
		$sql  = " UPDATE " . $table_prefix . "coupons ";
		$sql .= " SET transfer_user_id=NULL ";
		$sql .= " , transfer_amount=NULL ";
		$sql .= " , transfer_code=NULL ";
		$sql .= " , transfer_type=NULL ";
		$sql .= " , transfer_data=NULL ";
		$sql .= " WHERE coupon_id=" . $db->tosql($voucher_id, INTEGER);
		$db->query($sql);

		header("Location: user_vouchers.php");
		exit;
	}

	$receiver_name = ""; $receiver_email = "";
	if ($transfer_code) {
		// calculate transfer fee if available
		$transfer_fee  = $transfer_fee_amount;
		$transfer_fee += round(($transfer_amount * $transfer_fee_percent) / 100, 2);

		$transfer_done = false; // if transfer was succefull
		$sql  = " SELECT user_id, login, email, name, first_name, last_name, nickname, company_name ";
		$sql .= " FROM " . $table_prefix . "users u ";
		$sql .= " WHERE user_id=" . $db->tosql($transfer_user_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$receiver_user = $db->Record;
			$receiver_name = get_user_name($receiver_user);
			$receiver_email = $db->f("email");
		}
		$t->set_var("receiver_name", htmlspecialchars($receiver_name));
		$t->set_var("receiver_email", htmlspecialchars($receiver_email));
		$t->set_var("receiver_amount", currency_format($transfer_amount));
		$t->set_var("receiver_amount", currency_format($transfer_amount));
		$t->set_var("transfer_expiration", va_date($datetime_show_format, $transfer_expiration)); 
		$t->set_var("code_valid_till", va_date($datetime_show_format, $transfer_expiration)); 
		if ($transfer_fee) {
			$t->set_var("transfer_fee", currency_format($transfer_fee)); 
			$t->parse("transfer_fee_block", false); 
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
					// start voucher transfer 
					// 1. deduct transfer amount from main coupon
					$sql  = " UPDATE " . $table_prefix . "coupons SET ";
					$sql .= " coupon_uses=coupon_uses+1";
					$sql .= ", discount_amount=discount_amount-".$db->tosql($transfer_amount+$transfer_fee, NUMBER);
					$sql .= " WHERE coupon_id=" . $db->tosql($voucher_id, INTEGER);
					$db->query($sql);

					// 2. add appropriate event for sender voucher
					$ce->set_value("coupon_id", $voucher_id);
					$ce->set_value("order_id", "");
					$ce->set_value("payment_id", "");
					$ce->set_value("transaction_id", "");
					$ce->set_value("admin_id", get_session("session_admin_id"));
					$ce->set_value("user_id", $user_id);
					$ce->set_value("from_user_id", $user_id);
					$ce->set_value("to_user_id", $transfer_user_id);
					$ce->set_value("event_date", va_time());
					$ce->set_value("event_type", "voucher_sent");
					$ce->set_value("remote_ip", get_ip());
					$ce->set_value("coupon_amount", -$transfer_amount);
					$ce->insert_record();
					if ($transfer_fee > 0) {
						$ce->set_value("event_type", "transfer_fee");
						$ce->set_value("coupon_amount", -$transfer_fee);
						$ce->insert_record();
					}
					
					// 3. add new voucher for receiver
					$new_voucher_code = "";
					while ($new_voucher_code == "") {
						$random_value = mt_rand();
						$voucher_hash = strtoupper(md5($order_id . $order_item_id . $transfer_amount. $random_value . va_timestamp()));
						$new_voucher_code = substr($voucher_hash, 0, 8);
						$sql = " SELECT coupon_id FROM " .$table_prefix. "coupons WHERE coupon_code=" . $db->tosql($new_voucher_code, TEXT);
						$db->query($sql);
						if ($db->next_record()) {
							$new_voucher_code = "";
						}
					}
	      
					$vr->set_value("parent_coupon_id", $voucher_id);
					$vr->set_value("order_id", $order_id);
					$vr->set_value("order_item_id", $order_item_id);
					$vr->set_value("user_id", $transfer_user_id);
					$vr->set_value("coupon_code", $new_voucher_code);
					$vr->set_value("coupon_title", $voucher_title);
					$vr->set_value("is_active", 1);
					$vr->set_value("discount_type", 8);
					$vr->set_value("discount_amount", $transfer_amount);
					$vr->set_value("quantity_limit", 0);
					$vr->set_value("coupon_uses", 0);
	      
					$vr->insert_record();
					$new_voucher_id = $db->last_insert_id();

					// 4. add new event for new voucher 
					$ce->set_value("coupon_id", $new_voucher_id);
					$ce->set_value("order_id", "");
					$ce->set_value("payment_id", "");
					$ce->set_value("transaction_id", "");
					$ce->set_value("admin_id", get_session("session_admin_id"));
					$ce->set_value("user_id", $user_id);
					$ce->set_value("from_user_id", $user_id);
					$ce->set_value("to_user_id", $transfer_user_id);
					$ce->set_value("event_date", va_time());
					$ce->set_value("event_type", "voucher_received");
					$ce->set_value("remote_ip", get_ip());
					$ce->set_value("coupon_amount", $transfer_amount);
					$ce->insert_record();

					// 5. clear transfer data
					$sql  = " UPDATE " . $table_prefix . "coupons ";
					$sql .= " SET transfer_user_id=NULL ";
					$sql .= " , transfer_amount=NULL ";
					$sql .= " , transfer_code=NULL ";
					$sql .= " , transfer_date=NULL ";
					$sql .= " , transfer_type=NULL ";
					$sql .= " , transfer_data=NULL ";
					$sql .= " , transfer_errors=0 ";
					$sql .= " WHERE coupon_id=" . $db->tosql($voucher_id, INTEGER);
					$db->query($sql);

					// send notification to sender and receiver
					$sender_notify = get_setting_value($voucher_settings, "sender_notify", "");
					$receiver_notify = get_setting_value($voucher_settings, "receiver_notify", "");
			  
					$t->set_var("sender_name", htmlspecialchars($sender_name));
					$t->set_var("sender_email", htmlspecialchars($sender_email));
					$t->set_var("receiver_name", htmlspecialchars($receiver_name));
					$t->set_var("receiver_email", htmlspecialchars($receiver_email));
					$t->set_var("transfer_code", htmlspecialchars($voucher_code));
					$t->set_var("voucher_code", htmlspecialchars($voucher_code));
					$t->set_var("old_transfer_code", htmlspecialchars($voucher_code));
					$t->set_var("old_voucher_code", htmlspecialchars($voucher_code));
					$t->set_var("new_transfer_code", htmlspecialchars($new_voucher_code));
					$t->set_var("new_voucher_code", htmlspecialchars($new_voucher_code));
					$t->set_var("transfer_amount", currency_format($transfer_amount));
					$t->set_var("voucher_amount", currency_format($transfer_amount));
			  
			  
					if ($sender_notify && $sender_email)
					{
						$sender_subject = get_setting_value($voucher_settings, "sender_subject", va_constant("TRANSFER_CODE_MSG"));
						$sender_message = get_setting_value($voucher_settings, "sender_message", $voucher_code);
			  
						$t->set_block("sender_subject", $sender_subject);
						$t->set_block("sender_message", $sender_message);
          
						$mail_to = $sender_email;
						$mail_from = get_setting_value($voucher_settings, "sender_from", $settings["admin_email"]);
						$email_headers = array();
						$email_headers["from"] = parse_value($mail_from);
						$email_headers["cc"] = get_setting_value($voucher_settings, "sender_cc");
						$email_headers["bcc"] = get_setting_value($voucher_settings, "sender_bcc");
						$email_headers["reply_to"] = get_setting_value($voucher_settings, "sender_reply_to");
						$email_headers["return_path"] = get_setting_value($voucher_settings, "sender_return_path");
						$email_headers["mail_type"] = get_setting_value($voucher_settings, "sender_message_type");
          
						$t->parse("sender_subject", false);
						$t->parse("sender_message", false);
						$sender_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("sender_message"));
						va_mail($mail_to, $t->get_var("sender_subject"), $sender_message, $email_headers);
					}
			  
					if ($receiver_notify && $receiver_email)
					{
			  
						$t->set_var("transfer_code", htmlspecialchars($new_voucher_code));
						$t->set_var("voucher_code", htmlspecialchars($new_voucher_code));
			  
						$receiver_subject = get_setting_value($voucher_settings, "receiver_subject", va_constant("TRANSFER_CODE_MSG"));
						$receiver_message = get_setting_value($voucher_settings, "receiver_message", $voucher_code);
			  
						$t->set_block("receiver_subject", $receiver_subject);
						$t->set_block("receiver_message", $receiver_message);
          
						$mail_to = $receiver_email;
						$mail_from = get_setting_value($voucher_settings, "receiver_from", $settings["admin_email"]);
						$email_headers = array();
						$email_headers["from"] = parse_value($mail_from);
						$email_headers["cc"] = get_setting_value($voucher_settings, "receiver_cc");
						$email_headers["bcc"] = get_setting_value($voucher_settings, "receiver_bcc");
						$email_headers["reply_to"] = get_setting_value($voucher_settings, "receiver_reply_to");
						$email_headers["return_path"] = get_setting_value($voucher_settings, "receiver_return_path");
						$email_headers["mail_type"] = get_setting_value($voucher_settings, "receiver_message_type");
          
						$t->parse("receiver_subject", false);
						$t->parse("receiver_message", false);
						$receiver_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("receiver_message"));
						va_mail($mail_to, $t->get_var("receiver_subject"), $receiver_message, $email_headers);
					}

					// show message for user
					$transfer_done = true;
					$transfer_message = va_constant("TRANSFER_SENT_DESC");
					$transfer_message = str_replace("{transfer_amount}", currency_format($transfer_amount), $transfer_message);
					$transfer_message = str_replace("{receiver_name}", $receiver_name, $transfer_message);
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

