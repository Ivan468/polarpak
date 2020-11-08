<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_newsletter_send.php                                ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once("../includes/common.php");
	include_once("../includes/record.php");
	include_once("../messages/".$language_code."/cart_messages.php");
	include_once("./admin_common.php");
	include_once("./admin_newsletter_functions.php");
	include_once("../includes/order_items.php");
	include_once("../includes/order_links.php");
	include_once("../includes/parameters.php");
	
	check_admin_security("newsletter");
	
	$errors = "";
	$eol = get_eol();
	$operation = get_param("operation");
	$newsletter_id = get_param("newsletter_id");
	$emails_qty = get_param("emails_qty");
	$emails_delay = get_param("emails_delay");

	// get sites to set appropriate tags
	$sites = array();
	$sql = " SELECT * FROM ".$table_prefix."sites ";
	$db->query($sql);
	while ($db->next_record()) {
		$db_site_id = $db->f("site_id");
		$sites[$db_site_id] = $db->Record;
	}
	                       
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_newsletter_send.html");
	$t->set_var("admin_newsletter_send_href", "admin_newsletter_send.php");
	$t->set_var("newsletter_id", $newsletter_id);
	
	$t->pparse("newsletter_header");
	
	$sql  = " SELECT * FROM " . $table_prefix . "newsletters ";
	$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$newsletter_data = $db->Record;
		$newsletter_type = $db->f("newsletter_type");
		$mail_type = $db->f("mail_type");
		$mail_from = $db->f("mail_from");
		$mail_reply_to = $db->f("mail_reply_to");
		$mail_return_path = $db->f("mail_return_path");
		$newsletter_subject = $db->f("newsletter_subject");
		$newsletter_body = $db->f("newsletter_body");
		$is_active = $db->f("is_active");
		$is_sent = $db->f("is_sent");
		$is_prepared = $db->f("is_prepared");
		$emails_left = $db->f("emails_left");
		$emails_sent = $db->f("emails_sent");
		$users_recipients = $db->f("users_recipients");
		$admins_recipients = $db->f("admins_recipients");
		$orders_recipients = $db->f("orders_recipients");
		$subscribed_recipients = $db->f("subscribed_recipients");
	} else {
		$errors = NEWSLETTER_WASNT_FOUND_MSG;
	}


	// get filters
	$newsletter_filters = array();
	$sql  = " SELECT filter_parameter, filter_value ";
	$sql .= " FROM " . $table_prefix . "newsletter_filters ";
	$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
	$db->query($sql);
	while ($db->next_record()) {
		$filter_parameter = $db->f("filter_parameter");
		$filter_value = $db->f("filter_value");
		$newsletter_filters[$filter_parameter] = $filter_value;
	}

	
	// for regular newsletter check necessary parameters
	if ($newsletter_type != 2) {
		if ($is_sent) {
			$errors = NEWSLETTER_SENT_MSG;
		} elseif (!$is_active) {
			$errors = NEWSLETTER_ISNT_ACTIVE_MSG;
		}
	
		if (!$is_prepared && !$is_sent) {
			generate_emails($newsletter_id);
	
			// count emails
			$sql  = " SELECT emails_left FROM " . $table_prefix . "newsletters ";
			$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$emails_left = $db->f("emails_left");
			}
		}
	
		if ($emails_left < 1 && !strlen($errors)) {
			$errors = NO_EMAILS_FOR_NEWSLETTER_MSG;
		}
	}
	
	if(strlen($errors))	{
		$t->set_var("errors_list", $errors);
		$t->pparse("errors", false);
	}
	
	if ($operation == "send" && !strlen($errors)) {
		$t->pparse("newsletter_sending", false);
		flush();
	
		if ($emails_sent < 1) {
			// update mailing_start field
			$sql  = " UPDATE " . $table_prefix . "newsletters ";
			$sql .= " SET mailing_start=" . $db->tosql(va_time(), DATETIME);
			$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
			$db->query($sql);
		}
		$emails = array();
		$sql  = " SELECT * FROM " . $table_prefix . "newsletters_emails ";
		$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
		$sql .= " AND (is_sent=0 OR is_sent IS NULL) ";
		$sql .= " ORDER BY email_id ";
		$db->RecordsPerPage = $emails_qty;
		$db->PageNumber = 1;
		$db->query($sql);
		while ($db->next_record()) {
			$email_id = $db->f("email_id");
			$emails[$email_id] = $db->Record;
		}
	
		echo "&nbsp;&nbsp;&nbsp;";
		$i = 0;
		$cycle_sent = 0;
		$total_errors = 0;
		foreach ($emails as $email_id => $email_data) {
			$i++;
			$email_sent = send_newsletter($email_data, $newsletter_data, $newsletter_filters);

			// increment table by one
			if ($email_sent) {
				$cycle_sent++;
			} else {
				$total_errors++;
			}
			echo " . ";
			flush();
			if($emails_delay == 1000000) {
				sleep(1);
			} elseif ($emails_delay > 0) {
				usleep($emails_delay);
			}
			if ($i > 0 && $i % 50 == 0) {
				echo EMAILS_SENT_MSG . $i . "<br>";
				echo "&nbsp;&nbsp;&nbsp;";
			}
		}

		if ($i % 50 != 0 && $i > 0) {
			echo $i . EMAILS_SENT_MSG;
		}
	
		// update newsletter status
		// count remaining emails
		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "newsletters_emails ";
		$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
		$sql .= " AND (is_sent=0 OR is_sent IS NULL) ";
		$db->query($sql);
		$db->next_record();
		$emails_left = $db->f(0);

		// count remaining emails
		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "newsletters_emails ";
		$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER) . " AND is_sent=1";
		$sql .= " AND is_sent=1 ";
		$db->query($sql);
		$db->next_record();
		$emails_sent = $db->f(0);

		// update table with emails qty
		$sql  = " UPDATE " . $table_prefix . "newsletters ";
		$sql .= " SET emails_left=" . $db->tosql($emails_left, INTEGER);
		$sql .= " , emails_sent=" . $db->tosql($emails_sent, INTEGER);
		if($emails_left < 1) {
			$sql .= " , is_sent=1 ";
		}
		$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
		$db->query($sql);
	
		if ($emails_left < 1) {
			// update mailing_end field
			$sql  = " UPDATE " . $table_prefix . "newsletters ";
			$sql .= " SET mailing_end=" . $db->tosql(va_time(), DATETIME);
			$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
			$db->query($sql);
		}
	
		$t->set_var("emails_sent", $cycle_sent);
		$t->set_var("total_emails_sent", $emails_sent);
		$t->set_var("emails_left", $emails_left);
		if ($total_errors) {
			$t->set_var("total_errors", $total_errors);
			$t->parse("emails_errors");
		}
	
		$t->pparse("newsletter_stats", false);
	
		if ($emails_left > 0) {
			$newsletter_href  = "admin_newsletter_send.php?operation=send";
			$newsletter_href .= "&newsletter_id=" . urlencode($newsletter_id);
			$newsletter_href .= "&emails_qty=" . urlencode($emails_qty);
			$newsletter_href .= "&emails_delay=" . urlencode($emails_delay);
			$t->set_var("admin_newsletter_send_href", $newsletter_href);
			$t->pparse("newsletter_refresh", false);
		} else {
	
		}
	} else {
		$t->set_var("mail_from", $mail_from);
		$t->set_var("mail_reply_to", $mail_reply_to);
		$t->set_var("mail_return_path", $mail_return_path);
		$t->set_var("newsletter_subject", $newsletter_subject);
		if (!$mail_type) {
			$newsletter_body = nl2br(htmlspecialchars($newsletter_body));
		}
		$t->set_var("newsletter_body", $newsletter_body);
		$t->pparse("newsletter_preview", false);
	
		if ($newsletter_type != 2) {
			$t->set_var("emails_left", intval($emails_left));
			$t->set_var("emails_sent", intval($emails_sent));
			$t->pparse("newsletter_form", false);
		}
	}
	
	$t->pparse("newsletter_footer");
	
