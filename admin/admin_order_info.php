<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_order_info.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ($root_folder_path . "includes/record.php");
	include_once ($root_folder_path . "includes/tabs_functions.php");
	include_once ("../messages/".$language_code."/cart_messages.php");

	include_once("./admin_common.php");

	check_admin_security("sales_orders");
	check_admin_security("order_profile");

	$va_trail = array(
		"admin_menu.php?code=settings" => va_constant("SETTINGS_MSG"),
		"admin_menu.php?code=orders-settings" => va_constant("ORDERS_MSG"),
		"admin_order_info.php" => va_constant("ORDER_PROFILE_PAGE_MSG"),
	);

	$setting_type = "order_info";

	// additional connection 
	$dbs = new VA_SQL();
	$dbs->DBType      = $db_type;
	$dbs->DBDatabase  = $db_name;
	$dbs->DBUser      = $db_user;
	$dbs->DBPassword  = $db_password;
	$dbs->DBHost      = $db_host;
	$dbs->DBPort      = $db_port;
	$dbs->DBPersistent= $db_persistent;

	$message_types =
		array(
			array(1, HTML_MSG), array(0, PLAIN_TEXT_MSG)
		);

	$yes_no = 
		array( 
			array(1, va_message("YES_MSG")), array(0, va_message("NO_MSG"))
			);

	$opc_types =
		array(
			array("steps", OPC_DIVIDED_STEPS_MSG), 
			array("single", OPC_SINGLE_FORM_MSG)
		);

	$subcomponents_values =
		array(
			array(0, EACH_SUBCOMP_SEPARATE_MSG),
			array(1, SUBCOMP_SHOWN_UNDERNEATH_MSG),
		);

	$control_types =
		array(
			array(0, LISTBOX_MSG),
			array(1, RADIOBUTTON_MSG)
		);

	$checkout_flow_types =
		array(
			array("default", DEFAULT_CHECKOUT_MSG),
			array("quote", QUOTE_FIRST_MSG)
		);


	$image_options =
		array(
			array(0, NO_IMAGE_MSG),
			array(1, IMAGE_SMALL_MSG),
			array(2, IMAGE_LARGE_MSG)
		);

	$attachments_allowed_values = 
		array(
			array(0, POINTS_NOT_ALLOWED_MSG),
			array(1, ONLY_FOR_LOGGED_IN_USERS_MSG),
			);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_order_info.html");
	include_once("./admin_header.php");

	$t->set_var("admin_order_info_href", "admin_order_info.php");
	$t->set_var("admin_order_help_href", "admin_order_help.php");
	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_email_help_href", "admin_email_help.php");
	$t->set_var("admin_order_property_href", "admin_order_property.php");
	$t->set_var("days_msg", strtolower(va_message("DAYS_MSG")));
	$t->set_var("CART_SUBPRODUCT_NAME_DESC", va_message("CART_SUBPRODUCT_NAME_DESC"));

	$html_editor = get_setting_value($settings, "html_editor_email", get_setting_value($settings, "html_editor", 1));
	$t->set_var("html_editor", $html_editor);
	$editors_list = 'am,um,pmb';
	add_html_editors($editors_list, $html_editor);

	$r = new VA_Record($table_prefix . "global_settings");
	// invoice settings
	$r->add_textbox("invoice_sequence_number", INTEGER, INVOICE_SEQUENCE_NUMBER_MSG);
	$r->add_textbox("invoice_number_mask", TEXT);
	$r->add_textbox("credit_sequence_number", INTEGER, CREDIT_SEQUENCE_NUMBER_MSG);
	$r->add_textbox("credit_number_mask", TEXT);

	$r->add_radio("opc_type", TEXT, $opc_types);
	$r->add_radio("subcomponents_show_type", TEXT, $subcomponents_values);
	$r->add_textbox("cart_subitem_name", TEXT);
	$r->change_property("cart_subitem_name", USE_IN_INSERT, false);

	// shipping settings 
	$r->add_textbox("shipping_intro", TEXT);
	$r->add_radio("shipping_block", TEXT, $control_types);
	$r->add_select("shipping_image", INTEGER, $image_options);
	// payment system fields
	$r->add_radio("payment_control_type", TEXT, $control_types);
	$r->add_select("payment_image", INTEGER, $image_options);
	$r->add_radio("allow_partial_payment", TEXT, $yes_no);
	$r->add_textbox("partial_payment_options", TEXT);

	// restriction fields
	$r->add_radio("checkout_flow", TEXT, $checkout_flow_types);
	$r->add_textbox("order_min_goods_cost", FLOAT, ORDER_MIN_PRODUCTS_COST_FIELD);
	$r->add_textbox("order_max_goods_cost", FLOAT, ORDER_MAX_PRODUCTS_COST_FIELD);
	$r->add_textbox("order_min_weight", FLOAT, ORDER_MIN_WEIGHT_FIELD);
	$r->add_textbox("order_max_weight", FLOAT, ORDER_MAX_WEIGHT_FIELD);
	$r->add_checkbox("prevent_repurchase", INTEGER);
	$r->add_textbox("repurchase_period", FLOAT, REPURCHASE_PERIOD_MSG);

	// attachments settings
	$r->add_textbox("attachments_dir", TEXT);
	$r->add_radio("attachments_users_allowed", TEXT, $attachments_allowed_values);
	$r->change_property("attachments_users_allowed", SHOW, false);
	$r->add_textbox("attachments_users_mask", TEXT);
	$r->change_property("attachments_users_mask", SHOW, false);

	// set up html form parameters
	$r->add_checkbox("show_name", INTEGER);
	$r->add_checkbox("show_first_name", INTEGER);
	$r->add_checkbox("show_middle_name", INTEGER);
	$r->add_checkbox("show_last_name", INTEGER);
	$r->add_checkbox("show_company_id", INTEGER);
	$r->add_checkbox("show_company_name", INTEGER);
	$r->add_checkbox("show_email", INTEGER);
	$r->add_checkbox("show_address1", INTEGER);
	$r->add_checkbox("show_address2", INTEGER);
	$r->add_checkbox("show_address3", INTEGER);
	$r->add_checkbox("show_city", INTEGER);
	$r->add_checkbox("show_province", INTEGER);
	$r->add_checkbox("show_state_id", INTEGER);
	$r->add_checkbox("show_zip", INTEGER);
	$r->add_checkbox("show_country_id", INTEGER);
	$r->add_checkbox("show_phone", INTEGER);
	$r->add_checkbox("show_daytime_phone", INTEGER);
	$r->add_checkbox("show_evening_phone", INTEGER);
	$r->add_checkbox("show_cell_phone", INTEGER);
	$r->add_checkbox("show_fax", INTEGER);

	$r->add_checkbox("show_delivery_name", INTEGER);
	$r->add_checkbox("show_delivery_first_name", INTEGER);
	$r->add_checkbox("show_delivery_middle_name", INTEGER);
	$r->add_checkbox("show_delivery_last_name", INTEGER);
	$r->add_checkbox("show_delivery_company_id", INTEGER);
	$r->add_checkbox("show_delivery_company_name", INTEGER);
	$r->add_checkbox("show_delivery_email", INTEGER);
	$r->add_checkbox("show_delivery_address1", INTEGER);
	$r->add_checkbox("show_delivery_address2", INTEGER);
	$r->add_checkbox("show_delivery_address3", INTEGER);
	$r->add_checkbox("show_delivery_city", INTEGER);
	$r->add_checkbox("show_delivery_province", INTEGER);
	$r->add_checkbox("show_delivery_state_id", INTEGER);
	$r->add_checkbox("show_delivery_zip", INTEGER);
	$r->add_checkbox("show_delivery_country_id", INTEGER);
	$r->add_checkbox("show_delivery_phone", INTEGER);
	$r->add_checkbox("show_delivery_daytime_phone", INTEGER);
	$r->add_checkbox("show_delivery_evening_phone", INTEGER);
	$r->add_checkbox("show_delivery_cell_phone", INTEGER);
	$r->add_checkbox("show_delivery_fax", INTEGER);

	$r->add_checkbox("name_required", INTEGER);
	$r->add_checkbox("first_name_required", INTEGER);
	$r->add_checkbox("middle_name_required", INTEGER);
	$r->add_checkbox("last_name_required", INTEGER);
	$r->add_checkbox("company_id_required", INTEGER);
	$r->add_checkbox("company_name_required", INTEGER);
	$r->add_checkbox("email_required", INTEGER);
	$r->add_checkbox("address1_required", INTEGER);
	$r->add_checkbox("address2_required", INTEGER);
	$r->add_checkbox("address3_required", INTEGER);
	$r->add_checkbox("city_required", INTEGER);
	$r->add_checkbox("province_required", INTEGER);
	$r->add_checkbox("state_id_required", INTEGER);
	$r->add_checkbox("zip_required", INTEGER);
	$r->add_checkbox("country_id_required", INTEGER);
	$r->add_checkbox("phone_required", INTEGER);
	$r->add_checkbox("daytime_phone_required", INTEGER);
	$r->add_checkbox("evening_phone_required", INTEGER);
	$r->add_checkbox("cell_phone_required", INTEGER);
	$r->add_checkbox("fax_required", INTEGER);

	$r->add_checkbox("delivery_name_required", INTEGER);
	$r->add_checkbox("delivery_first_name_required", INTEGER);
	$r->add_checkbox("delivery_middle_name_required", INTEGER);
	$r->add_checkbox("delivery_last_name_required", INTEGER);
	$r->add_checkbox("delivery_company_id_required", INTEGER);
	$r->add_checkbox("delivery_company_name_required", INTEGER);
	$r->add_checkbox("delivery_email_required", INTEGER);
	$r->add_checkbox("delivery_address1_required", INTEGER);
	$r->add_checkbox("delivery_address2_required", INTEGER);
	$r->add_checkbox("delivery_address3_required", INTEGER);
	$r->add_checkbox("delivery_city_required", INTEGER);
	$r->add_checkbox("delivery_province_required", INTEGER);
	$r->add_checkbox("delivery_state_id_required", INTEGER);
	$r->add_checkbox("delivery_zip_required", INTEGER);
	$r->add_checkbox("delivery_country_id_required", INTEGER);
	$r->add_checkbox("delivery_phone_required", INTEGER);
	$r->add_checkbox("delivery_daytime_phone_required", INTEGER);
	$r->add_checkbox("delivery_evening_phone_required", INTEGER);
	$r->add_checkbox("delivery_cell_phone_required", INTEGER);
	$r->add_checkbox("delivery_fax_required", INTEGER);

	// add checkboxes for Call Center
	$r->add_checkbox("call_center_show_name", INTEGER);
	$r->add_checkbox("call_center_show_first_name", INTEGER);
	$r->add_checkbox("call_center_show_middle_name", INTEGER);
	$r->add_checkbox("call_center_show_last_name", INTEGER);
	$r->add_checkbox("call_center_show_company_id", INTEGER);
	$r->add_checkbox("call_center_show_company_name", INTEGER);
	$r->add_checkbox("call_center_show_email", INTEGER);
	$r->add_checkbox("call_center_show_address1", INTEGER);
	$r->add_checkbox("call_center_show_address2", INTEGER);
	$r->add_checkbox("call_center_show_address3", INTEGER);
	$r->add_checkbox("call_center_show_city", INTEGER);
	$r->add_checkbox("call_center_show_province", INTEGER);
	$r->add_checkbox("call_center_show_state_id", INTEGER);
	$r->add_checkbox("call_center_show_zip", INTEGER);
	$r->add_checkbox("call_center_show_country_id", INTEGER);
	$r->add_checkbox("call_center_show_phone", INTEGER);
	$r->add_checkbox("call_center_show_daytime_phone", INTEGER);
	$r->add_checkbox("call_center_show_evening_phone", INTEGER);
	$r->add_checkbox("call_center_show_cell_phone", INTEGER);
	$r->add_checkbox("call_center_show_fax", INTEGER);

	$r->add_checkbox("call_center_show_delivery_name", INTEGER);
	$r->add_checkbox("call_center_show_delivery_first_name", INTEGER);
	$r->add_checkbox("call_center_show_delivery_middle_name", INTEGER);
	$r->add_checkbox("call_center_show_delivery_last_name", INTEGER);
	$r->add_checkbox("call_center_show_delivery_company_id", INTEGER);
	$r->add_checkbox("call_center_show_delivery_company_name", INTEGER);
	$r->add_checkbox("call_center_show_delivery_email", INTEGER);
	$r->add_checkbox("call_center_show_delivery_address1", INTEGER);
	$r->add_checkbox("call_center_show_delivery_address2", INTEGER);
	$r->add_checkbox("call_center_show_delivery_address3", INTEGER);
	$r->add_checkbox("call_center_show_delivery_city", INTEGER);
	$r->add_checkbox("call_center_show_delivery_province", INTEGER);
	$r->add_checkbox("call_center_show_delivery_state_id", INTEGER);
	$r->add_checkbox("call_center_show_delivery_zip", INTEGER);
	$r->add_checkbox("call_center_show_delivery_country_id", INTEGER);
	$r->add_checkbox("call_center_show_delivery_phone", INTEGER);
	$r->add_checkbox("call_center_show_delivery_daytime_phone", INTEGER);
	$r->add_checkbox("call_center_show_delivery_evening_phone", INTEGER);
	$r->add_checkbox("call_center_show_delivery_cell_phone", INTEGER);
	$r->add_checkbox("call_center_show_delivery_fax", INTEGER);

	$r->add_checkbox("call_center_name_required", INTEGER);
	$r->add_checkbox("call_center_first_name_required", INTEGER);
	$r->add_checkbox("call_center_middle_name_required", INTEGER);
	$r->add_checkbox("call_center_last_name_required", INTEGER);
	$r->add_checkbox("call_center_company_id_required", INTEGER);
	$r->add_checkbox("call_center_company_name_required", INTEGER);
	$r->add_checkbox("call_center_email_required", INTEGER);
	$r->add_checkbox("call_center_address1_required", INTEGER);
	$r->add_checkbox("call_center_address2_required", INTEGER);
	$r->add_checkbox("call_center_address3_required", INTEGER);
	$r->add_checkbox("call_center_city_required", INTEGER);
	$r->add_checkbox("call_center_province_required", INTEGER);
	$r->add_checkbox("call_center_state_id_required", INTEGER);
	$r->add_checkbox("call_center_zip_required", INTEGER);
	$r->add_checkbox("call_center_country_id_required", INTEGER);
	$r->add_checkbox("call_center_phone_required", INTEGER);
	$r->add_checkbox("call_center_daytime_phone_required", INTEGER);
	$r->add_checkbox("call_center_evening_phone_required", INTEGER);
	$r->add_checkbox("call_center_cell_phone_required", INTEGER);
	$r->add_checkbox("call_center_fax_required", INTEGER);

	$r->add_checkbox("call_center_delivery_name_required", INTEGER);
	$r->add_checkbox("call_center_delivery_first_name_required", INTEGER);
	$r->add_checkbox("call_center_delivery_middle_name_required", INTEGER);
	$r->add_checkbox("call_center_delivery_last_name_required", INTEGER);
	$r->add_checkbox("call_center_delivery_company_id_required", INTEGER);
	$r->add_checkbox("call_center_delivery_company_name_required", INTEGER);
	$r->add_checkbox("call_center_delivery_email_required", INTEGER);
	$r->add_checkbox("call_center_delivery_address1_required", INTEGER);
	$r->add_checkbox("call_center_delivery_address2_required", INTEGER);
	$r->add_checkbox("call_center_delivery_address3_required", INTEGER);
	$r->add_checkbox("call_center_delivery_city_required", INTEGER);
	$r->add_checkbox("call_center_delivery_province_required", INTEGER);
	$r->add_checkbox("call_center_delivery_state_id_required", INTEGER);
	$r->add_checkbox("call_center_delivery_zip_required", INTEGER);
	$r->add_checkbox("call_center_delivery_country_id_required", INTEGER);
	$r->add_checkbox("call_center_delivery_phone_required", INTEGER);
	$r->add_checkbox("call_center_delivery_daytime_phone_required", INTEGER);
	$r->add_checkbox("call_center_delivery_evening_phone_required", INTEGER);
	$r->add_checkbox("call_center_delivery_cell_phone_required", INTEGER);
	$r->add_checkbox("call_center_delivery_fax_required", INTEGER);

	$r->add_checkbox("subscribe_block", INTEGER);

	$r->add_checkbox("admin_notification", INTEGER);
	$r->add_textbox("admin_email", TEXT);
	$r->add_textbox("admin_mail_from", TEXT);
	$r->add_textbox("cc_emails", TEXT);
	$r->add_textbox("admin_mail_bcc", TEXT);
	$r->add_textbox("admin_mail_reply_to", TEXT);
	$r->add_textbox("admin_mail_return_path", TEXT);
	$r->add_textbox("admin_subject", TEXT);
	$r->add_radio("admin_message_type", TEXT, $message_types);
	$r->add_textbox("admin_message", TEXT);

	$r->add_checkbox("user_notification", INTEGER);
	$r->add_textbox("user_mail_from", TEXT);
	$r->add_textbox("user_mail_cc", TEXT);
	$r->add_textbox("user_mail_bcc", TEXT);
	$r->add_textbox("user_mail_reply_to", TEXT);
	$r->add_textbox("user_mail_return_path", TEXT);
	$r->add_textbox("user_subject", TEXT);
	$r->add_radio("user_message_type", TEXT, $message_types);
	$r->add_textbox("user_message", TEXT);

	// sms notification settings
	$r->add_checkbox("admin_sms_notification", INTEGER);
	$r->add_textbox("admin_sms_recipient", TEXT, ADMIN_SMS_RECIPIENT_MSG);
	$r->add_textbox("admin_sms_originator", TEXT, ADMIN_SMS_ORIGINATOR_MSG);
	$r->add_textbox("admin_sms_message", TEXT, ADMIN_SMS_MESSAGE_MSG);

	$r->add_checkbox("user_sms_notification", INTEGER);
	$r->add_textbox("user_sms_recipient", TEXT, USER_SMS_RECIPIENT_MSG);
	$r->add_textbox("user_sms_originator", TEXT, USER_SMS_ORIGINATOR_MSG);
	$r->add_textbox("user_sms_message", TEXT, USER_SMS_MESSAGE_MSG);

	// predefined email
	$r->add_textbox("predefined_mail_from", TEXT);
	$r->add_textbox("predefined_mail_cc", TEXT);
	$r->add_textbox("predefined_mail_bcc", TEXT);
	$r->add_textbox("predefined_mail_reply_to", TEXT);
	$r->add_textbox("predefined_mail_return_path", TEXT);
	$r->add_textbox("predefined_mail_subject", TEXT);
	$r->add_radio("predefined_mail_type", TEXT, $message_types);
	$r->add_textbox("predefined_mail_body", TEXT);

	$r->get_form_values();

	// sub record for partial options
	$po = new VA_Record($table_prefix . "global_settings");
	$po->add_textbox("description", TEXT, va_message("PARTIAL_PAYMENT_DESCRIPTION_MSG"));
	$po->change_property("description", REQUIRED, true);
	$po->add_textbox("percentage", NUMBER, va_message("PARTIAL_PAYMENT_PERCENTAGE_MSG"));
	$po->change_property("percentage", REQUIRED, true);
	$po->change_property("percentage", MIN_VALUE, 1);
	$po->change_property("percentage", MAX_VALUE, 100);
	$po->add_textbox("min_order", NUMBER, va_message("MIN_ORDER_COST_MSG"));
	$po->change_property("min_order", MIN_VALUE, 0.01);
	$po->add_textbox("max_order", NUMBER, va_message("MAX_ORDER_COST_MSG"));
	$po->change_property("max_order", MIN_VALUE, 0.01);

	$param_site_id = get_session("session_site_id");
	$operation = get_param("operation");	
	$property_id = get_param("property_id");
	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }	
	$return_page = get_param("rp");
	if (!strlen($return_page)) $return_page = "admin.php";
	$errors = "";

	$partial_options = array();
	if (strlen($operation))
	{
		// get partial options
		$partial_index = get_param("partial_index");
		for ($pi = 1; $pi <= $partial_index; $pi++) {
			$description = get_param("partial_description_".$pi);
			$percentage =  get_param("partial_percentage_".$pi);
			$min_order = get_param("partial_min_order_".$pi);
			$max_order = get_param("partial_max_order_".$pi);
			if(strlen($description) || strlen($percentage) || strlen($min_order) || strlen($max_order)) {
				$po->errors = "";
				$po->empty_values();
				$po->set_value("description", $description);
				$po->set_value("percentage", $percentage);
				$po->set_value("min_order", $min_order);
				$po->set_value("max_order", $max_order);
				if ($operation == "update" || $operation == "save") {
					$po->validate();
				} 
				$partial_options[] = array(
					"description" => $description,
					"percentage" => $percentage,
					"min_order" => $min_order,
					"max_order" => $max_order,
					"errors" => $po->errors,
				);
			}
		}

		if ($operation == "cancel") {
			header("Location: " . $return_page);
			exit;
		} else if ($operation == "required-yes") {
			$sql = " UPDATE ".$table_prefix."order_custom_properties ";
			$sql.= " SET required=1 ";
			$sql.= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
			$db->query($sql);
			$operation = ""; // clear operation parameter to get data from DB
		} else if ($operation == "required-no") {
			$sql = " UPDATE ".$table_prefix."order_custom_properties ";
			$sql.= " SET required=0 ";
			$sql.= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
			$db->query($sql);
			$operation = ""; // clear operation parameter to get data from DB
		} else if ($operation == "update" || $operation == "save") {

			if ($r->get_value("admin_sms_notification")) {
				$r->change_property("admin_sms_recipient", REQUIRED, true);
				$r->change_property("admin_sms_message", REQUIRED, true);
			}
			if ($r->get_value("user_sms_notification")) {
				$r->change_property("user_sms_message", REQUIRED, true);
			}
	  
			$form_valid = $r->validate();
			foreach ($partial_options as $pi => $partial_option) {
				if ($partial_option["errors"]) {
					$form_valid = false;
					$r->errors .= $partial_option["errors"];
					break;
				}
			}

			if (!strlen($r->errors))
			{
				// encode partial options to save them in database
				$r->set_value("partial_payment_options", json_encode($partial_options));

				// update order settings
				$new_settings = array();
				foreach ($r->parameters as $key => $value) {
					if ($r->get_property_value($key, USE_IN_INSERT)) {
						$new_settings[$key] = $value[CONTROL_VALUE];
					}
				}
				update_settings($setting_type, $param_site_id, $new_settings);

				// update some product settings
				$product_settings = get_settings("products", $param_site_id);
				$new_product_settings = $product_settings;
				$new_product_settings["cart_subitem_name"] = $r->get_value("cart_subitem_name");
				update_settings("products", $param_site_id, $new_product_settings, $product_settings);

				header("Location: " . $return_page);
				exit;
			}
		}
	} else {
		// get order_info settings
		$order_settings = get_settings($setting_type, $param_site_id);
		foreach ($order_settings as $setting_name => $setting_value) {
			if ($r->parameter_exists($setting_name)) {
				$r->set_value($setting_name, $setting_value);
			}
			if ($setting_name == "partial_payment_options") {
				$partial_options = json_decode($setting_value, true);
			}
		}
		// set product settings values
		$product_settings = get_settings("products", $param_site_id);
		$cart_subitem_name	= get_setting_value($product_settings, "cart_subitem_name");
		$r->set_value("cart_subitem_name", $cart_subitem_name);
	}

	$arp = new VA_URL("admin_order_info.php", false);
	$arp->add_parameter("property_id", DB, "property_id");
	$arp->add_parameter("tab", CONSTANT, "custom_fields");

	$sql  = " SELECT ocp.property_id, ocp.property_name, ocp.property_type, ocp.property_show, ocp.required, ocp.control_type ";
	$sql .= " FROM (" . $table_prefix . "order_custom_properties ocp ";
	$sql .= " LEFT JOIN " . $table_prefix . "order_custom_sites ocs ON ocp.property_id=ocs.property_id) ";
	$sql .= " WHERE property_type IN (0,1,2,3) ";
	$sql .= " AND (ocp.sites_all=1 OR ocp.site_id=".$db->tosql($param_site_id, INTEGER) . " OR ocs.site_id=".$db->tosql($param_site_id, INTEGER).") ";
	$sql .= " GROUP BY ocp.property_id ";
	$sql .= " ORDER BY ocp.property_order, ocp.property_id ";
	$db->query($sql);
	if ($db->next_record()) {
		$property_types = array("0" => HIDDEN_MSG, "1" => ADMIN_CART_MSG, "2" => PERSONAL_DETAILS_MSG, "3" => DELIVERY_DETAILS_MSG, "4" => PAYMENT_DETAILS_MSG, "5" => SHIPPING_SETTINGS_MSG, "6" => SHIPPING_SETTINGS_MSG);
		$property_show_values = array("-1" => DONT_SHOW_MSG, "0" => FOR_ALL_ORDERS_MSG, "1" => ONLY_WEB_ORDERS_MSG, "2" => ONLY_FOR_CALL_CENTRE_MSG);
		$controls = array(
			"CHECKBOXLIST" => va_message("CHECKBOXLIST_MSG"), "LABEL" => va_message("LABEL_MSG"), "LISTBOX" => va_message("LISTBOX_MSG"),
			"RADIOBUTTON" => va_message("RADIOBUTTON_MSG"), "TEXTAREA" => va_message("TEXTAREA_MSG"), "TEXTBOX" => va_message("TEXTBOX_MSG"), "HIDDEN" => va_message("HIDDEN_MSG"),
		);

		$t->parse("name_properties", false);
		do {
			$property_id = $db->f("property_id");
			$property_name = get_translation($db->f("property_name"));
			$property_type = get_setting_value($property_types, $db->f("property_type"), "");
			$property_show = get_setting_value($property_show_values, $db->f("property_show"), "");
			$control_type = $controls[$db->f("control_type")];
			$property_required = $db->f("required");
			if ($property_required) {
				$property_required_desc = YES_MSG;
				$property_required_class= "required-yes";
				$arp->add_parameter("operation", CONSTANT, "required-no");
			} else {
				$property_required_desc = NO_MSG;
				$property_required_class= "required-no";
				$arp->add_parameter("operation", CONSTANT, "required-yes");
			}

			$t->set_var("property_id",   $property_id);
			$t->set_var("property_name", $property_name);
			$t->set_var("property_required_desc", $property_required_desc);
			$t->set_var("property_required_class", $property_required_class);
			$t->set_var("property_required_url", $arp->get_url());
			$t->set_var("property_type", $property_type);
			$t->set_var("property_show", $property_show);
			$t->set_var("control_type",  $control_type);

			$t->parse("properties", true);
		} while ($db->next_record());
	} else {
		$t->parse("no_properties", false);
	}

	$r->set_parameters();
	$t->set_var("rp", htmlspecialchars($return_page));
	// parse partial payment settings
	if (is_array($partial_options)) {
		$partial_index = 0;
		foreach ($partial_options as $key => $partial_option) {
			$partial_index++;
			$description = $partial_option["description"];
			$percentage = $partial_option["percentage"];
			$min_order = $partial_option["min_order"];
			$max_order = $partial_option["max_order"];
			$po_error = $partial_option["errors"];
			if ($po_error) {
				$t->set_var("error_desc", $po_error);
				$t->parse("partial_option_error", false);
			} else {
				$t->set_var("partial_option_error", "");
			}
			$t->set_var("partial_option_class", "");
			$t->set_var("partial_option_style", "");
			$t->set_var("partial_description", htmlspecialchars($description));
			$t->set_var("partial_percentage", htmlspecialchars($percentage));
			$t->set_var("partial_min_order", htmlspecialchars($min_order));
			$t->set_var("partial_max_order", htmlspecialchars($max_order));
			$t->set_var("partial_description_name", "partial_description_".$partial_index);
			$t->set_var("partial_percentage_name", "partial_percentage_".$partial_index);
			$t->set_var("partial_min_order_name", "partial_min_order_".$partial_index);
			$t->set_var("partial_max_order_name", "partial_max_order_".$partial_index);
			$t->parse("partial_option", true);
		}
	}
	$t->set_var("partial_index", $partial_index);
	// parse partial option template
	$t->set_var("partial_option_error", "");
	$t->set_var("partial_option_class", "partial-option");
	$t->set_var("partial_option_style", "display: none;");
	$t->set_var("partial_description", "");
	$t->set_var("partial_percentage", "");
	$t->set_var("partial_min_order", "");
	$t->set_var("partial_max_order", "");
	$t->set_var("partial_description_name", "partial_description");
	$t->set_var("partial_percentage_name", "partial_percentage");
	$t->set_var("partial_min_order_name", "partial_min_order");
	$t->set_var("partial_max_order_name", "partial_max_order");
	$t->parse("partial_option", true);

	// multi-site settings
	multi_site_settings();

	$tabs = array(
		"general" => array("title" => va_message("ADMIN_GENERAL_MSG")), 
		"payment" => array("title" => va_message("PAYMENT_SETTINGS_MSG")), 
		"invoice" => array("title" => va_message("INVOICE_SETTINGS_MSG")), 
		"predefined_fields" => array("title" => va_message("PREDEFINED_FIELDS_MSG")), 
		"custom_fields" => array("title" => va_message("CUSTOM_ORDER_FILEDS_MSG")), 
		"notification_email" => array("title" => va_message("NOTIFICATION_EMAIL_MSG")), 
		"predefined_email" => array("title" => va_message("PREDEFINED_ORDER_EMAIL_MSG")), 
	);

	parse_tabs($tabs, $tab);

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_href", "admin.php");
	
	$t->pparse("main");

