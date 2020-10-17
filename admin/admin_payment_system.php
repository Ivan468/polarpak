<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_payment_system.php                                 ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/editgrid.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once("./admin_common.php");
	
	check_admin_security("payment_systems");

	// check default currency code
	$default_currency_code = get_db_value("SELECT currency_code FROM ".$table_prefix."currencies WHERE is_default=1");

	$current_date = va_time();

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_payment_system.html");
	$t->set_var("current_date", va_date("YYYY-MM-DD, H:mm, WWWW", $current_date));
	$t->set_var("default_currency_code", htmlspecialchars($default_currency_code));


	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$full_image_url = get_setting_value($settings, "full_image_url", 0);
	$site_url_path = get_setting_value($settings, "site_url", "");
	if ($full_image_url){
		$t->set_var("site_url", $site_url_path);					
	} else {
		$t->set_var("site_url", "");					
	}
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", PAYMENT_SYSTEMS_MSG, CONFIRM_DELETE_MSG));

	// set up html form parameters
	$r = new VA_Record($table_prefix . "payment_systems");
	$r->add_where("payment_id", INTEGER);

	$r->add_checkbox("is_active", INTEGER);
	$r->add_checkbox("is_default", INTEGER);
	$r->add_checkbox("is_call_center", INTEGER);
	$r->add_checkbox("allowed_user_edit", INTEGER);

	$r->add_textbox("payment_order", INTEGER, ADMIN_ORDER_MSG);
	$r->change_property("payment_order", REQUIRED, true);
	$r->add_textbox("payment_name", TEXT, PAYMENT_SYSTEM_NAME_MSG);
	$r->change_property("payment_name", REQUIRED, true);
	$r->add_textbox("user_payment_name", TEXT, PAYMENT_NAME_COLUMN);
	$r->add_textbox("order_total_min", NUMBER);
	$r->add_textbox("order_total_max", NUMBER);
	$r->add_textbox("processing_time", INTEGER, PROCESSING_TIME_MSG);

	// fast checkout fields
	$r->add_checkbox("fast_checkout_active", INTEGER, FAST_CHECKOUT_ACTIVE_MSG);
	$r->add_textbox("fast_checkout_image", TEXT, FAST_CHECKOUT_IMAGE_MSG);
	$r->add_textbox("fast_checkout_width", INTEGER, FAST_CHECKOUT_WIDTH_MSG);
	$r->add_textbox("fast_checkout_height", INTEGER, FAST_CHECKOUT_HEIGHT_MSG);
	$r->add_textbox("fast_checkout_alt", TEXT, FAST_CHECKOUT_ALT_MSG);

	//image settings
	$r->add_textbox("image_small", TEXT);
	$r->add_textbox("image_small_alt", TEXT);
	$r->add_textbox("image_large", TEXT);
	$r->add_textbox("image_large_alt", TEXT);

	// processing fee fields
	$r->add_checkbox("processing_tax_free", INTEGER, TAX_FREE_MSG);
	$r->add_textbox("fee_percent", NUMBER, PERCENTAGE_PER_ORDER_AMOUNT_MSG);
	$r->add_textbox("fee_amount", NUMBER, AMOUNT_PER_ORDER_MSG);
	$r->add_textbox("fee_min_amount", NUMBER, MIN_ORDER_COST_MSG);
	$r->add_textbox("fee_max_amount", NUMBER, MAX_ORDER_COST_MSG);

	$recurring_methods = array(
		array(0, RECURRING_NOT_ALLOWED_MSG), array(1, RECURRING_AUTO_CREATE_MSG), array(2, RECURRING_AUTO_BILL_MSG)
	);
	$r->add_radio("recurring_method", INTEGER, $recurring_methods, RECURRING_METHOD_MSG);

	$r->add_textbox("payment_info", TEXT, PAYMENT_INFO_MSG);
	$r->add_textbox("payment_notes", TEXT, PAYMENT_NOTES_MSG);
	$payment_types = array(
		array("DIRECT", PAYMENT_DIRECT), array("REMOTE", PAYMENT_REMOTE), 
	);
	$r->add_radio("payment_type", TEXT, $payment_types, PAYMENT_TYPE_MSG);
	$r->add_textbox("payment_code", TEXT, PAYMENT_CODE_MSG);
	$r->add_textbox("payment_php_lib", TEXT, PAYMENT_LIBRARY_MSG);
	$r->add_textbox("validation_php_lib", TEXT, VALIDATION_LIBRARY_MSG);
	$r->add_textbox("payment_url", TEXT, PAYMENT_URL_MSG);
	$r->change_property("payment_url", USE_SQL_NULL, false);
	$methods = array(array("GET", "GET"), array("POST", "POST"));
	$r->add_radio("submit_method", TEXT, $methods, FORM_SUBMIT_METHOD_MSG);


	// advanced parameters
	$sql = "SELECT status_id, status_name FROM " . $table_prefix . "order_statuses WHERE is_active=1 ORDER BY status_order, status_id";
	$order_statuses = get_db_values($sql, array(array("", "")));

	$failure_actions = array(
		array(0, GO_TO_FINAL_STEP_MSG),
		array(1, REDIRECT_BACK_PAYMENT_PAGE_MSG)
	);

	$r->add_checkbox("is_advanced", INTEGER);
	$r->add_textbox("advanced_url", TEXT, ADVANCED_URL_MSG);
	$r->add_textbox("advanced_php_lib", TEXT, ADVANCED_PHP_LIBRARY_MSG);
	$r->add_select("success_status_id", INTEGER, $order_statuses, SUCCESS_STATUS_MSG);
	$r->add_select("pending_status_id", INTEGER, $order_statuses, PENDING_STATUS_MSG);
	$r->add_select("failure_status_id", INTEGER, $order_statuses, FAILURE_STATUS_MSG);
	$r->add_radio("failure_action", INTEGER, $failure_actions, ON_FAILURE_ACTION_MSG);
	$r->add_textbox("capture_php_lib", TEXT);
	$r->add_textbox("refund_php_lib", TEXT);
	$r->add_textbox("void_php_lib", TEXT);
	$is_advanced = get_param("is_advanced");
	if ($is_advanced) {
		$r->change_property("advanced_url", REQUIRED, true);
		$r->change_property("advanced_php_lib", REQUIRED, true);
		$r->change_property("success_status_id", REQUIRED, true);
		$r->change_property("failure_status_id", REQUIRED, true);
	}

	$r->add_checkbox("non_logged_users", INTEGER);
	$r->add_checkbox("user_types_all", INTEGER);
	$r->add_checkbox("item_types_all", INTEGER);
	$r->add_checkbox("countries_all", INTEGER);
	$r->add_checkbox("currencies_all", INTEGER);
	$r->add_checkbox("sites_all", INTEGER);

	// time activity
	$r->add_textbox("active_start_time", INTEGER);
	$r->add_textbox("active_start_time_show", TEXT, va_constant("ACTIVITY_TIME_MSG").": ".va_constant("START_TIME_MSG"));
	$r->change_property("active_start_time_show", TRIM, true);
	$r->change_property("active_start_time_show", REGEXP_MASK, "/^\d{1,2}:\d{2}$/");
	$r->change_property("active_start_time_show", USE_IN_SELECT, false);
	$r->change_property("active_start_time_show", USE_IN_INSERT, false);
	$r->change_property("active_start_time_show", USE_IN_UPDATE, false);
	$r->add_textbox("active_end_time", INTEGER);
	$r->add_textbox("active_end_time_show", TEXT, va_constant("ACTIVITY_TIME_MSG").": ".va_constant("END_TIME_MSG"));
	$r->change_property("active_end_time_show", TRIM, true);
	$r->change_property("active_end_time_show", REGEXP_MASK, "/^\d{1,2}:\d{2}$/");
	$r->change_property("active_end_time_show", USE_IN_SELECT, false);
	$r->change_property("active_end_time_show", USE_IN_INSERT, false);
	$r->change_property("active_end_time_show", USE_IN_UPDATE, false);
	$r->add_textbox("active_week_days", INTEGER);

	$r->get_form_values();

	$rp = new VA_Record($table_prefix . "payment_parameters", "parameters");
	$rp->add_where("parameter_id", INTEGER);
	$rp->add_hidden("payment_id", INTEGER);
	$rp->change_property("payment_id", USE_IN_INSERT, true);
	$rp->add_textbox("parameter_name", TEXT, PARAMETER_NAME_MSG);
	$rp->change_property("parameter_name", REQUIRED, true);

	$parameter_types = array(
		array("", ""),
		array("CONSTANT", ADMIN_CONSTANT_MSG),
		array("VARIABLE", ADMIN_VARIABLE_MSG)
	);


	$rp->add_select("parameter_type", TEXT, $parameter_types, PARAMETER_TYPE_MSG);
	$rp->change_property("parameter_type", REQUIRED, true);
	$rp->change_property("parameter_type", USE_SQL_NULL, false);

	$rp->add_textbox("parameter_source", TEXT, PARAMETER_SOURCE_MSG);
	$rp->add_checkbox("not_passed", INTEGER, NOT_PASSED_MSG);

	$payment_id = get_param("payment_id");

	$more_parameters = get_param("more_parameters");
	$number_parameters = get_param("number_parameters");

	$eg = new VA_EditGrid($rp, "parameters");
	$eg->get_form_values($number_parameters);

	$operation = get_param("operation");
	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }

	$return_page = "admin_payment_systems.php";

	$selected_user_types = array();
	if (strlen($operation)) {
		$user_types = get_param("user_types");
		if ($user_types) {
			$selected_user_types = explode(",", $user_types);
		}
	} elseif ($payment_id) {
		$sql  = "SELECT user_type_id FROM " . $table_prefix . "payment_user_types ";
		$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$selected_user_types[] = $db->f("user_type_id");
		}
	}

	$selected_item_types = array();
	if (strlen($operation)) {
		$item_types = get_param("item_types");
		if ($item_types) {
			$selected_item_types = explode(",", $item_types);
		}
	} elseif ($payment_id) {
		$sql  = "SELECT item_type_id FROM " . $table_prefix . "payment_item_types ";
		$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$selected_item_types[] = $db->f("item_type_id");
		}
	}

	$selected_countries = array();
	if (strlen($operation)) {
		$countries = get_param("countries");
		if ($countries) {
			$selected_countries = explode(",", $countries);
		}
	} elseif ($payment_id) {
		$sql  = " SELECT country_id FROM " . $table_prefix . "payment_countries ";
		$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$selected_countries[] = $db->f("country_id");
		}
	}

	$selected_currencies = array();
	if (strlen($operation)) {
		$currencies = get_param("currencies");
		if ($currencies) {
			$selected_currencies = explode(",", $currencies);
		}
	} elseif ($payment_id) {
		$sql  = " SELECT currency_id FROM " . $table_prefix . "payment_currencies ";
		$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$selected_currencies[] = $db->f("currency_id");
		}
	}
	
	if ($sitelist) {
		$selected_sites = array();
		if (strlen($operation)) {
			$sites = get_param("sites");
			if ($sites) {
				$selected_sites = explode(",", $sites);
			}
		} elseif ($payment_id) {
			$sql  = "SELECT site_id FROM " . $table_prefix . "payment_systems_sites ";
			$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$selected_sites[] = $db->f("site_id");
			}
		}
	}

	// get activity time parameters 
	$active_start_time_show = $r->get_value("active_start_time_show");
	if (preg_match("/(\d{1,2}):(\d{2})/", $active_start_time_show, $matches)) {
		$active_start_time = $matches[1]*60 + $matches[2];
		$r->set_value("active_start_time", $active_start_time);
	}
	$active_end_time_show = $r->get_value("active_end_time_show");
	if (preg_match("/(\d{1,2}):(\d{2})/", $active_end_time_show, $matches)) {
		$active_end_time = $matches[1]*60 + $matches[2];
		$r->set_value("active_end_time", $active_end_time);
	}
	$week_values = array(
		"1" => 1, "2" => 2, "3" => 4, "4" => 8, "5" => 16, "6" => 32, "7" => 64,
	);
	$active_week_days = 0;
	$all_days = get_param("all_days");
	if ($all_days) {
		$active_week_days = 127;
	} else {
		foreach ($week_values as $day => $day_value) {
			$day_selected = get_param("day_".$day);
			if ($day_selected) { $active_week_days += $day_value; }
		}
	}
	$r->set_value("active_week_days", $active_week_days);


	if (strlen($operation) && !$more_parameters)
	{
		if ($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		}
		elseif ($operation == "delete" && $payment_id)
		{
			$sql  = " DELETE FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type=" . $db->tosql("credit_card_info_" . $payment_id, TEXT);
			$sql .= " OR setting_type=" . $db->tosql("order_final_" . $payment_id, TEXT);
			$sql .= " OR setting_type=" . $db->tosql("recurring_" . $payment_id, TEXT);
			$db->query($sql);
			$db->query("DELETE FROM " . $table_prefix . "payment_user_types WHERE payment_id=" . $db->tosql($payment_id, INTEGER));
			$db->query("DELETE FROM " . $table_prefix . "payment_countries WHERE payment_id=" . $db->tosql($payment_id, INTEGER));
			$db->query("DELETE FROM " . $table_prefix . "payment_currencies WHERE payment_id=" . $db->tosql($payment_id, INTEGER));
			$db->query("DELETE FROM " . $table_prefix . "payment_systems_sites WHERE payment_id=" . $db->tosql($payment_id, INTEGER));
			$db->query("DELETE FROM " . $table_prefix . "payment_parameters WHERE payment_id=" . $db->tosql($payment_id, INTEGER));
			$db->query("DELETE FROM " . $table_prefix . "payment_systems WHERE payment_id=" . $db->tosql($payment_id, INTEGER));

			header("Location: " . $return_page);
			exit;
		}
		$is_valid = $r->validate();
		$is_valid = ($eg->validate() && $is_valid);

		if ($is_valid)
		{
			if (!$sitelist) {
				$r->set_value("sites_all", 1);
			}
			if (strlen($payment_id))
			{
				$r->update_record();
				$eg->set_values("payment_id", $payment_id);
				$eg->update_all($number_parameters);
			}
			else
			{
				$r->insert_record();
				$payment_id = $db->last_insert_id();
				$r->set_value("payment_id", $payment_id);
				$eg->set_values("payment_id", $payment_id);
				$eg->insert_all($number_parameters);
				// redirect to payment details page settings
				$return_page = "admin_credit_card_info.php?payment_id=" . urlencode($payment_id);
			}
			if ($r->get_value("is_default") == 1) {
				$sql = "UPDATE " . $table_prefix . "payment_systems SET is_default=0 WHERE payment_id<>" . $db->tosql($payment_id, INTEGER);
				$db->query($sql);
			}
			// update users types
			$db->query("DELETE FROM " . $table_prefix . "payment_user_types WHERE payment_id=" . $db->tosql($payment_id, INTEGER));
			for ($ut = 0; $ut < sizeof($selected_user_types); $ut++) {
				$type_id = $selected_user_types[$ut];
				if (strlen($type_id)) {
					$sql  = " INSERT INTO " . $table_prefix . "payment_user_types (payment_id, user_type_id) VALUES (";
					$sql .= $db->tosql($payment_id, INTEGER) . ", ";
					$sql .= $db->tosql($type_id, INTEGER) . ") ";
					$db->query($sql);
				}
			}	
			// update item types
			$db->query("DELETE FROM " . $table_prefix . "payment_item_types WHERE payment_id=" . $db->tosql($payment_id, INTEGER));
			for ($it = 0; $it < sizeof($selected_item_types); $it++) {
				$item_type_id = $selected_item_types[$it];
				if (strlen($item_type_id)) {
					$sql  = " INSERT INTO " . $table_prefix . "payment_item_types (payment_id, item_type_id) VALUES (";
					$sql .= $db->tosql($payment_id, INTEGER) . ", ";
					$sql .= $db->tosql($item_type_id, INTEGER) . ") ";
					$db->query($sql);
				}
			}
			// update countries
			$db->query("DELETE FROM " . $table_prefix . "payment_countries WHERE payment_id=" . $db->tosql($payment_id, INTEGER));
			for ($sc = 0; $sc < sizeof($selected_countries); $sc++) {
				$country_id = $selected_countries[$sc];
				if (strlen($country_id)) {
					$sql  = " INSERT INTO " . $table_prefix . "payment_countries (payment_id, country_id) VALUES (";
					$sql .= $db->tosql($payment_id, INTEGER) . ", ";
					$sql .= $db->tosql($country_id, INTEGER) . ") ";
					$db->query($sql);
				}
			}
			// update currenices 
			$db->query("DELETE FROM " . $table_prefix . "payment_currencies WHERE payment_id=" . $db->tosql($payment_id, INTEGER));
			for ($sc = 0; $sc < sizeof($selected_currencies); $sc++) {
				$currency_id = $selected_currencies[$sc];
				if (strlen($currency_id)) {
					$sql  = " INSERT INTO " . $table_prefix . "payment_currencies (payment_id, currency_id) VALUES (";
					$sql .= $db->tosql($payment_id, INTEGER) . ", ";
					$sql .= $db->tosql($currency_id, INTEGER) . ") ";
					$db->query($sql);
				}
			}
			// update sites
			if ($sitelist) {
				$db->query("DELETE FROM " . $table_prefix . "payment_systems_sites WHERE payment_id=" . $db->tosql($payment_id, INTEGER));
				for ($st = 0; $st < sizeof($selected_sites); $st++) {
					$site_id = $selected_sites[$st];
					if (strlen($site_id)) {
						$sql  = " INSERT INTO " . $table_prefix . "payment_systems_sites (payment_id, site_id) VALUES (";
						$sql .= $db->tosql($payment_id, INTEGER) . ", ";
						$sql .= $db->tosql($site_id, INTEGER) . ") ";
						$db->query($sql);
					}
				}
			}

			header("Location: " . $return_page);
			exit;
		}
	}
	elseif (strlen($payment_id) && !$more_parameters)
	{
		$r->get_db_values();
		$eg->set_value("payment_id", $payment_id);
		$eg->change_property("parameter_id", USE_IN_SELECT, true);
		$eg->change_property("parameter_id", USE_IN_WHERE, false);
		$eg->change_property("payment_id", USE_IN_WHERE, true);
		$eg->change_property("payment_id", USE_IN_SELECT, true);
		$number_parameters = $eg->get_db_values();
		if ($number_parameters == 0) {
			$number_parameters = 5;	
		}
		$active_start_time = $r->get_value("active_start_time");
		if ($active_start_time) {
			$active_start_time_show = intval($active_start_time / 60);
			if (($active_start_time % 60) < 10) {
				$active_start_time_show .= ":0".($active_start_time%60);
			} else {
				$active_start_time_show .= ":".($active_start_time%60);
			}
			$r->set_value("active_start_time_show", $active_start_time_show);
		}
		$active_end_time = $r->get_value("active_end_time");
		if ($active_end_time) {
			$active_end_time_show = intval($active_end_time / 60);
			if (($active_end_time % 60) < 10) {
				$active_end_time_show .= ":0".($active_end_time%60);
			} else {
				$active_end_time_show .= ":".($active_end_time%60);
			}
			$r->set_value("active_end_time_show", $active_end_time_show);
		}
	}
	elseif ($more_parameters)
	{
		$number_parameters += 5;
	}
	else
	{
		$sql = " SELECT MAX(payment_order) FROM " . $table_prefix . "payment_systems ";		
		$payment_order = get_db_value($sql);
		$r->set_value("payment_order", $payment_order + 1);
		$r->set_value("submit_method", "GET");
		$r->set_value("non_logged_users", 1);
		$r->set_value("user_types_all", 1);
		$r->set_value("item_types_all", 1);
		$r->set_value("countries_all", 1);
		$r->set_value("currencies_all", 1);
		$r->set_value("sites_all", 1);
		$r->set_value("active_week_days", 127);
		$number_parameters = 5;
	}

	$t->set_var("number_parameters", $number_parameters);

	// set week days
	$week_days = $r->get_value("active_week_days");
	foreach ($week_values as $day => $day_value) {
		if ($week_days & $day_value) {
			$t->set_var("day_".$day, "checked=\"checked\" ");
		} else {
			$t->set_var("day_".$day, "");
		}
		if ($week_days == 127) {
			$t->set_var("day_disabled_".$day, "disabled=\"disabled\" ");
		} else {
			$t->set_var("day_disabled_".$day, "");
		}
	}
	if ($week_days == 127) {
		$t->set_var("all_days", "checked=\"checked\" ");
	} else {
		$t->set_var("all_days", "");
	}

	$eg->set_parameters_all($number_parameters);
	$r->set_parameters();

	$user_types = array();
	$sql = " SELECT type_id, type_name FROM " . $table_prefix . "user_types ";
	$db->query($sql);
	while ($db->next_record())	{
		$type_id = $db->f("type_id");
		$type_name = get_translation($db->f("type_name"));
		$user_types[$type_id] = $type_name;
	}

	foreach($user_types as $type_id => $type_name) {
		$t->set_var("type_id", $type_id);
		$t->set_var("type_name", $type_name);
		if (in_array($type_id, $selected_user_types)) {
			$t->parse("selected_user_types", true);
		} else {
			$t->parse("available_user_types", true);
		}
	}

	$item_types = array();
	$db->query("SELECT * FROM " . $table_prefix . "item_types");
	while ($db->next_record()) {
		$item_type_id =  $db->f("item_type_id");
		$item_type_name =  get_translation($db->f("item_type_name"));
		$item_types[$item_type_id] = $item_type_name;
	}
	foreach($item_types as $item_type_id => $item_type_name) {
		$t->set_var("item_type_id", $item_type_id);
		$t->set_var("item_type_name", $item_type_name);
		if (in_array($item_type_id, $selected_item_types)) {
			$t->parse("selected_item_types", true);
		} else {
			$t->parse("available_item_types", true);
		}
	}

	$countries = array();
	$sql  = " SELECT * FROM " . $table_prefix . "countries ";
	$sql .= " ORDER BY country_code, country_name ";
	$db->query($sql);
	while ($db->next_record()) {
		$country_id =  $db->f("country_id");
		$country_name =  get_translation($db->f("country_name"));
		$countries[$country_id] = $country_name;
	}
	foreach($countries as $country_id => $country_name) {
		$t->set_var("country_id", $country_id);
		$t->set_var("country_name", $country_name);
		if (in_array($country_id, $selected_countries)) {
			$t->parse("selected_countries", true);
		} else {
			$t->parse("available_countries", true);
		}
	}
	$currencies = array();
	$sql  = " SELECT * FROM " . $table_prefix . "currencies ";
	$sql .= " ORDER BY is_default DESC, currency_title ";
	$db->query($sql);
	while ($db->next_record()) {
		$currency_id =  $db->f("currency_id");
		$currency_title =  get_translation($db->f("currency_title"));
		$currencies[$currency_id] = $currency_title;
	}
	foreach($currencies as $currency_id => $currency_title) {
		$t->set_var("currency_id", $currency_id);
		$t->set_var("currency_title", $currency_title);
		if (in_array($currency_id, $selected_currencies)) {
			$t->parse("selected_currencies", true);
		} else {
			$t->parse("available_currencies", true);
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

	if (strlen($payment_id))	{
		$t->set_var("save_button", UPDATE_BUTTON);
		$t->parse("delete", false);
	} else {
		$t->set_var("save_button", ADD_BUTTON);
		$t->set_var("delete", "");
	}

	// set styles for tabs
	$tabs = array(
		"general" => array("title" => ADMIN_GENERAL_MSG), 
		"images" => array("title" => IMAGES_MSG), 
		"fee" => array("title" => FEE_SETTINGS_MSG), 
		"fast_checkout" => array("title" => FAST_CHECKOUT_MSG), 
		"activity" => array("title" => ACTIVITY_TIME_MSG), 
		"user_types" => array("title" => USERS_TYPES_MSG), 
		"sites" => array("title" => ADMIN_SITES_MSG, "show" => $sitelist),
		"item_types" => array("title" => PRODUCT_TYPES_MSG),
		"countries" => array("title" => COUNTRIES_MSG),
		"currencies" => array("title" => CURRENCIES_MSG),
	);

	parse_admin_tabs($tabs, $tab);

	if ($sitelist) {
		$t->parse("sitelist");
	}

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_payment_systems_href", "admin_payment_systems.php");
	$t->set_var("admin_payment_system_href",  "admin_payment_system.php");
	$t->set_var("admin_payment_help_href",    "admin_payment_help.php");
	$t->set_var("admin_order_final_href",     "admin_order_final.php");
	$t->set_var("admin_export_payment_system_href",     "admin_export_payment_system.php?payment_id=".$payment_id);
	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");

	$t->pparse("main");

?>