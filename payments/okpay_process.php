<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  okpay_process.php                                        ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	/**
	 *
	 *Okpay (http://okpay.com/) transaction handler by www.viart.com
	 *
	 */
	


	$is_admin_path = true;

	$root_folder_path = "../";



	include_once ($root_folder_path ."includes/common.php");

	include_once ($root_folder_path ."includes/order_items.php");

	include_once ($root_folder_path ."includes/parameters.php");

	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");



	$vc = get_session("session_vc");

	$order_id = get_session("session_order_id");



	$order_errors = check_order($order_id, $vc);

	if($order_errors) {

		echo $order_errors;

		exit;

	}

	

	$payment_parameters = array();

	$pass_parameters = array();

	$post_parameters = '';

	$pass_data = array();

	$variables = array();

	$inputs_array = array();
	
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);

	$item_counter = 0; $items_tax = 0;

	$pass_data["ok_currency"] = strlen($pass_data["ok_currency"] === 3) ? $pass_data["ok_currency"] : "USD";
	
	$processing_fee_excl_tax = $variables["processing_fee_excl_tax"];

	//items
	foreach($variables["items"] as $items_index => $items_array){
	
		$item_counter += 1;

		$item_amount = round($items_array["price_excl_tax"], 2);

		$item_tax = round($items_array["item_tax"], 2);

		$quantity = $items_array["quantity"];

		$items_tax += ($item_tax * $quantity);
		
		$pass_data["ok_item_" . $item_counter . "_name"] = $items_array["item_name"];
		
		$pass_data["ok_item_" . $item_counter . "_quantity"] = $quantity;
		
		$pass_data["ok_item_" . $item_counter . "_price"] = $item_amount;
		
	}
	
	//props
	foreach ($variables["properties"] as $number => $property) {

		$item_counter += 1;
		
		$item_amount = round($property["property_price_excl_tax"], 2);

		$item_tax = round($property["property_tax"], 2);

		$items_tax += $item_tax;

		$pass_data["ok_item_" . $item_counter . "_name"] = $property['property_name'];
		
		$pass_data["ok_item_" . $item_counter . "_quantity"] = 1;
		
		$pass_data["ok_item_" . $item_counter . "_price"] = $item_amount;

	}
	
	//add shipping
	if ($variables["shipping_type_desc"]) {
	
		$item_counter += 1;

		$item_amount = round($variables["shipping_cost_excl_tax"], 2);

		$item_tax = round($variables["shipping_tax"], 2);
		
		$items_tax += $item_tax;

		$pass_data["ok_item_" . $item_counter . "_name"] = $variables["shipping_type_desc"];
		
		$pass_data["ok_item_" . $item_counter . "_quantity"] = 1;
		
		$pass_data["ok_item_" . $item_counter . "_price"] = $item_amount;
		
	}	

	// New Shipping Structure
	if (isset($variables["shipments"]) && is_array($variables["shipments"]) && sizeof($variables["shipments"]) > 0) {

		foreach ($variables["shipments"] as $shipment_id => $shipment) {

			$item_counter += 1;
			
			$item_amount = round($shipment["shipping_cost_excl_tax"], 2);

			$item_tax = round($shipment["shipping_tax"], 2);
			
			$items_tax += $item_tax;

			$pass_data["ok_item_" . $item_counter . "_name"] = $shipment["shipping_type_desc"];
			
			$pass_data["ok_item_" . $item_counter . "_quantity"] = 1;
			
			$pass_data["ok_item_" . $item_counter . "_price"] = $item_amount;

		}

	}
	
	if ($processing_fee_excl_tax != 0) {
	
		$item_counter += 1;

		$item_amount = round($processing_fee_excl_tax, 2);

		$item_tax = round($variables["processing_fee_tax"], 2);

		$items_tax += $item_tax;

		$pass_data["ok_item_" . $item_counter . "_name"] = "Processing fee";
		
		$pass_data["ok_item_" . $item_counter . "_quantity"] = 1;
		
		$pass_data["ok_item_" . $item_counter . "_price"] = $item_amount;

	}

	if ($items_tax > 0){
	
		$item_counter += 1;
	
		$item_name = $variables["tax_name"];
		
		$pass_data["ok_item_" . $item_counter . "_name"] = $item_name;
		
		$pass_data["ok_item_" . $item_counter . "_quantity"] = 1;
		
		$pass_data["ok_item_" . $item_counter . "_price"] = $items_tax;
	}
	
	$t = new VA_Template('.'.$settings["templates_dir"]);
	
	$t->set_file("main","payment.html");
	
	$payment_name = 'OKpay Payments';
	
	$goto_payment_message = str_replace("{payment_system}", $payment_name, GOTO_PAYMENT_MSG);
	
	$goto_payment_message = str_replace("{button_name}", CONTINUE_BUTTON, $goto_payment_message);
	
	$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);
	
	$t->set_var("payment_url", $variables['advanced_url']);
	
	$t->set_var("submit_method", "post");
	
	//$vlink = '';
	
	foreach ($pass_data as $parameter_name => $parameter_value) {
	
		//$vlink .= 
		$t->set_var("parameter_name", $parameter_name);
		
		$t->set_var("parameter_value", $parameter_value);
		
		$t->parse("parameters", true);
		
	}
	
	$t->sparse("submit_payment", false);
	
	$t->pparse("main");
		
	exit;
	
?>

