<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_author.php                                         ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/friendly_functions.php");
	include_once($root_folder_path . "includes/tabs_functions.php");

	check_admin_security("");

	$win_type = get_param("win_type");
	$operation = get_param("operation");
	$sites = get_param("sites");
	$languages = get_param("languages");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_author.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_lookup_tables_href", "admin_lookup_tables.php");
	$t->set_var("admin_authors_href", "admin_authors.php");
	$t->set_var("admin_author_href", "admin_author.php");
	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", AUTHORS_MSG, CONFIRM_DELETE_MSG));

	$full_image_url = get_setting_value($settings, "full_image_url", 0);
	$site_url_path = get_setting_value($settings, "site_url", "");
	if ($full_image_url){
		$t->set_var("site_url", $site_url_path);					
	} else {
		$t->set_var("site_url", "");					
	}

	$countries = get_db_values("SELECT country_id,country_name FROM " . $table_prefix . "countries ORDER BY country_order, country_name ", array(array("", SELECT_COUNTRY_MSG)));

	$r = new VA_Record($table_prefix . "authors");
	$r->return_page = "admin_authors.php";
	$r->add_where("author_id", INTEGER);

	$r->add_textbox("author_name", TEXT, AUTHOR_NAME_MSG);
	$r->change_property("author_name", REQUIRED, true);
	$r->add_textbox("other_name", TEXT);
	$r->add_textbox("extra_name", TEXT);

	$r->add_select("author_country_id", INTEGER, $countries, COUNTRY_FIELD);
	$r->add_textbox("author_country_code", TEXT);

	$r->add_textbox("friendly_url", TEXT, FRIENDLY_URL_MSG);
	$r->change_property("friendly_url", USE_SQL_NULL, false);
	$r->change_property("friendly_url", BEFORE_VALIDATE, "validate_friendly_url");
	$r->change_property("friendly_url", REGEXP_MASK, FRIENDLY_URL_REGEXP);
	$r->change_property("friendly_url", REGEXP_ERROR, ALPHANUMERIC_ALLOWED_ERROR);
	$r->add_textbox("short_description", TEXT, SHORT_DESCRIPTION_MSG);
	$r->add_textbox("full_description", TEXT, FULL_DESCRIPTION_MSG);

	$r->add_textbox("image_tiny", TEXT, IMAGE_TINY_MSG);
	$r->add_textbox("image_small", TEXT, IMAGE_SMALL_MSG);
	//$r->add_textbox("image_small_alt", TEXT, IMAGE_SMALL_ALT_MSG);
	$r->add_textbox("image_large", TEXT, IMAGE_LARGE_MSG);
	//$r->add_textbox("image_large_alt", TEXT, IMAGE_LARGE_ALT_MSG);
	$r->add_textbox("image_super", TEXT, IMAGE_SUPER_MSG);

	$r->add_textbox("name_first", TEXT);
	$r->add_textbox("middle_first", TEXT);
	$r->add_textbox("last_first", TEXT);
	$r->add_textbox("other_first", TEXT);

	$r->add_hidden("languages", TEXT);
	$r->add_checkbox("languages_all", INTEGER);
	$r->change_property("languages_all", DEFAULT_VALUE, 1);

	$r->add_hidden("sites", TEXT);
	$r->add_checkbox("sites_all", INTEGER);
	$r->change_property("sites_all", DEFAULT_VALUE, 1);

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

	$r->events[BEFORE_INSERT] = "before_update";
	$r->events[BEFORE_UPDATE] = "before_update";
	$r->events[AFTER_INSERT] = "update_author_data";
	$r->events[AFTER_UPDATE] = "update_author_data";
	$r->events[AFTER_SELECT] = "select_author_data";

	$r->process();

	if ($win_type != "popup") {

		$custom_breadcrumb = array(
			"admin_global_settings.php" => SETTINGS_MSG,
			"admin_lookup_tables.php" => STATIC_TABLES_MSG,
			$r->get_return_url() => AUTHORS_MSG,
			"admin_author.php" => EDIT_MSG,
		);

		include_once("./admin_header.php");
		include_once("./admin_footer.php");
	}

	// show languages to select
	$languages = array();
	$selected_languages = explode(",", $r->get_value("languages"));
	$sql = " SELECT language_code, language_name FROM " . $table_prefix . "languages ORDER BY language_order, language_name ";
	$db->query($sql);
	while ($db->next_record())	{
		$language_code = $db->f("language_code");
		$language_name = $db->f("language_name");
		$languages[$language_code] = $language_name;
		$t->set_var("language_code", $language_code);
		$t->set_var("language_name", $language_name);
		if (in_array($language_code, $selected_languages)) {
			$t->parse("selected_languages", true);
		} else {
			$t->parse("available_languages", true);
		}
	}

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
		"general" => array("title" => ADMIN_GENERAL_MSG),
		"desc"    => array("title" => DESCRIPTION_MSG),
		"languages" => array("title" => LANGUAGE_TITLE),
		"sites"   => array("title" => ADMIN_SITES_MSG, "show" => $sitelist),
	);
	parse_tabs($tabs);


	$t->pparse("main");

function before_update($params)
{
	global $r, $db, $table_prefix, $sitelist;
	$event = isset($params["event"]) ? $params["event"] : "";
	if ($event == BEFORE_INSERT) {
		if ($db->DBType == "postgre") {
			$author_id = get_db_value(" SELECT NEXTVAL('seq_" . $table_prefix . "authors ') ");
			$r->change_property("author_id", USE_IN_INSERT, true);
			$r->set_value("author_id", $author_id);
		}
	}
	set_friendly_url();
	// set first letter
	$name_first = ""; $middle_first = ""; $last_first = ""; $other_first = ""; $extra_first = "";
	$name_second = ""; $other_second = ""; $extra_second = "";
	$author_name = $r->get_value("author_name");
	$name_parts = preg_split("/\s+/", $author_name);
	$parts_total = count($name_parts);
	if (function_exists("mb_substr")) {
		$name_first = mb_substr($name_parts[0],0,1,"UTF-8");
		if (mb_strlen($name_parts[0]) > 1) {
			$name_second = mb_substr($name_parts[0],1,1,"UTF-8");
		}
	} else {
		$name_first = substr($name_parts[0],0,1);
		if (strlen($name_parts[0]) > 1) {
			$name_second = substr($name_parts[0],1,1,"UTF-8");
		}
	}
	if($parts_total > 1) { 
		if (function_exists("mb_substr")) {
			$last_first = mb_substr($name_parts[$parts_total-1],0,1,"UTF-8"); 
		} else {
			$last_first = substr($name_parts[$parts_total-1],0,1); 
		}
	}
	if($parts_total > 2) { 
		if (function_exists("mb_substr")) {
			$middle_first = mb_substr($name_parts[1],0,1,"UTF-8"); 
		} else {
			$middle_first = substr($name_parts[1],0,1); 
		}
	}
	
	$other_name = $r->get_value("other_name");
	if ($other_name) {
		if (function_exists("mb_substr")) {
			$other_first = mb_substr($other_name,0,1,"UTF-8");
			if (mb_strlen($other_name) > 1) {
				$other_second = trim(mb_substr($other_name,1,1,"UTF-8"));
			}
		} else {
			$other_first = substr($other_name,0,1);
			if (strlen($other_name) > 1) {
				$other_second = trim(substr($other_name,1,1,"UTF-8"));
			}
		}
	}
	$extra_name = $r->get_value("extra_name");
	if ($extra_name) {
		if (function_exists("mb_substr")) {
			$extra_first = mb_substr($extra_name,0,1,"UTF-8");
			if (mb_strlen($extra_name) > 1) {
				$extra_second = trim(mb_substr($extra_name,1,1,"UTF-8"));
			}
		} else {
			$extra_first = substr($extra_name,0,1);
			if (strlen($extra_name) > 1) {
				$extra_second = trim(substr($extra_name,1,1,"UTF-8"));
			}
		}
	}

	$r->set_value("name_first", $name_first);
	$r->set_value("name_second", $name_second);
	$r->set_value("middle_first", $middle_first);
	$r->set_value("last_first", $last_first);
	$r->set_value("other_first", $other_first);
	$r->set_value("other_second", $other_second);
	$r->set_value("extra_first", $extra_first);
	$r->set_value("extra_second", $extra_second);

	if (!$r->is_empty("author_country_id")) {
		$sql = " SELECT country_code FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($r->get_value("author_country_id"), INTEGER);
		$r->set_value("author_country_code", get_db_value($sql));
	}

	if (!$sitelist) {
		$r->set_value("sites_all", 1);
	}
}

function update_author_data($params)
{
	global $r, $t, $db, $table_prefix, $sitelist, $selected_sites, $selected_languages;

	$event = isset($params["event"]) ? $params["event"] : "";
	if ($event == AFTER_INSERT) {
		if ($db->DBType == "mysql") {
			$author_id = get_db_value(" SELECT LAST_INSERT_ID() ");
			$r->set_value("author_id", $author_id);
		} elseif ($db->DBType == "access") {
			$author_id = get_db_value(" SELECT @@IDENTITY ");
			$r->set_value("author_id", $author_id);
		} elseif ($db->DBType == "db2") {
			$author_id = get_db_value(" SELECT PREVVAL FOR seq_" . $table_prefix . "authors FROM " . $table_prefix . "authors ");
			$r->set_value("author_id", $author_id);
		}
	}

	$author_id = $r->get_value("author_id");

	$selected_languages = explode(",", $r->get_value("languages"));
	$db->query("DELETE FROM " . $table_prefix . "authors_languages WHERE author_id=" . $db->tosql($author_id, INTEGER));
	for ($st = 0; $st < sizeof($selected_languages); $st++) {
		$language_code = $selected_languages[$st];
		if (strlen($language_code)) {
			$sql  = " INSERT INTO " . $table_prefix . "authors_languages (author_id, language_code) VALUES (";
			$sql .= $db->tosql($author_id, INTEGER) . ", ";
			$sql .= $db->tosql($language_code, TEXT) . ") ";
			$db->query($sql);
		}
	}

	$selected_sites = explode(",", $r->get_value("sites"));
	if ($sitelist) {
		$db->query("DELETE FROM " . $table_prefix . "authors_sites WHERE author_id=" . $db->tosql($author_id, INTEGER));
		for ($st = 0; $st < sizeof($selected_sites); $st++) {
			$site_id = $selected_sites[$st];
			if (strlen($site_id)) {
				$sql  = " INSERT INTO " . $table_prefix . "authors_sites (author_id, site_id) VALUES (";
				$sql .= $db->tosql($author_id, INTEGER) . ", ";
				$sql .= $db->tosql($site_id, INTEGER) . ") ";
				$db->query($sql);
			}
		}
	}
}

function select_author_data()
{
	global $r, $db, $table_prefix;
	$author_id = $r->get_value("author_id");

	$selected_languages = array();
	if ($author_id) {
		$sql  = " SELECT language_code FROM " . $table_prefix . "authors_languages ";
		$sql .= " WHERE author_id=" . $db->tosql($author_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$selected_languages[] = $db->f("language_code");
		}
	}
	$r->set_value("languages", implode(",", $selected_languages));

	$selected_sites = array();
	if ($author_id) {
		$sql  = " SELECT site_id FROM " . $table_prefix . "authors_sites ";
		$sql .= " WHERE author_id=" . $db->tosql($author_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$selected_sites[] = $db->f("site_id");
		}
	}
	$r->set_value("sites", implode(",", $selected_sites));
}

?>
