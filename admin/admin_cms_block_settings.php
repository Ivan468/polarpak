<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_cms_block_settings.php                             ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path."includes/sorter.php");
	include_once($root_folder_path."includes/navigator.php");
	include_once($root_folder_path."includes/record.php");
	include_once($root_folder_path."includes/cms_functions.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");
	include_once($root_folder_path."messages/".$language_code."/download_messages.php");
	include_once($root_folder_path."messages/".$language_code."/profiles_messages.php");
	include_once($root_folder_path."messages/".$language_code."/install_messages.php");

	include_once("./admin_common.php");

	check_admin_security("cms_settings");

	// check for options upgrade
	$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='categories_list' "; 
	$categories_block_id = get_db_value($sql);
	$sql = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties WHERE variable_name='desc_type' AND block_id=".$db->tosql($categories_block_id, INTEGER); 
	$desc_property_id = get_db_value($sql);
	if ($categories_block_id && !$desc_property_id) {
		$sqls = array();
		$sql = " SELECT MAX(property_order) FROM ".$table_prefix."cms_blocks_properties WHERE block_id=".$db->tosql($categories_block_id, INTEGER); 
		$property_order = get_db_value($sql);
		$sql = " SELECT MAX(property_id) FROM ".$table_prefix."cms_blocks_properties "; 
		$property_id = get_db_value($sql);
		$sql = " SELECT MAX(value_id) FROM ".$table_prefix."cms_blocks_values "; 
		$value_id = get_db_value($sql);

		// global property how show navigation bar
		$property_id++; $property_order++;
		$sql = "INSERT INTO " . $table_prefix . "cms_blocks_properties (";
		$sql .= "property_id,block_id,property_order,property_name,control_type,variable_name,required) VALUES (";
		$sql .= $db->tosql($property_id, INTEGER).",";
		$sql .= $db->tosql($categories_block_id, INTEGER).",";
		$sql .= $db->tosql($property_order, INTEGER).",";
		$sql .= $db->tosql("DESCRIPTION_MSG", TEXT).",";
		$sql .= $db->tosql("LISTBOX", TEXT).",";
		$sql .= $db->tosql("desc_type", TEXT).",";
		$sql .= $db->tosql(0, INTEGER).")";
		$sqls[] = $sql;

		$value_id++;
		$sql = "INSERT INTO " . $table_prefix . "cms_blocks_values ";
		$sql .= "(value_id,property_id,value_order,value_name,variable_value,is_default_value) VALUES (";
		$sql .= $db->tosql($value_id, INTEGER).",";
		$sql .= $db->tosql($property_id, INTEGER).",";
		$sql .= $db->tosql(1, INTEGER).",";
		$sql .= $db->tosql("DONT_SHOW_DESC_MSG", TEXT).",";
		$sql .= "'0', 0) ";
		$sqls[] = $sql;	

		$value_id++;
		$sql = "INSERT INTO " . $table_prefix . "cms_blocks_values ";
		$sql .= "(value_id,property_id,value_order,value_name,variable_value) VALUES (";
		$sql .= $db->tosql($value_id, INTEGER).",";
		$sql .= $db->tosql($property_id, INTEGER).",";
		$sql .= $db->tosql(2, INTEGER).",";
		$sql .= $db->tosql("SHORT_DESCRIPTION_MSG", TEXT).",";
		$sql .= $db->tosql("1", TEXT).") ";
		$sqls[] = $sql;	

		$value_id++;
		$sql = "INSERT INTO " . $table_prefix . "cms_blocks_values ";
		$sql .= "(value_id,property_id,value_order,value_name,variable_value) VALUES (";
		$sql .= $db->tosql($value_id, INTEGER).",";
		$sql .= $db->tosql($property_id, INTEGER).",";
		$sql .= $db->tosql(3, INTEGER).",";
		$sql .= $db->tosql("FULL_DESCRIPTION_MSG", TEXT).",";
		$sql .= $db->tosql("2", TEXT).") ";
		$sqls[] = $sql;	

		foreach ($sqls as $sql) {
			$db->query($sql);
		}
	}
	// end options upgrade

	// check for banner slider options upgrade
	$sql = " SELECT block_id FROM ".$table_prefix."cms_blocks WHERE block_code='banners' "; 
	$banner_block_id = get_db_value($sql);
	$sql = " SELECT property_id FROM ".$table_prefix."cms_blocks_properties WHERE variable_name='slider_type' AND block_id=".$db->tosql($banner_block_id, INTEGER); 
	$slider_type_id = get_db_value($sql);
	if ($banner_block_id && !$slider_type_id) {
		$sqls = array();
		$sql = " SELECT MAX(property_order) FROM ".$table_prefix."cms_blocks_properties WHERE block_id=".$db->tosql($banner_block_id, INTEGER); 
		$property_order = get_db_value($sql);
		$sql = " SELECT MAX(property_id) FROM ".$table_prefix."cms_blocks_properties "; 
		$property_id = get_db_value($sql);
		$sql = " SELECT MAX(value_id) FROM ".$table_prefix."cms_blocks_values "; 
		$value_id = get_db_value($sql);

		$property_id++; $property_order++;
		$sql = "INSERT INTO " . $table_prefix . "cms_blocks_properties (";
		$sql .= "property_id,block_id,property_order,property_name,control_type,variable_name,required) VALUES (";
		$sql .= $db->tosql($property_id, INTEGER).",";
		$sql .= $db->tosql($banner_block_id, INTEGER).",";
		$sql .= $db->tosql($property_order, INTEGER).",";
		$sql .= $db->tosql("SLIDER_TYPE_MSG", TEXT).",";
		$sql .= $db->tosql("LISTBOX", TEXT).",";
		$sql .= $db->tosql("slider_type", TEXT).",";
		$sql .= $db->tosql(0, INTEGER).")";
		$sqls[] = $sql;

		$value_id++;
		$sql = "INSERT INTO " . $table_prefix . "cms_blocks_values ";
		$sql .= "(value_id,property_id,value_order,value_name,variable_value,is_default_value) VALUES (";
		$sql .= $db->tosql($value_id, INTEGER).",";
		$sql .= $db->tosql($property_id, INTEGER).",";
		$sql .= $db->tosql(1, INTEGER).",";
		$sql .= $db->tosql("SLIDESHOW_MSG", TEXT).",";
		$sql .= "'slideshow', 0) ";
		$sqls[] = $sql;	


		$property_id++; $property_order++;
		$sql = "INSERT INTO " . $table_prefix . "cms_blocks_properties (";
		$sql .= "property_id,block_id,property_order,property_name,after_control_html,control_type,variable_name,required) VALUES (";
		$sql .= $db->tosql($property_id, INTEGER).",";
		$sql .= $db->tosql($banner_block_id, INTEGER).",";
		$sql .= $db->tosql($property_order, INTEGER).",";
		$sql .= $db->tosql("TRANSITION_DELAY_MSG", TEXT).",";
		$sql .= $db->tosql("<br/>{TRANSITION_DELAY_DESC}", TEXT).",";
		$sql .= $db->tosql("TEXTBOX", TEXT).",";
		$sql .= $db->tosql("transition_delay", TEXT).",";
		$sql .= $db->tosql(0, INTEGER).")";
		$sqls[] = $sql;

		$property_id++; $property_order++;
		$sql = "INSERT INTO " . $table_prefix . "cms_blocks_properties (";
		$sql .= "property_id,block_id,property_order,property_name,after_control_html,control_type,variable_name,required) VALUES (";
		$sql .= $db->tosql($property_id, INTEGER).",";
		$sql .= $db->tosql($banner_block_id, INTEGER).",";
		$sql .= $db->tosql($property_order, INTEGER).",";
		$sql .= $db->tosql("TRANSITION_DURATION_MSG", TEXT).",";
		$sql .= $db->tosql("<br/>{TRANSITION_DURATION_DESC}", TEXT).",";
		$sql .= $db->tosql("TEXTBOX", TEXT).",";
		$sql .= $db->tosql("transition_duration", TEXT).",";
		$sql .= $db->tosql(0, INTEGER).")";
		$sqls[] = $sql;

		foreach ($sqls as $sql) {
			$db->query($sql);
		}
	}
	// end banner options upgrade

	$layout_types = array(
		array("", ""),
		array("bk", DEFAULT_MSG),
		array("aa", BLOCK_AREA_MSG),
		array("bb", BREADCRUMB_MSG),
		array("no", NONE_MSG),
		array("cm", CUSTOM_LAYOUT_MSG),
	);

	// begin delete selected properties
	$operation = get_param("operation");
	$frame_id = get_param("frame_id");
	$block_id = get_param("block_id");
	$block_position = get_param("block_position");
	$params_string = get_param("block_params");
	$selected_properties = "";
	$periods = "";

	if ($frame_id) {
		// old way passing parameters
		$block_properties = get_param("block_properties");
		$block_params = get_cms_params($params_string);
		if (strlen($block_properties)) {
			$selected_properties = parse_cms_block_properties($block_properties, "option", true);
			$periods = parse_cms_block_properties($block_properties, "period");
		}
	} else {
		$json_settings = json_decode($params_string, true);
		$block_params = $json_settings;
		$selected_properties = isset($json_settings["properties"]) ? $json_settings["properties"] : "";
		$periods = isset($json_settings["periods"]) ? $json_settings["periods"] : "";
	}
	$current_date = va_time();
	
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_cms_block_settings.html");
	$t->set_var("admin_href", "admin.php");
	$t->set_var("frame_id", htmlspecialchars($frame_id));
	$t->set_var("block_id", htmlspecialchars($block_id));
	$t->set_var("block_position", htmlspecialchars($block_position));
	$required_message_js = strip_tags(REQUIRED_MESSAGE);
	$required_message_js = str_replace("\"", "\\\"", $required_message_js);
	$t->set_var("required_message_js", $required_message_js);
	$t->set_var("current_date", va_date("YYYY-MM-DD, H:mm, WWWW", $current_date));

	$tag_name = get_setting_value($block_params, "tag_name", "");
	$layout_type = get_setting_value($block_params, "layout_type", "");
	$layout_template = get_setting_value($block_params, "layout_template", "");
	$html_template = get_setting_value($block_params, "html_template", "");
	$css_class = get_setting_value($block_params, "css_class", "");
	$block_style = get_setting_value($block_params, "block_style", "");
	$block_title = get_setting_value($block_params, "block_title", "");
	$t->set_var("tag_name", htmlspecialchars($tag_name));
	// set layout settings
	set_options($layout_types, $layout_type, "layout_type");
	if ($layout_type == "cm" || $layout_type == "custom") {
		$t->set_var("layout_template_style", "display: inline;");
	} else {
		$t->set_var("layout_template_style", "display: none;");
	}
	$t->set_var("layout_template", htmlspecialchars($layout_template));
	$t->set_var("html_template", htmlspecialchars($html_template));
	$t->set_var("css_class", htmlspecialchars($css_class));
	$t->set_var("block_style", htmlspecialchars($block_style));
	$t->set_var("block_title", htmlspecialchars($block_title));

		// connection for properties
		$dbp = new VA_SQL();
		$dbp->DBType     = $db->DBType;
		$dbp->DBDatabase = $db->DBDatabase;
		$dbp->DBUser     = $db->DBUser;
		$dbp->DBPassword = $db->DBPassword;
		$dbp->DBHost     = $db->DBHost;
		$dbp->DBPort       = $db->DBPort;
		$dbp->DBPersistent = $db->DBPersistent;
  
		$eol = get_eol();

		// check product properites
		$properties_ids = "";
		$is_properties = false;
		$t->set_var("properties", "");

		$options = array(); 
		$sql  = " SELECT cmp.* ";
		$sql .= " FROM " . $table_prefix . "cms_blocks_properties cmp ";
		$sql .= " WHERE cmp.block_id=" . $db->tosql($block_id, INTEGER);
		$sql .= " ORDER BY cmp.property_order, cmp.property_id ";
		$dbp->query($sql);
		while ($dbp->next_record()) {
			$property_id = $dbp->f("property_id");
				$option = array(
					"property_id" => $property_id,
					"property_name" => get_translation($dbp->f("property_name")),
					"parent_property_id" => $dbp->f("parent_property_id"),
					"parent_value_id" => $dbp->f("parent_value_id"),
					"default_value" => $dbp->f("default_value"),
					"property_class" => $dbp->f("property_class"),
					"property_style" => $dbp->f("property_style"),
					"control_type" => $dbp->f("control_type"),
					"control_style" => $dbp->f("control_style"),
					"required" => $dbp->f("required"),
					"start_html" => $dbp->f("start_html"),
					"middle_html" => $dbp->f("middle_html"),
					"before_control_html" => $dbp->f("before_control_html"),
					"after_control_html" => $dbp->f("after_control_html"),
					"end_html" => get_translation($dbp->f("end_html")),
					"onchange_code" => $dbp->f("onchange_code"),
					"onclick_code" => $dbp->f("onclick_code"),
					"control_code" => $dbp->f("control_code"),
					"values" => array(),
				);
				$options[$property_id] = $option;
		}

		if (sizeof($options) > 0)
		{
			$is_properties = true;
			foreach ($options as $property_id => $option) 
			{
				$property_id = $option["property_id"];
				//$object_id = $form_id . "_" . $property_id;
				$object_id = $property_id;
				$property_block_id = "pr_" . $object_id;
				$property_name = $option["property_name"];
				parse_value($property_name);
				$parent_property_id = $option["parent_property_id"];
				$parent_value_id = $option["parent_value_id"];

				$default_value = $option["default_value"];
				$property_class = $option["property_class"];
				$property_style = $option["property_style"];
				$control_type = $option["control_type"];
				$control_style = $option["control_style"];
				$property_required = $option["required"];
				$start_html = $option["start_html"];
				$middle_html = $option["middle_html"];
				$before_control_html = $option["before_control_html"];
				$after_control_html = $option["after_control_html"];
				$end_html = $option["end_html"];
				parse_value($start_html);
				parse_value($middle_html);
				parse_value($before_control_html);
				parse_value($after_control_html);
				parse_value($end_html);

				$onchange_code = $option["onchange_code"];
				$onclick_code = $option["onclick_code"];
				$control_code = $option["control_code"];
				
				if ($properties_ids) $properties_ids .= ",";
				$properties_ids .= $property_id;
				$tags_replace = array("{block_id}", "{option_id}", "{property_id}");
				$tags_values  = array($block_id, $property_id, $property_id);
				if ($onchange_code) {	
					$onchange_code = str_replace($tags_replace, $tags_values, $onchange_code); 
				}
				if ($onclick_code) {	
					$onclick_code = str_replace($tags_replace, $tags_values, $onclick_code); 
				}
				if ($control_code) {	
					$control_code = str_replace($tags_replace, $tags_values, $control_code); 
				}
				if ($start_html) {	
					$start_html = str_replace($tags_replace, $tags_values, $start_html); 
				}
				if ($middle_html) {	
					$middle_html = str_replace($tags_replace, $tags_values, $middle_html); 
				}
				if ($before_control_html) {	
					$before_control_html = str_replace($tags_replace, $tags_values, $before_control_html); 
				}
				if ($after_control_html) {	
					$after_control_html = str_replace($tags_replace, $tags_values, $after_control_html); 
				}
				if ($end_html) {	
					$end_html = str_replace($tags_replace, $tags_values, $end_html); 
				}

				$property_control  = "";
				$property_control .= "<input type=\"hidden\" name=\"property_name_" . $property_id . "\"";
				$property_control .= " value=\"" . strip_tags($property_name) . "\">";
				$property_control .= "<input type=\"hidden\" name=\"property_required_" . $property_id . "\"";
				$property_control .= " value=\"" . intval($property_required) . "\">";
				$property_control .= "<input type=\"hidden\" name=\"property_control_" . $property_id . "\"";
				$property_control .= " value=\"" . strtoupper($control_type) . "\">";
				$property_control .= "<input type=\"hidden\" name=\"property_parent_id_" . $property_id . "\"";
				$property_control .= " value=\"" . $parent_property_id . "\">";
				$property_control .= "<input type=\"hidden\" name=\"property_parent_value_id_" . $property_id . "\"";
				$property_control .= " value=\"" . $parent_value_id . "\">";

				if ($parent_property_id) {
					if (!isset($options[$parent_property_id]) || sizeof($options[$parent_property_id]["values"]) == 0) {
						$property_style = "display: none;" . $property_style;
					} else if ($parent_value_id && !in_array($parent_value_id, $options[$parent_property_id]["values"])) {
						$property_style = "display: none;" . $property_style;
					}
				}

				$sql  = " SELECT ipv.value_id, ipv.value_name, ipv.is_default_value ";
				$sql .= " FROM " . $table_prefix . "cms_blocks_values ipv ";
				$sql .= " WHERE ipv.property_id=" . $db->tosql($property_id, INTEGER);
				$sql .= " AND (ipv.hide_value=0 OR ipv.hide_value IS NULL) ";
				$sql .= " ORDER BY ipv.value_order, ipv.value_id ";

				if (strtoupper($control_type) == "LISTBOX") {
					$properties_values = "<option value=\"\">" . SELECT_MSG . " " . $property_name . "</option>" . $eol;
					$dbp->query($sql);
					while ($dbp->next_record())
					{
						$value_name = $dbp->f("value_name");
						parse_value($value_name);

						$value_id = $dbp->f("value_id");
						$is_default_value = $dbp->f("is_default_value");

						$property_selected  = "";
						$is_selected = false;
						if (is_array($selected_properties)) {
							if (isset($selected_properties[$property_id]) && in_array($value_id, $selected_properties[$property_id])) {
								$is_selected = true;
							}
						} else if ($is_default_value) {
							$is_selected = true;
						}
						if ($is_selected) {
							$property_selected  = "selected ";
							$options[$property_id]["values"][] = $value_id;
						} 

						
						$properties_values .= "<option " . $property_selected . "value=\"" . htmlspecialchars($value_id) . "\">";
						$properties_values .= htmlspecialchars($value_name);
						$properties_values .= "</option>" . $eol;
					}
					$property_control .= $before_control_html;
					$property_control .= "<select name=\"property_" . $property_id . "\" onChange=\"changeProperty();";
					if ($onchange_code) {	$property_control .= $onchange_code; }
					$property_control .= "\"";
					if ($onclick_code) {	$property_control .= " onClick=\"" . $onclick_code . "\""; }
					if ($control_code) {	$property_control .= " " . $control_code . " "; }
					if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
					$property_control .= ">" . $properties_values . "</select>";				
					$property_control .= $after_control_html;
				} elseif (strtoupper($control_type) == "RADIOBUTTON" || strtoupper($control_type) == "CHECKBOXLIST") {
					$is_multiple = (strtoupper($control_type) != "RADIOBUTTON");
					if (strtoupper($control_type) == "RADIOBUTTON") {
						$input_type = "radio"; $is_multiple = false;
					} else if (strtoupper($control_type) == "CHECKBOXLIST") {
						$input_type = "checkbox"; $is_multiple = true;
					}
					$property_control .= "<span";
					if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
					$property_control .= ">";
					
					$value_number = 0;
					$dbp->query($sql);
					while ($dbp->next_record())
					{
						$value_number++;

						$value_id = $dbp->f("value_id");
						$item_code = $dbp->f("item_code");
						$manufacturer_code = $dbp->f("manufacturer_code");
						$is_default_value = $dbp->f("is_default_value");
						$value_name = $dbp->f("value_name");
						parse_value($value_name);

						$tags_replace = array("{item_code}", "{manufacturer_code}", "{option_value}", "{value_id}", "{value_index}",  "{value_number}");
						$tags_values  = array($item_code, $manufacturer_code, $value_name, $value_id, ($value_number - 1), $value_number);

						$property_checked = "";
						$property_control .= $before_control_html;

						$is_selected = false;
						if (is_array($selected_properties)) {
							if (isset($selected_properties[$property_id]) && in_array($value_id, $selected_properties[$property_id])) {
								$is_selected = true;
							}
						} else if ($is_default_value) {
							$is_selected = true;
						}
						if ($is_selected) {
							$property_checked = "checked ";
							$options[$property_id]["values"][] = $value_id;
						} 
	
						$control_name = ($is_multiple) ? ("property_".$property_id."_".$value_number) : ("property_".$property_id);
						$property_control .= "<input type=\"" . $input_type . "\" id=\"item_property_" . $value_id . "\" name=\"" . $control_name . "\" ". $property_checked;
						$property_control .= "value=\"" . htmlspecialchars($value_id) . "\" onClick=\"changeProperty(); ";
						if ($onclick_code) {	$property_control .= $onclick_code; }
						$property_control .= "\"";
						if ($onchange_code) {	$property_control .= " onChange=\"" . $onchange_code . "\""; }
						if ($control_code) {	$property_control .= " " . $control_code . " "; }
						$property_control .= ">";						
						
						$image       = $dbp->f("big_image");
						$tiny_image  = $dbp->f("tiny_image");

						$property_control .= $value_name;
						$property_control .= $after_control_html;
											
						// added here to have a possibilty to parse different tags like value_id for any option in HTML, JavaScript or CSS
						$property_control = str_replace($tags_replace, $tags_values, $property_control); 
					}
					$property_control .= "</span>";
					$property_control .= "<input type=\"hidden\" name=\"property_total_".$property_id."\" value=\"".$value_number."\">";
				} elseif (strtoupper($control_type) == "TEXTBOX") {
					$property_control .= $before_control_html;
					$property_control .= "<input type=\"text\" name=\"property_" . $property_id . "\"";
					if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
					if ($onclick_code) {	$property_control .= " onClick=\"" . $onclick_code . "\""; }
					$property_control .= " onChange=\"changeProperty();";
					if ($onchange_code) {	
						$property_control .= $onchange_code; 
					}
					$property_control .= "\"";
					if ($control_code) {	$property_control .= " " . $control_code . " "; }

					$property_control .= " value=\"";
					if (is_array($selected_properties)) {
						if (isset($selected_properties[$property_id])) {
							$property_control .= htmlspecialchars($selected_properties[$property_id][0]);
							$options[$property_id]["values"][] = $selected_properties[$property_id][0];
						}
					} else {
						$property_control .= htmlspecialchars(get_translation($default_value));
						$options[$property_id]["values"][] = get_translation($default_value);
					}
					$property_control .= "\">";
					$property_control .= $after_control_html;
				} elseif (strtoupper($control_type) == "TEXTAREA") {
					$property_control .= $before_control_html;
					$property_control .= "<textarea name=\"property_" . $property_id . "\"";
					if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
					if ($onclick_code) {	$property_control .= " onClick=\"" . $onclick_code . "\""; }
					$property_control .= " onChange=\"changeProperty();";
					if ($onchange_code) {	
						$property_control .= $onchange_code; 
					}
					$property_control .= "\"";
					if ($control_code) {	$property_control .= " " . $control_code . " "; }

					$property_control .= ">";
					if (is_array($selected_properties)) {
						if (isset($selected_properties[$property_id])) {
							$property_control .= htmlspecialchars($selected_properties[$property_id][0]);
							$options[$property_id]["values"][] = $selected_properties[$property_id][0];
						}
					} else {
						$property_control .= htmlspecialchars(get_translation($default_value));
						$options[$property_id]["values"][] = get_translation($default_value);
					}
					$property_control .= "</textarea>";
					$property_control .= $after_control_html;
				} elseif (strtoupper($control_type) == "CHECKBOX") {

					$property_control .= $before_control_html;
					$property_control .= "<input type=\"checkbox\" name=\"property_" . $property_id . "\"";
					if (is_array($selected_properties)) {
						if (isset($selected_properties[$property_id])) {
							$property_control .= " checked ";
							$options[$property_id]["values"][] = 1;
						}
					} else if ($default_value) {
						$property_control .= " checked ";
						$options[$property_id]["values"][] = 1;
					}
					$property_control .= "value=\"" . htmlspecialchars(1) . "\" onClick=\"changeProperty(); ";
					if ($onclick_code) {	$property_control .= $onclick_code; }
					$property_control .= "\"";
					if ($onchange_code) {	$property_control .= " onChange=\"" . $onchange_code . "\""; }
					if ($control_code) {	$property_control .= " " . $control_code . " "; }
					$property_control .= ">";						

					$property_control .= $after_control_html;
				} else {
					$property_control .= $before_control_html;
					if ($property_required) {
						$property_control .= "<input type=\"hidden\" name=\"property_" . $property_id . "\" value=\"" . htmlspecialchars($default_value) . "\">";
					}
					$property_control .= "<span";
					if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
					if ($onclick_code) {	$property_control .= " onClick=\"" . $onclick_code . "\""; }
					if ($onchange_code) {	$property_control .= " onChange=\"" . $onchange_code . "\""; }
					if ($control_code) {	$property_control .= " " . $control_code . " "; }
					$property_control .= ">" . get_translation($default_value) . "</span>";
					$property_control .= $after_control_html;
					$options[$property_id]["values"][] = get_translation($default_value);
				}

				$t->set_var("property_id", $property_id);
				$t->set_var("property_block_id", $property_block_id);
				$t->set_var("property_name", $start_html . $property_name . $middle_html);
				$t->set_var("property_class", $property_class);
				$t->set_var("property_style", $property_style);
				$t->set_var("property_control", $property_control . $end_html);

				$t->parse("properties", true);
			} 
		} // end options block
		$t->set_var("properties_ids", $properties_ids);

	$periods_number = 0;
	$week_values = array(
		"1" => 1, "2" => 2, "3" => 4, "4" => 8, "5" => 16, "6" => 32, "7" => 64,
	);

	if (is_array($periods) && sizeof($periods) > 0) {
		foreach ($periods as $id => $period) {
			$periods_number++;
			$period_class = ($periods_number % 2 == 1) ? "row1" : "row2";

			$week_days = $period["week_days"];
			$t->set_var("start_date", $period["start_date"]);
			$t->set_var("end_date", $period["end_date"]);
			$t->set_var("start_time", $period["start_time"]);
			$t->set_var("end_time", $period["end_time"]);

			foreach ($week_values as $day => $day_value) {
				if ($week_days & $day_value) {
					$t->set_var("day_".$day, "checked=\"checked\" ");
				} else {
					$t->set_var("day_".$day, "");
				}
				if ($week_days == 127) {
					$t->set_var("day_disabled_".$day, "disabled=\"disabled\" ");
				} else {
					$t->set_var("day_disabled_".$day, "");
				}
			}
			if ($week_days == 127) {
				$t->set_var("all_days", "checked=\"checked\" ");
			} else {
				$t->set_var("all_days", "");
			}

			$t->set_var("period_style", "");
			$t->set_var("period_number", $periods_number);
			$t->set_var("period_class", $period_class);
			$t->set_var("period_row_id", "periodRow".$periods_number);
			$t->parse_to("period_row", "periods_rows", true);
		}
	}
	// set total number of periods
	$t->set_var("periods_number", $periods_number);

		// set data for new record
	$t->set_var("start_date", "");
	$t->set_var("end_date", "");
	$t->set_var("start_time", "");
	$t->set_var("end_time", "");
	$t->set_var("all_days", "checked=\"checked\" ");
	foreach ($week_values as $day => $day_value) {
		$t->set_var("day_".$day, "checked=\"checked\" ");
		$t->set_var("day_disabled_".$day, "disabled=\"disabled\" ");
	}
	$t->set_var("period_number", "{period_number}");
	$t->set_var("period_class", "");
	$t->set_var("period_style", "display: none;");
	$t->set_var("period_row_id", "period_row");
	$t->parse("period_row", false);

	// parse tabs
	$tab = $is_properties ? "properties" : "appearance";
	$tabs = array(
			"properties" => array("title" => BLOCK_SETTINGS_MSG, "show" => $is_properties),
			"appearance" => array("title" => APPEARANCE_MSG), 
			"periods" => array("title" => TIME_PERIODS_MSG),
		);

	parse_admin_tabs($tabs, $tab, 6);

	$t->pparse("main");

?>