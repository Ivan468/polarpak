<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  order_recalculate.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	// function to recalculate order total value after shipping change
	function recalculate_order($order_id)
	{
		global $db, $table_prefix, $settings;

		// get global settings
		$global_tax_prices_type = get_setting_value($settings, "tax_prices_type", 0);
		$global_tax_round = get_setting_value($settings, "tax_round", 1);
		$tax_prices = get_setting_value($settings, "tax_prices", 0);
		$points_decimals = get_setting_value($settings, "points_decimals", 0);

		// get order tax rates
		$tax_available = false; $tax_percent_sum = 0; $taxes_total = 0; 
		$order_tax_rates = order_tax_rates($order_id);
		if (sizeof($order_tax_rates) > 0) {
			$tax_available = true;
		}

		// get information about order
		$sql  = " SELECT o.user_type_id, o.site_id, o.coupons_ids, o.vouchers_ids, o.total_discount, o.total_discount_tax, o.shipping_type_desc, ";
		$sql .= " o.shipping_cost, o.shipping_taxable, o.tax_name, o.tax_percent, o.vouchers_amount, ";
		$sql .= " o.processing_fee, o.shipping_type_id, o.country_id, o.state_id, o.delivery_state_id, ";
		$sql .= " o.tax_prices_type, o.weight_total, o.actual_weight_total, o.goods_total, o.goods_tax, o.goods_incl_tax, ";
		$sql .= " o.currency_code, o.currency_rate, o.order_payment_id, o.paid_total, ";
		$sql .= " o.shipping_points_amount, o.total_points_amount, o.credit_amount, o.total_reward_credits, o.total_reward_points ";
		$sql .= " FROM " . $table_prefix . "orders o ";
		$sql .= " WHERE o.order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		$db->next_record();

		$order_user_type_id = $db->f("user_type_id");
		$order_site_id = $db->f("site_id");
		$order_status_type = $db->f("status_type");
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

		//$goods_total = $db->f("goods_total");
		//$goods_tax = $db->f("goods_tax");
		//$goods_incl_tax = $db->f("goods_incl_tax");
		//if ($tax_prices_type == 1) {
		//	$goods_excl_tax = $goods_total - $goods_tax;
		//	$goods_incl_tax = $goods_total;
		//} else {
		//	$goods_excl_tax = $goods_total;
		//	$goods_incl_tax = $goods_total + $goods_tax;
		//}
		$total_discount = $db->f("total_discount");
		$total_discount_tax = $db->f("total_discount_tax");
		$processing_fee = $db->f("processing_fee");
		// check saved payment id and already paid amount to update this value
		$order_payment_id = $db->f("order_payment_id");
		$paid_total = doubleval($db->f("paid_total"));

		// calculate tax for old shipping
		$old_shipping_cost = doubleval($db->f("shipping_cost"));
		$old_shipping_taxable = intval($db->f("shipping_taxable"));

		// NEW SHIPPING STRUCTURE
		$shipments_cost_excl_tax = 0; $shipments_tax = 0; $shipments_cost_incl_tax = 0;
		$sql  = " SELECT * FROM " . $table_prefix . "orders_shipments ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			do {
				$order_shipping_id = $db->f("order_shipping_id");
				$shipping_cost = $db->f("shipping_cost");
				$points_cost = $db->f("points_cost");
				$shipping_tax_free = $db->f("tax_free");
				$orders_shipments[$order_shipping_id] = $db->Record;
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
				$orders_shipments[$order_shipping_id]["shipping_cost_excl_tax"] = $shipping_cost_excl_tax;
				$orders_shipments[$order_shipping_id]["shipping_tax"] = $shipping_tax_total;
				$orders_shipments[$order_shipping_id]["shipping_cost_incl_tax"] = $shipping_cost_incl_tax;
				// calculate sum of shipments
				$shipments_cost_excl_tax += $shipping_cost_excl_tax; 
				$shipments_tax += $shipping_tax_total; 
				$shipments_cost_incl_tax += $shipping_cost_incl_tax;
			} while ($db->next_record());
		} else {
			// old way shipping
			$shipping_tax_id = 0;
			$shipping_tax_free = ($old_shipping_taxable) ? 0 : 1;
			$shipping_tax_values = get_tax_amount($order_tax_rates, "shipping", $old_shipping_cost, 1, $shipping_tax_id, $shipping_tax_free, $shipping_tax_percent, "", 2, $tax_prices_type, $tax_round);
			$shipping_tax_total = add_tax_values($order_tax_rates, $shipping_tax_values, "shipping", $tax_round);
	  
			if ($tax_prices_type == 1) {
				$shipping_cost_excl_tax = $old_shipping_cost - $shipping_tax_total;
				$shipping_cost_incl_tax = $old_shipping_cost;
			} else {
				$shipping_cost_excl_tax = $old_shipping_cost;
				$shipping_cost_incl_tax = $old_shipping_cost + $shipping_tax_total;
			}

			// calculate sum of shipments
			$shipments_cost_excl_tax += $shipping_cost_excl_tax; 
			$shipments_tax += $shipping_tax_total; 
			$shipments_cost_incl_tax += $shipping_cost_incl_tax;
		} 


		// check order properties
		$properties_total = 0; $properties_tax = 0; $properties_taxable = 0; $properties_incl_tax = 0; $properties_excl_tax = 0;
		$sql  = " SELECT op.property_id, op.property_type, op.property_name, op.property_value, ";
		$sql .= "  op.property_price, op.property_points_amount, op.tax_free ";
		$sql .= " FROM " . $table_prefix . "orders_properties op ";
		$sql .= " WHERE op.order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$property_id   = $db->f("property_id");
			$property_type = $db->f("property_type");
			$property_price = $db->f("property_price");
			$property_points_amount = $db->f("property_points_amount");
			$property_tax_free = $db->f("tax_free");
			$control_type = $db->f("control_type");
	  
			$properties_total += $property_price;
			if ($property_tax_free != 1) {
				$properties_taxable += $property_price;
			}
			$property_tax_id = 0;
			$property_tax_values = get_tax_amount($order_tax_rates, "properties", $property_price, 1, $property_tax_id, $property_tax_free, $property_tax_percent, "", 2, $tax_prices_type, $tax_round);
			$property_tax = add_tax_values($order_tax_rates, $property_tax_values, "properties", $tax_round);

			if ($tax_prices_type == 1) {
				$property_price_excl_tax = $property_price - $property_tax;
				$property_price_incl_tax = $property_price;
			} else {
				$property_price_excl_tax = $property_price;
				$property_price_incl_tax = $property_price + $property_tax;
			}
			// calculate sum of properties
			$properties_tax += $property_tax;
			$properties_incl_tax += $property_price_incl_tax;
			$properties_excl_tax += $property_price_excl_tax;
		}


		// get info about order items
		$goods_total = 0; $goods_tax = 0; $goods_excl_tax = 0; $goods_incl_tax = 0;
		$total_quantity = 0; $total_items = 0;
		$sql  = " SELECT oi.order_item_id,oi.top_order_item_id,oi.item_id,oi.item_user_id,oi.item_type_id,";
		$sql .= " oi.item_status,oi.item_code,oi.manufacturer_code, oi.component_name, oi.item_name, ";
		$sql .= " oi.is_recurring, oi.recurring_last_payment, oi.recurring_next_payment, oi.downloadable, ";
		$sql .= " oi.price,oi.tax_id,oi.tax_free,oi.tax_percent,oi.discount_amount,oi.real_price, oi.weight, oi.actual_weight, ";
		$sql .= " oi.buying_price,oi.points_price,oi.reward_points,oi.reward_credits,oi.quantity,oi.coupons_ids ";
		$sql .= " FROM " . $table_prefix . "orders_items oi ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$item_type_id = $db->f("item_type_id");

			$price = $db->f("price");
			$quantity = $db->f("quantity");
			$item_tax_id = $db->f("tax_id");
			$item_tax_free = $db->f("tax_free");
			$item_total = $price * $quantity;

			$buying_price = $db->f("buying_price");
			$points_price = $db->f("points_price");
			$reward_points = $db->f("reward_points");
			$reward_credits = $db->f("reward_credits");

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

			$goods_total += $item_total;
			$goods_tax += $item_tax_total;
			$goods_excl_tax += $price_excl_tax_total;
			$goods_incl_tax += $price_incl_tax_total;
			$total_quantity += $quantity;

			// calculate points and credits
			//$total_points_price += ($points_price  * $quantity);
			//$total_reward_points += ($reward_points * $quantity);
			//$total_reward_credits += ($reward_credits * $quantity);
		}//*/

		$order_fixed_amount = 0; 
		foreach($order_tax_rates as $tax_id => $tax_info) {
			$external_tax_amount = isset($tax_info["order_fixed_amount"]) ? $tax_info["order_fixed_amount"] : 0;
			$order_fixed_amount += $external_tax_amount;
		}
		$tax_total = $goods_tax + $properties_tax + $shipments_tax - $total_discount_tax + $order_fixed_amount;
		$order_total = $goods_incl_tax + $properties_incl_tax + $shipments_cost_incl_tax + $processing_fee - $total_discount;
		if ($tax_prices_type == 0) {
			// when prices use exclude tax option then we need to deduct total discount tax value from order total 
			$order_total -= $total_discount_tax;
		}
		// always add external tax
		$order_total += round($order_fixed_amount, 2);

		// update goods and order total data
		$sql  = " UPDATE " . $table_prefix . "orders SET ";
		$sql .= " goods_total=" . $db->tosql($goods_total, NUMBER) . ", ";
		$sql .= " goods_tax=" . $db->tosql($goods_tax, NUMBER) . ", ";
		$sql .= " goods_incl_tax=" . $db->tosql($goods_incl_tax, NUMBER) . ", ";
		$sql .= " shipping_excl_tax=" . $db->tosql($shipments_cost_excl_tax, NUMBER) . ", ";
		$sql .= " shipping_tax=" . $db->tosql($shipments_tax, NUMBER) . ", ";
		$sql .= " shipping_incl_tax=" . $db->tosql($shipments_cost_incl_tax, NUMBER) . ", ";
		$sql .= " tax_total=" . $db->tosql($tax_total, NUMBER) . ", ";
		$sql .= " order_total=" . $db->tosql($order_total, NUMBER) . " ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);

		// update payment amount for selected payment method
		if ($order_payment_id) {
			$payment_amount = $order_total - $paid_total;
			$sql  = " UPDATE " . $table_prefix . "orders_payments SET ";
			$sql .= " payment_amount=" . $db->tosql($payment_amount, NUMBER);
			$sql .= " WHERE order_payment_id=" . $db->tosql($order_payment_id, INTEGER);
			$db->query($sql);
		}

	}


	function update_order_shipping($order_id, $order_items_ids, $order_shipping_id, $shipping_type_id, $shipping_custom_desc, $shipping_cost, $shipping_tracking_id, $shipping_company_id = "")
	{
		global $db, $table_prefix;

		// check current shipping
		$current_shipping = ""; $current_company_id = "";
		if ($order_shipping_id) {
			$sql  = " SELECT shipping_desc,shipping_company_id FROM " . $table_prefix . "orders_shipments ";
			$sql .= " WHERE order_shipping_id=" . $db->tosql($order_shipping_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$current_shipping = $db->f("shipping_desc");
				$current_company_id = $db->f("shipping_company_id");
			}
		}
		if ($shipping_company_id == $current_company_id) { $shipping_company_id = ""; }

		// get additional information about shipping
		$shipping_desc = $shipping_custom_desc; $tare_weight = 0; $shipping_code = ""; $shipping_taxable = 1; $shipping_tax_free = 0; $shipping_points_amount = 0; 
		if (strlen($shipping_type_id)) {
			$sql  = " SELECT shipping_type_code, shipping_type_desc, is_taxable, tare_weight ";
			$sql .= " FROM ". $table_prefix . "shipping_types ";
			$sql .= " WHERE shipping_type_id=" . $db->tosql($shipping_type_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$shipping_code = $db->f("shipping_type_code");
				$shipping_desc = $db->f("shipping_type_desc");
				$shipping_taxable = $db->f("is_taxable");
				$tare_weight = $db->f("tare_weight");
				$shipping_tax_free = ($shipping_taxable) ? 0 : 1;
			} else {
				$shipping_type_id = "";
			}
		} else if (!$order_shipping_id) {
			if (!$shipping_desc) { $shipping_desc = PROD_SHIPPING_MSG; }
			if (!$shipping_cost) { $shipping_cost = 0; }
		}

		// add shipping tracking event
		$os = new VA_Record($table_prefix . "orders_shipments");
		$os->add_where("order_shipping_id", INTEGER);
		$os->add_textbox("order_id", INTEGER);
		$os->change_property("order_id", USE_IN_UPDATE, false);
		$os->add_textbox("shipping_id", INTEGER);
		$os->change_property("shipping_id", USE_SQL_NULL, false);
		$os->add_textbox("shipping_code", TEXT);
		$os->add_textbox("shipping_desc", TEXT);
		$os->add_textbox("shipping_cost", NUMBER);
		$os->add_hidden("points_cost", NUMBER);
		$os->add_textbox("tax_free", INTEGER);
		$os->add_textbox("tracking_id", TEXT);
		$os->add_textbox("expecting_date", DATETIME);
		$os->add_textbox("goods_weight", NUMBER);
		$os->add_textbox("actual_goods_weight", NUMBER);
		$os->add_textbox("tare_weight", NUMBER);
		$os->add_textbox("order_items_ids", TEXT);
		$os->add_textbox("shipping_company_id", INTEGER);
		$os->change_property("order_items_ids", USE_IN_UPDATE, false);
		// set values
		$os->set_value("order_shipping_id", $order_shipping_id);
		$os->set_value("order_id", $order_id);
		$os->set_value("shipping_id", $shipping_type_id);
		$os->set_value("shipping_code", $shipping_code);
		$os->set_value("shipping_desc", $shipping_desc);
		$os->set_value("shipping_cost", $shipping_cost);
		$os->set_value("tax_free", $shipping_tax_free);
		$os->set_value("tracking_id", $shipping_tracking_id);
		$os->set_value("shipping_company_id", $shipping_company_id);
		//TODO: apply new fields
		//$os->set_value("expecting_date", "");
		//$os->set_value("goods_weight", "");
		//$os->set_value("actual_goods_weight", "");
		$os->set_value("tare_weight", $tare_weight);
		$os->set_value("order_items_ids", $order_items_ids);

		if ($order_shipping_id) {
			// update order shipment
			if (!$shipping_type_id && !$shipping_custom_desc) {
				$os->change_property("shipping_id", USE_IN_UPDATE, false);
				$os->change_property("shipping_code", USE_IN_UPDATE, false);
				$os->change_property("tax_free", USE_IN_UPDATE, false);
				$os->change_property("tare_weight", USE_IN_UPDATE, false);
			}
			if (!strlen($shipping_desc)) {
				$os->change_property("shipping_desc", USE_IN_UPDATE, false);
			}
			if (!strlen($shipping_cost)) {
				$os->change_property("shipping_cost", USE_IN_UPDATE, false);
			}
			if (!strlen($shipping_tracking_id)) {
				$os->change_property("shipping_tracking_id", USE_IN_UPDATE, false);
			}
			if (!strlen($shipping_company_id)) {
				$os->change_property("shipping_company_id", USE_IN_UPDATE, false);
			}
			$os->update_record();

		} else {
			// add new shipment
			if ($db->DBType == "postgre") {
				$order_shipping_id = get_db_value(" SELECT NEXTVAL('seq_" . $table_prefix . "orders_shipments ') ");
				$os->change_property("order_shipping_id", USE_IN_INSERT, true);
				$os->set_value("order_shipping_id", $order_shipping_id);
			}
			$os->insert_record();
			if ($db->DBType == "mysql") {
				$order_shipping_id = get_db_value(" SELECT LAST_INSERT_ID() ");
				$os->set_value("order_shipping_id", $order_shipping_id);
			} elseif ($db->DBType == "access") {
				$order_shipping_id = get_db_value(" SELECT @@IDENTITY ");
				$os->set_value("order_shipping_id", $order_shipping_id);
			} elseif ($db->DBType == "db2") {
				$order_shipping_id = get_db_value(" SELECT PREVVAL FOR seq_" . $table_prefix . "orders_shipments FROM " . $table_prefix . "orders_shipments ");
				$os->set_value("order_shipping_id", $order_shipping_id);
			}
		}
		recalculate_order($order_id);

		// update shipping information
		if (strlen($shipping_desc)) {
			// save event with updated shipping
			$r = new VA_Record($table_prefix . "orders_events");
			$r->add_textbox("order_id", INTEGER);
			$r->add_textbox("status_id", INTEGER);
			$r->add_textbox("admin_id", INTEGER);
			$r->add_textbox("event_date", DATETIME);
			$r->add_textbox("event_type", TEXT);
			$r->add_textbox("event_name", TEXT);
			$r->add_textbox("event_description", TEXT);
			$r->set_value("order_id", $order_id);
			$r->set_value("status_id", 0);
			$r->set_value("admin_id", get_session("session_admin_id"));
			$r->set_value("event_date", va_time());
			$r->set_value("event_type", "update_order_shipping");
			if ($current_shipping) {
				$r->set_value("event_name", $current_shipping . " &ndash;&gt; " . $shipping_desc);
			} else {
				$r->set_value("event_name", $shipping_desc);
			}
			$r->insert_record();
		}

		// save shipping tracking event 
		if (strlen($shipping_tracking_id)) {
			// add shipping tracking event
			$oe = new VA_Record($table_prefix . "orders_events");
			$oe->add_textbox("order_id", INTEGER);
			$oe->add_textbox("status_id", INTEGER);
			$oe->add_textbox("admin_id", INTEGER);
			$oe->add_textbox("order_items", TEXT);
			$oe->add_textbox("event_date", DATETIME);
			$oe->add_textbox("event_type", TEXT);
			$oe->add_textbox("event_name", TEXT);
			$oe->add_textbox("event_description", TEXT);
			$oe->set_value("order_id", $order_id);
			$oe->set_value("admin_id", get_session("session_admin_id"));
			$oe->set_value("event_date", va_time());
			$oe->set_value("event_type", "update_shipping_tracking");
			$oe->set_value("event_name", $shipping_tracking_id);
			$oe->insert_record();
		}

		// save shipping company event 
		if (strlen($shipping_company_id)) {
		  $sql  = " SELECT company_name FROM ".$table_prefix."shipping_companies ";
		  $sql .= " WHERE shipping_company_id=".$db->tosql($shipping_company_id, INTEGER);
			$shipping_company_name = get_db_value($sql);
			
			$oe = new VA_Record($table_prefix . "orders_events");
			$oe->add_textbox("order_id", INTEGER);
			$oe->add_textbox("status_id", INTEGER);
			$oe->add_textbox("admin_id", INTEGER);
			$oe->add_textbox("order_items", TEXT);
			$oe->add_textbox("event_date", DATETIME);
			$oe->add_textbox("event_type", TEXT);
			$oe->add_textbox("event_name", TEXT);
			$oe->add_textbox("event_description", TEXT);
			$oe->set_value("order_id", $order_id);
			$oe->set_value("admin_id", get_session("session_admin_id"));
			$oe->set_value("event_date", va_time());
			$oe->set_value("event_type", "update_shipping_company");
			$oe->set_value("event_name", $shipping_company_name);
			$oe->insert_record();
		}

		return $order_shipping_id;
	}


	function delete_order_shipping($order_shipping_id)
	{
		global $db, $table_prefix;
		$sql  = " SELECT order_id,shipping_desc FROM " . $table_prefix . "orders_shipments ";
		$sql .= " WHERE order_shipping_id=" . $db->tosql($order_shipping_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$order_id = $db->f("order_id");
			$shipping_desc = $db->f("shipping_desc");

			// delete shipment
			$sql  = " DELETE FROM " . $table_prefix . "orders_shipments ";
			$sql .= " WHERE order_shipping_id=" . $db->tosql($order_shipping_id, INTEGER);
			$db->query($sql);

			// remove shipment from orders_items table
			$sql  = " UPDATE " . $table_prefix . "orders_items ";
			$sql .= " SET order_shipping_id=NULL ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$sql .= " AND order_shipping_id=" . $db->tosql($order_shipping_id, INTEGER);
			$db->query($sql);

			// add shipping tracking event
			$oe = new VA_Record($table_prefix . "orders_events");
			$oe->add_textbox("order_id", INTEGER);
			$oe->add_textbox("status_id", INTEGER);
			$oe->add_textbox("admin_id", INTEGER);
			$oe->add_textbox("order_items", TEXT);
			$oe->add_textbox("event_date", DATETIME);
			$oe->add_textbox("event_type", TEXT);
			$oe->add_textbox("event_name", TEXT);
			$oe->add_textbox("event_description", TEXT);
			$oe->set_value("order_id", $order_id);
			$oe->set_value("admin_id", get_session("session_admin_id"));
			$oe->set_value("event_date", va_time());
			$oe->set_value("event_type", "remove_shipping");
			$oe->set_value("event_name", $shipping_desc);
			$oe->insert_record();

			recalculate_order($order_id);
		}
	}


?>