<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_black_ip.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "messages/" . $language_code . "/support_messages.php");
	include_once($root_folder_path . "messages/" . $language_code . "/forum_messages.php");

	check_admin_security("black_ips");

	$popup = get_param("popup");
	$rules_fields = array("log_in", "sign_up", "orders", "support", "forum", "products_reviews", "articles_reviews");


	$module_block_actions = array(
		array("blocked", va_message("NOT_ALLOWED_MSG")),
		array("warning", va_message("SHOW_WARNING_MSG")),
		array("allowed", va_message("ALLOWED_MSG")),
	);

	$module_allowed_actions = array(
		array("blocked", va_message("NOT_ALLOWED_MSG")),
		array("allowed", va_message("ALLOWED_MSG")),
	);

	$address_actions = array(
		array(0, va_message("SHOW_WARNING_MSG")),
		array(1, va_message("BLOCK_ALL_ACTIVITIES_MSG")),
	);

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_black_ip.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_black_ips_href", "admin_black_ips.php");
	$t->set_var("admin_black_ip_href", "admin_black_ip.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", IP_ADDRESS_MSG, CONFIRM_DELETE_MSG));

	$r = new VA_Record($table_prefix . "black_ips");
	$r->return_page = "admin_black_ips.php";
	if ($popup) { $r->redirect = false; }
	$r->add_hidden("popup", TEXT);
	$r->add_hidden("module", TEXT);
	$r->add_where("ip_address", TEXT);

	$r->add_textbox("ip_address_edit", TEXT, IP_ADDRESS_MSG);
	$r->change_property("ip_address_edit", COLUMN_NAME, "ip_address");
	$r->change_property("ip_address_edit", REQUIRED, true);
	$r->change_property("ip_address_edit", UNIQUE, true);
	$r->change_property("ip_address_edit", REGEXP_MASK, "/^[\d\.\:]+$/");
	$r->change_property("ip_address_edit", MIN_LENGTH, 3);
	$r->change_property("ip_address_edit", MAX_LENGTH, 15);

	$r->add_textbox("address_notes", TEXT, NOTES_MSG);

	$r->add_hidden("log_in", TEXT);
	$r->change_property("log_in", CONTROL_TYPE, RADIOBUTTON);
	$r->change_property("log_in", VALUES_LIST, $module_allowed_actions);
	$r->change_property("log_in", DEFAULT_VALUE, "blocked");

	$r->add_hidden("sign_up", TEXT);
	$r->change_property("sign_up", CONTROL_TYPE, RADIOBUTTON);
	$r->change_property("sign_up", VALUES_LIST, $module_block_actions);
	$r->change_property("sign_up", DEFAULT_VALUE, "blocked");

	$r->add_hidden("orders", TEXT);
	$r->change_property("orders", CONTROL_TYPE, RADIOBUTTON);
	$r->change_property("orders", VALUES_LIST, $module_block_actions);
	$r->change_property("orders", DEFAULT_VALUE, "blocked");

	$r->add_hidden("support", TEXT);
	$r->change_property("support", CONTROL_TYPE, RADIOBUTTON);
	$r->change_property("support", VALUES_LIST, $module_block_actions);
	$r->change_property("support", DEFAULT_VALUE, "blocked");

	$r->add_hidden("forum", TEXT);
	$r->change_property("forum", CONTROL_TYPE, RADIOBUTTON);
	$r->change_property("forum", VALUES_LIST, $module_block_actions);
	$r->change_property("forum", DEFAULT_VALUE, "blocked");

	$r->add_hidden("products_reviews", TEXT);
	$r->change_property("products_reviews", CONTROL_TYPE, RADIOBUTTON);
	$r->change_property("products_reviews", VALUES_LIST, $module_block_actions);
	$r->change_property("products_reviews", DEFAULT_VALUE, "blocked");

	$r->add_hidden("articles_reviews", TEXT);
	$r->change_property("articles_reviews", CONTROL_TYPE, RADIOBUTTON);
	$r->change_property("articles_reviews", VALUES_LIST, $module_block_actions);
	$r->change_property("articles_reviews", DEFAULT_VALUE, "blocked");

	$r->add_textbox("ip_rules", TEXT);
	$r->add_textbox("date_added", DATETIME);

	$r->set_event(AFTER_SELECT, "check_select");
	$r->set_event(BEFORE_UPDATE, "prepare_db_data");
	$r->set_event(BEFORE_INSERT, "prepare_db_data");
	
	$r->set_event(AFTER_INSERT, "set_black_ip");
	$r->set_event(AFTER_UPDATE, "set_black_ip");
	$r->set_event(AFTER_DELETE, "clear_black_ip");
	$r->set_event(ON_CANCEL_OPERATION, "set_ip_class");

	$r->process();

	if ($popup) {
		$t->set_var("popup_class", "pg-popup");
	} else {
		include_once("./admin_header.php");
		include_once("./admin_footer.php");
	}

	$t->pparse("main");

function check_select($params)
{
	global $r, $rules_fields;
	$record_returned = $params["record_returned"];
	if (!$record_returned) {
		$r->where_set = false;
		$ip_address = $r->get_value("ip_address");
		$r->set_value("ip_address", "");
		$r->set_value("ip_address_edit", $ip_address);
		$r->set_default_values();
	} else {
		$ip_rules = $r->get_value("ip_rules");
		if ($ip_rules) {
			$ip_rules = json_decode($ip_rules, true);
			foreach ($ip_rules as $rule_name => $rule_value) {
				if ($r->parameter_exists($rule_name)) {
					$r->set_value($rule_name, $rule_value);
				}
			}
		}
	}
}

function prepare_db_data()
{
	global $r, $t, $rules_fields;
	$ip_rules = array();
	foreach ($rules_fields as $rule_name) {
		$rule_value = $r->get_value($rule_name);
		$ip_rules[$rule_name] = $rule_value;
	}
	$module = $r->get_value("module");
	$module_rule = get_setting_value($ip_rules, $module, "blocked");
	if ($module_rule == "block" || $module_rule == "blocked") {
		$t->set_var("ip_class", "blocked-ip");
	} else if ($module_rule == "warn" || $module_rule == "warning") {
		$t->set_var("ip_class", "warning-ip");
	} else {
		$t->set_var("ip_class", "black-ip");
	}
	
	$r->set_value("ip_rules", json_encode($ip_rules));
}
                                       

function set_black_ip()
{
	global $t;
	$t->set_var("ip_operation", "add");
	$t->parse("set_ip_class", false);
}
function clear_black_ip()
{
	global $t;
	$t->set_var("ip_operation", "remove");
	$t->set_var("ip_class", "black-ip");
	$t->parse("set_ip_class", false);
}

function set_ip_class()
{
	global $t;
	$t->parse("close_popup", false);
}

