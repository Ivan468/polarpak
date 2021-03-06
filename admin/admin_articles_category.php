<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_articles_category.php                              ***
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
	include_once($root_folder_path . "includes/articles_functions.php");
	include_once($root_folder_path . "includes/tabs_functions.php");
	include_once($root_folder_path . "includes/sites_table.php");
	include_once($root_folder_path . "includes/access_table.php");	

	check_admin_security("articles");

	// check for field update field
	$article_settings_field = false;
	$fields = $db->get_fields($table_prefix."articles_categories");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "article_settings") {
			$article_settings_field = true;
		}
	}
	if (!$article_settings_field) {
		if ($db->DBType == "mysql") {
			$sql = "ALTER TABLE " . $table_prefix . "articles_categories ADD COLUMN article_settings TEXT";
		} else if ($db->DBType == "access") {
			$sql = "ALTER TABLE " . $table_prefix . "articles_categories ADD COLUMN article_settings LONGTEXT";
		} else {
			$sql = "ALTER TABLE " . $table_prefix . "articles_categories ADD COLUMN article_settings TEXT";
		}
		$db->query($sql);
	}
	// end field check
	
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_articles_category.html");

	$html_editor = get_setting_value($settings, "html_editor_articles", get_setting_value($settings, "html_editor", 1));
	$t->set_var("html_editor", $html_editor);		

	$t->set_var("css_file", "../styles/" . $settings["style_name"] . ".css");

	$t->set_var("admin_articles_top_href",      "admin_articles_top.php");
	$t->set_var("admin_articles_category_href", "admin_articles_category.php");
	$t->set_var("admin_select_href",   "admin_select.php");
	$t->set_var("admin_upload_href",   "admin_upload.php");
	$t->set_var("admin_articles_href", "admin_articles.php");
	$t->set_var("admin_category_select_href", "admin_category_select.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", CATEGORY_MSG, CONFIRM_DELETE_MSG));
	$t->set_var("datetime_format", join("", $datetime_edit_format));
	$t->set_var("date_format", join("", $date_edit_format));

	$full_image_url = get_setting_value($settings, "full_image_url", 0);
	$site_url_path = get_setting_value($settings, "site_url", "");
	if ($full_image_url){
		$t->set_var("image_site_url", $site_url_path);					
	} else {
		$t->set_var("image_site_url", "");					
	}
		
	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }
	$category_id = get_param("category_id");
	$parent_category_id = get_param("parent_category_id");
	if (!$parent_category_id) $parent_category_id = 0;
	$top_id = ""; $top_name = "";
	if ($parent_category_id) {
		$sql  = " SELECT category_path FROM " . $table_prefix . "articles_categories ";
		$sql .= " WHERE category_id = " . $db->tosql($parent_category_id, INTEGER);
		$parent_path = get_db_value($sql);
		if ($parent_path) {
			$tmp = explode(",", $parent_path);
			if (isset($tmp[1]) && $tmp[1]) {
				$top_id = $tmp[1];
			} else {
				$top_id = $parent_category_id;
			}
		} else {
			$parent_category_id = 0;
		}
	}
	$sql  = " SELECT category_name FROM " . $table_prefix . "articles_categories ";
	$sql .= " WHERE category_id=" . $db->tosql($top_id, INTEGER);
	$top_name = get_translation(get_db_value($sql));

	$t->set_var("top_id", $top_id);
	$t->set_var("top_name", $top_name);
	
	if ($parent_category_id > 0) {
		$return_page = "admin_articles.php?category_id=" . $parent_category_id;
	} else {
		$return_page = "admin_articles_top.php";
	}
	$editors_list = 'sd,fd';
	add_html_editors($editors_list, $html_editor);

	$article_fields = array(
		"article_date", "date_end", "article_title", "article_comment", 
		"authors", "albums", "author_name", "author_email","author_url", "link_url", 
		"download_url", "highlights", "short_description", "full_description",
		"image_tiny", "image_small", "image_large", "image_super", 
		"youtube_video", "stream_video","keywords", "tags", "notes",
	);
	
	$r = new VA_Record($table_prefix . "articles_categories");
	$r->return_page = $return_page;
	if (get_param("apply")) {
		$r->redirect = false;
	}
	
	$r->add_where("category_id", INTEGER);
	$r->add_textbox("alias_category_id", INTEGER);
	if ($parent_category_id == 0) {
		$r->change_property("alias_category_id", SHOW, false);
	}
	$r->add_textbox("category_order", INTEGER, CATEGORY_ORDER_MSG);
	$r->change_property("category_order", REQUIRED, true);
	$r->add_textbox("total_views", INTEGER);
	$r->change_property("total_views", USE_IN_INSERT, false);
	$r->change_property("total_views", USE_IN_UPDATE, false);
	$r->add_textbox("category_name", TEXT, CATEGORY_NAME_MSG);
	$r->change_property("category_name", REQUIRED, true);
	$r->change_property("category_name", MAX_LENGTH, 255);
	$r->add_textbox("friendly_url", TEXT, FRIENDLY_URL_MSG);
	$r->change_property("friendly_url", USE_SQL_NULL, false);
	$r->change_property("friendly_url", BEFORE_VALIDATE, "validate_friendly_url");
	$r->change_property("friendly_url", REGEXP_MASK, FRIENDLY_URL_REGEXP);
	$r->change_property("friendly_url", REGEXP_ERROR, ALPHANUMERIC_ALLOWED_ERROR);

	$r->add_checkbox("is_rss", INTEGER);
	$r->add_checkbox("rss_on_breadcrumb", INTEGER);
	$r->add_checkbox("rss_on_list", INTEGER);
	$r->add_textbox("rss_limit", INTEGER);

	$r->add_checkbox("is_remote_rss", INTEGER);
	$r->add_textbox("remote_rss_url", TEXT);
	$r->add_textbox("remote_rss_date_updated", DATETIME);
	$r->change_property("remote_rss_date_updated", VALUE_MASK, $datetime_edit_format);
	$r->add_textbox("remote_rss_ttl", INTEGER);
	$r->add_textbox("remote_rss_refresh_rate", INTEGER);

	$order_columns = array(
		array("", ""), array("article_order", ORDER_COLUMN_MSG),
		array("article_date", DATE_MSG), array("date_end", DATE_END_MSG),
		array("article_title", TITLE_MSG), array("author_name", AUTHOR_NAME_MSG),
		array("date_added", DATE_ADDED_MSG), array("date_updated", DATE_UPDATED_MSG)
	);
	$r->add_select("articles_order_column", TEXT, $order_columns);
	$order_directions = array(array("", ""), array("ASC", ASC_MSG), array("DESC", DESC_MSG));
	$r->add_select("articles_order_direction", TEXT, $order_directions);

	$r->add_textbox("article_edit_fields", TEXT);
	$r->add_textbox("article_list_fields", TEXT);
	$r->add_textbox("article_details_fields", TEXT);
	$r->add_textbox("article_required_fields", TEXT);
	$r->add_textbox("article_settings", TEXT);

	$r->add_checkbox("allowed_rate", INTEGER);
	$r->add_textbox("short_description", TEXT);
	$r->add_textbox("full_description", TEXT);

	$r->add_textbox("image_small", TEXT);
	$r->add_textbox("image_small_alt", TEXT);
	$r->add_textbox("image_large", TEXT);
	$r->add_textbox("image_large_alt", TEXT);

	$r->add_textbox("meta_title", TEXT);
	$r->add_textbox("meta_keywords", TEXT);
	$r->add_textbox("meta_description", TEXT);

	//-- parent items
	$items = array();
	if ($parent_category_id) {
		$sql  = " SELECT * FROM " . $table_prefix . "articles_categories ";
		$sql .= " WHERE category_path LIKE '0," . $top_id .",%' ";
		$sql .= " ORDER BY category_path, category_order ";
		$db->query($sql);
		while ($db->next_record()) {
			$list_id        = $db->f("category_id");
			$list_parent_id = $db->f("parent_category_id");
			$list_title     = get_translation($db->f("category_name"));
			
			$categories[$list_id]["category_name"] = $list_title;
			$categories[$list_id]["category_path"] = $db->f("category_path");
			$categories[$list_parent_id]["subs"][] = $list_id;
			$parent_categories[$list_id] = $list_parent_id;
		}
		$sql  = " SELECT category_name  FROM " . $table_prefix ."articles_categories ";
		$sql .= " WHERE category_id=" . $db->tosql($top_id, INTEGER);
		$top_name = get_translation(get_db_value($sql));
		$items[] = array($top_id, $top_name);
		build_category_list($top_id);			
	}
	$r->add_textbox("parent_category_id", INTEGER, PARENT_CATEGORY_MSG);
	$r->change_property("parent_category_id", REQUIRED, true);
	$r->change_property("parent_category_id", USE_SQL_NULL, false);
	// parent category default value
	$parent_category_id = get_param("parent_category_id");
	if (!strlen($parent_category_id)) { $parent_category_id = 0; }
	$r->change_property("parent_category_id", DEFAULT_VALUE, $parent_category_id);
	if ($parent_category_id) {
		$r->change_property("parent_category_id", SHOW, true);
	} else {
		$r->change_property("parent_category_id", SHOW, false);
	}

	$r->add_textbox("category_path", TEXT);
	$r->add_textbox("language_code", TEXT, LANGUAGE_CODE_MSG);
	$r->change_property("language_code", MAX_LENGTH, 2);
	$r->change_property("language_code", USE_SQL_NULL, false);
	$r->add_textbox("total_articles", INTEGER);
	$r->change_property("total_articles", USE_IN_INSERT, true);
	$r->change_property("total_articles", USE_IN_UPDATE, false);
	$r->add_textbox("total_subcategories", INTEGER);
	$r->change_property("total_subcategories", USE_IN_INSERT, true);
	$r->change_property("total_subcategories", USE_IN_UPDATE, false);

	$r->add_checkbox("access_level", INTEGER);
	$r->add_checkbox("guest_access_level", INTEGER);
	$r->add_checkbox("admin_access_level", INTEGER);
	$r->add_checkbox("sites_all", INTEGER);

	// fields
	for ($i = 0; $i < sizeof($article_fields); $i++) {
		$field_name = $article_fields[$i];
		$edit_field_name = "edit_".$field_name;
		$r->add_checkbox($edit_field_name, TEXT, $date_formats);
		$r->change_property($edit_field_name, USE_IN_SELECT, false);
		$r->change_property($edit_field_name, USE_IN_INSERT, false);
		$r->change_property($edit_field_name, USE_IN_UPDATE, false);
	}
	$r->change_property("edit_article_date", DEFAULT_VALUE, 1);
	$r->change_property("edit_article_title", DEFAULT_VALUE, 1);
	$r->change_property("edit_image_tiny", DEFAULT_VALUE, 1);
	$r->change_property("edit_image_small", DEFAULT_VALUE, 1);
	$r->change_property("edit_image_large", DEFAULT_VALUE, 1);
	$r->change_property("edit_image_super", DEFAULT_VALUE, 1);
	$r->change_property("edit_short_description", DEFAULT_VALUE, 1);
	$r->change_property("edit_full_description", DEFAULT_VALUE, 1);

	// fields format and additional settings
	$date_formats = array(
		array("datetime", join("", $datetime_edit_format)),
		array("date", join("", $date_edit_format)),
	);
	$r->add_select("article_date_format", TEXT, $date_formats);
	$r->change_property("article_date_format", USE_IN_SELECT, false);
	$r->change_property("article_date_format", USE_IN_INSERT, false);
	$r->change_property("article_date_format", USE_IN_UPDATE, false);
	$r->add_select("date_end_format", TEXT, $date_formats);
	$r->change_property("date_end_format", USE_IN_SELECT, false);
	$r->change_property("date_end_format", USE_IN_INSERT, false);
	$r->change_property("date_end_format", USE_IN_UPDATE, false);
	$r->add_checkbox("authors_filter", INTEGER);
	$r->change_property("authors_filter", USE_IN_SELECT, false);
	$r->change_property("authors_filter", USE_IN_INSERT, false);
	$r->change_property("authors_filter", USE_IN_UPDATE, false);

	$rs = new VA_Record("", "fb");
	$rs->add_checkbox("fb_active", TEXT);
	$rs->add_textbox("fb_app_id", TEXT, "fb:app_id");
	$rs->add_textbox("fb_og_type", TEXT, "og:type");
	$rs->add_textbox("fb_og_title", TEXT, "og:title");
	$rs->add_textbox("fb_og_image", TEXT, "og:image");
	$rs->add_textbox("fb_og_site_name", TEXT, "og:site_name");
	$rs->add_textbox("fb_og_description", TEXT, "og:description");
	$rs->get_form_parameters();

	if ($category_id) {
		$action_title = EDIT_CATEGORY_MSG;
	} else {
		$action_title = ADD_CATEGORY_MSG;
	}
	$t->set_var("action_title", $action_title);

	if ($category_id) {
		$t->set_var("live_href", $root_folder_path . "articles.php?category_id=" . $category_id);
		$t->parse("view_live");
	}
		
	$access_table = new VA_Access_Table($settings["admin_templates_dir"], "access_table.html");
	$access_table->set_access_levels(
		array(
			1 => array(VIEW_MSG, VIEW_CATEGORY_IN_THE_LIST_MSG), 
			2 => array(ACCESS_LIST_MSG, ACCESS_CATEGORY_DETAILS_AND_ITEMS_LIST_MSG),
			4 => array(ACCESS_DETAILS_MSG, ACCESS_CATEGORY_ITEMS_DETAILS_MSG),
			8 => array(POST_MSG, ALLOW_TO_POST_NEW_ITEMS_TO_CATEGORY_MSG)
		)
	);
	$access_table->set_tables("articles_categories", "articles_categories_types",  "articles_categories_subscriptions", "category_id", "category_path", $category_id);
	
	$sites_table = new VA_Sites_Table($settings["admin_templates_dir"], "sites_table.html");
	$sites_table->set_tables("articles_categories", "articles_categories_sites", "category_id", "category_path", $category_id);
	
		
	$edit_fields = ""; $list_fields = ""; $details_fields = ""; $required_fields = "";
	
	$r->set_event(BEFORE_VALIDATE, "before_validate_category");
	$r->set_event(BEFORE_INSERT,  "before_insert_category");
	$r->set_event(AFTER_VALIDATE, "after_validate_category");
	$r->set_event(AFTER_INSERT,   "after_insert_category");
	$r->set_event(AFTER_UPDATE,   "after_update_category");
	$r->set_event(BEFORE_DELETE,   "before_delete_category");
	$r->set_event(AFTER_DEFAULT,  "default_category");
	$r->set_event(AFTER_SELECT,  "after_select");
	$r->set_event(BEFORE_SHOW,  "show_category_columns");
	$r->process();
	$rs->set_form_parameters();
	
	$sites_table->parse("sites_table", $r->get_value("sites_all"));
	$has_any_subscriptions = $access_table->parse("subscriptions_table", $r->get_value("access_level"), $r->get_value("guest_access_level"), $r->get_value("admin_access_level"));
	
	include_once("./admin_header.php");
	include_once("./admin_footer.php");
		
	if ($r->get_value("parent_category_id") == 0) {
		if ($category_id) {
			$edit_fields = $r->get_value("article_edit_fields");
			$list_fields = $r->get_value("article_list_fields");
			$details_fields = $r->get_value("article_details_fields");
			$required_fields = $r->get_value("article_required_fields");
		} else {	
			$edit_fields = "article_date,article_title,image_small,image_large,short_description,full_description";
			$list_fields = "article_date,article_title,image_small,short_description";
			$details_fields = "article_date,article_title,image_large,full_description";
			$required_fields = "article_date,article_title";
		}
	
		$edit_fields = ",," . $edit_fields . ",,";
		$list_fields = ",," . $list_fields . ",,";
		$details_fields = ",," . $details_fields . ",,";
		$required_fields = ",," . $required_fields . ",,";
		for ($i = 0; $i < sizeof($article_fields); $i++) {
			$field_name = $article_fields[$i];
			if (strpos($list_fields, "," . $field_name . ",")) {
				$t->set_var("list_" . $field_name, "checked");
			} else {
				$t->set_var("list_" . $field_name, "");
			}
			if (strpos($details_fields, "," . $field_name . ",")) {
				$t->set_var("details_" . $field_name, "checked");
			} else {
				$t->set_var("details_" . $field_name, "");
			}
			if (strpos($required_fields, "," . $field_name . ",")) {
				$t->set_var("required_" . $field_name, "checked");
			} else {
				$t->set_var("required_" . $field_name, "");
			}
		}
	}
		
	if (strlen($category_id)) {
		$t->set_var("save_button", UPDATE_BUTTON);
		$t->parse("delete", false);
	} else {
		$t->set_var("save_button", ADD_NEW_MSG);
		$t->set_var("delete", "");
	}
	
	$layout_properties = ($r->get_value("parent_category_id") == 0);
	if ($layout_properties) {
		$t->parse("layout_properties", false);
	} else {
		$t->set_var("layout_properties", "");
	}
		
	$tabs = array(
		"general"       => array( "title" => EDIT_CATEGORY_MSG),
		"fields"        => array( "title" => FIELDS_PROPERTIES_MSG, "show"=> $layout_properties),
		"fb"            => array( "title" => "FB Tags", "show"=> $layout_properties),
		"images"        => array( "title" => IMAGES_MSG),
		"meta"          => array( "title" => META_DATA_MSG),
		"rss"           => array( "title" => "RSS"),
		"sites"         => array( "title" => ADMIN_SITES_MSG, "show" => $sitelist),
		"subscriptions" => array( "title" => ACCESS_LEVELS_MSG, "show" => $has_any_subscriptions)
	);
	parse_tabs($tabs, $tab);
	
	$t->pparse("main");
	
	function before_validate_category() {
		global $r, $article_fields, $db, $table_prefix;
		if ($r->get_value("parent_category_id") == 0) {
			$edit_fields = $r->get_value("article_edit_fields");
			$list_fields = $r->get_value("article_list_fields");
			$details_fields = $r->get_value("article_details_fields");
			$required_fields = $r->get_value("article_required_fields");
			$edit_fields = array();
			for ($i = 0; $i < sizeof($article_fields); $i++) {
				$field_name = $article_fields[$i];
				if (get_param("edit_" . $field_name) == 1) {
					$edit_fields[$field_name] = 1;
				}
				if (get_param("list_" . $field_name) == 1) {
					if ($list_fields) { $list_fields .= ","; }
					$list_fields .= $field_name;
				}
				if (get_param("details_" . $field_name) == 1) {
					if ($details_fields) { $details_fields .= ","; }
					$details_fields .= $field_name;
				}
				if (get_param("required_" . $field_name) == 1) {
					if ($required_fields) { $required_fields .= ","; }
					$required_fields .= $field_name;
				}

			}
			$edit_fields["article_date_format"] = $r->get_value("article_date_format");
			$edit_fields["date_end_format"] = $r->get_value("date_end_format");
			$edit_fields["authors_filter"] = $r->get_value("authors_filter");

			$r->set_value("article_edit_fields", json_encode($edit_fields));
			$r->set_value("article_list_fields", $list_fields);
			$r->set_value("article_details_fields", $details_fields);
			$r->set_value("article_required_fields", $required_fields);
		}
	}
	
	function before_insert_category() {
		global $r, $table_prefix;
		$r->set_value("total_articles", 0);
		$r->set_value("total_subcategories", 0);
		$category_id = get_db_value("SELECT MAX(category_id) FROM " . $table_prefix . "articles_categories") + 1;
		$r->set_value("category_id", $category_id);
		return true;
	}
	
	
	function after_validate_category() {
		global $r, $rs, $access_table, $table_prefix;
		$tree = new VA_Tree("category_id", "category_name", "parent_category_id", $table_prefix . "articles_categories", "");
		set_friendly_url();
		$r->set_value("category_path", $tree->get_path($r->get_value("parent_category_id")));		
		$r->set_value("access_level", $access_table->all_selected_access_level);
		$r->set_value("guest_access_level", $access_table->guest_selected_access_level);
		$r->set_value("admin_access_level", $access_table->admin_selected_access_level);

		$article_settings = array();
		foreach ($rs->parameters as $parameter) {
			$column_name = $parameter[CONTROL_NAME];
			$control_value = $parameter[CONTROL_VALUE];
			if (strlen($control_value)) {
				$article_settings[$column_name] = $control_value;
			}
		}
		$r->set_value("article_settings", json_encode($article_settings));
	}
	
	function after_insert_category() {
		global $r, $table_prefix, $db;
		$sql  = " UPDATE " . $table_prefix . "articles_categories ";
		$sql .= " SET total_subcategories = total_subcategories + 1 ";
		$sql .= " WHERE category_id = " . $db->tosql($r->get_value("parent_category_id"), INTEGER);
		after_update_category();
	}		
	
	function after_update_category() {
		global $r, $access_table, $sites_table;
		$category_id = $r->get_value("category_id"); 
		update_category_tree($category_id, $r->get_value("category_path"));
		$access_table->save_values($category_id, get_param("save_nested_subscriptions"));
		$sites_table->save_values($category_id, $r->get_value("sites_all"), get_param("save_nested_sites"));		
	}
	
	function before_delete_category() {

		global $r, $table_prefix, $db, $dbr;
		if (!isset($dbr) || !is_object($dbr)) { $dbr = new VA_SQL($db); }

		$category_id = $r->get_value("category_id"); 
		$parent_category_id = $r->get_value("parent_category_id"); 
		
		$categories = array();
		$tree = new VA_Tree("category_id", "category_name", "parent_category_id", $table_prefix . "articles_categories", "");
		$category_path = $tree->get_path($category_id);
		if ($category_path && $category_path != "0,") {
			$sql  = " SELECT category_id FROM " . $table_prefix . "articles_categories ";
			$sql .= " WHERE category_path LIKE '" . $db->tosql($category_path, TEXT, false) . "%'";
			$db->query($sql);
			while ($db->next_record()) {
				$categories[] = $db->f("category_id");
			}
			
			if (count($categories)) {
				$db->query("DELETE FROM " . $table_prefix . "articles_assigned WHERE category_id IN ( " . $db->tosql($categories, INTEGERS_LIST) . ")");
				$db->query("DELETE FROM " . $table_prefix . "articles_categories WHERE category_id IN ( " . $db->tosql($categories, INTEGERS_LIST) . ")");
				$db->query("DELETE FROM " . $table_prefix . "articles_categories_items WHERE category_id IN ( " . $db->tosql($categories, INTEGERS_LIST) . ")");
				$db->query("DELETE FROM " . $table_prefix . "articles_categories_types WHERE category_id IN ( " . $db->tosql($categories, INTEGERS_LIST) . ")");
				$db->query("DELETE FROM " . $table_prefix . "articles_categories_subscriptions WHERE category_id IN ( " . $db->tosql($categories, INTEGERS_LIST) . ")");
				$db->query("DELETE FROM " . $table_prefix . "articles_categories_sites WHERE category_id IN ( " . $db->tosql($categories, INTEGERS_LIST) . ")");
			}
		}

		if ($parent_category_id) {
			$db->query("UPDATE " . $table_prefix . "articles_categories SET total_subcategories=total_subcategories - 1 WHERE category_id = " . $db->tosql($parent_category_id, INTEGER));
		}
		$db->query("DELETE FROM " . $table_prefix . "articles_assigned WHERE category_id=" . $db->tosql($category_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "articles_categories_types WHERE category_id=" . $db->tosql($category_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "articles_categories_types WHERE category_id=" . $db->tosql($category_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "articles_categories_subscriptions WHERE category_id=" . $db->tosql($category_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "articles_categories_sites WHERE category_id=" . $db->tosql($category_id, INTEGER));

		// need to delete also all related layouts
		// 1. check page ids for layouts we will need to deleta
		$page_ids = array();
		$sql = " SELECT page_id FROM ".$table_prefix."cms_pages WHERE page_code='articles_list' OR page_code='article_details' OR page_code='article_reviews' "; 
		$db->query($sql);
		while ($db->next_record()) {
			$page_id = $db->f("page_id");
			$page_ids[] = $page_id;
		}
		// 2. check saved page settings
		if (count($page_ids)) {
			$ps_ids = array();
			$categories[] = intval($category_id);
			$key_code_list = "'".implode("', '", $categories)."'";
			$sql  = " SELECT ps_id FROM ".$table_prefix."cms_pages_settings ";
			$sql .= " WHERE page_id IN ( " . $db->tosql($page_ids, INTEGERS_LIST) . ") ";
			$sql .= " AND key_code IN ( " .$key_code_list. ")" ;
			$db->query($sql);
			while ($db->next_record()) {
				$ps_id = $db->f("ps_id");
				$ps_ids[] = $ps_id;
			}
			if (count($ps_ids)) {
				$db->query("DELETE FROM " . $table_prefix . "cms_blocks_periods WHERE ps_id IN ( " . $db->tosql($ps_ids, INTEGERS_LIST) . ")");
				$db->query("DELETE FROM " . $table_prefix . "cms_frames_settings WHERE ps_id IN ( " . $db->tosql($ps_ids, INTEGERS_LIST) . ")");
				$db->query("DELETE FROM " . $table_prefix . "cms_pages_settings WHERE ps_id IN ( " . $db->tosql($ps_ids, INTEGERS_LIST) . ")");
				$db->query("DELETE FROM " . $table_prefix . "cms_blocks_settings WHERE ps_id IN ( " . $db->tosql($ps_ids, INTEGERS_LIST) . ")");
				$db->query("DELETE FROM " . $table_prefix . "cms_pages_blocks WHERE ps_id IN ( " . $db->tosql($ps_ids, INTEGERS_LIST) . ")");
			}
		}

		// delete products that are not assigned to any category 
		$sql  = " SELECT a.article_id FROM (" . $table_prefix ."articles a ";
		$sql .= " LEFT JOIN " . $table_prefix . "articles_assigned aa ON a.article_id=aa.article_id) ";
		$sql .= " WHERE aa.category_id IS NULL ";
		$dbr->query($sql);
		while ($dbr->next_record()) {
			$article_id = $dbr->f("article_id");
			VA_Articles::delete($article_id);
		}
	}
	
	function default_category() {
		global $r, $parent_category_id, $table_prefix, $db;
		$category_order = get_db_value("SELECT MAX(category_order) FROM " . $table_prefix . "articles_categories WHERE parent_category_id=" . $db->tosql($parent_category_id, INTEGER));
		$category_order++;
		$r->set_value("category_order", $category_order);
		$r->set_value("parent_category_id", $parent_category_id);
		$r->set_value("access_level", 255);
		$r->set_value("guest_access_level", 255);
		$r->set_value("admin_access_level", 255);
		$r->set_value("sites_all", 1);		
		$r->set_value("allowed_rate", 1);
	}
	
	function spaces_level($level) {
		$spaces = "";
		for ($i =1; $i <= $level; $i++) {
			$spaces .= "--";
		}
		return $spaces . " ";
	}
	
	function update_category_tree($parent_category_id, $category_path) {
		global $db, $table_prefix, $categories, $parent_categories;
		
		if (isset($categories[$parent_category_id]["subs"])) {
			$category_path .= $parent_category_id . ",";	
			$subs = $categories[$parent_category_id]["subs"];
			for ($s = 0; $s < sizeof($subs); $s++) {
				$sub_id = $subs[$s];
				$sql  = " UPDATE " . $table_prefix . "articles_categories SET ";
				$sql .= " category_path=" . $db->tosql($category_path, TEXT);
				$sql .= " WHERE category_id=" . $db->tosql($sub_id, INTEGER);
				$db->query($sql);

				if (isset($categories[$sub_id]["subs"])) {
					update_category_tree($sub_id, $category_path);
				}
			}
		}
	}
	
	function build_category_list($parent_id) {
		global $t, $categories, $items;
		$subs = $categories[$parent_id]["subs"];
		for ($m = 0; $m < sizeof($subs); $m++) {
			$category_id = $subs[$m];
			$category_path = $categories[$category_id]["category_path"];
			$category_name = $categories[$category_id]["category_name"];
			$category_level = preg_replace("/\d/", "", $category_path);
			$spaces = spaces_level(strlen($category_level));
	
			$items[] = array($category_id, $spaces.$category_name);
	
			if (isset($categories[$category_id]["subs"])) {
				build_category_list($category_id);
			}
		}
	}

	function show_category_columns()
	{
		global $t, $r, $db, $table_prefix, $cc_eg, $columns_number;

		$parent_category_id = $r->get_value("parent_category_id");
		if ($parent_category_id) {
			$parent_category_desc = "";

			$tree_category_id = $parent_category_id;
			$sql  = " SELECT category_name, parent_category_id ";
			$sql .= " FROM ".$table_prefix."articles_categories ";
			$sql .= " WHERE category_id=";
			while ($tree_category_id) {
				$db->query($sql . $db->tosql($tree_category_id, INTEGER));
				if($db->next_record()) {
					if ($parent_category_desc) { $parent_category_desc = " > " . $parent_category_desc; }
					$tree_name = get_translation($db->f("category_name"));
					$parent_category_desc =  $tree_name . $parent_category_desc;
					$tree_category_id = $db->f("parent_category_id");
				} else {
					$tree_category_id = 0;
				}
			} 
			$t->set_var("parent_category_desc", $parent_category_desc);
			$t->set_var("parent_category_remove_style", "display: inline;");
		} else {
			$t->set_var("parent_category_desc", "[Top]");
			$t->set_var("parent_category_remove_style", "display: none;");

		}

		$alias_category_id = $r->get_value("alias_category_id");
		if ($alias_category_id) {
			$alias_category_desc = "";

			$tree_category_id = $alias_category_id;
			$sql  = " SELECT category_name, parent_category_id ";
			$sql .= " FROM ".$table_prefix."articles_categories ";
			$sql .= " WHERE category_id=";
			while ($tree_category_id) {
				$db->query($sql . $db->tosql($tree_category_id, INTEGER));
				if($db->next_record()) {
					if ($alias_category_desc) { $alias_category_desc = " > " . $alias_category_desc; }
					$tree_name = get_translation($db->f("category_name"));
					$alias_category_desc =  $tree_name . $alias_category_desc;
					$tree_category_id = $db->f("parent_category_id");
				} else {
					$tree_category_id = 0;
				}
			} 
			$t->set_var("alias_category_desc", $alias_category_desc);
		} else {
			$t->set_var("alias_category_style", "display: none;");
		}
	}

	function after_select()
	{
		global $r, $rs, $article_fields;
		$edit_fields = $r->get_value("article_edit_fields");
		if ($edit_fields) {
			$edit_fields = json_decode($edit_fields, true);
			for ($i = 0; $i < sizeof($article_fields); $i++) {
				$field_name = $article_fields[$i];
				$edit_field_name = "edit_".$field_name;
				if(isset($edit_fields[$field_name]) && $edit_fields[$field_name]){ 
					$r->set_value($edit_field_name, 1);
				}
			}
			$r->set_value("article_date_format", get_setting_value($edit_fields, "article_date_format"));
			$r->set_value("date_end_format", get_setting_value($edit_fields, "date_end_format"));
			$r->set_value("authors_filter", get_setting_value($edit_fields, "authors_filter"));
		}

		$article_settings = $r->get_value("article_settings");
		if ($article_settings) {
			$article_settings = json_decode($article_settings, true);
			foreach ($rs->parameters as $parameter) {
				$control_name = $parameter[CONTROL_NAME];
				$control_value = get_setting_value($article_settings, $control_name, "");
				$rs->set_value($control_name, $control_value);
			}
		}

	}

?>