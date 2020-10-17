<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  gate2shop_process.php                                    ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * Gate2Shop (www.g2s.com) handler by ViArt Ltd (http://www.viart.com/)
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
	foreach ($payment_parameters as $parameter_name => $parameter_value) {
		if (isset($pass_parameters[$parameter_name]) && $pass_parameters[$parameter_name] == 1) {
			if($parameter_name == 'time_stamp' || strtolower($parameter_name) == 'time_stamp'){
				$parameter_value = va_date(array("YYYY","-","MM","-","DD",".","HH",":","mm",":","ss"),$parameter_value);
			}
			$pass_data[$parameter_name] = $parameter_value;
			if(isset($pass_data[strtolower($parameter_name)])){
				unset($pass_data[strtolower($parameter_name)]);
				$pass_data[$parameter_name] = $parameter_value;
			}
		}
	}

	$total_tax_with_shipping = $payment_parameters['total_amount'];
	$checksum = $payment_parameters['secret'];
	$checksum.= $payment_parameters['merchant_id'];
	$checksum.= $payment_parameters['currency'];
	$checksum.= $payment_parameters['total_amount'];
	$count = 0;
	$discount = 0; // calculated all discounted values here
	$payment_items = $variables["payment_items"];
	foreach ($payment_items as $items_index => $item_info) {
		$item_price = number_format($item_info["price"], 2, ".", "");
		if ($item_price > 0) {
			$count++;
			$pass_data["item_name_".$count] = $item_info["name"];
			$pass_data["item_amount_".$count] = $item_price;
			$pass_data["item_number_".$count] = $item_info["type"]."_".$item_info["id"];
			$pass_data["item_quantity_".$count] = $item_info["quantity"];
			$checksum.= $item_info["name"];
			$checksum.= $item_price;
			$checksum.= $item_info["quantity"];
		} else {
			$discount += abs($item_price);
		}
	}

	$checksum.= $pass_data["time_stamp"];
	$checksum = md5($checksum);
	$pass_data["numberofitems"] = $count;
	$pass_data["checksum"] = $checksum;
	$pass_data["version"] = "3.0.0";
	if ($discount > 0) {
		$pass_data["discount"] = number_format($discount, 2, ".", "");;
	}


	$post_parameters = '';
	foreach ($pass_data as $param_name => $param_value) {
		if ($post_parameters) { $post_parameters .= "&"; }
		$post_parameters .= urlencode($param_name) . "=" . urlencode($param_value);
	}

	$payment_url = $payment_parameters['payment_url'] . "?" . $post_parameters;

	header("Location: " . $payment_url);
	exit;
?>
