<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  order_functions.php                                      ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	function create_order($order_data)
	{
		global $db, $table_prefix, $site_id;

		$order_items = isset($order_data["items"]) ? $order_data["items"] : array();
		if (!isset($order_data["site_id"])) { $order_data["site_id"] = $site_id; }
		if (!isset($order_data["order_status"])) { $order_data["order_status"] = 0; }
		if (!isset($order_data["total_buying"])) { $order_data["total_buying"] = 0; }
		if (!isset($order_data["goods_total"])) { $order_data["goods_total"] = 0; }
		if (!isset($order_data["order_total"])) { $order_data["order_total"] = 0; }
		if (!isset($order_data["total_quantity"])) { $order_data["total_quantity"] = count($order_items); }
		if (!isset($order_data["user_id"])) { $order_data["user_id"] = get_session("session_user_id"); }
		if (!isset($order_data["user_type_id"])) { $order_data["user_type_id"] = get_session("session_user_type_id"); }
		$order_site_id = $order_data["site_id"];
		$order_status = $order_data["order_status"];
		$user_id = $order_data["user_id"];
		$user_type_id = $order_data["user_type_id"];

		// init record for order
		$r = new VA_Record($table_prefix . "orders");
		$r->add_where("order_id", INTEGER);
		$r->add_textbox("invoice_number", TEXT);
		$r->change_property("invoice_number", USE_SQL_NULL, false);
		$r->change_property("invoice_number", USE_IN_UPDATE, false);
		$r->add_textbox("session_id", TEXT);
		$r->add_textbox("site_id", INTEGER);
		$r->add_textbox("user_id", INTEGER);
		$r->change_property("user_id", USE_SQL_NULL, false);
		$r->add_textbox("user_type_id", INTEGER);
		$r->add_textbox("newsletter_id", INTEGER);
		$r->add_textbox("newsletter_email_id", INTEGER);
		$r->add_textbox("admin_id_added_by", INTEGER);
		$r->add_textbox("payment_id", INTEGER, PAYMENT_GATEWAY_MSG);
		$r->change_property("payment_id", USE_SQL_NULL, false);
		$r->add_textbox("order_payment_id", INTEGER);
		$r->add_textbox("payment_amount", FLOAT);
		$r->add_textbox("success_message", TEXT);
		$r->add_textbox("error_message", TEXT);
		$r->add_textbox("pending_message", TEXT);
		$r->add_textbox("remote_address", TEXT);
		$r->add_textbox("initial_ip", TEXT);
		$r->add_textbox("cookie_ip", TEXT);
		$r->add_textbox("visit_id", INTEGER);
		$r->change_property("visit_id", USE_SQL_NULL, false);
		$r->add_textbox("affiliate_code", TEXT);
		$r->change_property("affiliate_code", USE_SQL_NULL, false);
		$r->add_textbox("affiliate_user_id", INTEGER);
		$r->change_property("affiliate_user_id", USE_SQL_NULL, false);
		$r->add_textbox("friend_code", TEXT);
		$r->change_property("friend_code", USE_SQL_NULL, false);
		$r->add_textbox("friend_user_id", INTEGER);
		$r->change_property("friend_user_id", USE_SQL_NULL, false);
		$r->add_textbox("keywords", TEXT);
		$r->change_property("keywords", USE_SQL_NULL, false);
		$r->add_textbox("coupons_ids", TEXT);
		$r->add_textbox("vouchers_ids", TEXT);
		$r->add_textbox("default_currency_code", TEXT);
		$r->add_textbox("currency_code", TEXT);
		$r->add_textbox("currency_rate", FLOAT);
		$r->add_textbox("payment_currency_code", TEXT);
		$r->add_textbox("payment_currency_rate", FLOAT);
		$r->add_textbox("order_status", INTEGER);
		$r->add_textbox("total_buying", NUMBER);
		$r->add_textbox("total_buying_tax", NUMBER);
		$r->add_textbox("total_merchants_commission", NUMBER);
		$r->add_textbox("total_affiliate_commission", NUMBER);
		$r->add_textbox("goods_total", NUMBER);
		$r->add_textbox("goods_tax", NUMBER);
		$r->add_textbox("goods_incl_tax", NUMBER);
		$r->add_textbox("goods_points_amount", NUMBER);
		$r->add_textbox("total_quantity", NUMBER);
		$r->add_textbox("weight_total", NUMBER);
		$r->add_textbox("actual_weight_total", NUMBER);
		$r->add_textbox("total_discount", NUMBER);
		$r->add_textbox("total_discount_tax", NUMBER);
		$r->add_textbox("properties_total", NUMBER);
		$r->add_textbox("properties_taxable", NUMBER);
		$r->add_textbox("properties_points_amount", NUMBER);
  
		$r->add_textbox("shipping_excl_tax", NUMBER);
		$r->add_textbox("shipping_tax", NUMBER);
		$r->add_textbox("shipping_incl_tax", NUMBER);
		$r->add_textbox("shipping_points_cost", NUMBER);
  
		$r->add_textbox("tax_name", TEXT);
		$r->add_textbox("tax_percent", NUMBER);
		$r->add_textbox("tax_total", NUMBER);
		$r->add_textbox("tax_prices_type", NUMBER);
		$r->add_textbox("vouchers_amount", NUMBER);
		$r->add_textbox("credit_amount", NUMBER);
  
		$r->add_textbox("processing_fee", NUMBER);
		$r->add_textbox("processing_tax_free", NUMBER);
		$r->add_textbox("processing_excl_tax", NUMBER);
		$r->add_textbox("processing_tax", NUMBER);
		$r->add_textbox("processing_incl_tax", NUMBER);
  
		$r->add_textbox("order_total", NUMBER);
		$r->add_textbox("total_points_amount", NUMBER);
		$r->add_textbox("total_reward_points", NUMBER);
		$r->add_textbox("total_reward_credits", NUMBER);
		$r->add_textbox("order_placed_date", DATETIME);
		$r->add_textbox("is_fast_checkout", INTEGER);
		$r->add_textbox("is_paid", INTEGER);

		$r->add_textbox("name", TEXT);
		$r->change_property("name", USE_SQL_NULL, false);
		$r->add_textbox("first_name", TEXT);
		$r->change_property("first_name", USE_SQL_NULL, false);
		$r->add_textbox("middle_name", TEXT);
		$r->change_property("middle_name", USE_SQL_NULL, false);
		$r->add_textbox("last_name", TEXT);
		$r->change_property("last_name", USE_SQL_NULL, false);
		$r->add_textbox("company_id", INTEGER);
		$r->add_textbox("company_name", TEXT);
		$r->add_textbox("email", TEXT);
		$r->add_textbox("address1", TEXT);
		$r->add_textbox("address2", TEXT);
		$r->add_textbox("address3", TEXT);
		$r->add_textbox("city", TEXT);
		$r->add_textbox("province", TEXT);
		$r->add_textbox("state_id", INTEGER);
		$r->add_textbox("state_code", TEXT);
		$r->add_textbox("zip", TEXT);
		$r->add_textbox("country_id", INTEGER);
		$r->add_textbox("country_code", TEXT);
		$r->add_textbox("phone", TEXT);
		$r->add_textbox("daytime_phone", TEXT);
		$r->add_textbox("evening_phone", TEXT);
		$r->add_textbox("cell_phone", TEXT);
		$r->add_textbox("fax", TEXT);

		$r->add_textbox("delivery_name", TEXT);
		$r->add_textbox("delivery_first_name", TEXT);
		$r->add_textbox("delivery_middle_name", TEXT);
		$r->add_textbox("delivery_last_name", TEXT);
		$r->add_textbox("delivery_company_id", INTEGER);
		$r->add_textbox("delivery_company_name", TEXT);
		$r->add_textbox("delivery_email", TEXT);
		$r->add_textbox("delivery_address1", TEXT);
		$r->add_textbox("delivery_address2", TEXT);
		$r->add_textbox("delivery_address3", TEXT);
		$r->add_textbox("delivery_city", TEXT);
		$r->add_textbox("delivery_province", TEXT);
		$r->add_textbox("delivery_state_id", INTEGER);
		$r->add_textbox("delivery_state_code", TEXT);
		$r->add_textbox("delivery_zip", TEXT);
		$r->add_textbox("delivery_country_id", INTEGER);
		$r->add_textbox("delivery_country_code", TEXT);
		$r->add_textbox("delivery_phone", TEXT);
		$r->add_textbox("delivery_daytime_phone", TEXT);
		$r->add_textbox("delivery_evening_phone", TEXT);
		$r->add_textbox("delivery_cell_phone", TEXT);
		$r->add_textbox("delivery_fax", TEXT);

		foreach ($order_data as $param_name => $param_value) {
			if($r->parameter_exists($param_name)) {
				$r->set_value($param_name, $param_value);
			}
		}
		$r->set_value("order_placed_date", va_time());
		$r->set_value("order_status", 0);
		$r->insert_record();
		$order_id = $db->last_insert_id();

		$oi = new VA_Record($table_prefix . "orders_items");
		$oi->add_where("order_item_id", INTEGER);
		$oi->add_textbox("order_id", INTEGER);
		$oi->add_textbox("site_id", INTEGER);
		$oi->add_textbox("top_order_item_id", INTEGER);
		$oi->add_textbox("user_id", INTEGER);
		$oi->add_textbox("user_type_id", INTEGER);

		$oi->add_textbox("item_id", INTEGER);
		$oi->change_property("item_id", USE_SQL_NULL, false);
		$oi->add_textbox("parent_item_id", INTEGER);
		$oi->add_textbox("cart_item_id", INTEGER);
		$oi->change_property("cart_item_id", USE_SQL_NULL, false);
		$oi->add_textbox("item_user_id", INTEGER);
		$oi->change_property("item_user_id", USE_SQL_NULL, false);
		$oi->add_textbox("affiliate_user_id", INTEGER);
		$oi->change_property("affiliate_user_id", USE_SQL_NULL, false);
		$oi->add_textbox("friend_user_id", INTEGER);
		$oi->change_property("friend_user_id", USE_SQL_NULL, false);
		$oi->add_textbox("item_type_id", INTEGER);
		$oi->change_property("item_type_id", USE_SQL_NULL, false);
		$oi->add_textbox("supplier_id", INTEGER);
		$oi->change_property("supplier_id", USE_SQL_NULL, false);
		$oi->add_textbox("item_code", TEXT);
		$oi->add_textbox("manufacturer_code", TEXT);
		$oi->add_textbox("coupons_ids", TEXT);
		$oi->add_textbox("item_status", INTEGER);
		$oi->add_textbox("component_order", INTEGER);
		$oi->add_textbox("component_name", TEXT);
		$oi->add_textbox("item_name", TEXT);
		$oi->add_textbox("item_properties", TEXT);
		$oi->add_textbox("buying_price", NUMBER);
		$oi->add_textbox("real_price", NUMBER);
		$oi->add_textbox("discount_amount", NUMBER);
		$oi->add_textbox("price", NUMBER);
		$oi->add_textbox("tax_id", INTEGER);
		$oi->add_textbox("tax_free", INTEGER);
		$oi->add_textbox("tax_percent", NUMBER);
		$oi->add_textbox("points_price", NUMBER);
		$oi->add_textbox("reward_points", NUMBER);
		$oi->add_textbox("reward_credits", NUMBER);
		$oi->add_textbox("merchant_commission", NUMBER);
		$oi->add_textbox("affiliate_commission", NUMBER);
		$oi->add_textbox("packages_number", NUMBER);
		$oi->add_textbox("weight", NUMBER);
		$oi->add_textbox("actual_weight", NUMBER);
		$oi->add_textbox("width", NUMBER);
		$oi->add_textbox("height", NUMBER);
		$oi->add_textbox("length", NUMBER);
		$oi->add_textbox("quantity", NUMBER);
		$oi->add_textbox("downloadable", NUMBER);
		$oi->add_textbox("is_shipping_free", INTEGER);
		$oi->add_textbox("shipping_cost", NUMBER);
		$oi->add_textbox("order_shipping_id", NUMBER);
		// recurring fields
		$oi->add_textbox("is_recurring", INTEGER);
		$oi->add_textbox("recurring_price", NUMBER);
		$oi->add_textbox("recurring_period", INTEGER);
		$oi->add_textbox("recurring_interval", INTEGER);
		$oi->add_textbox("recurring_payments_total", INTEGER);
		$oi->add_textbox("recurring_payments_made", INTEGER);
		$oi->add_textbox("recurring_payments_failed", INTEGER);
		$oi->add_textbox("recurring_end_date", DATETIME);
		$oi->add_textbox("recurring_last_payment", DATETIME);
		$oi->add_textbox("recurring_next_payment", DATETIME);
		$oi->add_textbox("recurring_plan_payment", DATETIME);
		// recurring fields
		$oi->add_textbox("is_subscription", INTEGER);
		$oi->add_textbox("is_account_subscription", INTEGER);
		$oi->add_textbox("subscription_id", INTEGER);
		$oi->change_property("subscription_id", USE_SQL_NULL, false);
		$oi->add_textbox("subscription_period",   INTEGER);
		$oi->add_textbox("subscription_interval", INTEGER);
		$oi->add_textbox("subscription_suspend",  INTEGER);

		$oc = new VA_Record($table_prefix . "orders_coupons");
		$oc->add_textbox("order_id", INTEGER);
		$oc->add_textbox("order_item_id", INTEGER);
		$oc->add_textbox("coupon_id", INTEGER);
		$oc->add_textbox("coupon_code", TEXT);
		$oc->add_textbox("coupon_title", TEXT);
		$oc->add_textbox("discount_type", INTEGER);
		$oc->add_textbox("discount_amount", NUMBER);
		$oc->add_textbox("discount_tax_amount", NUMBER);

		foreach ($order_items as $key_id => $order_item) {

			$item_id = get_setting_value($order_item, "id", 0);
			$item_id = get_setting_value($order_item, "item_id", $item_id);

			$item_name = get_setting_value($order_item, "name");
			$item_name = get_setting_value($order_item, "item_name", $item_name);

			$item_code = get_setting_value($order_item, "item_code");
			$manufacturer_code = get_setting_value($order_item, "manufacturer_code");

			$buying_price = get_setting_value($order_item, "buying_price", 0);
			$buying_price = get_setting_value($order_item, "full_buying_price", $buying_price);
			$price = get_setting_value($order_item, "price", 0);
			$price = get_setting_value($order_item, "full_price", $price);
			$price_incl_tax = get_setting_value($order_item, "price_incl_tax", 0);
			$real_price = get_setting_value($order_item, "real_price", 0);
			$real_price = get_setting_value($order_item, "full_real_price", $buying_price);
			$item_discount = $real_price - $price;

			$quantity = get_setting_value($order_item, "quantity", 1);
			$is_shipping_free = get_setting_value($order_item, "is_shipping_free");

			$coupons_ids = get_setting_value($order_item, "coupons_ids");

			$oi->set_value("order_id", $order_id);
			$oi->set_value("site_id", $order_site_id);
			$oi->set_value("user_id", $user_id);
			$oi->set_value("user_type_id", $user_type_id);

			$oi->set_value("item_id", $item_id);
			//$oi->set_value("parent_item_id", "");
			//$oi->set_value("cart_item_id", "");
			//$oi->set_value("item_user_id", "");
			//$oi->set_value("affiliate_user_id", "");
			//$oi->set_value("friend_user_id", "");
			//$oi->set_value("item_type_id", "");
			//$oi->set_value("supplier_id", "");
			$oi->set_value("item_code", $item_code);
			$oi->set_value("manufacturer_code", $manufacturer_code);
			$oi->set_value("coupons_ids", $coupons_ids);
			$oi->set_value("item_status", 0);
			//$oi->set_value("component_order", "");
			//$oi->set_value("component_name", "");
			$oi->set_value("item_name", $item_name);
			//$oi->set_value("item_properties", "");
			$oi->set_value("buying_price", $buying_price);
			$oi->set_value("real_price", $real_price);
			$oi->set_value("discount_amount", $item_discount);
			$oi->set_value("price", $price);
			$oi->set_value("quantity", $quantity);
			$oi->set_value("is_shipping_free", $is_shipping_free);
			//$oi->set_value("tax_id", "");
			//$oi->set_value("tax_free", "");
			//$oi->set_value("tax_percent", "");

			$oi->insert_record();
			$order_item_id = $db->last_insert_id();

			$item_coupons = get_setting_value($order_item, "coupons");
			if (is_array($item_coupons)) {
				foreach ($item_coupons as $coupon_key => $coupon_data) {
					$oc->set_value("order_id", $order_id);
					$oc->set_value("order_item_id", $order_item_id);
					$oc->set_value("coupon_id", $coupon_data["id"]);
					$oc->set_value("coupon_code", $coupon_data["code"]);
					$oc->set_value("coupon_title", $coupon_data["title"]);
					$oc->set_value("discount_type", $coupon_data["type"]);
					$oc->set_value("discount_amount", $coupon_data["discount"]);
					$oc->set_value("discount_tax_amount", $coupon_data["discount"]);
					$oc->insert_record();
				}
			}
		}

		if ($order_status > 0) {
			update_order_status($order_id, $order_status, true, "", $status_error);
		}

		return $order_id;
	}