<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_coupon.php                                         ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	// coupon types
	// 1 - order percentage discount
	// 2 - order fixed amount discount
	// 3 - product percentage discount
	// 4 - product fixed amout discount
	// 5 - gift voucher
	// 6 - product percentage sales discount
	// 7 - product fixed amout sales discount
	// 8 - personal user voucher


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/profile_functions.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");

	$rp = get_param("rp");
	$operation   = get_param("operation");
	$coupon_id   = get_param("coupon_id");
	
	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }
	$is_record_controls = false; // global variable to prevent double call of function set_record_controls
	
	$order_id = get_param("order_id");

	if ($order_id > 0) {
		check_admin_security("order_vouchers");
	} else {
		check_admin_security("coupons");
	}
	
	$s = get_param("s");
	$s_a = get_param("s_a");
	$discount_type = get_param("discount_type");
	$date_format_msg = str_replace("{date_format}", join("", $date_edit_format), DATE_FORMAT_MSG);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_coupon.html");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("date_format_msg", $date_format_msg);
	$t->set_var("date_edit_format", join("", $date_edit_format));
	$t->set_var("admin_coupon_href", "admin_coupon.php");
	$t->set_var("admin_user_href", "admin_user.php");
	$t->set_var("admin_users_select_href", "admin_users_select.php");
	$t->set_var("admin_user_types_select_href", "admin_user_types_select.php");
	$t->set_var("admin_item_types_select_href", "admin_item_types_select.php");
	$t->set_var("admin_category_select_href", "admin_category_select.php");
	$t->set_var("admin_shippings_select_href", "admin_shippings_select.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", COUPON_MSG, CONFIRM_DELETE_MSG));
	$t->set_var("items_all_untick_msg", htmlspecialchars(UNTICK_APPLY_ALL_PRODUCTS_MSG));

	$items_rules = array(
		array(1, ONLY_TO_PRODUCTS_MSG),
		array(2, ONLY_TO_SUBCOMPONENTS_MSG),
		array(3, BOTH_TO_PRODUCTS_AND_SUBS_MSG),
	);

	$friends_discount_types = array(
		array(0, NO_FRIENDS_DISCOUNT_MSG),
		array(1, INVITERS_DISCOUNT_MSG),
		array(2, INVITED_PARTY_DISCOUNT_MSG),
	);

	$cart_items_options = array(
		array(1, ALL_CART_ITEMS_MSG),
		array(0, SELECTED_CART_ITEMS_MSG),
		array(2, EXCEPT_CART_ITEMS_MSG),
	);

	$past_items_options = array(
		array(1, ANY_PAST_PRODUCTS_MSG),
		array(2, ALL_SELECTED_PAST_PRODUCTS_MSG),
		array(3, ANY_SELECTED_PAST_PRODUCTS_MSG),
	);

	$periods =
		array(
			array("", ""), array(1, DAY_MSG), array(2, WEEK_MSG), array(3, MONTH_MSG), array(4, YEAR_MSG)
		);

	// record for coupons events
	$ce = new VA_Record($table_prefix . "coupons_events");
	$ce->add_textbox("coupon_id", INTEGER);
	$ce->add_textbox("order_id", INTEGER);
	$ce->add_textbox("payment_id", INTEGER);
	$ce->add_textbox("transaction_id", TEXT);

	$ce->add_textbox("admin_id", INTEGER);
	$ce->add_textbox("user_id", INTEGER);
	$ce->add_textbox("from_user_id", INTEGER);
	$ce->add_textbox("to_user_id", INTEGER);
	$ce->add_textbox("event_date", DATETIME);
	$ce->add_textbox("event_type", TEXT);
	$ce->add_textbox("remote_ip", TEXT);
	$ce->add_textbox("coupon_amount", NUMBER);

	$r = new VA_Record($table_prefix . "coupons");
	$r->return_page = "admin_coupons.php";
	$r->add_hidden("rp", TEXT);
	$r->add_hidden("s_n", TEXT);
	$r->add_hidden("s_a", TEXT);
	$r->add_hidden("s_dt", TEXT);
	$r->add_hidden("page", INTEGER);
	$r->add_hidden("sort_ord", TEXT);
	$r->add_hidden("sort_dir", TEXT);
	$r->add_hidden("order_id", TEXT);
	if ($order_id > 0 && $rp == "order") {
		$r->return_page = "admin_order_vouchers.php";
	}

	$yes_no = 
		array( 
			array(1, YES_MSG), array(0, NO_MSG)
		);

	$r->add_where("coupon_id", INTEGER);
	$r->add_textbox("order_id", INTEGER);
	$r->change_property("order_id", DEFAULT_VALUE, $order_id);
	$r->change_property("order_id", USE_IN_UPDATE, false);
	$r->add_textbox("order_item_id", INTEGER);
	$r->change_property("order_item_id", USE_IN_UPDATE, false);

	$r->add_radio("is_active", INTEGER, $yes_no);
	$r->change_property("is_active", REQUIRED, true);
	$r->change_property("is_active", DEFAULT_VALUE, 1);
	$r->add_radio("is_auto_apply", INTEGER, $yes_no); // new
	$r->change_property("is_auto_apply", REQUIRED, true);
	$r->change_property("is_auto_apply", DEFAULT_VALUE, 0);
	$r->add_textbox("apply_order", INTEGER); 
	$r->change_property("apply_order", REQUIRED, true);
	$r->change_property("apply_order", DEFAULT_VALUE, 1);

	$r->add_textbox("owner_user_id", TEXT, va_constant("OWNER_MSG"));
	$r->change_property("owner_user_id", COLUMN_NAME, "user_id");
	$r->add_hidden("prev_owner_user_id", NUMBER);

	$r->add_textbox("coupon_code", TEXT, COUPON_CODE_MSG);
	$r->change_property("coupon_code", REQUIRED, true);
	$r->change_property("coupon_code", UNIQUE, true);
	$r->change_property("coupon_code", TRIM, true);
	$r->change_property("coupon_code", MIN_LENGTH, 3);
	$r->change_property("coupon_code", MAX_LENGTH, 64);
	$r->change_property("coupon_code", DEFAULT_VALUE, strtoupper(substr(md5(va_timestamp()), 0, 8)));
	$r->add_textbox("coupon_title", TEXT, COUPON_TITLE_MSG);
	$r->change_property("coupon_title", REQUIRED, true);
	$r->add_radio("discount_type", INTEGER, "");
	$r->change_property("discount_type", REQUIRED, true);
	$r->change_property("discount_type", DEFAULT_VALUE, $discount_type);
	$r->add_textbox("discount_type_text", INTEGER);
	$r->change_property("discount_type_text", COLUMN_NAME, "discount_type");
	$r->change_property("discount_type_text", CONTROL_NAME, "discount_type");
	$r->change_property("discount_type_text", DEFAULT_VALUE, $discount_type);

	$r->add_textbox("discount_quantity", INTEGER, DISCOUNT_MULTIPLE_MSG); // new
	$r->change_property("discount_quantity", DEFAULT_VALUE, 1);
	$r->add_textbox("discount_amount", NUMBER, DISCOUNT_AMOUNT_MSG);
	$r->change_property("discount_amount", REQUIRED, true);
	//$r->change_property("discount_amount", DEFAULT_VALUE, 0);
	$r->add_hidden("prev_discount_amount", NUMBER);

	$r->add_checkbox("coupon_tax_free", NUMBER);
	$r->add_checkbox("order_tax_free", NUMBER);
	$r->add_textbox("order_min_goods_cost", FLOAT, ORDER_MIN_PRODUCTS_COST_FIELD);
	$r->add_textbox("order_max_goods_cost", FLOAT, ORDER_MAX_PRODUCTS_COST_FIELD);
	$r->add_textbox("order_min_weight", FLOAT, ORDER_MIN_WEIGHT_FIELD);
	$r->add_textbox("order_max_weight", FLOAT, ORDER_MAX_WEIGHT_FIELD);

	// free postage fields
	$r->add_checkbox("free_postage", NUMBER);
	$r->add_checkbox("free_postage_all", INTEGER);
	$r->change_property("free_postage_all", DEFAULT_VALUE, 1);
	$r->add_textbox("free_postage_ids", TEXT);

	$r->add_textbox("start_date", DATETIME, START_DATE_MSG);
	$r->change_property("start_date", VALUE_MASK, $date_edit_format);
	$r->add_textbox("expiry_date", DATETIME, ADMIN_EXPIRY_DATE_MSG);
	$r->change_property("expiry_date", VALUE_MASK, $date_edit_format);
	if ($discount_type != 5 && $discount_type != 8) {
		$r->change_property("expiry_date", DEFAULT_VALUE, va_time(va_timestamp() + (60*60*24*366)));
	}

	$r->add_textbox("users_use_limit", INTEGER, USERS_USE_LIMIT_MSG);
	$r->add_textbox("quantity_limit", INTEGER, TIMES_COUPON_CAN_BE_USED);
	$r->change_property("quantity_limit", DEFAULT_VALUE, 1);
	$r->add_textbox("coupon_uses", INTEGER);
	$r->change_property("coupon_uses", DEFAULT_VALUE, 0);

	$r->add_textbox("min_quantity", NUMBER, MINIMUM_ITEMS_QTY_MSG); 
	$r->add_textbox("max_quantity", NUMBER, MAXIMUM_ITEMS_QTY_MSG); 
	$r->add_textbox("minimum_amount", NUMBER, MINIMUM_PRICE_OF_PRODUCT_MSG);
	$r->add_textbox("maximum_amount", NUMBER, MAXIMUM_PRICE_OF_PRODUCT_MSG); 

	$r->add_textbox("min_cart_quantity", NUMBER, MIN_CART_QTY_MSG); 
	$r->add_textbox("max_cart_quantity", NUMBER, MAX_CART_QTY_MSG); 
	$r->add_textbox("min_cart_cost", NUMBER, MIN_CART_COST_MSG);
	$r->add_textbox("max_cart_cost", NUMBER, MAX_CART_COST_MSG); 

	$r->add_checkbox("is_exclusive", NUMBER);
	$r->change_property("is_exclusive", DEFAULT_VALUE, 1);


	// products fields
	$r->add_checkbox("items_all", INTEGER);
	$r->change_property("items_all", DEFAULT_VALUE, 1);
	$r->add_radio("items_rule", INTEGER, $items_rules); 
	$r->change_property("items_rule", DEFAULT_VALUE, 1);
	$r->add_textbox("items_ids", TEXT);
	$r->add_textbox("items_types_ids", TEXT);
	$r->add_textbox("items_categories_ids", TEXT);

	// cart products fields
	$r->add_radio("cart_items_all", INTEGER, $cart_items_options); 
	$r->change_property("cart_items_all", DEFAULT_VALUE, 1); 
	$r->add_textbox("cart_items_ids", TEXT); 
	$r->add_textbox("cart_items_types_ids", TEXT);

	// past orders settings 
	$r->add_select("orders_period", INTEGER, $periods, ORDERS_PERIOD_MSG);
	$r->add_textbox("orders_interval", INTEGER, ORDERS_INTERVAL_MSG);
	$r->add_textbox("orders_min_goods", NUMBER, PAST_ORDERS_MSG.": ".GOODS_TOTAL_MSG." (".MINIMUM_MSG.")");
	$r->add_textbox("orders_max_goods", NUMBER, PAST_ORDERS_MSG.": ".GOODS_TOTAL_MSG." (".MAXIMUM_MSG.")");
	$r->add_textbox("orders_min_quantity", NUMBER, PAST_ORDERS_MSG.": ".MINIMUM_ITEMS_QTY_MSG); 
	$r->add_textbox("orders_max_quantity", NUMBER, PAST_ORDERS_MSG.": ".MAXIMUM_ITEMS_QTY_MSG); 
	$r->add_hidden("orders_restrictions", TEXT);

	$r->add_radio("orders_items_type", INTEGER, $past_items_options); 
	$r->change_property("orders_items_type", DEFAULT_VALUE, 1); 
	$r->add_textbox("orders_items_ids", TEXT); 
	$r->add_textbox("orders_types_ids", TEXT);

	// user fields
	$r->add_checkbox("users_all", INTEGER);
	$r->change_property("users_all", DEFAULT_VALUE, 1);
	$r->add_textbox("users_ids", TEXT);
	$r->add_textbox("users_types_ids", TEXT);

	// friends fields
	$r->add_radio("friends_discount_type", INTEGER, $friends_discount_types, FRIENDS_AND_AFFILIATES_MSG.": ".DISCOUNT_TYPE_MSG);
	$r->change_property("friends_discount_type", REQUIRED, true);
	$r->change_property("friends_discount_type", DEFAULT_VALUE, 0);
	$r->add_select("friends_period", INTEGER, $periods, FRIENDS_PERIOD_MSG);
	$r->add_textbox("friends_interval", INTEGER, FRIENDS_INTERVAL_MSG);
	$r->add_textbox("friends_min_goods", NUMBER, GOODS_TOTAL_MSG." (".MINIMUM_MSG.")");
	$r->add_textbox("friends_max_goods", NUMBER, GOODS_TOTAL_MSG." (".MAXIMUM_MSG.")");

	$r->add_checkbox("friends_all", INTEGER);
	$r->change_property("friends_all", DEFAULT_VALUE, 1);
	$r->add_textbox("friends_ids", TEXT);
	$r->add_textbox("friends_types_ids", TEXT);

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
		} elseif ($coupon_id) {
			$sql  = "SELECT site_id FROM " . $table_prefix . "coupons_sites ";
			$sql .= " WHERE coupon_id=" . $db->tosql($coupon_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$selected_sites[] = $db->f("site_id");
			}
		}
	}
	
	// editing information
	$r->add_textbox("admin_id_added_by", INTEGER);
	$r->change_property("admin_id_added_by", USE_IN_UPDATE, false);
	$r->add_textbox("admin_id_modified_by", INTEGER);
	$r->change_property("admin_id_modified_by", USE_IN_INSERT, false);
	$r->add_textbox("date_added", DATETIME);
	$r->change_property("date_added", USE_IN_UPDATE, false);
	$r->add_textbox("date_modified", DATETIME);
	$r->change_property("date_modified", USE_IN_INSERT, false);
	
	$r->events[BEFORE_SHOW] = "set_record_controls";
	$r->events[AFTER_REQUEST] = "set_coupon_data";
	$r->events[AFTER_VALIDATE] = "set_record_controls";	
	$r->events[BEFORE_DEFAULT] = "coupon_default_values";	
	$r->events[BEFORE_INSERT] = "set_coupon_id";
	$r->events[BEFORE_UPDATE] = "set_admin_data";
	$r->events[AFTER_INSERT] = "update_coupon_data";
	$r->events[AFTER_UPDATE] = "update_coupon_data";
	$r->events[AFTER_DELETE] = "delete_coupon_data";

	// parse template
	$t->set_var("owner_user_id", "[user_id]");
	$t->set_var("owner_user_name", "[user_name]");
	$t->parse("user_template", false);
	
	$r->process();

	$t->set_var("s", $s);
	$t->set_var("s_a", $s_a);

	$t->set_var("date_added_format", join("", $date_edit_format));
	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_coupons_href", "admin_coupons.php");
	$t->set_var("admin_orders_href",  "admin_orders.php");
	$t->set_var("admin_order_href",   $order_details_site_url . "admin_order.php");
	$t->set_var("admin_order_vouchers_href", "admin_order_vouchers.php");
	$t->set_var("admin_product_select_href", "admin_product_select.php");

/*
	if ($order_id > 0) {
		$t->parse("orders_path", false);
	} else {
		$t->parse("coupons_path", false);
	}
*/
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

	$events = array();
	$sql  = " SELECT ce.* ";
	$sql .= " FROM " . $table_prefix . "coupons_events ce ";
	$sql .= " WHERE ce.coupon_id=" . $db->tosql($coupon_id, INTEGER);
	$sql .= " ORDER BY ce.event_id ";
	$db->query($sql);
	while ($db->next_record()) {
		$event_id = $db->f("event_id");
		$event_date = $db->f("event_date", DATETIME);
		$events[$event_id] = $db->Record;
		$events[$event_id]["event_date"] = $event_date;
	}

	$activity_tab = false;
	if (count($events)) {
		$activity_tab = true;
		$voucher_balance = 0;
		foreach ($events as $event_id => $event_data) {
			$order_id = $event_data["order_id"];
			$payment_id = $event_data["payment_id"];
			$transaction_id = $event_data["transaction_id"];
			$admin_id = $event_data["admin_id"];
			$user_id = $event_data["user_id"];
			$from_user_id = $event_data["from_user_id"];
			$to_user_id = $event_data["to_user_id"];
			$event_type = $event_data["event_type"];
			$event_date = $event_data["event_date"];
			$remote_ip = $event_data["remote_ip"];
			$voucher_amount = $event_data["coupon_amount"];
			$voucher_balance += $voucher_amount;
			if (!strlen($transaction_id)) { $transaction_id = $order_id; }

			if ($event_type == "voucher_added" || $event_type == "voucher_add") {
				$event_desc = va_constant("VOUCHER_ADDED_EVENT");
			} else if ($event_type == "voucher_purchased" || $event_type == "voucher_purchase") {
				$event_desc = va_constant("VOUCHER_PURCHASED_EVENT");
			} else if ($event_type == "voucher_sent") {
				$event_desc = va_constant("VOUCHER_SENT_EVENT");
				$sql = " SELECT * FROM ".$table_prefix."users WHERE user_id=".$db->tosql($to_user_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$receiver_name = get_user_name($db->Record);
				} else {
					$receiver_name = "#".$to_user_id;
				}
				$event_desc = str_replace("{receiver_name}", $receiver_name, $event_desc);
			} else if ($event_type == "voucher_received") {
				$event_desc = va_constant("VOUCHER_RECEIVED_EVENT");
				$sql = " SELECT * FROM ".$table_prefix."users WHERE user_id=".$db->tosql($from_user_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$sender_name = get_user_name($db->Record);
				} else {
					$sender_name = "#".$from_user_id;
				}
				$event_desc = str_replace("{sender_name}", $sender_name, $event_desc);
			} else if ($event_type == "voucher_used") {
				$event_desc = va_constant("VOUCHER_USED_EVENT");
			} else if ($event_type == "voucher_cashed_out" || $event_type == "voucher_cash_out") {
				$event_desc = va_constant("VOUCHER_CASHED_OUT_EVENT");
			} else if ($event_type == "transfer_fee") {
				$event_desc = va_constant("TRANSFER_FEE_MSG");
			} else if ($event_type == "subtract_amount") {
				$event_desc = va_constant("VOUCHER_SUBTRACT_AMOUNT_MSG");
			} else if ($event_type == "add_amount") {
				$event_desc = va_constant("VOUCHER_ADD_AMOUNT_MSG");
			} else if ($event_type == "cash_out_fee") {
				$event_desc = va_constant("CASH_OUT_FEE_MSG");
			} else {
				$event_desc = $event_type;
			}

			$t->set_var("event_date", va_date($datetime_show_format, $event_date));
			$t->set_var("event_desc", htmlspecialchars($event_desc));
			$t->set_var("transaction_id", htmlspecialchars($transaction_id));
			$t->set_var("transaction_number", htmlspecialchars($transaction_id));

			$t->set_var("voucher_amount", currency_format($voucher_amount));
			$t->set_var("voucher_balance", currency_format($voucher_balance));
			$t->set_var("remote_ip", htmlspecialchars($remote_ip));
	  
			$t->parse("events", true);
		}
	}

	$discount_type = $r->get_value("discount_type");
	$shipping_tab = ($discount_type == 1 || $discount_type == 2);
	$order_restrictions_tab = ($discount_type == 1 || $discount_type == 2);
	$products_tab = ($discount_type == 3 || $discount_type == 4 || $discount_type == 6 || $discount_type == 7);
	$cart_products_tab = ($discount_type == 3 || $discount_type == 4);
	$order_products_tab = ($discount_type <= 4);
	$past_orders_tab = ($discount_type <= 4);
	$users_tab = ($discount_type != 8);

	$tabs = array(
		"general" => array("title" => EDIT_COUPON_MSG), 
		"shipping" => array("title" => PROD_SHIPPING_MSG, "show" => $shipping_tab), 
		"restrictions" => array("title" => COUPON_RESTRICTIONS_MSG), 
		"order_restrictions" => array("title" => ORDER_RESTRICTIONS_MSG, "show" => $order_restrictions_tab), 
		"users" => array("title" => USERS_MSG, "show" => $users_tab), 
		"products" => array("title" => PRODUCTS_MSG, "show" => $products_tab), 
		"cart_products" => array("title" => CART_PRODUCTS_MSG, "show" => $cart_products_tab), 
		"past_orders" => array("title" => PAST_ORDERS_MSG, "show" => $past_orders_tab), 
		"friends" => array("title" => FRIENDS_AND_AFFILIATES_MSG, "show" => $order_products_tab), 
		"sites" => array("title" => ADMIN_SITES_MSG, "show" => $sitelist),
		"activity" => array("title" => ACTIVITY_MSG, "show" => $activity_tab),
	);

	// check active tabs
	$active_tabs = 0;
	foreach ($tabs as $tab_name => $tab_info) {
		$tab_show = isset($tab_info["show"]) ? $tab_info["show"] : true;
		if ($tab_show) { $active_tabs++; }
	}

	if ($language_code == "en") {
		$tabs_in_row = 8; 
	} else {
		if ($active_tabs > 6) {
			$tabs_in_row = 4; 
		} else {
			$tabs_in_row = 6; 	
		}
	}
	parse_admin_tabs($tabs, $tab, $tabs_in_row);

	if ($sitelist) {
		$t->parse("sitelist");
	}

	$t->pparse("main");
	
	function set_coupon_id()  {
		global $db, $table_prefix, $r;
		global $coupon_id;

		$r->set_value("admin_id_added_by", get_session("session_admin_id"));
		$r->set_value("date_added", va_time());
	}

	function set_admin_data() {
		global $db, $table_prefix, $r;
		$r->set_value("admin_id_modified_by", get_session("session_admin_id"));
		$r->set_value("date_modified", va_time());

		$coupon_id = $r->get_value("coupon_id");
		$discount_type = $r->get_value("discount_type");
		if ($coupon_id && ($discount_type == 5 || $discount_type == 8)) {
			$sql  = " SELECT * FROM ".$table_prefix."coupons ";	
			$sql .= " WHERE coupon_id=" . $db->tosql($coupon_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$owner_user_id = $db->f("user_id");
				$discount_amount = $db->f("discount_amount");
				$r->set_value("prev_owner_user_id", $owner_user_id);
				$r->set_value("prev_discount_amount", $discount_amount);
			}
		}

	}


	function update_coupon_data($params)  {
		global $db, $table_prefix, $r, $ce;
		global $coupon_id, $sitelist, $selected_sites;

		$event = isset($params["event"]) ? $params["event"] : "";
					
		if ($event == AFTER_INSERT) {
			$coupon_id = $db->last_insert_id();
			$r->set_value("coupon_id", $coupon_id);
		} else {
			$coupon_id = $r->get_value("coupon_id");
		}
		$discount_type = $r->get_value("discount_type");

		if ($sitelist) {
			$db->query("DELETE FROM " . $table_prefix . "coupons_sites WHERE coupon_id=" . $db->tosql($coupon_id, INTEGER));
			for ($st = 0; $st < sizeof($selected_sites); $st++) {
				$site_id = $selected_sites[$st];
				if (strlen($site_id)) {
					$sql  = " INSERT INTO " . $table_prefix . "coupons_sites (coupon_id, site_id) VALUES (";
					$sql .= $db->tosql($coupon_id, INTEGER) . ", ";
					$sql .= $db->tosql($site_id, INTEGER) . ") ";
					$db->query($sql);
				}
			}
		}

		if ($event == AFTER_INSERT && ($discount_type == 5 || $discount_type == 8)) {
			$ce->set_value("coupon_id", $coupon_id);
			$ce->set_value("order_id", "");
			$ce->set_value("payment_id", "");
			$ce->set_value("transaction_id", "");
			$ce->set_value("admin_id", get_session("session_admin_id"));
			$ce->set_value("user_id", "");
			$ce->set_value("event_date", va_time());
			$ce->set_value("event_type", "voucher_added");
			$ce->set_value("remote_ip", get_ip());
			$ce->set_value("coupon_amount", $r->get_value("discount_amount"));
			$ce->insert_record();
		} else if ($event == AFTER_UPDATE && ($discount_type == 5 || $discount_type == 8)) {
			$owner_user_id = $r->get_value("owner_user_id");
			$discount_amount = $r->get_value("discount_amount");
			$prev_owner_user_id = $r->get_value("prev_owner_user_id");
			$prev_discount_amount = $r->get_value("prev_discount_amount");
			if (doubleval($discount_amount) != doubleval($prev_discount_amount)) {
				$ce->set_value("coupon_id", $coupon_id);
				$ce->set_value("order_id", "");
				$ce->set_value("payment_id", "");
				$ce->set_value("transaction_id", "");
				$ce->set_value("admin_id", get_session("session_admin_id"));
				$ce->set_value("user_id", "");
				$ce->set_value("event_date", va_time());
				if ($discount_amount > $prev_discount_amount) {
					$ce->set_value("event_type", "add_amount");
				} else {
					$ce->set_value("event_type", "subtract_amount");
				}
				$ce->set_value("remote_ip", get_ip());
				$ce->set_value("coupon_amount", ($discount_amount - $prev_discount_amount));
				$ce->insert_record();
			}
			if ($prev_owner_user_id != $owner_user_id) {
				$ce->set_value("coupon_id", $coupon_id);
				$ce->set_value("order_id", "");
				$ce->set_value("payment_id", "");
				$ce->set_value("transaction_id", "");
				$ce->set_value("admin_id", get_session("session_admin_id"));
				$ce->set_value("user_id", "");
				$ce->set_value("from_user_id", $prev_owner_user_id);
				$ce->set_value("to_user_id", $owner_user_id);
				$ce->set_value("event_date", va_time());
				$ce->set_value("event_type", "voucher_sent");
				$ce->set_value("remote_ip", get_ip());
				$ce->set_value("coupon_amount", $discount_amount);
				$ce->insert_record();
			}
		}
	}

	function delete_coupon_data()  {
		global $db, $table_prefix, $r;
		global $coupon_id;
		$db->query("DELETE FROM " . $table_prefix . "coupons_sites WHERE coupon_id=" . $db->tosql($coupon_id, INTEGER));
	}


	function set_record_controls()
	{
		global $t, $r, $db, $table_prefix, $is_record_controls;
		$discount_type = $r->get_value("discount_type");
		if ($is_record_controls) {
			return false;
		} else {
			$is_record_controls = true;
		}

		if ($r->get_value("order_id") < 1) {
			$r->set_value("order_id", 0);
			$r->set_value("order_item_id", 0);
			$r->change_property("order_item_id", SHOW, false);
		} 

		$order_item_id = $r->get_value("order_item_id");
		if ($order_item_id) {
			$order_items = array();
			$sql  = " SELECT oi.item_name ";
			$sql .= " FROM " . $table_prefix . "orders_items oi ";
			$sql .= " WHERE oi.order_item_id=" . $db->tosql($order_item_id, INTEGER);
			$order_item_name = get_db_value($sql);
			$t->set_var("order_item_name", htmlspecialchars($order_item_name));
		}
		if ($discount_type <= 2) {
			// order coupons
			$discount_types = array( array(1, PERCENTAGE_PER_ORDER_MSG), array(2, AMOUNT_PER_ORDER_MSG) );
			$r->change_property("discount_type", VALUES_LIST, $discount_types);
			$r->change_property("items_ids", SHOW, false);
			$r->change_property("discount_type_text", SHOW, false);
			$r->change_property("discount_type_text", USE_IN_INSERT, false);
			$r->change_property("discount_type_text", USE_IN_UPDATE, false);
			$r->change_property("discount_quantity",SHOW, false);
			$r->change_property("min_quantity",SHOW, false);
			$r->change_property("max_quantity",SHOW, false);
			$r->change_property("minimum_amount",SHOW, false);
			$r->change_property("maximum_amount",SHOW, false);

		} else if ($discount_type == 5 || $discount_type == 8) {
			// vouchers: 5 - gift voucher, 8 - user voucher
			$r->change_property("items_ids",     SHOW, false);
			$r->change_property("free_postage",  SHOW, false);
			$r->change_property("coupon_tax_free",SHOW, false);
			$r->change_property("order_tax_free",SHOW, false);
			$r->change_property("discount_quantity",SHOW, false);
			$r->change_property("min_quantity",SHOW, false);
			$r->change_property("max_quantity",SHOW, false);
			$r->change_property("minimum_amount",SHOW, false);
			$r->change_property("maximum_amount",SHOW, false);
			$r->change_property("min_cart_quantity",SHOW, false);
			$r->change_property("max_cart_quantity",SHOW, false);
			$r->change_property("min_cart_cost",SHOW, false);
			$r->change_property("max_cart_cost",SHOW, false);
			$r->change_property("discount_type", SHOW, false);
			$r->change_property("is_exclusive",  SHOW, false);
			$r->change_property("users_use_limit",SHOW, false);
			$r->change_property("quantity_limit",SHOW, false);
			$r->set_value("quantity_limit", 0);
			$r->change_property("discount_type", USE_IN_INSERT, false);
			$r->change_property("discount_type", USE_IN_UPDATE, false);
			$r->change_property("coupon_code", CONTROL_DESC, va_constant("VOUCHER_CODE_MSG"));
			$r->change_property("discount_amount", CONTROL_DESC, va_constant("VOUCHER_AMOUNT_MSG"));


			$t->set_var("COUPON_CODE_MSG", va_constant("VOUCHER_CODE_MSG"));
			$t->set_var("COUPON_TITLE_MSG", va_constant("NAME_MSG"));
			$t->set_var("DISCOUNT_AMOUNT_MSG", va_constant("VOUCHER_AMOUNT_MSG"));

		} else if ($discount_type == 6 || $discount_type == 7) {
			// sales discount
			$discount_types = array( array(6, PERCENTAGE_PER_PRODUCT_MSG), array(7, AMOUNT_PER_PRODUCT_MSG));
			$r->change_property("discount_type", VALUES_LIST, $discount_types);
			$r->change_property("discount_type_text", SHOW, false);
			$r->change_property("discount_type_text", USE_IN_INSERT, false);
			$r->change_property("discount_type_text", USE_IN_UPDATE, false);
			$r->change_property("is_auto_apply", SHOW, false);
			$r->change_property("free_postage", SHOW, false);
			$r->change_property("coupon_tax_free",SHOW, false);
			$r->change_property("order_tax_free", SHOW, false);
			$r->change_property("discount_quantity",SHOW, false);
			// restrictions
			$r->change_property("min_quantity",SHOW, false);
			$r->change_property("max_quantity",SHOW, false);
			$r->change_property("is_exclusive",  SHOW, false);
			$r->change_property("users_use_limit",SHOW, false);
			$r->change_property("quantity_limit",SHOW, false);
			$r->change_property("coupon_uses",SHOW, false);
			// Cart Restrictions
			$r->change_property("min_cart_quantity",SHOW, false);
			$r->change_property("max_cart_quantity",SHOW, false);
			$r->change_property("min_cart_cost",SHOW, false);
			$r->change_property("max_cart_cost",SHOW, false);
			// Past Orders Restrictions
			$r->change_property("orders_restrictions",SHOW, false);
		} else  {
			$discount_types = array( array(3, PERCENTAGE_PER_PRODUCT_MSG), array(4, AMOUNT_PER_PRODUCT_MSG));
			$r->change_property("discount_type", VALUES_LIST, $discount_types);
			$r->change_property("free_postage", SHOW, false);
			$r->change_property("coupon_tax_free",SHOW, false);
			$r->change_property("order_tax_free", SHOW, false);
			$r->change_property("discount_type_text", SHOW, false);
			$r->change_property("discount_type_text", USE_IN_INSERT, false);
			$r->change_property("discount_type_text", USE_IN_UPDATE, false);
			$t->set_var("minimum_amount_title", MINIMUM_PRICE_OF_PRODUCT_MSG);
			$t->set_var("maximum_amount_title", MAXIMUM_PRICE_OF_PRODUCT_MSG);
			$t->set_var("min_quantity_desc", MIN_QTY_SAME_PRODUCTS_MSG);
			$t->set_var("max_quantity_desc", MAX_QTY_SAME_PRODUCTS_MSG);
		}

	
		if ($discount_type == 8) {
			$r->change_property("owner_user_id", REQUIRED, true);
		} else {
			$r->change_property("owner_user_id", SHOW, false);
		}
		$owner_user_id = $r->get_value("owner_user_id");
		if ($owner_user_id) {
			$sql  = " SELECT user_id, login, email, name, first_name, last_name, nickname, company_name ";
			$sql .= " FROM " . $table_prefix . "users u ";
			$sql .= " WHERE user_id=" . $db->tosql($owner_user_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$user_name = $db->f("name");
				if (!strlen($user_name)) { $user_name = trim($db->f("first_name") . " " . $db->f("last_name")); }
				if (!strlen($user_name)) { $user_name = trim($db->f("nickname")); }
				if (!strlen($user_name)) { $user_name = $db->f("company_name"); }
				if (!strlen($user_name)) { $user_name = $db->f("login"); }

				$t->set_var("owner_user_id", $owner_user_id);
				$t->set_var("owner_user_name", $user_name);
				$t->parse_to("user_template", "selected_user", false);
			}
		}

		if ($discount_type == 3 || $discount_type == 4 || $discount_type == 6 || $discount_type == 7) {

			$items_ids = $r->get_value("items_ids");
			if ($items_ids) {
				$sql  = " SELECT i.item_id, i.item_name ";
				$sql .= " FROM " . $table_prefix . "items i ";
				$sql .= " WHERE i.item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ") ";
				$sql .= " ORDER BY i.item_name ";
				$db->query($sql);
				while($db->next_record())
				{
					$row_item_id = $db->f("item_id");
					$item_name = $db->f("item_name");
		  
					$t->set_var("item_id", $row_item_id);
					$t->set_var("item_name", $item_name);
					$t->set_var("item_name_js", str_replace("\"", "&quot;", $item_name));
		  
					$t->parse("selected_items", true);
					$t->parse("selected_items_js", true);
				}
			}

			$items_types_ids = $r->get_value("items_types_ids");
			if ($items_types_ids) {
				$sql  = " SELECT it.item_type_id, it.item_type_name ";
				$sql .= " FROM " . $table_prefix . "item_types it ";
				$sql .= " WHERE it.item_type_id IN (" . $db->tosql($items_types_ids, INTEGERS_LIST) . ") ";
				$sql .= " ORDER BY it.item_type_name ";
				$db->query($sql);
				while($db->next_record())
				{
					$row_type_id = $db->f("item_type_id");
					$type_name = $db->f("item_type_name");
		  
					$t->set_var("item_type_id", $row_type_id);
					$t->set_var("item_type_name", $type_name);
					$t->set_var("item_type_name_js", str_replace("\"", "&quot;", $type_name));
		  
					$t->parse("selected_item_types", true);
					$t->parse("selected_item_types_js", true);
				}
			}

			// parse selected categories
			$categories = array();
			$items_categories = array();
			$items_categories_ids = $r->get_value("items_categories_ids");
			$categories_ids = array();
			if ($items_categories_ids) {
				$sql  = " SELECT c.category_id, c.category_name, c.category_path ";
				$sql .= " FROM " . $table_prefix . "categories c ";
				$sql .= " WHERE c.category_id IN (" . $db->tosql($items_categories_ids, INTEGERS_LIST) . ") ";
				$sql .= " ORDER BY c.category_order, c.category_name ";
				$db->query($sql);
				while($db->next_record())
				{
					$row_category_id = $db->f("category_id");
					$category_name = get_translation($db->f("category_name"));
					$category_path = $db->f("category_path");
					$categories[$row_category_id] = $category_name;
					$items_categories[$row_category_id] = $category_path;
					$path_ids = explode(",", $category_path);
					for ($p = 0; $p < sizeof($path_ids); $p++) {
						$path_id = $path_ids[$p];
						if ($path_id) {
							$categories_ids[] = $path_id;
						}
					}
				}
			}
			if (sizeof($categories_ids) > 0) {
				$sql  = " SELECT c.category_id, c.category_name ";
				$sql .= " FROM " . $table_prefix . "categories c ";
				$sql .= " WHERE c.category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ") ";
				$sql .= " ORDER BY c.category_order, c.category_name ";
				$db->query($sql);
				while($db->next_record()) {
					$row_category_id = $db->f("category_id");
					$category_name = get_translation($db->f("category_name"));
					$categories[$row_category_id] = $category_name;
				}
			}

			if (sizeof($items_categories) > 0) {
				foreach ($items_categories as $category_id => $category_path) {
					$category_name = "";
					$path_ids = explode(",", $category_path);
					for ($p = 0; $p < sizeof($path_ids); $p++) {
						$path_id = $path_ids[$p];
						if (isset($categories[$path_id])) {
							$category_name .= $categories[$path_id] . " > ";
						}
					}
					if (isset($categories[$category_id])) {
						$category_name .= $categories[$category_id];
					}
		  
					$t->set_var("category_id", $category_id);
					$t->set_var("category_name", $category_name);
					$t->set_var("category_name_js", str_replace("\"", "&quot;", $category_name));
		  
					$t->parse("selected_categories", true);
					$t->parse("selected_categories_js", true);
				}
			}
			// end categories parse

		}

		if ($discount_type <= 2) {
			// get shipping methods 
			$shipping_ids = $r->get_value("free_postage_ids");
			if ($shipping_ids) {
				$sql  = " SELECT st.shipping_type_id, sm.shipping_module_name, st.shipping_type_desc ";
				$sql .= " FROM (" . $table_prefix . "shipping_types st ";
				$sql .= " INNER JOIN " . $table_prefix . "shipping_modules sm ON st.shipping_module_id=sm.shipping_module_id) ";
				$sql .= " WHERE st.shipping_type_id IN (" . $db->tosql($shipping_ids, INTEGERS_LIST) . ") ";
				$sql .= " ORDER BY st.shipping_order ";
				$db->query($sql);
				while($db->next_record())
				{
					$row_shipping_id = $db->f("shipping_type_id");
					$module_name = $db->f("shipping_module_name");
					$type_desc = $db->f("shipping_type_desc");
					$shipping_name = $module_name . " &gt; " . $type_desc;
		  
					$t->set_var("shipping_id", $row_shipping_id);
					$t->set_var("shipping_name", $shipping_name);
					$t->set_var("shipping_name_js", str_replace("\"", "&quot;", $shipping_name));
		  
					$t->parse("selected_shippings", true);
					$t->parse("selected_shippings_js", true);
				}
			}
		}

		if ($discount_type <= 4) {

			$cart_items_ids = $r->get_value("cart_items_ids");
			if ($cart_items_ids) {
				$sql  = " SELECT i.item_id, i.item_name ";
				$sql .= " FROM " . $table_prefix . "items i ";
				$sql .= " WHERE i.item_id IN (" . $db->tosql($cart_items_ids, INTEGERS_LIST) . ") ";
				$sql .= " ORDER BY i.item_name ";
				$db->query($sql);
				while($db->next_record())
				{
					$row_item_id = $db->f("item_id");
					$item_name = $db->f("item_name");
		  
					$t->set_var("item_id", $row_item_id);
					$t->set_var("item_name", $item_name);
					$t->set_var("item_name_js", str_replace("\"", "&quot;", $item_name));
		  
					$t->parse("selected_cart_items", true);
					$t->parse("selected_cart_items_js", true);
				}
			}

			$cart_items_types_ids = $r->get_value("cart_items_types_ids");
			if ($cart_items_types_ids) {
				$sql  = " SELECT it.item_type_id, it.item_type_name ";
				$sql .= " FROM " . $table_prefix . "item_types it ";
				$sql .= " WHERE it.item_type_id IN (" . $db->tosql($cart_items_types_ids, INTEGERS_LIST) . ") ";
				$sql .= " ORDER BY it.item_type_name ";
				$db->query($sql);
				while($db->next_record())
				{
					$row_type_id = $db->f("item_type_id");
					$type_name = $db->f("item_type_name");
		  
					$t->set_var("item_type_id", $row_type_id);
					$t->set_var("item_type_name", $type_name);
					$t->set_var("item_type_name_js", str_replace("\"", "&quot;", $type_name));
		  
					$t->parse("selected_cart_types", true);
					$t->parse("selected_cart_types_js", true);
				}
			}

			// past orders data
			$orders_items_ids = $r->get_value("orders_items_ids");
			if ($orders_items_ids) {
				$sql  = " SELECT i.item_id, i.item_name ";
				$sql .= " FROM " . $table_prefix . "items i ";
				$sql .= " WHERE i.item_id IN (" . $db->tosql($orders_items_ids, INTEGERS_LIST) . ") ";
				$sql .= " ORDER BY i.item_name ";
				$db->query($sql);
				while($db->next_record())
				{
					$row_item_id = $db->f("item_id");
					$item_name = $db->f("item_name");
		  
					$t->set_var("item_id", $row_item_id);
					$t->set_var("item_name", $item_name);
					$t->set_var("item_name_js", str_replace("\"", "&quot;", $item_name));
		  
					$t->parse("selected_orders_items", true);
					$t->parse("selected_orders_items_js", true);
				}
			}

			$orders_types_ids = $r->get_value("orders_types_ids");
			if ($orders_types_ids) {
				$sql  = " SELECT it.item_type_id, it.item_type_name ";
				$sql .= " FROM " . $table_prefix . "item_types it ";
				$sql .= " WHERE it.item_type_id IN (" . $db->tosql($orders_types_ids, INTEGERS_LIST) . ") ";
				$sql .= " ORDER BY it.item_type_name ";
				$db->query($sql);
				while($db->next_record())
				{
					$row_type_id = $db->f("item_type_id");
					$type_name = $db->f("item_type_name");
		  
					$t->set_var("item_type_id", $row_type_id);
					$t->set_var("item_type_name", $type_name);
					$t->set_var("item_type_name_js", str_replace("\"", "&quot;", $type_name));
		  
					$t->parse("selected_orders_types", true);
					$t->parse("selected_orders_types_js", true);
				}
			}

			// get friends ids
			$friends_ids = $r->get_value("friends_ids");
			if ($friends_ids) {
				$sql  = " SELECT user_id, login, nickname, email, name, first_name, last_name, company_name, ";
				$sql .= " delivery_name, delivery_first_name, delivery_last_name, delivery_company_name ";
				$sql .= " FROM " . $table_prefix . "users u ";
				$sql .= " WHERE user_id IN (" . $db->tosql($friends_ids, INTEGERS_LIST) . ") ";
				$sql .= " ORDER BY name";
				$db->query($sql);
				while($db->next_record())
				{
					$row_user_id = $db->f("user_id");
					$user_name = $db->f("name");
					if (!strlen($user_name)) { $user_name = trim($db->f("first_name") . " " . $db->f("last_name")); }
					if (!strlen($user_name)) { $user_name = trim($db->f("nickname")); }
					if (!strlen($user_name)) { $user_name = $db->f("company_name"); }
					if (!strlen($user_name)) { $user_name = trim($db->f("delivery_name")); }
					if (!strlen($user_name)) { $user_name = trim($db->f("delivery_first_name") . " " . $db->f("delivery_last_name")); }
					if (!strlen($user_name)) { $user_name = $db->f("delivery_company_name"); }
					if (!strlen($user_name)) { $user_name = $db->f("login"); }
		  
					$t->set_var("user_id", $row_user_id);
					$t->set_var("user_name", $user_name);
					$t->set_var("user_name_js", str_replace("\"", "&quot;", $user_name));
		  
					$t->parse("selected_friends", true);
					$t->parse("selected_friends_js", true);
				}
			}

			$friends_types_ids = $r->get_value("friends_types_ids");
			if ($friends_types_ids) {
				$sql  = " SELECT ut.type_id, ut.type_name ";
				$sql .= " FROM " . $table_prefix . "user_types ut ";
				$sql .= " WHERE ut.type_id IN (" . $db->tosql($friends_types_ids, INTEGERS_LIST) . ") ";
				$sql .= " ORDER BY ut.type_name ";
				$db->query($sql);
				while($db->next_record())
				{
					$row_type_id = $db->f("type_id");
					$type_name = $db->f("type_name");
		  
					$t->set_var("user_type_id", $row_type_id);
					$t->set_var("user_type_name", $type_name);
					$t->set_var("user_type_name_js", str_replace("\"", "&quot;", $type_name));
		  
					$t->parse("selected_friends_types", true);
					$t->parse("selected_friends_types_js", true);
				}
			}


		}

		if ($discount_type <= 4 || $discount_type == 6 || $discount_type == 7) {
			// get users ids
			$users_ids = $r->get_value("users_ids");
			if ($users_ids) {
				$sql  = " SELECT user_id, login, nickname, email, name, first_name, last_name, company_name, ";
				$sql .= " delivery_name, delivery_first_name, delivery_last_name, delivery_company_name ";
				$sql .= " FROM " . $table_prefix . "users u ";
				$sql .= " WHERE user_id IN (" . $db->tosql($users_ids, INTEGERS_LIST) . ") ";
				$sql .= " ORDER BY name ";
				$db->query($sql);
				while($db->next_record())
				{
					$row_user_id = $db->f("user_id");
					$user_name = $db->f("name");
					if (!strlen($user_name)) { $user_name = trim($db->f("first_name") . " " . $db->f("last_name")); }
					if (!strlen($user_name)) { $user_name = trim($db->f("nickname")); }
					if (!strlen($user_name)) { $user_name = $db->f("company_name"); }
					if (!strlen($user_name)) { $user_name = trim($db->f("delivery_name")); }
					if (!strlen($user_name)) { $user_name = trim($db->f("delivery_first_name") . " " . $db->f("delivery_last_name")); }
					if (!strlen($user_name)) { $user_name = $db->f("delivery_company_name"); }
					if (!strlen($user_name)) { $user_name = $db->f("login"); }
		  
					$t->set_var("user_id", $row_user_id);
					$t->set_var("user_name", $user_name);
					$t->set_var("user_name_js", str_replace("\"", "&quot;", $user_name));
		  
					$t->parse("selected_users", true);
					$t->parse("selected_users_js", true);
				}
			}

			$users_types_ids = $r->get_value("users_types_ids");
			if ($users_types_ids) {
				$sql  = " SELECT ut.type_id, ut.type_name ";
				$sql .= " FROM " . $table_prefix . "user_types ut ";
				$sql .= " WHERE ut.type_id IN (" . $db->tosql($users_types_ids, INTEGERS_LIST) . ") ";
				$sql .= " ORDER BY ut.type_name ";
				$db->query($sql);
				while($db->next_record())
				{
					$row_type_id = $db->f("type_id");
					$type_name = $db->f("type_name");
		  
					$t->set_var("user_type_id", $row_type_id);
					$t->set_var("user_type_name", $type_name);
					$t->set_var("user_type_name_js", str_replace("\"", "&quot;", $type_name));
		  
					$t->parse("selected_user_types", true);
					$t->parse("selected_user_types_js", true);
				}
			}
		}

		$friend_controls = array();
		//$friend_controls["friends_period"] = "disabled";
		//$friend_controls["friends_interval"] = "disabled";
		//$friend_controls["friends_min_goods"] = "disabled";
		//$friend_controls["friends_max_goods"] = "disabled";
		//$friend_controls["friends_all"] = "disabled";
		// hide controls
		$friend_controls["friends_all_tr"] = "none";
		$friend_controls["friends_users_tr"] = "none";
		$friend_controls["friends_types_tr"] = "none";
		$friend_controls["friends_period_tr"] = "none";
		$friend_controls["friends_min_goods_tr"] = "none";
		$friend_controls["friends_max_goods_tr"] = "none";

		if ($r->get_value("friends_discount_type") == 1) {
			$friend_controls["friends_period"] = "active";
			$friend_controls["friends_interval"] = "active";
			$friend_controls["friends_min_goods"] = "active";
			$friend_controls["friends_max_goods"] = "active";
			// show rows
			$friend_controls["friends_period_tr"] = "table-row";
			$friend_controls["friends_min_goods_tr"] = "table-row";
			$friend_controls["friends_max_goods_tr"] = "table-row";

		} else if ($r->get_value("friends_discount_type") == 2) {
			$friend_controls["friends_all"] = "active";
			$friend_controls["friends_all_tr"] = "table-row";
			$friend_controls["friends_users_tr"] = "table-row";
			$friend_controls["friends_types_tr"] = "table-row";
		}
		foreach ($friend_controls as $control_name => $control_type) {
			if ($control_type == "active") {
				$t->set_var($control_name."_disabled", "");
			} else if ($control_type == "disabled") {
				$t->set_var($control_name."_disabled", "disabled");
			} else if ($control_type == "none") {
				$t->set_var($control_name."_style", "display:none;");
			} else if ($control_type == "table-row") {
				$t->set_var($control_name."_style", "display:table-row;");
			}
		}

		// set styles for a tag for items_all checkbox
		$items_all = $r->get_value("items_all");
		if ($items_all) {
			$t->set_var("items_all_a_class", "disabled");
		} else {
			$t->set_var("items_all_a_class", "title");
		}


	}

	function set_coupon_data()  
	{
		global $r, $t, $sitelist;
		$discount_type = $r->get_value("discount_type");
		if (!$sitelist) {
			$r->set_value("sites_all", 1);
		}
		if ($discount_type == 6 || $discount_type == 7) {
			$r->set_value("is_auto_apply", 1);
			$r->set_value("quantity_limit", 0);
		}
		if ($discount_type == 5 || $discount_type == 8) {
			$t->set_var("COUPON_CODE_MSG", va_constant("VOUCHER_CODE_MSG"));
			$t->set_var("COUPON_TITLE_MSG", va_constant("NAME_MSG"));
			$t->set_var("DISCOUNT_AMOUNT_MSG", va_constant("VOUCHER_AMOUNT_MSG"));

			$r->change_property("coupon_code", CONTROL_DESC, va_constant("VOUCHER_CODE_MSG"));
			$r->change_property("coupon_title", CONTROL_DESC, va_constant("NAME_MSG"));
			$r->change_property("discount_amount", CONTROL_DESC, va_constant("VOUCHER_AMOUNT_MSG"));
		}

	}

	function coupon_default_values()
	{
		global $r;
		$discount_type = get_param("discount_type");
		if ($discount_type == 6 || $discount_type == 7) {
			$r->change_property("discount_amount", DEFAULT_VALUE, "");
		}

	}

?>