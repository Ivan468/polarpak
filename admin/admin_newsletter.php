<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_newsletter.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "messages/" . $language_code . "/download_messages.php");
	include_once($root_folder_path . "includes/tabs_functions.php");
	include_once("./admin_common.php");
	include_once("./admin_newsletter_functions.php");
	
	check_admin_security("newsletter");
	
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_newsletter.html");
	$t->set_var("date_edit_format", join("", $date_edit_format));
	
	$site_url = get_setting_value($settings, "site_url", "");
	$campaign_id = get_param("campaign_id");
	$newsletter_id = get_param("newsletter_id");
	// get campaign name
	$sql  = " SELECT nc.campaign_name FROM " . $table_prefix . "newsletters_campaigns nc ";
	if ($campaign_id) {
		$sql .= " WHERE nc.campaign_id=" . $db->tosql($campaign_id, INTEGER);
	} else {
		$sql .= " INNER JOIN  " . $table_prefix . "newsletters n ON n.campaign_id=nc.campaign_id ";
		$sql .= " WHERE n.newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
	}
	$db->query($sql);
	if ($db->next_record()) {
		$campaign_name = $db->f("campaign_name");
	} else {
		header ("Location: admin_newsletter_campaigns.php");
		exit;
	}
	// newsletters (template_newsletter_id) // TODO: apply field
	// remove -- newsletters ADD COLUMN template_active TINYINT DEFAULT '0'", // TODO: remove field


	include_once("./admin_header.php");
	include_once("./admin_footer.php");
	
	$html_editor = get_setting_value($settings, "html_editor_email", get_setting_value($settings, "html_editor", 1));
	$t->set_var("html_editor", $html_editor);		
	$site_url_path = $settings["site_url"] ? $settings["site_url"] : "../";
	$t->set_var("css_file", $site_url_path . "styles/" . $settings["style_name"] . ".css");
	
	$editors_list = 'nl';
	add_html_editors($editors_list, $html_editor);
	
	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_newsletters_href", "admin_newsletter.php");
	$t->set_var("admin_newsletter_href",  "admin_newsletter.php");
	$t->set_var("datetime_format", join("", $datetime_edit_format));
	$t->set_var("campaign_name", htmlspecialchars($campaign_name));

	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", NEWSLETTER_MSG, CONFIRM_DELETE_MSG));
	$newsletter_types =
		array(
			array(1, NEWSLETTER_MSG), array(2, TEMPLATE_MSG)
		);
	$mail_types =
		array(
			array(1, HTML_MSG), array(0, PLAIN_TEXT_MSG)
		);

	$time_periods =
		array(
			array("", ""), array(1, DAY_MSG), array(2, WEEK_MSG), array(3, MONTH_MSG), array(4, YEAR_MSG)
		);
	
	$r = new VA_Record($table_prefix . "newsletters");
	$return_page = "admin_newsletters.php?campaign_id=".$campaign_id;
	
	$r->add_where("newsletter_id", INTEGER);
	
	$r->add_checkbox("is_active", INTEGER);
	$r->add_textbox("campaign_id", INTEGER);
	$r->change_property("campaign_id", USE_IN_UPDATE, false);
	$r->add_radio("newsletter_type", INTEGER, $newsletter_types, TYPE_MSG);

	$r->add_textbox("newsletter_date", DATETIME, DATE_MSG);
	$r->change_property("newsletter_date", VALUE_MASK, $datetime_edit_format);
	$r->change_property("newsletter_date", REQUIRED, true);
	$r->add_radio("mail_type", INTEGER, $mail_types, EMAIL_MESSAGE_TYPE_MSG);
	$r->add_textbox("mail_from", TEXT, EMAIL_FROM_MSG);
	$r->add_textbox("mail_reply_to", TEXT, EMAIL_REPLY_TO_MSG);
	$r->add_textbox("mail_return_path", TEXT, EMAIL_RETURN_PATH_MSG);
	$r->add_textbox("mail_cc", TEXT);
	$r->add_textbox("mail_bcc", TEXT);
	
	$r->add_textbox("newsletter_subject", TEXT, EMAIL_SUBJECT_MSG);
	$r->change_property("newsletter_subject", REQUIRED, true);
	$r->add_textbox("newsletter_body", TEXT, EMAIL_SUBJECT_MSG);

	$r->add_checkbox("add_unsubscribe_link", INTEGER);
	$r->change_property("add_unsubscribe_link", USE_IN_INSERT, false);
	$r->change_property("add_unsubscribe_link", USE_IN_UPDATE, false);
	$r->change_property("add_unsubscribe_link", USE_IN_SELECT, false);
	
	$r->add_textbox("added_by", INTEGER);
	$r->change_property("added_by", USE_IN_UPDATE, false);
	$r->add_textbox("added_date", DATETIME);
	$r->change_property("added_date", USE_IN_UPDATE, false);
	
	$r->add_textbox("edited_by", INTEGER);
	$r->add_textbox("edited_date", DATETIME);
	
	$r->add_textbox("emails_left", INTEGER);
	$r->add_textbox("emails_sent", INTEGER);
	$r->add_textbox("is_sent", INTEGER);
	//$r->change_property("is_sent", USE_IN_UPDATE, false);
	$r->add_textbox("is_prepared", INTEGER);
	
	$r->add_checkbox("subscribed_recipients", TEXT);
	$r->add_checkbox("users_recipients", TEXT);
	$r->add_checkbox("orders_recipients", TEXT);
	$r->add_checkbox("admins_recipients", TEXT);
	
	$r->add_textbox("custom_recipients", TEXT, EMAIL_SUBJECT_MSG);
	$r->add_textbox("custom_recipients_file", TEXT, EMAIL_SUBJECT_MSG);
	$r->change_property("custom_recipients_file", USE_IN_SELECT, false);
	$r->change_property("custom_recipients_file", USE_IN_UPDATE, false);
	$r->change_property("custom_recipients_file", USE_IN_INSERT, false);

	// template settings
	//$r->add_checkbox("template_active", INTEGER);
	$r->add_select("template_period", INTEGER, $time_periods, TEMPLATE_INTERVAL_MSG);
	$r->add_textbox("template_interval", INTEGER, TEMPLATE_INTERVAL_MSG);
	$r->add_textbox("template_newsletters_limit", INTEGER, NEWSLETTERS_LIMIT_MSG);
	$r->add_textbox("template_newsletters_added", INTEGER);
	$r->add_textbox("template_start_date", DATETIME, RECURRING_START_DATE_MSG);
	$r->change_property("template_start_date", VALUE_MASK, $date_edit_format);
	$r->add_textbox("template_end_date", DATETIME, RECURRING_END_DATE_MSG);
	$r->change_property("template_end_date", VALUE_MASK, $date_edit_format);
	$r->add_textbox("template_next_date", DATETIME, RECURRING_END_DATE_MSG);
	$r->change_property("template_next_date", VALUE_MASK, $date_edit_format);
	$r->add_textbox("template_last_date", DATETIME, RECURRING_END_DATE_MSG);
	$r->change_property("template_last_date", VALUE_MASK, $date_edit_format);

	$r->add_select("template_filter_period", INTEGER, $time_periods, TEMPLATE_INTERVAL_MSG);
	$r->add_textbox("template_filter_interval", INTEGER, TEMPLATE_INTERVAL_MSG);

	//newsletters_emails ADD COLUMN email_type TINYINT DEFAULT '0'",
	//newsletters_emails ADD COLUMN order_id INT(11) default '0' ",
	//newsletters_emails ADD COLUMN admin_id INT(11) default '0' ",

	// prepare list values for filters
	$sql = "SELECT site_id, site_name FROM " . $table_prefix . "sites ORDER BY site_id ";
	$sites_list = get_db_values($sql, "");

	// filters records
	$fr = new VA_Record($table_prefix . "newsletter_filters", "newsletter_filters");

	// sites filter for subscribed users
	$fr->add_checkboxlist("s_sites", TEXT, $sites_list);

	// users filters
	$sql = " SELECT type_id, type_name FROM " . $table_prefix . "user_types ";
	$user_groups = get_db_values($sql, "");
	$fr->add_checkboxlist("u_groups", TEXT, $user_groups);
	$fr->add_checkboxlist("u_sites", TEXT, $sites_list);

	// order filters
	$sql = "SELECT status_id, status_name FROM " . $table_prefix . "order_statuses WHERE is_active=1 ORDER BY status_order, status_id";
	$order_statuses = get_db_values($sql, "");
	$order_statuses_select = array_merge(array(array("", "")), $order_statuses);
	//$order_statuses_select = get_db_values($sql, array(array("", "")));

	$countries = get_db_values("SELECT country_id, country_name FROM " . $table_prefix . "countries ORDER BY country_order, country_name ", array(array("", "")));
	$states = get_db_values("SELECT state_id, state_name FROM " . $table_prefix . "states ORDER BY state_name ", array(array("", "")));
	$cc_default_types = array(array("", ""), array("blank", WITHOUT_CARD_TYPE_MSG));
	$credit_card_types = get_db_values("SELECT credit_card_id, credit_card_name FROM " . $table_prefix . "credit_cards ORDER BY credit_card_name", $cc_default_types);
	$export_options = array(array("", ALL_MSG), array("1", EXPORTED_MSG), array("0", NOT_EXPORTED_MSG));
	$paid_options = array(array("", ALL_MSG), array("1", PAID_MSG), array("0", NOT_PAID_MSG));
	if ($sitelist) {
		$sites = get_db_values("SELECT site_id, site_name FROM " . $table_prefix . "sites ORDER BY site_id ", array(array("", "")));
	}
	$fr->add_textbox("o_sd", DATETIME, ORDER_DATE_MSG." (".FROM_DATE_MSG.")");
	$fr->change_property("o_sd", VALUE_MASK, $date_edit_format);
	$fr->change_property("o_sd", TRIM, true);
	$fr->add_textbox("o_ed", DATETIME, ORDER_DATE_MSG." (".END_DATE_MSG.")");
	$fr->change_property("o_ed", VALUE_MASK, $date_edit_format);
	$fr->change_property("o_ed", TRIM, true);		
	$fr->add_textbox("os_sd", DATETIME, STATUS_SHIPPED_MSG." (".FROM_DATE_MSG.")");
	$fr->change_property("os_sd", VALUE_MASK, $date_edit_format);
	$fr->change_property("os_sd", TRIM, true);
	$fr->add_textbox("os_ed", DATETIME, STATUS_SHIPPED_MSG." (".END_DATE_MSG.")");
	$fr->change_property("os_ed", VALUE_MASK, $date_edit_format);
	$fr->change_property("os_ed", TRIM, true);		

	$fr->add_checkboxlist("o_os", TEXT, $order_statuses);
	$fr->add_checkboxlist("o_sites", TEXT, $sites_list);
	$fr->add_select("o_ci", TEXT, $countries);
	$fr->add_select("o_si", TEXT, $states);

	$fr->add_textbox("o_total_min", FLOAT, ADMIN_ORDER_TOTAL_MSG . "(".MINIMUM_MSG.")");
	$fr->add_textbox("o_total_max", FLOAT, ADMIN_ORDER_TOTAL_MSG . "(".MAXIMUM_MSG.")");
	$fr->add_textbox("o_grand_min", FLOAT, "Grand Total". "(".MINIMUM_MSG.")");
	$fr->add_textbox("o_grand_max", FLOAT, "Grand Total". "(".MAXIMUM_MSG.")");
	$fr->add_textbox("o_coupon_code", TEXT);
	$fr->add_checkboxlist("o_os", TEXT, $order_statuses);
	$fr->add_select("o_new_os", TEXT, $order_statuses_select);

	$user_statuses = array(array("", ALL_MSG), array("new", "New"), array("return", "Returning"));

	// administrator filters
	$sql = " SELECT privilege_id, privilege_name FROM " . $table_prefix . "admin_privileges ";
	$admin_groups = get_db_values($sql, "");
	$fr->add_checkboxlist("a_groups", TEXT, $admin_groups);

	$r->get_form_parameters();
	$fr->get_form_parameters();
	if ($r->get_value("newsletter_type") == 2) {
		$r->change_property("template_period", REQUIRED, true);
		$r->change_property("template_interval", REQUIRED, true);
	}
	$custom_emails_check_error = false;
	$operation = get_param("operation");
	$newsletter_id = get_param("newsletter_id");
	// check if we need add some tracking code
	$add_tracking_code = true;
	$add_unsubscribe_link = get_param("add_unsubscribe_link");
	$newsletter_body = $r->get_value("newsletter_body");
	if ($add_tracking_code) {
		if (!preg_match("/email_open\.php/", $newsletter_body)) {
			$image_open = "<img src=\"".$site_url."email_open.php?eid={eid}\" width=\"1\" height=\"1\" border=\"0\">";
			if(strpos ($newsletter_body ,"</body>")) {
				$newsletter_body = str_replace("</body>", $image_open. "</body>", $newsletter_body);
			} else if (strpos ($newsletter_body,"</html>")) {
				$newsletter_body = str_replace("</html>", $image_open. "</html>", $newsletter_body);
			} else {
				$newsletter_body .= $image_open;
			}
			$r->set_value("newsletter_body", $newsletter_body);
		}
	}
	if ($add_unsubscribe_link) {
		if (!preg_match("/unsubscribe\.php/", $newsletter_body)) {
			$unsubscribe_link = "<a href=\"".$site_url."unsubscribe.php?operation=unsubscribe&eid={eid}\">".UNSUBSCRIBE_LINK_MSG."</a>";
			if(strpos ($newsletter_body ,"</body>")) {
				$newsletter_body = str_replace("</body>", $unsubscribe_link."</body>", $newsletter_body);
			} else if (strpos ($newsletter_body,"</html>")) {
				$newsletter_body = str_replace("</html>", $unsubscribe_link."</html>", $newsletter_body);
			} else {
				$newsletter_body .= $unsubscribe_link;
			}
			$r->set_value("newsletter_body", $newsletter_body);
		}
	}


	if (strlen($operation))
	{
		if ($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		}
		elseif ($operation == "delete" && $newsletter_id)
		{
			$r->delete_record();
			delete_filters();

			$sql  = " DELETE FROM " . $table_prefix . "newsletters_emails ";
			$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
			$db->query($sql);

			header("Location: " . $return_page);
			exit;
		}
		elseif ($operation == "load")
		{
			$errors = "";
			$custom_recipients = "";
			if (isset($_FILES)) {
				$tmp_name = $_FILES["custom_recipients_file"]["tmp_name"];
				$filesize = $_FILES["custom_recipients_file"]["size"];
				$upload_error = isset($_FILES["custom_recipients_file"]["error"]) ? $_FILES["custom_recipients_file"]["error"] : "";
			} else {
				$tmp_name = $HTTP_POST_FILES["custom_recipients_file"]["tmp_name"];
				$filesize = $HTTP_POST_FILES["custom_recipients_file"]["size"];
				$upload_error = isset($HTTP_POST_FILES["custom_recipients_file"]["error"]) ? $HTTP_POST_FILES["custom_recipients_file"]["error"] : "";
			}

			if ($upload_error == 0) {
				$handle = fopen($tmp_name, "r");
	
				while (!feof($handle)) {
					$buffer = fgets($handle, 256);
					$custom_recipients .= $buffer ;
				}
				fclose($handle);
			}
			$custom_recipients_array = check_custom_recipients($custom_recipients, $email_errors);
			$custom_recipients = implode("\n", $custom_recipients_array);
			$r->set_value("custom_recipients", $custom_recipients);
		  $r->errors = $email_errors;
		} else {
			// save operation otherwise
			$r->validate();
			$fr->validate();
		  $r->errors .= $fr->errors;

			$custom_recipients = $r->get_value("custom_recipients");
			$custom_recipients_array = check_custom_recipients($custom_recipients, $email_errors);
		  $r->errors .= $email_errors;

			if (!strlen($r->errors)) {
				if (strlen($newsletter_id)) {
					// check newsletters number
					$sql  = " SELECT COUNT(*) FROM " . $table_prefix ."newsletters ";
					$sql .= " WHERE template_newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
					$template_newsletters_added = get_db_value($sql);

					$r->set_value("template_newsletters_added", $template_newsletters_added);
					$r->set_value("edited_by",   get_session("session_admin_id"));
					$r->set_value("edited_date", va_time());
					$r->set_value("emails_left", 0);
					$r->set_value("emails_sent", 0);
					$r->set_value("is_sent", 0);
					$r->set_value("is_prepared", 0);
					$r->update_record();
	
					$sql  = " DELETE FROM " . $table_prefix . "newsletters_emails ";
					$sql .= " WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER);
					$db->query($sql);
				} else {
					$r->set_value("added_by",   get_session("session_admin_id"));
					$r->set_value("added_date", va_time());
					$r->set_value("edited_by",   get_session("session_admin_id"));
					$r->set_value("edited_date", va_time());
					$r->set_value("emails_left", 0);
					$r->set_value("emails_sent", 0);
					$r->set_value("is_sent", 0);
					$r->set_value("is_prepared", 0);
					$r->insert_record();
					$newsletter_id = $db->last_insert_id();
					$r->set_value("newsletter_id", $newsletter_id);
				}
				update_filters();
				if ($r->get_value("newsletter_type") != 2) {
					// add emails only for newsletter type
					generate_emails($newsletter_id);
				}

				header ("Location: " . $return_page);
				exit;
			}
		}
	} elseif (strlen($newsletter_id))	{
		$r->get_db_values();
		select_filters();
		if ($r->get_value("subscribed_recipients") == "all") {
			$r->set_value("subscribed_recipients", 1);
		}
		// todo: show filters for old orders
		$users_recipients = explode(",", $r->get_value("users_recipients"));
		$orders_recipients = explode(",", $r->get_value("orders_recipients"));
		$admins_recipients = explode(",", $r->get_value("admins_recipients"));

	}	else {
		// new record (set default values)
		$r->set_value("is_active", 1);
		$r->set_value("newsletter_date", va_time());
		$r->set_value("mail_type", 1);
	}
	
	if ($r->is_empty("template_newsletters_added")) {
		$r->set_value("template_newsletters_added", 0);
	}
	$r->set_form_parameters();
	$fr->set_form_parameters();
	
	if (strlen($newsletter_id)) {
		$t->set_var("save_button", UPDATE_BUTTON);
		$t->parse("delete", false);
	} else {
		$t->set_var("save_button", ADD_NEW_MSG);
		$t->set_var("delete", "");
	}
	if ($r->get_value("is_sent")) {
		$t->set_var("success_message", NEWSLETTER_SENT_MSG);
		$t->parse("success", false);
	}



	// set styles for tabs
	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }
	$tabs = array(
		"general" => array("title" => ADMIN_GENERAL_MSG), 
		"subscribed" => array("title" => SUBSCRIBED_CUSTOMERS_MSG), 
		"users" => array("title" => REGISTERED_CUSTOMERS_MSG), 
		"orders" => array("title" => ORDERS_MSG), 
		"admins" => array("title" => ADMINISTRATORS_MSG),
		"custom" => array("title" => CUSTOM_MSG),
		"template" => array("title" => TEMPLATE_SETTINGS_MSG),
	);
	parse_tabs($tabs, $tab);

	$t->pparse("main");
	
	function select_filters()
	{
		global $r, $fr, $db, $table_prefix;
		$newsletter_id = $r->get_value("newsletter_id");
		if (strlen($newsletter_id)) {
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
		}
	}

	function insert_filters()
	{
		global $r, $fr, $db, $table_prefix;
		$new_newsletter_id = $db->last_insert_id();
		$r->set_value("newsletter_id", $new_newsletter_id);
		update_filters();
	}

	function update_filters()
	{
		global $r, $fr, $db, $table_prefix;
		$newsletter_id = $r->get_value("newsletter_id");
		if (strlen($newsletter_id)) {
			// delete filters before insert new
			$sql = " DELETE FROM " . $table_prefix . "newsletter_filters WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER); 
			$db->query($sql);
			// add new filters 
			foreach ($fr->parameters as $key => $value) {
				$parameter_name = $key;
				$value_type = $value[VALUE_TYPE];
				$parameter_values = $value[CONTROL_VALUE];
				if (is_array($parameter_values) && ($value_type == DATETIME || $value_type == DATE || $value_type == TIMESTAMP || $value_type == TIME)) {
					$parameter_values = va_timestamp($parameter_values);
				}

				if (!is_array($parameter_values)) { $parameter_values = array($parameter_values); } 
				for ($v = 0; $v < sizeof($parameter_values); $v++) {
					$parameter_value = $parameter_values[$v];
					if (strlen($parameter_value)) {
						$sql  = "INSERT INTO " . $table_prefix . "newsletter_filters (newsletter_id, filter_parameter, filter_value) VALUES (";
						$sql .= $db->tosql($newsletter_id, INTEGER) . ", ";
						$sql .= $db->tosql($parameter_name, TEXT) . ", ";
						$sql .= $db->tosql($parameter_value, TEXT) . ") ";
						$db->query($sql);
					}
				}
			}
		}
	}

	function delete_filters()
	{
		global $r, $db, $table_prefix;
		$newsletter_id = $r->get_value("newsletter_id");
		if (strlen($newsletter_id)) {
			// delete filters before insert new
			$sql = " DELETE FROM " . $table_prefix . "newsletter_filters WHERE newsletter_id=" . $db->tosql($newsletter_id, INTEGER); 
			$db->query($sql);
		}
	}

