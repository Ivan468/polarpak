<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_country.php                                        ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/tabs_functions.php");
	include_once("./admin_common.php");

	check_admin_security("static_tables");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_country.html");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_lookup_tables_href", "admin_lookup_tables.php");
	$t->set_var("admin_countries_href", "admin_countries.php");
	$t->set_var("admin_country_href", "admin_country.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", COUNTRY_FIELD, CONFIRM_DELETE_MSG));

	$sp = get_param("sp");
	$sort_ord = get_param("sort_ord");
	$sort_dir = get_param("sort_dir");
	$page = get_param("page");

	$length_systems =
		array( 
			array("", ""),
			array("int", va_message("INTERNATIONAL_SYSTEM_MSG")),
			array("imp", va_message("IMPERIAL_SYSTEM_MSG")),
		);

	$weight_systems =
		array( 
			array("", ""),
			array("int", va_message("INTERNATIONAL_SYSTEM_MSG")),
			array("imp", va_message("IMPERIAL_SYSTEM_MSG")),
		);

	$cs = new VA_Record(""); // special record to save data in country_settings field

	$r = new VA_Record($table_prefix . "countries");
	$r->return_page = "admin_countries.php?sort_ord=".urlencode($sort_ord)."&sort_dir=".urlencode($sort_dir)."&page=".urlencode($page)."&sp=".urlencode($sp);

	$r->add_where("country_id", INTEGER);
	$r->add_textbox("country_settings", TEXT);
	$r->add_checkbox("show_for_user", INTEGER);
	$r->change_property("show_for_user", DEFAULT_VALUE, 1);
	$r->add_checkbox("delivery_for_user", INTEGER);
	$r->change_property("delivery_for_user", DEFAULT_VALUE, 1);
	$r->add_checkbox("show_for_admin", INTEGER);
	$r->change_property("show_for_admin", DEFAULT_VALUE, 1);
	$r->add_checkbox("delivery_for_admin", INTEGER);
	$r->change_property("delivery_for_admin", DEFAULT_VALUE, 1);

	$r->add_textbox("country_order", INTEGER, ADMIN_ORDER_MSG);
	$r->add_textbox("country_iso_number", TEXT, ISO_NUMBER_MSG);
	$r->change_property("country_iso_number", REQUIRED, true);
	$r->change_property("country_iso_number", REGEXP_MASK, "/\\d+/");
	$r->add_textbox("country_code", TEXT, COUNTRY_CODE_MSG);
	$r->change_property("country_code", REQUIRED, true);
	$r->add_textbox("country_code_alpha3", TEXT, COUNTRY_CODE_ALPHA3_MSG);
	$r->add_textbox("phone_code", TEXT);

	$currencies = get_db_values("SELECT currency_code,currency_title FROM " . $table_prefix . "currencies", array(array("", "")));
	$r->add_select("currency_code", TEXT, $currencies, CURRENCY_CODE_MSG);
	$r->add_textbox("country_name", TEXT, COUNTRY_NAME_MSG);
	$r->change_property("country_name", REQUIRED, true);
	$r->add_textbox("state_field_name", TEXT);

	// postal code fields
	$r->add_textbox("postal_code_regexp", TEXT);
	$r->add_textbox("postal_code_error", TEXT);

	// country system units
	$cs->add_radio("length_system", TEXT, $length_systems);
	$cs->add_radio("weight_system", TEXT, $weight_systems);

	// save cs record parameters in country_settings field
	$r->add_textbox("country_settings", TEXT);

	// sites list
	$operation = get_param("operation");
	$country_id = get_param("country_id");
	$r->add_checkbox("sites_all", INTEGER);
	$r->change_property("sites_all", DEFAULT_VALUE, 1);
	if ($sitelist) {
		$selected_sites = array();
		if (strlen($operation)) {
			$sites = get_param("sites");
			if ($sites) {
				$selected_sites = explode(",", $sites);
			}
		} elseif ($country_id) {
			$sql  = "SELECT site_id FROM " . $table_prefix . "countries_sites ";
			$sql .= " WHERE country_id=" . $db->tosql($country_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$selected_sites[] = $db->f("site_id");
			}
		}
	}
	
	$r->set_event(BEFORE_INSERT, "check_country_data");
	$r->set_event(AFTER_INSERT, "update_country_data");
	$r->set_event(BEFORE_UPDATE, "check_country_data");
	$r->set_event(AFTER_UPDATE, "update_country_data");
	$r->set_event(AFTER_DELETE, "delete_country");
	$r->set_event(AFTER_SELECT, "select_country");
	$r->set_event(AFTER_REQUEST, "country_after_request");
	$r->set_event(BEFORE_SHOW, "country_before_show");

	$r->process();

	if ($sitelist) {
		$sites = array();
		$sql = " SELECT site_id, site_name FROM " . $table_prefix . "sites ";
		$db->query($sql);
		while ($db->next_record())	{
			$site_id   = $db->f("site_id");
			$site_name = $db->f("site_name");
			$sites[$site_id] = $site_name;
			$t->set_var("site_id", $site_id);
			$t->set_var("site_name", $site_name);
			if (in_array($site_id, $selected_sites)) {
				$t->parse("selected_sites", true);
			} else {
				$t->parse("available_sites", true);
			}
		}
	}

	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }
	$tabs = array(
		"general" => array("title" => va_message("ADMIN_GENERAL_MSG")), 
		"postal_code" => array("title" => va_message("ZIP_FIELD")), 
		"units" => array("title" => va_message("MEASUREMENT_UNITS_MSG")), 
		"sites" => array("title" => va_message("ADMIN_SITES_MSG"), "show" => $sitelist),
	);


	parse_tabs($tabs, $tab);

	$t->set_var("sp", htmlspecialchars($sp));
	$t->set_var("sort_ord", htmlspecialchars($sort_ord));
	$t->set_var("sort_dir", htmlspecialchars($sort_dir));
	$t->set_var("page", htmlspecialchars($page));

	$t->pparse("main");

	function check_country_data() {
		global $r, $cs, $db, $table_prefix, $r, $sitelist;
		if (!$sitelist) {
			$r->set_value("sites_all", 1);
		}

		// set country settings parameter
		$country_settings = array();
		foreach ($cs->parameters as $key => $data) {
			if ($cs->get_property_value($key, USE_IN_INSERT)) {
				$country_settings[$key] = $data[CONTROL_VALUE];
			}
		}
		$r->set_value("country_settings", json_encode($country_settings));
	}

	function update_country_data($params) {
		global $r, $db, $table_prefix, $sitelist, $selected_sites;

		$event = isset($params["event"]) ? $params["event"] : "";
		if ($event == AFTER_INSERT) {
			$country_id = $db->last_insert_id();
			$r->set_value("country_id", $country_id);
		}
	
		$country_id = $r->get_value("country_id");
		$country_code = $r->get_value("country_code");
		
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET country_code=" . $db->tosql($country_code, TEXT);
		$sql .= " WHERE country_id=" . $db->tosql($country_id, INTEGER, true, false);
		$db->query($sql);
		
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET delivery_country_code=" . $db->tosql($country_code, TEXT);
		$sql .= " WHERE delivery_country_id=" . $db->tosql($country_id, INTEGER, true, false);
		$db->query($sql);
					
		$country_id = $r->get_value("country_id");
		if ($sitelist) {
			$db->query("DELETE FROM " . $table_prefix . "countries_sites WHERE country_id=" . $db->tosql($country_id, INTEGER));
			for ($st = 0; $st < sizeof($selected_sites); $st++) {
				$site_id = $selected_sites[$st];
				if (strlen($site_id)) {
					$sql  = " INSERT INTO " . $table_prefix . "countries_sites (country_id, site_id) VALUES (";
					$sql .= $db->tosql($country_id, INTEGER) . ", ";
					$sql .= $db->tosql($site_id, INTEGER) . ") ";
					$db->query($sql);
				}
			}
		}
	}

	function delete_country()
	{
		global $r, $db, $table_prefix;
		$country_id = $r->get_value("country_id");
		$db->query("DELETE FROM " . $table_prefix . "countries_sites WHERE country_id=" . $db->tosql($country_id, INTEGER));
	}

	function select_country()
	{
		global $r, $cs, $db, $table_prefix;
		$country_settings = $r->get_value("country_settings");
		if ($country_settings) {
			$country_settings = json_decode($country_settings, true);
			if (is_array($country_settings)) {
				foreach ($country_settings as $param_name => $param_value) {
					if ($cs->parameter_exists($param_name)) {
						$cs->set_value($param_name, $param_value);
					}
				}
			}
		}
	}

	function country_after_request()
	{
		global $cs;
		$cs->get_form_parameters();	
	}

	function country_before_show()
	{
		global $cs;
		$cs->set_form_parameters();
	}
