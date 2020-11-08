<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_site.php                                           ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ("./admin_common.php");
	include_once ($root_folder_path . "includes/record.php");
	include_once ($root_folder_path . "messages/".$language_code."/download_messages.php");

	check_admin_security("admin_sites");

	$domain_start_regexp = "/^http(s)?:\\/\\//i";

	$param_site_id 	   = get_param("param_site_id");
	$permissions   = get_permissions();
	$add_sites     = get_setting_value($permissions, "add_sites", 0);
	$update_sites  = get_setting_value($permissions, "update_sites", 0);
	// at least one site
	$remove_sites  = (get_setting_value($permissions, "remove_sites", 0) && ($param_site_id!=1));
	$return_page = "admin_sites.php";

	$operation = get_param("operation");
	if (strlen($operation)&& ($operation == "delete") && ($param_site_id>1))
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
	
		if($clear_global_settings) {
			$db->query("DELETE FROM " . $table_prefix . "global_settings WHERE site_id=" . $db->tosql($param_site_id, INTEGER));	
		}		
		header("Location: " . $return_page);
		exit;
	
	} elseif  (strlen($operation)&& ($operation == "duplicate") ) {
		
		$duplicate_global_settings = get_param('duplicate_global_settings');
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
	} else {
		$t = new VA_Template($settings["admin_templates_dir"]);
		$t->set_file("main","admin_site.html");

		$t->set_var("admin_href", "admin.php");
		$t->set_var("admin_site_href", "admin_site.php");
		$t->set_var("admin_sites_href", "admin_sites.php");
		$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", ADMIN_SITE_MSG, CONFIRM_DELETE_MSG));

		$r = new VA_Record($table_prefix . "sites");
		$r->return_page = "admin_sites.php";
		$r->add_where("param_site_id", INTEGER);
		$r->change_property("param_site_id", COLUMN_NAME, "site_id");
		
		$sql  = " SELECT site_id, site_name FROM " . $table_prefix . "sites ";
		$sql .= " WHERE (parent_site_id IS NULL OR parent_site_id=0) ";
		if ($param_site_id) {
			$sql .= " AND site_id<>" . $db->tosql($param_site_id, INTEGER);
		}
		$parent_sites = get_db_values($sql, array(array("", "")));

		$r->add_checkbox("is_mobile", INTEGER);
		$r->add_checkbox("is_mobile_redirect", INTEGER);
		if (sizeof($parent_sites) > 1) {
			$r->add_select("parent_site_id", INTEGER, $parent_sites);
		}

		$r->add_textbox("short_name", TEXT, va_message("SHORT_NAME_MSG"));
		$r->change_property("short_name", REQUIRED, true);
		$r->add_textbox("site_name", TEXT, va_message("SITE_NAME_MSG"));
		$r->parameters["site_name"][REQUIRED] = true;
		$r->parameters["site_name"][UNIQUE] = true;
		$r->parameters["site_name"][MIN_LENGTH] = 3;
		$r->add_textbox("site_url", TEXT, va_message("SITE_URL_MSG")." (".va_message("FRONT_END_MSG").")");
		$r->change_property("site_url", PARSE_NAME, "edit_site_url");
		$r->change_property("site_url", REQUIRED, true);
		$r->change_property("site_url", REGEXP_MASK, $domain_start_regexp);
		$r->add_textbox("admin_url", TEXT, va_message("SITE_URL_MSG")." (".va_message("BACK_END_MSG").")");
		$r->change_property("admin_url", REGEXP_MASK, $domain_start_regexp);
		$r->add_textbox("site_class", TEXT);

		$r->add_textbox("site_description", TEXT, va_message("SITE_DESCRIPTION_MSG"));

		$r->operations[INSERT_ALLOWED] = $add_sites;
		$r->operations[UPDATE_ALLOWED] = $update_sites;
		$r->operations[DELETE_ALLOWED] = $remove_sites;
		$r->set_event(AFTER_INSERT, "new_site_added");
		$r->set_event(AFTER_REQUEST, "check_site_params");
		$r->process();

		include_once("./admin_header.php");
		include_once("./admin_footer.php");
		
		$t->pparse("main");
	}


function check_site_params()
{
	global $r;
	$site_url = $r->get_value("site_url");
	if (strlen($site_url) && substr($site_url, strlen($site_url) - 1) != "/") {
		$site_url .= "/";
		$r->set_value("site_url", $site_url);
	}
	$admin_url = $r->get_value("admin_url");
	if (strlen($admin_url) && substr($admin_url, strlen($admin_url) - 1) != "/") {
		$admin_url .= "/";
		$r->set_value("admin_url", $admin_url);
	}
}

function new_site_added()
{
	global $r, $db, $table_prefix;
	$sql = " SELECT MAX(site_id) FROM " . $table_prefix . "sites ";
	$new_site_id = get_db_value($sql);
	$r->return_page = "admin_sites.php?new_site_id=".urlencode($new_site_id);

	// if new site is mobile assign mobile design for it
	if ($r->get_value("is_mobile")) {
		$sql  = " SELECT layout_id FROM " . $table_prefix . "layouts WHERE style_name='mobile' ";
		$db->query($sql);
		if($db->next_record()) {
			$layout_id = $db->f("layout_id");
			
			$sql  = " DELETE FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type='global' AND setting_name='layout_id' AND site_id=" . $db->tosql($new_site_id, INTEGER);
			$db->query($sql);	
			
			$sql  = "INSERT INTO " . $table_prefix . "global_settings (setting_type, setting_name, setting_value, site_id) VALUES (";
			$sql .= "'global', 'layout_id'," . intval($layout_id) . "," . $db->tosql($new_site_id, INTEGER) . ")";				
			$db->query($sql);
		}
	}


}

