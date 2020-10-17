<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  items_properties.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	function show_items_properties($form_name, $form_id, $item_id, $item_type_id, $item_price, $tax_id, $tax_free, $type, &$product_params, $parse_template = true, $show_price_matrix = false, $discount_applicable = true, $properties_percent = 0, $selected_properties = "")
	{
	 	global $t, $db, $dbp, $site_id, $table_prefix;
		global $settings, $currency, $tax_rates, $root_folder_path;
		$db_rsi = $db->set_rsi("p"); // separate DB key for options queries

		// return some data
		$json_data = array();
		$data = array("random_image_src"=>"", "random_image_alt"=>"");
		$properties = array();
		$features = array();

  
		$eol = get_eol();
		$discount_type = get_session("session_discount_type");
		$discount_amount = get_session("session_discount_amount");
		$display_products = get_setting_value($settings, "display_products", 0);
		$tax_prices_type = get_setting_value($settings, "tax_prices_type", 0);
		$tax_prices = get_setting_value($settings, "tax_prices", 0);
		$points_conversion_rate = get_setting_value($settings, "points_conversion_rate", 1);
		$points_decimals = get_setting_value($settings, "points_decimals", 0);
		$points_prices = get_setting_value($settings, "points_prices", 0);

		$units = array(
			"mm" => "MM",
			"cm" => "CM",
			"in" => "INCHES",
			"m2" => "m2",
		);


		// option delimiter and price options
		$option_name_delimiter = get_setting_value($settings, "option_name_delimiter", ": "); 
		$option_positive_price_right = get_setting_value($settings, "option_positive_price_right", ""); 
		$option_positive_price_left = get_setting_value($settings, "option_positive_price_left", ""); 
		$option_negative_price_right = get_setting_value($settings, "option_negative_price_right", ""); 
		$option_negative_price_left = get_setting_value($settings, "option_negative_price_left", "");
		$option_notes = get_setting_value($settings, "option_notes", 1);

		$json_data["name_delimiter"] = $option_name_delimiter;
		$json_data["positive_price_right"] = $option_positive_price_right;
		$json_data["positive_price_left"] = $option_positive_price_left;
		$json_data["negative_price_right"] = $option_negative_price_right;
		$json_data["negative_price_left"] = $option_negative_price_left;

		$user_id = get_session("session_user_id");		
		$user_type_id = get_session("session_user_type_id");
		$price_type = get_session("session_price_type");
		if ($price_type == 1) {
			$price_field = "trade_price";
			$sales_field = "trade_sales";
			$additional_price_field = "trade_additional_price";
		} else {
			$price_field = "price";
			$sales_field = "sales_price";
			$additional_price_field = "additional_price";
		}

		// check random image option
		$random_image = get_setting_value($product_params, "random_image");
		$image_type = get_setting_value($product_params, "image_type", "small");
		if ($random_image) {
			if ($db->DBType == "access") {
				$sql  = " SELECT TOP 1 * ";
			} else {
				$sql  = " SELECT * ";
			}
			$sql .= " FROM ".$table_prefix."items_images ii ";
			$sql .= " WHERE ii.item_id=".$db->tosql($item_id, INTEGER);
			$sql .= " AND ii.image_position=2 AND ii.image_small<>'' AND ii.image_small IS NOT NULL ";
			if ($db->DBType == "mysql") {
				$sql .= " ORDER BY RAND() LIMIT 1 ";
			} else if ($db->DBType == "postgre") {
				$sql .= " ORDER BY RANDOM() LIMIT 1 ";
			} else if ($db->DBType == "access") {
				$sql .= " ORDER BY Rnd(-(100000*image_id)*Time()) ";
			}
			$db->query($sql);
			if ($db->next_record()) {
				$image_src = $db->f("image_".$image_type);
				$image_alt = $db->f("image_".$image_type."_alt");
				$data["random_image_src"] = $image_src;
				$data["random_image_alt"] = $image_alt;
			}
		}

		// check product properites
		$properties_ids = "";
		$selected_price = 0;
		$is_properties = false;
		$t->set_var("properties", "");
		$t->set_var("properties_block", "");

		$values_prices = array(); // save here ids we need to calculate percentage prices
		$options = array(); $components = array(); $components_price = 0; $components_tax_price = 0;  
		$components_points_price = 0; $components_reward_points = 0; $components_reward_credits = 0;
		$sql  = " SELECT ip.* ";
		$sql .= " FROM (" . $table_prefix . "items_properties ip ";
		$sql .= " LEFT JOIN " . $table_prefix . "items_properties_sites ips ON ip.property_id=ips.property_id) ";
		$sql .= " WHERE (ip.item_id=" . $db->tosql($item_id, INTEGER) . " OR ip.item_type_id=" . $db->tosql($item_type_id, INTEGER) . ")";
		$sql .= " AND ip.show_for_user=1 ";
		if (isset($site_id)) {
			$sql .= " AND (ip.sites_all=1 OR ips.site_id=" . $db->tosql($site_id, INTEGER) . ")";
		} else {
			$sql .= " AND ip.sites_all=1 ";
		}
		if ($type == "list") {
			$sql .= " AND ip.use_on_list=1 ";
		} elseif ($type == "table") {
			$sql .= " AND ip.use_on_table=1 ";
		} elseif ($type == "grid") {
			$sql .= " AND ip.use_on_grid=1 ";
		} elseif ($type == "details") {
			$sql .= " AND ip.use_on_details=1 ";
		}
		$sql .= " ORDER BY ip.property_order, ip.property_id ";
		$db->query($sql);
		while ($db->next_record()) {
			$property_id = $db->f("property_id");
			$property_type_id = $db->f("property_type_id");
			$usage_type = $db->f("usage_type");
			$parent_property_id = $db->f("parent_property_id");
			$parent_value_id = $db->f("parent_value_id");
			$percentage_price_type = $db->f("percentage_price_type");
			$percentage_property_id = $db->f("percentage_property_id");

			if ($property_type_id == 2) {
				$sub_item_id = $db->f("sub_item_id");
				$sub_quantity = $db->f("quantity");
				$sub_price = $db->f($additional_price_field);				
				$components[$property_id] = array("item_id" => $sub_item_id, "quantity" => $sub_quantity, "price" => $sub_price, "usage_type" => $usage_type);
			} else {
				$option = array(
					"property_id" => $property_id,
					"property_type_id" => $property_type_id,
					"usage_type" => $usage_type,
					"property_code" => $db->f("property_code"),
					"property_name" => get_translation($db->f("property_name")),
					"hide_name" => $db->f("hide_name"),
					"property_hint" => get_translation($db->f("property_hint")),
					"parent_property_id" => $db->f("parent_property_id"),
					"parent_value_id" => $db->f("parent_value_id"),
					"parent_value_price" => "", // need this value to calculate percentage price from parent option 
					"property_description" => get_translation($db->f("property_description")),
					"property_class" => get_translation($db->f("property_class")),
					"property_style" => get_translation($db->f("property_style")),
					"property_price_type" => $db->f("property_price_type"),
					"property_price" => $db->f($additional_price_field),
					"percentage_price_type" => $db->f("percentage_price_type"),
					"percentage_property_id" => $db->f("percentage_property_id"),
					"free_price_type" => $db->f("free_price_type"),
					"free_price_amount" => $db->f("free_price_amount"),
					"max_limit_type" => $db->f("max_limit_type"),
					"max_limit_length" => $db->f("max_limit_length"),
					"control_type" => $db->f("control_type"),
					"control_style" => $db->f("control_style"),
					"length_units" => $db->f("length_units"),
					"required" => $db->f("required"),
					"start_html" => get_translation($db->f("start_html")),
					"middle_html" => get_translation($db->f("middle_html")),
					"before_control_html" => get_translation($db->f("before_control_html")),
					"after_control_html" => get_translation($db->f("after_control_html")),
					"end_html" => get_translation($db->f("end_html")),
					"onchange_code" => get_translation($db->f("onchange_code")),
					"onclick_code" => get_translation($db->f("onclick_code")),
					"control_code" => get_translation($db->f("control_code")),
					"values" => array(),
				);
				$options[$property_id] = $option;
				// check if we can get parent option price for percentage calculation
				if (($percentage_price_type == 2 || $percentage_price_type == 3) && $parent_property_id == $percentage_property_id && $parent_value_id) {
					$values_prices[$parent_value_id] = "";
				}
			}
		}

		// check for parent prices 
		if (is_array($values_prices) && count($values_prices)) {
			$sql  = " SELECT ipv.item_property_id, ip.property_id, ip.property_price_type, ip.percentage_price_type, ipv.additional_price, ipv.trade_additional_price, ipv.percentage_price  ";
			$sql .= " FROM (" . $table_prefix . "items_properties ip ";
			$sql .= " INNER JOIN " . $table_prefix . "items_properties_values ipv ON ip.property_id=ipv.property_id) ";
			$sql .= " WHERE ipv.item_property_id IN (" . $db->tosql(array_keys($values_prices), INTEGERS_LIST) . ")";
			$db->query($sql);
			while ($db->next_record()) {
				$property_id = $db->f("property_id");
				$item_property_id = $db->f("item_property_id");
				$property_price_type = $db->f("property_price_type");
				$percentage_price_type = $db->f("percentage_price_type");
				$value_price = $db->f($additional_price_field);
				$percentage_price = $db->f("percentage_price");
				if ($percentage_price_type == 1 && $percentage_price && $item_price) {
					$value_price += round(($item_price * $percentage_price) / 100, 2);
				}
				if (isset($options[$property_id]) && $percentage_price_type != 2 && $percentage_price_type != 3) {
					$values_prices[$item_property_id] = $value_price;
				}
			}
		}

		// check usage option for options and components
		foreach ($components as $property_id => $component_info) {
			if ($component_info["usage_type"] == 2 || $component_info["usage_type"] == 3) {
				$sql  = " SELECT item_id FROM " . $table_prefix . "items_properties_assigned ";
				$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
				$sql .= " AND property_id=" . $db->tosql($property_id, INTEGER);
				$db->query($sql);
				if (!$db->next_record()) {
					// remove component if it wasn't assigned to product
					unset($components[$property_id]);
					continue;
				}
			}
			
			/*$db->query(VA_Products::_sql("i.item_id = ". $db->tosql($component_info["item_id"], INTEGER), VIEW_ITEMS_PERM));
			if (!$db->next_record()) {
				unset($components[$property_id]);
				continue;
			}*/
		}

		foreach ($options as $property_id => $option) {
			if ($option["usage_type"] == 2 || $option["usage_type"] == 3) {
				$sql  = " SELECT item_id, property_description FROM " . $table_prefix . "items_properties_assigned ";
				$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
				$sql .= " AND property_id=" . $db->tosql($property_id, INTEGER);
				$db->query($sql);
				if (!$db->next_record()) {
					// remove option if it wasn't assigned to product
					unset($options[$property_id]);
				} elseif ($option["usage_type"] == 2) {
					$options[$property_id]["property_description"] = get_translation($db->f("property_description"));
				}
			}
		}

		$is_quantity_price = false;
		$max_available_quantity = 1;
		// calculate subcomponents price
		if (sizeof($components) > 0) {
			foreach ($components as $property_id => $component_info) {
				// get subcomponent information
				$sub_item_id = $component_info["item_id"];
				$sub_quantity = $component_info["quantity"];
				$component_price = $component_info["price"];
				// get original information for component product
				$price = 0; $buying_price = 0; $points_price = 0; $reward_points = 0; $reward_credits = 0;
				$sql  = " SELECT i.item_type_id, i.buying_price, i." . $price_field . ", i.is_sales, i." . $sales_field . ", i.tax_id, i.tax_free, ";
				$sql .= " i.is_points_price, i.points_price, i.reward_type, i.reward_amount, i.credit_reward_type, i.credit_reward_amount, ";
				$sql .= " it.reward_type AS type_bonus_reward, it.reward_amount AS type_bonus_amount, ";
				$sql .= " it.credit_reward_type AS type_credit_reward, it.credit_reward_amount AS type_credit_amount ";
				$sql .= " FROM (" . $table_prefix . "items i ";
				$sql .= " LEFT JOIN " . $table_prefix . "item_types it ON i.item_type_id=it.item_type_id) ";
				$sql .= " WHERE i.item_id=" . $db->tosql($sub_item_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$sub_type_id = $db->f("item_type_id");
					$sub_tax_id = $db->f("tax_id");
					$sub_tax_free = $db->f("tax_free");
					$buying_price = $db->f("buying_price");
					$is_points_price = $db->f("is_points_price");
					$points_price = $db->f("points_price");
					$reward_type = $db->f("reward_type");
					$reward_amount = $db->f("reward_amount");
					$credit_reward_type = $db->f("credit_reward_type");
					$credit_reward_amount = $db->f("credit_reward_amount");
					if (!strlen($reward_type)) {
						$reward_type = $db->f("type_bonus_reward");
						$reward_amount = $db->f("type_bonus_amount");
					}
					if (!strlen($credit_reward_type)) {
						$credit_reward_type = $db->f("type_credit_reward");
						$credit_reward_amount = $db->f("type_credit_amount");
					}
					if (!strlen($is_points_price)) {
						$is_points_price = $points_prices;
					}
					if ($sub_quantity < 1) { $sub_quantity = 1; }
					if (strlen($component_price)) {
						$price = $component_price;
					} else {
						$price = $db->f($price_field);
						$is_sales = $db->f("is_sales");
						$sales_price = $db->f($sales_field);
						
						$discount_applicable = 1;
						$q_prices    = get_quantity_price($sub_item_id, $sub_quantity);
						if (sizeof($q_prices)) {
							$price  = $q_prices [0];
							$discount_applicable = $q_prices [2];
						} elseif ($is_sales) {
							$price = $sales_price; 
						}

						if ($discount_applicable) {
							if ($discount_type == 1 || $discount_type == 3) {
								$price -= round(($price * $discount_amount) / 100, 2);
							} elseif ($discount_type == 2) {
								$price -= round($discount_amount, 2);
							} elseif ($discount_type == 4) {
								$price -= round((($price - $buying_price) * $discount_amount) / 100, 2);
							}
						}
					}
					if ($points_price <= 0) {
						$points_price = $price * $points_conversion_rate;
					}
					$reward_points = calculate_reward_points($reward_type, $reward_amount, $price, $buying_price, $points_conversion_rate, $points_decimals);
					$reward_credits = calculate_reward_credits($credit_reward_type, $credit_reward_amount, $price, $buying_price);
					$components[$property_id]["base_price"] = $price;
					$components[$property_id]["buying_price"] = $buying_price;
					$components[$property_id]["item_type_id"] = $item_type_id;
					$components[$property_id]["tax_id"] = $tax_id;
					$components[$property_id]["tax_free"] = $tax_free;

					// add to total values
					$components_price += ($price * $sub_quantity);
					$tax_amount = get_tax_amount($tax_rates, $sub_type_id, $price, 1, $sub_tax_id, $sub_tax_free, $sub_tax_percent);
					$components_tax_price += ($tax_amount * $sub_quantity);
					$components_points_price += ($points_price * $sub_quantity);
					$components_reward_points += ($reward_points * $sub_quantity);
					$components_reward_credits += ($reward_credits * $sub_quantity);

					// check components quantity prices
					$sql  = " SELECT ip.is_active, ip.min_quantity, ip.max_quantity, ";
					$sql .= " ip.price, ip.properties_discount, ip.discount_action ";
					$sql .= " FROM " . $table_prefix . "items_prices ip ";
					$sql .= " WHERE ip.item_id=" . $db->tosql($sub_item_id, INTEGER);
					$sql .= " AND ip.is_active=1 ";
					if (isset($site_id)) {
						$sql .= " AND (ip.site_id=0 OR ip.site_id=" . $db->tosql($site_id, INTEGER, true, false) . ") ";
					} else {
						$sql .= " AND ip.site_id=0 ";
					}
					if (strlen($user_type_id)) {
						$sql .= " AND (ip.user_type_id=0 OR ip.user_type_id=" . $db->tosql($user_type_id, INTEGER, true, false) . ") ";
					} else {
						$sql .= " AND ip.user_type_id=0 ";
					}
					$sql .= " ORDER BY ip.site_id DESC, ip.user_type_id DESC, ip.min_quantity ";		
					$db->query($sql);
					while ($db->next_record()) {
						$min_quantity = $db->f("min_quantity");
						$max_quantity = $db->f("max_quantity");
						$quantity_price = $db->f("price");
						if (strlen($component_price)) {
							// get overrode price for component product
							$quantity_price = $component_price;
						}
						$properties_discount = $db->f("properties_discount");
						$discount_action = $db->f("discount_action");
						if ($discount_type > 0 && $discount_action == 0) {
							// don't use this price as user discount in use
							continue;
						} 
						if ($discount_type > 0 && $discount_action == 2) {
							// apply user discount to quantity price
							if ($discount_type == 1 || $discount_type == 3) {
								$quantity_price -= round(($quantity_price * $discount_amount) / 100, 2);
							} elseif ($discount_type == 2) {
								$quantity_price -= round($discount_amount, 2);
							} elseif ($discount_type == 4) {
								$quantity_price -= round((($quantity_price - $buying_price) * $discount_amount) / 100, 2);
							}
						}
						$is_quantity_price = true;
						$components[$property_id]["quantities"][$min_quantity] = array("min_quantity" => $min_quantity, "max_quantity" => $max_quantity, "quantity_price" => $quantity_price);
						if ($max_quantity > $max_available_quantity) { $max_available_quantity = $max_quantity; }
					}
				}
			}
		}

		// check product features
		if ($type == "table") {
			$sql  = " SELECT f.feature_id, fg.group_id,fg.group_name,f.feature_code,f.feature_name,f.feature_value ";
			$sql .= " FROM " . $table_prefix . "features f, " . $table_prefix . "features_groups fg ";
			$sql .= " WHERE f.group_id=fg.group_id ";
			$sql .= " AND f.item_id=" . intval($item_id);
			if ($type == "table") {
				$sql .= " AND fg.show_on_table=1 ";
				$sql .= " AND (f.show_on_table=1 OR f.show_as_group=1) ";
			} else if ($type == "details") {
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
			while ($db->next_record()) {
				$feature_id = $db->f("feature_id");
				$features[$feature_id] = array(
					"group_id" => $db->f("group_id"),
					"group_name" => get_translation($db->f("group_name")),
					"code" => $db->f("feature_code"),
					"name" => get_translation($db->f("feature_name")),
					"value" => get_translation($db->f("feature_value")),
				);
			}
		}

		// check product prices based on quantity
		$item_min_qty = isset($product_params["min_qty"]) ? $product_params["min_qty"] : 1;
		$item_max_qty = isset($product_params["max_qty"]) ? $product_params["max_qty"] : "";
		if (!$item_min_qty) { $item_min_qty = 1; }
		if (!$item_max_qty) { $item_max_qty = 0; }

		$quantity_prices = "";
		$item_quantities = array();

		$is_price_matrix = false;				
		$t->set_var("price_matrix", "");
		$t->set_var("matrix_prices", "");
		$t->set_var("matrix_quantities", "");

		$order_by = " ORDER BY ";
		$sql  = " SELECT i.buying_price, ip.is_active, ip.min_quantity, ip.max_quantity, ";
		$sql .= " ip.price, ip.properties_discount, ip.discount_action ";
		$sql .= " FROM " . $table_prefix . "items_prices ip, " . $table_prefix . "items i ";
		$sql .= " WHERE ip.item_id=i.item_id ";
		$sql .= " AND ip.item_id=" . $db->tosql($item_id, INTEGER);
		$sql .= " AND ip.is_active=1 ";
		
		if (isset($site_id)) {
			$sql .= " AND (ip.site_id=0 OR ip.site_id=" . $db->tosql($site_id, INTEGER, true, false) . ") ";
			$order_by .= " ip.site_id DESC, ";
		} else {
			$sql .= " AND ip.site_id=0 ";
		}
		
		if (strlen($user_type_id)) {
			$sql .= " AND (ip.user_type_id=0 OR ip.user_type_id=" . $db->tosql($user_type_id, INTEGER, true, false) . ") ";
			$order_by .= " ip.user_type_id DESC, ";
		} else {
			$sql .= " AND ip.user_type_id=0 ";
		}

		$order_by .= " ip.min_quantity ";		
		$db->query($sql . $order_by);
		
		while ($db->next_record()) {
			$is_active = $db->f("is_active");
			$min_quantity = $db->f("min_quantity");
			$max_quantity = $db->f("max_quantity");
			$price = $db->f("price");
			$properties_discount = $db->f("properties_discount");
			$discount_action = $db->f("discount_action");
			$buying_price = $db->f("buying_price");
			if ($discount_type > 0) {	
				if ($discount_action == 0) {
					$is_active = 0;
				} elseif ($discount_action == 2) {
					if ($discount_type == 1 || $discount_type == 3) {
						$price -= round(($price * $discount_amount) / 100, 2);
					} elseif ($discount_type == 2) {
						$price -= round($discount_amount, 2);
					} elseif ($discount_type == 4) {
						$price -= round((($price - $buying_price) * $discount_amount) / 100, 2);
					}
				}
			}

			if ($is_active) {
				$is_quantity_price = true;
				$item_quantities[$min_quantity] = array(
					"max_quantity" => $max_quantity, "quantity_price" => $price, "properties_discount" => $properties_discount);
				if ($max_quantity > $max_available_quantity) { $max_available_quantity = $max_quantity; }
			}
		}


		$quantities = array();
		$min_quantities = array();
		$max_quantities = array();
		if ($is_quantity_price) {
			// check for min and max values
			$components["parent_product"]["base_price"] = $item_price;
			$components["parent_product"]["quantities"] = $item_quantities;
			$components["parent_product"]["item_type_id"] = $item_type_id;
			$components["parent_product"]["tax_id"] = $tax_id;
			$components["parent_product"]["tax_free"] = $tax_free;
		  foreach ($components as $property_id => $component) {
				$component_quantities = isset($components[$property_id]["quantities"]) ? $components[$property_id]["quantities"] : "";
				if (is_array($component_quantities)) {
					ksort($component_quantities);
					$last_min_quantity = 0; $last_max_quantity = 0;
					foreach($component_quantities as $min_quantity => $quantity_info) {
						$max_quantity = $quantity_info["max_quantity"];
						if ($min_quantity > ($last_max_quantity + $item_min_qty)) {
							if (!in_array(($last_max_quantity + $item_min_qty), $min_quantities)) { $min_quantities[] = ($last_max_quantity + $item_min_qty); }
							if (!in_array(($min_quantity - 1), $max_quantities)) { $max_quantities[] = ($min_quantity - 1); }
						}
						if (!in_array($min_quantity, $min_quantities)) { $min_quantities[] = $min_quantity; }
						if (!in_array($max_quantity, $max_quantities)) { $max_quantities[] = $max_quantity; }
						//$last_min_quantity = $min_quantity;
						$last_max_quantity = $max_quantity;
					}
					if ($max_available_quantity > $last_max_quantity) {
						if (!in_array(($last_max_quantity + 1), $min_quantities)) { $min_quantities[] = ($last_max_quantity + 1); }
						if (!in_array($max_available_quantity, $max_quantities)) { $max_quantities[] = $max_available_quantity; }
					}
				}
			}

			// prepare prices ranges 
			sort($min_quantities); sort($max_quantities);
			while (sizeof($min_quantities) || sizeof($max_quantities)) {
				$min_quantity = array_shift($min_quantities);
				$max_quantity = array_shift($max_quantities);
				$quantity_price = 0; $quantity_tax = 0; $properties_discount = 0;
				// check components and parent product prices
			  foreach ($components as $property_id => $component) {
					$component_quantities = isset($component["quantities"]) ? $component["quantities"] : "";
					$range_found = false; $component_price = 0;
					if (is_array($component_quantities)) {
						foreach($component_quantities as $component_min => $quantity_info) {
							$component_max = $quantity_info["max_quantity"];
							if ($component_min <= $min_quantity && $component_max >= $max_quantity) {
								$range_found = true;
								$component_price = $quantity_info["quantity_price"];
								if (isset($quantity_info["properties_discount"])) {
									$properties_discount = $quantity_info["properties_discount"];
								}
							}
						}
					}
					if (!$range_found) {
						$component_price = $component["base_price"];
					}
					$component_tax = get_tax_amount($tax_rates, $component["item_type_id"], $component_price, 1, $component["tax_id"], $component["tax_free"], $sub_tax_percent);

					$quantity_price += $component_price;
					$quantity_tax += $component_tax;
				}
				$quantities[$min_quantity] = array(
					"max_quantity" => $max_quantity, "quantity_price" => $quantity_price, "quantity_tax" => $quantity_tax, "properties_discount" => $properties_discount
				);
			}
		}


		// check if we can group some pricing ranges
		$last_min_quantity = ""; $last_price = ""; $last_tax = ""; $last_discount = "";
		foreach ($quantities as $min_quantity => $quantity_info) {
			if ($last_min_quantity && $last_price == $quantity_info["quantity_price"] 
				&& $last_tax == $quantity_info["quantity_tax"]  && $last_discount == $quantity_info["properties_discount"]) {
				$quantities[$last_min_quantity]["max_quantity"] = $quantity_info["max_quantity"];
				unset($quantities[$min_quantity]);
			} else {
				$last_min_quantity = $min_quantity; 
				$last_price = $quantity_info["quantity_price"]; 
				$last_tax = $quantity_info["quantity_tax"]; 
				$last_discount = $quantity_info["properties_discount"];
			}
		}

		foreach ($quantities as $min_quantity => $quantity_info) {
			$max_quantity = $quantity_info["max_quantity"];
			$quantity_price = $quantity_info["quantity_price"];
			$quantity_tax = $quantity_info["quantity_tax"];
			$properties_discount = $quantity_info["properties_discount"];

			if ($quantity_prices) { $quantity_prices .= ","; }
			$quantity_prices .= $min_quantity . "," . $max_quantity . "," . $quantity_price . "," . $quantity_tax . "," . round($properties_discount, 2);

			// parse price matrix
			if ($show_price_matrix) {
				if ($min_quantity > 1 || $max_quantity > 1) {
					$is_price_matrix = true;
				}
				if ($min_quantity == $max_quantity) {
					$matrix_quantity = $min_quantity;
				} else if ($max_quantity == MAX_INTEGER) {
					$matrix_quantity = $min_quantity."+";
				} else {
					$matrix_quantity = $min_quantity."-".$max_quantity;
				}
				$t->set_var("matrix_quantity", $matrix_quantity);
				$t->parse("matrix_quantities", true);
		  
				if ($tax_prices_type == 1) {
					$price_incl = $quantity_price;
					$price_excl = $quantity_price - $quantity_tax;
				} else {
					$price_incl = $quantity_price + $quantity_tax;
					$price_excl = $quantity_price;
				}
				if ($tax_prices == 0 || $tax_prices == 1) {
					$t->set_var("matrix_price", currency_format($price_excl));
				} else {
					$t->set_var("matrix_price", currency_format($price_incl));
				}
				if ($tax_prices == 1) {
					$t->set_var("matrix_tax_price", "(".currency_format($price_incl).")");
				} else if ($tax_prices == 2) {
					$t->set_var("matrix_tax_price", "(".currency_format($price_excl).")");
				} 

				$t->parse("matrix_prices", true);
			}

		}

		if ($is_price_matrix) {
			$t->parse("price_matrix", false);
		}


		$product_params["quantity_price"] = $quantity_prices;

		// show options and components selection
		$open_large_image = get_setting_value($settings, "open_large_image", 0);
		$open_large_image_function = ($open_large_image) ? "popupImage(this); return false;" : "openImage(this); return false;";

		$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");
		$watermark                = get_setting_value($settings, "watermark_big_image", 0);
		$tiny_watermark           = get_setting_value($settings, "watermark_tiny_image", 0);
		$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
		$friendly_extension = get_setting_value($settings, "friendly_extension", "");
		$product_link = get_custom_friendly_url("product_details.php") . "?item_id=";
	
		$json_data["options"] = array();

		if (sizeof($options) > 0)
		{
			$is_properties = true;
			foreach ($options as $property_id => $option) 
			{
				$property_id = $option["property_id"];
				$usage_type = $option["usage_type"];
				$property_type_id = $option["property_type_id"];
				$object_id = $form_id . "_" . $property_id;
				$property_block_id = "pr".$form_id."_".$property_id;
				$property_code = $option["property_code"];
				$property_name = $option["property_name"];
				$hide_name = $option["hide_name"];
				$property_hint = $option["property_hint"];
				$parent_property_id = $option["parent_property_id"];
				$parent_value_id = $option["parent_value_id"];
				$parent_value_price = isset($values_prices[$parent_value_id]) ? $values_prices[$parent_value_id] : "";

				$property_price_type = $option["property_price_type"];
				$property_price = $option["property_price"];
				$free_price_type = $option["free_price_type"];
				$free_price_amount = $option["free_price_amount"];
				$percentage_price_type = $option["percentage_price_type"];
				$percentage_property_id = $option["percentage_property_id"];
				$max_limit_type = $option["max_limit_type"];
				$max_limit_length = $option["max_limit_length"];

				$property_description = $option["property_description"];
				$property_class = $option["property_class"];
				$property_style = $option["property_style"];
				$control_type = $option["control_type"];
				$control_style = $option["control_style"];
				$property_required = $option["required"];
				$length_units = $option["length_units"];
				$start_html = $option["start_html"];
				$middle_html = $option["middle_html"];
				$before_control_html = $option["before_control_html"];
				$after_control_html = $option["after_control_html"];
				$end_html = $option["end_html"];
				$onchange_code = $option["onchange_code"];
				$onclick_code = $option["onclick_code"];
				$control_code = $option["control_code"];

				if ($property_required) { $property_class .= " property-required"; }
				$property_class = trim($property_class);

				$json_data["options"][$property_id] = array(
					"id" => $property_id,
					"code" => $property_code,
					"name" => $property_name,
					"hide_name" => $hide_name,
					"hint" => $property_hint,
					"parent_property_id" => $parent_property_id,
					"parent_value_id" => $parent_value_id,
					"property_price_type" => $property_price_type,
					"property_price" => $property_price,
					"free_price_type" => $free_price_type,
					"free_price_amount" => $free_price_amount,
					"percentage_price_type" => $percentage_price_type,
					"percentage_property_id" => $percentage_property_id,
					"property_description" => $property_description,
					"property_class" => $property_class,
					"property_style" => $property_style,
					"control_type" => $control_type,
					"values" => array(),
					"prices" => array(),
				);

				if ($option_notes) {
					$option_notes_html = "";
					if ($property_price != 0) {
						if ($property_price_type == 1) {
							$option_notes_html .= "<br> * ". PRICE_MSG . " : " . currency_format($property_price);
						} else if ($property_price_type == 2) {
							$option_notes_html .= "<br> * " . currency_format($property_price) . " " . PER_LINE_MSG; 
						} else if ($property_price_type == 3) {
							$option_notes_html .= "<br> * " . currency_format($property_price) . " " . PER_LETTER_MSG;
						} else if ($property_price_type == 4) {
							$option_notes_html .= "<br> * " . currency_format($property_price) . " " . PER_NON_SPACE_LETTER_MSG;
						}
					}

					if ($free_price_type == 1) {
						$option_notes_html .= "<br> * " . DISCOUNT_MSG . ": -" . currency_format($free_price_amount);
					} else if ($free_price_type == 2) {
						$option_notes_html .= "<br> * " . str_replace("{free_price_amount}", intval($free_price_amount), FIRST_CONTROLS_ARE_FREE_MSG); //First " . intval($free_price_amount) . " controls are free";
					} else if ($free_price_type == 3) {
						$option_notes_html .= "<br> * " . str_replace("{free_price_amount}", intval($free_price_amount), FIRST_LETTERS_ARE_FREE_MSG); //First " . intval($free_price_amount) . " letters are free";
					} else if ($free_price_type == 4) {
						$option_notes_html .= "<br> * " . str_replace("{free_price_amount}", intval($free_price_amount), FIRST_NONSPACE_LETTERS_ARE_FREE_MSG); //First " . intval($free_price_amount) . " non-space letters are free";
					}

					if ($max_limit_type == 1) {
						$option_notes_html .= "<br> * " . $max_limit_length . " " . LETTERS_ALLOWED_MSG;
					} else if ($max_limit_type == 2) {
						$option_notes_html .= "<br> * " . $max_limit_length . " " . LETTERS_ALLOWED_PER_LINEMSG;
					} else if ($max_limit_type == 3) {
						$option_notes_html .= "<br> * " . $max_limit_length . " " . NON_SPACE_LETTERS_ALLOWED_MSG;
					} else if ($max_limit_type == 4) {
						$option_notes_html .= "<br> * " . $max_limit_length . " " . NON_SPACE_PERLINE_ALLOWED_MSG;
					}

					if ($option_notes_html) {
						$end_html = $option_notes_html . $end_html;
					}
				}
				
				if ($properties_ids) $properties_ids .= ",";
				$properties_ids .= $property_id;
				$tags_replace = array("{form_name}", "{form_id}", "{item_index}", "{product_index}", "{item_id}", "{option_id}", "{property_id}", "{property_code}", "{type}");
				$tags_values  = array($form_name, $form_id, $form_id, $form_id, $item_id, $property_id, $property_id, $property_code, $type);
				if ($onchange_code) {	
					$onchange_code = str_replace($tags_replace, $tags_values, $onchange_code); 
				}
				if ($onclick_code) {	
					$onclick_code = str_replace($tags_replace, $tags_values, $onclick_code); 
				}
				if ($control_code) {	
					$control_code = str_replace($tags_replace, $tags_values, $control_code); 
				}
				if ($start_html) {	
					$start_html = "<span class=\"before-name\">".str_replace($tags_replace, $tags_values, $start_html)."</span>"; 
				}
				if ($middle_html) {	
					$middle_html = "<span class=\"after-name\">".str_replace($tags_replace, $tags_values, $middle_html)."</span>"; 
				}
				if ($before_control_html) {	
					$before_control_html = "<span class=\"before-control\">".str_replace($tags_replace, $tags_values, $before_control_html)."</span>"; 
				}
				if ($after_control_html) {	
					$after_control_html = "<span class=\"after-control\">".str_replace($tags_replace, $tags_values, $after_control_html)."</span>"; 
				}
				if ($end_html) {	
					$end_html = "<span class=\"end-html\">".str_replace($tags_replace, $tags_values, $end_html)."</span>"; 
				}

				$property_control  = "";
				$property_control .= "<input type=\"hidden\" name=\"property_code".$form_id."_" . $property_id . "\"";
				$property_control .= " value=\"" . strip_tags($property_code) . "\" />";
				$property_control .= "<input type=\"hidden\" name=\"property_name".$form_id."_" . $property_id . "\"";
				$property_control .= " value=\"" . strip_tags($property_name) . "\" />";
				$property_control .= "<input type=\"hidden\" name=\"property_required".$form_id."_" . $property_id . "\"";
				$property_control .= " value=\"" . intval($property_required) . "\" />";
				$property_control .= "<input type=\"hidden\" name=\"property_control".$form_id."_" . $property_id . "\"";
				$property_control .= " value=\"" . strtoupper($control_type) . "\" />";
				$property_control .= "<input type=\"hidden\" name=\"property_parent_id".$form_id."_" . $property_id . "\"";
				$property_control .= " value=\"" . $parent_property_id . "\" />";
				$property_control .= "<input type=\"hidden\" name=\"property_parent_value_id".$form_id."_" . $property_id . "\"";
				$property_control .= " value=\"" . $parent_value_id . "\" />";

				$hidden_option = false;
				if ($parent_property_id) {
					// Check if all parent options are visible including top options without parent elements
					$next_parent_id = $parent_property_id;
					$next_value_id = $parent_value_id;
					do {
						if (!isset($options[$next_parent_id]) || sizeof($options[$next_parent_id]["values"]) == 0) {
							$hidden_option = true;
							//$property_style = "display: none;" . $property_style;
						} else if ($next_value_id && !in_array($next_value_id, $options[$next_parent_id]["values"])) {
							$hidden_option = true;
							//$property_style = "display: none;" . $property_style;
						} else {
							// check next parent ID
							$next_value_id = $options[$next_parent_id]["parent_value_id"]; // this need to be checked first and only then we check next_parent_id
							$next_parent_id = $options[$next_parent_id]["parent_property_id"]; // this need to be checked after parent_value_id
						}
					} while ($next_parent_id && !$hidden_option); 
				}
				// hide option block from initial showing
				if ($hidden_option) {
					$property_style = "display: none;" . $property_style;
				}

				if ($property_type_id == 3) {
					$sql_params = array();
					$sql_params["join"]     = " INNER JOIN " . $table_prefix . "items_properties_values ipv ON i.item_id=ipv.sub_item_id ";
					$sql_params["where"]    = " ipv.property_id=" . $db->tosql($property_id, INTEGER);
					$sql = VA_Products::_sql($sql_params, VIEW_ITEMS_PERM, false);
					$db->query($sql);
					$ids = array();
					while ($db->next_record()) {
						$ids[] = $db->f(0);
					}
				}

				$sql_data = new VA_Query();
				$sql_data->add_select("ipv.item_property_id, ipv.quantity, ipv.percentage_price ");
				$sql_data->add_select("ipv.".$additional_price_field.", ipv.property_value, ipv.sub_item_id ");
				if ($usage_type == 2 || $usage_type == 3) {
					$sql_data->add_select("iva.is_default_value ");
				} else {
					$sql_data->add_select("ipv.is_default_value ");
				}
				$sql_data->add_from($table_prefix."items_properties_values ipv ");
				if ($property_type_id == 3) {
					$sql_data->add_select(" i.item_type_id, i.buying_price, i.item_code, i.manufacturer_code, i." . $price_field . ", i.is_sales, i." . $sales_field);
					$sql_data->add_select(" i.tax_id, i.tax_free, i.big_image, i.tiny_image, i.friendly_url, i.use_stock_level, i.stock_level ");
					$sql_data->add_join(" INNER JOIN " . $table_prefix . "items i ON i.item_id=ipv.sub_item_id ");
				} else {
					$sql_data->add_select(" ipv.buying_price, ipv.item_code, ipv.manufacturer_code, ipv.use_stock_level, ipv.stock_level ");
					$sql_data->add_select(" ipv.image_tiny, ipv.image_small, ipv.image_large, ipv.image_super ");
				}
				if ($usage_type == 2) {
					$join  = " INNER JOIN " . $table_prefix . "items_values_assigned iva ";
					$join .= " ON (iva.item_id=" . $db->tosql($item_id, INTEGER) . " AND ipv.item_property_id=iva.property_value_id) ";
					$sql_data->add_join($join);
				} else if ($usage_type == 3) {
					$join  = " LEFT JOIN " . $table_prefix . "items_values_assigned iva ";
					$join .= " ON (iva.item_id=" . $db->tosql($item_id, INTEGER) . " AND ipv.item_property_id=iva.property_value_id) ";
					$sql_data->add_join($join);
				}
				$where  = " ipv.property_id=" . $db->tosql($property_id, INTEGER);
				$where .= " AND ipv.hide_value=0 ";
				$where .= " AND ((ipv.hide_out_of_stock=1 AND ipv.stock_level > 0) OR ipv.hide_out_of_stock=0 OR ipv.hide_out_of_stock IS NULL)";
				if ($property_type_id == 3) {
					if (is_array($ids) && sizeof($ids)) {
						$where .= " AND i.item_id IN (" . $db->tosql($ids, INTEGERS_LIST) . ")";
					} else {
						$where .= " AND i.item_id IS NULL "; // just some false condition
					}
				}
				$sql_data->add_where($where);
				$sql_data->add_order(" ipv.value_order, ipv.item_property_id ");
				$sql = $sql_data->build();

				if (strtoupper($control_type) == "LISTBOX") {
					$properties_prices = "";
					$properties_values = "<option value=\"\">" . SELECT_MSG . " " . $property_name . "</option>" . $eol;
					$db->query($sql);
					$default_property = null;
					$property_function = "return false;";
					while ($db->next_record())
					{
						$item_property_id = $db->f("item_property_id");
						$property_value = get_translation($db->f("property_value"));
						$sub_quantity = $db->f("quantity");
						$sub_item_id = $db->f("sub_item_id");
						$percentage_price = $db->f("percentage_price");
						$image_tiny = get_translation($db->f("image_tiny"));
						$image_small = get_translation($db->f("image_small"));
						$image_large = get_translation($db->f("image_large"));
						$image_super = get_translation($db->f("image_super"));

						$buying_price = 0; $option_price = 0; $option_tax = 0; $option_price_incl = 0; $option_price_excl = 0;
						if ($display_products != 2 || strlen($user_id)) {
							$option_price = $db->f($additional_price_field);	
							$percentage_price = $db->f("percentage_price");
							if ($percentage_price_type == 1 && $percentage_price && $item_price) {
								$option_price = doubleval($option_price) + round(($item_price * $percentage_price) / 100, 2);
							} else if (($percentage_price_type == 2 || $percentage_price_type == 3) && $percentage_price && strlen($parent_value_price)) {
								$option_price = doubleval($option_price) + round(($parent_value_price * $percentage_price) / 100, 2);
								if ($percentage_price_type == 3) {
									$option_price = doubleval($option_price) + round(($item_price * $percentage_price) / 100, 2);
								}
							}

							$buying_price = $db->f("buying_price");	
							if ($property_type_id == 3) {
								$sub_type_id = $db->f("item_type_id");
								$sub_tax_id = $db->f("tax_id");
								$sub_tax_free = $db->f("tax_free");
								if (!strlen($option_price)) {
									$sub_price = $db->f($price_field);
									$sub_buying = $db->f("buying_price");
									$sub_is_sales = $db->f("is_sales");
									$sub_sales = $db->f($sales_field);
									
									$sub_user_price  = ""; 
									$discount_applicable = 1;
									$q_prices    = get_quantity_price($sub_item_id, 1);
									if ($q_prices) {
										$sub_user_price  = $q_prices [0];
										$discount_applicable = $q_prices [2];
									}

									$prices = get_product_price($sub_item_id, $sub_price, $sub_buying, $sub_is_sales, $sub_sales, $sub_user_price, $discount_applicable, $discount_type, $discount_amount);
									$option_price = $prices["base"];	
								}
								if ($sub_quantity > 1) {
									$option_price = $sub_quantity * $option_price; 
								}
								$option_tax = set_tax_price($sub_item_id, $sub_type_id, $option_price, $sub_quantity, 0, $sub_tax_id, $sub_tax_free);
							} else {
								$option_price = get_option_price($option_price, $buying_price, $properties_percent, $discount_applicable, $discount_type, $discount_amount);
								$option_tax = set_tax_price($item_id, $item_type_id, $option_price, $sub_quantity, 0, $tax_id, $tax_free);
							}
							if ($tax_prices_type == 1) {
								$option_price_incl = doubleval($option_price);
								$option_price_excl = doubleval($option_price) - $option_tax;
							} else {
								$option_price_incl = doubleval($option_price) + $option_tax;
								$option_price_excl = doubleval($option_price);
							}
						}
						if ($tax_prices == 2 || $tax_prices == 3) {
							$shown_price = $option_price_incl;
						} else {
							$shown_price = $option_price_excl;
						}

						$json_data["options"][$property_id]["values"][$item_property_id] = array(
							"id" => $item_property_id,
							"value" => $property_value,
							"desc" => $property_value,
							"price" => $option_price,
							"buying" => $buying_price,
							"percentage_price" => $percentage_price,
							"image_tiny" => $image_tiny,
							"image_small" => $image_small,
							"image_large" => $image_large,
							"image_super" => $image_super,
						);

						$item_property_id = $db->f("item_property_id");
						$is_default_value = $db->f("is_default_value");
						$sub_item_id      = $db->f("sub_item_id");
						$image            = get_translation($db->f("big_image"));
						$use_stock_level  = $db->f("use_stock_level");
						$stock_level      = $db->f("stock_level");
						
						$property_selected  = "";
						$properties_prices .= "<input type=\"hidden\" name=\"use_sl_" . $item_property_id . "\"";
						$properties_prices .= " value=\"" . $use_stock_level . "\" />";
						$properties_prices .= "<input type=\"hidden\" name=\"sl_" . $item_property_id . "\"";
						$properties_prices .= " value=\"" . $stock_level . "\" />";
						//$property_function = "return false;";
						if ($image) {
							$property_function = $open_large_image_function;
							if (!preg_match("/^([a-zA-Z]*):\/\/(.*)/i", $image)) {
								if (!$open_large_image) {
									$image_size = @getimagesize($image);
									if (is_array($image_size)) {																		
										$property_function =  " openImage(this, " . $image_size[0]  . ", " . $image_size[1]  . "); return false;";								
									}
								}
								if ($watermark || $restrict_products_images) { 
									$image = "image_show.php?item_id=" . $sub_item_id . "&type=large&vc=".md5($image); 
								}
							}
							$properties_prices .= "<input type=\"hidden\" name=\"option_image_" . $item_property_id . "\"";
							$properties_prices .= " value=\"" . $image . "\" />";
							$properties_prices .= "<input type=\"hidden\" name=\"option_image_action_" . $item_property_id . "\"";
							$properties_prices .= " onclick='" . $property_function . "' />";		
						}
						$is_selected = false;
						if (is_array($selected_properties)) {
							if (isset($selected_properties[$property_id]) && in_array($item_property_id, $selected_properties[$property_id])) {
								$is_selected = true;
							}
						} else if ($is_default_value) {
							$is_selected = true;
						}
						if ($is_selected) {
							$property_selected  = "selected ";
							if (!$hidden_option) {
								$selected_price += doubleval($option_price);
								$options[$property_id]["values"][] = $item_property_id;
							}
						} 

						
						$properties_values .= "<option " . $property_selected . "value=\"" . htmlspecialchars($item_property_id) . "\">";
						$properties_text = "";
						if ($sub_quantity > 1) {
							$properties_text .= $sub_quantity . " x ";
						}
						$properties_text .= htmlspecialchars($property_value);
						if ($option_price > 0) {
							$properties_text .= $option_positive_price_right . currency_format($shown_price) . $option_positive_price_left;
						} elseif ($option_price < 0) {
							$properties_text .= $option_negative_price_right . currency_format(abs($shown_price)) . $option_negative_price_left;
						}
						$properties_values .= $properties_text ."</option>" . $eol;
						if ($is_selected) {
							$default_property = array("image" => $image, "text" => $properties_text, "function" => $property_function);							
						}
					}
					$property_control .= $before_control_html;
					$property_control .= "<label class=\"cl-select\"><select name=\"property".$form_id."_" . $property_id . "\" onchange=\"changeProperty('$form_name','$form_id');";
					if ($onchange_code) {	$property_control .= $onchange_code; }
					$property_control .= "\"";
					if ($onclick_code) {	$property_control .= " onclick=\"" . $onclick_code . "\""; }
					if ($control_code) {	$property_control .= " " . $control_code . " "; }
					if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
					$property_control .= ">" . $properties_values . "</select></label>";				
					// images button 
					if ($default_property && $default_property["image"] && $default_property["text"]) {
						$property_control .= "<a style='display: inline;' href='" . $default_property["image"] .  "' ";
						$property_control .= " title=\"" . htmlspecialchars($default_property["text"]) . "\"";
						$property_control .= " id=\"option_image_action".$form_id."_".$property_id . "\""; ;
						$property_control .= " onclick='" . $default_property["function"] . "'>";
						$property_control .= "<img src='images/icons/view_page.gif' alt='" . VIEW_MSG . "' /></a>";
					} else {
						$property_control .= "<a style='display: none;' href='#' id=\"option_image_action".$form_id."_".$property_id."\"";
						$property_control .= " onclick='$property_function'>";
						$property_control .= "<img src='images/icons/view_page.gif' alt='" . VIEW_MSG . "' /></a>";
					}
					$property_control .= $properties_prices;
					$property_control .= $after_control_html;
				} elseif (strtoupper($control_type) == "RADIOBUTTON" || strtoupper($control_type) == "CHECKBOXLIST") {
					$is_multiple = (strtoupper($control_type) != "RADIOBUTTON");
					if (strtoupper($control_type) == "RADIOBUTTON") {
						$input_type = "radio"; $is_multiple = false;
					} else if (strtoupper($control_type) == "CHECKBOXLIST") {
						$input_type = "checkbox"; $is_multiple = true;
					}
					$property_control .= "<span";
					if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
					$property_control .= ">";
					
					$value_number = 0;
					$db->query($sql);
					while ($db->next_record())
					{
						$value_number++;
						$buying_price = 0; $option_price = 0; $option_tax = 0; $option_price_incl = 0; $option_price_excl = 0;
						$item_property_id = $db->f("item_property_id");
						$sub_quantity = $db->f("quantity");
						$sub_item_id = $db->f("sub_item_id");
						$percentage_price = $db->f("percentage_price");
						$property_value = get_translation($db->f("property_value"));
						$image_tiny = get_translation($db->f("image_tiny"));
						$image_small = get_translation($db->f("image_small"));
						$image_large = get_translation($db->f("image_large"));
						$image_super = get_translation($db->f("image_super"));

						if ($display_products != 2 || strlen($user_id)) {
							$option_price = $db->f($additional_price_field);	
							$percentage_price = $db->f("percentage_price");
							if ($percentage_price_type == 1 && $percentage_price && $item_price) {
								$option_price = doubleval($option_price) + round(($item_price * $percentage_price) / 100, 2);
							} else if (($percentage_price_type == 2 || $percentage_price_type == 3) && $percentage_price && strlen($parent_value_price)) {
								$option_price = doubleval($option_price) + round(($parent_value_price * $percentage_price) / 100, 2);
								if ($percentage_price_type == 3) {
									$option_price = doubleval($option_price) + round(($item_price * $percentage_price) / 100, 2);
								}
							}
							$buying_price = $db->f("buying_price");	

							if ($property_type_id == 3) {
								$sub_type_id = $db->f("item_type_id");
								$sub_tax_id = $db->f("tax_id");
								$sub_tax_free = $db->f("tax_free");

								if (!strlen($option_price)) {
									$sub_price = $db->f($price_field);
									$sub_buying = $db->f("buying_price");
									$sub_is_sales = $db->f("is_sales");
									$sub_sales = $db->f($sales_field);
									
									$sub_user_price  = ""; 
									$discount_applicable = 1;
									$q_prices    = get_quantity_price($sub_item_id, 1);
									if ($q_prices) {
										$sub_user_price  = $q_prices [0];
										$discount_applicable = $q_prices [2];
									}
									$prices = get_product_price($sub_item_id, $sub_price, $sub_buying, $sub_is_sales, $sub_sales, $sub_user_price, $discount_applicable, $discount_type, $discount_amount);
									$option_price = $prices["base"];	
								}
								if ($sub_quantity > 1) {
									$option_price = $sub_quantity * $option_price; 
								}
								$option_tax = set_tax_price($sub_item_id, $sub_type_id, $option_price, $sub_quantity, 0, $sub_tax_id, $sub_tax_free);
								if ($sub_quantity > 1) {
									$option_price = $sub_quantity * $option_price;
								}								
								
							} else {
								$option_price = get_option_price($option_price, $buying_price, $properties_percent, $discount_applicable, $discount_type, $discount_amount);
								$option_tax = set_tax_price($item_id, $item_type_id, $option_price, $sub_quantity, 0, $tax_id, $tax_free);
							}
							if ($tax_prices_type == 1) {
								$option_price_incl = doubleval($option_price);
								$option_price_excl = doubleval($option_price) - $option_tax;
							} else {
								$option_price_incl = doubleval($option_price) + $option_tax;
								$option_price_excl = doubleval($option_price);
							}
						}
						if ($tax_prices == 2 || $tax_prices == 3) {
							$shown_price = $option_price_incl;
						} else {
							$shown_price = $option_price_excl;
						}

						$json_data["options"][$property_id]["values"][$item_property_id] = array(
							"id" => $item_property_id,
							"value" => $property_value,
							"desc" => $property_value,
							"price" => $option_price,
							"buying" => $buying_price,
							"percentage_price" => $percentage_price,
							"image_tiny" => $image_tiny,
							"image_small" => $image_small,
							"image_large" => $image_large,
							"image_super" => $image_super,
						);

						$item_property_id = $db->f("item_property_id");
						$item_code = $db->f("item_code");
						$manufacturer_code = $db->f("manufacturer_code");
						$is_default_value = $db->f("is_default_value");
						$property_value = get_translation($db->f("property_value"));
						$use_stock_level  = $db->f("use_stock_level");
						$stock_level      = $db->f("stock_level");

						$tags_replace = array("{item_code}", "{manufacturer_code}", "{option_value}", "{item_property_id}", "{value_index}",  "{value_number}");
						$tags_values  = array($item_code, $manufacturer_code, $property_value, $item_property_id, ($value_number - 1), $value_number);

						$property_checked = "";
						$property_control .= $before_control_html;
						$property_control .= "<input type=\"hidden\" name=\"use_sl_" . $item_property_id . "\"";
						$property_control .= " value=\"" . $use_stock_level . "\" />";
						$property_control .= "<input type=\"hidden\" name=\"sl_" . $item_property_id . "\"";
						$property_control .= " value=\"" . $stock_level . "\" />";

						$is_selected = false;
						if (is_array($selected_properties)) {
							if (isset($selected_properties[$property_id]) && in_array($item_property_id, $selected_properties[$property_id])) {
								$is_selected = true;
							}
						} else if ($is_default_value) {
							$is_selected = true;
						}
						if ($is_selected) {
							$property_checked = "checked ";
							if (!$hidden_option) {
								$selected_price  += doubleval($option_price);
								$options[$property_id]["values"][] = $item_property_id;
							}
						} 
	
						$control_name = ($is_multiple) ? ("property".$form_id."_".$property_id."_".$value_number) : ("property".$form_id."_".$property_id);
						$property_control .= "<label class=\"cl-".$input_type."\"><input type=\"" . $input_type . "\" id=\"item_property_" . $item_property_id . "\" name=\"" . $control_name . "\" ". $property_checked;
						$property_control .= "value=\"" . htmlspecialchars($item_property_id) . "\" onclick=\"changeProperty('$form_name','$form_id'); ";
						if ($onclick_code) {	$property_control .= $onclick_code; }
						$property_control .= "\"";
						if ($onchange_code) {	$property_control .= " onchange=\"" . $onchange_code . "\""; }
						if ($control_code) {	$property_control .= " " . $control_code . " "; }
						$property_control .= " /><span class=\"cl-element\"></span>";
						
						$image       = get_translation($db->f("big_image"));
						$tiny_image  = get_translation($db->f("tiny_image"));

						if ($sub_quantity > 1) {
							$property_control .= "<span class=\"cl-qty\">".$sub_quantity . " x "."</span>";
						}						
						$property_control .= "<span class=\"cl-desc\">".$property_value."</span>";
						if ($image) {
							$property_control .=  "<span class=\"cl-image\">".product_image_icon($sub_item_id, $property_value, $image, 3)."</span>";
						}
						if ($option_price!=0) { $property_control .= "<span class=\"cl-price\">"; }
						if ($option_price > 0) {
							$property_control .= $option_positive_price_right . currency_format($shown_price) . $option_positive_price_left;
						} elseif ($option_price < 0) {
							$property_control .= $option_negative_price_right . currency_format(abs($shown_price)) . $option_negative_price_left;
						}
						if ($option_price!=0) { $property_control .= "</span>"; }
						$property_control .= "</label>".$after_control_html;
											
						// added here to have a possibilty to parse different tags like item_property_id for any option in HTML, JavaScript or CSS
						$property_control = str_replace($tags_replace, $tags_values, $property_control); 
					}
					$property_control .= "</span>";
					$property_control .= "<input type=\"hidden\" name=\"property_total".$form_id."_".$property_id."\" value=\"".$value_number."\" />";

				} elseif (strtoupper($control_type) == "TEXTBOXLIST") {
					$value_number = 0;
					$db->query($sql);
					while ($db->next_record())
					{
						$value_number++;
						$buying_price = 0; $option_price = 0; $option_tax = 0; $option_price_incl = 0; $option_price_excl = 0;
						$item_property_id = $db->f("item_property_id");
						$sub_quantity = $db->f("quantity");
						$sub_item_id = $db->f("sub_item_id");
						$percentage_price = $db->f("percentage_price");
						$property_value = get_translation($db->f("property_value"));
						$image_tiny = get_translation($db->f("image_tiny"));
						$image_small = get_translation($db->f("image_small"));
						$image_large = get_translation($db->f("image_large"));
						$image_super = get_translation($db->f("image_super"));

						if ($display_products != 2 || strlen($user_id)) {
							$option_price = $db->f($additional_price_field);
							$percentage_price = $db->f("percentage_price");
							if ($percentage_price_type == 1 && $percentage_price && $item_price) {
								$option_price += round(($item_price * $percentage_price) / 100, 2);
							} else if (($percentage_price_type == 2 || $percentage_price_type == 3) && $percentage_price && strlen($parent_value_price)) {
								$option_price += round(($parent_value_price * $percentage_price) / 100, 2);
								if ($percentage_price_type == 3) {
									$option_price += round(($item_price * $percentage_price) / 100, 2);
								}
							}
							$buying_price = $db->f("buying_price");	
							$option_price = get_option_price($option_price, $buying_price, $properties_percent, $discount_applicable, $discount_type, $discount_amount);
							$option_tax = set_tax_price($item_id, $item_type_id, $option_price, $sub_quantity, 0, $tax_id, $tax_free);
							if ($tax_prices_type == 1) {
								$option_price_incl = $option_price;
								$option_price_excl = $option_price - $option_tax;
							} else {
								$option_price_incl = $option_price + $option_tax;
								$option_price_excl = $option_price;
							}
						}
						if ($tax_prices == 2 || $tax_prices == 3) {
							$shown_price = $option_price_incl;
						} else {
							$shown_price = $option_price_excl;
						}

						$json_data["options"][$property_id]["values"][$item_property_id] = array(
							"id" => $item_property_id,
							"value" => $property_value,
							"desc" => $property_value,
							"price" => $option_price,
							"buying" => $buying_price,
							"percentage_price" => $percentage_price,
							"image_tiny" => $image_tiny,
							"image_small" => $image_small,
							"image_large" => $image_large,
							"image_super" => $image_super,
						);

						$item_property_id = $db->f("item_property_id");
						$item_code = $db->f("item_code");
						$manufacturer_code = $db->f("manufacturer_code");
						$property_value = get_translation($db->f("property_value"));

						$tags_replace = array("{item_code}", "{manufacturer_code}", "{option_value}", "{item_property_id}", "{value_index}",  "{value_number}");
						$tags_values  = array($item_code, $manufacturer_code, $property_value, $item_property_id, ($value_number - 1), $value_number);

						$property_checked = "";
						$value_control_name = "property_value".$form_id."_".$property_id."_".$value_number;
						$property_control .= "<input type=\"hidden\" value=\"".$item_property_id."\" name=\"".$value_control_name."\" />";

						$property_control .= $before_control_html;
						$property_control .= $property_value . ": ";
						$control_name = "property".$form_id."_".$property_id."_".$value_number;
						$property_control .= "<input type=\"text\" value=\"\" id=\"item_property_" . $item_property_id . "\" name=\"" . $control_name . "\" ";
						if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
						$property_control .= " onchange=\"changeProperty('$form_name','$form_id');";
						if ($onchange_code) {	
							$property_control .= $onchange_code; 
						}
						$property_control .= "\"";
						if ($onclick_code) {	
							$property_control .= " onclick=\"" . $onclick_code . "\"";
						}
						if ($control_code) {	$property_control .= " " . $control_code . " "; }
						if (($max_limit_type == 2 || $max_limit_type == 4) && $max_limit_length) {
							$property_control .= " onfocus=\"saveInputValue(this);\" oninput=\"checkInputLength(this, " . $max_limit_length . ", " . $max_limit_type . ");\"";
						} else if (($max_limit_type == 1 || $max_limit_type == 3) && $max_limit_length) {
							$property_control .= " onkeypress=\"return checkBoxesMaxLength(event, this, '$form_name', '$form_id', ".$property_id.", ".$max_limit_length.",".$max_limit_type.");\"";
						}
						if ($property_price && $property_price_type) {
							$property_control .= " onkeyup=\"changeProperty('$form_name', '$form_id');\"";
						}

						$property_control .= " />";
						if ($option_price > 0) {
							$property_control .= $option_positive_price_right . currency_format($shown_price) . $option_positive_price_left;
						} elseif ($option_price < 0) {
							$property_control .= $option_negative_price_right . currency_format(abs($shown_price)) . $option_negative_price_left;
						}
						$property_control .= $after_control_html;
						// added here to have a possibilty to parse different tags like item_property_id for any option in HTML, JavaScript or CSS
						$property_control = str_replace($tags_replace, $tags_values, $property_control); 
					}
					$property_control .= "<input type=\"hidden\" name=\"property_total".$form_id."_".$property_id."\" value=\"".$value_number."\" />";

				} else if (strtoupper($control_type) == "IMAGE_SELECT") {
					$db->query($sql);
					$value_number = 0; $selected_value_id = "";
					while ($db->next_record()) {
						$value_number++;
						$item_property_id = $db->f("item_property_id");
						$item_code = $db->f("item_code");
						$manufacturer_code = $db->f("manufacturer_code");
						$property_value = get_translation($db->f("property_value"));
						$is_default_value = $db->f("is_default_value");
						$percentage_price = $db->f("percentage_price");

						$image_tiny = get_translation($db->f("image_tiny"));
						$image_small = get_translation($db->f("image_small"));
						$image_large = get_translation($db->f("image_large"));
						$image_super = get_translation($db->f("image_super"));
						// calculate value price
						$buying_price = 0; $option_price = 0; $option_tax = 0; $option_price_incl = 0; $option_price_excl = 0;
						if ($display_products != 2 || strlen($user_id)) {
							$option_price = $db->f($additional_price_field);	
							$buying_price = $db->f("buying_price");	
							if ($percentage_price_type == 1 && $percentage_price && $item_price) {
								$option_price += round(($item_price * $percentage_price) / 100, 2);
							} else if (($percentage_price_type == 2 || $percentage_price_type == 3) && $percentage_price && strlen($parent_value_price)) {
								$option_price += round(($parent_value_price * $percentage_price) / 100, 2);
								if ($percentage_price_type == 3) {
									$option_price += round(($item_price * $percentage_price) / 100, 2);
								}
							}
							$sub_quantity = 1;
							$option_price = get_option_price($option_price, $buying_price, $properties_percent, $discount_applicable, $discount_type, $discount_amount);
							$option_tax = set_tax_price($item_id, $item_type_id, $option_price, $sub_quantity, 0, $tax_id, $tax_free);

							if ($tax_prices_type == 1) {
								$option_price_incl = $option_price;
								$option_price_excl = $option_price - $option_tax;
							} else {
								$option_price_incl = $option_price + $option_tax;
								$option_price_excl = $option_price;
							}
						}
						if ($tax_prices == 2 || $tax_prices == 3) {
							$shown_price = $option_price_incl;
						} else {
							$shown_price = $option_price_excl;
						}


						$json_data["options"][$property_id]["values"][$item_property_id] = array(
							"id" => $item_property_id,
							"value" => $property_value,
							"price" => $option_price,
							"buying" => $buying_price,
							"percentage_price" => $percentage_price,
							"image_tiny" => $image_tiny,
							"image_small" => $image_small,
							"image_large" => $image_large,
							"image_super" => $image_super,
						);

						//$property_checked = "";
						//$value_control_name = "property_value".$form_id."_".$property_id."_".$value_number;
						//$property_control .= "<input type=\"hidden\" value=\"".$item_property_id."\" name=\"".$value_control_name."\" />";

						$property_control .= $before_control_html;
						//$property_control .= $property_value . ": ";
						$control_name = "property".$form_id."_".$property_id."_".$value_number;
						//$property_control .= "<input type=\"text\" value=\"\" id=\"item_property_" . $item_property_id . "\" name=\"" . $control_name . "\" ";
						$image_class  = "imageSelect";
						if ($is_default_value) {
							$selected_value_id  = $item_property_id;
							$image_class = "imageSelected";
						}
						$image_onclick = "optionImageSelect('$form_name','$form_id', '$property_id', '$item_property_id');" . $onclick_code;
						$image_over = "optionImageShow('$form_name','$form_id', '$property_id', '$item_property_id');";
						//changeProperty('$form_name','$form_id');";
						$property_control .= "<img class=\"$image_class\" src=\"$image_tiny\" id=\"option_image".$form_id."_".$item_property_id . "\" name=\"" . $control_name . "\" ";
						$property_control .= " onclick=\"" . $image_onclick . "\"";
						$property_control .= " onmouseover=\"" . $image_over. "\"";
						$property_control .= " title=\"" . htmlspecialchars($property_value) . "\"";
						$property_control .= " alt=\"" . htmlspecialchars($property_value) . "\"";


						if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
						if ($control_code) {	$property_control .= " " . $control_code . " "; }

						$property_control .= " />";
						$property_control .= $after_control_html;
						// added here to have a possibilty to parse different tags like item_property_id for any option in HTML, JavaScript or CSS
						$property_control = str_replace($tags_replace, $tags_values, $property_control); 


					}
					$property_control .= "<input type=\"hidden\" name=\"property".$form_id."_".$property_id."\" value=\"" . htmlspecialchars($selected_value_id ) . "\" />";

				} elseif (strtoupper($control_type) == "WIDTH_HEIGHT") {
					$units_desc = ($length_units && isset($units[$length_units])) ? $units[$length_units] : "CM";
					$sizes = array();
					$min_width = 0; $max_width = 0; 
					$min_height = 0; $max_height = 0; 
					$sql  = " SELECT * FROM " . $table_prefix . "items_properties_sizes ";
					$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
					$sql .= " ORDER BY width, height ";
					$db->query($sql);	
					if ($db->next_record()) {
						$min_width = round($db->f("width"), 4);
						$min_height = round($db->f("height"), 4);
						do {
							$width = round($db->f("width"),4);
							$height = round($db->f("height"),4);
							if ($width > $max_width) { $max_width = $width; }
							if ($width < $min_width) { $min_width = $width; }
							if ($height > $max_height) { $max_height = $height; }
							if ($height < $min_height) { $min_height = $height; }
							$price = $db->f("price");
							if (!isset($sizes[$width])) { $sizes[$width] = array(); }
							$sizes[$width][$height] = $price;
						} while ($db->next_record());
					}

					$json_data["options"][$property_id]["prices"] = $sizes;

					if (!$hide_name) {
						$property_control .= "<br/>";
					}
					$property_control .= WIDTH_MSG.": ";
					$property_control .= $before_control_html;
					$property_control .= "<input type=\"text\" name=\"property_width".$form_id."_" . $property_id . "\"";
					if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
					if ($onclick_code) {	$property_control .= " onclick=\"" . $onclick_code . "\""; }
					//$property_control .= " onkeyup=\"changeProperty('$form_name','$form_id');\" ";
					$property_control .= " onchange=\"changeProperty('$form_name','$form_id');";
					if ($onchange_code) {	$property_control .= $onchange_code;  }
					$property_control .= "\"";
					if ($control_code) {	$property_control .= " " . $control_code . " "; }
					$property_control .= " value=\"";
					$property_width = "";
					if (is_array($selected_properties)) {
						if (isset($selected_properties[$property_id]) && isset($selected_properties[$property_id]["width"])) {
							$property_width = $selected_properties[$property_id]["width"];
							$property_control .= htmlspecialchars($selected_properties[$property_id]["width"]);
						}
					}
					$property_control .= "\" /> ";
					$property_control .= "(".$min_width . " - " . $max_width . " " . $units_desc.")";
					$property_control .= $after_control_html;

					$property_control .= "<br/>".HEIGHT_MSG.": ";
					$property_control .= $before_control_html;
					$property_control .= "<input type=\"text\" name=\"property_height".$form_id."_" . $property_id . "\"";
					if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
					if ($onclick_code) {	$property_control .= " onclick=\"" . $onclick_code . "\""; }
					//$property_control .= " onkeyup=\"changeProperty('$form_name','$form_id');\" ";
					$property_control .= " onchange=\"changeProperty('$form_name','$form_id');";
					if ($onchange_code) {	$property_control .= $onchange_code;  }
					$property_control .= "\"";
					if ($control_code) {	$property_control .= " " . $control_code . " "; }
					$property_control .= " value=\"";
					$property_height = "";
					if (is_array($selected_properties)) {
						if (isset($selected_properties[$property_id]) && isset($selected_properties[$property_id]["height"])) {
							$property_height = $selected_properties[$property_id]["height"];
							$property_control .= htmlspecialchars($selected_properties[$property_id]["height"]);
						}
					}
					$property_control .= "\" /> ";
					$property_control .= "(".$min_height . " - " . $max_height . " " . $units_desc.")";
					$property_control .= $after_control_html;
					// calculate price if width and height was selected
					if (strlen($property_width) && strlen($property_height)) {
						property_sizes($property_id, $property_width, $property_height, $size_price, $min_width, $max_width, $min_height, $max_height, $prices);
						$selected_price += $size_price;
					}
				} elseif (strtoupper($control_type) == "TEXTBOX" || strtoupper($control_type) == "IMAGEUPLOAD") {
					$property_control .= $before_control_html;
					$property_control .= "<input type=\"text\" name=\"property".$form_id."_" . $property_id . "\"";
					if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
					if ($onclick_code) {	$property_control .= " onclick=\"" . $onclick_code . "\""; }
					$property_control .= " onchange=\"changeProperty('$form_name','$form_id');";
					if ($onchange_code) {	
						$property_control .= $onchange_code; 
					}
					$property_control .= "\"";
					if ($control_code) {	$property_control .= " " . $control_code . " "; }
					if ($max_limit_type && $max_limit_length) {
						$property_control .= " onfocus=\"saveInputValue(this);\" oninput=\"checkInputLength(this, " . $max_limit_length . ", " . $max_limit_type . ");\"";
					}
					if ($property_price && $property_price_type) {
						$property_control .= " onkeyup=\"changeProperty('$form_name', '$form_id');\"";
					}

					$property_control .= " value=\"";
					if (is_array($selected_properties)) {
						if (isset($selected_properties[$property_id])) {
							$property_control .= htmlspecialchars($selected_properties[$property_id][0]);
						}
					} else {
						$property_control .= htmlspecialchars(get_translation($property_description));
					}
					$property_control .= "\" />";
					$property_control .= $after_control_html;
					if (strtoupper($control_type) == "IMAGEUPLOAD") {
						$upload_url = "user_upload.php?filetype=option_image&fid=" . $form_name . "&control_name=property".$form_id."_" . $property_id;
						$property_control .= " <a href=\"javascript:properyImageUpload('" . $upload_url . "')\">" . UPLOAD_IMAGE_MSG . "</a>";
					}
				} elseif (strtoupper($control_type) == "TEXTAREA") {
					$property_control .= $before_control_html;
					$property_control .= "<textarea name=\"property".$form_id."_" . $property_id . "\"";
					if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
					if ($onclick_code) {	$property_control .= " onclick=\"" . $onclick_code . "\""; }
					$property_control .= " onchange=\"changeProperty('$form_name','$form_id');";
					if ($onchange_code) {	
						$property_control .= $onchange_code; 
					}
					$property_control .= "\"";
					if ($control_code) {	$property_control .= " " . $control_code . " "; }
					if ($max_limit_type && $max_limit_length) {
						$property_control .= " onfocus=\"saveInputValue(this);\" oninput=\"checkInputLength(this, " . $max_limit_length . ", " . $max_limit_type . ");\"";
					}
					if ($property_price && $property_price_type) {
						$property_control .= " onkeyup=\"changeProperty('$form_name','$form_id');\"";
					}

					$property_control .= ">";
					if (is_array($selected_properties)) {
						if (isset($selected_properties[$property_id])) {
							$property_control .= htmlspecialchars($selected_properties[$property_id][0]);
						}
					} else {
						$property_control .= htmlspecialchars(get_translation($property_description));
					}
					$property_control .= "</textarea>";
					$property_control .= $after_control_html;
				} else {
					$property_control .= $before_control_html;
					if ($property_required) {
						$property_control .= "<input type=\"hidden\" name=\"property".$form_id."_" . $property_id . "\" value=\"" . htmlspecialchars($property_description) . "\" />";
					}
					$property_control .= "<span";
					if ($control_style) {	$property_control .= " style=\"" . $control_style . "\""; }
					if ($onclick_code) {	$property_control .= " onclick=\"" . $onclick_code . "\""; }
					if ($onchange_code) {	$property_control .= " onchange=\"" . $onchange_code . "\""; }
					if ($control_code) {	$property_control .= " " . $control_code . " "; }
					$property_control .= ">" . get_translation($property_description) . "</span>";
					$property_control .= $after_control_html;
				}

				if ($parse_template) {
					$t->set_var("property_id", $property_id);
					$t->set_var("form_id", $form_id);
					$t->set_var("object_id", $object_id);
					$t->set_var("property_block_id", $property_block_id);
					if ($hide_name) {
						$t->set_var("property_name", $start_html.$middle_html);
					} else if ($property_hint) {
						$t->set_var("property_name", $start_html.$property_hint.$middle_html);
					} else {
						$t->set_var("property_name", $start_html.$property_name.$option_name_delimiter.$middle_html);
					}
					$t->set_var("property_class", $property_class);
					$t->set_var("property_style", $property_style);
					$t->set_var("property_control", $property_control . $end_html);

					$t->parse("properties", true);
				}
				$properties[$property_id] = array(
					"id" => $property_id,
					"code" => $property_code,
					"block_id" => $property_block_id,
					"name" => $property_name,
					"style" => $property_style,
					"control" => $property_control,
					"start_html" => $start_html,
					"middle_html" => $middle_html,
					"end_html" => $end_html,
				);
			} 
			$t->sparse("properties_block", "");
		}

		$params = array(
			"is_any" => $is_properties,
			"ids" => $properties_ids,
			"price" => $selected_price,
			"components_price" => $components_price,
			"components_tax_price" => $components_tax_price,
			"components_points_price" => $components_points_price,
			"components_reward_points" => $components_reward_points,
			"components_reward_credits" => $components_reward_credits,
		);
		$data["params"] = $params;
		$data["properties"] = $properties;
		$data["features"] = $features;
		$data["json"] = $json_data;

		$product_params["comp_price"] = $components_price;
		$product_params["comp_tax"] = $components_tax_price;
		$product_params["properties_ids"] = $properties_ids;

		$db->set_rsi($db_rsi); // return default DB key 
		return $data;
	}

	function calculate_subcomponents_price($item_id, $item_type_id, &$components_price, &$components_tax_price)
	{
	 	global $t, $db, $dbp, $table_prefix;
		global $settings, $currency;
		$db_rsi = $db->set_rsi("p"); // separate DB key for options queries

		$discount_type = get_session("session_discount_type");
		$discount_amount = get_session("session_discount_amount");
		$user_type_id = get_session("session_user_type_id");
		$price_type = get_session("session_price_type");
		if ($price_type == 1) {
			$price_field = "trade_price";
			$sales_field = "trade_sales";
			$additional_price_field = "trade_additional_price";
		} else {
			$price_field = "price";
			$sales_field = "sales_price";
			$additional_price_field = "additional_price";
		}

		// connection for subcomponents 
		if (!isset($dbp) || !is_object($dbp)) {
			$dbp = new VA_SQL($db);
		}

		$components = array(); $components_price = 0; $components_tax_price = 0;
		$sql  = " SELECT ip.* ";
		$sql .= " FROM (" . $table_prefix . "items_properties ip ";
		$sql .= " LEFT JOIN " . $table_prefix . "items_properties_sites ips ON ip.property_id=ips.property_id) ";
		$sql .= " WHERE (ip.item_id=" . $db->tosql($item_id, INTEGER) . " OR ip.item_type_id=" . $db->tosql($item_type_id, INTEGER) . ")";
		$sql .= " AND ip.property_type_id=2 ";
		if (isset($site_id)) {
			$sql .= " AND (ip.sites_all=1 OR ips.site_id=" . $db->tosql($site_id, INTEGER) . ")";
		} else {
			$sql .= " AND ip.sites_all=1 ";
		}
		$sql .= " ORDER BY ip.property_order, ip.property_id ";
		$db->query($sql);
		while ($db->next_record()) {
			$property_id = $db->f("property_id");
			$sub_item_id = $db->f("sub_item_id");
			$sub_quantity = $db->f("quantity");
			$usage_type = $db->f("usage_type");
			$sub_price = $db->f($additional_price_field);
			$components[$property_id] = array("item_id" => $sub_item_id, "quantity" => $sub_quantity, "price" => $sub_price, "usage_type" => $usage_type);
		}

		// check if components need to be assigned first
		foreach ($components as $property_id => $component_info) {
			if ($component_info["usage_type"] == 2 || $component_info["usage_type"] == 3) {
				$sql  = " SELECT item_id FROM " . $table_prefix . "items_properties_assigned ";
				$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
				$sql .= " AND property_id=" . $db->tosql($property_id, INTEGER);
				$db->query($sql);
				if (!$db->next_record()) {
					// remove component if it wasn't assigned to product
					unset($components[$property_id]);
					continue;
				}
			}
		}

		// calculate subcomponents price
		if (sizeof($components) > 0) {

			foreach ($components as $property_id => $component_info) {
				// get subcomponent information
				$sub_item_id = $component_info["item_id"];
				$sub_quantity = $component_info["quantity"];
				$component_price = $component_info["price"];
				// get original information for component product
				$price = 0; $buying_price = 0; $points_price = 0; $reward_points = 0; $reward_credits = 0;
				$sql  = " SELECT i.item_type_id, i.buying_price, i." . $price_field . ", i.is_sales, i." . $sales_field . ", i.tax_id, i.tax_free, ";
				$sql .= " i.is_points_price, i.points_price, i.reward_type, i.reward_amount, i.credit_reward_type, i.credit_reward_amount, ";
				$sql .= " it.reward_type AS type_bonus_reward, it.reward_amount AS type_bonus_amount, ";
				$sql .= " it.credit_reward_type AS type_credit_reward, it.credit_reward_amount AS type_credit_amount ";
				$sql .= " FROM (" . $table_prefix . "items i ";
				$sql .= " LEFT JOIN " . $table_prefix . "item_types it ON i.item_type_id=it.item_type_id) ";
				$sql .= " WHERE i.item_id=" . $db->tosql($sub_item_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$sub_type_id = $db->f("item_type_id");
					$sub_tax_id = $db->f("tax_id");
					$sub_tax_free = $db->f("tax_free");
					$sub_quantity = $db->f("quantity");
					if ($sub_quantity < 1) { $sub_quantity = 1; }
					if (strlen($component_price)) {
						$price = $component_price;
					} else {
						$price = $db->f($price_field);
						$buying_price = $db->f("buying_price");
						$is_sales = $db->f("is_sales");
						$sales_price = $db->f($sales_field);
						
						$discount_applicable = 1;
						$q_prices    = get_quantity_price($sub_item_id, 1);
						if (sizeof($q_prices)) {
							$price  = $q_prices [0];
							$discount_applicable = $q_prices [2];
						} elseif ($is_sales) {
							$price = $sales_price; 
						}

						if ($discount_applicable) {
							if ($discount_type == 1 || $discount_type == 3) {
								$price -= round(($price * $discount_amount) / 100, 2);
							} elseif ($discount_type == 2) {
								$price -= round($discount_amount, 2);
							} elseif ($discount_type == 4) {
								$price -= round((($price - $buying_price) * $discount_amount) / 100, 2);
							}
						}
					}
				}

				$components_price += ($price * $sub_quantity);
				$tax_amount = set_tax_price($sub_item_id, $sub_type_id, $price, $sub_quantity, 0, $sub_tax_id, $sub_tax_free);
				$components_tax_price += ($tax_amount * $sub_quantity);
			}
		}
		
		$db->set_rsi($db_rsi); // return default DB key 
	}

