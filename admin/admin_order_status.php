<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_order_status.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once($root_folder_path . "includes/tabs_functions.php");
	include_once("./admin_common.php");

	check_admin_security("sales_orders");
	check_admin_security("order_statuses");

	// check for field update field
	$php_lib_field = false; $status_attachments = false;
	$fields = $db->get_fields($table_prefix."order_statuses");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "status_php_lib") {
			$php_lib_field = true;
		} else if ($field_info["name"] == "mail_status_attachments") {
			$status_attachments = true;
		}
	}
	if (!$php_lib_field) {
		$sql = "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN status_php_lib VARCHAR(255) ";
		$db->query($sql);
	}
	if (!$status_attachments) {
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN mail_status_attachments TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN mail_status_attachments SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN mail_status_attachments BYTE ",
		);
		$sql = $sql_types[$db_type];
		$db->query($sql);
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN admin_status_attachments TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN admin_status_attachments SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "order_statuses ADD COLUMN admin_status_attachments BYTE ",
		);
		$sql = $sql_types[$db_type];
		$db->query($sql);
	}
	// end field check

	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }
	$operation = get_param("operation");
	if ($operation) { $tab = "general"; }
	$sites = get_param("sites");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_order_status.html");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_href", "admin.php");  
	$t->set_var("admin_lookup_tables_href",  "admin_lookup_tables.php");
	$t->set_var("admin_order_statuses_href", "admin_order_statuses.php");
	$t->set_var("admin_order_status_href",   "admin_order_status.php");
	$t->set_var("admin_email_help_href",     "admin_email_help.php");
	$t->set_var("admin_order_help_href",     "admin_order_help.php");
	$t->set_var("admin_download_info_href",  "admin_download_info.php");
	$t->set_var("admin_privileges_select_href", "admin_privileges_select.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", va_message("STATUS_MSG"), va_message("CONFIRM_DELETE_MSG")));

	$paid_statuses = 
		array( 
			array(1, va_message("PAID_MSG")), array(0, va_message("NOT_PAID_MSG"))
		);

	$activations_values = 
		array( 
			array(1, va_message("ACTIVATE_MSG")), array(0, va_message("DISABLE_MSG"))
		);

	$commission_values = 
		array( 
			array(1, va_message("COMMISSION_REWARD_ADD_MSG")), array(-1, va_message("COMMISSION_REWARD_SUBTRACT_MSG")),
		);

	$stock_level_values = 
		array( 
			array(1, va_message("STOCK_LEVEL_RESERVE_MSG")), array(-1, va_message("STOCK_LEVEL_RELEASE_MSG")),
		);

	$points_action_values = 
		array( 
			array(-1, va_message("POINTS_SUBTRACT_MSG")), array(1, va_message("POINTS_RETURN_MSG")),
		);

	$credit_action_values = 
		array( 
			array(-1, va_message("SUBSTRACT_CREDIT_AMOUNT_MSG")), array(1, va_message("RETURN_CREDIT_BALANCE_MSG")),
		);

	$credit_note_values = 
		array( 
			array(-1, va_message("SUBSTRACT_CREDIT_AMOUNT_MSG")), array(1, va_message("RETURN_CREDIT_BALANCE_MSG")),
		);

	$mail_types = 
		array( 
			array(1, va_message("HTML_MSG")), array(0, va_message("PLAIN_TEXT_MSG"))
		);

	$status_types = array(
		array("", ""), 
		array("NEW", "NEW"), 
		array("PAYMENT_INFO", "PAYMENT_INFO"), 
		array("CONFIRMED", "CONFIRMED"), 
		array("PAID", "PAID"), 
		array("PARTIALLY_PAID", "PARTIALLY_PAID"), 
		array("SHIPPED", "SHIPPED"), 
		array("PENDING", "PENDING"), 
		array("DECLINED", "DECLINED"), 
		array("VALIDATED", "VALIDATED"), 
		array("FAILED", "FAILED"), 
		array("DISPATCHED", "DISPATCHED"), 
		array("REFUNDED", "REFUNDED"), 
		array("CAPTURED", "CAPTURED"), 
		array("VOIDED", "VOIDED"), 
		array("AUTHORIZED", "AUTHORIZED"), 
		array("CANCELLED", "CANCELLED"), 
		array("APPROVED", "APPROVED"), 
		array("REVISION", "REVISION"), 
		array("CREDIT_NOTE", "CREDIT_NOTE"), 
		array("QUOTE_REQUEST", "QUOTE_REQUEST"), 
		array("EXPORTED", "EXPORTED"), 
		array("OTHER", "OTHER"), 
	);

	$html_editor = get_setting_value($settings, "html_editor_email", get_setting_value($settings, "html_editor", 1));
	$t->set_var("html_editor", $html_editor);
	$editors_list = 'mb,mrb,sb,affb,ab';
	add_html_editors($editors_list, $html_editor);
	
	$r = new VA_Record($table_prefix . "order_statuses");
	$r->return_page = "admin_order_statuses.php";

	$r->add_where("status_id", INTEGER);
	$r->add_checkbox("is_active", INTEGER);
	$r->change_property("is_active", DEFAULT_VALUE, 1);
	$r->add_textbox("status_order", INTEGER, va_message("STATUS_ORDER_MSG"));
	$r->parameters["status_order"][REQUIRED] = true;
	$r->add_select("status_type", TEXT, $status_types, va_message("STATUS_TYPE_MSG"));
	$r->change_property("status_type", REQUIRED, true);
	$r->add_textbox("status_name", TEXT, va_message("STATUS_NAME_MSG"));
	$r->change_property("status_name", REQUIRED, true);
	$r->add_textbox("status_php_lib", TEXT);
	$r->add_textbox("admin_order_class", TEXT);
	$r->add_textbox("user_order_class", TEXT);

	$r->add_checkbox("allow_user_cancel", INTEGER);
	$r->add_checkbox("is_user_cancel", INTEGER);
	$r->add_checkbox("payment_allowed", INTEGER);
	$r->add_checkbox("generate_invoice", INTEGER);
	$r->add_checkbox("user_invoice_activation", INTEGER);
	$r->add_checkbox("is_list", INTEGER);
	$r->change_property("is_list", DEFAULT_VALUE, 1);
	$r->add_checkbox("show_for_user", INTEGER);
	$r->change_property("show_for_user", DEFAULT_VALUE, 1);
	$r->add_checkbox("show_for_affiliate", INTEGER);
	$r->add_checkbox("show_for_merchant", INTEGER);
	$r->add_checkbox("item_notify", INTEGER);
	$r->add_checkbox("credit_note_action", INTEGER);
	$r->add_radio("paid_status", INTEGER, $paid_statuses, va_message("PAID_STATUS_MSG"));
	$r->add_radio("download_activation", INTEGER, $activations_values);
	$r->add_checkbox("download_notify", INTEGER);
	$r->add_radio("commission_action", INTEGER, $commission_values);
	$r->add_radio("stock_level_action", INTEGER, $stock_level_values);
	$r->add_radio("points_action", INTEGER, $points_action_values);
	$r->add_radio("credit_action", INTEGER, $credit_action_values);

	// customer notification fields
	$r->add_checkbox("mail_notify", INTEGER);
	$r->add_textbox("mail_from", TEXT);
	$r->add_textbox("mail_cc", TEXT);
	$r->add_textbox("mail_bcc", TEXT);
	$r->add_textbox("mail_reply_to", TEXT);
	$r->add_textbox("mail_return_path", TEXT);
	$r->add_checkbox("mail_pdf_invoice", INTEGER);
	$r->add_checkbox("mail_status_attachments", INTEGER);
	$r->add_textbox("mail_subject", TEXT);
	$r->add_radio("mail_type", INTEGER, $mail_types);
	$r->parameters["mail_type"][DEFAULT_VALUE] = 0;
	$r->add_textbox("mail_body", TEXT);

	$r->add_checkbox("sms_notify", INTEGER);
	$r->add_textbox("sms_recipient", TEXT, va_message("USER_SMS_RECIPIENT_MSG"));
	$r->add_textbox("sms_originator",TEXT, va_message("USER_SMS_ORIGINATOR_MSG"));
	$r->add_textbox("sms_message",   TEXT, va_message("USER_SMS_MESSAGE_MSG"));

	// merchant notification fields
	$r->add_checkbox("merchant_notify", INTEGER);
	$r->add_textbox("merchant_to", TEXT);
	$r->add_textbox("merchant_from", TEXT);
	$r->add_textbox("merchant_cc", TEXT);
	$r->add_textbox("merchant_bcc", TEXT);
	$r->add_textbox("merchant_reply_to", TEXT);
	$r->add_textbox("merchant_return_path", TEXT);
	$r->add_textbox("merchant_subject", TEXT);
	$r->add_radio("merchant_mail_type", INTEGER, $mail_types);
	$r->parameters["merchant_mail_type"][DEFAULT_VALUE] = 0;
	$r->add_textbox("merchant_body", TEXT);

	$r->add_checkbox("merchant_sms_notify", INTEGER);
	$r->add_textbox("merchant_sms_recipient", TEXT, va_message("MERCHANT_SMS_RECIPIENT_MSG"));
	$r->add_textbox("merchant_sms_originator",TEXT, va_message("MERCHANT_SMS_ORIGINATOR_MSG"));
	$r->add_textbox("merchant_sms_message",   TEXT, va_message("MERCHANT_SMS_MESSAGE_MSG"));


	// supplier notification fields
	$r->add_checkbox("supplier_notify", INTEGER);
	$r->add_textbox("supplier_to", TEXT);
	$r->add_textbox("supplier_from", TEXT);
	$r->add_textbox("supplier_cc", TEXT);
	$r->add_textbox("supplier_bcc", TEXT);
	$r->add_textbox("supplier_reply_to", TEXT);
	$r->add_textbox("supplier_return_path", TEXT);
	$r->add_textbox("supplier_subject", TEXT);
	$r->add_radio("supplier_mail_type", INTEGER, $mail_types);
	$r->parameters["supplier_mail_type"][DEFAULT_VALUE] = 0;
	$r->add_textbox("supplier_body", TEXT);

	$r->add_checkbox("supplier_sms_notify", INTEGER);
	$r->add_textbox("supplier_sms_recipient", TEXT, va_message("SMS_RECIPIENT_MSG"));
	$r->add_textbox("supplier_sms_originator",TEXT, va_message("SMS_ORIGINATOR_MSG"));
	$r->add_textbox("supplier_sms_message",   TEXT, va_message("SMS_MESSAGE_MSG"));

	// affiliate notification fields
	$r->add_checkbox("affiliate_notify", INTEGER);
	$r->add_textbox("affiliate_to", TEXT);
	$r->add_textbox("affiliate_from", TEXT);
	$r->add_textbox("affiliate_cc", TEXT);
	$r->add_textbox("affiliate_bcc", TEXT);
	$r->add_textbox("affiliate_reply_to", TEXT);
	$r->add_textbox("affiliate_return_path", TEXT);
	$r->add_textbox("affiliate_subject", TEXT);
	$r->add_radio("affiliate_mail_type", INTEGER, $mail_types);
	$r->parameters["affiliate_mail_type"][DEFAULT_VALUE] = 0;
	$r->add_textbox("affiliate_body", TEXT);

	$r->add_checkbox("affiliate_sms_notify", INTEGER);
	$r->add_textbox("affiliate_sms_recipient", TEXT, va_message("SMS_RECIPIENT_MSG"));
	$r->add_textbox("affiliate_sms_originator",TEXT, va_message("SMS_ORIGINATOR_MSG"));
	$r->add_textbox("affiliate_sms_message",   TEXT, va_message("SMS_MESSAGE_MSG"));

	// admin notification fields
	$r->add_checkbox("admin_notify", INTEGER);
	$r->add_textbox("admin_to", TEXT);
	$r->add_textbox("admin_to_groups_ids", TEXT);
	$r->add_textbox("admin_from", TEXT);
	$r->add_textbox("admin_cc", TEXT);
	$r->add_textbox("admin_bcc", TEXT);
	$r->add_textbox("admin_reply_to", TEXT);
	$r->add_textbox("admin_return_path", TEXT);
	$r->add_checkbox("admin_pdf_invoice", INTEGER);
	$r->add_checkbox("admin_status_attachments", INTEGER);
	$r->add_textbox("admin_subject", TEXT);
	$r->add_radio("admin_mail_type", INTEGER, $mail_types);
	$r->parameters["admin_mail_type"][DEFAULT_VALUE] = 0;
	$r->add_textbox("admin_body", TEXT);

	$r->add_checkbox("admin_sms_notify", INTEGER);
	$r->add_textbox("admin_sms_recipient", TEXT, va_message("ADMIN_SMS_RECIPIENT_MSG"));
	$r->add_textbox("admin_sms_originator",TEXT, va_message("ADMIN_SMS_ORIGINATOR_MSG"));
	$r->add_textbox("admin_sms_message",   TEXT, va_message("ADMIN_SMS_MESSAGE_MSG"));

	$r->add_textbox("final_title",   TEXT);
	$r->add_textbox("final_message",   TEXT);
	$r->set_event(BEFORE_INSERT, "before_update");
	$r->set_event(BEFORE_UPDATE, "before_update");
	$r->set_event(AFTER_INSERT, "update_status_data");
	$r->set_event(AFTER_UPDATE, "update_status_data");
	$r->set_event(AFTER_SELECT, "select_status_data");
	$r->set_event(BEFORE_VALIDATE, "check_status_options");
	$r->set_event(BEFORE_DEFAULT, "set_status_order");
	$r->set_event(BEFORE_SHOW, "show_admin_groups");

	$r->add_hidden("sites", TEXT);
	$r->add_checkbox("sites_all", INTEGER);
	$r->change_property("sites_all", DEFAULT_VALUE, 1);

	// access levels
	$r->add_checkbox("view_order_groups_all", INTEGER);
	$r->change_property("view_order_groups_all", DEFAULT_VALUE, 1);
	$r->add_textbox("view_order_groups_ids", TEXT);
	$r->add_checkbox("set_status_groups_all", INTEGER);
	$r->change_property("set_status_groups_all", DEFAULT_VALUE, 1);
	$r->add_textbox("set_status_groups_ids", TEXT);
	$r->add_checkbox("update_order_groups_all", INTEGER);
	$r->change_property("update_order_groups_all", DEFAULT_VALUE, 1);
	$r->add_textbox("update_order_groups_ids", TEXT);

	$r->process();

	if ($sitelist) {
		$sites = array();
		$selected_sites = explode(",", $r->get_value("sites"));
		$sql = " SELECT site_id, site_name FROM " . $table_prefix . "sites ";
		$db->query($sql);
		while ($db->next_record())	{
			$site_id   = $db->f("site_id");
			$site_name = $db->f("site_name");
			$sites[$site_id] = $site_name;
			$t->set_var("site_id", $site_id);
			$t->set_var("site_name", $site_name);
			if (in_array($site_id, $selected_sites)) {
				$t->parse("selected_sites", true);
			} else {
				$t->parse("available_sites", true);
			}
		}
	}

	$tabs = array(
		"general" => array("title" => va_message("ADMIN_GENERAL_MSG")), 
		"user_notify" => array("title" => va_message("USER_NOTIFICATION_MSG")), 
		"merchant_notify" => array("title" => va_message("MERCHANT_MSG")), 
		"supplier_notify" => array("title" => va_message("SUPPLIER_MSG")), 
		"affiliate_notify" => array("title" => va_message("AFFILIATE_MSG")), 
		"admin_notify" => array("title" => va_message("ADMINISTRATOR_NOTIFICATION_MSG")), 
		"final_checkout" => array("title" => va_message("FINAL_CHECKOUT_MSG")),
		"sites"   => array("title" => va_message("ADMIN_SITES_MSG"), "show" => $sitelist),
		"access_levels" => array("title" => va_message("ACCESS_LEVELS_MSG")),
	);

	parse_tabs($tabs);

	$t->pparse("main");

	function check_status_options()
	{
		global $r, $db, $table_prefix;
		if ($r->get_value("sms_notify")) {
			$r->change_property("sms_message", REQUIRED, true);
		} 
		if ($r->get_value("merchant_sms_notify")) {
			$r->change_property("merchant_sms_message", REQUIRED, true);
		} 
		if ($r->get_value("admin_sms_notify")) {
			$r->change_property("admin_sms_message", REQUIRED, true);
		} 

		$status_id = $r->get_value("status_id");
		$status_type = $r->get_value("status_type");
		if ($status_type == "NEW") {
			$sql  = " SELECT COUNT(*) FROM ".$table_prefix."order_statuses ";
			$sql .= " WHERE status_type='NEW' ";
			if (strlen($status_id)) {
				$sql .= " AND status_id<>" . $db->tosql($status_id, INTEGER);
			}
			$new_statuses = get_db_value($sql);
			if ($new_statuses > 0)  {
				$r->errors = va_constant("STATUS_NEW_LIMIT_ERROR")."<br/>";
			}
		}
	}

	function set_status_order()  
	{
		global $db, $table_prefix, $r;
		$sql = "SELECT MAX(status_order) FROM " . $table_prefix . "order_statuses ";
		$db->query($sql);
		if ($db->next_record()) {
			$status_order = $db->f(0) + 1;
			$r->change_property("status_order", DEFAULT_VALUE, $status_order);
		}	
	}

	function show_admin_groups()
	{
		global $db, $table_prefix, $r, $t;

		$data = array(
			"admin_to_groups_ids" => "admin_to_groups",
			"view_order_groups_ids" => "view_order_admin_groups",
			"set_status_groups_ids" => "set_status_admin_groups",
			"update_order_groups_ids" => "update_order_admin_groups",
		);
		
		foreach ($data as $field_name => $block_name) {
			$ids = $r->get_value($field_name);
			if ($ids) {
				$sql  = " SELECT ap.privilege_id, ap.privilege_name ";
				$sql .= " FROM " . $table_prefix . "admin_privileges ap ";
				$sql .= " WHERE ap.privilege_id IN (" . $db->tosql($ids, INTEGERS_LIST) . ") ";
				$sql .= " ORDER BY ap.privilege_name ";
				$db->query($sql);
				while($db->next_record())
				{
					$row_privilege_id = $db->f("privilege_id");
					$privilege_name = get_translation($db->f("privilege_name"));
			
					$t->set_var("privilege_id", $row_privilege_id);
					$t->set_var("privilege_name", $privilege_name);
					$t->set_var("privilege_name_js", str_replace("\"", "&quot;", $privilege_name));
			
					$t->parse($block_name, true);
					$t->parse($block_name."_js", true);
				}
			}
		}

	}

function before_update($params)
{
	global $r, $db, $table_prefix, $sitelist;
	$event = isset($params["event"]) ? $params["event"] : "";
	if ($event == BEFORE_INSERT) {
		if ($db->DBType == "postgre") {
			$status_id = get_db_value(" SELECT NEXTVAL('seq_" . $table_prefix . "order_statuses ') ");
			$r->change_property("status_id", USE_IN_INSERT, true);
			$r->set_value("status_id", $status_id);
		}
	}

	if (!$sitelist) {
		$r->set_value("sites_all", 1);
	}
}

function update_status_data($params)
{
	global $r, $t, $db, $table_prefix, $sitelist, $selected_sites, $selected_languages;

	$event = isset($params["event"]) ? $params["event"] : "";
	if ($event == AFTER_INSERT) {
		if ($db->DBType == "mysql") {
			$status_id = get_db_value(" SELECT LAST_INSERT_ID() ");
			$r->set_value("status_id", $status_id);
		} elseif ($db->DBType == "access") {
			$status_id = get_db_value(" SELECT @@IDENTITY ");
			$r->set_value("status_id", $status_id);
		} elseif ($db->DBType != "postgre") {
			$status_id = get_db_value(" SELECT MAX(status_id) FROM " . $table_prefix . "order_statuses ");
			$r->set_value("status_id", $status_id);
		}
	}

	$status_id = $r->get_value("status_id");

	$selected_sites = explode(",", $r->get_value("sites"));
	if ($sitelist) {
		$db->query("DELETE FROM " . $table_prefix . "order_statuses_sites WHERE status_id=" . $db->tosql($status_id, INTEGER));
		for ($st = 0; $st < sizeof($selected_sites); $st++) {
			$site_id = $selected_sites[$st];
			if (strlen($site_id)) {
				$sql  = " INSERT INTO " . $table_prefix . "order_statuses_sites (status_id, site_id) VALUES (";
				$sql .= $db->tosql($status_id, INTEGER) . ", ";
				$sql .= $db->tosql($site_id, INTEGER) . ") ";
				$db->query($sql);
			}
		}
	}
}

function select_status_data()
{
	global $r, $db, $table_prefix;
	$status_id = $r->get_value("status_id");

	$selected_sites = array();
	if ($status_id) {
		$sql  = " SELECT site_id FROM " . $table_prefix . "order_statuses_sites ";
		$sql .= " WHERE status_id=" . $db->tosql($status_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$selected_sites[] = $db->f("site_id");
		}
	}
	$r->set_value("sites", implode(",", $selected_sites));
}


