<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  order_items.php                                          ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	function show_order_items($order_id, $parse_template = true, $page_type = "")
	{
	 	global $t, $db, $table_prefix, $site_id, $settings, $date_show_format;
		global $items_text, $order_items, $total_items, $currency;
		global $cart_properties, $personal_properties, $delivery_properties, $payment_properties;
		global $is_admin_path, $root_folder_path, $is_ssl;
		
		$dbd = new VA_SQL();
		$dbd->DBType       = $db->DBType;
		$dbd->DBDatabase   = $db->DBDatabase;
		$dbd->DBUser       = $db->DBUser;
		$dbd->DBPassword   = $db->DBPassword;
		$dbd->DBHost       = $db->DBHost;
		$dbd->DBPort       = $db->DBPort;
		$dbd->DBPersistent = $db->DBPersistent;

		$order_data = array(); // save here data to return
		// check admin permissions
		$permissions = array();
		if (function_exists("get_permissions")) {
			$permissions = get_permissions();
		}
		$update_orders = get_setting_value($permissions, "update_orders", 0);

		$eol = get_eol();
		// columns settings
		if ($page_type == "admin_invoice_html" || $page_type == "user_invoice_html") {
			$ordinal_number_column = get_setting_value($settings, "invoice_ordinal_number", 0);
			$item_name_column = get_setting_value($settings, "invoice_item_name", 1);
			$item_price_column = get_setting_value($settings, "invoice_item_price", 1);
			$item_tax_percent_column = get_setting_value($settings, "invoice_item_tax_percent", 0);
			$item_tax_column = get_setting_value($settings, "invoice_item_tax", 0);
			$item_price_incl_tax_column = get_setting_value($settings, "invoice_item_price_incl_tax", 0);
			$item_quantity_column = get_setting_value($settings, "invoice_item_quantity", 1);
			$item_price_total_column = get_setting_value($settings, "invoice_item_price_total", 1);
			$item_tax_total_column = get_setting_value($settings, "invoice_item_tax_total", 1);
			$item_price_incl_tax_total_column = get_setting_value($settings, "invoice_item_price_incl_tax_total", 1);
			$item_image_column = get_setting_value($settings, "invoice_item_image", 0);
		} else if ($page_type == "email") {
			$ordinal_number_column = get_setting_value($settings, "email_ordinal_number", 0);
			$item_name_column = get_setting_value($settings, "email_item_name", 1);
			$item_price_column = get_setting_value($settings, "email_item_price", 1);
			$item_tax_percent_column = get_setting_value($settings, "email_item_tax_percent", 0);
			$item_tax_column = get_setting_value($settings, "email_item_tax", 0);
			$item_price_incl_tax_column = get_setting_value($settings, "email_item_price_incl_tax", 0);
			$item_quantity_column = get_setting_value($settings, "email_item_quantity", 1);
			$item_price_total_column = get_setting_value($settings, "email_item_price_total", 1);
			$item_tax_total_column = get_setting_value($settings, "email_item_tax_total", 1);
			$item_price_incl_tax_total_column = get_setting_value($settings, "email_item_price_incl_tax_total", 1);
			$item_image_column = get_setting_value($settings, "email_item_image", 0);
		} else {
			$ordinal_number_column = get_setting_value($settings, "checkout_ordinal_number", 0);
			$item_name_column = get_setting_value($settings, "checkout_item_name", 1);
			$item_price_column = get_setting_value($settings, "checkout_item_price", 1);
			$item_tax_percent_column = get_setting_value($settings, "checkout_item_tax_percent", 0);
			$item_tax_column = get_setting_value($settings, "checkout_item_tax", 0);
			$item_price_incl_tax_column = get_setting_value($settings, "checkout_item_price_incl_tax", 0);
			$item_quantity_column = get_setting_value($settings, "checkout_item_quantity", 1);
			$item_price_total_column = get_setting_value($settings, "checkout_item_price_total", 1);
			$item_tax_total_column = get_setting_value($settings, "checkout_item_tax_total", 1);
			$item_price_incl_tax_total_column = get_setting_value($settings, "checkout_item_price_incl_tax_total", 1);
			$item_image_column = get_setting_value($settings, "checkout_item_image", 0);
		}

		$global_tax_prices_type = get_setting_value($settings, "tax_prices_type", 0);
		$global_tax_round = get_setting_value($settings, "tax_round", 1);
		$tax_prices = get_setting_value($settings, "tax_prices", 0);
		$tax_note = get_translation(get_setting_value($settings, "tax_note", ""));
		$tax_note_excl = get_translation(get_setting_value($settings, "tax_note_excl", ""));
		$points_decimals = get_setting_value($settings, "points_decimals", 0);
		$weight_measure = get_setting_value($settings, "weight_measure", "");

		// option delimiter and price options
		$option_name_delimiter = get_setting_value($settings, "option_name_delimiter", ": "); 
		$option_positive_price_right = get_setting_value($settings, "option_positive_price_right", ""); 
		$option_positive_price_left = get_setting_value($settings, "option_positive_price_left", ""); 
		$option_negative_price_right = get_setting_value($settings, "option_negative_price_right", ""); 
		$option_negative_price_left = get_setting_value($settings, "option_negative_price_left", "");

		$orders_currency = get_setting_value($settings, "orders_currency", 0);
		if ($page_type == "admin_invoice_html" || $page_type == "user_invoice_html") {
			$show_item_code = get_setting_value($settings, "item_code_invoice", 0);
			$show_manufacturer_code = get_setting_value($settings, "manufacturer_code_invoice", 0);
			$show_item_weight = get_setting_value($settings, "item_weight_invoice", 0);
			$show_actual_weight = get_setting_value($settings, "actual_weight_invoice", 0);
			$show_total_weight = get_setting_value($settings, "total_weight_invoice", 0);
			$show_total_actual_weight = get_setting_value($settings, "total_actual_weight_invoice", 0);
			$show_points_price = get_setting_value($settings, "points_price_invoice", 0);
			$show_reward_points = get_setting_value($settings, "reward_points_invoice", 0);
			$show_reward_credits = get_setting_value($settings, "reward_credits_invoice", 0);
		} elseif ($page_type == "order_info" || $page_type == "admin_order"  || $page_type == "cc_info" || $page_type == "order_confirmation") {
			$show_item_code = get_setting_value($settings, "item_code_checkout", 0);
			$show_manufacturer_code = get_setting_value($settings, "manufacturer_code_checkout", 0);
			$show_item_weight = get_setting_value($settings, "item_weight_checkout", 0);
			$show_actual_weight = get_setting_value($settings, "actual_weight_checkout", 0);
			$show_total_weight = get_setting_value($settings, "total_weight_checkout", 0);
			$show_total_actual_weight = get_setting_value($settings, "total_actual_weight_checkout", 0);
			$show_points_price = get_setting_value($settings, "points_price_checkout", 0);
			$show_reward_points = get_setting_value($settings, "reward_points_checkout", 0);
			$show_reward_credits = get_setting_value($settings, "reward_credits_checkout", 0);
		} else {
			$show_item_code = get_setting_value($settings, "item_code_checkout", 0);
			$show_manufacturer_code = get_setting_value($settings, "manufacturer_code_checkout", 0);
			$show_item_weight = get_setting_value($settings, "item_weight_checkout", 0);
			$show_actual_weight = get_setting_value($settings, "actual_weight_checkout", 0);
			$show_total_weight = get_setting_value($settings, "total_weight_checkout", 0);
			$show_total_actual_weight = get_setting_value($settings, "total_actual_weight_checkout", 0);
			$show_points_price = get_setting_value($settings, "points_price_checkout", 0);
			$show_reward_points = get_setting_value($settings, "reward_points_checkout", 0);
			$show_reward_credits = get_setting_value($settings, "reward_credits_checkout", 0);
		}

		$session_user_id = get_session("session_user_id");
		if ($page_type == "user_invoice_html" || $page_type == "order_info" || $page_type == "cc_info" || $page_type == "order_confirmation") {
			$reward_credits_users = get_setting_value($settings, "reward_credits_users", 0);
		} else {
			$reward_credits_users = 0;
		}

		// get order tax rates
		$tax_available = false; $tax_percent_sum = 0; $tax_names = "";	$tax_column_names = "";	$taxes_total = 0; 
		$order_tax_rates = order_tax_rates($order_id);
		if (sizeof($order_tax_rates) > 0) {
			$tax_available = true;
			foreach ($order_tax_rates as $tax_id => $tax_info) {
				$show_type = $tax_info["show_type"];
				$tax_type = $tax_info["tax_type"];
				if ($tax_type == 1) {
					// sum only general tax 
					$tax_percent_sum += $tax_info["tax_percent"];
				}
				if ($show_type&1) {
					if ($tax_column_names) { $tax_column_names .= " & "; }
					$tax_column_names .= get_translation($tax_info["tax_name"]);
				}
				if ($tax_names) { $tax_names .= " & "; }
				$tax_names .= get_translation($tax_info["tax_name"]);
			}
		}

		// init order variables
		$goods_total_incl_tax = 0; 
		$properties_total = 0; $properties_taxable = 0; $properties_points_amount = 0; $properties_incl_tax = 0; $properties_excl_tax = 0;
		$shipments_incl_tax = 0; $shipments_excl_tax = 0;
		$total_discount_incl_tax = 0; $vouchers_amount = 0;
		$total_reward_points = 0; $total_reward_credits = 0; 					
		$total_merchants_commission = 0; $total_affiliate_commission = 0; 

		// get information about order
		$sql  = " SELECT o.user_type_id, o.site_id, o.coupons_ids, o.vouchers_ids, o.total_discount, o.total_discount_tax, o.shipping_type_desc, ";
		$sql .= " o.shipping_cost, o.shipping_taxable, o.tax_name, o.tax_percent, o.vouchers_amount, ";
		$sql .= " o.processing_fee, o.processing_tax_free, o.shipping_type_id, o.country_id, o.state_id, o.delivery_state_id, ";
		$sql .= " o.tax_prices_type, o.weight_total, o.actual_weight_total, o.order_placed_date, ";
		$sql .= " o.currency_code, o.currency_rate, c.symbol_right, c.symbol_left, c.decimals_number, c.decimal_point, c.thousands_separator, ";
		$sql .= " o.shipping_points_amount, o.total_points_amount, o.credit_amount, o.total_reward_credits, o.total_reward_points, ";
		$sql .= " o.order_status, os.status_type, os.payment_allowed, o.order_total, o.paid_total, o.payment_amount ";
		$sql .= " FROM ((" . $table_prefix . "orders o ";
		$sql .= " LEFT JOIN " . $table_prefix . "currencies c ON o.currency_code=c.currency_code) ";
		$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
		$sql .= " WHERE o.order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		$db->next_record();
		// get order values

		$order_user_type_id = $db->f("user_type_id");
		$order_site_id = $db->f("site_id");
		$order_status = $db->f("order_status");
		$order_status_type = $db->f("status_type");
		$payment_allowed = $db->f("payment_allowed");
		$order_data["status_type"] = $order_status_type;
		$order_data["payment_allowed"] = $payment_allowed;
		$tax_available = sizeof($order_tax_rates);
		$tax_prices_type = $db->f("tax_prices_type");
		if (!strlen($tax_prices_type)) {
			$tax_prices_type = $global_tax_prices_type;
		}
		$tax_round = $db->f("tax_round_type");
		if (!strlen($tax_round)) {
			$tax_round = $global_tax_round;
		}

		$order_coupons_ids = $db->f("coupons_ids");
		$vouchers_ids = $db->f("vouchers_ids");
		$vouchers_amount = $db->f("vouchers_amount");

		$total_discount = doubleval($db->f("total_discount"));
		$total_discount_tax = doubleval($db->f("total_discount_tax"));
		$paid_total = doubleval($db->f("paid_total"));
		$payment_amount = doubleval($db->f("payment_amount"));

		// shipping data for older orders
		$old_shipping_type_id = $db->f("shipping_type_id");
		$old_shipping_type_desc = get_translation($db->f("shipping_type_desc"));
		$old_shipping_type_code = $db->f("shipping_type_code");
		$old_shipping_cost = $db->f("shipping_cost");
		$old_shipping_taxable = $db->f("shipping_taxable");
		$old_shipping_points_amount = $db->f("shipping_points_amount");
		$old_shipping_tracking_id = $db->f("shipping_tracking_id");
		$old_shipping_expecting_date = $db->f("shipping_expecting_date", DATETIME);

		$credit_amount = doubleval($db->f("credit_amount"));
		$total_reward_credits = doubleval($db->f("total_reward_credits"));
		$total_reward_points = doubleval($db->f("total_reward_points"));


		$country_id = $db->f("delivery_country_id");
		$state_id = $db->f("delivery_state_id");
		$weight_total = $db->f("weight_total");
		$actual_weight_total = $db->f("actual_weight_total");
		$order_placed_date = $db->f("order_placed_date", DATETIME);
		$order_date = va_date($date_show_format, $order_placed_date);
		if (!$country_id) {
			$country_id = $db->f("country_id");
		}
		if (!$state_id) {
			$state_id = $db->f("state_id");
		}
		// get order currency
		$order_currency = array();
		$order_currency_code = $db->f("currency_code");
		$order_currency_rate= $db->f("currency_rate");
		$order_currency["code"] = $db->f("currency_code");
		$order_currency["rate"] = $db->f("currency_rate");
		$order_currency["left"] = $db->f("symbol_left");
		$order_currency["right"] = $db->f("symbol_right");
		$order_currency["decimals"] = $db->f("decimals_number");
		$order_currency["point"] = $db->f("decimal_point");
		$order_currency["separator"] = $db->f("thousands_separator");

		if ($orders_currency != 1) {
			$order_currency["left"] = $currency["left"];
			$order_currency["right"] = $currency["right"];
			$order_currency["decimals"] = $currency["decimals"];
			$order_currency["point"] = $currency["point"];
			$order_currency["separator"] = $currency["separator"];
			if (strtolower($currency["code"]) != strtolower($order_currency_code)) {
				$order_currency["rate"] = $currency["rate"];
			}
		}
		$total_points_amount = $db->f("total_points_amount");

		// get order site settings 
		if ($site_id == $order_site_id) {
			$order_site_settings = $settings;
		} else {
			$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE (setting_type='global' OR setting_type='products') ";
			$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($order_site_id, INTEGER, true, false) . ")";
			$sql .= " ORDER BY site_id ASC ";
			$db->query($sql);
			while ($db->next_record()) {
				$order_site_settings[$db->f("setting_name")] = $db->f("setting_value");
			}
		} 

		if ($parse_template) {
			if ($order_status_type == "CREDIT_NOTE") {
				$t->set_var("INVOICE_TO_MSG", va_constant("CREDIT_TO_MSG"));
				$t->set_var("INVOICE_DATE_MSG", va_constant("CREDIT_DATE_MSG"));
				$t->set_var("INVOICE_NUMBER_MSG", va_constant("CREDIT_NUMBER_MSG"));
			} else {
				$t->set_var("INVOICE_TO_MSG", va_constant("INVOICE_TO_MSG"));
				$t->set_var("INVOICE_DATE_MSG", va_constant("INVOICE_DATE_MSG"));
				$t->set_var("INVOICE_NUMBER_MSG", va_constant("INVOICE_NUMBER_MSG"));
			}
			$t->set_var("items", "");
			$t->set_var("cart_properties", "");
			$t->set_var("personal_properties", "");
			$t->set_var("delivery_properties", "");
			$t->set_var("shipping_properties", "");
			$t->set_var("payment_properties", "");

			$t->set_var("order_coupons", "");
			$t->set_var("discount", "");
			$t->set_var("shipping_type", "");
			$t->set_var("taxes", "");
			$t->set_var("credit_amount_block", "");
			$t->set_var("fee", "");
			$t->set_var("total_points_block", "");

			if ($order_currency_rate != 1) {
				$t->set_var("order_currency_code", $order_currency_code);
				$t->set_var("order_currency_rate", $order_currency_rate);
				$t->sparse("order_currency", false);
			}


			$t->set_var("tax_name", $tax_column_names); 
			$t->set_var("tax_note", $tax_note);
			$t->set_var("tax_note_incl", $tax_note);
			$t->set_var("tax_note_excl", $tax_note_excl);
			// set titles for cart columns
			set_cart_titles($order_site_settings);

			$t->set_var("points_msg", strtolower(va_constant("POINTS_MSG")));
			if ($page_type == "admin_order" ) {
				$properties_colspan = 1; $total_columns = 1;
			} else {
				$properties_colspan = 0; $total_columns = 0;
			}
			if ($ordinal_number_column) {
				$properties_colspan++;
				$total_columns++;
				$t->sparse("ordinal_number_header", false);
			}
			if ($item_image_column) {
				$properties_colspan++;
				$total_columns++;
				$t->sparse("item_image_header", false);
			}
			if ($item_name_column) {
				$properties_colspan++;
				$total_columns++;
				$t->sparse("item_name_header", false);
			}
			if ($item_price_column || ($item_price_incl_tax_column && !$tax_available)) {
				$item_price_column = true;
				$properties_colspan++;
				$total_columns++;
				$t->sparse("item_price_header", false);
			}
			if ($item_tax_percent_column && $tax_available) {
				$properties_colspan++;
				$total_columns++;
				$t->sparse("item_tax_percent_header", false);
			} else {
				$item_tax_percent_column = false;
			}
			if ($item_tax_column && $tax_available) {
				$properties_colspan++;
				$total_columns++;
				$t->sparse("item_tax_header", false);
			} else {
				$item_tax_column = false;
			}
			if ($item_price_incl_tax_column && $tax_available) {
				$properties_colspan++;
				$total_columns++;
				$t->sparse("item_price_incl_tax_header", false);
			} else {
				$item_price_incl_tax_column = false;
			}
			$goods_colspan = $properties_colspan;
			if ($item_quantity_column) {
				$properties_colspan++;
				$total_columns++;
				$t->sparse("item_quantity_header", false);
			}
			if ($item_price_total_column || ($item_price_incl_tax_total_column && !$tax_available)) {
				$item_price_total_column = true;
				$total_columns++;
				$t->sparse("item_price_total_header", false);
			}
			if ($item_tax_total_column && $tax_available) {
				$total_columns++;
				$t->sparse("item_tax_total_header", false);
			} else {
				$item_tax_total_column = false;
			}
			if ($item_price_incl_tax_total_column && $tax_available) {
				$total_columns++;
				$t->sparse("item_price_incl_tax_total_header", false);
			} else {
				$item_price_incl_tax_total_column = false;
			}
			$sc_colspan = $total_columns - 1;
			$t->set_var("properties_colspan", $properties_colspan);
			$t->set_var("goods_colspan", $goods_colspan);
			$t->set_var("sc_colspan", $sc_colspan);
			$t->set_var("total_columns", $total_columns);
		}

		// check shipments
		$orders_shipments = array(); $total_shipping_cost = 0;
		// New multi-shipping structure 
		$sql  = " SELECT os.*,sm.tracking_url,sc.company_name,sc.company_url FROM " . $table_prefix . "orders_shipments os ";
		$sql .= " LEFT JOIN " . $table_prefix . "shipping_types st ON os.shipping_id=st.shipping_type_id ";
		$sql .= " LEFT JOIN " . $table_prefix . "shipping_modules sm ON sm.shipping_module_id=st.shipping_module_id ";
		$sql .= " LEFT JOIN " . $table_prefix . "shipping_companies sc ON sc.shipping_company_id=os.shipping_company_id ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$order_shipping_id = $db->f("order_shipping_id");
			$shipping_cost = $db->f("shipping_cost");
			$points_cost = $db->f("points_cost");
			$shipping_tax_free = $db->f("tax_free");
			$expecting_date = $db->f("expecting_date", DATETIME);
			$order_items_ids = $db->f("order_items_ids");
			$shipping_desc = get_translation($db->f("shipping_desc"));

			$orders_shipments[$order_shipping_id] = $db->Record;
			$orders_shipments[$order_shipping_id]["expecting_date"] = $expecting_date;
			$orders_shipments[$order_shipping_id]["order_items_ids"] = array_flip(explode(",", $order_items_ids));
			$orders_shipments[$order_shipping_id]["shipping_desc"] = $shipping_desc;
		}
		// Old shipping structure for older orders
		if (!is_array($orders_shipments) && strlen($old_shipping_type_desc)) {
			$orders_shipments[0] = array(
		  	"order_shipping_id" => 0,
			  "order_id" => $order_id,
			  "shipping_id" => $old_shipping_type_id,
			  "shipping_code" => $old_shipping_type_code,
			  "shipping_desc" => $old_shipping_type_desc,
			  "shipping_cost" => $old_shipping_cost,
			  "points_cost" => $old_shipping_points_amount,
			  "tax_free" => !($old_shipping_taxable),
			  "tracking_id" => $old_shipping_tracking_id,
			  "expecting_date" => $old_shipping_expecting_date,
			  "goods_weight" => $weight_total,
			  "actual_goods_weight" => $actual_weight_total,
				"tare_weight" => 0,
				"order_items_ids" => "",
				"company_name" => "",
				"company_url" => "",
			);
		}

		foreach ($orders_shipments as $order_shipping_id => $order_shipping) {
			$shipping_cost = $order_shipping["shipping_cost"];
			$shipping_tax_free = $order_shipping["tax_free"];

			// calculate tax and total values
			$shipping_tax_id = 0;
			$shipping_tax_values = get_tax_amount($order_tax_rates, "shipping", $shipping_cost, 1, $shipping_tax_id, $shipping_tax_free, $shipping_tax_percent, "", 2, $tax_prices_type, $tax_round);
			$shipping_tax_total = add_tax_values($order_tax_rates, $shipping_tax_values, "shipping", $tax_round);
			if ($tax_prices_type == 1) {
				$shipping_cost_excl_tax = $shipping_cost - $shipping_tax_total;
				$shipping_cost_incl_tax = $shipping_cost;
			} else {
				$shipping_cost_excl_tax = $shipping_cost;
				$shipping_cost_incl_tax = $shipping_cost + $shipping_tax_total;
			}
			$shipments_excl_tax += $shipping_cost_excl_tax;
			$shipments_incl_tax += $shipping_cost_incl_tax; 

			$orders_shipments[$order_shipping_id]["shipping_cost_excl_tax"] = $shipping_cost_excl_tax;
			$orders_shipments[$order_shipping_id]["shipping_tax"] = $shipping_tax_total;
			$orders_shipments[$order_shipping_id]["shipping_cost_incl_tax"] = $shipping_cost_incl_tax;
			$total_shipping_cost += $shipping_cost;
		}

		// site utl and image settings
		$site_url = get_setting_value($order_site_settings, "site_url", "");
		$secure_url = get_setting_value($order_site_settings, "secure_url", "");
		product_image_names($item_image_column, $image_type_name, $image_field, $image_alt_field, $watermark_name, $no_image_name);
		$watermark = get_setting_value($order_site_settings, $watermark_name, 0);
		$product_no_image = get_setting_value($order_site_settings, $no_image_name, "");
		$restrict_products_images = get_setting_value($order_site_settings, "restrict_products_images", "");		

		// get order profile settings
		$order_info = array();
		$sql  = "SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
		$sql .= "WHERE setting_type='order_info'";
		$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($order_site_id, INTEGER, true, false) . ")";
		$sql .= " ORDER BY site_id ASC ";
		$db->query($sql);
		while ($db->next_record()) {
			$order_info[$db->f("setting_name")] = $db->f("setting_value");
		}
		$subcomponents_show_type = get_setting_value($order_info, "subcomponents_show_type", 0);

		// check statuses and their access levels for order page
		$see_statuses_ids = array();
		$set_statuses_ids = array();
		$update_statuses_ids = array();
		$set_statuses = array(array("", va_constant("SELECT_PRODUCT_STATUS_MSG")));
		if ($page_type == "admin_order") {
			$privilege_id = get_session("session_admin_privilege_id");
			$sql = " SELECT os.* ";
			$sql.= " FROM (" . $table_prefix . "order_statuses os ";
			// sub join select 
			$sql .= " INNER JOIN (";
			$sql .= " SELECT jos.status_id ";
			$sql .= " FROM (".$table_prefix."order_statuses jos ";
			$sql .= " LEFT JOIN " . $table_prefix . "order_statuses_sites joss ON jos.status_id=joss.status_id) ";
			$sql .= " WHERE jos.is_active=1 ";
			$sql .= " AND (jos.sites_all=1 OR joss.site_id=" . $db->tosql($order_site_id, INTEGER).") ";
			$sql .= " GROUP BY jos.status_id ";
			$sql .= " ) j ON j.status_id=os.status_id) ";
			// end sub join select 
			$sql.= " ORDER BY os.status_order, os.status_id";
			$db->query($sql);
			while ($db->next_record()) {
				$status_id = $db->f("status_id");
				$status_name = get_translation($db->f("status_name"));
    
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
		}

		if ( strlen($order_status) && !in_array($order_status, $update_statuses_ids)) {
			$update_orders = false;
		}

		$order_items = array();
		$items_text = ""; $order_items_ids = ""; $cart_items = array(); $items_taxes = array();
		$goods_total = 0; $goods_tax_total = 0; $goods_tax_total_show = 0; $goods_total_excl_tax = 0; $goods_total_incl_tax = 0;
		$total_quantity = 0; $total_items = 0; $shipping_quantity = 0;
		$sql  = " SELECT oi.order_item_id,oi.top_order_item_id,oi.item_id,oi.item_user_id,oi.item_type_id,";
		$sql .= " oi.item_status,os.status_name AS item_status_desc,oi.item_code,oi.manufacturer_code, oi.component_name, oi.item_name, ";
		$sql .= " oi.is_recurring, oi.recurring_last_payment, oi.recurring_next_payment, oi.downloadable, ";
		$sql .= " oi.price,oi.tax_id,oi.tax_free,oi.tax_percent,oi.discount_amount,oi.real_price, oi.weight, oi.actual_weight, ";
		$sql .= " oi.buying_price,oi.points_price,oi.reward_points,oi.reward_credits,oi.quantity,oi.coupons_ids, ";
		$sql .= " oi.is_shipping_free, oi.order_shipping_id, ";
		$sql .= " oi.is_subscription, oi.subscription_id, oi.subscription_start_date, oi.subscription_expiry_date, ";
		// merchant data
		$sql .= " oi.merchant_commission, mu.email, mu.name, mu.first_name, mu.last_name, mu.cell_phone, ";
		$sql .= " mu.email, mu.name, mu.first_name, mu.last_name, mu.cell_phone, ";
		// affiliate fields
		$sql .= " oi.affiliate_user_id, oi.affiliate_commission, af.email AS af_email, af.name AS af_name, ";
		$sql .= " af.first_name AS af_first_name, af.last_name AS af_last_name, af.cell_phone AS af_cell_phone, ";
		// supplier fields
		$sql .= " sp.supplier_id, sp.supplier_email, sp.supplier_name, ";
		$sql .= " sp.short_description AS supplier_short_desc, sp.full_description AS supplier_full_desc ";
		$sql .= " FROM ((((" . $table_prefix . "orders_items oi ";
		$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON oi.item_status=os.status_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "users mu ON oi.item_user_id=mu.user_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "users af ON oi.affiliate_user_id=af.user_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "suppliers sp ON oi.supplier_id=sp.supplier_id) ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		// for merchant - show only his products
		if ($page_type == "user_merchant_order") {
			$sql .= " AND oi.item_user_id=" . $db->tosql(get_session("session_user_id"), INTEGER);
		}
		$db->query($sql);
		while ($db->next_record()) {
			$order_item_id = $db->f("order_item_id");
			$top_order_item_id = $db->f("top_order_item_id");
			$is_shipping_free = $db->f("is_shipping_free");
			$item_shipping_id = $db->f("order_shipping_id");

			$item_status = $db->f("item_status");
			$item_status_desc = get_translation($db->f("item_status_desc"));
			if (!strlen($item_status_desc)) {
				$item_status_desc = va_constant("ID_MSG").": [".$item_status."]";
			}
			$item_type_id = $db->f("item_type_id");

			$selection_name = get_translation($db->f("component_name"));
			$item_name = get_translation($db->f("item_name"));
			$item_code = $db->f("item_code");
			$manufacturer_code = $db->f("manufacturer_code");
			$is_recurring = $db->f("is_recurring");
			$recurring_last_payment = $db->f("recurring_last_payment", DATETIME);
			$recurring_next_payment = $db->f("recurring_next_payment", DATETIME);

			$price = $db->f("price");
			$quantity = $db->f("quantity");
			$item_tax_id = $db->f("tax_id");
			$item_tax_free = $db->f("tax_free");

			$item_total = $price * $quantity;

			if (!$is_shipping_free) {
				$shipping_quantity += $quantity;
			}

			// calculate reward totals
			$reward_points = doubleval($db->f("reward_points"));
			$reward_credits = doubleval($db->f("reward_credits"));
			$merchant_commission = doubleval($db->f("merchant_commission"));
			$affiliate_commission = doubleval($db->f("affiliate_commission"));
			$total_reward_points += ($reward_points * $quantity); 
			$total_reward_credits += ($reward_credits * $quantity); 
			$total_merchants_commission += ($merchant_commission * $quantity); 
			$total_affiliate_commission += ($affiliate_commission * $quantity); 

			// new
			$item_tax = get_tax_amount($order_tax_rates, $item_type_id, $price, 1, $item_tax_id, $item_tax_free, $item_tax_percent, "", 1, $tax_prices_type, $tax_round);
			$item_tax_values = get_tax_amount($order_tax_rates, $item_type_id, $price, 1, $item_tax_id, $item_tax_free, $item_tax_percent, "", 2, $tax_prices_type, $tax_round);
			$item_tax_total_values = get_tax_amount($order_tax_rates, $item_type_id, $item_total, $quantity, $item_tax_id, $item_tax_free, $item_tax_percent, "", 2, $tax_prices_type, $tax_round);
			$item_tax_total = add_tax_values($order_tax_rates, $item_tax_total_values, "products", $tax_round);

			if ($tax_prices_type == 1) {
				$price_excl_tax = $price - $item_tax;
				$price_incl_tax = $price;
				$price_excl_tax_total = $item_total - $item_tax_total;
				$price_incl_tax_total = $item_total;
			} else {
				$price_excl_tax = $price;
				$price_incl_tax = $price + $item_tax;
				$price_excl_tax_total = $item_total;
				$price_incl_tax_total = $item_total + $item_tax_total;
			}
			// check if components was saved before
			$components = isset($order_items[$order_item_id]) ? $order_items[$order_item_id]["components"] : array();
			$order_items[$order_item_id] = array(
				"id" => $order_item_id,
				"top_order_item_id" => $top_order_item_id,
				"item_id" => $db->f("item_id"), "item_type_id" => $db->f("item_type_id"),
				"item_status" => $item_status, "item_status_desc" => $item_status_desc,
				"item_code" => $item_code, "manufacturer_code" => $manufacturer_code,
				"selection_name" => $selection_name, "item_name" => $item_name,
				"is_recurring" => $is_recurring, 
				"recurring_last_payment" => $recurring_last_payment, "recurring_next_payment" => $recurring_next_payment,
				"price" => $price, "quantity" => $quantity, "item_total" => $item_total,
				"price_excl_tax" => $price_excl_tax, "price_incl_tax" => $price_incl_tax,
				"price_excl_tax_total" => $price_excl_tax_total, "price_incl_tax_total" => $price_incl_tax_total,
				"item_tax" => $item_tax, "item_tax_total" => $item_tax_total,
				"item_tax_values" => $item_tax_values, "item_tax_total_values" => $item_tax_total_values,
				"tax_id" => $item_tax_id, "tax_free" => $item_tax_free, "tax_percent" => $item_tax_percent,
				"weight" => $db->f("weight"), "actual_weight" => $db->f("actual_weight"),
				"downloadable" => $db->f("downloadable"),
				"discount_amount" => $db->f("discount_amount"),
				"buying_price" => $db->f("buying_price"),
				"real_price" => $db->f("real_price"),
				"points_price" => $db->f("points_price"),
				"reward_points" => $db->f("reward_points"),
				"reward_credits" => $db->f("reward_credits"),
				"coupons_ids" => $db->f("coupons_ids"),
				"item_shipping_id" => $item_shipping_id,
				"merchant_commission" => $db->f("merchant_commission"), "merchant_id" => $db->f("item_user_id"), 
				"merchant_email" => $db->f("email"), "merchant_name" => $db->f("name"), "merchant_first_name" => $db->f("first_name"),
				"merchant_last_name" => $db->f("last_name"), "merchant_cell_phone" => $db->f("cell_phone"),
				"affiliate_id" => $db->f("affiliate_user_id"), "affiliate_commission" => $db->f("affiliate_commission"), 
				"affiliate_email" => $db->f("af_email"), "affiliate_name" => $db->f("af_name"), 
				"affiliate_first_name" => $db->f("af_first_name"), "affiliate_last_name" => $db->f("af_last_name"), 
				"affiliate_cell_phone" => $db->f("af_cell_phone"),
				"supplier_id" => $db->f("supplier_id"), "supplier_email" => $db->f("supplier_email"),
				"supplier_name" => $db->f("supplier_name"), "supplier_short_desc" => $db->f("supplier_short_desc"),
				"supplier_full_desc" => $db->f("supplier_full_desc"), "supplier_cell_phone" => "",
				"is_subscription" => $db->f("is_subscription"), "subscription_id" => $db->f("subscription_id"),
				"subscription_start_date" => $db->f("subscription_start_date", DATETIME), 
				"subscription_expiry_date" => $db->f("subscription_expiry_date", DATETIME),
				"components" => $components,
				"stock_level" => 0, "use_stock_level" => 0, "short_description" => "", "full_description" => "", 
				"tiny_image" => "", "small_image" => "", "big_image" => "", "super_image" => "",
				"tiny_image_alt" => "", "small_image_alt" => "", "big_image_alt" => "", "super_image_alt" => "",
			);
			if ($top_order_item_id) {
				$order_items[$top_order_item_id]["components"][] = $order_item_id;
			}
		}

		// check if all top order items exists and remove them if not
		foreach ($order_items as $order_item_id => $item) {
			if (!isset($item["id"])) { 
				unset($order_items[$order_item_id]); 
				// clear top order item as it's not exists anymore
				$components = $item["components"];
				foreach ($components as $order_item_id) {
					if (isset($order_items[$order_item_id])) { $order_items[$order_item_id]["top_order_item_id"] = ""; }
				}
			}
		}

		// get additional data from items table
		$items_fields = array(
			"stock_level", "use_stock_level", "short_description", "full_description", 
			"tiny_image", "small_image", "big_image", "super_image",
			"tiny_image_alt", "small_image_alt", "big_image_alt", "super_image_alt",
		);
		foreach ($order_items as $order_item_id => $item)
		{
			$item_id = $item["item_id"];
			if ($item_id) {
				$sql  = " SELECT stock_level, use_stock_level, short_description, full_description, ";
				$sql .= " tiny_image, small_image, big_image, super_image, ";
				$sql .= " tiny_image_alt, small_image_alt, big_image_alt, super_image_alt ";
				$sql .= " FROM " . $table_prefix . "items ";
				$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					for($f = 0; $f < sizeof($items_fields); $f++) {
						$field_name = $items_fields[$f];
						$order_items[$order_item_id][$field_name] = $db->f($field_name);
					}
				}			
			}

			// get options for every product
			$item_properties = ""; $item_properties_text = ""; $properties_values_ids = array();
			$sql  = " SELECT property_name, hide_name, property_value, property_values_ids, additional_price, length_units ";
			$sql .= " FROM " . $table_prefix . "orders_items_properties ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$sql .= " AND order_item_id=" . $db->tosql($order_item_id, INTEGER);
			$sql .= " ORDER BY property_order, property_id, item_property_id";
			$dbd->query($sql);
			while ($dbd->next_record()) {
				$property_name = get_translation($dbd->f("property_name"));
				$hide_name = $dbd->f("hide_name");
				$property_value = get_translation($dbd->f("property_value"));
				$property_values_ids = trim($dbd->f("property_values_ids"));
				if ($property_values_ids) {
					$properties_values_ids = array_merge($properties_values_ids, explode(",", $property_values_ids));
				}
				$property_price = $dbd->f("additional_price");
				$length_units = $dbd->f("length_units");
				if ($hide_name) {
					$property_text = $property_value;
				} else {
					$property_text = $property_name . $option_name_delimiter . $property_value;
				}
				if (strlen($property_text)) {
					if ($length_units) {
						$property_text .= " " . strtoupper($length_units);
					}
					// get tax amount to show for product option
					$property_price_tax = get_tax_amount($order_tax_rates, $item_type_id, $property_price, 1, $item_tax_id, $item_tax_free, $item_property_tax_percent, "", 1, $tax_prices_type, $tax_round);
					if ($property_price > 0) {
						$property_text .= $option_positive_price_right . currency_format($property_price, $order_currency, $property_price_tax) . $option_positive_price_left;
					} elseif ($property_price < 0) {
						$property_text .= $option_negative_price_right . currency_format(abs($property_price), $order_currency, $property_price_tax) . $option_negative_price_left;
					}				
					$item_properties .= "<br/>" . $property_text;
					if ($item_properties_text) { $item_properties_text .= "; "; }
					$item_properties_text .= $property_text;
				}
			}
			// check if properties has images to update original image
			if (count($properties_values_ids)) {
				$sql  = " SELECT image_tiny, image_small, image_large, image_super ";
				$sql .= " FROM ".$table_prefix."items_properties_values WHERE item_property_id IN (".$dbd->tosql($properties_values_ids, INTEGERS_LIST).")";
				$sql .= " ORDER BY value_order ";
				$dbd->query($sql);
				while ($dbd->next_record()) {
					$image_tiny = $dbd->f("image_tiny");
					$image_small = $dbd->f("image_small");
					$image_large = $dbd->f("image_large");
					$image_super = $dbd->f("image_super");
					if ($image_tiny || $image_small || $image_large || $image_super) { 
						$order_items[$order_item_id]["tiny_image"] = $image_tiny; 
						$order_items[$order_item_id]["small_image"] = $image_small; 
						$order_items[$order_item_id]["big_image"] = $image_large; 
						$order_items[$order_item_id]["super_image"] = $image_super; 
					}
				}
			}

			// update order_items array with properties info
			$order_items[$order_item_id]["html_item_properties"] = $item_properties;
			$order_items[$order_item_id]["text_item_properties"] = $item_properties_text;
		}


		$ordinal_number = 0;
		foreach ($order_items as $order_item_id => $item)
		{
			$total_items++;
			$top_order_item_id = $item["top_order_item_id"];
			if ($subcomponents_show_type == 1 && $top_order_item_id && isset($order_items[$top_order_item_id])) {
				// component already shown with parent product
				continue;
			}
			$ordinal_number++;
			$item_id = $item["item_id"];
			$item_status = $item["item_status"];
			$item_status_desc = $item["item_status_desc"];
			$real_price = $item["real_price"];
			$coupons_ids = $item["coupons_ids"];
			$item_shipping_id = $item["item_shipping_id"];

			$price = $item["price"];
			$item_tax_id = $item["tax_id"];
			$item_tax_free = $item["tax_free"];
			$item_tax_percent = $item["tax_percent"];
			$discount_amount = $item["discount_amount"];
			$real_price = $item["real_price"];
			$quantity = $item["quantity"];
			$weight = $item["weight"];
			$actual_weight = $item["actual_weight"];
			$buying_price = $item["buying_price"];
			$points_price = $item["points_price"];
			$reward_points = $item["reward_points"];
			$reward_credits = $item["reward_credits"];
			$downloadable = $item["downloadable"];
			$item_type_id = $item["item_type_id"];

			// variables from items table
			$stock_level = $item["stock_level"];
			$use_stock_level = $item["use_stock_level"];
			$short_description = $item["short_description"];
			$full_description = $item["full_description"];

			$item_total = $item["item_total"];
			$item_tax = $item["item_tax"];
			$item_tax_values = $item["item_tax_values"];
			$item_tax_total_values = $item["item_tax_total_values"];
			$item_tax_total = $item["item_tax_total"];
			$price_excl_tax = $item["price_excl_tax"];
			$price_incl_tax = $item["price_incl_tax"];
			$price_excl_tax_total = $item["price_excl_tax_total"];
			$price_incl_tax_total = $item["price_incl_tax_total"];

			// merchant fields
			$merchant_id = $item["merchant_id"];
			$merchant_commission = $item["merchant_commission"];
			$merchant_email = $item["merchant_email"];
			$merchant_name = $item["merchant_name"];
			$merchant_first_name = $item["merchant_first_name"];
			$merchant_last_name = $item["merchant_last_name"];
			$merchant_cell_phone = $item["merchant_cell_phone"];

			// affiliate fields
			$affiliate_id = $item["affiliate_id"];
			$affiliate_commission = $item["affiliate_commission"];
			$affiliate_email = $item["affiliate_email"];
			$affiliate_name = $item["affiliate_name"];
			$affiliate_first_name = $item["affiliate_first_name"];
			$affiliate_last_name = $item["affiliate_last_name"];
			$affiliate_cell_phone = $item["affiliate_cell_phone"];

			// supplier fields
			$supplier_id = $item["supplier_id"];
			$supplier_email = $item["supplier_email"];
			$supplier_name = $item["supplier_name"];
			$supplier_short_desc = $item["supplier_short_desc"];
			$supplier_full_desc = $item["supplier_full_desc"];
			$supplier_cell_phone = $item["supplier_cell_phone"];

			$item_name = $item["item_name"];
			$item_code = $item["item_code"];
			$manufacturer_code = $item["manufacturer_code"];
			$is_recurring = $item["is_recurring"];
			$recurring_last_payment = $item["recurring_last_payment"];
			$recurring_next_payment = $item["recurring_next_payment"];

			$item_properties = $item["html_item_properties"];
			$item_properties_text = $item["text_item_properties"];

			// subscription fields
			$is_subscription = $item["is_subscription"];
			$subscription_id = $item["subscription_id"];
			$subscription_start_date = $item["subscription_start_date"];
			$subscription_expiry_date= $item["subscription_expiry_date"];

			if ($parse_template) {
				$t->set_var("components", "");
				$t->set_var("components_block", "");
			}
			$components = isset($item["components"]) ? $item["components"] : "";
			if ($subcomponents_show_type == 1 && is_array($components) && sizeof($components) > 0) {
				for ($c = 0; $c < sizeof($components); $c++) {
					$cc_id = $components[$c];
					$component = $order_items[$cc_id];
					$component_id = $component["item_id"];
					$selection_name = $component["selection_name"];
					if ($selection_name) { $selection_name .= ": "; }
					$component_name = $component["item_name"];
					$component_price = $component["price"];
					$component_quantity = $component["quantity"];
					$component_sub_quantity = intval($component_quantity / $quantity);
					$component_item_code = $component["item_code"];
					$component_manufacturer_code = $component["manufacturer_code"];

					$component_image = $component["super_image"];
					$image_type = 4;
					if (!$component_image) { 
						$component_image = $component["big_image"];
						$image_type = 3;
					}
					if ($component_image && $parse_template) {
						$component_icon = product_image_icon($component_id, $component_name, $component_image, $image_type);
					} else {
						$component_icon = "";
					}

					$price += ($component["price"] * $component_sub_quantity);
					$item_total += $component["item_total"];
					$item_tax += ($component["item_tax"] * $component_sub_quantity);
					$item_tax_total += $component["item_tax_total"];
					$price_excl_tax += ($component["price_excl_tax"] * $component_sub_quantity);
					$price_incl_tax += ($component["price_incl_tax"] * $component_sub_quantity);
					$price_excl_tax_total += ($component["price_excl_tax_total"]);
					$price_incl_tax_total += ($component["price_incl_tax_total"]);

					$points_price += ($component["points_price"] * $component_sub_quantity);
					$reward_points += ($component["reward_points"] * $component_sub_quantity);
					$reward_credits += ($component["reward_credits"] * $component_sub_quantity);

					if ($parse_template) {
						$t->set_var("component_codes", "");
						$t->set_var("component_item_code_block", "");
						$t->set_var("component_man_code_block", "");
						$t->set_var("component_order_item_id", $cc_id);
						$t->set_var("component_quantity", $component_sub_quantity);
						$t->set_var("selection_name", $selection_name);
						$t->set_var("component_name", $component_name);
						if (($show_item_code && strlen($component_item_code)) || ($show_manufacturer_code && strlen($component_manufacturer_code))) {
							if ($show_item_code && strlen($component_item_code)) {
								$t->set_var("component_item_code", $component_item_code);
								$t->sparse("component_item_code_block", false);
							}
							if ($show_manufacturer_code && strlen($component_manufacturer_code)) {
								$t->set_var("component_manufacturer_code", $component_manufacturer_code);
								$t->sparse("component_man_code_block", false);
							}
							$t->sparse("component_codes", false);
						}
						$t->set_var("component_icon", $component_icon);
						if ($component_price > 0) {
							$t->set_var("component_price", $option_positive_price_right . currency_format($component_price, $order_currency) . $option_positive_price_left);
						} elseif ($component_price < 0) {
							$t->set_var("component_price", $option_negative_price_right . currency_format(abs($component_price), $order_currency) . $option_negative_price_left);
						} else {
							$t->set_var("component_price", "");
						}

						$t->sparse("components", true);
					}
				}
				if ($parse_template) {
					$t->sparse("components_block", false);
				}
			}

			if (strlen($order_items_ids)) { $order_items_ids .= ","; }
			$order_items_ids .= $order_item_id;

			if ($page_type == "user_order") {
				$sql  = " SELECT id.download_id,i.item_name,id.download_added, ";
				$sql .= " i.download_path AS product_path, id.download_path, ";
				$sql .= " id.download_expiry ";
				$sql .= " FROM " . $table_prefix . "items_downloads id, " . $table_prefix . "items i ";
				$sql .= " WHERE id.item_id=i.item_id ";
				$sql .= " AND id.order_item_id=" . $db->tosql($order_item_id, INTEGER);
				$sql .= " AND id.order_id=" . $db->tosql($order_id, INTEGER);
				$sql .= " AND id.activated=1";
				$t->set_var("download_links", "");
				$dbd->query($sql);
				while ($dbd->next_record()) {
					$download_id = $dbd->f("download_id");
					$product_path = trim($dbd->f("product_path"));
					$download_path = trim($dbd->f("download_path"));
					if (!$download_path) {
						$download_path = $product_path;
					}
					$download_added = $dbd->f("download_added", DATETIME);
					$download_expiry = $dbd->f("download_expiry", DATETIME);
					$current_date = mktime(0,0,0, date("m"), date("d"), date("Y"));
					$expiry_date = $current_date;
					if (is_array($download_expiry)) {
						$expiry_date = mktime (0,0,0, $download_expiry[MONTH], $download_expiry[DAY], $download_expiry[YEAR]);
					}
					$item_download_url  = $site_url . "download.php?download_id=" . $download_id;
					$vc = md5($download_id . $download_added[3].$download_added[4].$download_added[5]);
					$item_download_url .= "&vc=" . urlencode($vc);
					if ($expiry_date >= $current_date) {
						$product_paths = explode(";", $download_path);
						for ($di = 0; $di < sizeof($product_paths); $di++) {
							$sub_path = $product_paths[$di];
							if ($sub_path) {
								$sub_url = $item_download_url . "&path_id=" . ($di + 1);
								$t->set_var("filename", basename($sub_path));
								$t->set_var("download_id", $download_id);
								$t->set_var("vc", $vc);
								$t->set_var("download_url", $sub_url);
								$t->parse("download_links");
							}
						}
					}
				}
				$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "releases  ";
				$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);;
				$dbd->query($sql);
				$dbd->next_record();
				$releases_number = $dbd->f(0);
				if ($releases_number > 0) {
					$t->set_var("order_item_id", $order_item_id);
					$t->parse("releases_link", false);
				} else {
					$t->set_var("releases_link", "");
				}
			}

			$serial_numbers = ""; $gift_vouchers = "";
			if ($page_type == "user_order" || $page_type == "user_merchant_order" || $page_type == "admin_order") {
				$t->set_var("serial_numbers", "");
				$sql  = " SELECT serial_id, serial_number, activations_number ";
				$sql .= " FROM " . $table_prefix . "orders_items_serials ";
				$sql .= " WHERE order_item_id=" . $db->tosql($order_item_id, INTEGER);
				$sql .= " AND order_id=" . $db->tosql($order_id, INTEGER);
				if ($page_type == "user_order") {
					$sql .= " AND activated=1";
				}
				$dbd->query($sql);
				while ($dbd->next_record()) {
					$serial_id = $dbd->f("serial_id");
					$serial_number = $dbd->f("serial_number");
					$t->set_var("serial_id", $serial_id);
					$t->set_var("serial_number", $serial_number);
					$t->sparse("serial_numbers", true);
				}
			}

			if ($page_type == "user_order" || $page_type == "admin_order") {
				$t->set_var("gift_vouchers", "");
				$sql  = " SELECT coupon_id, coupon_code ";
				$sql .= " FROM " . $table_prefix . "coupons ";
				$sql .= " WHERE order_item_id=" . $db->tosql($order_item_id, INTEGER);
				$sql .= " AND order_id=" . $db->tosql($order_id, INTEGER);
				if ($page_type == "user_order") {
					$sql .= " AND is_active=1";
				}
				$dbd->query($sql);
				while ($dbd->next_record()) {
					$coupon_id = $dbd->f("coupon_id");
					$coupon_code = $dbd->f("coupon_code");
					$t->set_var("coupon_id", $coupon_id);
					$t->set_var("coupon_code", $coupon_code);
					$t->sparse("gift_vouchers", true);
				}
			}

			// show information about coupons used
			if ($parse_template) {
				$t->set_var("item_coupons", "");
				$sql  = " SELECT coupon_id, coupon_code, coupon_title, discount_amount ";
				$sql .= " FROM " . $table_prefix . "orders_coupons ";
				$sql .= " WHERE order_item_id=" . $db->tosql($order_item_id, INTEGER);

				$dbd->query($sql);
				while ($dbd->next_record()) {
					$coupon_id = $dbd->f("coupon_id");
					$coupon_code = $dbd->f("coupon_code");
					$coupon_title = $dbd->f("coupon_title");
					$coupon_discount = $dbd->f("discount_amount");

					$t->set_var("coupon_id", $coupon_id);
					$t->set_var("coupon_code", $coupon_code);
					$t->set_var("coupon_title", $coupon_title);
					$t->set_var("discount_amount", currency_format(-$coupon_discount, $order_currency));

					$t->sparse("item_coupons", true);
				}
			}

			if ($page_type == "admin_order") {
				if ($use_stock_level) {
					$t->set_var("stock_level", $stock_level);
					$t->sparse("stock_level_block", false);
				} else {
					$t->set_var("stock_level_block", "");
				}

				set_options($set_statuses, "", "item_status");
				$t->set_var("current_item_status", $item_status);
				$t->set_var("item_status_desc", $item_status_desc);
				$t->set_var("current_item_status_desc", $item_status_desc);
			}			

			// get 
			$show_percentage = 0; $show_tax = 0; $show_tax_total = 0;
			foreach ($item_tax_values as $tax_id => $tax) {
				$show_type = $tax["show_type"];
				if ($show_type&1) {
					$show_percentage += $tax["tax_percent"];
					$show_tax += $tax["tax_amount"];
					$show_tax_total += $item_tax_total_values[$tax_id]["tax_amount"];
				}
			}

			$goods_total += $item_total;
			$goods_tax_total += $item_tax_total;
			$goods_tax_total_show += $show_tax_total;
			$goods_total_excl_tax += $price_excl_tax_total;
			$goods_total_incl_tax += $price_incl_tax_total;

			$total_quantity += $quantity;

			// save tax summary data
			$item_tax_text = str_replace(".", "_", strval(round($item_tax_percent, 4)));
			if (isset($items_taxes[$item_tax_text])) {
				$items_taxes[$item_tax_text][0] += $price_excl_tax_total;
				$items_taxes[$item_tax_text][1] += $item_tax_total;
				$items_taxes[$item_tax_text][2] += $price_incl_tax_total;
			} else {
				$items_taxes[$item_tax_text] = array($price_excl_tax_total, $item_tax_total, $price_incl_tax_total, $item_tax_percent);
			}

			$item_text = $item_name;

			if ($item_properties) {
				$item_text .= " (" . $item_properties_text . ")";
			}
			//$item_text .= " " . PROD_QTY_COLUMN . ": " . $quantity . " " . currency_format($item_total, $order_currency);
			$item_text .= " " . PROD_QTY_COLUMN . ": " . $quantity . " " . currency_format($price_incl_tax_total, $order_currency);
			$items_text .= $item_text . $eol;

			// get appopriate image 
			$item_image = ""; $item_image_alt = "";
			$super_image = ""; $super_image_alt = ""; 
			if ($item_image_column && $image_field) { 
				$image_exists = false;
				$item_image = $order_items[$order_item_id][$image_field];
				$item_image_alt = $order_items[$order_item_id][$image_alt_field];
				$super_image = $order_items[$order_item_id]["super_image"];
				$super_image_alt = $order_items[$order_item_id]["super_image_alt"];
				if (!$super_image) {
					$super_image = $order_items[$order_item_id]["big_image"];
					$super_image_alt = $order_items[$order_item_id]["big_image_alt"];
				}
				if (!strlen($item_image)) {
					$item_image = $product_no_image;
				} else {
					$image_exists = true;
				}
			}
			
			$cart_items[] = array(
				"item_id" => $item_id, "id" => $item_id, "product_id" => $item_id,
				"weight" => $weight, "actual_weight" => $actual_weight, "price" => $price, "quantity" => $quantity, "tax_id" => $item_tax_id, "tax_free" => $item_tax_free,
				"discount_amount" => $discount_amount, "real_price" => $real_price,
				"points_price" => $points_price, "reward_points" => $reward_points, "reward_credits" => $reward_credits,
				"buying_price" => $buying_price, "item_name" => $item_name, "product_name" => $item_name,
				"product_title" => $item_name, "item_title" => $item_name, "item_total" => $item_total,
				"downloadable" => $downloadable, "item_type_id" => $item_type_id, "stock_level" => $stock_level,
				"short_description" => $short_description, "description" => $short_description,
				"full_description" => $full_description, "item_properties_text" => $item_properties_text,
				"merchant_id" => $merchant_id, "merchant_email" => $merchant_email, "merchant_name" => $merchant_name,
				"merchant_first_name" => $merchant_first_name, "merchant_last_name" => $merchant_last_name,
				"merchant_cell_phone" => $merchant_cell_phone, 
				"affiliate_id" => $affiliate_id, "affiliate_commission" => $affiliate_commission, 
				"affiliate_email" => $affiliate_email, "affiliate_name" => $affiliate_name,
				"affiliate_first_name" => $affiliate_first_name, "affiliate_last_name" => $affiliate_last_name,
				"affiliate_cell_phone" => $affiliate_cell_phone, 
				"supplier_id" => $supplier_id, "supplier_email" => $supplier_email, "supplier_name" => $supplier_name,
				"supplier_short_desc" => $supplier_short_desc, "supplier_full_desc" => $supplier_full_desc,
				"supplier_cell_phone" => $supplier_cell_phone, 
				"super_image" => $super_image, "super_image_alt" => $super_image_alt, 
				"item_image" => $item_image, "item_image_alt" => $item_image_alt
			);
						
			if ($parse_template) { // set item variables into html
				$t->set_var("item_id", $item_id);
				$t->set_var("order_item_id", $order_item_id);
				$t->set_var("ordinal_number", $ordinal_number);
				$t->set_var("item_name", $item_name);
				$t->set_var("item_title", $item_name);
				$t->set_var("item_name_strip", htmlspecialchars(strip_tags($item_name)));
				$t->set_var("item_code", $item_code);
				$t->set_var("manufacturer_code", $manufacturer_code);
				// show product code
				if ($show_item_code && $item_code) {
					$t->sparse("item_code_block", false);
				} else {
					$t->set_var("item_code_block", "");
				}
				if ($show_manufacturer_code && $manufacturer_code) {
					$t->sparse("manufacturer_code_block", false);
				} else {
					$t->set_var("manufacturer_code_block", "");
				}
				if ($show_item_weight) {
					$weight = round($weight, 4);
					$t->set_var("item_weight", $weight . $weight_measure);
					$t->sparse("item_weight_block", false);
				} else {
					$t->set_var("item_weight_block", "");
				}
				if ($show_actual_weight) {
					$actual_weight = round($actual_weight, 4);
					$t->set_var("actual_weight", $actual_weight. $weight_measure);
					$t->sparse("actual_weight_block", false);
				} else {
					$t->set_var("actual_weight_block", "");
				}

				// new-spec begin
				if ($page_type == "admin_invoice_html" || $page_type == "user_invoice_html") {
					show_item_features($item_id, "invoice");
				} else {
					show_item_features($item_id, "checkout");
				}
				// new-spec end

				// show tax below product if such option set
				$t->set_var("item_taxes", "");
				foreach ($item_tax_total_values as $tax_id => $tax_info) {
					$show_type = $tax_info["show_type"];
					if ($show_type & 2) {
						$t->set_var("tax_name", $tax_info["tax_name"]);
						$t->set_var("tax_amount", currency_format($item_tax_values[$tax_id]["tax_amount"], $order_currency));
						$t->set_var("tax_amount_total", currency_format($tax_info["tax_amount"], $order_currency));
						$t->sparse("item_taxes", true);
					}
				}

				if ($points_price > 0 && $show_points_price) {
					$t->set_var("points_price", number_format($points_price, $points_decimals));
					$t->sparse("points_price_block", false);
				} else {
					$t->set_var("points_price_block", "");
				}
				if ($reward_points > 0 && $show_reward_points) {
					$t->set_var("reward_points", number_format($reward_points, $points_decimals));
					$t->sparse("reward_points_block", false);
				} else {
					$t->set_var("reward_points_block", "");
				}
				if ($reward_credits > 0 && $show_reward_credits 
					&& ($reward_credits_users == 0 || ($reward_credits_users == 1 && $session_user_id))) {
					$t->set_var("reward_credits", currency_format($reward_credits, $order_currency));
					$t->sparse("reward_credits_block", false);
				} else {
					$t->set_var("reward_credits_block", "");
				}

				if ($is_recurring) {
					$t->set_var("next_payment_date", va_date($date_show_format, $recurring_next_payment));
					$t->sparse("next_recurring_payment", false);
				} else {
					$t->set_var("next_recurring_payment", "");
				}
				if (preg_match_all("/https?\:\/\/[^\s<>\n]+/mi", $item_properties, $matches)) {
					for ($m = 0; $m < sizeof($matches[0]); $m++) {
						$link_url = $matches[0][$m];
						$html_link_url = "<a href=\"".$link_url."\" target=\"_blank\">" . basename($link_url) . "</a>";
						$item_properties = str_replace($link_url, $html_link_url, $item_properties);
					}
				}
				if ($is_subscription && $t->block_exists("cancel_subscription_link")) {
					$current_datetime = va_time();
					$current_date_ts = mktime (0, 0, 0, $current_datetime[MONTH], $current_datetime[DAY], $current_datetime[YEAR]);
					$subscription_sd_ts = va_timestamp($subscription_start_date);
					$subscription_ed_ts = va_timestamp($subscription_expiry_date);
					$subscription_days = intval(($subscription_ed_ts - $subscription_sd_ts) / 86400); // get int value due to possible 1 hour difference
					// check days difference and add current day as well
					$used_days = intval(($current_date_ts - $subscription_sd_ts) / 86400) + 1;
					$sql  = " SELECT setting_value FROM " . $table_prefix . "user_types_settings ";
					$sql .= " WHERE type_id=" . $db->tosql($order_user_type_id, INTEGER);
					$sql .= " AND setting_name='cancel_subscription'";
					$cancel_subscription = get_db_value($sql);
					if ($cancel_subscription == 1) {
						// return money to credits balance
						$credits_return = round((($price - $reward_credits)/ $subscription_days) * ($subscription_days - $used_days), 2); 
					} else {
						$credits_return = 0; 
					}
					if ($credits_return > 0) {
						$confirm_cancel_subscription = va_constant("CONFIRM_RETURN_SUBSCRIPTION_MSG");
					} else {
						$confirm_cancel_subscription = va_constant("CONFIRM_CANCEL_SUBSCRIPTION_MSG");
					}
					$confirm_cancel_subscription = str_replace(array("{credits_amount}", "\'"), array(currency_format($credits_return, $order_currency), "\\'"), $confirm_cancel_subscription);
					$t->set_var("confirm_cancel_subscription", $confirm_cancel_subscription);
					$t->sparse("cancel_subscription_link", false);
				} else {
					$t->set_var("cancel_subscription_link", "");
				}
				$t->set_var("item_properties", $item_properties);
				$t->set_var("item_options", $item_properties);
				$t->set_var("quantity", $quantity);

				$t->set_var("price_excl_tax", currency_format($price_excl_tax, $order_currency));
				$t->set_var("price_incl_tax", currency_format($price_incl_tax, $order_currency));
				$t->set_var("price_excl_tax_total", currency_format($price_excl_tax_total, $order_currency));
				$t->set_var("price_incl_tax_total", currency_format($price_incl_tax_total, $order_currency));

				// show tax information if column option selected
				$t->set_var("item_tax_percent",  $show_percentage . "%");
				$t->set_var("item_tax", currency_format($show_tax, $order_currency));
				$t->set_var("item_tax_total", currency_format($item_tax_total, $order_currency));
				
				// item image display
				if ($item_image) {
					if (!$super_image) { $super_image = $item_image; }
					if (!preg_match("/^https?\:\/\//", $super_image)) {
						$super_image = ($is_ssl) ? $secure_url.$super_image : $site_url.$super_image;
					}
					if (!preg_match("/^https?\:\/\//", $item_image)) {
						if ($image_exists && ($watermark || $restrict_products_images)) {
							$item_image = "image_show.php?item_id=".$item_id."&type=".$image_type_name."&vc=".md5($item_image);
						}
						$item_image = ($is_ssl) ? $secure_url.$item_image: $site_url.$item_image;
					}
					if (!strlen($item_image_alt)) { $item_image_alt = $item_name; }
					$t->set_var("alt", htmlspecialchars($item_image_alt));
					$t->set_var("src", htmlspecialchars($item_image));
					$t->set_var("super_src", htmlspecialchars($super_image));
					$t->sparse("image_preview", false);
				} else {
					$t->set_var("image_preview", "");
				}

				$t->set_var("item_shipping", "");
				// show item shipping information if available
				foreach ($orders_shipments as $order_shipping_id => $shipment) {
					$shipping_items_ids = $shipment["order_items_ids"];
					if ($item_shipping_id == $order_shipping_id || isset($shipping_items_ids[$order_item_id])) {
						$t->set_var("item_shipping_id", $shipment["shipping_id"]);
						$t->set_var("shipping_desc",  $shipment["shipping_desc"]);
						$tracking_id = $shipment["tracking_id"];
						$tracking_url = $shipment["tracking_url"];
						$expecting_date = $shipment["expecting_date"];
						$shipping_company_name = $shipment["company_name"];
						$shipping_company_url = $shipment["company_url"];
						$t->set_var("item_tracking_link", "");
						$t->set_var("item_tracking_text", "");
						$t->set_var("shipping_company", "");
						$t->set_var("shipping_company_url", "");
						if ($tracking_id) {
							$tracking_url = str_replace("{tracking_id}", $tracking_id, $tracking_url);
							$t->set_var("tracking_id", $tracking_id);
							$t->set_var("tracking_url", $tracking_url);
							if ($tracking_url) {
								$t->sparse("item_tracking_link", false);
							} else {
								$t->sparse("item_tracking_text", false);
							}
						}
						if ($shipping_company_name) {
							parse_value($shipping_company_url);
							$t->set_var("shipping_company_name", $shipping_company_name);
							$t->set_var("shipping_company_url", $shipping_company_url);
							if ($shipping_company_url) {
								$t->sparse("shipping_company_link", false);
							} else {
								$t->sparse("shipping_company", false);
							}
						}

						$t->sparse("item_shipping", true);
					}
				}
				
				parse_cart_columns($item_name_column, $item_price_column, $item_tax_percent_column, $item_tax_column, $item_price_incl_tax_column, $item_quantity_column, $item_price_total_column, $item_tax_total_column, $item_price_incl_tax_total_column, $item_image_column, $ordinal_number_column);
				$t->sparse("items", true);
			}
		}

		if ($parse_template) {
			//$t->set_var("tax_name", $tax_name); TODO change tax name to summary
			$t->set_var("order_date", $order_date);
			$t->set_var("order_items_ids", $order_items_ids);
			$t->set_var("total_quantity", $total_quantity);
			$t->set_var("goods_total", currency_format($goods_total, $order_currency));
			$t->set_var("goods_tax_total", currency_format($goods_tax_total_show, $order_currency));
			$t->set_var("goods_total_excl_tax", currency_format($goods_total_excl_tax, $order_currency));
			$t->set_var("goods_total_incl_tax", currency_format($goods_total_incl_tax, $order_currency));

			// show total reward credits
			if ($show_reward_credits && $total_reward_credits && ($reward_credits_users == 0 || ($reward_credits_users == 1 && $session_user_id))) {
				$t->set_var("reward_credits_total", currency_format($total_reward_credits, $order_currency));
				$t->sparse("reward_credits_total_block", false);
			}
			// show total reward points 
			if ($show_reward_points && $total_reward_points) {
				$t->set_var("reward_points_total", number_format($total_reward_points, $points_decimals));
				$t->sparse("reward_points_total_block", false);
			}

			if ($goods_colspan > 0) {
				$t->sparse("goods_name_column", false);
			}
			if ($item_quantity_column) {
				$t->sparse("goods_total_quantity_column", false);
			}
			if ($item_price_total_column) {
				$t->sparse("goods_total_excl_tax_column", false);
			}
			if ($item_tax_total_column) {
				$t->sparse("goods_tax_total_column", false);
			}
			if ($item_price_incl_tax_total_column) {
				$t->sparse("goods_total_incl_tax_column", false);
			}

			// parse tax groups
			foreach ($items_taxes as $items_tax_text => $items_tax_data) {
				$t->set_var("goods_total_excl_tax_" . $items_tax_text, currency_format($items_tax_data[0], $order_currency));
				$t->set_var("goods_total_" . $items_tax_text, currency_format($items_tax_data[0], $order_currency));
				$t->set_var("goods_tax_total_" . $items_tax_text,  currency_format($items_tax_data[1], $order_currency));
				$t->set_var("goods_with_tax_total_" . $items_tax_text, currency_format(($items_tax_data[2] + $items_tax_data[1]), $order_currency));
				$t->set_var("goods_total_incl_tax_" . $items_tax_text, currency_format(($items_tax_data[2] + $items_tax_data[1]), $order_currency));
			}

		}

		// show information about order coupons used
		if ($parse_template && strlen($order_coupons_ids)) {
			$max_discount = $goods_total;
			$max_tax_discount = $goods_tax_total;

			$t->set_var("order_coupons", "");
			$sql  = " SELECT * FROM " . $table_prefix . "orders_coupons ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$sql .= " AND coupon_id IN (" . $db->tosql($order_coupons_ids, INTEGERS_LIST) . ") ";
			$dbd->query($sql);
			if ($dbd->next_record()) {
				do {
					$coupon_id = $dbd->f("coupon_id");
					$coupon_code = $dbd->f("coupon_code");
					$coupon_title = $dbd->f("coupon_title");
					$discount_amount = $dbd->f("discount_amount");
					$discount_tax_amount = $dbd->f("discount_tax_amount");
					if ($tax_prices_type == 1) {
						$discount_amount_excl_tax = $discount_amount - $discount_tax_amount;
						$discount_amount_incl_tax = $discount_amount;
					} else {
						$discount_amount_excl_tax = $discount_amount;
						$discount_amount_incl_tax = $discount_amount + $discount_tax_amount;
					}

					$t->set_var("coupon_id", $coupon_id);
					$t->set_var("coupon_code", $coupon_code);
					$t->set_var("coupon_title", $coupon_title);
					$t->set_var("discount_amount_excl_tax", "-" . currency_format($discount_amount_excl_tax, $order_currency));
					$t->set_var("discount_tax_amount", "-" . currency_format($discount_tax_amount, $order_currency));
					$t->set_var("discount_amount_incl_tax", "- " . currency_format($discount_amount_incl_tax, $order_currency));

					if ($goods_colspan > 0) {
						$t->sparse("coupon_name_column", false);
					}
					if ($item_price_total_column) {
						$t->sparse("coupon_amount_column", false);
					}
					if ($item_tax_total_column) {
						$t->sparse("coupon_tax_column", false);
					}
					if ($item_price_incl_tax_total_column) {
						$t->sparse("coupon_amount_incl_tax_column", false);
					}

					$t->sparse("order_coupons", true);

				} while ($dbd->next_record());
			} else {
				$sql  = " SELECT coupon_id, coupon_code, coupon_title, discount_type, coupon_tax_free, discount_amount ";
				$sql .= " FROM " . $table_prefix . "coupons ";
				$sql .= " WHERE coupon_id IN (" . $db->tosql($order_coupons_ids, INTEGERS_LIST) . ") ";
				$dbd->query($sql);
				while ($dbd->next_record()) {
					$coupon_id = $dbd->f("coupon_id");
					$coupon_code = $dbd->f("coupon_code");
					$coupon_title = $dbd->f("coupon_title");
					$coupon_type = $dbd->f("discount_type");
					$coupon_tax_free = $dbd->f("coupon_tax_free");
					$coupon_discount = $dbd->f("discount_amount");
					if ($coupon_type == 1) {
						$discount_amount = round(($goods_total / 100) * $coupon_discount, 2);
					} else {
						$discount_amount = $coupon_discount;
					}
					if ($discount_amount > $max_discount) {
						$discount_amount = $max_discount;
					}
					$max_discount -= $discount_amount;

					// check discount tax
					if ($coupon_tax_free && $tax_prices_type != 1) {
						$discount_tax_amount = 0;
					} else {
						$discount_tax_amount = round(($discount_amount * $goods_tax_total) / $goods_total, 2);
						if ($discount_tax_amount > $max_tax_discount) {
							$discount_tax_amount = $max_tax_discount;
						}
						$max_tax_discount -= $discount_tax_amount;
					}
					if ($tax_prices_type == 1) {
						$discount_amount_excl_tax = $discount_amount - $discount_tax_amount;
						$discount_amount_incl_tax = $discount_amount;
					} else {
						$discount_amount_excl_tax = $discount_amount;
						$discount_amount_incl_tax = $discount_amount + $discount_tax_amount;
					}

					$t->set_var("coupon_id", $coupon_id);
					$t->set_var("coupon_code", $coupon_code);
					$t->set_var("coupon_title", $coupon_title);
					$t->set_var("discount_amount_excl_tax", "-" . currency_format($discount_amount_excl_tax, $order_currency));
					$t->set_var("discount_tax_amount", "-" . currency_format($discount_tax_amount, $order_currency));
					$t->set_var("discount_amount_incl_tax", "- " . currency_format($discount_amount_incl_tax, $order_currency));

					if ($goods_colspan > 0) {
						$t->sparse("coupon_name_column", false);
					}
					if ($item_price_total_column) {
						$t->sparse("coupon_amount_column", false);
					}
					if ($item_tax_total_column) {
						$t->sparse("coupon_tax_column", false);
					}
					if ($item_price_incl_tax_total_column) {
						$t->sparse("coupon_amount_incl_tax_column", false);
					}

					$t->sparse("order_coupons", true);
				}
			}
		}


		if ($total_discount > 0) 
		{
			if ($parse_template) {
				if ($tax_prices_type == 1) {
					$total_discount_excl_tax = $total_discount - $total_discount_tax;
					$total_discount_incl_tax = $total_discount;
				} else {
					$total_discount_excl_tax = $total_discount;
					$total_discount_incl_tax = $total_discount + $total_discount_tax;
				}

				$t->set_var("total_discount_excl_tax", "-" . currency_format($total_discount_excl_tax, $order_currency));
				$t->set_var("total_discount_tax_amount", "- " . currency_format($total_discount_tax, $order_currency));
				$t->set_var("total_discount_incl_tax", "- " . currency_format($total_discount_incl_tax, $order_currency));
				$t->set_var("discounted_amount_excl_tax", currency_format(($goods_total_excl_tax - $total_discount_excl_tax), $order_currency));
				$t->set_var("discounted_tax_amount", currency_format(($goods_tax_total - $total_discount_tax), $order_currency));
				$t->set_var("discounted_amount_incl_tax", currency_format(($goods_total_incl_tax - $total_discount_incl_tax), $order_currency));

				if ($goods_colspan > 0) {
					$t->sparse("total_discount_name_column", false);
					$t->sparse("discounted_name_column", false);
				}
				if ($item_price_total_column) {
					$t->sparse("total_discount_amount_excl_tax_column", false);
					$t->sparse("discounted_amount_excl_tax_column", false);
				}
				if ($item_tax_total_column) {
					$t->sparse("total_discount_tax_column", false);
					$t->sparse("discounted_tax_column", false);
				}
				if ($item_price_incl_tax_total_column) {
					$t->sparse("total_discount_amount_incl_tax_column", false);
					$t->sparse("discounted_amount_incl_tax_column", false);
				}

				$t->sparse("discount", false);
			}
		}
		$goods_with_discount = $goods_total - $total_discount;
		$goods_tax_value = $goods_tax_total - $total_discount_tax;

		$cart_properties = 0; $personal_properties = 0;
		$delivery_properties = 0; $shipping_properties = 0; $payment_properties = 0;
		$properties_total = 0; $properties_taxable = 0;
		$orders_properties = array();

		// check order properties
		$sql  = " SELECT op.property_id, op.property_type, op.property_code, op.property_name, op.property_notes, op.property_value, ";
		$sql .= "  op.property_price, op.property_points_amount, op.tax_free ";
		$sql .= " FROM " . $table_prefix . "orders_properties op ";
		$sql .= " WHERE op.order_id=" . $db->tosql($order_id, INTEGER);
		if ($page_type == "cc_info" || $page_type == "user_order") {
			$sql .= " AND op.property_type IN (1,2,3) ";
		}
		$sql .= " ORDER BY op.property_order, op.property_id ";
		$db->query($sql);
		while ($db->next_record()) {
			$property_id   = $db->f("property_id");
			$property_type = $db->f("property_type");
			$property_code = $db->f("property_code");
			$property_name = get_translation($db->f("property_name"));
			$property_value = get_translation($db->f("property_value"));
			$property_notes = $db->f("property_notes");
			$property_price = $db->f("property_price");
			$property_points_amount = $db->f("property_points_amount");
			$property_tax_free = $db->f("tax_free");
			$control_type = $db->f("control_type");
	  
			if (isset($orders_properties[$property_id])) {
				$orders_properties[$property_id]["value"] .= "; " . $property_value;
				$orders_properties[$property_id]["price"] += $property_price;
				$orders_properties[$property_id]["points_amount"] += $property_points_amount;
			} else {
				$orders_properties[$property_id] = array(
					"type" => $property_type, "code" => $property_code, "name" => $property_name, "notes" => $property_notes, 
					"value" => $property_value, "price" => $property_price, "points_amount" => $property_points_amount, "tax_free" => $property_tax_free,
				);
			}
		}

		foreach ($orders_properties as $property_id => $property_values) {
			$property_type = $property_values["type"];
			$property_code = $property_values["code"];
			$property_name = $property_values["name"];
			$property_value = $property_values["value"];
			$property_notes = $property_values["notes"];
			if ($property_code == "vat_number") {
				$property_notes = html_format_array($property_notes, "vat_response");
			}
			$property_price = $property_values["price"];
			$property_points_amount = $property_values["points_amount"];
			$property_tax_id = 0;
			$property_tax_free = $property_values["tax_free"];

			$properties_total += $property_price;
			if ($property_tax_free != 1) {
				$properties_taxable += $property_price;
			}
			$property_tax_values = get_tax_amount($order_tax_rates, "properties", $property_price, 1, $property_tax_id, $property_tax_free, $property_tax_percent, "", 2, $tax_prices_type, $tax_round);
			$property_tax = add_tax_values($order_tax_rates, $property_tax_values, "properties", $tax_round);

			if ($tax_prices_type == 1) {
				$property_price_excl_tax = $property_price - $property_tax;
				$property_price_incl_tax = $property_price;
			} else {
				$property_price_excl_tax = $property_price;
				$property_price_incl_tax = $property_price + $property_tax;
			}
			$properties_incl_tax += $property_price_excl_tax; 
			$properties_excl_tax += $property_price_incl_tax;

			if ($property_type == 1) {
				$items_text .= $property_name . "(" . $property_value . ") " . $eol;
			}
			if ($parse_template) {
				$t->set_var("field_name_" . $property_id, $property_name);
				$t->set_var("field_value_" . $property_id, $property_value);
				$t->set_var("field_price_" . $property_id, $property_price);
				$t->set_var("field_" . $property_id, $property_value);
				$t->set_var("property_name", $property_name);
				$t->set_var("property_control_block", "");
				$t->set_var("property_value", $property_value);
				$t->sparse("property_value_block", false);
				if ($property_price != 0) {
					$property_price_text = currency_format($property_price, $order_currency, $property_tax);
				} else {
					$property_price_text = "";
				}
				if ($property_notes) {
					$t->set_var("property_notes", $property_notes);
					$t->sparse("property_notes_block", false);
				} else {
					$t->set_var("property_notes_block", "");
				}
				$t->set_var("property_price", $property_price_text);
				if ($property_points_amount > 0 && $show_points_price) {
					$t->set_var("property_points_price", number_format($property_points_amount, $points_decimals));
					$t->sparse("property_points_price_block", false);
				} else {
					$t->set_var("property_points_price_block", "");
				}

				if ($property_price == 0) {
					$t->set_var("property_price_excl_tax", "");
					$t->set_var("property_tax", "");
					$t->set_var("property_price_incl_tax", "");
				} else {
					$t->set_var("property_price_excl_tax", currency_format($property_price_excl_tax, $order_currency));
					$t->set_var("property_tax", currency_format($property_tax, $order_currency));
					$t->set_var("property_price_incl_tax", currency_format($property_price_incl_tax, $order_currency));
				}
				if ($property_type == 1) {
			    $cart_properties++;
					if ($item_price_total_column) {
						$t->sparse("property_price_excl_tax_column", false);
					}
					if ($item_tax_total_column) {
						$t->sparse("property_tax_column", false);
					}
					if ($item_price_incl_tax_total_column) {
						$t->sparse("property_price_incl_tax_column", false);
					}
					$t->sparse("cart_properties", true);
				} elseif ($property_type == 2) {
					$personal_properties++;
					$t->sparse("personal_properties", true);
				} elseif ($property_type == 3) {
					$delivery_properties++;
					$t->sparse("delivery_properties", true);
				} elseif ($property_type == 4) {
					if ($page_type != "order_info" && $page_type != "opc") {
						$payment_properties++;
						$t->sparse("payment_properties", true);
					}
				} elseif ($property_type == 5 || $property_type == 6) {
					$shipping_properties++;
					$t->sparse("shipping_properties", true);
				}
			}
		}
		if ($parse_template) {
			$t->set_var("properties_total", $properties_total);
			$t->set_var("properties_taxable", $properties_taxable);
		}

		if ($parse_template) {
			if (is_array($orders_shipments) && sizeof($orders_shipments) > 0) {
				foreach ($orders_shipments as $order_shipping_id => $shipment) {

					$t->set_var("order_shipping_id", $order_shipping_id);
					$t->set_var("current_shipping_id", $shipment["shipping_id"]);
					$t->set_var("shipping_type_desc",  $shipment["shipping_desc"]);
					$t->set_var("shipping_cost_excl_tax", currency_format($shipment["shipping_cost_excl_tax"], $order_currency));
					$t->set_var("shipping_cost_desc", currency_format($shipment["shipping_cost_incl_tax"], $order_currency));
					$t->set_var("shipping_tax", currency_format($shipment["shipping_tax"], $order_currency));
					$t->set_var("shipping_cost_incl_tax", currency_format($shipment["shipping_cost_incl_tax"], $order_currency));
					if ($shipment["points_cost"] > 0 && $show_points_price) {
						$t->set_var("shipping_points_price", number_format($shipment["points_cost"], $points_decimals));
						$t->sparse("shipping_points_price_block", false);
					} else {
						$t->set_var("shipping_points_price_block", "");
					}
					if ($update_orders) {
						$t->sparse("shipping_edit_link", false);
					}
					if ($item_price_total_column) {
						$t->sparse("shipping_cost_excl_tax_column", false);
					}
					if ($item_tax_total_column) {
						$t->sparse("shipping_tax_column", false);
					}
					if ($item_price_incl_tax_total_column) {
						$t->sparse("shipping_cost_incl_tax_column", false);
					}

					$tracking_id = $shipment["tracking_id"];
					$tracking_url = $shipment["tracking_url"];
					$expecting_date = $shipment["expecting_date"];
					$t->set_var("tracking_link", "");
					$t->set_var("tracking_text", "");
					if ($tracking_id) {
						$tracking_url = str_replace("{tracking_id}", $tracking_id, $tracking_url);
						$t->set_var("tracking_id", $tracking_id);
						$t->set_var("tracking_url", $tracking_url);
						if ($tracking_url) {
							$t->sparse("tracking_link", false);
						} else {
							$t->sparse("tracking_text", false);
						}
					}

					$t->set_var("shipping_date", "");
					if (is_array($expecting_date)) {
						$t->set_var("expecting_date", va_date($date_show_format, $expecting_date));
						$t->sparse("shipping_date", false);
					}
					$confirm_message = str_replace("{record_name}", va_constant("PROD_SHIPPING_MSG") . ": ".$shipment["shipping_desc"], va_constant("CONFIRM_DELETE_MSG"));
					$confirm_message = str_replace("'", "\'", $confirm_message);
					$t->set_var("confirm_message", htmlspecialchars($confirm_message));

					if ($page_type == "order_info") {
						$t->sparse("shipping_single", true);
					} else {
						$t->sparse("shipping_type", true);
					}
				}
				if ($page_type == "order_info") {
					$t->set_var("group_name", va_constant("PROD_SHIPPING_MSG"));
					$t->sparse("shipping_groups", false);
				}
			}
		}

		// check paid and unpiad processing fees 
		$processing_fee = 0; $processing_excl_tax = 0; $processing_tax = 0; $processing_incl_tax = 0;
		$paid_processing_fee = 0;  $paid_processing_excl_tax = 0; $paid_processing_tax = 0; $paid_processing_incl_tax = 0;
		$sql  = " SELECT * FROM ".$table_prefix."orders_payments ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {	
			$payment_paid = $db->f("payment_paid");
			$payment_fee = doubleval($db->f("processing_fee"));
			$payment_tax_free = $db->f("processing_tax_free");
			$payment_tax_id = 0;
			$payment_tax_values = get_tax_amount($order_tax_rates, "processing", $payment_fee, 1, $payment_tax_id, $payment_tax_free, $fee_tax_percent, "", 2, $tax_prices_type, $tax_round);
			$payment_tax = add_tax_values($order_tax_rates, $payment_tax_values, "processing", $tax_round);
			if ($tax_prices_type == 1) {
				$payment_incl_tax = $payment_fee;
				$payment_excl_tax = $payment_fee - $payment_tax;
			} else {
				$payment_incl_tax = $payment_fee + $payment_tax;
				$payment_excl_tax = $payment_fee;
			}
			$processing_fee += $payment_fee; 
			$processing_excl_tax += $payment_excl_tax; 
			$processing_tax += $payment_tax; 
			$processing_incl_tax += $payment_incl_tax;
			if ($payment_paid) {
				$paid_processing_fee += $payment_fee; 
				$paid_processing_excl_tax += $payment_excl_tax; 
				$paid_processing_tax += $payment_tax; 
				$paid_processing_incl_tax += $payment_incl_tax;
			}
		}

		if ($processing_fee != 0 && $parse_template) {
			$t->set_var("fee_value", round($processing_fee, 2));
			$t->set_var("processing_fee_cost", currency_format($processing_fee, $order_currency));
			$t->sparse("fee");
		}

		$taxes_total = 0; $order_fixed_tax = 0;
		// calculate the tax
		if ($tax_available) {

			// get taxes sums for further calculations
			$taxes_sum = 0; $discount_tax_sum = $total_discount_tax;
			foreach($order_tax_rates as $tax_id => $tax_info) {
				$tax_cost = isset($tax_info["tax_total"]) ? $tax_info["tax_total"] : 0;
				$order_fixed_amount = isset($tax_info["order_fixed_amount"]) ? $tax_info["order_fixed_amount"] : 0;
				$taxes_sum += va_round($tax_cost, $currency["decimals"]);
				$order_fixed_tax += doubleval($order_fixed_amount);
			}

			// TODO 
			$taxes_param = ""; $tax_number = 0;
			foreach($order_tax_rates as $tax_id => $tax_info) {
				$tax_number++;
				$tax_name = get_translation($tax_info["tax_name"]);
				$current_tax_free = isset($tax_info["tax_free"]) ? $tax_info["tax_free"] : 0;
				//if ($tax_free) { $current_tax_free = true; }
				$tax_percent = $tax_info["tax_percent"];
				$fixed_amount = $tax_info["fixed_amount"];
				$order_fixed_amount = $tax_info["order_fixed_amount"];
				$tax_types = $tax_info["types"];
				$tax_cost = isset($tax_info["tax_total"]) ? $tax_info["tax_total"] : 0;
				$tax_processing = isset($tax_info["processing"]) ? $tax_info["processing"] : 0;
				if ($total_discount_tax) {
					// in case if there are any order coupons decrease taxes value 
					if ($tax_number == sizeof($order_tax_rates)) {
						$tax_discount = $discount_tax_sum;
					} elseif ($taxes_sum != 0) {
						$tax_discount = round(($tax_cost * $total_discount_tax) / $taxes_sum, 2);
					} else {
						$tax_discount = 0;
					}
					$discount_tax_sum -= $tax_discount;
					$tax_cost -= $tax_discount;
				}
				$tax_cost += doubleval($order_fixed_amount);
				$taxes_total += va_round($tax_cost, $currency["decimals"]);
  
				if ($parse_template) {
					// hide tax if it has zero value
					if ($tax_cost != 0) {
						$t->set_var("tax_id", $tax_id);
						$t->set_var("tax_percent", $tax_percent);
						$t->set_var("fixed_amount", $fixed_amount);
						$t->set_var("tax_name", $tax_name);
						$t->set_var("tax_cost", currency_format($tax_cost, $order_currency));
						$t->sparse("taxes", true);
					}
  
					// build param
					if ($taxes_param) { $taxes_param .= "&"; }
					$taxes_param .= "tax_id=".$tax_id;
					$taxes_param .= "&tax_name=".prepare_js_value($tax_name);
					$taxes_param .= "&tax_free=".prepare_js_value($current_tax_free);
					$taxes_param .= "&tax_percent=".prepare_js_value($tax_percent);
					$taxes_param .= "&fixed_amount=".prepare_js_value($fixed_amount);
					$taxes_param .= "&order_fixed_amount=".prepare_js_value($order_fixed_amount);
					if (is_array($tax_types) && sizeof($tax_types) > 0) {
						foreach($tax_types as $item_type_id => $item_type_info) {
							$taxes_param .= "&item_type_percent_".$item_type_id."=".prepare_js_value($item_type_info["tax_percent"]);
							$taxes_param .= "&item_type_fixed_".$item_type_id."=".prepare_js_value($item_type_info["fixed_amount"]);
						}
					}
				}
			}
			if ($parse_template) {
				$t->set_var("tax_rates", $taxes_param);
			}
		}

		$order_total = round($goods_with_discount, 2) + round($properties_total, 2) + round($total_shipping_cost, 2) + round($processing_fee, 2);
		if ($tax_prices_type != 1) {
			$order_total += round($taxes_total, 2);
		}

		if ($parse_template && $paid_total > 0) {
			$t->set_var("paid_total_desc", currency_format($paid_total, $order_currency));
			$t->sparse("paid_total_block");
		}
		if ($page_type != "order_info" && $parse_template && $payment_amount > 0 && $order_total > $payment_amount) {
			$t->set_var("payment_amount", currency_format($payment_amount, $order_currency));
			$t->sparse("part_payment_block");
		}


		// show information about vouchers used
		if ($parse_template && strlen($vouchers_ids)) {

			$t->set_var("vouchers_block", "");
			$sql  = " SELECT * FROM " . $table_prefix . "orders_coupons ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$sql .= " AND coupon_id IN (" . $db->tosql($vouchers_ids, INTEGERS_LIST) . ") ";
			$dbd->query($sql);
			if ($dbd->next_record()) {
				do {
					$voucher_id = $dbd->f("coupon_id");
					$voucher_code = $dbd->f("coupon_code");
					$voucher_title = $dbd->f("coupon_title");
					$voucher_amount = $dbd->f("discount_amount");
					$order_total = round($order_total - $voucher_amount, 2);

					$t->set_var("voucher_id", $voucher_id);
					$t->set_var("voucher_code", $voucher_code);
					$t->set_var("voucher_title", $voucher_title);
					$t->set_var("voucher_amount", "-" . currency_format($voucher_amount, $order_currency));

					$t->sparse("used_vouchers", true);
				} while ($dbd->next_record());

				$t->sparse("vouchers_block", true);
			} 
		}

		if ($credit_amount != 0) {
			if ($parse_template) {
				$t->set_var("credit_amount_value", round($credit_amount, 2));
				$t->set_var("credit_amount_cost", "-" . currency_format($credit_amount, $order_currency));
				$t->sparse("credit_amount_block");
			}
			$order_total -= $credit_amount;
		}

		if ($parse_template) {

			if ($show_total_weight && $weight_total > 0) {
				$weight_total = round($weight_total, 4);
				$t->set_var("total_weight", $weight_total . $weight_measure);
				$t->sparse("total_weight_block", false);
			}
			if ($show_total_actual_weight && $actual_weight_total > 0) {
				$actual_weight_total = round($actual_weight_total, 4);
				$t->set_var("total_actual_weight", $actual_weight_total. $weight_measure);
				$t->sparse("total_actual_weight_block", false);
			}

			$t->set_var("order_total", currency_format($order_total, $order_currency));
			if ($total_points_amount > 0) {
				$t->set_var("total_points_amount", number_format($total_points_amount, $points_decimals));
				$t->sparse("total_points_block", false);
			} else {
				$t->set_var("total_points_block", "");
			}
			$t->sparse("basket", false);
		}
		// save data for return


		$order_data["items_text"] = $items_text;
		$order_data["order_items"] = $order_items;
		$order_data["total_items"] = $total_items;
		$order_data["shipping_quantity"] = $shipping_quantity;
		$order_data["goods_total"] = $goods_total;
		$order_data["goods_total_incl_tax"] = $goods_total_incl_tax;
		$order_data["total_discount_incl_tax"] = $total_discount_incl_tax;
		$order_data["properties_incl_tax"] = $properties_incl_tax;
		$order_data["total_shipping_incl_tax"] = $shipments_incl_tax;
		$order_data["shipments_incl_tax"] = $shipments_incl_tax;
		$order_data["paid_total"] = $paid_total;
		$order_data["total_reward_points"] = $total_reward_points;
		$order_data["total_reward_credits"] = $total_reward_credits;
		$order_data["total_merchants_commission"] = $total_merchants_commission;
		$order_data["total_affiliate_commission"] = $total_affiliate_commission;
		$order_data["taxes_total"] = $taxes_total;
		$order_data["weight_total"] = $weight_total;
		$order_data["tax_rates"] = $order_tax_rates;
		$order_data["processing_fee"] = $processing_fee;
		$order_data["processing_excl_tax"] = $processing_excl_tax;
		$order_data["processing_tax"] = $processing_tax;
		$order_data["processing_incl_tax"] = $processing_incl_tax;
		$order_data["paid_processing_fee"] = $paid_processing_fee;
		$order_data["paid_processing_excl_tax"] = $paid_processing_excl_tax;
		$order_data["paid_processing_tax"] = $paid_processing_tax;
		$order_data["paid_processing_incl_tax"] = $paid_processing_incl_tax;

		if ($page_type == "order_info" || $page_type == "checkout_final") {
	 		return $order_data;
		} else {
			return $items_text;
		}
	}


	function check_order($order_id, $vc, $final_check = false)
	{
		global $db, $table_prefix;
		$errors = "";
		$sql  = " SELECT order_placed_date,is_confirmed,is_placed,order_total,paid_total ";
		$sql .= " FROM " . $table_prefix . "orders ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		if ($db->next_record())
		{
			$is_placed = $db->f("is_placed");
			$is_confirmed = $db->f("is_confirmed");
			$order_total = $db->f("order_total");
			$paid_total = $db->f("paid_total");
			$order_placed_date = $db->f("order_placed_date", DATETIME);
			if (!$final_check && $vc != md5($order_id . $order_placed_date[3].$order_placed_date[4].$order_placed_date[5])) {
				$errors .= va_constant("ORDER_CODE_ERROR") . "<br>";
			}
			if ($is_placed && (!$final_check || ($final_check && $order_id != get_session("session_order_id")))) {
				$errors .= va_constant("ORDER_PLACED_ERROR");
			}
		} else {
			$errors .= va_constant("ORDER_EXISTS_ERROR") . "<br>";
		}
		return $errors;
	}

	function get_order_id()
	{
		$order_id = get_session("session_order_id");
		if (!strlen($order_id)) { $order_id = get_param("cart_order_id"); }
		if (!strlen($order_id)) { $order_id = get_param("oid"); }
		if (!strlen($order_id)) { $order_id = get_param("cartId"); }
		if (!strlen($order_id)) { $order_id = get_param("x_invoice_num"); }
		// Ogone parameter name - orderID
		if (!strlen($order_id)) { $order_id = get_param("orderID"); }

		return $order_id;
	}

	function generate_invoice_number($order_data, $invoice_type = "invoice")
	{
		global $db, $site_id, $table_prefix, $date_formats;

		// check if old format used with one order_id parameter
		if (!is_array($order_data)) {
			$order_id = $order_data;
			$order_data = array("order_id" => $order_id);
		}
		$order_id = get_setting_value($order_data, "order_id", "");
		$order_placed_date = get_setting_value($order_data, "order_placed_date", "");
		if (!strlen($order_id)) {
			return false;
		}

		// get invoice settings
		$order_info = array();
		$sql  = " SELECT site_id,setting_name,setting_value FROM " . $table_prefix . "global_settings ";
		$sql .= " WHERE setting_type='order_info'";
		if (isset($site_id) && $site_id) {
			$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER) . ")";
			$sql .= " ORDER BY site_id ASC ";
		} else {
			$sql .= " AND site_id=1 ";
		}
		$db->query($sql);
		while ($db->next_record()) {
			$order_info[$db->f("setting_name")] = $db->f("setting_value");
			$order_info_sites[$db->f("setting_name")] = $db->f("site_id");
		}
		$sequence = get_setting_value($order_info, $invoice_type."_sequence_number", "");
		$sequence_site = get_setting_value($order_info_sites, $invoice_type."_sequence_number", "");
		$mask = get_setting_value($order_info, $invoice_type."_number_mask", "");
		// end of invoice settings

		// if mask wasn't set use order_id as invoice number
		if (!$mask) {
			$mask = $order_id;
		}
		// replace some common tags
		$mask = str_replace("{order_id}", $order_id, $mask);
		// replace date mask
		if (is_array($date_formats) && is_array($order_placed_date)) {
			foreach ($date_formats as $key => $date_mask) {
				$mask = str_replace("{".$date_mask."}", va_date(array($date_mask), $order_placed_date), $mask);
			}
		}


		// if sequence is used for mask create a new sequence using current order_id
		$is_sequence_mask = preg_match("/#/", $mask);
		if (!$sequence && $is_sequence_mask) {
			$sequence = $order_id;
			$sequence_site = 1;
		}
		// get mask parameters for futher generation
		$seq_mask = preg_replace("/[^\#]/", "", $mask);
		$seq_mask_length = strlen($seq_mask);
		$asterisks = preg_replace("/[^\*]/", "", $mask);
		$asterisks_length = strlen($asterisks);

		if ($db->DBType == "mysql") {
			$db->query("LOCK TABLES ".$table_prefix."orders WRITE"); // lock orders table for MySQL for safe invoice update
		}
		// generate new invoice number
		$initial_mask = $mask; $invoice_number = ""; $i = 0;
		while ($invoice_number == "") {
			$i++; // calculate iterations
	  
			$invoice_number = $mask;
			if ($seq_mask_length > 0) {
				// add sequence to new invoice number
				$sequence_string = strval($sequence);
				$sequence_length = strlen($sequence_string);
				for ($ch = 0; $ch < ($seq_mask_length - $sequence_length); $ch++) {
					$sequence_string = "0".$sequence_string;
				}
				for ($ch = 0; $ch < $seq_mask_length - 1; $ch++) {
					$invoice_number = preg_replace("/\#/", $sequence_string[0], $invoice_number, 1);
					$sequence_string = substr($sequence_string, 1);
				}
				$invoice_number = preg_replace("/\#/", $sequence_string, $invoice_number);
			}
			if ($asterisks_length > 0) {
				// add random symbols to new invoice number
				$random_string = "";
				while (strlen($random_string) < $asterisks_length) {
					$random_value = mt_rand();
					$random_hash  = strtoupper(md5($sequence . $random_value . va_timestamp()));
					$random_string .= $random_hash;
				}
				for ($ch = 0; $ch < $asterisks_length; $ch++) {
					$invoice_number = preg_replace("/\*/", $random_string[$ch], $invoice_number, 1);
				}
			}
			$sql  = " SELECT order_id FROM " .$table_prefix. "orders ";
			$sql .= " WHERE invoice_number=" . $db->tosql($invoice_number, TEXT);
			$sql .= " AND order_id<>" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$invoice_number = "";
				if ($is_sequence_mask) {
					$sequence++; 
				} else {
					$mask = $initial_mask . "-" . $i;
				}
			}
		}
		// end of invoice number generation

		// update invoice number
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET invoice_number=" . $db->tosql($invoice_number, TEXT);
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);

		// unlock table
		if ($db->DBType == "mysql") {
			$db->query("UNLOCK TABLES");
		}

		// update invoice settings
		if ($is_sequence_mask) {
			$sequence++;

			if ($db->DBType == "mysql") {
				$db->query("LOCK TABLES ".$table_prefix."global_settings WRITE"); // lock global_settings table for MySQL
			}
			$sql  = " DELETE FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type='order_info'";
			$sql .= " AND setting_name=" . $db->tosql($invoice_type."_sequence_number", TEXT);
			$sql .= " AND site_id=" . $db->tosql($sequence_site, INTEGER);
			$db->query($sql);

			$sql  = " INSERT INTO " . $table_prefix . "global_settings (site_id, setting_type, setting_name, setting_value) ";
			$sql .= " VALUES (";
			$sql .= $db->tosql($sequence_site, INTEGER) . ",";
			$sql .= $db->tosql("order_info", TEXT) . ",";
			$sql .= $db->tosql($invoice_type."_sequence_number", TEXT) . ",";
			$sql .= $db->tosql($sequence, TEXT) . ")";
			$db->query($sql);

			if ($db->DBType == "mysql") {
				$db->query("UNLOCK TABLES");
			}
		}

		return $invoice_number;
	}

	function check_payment($order_id, $payment_total, $payment_currency = "")
	{
		global $db, $table_prefix;
		$errors = "";
		$exchange_rate = 1;
		$currency_decimals = 2;
		if (strlen($payment_currency)) {
			$sql  = " SELECT * FROM " . $table_prefix . "currencies ";
			$sql .= " WHERE currency_code=" . $db->tosql($payment_currency, TEXT);
			$db->query($sql);
			if ($db->next_record()) {
  			$exchange_rate = $db->f("exchange_rate");
  			$currency_decimals = $db->f("decimals_number");
			} else {
				$errors .= va_constant("CURRENCY_WRONG_VALUE_MSG");
			}
		}
		$sql  = " SELECT order_placed_date,order_total,order_status,payment_currency_code,payment_currency_rate ";
		$sql .= " FROM " . $table_prefix . "orders ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		if ($db->next_record())
		{
			$order_total = $db->f("order_total");						
 			$payment_currency_code = $db->f("payment_currency_code");
 			$payment_currency_rate = $db->f("payment_currency_rate");
			if ($payment_currency_code && !$payment_currency) {
				$sql  = " SELECT * FROM " . $table_prefix . "currencies ";
				$sql .= " WHERE currency_code=" . $db->tosql($payment_currency_code, TEXT);
				$db->query($sql);
				if ($db->next_record()) {
  				$currency_decimals = $db->f("decimals_number");
				} 
			}
			$order_total = round(($order_total * $payment_currency_rate), $currency_decimals);
			if ($order_total != $payment_total) {
				$errors .= va_constant("TRANSACTION_AMOUNT_DOESNT_MATCH_MSG");
			}
		} else {
			$errors .= va_constant("ORDER_EXISTS_ERROR");
		}

		return $errors;
	}

	function update_order_status($order_id, $status_id, $order_event, $updated_items_ids, &$status_error, $status_description = "")
	{
		global $t, $db, $db_type, $table_prefix, $settings, $order_items, $cart_items, $language_code, $va_messages;
		global $datetime_show_format, $is_admin_path, $is_sub_folder, $order_step, $currency;

		if (!isset($is_admin_path)) { $is_admin_path = false; } 
		if (!isset($is_sub_folder)) { $is_sub_folder = false; } 
		$root_folder_path = ($is_admin_path || $is_sub_folder) ? "../" : "./";

		$eol = get_eol();
		$site_url = get_setting_value($settings, "site_url");
		$user_id = get_session("session_user_id");
		$admin_id = get_session("session_admin_id");

		// get current order data from database
		$sql  = " SELECT o.*,os.status_name,os.status_type,os.paid_status,os.credit_note_action,os.stock_level_action,ps.payment_name,";
		$sql .= " ps.capture_php_lib,ps.refund_php_lib,ps.void_php_lib, ";
		$sql .= " o.currency_code, o.currency_rate, c.symbol_right, c.symbol_left, c.decimals_number, c.decimal_point, c.thousands_separator ";
		$sql .= " FROM (((" . $table_prefix . "orders o ";
		$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON os.status_id=o.order_status) ";
		$sql .= " LEFT JOIN " . $table_prefix . "payment_systems ps ON ps.payment_id=o.payment_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "currencies c ON o.currency_code=c.currency_code) ";
		$sql .= " WHERE o.order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$order_data = $db->Record;
			$order_data["order_placed_date"] = $db->f("order_placed_date", DATETIME);
			$order_data["order_shipped_date"] = $db->f("order_shipped_date", DATETIME);
			$invoice_number = $db->f("invoice_number");
			$transaction_id = $db->f("transaction_id");
			$order_site_id = $db->f("site_id");
			$order_language_code = $db->f("language_code");
			$order_user_id = $db->f("user_id");
			$order_user_type_id = $db->f("user_type_id");
			$current_status_id = $db->f("order_status");
			$current_status = get_translation($db->f("status_name"), $order_language_code);
			$current_status_type = $db->f("status_type");
			$current_paid_status = $db->f("paid_status");

			$order_payment_id = $db->f("order_payment_id");
			$order_total = $db->f("order_total");
			$paid_total = $db->f("paid_total");
			$payment_amount = $db->f("payment_amount");

			$current_credit_note_action = $db->f("credit_note_action");
			if ($current_status_type == "CREDIT_NOTE") {
				$current_credit_note_action = "";
			}
			$current_stock_action = $db->f("stock_level_action");
			if (!$current_stock_action) {
				// if there is no active action we assume that it wasn't reserved
				$current_stock_action = -1;
			}
			$payment_name = get_translation($db->f("payment_name"), $order_language_code);
			$shipping_points_amount = doubleval($db->f("shipping_points_amount"));
			$properties_points_amount = doubleval($db->f("properties_points_amount"));
			$credit_amount = $db->f("credit_amount");

			// get order currency
			$order_currency = array();
			$order_currency_code = $db->f("currency_code");
			$order_currency_rate = $db->f("currency_rate");
			$order_currency["code"] = $db->f("currency_code");
			$order_currency["rate"] = $db->f("currency_rate");
			$order_currency["left"] = $db->f("symbol_left");
			$order_currency["right"] = $db->f("symbol_right");
			$order_currency["decimals"] = $db->f("decimals_number");
			$order_currency["point"] = $db->f("decimal_point");
			$order_currency["separator"] = $db->f("thousands_separator");

			$affiliate_user_id = $db->f("affiliate_user_id");
			$tax_total = $db->f("tax_total");
			$order_data["tax_cost"] = $tax_total;
			$user_mail = strlen($order_data["email"]) ? $order_data["email"] : $order_data["delivery_email"];

			$t->set_vars($order_data);
			$t->set_var("site_url", $site_url);

			$order_placed_date = $db->f("order_placed_date", DATETIME);
			$date_formated = va_date($datetime_show_format, $order_placed_date);
			$t->set_var("order_placed_date", $date_formated);

			// set verification parameter which may require
			$placed_hour = isset($order_placed_date[3]) ? $order_placed_date[3] : 0;
			$placed_minute = isset($order_placed_date[4]) ? $order_placed_date[4] : 0;
			$placed_second = isset($order_placed_date[5]) ? $order_placed_date[5] : 0;
			$vc = md5($order_id . $placed_hour.$placed_minute.$placed_second);
			$t->set_var("vc", $vc);

		} else {
			$is_valid = false;
			$status_error = va_message("APPROPRIATE_CODE_ERROR_MSG")." - ".$order_id;
			return false;
		}

		// include language messages which user submit his order
		$active_language_code = $language_code;
		if ($order_language_code && $language_code != $order_language_code) {
			$language_code = $order_language_code;
			include($root_folder_path."messages/".$order_language_code."/messages.php");
			include($root_folder_path."messages/".$order_language_code."/cart_messages.php");
			include($root_folder_path."messages/".$order_language_code."/download_messages.php");
			if (file_exists($root_folder_path."messages/".$order_language_code."/custom_messages.php")) {
				include_once($root_folder_path."messages/".$order_language_code."/custom_messages.php");
			}
		}

		// get new status data
		$is_valid = true; $is_update = false; $status_error = ""; $status_php_lib = ""; $payment_php_lib = ""; $order_event_id = "";
		$sql  = " SELECT * FROM " . $table_prefix . "order_statuses ";
		$sql .= " WHERE status_id=" . $db->tosql($status_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$status_id = $db->f("status_id");
			$status_php_lib = $db->f("status_php_lib");

			$paid_status = $db->f("paid_status");
			$generate_invoice = $db->f("generate_invoice");
			$credit_note_action = $db->f("credit_note_action");

			$download_activation = $db->f("download_activation");
			$download_notify = $db->f("download_notify");
			$commission_action = $db->f("commission_action");
			$stock_level_action = $db->f("stock_level_action");
			if (!$stock_level_action) {
				// if there is no active action we assume that it wasn't reserved
				$stock_level_action = -1;
			}
			$points_action = $db->f("points_action"); // users points
			$credit_action = $db->f("credit_action"); // users credits 
			$status_name = get_translation($db->f("status_name"));
			$status_type = $db->f("status_type");
			if ($status_type == "CREDIT_NOTE") {
				$credit_note_action = "";
			}

			// email settings
			$email_headers = array();
			$mail_notify = $db->f("mail_notify");
			$mail_from = $db->f("mail_from");
			if (!strlen($mail_from)) { $mail_from = $settings["admin_email"]; }
			$email_headers["from"] = $mail_from;
			$email_headers["cc"] = $db->f("mail_cc");
			$email_headers["bcc"] = $db->f("mail_bcc");
			$email_headers["reply_to"] = $db->f("mail_reply_to");
			$email_headers["return_path"] = $db->f("mail_return_path");
			$mail_type = $db->f("mail_type");
			$email_headers["mail_type"] = $mail_type;
			$mail_pdf_invoice = $db->f("mail_pdf_invoice");
			$mail_status_attachments = $db->f("mail_status_attachments");
			$mail_subject = get_translation($db->f("mail_subject"));
			$mail_body = get_translation($db->f("mail_body"));
			// sms settings
			$sms_notify = $db->f("sms_notify");
			$sms_recipient = $db->f("sms_recipient");
			$sms_originator = $db->f("sms_originator");
			$sms_message = get_translation($db->f("sms_message"));

			// merchant notify settings
			$merchant_headers = array();
			$merchant_notify = $db->f("merchant_notify");
			$merchant_to = $db->f("merchant_to");
			$merchant_from = $db->f("merchant_from");
			if (!strlen($merchant_from)) { $merchant_from = $settings["admin_email"]; }
			$merchant_headers["from"] = $merchant_from;
			$merchant_headers["cc"] = $db->f("merchant_cc");
			$merchant_headers["bcc"] = $db->f("merchant_bcc");
			$merchant_headers["reply_to"] = $db->f("merchant_reply_to");
			$merchant_headers["return_path"] = $db->f("merchant_return_path");
			$merchant_mail_type = $db->f("merchant_mail_type");
			$merchant_headers["mail_type"] = $merchant_mail_type;
			$merchant_subject = get_translation($db->f("merchant_subject"));
			$merchant_body = get_translation($db->f("merchant_body"));
			// merchant sms settings
			$merchant_sms_notify = $db->f("merchant_sms_notify");
			$merchant_sms_recipient = $db->f("merchant_sms_recipient");
			$merchant_sms_originator = $db->f("merchant_sms_originator");
			$merchant_sms_message = get_translation($db->f("merchant_sms_message"));

			// supplier notify settings
			$supplier_headers = array();
			$supplier_notify = $db->f("supplier_notify");
			$supplier_to = $db->f("supplier_to");
			$supplier_from = $db->f("supplier_from");
			if (!strlen($supplier_from)) { $supplier_from = $settings["admin_email"]; }
			$supplier_headers["from"] = $supplier_from;
			$supplier_headers["cc"] = $db->f("supplier_cc");
			$supplier_headers["bcc"] = $db->f("supplier_bcc");
			$supplier_headers["reply_to"] = $db->f("supplier_reply_to");
			$supplier_headers["return_path"] = $db->f("supplier_return_path");
			$supplier_mail_type = $db->f("supplier_mail_type");
			$supplier_headers["mail_type"] = $supplier_mail_type;
			$supplier_subject = get_translation($db->f("supplier_subject"));
			$supplier_body = get_translation($db->f("supplier_body"));
			// supplier sms settings
			$supplier_sms_notify = $db->f("supplier_sms_notify");
			$supplier_sms_recipient = $db->f("supplier_sms_recipient");
			$supplier_sms_originator = $db->f("supplier_sms_originator");
			$supplier_sms_message = get_translation($db->f("supplier_sms_message"));

			// affiliate notify settings
			$affiliate_headers = array();
			$affiliate_notify = $db->f("affiliate_notify");
			$affiliate_to = $db->f("affiliate_to");
			$affiliate_from = $db->f("affiliate_from");
			if (!strlen($affiliate_from)) { $affiliate_from = $settings["admin_email"]; }
			$affiliate_headers["from"] = $affiliate_from;
			$affiliate_headers["cc"] = $db->f("affiliate_cc");
			$affiliate_headers["bcc"] = $db->f("affiliate_bcc");
			$affiliate_headers["reply_to"] = $db->f("affiliate_reply_to");
			$affiliate_headers["return_path"] = $db->f("affiliate_return_path");
			$affiliate_mail_type = $db->f("affiliate_mail_type");
			$affiliate_headers["mail_type"] = $affiliate_mail_type;
			$affiliate_subject = get_translation($db->f("affiliate_subject"));
			$affiliate_body = get_translation($db->f("affiliate_body"));
			// affiliate sms settings
			$affiliate_sms_notify = $db->f("affiliate_sms_notify");
			$affiliate_sms_recipient = $db->f("affiliate_sms_recipient");
			$affiliate_sms_originator = $db->f("affiliate_sms_originator");
			$affiliate_sms_message = get_translation($db->f("affiliate_sms_message"));

			// admin notify settings
			$admin_headers = array();
			$admin_notify = $db->f("admin_notify");
			$admin_to = $db->f("admin_to");
			$admin_to_groups_ids = $db->f("admin_to_groups_ids");
			$admin_from = $db->f("admin_from");
			if (!strlen($admin_from)) { $admin_from = $settings["admin_email"]; }
			$admin_headers["from"] = $admin_from;
			$admin_headers["cc"] = $db->f("admin_cc");
			$admin_headers["bcc"] = $db->f("admin_bcc");
			$admin_headers["reply_to"] = $db->f("admin_reply_to");
			$admin_headers["return_path"] = $db->f("admin_return_path");
			$admin_mail_type = $db->f("admin_mail_type");
			$admin_headers["mail_type"] = $admin_mail_type;
			$admin_pdf_invoice = $db->f("admin_pdf_invoice");
			$admin_status_attachments = $db->f("admin_status_attachments");
			$admin_subject = get_translation($db->f("admin_subject"));
			$admin_body = get_translation($db->f("admin_body"));
			// admin sms settings
			$admin_sms_notify = $db->f("admin_sms_notify");
			$admin_sms_recipient = $db->f("admin_sms_recipient");
			$admin_sms_originator = $db->f("admin_sms_originator");
			$admin_sms_message = $db->f("admin_sms_message");
		} else {
			$is_valid = false;
			$status_error = str_replace("{order_id}", $order_id, va_constant("STATUS_CANT_BE_UPDATED_MSG")) . str_replace("{status_id}", $status_id, va_constant("CANT_FIND_STATUS_MSG"));
			//"The status for order No " . $order_id . " can't be updated. Can't find the status with ID: " . $status_id;              			
			return false;
		}

		// additional order data process
		// get library to handle status change
		if ($status_type == "CAPTURE" || $status_type == "CAPTURED") {
			$payment_php_lib = $order_data["capture_php_lib"];
		} elseif ($status_type == "REFUND" || $status_type == "REFUNDED") {
			$payment_php_lib = $order_data["refund_php_lib"];
		} elseif ($status_type == "VOID" || $status_type == "VOIDED") {
			$payment_php_lib = $order_data["void_php_lib"];
		}

		// preparing downloadable data
		// get download links
		$links = get_order_links($order_id);
		$links_notify = ($download_notify && $links["text"] != "");
		// get serial numbers
		$order_serials = get_serial_numbers($order_id);
		$serials_notify = ($download_notify && $order_serials["text"] != "");
		// get gift vouchers
		$order_vouchers = get_gift_vouchers($order_id);
		$vouchers_notify = ($download_notify && $order_vouchers["text"] != "");
		// end of additional order data process


		// update successful order payment
		if ($order_payment_id && $paid_status) {
			$sql  = " UPDATE " . $table_prefix ."orders_payments ";
			$sql .= " SET payment_status=". $db->tosql($status_id, INTEGER);
			$sql .= ", payment_paid=". $db->tosql($paid_status, INTEGER);
			$sql .= ", transaction_id=". $db->tosql($transaction_id, TEXT);
			$sql .= " WHERE order_payment_id=" . $db->tosql($order_payment_id, INTEGER);
			$db->query($sql);

  		$sql  = " SELECT SUM(payment_amount) AS paid_total FROM " . $table_prefix . "orders_payments ";
  		$sql .= " WHERE order_id=".$db->tosql($order_id, INTEGER);
			$paid_total = get_db_value($sql);

			$sql  = " UPDATE " . $table_prefix ."orders ";
			$sql .= " SET order_payment_id=0, payment_amount=NULL, ";
			$sql .= " paid_total=" . $db->tosql($paid_total, FLOAT);
			$sql .= " WHERE order_payment_id=" . $db->tosql($order_payment_id, INTEGER);
			$db->query($sql);

			if ($order_total > $paid_total) {
				// order was partially paid only so we have to check if partial payment status active to set it
				$sql  = " SELECT status_id FROM " . $table_prefix . "order_statuses ";
				$sql .= " WHERE status_type='PARTIALLY_PAID' ";
				$sql .= " AND is_active=1 ";
				$db->query($sql);
				if ($db->next_record()) {
					$partially_paid_status = $db->f("status_id");
					update_order_status($order_id, $partially_paid_status, true, "", $status_error);
					return;
				}
			}
		}

		$shipping_ids = ""; $shipping_codes = ""; $shipping_descs = ""; $shipping_costs = 0; $tracking_ids = "";
		$shipping_module_name = ""; $tracking_url = "";
		$shipping_company_names = ""; $shipping_company_urls = ""; 
		$shipping_image_small = ""; $shipping_image_small_alt = ""; $shipping_image_large = ""; $shipping_image_large_alt = ""; 
		$sql  = " SELECT os.shipping_id, os.shipping_code, os.shipping_desc, os.shipping_cost, os.tracking_id, ";
		$sql .= " st.image_small, st.image_small_alt, st.image_large, st.image_large_alt, ";
		$sql .= " sm.shipping_module_name, sm.tracking_url, sc.company_name, sc.company_url ";
		$sql .= " FROM (((" . $table_prefix . "orders_shipments os ";
		$sql .= " LEFT JOIN " . $table_prefix . "shipping_types st ON os.shipping_id=st.shipping_type_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "shipping_modules sm ON sm.shipping_module_id=st.shipping_module_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "shipping_companies sc ON os.shipping_company_id=sc.shipping_company_id) ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			do {
				$shipping_id = $db->f("shipping_id");
				$shipping_code = $db->f("shipping_code");
				$shipping_desc = $db->f("shipping_desc");
				$shipping_cost = $db->f("shipping_cost");
				$shipping_module_name = $db->f("shipping_module_name");
				$tracking_id = $db->f("tracking_id");
				$tracking_url = $db->f("tracking_url");
				$tracking_url = str_replace("{tracking_id}", $tracking_id, $tracking_url);
				if ($shipping_ids) {
					$shipping_ids .= "; "; $shipping_codes .= "; "; $shipping_descs .= "; "; $tracking_ids .= "; ";
				}
				$shipping_ids .= $shipping_id; 
				$shipping_codes .= $shipping_code; 
				$shipping_descs .= $shipping_desc; 
				$shipping_costs += $shipping_cost;
				$tracking_ids .= $tracking_id;
				$shipping_company_name = $db->f("company_name");
				$shipping_company_url = $db->f("company_url");
				$shipping_company_url = str_replace("{tracking_id}", $tracking_id, $shipping_company_url);

				if ($shipping_company_name && $shipping_company_names) { $shipping_company_names .= "; "; $shipping_company_urls .= "; "; }
				$shipping_company_names .=  $shipping_company_name;
				$shipping_company_urls .= $shipping_company_url;

				$shipping_image_small = $db->f("image_small");
				$shipping_image_small_alt = $db->f("image_small_alt");
				$shipping_image_large = $db->f("image_large");
				$shipping_image_large_alt = $db->f("image_large_alt");
			} while ($db->next_record());
		} else {
			// get old shipping data
			$shipping_ids = $order_data["shipping_type_id"]; 
			$shipping_codes = $order_data["shipping_type_code"];
			$shipping_descs = $order_data["shipping_type_desc"]; 
			$shipping_costs = $order_data["shipping_cost"]; 
			$tracking_ids = $order_data["shipping_tracking_id"];
		}

		// apply a php library for Capture, Refund or Void status
		if ($is_valid && $status_id != $current_status_id && strlen($payment_php_lib)) {
			$root_folder_path = $is_admin_path ? "../" : "";
			$error_message = "";
			if (file_exists($root_folder_path . $payment_php_lib)) {
				if (!$order_step) { $order_step = "status"; }
				// get payment data
				$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
				get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_params, $pass_data, $variables, $order_step);

				$cwd = getcwd(); // save current working directory as it could be changed
				$cap = $is_admin_path;  $csf = $is_sub_folder;
				include($root_folder_path . $payment_php_lib);
				chdir ($cwd); // set saved working directory
				$is_admin_path = $cap; $is_sub_folder = $csf;
			} else {
				$error_message = va_constant("APPROPRIATE_LIBRARY_ERROR_MSG") .": " . $root_folder_path . $payment_php_lib;
			}
			if (strlen($error_message)) {
				$is_valid = false;
				$status_error = str_replace("{order_id}", $order_id, va_constant("STATUS_CANT_BE_UPDATED_MSG")) . $error_message;
			}
		}


		$other_statuses = false; $order_items_ids = "";
		// arrays for updated order items 
		$items_statuses = array(); $items_paid = array(); $items_stock_actions = array(); $credit_notes_actions = array(); 
		// check information if any of order items has a different status 
		if (strlen($updated_items_ids)) {
			$sql  = " SELECT oi.order_item_id, oi.item_name, ";
			$sql .= " os.status_id, os.status_name, os.status_type, os.paid_status, os.download_activation, os.credit_note_action, os.stock_level_action ";
			$sql .= " FROM (" . $table_prefix . "orders_items oi ";
			$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON os.status_id=oi.item_status) ";
			$sql .= " WHERE oi.order_item_id IN (" . $db->tosql($updated_items_ids, INTEGERS_LIST) . ")" ;
			$db->query($sql);
			while ($db->next_record()) {
				$order_item_id = $db->f("order_item_id");
				$item_name = get_translation($db->f("item_name"));
				$cur_item_status = $db->f("status_id");
				$item_status_name = get_translation($db->f("status_name"));
				$item_status_type = $db->f("status_type");
				$item_paid_status = $db->f("paid_status");

				$items_paid[$order_item_id] = $item_paid_status;
				$item_credit_note_action = $db->f("credit_note_action");
				if ($item_status_type == "CREDIT_NOTE") {
					$item_credit_note_action = "";
				}
				$credit_notes_actions[$order_item_id] = $item_credit_note_action;
				$item_stock_action = $db->f("stock_level_action");
				if (!$item_stock_action) {
					$item_stock_action = -1;
				}
				$items_stock_actions[$order_item_id] = $item_stock_action;
				$new_item_status = get_param("item_status_" . $order_item_id);
				if (!strlen($new_item_status)) { 
					// if there is no status parameter for order item use current item status
					$new_item_status = $cur_item_status;
				}
				if ($cur_item_status != $new_item_status && ($status_id != $new_item_status || $status_id == $current_status_id)) {
				//if ($new_item_status != $status_id || $cur_item_status == $new_item_status) {
					$other_statuses = true;
				}

				// check items with updated statuses
				if ($cur_item_status != $new_item_status) {
					if ($order_items_ids) { $order_items_ids .= ","; }
					$order_items_ids .= $order_item_id;
					$items_statuses[$new_item_status][] = array($order_item_id, $item_name, $item_status_name);
				}
			}
		}

		if ($is_valid) {
			$r = new VA_Record($table_prefix . "orders_events");
			$r->add_where("event_id", INTEGER);
			$r->add_textbox("order_id", INTEGER);
			$r->add_textbox("status_id", INTEGER);
			$r->add_textbox("admin_id", INTEGER);
			$r->add_textbox("order_items", TEXT);
			$r->add_textbox("event_date", DATETIME);
			$r->add_textbox("event_type", TEXT);
			$r->add_textbox("event_name", TEXT);
			$r->add_textbox("event_description", TEXT);
			$r->set_value("order_id", $order_id);
			$r->set_value("admin_id", get_session("session_admin_id"));
			$r->set_value("event_date", va_time());

			if ($current_status_id != $status_id) {
				// update status
				$is_update = true;
				$sql  = " UPDATE " . $table_prefix . "orders ";
				$sql .= " SET order_status=" . $db->tosql($status_id, INTEGER);
				// update paid status and date when it was paid
				if ($paid_status == 1 && $current_paid_status == 0) {
					$sql .= " , is_paid=1, order_paid_date=" . $db->tosql(va_time(), DATETIME);
				} else if ($paid_status == 0 && $current_paid_status == 1) {
					$sql .= " , is_paid=0, order_paid_date=NULL ";
				}
				if ($status_type == "SHIPPED" && !is_array($order_data["order_shipped_date"])) {
					// set date only for first SHIPPED status 
					$sql .= " , order_shipped_date=" . $db->tosql(va_time(), DATETIME);
				}
				$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
				$db->query($sql);

				// generate and update invoice number if it wasn't set
				if($generate_invoice && !strlen($invoice_number)) {
					$invoice_number = generate_invoice_number($order_data);
				}

				if (!$other_statuses) {
					// update items status
					$sql  = " UPDATE " . $table_prefix . "orders_items ";
					$sql .= " SET item_status=" . $db->tosql($status_id, INTEGER);
					$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
					$db->query($sql);

					if ($download_activation == 1) {
						$sql = "UPDATE " . $table_prefix . "items_downloads SET activated=1 WHERE order_id=" . $db->tosql($order_id, INTEGER);
						$db->query($sql);
						$sql = "UPDATE " . $table_prefix . "orders_items_serials SET activated=1 WHERE order_id=" . $db->tosql($order_id, INTEGER);
						$db->query($sql);
						$sql = "UPDATE " . $table_prefix . "coupons SET is_active=1 WHERE order_id=" . $db->tosql($order_id, INTEGER);
						$db->query($sql);
					} elseif ($download_activation == 0) {
						$sql = "UPDATE " . $table_prefix . "items_downloads SET activated=0 WHERE order_id=" . $db->tosql($order_id, INTEGER);
						$db->query($sql);
						$sql = "UPDATE " . $table_prefix . "orders_items_serials SET activated=0 WHERE order_id=" . $db->tosql($order_id, INTEGER);
						$db->query($sql);
						$sql = "UPDATE " . $table_prefix . "coupons SET is_active=0 WHERE order_id=" . $db->tosql($order_id, INTEGER);
						$db->query($sql);
					}
				}

				if ($order_event) {
					// save event with updated status
					if ($db->DBType == "postgre") {
						$event_id = get_db_value(" SELECT NEXTVAL('seq_" . $table_prefix . "orders_events ') ");
						$r->change_property("event_id", USE_IN_INSERT, true);
						$r->set_value("event_id", $event_id);
					}
					$r->set_value("status_id", $status_id);
					$r->set_value("event_type", "update_order_status"); //"Update status"
					if ($current_status_id > 0) {
						$r->set_value("event_name", $current_status . " &ndash;&gt; " . $status_name);
					} else {
						// new order added
						if ($payment_name) {
							$r->set_value("event_name", $status_name . " (".$payment_name.")");
						} else {
							$r->set_value("event_name", $status_name);
						}
					}
					$r->set_value("event_description", $status_description); 
					$r->insert_record();
					$r->change_property("event_id", USE_IN_INSERT, false);
					if ($db->DBType == "mysql") {
						$order_event_id = get_db_value(" SELECT LAST_INSERT_ID() ");
					} elseif ($db->DBType == "postgre") {
						$order_event_id = $r->get_value("event_id");
					} elseif ($db->DBType == "access") {
						$order_event_id = get_db_value(" SELECT @@IDENTITY ");
					} else {
						$order_event_id = get_db_value(" SELECT MAX(payment_id) FROM " . $table_prefix . "users_payments");
					}

					$sql  = " UPDATE " . $table_prefix . "orders_attachments ";
					$sql .= " SET event_id=" . $db->tosql($order_event_id, INTEGER);
					$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
					$sql .= " AND event_id=0 ";
					if ($is_admin_path) {
						$sql .= " AND admin_id=". $db->tosql($admin_id, INTEGER);
					} else {
						$sql .= " AND user_id=". $db->tosql($user_id, INTEGER);
					}
					$db->query($sql);
				}

				if ($mail_notify || $sms_notify
					|| $merchant_notify || $merchant_sms_notify
					|| $supplier_notify || $supplier_sms_notify
					|| $affiliate_notify || $affiliate_sms_notify
					|| $admin_notify || $admin_sms_notify
					|| $links_notify || $serials_notify || $vouchers_notify) {

					// get the full information about order and prepare basket variable

					if ($is_admin_path) {
						$user_template_path = $settings["templates_dir"];
						if (preg_match("/^\.\//", $user_template_path)) {
							$user_template_path = str_replace("./", "../", $user_template_path);
						} elseif (!preg_match("/^\//", $user_template_path)) {
							$user_template_path = "../" . $user_template_path;
						}
						$t->set_template_path($user_template_path);
					}
					$t->set_vars($order_data);
					// set global shipping information for order
					$t->set_var("shipping_id", $shipping_ids);
					$t->set_var("shipping_code", $shipping_codes);
					$t->set_var("shipping_desc", $shipping_descs);
					$t->set_var("shipping_type_desc", $shipping_descs);
					$t->set_var("shipping_cost", $shipping_costs);
					$t->set_var("shipping_tracking_id", $tracking_ids);
					$t->set_var("shipping_module_name", htmlspecialchars($shipping_module_name));
					$t->set_var("tracking_id", $tracking_ids);
					$t->set_var("tracking_url", htmlspecialchars($tracking_url));
					$t->set_var("delivery_company_name", $shipping_company_names);
					$t->set_var("delivery_company_url", $shipping_company_urls);
	
					$shipping_image_small_tag = "";
					if ($shipping_image_small) {
						if (!preg_match("/^http\:\/\//", $shipping_image_small)) { 
							$shipping_image_small = $site_url.$shipping_image_small;
						}
						$t->set_var("src", htmlspecialchars($shipping_image_small));
						$t->set_var("alt", htmlspecialchars($shipping_image_small_alt));
						$t->set_var("shipping_image_small_src", htmlspecialchars($shipping_image_small));
						$t->set_var("shipping_image_small_alt", htmlspecialchars($shipping_image_small_alt));
						$shipping_image_small_tag = "<img src=\"".htmlspecialchars($shipping_image_small)."\" alt=\"".htmlspecialchars($shipping_image_small_alt)."\">";
					}
					$t->set_var("shipping_image_small", $shipping_image_small_tag);

					$shipping_image_large_tag = "";
					if ($shipping_image_large) {
						if (!preg_match("/^http\:\/\//", $shipping_image_large)) { 
							$shipping_image_large = $site_url.$shipping_image_large;
						}
						$t->set_var("src", htmlspecialchars($shipping_image_large));
						$t->set_var("alt", htmlspecialchars($shipping_image_large_alt));
						$t->set_var("shipping_image_large_src", htmlspecialchars($shipping_image_large));
						$t->set_var("shipping_image_large_alt", htmlspecialchars($shipping_image_large_alt));
						$shipping_image_large_tag = "<img src=\"".htmlspecialchars($shipping_image_large)."\" alt=\"".htmlspecialchars($shipping_image_large_alt)."\">";
					}
					$t->set_var("shipping_image_large", $shipping_image_large_tag);

					// set basket tag
					$t->set_file("basket_html", "email_basket.html");
					$basket = show_order_items($order_id, true, "email");
					$t->parse("basket_html", false);

					$t->set_file("basket_text", "email_basket.txt");
					show_order_items($order_id, true, "email");
					$t->parse("basket_text", false);
					if ($is_admin_path) {
						$t->set_template_path($settings["admin_templates_dir"]);
					}

					$company_select = ""; $delivery_company_select = "";
					$state = ""; $delivery_state = "";
					$country = ""; $delivery_country = "";
					$cc_type_code = ""; $cc_type_name = "";
					$company_select = get_translation(get_db_value("SELECT company_name FROM " . $table_prefix . "companies WHERE company_id=" . $db->tosql($order_data["company_id"], INTEGER, true, false)));
					$delivery_company_select = get_translation(get_db_value("SELECT company_name FROM " . $table_prefix . "companies WHERE company_id=" . $db->tosql($order_data["delivery_company_id"], INTEGER, true, false)));
					$state = get_translation(get_db_value("SELECT state_name FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($order_data["state_id"], INTEGER, true, false)));
					$delivery_state = get_translation(get_db_value("SELECT state_name FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($order_data["delivery_state_id"], INTEGER, true, false)));
					$country = get_translation(get_db_value("SELECT country_name FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($order_data["country_id"], INTEGER, true, false)));
					$delivery_country = get_translation(get_db_value("SELECT country_name FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($order_data["delivery_country_id"], INTEGER, true, false)));
					if ($order_data["cc_type"]) {
						$sql = "SELECT credit_card_code, credit_card_name FROM " . $table_prefix . "credit_cards WHERE credit_card_id=" . $db->tosql($order_data["cc_type"], INTEGER, true, false);
						$db->query($sql);
						if ($db->next_record()) {
							$cc_type_code = $db->f("credit_card_code");
							$cc_type_name = $db->f("credit_card_name");
						}
					}

					// check cc number data
					$cc_number_first = ""; $cc_number_last = "";
					$cc_number = $order_data["cc_number"];
					if ($cc_number) {
						if (!preg_match("/^[\d\s\*\-\#\.]+$/", $cc_number)) {
							$cc_number = va_decrypt($cc_number);
						}
						$cc_number_len = strlen($cc_number);
						if ($cc_number_len > 6) {
							$cc_number_first = substr($cc_number, 0, 6);
						} else {
							$cc_number_first = $cc_number;
						}
						if ($cc_number_len > 4) {
							$cc_number_last = substr($cc_number, $cc_number_len - 4);
						} else {
							$cc_number_last = $cc_number;
						}
					}

					$t->set_var("basket", $basket);
					$t->set_var("company_select", $company_select);
					$t->set_var("state", $state);
					$t->set_var("country", $country);
					$t->set_var("delivery_company_select", $delivery_company_select);
					$t->set_var("delivery_state", $delivery_state);
					$t->set_var("delivery_country", $delivery_country);
					$t->set_var("cc_type", $cc_type_name);
					$t->set_var("cc_type_code", $cc_type_code);
					$t->set_var("cc_type_name", $cc_type_name);
					$t->set_var("cc_number_first", $cc_number_first);
					$t->set_var("cc_number_last", $cc_number_last);


					// check for merchants products
					$merchants = array();
					if ($merchant_notify || $merchant_sms_notify) {
						for ($ci = 0; $ci < sizeof($cart_items); $ci++) {
							$cart_item = $cart_items[$ci];
							$merchant_id = $cart_item["merchant_id"];
							if ($merchant_id) {
								$item_text = $cart_item["item_title"];
								if ($cart_item["item_properties_text"]) {
									$item_text .= " (" . $cart_item["item_properties_text"] . ")";
								}
								$item_text .= " " . PROD_QTY_COLUMN . ": " . $cart_item["quantity"] . " " . currency_format($cart_item["item_total"], $order_currency);

								if (isset($merchants[$merchant_id])) {
									$merchants[$merchant_id]["merchant_items_text"] .= $eol . $item_text;
									$merchants[$merchant_id]["merchant_items_html"] .= "<br>" . $eol . $item_text;
								} else {
									$merchants[$merchant_id] = array(
										"merchant_id" => $cart_item["merchant_id"],
										"merchant_email" => $cart_item["merchant_email"], "merchant_name" => $cart_item["merchant_name"],
										"merchant_first_name" => $cart_item["merchant_first_name"], "merchant_last_name" => $cart_item["merchant_last_name"],
										"merchant_cell_phone" => $cart_item["merchant_cell_phone"],
										"merchant_items_text" => $item_text, "merchant_items_html" => $item_text,
									);
								}
							}
						}
					} // end check merchants products

					// check for suppliers products
					$suppliers = array();
					if ($supplier_notify || $supplier_sms_notify) {
						foreach ($order_items as $id => $item) {
							$supplier_id = $item["supplier_id"];
							if ($supplier_id) {
								if (!isset($suppliers[$supplier_id])) {
									$suppliers[$supplier_id] = array(
										"supplier_id" => $item["supplier_id"],
										"supplier_email" => $item["supplier_email"], "supplier_name" => $item["supplier_name"],
										"supplier_short_desc" => $item["supplier_short_desc"], "supplier_full_desc" => $item["supplier_full_desc"],
										"supplier_cell_phone" => $item["supplier_cell_phone"],
										"items" => array(), 
									);
								}
								$suppliers[$supplier_id]["items"][$id] = $item;
							}
						}
					} // end check suppliers products


					// check for affiliates products
					$affiliates = array();
					if ($affiliate_notify || $affiliate_sms_notify) {
						foreach ($order_items as $id => $item) {
							$affiliate_id = $item["affiliate_id"];
							if ($affiliate_id) {
								if (!isset($affiliates[$affiliate_id])) {
									$affiliates[$affiliate_id] = array(
										"affiliate_id" => $item["affiliate_id"],
										"affiliate_email" => $item["affiliate_email"], "affiliate_name" => $item["affiliate_name"],
										"affiliate_first_name" => $item["affiliate_first_name"], "affiliate_last_name" => $item["affiliate_last_name"],
										"affiliate_cell_phone" => $item["affiliate_cell_phone"],
										"items" => array(), 
									);
								}
								$affiliates[$affiliate_id]["items"][$id] = $item;
							}
						}
					} // end check affiliates products
				}

				// pdf invoice notification
				$pdf_invoice = "";
				if (($mail_notify && $mail_pdf_invoice) || ($admin_notify && $admin_pdf_invoice)) {
					include_once(dirname(__FILE__)."/invoice_functions.php");
					$pdf_invoice = pdf_invoice($order_id);
				}

				$user_attachments = array(); $admin_attachments = array();
				if (($mail_notify && $mail_status_attachments) || ($admin_notify && $admin_status_attachments)) {
					$sql = " SELECT * FROM " . $table_prefix . "orders_attachments ";
					$sql.= " WHERE order_id=".$db->tosql($order_id, INTEGER);
					$sql.= " AND event_id=".$db->tosql($order_event_id, INTEGER);
					$db->query($sql);
					while ($db->next_record()) {
					  $file_name = $db->f("file_name");
					  $file_path = $db->f("file_path");
						if ($is_admin_path || $is_sub_folder) {
							// for admin or subfolder folder use one level up if path is not absolute
							if (!preg_match("/^[\/\\\\]/", $file_path) && !preg_match("/\:/", $file_path)) {
								$file_path = "../".$file_path;
							}
						}
						if ($mail_status_attachments) { $user_attachments[] = array($file_name, $file_path); }
						if ($admin_status_attachments) { $admin_attachments[] = array($file_name, $file_path); }
					}					
				}

				// customer notification
				if ($mail_notify) {
					$attachments = array();
					if ($mail_pdf_invoice) {
						$attachments[] = array("Invoice_".$order_id.".pdf", $pdf_invoice, "buffer");
					}
					if (count($user_attachments)) {
						foreach ($user_attachments as $user_attachemnt)  { $attachments[] = $user_attachemnt; }
					}

					$t->set_block("mail_subject", $mail_subject);
					$t->set_block("mail_body", $mail_body);
					$mail_type_code = ($mail_type == 1) ? "html" : "text";

					// set basket
					$t->set_var("basket", $t->get_var("basket_" . $mail_type_code));
					// set full order items tag
					set_items_tag($order_items, $mail_type, $mail_body, "order_items");
					// set download links
					$t->set_var("links", $links[$mail_type_code]);
					// set serial numbers
					$t->set_var("serials", $order_serials[$mail_type_code]);
					$t->set_var("serial_numbers", $order_serials[$mail_type_code]);
					// set serial numbers
					$t->set_var("vouchers", $order_vouchers[$mail_type_code]);
					$t->set_var("gift_vouchers", $order_vouchers[$mail_type_code]);

					$t->parse("mail_subject", false);
					$t->parse("mail_body", false);
					$mail_body = str_replace("\r", "", $t->get_var("mail_body"));
					$notify_sent = va_mail($user_mail, $t->get_var("mail_subject"), $mail_body, $email_headers, $attachments);
					if ($notify_sent) {
						$r->set_value("event_date", va_time());
						$r->set_value("event_type", "status_notification_sent"); //"Email notification sent"
						$r->set_value("event_name", $t->get_var("mail_subject"));
						$r->set_value("event_description", $mail_body);
						$r->insert_record();
					}
				}

				if ($sms_notify) {
					if (!$sms_recipient) { $sms_recipient = $order_data["cell_phone"]; }
					$t->set_block("sms_recipient", $sms_recipient);
					$t->set_block("sms_originator", $sms_originator);
					$t->set_block("sms_message", $sms_message);

					// set download links
					$t->set_var("links", $links["text"]);
					// set serial numbers
					$t->set_var("serials", $order_serials["text"]);
					$t->set_var("serial_numbers", $order_serials["text"]);
					// set serial numbers
					$t->set_var("vouchers", $order_vouchers["text"]);
					$t->set_var("gift_vouchers", $order_vouchers["text"]);

					$t->parse("sms_recipient", false);
					$t->parse("sms_originator", false);
					$t->parse("sms_message", false);

					if (sms_send_allowed($t->get_var("sms_recipient"))) {
						$sms_sent = sms_send($t->get_var("sms_recipient"), $t->get_var("sms_message"), $t->get_var("sms_originator"), $sms_errors);
					} else {
						$sms_sent = false;
					}
					if ($sms_sent) {
						$event_description = $t->get_var("sms_message");

						$r->set_value("event_date", va_time());
						$r->set_value("event_type", "status_sms_sent"); //"SMS notification sent");
						$r->set_value("event_name", $t->get_var("sms_recipient"));
						$r->set_value("event_description", $event_description);
						$r->insert_record();
					}
				}
				// end user notification

				// merchant, supplier and affiliate notifications
				// don't send information about links, serials and vouchers for merchants as it has the whole order information
				$t->set_var("links",   "");
				$t->set_var("serials", "");
				$t->set_var("serial_numbers", "");
				$t->set_var("vouchers", "");
				$t->set_var("gift_vouchers", "");

				// start merchant notifications
				if ($merchant_notify) {
					// set email templates
					$t->set_block("mail_subject", $merchant_subject);
					$t->set_block("mail_body", $merchant_body);
					foreach ($merchants as $merchant_id => $merchant_info) {
						$t->set_vars($merchant_info);

						if ($merchant_to) {
							$merchant_mail = $merchant_to; 
						} else {
							$merchant_mail = $merchant_info["merchant_email"]; 
						}
						$merchant_type_code = ($merchant_mail_type == 1) ? "html" : "text";

						// set basket
						$t->set_var("basket", "");
						// set merchant items
						$t->set_var("merchant_items", $merchant_info["merchant_items_" . $merchant_type_code]);

						$t->parse("mail_subject", false);
						$t->parse("mail_body", false);
						$mail_body = str_replace("\r", "", $t->get_var("mail_body"));
						$notify_sent = va_mail($merchant_mail, $t->get_var("mail_subject"), $mail_body, $merchant_headers);
						if ($notify_sent) {
							$r->set_value("event_date", va_time());
							$r->set_value("event_type", "status_merchant_email_sent"); //"Merchant email notification sent"
							$r->set_value("event_name", $t->get_var("mail_subject"));
							$r->set_value("event_description", $mail_body);
							$r->insert_record();
						}
					}
				}

				if ($merchant_sms_notify) {
					foreach ($merchants as $merchant_id => $merchant_info) {
						$t->set_vars($merchant_info);
						if ($merchant_sms_recipient) { 
							$sms_recipient = $merchant_sms_recipient; 
						} else {
							$sms_recipient = $merchant_info["merchant_cell_phone"]; 
						}
						$t->set_block("sms_recipient", $sms_recipient);
						$t->set_block("sms_originator", $merchant_sms_originator);
						$t->set_block("sms_message", $merchant_sms_message);

						// set basket
						$t->set_var("basket", "");
						// set merchant items
						$t->set_var("merchant_items", $merchant_info["merchant_items_text"]);

						$t->parse("sms_recipient", false);
						$t->parse("sms_originator", false);
						$t->parse("sms_message", false);

						if (sms_send_allowed($t->get_var("sms_recipient"))) {
							$merchant_sms_sent = sms_send($t->get_var("sms_recipient"), $t->get_var("sms_message"), $t->get_var("sms_originator"), $sms_errors);
						} else {
							$merchant_sms_sent = false;
						}
						if ($merchant_sms_sent) {
							$event_description = $t->get_var("sms_message");

							$r->set_value("event_date", va_time());
							$r->set_value("event_type", "status_merchant_sms_sent"); 
							$r->set_value("event_name", $t->get_var("sms_recipient"));
							$r->set_value("event_description", $event_description);
							$r->insert_record();
						}
					}
				}
				// end merchant notifications

				// start supplier notifications
				if ($supplier_notify) {
					// set email templates
					$t->set_block("mail_subject", $supplier_subject);
					$t->set_block("mail_body", $supplier_body);
					foreach ($suppliers as $supplier_id => $supplier) {

						$t->set_vars($supplier);

						if ($supplier_to) {
							$supplier_mail = $supplier_to; 
						} else {
							$supplier_mail = $supplier["supplier_email"]; 
						}

						// set basket
						$t->set_var("basket", "");
						// set supplier items
						set_items_tag($supplier["items"], $supplier_mail_type, $supplier_body, "supplier_items");

						$t->parse("mail_subject", false);
						$t->parse("mail_body", false);
						$mail_body = str_replace("\r", "", $t->get_var("mail_body"));
						$notify_sent = va_mail($supplier_mail, $t->get_var("mail_subject"), $mail_body, $supplier_headers);
						if ($notify_sent) {
							$r->set_value("event_date", va_time());
							$r->set_value("event_type", "status_supplier_email_sent"); //"supplier email notification sent"
							$r->set_value("event_name", $t->get_var("mail_subject"));
							$r->set_value("event_description", $mail_body);
							$r->insert_record();
						}
					}
				}

				if ($supplier_sms_notify) {
					foreach ($suppliers as $supplier_id => $supplier) {
						$t->set_vars($supplier);
						if ($supplier_sms_recipient) { 
							$sms_recipient = $supplier_sms_recipient; 
						} else {
							$sms_recipient = $supplier["supplier_cell_phone"]; 
						}
						$t->set_block("sms_recipient", $sms_recipient);
						$t->set_block("sms_originator", $supplier_sms_originator);
						$t->set_block("sms_message", $supplier_sms_message);

						// set basket
						$t->set_var("basket", "");
						// set supplier items
						set_items_tag($supplier["items"], 0, $supplier_sms_message, "supplier_items");

						$t->parse("sms_recipient", false);
						$t->parse("sms_originator", false);
						$t->parse("sms_message", false);

						if (sms_send_allowed($t->get_var("sms_recipient"))) {
							$supplier_sms_sent = sms_send($t->get_var("sms_recipient"), $t->get_var("sms_message"), $t->get_var("sms_originator"), $sms_errors);
						} else {
							$supplier_sms_sent = false;
						}
						if ($supplier_sms_sent) {
							$event_description = $t->get_var("sms_message");

							$r->set_value("event_date", va_time());
							$r->set_value("event_type", "status_supplier_sms_sent"); 
							$r->set_value("event_name", $t->get_var("sms_recipient"));
							$r->set_value("event_description", $event_description);
							$r->insert_record();
						}
					}
				}
				// end supplier notifications

				// start affiliate notifications
				if ($affiliate_notify) {
					// set email templates
					$t->set_block("mail_subject", $affiliate_subject);
					$t->set_block("mail_body", $affiliate_body);
					foreach ($affiliates as $affiliate_id => $affiliate) {

						$t->set_vars($affiliate);

						if ($affiliate_to) {
							$affiliate_mail = $affiliate_to; 
						} else {
							$affiliate_mail = $affiliate["affiliate_email"]; 
						}

						// set basket
						$t->set_var("basket", "");
						// set affiliate items
						set_items_tag($affiliate["items"], $affiliate_mail_type, $affiliate_body, "affiliate_items");

						$t->parse("mail_subject", false);
						$t->parse("mail_body", false);
						$mail_body = str_replace("\r", "", $t->get_var("mail_body"));
						$notify_sent = va_mail($affiliate_mail, $t->get_var("mail_subject"), $mail_body, $affiliate_headers);
						if ($notify_sent) {
							$r->set_value("event_date", va_time());
							$r->set_value("event_type", "status_affiliate_email_sent"); //"affiliate email notification sent"
							$r->set_value("event_name", $t->get_var("mail_subject"));
							$r->set_value("event_description", $mail_body);
							$r->insert_record();
						}
					}
				}

				if ($affiliate_sms_notify) {
					foreach ($affiliates as $affiliate_id => $affiliate) {
						$t->set_vars($affiliate);
						if ($affiliate_sms_recipient) { 
							$sms_recipient = $affiliate_sms_recipient; 
						} else {
							$sms_recipient = $affiliate["affiliate_cell_phone"]; 
						}
						$t->set_block("sms_recipient", $sms_recipient);
						$t->set_block("sms_originator", $affiliate_sms_originator);
						$t->set_block("sms_message", $affiliate_sms_message);

						// set basket
						$t->set_var("basket", "");
						// set affiliate items
						set_items_tag($affiliate["items"], 0, $affiliate_sms_message, "affiliate_items");

						$t->parse("sms_recipient", false);
						$t->parse("sms_originator", false);
						$t->parse("sms_message", false);

						if (sms_send_allowed($t->get_var("sms_recipient"))) {
							$affiliate_sms_sent = sms_send($t->get_var("sms_recipient"), $t->get_var("sms_message"), $t->get_var("sms_originator"), $sms_errors);
						} else {
							$affiliate_sms_sent = false;
						}
						if ($affiliate_sms_sent) {
							$event_description = $t->get_var("sms_message");

							$r->set_value("event_date", va_time());
							$r->set_value("event_type", "status_affiliate_sms_sent"); 
							$r->set_value("event_name", $t->get_var("sms_recipient"));
							$r->set_value("event_description", $event_description);
							$r->insert_record();
						}
					}
				}
				// end affiliate notifications


				// admin notification
				if ($admin_notify) {
					$attachments = array();
					if ($admin_pdf_invoice) {
						$attachments[] = array("Invoice_".$order_id.".pdf", $pdf_invoice, "buffer");
					}
					if (count($admin_attachments)) {
						foreach ($admin_attachments as $admin_attachemnt)  { $attachments[] = $admin_attachemnt; }
					}

					// prepare email addresses to send
					$mails_to = array();	
					if (strlen($admin_to)) { 
						$mails_to[] = array(
							"email" => $admin_to, "name" => "",
						);
					}
					if (strlen($admin_to_groups_ids)) {
						$sql  = " SELECT a.admin_name, a.email, ap.user_types_all, ap.non_logged_users, ap.user_types_ids ";
						$sql .= " FROM (".$table_prefix."admins a ";
						$sql .= " INNER JOIN ".$table_prefix."admin_privileges ap ON a.privilege_id=ap.privilege_id) ";
						$sql .= " WHERE a.privilege_id IN (" . $db->tosql($admin_to_groups_ids, INTEGERS_LIST) . ") ";
						$db->query($sql);
						while ($db->next_record()) {
							$admin_name = $db->f("admin_name");
							$email = $db->f("email");
							$user_types_all = $db->f("user_types_all");
							$non_logged_users = $db->f("non_logged_users");
							$user_types_ids = explode(",", $db->f("user_types_ids"));
							if ($user_types_all || (!$order_user_type_id && $non_logged_users) 
								|| ($order_user_type_id && in_array($order_user_type_id, $user_types_ids))
							) {
								$mails_to[] = array(
									"email" => $email, "name" => $admin_name,
								);
							}
						}
					}
					if (!is_array($mails_to) && !sizeof($mails_to)) {
						$mails_to[] = array(
							"email" => $settings["admin_email"], "name" => "",
						);
					} // TODO for testing purposes only
					for ($m = 0; $m < sizeof($mails_to); $m++) {
						$mail_info = $mails_to[$m];
						$mail_to = $mail_info["email"];
						$admin_name = $mail_info["name"];
						$t->set_var("mail_to", $mail_to);
						$t->set_var("admin_name", $admin_name);
		
						$t->set_block("mail_subject", $admin_subject);
						$t->set_block("mail_body", $admin_body);
						$admin_type_code = ($admin_mail_type == 1) ? "html" : "text";
				  
						// set basket
						$t->set_var("basket", $t->get_var("basket_" . $admin_type_code));
						// set full order items tag
						set_items_tag($order_items, $admin_mail_type, $admin_body, "order_items");
						// set download links
						$t->set_var("links", $links[$admin_type_code]);
						// set serial numbers
						$t->set_var("serials", $order_serials[$admin_type_code]);
						$t->set_var("serial_numbers", $order_serials[$admin_type_code]);
						// set serial numbers
						$t->set_var("vouchers", $order_vouchers[$admin_type_code]);
						$t->set_var("gift_vouchers", $order_vouchers[$admin_type_code]);
				  
						$t->parse("mail_subject", false);
						$t->parse("mail_body", false);
						$mail_body = str_replace("\r", "", $t->get_var("mail_body"));
						$notify_sent = va_mail($mail_to, $t->get_var("mail_subject"), $mail_body, $admin_headers, $attachments);
						if ($notify_sent) {
							$r->set_value("event_date", va_time());
							$r->set_value("event_type", "status_admin_email_sent"); 
							$r->set_value("event_name", $t->get_var("mail_subject"));
							$r->set_value("event_description", $mail_body);
							$r->insert_record();
						}
					}
				}

				if ($admin_sms_notify) {
					if (!$admin_sms_recipient) { $admin_sms_recipient = $order_data["cell_phone"]; }
					$t->set_block("sms_recipient", $admin_sms_recipient);
					$t->set_block("sms_originator", $admin_sms_originator);
					$t->set_block("sms_message", $admin_sms_message);

					// set basket
					$t->set_var("basket", $basket);
					// set download links
					$t->set_var("links",    $links["text"]);
					// set serial numbers
					$t->set_var("serials", $order_serials["text"]);
					$t->set_var("serial_numbers", $order_serials["text"]);
					// set serial numbers
					$t->set_var("vouchers", $order_vouchers["text"]);
					$t->set_var("gift_vouchers", $order_vouchers["text"]);

					$t->parse("sms_recipient", false);
					$t->parse("sms_originator", false);
					$t->parse("sms_message", false);

					if (sms_send_allowed($t->get_var("sms_recipient"))) {
						$admin_sms_sent = sms_send($t->get_var("sms_recipient"), $t->get_var("sms_message"), $t->get_var("sms_originator"), $sms_errors);
					} else {
						$admin_sms_sent = false;
					}
					if ($admin_sms_sent) {
						$event_description = $t->get_var("sms_message");

						$r->set_value("event_date", va_time());
						$r->set_value("event_type", "status_admin_sms_sent"); 
						$r->set_value("event_name", $t->get_var("sms_recipient"));
						$r->set_value("event_description", $event_description);
						$r->insert_record();
					}
				}
				// end admin notifications

				if ($links_notify || $serials_notify || $vouchers_notify) {
					// prepare download info settings
					$download_info = array();
					$sql = "SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings WHERE setting_type='download_info'";
					$db->query($sql);
					while ($db->next_record()) {
						$download_info[$db->f("setting_name")] = $db->f("setting_value");
					}
				}

				if ($links_notify) {
					$email_headers = array();
					$email_headers["from"] = get_setting_value($download_info, "links_from", $settings["admin_email"]);
					$email_headers["cc"] = get_setting_value($download_info, "links_cc");
					$email_headers["bcc"] = get_setting_value($download_info, "links_bcc");
					$email_headers["reply_to"] = get_setting_value($download_info, "links_reply_to");
					$email_headers["return_path"] = get_setting_value($download_info, "links_return_path");
					$mail_type = get_setting_value($download_info, "links_message_type", 0);
					$email_headers["mail_type"] = $mail_type;
					$mail_type_code = ($mail_type == 1) ? "html" : "text";

					// set basket
					$t->set_var("basket", $t->get_var("basket_" . $mail_type_code));
					// set download links
					$t->set_var("links", $links[$mail_type_code]);
					// set serial numbers
					$t->set_var("serials", $order_serials[$mail_type_code]);
					$t->set_var("serial_numbers", $order_serials[$mail_type_code]);
					// set serial numbers
					$t->set_var("vouchers", $order_vouchers[$mail_type_code]);
					$t->set_var("gift_vouchers", $order_vouchers[$mail_type_code]);

					$mail_subject = get_translation(get_setting_value($download_info, "links_subject", va_constant("LINKS_FOR_ORDER_MSG") . $order_id));
					$mail_body = get_translation(get_setting_value($download_info, "links_message", $links[$mail_type_code]));

					$t->set_block("mail_subject", $mail_subject);
					$t->set_block("mail_body", $mail_body);
					$t->parse("mail_subject", false);
					$t->parse("mail_body", false);
					$mail_body = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("mail_body"));
					$notify_sent = va_mail($user_mail, $t->get_var("mail_subject"), $mail_body, $email_headers);
					if ($notify_sent) {
						$r->set_value("event_date", va_time());
						$r->set_value("event_type", "links_sent");
						$r->set_value("event_name", $t->get_var("mail_subject"));
						$r->set_value("event_description", $mail_body);
						$r->insert_record();
					}
				}

				if ($serials_notify) {
					$email_headers = array();
					$email_headers["from"] = get_setting_value($download_info, "serials_from", $settings["admin_email"]);
					$email_headers["cc"] = get_setting_value($download_info, "serials_cc");
					$email_headers["bcc"] = get_setting_value($download_info, "serials_bcc");
					$email_headers["reply_to"] = get_setting_value($download_info, "serials_reply_to");
					$email_headers["return_path"] = get_setting_value($download_info, "serials_return_path");
					$mail_type = get_setting_value($download_info, "serials_message_type", 0);
					$email_headers["mail_type"] = $mail_type;
					$mail_type_code = ($mail_type == 1) ? "html" : "text";

					// set basket
					$t->set_var("basket", $t->get_var("basket_" . $mail_type_code));
					// set download links
					$t->set_var("links", $links[$mail_type_code]);
					// set serial numbers
					$t->set_var("serials", $order_serials[$mail_type_code]);
					$t->set_var("serial_numbers", $order_serials[$mail_type_code]);
					// set serial numbers
					$t->set_var("vouchers", $order_vouchers[$mail_type_code]);
					$t->set_var("gift_vouchers", $order_vouchers[$mail_type_code]);

					$mail_subject = get_translation(get_setting_value($download_info, "serials_subject", va_constant("SERIAL_NUMBERS_FOR_ORDER_MSG") . $order_id));
					$mail_body = get_translation(get_setting_value($download_info, "serials_message", $order_serials[$mail_type_code]));

					$t->set_block("mail_subject", $mail_subject);
					$t->set_block("mail_body", $mail_body);
					$t->parse("mail_subject", false);
					$t->parse("mail_body", false);
					$mail_body = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("mail_body"));
					$notify_sent = va_mail($user_mail, $t->get_var("mail_subject"), $mail_body, $email_headers);
					if ($notify_sent) {
						$r->set_value("event_date", va_time());
						$r->set_value("event_type", "serials_sent");
						$r->set_value("event_name", $t->get_var("mail_subject"));
						$r->set_value("event_description", $mail_body);
						$r->insert_record();
					}
				}

				if ($vouchers_notify) {
					$email_headers = array();
					$email_headers["from"] = get_setting_value($download_info, "vouchers_from", $settings["admin_email"]);
					$email_headers["cc"] = get_setting_value($download_info, "vouchers_cc");
					$email_headers["bcc"] = get_setting_value($download_info, "vouchers_bcc");
					$email_headers["reply_to"] = get_setting_value($download_info, "vouchers_reply_to");
					$email_headers["return_path"] = get_setting_value($download_info, "vouchers_return_path");
					$mail_type = get_setting_value($download_info, "vouchers_message_type", 0);
					$email_headers["mail_type"] = $mail_type;
					$mail_type_code = ($mail_type == 1) ? "html" : "text";

					// set basket
					$t->set_var("basket", $t->get_var("basket_" . $mail_type_code));
					// set download links
					$t->set_var("links", $links[$mail_type_code]);
					// set serial numbers
					$t->set_var("serials", $order_serials[$mail_type_code]);
					$t->set_var("serial_numbers", $order_serials[$mail_type_code]);
					// set serial numbers
					$t->set_var("vouchers", $order_vouchers[$mail_type_code]);
					$t->set_var("gift_vouchers", $order_vouchers[$mail_type_code]);

					$mail_subject = get_translation(get_setting_value($download_info, "vouchers_subject", va_constant("GIFT_VOUCHERS_FOR_ORDERS_MSG") . $order_id));
					$mail_body = get_translation(get_setting_value($download_info, "vouchers_message", $order_vouchers[$mail_type_code]));

					$t->set_block("mail_subject", $mail_subject);
					$t->set_block("mail_body", $mail_body);
					$t->parse("mail_subject", false);
					$t->parse("mail_body", false);
					$mail_body = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("mail_body"));
					$notify_sent = va_mail($user_mail, $t->get_var("mail_subject"), $mail_body, $email_headers);
					if ($notify_sent) {
						$r->set_value("event_date", va_time());
						$r->set_value("event_type", "vouchers_sent");
						$r->set_value("event_name", $t->get_var("mail_subject"));
						$r->set_value("event_description", $mail_body);
						$r->insert_record();
					}
				}
			}

			if ($other_statuses && sizeof($items_statuses) > 0) {
				$is_update = true;

				foreach ($items_statuses as $new_item_status => $items) {
					$items_ids = ""; $items_names = ""; $old_status_name = "";
					for ($i = 0; $i < sizeof($items); $i++) {
						list($order_item_id, $item_name, $item_status_name) = $items[$i];
						if (strlen($items_ids)) {
							$items_ids .= ",";
						}
						$items_ids .= $order_item_id;
						$items_names .= "<br>" . $item_name;
						$old_status_name = $item_status_name;
					}

					// update items statuses
					$sql  = " UPDATE " . $table_prefix . "orders_items ";
					$sql .= " SET item_status=" . $db->tosql($new_item_status, INTEGER);
					$sql .= " WHERE order_item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ") ";
					$db->query($sql);

					$sql  = " SELECT status_name, download_activation FROM " . $table_prefix . "order_statuses ";
					$sql .= " WHERE status_id=" . $db->tosql($new_item_status, INTEGER);
					$db->query($sql);
					if ($db->next_record()) {
						$new_status_name = get_translation($db->f("status_name"));
						$item_activation = $db->f("download_activation");
					}

					if ($item_activation == 1) {
						$sql = "UPDATE " . $table_prefix . "items_downloads SET activated=1 WHERE order_item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ") ";
						$db->query($sql);
						$sql = "UPDATE " . $table_prefix . "orders_items_serials SET activated=1 WHERE order_item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ") ";
						$db->query($sql);
						$sql = "UPDATE " . $table_prefix . "coupons SET is_active=1 WHERE order_item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ") ";
						$db->query($sql);
					} elseif ($item_activation == 0) {
						$sql = "UPDATE " . $table_prefix . "items_downloads SET activated=0 WHERE order_item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ") ";
						$db->query($sql);
						$sql = "UPDATE " . $table_prefix . "orders_items_serials SET activated=0 WHERE order_item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ") ";
						$db->query($sql);
						$sql = "UPDATE " . $table_prefix . "coupons SET is_active=0 WHERE order_item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ") ";
						$db->query($sql);
					}

					$r->set_value("status_id", $new_item_status);
					$r->set_value("order_items", $items_ids);
					$r->set_value("event_type", "update_items_status");
					$r->set_value("event_name", $old_status_name . " &ndash;&gt; " . $new_status_name);
					$r->set_value("event_description", $items_names);
					$r->insert_record();
				}

			}

			// update credit amount
			if ($current_status_id != $status_id && $credit_amount > 0) {
				$cdt = new VA_Record($table_prefix . "users_credits");
				$cdt->add_textbox("user_id", INTEGER);
				$cdt->add_textbox("order_id", INTEGER);
				$cdt->add_textbox("order_item_id", INTEGER);
				$cdt->add_textbox("credit_amount", NUMBER);
				$cdt->add_textbox("credit_action", INTEGER);
				$cdt->add_textbox("credit_type", INTEGER);
				$cdt->add_textbox("date_added", DATETIME);

				// subtract or return credit amount from credit balance
				$cdt->set_value("user_id", $order_user_id);
				$cdt->set_value("order_id", $order_id);
				$cdt->set_value("order_item_id", 0);
				$cdt->set_value("credit_amount", $credit_amount);
				$cdt->set_value("credit_type", 1);
				$cdt->set_value("date_added", va_time());

				$credit_user = false;
				$sql  = " SELECT SUM(credit_action) FROM " . $table_prefix . "users_credits ";
				$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
				$sum_credit_action = get_db_value($sql);
				if ($credit_action == 1 && $sum_credit_action == -1) { // return points to account
					$credit_user = true;
					$cdt->set_value("credit_action", 1);
					$cdt->insert_record();
				} elseif ($credit_action == -1 && $sum_credit_action != -1) { // subtract points from account
					$credit_user = true;
					$cdt->set_value("credit_action", -1);
					$cdt->insert_record();
				}

				// update credit balance field in users table
				if ($credit_user) {
					$sql  = " SELECT SUM(credit_action * credit_amount) ";
					$sql .= " FROM " . $table_prefix . "users_credits ";
					$sql .= " WHERE user_id=" . $db->tosql($order_user_id, INTEGER);
					$total_credit_sum = get_db_value($sql);

					$sql  = " UPDATE " . $table_prefix . "users ";
					$sql .= " SET credit_balance=" . $db->tosql($total_credit_sum, NUMBER);
					$sql .= " WHERE user_id=" . $db->tosql($order_user_id, INTEGER);
					$db->query($sql);

					// update user information in session if available
					$user_info = get_session("session_user_info");
					$session_user_id = get_setting_value($user_info, "user_id", 0);
					$session_credit_balance = get_setting_value($user_info, "credit_balance", 0);
					if ($session_user_id == $order_user_id && $total_credit_sum != $session_credit_balance) {
						$user_info["credit_balance"] = $total_credit_sum;
						set_session("session_user_info", $user_info);
					}
				}
			}

			// check product notification, commissions and subscriptions
			if ($order_items_ids || (!$other_statuses && $current_status_id != $status_id)) {
				$events = array();
				$commissions_points = array();
				$parent_items = array();
				$subscriptions = array();
				$items_stock_levels = array();
				$credit_notes_items = array();

				$uc = new VA_Record($table_prefix . "users_commissions");
				$uc->add_textbox("payment_id", INTEGER);
				$uc->add_textbox("user_id", INTEGER);
				$uc->add_textbox("order_id", INTEGER);
				$uc->add_textbox("order_item_id", INTEGER);
				$uc->add_textbox("commission_amount", NUMBER);
				$uc->add_textbox("commission_action", INTEGER);
				$uc->add_textbox("commission_type", INTEGER);
				$uc->add_textbox("date_added", DATETIME);

				$uc->set_value("payment_id", 0);
				$uc->set_value("order_id", $order_id);
				$uc->set_value("date_added", va_time());

				$pts = new VA_Record($table_prefix . "users_points");
				$pts->add_textbox("user_id", INTEGER);
				$pts->add_textbox("order_id", INTEGER);
				$pts->add_textbox("order_item_id", INTEGER);
				$pts->add_textbox("points_amount", NUMBER);
				$pts->add_textbox("points_action", INTEGER);
				$pts->add_textbox("points_type", INTEGER);
				$pts->add_textbox("date_added", DATETIME);

				$pts->set_value("order_id", $order_id);
				$pts->set_value("date_added", va_time());

				$cdt = new VA_Record($table_prefix . "users_credits");
				$cdt->add_textbox("user_id", INTEGER);
				$cdt->add_textbox("order_id", INTEGER);
				$cdt->add_textbox("order_item_id", INTEGER);
				$cdt->add_textbox("credit_amount", NUMBER);
				$cdt->add_textbox("credit_action", INTEGER);
				$cdt->add_textbox("credit_type", INTEGER);
				$cdt->add_textbox("date_added", DATETIME);

				$cdt->set_value("order_id", $order_id);
				$cdt->set_value("date_added", va_time());

				$sql  = " SELECT oi.order_item_id, oi.parent_order_item_id, oi.top_order_item_id, oi.cart_item_id, ";
				$sql .= " os.status_id, os.status_name, os.status_type, os.paid_status, os.item_notify, os.credit_note_action,os.stock_level_action, ";
				$sql .= " os.commission_action, os.points_action, os.credit_action, ";
				$sql .= " oi.item_id, oi.parent_item_id, oi.item_type_id, oi.item_name, oi.item_code, oi.manufacturer_code, ";
				$sql .= " oi.buying_price, oi.price, oi.quantity, oi.component_name, oi.component_order, oi.item_properties, ";
				$sql .= " oi.tax_id, oi.tax_free, oi.tax_percent, oi.is_shipping_free, oi.shipping_cost, ";
				$sql .= " i.stock_level, i.use_stock_level, i.short_description, i.full_description, ";
				$sql .= " oi.item_user_id, oi.merchant_commission, oi.affiliate_commission, oi.reward_points, oi.reward_credits, oi.points_price, ";
				$sql .= " i.mail_notify, i.mail_to, i.mail_from, i.mail_subject, i.mail_cc, i.mail_bcc, i.mail_reply_to, i.mail_return_path, ";
				$sql .= " i.mail_type, i.mail_subject, i.mail_body, ";
				$sql .= " i.sms_notify, i.sms_recipient, i.sms_originator, i.sms_message, ";
				$sql .= " oi.user_id, oi.subscription_id, oi.is_subscription, oi.is_account_subscription, ";
				$sql .= " oi.subscription_period, oi.subscription_interval, oi.subscription_suspend, ";
				$sql .= " oi.subscription_start_date, oi.subscription_expiry_date ";
				$sql .= " FROM ((" . $table_prefix . "orders_items oi ";
				$sql .= " LEFT JOIN " . $table_prefix . "items i ON i.item_id=oi.item_id) ";
				$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON os.status_id=oi.item_status) ";
				if ($other_statuses && $order_items_ids) {
					$sql .= " WHERE oi.order_item_id IN (" . $db->tosql($order_items_ids, INTEGERS_LIST) . ")" ;
				} else {
					$sql .= " WHERE oi.order_id=" . $db->tosql($order_id, INTEGER);
				}
				$db->query($sql);
				while ($db->next_record()) {

					$order_item_id = $db->f("order_item_id");
					$parent_order_item_id = $db->f("parent_order_item_id");
					$cart_item_id = $db->f("cart_item_id");
					$new_status_id = $db->f("status_id");
					$new_status_type = $db->f("status_type");
					$new_item_paid = $db->f("paid_status");
					$new_credit_note_action = $db->f("credit_note_action");
					if ($new_status_type == "CREDIT_NOTE") {
						$new_credit_note_action = "";
					}
					$stock_level = $db->f("stock_level");
					$use_stock_level = $db->f("use_stock_level");
					$new_stock_action = $db->f("stock_level_action");
					if (!$new_stock_action) {
						$new_stock_action = -1;
					}
					$item_user_id = $db->f("item_user_id");
					$item_id = $db->f("item_id");
					$parent_item_id = $db->f("parent_item_id");
					$item_type_id = $db->f("item_type_id");
					$item_name_original = $db->f("item_name");
					$item_name = get_translation($item_name_original);
					$item_code = $db->f("item_code");
					$item_properties = $db->f("item_properties");
					$manufacturer_code = $db->f("manufacturer_code");
					$component_name = $db->f("component_name");
					$component_order = $db->f("component_order");
					$price = $db->f("price");
					$merchant_commission = $db->f("merchant_commission");
					$affiliate_commission = $db->f("affiliate_commission");
					$reward_points = $db->f("reward_points");
					$reward_credits = $db->f("reward_credits");
					$points_price = $db->f("points_price");
					$quantity = $db->f("quantity");
					$short_description = $db->f("short_description");
					$full_description = $db->f("full_description");
					$item_notify = $db->f("item_notify");
					// tax fields
					$tax_id = $db->f("tax_id");
					$tax_free = $db->f("tax_free");
					$tax_percent = $db->f("tax_percent");
					// shipping data
					$is_shipping_free = $db->f("is_shipping_free");
					$shipping_cost = $db->f("shipping_cost");

					$item_commission_action = $db->f("commission_action");
					$item_points_action = $db->f("points_action");
					$item_credit_action = $db->f("credit_action");

					$user_id = $db->f("user_id");
					$is_subscription = $db->f("is_subscription");
					$is_account_subscription = $db->f("is_account_subscription");
					$subscription_id = $db->f("subscription_id");
					$subscription_period = $db->f("subscription_period");
					$subscription_interval = $db->f("subscription_interval");
					$subscription_suspend = $db->f("subscription_suspend");
					$subscription_start_date = $db->f("subscription_start_date", DATETIME);
					$subscription_expiry_date = $db->f("subscription_expiry_date", DATETIME);

					if ($is_subscription) {
						$old_item_paid = isset($items_paid[$order_item_id]) ? $items_paid[$order_item_id] : $current_paid_status;
						if (($old_item_paid || $new_item_paid) && $old_item_paid != $new_item_paid) {
							$subscriptions[$order_item_id] = array(
								"is_account_subscription" => $is_account_subscription, "user_id" => $user_id, "paid" => $new_item_paid, "period" => $subscription_period, 
								"interval" => $subscription_interval, "suspend" => $subscription_suspend,
								"start_date" => $subscription_start_date, "expiry_date" => $subscription_expiry_date,
							);
						}
					}

					if ($parent_order_item_id) {
						$old_item_paid = isset($items_paid[$order_item_id]) ? $items_paid[$order_item_id] : $current_paid_status;
						if (($old_item_paid || $new_item_paid) && $old_item_paid != $new_item_paid) {
							$parent_items[] = array($parent_order_item_id, $new_item_paid);
						}
					}

					// check if credit note was changed
					if (isset($credit_notes_actions[$order_item_id])) {
						$old_credit_note_action = $credit_notes_actions[$order_item_id];
					} else {
						$old_credit_note_action = $current_credit_note_action;
					}
					// generate a new credit note only if it wasn't generated before and credit note was activated
					if ($new_credit_note_action != $old_credit_note_action && $new_credit_note_action == 1) {
						$credit_notes_items[] = $order_item_id;
					}

					// check if stock action was changed
					if (isset($items_stock_actions[$order_item_id])) {
						$old_stock_action = $items_stock_actions[$order_item_id];
					} else {
						$old_stock_action = $current_stock_action;
					}
					if ($new_stock_action != $old_stock_action) {
						$items_stock_levels[$order_item_id] = array(
							"item_id" => $item_id, "quantity" => $quantity, "stock_action" => $new_stock_action, 
							"stock_level" => $stock_level, "use_stock_level" => $use_stock_level, "cart_item_id" => $cart_item_id,
						);
					}

					if ($item_notify == 1) {
						$email_headers = array();
						$mail_notify = $db->f("mail_notify");
						$mail_to = $db->f("mail_to");
						if (!strlen($mail_to)) { $mail_to = $user_mail; }
						$mail_from = $db->f("mail_from");
						if (!strlen($mail_from)) { $mail_from = $settings["admin_email"]; }
						$email_headers["from"] = $mail_from;
						$email_headers["cc"] = $db->f("mail_cc");
						$email_headers["bcc"] = $db->f("mail_bcc");
						$email_headers["reply_to"] = $db->f("mail_reply_to");
						$email_headers["return_path"] = $db->f("mail_return_path");
						$mail_type = $db->f("mail_type");
						$email_headers["mail_type"] = $mail_type;
						$mail_subject = $db->f("mail_subject");
						$mail_body = $db->f("mail_body");

						// sms settings
						$sms_notify = $db->f("sms_notify");
						$sms_recipient = $db->f("sms_recipient");
						$sms_originator = $db->f("sms_originator");
						$sms_message = $db->f("sms_message");

						$t->set_var("item_name", $item_name);
						$t->set_var("item_title", $item_name);
						$t->set_var("product_title", $item_name);
						$t->set_var("product_name", $item_name);
						$t->set_var("product_code", $manufacturer_code);
						$t->set_var("price", $price);
						$t->set_var("quantity", $quantity);
						$t->set_var("product_quantity", $quantity);
						$t->set_var("short_description", $short_description);
						$t->set_var("full_description", $full_description);

						if ($mail_notify) {
							$t->set_block("mail_subject", $mail_subject);
							$t->set_block("mail_body", $mail_body);

							// set basket
							if ($mail_type) {
								$t->set_var("basket", $t->get_var("basket_html"));
							} else {
								$t->set_var("basket", $t->get_var("basket_text"));
							}

							// set download links
							if (!isset($links["html_" . $order_item_id])) {
								$t->set_var("links", "");
							} elseif ($mail_type) {
								$t->set_var("links", $links["html_" . $order_item_id]);
							} else {
								$t->set_var("links", $links["text_" . $order_item_id]);
							}
							// set serial numbers
							if (!isset($order_serials["html_" . $order_item_id])) {
								$t->set_var("serials", "");
								$t->set_var("serial_numbers", "");
							} elseif ($mail_type) {
								$t->set_var("serials", $order_serials["html_" . $order_item_id]);
								$t->set_var("serial_numbers", $order_serials["html_" . $order_item_id]);
							} else {
								$t->set_var("serials", $order_serials["text_" . $order_item_id]);
								$t->set_var("serial_numbers", $order_serials["text_" . $order_item_id]);
							}
							// set serial numbers
							if (!isset($order_vouchers["html_" . $order_item_id])) {
								$t->set_var("vouchers", "");
								$t->set_var("gift_vouchers", "");
							} elseif ($mail_type) {
								$t->set_var("vouchers", $order_vouchers["html_" . $order_item_id]);
								$t->set_var("gift_vouchers", $order_vouchers["html_" . $order_item_id]);
							} else {
								$t->set_var("vouchers", $order_vouchers["text_" . $order_item_id]);
								$t->set_var("gift_vouchers", $order_vouchers["text_" . $order_item_id]);
							}

							$t->parse("mail_subject", false);
							$t->parse("mail_body", false);

							$mail_subject = $t->get_var("mail_subject");
							$mail_body = str_replace("\r", "", $t->get_var("mail_body"));
							$notify_sent = va_mail($mail_to, $mail_subject, $mail_body, $email_headers);
							if ($notify_sent) {
								$event_name = $mail_subject;
								$event_description = $mail_body;
								$events[] = array($new_status_id, $order_item_id, va_time(), "product_notification_sent", $event_name, $event_description);
							}
						}
						if ($sms_notify) {
							if (!$sms_recipient) { $sms_recipient = $order_data["cell_phone"]; }
							$t->set_block("sms_recipient", $sms_recipient);
							$t->set_block("sms_originator", $sms_originator);
							$t->set_block("sms_message", $sms_message);

							// set basket
							$t->set_var("basket", $basket);

							// set download links
							if (!isset($links["html_" . $order_item_id])) {
								$t->set_var("links", "");
							} else {
								$t->set_var("links", $links["text_" . $order_item_id]);
							}
							// set serial numbers
							if (!isset($order_serials["html_" . $order_item_id])) {
								$t->set_var("serials", "");
								$t->set_var("serial_numbers", "");
							} else {
								$t->set_var("serials", $order_serials["text_" . $order_item_id]);
								$t->set_var("serial_numbers", $order_serials["text_" . $order_item_id]);
							}
							// set serial numbers
							if (!isset($order_vouchers["html_" . $order_item_id])) {
								$t->set_var("vouchers", "");
								$t->set_var("gift_vouchers", "");
							} else {
								$t->set_var("vouchers", $order_vouchers["text_" . $order_item_id]);
								$t->set_var("gift_vouchers", $order_vouchers["text_" . $order_item_id]);
							}

							$t->parse("sms_recipient", false);
							$t->parse("sms_originator", false);
							$t->parse("sms_message", false);

							$sms_message = $t->get_var("sms_message");

							if (sms_send_allowed($t->get_var("sms_recipient"))) {
								$sms_sent = sms_send($t->get_var("sms_recipient"), $sms_message, $t->get_var("sms_originator"), $sms_errors);
							} else {
								$sms_sent = false;
							}
							if ($sms_sent) {
								$event_name = $t->get_var("sms_recipient");
								$event_description = $sms_message;
								$events[] = array($new_status_id, $order_item_id, va_time(), "product_sms_sent", $event_name, $event_description);
							}
						}
					}

					// save commisions, reward points and credits information
					if ($affiliate_commission > 0 || $merchant_commission > 0 || $reward_points > 0 || $reward_credits > 0 || $points_price > 0) {
						$commissions_points[$order_item_id] = array(
							"order_item_id" => $order_item_id,
							"order_user_id" => $order_user_id,
							"affiliate_user_id" => $affiliate_user_id,
							"item_user_id" => $item_user_id,
							"quantity" => $quantity,
							"affiliate_commission" => $affiliate_commission,
							"merchant_commission" => $merchant_commission,
							"reward_points" => $reward_points,
							"reward_credits" => $reward_credits,
							"points_price" => $points_price,
							"commission_action" => $item_commission_action,
							"points_action" => $item_points_action,
							"credit_action" => $item_credit_action,
						);
					}
				}
				// add shipping and properties points for order
				if ($points_action && ($shipping_points_amount + $properties_points_amount) > 0) {
					$commissions_points["order"] = array(
						"order_item_id" => 0,
						"order_user_id" => $order_user_id,
						"affiliate_user_id" => $affiliate_user_id,
						"item_user_id" => 0,
						"quantity" => 1,
						"affiliate_commission" => 0,
						"merchant_commission" => 0,
						"reward_points" => 0,
						"reward_credits" => 0,
						"points_price" => ($shipping_points_amount + $properties_points_amount),
						"commission_action" => $commission_action,
						"points_action" => $points_action,
						"credit_action" => $credit_action,
					);
				}

				// update payment plan date for recurring items
				$current_date = va_time();
				$current_ts = mktime (0, 0, 0, $current_date[MONTH], $current_date[DAY], $current_date[YEAR]);
				for ($i = 0; $i < sizeof($parent_items); $i++) {
					list($parent_order_item_id, $new_item_paid) = $parent_items[$i];
					$sql  = " SELECT oi.is_recurring, oi.recurring_period, oi.recurring_interval, ";
					$sql .= " oi.recurring_payments_total, oi.recurring_payments_made, oi.recurring_payments_failed, ";
					$sql .= " oi.recurring_end_date, oi.recurring_last_payment, oi.recurring_next_payment, oi.recurring_plan_payment ";
					$sql .= " FROM " . $table_prefix . "orders_items oi ";
					$sql .= " WHERE oi.order_item_id=" . $db->tosql($parent_order_item_id, INTEGER);
					$db->query($sql);
					if ($db->next_record()) {
						$is_recurring = $db->f("is_recurring");
						$recurring_period = $db->f("recurring_period");
						$recurring_interval = $db->f("recurring_interval");
						$recurring_payments_total = $db->f("recurring_payments_total");
						$recurring_payments_made = $db->f("recurring_payments_made");
						$recurring_payments_failed = $db->f("recurring_payments_failed");
						$recurring_end_date = $db->f("recurring_end_date", DATETIME);
						$recurring_last_payment = $db->f("recurring_last_payment", DATETIME);
						$recurring_next_payment = $db->f("recurring_next_payment", DATETIME);
						$recurring_plan_payment = $db->f("recurring_plan_payment", DATETIME);

						if ($is_recurring) {
							if ($new_item_paid) {
								$recurring_payments_made++;
								$recurring_payments_failed = 0;
								$recurring_last_ts = $current_ts;
							} else {
								$recurring_payments_made--;
								$recurring_interval = -$recurring_interval;
								$recurring_last_ts = 0;
							}

							$recurring_plan_ts = mktime (0, 0, 0, $recurring_plan_payment[MONTH], $recurring_plan_payment[DAY], $recurring_plan_payment[YEAR]);

							if ($recurring_period == 1) {
								$recurring_plan_ts = mktime (0, 0, 0, $recurring_plan_payment[MONTH], $recurring_plan_payment[DAY] + $recurring_interval, $recurring_plan_payment[YEAR]);
							} elseif ($recurring_period == 2) {
								$recurring_plan_ts = mktime (0, 0, 0, $recurring_plan_payment[MONTH], $current_date[DAY] + ($recurring_interval * 7), $recurring_plan_payment[YEAR]);
							} elseif ($recurring_period == 3) {
								$recurring_plan_ts = mktime (0, 0, 0, $recurring_plan_payment[MONTH] + $recurring_interval, $recurring_plan_payment[DAY], $recurring_plan_payment[YEAR]);
							} else {
								$recurring_plan_ts = mktime (0, 0, 0, $recurring_plan_payment[MONTH], $recurring_plan_payment[DAY], $recurring_plan_payment[YEAR] + $recurring_interval);
							}

							$recurring_end_ts = 0;
							if (is_array($recurring_end_date)) {
								$recurring_end_ts = mktime (0, 0, 0, $recurring_end_date[MONTH], $recurring_end_date[DAY], $recurring_end_date[YEAR]);
							}
							if (($recurring_payments_total && $recurring_payments_made >= $recurring_payments_total)
								|| ($recurring_end_ts && $recurring_end_ts < $recurring_plan_ts)) {
								$is_recurring = 0;
							}
							$sql  = " UPDATE " . $table_prefix . "orders_items SET ";
							$sql .= " recurring_payments_failed=" . $db->tosql($recurring_payments_failed, INTEGER) . ", ";
							$sql .= " recurring_payments_made=" . $db->tosql($recurring_payments_made, INTEGER) . ", ";
							if ($recurring_last_ts) {
								$sql .= " recurring_last_payment=" . $db->tosql($recurring_last_ts, DATETIME) . ", ";
							}
							$sql .= " recurring_plan_payment=" . $db->tosql($recurring_plan_ts, DATETIME) . ", ";
							$sql .= " is_recurring=" . $db->tosql($is_recurring, INTEGER);
							$sql .= " WHERE order_item_id=" . $db->tosql($parent_order_item_id, INTEGER);
							$db->query($sql);
						}
					}
				}

				foreach ($subscriptions as $order_item_id => $subscription) {
					$is_account_subscription = $subscription["is_account_subscription"];
					$user_id = $subscription["user_id"];
					$new_item_paid = $subscription["paid"];
					$subscription_period = $subscription["period"];
					$subscription_interval = $subscription["interval"];
					$subscription_suspend = $subscription["suspend"];
					$subscription_start_date = $subscription["start_date"];
					$subscription_expiry_date = $subscription["expiry_date"];

					if ($is_account_subscription) {
						$sql  = " SELECT expiry_date FROM " . $table_prefix . "users ";
						$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
						$db->query($sql);
						if ($db->next_record()) {
							$current_date = va_time();
							$current_date_ts = mktime (0,0,0, $current_date[MONTH], $current_date[DAY], $current_date[YEAR]);
							$expiry_date = $db->f("expiry_date", DATETIME);
							$expiry_date_ts = $current_date_ts;
							if (is_array($expiry_date)) {
								$expiry_date_ts = mktime (0,0,0, $expiry_date[MONTH], $expiry_date[DAY], $expiry_date[YEAR]);
							}
							if ($expiry_date_ts < $current_date_ts) {
								$expiry_date_ts = $current_date_ts;
							}
							$new_expiry_date = va_time($expiry_date_ts);
							if (!$new_item_paid) {
								$subscription_interval = -$subscription_interval;
							}
							if ($subscription_period == 1) {
								$new_expiry_date_ts = mktime (0, 0, 0, $new_expiry_date[MONTH], $new_expiry_date[DAY] + $subscription_interval, $new_expiry_date[YEAR]);
							} elseif ($subscription_period == 2) {
								$new_expiry_date_ts = mktime (0, 0, 0, $new_expiry_date[MONTH], $new_expiry_date[DAY] + ($subscription_interval * 7), $new_expiry_date[YEAR]);
							} elseif ($subscription_period == 3) {
								$new_expiry_date_ts = mktime (0, 0, 0, $new_expiry_date[MONTH] + $subscription_interval, $new_expiry_date[DAY], $new_expiry_date[YEAR]);
							} else {
								$new_expiry_date_ts = mktime (0, 0, 0, $new_expiry_date[MONTH], $new_expiry_date[DAY], $new_expiry_date[YEAR] + $subscription_interval);
							}
							if ($new_item_paid) {
								$subscription_start_date = $expiry_date_ts;
								$subscription_expiry_date = $new_expiry_date_ts;
							} else {
								$subscription_start_date = "";
								$subscription_expiry_date = "";
							}
				  
							$new_suspend_date_ts = $new_expiry_date_ts + (intval($subscription_suspend) * 86400);
							$sql  = " UPDATE " . $table_prefix . "users SET ";
							$sql .= " expiry_date=" . $db->tosql($new_expiry_date_ts, DATETIME);
							$sql .= ", suspend_date=" . $db->tosql($new_suspend_date_ts, DATETIME);
							$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
							$db->query($sql);
				  
							// update order item with subscriptions dates
							$sql  = " UPDATE " . $table_prefix . "orders_items SET ";
							$sql .= " subscription_start_date=" . $db->tosql($subscription_start_date, DATETIME);
							$sql .= ", subscription_expiry_date=" . $db->tosql($subscription_expiry_date, DATETIME);
							$sql .= " WHERE order_item_id=" . $db->tosql($order_item_id, INTEGER);
							$db->query($sql);
						}
					} else {
					  // set subscription date
						if (!is_array($subscription_start_date)) {
							$subscription_start_date = va_time();
							$subscription_start_date_ts = mktime (0,0,0, $subscription_start_date[MONTH], $subscription_start_date[DAY], $subscription_start_date[YEAR]);
						} else {
							$subscription_start_date_ts = mktime (0,0,0, $subscription_start_date[MONTH], $subscription_start_date[DAY], $subscription_start_date[YEAR]);
						}
						if ($new_item_paid) {
							// update order item with subscriptions dates
							if ($subscription_period == 1) {
								$subscription_expiry_date_ts = mktime (0, 0, 0, $subscription_start_date[MONTH], $subscription_start_date[DAY] + $subscription_interval, $subscription_start_date[YEAR]);
							} elseif ($subscription_period == 2) {
								$subscription_expiry_date_ts = mktime (0, 0, 0, $subscription_start_date[MONTH], $subscription_start_date[DAY] + ($subscription_interval * 7), $subscription_start_date[YEAR]);
							} elseif ($subscription_period == 3) {
								$subscription_expiry_date_ts = mktime (0, 0, 0, $subscription_start_date[MONTH] + $subscription_interval, $subscription_start_date[DAY], $subscription_start_date[YEAR]);
							} else {
								$subscription_expiry_date_ts = mktime (0, 0, 0, $subscription_start_date[MONTH], $subscription_start_date[DAY], $subscription_start_date[YEAR] + $subscription_interval);
							}

							$sql  = " UPDATE " . $table_prefix . "orders_items SET ";
							$sql .= " subscription_start_date=" . $db->tosql($subscription_start_date_ts, DATETIME);
							$sql .= ", subscription_expiry_date=" . $db->tosql($subscription_expiry_date_ts, DATETIME);
							$sql .= " WHERE order_item_id=" . $db->tosql($order_item_id, INTEGER);
							$db->query($sql);
						} else {
							$sql  = " UPDATE " . $table_prefix . "orders_items SET ";
							$sql .= " subscription_expiry_date='' ";
							$sql .= " WHERE order_item_id=" . $db->tosql($order_item_id, INTEGER);
							$db->query($sql);
						}
				  
					}
				}

				// generate credit notes
				if (is_array($credit_notes_items) && sizeof($credit_notes_items) > 0) {
					global $is_admin_path;
					$root_folder_path = $is_admin_path ? "../" : "";
					include_once($root_folder_path."includes/order_recalculate.php");
					include_once($root_folder_path."includes/credit_notes_functions.php");
					generate_credit_note($order_id, $credit_notes_items, $other_statuses);
				}

				// update stock levels
				foreach ($items_stock_levels as $order_item_id => $item_info) {
					$item_id = $item_info["item_id"];
					$quantity = $item_info["quantity"];
					$stock_action = $item_info["stock_action"];
					$stock_level = $item_info["stock_level"];
					$use_stock_level = $item_info["use_stock_level"];
					$cart_item_id = $item_info["cart_item_id"];
					// update stock level for product
					if ($use_stock_level) {
						if (strlen($stock_level)) {
							if ($stock_action == -1) {
								$sql  = " UPDATE " . $table_prefix . "items SET ";
								$sql .= " stock_level=stock_level+" . $db->tosql($quantity, NUMBER);
								$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
							} else {
								$sql  = " UPDATE " . $table_prefix . "items SET ";
								$sql .= " stock_level=stock_level-" . $db->tosql($quantity, NUMBER);
								$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
							}
							$db->query($sql);
						} else {
							$sql  = " UPDATE " . $table_prefix . "items SET ";
							$sql .= " stock_level=" . $db->tosql(-$stock_action * $quantity, NUMBER);
							$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
							$db->query($sql);
						}
					}
					// update information for saved items
					if ($cart_item_id) {
						$sql  = " UPDATE " . $table_prefix . "saved_items SET ";
						$sql .= " quantity_bought=quantity_bought+" . $db->tosql($stock_action * $quantity, NUMBER);
						$sql .= " WHERE cart_item_id=" . $db->tosql($cart_item_id, INTEGER);
						$db->query($sql);
					}
					// check information for order item properties
					$options_values_ids = array();
					$sql  = " SELECT property_values_ids FROM " . $table_prefix . "orders_items_properties oip ";
					$sql .= " WHERE order_item_id=" . $db->tosql($order_item_id, INTEGER);
					$sql .= " ORDER BY property_order";
					$db->query($sql);
					while ($db->next_record()) {
						$property_values_ids = $db->f("property_values_ids");
						if ($property_values_ids) {
							$values_ids = explode(",", $property_values_ids);
							for ($v = 0; $v < sizeof($values_ids); $v++) {
								$value_id = $values_ids[$v];
								$options_values_ids[] = $value_id;
							}
						}
					}
					foreach ($options_values_ids as $value_id) {
						$sql  = " SELECT stock_level, use_stock_level ";
						$sql .= " FROM " . $table_prefix . "items_properties_values ";
						$sql .= " WHERE item_property_id=" . $db->tosql($value_id, INTEGER);
						$db->query($sql);
						if ($db->next_record()) {
							$option_stock_level = $db->f("stock_level");
							$option_use_stock_level = $db->f("use_stock_level");
							if ($option_use_stock_level) {
								if (strlen($option_stock_level)) {
									$sql  = " UPDATE " . $table_prefix . "items_properties_values SET ";
									$sql .= " stock_level=stock_level-" . $db->tosql($stock_action * $quantity, NUMBER);
									$sql .= " WHERE item_property_id=" . $db->tosql($value_id, INTEGER);
									$db->query($sql);
								} else {
									$sql  = " UPDATE " . $table_prefix . "items_properties_values SET ";
									$sql .= " stock_level=" . $db->tosql(-$stock_action * $quantity, NUMBER);
									$sql .= " WHERE item_property_id=" . $db->tosql($value_id, INTEGER);
									$db->query($sql);
								}
							}
						}
					}
				}

				calculate_commissions_points($order_id, "", $commissions_points);

				// save events
				for ($i = 0; $i < sizeof($events); $i++) {
					list($new_status_id, $order_item_id, $event_date, $event_type, $event_name, $event_description) = $events[$i];
					$r->set_value("status_id",   $new_status_id);
					$r->set_value("order_items", $order_item_id);
					$r->set_value("event_date",  va_time());
					$r->set_value("event_type",  $event_type);
					$r->set_value("event_name",  $event_name);
					$r->set_value("event_description", $event_description);
					$r->insert_record();
				}

			}

		}

		// apply status php library when status applied
		if ($is_valid && $status_id != $current_status_id && strlen($status_php_lib)) {
			if ($is_admin_path) {
				$status_php_lib = preg_replace("/^\.\//", "", $status_php_lib);
				$status_php_lib = "../".$status_php_lib;
			} 

			$error_message = "";
			if (file_exists($status_php_lib)) {
				$cwd = getcwd(); // save current working directory as it could be changed
				$cap = $is_admin_path;  $csf = $is_sub_folder;
				include($status_php_lib);
				chdir ($cwd); // set saved working directory
				$is_admin_path = $cap; $is_sub_folder = $csf;
			} else {
				$error_message = va_constant("APPROPRIATE_LIBRARY_ERROR_MSG") .": " . $root_folder_path . $status_php_lib;
			}
		}

		// restore active administrator language messages 
		if ($active_language_code != $order_language_code) {
			$language_code = $active_language_code;
			include($root_folder_path."messages/".$language_code."/messages.php");
			include($root_folder_path."messages/".$language_code."/cart_messages.php");
			include($root_folder_path."messages/".$language_code."/download_messages.php");
			if (file_exists($root_folder_path."messages/".$language_code."/custom_messages.php")) {
				include_once($root_folder_path."messages/".$language_code."/custom_messages.php");
			}
		}

		return $is_update;
	}

	function calculate_commissions_points($order_id, $order_items_ids = "", $commissions_points = array())
	{
		global $db, $datetime_show_format, $table_prefix, $settings;

		if (!is_array($commissions_points)) { $commissions_points = array(); }

		if (sizeof($commissions_points) == 0) {
			$commissions_points = array();

			$sql  = " SELECT oi.order_item_id, oi.user_id, oi.item_user_id, oi.affiliate_user_id, ";
			$sql .= " os.status_id, os.commission_action, os.points_action, os.credit_action, ";
			$sql .= " oi.item_id, oi.item_name, oi.price, oi.quantity, ";
			$sql .= " oi.merchant_commission, oi.affiliate_commission, oi.reward_points, oi.reward_credits, oi.points_price ";
			$sql .= " FROM (" . $table_prefix . "orders_items oi ";
			$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON os.status_id=oi.item_status) ";
			$sql .= " WHERE oi.order_id=" . $db->tosql($order_id, INTEGER);
			if ($order_items_ids) {
				$sql .= " AND oi.order_item_id IN (" . $db->tosql($order_items_ids, INTEGERS_LIST) . ")" ;
			}
			$db->query($sql);
			while ($db->next_record()) {
				$order_item_id = $db->f("order_item_id");
				$status_id = $db->f("status_id");

				$order_user_id = $db->f("user_id");
				$affiliate_user_id = $db->f("affiliate_user_id");
				$item_user_id = $db->f("item_user_id");
				$item_id = $db->f("item_id");
				$price = $db->f("price");
				$merchant_commission = $db->f("merchant_commission");
				$affiliate_commission = $db->f("affiliate_commission");
				$reward_points = $db->f("reward_points");
				$reward_credits = $db->f("reward_credits");
				$points_price = $db->f("points_price");
				$quantity = $db->f("quantity");

				$commission_action = $db->f("commission_action");
				$points_action = $db->f("points_action");
				$credit_action = $db->f("credit_action");

				$commissions_points[$order_item_id] = array(
					"order_item_id" => $order_item_id,
					"order_user_id" => $order_user_id,
					"affiliate_user_id" => $affiliate_user_id,
					"item_user_id" => $item_user_id,
					"quantity" => $quantity,
					"affiliate_commission" => $affiliate_commission,
					"merchant_commission" => $merchant_commission,
					"reward_points" => $reward_points,
					"reward_credits" => $reward_credits,
					"points_price" => $points_price,
					"commission_action" => $commission_action,
					"points_action" => $points_action,
					"credit_action" => $credit_action,
				);
			}

			// check points price for order shipping and properties
			if ($order_items_ids == "") {
				$order_info = array();
				$sql  = " SELECT o.user_id, o.affiliate_user_id, ";
				$sql .= " o.shipping_points_amount, o.properties_points_amount, ";
				$sql .= " os.status_id, os.commission_action, os.points_action, os.credit_action ";
				$sql .= " FROM (" . $table_prefix . "orders o ";
				$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON os.status_id=o.order_status) ";
				$sql .= " WHERE o.order_id=" . $db->tosql($order_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$order_info = $db->Record;
				}
				$shipping_points_amount = get_setting_value($order_info, "shipping_points_amount", 0);
				$properties_points_amount = get_setting_value($order_info, "properties_points_amount", 0);
				$order_points_action = get_setting_value($order_info, "points_action", "");
				if ($order_points_action && ($shipping_points_amount + $properties_points_amount) > 0) {
					$order_user_id = get_setting_value($order_info, "user_id", "");
					$affiliate_user_id = get_setting_value($order_info, "affiliate_user_id", "");
					$order_commission_action = get_setting_value($order_info, "commission_action", "");
					$order_credit_action = get_setting_value($order_info, "credit_action", "");
	  
					$commissions_points["order"] = array(
						"order_item_id" => 0,
						"order_user_id" => $order_user_id,
						"affiliate_user_id" => $affiliate_user_id,
						"item_user_id" => 0,
						"quantity" => 1,
						"affiliate_commission" => 0,
						"merchant_commission" => 0,
						"reward_points" => 0,
						"reward_credits" => 0,
						"points_price" => ($shipping_points_amount + $properties_points_amount),
						"commission_action" => $order_commission_action,
						"points_action" => $order_points_action,
						"credit_action" => $order_credit_action,
					);
				}
			}
		}

		// initialize array to save users ids which should be updated later
		$users_commissions = array(); $users_points = array(); $users_credits = array();

		// add or subtract commisions, points or credits
		if (sizeof($commissions_points) > 0) {
			$uc = new VA_Record($table_prefix . "users_commissions");
			$uc->add_textbox("payment_id", INTEGER);
			$uc->add_textbox("user_id", INTEGER);
			$uc->add_textbox("order_id", INTEGER);
			$uc->add_textbox("order_item_id", INTEGER);
			$uc->add_textbox("commission_amount", NUMBER);
			$uc->add_textbox("commission_action", INTEGER);
			$uc->add_textbox("commission_type", INTEGER);
			$uc->add_textbox("date_added", DATETIME);
	  
			$uc->set_value("payment_id", 0);
			$uc->set_value("order_id", $order_id);
			$uc->set_value("date_added", va_time());
	  
			$pts = new VA_Record($table_prefix . "users_points");
			$pts->add_textbox("user_id", INTEGER);
			$pts->add_textbox("order_id", INTEGER);
			$pts->add_textbox("order_item_id", INTEGER);
			$pts->add_textbox("points_amount", NUMBER);
			$pts->add_textbox("points_action", INTEGER);
			$pts->add_textbox("points_type", INTEGER);
			$pts->add_textbox("date_added", DATETIME);
	  
			$pts->set_value("order_id", $order_id);
			$pts->set_value("date_added", va_time());
	  
			$cdt = new VA_Record($table_prefix . "users_credits");
			$cdt->add_textbox("user_id", INTEGER);
			$cdt->add_textbox("order_id", INTEGER);
			$cdt->add_textbox("order_item_id", INTEGER);
			$cdt->add_textbox("credit_amount", NUMBER);
			$cdt->add_textbox("credit_action", INTEGER);
			$cdt->add_textbox("credit_type", INTEGER);
			$cdt->add_textbox("date_added", DATETIME);
	  
			$cdt->set_value("order_id", $order_id);
			$cdt->set_value("date_added", va_time());
	  
			foreach ($commissions_points as $key => $data) {
				// get general data
				$order_item_id = get_setting_value($data, "order_item_id", 0);
				$order_user_id = get_setting_value($data, "order_user_id", "");
				$item_user_id = get_setting_value($data, "item_user_id", "");
				$affiliate_user_id = get_setting_value($data, "affiliate_user_id", "");
				$quantity = get_setting_value($data, "quantity", 1);
				// get actions
				$commission_action = get_setting_value($data, "commission_action", "");
				$points_action = get_setting_value($data, "points_action", "");
				$credit_action = get_setting_value($data, "credit_action", "");
				// check merchant commissions
				$merchant_commission = get_setting_value($data, "merchant_commission", 0);
				if ($merchant_commission > 0) {
					$uc->set_value("user_id", $item_user_id);
					$uc->set_value("order_item_id", $order_item_id);
					$uc->set_value("commission_type", 1);
	  
					$sql  = " SELECT SUM(commission_action * commission_amount) FROM " . $table_prefix . "users_commissions ";
					$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
					$sql .= " AND order_item_id=" . $db->tosql($order_item_id, INTEGER);
					$sql .= " AND commission_type=1 ";
					$sum_commission = doubleval(get_db_value($sql));
					$item_commission = 0;
					if ($commission_action == 1) { // add merchant commissions
						$item_commission = $merchant_commission * $quantity;
					} else if ($commission_action == -1) { // subtract merchant commissions
						$item_commission = 0;
					}
					if ($commission_action && $sum_commission != $item_commission) {
						$users_commissions[$item_user_id] = true;
						if ($sum_commission > $item_commission) {
							$uc->set_value("commission_action", -1);
							$uc->set_value("commission_amount", ($sum_commission - $item_commission));
							$uc->insert_record();
						} else if ($sum_commission < $item_commission) {		
							$uc->set_value("commission_action", 1);
							$uc->set_value("commission_amount", ($item_commission - $sum_commission));
							$uc->insert_record();
						}
					}
				}

				// check affiliate commissions
				$affiliate_commission = get_setting_value($data, "affiliate_commission", 0);
				if ($affiliate_commission > 0) {
					$uc->set_value("user_id", $affiliate_user_id);
					$uc->set_value("order_item_id", $order_item_id);
					$uc->set_value("commission_amount", ($affiliate_commission * $quantity));
					$uc->set_value("commission_type", 2);
	  
					$sql  = " SELECT SUM(commission_action * commission_amount) FROM " . $table_prefix . "users_commissions ";
					$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
					$sql .= " AND order_item_id=" . $db->tosql($order_item_id, INTEGER);
					$sql .= " AND commission_type=2 ";
					$sum_commission = doubleval(get_db_value($sql));
					$item_commission = 0;
					if ($commission_action == 1) { // add affiliate commissions
						$item_commission = $affiliate_commission * $quantity;
					} else if ($commission_action == -1) { // subtract affiliate commissions
						$item_commission = 0;
					}
					if ($commission_action && $sum_commission != $item_commission) {
						$users_commissions[$affiliate_user_id] = true;
						if ($sum_commission > $item_commission) {
							$uc->set_value("commission_action", -1);
							$uc->set_value("commission_amount", ($sum_commission - $item_commission));
							$uc->insert_record();
						} else if ($sum_commission < $item_commission) {		
							$uc->set_value("commission_action", 1);
							$uc->set_value("commission_amount", ($item_commission - $sum_commission));
							$uc->insert_record();
						}
					}
				}

				// add or subtract reward points if they available for product and user registered
				$reward_points = get_setting_value($data, "reward_points", 0);
				if ($reward_points > 0 && $order_user_id > 0) {
					$pts->set_value("user_id", $order_user_id);
					$pts->set_value("order_item_id", $order_item_id);
					$pts->set_value("points_type", 2);
					$sql  = " SELECT SUM(points_action * points_amount) FROM " . $table_prefix . "users_points ";
					$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
					$sql .= " AND order_item_id=" . $db->tosql($order_item_id, INTEGER);
					$sql .= " AND points_type=2 ";
					$sum_points = doubleval(get_db_value($sql));

					$item_points = 0;
					if ($commission_action == 1) { // add reward points 
						$item_points = $reward_points * $quantity;
					} else if ($commission_action == -1) { // subtract reward points
						$item_points = 0;
					}
					if ($commission_action && $sum_points != $item_points) {
						$users_points[$order_user_id] = true;
						if ($sum_points > $item_points) {
							$pts->set_value("points_action", -1);
							$pts->set_value("points_amount", ($sum_points - $item_points));
							$pts->insert_record();
						} else if ($sum_points < $item_points) {		
							$pts->set_value("points_action", 1);
							$pts->set_value("points_amount", ($item_points - $sum_points));
							$pts->insert_record();
						}
					}
				}

				// add or subtract reward points if they available for product and user registered
				$reward_credits = get_setting_value($data, "reward_credits", 0);
				if ($reward_credits > 0 && $order_user_id > 0) {
					$cdt->set_value("user_id", $order_user_id);
					$cdt->set_value("order_item_id", $order_item_id);
					$cdt->set_value("credit_type", 2);
	  
					$sql  = " SELECT SUM(credit_action * credit_amount) FROM " . $table_prefix . "users_credits ";
					$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
					$sql .= " AND order_item_id=" . $db->tosql($order_item_id, INTEGER);
					$sql .= " AND credit_type=2 ";
					$sum_credits = get_db_value($sql);

					$item_credits = 0;
					if ($commission_action == 1) { // add reward credits 
						$item_credits = $reward_credits * $quantity;
					} else if ($commission_action == -1) { // subtract reward credits
						$item_credits = 0;
					}
					if ($commission_action && $sum_credits != $item_credits) {
						$users_credits[$order_user_id] = true;
						if ($sum_credits > $item_credits) {
							$cdt->set_value("credit_action", -1);
							$cdt->set_value("credit_amount", ($sum_credits - $item_credits));
							$cdt->insert_record();
						} else if ($sum_credits < $item_credits) {		
							$cdt->set_value("credit_action", 1);
							$cdt->set_value("credit_amount", ($item_credits - $sum_credits));
							$cdt->insert_record();
						}
					}
				}

				// subtract or return points if they were used to pay for something
				$points_price = get_setting_value($data, "points_price", 0);
				if ($points_price > 0) {
					$pts->set_value("user_id", $order_user_id);
					$pts->set_value("order_item_id", $order_item_id);
					$pts->set_value("points_amount", ($points_price * $quantity));
					$pts->set_value("points_type", 1);
	  
					$sql  = " SELECT SUM(points_action * points_amount) FROM " . $table_prefix . "users_points ";
					$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
					$sql .= " AND order_item_id=" . $db->tosql($order_item_id, INTEGER);
					$sql .= " AND points_type=1 ";
					$sum_points = get_db_value($sql);

					$item_points = 0;
					if ($points_action == 1) { // return points to account
						$item_points = 0;
					} else if ($points_action == -1) { // subtract points from account
						$item_points = -($points_price * $quantity);
					}
					if ($points_action && $sum_points != $item_points) {
						$users_points[$order_user_id] = true;
						if ($sum_points > $item_points) {
							$pts->set_value("points_action", -1);
							$pts->set_value("points_amount", ($sum_points - $item_points));
							$pts->insert_record();
						} else if ($sum_points < $item_points) {		
							$pts->set_value("points_action", 1);
							$pts->set_value("points_amount", ($item_points - $sum_points));
							$pts->insert_record();
						}
					}
				}

			} // end of order item cycle
		} // end of adding commissions, points and credits

		// start updating total fields

		// update total_points field in users table
		foreach ($users_points as $user_id => $user_value) {
			$sql  = " SELECT SUM(points_action * points_amount) ";
			$sql .= " FROM " . $table_prefix . "users_points ";
			$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
			$total_points_sum = get_db_value($sql);

			$sql  = " UPDATE " . $table_prefix . "users ";
			$sql .= " SET total_points=" . $db->tosql($total_points_sum, NUMBER);
			$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
			$db->query($sql);

			// update user information in session if available
			$user_info = get_session("session_user_info");
			$session_user_id = get_setting_value($user_info, "user_id", 0);
			$session_total_points = get_setting_value($user_info, "total_points", 0);
			if ($session_user_id == $user_id && $total_points_sum != $session_total_points) {
				$user_info["total_points"] = $total_points_sum;
				set_session("session_user_info", $user_info);
			}
		}
		// update credit_balance field in users table
		foreach ($users_credits as $user_id => $user_value) {
			$sql  = " SELECT SUM(credit_action * credit_amount) ";
			$sql .= " FROM " . $table_prefix . "users_credits ";
			$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
			$total_credit_sum = get_db_value($sql);

			$sql  = " UPDATE " . $table_prefix . "users ";
			$sql .= " SET credit_balance=" . $db->tosql($total_credit_sum, NUMBER);
			$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
			$db->query($sql);

			// update user information in session if available
			$user_info = get_session("session_user_info");
			$session_user_id = get_setting_value($user_info, "user_id", 0);
			$session_credit_balance = get_setting_value($user_info, "credit_balance", 0);
			if ($session_user_id == $user_id && $total_credit_sum != $session_credit_balance) {
				$user_info["credit_balance"] = $total_credit_sum;
				set_session("session_user_info", $user_info);
			}
		}

		// check if new user payment should be generated
		if (sizeof($users_commissions) > 0) {
			$min_payment_amount = get_setting_value($settings, "min_payment_amount", 100);
			foreach ($users_commissions as $user_id => $user_value) {
				$total_commissions = 0; $commissions_ids = ""; $commission_start = va_timestamp(); $commission_end = 0;
				$sql  = " SELECT commission_id, commission_action, commission_amount, date_added ";
				$sql .= " FROM " . $table_prefix . "users_commissions ";
				$sql .= " WHERE payment_id=0 AND user_id=" . $db->tosql($user_id, INTEGER);
				$db->query($sql);
				while ($db->next_record()) {
					$commission_id = $db->f("commission_id");
					$commission_action = $db->f("commission_action");
					$commission_amount = $db->f("commission_amount");
					$date_added = $db->f("date_added", DATETIME);
					$date_added_ts = mktime ($date_added[HOUR], $date_added[MINUTE], $date_added[SECOND], $date_added[MONTH], $date_added[DAY], $date_added[YEAR]);
					if ($date_added_ts > $commission_end) {
						$commission_end = $date_added_ts;
					}
					if ($date_added_ts < $commission_start) {
						$commission_start = $date_added_ts;
					}
					if ($commissions_ids) { $commissions_ids .= ","; }
					$commissions_ids .= $commission_id;
					$total_commissions += ($commission_action * $commission_amount);
				}
				if ($total_commissions >= $min_payment_amount) {
					$up = new VA_Record($table_prefix . "users_payments");
					if ($db->DBType == "postgre") {
						$payment_id = get_db_value(" SELECT NEXTVAL('seq_" . $table_prefix . "users_payments') ");
						$up->add_textbox("payment_id", INTEGER);
						$up->set_value("payment_id", $payment_id);
					}
					$up->add_textbox("user_id", INTEGER);
					$up->add_textbox("is_paid", INTEGER);
					$up->add_textbox("transaction_id", TEXT);
					$up->add_textbox("payment_total", NUMBER);
					$up->add_textbox("payment_name", TEXT);
					$up->add_textbox("payment_notes", TEXT);
					$up->add_textbox("date_added", DATETIME);
					$up->add_textbox("admin_id_added_by", INTEGER);
					$up->add_textbox("admin_id_modified_by", INTEGER);


					// generate payment name
					$payment_name = va_date($datetime_show_format, $commission_start) . " - " . va_date($datetime_show_format, $commission_end);

					$up->set_value("user_id", $user_id);
					$up->set_value("is_paid", 0);
					$up->set_value("payment_total", $total_commissions);
					$up->set_value("payment_name", $payment_name);
					$up->set_value("payment_notes", va_constant("AUTO_SUBMITTED_PAYMENT_MSG")); //"Auto-submitted payment"
					$up->set_value("date_added", va_time());
					$up->set_value("admin_id_added_by", 0);
					$up->set_value("admin_id_modified_by", 0);
					$up->insert_record();
					if ($db->DBType == "mysql") {
						$payment_id = get_db_value(" SELECT LAST_INSERT_ID() ");
					} elseif ($db->DBType == "access") {
						$payment_id = get_db_value(" SELECT @@IDENTITY ");
					} elseif ($db->DBType == "db2") {
						$payment_id = get_db_value(" SELECT PREVVAL FOR seq_" . $table_prefix . "users_payments FROM " . $table_prefix . "users_payments");
					} else {
						$payment_id = get_db_value(" SELECT MAX(payment_id) FROM " . $table_prefix . "users_payments");
					}

					$sql  = " UPDATE " . $table_prefix . "users_commissions ";
					$sql .= " SET payment_id=" . $db->tosql($payment_id, INTEGER);
					$sql .= " WHERE payment_id=0 AND user_id=" . $db->tosql($user_id, INTEGER);
					$sql .= " AND commission_id IN (" . $db->tosql($commissions_ids, INTEGERS_LIST) . ")";
					$db->query($sql);

					// check and update total amount for generated payment if it was change
					$sql  = " SELECT SUM(commission_action * commission_amount) ";
					$sql .= " FROM " . $table_prefix . "users_commissions ";
					$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
					$payment_total = get_db_value($sql);
					if ($payment_total != $total_commissions) {
						$sql  = " UPDATE " . $table_prefix . "users_payments ";
						$sql .= " SET payment_total=" . $db->tosql($payment_total, NUMBER);
						$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
						$db->query($sql);
					}
				}
			}
		}
		// end updating total values
	}


	function update_order_items($order_id, $updated_order_item_id = "")
	{
		global $db, $table_prefix;
		$order_tax_rates = order_tax_rates($order_id);

		$sql  = " SELECT o.shipping_cost, o.shipping_taxable, st.tare_weight, o.properties_total, o.properties_taxable, ";
		$sql .= " o.total_discount, o.total_discount_tax, o.tax_percent, o.processing_fee, o.tax_prices_type, o.tax_round_type ";
		$sql .= " FROM (" . $table_prefix . "orders o ";
		$sql .= " LEFT JOIN " . $table_prefix . "shipping_types st ON st.shipping_type_id=o.shipping_type_id) ";
		$sql .= " WHERE o.order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {

			$shipping_cost = $db->f("shipping_cost");
			$shipping_taxable = $db->f("shipping_taxable");
			$tare_weight = $db->f("tare_weight");

			$properties_total = $db->f("properties_total");
			$properties_taxable = $db->f("properties_taxable");
			$total_discount = $db->f("total_discount");
			$total_discount_tax = $db->f("total_discount_tax");
			$tax_percent = $db->f("tax_percent");
			$processing_fee = $db->f("processing_fee");
			
			$tax_prices_type = $db->f("tax_prices_type");
			$tax_round = $db->f("tax_round_type");
		} else {
			return false;
		}

		$total_buying = 0; $goods_total = 0; $goods_tax_total = 0; $total_quantity = 0;
		$weight_total = 0; $actual_weight_total = 0; $tax_total = 0; $order_total = 0;
		$sql  = " SELECT * FROM " . $table_prefix . "orders_items ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$weight = $db->f("weight");
			$actual_weight = $db->f("actual_weight");
			$buying_price = $db->f("buying_price");
			$price = $db->f("price");
			$quantity = $db->f("quantity");
			$item_tax_id = $db->f("tax_id");
			$tax_free = $db->f("tax_free");
			$item_tax_percent = $db->f("tax_percent");
			$item_type_id = $db->f("item_type_id");
			if (!strlen($item_tax_percent)) { $item_tax_percent = $tax_percent; }
			
			$item_total = $price * $quantity;
			$item_tax = get_tax_amount($order_tax_rates, $item_type_id, $price, 1, $item_tax_id, $tax_free, $item_tax_percent, "", 1, $tax_prices_type, $tax_round);
			$item_tax_total = get_tax_amount($order_tax_rates, $item_type_id, $item_total, $quantity, $item_tax_id, $tax_free, $item_tax_percent, "", 1, $tax_prices_type, $tax_round);
			
			if ($tax_prices_type == 1) {
				$price_excl_tax = $price - $item_tax;
				$price_incl_tax = $price;
				$price_excl_tax_total = $item_total - $item_tax_total;
				$price_incl_tax_total = $item_total;
			} else {
				$price_excl_tax = $price;
				$price_incl_tax = $price + $item_tax;
				$price_excl_tax_total = $item_total;
				$price_incl_tax_total = $item_total + $item_tax_total;
			}
			$total_quantity  += $quantity;
			$weight_total    += ($weight * $quantity);
			$actual_weight_total += ($actual_weight * $quantity);
			$total_buying    += ($buying_price * $quantity);
			$goods_total     += $item_total;			
			$goods_tax_total += $item_tax_total;
		}
		$weight_total += $tare_weight;
		$actual_weight_total += $tare_weight;
		if (!$total_discount_tax && $goods_tax_total > 0) {
			$total_discount_tax = round(($total_discount * $goods_tax_total) / $goods_total, 2);
			if ($tax_prices_type == 1) {
				$total_discount = $total_discount - $total_discount_tax;
			}
		}

		$shipping_tax_id = 0; $properties_tax_id = 0;
		$shipping_tax_free = (!$shipping_taxable);
		$shipping_tax = get_tax_amount($order_tax_rates, "shipping", $shipping_cost, 1, $shipping_tax_id, $shipping_tax_free, $shipping_tax_percent, "", 1, $tax_prices_type, $tax_round);
		$properties_tax = get_tax_amount($order_tax_rates, "", $properties_taxable, 1, $properties_tax_id, 0, $properties_tax_percent, "", 1, $tax_prices_type, $tax_round);

		$tax_total = $goods_tax_total - $total_discount_tax + $properties_tax + $shipping_tax;
		$order_total = round($goods_total, 2) - round($total_discount, 2) + round($properties_total, 2) + round($shipping_cost, 2) + $processing_fee;
		if ($tax_prices_type != 1) {
			$order_total += round($tax_total, 2);
		}

		$sql  = " UPDATE " . $table_prefix . "orders SET ";
		$sql .= " total_buying=" . $db->tosql($total_buying, FLOAT) . ", ";
		$sql .= " goods_total=" . $db->tosql($goods_total, FLOAT) . ", ";
		$sql .= " total_quantity=" . $db->tosql($total_quantity, FLOAT) . ", ";
		$sql .= " weight_total=" . $db->tosql($weight_total, FLOAT) . ", ";
		$sql .= " actual_weight_total=" . $db->tosql($actual_weight_total, FLOAT) . ", ";
		$sql .= " tax_total=" . $db->tosql($tax_total, FLOAT) . ", ";
		$sql .= " order_total=" . $db->tosql($order_total, FLOAT);
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		
		if ($updated_order_item_id > 0) {
			$sql = "DELETE FROM " . $table_prefix . "users_points WHERE order_item_id=" . $db->tosql($updated_order_item_id, INTEGER);
			$db->query($sql);
			calculate_commissions_points($order_id, $updated_order_item_id);
		}
	}

	function remove_orders($orders_ids, $delete_orders = true)
	{
		global $db, $table_prefix;

		$downloads_ids = "";
		$sql = "SELECT download_id FROM " . $table_prefix . "items_downloads WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")";
		$db->query($sql);
		while ($db->next_record()) {
			if (strlen($downloads_ids)) { $downloads_ids .= ","; }
			$downloads_ids .= $db->f("download_id");
		}
		$order_tax_ids = "";
		$sql = "SELECT order_tax_id FROM " . $table_prefix . "orders_taxes WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")";
		$db->query($sql);
		while ($db->next_record()) {
			if (strlen($order_tax_ids)) { $order_tax_ids .= ","; }
			$order_tax_ids .= $db->f("order_tax_id");
		}

		$items = array(); $saved_items = array(); $users_points = array(); $users_credits = array();
		$sql  = " SELECT oi.item_id, oi.cart_item_id, oi.quantity, oi.user_id, oi.points_price, oi.reward_points, oi.reward_credits, ";
		$sql .= " os.stock_level_action ";
		$sql .= " FROM (" . $table_prefix . "orders_items oi ";
		$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON os.status_id=oi.item_status) ";
		$sql .= " WHERE oi.order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")";
		$db->query($sql);
		while ($db->next_record()) {
			$item_id  = $db->f("item_id");
			$cart_item_id = $db->f("cart_item_id");
			$quantity = $db->f("quantity");
			$stock_level_action = $db->f("stock_level_action");
			$user_id  = $db->f("user_id");
			$points_price = $db->f("points_price");
			$reward_points = $db->f("reward_points");
			$reward_credits = $db->f("reward_credits");
			// release stock only if it was reserved before
			if ($stock_level_action == 1) {
				if (isset($items[$item_id])) {
					$items[$item_id] += $quantity;
				} else {
					$items[$item_id] = $quantity;
				}
				if ($cart_item_id) {
					if (isset($saved_items[$cart_item_id])) {
						$saved_items[$cart_item_id] += $quantity;
					} else {
						$saved_items[$cart_item_id] = $quantity;
					}
				}
			}
			if ($user_id > 0 && ($points_price > 0 || $reward_points > 0)) {
				$users_points[$user_id] = $user_id;
			}
			if ($user_id > 0 && $reward_credits > 0) {
				$users_credits[$user_id] = $user_id;
			}
		}

		$sql  = " SELECT user_id, total_points_amount, total_reward_points, total_reward_credits, credit_amount ";
		$sql .= " FROM " . $table_prefix . "orders ";
		$sql .= " WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")";
		$db->query($sql);
		while ($db->next_record()) {
			$user_id  = $db->f("user_id");
			$total_points_amount = $db->f("total_points_amount");
			$total_reward_points = $db->f("total_reward_points");
			$total_reward_credits = $db->f("total_reward_credits");
			$credit_amount = $db->f("credit_amount");
			if ($user_id > 0 && ($total_points_amount > 0 || $total_reward_points > 0)) {
				$users_points[$user_id] = $user_id;
			}
			if ($user_id > 0 && ($total_reward_credits > 0 || $credit_amount > 0)) {
				$users_credits[$user_id] = $user_id;
			}
		}
		$serials = array();
		$sql  = " SELECT ois.item_id, ois.serial_number ";
		$sql .= " FROM " . $table_prefix . "orders_items_serials ois, " . $table_prefix . "items i ";
		$sql .= " WHERE ois.item_id=i.item_id ";
		$sql .= " AND i.generate_serial=2 ";
		$sql .= " AND ois.order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")";
		$sql .= " GROUP BY ois.item_id, ois.serial_number ";
		$db->query($sql);
		while ($db->next_record()) {
			$item_id  = $db->f("item_id");
			$serial_number = $db->f("serial_number");
			if (isset($serials[$item_id])) {
				$serials[$item_id] .= "," . $db->tosql($serial_number, TEXT);
			} else {
				$serials[$item_id] = $db->tosql($serial_number, TEXT);
			}
		}
		$options_values_ids = array();
		$sql  = " SELECT oip.property_values_ids, oi.quantity, os.stock_level_action ";
		$sql .= " FROM ((" . $table_prefix . "orders_items_properties oip ";
		$sql .= " INNER JOIN " . $table_prefix . "orders_items oi ON oip.order_item_id=oi.order_item_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON os.status_id=oi.item_status) ";
		$sql .= " WHERE oip.order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")";
		$db->query($sql);
		while ($db->next_record()) {
			$property_values_ids = $db->f("property_values_ids");
			$quantity = $db->f("quantity");
			$stock_level_action = $db->f("stock_level_action");
			// release stock only if it was reserved before
			if ($stock_level_action == 1 && $property_values_ids) {
				$values_ids = explode(",", $property_values_ids);
				for ($v = 0; $v < sizeof($values_ids); $v++) {
					$value_id = $values_ids[$v];
					if (isset($options_values_ids[$value_id])) {
						$options_values_ids[$value_id] += $quantity;
					} else {
						$options_values_ids[$value_id] = $quantity;
					}
				}
			}
		}
		// check number of used coupons to decrease number 
		$coupons = array(); 
		$sql  = " SELECT oc.coupon_id, oc.discount_type, oc.discount_amount  ";
		$sql .= " FROM " . $table_prefix . "orders_coupons oc ";
		$sql .= " WHERE oc.order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")";
		$db->query($sql);
		while ($db->next_record()) {
			$coupon_id = $db->f("coupon_id");
			$coupon_id = $db->f("coupon_id");
			$discount_type = $db->f("discount_type");
			$discount_amount = $db->f("discount_amount");
			if (isset($coupons[$coupon_id])) {
				$coupons[$coupon_id]["used"] += 1;
				$coupons[$coupon_id]["amount"] += $discount_amount;
			} else {
				$coupons[$coupon_id] = array("used" => 1, "type" => $discount_type, "amount" => $discount_amount);
			}
		}
		// check coupon vouchers which were purchased for selected orders to delete their events
		$coupons_ids = "";
		$sql  = " SELECT c.coupon_id ";
		$sql .= " FROM " . $table_prefix . "coupons c ";
		$sql .= " WHERE c.order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")";
		$db->query($sql);
		while ($db->next_record()) {
			$coupon_id = $db->f("coupon_id");
			if (strlen($coupons_ids)) { $coupons_ids .= ","; }
			$coupons_ids .= $coupon_id;
		}

		if ($delete_orders) {
			// keep original order and it events
			$db->query("DELETE FROM " . $table_prefix . "orders WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")");
			$db->query("DELETE FROM " . $table_prefix . "orders_events WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")");
			$db->query("DELETE FROM " . $table_prefix . "orders_payments WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")");
		} else {
			$sql  = " DELETE FROM " . $table_prefix . "orders_payments WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")";
			$sql .= " AND payment_status=0 AND payment_paid=0 ";
			$db->query($sql);
		}
		$db->query("DELETE FROM " . $table_prefix . "orders_properties WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "orders_shipments WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "orders_coupons WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "orders_items WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "orders_items_properties WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "users_commissions WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ") AND payment_id=0");
		$db->query("DELETE FROM " . $table_prefix . "users_points WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "users_credits WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")");

		foreach ($items as $item_id => $quantity) {
			$sql  = " UPDATE " . $table_prefix . "items SET ";
			$sql .= " stock_level=stock_level+" . $db->tosql($quantity, INTEGER);
			$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
			$sql .= " AND use_stock_level=1 ";
			$db->query($sql);
		}
		foreach ($saved_items as $cart_item_id => $quantity) {
			$sql  = " UPDATE " . $table_prefix . "saved_items SET ";
			$sql .= " quantity_bought=quantity_bought-" . $db->tosql($quantity, INTEGER);
			$sql .= " WHERE cart_item_id=" . $db->tosql($cart_item_id, INTEGER);
			$db->query($sql);
		}
		foreach ($serials as $item_id => $serial_numbers) {
			$sql  = " UPDATE " . $table_prefix . "items_serials SET ";
			$sql .= " used=0 ";
			$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
			$sql .= " AND serial_number IN (" . $serial_numbers . ") ";
			$db->query($sql);
		}
		foreach ($options_values_ids as $value_id => $quantity) {
			$sql  = " UPDATE " . $table_prefix . "items_properties_values SET ";
			$sql .= " stock_level=stock_level+" . $db->tosql($quantity, INTEGER);
			$sql .= " WHERE item_property_id=" . $db->tosql($value_id, INTEGER);
			$sql .= " AND use_stock_level=1 ";
			$db->query($sql);
		}
		$user_info = get_session("session_user_info");
		$session_user_id = get_setting_value($user_info, "user_id", 0);
		// update users points
		foreach ($users_points as $user_id => $user_id) {
			$sql  = " SELECT SUM(points_action * points_amount) ";
			$sql .= " FROM " . $table_prefix . "users_points ";
			$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
			$total_points_sum = get_db_value($sql);

			$sql  = " UPDATE " . $table_prefix . "users ";
			$sql .= " SET total_points=" . $db->tosql($total_points_sum, NUMBER);
			$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
			$db->query($sql);

			// update user information in session if available
			$session_total_points = get_setting_value($user_info, "total_points", 0);
			if ($session_user_id == $user_id && $total_points_sum != $session_total_points) {
				$user_info["total_points"] = $total_points_sum;
				set_session("session_user_info", $user_info);
			}
		}
		// update user credits balance
		foreach ($users_credits as $user_id => $user_id) {
			$sql  = " SELECT SUM(credit_action * credit_amount) ";
			$sql .= " FROM " . $table_prefix . "users_credits ";
			$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
			$total_credit_sum = get_db_value($sql);

			$sql  = " UPDATE " . $table_prefix . "users ";
			$sql .= " SET credit_balance=" . $db->tosql($total_credit_sum, NUMBER);
			$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
			$db->query($sql);

			// update user credit balance information in session if available
			$session_credit_balance = get_setting_value($user_info, "credit_balance", 0);
			if ($session_user_id == $user_id && $total_credit_sum != $session_credit_balance) {
				$user_info["credit_balance"] = $total_credit_sum;
				set_session("session_user_info", $user_info);
			}
		}
		// update coupons number
		foreach ($coupons as $coupon_id => $coupon_data) {
			$coupon_used = $coupon_data["used"];
			$coupon_type = $coupon_data["type"];
			$discount_amount = $coupon_data["amount"];
			$sql  = " UPDATE " . $table_prefix . "coupons SET ";
			$sql .= " coupon_uses=coupon_uses-" . $db->tosql($coupon_used, INTEGER);
			if ($coupon_type == 5 || $coupon_type == 8) {
				// back voucher amount if order with voucher was deleted
				$sql .= ", discount_amount=discount_amount+".$db->tosql($discount_amount, NUMBER);
			}
			$sql .= " WHERE coupon_id=" . $db->tosql($coupon_id, INTEGER);
			$db->query($sql);
		}

		$db->query("DELETE FROM " . $table_prefix . "orders_notes WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "orders_items_serials WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "orders_serials_activations WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "coupons WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "coupons_events WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "coupons_events WHERE coupon_id IN (" . $db->tosql($coupons_ids, INTEGERS_LIST) . ")");
		if (strlen($downloads_ids)) {
			$db->query("DELETE FROM " . $table_prefix . "items_downloads WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")");
			$db->query("DELETE FROM " . $table_prefix . "items_downloads_statistic WHERE download_id IN (" . $db->tosql($downloads_ids, INTEGERS_LIST) . ")");
		}
		if (strlen($order_tax_ids)) {
			$db->query("DELETE FROM " . $table_prefix . "orders_taxes WHERE order_id IN (" . $db->tosql($orders_ids, INTEGERS_LIST) . ")");
			$db->query("DELETE FROM " . $table_prefix . "orders_items_taxes WHERE order_tax_id IN (" . $db->tosql($order_tax_ids, INTEGERS_LIST) . ")");
		}
	}

	function get_payment_rate($payment_id, $currency)
	{
		global $db, $table_prefix;

		$payment_currency = $currency;
		$sql  = " SELECT parameter_type,parameter_source FROM " . $table_prefix . "payment_parameters ";
		$sql .= " WHERE payment_id=" . $db->tosql($payment_id, INTEGER);
		$sql .= " AND parameter_name IN ('currency_code', 'x_currency_code', 'currency', 'currencycode', 'currencyid', 'itransfer_order_currency_ident') ";
		$db->query($sql);
		if ($db->next_record()) {
			$parameter_type = $db->f("parameter_type");
			$parameter_source = trim($db->f("parameter_source"));
			if ($parameter_source == "currency_code" || $parameter_source == "{currency_code}"
				|| $parameter_source == "currency_value" || $parameter_source == "{currency_value}") {
				$payment_currency = $currency;
			} else {
				$sql  = " SELECT * FROM " . $table_prefix . "currencies ";
				$sql .= " WHERE currency_code=" . $db->tosql($parameter_source, TEXT);
				$sql .= " OR currency_value=" . $db->tosql($parameter_source, TEXT);
				$db->query($sql);
				if ($db->next_record()) {
					$decimals_number = $db->f("decimals_number");
					$decimal_point = $db->f("decimal_point");
					if (!strlen($decimals_number)) { $decimals_number = 2; }
					if (!strlen($decimal_point)) { $decimal_point = "."; }
					$payment_currency["code"] = $db->f("currency_code");
					$payment_currency["value"] = $db->f("currency_value");
					$payment_currency["left"] = $db->f("symbol_left");
					$payment_currency["right"] = $db->f("symbol_right");
					$payment_currency["rate"] = $db->f("exchange_rate");
					$payment_currency["decimals"] = intval($decimals_number);
					$payment_currency["point"] = $decimal_point;
					$payment_currency["separator"] = $db->f("thousands_separator");
				}
			}
		}
		return $payment_currency;
	}

	function generate_serial($order_item_id, $sn, $product_info, $generation_type = 1)
	{
		global $db, $table_prefix, $site_id, $settings, $t;

		$serial_number = "";
		if ($generation_type == 1) {
			// random generation
			while ($serial_number == "")
			{
				$random_value  = mt_rand();
				$serial_hash   = strtoupper(md5($order_item_id . $sn . $random_value . va_timestamp()));
				$serial_number = substr($serial_hash,0,4)."-".substr($serial_hash,4,4)."-".substr($serial_hash,8,4)."-".substr($serial_hash,12,4);
				$sql = " SELECT serial_id FROM " .$table_prefix. "orders_items_serials WHERE serial_number=" . $db->tosql($serial_number, TEXT);
				$db->query($sql);
				if ($db->next_record()) {
					$serial_number = "";
				}
			}
		} elseif ($generation_type == 2) {
			// get from predefined list
			$item_id = $product_info["item_id"];
			$sql  = " SELECT serial_id, serial_number FROM " . $table_prefix . "items_serials ";
			$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
			$sql .= " AND (used=0 OR used IS NULL) ";
			$db->RecordsPerPage = 1;
			$db->PageNumber = 1;
			$db->query($sql);
			if ($db->next_record()) {
				$serial_id = $db->f("serial_id");
				$serial_number = $db->f("serial_number");
				$sql  = " UPDATE " . $table_prefix . "items_serials SET used=1 ";
				$sql .= " WHERE serial_id=" . $db->tosql($serial_id, INTEGER);
				$db->query($sql);
			}

			// calculate number of left serial numbers
			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "items_serials ";
			$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
			$sql .= " AND (used=0 OR used IS NULL) ";
			$sn_left = get_db_value($sql);

			// check site_id to get download settings
			if (!isset($site_id) || !$site_id) {
				$site_id = 1;
			}
			$download_info = array();
			$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type='download_info' ";
			$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER) . ")";
			$sql .= " ORDER BY site_id ASC ";
			$db->query($sql);
			while ($db->next_record()) {
				$download_info[$db->f("setting_name")] = $db->f("setting_value");
			}

			$sn_limit = get_setting_value($download_info, "sn_limit", "");
			$sn_limit_admin_notify = get_setting_value($download_info, "sn_limit_admin_notify", "0");

			if ($sn_limit_admin_notify && $sn_limit && $sn_limit >= $sn_left) {
				$eol = get_eol();
				$mail_to = get_setting_value($download_info, "sn_limit_to", $settings["admin_email"]);
	
				$email_headers = array();
				$email_headers["from"] = get_setting_value($download_info, "sn_limit_from", $settings["admin_email"]);
				$email_headers["cc"] = get_setting_value($download_info, "sn_limit_cc");
				$email_headers["bcc"] = get_setting_value($download_info, "sn_limit_bcc");
				$email_headers["reply_to"] = get_setting_value($download_info, "sn_limit_reply_to");
				$email_headers["return_path"] = get_setting_value($download_info, "sn_limit_return_path");
				$mail_type = get_setting_value($download_info, "sn_limit_message_type", 0);
				$email_headers["mail_type"] = $mail_type;
				$mail_subject = get_translation(get_setting_value($download_info, "sn_limit_subject", ""));
				$mail_body = get_translation(get_setting_value($download_info, "sn_limit_message", ""));

				$t->set_var("sn_left", $sn_left);
				$t->set_vars($product_info);
				$t->set_block("mail_subject", $mail_subject);
				$t->set_block("mail_body", $mail_body);
				$t->parse("mail_subject", false);
				$t->parse("mail_body", false);
				$mail_body = preg_replace("/\r\n|\r|\n/", $eol, $t->get_var("mail_body"));
				va_mail($mail_to, $t->get_var("mail_subject"), $mail_body, $email_headers);
			}
		}

		return $serial_number;
	}

	function generate_voucher($order_id, $order_item_id, $user_id, $voucher_name, $voucher_price, $voucher_type = 5)
	{
		global $db, $table_prefix;

		if (!$voucher_type) { $voucher_type = 5; }

		$voucher_code = "";
		while ($voucher_code == "") {
			$random_value = mt_rand();
			$voucher_hash = strtoupper(md5($order_id . $order_item_id . $voucher_price . $random_value . va_timestamp()));
			$voucher_code = substr($voucher_hash, 0, 8);
			$sql = " SELECT coupon_id FROM " .$table_prefix. "coupons WHERE coupon_code=" . $db->tosql($voucher_code, TEXT);
			$db->query($sql);
			if ($db->next_record()) {
				$voucher_code = "";
			}
		}

		$vr = new VA_Record($table_prefix . "coupons");
		$vr->add_textbox("order_id", INTEGER);
		$vr->add_textbox("order_item_id", INTEGER);
		$vr->add_textbox("user_id", INTEGER);
		$vr->add_textbox("coupon_code", TEXT);
		$vr->add_textbox("coupon_title", TEXT);
		$vr->add_textbox("is_active", INTEGER);
		$vr->add_textbox("discount_type", INTEGER);
		$vr->add_textbox("discount_amount", NUMBER);
		$vr->add_textbox("quantity_limit", INTEGER);
		$vr->add_textbox("coupon_uses", INTEGER);

		$vr->set_value("order_id", $order_id);
		$vr->set_value("order_item_id", $order_item_id);
		if ($voucher_type == 8) {
			$vr->set_value("user_id", $user_id);
		}
		$vr->set_value("coupon_code", $voucher_code);
		$vr->set_value("coupon_title", $voucher_name);
		$vr->set_value("is_active", 0);
		$vr->set_value("discount_type", $voucher_type);
		$vr->set_value("discount_amount", $voucher_price);
		$vr->set_value("quantity_limit", 0);
		$vr->set_value("coupon_uses", 0);

		$vr->insert_record();
		$coupon_id = $db->last_insert_id();

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

		$ce->set_value("coupon_id", $coupon_id);
		$ce->set_value("order_id", $order_id);
		$ce->set_value("payment_id", "");
		$ce->set_value("transaction_id", "");
		$ce->set_value("admin_id", get_session("session_admin_id"));
		$ce->set_value("user_id", get_session("session_user_id"));
		$ce->set_value("event_date", va_time());
		$ce->set_value("event_type", "voucher_added");
		$ce->set_value("remote_ip", get_ip());
		$ce->set_value("coupon_amount", $voucher_price);
		$ce->insert_record();


		return $voucher_code;
	}

	// calculate fingerprint for Authorize.net
	function calculate_fp ($login_id, $trankey, $amount, $sequence, $timestamp, $currency = "")
	{
  	return (hmac_md5 ($login_id."^".$sequence."^".$timestamp."^".$amount."^".$currency, $trankey));
	}


	function get_final_message($message, $message_type)
	{
		$message_type = str_replace("/", "\/", $message_type);
		$message = preg_replace("/\[" . $message_type . "\]/si", "", $message);
		$message = preg_replace("/\[\/" . $message_type . "\]/si", "", $message);
		$message = preg_replace("/\[success].*\[\/success]/s", "", $message);
		$message = preg_replace("/\[pending].*\[\/pending]/s", "", $message);
		$message = preg_replace("/\[failure].*\[\/failure]/s", "", $message);

		return $message;
	}

	function clean_cc_number($cc_number)
	{
		return preg_replace("/[^0-9]+/", "", $cc_number);
	}

	function format_cc_number($cc_number, $delimiter = "-", $hide_first = false)
	{
		$cc_formatted = "";
		$cc_number = preg_replace("/[\s\-]/", "", $cc_number);
		$total_digit = strlen($cc_number);
		if ($total_digit) {
			for ($i = 0; $i < $total_digit; $i++) {
				if ($i && $i % 4 == 0) {
					$cc_formatted .= $delimiter;
				}
				if ($hide_first && ($i + 4) < $total_digit) {
					$cc_formatted .= "*";
				} else {
					$cc_formatted .= $cc_number[$i];
				}
			}
		}
		return $cc_formatted;
	}

	function check_cc_number($cc_number)
	{
		$cc_number = strrev (clean_cc_number($cc_number));

		$digits = ""; $sum = 0;
		// Loop through the number one digit at a time
		// Double the value of every second digit (starting from the right)
		// Concatenate the new values with the unaffected digits
		for ($i = 0; $i < strlen ($cc_number); ++$i) {
			$digits .= ($i % 2) ? $cc_number[$i] * 2 : $cc_number[$i];
		}

		// Add all of the single digits together
		for ($i = 0; $i < strlen ($digits); ++$i) {
			$sum += $digits[$i];
		}

		// Valid card numbers will be transformed into a multiple of 10
		return ($sum % 10) ? false : true;
	}

	function get_expecting_date($handle_hours)
	{
		$expecting_date = va_timestamp();
		// add one day if today is Sunday
		if (date("w", $expecting_date) == 0) {
			$expecting_date += 86400;
		}
		while ($handle_hours > 0) {
			if ($handle_hours < 24) {
				$expecting_date += $handle_hours * 3600;
			} else {
				$expecting_date += 86400;
			}
			$handle_hours -= 24;
			if (date("w", $expecting_date) == 0) {
				$expecting_date += 86400;
			}
		}

		return $expecting_date;
	}

	function get_commission($item_user_id, $affiliate_user_id, $price, $options_price, $buying_price, $item_commision_type, $item_commision_amount)
	{
		global $db, $table_prefix, $settings;
		$item_commissions = 0;
		if ($item_user_id || $affiliate_user_id) {
			$commission_type = ""; $commission_amount = 0;
			if (strlen($item_commision_type)) {
				$commission_type = $item_commision_type;
				$commission_amount = $item_commision_amount;
			} else {
				if ($item_user_id) {
					$sql  = " SELECT u.merchant_fee_type AS user_commision_type, u.merchant_fee_amount AS user_commision_amount, ";
					$sql .= " ut.merchant_fee_type AS type_commision_type, ut.merchant_fee_amount AS type_commision_amount ";
				} else {
					$sql  = " SELECT u.affiliate_commission_type AS user_commision_type, u.affiliate_commission_amount AS user_commision_amount, ";
					$sql .= " ut.affiliate_commission_type AS type_commision_type, ut.affiliate_commission_amount AS type_commision_amount ";
				}
				$sql .= " FROM (" . $table_prefix . "users u ";
				$sql .= " LEFT JOIN " . $table_prefix . "user_types ut ON u.user_type_id=ut.type_id) ";
				if ($item_user_id) {
					$sql .= " WHERE u.user_id=" . $db->tosql($item_user_id, INTEGER);
				} else {
					$sql .= " WHERE u.user_id=" . $db->tosql($affiliate_user_id, INTEGER);
				}
				$db->query($sql);
				if ($db->next_record()) {
					$user_commision_type = $db->f("user_commision_type");
					$user_commision_amount = $db->f("user_commision_amount");
					$type_commision_type = $db->f("type_commision_type");
					$type_commision_amount = $db->f("type_commision_amount");
					if (strlen($user_commision_type)) {
						$commission_type = $user_commision_type;
						$commission_amount = $user_commision_amount;
					} elseif (strlen($type_commision_type)) {
						$commission_type = $type_commision_type;
						$commission_amount = $type_commision_amount;
					} else { // check global products commissions
						if ($item_user_id) {
							$commission_type = get_setting_value($settings, "merchant_fee_type", "");
							$commission_amount = get_setting_value($settings, "merchant_fee_amount", 0);
						} else {
							$commission_type = get_setting_value($settings, "affiliate_commission_type", "");
							$commission_amount = get_setting_value($settings, "affiliate_commission_amount", 0);
						}
					}
				}
			}
			if ($commission_type == 1) { // percentage to the whole price
				$item_commissions = round((($price + $options_price) * $commission_amount) / 100, 2);
			} elseif ($commission_type == 2) { // fixed amount
				$item_commissions = $commission_amount;
			} elseif ($commission_type == 3) { // percentage to the product price
				$item_commissions = round(($price * $commission_amount) / 100, 2);
			} elseif ($commission_type == 4) { // percentage to the margin price
				$item_commissions = round((($price + $options_price - $buying_price) * $commission_amount) / 100, 2);
			}
			if ($item_commissions < 0) { $item_commissions = 0; }
		}
		if ($item_user_id) {
			// for merchant subtract fees to get commision
			$item_commissions = $price + $options_price - $item_commissions;
		}
		return $item_commissions;
	}

	function get_merchant_commission($item_user_id, $price, $options_price, $buying_price, $item_commision_type, $item_commision_amount)
	{
		return get_commission($item_user_id, "", $price, $options_price, $buying_price, $item_commision_type, $item_commision_amount);
	}

	function get_affiliate_commission($affiliate_user_id, $price, $options_price, $buying_price, $item_commision_type, $item_commision_amount)
	{
		return get_commission("", $affiliate_user_id, $price, $options_price, $buying_price, $item_commision_type, $item_commision_amount);
	}

	function get_payment_parameters($order_id, &$payment_parameters, &$pass_parameters, &$post_parameters, &$pass_data, &$variables, $order_step = "", $params_transform = "lowercase")
	{
		global $db, $table_prefix, $settings;
		global $parameters, $cc_parameters;
		global $datetime_show_format, $cart_items, $total_items;

		include_once(dirname(__FILE__)."/parameters.php");

		// get user info
		$user_info = get_session("session_user_info");
		$user_login = get_setting_value($user_info, "login", "");

		// get orders variables
		//$items_text = show_order_items($order_id, false);
		$order_tax_rates = order_tax_rates($order_id);
		$orders_currency = get_setting_value($settings, "orders_currency", 0);
		$global_tax_round = get_setting_value($settings, "tax_round", 1);
		$global_tax_prices_type = get_setting_value($settings, "tax_prices_type", 0);

		$variables = array();
		$variables["charset"] = CHARSET;
		$variables["order_id"] = $order_id;
		$variables["session_id"] = session_id();
		$variables["remote_address"] = get_ip();
		$variables["site_url"] = get_setting_value($settings, "site_url", "");
		$variables["secure_url"] = get_setting_value($settings, "secure_url", $variables["site_url"]); 
		$variables["user_login"] = $user_login ;
		$payment_rate_total = 0;  // special variable to compare with $order_rate_total variable
		$payment_items = array(); // save here data we will pass to payment system

		// get order data
		$sql = "SELECT * FROM " . $table_prefix . "orders WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {

			$tax_prices_type = $db->f("tax_prices_type");

			$variables["user_ip"] = $db->f("remote_address");
			$variables["order_ip"] = $db->f("remote_address");
			$variables["initial_ip"] = $db->f("initial_ip");
			$variables["cookie_ip"] = $db->f("cookie_ip");

			$variables["transaction_id"] = $db->f("transaction_id");
			$variables["authorization_code"] = $db->f("authorization_code");

			// AVS data
			$variables["avs_response_code"] = $db->f("avs_response_code");
			$variables["avs_message"] = $db->f("avs_message");
			$variables["avs_address_match"] = $db->f("avs_address_match");
			$variables["avs_zip_match"] = $db->f("avs_zip_match");
			$variables["cvv2_match"] = $db->f("cvv2_match");

			// 3d fields
			$variables["secure_3d_check"] = $db->f("secure_3d_check");
			$variables["secure_3d_status"] = $db->f("secure_3d_status");
			$variables["secure_3d_md"] = $db->f("secure_3d_md");
			$variables["secure_3d_eci"] = $db->f("secure_3d_eci");
			$variables["secure_3d_cavv"] = $db->f("secure_3d_cavv");
			$variables["secure_3d_xid"] = $db->f("secure_3d_xid");
			$variables["authorization_code"] = $db->f("authorization_code");

			for ($i = 0; $i < sizeof($parameters); $i++) {
				if (in_array($parameters[$i], array("company_name", "province"))) {
					$variables[$parameters[$i]] = get_translation($db->f($parameters[$i]));
					$variables["delivery_" . $parameters[$i]] = get_translation($db->f("delivery_" . $parameters[$i]));
				} else {
					$variables[$parameters[$i]] = $db->f($parameters[$i]);
					$variables["delivery_" . $parameters[$i]] = $db->f("delivery_" . $parameters[$i]);
				}
			}

			for ($i = 0; $i < sizeof($cc_parameters); $i++) {
				$variables[$cc_parameters[$i]] = $db->f($cc_parameters[$i]);
			}

			prepare_user_name($variables["name"], $variables["first_name"], $variables["last_name"]);
			prepare_user_name($variables["delivery_name"], $variables["delivery_first_name"], $variables["delivery_last_name"]);
			prepare_user_name($variables["cc_name"], $variables["cc_first_name"], $variables["cc_last_name"]);

			$address = $variables["address2"] ? ($variables["address1"] . " " . $variables["address2"]) : $variables["address1"];
			$delivery_address = $variables["delivery_address2"] ? ($variables["delivery_address1"] . " " . $variables["delivery_address2"]) : $variables["delivery_address1"];
			$address_number = (preg_match("/\d+/", $address, $match)) ? $match[0] : "";
			$delivery_address_number = (preg_match("/\d+/", $delivery_address, $match)) ? $match[0] : "";
			$variables["address"] = $address;
			$variables["address_number"] = $address_number;
			$variables["delivery_address"] = $delivery_address;
			$variables["delivery_address_number"] = $delivery_address_number;

			$order_placed_date = $db->f("order_placed_date", DATETIME);
			$cc_start_date = $db->f("cc_start_date", DATETIME);
			$cc_expiry_date = $db->f("cc_expiry_date", DATETIME);

			$opd_timestamp = mktime($order_placed_date[HOUR], $order_placed_date[MINUTE], $order_placed_date[SECOND], $order_placed_date[MONTH], $order_placed_date[DAY], $order_placed_date[YEAR]);
			$vc = md5($order_id . $order_placed_date[HOUR] . $order_placed_date[MINUTE] . $order_placed_date[SECOND]);

			$payment_id = $db->f("payment_id");
			$user_id = $db->f("user_id");
			$affiliate_code = $db->f("affiliate_code");
			$order_currency_code = $db->f("currency_code");
			$order_currency_rate = $db->f("currency_rate");
			$payment_currency_code = $db->f("payment_currency_code");
			$payment_currency_rate = $db->f("payment_currency_rate");
			if (!strlen($payment_currency_code)) {
				$payment_currency_code = $order_currency_code;
				$payment_currency_rate = $order_currency_rate;
			}

			$goods_total = $db->f("goods_total");
			$goods_tax = $db->f("goods_tax");
			$goods_incl_tax = $db->f("goods_incl_tax");
			$total_discount = $db->f("total_discount");
			$total_discount_tax = $db->f("total_discount_tax");
			$properties_total = $db->f("properties_total");
			$properties_taxable = $db->f("properties_taxable");
			// old way shipping data
			$old_shipping_type_desc = $db->f("shipping_type_desc");
			$old_shipping_cost = $db->f("shipping_cost");
			$old_shipping_taxable = $db->f("shipping_taxable");
			// end old way shipping data
			$total_quantity = $db->f("total_quantity");
			$weight_total = $db->f("weight_total");
			$actual_weight_total = $db->f("actual_weight_total");
			$tax_name = get_translation($db->f("tax_name"));
			$tax_percent = $db->f("tax_percent");
			$tax_cost = $db->f("tax_total");
			$processing_fee = $db->f("processing_fee");
			$processing_tax_free = $db->f("processing_tax_free");
			$credit_amount = $db->f("credit_amount");
			$order_total = $db->f("order_total");
			$tax_prices_type = $db->f("tax_prices_type");
			if (!strlen($tax_prices_type)) {
				$tax_prices_type = $global_tax_prices_type;
			}
			$tax_round = $db->f("tax_round_type");
			if (!strlen($tax_round)) {
				$tax_round = $global_tax_round;
			}

			if($tax_prices_type == 1){
				$goods_excl_tax = $goods_total - $goods_tax;
				$goods_incl_tax = $goods_total;
			}else{
				$goods_excl_tax = $goods_total;
				$goods_incl_tax = $goods_total + $goods_tax;
			}
			if($tax_prices_type == 1){
				$total_discount_excl_tax = $total_discount - $total_discount_tax;
				$total_discount_incl_tax = $total_discount;
			}else{
				$total_discount_excl_tax = $total_discount;
				$total_discount_incl_tax = $total_discount + $total_discount_tax;
			}
			// get numeric code
			$order_currency = get_currency($order_currency_code);
			$order_currency_value = $order_currency["value"];
			$order_currency_decimals = $order_currency["decimals"];

			// get currency rate for the selected gateway
			$payment_currency = get_currency($payment_currency_code, false);
			$payment_currency["rate"] = $payment_currency_rate;
			$payment_currency_value = $payment_currency["value"];
			$payment_decimals = $payment_currency["decimals"];
			$payment_rate = $payment_currency_rate;
			// calculate order total with payment rate
			$order_rate_total = round($order_total * $payment_rate, $payment_decimals);

			$variables["vc"] = $vc;
			$variables["timestamp"] = time();
			$variables["va_timestamp"] = va_timestamp();
			$variables["server_timestamp"] = time();
			$variables["order_placed_timestamp"] = $opd_timestamp;
			$variables["order_placed_date"] = va_date($datetime_show_format, $order_placed_date);
			$variables["cc_start_date"] = ""; $variables["cc_start_date_short"] = "";
			$variables["cc_start_year"] = ""; $variables["cc_start_yyyy"] = ""; $variables["cc_start_month"] = "";
			if (is_array($cc_start_date)) {
				$variables["cc_start_date"] = va_date(array("MM"," / ","YYYY"), $cc_start_date);
				$variables["cc_start_date_short"] = va_date(array("MM"," / ","YY"), $cc_start_date);
				$variables["cc_start_year"] = va_date(array("YY"), $cc_start_date);
				$variables["cc_start_yyyy"] = va_date(array("YYYY"), $cc_start_date);
				$variables["cc_start_month"] = va_date(array("MM"), $cc_start_date);
			}
			$variables["cc_expiry_date"] = ""; $variables["cc_expiry_date_short"] = "";
			$variables["cc_expiry_year"] = ""; $variables["cc_expiry_yyyy"] = ""; $variables["cc_expiry_month"] = "";
			if (is_array($cc_expiry_date)) {
				$variables["cc_expiry_date"] = va_date(array("MM"," / ","YYYY"), $cc_expiry_date);
				$variables["cc_expiry_date_short"] = va_date(array("MM"," / ","YY"), $cc_expiry_date);
				$variables["cc_expiry_year"] = va_date(array("YY"), $cc_expiry_date);
				$variables["cc_expiry_yyyy"] = va_date(array("YYYY"), $cc_expiry_date);
				$variables["cc_expiry_month"] = va_date(array("MM"), $cc_expiry_date);
			}

			$variables["user_id"] = $user_id;
			$variables["affiliate_code"] = $affiliate_code;
			$variables["order_currency_code"] = $order_currency_code;
			$variables["order_currency_value"] = $order_currency_value;
			$variables["order_currency_rate"] = $order_currency_rate;
			$variables["currency_code"] = $payment_currency_code;
			$variables["currency_value"] = $payment_currency_value;
			$variables["currency_rate"] = $payment_currency_rate;
			$variables["payment_currency_code"] = $payment_currency_code;
			$variables["payment_currency_rate"] = $payment_currency_rate;
			$variables["goods_total"] = number_format($goods_total * $payment_rate, $payment_decimals, ".", "");
			$variables["goods_excl_tax"] = number_format($goods_excl_tax * $payment_rate, $payment_decimals, ".", "");
			$variables["goods_tax"] = number_format($goods_tax * $payment_rate, $payment_decimals, ".", "");
			$variables["goods_incl_tax"] = number_format($goods_incl_tax * $payment_rate, $payment_decimals, ".", "");

			$goods_with_discount = $goods_total - $total_discount;
			$variables["goods_with_discount"] = number_format($goods_with_discount * $payment_rate, $payment_decimals, ".", "");
			$variables["total_discount"] = $total_discount;
			$variables["total_quantity"] = $total_quantity;
			$variables["weight_total_2"] = number_format($weight_total, 2);
			$variables["total_weight_2"] = number_format($weight_total, 2);
			$variables["weight_total"] = $weight_total;
			$variables["total_weight"] = $weight_total;
			$variables["actual_weight_total"] = $actual_weight_total;
			$variables["total_actual_weight"] = $actual_weight_total;
			$variables["properties_total"] = number_format($properties_total * $payment_rate, $payment_decimals, ".", "");
			$variables["properties_taxable"] = $properties_taxable;
			$variables["tax_name"] = $tax_name;
			$variables["tax_percent"] = $tax_percent;
			$variables["tax_cost"] = $tax_cost;
			$variables["processing_fee"] = number_format($processing_fee * $payment_rate, $payment_decimals, ".", "");
			$variables["processing_fee_excl_tax"] = number_format($processing_fee * $payment_rate, $payment_decimals, ".", "");
			$variables["processing_fee_tax"] = number_format(0 * $payment_rate, $payment_decimals, ".", "");
			$variables["processing_fee_incl_tax"] = number_format($processing_fee * $payment_rate, $payment_decimals, ".", "");
			$variables["credit_amount"] = number_format($credit_amount * $payment_rate, $payment_decimals, ".", "");
			$variables["order_total"] = number_format($order_total * $payment_rate, $payment_decimals, ".", "");
			$variables["order_total_100"] = round($order_total * $payment_rate * 100, 0);

			$variables["company_select"] = get_translation(get_db_value("SELECT company_name FROM " . $table_prefix . "companies WHERE company_id=" . $db->tosql($variables["company_id"], INTEGER, true, false)));
			$variables["state"] = ""; $variables["state_code"] = ""; 
			$sql = "SELECT * FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($variables["state_id"], INTEGER, true, false);
			$db->query($sql);
			if ($db->next_record()) {
				$variables["state"] = get_translation($db->f("state_name"));
				$variables["state_code"] = $db->f("state_code");
			}
			if (strlen($variables["state_code"])) {
				$variables["state_code_or_province"] = $variables["state_code"];
				$variables["state_or_province"] = $variables["state"];
			} else {
				$variables["state_code_or_province"] = $variables["province"];
				$variables["state_or_province"] = $variables["province"];
			}
			$variables["country"] = ""; $variables["country_code"] = ""; 
			$variables["country_number"] = ""; $variables["country_code_alpha3"] = "";
			$sql = "SELECT * FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($variables["country_id"], INTEGER, true, false);
			$db->query($sql);
			if ($db->next_record()) {
				$variables["country"] = get_translation($db->f("country_name"));
				$variables["country_code"] = $db->f("country_code");
				$variables["country_number"] = $db->f("country_iso_number");
				$variables["country_code_alpha3"] = $db->f("country_code_alpha3");
			}
			$variables["delivery_company_select"] = get_translation(get_db_value("SELECT company_name FROM " . $table_prefix . "companies WHERE company_id=" . $db->tosql($variables["delivery_company_id"], INTEGER, true, false)));
			$variables["delivery_state"] = ""; $variables["delivery_state_code"] = ""; 
			$sql = "SELECT * FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($variables["delivery_state_id"], INTEGER, true, false);
			$db->query($sql);
			if ($db->next_record()) {
				$variables["delivery_state"] = get_translation($db->f("state_name"));
				$variables["delivery_state_code"] = $db->f("state_code");
			}
			if (strlen($variables["delivery_state_code"])) {
				$variables["delivery_state_code_or_province"] = $variables["delivery_state_code"];
				$variables["delivery_state_or_province"] = $variables["delivery_state"];
			} else {
				$variables["delivery_state_code_or_province"] = $variables["delivery_province"];
				$variables["delivery_state_or_province"] = $variables["delivery_province"];
			}
			$variables["delivery_country"] = ""; $variables["delivery_country_code"] = ""; 
			$variables["delivery_country_number"] = ""; $variables["delivery_country_code_alpha3"] = "";
			$sql = "SELECT * FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($variables["delivery_country_id"], INTEGER, true, false);
			$db->query($sql);
			if ($db->next_record()) {
				$variables["delivery_country"] = get_translation($db->f("country_name"));
				$variables["delivery_country_code"] = $db->f("country_code");
				$variables["delivery_country_number"] = $db->f("country_iso_number");
				$variables["delivery_country_code_alpha3"] = $db->f("country_code_alpha3");
			}
			$variables["cc_type"] = get_db_value("SELECT credit_card_code FROM " . $table_prefix . "credit_cards WHERE credit_card_id=" . $db->tosql($variables["cc_type"], INTEGER, true, false));//, INTEGER));

			$cc_info = array();
			$setting_type = "credit_card_info_" . $payment_id;
			$sql = "SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings WHERE setting_type=" . $db->tosql($setting_type, TEXT);
			$db->query($sql);
			while ($db->next_record()) {
				$cc_info[$db->f("setting_name")] = $db->f("setting_value");
			}
			$cc_number_security = get_setting_value($cc_info, "cc_number_security", 1);
			$cc_code_security = get_setting_value($cc_info, "cc_code_security", 1);
			if ($order_step == "recurring") {
				if ($cc_number_security > 0) {
					$variables["cc_number"] = va_decrypt($variables["cc_number"]);
				}
				if ($cc_code_security > 0) {
					$variables["cc_security_code"] = va_decrypt($variables["cc_security_code"]);
				}
			} else if ($order_step == "opc") {
				$variables["cc_number"] = clean_cc_number(get_param("cc_number"));
				$variables["cc_security_code"] = get_param("cc_security_code");
			} else {
				$variables["cc_number"] = get_session("session_cc_number");
				$variables["cc_security_code"] = get_session("session_cc_code");
			}
			$cc_number_len = strlen($variables["cc_number"]);
			if ($cc_number_len > 6) {
				$variables["cc_number_first"] = substr($variables["cc_number"], 0, 6);
			} else {
				$variables["cc_number_first"] = $variables["cc_number"];
			}
			if ($cc_number_len > 4) {
				$variables["cc_number_last"] = substr($variables["cc_number"], $cc_number_len - 4);
			} else {
				$variables["cc_number_last"] = $variables["cc_number"];
			}

			// #1. get items for order
			$order_items = array(); $total_quantity = 0; $total_items = 0;
			$sql  = " SELECT oi.order_item_id,oi.top_order_item_id,oi.item_id,oi.item_user_id,oi.item_type_id,";
			$sql .= " oi.item_status,oi.item_code,oi.manufacturer_code,oi.item_name, ";
			$sql .= " oi.is_recurring, oi.recurring_last_payment, oi.recurring_next_payment, oi.downloadable, ";
			$sql .= " oi.price,oi.tax_id, oi.tax_free,oi.tax_percent,oi.discount_amount,oi.real_price, oi.weight, oi.actual_weight, ";
			$sql .= " oi.buying_price,oi.points_price,oi.reward_points,oi.reward_credits,oi.quantity, ";
			$sql .= " oi.is_shipping_free, oi.shipping_cost ";
			$sql .= " FROM " . $table_prefix . "orders_items oi ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$total_items++;
				$order_item_id = $db->f("order_item_id");
				$top_order_item_id = $db->f("top_order_item_id");
	  
				$item_id = $db->f("item_id");
				$item_type_id = $db->f("item_type_id");
				$item_name = get_translation($db->f("item_name"));
				$item_code = $db->f("item_code");
				$manufacturer_code = $db->f("manufacturer_code");
				$is_recurring = $db->f("is_recurring");
				$recurring_last_payment = $db->f("recurring_last_payment", DATETIME);
				$recurring_next_payment = $db->f("recurring_next_payment", DATETIME);

				$price = $db->f("price");
				$quantity = $db->f("quantity");
				$item_tax_id = $db->f("tax_id");
				$tax_free = $db->f("tax_free");
				$item_tax_percent = $db->f("tax_percent");
				if (!strlen($item_tax_percent)) {
					$item_tax_percent = $tax_percent;
				}
	  
				$total_quantity += $quantity;
				$item_total = $price * $quantity;
	  
				$item_tax = get_tax_amount($order_tax_rates, $item_type_id, $price, 1, $item_tax_id, $tax_free, $item_tax_percent, "", 1, $tax_prices_type, $tax_round);
				$item_tax_total_values = get_tax_amount($order_tax_rates, $item_type_id, $item_total, $quantity, $item_tax_id, $tax_free, $item_tax_percent, "", 2, $tax_prices_type, $tax_round);
				$item_tax_total = add_tax_values($order_tax_rates, $item_tax_total_values, "products", $tax_round);

				if ($tax_prices_type == 1) {
					$price_excl_tax = $price - $item_tax;
					$price_incl_tax = $price;
					$price_excl_tax_total = $item_total - $item_tax_total;
					$price_incl_tax_total = $item_total;
				} else {
					$price_excl_tax = $price;
					$price_incl_tax = $price + $item_tax;
					$price_excl_tax_total = $item_total;
					$price_incl_tax_total = $item_total + $item_tax_total;
				}
				$order_items[$order_item_id] = array(
					"top_order_item_id" => $top_order_item_id,
					"item_id" => $db->f("item_id"), "item_type_id" => $db->f("item_type_id"),
					"item_code" => $item_code, "manufacturer_code" => $manufacturer_code,
					"item_name" => $item_name,
					"is_recurring" => $is_recurring, 
					"recurring_last_payment" => $recurring_last_payment, "recurring_next_payment" => $recurring_next_payment,
					"price" => $price * $payment_rate, 
					"quantity" => $quantity, 
					"item_total" => $item_total * $payment_rate,
					"price_excl_tax" => $price_excl_tax * $payment_rate, 
					"price_incl_tax" => $price_incl_tax * $payment_rate,
					"price_excl_tax_total" => $price_excl_tax_total * $payment_rate, 
					"price_incl_tax_total" => $price_incl_tax_total * $payment_rate,
					"item_tax" => $item_tax * $payment_rate, 
					"item_tax_total" => $item_tax_total * $payment_rate,
					"tax_id" => $item_tax_id, "tax_free" => $tax_free, "tax_percent" => $item_tax_percent,
					"weight" => $db->f("weight"), "actual_weight" => $db->f("actual_weight"),
					"is_shipping_free" => $db->f("is_shipping_free"),
					"shipping_cost" => $db->f("shipping_cost") * $payment_rate, 
					"downloadable" => $db->f("downloadable"),
					"discount_amount" => $db->f("discount_amount") * $payment_rate,
					"buying_price" => $db->f("buying_price") * $payment_rate,
					"real_price" => $db->f("real_price") * $payment_rate,
					"points_price" => $db->f("points_price"),
					"reward_points" => $db->f("reward_points"),
					"reward_credits" => $db->f("reward_credits"),
					"components" => array(),
				);
				if ($top_order_item_id) {
					$order_items[$top_order_item_id]["components"][] = $order_item_id;
				}

				// save products in payment_items array
				$price_rate_excl = round($price_excl_tax * $payment_rate, $payment_decimals);
				$price_rate_incl = round($price_incl_tax * $payment_rate, $payment_decimals);
				$payment_rate_total += $price_rate_excl * $quantity;
				$payment_items[] = array(
					"id" => $item_id,
					"type" => "item",
					"name" => $item_name,
					"price" => $price_rate_excl,
					"amount" => $price_rate_excl,
					"price_excl_tax" => $price_rate_excl,
					"price_incl_tax" => $price_rate_incl,
					"quantity" => $quantity,
				);
			}

			// generate basket description
			$eol = get_eol();
			$items_text = ""; $items_html = "";
			foreach ($order_items as $id => $order_item) {
				if ($items_text) {
					$items_text .= $eol; $items_html .= "<br>";
				}
				$item_name = $order_item["item_name"];
				$quantity = $order_item["quantity"];
				$item_total = $order_item["item_total"];
				$items_text .= $item_name;
				$items_html .= $item_name;

				$properties = array(); $properties_text = ""; $properties_html = "";
				$sql  = " SELECT * FROM " . $table_prefix . "orders_items_properties ";
				$sql .= " WHERE order_item_id=" . $db->tosql($id, INTEGER);
				$sql .= " ORDER BY property_order";
				$db->query($sql);
				while ($db->next_record()) {
					if ($properties_text) {
						$properties_text .= "; "; $properties_html .= "<br>";
					}
					$item_property_id = $db->f("item_property_id");
					$property_name = get_translation($db->f("property_name"));
					$property_value = get_translation($db->f("property_value"));
					$property_price = $db->f("additional_price");
					$property_weight = $db->f("additional_weight");
					$property_actual_weight = $db->f("actual_weight");
					$properties_text .= $property_name . ": " . $property_value; 
					$properties_html .= $property_name . ": " . $property_value; 
					$properties[$item_property_id] = array(
						"name" => $property_name,
						"value" => $property_value,
						"price" => $property_price,
						"weight" => $property_weight,
						"actual_weight" => $property_actual_weight,
					);
				}
				if ($properties_text) {
					$items_text .= "(" . $properties_text . ")";
					$items_html .= "<br>" . $properties_html;
				}
				// payment rate already applied to items so we need override it to 1 
				$payment_currency["rate"] = 1;
				$items_text .= " - " . $quantity . " x " . currency_format($item_total, $payment_currency);
				$items_html .= "<br>" . $quantity . " x " . currency_format($item_total, $payment_currency);

				$order_items[$id]["properties"] = $properties;
				$order_items[$id]["properties_html"] = $properties_html;
				$order_items[$id]["properties_text"] = $properties_text;
			}
			$variables["items"] = $order_items;
			$variables["items_text"] = $items_text;
			$variables["items_html"] = $items_html;
			$variables["basket"] = $items_text; 
			$variables["total_quantity"] = $total_quantity;
			$variables["total_items"] = $total_items;

			// #2. get properties for order
			$properties_total_excl_tax = 0; $properties_total_tax = 0; $properties_total_incl_tax = 0; 
			$order_properties = array();
			$sql  = " SELECT order_property_id, property_id, property_name, property_value, property_price, property_points_amount, tax_free ";
			$sql .= " FROM " . $table_prefix . "orders_properties ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$order_property_id = $db->f("order_property_id");
				$property_id = $db->f("property_id");
				$property_name = get_translation($db->f("property_name"));
				$property_value = get_translation($db->f("property_value"));
				$property_price = $db->f("property_price");
				$property_points_amount = $db->f("property_points_amount");
				$tax_free = $db->f("tax_free");
				$order_property_id = $db->f("order_property_id");

				$property_tax_id = 0;
				$property_tax_percent = $tax_percent;
				$property_tax_values = get_tax_amount($order_tax_rates, "properties", $property_price, 1, $property_tax_id, $tax_free, $property_tax_percent, "", 2, $tax_prices_type, $tax_round);
				$property_tax = add_tax_values($order_tax_rates, $property_tax_values, "properties", $tax_round);

				if ($tax_prices_type == 1) {
					$property_price_excl_tax = $property_price - $property_tax;
					$property_price_incl_tax = $property_price;
				} else {
					$property_price_excl_tax = $property_price;
					$property_price_incl_tax = $property_price + $property_tax;
				}
				$properties_total_excl_tax += $property_price_excl_tax;
				$properties_total_tax += $property_tax; 
				$properties_total_incl_tax += $property_price_incl_tax; 

				$order_properties[$order_property_id] = array(
					"property_name" => $property_name,
					"property_value" => $property_value, 
					"property_price" => $property_price * $payment_rate, 
					"property_tax" => $property_tax * $payment_rate, 
					"property_tax_percent" => $property_tax_percent, 
					"property_price_excl_tax" => $property_price_excl_tax * $payment_rate, 
					"property_price_incl_tax" => $property_price_incl_tax * $payment_rate,
					"property_points_amount" => $property_points_amount, 
					"tax_free" => $tax_free
				);

				// populate variables array
				$variables["field_" . $property_id] = $property_value;
				$variables["field_name_" . $property_id] = $property_name;
				$variables["field_value_" . $property_id] = $property_value;

				// save order properties in payment_items array but only with price
				if ($property_price_excl_tax != 0) {
					$item_name = $property_name;
					if (strlen($property_value)) { $item_name .= ": ". $property_value; }
					$price_rate_excl = round($property_price_excl_tax * $payment_rate, $payment_decimals);
					$price_rate_incl = round($property_price_incl_tax * $payment_rate, $payment_decimals);
					$payment_rate_total += $price_rate_excl;
					$payment_items[] = array(
						"id" => $property_id,
						"type" => "option",
						"name" => $item_name,
						"price" => $price_rate_excl,
						"amount" => $price_rate_excl,
						"price_excl_tax" => $price_rate_excl,
						"price_incl_tax" => $price_rate_incl,
						"quantity" => 1,
					);
				}
			}
			$variables["properties"] = $order_properties;
			$variables["properties_total_excl_tax"] = $properties_total_excl_tax;
			$variables["properties_total_tax"] = $properties_total_tax;
			$variables["properties_total_incl_tax"] = $properties_total_incl_tax;

			// #3. get shipments for order
			$shipments_total_excl_tax = 0; $shipments_total_tax = 0; $shipments_total_incl_tax = 0; 
			$order_shipments = array();
			$sql  = " SELECT * FROM " . $table_prefix . "orders_shipments ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$order_shipping_id = $db->f("order_shipping_id");
				$shipping_id = $db->f("shipping_id");
				$shipping_cost = $db->f("shipping_cost");
				$shipping_desc = get_translation($db->f("shipping_desc"));
				$points_cost = $db->f("points_cost");
				$shipping_tax_free = $db->f("tax_free");
				$order_shipments[$order_shipping_id] = $db->Record;
				// calculate tax and total values
				$shipping_tax_id = 0;
				$shipping_tax_values = get_tax_amount($order_tax_rates, "shipping", $shipping_cost, 1, $shipping_tax_id, $shipping_tax_free, $shipping_tax_percent, "", 2, $tax_prices_type, $tax_round);
				$shipping_tax_total = add_tax_values($order_tax_rates, $shipping_tax_values, "shipping", $tax_round);

				if ($tax_prices_type == 1) {
					$shipping_cost_excl_tax = $shipping_cost - $shipping_tax_total;
					$shipping_cost_incl_tax = $shipping_cost;
				} else {
					$shipping_cost_excl_tax = $shipping_cost;
					$shipping_cost_incl_tax = $shipping_cost + $shipping_tax_total;
				}
				$shipments_total_excl_tax += $shipping_cost_excl_tax; 
				$shipments_total_tax += $shipping_tax_total; 
				$shipments_total_incl_tax += $shipping_cost_incl_tax; 

				$order_shipments[$order_shipping_id]["shipping_cost_excl_tax"] = $shipping_cost_excl_tax;
				$order_shipments[$order_shipping_id]["shipping_tax"] = $shipping_tax_total;
				$order_shipments[$order_shipping_id]["shipping_cost_incl_tax"] = $shipping_cost_incl_tax;

				// save shipments in payment_items array
				$price_rate_excl = round($shipping_cost_excl_tax * $payment_rate, $payment_decimals);
				$price_rate_incl = round($shipping_cost_incl_tax * $payment_rate, $payment_decimals);
				$payment_rate_total += $price_rate_excl;
				$payment_items[] = array(
					"id" => $shipping_id,
					"type" => "shipping",
					"name" => $shipping_desc,
					"price" => $price_rate_excl,
					"amount" => $price_rate_excl,
					"price_excl_tax" => $price_rate_excl,
					"price_incl_tax" => $price_rate_incl,
					"quantity" => 1,
				);
			}
			// check old way shipments
			if (sizeof($order_shipments) == 0 && $old_shipping_type_desc) {
				$order_shipping_id = 0;
				$shipping_tax_free = !$old_shipping_taxable;
				$order_shipments[$order_shipping_id] = array(
					"shipping_cost" => $old_shipping_cost,
					"shipping_desc" => $old_shipping_type_desc,
					"points_cost" => 0,
					"tax_free" => $shipping_tax_free,
				);

				// calculate tax and total values
				$shipping_tax_id = 0;
				$shipping_tax_values = get_tax_amount($order_tax_rates, "shipping", $old_shipping_cost, 1, $shipping_tax_id, $shipping_tax_free, $shipping_tax_percent, "", 2, $tax_prices_type, $tax_round);
				$shipping_tax_total = add_tax_values($order_tax_rates, $shipping_tax_values, "shipping", $tax_round);

				if ($tax_prices_type == 1) {
					$shipping_cost_excl_tax = $old_shipping_cost - $shipping_tax_total;
					$shipping_cost_incl_tax = $old_shipping_cost;
				} else {
					$shipping_cost_excl_tax = $old_shipping_cost;
					$shipping_cost_incl_tax = $old_shipping_cost + $shipping_tax_total;
				}
				$shipments_total_excl_tax += $shipping_cost_excl_tax; 
				$shipments_total_tax += $shipping_tax_total; 
				$shipments_total_incl_tax += $shipping_cost_incl_tax; 

				$order_shipments[$order_shipping_id]["shipping_cost_excl_tax"] = $shipping_cost_excl_tax;
				$order_shipments[$order_shipping_id]["shipping_tax"] = $shipping_tax_total;
				$order_shipments[$order_shipping_id]["shipping_cost_incl_tax"] = $shipping_cost_incl_tax;

				// save shipments in payment_items array
				$price_rate_excl = round($shipping_cost_excl_tax * $payment_rate, $payment_decimals);
				$price_rate_incl = round($shipping_cost_incl_tax * $payment_rate, $payment_decimals);
				$payment_rate_total += $price_rate_excl;
				$payment_items[] = array(
					"id" => 0,
					"type" => "shipping",
					"name" => $old_shipping_type_desc,
					"price" => $price_rate_excl,
					"amount" => $price_rate_excl,
					"price_excl_tax" => $price_rate_excl,
					"price_incl_tax" => $price_rate_incl,
					"quantity" => 1,
				);
			}

			$variables["shipments"] = $order_shipments;
			$variables["shipments_total_excl_tax"] = $shipments_total_excl_tax;
			$variables["shipments_total_tax"] = $shipments_total_tax;
			$variables["shipments_total_incl_tax"] = $shipments_total_incl_tax;
			// old way variables
			$variables["shipping_type_desc"] = va_constant("DELIVERY_MSG");
			$variables["shipping_cost"] = number_format($shipments_total_excl_tax * $payment_rate, $payment_decimals, ".", "");
			$variables["shipping_taxable"] = $old_shipping_taxable;
			$variables["shipping_cost_excl_tax"] = number_format($shipments_total_excl_tax * $payment_rate, $payment_decimals, ".", "");
			$variables["shipping_tax"] = number_format($shipments_total_tax * $payment_rate, $payment_decimals, ".", "");
			$variables["shipping_cost_incl_tax"] = number_format($shipments_total_incl_tax * $payment_rate, $payment_decimals, ".", "");


			// #4. order coupons discount data 
			$total_discount_excl_tax = 0; $total_discount_tax = 0; $total_discount_incl_tax = 0; 
			$order_coupons = array();
			$sql  = " SELECT * FROM " . $table_prefix . "orders_coupons ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$order_coupon_id = $db->f("order_coupon_id");
				$coupon_id = $db->f("coupon_id");
				$coupon_code = $db->f("coupon_code");
				$coupon_title = $db->f("coupon_title");
				$discount_amount = $db->f("discount_amount");
				$discount_tax = $db->f("discount_tax_amount");
				// calculate tax and total values
				if ($tax_prices_type == 1) {
					$discount_excl_tax = $discount_amount - $discount_tax;
					$discount_incl_tax = $discount_amount;
				} else {
					$discount_excl_tax = $discount_amount;
					$discount_incl_tax = $discount_amount + $discount_tax;
				}
				$total_discount_excl_tax += $discount_excl_tax; 
				$total_discount_tax += $discount_tax; 
				$total_discount_incl_tax += $discount_incl_tax; 

				$order_coupons[$order_coupon_id] = array(
					"coupon_id" => $coupon_id,
					"coupon_code" => $coupon_code,
					"coupon_title" => $coupon_title,
					"discount_excl_tax" => $discount_excl_tax,
					"discount_tax" => $discount_tax,
					"discount_incl_tax" => $discount_incl_tax,
				);

				// save discounts in payment_items array
				$price_rate_excl = round($discount_excl_tax * $payment_rate, $payment_decimals);
				$price_rate_incl = round($discount_incl_tax * $payment_rate, $payment_decimals);
				$payment_rate_total -= $price_rate_excl;
				$payment_items[] = array(
					"id" => $coupon_id,
					"type" => "coupon",
					"name" => $coupon_title,
					"price" => -$price_rate_excl,
					"amount" => -$price_rate_excl,
					"price_excl_tax" => -$price_rate_excl,
					"price_incl_tax" => -$price_rate_incl,
					"quantity" => 1,
				);

			}
			$variables["coupons"] = $order_coupons;
			$variables["total_discount_excl_tax"] = $total_discount_excl_tax;
			$variables["total_discount_tax"] = $total_discount_tax;
			$variables["total_discount_incl_tax"] = $total_discount_incl_tax;
			// end order coupons


			// #5. processing fee for order
			if ($processing_fee != 0) {
				// calculate tax and total values
				$processing_tax_id = 0;
				$processing_tax_values = get_tax_amount($order_tax_rates, "fee", $processing_fee, 1, $processing_tax_id, $processing_tax_free, $processing_tax_percent, "", 2, $tax_prices_type, $tax_round);
				$processing_tax_total = add_tax_values($order_tax_rates, $processing_tax_values, "fee", $tax_round);

				if ($tax_prices_type == 1) {
					$processing_excl_tax = $processing_fee - $processing_tax_total;
					$processing_incl_tax = $processing_fee;
				} else {
					$processing_excl_tax = $processing_fee;
					$processing_incl_tax = $processing_fee + $processing_tax_total;
				}

				// save processing fee in payment_items array
				$price_rate_excl = round($processing_excl_tax * $payment_rate, $payment_decimals);
				$price_rate_incl = round($processing_incl_tax * $payment_rate, $payment_decimals);
				$payment_rate_total += $price_rate_excl;
				$payment_items[] = array(
					"id" => 0,
					"type" => "fee",
					"name" => va_constant("PROCESSING_FEE_MSG"),
					"price" => $price_rate_excl,
					"amount" => $price_rate_excl,
					"price_excl_tax" => $price_rate_excl,
					"price_incl_tax" => $price_rate_incl,
					"quantity" => 1,
				);
			}


			// #5. tax rates for order
			$taxes_total = 0;
			if (is_array($order_tax_rates)) {
				// get taxes sums for further calculations
				$taxes_sum = 0; $discount_tax_sum = $total_discount_tax;
				foreach($order_tax_rates as $tax_id => $tax_info) {
					$tax_cost = isset($tax_info["tax_total"]) ? $tax_info["tax_total"] : 0;
					$taxes_sum += va_round($tax_cost, $payment_decimals);
				}

				$tax_number = 0;
				foreach($order_tax_rates as $tax_id => $tax_info) {
					$tax_number++;
					$tax_id = $tax_info["tax_id"];
					$tax_name = get_translation($tax_info["tax_name"]);
					$current_tax_free = isset($tax_info["tax_free"]) ? $tax_info["tax_free"] : 0;
					//if ($tax_free) { $current_tax_free = true; }
					$tax_percent = $tax_info["tax_percent"];
					$fixed_amount = $tax_info["fixed_amount"];
					$tax_types = $tax_info["types"];
					$tax_cost = isset($tax_info["tax_total"]) ? $tax_info["tax_total"] : 0;
					if ($total_discount_tax) {
						// in case if there are any order coupons decrease taxes value 
						if ($tax_number == sizeof($order_tax_rates)) {
							$tax_discount = $discount_tax_sum;
						} elseif ($taxes_sum != 0) {
							$tax_discount = round(($tax_cost * $total_discount_tax) / $taxes_sum, 2);
						} else {
							$tax_discount = 0;
						}
						$discount_tax_sum -= $tax_discount;
						$tax_cost -= $tax_discount;
					}
					$taxes_total += va_round($tax_cost, $payment_decimals);

					// save discounts in payment_items array
					$price_rate_excl = round($tax_cost * $payment_rate, $payment_decimals);
					$price_rate_incl = round($tax_cost * $payment_rate, $payment_decimals);
					$payment_rate_total += $price_rate_excl;
					$payment_items[] = array(
						"id" => $tax_id,
						"type" => "tax",
						"name" => $tax_name,
						"price" => $price_rate_excl,
						"amount" => $price_rate_excl,
						"price_excl_tax" => $price_rate_excl,
						"price_incl_tax" => $price_rate_incl,
						"quantity" => 1,
					);
				}
			}
			// end tax rates for order

			// #7. check if there are any difference to make price total correction between $order_rate_total and $payment_rate_total
			if (round($payment_rate_total, 2) != round($order_rate_total, 2)) {
				$correction_price = round($order_rate_total - $payment_rate_total, 2);
				$payment_items[] = array(
					"id" => "0",
					"type" => "correction",
					"name" => "Price Correction",
					"price" => $correction_price,
					"amount" => $correction_price,
					"price_excl_tax" => $correction_price,
					"price_incl_tax" => $correction_price,
					"quantity" => 1,
				);
			}
			$variables["payment_items"] = $payment_items;

			$db->query("SELECT * FROM " . $table_prefix . "payment_systems WHERE payment_id=" . $db->tosql($payment_id, INTEGER));
			if ($db->next_record()) {
				$payment_name = get_translation($db->f("payment_name"));
				$user_payment_name = get_translation($db->f("user_payment_name"));
				if ($user_payment_name) {
					$payment_name = $user_payment_name;
				}
				$variables["payment_url"] = $db->f("payment_url");
				$variables["submit_method"] = $db->f("submit_method");
				$variables["payment_name"] = $db->f("payment_name");
				$variables["user_payment_name"] = $db->f("user_payment_name");
				$variables["is_advanced"] = $db->f("is_advanced");
				$variables["advanced_url"] = $db->f("advanced_url");
				$variables["advanced_php_lib"] = $db->f("advanced_php_lib");
				$variables["failure_action"] = $db->f("failure_action");
				$variables["success_status_id"] = $db->f("success_status_id");
				$variables["pending_status_id"] = $db->f("pending_status_id");
				$variables["failure_status_id"] = $db->f("failure_status_id");
			}

			$fp_hash_name = ""; $epdqdata_name = ""; $protx_crypt_name = ""; $gate2shop_name = "";
			$db->query("SELECT * FROM " . $table_prefix . "payment_parameters WHERE payment_id=" . $db->tosql($payment_id, INTEGER));
			while ($db->next_record())
			{
				$parameter_source = $db->f("parameter_source");
				$parameter_name = $db->f("parameter_name");
				$parameter_type = $db->f("parameter_type");
				$not_passed = $db->f("not_passed");
				if (strtolower($parameter_name) == "x_fp_hash" && $parameter_type == "VARIABLE") {
					$fp_hash_name = $parameter_name;
					$fp_not_passed = $not_passed;
				} elseif (strtolower($parameter_name) == "epdqdata" && $parameter_type == "VARIABLE") {
					$epdqdata_name = $parameter_name;
					$epdqdata_not_passed = $not_passed;
				} elseif (strtolower($parameter_name) == "crypt" && strtolower($parameter_source) == "protx_crypt") {
					$protx_crypt_name = $parameter_name;
					$protx_crypt_not_passed = $not_passed;
				} elseif (strtolower($parameter_name) == "numberofitems"){
					$gate2shop_name = $parameter_name;
					$gate2shop_not_passed = $not_passed;
				} elseif (preg_match("/\{digit\}/", $parameter_name) || preg_match("/\{no_digit\}/", $parameter_name)) {
					$i = 0;
					foreach ($order_items as $id => $order_item) {
						$digit_parameter = str_replace("{digit}", ($i + 1), $parameter_name);
						if (preg_match("/\{digit\}/", $parameter_name)) {
							$i++;
						}
						$digit_parameter = str_replace("{no_digit}", "", $digit_parameter);
						if ($parameter_type == "CONSTANT") {
							$parameter_value = $parameter_source;
						} elseif ($parameter_type == "VARIABLE") {
							if (preg_match_all("/\{(\w+)\}/is", $parameter_source, $matches)) {
								$parameter_value = $parameter_source;
								for ($p = 0; $p < sizeof($matches[1]); $p++) {
									$l_source = strtolower($matches[1][$p]);
									if (isset($order_item[$l_source])) {
										$parameter_value = str_replace("{".$l_source."}", $order_item[$l_source], $parameter_value);
									}
								}
							} else {
								$l_source = strtolower($parameter_source);
								$parameter_value = isset($order_item[$l_source]) ? $order_item[$l_source] : $parameter_source;
							}
						} else {
							$parameter_value = $parameter_source;
						}
						$payment_parameters[$digit_parameter] = $parameter_value;
						if ($params_transform == "lowercase") {
							$payment_parameters[strtolower($digit_parameter)] = $parameter_value;
						}
						if (!$not_passed) {
							$pass_data[$digit_parameter] = $parameter_value;
							$pass_parameters[$digit_parameter] = 1;
							if ($params_transform == "lowercase") {
								$pass_data[strtolower($digit_parameter)] = $parameter_value;
								$pass_parameters[strtolower($digit_parameter)] = 1;
							}
							if (!is_array($parameter_value)) {
								// pass all parameters except arrays
								if ($post_parameters) { $post_parameters .= "&"; }
								$post_parameters .= $digit_parameter . "=" . urlencode($parameter_value);
							}
						} else {
							if ($params_transform == "lowercase") {
								$pass_parameters[strtolower($digit_parameter)] = 0;
							}
							$pass_parameters[$digit_parameter] = 0;
						}
					}
				} else {
					if ($parameter_type == "CONSTANT") {
						$parameter_value = $parameter_source;
					} elseif ($parameter_type == "VARIABLE") {
						if (preg_match_all("/\{(\w+)\}/is", $parameter_source, $matches)) {
							$parameter_value = $parameter_source;
							for ($p = 0; $p < sizeof($matches[1]); $p++) {
								$l_source = strtolower($matches[1][$p]);
								if (isset($variables[$l_source])) {
									$parameter_value = str_replace("{".$l_source."}", $variables[$l_source], $parameter_value);
								}
							}
						} else {
							$l_source = strtolower($parameter_source);
							$parameter_value = isset($variables[$l_source]) ? $variables[$l_source] : $parameter_source;
						}
					} else {
						$parameter_value = $parameter_source;
					}
					if ($params_transform == "lowercase") {
						$payment_parameters[strtolower($parameter_name)] = $parameter_value;
					}
					$payment_parameters[$parameter_name] = $parameter_value;
					if (!$not_passed) {
						$pass_data[$parameter_name] = $parameter_value;
						$pass_parameters[$parameter_name] = 1;
						if ($params_transform == "lowercase") {
							$pass_data[strtolower($parameter_name)] = $parameter_value;
							$pass_parameters[strtolower($parameter_name)] = 1;
						}
						if (!is_array($parameter_value)) {
							// pass all parameters except arrays
							if ($post_parameters) { $post_parameters .= "&"; }
							$post_parameters .= $parameter_name . "=" . urlencode($parameter_value);
						}
					} else {
						if ($params_transform == "lowercase") {
							$pass_parameters[strtolower($parameter_name)] = 0;
						}
						$pass_parameters[$parameter_name] = 0;
					}
				}
			}

			$additional_params = array();
			if (strlen($fp_hash_name)) {
				$x_login = isset($payment_parameters["x_login"]) ? $payment_parameters["x_login"] : "";
				$x_tran_key = isset($payment_parameters["x_tran_key"]) ? $payment_parameters["x_tran_key"] : "";
				$x_currency_code = isset($payment_parameters["x_currency_code"]) ? $payment_parameters["x_currency_code"] : "";
				$x_fp_timestamp = isset($payment_parameters["x_fp_timestamp"]) ? $payment_parameters["x_fp_timestamp"] : "";

				$fp_hash_value = calculate_fp ($x_login, $x_tran_key, $variables["order_total"], $variables["order_id"], $x_fp_timestamp, $x_currency_code);
				$payment_parameters[$fp_hash_name] = $fp_hash_value;
				if (!$fp_not_passed) {
					$pass_data[$fp_hash_name] = $fp_hash_value;
					$additional_params[$fp_hash_name] = $fp_hash_value;
				}
			}
			if (strlen($epdqdata_name) && !$epdqdata_not_passed) {
				include_once("./payments/epdq_cpi_encryption.php");
				$epdqdata_value = get_epdqdata($payment_parameters);
				$additional_params[$epdqdata_name] = $epdqdata_value;
			}
			if (strlen($gate2shop_name)) {
				include_once("./payments/gate2shop_functions.php");
				$gate2shop = get_gate2shop($payment_parameters);
				foreach ($gate2shop as $gate2shop_name => $gate2shop_value) {
					$additional_params[$gate2shop_name] = $gate2shop_value;
				}
			}
			if (strlen($protx_crypt_name) && !$protx_crypt_not_passed) {
				include_once("./payments/protx_form_encryption.php");
				$protx_crypt_value = get_protx_crypt($payment_parameters);
				$additional_params[$protx_crypt_name] = $protx_crypt_value;
			}
			foreach ($additional_params as $param_name => $param_value) {
				$pass_data[$param_name] = $param_value;
				if ($post_parameters) { $post_parameters .= "&"; }
				$post_parameters .= urlencode($param_name) . "=" . urlencode($param_value);
			}
		}
	}

	function set_cart_titles($cart_settings = "")
	{
		global $t, $settings;
		if (!is_array($cart_settings)) { $cart_settings = $settings; }
		$ordinal_number_col_name = get_setting_value($cart_settings, "ordinal_number_col_name", va_message("ORDINAL_NUMBER_COLUMN"));
		$image_col_name = get_setting_value($cart_settings, "image_col_name", va_message("IMAGE_MSG"));
		$name_col_name = get_setting_value($cart_settings, "name_col_name", va_message("PROD_TITLE_COLUMN"));
		$price_excl_col_name = get_setting_value($cart_settings, "price_excl_col_name", va_message("PROD_PRICE_COLUMN")." {tax_note_excl}");
		$tax_percent_col_name = get_setting_value($cart_settings, "tax_percent_col_name", "{tax_name} (%)");
		$tax_col_name = get_setting_value($cart_settings, "tax_col_name", "{tax_name}");
		$price_incl_col_name = get_setting_value($cart_settings, "price_incl_col_name", va_message("PROD_PRICE_COLUMN")." {tax_note_incl}");
		$quantity_col_name = get_setting_value($cart_settings, "quantity_col_name", va_message("PROD_QTY_COLUMN"));
		$total_excl_col_name = get_setting_value($cart_settings, "total_excl_col_name", va_message("PROD_TOTAL_COLUMN")." {tax_note_excl}");
		$tax_total_col_name = get_setting_value($cart_settings, "tax_total_col_name", "{tax_name} ".va_message("PROD_TAX_TOTAL_COLUMN"));
		$total_incl_col_name = get_setting_value($cart_settings, "total_incl_col_name", va_message("PROD_TOTAL_COLUMN")." {tax_note_incl}");
		parse_value($ordinal_number_col_name);
		parse_value($image_col_name);
		parse_value($name_col_name);
		parse_value($price_excl_col_name);
		parse_value($tax_percent_col_name);
		parse_value($tax_col_name);
		parse_value($price_incl_col_name);
		parse_value($quantity_col_name);
		parse_value($total_excl_col_name);
		parse_value($tax_total_col_name);
		parse_value($total_incl_col_name);
		$t->set_var("ordinal_number_col_name", $ordinal_number_col_name);
		$t->set_var("image_col_name", $image_col_name);
		$t->set_var("name_col_name", $name_col_name);
		$t->set_var("price_excl_col_name", $price_excl_col_name);
		$t->set_var("tax_percent_col_name", $tax_percent_col_name);
		$t->set_var("tax_col_name", $tax_col_name);
		$t->set_var("price_incl_col_name", $price_incl_col_name);
		$t->set_var("quantity_col_name", $quantity_col_name);
		$t->set_var("total_excl_col_name", $total_excl_col_name);
		$t->set_var("tax_total_col_name", $tax_total_col_name);
		$t->set_var("total_incl_col_name", $total_incl_col_name);
	}

	function parse_cart_columns($name_column, $price_excl_tax_column, $tax_percent_column, $tax_column, $price_incl_tax_column, $quantity_column, $price_excl_tax_total_column, $tax_total_column, $price_incl_tax_total_column, $item_image_column = 0, $ordinal_number_column = 0)
	{
		global $t;
		if ($ordinal_number_column) {
			$t->sparse("ordinal_number_column", false);
		}
		if ($name_column) {
			$t->sparse("item_name_column", false);
		}
		if ($price_excl_tax_column) {
			$t->sparse("item_price_excl_tax_column", false);
		}
		if ($tax_percent_column) {
			$t->sparse("item_tax_percent_column", false);
		}
		if ($tax_column) {
			$t->sparse("item_tax_column", false);
		}
		if ($price_incl_tax_column) {
			$t->sparse("item_price_incl_tax_column", false);
		}
		if ($quantity_column) {
			$t->sparse("item_quantity_column", false);
		}
		if ($price_excl_tax_total_column) {
			$t->sparse("item_price_excl_tax_total_column", false);
		}
		if ($tax_total_column) {
			$t->sparse("item_tax_total_column", false);
		}
		if ($price_incl_tax_total_column) {
			$t->sparse("item_price_incl_tax_total_column", false);
		}
		if ($item_image_column) {
			$t->sparse("item_image_column", false);
		}
	}

	function get_checkout_details($order_info, $operation = "")
	{
		global $db, $table_prefix, $settings, $param_prefix, $parameters;

		$user_id = get_session("session_user_id");
		$checkout_details = array();
		$user_details = array();
		if ($operation == "load") {
			$user_order_id = get_session("session_user_order_id"); 
			$sql  = " SELECT * ";
			$sql .= " FROM " . $table_prefix . "orders WHERE order_id=" . $db->tosql($user_order_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$user_details = $db->Record;
			}
		} else if ($user_id) {
			$sql  = " SELECT * ";
			$sql .= " FROM " . $table_prefix . "users WHERE user_id=" . $db->tosql($user_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$user_details = $db->Record;
			}
		} else { // get default country and state from cookies
			$cookie_order_info = trim(get_cookie("cookie_order_info"));
			if (strlen($cookie_order_info)) {
				$cookie_pairs = explode("|", $cookie_order_info);
				for ($i = 0; $i < sizeof($cookie_pairs); $i++) {
					$cookie_line = trim($cookie_pairs[$i]);
					if (strlen($cookie_line)) {
						$cookie_values = explode("=", $cookie_line, 2);
						$user_details[$cookie_values[0]] = $cookie_values[1];
					}
				}
			}
		}

		// calculate number of predefined billing and shipping fields
		if (!isset($param_prefix)) { $param_prefix = ""; }
		$bill_fields = 0; $ship_fields = 0;
		foreach ($parameters as $param_key => $param_name) {
			$bill_field = $param_prefix."show_".$param_name;
			$ship_field = $param_prefix."show_delivery_".$param_name;
			if (get_setting_value($order_info, $bill_field)) { $bill_fields++; }
			if (get_setting_value($order_info, $ship_field)) { $ship_fields++; }
		}
		// prepare ship and bill data
		foreach ($parameters as $param_key => $param_name) {
			$bill_value = get_setting_value($user_details, $param_name);
			$ship_value = get_setting_value($user_details, "delivery_".$param_name);
			// save bill data
			$checkout_details["bill_".$param_name] = ($bill_fields) ? $bill_value : $ship_value;
			// save ship data
			$checkout_details["ship_".$param_name] = ($ship_fields) ? $ship_value : $bill_value;
		}
		// check for default country if it wasn't set
		if (!$checkout_details["bill_country_id"]) {
			$checkout_details["bill_country_id"] = get_setting_value($settings, "country_id");
		}
		if (!$checkout_details["ship_country_id"]) {
			$checkout_details["ship_country_id"] = get_setting_value($settings, "country_id");
		}

		return $checkout_details;
	}

	function order_tax_rates($order_id)
	{
		global $db, $table_prefix, $settings, $db_type;

		$tax_ids = array();
		$tax_rates = array();
		$order_tax_ids = "";
		$sql  = " SELECT order_tax_id, tax_id, tax_type, show_type, tax_name, tax_percent, fixed_amount, order_fixed_amount, ";
		$sql .= " shipping_tax_percent, shipping_fixed_amount ";
		$sql .= " FROM " . $table_prefix . "orders_taxes ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$tax_id = $db->f("tax_id");
			$order_tax_id = $db->f("order_tax_id");
			$tax_ids[$order_tax_id] = $tax_id;
			$tax_rate = array(
				"tax_id" => $db->f("tax_id"), "tax_type" => $db->f("tax_type"), "show_type" => $db->f("show_type"),
				"tax_name" => $db->f("tax_name"), "tax_percent" => $db->f("tax_percent"), 
				"fixed_amount" => $db->f("fixed_amount"), "order_fixed_amount" => $db->f("order_fixed_amount"), 
				"types" => array("shipping" => array(
						"tax_percent" => $db->f("shipping_tax_percent"), "fixed_amount" => $db->f("shipping_fixed_amount"), 
					),
				),
			);
			$tax_rates[$tax_id] = $tax_rate;
			if (strval($order_tax_ids) !== "") { $order_tax_ids .= ","; }
			$order_tax_ids .= $order_tax_id;
		}

		if (strlen($order_tax_ids)) {
			$sql  = " SELECT order_tax_id, item_type_id, tax_percent, fixed_amount FROM " . $table_prefix . "orders_items_taxes ";
			$sql .= " WHERE order_tax_id IN (" . $db->tosql($order_tax_ids, INTEGERS_LIST) . ") ";
			$db->query($sql);
			while ($db->next_record()) {
				$order_tax_id = $db->f("order_tax_id");
				$tax_id = $tax_ids[$order_tax_id];
				$item_type_id = $db->f("item_type_id");
				$tax_percent = $db->f("tax_percent");
				$fixed_amount = $db->f("fixed_amount");
				if (strlen($tax_percent) || strlen($fixed_amount)) {
					$tax_rates[$tax_id]["types"][$item_type_id] = array(
						"tax_percent" => $tax_percent, "fixed_amount" => $fixed_amount,
					);
				}
			}
		} else {
			// check old taxes
			$sql  = " SELECT o.tax_name, o.tax_percent ";
			$sql .= " FROM " . $table_prefix . "orders o ";
			$sql .= " WHERE o.order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$tax_name = get_translation($db->f("tax_name"));
				$tax_percent = $db->f("tax_percent");
				if (strlen($tax_name) || $tax_percent > 0) {
					$tax_rates[0] = array(
						"tax_id" => 0, "tax_type" => 1, "show_type" => 0, 
						"tax_name" => $tax_name, "tax_percent" => $tax_percent, "fixed_amount" => "", "order_fixed_amount" => "", 
						"shipping_tax_percent" => "", "shipping_fixed_amount" => "", "types" => array(),
					);
				}
			}
			if (sizeof($tax_rates)) {
				$sql  = " SELECT item_type_id, tax_free, tax_percent FROM " . $table_prefix . "orders_items ";
				$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
				$db->query($sql);
				while ($db->next_record()) {
					$item_type_id = $db->f("item_type_id");
					$tax_free = $db->f("tax_free");
					$tax_percent = $db->f("tax_percent");
					if (!$tax_free && (strlen($tax_percent))) {
						$tax_rates[0]["types"][$item_type_id] = array(
							"tax_percent" => $tax_percent, "fixed_amount" => "",
						);
					}
				}
			}
		}

		return $tax_rates;
	}

	function set_basket_tag($order_id, $type, $message)
	{
		global $settings, $t, $is_admin_path;
		if (strpos($message, "{basket}") !== false) {
			if ($is_admin_path) {
				$user_template_path = $settings["templates_dir"];
				if (preg_match("/^\.\//", $user_template_path)) {
					$user_template_path = str_replace("./", "../", $user_template_path);
				} elseif (!preg_match("/^\//", $user_template_path)) {
					$user_template_path = "../" . $user_template_path;
				}
				$t->set_template_path($user_template_path);
			}
			if ($type) {
				if (!$t->block_exists("basket_html")) {
					$t->set_file("basket_html", "email_basket.html");
				}
				if (!$t->var_exists("basket_html")) {
					$items_text = show_order_items($order_id, true, "email");
					$t->parse("basket_html", false);
				}
				$t->set_var("basket", $t->get_var("basket_html"));
			} else {
				if (!$t->block_exists("basket_text")) {
					$t->set_file("basket_text", "email_basket.txt");
				}
				if (!$t->var_exists("basket_text")) {
					$items_text = show_order_items($order_id, true, "email");
					$t->parse("basket_text", false);
				}
				$t->set_var("basket", $t->get_var("basket_text"));
			}
			if ($is_admin_path) {
				$t->set_template_path($settings["admin_templates_dir"]);
			}
		}
	}

	function unset_basket_tag()
	{
		global $t;
		$t->delete_var("basket_html");
		$t->delete_var("basket_text");
	}

	function set_order_items_tag($order_id, $type, $message)
	{
		set_order_tag($order_id, $type, $message, "order_items");
	}

	function set_order_tag($order_id, $type, $message, $tag_name)
	{
		global $db, $table_prefix, $settings, $language_code, $t, $is_admin_path, $is_sub_folder;
		if ($tag_name == "review_items") {
			$root_folder_path = ((isset($is_admin_path) && $is_admin_path) || (isset($is_sub_folder) && $is_sub_folder)) ? "../" : "./";
			include_once($root_folder_path."messages/".$language_code."/reviews_messages.php");
		}

		if (strpos($message, "{".$tag_name."}") !== false) {
			$site_url = get_setting_value($settings, "site_url", "");
			$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
			$friendly_extension = get_setting_value($settings, "friendly_extension", "");

			if ($is_admin_path) {
				$user_template_path = $settings["templates_dir"];
				if (preg_match("/^\.\//", $user_template_path)) {
					$user_template_path = str_replace("./", "../", $user_template_path);
				} elseif (!preg_match("/^\//", $user_template_path)) {
					$user_template_path = "../" . $user_template_path;
				}
				$t->set_template_path($user_template_path);
			}
			// get template for selected mail type
			if ($type) {
				$prefix = "html_"; 				
				$template_tag = $tag_name."_html";
				$template_name = "email_".$tag_name.".html";
			} else {
				$prefix = "text_";
				$template_tag = $tag_name."_html";
				$template_name = "email_".$tag_name.".txt";
			}
			if (!$t->block_exists($template_tag)) {
				$t->set_file($template_tag, $template_name);
			}

			$image_types = array("image_tiny", "image_small", "image_large", "image_super");

			// parse order items
			$goods_total = 0; $order_items = array();
			$t->set_var($prefix.$tag_name, "");
			$sql  = " SELECT oi.order_item_id,oi.top_order_item_id,oi.item_id,oi.item_user_id,oi.item_type_id,";
			$sql .= " oi.item_code,oi.manufacturer_code, oi.item_name, oi.downloadable, ";
			$sql .= " oi.price,oi.tax_id,oi.tax_free,oi.tax_percent,oi.discount_amount,oi.real_price, oi.weight, oi.actual_weight, ";
			$sql .= " oi.buying_price,oi.points_price,oi.reward_points,oi.reward_credits,oi.quantity, ";
			$sql .= " i.friendly_url,i.tiny_image, i.small_image, i.big_image, i.super_image ";
			$sql .= " FROM (" . $table_prefix . "orders_items oi ";
			$sql .= " LEFT JOIN " . $table_prefix . "items i ON oi.item_id=i.item_id) ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$order_item_id = $db->f("order_item_id");
				$order_items[$order_item_id] = $db->Record;
				// get product url 
				$item_id = $db->f("item_id");
				$friendly_url = $db->f("friendly_url");
				if ($friendly_urls && strlen($friendly_url)) {
					$item_url = $site_url.$friendly_url.$friendly_extension;
					$item_url_params = $item_url."?";
				} else {
					$item_url = $site_url."product_details.php?item_id=".urlencode($item_id);
					$item_url_params = $item_url."&";
				}
				$reviews_url = $site_url."reviews.php?item_id=".urlencode($item_id);
				$reviews_url_params = $reviews_url."&";
				$order_items[$order_item_id]["item_url"] = $item_url;
				$order_items[$order_item_id]["item_url_params"] = $item_url_params;
				$order_items[$order_item_id]["reviews_url"] = $reviews_url;
				$order_items[$order_item_id]["reviews_url_params"] = $reviews_url_params;
				$order_items[$order_item_id]["image_tiny"] = $order_items[$order_item_id]["tiny_image"];
				$order_items[$order_item_id]["image_small"] = $order_items[$order_item_id]["small_image"];
				$order_items[$order_item_id]["image_large"] = $order_items[$order_item_id]["big_image"];
				$order_items[$order_item_id]["image_super"] = $order_items[$order_item_id]["super_image"];
			}

			foreach($order_items as $id => $item) {
				$item_name = $item["item_name"];
				$item_url = $item["item_url"];
				$item_url_params = $item["item_url_params"];
				$reviews_url = $item["reviews_url"];
				$reviews_url_params = $item["reviews_url_params"];
				$item_code = $item["item_code"];
				$manufacturer_code = $item["manufacturer_code"];
				$price = $item["price"];
				$quantity = $item["quantity"];
				$item_total = $price * $quantity;
				$goods_total += $item_total;

				$t->set_var("item_name", htmlspecialchars($item_name));
				$t->set_var("item_url", htmlspecialchars($item_url));
				$t->set_var("item_url_params", htmlspecialchars($item_url_params));
				$t->set_var("review_url", htmlspecialchars($reviews_url));
				$t->set_var("review_url_params", htmlspecialchars($reviews_url_params));
				$t->set_var("reviews_url", htmlspecialchars($reviews_url));
				$t->set_var("reviews_url_params", htmlspecialchars($reviews_url_params));

				if (strlen($item_code)) {
					$t->set_var("item_code", $item_code);
					$t->sparse($prefix.$tag_name."_item_code", false);
				} else {
					$t->set_var($prefix.$tag_name."_item_code", false);
				}
				if (strlen($manufacturer_code)) {
					$t->set_var("manufacturer_code", $manufacturer_code);
					$t->sparse($prefix.$tag_name."_manufacturer_code", false);
				} else {
					$t->set_var($prefix.$tag_name."_manufacturer_code", false);
				}

				foreach ($image_types as $image_type) {
					$image_src = isset($item[$image_type]) ? $item[$image_type] : "";
					if (strlen($image_src)) {
						$image_url = $site_url.$image_src;
						$t->set_var("src", htmlspecialchars($image_url));
						$t->set_var($image_type, htmlspecialchars($image_url));
						$t->sparse($prefix.$tag_name."_".$image_type, false);
					} else {
						$t->set_var($prefix.$tag_name."_".$image_type, false);
					}
				}

				$t->set_var("price", currency_format($price));
				$t->set_var("item_price", currency_format($price));
				$t->set_var("quantity", $quantity);
				$t->set_var("item_quantity", $quantity);
				$t->set_var("item_total", currency_format($item_total));
				$t->sparse($prefix.$tag_name, true);
			}
			$t->set_var("goods_total", currency_format($goods_total));

			// set main order_items tag
			$t->parse($template_tag, false);
			$t->set_var($tag_name, $t->get_var($template_tag));

			if ($is_admin_path) {
				$t->set_template_path($settings["admin_templates_dir"]);
			}
		}
	}

	function set_items_tag($items, $type, $message, $tag_name)
	{
		global $settings, $t, $is_admin_path;
		if (strpos($message, "{".$tag_name."}") !== false) {
			if ($is_admin_path) {
				$user_template_path = $settings["templates_dir"];
				if (preg_match("/^\.\//", $user_template_path)) {
					$user_template_path = str_replace("./", "../", $user_template_path);
				} elseif (!preg_match("/^\//", $user_template_path)) {
					$user_template_path = "../" . $user_template_path;
				}
				$t->set_template_path($user_template_path);
			}
			// get template for selected mail type
			if ($type) {
				$prefix = "html_"; 				
				$block_tag = "html_".$tag_name;
				$template_tag = $tag_name."_html";
				$template_name = "email_".$tag_name.".html";
			} else {
				$prefix = "text_";
				$block_tag = "text_".$tag_name;
				$template_tag = $tag_name."_text";
				$template_name = "email_".$tag_name.".txt";
			}
			if (!$t->block_exists($template_tag)) {
				$t->set_file($template_tag, $template_name);
			}

			// parse order items
			$item_index = 0; $goods_total = 0; $affiliate_commission_sum = 0;
			$t->set_var($block_tag, "");
			foreach($items as $id => $item) {
				$item_index++;
				$item_index_2 = str_pad($item_index, 2, "0", STR_PAD_LEFT);
				$item_name = $item["item_name"];
				$item_code = $item["item_code"];
				$manufacturer_code = $item["manufacturer_code"];
				$price = $item["price"];
				$affiliate_commission = doubleval($item["affiliate_commission"]);
				$quantity = $item["quantity"];
				$item_total = $price * $quantity;
				$affiliate_commission_total = $affiliate_commission * $quantity;
				$goods_total += $item_total;
				$affiliate_commission_sum += $affiliate_commission_total;

				$t->set_var("item_index", $item_index);
				$t->set_var("item_index_2", $item_index_2);
				$t->set_var("item_name", $item_name);
				if (strlen($item_code)) {
					$t->set_var("item_code", $item_code);
					$t->sparse($block_tag."_item_code", false);
				} else {
					$t->set_var($block_tag."_item_code", false);
				}
				if (strlen($manufacturer_code)) {
					$t->set_var("manufacturer_code", $manufacturer_code);
					$t->sparse($block_tag."_manufacturer_code", false);
				} else {
					$t->set_var($block_tag."_manufacturer_code", false);
				}
				$t->set_var("item_properties", $item[$prefix."item_properties"]);
				$t->set_var("price", currency_format($price));
				$t->set_var("affiliate_commission", currency_format($affiliate_commission));
				$t->set_var("affiliate_commission_total", currency_format($affiliate_commission_total));
				$t->set_var("quantity", $quantity);
				$t->set_var("item_total", currency_format($item_total));
				$t->sparse($block_tag, true);
			}
			$t->set_var("goods_total", currency_format($goods_total));
			$t->set_var("affiliate_commission_sum", currency_format($affiliate_commission_sum));

			// set main file template tag and parse
			$t->parse($template_tag, false);
			$t->set_var($tag_name, $t->get_var($template_tag));

			if ($is_admin_path) {
				$t->set_template_path($settings["admin_templates_dir"]);
			}
		}
	}

	function cancel_subscription($order_item_id)
	{
		global $db, $table_prefix;

		$current_datetime = va_time();
		$current_date_ts = mktime (0, 0, 0, $current_datetime[MONTH], $current_datetime[DAY], $current_datetime[YEAR]);

		$sql  = " SELECT oi.order_id, oi.order_item_id, oi.item_name, oi.user_id, oi.user_type_id, ";
		$sql .= " oi.subscription_id, oi.price, oi.reward_credits, ";
		$sql .= " oi.subscription_start_date, oi.subscription_expiry_date ";
		$sql .= " FROM (" . $table_prefix . "orders_items oi ";
		$sql .= " INNER JOIN " . $table_prefix . "order_statuses os ON oi.item_status=os.status_id) ";
		$sql .= " WHERE order_item_id=" . $db->tosql($order_item_id, INTEGER);
		$sql .= " AND oi.is_subscription=1 ";
		$sql .= " AND os.paid_status=1 ";
		$sql .= " AND subscription_expiry_date>" . $db->tosql($current_date_ts, DATETIME);
		$db->query($sql);
		if ($db->next_record()) {
			$order_id = $db->f("order_id");
			$order_item_id = $db->f("order_item_id");
			$subscription_id = $db->f("subscription_id");
			$item_name = $db->f("item_name");
			$user_id = $db->f("user_id");
			$user_type_id = $db->f("user_type_id");
			$price = $db->f("price");
			$reward_credits = $db->f("reward_credits");
			$subscription_sd = $db->f("subscription_start_date", DATETIME);
			$subscription_ed = $db->f("subscription_expiry_date", DATETIME);
			$subscription_sd_ts = va_timestamp($subscription_sd);
			$subscription_ed_ts = va_timestamp($subscription_ed);
			$subscription_days = intval(($subscription_ed_ts - $subscription_sd_ts) / 86400); // get int value due to possible 1 hour difference
			// check days difference and add current day as well
			$used_days = intval(($current_date_ts - $subscription_sd_ts) / 86400) + 1;
			$sql  = " SELECT setting_value FROM " . $table_prefix . "user_types_settings ";
			$sql .= " WHERE type_id=" . $db->tosql($user_type_id, INTEGER);
			$sql .= " AND setting_name='cancel_subscription'";
			$cancel_subscription = get_db_value($sql);
			if ($cancel_subscription == 1) {
				// return money to credits balance
				$credits_return = round((($price - $reward_credits)/ $subscription_days) * ($subscription_days - $used_days), 2); 
			} else {
				$credits_return = 0; 
			}

			// cancel order subscription
			$new_reward_credits = $reward_credits + $credits_return;
			$sql  = " UPDATE " . $table_prefix . "orders_items ";
			$sql .= " SET is_recurring=0, is_subscription=0, ";
			$sql .= " reward_credits=" . $db->tosql($new_reward_credits, NUMBER) . ",";
			$sql .= " subscription_expiry_date=" . $db->tosql($current_date_ts, DATETIME);
			$sql .= " WHERE order_item_id=" . $db->tosql($order_item_id, INTEGER);
			$sql .= " AND is_subscription=1 ";
			$db->query($sql);

			// save event for subscription cancellation
			$r = new VA_Record($table_prefix . "orders_events");
			$r->add_textbox("order_id", INTEGER);
			$r->add_textbox("status_id", INTEGER);
			$r->add_textbox("admin_id", INTEGER);
			$r->add_textbox("order_items", TEXT);
			$r->add_textbox("event_date", DATETIME);
			$r->add_textbox("event_type", TEXT);
			$r->add_textbox("event_name", TEXT);
			$r->add_textbox("event_description", TEXT);

			// save subscription event
			$r->set_value("order_id", $order_id);
			$r->set_value("order_items", $order_item_id);
			$r->set_value("status_id", 0);
			$r->set_value("admin_id", get_session("session_admin_id"));
			$r->set_value("event_date", va_time());
			$r->set_value("event_type", "cancel_subscription");
			$r->set_value("event_name", $item_name);
			$r->insert_record();

			// update user commissions if reward credits amount changed
			if ($new_reward_credits != $reward_credits) {
				calculate_commissions_points($order_id, $order_item_id);
			}
		}
	}

	// new-spec begin
	function show_item_features($item_id, $type)
	{
		global $t, $db, $table_prefix;
		// clear all template blocks before parse
		$t->set_var("spec_groups", "");
		$t->set_var("spec_features", "");
		$t->set_var("specification_block", "");

		$sql  = " SELECT fg.group_id,fg.group_name,f.feature_name,f.feature_value ";
		$sql .= " FROM " . $table_prefix . "features f, " . $table_prefix . "features_groups fg ";
		$sql .= " WHERE f.group_id=fg.group_id ";
		$sql .= " AND f.item_id=" . intval($item_id);
		if ($type == "details") {
			$sql .= " AND fg.show_on_details=1 ";
			$sql .= " AND (f.show_on_details=1 OR f.show_as_group=1) ";
		} else if ($type == "basket") {
			$sql .= " AND fg.show_on_basket=1 ";
			$sql .= " AND (f.show_on_basket=1 OR f.show_as_group=1) ";
		} else if ($type == "checkout") {
			$sql .= " AND fg.show_on_checkout=1 ";
			$sql .= " AND (f.show_on_checkout=1 OR f.show_as_group=1) ";
		} else if ($type == "invoice") {
			$sql .= " AND fg.show_on_invoice=1 ";
			$sql .= " AND (f.show_on_invoice=1 OR f.show_as_group=1) ";
		}
		$sql .= " ORDER BY fg.group_order, f.feature_id ";
		$db->query($sql);
			if ($db->next_record()) {
				$last_group_id = $db->f("group_id");
				do {
					$group_id = $db->f("group_id");
					$group_name = get_translation($db->f("group_name"));
					$feature_name = get_translation($db->f("feature_name"));
					$feature_value = get_translation($db->f("feature_value"));
					if ($group_id != $last_group_id) {
						$t->set_var("group_name", $last_group_name);
						$t->sparse("spec_groups", true);
						$t->set_var("spec_features", "");
					}
      
					$t->set_var("group_name", $group_name);
					$t->set_var("feature_name", $feature_name);
					$t->set_var("feature_value", $feature_value);
					$t->sparse("spec_features", true);
      
					$last_group_id = $group_id;
					$last_group_name = $group_name;
				} while ($db->next_record());
				$t->set_var("group_name", $last_group_name);
				$t->sparse("spec_groups", true);
				$t->sparse("specification_block", false);

			} 
	}
	// new-spec end 


	function html_format_array($data, $type = "")
	{
		$html_data = ""; $type_messages = array(); $ignore_names = array();
		if (!is_array($data)) {
			$json_data = json_decode($data, true);
			if (is_array($json_data)) { $data = $json_data; }
		}
		if ($type == "vat_response") {
			$type_messages = array(
				"country_code" => va_message("COUNTRY_CODE_MSG"),
				"vat_number" => va_message("VAT_NUMBER_FIELD"),
				"name" => va_message("COMPANY_NAME_FIELD"),
				"address" => va_message("ADDRESS_MSG"),
				"error" => va_message("ERROR_MSG"),
				"message" => va_message("MESSAGE_MSG"),
				"warning" => va_message("WARNING_MSG"),
			);
			$ignore_names = array("valid" => 1);
		}

		if (is_array($data)) {
			foreach ($data as $data_name => $data_value) {
				$data_name = get_translation($data_name);
				if (isset($type_messages[$data_name])) {
					$data_name = $type_messages[$data_name];
				}
				$data_value = get_translation($data_value);
				if ($data_value && !isset($ignore_names[$data_name])) {
					$html_data .= "<div class=\"note\">";
					$html_data .= "<div class=\"name\">$data_name</div>";
					$html_data .= "<div class=\"desc\">".nl2br($data_value)."</div>";
					$html_data .= "</div>";
				}
			}
			$html_data .= "<div class=\"clear\"></div>";
		} else {
			$html_data = $data;
		}
		return $html_data;
	}

