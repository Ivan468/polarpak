<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  shopping_functions.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	global $sc_params, $sc_errors, $sc_notice, $sc_message, $language_code;
	$root_folder_path = ((isset($is_admin_path) && $is_admin_path) || (isset($is_sub_folder) && $is_sub_folder)) ? "../" : "./";
	include_once($root_folder_path."includes/products_functions.php");

	function add_to_cart($sc_item_id, $sc_index, $sc_price, $sc_quantity, $type, $cart, &$new_cart_id, &$sc_errors, &$sc_message, $db_item_id = "", $sc_item_name = "")
	{
		global $db, $t, $table_prefix, $site_id, $settings, $eol, $currency;

		$options_errors = "";
		$item_added = false;
		$item_status = ""; // could be SHIPPING or COMPARE when item added to the cart to calculate shipping cost or comparing items
		$sc_notice = "";
		$cart_item_id = ""; // current active cart item and related id in va_saved_items table
		$wish_item_id = ""; // id from va_saved_items table for previously saved product
		$order_item_id = ""; // id from va_orders_items table for previously saved order 

		if ($type == "cart") {
			$cart_item_id = $db_item_id;
		} else if ($type == "wish") {
			$wish_item_id = $db_item_id;
		} else if ($type== "order") {
			$order_item_id = $db_item_id;
		}
		//$cart_item_id = ($type == "db") ? $db_item_id : "";
		$user_id = get_session("session_user_id");

		// check call center order option
		$cc_order = false; $cc_price = "";
		$operation = get_param("operation"); 
		if ($operation == "fast_order" || $operation == "cc_order") {
			$admin_permissions = get_admin_permissions();
			$call_center = get_setting_value($admin_permissions, "create_orders", 0);
			if ($call_center) { 
				$cc_order = true; 
				$cc_price = get_param("cc_price");
			}
		}

		if ($cart == "SHIPPING") {                                            
			$shopping_cart = array(); // always refresh array for shipping as shipping calculate for one item 
		} else if ($cart == "COMPARE") {
			$shopping_cart = get_session("compare_cart");
			if (is_array($shopping_cart) && count($shopping_cart) >= 5) {
				array_shift($shopping_cart);
			}
		} else if ($cart == "WISHLIST") {
			$shopping_cart = array(); // use empty array for wishlist as it will be saved in database
		} else {
			if ($cart == "CHECKOUT" || $cart == "SHIPPINGADD") {
				$cart = "ADD"; // use default 'add' operation for different cart methods
			}
			$shopping_cart = get_session("shopping_cart");
		}
		if (!is_array($shopping_cart)) { $shopping_cart = array(); }

		// check if cart item was already added to the cart so we don't need to add it again
		if ($cart_item_id || $wish_item_id || $order_item_id) {
			foreach ($shopping_cart as $cart_id => $cart_item) {
				$sc_cart_item_id = get_setting_value($cart_item, "CART_ITEM_ID");
				$sc_wish_item_id = get_setting_value($cart_item, "WISH_ITEM_ID");
				$sc_order_item_id = get_setting_value($cart_item, "ORDER_ITEM_ID");
				if (($cart_item_id && $cart_item_id == $sc_cart_item_id) || 
					($wish_item_id && $wish_item_id == $sc_wish_item_id) || 
					($order_item_id && $order_item_id == $sc_order_item_id)) {
					return true;
				}
			}
		}

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

		$is_error = false;
		if (!strlen($sc_item_id)) {
			$is_error = true;
			$sc_errors .= str_replace("{field_name}", ID_MSG, REQUIRED_MESSAGE) . "<br/>";
		}	else if (!$cc_order && !VA_Products::check_permissions($sc_item_id, VIEW_ITEMS_PERM)) {
			$sc_errors .= va_constant("PROD_NOT_AVAILABLE_ERROR")."<br/>";
			$is_error = true;
		}

		if (!$is_error) {
			$sql  = " SELECT i.item_type_id,i.item_name,i.".$price_field.",i.is_price_edit,i.is_sales,i.".$sales_field.",i.buying_price,";
			$sql .= " i.tax_id,i.tax_free,i.stock_level, i.min_quantity,i.max_quantity,i.quantity_increment, ";
			$sql .= " i.use_stock_level,i.hide_out_of_stock,i.disable_out_of_stock, ";
			$sql .= " it.is_user_voucher ";
			$sql .= " FROM (" . $table_prefix . "items i ";
			$sql .= " LEFT JOIN " . $table_prefix . "item_types it ON i.item_type_id=it.item_type_id) ";
			$sql .= " WHERE i.item_id=" . $db->tosql($sc_item_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$item_type_id = $db->f("item_type_id");
				$is_user_voucher = $db->f("is_user_voucher");
				$item_name = $db->f("item_name");
				$stock_level = $db->f("stock_level");
				$use_stock_level = $db->f("use_stock_level");
				$hide_out_of_stock = $db->f("hide_out_of_stock");
				$disable_out_of_stock = $db->f("disable_out_of_stock");
				$min_quantity = intval($db->f("min_quantity"));
				if (!strlen($sc_quantity) && $min_quantity) {
					$sc_quantity = $min_quantity;
				} 
				if ($cart != "QTY" && $sc_quantity < 1) {
					$sc_quantity = 1;
				}
				$max_quantity = $db->f("max_quantity");
				$quantity_increment = $db->f("quantity_increment");
				$buying_price = $db->f("buying_price");
				$tax_id = $db->f("tax_id");
				$tax_free = $db->f("tax_free");
				$is_price_edit = $db->f("is_price_edit");
				$price = $db->f($price_field);
				$is_sales = $db->f("is_sales");
				$sales_price = $db->f($sales_field);

				if ($is_price_edit) {
					$price = $sc_price;
				} else {
					$coupons_ids = ""; $coupons_discount = ""; $coupons_applied = array();
					get_sales_price($price, $is_sales, $sales_price, $sc_item_id, $item_type_id, $coupons_ids, $coupons_discount, $coupons_applied);
					$price = calculate_price($price, $is_sales, $sales_price);
				}
				$properties_buying = 0;
				$properties_discount = 0;
				$discount_applicable = 1;
				if ($is_user_voucher) {
					if (!$user_id) {
						$sc_errors .= va_constant("VOUCHER_SIGN_IN_MSG")."<br>"; 
						$is_error = true;
					} else {
						// check default voucher settings
						$voucher_settings = get_settings("user_voucher");
						$default_voucher_purchase = get_setting_value($voucher_settings, "voucher_purchase", 0);
						// check user settings
						$user_settings = user_settings($user_id);
						$voucher_purchase = get_setting_value($user_settings, "voucher_purchase", $default_voucher_purchase);
						if (!$voucher_purchase) {
							$sc_errors .= va_constant("VOUCHER_PURCHASE_ERROR")."<br>"; 
							$is_error = true;
						}
					}
				}
			} else {
				$sc_errors .= va_constant("PROD_NOT_AVAILABLE_ERROR")."<br>";
				$is_error = true;
			}
		}
		
		if ($is_error){
			// item doesn't exists or no longer available
			if ($type == "cart" || $type == "wish" || $type == "order") {

				$item = array (
				"ITEM_ID"	=> intval($sc_item_id),
				"CART_ITEM_ID" => $cart_item_id,
				"ORDER_ITEM_ID" => $order_item_id,
				"WISH_ITEM_ID" => $wish_item_id,
				"ITEM_TYPE_ID"	=> 0,
				"ITEM_NAME" => $sc_item_name,
				"ERROR" => PROD_NOT_AVAILABLE_ERROR."<br>",
				"PROPERTIES"	=> "", "PROPERTIES_PRICE"	=> 0, "PROPERTIES_PERCENTAGE"	=> 0,
				"PROPERTIES_BUYING"	=> 0, "PROPERTIES_DISCOUNT" => 0, 
				"PROPERTIES_EXISTS" => 0, "PROPERTIES_REQUIRED" => 0, "PROPERTIES_MESSAGE" => "",
				"COMPONENTS" => "",
				"QUANTITY"	=> $sc_quantity, // only one item can be placed
				"TAX_ID" => 0, "TAX_FREE" => 0, "DISCOUNT" => 0, "BUYING_PRICE" => 0, "PRICE_EDIT"	=> 0,
				"PRICE"	=> $sc_price
				);
				//-- add to cart with error
				$shopping_cart[] = $item;
				end($shopping_cart);
				$new_cart_id = key($shopping_cart);
				set_session("shopping_cart", $shopping_cart);

				return true;
			} else {
				return false;
			}
		}

		// calculate summary stock levels for products and options available in the cart
		$stock_levels = array();
		foreach ($shopping_cart as $cart_id => $cart_info) {
			$item_id = $cart_info["ITEM_ID"];
			$item_quantity = $cart_info["QUANTITY"];
			$item_properties = $cart_info["PROPERTIES"];
			if (!is_array($item_properties)) { $item_properties = array(); }
			if (isset($stock_levels[$item_id])) {
				$stock_levels[$item_id] += $item_quantity;
			} else {
				$stock_levels[$item_id] = $item_quantity;
			}
			$item_components = $cart_info["COMPONENTS"];
			if (is_array($item_components) && sizeof($item_components) > 0) {
				foreach ($item_components as $property_id => $component_values) {
					foreach ($component_values as $property_item_id => $component) {
						$sub_item_id = $component["sub_item_id"];
						$sub_quantity = $component["quantity"];
						$sub_quantity_action = $component["quantity_action"];			
						if ($sub_quantity < 1) { $sub_quantity = 1; }
						if ($sub_quantity_action == 2) {
							$component_quantity = $sub_quantity;
						} else {
							$component_quantity = $item_quantity * $sub_quantity;
						}
						if (isset($stock_levels[$sub_item_id])) {
							$stock_levels[$sub_item_id] += $component_quantity;
						} else {
							$stock_levels[$sub_item_id] = $component_quantity;
						}
					}
				}
			}
		}

		// check stock level for parent product
		if (isset($stock_levels[$sc_item_id])) {
			$total_quantity = $stock_levels[$sc_item_id];
			if ($type != "options") {
				$total_quantity += $sc_quantity;
			}
		} else {
			$total_quantity = $sc_quantity;
		}

		if ($cc_order) {
			// allow for call center add out stock products 
			$hide_out_of_stock = 0; $disable_out_of_stock = 0; $min_quantity = 0; $max_quantity = ""; $quantity_increment = "";
		}

		// check stock levels only if product added to the shopping cart
		if (($cart == "ADD" || $cart == "QTY") && $use_stock_level && $stock_level < $total_quantity && ($hide_out_of_stock || $disable_out_of_stock)) {
			if ($stock_level > 0) {
				$limit_error = str_replace("{limit_quantity}", $stock_level, va_constant("PRODUCT_LIMIT_MSG"));
				$limit_error = str_replace("{product_name}", get_translation($item_name), $limit_error);
				$sc_errors .= $limit_error . "<br>";
			} else {
				$sc_errors .= va_constant("PRODUCT_OUT_STOCK_MSG") . "<br>";
			}
			if ($type != "cart" && $type != "wish" && $type != "order") {
				return false;
			}
		} elseif (($cart == "ADD" || $cart == "QTY") && $min_quantity && $total_quantity < $min_quantity) {
			$limit_error = str_replace("{limit_quantity}", $min_quantity, va_constant("PRODUCT_MIN_LIMIT_MSG"));
			$limit_error = str_replace("{product_name}", get_translation($item_name), $limit_error);
			$sc_errors .= $limit_error . "<br>";
			if ($type != "cart" && $type != "wish" && $type != "order") { return false; }
		} elseif (($cart == "ADD" || $cart == "QTY") && $max_quantity && $total_quantity > $max_quantity) {
			$limit_error = str_replace("{limit_quantity}", $max_quantity, va_constant("PRODUCT_LIMIT_MSG"));
			$limit_error = str_replace("{product_name}", get_translation($item_name), $limit_error);
			$sc_errors .= $limit_error . "<br>";
			if ($type != "cart" && $type != "wish" && $type != "order") { return false; }
		} elseif (($cart == "ADD" || $cart == "QTY") && $quantity_increment && (($sc_quantity - $min_quantity) % $quantity_increment) != 0) {
			$quantity_error = str_replace("{quantity}", $sc_quantity, PRODUCT_QUANTITY_ERROR);
			$quantity_error = str_replace("{product_name}", get_translation($item_name), $quantity_error);
			$sc_errors .= $quantity_error . "<br>";
			$quantities_list = ""; $quantities_index = 0;
			$quantity_list = ($min_quantity) ? $min_quantity : $quantity_increment;
			while ((!$max_quantity || $quantity_list < $max_quantity) && $quantities_index < 5) {
				$quantities_index++;
				$quantities_list .= $quantity_list.", ";
				$quantity_list += $quantity_increment;
			}
			if (!$max_quantity || $quantity_list < $max_quantity) {
				$quantities_list .= "...";
			}
			$quantities_allowed = str_replace("{quantities_list}", $quantities_list, va_constant("PRODUCT_ALLOWED_QUANTITIES_MSG"));
			$sc_errors .= $quantities_allowed . "<br>";
			if ($type != "cart" && $type != "wish" && $type != "order") { return false; }
		} elseif ($is_price_edit && $type != "options") {
			$error_message = "";
			if (!strlen($price)) {
				$error_message = str_replace("{field_name}", va_constant("PRICE_MSG"), va_constant("REQUIRED_MESSAGE"));
			} elseif (!is_numeric($price)) {
				$error_message = str_replace("{field_name}", va_constant("PRICE_MSG"), va_constant("INCORRECT_VALUE_MESSAGE"));
			} elseif ($price < 0) {
				$error_message = str_replace("{field_name}", va_constant("PRICE_MSG"), va_constant("MIN_VALUE_MESSAGE"));
				$error_message = str_replace("{min_value}", "0.01", $error_message);
			}
			if ($error_message) {
				$sc_errors .= $error_message . "<br>" . $eol;
				if ($type != "cart" && $type != "wish" && $type != "order") {
					return false;
				}
			} else {
				// convert value to basic currency
				$price = $price / $currency["rate"];
			}
		}

		// get saved properties from db
		$db_properties = array();
		if ($type == "cart" || $type == "wish") {
			$sql  = " SELECT property_id, property_value, property_values_ids FROM " . $table_prefix . "saved_items_properties ";
			$sql .= " WHERE cart_item_id=" . $db->tosql($db_item_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$property_id = $db->f("property_id");
				$property_value = $db->f("property_value");
				$property_values_ids = $db->f("property_values_ids");
				if (preg_match("/^\[.+\]$/", $property_value)) {
					$db_properties[$property_id] = json_decode($property_value, true);
				} else {
					if (strlen($property_value)) {
						$db_properties[$property_id] = array($property_value);
					} elseif (strlen($property_values_ids)) {
						$db_properties[$property_id] = explode(",", $property_values_ids);
					}
				}
			}
		} else if ($type == "order") {
			$sql  = " SELECT oip.property_id, oip.property_name, oip.property_value, oip.property_values_ids, ip.control_type ";
			$sql .= " FROM " . $table_prefix . "orders_items_properties oip ";
			$sql .= " INNER JOIN " . $table_prefix . "items_properties ip ON ip.property_id=oip.property_id ";
			$sql .= " WHERE order_item_id=" . $db->tosql($db_item_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$property_id = $db->f("property_id");
				$property_value = $db->f("property_value");
				$property_name = $db->f("property_name");
				$control_type = $db->f("control_type");
				$property_values_ids = $db->f("property_values_ids");
				if ($control_type == "WIDTH_HEIGHT") {
					if (!isset($db_properties[$property_id])) {
						$db_properties[$property_id] = array("width" => "", "height" => "");
					}
					if (strtoupper($property_name) == "WIDTH_MSG" || strtoupper($property_name) == "WIDTH") {
						$db_properties[$property_id]["width"] = $property_value;
					} else if (strtoupper($property_name) == "HEIGHT_MSG" || strtoupper($property_name) == "HEIGHT") {
						$db_properties[$property_id]["height"] = $property_value;
					}
				} else {
					if (strlen($property_values_ids)) {
						$db_properties[$property_id] = explode(",", $property_values_ids);
					} elseif (strlen($property_value)) {
						$db_properties[$property_id] = array($property_value);
					}
				}
			}
		}

		$components = array(); $components_values = array();
		$components_price = 0; $controls_price = 0;
		$properties_ids = ""; $properties = array(); $properties_info = array(); $product_properties = array();
		$properties_exists = false; $properties_required = false; $properties_message = ""; 
		//TODO: something
		if ($cart == "QTY") {
			$properties = $shopping_cart[$sc_index]["PROPERTIES"];
			if (!is_array($properties)) { $properties = array(); }
			$product_properties = $shopping_cart[$sc_index]["PROPERTIES_INFO"];
			if (!is_array($product_properties)) { $product_properties = array(); }
			$components = $shopping_cart[$sc_index]["COMPONENTS"];
		} else {
			// begin: get properties for added product
			$sql  = " SELECT ip.property_type_id, ip.property_order, ip.usage_type, ip.property_id, ip.sub_item_id, ip.property_name, ";
			$sql .= " ip.quantity, ip.quantity_action, ip.property_price_type, ip.additional_price, ip.trade_additional_price, ";
			$sql .= " ip.control_type, ip.required, ip.parent_property_id, ip.parent_value_id, ";
			$sql .= " ip.percentage_price_type, ip.percentage_property_id, ip.free_price_type, ip.free_price_amount, ";
			$sql .= " ip.use_on_second, ip.use_on_details, ip.use_on_list, ip.use_on_table, ip.use_on_grid ";
			$sql .= " FROM (" . $table_prefix . "items_properties ip ";
			$sql .= " LEFT JOIN " . $table_prefix . "items_properties_sites ips ON ip.property_id=ips.property_id) ";
			$sql .= " WHERE (ip.item_id=" . $db->tosql($sc_item_id, INTEGER) . " OR ip.item_type_id=" . $db->tosql($item_type_id, INTEGER) . ") ";
			if (isset($site_id)) {
				$sql .= " AND (ip.sites_all=1 OR ips.site_id=" . $db->tosql($site_id, INTEGER) . ")";
			} else {
				$sql .= " AND ip.sites_all=1 ";
			}
			$sql .= " AND ip.show_for_user=1 ";
			$sql .= " ORDER BY ip.property_order, ip.property_id ";
			$db->query($sql);
			while ($db->next_record())
			{
				$property_id = $db->f("property_id");
				$property_name = $db->f("property_name");
				$property_order = $db->f("property_order");
				$usage_type = $db->f("usage_type");
				$parent_property_id = $db->f("parent_property_id");
				$parent_value_id = $db->f("parent_value_id");
				$property_type_id = $db->f("property_type_id");
				$property_name = get_translation($db->f("property_name"));
				$property_price_type = $db->f("property_price_type");
				$additional_price = doubleval($db->f($additional_price_field));
				$percentage_price_type = $db->f("percentage_price_type");
				$percentage_property_id = $db->f("percentage_property_id");
				$free_price_type = $db->f("free_price_type");
				$free_price_amount = $db->f("free_price_amount");
				$property_quantity_action = $db->f("quantity_action");
				$use_on_second = $db->f("use_on_second");
				$option_step = 1; // only one step available
	  
				if ($property_type_id == 2) {
					// single components which doesn't have any dependent parent option
					$sub_item_id = $db->f("sub_item_id");
					$sub_quantity = $db->f("quantity");
					if ($sub_quantity < 1) { $sub_quantity = 1; }
					$components[$property_id][0] = array(
						"type_id" => 2, "usage_type" => $usage_type, "sub_item_id" => $sub_item_id, 
						"quantity" => $sub_quantity, "quantity_action" => $property_quantity_action, 
						"name" => $property_name,
						"price" => $additional_price);
				} else {
					// radio/listbox/checkboxes components which could have dependent parent option
					$property_type = $db->f("control_type");
					$property_required = $db->f("required");
					$property_values = array();
					$values_text = array();
					if ($properties_ids) { $properties_ids .= ","; }
					$properties_ids .= $property_id;
					if ($property_type != "WIDTH_HEIGHT" && ($type == "cart" || $type == "wish" || $type == "order")) {
						// get properties from db
						if (isset($db_properties[$property_id])) {
							$property_values = $db_properties[$property_id];
						}
					} else {
						// get properties from form
						if ($property_type == "CHECKBOXLIST") {
							$property_total = get_param("property_total".$sc_index."_".$property_id);
							for ($i = 1; $i <= $property_total; $i++) {
								$property_value = get_param("property".$sc_index."_" . $property_id . "_" . $i);
								if ($property_value) { $property_values[] = $property_value; }
							}
						} else if ($property_type == "TEXTBOXLIST") {
							$property_total = get_param("property_total".$sc_index."_" . $property_id);
							for ($i = 1; $i <= $property_total; $i++) {
								$property_value = get_param("property".$sc_index."_" . $property_id . "_" . $i);
								if ($property_value) { 
									$value_id = get_param("property_value".$sc_index."_" . $property_id . "_" . $i);
									$property_values[] = $value_id; 
									$values_text[$value_id] = $property_value; 
								}
							}
						} else if ($property_type == "WIDTH_HEIGHT") {
							$property_price_type = 1; // always use SINGLE TOTAL PRICE for width and height control
							$property_width = ""; $property_height = "";
							if ($type == "cart" || $type == "wish" || $type == "order") {
								// get properties from db
								if (isset($db_properties[$property_id])) {
									$property_width = $db_properties[$property_id]["width"];
									$property_height = $db_properties[$property_id]["height"]; 
								}
							} else {
								$property_width = get_param("property_width".$sc_index."_" . $property_id);
								$property_height = get_param("property_height".$sc_index."_" . $property_id);
							}
	  
							if (strlen($property_width) && strlen($property_height)) {
								property_sizes($property_id, $property_width, $property_height, $size_price, $min_width, $max_width, $min_height, $max_height, $prices);
								if ($property_width < $min_width || $property_width > $max_width) {
									$sc_errors = "Please enter a width between ".$min_width." and ".$max_width.".";
								}
								if ($property_height < $min_height || $property_height > $max_height) {
									$sc_errors = "Please enter a height between ".$min_height." and ".$max_height.".";
								}
								$additional_price += $size_price;
								if (!$sc_errors) {
									$property_values["width"] = $property_width; 
									$values_text["width"] = $property_width; 
									$property_values["height"] = $property_height; 
									$values_text["height"] = $property_height; 
								}
							}
						} else {
							$property_value = get_param("property".$sc_index."_".$property_id);
							if (strlen($property_value)) {
								if ($property_type == "IMAGEUPLOAD" && !preg_match("/^http\:\/\//", $property_value)) {
									$property_value = $settings["site_url"] . "images/options/" . $property_value;
								}
								$property_values[] = $property_value;
								if ($property_type == "TEXTBOX" || $property_type == "TEXTAREA") {
									$values_text[$property_value] = $property_value; 
								}
							}
						}
					}
					$control_price = calculate_control_price($property_values, $values_text, $property_price_type, $additional_price, $free_price_type, $free_price_amount);
	  
					$controls_price += $control_price;
					// add all properties for further checks for their different use
					$properties_info[$property_id] = array(
						"USAGE_TYPE" => $usage_type, "CONTROL" => $property_type, "TYPE" => $property_type_id, 
						"NAME" => $property_name, "VALUES" => $property_values, "VALUES_INFO" => $property_values, 
						"REQUIRED" => $property_required,
						"PARENT_PROPERTY_ID" => $parent_property_id, "PARENT_VALUE_ID" => $parent_value_id, 
						"TEXT" => $values_text, "CONTROL_PRICE" => $control_price, "ORDER" => $property_order,
						"QUANTITY_ACTION" => $property_quantity_action, "OPTION_STEP" => $option_step,
						"BUYING" => 0, "PRICE" => 0, "PERCENTAGE" => 0, 
						"PRICE_TYPE" => $property_price_type, "ADDITIONAL_PRICE" => $additional_price,
						"FREE_PRICE_TYPE" => $free_price_type, "FREE_PRICE_AMOUNT" => $free_price_amount,
						"PERCENTAGE_PRICE_TYPE" => $percentage_price_type, "PERCENTAGE_PROPERTY_ID" => $percentage_property_id,
					);
				}
			}
	  
			// check components
			foreach ($components as $property_id => $component_values) {
				$component = $component_values[0];
				if ($component["usage_type"] == 2 || $component["usage_type"] == 3) {
					$sql  = " SELECT item_id FROM " . $table_prefix . "items_properties_assigned ";
					$sql .= " WHERE item_id=" . $db->tosql($sc_item_id, INTEGER);
					$sql .= " AND property_id=" . $db->tosql($property_id, INTEGER);
					$db->query($sql);
					if (!$db->next_record()) {
						// remove component if it wasn't assigned to product
						unset($components[$property_id]);
						continue;
					}
				}			
				/*if (isset($component["sub_item_id"]) && $component["sub_item_id"]) {
					if (!VA_Products::check_permissions($component["sub_item_id"], VIEW_ITEMS_PERM)) {
						unset($components[$property_id]);
						continue;
					}
				}*/
			}

			// check usage and required settings for product options and populate $product_properties and $component_values arrays
			if (isset($properties_info) && count($properties_info)) {
				// properties_info cycle
				foreach ($properties_info as $property_id => $property_info) {
					$property_exists = true;			
					if ($property_info["USAGE_TYPE"] == 2 || $property_info["USAGE_TYPE"] == 3) {
						// check if option should be assigned to product first
						$sql  = " SELECT item_id FROM " . $table_prefix . "items_properties_assigned ";
						$sql .= " WHERE item_id=" . $db->tosql($sc_item_id, INTEGER);
						$sql .= " AND property_id=" . $db->tosql($property_id, INTEGER);
						$db->query($sql);
						if (!$db->next_record()) {
							// remove option if it wasn't assigned to product
							$property_exists = false;
							unset($properties_info[$property_id]);	
						}
					}
					$parent_property_id = $property_info["PARENT_PROPERTY_ID"];
					$parent_value_id = $property_info["PARENT_VALUE_ID"];
					if ($property_exists && $parent_property_id) {
						$values = array();
						if (isset($properties_info[$parent_property_id]["VALUES"])) {
							$values = $properties_info[$parent_property_id]["VALUES"];
						}
						if (!isset($properties_info[$parent_property_id]) || sizeof($values) == 0) {
							$property_exists = false;
							unset($properties_info[$property_id]);	
						} else if ($parent_value_id && !in_array($parent_value_id, $values)) {
							$property_exists = false;
							unset($properties_info[$property_id]);	
						}
					}
	    
					if ($property_exists) {
						$properties_exists = true;
						$property_values = $property_info["VALUES"];
						$property_required = $property_info["REQUIRED"];
						if (sizeof($property_values) > 0) {
							$properties[$property_id] = $property_values;
							if ($property_info["TYPE"] == 3) {
								$components_values[$property_id] = $property_values;
							}
							$product_properties[$property_id] = $property_info;
						} else if ($property_required) {
							$properties_required = true;
							$properties_message = "";
							$property_message = str_replace("{property_name}", $property_info["NAME"], va_constant("REQUIRED_PROPERTY_MSG"));
							$property_message = str_replace("{product_name}", get_translation($item_name), $property_message);
							$properties_message .= $property_message;
							//$sc_errors .= $property_error . "<br>"; // DELETE
							//$options_errors .= $property_error . "<br>"; // DELETE
						}
					}
				}
				// end of properties_info cycle
			}

		} // end: prepare product properties

		// calculate summary stock levels for options recently selected
		$options_levels = array();
		foreach ($shopping_cart as $cart_id => $cart_info) {
			$item_id = $cart_info["ITEM_ID"];
			$item_quantity = $cart_info["QUANTITY"];
			$item_properties = $cart_info["PROPERTIES"];
			if (!is_array($item_properties)) { $item_properties = array(); }
			if (count($item_properties)) {
				foreach ($item_properties as $property_id => $property_values) {
					if (isset($product_properties[$property_id])) {
						$ct = $product_properties[$property_id]["CONTROL"];
						if (strtoupper($ct) == "LISTBOX"
						|| strtoupper($ct) == "RADIOBUTTON"
						|| strtoupper($ct) == "IMAGE_SELECT"
						|| strtoupper($ct) == "CHECKBOXLIST"
						|| strtoupper($ct) == "TEXTBOXLIST") {
							for ($ov = 0; $ov < sizeof($property_values); $ov++) {
								$option_value_id = $property_values[$ov];
								if (isset($options_levels[$option_value_id])) {
									$options_levels[$option_value_id] += $item_quantity;
								} else {
									$options_levels[$option_value_id] = $item_quantity;
								}
							}
						}
					}
				}
			}
		}

		// check components values for select controls like listbox/radio/checkboxes/image_select and add to global components array
		if ($cart != "QTY" && sizeof($components_values)) {
			foreach ($components_values as $property_id => $values) {
				for ($v = 0; $v < sizeof($values); $v++) {
					$item_property_id = $values[$v];
					$sql  = " SELECT ipv.sub_item_id, ipv.quantity, ipv.additional_price, ipv.trade_additional_price ";
					$sql .= " FROM " . $table_prefix . "items_properties_values ipv ";
					$sql .= " WHERE ipv.item_property_id=" . $db->tosql($item_property_id, INTEGER);
					$db->query($sql);
					if ($db->next_record()) {
						$sub_item_id = $db->f("sub_item_id");
						$sub_quantity = $db->f("quantity");
						if ($sub_quantity < 1) { $sub_quantity = 1; }
						$additional_price = doubleval($db->f($additional_price_field));
						$property_name = "";
						if (isset($product_properties[$property_id]["NAME"])) {
							$property_name = $product_properties[$property_id]["NAME"];
						}
						$components[$property_id][$item_property_id] = array(
							"type_id" => 3, "sub_item_id" => $sub_item_id, 
							"quantity" => $sub_quantity, 
							"quantity_action" => $properties_info[$property_id]["QUANTITY_ACTION"], 
							"name" => $property_name, 
							"price" => $additional_price);
					}
				}
			}
		}

		if ($sc_errors && $type != "cart" && $type != "wish" && $type != "order") {
			// error occurred can't continue process
			return false;
		}

		// set special SHIPPING or COMPARE status for items which were added to estimate shipping cost or comparing items
		if ($cart == "SHIPPING" || $cart == "COMPARE") {
			$item_status = $cart;
		}


		// begin calculate buying, price and percentage values
		$properties_price = 0; $properties_percentage = 0;
		if (count($properties)) {
			//foreach ($properties as $property_id => $property_values) {
			foreach ($product_properties as $property_id => $property) {
				$property_values = $property["VALUES"];
				$control_type = strtoupper($property["CONTROL"]);
				if ( $control_type == "LISTBOX"
					|| $control_type == "RADIOBUTTON"
					|| $control_type == "CHECKBOXLIST"
					|| $control_type == "IMAGE_SELECT"
					|| $control_type == "TEXTBOXLIST") {
					$values_info = array();
					for ($pv = 0; $pv < sizeof($property_values); $pv++) {
						if ($product_properties[$property_id]["TYPE"] == 3) {

						} else {
							$item_property_id = $property_values[$pv];
							if (isset($options_levels[$item_property_id])) {
								$option_quantity = $options_levels[$item_property_id] + $sc_quantity;
							} else {
								$option_quantity = $sc_quantity;
							}
							$sql  = " SELECT item_property_id, buying_price, additional_price, trade_additional_price, percentage_price, additional_weight, ";
							$sql .= " property_value, stock_level, use_stock_level, hide_out_of_stock ";
							$sql .= " FROM " . $table_prefix . "items_properties_values ipv ";
							$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
							$sql .= " AND item_property_id=" . $db->tosql($property_values[$pv], INTEGER);
							$sql .= " ORDER BY item_property_id ";
							$db->query($sql);
							if ($db->next_record()) {
								$item_property_id = $db->f("item_property_id");
								$additional_price = doubleval($db->f($additional_price_field));
								$percentage_price = doubleval($db->f("percentage_price"));
								$buying_price = doubleval($db->f("buying_price"));
								$properties_price += $additional_price;
								$properties_percentage += $percentage_price;
								$properties_buying += $buying_price;
								$option_value = get_translation($db->f("property_value"));
								$option_stock_level = $db->f("stock_level");
								$option_use_stock = $db->f("use_stock_level");
								$option_hide_stock = $db->f("hide_out_of_stock");
								// populate properties array with prices information
								$product_properties[$property_id]["BUYING"] += $buying_price;
								$product_properties[$property_id]["PRICE"] += $additional_price;
								$product_properties[$property_id]["PERCENTAGE"] += $percentage_price;

								$values_info[$item_property_id] = array(
									"ID" => $item_property_id, 
									"PRICE" => $additional_price, 
									"PERCENTAGE" => $percentage_price, 
									"BUYING" => $buying_price, 
									"DESC" => $option_value, 
								);
							}
							// check stock levels only if product added to shopping cart
							if (($cart == "ADD" || $cart == "QTY") && $option_use_stock && $option_stock_level < $option_quantity && $option_hide_stock) {
								if ($option_stock_level > 0) {
									$limit_product = get_translation($item_name) . " (" . $product_properties[$property_id]["NAME"] . ": " . $option_value . ")";
									$limit_error = str_replace("{limit_quantity}", $option_stock_level, va_constant("PRODUCT_LIMIT_MSG"));
									$limit_error = str_replace("{product_name}", $limit_product, $limit_error);
									$sc_errors .= $limit_error . "<br>";
								} else {
									$sc_errors .= va_constant("PRODUCT_OUT_STOCK_MSG") . "<br>";
								}
								if ($type != "cart" || $type != "wish" && $type != "order") {
									return false;
								}
							}
						}
					} //
					// update values info for  "LISTBOX" "RADIOBUTTON" "CHECKBOXLIST" "TEXTBOXLIST" "IMAGE_SELECT"
          $product_properties[$property_id]["VALUES_INFO"] = $values_info;
				}
			}
		}
		// end calculate buying, price and percentage values

		// check if the item already in the cart than increase quantity
		$in_cart = false;
		if ($cart == "QTY") {
			$in_cart_id = $sc_index;
			$in_cart = true;
		} else if (($cart == "ADD" || $cart == "COMPARE") && $type != "options") {
			foreach ($shopping_cart as $in_cart_id => $item)
			{
				if ($item["ITEM_ID"] == $sc_item_id) {
					$item_properties = $item["PROPERTIES"];
					if (!is_array($item_properties)) { $item_properties = array(); }
					$item_properties_info = $item["PROPERTIES_INFO"];
					if (!is_array($item_properties_info)) { $item_properties_info = array(); }
					if ($item_properties_info == $product_properties) {
						// compare if new product and product in the cart has the same options values
						$in_cart = true;
						break;
					}
				}
			}
		}

		if ($in_cart) {
			$new_quantity = $shopping_cart[$in_cart_id]["QUANTITY"] + $sc_quantity;
		} else {
			$new_quantity = $sc_quantity;
		}

		// check components prices and stock levels
		if (sizeof($components) > 0) {
			foreach ($components as $property_id => $component_values) {
				foreach ($component_values as $item_property_id => $component) {
					$sub_type = $component["type_id"];
					$sub_item_id = $component["sub_item_id"];
					$sub_quantity = $component["quantity"];
					$sub_quantity_action = $component["quantity_action"];
					$sub_name = $component["name"];
					if ($sub_quantity < 1) { $sub_quantity = 1; }
					$component_price = $component["price"];
					$add_component_quantity = 0;
					if ($sub_quantity_action == 2) {
						if (!$in_cart) { $add_component_quantity = $sub_quantity; }
						$new_component_quantity = $sub_quantity;
					} else {
						$add_component_quantity = $sc_quantity * $sub_quantity;
						$new_component_quantity = $new_quantity * $sub_quantity;
					}


					if (isset($stock_levels[$sub_item_id])) {
						$total_component_quantity = $stock_levels[$sub_item_id] + $add_component_quantity;
					} else {
						$total_component_quantity = $add_component_quantity;
					}

					$sql  = " SELECT i.item_type_id, i.item_name, i.buying_price, i." . $price_field . ", i.is_sales, i." . $sales_field . ", i.tax_id, i.tax_free, ";
					$sql .= " i.stock_level, i.use_stock_level, i.hide_out_of_stock, i.disable_out_of_stock ";
					$sql .= " FROM " . $table_prefix . "items i ";
					$sql .= " WHERE i.item_id=" . $db->tosql($sub_item_id, INTEGER);
					$db->query($sql);
					if ($db->next_record()) {
						$sub_item_type_id = $db->f("item_type_id");
						$sub_tax_id = $db->f("tax_id");
						$sub_tax_free = $db->f("tax_free");
						$sub_stock_level = $db->f("stock_level");
						$sub_use_stock = $db->f("use_stock_level");
						$sub_hide_stock = $db->f("hide_out_of_stock");
						$sub_disable_stock = $db->f("disable_out_of_stock");
						$sub_item_name = get_translation($db->f("item_name"));
						// check stock levels only if product added to shopping cart
						if (($cart == "ADD" || $cart == "QTY") && $sub_use_stock && $sub_stock_level < $total_component_quantity && ($sub_hide_stock || $sub_disable_stock)) {
							if ($sub_stock_level > 0) {
								$limit_product = get_translation($item_name);
								if ($sub_type == 2) {
									$limit_product .= " (".$sub_name.")";
								} else {
									$limit_product .= " (".$sub_name.": ".$sub_item_name.")";
								}
								$limit_error = str_replace("{limit_quantity}", $sub_stock_level, va_constant("PRODUCT_LIMIT_MSG"));
								$limit_error = str_replace("{product_name}", $limit_product, $limit_error);
								$sc_errors .= $limit_error . "<br>";
							} else {
								$sc_errors .= va_constant("PRODUCT_OUT_STOCK_MSG");
								if ($sub_type == 2) {
									$sc_errors .= " (".$sub_name.")";
								} else {
									$sc_errors .= " (".$sub_name.": ".$sub_item_name.")";
								}
								$sc_errors .= "<br>";
							}
							if ($type != "cart" && $type != "wish" && $type != "order") {
								return false;
							}
						}
						$components[$property_id][$item_property_id]["item_type_id"] = $sub_item_type_id;
						$components[$property_id][$item_property_id]["tax_id"] = $sub_tax_id;
						$components[$property_id][$item_property_id]["tax_free"] = $sub_tax_free;
						if (!strlen($component_price)) {
							$sub_price = $db->f($price_field);
							$sub_is_sales = $db->f("is_sales");
							$sub_buying = $db->f("buying_price");
							$sub_sales = $db->f($sales_field);
							$coupons_ids = ""; $coupons_discount = ""; $coupons_applied = array();
							get_sales_price($sub_price, $sub_is_sales, $sub_sales, $sub_item_id, $sub_item_type_id, $coupons_ids, $coupons_discount, $coupons_applied);
							if ($sub_is_sales && $sub_sales > 0) {
								$sub_price = $sub_sales;
							}
							
							$sub_user_price  = ""; $sub_user_action = 1;
							$volume_price = get_quantity_price($sub_item_id, $new_component_quantity);
							if (sizeof($volume_price)) {
								$sub_user_price  = $volume_price[0];
								$sub_user_action = $volume_price[2];
							}				
				
							$components[$property_id][$item_property_id]["base_price"] = $sub_price;
							$components[$property_id][$item_property_id]["buying"] = $sub_buying;
							$components[$property_id][$item_property_id]["user_price"] = $sub_user_price;
							$components[$property_id][$item_property_id]["user_price_action"] = $sub_user_action;
							if ($in_cart) {
								$shopping_cart[$in_cart_id]["COMPONENTS"][$property_id][$item_property_id] = $components[$property_id][$item_property_id];
							}
							$sub_prices = get_product_price($sub_item_id, $sub_price, $sub_buying, 0, 0, $sub_user_price, $sub_user_action, $discount_type, $discount_amount);
							$component_price = $sub_prices["base"];
						}

						if ($sub_quantity_action == 2) {
							$components_price += ($component_price * $sub_quantity / $new_quantity); 
						} else {
							$components_price += ($component_price * $sub_quantity);
						}

					} else { // there is no such subcomponent
						$sc_errors .= "Component is missing.<br>";
						if ($type != "cart" && $type != "wish" && $type != "order") {
							return false;
						}
					}
				}
			}
		}

		$cart_action = "";
		if ($in_cart && !$is_price_edit && !$sc_errors)
		{
			$cart_action = "update";
			$shopping_cart[$in_cart_id]["QUANTITY"] += $sc_quantity;
			$quantity_price = get_quantity_price($shopping_cart[$in_cart_id]["ITEM_ID"], $shopping_cart[$in_cart_id]["QUANTITY"]);
			if (sizeof($quantity_price) > 0) {
				$shopping_cart[$in_cart_id]["PRICE"] = $quantity_price[0];
				$shopping_cart[$in_cart_id]["PROPERTIES_DISCOUNT"] = $quantity_price[1];
				$shopping_cart[$in_cart_id]["DISCOUNT"] = $quantity_price[2];
			}
			$shopping_cart[$in_cart_id]["COMPONENTS_PRICE"] = $components_price;
			// begin: update database with new values
			//cart_update($shopping_cart, $in_cart_id, false);
			// end: update database with new values

			$item_added = true;
		} else {
			if ($type == "options") {
				$cart_action = "options";
				// get cart_id to update the cart
				$update_cart_id = get_param("cart_id");
				// remove options for all following steps if they were added before
				$options_step = 1; // now only one step available
				$all_properties = $shopping_cart[$update_cart_id]["PROPERTIES"];
				if (!is_array($all_properties)) { $all_properties = array(); }
				$all_properties_info = $shopping_cart[$update_cart_id]["PROPERTIES_INFO"];
				if (!is_array($all_properties_info)) { $all_properties_info = array(); }
				if (count($all_properties)) {
					foreach ($all_properties_info as $property_id => $property_info) {
						if ($property_info["OPTION_STEP"] >= $options_step) {
							unset($all_properties[$property_id]);
							unset($all_properties_info[$property_id]);
						}
					}
				}

				if (count($properties)) {
					foreach ($properties as $property_id => $property_values) {
						$all_properties[$property_id] = $property_values;
					}
					foreach ($product_properties as $property_id => $property_info) {
						$all_properties_info[$property_id] = $property_info;
					}
				}

				$shopping_cart[$update_cart_id]["PROPERTIES"] = $all_properties;
				$shopping_cart[$update_cart_id]["PROPERTIES_INFO"] = $all_properties_info;
				$shopping_cart[$update_cart_id]["PROPERTIES_EXISTS"] = $properties_exists;
				$shopping_cart[$update_cart_id]["PROPERTIES_REQUIRED"] = $properties_required;
				$shopping_cart[$update_cart_id]["PROPERTIES_MESSAGE"] = $properties_message;
				// update components
				$shopping_cart[$update_cart_id]["COMPONENTS"] = $components;
				// recalculate options totals
				$shopping_cart[$update_cart_id]["PROPERTIES_PRICE"] = 0;
				$shopping_cart[$update_cart_id]["PROPERTIES_PERCENTAGE"] = 0;
				$shopping_cart[$update_cart_id]["PROPERTIES_BUYING"] = 0;
				if (count($all_properties)) {
					foreach ($all_properties_info as $property_id => $property_info) {
						$control_price = 0;
						if (isset($property_info["FREE_PRICE_TYPE"])) {
							$control_price = calculate_control_price($property_info["VALUES"], $property_info["TEXT"], $property_info["PRICE_TYPE"], 
								$property_info["ADDITIONAL_PRICE"], $property_info["FREE_PRICE_TYPE"], $property_info["FREE_PRICE_AMOUNT"]);
						} 
						$shopping_cart[$update_cart_id]["PROPERTIES_PRICE"] += $property_info["PRICE"] + $control_price;
						$shopping_cart[$update_cart_id]["PROPERTIES_PERCENTAGE"] += $property_info["PERCENTAGE"];
						$shopping_cart[$update_cart_id]["PROPERTIES_BUYING"] += $property_info["BUYING"];
					}
				}
				$shopping_cart[$update_cart_id]["COMPONENTS_PRICE"] = $components_price;

				// begin: update cart database with new item properties 
				//cart_update($shopping_cart, $update_cart_id, true);
				// end: update cart database with new item properties 
			} else {
				$cart_action = "add";
				if (!$is_price_edit) {
					$quantity_price = get_quantity_price($sc_item_id, $sc_quantity);
					if (sizeof($quantity_price) > 0) {
						$price = $quantity_price[0];
						$properties_discount = $quantity_price[1];
						$discount_applicable = $quantity_price[2];
					}
				}
				$new_price = round($price+$properties_price+$controls_price+$components_price,2);
				$old_price = round($sc_price,2);
				if (($type == "cart" || $type == "wish" || $type == "order") && $new_price != $old_price) {
					if ($new_price < $old_price) {
						$sc_notice .= va_constant("PROD_PRICE_CHANGED_MSG").": ".currency_format($new_price-$old_price) . "<br />";
					} else {
						$sc_notice .= va_constant("PROD_PRICE_CHANGED_MSG").": +".currency_format($new_price-$old_price) . "<br />";
					}
				}
				// prepare cart item array and save to the session
				$item = array (
					"ITEM_ID"	=> intval($sc_item_id),
					"ITEM_TYPE_ID"	=> $item_type_id,
					"CART_ITEM_ID"	=> $cart_item_id,
					"WISH_ITEM_ID"	=> $wish_item_id,
					"ORDER_ITEM_ID"	=> $order_item_id,
					"SAVED_TYPE_ID" => get_param("saved_type_id"),
					"ITEM_NAME" => $item_name,
					"STATUS" => $item_status,
					"ERROR" => $sc_errors,
					"PROPERTIES"	=> $properties,
					"PROPERTIES_INFO"	=> $product_properties,
					"PROPERTIES_PRICE"	=> ($properties_price + $controls_price),
					"PROPERTIES_PERCENTAGE"	=> $properties_percentage,
					"PROPERTIES_BUYING"	=> $properties_buying,
					"PROPERTIES_DISCOUNT" => $properties_discount,
					"PROPERTIES_EXISTS" => $properties_exists,
					"PROPERTIES_REQUIRED" => $properties_required,
					"PROPERTIES_MESSAGE" => $properties_message,
					"COMPONENTS" => $components,
					"COMPONENTS_PRICE" => $components_price,
					"QUANTITY"	=> $sc_quantity, // only one item can be placed
					"MAX_QTY"	=> $max_quantity, // how much items could be added 
					"TAX_ID" => $tax_id,
					"TAX_FREE" => $tax_free,
					"DISCOUNT" => $discount_applicable,
					"BUYING_PRICE" => $buying_price,
					"PRICE_EDIT"	=> $is_price_edit,
					"PRICE"	=> $price,
					"CC_PRICE"	=> $cc_price,
					"NOTICE" => $sc_notice
				);
				//-- add to cart
				$shopping_cart[] = $item;
				end($shopping_cart);
				$new_cart_id = key($shopping_cart);
				if ($cart == "WISHLIST") {
					//add_to_saved_items($shopping_cart, $new_cart_id, 0, true);
				}
			}
			$item_added = true;
		}

		// save session
		if ($cart == "SHIPPING") {                                            
			set_session("shipping_cart", $shopping_cart);
		} else if ($cart == "COMPARE") {                                            
			set_session("compare_cart", $shopping_cart);
		} else if ($cart == "WISHLIST") {
			set_session("wishlist_cart", $shopping_cart);
			db_cart_update("wishlist", $new_cart_id);
		} else {
			set_session("shopping_cart", $shopping_cart);
			if ($cart_action == "qty" || $cart_action == "update") {
				db_cart_update("update", $in_cart_id);
			} else if ($cart_action == "options") {
				db_cart_update("options", $update_cart_id);
			} else if ($cart_action == "add") {
				db_cart_update("add", $new_cart_id);
			}
		}

		// return success message
		if ($cart == "WISHLIST") {
			$sc_message .= str_replace("{product_name}", get_translation($item_name), va_constant("ADDED_TO_WISHLIST_MSG"))."<br>";
		} else if ($cart == "COMPARE") {
			$sc_message .= str_replace("{product_name}", get_translation($item_name), va_constant("ADDED_TO_COMPARE_MSG"))."<br>";
		} else {
			$sc_message .= str_replace("{product_name}", get_translation($item_name), va_constant("ADDED_PRODUCT_MSG"))."<br>";
		}
		return $item_added;
	}


	function cart_retrieve($retrieve_type, $cart_id = "")
	{
		global $db, $t, $table_prefix, $site_id, $settings, $eol, $currency;

		// check user and cookie data
		$user_id = get_session("session_user_id");
		$va_cart = get_cookie("_va_cart");
		if (!$va_cart) { $va_cart = get_cookie("_va_track"); } // used before to save cart information
		$va_cart = json_decode($va_cart, true);
		$cookie_cart_id = get_setting_value($va_cart, "cartid");
		$cookie_sess_id = get_setting_value($va_cart, "sessid");
		$db_cart_id = get_session("db_cart_id");
		if ($retrieve_type == "init") {
			if (!$user_id && $cookie_cart_id) {
				$sql = " SELECT cart_name FROM ".$table_prefix."saved_carts ";
				$sql.= " WHERE cart_type=0 AND (user_id IS NULL or user_id=0) ";
				$sql.= " AND cart_id=" . $db->tosql($cookie_cart_id, INTEGER);
				$cart_name = get_db_value($sql);
				if ($cart_name && strtolower($cart_name) == strtolower($cookie_sess_id)) {
					$cart_id = $cookie_cart_id;
					$db_cart_id = $cookie_cart_id;
					// set new session and date for cart
					set_session("db_cart_id", $cart_id);
					$sql = " UPDATE ".$table_prefix."saved_carts ";
					$sql.= " SET cart_name=".$db->tosql(session_id(), TEXT);
					$sql.= " , cart_updated=".$db->tosql(va_time(), DATETIME);
					$sql.= " WHERE cart_id=".$db->tosql($cart_id, INTEGER);
					$db->query($sql);
					va_cart_update(array("cartid" => $cart_id, "sessid" => session_id()));
				}
			}
			if ($cookie_cart_id && !$cart_id) {
				// clear cart information from cookie as it didn'n matched
				va_cart_update(array("cartid" => "", "sessid" => ""));
			}
		} else if ($retrieve_type == "login") {
			if ($user_id) {
				$sql = " SELECT cart_id FROM ".$table_prefix."saved_carts ";
				$sql.= " WHERE cart_type=0 ";
				$sql.= " AND user_id=" . $db->tosql($user_id, INTEGER);
				$cart_id = get_db_value($sql);
			}
			if ($db_cart_id && $cart_id && $db_cart_id != $cart_id) {
				// delete current cart and move all items to user cart
				$sql  = " DELETE FROM " . $table_prefix . "saved_carts ";
				$sql .= " WHERE cart_id=" . $db->tosql($db_cart_id, INTEGER);
				$db->query($sql);
				$sql  = " UPDATE " . $table_prefix . "saved_items ";
				$sql .= " SET cart_id=" . $db->tosql($cart_id, INTEGER);
				$sql .= " , user_id=" . $db->tosql($user_id, INTEGER, false, false);
				$sql .= " WHERE cart_id=" . $db->tosql($db_cart_id, INTEGER);
				$db->query($sql);
				// clear cart information from cookie as it was saved directly in user cart
				va_cart_update(array("cartid" => "", "sessid" => ""));
			}
			if ($db_cart_id && !$cart_id) {
				// save cart for user
				$cart_id = $db_cart_id;
				// update user_id field for saved_items table
				$sql  = " UPDATE " . $table_prefix . "saved_items ";
				$sql .= " SET user_id=" . $db->tosql($user_id, INTEGER, false, false); 
				$sql .= " WHERE cart_id=" . $db->tosql($db_cart_id, INTEGER);
				$db->query($sql);
			}
			if ($cart_id) {
				// after login re-check user country data
				user_country($country_id, $country_code);
				// set user_id, session and date it was updated for cart
				$db_cart_id = $cart_id;
				set_session("db_cart_id", $cart_id);
				$sql = " UPDATE ".$table_prefix."saved_carts ";
				$sql.= " SET cart_name=".$db->tosql(session_id(), TEXT);
				$sql.= " , cart_updated=".$db->tosql(va_time(), DATETIME);
				$sql.= " , user_id=".$db->tosql($user_id, INTEGER);
				$sql.= " , country_id=" . $db->tosql($country_id, INTEGER);
				$sql.= " , country_code=" . $db->tosql($country_code, TEXT);
				$sql.= " WHERE cart_id=".$db->tosql($cart_id, INTEGER);
				$db->query($sql);
			}
			// check if we probably need to save some shopping cart items in database
			db_cart_update("login");
		} else if ($retrieve_type == "add" || $retrieve_type == "load" || $retrieve_type == "retrieve") {
			if (!$db_cart_id) {
				// check ip and country data for user
				$user_ip = get_ip();
				user_country($country_id, $country_code);

				$sql = " INSERT INTO ".$table_prefix."saved_carts (";
				$sql .= "site_id, user_id, cart_type, cart_name, cart_total, cart_added, cart_updated, country_id, country_code, user_ip) VALUES (";
				$sql .= $db->tosql($site_id, INTEGER).", ";
				$sql .= $db->tosql($user_id, INTEGER).", 0,";
				$sql .= $db->tosql(session_id(), TEXT).",0,";
				$sql .= $db->tosql(va_time(), DATETIME).", ";
				$sql .= $db->tosql(va_time(), DATETIME).", ";
				$sql .= $db->tosql($country_id, INTEGER).", ";
				$sql .= $db->tosql($country_code, TEXT).", ";
				$sql .= $db->tosql($user_ip, TEXT).") ";
				$db->query($sql);
				$db_cart_id = $db->last_insert_id();
				set_session("db_cart_id", $db_cart_id);
				// save cart id in cookies if user unregistered
				if (!$user_id) {
					va_cart_update(array("cartid" => $db_cart_id, "sessid" => session_id()));
				}
			}
		}
		
		if ($cart_id) {

			$cart_item_type = ($retrieve_type == "load" || $retrieve_type == "retrieve") ? "wish" : "cart";
			// retrieve cart
			$sql  = " SELECT si.*,i.sites_all,s.site_id AS item_site_id ";
			$sql .= " FROM ((" . $table_prefix . "saved_items si ";
			$sql .= " LEFT JOIN " . $table_prefix . "items i ON i.item_id=si.item_id) ";
			$sql .= " LEFT JOIN " . $table_prefix . "items_sites s ON (i.item_id=s.item_id AND s.site_id=".$db->tosql($site_id, INTEGER).")) ";
			$sql .= " WHERE cart_id=" . $db->tosql($cart_id, INTEGER);
			$sql .= " ORDER BY cart_item_id ";
			$db->query($sql);
			if ($db->next_record()) {
				do {
					$cart_item_id = $db->f("cart_item_id");
					$cart_site_id = $db->f("site_id");
					$sites_all = $db->f("sites_all");
					$item_site_id = $db->f("item_site_id");
					if ($cart_site_id == $site_id || $sites_all || $item_site_id == $site_id) {
						$cart_items[$cart_item_id] = $db->Record;
					}
				} while ($db->next_record());

				foreach ($cart_items as $cart_item_id => $item_data) {
					$item_id = $item_data["item_id"];
					$item_name = $item_data["item_name"];
					$quantity = $item_data["quantity"];
					$price = $item_data["price"];
					$sc_errors = ""; $sc_message = "";
					$item_added = add_to_cart($item_id, "", $price, $quantity, $cart_item_type, "ADD", $new_cart_id, $sc_errors, $sc_message, $cart_item_id, $item_name);
				}
			}
		}
		return $db_cart_id;
	}

	function check_user_cart($check_param = "")
	{
		$user_id = get_session("session_user_id");
		$db_cart_id = get_session("db_cart_id");
		$session_start = get_session("session_start_ts");
		$current_time = va_timestamp();
		$check_value = ($check_param) ? get_param($check_param) : 1;
		if ($user_id || $db_cart_id) {
			// if cart was already created or it's registered user then we assume that's a user cart
			$is_user_cart = true;
		} else {
			// for guests check if user was some time on the site before he add something to cart 
			$is_user_cart = (($current_time - $session_start) > 5 && !check_bot() && $check_value);
		}
		return $is_user_cart;
	}


	function db_cart_update($cart_action, $cart_item_id = "")
	{
		global $db, $table_prefix, $site_id;
		// get and prepare necesary cart vars
		$shopping_cart = get_session("shopping_cart");
		$db_cart_id = get_session("db_cart_id");
		$user_id = get_session("session_user_id");
		$saving_items = array();
		$cart_update = false; // if we need to update total values for the cart  
		$cart_clear = false; // if we delete the whole cart and all it products
		$add_items = false; $update_items = false; $add_properties = false; $update_properies = false; $delete_properties = false;
		// check cart action
		if ($cart_action == "add") {
			$cart_update = true; $add_items = true; $add_properties = true; 
			if (!$db_cart_id) { 
				// create a new cart only for real users
				if (check_user_cart()) {
					$db_cart_id = cart_retrieve("add"); // add a new cart to database
					// add all shopping cart items as there wasn't db_cart_id which mean we can't save them before
					$saving_items = $shopping_cart;
				}
			} else {
				// if cart available just save last item
				if (isset($shopping_cart[$cart_item_id])) {
					$saving_items = array($cart_item_id => $shopping_cart[$cart_item_id]);
				}
			}
		} else if ($cart_action == "login") {
			$cart_update = true; $add_items = true; $add_properties = true; 
			$saving_items = $shopping_cart;
		} else if ($cart_action == "wishlist") {
			$add_items = true; $add_properties = true; 
			$db_cart_id = 0; // wishlist items saved with zero cart_id
			$saving_items = get_session("wishlist_cart");
		} else if ($cart_action == "options") {
			if (isset($shopping_cart[$cart_item_id])) {
				$cart_update = true; $update_items = true; $update_properies = true; 
				$saving_items = array($cart_item_id => $shopping_cart[$cart_item_id]);
			}
		} else if ($cart_action == "update") {
			if (isset($shopping_cart[$cart_item_id])) {
				$cart_update = true; $update_items = true; 
				$saving_items = array($cart_item_id => $shopping_cart[$cart_item_id]);
			}
		} else if ($cart_action == "remove") {
			$cart_update = true; 
			if (strlen($cart_item_id) && isset($shopping_cart[$cart_item_id])) {
				$db_cart_item_id = get_setting_value($shopping_cart[$cart_item_id], "CART_ITEM_ID");
				$sql = " DELETE FROM ".$table_prefix."saved_items ";
				$sql.= " WHERE cart_item_id=".$db->tosql($db_cart_item_id, INTEGER);
				$db->query($sql);
				$sql = " DELETE FROM ".$table_prefix."saved_items_properties ";
				$sql.= " WHERE cart_item_id=".$db->tosql($db_cart_item_id, INTEGER);
				$db->query($sql);
			}
		} else if ($cart_action == "clear") {
			$cart_clear = true;
		}
		if (strlen($db_cart_id) && is_array($saving_items) && count($saving_items) > 0) {
			foreach ($saving_items as $si_id => $saving_item) {
				$db_cart_item_id = get_setting_value($saving_item, "CART_ITEM_ID");
				$saved_type_id = $saving_item["SAVED_TYPE_ID"]; // saved type for wishlist
				$price = $saving_item["PRICE"] + $saving_item["PROPERTIES_PRICE"] + $saving_item["COMPONENTS_PRICE"];
				$quantity = $saving_item["QUANTITY"];
				// begin: save shopping cart item in database
				if (!$db_cart_item_id) {
					$delete_properties = false; // it's a new products so we don't need to delete old properties 
					if ($add_items) {
						$sql = " INSERT INTO ".$table_prefix."saved_items (";
						$sql .= "site_id, item_id, cart_id, user_id, type_id, item_name, quantity, quantity_bought, price, date_added, date_updated) VALUES (";
						$sql .= $db->tosql($site_id, INTEGER).", ";
						$sql .= $db->tosql($saving_item["ITEM_ID"], INTEGER).", ";
						$sql .= $db->tosql($db_cart_id, INTEGER).", ";
						$sql .= $db->tosql($user_id, INTEGER, false, false).", ";
						$sql .= $db->tosql($saving_item["SAVED_TYPE_ID"], INTEGER, true, false) . ", ";
						$sql .= $db->tosql($saving_item["ITEM_NAME"], TEXT).", ";
						$sql .= $db->tosql($saving_item["QUANTITY"], INTEGER).",0, ";
						$sql .= $db->tosql($price, NUMBER).", ";
						$sql .= $db->tosql(va_time(), DATETIME).", ";
						$sql .= $db->tosql(va_time(), DATETIME).") ";
						$db->query($sql);
						$db_cart_item_id = $db->last_insert_id();
						$shopping_cart[$si_id]["CART_ITEM_ID"] = $db_cart_item_id;
					}
				} else {
					$delete_properties = true; // product already exists in DB so we may need to clear old properties
					if ($update_items) {
						$sql = " UPDATE ".$table_prefix."saved_items ";
						$sql.= " SET price=".$db->tosql($price, NUMBER);
						$sql.= " , quantity=".$db->tosql($quantity, INTEGER);
						$sql.= " , date_updated=".$db->tosql(va_time(), DATETIME);
						$sql.= " WHERE cart_item_id=".$db->tosql($db_cart_item_id, INTEGER);
						$db->query($sql);
					}
				}

				if ($db_cart_item_id && ($add_properties || $update_properies)) {
					if ($delete_properties && $update_properies) {
						$sql = " DELETE FROM ".$table_prefix."saved_items_properties ";
						$sql.= " WHERE cart_item_id=".$db->tosql($db_cart_item_id, INTEGER);
						$db->query($sql);
					}

					// -- save properties for product
					if ($add_properties || $update_properies) {
						$properties = $saving_item["PROPERTIES"];
						if (!is_array($properties)) { $properties = array(); }
						foreach($properties as $property_id => $property_values) {
							$sql  = " INSERT INTO ".$table_prefix."saved_items_properties ";
							$sql .= " (cart_item_id, cart_id, property_id, property_value) VALUES (";
							$sql .= $db->tosql($db_cart_item_id, INTEGER).", ";
							$sql .= $db->tosql($db_cart_id, INTEGER).", ";
							$sql .= $db->tosql($property_id, INTEGER).", ";
							$sql .= $db->tosql(json_encode($property_values), TEXT).") ";
							$db->query($sql);
						}
					} // end saving properties --
				} // end: save shopping cart item in database
			}
			if ($cart_action == "add") {
				// update shopping cart in the session with saved cart_item_id DB values
				set_session("shopping_cart", $shopping_cart);
			}
		}
		if($cart_update) {
			// check and update total values for the cart 
			$cart_quantity = 0; $cart_total = 0;
			$sql = " SELECT SUM(quantity) as cart_quantity,SUM(price*quantity) as cart_total FROM ".$table_prefix."saved_items ";
			$sql.= " WHERE cart_id=".$db->tosql($db_cart_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$cart_quantity = $db->f("cart_quantity");
				$cart_total= $db->f("cart_total");
			}
			if ($cart_quantity || $cart_total) {
				$sql = " UPDATE ".$table_prefix."saved_carts ";
				$sql.= " SET cart_updated=".$db->tosql(va_time(), DATETIME);
				$sql.= " , cart_quantity=".$db->tosql($cart_quantity, NUMBER);
				$sql.= " , cart_total=".$db->tosql($cart_total, NUMBER);
				$sql.= " WHERE cart_id=".$db->tosql($db_cart_id, INTEGER);
				$db->query($sql);
			} else {
				// nothing left in the cart can delete it
				$cart_clear = true;
			}
			// end total cart values update
		}

		if ($cart_clear) {
			$sql = " DELETE FROM ".$table_prefix."saved_items_properties ";
			$sql.= " WHERE cart_id=".$db->tosql($db_cart_id, INTEGER);
			$db->query($sql);
			$sql = " DELETE FROM ".$table_prefix."saved_items ";
			$sql.= " WHERE cart_id=".$db->tosql($db_cart_id, INTEGER);
			$db->query($sql);
			$sql = " DELETE FROM ".$table_prefix."saved_carts ";
			$sql.= " WHERE cart_id=".$db->tosql($db_cart_id, INTEGER);
			$db->query($sql);
			// clear cart information from session and cookie
			set_session("db_cart_id", "");
			va_cart_update(array("cartid" => "", "sessid" => ""));
		} 
	}

	function add_subscription($user_type_id, $subscription_id, &$subscription_name, $group_id = "")
	{
		global $db, $table_prefix;

		$subscription_added = false;

		$shopping_cart = get_session("shopping_cart");
		if (!is_array($shopping_cart)) {
			$shopping_cart = array();
		}

		foreach ($shopping_cart as $cart_id => $item) {
			$cart_subscription_group_id = isset($item["SUBSCRIPTION_GROUP_ID"]) ? $item["SUBSCRIPTION_GROUP_ID"] : "";
			$cart_subscription_type_id = isset($item["SUBSCRIPTION_TYPE_ID"]) ? $item["SUBSCRIPTION_TYPE_ID"] : "";
			$cart_subscription_id = isset($item["SUBSCRIPTION_ID"]) ? $item["SUBSCRIPTION_ID"] : "";
			if ($cart_subscription_type_id && $cart_subscription_id) {
				// remove all subscriptions related for user type
				unset($shopping_cart[$cart_id]);
			} else if ($cart_subscription_id == $subscription_id) {
				// remove subscription from the cart if it was previously added
				unset($shopping_cart[$cart_id]);
			} else if ($group_id && $cart_subscription_group_id == $group_id) {
				// remove subscription from the cart if it was previously added
				unset($shopping_cart[$cart_id]);
			}
		}

		if (!$subscription_id) {
			$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "subscriptions ";
			$sql .= " WHERE user_type_id=" . $db->tosql($user_type_id, INTEGER) . " AND is_active=1 ";
			$total_subscriptions = get_db_value($sql);
			if ($total_subscriptions == 1) {
				$sql  = " SELECT subscription_id FROM " . $table_prefix . "subscriptions ";
				$sql .= " WHERE user_type_id=" . $db->tosql($user_type_id, INTEGER) . " AND is_active=1 ";
				$subscription_id = get_db_value($sql);
			} else if ($user_type_id) {
				// redirect user to page to select subscription option
				header("Location: user_change_type.php");
				exit;
			}
		}

		$sql  = " SELECT group_id, subscription_name, subscription_fee, subscription_period, subscription_interval ";
		$sql .= " FROM " . $table_prefix . "subscriptions ";
		$sql .= " WHERE subscription_id=" . $db->tosql($subscription_id, INTEGER);
		if ($user_type_id) {
			$sql .= " AND user_type_id=" . $db->tosql($user_type_id, INTEGER);
		}
		$sql .= " AND is_active=1 ";
		$db->query($sql);
		if ($db->next_record()) {
			$group_id = $db->f("group_id");
			$is_subscription = $db->f("is_subscription");
			$subscription_fee = $db->f("subscription_fee");
			$subscription_name = $db->f("subscription_name");
			$subscription_period = $db->f("subscription_period");
			$subscription_interval = $db->f("subscription_interval");

			$item = array (
				"ITEM_ID"	=> 0,
				"CART_ITEM_ID" => "",
				"ITEM_TYPE_ID"	=> 0,
				"SUBSCRIPTION_TYPE_ID" => $user_type_id,
				"SUBSCRIPTION_GROUP_ID" => $group_id,
				"SUBSCRIPTION_ID"	=> $subscription_id,
				"ITEM_NAME" => $subscription_name,
				"PROPERTIES"	=> "", "PROPERTIES_PRICE"	=> 0, "PROPERTIES_PERCENTAGE"	=> 0,
				"PROPERTIES_BUYING"	=> 0, "PROPERTIES_DISCOUNT" => 0, 
				"PROPERTIES_EXISTS" => 0, "PROPERTIES_REQUIRED" => 0, "PROPERTIES_MESSAGE" => "",
				"TAX_ID"	=> 0,
				"TAX_FREE"	=> 0,
				"DISCOUNT"	=> 0,
				"COMPONENTS" => "",
				"QUANTITY"	=> 1,
				"PRICE_EDIT"	=> 0,
				"BUYING_PRICE"	=> 0,
				"PRICE"	=> $subscription_fee,
			);
			//-- add to cart
			$shopping_cart[] = $item;
			end($shopping_cart);
			$new_cart_id = key($shopping_cart);

			$subscription_added = true;
		}

		set_session("shopping_cart", $shopping_cart);
		return $subscription_added;
	}

	function calculate_price($price, $is_sales, $sales_price)
	{
		if ($is_sales) {
			$price = $sales_price;
		}
		return $price;
	}

	function calculate_reward_points(&$reward_type, &$reward_amount, $price, $buying_price, $conversion_rate = 1, $points_decimals = 0)
	{
		global $settings;
		if (!strlen($reward_type)) {
			$user_info = get_session("session_user_info");
			$reward_type = get_setting_value($user_info, "reward_type", "");
			if (strlen($reward_type)) {
				$reward_amount = get_setting_value($user_info, "reward_amount", "");
			} else {
				$reward_type = get_setting_value($settings, "reward_type", "");
				$reward_amount = get_setting_value($settings, "reward_amount", "");
			}
		}
		if ($reward_type == 1 || $reward_type == 3) {
			$reward_points = round(($price * $reward_amount * $conversion_rate) / 100, $points_decimals);
		} elseif ($reward_type == 2) {
			$reward_points = round($reward_amount, $points_decimals);
		} elseif ($reward_type == 4) {
			$reward_points = round((($price - $buying_price) * $reward_amount * $conversion_rate) / 100, $points_decimals);
		} else {
			$reward_points = 0;
		}

		return $reward_points;
	}

	function calculate_reward_credits(&$credit_reward_type, &$credit_reward_amount, $price, $buying_price)
	{
		global $settings;
		if (!strlen($credit_reward_type)) {
			$user_info = get_session("session_user_info");
			$credit_reward_type = get_setting_value($user_info, "credit_reward_type", "");
			if (strlen($credit_reward_type)) {
				$credit_reward_amount = get_setting_value($user_info, "credit_reward_amount", "");
			} else {
				$credit_reward_type = get_setting_value($settings, "credit_reward_type", "");
				$credit_reward_amount = get_setting_value($settings, "credit_reward_amount", "");
			}
		}
		if ($credit_reward_type == 1 || $credit_reward_type == 3) {
			$reward_credits = round(($price * $credit_reward_amount) / 100, 2);
		} elseif ($credit_reward_type == 2) {
			$reward_credits = round($credit_reward_amount, 2);
		} elseif ($credit_reward_type == 4) {
			$reward_credits = round((($price - $buying_price) * $credit_reward_amount) / 100, 2);
		} else {
			$reward_credits = 0;
		}

		return $reward_credits;
	}

	function set_buy_button($pb_id, $item_index, $internal_link = "", $external_link = "")
	{
		global $t;
		$buy_onclick = "";
		if (strlen($external_link)) {
			$t->set_var("buy_href", htmlspecialchars($external_link));
			$t->set_var("buy_onclick", "");
		} else {
			if (!$internal_link) { $internal_link = "#"; }
			$buy_onclick  = "document.products_".$pb_id.".item_index.value='".$item_index."'; ";
			$buy_onclick .= "return confirmBuy('products_".$pb_id."','".$item_index."','cart', 'add".$item_index."');";
			$t->set_var("buy_href", htmlspecialchars($internal_link));
			$t->set_var("buy_onclick", $buy_onclick);
		}
	}

	function set_quantity_control($quantity_limit, $stock_level, $control_type, $form_name, $control_index = "", $zero_quantity = false, $min_quantity = 1, $max_quantity = "", $quantity_increment = 1)
	{
		global $settings, $t;
		$quantity_control = "";
		$hidden_control = "";
		$quantity_name = "quantity";
		if (!$quantity_increment) { $quantity_increment = 1; }
		if (strlen($control_index)) { $quantity_name .= $control_index; }
		if (!$min_quantity) { $min_quantity = $quantity_increment; }
		if (!$quantity_limit || $stock_level >= $min_quantity) {
			if ($quantity_increment < 1) { $quantity_increment = 1; }
			if (strtoupper($control_type) == "LISTBOX") {
				$increment_limit = 9;
				$show_max_quantity = $min_quantity + ($quantity_increment * $increment_limit);
				if ($max_quantity > 0 && $show_max_quantity > $max_quantity) {
					$show_max_quantity = $max_quantity;
				}
				if ($quantity_limit && $show_max_quantity > $stock_level) {
					$show_max_quantity = $stock_level;
				}
				$quantity_control .= "<select name=\"".$quantity_name."\" onchange=\"changeQuantity('$form_name', '$control_index')\">";
				if ($zero_quantity) {
					$quantity_control .= "<option value=\"0\">0</option>";
				}
				for ($i = $min_quantity; $i <= $show_max_quantity; $i = $i + $quantity_increment) {
					$quantity_control .= "<option value=\"" . $i ."\">" . $i . "</option>";
				}
				$quantity_control .= "</select>";
			} elseif (strtoupper($control_type) == "TEXTBOX") {
				if ($zero_quantity) { $min_quantity = 0; }
				$quantity_control .= "<input type=\"text\" name=\"".$quantity_name."\" class=\"field\"";
				$quantity_control .= " value=\"" . $min_quantity . "\" size=\"4\" maxlength=\"6\"";
				$quantity_control .= " onchange=\"changeQuantity('$form_name', '$control_index')\" />";
			} elseif (strtoupper($control_type) == "LABEL") {
				$quantity_control .= "<input type=\"hidden\" name=\"".$quantity_name."\" value=\"" . $min_quantity . "\" />";
				$quantity_control .= $min_quantity;
			} elseif (strtoupper($control_type) == "DISABLED") {
				$hidden_control = "<input type=\"hidden\" name=\"".$quantity_name."\" value=\"0\" />";
			} else {
				$hidden_control = "<input type=\"hidden\" name=\"".$quantity_name."\" value=\"".htmlspecialchars($min_quantity)."\" />";
			}
		} else {
			$hidden_control = "<input type=\"hidden\" name=\"".$quantity_name."\" value=\"0\" />";
		}
		$t->set_var("quantity_name", $quantity_name);
		if ($quantity_control) {
			$t->set_var("quantity_control", $quantity_control);
			$t->sparse("quantity", false);
		} else {
			$t->set_var("quantity_control", $hidden_control);
			$t->set_var("quantity", $hidden_control);
		}
	}

	function get_quantity_price($item_id, $quantity)
	{
		global $db, $table_prefix, $site_id;

		$db_rsi = $db->set_rsi("s");
		$price = array();
		$discount_type = get_session("session_discount_type");
		$user_type_id  = get_session("session_user_type_id");
		
		$order_by = " ORDER BY ";
			
		$sql  = " SELECT site_id, user_type_id, price_id, price, properties_discount, discount_action FROM " . $table_prefix . "items_prices ";
		$sql .= " WHERE is_active=1 AND item_id=" . $db->tosql($item_id, INTEGER);
		$sql .= " AND min_quantity<=" . $db->tosql($quantity, INTEGER);
		$sql .= " AND max_quantity>=" . $db->tosql($quantity, INTEGER);
		
		if (isset($site_id)) {
			$sql .= " AND (site_id=0 OR site_id=" . $db->tosql($site_id, INTEGER, true, false) . ") ";
			$order_by .= " site_id DESC, ";
		} else {
			$sql .= " AND site_id=0 ";
		}
		
		if (strlen($user_type_id)) {
			$sql .= " AND (user_type_id=0 OR user_type_id=" . $db->tosql($user_type_id, INTEGER, true, false) . ") ";
			$order_by .= " user_type_id DESC, ";
		} else {
			$sql .= " AND user_type_id=0 ";
		}
		
		if ($discount_type > 0) {
			$sql .= " AND discount_action>0 ";
		}
		
		$order_by .= " price_id DESC ";
		$db->query($sql . $order_by);
		
		if ($db->next_record()) {
			$max_site_id = $db->f("site_id");
			$max_type_id = $db->f("user_type_id");
			$price[0] = $db->f("price");
			$price[1] = $db->f("properties_discount");
			$discount_action = $db->f("discount_action");
			$price[2] = ($discount_action == 1) ? 0 : 1;
		}
		if ( isset($site_id) && strlen($user_type_id) ) {
			while ($db->next_record()) {		
				if ( ($max_site_id <= $db->f("site_id")) && ($max_type_id <= $db->f("user_type_id")) ) {
					$max_site_id = $db->f("site_id");
					$max_type_id = $db->f("user_type_id");				
					$price[0] = $db->f("price");
					$price[1] = $db->f("properties_discount");
					$discount_action = $db->f("discount_action");
					$price[2] = ($discount_action == 1) ? 0 : 1;
				
				}
			}			
		}
		$db->set_rsi($db_rsi);
		return $price;
	}

	function get_sales_price(&$price, &$is_sales, &$sales_price, $item_id, $item_type_id, &$coupons_ids, &$coupons_discount, &$coupons_applied, $sales_type = "price")
	{
		global $db, $dbs, $table_prefix, $site_id, $sales_coupons;
		$db_rsi = $db->set_rsi("s");

		$coupons_ids = ""; $coupons_discount = 0; $coupons_applied = array();
		if (!$is_sales) {
			if (!is_array($sales_coupons))  {
				$sales_coupons = array();
				$current_time = va_time();
				$sql  = " SELECT c.* FROM (" . $table_prefix . "coupons c";
				// start sub query
				$sql .= " INNER JOIN (SELECT c.coupon_id FROM (" . $table_prefix . "coupons c";
				$sql .= " LEFT JOIN  " . $table_prefix . "coupons_sites s ON s.coupon_id=c.coupon_id)";
				$sql .= " WHERE is_active=1 ";
				$sql .= " AND (c.discount_type=6 OR c.discount_type=7) ";
				$sql .= " AND (c.sites_all=1 OR s.site_id=" . $db->tosql($site_id, INTEGER) . ")";
				$sql .= " AND (start_date IS NULL OR start_date<=" . $db->tosql($current_time, DATETIME) . ")";
				$sql .= " AND (expiry_date IS NULL OR expiry_date>=" . $db->tosql($current_time, DATETIME) . ")";
				$sql .= " GROUP BY c.coupon_id) cg ON cg.coupon_id=c.coupon_id) ";
				// end sub query
				$sql .= " ORDER BY c.apply_order ";
				$db->query($sql);
				while ($db->next_record()) {
					$coupon_id = $db->f("coupon_id");
					$sales_coupons[$coupon_id] = $db->Record;
				}
			}

			if (!sizeof($sales_coupons)) { $db->set_rsi($db_rsi); return; }

			$categories_ids = array();
			$sql  = " SELECT ic.category_id, c.category_path ";
			$sql .= " FROM (" . $table_prefix . "items_categories ic ";
			$sql .= " INNER JOIN " . $table_prefix . "categories c ON c.category_id=ic.category_id) ";
			$sql .= " WHERE ic.item_id=" . $db->tosql($item_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$category_path = $db->f("category_path");
				$category_path.= $db->f("category_id");
				$ids = explode(",", $category_path);
				foreach ($ids as $id) {
					if (!in_array($id, $categories_ids)) {
						$categories_ids[] = $id;
					}
				}
			}	

			// get user data 
			$user_info = get_session("session_user_info");
			$user_id = get_setting_value($user_info, "user_id", "");
			$user_type_id = get_setting_value($user_info, "user_type_id", "");
			// check sales coupons for current product
			foreach ($sales_coupons as $coupon_id => $data) {
				$coupon_code = $data["coupon_code"];
				$coupon_title = $data["coupon_title"];

				$discount_type = $data["discount_type"];
				$discount_amount = $data["discount_amount"];
				$minimum_amount = $data["minimum_amount"];
				$maximum_amount = $data["maximum_amount"];

				$items_all = $data["items_all"];
				$items_ids = $data["items_ids"];
				$items_types_ids = $data["items_types_ids"];
				$items_categories_ids = $data["items_categories_ids"];
				$search_items_ids = explode(",", $items_ids);
				$search_items_types_ids = explode(",", $items_types_ids);
				$search_items_categories_ids = explode(",", $items_categories_ids);

				// check categories 
				$category_found = false;
				if (is_array($search_items_categories_ids) && is_array($categories_ids) && sizeof($search_items_categories_ids) && sizeof($categories_ids)) {
					foreach ($categories_ids as $id) {
						if (in_array($id, $search_items_categories_ids)) {
							$category_found = true;
							break;
						}
					}
				}
				// end categories check

				$users_all = $data["users_all"];
				$users_ids = $data["users_ids"];
				$users_types_ids = $data["users_types_ids"];
				$search_users_ids = explode(",", $users_ids);
				$search_users_types_ids = explode(",", $users_types_ids);
				if (
					$price >= $minimum_amount &&
					(!$maximum_amount || $price <= $maximum_amount) &&
					($items_all || $category_found || in_array($item_id, $search_items_ids) || in_array($item_type_id, $search_items_types_ids)) &&
					($users_all || ($user_id && in_array($user_id, $search_users_ids)) || ($user_type_id && in_array($user_type_id, $search_users_types_ids)))
				) {
					// sales discount found
					if ($discount_type == 6) {
						$item_discount = round(($price * $discount_amount) / 100, 2);
					} elseif ($discount_type == 7) {
						$item_discount = round($discount_amount, 2);
					}
					$is_sales = 1;
					$sales_price = $price - $item_discount;

					// added applied coupons information for 'coupon' type
					if ($sales_type == "coupon") {
						if (strlen($coupons_ids)) { $coupons_ids .= ","; }
						$coupons_ids .= $coupon_id;
						$coupons_applied[$coupon_id] = array(
							"id" => $coupon_id, "code" => $coupon_code, "type" => $discount_type, "title" => $coupon_title, "discount" => $item_discount);
					}
					break;
				}
			}
		}
		$db->set_rsi($db_rsi);
	}

	function get_item_info(&$item, $item_id = "", $quantity = "") 
	{
		global $db, $dbs, $table_prefix, $site_id;
		$db_rsi = $db->set_rsi("s");

		$item_id = isset($item["ITEM_ID"]) ? $item["ITEM_ID"] : $item_id;
		$quantity = isset($item["QUANTITY"]) ? $item["QUANTITY"] : $quantity;
		$is_price_edit = isset($item["PRICE_EDIT"]) ? $item["PRICE_EDIT"] : 0;

		if (!$is_price_edit) {
			$quantity_price = get_quantity_price($item_id, $quantity);
			if (is_array($quantity_price) && sizeof($quantity_price) == 3) {
				$item["ITEM_ID"] = $item_id;
				$item["PRICE"] = $quantity_price[0];
				$item["PROPERTIES_DISCOUNT"] = $quantity_price[1];
				$item["DISCOUNT"] = $quantity_price[2];
			} else {
				// check original price
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
	      
				$sql  = " SELECT item_id,item_type_id,".$price_field.",".$sales_field.",is_sales ";
				$sql .= " FROM " . $table_prefix . "items ";
				$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$item_type_id = $db->f("item_type_id");
					$price = $db->f($price_field);
					$is_sales = $db->f("is_sales");
					$sales_price = $db->f($sales_field);
					$coupons_ids = ""; $coupons_discount = ""; $coupons_applied = array();
					get_sales_price($price, $is_sales, $sales_price, $item_id, $item_type_id, $coupons_ids, $coupons_discount, $coupons_applied);
					$product_price = calculate_price($price, $is_sales, $sales_price);
				} else {
					$product_price = isset($item["PRICE"]) ? $item["PRICE"] : "";
				}
				$item["ITEM_ID"] = $item_id;
				$item["PRICE"] = $product_price;
				$item["PROPERTIES_DISCOUNT"] = 0;
				$item["DISCOUNT"] = 1; // discount applicable
			}
		}
		$db->set_rsi($db_rsi);
	}

	// get data from product subcomponent
	function get_component_info($property_id, $item_property_id, $property_type_id, $parent_quantity) 
	{
		global $db, $table_prefix, $site_id;
		$db_rsi = $db->set_rsi("s");

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
		$component = array();
		if ($property_type_id == 2) {
			$sql  = " SELECT i.item_id, i.item_type_id, i.item_name, pr.quantity, pr.quantity_action, pr.usage_type, ";
			$sql .= " pr.property_name AS component_name, pr.".$additional_price_field." AS component_price, ";
			$sql .= " i.buying_price, i." . $price_field . ", i.is_sales, i." . $sales_field . ", i.tax_id, i.tax_free, ";
			$sql .= " i.stock_level, i.use_stock_level, i.hide_out_of_stock, i.disable_out_of_stock  ";
			$sql .= " FROM (" . $table_prefix . "items_properties pr ";								
			$sql .= " INNER JOIN  " . $table_prefix . "items i ON pr.sub_item_id=i.item_id)";
			$sql .= " WHERE pr.property_id=" . $db->tosql($property_id, INTEGER);									
		} else if ($property_type_id == 3) {
			$sql  = " SELECT i.item_id, i.item_type_id, i.item_name, pr.quantity,  pr.quantity_action, pr.usage_type, ";
			$sql .= " ipv.property_value AS component_name, ipv.".$additional_price_field." AS component_price, ";
			$sql .= " i.buying_price, i." . $price_field . ", i.is_sales, i." . $sales_field . ", i.tax_id, i.tax_free, ";
			$sql .= " i.stock_level, i.use_stock_level, i.hide_out_of_stock, i.disable_out_of_stock ";
			$sql .= " FROM (( " . $table_prefix . "items_properties_values ipv ";
			$sql .= " INNER JOIN " . $table_prefix . "items_properties pr ON pr.property_id=ipv.property_id)";
			$sql .= " INNER JOIN " . $table_prefix . "items i ON ipv.sub_item_id=i.item_id)";
			$sql .= " WHERE ipv.item_property_id=" . $db->tosql($item_property_id, INTEGER);
		} else {
			$db->set_rsi($db_rsi);
			return false;
		}
		$db->query($sql);
		if ($db->next_record()) {
			$sub_item_id = $db->f("item_id");
			$sub_item_type_id = $db->f("item_type_id");
			$component_name = $db->f("component_name");
			$usage_type = $db->f("usage_type");
			$sub_quantity = $db->f("quantity");
			if ($sub_quantity < 1) { $sub_quantity = 1; }
			$quantity_action = $db->f("quantity_action");
			$sub_tax_id = $db->f("tax_id");
			$sub_tax_free = $db->f("tax_free");

			// calculate price
			$component_price = $db->f("component_price");
			$buying_price = $db->f("buying_price");
			$sub_price = $db->f($price_field);
			$sub_is_sales = $db->f("is_sales");
			$sub_sales = $db->f($sales_field);

			$base_price = ""; $user_price = ""; $user_price_action = "";
			if (!strlen($component_price)) {
				$coupons_ids = ""; $coupons_discount = ""; $coupons_applied = array();
				get_sales_price($sub_price, $sub_is_sales, $sub_sales, $sub_item_id, $sub_item_type_id, $coupons_ids, $coupons_discount, $coupons_applied);
				if ($sub_is_sales && $sub_sales > 0) {
					$base_price = $sub_sales;
				} else {
					$base_price = $sub_price;
				}
				if ($quantity_action == 2) {
					$component_quantity = $sub_quantity;
				} else {
					$component_quantity = $parent_quantity * $sub_quantity;
				}
				// get qty prices if exists
				$user_price  = ""; $discount_applicable = 1;
				$q_prices    = get_quantity_price($sub_item_id, $component_quantity);
				if (sizeof($q_prices)) {
					$user_price  = $q_prices [0];
					$user_price_action = $q_prices[2];
				}				
				// probably always apply here final component price
				//$user_discount_type = get_session("session_discount_type");
				//$user_discount_amount = get_session("session_discount_amount");
				//$sub_prices = get_product_price($sub_item_id, $sub_price, $sub_buying, 0, 0, $user_price, $user_price_action, $user_discount_type, $user_discount_amount);
				//$component_price = $sub_prices["base"];

			}
			$component = array(
				"type_id" => $property_type_id, "sub_item_id" => $sub_item_id, "item_type_id" => $sub_item_type_id,
				"usage_type" => $usage_type, "quantity" => $sub_quantity, "quantity_action" => $quantity_action, 
				"name" => $component_name, "price" => $component_price, "tax_id" => $sub_tax_id, "tax_free" => $sub_tax_free,
				"buying" => $buying_price, "base_price" => $base_price,  "user_price" => $user_price, "user_price_action" => $user_price_action,
			);
		}
		$db->set_rsi($db_rsi);
		return $component;
	}

	function get_product_price($item_id, $price, $buying, $is_sales, $sales, $user_price, $discount_applicable, $discount_type, $discount_amount)
	{
		$prices = array();

		if ($user_price > 0 && ($discount_applicable > 0 || !$discount_type)) {
			if ($is_sales && $sales > 0) {
				$sales = $user_price;
			} else {
				$price = $user_price;
			}
		}
		if ($is_sales && $sales > 0) {
			$real_price = $sales;
		} else {
			$real_price = $price;
		}

		if ($discount_applicable) {
			if ($discount_type == 1 || $discount_type == 3) {
				$price -= round(($price * $discount_amount) / 100, 2);
				$sales -= round(($sales * $discount_amount) / 100, 2);
			} elseif ($discount_type == 2) {
				$price -= round($discount_amount, 2);
				$sales -= round($discount_amount, 2);
			} elseif ($discount_type == 4) {
				$price -= round((($price - $buying) * $discount_amount) / 100, 2);
				$sales -= round((($sales - $buying) * $discount_amount) / 100, 2);
			}
		}

		if ($is_sales && $sales > 0) {
			$prices["base"] = $sales;
		} else {
			$prices["base"] = $price;
		}
		$prices["price"] = $price;
		$prices["sales"] = $price;
		$prices["real"] = $real_price;

		return $prices;
	}

	function get_option_price($additional_price, $buying_price, $properties_percent, $discount_applicable, $discount_type, $discount_amount)
	{
		if ($properties_percent) {
			$additional_price -= round((doubleval($additional_price) * doubleval($properties_percent)) / 100, 2);
		}
		if ($discount_applicable) {
			if ($discount_type == 1) {
				$additional_price -= round((doubleval($additional_price) * doubleval($discount_amount)) / 100, 2);
			} elseif ($discount_type == 4) {
				$additional_price -= round(((doubleval($additional_price) - doubleval($buying_price)) * doubleval($discount_amount)) / 100, 2);
			}
		}

		return doubleval($additional_price);
	}

	function get_stock_levels(&$items_stock, &$options_stock)
	{
		global $db, $table_prefix, $shopping_cart;

		$items_stock = array();
		$options_stock = array();
		foreach ($shopping_cart as $cart_id => $cart_info) {
			$item_id = $cart_info["ITEM_ID"];
			$item_quantity = $cart_info["QUANTITY"];
			if (isset($items_stock[$item_id])) {
				$items_stock[$item_id] += $item_quantity;
			} else {
				$items_stock[$item_id] = $item_quantity;
			}
			$item_properties = $cart_info["PROPERTIES"];
			if (!is_array($item_properties)) { $item_properties = array(); }
			$properties_info = isset($cart_info["PROPERTIES_INFO"]) ? $cart_info["PROPERTIES_INFO"] : "";
			if (!is_array($properties_info)) { $properties_info = array(); }
			if (count($item_properties)) {
				foreach ($properties_info as $property_id => $property_info) {
					$ct = strtoupper($property_info["CONTROL"]);
					$property_type_id = $property_info["TYPE"];
					$property_name = $property_info["NAME"];
					$property_values = $property_info["VALUES"];
					if ($property_type_id == 1) {
						if (strtoupper($ct) == "LISTBOX"
						|| strtoupper($ct) == "RADIOBUTTON"
						|| strtoupper($ct) == "IMAGE_SELECT"
						|| strtoupper($ct) == "CHECKBOXLIST") {
							for ($ov = 0; $ov < sizeof($property_values); $ov++) {
								$option_value_id = $property_values[$ov];
								if (isset($options_stock[$option_value_id])) {
									$options_stock[$option_value_id] += $item_quantity;
								} else {
									$options_stock[$option_value_id] = $item_quantity;
								}
							}
						}
					}

				}
			}
		}
	}

	function remove_coupon($coupon_id)
	{
		global $shopping_cart, $coupons;
		if (!isset($shopping_cart)) {
			$shopping_cart = get_session("shopping_cart");
		}
		if (!isset($coupons)) {
			$coupons = get_session("session_coupons");
		}
		if (is_array($coupons) && isset($coupons[$coupon_id])) {
			unset($coupons[$coupon_id]);
			if (sizeof($coupons) == 0) {
				set_session("session_coupons", "");
			} else {
				set_session("session_coupons", $coupons);
			}
		}
		if (is_array($shopping_cart)) {
			foreach ($shopping_cart as $cart_id => $item) {
				if (isset($shopping_cart[$cart_id]["COUPONS"]) && isset($shopping_cart[$cart_id]["COUPONS"][$coupon_id])) {
					unset($shopping_cart[$cart_id]["COUPONS"][$coupon_id]);
					if (sizeof($shopping_cart[$cart_id]["COUPONS"]) == 0) {
						unset($shopping_cart[$cart_id]["COUPONS"]);
					}
				}
			}
		}
		set_session("shopping_cart", $shopping_cart);
		set_session("session_coupons", $coupons);
	}

	function get_tax_rates($live_taxes = false, $country_id = "", $state_id = "", $postal_code = "")
	{
		global $db, $table_prefix, $settings;

		if (!$live_taxes) {
			$tax_rates = get_session("session_tax_rates");
		} else {
			$tax_rates = "";
		}
		if (!is_array($tax_rates)) {
			$postal_code = trim(str_replace(" ", "", $postal_code));
			$tax_rates = array();
			$tax_ids = "";
			$sql  = " SELECT * ";
			$sql .= " FROM " . $table_prefix . "tax_rates ";
			if ($country_id) {
				$sql .= " WHERE country_id=" . $db->tosql($country_id, INTEGER, true, false);
				$sql .= " AND (state_id=0 OR state_id=" . $db->tosql($state_id, INTEGER, true, false) . ")";
			} else {
				$sql .= " WHERE is_default=1 ";
			}
			$sql .= " AND tax_type>0 ";
			$sql .= " ORDER BY state_id DESC ";
			$db->query($sql);
			while ($db->next_record()) {
				$tax_id = $db->f("tax_id");
				$tax_pc = trim($db->f("postal_code"));
				if (strlen($tax_pc)) {
					$tax_pc_match = false;
					$tax_pc = str_replace(";", ",", $tax_pc);
					$tax_pcs = explode(",", $tax_pc);
					foreach($tax_pcs as $id => $tax_pc) {
						$tax_pc = trim($tax_pc);
						if (strlen($tax_pc) && preg_match("/^".preg_quote(trim($tax_pc), "/")."/", $postal_code)) {
							$tax_pc_match = true; break;
						}
					}
				} else {
					// if there is no postal code to match then automatically set it to true

					$tax_pc_match = true;
				}
				
				if ($tax_pc_match) {
					// if postal code matched add tax to the list
					$tax_rate = array(
						"tax_id" => $db->f("tax_id"), "tax_type" => $db->f("tax_type"), "show_type" => $db->f("show_type"), 
						"tax_name" => $db->f("tax_name"), "tax_php_lib" => $db->f("tax_php_lib"), 
						"country_id" => $db->f("country_id"), "state_id" => $db->f("state_id"), 
						"tax_percent" => $db->f("tax_percent"), "fixed_amount" => $db->f("fixed_amount"), "order_fixed_amount" => $db->f("order_fixed_amount"), 
						"types" => array("shipping" => array(
								"tax_percent" => $db->f("shipping_tax_percent"), "fixed_amount" => $db->f("shipping_fixed_amount"), 
							),
						),
					);
					$tax_rates[$tax_id] = $tax_rate;
					if (strval($tax_ids) !== "") { $tax_ids .= ","; }
					$tax_ids .= $tax_id;
				}
			}

			if (strlen($tax_ids)) {
				$sql  = " SELECT tax_id, item_type_id, tax_percent, fixed_amount ";	
				$sql .= " FROM " . $table_prefix . "tax_rates_items ";
				$sql .= " WHERE tax_id IN (" . $db->tosql($tax_ids, INTEGERS_LIST) . ") ";
				$db->query($sql);
				while ($db->next_record()) {
					$tax_id = $db->f("tax_id");
					$item_type_id = $db->f("item_type_id");
					$tax_percent = $db->f("tax_percent");
					$fixed_amount = $db->f("fixed_amount");
					if (strlen($tax_percent) || strlen($fixed_amount)) {
						$tax_rates[$tax_id]["types"][$item_type_id] = array(
							"tax_percent" => $tax_percent, "fixed_amount" => $fixed_amount, 
						);
					}
				}
			}
			set_session("session_tax_rates", $tax_rates);
		}

		return $tax_rates;
	}

	function set_tax_price($item_index, $item_type_id, $price, $quantity, $sales, $tax_id, $tax_free, $price_tag = "", $sales_tag = "", $tax_price_tag = "", $tag_id = false, $comp_price = 0, $comp_tax = 0, $tax_vars = true)
	{
		global $t, $settings, $tax_rates, $currency;

		$price = doubleval($price);	
		$sales = doubleval($sales);
		$zero_price_type = get_setting_value($settings, "zero_price_type", 0);
		$zero_price_message = get_translation(get_setting_value($settings, "zero_price_message", ""));
		$tax_prices_type = get_setting_value($settings, "tax_prices_type", 0);
		$tax_prices = get_setting_value($settings, "tax_prices", 0);
		$tax_note_excl = get_translation(get_setting_value($settings, "tax_note_excl", ""));
		$tax_note_incl = get_translation(get_setting_value($settings, "tax_note", ""));
		$price_tax = get_tax_amount($tax_rates, $item_type_id, $price, $quantity, $tax_id, $tax_free, $tax_percent);
		$sales_tax = get_tax_amount($tax_rates, $item_type_id, $sales, $quantity, $tax_id, $tax_free, $tax_percent);
		$tax_amount = $price_tax;

		if ($tax_prices_type == 1) {
			$price_incl = $price + $comp_price;
			$price_excl = $price - $price_tax + $comp_price - $comp_tax;
			$sales_incl = $sales + $comp_price;
			$sales_excl = $sales - $sales_tax + $comp_price - $comp_tax;
		} else {
			$price_incl = $price + $price_tax + $comp_price + $comp_tax;
			$price_excl = $price + $comp_price;
			$sales_incl = $sales + $sales_tax + $comp_price + $comp_tax;
			$sales_excl = $sales + $comp_price;
		}

		if ($tax_prices == 0 || $tax_prices == 3) {
			$tax_price_tag = "";
		}

		// set some product settings
		$prices_classes = array("0" => "price-excl", "1" => "price-excl-incl", "2" => "price-incl-excl", "3" => "price-incl");
		$t->set_var("price_type_class", $prices_classes[$tax_prices]);
		$t->set_var("price_block_class", "price-main");
		if ($price_tag) {

			$price_note = "";
			if ($tax_prices == 0 || $tax_prices == 1) {
				$price_note = $tax_note_excl;
			} else {
				$price_note = $tax_note_incl;
			}
			$t->set_var("price_note", $price_note);

			if ($tax_prices == 0 || $tax_prices == 1) {
				$product_price = $price_excl;
				$product_sales = $sales_excl;
			} else {
				$product_price = $price_incl;
				$product_sales = $sales_incl;
			}

			if ($tax_vars) {
				$t->set_var("tax_percent", $tax_percent);
				$t->set_var("tax_prices", $tax_prices);
			}

			if ($zero_price_type && $product_price == 0) {
				if ($zero_price_type == 1) {
					$t->set_var("price_block_class", "hidden");
				}
				$t->set_var($price_tag, $zero_price_message);
				$t->set_var($price_tag . "_control", $zero_price_message);
			} else {
				$t->set_var("price_block_class", "price-main");
				$t->set_var($price_tag, currency_format($product_price));
				$t->set_var($price_tag . "_control", currency_format($product_price));
			}
			if ($sales_tag) {
				$t->set_var("price_block_class", "price-old");
				$t->set_var($sales_tag, currency_format($product_sales));
				$t->set_var($sales_tag. "_control", currency_format($product_sales));
				$t->set_var("you_save", currency_format($product_price - $product_sales));
				$discount_percent = $product_price ? round(($product_price - $product_sales) / ($product_price / 100), 0) : 0;
				$t->set_var("discount_percent", $discount_percent);
			}
		}
		if ($tax_price_tag) {
			if ($tax_prices == 1) {
				$product_price = $price_incl;
				$product_sales = $sales_incl;
				$tax_note = $tax_note_incl;
			} else {
				$product_price = $price_excl;
				$product_sales = $sales_excl;
				$tax_note = $tax_note_excl;
			}
			$tax_price = ($sales_tag) ? $product_sales : $product_price;
			if ($tax_note) { $tax_note = " " . $tax_note; }
			if ($zero_price_type && $product_price == 0) {
				if ($tag_id) {
					$t->set_var($tax_price_tag, "<span class=\"tax-price\" id=\"tax_price" . $item_index. "\"></span>");
				} else {
					$t->set_var($tax_price_tag, "");
				}
			} else {
				if ($tag_id) {
					$t->set_var($tax_price_tag, "<span class=\"tax-price\" id=\"tax_price" . $item_index . "\">" . currency_format($tax_price) . $tax_note . "</span>");
				} else {
					$t->set_var($tax_price_tag, currency_format($tax_price) . $tax_note);
				}
			}
		}

		return $tax_amount;
	}

	function delete_products($items_ids)
	{
		global $db, $table_prefix;

		// delete all properties
		$properties_ids = "";
		$sql = " SELECT property_id FROM " . $table_prefix ."items_properties WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")";
		$db->query($sql);
		while ($db->next_record()) {
			if (strlen($properties_ids)) { $properties_ids .= ","; }
			$properties_ids .= $db->f("property_id");
		}
		if (strlen($properties_ids)) {
			$db->query("DELETE FROM " . $table_prefix . "items_properties_values WHERE property_id IN (" . $db->tosql($properties_ids, INTEGERS_LIST) . ") ");
			$db->query("DELETE FROM " . $table_prefix . "items_properties_sizes WHERE property_id IN (" . $db->tosql($properties_ids, INTEGERS_LIST) . ") ");
			$db->query("DELETE FROM " . $table_prefix . "items_properties WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ") ");
		}
		// delete properties and values where it's a subcomponent
		$db->query("DELETE FROM " . $table_prefix . "items_properties_values WHERE sub_item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ") ");
		$db->query("DELETE FROM " . $table_prefix . "items_properties WHERE sub_item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ") ");

		// delete all releases
		$releases_ids = "";
		$sql = " SELECT release_id FROM " . $table_prefix ."releases WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")";
		$db->query($sql);
		while ($db->next_record()) {
			if (strlen($releases_ids)) { $releases_ids .= ","; }
			$releases_ids .= $db->f("release_id");
		}
		if (strlen($releases_ids)) {
			$db->query("DELETE FROM " . $table_prefix . "release_changes WHERE release_id  IN (" . $db->tosql($releases_ids, INTEGERS_LIST) . ") ");
			$db->query("DELETE FROM " . $table_prefix . "releases WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ") ");
		}

		// delete from other tables
		$db->query("DELETE FROM " . $table_prefix . "items_sites WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "items_subscriptions WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "items_user_types WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "reviews WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "items_categories WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "items_related WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "articles_categories_items WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "articles_items_related WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "features WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "items_images WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "items_accessories WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "items_properties_assigned WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "items_values_assigned WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "items WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "items_files WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "items_serials WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "items_prices WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")");
		$db->query("DELETE FROM " . $table_prefix . "keywords_items WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")");
	}

	function delete_categories($categories_ids)
	{
		global $db, $table_prefix;
		$db->set_rsi(0);
    
		$categories = array();
		$sql  = " SELECT category_id,category_path FROM " . $table_prefix . "categories ";
		$sql .= " WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ") ";
		$db->query($sql);
		while ($db->next_record()) {
			$category_id = $db->f("category_id");
			$category_path = $db->f("category_path");
			if (!in_array($category_id, $categories)) {
				$categories[] = $category_id;
				$sql  = " SELECT category_id FROM " . $table_prefix . "categories ";
				$sql .= " WHERE category_path LIKE '" . $db->tosql($category_path.$category_id.",", TEXT, false) . "%'";
				$db->query($sql, "s");
				while($db->next_record()) {
					$categories[] = $db->f("category_id");
				}
				$db->set_rsi(0);
			}
		}

		$db->set_rsi(0);
		if (is_array($categories) && sizeof($categories) > 0) {
			$categories_ids = join(",", $categories);
			$db->query("DELETE FROM " . $table_prefix . "categories WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")");
			$db->query("DELETE FROM " . $table_prefix . "items_categories WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")");
			$db->query("DELETE FROM " . $table_prefix . "categories_user_types WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")");
			$db->query("DELETE FROM " . $table_prefix . "categories_subscriptions WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")");
			$db->query("DELETE FROM " . $table_prefix . "categories_sites WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")");
			$db->query("DELETE FROM " . $table_prefix . "categories_columns WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")");
		}

		// delete products that are not assigned to any category 
		$sql  = " SELECT i.item_id FROM (" . $table_prefix ."items i ";
		$sql .= " LEFT JOIN " . $table_prefix . "items_categories ic ON i.item_id=ic.item_id) ";
		$sql .= " WHERE ic.category_id IS NULL ";
		$db->query($sql);
		while ($db->next_record()) {
			$item_id = $db->f("item_id");
			delete_products($item_id);
		}
	}

	function check_coupons($auto_apply = true)
	{
		check_add_coupons($auto_apply, "", $coupon_error);
	}

	function check_add_coupons($auto_apply, $new_coupon_code, &$new_coupon_error)
	{
		global $db, $site_id, $table_prefix, $date_show_format;
		global $currency;

		$shopping_cart = get_session("shopping_cart");
		$order_coupons = get_session("session_coupons");
		$user_info = get_session("session_user_info");
		$user_id = get_setting_value($user_info, "user_id", "");
		$user_type_id = get_setting_value($user_info, "user_type_id", "");
		$user_tax_free = get_setting_value($user_info, "tax_free", 0);
		$user_discount_type = get_session("session_discount_type");
		$user_discount_amount = get_session("session_discount_amount");

		if (!is_array($shopping_cart) || sizeof($shopping_cart) < 1) { $shopping_cart = array(); }
		if (!is_array($order_coupons)) { $order_coupons = array(); }

		// check basic product prices and product categories ids before any further checks
		foreach($shopping_cart as $cart_id => $item)
		{
			$item_id = $item["ITEM_ID"];
			if (!$item_id) { 
				continue;
			}

			$categories_ids = array();
			$sql  = " SELECT ic.category_id, c.category_path ";
			$sql .= " FROM (" . $table_prefix . "items_categories ic ";
			$sql .= " INNER JOIN " . $table_prefix . "categories c ON c.category_id=ic.category_id) ";
			$sql .= " WHERE ic.item_id=" . $db->tosql($item_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$category_path = $db->f("category_path");
				$category_path.= $db->f("category_id");
				$ids = explode(",", $category_path);
				foreach ($ids as $id) {
					if (!in_array($id, $categories_ids)) {
						$categories_ids[] = $id;
					}
				}
			}	
			$shopping_cart[$cart_id]["CATEGORIES_IDS"] = $categories_ids;

			$item_type_id = $item["ITEM_TYPE_ID"];
			$properties = $item["PROPERTIES"];
			if (!is_array($properties)) { $properties = array(); }
			$quantity = $item["QUANTITY"];
			$tax_id = $item["TAX_ID"];
			$tax_free = $item["TAX_FREE"];
			$discount_applicable = $item["DISCOUNT"];
			$buying_price = $item["BUYING_PRICE"];
			$price = $item["PRICE"];
			$is_price_edit = $item["PRICE_EDIT"];
			$properties_price = $item["PROPERTIES_PRICE"];
			$properties_percentage = $item["PROPERTIES_PERCENTAGE"];
			$properties_buying = $item["PROPERTIES_BUYING"];
			$properties_discount = $item["PROPERTIES_DISCOUNT"];
			$components = $item["COMPONENTS"];
			if ($discount_applicable) {
				if (!$is_price_edit) {
					if ($user_discount_type == 1) {
						$price -= round(($price * $user_discount_amount) / 100, 2);
					} else if ($user_discount_type == 2) {
						$price -= round($user_discount_amount, 2);
					} else if ($user_discount_type == 3) {
						$price -= round(($price * $user_discount_amount) / 100, 2);
					} else if ($user_discount_type == 4) {
						$price -= round((($price - $buying_price) * $user_discount_amount) / 100, 2);
					}
				}
			} 
			if ($properties_percentage && $price) {
				$properties_price += round(($price * $properties_percentage) / 100, 2);
			}
			if ($properties_discount > 0) {
				$properties_price -= round(($properties_price * $properties_discount) / 100, 2);
			}
			if ($discount_applicable) {
				if ($user_discount_type == 1) {
					$properties_price -= round((($properties_price) * $user_discount_amount) / 100, 2);
				} else if ($user_discount_type == 4) {
					$properties_price -= round((($properties_price - $properties_buying) * $user_discount_amount) / 100, 2);
				}
			}
			$price += $properties_price;

			// add components prices
			if (is_array($components) && sizeof($components) > 0) {
				foreach ($components as $property_id => $component_values) {
					foreach ($component_values as $property_item_id => $component) {
						$component_price = $component["price"];
						$component_tax_id = $component["tax_id"];
						$component_tax_free = $component["tax_free"];
						if ($user_tax_free) { $component_tax_free = $user_tax_free; }
						$sub_item_id = $component["sub_item_id"];
						$sub_quantity = $component["quantity"];
						if ($sub_quantity < 1)  { $sub_quantity = 1; }
						$sub_type_id = $component["item_type_id"];
						if (!strlen($component_price)) {
							$sub_price = $component["base_price"];
							$sub_buying = $component["buying"];
							$sub_user_price = $component["user_price"];
							$sub_user_action = $component["user_price_action"];
							$sub_prices = get_product_price($sub_item_id, $sub_price, $sub_buying, 0, 0, $sub_user_price, $sub_user_action, $user_discount_type, $user_discount_amount);
							$component_price = $sub_prices["base"];
						}
						// add to the item price component price
						$price += $component_price;
					}
				}
			}

			$shopping_cart[$cart_id]["BASIC_PRICE"] = $price; // basic price to calculate discount amount for product coupons 
			$shopping_cart[$cart_id]["DISCOUNTED_PRICE"] = $price; // product price with all coupon discounts
		}
		// end of product prices check

		// check if any product coupons should be removed
		$exclusive_applied = false; $new_coupons_total = 0; $coupons_total = 0;
		// collect all product coupons first and remove auto-applied coupons and try add them again
		$products_coupons = array();
		foreach($shopping_cart as $cart_id => $item) {
			$item_id = $item["ITEM_ID"];
			if (!$item_id) { continue; }
			// product coupons
			if (isset($item["COUPONS"]) && is_array($item["COUPONS"])) {
				foreach ($item["COUPONS"] as $coupon_id => $coupon_info) {
					if ($auto_apply && $coupon_info["AUTO_APPLY"]) {
						// always remove auto-apply coupons
						unset($shopping_cart[$cart_id]["COUPONS"][$coupon_id]);
					} else {
						$products_coupons[$coupon_id] = $coupon_info;
					}
				}
			}
		}

		foreach($products_coupons as $coupon_id => $coupon_info) {
			$apply_coupon = false;
			$sql  = " SELECT * FROM " . $table_prefix . "coupons ";
			$sql .= " WHERE coupon_id=" . $db->tosql($coupon_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$apply_coupon = true;
				$discount_type = $db->f("discount_type");
				$coupon_discount = $db->f("discount_amount");
				$min_quantity = $db->f("min_quantity");
				$max_quantity = $db->f("max_quantity");
				$minimum_amount = $db->f("minimum_amount");
				$maximum_amount = $db->f("maximum_amount");
				$is_exclusive = $db->f("is_exclusive");
				// check cart fields and total values 
				$min_cart_quantity = $db->f("min_cart_quantity");
				$max_cart_quantity = $db->f("max_cart_quantity");
				$min_cart_cost = $db->f("min_cart_cost");
				$max_cart_cost = $db->f("max_cart_cost");

				$cart_items_all = $db->f("cart_items_all");
				$cart_items_ids = $db->f("cart_items_ids");
				$cart_items_types_ids = $db->f("cart_items_types_ids");

				check_cart_totals($cart_quantity, $cart_cost, $shopping_cart, $cart_items_all, $cart_items_ids, $cart_items_types_ids);
			}
			// check if coupon could be still used
			foreach($shopping_cart as $cart_id => $item) {
				$item_id = $item["ITEM_ID"];
				if (!$item_id) { continue; }

				if (isset($item["COUPONS"]) && isset($item["COUPONS"][$coupon_id])) {
					if ($apply_coupon) {
						// check other restriction if coupon could be applied
						$item_type_id = $item["ITEM_TYPE_ID"];
						$basic_price = $item["BASIC_PRICE"];
						$discounted_price = $item["DISCOUNTED_PRICE"];
						$quantity = $item["QUANTITY"];
						if ($quantity < $min_quantity || $basic_price < $minimum_amount ||
							($max_quantity && $max_quantity < $quantity) ||
							($maximum_amount && $maximum_amount < $basic_price) ||
							$cart_quantity < $min_cart_quantity || $cart_cost < $min_cart_cost ||
							($max_cart_quantity && $max_cart_quantity < $cart_quantity) ||
							($max_cart_cost && $max_cart_cost < $cart_cost)
						) {
							$apply_coupon = false;
						}
					}
					if ($apply_coupon) {
						// descrease product price for coupon discount
						$discount_amount = $coupon_info["DISCOUNT_AMOUNT"];
						$discounted_price -= $discount_amount;
						$shopping_cart[$cart_id]["DISCOUNTED_PRICE"] = $discounted_price;
						if ($is_exclusive) { $exclusive_applied = true; }
						$coupons_total++;
					} else {
						unset($shopping_cart[$cart_id]["COUPONS"][$coupon_id]);
					}
				} 
				$coupon_exists = false;
			}
		}

		// check if any order coupons should be removed
		// cart_quantity and cart_cost variable is used to check order coupons
		if (is_array($order_coupons)) {
			foreach ($order_coupons as $coupon_id => $coupon_info) {
				if ($auto_apply && $coupon_info["AUTO_APPLY"]) {
					// always remove auto-apply coupons
					unset($order_coupons[$coupon_id]);
				} else {
					$sql  = " SELECT c.* FROM ";
					if (isset($site_id)) {
						$sql .= "(";
					}
					$sql .= $table_prefix . "coupons c";
					if (isset($site_id)) {
							$sql .= " LEFT JOIN  " . $table_prefix . "coupons_sites s ON s.coupon_id=c.coupon_id)";
					}
					$sql .= " WHERE c.coupon_id=" . $db->tosql($coupon_id, INTEGER);
					if (isset($site_id)) {
						$sql .= " AND (c.sites_all=1 OR s.site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
					} else {
						$sql .= " AND c.sites_all=1 ";
					}
					$sql .= " ORDER BY c.apply_order ";
					$db->query($sql);
					if ($db->next_record()) {
						$discount_type = $db->f("discount_type");
						$coupon_discount = $db->f("discount_amount");
						$is_exclusive = $db->f("is_exclusive");

						// check cart fields and cart totals
						$min_cart_quantity = $db->f("min_cart_quantity");
						$max_cart_quantity = $db->f("max_cart_quantity");
						$min_cart_cost = $db->f("min_cart_cost");
						$max_cart_cost = $db->f("max_cart_cost");

						/*
						$cart_items_all = $db->f("cart_items_all");
						$cart_items_ids = $db->f("cart_items_ids");
						$cart_items_types_ids = $db->f("cart_items_types_ids");

						check_cart_totals($cart_quantity, $cart_cost, $shopping_cart, $cart_items_all, $cart_items_ids, $cart_items_types_ids);
						*/


						check_cart_totals($cart_quantity, $cart_cost, $shopping_cart, 1, "", "");

						if ($cart_quantity < $min_cart_quantity || $cart_cost < $min_cart_cost ||
							($max_cart_quantity && $max_cart_quantity < $cart_quantity) ||
							($max_cart_cost && $max_cart_cost < $cart_cost)) {
							unset($order_coupons[$coupon_id]);
						} else {
							if ($is_exclusive) { $exclusive_applied = true; }
							$coupons_total++;
						}
					} else {
						unset($order_coupons[$coupon_id]);
					}
				}
			}
		}

		// check if new coupons could be added
		$new_coupons = array(); 
		if (strlen($new_coupon_code)) {
			$sql  = " SELECT c.* FROM (" . $table_prefix . "coupons c";
			if (isset($site_id)) {
				$sql .= " LEFT JOIN  " . $table_prefix . "coupons_sites s ON s.coupon_id=c.coupon_id)";
			} else {
				$sql .= ")";
			}
			$sql .= " WHERE c.coupon_code=" . $db->tosql($new_coupon_code, TEXT);
			if (isset($site_id)) {
				$sql .= " AND (c.sites_all=1 OR s.site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
			} else {
				$sql .= " AND c.sites_all=1 ";
			}
			$sql .= " ORDER BY c.apply_order ";
			$db->query($sql);
			if ($db->next_record()) {
				$new_coupon_id = $db->f("coupon_id");
				$start_date_db = $db->f("start_date", DATETIME);
				$expiry_date_db = $db->f("expiry_date", DATETIME);
				$new_coupons[$new_coupon_id] = $db->Record;
				$new_coupons[$new_coupon_id]["start_date_db"] = $start_date_db;
				$new_coupons[$new_coupon_id]["expiry_date_db"] = $expiry_date_db;
			} else {
				$new_coupon_error = va_constant("COUPON_NOT_FOUND_MSG"); 
			}
		}

		// check if some coupons from session could be added
		$auto_coupons = get_session("session_auto_coupons");
		if ($auto_apply && is_array($auto_coupons) && sizeof($auto_coupons) > 0) {
			$sql  = " SELECT c.* FROM (" . $table_prefix . "coupons c";
			if (isset($site_id)) {
				$sql .= " LEFT JOIN  " . $table_prefix . "coupons_sites s ON s.coupon_id=c.coupon_id)";
			} else {
				$sql .= ")";
			}
			$sql .= " WHERE ( ";
			for ($ac = 0; $ac < sizeof($auto_coupons); $ac++) {
				if ($ac > 0) { $sql .= " OR "; }
				$sql .= " c.coupon_code=" . $db->tosql($auto_coupons[$ac], TEXT);
			}
			$sql .= " ) ";
			if (isset($site_id)) {
				$sql .= " AND (c.sites_all=1 OR s.site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
			} else {
				$sql .= " AND c.sites_all=1 ";
			}
			$sql .= " ORDER BY c.apply_order ";
			$db->query($sql);
			if ($db->next_record()) {
				$new_coupon_id = $db->f("coupon_id");
				$start_date_db = $db->f("start_date", DATETIME);
				$expiry_date_db = $db->f("expiry_date", DATETIME);
				$new_coupons[$new_coupon_id] = $db->Record;
				$new_coupons[$new_coupon_id]["start_date_db"] = $start_date_db;
				$new_coupons[$new_coupon_id]["expiry_date_db"] = $expiry_date_db;
			}
		}

		$discount_types = array("3,4", "1,2", "5,8"); // check products coupons, then order coupons and only then vouchers 

		if ($auto_apply) {
			for ($dt = 0; $dt < sizeof($discount_types); $dt++) {
				$sql  = " SELECT c.* FROM ";
				if (isset($site_id)) {
					$sql .= " ( ";
				}
				$sql .= $table_prefix . "coupons c";
				if (isset($site_id)) {
					$sql .= " LEFT JOIN  " . $table_prefix . "coupons_sites s ON s.coupon_id=c.coupon_id)";
				}
				$sql .= " WHERE c.is_auto_apply=1 ";
				$sql .= " AND c.discount_type IN (" . $discount_types[$dt] . ") ";
				if (isset($site_id)) {
					$sql .= " AND (c.sites_all=1 OR s.site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
				} else {
					$sql .= " AND c.sites_all=1 ";
				}
				$sql .= " ORDER BY c.apply_order ";
				$db->query($sql);
				while ($db->next_record()) {
					$new_coupon_id = $db->f("coupon_id");
					$start_date_db = $db->f("start_date", DATETIME);
					$expiry_date_db = $db->f("expiry_date", DATETIME);
					$new_coupons[$new_coupon_id] = $db->Record;
					$new_coupons[$new_coupon_id]["start_date_db"] = $start_date_db;
					$new_coupons[$new_coupon_id]["expiry_date_db"] = $expiry_date_db;
				}
			}
		}
		
		// check if new coupons could be added
		if (sizeof($new_coupons) > 0) {
			foreach ($new_coupons as $new_coupon_id => $data) {
				$coupon_error = "";
				$is_active = $data["is_active"];
				$new_coupon_id = $data["coupon_id"];
				$coupon_auto_apply = $data["is_auto_apply"];
				$coupon_code = $data["coupon_code"];
				$coupon_title = $data["coupon_title"];
				$discount_type = $data["discount_type"];
				$discount_quantity = $data["discount_quantity"];
				$coupon_discount = $data["discount_amount"];
				$free_postage = $data["free_postage"];
				$coupon_tax_free = $data["coupon_tax_free"];
				$coupon_order_tax_free = $data["order_tax_free"];
				$items_all = $data["items_all"];
				$items_rule = $data["items_rule"];
				$items_ids = $data["items_ids"];
				$items_types_ids = $data["items_types_ids"];
				$items_categories_ids = $data["items_categories_ids"];
				$search_items_ids = explode(",", $items_ids);
				$search_items_types_ids = explode(",", $items_types_ids);
				$search_items_categories_ids = explode(",", $items_categories_ids);
				$cart_items_all = $data["cart_items_all"];
				$cart_items_ids = $data["cart_items_ids"];
				$cart_items_types_ids = $data["cart_items_types_ids"];

				$users_all = $data["users_all"];
				$users_use_limit = $data["users_use_limit"];
				$users_ids = $data["users_ids"];
				$users_types_ids = $data["users_types_ids"];
				$search_users_ids = explode(",", $users_ids);
				$search_users_types_ids = explode(",", $users_types_ids);

				$expiry_date = "";
				$is_expired = false;
				$expiry_date_db = $data["expiry_date_db"];
				if (is_array($expiry_date_db)) {
					$expiry_date = va_date($date_show_format, $expiry_date_db);
					$expiry_date_ts = mktime (0,0,0, $expiry_date_db[MONTH], $expiry_date_db[DAY], $expiry_date_db[YEAR]);
					$current_date_ts = va_timestamp();
					if ($current_date_ts > $expiry_date_ts) {
						$is_expired = true;
					}
				}
				$start_date = "";
				$is_upcoming = false;
				$start_date_db = $data["start_date_db"];
				if (is_array($start_date_db)) {
					$start_date = va_date($date_show_format, $start_date_db);
					$start_date_ts = mktime (0,0,0, $start_date_db[MONTH], $start_date_db[DAY], $start_date_db[YEAR]);
					$current_date_ts = va_timestamp();
					if ($current_date_ts < $start_date_ts) {
						$is_upcoming = true;
					}
				}
				// check number how many times user can use coupon
				$user_not_limited = false;
				if ($users_use_limit && $user_id) {
					$sql  = " SELECT COUNT(*) FROM ((" . $table_prefix . "orders o ";
					$sql .= " INNER JOIN " . $table_prefix . "orders_coupons oc ON o.order_id=oc.order_id) ";
					$sql .= " INNER JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
					$sql .= " WHERE o.user_id=" . $db->tosql($user_id, INTEGER);
					$sql .= " AND oc.coupon_id=" . $db->tosql($new_coupon_id, INTEGER);
					$sql .= " AND NOT (os.paid_status=0 AND o.is_placed=1) ";
					$user_uses = get_db_value($sql);
					if ($users_use_limit > $user_uses) {
						$user_not_limited = true;
					}
				}

				// check past orders limits
				$past_orders = true; $past_orders_items = true;
				$orders_period = $data["orders_period"];
				$orders_interval = $data["orders_interval"];
				$orders_min_goods = $data["orders_min_goods"];
				$orders_max_goods = $data["orders_max_goods"];
				$orders_min_quantity = $data["orders_min_quantity"];
				$orders_max_quantity = $data["orders_max_quantity"];
				$orders_items_type = $data["orders_items_type"];
				$orders_items_ids = $data["orders_items_ids"];
				$orders_types_ids = $data["orders_types_ids"];
				if ($orders_min_goods || $orders_max_goods || $orders_min_quantity || $orders_max_quantity || $orders_items_type > 1) {
					$user_goods_total = 0; $user_qty_total = 0;
					$past_orders = false; $past_orders_items = false; 
					$items_ids = array(); $types_ids = array();
					$search_items_ids = array(); $search_types_ids = array();
					if (strlen($orders_items_ids)) {
						$search_items_ids = explode(",", $orders_items_ids);
						$items_ids = array_combine($search_items_ids, $search_items_ids);
					}
					if (strlen($orders_types_ids)) {
						$search_types_ids = explode(",", $orders_types_ids);
						$types_ids = array_combine($search_types_ids, $search_types_ids);
					}
					if ($user_id && ($orders_items_type == 1 || count($items_ids) > 0 || count($types_ids) > 0)) {
						// check if user buy something in the past
						$sql  = " SELECT oi.item_id, oi.item_type_id, oi.price, oi.quantity ";
						$sql .= " FROM ((" . $table_prefix . "orders_items oi ";
						$sql .= " INNER JOIN " . $table_prefix . "orders o ON o.order_id=oi.order_id) ";
						$sql .= " INNER JOIN " . $table_prefix . "order_statuses os ON oi.item_status=os.status_id) ";
						$sql .= " WHERE o.user_id=" . $db->tosql($user_id, INTEGER);
						$sql .= " AND os.paid_status=1 ";
						if ($orders_items_type > 1) {
							if (count($items_ids) > 0) {
								$sql .= " AND oi.item_id IN (" . $db->tosql($search_items_ids, INTEGERS_LIST) . ") ";
							}
							if (count($types_ids) > 0) {
								$sql .= " AND oi.item_type_id IN (" . $db->tosql($search_types_ids, INTEGERS_LIST) . ") ";
							}
						}
						if ($orders_period && $orders_interval) {
							$cd = va_time();
							if ($orders_period == 1) {
								$od = mktime (0, 0, 0, $cd[MONTH], $cd[DAY] - $orders_interval, $cd[YEAR]);
							} elseif ($orders_period == 2) {
								$od = mktime (0, 0, 0, $cd[MONTH], $cd[DAY] - ($orders_interval * 7), $cd[YEAR]);
							} elseif ($orders_period == 3) {
								$od = mktime (0, 0, 0, $cd[MONTH] - $orders_interval, $cd[DAY], $cd[YEAR]);
							} else {
								$od = mktime (0, 0, 0, $cd[MONTH], $cd[DAY], $cd[YEAR] - $orders_interval);
							}
							$sql .= " AND o.order_placed_date>=" . $db->tosql($od, DATETIME);
						}
						$db->query($sql);
						while ($db->next_record()) {
							$item_id = $db->f("item_id");
							$item_type_id = $db->f("item_type_id");
							$price = $db->f("price");
							$quantity = $db->f("quantity");
							$user_goods_total += ($price * $quantity);
							$user_qty_total += $quantity;
							if ($orders_items_type == 2) {
								if (isset($items_ids[$item_id])) { unset($items_ids[$item_id]); } 
								if (isset($types_ids[$item_type_id])) { unset($types_ids[$item_type_id]); }
							} else if ($orders_items_type == 3) {
								if (isset($items_ids[$item_id]) || isset($types_ids[$item_type_id])) {
									$past_orders_items = true;
								}
							}
  					}
						if ($orders_items_type == 2 && count($items_ids) == 0 && count($types_ids) == 0) {
							$past_orders_items = true;
						}
						if ($user_goods_total >= $orders_min_goods && ($user_goods_total <= $orders_max_goods || !strlen($orders_max_goods))
							&& $user_qty_total >= $orders_min_quantity && ($user_qty_total <= $orders_max_quantity || !strlen($orders_max_quantity))
						) {
							$past_orders = true;
						}
					}
				}

				// check for friends coupons
				$friends_coupon = false;
				$friends_discount_type = $data["friends_discount_type"];
				$friends_all = $data["friends_all"];
				$friends_ids = $data["friends_ids"];
				$friends_types_ids = $data["friends_types_ids"];
				$friends_period = $data["friends_period"];
				$friends_interval = $data["friends_interval"];
				$friends_min_goods = $data["friends_min_goods"];
				$friends_max_goods = $data["friends_max_goods"];
				$search_friends_ids = explode(",", $friends_ids);
				$search_friends_types_ids = explode(",", $friends_types_ids);
				if ($friends_discount_type == 1) {
					// check if user friends buy something
					$user_friends_goods = 0;
					if ($user_id) {
						$sql  = " SELECT SUM(o.goods_total) FROM (" . $table_prefix . "orders o ";
						$sql .= " INNER JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
						$sql .= " WHERE o.friend_user_id=" . $db->tosql($user_id, INTEGER);
						$sql .= " AND os.paid_status=1 ";
						if ($friends_period && $friends_interval) {
							$cd = va_time();
							if ($friends_period == 1) {
								$od = mktime (0, 0, 0, $cd[MONTH], $cd[DAY] - $friends_interval, $cd[YEAR]);
							} elseif ($friends_period == 2) {
								$od = mktime (0, 0, 0, $cd[MONTH], $cd[DAY] - ($friends_interval * 7), $cd[YEAR]);
							} elseif ($friends_period == 3) {
								$od = mktime (0, 0, 0, $cd[MONTH] - $friends_interval, $cd[DAY], $cd[YEAR]);
							} else {
								$od = mktime (0, 0, 0, $cd[MONTH], $cd[DAY], $cd[YEAR] - $friends_interval);
							}
							$sql .= " AND order_placed_date>=" . $db->tosql($od, DATETIME);
						}
						$user_friends_goods = get_db_value($sql);
					}
					if ($user_friends_goods >= $friends_min_goods && ($user_friends_goods <= $friends_max_goods || !strlen($friends_max_goods))) {
						$friends_coupon = true;
					}
				} elseif ($friends_discount_type == 2) {
					$friend_code = get_session("session_friend");
					$friend_user_id = get_friend_info(1);
					$friend_type_id = get_session("session_friend_type_id");

					$affiliate_user_id = get_friend_info(2);
					$affiliate_type_id = get_session("session_af_type_id");

					// check whose friends could use coupon
					if (($friends_all && ($friend_user_id || $affiliate_user_id)) 
						|| (($friend_user_id && in_array($friend_user_id, $search_friends_ids)) || ($affiliate_user_id && in_array($affiliate_user_id, $search_friends_ids))) 
						|| (($friend_type_id && in_array($friend_type_id, $search_friends_types_ids)) || ($affiliate_type_id && in_array($affiliate_type_id, $search_friends_types_ids)))) {

						$friends_coupon = true;
					}
				}

				// global options 
				$is_exclusive = $data["is_exclusive"];
				$quantity_limit = $data["quantity_limit"];
				$coupon_uses = $data["coupon_uses"];

				// check cart total values
				$min_cart_quantity = $data["min_cart_quantity"];
				$max_cart_quantity = $data["max_cart_quantity"];
				$min_cart_cost = $data["min_cart_cost"];
				$max_cart_cost = $data["max_cart_cost"];

				if ($discount_type <= 2) { $cart_items_all = 1; } // for order coupons always use all cart products to calculate totals
				check_cart_totals($cart_quantity, $cart_cost, $shopping_cart, $cart_items_all, $cart_items_ids, $cart_items_types_ids);

        // product specific fields
				$min_quantity = $data["min_quantity"];
				$max_quantity = $data["max_quantity"];
				$minimum_amount = $data["minimum_amount"];
				$maximum_amount = $data["maximum_amount"];

				// check if coupon can be applied
				if (!$is_active) {
					$coupon_error = va_constant("COUPON_NON_ACTIVE_MSG");
				} elseif ($quantity_limit > 0 && $coupon_uses >= $quantity_limit) {
					$coupon_error = va_constant("COUPON_USED_MSG");
				} elseif ($is_expired) {
					$coupon_error = va_constant("COUPON_EXPIRED_MSG");
				} elseif ($is_upcoming) {
					$coupon_error = va_constant("COUPON_UPCOMING_MSG");
				} elseif ($exclusive_applied || ($is_exclusive && $coupons_total > 0))  {
					$coupon_error = va_constant("COUPON_EXCLUSIVE_MSG");
				} elseif ($discount_type <= 4 && $min_cart_cost > $cart_cost) {
					$coupon_error = str_replace("{cart_amount}", currency_format($min_cart_cost), va_constant("MIN_CART_COST_ERROR"));
				} elseif ($discount_type <= 4 && $max_cart_cost && $max_cart_cost < $cart_cost) {
					$coupon_error = str_replace("{cart_amount}", currency_format($max_cart_cost), va_constant("MAX_CART_COST_ERROR"));
				} elseif ($discount_type <= 4 && $min_cart_quantity > $cart_quantity) {
					$coupon_error = str_replace("{min_quantity}", $min_cart_quantity, va_constant("COUPON_MIN_QTY_ERROR"));
				} elseif ($discount_type <= 4 && $max_cart_quantity && $max_cart_quantity < $cart_quantity) {
					$coupon_error = str_replace("{max_quantity}", $max_cart_quantity, va_constant("COUPON_MAX_QTY_ERROR"));
				} elseif (!($users_all || ($user_id && in_array($user_id, $search_users_ids)) 
					|| ($user_type_id && in_array($user_type_id, $search_users_types_ids)))) {
					$coupon_error = va_constant("COUPON_CANT_BE_USED_MSG"); // coupon can't be used for current user
				} elseif ($users_use_limit && !$user_not_limited) {
					// coupon can't be used more times
					if ($users_use_limit == 1) {
						$coupon_error = va_constant("COUPON_CAN_BE_USED_ONCE_MSG"); 
					} else {
						$coupon_error = str_replace("{use_limit}", $users_use_limit, va_constant("COUPON_SAME_USE_LIMIT_MSG"));
					}
				} elseif ($friends_discount_type > 0 && !$friends_coupon) {
					$coupon_error = va_constant("COUPON_CANT_BE_USED_MSG"); // coupon has friends options which can't be used for current user
				} elseif (!$past_orders || !$past_orders_items) {
					$coupon_error = va_constant("COUPON_CANT_BE_USED_MSG"); // the sum of user purchased goods doesn't match with goods values for this coupon
				} // end coupons checks

				if (!$coupon_error) {
					// check products coupons 
					$coupon_items = false;
					foreach($shopping_cart as $cart_id => $item)
					{
						$item_id = $item["ITEM_ID"];
						$item_type_id = $item["ITEM_TYPE_ID"];
						$categories_ids = isset($item["CATEGORIES_IDS"]) ? $item["CATEGORIES_IDS"] : ""; // doesn't available for subscriptions
						if (!$item_id) { 
							// ignore non-products items 
							continue;
						}
						$quantity = $item["QUANTITY"];
						$basic_price = $item["BASIC_PRICE"];
						$discounted_price = $item["DISCOUNTED_PRICE"];
						// add a new coupon
						if ($discount_type == 3 || $discount_type == 4) {
							// check categories 
							$category_found = false;
							if (is_array($search_items_categories_ids) && is_array($categories_ids) && sizeof($search_items_categories_ids) && sizeof($categories_ids)) {
								foreach ($categories_ids as $id) {
									if (in_array($id, $search_items_categories_ids)) {
										$category_found = true;
										break;
									}
								}
							}
							// end categories check

							// check subcomponents coupon option
							$subitems_found = false; $subitems_only = false; $subitems_check = false;
							if ($items_rule == 2) { 
								$subitems_only = true; 
							} else if ($items_rule == 3) { 
								$subitems_check = true; 
							}
							if ($items_rule == 2 || $items_rule == 3) {
								$item_components = $item["COMPONENTS"];
								if (is_array($item_components) && count($item_components) > 0) {
									foreach ($item_components as $property_id => $component_values) {
										foreach ($component_values as $property_item_id => $component) {
											$sub_item_id = $component["sub_item_id"];
											$sub_type_id = $component["item_type_id"];
											$component_price = $component["price"];
											if (!strlen($component_price) && isset($component["base_price"])) {
												$component_price = $component["base_price"];
											}
											$sub_quantity = $component["quantity"];
											$quantity_action = $component["quantity_action"];
									
											$component_quantity = ($quantity_action == 1) ? ($sub_quantity*$quantity) : $sub_quantity;
											if ($component_price >= $minimum_amount && 
												$component_quantity >= $min_quantity && 
												(!$maximum_amount || $component_price <= $maximum_amount) && 
												(!$max_quantity || $component_quantity <= $max_quantity) && 
												(in_array($sub_item_id, $search_items_ids) || in_array($sub_type_id, $search_items_types_ids)) ) {
												// add coupon for subcomponents
												$coupon_items = true;
												if ($discount_type == 3) {
													$discount_amount = round(($component_price / 100) * $coupon_discount, 2);
												} else {
													$discount_amount = $coupon_discount;
												}
												if ($discount_amount > $discounted_price) {
													$discount_amount = $discounted_price;
												}
												$shopping_cart[$cart_id]["DISCOUNTED_PRICE"] -= $discount_amount;
												if (!isset($shopping_cart[$cart_id]["COUPONS"][$new_coupon_id])) {
													// calculate number of new applied coupons
													$new_coupons_total++;
												} 
												$shopping_cart[$cart_id]["COUPONS"][$new_coupon_id] = array(
													"COUPON_ID" => $new_coupon_id, "EXCLUSIVE" => $is_exclusive, 
													"DISCOUNT_QUANTITY" => $discount_quantity,
													"DISCOUNT_AMOUNT" => $discount_amount, "AUTO_APPLY" => $coupon_auto_apply,
												);
												if ($is_exclusive) { $exclusive_applied = true; }
												$coupons_total++;
											}
										}
									}
								}
							}
							// end subcomponents coupon option

							if ($basic_price >= $minimum_amount && 
								$quantity >= $min_quantity && 
								(!$maximum_amount || $basic_price <= $maximum_amount) && 
								(!$max_quantity || $quantity <= $max_quantity) && 
								!$subitems_only && ($items_all || $category_found || in_array($item_id, $search_items_ids) || in_array($item_type_id, $search_items_types_ids)) ) {
								// add coupon to products
								$coupon_items = true;
								if ($discount_type == 3) {
									$discount_amount = round(($basic_price / 100) * $coupon_discount, 2);
								} else {
									$discount_amount = $coupon_discount;
								}
								if ($discount_amount > $discounted_price) {
									$discount_amount = $discounted_price;
								}
								$shopping_cart[$cart_id]["DISCOUNTED_PRICE"] -= $discount_amount;
								if (!isset($shopping_cart[$cart_id]["COUPONS"][$new_coupon_id])) {
									// calculate number of new applied coupons
									$new_coupons_total++;
								}
								$shopping_cart[$cart_id]["COUPONS"][$new_coupon_id] = array(
									"COUPON_ID" => $new_coupon_id, "EXCLUSIVE" => $is_exclusive, 
									"DISCOUNT_QUANTITY" => $discount_quantity,
									"DISCOUNT_AMOUNT" => $discount_amount, "AUTO_APPLY" => $coupon_auto_apply,
								);
								if ($is_exclusive) { $exclusive_applied = true; }
								$coupons_total++;
							}
						}
					} 
					if (($discount_type == 3 || $discount_type == 4) && !$coupon_items) {
						$coupon_error = va_constant("COUPON_PRODUCTS_MSG");
					}
					// end products checks 
	    
					if ($discount_type == 6 || $discount_type == 7) { // save sales coupons
						$new_coupons_total++;
						$auto_coupons = get_session("session_auto_coupons");
						if (!is_array($auto_coupons)) { $auto_coupons= array(); }
						if (!isset($auto_coupons[$coupon_code])) {
							$auto_coupons[$coupon_code] = $coupon_code;
							set_session("session_auto_coupons", $auto_coupons);
						}
					} else if ($discount_type <= 2 || $discount_type == 5) { // check order coupons

						if (!isset($order_coupons[$new_coupon_id])) {
							$new_coupons_total++;
						}
						// add new coupon to system
						$order_coupons[$new_coupon_id] = array(
							"COUPON_ID" => $new_coupon_id, "DISCOUNT_TYPE" => $discount_type, 
							"EXCLUSIVE" => $is_exclusive, "COUPON_TAX_FREE" => $coupon_tax_free, 
							"MIN_QUANTITY" => $min_cart_quantity, "MAX_QUANTITY" => $max_cart_quantity, 
							"MIN_AMOUNT" => $min_cart_cost, "MAX_AMOUNT" => $max_cart_cost, 
							"ORDER_TAX_FREE" => $coupon_order_tax_free, "AUTO_APPLY" => $coupon_auto_apply,
						);
						if ($is_exclusive) { $exclusive_applied = true; }
						$coupons_total++;
					}
					// end order coupons checks
				}
	  
				if (strtolower($coupon_code) == strtolower($new_coupon_code) && $coupon_error) {
					$new_coupon_error = $coupon_error;
				}
			} // cycle end of new coupons check

		}
		// end check a new coupons and auto-applied coupons

		// update shopping cart and order coupons
		set_session("shopping_cart", $shopping_cart);
		set_session("session_coupons", $order_coupons);

		// return number of applied coupons
		return $new_coupons_total;
	}

	function check_cart_totals(&$cart_quantity, &$cart_cost, $shopping_cart, $cart_items_all = 1, $cart_items_ids = "", $cart_items_types_ids = "")
	{
		$cart_quantity = 0; $cart_cost = 0;
		if (!isset($shopping_cart) || !is_array($shopping_cart)) {
			$shopping_cart = get_session("shopping_cart");
		}
		if (is_array($shopping_cart) && sizeof($shopping_cart)) {
			foreach ($shopping_cart as $cart_id => $info) {
				$item_id = $info["ITEM_ID"];
				if (!$item_id) { 
					continue;
				}
				$item_type_id = $info["ITEM_TYPE_ID"];
				$quantity = $info["QUANTITY"];
				$discounted_price = $info["DISCOUNTED_PRICE"];
				$search_items_ids = explode(",", $cart_items_ids);
				$search_items_types_ids = explode(",", $cart_items_types_ids);
				if ($cart_items_all == 1
					|| ($cart_items_all == 0 && (in_array($item_id, $search_items_ids) || in_array($item_type_id, $search_items_types_ids)) )
					|| ($cart_items_all == 2 && !in_array($item_id, $search_items_ids) && !in_array($item_type_id, $search_items_types_ids))
				) {
					$cart_quantity += $quantity;
					$cart_cost += ($quantity * $discounted_price);
				}
			}
		}
	}

	function prepare_product_params()
	{
		global $currency, $settings;

		$product_params["cleft"] = $currency["left"];
		$product_params["cright"] = $currency["right"];
		$product_params["crate"] = $currency["rate"];
		$product_params["cdecimals"] = $currency["decimals"];
		$product_params["cpoint"] = $currency["point"];
		$product_params["cseparator"] = $currency["separator"];

		$show_prices = get_setting_value($settings, "tax_prices", 0);
		$product_params["show_prices"] = $show_prices; 
		$product_params["tax_prices_type"] = get_setting_value($settings, "tax_prices_type", 0); 
		$product_params["points_rate"] = get_setting_value($settings, "points_conversion_rate", 1); 
		$product_params["points_decimals"] = get_setting_value($settings, "points_decimals", 0);
		$product_params["zero_price_type"] = get_setting_value($settings, "zero_price_type", 0);
		$product_params["zero_price_message"] = get_translation(get_setting_value($settings, "zero_price_message", "")); 
		$product_params["zero_product_action"] = get_setting_value($settings, "zero_product_action", 1); 
		$product_params["zero_product_warn"] = get_translation(get_setting_value($settings, "zero_product_warn", "")); 
		if ($show_prices == 2) {
			$tax_note = get_translation(get_setting_value($settings, "tax_note_excl", ""));
		} else {
			$tax_note = get_translation(get_setting_value($settings, "tax_note", ""));
		}
		$product_params["tax_note"] = $tax_note;

		return $product_params;
	}

	function set_product_params($product_params)
	{
		global $t, $currency, $settings;
		$params = "";
		foreach($product_params as $param_name => $param_value) {
			if ($params) { $params .= "#"; }
			$param_value = prepare_js_value($param_value);
			$params .= $param_name."=".$param_value;
		}
		$t->set_var("product_params", $params);
	}

	function calculate_control_price($values_ids, $values_text, $property_price_type, $property_price_amount, $free_price_type, $free_price_amount)
	{
		$controls_price = 0;
		$used_controls = 0; $free_controls = 0;
		$controls_text = ""; $free_letters = 0;
		$property_price_amount = doubleval($property_price_amount);
		// if property has some specified values
		if (sizeof($values_ids) > 0) {
			if ($free_price_amount != 1) {
				$free_price_amount = intval($free_price_amount);
			}
			if ($free_price_type == 2) {
				$free_controls = $free_price_amount;
			} else if ($free_price_type == 3 || $free_price_type == 4) {
				$free_letters = $free_price_amount;
			}
	  
			foreach ($values_ids as $id => $value) {
				$used_controls++;
				if (isset($values_text[$value])) {
					$controls_text .= $values_text[$value];
				}
				if ($free_controls >= $used_controls) {
					if ($property_price_type == 3) {
						$free_letters = strlen($controls_text);
					} else if ($property_price_type == 4) {
						$non_space_text = preg_replace("/[\n\r\s]/", "", $controls_text);
						$free_letters = strlen($non_space_text);
					}
				}
			}	
			if ($property_price_type == 1) {
				$controls_price += $property_price_amount;
			} else if ($property_price_type == 2) {
				if ($used_controls > $free_letters) {
					$controls_price += ($property_price_amount * ($used_controls - $free_controls));
				}
			} else if ($property_price_type == 3) {
				$text_length = strlen($controls_text);
				if ($text_length > $free_letters) {
					$controls_price += ($property_price_amount * ($text_length - $free_letters));
				}
			} else if ($property_price_type == 4) {
				$text_length = strlen(preg_replace("/[\n\r\s]/", "", $controls_text));
				if ($text_length > $free_letters) {
					$controls_price += ($property_price_amount * ($text_length - $free_letters));
				}
			}
			if ($free_price_type == 1) {
				$controls_price -= $free_price_amount;
			}
		}
		return $controls_price;
	}

	function product_image_fields($image_type, &$image_type_name, &$image_field, &$image_alt_field, &$watermark, &$product_no_image)
	{
		global $settings;
		if ($image_type == 1) {
			$image_type_name = "tiny";
			$image_field = "tiny_image";
			$image_alt_field = "tiny_image_alt";
			$watermark = get_setting_value($settings, "watermark_tiny_image", 0);
			$product_no_image = get_setting_value($settings, "product_no_image_tiny", "");
		} elseif ($image_type == 2) {
			$image_type_name = "small";
			$image_field = "small_image";
			$image_alt_field = "small_image_alt";
			$watermark = get_setting_value($settings, "watermark_small_image", 0);
			$product_no_image = get_setting_value($settings, "product_no_image", "");
		} elseif ($image_type == 3) {
			$image_type_name = "large";
			$image_field = "big_image";
			$image_alt_field = "big_image_alt";
			$watermark = get_setting_value($settings, "watermark_big_image", 0);
			$product_no_image = get_setting_value($settings, "product_no_image_large", "");
		} elseif ($image_type == 4) {
			$image_type_name = "super";
			$image_field = "super_image";
			$image_alt_field = "big_image_alt";
			$watermark = get_setting_value($settings, "watermark_super_image", 0);
			$product_no_image = get_setting_value($settings, "product_no_image", "");
		} else {
			$image_type_name = "no";
			$image_field = ""; $image_alt_field = "";
			$watermark = ""; $product_no_image = "";
		}
	}

	function product_image_names($image_type, &$image_type_name, &$image_field, &$image_alt_field, &$watermark_name, &$no_image_name)
	{
		global $settings;
		if ($image_type == 1) {
			$image_type_name = "tiny";
			$image_field = "tiny_image";
			$image_alt_field = "tiny_image_alt";
			$watermark_name = "watermark_tiny_image";
			$no_image_name = "product_no_image_tiny";
		} elseif ($image_type == 2) {
			$image_type_name = "small";
			$image_field = "small_image";
			$image_alt_field = "small_image_alt";
			$watermark_name = "watermark_small_image";
			$no_image_name = "product_no_image";
		} elseif ($image_type == 3) {
			$image_type_name = "large";
			$image_field = "big_image";
			$image_alt_field = "big_image_alt";
			$watermark_name = "watermark_big_image";
			$no_image_name = "product_no_image_large";
		} elseif ($image_type == 4) {
			$image_type_name = "super";
			$image_field = "super_image";
			$image_alt_field = "big_image_alt";
			$watermark_name = "watermark_super_image";
			$no_image_name = "product_no_image";
		} else {
			$image_field = ""; $image_alt_field = "";
			$watermark = ""; $product_no_image = "";
		}
	}
	
	function product_image_icon($item_id, $title, $image, $image_type, $text = false) 
	{
		global $settings, $root_folder_path, $is_ssl;

		$site_url = get_setting_value($settings, "site_url", "");
		$secure_url = get_setting_value($settings, "secure_url", "");
		if ($is_ssl) {
			$absolute_url = $secure_url;		
		} else {
			$absolute_url = $site_url;
		}
		$open_large_image = get_setting_value($settings, "open_large_image", 0);
		$property_function = ($open_large_image) ? "popupImage(this, '".$site_url."'); return false;" : "openImage(this); return false;";			
		$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");
		
		if ($image_type == 1) {
			$type = "tiny";
			$watermark = get_setting_value($settings, "watermark_tiny_image", 0);
		} elseif ($image_type == 2) {
			$type = "small";
			$watermark = get_setting_value($settings, "watermark_small_image", 0);
		} elseif ($image_type == 3) {
			$type = "large";
			$watermark = get_setting_value($settings, "watermark_big_image", 0);
		} elseif ($image_type == 4) {
			$type = "super";
			$watermark = get_setting_value($settings, "watermark_super_image", 0);
		}
		if (!preg_match("/^([a-zA-Z]*):\/\/(.*)/i", $image)) {			
			if ($watermark || $restrict_products_images) { 
				$image = $site_url . "image_show.php?item_id=" . $item_id . "&type=" . $type . "&vc=".md5($image); 
			} else {
				$image = $absolute_url.$image;
			}
		}
		$property_control  = "<a class=\"image-view\" href=\"" . htmlspecialchars($image) .  "\" ";
		$property_control .= " title=\"" . htmlspecialchars($title) . "\" onclick=\"" . $property_function . "\">";
		if ($text) {
			$property_control .= $text;
		} else {
			$property_control .= "<img src='". $absolute_url . "images/icons/view_page.gif' width='16' height='16' alt='View' border='0'>";
		}
		$property_control .= "</a>";				
		
		return $property_control;
	}
	
	function is_new_product($new_product_date = false) 
	{
		global $settings, $table_prefix, $db;
		$new_product_enable = get_setting_value($settings, "new_product_enable", 0);
		if (!$new_product_enable) return false;		
		if (!$new_product_date) return false;
		
		$new_date = strtotime($new_product_date);		
		
		$new_product_range = get_setting_value($settings, "new_product_range", 0);
		switch ($new_product_range) {
			case 0:
				// last week
				$limit_date = strtotime("-7 days");
			break;
			case 1:
				// last month
				$limit_date = strtotime("-30 days");				
			break;
			case 2:
				// last x days
				$new_product_x_days = get_setting_value($settings, "new_product_x_days", 0);
				$limit_date = strtotime("-" . $new_product_x_days ." days");				
			break;
			case 3:
				// from date
				$new_product_from_date = get_setting_value($settings, "new_product_from_date", "");
				$limit_date = strtotime($new_product_from_date);				
			break;
		}
		
		return ($limit_date < $new_date);		
	}

	function recalculate_shopping_cart()
	{
		$shopping_cart = get_session("shopping_cart");
		if (is_array($shopping_cart) && sizeof($shopping_cart) > 0) {
			foreach($shopping_cart as $cart_id => $item) {
				get_item_info($item);
				$shopping_cart[$cart_id] = $item;
				// update components
				$parent_quantity = $item["QUANTITY"];
				$components = $item["COMPONENTS"];
				if (is_array($components) && sizeof($components) > 0) {
					foreach ($components as $property_id => $component_values) {
						foreach ($component_values as $item_property_id => $component) {
							$property_type_id = $component["type_id"];
							$component = get_component_info($property_id, $item_property_id, $property_type_id, $parent_quantity);
							$components[$property_id][$item_property_id] = $component;
						}
					}
					$shopping_cart[$cart_id]["COMPONENTS"] = $components;
				}
			}
			set_session("shopping_cart", $shopping_cart);
			// after recalculate shopping cart always update coupons
			check_coupons();
		}
	}

	function property_sizes($property_id, $property_width, $property_height, &$size_price, &$min_width, &$max_width, &$min_height, &$max_height, &$prices)
	{
		global $db, $table_prefix;
		$db_rsi = $db->set_rsi("s");

		$prices = array();
		$min_width = 0; $max_width = 0; 
		$min_height = 0; $max_height = 0; 
		$size_price = 0;
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
				if (!isset($prices[$width])) { $prices[$width] = array(); }
				$prices[$width][$height] = $price;
			} while ($db->next_record());
		}

		foreach($prices as $cur_width => $height_prices) {
			if ($property_width <= $cur_width) {
				foreach($height_prices as $cur_height => $cur_price) {
					if ($property_height <= $cur_height) {
						$size_price = $cur_price;
						break;
					}
				}
				break;
			}
		}
		$db->set_rsi($db_rsi);
	}
