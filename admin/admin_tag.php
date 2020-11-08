<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_tag.php                                            ***
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

	check_admin_security("");

	$operation = get_param("operation");
	$win_type = get_param("win_type");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_tag.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_lookup_tables_href", "admin_lookup_tables.php");
	$t->set_var("admin_tags_href", "admin_tags.php");
	$t->set_var("admin_tag_href", "admin_tag.php");
	$t->set_var("admin_authors_href", "admin_authors.php");
	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", TAG_MSG, CONFIRM_DELETE_MSG));
	$date_format_msg = str_replace("{date_format}", join("", $date_edit_format), DATE_FORMAT_MSG);
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

	$r = new VA_Record($table_prefix . "tags");
	$r->return_page = "admin_tags.php";

	$r->add_where("tag_id", INTEGER);

	$r->add_textbox("tag_name", TEXT, NAME_MSG);
	$r->change_property("tag_name", REQUIRED, true);
	$r->add_textbox("name_first", TEXT);
	$r->add_textbox("friendly_url", TEXT, FRIENDLY_URL_MSG);
	$r->change_property("friendly_url", USE_SQL_NULL, false);
	$r->change_property("friendly_url", BEFORE_VALIDATE, "validate_friendly_url");
	$r->change_property("friendly_url", REGEXP_MASK, FRIENDLY_URL_REGEXP);
	$r->change_property("friendly_url", REGEXP_ERROR, ALPHANUMERIC_ALLOWED_ERROR);

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

	$r->events[BEFORE_INSERT] = "set_tag_data";
	$r->events[BEFORE_UPDATE] = "set_tag_data";
	$r->events[AFTER_DELETE] = "delete_tag_data";

	$r->process();

	if ($win_type != "popup") {
		$custom_breadcrumb = array(
			"admin_global_settings.php" => SETTINGS_MSG,
			"admin_lookup_tables.php" => STATIC_TABLES_MSG,
			$r->get_return_url() => TAGS_MSG,
			"admin_tag.php" => EDIT_MSG,
		);

		include_once("./admin_header.php");
		include_once("./admin_footer.php");
	}

	$t->pparse("main");

function set_tag_data($params)
{
	global $r, $db, $table_prefix;	

	$event = isset($params["event"]) ? $params["event"] : "";
	if ($event == BEFORE_INSERT) {
		if ($db->DBType == "postgre") {
			$tag_id = get_db_value(" SELECT NEXTVAL('seq_" . $table_prefix . "tags ') ");
			$r->change_property("tag_id", USE_IN_INSERT, true);
			$r->set_value("tag_id", $tag_id);
		}
	}
	set_friendly_url();
	// set first letter for tag name
	$tag_name = trim($r->get_value("tag_name"));
	$name_first = substr($tag_name,0,1);
	$r->set_value("name_first", $name_first);
}

function update_tag_data($params)
{
	global $r, $t, $db, $table_prefix;

	$event = isset($params["event"]) ? $params["event"] : "";
	if ($event == AFTER_INSERT) {
		if ($db->DBType == "mysql") {
			$tag_id = get_db_value(" SELECT LAST_INSERT_ID() ");
			$r->set_value("tag_id", $tag_id);
		} elseif ($db->DBType == "access") {
			$tag_id = get_db_value(" SELECT @@IDENTITY ");
			$r->set_value("tag_id", $tag_id);
		} elseif ($db->DBType == "db2") {
			$tag_id = get_db_value(" SELECT PREVVAL FOR seq_" . $table_prefix . "tags FROM " . $table_prefix . "tags ");
			$r->set_value("tag_id", $tag_id);
		}
	}
	$tag_id = $r->get_value("tag_id");
}

function delete_tag_data()
{
	global $r, $t, $db, $table_prefix;
	$tag_id = $r->get_value("tag_id");
	$sql  = " DELETE FROM ".$table_prefix."articles_tags ";
	$sql .= " WHERE tag_id=" . $db->tosql($tag_id, INTEGER);
	$db->query($sql);
}


?>
