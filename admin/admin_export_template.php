<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_export_template.php                                ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "messages/".$language_code."/cart_messages.php");
	include_once($root_folder_path . "messages/".$language_code."/download_messages.php");

	check_admin_security("static_tables");

	$permissions = get_permissions();
	$products_export = get_setting_value($permissions, "products_export", 0);
	$categories_export = get_setting_value($permissions, "categories_export", 0);
	$admin_registration = get_setting_value($permissions, "admin_registration", 0);
	$export_users = get_setting_value($permissions, "export_users", 0);
	$sales_orders = get_setting_value($permissions, "sales_orders", 0);

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_export_template.html");
	$t->set_var("date_edit_format", join("", $date_edit_format));
	$t->set_var("admin_export_template_href",   "admin_export_template.php");
	$t->set_var("admin_export_templates_href", "admin_export_templates.php");
	$t->set_var("admin_lookup_tables_href", "admin_lookup_tables.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", EXPORT_TEMPLATE_MSG, CONFIRM_DELETE_MSG));

	$r = new VA_Record($table_prefix . "export_templates ");
	$r->return_page  = "admin_export_templates.php";

	// initialiaze arrays
	$export_tables = array(array("", ""));
	$table_filters = array();

	if ($products_export) {
		$export_tables[] = array("items", PRODUCTS_MSG);
		$table_filters["items"] = "i";
	}
	if ($categories_export) {
		$export_tables[] = array("categories", PRODUCT_CATEGORIES_MSG);
		$table_filters["categories"] = "c";
	}
	if ($sales_orders) {
		$export_tables[] = array("orders", ORDERS_MSG);
		$table_filters["orders"] = "o";
	}
	if ($products_export) {
		$export_tables[] = array("items_files", DOWNLOADABLE_FILES_MSG);
		$table_filters["items_files"] = "if";
	}
	if ($products_export) {
		$export_tables[] = array("items_prices", QUANTITY_PRICES_MSG);
		$table_filters["items_prices"] = "prices";
	}
	if ($products_export) {
		$export_tables[] = array("items_properties_values", OPTIONS_VALUES_MSG);
		$table_filters["items_properties_values"] = "ipv";
	}

	if ($export_users) {
		$export_tables[] = array("users", USERS_MSG);
		$table_filters["users"] = "u";
	}
	if ($export_users) {
		$export_tables[] = array("newsletters_users", NEWSLETTER_USERS_MSG);
		$table_filters["newsletters_users"] = "nu";
	}
	if ($admin_registration) {
		$export_tables[] = array("registration_list", REGISTERED_PRODUCTS_MSG);
		$table_filters["registration_list"] = "rl";
	}

	$r->add_where("template_id", INTEGER);
	$r->add_textbox("template_name", TEXT, TEMPLATE_NAME_MSG);
	$r->change_property("template_name", REQUIRED, true);
	$r->add_select("table_name", TEXT, $export_tables, DATABASE_TABLE_MSG);
	$r->change_property("table_name", REQUIRED, true);

	$r->add_checkbox("save_file", INTEGER);
	$r->add_textbox("file_path_mask", TEXT);
	$r->add_textbox("file_path_mask_copy", TEXT);
	$r->add_checkbox("is_cronjob", INTEGER);
	$r->add_checkbox("use_filter", INTEGER);

	// ftp fields
	$ftp_transfer_modes = array(
		array("ascii", FTP_ASCII_MSG),
		array("binary", FTP_BINARY_MSG),
	);

	$r->add_checkbox("ftp_upload", INTEGER);
	$r->add_checkbox("ftp_passive_mode", INTEGER);
	$r->add_radio("ftp_transfer_mode", TEXT, $ftp_transfer_modes);
	$r->add_textbox("ftp_host", TEXT);
	$r->add_textbox("ftp_port", TEXT);
	$r->add_textbox("ftp_login", TEXT);
	$r->add_textbox("ftp_password", TEXT);
	$r->add_textbox("ftp_path", TEXT);

	$sql = "SELECT status_id, status_name FROM " . $table_prefix . "order_statuses WHERE is_active=1 ORDER BY status_order, status_id";
	$order_statuses = get_db_values($sql, array(array("", "")));
	$r->add_select("order_status_update", INTEGER, $order_statuses);

	// editing information
	$r->add_textbox("admin_id_added_by", INTEGER);
	$r->change_property("admin_id_added_by", USE_IN_UPDATE, false);
	$r->add_textbox("admin_id_modified_by", INTEGER);
	$r->change_property("admin_id_modified_by", USE_IN_INSERT, false);
	$r->add_textbox("date_added", DATETIME);
	$r->change_property("date_added", USE_IN_UPDATE, false);
	$r->add_textbox("date_modified", DATETIME);
	$r->change_property("date_modified", USE_IN_INSERT, false);

	// events
	$r->set_event(BEFORE_INSERT,  "set_export_data");
	$r->set_event(BEFORE_UPDATE,  "set_export_data");
	$r->set_event(AFTER_DELETE,  "delete_export_fields");
	$r->set_event(AFTER_SHOW,    "show_filters");
	$r->set_event(AFTER_REQUEST, "get_filters");
	$r->set_event(AFTER_SELECT,  "select_filters");
	$r->set_event(AFTER_INSERT,  "insert_filters");
	$r->set_event(AFTER_UPDATE,  "update_filters");
	$r->set_event(AFTER_DELETE,  "delete_filters");

	// filters records
	$fr = new VA_Record($table_prefix . "export_filters", "export_filters");
	// order filters
	$sql = "SELECT status_id, status_name FROM " . $table_prefix . "order_statuses WHERE is_active=1 ORDER BY status_order, status_id";
	$order_statuses = get_db_values($sql, array(array("", "")));
	$countries = get_db_values("SELECT country_id, country_name FROM " . $table_prefix . "countries ORDER BY country_order, country_name ", array(array("", "")));
	$states = get_db_values("SELECT state_id, state_name FROM " . $table_prefix . "states ORDER BY state_name ", array(array("", "")));
	$cc_default_types = array(array("", ""), array("blank", WITHOUT_CARD_TYPE_MSG));
	$credit_card_types = get_db_values("SELECT credit_card_id, credit_card_name FROM " . $table_prefix . "credit_cards ORDER BY credit_card_name", $cc_default_types);
	$export_options = array(array("", ALL_MSG), array("1", EXPORTED_MSG), array("0", NEVER_EXPORTED_MSG));
	$paid_options = array(array("", ANY_MSG), array("1", PAID_MSG), array("0", NOT_PAID_MSG));
	if ($sitelist) {
		$sites = get_db_values("SELECT site_id, site_name FROM " . $table_prefix . "sites ORDER BY site_id ", array(array("", "")));
	}
	$fr->add_textbox("o_s_on", TEXT, ORDER_NUMBER_MSG);
	$fr->change_property("o_s_on", TRIM, true);
	$fr->add_textbox("o_s_ne", TEXT);
	$fr->change_property("o_s_ne", TRIM, true);
	$fr->add_textbox("o_s_kw", TEXT);
	$fr->change_property("o_s_kw", TRIM, true);
	$fr->add_textbox("o_s_sd", TEXT, FROM_DATE_MSG);
	//$fr->change_property("o_s_sd", VALUE_MASK, $date_edit_format);
	$fr->change_property("o_s_sd", TRIM, true);
	$fr->add_textbox("o_s_ed", TEXT, END_DATE_MSG);
	//$fr->change_property("o_s_ed", VALUE_MASK, $date_edit_format);
	$fr->change_property("o_s_ed", TRIM, true);		
	$fr->add_select("o_s_os", TEXT, $order_statuses);
	$fr->add_select("o_s_ci", TEXT, $countries);
	$fr->add_select("o_s_si", TEXT, $states);
	$fr->add_select("o_s_cct", TEXT, $credit_card_types);
	$fr->add_select("o_s_ex", TEXT, $export_options);
	$fr->add_select("o_s_pd", TEXT, $paid_options);
	if ($sitelist) {
		$fr->add_select("o_s_sti", TEXT, $sites);
	}
	// products filters
	$stock_levels =
		array(
			array("", ""), array(0, OUTOFSTOCK_PRODUCTS_MSG), array(1, INSTOCK_PRODUCTS_MSG)
		);
	$sales =
		array(
			array("", ""), array(0, NOT_FOR_SALES_MSG), array(1, FOR_SALES_MSG)
		);
	$aproved_values =
		array(
			array("", ""), array(0, NO_MSG), array(1, YES_MSG)
		);
	$show_items =
		array(
			array("", ""), array(2, WITHOUT_IMAGES_MSG), 
		);
	$fr->add_select("i_sl", TEXT, $stock_levels, PROD_STOCK_MSG);
	$fr->add_select("i_ss", TEXT, $sales, SALES_MSG);
	$fr->add_select("i_sit", TEXT, $show_items);
	$fr->add_select("i_ap", TEXT, $aproved_values, IS_APPROVED_MSG);

	$r->process();


	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set styles for tabs
	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }
	$tabs = array(
		"general" => array("title" => ADMIN_GENERAL_MSG), 
		"cronjob" => array("title" => CRON_JOB_MSG), 
		"ftp" => array("title" => "FTP"), 
		"filters" => array("title" => FILTERS_MSG),
	);

	parse_admin_tabs($tabs, $tab, 6);


	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_export_templates_href", "admin_export_templates.php");
	$t->pparse("main");

	function delete_export_fields()
	{
		global $r, $db, $table_prefix;
		$template_id = $r->get_value("template_id");
		$sql  = " DELETE FROM " . $table_prefix . "export_fields ";
		$sql .= " WHERE template_id=" . $db->tosql($template_id, INTEGER);
		$db->query($sql);
	}

	function set_export_data()
	{
		global $r;
		$r->set_value("admin_id_added_by", get_session("session_admin_id"));
		$r->set_value("admin_id_modified_by", get_session("session_admin_id"));
		$r->set_value("date_added", va_time());
		$r->set_value("date_modified", va_time());
	}

	function show_filters()
	{
		global $fr, $r, $t, $table_filters;
		$fr->set_parameters();
		// hide or show table filters
		$table_name = $r->get_value("table_name");
		foreach($table_filters as $filter_table => $filter_prefix) {
			if ($table_name == $filter_table) {
				$t->set_var($filter_table."_filters_style", "display: block;");
			} else {
				$t->set_var($filter_table."_filters_style", "display: none;");
			}
		}
		if ($table_name != "orders") {
			$t->set_var("update_action_style", "display: none;");
			$t->set_var("update_order_status_style", "display: none;");
		}
		
	}

	function get_filters()
	{
		global $fr;
		$fr->get_form_parameters();
	}

	function select_filters()
	{
		global $r, $fr, $db, $table_prefix, $table_filters;
		$template_id = $r->get_value("template_id");
		$table_name = $r->get_value("table_name");
		if (strlen($template_id) && $table_name) {
			if (isset($table_filters[$table_name])) {
				$param_prefix = $table_filters[$table_name];
				$sql  = " SELECT filter_parameter, filter_value ";
				$sql .= " FROM  " . $table_prefix . "export_filters ";
				$sql .= " WHERE template_id=" . $db->tosql($template_id, INTEGER);
				$db->query($sql);
				while ($db->next_record()) {
					$filter_parameter = $db->f("filter_parameter");
					$filter_value = $db->f("filter_value");
					$parameter_name = $param_prefix."_".$filter_parameter;
					$fr->set_value($parameter_name, $filter_value);
				}
			} else {
				$r->errors = ADMIN_ACCESS_ERROR;
				$r->operations[INSERT_ALLOWED] = false;
				$r->operations[UPDATE_ALLOWED] = false;
				$r->operations[DELETE_ALLOWED] = false;
			}
		}
	}

	function insert_filters()
	{
		global $r, $fr, $db, $table_prefix, $table_filters;
		if ($db->DBType == "mysql") {
			$new_template_id = get_db_value(" SELECT LAST_INSERT_ID() ");
			$r->set_value("template_id", $new_template_id);
		} elseif ($db->DBType == "access") {
			$new_template_id = get_db_value(" SELECT @@IDENTITY ");
			$r->set_value("template_id", $new_template_id);
		} else {
			$new_template_id = get_db_value(" SELECT MAX(template_id) FROM " . $table_prefix . "export_templates ");
			$r->set_value("template_id", $new_template_id);
		}
		update_filters();
	}

	function update_filters()
	{
		global $r, $fr, $db, $table_prefix, $table_filters;
		$template_id = $r->get_value("template_id");
		$table_name = $r->get_value("table_name");
		if (strlen($template_id) && $table_name) {
			// delete filters before insert new
			$sql = " DELETE FROM " . $table_prefix . "export_filters WHERE template_id=" . $db->tosql($template_id, INTEGER); 
			$db->query($sql);
			// add new filters if they start with selected prefix
			$param_prefix = $table_filters[$table_name];
			$fr->get_form_parameters();
			foreach ($fr->parameters as $key => $value) {
				if (preg_match("/^".preg_quote($param_prefix, "/")."_(\w+)$/", $key, $matches) && strlen($value[CONTROL_VALUE])) {
					$parameter_name = $matches[1];
					$parameter_value = $value[CONTROL_VALUE];
					$sql  = "INSERT INTO " . $table_prefix . "export_filters (template_id, filter_parameter, filter_value) VALUES (";
					$sql .= $db->tosql($template_id, INTEGER) . ", ";
					$sql .= $db->tosql($parameter_name, TEXT) . ", ";
					$sql .= $db->tosql($parameter_value, TEXT) . ") ";
					$db->query($sql);
				}
			}

		}
	}

	function delete_filters()
	{
		global $r, $db, $table_prefix;
		$template_id = $r->get_value("template_id");
		$table_name = $r->get_value("table_name");
		if (strlen($template_id) && $table_name) {
			// delete filters before insert new
			$sql = " DELETE FROM " . $table_prefix . "export_filters WHERE template_id=" . $db->tosql($template_id, INTEGER); 
			$db->query($sql);
		}
	}

?>