<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_album.php                                          ***
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

	check_admin_security("");

	$operation = get_param("operation");
	$authors = get_param("authors");
	$win_type = get_param("win_type");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_album.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_lookup_tables_href", "admin_lookup_tables.php");
	$t->set_var("admin_albums_href", "admin_albums.php");
	$t->set_var("admin_album_href", "admin_album.php");
	$t->set_var("admin_authors_href", "admin_authors.php");
	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", ALBUM_MSG, CONFIRM_DELETE_MSG));
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

	$r = new VA_Record($table_prefix . "albums");
	$r->return_page = "admin_albums.php";

	$r->add_where("album_id", INTEGER);
	$r->add_hidden("authors", TEXT);
	$r->change_property("authors", BEFORE_SHOW, "album_authors_show");
	$r->change_property("authors", TRANSFER, false);

	$r->add_textbox("album_name", TEXT, NAME_MSG);
	$r->change_property("album_name", REQUIRED, true);
	$r->add_textbox("name_first", TEXT);
	$r->add_textbox("album_type", TEXT);
	$r->add_textbox("album_date", DATETIME, DATE_MSG);
	$r->change_property("album_date", VALUE_MASK, $date_edit_format);
	$r->add_textbox("friendly_url", TEXT, FRIENDLY_URL_MSG);
	$r->change_property("friendly_url", USE_SQL_NULL, false);
	$r->change_property("friendly_url", BEFORE_VALIDATE, "validate_friendly_url");
	$r->change_property("friendly_url", REGEXP_MASK, FRIENDLY_URL_REGEXP);
	$r->change_property("friendly_url", REGEXP_ERROR, ALPHANUMERIC_ALLOWED_ERROR);
	$r->add_textbox("short_description", TEXT, SHORT_DESCRIPTION_MSG);
	$r->add_textbox("full_description", TEXT, FULL_DESCRIPTION_MSG);

	$r->add_textbox("image_tiny", TEXT, IMAGE_TINY_MSG);
	$r->add_textbox("image_small", TEXT, IMAGE_SMALL_MSG);
	$r->add_textbox("image_large", TEXT, IMAGE_LARGE_MSG);
	$r->add_textbox("image_super", TEXT, IMAGE_SUPER_MSG);

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

	$r->events[BEFORE_INSERT] = "set_album_data";
	$r->events[BEFORE_UPDATE] = "set_album_data";
	$r->events[AFTER_INSERT] = "update_album_data";
	$r->events[AFTER_UPDATE] = "update_album_data";
	$r->events[AFTER_SELECT] = "after_select_album";

	$r->process();

	$authors = $r->get_value("authors");

	if ($win_type != "popup") {
		$custom_breadcrumb = array(
			"admin_global_settings.php" => SETTINGS_MSG,
			"admin_lookup_tables.php" => STATIC_TABLES_MSG,
			$r->get_return_url() => ALBUMS_MSG,
			"admin_album.php" => EDIT_MSG,
		);

		include_once("./admin_header.php");
		include_once("./admin_footer.php");
	}

	$t->pparse("main");

function set_album_data($params)
{
	global $r, $db, $table_prefix;	

	$event = isset($params["event"]) ? $params["event"] : "";
	if ($event == BEFORE_INSERT) {
		if ($db->DBType == "postgre") {
			$album_id = get_db_value(" SELECT NEXTVAL('seq_" . $table_prefix . "albums ') ");
			$r->change_property("album_id", USE_IN_INSERT, true);
			$r->set_value("album_id", $album_id);
		}
	}
	set_friendly_url();
	// set first letter for album name
	$album_name = trim($r->get_value("album_name"));
	$name_first = substr($album_name,0,1);
	$r->set_value("name_first", $name_first);
}

function update_album_data($params)
{
	global $r, $t, $db, $table_prefix;

	$event = isset($params["event"]) ? $params["event"] : "";
	if ($event == AFTER_INSERT) {
		if ($db->DBType == "mysql") {
			$album_id = get_db_value(" SELECT LAST_INSERT_ID() ");
			$r->set_value("album_id", $album_id);
		} elseif ($db->DBType == "access") {
			$album_id = get_db_value(" SELECT @@IDENTITY ");
			$r->set_value("album_id", $album_id);
		} elseif ($db->DBType == "db2") {
			$album_id = get_db_value(" SELECT PREVVAL FOR seq_" . $table_prefix . "albums FROM " . $table_prefix . "albums ");
			$r->set_value("album_id", $album_id);
		}
	}
	$album_id = $r->get_value("album_id");
	$sql  = " DELETE FROM " . $table_prefix . "albums_authors "; 
	$sql .= " WHERE album_id=" . $db->tosql($album_id, INTEGER); 
	$db->query($sql);
	$authors = $r->get_value("authors");
	if ($authors) {
		$authors = json_decode($authors, true);
		foreach ($authors as $id => $author_data) {
			$author_id = $author_data["author_id"];
			$sql  = " INSERT INTO ".$table_prefix."albums_authors (album_id, author_id) VALUES ("; 
			$sql .= $db->tosql($album_id, INTEGER).", ";
			$sql .= $db->tosql($author_id, INTEGER).") ";
			$db->query($sql);
		}
	}
}

function after_select_album()
{
	global $r, $db, $table_prefix;
	$album_id = $r->get_value("album_id");
	if ($album_id) {
		$authors = array();
		$sql  = " SELECT a.* FROM (" . $table_prefix . "authors a ";
		$sql .= " INNER JOIN " . $table_prefix . "albums_authors aa ON aa.author_id=a.author_id) ";
		$sql .= " WHERE aa.album_id=" . $db->tosql($album_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$author_id = $db->f("author_id");
			$authors[] = array_merge($db->Record, array("id" => $author_id));
		}
		$r->set_value("authors", json_encode($authors));
	}
}

function album_authors_show()
{
	global $r, $t;
	$authors = $r->get_value("authors");
	if ($authors) {
		$authors = json_decode($authors, true);
		foreach ($authors as $id => $author_data) {
			$author_id = $author_data["author_id"];
			$author_name = $author_data["author_name"];
			$t->set_var("author_id", htmlspecialchars($author_id));
			$t->set_var("author_name", htmlspecialchars($author_name));
			$t->parse_to("author_template", "selected_authors", true);
		}
	}

	// parse template
	$t->set_var("author_id", "[author_id]");
	$t->set_var("author_name", "[author_name]");
	$t->parse("author_template", false);
}

?>
