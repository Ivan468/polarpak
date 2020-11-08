<?php

	$default_title = "";

	check_user_security("my_orders");
	$show_restore = get_setting_value($vars, "show_restore", 0);
	
	$operation = get_param("operation");
	if ($operation == "restore") {
		include_once("./includes/order_restore.php");
	}


	set_script_tag("js/blocks.js");

	// connection to delete items or event attachments
	$dbi = new VA_SQL();
	$dbi->DBType      = $db_type;
	$dbi->DBDatabase  = $db_name;
	$dbi->DBUser      = $db_user;
	$dbi->DBPassword  = $db_password;
	$dbi->DBHost      = $db_host;
	$dbi->DBPort      = $db_port;
	$dbi->DBPersistent= $db_persistent;

	// parse popup fram layout
  $t->set_file("block_body", "block_popup.html");
	$t->parse_to("block_body", "hidden_blocks");

	$html_template = get_setting_value($block, "html_template", "block_user_order.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("user_home_href",   get_custom_friendly_url("user_home.php"));
	$t->set_var("user_orders_href", get_custom_friendly_url("user_orders.php"));
	$t->set_var("user_order_href",  get_custom_friendly_url("user_order.php"));
	$t->set_var("user_order_links_href", get_custom_friendly_url("user_order_links.php"));
	$t->set_var("user_order_note_href",  get_custom_friendly_url("user_order_note.php"));
	$t->set_var("releases_href", get_custom_friendly_url("releases.php"));

	$errors = "";
	$payment_id = "";
	$order_id = get_param("order_id");
	$user_id = get_session("session_user_id");
	$operation = get_param("operation");
	$order_status = "";
	$allow_user_cancel = 0;
	$cancel_status_id = "";
	$is_recurring = 0;

	$sql  = " SELECT o.order_id, o.order_status, os.status_name, o.payment_id, o.site_id, os.allow_user_cancel ";
	$sql .= " FROM (" . $table_prefix . "orders o ";
	$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
	$sql .= " WHERE o.order_id=" . $db->tosql($order_id, INTEGER);
	$sql .= " AND os.show_for_user=1 ";
	$sql .= " AND o.user_id=" . $db->tosql($user_id, INTEGER);
	$db->query($sql);
	if ($db->next_record())  {
		$order_site_id = $db->f("site_id");
		$order_status = $db->f("order_status");
		$status_name = get_translation($db->f("status_name"));
		$allow_user_cancel = $db->f("allow_user_cancel");
		$payment_id = $db->f("payment_id");
		$is_recurring = $db->f("is_recurring");
	} else {
		$errors = "Such <b>Order</b> doesn't exists.<br>";
		header("Location: " . get_custom_friendly_url("user_orders.php"));
		exit;
	}

	$t->set_var("order_status_name", $status_name);

	if ($allow_user_cancel) {
		$sql = " SELECT status_id FROM " . $table_prefix . "order_statuses WHERE is_user_cancel=1 ";
		$cancel_status_id = get_db_value($sql);
	}

	if ($operation == "cancel") {
		if ($allow_user_cancel == 1 && strlen($cancel_status_id)) {
			update_order_status($order_id, $cancel_status_id, true, "", $status_error);

			header("Location: " . get_custom_friendly_url("user_orders.php"));
			exit;
		}
	} else if ($operation == "cancel_susbcription") {
		$order_item_id = get_param("order_item_id");
		$sql  = " SELECT oi.order_id, oi.order_item_id, oi.item_name ";
		$sql .= " FROM (" . $table_prefix . "orders_items oi ";
		$sql .= " INNER JOIN " . $table_prefix . "order_statuses os ON oi.item_status=os.status_id) ";
		$sql .= " WHERE order_item_id=" . $db->tosql($order_item_id, INTEGER);
		$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			cancel_subscription($order_item_id);
		}
	}

	// generate link to update payment details for order
	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", "");
	$secure_payments = get_setting_value($settings, "secure_payments", 0);
	if ($secure_payments) {
		$order_update_link = $secure_url . get_custom_friendly_url("user_order_update.php");
	} else {
		$order_update_link = $site_url . get_custom_friendly_url("user_order_update.php");
	}
	$order_update_pd_url = new VA_URL($order_update_link);
	$order_update_pd_url->add_parameter("order_id", CONSTANT, $order_id);
	$order_update_pd_url->add_parameter("od", CONSTANT, 4);

	$t->set_var("user_order_update_pd_url",  $order_update_pd_url->get_url());

	$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type='order_info'";
	$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($order_site_id, INTEGER, true, false) . ")";
	$sql .= " ORDER BY site_id ASC ";
	$db->query($sql);
	while($db->next_record()) {
		$order_info[$db->f("setting_name")] = $db->f("setting_value");
	}

	$user_settings = array();
	$sql = "SELECT setting_name,setting_value FROM " . $table_prefix . "user_types_settings WHERE type_id=" . $db->tosql(get_session("session_user_type_id"), INTEGER);
	$db->query($sql);
	while ($db->next_record()) {
		$user_settings[$db->f("setting_name")] = $db->f("setting_value");
	}
	$user_order_update_pd = get_setting_value($user_settings, "user_order_update_pd", 0);

	if ($user_order_update_pd == 2) {
		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "orders_items ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$sql .= " AND is_recurring=1 ";
		$is_recurring = get_db_value($sql);
		if (!$is_recurring) {
			$user_order_update_pd = 0;
		}
	}
       
	$r = new VA_Record($table_prefix . "orders");
	$r->add_where("order_id", INTEGER);
	$r->set_value("order_id", $order_id);
	$r->add_textbox("order_placed_date", DATETIME);
	$r->change_property("order_placed_date", VALUE_MASK, $datetime_show_format);
	$r->add_textbox("shipping_tracking_id", TEXT);
	$r->add_textbox("shipping_type_id", TEXT);
	$r->add_textbox("shipping_expecting_date", DATETIME);
	$r->change_property("shipping_expecting_date", VALUE_MASK, $datetime_show_format);
	$r->change_property("shipping_expecting_date", SHOW, false); //todo
	$r->add_textbox("currency_code", TEXT);

	$personal_number = 0;
	$delivery_number = 0;
	for ($i = 0; $i < sizeof($parameters); $i++)
	{
		$personal_param = "show_" . $parameters[$i];
		$delivery_param = "show_delivery_" . $parameters[$i];
		$r->add_textbox($parameters[$i], TEXT);
		$r->add_textbox("delivery_" . $parameters[$i], TEXT);
		if (isset($order_info[$personal_param]) && $order_info[$personal_param] == 1) {
			$personal_number++;
		} else {
			$r->parameters[$parameters[$i]][SHOW] = false;
		}
		if (isset($order_info[$delivery_param]) && $order_info[$delivery_param] == 1) {
			$delivery_number++;
		} else {
			$r->parameters["delivery_" . $parameters[$i]][SHOW] = false;
		}
	}
	if($show_restore == 1) {			$t->set_var("order_id", $order_id);
		$t->parse("reorder_body", false);
	}
	else {
		$t->set_var("reorder_body", "");
	}
	// show name and CC four last digits only if customer can update his payment details
	$r->add_textbox("cc_name", TEXT);
	$r->change_property("cc_name", SHOW, $user_order_update_pd);
	$r->add_textbox("cc_first_name", TEXT);
	$r->change_property("cc_first_name", SHOW, $user_order_update_pd);
	$r->add_textbox("cc_last_name", TEXT);
	$r->change_property("cc_last_name", SHOW, $user_order_update_pd);
	$r->add_textbox("cc_number", TEXT);
	$r->change_property("cc_number", SHOW, $user_order_update_pd);
	$r->add_textbox("cc_start_date", DATETIME);
	$r->change_property("cc_start_date", VALUE_MASK, array("MM", " / ", "YYYY"));
	$r->change_property("cc_start_date", SHOW, false);
	$r->add_textbox("cc_expiry_date", DATETIME);
	$r->change_property("cc_expiry_date", VALUE_MASK, array("MM", " / ", "YYYY"));
	$r->change_property("cc_expiry_date", SHOW, false);
	$r->add_textbox("cc_type", INTEGER);
	$r->change_property("cc_type", SHOW, false);
	$r->add_textbox("cc_issue_number", INTEGER);
	$r->change_property("cc_issue_number", SHOW, false);
	$r->add_textbox("cc_security_code", TEXT);
	$r->change_property("cc_security_code", SHOW, false);
	$r->add_textbox("pay_without_cc", TEXT);
	$r->add_textbox("state_id", INTEGER);
	$r->add_textbox("country_id", INTEGER);
	$r->add_textbox("delivery_state_id", INTEGER);
	$r->add_textbox("delivery_country_id", INTEGER);

	$payment_params = 0;
	if (!$errors) {
		$r->get_db_values();
		$user_currency_code = $r->get_value("currency_code");
		// get payment info if available
		$sql = "SELECT payment_info FROM " . $table_prefix . "payment_systems WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
		$payment_info = get_db_value($sql);
		$payment_info = get_translation($payment_info);
		$user_currency = get_currency($user_currency_code);
		$payment_info = get_currency_message($payment_info, $user_currency);
		if (trim($payment_info)) {
			$payment_params++;
			$t->set_block("payment_info", $payment_info);
			$t->parse("payment_info", false);
			$t->global_parse("payment_info_block", false, false, true);
		} else {
			$t->set_var("payment_info_block", "");
		}

		$cc_info = array();
		$setting_type = "credit_card_info_" . $payment_id;
		$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
		$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
		$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($order_site_id, INTEGER, true, false) . ")";
		$sql .= " ORDER BY site_id ASC ";
		$db->query($sql);
		while($db->next_record()) {
			$cc_info[$db->f("setting_name")] = $db->f("setting_value");
		}
		$cc_number_security = get_setting_value($cc_info, "cc_number_security", 0);
		$cc_code_security = get_setting_value($cc_info, "cc_code_security", 0);
		if ($cc_number_security > 0) {
			$r->set_value("cc_number", format_cc_number(va_decrypt($r->get_value("cc_number")), "-", true));
		}
		if ($cc_code_security > 0) {
			$r->set_value("cc_security_code", va_decrypt($r->get_value("cc_security_code")));
		}
	}

	if ($allow_user_cancel == 1 && strlen($cancel_status_id)) {
		$t->set_var("order_id", $order_id);
		$t->set_var("CANCEL_ORDER_CONFIRM_MSG", CANCEL_ORDER_CONFIRM_MSG);
		$t->set_var("CANCEL_ORDER_MSG", CANCEL_ORDER_MSG);
		$t->sparse("cancel_order_link", false);
	}


	if ($r->is_empty("shipping_tracking_id")) {
		$r->parameters["shipping_tracking_id"][SHOW] = false;
	} else {
		$shipping_type_id = $r->get_value("shipping_type_id");
		$sql  = " SELECT sm.tracking_url ";
		$sql .= " FROM " . $table_prefix . "shipping_modules sm, " . $table_prefix . "shipping_types st ";
		$sql .= " WHERE sm.shipping_module_id=st.shipping_module_id ";
		$sql .= " AND st.shipping_type_id=" . $db->tosql($shipping_type_id, INTEGER);
		$shipping_tracking_url = get_db_value($sql);

		$t->set_var("shipping_tracking_id", $r->get_value("shipping_tracking_id"));
		$t->set_var("shipping_tracking_id_url", urlencode($r->get_value("shipping_tracking_id")));
		if ($shipping_tracking_url) {
			$t->set_var("shipping_tracking_url", $shipping_tracking_url);
			$t->sparse("shipping_tracking_url", false);
		} else {
			$t->sparse("shipping_tracking_text", false);
		}
	}
	if ($r->is_empty("shipping_expecting_date")) {
		$r->parameters["shipping_expecting_date"][SHOW] = false;
	}
	if ($r->is_empty("pay_without_cc")) {
		$r->parameters["pay_without_cc"][SHOW] = false;
	}
	for ($i = 0; $i < sizeof($cc_parameters); $i++) {
		if ($r->is_empty($cc_parameters[$i])) {
			$r->parameters[$cc_parameters[$i]][SHOW] = false;
		} else if ($r->parameters[$cc_parameters[$i]][SHOW]) {
			$payment_params++;
		}
	}
	
	if ($r->parameter_exists("state_id") && $r->get_value("state_id")) {
		$state_name = get_db_value("SELECT state_name FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($r->get_value("state_id"), INTEGER, true, false));
	} else {
		$state_name = "";
	}
	
	if ($r->parameter_exists("delivery_state_id") && $r->get_value("delivery_state_id")) {
		$delivery_state_name = get_db_value("SELECT state_name FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($r->get_value("delivery_state_id"), INTEGER, true, false));
	} else {
		$delivery_state_name = "";
	}
		
	if ($r->parameter_exists("country_id") && $r->get_value("country_id")) {
		$country_name = get_db_value("SELECT country_name FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($r->get_value("country_id"), INTEGER, true, false));
	} else {
		$country_name = "";
	}
	
	if ($r->parameter_exists("delivery_country_id") && $r->get_value("delivery_country_id")) {
		$delivery_country_name = get_db_value("SELECT country_name FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($r->get_value("delivery_country_id"), INTEGER, true, false));
	} else {
		$delivery_country_name = "";
	}

	$r->set_value("state_id", get_translation($state_name));	
	$r->set_value("country_id", get_translation($country_name));
	$r->set_value("delivery_state_id", get_translation($delivery_state_name));
	$r->set_value("delivery_country_id", get_translation($delivery_country_name));
	
	
	$r->set_value("company_id", get_translation(get_db_value("SELECT company_name FROM " . $table_prefix . "companies WHERE company_id=" . $db->tosql($r->get_value("company_id"), INTEGER, true, false))));
	$r->set_value("delivery_company_id", get_translation(get_db_value("SELECT company_name FROM " . $table_prefix . "companies WHERE company_id=" . $db->tosql($r->get_value("delivery_company_id"), INTEGER, true, false))));
	$r->set_value("cc_type", get_translation(get_db_value("SELECT credit_card_name FROM " . $table_prefix . "credit_cards WHERE credit_card_id=" . $db->tosql($r->get_value("cc_type"), INTEGER))));

	$r->set_parameters();

	if (!$errors) {
		show_order_items($order_id, true, "user_order");
	}

	if ($personal_number > 0) {
		$t->parse("personal", false);
	}
	if ($delivery_number > 0) {
		$t->parse("delivery", false);
	}

	if ($payment_id && ($payment_params > 0 || $user_order_update_pd)) {
		// show payment information block so customer can view or update his details
		if ($user_order_update_pd) {
			$t->parse("payment_update_link", false);
		}
		$t->parse("payment", false);
	}

	if (!$errors) {
		$sql  = " SELECT * FROM " . $table_prefix . "orders_notes ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$sql .= " AND show_for_user=1 ";
		$sql .= " ORDER BY date_added DESC ";
		$db->query($sql);
		if ($db->next_record()) {
			do {
				$note_id = $db->f("note_id");
				$note_title = $db->f("note_title");
				$note_details = trim($db->f("note_details"));
				$note_date = $db->f("date_added", DATETIME);
				$user_order_note_href  = get_custom_friendly_url("user_order_note.php") . "?order_id=" . urlencode($order_id);
				$user_order_note_href .= "&note_id=" . urlencode($note_id);

				$t->set_var("note_title", $note_title);
				$t->set_var("note_date", va_date($datetime_show_format, $note_date));
				$t->set_var("note_description", nl2br($note_details));
				$t->set_var("user_order_note_href", $user_order_note_href);

				if ($note_details) {
					$t->parse("note_more", false);
				} else {
					$t->set_var("note_more", "");
				}

				$t->parse("notes", true);
			} while ($db->next_record());

			$t->parse("order_notes", false);
		}
	}



	// possible user events:
	// "activation_added", "activation_updated", "activation_removed", "links_sent", "serials_sent",  "vouchers_sent", "email_sent",
	// "update_product", "cancel_subscription", "update_order", "update_order_status", "update_items_status", "update_order_shipping",		
	// "delete_shipping", "remove_shipping", "update_shipping_tracking", "remove_shipping_tracking", "status_notification_sent", "product_notification_sent",
	$sql  = " SELECT oe.event_id, oe.event_date, oe.event_type, oe.event_name, oe.event_description ";
	$sql .= " FROM " . $table_prefix . "orders_events oe ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
//	$sql .= " AND event_type IN ('update_order_status', 'status_notification_sent') ";
	$sql .= " ORDER BY oe.event_date ASC ";
	$db->query($sql);
	if ($db->next_record()) {
		do {
			$event_id = $db->f("event_id");
			$event_date = $db->f("event_date", DATETIME);
			$t->set_var("event_date", va_date($datetime_show_format, $event_date));

			$event_type = $db->f("event_type");
			$event_name = get_translation($db->f("event_name"));
			$event_description = get_translation($db->f("event_description"));
			$event_type_desc = isset($events_types[$event_type]) ? $events_types[$event_type] : OTHER_MSG;
			$t->set_var("event_id", $event_id);
			$t->set_var("event_type", $event_type_desc);
			$t->set_var("event_name", $event_name);
			$t->set_var("event_description", nl2br($event_description));

			if ($event_description) {
				$t->parse("event_more", false);
			} else {
				$t->set_var("event_more", "");
			}

			$t->set_var("event_attachments", "");
			$t->set_var("event_attachments_block", "");
			$sql  = " SELECT * FROM " . $table_prefix . "orders_attachments ";
			$sql .= " WHERE event_id=" . $db->tosql($event_id, INTEGER);
			$dbi->query($sql);
			if ($dbi->next_record()) {
				do {
					$attachment_id = $dbi->f("attachment_id");
					$file_name = $dbi->f("file_name");
					$file_path = $dbi->f("file_path");
					$date_added = $dbi->f("date_added", DATETIME);
					$vc = md5($attachment_id. $date_added[3].$date_added[4].$date_added[5]);

					$admin_order_attachment_url = "order_attachment.php?atid=".urlencode($attachment_id)."&vc=".urlencode($vc);
					$file_size = filesize($file_path);
					$t->set_var("file_name", htmlspecialchars($file_name));
					$t->set_var("file_size", get_nice_bytes($file_size));
					$t->set_var("admin_order_attachment_url", htmlspecialchars($admin_order_attachment_url));
					$t->sparse("event_attachments", true);
				} while ($dbi->next_record());
				$t->sparse("event_attachments_block", false);
			}

			$t->sparse("events", true);
		} while ($db->next_record());
		$t->sparse("order_log", false);
	} else {
		$t->set_var("order_log", "");
	}

	$block_parsed = true;

?>