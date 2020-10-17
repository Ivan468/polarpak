<?php
	
	include_once("./includes/products_functions.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	// set necessary scripts
	set_script_tag("js/shopping.js");
	set_script_tag("js/ajax.js");
	set_script_tag("js/blocks.js");

	$default_title = "{FAST_PRODUCT_ADDING_MSG}";
	
	$user_id = get_session("session_user_id");
	$shopping_cart = get_session("shopping_cart");
	$user_info = get_session("session_user_info");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	$redirect_to_cart = get_setting_value($settings, "redirect_to_cart", ""); 
	
	$remove_parameters = array();
	if ($friendly_urls && isset($page_friendly_url) && $page_friendly_url) {
		$current_page = $page_friendly_url . $friendly_extension;
		$query_string = transfer_params($page_friendly_params, true);
	} else {
		$query_string = transfer_params("", true);
	}
	$t->set_var("current_href", $current_page);

	$html_template = get_setting_value($block, "html_template", "block_products_fast_add.html"); 
	$t->set_file("block_body", $html_template);
	$t->set_var("redirect_to_cart", $redirect_to_cart);

	$cart_code = ""; $quantity = "";
	$param_pb_id = get_param("pb_id"); // check if this block was used
	$errors = ""; $message = "";
	if ($param_pb_id == $pb_id) {
		// check parameters from request
		$cart_code = trim(get_param("cart_code"));
		$quantity = trim(get_param("quantity"));

		if (!strlen($cart_code)) {
			$sc_errors = str_replace("{field_name}", CODE_MSG, REQUIRED_MESSAGE) . "<br>";
		}		

		// show error message or information about added products 
		if ($sc_errors) {
			$t->set_var("errors", $sc_errors);
			$t->parse("errors_block", false);
		} 
		if ($sc_message) {
			$t->set_var("message", $sc_message);
			$t->parse("message_block", false);
			// clear parameters if product was successfully added
			$cart_code = ""; $quantity = "";
		}

	}

	$t->set_var("rnd", va_timestamp());
	$t->set_var("cart_code", $cart_code);			
	$t->set_var("quantity",  $quantity);

	$block_parsed = true;
?>