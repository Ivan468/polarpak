<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  ideal_lite_process.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Rabo iDEAL Lite (http://ideal.rabobank.nl) transaction handler by www.viart.com
 * Also supported by ING Wholesale Banking (https://ideal.secureing.com/ideal/mpiPayInitIng.do) As Ideal Basic
 */

	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/shopping_cart.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");

	$vc = get_session("session_vc");
	$order_id = get_session("session_order_id");

	$order_errors = check_order($order_id, $vc);
	if ($order_errors) {
		echo $order_errors;
		exit;
	}
	
	$settings = va_settings();
	$tax_prices_type = get_setting_value($settings, "tax_prices_type", 0);
		
	$post_parameters = ""; 
	$payment_params  = array(); 
	$pass_parameters = array(); 
	$pass_data = array(); 
	$variables = array();
	get_payment_parameters($order_id, $payment_params, $pass_parameters, $post_parameters, $pass_data, $variables, "");
		
	if (isset($payment_params["URL"])) {
		$payment_url = rtrim(trim($payment_params["URL"]));
	} else {
		$payment_url = "https://idealtest.rabobank.nl/ideal/mpiPayInitRabo.do";
	}
	
	$hash_fields = "";
	$post_fields = "";

	if (isset($payment_params["Key"])) {
		$key = $payment_params["Key"];
		$hash_fields = $key;
	} else {
		$error_message = 'Key is required!';
		exit;
	}
	if (isset($payment_params["MerchantID"])) {
		$post_fields .= "merchantID=". $payment_params["MerchantID"];
		$hash_fields .= $payment_params["MerchantID"];
	} else {
		$error_message = 'Merchant ID is required!';
		exit;
	}
	if (isset($payment_params["subID"])) {
		$post_fields .= "&subID=" . $payment_params["subID"];
		$hash_fields .= $payment_params["subID"];
	} else {
		$post_fields .= "&subID=0";
		$hash_fields .= "0";
	}
		
	$post_fields .= "&amount=" . $variables["order_total"] * 100;
	$hash_fields .= ($variables["order_total"] * 100);
	$post_fields .= "&purchaseID=" . $order_id;
	$hash_fields .= $order_id;
	
	if (isset($payment_params["paymentType"])) {
		$post_fields .= "&paymentType=" . $payment_params["paymentType"];
		$hash_fields .= $payment_params["paymentType"];
	} else {
		$post_fields .= "&paymentType=" . "ideal";
		$hash_fields .= "ideal";
	}

	$validUntil   =  date("Y-m-d\Th:i:s",strtotime ("+1 week")) . ".SSSZ";
	$post_fields .= "&validUntil=" . $validUntil;
	$hash_fields .= $validUntil;

	$i = 0;
	$payment_items = $variables["payment_items"];
	foreach ($payment_items as $key => $payment_item) {
		$i++;
		$name = get_translation($payment_item["name"]);
		$price = $payment_item["price"] * 100;
		$quantity = $payment_item["quantity"];

		$post_fields .= "&itemNumber".$i."=".$i;
		$post_fields .= "&itemDescription".$i."=".$name;
		$post_fields .= "&itemQuantity".$i."=".$quantity;
		$post_fields .= "&itemPrice".$i."=".$price;	

		$hash_fields .= $i.$name.$quantity.$price;
	}

	$hash_fields = HTML_entity_decode($hash_fields);
	$not_allowed = array("\t", "\n", "\r", " ");
	$hash_fields = str_replace($not_allowed, "",$hash_fields);

	$hash = sha1($hash_fields);

	$post_fields .= "&hash=" . substr($hash, 0, 50);
	
	$post_fields .= "&currency=" . $variables["currency_code"];
	if (isset($payment_params["description"]))
		$post_fields .="&description=" . $payment_params["description"];
	else 
		$post_fields .="&description=VIARTShopOrder";
		
	if (isset($payment_params["urlCancel"]))
		$post_fields .="&urlCancel=" . $payment_params["urlCancel"];
	if (isset($payment_params["urlError"]))
		$post_fields .="&urlError=" . $payment_params["urlError"];
	if (isset($payment_params["urlSuccess"]))
		$post_fields .="&urlSuccess=" . $payment_params["urlSuccess"];

		
	if (isset($payment_params["language"])) {
		$post_fields .= "&language=" . $payment_params["language"];
	} else {
		$post_fields .= "&language=" . $language_code;
	}
	
	$header = "Location: " .$payment_url . "?" . $post_fields;
	header($header);
?>