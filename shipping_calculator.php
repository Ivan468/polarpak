<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  shipping_calculator.php                                  ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$type = "details";
	include_once("./includes/common.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/admin_messages.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/profile_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/shipping_functions.php");
	include_once("./includes/record.php");

	$tax_rates = get_tax_rates();
	// check id of temporary item in the shopping cart
 	if (!isset($new_cart_id)) {
		$new_cart_id = get_param("new_cart_id");
	}
	$form_name = get_param("form_name");
	$selected_index = get_param("selected_index");
	$control_id = get_param("control_id");

	// get tax settings
	$tax_prices_type = get_setting_value($settings, "tax_prices_type", 0);
	$tax_prices_show = get_setting_value($settings, "tax_prices", 0);
	$tax_note_incl = get_translation(get_setting_value($settings, "tax_note", ""));
	$tax_note_excl = get_translation(get_setting_value($settings, "tax_note_excl", ""));

	// option delimiter and price options
	$option_name_delimiter = get_setting_value($settings, "option_name_delimiter", ": "); 
	$option_positive_price_right = get_setting_value($settings, "option_positive_price_right", ""); 
	$option_positive_price_left = get_setting_value($settings, "option_positive_price_left", ""); 
	$option_negative_price_right = get_setting_value($settings, "option_negative_price_right", ""); 
	$option_negative_price_left = get_setting_value($settings, "option_negative_price_left", "");

	// get price type
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

	$t = new VA_Template($settings["templates_dir"]);
	$t->set_file("main", "shipping_calculator.html");
	$t->set_var("shipping_calculator_href", "shipping_calculator.php");
	$t->set_var("new_cart_id", htmlspecialchars($new_cart_id));
	$t->set_var("form_name", htmlspecialchars($form_name));
	$t->set_var("selected_index", htmlspecialchars($selected_index));
	$t->set_var("control_id", htmlspecialchars($control_id));

	// set necessary scripts
	set_script_tag("js/profile.js");

	$css_file = "";
	$style_name = get_setting_value($settings, "style_name", "");
	$scheme_class = get_setting_value($settings, "scheme_name", "");
	if (strlen($style_name)) {
		$css_file = "styles/".$style_name;
		if (!preg_match("/\.css$/", $style_name)) { $css_file .= ".css"; }
	}
	$t->set_var("css_file", $css_file);
	$t->set_var("scheme_class", $scheme_class);
	set_link_tag($css_file, "stylesheet", "text/css");

	$operation = get_param("operation");
	$user_id = get_session("session_user_id");
	$user_type_id = get_session("session_user_type_id");

	$countries = get_db_values("SELECT country_id,country_name FROM " . $table_prefix . "countries WHERE show_for_user=1 ORDER BY country_order, country_name ", array(array("", SELECT_COUNTRY_MSG)));

	// check destination point 
	$country_id = ""; $state_id = ""; $postal_code_param = "";
	if (strlen($operation)){
		$country_id = get_param("country_id");
		$state_id = get_param("state_id");
		$postal_code_param = get_param("postal_code");
		$shipping_info = array(
			"country_id" => $country_id,
			"state_id" => $state_id,
			"postal_code" => $postal_code_param,
			"zip" => $postal_code_param,
		);
		set_session("session_shipping_info", $shipping_info);
	} else {
		$shipping_info = get_session("session_shipping_info");
		if (is_array($shipping_info)) {
			$country_id = get_setting_value($shipping_info, "country_id");
			$state_id = get_setting_value($shipping_info, "state_id");
			$postal_code_param = get_setting_value($shipping_info, "postal_code");
		} else if ($user_id) {
			// get data from user profile
			$sql = " SELECT * FROM " . $table_prefix . "users ";
			$sql.= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$user_data = $db->Record;

				// get user type settings to check what data we need to get delivery or personal
				$setting_type = "user_profile_" . $user_type_id;
				$user_settings = get_settings($setting_type); 		
				$show_delivery_country_id = get_setting_value($user_settings, "show_delivery_country_id");
				$show_delivery_state_id = get_setting_value($user_settings, "show_delivery_state_id");
				$show_delivery_zip = get_setting_value($user_settings, "show_delivery_zip");
				if ($show_delivery_country_id || $show_delivery_state_id || $show_delivery_zip) {
					$country_id = get_setting_value($user_data, "delivery_country_id");
					$state_id = get_setting_value($user_data, "delivery_state_id");
					$postal_code_param = get_setting_value($user_data, "delivery_zip");
				} else {
					$country_id = get_setting_value($user_data, "country_id");
					$state_id = get_setting_value($user_data, "state_id");
					$postal_code_param = get_setting_value($user_data, "zip");
				}
			}
		}
	}

	$r = new VA_Record("");
	$r->add_select("country_id", INTEGER, $countries, COUNTRY_FIELD);
	$r->add_select("state_id", INTEGER, "", STATE_FIELD);

	$r->set_value("country_id", $country_id);
	$r->set_value("state_id", $state_id);

	prepare_states($r);	

	$r->set_form_parameters();

	$delivery_items = array();
	if (strlen($new_cart_id)) {
		// for newly added item check temporary shipping cart array
		$shopping_cart = get_session("shipping_cart");
	} else {
		$shopping_cart = get_session("shopping_cart");
	}
	if (is_array($shopping_cart)) {

		$items_quantity = 0; $items_weight = 0; $cart_item_weight = 0; $items_actual_weight = 0; $cart_item_actual_weight = 0;
		foreach ($shopping_cart as $cart_id => $item) {
			$item_id = $item["ITEM_ID"];
			$quantity = $item["QUANTITY"];
			$price = $item["PRICE"];
			$full_price = $item["PRICE"];
			$properties = $item["PROPERTIES"];
			$cart_item_weight = 0;
			$cart_item_actual_weight = 0;
			$properties_price = $item["PROPERTIES_PRICE"];

			// clear 
			$t->set_var("components_block", "");
			$t->set_var("properties_values", "");

			$item_name = get_translation($item["ITEM_NAME"]);
			$sql  = " SELECT i.item_id, i.item_name, i.weight, i.actual_weight, i.packages_number, ";
			$sql .= " i.width, i.height, i.length, i.downloadable, i.is_shipping_free, i.shipping_cost, ";
			$sql .= " i.is_separate_shipping, i.shipping_modules_default, i.shipping_modules_ids ";
			$sql .= " FROM " . $table_prefix . "items i ";
			$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$weight = doubleval($db->f("weight"));
				$actual_weight = doubleval($db->f("actual_weight"));
				$packages_number = doubleval($db->f("packages_number"));
				$width = $db->f("width");
				$height = $db->f("height");
				$length = $db->f("length");
				$downloadable = $db->f("downloadable");
				$is_shipping_free = $db->f("is_shipping_free");
				$shipping_cost = doubleval($db->f("shipping_cost"));
				$is_separate_shipping = $db->f("is_separate_shipping");
				$shipping_modules_default = $db->f("shipping_modules_default");
				$shipping_modules_ids = $db->f("shipping_modules_ids");
			}

			// check product properties and their additional weight
			$properties_values = ""; $options_weight = 0; $options_actual_weight = 0; $options_price = 0;
			if (is_array($properties)) {
				foreach ($properties as $property_id => $property_values) {
					$sql  = " SELECT property_type_id, property_name, hide_name, control_type, ";
					$sql .= " property_price_type, additional_price, trade_additional_price, free_price_type, free_price_amount, length_units ";
					$sql .= " FROM " . $table_prefix . "items_properties ";
					$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
					$db->query($sql);
					if ($db->next_record()) {
						$property_type_id = $db->f("property_type_id");
						// show only product options and subcomponents separately
						if ($property_type_id == 1) {
							$property_name = get_translation($db->f("property_name"));
							$hide_name = $db->f("hide_name");
							$control_type = $db->f("control_type");

							$property_price_type = $db->f("property_price_type");
							$additional_price = doubleval($db->f($additional_price_field));
							$free_price_type = $db->f("free_price_type");
							$free_price_amount = $db->f("free_price_amount");
							$length_units = $db->f("length_units");

							if ($control_type == "WIDTH_HEIGHT") {
								$property_price_type = 1;
								$property_width = $property_values["width"];
								$property_height = $property_values["height"];
								property_sizes($property_id, $property_width, $property_height, $size_price, $min_width, $max_width, $min_height, $max_height, $prices);
								$additional_price += $size_price;
							}

							$option_price = calculate_control_price($item["PROPERTIES_INFO"][$property_id]["VALUES"], $item["PROPERTIES_INFO"][$property_id]["TEXT"], $property_price_type, $additional_price, $free_price_type, $free_price_amount);
							$options_price += $option_price;

							$additional_weight = 0; $additional_actual_weight = 0; 
							if (strtoupper($control_type) == "LISTBOX" || strtoupper($control_type) == "RADIOBUTTON"
								|| strtoupper($control_type) == "CHECKBOXLIST" || strtoupper($control_type) == "TEXTBOXLIST") {
								$values_list = ""; 
								for($pv = 0; $pv < sizeof($property_values); $pv++) {
									$sql  = " SELECT property_value, additional_weight, actual_weight, additional_price, trade_additional_price ";
									$sql .= " FROM " . $table_prefix . "items_properties_values ipv ";
									$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
									$sql .= " AND item_property_id=" . $db->tosql($property_values[$pv], INTEGER);
									$db->query($sql);
									if ($db->next_record()) {
										$additional_weight = doubleval($db->f("additional_weight"));
										$additional_actual_weight = doubleval($db->f("actual_weight"));
										$additional_price = doubleval($db->f($additional_price_field));

										$option_price += $additional_price;
										$options_price += $additional_price;

										if (strtoupper($control_type) == "TEXTBOXLIST") {
											$values_list .= "<br>";
											$values_list .= get_translation($db->f("property_value")) . ": ";
											$values_list .= $item["PROPERTIES_INFO"][$property_id]["TEXT"][$property_values[$pv]];
										} else {
											if ($values_list) { $values_list .= ", "; }
											$values_list .= get_translation($db->f("property_value"));
										}
									}
								}

								if (strtoupper($control_type) == "TEXTBOXLIST") {
									$properties_values .= "<br>" . $property_name . ": ";
								} else {
									$properties_values .= "<br>" . $property_name . ": " . $values_list;
								}
								if ($additional_weight > 0) {
									$properties_values .= " (<b>" . weight_format($additional_weight) . "</b>)";
								}
								if (strtoupper($control_type) == "TEXTBOXLIST") {
									$properties_values .= $values_list;
								}
							} elseif (strtoupper($control_type) == "WIDTH_HEIGHT") {
								if (!$hide_name) {
									$properties_values .= "<br />" . $property_name . $option_name_delimiter;
								}
								$properties_values .= "<br />" . WIDTH_MSG . $option_name_delimiter . $property_values["width"]." ".strtoupper($length_units);
								$properties_values .= "<br />" . HEIGHT_MSG. $option_name_delimiter . $property_values["height"]." ".strtoupper($length_units);

							} elseif ($property_values[0]) {
								$property_value = get_translation($property_values[0]);
								if (preg_match("/^http\:\/\//", $property_value)) {
									$property_value = "<a href=\"".$property_value."\" target=\"_blank\">" . basename($property_value) . "</a>";
								}
								$properties_values .= "<br>" . $property_name . ": " . $property_value;

								if ($additional_weight > 0) {
									$properties_values .= " (<b>" . weight_format($additional_weight) . "</b>)";
								}
							}
							$options_weight += $additional_weight;
							$options_actual_weight += $additional_actual_weight;
						}
					}
				}
			}
			$t->set_var("item_name", htmlspecialchars($item_name));
			$t->set_var("properties_values", $properties_values);
			$t->set_var("quantity", $quantity);	
			if ($weight > 0) {
				$t->set_var("item_weight", "(<b>".weight_format($weight)."</b>)");
			} else {
				$t->set_var("item_weight", "");
			}
			$weight += $options_weight;
			$actual_weight += $options_actual_weight;
			$full_price += $options_price;
			$item_total_weight = $quantity * $weight;
			$item_total_actual_weight = $quantity * $actual_weight;
			$items_quantity += $quantity; 
			$cart_item_weight += $item_total_weight;
			$cart_item_actual_weight += $item_total_actual_weight;

			// add parent item to delivery list
			$delivery_items[] = array(
				"item_id" => $item_id, 
				"quantity" => $quantity, 
				"packages_number" => $packages_number, 
				"price" => $price, 
				"full_price" => $full_price, 
				"weight" => $weight, 
				"full_weight" => $weight, 
				"actual_weight" => $actual_weight, 
				"full_actual_weight" => $actual_weight, 
				"width" => $width, 
				"height" => $height, 
				"length" => $length, 
				"downloadable" => $downloadable, 
				"is_shipping_free" => $is_shipping_free, 
				"shipping_cost" => $shipping_cost, 
				"is_separate_shipping" => $is_separate_shipping, 
				"shipping_modules_default" => $shipping_modules_default, 
				"shipping_modules_ids" => $shipping_modules_ids, 
			);

			// parse components
			$components = $item["COMPONENTS"];
			if (is_array($components) && sizeof($components) > 0) {
				$t->set_var("components", "");
				foreach ($components as $property_id => $component_values) {
					foreach ($component_values as $item_property_id => $component) {
						$property_type_id = $component["type_id"];
						$sub_item_id = $component["sub_item_id"];
						$sub_quantity = $component["quantity"];
						if ($property_type_id == 2) {
							$sql  = " SELECT pr.property_name AS component_name, pr.quantity, pr.quantity_action, ";
							$sql .= " i.item_id, i.price, i.packages_number, i.weight, i.actual_weight, i.width, i.height, i.length, ";
							$sql .= " i.downloadable, i.is_shipping_free, i.shipping_cost, ";
							$sql .= " i.is_separate_shipping, i.shipping_modules_default, i.shipping_modules_ids ";
							$sql .= " FROM (" . $table_prefix . "items_properties pr ";
							$sql .= " INNER JOIN " . $table_prefix . "items i ON pr.sub_item_id=i.item_id)";
							$sql .= " WHERE pr.property_id=" . $db->tosql($property_id, INTEGER);
						} else {
							$sql  = " SELECT ipv.property_value AS component_name, i.weight, i.actual_weight, ipv.quantity, pr.quantity_action, ";
							$sql .= " i.item_id, i.price, i.packages_number, i.width, i.height, i.length, ";
							$sql .= " i.downloadable, i.is_shipping_free, i.shipping_cost, ";
							$sql .= " i.is_separate_shipping, i.shipping_modules_default, i.shipping_modules_ids ";
							$sql .= " FROM ((" . $table_prefix . "items_properties_values ipv ";
							$sql .= " INNER JOIN " . $table_prefix . "items_properties pr ON ipv.property_id=pr.property_id) ";
							$sql .= " INNER JOIN " . $table_prefix . "items i ON ipv.sub_item_id=i.item_id)";
							$sql .= " WHERE ipv.item_property_id=" . $db->tosql($item_property_id, INTEGER);
						}
						$db->query($sql);
						if ($db->next_record()) {
							$component_item_id = $db->f("item_id");
							$component_name = $db->f("component_name");
							$component_quantity = $db->f("quantity");
							$component_qty_action = $db->f("quantity_action");
							if ($component_quantity < 1) { $component_quantity = 1; }
							if ($component_qty_action != 2) { $component_quantity = $component_quantity * $quantity; }
			
							$price = $db->f("price");
							$weight = $db->f("weight");
							$actual_weight = $db->f("actual_weight");
							$component_total_weight = $component_quantity * $weight;
							$component_total_actual_weight = $component_quantity * $actual_weight;
							$cart_item_weight += $component_total_weight;
							$cart_item_actual_weight += $component_total_actual_weight;
							$packages_number = $db->f("packages_number");
							$width = $db->f("width");
							$height = $db->f("height");
							$length = $db->f("length");
							$downloadable = $db->f("downloadable");
							$is_shipping_free = $db->f("is_shipping_free");
							$shipping_cost = $db->f("shipping_cost");
							$is_separate_shipping = $db->f("is_separate_shipping");
							$shipping_modules_default = $db->f("shipping_modules_default");
							$shipping_modules_ids = $db->f("shipping_modules_ids");

							// add component item to delivery list
							$delivery_items[] = array(
								"item_id" => $component_item_id, 
								"quantity" => $component_quantity, 
								"packages_number" => $packages_number, 
								"price" => $price, 
								"full_price" => $price, 
								"weight" => $weight, 
								"full_weight" => $weight, 
								"actual_weight" => $actual_weight, 
								"full_actual_weight" => $actual_weight, 
								"width" => $width, 
								"height" => $height, 
								"length" => $length, 
								"downloadable" => $downloadable, 
								"is_shipping_free" => $is_shipping_free, 
								"shipping_cost" => $shipping_cost, 
								"is_separate_shipping" => $is_separate_shipping, 
								"shipping_modules_default" => $shipping_modules_default, 
								"shipping_modules_ids" => $shipping_modules_ids, 
							);

							$selection_name = "";
							if (isset($item["PROPERTIES_INFO"][$property_id])) {
								$selection_name = $item["PROPERTIES_INFO"][$property_id]["NAME"] . ": ";
							} 
							$t->set_var("selection_name", $selection_name);
							$t->set_var("component_quantity", $component_quantity);
							$t->set_var("component_name", htmlspecialchars($component_name));
							if ($weight > 0) {
								$t->set_var("component_weight", "(<b>".weight_format($weight)."</b>)");
							} else {
								$t->set_var("component_weight", "");
							}

							$t->sparse("components", true);
						}
					}
				}
				$t->sparse("components_block", false);
			} else {
				$t->set_var("components_block", "");
			}

			// set total weight of item bundle
			$items_weight += $cart_item_weight;
			$items_actual_weight += $cart_item_actual_weight;
			if ($cart_item_weight > 0) {
				$t->set_var("total_weight", weight_format($cart_item_weight));
			} else {
				$t->set_var("total_weight", NOT_AVAILABLE_MSG);
			}

			$t->parse("items", true);
		}

		if ($items_weight > 0) {
			$t->set_var("items_weight", weight_format($items_weight));
		} else {
			$t->set_var("items_weight", NOT_AVAILABLE_MSG);
		}
		$t->set_var("items_quantity", $items_quantity);
	}

	// check delivery methods and their costs
	if ($operation == "go" || ($country_id && $postal_code_param)) {
		$shipping_groups = get_shipping_types($country_id, $state_id, $postal_code_param, $site_id, $user_type_id, $delivery_items);

		//prepare taxes
		$tax_rates = get_tax_rates(true, $country_id, $state_id, $postal_code_param);		
		$tax_persentage = 0; $tax_fixed_amount = 0;

		$user_info = get_session("session_user_info");
		$user_tax_free = false;
		if(is_array($user_info) && $user_info["tax_free"] == true){
			$user_tax_free = true;
		}
		if (sizeof($tax_rates) > 0 && $user_tax_free === false) {
			foreach ($tax_rates as $tax_id => $tax_info) {
				if (floatval($tax_info["types"]["shipping"]["fixed_amount"]) > 0 || floatval($tax_info["types"]["shipping"]["tax_percent"]) > 0){
					if(floatval($tax_info["types"]["shipping"]["tax_percent"]) > 0){
						$tax_persentage += floatval($tax_info["types"]["shipping"]["tax_percent"]);
					}
					else{
						$tax_fixed_amount += floatval($tax_info["types"]["shipping"]["fixed_amount"]);
					}
				}
				elseif(floatval($tax_info["fixed_amount"]) > 0 || floatval($tax_info["tax_percent"]) > 0){
					if(floatval($tax_info["tax_percent"]) > 0){
						$tax_persentage += floatval($tax_info["tax_percent"]);
					}
					else{		
						$tax_fixed_amount += floatval($tax_info["fixed_amount"]);
					}
				}
			}
		}


		$si = 0; // shipping group index
		$st = 0; // shipping type index
		if (sizeof($shipping_groups)) {
			foreach ($shipping_groups as $group_id => $group) {
				$si++;
				$shipping_types = $group["types"];
				$t->set_var("shipping_methods", "");
				$t->set_var("si", $si);
				foreach ($shipping_types as $id => $shipping_info) {
		  		$st++;
					$shipping_id = $shipping_info["id"];
					$shipping_code = $shipping_info["code"];
					$shipping_name = $shipping_info["desc"];
					$shipping_cost = $shipping_info["cost"];
					$tare_weight = $shipping_info["tare_weight"];
					$shipping_taxable = $shipping_info["taxable"];
					$shipping_time = $shipping_info["shipping_time"];

					$t->set_var("shipping_id", htmlspecialchars($shipping_id));
					$t->set_var("shipping_type_id", htmlspecialchars($shipping_id));
					$t->set_var("shipping_name", htmlspecialchars($shipping_name));

					if ($shipping_cost > 0 || $tax_fixed_amount > 0) {

						// calculate taxes for shipping
						$shipping_tax_id = 0;
						$shipping_tax_free = (!$shipping_taxable);
						$shipping_tax = get_tax_amount($tax_rates, "shipping", $shipping_cost, 1, $shipping_tax_id, $shipping_tax_free, $shipping_tax_percent, $tax_rates);
						// get shipping values excl and incl tax
						if ($tax_prices_type == 1) {
							$shipping_excl_tax = $shipping_cost - $shipping_tax;
							$shipping_incl_tax = $shipping_cost;
						} else {
							$shipping_excl_tax = $shipping_cost;
							$shipping_incl_tax = $shipping_cost + $shipping_tax;
						}
						// formatting shipping cost accordingly to selected settings
						if ($tax_prices_show == 0 || $tax_prices_show == 1) {
							$shipping_cost_desc = currency_format($shipping_excl_tax);
						} else {
							$shipping_cost_desc = currency_format($shipping_incl_tax);
						}
						if ($tax_prices_show == 1 || $tax_prices_show == 2) {
							if ($tax_prices_show == 1) {
								$shipping_cost_desc .= " (".currency_format($shipping_incl_tax)." ".$tax_note_incl.")";
							} else {
								$shipping_cost_desc .= " (".currency_format($shipping_excl_tax)." ".$tax_note_excl.")";
							}
						}

						$t->set_var("shipping_cost", $shipping_cost_desc);
					} else {
						$t->set_var("shipping_cost", FREE_SHIPPING_MSG);
					}
					$t->parse("shipping_methods", true);
				}

				$group_name = $group["group_name"];
				if (!is_array($group_name) || !sizeof($group_name)) {
					$group_name = array(SHIPPING_METHOD_MSG);
				}

				$t->set_var("group_name", implode(" / ", $group_name));

				$t->parse("shipping", true);
			}
		}

		if ($shipping_errors) {
			$r->errors .= $shipping_errors;
		}
		if ($r->errors) {
			$t->set_var("shipping", "");
			$t->set_var("errors_list", $r->errors);
			$t->sparse("errors", false);
		} else if ($st == 0) {
			$t->set_var("shipping", "");
			$t->parse("no_shipping", false);
		}
		$t->set_var("shipping_groups_number", $si);

		// show buy buttons 
		if ($st > 0) {
			if (strlen($new_cart_id)) {
				$t->sparse("new_item_buttons", false);
			} else {
				$t->sparse("basket_buttons", false);
			}
		}

	}


	// set delivery fields
	//set_options($countries, $country_id, "country_id");
	//$states = get_db_values("SELECT state_id,state_name FROM " . $table_prefix . "states WHERE show_for_user=1 ORDER BY state_name ", array(array("", "")));
	//set_options($states, $state_id, "state_id");

	$t->set_var("postal_code", htmlspecialchars($postal_code_param));


	$page_code = get_param("page");
	$user_id = get_session("session_user_id");		
	$user_info = get_session("session_user_info");
	$user_type_id = get_setting_value($user_info, "user_type_id", "");

	$t->pparse("main");

function weight_format($weight)
{
	global $settings;
	$weight_measure = get_setting_value($settings, "weight_measure", "");
	if ($weight > 0) {
		if (strpos ($weight, ".") !== false) {
			while (substr($weight, strlen($weight) - 1) == "0") {
				$weight = substr($weight, 0, strlen($weight) - 1);
			}
		}
		if (substr($weight, strlen($weight) - 1) == ".") {
			$weight = substr($weight, 0, strlen($weight) - 1);
		}
		$weight .= " " . $weight_measure;
	} else {
		$weight = "0";
	}
	return $weight;
}


?>