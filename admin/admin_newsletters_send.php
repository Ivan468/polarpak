<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_newsletters_send.php                               ***
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
	include_once("../includes/order_items.php");
	include_once("../includes/order_links.php");
	include_once("../includes/parameters.php");

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

	// generate new newsletters
	include("./admin_newsletters_generate.php");

	// get sites to set appropriate tags
	$sites = array();
	$sql = " SELECT * FROM ".$table_prefix."sites ";
	$db->query($sql);
	while ($db->next_record()) {
		$db_site_id = $db->f("site_id");
		$sites[$db_site_id] = $db->Record;
	}

	$current_ts = va_timestamp();
	$newsletters_sent = 0;

	// check and add new newsletters
	$newsletter_sql  = " SELECT n.* FROM " . $table_prefix . "newsletters n ";
	$newsletter_sql .= " INNER JOIN " . $table_prefix . "newsletters_campaigns nc ON n.campaign_id=nc.campaign_id ";
	$newsletter_sql .= " WHERE n.is_active=1 ";
	$newsletter_sql .= " AND n.newsletter_type=1 ";
	$newsletter_sql	.= " AND (n.newsletter_date IS NULL OR ";
	$newsletter_sql .= " n.newsletter_date<=" . $db->tosql($current_ts, DATETIME) . ") ";
	$newsletter_sql	.= " AND (n.is_sent IS NULL OR n.is_sent=0) ";
	// campaign filters
	$newsletter_sql	.= " AND nc.is_active=1 ";
	$newsletter_sql	.= " AND (nc.campaign_date_start IS NULL OR ";
	$newsletter_sql .= " nc.campaign_date_start<=" . $db->tosql($current_ts, DATETIME) . ") ";
	$newsletter_sql	.= " AND (nc.campaign_date_end IS NULL OR ";
	$newsletter_sql .= " nc.campaign_date_end>=" . $db->tosql($current_ts, DATETIME) . ") ";
	$db->RecordsPerPage = 1;
	$db->PageNumber = 1;
	$db->query($newsletter_sql);
	if ($db->next_record()) {
		do {
			$newsletters_sent++;
			$newsletter_id = $db->f("newsletter_id");
			$emails_sent = $db->f("emails_sent");
			$newsletter_data = $db->Record;
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

			if ($emails_sent < 1) {
				// update mailing_start field
				$sql  = " UPDATE " . $table_prefix . "newsletters ";
				$sql .= " SET mailing_start=" . $db->tosql(va_time(), DATETIME);
				$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
				$db->query($sql);
			}

			// check emails to send newsletter
			$emails = get_newsletter_emails($newsletter_id, 100);
			while (is_array($emails) && sizeof($emails) > 0) {

				foreach ($emails as $email_id => $email_data) {
					$email_sent = send_newsletter($email_data, $newsletter_data, $newsletter_filters);
				}

				$emails = get_newsletter_emails($newsletter_id, 100);
			}

			// count emails were sent
			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "newsletters_emails ";
			$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER) . " AND is_sent=1";
			$db->query($sql);
			$db->next_record();
			$emails_sent = $db->f(0);

			// update table with emails qty
			$sql  = " UPDATE " . $table_prefix . "newsletters ";
			$sql .= " SET emails_sent=" . $db->tosql($emails_sent, INTEGER);
			$sql .= " , emails_left=0 ";
			$sql .= " , is_sent=1 ";
			$sql .= " , mailing_end=" . $db->tosql(va_time(), DATETIME);
			$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
			$db->query($sql);
	

			// check for next newsletter
			$db->RecordsPerPage = 1;
			$db->PageNumber = 1;
			$db->query($newsletter_sql);
		} while ($db->next_record());

		$success_message = $newsletters_sent. " newsletters sent";
	} else {
		$success_message = "There are no newsletters to send";
	}

	// settings for errors notifications 
	$eol = get_eol();
	$recipients     = $settings["admin_email"];
	$email_headers  = "From: ". $settings["admin_email"] . $eol;
	$email_headers .= "Content-Type: text/plain";

