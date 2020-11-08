<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_newsletter_stats.php                               ***
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

	$dbs = new VA_SQL();
	$dbs->DBType       = $db->DBType;
	$dbs->DBDatabase   = $db->DBDatabase;
	$dbs->DBUser       = $db->DBUser;
	$dbs->DBPassword   = $db->DBPassword;
	$dbs->DBHost       = $db->DBHost;
	$dbs->DBPort       = $db->DBPort;
	$dbs->DBPersistent = $db->DBPersistent;

	$campaign_id = get_param("campaign_id");

	// get campaign name
	$sql  = " SELECT nc.campaign_name FROM " . $table_prefix . "newsletters_campaigns nc ";
	$sql .= " WHERE nc.campaign_id=" . $db->tosql($campaign_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$campaign_name = $db->f("campaign_name");
	} else {
		header ("Location: admin_newsletter_campaigns.php");
		exit;
	}


	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_newsletter_stats.html");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_newsletter_href",  "admin_newsletter.php");
	$t->set_var("admin_newsletters_href", "admin_newsletters.php");
	$t->set_var("admin_newsletter_send_href", "admin_newsletter_send.php");
	$t->set_var("admin_newsletter_campaign_href", "admin_newsletter_campaign.php");
	$t->set_var("admin_newsletter_campaigns_href", "admin_newsletter_campaigns.php");
	$t->set_var("admin_newsletters_stats_href", "admin_newsletters_stats.php");
	$t->set_var("campaign_name", htmlspecialchars($campaign_name));
	$t->set_var("currency_left", htmlspecialchars($currency["left"]));
	$t->set_var("currency_right", htmlspecialchars($currency["right"]));



	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_newsletter_stats.php");
	$s->set_default_sorting(1, "desc");
	$s->set_parameters(false, true, true, false);
	$s->set_sorter(ID_MSG, "sorter_newsletter_id", "1", "newsletter_id");
	$s->set_sorter(EMAIL_SUBJECT_MSG, "sorter_newsletter_subject", "2", "newsletter_subject");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_newsletter_stats.php");

	// set up variables for navigator
	$sql = " SELECT COUNT(*) FROM " . $table_prefix . "newsletters ";
	$sql.= " WHERE campaign_id=" . $db->tosql($campaign_id, INTEGER);
	$sql.= " AND (newsletter_type=1 OR newsletter_type IS NULL) ";
	$db->query($sql);
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = " SELECT n.newsletter_id, n.newsletter_subject, ";
	$sql .= " COUNT(*) AS emails, SUM(ne.is_sent) AS sent, SUM(ne.is_opened) AS opened, ";
	$sql .= " SUM(ne.is_clicked) AS clicked, SUM(ne.is_bounced) AS bounced, SUM(ne.is_unsubscribed) AS unsubscribed ";
	$sql .= " FROM " . $table_prefix . "newsletters n ";
	$sql .= " LEFT JOIN " . $table_prefix . "newsletters_emails ne ON n.newsletter_id=ne.newsletter_id ";
	$sql .= " WHERE campaign_id=" . $db->tosql($campaign_id, INTEGER);
	$sql .= " AND (newsletter_type=1 OR newsletter_type IS NULL) ";
	$sql .= " GROUP BY n.newsletter_id, n.newsletter_subject ";
	$db->query($sql . $s->order_by);
	if ($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do {

			$newsletter_id = $db->f("newsletter_id");
			$newsletter_subject = get_translation($db->f("newsletter_subject"));
			$emails = $db->f("emails");
			$sent = $db->f("sent");
			$opened = $db->f("opened");
			$clicked = $db->f("clicked");
			$bounced = $db->f("bounced");
			$unsubscribed = $db->f("unsubscribed");

			// check order data
			$sql  = " SELECT COUNT(*) AS orders_number, SUM(order_total) AS orders_total ";
			$sql .= " FROM " . $table_prefix . "orders ";
			$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
			$dbs->query($sql);
			$dbs->next_record($sql);
			$orders_number = intval($dbs->f("orders_number"));
			$orders_total = doubleval($dbs->f("orders_total"));

			$t->set_var("newsletter_id", $newsletter_id);
			$t->set_var("newsletter_subject", $newsletter_subject);
			$t->set_var("emails", intval($emails));
			$t->set_var("sent", intval($sent));
			$t->set_var("opened", intval($opened));
			$t->set_var("clicked", intval($clicked));
			$t->set_var("bounced", intval($bounced));
			$t->set_var("unsubscribed", intval($unsubscribed));
			$t->set_var("orders_number", intval($orders_number));
			$t->set_var("orders_total", currency_format($orders_total));

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

	$t->pparse("main");

?>
