<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  amazon_checkout.php                                      ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Amazon Checkout handler by http://www.viart.com/
 */

	ini_set("display_errors", "1");
	error_reporting(E_ALL & ~E_STRICT);

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

	$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables, "", "no");

	$payment_name = get_setting_value($variables, "payment_name", "");
	$user_payment_name = get_setting_value($variables, "user_payment_name", $payment_name);

	// general account data
	// 14-character alphanumeric string Get from Settings > Account Info > Checkout Pipeline Settings
	$merchant_id = get_setting_value($payment_parameters, "merchant_id", ""); 
	$currency_code = get_setting_value($payment_parameters, "currency_code", "USD"); 

	// check some additional parameters to pass
	$ReturnUrl = get_setting_value($pass_data, "ReturnUrl", ""); 
	$CancelUrl = get_setting_value($pass_data, "CancelUrl", ""); 

	// script url for Production and Sandbox modes
	$sandbox = get_setting_value($payment_parameters, "sandbox", "0");
	if ($sandbox) {
		$payment_url = "https://payments-sandbox.amazon.com/checkout/".$merchant_id;
		$payment_script_url = "https://static-na.payments-amazon.com/cba/js/us/PaymentWidgets.js";
	} else {
		$payment_url = "https://payments.amazon.com/checkout/".$merchant_id;
		$payment_script_url = "https://payments-sandbox.amazon.com/cba/js/PaymentWidgets.js";
	}

	// 1. In Seller Central, go to Integration > Access Key.
	// The Access Key page appears, showing your Access Key ID and a placeholder for your Secret Access Key.
	// 2. To view your Secret Access Key, click Show. Your Secret Access Key appears.
	$aws_access_key_id = get_setting_value($payment_parameters, "aws_access_key_id", ""); // Your AWS Access Key ID (public)
	$aws_secret_key_id = get_setting_value($payment_parameters, "aws_secret_key_id", ""); // Your AWS Secret Access Key (private)


	// build XML items and promo blocks
	$xml_items = "";
	$xml_promo = "";

	$item_number = 0; $discount_number = 0;
	$payment_items = $variables["payment_items"];
	foreach ($payment_items as $items_index => $item_info) {
		$item_price = number_format($item_info["price"], 2, ".", "");
		if ($item_price > 0) {
			$item_number++;
			$xml_items .= "<Item>";
			$xml_items .= "<SKU></SKU>";
			$xml_items .= "<MerchantId>".$merchant_id."</MerchantId>";
			$xml_items .= "<Title>".$item_info["name"]."</Title>";
			$xml_items .= "<Price>";
			$xml_items .= "<Amount>".$item_price."</Amount>";
			$xml_items .= "<CurrencyCode>".$currency_code."</CurrencyCode>";
			$xml_items .= "</Price>";
			$xml_items .= "<Quantity>".$item_info["quantity"]."</Quantity>";
			$xml_items .= "</Item>";
		} else {
			$discount_number++;
			$xml_promo .= "<Promotion>";
			$xml_promo .= "<PromotionId>cart-promotion-".$discount_number."</PromotionId>";
			$xml_promo .= "<Description>".$item_info["name"]."</Description>";
			$xml_promo .= "<Benefit>";
			$xml_promo .= "<FixedAmountDiscount>";
			$xml_promo .= "<Amount>".abs($item_price)."</Amount>";
			$xml_promo .= "<CurrencyCode>".$currency_code."</CurrencyCode>";
			$xml_promo .= "</FixedAmountDiscount>";
			$xml_promo .= "</Benefit>";
			$xml_promo .= "</Promotion>";
		}
	}//*/
    
	// build XML
	$xml  = '<?xml version="1.0" encoding="UTF-8"?>';
	$xml .= '<Order xmlns="http://payments.amazon.com/checkout/2009-05-15/">';

	$xml .= "<ClientRequestId>".$order_id."</ClientRequestId>";
	$xml .= "<Cart>";
	$xml .= "<Items>";
	$xml .= $xml_items;
	$xml .= "</Items>";
	$xml .= "</Cart>";

	if ($xml_promo) {
		$xml .= "<Promotions>";
		$xml .= $xml_promo;
		$xml .= "</Promotions>";
	}

	if ($ReturnUrl) {
		$xml .= "<ReturnUrl>".$ReturnUrl."</ReturnUrl>";
	}
	if ($CancelUrl) {
		$xml .= "<CancelUrl>".$CancelUrl."</CancelUrl>";
	}
	$xml .= "</Order>";

	// calculate signature
	$merchant_signature = base64_encode(hash_hmac("sha1", $xml, $aws_secret_key_id, true));

	$t = new VA_Template('.'.$settings["templates_dir"]);
	$t->set_file("main","payment_amazon.html");
	$t->set_var("CHARSET", "utf-8");

	$t->set_var("merchant_id", $merchant_id);
	$t->set_var("signature", $merchant_signature);
	$t->set_var("merchant_signature", $merchant_signature);
	$t->set_var("merchant_id", $merchant_id);
	$t->set_var("aws_access_key_id", $aws_access_key_id);
	$t->set_var("xml", base64_encode($xml));
	$t->set_var("order_xml", base64_encode($xml));

	if ($sandbox) {
		$t->sparse("sandbox_payment_js", false);
	} else {
		$t->sparse("production_payment_js", false);
	}
	$t->pparse("main");

?>