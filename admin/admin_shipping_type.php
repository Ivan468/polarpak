<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_shipping_type.php                                  ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/tabs_functions.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("shipping_methods");

	// check for new fields
	$ignore_items_shipping_cost = false; $shipping_parameters_field = false; $min_discounted_cost_field = false; $max_discounted_cost_field = false;
	$fields = $db->get_fields($table_prefix."shipping_types");
	foreach ($fields as $id => $field_info) {
		if ($field_info["name"] == "ignore_items_shipping_cost") {
			$ignore_items_shipping_cost = true;
		} else if ($field_info["name"] == "shipping_parameters") {
			$shipping_parameters_field = true;
		} else if ($field_info["name"] == "min_discounted_cost") {
			$min_discounted_cost_field = true;
		} else if ($field_info["name"] == "max_discounted_cost") {
			$max_discounted_cost_field = true;
		}
	}
	if (!$ignore_items_shipping_cost) {
		$sql_types = array(
			"mysql"   => "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN ignore_items_shipping_cost TINYINT DEFAULT '0'",
			"postgre" => "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN ignore_items_shipping_cost SMALLINT DEFAULT '0'",
			"access"  => "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN ignore_items_shipping_cost BYTE ",
		);
		$sql = $sql_types[$db_type];
		$db->query($sql);
	}
	if (!$shipping_parameters_field) {
		if ($db->DBType == "mysql") {
			$sql = "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN shipping_parameters TEXT";
		} else if ($db->DBType == "access") {
			$sql = "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN shipping_parameters LONGTEXT";
		} else {
			$sql = "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN shipping_parameters TEXT";
		}                                                                          
		$db->query($sql);
	}
	if (!$max_discounted_cost_field) {
		$sql_types = array(
			"mysql"  => "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN max_discounted_cost DOUBLE(16,2) AFTER max_goods_cost",
			"postgre"=> "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN max_discounted_cost FLOAT4 ",
			"access" => "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN max_discounted_cost FLOAT "
		);
		$sql = $sql_types[$db_type];
		$db->query($sql);
	}
	if (!$min_discounted_cost_field) {
		$sql_types = array(
			"mysql"  => "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN min_discounted_cost DOUBLE(16,2) AFTER max_goods_cost ",
			"postgre"=> "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN min_discounted_cost FLOAT4 ",
			"access" => "ALTER TABLE " . $table_prefix . "shipping_types ADD COLUMN min_discounted_cost FLOAT "
		);
		$sql = $sql_types[$db_type];
		$db->query($sql);
	}
	// end fields check
		
	$shipping_module_id = get_param("shipping_module_id");
	$shipping_type_id = get_param("shipping_type_id");
	$weight_measure = get_translation(get_setting_value($settings, "weight_measure", ""));

	if ($shipping_module_id) {
		$sql  = " SELECT shipping_module_id, shipping_module_name ";
		$sql .= " FROM " . $table_prefix . "shipping_modules WHERE shipping_module_id=" . $db->tosql($shipping_module_id, INTEGER);
	} else {
		$sql  = " SELECT sm.shipping_module_id, sm.shipping_module_name ";
		$sql .= " FROM (" . $table_prefix . "shipping_modules sm ";
		$sql .= " INNER JOIN " . $table_prefix . "shipping_types st ON sm.shipping_module_id=st.shipping_module_id) ";
		$sql .= " WHERE st.shipping_type_id=" . $db->tosql($shipping_type_id, INTEGER);
	}
	$db->query($sql);
	if ($db->next_record()) {
		$shipping_module_id = $db->f("shipping_module_id");
		$shipping_module_name = $db->f("shipping_module_name");
	} else {
		header ("Location: admin_shipping_modules.php");
		exit;
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_shipping_type.html");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", SHIPPING_TYPE_MSG, CONFIRM_DELETE_MSG));

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_lookup_tables_href",    "admin_lookup_tables.php");
	$t->set_var("admin_shipping_types_href",   "admin_shipping_types.php");
	$t->set_var("admin_shipping_type_href",    "admin_shipping_type.php");
	$t->set_var("admin_shipping_modules_href", "admin_shipping_modules.php");
	$t->set_var("admin_shipping_module_href",  "admin_shipping_module.php");
	$t->set_var("shipping_module_id",          $shipping_module_id);
	$t->set_var("shipping_module_name",        $shipping_module_name);
	$t->set_var("weight_measure",              $weight_measure);


	$postal_match_types = array(
		array(0, "Use for any Zip/Postal Codes"),
		array(1, "Use only for Zip/Postal Codes listed below"),
		array(2, "Use for any Zip/Postal Codes except the ones listed below"),
	);

	$r = new VA_Record($table_prefix . "shipping_types");
	$r->return_page = "admin_shipping_types.php";

	$r->add_where("shipping_type_id", INTEGER);
	$r->add_hidden("shipping_module_id", INTEGER);
	$r->change_property("shipping_module_id", USE_IN_INSERT, true);
	$r->change_property("shipping_module_id", USE_IN_SELECT, true);
	$r->add_checkbox("is_active", INTEGER);
	$r->change_property("is_active", DEFAULT_VALUE, 1);
	$r->add_textbox("shipping_order", INTEGER, SHIPPING_ORDER_MSG);
	$r->change_property("shipping_order", REQUIRED, true);
	$r->add_textbox("shipping_time", INTEGER, SHIPPING_TIME_MSG);
	$r->add_textbox("shipping_type_code", TEXT, SHIPPING_CODE_MSG);
	$r->add_textbox("shipping_type_desc", TEXT, SHIPPING_DESCRIPTION_MSG);
	$r->change_property("shipping_type_desc", REQUIRED, true);
	$r->add_textbox("shipping_parameters", TEXT);
	$r->add_textbox("admin_order_class", TEXT);
	$r->add_textbox("user_order_class", TEXT);

	$r->add_textbox("min_goods_cost", NUMBER, GOODS_TOTAL_MSG." (".MINIMUM_MSG.")");
	$r->add_textbox("max_goods_cost", NUMBER, GOODS_TOTAL_MSG." (".MAXIMUM_MSG.")");
	$r->add_textbox("min_discounted_cost", NUMBER, GOODS_WITH_DISCOUNT_MSG." (".MINIMUM_MSG.")");
	$r->add_textbox("max_discounted_cost", NUMBER, GOODS_WITH_DISCOUNT_MSG." (".MAXIMUM_MSG.")");

	$r->add_textbox("min_weight", NUMBER, MIN_WEIGHT_MSG);
	$r->add_textbox("max_weight", NUMBER, MAX_WEIGHT_MSG);
	$r->add_textbox("min_quantity", INTEGER, MINIMUM_ITEMS_QTY_MSG.USE_MSG);
	$r->add_textbox("max_quantity", INTEGER, MAXIMUM_ITEMS_QTY_MSG);
	$r->add_textbox("tare_weight", NUMBER, TARE_WEIGHT_MSG);
	$r->add_textbox("cost_per_order", NUMBER);
	$r->add_textbox("cost_per_product", NUMBER);
	$r->add_textbox("cost_per_weight", NUMBER);
	$r->add_checkbox("ignore_items_shipping_cost", INTEGER);
	$r->add_checkbox("is_taxable", INTEGER);
	$r->change_property("is_taxable", DEFAULT_VALUE, 1);
	$r->add_checkbox("guest_access", INTEGER);
	$r->change_property("guest_access", DEFAULT_VALUE, 1);
	$r->add_checkbox("user_types_all", INTEGER);
	$r->change_property("user_types_all", DEFAULT_VALUE, 1);
	$r->add_radio("postal_match_type", INTEGER, $postal_match_types);
	$r->change_property("postal_match_type", DEFAULT_VALUE, 0);
	$r->add_textbox("postal_codes", TEXT);
	$r->change_property("postal_codes", TRIM, true);
	$r->change_property("postal_codes", BEFORE_SHOW, "postal_codes_show");
	//image settings
	$r->add_textbox("image_small", TEXT);
	$r->add_textbox("image_small_alt", TEXT);
	$r->add_textbox("image_large", TEXT);
	$r->add_textbox("image_large_alt", TEXT);

	$r->events[BEFORE_INSERT] = "set_shipping_type_id";
	$r->events[AFTER_INSERT] = "update_shipping_data";
	$r->events[AFTER_UPDATE] = "update_shipping_data";
	$r->events[AFTER_DELETE] = "delete_shipping_data";
	$r->events[AFTER_REQUEST] = "set_shipping_data";
	$r->set_event(BEFORE_DEFAULT, "set_shipping_order");

	$r->add_hidden("page", INTEGER);

	$operation = get_param("operation");
	$shipping_type_id = get_param("shipping_type_id");
	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }

	$selected_user_types = array();
	if (strlen($operation)) {
		$tab = "general";
		$user_types = get_param("user_types");
		if ($user_types) {
			$selected_user_types = explode(",", $user_types);
		}
	} elseif (strlen($shipping_type_id)) {
		$sql  = " SELECT user_type_id FROM " . $table_prefix . "shipping_types_users ";
		$sql .= " WHERE shipping_type_id=" . $db->tosql($shipping_type_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$selected_user_types[] = $db->f("user_type_id");
		}
	}
	
	// sites list
	$r->add_checkbox("sites_all", INTEGER);
	if ($sitelist) {
		$selected_sites = array();
		if (strlen($operation)) {
			$sites = get_param("sites");
			if ($sites) {
				$selected_sites = explode(",", $sites);
			}
		} elseif ($shipping_type_id) {
			$sql  = "SELECT site_id FROM " . $table_prefix . "shipping_types_sites ";
			$sql .= " WHERE shipping_type_id=" . $db->tosql($shipping_type_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$selected_sites[] = $db->f("site_id");
			}
		}
	}
	
	// states list
	$r->add_checkbox("states_all", INTEGER);
	$selected_states = array();
	if (strlen($operation)) {
		$states = get_param("states");
		if ($states) {
			$selected_states = explode(",",$states);
		}
	} elseif ($shipping_type_id) {
		$sql  = "SELECT state_id FROM " . $table_prefix . "shipping_types_states ";
		$sql .= " WHERE shipping_type_id=" . $db->tosql($shipping_type_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$selected_states[] = $db->f("state_id");
		}
	}
	
	// countries list
	$r->add_checkbox("countries_all", INTEGER);
	$selected_countries = array();
	if (strlen($operation)) {
		$countries = get_param("countries");
		if ($countries) {
			$selected_countries = explode(",", $countries);
		}
	} elseif ($shipping_type_id) {
		$sql  = "SELECT country_id FROM " . $table_prefix . "shipping_types_countries ";
		$sql .= " WHERE shipping_type_id=" . $db->tosql($shipping_type_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$selected_countries[] = $db->f("country_id");
		}
	}	
	if (strlen($operation) == 0) {
		$r->set_value("sites_all", 1);
		$r->set_value("countries_all", 1);
		$r->set_value("states_all", 1);
	}

	$r->process();


	// show user types
	$user_types = array();
	$sql = " SELECT type_id, type_name FROM " . $table_prefix . "user_types ";
	$db->query($sql);
	while ($db->next_record())	{
		$type_id = $db->f("type_id");
		$type_name = get_translation($db->f("type_name"));
		$user_types[$type_id] = $type_name;
	}

	foreach ($user_types as $type_id => $type_name) {
		$t->set_var("type_id", $type_id);
		$t->set_var("type_name", $type_name);
		if (in_array($type_id, $selected_user_types)) {
			$t->parse("selected_user_types", true);
		} else {
			$t->parse("available_user_types", true);
		}
	}
	
	// states list	
	$states = array();
	$sql = " SELECT state_id, state_name FROM " . $table_prefix . "states  ORDER BY state_name ";
	$db->query($sql);
	while ($db->next_record()) {
		$state_id   = $db->f("state_id");
		$state_name = get_translation($db->f("state_name"));
		$states[$state_id] = $state_name;
		$t->set_var("state_id", $state_id);
		$t->set_var("state_name", $state_name);
		if (in_array($state_id, $selected_states)) {
			$t->parse("selected_states", true);
		} else {
			$t->parse("available_states", true);
		}
	}
	
	// countries list	
	$countries = array();
	$sql = " SELECT country_id, country_name FROM " . $table_prefix . "countries ORDER BY country_order, country_name ";
	$db->query($sql);
	while ($db->next_record())	{
		$country_id   = $db->f("country_id");
		$country_name = get_translation($db->f("country_name"));
		$countries[$country_id] = $country_name;
		$t->set_var("country_id", $country_id);
		$t->set_var("country_name", $country_name);
		if (in_array($country_id, $selected_countries)) {
			$t->parse("selected_countries", true);
		} else {
			$t->parse("available_countries", true);
		}
	}
	
	
	if ($sitelist) {
		$sites = array();
		$sql = " SELECT site_id, site_name FROM " . $table_prefix . "sites ";
		$db->query($sql);
		while ($db->next_record())	{
			$site_id   = $db->f("site_id");
			$site_name = get_translation($db->f("site_name"));
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

	$tabs = array(
		"general" => array("title" => ADMIN_GENERAL_MSG), 
		"images" => array("title" => IMAGES_MSG), 
		"countries" => array("title" => COUNTRIES_MSG), 
		"states" => array("title" => STATES_MSG), 
		"postcodes" => array("title" => ZIP_FIELD), 
		"user_types" => array("title" => USERS_TYPES_MSG),
		"sites" => array("title" => SITES_MSG, "show" => $sitelist),
	);
	parse_tabs($tabs, $tab);

	if ($sitelist) {
		$t->parse("sitelist");
	}

	$t->pparse("main");

	function set_shipping_type_id()
	{
		global $db, $table_prefix, $r;
		$sql = "SELECT MAX(shipping_type_id) FROM " . $table_prefix . "shipping_types";
		$db->query($sql);
		if ($db->next_record()) {
			$shipping_type_id = $db->f(0) + 1;
			$r->change_property("shipping_type_id", USE_IN_INSERT, true);
			$r->set_value("shipping_type_id", $shipping_type_id);
		}	
	}

	function set_shipping_order()
	{
		global $db, $table_prefix, $r, $shipping_module_id;
		$sql  = " SELECT MAX(shipping_order) FROM " . $table_prefix . "shipping_types";
		$sql .= " WHERE shipping_module_id=" . $db->tosql($shipping_module_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$shipping_order = intval($db->f(0)) + 1;
			$r->change_property("shipping_order", DEFAULT_VALUE, $shipping_order);
		}	
	}

	function update_shipping_data()
	{
		global $db, $table_prefix, $r, $selected_user_types;
		global $sitelist, $selected_sites, $selected_states, $selected_countries;

		$shipping_type_id = $r->get_value("shipping_type_id");
		
		$db->query("DELETE FROM " . $table_prefix . "shipping_types_countries WHERE shipping_type_id=" . $db->tosql($shipping_type_id, INTEGER));


		for ($cn = 0; $cn < sizeof($selected_countries); $cn++) {
			$db->query("INSERT INTO " . $table_prefix . "shipping_types_countries (shipping_type_id, country_id) VALUES (" . $db->tosql($shipping_type_id, INTEGER) . "," . $db->tosql($selected_countries[$cn], TEXT) . ")");
		}
		
		$db->query("DELETE FROM " . $table_prefix . "shipping_types_states WHERE shipping_type_id=" . $db->tosql($shipping_type_id, INTEGER));
		for ($st = 0; $st < sizeof($selected_states); $st++) {
			$state_id = $selected_states[$st];
			if (strlen($state_id)) {
				$sql  = " INSERT INTO " . $table_prefix . "shipping_types_states (shipping_type_id, state_id) VALUES (";
				$sql .= $db->tosql($shipping_type_id, INTEGER) . ", ";
				$sql .= $db->tosql($state_id, INTEGER) . ") ";
				$db->query($sql);
			}
		}
		
		$db->query("DELETE FROM " . $table_prefix . "shipping_types_users WHERE shipping_type_id=" . $db->tosql($shipping_type_id, INTEGER));
		for ($ut = 0; $ut < sizeof($selected_user_types); $ut++) {
			$type_id = $selected_user_types[$ut];
			if (strlen($type_id)) {
				$sql  = " INSERT INTO " . $table_prefix . "shipping_types_users (shipping_type_id, user_type_id) VALUES (";
				$sql .= $db->tosql($shipping_type_id, INTEGER) . ", ";
				$sql .= $db->tosql($type_id, INTEGER) . ") ";
				$db->query($sql);
			}
		}
					
		if ($sitelist) {
			$db->query("DELETE FROM " . $table_prefix . "shipping_types_sites WHERE shipping_type_id=" . $db->tosql($shipping_type_id, INTEGER));
			for ($st = 0; $st < sizeof($selected_sites); $st++) {
				$site_id = $selected_sites[$st];
				if (strlen($site_id)) {
					$sql  = " INSERT INTO " . $table_prefix . "shipping_types_sites (shipping_type_id, site_id) VALUES (";
					$sql .= $db->tosql($shipping_type_id, INTEGER) . ", ";
					$sql .= $db->tosql($site_id, INTEGER) . ") ";
					$db->query($sql);
				}
			}
		}

	}

	function delete_shipping_data()
	{
		global $db, $table_prefix, $r;
		$shipping_type_id = $r->get_value("shipping_type_id");
		$db->query("DELETE FROM " . $table_prefix . "shipping_types_countries WHERE shipping_type_id=" . $db->tosql($shipping_type_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "shipping_types_users WHERE shipping_type_id=" . $db->tosql($shipping_type_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "shipping_types_sites WHERE shipping_type_id=" . $db->tosql($shipping_type_id, INTEGER));
		$db->query("DELETE FROM " . $table_prefix . "shipping_types_states WHERE shipping_type_id=" . $db->tosql($shipping_type_id, INTEGER));
	}

	function set_shipping_data()  
	{
		global $r, $sitelist;
		if (!$sitelist) {
			$r->set_value("sites_all", 1);
		}
	}

	function postal_codes_show()
	{
		global $r, $t;
		$postal_match_type = $r->get_value("postal_match_type");
		if (strval($postal_match_type) == strval("0")) {
			$t->set_var("postal_codes_readonly", " readonly=\"readonly\" ");
		} else {
			$t->set_var("postal_codes_readonly", "");
		}
	}

?>