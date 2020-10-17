<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  amazon_pay_checkout.php                                  ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Amazon Pay Checkout handler by http://www.viart.com/
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

	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", $site_url);

	$order_errors = check_order($order_id, $vc);
	if($order_errors) {
		echo $order_errors;
		exit;
	}

	$live_widget_urls = array(
		"us" => "https://static-na.payments-amazon.com/OffAmazonPayments/us/js/Widgets.js",
		"eur" => "https://static-eu.payments-amazon.com/OffAmazonPayments/eur/lpa/js/Widgets.js",
		"gbp" => "https://static-eu.payments-amazon.com/OffAmazonPayments/gbp/lpa/js/Widgets.js",
		"uk" => "https://static-eu.payments-amazon.com/OffAmazonPayments/uk/lpa/js/Widgets.js",
		"de" => "https://static-eu.payments-amazon.com/OffAmazonPayments/de/lpa/js/Widgets.js",
	);

	$sabdbox_widget_urls = array(
		"us" => "https://static-na.payments-amazon.com/OffAmazonPayments/us/sandbox/js/Widgets.js",
		"eur" => "https://static-eu.payments-amazon.com/OffAmazonPayments/eur/sandbox/lpa/js/Widgets.js",
		"gbp" => "https://static-eu.payments-amazon.com/OffAmazonPayments/gbp/sandbox/lpa/js/Widgets.js",
		"uk" => "https://static-eu.payments-amazon.com/OffAmazonPayments/uk/sandbox/lpa/js/Widgets.js",
		"de" => "https://static-eu.payments-amazon.com/OffAmazonPayments/de/sandbox/lpa/js/Widgets.js",
	);


	$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables, "", "no");

	// get payment currency and set it as 1
	$payment_currency_code = $variables["payment_currency_code"];
	$payment_currency = get_currency($payment_currency_code, false);
	$payment_currency["rate"] = 1;

	// sandbox mode
	$sandbox = get_setting_value($payment_parameters, "sandbox", "0");
	if ($sandbox == "1" || $sandbox == "true" || $sandbox == "yes") {
		$sandbox = true;
	}

	// This is your unique Seller ID. This is the same as your Merchant ID that appears in Seller Central.
	// You can view your Merchant ID by clicking Settings and then Integration Settings.
	$sellerId = get_setting_value($payment_parameters, "sellerId"); // Example: ADEMO3053M41F7EXAMPLE
	$amount = get_setting_value($payment_parameters, "amount"); // The amount of the payment. Example: 25.50

	$returnURL = get_setting_value($payment_parameters, "returnURL", $secure_url."order_final.php"); // The URL that you want Amazon Pay to return responses to.
	$cancelReturnURL = get_setting_value($payment_parameters, "cancelReturnURL", $secure_url."order_final.php?va_status=cancel"); // The URL that you want Amazon Pay to return responses to in the event that the buyer abandons the checkout or the transaction fails. 

	$accessKey = get_setting_value($payment_parameters, "accessKey"); // Your Amazon MWS public access key. Example: ADEMOBRU3PYWWEXAMPLE
	$secretAccessKey = get_setting_value($payment_parameters, "secretAccessKey"); 

	$lwaClientId = get_setting_value($payment_parameters, "lwaClientId"); // The Login with Amazon Client ID of your application. Example: amzn1.application-oa2-client.demoa234d9024af28f4f6f8078example
	$currencyCode = get_setting_value($payment_parameters, "currencyCode"); // The currency to use for charging the buyer. Default: current seller region Example: USD

	$sellerNote = get_setting_value($payment_parameters, "sellerNote"); // The message that will appear in the checkout pages.
	$sellerOrderId = get_setting_value($payment_parameters, "sellerOrderId", $order_id); // The seller-specified identifier for this order. 

	$shippingAddressRequired = get_setting_value($payment_parameters, "shippingAddressRequired"); // A flag indicating whether the buyer should choose a shipping address. Example: true, false
	$paymentAction = get_setting_value($payment_parameters, "paymentAction", "AuthorizeAndCapture"); // Specifies what happens when a buyer clicks Pay Now at the end of the checkout flow.

	$endpoint = strtolower(get_setting_value($payment_parameters, "endpoint")); // check the endpoint
	if (!$endpoint) {
		if (strtolower($currencyCode) == "gbp") {
			$endpoint = "gbp";
		} else if (strtolower($currencyCode) == "eur") {	
			$endpoint = "eur";
		} else {
			$endpoint = "us";
		}
	}
	if ($sandbox) {
		$widget_url = isset($sabdbox_widget_urls[$endpoint]) ? $sabdbox_widget_urls[$endpoint] : $sabdbox_widget_urls["us"];
	} else {
		$widget_url = isset($live_widget_urls[$endpoint]) ? $live_widget_urls[$endpoint] : $live_widget_urls["us"];
	}

	if (!$sellerNote) {
		$total_quantity = $variables["total_quantity"];
		if ($total_quantity == 1) {
			$sellerNote = "1 product";
		} else {
			$sellerNote = $total_quantity." products";
		}
		$sellerNote .= " for ".currency_format($variables["order_total"], $payment_currency);
		/*
		$payment_items = $variables["payment_items"];
		foreach ($payment_items as $items_index => $item_info) {
			$item_price = $item_info["price"];
			$item_qty = $item_info["quantity"];
			$item_name = $item_info["name"];
			$item_name = preg_replace("/[\n\r]/", "", $item_name);
			$item_total = $item_qty*$item_price;
			$item_line = $item_qty." x ".$item_name." - ".currency_format($item_total, 2, ".", "");
			if (strlen($sellerNote.$item_line) < 1020) {
				if ($sellerNote) { $sellerNote .= "; "; }
				$sellerNote.=$item_line;
			} else {
				$sellerNote.="...";
			}
		}
		*/
	}

	$amazon_params = array(
		"sellerId" => $sellerId,
		"amount" => $amount,
		"returnURL" => $returnURL,
		"cancelReturnURL" => $cancelReturnURL,
		"accessKey" => $accessKey,
		"lwaClientId" => $lwaClientId,
		"currencyCode" => $currencyCode,
		"sellerNote" => $sellerNote,
		"sellerOrderId" => $sellerOrderId,
		"shippingAddressRequired" => $shippingAddressRequired,
		"paymentAction" => $paymentAction,
	);

	$sign_lines = array();
	uksort($amazon_params, "strcmp");
	foreach ($amazon_params as $key => $value) {
		$sign_lines[] = $key . '=' . rawurlencode($value);
	}
	$data  = "POST\npayments.amazon.com\n/\n";
	$data .= str_replace("%7E", "~", implode("&", $sign_lines));

	$signature = rawurlencode(base64_encode(hash_hmac("sha256", $data, $secretAccessKey, true)));
	$amazon_params["signature"] = $signature;

	$t = new VA_Template('.'.$settings["templates_dir"]);
	$t->set_file("main","payment.html");
	$t->set_var("CHARSET", "utf-8");
	$t->set_var("sellerId", htmlspecialchars($sellerId));
	$t->set_var("seller_id", htmlspecialchars($sellerId));
	$t->set_var("SELLERID", htmlspecialchars($sellerId));
	$t->set_var("SELLER_ID", htmlspecialchars($sellerId));

	$payment_name = get_setting_value($variables, "payment_name", "");
	$user_payment_name = get_setting_value($variables, "user_payment_name", $payment_name);
	$goto_payment_message = str_replace("{payment_system}", $user_payment_name, GOTO_PAYMENT_MSG);
	$goto_payment_message = str_replace("{button_name}", "Amazon", $goto_payment_message);
	$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);

	if ($t->block_exists("amazon_widget")) {
		$t->set_var("widget_url", htmlspecialchars($widget_url));
		$t->parse("amazon_widget", false);
	} else {
		if ($sandbox) {
			$t->parse("amazon_sandbox_js", false);
		} else {
			$t->parse("amazon_js", false);
		}
	}
	$t->set_var("amazon_params", json_encode($amazon_params));
	$t->sparse("amazon_button", false);

	$t->pparse("main");

