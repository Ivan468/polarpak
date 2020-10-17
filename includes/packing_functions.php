<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  packing_functions.php                                    ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$root_folder_path = (isset($is_admin_path) && $is_admin_path) ? "../" : "./";
	include_once($root_folder_path . "includes/pdflib.php");
	include_once($root_folder_path . "includes/pdf.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/parameters.php");
	include_once($root_folder_path . "includes/shopping_cart.php");
	include_once($root_folder_path . "includes/barcode_functions.php");
	include_once($root_folder_path . "includes/shipping_functions.php");

	@ini_set("max_execution_time", 200);
	function pdf_packing_slip($orders_ids, $pdf_params = array())
	{
		global $db, $pdf, $table_prefix, $settings, $currency, $parameters, $site_id;
		global $is_admin_path, $root_folder_path, $date_show_format;

		// global settings if we create a new PDF document and return PDF in the end
		$new_pdf = get_setting_value($pdf_params, "new_pdf", true);
		$return_pdf = get_setting_value($pdf_params, "return_pdf", true);
		$product_no_image = get_setting_value($settings, "product_no_image", "");

		// additional connection
		$dbi = new VA_SQL();
		$dbi->DBType      = $db->DBType      ;
		$dbi->DBDatabase  = $db->DBDatabase  ;
		$dbi->DBUser      = $db->DBUser      ;
		$dbi->DBPassword  = $db->DBPassword  ;
		$dbi->DBHost      = $db->DBHost      ;
		$dbi->DBPort      = $db->DBPort      ;
		$dbi->DBPersistent= $db->DBPersistent;

		// get initial invoice settings
		$packing = array();
		$sql  = " SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings";
		$sql .= " WHERE setting_type='printable'";
		if (isset($site_id)) {
			$sql .= "AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
			$sql .= "ORDER BY site_id ASC";
		} else {
			$sql .= "AND site_id=1";
		}
		$db->query($sql);
		while ($db->next_record()) {
			$packing[$db->f("setting_name")] = $db->f("setting_value");
		}

		// global variables
		$site_url	= get_setting_value($settings, "site_url", "");
		$secure_url	= get_setting_value($settings, "secure_url", "");
		$tmp_dir = get_setting_value($settings, "tmp_dir", "");
		$weight_measure = get_setting_value($settings, "weight_measure", "");
		$tmp_images = array();
		$packing_sets = array();
		$order_sets = array();

		// option delimiter and price options
		$option_name_delimiter = strip_tags(get_setting_value($settings, "option_name_delimiter", ": ")); 
		$option_positive_price_right = get_setting_value($settings, "option_positive_price_right", ""); 
		$option_positive_price_left = get_setting_value($settings, "option_positive_price_left", ""); 
		$option_negative_price_right = get_setting_value($settings, "option_negative_price_right", ""); 
		$option_negative_price_left = get_setting_value($settings, "option_negative_price_left", "");

		// create a new PDF object
		if ($new_pdf || !isset($pdf) || !$pdf) {
			$pdf = new VA_PDF();
			$pdf->set_creator("admin_packing_pdf.php");
			$pdf->set_author("ViArt LLC");
			$pdf->set_title(PACKING_SLIP_NO_MSG . $orders_ids);
			$pdf->set_font_encoding(CHARSET);
		}
		$page_number = 0;

		// general order fields settings
		$r = new VA_Record($table_prefix . "orders");
		$r->add_where("order_id", INTEGER);
		$r->add_textbox("site_id", INTEGER);
		for ($i = 0; $i < sizeof($parameters); $i++) {
			$r->add_textbox($parameters[$i], TEXT);
			$r->add_textbox("delivery_" . $parameters[$i], TEXT);
		}
		$r->add_textbox("invoice_number", TEXT);
		$r->add_textbox("invoice_copy_number", TEXT);
		$r->add_textbox("order_status", INTEGER);
		$r->add_hidden("order_status_type", TEXT);
		$r->add_textbox("user_id", INTEGER);
		$r->add_textbox("user_type_id", INTEGER);
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
		$r->add_textbox("tax_name", TEXT);
		$r->add_textbox("tax_percent", NUMBER);
		$r->add_textbox("tax_total", NUMBER);
		$r->add_textbox("tax_prices_type", INTEGER);
		$r->add_textbox("tax_round", INTEGER);
		$r->change_property("tax_round", USE_IN_SELECT, false);
		$r->add_textbox("total_discount", NUMBER);
		$r->add_textbox("total_discount_tax", NUMBER);
		$r->add_textbox("shipping_type_desc", TEXT);
		$r->add_textbox("shipping_cost", NUMBER);
		$r->add_textbox("shipping_taxable", NUMBER);
		$r->add_textbox("credit_amount", NUMBER);
		$r->add_textbox("processing_fee", NUMBER);
		$r->add_textbox("order_total", NUMBER);

		$ids = explode(",", $orders_ids);
		for ($order_index = 0; $order_index < sizeof($ids); $order_index++)
		{
			$order_id = $ids[$order_index];
			$r->set_value("order_id", $order_id);
			$r->get_db_values();
			$order_site_id = $r->get_value("site_id");
			$order_status = $r->get_value("order_status");
			$tmp_images = array(); // array where we save all temporary images

			// check order status type
			$sql = " SELECT status_type FROM " . $table_prefix ."order_statuses WHERE status_id=" . $db->tosql($order_status, INTEGER);
			$order_status_type = get_db_value($sql);
			$r->set_value("order_status_type", $order_status_type);

			if (isset($packing_sets[$order_site_id])) {
				$packing = $packing_sets[$order_site_id];
				$order_info = $order_sets[$order_site_id];
			} else {
				// get packing settings for current order
				$packing = array();
				$sql  = " SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings";
				$sql .= " WHERE setting_type='printable'";
				if ($order_site_id) {
					$sql .= "AND (site_id=1 OR site_id=" . $db->tosql($order_site_id, INTEGER, true, false) . ")";
					$sql .= "ORDER BY site_id ASC";
				} else {
					$sql .= "AND site_id=1";
				}
				$db->query($sql);
				while ($db->next_record()) {
					$packing[$db->f("setting_name")] = $db->f("setting_value");
				}
	  
				// get order fields settings for current order
				$order_info = array();
				$sql  = " SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings";
				$sql .= " WHERE setting_type='order_info'";
				if (isset($order_site_id)) {
					$sql .= "AND (site_id=1 OR site_id=" . $db->tosql($order_site_id, INTEGER, true, false) . ")";
					$sql .= "ORDER BY site_id ASC";
				} else {
					$sql .= "AND site_id=1";
				}
				$db->query($sql);
				while ($db->next_record()) {
					$order_info[$db->f("setting_name")] = $db->f("setting_value");
				}
	  
				$packing_sets[$order_site_id] = $packing;
				$order_sets[$order_site_id] = $order_info;
			}

			// check parameters list to hide
			$personal_number = 0; $delivery_number = 0;
			for ($i = 0; $i < sizeof($parameters); $i++)
			{
				$personal_param = "show_" . $parameters[$i];
				$delivery_param = "show_delivery_" . $parameters[$i];
				if (isset($order_info[$personal_param]) && $order_info[$personal_param] == 1) {
					$personal_number++;
					$r->parameters[$parameters[$i]][SHOW] = true;
				} else {
					$r->parameters[$parameters[$i]][SHOW] = false;
				}
				if (isset($order_info[$delivery_param]) && $order_info[$delivery_param] == 1) {
					$delivery_number++;
					$r->parameters["delivery_" . $parameters[$i]][SHOW] = true;
				} else {
					$r->parameters["delivery_" . $parameters[$i]][SHOW] = false;
				}
			}


			// codes settings 
			$packing_image = get_setting_value($packing, "packing_image", 0);
			$show_item_code = get_setting_value($packing, "item_code_packing", 0);
			$show_manufacturer_code = get_setting_value($packing, "manufacturer_code_packing", 0);
			$show_item_weight = get_setting_value($packing, "item_weight_packing", 0);
			$show_actual_weight = get_setting_value($packing, "actual_weight_packing", 0);
			$show_total_weight = get_setting_value($packing, "total_weight_packing", 0);
			$show_total_actual_weight = get_setting_value($packing, "total_actual_weight_packing", 0);
			$sc_properties_packing = get_setting_value($packing, "sc_properties_packing", 0);
			$shipping_method_packing = get_setting_value($packing, "shipping_method_packing", 0);
	  
			// image settings
			if ($packing_image) {
				$site_url = get_setting_value($settings, "site_url", "");
				$product_no_image = get_setting_value($settings, "product_no_image", "");
				$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");		
				product_image_fields($packing_image, $image_type_name, $image_field, $image_alt_field, $watermark, $product_no_image);			
				$item_image_tmp_dir  = get_setting_value($settings, "tmp_dir", $root_folder_path);
				$item_image_position = 0;
				if ($packing_image == 1) {
					$max_image_width  = get_setting_value($settings, "tiny_image_max_width", 100);
					$item_image_position = 1;
				} elseif ($packing_image == 2) {
					$max_image_width  = get_setting_value($settings, "small_image_max_width", 200);
					$item_image_position = 1;
				} elseif ($packing_image == 3) {
					$max_image_width  = get_setting_value($settings, "big_image_max_width", 300);
					$item_image_position = 2;
				}			
			}

			$columns = array(
				"item_image" => array("name" => IMAGE_MSG, "active" => $packing_image, "align" => "center"), 
				"quantity" => array("name" => QTY_MSG, "active" => true, "align" => "center"), 
				"item_name" => array("name" => PROD_TITLE_COLUMN, "active" => true, "align" => "left"), 
				"item_code" => array("name" => PROD_CODE_MSG, "active" => $show_item_code, "align" => "center"), 
				"manufacturer_code" => array("name" => MANUFACTURER_CODE_MSG, "active" => $show_manufacturer_code, "align" => "center"), 
			);
			foreach ($columns as $column_name => $column_values) {
				$columns[$column_name]["width"] = 0;
				$columns[$column_name]["start"] = 0;
			}
	  
			// get order currency
			$order_currency_code = $r->get_value("currency_code");
			$order_currency_rate = $r->get_value("currency_rate");
			$currency = get_currency($order_currency_code);
	  
			$order_placed_date = $r->get_value("order_placed_date");
			$order_date = va_date($date_show_format, $order_placed_date);
			//$t->set_var("order_date", $order_date);
    
			$r->set_value("company_id", get_translation(get_db_value("SELECT company_name FROM " . $table_prefix . "companies WHERE company_id=" . $db->tosql($r->get_value("company_id"), INTEGER))));
			$r->set_value("state_id", get_translation(get_db_value("SELECT state_name FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($r->get_value("state_id"), INTEGER))));
			$r->set_value("country_id", get_translation(get_db_value("SELECT country_name FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($r->get_value("country_id"), INTEGER))));
			$r->set_value("delivery_company_id", get_translation(get_db_value("SELECT company_name FROM " . $table_prefix . "companies WHERE company_id=" . $db->tosql($r->get_value("delivery_company_id"), INTEGER))));
			$r->set_value("delivery_state_id", get_translation(get_db_value("SELECT state_name FROM " . $table_prefix . "states WHERE state_id=" . $db->tosql($r->get_value("delivery_state_id"), INTEGER))));
			$r->set_value("delivery_country_id", get_translation(get_db_value("SELECT country_name FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($r->get_value("delivery_country_id"), INTEGER))));
			$r->set_value("cc_type", get_translation(get_db_value("SELECT credit_card_name FROM " . $table_prefix . "credit_cards WHERE credit_card_id=" . $db->tosql($r->get_value("cc_type"), INTEGER))));


			// parse properties
			$orders_properties = array(); $cart_properties = array(); $personal_properties = array();
			$delivery_properties = array(); $payment_properties = array(); $shipping_properties = array();
			$properties_total = 0; $properties_taxable = 0;
			$sql  = " SELECT op.property_id, op.property_type, op.property_name, op.property_value, ";
			$sql .= " op.property_price, op.property_points_amount, op.tax_free ";
			$sql .= " FROM " . $table_prefix . "orders_properties op ";
			$sql .= " WHERE op.order_id=" . $db->tosql($order_id, INTEGER);
			$sql .= " ORDER BY op.property_order, op.property_id ";
			$db->query($sql);
			while ($db->next_record()) {
				$property_id = $db->f("property_id");
				$property_type = $db->f("property_type");
				$property_name = strip_tags(get_translation($db->f("property_name")));
				$property_value = strip_tags(get_translation($db->f("property_value")));
				$property_price = $db->f("property_price");
				$property_points_amount = $db->f("property_points_amount");
				$property_tax_free = $db->f("tax_free");
				$control_type = $db->f("control_type");
				$properties_total += $property_price;
				if ($property_tax_free != 1) {
					$properties_taxable += $property_price;
				}
    
				if (isset($orders_properties[$property_id])) {
					$orders_properties[$property_id]["value"] .= "; " . $property_value;
					$orders_properties[$property_id]["price"] += $property_price;
					$orders_properties[$property_id]["points_amount"] += $property_points_amount;
				} else {
					$orders_properties[$property_id] = array(
						"type" => $property_type, "name" => $property_name, "value" => $property_value, 
						"price" => $property_price, "points_amount" => $property_points_amount, "tax_free" => $property_tax_free,
					);
				}
	  
				// save data by arrays
				if ($property_type == 1) {
				  $cart_properties[$property_id] = $orders_properties[$property_id];
				} elseif ($property_type == 2) {
					$personal_properties[$property_id] = $orders_properties[$property_id];
				} elseif ($property_type == 3) {
					$delivery_properties[$property_id] = $orders_properties[$property_id];
				} elseif ($property_type == 4) {
					$payment_properties[$property_id] = $orders_properties[$property_id];
				} elseif ($property_type == 5 || $property_type == 6) {
					$shipping_properties[$property_id] = $orders_properties[$property_id];
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
					"item_image" => "",
					"image_ext" => "",
					"image_width" => "",
					"image_height" => "",
					"item_name" => strip_tags(get_translation($db->f("item_name"))),
					"item_code" => $db->f("item_code"),
					"manufacturer_code" => $db->f("manufacturer_code"),
					"weight" => $db->f("weight"),
					"actual_weight" => $db->f("actual_weight"),
					"properties" => array(),
				);
	  
				$items_ids[] = $item_id;
				if (strlen($order_shipping_id)) {
					if (!isset($packing_slips[$order_shipping_id])) {
						$packing_slips[$order_shipping_id] = array();
					}
					$packing_slips[$order_shipping_id][$order_item_id] = $order_item;
				}
			}

			// get images from items table to show accordingly to selected settings 
			$images = array(); $max_image_width = 0;
			if ($packing_image && $image_field) {
				$sql  = " SELECT item_id, " . $image_field; 
				$sql .= " FROM " . $table_prefix . "items";
				$sql .= " WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")";
				$db->query($sql);			
				while ($db->next_record()) {
					$image_height = 0;
					$item_id = $db->f("item_id");
					$item_image = $db->f($image_field);
					$item_image = str_replace($site_url, "", $item_image);
					$item_image = str_replace($secure_url, "", $item_image);
					if (preg_match("/^https?:/", $item_image)) { $item_image = ""; }

					foreach ($packing_slips as $order_shipping_id => $order_items) {
						foreach ($order_items as $order_item_id => $order_item) {
							if ($order_item["item_id"] == $item_id) {
								$packing_slips[$order_shipping_id][$order_item_id]["item_image"] = $item_image;
							}
						}
					}
				}
			}

			// get order items properties and add to packing items array
			$properties_values_ids = array(); $values_packs = array();
			$sql  = " SELECT item_property_id, order_item_id, property_name, hide_name, property_value, property_values_ids, length_units  FROM " . $table_prefix . "orders_items_properties ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$sql .= " ORDER BY property_order, property_id, item_property_id ";
			$db->query($sql);
			while ($db->next_record()) {
				$item_property_id = $db->f("item_property_id");
				$property_order_item_id = $db->f("order_item_id");
				$hide_name = $db->f("hide_name");
				$property_name = strip_tags(get_translation($db->f("property_name")));
				$property_value = strip_tags(get_translation($db->f("property_value")));
				$length_units = $db->f("length_units");
				
				$explode_values_ids = array();
				$property_values_ids = trim($db->f("property_values_ids"));
				if ($property_values_ids) { 
					$explode_values_ids = explode(",", $property_values_ids);
					$properties_values_ids = array_merge($properties_values_ids, $explode_values_ids); 
				}

				foreach ($packing_slips as $order_shipping_id => $order_items) {
					foreach ($order_items as $order_item_id => $order_item) {
						if ($order_item_id == $property_order_item_id) {
							$packing_slips[$order_shipping_id][$order_item_id]["properties"][$item_property_id] = array(
								"hide_name" => $hide_name, 
								"property_name" => $property_name, 
								"property_value" => $property_value, 
								"length_units" => $length_units, 
							);
							// also save add $order_shipping_id & $order_item_id to values packs for easy access
							foreach ($explode_values_ids as $property_value_id) {
								if (isset($values_packs[$property_value_id])) { $values_packs[$property_value_id] = array(); }
								$values_packs[$property_value_id][] = array("order_shipping_id" => $order_shipping_id, "order_item_id" => $order_item_id);
							}
						}
					}
				}
			}

			// check if properties has images to update original image
			if ($packing_image && count($properties_values_ids)) {
				$property_image_field = "image_".$image_type_name;
				$sql  = " SELECT item_property_id	,".$property_image_field." ";
				$sql .= " FROM ".$table_prefix."items_properties_values WHERE item_property_id IN (".$db->tosql($properties_values_ids, INTEGERS_LIST).")";
				$sql .= " ORDER BY value_order ";
				$db->query($sql);
				while ($db->next_record()) {
					$item_property_id = $db->f("item_property_id");
					$property_image = $db->f($property_image_field);
					$property_image = str_replace($site_url, "", $property_image);
					$property_image = str_replace($secure_url, "", $property_image);
					if (preg_match("/^https?:/", $property_image)) { $property_image = ""; }
					if ($property_image) {
						// update original image
						foreach ($values_packs[$item_property_id] as $value_pack) {
							$packing_slips[$value_pack["order_shipping_id"]][$value_pack["order_item_id"]]["item_image"] = $property_image;
						}
					}
				}
			}

			// check and prepare all order images for placing on PDF
			if ($packing_image) {
				foreach ($packing_slips as $order_shipping_id => $order_items) {
					foreach ($order_items as $order_item_id => $order_item) {
						$item_image = $order_item["item_image"];
						if (!strlen($item_image)) {
							$item_image = $product_no_image;
							$image_exists = false;
						} else {
							$image_exists = true;
						}
		  
						$item_image_tmp_created = false;
						if ($item_image) {
							$image_ext = strtolower(pathinfo($item_image, PATHINFO_EXTENSION));
							$packing_slips[$order_shipping_id][$order_item_id]["image_ext"] = $image_ext;
							if ($image_exists && $watermark) {
								$image_tmp = tempnam ($tmp_dir, "tmp");
								$tmp_images[] = $image_tmp; // save to array to delete later
								$item_image = $site_url . "image_show.php?item_id=".$item_id."&type=".$image_type_name."&vc=".md5($item_image);
								$image_out = fopen($image_tmp, 'wb');
								$item_image_tmp_created = true;
								if (function_exists("curl_init") && $image_out) {	
									$ch = curl_init();
									curl_setopt($ch, CURLOPT_FILE, $image_out);
									curl_setopt($ch, CURLOPT_HEADER, 0);
									curl_setopt($ch, CURLOPT_URL, $item_image);
									curl_exec($ch);
									if (curl_errno($ch)) {
										$item_image = "";
									} else {
										$item_image = $image_tmp;
									}
									curl_close($ch);
									fclose($image_out);
								} else {
									$item_image = "";
								}
							} else {
								if ($is_admin_path) {
									$item_image  = $root_folder_path . $item_image;
								}
							}
						}
						$packing_slips[$order_shipping_id][$order_item_id]["item_image"] = $item_image;
						// check image size
						$image_width = ""; $image_height = "";
						if ($item_image) {
							$image_size = @getimagesize($item_image);
							if (is_array($image_size)) {
								$image_width = $image_size[0];
								$image_height = $image_size[1];
								if ($image_width > $max_image_width) { $max_image_width = $image_width; }
							}
							$packing_slips[$order_shipping_id][$order_item_id]["image_width"] = $image_width;
							$packing_slips[$order_shipping_id][$order_item_id]["image_height"] = $image_height;
						}
					}	
				}
			}


			// check what columns to show and where
			$column_end = 40;
			// left space for image
			if ($max_image_width <= 0) { $max_image_width = 50; }
			if ($packing_image && $item_image_position == 1 && $max_image_width > 0) {
				$columns["item_image"]["start"] = $column_end;
				$columns["item_image"]["width"] = $max_image_width;
				$column_end += $max_image_width;
			}
	  
			// quantity field
			$columns["quantity"]["start"] = $column_end;
			$columns["quantity"]["width"] = 30;
			$column_end += $columns["quantity"]["width"];
	  
			// check how many columns left and calculate column width
			$columns_left = 1;
			if ($show_item_code) { $columns_left++; }
			if ($show_manufacturer_code) { $columns_left++; }
			$width_left = 515 - ($column_end - 40);
			$average_width = intval($width_left / $columns_left);
	  
			// title field
			$columns["item_name"]["start"] = $column_end;
			$columns["item_name"]["width"] = $average_width;
			$column_end += $average_width;
	  
			if ($show_item_code) { 
				$columns["item_code"]["start"] = $column_end;
				$columns["item_code"]["width"] = $average_width;
				$column_end += $average_width;
			}
			if ($show_manufacturer_code) { 
				$columns["manufacturer_code"]["start"] = $column_end;
				$columns["manufacturer_code"]["width"] = $average_width;
				$column_end += $average_width;
			}


			foreach ($packing_slips as $order_shipping_id => $order_items) {
				packing_slip_new_page($pdf, $height_position, $page_number, $packing);
				packing_slip_header($pdf, $height_position, $packing, $r);
				packing_slip_user_info($pdf, $height_position, $r, $personal_number, $delivery_number, $personal_properties, $delivery_properties, $currency);
				packing_slip_table_header($pdf, $height_position, $r, $columns);
				$packing_total_weight = 0; $packing_total_actual_weight = 0;

				foreach ($order_items as $order_item_id => $order_item) {
					if (($height_position - intval($order_item["image_height"])) < 140) {
						$pdf->end_page();
						packing_slip_new_page($pdf, $height_position, $page_number, $packing);
						packing_slip_table_header($pdf, $height_position, $r, $columns);
					}
    
    
					$item_id = $order_item["item_id"];
					//$item_image = isset($images[$item_id]) ? $images[$item_id] : "";
					$order_item_id = $order_item["order_item_id"];
					$quantity = $order_item["quantity"];
					$item_name = strip_tags(get_translation($order_item["item_name"]));
					$item_code = $order_item["item_code"];
					$manufacturer_code = $order_item["manufacturer_code"];
					$item_weight = $order_item["weight"];
					$actual_weight = $order_item["actual_weight"];
					$packing_total_weight += ($item_weight * $quantity);
					$packing_total_actual_weight += ($actual_weight * $quantity);
					$item_properties = $order_item["properties"];

					// image fields
					$item_image = $order_item["item_image"];
					$image_ext = $order_item["image_ext"];
					$image_width = $order_item["image_width"];
					$image_height = $order_item["image_height"];

					// show image
					if ($packing_image && $item_image) { 
						$pdf->place_image($item_image, $columns["item_image"]["start"], $height_position - $image_height, $image_ext);	
					}

					// set font for quantity and title
					$pdf->setfont("helvetica", "", 9);
					// set quantity and product title
					$qty_height = $pdf->show_xy($quantity, $columns["quantity"]["start"] + 4, $height_position - 2, $columns["quantity"]["width"] - 6, 0, $columns["quantity"]["align"]);
					$item_height = $pdf->show_xy($item_name, $columns["item_name"]["start"] + 4, $height_position - 2, $columns["item_name"]["width"] - 6, 0);
					// set smaller font for product additional information
					$pdf->setfont("helvetica", "", 8);

					// show item properties 
					$properties_height = 0; 	
					foreach ($item_properties as $item_property_id => $property_data) {
						$item_height += 2;
						$property_name = $property_data["property_name"];
						$property_value = $property_data["property_value"];
						$hide_name = $property_data["hide_name"];
						$length_units = $property_data["length_units"];
						$property_line = "";
						if (!$hide_name) {
							$property_line = $property_name.$option_name_delimiter;
						}
						$property_line .= $property_value;
						if (strlen($property_line)) {
							if ($length_units) {
								$property_line .= " ".strtoupper($length_units);
							}
							$property_height = $pdf->show_xy($property_line, $columns["item_name"]["start"] + 8, $height_position - $item_height - 2, $columns["item_name"]["width"] - 10, 0);
							$item_height += $property_height;
						}
					}
					// end properties block

					// show item weight
					if ($show_item_weight && $item_weight > 0) {
						$item_height += 2;
						$item_weight = round($item_weight, 4);
						$weight_height = $pdf->show_xy(WEIGHT_MSG.": " . $item_weight.$weight_measure, $columns["item_name"]["start"] + 8, $height_position - $item_height - 2, $columns["item_name"]["width"] - 10, 0);
						$item_height += $weight_height;
					}
					// show actual weight
					if ($show_actual_weight && $actual_weight > 0) {
						$item_height += 2;
						$actual_weight= round($actual_weight, 4);
						$weight_height = $pdf->show_xy(ACTUAL_WEIGHT_MSG.": " . $actual_weight.$weight_measure, $columns["item_name"]["start"] + 8, $height_position - $item_height - 2, $columns["item_name"]["width"] - 10, 0);
						$item_height += $weight_height;
					}
	  
					// add additional indent after each product
					$item_height += 6;
					$row_height = $item_height;
    
					$item_code_image_height = 0; $manufacturer_code_image_height = 0;
					$item_code_height = 0; $manufacturer_code_height = 0; 
					if (strlen($item_code)) {
						if ($show_item_code == 1) {
							$item_code_height = $pdf->show_xy($item_code, $columns["item_code"]["start"], $height_position - 2, $columns["item_code"]["width"], 0, "center");
							$item_code_height += 6;
						} elseif ($show_item_code == 2) {
							$image_type = "png";
							if ($tmp_dir) {
								$item_code_image = $tmp_dir . "tmp_" . md5(uniqid(rand(), true)) . "." . $image_type;
								save_barcode ($item_code_image, $item_code, $image_type, "code128");
								$tmp_images[] = $item_code_image;
							} else {
								$item_code_image = $settings["site_url"] . "barcode_image.php?text=" . $item_code;
							}
			        $image_size = @GetImageSize($item_code_image);
							if (is_array($image_size)) {
								$image_width = $image_size[0];
								$item_code_image_height = $image_size[1];
							} else {
								$image_width = 100;
								$item_code_image_height = 100;
							}
							$item_code_image_height += 10; // additional pixels for better positioning by center
							if ($average_width > $image_width) {
								$image_shift = round(($average_width - $image_width) / 2);
							}	else {
								$image_shift = 0;
							}
						  $pdf->place_image($item_code_image, $columns["item_code"]["start"] + $image_shift, $height_position - $item_code_image_height + 5, $image_type);
						}
					}
					if (strlen($manufacturer_code)) {
						if ($show_manufacturer_code == 1) {
							$manufacturer_code_height  = $pdf->show_xy($manufacturer_code, $columns["manufacturer_code"]["start"], $height_position - 2, $columns["manufacturer_code"]["width"], 0, "center");
							$manufacturer_code_height += 6;
						} elseif ($show_manufacturer_code == 2) {
							$image_type = "png";
							if ($tmp_dir) {
								$manufacturer_code_image = $tmp_dir . "tmp_" . md5(uniqid(rand(), true)) . "." . $image_type;
								save_barcode ($manufacturer_code_image, $manufacturer_code, $image_type, "code128");
								$tmp_images[] = $manufacturer_code_image;
							} else {
								$manufacturer_code_image = $settings["site_url"] . "barcode_image.php?text=" . $manufacturer_code;
							}
			        $image_size = @GetImageSize($manufacturer_code_image);
							if (is_array($image_size)) {
								$image_width = $image_size[0];
								$manufacturer_code_image_height = $image_size[1];
							} else {
								$image_width = 100;
								$manufacturer_code_image_height = 100;
							}
							$manufacturer_code_image_height += 10; // additional pixels for better positioning
							if ($average_width > $image_width) {
								$image_shift = round(($average_width - $image_width) / 2);
							}	else {
								$image_shift = 0;
							}
						  $pdf->place_image($manufacturer_code_image, $columns["manufacturer_code"]["start"]+ $image_shift, $height_position - $manufacturer_code_image_height + 5, $image_type);
						}
					}
	  
	  
					if ($image_height > $row_height)  {
						$row_height = $image_height;
					}
					if ($item_code_image_height > $row_height) {
						$row_height = $item_code_image_height;
					}
					if ($manufacturer_code_image_height > $row_height) {
						$row_height = $manufacturer_code_image_height;
					}
					if ($item_code_height > $row_height) {
						$row_height = $item_code_height;
					}
					if ($manufacturer_code_height > $row_height) {
						$row_height = $manufacturer_code_height;
					}
	  
					$height_position -= $row_height;
	  
					// show table row  
					$pdf->setlinewidth(1.0);
					$pdf->rect (40, $height_position, 515, $row_height);
					foreach ($columns as $column_name => $values) {
						if ($values["active"]) {
							$pdf->line( $values["start"], $height_position, $values["start"], $height_position + $row_height);
						}
					}
					// end table row  
				}
	  
				// show shopping cart properties
				if ($sc_properties_packing) {
					$height_position -= 2;
					foreach ($cart_properties as $property_id => $property_values) {
						$property_price = $property_values["price"];
						$property_tax_id = 0;
						$property_tax = get_tax_amount("", 0, $property_price, 1, $property_tax_id, $property_values["tax_free"], $property_tax_percent);
						$property_height = $pdf->show_xy($property_values["name"] . ": " . $property_values["value"], 40, $height_position - 2, 500, 0, "left");
						$height_position -= ($property_height + 2);
					}
				}
	  
				// show shipping methods
				if ($shipping_method_packing && isset($orders_shipments[$order_shipping_id])) {
					$shipping_data = $orders_shipments[$order_shipping_id];
					$height_position -= 2;
					$shipping_height = $pdf->show_xy(PROD_SHIPPING_MSG . ": " . $shipping_data["desc"], 40, $height_position - 2, 500, 0, "left");
					$height_position -= ($shipping_height + 2);
					$packing_total_weight += $shipping_data["tare_weight"];
					$packing_total_actual_weight += $shipping_data["tare_weight"];

					foreach ($shipping_properties as $property_id => $property_values) {
						$property_name = strip_tags($property_values["name"]);
						$property_value = strip_tags($property_values["value"]);
	      
						$property_height = $pdf->show_xy($property_name. ": " . $property_value, 40, $height_position - 2, 500, 0, "left");
						$height_position -= ($property_height + 2);
					}
				}

				// show total weight of order
				if ($show_total_weight && $packing_total_weight > 0) {
					$packing_total_weight = round($packing_total_weight, 4);
					$height_position -= 2;
					$weight_height = $pdf->show_xy(WEIGHT_TOTAL_MSG. ": " . $packing_total_weight.$weight_measure, 40, $height_position - 2, 500, 0, "left");
					$height_position -= ($weight_height + 2);
				}
				// show actual total weight of order
				if ($show_total_actual_weight && $packing_total_actual_weight > 0) {
					$packing_total_actual_weight = round($packing_total_actual_weight, 4);
					$height_position -= 2;
					$weight_height = $pdf->show_xy(WEIGHT_TOTAL_MSG. " (".ACTUAL_WEIGHT_MSG."): " . $packing_total_actual_weight.$weight_measure, 40, $height_position - 2, 500, 0, "left");
					$height_position -= ($weight_height + 2);
				}
	  
				packing_slip_footer($pdf, $height_position, $packing, $r);
				$pdf->end_page();
			}

			// clearing temporary images
			for ($t = 0; $t < sizeof($tmp_images); $t++) {
				unlink($tmp_images[$t]);
			}

		}

		$pdf_buffer = "";
		if ($return_pdf) {
			$pdf_buffer = $pdf->get_buffer();
		}
		return $pdf_buffer;
	}


	function packing_slip_new_page(&$pdf, &$height_position, &$page_number, $packing)
	{
		$page_number++;
		$pdf_library = isset($packing["pdf_page_type"]) ? $packing["pdf_page_type"] : "A4";
		if($pdf_library == "LETTER"){
			$pdf->begin_page(612, 792);
		$height_position = 750;
		}else{
			$pdf->begin_page(595, 842);
			$height_position = 800;
		}
	
		$pdf->setfont ("helvetica", "", 8);
		if ($page_number > 1) {
			//$pdf->show_xy("- " . $page_number . " -", 40, 20, 555, 0, "center");
		}
	}
	
	function packing_slip_header(&$pdf, &$height_position, $packing, $r)
	{
		global $db, $table_prefix, $date_show_format, $settings;
	
		$tmp_dir  = get_setting_value($settings, "tmp_dir", ".");

		$order_id = $r->get_value("order_id");
		$invoice_number = $r->get_value("invoice_number");
		if (!$invoice_number) { $invoice_number = $order_id; }
		$order_placed_date = $r->get_value("order_placed_date");
		$order_date = va_date($date_show_format, $order_placed_date);
		$order_status_type = $r->get_value("order_status_type");

		$logo_height = 0;
		$start_position = $height_position;
		$packing_logo = get_setting_value($packing, "packing_logo");
		$packing_logo_dpi = get_setting_value($packing, "packing_logo_dpi", 72);
		if ($packing_logo_dpi <= 0) { $packing_logo_dpi = 72; }
		if ($packing_logo) {
			$image_path = $packing_logo;
			if (!file_exists($image_path)) {
				if (preg_match("/^\.\.\//", $image_path)) {
					if (@file_exists(preg_replace("/^\.\.\//", "", $image_path))) {
						$image_path = preg_replace("/^\.\.\//", "", $image_path);
					}
				} else if (@file_exists("../".$image_path)) {
					$image_path = "../" . $image_path;
				}
			}
			$image_size = @GetImageSize($image_path);
			$logo_width = $image_size[0];
			$logo_height = $image_size[1];
			// convert image size accoridngly to selected DPI value
			$logo_width = $logo_width * (72 / $packing_logo_dpi);
			$logo_height = $logo_height * (72 / $packing_logo_dpi);

			if ($logo_width > 0 && $logo_height > 0) {
				if (preg_match("/((\.jpeg)|(\.jpg))$/i", $image_path)) {
					$image_type = "jpeg";
				} elseif (preg_match("/(\.gif)$/i", $image_path)) {
					$image_type = "gif";
				} elseif (preg_match("/((\.tif)|(\.tiff))$/i", $image_path)) {
					$image_type = "tiff";
				} elseif (preg_match("/(\.png)$/i", $image_path)) {
					$image_type = "png";
				}
			  $pdf->place_image($image_path, 555 - $logo_width, $height_position - $logo_height, $image_type, "", $logo_width, $logo_height);
			}
		}
	
		if (isset($packing["packing_header"])) {
			$packing_header = strip_tags($packing["packing_header"]);
			if (strlen($packing_header)) {
				$pdf->setfont("helvetica", "", 10);
				$header_lines = explode("\n", $packing_header);
				for ($i = 0; $i < sizeof($header_lines); $i++) {
					$header_line = $header_lines[$i];
					$line_height = $pdf->show_xy($header_line, 40, $height_position, 200, 0, "left");
					$height_position -= ($line_height + 2);
				}
			}
		}
	
		if ($order_status_type == "CREDIT_NOTE") {
			$date_width = $pdf->stringwidth(va_constant("CREDIT_DATE_MSG").":");
			$number_width = $pdf->stringwidth(va_constant("CREDIT_NUMBER_MSG").":");
		} else {
			$date_width = $pdf->stringwidth(va_constant("INVOICE_DATE_MSG").":");
			$number_width = $pdf->stringwidth(va_constant("INVOICE_NUMBER_MSG").":");
		}
		$date_number_width = ($number_width > $date_width) ? $number_width : $date_width;
		$date_number_x = 60 + $date_number_width;

		$height_position -= 12;
		$pdf->setfont("helvetica", "B", 10);
		if ($order_status_type == "CREDIT_NOTE") {
			$pdf->show_xy(va_constant("CREDIT_DATE_MSG"). ":", 40, $height_position, 90, 0, "left");
		} else {
			$pdf->show_xy(va_constant("INVOICE_DATE_MSG"). ":", 40, $height_position, 90, 0, "left");
		}
		$pdf->setfont("helvetica", "", 10);
		$pdf->show_xy($order_date, $date_number_x, $height_position, 200, 0, "left");
	
		$height_position -= 16;
		$pdf->setfont("helvetica", "B", 10);
		if ($order_status_type == "CREDIT_NOTE") {
			$pdf->show_xy(va_constant("CREDIT_NUMBER_MSG"). ":", 40, $height_position, 90, 0, "left");
		} else {
			$pdf->show_xy(va_constant("INVOICE_NUMBER_MSG"). ":", 40, $height_position, 90, 0, "left");
		}
		$packing_number_show = get_setting_value($packing, "packing_number_show");
		if ($packing_number_show == 2) {
			$image_type = "png";
			$item_code_image = $tmp_dir . "tmp_" . md5(uniqid(rand(), true)) . "." . $image_type;
			save_barcode ($item_code_image, $invoice_number, $image_type, "code128");
			$image_size = @GetImageSize($item_code_image);
			$image_width = $image_size[0];
			$image_height = $image_size[1];
			$height_position -= $image_height;

		  $pdf->place_image($item_code_image, $date_number_x - 10, $height_position, $image_type, "", $image_width, $image_height);
			unlink($item_code_image);
		} else {
			$pdf->setfont("helvetica", "", 10);
			$pdf->show_xy($invoice_number, $date_number_x, $height_position, 200, 0, "left");
		}
	
		if ($height_position > ($start_position - $logo_height)) {
			$height_position = $start_position - $logo_height;
		}
	}
	
	
	function packing_slip_table_header(&$pdf, &$height_position, $r, $columns)
	{
		$pdf->setlinewidth(1.0);
	
		$pdf->setfont("helvetica", "B", 8);
		$height_position -= 12;
	
		$max_height = 12;
		foreach ($columns as $column_name => $values) {
			if ($values["active"]) {
				$column_height = $pdf->show_xy($values["name"], $values["start"] + 1, $height_position - 2, $values["width"] - 2, 0, "center");
				if ($column_height > $max_height) {
					$max_height = $column_height;
				}
			}
		}
		$max_height += 6;
		$pdf->rect ( 40, $height_position - $max_height, 515, $max_height);
		foreach ($columns as $column_name => $values) {
			if ($values["active"]) {
				$pdf->line( $values["start"], $height_position - $max_height, $values["start"], $height_position);
			}
		}
		$height_position -= $max_height;
	}


	function packing_slip_user_info(&$pdf, &$height_position, $r, $personal_number, $delivery_number, $personal_properties, $delivery_properties, $currency)
	{
		$pdf->setfont ("helvetica", "BU", 10);
		$height_position -= 24;
		$invoice_height = $pdf->show_xy(INVOICE_TO_MSG.":", 40, $height_position, 250, 0, "left");
		$delivery_height = 0;
		if ($delivery_number > 0) {
			$delivery_height = $pdf->show_xy(DELIVERY_TO_MSG.":", 300, $height_position, 250, 0, "left");
		}
		$max_height = max($invoice_height, $delivery_height);

		$height_position -= ($max_height + 2); // initial position for first row with data
		$personal_height = $height_position;
		$pdf->setfont("helvetica", "", 10);
	
		$name = "";
		if ($r->parameters["name"][SHOW]) {
			$name = $r->get_value("name");
		}
		if ($r->parameters["first_name"][SHOW] && $r->get_value("first_name")) {
			if ($name) { $name .= " "; }
			$name .= $r->get_value("first_name");
		}
		if ($r->parameters["last_name"][SHOW] && $r->get_value("last_name")) {
			if ($name) { $name .= " "; }
			$name .= $r->get_value("last_name");
		}
	
		if (strlen($name)) {
			$row_height = $pdf->show_xy($name, 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
	
		if ($r->parameters["company_id"][SHOW] && $r->get_value("company_id")) {
			$row_height = $pdf->show_xy($r->get_value("company_id"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
		if ($r->parameters["company_name"][SHOW] && $r->get_value("company_name")) {
			$row_height = $pdf->show_xy($r->get_value("company_name"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
	
		if ($r->parameters["address1"][SHOW] && $r->get_value("address1")) {
			$row_height = $pdf->show_xy($r->get_value("address1"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
		if ($r->parameters["address2"][SHOW] && $r->get_value("address2")) {
			$row_height = $pdf->show_xy($r->get_value("address2"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
		$city = ""; $address_line = "";
		if ($r->parameters["city"][SHOW]) {
			$city = $r->get_value("city");
		}
		if ($r->parameters["province"][SHOW]) {
			$address_line = $r->get_value("province");
		}
		if ($r->parameters["state_id"][SHOW] && $r->get_value("state_id")) {
			if ($address_line) { $address_line .= " "; }
			$address_line .= $r->get_value("state_id");
		}
		if ($r->parameters["zip"][SHOW] && $r->get_value("zip")) {
			if ($address_line) { $address_line .= " "; }
			$address_line .= $r->get_value("zip");
		}
		if (strlen($city) && strlen($address_line)) {
			$address_line = $city . ", " . $address_line;
		} elseif (strlen($city)) {
			$address_line = $city;
		}
	
		if (strlen($address_line)) {
			$row_height = $pdf->show_xy($address_line, 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
	
		if ($r->parameters["country_id"][SHOW] && $r->get_value("country_id")) {
			$row_height = $pdf->show_xy($r->get_value("country_id"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
	
		if ($r->parameters["phone"][SHOW] && !$r->is_empty("phone")) {
			$row_height = $pdf->show_xy(PHONE_FIELD.": ".$r->get_value("phone"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
		if ($r->parameters["daytime_phone"][SHOW] && !$r->is_empty("daytime_phone")) {
			$row_height = $pdf->show_xy(DAYTIME_PHONE_FIELD.": ".$r->get_value("daytime_phone"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
		if ($r->parameters["evening_phone"][SHOW] && !$r->is_empty("evening_phone")) {
			$row_height = $pdf->show_xy(EVENING_PHONE_FIELD.": ".$r->get_value("evening_phone"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
		if ($r->parameters["cell_phone"][SHOW] && !$r->is_empty("cell_phone")) {
			$row_height = $pdf->show_xy(CELL_PHONE_FIELD.": ".$r->get_value("cell_phone"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
		if ($r->parameters["fax"][SHOW] && !$r->is_empty("fax")) {
			$row_height = $pdf->show_xy(FAX_FIELD.": ".$r->get_value("fax"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
	
		if ($r->parameters["email"][SHOW] && strlen($r->get_value("email"))) {
			$row_height = $pdf->show_xy(EMAIL_FIELD.": " . $r->get_value("email"), 40, $personal_height, 250, 0, "left");
			$personal_height -= ($row_height + 2);
		}
	
		foreach ($personal_properties as $property_id => $property_values) {
			$property_price = $property_values["price"];
			$property_tax_id = 0;
			$property_tax = get_tax_amount("", 0, $property_price, 1, $property_tax_id, $property_values["tax_free"], $property_tax_percent);
			$property_height = $pdf->show_xy($property_values["name"] . ": " . $property_values["value"], 40, $personal_height, 250, 0, "left");
			$personal_height -= ($property_height + 2);
		}
	
		$delivery_height = $height_position;
	
		$delivery_name = "";
		if ($r->parameters["delivery_name"][SHOW]) {
			$delivery_name = $r->get_value("delivery_name");
		}
		if ($r->parameters["delivery_first_name"][SHOW] && $r->get_value("delivery_first_name")) {
			if ($delivery_name) { $delivery_name .= " "; }
			$delivery_name .= $r->get_value("delivery_first_name");
		}
		if ($r->parameters["delivery_last_name"][SHOW] && $r->get_value("delivery_last_name")) {
			if ($delivery_name) { $delivery_name .= " "; }
			$delivery_name .= $r->get_value("delivery_last_name");
		}
	
		if (strlen($delivery_name)) {
			$row_height = $pdf->show_xy($delivery_name, 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
	
		if ($r->parameters["delivery_company_id"][SHOW] && $r->get_value("delivery_company_id")) {
			$row_height = $pdf->show_xy($r->get_value("delivery_company_id"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
		if ($r->parameters["delivery_company_name"][SHOW] && $r->get_value("delivery_company_name")) {
			$row_height = $pdf->show_xy($r->get_value("delivery_company_name"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
	
		if ($r->parameters["delivery_address1"][SHOW] && $r->get_value("delivery_address1")) {
			$row_height = $pdf->show_xy($r->get_value("delivery_address1"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
		if ($r->parameters["delivery_address2"][SHOW] && $r->get_value("delivery_address2")) {
			$row_height = $pdf->show_xy($r->get_value("delivery_address2"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
		$delivery_city = ""; $delivery_address = "";
		if ($r->parameters["delivery_city"][SHOW]) {
			$delivery_city = $r->get_value("delivery_city");
		}
		if ($r->parameters["delivery_province"][SHOW]) {
			$delivery_address = $r->get_value("delivery_province");
		}
		if ($r->parameters["delivery_state_id"][SHOW] && $r->get_value("delivery_state_id")) {
			if ($delivery_address) { $delivery_address .= " "; }
			$delivery_address .= $r->get_value("delivery_state_id");
		}
		if ($r->parameters["delivery_zip"][SHOW] && $r->get_value("delivery_zip")) {
			if ($delivery_address) { $delivery_address .= " "; }
			$delivery_address .= $r->get_value("delivery_zip");
		}
		if (strlen($delivery_city) && strlen($delivery_address)) {
			$delivery_address = $delivery_city . ", " . $delivery_address;
		} elseif (strlen($delivery_city)) {
			$delivery_address = $delivery_city;
		}
	
		if (strlen($delivery_address)) {
			$row_height = $pdf->show_xy($delivery_address, 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
	
		if ($r->parameters["delivery_country_id"][SHOW] && $r->get_value("delivery_country_id")) {
			$row_height = $pdf->show_xy($r->get_value("delivery_country_id"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
	
		if ($r->parameters["delivery_phone"][SHOW] && !$r->is_empty("delivery_phone")) {
			$row_height = $pdf->show_xy(PHONE_FIELD.": ".$r->get_value("delivery_phone"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
		if ($r->parameters["delivery_daytime_phone"][SHOW] && !$r->is_empty("delivery_daytime_phone")) {
			$row_height = $pdf->show_xy(DAYTIME_PHONE_FIELD.": ".$r->get_value("delivery_daytime_phone"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
		if ($r->parameters["delivery_evening_phone"][SHOW] && !$r->is_empty("delivery_evening_phone")) {
			$row_height = $pdf->show_xy(EVENING_PHONE_FIELD.": ".$r->get_value("delivery_evening_phone"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
		if ($r->parameters["delivery_cell_phone"][SHOW] && !$r->is_empty("delivery_cell_phone")) {
			$row_height = $pdf->show_xy(CELL_PHONE_FIELD.": ".$r->get_value("delivery_cell_phone"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
		if ($r->parameters["delivery_fax"][SHOW] && !$r->is_empty("delivery_fax")) {
			$row_height = $pdf->show_xy(FAX_FIELD.": ".$r->get_value("delivery_fax"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
	
		if ($r->parameters["delivery_email"][SHOW] && strlen($r->get_value("delivery_email"))) {
			$row_height = $pdf->show_xy(EMAIL_FIELD.": " . $r->get_value("delivery_email"), 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($row_height + 2);
		}
	
		foreach ($delivery_properties as $property_id => $property_values) {
			$property_price = $property_values["price"];
			$property_tax_id = 0;
			$property_tax = get_tax_amount("", 0, $property_price, 1, $property_tax_id, $property_values["tax_free"], $property_tax_percent);
			$property_height = $pdf->show_xy($property_values["name"].": " . $property_values["value"], 300, $delivery_height, 250, 0, "left");
			$delivery_height -= ($property_height + 2);
		}
	
		if ($personal_height > $delivery_height) {
			$height_position = $delivery_height;
		} else {
			$height_position = $personal_height;
		}
		$height_position -= 12;
	
	}

	function packing_slip_footer(&$pdf, &$height_position, $packing, $r)
	{
		global $db, $table_prefix;
	
		$order_id = $r->get_value("order_id");
		$pdf->setfont("helvetica", "", 8);

		if (isset($packing["sw_orders_coupons_ps"]) && $packing["sw_orders_coupons_ps"]) {
			$sql  = " SELECT oc.coupon_title, oc.coupon_code ";
			$sql .= " FROM ".$table_prefix."orders o LEFT JOIN ".$table_prefix."orders_coupons oc ON o.order_id = oc.order_id ";
			$sql .= " WHERE o.order_id = ".$db->tosql($order_id,INTEGER);
			$sql .= " AND oc.order_item_id=0 ";
			$db->query($sql);
			while ($db->next_record()) {
				$coupon_title = strip_tags(get_translation($db->f("coupon_title")));
				$coupon_code = strip_tags($db->f("coupon_code"));
				if(strlen($coupon_title)){
					$coupon_height = $pdf->show_xy($coupon_title." (".COUPON_MSG.": ".$coupon_code.")", 43, $height_position - 15, 300, 0, "left");
					$height_position -= $coupon_height + 2;
				}
			}
		}

		if (isset($packing["packing_footer"])) {
			$packing_footer = strip_tags($packing["packing_footer"]);
			if (strlen($packing_footer)) {
				$footer_lines = explode("\n", $packing_footer);
				$height_position = 40 + sizeof($footer_lines) * 10;
				for ($i = 0; $i < sizeof($footer_lines); $i++) {
					$height_position -= 10;
					$footer_line = $footer_lines[$i];
					$pdf->show_xy($footer_line, 40, $height_position, 555, 0, "center");
				}
			}
		}
	}
	

?>