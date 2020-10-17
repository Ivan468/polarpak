<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  gtbill_api_process.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * GTBill QuickPay API (http://www.gtbill.com/) transaction handler by www.viart.com
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
	
	$index_of_items = 0;
	$payment_parameters = array();
	$pass_parameters = array();
	$post_parameters = '';
	$pass_data = array();
	$variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);

	foreach ($payment_parameters as $parameter_name => $parameter_value) {
		if (isset($pass_parameters[$parameter_name]) && $pass_parameters[$parameter_name] == 1) {
			$pass_data[$parameter_name] = $parameter_value;
			if(isset($pass_data[strtolower($parameter_name)])){
				unset($pass_data[strtolower($parameter_name)]);
				$pass_data[$parameter_name] = $parameter_value;
			}
		}
	}

	$t = new VA_Template('.'.$settings["templates_dir"]);
	$t->set_file("main","payment.html");
	$goto_payment_message = str_replace("{payment_system}", $variables['payment_name'], GOTO_PAYMENT_MSG);
	$goto_payment_message = str_replace("{button_name}", CONTINUE_BUTTON, $goto_payment_message);
	$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);
	$t->set_var("payment_url",$payment_parameters['action_url']);
	$t->set_var("submit_method", "post");
	foreach ($pass_data as $parameter_name => $parameter_value) {
		$t->set_var("parameter_name", $parameter_name);
		$t->set_var("parameter_value", $parameter_value);
		$t->parse("parameters", true);
	}
	foreach ($variables["items"] as $number => $item) {
		$t->set_var("parameter_name", "ItemAmount[".$index_of_items."]");
		$t->set_var("parameter_value", $item['price_incl_tax_total']);
		$t->parse("parameters", true);
		$t->set_var("parameter_name", "ItemDesc[".$index_of_items."]");
		$t->set_var("parameter_value", $item['item_name']);
		$t->parse("parameters", true);
		$t->set_var("parameter_name", "ItemName[".$index_of_items."]");
		$t->set_var("parameter_value", $item['item_name']);
		$t->parse("parameters", true);
		$t->set_var("parameter_name", "ItemQuantity[".$index_of_items."]");
		$t->set_var("parameter_value", $item['quantity']);
		$t->parse("parameters", true);
		$index_of_items ++;
	}
	foreach ($variables["properties"] as $number => $property) {
		$t->set_var("parameter_name", "ItemAmount[".$index_of_items."]");
		$t->set_var("parameter_value", $property['property_price_incl_tax']);
		$t->parse("parameters", true);
		$t->set_var("parameter_name", "ItemDesc[".$index_of_items."]");
		$t->set_var("parameter_value", $property['property_name']);
		$t->parse("parameters", true);
		$t->set_var("parameter_name", "ItemName[".$index_of_items."]");
		$t->set_var("parameter_value", $property['property_name']);
		$t->parse("parameters", true);
		$t->set_var("parameter_name", "ItemQuantity[".$index_of_items."]");
		$t->set_var("parameter_value", 1);
		$t->parse("parameters", true);
		$index_of_items ++;
	}
	if ($variables["total_discount"] != 0) {
		$t->set_var("parameter_name", "ItemAmount[".$index_of_items."]");
		$t->set_var("parameter_value", -$property['total_discount_incl_tax']);
		$t->parse("parameters", true);
		$t->set_var("parameter_name", "ItemDesc[".$index_of_items."]");
		$t->set_var("parameter_value", TOTAL_DISCOUNT_MSG);
		$t->parse("parameters", true);
		$t->set_var("parameter_name", "ItemName[".$index_of_items."]");
		$t->set_var("parameter_value", TOTAL_DISCOUNT_MSG);
		$t->parse("parameters", true);
		$t->set_var("parameter_name", "ItemQuantity[".$index_of_items."]");
		$t->set_var("parameter_value", 1);
		$t->parse("parameters", true);
		$index_of_items ++;
	}
	if ($variables["processing_fee"] != 0) {
		$t->set_var("parameter_name", "ItemAmount[".$index_of_items."]");
		$t->set_var("parameter_value", $property['processing_fee']);
		$t->parse("parameters", true);
		$t->set_var("parameter_name", "ItemDesc[".$index_of_items."]");
		$t->set_var("parameter_value", PROCESSING_FEE_MSG);
		$t->parse("parameters", true);
		$t->set_var("parameter_name", "ItemName[".$index_of_items."]");
		$t->set_var("parameter_value", PROCESSING_FEE_MSG);
		$t->parse("parameters", true);
		$t->set_var("parameter_name", "ItemQuantity[".$index_of_items."]");
		$t->set_var("parameter_value", 1);
		$t->parse("parameters", true);
		$index_of_items ++;
	}
	$t->sparse("submit_payment", false);
	$t->pparse("main");
		
	exit;
?>