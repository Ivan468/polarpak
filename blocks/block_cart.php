<?php

	include_once("./includes/shopping_cart.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	$default_title = SMALL_CART_TITLE;
	$ajax = get_param("ajax");

	$block_type = get_setting_value($vars, "block_type", "");
	if ($block_type != "bar" && $block_type != "header") {
		$html_template = get_setting_value($block, "html_template", "block_cart.html"); 
	  $t->set_file("block_body", $html_template);
	}
	$t->set_var("cart_href", get_custom_friendly_url("basket.php"));
	$t->set_var("basket_href", get_custom_friendly_url("basket.php"));
	$t->set_var("checkout_href", get_custom_friendly_url("checkout.php"));

	// necessary scripts to work with cart
	if (!$ajax) {
		set_script_tag("js/shopping.js");
		set_script_tag("js/basket.js");
		set_script_tag("js/images.js");
		set_script_tag("js/blocks.js");
		set_script_tag("js/ajax.js");
	}

	$tax_prices_type = get_setting_value($settings, "tax_prices_type", 0);
	$tax_prices = get_setting_value($settings, "tax_prices", 0);

	// check if there are any coupon with order tax free option
	$order_tax_free = false;
	$coupons = get_session("session_coupons");
	if (is_array($coupons)) {
		foreach ($coupons as $coupon_id => $coupon_info) {
			$coupon_order_tax_free = $coupon_info["ORDER_TAX_FREE"];
			if ($coupon_order_tax_free) {
				$order_tax_free = true;
				break;
			}
		}
	}

	$tax_rates     = get_session("session_tax_rates");
	$shopping_cart = get_session("shopping_cart");
	$total_quantity = 0; $total_price = 0; $goods_excl_tax = 0; $goods_incl_tax = 0;
	if(is_array($shopping_cart) && sizeof($shopping_cart) > 0) {
		
		$t->set_var("empty_small_cart", "");
		$t->set_var("small_cart_items", "");

		$user_info = get_session("session_user_info");
		$user_tax_free = get_setting_value($user_info, "tax_free", 0);
		$discount_type = get_session("session_discount_type");
		$discount_amount = get_session("session_discount_amount");

		foreach($shopping_cart as $cart_id => $item)
		{
			if (!$item || !(isset($item["ITEM_ID"]))) {
				continue;
			}
			$item_id = $item["ITEM_ID"];
			
			$item_type_id = $item["ITEM_TYPE_ID"];
			$item_name = get_translation($item["ITEM_NAME"]);
			if (strlen($item_name) < 20) {
				$short_name = $item_name;
			} else if (preg_match("/^.{10}[^\s\&\+\-\_\.\(,]{0,8}/", $item_name, $matches)) {
				$short_name = $matches[0];
			} else {
				$short_name = substr($item_name, 0, 18);
			}
			$properties = $item["PROPERTIES"];
			$properties_info = isset($item["PROPERTIES_INFO"]) ? $item["PROPERTIES_INFO"] : "";
			$quantity = $item["QUANTITY"];
			$tax_id = $item["TAX_ID"];
			$tax_free = $item["TAX_FREE"];
			if ($user_tax_free || $order_tax_free) { $tax_free = true; }
			$discount_applicable = $item["DISCOUNT"];
			$buying_price = $item["BUYING_PRICE"];
			$price = $item["PRICE"];
			$is_price_edit = $item["PRICE_EDIT"];
			$properties_price = $item["PROPERTIES_PRICE"];
			$properties_percentage = $item["PROPERTIES_PERCENTAGE"];
			$properties_buying = $item["PROPERTIES_BUYING"];
			$properties_discount = $item["PROPERTIES_DISCOUNT"]; // properties discount based on selected quantity
			$components = $item["COMPONENTS"];				
			//$discount_total = 0;
			if ($discount_applicable) {
				if (!$is_price_edit) {
					if ($discount_type == 1) {
						$price -= round(($price * $discount_amount) / 100, 2);
					} else if ($discount_type == 2) {
						$price -= round($discount_amount, 2);
					} else if ($discount_type == 3) {
						$price -= round(($price * $discount_amount) / 100, 2);
					} else if ($discount_type == 4) {
						$price -= round((($price - $buying_price) * $discount_amount) / 100, 2);
					}
				}
			} 
			// calculate propeties data
			$properties_price = 0; $properties_buying = 0;
			if (is_array($properties_info)) {
				foreach ($properties_info as $property_id => $property) {
					$control_type = strtoupper($property["CONTROL"]);
					$properties_price += $property["CONTROL_PRICE"] + $property["PRICE"];
					// for listbox controls check their percentage and buying prices
					if (($control_type) == "LISTBOX" || ($control_type) == "RADIOBUTTON"
						|| ($control_type) == "CHECKBOXLIST" || ($control_type) == "TEXTBOXLIST") {
						$percentage_price_type = $property["PERCENTAGE_PRICE_TYPE"];
						$percentage_property_id = $property["PERCENTAGE_PROPERTY_ID"];
						$values_info = $property["VALUES_INFO"];
						foreach ($values_info as $value_id => $value_data) {
							$properties_buying += doubleval($value_data["BUYING"]);
							$value_percentage = $value_data["PERCENTAGE"];
							if ($value_percentage) {
								if (($percentage_price_type == 1 || $percentage_price_type == 3) && $price) {
									$properties_price += round(($price * $value_percentage) / 100, 2);
								} 	
								if (($percentage_price_type == 2 || $percentage_price_type == 3) && $percentage_property_id) {
									$parent_price = 0;
									if (isset($properties_info[$percentage_property_id])) {
										$parent_price = $properties_info[$percentage_property_id]["CONTROL_PRICE"] + $properties_info[$percentage_property_id]["PRICE"];
									}
									$properties_price += round(($parent_price * $value_percentage) / 100, 2);
								}
							}
						}
					}
				}
			}
			if ($properties_discount > 0) {
				$properties_price -= round(($properties_price * $properties_discount) / 100, 2);
			}
			if ($discount_applicable) {
				if ($discount_type == 1) {
					$properties_price -= round((($properties_price) * $discount_amount) / 100, 2);
				} else if ($discount_type == 4) {
					$properties_price -= round((($properties_price - $properties_buying) * $discount_amount) / 100, 2);
				}
			}
			$price += $properties_price;

			// subtract discount from price
			if (isset($item["COUPONS"]) && is_array($item["COUPONS"])) {
				foreach ($item["COUPONS"] as $coupon_id => $coupon_info) {
					$item_discount_amount = $coupon_info["DISCOUNT_AMOUNT"];
					$coupon_discount_quantity = $coupon_info["DISCOUNT_QUANTITY"];
					if ($coupon_discount_quantity > 1) {
						$discount_number = intval($quantity / $coupon_discount_quantity) * $coupon_discount_quantity;
					} else {
						$discount_number = $quantity;
					}
					$item_discount_amount = ($item_discount_amount * $discount_number) / $quantity;
					$price -= $item_discount_amount;
				}
			}

			// check the tax for basic price
			$tax_values = get_tax_amount($tax_rates, $item_type_id, $price, 1, $tax_id, $tax_free, $tax_percent, "", 2);
			$item_total = $price * $quantity;

			$tax_amount 						= get_tax_amount($tax_rates, $item_type_id, $price, 1, $tax_id, $tax_free, $tax_percent);
			$item_tax_total_values 	= get_tax_amount($tax_rates, $item_type_id, $item_total, $quantity, $tax_id, $tax_free, $tax_percent, "", 2);
			$item_tax_total 				= add_tax_values($tax_rates, $item_tax_total_values, "products");

			if ($tax_prices_type == 1) {
				$price_excl_tax = $price - $tax_amount;
				$price_incl_tax = $price;
				$price_excl_tax_total = $item_total - $item_tax_total;
				$price_incl_tax_total = $item_total;
			} else {
				$price_excl_tax = $price;
				$price_incl_tax = $price + $tax_amount;
				$price_excl_tax_total = $item_total;
				$price_incl_tax_total = $item_total + $item_tax_total;
			}

			// total goods values
			$goods_excl_tax += $price_excl_tax_total; 
			$goods_incl_tax += $price_incl_tax_total;			

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
						$sub_qty_action = isset($component["quantity_action"]) ? $component["quantity_action"] : 1;
						if ($sub_quantity < 1)  { $sub_quantity = 1; }
						$sub_type_id = $component["item_type_id"];
						if (!strlen($component_price)) {
							$sub_price = $component["base_price"];
							$sub_buying = $component["buying"];
							$sub_user_price = $component["user_price"];
							$sub_user_action = $component["user_price_action"];
							$sub_prices = get_product_price($sub_item_id, $sub_price, $sub_buying, 0, 0, $sub_user_price, $sub_user_action, $discount_type, $discount_amount);
							$component_price = $sub_prices["base"];
						}
						// check the price including the tax
						$component_tax_amount = get_tax_amount($tax_rates, $sub_type_id, $component_price, 1, $component_tax_id, $component_tax_free, $component_tax_percent); 
						if ($tax_prices_type == 1) {
							$component_price_excl_tax = $component_price - $component_tax_amount;
							$component_price_incl_tax = $component_price;
						} else {
							$component_price_excl_tax = $component_price;
							$component_price_incl_tax = $component_price + $component_tax_amount;
						}

						if ($sub_qty_action == 2) {
							$goods_excl_tax += ($component_price_excl_tax * $sub_quantity); 
							$goods_incl_tax += ($component_price_incl_tax * $sub_quantity);
							$price_excl_tax += ($component_price_excl_tax * $sub_quantity / $quantity); 
							$price_incl_tax += ($component_price_incl_tax * $sub_quantity / $quantity);
						} else {
							$goods_excl_tax += ($component_price_excl_tax * $sub_quantity * $quantity); 
							$goods_incl_tax += ($component_price_incl_tax * $sub_quantity * $quantity);
							$price_excl_tax += ($component_price_excl_tax * $sub_quantity); 
							$price_incl_tax += ($component_price_incl_tax * $sub_quantity);
						}
					}
				}
			}

			if ($tax_prices > 0) {
				$price = $price_incl_tax;
			} else {
				$price = $price_excl_tax;
			}

			$total_quantity += $quantity;
			//$total_price += ($quantity * $price);

			$t->set_var("item_name", htmlspecialchars($item_name));
			$t->set_var("short_name", htmlspecialchars($short_name));
			$t->set_var("quantity", $quantity);
			$t->set_var("price", currency_format($price));

			$t->sparse("small_cart_items", true);
		}
	}

	if ($total_quantity > 0) {

		// get total price
		if ($tax_prices > 0) {
			$total_price = $goods_incl_tax;
		} else {
			$total_price = $goods_excl_tax;
		}

		$t->set_var("total_quantity", $total_quantity);
		$t->set_var("cart_quantity", $total_quantity);
		$t->set_var("total_price", currency_format($total_price));
		$t->set_var("cart_amount", currency_format($total_price));
		$t->set_var("cart_total", currency_format($total_price));

		$t->parse("small_cart", false);
	} else {
		$t->set_var("total_quantity", 0);
		$t->set_var("cart_quantity", 0);
		$t->set_var("total_price", currency_format(0));
		$t->set_var("cart_amount", currency_format(0));
		$t->set_var("cart_total", currency_format(0));

		$t->set_var("EMPTY_CART_MSG", EMPTY_CART_MSG);
		$t->sparse("empty_small_cart", false);
		$t->set_var("small_cart", "");
	}

	$block_parsed = true;

?>