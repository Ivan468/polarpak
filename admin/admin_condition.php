<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_condition.php                                      ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/friendly_functions.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");

	check_admin_security("static_tables");

	$operation = get_param("operation");
	$win_type = get_param("win_type");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_condition.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_lookup_tables_href", "admin_lookup_tables.php");
	$t->set_var("admin_conditions_href", "admin_conditions.php");
	$t->set_var("admin_condition_href", "admin_condition.php");
	$t->set_var("admin_authors_href", "admin_authors.php");
	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", va_constant("CONDITION_MSG"), va_constant("CONFIRM_DELETE_MSG")));
	$date_format_msg = str_replace("{date_format}", join("", $date_edit_format), va_constant("DATE_FORMAT_MSG"));
	$t->set_var("date_format", join("", $date_edit_format));
	$t->set_var("datetime_format", join("", $datetime_edit_format));
	$t->set_var("date_format_msg", $date_format_msg);
	$t->set_var("date_edit_format", join("", $date_edit_format));


	$full_image_url = get_setting_value($settings, "full_image_url", 0);
	$site_url = get_setting_value($settings, "site_url", "");
	if ($full_image_url){
		$t->set_var("image_site_url", $site_url);
	} else {
		$t->set_var("image_site_url", "");					
	}

	$r = new VA_Record($table_prefix . "conditions");
	$r->return_page = "admin_conditions.php";

	$r->add_where("condition_id", INTEGER);

	$r->add_textbox("condition_name", TEXT, va_constant("NAME_MSG"));
	$r->change_property("condition_name", REQUIRED, true);
	$r->add_textbox("sort_order", INTEGER, va_constant("ADMIN_ORDER_MSG"));
	$r->change_property("sort_order", DEFAULT_VALUE, 1);

	$r->add_hidden("sw", TEXT);
	$r->add_hidden("form_name", TEXT);
	$r->add_hidden("items_field", TEXT);
	$r->add_hidden("items_object", TEXT);
	$r->add_hidden("item_template", TEXT);
	$r->add_hidden("selection_type", TEXT);
	$r->add_hidden("win_type", TEXT);
	$r->add_hidden("sort_ord", TEXT);
	$r->add_hidden("sort_dir", TEXT);
	$r->add_hidden("page", TEXT);

	$r->events[BEFORE_INSERT] = "set_condition_data";
	$r->events[BEFORE_UPDATE] = "set_condition_data";
	$r->events[AFTER_DELETE] = "delete_condition_data";

	$r->process();

	if ($win_type != "popup") {
		$custom_breadcrumb = array(
			"admin_global_settings.php" => va_constant("SETTINGS_MSG"),
			"admin_menu.php?code=system-settings" => va_constant("SYSTEM_MSG"),
			"admin_static_tables.php" => va_constant("STATIC_TABLES_MSG"),
			$r->get_return_url() => va_constant("CONDITIONS_MSG"),
			"admin_condition.php" => va_constant("EDIT_MSG"),
		);

		include_once("./admin_header.php");
		include_once("./admin_footer.php");
	}

	$t->pparse("main");

function set_condition_data($params)
{
	global $r, $db, $table_prefix;	

	$event = isset($params["event"]) ? $params["event"] : "";
	if ($event == BEFORE_INSERT) {
		if ($db->DBType == "postgre") {
			$condition_id = get_db_value(" SELECT NEXTVAL('seq_" . $table_prefix . "conditions ') ");
			$r->change_property("condition_id", USE_IN_INSERT, true);
			$r->set_value("condition_id", $condition_id);
		}
	}
}

function update_condition_data($params)
{
	global $r, $t, $db, $table_prefix;

	$event = isset($params["event"]) ? $params["event"] : "";
	if ($event == AFTER_INSERT) {
		if ($db->DBType == "mysql") {
			$condition_id = get_db_value(" SELECT LAST_INSERT_ID() ");
			$r->set_value("condition_id", $condition_id);
		} elseif ($db->DBType == "access") {
			$condition_id = get_db_value(" SELECT @@IDENTITY ");
			$r->set_value("condition_id", $condition_id);
		} else {
			$condition_id = get_db_value(" SELECT MAX(condition_id) FROM " . $table_prefix . "conditions ");
			$r->set_value("condition_id", $condition_id);
		}
	}
	$condition_id = $r->get_value("condition_id");
}

function delete_condition_data()
{
	global $r, $t, $db, $table_prefix;
	$condition_id = $r->get_value("condition_id");
	$sql  = " UPDATE ".$table_prefix."items ";
	$sql .= " SET condition_id=NULL ";
	$sql .= " WHERE condition_id=" . $db->tosql($condition_id, INTEGER);
	$db->query($sql);
}


?>
