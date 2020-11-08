<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  cart_add.php                                             ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$type = "";
	include_once("./includes/common.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/items_properties.php");
	
	// fast order action
	$operation = get_param("operation");
	if ($operation == "fast_order") {
		$admin_permissions = get_admin_permissions();
		$call_center = get_setting_value($admin_permissions, "create_orders", 0);
		$cc_user_id = get_param("cc_user_id");
		$session_user_id = get_session("session_user_id");
		if ($call_center && $cc_user_id && $cc_user_id != $session_user_id) {
			user_login("", "", $cc_user_id, false, "", false, $errors);
		}
		// clear cart before adding a new items
		set_session("shopping_cart", "");
		set_session("session_coupons", "");
		if (function_exists("db_cart_update")) {
			db_cart_update("clear");
		}
		// automatically set required label options for selected product
		$item_id = get_param("item_id");
		$item_type_id = get_param("item_type_id");
		$sql  = " SELECT ip.property_id, ip.property_type_id, ip.property_description ";
		$sql .= " FROM (" . $table_prefix . "items_properties ip ";
		$sql .= " LEFT JOIN " . $table_prefix . "items_properties_sites ips ON ip.property_id=ips.property_id) ";
		$sql .= " WHERE (ip.item_id=" . $db->tosql($item_id, INTEGER) . " OR ip.item_type_id=" . $db->tosql($item_type_id, INTEGER) . ") ";
		$sql .= " AND (ip.sites_all=1 OR ips.site_id=" . $db->tosql($site_id, INTEGER) . ")";
		$sql .= " AND ip.show_for_user=1 AND ip.required=1 AND control_type='LABEL' ";
		$sql .= " ORDER BY ip.property_order, ip.property_id ";
		$db->query($sql);
		while ($db->next_record()) {
			$property_id = $db->f("property_id");
			$property_description = $db->f("property_description");
			$_POST["property_".$property_id] = $property_description;
		}
	}

	// include shopping cart script where we add new products to the cart
	include_once("./includes/shopping_cart.php");
	
	// set headers for block
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");
	header("Content-Type: text/html; charset=" . CHARSET);

	$shopping_cart = get_session("shopping_cart");
	$redirect_to_cart = get_param("redirect_to_cart");
	$callback = get_param("callback");
	$control_id = get_param("control_id");
	$form_name = get_param("form_name");
	$cart = get_param("cart");
	$cart_added_popup = get_param("cart_added_popup");
	$cart_popup_view = get_param("cart_popup_view");
	$cart_popup_checkout = get_param("cart_popup_checkout");


	$response = array();
	$response["cart"] = $cart;
	if ($form_name) { $response["form_name"] = $form_name; }
	if (strlen($control_id)) { $response["control_id"] = $control_id; }
	if (strlen($redirect_to_cart)) { $response["redirect_to_cart"] = $redirect_to_cart; }
	$response["added_indexes"] = $sc_indexes;
	// check if we need to hide Add button for some products
	$hide_add_indexes = array();
	$hide_add_limit = get_setting_value($settings, "hide_add_limit", ""); 
	if ($hide_add_limit) {
		foreach ($sc_indexes as $index => $sc_item_id) {
			$items_in_cart = 0; $items_max = 0;
			foreach ($shopping_cart as $cart_id => $cart_data) {
				if ($sc_item_id == $cart_data["ITEM_ID"]) {
					$items_in_cart += $cart_data["QUANTITY"];
					$items_max = $cart_data["MAX_QTY"];
				}
			}
			if ($items_in_cart && $items_max && $items_in_cart >= $items_max) {
				$hide_add_indexes[$index] = $sc_item_id;
			}
		}
	}
	$response["hide_add_indexes"] = $hide_add_indexes;
	// end of max allowed products check

	$blocks_parsed = 0;
	if ($redirect_to_cart == "popup" && $cart != "WISHLIST" && $cart != "COMPARE") {
		$tax_rates = get_tax_rates();
		// set products index for ajax page to 10000 to exclude overlaping indexes
		$va_data = array();
		$va_data["products_index"] = 10000;
		// check shopping cart messages to show for layout
		if (isset($sc_message) && $sc_message) {
			$layout_message = $sc_message;
		}
		if (isset($sc_errors) && $sc_errors) {
			$layout_message = $sc_errors;
		}

		$is_frame_layout = true;
		$cms_page_code = "add_to_cart_frame";
		$script_name   = "#";
		include_once("./includes/page_layout.php");
		$popup_page = $t->get_var("main");
		if ($blocks_parsed) {
			$response["block"] = $popup_page;
		}
	}

	if (!$blocks_parsed) {
		$templates_dir = get_setting_value($settings, "templates_dir", "./templates/user");
		$t = new VA_Template($templates_dir);
		if ($sc_errors) {
			$t->set_file("popup", "popup_error.html");
			$t->set_var("sc_errors", $sc_errors);
			$t->set_var("error_desc", $sc_errors);
			$t->parse("popup", false);
			$popup_error = $t->get_var("popup");
			$response["errors"] = $popup_error;
		} else if ($cart == "CLR") {
			$t->set_file("popup", "popup_message.html");
			$t->set_var("sc_message", va_message("EMPTY_CART_MSG"));
			$t->set_var("message_desc", va_message("EMPTY_CART_MSG"));
			$t->parse("popup", false);
			$popup_message = $t->get_var("popup");
			$response["success"] = 1;
			$response["message"] = $popup_message;
		} else if ($cart == "RM") {
			$response["success"] = 1; 
			$response["message"] = ""; // don't show any messages
		} else if ($sc_message) {
			$popup_message = "";
			if (!strlen($cart_added_popup)) {
				$cart_added_popup = get_setting_value($settings, "cart_added_popup");
			}
			if (!strlen($cart_popup_view)) {
				$cart_popup_view = get_setting_value($settings, "cart_popup_view");
			}
			if (!strlen($cart_popup_checkout)) {
				$cart_popup_checkout = get_setting_value($settings, "cart_popup_checkout");
			}
			$t->set_var("products_added", $sc_message);
			$t->set_var("sc_message", $sc_message);
			$t->set_var("compare_href", "compare.php");
			$t->set_var("basket_href",   get_custom_friendly_url("basket.php"));
			$t->set_var("checkout_href", get_custom_friendly_url("checkout.php"));
			$t->set_var("user_wishlist_href", "user_wishlist.php");
			if ($cart == "WISHLIST") {
				$t->set_file("popup", "popup_wishlist_added.html");
			} else if ($cart == "COMPARE") {
				$t->set_file("popup", "popup_compare_added.html");
			} else {
				$cart = "CART";
				$t->set_file("popup", "popup_cart_added.html");
				if ($cart_popup_view) { $t->sparse("view_button", false); }
				if ($cart_popup_checkout) { $t->sparse("checkout_button", false); }
			}
			if ($cart != "CART" || $cart_added_popup) {
				$t->parse("popup", false);
				$popup_message = $t->get_var("popup");
			}
			$response["success"] = 1;
			$response["message"] = $popup_message;
		} else {
			$t->set_file("popup", "popup_error.html");
			$t->set_var("sc_errors", va_message("ERRORS_MSG"));
			$t->set_var("error_desc", va_message("ERRORS_MSG"));
			$t->parse("popup", false);
			$popup_error = $t->get_var("popup");
			$response["errors"] = $popup_error;
		}
	}

	if ($callback) { echo "reloadCartBlocks("; }
	echo json_encode($response);
	if ($callback) { echo ");"; }

