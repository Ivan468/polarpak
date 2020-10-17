<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_design.php                                         ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");

	include_once("./admin_common.php");

	check_admin_security("cms_settings");

	// check for error field
	$fields = $db->get_fields($table_prefix."layouts");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "block_detault_template") {
			if ($db->DBType == "mysql") {
				$sql = "ALTER TABLE ".$table_prefix."layouts CHANGE COLUMN block_detault_template block_default_template VARCHAR(64)";
			} else if ($db->DBType == "access") {
				$sql = "ALTER TABLE ".$table_prefix."layouts RENAME COLUMN block_detault_template TO block_default_template";
			} else {
				$sql = "ALTER TABLE ".$table_prefix."layouts RENAME COLUMN block_detault_template TO block_default_template";
			}
			$db->query($sql);
		}
	}

	$param_site_id = get_session("session_site_id");
	$set_default_layout_id = get_param("set_default_layout_id");
	if ($set_default_layout_id) {
		$sql  = " SELECT layout_id FROM " . $table_prefix . "layouts WHERE layout_id=" . intval($set_default_layout_id);
		$db->query($sql);
		if($db->next_record()) {
			
			$sql  = " DELETE FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type='global' AND setting_name='layout_id' AND site_id=" . $db->tosql($param_site_id, INTEGER);
			$db->query($sql);	
			
			$sql  = " INSERT INTO " . $table_prefix . "global_settings (setting_type, setting_name, setting_value, site_id) VALUES (";
			$sql .= "'global', 'layout_id'," . $db->tosql($set_default_layout_id, TEXT) . "," . $db->tosql($param_site_id, INTEGER) . ")";				
			$db->query($sql);
			set_session("session_settings", "");
		}
		header("Location: admin_designs.php");
		exit;
	}

	$top_menu_types = 
		array( 
			array(0, DONT_SHOW_LINKS_MSG), array(1, IMAGE_LINKS_FIRST_MSG), array(2, TEXT_LINKS_FIRST_MSG), array(3, IMAGE_AND_LINKS_MSG)
		);


	$t = new VA_Template($settings["admin_templates_dir"]);
	if (!file_exists($t->get_template_path(). "/admin_designs.html")) {
		$t->set_template_path("../templates/admin");
	}
	$t->set_file("main","admin_design.html");

	$t->set_var("admin_design_href", "admin_design.php");
	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_designs_href", "admin_designs.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", DESIGN_MSG, CONFIRM_DELETE_MSG));

	$r = new VA_Record($table_prefix . "layouts");
	$r->return_page = "admin_designs.php";

	// load data to listbox
	$dir_index = 0;
	$directory_list_values[$dir_index] = array("", " --- Select from list --- ");
	$dir_index++;

	if ($dir = @opendir("../templates")) 
	{
		while ($file = readdir($dir)) 
		{
			if ($file != "." && $file != ".." && is_dir("../templates/" . $file) && $file != "admin") 
			{ 
				$directory_list_values[$dir_index] = array($file, $file);
				$dir_index++;
			} 
		}
		closedir($dir);
	}

	$layout_id = get_param("layout_id"); $scheme_values = array();
	if (strlen($layout_id)) {
		$filepath = "";
		$sql  = " SELECT layout_name, style_name FROM " . $table_prefix . "layouts "; 
		$sql .= " WHERE layout_id=" . $db->tosql($layout_id, INTEGER); 
		$db->query($sql);
		if ($db->next_record()) {
			$style_name = $db->f("style_name");
			$layout_name = $db->f("layout_name");
			$layout_name_lc = strtolower($layout_name);
			if ($style_name) {
				if (file_exists("../styles/".$style_name)) {
					$filepath = "../styles/".$style_name;
				} else if (file_exists("../styles/".$style_name.".css")) {
					$filepath = "../styles/".$style_name.".css";
				} else if (file_exists("../styles/".$layout_name_lc.".css")) {
					$filepath = "../styles/".$layout_name_lc.".css";
				}
			}
		}
		                
		if ($filepath) {
			$filecontent = implode("", file($filepath));
			if (preg_match("/schemes: (\{[^\}]+\})/Uis", $filecontent, $match)) {
				$schemes_json = $match[1];
				$schemes = json_decode($schemes_json, true);
				if (is_array($schemes)) {
					$scheme_values[] = array("", "");
					foreach ($schemes as $scheme_code => $scheme_name) {
						$scheme_values[] = array($scheme_code, $scheme_name);
					}
				}
			}
		}
	}


	$r->add_where("layout_id", INTEGER);
	$r->add_checkbox("show_for_user", INTEGER);               
	$r->add_textbox("layout_name", TEXT, DESIGN_NAME_MSG);
	$r->change_property("layout_name", REQUIRED, true);
	$r->add_textbox("user_layout_name", TEXT);
	$r->add_textbox("style_name", TEXT, STYLE_NAME_MSG);
	//$r->change_property("style_name", REQUIRED, true);
	$r->add_select("scheme_name", TEXT, $scheme_values, CHANGE_ACTIVE_SCHEME_MSG);
	if (!count($scheme_values)) {
		$r->change_property("scheme_name", SHOW, false);
	}
	$r->add_textbox("templates_dir", TEXT, TEMPLATES_DIRECTORY_MSG);
	$r->change_property("templates_dir", REQUIRED, true);
	$r->add_textbox("admin_templates_dir", TEXT, ADMIN_TEMPLATES_DIRECTORY_MSG);
	$r->change_property("admin_templates_dir", REQUIRED, true);

	$r->add_textbox("block_default_template", TEXT, DEFAULT_MSG);
	$r->add_textbox("block_area_template", TEXT, BLOCK_AREA_MSG);
	$r->add_textbox("block_breadcrumb_template", TEXT, BREADCRUMB_MSG);

	set_options($directory_list_values, "", "directory_list");

	$r->add_checkbox("sites_all", INTEGER);
	
	$r->get_form_values();

	$operation = get_param("operation");
	$layout_id = get_param("layout_id");
	$return_page = "admin_designs.php";
	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }

	if ($sitelist) {
		$selected_sites = array();
		if (strlen($operation)) {
			$sites = get_param("sites");
			if ($sites) {
				$selected_sites = explode(",", $sites);
			}
		} elseif ($layout_id) {
			$sql  = "SELECT site_id FROM " . $table_prefix . "layouts_sites ";
			$sql .= " WHERE layout_id=" . $db->tosql($layout_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$selected_sites[] = $db->f("site_id");
			}
		}
	}
	
	if (strlen($operation))
	{
		if ($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		}
		elseif ($operation == "delete" && $layout_id)
		{
			$db->query("DELETE FROM " . $table_prefix . "layouts WHERE layout_id=" . $db->tosql($layout_id, INTEGER));		
			$db->query("DELETE FROM " . $table_prefix . "layouts_sites WHERE layout_id=" . $db->tosql($layout_id, INTEGER));
		
			header("Location: " . $return_page);
			exit;
		}

		$r->validate();

		if (!$r->is_empty("templates_dir") && !file_exists("../" . $r->get_value("templates_dir"))) {
			$r->errors .= FOLDER_DOESNT_EXIST_MSG . " <b>" . $r->get_value("templates_dir") . "</b><br>";
		}

		if (!$r->is_empty("admin_templates_dir") && !file_exists($r->get_value("admin_templates_dir"))) {
			$r->errors .= FOLDER_DOESNT_EXIST_MSG ." <b>" . $r->get_value("admin_templates_dir") . "</b><br>";
		}
		
		if (!strlen($r->errors))
		{
			if (!$sitelist) {
				$r->set_value("sites_all", 1);
			}
			if (strlen($r->get_value("layout_id"))) {
				$r->update_record();
				set_session("session_settings", "");
			} else {
				$r->insert_record();
				$layout_id = $db->last_insert_id();
				$r->set_value("layout_id", $layout_id);
			}
			
			// update sites
			if ($sitelist) {
				$db->query("DELETE FROM " . $table_prefix . "layouts_sites WHERE layout_id=" . $db->tosql($layout_id, INTEGER));
				for ($st = 0; $st < sizeof($selected_sites); $st++) {
					$site_id = $selected_sites[$st];
					if (strlen($site_id)) {
						$sql  = " INSERT INTO " . $table_prefix . "layouts_sites (layout_id, site_id) VALUES (";
						$sql .= $db->tosql($layout_id, INTEGER) . ", ";
						$sql .= $db->tosql($site_id, INTEGER) . ") ";
						$db->query($sql);
					}
				}
			}

			header("Location: " . $return_page);
			exit;
		}
	}
	elseif (strlen($r->get_value("layout_id")))
	{
		$r->get_db_values();
	}
	else // new design (set default values)
	{
		$r->set_value("admin_templates_dir", "../templates/admin");
		$r->set_value("sites_all", 1);
	}

	$r->set_form_parameters();
	
	if (strlen($layout_id))	
	{
		$t->set_var("save_button", UPDATE_BUTTON);
		$t->parse("delete", false);	
	}
	else
	{
		$t->set_var("save_button", ADD_NEW_MSG);
		$t->set_var("delete", "");	
	}

	if ($sitelist) {
		$sites = array();
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
	
	$tabs = array("general" => ADMIN_GENERAL_MSG);
	$tabs["block_layout"] = BLOCK_LAYOUT_MSG;
	if ($sitelist) {
		$tabs["sites"] = ADMIN_SITES_MSG;
	}
	foreach ($tabs as $tab_name => $tab_title) {
		$t->set_var("tab_id", "tab_" . $tab_name);
		$t->set_var("tab_name", $tab_name);
		$t->set_var("tab_title", $tab_title);
		if ($tab_name == $tab) {
			$t->set_var("tab_class", "adminTabActive");
			$t->set_var($tab_name . "_style", "display: block;");
		} else {
			$t->set_var("tab_class", "adminTab");
			$t->set_var($tab_name . "_style", "display: none;");
		}
		$t->parse("tabs", $tab_title);
	}
	$t->set_var("tab", $tab);
	
	if ($sitelist) {
		$t->parse('sitelist');
	}
	
	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

?>