<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_site_settings.php                                  ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ("./admin_common.php");
	include_once ($root_folder_path . "includes/record.php");
	include_once ($root_folder_path . "messages/".$language_code."/download_messages.php");
	include_once ($root_folder_path . "messages/".$language_code."/cart_messages.php");
	include_once ($root_folder_path . "messages/".$language_code."/forum_messages.php");
	include_once ($root_folder_path . "messages/".$language_code."/manuals_messages.php");
	include_once ($root_folder_path . "messages/".$language_code."/support_messages.php");

	check_admin_security("admin_sites");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_site_settings.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_site_href", "admin_site.php");
	$t->set_var("admin_sites_href", "admin_sites.php");
	$t->set_var("admin_site_settings_href", "admin_site_settings.php");

	$permissions   = get_permissions();
	$add_sites     = get_setting_value($permissions, "add_sites", 0);
	$update_sites  = get_setting_value($permissions, "update_sites", 0);
	// at least one site
	$remove_sites  = (get_setting_value($permissions, "remove_sites", 0));
	$return_page = "admin_sites.php";

	// initialize arrays
	$operations = array(
		array("", ""),
		//array("copy_block", COPY_BLOCK_MSG),
		array("copy_page",  COPY_PAGE_SETTINGS_MSG),
		//array("clear_block", CLEAR_BLOCK_MSG),
		array("clear_page", CLEAR_PAGE_SETTINGS_MSG),
	);

	$sql = " SELECT site_id, site_name FROM " . $table_prefix . "sites ORDER BY site_id ";
	$sites = get_db_values($sql, array(array("", "")));

	$r = new VA_Record($table_prefix . "sites");
	$r->return_page = "admin_sites.php";

	$r->add_select("operation", TEXT, $operations, OPERATION_MSG);
	$r->change_property("operation", REQUIRED, true);

	$r->add_select("source_site_id", INTEGER, $sites, ADMIN_SITE_MSG);
	$r->add_select("target_site_id", INTEGER, $sites, ADMIN_SITE_MSG);

	$r->add_checkbox("pages_all", INTEGER);
	$r->change_property("pages_all", DEFAULT_VALUE, 1);

	$r->operations[INSERT_ALLOWED] = $add_sites;
	$r->operations[UPDATE_ALLOWED] = $update_sites;
	$r->operations[DELETE_ALLOWED] = $remove_sites;

	$r->set_event(ON_CUSTOM_OPERATION, "update_site_settings");
	$r->set_event(ON_CUSTOM_OPERATION, "update_site_settings");
	$r->process();

/*
	if (strlen($operation)&& ($operation == "delete"))
	{
		
		$db->query("DELETE FROM " . $table_prefix . "ads_categories_sites WHERE site_id=" . $db->tosql($param_site_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "articles_categories_sites WHERE site_id=" . $db->tosql($param_site_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "categories_sites WHERE site_id=" . $db->tosql($param_site_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "coupons_sites WHERE site_id=" . $db->tosql($param_site_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "forum_categories_sites WHERE site_id=" . $db->tosql($param_site_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "items_sites WHERE site_id=" . $db->tosql($param_site_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "layouts_sites WHERE site_id=" . $db->tosql($param_site_id, INTEGER));	
		$db->query("DELETE FROM " . $table_prefix . "manuals_categories_sites WHERE site_id=" . $db->tosql($param_site_id, INTEGER));		
		$db->query("DELETE FROM " . $table_prefix . "pages_sites WHERE site_id=" . $db->tosql($param_site_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "payment_systems_sites WHERE site_id=" . $db->tosql($param_site_id, INTEGER));		
		$db->query("DELETE FROM " . $table_prefix . "shipping_types_sites WHERE site_id=" . $db->tosql($param_site_id, INTEGER));		
		$db->query("DELETE FROM " . $table_prefix . "support_departments_sites  WHERE site_id=" . $db->tosql($param_site_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "support_products_sites  WHERE site_id=" . $db->tosql($param_site_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "user_types_sites WHERE site_id=" . $db->tosql($param_site_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "sites WHERE site_id=" . $db->tosql($param_site_id, INTEGER));		
		$db->query("DELETE FROM " . $table_prefix . "global_settings WHERE site_id=" . $db->tosql($param_site_id, INTEGER));
		header("Location: " . $return_page);
		exit;
		
	} elseif  (strlen($operation)&& ($operation == "clear") && ($param_site_id>1)) {
		
		$clear_global_settings = get_param('clear_global_settings');
		$clear_page_settings   = get_param('clear_page_settings');
	
		if($clear_global_settings) {
			$db->query("DELETE FROM " . $table_prefix . "global_settings WHERE site_id=" . $db->tosql($param_site_id, INTEGER));	
		}		
		if($clear_page_settings) {
			//$db->query("DELETE FROM " . $table_prefix . "page_settings WHERE site_id=" . $db->tosql($param_site_id, INTEGER));			
		}		
		header("Location: " . $return_page);
		exit;
	
	} elseif  (strlen($operation)&& ($operation == "duplicate") ) {
		
		$duplicate_global_settings = get_param('duplicate_global_settings');
		$duplicate_page_settings   = get_param('duplicate_page_settings');
		$duplicate_site_id         = get_param('duplicate_site_id');

		if($duplicate_global_settings) {
			$sql  = " SELECT setting_type, setting_name, setting_value FROM " . $table_prefix . "global_settings ";
			if($param_site_id>1) {
				$sql .= " WHERE (site_id=1 OR site_id=" . $db->tosql($duplicate_site_id, INTEGER) . ") ";
				$sql .= " ORDER BY site_id ASC";
			} else {
				$sql .= " WHERE site_id=" . $db->tosql($duplicate_site_id, INTEGER);
			}
			$db->query($sql);			
			$tmp_settings = array();
			while ($db->next_record())	{
				$duplicate_setting_type  = $db->f("setting_type");
				$duplicate_setting_name  = $db->f("setting_name");
				$duplicate_setting_value = $db->f("setting_value");
				$tmp_settings[$duplicate_setting_type][$duplicate_setting_name]=$duplicate_setting_value;
			}
			if($tmp_settings) {
				$db->query("DELETE FROM " . $table_prefix . "global_settings WHERE site_id=" . $db->tosql($param_site_id, INTEGER));
				foreach ($tmp_settings AS $duplicate_setting_type=>$tmp2){
					foreach ($tmp2 AS $duplicate_setting_name=>$duplicate_setting_value){
						$sql  = " INSERT INTO " . $table_prefix . "global_settings ";
						$sql .= " (setting_type,setting_name,setting_value,site_id) VALUES ( ";						
						$sql .= $db->tosql($duplicate_setting_type, TEXT) . ",";
						$sql .= $db->tosql($duplicate_setting_name, TEXT) . ",";
						$sql .= $db->tosql($duplicate_setting_value, TEXT) . ",";
						$sql .= $db->tosql($param_site_id, INTEGER) . ")";
						$db->query($sql);
					}
				}
			}			
		}		
		if($duplicate_page_settings && false) { // todo: apply new CMS rules
			@set_time_limit(60);
			$sql  = " SELECT layout_id, page_name, setting_name, setting_order, setting_value FROM " . $table_prefix . "page_settings ";
			if($param_site_id>1) {
				$sql .= " WHERE (site_id=1 OR site_id=" . $db->tosql($duplicate_site_id, INTEGER) . ") ";
				$sql .= " ORDER BY site_id ASC";
			} else {
				$sql .= " WHERE site_id=" . $db->tosql($duplicate_site_id, INTEGER);
			}
			$db->query($sql);			
			$tmp_settings = array();
			while ($db->next_record())	{
				$duplicate_layout_id     = $db->f("layout_id");
				$duplicate_page_name     = $db->f("page_name");
				$duplicate_setting_name  = $db->f("setting_name");
				$duplicate_setting_order = $db->f("setting_order");
				$duplicate_setting_value = $db->f("setting_value");
				$tmp_settings[$duplicate_layout_id][$duplicate_page_name][$duplicate_setting_name]=
					array("value"=>$duplicate_setting_value,"order"=>$duplicate_setting_order);
			}
			if($tmp_settings) {
				$db->query("DELETE FROM " . $table_prefix . "page_settings WHERE site_id=" . $db->tosql($param_site_id, INTEGER));
				foreach ($tmp_settings AS $duplicate_layout_id=>$tmp2) {
					foreach ($tmp2 AS $duplicate_page_name =>$tmp3) {
						foreach ($tmp3 AS $duplicate_setting_name=>$tmp4) {
							$sql = " INSERT INTO " . $table_prefix . "page_settings ";
							$sql .= " (layout_id, page_name, setting_name, setting_order, setting_value, site_id) VALUES ( ";						
							$sql .= $db->tosql($duplicate_layout_id, INTEGER) . ",";
							$sql .= $db->tosql($duplicate_page_name, TEXT) . ",";
							$sql .= $db->tosql($duplicate_setting_name, TEXT) . ",";
							$sql .= $db->tosql($tmp4['order'], TEXT) . ",";
							$sql .= $db->tosql($tmp4['value'], TEXT) . ",";
							$sql .= $db->tosql($param_site_id, INTEGER) . "); \n";
							$db->query($sql);
						}
					}
				}
					
			}	
		}			
		header("Location: " . $return_page);
		exit;		
	} 
*/


		// parse cms page 

		
	$articles = array();
	$sql  = " SELECT ac.category_id, ac.category_name ";
	$sql .= " FROM " . $table_prefix . "articles_categories ac ";
	$sql .= " WHERE ac.parent_category_id=0 ";
	$db->query($sql);
	while ($db->next_record()) {
		$category_id = $db->f("category_id");
		$category_name = get_translation($db->f("category_name"));
		$articles[$category_id] = $category_name;
	}

	$modules = array();
	$sql  = " SELECT m.module_id, m.module_code, m.module_name ";
	$sql .= " FROM " . $table_prefix . "cms_modules m ";
	$sql .= " ORDER BY m.module_order ";
	$db->query($sql);
	while($db->next_record()) {
		$module_id = $db->f("module_id");
		$module_code = $db->f("module_code");
		$module_name = get_translation($db->f("module_name"));

		if ($module_code == "articles") {
			foreach ($articles as $category_id => $category_name) {
				$article_module = $module_name;
				$t->set_var("category_name", $category_name);
				parse_value($article_module);
				$modules[$module_id."_".$category_id] = array(
					"id" => $module_id, "name" => $article_module, "key_code" => $category_id, "key_type" => "category");
			}
		} else {
			$modules[$module_id] = array("id" => $module_id,  "name" => $module_name, "key_code" => "", );
		}
	}

	$pages = array();
	$sql  = " SELECT p.page_id, p.module_id, p.page_code, p.page_name ";
	$sql .= " FROM " . $table_prefix . "cms_pages p ";
	$sql .= " ORDER BY p.page_order ";
	$db->query($sql);
	while($db->next_record()) {
		$page_id = $db->f("page_id");
		$module_id = $db->f("module_id");
		$page_code = $db->f("page_code");
		$page_name = get_translation($db->f("page_name"));
		parse_value($page_name);

		$pages[$module_id][$page_id] = array("code" => $page_code, "name" => $page_name);
	}

	// check pages_all parameter and related parameters
	$pages_all = $r->get_value("pages_all");
	$pages_list_style = "";
	if ($pages_all) {
		$pages_list_style = "display: none;";
	}
	$pages_number = 0;
	foreach ($modules as $module_key => $module) {
		$page_index = 0;
		$module_id = $module["id"];
		$module_pages = isset($pages[$module_id]) ? $pages[$module_id] : "";
		$key_code = isset($module["key_code"]) ? $module["key_code"] : "";
		$key_type = isset($module["key_type"]) ? $module["key_type"] : "";


		$t->set_var("module_id", $module["id"]);
		$t->set_var("module_name", $module["name"]);

		// parse pages
		$t->set_var("cms_pages_rows", "");
		$t->set_var("cms_pages_cols", "");
		$start_index = $pages_number + 1;
		if (is_array($module_pages) && sizeof($module_pages) > 0)  {
			foreach ($module_pages as $page_id => $page) {
				$page_index++;
				$pages_number++;
				$page_code = $page["code"];
				$page_name = $page["name"];
				$checked_id = get_param("page_".$pages_number);
				$page_checked = "";
				if (strval($page_id) == strval($checked_id) || $pages_all) {
					$page_checked = " checked ";
				}

				$page_url = new VA_URL("admin_cms_page_layout.php", false);
				$page_url->add_parameter("page_id", CONSTANT, $page_id);
				$page_url->add_parameter("key_code", CONSTANT, $key_code);
				$page_url->add_parameter("key_type", CONSTANT, $key_type);
				$page_url->add_parameter("rp", CONSTANT, "admin_cms.php");

				$t->set_var("page_index", $page_index);
				$t->set_var("page_number", $pages_number);
				$t->set_var("page_id", $page_id);
				$t->set_var("page_code", $page_code);
				$t->set_var("key_code", $key_code);
				$t->set_var("key_type", $key_type);
				$t->set_var("page_name", $page_name);
				$t->set_var("page_checked", $page_checked);
				$t->set_var("admin_cms_page_url", $page_url->get_url());
				
				$t->parse("cms_pages_cols", true);
				if ($page_index % 4 == 0) {
					$t->parse("cms_pages_rows", true);
					$t->set_var("cms_pages_cols", "");
				}
			}
			if ($page_index % 4 != 0) {
				$t->parse("cms_pages_rows", true);
			}

			$t->set_var("start_index", $start_index);
			$t->set_var("end_index", $pages_number);
			$t->parse("cms_modules", true);
		}

	}
	$t->set_var("pages_number", $pages_number);
	$t->set_var("pages_list_style", $pages_list_style);

	$copy_from_style = ""; $copy_to_style = "";
	$source_site_style = ""; $target_site_style = ""; $pages_all_style = "";
	if ($r->get_value("operation") == "") {
		$copy_from_style = "display:none;";
		$copy_to_style = "display:none;";
		$source_site_style = "display:none;";
		$target_site_style = "display:none;";
		$pages_all_style = "display:none;";
	} else if ($r->get_value("operation") == "copy_page") {
	} else if ($r->get_value("operation") == "clear_page") {
		$copy_from_style = "display:none;";
		$copy_to_style = "display:none;";
		$source_site_style = "display:none;";
	}
	$t->set_var("copy_from_style", $copy_from_style);
	$t->set_var("copy_to_style", $copy_to_style);
	$t->set_var("source_site_style", $source_site_style);
	$t->set_var("target_site_style", $target_site_style);
	$t->set_var("pages_all_style", $pages_all_style);


	include_once("./admin_header.php");
	include_once("./admin_footer.php");
		
	$t->pparse("main");

function update_site_settings($params)
{
	global $r, $db, $table_prefix;

	$operation = $r->get_value("operation");
	if (strlen($operation)) {
		// set some required fields before validate them
		$r->change_property("target_site_id", REQUIRED, true);
		if ($operation == "copy_page") {
			$r->change_property("source_site_id", REQUIRED, true);
		}
		$r->data_valid = $r->validate();
		if ($r->data_valid) {
			$source_site_id = get_param("source_site_id");
			$target_site_id = get_param("target_site_id");
			$pages_number = get_param("pages_number");
			if ($operation == "copy_page") {
				for ($index = 1; $index <= $pages_number; $index++) {
					$page_id = get_param("page_".$index);
					if (strlen($page_id)) {
						$key_code = get_param("key_code_".$index);
						$key_type = get_param("key_type_".$index);
						copy_page_settings($source_site_id, $target_site_id, $page_id, $key_code, $key_type);
					}
				}
				$r->success_message = SETTINGS_COPIED_MSG;
				$r->empty_values();
				$r->set_default_values();
				$r->redirect = false;
			} else if ($operation == "clear_page") {
				for ($index = 1; $index <= $pages_number; $index++) {
					$page_id = get_param("page_".$index);
					if (strlen($page_id)) {
						$key_code = get_param("key_code_".$index);
						$key_type = get_param("key_type_".$index);
						clear_page_settings($target_site_id, $page_id, $key_code, $key_type);
					}
				}
				$r->success_message = SETTINGS_CLEARED_MSG;
				$r->empty_values();
				$r->set_default_values();
				$r->redirect = false;
			}
		} else {
			$r->redirect = false;
		}
	}
}

function copy_page_settings($source_site_id, $target_site_id, $page_id, $key_code, $key_type)
{
	global $r, $db, $table_prefix;
	// addition connection for properties
	$dbp = new VA_SQL();
	$dbp->DBType     = $db->DBType;
	$dbp->DBDatabase = $db->DBDatabase;
	$dbp->DBUser     = $db->DBUser;
	$dbp->DBPassword = $db->DBPassword;
	$dbp->DBHost     = $db->DBHost;
	$dbp->DBPort       = $db->DBPort;
	$dbp->DBPersistent = $db->DBPersistent;

	// frame settings
	$pf = new VA_Record($table_prefix . "cms_frames_settings");
	$frame_fields = array(
		"frame_id" => INTEGER, "frame_style" => TEXT, "frame_class" => TEXT, "frame_code" => TEXT,
		"html_frame_start" => TEXT, "html_before_block" => TEXT, "html_between_blocks" => TEXT,
		"html_after_block" => TEXT, "html_frame_end" => TEXT,
	);
	$pf->add_where("fs_id", INTEGER);
	$pf->add_textbox("ps_id", INTEGER);
	foreach ($frame_fields as $field => $type) {
		$pf->add_textbox($field, $type);
	}

	// page blocks
	$pb = new VA_Record($table_prefix . "cms_pages_blocks");
	$block_fields = array(
		"block_id" => INTEGER, "block_key" => TEXT, "frame_id" => INTEGER,
		"block_order" => INTEGER, "tag_name" => TEXT, "html_template" => TEXT,
		"css_class" => TEXT, "block_style" => TEXT, "block_title" => TEXT,
	);
	$pb->add_where("pb_id", INTEGER);
	$pb->add_textbox("ps_id", INTEGER);
	foreach ($block_fields as $field => $type) {
		$pb->add_textbox($field, $type);
	}

	// pages blocks settings
	$bs = new VA_Record($table_prefix . "cms_blocks_settings");
	$bs->add_where("bs_id", INTEGER);
	$bs->add_textbox("ps_id", INTEGER);
	$bs->add_textbox("pb_id", INTEGER);
	$bs->add_textbox("property_id", INTEGER);
	$bs->add_textbox("value_id", INTEGER);
	$bs->add_textbox("variable_name", TEXT);
	$bs->add_textbox("variable_value", TEXT);

	$ps_id = "";
	$sql  = " SELECT * ";
	$sql .= " FROM " . $table_prefix . "cms_pages_settings cps ";
	$sql .= " WHERE cps.page_id=" . $db->tosql($page_id, INTEGER);
	if (strlen($key_code)) {
		$sql .= " AND cps.key_code=" . $db->tosql($key_code, TEXT);
	} else {
		$sql .= " AND (cps.key_code='' OR cps.key_code IS NULL) ";
	}
	if (strlen($key_type)) {
		$sql .= " AND cps.key_type=" . $db->tosql($key_type, TEXT);
	} else {
		$sql .= " AND (cps.key_type='' OR cps.key_type IS NULL) ";
	}
	$sql .= " AND cps.site_id=" . $db->tosql($source_site_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		// get global page settings
		$ps_id = $db->f("ps_id");
		$page_settings = $db->Record;

		// get frame settings
		$frame_settings = array();
		$sql = "SELECT * FROM " . $table_prefix . "cms_frames_settings WHERE ps_id=" . $db->tosql($ps_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$fs_id = $db->f("fs_id");
			$frame_settings[$fs_id] = $db->Record;
		}
		// get blocks 
		$blocks = array();
		$sql = "SELECT * FROM " . $table_prefix . "cms_pages_blocks WHERE ps_id=" . $db->tosql($ps_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$pb_id = $db->f("pb_id");
			$blocks[$pb_id] = $db->Record;
			$blocks[$pb_id]["properties"] = array();
		}
		// get blocks settings 
		$sql = "SELECT * FROM " . $table_prefix . "cms_blocks_settings WHERE ps_id=" . $db->tosql($ps_id, INTEGER);
		$db->query($sql);
		$db->query($sql);
		while ($db->next_record()) {
			$bs_id = $db->f("bs_id");
			$pb_id = $db->f("pb_id");
			if (isset($blocks[$pb_id])) {
				$blocks[$pb_id]["properties"][] = $db->Record;
			}
		}
	}

	// clear old settings
	clear_page_settings($target_site_id, $page_id, $key_code, $key_type);

	// add to settings to target site
	if (strlen($ps_id)) {
		$ps = new VA_Record($table_prefix . "cms_pages_settings");
		$ps->add_where("ps_id", INTEGER);
		$ps->add_textbox("page_id", INTEGER);
		$ps->add_textbox("key_code", TEXT);
		$ps->change_property("key_code", USE_SQL_NULL, false);
		$ps->add_textbox("key_type", TEXT);
		$ps->change_property("key_type", USE_SQL_NULL, false);
		$ps->add_textbox("key_rule", TEXT);
		$ps->change_property("key_rule", USE_SQL_NULL, false);
		$ps->add_textbox("layout_id", INTEGER);
		$ps->add_textbox("site_id", INTEGER);
		$ps->add_textbox("meta_title", TEXT);
		$ps->add_textbox("meta_keywords", TEXT);
		$ps->add_textbox("meta_description", TEXT);

		$ps->set_value("site_id", $target_site_id);
		$ps->set_value("page_id", $page_settings["page_id"]);
		$ps->set_value("key_code", $page_settings["key_code"]);
		$ps->set_value("key_type", $page_settings["key_type"]);
		$ps->set_value("key_rule", $page_settings["key_rule"]);
		$ps->set_value("layout_id", $page_settings["layout_id"]);
		$ps->set_value("meta_title", $page_settings["meta_title"]);
		$ps->set_value("meta_keywords", $page_settings["meta_keywords"]);
		$ps->set_value("meta_description", $page_settings["meta_description"]);
		// insert new page settings 
		if ($db->DBType == "postgre") {
			$sql = " SELECT NEXTVAL('seq_" . $table_prefix . "cms_pages_settings ') ";
			$ps_id = get_db_value($sql);
			$ps->set_value("ps_id", $ps_id);
			$ps->change_property("ps_id", USE_IN_INSERT, true);
		}
		$ps->insert_record();
		if ($db->DBType == "mysql") {
			$sql = " SELECT LAST_INSERT_ID() ";
			$ps_id = get_db_value($sql);
		} else if ($db->DBType == "access") {
			$sql = " SELECT @@IDENTITY ";
			$ps_id = get_db_value($sql);
		} else if ($db->DBType == "db2") {
			$sql = " SELECT PREVVAL FOR seq_" . $table_prefix . "cms_pages_settings FROM " . $table_prefix . "cms_pages_settings";
			$ps_id = get_db_value($sql);
		} else {
			$sql = " SELECT MAX(ps_id) " . $table_prefix . "cms_pages_settings FROM " . $table_prefix . "cms_pages_settings";
			$ps_id = get_db_value($sql);
		}

		// add frame settings
		foreach ($frame_settings as $fs_id => $frame) {
			$pf->set_value("ps_id", $ps_id);
			foreach ($frame_fields as $field => $type) {
				$pf->set_value($field, $frame[$field]);
			}
			$pf->insert_record();
		}

		// add blocks to page
		foreach ($blocks as $pb_id => $block) {
			$pb->set_value("ps_id", $ps_id);
			foreach ($block_fields as $field => $type) {
				$pb->set_value($field, $block[$field]);

			}
			if ($db->DBType == "postgre") {
				$sql = " SELECT NEXTVAL('seq_" . $table_prefix . "cms_pages_blocks ') ";
				$pb_id = get_db_value($sql);
				$pb->set_value("pb_id", $pb_id);
				$pb->change_property("pb_id", USE_IN_INSERT, true);
			}
			$pb->insert_record();
			if ($db->DBType == "mysql") {
				$sql = " SELECT LAST_INSERT_ID() ";
				$pb_id = get_db_value($sql);
			} else if ($db->DBType == "access") {
				$sql = " SELECT @@IDENTITY ";
				$pb_id = get_db_value($sql);
			} else if ($db->DBType == "db2") {
				$sql = " SELECT PREVVAL FOR seq_" . $table_prefix . "cms_pages_blocks FROM " . $table_prefix . "cms_pages_blocks ";
				$pb_id = get_db_value($sql);
			} else {
				$sql = " SELECT MAX(pb_id) " . $table_prefix . "cms_pages_blocks FROM " . $table_prefix . "cms_pages_blocks ";
				$pb_id = get_db_value($sql);
			}

			// add block settings if there are any
			foreach ($block["properties"] as $bs_id => $block_settings) {
				$bs->set_value("ps_id", $ps_id);
				$bs->set_value("pb_id", $pb_id);
				$bs->set_value("property_id", $block_settings["property_id"]);
				$bs->set_value("value_id", $block_settings["value_id"]);
				$bs->set_value("variable_name", $block_settings["variable_name"]);
				$bs->set_value("variable_value", $block_settings["variable_value"]);
				$bs->insert_record();
			}
		}
	}
}

function clear_page_settings($site_id, $page_id, $key_code, $key_type)
{
	global $r, $db, $table_prefix;

	$sql  = " SELECT cps.ps_id ";
	$sql .= " FROM " . $table_prefix . "cms_pages_settings cps ";
	$sql .= " WHERE cps.page_id=" . $db->tosql($page_id, INTEGER);
	if (strlen($key_code)) {
		$sql .= " AND cps.key_code=" . $db->tosql($key_code, TEXT);
	} else {
		$sql .= " AND (cps.key_code='' OR cps.key_code IS NULL) ";
	}
	if (strlen($key_type)) {
		$sql .= " AND cps.key_type=" . $db->tosql($key_type, TEXT);
	} else {
		$sql .= " AND (cps.key_type='' OR cps.key_type IS NULL) ";
	}
	$sql .= " AND cps.site_id=" . $db->tosql($site_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$ps_id = $db->f("ps_id");
		$sql = " DELETE FROM " . $table_prefix . "cms_blocks_settings WHERE ps_id=" . $db->tosql($ps_id, INTEGER);
		$db->query($sql);
		$sql = " DELETE FROM " . $table_prefix . "cms_pages_blocks WHERE ps_id=" . $db->tosql($ps_id, INTEGER);
		$db->query($sql);
		$sql = " DELETE FROM " . $table_prefix . "cms_frames_settings WHERE ps_id=" . $db->tosql($ps_id, INTEGER);
		$db->query($sql);
		$sql = " DELETE FROM " . $table_prefix . "cms_pages_settings WHERE ps_id=" . $db->tosql($ps_id, INTEGER);
		$db->query($sql);
	}
}



?>