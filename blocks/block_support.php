<?php

	$default_title = "{SUPPORT_TITLE}";

	include_once("./includes/record.php");
	include_once("./includes/support_functions.php");
	include_once("./messages/" . $language_code . "/support_messages.php");

	$var_deps_ids = get_setting_value($vars, "deps_ids", "");
	if (!strlen($var_deps_ids)) { 
		$var_deps_ids = get_setting_value($vars, "dep_ids", "");
	}
	$var_types_ids = get_setting_value($vars, "types_ids", "");
	if (!strlen($var_types_ids)) { 
		$var_types_ids = get_setting_value($vars, "type_ids", "");
	}

	$support_fields = get_support_fields();

	$html_template = get_setting_value($block, "html_template", "block_support.html"); 
  $t->set_file("block_body", $html_template);
	set_script_tag("js/attachments.js");
	$errors = false;
	
	$user_id = get_session("session_user_id");
	$support_settings = get_settings("support");
	$operation = get_param("operation");

	// save departments and types settings in global JS array
	if (!isset($js_settings["support"])) {
		$js_settings["support"] = array();
	}
	if (!isset($js_settings["support"]["settings"])) {
		$js_settings["support"]["settings"] = array();
	}
	if (!isset($js_settings["support"]["deps"])) {
		$js_settings["support"]["deps"] = array();
	}
	if (!isset($js_settings["types"])) {
		$js_settings["support"]["types"] = array();
	}
	// prepare global settings for JS
	$global_fields = array();
	foreach ($support_fields as $param_name => $field_data)  {
		$setting_name = $field_data["setting_name"];
		$default_class = $field_data["class"];
		$name_constant = $field_data["name_constant"];
		$default_name = $field_data["default_name"];
		$default_show = $field_data["show"];
		$default_required = $field_data["required"];
		$default_order= $field_data["order"];
		$field_name = get_setting_value($support_settings, $setting_name."_field_name");
		if (!strlen($field_name)) { $field_name = $default_name; }
		$field_show = get_setting_value($support_settings, $setting_name."_field_show");
		if (!strlen($field_show)) { $field_show = $default_show; }
		$field_required = get_setting_value($support_settings, $setting_name."_field_required");
		if (!strlen($field_required)) { $field_required = $default_required ; }
		$field_order = get_setting_value($support_settings, $setting_name."_field_order");
		if (!strlen($field_order)) { $field_order = $default_order; }
		$global_fields[$param_name] = array(
			"name" => $field_name, "class" => $default_class, "show" => $field_show, "required" => $field_required, "order" => $field_order,
		);
	}
	$js_settings["support"]["fields"] = $global_fields;


	$eol = get_eol();
	$submit_tickets = intval(get_setting_value($support_settings, "submit_tickets", 0));
	$use_random_image = intval(get_setting_value($support_settings, "use_random_image", 1));
	$attachments_users_allowed = get_setting_value($support_settings, "attachments_users_allowed", 0);
	if (!$user_id) { $attachments_users_allowed = 0; }

	if ($submit_tickets == 1) {
		check_user_session();
	}

	$site_name = get_setting_value($settings, "site_name", "");
	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", "");
	$admin_site_url = get_setting_value($settings, "admin_site_url", $site_url."admin/");

	$site_domain = ""; $secure_domain = "";
	$parsed_url = parse_url($site_url);
	if (isset($parsed_url["scheme"])) {
		$site_domain = $parsed_url["scheme"]."://".$parsed_url["host"];
	}
	$parsed_url = parse_url($secure_url);
	if (isset($parsed_url["scheme"])) {
		$secure_domain = $parsed_url["scheme"]."://".$parsed_url["host"];
	}
	$secure_user_ticket = get_setting_value($settings, "secure_user_ticket", 0);
	$secure_user_tickets = get_setting_value($settings, "secure_user_tickets", 0);
	if ($secure_user_ticket) {
		$support_url = $secure_domain . get_request_uri()."#pb_".$pb_id;
		$support_messages_url = $secure_url . get_custom_friendly_url("support_messages.php");
		$user_support_attachments_url = $secure_url . get_custom_friendly_url("user_support_attachments.php");
	} else {
		$support_url = $site_domain. get_request_uri()."#pb_".$pb_id;
		$support_messages_url = $site_url . get_custom_friendly_url("support_messages.php");
		$user_support_attachments_url = $site_url . get_custom_friendly_url("user_support_attachments.php");
	}
	if ($secure_user_tickets) {
		$user_support_url = $secure_url . get_custom_friendly_url("user_support.php");
	} else {
		$user_support_url = $site_url . get_custom_friendly_url("user_support.php");
	}
	$user_home_url = $site_url . get_custom_friendly_url("user_home.php");

	if (($use_random_image == 2) || ($use_random_image == 1 && !strlen(get_session("session_user_id")))) { 
		$use_validation = true;
	} else {
		$use_validation = false;
	}
	
	// prepare custom options
	$custom_ids = array();
	$sql  = " SELECT scp.* ";
	$sql .= " FROM " . $table_prefix . "support_custom_properties scp ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_custom_sites scs ON scp.property_id=scs.property_id ";
	$sql .= " WHERE (scp.sites_all=1 OR scs.site_id=" . $db->tosql($site_id, INTEGER).") ";
	if ($user_id) {
		$sql .= " AND scp.property_show IN (1,3) ";
	} else {
		$sql .= " AND scp.property_show IN (1,2) ";
	}
	$sql .= " GROUP BY scp.property_id ";
	$db->query($sql);
	while ($db->next_record()) {
		$property_id = $db->f("property_id");
		$custom_ids[] = $property_id;
	}

	$pp = array(); $type_property_ids = array(); $dep_property_ids = array(); 
	$sql  = " SELECT scp.* ";
	$sql .= " FROM " . $table_prefix . "support_custom_properties scp ";
	$sql .= " WHERE scp.property_id IN (" . $db->tosql($custom_ids, INTEGER_LIST).") ";
	$sql .= " ORDER BY scp.property_order, scp.property_id ";
	$db->query($sql);
	while ($db->next_record()) {
		$property_id = $db->f("property_id");
		$types_all = $db->f("types_all");
		$deps_all = $db->f("deps_all");
		if (!$types_all) {
			$type_property_ids[] = $property_id; 
		}
		if (!$deps_all) {
			$dep_property_ids[] = $property_id; 
		}

		$pp[$property_id] = array(
			"property_id" => $db->f("property_id"),
			"property_order" => $db->f("property_order"),
			"property_name" => $db->f("property_name"),
			"property_description" => $db->f("property_description"),
			"default_value" => $db->f("default_value"),
			"property_style" => $db->f("property_style"),
			"control_type" => $db->f("control_type"),
			"control_style" => $db->f("control_style"),
			"control_code" => $db->f("control_code"),
			"onchange_code" => $db->f("onchange_code"),
			"onclick_code" => $db->f("onclick_code"),
			"required" => $db->f("required"),
			"before_name_html" => $db->f("before_name_html"),
			"after_name_html" => $db->f("after_name_html"),
			"before_control_html" => $db->f("before_control_html"),
			"after_control_html" => $db->f("after_control_html"),
			"validation_regexp" => $db->f("validation_regexp"),
			"regexp_error" => $db->f("regexp_error"),
			"options_values_sql" => $db->f("options_values_sql"),
			"types" => Array(),
			"types_all" => $types_all,
			"deps" => Array(),
			"deps_all" => $deps_all,
		);
	}

	// get departments ids when show properties
	$sql  = " SELECT scd.* ";
	$sql .= " FROM " . $table_prefix . "support_custom_departments scd ";
	$sql .= " WHERE scd.property_id IN (" . $db->tosql($dep_property_ids, INTEGER_LIST).") ";
	$db->query($sql);
	while ($db->next_record()) {
		$property_id = $db->f("property_id");
		$dep_id = $db->f("dep_id");
		$pp[$property_id]["deps"][$dep_id] = $dep_id;
	}

	// get type ids when show properties
	$sql  = " SELECT sct.* ";
	$sql .= " FROM " . $table_prefix . "support_custom_types sct ";
	$sql .= " WHERE sct.property_id IN (" . $db->tosql($type_property_ids, INTEGER_LIST).") ";
	$db->query($sql);
	while ($db->next_record()) {
		$property_id = $db->f("property_id");
		$type_id = $db->f("type_id");
		$pp[$property_id]["types"][$type_id] = $type_id;
	}

	$t->set_var("site_url", $settings["site_url"]);

	$t->set_var("support_href", $support_url);
	$t->set_var("user_home_href", $user_home_url);
	$t->set_var("user_support_href", $user_support_url);
	$t->set_var("user_support_attachments_url", $user_support_attachments_url);
	$t->set_var("rnd", va_timestamp());


	$support_deps_values = array(array("", ""));
	$active_dep_id = ""; $number_of_deps = 0; $deps_data = array(); $deps_ids = array(); 
	$sql  = " SELECT d.dep_id, d.is_default, d.dep_order, d.dep_name, d.intro_text, d.new_admin_mail, d.new_user_mail, d.dep_settings ";
	$sql .= " FROM (" . $table_prefix . "support_departments d ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_departments_sites ds  ON (ds.dep_id=d.dep_id AND d.sites_all=0))";	
	$sql .= " WHERE d.show_for_user=1 ";
	$sql .= " AND (d.sites_all=1 OR ds.site_id=" . $db->tosql($site_id, INTEGER, true, false).") ";		
	if (strlen($var_deps_ids)) {
		$sql .= " AND d.dep_id IN (" . $db->tosql($var_deps_ids, INTEGER_LIST).") ";
	}
	$db->query($sql);
	if ($db->next_record()) {
		do {
			$number_of_deps++;
			$dep_id = $db->f("dep_id");
			$is_default = $db->f("is_default");
			$dep_order = $db->f("dep_order");
			$dep_name = get_translation($db->f("dep_name"));
			$intro_text = get_translation($db->f("intro_text"));
			$dep_settings	= json_decode($db->f("dep_settings"), true);
			if (!$operation && $is_default) {
				$active_dep_id = $dep_id;
			}
			$deps_ids[$dep_id] = $dep_id;
			$support_deps_values[] = array($dep_id, $dep_name);

			// prepare department fields settings
			$dep_fields = array();
			foreach ($support_fields as $param_name => $field_data)  {
				$setting_name = $field_data["setting_name"];
				$field_name = get_setting_value($dep_settings, $setting_name."_field_name");
				if (!strlen($field_name)) { $field_name = $global_fields[$param_name]["name"]; }
				$field_show = get_setting_value($dep_settings, $setting_name."_field_show");
				if (!strlen($field_show)) { $field_show = $global_fields[$param_name]["show"]; }
				$field_required = get_setting_value($dep_settings, $setting_name."_field_required");
				if (!strlen($field_required)) { $field_required = $global_fields[$param_name]["required"]; }
				$field_order = get_setting_value($dep_settings, $setting_name."_field_order");
				if (!strlen($field_order)) { $field_order = $global_fields[$param_name]["order"]; }
				$dep_fields[$param_name] = array(
					"name" => $field_name, "show" => $field_show, "required" => $field_required, "order" => $field_order,
				);
			}

			$js_settings["support"]["deps"][$dep_id] = array(
				"id" => $dep_id,
				"order" => $dep_order,
				"name" => $dep_name,
				"intro" => $intro_text,
				"fields" => $dep_fields,
			);
			$deps_data[$dep_id] = $db->Record;
		} while ($db->next_record());
	} else {
		$block_parsed = true;
		$t->set_var("errors_list", 'No support department is availiable');
		$t->parse("support_error", false);
		$block_parsed = true;
		return false;
	}
	if ($number_of_deps > 1)  {
	}

	if ($operation) {
		$active_dep_id = get_param("dep_id");
	} else if ($number_of_deps == 1) {
		$active_dep_id = $support_deps_values[1][0];	
	}

	// get support types
	$support_types_values = array(array("", ""));
	$number_of_types = 0; $active_types = 0;
	$data_types = array(); $types_ids = array();
	$sql  = " SELECT st.type_id, st.is_default, st.type_order, st.type_name, st.intro_text, st.deps_all, std.dep_id ";
	$sql .= " FROM " . $table_prefix . "support_types st ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_types_departments std ON st.type_id=std.type_id ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_types_sites sts ON st.type_id=sts.type_id ";
	$sql .= " WHERE st.show_for_user=1 ";
	$sql .= " AND (st.sites_all=1 OR sts.site_id=" . $db->tosql($site_id, INTEGER).") ";		
	$sql .= " AND (st.deps_all=1 OR std.dep_id IN (" . $db->tosql($deps_ids, INTEGER_LIST).")) ";
	if (strlen($var_types_ids)) {
		$sql .= " AND st.type_id IN (" . $db->tosql($var_types_ids, INTEGER_LIST).") ";
	}
	$sql .= " ORDER BY st.type_order ";
	$db->query($sql);
	if ($db->next_record()) {
		do {
			$number_of_types++;
			$type_id = $db->f("type_id");
			$is_default = $db->f("is_default");
			$type_order = $db->f("type_order");
			$type_name = get_translation($db->f("type_name"));
			$intro_text = get_translation($db->f("intro_text"));
			$deps_all = $db->f("deps_all");
			$type_dep_id = $db->f("dep_id");
			$types_ids[$type_id] = $type_id;

			$data_types[$type_id] = $db->Record;

			if (!isset($js_settings["support"]["types"][$type_id])) {
				if ($deps_all || ($active_dep_id && $active_dep_id == $type_dep_id)) {
					$active_types++;
					$support_types_values[] = array($type_id, $type_name);
				}
				$js_settings["support"]["types"][$type_id] = array(
					"id" => $type_id,
					"default" => $is_default,
					"order" => $type_order,
					"name" => $type_name,
					"intro" => $intro_text,
					"deps_all" => $deps_all,
					"deps" => array(),
				);
			}
			if (strlen($type_dep_id)) {
				$js_settings["support"]["types"][$type_id]["deps"][$type_dep_id] = $type_dep_id;
			}
			
		} while ($db->next_record());
	}

	// get support products
	$support_products = array(array("", ""));
	$number_of_products = 0; $active_products = 0;
	$sql  = " SELECT sp.product_id, sp.product_order, sp.product_name, sp.deps_all, spd.dep_id  ";
	$sql .= " FROM " . $table_prefix . "support_products AS sp ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_products_departments spd ON sp.product_id=spd.product_id ";
	$sql .= " LEFT JOIN " . $table_prefix . "support_products_sites sps ON sp.product_id=sps.product_id ";
	$sql .= " WHERE sp.show_for_user=1 ";
	$sql .= " AND (sp.sites_all=1 OR sps.site_id=" . $db->tosql($site_id, INTEGER).") ";		
	$sql .= " AND (sp.deps_all=1 OR spd.dep_id IN (" . $db->tosql($deps_ids, INTEGER_LIST).")) ";
	$sql .= " ORDER BY sp.product_order, sp.product_name ";
	$db->query($sql);
	if ($db->next_record()) {
		do {
			$number_of_products++;
			$product_id = $db->f("product_id");
			$product_order = $db->f("product_order");
			$product_name = get_translation($db->f("product_name"));
			$deps_all = $db->f("deps_all");
			$product_dep_id = $db->f("dep_id");

			if (!isset($js_settings["support"]["products"][$product_id])) {
				if ($deps_all || ($active_dep_id && $active_dep_id == $product_dep_id)) {
					$active_products++;
					$support_products[] = array($product_id, $product_name);
				}
				$js_settings["support"]["products"][$product_id] = array(
					"id" => $product_id,
					"order" => $product_order,
					"name" => $product_name,
					"deps_all" => $deps_all,
					"deps" => array(),
				);
			}
			if (strlen($product_dep_id)) {
				$js_settings["support"]["products"][$product_id]["deps"][$product_dep_id] = $product_dep_id;
			}
			
		} while ($db->next_record());
	}

	$r = new VA_Record($table_prefix . "support", "support");
	$r->add_where("support_id", INTEGER);
	$r->add_textbox("site_id", INTEGER);
	$r->change_property("site_id", USE_SQL_NULL, false);
	$r->add_textbox("user_id", INTEGER);
	$r->change_property("user_id", USE_SQL_NULL, false);
	$r->add_textbox("affiliate_code", TEXT);
	$r->change_property("affiliate_code", USE_SQL_NULL, false);
	$r->add_textbox("user_name", TEXT);
	$r->change_property("user_name", TRIM, true);
	$r->add_textbox("user_email", TEXT);
	$r->change_property("user_email", REGEXP_MASK, EMAIL_REGEXP);
	$r->change_property("user_email", TRIM, true);
	$r->add_textbox("remote_address", TEXT);
	$r->add_textbox("identifier", TEXT);
	$r->add_textbox("environment", TEXT);
	$r->add_select("dep_id", INTEGER, $support_deps_values);
	$r->change_property("dep_id", REQUIRED, true);
	if ($active_dep_id) {
		$r->change_property("dep_id", DEFAULT_VALUE, $active_dep_id);
	}
	$r->change_property("dep_id", BEFORE_SHOW, "support_param_show");
	$r->change_property("dep_id", AFTER_SHOW, "support_param_show");
	$r->add_select("support_type_id", INTEGER, $support_types_values);
	$r->change_property("support_type_id", USE_SQL_NULL, false);
	if ($active_types == 1) {
		$r->change_property("support_type_id", DEFAULT_VALUE, $support_types_values[1][0]);
	}
	$r->change_property("support_type_id", BEFORE_SHOW, "support_param_show");
	$r->change_property("support_type_id", AFTER_SHOW, "support_param_show");
	$r->add_select("support_product_id", INTEGER, $support_products);
	$r->change_property("support_product_id", USE_SQL_NULL, false);
	$r->add_textbox("summary", TEXT);
	$r->change_property("summary", TRIM, true);
	$r->change_property("summary", USE_SQL_NULL, false);

	$r->add_hidden("attachments", TEXT);
	$r->change_property("attachments", SHOW, $attachments_users_allowed);
	$r->change_property("attachments", VALIDATION, false);
	$r->change_property("attachments", BEFORE_VALIDATE, "validate_ticket_attachments");
	$r->change_property("attachments", BEFORE_SHOW, "show_ticket_attachments");

	$r->add_textbox("description", TEXT);
	$r->change_property("description", TRIM, true);
	$r->add_textbox("support_status_id", INTEGER);
	$r->add_textbox("support_priority_id", INTEGER);
	$r->add_textbox("admin_id_assign_to", INTEGER);
	$r->add_textbox("admin_id_assign_by", INTEGER);
	$r->add_textbox("admin_id_added_by", INTEGER);
	$r->change_property("admin_id_added_by", USE_SQL_NULL, false);
	$r->add_textbox("admin_id_modified_by", INTEGER);
	$r->change_property("admin_id_modified_by", USE_SQL_NULL, false);
	$r->add_textbox("date_added", DATETIME);
	$r->add_textbox("date_modified", DATETIME);
	$r->add_textbox("validation_number", TEXT, va_message("VALIDATION_CODE_FIELD"));
	$r->change_property("validation_number", USE_IN_INSERT, false);
	$r->change_property("validation_number", USE_IN_UPDATE, false);
	$r->change_property("validation_number", USE_IN_SELECT, false);
	if ($use_validation) {
		$r->change_property("validation_number", REQUIRED, true);
		$r->change_property("validation_number", SHOW, true);
	} else {
		$r->change_property("validation_number", REQUIRED, false);
		$r->change_property("validation_number", SHOW, false);
	}
	$r->change_property("validation_number", BEFORE_SHOW, "show_ticket_validation_number");
	$r->change_property("validation_number", AFTER_SHOW, "show_ticket_validation_number");

	// check global settings for predefined fields
	foreach ($support_fields as $param_name => $field_data)  {
		$setting_name = $field_data["setting_name"];
		$name_constant = $field_data["name_constant"];
		$default_show = $field_data["show"];
		$default_required = $field_data["required"];
		$default_order= $field_data["order"];
		$field_show = get_setting_value($support_settings, $setting_name."_field_show");
		$field_name = get_setting_value($support_settings, $setting_name."_field_name");
		$field_required = get_setting_value($support_settings, $setting_name."_field_required");
		$field_order = get_setting_value($support_settings, $setting_name."_field_order");
		if (strlen($field_show)) {
			$r->change_property($param_name, CONTROL_HIDE, !$field_show);
		} else if (!$default_show) {
			$r->change_property($param_name, CONTROL_HIDE, true);
		}
		if (strlen($field_name)) {
			$r->change_property($param_name, CONTROL_DESC, $field_name);
		} else if ($name_constant) {
			$r->change_property($param_name, CONTROL_DESC, va_message($name_constant));
		}
		if (strlen($field_required)) {
			$r->change_property($param_name, REQUIRED, $field_required);
		} else if ($default_required) {
			$r->change_property($param_name, REQUIRED, true);
		}
		if (strlen($field_order)) {
			$r->change_property($param_name, CONTROL_ORDER, doubleval($field_order));
		} else if ($default_order) {
			$r->change_property($param_name, CONTROL_ORDER, doubleval($default_order));
		}
		if (strlen($field_name) && $name_constant) {
			$t->set_var($name_constant, $field_name);
		}
	}
	// end of global settings check for predefined fields

	// check department settings for predefined fields
	$dep_success_message = ""; $dep_new_status_id = "";
	if ($active_dep_id && isset($deps_data[$active_dep_id])) {
		$dep_settings	= json_decode(get_setting_value($deps_data[$active_dep_id], "dep_settings"), true);
		$dep_new_status_id = get_setting_value($dep_settings, "new_status_id");
		$dep_success_message = get_translation(get_setting_value($dep_settings, "success_message"));
		foreach ($support_fields as $param_name => $field_data)  {
			$setting_name = $field_data["setting_name"];
			$name_constant = $field_data["name_constant"];
			$field_show = get_setting_value($dep_settings, $setting_name."_field_show");
			$field_name = get_setting_value($dep_settings, $setting_name."_field_name");
			$field_required = get_setting_value($dep_settings, $setting_name."_field_required");
			$field_order = get_setting_value($dep_settings, $setting_name."_field_order");
			if (strlen($field_show)) {
				$r->change_property($param_name, CONTROL_HIDE, !$field_show);
			}
			if (strlen($field_name)) {
				$r->change_property($param_name, CONTROL_DESC, $field_name);
			}
			if (strlen($field_required)) {
				$r->change_property($param_name, REQUIRED, $field_required);
			}
			if (strlen($field_order)) {
				$r->change_property($param_name, CONTROL_ORDER, doubleval($field_order));
			}
			if (strlen($field_name) && $name_constant) {
				$t->set_var($name_constant, $field_name);
			}
		}
	}
	if ($active_types == 0) {
		$r->change_property("support_type_id", CONTROL_HIDE, true);
		$r->change_property("support_type_id", REQUIRED, false);
	}
	if ($active_products == 0) {
		$r->change_property("support_product_id", CONTROL_HIDE, true);
		$r->change_property("support_product_id", REQUIRED, false);
	}
	// end of department settings check for predefined fields
	
	foreach ($pp as $property_id => $pp_row) {
		$control_type = $pp_row["control_type"];
		$param_name = "pp_" . $pp_row["property_id"];
		$param_title = $pp_row["property_name"];
		$field_order = $pp_row["property_order"];

		if ($control_type == "CHECKBOXLIST") {
			$r->add_checkboxlist($param_name, TEXT, "", $param_title);
		} elseif ($control_type == "RADIOBUTTON") {
			$r->add_radio($param_name, TEXT, "", $param_title);
		} elseif ($control_type == "LISTBOX") {
			$r->add_select($param_name, TEXT, "", $param_title);
		} else {
			$r->add_textbox($param_name, TEXT, $param_title);
		}
		if ($control_type == "CHECKBOXLIST" || $control_type == "RADIOBUTTON" || $control_type == "LISTBOX") {
			if ($pp_row["options_values_sql"]) {
				$sql = $pp_row["options_values_sql"];
			} else {
				$sql  = " SELECT property_value_id, property_value FROM " . $table_prefix . "support_custom_values ";
				$sql .= " WHERE property_id=" . $db->tosql($pp_row["property_id"], INTEGER) . " AND hide_value=0";
				$sql .= " ORDER BY property_value_id ";
			}
			$r->change_property($param_name, VALUES_LIST, get_db_values($sql, ""));
		}
		if ($pp_row["required"] == 1) {
			$r->change_property($param_name, REQUIRED, true);
		}
		if ($pp_row["validation_regexp"]) {
			$r->change_property($param_name, REGEXP_MASK, $pp_row["validation_regexp"]);
			if ($pp_row["regexp_error"]) {
				$r->change_property($param_name, REGEXP_ERROR, $pp_row["regexp_error"]);
			}
		}
		$r->change_property($param_name, CONTROL_ORDER, intval($field_order));
		$r->change_property($param_name, USE_IN_SELECT, false);
		$r->change_property($param_name, USE_IN_INSERT, false);
		$r->change_property($param_name, USE_IN_UPDATE, false);
		$r->change_property($param_name, BEFORE_SHOW, "show_ticket_custom_field", array("property_id" => $property_id));
	}
	// sort form parameters before any operation with them
	$r->sort_parameters();

	$validation_class = "normal"; 

	$operation = get_param("operation");
	$rnd = get_param("rnd");
	$filter = get_param("filter");
	$remote_address = get_ip();

	$session_rnd = get_session("session_rnd");
	if ($operation && $rnd != $session_rnd)
	{
		set_session("session_rnd", $rnd);

		$r->get_form_values();
		$r->set_value("affiliate_code", get_session("session_af"));

		if ($number_of_deps == 1) {
			$r->set_value("dep_id", $support_deps_values[1][0]);	
		}
		
		if ($number_of_types == 1) {
			$r->set_value("support_type_id", $support_types_values[1][0]);
		} elseif ($number_of_types == 0) {
			$r->set_value("support_type_id", 0);
		}		
		
		foreach ($pp as $property_id => $data) {
			$param_name = "pp_".$property_id;
			$field_required = $data["required"];
			$property_types = $data["types"];
			$types_all = $data["types_all"];
			$property_deps = $data["deps"];
			$deps_all = $data["deps_all"];
			$dep_id = $r->get_value("dep_id");
			$support_type_id = $r->get_value("support_type_id");
			// if some custom field isn't available for some department or type set required option to false
			if (
				(!$types_all && (!strlen($support_type_id) || !isset($property_types[$support_type_id]))) ||
				(!$deps_all && (!strlen($dep_id) || !isset($property_deps[$dep_id])))
			) {
				$field_required = false;
				$r->change_property($param_name, REQUIRED, false);
			}

			if ($r->is_empty($param_name) && $field_required) {
				$pp[$property_id]["property_class"] = "error";
			}
		}

		$form_valid = $r->validate();
		if ($form_valid) {
			if (check_banned_content($r->get_value("description"))) {
				$form_valid = false;
				$r->errors = va_message("BANNED_CONTENT_MSG"); 
			}
		}

		if (blacklist_check("support") == "blocked") {
			$r->errors = va_message("BLACK_IP_MSG")."<br>";	
		}

		if ($use_validation) {
			if ($r->is_empty("validation_number")) {
				$validation_class = "error"; 
			} else {
				$validated_number = check_image_validation($r->get_value("validation_number"));
				if (!$validated_number) {
					$validation_class = "error"; 
					$r->errors .= str_replace("{field_name}", VALIDATION_CODE_FIELD, VALIDATION_MESSAGE);
				} elseif ($r->errors) {
					// saved validated number for following submits	
					set_session("session_validation_number", $validated_number);
				}
			} 
		}

		if (strlen($r->errors)) {
			$errors = true;
			set_session("session_rnd", "");
		}

		if (!$errors)
		{
			$ticket_user_id = $user_id;
			$user_email = trim($r->get_value("user_email"));

			// if summary empty set type name instead
			if ($r->is_empty("summary")) {
				$r->set_value("summary", $data_types[$support_type_id]["type_name"]);
			}

			// if user is not registered check user_id for new ticket by email
			if (!$ticket_user_id && $user_email) {
				$sql  = " SELECT user_id FROM " . $table_prefix . "users ";
				$sql .= " WHERE email=" . $db->tosql($user_email, TEXT);
				$db->query($sql);
				if ($db->next_record()) {
					$ticket_user_id = $db->f("user_id");
				}
			}

			// get status for new message
			$status_id = ""; $status_name = ""; $status_caption = "";
			// check department special NEW status
			if (strlen($dep_new_status_id)) {
				$sql  = " SELECT status_id,status_name,status_caption FROM " . $table_prefix . "support_statuses ";
				$sql .= " WHERE status_id=" . $db->tosql($dep_new_status_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$status_id = $db->f("status_id");
					$status_name = get_translation($db->f("status_name"));
					$status_caption = get_translation($db->f("status_caption"));
				}
			}
			if (!strlen($status_id)) {
				$sql  = " SELECT status_id,status_name,status_caption FROM " . $table_prefix . "support_statuses ";
				$sql .= " WHERE status_type='NEW' ";
				$db->query($sql);
				if ($db->next_record()) {
					$status_id = $db->f("status_id");
					$status_name = get_translation($db->f("status_name"));
					$status_caption = get_translation($db->f("status_caption"));
				} else {
					$status_id = 0;
					$status_name = va_message("NEW_MSG");
					$status_caption = va_message("NEW_MSG");
				}
			}
			// set status
			$r->set_value("support_status_id", $status_id);	

			// get priority for new message
			$priority_id = 0;
			$sql  = " SELECT sp.priority_id, sup.priority_expiry ";
			$sql .= " FROM " . $table_prefix . "support_priorities sp, " . $table_prefix . "support_users_priorities sup ";
			$sql .= " WHERE sp.priority_id=sup.priority_id ";
			if ($user_id > 0) {
				$sql .= " AND (user_id=" . $db->tosql($user_id, INTEGER);
				$sql .= " OR user_email=" . $db->tosql($user_email, TEXT) . ")";
			} else {
				$sql .= " AND user_email=" . $db->tosql($user_email, TEXT);
			}
			$db->query($sql);
			if ($db->next_record()) {
				$priority_id = $db->f("priority_id");	
				$current_ts = va_timestamp();
				$priority_expiry = $db->f("priority_expiry", DATETIME);
				if (is_array($priority_expiry)) {
					$priority_expiry_ts = va_timestamp($priority_expiry); 
					if ($current_ts > $priority_expiry_ts) {
						// user rank expired
						$priority_id = 0;
					}
				}
			} 
			if (!$priority_id) {
				$sql  = " SELECT priority_id FROM " . $table_prefix . "support_priorities WHERE is_default=1 ";
				$db->query($sql);
				if ($db->next_record()) {
					$priority_id = $db->f("priority_id");	
				}
			}
			$date_added = va_time();
			
			if (isset($site_id)) {
				$r->set_value("site_id", $site_id);
			} else {
				$r->set_value("site_id", 1);
			}
			$r->set_value("user_id", $ticket_user_id);
			$r->set_value("date_added", $date_added);
			$r->set_value("date_modified", va_time());
			$r->set_value("remote_address", $remote_address);
			$r->set_value("admin_id_assign_to", 0);
			$r->set_value("admin_id_assign_by", 0);
			if (get_session("session_admin_id")) {
				$r->set_value("admin_id_added_by", get_session("session_admin_id"));
			} else {
				$r->set_value("admin_id_added_by", 0);
			}
			$r->set_value("admin_id_modified_by", 0);
			$r->set_value("support_priority_id", $priority_id);
			
			if ($r->insert_record())
			{	
				if ($dep_success_message) {
					$r->success_message = $dep_success_message;
				} else {
					$r->success_message = va_message("SUPPORT_REQUEST_ADDED_MSG");
				}
				// get new ticket id
				$support_id = $db->last_insert_id();
				$r->set_value("support_id", $support_id);
				$vc = md5($support_id . $date_added[3].$date_added[4].$date_added[5]);
				$ticket_url = $support_messages_url . "?support_id=".$support_id."&vc=".$vc;
				$admin_ticket_url = $admin_site_url."admin_support_reply.php?support_id=".$support_id;
				// get department id
				$ticket_dep_id = $r->get_value("dep_id");
				$ticket_type_id = $r->get_value("support_type_id");
				$ticket_product_id = $r->get_value("support_product_id");

				update_support_properties($pp, $r, $support_id);

				// update attachments
				$sql  = " UPDATE " . $table_prefix . "support_attachments ";
				$sql .= " SET support_id=" . $db->tosql($support_id, INTEGER);
				$sql .= " , attachment_status=1 ";
				$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
				$sql .= " AND support_id=0 ";
				$sql .= " AND message_id=0 ";
				$sql .= " AND attachment_status=0 ";
				$db->query($sql);

				// check attachments
				$attachments = array();
				if ($user_id) {
					$sql  = " SELECT attachment_id, file_name, file_path FROM " . $table_prefix . "support_attachments ";
					$sql .= " WHERE support_id=" . $db->tosql($support_id, INTEGER);
					$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
					$sql .= " AND message_id=0 ";
					$sql .= " AND attachment_status=1 ";
					$db->query($sql);
					while ($db->next_record()) {
						$filename = $db->f("file_name");
						$filepath = $db->f("file_path");
						$attachments[] = array($filename, $filepath);
					}
				}

				// check if outgoing_email could be found 
				$outgoing_email = get_outgoing_email($ticket_dep_id, $ticket_type_id, $ticket_product_id);

				// check global admin and user notification 
				$admin_notification = get_setting_value($support_settings, "new_admin_notification", 0);
				$user_notification = get_setting_value($support_settings, "new_user_notification", 0);

				// check department notification settings
				$new_admin_mail = json_decode($deps_data[$ticket_dep_id]["new_admin_mail"], true);
				$new_user_mail = json_decode($deps_data[$ticket_dep_id]["new_user_mail"], true);

				$admin_dep_notification = get_setting_value($new_admin_mail, "new_admin_notification", 0);
				$admin_hp_disable = get_setting_value($new_admin_mail, "new_admin_hp_disable", 0);
				if ($admin_hp_disable) { $admin_notification = 0; }
				$user_dep_notification = get_setting_value($new_user_mail, "new_user_notification", 0);
				$user_hp_disable = get_setting_value($new_user_mail, "new_user_hp_disable", 0);
				if ($user_hp_disable) { $user_notification = 0; }

				// prepare tags for email
				$mail_tags = array();
				$date_added_string = va_date($datetime_show_format, $date_added);

				$support_department = get_array_value($r->get_value("dep_id"), $support_deps_values);
				$support_product = get_array_value($r->get_value("support_product_id"), $support_products);
				$support_type = get_array_value($r->get_value("support_type_id"), $support_types_values);
				$user_name = $r->get_value("user_name");
				$user_email = $r->get_value("user_email");
				$summary = $r->get_value("summary");
				$description = $r->get_value("description");

				$mail_tags = array(
					"site_name" => $site_name,
					"user_id" => $user_id,

					"ticket_added" => $date_added_string,
					"request_added" => $date_added_string,
					"message_added" => $date_added_string,
					"date_added" => $date_added_string,
					"date_modified" => $date_added_string,
					"vc" => $vc,
					"ticket_id" => $support_id,
					"support_id" => $support_id,
					"support_url" => $ticket_url,
					"ticket_url" => $ticket_url,
					"admin_support_url" => $admin_ticket_url,
					"admin_ticket_url" => $admin_ticket_url,

					"department" => $support_department,
					"dep_name" => $support_department,
					"product" => $support_product,
					"product_name" => $support_product,
					"type" => $support_type,
					"type_name" => $support_type,
					"status" => $status_name,
					"status_name" => $status_name,
					"status_caption" => $status_caption,
					"priority" => "Normal",

					"summary" => $summary,
					"description" => $description,
					"message_text" => $description,
					"user_name" => $user_name,
					"user_email" => $user_email,
					"remote_address" => $r->get_value("remote_address"),
					"identifier" => $r->get_value("identifier"),
					"environment" => $r->get_value("environment"),
				);


				if ($admin_notification || $user_notification || $admin_dep_notification || $user_dep_notification) {
					// start custom fields check
					$support_properties = array();
					$sql  = " SELECT sp.property_id, scp.property_name, sp.property_value,  scp.control_type";
					$sql .= " FROM (" . $table_prefix . "support_properties sp ";
					$sql .= " INNER JOIN " . $table_prefix . "support_custom_properties scp ON sp.property_id=scp.property_id)";
					$sql .= " WHERE sp.support_id=" . $db->tosql($support_id, INTEGER);
					$sql .= " ORDER BY sp.property_id ";
					$db->query($sql);
					if ($db->next_record()){
						$dbd = new VA_SQL();
						$dbd->DBType = $db->DBType;
						$dbd->DBDatabase = $db->DBDatabase;
						$dbd->DBHost = $db->DBHost;
						$dbd->DBPort = $db->DBPort;
						$dbd->DBUser = $db->DBUser;
						$dbd->DBPassword = $db->DBPassword;
						$dbd->DBPersistent = $db->DBPersistent;
						do {
							$property_id   = $db->f("property_id");
							$property_name = $db->f("property_name");
							$property_value = $db->f("property_value");
							$property_price = $db->f("property_price");
							$control_type = $db->f("control_type");
							// check value description
							if (($control_type == "CHECKBOXLIST" ||  $control_type == "RADIOBUTTON" || $control_type == "LISTBOX") && is_numeric($property_value)) {
								$sql  = " SELECT property_value FROM " . $table_prefix . "support_custom_values ";
								$sql .= " WHERE property_value_id=" . $db->tosql($property_value, INTEGER);
								$dbd->query($sql);
								if ($dbd->next_record()) {
									$property_value = get_translation($dbd->f("property_value"));
								}
							}
							if (isset($support_properties[$property_id])) {
								$support_properties[$property_id]["value"] .= "; " . $property_value;
							} else {
								$support_properties[$property_id] = array(
									"name" => $property_name, "value" => $property_value,
								);
							}
						} while ($db->next_record());
					}
					
					if (count($pp) > 0) {
						foreach ($support_properties as $property_id => $property_values) {
							$property_name = $property_values["name"];
							$property_value = $property_values["value"];
							$mail_tags["field_name_".$property_id] = $property_name;
							$mail_tags["field_value_".$property_id] = $property_value;
							$mail_tags["field_".$property_id] = $property_value;
						}
					}
					// end custom fields check
				}

				// send global email notification to admin
				if ($admin_notification) {
					$mail_to = get_setting_value($support_settings, "new_admin_to", $settings["admin_email"]);
					$admin_subject = get_setting_value($support_settings, "new_admin_subject", $summary);
					$admin_message = get_setting_value($support_settings, "new_admin_message", $description);
		    
					$email_headers = array();
					if ($outgoing_email) {
						$email_headers["from"] = $outgoing_email;	
					} else {
						$email_headers["from"] = get_setting_value($support_settings, "new_admin_from", $settings["admin_email"]);	
					}
					$email_headers["cc"] = get_setting_value($support_settings, "new_admin_cc");
					$email_headers["bcc"] = get_setting_value($support_settings, "new_admin_bcc");
					$email_headers["reply_to"] = get_setting_value($support_settings, "new_admin_reply_to");
					$email_headers["return_path"] = get_setting_value($support_settings, "new_admin_return_path");
					$email_headers["mail_type"] = get_setting_value($support_settings, "new_admin_message_type");
		    
					va_mail($mail_to, $admin_subject, $admin_message, $email_headers, $attachments, $mail_tags);
				} // end global admin notification

				// send department email notification to admin
				if ($admin_dep_notification) {
					$mail_to = get_setting_value($new_admin_mail, "new_admin_to", $settings["admin_email"]);
					$admin_subject = get_setting_value($new_admin_mail, "new_admin_subject", $summary);
					$admin_message = get_setting_value($new_admin_mail, "new_admin_message", $description);
		    
					$email_headers = array();
					if ($outgoing_email) {
						$email_headers["from"] = $outgoing_email;	
					} else {
						$email_headers["from"] = get_setting_value($new_admin_mail, "new_admin_from", $settings["admin_email"]);	
					}
					$email_headers["cc"] = get_setting_value($new_admin_mail, "new_admin_cc");
					$email_headers["bcc"] = get_setting_value($new_admin_mail, "new_admin_bcc");
					$email_headers["reply_to"] = get_setting_value($new_admin_mail, "new_admin_reply_to");
					$email_headers["return_path"] = get_setting_value($new_admin_mail, "new_admin_return_path");
					$email_headers["mail_type"] = get_setting_value($new_admin_mail, "new_admin_message_type");
		    
					va_mail($mail_to, $admin_subject, $admin_message, $email_headers, $attachments, $mail_tags);
				} // end department admin notification

				// send global email notification to user if it's active and user email available
				if ($user_notification && $user_email) {
					$user_subject = get_setting_value($support_settings, "new_user_subject", $summary);
					$user_message = get_setting_value($support_settings, "new_user_message", $description);
		    
					$email_headers = array();
					if ($outgoing_email) {
						$email_headers["from"] = $outgoing_email;	
					} else {
						$email_headers["from"] = get_setting_value($support_settings, "new_user_from", $settings["admin_email"]);	
					}
					$email_headers["cc"] = get_setting_value($support_settings, "new_user_cc");
					$email_headers["bcc"] = get_setting_value($support_settings, "new_user_bcc");
					$email_headers["reply_to"] = get_setting_value($support_settings, "new_user_reply_to");
					$email_headers["return_path"] = get_setting_value($support_settings, "new_user_return_path");
					$email_headers["mail_type"] = get_setting_value($support_settings, "new_user_message_type");
		    
					va_mail($user_email, $user_subject, $user_message, $email_headers, $attachments, $mail_tags);
				} // end global user notification


				// send department email notification to user if it's active and user email is available
				if ($user_dep_notification && $user_email) {
					$user_subject = get_setting_value($new_user_mail, "new_user_subject", $summary);
					$user_message = get_setting_value($new_user_mail, "new_user_message", $description);
		    
					$email_headers = array();
					if ($outgoing_email) {
						$email_headers["from"] = $outgoing_email;	
					} else {
						$email_headers["from"] = get_setting_value($new_user_mail, "new_user_from", $settings["admin_email"]);	
					}
					$email_headers["cc"] = get_setting_value($new_user_mail, "new_user_cc");
					$email_headers["bcc"] = get_setting_value($new_user_mail, "new_user_bcc");
					$email_headers["reply_to"] = get_setting_value($new_user_mail, "new_user_reply_to");
					$email_headers["return_path"] = get_setting_value($new_user_mail, "new_user_return_path");
					$email_headers["mail_type"] = get_setting_value($new_user_mail, "new_user_message_type");
		    
					va_mail($user_email, $user_subject, $user_message, $email_headers, $attachments, $mail_tags);
				} // end department user notification

				// clear form and set default values
				$r->empty_values();
				if (strlen(get_session("session_user_id"))) {
					$r->set_value("user_name", get_session("session_user_name"));
					$r->set_value("user_email", get_session("session_user_email"));
				}
				$r->set_default_values();
				
			} else {
				$errors = true;
				if (!strlen($r->errors)) {
					$r->errors = va_message("DATABASE_ERROR_MSG");
				}
				set_session("session_rnd", "");
			}
		}

	} else {
		if (strlen(get_session("session_user_id"))) {
			$r->set_value("user_name", get_session("session_user_name"));
			$r->set_value("user_email", get_session("session_user_email"));
		}
		$r->set_default_values();
		// check if summary was passed as url parameter
		$support_type_id_param = get_param("support_type_id");
		if (strlen($support_type_id_param)) {
			$r->set_value("support_type_id", $support_type_id_param);
		}
		$r->set_value("identifier", get_param("identifier"));
		$r->set_value("environment", get_param("environment"));
		$r->set_value("summary", get_param("summary"));
	}

	$t->set_var("validation_class", $validation_class);

	foreach ($pp as $property_id => $pp_row) {
		$param_name = "pp_" . $pp_row["property_id"];
		if ($r->parameter_exists($param_name)) {
			$r->change_property($param_name, SHOW, false);
		}
	}

	if (blacklist_check("support") == "blocked") {
		$r->errors = va_message("BLACK_IP_MSG")."<br>";	
	}
	$t->set_var("custom_properties", "");
	$r->set_parameters();

	$intro_text = get_translation(get_setting_value($support_settings, "intro_text", ""));
	$intro_text = get_currency_message($intro_text, $currency);
	if ($intro_text) {
		$t->set_var("intro_text", $intro_text);
		$t->parse("intro_block", false);
	}
	// get department and support type id to show/hide properties and dep intro text
	$dep_id = $r->get_value("dep_id");
	if (strlen($dep_id) && $deps_data[$dep_id]["intro_text"]) {
		$dep_intro = $deps_data[$dep_id]["intro_text"];
		$t->set_var("dep_intro", $dep_intro);
	} else {
		$t->set_var("dep_intro_class", "hide-block");
	}
	$support_type_id = $r->get_value("support_type_id");
	if (strlen($support_type_id) && $data_types[$support_type_id]["intro_text"]) {
		$type_intro = $data_types[$support_type_id]["intro_text"];
		$t->set_var("type_intro", $type_intro);
	} else {
		$t->set_var("type_intro_class", "hide-block");
	}

	$block_parsed = true;

function update_support_properties($pp,$r,$support_id)
{
	global $db, $table_prefix;

	foreach ($pp as $property_id => $data) {
		$property_id =$data["property_id"];
		$param_name = "pp_" . $property_id;
		$values = array();
		if ($r->get_property_value($param_name, CONTROL_TYPE) == CHECKBOXLIST) {
			$values = $r->get_value($param_name);
		} else {
			$values[] = $r->get_value($param_name);
		}
		if (is_array($values)) {
			for ($i = 0; $i < sizeof($values); $i++) {
				$property_value = $values[$i];
				if (strlen($property_value)) {
					$sql  = " INSERT INTO " . $table_prefix . "support_properties ";
					$sql .= " (support_id, property_id, property_value) VALUES (";
					$sql .= $db->tosql($support_id, INTEGER) . ", ";
					$sql .= $db->tosql($property_id, INTEGER) . ", ";
					$sql .= $db->tosql($property_value, TEXT) . ") ";
					$db->query($sql);
				}
			}
		}
	}
}


function support_param_show($params){
	global $r, $t;
	$control_name = $params[CONTROL_NAME];
	if ($control_name == "dep_id" || $control_name == "support_type_id") {
		$values_list = $params[VALUES_LIST];
		$event = isset($params["event"]) ? $params["event"] : "";
		if (count($values_list) == 2) {
			if ($event == BEFORE_SHOW) {
				$r->default_class = "fd-value";
				$t->set_var($control_name."_selected_desc", $values_list[1][1]);
			} else if ($event == AFTER_SHOW) {
				$r->default_class = "";
			}
		}
	}
}

function show_ticket_attachments()
{
	global $t, $db, $user_id, $table_prefix;

 	// check attachments
	$attachments_files = "";
	if ($user_id) 
	{
		$sql  = " SELECT attachment_id, file_name, file_path, date_added ";
		$sql .= " FROM " . $table_prefix . "support_attachments ";
		$sql .= " WHERE support_id=0 ";
		$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
		$sql .= " AND message_id=0 ";
		$sql .= " AND attachment_status=0 ";
		$db->query($sql);
		while ($db->next_record()) {
			$attachment_id = $db->f("attachment_id");
			$filename = $db->f("file_name");
			$filepath = $db->f("file_path");
			$date_added = $db->f("date_added", DATETIME);
			$attachment_vc = md5($attachment_id . $date_added[3].$date_added[4].$date_added[5]);
			$filesize = filesize($filepath);
			if ($attachments_files) { $attachments_files .= "; "; }
			$attachments_files .= "<a href=\"support_attachment.php?atid=" . $attachment_id . "&vc=" . $attachment_vc . "\" target=\"_blank\">" . $filename . "</a> (" . get_nice_bytes($filesize) . ")";
		}
	}
	if ($attachments_files) {
		$t->set_var("attached_files", $attachments_files);
		$t->set_var("attached_files_style", "display: block;");
	} else {
		$t->set_var("attached_files_style", "display: none;");
	}
}

function validate_ticket_attachments($params)
{
	global $r, $db, $table_prefix;
	$user_id = get_session("session_user_id");
	if ($params[REQUIRED]) {
		// check if user has uploaded any files
		$sql  = " SELECT COUNT(*) ";
		$sql .= " FROM " . $table_prefix . "support_attachments ";
		$sql .= " WHERE support_id=0 ";
		$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
		$sql .= " AND message_id=0 ";
		$sql .= " AND attachment_status=0 ";
		$attached_files = get_db_value($sql);
		if (!$attached_files || !$user_id) {
			$error_message = str_replace("{field_name}", $params[CONTROL_DESC], va_message("REQUIRED_MESSAGE"));
			$r->change_property("attachments", IS_VALID, false);
			$r->change_property("attachments", ERROR_DESC, $error_message);
		}
	}
}


function show_ticket_custom_field($params)
{
	global $r, $t, $db, $table_prefix, $pp;
	// some general variables
	$eol = get_eol();
	$operation = get_param("operation");
	// get information about selected help desk department and type	
	$dep_id = $r->get_value("dep_id");
	$support_type_id = $r->get_value("support_type_id");
	// get custom field data
	$property_id = $params["property_id"];
	$data = $pp[$property_id];

	$is_fields_block = $t->block_exists($r->fields_block) || $t->block_exists($r->fields_block, $r->record_block);

	// show custom field 
			$property_id = $data["property_id"];
			$param_name = "pp_" . $property_id;
			$property_order  = $data["property_order"];
			$property_name_initial = $data["property_name"];
			$property_name = get_translation($property_name_initial);
			$property_description = $data["property_description"];
			$default_value = $data["default_value"];
			$property_style = $data["property_style"];
			$control_type = $data["control_type"];
			$control_style = $data["control_style"];
			$property_required = $data["required"];
			$before_name_html = $data["before_name_html"];
			$after_name_html = $data["after_name_html"];
			$before_control_html = $data["before_control_html"];
			$after_control_html = $data["after_control_html"];
			$onchange_code = $data["onchange_code"];
			$onclick_code = $data["onclick_code"];
			$control_code = $data["control_code"];
			$validation_regexp = $data["validation_regexp"];
			$regexp_error = $data["regexp_error"];
			$options_values_sql = $data["options_values_sql"];
			$property_types = $data["types"];
			$types_all = $data["types_all"];
			$property_deps = $data["deps"];
			$deps_all = $data["deps_all"];

			if (isset($data["property_class"])){
				$property_class = $data["property_class"];
			} else {
				$property_class = "normal";
			}
			if ($property_required) { 
				$property_class .= " required";
			}

			// check if option could be shown
			if (
				(!$types_all && (!strlen($support_type_id) || !isset($property_types[$support_type_id]))) ||
				(!$deps_all && (!strlen($dep_id) || !isset($property_deps[$dep_id])))
			) {
				$property_class .= " hide-block";
			}

			$property_control  = "";
			$property_control .= "<input type=\"hidden\" name=\"pp_name_" . $property_id . "\"";
			$property_control .= " value=\"" . strip_tags($property_name) . "\">";
			$property_control .= "<input type=\"hidden\" name=\"pp_required_" . $property_id . "\"";
			$property_control .= " value=\"" . intval($property_required) . "\">";
			$property_control .= "<input type=\"hidden\" name=\"pp_control_" . $property_id . "\"";
			$property_control .= " value=\"" . strtoupper($control_type) . "\">";

			if ($options_values_sql) {
				$sql = $options_values_sql;
			} else {
				$sql  = " SELECT * FROM " . $table_prefix . "support_custom_values ";
				$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER) . " AND hide_value=0";
				$sql .= " ORDER BY property_value_id ";
			}
			if (strtoupper($control_type) == "LISTBOX") 
			{
				$selected_value = $r->get_value($param_name);
				$properties_values = "<option value=\"\"></option>" . $eol;
				$db->query($sql);
				while ($db->next_record())
				{
					if ($options_values_sql) {
						$property_value_id = $db->f(0);
						$property_value = get_translation($db->f(1));
					} else {
						$property_value_id = $db->f("property_value_id");
						$property_value = get_translation($db->f("property_value"));
					}
					$is_default_value = $db->f("is_default_value");
					$property_selected  = "";
					if (strlen($operation)) {
						if ($selected_value == $property_value_id) {
							$property_selected  = "selected ";
						}
					} elseif ($is_default_value) {
						$property_selected  = "selected ";
					}

					$properties_values .= "<option " . $property_selected . "value=\"" . htmlspecialchars($property_value_id) . "\">";
					$properties_values .= htmlspecialchars($property_value);
					$properties_values .= "</option>" . $eol;
				}
				$property_control .= $before_control_html;
				$property_control .= "<select name=\"pp_" . $property_id . "\" ";
				if ($onchange_code) { $property_control .= " onchange=\"" . $onchange_code. "\""; }
				if ($onclick_code) { $property_control .= " onclick=\"" . $onclick_code . "\""; }
				if ($control_code) { $property_control .= " " . $control_code . " "; }
				if ($control_style) { $property_control .= " style=\"" . $control_style . "\""; }
				$property_control .= ">" . $properties_values . "</select>";
				$property_control .= $after_control_html;
			} 
			elseif (strtoupper($control_type) == "RADIOBUTTON" || strtoupper($control_type) == "CHECKBOXLIST") 
			{
				$is_radio = (strtoupper($control_type) == "RADIOBUTTON");
				$selected_value = array();
				if ($is_radio) {
					$selected_value[] = $r->get_value($param_name);
				} else {
					$selected_value = $r->get_value($param_name);
				}

				$input_type = $is_radio ? "radio" : "checkbox";
				$property_control .= "<span";
				if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
				$property_control .= ">";
				$value_number = 0;
				$db->query($sql);
				while ($db->next_record())
				{
					$value_number++;
					if ($options_values_sql) {
						$property_value_id = $db->f(0);
						$property_value = get_translation($db->f(1));
					} else {
						$property_value_id = $db->f("property_value_id");
						$property_value = get_translation($db->f("property_value"));
					}
					$is_default_value = $db->f("is_default_value");
					$property_checked = "";
					$property_control .= $before_control_html;
					if (strlen($operation)) {
						if (is_array($selected_value) && in_array($property_value_id, $selected_value)) {
							$property_checked = "checked ";
						}
					} elseif ($is_default_value) {
						$property_checked = "checked ";
					}

					$control_name = ($is_radio) ? ("pp_".$property_id) : ("pp_".$property_id."_".$value_number);
					$property_control .= "<input type=\"" . $input_type . "\" name=\"" . $control_name . "\" ". $property_checked;
					$property_control .= "value=\"" . htmlspecialchars($property_value_id) . "\" ";
					if ($onclick_code) {
						$control_onclick_code = str_replace("{option_value}", $property_value, $onclick_code);
						$property_control .= " onclick=\"" . $control_onclick_code. "\"";
					}
					if ($onchange_code) {	$property_control .= " onchange=\"" . $onchange_code . "\""; }
					if ($control_code) {	$property_control .= " " . $control_code . " "; }
					$property_control .= ">";
					$property_control .= $property_value;
					$property_control .= $after_control_html;
				}
				$property_control .= "</span>";
				if (!$is_radio) {
					$property_control .= "<input type=\"hidden\" name=\"pp_".$property_id."\" value=\"".$value_number."\">";
				}
			} 
			elseif (strtoupper($control_type) == "TEXTBOX") 
			{
				if (strlen($operation)) {
					$control_value = $r->get_value($param_name);
				} else {
					$control_value = $default_value;
				}
				$property_control .= $before_control_html;
				$property_control .= "<input type=\"text\" name=\"pp_" . $property_id . "\"";
				if ($control_style) { $property_control .= " style=\"" . $control_style . "\""; }
				if ($onclick_code) { $property_control .= " onclick=\"" . $onclick_code . "\""; }
				if ($onchange_code) { $property_control .= " onchange=\"" . $onchange_code . "\""; }
				if ($control_code) { $property_control .= " " . $control_code . " "; }
				$property_control .= " value=\"". htmlspecialchars($control_value) . "\">";
				$property_control .= $after_control_html;
			} 
			elseif (strtoupper($control_type) == "TEXTAREA") 
			{
				if (strlen($operation)) {
					$control_value = $r->get_value($param_name);
				} else {
					$control_value = $default_value;
				}
				$property_control .= $before_control_html;
				$property_control .= "<textarea name=\"pp_" . $property_id . "\"";
				if ($control_style) { $property_control .= " style=\"" . $control_style . "\""; }
				if ($onclick_code) { $property_control .= " onclick=\"" . $onclick_code . "\""; }
				if ($onchange_code) { $property_control .= " onchange=\"" . $onchange_code . "\""; }
				if ($control_code) { $property_control .= " " . $control_code . " "; }
				$property_control .= ">". htmlspecialchars($control_value) ."</textarea>";
				$property_control .= $after_control_html;
			} 
			else 
			{
				$property_control .= $before_control_html;
				if ($property_required) {
					$property_control .= "<input type=\"hidden\" name=\"pp_" . $property_id . "\" value=\"" . htmlspecialchars($property_description) . "\">";
				}
				$property_control .= "<span";
				if ($control_style) { $property_control .= " style=\"" . $control_style . "\""; }
				if ($onclick_code) { $property_control .= " onclick=\"" . $onclick_code . "\""; }
				if ($onchange_code) { $property_control .= " onchange=\"" . $onchange_code . "\""; }
				if ($control_code) { $property_control .= " " . $control_code . " "; }
				$property_control .= ">" . get_translation($default_value) . "</span>";
				$property_control .= $after_control_html;
			}

			if ($deps_all) {
				$property_deps = array("all" => "all");
			}
			if ($types_all) {
				$property_types = array("all" => "all");
			}

			$t->set_var("property_id", $property_id);
			$t->set_var("property_order", $property_order);

			$t->set_var("property_name", $before_name_html . $property_name . $after_name_html);
			$t->set_var("property_style", $property_style);
			$t->set_var("property_class", $property_class);
			$t->set_var("property_control", $property_control);
			$t->set_var("property_deps", htmlspecialchars(json_encode($property_deps)));
			$t->set_var("property_types", htmlspecialchars(json_encode($property_types)));

	if ($is_fields_block) {
		$t->parse_to("custom_properties", $r->fields_block);
	} else {
		$t->parse("custom_properties", true);
	}
	// end show custom field
}

function show_ticket_validation_number($params)
{
	global $r;
	$event = isset($params["event"]) ? $params["event"] : "";
	if ($event == BEFORE_SHOW) {
		$r->is_fields_block = false;
	} else if ($event == AFTER_SHOW) {
		$r->is_fields_block = true;
	}
}

