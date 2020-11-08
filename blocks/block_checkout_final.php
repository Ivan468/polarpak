<?php

	include_once("./includes/record.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/order_items.php");
	include_once("./includes/order_links.php");
	include_once("./includes/parameters.php");

	$default_title = "{final_title}";

	$html_template = get_setting_value($block, "html_template", "block_checkout_final.html"); 
  $t->set_file("block_body", $html_template);

	$eol = get_eol();
	$referer = get_session("session_referer");
	$user_ip = get_ip();
	$visit_number = get_session("session_visit_number");
	$t->set_var("site_url", $settings["site_url"]);
	$t->set_var("order_final", "order_final.php");
	$t->set_var("referer", $referer);
	$t->set_var("referrer", $referer);
	$t->set_var("HTTP_REFERER", $referer);
	$t->set_var("visit_number", $visit_number);

	$order_id = get_order_id();
	$vc = get_session("session_vc");
	$error_message  = check_order($order_id, "", true);

	$variables = array();
	$variables["charset"] = CHARSET;
	$variables["site_url"] = $settings["site_url"];
	$variables["user_ip"] = $user_ip;

	$is_placed = 0; $payment_id = ""; $order_payment_id = ""; $payment_info = ""; $order_status = 0; $status_type = ""; $order_total = 0; $pending_message = ""; $default_currency_code = "";
	$sql  = " SELECT o.*, os.status_type, ps.payment_info ";
	$sql .= " FROM " . $table_prefix . "orders o ";
	$sql .= " LEFT JOIN " . $table_prefix . "payment_systems ps ON o.payment_id=ps.payment_id ";
	$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id ";
	$sql .= " WHERE o.order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$is_placed = $db->f("is_placed");
		$payment_id = $db->f("payment_id");
		$order_payment_id = $db->f("order_payment_id");
		$status_type = $db->f("status_type");
		$default_currency_code = $db->f("default_currency_code");
		$payment_info = get_translation($db->f("payment_info"));
		$payment_info = get_currency_message($payment_info, $currency);
		$order_status  = $db->f("order_status");
		$order_total = $db->f("order_total");
		$payment_data = $db->f("payment_data");
		if ($payment_data) { $payment_data = json_decode($payment_data, true); }
		if (!is_array($payment_data)) { $payment_data = array(); }
		$va_data["payment_data"] = $payment_data;
		$pending_message = $db->f("pending_message");
		if (!strlen($error_message)) {
			$error_message = $db->f("error_message");
		}
	} else {
		$error_message = va_constant("APPROPRIATE_CODE_ERROR_MSG") . $order_id . ".<br>";
	}

	// get payment data
	$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_params, $pass_data, $variables, "final");
	$payment_params = $payment_parameters;

	$t->set_vars($variables);

	$order_final = array();
	if (strlen($payment_id)) {
		$setting_type = "order_final_" . $payment_id;
		$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
		$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
		if (isset($site_id)) {
			$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
			$sql .= " ORDER BY site_id ASC ";
		} else {
			$sql .= " AND site_id=1 ";
		}
		$db->query($sql);
		while ($db->next_record()) {
			$order_final[$db->f("setting_name")] = $db->f("setting_value");
		}
	}

	// special option if we need to wait a postback response from payment gateway
	$payment_waiting = 0; // time in seconds to wait for next attempt

	$is_validation = get_setting_value($order_final, "is_validation", 0);
	if ($is_validation && $order_payment_id && !$is_placed && !strlen($error_message) && !strlen($pending_message)) {
		$validation_php_lib = get_setting_value($order_final, "validation_php_lib", "");
		if (strlen($validation_php_lib)) {

			// get statuses
			$success_status_id = get_setting_value($order_final, "success_status_id", "");
			$pending_status_id = get_setting_value($order_final, "pending_status_id", "");
			$failure_status_id = get_setting_value($order_final, "failure_status_id", "");

			$transaction_id  = ""; // save transaction number to this variable
			$error_message   = ""; // save validation errors to this variable
			$pending_message = ""; // save pending message to this variable

			// flag to update order status and other data when using foreign library
			$update_order_status = true; $update_order_data = true ;
			// check if payment system support 3D secure
			$secure_3d = false;

			// include payment module only if total order value greater than zero
			if ($order_total > 0) {
				// use php library to validate transaction
				$order_step = "final";
				if (file_exists($validation_php_lib)) {
					include_once ($validation_php_lib);
				} else {
					$error_message = va_constant("APPROPRIATE_LIBRARY_ERROR_MSG") . ": " . $validation_php_lib;
				}
			}

			$payment_waiting = get_setting_value($va_data, "payment_waiting", 0); // time in seconds to wait for next attempt	
			if ($payment_waiting) {
				$waiting_attempt = get_setting_value($va_data["payment_data"], "waiting_attempt", 0); // previous attempt number to wait payment response
				$waiting_attempts = get_setting_value($va_data["payment_data"], "waiting_attempts", 30); // total number of attempts 
				if ($waiting_attempt >= $waiting_attempts) {
					// we reach max number of attempts so we automatically disable waiting process and set order to pending status
					$payment_waiting = 0;
					if (!$pending_message) { $pending_message = va_message("CHECKOUT_PENDING_MSG"); }
				} else {               	
					$pending_message = ""; // ignore pending status at this moment
					$waiting_attempt++; // increase by one for current attempt number and update payment data
					$va_data["payment_data"]["waiting_attempt"] = $waiting_attempt;
					$va_data["payment_data"]["waiting_attempts"] = $waiting_attempts;
				}
			}

			if ($update_order_data) {
				$r = new VA_Record($table_prefix . "orders");
				$r->add_where("order_id", INTEGER);
				$r->set_value("order_id", $order_id);

				$r->add_textbox("error_message", TEXT);
				$r->add_textbox("pending_message", TEXT);
				$r->add_textbox("transaction_id", TEXT);
				$r->change_property("transaction_id", USE_IN_UPDATE, false);
				$r->add_textbox("authorization_code", TEXT);
				$r->add_textbox("payment_data", TEXT);
				// AVS fields
				$r->add_textbox("avs_response_code", TEXT);
				$r->add_textbox("avs_message", TEXT);
				$r->add_textbox("avs_address_match", TEXT);
				$r->add_textbox("avs_zip_match", TEXT);
				$r->add_textbox("cvv2_match", TEXT);
				// 3D fields
				$r->add_textbox("secure_3d_check", TEXT);
				$r->add_textbox("secure_3d_status", TEXT);
				$r->add_textbox("secure_3d_md", TEXT);
				$r->add_textbox("secure_3d_eci", TEXT);
				$r->add_textbox("secure_3d_cavv", TEXT);
				$r->add_textbox("secure_3d_xid", TEXT);

				// update order data
				$r->set_value("error_message", $error_message);
				$r->set_value("pending_message", $pending_message);
				if (strlen($transaction_id)) {
					$r->set_value("transaction_id", $transaction_id);
					$r->change_property("transaction_id", USE_IN_UPDATE, true);
				}
				$r->set_value("authorization_code", $variables["authorization_code"]);
				$r->set_value("payment_data", json_encode($va_data["payment_data"]));

				// set AVS data
				$r->set_value("avs_response_code", $variables["avs_response_code"]);
				$r->set_value("avs_message", $variables["avs_message"]);
				$r->set_value("avs_address_match", $variables["avs_address_match"]);
				$r->set_value("avs_zip_match", $variables["avs_zip_match"]);
				$r->set_value("cvv2_match", $variables["cvv2_match"]);
				// set 3D data
				$r->set_value("secure_3d_check", $variables["secure_3d_check"]);
				$r->set_value("secure_3d_status", $variables["secure_3d_status"]);
				$r->set_value("secure_3d_md", $variables["secure_3d_md"]);
				$r->set_value("secure_3d_eci", $variables["secure_3d_eci"]);
				$r->set_value("secure_3d_cavv", $variables["secure_3d_cavv"]);
				$r->set_value("secure_3d_xid", $variables["secure_3d_xid"]);

				$r->update_record();
			}

			if (!$payment_waiting && $update_order_status) {
				if (strlen($error_message)) {
					$order_status = $failure_status_id;
				} elseif (strlen($pending_message)) {
					$order_status = $pending_status_id;
				} else {
					$order_status = $success_status_id;
				}

				// update order status for payment
				update_order_status($order_id, $order_status, true, "", $status_error);
			}

			$failure_action = get_setting_value($variables, "failure_action", 0);
			if ($secure_3d && strlen($error_message) && $failure_action == 1) {
				// make redirect user make another try
				header("Location: order_info.php?operation=load&active_step=payment");
				exit;
			}
		}
	}

	set_session("session_user_order_id", "");
	// empty cart and new user_id only if order was placed without any errors
	if (!strlen($error_message)) {
		set_session("shopping_cart", "");
		set_session("session_coupons", "");
		set_session("session_new_user", "");
		set_session("session_new_user_id", "");
		set_session("session_new_user_type_id", "");
		// delete cart 
		db_cart_update("clear");
	}

	$final_title = ""; $final_message = ""; $paid_status = 0;
	// get orders data 
	$sql  = " SELECT o.*,os.status_name,os.status_type, os.final_title, os.final_message, os.paid_status,os.user_invoice_activation ";
	$sql .= " FROM (" . $table_prefix . "orders o ";
	$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
	$sql .= " WHERE o.order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		// email variables
		$email = $db->f("email");
		$delivery_email = $db->f("delivery_email");
		$cell_phone = $db->f("cell_phone");

		// shopping variables
		$goods_total = $db->f("goods_total");
		$total_discount = doubleval($db->f("total_discount"));
		$goods_with_discount = $goods_total - $total_discount;
		$shipping_cost = doubleval($db->f("shipping_incl_tax")); // use new value with tax
		$shipping_excl_tax = doubleval($db->f("shipping_excl_tax"));
		$shipping_tax = doubleval($db->f("shipping_tax"));
		$shipping_incl_tax = doubleval($db->f("shipping_incl_tax"));

		$tax_percent = $db->f("tax_percent");
		$tax_total = doubleval($db->f("tax_total"));
		$processing_fee = $db->f("processing_fee");
		$order_total = $db->f("order_total");
		$order_placed_date = $db->f("order_placed_date", DATETIME);
		$cc_start_date = $db->f("cc_start_date", DATETIME);
		$cc_expiry_date = $db->f("cc_expiry_date", DATETIME);
		$cc_type = $db->f("cc_type");

		// info variables
		$company_id = $db->f("company_id");
		$state_id = $db->f("state_id");
		$country_id = $db->f("country_id");
		$delivery_company_id = $db->f("delivery_company_id");
		$delivery_state_id = $db->f("delivery_state_id");
		$delivery_country_id = $db->f("delivery_country_id");
		$t->set_vars($db->Record);

		//google analytics changes
		$affiliate_code = $db->f("affiliate_code");
		$delivery_city = $db->f("delivery_city");

		// status data
		$order_status_name = $db->f("status_name");
		$status_type = $db->f("status_type");
		parse_value($order_status_name);
		$final_title = $db->f("final_title");
		$final_message = $db->f("final_message");
		$paid_status = $db->f("paid_status");
		$user_invoice_activation = $db->f("user_invoice_activation");

		$t->set_var("goods_total", currency_format($goods_total));
		$t->set_var("goods_total_value", number_format($goods_total, 2, ".", ""));
		$t->set_var("total_discount", currency_format($total_discount));
		$t->set_var("goods_with_discount", currency_format($goods_with_discount));
		$t->set_var("shipping_cost", currency_format($shipping_cost));
		$t->set_var("tax_percent", number_format($tax_percent, 2) . "%");
		$t->set_var("tax_total", currency_format($tax_total));
		$t->set_var("tax_cost", currency_format($tax_total));
		$t->set_var("processing_fee", currency_format($processing_fee));
		$t->set_var("order_total", currency_format($order_total));
		$t->set_var("order_total_value", number_format($order_total, 2, ".", ""));
		$t->set_var("order_placed_date", va_date($datetime_show_format, $order_placed_date));
		$t->set_var("cc_start_date", va_date(array("MM"," / ","YYYY"), $cc_start_date));
		$t->set_var("cc_expiry_date", va_date(array("MM"," / ","YYYY"), $cc_expiry_date));

		$t->set_var("cc_type",                $variables["cc_type"]);
		$t->set_var("company_select",         $variables["company_select"]);
		$t->set_var("state",                  $variables["state"]);
		$t->set_var("country",                $variables["country"]);
		$t->set_var("delivery_company_select",$variables["delivery_company_select"]);
		$t->set_var("delivery_state",         $variables["delivery_state"]);
		$t->set_var("delivery_country",       $variables["delivery_country"]);

		$t->set_var("status_name", $order_status_name);
		$t->set_var("order_status_name", $order_status_name);
		$t->set_var("status_final_message", $final_message);
	}

	if (isset($va_cc_tags) && $va_cc_tags) {
		$t->set_var("cc_number", get_session("session_cc_number"));
		$t->set_var("cc_number_first", get_session("session_cc_number_first"));
		$t->set_var("cc_number_last", get_session("session_cc_number_last"));
		$t->set_var("cc_security_code", get_session("session_cc_code"));
	} else {
		$t->set_var("cc_name", "");
		$t->set_var("cc_first_name", "");
		$t->set_var("cc_last_name", "");
		$t->set_var("cc_number", "");
		$t->set_var("cc_start_date", "");
		$t->set_var("cc_expiry_date", "");
		$t->set_var("cc_type", "");
		$t->set_var("cc_issue_number", "");
		$t->set_var("cc_security_code", "");
	}

	$is_failed = false; $is_pending = false; $is_success = false;
	if ($payment_waiting > 0) {
		// set script to reload block
		set_script_tag("js/ajax.js");
		set_script_tag("js/blocks.js");

		$default_title = va_constant("PAYMENT_PROCESSING_MSG");
		$t->set_var("final_title", $final_title);
		$t->set_var("attempt_number", $waiting_attempt);
		$t->set_var("waiting_attempt", $waiting_attempt);
		$t->set_var("waiting_attempts", $waiting_attempts);
		$t->set_var("waiting_time", intval($payment_waiting)*1000);
		$t->sparse("payment_waiting_block", false);
		$block_parsed = true;
		return;
	} else if (strlen($error_message)) {
		$is_failed = true;
		$message_type = "failure";
		if (!$final_title) { $final_title = va_constant("CHECKOUT_ERROR_TITLE"); }
		$final_message .= get_setting_value($order_final, "failure_message", "");
		if (!$final_message) {
			$final_message = $error_message;
		}
		$t->set_var("error_desc", $error_message);
		$t->set_var("error_message", $error_message);
	} elseif (strlen($pending_message)) {
		$is_pending = true;
		$message_type = "pending";
		if (!$final_title) {  $final_title = va_constant("CHECKOUT_PENDING_TITLE"); }
		$final_message .= get_setting_value($order_final, "pending_message", "");
		if (!$final_message) {
			$final_message = va_constant("CHECKOUT_PENDING_MSG");
		}
		$t->set_var("pending_desc", $pending_message);
		$t->set_var("pending_message", $pending_message);
	} else {
		$is_success = true;
		$message_type = "success";
		if (!$final_title) { $final_title = va_constant("CHECKOUT_SUCCESS_TITLE"); }
		$final_message .= get_setting_value($order_final, "success_message", "");
		if (!$final_message) {
			$final_message = va_constant("CHECKOUT_SUCCESS_MSG");
		}
		// payment system success response
		if (isset($success_message)) {
			$t->set_var("success_desc", $success_message);
			$t->set_var("success_message", $success_message);
		}
	}
	$final_message = get_translation($final_message);
	$final_message = get_currency_message($final_message, $currency);
	$t->set_var("final_title", $final_title);
	$t->set_block("final_message", $final_message);

	$order_data = show_order_items($order_id, true, "checkout_final");
	$items_text = $order_data["items_text"];

	$t->set_var("basket",      $items_text);
	$t->set_var("items_text",  $items_text);
	$t->set_var("total_items", $total_items);

	// get download links
	$links = get_order_links($order_id);
	$t->set_var("links",      $links["html"]);
	$t->set_var("links_html", $links["html"]);
	$t->set_var("links_txt",  $links["text"]);

	// get serial numbers
	$order_serials = get_serial_numbers($order_id);
	$t->set_var("serials", $order_serials["html"]);
	$t->set_var("serial_numbers", $order_serials["html"]);

	// get gift vouchers
	$order_vouchers = get_gift_vouchers($order_id);
	$t->set_var("vouchers", $order_vouchers["html"]);
	$t->set_var("gift_vouchers", $order_vouchers["html"]);

	$t->set_block("payment_info", $payment_info);
	$t->parse("payment_info", false);

	// parse final message
	$t->parse("final_message", false);
	$t->sparse("final_message_block", false);

	// send emails
	if (!$payment_waiting && !$is_placed) { // check if order wasn't placed before and it isn't wait for payment data
		set_session("session_order_id", $order_id);

		// get admin notify
		$admin_notification   = get_setting_value($order_final, "admin_notification",   0);
		$admin_pending_notify = get_setting_value($order_final, "admin_pending_notify", 0);
		$admin_failure_notify = get_setting_value($order_final, "admin_failure_notify", 0);

		// get user notify
		$user_notification   = get_setting_value($order_final, "user_notification",   0);
		$user_pending_notify = get_setting_value($order_final, "user_pending_notify", 0);
		$user_failure_notify = get_setting_value($order_final, "user_failure_notify", 0);

		// get admin sms notify
		$admin_sms_success = get_setting_value($order_final, "admin_sms_success", 0);
		$admin_sms_pending = get_setting_value($order_final, "admin_sms_pending", 0);
		$admin_sms_failure = get_setting_value($order_final, "admin_sms_failure", 0);

		// get user sms notify
		$user_sms_success = get_setting_value($order_final, "user_sms_success", 0);
		$user_sms_pending = get_setting_value($order_final, "user_sms_pending", 0);
		$user_sms_failure = get_setting_value($order_final, "user_sms_failure", 0);

		$admin_notify = (($is_success && $admin_notification) || ($is_pending && $admin_pending_notify) || ($is_failed && $admin_failure_notify));
		if (isset($order_final["admin_message"])){
			$admin_message = get_final_message($order_final["admin_message"], $message_type);
		} else {
			$admin_message = "";
		}
		$admin_mail_type = get_setting_value($order_final, "admin_message_type");
		$user_notify = (($is_success && $user_notification) || ($is_pending && $user_pending_notify) || ($is_failed && $user_failure_notify));
		if (isset($order_final["user_message"])){
			$user_message = get_final_message($order_final["user_message"], $message_type);
		} else {
			$user_message = "";
		}
		$user_mail_type = get_setting_value($order_final, "user_message_type");
		// pdf invoice notification
		$admin_mail_pdf_invoice = get_setting_value($order_final, "admin_mail_pdf_invoice", 0);
		$user_mail_pdf_invoice = (get_setting_value($order_final, "user_mail_pdf_invoice", 0) && $user_invoice_activation);
		$pdf_invoice = "";
		if (($admin_notify && $admin_mail_pdf_invoice) || ($user_notify && $user_mail_pdf_invoice)) {
			include_once("./includes/invoice_functions.php");
			$pdf_invoice = pdf_invoice($order_id);
		}
		// pdf packing slip notification
		$admin_mail_pdf_packing_slip = get_setting_value($order_final, "admin_mail_pdf_packing_slip", 0);
		$user_mail_pdf_packing_slip = get_setting_value($order_final, "user_mail_pdf_packing_slip", 0);
		$pdf_packing_slip = "";
		if (($admin_notify && $admin_mail_pdf_packing_slip) || ($user_notify && $user_mail_pdf_packing_slip)) {
			include_once("./includes/invoice_functions.php");
			$pdf_packing_slip = pdf_packing_slip($order_id);
		}

		// parse basket template if tag used in notification
		if (($admin_notify && $admin_mail_type && strpos($admin_message, "{basket}") !== false)
			|| ($user_notify && $user_mail_type && strpos($user_message, "{basket}") !== false))
		{
			$t->set_file("basket_html", "email_basket.html");
			show_order_items($order_id, true, "email");
			$t->parse("basket_html", false);
		}
		if (($admin_notify && !$admin_mail_type && strpos($admin_message, "{basket}") !== false) 
			|| ($user_notify && !$user_mail_type && strpos($user_message, "{basket}") !== false) )
		{
			$t->set_file("basket_text", "email_basket.txt");
			show_order_items($order_id, true, "email");
			$t->parse("basket_text", false);
		}

		if ($admin_notify)
		{
			$admin_subject = get_final_message($order_final["admin_subject"], $message_type);
			$admin_subject = get_translation($admin_subject);
			$admin_message = get_currency_message(get_translation($admin_message), $currency);
			// PGP enable
			$admin_notification_pgp = get_setting_value($order_final, "admin_notification_pgp",   0);

			$t->set_block("admin_subject", $admin_subject);
			$t->set_block("admin_message", $admin_message);

			$attachments = array();
			if ($admin_mail_pdf_invoice) {
				$attachments[] = array("Invoice_".$order_id.".pdf", $pdf_invoice, "buffer");
			}
			if ($admin_mail_pdf_packing_slip) {
				$attachments[] = array("Packing_Slip_".$order_id.".pdf", $pdf_packing_slip, "buffer");
			}

			$mail_to = get_setting_value($order_final, "admin_email", $settings["admin_email"]);
			$mail_to = str_replace(";", ",", $mail_to);
			$email_headers = array();
			$email_headers["from"] = get_setting_value($order_final, "admin_mail_from", $settings["admin_email"]);
			$email_headers["cc"] = get_setting_value($order_final, "cc_emails");
			$email_headers["bcc"] = get_setting_value($order_final, "admin_mail_bcc");
			$email_headers["reply_to"] = get_setting_value($order_final, "admin_mail_reply_to");
			$email_headers["return_path"] = get_setting_value($order_final, "admin_mail_return_path");
			$email_headers["mail_type"] = get_setting_value($order_final, "admin_message_type");

			if (!$email_headers["mail_type"]) {
				$t->set_var("basket", $t->get_var("basket_text"));
				$t->set_var("links",  $links["text"]);
				$t->set_var("serials", $order_serials["text"]);
				$t->set_var("serial_numbers", $order_serials["text"]);
				$t->set_var("vouchers", $order_vouchers["text"]);
				$t->set_var("gift_vouchers", $order_vouchers["text"]);
			} else {
				$t->set_var("basket", $t->get_var("basket_html"));
			}

			$t->parse("admin_subject", false);
			$t->parse("admin_message", false);
			$admin_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("admin_message"));
			// PGP encryption			
			if ( $admin_notification_pgp && $admin_message) {	
				include_once ("./includes/pgp_functions.php");
				if (pgp_test()) {
					$tmp_admin_emails = explode(',',$mail_to);
					foreach ($tmp_admin_emails AS $tmp_admin_email) {
						$admin_message = pgp_encrypt($admin_message, $tmp_admin_email);
						if ($admin_message){
							va_mail($tmp_admin_email, $t->get_var("admin_subject"), $admin_message, $email_headers, $attachments);
						}
					}
				}
			} else {
				va_mail($mail_to, $t->get_var("admin_subject"), $admin_message, $email_headers, $attachments);		
			}
		}

		if ($user_notify)
		{
			// Product individual notidication mode
			$product_individual_notification = get_setting_value($order_final, "product_individual_notification", 0);
			$all_products_have_notifications = false;
			
			$attachments = array();
			if ($user_mail_pdf_invoice) {
				$attachments[] = array("Invoice_".$order_id.".pdf", $pdf_invoice, "buffer");
			}
			if ($user_mail_pdf_packing_slip) {
				$attachments[] = array("Packing_Slip_".$order_id.".pdf", $pdf_packing_slip, "buffer");
			}


			if ($product_individual_notification) {
				$all_products_have_notifications = true;
				$sql  = " SELECT item.item_id ";
				$sql .= " FROM " . $table_prefix . "orders_items AS item";
				$sql .= " LEFT JOIN " . $table_prefix . "items AS fullitem ON fullitem.item_id=item.item_id";
				$sql .= " WHERE item.order_id=" . $db->tosql($order_id, INTEGER);
				$sql .= " AND ( fullitem.mail_notify IS NULL OR fullitem.mail_notify=0 )";
				$db->query($sql);				
				if($db->next_record()){
					$all_products_have_notifications = false;
				}
			}	
			if( !$all_products_have_notifications ){		
				$user_subject = get_final_message($order_final["user_subject"], $message_type);
				$user_subject = get_translation($user_subject);
				$user_message = get_currency_message(get_translation($user_message), $currency);
	
				$t->set_block("user_subject", $user_subject);
				$t->set_block("user_message", $user_message);
	
				$email_headers = array();
				$email_headers["from"] = get_setting_value($order_final, "user_mail_from", $settings["admin_email"]);
				$email_headers["cc"] = get_setting_value($order_final, "user_mail_cc");
				$email_headers["bcc"] = get_setting_value($order_final, "user_mail_bcc");
				$email_headers["reply_to"] = get_setting_value($order_final, "user_mail_reply_to");
				$email_headers["return_path"] = get_setting_value($order_final, "user_mail_return_path");
				$email_headers["mail_type"] = get_setting_value($order_final, "user_message_type");
	
				if (!$email_headers["mail_type"]) {
					$t->set_var("basket", $t->get_var("basket_text"));
					$t->set_var("links",  $links["text"]);
					$t->set_var("serials", $order_serials["text"]);
					$t->set_var("serial_numbers", $order_serials["text"]);
					$t->set_var("vouchers", $order_vouchers["text"]);
					$t->set_var("gift_vouchers", $order_vouchers["text"]);
				} else {
					$t->set_var("basket", $t->get_var("basket_html"));
				}
	
				$t->parse("user_subject", false);
				$t->parse("user_message", false);
				$user_email = strlen($email) ? $email : $delivery_email;
				$user_message = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("user_message"));
				va_mail($user_email, $t->get_var("user_subject"), $user_message, $email_headers, $attachments);
			}
		}

		if (($is_success && $admin_sms_success) || ($is_pending && $admin_sms_pending) || ($is_failed && $admin_sms_failure))
		{
			$admin_sms_recipient  = get_setting_value($order_final, "admin_sms_recipient", "");
			$admin_sms_originator = get_setting_value($order_final, "admin_sms_originator", "");
			$admin_sms_message    = get_setting_value($order_final, "admin_sms_message", "");

			$t->set_block("admin_sms_recipient",  $admin_sms_recipient);
			$t->set_block("admin_sms_originator", $admin_sms_originator);
			$t->set_block("admin_sms_message",    $admin_sms_message);

			$t->set_var("basket", $items_text);
			$t->set_var("links",  $links["text"]);
			$t->set_var("serials", $order_serials["text"]);
			$t->set_var("vouchers", $order_vouchers["text"]);

			$t->parse("admin_sms_recipient", false);
			$t->parse("admin_sms_originator", false);
			$t->parse("admin_sms_message", false);

			sms_send($t->get_var("admin_sms_recipient"), $t->get_var("admin_sms_message"), $t->get_var("admin_sms_originator"), $sms_errors);
		}

		if (($is_success && $user_sms_success) || ($is_pending && $user_sms_pending) || ($is_failed && $user_sms_failure))
		{
			$user_sms_recipient  = get_setting_value($order_final, "user_sms_recipient", $cell_phone);
			$user_sms_originator = get_setting_value($order_final, "user_sms_originator", "");
			$user_sms_message    = get_setting_value($order_final, "user_sms_message", "");

			$t->set_block("user_sms_recipient",  $user_sms_recipient);
			$t->set_block("user_sms_originator", $user_sms_originator);
			$t->set_block("user_sms_message",    $user_sms_message);

			$t->set_var("basket", $items_text);
			$t->set_var("links",  $links["text"]);
			$t->set_var("serials", $order_serials["text"]);
			$t->set_var("vouchers", $order_vouchers["text"]);

			$t->parse("user_sms_recipient", false);
			$t->parse("user_sms_originator", false);
			$t->parse("user_sms_message", false);

			if (sms_send_allowed($t->get_var("user_sms_recipient"))) {
				sms_send($t->get_var("user_sms_recipient"), $t->get_var("user_sms_message"), $t->get_var("user_sms_originator"), $sms_errors);
			}
		}


		if ($status_type != "QUOTE_REQUEST" && $status_type != "QUOTE" && $status_type != "PARTIALLY_PAID") {
			// set for all statuses except QUOTE that order was placed and couldn't be changed
			$sql  = " UPDATE " . $table_prefix . "orders SET is_placed=1 ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
			$db->query($sql);
		}
	}
	set_session("session_order_sent", $order_id);
	set_session("session_cc_number", "");
	set_session("session_cc_number_first", "");
	set_session("session_cc_number_last", "");
	set_session("session_cc_code", "");

	// BEGIN google analytics and google trusted stores
	$google_analytics = get_setting_value($settings, "google_analytics", 0);
	$google_tracking_code = get_setting_value($settings, "google_tracking_code", "");
	if (!$google_tracking_code) { $google_analytics = 0; }
	if (!$payment_waiting && $paid_status && $google_analytics) {
		$t->set_var("google_order_id", $order_id);
		$t->set_var("google_affiliation", str_replace("\"", "\\\"", htmlspecialchars($affiliate_code)));
		$t->set_var("google_currency", $default_currency_code);
		$t->set_var("google_currency_code", $default_currency_code);
		$t->set_var("default_currency_code", $default_currency_code);
		$t->set_var("google_total", doubleval($order_total));
		$t->set_var("google_tax", doubleval($tax_total));
		$t->set_var("google_shipping", doubleval($shipping_cost));
		$t->set_var("google_city", str_replace("\"", "\\\"", htmlspecialchars($delivery_city)));
		$t->set_var("google_state", str_replace("\"", "\\\"", htmlspecialchars($variables["delivery_state"])));
		$t->set_var("google_country", str_replace("\"", "\\\"", htmlspecialchars($variables["delivery_country"])));

		$dbh = new VA_SQL();
		$dbh->DBType      = $db_type;
		$dbh->DBDatabase  = $db_name;
		$dbh->DBHost      = $db_host;
		$dbh->DBPort      = $db_port;
		$dbh->DBUser      = $db_user;
		$dbh->DBPassword  = $db_password;
		$dbh->DBPersistent= $db_persistent;

		$google_items = array();
		$sql  = " SELECT oi.item_id, oi.item_code, oi.manufacturer_code, m.manufacturer_name, ";
		$sql .= " oi.item_name, oi.downloadable, oi.price, oi.quantity ";
		$sql .= " FROM " . $table_prefix . "orders_items oi ";
		$sql .= " LEFT JOIN " . $table_prefix . "items i ON oi.item_id=i.item_id ";
		$sql .= " LEFT JOIN " . $table_prefix . "manufacturers m ON i.manufacturer_id=m.manufacturer_id ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		while($db->next_record())
		{
			$google_item_id = $db->f("item_id");
			$google_item_name = $db->f("item_name");
			$google_manufacturer = $db->f("manufacturer_name");
			$google_sku_code = $db->f("manufacturer_code");
			if (!$google_sku_code) {
				$google_sku_code = $db->f("item_code");
			}
			$google_price = $db->f("price");
			$google_quantity = $db->f("quantity");
			$item_downloadable = $db->f("downloadable");

			$google_category = "";
			$count = 0;
			$sql = "SELECT c.category_name FROM " . $table_prefix . "items_categories ic, " . $table_prefix . "categories c WHERE ic.category_id=c.category_id AND ic.item_id=" . $dbh->tosql($google_item_id, INTEGER);
			$dbh->query($sql);
			while ($dbh->next_record()) {
				if ($count > 0) {
					$google_category .= " / ";
				}
				$count++;
				$google_category .= $dbh->f("category_name");
			}

			if ($google_analytics) {
				$google_items[] = array(
					"id" => $google_sku_code,
					"name" => $google_item_name,
    		  "brand" => $google_manufacturer,
		      "category" => $google_category,
		      "quantity" => $google_quantity,
    		  "price" => $google_price,
				);
			}
		}
		if ($google_analytics) {
			$t->set_var("google_items", json_encode($google_items));
			$t->sparse("google_trans", true);
		}
	}
	//End google analytics

	$block_parsed = true;
