<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  block.php                                                ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");

	// set headers for block
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");
	header("Content-Type: text/html; charset=" . CHARSET);

	$pb_id = get_param("pb_id");
	$cms_page_code = get_param("cms_page_code");
	$is_block_reload = true;
	$ajax_data = array(); // use this array to return response as JSON object

	$layout_templates = array(
		"bk" => get_setting_value($settings, "block_default_template", "layout_block_default.html"),
		"aa" => get_setting_value($settings, "block_area_template", "layout_block_area.html"),
		"bb" => get_setting_value($settings, "block_breadcrumb_template", "layout_block_default.html"),
		"no" => "",
	);

	// get block data
	$page_blocks = array();
	$sql  = " SELECT cpb.pb_id, cb.block_code, cb.php_script, cpb.frame_id, cpb.block_key, ";
	$sql .= " cb.css_class AS cms_css_class, cb.html_template AS cms_html_template, ";
	$sql .= " cpb.css_class AS page_block_class, cpb.html_template AS page_html_template, ";
	$sql .= " cb.layout_type AS cms_layout_type, cb.layout_template AS cms_layout_template, ";
	$sql .= " cpb.layout_type AS page_layout_type, cpb.layout_template AS page_layout_template, ";
	$sql .= " cpb.block_style, cb.block_title, cpb.block_title AS page_block_title ";
	$sql .= " FROM (" . $table_prefix . "cms_pages_blocks cpb ";
	$sql .= " INNER JOIN " . $table_prefix . "cms_blocks cb ON cpb.block_id=cb.block_id) ";
	$sql .= " WHERE cpb.pb_id=" . $db->tosql($pb_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$block = $db->Record;
		$php_script = $db->f("php_script");
		$cms_block_code = $db->f("block_code");
		$cms_css_class = $db->f("cms_css_class");
		if (!$cms_css_class) { $cms_css_class = "bk-".str_replace("_", "-", $cms_block_code); }
		// get template for block
		$html_template = $db->f("page_html_template");
		if (!$html_template) {
			$html_template = $db->f("cms_html_template");
		}
		$block["html_template"] = $html_template;
		// get block layout template
		$layout_type = $db->f("page_layout_type"); 
		$layout_template = "";
		if ($layout_type) {
			if ($layout_type == "cm") {
				$layout_template = $db->f("page_layout_template");
			}
		} else {
			$layout_type = $db->f("cms_layout_type");
			if ($layout_type == "cm") {
				$layout_template = $db->f("cms_layout_template");
			}
		}
		$block_style = $db->f("block_style");
		$page_block_class = $db->f("page_block_class");
		$block_key = $db->f("block_key");
		$block_title = $db->f("block_title");
		$page_block_title = $db->f("page_block_title");
		if (strlen($page_block_title)) { $block_title = $page_block_title; }
	} else {
		echo "Block wasn't found";
		return;
	}


	// get block variables
	$vars = array();
	$sql  = " SELECT cbs.pb_id, cbs.variable_name, cbs.variable_value ";
	$sql .= " FROM " . $table_prefix . "cms_blocks_settings cbs ";
	$sql .= " WHERE cbs.pb_id=" . $db->tosql($pb_id, INTEGER);
	$db->query($sql);
	while ($db->next_record()) {
		$variable_name = $db->f("variable_name");
		$variable_value = $db->f("variable_value");
		if (isset($vars[$variable_name])) {
			if (is_array($vars[$variable_name])) {
				$vars[$variable_name][] = $variable_value;
			} else {
				$vars[$variable_name] = array($vars[$variable_name]);
				$vars[$variable_name][] = $variable_value;
			}
		} else {
			$vars[$variable_name] = $variable_value;
		}
	}

	// added two additional vars to array
	$vars["block_key"] = $block_key;
	$vars["tag_name"] = "block";

	$block_parsed = false;
	$t = new VA_Template($settings["templates_dir"]);
	$t->set_var("pb_id", $pb_id);
	$t->set_var("block_style", $block_style);
	$block_css_class = ""; $var_css_class = ""; $extra_css_class = ""; // clear before include block
	$t->block_clear("block_head");
	$t->block_clear("block_foot");
	if (file_exists("./blocks_custom/".$php_script)) {
		include("./blocks_custom/".$php_script);
	} else {
		include("./blocks/".$php_script);
	}
	if ($block_parsed) {
		// check class for block
		if ($page_block_class || $var_css_class) {
			$cms_css_class = trim($page_block_class." ".$var_css_class);
		} else if ($block_css_class) {
			$cms_css_class = $block_css_class;
		}
		if ($extra_css_class) { $cms_css_class .= " ".$extra_css_class; }
		if (!$layout_type) { $layout_type = "bk"; }
		if (!$layout_template && $layout_type) {
			$layout_template = isset($layout_templates[$layout_type]) ? $layout_templates[$layout_type] : "";
		}
		// check default title if it wasn't set
		if (!strlen($block_title) && isset($default_title)) { $block_title = $default_title; }
		// set final block title
		$t->set_block("block_title", get_translation($block_title));
		$t->parse("block_title", false);
		// check if we need to hide block title
		$parsed_title = $t->get_var("block_title");
		if (!strlen($parsed_title)) { $cms_css_class = "hidden-title ". $cms_css_class; }
		$t->set_var("block_class", $cms_css_class);
		if ($layout_template) {
			$t->set_file($layout_template, $layout_template);
			if ($t->block_exists("block_head")) {
				$t->parse_to("block_head", "head_tag", false);	
			} else {
				$t->set_var("head_tag", $parsed_title);	
			}
			$t->parse_to("block_body", "body_tag", false);	
			if ($t->block_exists("block_foot")) {
				$t->parse_to("block_foot", "foot_tag", false);	
			}
			$block_tag = $layout_template;
		} else {
			$block_tag = "block_body";
		}
		// return response as JSON object
		$t->parse($block_tag, false);
		$ajax_data["pb_id"] = $pb_id;	
		$ajax_data["html_id"] = "pb_".$pb_id;	
		$ajax_data["block"] = $t->get_var($block_tag);	
		echo json_encode($ajax_data);	
	}
