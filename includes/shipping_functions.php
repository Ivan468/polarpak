<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  shipping_functions.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


function get_shipping_types($delivery_country_id, $delivery_state_id, $delivery_postal_code, $delivery_site_id, $user_type_id, $delivery_items, $call_center = 0)
{
	global $db, $table_prefix, $site_id, $country_code, $postal_code, $order_total, $state_code, $r, $errors;
	global $goods_total_full, $total_quantity;
	global $shipping_packages, $shipping_items_total, $shipping_weight, $shipping_quantity, $cms_page_code, $va_shipment_grouping;

	// remove space symbols to match postal code with shipping methods
	$check_postal_code = trim($delivery_postal_code);

	// check modules available in delivery items and calculate full cart goods cost
	$is_default = false;
	$custom_modules = array();
	$cart_goods_cost = 0;
	foreach ($delivery_items as $key => $item) {
		// calculate cart goods cost
		$price = isset($item["full_price"]) ? $item["full_price"] : $item["price"];
		$quantity = $item["quantity"];
		$cart_goods_cost += ($quantity * $price);
		// remove downloadable items from checking any shipping methods
		$downloadable = $item["downloadable"];
		if ($downloadable) {
			unset($delivery_items[$key]);
		} else {
			$shipping_modules_default = $item["shipping_modules_default"];
			$shipping_modules_ids = $item["shipping_modules_ids"];
			if ($shipping_modules_default) { $shipping_modules_ids = ""; }
			$delivery_items[$key]["shipping_modules_ids"] = array(); // assign values as array
			/*
			if (!strlen($shipping_modules_ids) && !$shipping_modules_default) {
				// if no modules selected use default methods
				$shipping_modules_default = true;
				$delivery_items[$key]["shipping_modules_default"] = 1;
			}//*/
			if ($shipping_modules_default) { $is_default = true; }
			if (strlen($shipping_modules_ids)) {
				$item_modules = explode(",", $shipping_modules_ids);
				for ($m = 0; $m < sizeof($item_modules); $m++) {
					$module_id = $item_modules[$m];
					$delivery_items[$key]["shipping_modules_ids"][$module_id] = $module_id; // assign values as array
					$custom_modules[$module_id] = $module_id;
				}
			}
		}
	}

	// check cart goods discount if available
	$max_discount = $cart_goods_cost; $cart_vouchers = 0;
	$cart_goods_discount = 0; 
	$coupons = get_session("session_coupons");
	if (is_array($coupons) && count($coupons) > 0) {
		foreach ($coupons as $id => $coupon) {
			$coupon_id = $coupon["COUPON_ID"];
			$sql  = " SELECT c.* FROM (".$table_prefix."coupons c";
			$sql .= " LEFT JOIN  " . $table_prefix . "coupons_sites s ON s.coupon_id=c.coupon_id)";
			$sql .= " WHERE c.coupon_id=" . $db->tosql($coupon_id, INTEGER);
			$sql .= " AND (c.sites_all=1 OR s.site_id=" . $db->tosql($site_id, INTEGER) . ")";
			$db->query($sql);
			if ($db->next_record()) {
				$discount_type = $db->f("discount_type");
				$coupon_discount = $db->f("discount_amount");
				if ($discount_type == 5) {
					// sum gift vouchers to use later 
					$cart_vouchers += $coupon_discount;
				} else {
					// calculate discounts
					if ($discount_type == 1) {
						$discount_amount = round(($cart_goods_cost / 100) * $coupon_discount, 2);
					} else {
						$discount_amount = $coupon_discount;
					}
					if ($discount_amount > $max_discount) {
						$discount_amount = $max_discount;
					}
					$max_discount -= $discount_amount;
					$cart_goods_discount += $discount_amount;
				}
			}
		}
		// add cart vouchers
		if ($cart_vouchers > $max_discount) {
			$cart_vouchers = $max_discount;
		}
		$cart_goods_discount += $cart_vouchers;
	}
	// calculate percent discount
	$cart_percent_discount = ($cart_goods_cost > 0) ? ($cart_goods_discount / $cart_goods_cost) * 100 : 0;

	// check active shipping modules for delivery items
	$custom_modules_ids = array_keys($custom_modules);
	$shipping_modules = array();
	if ($is_default || sizeof($custom_modules_ids) > 0) {
		$sql  = " SELECT * ";
		$sql .= " FROM " . $table_prefix . "shipping_modules ";
		$sql .= " WHERE is_active=1 AND (";
		if ($is_default) {
			$sql .= " is_default=1 ";
		}
		if ($is_default && sizeof($custom_modules_ids) > 0) {
			$sql .= " OR ";
		}
		if (sizeof($custom_modules_ids) > 0) {
			$sql .= " shipping_module_id IN (" . $db->tosql($custom_modules_ids, INTEGERS_LIST) . ") ";
		}
		$sql .= ") ";
		if ($call_center) {
			$sql .= " AND is_call_center=1 ";
		}
		$sql .= " ORDER BY module_order, shipping_module_id ";
		$db->query($sql);
		while ($db->next_record()) {
			$shipping_module_id   = $db->f("shipping_module_id");
			$module_order         = $db->f("module_order");
			$shipping_module_name = get_translation($db->f("shipping_module_name"));
			$user_module_name     = get_translation($db->f("user_module_name"));
			if (!strlen($user_module_name)) { $user_module_name = $shipping_module_name; }
			$is_external          = $db->f("is_external");
			$is_default           = $db->f("is_default");
			$php_external_lib     = $db->f("php_external_lib");
			$external_url         = $db->f("external_url");
			$cost_add_percent     = $db->f("cost_add_percent");
			$shipping_modules[$shipping_module_id] = array(
				"is_default" => $is_default, 
				"module_id" => $shipping_module_id, 
				"module_order" => $module_order, 
				"module_name" => $shipping_module_name, 
				"user_module_name" => $user_module_name, 
				"is_external" => $is_external, 
				"php_external_lib" => $php_external_lib, 
				"external_url" => $external_url, 
				"cost_add_percent" => $cost_add_percent,
			);
			// add default modules ids to items
			if ($is_default) {
				foreach ($delivery_items as $key => $item) {
					$shipping_modules_default = $item["shipping_modules_default"];
					if ($shipping_modules_default) {
						$delivery_items[$key]["shipping_modules_ids"][$shipping_module_id] = $shipping_module_id; // assign values as array
					}
				}
			}
		}
	}

	// check shipping methods available for selected destination 
	foreach ($shipping_modules as $module_id => $module) {
		$sql  = " SELECT st.shipping_type_id, st.postal_match_type, st.postal_codes ";
		$sql .= " FROM ((((";
		$sql .= $table_prefix . "shipping_types st ";
		$sql .= " LEFT JOIN " . $table_prefix . "shipping_types_countries stc ON st.shipping_type_id=stc.shipping_type_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "shipping_types_states stt ON st.shipping_type_id=stt.shipping_type_id) ";
		if ($delivery_site_id) {
			$sql .= " LEFT JOIN " . $table_prefix . "shipping_types_sites s ON st.shipping_type_id=s.shipping_type_id) ";
		} else {
			$sql .= ")";
		}
		if (strlen($user_type_id)) {
			$sql .= " LEFT JOIN " . $table_prefix . "shipping_types_users ut ON st.shipping_type_id=ut.shipping_type_id) ";
		} else {
			$sql .= ")";
		}
		$sql .= " WHERE st.is_active=1 ";
		$sql .= " AND st.shipping_module_id=" . $db->tosql($module_id, INTEGER);
		$sql .= " AND (st.countries_all=1 OR stc.country_id=" . $db->tosql($delivery_country_id, INTEGER, true, false) . ") ";
		$sql .= " AND (st.states_all=1 OR stt.state_id=" . $db->tosql($delivery_state_id, INTEGER, true, false) . ") ";
		if ($delivery_site_id) {
			$sql .= " AND (st.sites_all=1 OR s.site_id=" . $db->tosql($delivery_site_id, INTEGER, true, false) . ")";
		} else {
			$sql .= " AND st.sites_all=1 ";
		}
		if (strlen($user_type_id)) {
			$sql .= " AND (st.user_types_all=1 OR ut.user_type_id=" . $db->tosql($user_type_id, INTEGER, true, false) . ")";
		} else {
			$sql .= " AND st.guest_access=1 ";
		}
		$shipping_types = array();
		$db->query($sql);
		if ($db->next_record()) {
			do {
				$shipping_type_id = $db->f("shipping_type_id");

				// check if postal code matched for shipping method
				$postal_match_type = $db->f("postal_match_type");
				$postal_codes = trim($db->f("postal_codes"));
				if (strlen($postal_codes) && ($postal_match_type == 1 || $postal_match_type == 2)) {
					$pc_match = false;
					$postal_codes = str_replace(";", ",", $postal_codes);
					$postal_codes = explode(",", $postal_codes);
					foreach($postal_codes as $id => $st_pc) {
						$st_pc = trim($st_pc);
						if (strlen($st_pc) && preg_match("/^".preg_quote(trim($st_pc), "/")."/i", $check_postal_code)) {
							$pc_match = true; break;
						}
					}
					if ($postal_match_type == 2) { $pc_match = (!$pc_match); }
				} else {
					// if there is no postal code to match then automatically set it to true
					$pc_match = true;
				}

				if ($pc_match) {
					$shipping_types[$shipping_type_id] = $shipping_type_id;
				}
			} while ($db->next_record());
			$shipping_modules[$module_id]["types_ids"] = array_keys($shipping_types); 
		}
		// deactivate module as there no any methods available
		if (sizeof($shipping_types) == 0) {
			// delete module
			unset($shipping_modules[$module_id]);
			// delete from items
			foreach ($delivery_items as $key => $item) {
				$shipping_modules_ids = $item["shipping_modules_ids"];
				if (isset($item["shipping_modules_ids"][$module_id])) {
					unset($delivery_items[$key]["shipping_modules_ids"][$module_id]);
				}
			}
		}

	}

	// remove inactive modules and modules without any methods and prepare shipping groups
	if (!isset($va_shipment_grouping)) { $va_shipment_grouping = 1; } // 1 - group only products with the same modules type; 2 - group products by the same modules
	$shipping_groups = array();
	foreach ($delivery_items as $key => $item) {
		$shipping_modules_ids = array_keys($item["shipping_modules_ids"]);
		// check if all modules still active and remove non-active module
		foreach ($shipping_modules_ids as $module_key => $module_id) {
			if (!isset($shipping_modules[$module_id])) {
				unset($shipping_modules_ids[$module_key]);
			}
		}
		sort($shipping_modules_ids); // sort array to compare

		// check if there is already shipping group exists for such shipping modules or we need create a new one
		$shipping_group_id = ""; $matched_modules = array();
		foreach ($shipping_groups as $id => $group) {
			$group_modules = $group["modules"];	
			if ($va_shipment_grouping == 2) {
				foreach ($shipping_modules_ids as $sid => $product_module_id) {
					foreach ($group_modules as $gid => $group_module_id) {
						if ($product_module_id == $group_module_id) {
							$matched_modules[] = $product_module_id;

						}
					}
				}
				// if at least one module matched then 
				if (count($matched_modules) > 0) { 
					$shipping_groups[$id]["modules"] = $matched_modules;
					$shipping_group_id = $id;
					break; 
				}
			} else {
				if ($group_modules == $shipping_modules_ids) {
					$shipping_group_id = $id;
					break;
				}
			}
		}
		if (!strlen($shipping_group_id)) {
			$shipping_groups[] = array(
				"modules" => $shipping_modules_ids,
				"group_name" => array(),
				"items" => array(),
				"items_ids" => array(),
			);
			end($shipping_groups);
			$shipping_group_id = key($shipping_groups);
		}

		$delivery_items[$key]["shipping_group_id"] = $shipping_group_id; // assign shipping group
		$shipping_groups[$shipping_group_id]["items"][] = $key;
		$shipping_groups[$shipping_group_id]["items_ids"][] = $item["item_id"];
	}
	// generate group name
	foreach ($shipping_groups as $id => $group) {
		$modules = $group["modules"];
		foreach ($modules as $mid => $module_id) {
			$shipping_groups[$id]["group_name"][] = $shipping_modules[$module_id]["user_module_name"];
		}
	}


	// get country and state codes
	$sql  = " SELECT country_code FROM " . $table_prefix . "countries ";
	$sql .= " WHERE country_id=" . $db->tosql($delivery_country_id, INTEGER);
	$country_code = get_db_value($sql);

	$sql  = " SELECT state_code FROM " . $table_prefix . "states ";
	$sql .= " WHERE state_id=" . $db->tosql($delivery_state_id, INTEGER);
	$state_code = get_db_value($sql);

	$postal_code = $delivery_postal_code;

	foreach ($shipping_groups as $group_id => $group) {
		$shipping_items = $group["items"];
		$modules_ids = $group["modules"];

		$shipping_types = array(); // return this array with available delivery methods
		$shipping_packages = array(); 
		$goods_total_full = 0; $shipping_items_total = 0; $total_quantity = 0; $shipping_weight = 0; $shipping_actual_weight = 0; 
		$shipping_quantity = 0; $shipping_goods_total = 0;
		$goods_weight = 0; $actual_goods_weight = 0; // weight of all items for current shipping group includes free delivery items
		// fit products to packages and get totals
		//foreach ($delivery_items as $id => $item) {
		for ($si = 0; $si < sizeof($shipping_items); $si++) {
			$item_index = $shipping_items[$si];
			$item = $delivery_items[$item_index];

			if (isset($item["full_price"])) {
				$price = $item["full_price"];
			} else {
				$price = $item["price"];
			}
			$quantity = $item["quantity"];
			$packages_number = $item["packages_number"];
			if ($packages_number <= 0) { $packages_number = 0.1; }
			if (isset($item["full_weight"])) {
				$weight = $item["full_weight"];
			} else {
				$weight = $item["weight"];
			}
			$goods_weight += ($weight * $quantity);
			if (isset($item["full_actual_weight"])) {
				$actual_weight = $item["full_actual_weight"];
			} else {
				$actual_weight = $item["actual_weight"];
			}
			$actual_goods_weight += ($actual_weight * $quantity);
			$width = $item["width"];
			$height = $item["height"];
			$length = $item["length"];
			$is_shipping_free = $item["is_shipping_free"];
			$shipping_cost = $item["shipping_cost"];
  
			$item_total = $price * $quantity;
			$total_quantity += $quantity;
			$goods_total_full += $item_total;
			if (!$is_shipping_free) {
				$shipping_quantity += $quantity;
				$shipping_goods_total += $item_total;
				$shipping_items_total += ($shipping_cost * $quantity); 
				$shipping_weight += ($weight * $quantity);
				$shipping_actual_weight += ($actual_weight * $quantity);
				// check each product one by one 
				for ($q = 1; $q <= $quantity; $q++) {
					$packages_left = $packages_number;
					while ($packages_left > 0) {
						// get no more than one package per iteration
						if ($packages_left > 1) {
							$package_number = 1;
						} else {
							$package_number = $packages_left;
						}
						$fit_in_package = false; // check if product could be fit in existed packages
						if ($package_number < 1) {
							foreach ($shipping_packages as $id => $package) {
								if ($package["width"] == $width && $package["height"] == $height
								&& $package["length"] == $length && ($package["packages"] + $package_number) <= 1) {
									$fit_in_package = true;
									$shipping_packages[$id]["price"] += round($price * ($package_number / $packages_number), 2);
									$shipping_packages[$id]["quantity"] += 1;
									$shipping_packages[$id]["packages"] += $package_number;
									$shipping_packages[$id]["weight"] += round($weight * ($package_number / $packages_number), 2);
									$shipping_packages[$id]["actual_weight"] += round($actual_weight * ($package_number / $packages_number), 2);
								}
							}
						}
						if (!$fit_in_package) {
							// add to new package
							$shipping_packages[] = array(
								"price" => round($price * ($package_number / $packages_number), 2),
								"quantity" => 1,
								"packages" => $package_number,
								"weight" => round($weight * ($package_number / $packages_number), 2),
								"actual_weight" => round($actual_weight* ($package_number / $packages_number), 2),
								"width" => $item["width"],
								"height" => $item["height"],
								"length" => $item["length"],
							);
						}
						$packages_left = $packages_left - $package_number;
					}
				}
			}
		}
		// get discounted value
		$shipping_goods_discounted = $shipping_goods_total - round(($shipping_goods_total / 100) * $cart_percent_discount, 2);

		// update goods weight for this group
		$shipping_groups[$group_id]["goods_weight"] = $goods_weight;
		$shipping_groups[$group_id]["actual_goods_weight"] = $actual_goods_weight;
  
		// check if not all items are free to ship
		if ($shipping_quantity > 0) {
			for ($sm = 0; $sm < sizeof($modules_ids); $sm++) {
				$module_id = $modules_ids[$sm];
				$module = $shipping_modules[$module_id];
				$shipping_module_id = $module["module_id"];
				$shipping_module_name = $module["module_name"];
				$is_external  = $module["is_external"];
				$php_external_lib  = $module["php_external_lib"];
				$external_url  = $module["external_url"];
				$cost_add_percent  = $module["cost_add_percent"];
				$types_ids = $module["types_ids"];

				$module_shipping = array();
				$sql  = " SELECT st.* ";
				$sql .= " FROM " . $table_prefix . "shipping_types st ";
				$sql .= " WHERE st.is_active=1 ";
				$sql .= " AND (st.min_weight IS NULL OR st.min_weight<=" . $db->tosql($shipping_weight, NUMBER) . ") ";
				$sql .= " AND (st.max_weight IS NULL OR st.max_weight>=" . $db->tosql($shipping_weight, NUMBER) . ") ";
				$sql .= " AND (st.min_goods_cost IS NULL OR st.min_goods_cost<=" . $db->tosql($shipping_goods_total, NUMBER) . ") ";
				$sql .= " AND (st.max_goods_cost IS NULL OR st.max_goods_cost>=" . $db->tosql($shipping_goods_total, NUMBER) . ") ";
				$sql .= " AND (st.min_discounted_cost IS NULL OR st.min_discounted_cost<=" . $db->tosql($shipping_goods_discounted, NUMBER) . ") ";
				$sql .= " AND (st.max_discounted_cost IS NULL OR st.max_discounted_cost>=" . $db->tosql($shipping_goods_discounted, NUMBER) . ") ";
				$sql .= " AND (st.min_quantity IS NULL OR st.min_quantity<=" . $db->tosql($shipping_quantity, NUMBER) . ") ";
				$sql .= " AND (st.max_quantity IS NULL OR st.max_quantity>=" . $db->tosql($shipping_quantity, NUMBER) . ") ";
				$sql .= " AND st.shipping_module_id=" . $db->tosql($shipping_module_id, INTEGER);
				$sql .= " AND st.shipping_type_id IN (" . $db->tosql($types_ids, INTEGERS_LIST) . ") ";
				$sql .= " ORDER BY st.shipping_order, st.shipping_type_id ";
				$db->query($sql);
				while ($db->next_record()) {
					$row_shipping_type_id = $db->f("shipping_type_id");
					$row_shipping_module_id = $db->f("shipping_module_id");
					$row_shipping_order = $db->f("shipping_order");
					$row_shipping_type_code = $db->f("shipping_type_code");
					$row_shipping_type_desc = get_translation($db->f("shipping_type_desc"));
					$row_shipping_parameters = $db->f("shipping_parameters");
					$row_shipping_time = doubleval($db->f("shipping_time"));
					$image_small = $db->f("image_small");
					$image_small_alt = $db->f("image_small_alt");
					$image_large = $db->f("image_large");
					$image_large_alt = $db->f("image_large_alt");
					$cost_per_order = doubleval($db->f("cost_per_order"));
					$cost_per_product = doubleval($db->f("cost_per_product"));
					$cost_per_weight = doubleval($db->f("cost_per_weight"));
					$ignore_items_shipping_cost = $db->f("ignore_items_shipping_cost");
					$row_tare_weight = doubleval($db->f("tare_weight"));
					$row_shipping_taxable = $db->f("is_taxable");
					$row_shipping_cost = ($cost_per_order + ($cost_per_product * $shipping_quantity) + ($cost_per_weight * ($shipping_weight + $row_tare_weight)));
					$row_shipping_cost = ceil($row_shipping_cost*100)/100; // round to biggest value 0.011 -> 0.02
					if (!$ignore_items_shipping_cost) { $row_shipping_cost += $shipping_items_total; }
					$shipping_type = array($row_shipping_type_id, $row_shipping_type_code, $row_shipping_type_desc, $row_shipping_cost, $row_tare_weight, $row_shipping_taxable, $row_shipping_time);
					$shipping_type = array(
						"id" => $row_shipping_type_id, 
						"module_id" => $row_shipping_module_id, 
						"module_order" => $shipping_modules[$row_shipping_module_id]["module_order"], 
						"shipping_order" => $row_shipping_order, 
						"code" => $row_shipping_type_code, 
						"desc" => $row_shipping_type_desc, 
						"cost" => $row_shipping_cost, 
						"tare_weight" => $row_tare_weight, 
						"taxable" => $row_shipping_taxable, 
						"shipping_time" => $row_shipping_time,
						"parameters" => $row_shipping_parameters,
						"image_small" => $image_small,
						"image_small_alt" => $image_small_alt,
						"image_large" => $image_large,
						"image_large_alt" => $image_large_alt,
					);
					$module_shipping[] = $shipping_type;
					if (!$is_external) {
						$shipping_types[] = $shipping_type;
					}
				}
    
				if ($is_external && strlen($php_external_lib) && sizeof($module_shipping) > 0) {
					$module_params = array();
					$sql  = " SELECT * FROM " . $table_prefix . "shipping_modules_parameters ";
					$sql .= " WHERE shipping_module_id=" . $db->tosql($shipping_module_id, INTEGER);
					$sql .= " AND not_passed<>1 ";
					$db->query($sql);
					while ($db->next_record()) {
						$param_name = $db->f("parameter_name");
						$param_source = $db->f("parameter_source");
						$module_params[$param_name] = $param_source;
					}
					if (!file_exists($php_external_lib)) {
						// check sub path if script run from admin folder
						if (preg_match("/^\.\//", $php_external_lib)) {
							$php_external_lib = ".".$php_external_lib;
						} else {
							$php_external_lib = "../".$php_external_lib;
						}
					}
					include($php_external_lib);
				}
				if ($cost_add_percent && $shipping_types) {
					for($i=0, $ic = count($shipping_types); $i<$ic; $i++) {
						$shipping_types[$i]["cost"] = $shipping_types[$i]["cost"] * (1 + $cost_add_percent/100);
					}
				}
			}
		}

		// check if there are no any methods
		// add default shipping type in case if there are no methods available
		if (sizeof($shipping_types) == 0) {
			if ($shipping_items_total > 0) {
				$shipping_type = array(
					"id" => 0, 
					"module_id" => 0, 
					"module_order" => 1, 
					"shipping_order" => 1, 
					"code" => "", 
					"desc" => PROD_SHIPPING_MSG, 
					"cost" => $shipping_items_total, 
					"tare_weight" => 0, 
					"taxable" => 1, 
					"shipping_time" => 0,
				);
				$shipping_types[] = $shipping_type;
				//array(0, "", PROD_SHIPPING_MSG, $shipping_items_total, 0, 1, 0);
			} else if ($shipping_quantity == 0 && $goods_weight > 0) {
				// all products has a free delivery
				$shipping_type = array(
					"id" => 0, 
					"module_id" => 0, 
					"module_order" => 1, 
					"shipping_order" => 1, 
					"code" => "", 
					"desc" => PROD_SHIPPING_MSG, 
					"cost" => 0, 
					"tare_weight" => 0, 
					"taxable" => 0, 
					"shipping_time" => 0,
				);
				$shipping_types[] = $shipping_type;
				//$shipping_types[] = array(0, "", PROD_SHIPPING_MSG, 0, 0, 0, 0);
			}
		}

		// apply module order first and then methods order
		if (is_array($shipping_types)) {
			$modules_order = array();
			$types_order = array();
			foreach ($shipping_types as $id => $shipping_type) {
				$modules_order[$id] = $shipping_type["module_order"];
				$types_order[$id] = $shipping_type["shipping_order"];
			}
			array_multisort($modules_order, $types_order, $shipping_types);
		}

		// save shipping types to group
		$shipping_groups[$group_id]["types"] = $shipping_types;
	}


	return $shipping_groups;
}


function check_shipping_items($order_shipping_id)
{
	global $db, $table_prefix;

	// check if any items assigned
	$order_id = ""; $order_items_ids = "";
	$sql  = " SELECT order_id, order_items_ids FROM " . $table_prefix . "orders_shipments ";
	$sql .= " WHERE order_shipping_id=" . $db->tosql($order_shipping_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$order_id = $db->f("order_id");
		$order_items_ids = $db->f("order_items_ids");
	} else {
		// shipping wasn't found in DB
		return false;
	}
	
	if (!$order_items_ids) {
		// check if there are any items available
		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "orders_items ";
		$sql .= " WHERE order_shipping_id=" . $db->tosql($order_shipping_id, INTEGER);
		$db->query($sql);
		$db->next_record();
		$shipping_items = $db->f(0);
		if (!$shipping_items) {
			// try assign items with default zero value
			$sql  = " UPDATE ".$table_prefix."orders_items ";
			$sql .= " SET order_shipping_id=" . $db->tosql($order_shipping_id, INTEGER);
			$sql .= " WHERE order_id = ".$db->tosql($order_id, INTEGER);
			$sql .= " AND order_shipping_id=0 ";
			$db->query($sql);
		} 
		// check if we can find any items for current shipping
		$ids = array();
		$sql  = " SELECT order_item_id FROM " . $table_prefix . "orders_items ";
		$sql .= " WHERE order_shipping_id=" . $db->tosql($order_shipping_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$order_item_id = $db->f("order_item_id");
			$ids[] = $order_item_id;
		}
		if (count($ids)) {
			$order_items_ids = implode(",", $ids);
			// update shipment with new order items ids
			$sql  = " UPDATE " . $table_prefix . "orders_shipments ";
			$sql .= " SET order_items_ids=" . $db->tosql($order_items_ids, TEXT);
			$sql .= " WHERE order_shipping_id=" . $db->tosql($order_shipping_id, INTEGER);
			$db->query($sql);
		}
	}
	return $order_items_ids;
}