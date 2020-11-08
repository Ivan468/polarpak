<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_order_shipping.php                                 ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/shipping_functions.php");
	include_once($root_folder_path . "includes/order_items.php");
	include_once($root_folder_path . "includes/order_recalculate.php");
	include_once($root_folder_path . "messages/" . $language_code . "/download_messages.php");
	include_once("./admin_common.php");

	check_admin_security("sales_orders");

	$cms_page_code = "admin_order_shipping";
	$order_id = get_param("order_id");
	$order_items = get_param("order_items");
	$operation = get_param("operation");
	$order_item_id = get_param("order_item_id");
	$order_items_ids = get_param("order_items_ids");
	$order_shipping_id = get_param("order_shipping_id");
	$shipping_tracking_id = get_param("shipping_tracking_id");
	$shipping_company_id = get_param("shipping_company_id");
	$currency = get_currency();

	if ($operation == "remove_item") {
		$sql  = " SELECT order_id, order_items_ids FROM " . $table_prefix . "orders_shipments ";
		$sql .= " WHERE order_shipping_id=" . $db->tosql($order_shipping_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$order_id = $db->f("order_id");
			$current_items_ids = $db->f("order_items_ids");
			$current_items_ids = array_flip(explode(",", $current_items_ids));
			unset($current_items_ids[$order_item_id]);
			$new_items_ids = implode(",", array_keys($current_items_ids));
			// update shipment with new order items ids
			$sql  = " UPDATE " . $table_prefix . "orders_shipments ";
			$sql .= " SET order_items_ids=" . $db->tosql($new_items_ids, TEXT);
			$sql .= " WHERE order_shipping_id=" . $db->tosql($order_shipping_id, INTEGER);
			$db->query($sql);
		}
	}

	// check order site_id
	$order_site_id = "";
	$sql  = " SELECT o.site_id ";
	$sql .= " FROM " . $table_prefix . "orders o ";
	$sql .= " WHERE o.order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$order_site_id = $db->f("site_id");	
	}

	// get order profile settings
	$order_info = array();
	$sql  = "SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= "WHERE setting_type='order_info'";
	if ($order_site_id) {
		$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($order_site_id, INTEGER, true, false) . ")";
		$sql .= " ORDER BY site_id ASC ";
	} else {
		$sql .= " AND site_id=1 ";
	}
	$db->query($sql);
	while ($db->next_record()) {
		$order_info[$db->f("setting_name")] = $db->f("setting_value");
	}
	$subcomponents_show_type = get_setting_value($order_info, "subcomponents_show_type", 0);

	if ($order_items && $subcomponents_show_type == 1) {
		// check for subcomponents 
		$sql  = " SELECT oi.order_item_id ";
		$sql .= " FROM " . $table_prefix . "orders_items oi ";
		$sql .= " WHERE oi.order_id=" . $db->tosql($order_id, INTEGER);
		$sql .= " AND (oi.order_item_id IN (" . $db->tosql($order_items, INTEGERS_LIST) . ") ";
		$sql .= " OR oi.top_order_item_id IN (" . $db->tosql($order_items, INTEGERS_LIST) . ")) ";
		$db->query($sql);
		while ($db->next_record()) {
			$order_item_id = $db->f("order_item_id");
			if ($order_items_ids) { $order_items_ids .= ","; }
			$order_items_ids .= $order_item_id;
		}
	} else if ($order_items) {
		$order_items_ids = $order_items;
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_order_shipping.html");
	$t->set_var("order_id", htmlspecialchars($order_id));
	$t->set_var("order_shipping_id", htmlspecialchars($order_shipping_id));
	$t->set_var("order_items_ids", htmlspecialchars($order_items_ids));
	if (strlen($order_shipping_id)) {
		$t->parse("update_button", false);
	} else {
		$t->parse("add_button", false);
	}

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_order_href", "admin_order.php");
	$t->set_var("admin_order_shipping_href", "admin_order_shipping.php");


	$operation = get_param("operation");
	if ($operation == "update") {
		// check custom shipping
		$shipping_type_id = get_param("shipping_type_id");
		$shipping_cost = get_param("shipping_cost");
		if ($shipping_cost) { $shipping_cost = $shipping_cost / $currency["rate"]; }
		$custom_shipping_type = get_param("custom_shipping_type");
		$custom_shipping_cost = get_param("custom_shipping_cost");
		if ($custom_shipping_cost) { $custom_shipping_cost = $custom_shipping_cost / $currency["rate"]; }
		if (strlen($shipping_type_id) && strlen($shipping_cost)) {
			update_order_shipping($order_id, $order_items_ids, $order_shipping_id, $shipping_type_id, "", $shipping_cost, $shipping_tracking_id, $shipping_company_id);
		} else {
			update_order_shipping($order_id, $order_items_ids, $order_shipping_id, "", $custom_shipping_type, $custom_shipping_cost, $shipping_tracking_id, $shipping_company_id);
		}

		$t->set_var("onload_js", "reloadParentWin();closeWindow();");
	} else if ($operation) {
		$t->set_var("onload_js", "reloadParentWin();");
	}

	// get general order data 
	$sql  = " SELECT site_id, user_type_id, country_id, state_id, zip, ";
	$sql .= " delivery_country_id, delivery_state_id, delivery_zip, weight_total, ";
	$sql .= " shipping_type_id, shipping_type_desc, shipping_cost, shipping_tracking_id ";
	$sql .= " FROM " . $table_prefix . "orders ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$delivery_site_id = $db->f("site_id");
		$user_type_id = $db->f("user_type_id");
		$delivery_country_id = $db->f("delivery_country_id");
		$delivery_state_id = $db->f("delivery_state_id");
		$delivery_postal_code = $db->f("delivery_zip");
		if (!strlen($delivery_country_id)) {
			$delivery_country_id = $db->f("country_id");
			$delivery_state_id = $db->f("state_id");
			$delivery_postal_code = $db->f("zip");
		}
		if ($order_shipping_id) {
			$sql  = " SELECT * FROM " . $table_prefix . "orders_shipments ";
			$sql .= " WHERE order_shipping_id=" . $db->tosql($order_shipping_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$cur_weight_total = doubleval($db->f("goods_weight")) + doubleval($db->f("tare_weight"));
				$cur_shipping_type_id = $db->f("shipping_id");
				$cur_shipping_type_desc = get_translation($db->f("shipping_desc"));
				$cur_shipping_cost = doubleval($db->f("shipping_cost"));
				$cur_shipping_tracking_id = $db->f("tracking_id");
				$cur_shipping_company_id = $db->f("shipping_company_id");
			}
		} else {
			$cur_weight_total = doubleval($db->f("weight_total"));
			$cur_shipping_type_id = $db->f("shipping_type_id");
			$cur_shipping_type_desc = get_translation($db->f("shipping_type_desc"));
			$cur_shipping_cost = doubleval($db->f("shipping_cost"));
			$cur_shipping_tracking_id = $db->f("shipping_tracking_id");
			$cur_shipping_company_id = $db->f("shipping_company_id");
		}
	} else {
		echo ERRORS_MSG;
		exit;
	}

	// check for order items for existed shipments
	if ($order_shipping_id) {
		$sql  = " SELECT * FROM " . $table_prefix . "orders_shipments ";
		$sql .= " WHERE order_shipping_id=" . $db->tosql($order_shipping_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$order_items_ids = $db->f("order_items_ids");
		}
		// check if there are only single shipping method without any items assign all items to it automatically
		if (!$order_items_ids) {
			$order_items_ids = check_shipping_items($order_shipping_id);
		}
	}

	$delivery_items = array();	$shipping_items_ids = "";
	$total_quantity = 0; $total_weight = 0; $total_packages = 0; $total_shipping_cost = 0;
	$sql  = " SELECT oi.*, ";
	$sql .= " i.shipping_modules_default, i.shipping_modules_ids ";
	$sql .= " FROM (" . $table_prefix . "orders_items oi ";
	$sql .= " LEFT JOIN " . $table_prefix . "items i ON i.item_id=oi.item_id) ";
	if (strlen($order_shipping_id)) {
		if ($order_items_ids) {
			$sql .= " WHERE oi.order_id=" . $db->tosql($order_id, INTEGER);
			$sql .= " AND oi.order_item_id IN (" . $db->tosql($order_items_ids, INTEGERS_LIST) . ") ";
		} else {
			$sql .= " WHERE oi.order_shipping_id=" . $db->tosql($order_shipping_id, INTEGER);
			$sql .= " OR (oi.order_id=" . $db->tosql($order_id, INTEGER) . " AND oi.order_shipping_id=0) ";
		}
	} else {
		$sql .= " WHERE oi.order_id=" . $db->tosql($order_id, INTEGER);
		$sql .= " AND oi.order_item_id IN (" . $db->tosql($order_items_ids, INTEGERS_LIST) . ") ";
	}
	$db->query($sql);
	while ($db->next_record()) {
		$order_item_id = $db->f("order_item_id");
		$item_id = $db->f("item_id");
		$item_name = get_translation($db->f("item_name"));
		$price = $db->f("price");
		$quantity = $db->f("quantity");
		$packages_number = $db->f("packages_number");
		if ($packages_number <= 0) { $packages_number = 0.1; }
		$actual_weight = $db->f("actual_weight");
		$weight = $db->f("weight");
		$width = $db->f("width");
		$height = $db->f("height");
		$length = $db->f("length");
		$downloadable = $db->f("downloadable");
		$is_shipping_free = $db->f("is_shipping_free");
		$shipping_cost = $db->f("shipping_cost");
		$shipping_modules_default = $db->f("shipping_modules_default");
		$shipping_modules_ids = $db->f("shipping_modules_ids");
		if ($shipping_items_ids) { $shipping_items_ids .= ","; }
		$shipping_items_ids .= $order_item_id;

		// populate delivery items to calculate cost
		$delivery_items[] = array(
			"item_id" => $item_id,
			"item_name" => $item_name,
			"price" => $price,
			"quantity" => $quantity,
			"packages_number" => $packages_number,
			"weight" => $weight,
			"actual_weight" => $actual_weight,
			"width" => $width,
			"height" => $height,
			"length" => $length,
			"downloadable" => $downloadable,
			"is_shipping_free" => $is_shipping_free,
			"shipping_cost" => $shipping_cost,
			"shipping_modules_default" => $shipping_modules_default,
			"shipping_modules_ids" => $shipping_modules_ids,
		);

		// calculate weight, cost and packages for total quantity to show this info per product
		$packages_number *= $quantity;
		$weight *= $quantity;
		$shipping_cost *= $quantity;
		
		$total_quantity += $quantity;
		$total_weight += $weight; 
		$total_packages += $packages_number; 
		$total_shipping_cost += $shipping_cost;

		$confirm_message = str_replace("{item_name}", $item_name, REMOVE_PRODUCT_SHIPPING_MSG);
		$confirm_message = str_replace("'", "\'", $confirm_message);
		$t->set_var("order_item_id", htmlspecialchars($order_item_id));
		$t->set_var("item_name", htmlspecialchars($item_name));
		$t->set_var("confirm_message", htmlspecialchars($confirm_message));
		$t->set_var("price", currency_format($price));
		$t->set_var("quantity", $quantity);
		$t->set_var("packages_number", round($packages_number, 4));
		$t->set_var("weight", round($weight, 4));
		$t->set_var("width", round($width, 4));
		$t->set_var("height", round($height, 4));
		$t->set_var("length", round($length, 4));
		if ($downloadable && $is_shipping_free) {
			$t->set_var("shipping_cost", FREE_SHIPPING_MSG);
		} else {
			$t->set_var("shipping_cost", currency_format($shipping_cost));
		}
		if ($order_shipping_id) {
			$t->parse("item_remove_button", false);
		} else {
			$t->set_var("shipping_cost", "");
		}

	      
		$t->parse("order_packages", true);
	}
	if ($shipping_items_ids && !$order_items_ids) {
		// update shipment with order items ids
		$sql  = " UPDATE " . $table_prefix . "orders_shipments ";
		$sql .= " SET order_items_ids=" . $db->tosql($shipping_items_ids, TEXT);
		$sql .= " WHERE order_shipping_id=" . $db->tosql($order_shipping_id, INTEGER);
		$db->query($sql);
	}

	$t->set_var("total_quantity", intval($total_quantity));
	$t->set_var("total_weight", round($total_weight, 4));
	$t->set_var("total_packages", round($total_packages, 4));
	$t->set_var("total_shipping_cost", currency_format($total_shipping_cost));

	// parse current shipping method and tracking number
	if (strlen($cur_shipping_type_desc) || $cur_shipping_cost > 0) {
		if (!strlen($cur_shipping_type_desc)) {	
			$cur_shipping_type_desc = PROD_SHIPPING_MSG;
		}
		if ($cur_shipping_cost > 0) {	
			$cur_shipping_type_desc .= " (". currency_format($cur_shipping_cost) .")";
		}
	} else {
		$cur_shipping_type_desc = NO_SHIPPING_MSG;
	}
	if (!strlen($cur_shipping_tracking_id)) {
		$cur_shipping_tracking_id = NOT_AVAILABLE_MSG;
	}

	$cur_shipping_company = NOT_AVAILABLE_MSG;
	if (strlen($cur_shipping_company_id)) {
		$sql  = " SELECT * FROM " . $table_prefix . "shipping_companies ";
		$sql .= " WHERE shipping_company_id=" . $db->tosql($cur_shipping_company_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$cur_shipping_company = get_translation($db->f("company_name"));
		} 
	}

	$t->set_var("cur_shipping_type_desc", $cur_shipping_type_desc);
	$t->set_var("cur_shipping_tracking_id", $cur_shipping_tracking_id);
	$t->set_var("cur_shipping_company", htmlspecialchars($cur_shipping_company));
	$t->set_var("currency_left", $currency["left"]);
	$t->set_var("currency_right", $currency["right"]);

	// check predefined shipping methods
	$shipping_groups = get_shipping_types($delivery_country_id, $delivery_state_id, $delivery_postal_code, $delivery_site_id, $user_type_id, $delivery_items);
	if (is_array($shipping_groups) && sizeof($shipping_groups) > 0) {
		foreach ($shipping_groups as $group_id => $shipping_group) {
			$shipping_types = $shipping_group["types"]; // get shipping types
			// parse empty shipping
			$t->set_var("shipping_type_id", "");
			$t->set_var("shipping_type_desc", "");
			$t->parse("shipping_types", true);
			for ($i = 0; $i < sizeof($shipping_types); $i++) {
				$shipping_type_id = $shipping_types[$i]["id"];
				$shipping_type_desc = $shipping_types[$i]["desc"];
				$shipping_cost = $shipping_types[$i]["cost"];
				if ($shipping_cost > 0) {
					$shipping_type_desc .= " (" . currency_format($shipping_cost) . ")";
				} else {
				}
				$t->set_var("shipping_type_id", $shipping_type_id);
				$t->set_var("shipping_type_desc", $shipping_type_desc);
				$t->set_var("shipping_cost", round($shipping_cost * $currency["rate"], $currency["decimals"]));
				$t->parse("shipping_cost_values", true);
				$t->parse("shipping_types", true);
			}
			$t->parse("predefined_shipping_types", false);
		}
	}

	$shipping_companies = get_db_values("SELECT shipping_company_id,company_name FROM " . $table_prefix . "shipping_companies ORDER BY shipping_company_id ", array(array("", "")));
	if (count($shipping_companies) > 1) {
		set_options($shipping_companies, "", "shipping_company_id");
		$t->sparse("shipping_company_id_block", false);
		$t->sparse("shipping_company_block", false);
	}

	$errors = "";

	if (strlen($errors)) {
		$t->set_var("after_upload", "");
		$t->set_var("errors_list", $errors);
		$t->parse("errors", false);
	} else {
		$t->set_var("errors", "");
	}

	$t->pparse("main");

?>