<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_newsletters.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once("./admin_common.php");

	check_admin_security("newsletter");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_newsletters.html");

	$campaign_id = get_param("campaign_id");
	$operation = get_param("operation");
	$param_rnd = get_param("rnd");
	$session_rnd = get_session("session_rnd");
	$rnd = mt_rand();

	$newsletters_errors = ""; $newsletters_success = "";
	$error_message = ""; $success_message = "";
	if ($operation == "newsletters" && $param_rnd && $param_rnd == $session_rnd) {
		include_once("./admin_newsletters_generate.php");
	}
	set_session("session_rnd", $rnd);

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_newsletter_href",  "admin_newsletter.php");
	$t->set_var("admin_newsletters_href", "admin_newsletters.php");
	$t->set_var("admin_newsletter_send_href", "admin_newsletter_send.php");
	$t->set_var("admin_newsletter_emails_href", "admin_newsletter_emails.php");
	$t->set_var("campaign_id", htmlspecialchars($campaign_id));
	$t->set_var("rnd", htmlspecialchars($rnd));

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_newsletters.php");
	$s->set_default_sorting(3, "desc");
	$s->set_sorter(ID_MSG, "sorter_newsletter_id", "1", "newsletter_id");
	$s->set_sorter(EMAIL_SUBJECT_MSG, "sorter_newsletter_subject", "2", "newsletter_subject");
	$s->set_sorter(DATE_MSG, "sorter_newsletter_date", "3", "newsletter_date");
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_newsletters.php");

	$newsletter_types = array(
		"1" => NEWSLETTER_MSG,
		"2" => TEMPLATE_MSG,
	);

	// set up variables for navigator
	$sql = " SELECT COUNT(*) FROM " . $table_prefix . "newsletters ";
	$sql.= " WHERE campaign_id=" . $db->tosql($campaign_id, INTEGER);
	$db->query($sql);
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = " SELECT * FROM " . $table_prefix . "newsletters ";
	$sql .= " WHERE campaign_id=" . $db->tosql($campaign_id, INTEGER);
	$db->query($sql . $s->order_by);
	if ($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do {
			$newsletter_type = $db->f("newsletter_type");
			$newsletter_subject = get_translation($db->f("newsletter_subject"));
			$newsletter_date = $db->f("newsletter_date", DATETIME);
			$newsletter_date = va_date($datetime_show_format, $newsletter_date);
			$is_sent = $db->f("is_sent");
			$is_active = $db->f("is_active");
			$emails_total = $db->f("emails_total");
			$emails_sent = $db->f("emails_sent");
			$newsletter_type = $db->f("newsletter_type");
			if ($newsletter_type == 2) {
				$send_preview_title = PREVIEW_BUTTON;
				if ($is_active) {
					$status = "<font color=\"blue\">" . ACTIVE_MSG . "</font>";
				} else {
					$status = "<font color=\"silver\">" . INACTIVE_MSG . "</font>";
				}

			} else {
				$send_preview_title = PREVIEW_BUTTON." / ".SEND_BUTTON;
				if ($is_sent) {
					$status = SENT_MSG;
				} elseif (!$is_active) {
					$status = INACTIVE_MSG;
				} elseif ($emails_sent > 0) {
					$status = SENDING_NOT_FINISHED_MSG;
				} else {
					$status = READY_FOR_SENDING_MSG;
				}
			}
			$newsletter_type_desc = isset($newsletter_types[$newsletter_type]) ? $newsletter_types[$newsletter_type] : $newsletter_type;
			$t->set_var("newsletter_id", $db->f("newsletter_id"));
			$t->set_var("newsletter_subject", $newsletter_subject);
			$t->set_var("newsletter_date", $newsletter_date);
			$t->set_var("emails_total", $emails_total);
			$t->set_var("send_preview_title", htmlspecialchars($send_preview_title));

			if ($newsletter_type == 2) {
				$t->parse("emails_na", false);
				$t->set_var("emails_link", "");
			} else {
				$t->set_var("emails_na", "");
				$t->parse("emails_link", false);
			}
			$t->set_var("newsletter_status", $status);
			$t->set_var("newsletter_type_desc", $newsletter_type_desc);

			$t->parse("records", true);
		} while ($db->next_record());
	}
	else
	{
		$t->set_var("sorters", "");
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}


	if ($error_message) {
		$t->set_var("errors_list", $error_message);
		$t->sparse("errors", false);
	} 

	if ($success_message) {
		$t->set_var("success_message", $success_message);
		$t->sparse("success", false);
	} 



	$t->pparse("main");

?>