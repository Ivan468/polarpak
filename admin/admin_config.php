<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_config.php                                         ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$is_admin_path = true; // use admin path to the root of the web folder
	$root_folder_path = "../";
	$tracking_ignore = true; // if it set to true ignoring statistics for such pages

	define("WHERE_DB_FIELD",   1);
	define("USUAL_DB_FIELD",   2);
	define("FOREIGN_DB_FIELD", 3);
	define("HIDE_DB_FIELD",    4);
	define("RELATED_DB_FIELD", 5);
	define("CUSTOM_FIELD",     6);

	/**
	 * Compare two versions in string format. Returns 1 (if first is bigger), 2 (if second), or 0 if equal
	 *
	 * @param string $version1
	 * @param string $version2
	 * @return integer
	 */	
	 
	function update_rating($table_name, $column_name, $review_id)
	{
		global $db;
		global $table_prefix;

		$sql = "SELECT " . $column_name . " FROM " . $table_name . " WHERE review_id=" . $db->tosql($review_id, INTEGER);		
		$column_id = get_db_value($sql);

		$sql = " SELECT COUNT(*) FROM " . $table_name . " WHERE approved=1 AND rating <> 0 AND " . $column_name . "=" . $db->tosql($column_id, INTEGER);
		$total_rating_votes = get_db_value($sql);

		$sql = " SELECT SUM(rating) FROM " . $table_name . " WHERE approved=1 AND rating <> 0 AND " . $column_name . "=" . $db->tosql($column_id, INTEGER);
		$total_rating_sum = get_db_value($sql);
		if(!strlen($total_rating_sum)) $total_rating_sum = 0;

		$average_rating = $total_rating_votes ? $total_rating_sum / $total_rating_votes : 0;

		if ($column_name == "item_id") {
			$sql  = " UPDATE " . $table_prefix . "items ";
			$sql .= " SET votes=" . $total_rating_votes . ", points=" . $total_rating_sum . ", ";
			$sql .= " rating=" . $average_rating;
			$sql .= " WHERE item_id=" . $db->tosql($column_id, INTEGER);
			$db->query($sql);
		} else {
			$sql  = " UPDATE " . $table_prefix . "articles ";
			$sql .= " SET total_votes=" . $total_rating_votes . ", total_points=" . $total_rating_sum . ", ";
			$sql .= " rating=" . $average_rating;
			$sql .= " WHERE article_id=" . $db->tosql($column_id, INTEGER);
			$db->query($sql);
		}
	}
	 
	 
	function delete_tickets($support_id)
	{
		global $db, $table_prefix, $is_admin_path, $is_sub_folder;
		$is_sub_folder = ((isset($is_admin_path) && $is_admin_path) || (isset($is_sub_folder) && $is_sub_folder)) ? true : false; 
		
		// delete attachments if available
		$sql = "SELECT file_path FROM " . $table_prefix . "support_attachments WHERE support_id=" . $db->tosql($support_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$file_path = $db->f("file_path");
			if ($is_sub_folder && !preg_match("/^[\/\\\\]/", $file_path) && !preg_match("/\:/", $file_path)) {
				$file_path = "../".$file_path;
			}
			@unlink($file_path);
		}
		
		$db->query("DELETE FROM " . $table_prefix . "support_attachments WHERE support_id=" . $db->tosql($support_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "support_messages WHERE support_id=" . $db->tosql($support_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "support WHERE support_id=" . $db->tosql($support_id, INTEGER));
	}

	function delete_chats($chats_ids)
	{
		global $db, $table_prefix;
		
		$db->query("DELETE FROM " . $table_prefix . "chats_messages WHERE chat_id IN (" . $db->tosql($chats_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "chats WHERE chat_id IN (" . $db->tosql($chats_ids, INTEGERS_LIST) . ")");
	}

	/**
	 * Return array with permissions for the Privilege Group of currently logged administrator
	 *
	 * @param void
	 * @return array
	 */	
	function get_permissions() 
	{
		global $db, $table_prefix;

		$permissions = array();
		$privilege_id = get_session("session_admin_privilege_id");
		$sql  = " SELECT block_name, permission FROM " . $table_prefix . "admin_privileges_settings ";
		$sql .= " WHERE privilege_id=" . $db->tosql($privilege_id, INTEGER, true, false);
		$db->query($sql);
		while($db->next_record()) {
			$block_name = $db->f("block_name");
			$permissions[$block_name] = $db->f("permission");
		}
		
		return $permissions;
	}

	/**
	 * Delete users with ids separated by comma
	 *
	 * @param string $user_ids
	 * @return void
	 */	
	function delete_users($users_ids) 
	{
		global $db, $table_prefix;
		$db->query("DELETE FROM " . $table_prefix . "users_addresses WHERE user_id IN (" . $db->tosql($users_ids, TEXT, false) . ")");
		$db->query("DELETE FROM " . $table_prefix . "users_files WHERE user_id IN (" . $db->tosql($users_ids, TEXT, false) . ")");
		$db->query("DELETE FROM " . $table_prefix . "users_photos WHERE user_id IN (" . $db->tosql($users_ids, TEXT, false) . ")");
		$db->query("DELETE FROM " . $table_prefix . "users_properties WHERE user_id IN (" . $db->tosql($users_ids, TEXT, false) . ")");
		$db->query("DELETE FROM " . $table_prefix . "users_ps_details WHERE user_id IN (" . $db->tosql($users_ids, TEXT, false) . ")");
		$db->query("DELETE FROM " . $table_prefix . "users_ps_properties WHERE user_id IN (" . $db->tosql($users_ids, TEXT, false) . ")");
		$db->query("DELETE FROM " . $table_prefix . "users WHERE user_id IN (" . $db->tosql($users_ids, TEXT, false) . ")");
		// the following users data should be deleted only if appropriate orders were deleted
		//$db->query("DELETE FROM " . $table_prefix . "users_commissions WHERE user_id IN (" . $db->tosql($users_ids, TEXT, false) . ")");
		//$db->query("DELETE FROM " . $table_prefix . "users_credits WHERE user_id IN (" . $db->tosql($users_ids, TEXT, false) . ")");
		//$db->query("DELETE FROM " . $table_prefix . "users_payments WHERE user_id IN (" . $db->tosql($users_ids, TEXT, false) . ")");
		//$db->query("DELETE FROM " . $table_prefix . "users_points WHERE user_id IN (" . $db->tosql($users_ids, TEXT, false) . ")");
	}

	/**
	 * Return folder name of administrative scripts
	 *
	 * @param void
	 * @return string
	 */	
	function get_admin_dir()
	{
		$admin_folder = "";
		$request_uri = get_request_uri();
		$request_uri = preg_replace("/\/+/", "/", $request_uri);
		if (strpos($request_uri,"?")){
			$request_uri = substr($request_uri,0,strpos($request_uri,"?"));
		}
		$slash_position = strrpos($request_uri, "/");
		
		if ($slash_position !== false) {
			$request_path = substr($request_uri, 0, $slash_position);
			$slash_position = strrpos($request_path, "/");
			if ($slash_position !== false) {
				$admin_folder = substr($request_path, $slash_position + 1);
			}
		}
		if (strlen($admin_folder)) {
			$admin_folder .= "/";
		} else {
			$admin_folder = "admin/";
		}
		
		return $admin_folder;
	}


function parse_admin_tabs($tabs, $current_tab = "", $tabs_in_row = 10)
{
	global $t;
	$tab_row = 0; $tab_number = 0; $active_tab = false;
	if (!strlen($current_tab)) {
		$current_tab = get_param("tab");
		if (!strlen($current_tab)) { 
			foreach ($tabs as $tab_name => $tab_info) {
				$tab_show = isset($tab_info["show"]) ? $tab_info["show"] : true;
				if ($tab_show) {
					$current_tab = $tab_name;
					break; 
				}
			}

			//$current_tab = key($tabs); 
		} 
	}

	foreach ($tabs as $tab_name => $tab_info) {
		$tab_title = $tab_info["title"];
		$tab_show = isset($tab_info["show"]) ? $tab_info["show"] : true;
		if ($tab_show) {
			$tab_number++;
			$t->set_var("tab_id", "tab_" . $tab_name);
			$t->set_var("tab_name", $tab_name);
			$t->set_var("tab_title", $tab_title);
			if ($tab_name == $current_tab) {
				$active_tab = true;
				$t->set_var("tab_class", "adminTabActive");
				$t->set_var($tab_name . "_style", "display: block;");
			} else {
				$t->set_var("tab_class", "adminTab");
				$t->set_var($tab_name . "_style", "display: none;");
			}
			$t->parse("tabs", true);
			if ($tab_number % $tabs_in_row == 0) {
				$tab_row++;
				$t->set_var("row_id", "tab_row_" . $tab_row);
				if ($active_tab) {
					$t->rparse("tabs_rows", true);
				} else {
					$t->parse("tabs_rows", true);
				}
				$t->set_var("tabs", "");
			}
		} else {
			// hide all related blocks in case if tab hidden
			$t->set_var($tab_name . "_style", "display: none;");
		}
	}
	if ($tab_number % $tabs_in_row != 0) {
		$tab_row++;
		$t->set_var("row_id", "tab_row_" . $tab_row);
		if ($active_tab) {
			$t->rparse("tabs_rows", true);
		} else {
			$t->parse("tabs_rows", true);
		}
	}
	$t->set_var("current_tab", htmlspecialchars($current_tab));
	$t->set_var("tab", htmlspecialchars($current_tab));
}

	/**
	 *convert incoming string to parsed wysiwyg editors for admin pages
	 *@var string $editors_list string containing editors separated with commas
	 *@var int $shown_type based on Administrator HTML Editor CMS setting
	 *@return void
	 */	
	function add_html_editors($editors_list, $shown_type)
	{
		global $t;
		static $CKEditor;
		$is_ckfinder = false;
		$editors_array = array();
		if($shown_type == 2 && !isset($CKEditor)) {
			if(@is_file('../ckeditor/ckeditor.js')) {
				$CKEditor_tag = '<script src="../ckeditor/ckeditor.js" type="text/javascript"></script>';
				if(@is_file('../ckfinder/ckfinder.js')) {
					$is_ckfinder = true;
					$CKEditor_tag .= "\n".'<script src="../ckfinder/ckfinder.js" type="text/javascript"></script>';
				}
				$t->set_var("CKEditor_tag", $CKEditor_tag);
				$CKEditor = 2;
			} else {
				$t->set_var("CKEditor_tag", "");
				$CKEditor = 1;
				$shown_type = 0;
			}
		}
		$editors_array = explode(',', $editors_list);
		if($shown_type == 1) {
			foreach($editors_array as $editor){
				$t->set_var($editor . "_ext_editor", "");
				$t->parse($editor . "_int_editor", false);
			}		
		}
		elseif($shown_type == 2 && $CKEditor === 2) {
			foreach($editors_array as $editor){
				if ($is_ckfinder) {
					$t->set_var("ckfinder", "CKFinder.setupCKEditor(".$editor."Editor, '../ckfinder/' );");
				} else {
					$t->set_var("ckfinder", "");
				}
				$t->parse($editor . "_ext_editor", false);
				$t->set_var($editor . "_int_editor", "");
			}
		}
		else {
			foreach($editors_array as $editor){
				$t->set_var($editor . "_int_editor", "");
				$t->set_var($editor . "_ext_editor", "");
				if($CKEditor === 1) {
					$t->set_var('editor_error', '<div style="width:557px;text-align:left;font-weight:700;font-size:8pt;padding:4px;" class="errorbg">' . EXTERNAL_CKEDITOR_TIP . '</div>');
				}
			}
		}
	}

	function get_admin_settings($settings = array(), $admin_id = "")
	{
		global $db, $table_prefix;

		// check admin_id
		if (!strlen($admin_id)) {
			$admin_id = get_session("session_admin_id");
		}

		// build SQL query
		$single_name = "";
		if (!is_array($settings) && strlen($settings)) {
			$single_name = $settings;
			$settings = array($settings);
		}
		$sql  = " SELECT * FROM ".$table_prefix."admins_settings ";
		$sql .= " WHERE admin_id=" . $db->tosql($admin_id, INTEGER);
		if (is_array($settings) && sizeof($settings) > 0) {
			$sql .= " AND (";
			for ($s = 0; $s < sizeof($settings); $s++) {
				if ($s > 0) { $sql .= " OR "; }
				$sql .= " setting_name=" . $db->tosql($settings[$s], TEXT); 
			}
			$sql .= " )";
		}
		// get admin settings
		$admin_settings = array();
		$db->query($sql);
		while ($db->next_record()) {
			$setting_name = $db->f("setting_name");
			$setting_value = $db->f("setting_value");
			$admin_settings[$setting_name] = $setting_value;
		}
		if ($single_name) {
			return get_setting_value($admin_settings, $single_name);
		} else {
			return $admin_settings;
		}
	}

	function update_admin_settings($settings, $admin_id = "")
	{
		// check admin_id
		if (!strlen($admin_id)) {
			$admin_id = get_session("session_admin_id");
		}

		global $db, $table_prefix;
		if (is_array($settings) && sizeof($settings) > 0) {
			foreach ($settings as $setting_name => $setting_value) {
				// delete value before add it again
				$sql  = " DELETE FROM ".$table_prefix."admins_settings ";
				$sql .= " WHERE admin_id=" . $db->tosql($admin_id, INTEGER);
				$sql .= " AND setting_name=" . $db->tosql($setting_name, TEXT); 
				$db->query($sql);
				// add a new value
				if (strlen($setting_value)) {
					$sql  = " INSERT INTO ".$table_prefix."admins_settings ";
					$sql .= " (admin_id, setting_name, setting_value) VALUES (";
					$sql .= $db->tosql($admin_id, INTEGER) . ",";
					$sql .= $db->tosql($setting_name, TEXT) . ",";
					$sql .= $db->tosql($setting_value, TEXT) . ") ";
					$db->query($sql);
				}
			}
		}
	}

	function remove_admin_settings($settings, $admin_id = "")
	{
		global $db, $table_prefix;
		// check admin_id
		if (!strlen($admin_id)) {
			$admin_id = get_session("session_admin_id");
		}
		foreach ($settings as $setting_name) {
			$sql  = " DELETE FROM ".$table_prefix."admins_settings ";
			$sql .= " WHERE admin_id=" . $db->tosql($admin_id, INTEGER);
			$sql .= " AND setting_name=" . $db->tosql($setting_name, TEXT); 
			$db->query($sql);
		}
	}


	// get and set records per page parameter
	function set_recs_param($page_name, $pass_parameters = "", $remove_parameters = "")
	{
		global $t;

		// prepare page url		
		if (!is_array($remove_parameters)) {
			$remove_parameters = array("page");
		}
		if (!is_array($pass_parameters)) {
			$pass_parameters = $_GET;
		}
		$page_url = $page_name; $param_number = 0;
		foreach($pass_parameters as $name => $value) {
			if (!in_array($name, $remove_parameters)) {
				$param_number++;
				if ($param_number > 1) {
					$page_url .= "&".urlencode($name)."=".urlencode($value);
				} else {
					$page_url .= "?".urlencode($name)."=".urlencode($value);
				}
			}
		}
		$page_url .= ($param_number) ? "&" : "?";

		$admin_id = get_session("session_admin_id");
		$admin_settings = get_admin_settings(array("records_per_page"));
		$records_per_page = get_setting_value($admin_settings, "records_per_page", 25);
		$recs = get_param("recs");
		$recs_values = array(10, 25, 50, 100, 200);
		if ($recs && in_array($recs, $recs_values)) { 
			$records_per_page = $recs; 
			update_admin_settings(array("records_per_page" => $recs), $admin_id);
		}
		if (!in_array($records_per_page, $recs_values)) {
			$records_per_page = 25;
		}
		for ($r = 0; $r < sizeof($recs_values); $r++) {
			$recs_value = $recs_values[$r];
			$recs_url = $page_url . "recs=".urlencode($recs_value);
			$t->set_var("recs_value", $recs_value);
			$t->set_var("recs_value_title", $recs_value);
			$t->set_var("recs_url", $recs_url);

			if ($recs_value == $records_per_page) {
				$t->set_var("recs_style", "recsShow");
			} else {
				$t->set_var("recs_style", "recsLink");
			}
			if ($r < sizeof($recs_values) - 1) {
				$t->set_var("recs_delimiter", ",");
			} else {
				$t->set_var("recs_delimiter", "");
			}
			$t->sparse("recs_values", true);
		}
		return $records_per_page;
	}


	function multi_site_settings()
	{
		global $t, $db, $table_prefix, $sitelist;

		if ($sitelist) {
			$sites = array();
			$sql = "SELECT site_id,short_name,site_name FROM " . $table_prefix . "sites ORDER BY site_id ";
			$db->query($sql);
			while ($db->next_record()) {
				$site_id = $db->f("site_id");
				$site_name = get_translation($db->f("short_name"));
				if (!strlen($site_name)) {
					$site_name = get_translation($db->f("site_name"));
				}
				if ($site_id == 1) {
					$site_name .= " (".va_constant("MASTER_SITE_MSG").")";
				}
				$sites[] = array($site_id, $site_name);
			}
			$param_site_id = get_session("session_site_id");
			set_options($sites, $param_site_id, "param_site_id");
			$t->parse("sitelist", false);
		} else {
			$t->set_var("sitelist", "");
		}
	}

	function set_search_fields($search_fields, $r, $filter_url, $search_filters)
	{
		global $t;
		$active_fields = 0;
		foreach ($search_fields as $field_key => $field_data) {
			$field_class = $field_data["class"];
			$controls = explode(",", $field_data["control"]);
			$default_value = isset($field_data["default_value"]) ? $field_data["default_value"] : "";
			$non_default = false;
			foreach ($controls as $control_name) {
				$control_value = get_param($control_name);
				if (strlen($control_value) && strval($control_value) !== strval($default_value)) {
					$non_default = true;
				}
			}
			if ($non_default) {
				$active_fields++;
			} else {
				$t->set_var($field_class, "hide-block");
			}
		}
		if ($active_fields == 0) { // if all search fields empty show default fields
			foreach ($search_fields as $field_key => $field_data) {
				$field_class = $field_data["class"];
				$default_field = isset($field_data["default_field"]) ? $field_data["default_field"] : false;
				if ($default_field) {
					$t->set_var($field_class, "");
				}
			}
		}
		if ($search_filters && $active_fields) {
			foreach ($r->parameters as $param_name => $param_data) {
				if (!$r->is_empty($param_name)) {
					$control_name = $r->get_property_value($param_name, CONTROL_NAME);
					$filter_url->add_parameter($control_name, REQUEST, $control_name);
				}
			}

			foreach ($r->parameters as $param_name => $param_data) {
				if (!$r->is_empty($param_name)) {
					$value_desc = $r->get_value_desc($param_name);
					$control_desc = $r->get_property_value($param_name, CONTROL_DESC);
					$control_name = $r->get_property_value($param_name, CONTROL_NAME);
					$default_value = $r->get_property_value($param_name, DEFAULT_VALUE);
					$control_value = get_param($control_name);
					if (!strlen($control_value) && isset($search_fields[$param_name]) && isset($search_fields[$param_name]["default_value"])) {
						// if parameter emtpy check if we can get default value
						$control_value = $search_fields[$param_name]["default_value"];
					}
					$filter_url->remove_parameter($control_name);
					$fiter_desc = $control_desc.": ".$value_desc;
					if (strval($default_value) === strval($control_value)) {
						$t->set_var("filter_class", "filter-default");
					} else {
						$t->set_var("filter_class", "");
					}
					$t->set_var("filter_url", $filter_url->get_url());
					$t->set_var("filter_desc", $fiter_desc);
					$t->parse("selected_filters", true);
					$filter_url->add_parameter($control_name, REQUEST, $control_name);
				}
			}
			// add Clear All button in the end
			foreach ($r->parameters as $param_name => $param_data) {
				if (!$r->is_empty($param_name)) {
					$control_name = $r->get_property_value($param_name, CONTROL_NAME);
					$filter_url->remove_parameter($control_name);
				}
			}
			$t->set_var("filter_class", "");
			$t->set_var("filter_url", $filter_url->get_url());
			$t->set_var("filter_desc", va_message("CLEAR_ALL_BUTTON"));
			$t->parse("selected_filters", true);
			$t->sparse("filtered_by", true);

			$t->sparse("search_info", true);
		}
		return $active_fields;
	}


