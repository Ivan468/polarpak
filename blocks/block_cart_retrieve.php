<?php                           

	$default_title = RETRIEVE_CART_TITLE;

	$html_template = get_setting_value($block, "html_template", "block_cart_retrieve.html"); 
  $t->set_file("block_body", $html_template);

	$current_page = "cart_save.php";
	$shopping_cart = get_session("shopping_cart");
	$new_cart_id = false;
	
	$t->set_var("basket_href",   get_custom_friendly_url("basket.php"));
	$t->set_var("current_href",  get_custom_friendly_url("cart_retrieve.php"));
	$t->set_var("checkout_href", get_custom_friendly_url("checkout.php"));
	$t->set_var("products_href", get_custom_friendly_url("products_list.php"));
	$t->set_var("cart_retrieve_href",get_custom_friendly_url("cart_retrieve.php"));

	// set up return page
	$rp = get_param("rp");
	if(!$rp) { $rp = get_custom_friendly_url("products_list.php"); }
	$t->set_var("rp", htmlspecialchars($rp));

	$operation = get_param("operation");
	$user_id = get_session("session_user_id");

	$r = new VA_Record($table_prefix . "saved_carts");
	$r->add_textbox("cart_id", INTEGER, CART_NO_FIELD);
	$r->change_property("cart_id", REQUIRED, true);
	$r->add_textbox("cart_name", TEXT, CART_NAME_FIELD);
	if (!$user_id) {
		$r->change_property("cart_name", REQUIRED, true);
	}

	if(strlen($operation)) 
	{
		if ($operation == "cancel") {
			header("Location: " . get_custom_friendly_url("basket.php") . "?rp=" . urlencode($rp));
			exit;
		} 
		$r->get_form_values();

		$is_valid = $r->validate();
		if ($is_valid) {
			$sql  = " SELECT cart_id FROM " . $table_prefix . "saved_carts ";
			$sql .= " WHERE cart_id=" . $db->tosql($r->get_value("cart_id"), INTEGER);
			$sql .= " AND (cart_name=" . $db->tosql($r->get_value("cart_name"), TEXT, true, false);
			if ($user_id) {
				$sql .= " OR user_id=" . $db->tosql($user_id, INTEGER);
			}
			$sql .= ")";
			$db->query($sql);
			if(!$db->next_record()) {
				$is_valid = false;
				$r->errors = RETRIEVE_CART_ERROR;
			}
		}

		if ($is_valid) {
			// clear current cart
			//set_session("shopping_cart", "");
			//set_session("session_coupons", "");
			cart_retrieve("retrieve", $r->get_value("cart_id"));

			// check if any coupons can be added or removed
			check_coupons();

			header("Location: " . get_custom_friendly_url("basket.php") . "?rp=" . urlencode($rp));
			exit;
		}
			
	}

	$r->set_parameters();

	$t->set_var("rp", htmlspecialchars($rp));

	$block_parsed = true;

?>