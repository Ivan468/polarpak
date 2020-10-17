<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_cms_block.php                                      ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path."includes/record.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");
	include_once($root_folder_path."messages/".$language_code."/forum_messages.php");
	include_once($root_folder_path."messages/".$language_code."/manuals_messages.php");
	include_once($root_folder_path."messages/".$language_code."/profiles_messages.php");

	check_admin_security("cms_settings");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$site_url_path = $settings["site_url"] ? $settings["site_url"] : "../";
	$t->set_var("css_file", $site_url_path . "styles/" . $settings["style_name"] . ".css");
	$t->set_var("html_editor", get_setting_value($settings, "html_editor", 1));
	$t->set_file("main","admin_cms_block.html");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", CMS_BLOCK_MSG, CONFIRM_DELETE_MSG));

	$admin_cms_blocks_url = new VA_URL("admin_cms_blocks.php", false);
	$admin_cms_blocks_url->add_parameter("sort_ord", REQUEST, "sort_ord");
	$admin_cms_blocks_url->add_parameter("sort_dir", REQUEST, "sort_dir");
	$admin_cms_blocks_url->add_parameter("page", REQUEST, "page");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_cms_blocks_href", "admin_cms_blocks.php");
	$t->set_var("admin_cms_block_href", "admin_cms_block.php");
	$t->set_var("admin_cms_pages_select_href", "admin_cms_pages_select.php");
	$t->set_var("admin_cms_blocks_url", $admin_cms_blocks_url->get_url());

	$layout_types = array(
		array("", ""),
		array("bk", DEFAULT_MSG),
		array("aa", BLOCK_AREA_MSG),
		array("bb", BREADCRUMB_MSG),
		array("no", NONE_MSG),
		array("cm", CUSTOM_LAYOUT_MSG),
	);

	$r = new VA_Record($table_prefix . "cms_blocks");
	$r->return_page = "admin_cms_blocks.php";

	$r->add_where("block_id", INTEGER);
	$modules = get_db_values("SELECT module_id, module_name FROM " . $table_prefix . "cms_modules ORDER BY module_order, module_name ", array(array("", "")));
	$r->add_select("module_id", INTEGER, $modules, MODULE_MSG);
	$r->change_property("module_id", REQUIRED, true);
	$r->change_property("module_id", REQUIRED, true);
	$r->add_textbox("block_order", INTEGER, SORT_ORDER_MSG);
	$r->change_property("block_order", REQUIRED, true);
	$r->add_textbox("block_code", TEXT, CODE_MSG);
	$r->change_property("block_code", REQUIRED, true);
	$r->add_textbox("block_name", TEXT, NAME_MSG);
	$r->change_property("block_name", REQUIRED, true);
	$r->add_textbox("php_script", TEXT, SCRIPT_NAME_MSG);
	$r->change_property("php_script", REQUIRED, true);
	$r->add_textbox("block_title", TEXT, BLOCK_TITLE_MSG);
	$r->add_checkbox("pages_all", INTEGER);
	$r->change_property("pages_all", DEFAULT_VALUE, 1);
	$r->add_hidden("pages_ids", TEXT);
	$r->change_property("pages_ids", TRANSFER, false);
	$r->change_property("pages_ids", TRIM, true);
	$r->add_select("layout_type", TEXT, $layout_types, LAYOUT_TYPE_MSG);
	$r->add_textbox("layout_template", TEXT, CUSTOM_TEMPLATE_MSG);
	$r->change_property("layout_template", BEFORE_SHOW, "set_layout_template_style");
	$r->change_property("layout_template", BEFORE_VALIDATE, "set_layout_template_required");
	$r->add_textbox("html_template", TEXT);
	$r->add_textbox("css_class", TEXT);

	$r->add_hidden("page", TEXT);
	$r->add_hidden("sort_ord", TEXT);
	$r->add_hidden("sort_dir", TEXT);

	$r->set_event(AFTER_DELETE, "remove_block_data");
	$r->set_event(BEFORE_SHOW, "set_record_controls");
	//$r->set_event(AFTER_VALIDATE, "set_record_controls");
	$r->set_event(AFTER_SELECT, "prepare_record_data");

	$r->events[AFTER_INSERT] = "update_block_data";
	$r->events[AFTER_UPDATE] = "update_block_data";
	$r->events[AFTER_DELETE] = "delete_block_data";

	$r->process();

	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }

	$tabs = array(
		"general" => array("title" => ADMIN_GENERAL_MSG), 
		"pages" => array("title" => CMS_PAGES_MSG), 
	);

	$tabs_in_row = 7; 
	parse_admin_tabs($tabs, $tab, 7);


	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

	function delete_block_data()
	{
		global $r, $db, $table_prefix;
		$block_id = $r->get_value("block_id");
		$sql  = " DELETE FROM " . $table_prefix . "cms_blocks_pages ";
		$sql .= " WHERE block_id=" . $db->tosql($block_id, INTEGER);
		$db->query($sql);
	}

	function update_block_data($params)
	{
		global $r, $db, $table_prefix;
		$event = $params["event"];
		if ($event == AFTER_INSERT) {
			if (isset($params["added"]) && !$params["added"]) { return; }
			$sql = "SELECT MAX(block_id) FROM " . $table_prefix . "cms_blocks ";
			$db->query($sql);
			if ($db->next_record()) {
				$block_id = $db->f(0);
			}	
		} else {
			$block_id = $r->get_value("block_id");
		}
		// delete all pages first before insert updated data
		$sql  = " DELETE FROM " . $table_prefix . "cms_blocks_pages ";
		$sql .= " WHERE block_id=" . $db->tosql($block_id, INTEGER);
		$db->query($sql);

		$pages_ids = $r->get_value("pages_ids");
		if ($pages_ids) {	
			$pages_values = explode(",", $pages_ids);
			foreach($pages_values as $page_id) {
				$sql  = " INSERT INTO " . $table_prefix . "cms_blocks_pages (block_id, page_id) VALUES (";
				$sql .= $db->tosql($block_id, INTEGER) . ", ";
				$sql .= $db->tosql($page_id, INTEGER) . ") ";
				$db->query($sql);
			}
		}
	}

	function set_record_controls()
	{
		global $t, $r, $db, $table_prefix;

		$pages_ids = $r->get_value("pages_ids");
		if ($pages_ids) {
			$sql  = " SELECT cp.page_id, cp.page_name ";
			$sql .= " FROM " . $table_prefix . "cms_pages cp ";
			$sql .= " WHERE cp.page_id IN (" . $db->tosql($pages_ids, INTEGERS_LIST) . ") ";
			$sql .= " ORDER BY cp.page_order, cp.page_name ";
			$db->query($sql);
			while($db->next_record())
			{
				$row_page_id = $db->f("page_id");
				$page_name = get_translation($db->f("page_name"));
		
				$t->set_var("page_id", $row_page_id);
				$t->set_var("page_name", $page_name);
				$t->set_var("page_name_js", str_replace("\"", "&quot;", $page_name));
		
				$t->parse("selected_pages", true);
				$t->parse("selected_pages_js", true);
			}
		}
	}

	function prepare_record_data()
	{
		global $r, $db, $table_prefix;
		$block_id = $r->get_value("block_id");
		$pages_ids = "";
		$sql  = " SELECT page_id FROM " . $table_prefix . "cms_blocks_pages ";
		$sql .= " WHERE block_id=" . $db->tosql($block_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$pages_ids = $db->f("page_id");
			while ($db->next_record()) {
				$pages_ids .= ",".$db->f("page_id");
			}
		}
		$r->set_value("pages_ids", $pages_ids);		
	}

	function set_layout_template_required()
	{
		global $t, $r;
		$layout_type = $r->get_value("layout_type");
		if ($layout_type == "cm" || $layout_type == "custom") {
			$r->change_property("layout_template", REQUIRED, true);
		}
	}

	function set_layout_template_style()
	{
		global $t, $r;
		$layout_type = $r->get_value("layout_type");
		if ($layout_type == "cm" || $layout_type == "custom") {
			$t->set_var("layout_template_style", "display: inline;");
		} else {
			$t->set_var("layout_template_style", "display: none;");
		}
	}

?>