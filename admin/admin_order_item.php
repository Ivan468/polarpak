<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_order_item.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/editgrid.php");
	include_once($root_folder_path . "includes/shopping_cart.php");
	include_once($root_folder_path . "includes/order_items.php");
	include_once($root_folder_path . "includes/order_recalculate.php");
	include_once($root_folder_path . "includes/tabs_functions.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("sales_orders");
	check_admin_security("update_orders");

	$permissions = get_permissions();
	$update_orders = get_setting_value($permissions, "update_orders", 0);
	$remove_orders = get_setting_value($permissions, "remove_orders", 0);
	$add_products = get_setting_value($permissions, "add_order_products", 0);
	$update_products = get_setting_value($permissions, "update_order_products", 0);
	$remove_products = get_setting_value($permissions, "remove_order_products", 0);

	$product_link = get_setting_value($permissions, "products_categories", 0);

	// admin privileges info
	$admin_info = get_session("session_admin_info");
	$privilege_id = get_session("session_admin_privilege_id");
	$access_all_user_types = get_setting_value($admin_info, "user_types_all", 0); 
	$access_unreg_users = get_setting_value($admin_info, "non_logged_users", 0); 
	$access_user_types = get_setting_value($admin_info, "user_types_ids", ""); 
	$orders_currency = get_setting_value($settings, "orders_currency", 0);

	// check statuses and their access levels
	$set_statuses = array();
	$see_statuses_ids = array();
	$set_statuses_ids = array();
	$update_statuses_ids = array();
	$sl_actions = array();
	$sql = " SELECT * FROM " . $table_prefix . "order_statuses ";
	$sql.= " WHERE is_active=1 ORDER BY status_order, status_id";
	$db->query($sql);
	while ($db->next_record()) {
		$status_id = $db->f("status_id");
		$status_name = get_translation($db->f("status_name"));
		$stock_level_action = $db->f("stock_level_action");
		$sl_actions[$status_id] = $stock_level_action;

		// check access levels
		$view_order_groups_all = $db->f("view_order_groups_all");
		$view_order_groups_ids = explode(",", $db->f("view_order_groups_ids"));
		$set_status_groups_all = $db->f("set_status_groups_all");
		$set_status_groups_ids = explode(",", $db->f("set_status_groups_ids"));
		$update_order_groups_all = $db->f("update_order_groups_all");
		$update_order_groups_ids = explode(",", $db->f("update_order_groups_ids"));
		if ($view_order_groups_all || in_array($privilege_id, $view_order_groups_ids)) {
			$see_statuses_ids[] = $status_id;
		}
		if ($set_status_groups_all || in_array($privilege_id, $set_status_groups_ids)) {
			$set_statuses[] = array($status_id, $status_name);
			$set_statuses_ids[] = $status_id;
		}
		if ($update_order_groups_all || in_array($privilege_id, $update_order_groups_ids)) {
			$update_statuses_ids[] = $status_id;
		}
	}

	$points_system = get_setting_value($settings, "points_system", 0);
	$points_conversion_rate = get_setting_value($settings, "points_conversion_rate", 1);
	$points_decimals = get_setting_value($settings, "points_decimals", 0);
	$reward_points_details = get_setting_value($settings, "reward_points_details", 0);
	$points_prices = get_setting_value($settings, "points_prices", 0);
	$affiliate_commission_deduct = get_setting_value($settings, "affiliate_commission_deduct", 0);
	$credit_system = get_setting_value($settings, "credit_system", 0);
	
	$date_format_msg = str_replace("{date_format}", join("", $date_edit_format), DATE_FORMAT_MSG);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_order_item.html");
	$t->set_var("admin_product_href", "admin_product.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_order_href", $order_details_site_url . "admin_order.php");
	$t->set_var("admin_orders_href", "admin_orders.php");
	$t->set_var("admin_order_item_href", "admin_order_item.php");
	$t->set_var("admin_product_href", "admin_product.php");	
	$t->set_var("admin_product_select_href", "admin_product_select.php");	
	$t->set_var("date_edit_format", join("", $date_edit_format));
	$t->set_var("date_format_msg", $date_format_msg);

	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", PRODUCT_MSG, CONFIRM_DELETE_MSG));

	$order_id = get_param("order_id");
	$set_currency_code = get_param("set_currency_code");
	$order_item_id = get_param("order_item_id");
	$default_status = ""; $order_status = ""; $item_status = ""; $original_item_name = "";
	$quantity_old = 0;
	if ($order_item_id) {
		$sql  = " SELECT oi.order_id, oi.item_name, oi.user_id, oi.affiliate_user_id, oi.quantity, oi.item_status, o.order_status ";
		$sql .= " FROM " . $table_prefix . "orders_items oi ";
		$sql .= " INNER JOIN ".$table_prefix."orders o ON oi.order_id=o.order_id ";
		$sql .= " WHERE oi.order_item_id=" . $db->tosql($order_item_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$order_id = $db->f("order_id");
			$order_status = $db->f("order_status");
			$user_id = $db->f("user_id");
			$affiliate_user_id = $db->f("affiliate_user_id");
			$quantity_old = $db->f("quantity");
			$original_item_name = $db->f("item_name");
		} else {
			$order_id = "";
		}
	} elseif ($order_id) {
		$sql  = " SELECT o.order_id, o.order_status, o.user_id, o.affiliate_user_id ";
		$sql .= " FROM " . $table_prefix . "orders o ";
		$sql .= " WHERE o.order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$order_id = $db->f("order_id");
			$order_status = $db->f("order_status");
			$default_status = $db->f("order_status");
			$user_id = $db->f("user_id");
			$affiliate_user_id = $db->f("affiliate_user_id");
		} else {
			$order_id = "";
		}
	}
	$current_sl_action = ($item_status && (isset($sl_actions[$item_status]))) ? $sl_actions[$item_status] : 0;	
		
	if (!$order_id) {
		die(NO_PRODUCT_ORDER_LIST_MSG);
	}

	if ((strlen($order_status) && !in_array($order_status, $see_statuses_ids))
		|| ( strlen($item_status) && !in_array($item_status, $see_statuses_ids))
	) {
		die(ADMIN_ACCESS_ERROR);
	}

	if ((strlen($order_status) && !in_array($order_status, $set_statuses_ids))
		|| ( strlen($item_status) && !in_array($item_status, $set_statuses_ids))
		|| ( strlen($order_status) && !in_array($order_status, $update_statuses_ids))
		|| ( strlen($item_status) && !in_array($item_status, $update_statuses_ids))
	) {
		$update_orders = false;
	}

	$time_periods =
		array(
			array("", ""), array(1, DAY_MSG), array(2, WEEK_MSG), array(3, MONTH_MSG), array(4, YEAR_MSG)
		);

	// set up html form parameters
	$r = new VA_Record($table_prefix . "orders_items");
	$r->add_where("order_item_id", INTEGER);
	$r->add_textbox("order_id", INTEGER);
	$r->change_property("order_id", USE_IN_INSERT, true);
	$r->change_property("order_id", USE_IN_UPDATE, false);
	$r->add_textbox("item_id", INTEGER);
	$r->change_property("item_id", USE_IN_INSERT, true);
	$r->change_property("item_id", USE_IN_UPDATE, true);
	$r->change_property("item_id", USE_SQL_NULL, false);
	$item_types = get_db_values("SELECT item_type_id, item_type_name FROM " . $table_prefix . "item_types", array(array(0, "")));
	$r->add_select("item_type_id", INTEGER, $item_types, TYPE_MSG);
	$suppliers = get_db_values("SELECT supplier_id,supplier_name FROM " . $table_prefix . "suppliers ORDER BY supplier_order, supplier_name", array(array("", "")));
	$r->add_select("supplier_id", INTEGER, $suppliers, SUPPLIER_MSG);
	$r->change_property("supplier_id", USE_SQL_NULL, false);
	if (sizeof($suppliers) <= 1) {
		$r->change_property("supplier_id", SHOW, false);
	}
	$r->add_textbox("item_code", TEXT);
	$r->change_property("item_code", USE_SQL_NULL, false);
	$r->add_textbox("manufacturer_code", TEXT);
	//$r->add_textbox("coupons_ids", TEXT);
	$sql = "SELECT status_id, status_name FROM " . $table_prefix . "order_statuses WHERE is_active=1 ORDER BY status_order, status_id";
	$order_statuses = get_db_values($sql, array(array("", "")));
	$r->add_select("item_status", INTEGER, $order_statuses, STATUS_MSG);
	$r->change_property("item_status", REQUIRED, true);
	$r->add_textbox("item_name", TEXT, PROD_NAME_MSG);
	$r->change_property("item_name", REQUIRED, true);
	$r->add_textbox("buying_price", FLOAT);
	$r->add_textbox("real_price", FLOAT);
	$r->change_property("real_price", USE_IN_UPDATE, false);
	$r->add_textbox("price", FLOAT, PRICE_MSG);
	$r->change_property("price", REQUIRED, true);
	$r->add_textbox("discount_amount", FLOAT, DISCOUNT_AMOUNT_MSG);
	$r->add_textbox("tax_percent", FLOAT, TAX_PERCENT_MSG);
	$r->add_checkbox("tax_free", INTEGER);
	$r->add_textbox("quantity", INTEGER, QUANTITY_MSG);
	$r->change_property("quantity", REQUIRED, true);
	$r->change_property("quantity", MIN_VALUE, 1);
	$r->add_checkbox("downloadable", INTEGER);

	// package parameters
	$r->add_textbox("packages_number", NUMBER, PACKAGES_NUMBER_MSG);
	$r->add_textbox("weight", NUMBER, WEIGHT_MSG);
	$r->add_textbox("actual_weight", NUMBER, va_message("ACTUAL_WEIGHT_MSG"));
	$r->add_textbox("width", NUMBER, WIDTH_MSG);
	$r->change_property("width", MIN_VALUE, 0);
	$r->add_textbox("height", NUMBER, HEIGHT_MSG);
	$r->change_property("height", MIN_VALUE, 0);
	$r->add_textbox("item_length", NUMBER, LENGTH_MSG);
	$r->change_property("item_length", COLUMN_NAME, "length"); // need to rename as JS handle it as function
	$r->change_property("item_length", MIN_VALUE, 0);

	// shipping options
	$r->add_checkbox("is_shipping_free", NUMBER);
	$r->add_textbox("shipping_cost", NUMBER, SHIPPING_COST_MSG);

	// points 
	$r->add_textbox("points_price", NUMBER, POINTS_PRICE_MSG);
	$r->add_textbox("reward_points", NUMBER, REWARD_POINTS_AMOUNT_MSG);
	$r->add_textbox("reward_credits", NUMBER, REWARD_CREDITS_AMOUNT_MSG);

	// commissions
	$r->add_textbox("affiliate_commission", NUMBER, AFFILIATE_MSG . " "  . COMMISSIONS_MSG);
	$r->add_textbox("merchant_commission", NUMBER, MERCHANT_MSG . " " . COMMISSIONS_MSG);

	// recurring options
	$r->add_checkbox("is_recurring", INTEGER);
	$r->add_textbox("recurring_price", NUMBER, RECURRING_PRICE_MSG);
	$r->add_select("recurring_period", INTEGER, $time_periods, RECURRING_PERIOD_MSG);
	$r->add_textbox("recurring_interval", INTEGER, RECURRING_INTERVAL_MSG);
	$r->add_textbox("recurring_payments_total", INTEGER, RECURRING_PAYMENTS_TOTAL_MSG);
	$r->add_textbox("recurring_payments_made", INTEGER, RECURRING_PAYMENTS_MADE_MSG);
	$r->add_textbox("recurring_payments_failed", INTEGER, RECURRING_PAYMENTS_FAILED_MSG);
	$r->add_textbox("recurring_last_payment", DATETIME, RECURRING_LAST_PAYMENT_MSG);
	$r->change_property("recurring_last_payment", VALUE_MASK, $date_edit_format);
	$r->add_textbox("recurring_plan_payment", DATETIME, RECURRING_PLAN_PAYMENT_MSG);
	$r->change_property("recurring_plan_payment", VALUE_MASK, $date_edit_format);
	$r->add_textbox("recurring_next_payment", DATETIME, RECURRING_NEXT_PAYMENT_MSG);
	$r->change_property("recurring_next_payment", VALUE_MASK, $date_edit_format);
	$r->add_textbox("recurring_end_date", DATETIME, RECURRING_END_DATE_MSG);
	$r->change_property("recurring_end_date", VALUE_MASK, $date_edit_format);

	// subscription options
	$r->add_checkbox("is_subscription", INTEGER);
	$r->add_checkbox("is_account_subscription", INTEGER);
	$r->add_select("subscription_period", INTEGER, $time_periods, SUBSCRIPTION_PERIOD_MSG);
	$r->add_textbox("subscription_interval", INTEGER, SUBSCRIPTION_INTERVAL_MSG);
	$r->add_textbox("subscription_suspend", INTEGER, SUBSCRIPTION_SUSPEND_MSG);
	$r->add_textbox("subscription_start_date", DATETIME, SUBSCRIPTION_SUSPEND_MSG);
	$r->change_property("subscription_start_date", VALUE_MASK, $date_edit_format);
	$r->add_textbox("subscription_expiry_date", DATETIME, SUBSCRIPTION_SUSPEND_MSG);
	$r->change_property("subscription_expiry_date", VALUE_MASK, $date_edit_format);

	$r->operations[INSERT_ALLOWED] = $add_products;
	$r->operations[UPDATE_ALLOWED] = $update_products;
	$r->operations[DELETE_ALLOWED] = $remove_products;

	$r->get_form_values();
	$r->set_value("order_id", $order_id);
	$new_item_status = $r->get_value("item_status");
	$new_sl_action = ($new_item_status && (isset($sl_actions[$new_item_status]))) ? $sl_actions[$new_item_status] : 0;	

	$ipv = new VA_Record($table_prefix . "orders_items_properties", "properties");
	$ipv->add_where("item_property_id", INTEGER);
	$ipv->add_hidden("order_id", INTEGER);
	$ipv->change_property("order_id", USE_IN_INSERT, true);
	$ipv->add_hidden("order_item_id", INTEGER);
	$ipv->change_property("order_item_id", USE_IN_INSERT, true);
	$ipv->add_hidden("property_id", INTEGER);
	$ipv->change_property("property_id", USE_IN_INSERT, true);
	$ipv->add_textbox("property_name", TEXT, PROPERTY_NAME_MSG);
	$ipv->change_property("property_name", REQUIRED, true);
	$ipv->add_checkbox("hide_name", INTEGER);
	$ipv->add_textbox("property_value", TEXT, PROPERTY_VALUE_MSG);
	$ipv->change_property("property_value", REQUIRED, true);
	$ipv->add_textbox("additional_price", NUMBER, ADDITIONAL_PRICE_MSG);
	$ipv->add_textbox("additional_weight", NUMBER, ADDITIONAL_WEIGHT_MSG);
	$ipv->add_textbox("additional_actual_weight", NUMBER, va_message("ACTUAL_WEIGHT_MSG"));
	$ipv->change_property("additional_actual_weight", COLUMN_NAME, "actual_weight");

	$property_id = get_param("property_id");
	$order_item_id = get_param("order_item_id");

	$more_properties = get_param("more_properties");
	$number_properties = get_param("number_properties");

	$eg = new VA_EditGrid($ipv, "properties");
	$eg->get_form_values($number_properties);

	$operation = get_param("operation");
	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }

	$return_page = $order_details_site_url . "admin_order.php?order_id=" . $order_id;

	// events record
	$oe = new VA_Record($table_prefix . "orders_events");
	$oe->add_textbox("order_id", INTEGER);
	$oe->add_textbox("status_id", INTEGER);
	$oe->add_textbox("admin_id", INTEGER);
	$oe->add_textbox("event_date", DATETIME);
	$oe->add_textbox("event_type", TEXT);
	$oe->add_textbox("event_name", TEXT);
	$oe->add_textbox("event_description", TEXT);
	// set basic values
	$oe->set_value("order_id", $order_id);
	$oe->set_value("status_id", 0);
	$oe->set_value("admin_id", get_session("session_admin_id"));
	$oe->set_value("event_date", va_time());

	if (strlen($operation) && $update_orders && !$more_properties)
	{
		if ($operation == "cancel") {
			header("Location: " . $return_page);
			exit;
		} elseif ($operation == "delete" && $order_item_id) {
			if ($remove_products) {
				// restore stock level if necessary
				$sql = "SELECT stock_level, use_stock_level FROM " . $table_prefix . "items WHERE item_id=" . $db->tosql($r->get_value("item_id"), INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$stock_level = $db->f("stock_level");
					$use_stock_level = $db->f("use_stock_level");
					if ($current_sl_action && intval($use_stock_level) > 0) {
						$sql  = " UPDATE " . $table_prefix . "items SET stock_level=" . $db->tosql($stock_level + $r->get_value("quantity"), INTEGER);
						$sql .= " WHERE item_id=" . $db->tosql($r->get_value("item_id"), INTEGER);
						$db->query($sql);
					}
				}
		  
				// set top_order_item_id as 0 if main item was deleted
				$db->query("UPDATE " . $table_prefix . "orders_items SET top_order_item_id=0 WHERE top_order_item_id=" . $db->tosql($order_item_id, INTEGER));
				$db->query("DELETE FROM " . $table_prefix . "orders_items WHERE order_item_id=" . $db->tosql($order_item_id, INTEGER));
				$db->query("DELETE FROM " . $table_prefix . "orders_items_properties WHERE order_item_id=" . $db->tosql($order_item_id, INTEGER));
				$db->query("DELETE FROM " . $table_prefix . "users_points WHERE order_item_id=" . $db->tosql($order_item_id, INTEGER));
		  
		  
				update_order_items($order_id, -1);
				recalculate_order($order_id);
		  
				// add event
				$oe->set_value("event_type", "delete_product");
				$oe->set_value("event_name", $original_item_name);
				$oe->insert_record();
		  
				header("Location: " . $return_page);
				exit;
			} else {
				$is_valid = false;
				$r->errors = DELETE_ALLOWED_ERROR;
			}
		} else {

			// update / add operations
			if ($r->get_value("is_recurring")) {
				$r->change_property("recurring_period", REQUIRED, true);
				$r->change_property("recurring_interval", REQUIRED, true);
				$r->change_property("recurring_interval", MIN_VALUE, 1);
				$r->change_property("recurring_plan_payment", REQUIRED, true);
				$r->change_property("recurring_next_payment", REQUIRED, true);
			}
	  
			if ($r->get_value("is_subscription")) {
				$r->change_property("subscription_period", REQUIRED, true);
				$r->change_property("subscription_interval", REQUIRED, true);
				$r->change_property("subscription_interval", MIN_VALUE, 1);
			}
	  
			// change quantity max value if necessary
			$use_stock_level = 0;
			$sql  = " SELECT stock_level, use_stock_level, disable_out_of_stock, hide_out_of_stock ";
			$sql .= " FROM " . $table_prefix . "items WHERE item_id=" . $db->tosql($r->get_value("item_id"), INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$stock_level = $db->f("stock_level");
				$use_stock_level = $db->f("use_stock_level");
				$disable_out_of_stock = $db->f("disable_out_of_stock");
				$hide_out_of_stock = $db->f("hide_out_of_stock");
				$quantity_limit = ($use_stock_level && ($disable_out_of_stock || $hide_out_of_stock));
				if ($quantity_limit) {
					$r->change_property("quantity", MAX_VALUE, intval($stock_level + $quantity_old));
				}
			}
	  
			$is_valid = $r->validate();
			$is_valid = ($eg->validate() && $is_valid);
	  
			if ($is_valid)
			{
				// comissions and points recalculation
				if (get_param("recalculate_comissions_points")) {
					$sql  = " SELECT i.is_points_price, i.points_price, i.reward_type, i.reward_amount, i.credit_reward_type, i.credit_reward_amount, ";
					$sql .= " it.reward_type AS type_bonus_reward, it.reward_amount AS type_bonus_amount, ";
					$sql .= " it.credit_reward_type AS type_credit_reward, it.credit_reward_amount AS type_credit_amount, ";
					$sql .= " i.merchant_fee_type AS item_merchant_type,	 i.merchant_fee_amount AS item_merchant_amount, ";
					$sql .= " i.affiliate_commission_type AS item_affiliate_type, i.affiliate_commission_amount AS item_affiliate_amount, ";
					$sql .= " it.merchant_fee_type AS type_merchant_type, it.merchant_fee_amount AS type_merchant_amount, i.user_id, ";
					$sql .= " it.affiliate_commission_type AS type_affiliate_type, it.affiliate_commission_amount AS type_affiliate_amount ";	
					$sql .= " FROM (" . $table_prefix . "items i ";
					$sql .= " LEFT JOIN " . $table_prefix . "item_types it ON i.item_type_id=it.item_type_id) ";
					$sql .= " WHERE i.item_id=" . $db->tosql($r->get_value("item_id"), INTEGER);				
					$db->query($sql);
					if ($db->next_record()) {	
						
						$is_points_price      = $db->f("is_points_price");
						$points_price         = $db->f("points_price");
						$reward_type          = $db->f("reward_type");
						$reward_amount        = $db->f("reward_amount");
						if (!strlen($reward_type)) {
							$reward_type = $db->f("type_bonus_reward");
							$reward_amount = $db->f("type_bonus_amount");
						}
						$credit_reward_type   = $db->f("credit_reward_type");
						$credit_reward_amount = $db->f("credit_reward_amount");
						if (!strlen($credit_reward_type)) {
							$credit_reward_type = $db->f("type_credit_reward");
							$credit_reward_amount = $db->f("type_credit_amount");
						}
						if (!strlen($is_points_price)) {
							$is_points_price  = $points_prices;
						}
								
						$item_merchant_type     = $db->f("item_merchant_type");
						$item_merchant_amount   = $db->f("item_merchant_amount");
						$item_affiliate_type    = $db->f("item_affiliate_type");
						$item_affiliate_amount  = $db->f("item_affiliate_amount");
						if (!strlen($item_merchant_type)) {
							$item_merchant_type   = $db->f("type_merchant_type");
							$item_merchant_amount = $db->f("type_merchant_amount");
						}
						if (!strlen($item_affiliate_type)) {
							$item_affiliate_type   = $db->f("type_affiliate_type");
							$item_affiliate_amount = $db->f("type_affiliate_amount");
						}
						$item_user_id = $db->f("user_id");
	  
						if ($r->get_value("points_price")) {
							$r->set_value("points_price", 0);
							if ($points_system) {
								if ($points_price <= 0) {
									$points_price = $r->get_value("real_price") * $points_conversion_rate;
								}
								if ($is_points_price) {
									$r->set_value("points_price", $points_price);
								}
							}
						}					
						
						$r->set_value("reward_points", 0);
						if ($points_system && $reward_points_details) {
							$reward_points = calculate_reward_points($reward_type, $reward_amount, $r->get_value("price"), $r->get_value("buying_price"), $points_conversion_rate, $points_decimals);
							if ($reward_type) {
								$r->set_value("reward_points", number_format($reward_points, $points_decimals));							
							}
						}
			
						$r->set_value("reward_credits", 0);
						if ($credit_system) {
							$reward_credits = calculate_reward_credits($credit_reward_type, $credit_reward_amount, $r->get_value("price"), $r->get_value("buying_price"));				
							if ($credit_reward_type) {
								$r->set_value("reward_credits", currency_format($reward_credits));				
							}
						}					
						
						$merchant_commission = get_merchant_commission(
							$item_user_id, $r->get_value("price") - $r->get_value("discount_amount"), 
							0, $r->get_value("buying_price"), $item_merchant_type, $item_merchant_amount);
						$affiliate_commission = get_affiliate_commission(
							$affiliate_user_id, $r->get_value("price") - $r->get_value("discount_amount"), 
							0, $r->get_value("buying_price"), $item_merchant_type, $item_merchant_amount);
						if ($merchant_commission && $affiliate_commission) {
							if ($affiliate_commission_deduct) {
								$merchant_fee = ( $r->get_value("price") - $r->get_value("discount_amount")) - $merchant_commission;
								if ($merchant_fee < $affiliate_commission) {
									$merchant_commission -= ($affiliate_commission - $merchant_fee);
								}
							} else {
								$merchant_commission -= $affiliate_commission;
							}
						}
				
						$r->set_value("merchant_commission", $merchant_commission);
						$r->set_value("affiliate_commission", $affiliate_commission);					
					}
				}
				if (strlen($order_item_id))
				{
					// update order product if it's allowed for current admin
					if ($update_products) {
						// change stock level if necessary
						$stock_level_delta = $quantity_old - $r->get_value("quantity");
						if ($use_stock_level && $stock_level_delta && $new_sl_action == 1 && $current_sl_action != 1) {
							$sql  = " UPDATE " . $table_prefix . "items ";
							$sql .= " SET stock_level = stock_level + " . $db->tosql($stock_level_delta, INTEGER);
							$sql .= " WHERE item_id=" . $db->tosql($r->get_value("item_id"), INTEGER);
							$db->query($sql);
						}
				  
						$r->update_record();
						$eg->set_values("order_item_id", $order_item_id);
						$eg->set_values("order_id", $order_id);
						$eg->set_values("property_id", 0);
						$eg->update_all($number_properties);
						// add event
						$item_name = $r->get_value("item_name");
						$oe->set_value("event_type", "update_product"); // set event type
						if (trim($item_name) != trim($original_item_name)) {
							$oe->set_value("event_name", $original_item_name." &ndash;&gt; ".$item_name);
						} else {
							$oe->set_value("event_name", $item_name);
						}
						$oe->insert_record();
					} else {
						$is_valid = false;
						$r->errors = UPDATE_ALLOWED_ERROR;
					}
				} else {
					// add new product to the order if it's allowed for current admin
					if ($add_products) {
						$r->insert_record();
						$order_item_id = $db->last_insert_id();
						$r->set_value("order_item_id", $order_item_id);
				  
						$eg->set_values("order_item_id", $order_item_id);
						$eg->set_values("order_id", $order_id);
						$eg->set_values("property_id", 0);
						$eg->insert_all($number_properties);
						// add event
						$oe->set_value("event_type", "add_product"); // set event type
						$oe->set_value("event_name", $r->get_value("item_name"));
						$oe->insert_record();
					} else {
						$is_valid = false;
						$r->errors = INSERT_ALLOWED_ERROR;
					}
				}

				if ($is_valid) {
					update_order_items($order_id, $order_item_id);
					recalculate_order($order_id);
	  
					header("Location: " . $return_page);
					exit;
				}
			}
		}
	}
	elseif (strlen($order_item_id) && !$more_properties)
	{
		$r->get_db_values();
		$eg->set_value("order_item_id", $order_item_id);
		$eg->change_property("item_property_id", USE_IN_SELECT, true);
		$eg->change_property("item_property_id", USE_IN_WHERE, false);
		$eg->change_property("order_item_id", USE_IN_WHERE, true);
		$eg->change_property("order_item_id", USE_IN_SELECT, true);
		$number_properties = $eg->get_db_values();
		if ($number_properties == 0) {
			$number_properties = 5;
		}
	}
	elseif ($more_properties)
	{
		$number_properties += 5;
	}
	else // set default values
	{
		$r->set_value("order_id", $order_id);
		$r->set_value("item_status", $default_status);
		$number_properties = 5;
	}

	// show / hide edit button for product
	$item_id = $r->get_value("item_id");
	if ($item_id) {
		$t->set_var("item_id", htmlspecialchars($item_id));
		$t->set_var("edit_product_style", "display: inline;");
	} else {
		$t->set_var("edit_product_style", "display: none;");
	}
	if ($product_link) {
		$t->parse("edit_product");
	} else {
		$t->set_var("edit_product", "");
	}


	$t->set_var("number_properties", $number_properties);
	$eg->set_parameters_all($number_properties);
	$r->set_parameters();

	if (strlen($order_item_id)) {
		if ($update_products) {
			$t->set_var("save_button_title", UPDATE_BUTTON);
			$t->parse("save_button", false);
		}
		if ($remove_products) {
			$t->parse("delete", false);
		}
	} else {
		if ($add_products) {
			$t->set_var("save_button_title", ADD_PRODUCT_MSG);
			$t->parse("save_button", false);
		}
		$t->set_var("delete", "");
	}

	// set styles for tabs
	$tabs = array(
		"general" => array("title" => PROD_GENERAL_TAB), 
		"shipping" => array("title" => SHIPPING_AND_PACKAGE_MSG), 
		"points" => array("title" => POINTS_MSG), 
		"commissions" => array("title" => COMMISSIONS_MSG), 
		"recurring" => array("title" => RECURRING_OPTIONS_MSG), 
		"subscription" => array("title" => SUBSCRIPTION_OPTIONS_MSG), 
		"options" => array("title" => OPTIONS_VALUES_MSG), 
	);
	parse_tabs($tabs, $tab);
	

	$t->pparse("main");

?>