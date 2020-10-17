<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_order_property.php                                 ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/editgrid.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");

	$operation = get_param("operation");
	$property_id = get_param("property_id");
	$payment_id    = get_param("payment_id");
	$shipping_type_id = get_param("shipping_type_id");
	$shipping_module_id = get_param("shipping_module_id");
	if ($property_id) {
		$sql  = " SELECT * FROM ".$table_prefix."order_custom_properties ";
		$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$payment_id    = $db->f("payment_id");
			$shipping_type_id = $db->f("shipping_type_id");
			$shipping_module_id = $db->f("shipping_module_id");
		}
	}

	// start building breadcrumb
	$settings_page = "admin_menu.php?code=settings";
	$custom_breadcrumb[$settings_page] = SETTINGS_MSG;
	$custom_breadcrumb["admin_order_info.php"] = ORDERS_MSG;

	if ($payment_id) {
		check_admin_security("payment_systems");

		$sql  = " SELECT payment_name FROM ".$table_prefix."payment_systems ";
		$sql .= " WHERE payment_id=".$db->tosql($payment_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$custom_module_name = strip_tags($db->f("payment_name"));

			$custom_breadcrumb["admin_payment_systems.php"] = PAYMENT_SYSTEMS_MSG;
			$custom_breadcrumb["admin_order_properties.php?payment_id=".$payment_id] = $custom_module_name." :: ".CUSTOM_FIELDS_MSG;

			$rp = "admin_credit_card_info.php?payment_id=".$payment_id;
		} else {
			header("Location: ".$settings_page);
			exit;
		}
	} else if ($shipping_type_id) {
		check_admin_security("shipping_methods");
		$sql  = " SELECT sm.shipping_module_id, sm.shipping_module_name, st.shipping_type_desc ";
		$sql .= " FROM (".$table_prefix."shipping_types st ";
		$sql .= " INNER JOIN ".$table_prefix."shipping_modules sm ON st.shipping_module_id=sm.shipping_module_id) ";
		$sql .= " WHERE st.shipping_type_id=".$db->tosql($shipping_type_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$param_module_id = $db->f("shipping_module_id");
			$shipping_module_name = strip_tags($db->f("shipping_module_name"));
			$custom_module_name = strip_tags($db->f("shipping_type_desc"));

			$custom_breadcrumb["admin_shipping_modules.php"] = SHIPPING_MODULES_MSG;
			$custom_breadcrumb["admin_shipping_module.php?shipping_module_id=".$param_module_id] = $shipping_module_name." :: ".SHIPPING_METHODS_MSG;
			$custom_breadcrumb["admin_order_properties.php?shipping_type_id=".$shipping_type_id] = $custom_module_name." :: ".CUSTOM_FIELDS_MSG;

			$rp = "admin_order_properties.php?shipping_type_id=".$shipping_type_id;
		} else {
			header("Location: ".$settings_page);
			exit;
		}
	} else if ($shipping_module_id) {
		check_admin_security("shipping_methods");
		$sql  = " SELECT sm.shipping_module_name ";
		$sql .= " FROM ".$table_prefix."shipping_modules sm  ";
		$sql .= " WHERE shipping_module_id=".$db->tosql($shipping_module_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$custom_module_name = strip_tags($db->f("shipping_module_name"));

			$custom_breadcrumb["admin_shipping_modules.php"] = SHIPPING_MODULES_MSG;
			$custom_breadcrumb["admin_order_properties.php?shipping_module_id=".$shipping_module_id] = $custom_module_name." :: ".CUSTOM_FIELDS_MSG;

			$rp = "admin_order_properties.php?shipping_module_id=".$shipping_module_id;
		} else {
			header("Location: ".$settings_page);
			exit;
		}
	} else {
		check_admin_security("order_profile");
		$custom_module_name = ORDER_PROFILE_PAGE_MSG;
		$custom_breadcrumb["admin_order_properties.php"] = $custom_module_name." :: ".CUSTOM_FIELDS_MSG;

		$rp = "admin_order_info.php?tab=custom_fields";
	}

	$custom_breadcrumb["admin_order_property.php?property_id=".urlencode($property_id)] = EDIT_MSG;

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_order_property.html");
	$t->set_var("admin_order_info_href", "admin_order_info.php");
	$t->set_var("admin_order_property_href", "admin_order_property.php");
	$t->set_var("admin_payment_systems_href", "admin_payment_systems.php");
	$t->set_var("admin_payment_system_href", "admin_payment_system.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", CUSTOM_FIELDS_MSG, CONFIRM_DELETE_MSG));	

	$controls = 
		array(			
			array("", ""),  
			array("CHECKBOXLIST", CHECKBOXLIST_MSG),
			array("LABEL",        LABEL_MSG),
			array("LISTBOX",      LISTBOX_MSG),
			array("RADIOBUTTON",  RADIOBUTTON_MSG),
			array("TEXTAREA",     TEXTAREA_MSG),
			array("TEXTBOX",      TEXTBOX_MSG),
			array("HIDDEN",       HIDDEN_MSG),
		);

	$property_type = ""; $property_types = array();
	if ($payment_id > 0) {
		$property_type = 4;
	} else if ($shipping_type_id > 0) {
		$property_type = 6;
	} else if ($shipping_module_id > 0) {
		$property_type = 5;
	} else {
		$property_types = 
			array(			
				array("", ""),
				array("1", ADMIN_CART_MSG),
				array("2", PERSONAL_DETAILS_MSG),
				array("3", DELIVERY_DETAILS_MSG)
			);
	}

	$property_show =
		array(
			array(-1, DONT_SHOW_MSG),
			array(0, FOR_ALL_ORDERS_MSG),
			array(1, ONLY_WEB_ORDERS_MSG),
			array(2, ONLY_FOR_CALL_CENTRE_MSG)
		);

	// set up html form parameters
	$r = new VA_Record($table_prefix . "order_custom_properties");
	$r->add_where("property_id", INTEGER);
	$r->add_textbox("payment_id", INTEGER);
	$r->add_textbox("shipping_type_id", INTEGER);
	$r->add_textbox("shipping_module_id", INTEGER);

	$r->add_textbox("property_order", INTEGER, OPTION_ORDER_MSG);
	$r->change_property("property_order", REQUIRED, true);
	$r->add_textbox("property_code", TEXT, FIELD_CODE_MSG);
	$r->change_property("property_code", MAX_LENGTH, 64);
	$r->add_textbox("property_name", TEXT, OPTION_NAME_MSG);
	$r->change_property("property_name", REQUIRED, true);
	$r->add_textbox("property_description", TEXT, OPTION_TEXT_MSG);
	$r->add_textbox("default_value", TEXT, DEFAULT_VALUE_MSG);
	$r->add_textbox("property_class", TEXT);
	$r->add_textbox("property_style", TEXT);
	$r->add_textbox("control_style", TEXT);
	$r->add_select("property_type", INTEGER, $property_types, OPTION_TYPE_MSG);
	if (is_array($property_types) && count($property_types) > 0) {
		$r->change_property("property_type", REQUIRED, true);
	} else {
		$r->change_property("property_type", SHOW, false);
	}
	$r->add_select("control_type", TEXT, $controls, OPTION_CONTROL_MSG);
	$r->change_property("control_type", REQUIRED, true);
	$r->add_radio("property_show", INTEGER, $property_show);
	$r->add_checkbox("required", INTEGER);
	$r->add_checkbox("tax_free", INTEGER);
	if ($payment_id > 0) {
		$r->change_property("tax_free", SHOW, false);
	}

	$r->add_textbox("before_name_html", TEXT);
	$r->add_textbox("after_name_html", TEXT);
	$r->add_textbox("before_control_html", TEXT);
	$r->add_textbox("after_control_html", TEXT);
	$r->add_textbox("control_code", TEXT);
	$r->add_textbox("onchange_code", TEXT);
	$r->add_textbox("onclick_code", TEXT);

	$r->add_textbox("validation_regexp", TEXT);
	$r->add_textbox("regexp_error", TEXT);
	$r->add_textbox("options_values_sql", TEXT);

	// sites list
	$r->add_checkbox("sites_all", INTEGER);
	$r->change_property("sites_all", DEFAULT_VALUE, 1);
	if ($sitelist) {
		$selected_sites = array();
		if (strlen($operation)) {
			$sites = get_param("sites");
			if ($sites) {
				$selected_sites = explode(",", $sites);
			}
		} elseif ($property_id) {
			$sql  = "SELECT site_id FROM " . $table_prefix . "order_custom_sites ";
			$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$selected_sites[] = $db->f("site_id");
			}
		}
	}

	$r->get_form_values();
	if (count($property_types) == 0) {
		$r->set_value("property_type", $property_type);
	}
	if (!$sitelist) {
		$r->set_value("sites_all", 1);
	}
	$r->set_value("payment_id", 0);
	$r->set_value("shipping_type_id", 0);
	$r->set_value("shipping_module_id", 0);
	if ($payment_id > 0) {
		$r->set_value("payment_id", $payment_id);
	} else if ($shipping_type_id > 0) {
		$r->set_value("shipping_type_id", $shipping_type_id);
	} else if ($shipping_module_id > 0) {
		$r->set_value("shipping_module_id", $shipping_module_id);
	}
	

	$ipv = new VA_Record($table_prefix . "order_custom_values", "properties");
	$ipv->add_where("property_value_id", INTEGER);
	$ipv->add_hidden("property_id", INTEGER);
	$ipv->change_property("property_id", USE_IN_INSERT, true);
	$ipv->add_textbox("property_value", TEXT, OPTION_VALUE_MSG);
	$ipv->change_property("property_value", REQUIRED, true);
	$ipv->add_textbox("property_price", NUMBER, OPTION_PRICE_MSG);
	if ($payment_id > 0 || $shipping_type_id > 0 || $shipping_module_id > 0 ) {
		$ipv->change_property("property_price", SHOW, false);
	}
	$ipv->add_textbox("property_weight", NUMBER, OPTION_WEIGHT_MSG);
	$ipv->add_checkbox("hide_value", INTEGER);
	$ipv->add_checkbox("is_default_value", INTEGER);
	
	$property_id = get_param("property_id");

	$more_properties = get_param("more_properties");
	$number_properties = get_param("number_properties");

	$eg = new VA_EditGrid($ipv, "properties");
	$eg->get_form_values($number_properties);

	$operation = get_param("operation");

	if (strlen($operation) && !$more_properties)
	{
		if ($operation == "cancel")
		{
			header("Location: " . $rp);
			exit;
		}
		elseif ($operation == "delete" && $property_id)
		{
			$db->query("DELETE FROM " . $table_prefix . "order_custom_properties WHERE property_id=" . $db->tosql($property_id, INTEGER));		
			$db->query("DELETE FROM " . $table_prefix . "order_custom_values WHERE property_id=" . $db->tosql($property_id, INTEGER));		
			$db->query("DELETE FROM " . $table_prefix . "order_custom_sites WHERE property_id=" . $db->tosql($property_id, INTEGER));		
			header("Location: " . $rp);
			exit;
		}

		$is_valid = $r->validate();
		$is_valid = ($eg->validate() && $is_valid); 

		if ($is_valid)
		{
			if (strlen($property_id))
			{
				$r->update_record();
				$eg->set_values("property_id", $property_id);
				$eg->update_all($number_properties);
			}
			else
			{
				$r->insert_record();
				$property_id = $db->last_insert_id();
				$eg->set_values("property_id", $property_id);
				$eg->insert_all($number_properties);
			}
			// update sites
			if ($sitelist) {
				$db->query("DELETE FROM " . $table_prefix . "order_custom_sites WHERE property_id=" . $db->tosql($property_id, INTEGER));
				for ($st = 0; $st < sizeof($selected_sites); $st++) {
					$site_id = $selected_sites[$st];
					if (strlen($site_id)) {
						$sql  = " INSERT INTO " . $table_prefix . "order_custom_sites (property_id, site_id) VALUES (";
						$sql .= $db->tosql($property_id, INTEGER) . ", ";
						$sql .= $db->tosql($site_id, INTEGER) . ") ";
						$db->query($sql);
					}
				}
			}

			header("Location: " . $rp);
			exit;
		}
	} elseif (strlen($property_id) && !$more_properties) {
		$r->get_db_values();
		$eg->set_value("property_id", $property_id);
		$eg->change_property("property_value_id", USE_IN_SELECT, true);
		$eg->change_property("property_value_id", USE_IN_WHERE, false);
		$eg->change_property("property_id", USE_IN_WHERE, true);
		$eg->change_property("property_id", USE_IN_SELECT, true);
		$number_properties = $eg->get_db_values();
		if ($number_properties == 0)
			$number_properties = 5;
	} elseif ($more_properties) {
		$number_properties += 5;
	} else { // set default values
		$r->set_value("property_show", 0);
		$r->set_value("sites_all", 1);

		$sql  = " SELECT MAX(property_order) FROM " . $table_prefix . "order_custom_properties ";
		$property_order = get_db_value($sql);
		$property_order = ($property_order) ? ($property_order + 1) : 1;
		$r->set_value("property_order", $property_order);

		$number_properties = 5;
	}

/*
	if (strlen($errors))
	{
		$t->set_var("errors_list", $errors);
		$t->parse("errors", false);
	}
	else
	{
		$t->set_var("errors", "");
	}
*/

	$t->set_var("number_properties", $number_properties);

	$eg->set_parameters_all($number_properties);
	$r->set_parameters();

	if ($sitelist) {
		$sites = array();
		$sql = " SELECT site_id, site_name FROM " . $table_prefix . "sites ";
		$db->query($sql);
		while ($db->next_record())	{
			$list_site_id   = $db->f("site_id");
			$site_name = $db->f("site_name");
			$sites[$list_site_id] = $site_name;
			$t->set_var("site_id", $list_site_id);
			$t->set_var("site_name", $site_name);
			if (in_array($list_site_id, $selected_sites)) {
				$t->parse("selected_sites", true);
			} else {
				$t->parse("available_sites", true);
			}
		}
	}

	if (strlen($property_id))	{
		$t->set_var("save_button", UPDATE_BUTTON);
		$t->parse("delete", false);	
	}	else {
		$t->set_var("save_button", ADD_NEW_MSG);
		$t->set_var("delete", "");	
	}

	if ($payment_id > 0 || $shipping_type_id > 0 || $shipping_module_id > 0 ) {
		$t->set_var("price_title",  "");
	} else {
		$t->parse("price_title",  false);
	}

	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }
	$tabs = array(
		"general" => array("title" => ADMIN_GENERAL_MSG), 
		"appearance" => array("title" => APPEARANCE_MSG), 
		"javascript" => array("title" => JAVASCRIPT_SETTINGS_MSG), 
		"regexp" => array("title" => "Regular Expression"),
		"sites" => array("title" => ADMIN_SITES_MSG, "show" => $sitelist),
	);
	parse_admin_tabs($tabs, $tab, 6);

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

?>