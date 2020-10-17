<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_packing_html.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/order_items.php");
	include_once($root_folder_path . "includes/parameters.php");
	include_once($root_folder_path . "includes/shopping_cart.php");
	include_once($root_folder_path . "includes/shipping_functions.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("sales_orders");

	if ($multisites_version) {
		$order_id = get_param("order_id");
		$sql  = " SELECT site_id FROM " . $table_prefix . "orders ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$order_site_id = get_db_value($sql);
		if (!$order_site_id) $order_site_id = $site_id;
	}

	$packing = array();
	$sql = "SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings WHERE setting_type='printable'";
	if ($multisites_version) {
		$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($order_site_id, INTEGER) . ") ";
		$sql .= " ORDER BY site_id ASC ";
	}
	$db->query($sql);
	while ($db->next_record()) {
		$packing[$db->f("setting_name")] = $db->f("setting_value");
	}

	$site_url	= get_setting_value($settings, "site_url", "");
	$secure_url	= get_setting_value($settings, "secure_url", "");
	$weight_measure = get_setting_value($settings, "weight_measure", "");
	$packing_image = get_setting_value($packing, "packing_image", 0);
	$show_item_code = get_setting_value($packing, "item_code_packing", 0);
	$show_manufacturer_code = get_setting_value($packing, "manufacturer_code_packing", 0);
	$show_item_weight = get_setting_value($packing, "item_weight_packing", 0);
	$show_actual_weight = get_setting_value($packing, "actual_weight_packing", 0);
	$show_total_weight = get_setting_value($packing, "total_weight_packing", 0);
	$show_total_actual_weight = get_setting_value($packing, "total_actual_weight_packing", 0);
	$sc_properties_packing = get_setting_value($packing, "sc_properties_packing", 0);
	$shipping_method_packing = get_setting_value($packing, "shipping_method_packing", 0);

	// option delimiter and price options
	$option_name_delimiter = get_setting_value($settings, "option_name_delimiter", ": "); 
	$option_positive_price_right = get_setting_value($settings, "option_positive_price_right", ""); 
	$option_positive_price_left = get_setting_value($settings, "option_positive_price_left", ""); 
	$option_negative_price_right = get_setting_value($settings, "option_negative_price_right", ""); 
	$option_negative_price_left = get_setting_value($settings, "option_negative_price_left", "");
	


	// image settings
	if ($packing_image) {
		$site_url = get_setting_value($settings, "site_url", "");
		$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");		
		product_image_fields($packing_image, $image_type_name, $image_field, $image_alt_field, $watermark, $product_no_image);			
	}

	$item_code_width = "0";	$manufacturer_code_width = "0";
	if ($show_item_code && $show_manufacturer_code) {
		$item_code_width = "15%";
		$manufacturer_code_width = "15%";
	} elseif ($show_item_code) {
		$item_code_width = "30%";
	} elseif ($show_manufacturer_code) {
		$manufacturer_code_width = "30%";
	}

	$currency = get_currency();

	$dbi = new VA_SQL();
	$dbi->DBType      = $db_type;
	$dbi->DBDatabase  = $db_name;
	$dbi->DBUser      = $db_user;
	$dbi->DBPassword  = $db_password;
	$dbi->DBHost      = $db_host;
	$dbi->DBPort      = $db_port;
	$dbi->DBPersistent= $db_persistent;


	$sql = "SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings WHERE setting_type='order_info'";
	if ($multisites_version) {
		$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id,INTEGER) . ") ";
		$sql .= " ORDER BY site_id ASC ";
	}
	$db->query($sql);
	while ($db->next_record()) {
		$order_info[$db->f("setting_name")] = $db->f("setting_value");
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_packing_html.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_orders_href", "admin_orders.php");
	$t->set_var("admin_order_href", $order_details_site_url . "admin_order.php");

	if ($packing_image) {
		$t->sparse("image_title", false);
	}
	if ($show_item_code) {
		$t->set_var("item_code_width", $item_code_width);
		$t->sparse("item_code_title", false);
	}
	if ($show_manufacturer_code) {
		$t->set_var("manufacturer_code_width", $manufacturer_code_width);
		$t->sparse("manufacturer_code_title", false);
	}
	

	$order_id = get_param("order_id");
	$r = new VA_Record($table_prefix . "orders");
	$r->add_where("order_id", INTEGER);
	$r->set_value("order_id", $order_id);


	$personal_number = 0;
	$delivery_number = 0;
	for ($i = 0; $i < sizeof($parameters); $i++)
	{                                    
		$personal_param = "show_" . $parameters[$i];
		$delivery_param = "show_delivery_" . $parameters[$i];
		$r->add_textbox($parameters[$i], TEXT);
		$r->add_textbox("delivery_" . $parameters[$i], TEXT);
		if (isset($order_info[$personal_param]) && $order_info[$personal_param] == 1) {
			$personal_number++;
		} else {
			$r->parameters[$parameters[$i]][SHOW] = false;
		}
		if (isset($order_info[$delivery_param]) && $order_info[$delivery_param] == 1) {
			$delivery_number++;
		} else {
			$r->parameters["delivery_" . $parameters[$i]][SHOW] = false;
		}
	}

	$r->add_textbox("invoice_number", TEXT);
	$r->add_textbox("user_id", INTEGER);
	$r->add_textbox("payment_id", INTEGER);
	$r->add_textbox("order_placed_date", DATETIME);
	$r->add_textbox("currency_code", TEXT);
	$r->add_textbox("currency_rate", NUMBER);
	$r->add_textbox("shipping_tracking_id", TEXT);
	$r->add_textbox("remote_address", TEXT);
	$r->add_textbox("cc_name", TEXT);
	$r->add_textbox("cc_first_name", TEXT);
	$r->add_textbox("cc_last_name", TEXT);
	$r->add_textbox("cc_number", TEXT);
	$r->add_textbox("cc_start_date", DATETIME);
	$r->change_property("cc_start_date", VALUE_MASK, array("MM", " / ", "YYYY"));
	$r->add_textbox("cc_expiry_date", DATETIME);
	$r->change_property("cc_expiry_date", VALUE_MASK, array("MM", " / ", "YYYY"));
	$r->add_textbox("cc_type", INTEGER);
	$r->add_textbox("cc_issue_number", INTEGER);
	$r->add_textbox("cc_security_code", TEXT);
	$r->add_textbox("pay_without_cc", TEXT);
	if ($shipping_method_packing) {
		$r->add_textbox("shipping_type_desc", TEXT);
	}

	$r->get_db_values();

	$order_currency_code = $r->get_value("currency_code");
	$order_currency_rate = $r->get_value("currency_rate");
	$currency = get_currency($order_currency_code);
	$order_currency_left = $currency["left"];
	$order_currency_right = $currency["right"];

	$order_placed_date = $r->get_value("order_placed_date");
	$order_date = va_date($date_show_format, $order_placed_date);
	$t->set_var("order_date", $order_date);

	$r->set_value("company_id", get_translation(get_db_value("SELECT company_name FROM " . $table_prefix . "companies WHERE company_id=" . $db->tosql($r->get_value("company_id"), INTEGER))));
	$r->set_value("state_id", get_translation(get_db_value("SELECT state_name FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($r->get_value("state_id"), INTEGER))));
	$r->set_value("country_id", get_translation(get_db_value("SELECT country_name FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($r->get_value("country_id"), INTEGER))));
	$r->set_value("delivery_company_id", get_translation(get_db_value("SELECT company_name FROM " . $table_prefix . "companies WHERE company_id=" . $db->tosql($r->get_value("delivery_company_id"), INTEGER))));
	$r->set_value("delivery_state_id", get_translation(get_db_value("SELECT state_name FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($r->get_value("delivery_state_id"), INTEGER))));
	$r->set_value("delivery_country_id", get_translation(get_db_value("SELECT country_name FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($r->get_value("delivery_country_id"), INTEGER))));
	$r->set_value("cc_type", get_translation(get_db_value("SELECT credit_card_name FROM " . $table_prefix . "credit_cards WHERE credit_card_id=" . $db->tosql($r->get_value("cc_type"), INTEGER))));

	for ($i = 0; $i < sizeof($parameters); $i++) {                                    
		$personal_param = $parameters[$i];
		$delivery_param = "delivery_" . $parameters[$i];
		if ($r->is_empty($personal_param)) {
			$r->parameters[$personal_param][SHOW] = false;
		}
		if ($r->is_empty($delivery_param)) {
			$r->parameters[$delivery_param][SHOW] = false;
		}
	}
	
	$r->set_parameters();

	// parse properties
	$cart_properties = 0; $personal_properties = 0;
	$delivery_properties = 0; $payment_properties = 0; $shipping_properties = 0;
	$properties_total = 0; $properties_taxable = 0;
	$sql  = " SELECT op.property_id, op.property_type, op.property_name, op.property_value, ";
	$sql .= "  op.property_price, op.property_points_amount, op.tax_free, ocp.control_type ";
	$sql .= " FROM (" . $table_prefix . "orders_properties op ";
	$sql .= " INNER JOIN " . $table_prefix . "order_custom_properties ocp ON op.property_id=ocp.property_id)";
	$sql .= " WHERE op.order_id=" . $db->tosql($order_id, INTEGER);
	$sql .= " ORDER BY op.property_order, op.property_id ";
	$db->query($sql);
	while ($db->next_record()) {
		$property_id   = $db->f("property_id");
		$property_type = $db->f("property_type");
		$property_name = get_translation($db->f("property_name"));
		$property_value = get_translation($db->f("property_value"));
		$property_price = $db->f("property_price");
		$property_points_amount = $db->f("property_points_amount");
		$tax_free = $db->f("tax_free");
		$control_type = $db->f("control_type");

		// check value description
		if(($control_type == "CHECKBOXLIST" ||  $control_type == "RADIOBUTTON" || $control_type == "LISTBOX") && is_numeric($property_value)) {
			$sql  = " SELECT property_value FROM " . $table_prefix . "order_custom_values ";
			$sql .= " WHERE property_value_id=" . $dbi->tosql($property_value, INTEGER);
			$dbi->query($sql);
			if ($dbi->next_record()) {
				$property_value = get_translation($dbi->f("property_value"));
			}
		}

		$properties_total += $property_price;
		if ($tax_free != 1) {
			$properties_taxable += $property_price;
		}

		$t->set_var("property_name", $property_name);
		$t->set_var("property_value", $property_value);
		if ($property_price == 0) {
			$t->set_var("property_price", "");
		} else {
			$t->set_var("property_price", $order_currency_left . number_format($property_price * $order_currency_rate, $currency["decimals"], $currency["point"], $currency["separator"]) . $order_currency_right);
		}
		if ($property_type == 1) {
			if ($sc_properties_packing) {
			  $cart_properties++;
				$t->sparse("cart_properties", true);
			}
		} elseif ($property_type == 2) {
			$personal_properties++;
			$t->sparse("personal_properties", true);
		} elseif ($property_type == 3) {
			$delivery_properties++;
			$t->sparse("delivery_properties", true);
		} elseif ($property_type == 4) {
			$payment_properties++;
			$t->sparse("payment_properties", true);
		} elseif ($property_type == 5 || $property_type == 6) {
			$shipping_properties++;
			$t->sparse("shipping_properties", true);
		}
	}


	if ($personal_number > 0 || $personal_properties) {
		$t->parse("personal", false);
	}

	if ($delivery_number > 0 || $delivery_properties) {
		$t->parse("delivery", false);
	}

	if (isset($packing["packing_header"])) {
		$t->set_var("packing_header", nl2br($packing["packing_header"]));
	}
	if (isset($packing["packing_logo"]) && strlen($packing["packing_logo"])) {
		$image_path = $packing["packing_logo"];
		if (!file_exists($image_path)) {
			if (preg_match("/^\.\.\//", $image_path)) {
				if (@file_exists(preg_replace("/^\.\.\//", "", $image_path))) {
					$image_path = preg_replace("/^\.\.\//", "", $image_path);
				}
			} else if (@file_exists("../".$image_path)) {
				$image_path = "../" . $image_path;
			}
		}
		if (preg_match("/^http\:\/\//", $image_path)) {
			$image_size = "";
		} else {
			$image_size = @GetImageSize($image_path);
		}
		$t->set_var("image_path", htmlspecialchars($image_path));
		if (is_array($image_size)) {
			$t->set_var("image_width", "width=\"" . $image_size[0] . "\"");
			$t->set_var("image_height", "height=\"" . $image_size[1] . "\"");
		} else {
			$t->set_var("image_width", "");
			$t->set_var("image_height", "");
		}
		$t->parse("packing_logo", false);
	}
	if (isset($packing["packing_footer"])) {
		$t->set_var("packing_footer", nl2br($packing["packing_footer"]));
	}
	if (isset($packing["sw_orders_coupons_ps"]) && $packing["sw_orders_coupons_ps"]) {
		$sql  = " SELECT oc.coupon_title, oc.coupon_code ";
		$sql .= " FROM ".$table_prefix."orders o LEFT JOIN ".$table_prefix."orders_coupons oc ON o.order_id = oc.order_id ";
		$sql .= " WHERE o.order_id = ".$db->tosql($order_id,INTEGER);
		$sql .= " AND oc.order_item_id=0 ";
		$db->query($sql);
		while ($db->next_record()) {
			$coupon_title = strip_tags(get_translation($db->f("coupon_title")));
			if(strlen($coupon_title)){
					$t->set_var("sw_orders_coupons_ps", "");
					$t->set_var("coupon_title", nl2br($coupon_title));
					$t->set_var("coupon_code", $db->f("coupon_code"));
					$t->parse("sw_orders_coupons_ps", true);
			}
		}
	}

	// NEW SHIPPING STRUCTURE
	$orders_shipments = array(); 
	$sql  = " SELECT * FROM " . $table_prefix . "orders_shipments ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		do {
			$order_shipping_id = $db->f("order_shipping_id");
			$order_items_ids = $db->f("order_items_ids");
			$shipping_desc = get_translation($db->f("shipping_desc"));
			$orders_shipments[$order_shipping_id] = $db->Record;
			$orders_shipments[$order_shipping_id]["desc"] = $shipping_desc;
			$orders_shipments[$order_shipping_id]["order_items_ids"] = array_flip(explode(",", $order_items_ids));
		} while ($db->next_record());

		// check if there are only single shipping method without any items assign all items to it automatically
		if (!$order_items_ids) {
			$order_items_ids = check_shipping_items($order_shipping_id);
			$orders_shipments[$order_shipping_id]["order_items_ids"] = array_flip(explode(",", $order_items_ids));
		}
	} else {
		// OLD SHIPPING DATA
		$shipping_desc = get_translation($r->get_value("shipping_type_desc"));
		if (strlen($shipping_desc)) {
			$orders_shipments[0] = array(
				"desc" => $shipping_desc,
				"tare_weight" => 0,
				"order_items_ids" => "",
			);
		}
	}

	// check if new order empty order shipments 
	foreach ($orders_shipments as $orders_shipment_id => $order_shipment) {
		$order_items_ids = $order_shipment["order_items_ids"];
		if (count($order_items_ids))
		$sql  = " SELECT order_item_id FROM ".$table_prefix."orders_items ";
		$sql .= " WHERE order_shipping_id=" . $db->tosql($order_shipping_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$order_item_id = $db->f("order_item_id");
		}
	}

 
	// get order items data
	$items_ids = array(); $packing_slips = array();
	$sql  = " SELECT * FROM " . $table_prefix . "orders_items ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$sql .= " ORDER BY order_item_id ";
	$db->query($sql);
	while ($db->next_record()) {
		$order_item_id = $db->f("order_item_id");
		$order_shipping_id = $db->f("order_shipping_id");
		$item_id = $db->f("item_id");

		$order_item = array(
			"item_id" => $db->f("item_id"),
			"order_item_id" => $db->f("order_item_id"),
			"quantity" => $db->f("quantity"),
			"item_name" => strip_tags(get_translation($db->f("item_name"))),
			"item_code" => $db->f("item_code"),
			"manufacturer_code" => $db->f("manufacturer_code"),
			"weight" => $db->f("weight"),
			"actual_weight" => $db->f("actual_weight"),
		);
	
		$items_ids[] = $item_id;
		if (strlen($order_shipping_id)) {
			if (!isset($packing_slips[$order_shipping_id])) {
				$packing_slips[$order_shipping_id] = array();
			}
			$packing_slips[$order_shipping_id][$order_item_id] = $order_item;
		}
		/*
		foreach ($orders_shipments as $order_shipping_id => $shipment_data) {
			$order_items_ids = $shipment_data["order_items_ids"];
			if (isset($order_items_ids[$order_item_id])) {
				$packing_slips[$order_shipping_id][$order_item_id] = $order_item;
			}
		}//*/
	}

	// check product images
	$images = array(); 
	if ($packing_image && $image_field) {

		$sql  = " SELECT item_id, ".$image_field.", ".$image_alt_field; 
		$sql .= " FROM " . $table_prefix . "items";
		$sql .= " WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")";
		$db->query($sql);			
		while ($db->next_record()) {
			$image_height = 0;
			$item_id = $db->f("item_id");
			$item_image = $db->f($image_field);
			$image_alt = $db->f($image_alt_field);
			if (!strlen($item_image)) {
				$item_image = $product_no_image;
				$image_exists = false;
			} else {
				$image_exists = true;
			}

			// item image display
			$image_size = ""; $image_width = ""; $image_height = "";
			if ($item_image) {
				if (preg_match("/^http\:\/\//", $item_image)) {
					$image_size = "";
				} else {
					$image_size = @getimagesize($item_image);
					if ($image_exists && ($watermark || $restrict_products_images)) {
						$item_image = "image_show.php?item_id=".$item_id."&type=".$image_type_name."&vc=".md5($item_image);
					}
					if ($is_admin_path) {
						$item_image  = $root_folder_path . $item_image;
					}
				}
			}

			// save image
			$images[$item_id] = array(
				"file" => $item_image,
				"alt"  => $image_alt,
				"size" => $image_size,
			);
		}
	}

	$ps = 0;
	foreach ($packing_slips as $order_shipping_id => $order_items) {
		$ps++;
		$packing_total_weight = 0;
		$packing_total_actual_weight = 0;
		$t->set_var("items", "");
		foreach ($order_items as $order_item_id => $order_item) {

			$item_id = $order_item["item_id"];
			$order_item_id = $order_item["order_item_id"];
			$quantity = $order_item["quantity"];
			$item_name = get_translation($order_item["item_name"]);
			$item_code = $order_item["item_code"];
			$manufacturer_code = $order_item["manufacturer_code"];
			$item_weight = $order_item["weight"];
			$actual_weight = $order_item["actual_weight"];
			$packing_total_weight += ($item_weight * $quantity);
			$packing_total_actual_weight += ($actual_weight * $quantity);

			$item_properties = "";
			$sql  = " SELECT property_name, hide_name, property_value, length_units FROM " . $table_prefix . "orders_items_properties ";
			$sql .= " WHERE order_item_id=" . $db->tosql($order_item_id, INTEGER);
			$sql .= " ORDER BY property_order, property_id, item_property_id ";
			$dbi->query($sql);
			while ($dbi->next_record()) {
				$property_name = get_translation($dbi->f("property_name"));
				$hide_name = $dbi->f("hide_name");
				$property_value = get_translation($dbi->f("property_value"));
				$length_units = $dbi->f("length_units");
				$property_line = "";
				if (!$hide_name) {
					$property_line = $property_name.$option_name_delimiter;
				}
				$property_line .= $property_value;
				if (strlen($property_line)) {
					if ($length_units) {
						$property_line .= " ".strtoupper($length_units);
					}
					$item_properties .= "<br/>".$property_line;
				}
			}


			$t->set_var("quantity", $quantity);
			$t->set_var("item_name", $item_name);
			$t->set_var("item_properties", $item_properties);
			if ($show_item_weight && $item_weight > 0) {
				$item_weight = round($item_weight, 4);
				$t->set_var("item_weight", $item_weight);
				$t->sparse("item_weight_block", false);
			} else {
				$t->set_var("item_weight_block", "");
			}
			if ($show_actual_weight && $actual_weight > 0) {
				$actual_weight= round($actual_weight, 4);
				$t->set_var("actual_weight", $actual_weight);
				$t->sparse("actual_weight_block", false);
			} else {
				$t->set_var("actual_weight_block", "");
			}

			if ($packing_image) {

				if (isset($images[$item_id]) && $images[$item_id]["file"]) { 

					$item_image = $images[$item_id]["file"];
					$item_image_alt = $images[$item_id]["alt"];
					$image_size = $images[$item_id]["size"];
					if (!strlen($item_image_alt)) { $item_image_alt = $item_name; }
					$t->set_var("alt", htmlspecialchars($item_image_alt));
					$t->set_var("src", htmlspecialchars($item_image));
					if (is_array($image_size)) {
						$t->set_var("width", "width=\"" . $image_size[0] . "\"");
						$t->set_var("height", "height=\"" . $image_size[1] . "\"");
					} else {
						$t->set_var("width", "");
						$t->set_var("height", "");
					}
						
					$t->sparse("image_block", false);
				} else {
					$t->set_var("image_block", "");
				}

				$t->sparse("image_cell", false);
			}

			if ($show_item_code) {
				if (strlen($item_code)) {
					if ($show_item_code == 1) {
						$t->set_var("item_code", $item_code);
						$t->sparse("item_code_text", false);
					} elseif ($show_item_code == 2) {
						$item_code_barcode_url = $site_url . "barcode_image.php?text=" . $item_code;
						$t->set_var("item_code_barcode_url", $item_code_barcode_url);
						$t->sparse("item_code_barcode", false);
					}
				} else {
					$t->set_var("item_code_text", "");
					$t->set_var("item_code_barcode", "");
				}
				$t->sparse("item_code_cell", false);
			}
			if ($show_manufacturer_code) {
				if (strlen($manufacturer_code)) {
					if ($show_manufacturer_code == 1) {
						$t->set_var("manufacturer_code", $manufacturer_code);
						$t->sparse("manufacturer_code_text", false);
					} elseif ($show_manufacturer_code == 2) {
						$manufacturer_code_barcode_url = $site_url . "barcode_image.php?text=" . $manufacturer_code;
						$t->set_var("manufacturer_code_barcode_url", $manufacturer_code_barcode_url);
						$t->sparse("manufacturer_code_barcode", false);
					}
				} else {
					$t->set_var("manufacturer_code_text", "");
					$t->set_var("manufacturer_code_barcode", "");
				}

				$t->sparse("manufacturer_code_cell", false);
			}

			$t->parse("items", true);
		}

		// show shipping method
		if (isset($orders_shipments[$order_shipping_id])) {
			$shipping_data = $orders_shipments[$order_shipping_id];
			$t->set_var("shipping_type_desc", $shipping_data["desc"]);
			$t->sparse("shipping_type_desc_block", false);
		}

		// show total weight of order
		if ($show_total_weight && $packing_total_weight > 0) {
			$packing_total_weight = round($packing_total_weight, 4);
			$t->set_var("total_weight", $packing_total_weight);
			$t->sparse("total_weight_block", false);
		}
		// show total actual weight of order
		if ($show_total_actual_weight && $packing_total_actual_weight > 0) {
			$packing_total_actual_weight = round($packing_total_actual_weight, 4);
			$t->set_var("total_actual_weight", $packing_total_actual_weight);
			$t->sparse("total_actual_weight_block", false);
		}

		if ($ps > 1) {
			$t->parse("page_break", false);
		} else {
			$t->set_var("page_break", "");
		}

		$t->parse("packing", true);
		$t->set_var("shipping_type_desc_block", "");
		$t->set_var("total_weight_block", "");
		$t->set_var("total_actual_weight_block", "");
	}

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

?>