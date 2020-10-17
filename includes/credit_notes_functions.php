<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  credit_notes_functions.php                               ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


function generate_credit_note($parent_order_id, $credit_notes_items, $other_statuses)
{
	global $t, $db, $table_prefix, $db_type, $settings;
	global $datetime_show_format, $vat_validation, $vat_obligatory_countries;
	global $credit_note_errors;

	// settings for errors notifications 
	$eol = get_eol();
	$recipients     = $settings["admin_email"];
	$email_headers  = "From: ". $settings["admin_email"] . $eol;
	$email_headers .= "Content-Type: text/plain";
	$credit_note_error = false;

	// credit note settings
	$preserve_item_options = true; 
	$preserve_cart_options = ($other_statuses) ? false : true;
	$preserve_shipping = ($other_statuses) ? false : true;
	$preserve_processing_fee = ($other_statuses) ? false : true;
	$price_multiplier = -1; // option to save order with negative prices

	// initialize data for credit note
	$tax_prices_type_global = get_setting_value($settings, "tax_prices_type", 0);
	$order_params = array(
		"site_id" => INTEGER, "user_id" => INTEGER, "user_type_id" => INTEGER, "visit_id" => INTEGER,
		"remote_address" => TEXT, "initial_ip" => TEXT, "cookie_ip" => TEXT,
		"default_currency_code" => TEXT, "currency_code" => TEXT, 
		"tax_name" => TEXT, "tax_percent" => NUMBER, "tax_total" => NUMBER, "tax_prices_type" => INTEGER,
		"name" => TEXT, "first_name" => TEXT, "last_name" => TEXT,
		"company_id" => INTEGER, "company_name" => TEXT, "email" => TEXT,
		"address1" => TEXT, "address2" => TEXT, "city" => TEXT,
		"province" => TEXT, "state_id" => INTEGER, "state_code" => TEXT, "zip" => TEXT,
		"country_id" => INTEGER, "country_code" => TEXT, "phone" => TEXT, "daytime_phone" => TEXT,
		"evening_phone" => TEXT, "cell_phone" => TEXT, "fax" => TEXT,
		"delivery_name" => TEXT, "delivery_first_name" => TEXT, "delivery_last_name" => TEXT,
		"delivery_company_id" => INTEGER, "delivery_company_name" => TEXT, "delivery_email" => TEXT,
		"delivery_address1" => TEXT, "delivery_address2" => TEXT, "delivery_city" => TEXT,
		"delivery_province" => TEXT, "delivery_state_id" => INTEGER, "delivery_state_code" => TEXT,
		"delivery_zip" => TEXT,
		"delivery_country_id" => INTEGER, "delivery_country_code" => TEXT, 
		"delivery_phone" => TEXT, "delivery_daytime_phone" => TEXT,
		"delivery_evening_phone" => TEXT, "delivery_cell_phone" => TEXT, "delivery_fax" => TEXT,
	);
  //success_message, pending_message, error_message
	$r = new VA_Record($table_prefix . "orders");

	$r->add_where("order_id", INTEGER);
	$r->add_textbox("parent_order_id", INTEGER);
	$r->add_textbox("invoice_number", TEXT);
	$r->change_property("invoice_number", USE_SQL_NULL, false); // save credit note number here
	$r->add_textbox("transaction_id", TEXT);

	$r->add_textbox("currency_rate", NUMBER);
	$r->add_textbox("currency_rate", NUMBER);
	$r->add_textbox("order_status", INTEGER);
	$r->set_value("order_status", 0);
	$r->add_textbox("total_buying", NUMBER);
	$r->add_textbox("total_merchants_commission", NUMBER);
	$r->set_value("total_merchants_commission", 0);
	$r->add_textbox("total_affiliate_commission", NUMBER);
	$r->set_value("total_affiliate_commission", 0);

	$r->add_textbox("goods_total", NUMBER);
	$r->add_textbox("goods_incl_tax", NUMBER);
	$r->add_textbox("goods_points_amount", NUMBER);
	$r->set_value("goods_points_amount", 0);
	$r->add_textbox("total_quantity", INTEGER);
	$r->add_textbox("weight_total", NUMBER);
	$r->set_value("weight_total", 0);
	$r->add_textbox("actual_weight_total", NUMBER);
	$r->set_value("actual_weight_total", 0);
	$r->add_textbox("total_discount", NUMBER);
	$r->add_textbox("total_discount_tax", NUMBER);

	$r->add_textbox("shipping_type_id", INTEGER);
	$r->add_textbox("shipping_type_code", TEXT);
	$r->add_textbox("shipping_type_desc", TEXT);
	$r->add_textbox("shipping_cost", NUMBER);
	$r->add_textbox("shipping_taxable", INTEGER);
	$r->add_textbox("shipping_points_amount", NUMBER);
	$r->set_value("shipping_points_amount", 0);

	$r->add_textbox("properties_total", NUMBER);
	$r->add_textbox("properties_taxable", NUMBER);

	$r->add_textbox("credit_amount", NUMBER); 
	$r->add_textbox("processing_fee", NUMBER);
	$r->add_textbox("order_total", NUMBER);
	$r->add_textbox("total_points_amount", NUMBER);
	$r->add_textbox("total_reward_points", NUMBER);
	$r->add_textbox("total_reward_credits", NUMBER);
	$r->set_value("total_points_amount", 0);
	$r->set_value("total_reward_points", 0);
	$r->set_value("total_reward_credits", 0);
	$r->add_textbox("order_placed_date", DATETIME);

	$r->add_textbox("modified_date", DATETIME);

	$r->add_textbox("is_placed", INTEGER);
	$r->set_value("is_placed", 0);
	$r->add_textbox("is_exported", INTEGER);
	$r->set_value("is_exported", 0);
	$r->add_textbox("is_call_center", INTEGER);
	$r->set_value("is_call_center", 0);
	$r->add_textbox("is_recurring", INTEGER);
	$r->set_value("is_recurring", 0);

	foreach ($order_params as $parameter_name => $parameter_type) {
		$r->add_textbox($parameter_name, $parameter_type);
	}
	$r->change_property("visit_id", USE_SQL_NULL, false);
	$r->change_property("affiliate_code", USE_SQL_NULL, false);
	$r->change_property("affiliate_user_id", USE_SQL_NULL, false);
	$r->change_property("keywords", USE_SQL_NULL, false);
	$r->change_property("name", USE_SQL_NULL, false);
	$r->change_property("first_name", USE_SQL_NULL, false);
	$r->change_property("last_name", USE_SQL_NULL, false);
	$r->change_property("email", USE_SQL_NULL, false);

	// order items fields
	$oi = new VA_Record($table_prefix . "orders_items");
	$oi->add_where("order_item_id", INTEGER);
	$oi->add_textbox("parent_order_item_id", INTEGER);
	$oi->add_textbox("order_id", INTEGER);
	$oi->add_textbox("site_id", INTEGER);
	$oi->add_textbox("item_id", INTEGER);
	$oi->add_textbox("parent_item_id", INTEGER);
	$oi->add_textbox("user_id", INTEGER);
	$oi->add_textbox("user_type_id", INTEGER);
	$oi->add_textbox("subscription_id", INTEGER);
	$oi->change_property("subscription_id", USE_SQL_NULL, false);
	$oi->add_textbox("cart_item_id", INTEGER);
	$oi->change_property("cart_item_id", USE_SQL_NULL, false);
	$oi->add_textbox("item_user_id", INTEGER);
	$oi->change_property("item_user_id", USE_SQL_NULL, false);
	$oi->add_textbox("affiliate_user_id", INTEGER);
	$oi->change_property("affiliate_user_id", USE_SQL_NULL, false);
	$oi->add_textbox("friend_user_id", INTEGER);
	$oi->change_property("friend_user_id", USE_SQL_NULL, false);
	$oi->add_textbox("item_type_id", INTEGER);
	$oi->add_textbox("item_code", TEXT);
	$oi->change_property("item_code", USE_SQL_NULL, false);
	$oi->add_textbox("manufacturer_code", TEXT);
	$oi->add_textbox("supplier_id", INTEGER);
	$oi->change_property("supplier_id", USE_SQL_NULL, false);
	$oi->add_textbox("coupons_ids", TEXT);
	$oi->add_textbox("item_status", INTEGER);
	$oi->set_value("item_status", 0);
	$oi->add_textbox("component_name", TEXT);
	$oi->add_textbox("component_order", INTEGER);
	$oi->add_textbox("item_name", TEXT);
	$oi->add_textbox("item_properties", TEXT);
	$oi->add_textbox("buying_price", NUMBER);
	$oi->add_textbox("real_price", NUMBER);
	$oi->add_textbox("discount_amount", NUMBER);
	$oi->set_value("discount_amount", 0);
	$oi->add_textbox("price", NUMBER);
	$oi->add_textbox("tax_id", INTEGER);
	$oi->add_textbox("tax_free", INTEGER);
	$oi->add_textbox("tax_percent", NUMBER);
	$oi->add_textbox("points_price", NUMBER);
	$oi->add_textbox("reward_points", NUMBER);
	$oi->add_textbox("reward_credits", NUMBER);
	$oi->set_value("points_price", 0);
	$oi->set_value("reward_points", 0);
	$oi->set_value("reward_credits", 0);
	$oi->add_textbox("merchant_commission", NUMBER);
	$oi->set_value("merchant_commission", 0);
	$oi->add_textbox("affiliate_commission", NUMBER);
	$oi->set_value("affiliate_commission", 0);
	$oi->add_textbox("weight", NUMBER);
	$oi->set_value("weight", 0);
	$oi->add_textbox("actual_weight", NUMBER);
	$oi->set_value("actual_weight", 0);
	$oi->add_textbox("quantity", NUMBER);
	$oi->add_textbox("downloadable", NUMBER);
	$oi->set_value("downloadable", 0);
	$oi->add_textbox("is_shipping_free", INTEGER);
	$oi->add_textbox("shipping_cost", NUMBER);

	// order items properties fields
	$oip = new VA_Record($table_prefix . "orders_items_properties");
	$oip->add_textbox("order_id", INTEGER);
	$oip->add_textbox("order_item_id", INTEGER);
	$oip->add_textbox("property_id", INTEGER);
	$oip->add_textbox("property_order", INTEGER);
	$oip->add_textbox("property_name", TEXT);
	$oip->add_textbox("hide_name", INTEGER);
	$oip->add_textbox("property_value", TEXT);
	$oip->add_textbox("property_values_ids", TEXT);
	$oip->add_textbox("length_units", TEXT);
	$oip->add_textbox("additional_price", FLOAT);
	$oip->add_textbox("additional_weight", FLOAT);
	$oip->add_textbox("actual_weight", FLOAT);

	// order properies fields
	$op = new VA_Record($table_prefix . "orders_properties");
	$op->add_textbox("order_id", INTEGER);
	$op->add_textbox("property_id", INTEGER);
	$op->add_textbox("property_order", INTEGER);
	$op->add_textbox("property_type", INTEGER);
	$op->add_textbox("property_name", TEXT);
	$op->add_textbox("property_value", TEXT);
	$op->add_textbox("property_price", FLOAT);
	$op->add_textbox("property_points_amount", FLOAT);
	$op->set_value("property_points_amount", 0);
	$op->add_textbox("property_weight", FLOAT);
	$op->set_value("property_weight", 0);
	$op->add_textbox("actual_weight", FLOAT);
	$op->set_value("actual_weight", 0);
	$op->add_textbox("tax_free", INTEGER);

	// order shipments fields
	$os = new VA_Record($table_prefix . "orders_shipments");
	$os->add_textbox("order_id", INTEGER);
	$os->add_textbox("shipping_id", INTEGER);
	$os->add_textbox("shipping_code", TEXT);
	$os->add_textbox("shipping_desc", TEXT);
	$os->add_textbox("shipping_cost", FLOAT);
	$os->add_textbox("points_cost", FLOAT);
	$os->add_textbox("tax_free", INTEGER);
	$os->add_textbox("tracking_id", TEXT);
	$os->add_textbox("expecting_date", DATETIME);
	$os->add_textbox("goods_weight", NUMBER);
	$os->add_textbox("actual_goods_weight", NUMBER);
	$os->add_textbox("tare_weight", NUMBER);

	$order_items = array(); $error_message = ""; $success_message = "";

	$sql  = " SELECT oi.* ";
	$sql .= " FROM (" . $table_prefix . "orders_items oi ";
	$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON os.status_id=oi.item_status) ";
	$sql .= " WHERE oi.order_id=" . $db->tosql($parent_order_id, INTEGER);
	$sql .= " AND oi.order_item_id IN (" . $db->tosql($credit_notes_items, INTEGERS_LIST) . ") ";
	$db->query($sql);
	if ($db->next_record()) {
		do {
			$order_id = $db->f("order_id");
			$order_item_id = $db->f("order_item_id");

			$order_items[] = array(
				"order_id" => $db->f("order_id"), 
				"site_id" => $db->f("site_id"), 
				"order_item_id" => $db->f("order_item_id"),
				"item_id" => $db->f("item_id"),
				"user_id" => $db->f("user_id"),
				"user_type_id" => $db->f("user_type_id"),
				"subscription_id" => $db->f("subscription_id"),
				"item_user_id" => $db->f("item_user_id"),
				"parent_item_id" => $db->f("parent_item_id"),
				"item_type_id" => $db->f("item_type_id"),
				"item_code" => $db->f("item_code"),
				"manufacturer_code" => $db->f("manufacturer_code"),
				"item_name" => $db->f("item_name"),
				"item_properties" => $db->f("item_properties"),
				"buying_price" => $price_multiplier * $db->f("buying_price"),
				"real_price" => $price_multiplier * $db->f("real_price"),
				"price" => $price_multiplier * $db->f("price"),
				"tax_id" => $db->f("tax_id"),
				"tax_free" => $db->f("tax_free"),
				"tax_percent" => 0,
				"quantity" => $db->f("quantity"),
				"is_shipping_free" => $db->f("is_shipping_free"),
				"shipping_cost" => $price_multiplier * $db->f("shipping_cost"),
			);

		} while ($db->next_record());
	}


	$sql = " SELECT * FROM " . $table_prefix . "orders WHERE order_id=" . $db->tosql($parent_order_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$r->set_value("parent_order_id", $parent_order_id);
		foreach ($order_params as $parameter_name => $parameter_type) {
			$r->set_value($parameter_name, $db->f($parameter_name, $parameter_type));
		}
		$tax_prices_type = $r->get_value("tax_prices_type");
		if (!strlen($tax_prices_type)) {
			$tax_prices_type = $tax_prices_type_global;
			$r->set_value("tax_prices_type", $tax_prices_type);
		}

		$parent_shipping_type_id = $db->f("shipping_type_id");
		$email = $r->get_value("email");
		$delivery_email = $r->get_value("delivery_email");;
		$user_email = strlen($email) ? $email : $delivery_email;
		$user_id = $r->get_value("user_id");
		$user_type_id = $r->get_value("user_type_id");

		// check for shipping method
		$shipping_type_id = ""; $shipping_type_code = ""; $shipping_type_desc = ""; $shipping_cost = 0;
		$shipping_taxable = 0; $shipping_tax = 0; 
		if ($preserve_shipping) {
			$shipping_type_id = $db->f("shipping_type_id");
			$shipping_type_code = $db->f("shipping_type_code");
			$shipping_type_desc = $db->f("shipping_type_desc");
			$shipping_cost = $price_multiplier * $db->f("shipping_cost");
			$shipping_taxable = $db->f("shipping_taxable");
		}
		// check processing fee
		$processing_fee = 0;
		if($preserve_processing_fee) {
			$processing_fee = $price_multiplier * $db->f("processing_fee");
		}

		
		$default_currency_code = get_db_value("SELECT currency_code FROM ".$table_prefix."currencies WHERE is_default=1");
		$r->set_value("default_currency_code", $default_currency_code);

		$currency = get_currency($r->get_value("currency_code"));
		$r->set_value("currency_code", $currency["code"]);
		$r->set_value("currency_rate", $currency["rate"]);


		// check main delivery details
		if ($r->get_value("delivery_country_id")) {
			$country_id = $r->get_value("delivery_country_id");
		} else {
			$country_id = $r->get_value("country_id");
		}
		if ($r->get_value("delivery_state_id")) {
			$state_id = $r->get_value("delivery_state_id");
		} else {
			$state_id = $r->get_value("state_id");
		}
		if ($r->get_value("delivery_zip")) {
			$postal_code = $r->get_value("delivery_zip");
		} else {
			$postal_code = $r->get_value("zip");
		}
		// check $country_code and $state_code variables
		$sql = "SELECT country_code FROM " . $table_prefix . "countries WHERE country_id=".$db->tosql($country_id,INTEGER,true,false);
		$country_code = get_db_value($sql);
		$sql = "SELECT state_code FROM " . $table_prefix . "states WHERE state_id=".$db->tosql($state_id,INTEGER,true,false);
		$state_code = get_db_value($sql);

		// as order was submitted before we don't check user tax free option and set it to false
		$tax_free = false;
		// get order tax rates
		$tax_available = false; $tax_percent_sum = 0; $tax_names = "";	$tax_column_names = "";	$taxes_total = 0; 
		$order_tax_rates = order_tax_rates($parent_order_id);
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

		$r->set_value("tax_name", $tax_names);
		$r->set_value("tax_percent", $tax_percent_sum);

		// calculate summary for order
		$total_buying = 0;
		$goods_total = 0; $goods_incl_tax = 0; $goods_tax_total = 0;
		$total_discount = 0; $total_discount_tax = 0;
		$properties_total = 0; $properties_taxable = 0;
		$order_total = 0;
		$properties_total = 0; $properties_tax_total = 0; $properties_taxable = 0;
		$shipping_items_total = 0; $total_quantity = 0; $shipping_quantity = 0;
	  
		for($i = 0; $i < sizeof($order_items); $i++)
		{
			$order_item = $order_items[$i];
	  
			$item_type_id = $order_item["item_type_id"];
			$buying_price = $order_item["buying_price"];

			$price = $order_item["price"];
			$item_tax_id = $order_item["tax_id"];
			$item_tax_free = $order_item["tax_free"];
			$item_tax_percent = $order_item["tax_percent"];
			if ($tax_free) { 
				$item_tax_free = $tax_free; 
				$order_items[$i]["tax_free"] = $tax_free;
				$order_items[$i]["tax_percent"] = 0;
			}

			$quantity = $order_item["quantity"];
			$item_shipping_cost = $order_item["shipping_cost"];
			$is_shipping_free = $order_item["is_shipping_free"];
			if ($is_shipping_free) { $item_shipping_cost = 0; }
	  
			$total_buying += ($buying_price * $quantity);
			$item_total = $price * $quantity;
			$goods_total += $item_total;
	  
			if ($tax_available && !$item_tax_free) {
				$item_tax_total = get_tax_amount($order_tax_rates, $item_type_id, $item_total, $quantity, $item_tax_id, $item_tax_free, $item_tax_percent, "", 1, $tax_prices_type);
				$goods_tax_total += $item_tax_total;
				$order_items[$i]["tax_free"] = $item_tax_free;
				$order_items[$i]["tax_percent"] = $item_tax_percent;
			}
	  
			$total_quantity += $quantity;
			if (!$is_shipping_free) {
				$shipping_quantity += $quantity;
				$shipping_items_total += ($item_shipping_cost * $quantity); 
			}  
		}

		// cart properties
		$custom_options = array();
		$sql  = " SELECT * FROM " . $table_prefix . "orders_properties ";
		$sql .= " WHERE order_id=" . $db->tosql($parent_order_id, INTEGER);
		// ignoring type for payment details - 4
		$sql .= " AND (property_type IN (2,3)";
		if ($preserve_cart_options) {
			$sql .= " OR property_type=1 ";
		}
		$sql .= " )";
		$db->query($sql);
		while ($db->next_record()) {
			$order_property_id = $db->f("order_property_id");
			$property_id = $db->f("property_id");
			$property_type = $db->f("property_type");
			$property_order = $db->f("property_order");
			$property_name = $db->f("property_name");
			$property_value = $db->f("property_value");
			$property_price = $price_multiplier * $db->f("property_price");
			$property_tax_free = $db->f("tax_free");
			if ($tax_free) { $property_tax_free = true; }

			$property_tax_id = 0;
			$property_tax_percent = $tax_percent_sum;
			$properties_total += $property_price;
			if (!$property_tax_free) {
				$property_tax = get_tax_amount($order_tax_rates, 0, $property_price, 1, $property_tax_id, $property_tax_free, $property_tax_percent, "", 1, $tax_prices_type);
				$properties_taxable += $property_price;
				$properties_tax_total += $property_tax;
			}
			//todo
			//$default_tax_rates = "", $return_type = 1, $tax_prices_type

			$custom_options[] = array(
				"property_id" => $property_id, "type" => $property_type, "type" => $property_type, 
				"order" => $property_order, "name" => $property_name, 
				"value" => $property_value, "price" => $property_price, "tax_free" => $property_tax_free,
				"points_amount" => 0, "weight" => 0, "actual_weight" => 0
			);
		}



		// order shipments 
		$order_shipments = array();
		if ($preserve_shipping) {
			$sql  = " SELECT * FROM " . $table_prefix . "orders_shipments ";
			$sql .= " WHERE order_id=" . $db->tosql($parent_order_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$shipping_id = $db->f("shipping_id");
				$shipping_code = $db->f("shipping_code");
				$shipping_desc = $db->f("shipping_desc");
				$shipping_cost = $price_multiplier * $db->f("shipping_cost");
				$shipping_tax_free = $db->f("tax_free");
				if ($tax_free) { $shipping_tax_free = true; }
				$goods_weight = $db->f("goods_weight");
				$actual_goods_weight = $db->f("actual_goods_weight");
				$tare_weight = $db->f("tare_weight");

				$order_shipments[] = array(
					"shipping_id" => $shipping_id, "shipping_code" => $shipping_code, "shipping_desc" => $shipping_desc, 
					"shipping_cost" => $shipping_cost,  "tax_free" => $shipping_tax_free,
					"goods_weight" => $goods_weight, "actual_goods_weight" => $actual_goods_weight, "tare_weight" => $tare_weight,
				);
			}
		}
		// end order shipments 


		// calculate shipping tax
		$shipping_tax_id = 0;
		$shipping_tax_free = (!$shipping_taxable || $tax_free);
		$shipping_tax = get_tax_amount($order_tax_rates, "shipping", $shipping_cost, 1, $shipping_tax_id, $shipping_tax_free, $shipping_tax_percent, "", 1, $tax_prices_type);
		$shipping_tax_values = get_tax_amount($order_tax_rates, "shipping", $shipping_cost, 1, $shipping_tax_id, $shipping_tax_free, $shipping_tax_percent, "", 2, $tax_prices_type);

		
		// calculate order total information
		$tax_total = $goods_tax_total + $properties_tax_total + $shipping_tax;
		$order_total = round($goods_total, 2) + round($properties_total, 2) + round($shipping_cost, 2);
		if ($tax_prices_type != 1) {
			$order_total += round($tax_total, 2);
		}
		$order_total += $processing_fee;
	  
		$r->set_value("total_buying", $total_buying);
		$r->set_value("goods_total", $goods_total);
		$r->set_value("goods_incl_tax", $goods_incl_tax);

		$r->set_value("properties_total", $properties_total);
		$r->set_value("properties_taxable", $properties_taxable);

		$r->set_value("total_quantity", $total_quantity);

		$r->set_value("shipping_type_id", $shipping_type_id);
		$r->set_value("shipping_type_code", $shipping_type_code);
		$r->set_value("shipping_type_desc", $shipping_type_desc);
		$r->set_value("shipping_cost", $shipping_cost);
		$r->set_value("shipping_taxable", $shipping_taxable);

		$r->set_value("tax_total", $tax_total);
		$r->set_value("processing_fee", $processing_fee);
		$r->set_value("order_total", $order_total);


		// insert credit note order
		if ($db_type == "postgre") {
			$new_order_id = get_db_value(" SELECT NEXTVAL('seq_" . $table_prefix . "orders') ");
			$r->change_property("order_id", USE_IN_INSERT, true);
			$r->set_value("order_id", $new_order_id);
		}
		$order_placed_date = va_time();
		$order_placed_date_string = va_date($datetime_show_format, $order_placed_date);
		$r->set_value("order_placed_date", $order_placed_date);

		if($r->insert_record()) 
		{
			if ($db_type == "mysql") {
				$new_order_id = get_db_value(" SELECT LAST_INSERT_ID() ");
				$r->set_value("order_id", $new_order_id);
			} else if ($db_type == "access") {
				$new_order_id = get_db_value(" SELECT @@IDENTITY ");
				$r->set_value("order_id", $new_order_id);
			} else if ($db_type == "db2") {
				$new_order_id = get_db_value(" SELECT PREVVAL FOR seq_" . $table_prefix . "orders FROM " . $table_prefix . "orders");
				$r->set_value("order_id", $new_order_id);
			}
			$vc = md5($new_order_id . $order_placed_date[3].$order_placed_date[4].$order_placed_date[5]);

			// generate credit note number
			$invoice_number = generate_invoice_number($new_order_id, "credit");
			$variables["invoice_number"] = $invoice_number;

			// save tax rates for submitted order
			if ($tax_available && is_array($order_tax_rates)) {
				$ot = new VA_Record($table_prefix . "orders_taxes");
				$ot->add_where("order_tax_id", INTEGER);
				$ot->add_textbox("order_id", INTEGER);
				$ot->set_value("order_id", $new_order_id);
				$ot->add_textbox("tax_id", INTEGER);
				$ot->add_textbox("tax_type", INTEGER);
				$ot->add_textbox("show_type", INTEGER);
				$ot->add_textbox("tax_name", TEXT);
				$ot->add_textbox("tax_percent", FLOAT);
				$ot->add_textbox("fixed_amount", FLOAT);
				$ot->add_textbox("shipping_tax_percent", FLOAT);
				$ot->add_textbox("shipping_fixed_amount", FLOAT);

				$oit = new VA_Record($table_prefix . "orders_items_taxes");
				$oit->add_textbox("order_tax_id", INTEGER);
				$oit->add_textbox("item_type_id", INTEGER);
				$oit->add_textbox("tax_percent", FLOAT);
				$oit->add_textbox("fixed_amount", FLOAT);

				foreach ($order_tax_rates as $tax_id => $tax_rate) {
					if ($db_type == "postgre") {
						$order_tax_id = get_db_value(" SELECT NEXTVAL('seq_" . $table_prefix . "orders_taxes') ");
						$r->change_property("order_tax_id", USE_IN_INSERT, true);
						$r->set_value("order_tax_id", $order_tax_id);
					}
					$ot->set_value("tax_id", $tax_id);
					$ot->set_value("tax_type", $tax_rate["tax_type"]);
					$ot->set_value("show_type", $tax_rate["show_type"]);
					$ot->set_value("tax_name", $tax_rate["tax_name"]);
					$ot->set_value("tax_percent", $tax_rate["tax_percent"]);
					$ot->set_value("fixed_amount", $tax_rate["fixed_amount"]);
					$ot->set_value("shipping_tax_percent", $tax_rate["types"]["shipping"]["tax_percent"]);
					$ot->set_value("shipping_fixed_amount", $tax_rate["types"]["shipping"]["fixed_amount"]);
					if ($ot->insert_record()) {
						// save taxes for item types if they available
						$tax_types = isset($tax_rate["types"]) ? $tax_rate["types"] : "";
						if (is_array($tax_types)) {
							if ($db_type == "mysql") {
								$order_tax_id = get_db_value(" SELECT LAST_INSERT_ID() ");
							} elseif ($db_type == "access") {
								$order_tax_id = get_db_value(" SELECT @@IDENTITY ");
							} elseif ($db_type == "db2") {
								$order_tax_id = get_db_value(" SELECT PREVVAL FOR seq_" . $table_prefix . "orders_taxes FROM " . $table_prefix . "orders_taxes");
							}
							$oit->set_value("order_tax_id", $order_tax_id);
							foreach ($tax_types as $item_type => $tax_type) {
								if (is_numeric($item_type)) {
									$oit->set_value("item_type_id", $item_type);
									$oit->set_value("tax_percent", $tax_type["tax_percent"]);
									$oit->set_value("fixed_amount", $tax_type["fixed_amount"]);
									$oit->insert_record();
								}
							}
						}
					}
				} 
			} // end of saving order taxes rules

			// add orders items
			for($i = 0; $i < sizeof($order_items); $i++)
			{
				$order_item = $order_items[$i];
				$order_item_id = $order_item["order_item_id"];

				$oi->set_value("parent_order_item_id", $order_item_id);
				$oi->set_value("order_id", $new_order_id);

				$oi->set_value("site_id", $order_item["site_id"]);
				$oi->set_value("item_id", $order_item["item_id"]);
				$oi->set_value("parent_item_id", $order_item["parent_item_id"]);
				$oi->set_value("user_id", $order_item["user_id"]);
				$oi->set_value("user_type_id", $order_item["user_type_id"]);
				$oi->set_value("subscription_id", $order_item["subscription_id"]);
				$oi->set_value("item_user_id", $order_item["item_user_id"]);
				$oi->set_value("item_type_id", $order_item["item_type_id"]);
				$oi->set_value("item_code", $order_item["item_code"]);
				$oi->set_value("manufacturer_code", $order_item["manufacturer_code"]);
				$oi->set_value("item_name", $order_item["item_name"]);
				$oi->set_value("item_properties", $order_item["item_properties"]);
				$oi->set_value("buying_price", $order_item["buying_price"]);
				$oi->set_value("real_price", $order_item["real_price"]);
				$oi->set_value("price", $order_item["price"]);
				$oi->set_value("tax_id", $order_item["tax_id"]);
				$oi->set_value("tax_free", $order_item["tax_free"]);
				$oi->set_value("tax_percent", $order_item["tax_percent"]);
				$oi->set_value("quantity", $order_item["quantity"]);
				$oi->set_value("is_shipping_free", $order_item["is_shipping_free"]);
				$oi->set_value("shipping_cost", $order_item["shipping_cost"]);

				if ($db_type == "postgre") {
					$new_order_item_id = get_db_value(" SELECT NEXTVAL('seq_" . $table_prefix . "orders_items') ");
					$oi->change_property("order_item_id", USE_IN_INSERT, true);
					$oi->set_value("order_item_id", $new_order_item_id);
				}
	    
				if($oi->insert_record())
				{
					if($db_type == "mysql") {
						$new_order_item_id = get_db_value(" SELECT LAST_INSERT_ID() ");
						$oi->set_value("order_item_id", $new_order_item_id);
					} else if ($db_type == "access") {
						$new_order_item_id = get_db_value(" SELECT @@IDENTITY ");
						$oi->set_value("order_item_id", $new_order_item_id);
					}
					// add product options if preserve option is set
					if ($preserve_item_options) {
						$items_properties = array(); // clear array
						$sql  = " SELECT * FROM " . $table_prefix . "orders_items_properties ";
						$sql .= " WHERE order_item_id=" . $db->tosql($order_item_id, INTEGER);
						$db->query($sql);
						while ($db->next_record()) {
							$items_properties[]	= array(
								"property_id" => $db->f("property_id"), 
								"property_order" => $db->f("property_order"), 
								"property_name" => $db->f("property_name"), 
								"hide_name" => $db->f("hide_name"), 
								"property_value" => $db->f("property_value"), 
								"property_values_ids" => $db->f("property_values_ids"), 
								"length_units" => $db->f("length_units"), 
								"additional_price" => $db->f("additional_price"), 
								"additional_weight" => $db->f("additional_weight"), 
								"actual_weight" => $db->f("actual_weight"), 
							);
						}

						for ($ip = 0; $ip < sizeof($items_properties); $ip++) {
							$item_property = $items_properties[$ip];
							$oip->set_value("order_id", $new_order_id);
							$oip->set_value("order_item_id", $new_order_item_id);
							$oip->set_value("property_id", $item_property["property_id"]);
							$oip->set_value("property_order", $item_property["property_order"]);
							$oip->set_value("property_name", $item_property["property_name"]);
							$oip->set_value("hide_name", $item_property["hide_name"]);
							$oip->set_value("property_value", $item_property["property_value"]);
							$oip->set_value("property_values_ids", $item_property["property_values_ids"]);
							$oip->set_value("length_units", $item_property["length_units"]);
							$oip->set_value("additional_price", $item_property["additional_price"]);
							$oip->set_value("additional_weight", $item_property["additional_weight"]);
							$oip->set_value("actual_weight", $item_property["actual_weight"]);
							$oip->insert_record();
						}
					}
				}
			}
			// end of adding items

			// adding order custom fields values 
			foreach ($custom_options as $key => $property_info) {
				$property_id = $property_info["property_id"];
				$property_type = $property_info["type"];
				$property_order = $property_info["order"];
				$property_name = $property_info["name"];
				$property_value = $property_info["value"];
				$property_price = $property_info["price"];
				$property_tax_free = $property_info["tax_free"];

				$t->set_var("field_name_" . $property_id, $property_name);
				$t->set_var("field_value_" . $property_id, $property_value);
				$t->set_var("field_price_" . $property_id, $property_price);
				$t->set_var("field_" . $property_id, $property_value);
				$op->set_value("order_id", $new_order_id);
				$op->set_value("property_id", $property_id);
				$op->set_value("property_order", $property_order);
				$op->set_value("property_type", $property_type);
				$op->set_value("property_name", $property_name);
				$op->set_value("property_value", $property_value);
				$op->set_value("property_price", $property_price);
				$op->set_value("tax_free", $property_tax_free);

				$op->insert_record();
			}
			// end of adding custom order values 

			// adding order shipments 
			foreach ($order_shipments as $key => $shipment_info) {
				$shipping_id = $shipment_info["shipping_id"];
				$shipping_code = $shipment_info["shipping_code"];
				$shipping_desc = $shipment_info["shipping_desc"];
				$shipping_cost = $shipment_info["shipping_cost"];
				$goods_weight = $shipment_info["goods_weight"];
				$actual_goods_weight = $shipment_info["actual_goods_weight"];
				$tare_weight = $shipment_info["tare_weight"];
				$shipping_tax_free = $shipment_info["tax_free"];

				$os->set_value("order_id", $new_order_id);
				$os->set_value("shipping_id", $shipping_id);
				$os->set_value("shipping_code", $shipping_code);
				$os->set_value("shipping_desc", $shipping_desc);
				$os->set_value("shipping_cost", $shipping_cost);
				$os->set_value("tax_free", $shipping_tax_free);
				$os->set_value("goods_weight", $goods_weight);
				$os->set_value("actual_goods_weight", $actual_goods_weight);
				$os->set_value("tare_weight", $tare_weight);

				$os->insert_record();
			}
			// end of adding order shipments 

			// recalculate total values for order
			recalculate_order($new_order_id);

			// check CREDIT_NOTE or NEW status to set it to order
			$sql = " SELECT status_id FROM " . $table_prefix . "order_statuses WHERE status_type='CREDIT_NOTE' ";
			$credit_note_status = get_db_value($sql);
    
			// update credit note order status
			if (strlen($credit_note_status)) {
				update_order_status($new_order_id, $credit_note_status, false, "", $status_error);
			}

			// prepare data for sending notifications if there will be such option somewhere
			$r->set_parameters();
			$t->set_var("vc", $vc);

			// delete basket tags for current order
			unset_basket_tag();

		} else {
			$credit_note_error = true;
			$error_subject = "CREDIT NOTE ERROR";
			$error_body = CANT_ADD_CREDIT_NOTE_MSG . $eol;
		}

	} else {
		$credit_note_error = true;
		$error_subject = "CREDIT NOTE ERROR";
		$error_body = PARENT_ORDER_WASNT_FOUND_MSG . $eol;
	}
	
	if ($credit_note_error) {
		$credit_note_errors .= PARENT_ORDER_NUMBER_MSG . ": " . $parent_order_id . " " . $error_body . "<br>";

		// check for errors
		$error_body .= PARENT_ORDER_NUMBER_MSG . ": " . $parent_order_id . $eol;
		mail($recipients, $error_subject, $error_body, $email_headers);

		$credit_note_added = false;
	} else {
		$credit_note_added = true;
	}
	// clear error flag and order_items array
	$order_items = array();

	return $credit_note_added;

}

?>