<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  page_layout.php                                          ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

	// initialize template class
	$t = new VA_Template($settings["templates_dir"]);
	// calculate how many blocks were parsed
	$blocks_parsed = 0;
	// check if it's a popup page
	if(!isset($is_frame_layout)) { $is_frame_layout = false; }
	// get and set global values
	$site_name = get_setting_value($settings, "site_name");
	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", "");
	$site_class = get_setting_value($settings, "site_class", "");
	$user_id = get_session("session_user_id");
	$user_name = get_session("session_user_name");
	$user_email = get_session("session_user_email");
	if (!$layout_site_id) { $layout_site_id = $site_id; } // check if layout_site_id parameter was set

	if ($is_ssl) {
		$absolute_url = $secure_url;
	} else {
		$absolute_url = $site_url;
	}
	$parsed_url = parse_url($site_url);
	$site_path = isset($parsed_url["path"]) ? $parsed_url["path"] : "/";
	$page_url = $site_url.ltrim(get_request_uri(), "/");
	if (!isset($canonical_url)) { $canonical_url = ""; } 
	
	$css_file = "";
	$style_name = get_setting_value($settings, "style_name", "");
	$scheme_class = get_setting_value($settings, "scheme_name", "");
	if (strlen($style_name)) {
		$css_file  = $site_url."styles/".$style_name;
		if (!preg_match("/\.css$/", $style_name)) { $css_file .= ".css"; }
	}
	$t->set_var("CHARSET", va_message("CHARSET"));
	$t->set_var("meta_language", $language_code);
	$t->set_var("site_name", htmlspecialchars($site_name));
	$t->set_var("site_url", $site_url);
	$t->set_var("secure_url", $site_url);
	$t->set_var("absolute_url", $site_url);
	$t->set_var("page_url", htmlspecialchars($page_url));
	$t->set_var("page_url_encode", urlencode($page_url));
	$t->set_var("canonical_url", htmlspecialchars($canonical_url));
	$t->set_var("canonical_url_encode", urlencode($canonical_url));

	$t->set_var("user_name", htmlspecialchars($user_name));
	$t->set_var("user_email", htmlspecialchars($user_email));
	$t->set_var("customer_name", htmlspecialchars($user_name));
	$t->set_var("customer_email", htmlspecialchars($user_email));

	$t->set_var("css_file", $css_file);
	$t->set_var("site_class", $site_class);
	$t->set_var("scheme_class", $scheme_class);
	if (isset($current_page)) {
		$t->set_var("current_href", $current_page);
	}
	// add google analytics code to hidden blocks
	if (!isset($tracking_ignore)) { $tracking_ignore = false; } 
	$google_analytics = get_setting_value($settings, "google_analytics", 0);
	$google_tracking_code = get_setting_value($settings, "google_tracking_code", "");
	$t->set_template_path("./js");
	if (!$tracking_ignore && $google_analytics && $google_tracking_code && !$is_frame_layout) {
		$t->set_file("google_analytics", "gtag.js");
		$t->set_var("google_tracking_code", $google_tracking_code);
		$cookie_control = get_session("cookie_control");
		if($cookie_control == 1){
			$t->set_var("disable_google_cookies", "window['ga-disable-" . $google_tracking_code . "'] = true;");
		}
	}

	if (isset($debug_mode) && $debug_mode) {
		$t->set_var("debug_buffer", $debug_buffer);
	}
	// check page settings id	
	$page_class = "";
	$sql  = " SELECT cps.* ";
	$sql .= " FROM (" . $table_prefix . "cms_pages_settings cps ";
	$sql .= " INNER JOIN " . $table_prefix . "cms_pages cp ON cp.page_id=cps.page_id) ";
	if (isset($cms_ps_id) && strlen($cms_ps_id)) {
		$sql .= " WHERE cps.ps_id=" . $db->tosql($cms_ps_id, INTEGER);
	} else {
		$sql .= " WHERE cp.page_code=" . $db->tosql($cms_page_code, TEXT);
		$sql .= " AND (cps.key_code='' OR cps.key_code IS NULL) ";
		if (isset($cms_key_type) && $cms_key_type) {
			$sql .= " AND cps.key_type=" . $db->tosql($cms_key_type, TEXT);
		} else {
			$sql .= " AND (cps.key_type='' OR cps.key_type IS NULL) ";
		}
		if ($layout_site_id != 1) {
			$sql .= " AND (cps.site_id=1 OR cps.site_id=" . $db->tosql($layout_site_id, INTEGER) . ") ";
		} else {
			$sql .= " AND cps.site_id=1 ";
		}
		$sql .= " ORDER BY cps.site_id DESC ";
	}
	$db->query($sql);
	if ($db->next_record()) {
		$ps_id = $db->f("ps_id");
		$layout_id = $db->f("layout_id");
		$page_class = $db->f("page_class");
		if (!$page_class) { $page_class = "pg-".str_replace("_", "-", $cms_page_code); }
		$page_meta_title = get_translation($db->f("meta_title"));
		$page_meta_keywords = get_translation($db->f("meta_keywords"));
		$page_meta_description = get_translation($db->f("meta_description"));
		$page_meta_data = get_translation($db->f("meta_data"));
	} else {
		echo "Page <b>".$cms_page_code."</b> wasn't found.";
		exit;
	}
	// get layout template 
	$sql  = " SELECT * FROM " . $table_prefix . "cms_layouts ";
	$sql .= " WHERE layout_id=" . $db->tosql($layout_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$layout_template = $db->f("layout_template");
	}	
	// set layout
	$t->set_template_path($settings["templates_dir"]);
	$t->set_file("main", $layout_template);

	$layout_block_templates = array(
		"bk" => get_setting_value($settings, "block_default_template", "layout_block_default.html"),
		"aa" => get_setting_value($settings, "block_area_template", "layout_block_area.html"),
		"bb" => get_setting_value($settings, "block_breadcrumb_template", "layout_block_breadcrumb.html"),
		"no" => "",
	);

	// set head tags
	if (!$is_frame_layout) {
		set_head_tag("base", array("href"=>$site_url), "href", 1);
		if ($css_file) {
			set_link_tag($css_file, "stylesheet", "text/css");
		}
		// set favicon
		$favicon = get_setting_value($settings, "favicon", "");
		if ($favicon) {
			if (preg_match("/gif$/i", $favicon)) {
				$icon_type = "image/gif";
			} else if (preg_match("/png$/i", $favicon)) {
				$icon_type = "image/png";
			} else {
				$icon_type = "image/x-icon";
			}
			set_link_tag($favicon, "icon", $icon_type);
		}
	}
	// get frames settings
	$frames = array();
	$sql  = " SELECT * FROM " . $table_prefix . "cms_frames ";
	$sql .= " WHERE layout_id=" . $db->tosql($layout_id, INTEGER);
	$db->query($sql);
	while ($db->next_record()) {
		$frame_id = $db->f("frame_id");
		$tag_name = $db->f("tag_name");
		// initialize all frames for layouts with empty values in case there are no saved settings
		$frames[$frame_id] = array(
			"tag_name" => $tag_name, "blocks" => 0, 
			"frame_class" => "", "frame_style" => "", "html_frame_start" => "", 
			"html_between_blocks" => "", "html_before_block" => "", 
			"html_after_block" => "", "html_frame_end" => "", 
		);
	}	
	$sql  = " SELECT * FROM " . $table_prefix . "cms_frames_settings ";
	$sql .= " WHERE ps_id=" . $db->tosql($ps_id, INTEGER);
	$db->query($sql);
	while ($db->next_record()) {
		$frame_id = $db->f("frame_id");
		if (isset($frames[$frame_id])) {
			$tag_name = $frames[$frame_id]["tag_name"];
			$frames[$frame_id] = $db->Record;
			$frames[$frame_id]["tag_name"] = $tag_name;
			$frames[$frame_id]["blocks"] = 0;
			$t->set_var($tag_name."_class", $frames[$frame_id]["frame_class"]);
			$t->set_var($tag_name."_style", $frames[$frame_id]["frame_style"]);
			$t->set_var($tag_name, $frames[$frame_id]["html_frame_start"]);
		}
	}	

	// get page blocks
	$page_blocks = array();
	$sql  = " SELECT cpb.pb_id, cb.block_code, cb.php_script, cpb.frame_id, cpb.block_key, ";
	$sql .= " cb.css_class AS cms_css_class, cb.html_template AS cms_html_template, ";
	$sql .= " cb.layout_type AS cms_layout_type, cb.layout_template AS cms_layout_template, ";
	$sql .= " cpb.layout_type AS page_layout_type, cpb.layout_template AS page_layout_template, ";
	$sql .= " cpb.tag_name, cpb.html_template AS page_html_template, ";
	$sql .= " cpb.block_style, cpb.css_class AS page_block_class, ";
	$sql .= " cb.block_title, cpb.block_title AS page_block_title ";
	$sql .= " FROM (" . $table_prefix . "cms_pages_blocks cpb ";
	$sql .= " INNER JOIN " . $table_prefix . "cms_blocks cb ON cpb.block_id=cb.block_id) ";
	$sql .= " WHERE cpb.ps_id=" . $db->tosql($ps_id, INTEGER);
	$sql .= " ORDER BY cpb.block_order ";
	$db->query($sql);
	while ($db->next_record()) {
		$pb_id = $db->f("pb_id");
		$page_blocks[$pb_id] = $db->Record;
		$page_blocks[$pb_id]["vars"] = array();
		$page_blocks[$pb_id]["periods"] = false;
		$page_blocks[$pb_id]["period_active"] = false;
	}
	// get blocks variables
	$sql  = " SELECT cbs.pb_id, cbs.variable_name, cbs.variable_value ";
	$sql .= " FROM " . $table_prefix . "cms_blocks_settings cbs ";
	$sql .= " WHERE cbs.ps_id=" . $db->tosql($ps_id, INTEGER);
	$db->query($sql);
	while ($db->next_record()) {
		$pb_id = $db->f("pb_id");
		if (isset($page_blocks[$pb_id])) {
			$variable_name = $db->f("variable_name");
			$variable_value = $db->f("variable_value");
			if (isset($page_blocks[$pb_id]["vars"][$variable_name])) {
				if (is_array($page_blocks[$pb_id]["vars"][$variable_name])) {
					$page_blocks[$pb_id]["vars"][$variable_name][] = $variable_value;
				} else {
					$page_blocks[$pb_id]["vars"][$variable_name] = array($page_blocks[$pb_id]["vars"][$variable_name]);
					$page_blocks[$pb_id]["vars"][$variable_name][] = $variable_value;
				}
			} else {
				$page_blocks[$pb_id]["vars"][$variable_name] = $variable_value;
			}
		}
	}
	// some vars to check periods
	$current_date = va_time();
	$current_ts = va_timestamp();
	$check_time = $current_date[HOUR] * 60 + $current_date[MINUTE];
	$week_values = array(
		"1" => 1, "2" => 2, "3" => 4, "4" => 8, "5" => 16, "6" => 32, "0" => 64,
	);
	$day_value = $week_values[date("w", $current_ts)];
	// get and check blocks periods
	$sql  = " SELECT cbp.pb_id, cbp.start_date, cbp.end_date, cbp.start_time, cbp.end_time, cbp.week_days ";
	$sql .= " FROM " . $table_prefix . "cms_blocks_periods cbp ";
	$sql .= " WHERE cbp.ps_id=" . $db->tosql($ps_id, INTEGER);
	$db->query($sql);
	while ($db->next_record()) {
		$pb_id = $db->f("pb_id");
		if (isset($page_blocks[$pb_id])) {
			$page_blocks[$pb_id]["periods"] = true; // has time periods to show this block on page
			$start_date_ts = 0;
			$start_date = $db->f("start_date", DATETIME);
			if (is_array($start_date)) {
				$start_date_ts = va_timestamp($start_date);
			}
			$end_date_ts = 0;
			$end_date = $db->f("end_date", DATETIME);
			if (is_array($end_date)) {
				$end_date[HOUR] = 23; $end_date[MINUTE] = 59; $end_date[SECOND] = 59;
				$end_date_ts = va_timestamp($end_date);
			}
			$start_time = $db->f("start_time");
			$end_time = $db->f("end_time");
			$week_days = $db->f("week_days");
			if ($current_ts >= $start_date_ts && ($current_ts <= $end_date_ts || !$end_date_ts) &&
				$check_time >= $start_time && ($check_time <= $end_time || !$end_time) &&
				($day_value&$week_days)
			) {
				$page_blocks[$pb_id]["period_active"] = true;
			}
		}
	}
	// parse blocks
	foreach ($page_blocks as $pb_id => $block) {
		// check if there are time periods to show this block
		if($block["periods"] && !$block["period_active"]) {
			continue;
		}
		$frame_id = $block["frame_id"];
		$frame_tag_name = $frames[$frame_id]["tag_name"];
		$php_script = $block["php_script"];
		$cms_block_code = $block["block_code"];
		$cms_css_class = $block["cms_css_class"];
		if (!$cms_css_class) { $cms_css_class = "bk-".str_replace("_", "-", $cms_block_code); }
		$block_tag_name = $block["tag_name"];
		// get template for block
		$html_template = $block["page_html_template"];
		if (!$html_template) {
			$html_template = $block["cms_html_template"];
		}
		$block["html_template"] = $html_template;
		// get block layout template
		$layout_type = $block["page_layout_type"]; 
		$layout_block_template = "";
		if ($layout_type) {
			if ($layout_type == "cm") {
				$layout_block_template = $block["page_layout_template"];
			}
		} else {
			$layout_type = $block["cms_layout_type"];
			if ($layout_type == "cm") {
				$layout_block_template = $block["cms_layout_template"];
			}
		}
		$block_style = $block["block_style"];
		$page_block_class = $block["page_block_class"];
		$block_title = $block["block_title"];
		$page_block_title = $block["page_block_title"];
		if (strlen($page_block_title)) { $block_title = $page_block_title; }
		$vars = array();
		$vars = $block["vars"];
		$vars["block_key"] = $block["block_key"];
		$vars["tag_name"] = $frames[$frame_id]["tag_name"];
		$block_css_class = ""; $extra_css_class = ""; // clear before include block
		$default_title = ""; // always clear default title before parse
		$block_parsed = false;
		// set global block vars
		$t->set_var("pb_id", $pb_id);
		$t->set_var("block_style", $block_style);
		$t->set_var("block_title", get_translation($block_title));
		// set script name for DB module in case of errors
		$db->DebugScript = basename($php_script);
		// clear blocks before parse next block
		$t->block_clear("before_block");
		$t->block_clear("after_block");
		$t->block_clear("block_head");
		$t->block_clear("block_foot");
		if (file_exists("./blocks_custom/".$php_script)) {
			include("./blocks_custom/".$php_script);
		} else {
			include("./blocks/".$php_script);
		}
		// clear script name
		$db->DebugScript = "";
		if ($block_parsed) {
			// check class for block
			if ($page_block_class) {
				$cms_css_class = trim($page_block_class); // override default block class to page specific class
			} 
			if ($block_css_class) {
				$cms_css_class .= " ".$block_css_class;
			}
			if ($extra_css_class) { $cms_css_class .= " ".$extra_css_class; }
			if (!$layout_type) { $layout_type = "bk"; }
			if (!$layout_block_template && $layout_type) {
				$layout_block_template = isset($layout_block_templates[$layout_type]) ? $layout_block_templates[$layout_type] : "";
			}
			$blocks_parsed++;
			// set block global vars
			$tag_name = strlen($block_tag_name) ? $block_tag_name : $frame_tag_name;
			if (!strlen($block_tag_name)) {
				// parse frame data only for frame blocks 
				$frames[$frame_id]["blocks"]++;
				if ($frames[$frame_id]["blocks"] > 1) {
					$t->set_var("frame_code", $frames[$frame_id]["html_between_blocks"]);
					$t->copy_var("frame_code", $tag_name, true);
				}
				$t->set_var("frame_code", $frames[$frame_id]["html_before_block"]);
				$t->copy_var("frame_code", $tag_name, true);
			}
			if (!strlen($block_title)) { $block_title = $default_title; }
			$accumulate_parse = true;
			// set final block title
			$t->set_block("block_title", get_translation($block_title));
			$t->parse("block_title", false);
			// check if we need to hide block title
			$parsed_title = $t->get_var("block_title");
			if (!strlen($parsed_title)) { $cms_css_class = "hidden-title ". $cms_css_class; }
			$t->set_var("block_class", $cms_css_class);
			if ($layout_block_template) {
				if (!$t->block_exists($layout_block_template)) {
					$t->set_file($layout_block_template, $layout_block_template);
				} 
				if ($t->block_exists("block_head")) {
					$t->parse_to("block_head", "head_tag", false);	
				} else {
					$t->set_var("head_tag", $parsed_title);	
				}
				$t->parse_to("block_body", "body_tag", false);	
				if ($t->block_exists("block_foot")) {
					$t->parse_to("block_foot", "foot_tag", false);	
				}
				$t->parse_to("before_block", $tag_name, $accumulate_parse);
				$t->parse_to($layout_block_template, $tag_name, $accumulate_parse);
				$t->parse_to("after_block", $tag_name, $accumulate_parse);
			} else {
				$t->parse_to("block_body", $tag_name, $accumulate_parse);
			}

			if (!strlen($block_tag_name)) {
				// parse frame data only for frame blocks 
				$t->set_var("frame_code", $frames[$frame_id]["html_after_block"]);
				$t->copy_var("frame_code", $tag_name, true);
			}
		}
	}

	// close frames 
	foreach ($frames as $frame_id => $frame) {
		$tag_name = $frames[$frame_id]["tag_name"];
		$t->set_var("frame_code", $frames[$frame_id]["html_frame_end"]);
		$t->copy_var("frame_code", $tag_name, true);
	}	
	// set js settings if it's available
	if (isset($js_settings) && is_array($js_settings) && count($js_settings) > 0) {
		$script_var = "var vaSettings = ".json_encode($js_settings).";";
		$script_tag = "<script>".$eol.$script_var.$eol."</script>";
		$t->set_block("head_tag", $script_tag);
		$t->parse_to("head_tag", "head_tags", true);
	}
	// check if unique meta data was set or we need use global CMS Page meta data or probably auto meta data
	if (!isset($meta_title) || !strlen($meta_title)) {
		if (strlen($page_meta_title)) {
			$meta_title = $page_meta_title; 
		} else if (isset($auto_meta_title) && strlen($auto_meta_title)) {
			$meta_title = $auto_meta_title; 
		} else {
			$meta_title = get_setting_value($settings, "site_name"); 
		}
	}
	if (!isset($meta_keywords) || !strlen($meta_keywords)) {
		if (strlen($page_meta_keywords)) {
			$meta_keywords = $page_meta_keywords; 
		} else if (isset($auto_meta_keywords) && strlen($auto_meta_keywords)) {
			$meta_keywords = $auto_meta_keywords; 
		} else {
			$meta_keywords = "";
		}
	}
	if (!isset($meta_description) || !strlen($meta_description)) {
		if (strlen($page_meta_description)) {
			$meta_description = $page_meta_description; 
		} else if (isset($auto_meta_description) && strlen($auto_meta_description)) {
			$meta_description = get_meta_desc($auto_meta_description); 
		} else {
			$meta_description = get_setting_value($settings, "site_description"); 
		}
	}
	if (!isset($meta_data) || !strlen($meta_data)) {
		if (strlen($page_meta_data)) {
			$meta_data = $page_meta_data; 
		}
	}

	// set page class and some meta data
	parse_value($meta_title);
	parse_value($page_class);
	$meta_title = strip_tags($meta_title); // strip any html tags 
	if ($site_class) { $page_class .= " ".$site_class; } // add site class
	$t->set_var("page_class", htmlspecialchars($page_class));
	$t->set_var("meta_title", htmlspecialchars($meta_title));
	if (!$is_frame_layout) {
		if ($meta_keywords) {
			set_head_tag("meta", array("name"=>"keywords","content"=>$meta_keywords), "name", 1);
		}
		if ($meta_description) {
			$t->set_block("_meta_description", $meta_description);
			$t->parse("_meta_description", false);
			$meta_description = $t->get_var("_meta_description");
			set_head_tag("meta", array("name"=>"description","content"=>$meta_description), "name", 1);
		}
		if (isset($meta_data) && strlen($meta_data)) {
			$t->set_block("html_tag", $meta_data);
			$t->parse_to("html_tag", "head_tags", true);
		}
		if (isset($canonical_url) && strlen($canonical_url)) {
			if(!preg_match("/^https?\:/", $canonical_url)) {
				$canonical_url = $site_url.$canonical_url;
			}
			set_link_tag(htmlspecialchars($canonical_url), "canonical", "");
		}
		if ($google_analytics && $google_tracking_code) {
			$t->parse_to("google_analytics", "head_tags");
		}
		if (isset($meta_tags) && is_array($meta_tags) && count($meta_tags)) {
			foreach ($meta_tags as $tag_key => $meta_data) {
				set_head_tag($meta_data["name"], $meta_data["attributes"], "", 1);
			}
		}
		$global_head_html = get_translation(get_setting_value($settings, "head_html"));
		if ($global_head_html) {
			$t->set_block("custom_html", $global_head_html);
			$t->parse_to("custom_html", "head_tags", true);
		}
	}
	// check if we need to show cookie bar consent
	va_cookie_bar();
	// call init script in the end of HTML page to run it immediately
	set_script_tag("js/init.js", false, "hidden_blocks");

	// check welcome popup settings 
	$welcome_popup = get_setting_value($settings, "welcome_popup");
	if ($welcome_popup == "once" || $welcome_popup == "every") {
		// check session
		$check_popups = get_session("popups");
		if (!is_array($check_popups)) { $check_popups = array(); }
		if ($welcome_popup == "once") {
			// check cookies
			$va_track = json_decode(get_cookie("_va_track"), true);
			$cookie_popups = get_setting_value($va_track, "popups");
			if (is_array($cookie_popups)) { 
				$check_popups = array_merge($check_popups, $cookie_popups); 
			}
		}
		$welcome_code = get_setting_value($settings, "welcome_code", "welcome");
		if (!in_array($welcome_code, $check_popups)) {
			$session_start_ts = get_session("session_start_ts");
			$welcome_layout = get_setting_value($settings, "welcome_layout");
			if ($welcome_layout == "default") {
				$t->set_file("hidden_block", "layout_popup.html");
				$t->parse_to("hidden_block", "hidden_blocks", true);
			}
			$welcome_delay = intval(get_setting_value($settings, "welcome_delay")) + $session_start_ts - time();
			$welcome_block = get_setting_value($settings, "welcome_block");
			$welcome_params = array("body" => $welcome_block, "layout" => $welcome_layout, "event" => "welcome-popup", "code" => $welcome_code);
			if ($welcome_delay > 0) {
				$welcome_script = "<script>setTimeout(function() { vaShowPopup(".json_encode($welcome_params)."); }, ".($welcome_delay*1000).");</script>";
			} else {
				$welcome_script = "<script>vaShowPopup(".json_encode($welcome_params).");</script>";
				// save shown popup in the session immediately to disable it showing next time
				$check_popups[] = $welcome_code;
				set_session("popups", $check_popups);
			}
			$t->set_block("custom_html", $welcome_script);
			$t->parse_to("custom_html", "hidden_blocks", true);
		}
	}

	// parse page content
	if ($is_frame_layout) {
		$t->parse("main");
	} else {
		$t->pparse("main");
	}
