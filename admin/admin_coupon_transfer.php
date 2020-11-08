<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_coupon_transfer.php                                ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/profile_functions.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");

	$eol = get_eol();
	$operation = get_param("operation");
	$coupon_id = get_param("coupon_id");
	$voucher_id = $coupon_id;

	check_admin_security("coupons");
	check_admin_security("order_vouchers");

	// check coupon data	
	$coupon_data = array(); $discount_type = 0;
	$sql  = " SELECT * FROM ".$table_prefix."coupons "; 
	$sql .= " WHERE coupon_id=" . $db->tosql($coupon_id, INTEGER); 
	$db->query($sql);
	if ($db->next_record()) {
		$coupon_data = $db->Record;
		$owner_user_id = $db->f("user_id"); 
		$discount_type = $db->f("discount_type");
		$voucher_code = $db->f("coupon_code");
		$voucher_amount = $db->f("discount_amount");
		$voucher_title = $db->f("coupon_title");
		$voucher_active = $db->f("is_active");
		$order_id = $db->f("order_id");
		$order_item_id = $db->f("order_item_id");
	}

	// transfer option allowed only for user voucher type - 8
	if ($discount_type != 8) { 
		header("Location: admin_coupons.php");
		exit;
	}

	// get owner data
	$owner_user_name = ""; $owner_user_email = ""; $owner_settings = array();
	$sql  = " SELECT * ";
	$sql .= " FROM " . $table_prefix . "users u ";
	$sql .= " WHERE user_id=" . $db->tosql($owner_user_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$owner_user = $db->Record;
		$owner_user_name = get_user_name($owner_user);
		$owner_user_email = $db->f("email");
		$owner_settings = $db->f("user_settings");
		if ($owner_settings) {
			$owner_settings = json_decode($owner_settings, true);
		}
	}

	// check default currency
	$default_currency_code = get_db_value("SELECT currency_code FROM ".$table_prefix."currencies WHERE is_default=1");
	$currency = get_currency($default_currency_code);

	// check voucher fee settings
	$voucher_settings = get_settings("user_voucher");
	$default_fee_percent = get_setting_value($voucher_settings, "transfer_fee_percent", 0); 
	$default_fee_amount = get_setting_value($voucher_settings, "transfer_fee_amount", 0); 
	$transfer_fee_percent = get_setting_value($owner_settings, "transfer_fee_percent", $default_fee_percent); 
	$transfer_fee_amount = get_setting_value($owner_settings, "transfer_fee_amount", $default_fee_amount); 
	$transfer_fee_percent = doubleval($transfer_fee_percent);
	$transfer_fee_amount = doubleval($transfer_fee_amount);
	$current_ts = va_timestamp();
	$transfer_fee_desc = "";
	if ($transfer_fee_percent > 0) {
		$transfer_fee_desc = $transfer_fee_percent."%";
	}
	if ($transfer_fee_amount > 0) {
		if ($transfer_fee_desc) { $transfer_fee_desc .= " + "; }
		$transfer_fee_desc .= currency_format($transfer_fee_amount);
	}

	$is_record_controls = false; // global variable to prevent double call of function set_record_controls
	
	$s = get_param("s");
	$s_a = get_param("s_a");
	$discount_type = get_param("discount_type");
	$date_format_msg = str_replace("{date_format}", join("", $date_edit_format), DATE_FORMAT_MSG);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_coupon_transfer.html");

	$t->set_var("operation", htmlspecialchars($operation));
	$t->set_var("voucher_amount", currency_format($voucher_amount));
	$t->set_var("owner_user_id", htmlspecialchars($owner_user_id));
	$t->set_var("owner_user_name", htmlspecialchars($owner_user_name));
	$t->set_var("owner_user_email", htmlspecialchars($owner_user_email));
	$t->set_var("transfer_fee_percent", round($transfer_fee_percent, 2));
	$t->set_var("transfer_fee_amount", round($transfer_fee_amount, 2));
	$t->set_var("transfer_fee_desc", htmlspecialchars($transfer_fee_desc));

	$t->set_var("currency_json", htmlspecialchars(json_encode($currency)));
	$t->set_var("currency_code", htmlspecialchars($currency["code"]));
	$t->set_var("currency_left", htmlspecialchars($currency["left"]));
	$t->set_var("currency_right", htmlspecialchars($currency["right"]));
	$t->set_var("currency_decimals", htmlspecialchars($currency["decimals"]));
	$t->set_var("currency_point", htmlspecialchars($currency["point"]));
	$t->set_var("currency_separator", htmlspecialchars($currency["separator"]));

	$t->set_var("date_format_msg", $date_format_msg);
	$t->set_var("date_edit_format", join("", $date_edit_format));
	$t->set_var("date_added_format", join("", $date_edit_format));
	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_coupon_href", "admin_coupon.php");
	$t->set_var("admin_coupons_href", "admin_coupons.php");
	$t->set_var("admin_user_href", "admin_user.php");
	$t->set_var("admin_users_select_href", "admin_users_select.php");

	$t->set_var("admin_orders_href",  "admin_orders.php");
	$t->set_var("admin_order_href",   $order_details_site_url . "admin_order.php");
	$t->set_var("admin_order_vouchers_href", "admin_order_vouchers.php");
	$t->set_var("admin_coupon_transfer_href", "admin_coupon_transfer.php");

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

	$r = new VA_Record("");
	$r->add_hidden("coupon_id", INTEGER);
	$r->add_hidden("s_n", TEXT);
	$r->add_hidden("s_a", TEXT);
	$r->add_hidden("s_dt", TEXT);
	$r->add_hidden("page", INTEGER);
	$r->add_hidden("sort_ord", TEXT);
	$r->add_hidden("sort_dir", TEXT);

	$r->add_textbox("receiver_user_id", INTEGER, VOUCHER_RECEIVER_MSG);
	$r->change_property("receiver_user_id", REQUIRED, true);
	$r->add_textbox("transfer_amount", NUMBER, TRANSFER_AMOUNT_MSG);
	$r->change_property("transfer_amount", REQUIRED, true);
	$r->change_property("transfer_amount", MIN_VALUE, 0.01);
	$r->add_textbox("transfer_fee", NUMBER, TRANSFER_FEE_MSG);
	$r->change_property("transfer_fee", MIN_VALUE, 0);
	$r->add_textbox("total_amount", NUMBER, TOTAL_AMOUNT_MSG);
	$r->change_property("total_amount", MAX_VALUE, $voucher_amount);
	$r->get_form_parameters();
	$total_amount = doubleval($r->get_value("transfer_amount")) + doubleval($r->get_value("transfer_fee"));
	$r->set_value("total_amount", $total_amount);
	// build admin edit url
	$r->return_page = "admin_coupon.php";
	$coupon_url = $r->get_return_url();
	// prepare data for return page
	$r->return_page  = "admin_coupons.php";
	$r->change_property("coupon_id", TRANSFER, false);
	$coupons_url = $r->get_return_url();

	$custom_breadcrumb = array(
		"admin_global_settings.php" => SETTINGS_MSG,
		"admin_products_settings.php" => PRODUCTS_MSG,
		$coupons_url => COUPONS_MSG,
		$coupon_url => $voucher_title,
		"#" => VOUCHER_TRANSFER_MSG,
	);

	// get receiver user data
	$receiver_user_id = $r->get_value("receiver_user_id");
	$receiver_user_name = ""; $receiver_user_email = "";
	if ($receiver_user_id) {
		$sql  = " SELECT * ";
		$sql .= " FROM ".$table_prefix."users u ";
		$sql .= " WHERE user_id=" . $db->tosql($receiver_user_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$receiver_user = $db->Record;
			$receiver_user_name = get_user_name($receiver_user);
			$receiver_user_email = $db->f("email");
		}
	}

	if ($operation == "send" || $operation == "confirm") {
		$is_valid = true;
		if (!$voucher_active) {
			$is_valid = false;
			$r->errors = va_constant("COUPON_NON_ACTIVE_MSG");
		}
  	if ($is_valid) {
			$is_valid = $r->validate();
		}
  	if ($is_valid) {
			$receiver_user_id = $r->get_value("receiver_user_id");
			if ($owner_user_id == $receiver_user_id) {
				$is_valid = false;
				$r->errors = va_constant("RECEIVER_OWNER_ERROR");
			}
		}
		if ($is_valid) {
			if ($operation == "send") {
				$operation = "confirm";
			} else {
				// start voucher transfer 
				$transfer_amount = $r->get_value("transfer_amount");
				$transfer_fee = $r->get_value("transfer_fee");
				$total_amount = $transfer_amount+$transfer_fee;
				$voucher_amount -= $total_amount;

				// 1. deduct transfer amount from main coupon
				$sql  = " UPDATE " . $table_prefix . "coupons SET ";
				$sql .= " coupon_uses=coupon_uses+1";
				$sql .= ", discount_amount=discount_amount-".$db->tosql($total_amount, NUMBER);
				$sql .= " WHERE coupon_id=" . $db->tosql($voucher_id, INTEGER);
				$db->query($sql);

				// 2. add appropriate event for sender voucher
				$ce->set_value("coupon_id", $voucher_id);
				$ce->set_value("order_id", "");
				$ce->set_value("payment_id", "");
				$ce->set_value("transaction_id", "");
				$ce->set_value("admin_id", get_session("session_admin_id"));
				$ce->set_value("user_id", $owner_user_id);
				$ce->set_value("from_user_id", $owner_user_id);
				$ce->set_value("to_user_id", $receiver_user_id);
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
					$voucher_hash = strtoupper(md5($order_id . $order_item_id . $transfer_amount. $random_value . time()));
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
				$vr->set_value("user_id", $receiver_user_id);
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
				$ce->set_value("user_id", $owner_user_id);
				$ce->set_value("from_user_id", $owner_user_id);
				$ce->set_value("to_user_id", $receiver_user_id);
				$ce->set_value("event_date", va_time());
				$ce->set_value("event_type", "voucher_received");
				$ce->set_value("remote_ip", get_ip());
				$ce->set_value("coupon_amount", $transfer_amount);
				$ce->insert_record();

				// send notification to sender and receiver
				$sender_notify = get_setting_value($voucher_settings, "sender_notify", "");
				$receiver_notify = get_setting_value($voucher_settings, "receiver_notify", "");

				$t->set_var("sender_name", htmlspecialchars($owner_user_name));
				$t->set_var("sender_email", htmlspecialchars($owner_user_email));
				$t->set_var("receiver_name", htmlspecialchars($receiver_user_name));
				$t->set_var("receiver_email", htmlspecialchars($receiver_user_email));
				$t->set_var("transfer_code", htmlspecialchars($voucher_code));
				$t->set_var("voucher_code", htmlspecialchars($voucher_code));
				$t->set_var("old_transfer_code", htmlspecialchars($voucher_code));
				$t->set_var("old_voucher_code", htmlspecialchars($voucher_code));
				$t->set_var("new_transfer_code", htmlspecialchars($new_voucher_code));
				$t->set_var("new_voucher_code", htmlspecialchars($new_voucher_code));
				$t->set_var("transfer_amount", currency_format($transfer_amount));
				$t->set_var("voucher_amount", currency_format($transfer_amount));


				if ($sender_notify && $owner_user_email)
				{
					$sender_subject = get_setting_value($voucher_settings, "sender_subject", va_constant("TRANSFER_CODE_MSG"));
					$sender_message = get_setting_value($voucher_settings, "sender_message", $voucher_code);

					$t->set_block("sender_subject", $sender_subject);
					$t->set_block("sender_message", $sender_message);
        
					$mail_to = $owner_user_email;
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

				if ($receiver_notify && $receiver_user_email)
				{

					$t->set_var("transfer_code", htmlspecialchars($new_voucher_code));
					$t->set_var("voucher_code", htmlspecialchars($new_voucher_code));

					$receiver_subject = get_setting_value($voucher_settings, "receiver_subject", va_constant("TRANSFER_CODE_MSG"));
					$receiver_message = get_setting_value($voucher_settings, "receiver_message", $voucher_code);

					$t->set_block("receiver_subject", $receiver_subject);
					$t->set_block("receiver_message", $receiver_message);
        
					$mail_to = $receiver_user_email;
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
				$operation = "back";
				$transfer_message = va_constant("TRANSFER_SENT_DESC");
				$transfer_message = str_replace("{transfer_amount}", currency_format($transfer_amount), $transfer_message);
				$transfer_message = str_replace("{receiver_name}", $receiver_user_name, $transfer_message);
			}
		}
	} else if ($operation == "cancel") {
		$return_page = $r->get_return_url();
		header("Location: ".$return_page);
		exit;
	} else {
		$operation = "send";
	}

	if ($operation == "confirm" || $operation == "back") {
		$r->change_property("transfer_amount", PARSE_NAME, "confirm_amount");
		$r->change_property("transfer_fee", PARSE_NAME, "confirm_fee");
		$r->change_property("receiver_user_id", PARSE_NAME, "confirm_user");
		if ($operation == "confirm") {
			$t->parse("confirm_button", false);
			$t->parse("cancel_button", false);
			$t->parse("edit_button", false);
		} else {	
			$t->set_var("transfer_message", $transfer_message);
			$t->parse("transfer_finished", false);
			$t->parse("back_button", false);
		}
	} else {
		$t->parse("send_button", false);
		$t->parse("cancel_button", false);
	}

	$t->set_var("operation", htmlspecialchars($operation));
	$t->set_var("voucher_amount", currency_format($voucher_amount));
	$t->set_var("confirm_amount_desc", currency_format($r->get_value("transfer_amount")));
	$t->set_var("confirm_fee_desc", currency_format($r->get_value("transfer_fee")));
	// parse template
	$t->set_var("receiver_user_id", "[user_id]");
	$t->set_var("receiver_user_name", "[user_name]");
	$t->set_var("receiver_user_email", "[user_email]");
	$t->parse("user_template", false);
	// parse receiver user
	$receiver_user_id = $r->get_value("receiver_user_id");
	if ($receiver_user_id) {
		$t->set_var("receiver_user_id", $receiver_user_id);
		$t->set_var("receiver_user_name", $receiver_user_name);
		$t->set_var("receiver_user_email", $receiver_user_email);
		$t->parse_to("user_template", "selected_user", false);
	}
	$r->set_form_parameters();
	$t->set_var("total_amount", currency_format($r->get_value("total_amount")));

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");
	