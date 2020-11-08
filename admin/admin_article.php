<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_article.php                                        ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/articles_functions.php");
	include_once($root_folder_path . "includes/friendly_functions.php");
	include_once($root_folder_path . "includes/tabs_functions.php");	
	include_once($root_folder_path . "messages/" . $language_code . "/reviews_messages.php");

	check_admin_security("articles");

	$html_editor = get_setting_value($settings, "html_editor_articles", get_setting_value($settings, "html_editor", 1));
	$category_id = get_param("category_id");
	$article_id  = get_param("article_id");
	if (!strlen($category_id)) {
		header("Location: admin_articles_all.php");
		exit;
	} else {
		$sql  = " SELECT category_path, parent_category_id, allowed_rate ";
		$sql .= " FROM " . $table_prefix . "articles_categories ";
		$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$category_allowed_rate = $db->f("allowed_rate");
			$parent_category_id = $db->f("parent_category_id");
			$category_path = $db->f("category_path");
			if ($parent_category_id == 0) {
				$parent_category_id = $category_id;
			} else {
				$categories_ids = explode(",", $category_path);
				$parent_category_id = $categories_ids[1];
			}
		} else {
			header("Location: admin_articles_all.php");
			exit;
		}
	}

	$edit_fields = array();
	$sql  = " SELECT article_edit_fields,article_list_fields,article_details_fields,article_required_fields ";
	$sql .= " FROM " . $table_prefix . "articles_categories ";
	$sql .= " WHERE category_id=" . $db->tosql($parent_category_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$article_edit_fields = $db->f("article_edit_fields");
		if ($article_edit_fields) { $edit_fields = json_decode($article_edit_fields, true);} 
		$shown_fields = ",,".$db->f("article_list_fields").",".$db->f("article_details_fields") . ",,";
		$required_fields = ",," . $db->f("article_required_fields") . ",,";
	}

	$content_types = 
		array( 
			array(1, HTML_MSG), array(0, PLAIN_TEXT_MSG)
		);
	
	$article_fields = array(
		"article_date", "date_end", "article_title", "article_comment", "authors", "albums", "author_name", "author_email", 
		"author_url", "link_url", "download_url", "highlights", "short_description", "full_description",
		"stream_video", "youtube_video", "keywords", "tags", "notes"
	);
	$article_date_type = get_setting_value($edit_fields, "article_date_format");
	$date_end_type = get_setting_value($edit_fields, "date_end_format");
	if ($article_date_type == "date") {
		$article_date_format = $date_edit_format;
	} else {
		$article_date_format = $datetime_edit_format;
	}
	if ($date_end_type == "date") {
		$date_end_format = $date_edit_format;
	} else {
		$date_end_format = $datetime_edit_format;
	}

 	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_var("css_file", "../styles/" . $settings["style_name"] . ".css");
 	$t->set_file("main", "admin_article.html");

	$t->set_var("admin_articles_top_href", "admin_articles_top.php");
	$t->set_var("admin_article_href", "admin_article.php");
	$t->set_var("admin_upload_href",  "admin_upload.php");
	$t->set_var("admin_select_href",  "admin_select.php");
	$t->set_var("admin_articles_href", "admin_articles.php");
	$t->set_var("admin_authors_href", "admin_authors.php");
	$t->set_var("admin_albums_href", "admin_albums.php");
	$t->set_var("admin_tags_href", "admin_tags.php");
	$t->set_var("datetime_format", join("", $datetime_edit_format));
	$t->set_var("date_format", join("", $date_edit_format));
	$t->set_var("article_date_format", join("", $article_date_format));
	$t->set_var("date_end_format", join("", $date_end_format));

	$t->set_var("html_editor", $html_editor);
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", ARTICLE_MSG, CONFIRM_DELETE_MSG));

	$editors_list = 'hd,hl,sd,fd';
	add_html_editors($editors_list, $html_editor);

	$r = new VA_Record($table_prefix . "articles");

	if (get_param("apply")) {
		$r->redirect = false;
	}
	
	$r->return_page = "admin_articles.php";
	$r->add_where("article_id", INTEGER);
	$r->add_textbox("is_draft", INTEGER);
	$r->add_hidden("category_id", INTEGER);
	$r->add_hidden("authors", TEXT);
	$r->change_property("authors", BEFORE_SHOW, "article_authors_show");
	$r->change_property("authors", TRANSFER, false);
	$r->add_hidden("albums", TEXT);
	$r->change_property("albums", BEFORE_SHOW, "article_albums_show");
	$r->change_property("albums", TRANSFER, false);
	
	//Common info
	$r->add_textbox("article_order", INTEGER, ADMIN_ORDER_MSG);
	$r->change_property("article_order", REQUIRED, true);
	$r->add_textbox("total_views", INTEGER);
	$r->change_property("total_views", USE_IN_INSERT, false);
	$r->change_property("total_views", USE_IN_UPDATE, false);
	$r->add_textbox("article_title", TEXT, TITLE_MSG);
	$r->change_property("article_title", MAX_LENGTH, 255);
	$r->add_textbox("article_comment", TEXT, COMMENT_MSG);
	$r->change_property("article_comment", MAX_LENGTH, 255);
	$r->add_textbox("title_first", TEXT);
	$r->add_textbox("friendly_url", TEXT, FRIENDLY_URL_MSG);
	$r->change_property("friendly_url", USE_SQL_NULL, false);
	$r->change_property("friendly_url", BEFORE_VALIDATE, "validate_friendly_url");
	$r->change_property("friendly_url", REGEXP_MASK, FRIENDLY_URL_REGEXP);
	$r->change_property("friendly_url", REGEXP_ERROR, ALPHANUMERIC_ALLOWED_ERROR);
	$r->add_textbox("article_date", DATETIME, DATE_MSG);
	$r->change_property("article_date", VALUE_MASK, $article_date_format);
	$r->add_textbox("date_end", DATETIME, DATE_END_MSG);
	$r->change_property("date_end", VALUE_MASK, $date_end_format);
	$r->add_textbox("language_code", TEXT, LANGUAGE_CODE_MSG);
	$r->change_property("language_code", MAX_LENGTH, 2);
	$r->change_property("language_code", USE_SQL_NULL, false);
	//$r->add_textbox("article_template", TEXT, "Article Template");
	//$r->change_property("article_template", MAX_LENGTH, 255);

	$r->add_checkbox("is_remote_rss", INTEGER);
	$r->add_textbox("details_remote_url", TEXT);
	// author information
	$r->add_textbox("author_name", TEXT, AUTHOR_NAME_MSG);
	$r->change_property("author_name", MAX_LENGTH, 255);
	$r->add_textbox("author_email", TEXT, AUTHOR_EMAIL_MSG);
	$r->change_property("author_email", MAX_LENGTH, 255);
	$r->change_property("author_email", REGEXP_MASK, EMAIL_REGEXP);
	$r->add_textbox("author_url", TEXT, AUTHOR_URL_MSG);
	$r->change_property("author_url", MAX_LENGTH, 255);
	$r->add_hidden("author_remote_address", TEXT);
	$r->change_property("author_remote_address", USE_IN_INSERT, true);
	
	// statuses
	$statuses = get_db_values("SELECT * FROM " . $table_prefix . "articles_statuses WHERE is_shown=1", array(array("", "")));
	$r->add_select("status_id", INTEGER, $statuses, STATUS_MSG);
	$r->change_property("status_id", REQUIRED, true);
	$r->add_checkbox("allowed_rate", INTEGER);

	// language 
	$languages  = get_db_values("SELECT language_code,language_name FROM " . $table_prefix . "languages ORDER BY language_order, language_name ", array(array("", "")));
	$r->add_select("language_code", TEXT, $languages, LANGUAGE_MSG);
	
	// links 
	$r->add_textbox("link_url", TEXT, LINK_URL_MSG);
	$r->add_textbox("link_title", TEXT, TITLE_MSG);
	$r->add_textbox("download_url", TEXT, DOWNLOAD_URL_MSG);
	// $r->add_textbox("is_link_direct", TEXT, "Link is direct");

	// article editors and date edit
	$r->add_hidden("created_user_id", INTEGER);
	$r->change_property("created_user_id", USE_IN_INSERT, true);
	$r->add_hidden("updated_user_id", INTEGER);
	$r->change_property("updated_user_id", USE_IN_INSERT, true);
	$r->change_property("updated_user_id", USE_IN_UPDATE, true);
	$r->add_hidden("created_admin_id", INTEGER);
	$r->change_property("created_admin_id", USE_IN_INSERT, true);
	$r->add_hidden("updated_admin_id", INTEGER);
	$r->change_property("updated_admin_id", USE_IN_INSERT, true);
	$r->change_property("updated_admin_id", USE_IN_UPDATE, true);

	$r->add_textbox("date_added", DATETIME);
	$r->change_property("date_added", USE_IN_INSERT, true);
	$r->change_property("date_added", USE_IN_UPDATE, false);
	$r->add_textbox("date_updated", DATETIME);
	$r->change_property("date_updated", USE_IN_INSERT, true);
	$r->change_property("date_updated", USE_IN_UPDATE, true);
	
	// stream video
	$r->add_textbox("stream_video", TEXT, STREAM_VIDEO_MSG);
	$r->add_textbox("stream_video_width", INTEGER, WIDTH_MSG);
	$r->add_textbox("stream_video_height", INTEGER, HEIGHT_MSG);
	$r->add_textbox("stream_video_preview", TEXT, PREVIEW_VIDEO_MSG);

	// youtube video
	$r->add_textbox("youtube_video", TEXT, YOUTUBE_VIDEO_MSG);
	$r->add_textbox("youtube_video_width", INTEGER, WIDTH_MSG);
	$r->add_textbox("youtube_video_height", INTEGER, HEIGHT_MSG);

	// descs
	$r->add_checkbox("is_hot", INTEGER, SHOWN_ON_MAIN_PAGE_NOTE);
	$r->add_textbox("hot_order", INTEGER, HOT_TITLE.": ".HOT_TITLE);
	$r->add_textbox("hot_description", TEXT, HOT_DESCRIPTION_MSG);
	$r->add_textbox("highlights", TEXT, HIGHLIGHTS_MSG);
	$r->add_textbox("short_description", TEXT, SHORT_DESCRIPTION_MSG);
	$r->add_radio("is_html", INTEGER, $content_types);
	$r->change_property("is_html", DEFAULT_VALUE, 1);
	if ($html_editor){
		$r->change_property("is_html", SHOW, false);
	}
	$r->add_textbox("full_description", TEXT, FULL_DESCRIPTION_MSG);
	$r->add_textbox("keywords", TEXT, KEYWORDS_MSG);
	$r->add_textbox("notes", TEXT, NOTES_MSG);
	$r->add_hidden("tags", TEXT);
	$r->change_property("tags", BEFORE_SHOW, "article_tags_show");
	$r->change_property("tags", TRANSFER, false);

	// meta data
	$r->add_textbox("meta_title", TEXT);
	$r->add_textbox("meta_keywords", TEXT);
	$r->add_textbox("meta_description", TEXT);
	
	// stats 
	$r->add_hidden("total_votes",  INTEGER, TOTAL_VOTES_MSG);
	$r->change_property("total_votes", USE_IN_INSERT, true);
	$r->add_hidden("total_points", INTEGER, TOTAL_POINTS_MSG);
	$r->change_property("total_points", USE_IN_INSERT, true);
	$r->add_hidden("rating", INTEGER, RATING_MSG);
	$r->change_property("rating", USE_IN_INSERT, true);
	$r->change_property("rating", USE_IN_SELECT, true);
	$r->add_hidden("total_clicks", INTEGER, TOTAL_CLICKS_MSG);
	$r->change_property("total_clicks", USE_IN_INSERT, true);

	if ($html_editor){
		$r->set_value("is_html", 1);
	}
	for ($i = 0; $i < sizeof($article_fields); $i++) {
		$field_name = $article_fields[$i];
		$edit_field = get_setting_value($edit_fields, $field_name);
		if (!$edit_field && !strpos($shown_fields, "," . $field_name . ",")) {
			$r->change_property($field_name, SHOW, false);
			if ($field_name == "article_date") {
				$r->set_value($field_name, va_time());
			}
		}
		if (strpos($required_fields, "," . $field_name . ",")) {
			$r->change_property($field_name, REQUIRED, true);
		}
	}
	if (!strpos($shown_fields, ",link_url,")) {
		$r->change_property("link_title", SHOW, false);
	}
	

	if (!$r->parameters["stream_video"][SHOW]) {
		$r->change_property("stream_video_width", SHOW, false);
		$r->change_property("stream_video_height", SHOW, false);
		$r->change_property("stream_video_preview", SHOW, false);
	}

	if (!$r->parameters["youtube_video"][SHOW]) {
		$r->change_property("youtube_video_width", SHOW, false);
		$r->change_property("youtube_video_height", SHOW, false);
	}

	// check if we need to create draft
	if (!$article_id) {
		// check if draft empty already created by this admin and for this category to automatically use it for edit 
		$sql  = " SELECT a.article_id FROM " . $table_prefix ."articles a ";
		$sql .= " INNER JOIN " . $table_prefix ."articles_assigned aa ON a.article_id=aa.article_id ";
		$sql .= " WHERE aa.category_id=" . $db->tosql($category_id, INTEGER);
		$sql .= " AND a.is_draft=1 ";
		$sql .= " AND a.created_admin_id=" . $db->tosql(get_session("session_admin_id"), INTEGER);
		$sql .= " AND (a.article_title IS NULL OR a.article_title<>'') ";
		$db->query($sql);
		if ($db->next_record()) {
			$article_id = $db->f("article_id");
		}//*/

		if (!$article_id) {
			// new record (set default values and save draft)
			before_update(array("event" => BEFORE_INSERT));
			after_default();
			$r->set_value("is_draft", 1);
			$r->insert_record();

			// assign new article to category and update total articles value
			update_article_data(array("event" => AFTER_INSERT));
			$article_id = $r->get_value("article_id");
		}

		if ($article_id) {
			// redirect user to the article draft 
			$request_uri = get_request_uri();
			$request_uri .= (strpos($request_uri, "?")) ? "&" : "?";
			$request_uri .= "article_id=".urlencode($article_id);

			header("Location: " .$request_uri);
			exit;
		}
	}


	$r->set_event(BEFORE_INSERT, "before_update");	
	$r->set_event(BEFORE_UPDATE, "before_update");
	$r->set_event(AFTER_INSERT,  "update_article_data");
	$r->set_event(AFTER_UPDATE,  "update_article_data");
	$r->set_event(AFTER_DELETE,  "after_delete");
	$r->set_event(AFTER_DEFAULT, "after_default");
	$r->set_event(AFTER_SELECT, "after_select_article");

	$r->process();
	
	if (strpos($shown_fields, ",author_name,") || strpos($shown_fields, ",author_email,") || strpos($shown_fields, ",author_url,")) {
		$t->parse("author_title", false);
	} else {
		$t->set_var("author_title", "");
	}

	if (strlen($article_id)) {
		$t->set_var("save_button", UPDATE_BUTTON);
		$t->parse("delete", false);	
	} else {
		$t->set_var("save_button", ADD_NEW_MSG);
		$t->set_var("delete", "");	
	}

	// images
	$block = "all"; $va_module = "articles"; $_GET["va_module"] = "articles";
	include("./admin_block_images.php");

	$video_show = $r->parameters["stream_video"][SHOW] || $r->parameters["youtube_video"][SHOW];

	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }
	$tabs = array(
			"general" => array("title" => ADMIN_GENERAL_MSG), 
			"desc" => array("title" => DESCRIPTION_MSG), 
			"images" => array("title" => IMAGES_MSG), 
			"hot" => array("title" => HOT_TITLE), 
			"stream" => array("title" => VIDEO_MSG, "show" => $video_show), 
			"meta" => array("title" => META_DATA_MSG), 
			"rss" => array("title" => "RSS"), 
			"stats" => array("title" => ADMIN_STATISTIC_MSG), 

		);
	parse_tabs($tabs, $tab);

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");
	
	function before_update($params) {
		global $r, $db, $table_prefix, $html_editor;

		set_friendly_url();
		$event = isset($params["event"]) ? $params["event"] : "";
		if ($event == BEFORE_INSERT) {
			$r->set_value("is_draft", 0);
			$r->set_value("date_updated", va_time());
			$r->set_value("updated_admin_id", get_session("session_admin_id"));
			if ($html_editor){
				$r->set_value("is_html", 1);
			}
		} else {

			$remote_address = get_ip();
			$r->set_value("author_remote_address", $remote_address);
			if ($r->is_empty("total_views")) {
				$r->set_value("total_views", 0);
			}
			if ($html_editor){
				$r->set_value("is_html", 1);
			}
			$r->set_value("total_votes",  0);
			$r->set_value("total_points", 0);
			$r->set_value("rating", 0);
			$r->set_value("total_clicks", 0);
			$r->set_value("created_admin_id", get_session("session_admin_id"));
			$r->set_value("updated_admin_id", get_session("session_admin_id"));
			$r->set_value("date_added", va_time());
			$r->set_value("date_updated", va_time());
		}
		// set first letter for article title
		$article_title = trim($r->get_value("article_title"));
		$title_first = substr($article_title,0,1);
		$r->set_value("title_first", $title_first);

	}

	function update_article_data($params)
	{
		global $r, $db, $table_prefix, $category_id;
  
		$event = isset($params["event"]) ? $params["event"] : "";
		if ($event == AFTER_INSERT) {

			$article_id = $db->last_insert_id();
			$r->set_value("article_id", $article_id);

			$sql  = " SELECT MAX(article_order) FROM " . $table_prefix . "articles_assigned ";
			$sql .= " WHERE category_id = " . $db->tosql($category_id, INTEGER);
			$article_order = get_db_value($sql) + 1;
			
			$article_id = $r->get_value("article_id");
			$sql  = " INSERT INTO " . $table_prefix . "articles_assigned ";
			$sql .= " (article_id, category_id, article_order) VALUES (";
			$sql .= $db->tosql($article_id, INTEGER) . ",";
			$sql .= $db->tosql($category_id, INTEGER) . ",";
			$sql .= $db->tosql($article_order, INTEGER) . ")";
			$db->query($sql);
						
			$db->query("UPDATE " . $table_prefix . "articles_categories SET total_articles = total_articles + 1 WHERE category_id = " . $category_id);
		}

		$article_id = $r->get_value("article_id");
		// update authors
		$sql  = " DELETE FROM " . $table_prefix . "articles_authors "; 
		$sql .= " WHERE article_id=" . $db->tosql($article_id, INTEGER); 
		$db->query($sql);
		$authors = $r->get_value("authors");
		if ($authors) {
			$authors = json_decode($authors, true);
			foreach ($authors as $id => $author_data) {
				$author_id = $author_data["author_id"];
				$sql  = " INSERT INTO ".$table_prefix."articles_authors (article_id, author_id) VALUES ("; 
				$sql .= $db->tosql($article_id, INTEGER).", ";
				$sql .= $db->tosql($author_id, INTEGER).") ";
				$db->query($sql);
			}
		}
		// update albums
		$sql  = " DELETE FROM " . $table_prefix . "articles_albums "; 
		$sql .= " WHERE article_id=" . $db->tosql($article_id, INTEGER); 
		$db->query($sql);
		$albums = $r->get_value("albums");
		if ($albums) {
			$albums = json_decode($albums, true);
			foreach ($albums as $id => $album_data) {
				$album_id = $album_data["album_id"];
				$sql  = " INSERT INTO ".$table_prefix."articles_albums (article_id, album_id) VALUES ("; 
				$sql .= $db->tosql($article_id, INTEGER).", ";
				$sql .= $db->tosql($album_id, INTEGER).") ";
				$db->query($sql);
			}
		}
		// update tags
		$sql  = " DELETE FROM " . $table_prefix . "articles_tags "; 
		$sql .= " WHERE article_id=" . $db->tosql($article_id, INTEGER); 
		$db->query($sql);
		$tags = $r->get_value("tags");
		if ($tags) {
			$tags = json_decode($tags, true);
			foreach ($tags as $id => $tag_data) {
				$tag_id = $tag_data["tag_id"];
				$sql  = " INSERT INTO ".$table_prefix."articles_tags (article_id, tag_id) VALUES ("; 
				$sql .= $db->tosql($article_id, INTEGER).", ";
				$sql .= $db->tosql($tag_id, INTEGER).") ";
				$db->query($sql);
			}
		}

	}

	function after_delete() {
		global $r;		
		$article_id = $r->get_value("article_id");
		VA_Articles::delete($article_id);
	}
	
	function after_default() {
		global $r, $db, $table_prefix, $category_id,  $category_allowed_rate;		
		$sql  = " SELECT MAX(article_order) FROM " . $table_prefix . "articles_assigned ";
		$sql .= " WHERE category_id = " . $db->tosql($category_id, INTEGER);
		$article_order = get_db_value($sql) + 1;
		$r->set_value("article_order", $article_order);
		$r->set_value("article_date", va_time());
		$r->set_value("status_id", 1);
		$r->set_value("allowed_rate", $category_allowed_rate);
		$r->set_value("is_html", 1);		
	}

function after_select_article()
{
	global $r, $db, $table_prefix;
	$article_id = $r->get_value("article_id");
	if ($article_id) {
		$authors = array();
		$sql  = " SELECT a.* FROM (" . $table_prefix . "authors a ";
		$sql .= " INNER JOIN " . $table_prefix . "articles_authors aa ON aa.author_id=a.author_id) ";
		$sql .= " WHERE aa.article_id=" . $db->tosql($article_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$author_id = $db->f("author_id");
			$authors[] = array_merge($db->Record, array("id" => $author_id));
		}
		$r->set_value("authors", json_encode($authors));

		$albums = array();
		$sql  = " SELECT a.* FROM (" . $table_prefix . "albums a ";
		$sql .= " INNER JOIN " . $table_prefix . "articles_albums aa ON aa.album_id=a.album_id) ";
		$sql .= " WHERE aa.article_id=" . $db->tosql($article_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$album_id = $db->f("album_id");
			$albums[] = array_merge($db->Record, array("id" => $album_id));
		}
		$r->set_value("albums", json_encode($albums));

		$tags = array();
		$sql  = " SELECT a.* FROM (" . $table_prefix . "tags a ";
		$sql .= " INNER JOIN " . $table_prefix . "articles_tags aa ON aa.tag_id=a.tag_id) ";
		$sql .= " WHERE aa.article_id=" . $db->tosql($article_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$tag_id = $db->f("tag_id");
			$tags[] = array_merge($db->Record, array("id" => $tag_id));
		}
		$r->set_value("tags", json_encode($tags));

	}
}


function article_authors_show()
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

function article_albums_show()
{
	global $r, $t;
	$albums = $r->get_value("albums");
	if ($albums) {
		$albums = json_decode($albums, true);
		foreach ($albums as $id => $album_data) {
			$album_id = $album_data["album_id"];
			$album_name = $album_data["album_name"];
			$t->set_var("album_id", htmlspecialchars($album_id));
			$t->set_var("album_name", htmlspecialchars($album_name));
			$t->parse_to("album_template", "selected_albums", true);
		}
	}

	// parse template
	$t->set_var("album_id", "[album_id]");
	$t->set_var("album_name", "[album_name]");
	$t->parse("album_template", false);
}

function article_tags_show()
{
	global $r, $t;
	$tags = $r->get_value("tags");
	if ($tags) {
		$tags = json_decode($tags, true);
		foreach ($tags as $id => $tag_data) {
			$tag_id = $tag_data["tag_id"];
			$tag_name = $tag_data["tag_name"];
			$t->set_var("tag_id", htmlspecialchars($tag_id));
			$t->set_var("tag_name", htmlspecialchars($tag_name));
			$t->parse_to("tag_template", "selected_tags", true);
		}
	}

	// parse template
	$t->set_var("tag_id", "[tag_id]");
	$t->set_var("tag_name", "[tag_name]");
	$t->parse("tag_template", false);
}


?>