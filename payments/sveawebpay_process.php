<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  sveawebpay_process.php                                   ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * SveaWebPay (http://www.sveawebpay.se/) transaction handler by www.viart.com
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
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);
	
	$pass_data = array();
	$count = 0;
	foreach ($variables["items"] as $number => $item) {
		$count++;
		$pass_data['Row'.$count.'AmountExVAT'] = $item['price_excl_tax'];
		if($item['price_incl_tax'] != round($item['price_excl_tax']*(1 + $item['tax_percent']/100), 2) ){
			$item_tax_percent = ($item['price_incl_tax']/$item['price_excl_tax'] - 1)*100;
		}else{
			$item_tax_percent = $item['tax_percent'];
		}
		$pass_data['Row'.$count.'VATPercentage'] = number_format($item_tax_percent, 0);
		$pass_data['Row'.$count.'Description'] = $item['item_name'];
		$pass_data['Row'.$count.'Quantity'] = $item['quantity'];
	}
	if (isset($variables["properties"])){
		foreach ($variables["properties"] as $number => $property) {
			$count++;
			$pass_data['Row'.$count.'AmountExVAT'] = $property['property_price_excl_tax'];
			if($property['property_price_incl_tax'] != round($property['property_price_excl_tax']*(1 + $property['property_tax_percent']/100), 2) ){
				$property_tax_percent = ($property['property_price_incl_tax']/$property['property_price_excl_tax'] - 1)*100;
			}else{
				$property_tax_percent = $property['property_tax_percent'];
			}
			$pass_data['Row'.$count.'VATPercentage'] = number_format($property_tax_percent, 0);
			$pass_data['Row'.$count.'Description'] = $property['property_name'];
			$pass_data['Row'.$count.'Quantity'] = 1;
		}
	}
	if (isset($variables["total_discount"]) && $variables["total_discount"] != 0) {
		$count++;
		$discount_tax_percent = round(($variables["total_discount_incl_tax"]-$variables["total_discount_excl_tax"])/$variables["total_discount_excl_tax"]*100);
		$pass_data['Row'.$count.'AmountExVAT'] = $variables['total_discount_excl_tax'];
		$pass_data['Row'.$count.'VATPercentage'] = number_format($discount_tax_percent, 0);
		$pass_data['Row'.$count.'Description'] = TOTAL_DISCOUNT_MSG;
		$pass_data['Row'.$count.'Quantity'] = 1;
	}
	if (isset($variables["processing_fee"]) && $variables["processing_fee"] != 0) {
		$count++;
		$pass_data['Row'.$count.'AmountExVAT'] = $variables['processing_fee'];
		$pass_data['Row'.$count.'VATPercentage'] = 0;
		$pass_data['Row'.$count.'Description'] = PROCESSING_FEE_MSG;
		$pass_data['Row'.$count.'Quantity'] = 1;
	}
	if (isset($variables["shipping_type_desc"]) && $variables["shipping_type_desc"]) {
		$count++;
		$shipping_tax_percent = round(($variables["shipping_cost_incl_tax"]-$variables["shipping_cost_excl_tax"])/$variables["shipping_cost_excl_tax"]*100);
		$pass_data['Row'.$count.'AmountExVAT'] = $variables["shipping_cost_excl_tax"];
		$pass_data['Row'.$count.'VATPercentage'] = number_format($shipping_tax_percent, 0);
		$pass_data['Row'.$count.'Description'] = $variables["shipping_type_desc"];
		$pass_data['Row'.$count.'Quantity'] = 1;
	}

	foreach ($pass_data as $param_name => $param_value) {
		if ($post_parameters) { $post_parameters .= "&"; }
		$post_parameters .= urlencode($param_name) . "=" . urlencode($param_value);
	}

	$payment_url = $payment_parameters['payment_url'] . "?" . $post_parameters;
	$md5 = md5($payment_url . $payment_parameters['password']);
	$payment_url .= "&md5=" . $md5;

	header("Location: " . $payment_url);
	exit;
?>