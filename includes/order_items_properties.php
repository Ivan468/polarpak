<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  order_items_properties.php                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	function order_items_properties($cart_id, $item, $parent_cart_id, $is_bundle, $discount_applicable = true, $properties_discount = 0, $parent_properties_info = array())
	{
	 	global $t, $db, $table_prefix, $site_id;
		global $settings, $tax_rates, $default_tax_rates, $currency;
		global $shopping_cart; // shopping cart variables
		global $options_code, $options_manufacturer_code; 
		global $downloads, $properties_ids; 
		global $sc_errors; // errors about required properties
		global $properties_info; // array where all the option data will be saved
		global $properties_values, $properties_values_text, $properties_values_html; // text variables for showing option data
		global $additional_price, $additional_real_price, $options_buying_price, $additional_weight, $additional_actual_weight; // variables for adding to product totals

		$item_id = $item["item_id"];
		$item_type_id = $item["item_type_id"];
		$item_price = $item["price"];
		$item_tax_id = $item["tax_id"];
		$item_tax_free = $item["tax_free"];
		$item_name = $item["item_name"];
		$item_code = $item["item_code"];
		$manufacturer_code = $item["manufacturer_code"];
		$options_downloads = array();
		$downloads = isset($item["parent_downloads"]) ? $item["parent_downloads"] : array();
		$properties_prices = array(); // save here all properties prices in case we would like to calculate percentage values from other percentage values

		$eol = get_eol();
		$operation = get_param("operation");
		$is_update = strlen($operation);

		$tax_prices = get_setting_value($settings, "tax_prices", 0);
		$tax_prices_type = get_setting_value($settings, "tax_prices_type", 0);
		$price_type = get_session("session_price_type");
		if ($price_type == 1) {
			$additional_price_field = "trade_additional_price";
		} else {
			$additional_price_field = "additional_price";
		}
		$user_discount_type = get_session("session_discount_type");
		$user_discount_amount = get_session("session_discount_amount");

		// option delimiter and price options
		$option_name_delimiter = get_setting_value($settings, "option_name_delimiter", ": "); 
		$option_positive_price_right = get_setting_value($settings, "option_positive_price_right", ""); 
		$option_positive_price_left = get_setting_value($settings, "option_positive_price_left", ""); 
		$option_negative_price_right = get_setting_value($settings, "option_negative_price_right", ""); 
		$option_negative_price_left = get_setting_value($settings, "option_negative_price_left", "");

		if (is_array($parent_properties_info) && sizeof($parent_properties_info) > 0) {
			for ($p = 0; $p < sizeof($parent_properties_info); $p++) {
				list ($property_id, $control_type, $property_name_initial, $hide_name, $values_list, $pr_add_price, $pr_add_weight, $pr_actual_weight, $pr_values, $property_order, $length_units) = $parent_properties_info[$p];
				$properties_info[] = array ($property_id, $control_type, $property_name_initial, $hide_name, $values_list, 0, $pr_add_weight, $pr_actual_weight, $pr_values, $property_order, $length_units);
				$property_name = get_translation($property_name_initial);
				$properties_values .= "<br/>" . $property_name . ": " . $values_list; 
			}
		}
		$pr_rows = array(); 
		$sql  = " SELECT ip.* ";
		$sql .= " FROM (" . $table_prefix . "items_properties ip ";
		$sql .= " LEFT JOIN " . $table_prefix . "items_properties_sites ips ON ip.property_id=ips.property_id) ";
		$sql .= " WHERE (ip.item_id=" . $db->tosql($item_id, INTEGER) . " OR ip.item_type_id=" . $db->tosql($item_type_id, INTEGER) . ") ";
		if (isset($site_id)) {
			$sql .= " AND (ip.sites_all=1 OR ips.site_id=" . $db->tosql($site_id, INTEGER) . ")";
		} else {
			$sql .= " AND ip.sites_all=1 ";
		}
		$sql .= " AND ip.property_type_id=1 ";
		$sql .= " ORDER BY ip.property_order, ip.property_id ";
		$db->query($sql);
		if ($db->next_record()) {
			do {
				$property_id = $db->f("property_id");
				$option = array(
					"property_id" => $db->f("property_id"),
					"property_type_id" => $db->f("property_type_id"),
					"property_order" => $db->f("property_order"),
					"usage_type" => $db->f("usage_type"),
					"property_name" => $db->f("property_name"),
					"hide_name" => $db->f("hide_name"),
					"parent_property_id" => $db->f("parent_property_id"),
					"parent_value_id" => $db->f("parent_value_id"),
					"property_description" => $db->f("property_description"),
					"property_style" => $db->f("property_style"),
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
					"required" => $db->f("required"),
					"start_html" => $db->f("start_html"),
					"middle_html" => $db->f("middle_html"),
					"before_control_html" => $db->f("before_control_html"),
					"after_control_html" => $db->f("after_control_html"),
					"end_html" => $db->f("end_html"),
					"onchange_code" => $db->f("onchange_code"),
					"onclick_code" => $db->f("onclick_code"),
					"control_code" => $db->f("control_code"),
					"length_units" => $db->f("length_units"),
				);
        $pr_rows[$property_id] = $option;
			} while ($db->next_record());
		}

		foreach ($pr_rows as $property_id => $option) {
			if ($option["usage_type"] == 2 || $option["usage_type"] == 3) {
				$sql  = " SELECT item_id FROM " . $table_prefix . "items_properties_assigned ";
				$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
				$sql .= " AND property_id=" . $db->tosql($property_id, INTEGER);
				$db->query($sql);
				if (!$db->next_record()) {
					// remove option if it wasn't assigned to product
					unset($pr_rows[$property_id]);
				}
			}
		}

		if (sizeof($pr_rows) > 0) {
			foreach ($pr_rows as $property_id => $option) 
			{
				$property_id = $option["property_id"];
				$property_type_id = $option["property_type_id"];
				$usage_type = $option["usage_type"];
				$property_order = $option["property_order"];
				$property_name_initial = $option["property_name"];
				$property_name = get_translation($property_name_initial);
				$hide_name = $option["hide_name"];
				$property_description = $option["property_description"];
				$parent_property_id = $option["parent_property_id"];
				$parent_value_id = $option["parent_value_id"];
				$property_price_type = $option["property_price_type"];
				$property_price = doubleval($option["property_price"]);
				$free_price_type = $option["free_price_type"];
				$free_price_amount = $option["free_price_amount"];
				$percentage_price_type = $option["percentage_price_type"];
				$percentage_property_id = $option["percentage_property_id"];
				$max_limit_type = $option["max_limit_type"];
				$max_limit_length = $option["max_limit_length"];
				$control_type = $option["control_type"];
				$control_style = $option["control_style"];
				$property_required = $option["required"];
				$start_html = $option["start_html"];
				$middle_html = $option["middle_html"];
				$before_control_html = $option["before_control_html"];
				$after_control_html = $option["after_control_html"];
				$end_html = $option["end_html"];
				$onchange_code = $option["onchange_code"];
				$onclick_code = $option["onclick_code"];
				$control_code = $option["control_code"];
				$length_units = $option["length_units"];

				$properties = "";
				if (strlen($parent_cart_id)) {
					if (isset($shopping_cart[$parent_cart_id]["COMPONENTS_PROPERTIES"][$cart_id])) {
						$properties = $shopping_cart[$parent_cart_id]["COMPONENTS_PROPERTIES"][$cart_id];
					}
				} else {
					$properties = $shopping_cart[$cart_id]["PROPERTIES"];
				}

				$property_value_param = ""; $property_value_params = array(); $property_value_texts = array();
				$property_value = ""; $pr_add_weight = 0; $pr_actual_weight = 0; $pr_add_price = 0; $pr_add_real_price = 0; 
				$pr_buy_price = 0; $pr_values = array(); 

				if (is_array($properties) && isset($properties[$property_id])) {
					// options added previously when adding product
					$property_values = $properties[$property_id];
					$values_list = ""; $values_list_translation = ""; 
					if(strtoupper($control_type) == "LISTBOX" || strtoupper($control_type) == "RADIOBUTTON" || strtoupper($control_type) == "IMAGE_SELECT" 
						|| strtoupper($control_type) == "CHECKBOXLIST" || strtoupper($control_type) == "TEXTBOXLIST") {
						for ($pv = 0; $pv < sizeof($property_values); $pv++) {
							$sql  = " SELECT item_code, manufacturer_code, property_value, ".$additional_price_field.", percentage_price, buying_price, ";
							$sql .= " additional_weight, actual_weight, use_stock_level, hide_out_of_stock, stock_level, download_files_ids ";
							$sql .= " FROM " . $table_prefix . "items_properties_values ipv ";
							$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
							$sql .= " AND item_property_id=" . $db->tosql($property_values[$pv], INTEGER);
							$db->query($sql);
							if ($db->next_record()) {
								$pr_item_code = $db->f("item_code");
								$pr_manufacturer_code = $db->f("manufacturer_code");
								$option_price = doubleval($db->f($additional_price_field));
								$percentage_price = doubleval($db->f("percentage_price"));
								if (($percentage_price_type == 1 || $percentage_price_type == 3) && $percentage_price && $item_price) {
									$option_price += round(($item_price * $percentage_price) / 100, 2);
								} 
								if (($percentage_price_type == 2 || $percentage_price_type == 3) && $percentage_property_id) {
									$parent_price = 0;
									if (false && isset($properties_prices[$percentage_property_id])) {
										// in case we would like to calculate percentage from other percentage values we will need to remove false condition above
										$parent_price = $properties_prices[$percentage_property_id];
									} else if (isset($shopping_cart[$cart_id]["PROPERTIES_INFO"][$percentage_property_id])) {
										$parent_price = $shopping_cart[$cart_id]["PROPERTIES_INFO"][$percentage_property_id]["CONTROL_PRICE"] + $shopping_cart[$cart_id]["PROPERTIES_INFO"][$percentage_property_id]["PRICE"];
									}
									$option_price += round(($parent_price * $percentage_price) / 100, 2);
								}
								$opt_buy_price = doubleval($db->f("buying_price"));
								if ($properties_discount > 0) {
									$option_price -= round(($option_price * $properties_discount) / 100, 2);
								}
								$option_real_price = $option_price;
								if ($discount_applicable && $user_discount_type == 1) {
									$option_price -= round(($option_price * $user_discount_amount) / 100, 2);
								} else if ($discount_applicable && $user_discount_type == 4) {
									$option_price -= round((($option_price - $opt_buy_price) * $user_discount_amount) / 100, 2);
								}
								$pr_add_price += $option_price;
								$pr_add_real_price += $option_real_price;
								$pr_buy_price += $opt_buy_price;
								$pr_add_weight += doubleval($db->f("additional_weight"));
								$pr_actual_weight += doubleval($db->f("actual_weight"));
								if (strtoupper($control_type) == "TEXTBOXLIST") {
									$value_text = $shopping_cart[$cart_id]["PROPERTIES_INFO"][$property_id]["TEXT"][$property_values[$pv]];
									$values_list .= "<br/>"; $values_list_translation .= "<br/>";
									$values_list .= $db->f("property_value") . ": ";
									$values_list .= htmlspecialchars($value_text);
									$values_list_translation .= get_translation($db->f("property_value"));
									$values_list_translation .= htmlspecialchars($value_text);
								} else {
									$value_text = "";
									if ($values_list) { $values_list .= ", "; $values_list_translation .= ", "; }
									$values_list .= $db->f("property_value");
									$values_list_translation .= get_translation($db->f("property_value"));
								}

								$options_code .= $pr_item_code;
								$options_manufacturer_code .= $pr_manufacturer_code;
								$pr_values[] = array($property_values[$pv], $db->f("property_value"), $value_text, $db->f("use_stock_level"), $db->f("hide_out_of_stock"), $db->f("stock_level"));
								$download_files_ids = $db->f("download_files_ids");
								if ($download_files_ids) { $options_downloads[] = $download_files_ids; }
							} else {
								if (strlen($parent_cart_id)) {
									// delete property for subcomponent
									$shopping_cart[$parent_cart_id]["COMPONENTS_PROPERTIES"][$cart_id][$property_id] = "";
									unset($shopping_cart[$parent_cart_id]["COMPONENTS_PROPERTIES"][$cart_id][$property_id]);
								} else {
									// delete property for product
									$shopping_cart[$cart_id]["PROPERTIES"][$property_id] = "";
									unset($shopping_cart[$cart_id]["PROPERTIES"][$property_id]);
								}
							}
						}

					} elseif(strtoupper($control_type) == "WIDTH_HEIGHT") {
						$property_price_type = 1;
						$property_width = $property_values["width"];
						$property_height = $property_values["height"];
						property_sizes($property_id, $property_width, $property_height, $size_price, $min_width, $max_width, $min_height, $max_height, $prices);
						$property_price += doubleval($size_price);
						$pr_values["width"] = $property_values["width"];
						$pr_values["height"] = $property_values["height"];
					} else {
						$values_list = htmlspecialchars($property_values[0]);
						$values_list_translation = htmlspecialchars(get_translation($property_values[0]));
					}
					// calculate control price
					if (strlen($parent_cart_id)) {
						$control_price = calculate_control_price($shopping_cart[$parent_cart_id]["COMPONENTS_PROPERTIES"][$cart_id][$property_id], $shopping_cart[$parent_cart_id]["COMPONENTS_PROPERTIES_TEXT"][$cart_id][$property_id], $property_price_type, $property_price, $free_price_type, $free_price_amount);
						$pr_add_price += $control_price;
						$pr_add_real_price += $control_price;
					} else {
						$control_price = calculate_control_price($shopping_cart[$cart_id]["PROPERTIES_INFO"][$property_id]["VALUES"], $shopping_cart[$cart_id]["PROPERTIES_INFO"][$property_id]["TEXT"], $property_price_type, $property_price, $free_price_type, $free_price_amount);
						$pr_add_price += $control_price;
						$pr_add_real_price += $control_price;
					}

					$additional_price += $pr_add_price;
					$additional_real_price += $pr_add_real_price;
					$options_buying_price += $pr_buy_price;
					$additional_weight += $pr_add_weight;
					$additional_actual_weight += $pr_actual_weight;

					$pr_add_tax = get_tax_amount($tax_rates, $item_type_id, $pr_add_price, 1, $item_tax_id, $item_tax_free, $item_tax_percent, $default_tax_rates);
					if ($tax_prices_type == 1) {
						$pr_price_incl = $pr_add_price;
						$pr_price_excl = $pr_add_price - $pr_add_tax;
					} else {
						$pr_price_incl = $pr_add_price + $pr_add_tax;
						$pr_price_excl = $pr_add_price;
					}
					if ($tax_prices == 2 || $tax_prices == 3) {
						$pr_shown_price = $pr_price_incl;
					} else {
						$pr_shown_price = $pr_price_excl;
					}


					$properties_values .= "<br/>";
					if (!$hide_name) {
						$properties_values .= $property_name . $option_name_delimiter;
					}
					if (strtoupper($control_type) != "TEXTBOXLIST" && strtoupper($control_type) != "WIDTH_HEIGHT") {
						$properties_values .= $values_list_translation;
					}
					if (!$hide_name || (strtoupper($control_type) != "TEXTBOXLIST" && strtoupper($control_type) != "WIDTH_HEIGHT")) {
						if ($pr_add_price > 0) {
							$properties_values .= $option_positive_price_right . currency_format($pr_shown_price) . $option_positive_price_left;
						} else if ($pr_add_price < 0) {
							$properties_values .= $option_negative_price_right . currency_format(abs($pr_shown_price)) . $option_negative_price_left;
						}
					}

					if (strtoupper($control_type) == "TEXTBOXLIST") {
						$properties_values .= $values_list;
					} else if (strtoupper($control_type) == "WIDTH_HEIGHT") {
						if (!$hide_name) {
							$properties_values .= "<br/>";
						}
						$properties_values .= WIDTH_MSG.$option_name_delimiter.$property_values["width"]." ".strtoupper($length_units);
						$properties_values .= "<br/>".HEIGHT_MSG.$option_name_delimiter.$property_values["height"]." ".strtoupper($length_units);
					}

					if ($control_type == "IMAGEUPLOAD" && preg_match("/^http\:\/\//", $values_list_translation)) { 
						$values_list_translation = "<a href=\"".$values_list_translation."\" target=\"_blank\">" . basename($values_list_translation) . "</a>";
					}

					$properties_info[] = array($property_id, $control_type, $property_name_initial, $hide_name, $values_list, $pr_add_price, $pr_add_weight, $pr_actual_weight, $pr_values, $property_order, $length_units);
					$properties_prices[$property_id] = $pr_add_price;
				}
			}
		}
		// save text and html variables
		$properties_values_html = $properties_values;
		$properties_values_text = str_replace("<br/>", "\n", $properties_values);

		// check downloads for product
		$sql  = " SELECT * FROM " . $table_prefix . "items_files ";
		$sql .= " WHERE (item_id=" . $db->tosql($item_id, INTEGER);
		$sql .= " AND download_type=1) ";
		if (sizeof($options_downloads)) {
			$files_ids = join(",", $options_downloads);
			$sql .= " OR (download_type=2 AND ";
			$sql .= " file_id IN (" . $db->tosql($files_ids, INTEGERS_LIST) . "))";
		}
		$db->query($sql);
		while ($db->next_record()) {
			$file_id = $db->f("file_id");
			$downloads[$file_id] = $db->Record;
		}

		set_session("shopping_cart", $shopping_cart);
	}

