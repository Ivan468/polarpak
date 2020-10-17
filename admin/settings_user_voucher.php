<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  settings_user_voucher.php                                ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/tabs_functions.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once($root_folder_path . "messages/" . $language_code . "/reviews_messages.php");
	include_once("./admin_common.php");

	check_admin_security("coupons");

	// check default currency code
	$default_currency_code = get_db_value("SELECT currency_code FROM ".$table_prefix."currencies WHERE is_default=1");

	$setting_type = "user_voucher";

	$validation_types = 
		array( 
			array(2, FOR_ALL_USERS_MSG), array(1, UNREGISTERED_USER_ONLY_MSG), array(0, NOT_USED_MSG)
		);

	$message_types =
		array(
			array(1, HTML_MSG), array(0, PLAIN_TEXT_MSG)
		);

	$time_periods =
		array(
			array("", ""), array(1, SECOND_MSG), array(2, MINUTE_MSG), array(3, HOUR_MSG),
		);

	$sql = "SELECT status_id, status_name FROM " . $table_prefix . "order_statuses WHERE is_active=1 ORDER BY status_order, status_id";
	$order_statuses = get_db_values($sql, array(array("", "")));

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "settings_user_voucher.html");

	include_once("./admin_header.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("settings_user_voucher_href", "settings_user_voucher.php");
	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");
	$t->set_var("admin_email_help_href", "admin_email_help.php");
	$t->set_var("default_currency_code", htmlspecialchars($default_currency_code));

	$r = new VA_Record($table_prefix . "global_settings");
	// global voucher settings
	$r->add_checkbox("voucher_purchase", INTEGER);
	$r->add_textbox("purchase_fee_percent", NUMBER, va_constant("PURCHASE_FEE_MSG")." - ".va_constant("PERCENTAGE_MSG"));
	$r->add_textbox("purchase_fee_amount", NUMBER, va_constant("PURCHASE_FEE_MSG")." - ".va_constant("FIXED_AMOUNT_MSG"));

	$r->add_checkbox("voucher_transfer", INTEGER);
	$r->add_textbox("transfer_time_limit", INTEGER);
	$r->add_textbox("transfer_fee_percent", NUMBER, va_constant("TRANSFER_FEE_MSG")." - ".va_constant("PERCENTAGE_MSG"));
	$r->add_textbox("transfer_fee_amount", NUMBER, va_constant("TRANSFER_FEE_MSG")." - ".va_constant("FIXED_AMOUNT_MSG"));

	$r->add_checkbox("cash_out_voucher", INTEGER);
	$r->add_textbox("cash_out_time_limit", INTEGER);
	$r->add_textbox("cash_out_fee_percent", NUMBER, va_constant("CASH_OUT_FEE_MSG")." - ".va_constant("PERCENTAGE_MSG"));
	$r->add_textbox("cash_out_fee_amount", NUMBER, va_constant("CASH_OUT_FEE_MSG")." - ".va_constant("FIXED_AMOUNT_MSG"));
	$r->add_select("cash_out_status_id", INTEGER, $order_statuses, ORDER_STATUS_MSG);

	// notification fields
	$r->add_checkbox("request_notify", INTEGER, SEND_NOTIFICATION_ADMIN_MSG);
	$r->add_textbox("request_from", TEXT);
	$r->add_textbox("request_cc", TEXT);
	$r->add_textbox("request_bcc", TEXT);
	$r->add_textbox("request_reply_to", TEXT);
	$r->add_textbox("request_return_path", TEXT);
	$r->add_textbox("request_subject", TEXT);
	$r->add_radio("request_message_type", TEXT, $message_types);
	$r->add_textbox("request_message", TEXT);

	// sms notification settings
	$r->add_checkbox("request_sms_notify", INTEGER, SMS_SEND_USER_MSG);
	$r->add_textbox("request_sms_recipient", TEXT, SMS_RECIPIENT_MSG);
	$r->add_textbox("request_sms_originator", TEXT, SMS_ORIGINATOR_MSG);
	$r->add_textbox("request_sms_message", TEXT, SMS_MESSAGE_MSG);

	// notification fields
	$r->add_checkbox("sender_notify", INTEGER, SEND_NOTIFICATION_ADMIN_MSG);
	$r->add_textbox("sender_from", TEXT);
	$r->add_textbox("sender_cc", TEXT);
	$r->add_textbox("sender_bcc", TEXT);
	$r->add_textbox("sender_reply_to", TEXT);
	$r->add_textbox("sender_return_path", TEXT);
	$r->add_textbox("sender_subject", TEXT);
	$r->add_radio("sender_message_type", TEXT, $message_types);
	$r->add_textbox("sender_message", TEXT);

	// sms notification settings
	$r->add_checkbox("sender_sms_notify", INTEGER, SMS_SEND_USER_MSG);
	$r->add_textbox("sender_sms_recipient", TEXT, SMS_RECIPIENT_MSG);
	$r->add_textbox("sender_sms_originator", TEXT, SMS_ORIGINATOR_MSG);
	$r->add_textbox("sender_sms_message", TEXT, SMS_MESSAGE_MSG);

	// notification fields
	$r->add_checkbox("receiver_notify", INTEGER, SEND_NOTIFICATION_ADMIN_MSG);
	$r->add_textbox("receiver_from", TEXT);
	$r->add_textbox("receiver_cc", TEXT);
	$r->add_textbox("receiver_bcc", TEXT);
	$r->add_textbox("receiver_reply_to", TEXT);
	$r->add_textbox("receiver_return_path", TEXT);
	$r->add_textbox("receiver_subject", TEXT);
	$r->add_radio("receiver_message_type", TEXT, $message_types);
	$r->add_textbox("receiver_message", TEXT);

	// sms notification settings
	$r->add_checkbox("receiver_sms_notify", INTEGER, SMS_SEND_USER_MSG);
	$r->add_textbox("receiver_sms_recipient", TEXT, SMS_RECIPIENT_MSG);
	$r->add_textbox("receiver_sms_originator", TEXT, SMS_ORIGINATOR_MSG);
	$r->add_textbox("receiver_sms_message", TEXT, SMS_MESSAGE_MSG);

	// notification fields
	$r->add_checkbox("cash_request_notify", INTEGER, SEND_NOTIFICATION_ADMIN_MSG);
	$r->add_textbox("cash_request_from", TEXT);
	$r->add_textbox("cash_request_cc", TEXT);
	$r->add_textbox("cash_request_bcc", TEXT);
	$r->add_textbox("cash_request_reply_to", TEXT);
	$r->add_textbox("cash_request_return_path", TEXT);
	$r->add_textbox("cash_request_subject", TEXT);
	$r->add_radio("cash_request_message_type", TEXT, $message_types);
	$r->add_textbox("cash_request_message", TEXT);

	// sms notification settings
	$r->add_checkbox("cash_request_sms_notify", INTEGER, SMS_SEND_USER_MSG);
	$r->add_textbox("cash_request_sms_recipient", TEXT, SMS_RECIPIENT_MSG);
	$r->add_textbox("cash_request_sms_originator", TEXT, SMS_ORIGINATOR_MSG);
	$r->add_textbox("cash_request_sms_message", TEXT, SMS_MESSAGE_MSG);

	// notification fields
	$r->add_checkbox("cash_confirm_notify", INTEGER, SEND_NOTIFICATION_ADMIN_MSG);
	$r->add_textbox("cash_confirm_from", TEXT);
	$r->add_textbox("cash_confirm_cc", TEXT);
	$r->add_textbox("cash_confirm_bcc", TEXT);
	$r->add_textbox("cash_confirm_reply_to", TEXT);
	$r->add_textbox("cash_confirm_return_path", TEXT);
	$r->add_textbox("cash_confirm_subject", TEXT);
	$r->add_radio("cash_confirm_message_type", TEXT, $message_types);
	$r->add_textbox("cash_confirm_message", TEXT);

	// sms notification settings
	$r->add_checkbox("cash_confirm_sms_notify", INTEGER, SMS_SEND_USER_MSG);
	$r->add_textbox("cash_confirm_sms_recipient", TEXT, SMS_RECIPIENT_MSG);
	$r->add_textbox("cash_confirm_sms_originator", TEXT, SMS_ORIGINATOR_MSG);
	$r->add_textbox("cash_confirm_sms_message", TEXT, SMS_MESSAGE_MSG);

	$r->get_form_values();

	$param_site_id = get_session("session_site_id");
	$tab = get_param("tab");
	if (!$tab) { $tab = "admin"; }
	$operation = get_param("operation");
	$return_page = get_param("rp");
	if (!strlen($return_page)) $return_page = "admin.php";
	if (strlen($operation))
	{
		$tab = "admin";
		if ($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		}

		$is_valid = $r->validate();

		if ($is_valid && $r->get_value("voucher_transfer")) {
			// check if one notification selected
			if (!$r->get_value("request_notify") && !$r->get_value("request_sms_notify")) {
				$error_message  = str_replace("{field_name}", va_constant("TRANSFER_REQUEST_MSG").": ".va_constant("EMAIL_NOTIFICATION_MSG"), va_constant("REQUIRED_MESSAGE"))."<br/>";
				$error_message .= OR_MSG."<br/>";
				$error_message .= str_replace("{field_name}", va_constant("TRANSFER_REQUEST_MSG").": ".va_constant("SMS_NOTIFICATION_USER_MSG"), va_constant("REQUIRED_MESSAGE"))."<br/>";
				$r->errors = $error_message;
			}
		}

		if (!strlen($r->errors))
		{
			$sql  = " DELETE FROM " . $table_prefix . "global_settings WHERE setting_type='".$setting_type."'";
			$sql .= " AND site_id=" . $db->tosql($param_site_id,INTEGER);
			$db->query($sql);
			foreach ($r->parameters as $key => $value)
			{
				$sql  = "INSERT INTO " . $table_prefix . "global_settings (setting_type, setting_name, setting_value, site_id) VALUES (";
				$sql .= "'".$setting_type."', '" . $key . "'," . $db->tosql($value[CONTROL_VALUE], TEXT) . ",";
				$sql .= $db->tosql($param_site_id,INTEGER) . ") ";
				$db->query($sql);
			}
			set_session("session_settings", "");

			header("Location: " . $return_page);
			exit;
		}
	}	else { // get settings
		foreach ($r->parameters as $key => $value)
		{
			$sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type='".$setting_type."' AND setting_name='" . $key . "'";
			$sql .= " AND ( site_id=1 OR site_id=" . $db->tosql($param_site_id,INTEGER). ") ";
			$sql .= " ORDER BY site_id DESC ";
			$r->set_value($key, get_db_value($sql));
		}
	}

	$r->set_parameters();
	$t->set_var("rp", htmlspecialchars($return_page));

	// set styles for tabs
	$tabs = array(
			"general" => array("title" => ADMIN_GENERAL_MSG), 
			"request" => array("title" => TRANSFER_REQUEST_MSG), 
			"confirm" => array("title" => TRANSFER_CONFIRMATION_MSG), 
			"cash_request" => array("title" => CASH_OUT_REQUEST_MSG), 
			"cash_confirm" => array("title" => CASH_OUT_CONFIRMATION_MSG), 
		);

	parse_tabs($tabs);

	// multisites
	if ($sitelist) {
		$sites   = get_db_values("SELECT site_id,site_name FROM " . $table_prefix . "sites ORDER BY site_id ", "");
		set_options($sites, $param_site_id, "param_site_id");
		$t->parse("sitelist", false);
	}	

	include_once("./admin_footer.php");
	
	$t->pparse("main");

?>