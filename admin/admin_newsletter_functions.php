<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_newsletter_functions.php                           ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	function generate_emails($newsletter_id)
	{
		global $db, $dbs, $table_prefix;
		if (!isset($dbs) || !is_object($dbs)) { $dbs = new VA_SQL($db); }

		// check if it's newsletter 
		$sql  = " SELECT newsletter_type FROM " . $table_prefix . "newsletters ";
		$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
		$newsletter_type = get_db_value($sql);
		if ($newsletter_type == 2) {
			return false;
		}

		// 1 - subscribed users, 2 - registered users, 4 - orders users, 8 - admin users, 16 - custom emails

		// initialize filters record
		$fr = new VA_Record($table_prefix . "newsletter_filters", "newsletter_filters");
		// subscribed filters 
		$fr->add_checkboxlist("s_sites", TEXT, "");
		// user emails
		$fr->add_checkboxlist("u_groups", TEXT, "");
		$fr->add_checkboxlist("u_sites", TEXT, "");
		// orders emails
		$fr->add_textbox("o_sd", DATETIME);
		$fr->add_textbox("o_ed", DATETIME);
		$fr->add_textbox("os_sd", DATETIME);
		$fr->add_textbox("os_ed", DATETIME);
		$fr->add_checkboxlist("o_os", TEXT, "");
		$fr->add_checkboxlist("o_sites", TEXT, "");
		$fr->add_textbox("o_new_os", TEXT, "");
		$fr->add_textbox("o_ci", TEXT);
		$fr->add_textbox("o_si", TEXT);
  
		$fr->add_textbox("o_total_min", FLOAT);
		$fr->add_textbox("o_total_max", FLOAT);
		$fr->add_textbox("o_grand_min", FLOAT);
		$fr->add_textbox("o_grand_max", FLOAT);
		$fr->add_textbox("o_coupon_code", TEXT);
		$fr->add_textbox("o_user_status", TEXT);
		// admin emails
		$fr->add_checkboxlist("a_groups", TEXT, "");

		$sql  = " SELECT filter_parameter, filter_value ";
		$sql .= " FROM  " . $table_prefix . "newsletter_filters ";
		$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$filter_parameter = $db->f("filter_parameter");
			$filter_value = $db->f("filter_value");
			$value_type = $fr->get_property_value($filter_parameter, VALUE_TYPE);
			if (is_numeric($filter_value) && ($value_type == DATETIME || $value_type == DATE || $value_type == TIMESTAMP || $value_type == TIME)) {
				// convert integer timestamp value to viart date value
				$filter_value = va_time($filter_value);
			}
			$fr->set_value($filter_parameter, $filter_value);
		}

		$sql  = " SELECT * FROM " . $table_prefix . "newsletters ";
		$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$subscribed_recipients = $db->f("subscribed_recipients");
		}

		if ($subscribed_recipients == "all" || $subscribed_recipients == 1) {
			$where = " WHERE email IS NOT NULL AND email<>'' ";
			$s_sites = $fr->get_value("s_sites");
			if (is_array($s_sites) && sizeof($s_sites) > 0) {
				if (strlen($where)) { $where .= " AND "; }
				$where .= " nu.site_id IN(" . $db->tosql($s_sites, INTEGERS_LIST) . ")";
			}

			$sql  = " INSERT INTO " . $table_prefix . "newsletters_emails (newsletter_id, site_id, email_type, user_email) ";
			$sql .= " SELECT " . $db->tosql($newsletter_id, INTEGER) . ", nu.site_id, 1, nu.email FROM " . $table_prefix . "newsletters_users nu ";
			$sql .= $where;
			$sql .= " GROUP BY nu.email, nu.site_id ";
			$db->query($sql);
		}


		// check user options
		$where = "";
		$u_groups = $fr->get_value("u_groups");
		if (is_array($u_groups) && sizeof($u_groups) > 0) {
			$where  = " WHERE u.user_type_id IN (" . $db->tosql($u_groups, INTEGERS_LIST) . ")";
			$where .= " AND u.is_approved=1 ";
			$where .= " AND u.email IS NOT NULL AND u.email<>'' ";
			$u_sites = $fr->get_value("u_sites");
			if (is_array($u_sites) && sizeof($u_sites) > 0) {
				if (strlen($where)) { $where .= " AND "; }
				$where .= " u.site_id IN(" . $db->tosql($u_sites, INTEGERS_LIST) . ")";
			}
			$sql  = " INSERT INTO " . $table_prefix . "newsletters_emails (newsletter_id, site_id, email_type, user_id, user_email, user_name) ";
			$sql .= " SELECT " . $db->tosql($newsletter_id, INTEGER) . ", u.site_id, 2, u.user_id, u.email, u.name ";
			$sql .= " FROM " . $table_prefix . "users u ";
			$sql .= $where;
			$sql .= " GROUP BY u.email, u.site_id ";
			$db->query($sql);
		}

		// check orders users
		$where = ""; $join = "";
		if (!$fr->is_empty("o_sd")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " o.order_placed_date>=" . $db->tosql($fr->get_value("o_sd"), DATE);
		}

		if (!$fr->is_empty("o_ed")) {
			if (strlen($where)) { $where .= " AND "; }
			$end_date = $fr->get_value("o_ed");
			$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
			$where .= " o.order_placed_date<" . $db->tosql($day_after_end, DATE);
		}

		if (!$fr->is_empty("os_sd")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " o.order_shipped_date>=" . $db->tosql($fr->get_value("os_sd"), DATE);
		}

		if (!$fr->is_empty("os_ed")) {
			if (strlen($where)) { $where .= " AND "; }
			$end_date = $fr->get_value("os_ed");
			$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
			$where .= " o.order_shipped_date<" . $db->tosql($day_after_end, DATE);
		}

		$o_os = $fr->get_value("o_os");
		if (is_array($o_os) && sizeof($o_os) > 0) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " o.order_status IN(" . $db->tosql($o_os, INTEGERS_LIST) . ")";
		}

		$o_sites = $fr->get_value("o_sites");
		if (is_array($o_sites) && sizeof($o_sites) > 0) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " o.site_id IN(" . $db->tosql($o_sites, INTEGERS_LIST) . ")";
		}

		if (!$fr->is_empty("o_ci")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " o.delivery_country_id=" . $db->tosql($fr->get_value("o_ci"), INTEGER);
		}

		if (!$fr->is_empty("o_si")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " o.delivery_state_id=" . $db->tosql($fr->get_value("o_si"), INTEGER);
		}

		if (!$fr->is_empty("o_total_min")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " o.order_total>=" . $db->tosql($fr->get_value("o_total_min"), NUMBER);
		}

		if (!$fr->is_empty("o_total_max")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " o.order_total<=" . $db->tosql($fr->get_value("o_total_max"), NUMBER);
		}

		if (!$fr->is_empty("o_grand_min")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " o.orders_prev_total>=" . $db->tosql($fr->get_value("o_grand_min"), NUMBER);
		}

		if (!$fr->is_empty("o_grand_max")) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " o.orders_prev_total<=" . $db->tosql($fr->get_value("o_grand_max"), NUMBER);
		}

		if (!$fr->is_empty("o_coupon_code")) {
			if (strlen($where)) { $where .= " AND "; }
			$join .= " INNER JOIN " . $table_prefix . "orders_coupons oc ON oc.order_id=o.order_id ";
			$where .= " oc.coupon_code=" . $db->tosql($fr->get_value("o_coupon_code"), TEXT);
		}

		$o_user_status = $fr->get_value("o_user_status");
		if ($o_user_status == "new") {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " o.orders_prev_number=1 ";
		} else if ($o_user_status == "return") {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " o.orders_prev_number>1 ";
		}

		// add orders users
		if ($where) {
			$where .= " AND (o.is_unsubscribed=0 OR o.is_unsubscribed IS NULL) ";
			$sql  = " INSERT INTO " . $table_prefix . "newsletters_emails (newsletter_id, site_id, email_type, order_id, user_email, user_name) ";
			$sql .= " SELECT " . $db->tosql($newsletter_id, INTEGER) . ", o.site_id, 4, o.order_id, o.email,o.name ";
			$sql .= " FROM " . $table_prefix . "orders o ";
			$sql .= " WHERE " . $where;
			$sql .= " AND o.email IS NOT NULL AND o.email<>'' ";
			$sql .= " GROUP BY o.email, o.name, o.site_id ";
			$db->query($sql);
		}

		$where = "";
		// add admin users
		$a_groups = $fr->get_value("a_groups");
		if (is_array($a_groups) && sizeof($a_groups) > 0) {
			$sql  = " INSERT INTO " . $table_prefix . "newsletters_emails (newsletter_id,email_type, admin_id, user_email,user_name) ";
			$sql .= " SELECT " . $db->tosql($newsletter_id, INTEGER) . ", 8, a.admin_id, a.email,a.admin_name ";
			$sql .= " FROM " . $table_prefix . "admins a, " . $table_prefix . "admin_privileges ap ";
			$sql .= " WHERE a.privilege_id=ap.privilege_id ";
			$sql .= " AND a.privilege_id IN (" . $db->tosql($a_groups, INTEGERS_LIST) . ")";
			$sql .= " AND a.email IS NOT NULL AND a.email<>'' ";
			$sql .= " GROUP BY a.email,a.admin_name ";
			$db->query($sql);
		}

		// check custom list
		$sql  = " SELECT custom_recipients FROM " . $table_prefix ."newsletters ";
		$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
		$custom_recipients = get_db_value($sql);
		if ($custom_recipients) {
			$custom_recipients_array = check_custom_recipients($custom_recipients, $email_errors);
			foreach ($custom_recipients_array as $i => $email) {
				$custom_recipients_array[$i] = "(".$db->tosql($newsletter_id, INTEGER).",".$db->tosql($email, TEXT).",1)";
			}
			if (count($custom_recipients_array) > 0) {
				$sql  = "INSERT INTO  " . $table_prefix . "newsletters_emails (newsletter_id,user_email,is_custom) VALUES ";
				$sql .= implode(",", $custom_recipients_array);
				$db->query($sql);
			}
		}

		// check and delete duplicated emails
		$sql  = " SELECT MIN(email_id) AS min_email_id, user_email, site_id, COUNT(*) AS email_count ";
		$sql .= " FROM ".$table_prefix."newsletters_emails WHERE newsletter_id=".$db->tosql($newsletter_id, INTEGER);
		$sql .= " GROUP BY user_email, site_id HAVING COUNT(*) > 1 ";
		$dbs->query($sql);
		while ($dbs->next_record()) {
			$min_email_id = $dbs->f("min_email_id");
			$mail_site_id = $dbs->f("site_id");
			$user_email = $dbs->f("user_email");
			// delete duplicated email records
			$sql  = " DELETE FROM ".$table_prefix."newsletters_emails ";
			$sql .= " WHERE newsletter_id=". $db->tosql($newsletter_id, INTEGER);
			$sql .= " AND user_email=". $db->tosql($user_email, TEXT);
			$sql .= " AND site_id=". $db->tosql($mail_site_id, INTEGER);
			$sql .= " AND email_id>". $db->tosql($min_email_id, INTEGER);
			$db->query($sql);
		}

		// count emails
		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "newsletters_emails ";
		$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
		$emails_total = get_db_value($sql);

		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "newsletters_emails ";
		$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
		$sql .= " AND is_sent=1 ";
		$emails_sent = get_db_value($sql);
		$emails_left = $emails_total - $emails_sent;
	
		// update table with emails qty
		$sql  = " UPDATE " . $table_prefix . "newsletters ";
		$sql .= " SET emails_total=".$db->tosql($emails_total, INTEGER);
		$sql .= " , emails_sent=" . $db->tosql($emails_sent, INTEGER);
		$sql .= " , emails_left=" . $db->tosql($emails_left, INTEGER);
		$sql .= " , is_prepared=1 ";
		$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
		$db->query($sql);
	}

	function send_newsletter($email_data, $newsletter_data, $newsletter_filters = array())
	{
		global $t, $db, $table_prefix, $settings, $sites;

		$eol = get_eol();
		// prepare newsletter data
		$newsletter_id = $newsletter_data["newsletter_id"];
		$mail_type = $newsletter_data["mail_type"];
		$newsletter_subject = $newsletter_data["newsletter_subject"];
		$newsletter_body = $newsletter_data["newsletter_body"];
		// check new order status to update after sending newsletter
		$o_new_os = get_setting_value($newsletter_filters, "o_new_os", "");

		$email_headers = array();
		$email_headers["from"] = $newsletter_data["mail_from"];
		$email_headers["reply_to"] = $newsletter_data["mail_reply_to"];
		$email_headers["return_path"] = $newsletter_data["mail_return_path"];
		$email_headers["cc"] = $newsletter_data["mail_cc"];
		$email_headers["bcc"] = $newsletter_data["mail_bcc"];
		$email_headers["mail_type"] = $mail_type;

		if (!isset($t)) {
			$t = new VA_Template($settings["templates_dir"]);
		}
		// clear tags 
		$t->set_var("eid", "");
		$t->set_var("email_id", "");
		$t->set_var("user_id", "");
		$t->set_var("order_id", "");
		$t->set_var("admin_id", "");
		$t->set_var("email", "");
		$t->set_var("user_email", "");
		$t->set_var("name", "");
		$t->set_var("user_name", "");
		$t->set_var("first_name", "");
		$t->set_var("last_name", "");
		// clear order tags
		$t->delete_var("basket");
		$t->delete_var("basket_html");
		$t->delete_var("basket_text");
		$t->delete_var("order_items");
		$t->delete_var("html_order_items");
		$t->delete_var("text_order_items");


		$t->set_block("mail_subject", $newsletter_subject);
		$t->set_block("mail_body", $newsletter_body);

		// check user data
		$email_id = $email_data["email_id"];
		$email_site_id = $email_data["site_id"];
		if (!$email_site_id) { $email_site_id = 1; } // use master site if site_id isn't available
		$user_email = $email_data["user_email"];
		$user_name = $email_data["user_name"];
		$order_id = $email_data["order_id"];
		$user_id = $email_data["user_id"];
		$admin_id = $email_data["admin_id"];

		// set main tags
		prepare_user_name($user_name, $first_name, $last_name);

		$t->set_var("eid", $email_id);
		$t->set_var("email_id", $email_id);
		$t->set_var("site_id", $email_site_id);
		$t->set_var("email_site_id", $email_site_id);
		$t->set_var("email", $user_email);
		$t->set_var("user_email", $user_email);
		$t->set_var("user_id", $user_id);
		$t->set_var("order_id", $order_id);
		$t->set_var("admin_id", $admin_id);
		$t->set_var("name", $user_name);
		$t->set_var("user_name", $user_name);
		$t->set_var("first_name", $first_name);
		$t->set_var("last_name", $last_name);

		// get and set site tags
		$site_settings = (isset($sites) && isset($sites[$email_site_id])) ? $sites[$email_site_id] : $settings;
		$site_name = get_setting_value($site_settings, "site_name");
		$site_url = get_setting_value($site_settings, "site_url");
		$site_admin_url = get_setting_value($site_settings, "admin_url");
		$site_image_url = get_setting_value($site_settings, "image_url");
		$site_description = get_setting_value($site_settings, "site_name");
		$site_class = get_setting_value($site_settings, "site_class");
		$site_short_name = get_setting_value($site_settings, "short_name");
		$t->set_var("site_name", $site_name);
		$t->set_var("site_url", $site_url);
		$t->set_var("site_admin_url", $site_admin_url);
		$t->set_var("site_image_url", $site_image_url);
		$t->set_var("site_description", $site_description);
		$t->set_var("site_class", $site_class);
		$t->set_var("site_short_name", $site_short_name);

		// check additional data
		$data = array();		
		$order_status = "";
		if ($order_id) {
			$sql  = " SELECT * FROM " . $table_prefix . "orders ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$data = $db->Record;
				$name = $db->f("name");
				$order_status = $db->f("order_status");
				$first_name = $db->f("first_name");
				$last_name = $db->f("last_name");
				prepare_user_name($name, $first_name, $last_name);

				$t->set_var("name", $name);
				$t->set_var("user_name", $name);
				$t->set_var("first_name", $first_name);
				$t->set_var("last_name", $last_name);

				set_basket_tag($order_id, $mail_type, $newsletter_body);
				set_order_tag($order_id, $mail_type, $newsletter_body, "order_items");
				set_order_tag($order_id, $mail_type, $newsletter_body, "review_items");
			}
		} else if ($user_id) {
			$sql  = " SELECT * FROM " . $table_prefix . "users ";
			$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$data = $db->Record;
				$name = $db->f("name");
				$first_name = $db->f("first_name");
				$last_name = $db->f("last_name");
				prepare_user_name($name, $first_name, $last_name);

				$t->set_var("name", $name);
				$t->set_var("user_name", $name);
				$t->set_var("first_name", $first_name);
				$t->set_var("last_name", $last_name);
			}
		}

		$t->parse("mail_subject", false);
		$t->parse("mail_body", false);
		$mail_subject = $t->get_var("mail_subject");
		$mail_body = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("mail_body"));

		$email_sent = va_mail($user_email, $mail_subject, $mail_body, $email_headers);

		// mark email as sent 
		$sql  = " UPDATE " . $table_prefix . "newsletters_emails ";
		$sql .= " SET is_sent=1 ";
		$sql .= " WHERE user_email=" . $db->tosql($user_email, TEXT);
		$sql .= " AND newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
		$db->query($sql);

		// update order status
		if ($order_id && strlen($o_new_os) && $order_status != $o_new_os) {
			update_order_status($order_id, $o_new_os, true, "", $status_error);
		}

		// clear all additional template tags 
		foreach ($data as $param_name => $param_value) {
			$t->delete_var($param_name);
		}

		return $email_sent;
	}

	function get_newsletter_emails($newsletter_id, $emails_number = 0)
	{
		global $db, $table_prefix;

		$emails = array();
		$sql  = " SELECT * FROM " . $table_prefix . "newsletters_emails ";
		$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
		$sql .= " AND is_sent=0";
		if ($emails_number) {
			$db->RecordsPerPage = $emails_number;
			$db->PageNumber = 1;
		}
		$db->query($sql);
		while ($db->next_record()) {
			$email_id = $db->f("email_id");
			$emails[$email_id] = $db->Record;
		}

		return $emails;
	}

	function check_custom_recipients($custom_recipients, &$email_errors) 
	{
		global $custom_emails_check_error;

		$email_errors = "";
		$custom_recipients_array = explode ("\n", $custom_recipients);
		foreach ($custom_recipients_array as $i => $email) {
			$email = trim($email);
			$lentgth = strlen($email);
			if ($lentgth > 0) {
				if ($lentgth > 6 && preg_match(EMAIL_REGEXP,$email)) {
					$custom_recipients_array[$i] = $email;
				} else {
					$email_errors .= "\n<br />".$email;
					$custom_emails_check_error = true;
				}
			} else {
				unset($custom_recipients_array[$i]);
			}
		}
		if ($custom_emails_check_error) {
			$email_errors = INCORRECT_EMAILS_MSG.$email_errors;
		}
		$custom_recipients_array = array_unique($custom_recipients_array);
		return  $custom_recipients_array;
	}

