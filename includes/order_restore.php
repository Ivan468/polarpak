<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  order_restore.php                                        ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

	$user_id = get_session("session_user_id");
	$shopping_cart = get_session("shopping_cart");
	$order_id = get_param("order_id");
	$rp = get_param("rp");
	if(!$rp) { $rp = get_custom_friendly_url("products_list.php"); }
	$new_cart_id = false;
	
	$sql  = " SELECT order_id FROM " . $table_prefix . "orders ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
	$db->query($sql);
	if(!$db->next_record()) {
		return;
	}
	$i = 0;
	// clear current cart
	set_session("shopping_cart", "");
	set_session("session_coupons", "");
		// Database Initialize
	$dbi = new VA_SQL();
	$dbi->DBType      = $db->DBType;
	$dbi->DBDatabase  = $db->DBDatabase;
	$dbi->DBHost      = $db->DBHost;
	$dbi->DBPort      = $db->DBPort;
	$dbi->DBUser      = $db->DBUser;
	$dbi->DBPassword  = $db->DBPassword;
	$dbi->DBPersistent= $db->DBPersistent;
		// retrieve cart
	$sql  = " SELECT * FROM " . $table_prefix . "orders_items ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$sql .= " AND user_id=" . $db->tosql($user_id, INTEGER);
	$sql .= " ORDER BY order_item_id ";
	$dbi->query($sql);
	if ($dbi->next_record()) {
		do {
			$sc_errors = ""; $sc_message = ""; $i++;
			$cart_item_id = $i;
			$item_id = $dbi->f("item_id");
			$item_name = $dbi->f("item_name");
			$quantity = $dbi->f("quantity");
			$price = $dbi->f("price");
			// add to cart
			add_to_cart($item_id, "", $price, $quantity, "db", "ADD", $new_cart_id, $sc_errors, $sc_message, $cart_item_id, $item_name);
		}
		while ($dbi->next_record());
	}
	// check if any coupons can be added or removed
	check_coupons();

	header("Location: " . get_custom_friendly_url("basket.php") . "?rp=" . urlencode($rp));
	exit;
		
?>