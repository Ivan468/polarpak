<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_currency.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once("./admin_common.php");

	check_admin_security("static_tables");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_currency.html");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_currencies_href", "admin_currencies.php");
	$t->set_var("admin_currency_href", "admin_currency.php");
	$t->set_var("admin_lookup_tables_href", "admin_lookup_tables.php");
	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");
	$full_image_url = get_setting_value($settings, "full_image_url", 0);
	$site_url_path = get_setting_value($settings, "site_url", "");
	if ($full_image_url){
		$t->set_var("site_url", $site_url_path);					
	} else {
		$t->set_var("site_url", "");					
	}

	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", CURRENCY_TITLE, CONFIRM_DELETE_MSG));

	$r = new VA_Record($table_prefix . "currencies");
	$r->return_page  = "admin_currencies.php";

	$yes_no = 
		array( 
			array(1, YES_MSG), array(0, NO_MSG)
		);

	$r->add_where("currency_id", INTEGER);
	$r->add_checkbox("is_default", INTEGER);
	$r->add_checkbox("recalculate_prices", INTEGER);
	$r->add_checkbox("show_for_user", INTEGER);
	$r->add_checkbox("is_default_show", INTEGER);
	$r->change_property("recalculate_prices", USE_IN_SELECT, false);
	$r->change_property("recalculate_prices", USE_IN_INSERT, false);
	$r->change_property("recalculate_prices", USE_IN_UPDATE, false);

	$r->add_textbox("currency_code", TEXT, CURRENCY_CODE_MSG);
	$r->change_property("currency_code", REQUIRED, true);
	$r->change_property("currency_code", UNIQUE, true);
	$r->change_property("currency_code", MIN_LENGTH, 3);
	$r->change_property("currency_code", MAX_LENGTH, 3);
	$r->add_textbox("currency_value", TEXT);
	$r->add_textbox("currency_title", TEXT, CURRENCY_TITLE_MSG);
	$r->change_property("currency_title", REQUIRED, true);
	$r->add_textbox("currency_image", TEXT, CURRENCY_IMAGE_MSG);
	$r->add_textbox("currency_image_active", TEXT, CURRENCY_IMAGE_ACTIVE_MSG);
	$r->add_textbox("exchange_rate", NUMBER, EXCHANGE_RATE_MSG);
	$r->change_property("exchange_rate", REQUIRED, true);
	$r->change_property("exchange_rate", DEFAULT_VALUE, 1);
	$r->add_textbox("symbol_left", TEXT);
	$r->add_textbox("symbol_right", TEXT);
	$r->add_textbox("decimals_number", INTEGER, NUMBER_OF_DECIMALS_MSG);
	$r->change_property("decimals_number", MIN_VALUE, 0);
	$r->add_textbox("decimal_point", TEXT, DECIMAL_POINT_MSG);
	$r->change_property("decimal_point", MAX_LENGTH, 1);
	$r->add_textbox("thousands_separator", TEXT, THOUSANDS_SEPARATOR_MSG);
	$r->change_property("thousands_separator", MAX_LENGTH, 1);

	// sites list
	$operation = get_param("operation");
	$currency_id = get_param("currency_id");
	$r->add_checkbox("sites_all", INTEGER);
	$r->change_property("sites_all", DEFAULT_VALUE, 1);
	if ($sitelist) {
		$selected_sites = array();
		if (strlen($operation)) {
			$sites = get_param("sites");
			if ($sites) {
				$selected_sites = explode(",", $sites);
			}
		} elseif ($currency_id) {
			$sql  = "SELECT site_id FROM " . $table_prefix . "currencies_sites ";
			$sql .= " WHERE currency_id=" . $db->tosql($currency_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$selected_sites[] = $db->f("site_id");
			}
		}
	}

	$r->events[AFTER_REQUEST] = "set_currency_data";
	$r->events[AFTER_INSERT] = "update_currency_data";
	$r->events[AFTER_UPDATE] = "update_currency_data";
	$r->events[AFTER_DELETE] = "delete_currency_data";
	$r->events[BEFORE_INSERT] = "set_currency_id";
	$r->events[BEFORE_UPDATE] = "check_currency_data";

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
		"general" => array("title" => ADMIN_GENERAL_MSG), 
		"sites" => array("title" => ADMIN_SITES_MSG, "show" => $sitelist),
	);

	parse_admin_tabs($tabs, $tab, 7);

	$t->set_var("date_added_format", join("", $date_edit_format));
	$t->set_var("tab", $tab);
	$t->pparse("main");

	

	function recalculate_rates_old()
	{
		global $r, $db, $table_prefix;
		$exchange_rate = $r->get_value("exchange_rate");
		if ($r->get_value("is_default") == 1) 
		{
			if ($exchange_rate != 1) {
				if ($r->get_value("recalculate_prices") == 1) {
					$sql  = " UPDATE " . $table_prefix . "items SET ";
					$sql .= " buying_price=buying_price*" . $db->tosql($exchange_rate, NUMBER) . ", ";
					$sql .= " sales_price=sales_price*" . $db->tosql($exchange_rate, NUMBER) . ", ";
					$sql .= " trade_sales=trade_sales*" . $db->tosql($exchange_rate, NUMBER) . ", ";
					$sql .= " price=price*" . $db->tosql($exchange_rate, NUMBER) . ", ";
					$sql .= " trade_price=trade_price*" . $db->tosql($exchange_rate, NUMBER) . ", ";
					$sql .= " properties_price=properties_price*" . $db->tosql($exchange_rate, NUMBER) . ", ";
					$sql .= " trade_properties_price=trade_properties_price*" . $db->tosql($exchange_rate, NUMBER) . ", ";
					$sql .= " shipping_cost=shipping_cost*" . $db->tosql($exchange_rate, NUMBER) . ", ";
					$sql .= " shipping_trade_cost=shipping_trade_cost*" . $db->tosql($exchange_rate, NUMBER) . " ";
					$db->query($sql);
		  
					$sql  = " UPDATE " . $table_prefix . "items_properties SET ";
					$sql .= " additional_price=additional_price*" . $db->tosql($exchange_rate, NUMBER) . ", ";
					$sql .= " trade_additional_price=trade_additional_price*" . $db->tosql($exchange_rate, NUMBER);
					$db->query($sql);

					$sql  = " UPDATE " . $table_prefix . "items_properties_values SET ";
					$sql .= " buying_price=buying_price*" . $db->tosql($exchange_rate, NUMBER) . ", ";
					$sql .= " additional_price=additional_price*" . $db->tosql($exchange_rate, NUMBER) . ", ";
					$sql .= " trade_additional_price=trade_additional_price*" . $db->tosql($exchange_rate, NUMBER);
					$db->query($sql);

					$sql  = " UPDATE " . $table_prefix . "items_prices SET ";
					$sql .= " price=price*" . $db->tosql($exchange_rate, NUMBER) . " ";
					$db->query($sql);

					$sql  = " UPDATE " . $table_prefix . "prices SET ";
					$sql .= " price_amount=price_amount*" . $db->tosql($exchange_rate, NUMBER) . " ";
					$db->query($sql);

					$sql  = " UPDATE " . $table_prefix . "shipping_types SET ";
					$sql .= " min_goods_cost=min_goods_cost*" . $db->tosql($exchange_rate, NUMBER) . ", ";
					$sql .= " max_goods_cost=max_goods_cost*" . $db->tosql($exchange_rate, NUMBER) . ", ";
					$sql .= " cost_per_order=cost_per_order*" . $db->tosql($exchange_rate, NUMBER) . ", ";
					$sql .= " cost_per_product=cost_per_product*" . $db->tosql($exchange_rate, NUMBER) . ", ";
					$sql .= " cost_per_weight=cost_per_weight*" . $db->tosql($exchange_rate, NUMBER) . " ";
					$db->query($sql);

					$sql  = " UPDATE " . $table_prefix . "saved_carts SET ";
					$sql .= " cart_total=cart_total*" . $db->tosql($exchange_rate, NUMBER) . " ";
					$db->query($sql);

					$sql  = " UPDATE " . $table_prefix . "saved_items SET ";
					$sql .= " price=price*" . $db->tosql($exchange_rate, NUMBER) . " ";
					$db->query($sql);

					$sql  = " UPDATE " . $table_prefix . "ads_items SET ";
					$sql .= " price=price*" . $db->tosql($exchange_rate, NUMBER) . " ";
					$db->query($sql);

					$sql  = " UPDATE " . $table_prefix . "coupons SET ";
					$sql .= " discount_amount=discount_amount*" . $db->tosql($exchange_rate, NUMBER) . ", ";
					$sql .= " minimum_amount=minimum_amount*" . $db->tosql($exchange_rate, NUMBER) . ", ";
					$sql .= " maximum_amount=maximum_amount*" . $db->tosql($exchange_rate, NUMBER) . " ";
					$sql .= " WHERE discount_type IN (2,4,5) ";
					$db->query($sql);
				}
		  
				$sql  = " UPDATE " . $table_prefix . "currencies SET exchange_rate=exchange_rate/" . $db->tosql($exchange_rate, NUMBER);
				$db->query($sql);
				$r->set_value("exchange_rate", 1);
			}

			$sql  = " UPDATE " . $table_prefix . "currencies SET is_default=0 ";
			$db->query($sql);
			set_session("session_currency", "");
		}
	}

	function check_currency_data()	{
		global $db, $table_prefix, $r;
		global $sitelist, $selected_sites;

		$currency_id = $r->get_value("currency_id");
		$exchange_rate = $r->get_value("exchange_rate");
		$is_default = $r->get_value("is_default");
		$is_default_show = $r->get_value("is_default_show");
		$sites_all = $r->get_value("sites_all");
		$currencies_ids = array();
		// check active sites for current currency
		if (($is_default || $is_default_show) && !$sites_all) {
			$currencies_ids[] = $currency_id;
			$site_ids = array();
			$sql  = " SELECT site_id FROM " . $table_prefix . "currencies_sites ";
			$sql .= " WHERE currency_id=" . $db->tosql($currency_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$site_ids[] = $db->f("site_id");
			}
			if (sizeof($site_ids) > 0)  {
				$sql  = " SELECT c.currency_id FROM (" . $table_prefix . "currencies c ";
				$sql .= " LEFT JOIN " . $table_prefix . "currencies_sites cs ON c.currency_id=cs.currency_id) ";
				$sql .= " WHERE c.sites_all=1 ";
				$sql .= " OR cs.site_id IN (" . $db->tosql($site_ids, INTEGERS_LIST) . ") ";
				$db->query($sql);
				while ($db->next_record()) {
					$currencies_ids[] = $db->f("currency_id");
				}
			}

		}

		if ($is_default == 1) {
			$r->set_value("exchange_rate", 1);
			// update exchange rates
			$sql  = " UPDATE " . $table_prefix . "currencies ";
			$sql .= " SET exchange_rate=exchange_rate/" . $db->tosql($exchange_rate, NUMBER);
			$sql .= " , is_default=0 ";
			if (!$sites_all) {
				$sql .= " WHERE currency_id IN (" . $db->tosql($currencies_ids, INTEGERS_LIST) . ") ";
			}
			$db->query($sql);
			set_session("session_currency", "");
		}

		if ($is_default_show == 1) {
			$sql  = " UPDATE " . $table_prefix . "currencies SET is_default_show=0 ";
			if (!$sites_all) {
				$sql .= " WHERE currency_id IN (" . $db->tosql($currencies_ids, INTEGERS_LIST) . ") ";
			}
			$db->query($sql);
			set_session("session_currency", "");
		}
	}

	function set_currency_id()  {
		global $db, $table_prefix, $r;

		$sql = "SELECT MAX(currency_id) FROM " . $table_prefix . "currencies ";
		$db->query($sql);
		if($db->next_record()) {
			$currency_id= $db->f(0) + 1;
			$r->change_property("currency_id", USE_IN_INSERT, true);
			$r->set_value("currency_id", $currency_id);
		}	

		check_currency_data();
	}

	function update_currency_data()  {
		global $db, $table_prefix, $r;
		global $sitelist, $selected_sites;
					
		$currency_id = $r->get_value("currency_id");
		if ($sitelist) {
			$db->query("DELETE FROM " . $table_prefix . "currencies_sites WHERE currency_id=" . $db->tosql($currency_id, INTEGER));
			for ($st = 0; $st < sizeof($selected_sites); $st++) {
				$site_id = $selected_sites[$st];
				if (strlen($site_id)) {
					$sql  = " INSERT INTO " . $table_prefix . "currencies_sites (currency_id, site_id) VALUES (";
					$sql .= $db->tosql($currency_id, INTEGER) . ", ";
					$sql .= $db->tosql($site_id, INTEGER) . ") ";
					$db->query($sql);
				}
			}
		}

	}

	function delete_currency_data()  {
		global $db, $table_prefix, $r;

		$currency_id = $r->get_value("currency_id");
		$db->query("DELETE FROM " . $table_prefix . "currencies_sites WHERE currency_id=" . $db->tosql($currency_id, INTEGER));
	}
	

	function set_currency_data()  
	{
		global $r, $sitelist;
		if (!$sitelist) {
			$r->set_value("sites_all", 1);
		}
	}


?>