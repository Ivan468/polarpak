<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_cms_multi_edit.php                                 ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path."includes/record.php");
	include_once($root_folder_path."includes/cms_functions.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");
	include_once($root_folder_path."messages/".$language_code."/forum_messages.php");
	include_once($root_folder_path."messages/".$language_code."/manuals_messages.php");
	include_once($root_folder_path."messages/".$language_code."/support_messages.php");
	include_once($root_folder_path."messages/".$language_code."/profiles_messages.php");

	check_admin_security("cms_settings");

	$dbs = new VA_SQL($db);

	$sts = get_param("sts");
	$pages_sts = get_param("pages_sts");
	$operation = get_param("operation");
	$param_site_id = get_session("session_site_id");
	if (!$param_site_id) { $param_site_id = 1; }
	// check settings for selected site 
	$sql  = " SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type='global' ";
	$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($param_site_id, INTEGER) . ")";
	$sql .= " ORDER BY site_id ASC ";
	$db->query($sql);
	while ($db->next_record()) {
		$site_settings[$db->f("setting_name")] = $db->f("setting_value");
	}
	$param_site_url = get_setting_value($site_settings, "site_url", "");
	$friendly_urls = get_setting_value($site_settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($site_settings, "friendly_extension", "");

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_cms_multi_edit.html");
	$t->set_file("frame", "admin_cms_frame.html");
	$t->set_var("admin_cms_href", "admin_cms.php");
	$t->set_var("admin_cms_page_layout_href", "admin_cms_page_layout.php");

	$layout_types = array(); $sub_name = ""; $sub_url = ""; $on_live_url = "";
	if ($sub_name) {
		$t->set_var("sub_name", $sub_name);
		$t->set_var("sub_url", $sub_url);
		$t->parse("sub_name_block", false);
	}

	// list of page urls
	$page_urls = array(
		"index" => "index.php",
		"site_map" => "site_map.php",
		"site_search" => "site_search.php",
		"forgot_password" => "forgot_password.php",
		"reset_password" => "reset_password.php",
		"polls" => "polls.php",
		"user_login" => "user_login.php",
		"user_profile" => "user_profile.php",
		"contact_us" => "contact_us.php",
		"subscribe" => "subscribe.php",
		"unsubscribe" => "unsubscribe.php",
		"products_list" => "products_list.php",
	);

	$r = new VA_Record($table_prefix . "cms_pages_settings");
	$r->add_where("ps_id", INTEGER);
	$r->add_textbox("page_id", INTEGER);
	$r->change_property("page_id", REQUIRED, true);
	$r->add_textbox("key_code", TEXT, CODE_MSG);
	$r->change_property("key_code", USE_SQL_NULL, false);
	$r->add_textbox("key_type", TEXT, CODE_MSG);
	$r->change_property("key_type", USE_SQL_NULL, false);
	$r->add_textbox("site_id", INTEGER, ADMIN_SITE_MSG);


	// pages blocks
	$pb = new VA_Record($table_prefix . "cms_pages_blocks");
	$pb->add_where("pb_id", INTEGER);
	$pb->add_textbox("ps_id", INTEGER);
	$pb->add_textbox("block_id", INTEGER);
	$pb->add_textbox("block_key", TEXT);
	$pb->add_textbox("frame_id", INTEGER);
	$pb->add_textbox("block_order", INTEGER);
	$pb->add_textbox("tag_name", TEXT);
	$pb->add_textbox("layout_type", TEXT);
	$pb->add_textbox("layout_template", TEXT);
	$pb->add_textbox("html_template", TEXT);
	$pb->add_textbox("css_class", TEXT);
	$pb->add_textbox("block_style", TEXT);
	$pb->add_textbox("block_title", TEXT);

	// pages blocks settings
	$bs = new VA_Record($table_prefix . "cms_blocks_settings");
	$bs->add_where("bs_id", INTEGER);
	$bs->add_textbox("ps_id", INTEGER);
	$bs->add_textbox("pb_id", INTEGER);
	$bs->add_textbox("property_id", INTEGER);
	$bs->add_textbox("value_id", INTEGER);
	$bs->add_textbox("variable_name", TEXT);
	$bs->add_textbox("variable_value", TEXT);


	// blocks periods
	$bp = new VA_Record($table_prefix . "cms_blocks_periods");
	$bp->add_where("period_id", INTEGER);
	$bp->add_textbox("ps_id", INTEGER);
	$bp->add_textbox("pb_id", INTEGER);
	$bp->add_textbox("start_date", DATETIME);
	$bp->add_textbox("end_date", DATETIME);
	$bp->add_textbox("start_time", INTEGER);
	$bp->add_textbox("end_time", INTEGER);
	$bp->add_textbox("week_days", INTEGER);

	$errors = "";

	// get all layouts frames
	$layouts_frames = array();
	$sql  = " SELECT * FROM " . $table_prefix . "cms_frames ";
	$db->query($sql);
	while ($db->next_record()) {
		$layout_id = $db->f("layout_id");
		$frame_id = $db->f("frame_id");
		$frame_name = $db->f("frame_name");
		$tag_name = $db->f("tag_name");
		if (!isset($layouts_frames[$layout_id])) {
			$layouts_frames[$layout_id] = array();
		}
		$layouts_frames[$layout_id][$tag_name] = $frame_id;
	}


	$top_articles = array();
	$sql  = " SELECT ac.category_id, ac.category_name ";
	$sql .= " FROM " . $table_prefix . "articles_categories ac ";
	$sql .= " WHERE ac.parent_category_id=0 ";
	$sql .= " ORDER BY ac.category_order ";
	$db->query($sql);
	while ($db->next_record()) {
		$category_id = $db->f("category_id");
		$category_name = get_translation($db->f("category_name"));
		$top_articles[$category_id] = $category_name;
	}
	// get CMS modules

	$modules = array();
	$sql  = " SELECT m.module_id, m.module_code, m.module_name ";
	$sql .= " FROM " . $table_prefix . "cms_modules m ";
	$sql .= " ORDER BY m.module_order, m.module_id  ";
	$db->query($sql);
	while($db->next_record()) {
		$module_id = $db->f("module_id");
		$module_code = $db->f("module_code");
		$module_name = $db->f("module_name");

		if ($module_code == "articles") {
			foreach ($top_articles as $category_id => $category_name) {
				$article_module = $module_name;
				$t->set_var("category_name", $category_name);
				parse_value($article_module);
				$modules[$module_id."_".$category_id] = array(
					"id" => $module_id, "name" => $article_module, "key_code" => $category_id, "key_type" => "category", "pages" => array());
			}
		} else {
			parse_value($module_name);
			$modules[$module_id] = array("id" => $module_id,  "name" => $module_name, "pages" => array());
		}
	}

	// get full pages list
	$sql  = " SELECT cps.ps_id, cps.key_code, cp.page_code, cp.page_name, cm.module_id, cm.module_code ";
	$sql .= " FROM " . $table_prefix . "cms_pages_settings cps ";
	$sql .= " INNER JOIN " . $table_prefix . "cms_pages cp ON cp.page_id=cps.page_id ";
	$sql .= " INNER JOIN " . $table_prefix . "cms_modules cm ON cm.module_id=cp.module_id ";
	$sql .= " WHERE site_id=" . $db->tosql($param_site_id, INTEGER);
	$sql .= " ORDER BY cm.module_order, cm.module_id, cp.page_order, cps.key_code ";
	$db->query($sql);
	while ($db->next_record()) {
		$ps_id = $db->f("ps_id");
		$module_id = $db->f("module_id");
		$module_code = $db->f("module_code");
		$page_code = $db->f("page_code");
		$page_name = $db->f("page_name");
		$key_code = $db->f("key_code");
		$key_type = $db->f("key_type");
		parse_value($module_name);
		parse_value($page_name);

		if ($module_code == "articles") {
			$module_key = $module_id."_".$key_code;
		} else {
			$module_key = $module_id;
		}
		if (isset($modules[$module_key])) {
			$modules[$module_key]["pages"][] = array("ps_id" => $ps_id, "page_code" => $page_code, "page_name" => $page_name, "key_code" => $key_code, "key_type" => $key_type);
		} else {
			// need clear data for deleted articles section
			// 1. select pages for module
			$cms_pages = array();
			$sql  = " SELECT page_id FROM ".$table_prefix."cms_pages "; 
			$sql .= " WHERE module_id=" . $db->tosql($module_id, INTEGER); 
			$dbs->query($sql);
			while ($dbs->next_record()) {
				$cms_pages[] = $dbs->f("page_id");
			}

			// 2. check saved settings for those pages
			$ps_ids = array();
			if (count($cms_pages)) {
				$sql  = " SELECT ps_id FROM ".$table_prefix."cms_pages_settings "; 
				$sql .= " WHERE page_id IN (" . $db->tosql($cms_pages, INTEGERS_LIST).")"; 
				$sql .= " AND key_code=" . $db->tosql($key_code, TEXT); 
				$dbs->query($sql);
				while ($dbs->next_record()) {
					$ps_ids[] = $dbs->f("ps_id");
				}
			}

			// 3. start delete old page settings
			if (count($ps_ids)) {
				$sql  = " DELETE FROM ".$table_prefix."cms_blocks_periods "; 
				$sql .= " WHERE ps_id IN (" . $db->tosql($ps_ids, INTEGERS_LIST).")"; 
				$dbs->query($sql);
				$sql  = " DELETE FROM ".$table_prefix."cms_blocks_settings "; 
				$sql .= " WHERE ps_id IN (" . $db->tosql($ps_ids, INTEGERS_LIST).")"; 
				$dbs->query($sql);
				$sql  = " DELETE FROM ".$table_prefix."cms_frames_settings "; 
				$sql .= " WHERE ps_id IN (" . $db->tosql($ps_ids, INTEGERS_LIST).")"; 
				$dbs->query($sql);
				$sql  = " DELETE FROM ".$table_prefix."cms_pages_blocks "; 
				$sql .= " WHERE ps_id IN (" . $db->tosql($ps_ids, INTEGERS_LIST).")"; 
				$dbs->query($sql);
				$sql  = " DELETE FROM ".$table_prefix."cms_pages_settings "; 
				$sql .= " WHERE ps_id IN (" . $db->tosql($ps_ids, INTEGERS_LIST).")"; 
				$dbs->query($sql);
			}
		}
	}

	foreach ($modules as $module_data) {
		$pages = isset($module_data["pages"]) ? $module_data["pages"] : array();
		if (count($pages) > 0) {
			$module_name = $module_data["name"];
			$module_key_code = isset($module_data["key_code"]) ? $module_data["key_code"] : "";

			$t->set_var("cms_pages", "");
			foreach ($pages as $page_data) {
				$ps_id = $page_data["ps_id"];
				$page_code = $page_data["page_code"];
				$page_name = $page_data["page_name"];
				$key_code = $page_data["key_code"];
				if ($key_code && $key_code != $module_key_code) {
					$page_name .= " > " . cms_key_name($key_code, $page_code);
				}
				$json_page_data = array("ps_id" => $ps_id, "name" => $module_name." > ".$page_name);
				$t->set_var("ps_id", htmlspecialchars($ps_id));
				$t->set_var("json_page_data", htmlspecialchars(json_encode($json_page_data)));
				$t->set_var("page_name", htmlspecialchars($page_name) );
				$t->parse("cms_pages", true);
			}
			$t->set_var("module_name", htmlspecialchars($module_name));
			$t->parse("cms_modules", true);
		}
	} 	

	if ($operation) {
		$json_sts = json_decode($sts, true);
		$json_pages = json_decode($pages_sts, true);

		$ps_ids = array();
		if (is_array($json_pages) && count($json_pages)) {
			foreach ($json_pages as $ps_id => $page_name) {
				$ps_ids[] = $ps_id;
			}
		} else {
			foreach ($modules as $module_data) {
				$pages = isset($module_data["pages"]) ? $module_data["pages"] : array();
				if (count($pages) > 0) {
					foreach ($pages as $page_data) {
						$ps_id = $page_data["ps_id"];
						$ps_ids[] = $ps_id;
					}
				}
			}
		}
		
		if (count($json_sts) == 0) {
			$r->errors = SELECT_BLOCK_DESC."<br/>";
		} else {
			foreach ($json_sts as $id => $block_data) {
				$block_name = $block_data["name"];
				$frame_tag = isset($block_data["frame_tag"]) ? $block_data["frame_tag"] : "";
				if (!strlen($frame_tag)) {
					$error_message = str_replace("{field_name}", $block_name.": ".LAYOUT_FRAME_MSG, REQUIRED_MESSAGE);
					$r->errors .= $error_message."<br/>";
				}
			}
		}

		if (!$r->errors) {
			foreach ($ps_ids as $ps_id) {
				// check page settings
				$sql  = " SELECT layout_id FROM ".$table_prefix . "cms_pages_settings";
				$sql .= " WHERE ps_id=" . $db->tosql($ps_id, INTEGER);
				$layout_id = get_db_value($sql);

				foreach ($json_sts as $id => $block_data) {
					$frame_tag = $block_data["frame_tag"];
					$pos_type = isset($block_data["pos_type"]) ? $block_data["pos_type"] : "end";
		  		$pos_number = isset($block_data["pos_number"]) ? $block_data["pos_number"] : "";
		  		$block_properties = isset($block_data["properties"]) ? $block_data["properties"] : "";
		  		$block_periods = isset($block_data["periods"]) ? $block_data["periods"] : "";

					// block settings
					$block_id = $block_data["id"];
					$block_operation = $block_data["operation"];
		  		$block_key = $block_data["key"];
		  		$block_tag_name = $block_data["tag_name"];
		  		$block_layout_type = $block_data["layout_type"];
		  		$block_layout_template = $block_data["layout_template"];
		  		$html_template = $block_data["html_template"];
		  		$css_class = $block_data["css_class"];
		  		$block_style = $block_data["block_style"];
		  		$block_title = $block_data["block_title"];

					// check frame id
					$frame_id = "";
					if (isset($layouts_frames[$layout_id]) && isset($layouts_frames[$layout_id][$frame_tag])) {
						$frame_id = $layouts_frames[$layout_id][$frame_tag];
					}
					if (strlen($frame_id)) {
						// frame exists we can add, update, remove block
						// check available blocks and their order in the frame first
						$pb_ids = array(); $min_order = 0; $max_order = 0;
						if ($block_operation == "delete" || $block_operation == "update") {
							$sql  = " SELECT pb_id, block_order FROM " . $table_prefix . "cms_pages_blocks ";
							$sql .= " WHERE ps_id=" . $db->tosql($ps_id, INTEGER);
							$sql .= " AND block_id=" . $db->tosql($block_id, INTEGER);
							$sql .= " AND frame_id=" . $db->tosql($frame_id, INTEGER);
							if ($block_key) {
								$sql .= " AND block_key=" . $db->tosql($block_key, TEXT);
							}
							$db->query($sql);
							while($db->next_record()) {
								$pb_id = $db->f("pb_id");
								$block_order = $db->f("block_order");
								$pb_ids[$pb_id] = $block_order;
							}
						} else {
							$sql  = " SELECT MIN(block_order) AS min_order, MAX(block_order) AS max_order ";
							$sql .= " FROM " . $table_prefix . "cms_pages_blocks ";
							$sql .= " WHERE ps_id=" . $db->tosql($ps_id, INTEGER);
							$sql .= " AND frame_id=" . $db->tosql($frame_id, INTEGER);
							$db->query($sql);
							if ($db->next_record()) {
								$min_order = $db->f("min_order");
								$max_order = $db->f("max_order");
							}
						}

						// set block values
						$pb->set_value("ps_id", $ps_id);
						$pb->set_value("block_id", $block_id);
						$pb->set_value("block_key", $block_key);
						$pb->set_value("frame_id", $frame_id);
						$pb->set_value("tag_name", $block_tag_name);
						$pb->set_value("layout_type", $block_layout_type);
						$pb->set_value("layout_template", $block_layout_template);
						$pb->set_value("html_template", $html_template);
						$pb->set_value("css_class", $css_class);
						$pb->set_value("block_style", $block_style);
						$pb->set_value("block_title", $block_title);
						if ($block_operation == "add") {
							// set block order 
							if ($pos_type == "start") {
								$block_order = 1;
							} else if ($pos_type == "end") {
								$block_order = $max_order + 1;
							} else if ($pos_type == "pos") {
								if (!$pos_number || $pos_number > ($max_order + 1)) {
									$block_order = $max_order + 1;
								} else {
									$block_order = $pos_number;
								}
							} else {
								$block_order = $max_order + 1;
							}
							$pb->set_value("block_order", $block_order);
							// update other frame blocks their order
							$sql  = " UPDATE " . $table_prefix . "cms_pages_blocks ";
							$sql .= " SET block_order=block_order+1 ";
							$sql .= " WHERE ps_id=" . $db->tosql($ps_id, INTEGER);
							$sql .= " AND frame_id=" . $db->tosql($frame_id, INTEGER);
							$sql .= " AND block_order>=" . $db->tosql($block_order, INTEGER);;
							$db->query($sql);

							// added block and check inserted value
							$pb->insert_record();
							$pb_id = $db->last_insert_id();
							$pb->set_value("pb_id", $pb_id);

							$pb_ids[$pb_id] = $block_order;
						} else if ($block_operation == "update") {
							foreach ($pb_ids as $pb_id => $block_order) {
								$pb_ids[$pb_id] = $block_order;
								$pb->set_value("pb_id", $pb_id);
								$pb->set_value("block_order", $block_order);
								$pb->update_record();
							}
						} else if ($block_operation == "delete") {
							foreach ($pb_ids as $pb_id => $block_order) {
								$pb_ids[$pb_id] = $block_order;
								$pb->set_value("pb_id", $pb_id);
								$pb->delete_record();
							}
						}
						// update properties and periods 
						if ($block_operation == "update" || $block_operation == "delete") {
							foreach ($pb_ids as $pb_id => $block_order) {
								$sql  = " DELETE FROM " . $table_prefix . "cms_blocks_settings ";
								$sql .= " WHERE pb_id=" . $db->tosql($pb_id, INTEGER);
								$db->query($sql);
								$sql  = " DELETE FROM " . $table_prefix . "cms_blocks_periods ";
								$sql .= " WHERE pb_id=" . $db->tosql($pb_id, INTEGER);
								$db->query($sql);
							}
						}
						if ($block_operation == "add" || $block_operation == "update") {
							foreach ($pb_ids as $pb_id => $block_order) {
								// adding block properties
								if (is_array($block_properties) && sizeof($block_properties) > 0) {
									foreach($block_properties as $property_id => $values) {
										foreach ($values as $value) {
											$value_id = "";
											$sql  = " SELECT * FROM " . $table_prefix . "cms_blocks_properties ";
											$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
											$db->query($sql);
											if ($db->next_record()) {
												$control_type = strtoupper($db->f("control_type"));
												$variable_name = $db->f("variable_name");
												$default_value = $db->f("default_value");
												if ($control_type == "CHECKBOXLIST" || $control_type == "RADIOBUTTON" || $control_type == "LISTBOX") {
													$sql  = " SELECT * FROM " . $table_prefix . "cms_blocks_values";
													$sql .= " WHERE value_id=" . $db->tosql($value, INTEGER);
													$db->query($sql);
													if ($db->next_record()) {
														$value_id = $value;
														$value_variable_name = $db->f("variable_name");
														if ($value_variable_name) { $variable_name = $value_variable_name; } 
														$variable_value = $db->f("variable_value");
														if (!strlen($variable_value)) {
															$variable_value = $db->f("value_name");
														}
													} else {
														$variable_value = $value;
													}
												} else if ($control_type == "LABEL") {
													$variable_value = $default_value;
												} else {
													$variable_value = $value;
												}
											} else {
												$variable_value = $value;
											}
									    
											// pages blocks settings
											$bs->set_value("ps_id", $ps_id);
											$bs->set_value("pb_id", $pb_id);
											$bs->set_value("property_id", $property_id);
											$bs->set_value("value_id", $value_id);
											$bs->set_value("variable_name", $variable_name);
											$bs->set_value("variable_value", $variable_value);
											$bs->insert_record();
										}
									} // end of block properties cycle
								}
						  
								// adding block periods
								if (is_array($block_periods) && sizeof($block_periods) > 0) {
									foreach($block_periods as $id => $period) {
										$start_date = parse_date($period["start_date"], "YYYY-MM-DD", $date_error);
										$end_date = parse_date($period["end_date"], "YYYY-MM-DD", $date_error);
										if (preg_match("/(\d{1,2}):(\d{1,2})/", $period["start_time"], $matches)) {
											$start_time = $matches[1]*60 + $matches[2];
										} else {
											$start_time = "";
										}
										if (preg_match("/(\d{1,2}):(\d{1,2})/", $period["end_time"], $matches)) {
											$end_time = $matches[1]*60 + $matches[2];
										} else {
											$end_time = "";
										}
										$week_days = $period["week_days"];
					    
										// add periods if they are not empty
										if (is_array($start_date) || is_array($end_date) || $start_time || $end_time || $week_days != 127) {
											$bp->set_value("ps_id", $ps_id);
											$bp->set_value("pb_id", $pb_id);
											$bp->set_value("start_date", $start_date);
											$bp->set_value("end_date", $end_date);
											$bp->set_value("start_time", $start_time);
											$bp->set_value("end_time", $end_time);
											$bp->set_value("week_days", $week_days);
											$bp->insert_record();
										}
									} 
								} // end of periods block 
							}
						}
					}
				}
			}
			// show success message
			$t->parse("success_block", false);
		}

	} else {
		// default values
	}

	// set page parameters
	$r->set_form_parameters();
	$t->set_var("sts", htmlspecialchars($sts));
	$t->set_var("pages_sts", htmlspecialchars($pages_sts));

	// set url parameters
	$admin_cms_pages_url = new VA_URL("admin_cms_pages.php", false);
	$admin_cms_pages_url->add_parameter("sort_ord", REQUEST, "sort_ord");
	$admin_cms_pages_url->add_parameter("sort_dir", REQUEST, "sort_dir");
	$admin_cms_pages_url->add_parameter("page", REQUEST, "page");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_cms_pages_href", "admin_cms_pages.php");
	$t->set_var("admin_cms_page_href", "admin_cms_page.php");
	$t->set_var("admin_cms_pages_url", $admin_cms_pages_url->get_url());


	// get list of available cms blocks which user can to all pages
	$db_blocks = array(); 
	$sql  = " SELECT cb.block_id, cb.block_name, cb.module_id, cm.module_code, cm.module_name ";
	$sql .= " FROM (" . $table_prefix . "cms_blocks cb ";	
	$sql .= " INNER JOIN " . $table_prefix . "cms_modules cm ON cb.module_id=cm.module_id)";	
	$sql .= " WHERE cb.pages_all=1 ";
	$sql .= " ORDER BY cm.module_order, cm.module_name, cb.block_order, cb.block_name ";	
	$db->query($sql);
	if ($db->next_record()) {
		do {
			$module_id = $db->f("module_id");
			$module_code = $db->f("module_code");
			$module_name = $db->f("module_name");
			if (!isset($db_blocks[$module_id])) {
				$db_blocks[$module_id] = array(
					"module_code" => $module_code, 
					"module_name" => $module_name, 
					"blocks" => array(), 
				);
			}

			$block_id = $db->f("block_id");
			$block_order = $db->f("block_order");
			$block_name = $db->f("block_name");

			$db_blocks[$module_id]["blocks"][] = array(
				"block_id" => $block_id, 
				"block_order" => $block_order, 
				"block_name" => $block_name, 
				"block_key" => "", 
			);

		} while ($db->next_record());
	}
	// prepare blocks
	$blocks = array(); 
	$modules_settings = array(
		"custom_blocks" => array(
			"sql" => " SELECT block_id, block_name FROM " . $table_prefix . "custom_blocks ",
			"key_field" => "block_id",
			"name_field" => "block_name",
		),
		"banners" => array(
			"sql" => " SELECT group_id, group_name FROM " . $table_prefix . "banners_groups ",
			"key_field" => "group_id",
			"name_field" => "group_name",
		),
		"custom_menus" => array(
			"sql" => " SELECT menu_id, menu_name AS menu_title FROM " . $table_prefix . "menus ORDER BY menu_name ",
			"key_field" => "menu_id",
			"name_field" => "menu_title",
		),
		"filters" => array(
			"sql" => " SELECT filter_id, filter_name FROM " . $table_prefix . "filters ",
			"key_field" => "filter_id",
			"name_field" => "filter_name",
		),
		"sliders" => array(
			"sql" => " SELECT slider_id, slider_name FROM " . $table_prefix . "sliders ORDER BY slider_id ",
			"key_field" => "slider_id",
			"name_field" => "slider_name",
		),
	);

	foreach ($db_blocks as $module_id => $module) {
		$module_code = $module["module_code"];
		$module_name = $module["module_name"];
		$module_blocks = $module["blocks"];
		if (isset($modules_settings[$module_code])) {
			$blocks[$module_id] = array(
				"module_code" => $module_code, 
				"module_name" => $module_name, 
				"blocks" => array(), 
			);
			$sql = $modules_settings[$module_code]["sql"];
			$key_field = $modules_settings[$module_code]["key_field"];
			$name_field = $modules_settings[$module_code]["name_field"];

			// populate blocks for a module
			$db->query($sql);
			while ($db->next_record()) {
				$key_value = $db->f($key_field);
				$name_value = get_translation($db->f($name_field));

				foreach ($module_blocks as $id => $block_info) {
					$block_id = $block_info["block_id"];
					$block_order = $block_info["block_order"];
					$block_name = $block_info["block_name"];
					$block_name = str_replace("{".$key_field."}", $key_value, $block_name);
					$block_name = str_replace("{".$name_field."}", $name_value, $block_name);

					$blocks[$module_id]["blocks"][] = array(
						"block_id" => $block_id, 
						"block_order" => $block_order, 
						"block_key" => $key_value, 
						"block_name" => $block_name, 
					);
				}
			}
			
		} else if ($module_code == "articles") {
			// check all custom blocks
			$sql  = " SELECT category_id, category_name FROM " . $table_prefix . "articles_categories ";
			$sql .= " WHERE parent_category_id=0 ";
			$db->query($sql);
			while ($db->next_record()) {
				$category_id = $db->f("category_id");
				$category_name = get_translation($db->f("category_name"));
				$block_key = $category_id;
				$article_module_id = $module_id."_".$category_id;
				$article_module_code = $module_code."_".$category_id;
				$article_module_name = str_replace("{category_name}", $category_name, $module_name);

				$blocks[$article_module_id] = array(
					"module_code" => $article_module_code, 
					"module_name" => $article_module_name, 
					"blocks" => array(), 
				);
				foreach ($module_blocks as $id => $block_info) {
					$block_id = $block_info["block_id"];
					$block_order = $block_info["block_order"];
					$block_name = $block_info["block_name"];
					$block_name = str_replace("{category_name}", $category_name, $block_name);

					$blocks[$article_module_id]["blocks"][] = array(
						"block_id" => $block_id, 
						"block_order" => $block_order, 
						"block_key" => $block_key, 
						"block_name" => $block_name, 
					);
				}

			}
		} else {
			$blocks[$module_id] = $module;
		}
	}

	// parse CMS blocks
	foreach ($blocks as $module_id => $module) {
		$module_code = $module["module_code"];
		$module_name = $module["module_name"];
		parse_value($module_name);
		$module_blocks = $module["blocks"];

		// prepare data to sort blocks by their order and name
		$blocks_orders = array(); $blocks_names = array();
		foreach ($module_blocks as $id => $block_info) {
			$block_id = $block_info["block_id"];
			$block_order = $block_info["block_order"];
			$block_key = $block_info["block_key"];
			$block_name = $block_info["block_name"];
			parse_value($block_name); 
			$module_blocks[$id]["block_name"] = $block_name;

			$blocks_orders[$id] = $block_order;
			$blocks_names[$id] = $block_name;
		}
		// sort blocks
		array_multisort($blocks_orders, SORT_ASC, $blocks_names, SORT_ASC, $module_blocks);

		foreach ($module_blocks as $id => $block_info) {
			$block_id = $block_info["block_id"];
			$block_name = $block_info["block_name"];
			//parse_value($block_name); 
			$block_key = $block_info["block_key"];

			$t->set_var("module_id", $module_id);
			$t->set_var("block_id", $block_id);
			$t->set_var("block_name", $block_name);
			$t->set_var("block_key", $block_key);
  
			$t->parse("cms_blocks", true);
		}
	
		// set module tags
		if ($module_id == "1") {
			$t->set_var("module_class", "leftNavActive");
		} else {
			$t->set_var("module_class", "leftNavNonActive");
		}
		$t->set_var("module_id", $module_id);
		$t->set_var("module_name", $module_name);
		$t->parse("cms_blocks_modules", true);
		$t->set_var("cms_blocks", "");
	}

	$frames = array(array("",""));
	$sql  = " SELECT frame_name, tag_name FROM ".$table_prefix."cms_frames ";
	$sql .= " GROUP BY tag_name, frame_name ";
	$db->query($sql);
	while ($db->next_record()) {
		$tag_name = $db->f("tag_name");
		$frame_name = $db->f("frame_name"); 
		parse_value($frame_name);
		$frames[] = array($tag_name, $frame_name);
	}
	set_options($frames, "", "frame_tag");

	$positions = array(
		array("", ""),
		array("start", FRAME_START_POS_MSG),
		array("end", FRAME_END_POS_MSG),
		array("pos", FRAME_SPECIFY_POS_MSG),
	);
	set_options($positions, "", "pos_type");


	// parse block template
	$t->set_var("id_tag", "{id_tag}");
	$t->set_var("frame_id", "{frame_id}");
	$t->set_var("frame_name", "{frame_name}");
	$t->set_var("module_name", "{module_name}");
	$t->set_var("block_id", "{block_id}");
	$t->set_var("block_key", "{block_key}");
	$t->set_var("block_name", "{block_name}");
	$t->set_var("block_position", "{block_position}");
	$t->set_var("operation_class", "{operation_class}");
	$t->set_var("operation", "{operation}");
	$t->parse("block_template", false);

	// multisites
	if ($sitelist) {
		$sites   = get_db_values("SELECT site_id,site_name FROM " . $table_prefix . "sites ORDER BY site_id ", "");
		set_options($sites, $param_site_id, "param_site_id");
		$t->sparse("sitelist", false);
	}	

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

function generate_cms_params($params)
{
	$param_index = 0; $params_string = "";
	foreach ($params as $param_name => $param_value) {
		if (!is_array($param_value)) {
			if ($param_index > 0) { $params_string .= "&"; }
			$params_string .= $param_name ."=". prepare_js_value($param_value);
			$param_index++;
		}
	}
	return $params_string;
}

?>