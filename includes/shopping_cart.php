<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  shopping_cart.php                                        ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	global $sc_params, $sc_errors, $sc_notice, $sc_message, $language_code;
	include_once(dirname(__FILE__)."/../messages/".$language_code."/cart_messages.php");
	include_once(dirname(__FILE__)."/products_functions.php");
	include_once(dirname(__FILE__)."/shopping_functions.php");

	// define shopping cart parameters to use in forms
	$confirm_add   = get_setting_value($settings, "confirm_add", 1);
	$sc_params = array(
		"msgRequiredProperty" => va_constant("REQUIRED_PROPERTY_MSG"),
		"msgMinMax" => va_constant("MIN_MAX_VALUE_MSG"),
		"msgAddProduct" => va_constant("CONFIRM_ADD_PRODUCT_MSG"),
		"msgAddSubscription" => va_constant("CONFIRM_SUBSCRIPTION_MSG"),
		"msgSelectProduct" => va_constant("SELECT_ONE_PRODUCT_MSG"),
		"confirmAdd" => $confirm_add,
	);

	// check if we need to add coupon to session 
	$auto_coupon = get_param("auto_coupon");
	if ($auto_coupon) {
		$auto_coupons = get_session("session_auto_coupons");
		if (!is_array($auto_coupons)) { $auto_coupons= array(); }
		if (!in_array($auto_coupon, $auto_coupons)) {
			$auto_coupons[] = $auto_coupon;
			set_session("session_auto_coupons", $auto_coupons);
		}
	}

	// clear user_order_id if it's not checkout scripts
	if (!isset($cms_page_code)) { $cms_page_code = ""; }
	$user_order_id = get_session("session_user_order_id");
	if ($user_order_id && strlen($cms_page_code) && $cms_page_code != "order_info" && $cms_page_code != "opc" && $cms_page_code != "order_confirmation" && $cms_page_code != "payment" && $cms_page_code != "order_final") {
		$user_order_id = "";
		set_session("session_vc", "");
		set_session("session_order_id", "");
		set_session("session_user_order_id", "");
	}

	$eol = get_eol();
	$sc_errors = ""; $sc_notice = ""; $sc_message = "";	$sc_item_id = ""; $sc_items = array(); $sc_indexes = array();
	$cart = get_param("cart");
	$cart_id = get_param("cart_id");
	if ($cart)
	{
		$placed_ids = get_session("placed_ids");
		if (!is_array($placed_ids)) {
			$placed_ids = array();
		}
		$random_id = get_param("rnd");

		//-- checking if such page has been already called
		if (!strlen($random_id) || !isset($placed_ids[$random_id]))
		{
			if ($cart != "SHIPPING") { // allow to add items for shipping calculations
				$placed_ids[$random_id] = $random_id;
			}

			switch (strtoupper($cart))
			{
				case "SHIPPINGADD": 
				case "CHECKOUT": 
				case "GOTOCHECKOUT":
					$saved_shipping_types = array();
					$shipping_groups_number = get_param("shipping_groups_number");
					for ($sg = 1; $sg <= $shipping_groups_number; $sg++) {
						$st_id = get_param("shipping_type_id_".$sg);
						$saved_shipping_types["shipping_type_id_".$sg] = $st_id;
					}
					set_session("session_shipping_types", $saved_shipping_types);
					if ($cart == "GOTOCHECKOUT") {
						// save shipping method and go to checkout
						header("Location: " . "checkout.php");
						exit;
						break;
					}
				case "ADD": // add item to the cart
				case "SHIPPING": // add item to the cart only to check shipping
				case "COMPARE": // add item to the compare list
				case "WISHLIST": // add item to wish list
				case "SHIPPINGADD": // add item to the cart from shipping calculator
				case "CHECKOUT": // add item to the cart and move to checkout
					$item_number = 0;
					// check for start index
					$start_index = get_param("start_index");
					$end_index = get_param("end_index");
					$final_index = get_param("final_index");
					$item_index = get_param("item_index");
					$items_indexes = trim(get_param("items_indexes"));
					$indexes = array();
					if (strlen($items_indexes)) {
						$indexes = explode(",", $items_indexes);
					}
					// check initial index
					$index = "";
					if (strlen($item_index)) { $index = $item_index; }
					else if (sizeof($indexes) > 0) { $index = $indexes[0]; }
					else if ($start_index) { $index = $start_index ; }
					$item_params = array("add_id", "cart_code", "cart_item_code", "cart_man_code", "item_id", "accessory_id", "item_code", "manufacturer_code");

					// initialize all_items_added variable as false
					$items_added = 0;
					$items_ignored = 0;
					do {
						$item_number++;
						// check all item parameters to check value
						$is_param_value = false;
						foreach ($item_params as $param_name) {
							$param_value = get_param($param_name.$index);
							if (strlen($param_value)) { $is_param_value = true; break; }
						}

						if ($is_param_value) {
							$item_id = get_param("add_id".$index);
							if (!strlen($item_id)) {
								$cart_code = get_param("cart_code".$index);
								$cart_item_code = get_param("cart_item_code".$index);
								$cart_man_code = get_param("cart_man_code".$index);
								if (strlen($cart_code)) {
									$sql  = " SELECT item_id FROM " . $table_prefix . "items ";
									$sql .= " WHERE item_code=" . $db->tosql($cart_code, TEXT);
									$sql .= " OR manufacturer_code=" . $db->tosql($cart_code, TEXT);
									$item_id = get_db_value($sql);
								} else if (strlen($cart_item_code)) {
									$sql = " SELECT item_id FROM " . $table_prefix . "items WHERE item_code=" . $db->tosql($cart_item_code, TEXT);
									$item_id = get_db_value($sql);
								} else if (strlen($cart_man_code)) {
									$sql = " SELECT item_id FROM " . $table_prefix . "items WHERE manufacturer_code=" . $db->tosql($cart_man_code, TEXT);
									$item_id = get_db_value($sql);
								}
							}
							if (!strlen($item_id)) {
								$item_id = get_param("item_id".$index);
							}
							if (!strlen($item_id)) {
								$item_code = get_param("item_code".$index);
								$manufacturer_code = get_param("manufacturer_code".$index);
								if (strlen($item_code)) {
									$sql = " SELECT item_id FROM " . $table_prefix . "items WHERE item_code=" . $db->tosql($item_code, TEXT);
									$item_id = get_db_value($sql);
								} else if (strlen($manufacturer_code)) {
									$sql = " SELECT item_id FROM " . $table_prefix . "items WHERE manufacturer_code=" . $db->tosql($manufacturer_code, TEXT);
									$item_id = get_db_value($sql);
								}
								if ($item_id) {
									$_GET["item_id"] = $item_id;
								}
							}
							$accessory_id = get_param("accessory_id".$index);
							$sc_item_id = $accessory_id ? $accessory_id : $item_id;
							$sc_price = get_param("price".$index);
							if ($cart == "COMPARE") {
								// when we are comparing items we don't need quantity parameter
								$sc_quantity = ""; 
							} else {
								$sc_quantity = get_param("quantity".$index);
								if (strlen($sc_quantity)) {
									// always round float value to nearest integer value
									$sc_quantity = ceil($sc_quantity);
								}

							}
							if ($cart == "WISHLIST" || $sc_quantity == 0) {
								// if there is no quantity for wishlist use default minimum value
								$sc_quantity = ""; 
							}
							$type_param_value = get_param("type");
							if ($type_param_value) { $type = $type_param_value; } else { $type = ""; }
							/* start of adding item to the cart */
							if (!strlen($sc_quantity) || $sc_quantity > 0) {
								$item_added = add_to_cart($sc_item_id, $index, $sc_price, $sc_quantity, $type, $cart, $new_cart_id, $sc_errors, $sc_message);
								if ($item_added) {
									$sc_items[] = $sc_item_id;
									$sc_indexes[$index] = $sc_item_id;
									$items_added++;
								} else {
									$items_ignored++;
								}
							}
							/* end of adding item to the cart */
							// check if any coupons can be added or removed
							check_coupons();
						}

						// check for next index only if particular index wasn't selected
						if (strlen($item_index)) { 
							$index = ""; 
						} else if (sizeof($indexes) > 0) { 
							$index = isset($indexes[$item_number]) ? $indexes[$item_number] : ""; 
						} else if ($final_index) {
							$index = ($final_index > $index) ? ($index++) : ""; 
						} else if ($end_index) {
							$index = ($end_index > $index) ? ($index++) : ""; 
						} else {
							$index = ""; 
						}
					} while ($index);
					// check param from request first
					$redirect_to_cart = get_param("redirect_to_cart");
					if (!strlen($redirect_to_cart)) {
						$redirect_to_cart = get_setting_value($settings, "redirect_to_cart", "");
					}
					if ($type == "options") {
						// redirect user to basket page after options update
						$cart_page = get_custom_friendly_url("basket.php");
						header("Location: " . $cart_page);
						exit;
					} else if ($cart == "CHECKOUT") {
						header("Location: " . get_custom_friendly_url("checkout.php"));
						exit;
					} else if ($redirect_to_cart != 3) {
						// redirect user to different page only if product wasn't added with Ajax
						if ($items_added && !$items_ignored && $cart != "SHIPPING" && $cart != "COMPARE" && $cart != "WISHLIST") {
							$rp = get_param("rp");
							if ($redirect_to_cart == 1) {
								$cart_page = strlen($rp) ? get_custom_friendly_url("basket.php") . "?rp=" . urlencode($rp) : get_custom_friendly_url("basket.php");
								header("Location: " . $cart_page);
								exit;
							} elseif ($redirect_to_cart == 2) {
								header("Location: " . get_custom_friendly_url("checkout.php"));
								exit;
							}
						}
					}
					break;
				case "SUBSCRIPTION": // add subscription to the cart
					$sc_subscription_id = get_param("subscription_id");
					$sc_group_id = get_param("group_id");

					/* start of adding item to the cart */
					$subscription_added = add_subscription(0, $sc_subscription_id, $sc_subscription_name, $sc_group_id);
					/* end of adding item to the cart */

					if ($subscription_added) {
						$rp = get_param("rp");
						if (isset($settings["redirect_to_cart"])) {
							if ($settings["redirect_to_cart"] == 1) {
								$cart_page = strlen($rp) ? get_custom_friendly_url("basket.php") . "?rp=" . urlencode($rp) : get_custom_friendly_url("basket.php");
								header("Location: " . $cart_page);
								exit;
							} elseif ($settings["redirect_to_cart"] == 2) {
								header("Location: " . get_custom_friendly_url("checkout.php"));
								exit;
							}
						}
					}

					break;
				case "CP": // copy product as new item 
				case "COPY": 
					$shopping_cart = get_session("shopping_cart");
					if (is_array($shopping_cart)) {
						$cart_id = get_param("cart_id");
						$shopping_cart[] = $shopping_cart[$cart_id];
						set_session("shopping_cart", $shopping_cart);
						// check if any coupons can be added or removed
						check_coupons();
					}
					break;
				case "RM": // remove the item from the cart
					$shopping_cart = get_session("shopping_cart");
					if (is_array($shopping_cart))
					{
						$cart_id = get_param("cart_id");
						db_cart_update("remove", $cart_id); // delete product from database 
						$cart_subscription_type_id = isset($shopping_cart[$cart_id]["SUBSCRIPTION_TYPE_ID"]) ? $shopping_cart[$cart_id]["SUBSCRIPTION_TYPE_ID"] : "";
						$new_user_type = get_session("session_new_user");
						unset($shopping_cart[$cart_id]);
						if ($cart_subscription_type_id && $new_user_type == "expired") {
							// in case user delete his account subscription then we need to remove his new user data
							set_session("session_new_user", "");
							set_session("session_new_user_id", "");
							set_session("session_new_user_type_id", "");
						}
				
						$cart_clear = false;
						if (sizeof($shopping_cart) == 0) {
							$cart_clear = true;
							unset($shopping_cart);
							set_session("shopping_cart", "");
							set_session("session_coupons", "");
						} else {
							set_session("shopping_cart", $shopping_cart);
							// check if any coupons can be added or removed
							check_coupons();
						}

					}
					break;
				case "RM-COMPARE": // remove the item from the compare list
					$compare_cart = get_session("compare_cart");
					$cart_id = get_param("cart_id");
					if (is_array($compare_cart) && isset($compare_cart[$cart_id])) {
						unset($compare_cart[$cart_id]);
						set_session("compare_cart", $compare_cart);
					}
					break;
				case "QTY": // update item quantity in the cart
					$shopping_cart = get_session("shopping_cart");
					if (is_array($shopping_cart) && isset($shopping_cart[$cart_id])) {

						$sc_item_id = $shopping_cart[$cart_id]["ITEM_ID"];
						$sc_price = $shopping_cart[$cart_id]["PRICE"];
						$new_quantity = get_param("new_quantity");
						$new_quantity = abs($new_quantity);
						$old_quantity = $shopping_cart[$cart_id]["QUANTITY"];
						$change_quantity = $new_quantity - $old_quantity;
						if ($change_quantity != 0) {
							$item_added = add_to_cart($sc_item_id, $cart_id, $sc_price, $change_quantity, $type, $cart, $new_cart_id, $sc_errors, $sc_message);
							// check if any coupons can be added or removed
							check_coupons();
						}
					}
					break;

				case "CLR": // remove all items from the cart
					$shopping_cart = get_session("shopping_cart");
					$new_user_type = get_session("session_new_user");
					if (is_array($shopping_cart)) {
						set_session("shopping_cart", "");
						set_session("session_coupons", "");
						if ($new_user_type == "expired") {
							// in case cart has subscription data we need to remove all new user data as well
							set_session("session_new_user", "");
							set_session("session_new_user_id", "");
							set_session("session_new_user_type_id", "");
						}
					}

					// begin: delete all products and cart from database 
					db_cart_update("clear");
					// end: delete all products from database 

					break;
			}
			set_session("placed_ids", $placed_ids);
		}
	}

