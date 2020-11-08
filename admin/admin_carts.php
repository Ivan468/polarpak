<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_carts.php                                          ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/
                                   	
		
	@set_time_limit (900);
	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/shopping_cart.php");
	include_once($root_folder_path . "includes/parameters.php");
	include_once($root_folder_path . "includes/profile_functions.php");
	include_once($root_folder_path . "includes/order_items.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("sales_orders");
	//check_admin_security("orders_recover");

	// additional connection 
	$dbs = new VA_SQL();
	$dbs->DBType      = $db_type;
	$dbs->DBDatabase  = $db_name;
	$dbs->DBUser      = $db_user;
	$dbs->DBPassword  = $db_password;
	$dbs->DBHost      = $db_host;
	$dbs->DBPort      = $db_port;
	$dbs->DBPersistent= $db_persistent;

	$operation = get_param("operation");	
	$ajax = get_param("ajax");	
	$cart_ids = get_param("cart_ids");
	$setting_type = "carts";

	$s_rs = get_param("s_rs");	

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_carts.html");
	$t->set_var("date_edit_format", join("", $date_edit_format));
	$t->set_var("admin_orders_reminder_href", "admin_orders_recover.php?operation=send");
	$t->set_var("admin_orders_reminder_filtered_href", "admin_orders_recover.php?operation=send_filtered");
	$t->set_var("admin_orders_recover_href", "admin_orders_recover.php");
	$t->set_var("admin_orders_recover_settings_href", "admin_orders_recover_settings.php");
	$t->set_var("admin_carts_href", "admin_carts.php");
	$t->set_var("admin_carts_settings_href", "admin_carts_settings.php");
	$t->set_var("admin_saved_cart_notify_href", "admin_saved_cart_notify.php");

	if ($s_rs == 1) {
		$t->set_var("date_sent_class", "usual");
	} else {
		$t->set_var("date_sent_class", "hidden");
	}

	// search form parameters
	$cart_types =
		array(
			array("0", ACTIVE_MSG), array("1", SAVED_MSG), array("", ALL_MSG)
		);

	$periods = 
		array(
			array("", ""), array("today", TODAY_MSG), array("yesterday", YESTERDAY_MSG), array("last7days", LAST_7DAYS_MSG),
			array("thismonth", THIS_MONTH_MSG),	array("lastmonth", LAST_MONTH_MSG),	
			//array("thisquarter", THIS_QUARTER_MSG), array("thisyear", THIS_YEAR_MSG)
		);

	$yes_no_all = 
		array( 
			array(0, NO_MSG), array(1, YES_MSG), array("", ALL_MSG)
		);

	$r = new VA_Record($table_prefix . "orders");
	$r->add_hidden("operation", TEXT);
	$r->add_select("s_ct", TEXT, $cart_types, TYPE_MSG);
	$r->add_select("s_em", TEXT, $yes_no_all, EMAIL_FIELD);
	$r->add_select("s_tp", TEXT, $periods);	
	$r->add_textbox("s_sd", DATE, FROM_DATE_MSG);
	$r->change_property("s_sd", VALUE_MASK, $date_edit_format);
	$r->change_property("s_sd", TRIM, true);
	$r->add_textbox("s_ed", DATE, END_DATE_MSG);
	$r->change_property("s_ed", VALUE_MASK, $date_edit_format);
	$r->change_property("s_ed", TRIM, true);
	
	$r->add_select("s_rs", TEXT, $yes_no_all);
	$r->change_property("s_rs", DEFAULT_VALUE, 0);
	$r->add_textbox("s_ssd", DATE, SEND_DATE_FROM_MSG);
	$r->change_property("s_ssd", VALUE_MASK, $date_edit_format);
	$r->change_property("s_ssd", TRIM, true);
	$r->add_textbox("s_sed", DATE, SEND_DATE_TO_MSG);
	$r->change_property("s_sed", VALUE_MASK, $date_edit_format);
	$r->change_property("s_sed", TRIM, true);
	$r->get_form_parameters();
	$r->set_value("operation", "search");
	$r->validate();
	if ($operation != "search") {
		// set default values
		//$r->set_value("s_ct", 0);
		//$r->set_value("s_em", 1);
		//$r->set_value("s_rs", 0);
	}
	$r->set_form_parameters();

	// start building where for filtered values
	$where = "";
	//$where = " (sc.cart_email IS NOT NULL AND sc.cart_email<>'') ";
	if (!$r->is_empty("s_ct")) {
		if (strlen($where)) { $where .= " AND "; }
		if ($r->get_value("s_ct") == 1) {
			$where .= " sc.cart_type=1 ";				
		} else {
			$where .= " sc.cart_type=0 ";				
		}
	}
	if (!$r->is_empty("s_em")) {
		if (strlen($where)) { $where .= " AND "; }
		if ($r->get_value("s_em") == 1) {
			$where .= " ((sc.cart_email IS NOT NULL AND sc.cart_email<>'') OR (u.email IS NOT NULL AND u.email<>'') OR (u.delivery_email IS NOT NULL AND u.delivery_email<>'')) ";
		} else {
			$where .= " ((sc.cart_email IS NULL OR sc.cart_email='') AND (u.email IS NULL OR u.email='') AND (u.delivery_email IS NULL OR u.delivery_email='')) ";
		}
	}
	
	if (!$r->is_empty("s_sd")) {
		if (strlen($where)) { $where .= " AND "; }
		$where .= " sc.cart_updated>=" . $db->tosql($r->get_value("s_sd"), DATE);
	}
	
	if (!$r->is_empty("s_ed")) {
		if (strlen($where)) { $where .= " AND "; }
		$end_date = $r->get_value("s_ed");
		$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
		$where .= " sc.cart_updated<" . $db->tosql($day_after_end, DATE);
	}
	
	if (!$r->is_empty("s_rs")) {
		if (strlen($where)) { $where .= " AND "; }
		if ($r->get_value("s_rs")) {
			$where .= " sc.reminders_count>=1 " ;
		} else {
			$where .= " (sc.reminders_count=0 OR sc.reminders_count IS NULL) " ;
		}
	}
	
	if (!$r->is_empty("s_ssd")) {
		if (strlen($where)) { $where .= " AND "; }
		$where .= " sc.reminder_sent>=" . $db->tosql($r->get_value("s_ssd"), DATE);
	}
	
	if (!$r->is_empty("s_sed")) {
		if (strlen($where)) { $where .= " AND "; }
		$end_date = $r->get_value("s_sed");
		$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
		$where .= " sc.reminder_sent<" . $db->tosql($day_after_end, DATE);
	}
	// end building where				

	if ($operation == "delete-selected" || $operation == "delete-filtered") {
	  $carts_sql  = " SELECT sc.cart_id ";
		if ($operation == "delete-selected") {
			$carts_sql .= " FROM " . $table_prefix . "saved_carts sc ";
			$carts_sql .= " WHERE sc.cart_id IN (" . $db->tosql($cart_ids, INTEGERS_LIST) . ")";  
		} else {
			$carts_sql .= " FROM (" . $table_prefix . "saved_carts sc ";
			$carts_sql .= " LEFT JOIN " . $table_prefix . "users u ON sc.user_id=u.user_id) ";
			if ($where) { $carts_sql .= " WHERE " . $where; 	}
		}
		$carts_sql .= " ORDER BY sc.cart_id ";
		$db->RecordsPerPage = 100;
		$db->PageNumber = 1;
		$db->query($carts_sql);
		while ($db->next_record()) {
			$cart_ids = array();
			do {
				$cart_ids[] = $db->f("cart_id");
			} while ($db->next_record());

			// delete carts one by one
			foreach ($cart_ids as $cart_id) {
				$sql = " DELETE FROM ".$table_prefix."saved_items_properties ";
				$sql.= " WHERE cart_id=".$db->tosql($cart_id, INTEGER);
				$db->query($sql);
				$sql = " DELETE FROM ".$table_prefix."saved_items ";
				$sql.= " WHERE cart_id=".$db->tosql($cart_id, INTEGER);
				$db->query($sql);
				$sql = " DELETE FROM ".$table_prefix."saved_carts ";
				$sql.= " WHERE cart_id=".$db->tosql($cart_id, INTEGER);
				$db->query($sql);
			}
			// check for next carts
			$db->query($carts_sql);
		}
	}

  
	$all_carts_settings = array();
	$sql  = " SELECT site_id, setting_name, setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type='saved_cart' ";
	$sql .= " ORDER BY site_id ASC ";
	$db->query($sql);
	while($db->next_record()) {
		$site_id = $db->f("site_id");
		$setting_name = $db->f("setting_name");
		$setting_value = $db->f("setting_value");
		$all_carts_settings[$site_id][$setting_name] = $setting_value;
	}
	$parent_cart_settings = isset($all_carts_settings[1]) ? $all_carts_settings[1] : array();

	$reminder_from = get_setting_value($parent_cart_settings, "reminder_from", $settings["admin_email"]);
	$reminder_cc = get_setting_value($parent_cart_settings, "reminder_cc");
	$reminder_bcc = get_setting_value($parent_cart_settings, "reminder_bcc");
	$reminder_reply_to = get_setting_value($parent_cart_settings, "reminder_reply_to");
	$reminder_return_path = get_setting_value($parent_cart_settings, "reminder_return_path");
	$reminder_subject = get_translation(get_setting_value($parent_cart_settings, "reminder_subject"));
	$reminder_mail_type = get_setting_value($parent_cart_settings, "reminder_mail_type");
	$reminder_body = get_translation(get_setting_value($parent_cart_settings, "reminder_body"));

	if ($reminder_from) {
		$t->set_var("reminder_from", htmlspecialchars($reminder_from));
		$t->sparse("reminder_from_block", false);
	}
	if ($reminder_cc) {
		$t->set_var("reminder_cc", htmlspecialchars($reminder_cc));
		$t->sparse("reminder_cc_block", false);
	}
	if ($reminder_bcc) {
		$t->set_var("reminder_bcc", htmlspecialchars($reminder_bcc));
		$t->sparse("reminder_bcc_block", false);
	}
	if ($reminder_reply_to) {
		$t->set_var("reminder_reply_to", htmlspecialchars($reminder_reply_to));
		$t->sparse("reminder_reply_to_block", false);
	}
	if ($reminder_return_path) {
		$t->set_var("reminder_return_path", htmlspecialchars($reminder_return_path));
		$t->sparse("reminder_return_path_block", false);
	}

	// get random data for preview
	$sql  = " SELECT sc.cart_id, sc.user_id, sc.cart_type, sc.cart_name, sc.cart_email, ";
	$sql .= " u.name, u.first_name, u.last_name, u.email, u.delivery_email, ";
	$sql .= " sc.cart_quantity, sc.cart_total, sc.cart_added, sc.cart_updated, sc.site_id, ";
	$sql .= " sc.reminder_sent, sc.reminders_count, sc.country_id, sc.country_code, sc.user_ip ";
	$sql .= " FROM (" . $table_prefix . "saved_carts sc ";
	$sql .= " LEFT JOIN " . $table_prefix . "users u ON sc.user_id=u.user_id) ";
	if ($where) { 
		$sql .= " WHERE " . $where;
	}
	$sql .= " ORDER BY sc.cart_id ";
	$db->RecordsPerPage = 1;
	$db->PageNumber = 1;
	$db->query($sql); 
	if ($db->next_record()) {
		$cart_id    = $db->f("cart_id");
		$cart_type  = $db->f("cart_type");
		$cart_name = $db->f("cart_name");
		$cart_total = $db->f("cart_total");

		$user_name = $db->f("name");
		$first_name = $db->f("first_name");
		$last_name = $db->f("last_name");
		prepare_user_name($user_name, $first_name, $last_name);

		$user_email = $db->f("cart_email");
		if (!$user_email) { $user_email = $db->f("email"); }
		if (!$user_email) { $user_email = $db->f("delivery_email"); }

		// set vars for preview 
		$t->set_var("cart_id", $cart_id);
		$t->set_var("cart_type", $cart_type);
		$t->set_var("cart_name", $cart_name);
		$t->set_var("cart_total", $cart_total);

		$t->set_var("user_name", $user_name);
		$t->set_var("user_email", $user_email);

		// get saved items
		$items = array();
		$sql  = " SELECT si.cart_item_id, si.item_name, si.quantity, si.price, i.item_code, i.manufacturer_code ";
		$sql .= " FROM (" . $table_prefix . "saved_items si ";
		$sql .= " LEFT JOIN " . $table_prefix . "items i ON si.item_id=i.item_id) ";
		$sql .= " WHERE cart_id=".$db->tosql($cart_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$cart_item_id = $db->f("cart_item_id");
			$item_name = $db->f("item_name");
			$item_code = $db->f("item_code");
			$manufacturer_code = $db->f("manufacturer_code");
			$quantity = $db->f("quantity");
			$price = $db->f("price");
			$items[$cart_item_id] = array(
				"item_name" => $item_name,
				"item_code" => $item_code,
				"manufacturer_code" => $manufacturer_code,
				"quantity" => $quantity,
				"price" => $price,
				"affiliate_commission" => 0,
				"html_item_properties" => "",
				"text_item_properties" => "",
				"price" => $price,
			);
		}

		set_items_tag($items, $reminder_mail_type, $reminder_body, "saved_items");
	}

	$t->set_block("reminder_subject", $reminder_subject);
	$t->set_block("reminder_body", $reminder_body);
	$t->parse("reminder_subject", false);
	$t->parse("reminder_body", false);

	$preview_subject = $t->get_var("reminder_subject");
	$preview_body = $t->get_var("reminder_body");
	$t->set_var("reminder_subject", htmlspecialchars($preview_subject));
	if ($reminder_mail_type) {
		$t->set_var("reminder_body", $preview_body);
	} else {
		$t->set_var("reminder_body", nl2br(htmlspecialchars($preview_body)));
	}

	// check number of prepared and sent reminders
	$sql  = " SELECT COUNT(*) FROM ".$table_prefix."saved_carts ";
	$sql .= " WHERE reminder_status=1 OR reminder_status=2 ";
	$reminders_total = get_db_value($sql);

	// check number of sent reminders
	$sql  = " SELECT COUNT(*) FROM ".$table_prefix."saved_carts ";
	$sql .= " WHERE reminder_status=2 ";
	$reminders_sent = get_db_value($sql);

	$t->set_var("reminders_total", $reminders_total);
	$t->set_var("reminders_sent", $reminders_sent);

	if ($reminders_total > $reminders_sent) {
		$t->set_var("start_button_style", "display: none;");
		$t->set_var("stop_button_style", "display: none;");
		$t->set_var("continue_button_style", "");
		$t->set_var("cancel_button_style", "");
		$t->set_var("close_icon_style", "display: none;");
	} else {
		$t->set_var("popup_area_style", "display: none;");
		$t->set_var("start_button_style", "");
		$t->set_var("stop_button_style", "display: none;");
		$t->set_var("continue_button_style", "display: none;");
		$t->set_var("cancel_button_style", "display: none;");
	}

	if ($operation == "start") {
		// set special status for carts to send reminders 
		$sql = " UPDATE ".$table_prefix."saved_carts SET reminder_status=1 ";
		$cart_ids = get_param("cart_ids");
		if ($cart_ids) {
			$sql .= " WHERE cart_id IN (".$db->tosql($cart_ids, INTEGERS_LIST).")";
		} else if ($where) {
			$sql .= " WHERE " . str_replace("sc.", "", $where);
		}
		$db->query($sql);
		$operation = "send";
		update_admin_settings(array("cart_reminder_operation" => "send"));
	}

	if ($operation == "continue") {
		$operation = "send";
		update_admin_settings(array("cart_reminder_operation" => "send"));
	}

	if ($operation == "stop") {
		update_admin_settings(array("cart_reminder_operation" => "stop"));
		if ($ajax) {
			echo json_encode(
				array(
					"status" => "ok", 
				)
			);
			exit;
		}
	}
	if ($operation == "cancel") {
		$sql  = " UPDATE ".$table_prefix."saved_carts SET reminder_status=0 ";
		$sql .= " WHERE reminder_status=1 OR reminder_status=2 ";
		$db->query($sql);

		update_admin_settings(array("cart_reminder_operation" => "stop"));
		echo json_encode(
			array(
				"status" => "cancelled", 
			)
		);
		exit;
	}

	if ($operation == "check") {

		// check number of prepared and sent reminders
		$sql  = " SELECT COUNT(*) FROM ".$table_prefix."saved_carts ";
		$sql .= " WHERE reminder_status=1 OR reminder_status=2 ";
		$reminders_total = get_db_value($sql);

		// check number of sent reminders
		$sql  = " SELECT COUNT(*) FROM ".$table_prefix."saved_carts ";
		$sql .= " WHERE reminder_status=2 ";
		$reminders_sent = get_db_value($sql);

		echo json_encode(
			array(
				"status" => "ok", 
				"reminders_total" => $reminders_total, 
				"reminders_sent" => $reminders_sent,
			)
		);
		exit;
	}

	if ($operation == "send") {
		// check stop status
		$cart_reminder_operation = get_admin_settings("cart_reminder_operation");

		$delay = get_param("delay");

		$start_point = microtime(true); // save start point to check script run time
		$runtime = 0; $runtime_limit = 20;
		$emails_count = 0; $emails_limit = 10;
	
		$cart_sql  = " SELECT sc.cart_id, sc.user_id, sc.cart_type, sc.cart_name, sc.cart_email, ";
		$cart_sql .= " u.name, u.first_name, u.last_name, u.email, u.delivery_email, ";
		$cart_sql .= " sc.cart_quantity, sc.cart_total, sc.cart_added, sc.cart_updated, sc.site_id, ";
		$cart_sql .= " sc.reminder_sent, sc.reminders_count, sc.country_id, sc.country_code, sc.user_ip ";
		$cart_sql .= " FROM (" . $table_prefix . "saved_carts sc ";
		$cart_sql .= " LEFT JOIN " . $table_prefix . "users u ON sc.user_id=u.user_id) ";
		$cart_sql .= " WHERE reminder_status=1 ";
		$db->RecordsPerPage = 1;
		$db->PageNumber = 1;
		$db->query($cart_sql);
		while ($cart_reminder_operation != "stop" && $runtime < $runtime_limit && $emails_count < $emails_limit && $db->next_record()) {

			// get site settings
			$site_id = $db->f("site_id");
			$site_cart_settings = isset($all_carts_settings[$site_id]) ? $all_carts_settings[$site_id] : $parent_cart_settings;

			$reminder_from = get_setting_value($site_cart_settings, "reminder_from", $settings["admin_email"]);
			$reminder_cc = get_setting_value($site_cart_settings, "reminder_cc");
			$reminder_bcc = get_setting_value($site_cart_settings, "reminder_bcc");
			$reminder_reply_to = get_setting_value($site_cart_settings, "reminder_reply_to");
			$reminder_return_path = get_setting_value($site_cart_settings, "reminder_return_path");
			$reminder_subject = get_translation(get_setting_value($site_cart_settings, "reminder_subject"));
			$reminder_mail_type = get_setting_value($site_cart_settings, "reminder_mail_type");
			$reminder_body = get_translation(get_setting_value($site_cart_settings, "reminder_body"));

			// prepare email fields
			$email_headers = array();
			$email_headers["from"] = $reminder_from;
			$email_headers["cc"] = $reminder_cc;
			$email_headers["bcc"] = $reminder_bcc;
			$email_headers["reply_to"] = $reminder_reply_to;
			$email_headers["return_path"] = $reminder_return_path;
			$email_headers["mail_type"] = $reminder_mail_type;


			$cart_id    = $db->f("cart_id");
			$cart_type  = $db->f("cart_type");
			$cart_name  = $db->f("cart_name");
			$cart_total = $db->f("cart_total");

			$user_name = $db->f("name");
			$first_name = $db->f("first_name");
			$last_name = $db->f("last_name");
			prepare_user_name($user_name, $first_name, $last_name);

			$user_email = $db->f("cart_email");
			if (!$user_email) { $user_email = $db->f("email"); }
			if (!$user_email) { $user_email = $db->f("delivery_email"); }

			$reminders_count = $db->f("reminders_count");

			$email_sent = false;
			if ($user_email) {
				$emails_count++;
				$reminders_count++;

				// set vars and send reminder
				$t->set_var("cart_id", $cart_id);
				$t->set_var("cart_type", $cart_type);
				$t->set_var("cart_name", $cart_name);
				$t->set_var("cart_total", $cart_total);

				$t->set_var("user_name", $user_name);
				$t->set_var("user_email", $user_email);

				// get saved items
				$items = array();
				$sql  = " SELECT si.cart_item_id, si.item_name, si.quantity, si.price, i.item_code, i.manufacturer_code ";
				$sql .= " FROM (" . $table_prefix . "saved_items si ";
				$sql .= " LEFT JOIN " . $table_prefix . "items i ON si.item_id=i.item_id) ";
				$sql .= " WHERE cart_id=".$db->tosql($cart_id, INTEGER);
				$db->query($sql);
				while ($db->next_record()) {
					$cart_item_id = $db->f("cart_item_id");
					$item_name = $db->f("item_name");
					$item_code = $db->f("item_code");
					$manufacturer_code = $db->f("manufacturer_code");
					$quantity = $db->f("quantity");
					$price = $db->f("price");
					$items[$cart_item_id] = array(
						"item_name" => $item_name,
						"item_code" => $item_code,
						"manufacturer_code" => $manufacturer_code,
						"quantity" => $quantity,
						"price" => $price,
						"affiliate_commission" => "",
						"html_item_properties" => "",
						"text_item_properties" => "",
						"price" => $price,
					);
				}
				set_items_tag($items, $reminder_mail_type, $reminder_body, "saved_items");

				$t->set_block("mail_subject", $reminder_subject);
				$t->set_block("mail_body", $reminder_body);
				$t->parse("mail_subject", false);
				$t->parse("mail_body", false);
				$mail_body = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("mail_body"));
				$email_sent = @va_mail($user_email, $t->get_var("mail_subject"), $mail_body, $email_headers);
				if ($delay) {
					usleep($delay);
				}
			}

			// update status that email was sent
			$sql  = " UPDATE ".$table_prefix."saved_carts SET reminder_status=2 ";
			if ($email_sent) {
				$sql .= " , reminder_sent=" . $db->tosql(va_time(), DATETIME);
				$sql .= " , reminders_count=" . $db->tosql($reminders_count, INTEGER);
			}
			$sql .= " WHERE cart_id=" . $db->tosql($cart_id, INTEGER);
			$db->query($sql);

			// check stop status
			$cart_reminder_operation = get_admin_settings("cart_reminder_operation");

			if ($cart_reminder_operation != "stop") {
				// check for next cart
				$db->query($cart_sql);
				$check_point = microtime(true); 
				$runtime = $check_point - $start_point;
			}
		}

		// check number of prepared and sent reminders
		$sql  = " SELECT COUNT(*) FROM ".$table_prefix."saved_carts ";
		$sql .= " WHERE reminder_status=1 OR reminder_status=2 ";
		$reminders_total = get_db_value($sql);

		// check number of sent reminders
		$sql  = " SELECT COUNT(*) FROM ".$table_prefix."saved_carts ";
		$sql .= " WHERE reminder_status=2 ";
		$reminders_sent = get_db_value($sql);

		$cart_status = "next";
		if ($reminders_total > 0 && $reminders_total == $reminders_sent) {
			// mark all sent reminders as finished
			$cart_status = "finished";
			$sql  = " UPDATE ".$table_prefix."saved_carts SET reminder_status=3 ";
			$sql .= " WHERE reminder_status=2 ";
			$db->query($sql);
		} else if ($cart_reminder_operation == "stop") {
			$cart_status = "stopped";
		}

		if ($ajax) {
			echo json_encode(
				array(
					"status" => $cart_status, 
					"reminders_total" => $reminders_total, 
					"reminders_sent" => $reminders_sent,
				)
			);
			exit;
		}
	}


	$va_countries = va_countries();

	// prepare dates for stats
	$current_date = va_time();
	$cyear = $current_date[YEAR]; $cmonth = $current_date[MONTH]; $cday = $current_date[DAY]; 
	$today_ts = mktime (0, 0, 0, $cmonth, $cday, $cyear);
	$tomorrow_ts = mktime (0, 0, 0, $cmonth, $cday + 1, $cyear);
	$yesterday_ts = mktime (0, 0, 0, $cmonth, $cday - 1, $cyear);
	$week_ts = mktime (0, 0, 0, $cmonth, $cday - 6, $cyear);
	$month_ts = mktime (0, 0, 0, $cmonth, 1, $cyear);
	$last_month_ts = mktime (0, 0, 0, $cmonth - 1, 1, $cyear);
	$last_month_days = date("t", $last_month_ts);
	$last_month_end = mktime (0, 0, 0, $cmonth - 1, $last_month_days, $cyear);
	$today_date = va_date($date_edit_format, $today_ts);

	$stats = array(
		array("title" => TODAY_MSG, "date_start" => $today_ts, "date_end" => $today_ts),
		array("title" => YESTERDAY_MSG, "date_start" => $yesterday_ts, "date_end" => $yesterday_ts),
		array("title" => LAST_SEVEN_DAYS_MSG, "date_start" => $week_ts, "date_end" => $today_ts),
		array("title" => THIS_MONTH_MSG, "date_start" => $month_ts, "date_end" => $today_ts),
		array("title" => LAST_MONTH_MSG, "date_start" => $last_month_ts, "date_end" => $last_month_end),
	);


	// get orders stats
	for ($i = 0; $i < sizeof($cart_types) - 1; $i++) {
		$cart_type = $cart_types[$i][0];
		$cart_type_name = $cart_types[$i][1];

		$t->set_var("cart_type",   $cart_type);
		$t->set_var("cart_type_name", $cart_type_name);

		$t->set_var("stats_periods", "");
		foreach ($stats as $key => $stat_info) {
			$start_date = $stat_info["date_start"];
			$end_date = va_time($stat_info["date_end"]);
			$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "saved_carts ";
			$sql .= " WHERE cart_type=" . $db->tosql($cart_type, INTEGER);
			$sql .= " AND cart_updated>=" . $db->tosql($start_date, DATE);
			$sql .= " AND cart_updated<" . $db->tosql($day_after_end, DATE);
			$period_carts = get_db_value($sql);
			if (isset($stats[$key]["total"])) {
				$stats[$key]["total"] += $period_carts;
			} else {
				$stats[$key]["total"] = $period_carts;
			}
			if($period_carts > 0) {
				$period_carts = "<a href=\"admin_carts.php?operation=search&s_ct=".$cart_type."&s_sd=".va_date($date_edit_format, $start_date)."&s_ed=".va_date($date_edit_format, $end_date)."\"><b>" . $period_carts."</b></a>";
			}
			$t->set_var("period_carts", $period_carts);
			$t->parse("stats_periods", true);
		}

		$t->parse("statuses_stats", true);
	}

	foreach ($stats as $key => $stat_info) {
		$t->set_var("start_date", va_date($date_edit_format, $stat_info["date_start"]));
		$t->set_var("end_date", va_date($date_edit_format, $stat_info["date_end"]));
		$t->set_var("stat_title", $stat_info["title"]);
		$t->set_var("period_total", $stat_info["total"]);
		$t->parse("stats_titles", true);
		$t->parse("stats_totals", true);
	}
	

	// check passed parameters
	$pass_parameters = array();
	foreach ($r->parameters as $param_name => $param_info) {
		$value_type = $param_info[VALUE_TYPE];
		$param_value = $param_info[CONTROL_VALUE];
		if ($value_type == DATETIME || $value_type == DATE || $value_type == TIMESTAMP || $value_type == TIME) {
			if (is_array($param_value)) {
				$pass_parameters[$param_name] = va_date($param_info[VALUE_MASK], $param_info[CONTROL_VALUE]);
			}
		} else if (strlen($param_value)) {
			$pass_parameters[$param_name] = $param_value;
		}
	}

	$ids = get_param("ids");
	$cart_id = get_param("cart_id");
	if ($cart_id) {
		$ids = $cart_id;
	}

	$where_sql = "";
	if ($where) {
		$where_sql = " WHERE " . $where;
	}

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_carts.php", "sort", "", $pass_parameters);
	$s->set_parameters(false, true, true, false);
	$s->set_default_sorting(2, "desc");
	$s->set_sorter(ID_MSG, "sorter_id", "1", "sc.cart_id", "", "");
	$s->set_sorter(LAST_UPDATED_MSG, "sorter_date", "2", "sc.cart_updated");
	$s->set_sorter(REMINDER_SEND_MSG, "sorter_reminder_sent", "6", "sc.reminder_sent");
	$s->set_sorter(CART_TOTAL_COLUMN, "sorter_cart_total", "3", "sc.cart_total");
	
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_carts.php");
	
	$sql  = " SELECT COUNT(*) ";
	$sql .= " FROM (" . $table_prefix . "saved_carts sc ";
	$sql .= " LEFT JOIN " . $table_prefix . "users u ON sc.user_id=u.user_id) ";
	$sql .= $where_sql;
	$total_records = get_db_value($sql);
	
	$records_per_page = set_recs_param("admin_carts.php", $pass_parameters);
	$pages_number = 5;
	
	$carts = array();
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false, $pass_parameters);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	
	$sql  = " SELECT sc.cart_id, sc.user_id, sc.cart_type, sc.cart_name, sc.cart_email, ";
	$sql .= " u.name, u.first_name, u.last_name, u.email, u.delivery_email, ";
	$sql .= " sc.cart_quantity, sc.cart_total, sc.cart_added, sc.cart_updated, sc.site_id, ";
	$sql .= " sc.reminder_sent, sc.reminders_count, sc.country_id, sc.country_code, sc.user_ip ";
	$sql .= " FROM (" . $table_prefix . "saved_carts sc ";
	$sql .= " LEFT JOIN " . $table_prefix . "users u ON sc.user_id=u.user_id) ";
	$sql .= $where_sql;
	$sql .= $s->order_by;	
	$db->query($sql);
	if ($db->next_record())
	{
		$cart_index = 0;
		do {

			$cart_id    = $db->f("cart_id");
			$cart_type  = $db->f("cart_type");
			$cart_name  = $db->f("cart_name");
			$cart_total = $db->f("cart_total");
	  
			$user_name = $db->f("name");
			$first_name = $db->f("first_name");
			$last_name = $db->f("last_name");
			prepare_user_name($user_name, $first_name, $last_name);

			$user_email = $db->f("cart_email");
			if (!$user_email) { $user_email = $db->f("email"); }
			if (!$user_email) { $user_email = $db->f("delivery_email"); }

			$cart_updated = $db->f("cart_updated", DATETIME);
			if (is_array($cart_updated)) {
				$cart_updated = va_date($datetime_show_format, $cart_updated);
			}
			
			$reminder_sent = $db->f("reminder_sent", DATETIME);
			$reminders_count = $db->f("reminders_count", INTEGER);
			
			$country_id = $db->f("country_id");
			$remote_address  = $db->f("user_ip");

			$t->set_var("cart_id", $cart_id);
			$t->set_var("user_name", htmlspecialchars($user_name));
			$t->set_var("user_email", htmlspecialchars($user_email));
			if ($user_email) {
				$t->sparse("user_email_block", false);
			} else {
				$t->set_var("user_email_block", "");
			}

			$t->set_var("cart_updated", $cart_updated);
			$t->set_var("cart_total", currency_format($cart_total));
		
			$carts[$cart_id] = array(
				"cart_id" => $cart_id, "cart_type" => $cart_type, "cart_name" => $cart_name, 
				"user_name" => $user_name, "user_email" => $user_email, 
				"cart_updated" => $cart_updated, "cart_total" => $cart_total, "country_id" => $country_id, 
				"reminders_count" => $reminders_count, "reminder_sent" => $reminder_sent, 
			);

		} while ($db->next_record());
	}


	if (sizeof($carts) > 0)
	{
		$cart_index = 0;
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		foreach ($carts as $cart_id => $cart_data) {
			$cart_index++;

			$cart_id    = $cart_data["cart_id"];
			$cart_total  = $cart_data["cart_total"];
			$cart_type = $cart_data["cart_type"];
			$cart_name = $cart_data["cart_name"];
			$cart_type_name = ($cart_type == 1) ? va_constant("SAVED_MSG") : va_constant("ACTIVE_MSG");

			$user_name = $cart_data["user_name"];
			$user_email = $cart_data["user_email"];
			$country_id = $cart_data["country_id"];
			$cart_updated = $cart_data["cart_updated"];

			$reminders_count = $cart_data["reminders_count"];
			$reminder_sent = $cart_data["reminder_sent"];

			$t->set_var("cart_index", $cart_index);

			$t->set_var("cart_id", $cart_id);
			$t->set_var("cart_type", $cart_type);
			$t->set_var("cart_type_name", $cart_type_name);
			$t->set_var("cart_name", htmlspecialchars($cart_name));
			$t->set_var("user_name", htmlspecialchars($user_name));
			$t->set_var("user_email", htmlspecialchars($user_email));
			if ($user_email) {
				$t->sparse("user_email_block", false);
			} else {
				$t->set_var("user_email_block", "");
			}

			$t->set_var("cart_updated", $cart_updated);
			if (is_array($reminder_sent)) {
				$reminder_sent_formatted = va_date($datetime_show_format, $reminder_sent);
				$t->set_var("reminder_sent", $reminder_sent_formatted);
			} else {
				$t->set_var("reminder_sent", va_message("NEVER_MSG"));
			}
			

			$t->set_var("cart_total", currency_format($cart_total));
			
			$country_code = isset($va_countries[$country_id]) ? $va_countries[$country_id]["country_code"] : "";
			$t->set_var("country_code", $country_code);
					
			$sql  = "SELECT ip_address FROM " . $table_prefix . "black_ips WHERE ip_address=" . $db->tosql($remote_address, TEXT);
			$db->query($sql);
			if ($db->next_record()) {
				$row_style = "rowWarn";
			} else {
				$row_style = ($cart_index % 2 == 0) ? "row-even" : "row-odd";
			}
			$t->set_var("row_style", $row_style);

			$t->set_var("cart_items", "");
			$total_quantity = 0;
			$total_price = 0;
			$sql  = " SELECT item_id, item_name, quantity, price ";
			$sql .= " FROM " . $table_prefix . "saved_items ";
			$sql .= " WHERE cart_id=" . $db->tosql($cart_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$item_name = get_translation($db->f("item_name"));
				$quantity = $db->f("quantity");
				$price = $db->f("price");

				$total_quantity += $quantity;
				$total_price += ($price * $quantity);

				$t->set_var("item_name", $item_name);
				$t->set_var("quantity",  $quantity);
				$t->set_var("price", currency_format($price));
				$t->parse("cart_items", true);
			}
			$t->set_var("total_quantity", $total_quantity);
			$t->set_var("total_price", currency_format($total_price));

			$t->parse("records", true);
		} 
		$t->set_var("carts_number", $cart_index);
	} else {
		$t->set_var("sorters", "");
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}
	
	if ($total_records > 0) {
		$t->set_var("total_filtered", $total_records);
		$t->parse("send_reminder_filtered", false);

		// show delete url 
		$df = new VA_URL("admin_carts.php", false);
		$df->add_parameter("operation", CONSTANT, "delete-filtered");
		$df->add_parameter("s_ct", REQUEST, "s_ct");
		$df->add_parameter("s_em", REQUEST, "s_em");
		$df->add_parameter("s_sd", REQUEST, "s_sd");
		$df->add_parameter("s_ed", REQUEST, "s_ed");
		$df->add_parameter("s_rs", REQUEST, "s_rs");
		$df->add_parameter("s_ssd", REQUEST, "s_ssd");
		$df->add_parameter("s_sed", REQUEST, "s_sed");
		$df->add_parameter("s", REQUEST, "s");
		$t->set_var("delete_filtered_url", htmlspecialchars($df->get_url()));

		$t->parse("delete_filtered", false);
	}
	
	include_once("./admin_header.php");
	include_once("./admin_footer.php");
	
	$t->pparse("main");
?>