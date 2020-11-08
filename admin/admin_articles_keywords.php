<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_articles_keywords.php                              ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/tabs_functions.php");
	include_once($root_folder_path . "includes/keywords_functions.php");
	include_once("./admin_common.php");

	check_admin_security("articles");

	$custom_breadcrumb = array(
		"admin_global_settings.php" => SETTINGS_MSG,
		"admin_articles_top.php" => ARTICLES_TITLE,
		"admin_articles_keywords.php" => KEYWORDS_SEARCH_MSG,
	);

	// additional connection
	$dbs = new VA_SQL();
	$dbs->DBType      = $db_type;
	$dbs->DBDatabase  = $db_name;
	$dbs->DBUser      = $db_user;
	$dbs->DBPassword  = $db_password;
	$dbs->DBHost      = $db_host;
	$dbs->DBPort      = $db_port;
	$dbs->DBPersistent= $db_persistent;

	$operation = get_param("operation");
	
	if ($operation == "clear" || $operation == "generate") {
		$articles_settings = get_settings("articles");
		if ($operation == "clear"){
			// clear keywords
			$sql = "DELETE from ".$table_prefix."keywords_articles ";
			$db->query($sql);
			$sql = "UPDATE ".$table_prefix."articles SET is_keywords=0 ";
			$db->query($sql);
		} else if ($operation == "generate") {
			// generate keywords for articles 
			$sql  = " SELECT * FROM " . $table_prefix . "articles ";
			$sql .= " WHERE is_keywords=0 OR is_keywords IS NULL ";
  
			$dbs->RecordsPerPage = 10;
			$dbs->PageNumber = 1;              
			$dbs->query($sql);
			while ($dbs->next_record()) {
				$article_id = $dbs->f("article_id");
				generate_keywords($dbs->Record, "articles");
			}
		}
  
		// prepare response
		$sql = " SELECT COUNT(*) FROM " . $table_prefix . "articles ";
		$total_articles = get_db_value($sql);
  
		$sql = " SELECT COUNT(*) FROM " . $table_prefix . "articles WHERE is_keywords=1 ";
		$indexed_articles = get_db_value($sql);
  
		$sql = " SELECT COUNT(*) FROM " . $table_prefix . "keywords_articles ";
		$indexed_keywords = get_db_value($sql);
  
		$data = array(
			"total_articles" => $total_articles,
			"indexed_articles" => $indexed_articles,
			"indexed_keywords"  =>  $indexed_keywords,
		);
		echo json_encode($data);
		return;
	}


	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_articles_keywords.html");

	include_once("./admin_header.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_articles_href", "admin_articles.php");
	$t->set_var("admin_articles_top_href", "admin_articles_top.php");
	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");

	$t->set_var("date_edit_format", join("", $date_edit_format));

	$yes_no = 
		array( 
			array(1, YES_MSG), array(0, NO_MSG)
			);

	$r = new VA_Record($table_prefix . "global_settings");
	// keywords settings
	$keywords_types = array(array("1", PER_KEYWORD_MSG), array("2", PER_FIELD_MSG));
	$r->add_radio("keywords_search", INTEGER, $yes_no);
	// Main title field
	$r->add_checkbox("article_title_index", INTEGER);
	$r->add_textbox("article_title_rank", INTEGER);
	$r->add_select("article_title_type", INTEGER, $keywords_types);
	// Important data: authors, albums, tags
	$r->add_checkbox("author_name_index", INTEGER);
	$r->add_textbox("author_name_rank", INTEGER);
	$r->add_select("author_name_type", INTEGER, $keywords_types);
	$r->add_checkbox("album_name_index", INTEGER);
	$r->add_textbox("album_name_rank", INTEGER);
	$r->add_select("album_name_type", INTEGER, $keywords_types);
	$r->add_checkbox("tag_name_index", INTEGER);
	$r->add_textbox("tag_name_rank", INTEGER);
	$r->add_select("tag_name_type", INTEGER, $keywords_types);
	// Valuable description data
	$r->add_checkbox("short_description_index", INTEGER);
	$r->add_textbox("short_description_rank", INTEGER);
	$r->add_select("short_description_type", INTEGER, $keywords_types);
	$r->add_checkbox("full_description_index", INTEGER);
	$r->add_textbox("full_description_rank", INTEGER);
	$r->add_select("full_description_type", INTEGER, $keywords_types);
	$r->add_checkbox("highlights_index", INTEGER);
	$r->add_textbox("highlights_rank", INTEGER);
	$r->add_select("highlights_type", INTEGER, $keywords_types);
	$r->add_checkbox("hot_description_index", INTEGER);
	$r->add_textbox("hot_description_rank", INTEGER);
	$r->add_select("hot_description_type", INTEGER, $keywords_types);
	$r->add_checkbox("notes_index", INTEGER);
	$r->add_textbox("notes_rank", INTEGER);
	$r->add_select("notes_type", INTEGER, $keywords_types);
	$r->add_checkbox("meta_title_index", INTEGER);
	$r->add_textbox("meta_title_rank", INTEGER);
	$r->add_select("meta_title_type", INTEGER, $keywords_types);
	$r->add_checkbox("meta_description_index", INTEGER);
	$r->add_textbox("meta_description_rank", INTEGER);
	$r->add_select("meta_description_type", INTEGER, $keywords_types);
	$r->add_checkbox("meta_keywords_index", INTEGER);
	$r->add_textbox("meta_keywords_rank", INTEGER);
	$r->add_select("meta_keywords_type", INTEGER, $keywords_types);
	
	$r->get_form_values();

	$param_site_id = get_session("session_site_id");
	$tab = get_param("tab");

	if (!$tab) { $tab = "general"; }
	$operation = get_param("operation");
	$return_page = get_param("rp");
	if (!strlen($return_page)) $return_page = "admin_articles_top.php";
	if (strlen($operation))
	{
		if ($operation == "cancel") {
			header("Location: " . $return_page);
			exit;
		} else {

			$is_valid = $r->validate();
	  
			if (!$is_valid) {
				$tab = "general";
			}
	  
			if ($is_valid) {
				// delete only keywords parameters 
				$param_names = array();
				foreach ($r->parameters as $key => $value) {
					$param_names[] = $key;
				}
				// update product settings
				$sql  = " DELETE FROM " . $table_prefix . "global_settings ";
				$sql .= " WHERE setting_type='articles'";
				$sql .= " AND setting_name IN (" . $db->tosql($param_names, TEXT_LIST) . ")";
				$sql .= " AND site_id=" . $db->tosql($param_site_id,INTEGER);
				$db->query($sql);
				foreach ($r->parameters as $key => $value) {
					$sql  = "INSERT INTO " . $table_prefix . "global_settings (setting_type, setting_name, setting_value, site_id) VALUES (";
					$sql .= "'articles', '" . $key . "'," . $db->tosql($value[CONTROL_VALUE], TEXT) . ",";
					$sql .= $db->tosql($param_site_id,INTEGER) . ") ";
					$db->query($sql);
				}
	  
				set_session("session_settings", "");
	  
				// show success message
				$t->parse("success_block", false);			
			}
		}
	} else {
		// get articles settings
		foreach ($r->parameters as $key => $value) {
			$sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type='articles' AND setting_name='" . $key . "'";
			$sql .= " AND ( site_id=1 OR  site_id=" . $db->tosql($param_site_id,INTEGER). ") ";
			$sql .= " ORDER BY site_id DESC ";
			$r->set_value($key, get_db_value($sql));
		}
	}

	// get 
	$sql = " SELECT COUNT(*) FROM " . $table_prefix . "articles ";
	$total_articles = get_db_value($sql);

	$sql = " SELECT COUNT(*) FROM " . $table_prefix . "articles WHERE is_keywords=1 ";
	$indexed_articles = get_db_value($sql);

	$sql = " SELECT COUNT(*) FROM " . $table_prefix . "keywords_articles ";
	$indexed_keywords = get_db_value($sql);

	$t->set_var("total_articles", $total_articles);
	$t->set_var("indexed_articles", $indexed_articles);
	$t->set_var("indexed_keywords", $indexed_keywords);


	// set parameters
	$r->set_parameters();
	$t->set_var("rp", htmlspecialchars($return_page));

	// set styles for tabs
	$tabs = array(
		"settings" => array("title" => SETTINGS_MSG), 
		"keywords" => array("title" => GENERATE_KEYWORDS_MSG),
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