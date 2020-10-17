<?php                           

	$default_title = SAVE_CART_TITLE;

	$html_template = get_setting_value($block, "html_template", "block_cart_save.html"); 
  $t->set_file("block_body", $html_template);

	$current_page = "cart_save.php";

	$operation = get_param("operation");
	$session_cart_id = get_session("saved_cart_id");

	if (!strlen($operation) && !$session_cart_id) {
		$shopping_cart = get_session("shopping_cart");
		$total_items = 0;
		if(is_array($shopping_cart)) {
			// check for active products in the cart
			foreach($shopping_cart as $cart_id => $item) {
				$item_id = $item["ITEM_ID"];
				$total_items++;
			}
		}
		if (!$total_items) {
			$rp = get_param("rp");
			$basket_page = strlen($rp) ? get_custom_friendly_url("basket.php") . "?rp=" . urlencode($rp) : get_custom_friendly_url("basket.php");
			header("Location: " . $basket_page);
			exit;
		}
	}

	// get contact us settings
	$saved_cart_settings = get_settings("saved_cart");
	$user_id = get_session("session_user_id");
	$user_info = get_session("session_user_info");
	$user_notify = get_setting_value($saved_cart_settings, "user_notify", 0);

	$t->set_var("basket_href",   get_custom_friendly_url("basket.php"));
	$t->set_var("current_href",  get_custom_friendly_url("basket.php"));
	$t->set_var("checkout_href", get_custom_friendly_url("checkout.php"));
	$t->set_var("products_href", get_custom_friendly_url("products_list.php"));
	$t->set_var("cart_save_href",get_custom_friendly_url("cart_save.php"));

	srand ((double) microtime() * 1000000);
	$new_random_value = rand();

	$discount_type = get_session("session_discount_type");
	$discount_amount = get_session("session_discount_amount");

	// set up return page
	$rp = get_param("rp");
	if(!$rp) { $rp = get_custom_friendly_url("products_list.php"); }
	$t->set_var("rp", htmlspecialchars($rp));

	$rnd = get_param("rnd");
	$session_rnd = get_session("session_rnd");

	$r = new VA_Record($table_prefix . "saved_carts");
	$r->add_where("cart_id", INTEGER);
	$r->add_textbox("site_id", INTEGER);
	$r->add_textbox("user_id", INTEGER);
	$r->add_textbox("cart_name", TEXT, CART_NAME_FIELD);
	$r->change_property("cart_name", REQUIRED, true);
	$r->add_textbox("cart_email", TEXT, EMAIL_FIELD);
	if (!$user_id) {
		$r->change_property("cart_email", REQUIRED, true);
	}
	$r->change_property("cart_email", REGEXP_MASK, EMAIL_REGEXP);
	$r->add_textbox("cart_total", NUMBER);
	$r->add_textbox("cart_added", DATETIME);
	$r->add_checkbox("cart_clear", INTEGER);

	if ($user_notify) {
		$t->parse("user_email_note", false);
	}

	$si = new VA_Record($table_prefix . "saved_items");
	$si->add_where("cart_item_id", INTEGER);
	$si->add_textbox("site_id", INTEGER);
	$si->add_textbox("item_id", INTEGER);
	$si->add_textbox("cart_id", INTEGER);
	$si->add_textbox("user_id", INTEGER);
	$si->add_textbox("item_name", TEXT);
	$si->add_textbox("quantity", INTEGER);
	$si->add_textbox("price", NUMBER);
	$si->add_textbox("date_added", DATETIME);

	$sip = new VA_Record($table_prefix . "saved_items_properties");
	$sip->add_where("item_property_id", INTEGER);
	$sip->add_textbox("cart_item_id", INTEGER);
	$sip->add_textbox("cart_id", INTEGER);
	$sip->add_textbox("property_id", INTEGER);
	$sip->add_textbox("property_value", TEXT);
	$sip->add_textbox("property_values_ids", TEXT);

	$rnd = get_param("rnd");
	$session_rnd = get_session("session_rnd");
	$success_message = "";
	$saved_cart_id = "";
	if(strlen($operation) && $rnd != $session_rnd) 
	{
		if ($operation == "cancel") {
			header("Location: " . get_custom_friendly_url("basket.php") . "?rp=" . urlencode($rp));
			exit;
		} 
		$r->get_form_values();

		$is_valid = $r->validate();

		if ($is_valid) {
			set_session("session_rnd", $rnd);

			// update current cart type and set new cart name and email data
			$db_cart_id = get_session("db_cart_id");
			$sql = " UPDATE ".$table_prefix."saved_carts ";
			$sql.= " SET cart_type=1 ";
			$sql.= " , cart_updated=".$db->tosql(va_time(), DATETIME);
			$sql.= " , cart_name=".$db->tosql($r->get_value("cart_name"), TEXT);
			$sql.= " , cart_email=".$db->tosql($r->get_value("cart_email"), TEXT);
			$sql.= " WHERE cart_id=".$db->tosql($db_cart_id, INTEGER);
			$db->query($sql);
			// clear saved cart information from cookie 
			va_cart_update(array("cartid" => "", "sessid" => ""));
			$saved_cart_id = $db_cart_id;
			set_session("saved_cart_id", $db_cart_id);

			// clear saved cart from session
			set_session("db_cart_id", ""); 
			set_session("shopping_cart", "");
			set_session("session_coupons", "");

			if (!$r->get_value("cart_clear")) {
				// automatically load saved cart as new cart if user would like to keep it in the cart
				cart_retrieve("retrieve", $db_cart_id);
			}


			// send notfication 
			$ip = get_ip();
			$eol = get_eol();
			$user_info = get_session("session_user_info");
			$admin_notify = get_setting_value($saved_cart_settings, "admin_notify", 0);
			$cart_email = $r->get_value("cart_email");
			$user_notify = get_setting_value($saved_cart_settings, "user_notify", 0);
			if ($admin_notify || $user_notify) {
				// set variables for email notifications
				$t->set_vars($user_info);
      
				$date_added_formatted = va_date($datetime_show_format, va_time());
				$t->set_var("date_added", $date_added_formatted);
      
				$t->set_var("ip", $ip);
				$t->set_var("remote_address", $ip);
				$t->set_var("cart_id", $saved_cart_id);
				$t->set_var("cart_name", $r->get_value("cart_name"));
				$t->set_var("cart_email", $r->get_value("cart_email"));
				$t->set_var("email", $r->get_value("cart_email"));

			}

			// send email notification to admin
			if ($admin_notify)
			{
				$admin_subject = get_setting_value($saved_cart_settings, "admin_subject", va_message("CART_TITLE"));
				$admin_message = get_setting_value($saved_cart_settings, "admin_message", get_translation("{CART_NO_FIELD}: {cart_id}\n{CART_NAME_FIELD}: {cart_name}"));
				$t->set_block("admin_subject", $admin_subject);
				$t->set_block("admin_message", $admin_message);
      
				$mail_to = get_setting_value($saved_cart_settings, "admin_email", $settings["admin_email"]);
				$mail_to = str_replace(";", ",", $mail_to);
				$mail_from = get_setting_value($saved_cart_settings, "admin_mail_from", $settings["admin_email"]);
				$email_headers = array();
				$email_headers["from"] = parse_value($mail_from);
				$email_headers["cc"] = get_setting_value($saved_cart_settings, "admin_mail_cc");
				$email_headers["bcc"] = get_setting_value($saved_cart_settings, "admin_mail_bcc");
				$email_headers["reply_to"] = get_setting_value($saved_cart_settings, "admin_mail_reply_to");
				$email_headers["return_path"] = get_setting_value($saved_cart_settings, "admin_mail_return_path");
				$email_headers["mail_type"] = get_setting_value($saved_cart_settings, "admin_message_type");
      
				$t->parse("admin_subject", false);
				$t->parse("admin_message", false);
				$admin_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("admin_message"));
				va_mail($mail_to, $t->get_var("admin_subject"), $admin_message, $email_headers);
			}
      
			// send email notification to user
			if ($user_notify && $cart_email)
			{
				$user_subject = get_setting_value($saved_cart_settings, "admin_subject", va_message("CART_TITLE"));
				$user_message = get_setting_value($saved_cart_settings, "admin_message", get_translation("{CART_NO_FIELD}: {cart_id}\n{CART_NAME_FIELD}: {cart_name}"));
				$t->set_block("user_subject", $user_subject);
				$t->set_block("user_message", $user_message);
      
				$mail_from = get_setting_value($saved_cart_settings, "user_mail_from", $settings["admin_email"]); 
				$email_headers = array();
				$email_headers["from"] = parse_value($mail_from);
				$email_headers["cc"] = get_setting_value($saved_cart_settings, "user_mail_cc");
				$email_headers["bcc"] = get_setting_value($saved_cart_settings, "user_mail_bcc");
				$email_headers["reply_to"] = get_setting_value($saved_cart_settings, "user_mail_reply_to");
				$email_headers["return_path"] = get_setting_value($saved_cart_settings, "user_mail_return_path");
				$email_headers["mail_type"] = get_setting_value($saved_cart_settings, "user_message_type");
      
				$t->parse("user_subject", false);
				$t->parse("user_message", false);
      
				$user_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("user_message"));
				va_mail($cart_email, $t->get_var("user_subject"), $user_message, $email_headers);
			}
			// end: sending email notifications
		}
	} else if (strlen($operation) && $session_cart_id) {
		$saved_cart_id = $session_cart_id;
	} else {
		$user_email = get_setting_value($user_info, "email", "");
		$r->set_value("cart_email", $user_email);
		$r->set_value("cart_clear", 1);
	}

	$r->set_parameters();

	if ($saved_cart_id) {
		$success_message = str_replace("{cart_id}", $saved_cart_id, CART_SAVED_MSG);
		$r->set_value("cart_name", "");
		$t->set_var("success_message", $success_message);
		$t->parse("success_block", false);
	}

	$t->set_var("rp", htmlspecialchars($rp));
	$t->set_var("random_value", htmlspecialchars($new_random_value));

	$block_parsed = true;
