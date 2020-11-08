<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_cms_layout.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/editgrid.php");
	include_once($root_folder_path . "includes/tabs_functions.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");
	include_once($root_folder_path."messages/".$language_code."/download_messages.php");
	include_once("./admin_common.php");

	check_admin_security("cms_settings");

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_cms_layout.html");

	$t->set_var("admin_href", $admin_site_url . "admin.php");
	$t->set_var("admin_cms_layout_href", "admin_cms_layout.php");
	$t->set_var("admin_cms_layouts_href", "admin_cms_layouts.php");

	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", va_constant("CMS_LAYOUT_MSG"), va_constant("CONFIRM_DELETE_MSG")));
	
	$layout_id = get_param("layout_id");
	if ($layout_id) {
		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "cms_pages_settings ";
		$sql .= " WHERE layout_id=" . $db->tosql($layout_id, INTEGER);
		$layout_pages = get_db_value($sql);
	}

	$param_site_id = get_session("session_site_id");

	$sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type='global' AND setting_name='layout_id' AND site_id=" . $db->tosql($param_site_id, INTEGER);
	$default_layout_id = get_db_value($sql);

	$sql  = " SELECT templates_dir, admin_templates_dir FROM " . $table_prefix . "layouts ";
	$sql .= " WHERE layout_id=" . $db->tosql($default_layout_id, INTEGER);
	$db->query($sql);

	if ($db->next_record()) {
		$templates_dir = $db->f("templates_dir");
		$admin_templates_dir = $db->f("admin_templates_dir");
		if (substr($templates_dir, -1, 1) != "/") {
			$templates_dir = $templates_dir ."/";
		}
		if (substr($admin_templates_dir, -1, 1) != "/") {
			$admin_templates_dir = $admin_templates_dir ."/";
		}
		
		$t->set_var("current_templates_dir", $templates_dir);
		$t->set_var("current_admin_templates_dir", $admin_templates_dir);

	}


	// set up html form parameters
	$r = new VA_Record($table_prefix . "cms_layouts");
	$r->add_where("layout_id", INTEGER);
	$r->add_textbox("layout_order", INTEGER, va_constant("OPTION_ORDER_MSG"));
	$r->change_property("layout_order", REQUIRED, true);
	$r->add_textbox("layout_name", TEXT, va_constant("OPTION_NAME_MSG"));
	$r->change_property("layout_name", REQUIRED, true);
	$r->add_textbox("layout_template", TEXT, va_constant("STOREFRONT_TEMPLATE_MSG"));
	$r->change_property("layout_template", REQUIRED, true);
	$r->add_textbox("admin_template", TEXT, va_constant("BACKEND_TEMPLATE_MSG"));
	$r->change_property("admin_template", REQUIRED, true);

	$r->add_hidden("sort_dir", TEXT);
	$r->add_hidden("sort_ord", TEXT);
	$r->add_hidden("page", TEXT);
	$r->return_page = "admin_cms_layouts.php";

	$r->get_form_values();

	$ipv = new VA_Record($table_prefix . "cms_frames", "blocks");
	$ipv->add_where("frame_id", INTEGER);
	$ipv->add_hidden("layout_id", INTEGER);
	$ipv->change_property("layout_id", USE_IN_INSERT, true);

	$ipv->add_textbox("frame_name", TEXT, va_constant("NAME_MSG"));
	$ipv->change_property("frame_name", REQUIRED, true);
	$ipv->add_textbox("tag_name", TEXT, va_constant("TAG_NAME_MSG"));
	$ipv->change_property("tag_name", REQUIRED, true);
	$ipv->add_checkbox("blocks_allowed", INTEGER, va_constant("CMS_BLOCKS_MSG"));
	
	$more_blocks = get_param("more_blocks");
	$number_blocks = get_param("number_blocks");

	$eg = new VA_EditGrid($ipv, "blocks");
	$eg->get_form_values($number_blocks);

	$operation = get_param("operation");
	$layout_id = get_param("layout_id");
	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }

	$return_page = $r->get_return_url();

	if(strlen($operation) && !$more_blocks)
	{
		$tab = "general";
		if($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		}
		else if($operation == "delete" && $layout_id)
		{
			if (!$layout_pages) {
				$db->query("DELETE FROM " . $table_prefix . "cms_layouts WHERE layout_id=" . $db->tosql($layout_id, INTEGER));		
				$db->query("DELETE FROM " . $table_prefix . "cms_frames WHERE layout_id=" . $db->tosql($layout_id, INTEGER));		
			}
			header("Location: " . $return_page);
			exit;
		}

		$is_valid = $r->validate();
		$is_valid = ($eg->validate() && $is_valid); 

		if($is_valid)
		{
			if(strlen($layout_id))
			{
				$r->update_record();
				$eg->set_values("layout_id", $layout_id);
				$eg->update_all($number_blocks);
			}
			else
			{
				$r->insert_record();
				$layout_id = $db->last_insert_id();
				$eg->set_values("layout_id", $layout_id);
				$eg->insert_all($number_blocks);
			}

			header("Location: " . $return_page);
			exit;
		}
	}
	else if(strlen($layout_id) && !$more_blocks)
	{
		$r->get_db_values();
		$eg->set_value("layout_id", $layout_id);
		$eg->change_property("frame_id", USE_IN_SELECT, true);
		$eg->change_property("frame_id", USE_IN_WHERE, false);
		$eg->change_property("layout_id", USE_IN_WHERE, true);
		$eg->change_property("layout_id", USE_IN_SELECT, true);
		$number_blocks = $eg->get_db_values();
		if ($number_blocks == 0) {
			$number_blocks = 5;
		}
	}
	else if($more_blocks)
	{
		$number_blocks += 5;
	}
	else // set default values
	{
		$sql  = " SELECT MAX(layout_order) FROM " . $table_prefix . "cms_layouts ";
		$layout_order = get_db_value($sql);
		$layout_order = ($layout_order) ? ($layout_order + 1) : 1;
		$r->set_value("layout_order", $layout_order);

		$number_blocks = 5;
	}
	$t->set_var("number_blocks", $number_blocks);


	$eg->set_parameters_all($number_blocks);
	$r->set_parameters();

	if(strlen($layout_id)) {
		$t->set_var("save_button", va_constant("UPDATE_BUTTON"));
	} else {
		$t->set_var("save_button", va_constant("ADD_BUTTON"));
	}
	if (strlen($layout_id) && !$layout_pages) {
		$t->parse("delete", false);	
	}

	$tabs = array(
		"general" => array("title" => va_constant("ADMIN_GENERAL_MSG")), 
	);
	parse_tabs($tabs, $tab);

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");


?>