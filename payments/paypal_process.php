<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  paypal_process.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * PayPal Standard (www.paypal.com) transaction handler by http://www.viart.com/
 */

	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");
	include_once ($root_folder_path ."includes/parameters.php");

	$vc = get_session("session_vc");
	$order_id = get_session("session_order_id");

	$order_errors = check_order($order_id, $vc);
	if($order_errors) {
		echo $order_errors;
		exit;
	}

	// two encrypted parameters may be used - cmd=_s-xclick and encrypted=...
	$payment_url = "https://www.paypal.com/cgi-bin/webscr";
	$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables, "");

	if(isset($pass_data["amount"])) { unset($pass_data["amount"]); }
	if(isset($pass_data["item_name"])) { unset($pass_data["item_name"]); }

	$pass_data["cmd"] = "_cart";
	$pass_data["upload"] = "1";

	// Single discount amount charged cart-wide. 
	// It must be less than the selling price of all items combined in the cart. This variable overrides any individual item discount_amount_x values, if present. 
	// Applies only to the Cart Upload command.
	$discount_amount_cart = 0; 

	$tax_cart	= 0; // Cart-wide tax, overriding any individual item tax_x value

	$index = 0;
	$payment_items = $variables["payment_items"];
	foreach ($payment_items as $items_index => $item_info) {
		$item_price = number_format($item_info["price"], 2, ".", "");
		if ($item_price > 0) {
			$index++;
			$pass_data["amount_".$index] = $item_price;
			$pass_data["item_name_".$index] = $item_info["name"];
			//$pass_data["item_number_".$index] = "";
			$pass_data["quantity_".$index] = $item_info["quantity"];
		} else {
			$discount_amount_cart += abs($item_price);
		}
	}
	if ($discount_amount_cart > 0) {
		$pass_data["discount_amount_cart"] = number_format($discount_amount_cart, 2, ".", "");;
	}
	if ($tax_cart > 0) {
		// by default tax passed with payment items above
		$pass_data["tax_cart"] = number_format($tax_cart, 2, ".", "");;
	}

	$t = new VA_Template('.'.$settings["templates_dir"]);
	$t->set_file("main","payment.html");
	$payment_name = 'PayPal Website Payments Standard';
	$goto_payment_message = str_replace("{payment_system}", $payment_name, GOTO_PAYMENT_MSG);
	$goto_payment_message = str_replace("{button_name}", CONTINUE_BUTTON, $goto_payment_message);
	$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);
	$t->set_var("payment_url",$payment_url);
	$t->set_var("submit_method", "post");
	foreach ($pass_data as $parameter_name => $parameter_value) {
		$t->set_var("parameter_name", $parameter_name);
		$t->set_var("parameter_value", $parameter_value);
		$t->parse("parameters", true);
	}
	$t->sparse("submit_payment", false);
	$t->pparse("main");
		
	exit;
