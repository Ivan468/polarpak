<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_order_properties.php                               ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ($root_folder_path . "includes/record.php");
	include_once ("../messages/".$language_code."/cart_messages.php");

	include_once("./admin_common.php");

	check_admin_security("sales_orders");
	check_admin_security("order_profile");

	// start building breadcrumb
	$settings_page = "admin_menu.php?code=settings";
	$custom_breadcrumb[$settings_page] = SETTINGS_MSG;
	$custom_breadcrumb["admin_order_info.php"] = ORDERS_MSG;

	$operation = get_param("operation");
	$payment_id = get_param("payment_id");
	$property_id = get_param("property_id");
	$shipping_type_id = get_param("shipping_type_id");
	$shipping_module_id = get_param("shipping_module_id");

	if ($operation == "required-yes") {
		$sql = " UPDATE ".$table_prefix."order_custom_properties ";
		$sql.= " SET required=1 ";
		$sql.= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
		$db->query($sql);
	} else if ($operation == "required-no") {
		$sql = " UPDATE ".$table_prefix."order_custom_properties ";
		$sql.= " SET required=0 ";
		$sql.= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
		$db->query($sql);
	}

	if ($payment_id) {
		$sql  = " SELECT payment_name FROM ".$table_prefix."payment_systems ";
		$sql .= " WHERE payment_id=".$db->tosql($payment_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$custom_module_name = strip_tags($db->f("payment_name"));

			$custom_breadcrumb["admin_payment_systems.php"] = PAYMENT_SYSTEMS_MSG;
			$custom_breadcrumb["admin_order_properties.php?payment_id=".$payment_id] = $custom_module_name." :: ".CUSTOM_FIELDS_MSG;

		} else {
			header("Location: ".$settings_page);
			exit;
		}
	} else if ($shipping_type_id) {
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
			$custom_breadcrumb["admin_shipping_types.php?shipping_module_id=".$param_module_id] = $shipping_module_name." :: ".SHIPPING_METHODS_MSG;
			$custom_breadcrumb["admin_order_properties.php?shipping_type_id=".$shipping_type_id] = $custom_module_name." :: ".CUSTOM_FIELDS_MSG;
		} else {
			header("Location: ".$settings_page);
			exit;
		}
	} else if ($shipping_module_id) {
		$sql  = " SELECT sm.shipping_module_name ";
		$sql .= " FROM ".$table_prefix."shipping_modules sm  ";
		$sql .= " WHERE shipping_module_id=".$db->tosql($shipping_module_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$custom_module_name = strip_tags($db->f("shipping_module_name"));

			$custom_breadcrumb["admin_shipping_modules.php"] = SHIPPING_MODULES_MSG;
			$custom_breadcrumb["admin_order_properties.php?shipping_module_id=".$shipping_module_id] = $custom_module_name." :: ".CUSTOM_FIELDS_MSG;
		} else {
			header("Location: ".$settings_page);
			exit;
		}
	} else {
		$custom_module_name = ORDER_PROFILE_PAGE_MSG;
		$custom_breadcrumb["admin_order_properties.php"] = $custom_module_name." :: ".CUSTOM_FIELDS_MSG;
	}

	$shipping_block =
		array(
			array(0, RADIOBUTTON_MSG),
			array(1, LISTBOX_MSG)
		);

	$payment_control_types =
		array(
			array(0, LISTBOX_MSG),
			array(1, RADIOBUTTON_MSG)
		);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_order_properties.html");
	$t->set_var("custom_module_name", htmlspecialchars($custom_module_name));


	$arps = new VA_URL("admin_order_properties.php", false);
	$arps->add_parameter("property_id", DB, "property_id");
	$arps->add_parameter("payment_id", REQUEST, "payment_id");
	$arps->add_parameter("shipping_module_id", REQUEST, "shipping_module_id");
	$arps->add_parameter("shipping_type_id", REQUEST, "shipping_type_id");

	$arp = new VA_URL("admin_order_property.php", false);
	$arp->add_parameter("property_id", DB, "property_id");
	$arp->add_parameter("payment_id", REQUEST, "payment_id");
	$arp->add_parameter("shipping_module_id", REQUEST, "shipping_module_id");
	$arp->add_parameter("shipping_type_id", REQUEST, "shipping_type_id");

	$t->set_var("admin_order_property_new_url", $arp->get_url());

	$arp = new VA_URL("admin_order_property.php", false);
	$arp->add_parameter("property_id", DB, "property_id");

	$sql  = " SELECT property_id, property_name, property_type, property_show, required, control_type ";
	$sql .= " FROM " . $table_prefix . "order_custom_properties ";
	$payment_id = get_param("payment_id");
	$shipping_module_id = get_param("shipping_module_id");
	$shipping_type_id = get_param("shipping_type_id");
	if ($payment_id) {
		$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
	} else if ($shipping_type_id) {
		$sql .= " WHERE shipping_type_id=" . $db->tosql($shipping_type_id, INTEGER);
	} else if ($shipping_module_id) {
		$sql .= " WHERE shipping_module_id=" . $db->tosql($shipping_module_id, INTEGER);
	} else {
		$sql .= " WHERE (payment_id=0 OR payment_id IS NULL) ";
		$sql .= " AND (shipping_type_id=0 OR shipping_type_id IS NULL) ";
		$sql .= " AND (shipping_module_id=0 OR shipping_module_id IS NULL)" ;
	}
	$sql .= " ORDER BY property_order, property_id ";
	$db->query($sql);
	if ($db->next_record()) {
		$property_types = array("0" => HIDDEN_MSG, "1" => ADMIN_CART_MSG, "2" => PERSONAL_DETAILS_MSG, "3" => DELIVERY_DETAILS_MSG, "4" => PAYMENT_DETAILS_MSG, "5" => SHIPPING_SETTINGS_MSG, "6" => SHIPPING_SETTINGS_MSG);
		$property_show_values = array("-1" => DONT_SHOW_MSG, "0" => FOR_ALL_ORDERS_MSG, "1" => ONLY_WEB_ORDERS_MSG, "2" => ONLY_FOR_CALL_CENTRE_MSG);
		$controls = array(
			"CHECKBOXLIST" => CHECKBOXLIST_MSG, "LABEL" => LABEL_MSG, "LISTBOX" => LISTBOX_MSG,
			"RADIOBUTTON" => RADIOBUTTON_MSG, "TEXTAREA" => TEXTAREA_MSG, "TEXTBOX" => TEXTBOX_MSG, "HIDDEN" => HIDDEN_MSG);

		$t->parse("name_properties", false);
		do {
			$property_id = $db->f("property_id");
			$property_name = get_translation($db->f("property_name"));
			$property_type = get_setting_value($property_types, $db->f("property_type"), "");
			$property_show = get_setting_value($property_show_values, $db->f("property_show"), "");
			$control_type = $db->f("control_type");
			$control_name = isset($controls[$control_type]) ? $controls[$control_type] : $control_type;
			$property_required = $db->f("required");
			if ($property_required) {
				$property_required_desc = YES_MSG;
				$property_required_class= "required-yes";
				$arps->add_parameter("operation", CONSTANT, "required-no");
			} else {
				$property_required_desc = NO_MSG;
				$property_required_class= "required-no";
				$arps->add_parameter("operation", CONSTANT, "required-yes");
			}

			$t->set_var("property_id",   $property_id);
			$t->set_var("property_name", $property_name);
			$t->set_var("property_required_desc", $property_required_desc);
			$t->set_var("property_required_class", $property_required_class);
			$t->set_var("property_required_url", $arps->get_url());
			$t->set_var("admin_order_property_url", $arp->get_url());

			$t->set_var("property_type", $property_type);
			$t->set_var("property_show", $property_show);
			$t->set_var("control_type",  $control_name);

			$t->parse("properties", true);
		} while ($db->next_record());
	} else {
		$t->parse("no_properties", false);
	}

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_href", "admin.php");
	
	$t->pparse("main");

?>