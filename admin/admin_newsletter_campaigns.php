<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_newsletter_campaigns.php                           ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
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
	$t->set_file("main", "admin_newsletter_campaigns.html");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_newsletter_href",  "admin_newsletter.php");
	$t->set_var("admin_newsletters_href", "admin_newsletters.php");
	$t->set_var("admin_newsletter_send_href", "admin_newsletter_send.php");
	$t->set_var("admin_newsletter_campaign_href", "admin_newsletter_campaign.php");
	$t->set_var("admin_newsletter_campaigns_href", "admin_newsletter_campaigns.php");
	$t->set_var("admin_newsletter_stats_href", "admin_newsletter_stats.php");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_newsletter_campaigns.php");
	//$s->set_default_sorting(3, "desc");
	$s->set_sorter(ID_MSG, "sorter_campaign_id", "1", "campaign_id", "", "", true);
	$s->set_sorter(NAME_MSG, "sorter_campaign_name", "2", "campaign_name");
	$s->set_sorter(START_DATE_MSG, "sorter_campaign_date_start", "3", "campaign_date_start");
	$s->set_sorter(END_DATE_MSG, "sorter_campaign_date_end", "4", "campaign_date_end");
	$s->set_sorter(IS_ACTIVE_MSG, "sorter_is_active", "5", "is_active");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_newsletter_campaigns.php");

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "newsletters_campaigns ");
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = " SELECT * FROM " . $table_prefix . "newsletters_campaigns ";
	$db->query($sql . $s->order_by);
	if ($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do {

			$campaign_id = $db->f("campaign_id");
			$campaign_name = get_translation($db->f("campaign_name"));
			$campaign_date_start = $db->f("campaign_date_start", DATETIME);
			$campaign_date_start = va_date($datetime_show_format, $campaign_date_start);
			$campaign_date_end = $db->f("campaign_date_end", DATETIME);
			if (is_array($campaign_date_end)) {
				$campaign_date_end = va_date($datetime_show_format, $campaign_date_end);
			}

			$is_active = ($db->f("is_active") == 1) ? "<b>".YES_MSG."</b>" : NO_MSG;
			$emails_sent = $db->f("emails_sent");

			$t->set_var("campaign_id", $campaign_id);
			$t->set_var("campaign_name", htmlspecialchars($campaign_name));
			$t->set_var("campaign_date_start", $campaign_date_start);
			$t->set_var("campaign_date_end", $campaign_date_end);
			$t->set_var("is_active", $is_active);

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
