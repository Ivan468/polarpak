<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_newsletter_campaign.php                            ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "messages/" . $language_code . "/download_messages.php");
	include_once("./admin_common.php");
	
	check_admin_security("newsletter");
	
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_newsletter_campaign.html");
	
	include_once("./admin_header.php");
	include_once("./admin_footer.php");
	
	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_newsletters_href", "admin_newsletters.php");
	$t->set_var("admin_newsletter_href",  "admin_newsletter.php");
	$t->set_var("admin_newsletter_campaign_href",  "admin_newsletter_campaign.php");
	$t->set_var("admin_newsletter_campaigns_href",  "admin_newsletter_campaigns.php");

	$t->set_var("datetime_format", join("", $datetime_edit_format));
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", EMAIL_CAMPAIGN_MSG, CONFIRM_DELETE_MSG));
	$mail_types =
		array(
			array(1, HTML_MSG), array(0, PLAIN_TEXT_MSG)
		);
	
	$r = new VA_Record($table_prefix . "newsletters_campaigns");
	$r->return_page = "admin_newsletter_campaigns.php";
	
	$r->add_where("campaign_id", INTEGER);
	$r->add_checkbox("is_active", INTEGER);
	$r->change_property("is_active", DEFAULT_VALUE, 1);
	$r->add_textbox("campaign_name", TEXT, NAME_MSG);
	$r->change_property("campaign_name", REQUIRED, true);

	$r->add_textbox("campaign_date_start", DATETIME, START_DATE_MSG);
	$r->change_property("campaign_date_start", VALUE_MASK, $datetime_edit_format);
	$r->change_property("campaign_date_start", REQUIRED, true);
	$r->change_property("campaign_date_start", DEFAULT_VALUE, va_time());
	$r->add_textbox("campaign_date_end", DATETIME, END_DATE_MSG);
	$r->change_property("campaign_date_end", VALUE_MASK, $datetime_edit_format);

	// fields for statistics: emails_sent, emails_opened, emails_clicked, emails_bounced, emails_unsubscribed

	// admin fields 
	$r->add_textbox("admin_id_added_by", INTEGER);
	$r->change_property("admin_id_added_by", USE_IN_UPDATE, false);
	$r->add_textbox("admin_id_modified_by", INTEGER);
	$r->add_textbox("date_added", DATETIME);
	$r->change_property("date_added", USE_IN_UPDATE, false);
	$r->add_textbox("date_modified", DATETIME);

	$r->events[BEFORE_INSERT] = "set_admin_data";
	$r->events[BEFORE_UPDATE] = "set_admin_data";

	$r->process();

	$t->pparse("main");
	
	function set_admin_data() {
		global $db, $table_prefix, $r;

		$r->set_value("admin_id_added_by", get_session("session_admin_id"));
		$r->set_value("date_added", va_time());
		$r->set_value("admin_id_modified_by", get_session("session_admin_id"));
		$r->set_value("date_modified", va_time());
	}
?>