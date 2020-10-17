<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_newsletters_generate.php                           ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	@set_time_limit (900);
	chdir (dirname(__FILE__));
	include_once("./admin_config.php");
	include_once("../includes/common.php");
	include_once("../includes/record.php");
	include_once("../messages/".$language_code."/cart_messages.php");
	include_once("./admin_common.php");
	include_once("./admin_newsletter_functions.php");

	$error_message = ""; $success_message = "";
	check_admin_security("newsletter");

	// Database Initialize
	$dbi = new VA_SQL();
	$dbi->DBType      = $db_type;
	$dbi->DBDatabase  = $db_name;
	$dbi->DBHost      = $db_host;
	$dbi->DBPort      = $db_port;
	$dbi->DBUser      = $db_user;
	$dbi->DBPassword  = $db_password;
	$dbi->DBPersistent= $db_persistent;

	// newsletter record
	$r = new VA_Record($table_prefix . "newsletters");
	$r->add_where("newsletter_id", INTEGER);
	$r->add_textbox("template_newsletter_id", INTEGER);
	$r->change_property("template_newsletter_id", USE_IN_UPDATE, false);
	$r->add_textbox("campaign_id", INTEGER);
	$r->add_textbox("newsletter_type", INTEGER);
	$r->add_textbox("newsletter_date", DATETIME);
	$r->add_textbox("newsletter_subject", TEXT);
	$r->add_textbox("newsletter_body", TEXT);
	$r->add_textbox("mail_type", INTEGER);
	$r->add_textbox("mail_from", TEXT);
	$r->add_textbox("mail_reply_to", TEXT);
	$r->add_textbox("mail_return_path", TEXT);
	$r->add_textbox("mail_cc", TEXT);
	$r->add_textbox("mail_bcc", TEXT);
	$r->add_textbox("mailing_start", DATETIME);
	$r->add_textbox("mailing_end", DATETIME);
	/* don't need these field for new newsletter
	$r->add_textbox("emails_left", INTEGER);
	$r->add_textbox("emails_sent", INTEGER);
	$r->add_textbox("emails_opened", INTEGER);
	$r->add_textbox("emails_clicked", INTEGER);
	$r->add_textbox("emails_bounced", INTEGER);
	$r->add_textbox("emails_unsubscribed", INTEGER);
	//*/
	$r->add_textbox("is_active", INTEGER);
	$r->add_textbox("is_sent", INTEGER);
	$r->add_textbox("is_prepared", INTEGER);
	// filter data
	$r->add_textbox("users_recipients", TEXT);
	$r->add_textbox("admins_recipients", TEXT);
	$r->add_textbox("subscribed_recipients", TEXT);
	$r->add_textbox("orders_recipients", TEXT);
	$r->add_textbox("custom_recipients", TEXT);
	// stats data
	$r->add_textbox("added_by", INTEGER);
	$r->change_property("added_by", USE_IN_UPDATE, false);
	$r->add_textbox("added_date", DATETIME);
	$r->change_property("added_date", USE_IN_UPDATE, false);
	$r->add_textbox("edited_by", INTEGER);
	$r->change_property("edited_by", USE_IN_INSERT, false);
	$r->add_textbox("edited_date", DATETIME);
	$r->change_property("edited_date", USE_IN_INSERT, false);

	$current_ts = va_timestamp();

	// check and add new newsletters
	$template_sql  = " SELECT n.* FROM (" . $table_prefix . "newsletters n ";
	$template_sql .= " INNER JOIN " . $table_prefix . "newsletters_campaigns nc ON n.campaign_id=nc.campaign_id)";
	$template_sql .= " WHERE n.newsletter_type=2 ";
	$template_sql	.= " AND (n.template_next_date IS NULL OR ";
	$template_sql .= " n.template_next_date<=" . $db->tosql($current_ts, DATE) . ") ";
	$template_sql	.= " AND (n.template_start_date IS NULL OR ";
	$template_sql .= " n.template_start_date<=" . $db->tosql($current_ts, DATE) . ") ";
	$template_sql	.= " AND nc.is_active=1 AND n.is_active=1 ";
	$db->RecordsPerPage = 1;
	$db->PageNumber = 1;
	$db->query($template_sql);
	if ($db->next_record()) {
		$newsletters_generated = 0;
		do {
			$newsletters_generated++;
			$template_newsletter_id = $db->f("newsletter_id");
			$newsletter_date = $db->f("newsletter_date", DATETIME);

			$is_active = $db->f("is_active");
			$template_period = $db->f("template_period");
			$template_interval = $db->f("template_interval");
			$template_newsletters_limit = $db->f("template_newsletters_limit");
			$template_newsletters_added = $db->f("template_newsletters_added");
			$template_start_date = $db->f("template_start_date");
			$template_end_date = $db->f("template_end_date");
			$template_next_date = $db->f("template_next_date");
			$template_last_date = $db->f("template_last_date");
			$template_filter_period = $db->f("template_filter_period");
			$template_filter_interval = $db->f("template_filter_interval");

			$template_start_ts = 0; $template_next_ts = 0; $template_end_ts = 0;
			if (is_array($template_start_date)) {
				$template_start_ts = mktime (0, 0, 0, $template_start_date[MONTH], $template_start_date[DAY], $template_start_date[YEAR]);
			}
			if (is_array($template_end_date)) {
				$template_end_ts = mktime (0, 0, 0, $template_end_date[MONTH], $template_end_date[DAY], $template_end_date[YEAR]);
			}
			if (!is_array($template_next_date)) { $template_next_date = va_time(); } // use current date
			$template_next_ts = mktime (0, 0, 0, $template_next_date[MONTH], $template_next_date[DAY], $template_next_date[YEAR]);

			$template_newsletters_limit = $db->f("template_newsletters_limit");
			$template_newsletters_added = $db->f("template_newsletters_added");
			if (($template_end_ts > 0 && $current_ts > $template_end_ts) || ($template_newsletters_limit > 0 && $template_newsletters_added >= $template_newsletters_limit)) {
				// deactivate template
				$sql  = " UPDATE " . $table_prefix . "newsletters ";
				$sql .= " SET is_active=0 ";
				$sql .= " WHERE newsletter_id=" . $db->tosql($template_newsletter_id, INTEGER);
				$db->query($sql);
			} else {

				// get template data
				$r->set_value("newsletter_id", $template_newsletter_id);
				$r->get_db_values();
		  
				// update some template data to insert new template
				$r->set_value("newsletter_id", "");
				$r->set_value("template_newsletter_id", $template_newsletter_id);
				$r->set_value("newsletter_type", 1);
				$r->set_value("is_sent", 0);
				$r->set_value("is_prepared", 0);
				$r->set_value("added_by",   get_session("session_admin_id"));
				$r->set_value("added_date", va_time());
				$r->set_value("edited_by",   get_session("session_admin_id"));
				$r->set_value("edited_date", va_time());
				$r->insert_record();

				// get latest added newsletter_id
				$sql = " SELECT LAST_INSERT_ID() ";
				$new_newsletter_id = get_db_value($sql);

				// get and add filters for new newsletter
				$filters = array(); $template_filters = array();
				$sql  = " SELECT filter_parameter, filter_value ";
				$sql .= " FROM  " . $table_prefix . "newsletter_filters ";
				$sql .= " WHERE newsletter_id=" . $db->tosql($template_newsletter_id, INTEGER);
				$db->query($sql);
				while ($db->next_record()) {
					$filter_parameter = $db->f("filter_parameter");
					$filter_value = $db->f("filter_value");
					if ($filter_parameter == "o_sd" ||  $filter_parameter == "o_ed" || $filter_parameter == "os_sd" ||  $filter_parameter == "os_ed") {
						// update date values
						$date_value = va_time($filter_value);
						// calculate next payment date
						if ($template_filter_period == 1) {
							$update_value = mktime (0, 0, 0, $date_value[MONTH], $date_value[DAY] + $template_filter_interval, $date_value[YEAR]);
						} elseif ($template_filter_period == 2) {
							$update_value = mktime (0, 0, 0, $date_value[MONTH], $date_value[DAY] + ($template_filter_interval * 7), $date_value[YEAR]);
						} elseif ($template_filter_period == 3) {
							$update_value = mktime (0, 0, 0, $date_value[MONTH] + $template_filter_interval, $date_value[DAY], $date_value[YEAR]);
						} else {
							$update_value = mktime (0, 0, 0, $date_value[MONTH], $date_value[DAY], $date_value[YEAR] + $template_filter_interval);
						}
						$template_filters[] = array("name" => $filter_parameter, "value" => $update_value);
					}
					$filters[] = array("name" => $filter_parameter, "value" => $filter_value);
				}
				// add filters
				for ($f = 0; $f < sizeof($filters); $f++) {
					$parameter_name = $filters[$f]["name"];
					$parameter_value = $filters[$f]["value"];
					$sql  = "INSERT INTO " . $table_prefix . "newsletter_filters (newsletter_id, filter_parameter, filter_value) VALUES (";
					$sql .= $db->tosql($new_newsletter_id, INTEGER) . ", ";
					$sql .= $db->tosql($parameter_name, TEXT) . ", ";
					$sql .= $db->tosql($parameter_value, TEXT) . ") ";
					$db->query($sql);
				}
				// update filters
				for ($f = 0; $f < sizeof($template_filters); $f++) {
					$parameter_name = $template_filters[$f]["name"];
					$parameter_value = $template_filters[$f]["value"];
					$sql  = " UPDATE " . $table_prefix . "newsletter_filters ";
					$sql .= " SET filter_value=" . $db->tosql($parameter_value, TEXT);
					$sql .= " WHERE newsletter_id=".$db->tosql($template_newsletter_id, INTEGER);
					$sql .= " AND filter_parameter=".$db->tosql($parameter_name, TEXT);
					$db->query($sql);
				}

				// generate emails for new newsletter
				generate_emails($new_newsletter_id);
				
				// calculate next payment date
				if ($template_period == 1) {
					$template_next_ts = mktime (0, 0, 0, $template_next_date[MONTH], $template_next_date[DAY] + $template_interval, $template_next_date[YEAR]);
					$newsletter_date_ts = mktime ($newsletter_date[HOUR], $newsletter_date[MINUTE], $newsletter_date[SECOND], $newsletter_date[MONTH], $newsletter_date[DAY] + $template_interval, $newsletter_date[YEAR]);
				} elseif ($template_period == 2) {
					$template_next_ts = mktime (0, 0, 0, $template_next_date[MONTH], $template_next_date[DAY] + ($template_interval * 7), $template_next_date[YEAR]);
					$newsletter_date_ts = mktime ($newsletter_date[HOUR], $newsletter_date[MINUTE], $newsletter_date[SECOND], $newsletter_date[MONTH], $newsletter_date[DAY] + ($template_interval * 7), $newsletter_date[YEAR]);
				} elseif ($template_period == 3) {
					$template_next_ts = mktime (0, 0, 0, $template_next_date[MONTH] + $template_interval, $template_next_date[DAY], $template_next_date[YEAR]);
					$newsletter_date_ts = mktime ($newsletter_date[HOUR], $newsletter_date[MINUTE], $newsletter_date[SECOND], $newsletter_date[MONTH] + $template_interval, $newsletter_date[DAY], $newsletter_date[YEAR]);
				} else {
					$template_next_ts = mktime (0, 0, 0, $template_next_date[MONTH], $template_next_date[DAY], $template_next_date[YEAR] + $template_interval);
					$newsletter_date_ts = mktime ($newsletter_date[HOUR], $newsletter_date[MINUTE], $newsletter_date[SECOND], $newsletter_date[MONTH], $newsletter_date[DAY], $newsletter_date[YEAR] + $template_interval);
				}

				// update template data
				$template_newsletters_added++;
				if (($template_end_ts > 0 && $template_next_ts > $template_end_ts) || ($template_newsletters_limit > 0 && $template_newsletters_added >= $template_newsletters_limit)) {
					$is_active = 0;
				} else {
					$is_active = 1;
				}

				$sql  = " UPDATE " . $table_prefix . "newsletters ";
				$sql .= " SET newsletter_date=" . $db->tosql($newsletter_date_ts, DATE);
				$sql .= " , template_last_date=" . $db->tosql($current_ts, DATE);
				$sql .= " , template_newsletters_added=" . $db->tosql($template_newsletters_added, INTEGER);
				if ($is_active) {
					$sql .= " , template_next_date=" . $db->tosql($template_next_ts, DATE);
				} else {
					$sql .= " , is_active=0 ";
				}
				$sql .= " WHERE newsletter_id=" . $db->tosql($template_newsletter_id, INTEGER);
				$db->query($sql);
			}


			// check for next template
			$db->RecordsPerPage = 1;
			$db->PageNumber = 1;
			$db->query($template_sql);
		} while ($db->next_record());

		$success_message = $newsletters_generated. " newsletters generated";
	} else {
		$success_message = "There are no newsletters to generate";
	}

	// settings for errors notifications 
	$eol = get_eol();
	$recipients     = $settings["admin_email"];
	$email_headers  = "From: ". $settings["admin_email"] . $eol;
	$email_headers .= "Content-Type: text/plain";




	
?>