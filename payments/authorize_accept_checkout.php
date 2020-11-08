<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  authorize_accept_checkout.php                            ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Authorize.Net Accept checkout handler by http://www.viart.com/
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

	$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables, "", "no");

	// based on sandbox parameter set API URL
	$sandbox = get_setting_value($payment_parameters, "sandbox", "0"); // 0 - don't use sandbox, 1 - use sandbox environment
	$sandbox = strtolower($sandbox);
	if ($sandbox == 1 || $sandbox == "yes" || $sandbox == "true") {
		$api_url = "https://apitest.authorize.net/xml/v1/request.api";
		$payment_url = "https://test.authorize.net/payment/payment";
	} else {
		$api_url = "https://api.authorize.net/xml/v1/request.api";
		$payment_url = "https://accept.authorize.net/payment/payment";
	}

	// get payment parameters
	$APILoginID = get_setting_value($payment_parameters, "APILoginID"); 
	$transactionKey = get_setting_value($payment_parameters, "transactionKey"); 
	$refId = get_setting_value($payment_parameters, "refId"); // Merchant-assigned reference ID for the request.
	$transactionType = get_setting_value($payment_parameters, "transactionType", "authCaptureTransaction"); // authCaptureTransaction, authOnlyTransaction
	$amount = get_setting_value($payment_parameters, "amount", $variables["order_total"]); // authCaptureTransaction, authOnlyTransaction
	$invoiceNumber = get_setting_value($payment_parameters, "invoiceNumber", $order_id);
	$customerType = get_setting_value($payment_parameters, "customerType");

	$order_user_id = $variables["user_id"];
	$order_email = ($variables["email"]) ? $variables["email"] : $variables["delivery_email"];

	// calculate billTo and shipTo parameters
	$billToNumber = 0; $shipToNumber = 0;
	foreach ($parameters as $param_name) {
		$ship_param = "delivery_".$param_name;
		if ($variables[$param_name]) {
			$billToNumber++;
		}
		if ($variables[$ship_param]) {
			$shipToNumber++;
		}
	}

	libxml_use_internal_errors(true);

	//$xml = new SimpleXMLElement('<getHostedPaymentPageRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"/>');
	$xml = new SimpleXMLElement('<getHostedPaymentPageRequest/>');
	$xml->addAttribute("xmlns", "AnetApi/xml/v1/schema/AnetApiSchema.xsd");
	// Merchant authentication information
	$merch_auth = $xml->addChild("merchantAuthentication");
	$merch_auth->addChild("name", $APILoginID);
	$merch_auth->addChild("transactionKey", $transactionKey);
	// This element is a container for transaction specific information
	$tran_req = $xml->addChild("transactionRequest");
	$tran_req->addChild("transactionType", $transactionType);
	$tran_req->addChild("amount", $amount);
	// order - contains information about the order.
	$order_xml = $tran_req->addChild("order");
	$order_xml->addChild("invoiceNumber", $invoiceNumber);
	// lineItems - contains one or more lineItem elements, up to a maximum of 30 line items.
	$payment_items = $variables["payment_items"];

	$payment_items[] = array(
		"id" => "0",
		"type" => "correction",
		"name" => "Special Correction",
		"price" => -0.01,
		"amount" => -0.01,
		"price_excl_tax" => -0.01,
		"price_incl_tax" => -0.01,
		"quantity" => 1,
	);

	if (count($payment_items) <= 30) {
		$line_items = $tran_req->addChild("lineItems");
		foreach ($payment_items as $order_item) {
			if ($order_item["price"] >= 0) {
				// lineItem	- contains information about one item.
				$line_item = $line_items->addChild("lineItem");
				if ($order_item["type"] == "item") {
					$line_item->addChild("itemId", $order_item["id"]);
				} else {
					$line_item->addChild("itemId", $order_item["type"].$order_item["id"]);
				}
				$item_name = $order_item["name"];
				if (strlen($item_name) > 31) {
					$line_item->addChild("name", mb_substr($order_item["name"], 0, 31)); // String, up to 31 characters.
					$line_item->addChild("description", $order_item["name"]); // up to 255 characters.
				} else {
					$line_item->addChild("name", $order_item["name"]); // String, up to 31 characters.
				}
				$line_item->addChild("quantity", $order_item["quantity"]); 
				$line_item->addChild("unitPrice", $order_item["price_excl_tax"]); 
			}
		}
	}
	// Customer information.
	$customer = $tran_req->addChild("customer");
	if ($customerType) {
		$customer->addChild("type", $customerType);
	}
	if ($order_user_id) {
		$customer->addChild("id", $order_user_id);
	}
	if ($order_email) {
		$customer->addChild("email", $order_email);
	}
	if ($billToNumber > 0) {
		$billTo = $tran_req->addChild("billTo");
		$billTo->addChild("firstName", $variables["first_name"]);
		$billTo->addChild("lastName", $variables["last_name"]);
		if ($variables["company_name"]) {
			$billTo->addChild("company", $variables["company_name"]);
		}
		if ($variables["address"]) {
			$billTo->addChild("address", $variables["address"]);
		}
		if ($variables["city"]) {
			$billTo->addChild("city", $variables["city"]);
		}
		if (strtoupper($variables["country_code"] == "US")) {
			$billTo->addChild("state", $variables["state_code"]);
		} else if ($variables["state"]) {
			$billTo->addChild("state", $variables["state"]);
		}
		if ($variables["country_code_alpha3"]) {
			$billTo->addChild("country", $variables["country_code_alpha3"]);
		} else if ($variables["country"]) {
			$billTo->addChild("country", $variables["country"]);
		}
		if ($variables["phone"]) {
			$billTo->addChild("phoneNumber", $variables["phone"]);
		} else if ($variables["daytime_phone"]) {
			$billTo->addChild("phoneNumber", $variables["daytime_phone"]);
		} else if ($variables["evening_phone"]) {
			$billTo->addChild("phoneNumber", $variables["evening_phone"]);
		} else if ($variables["cell_phone"]) {
			$billTo->addChild("phoneNumber", $variables["cell_phone"]);
		}
		if ($variables["fax"]) {
			$billTo->addChild("faxNumber", $variables["fax"]);
		}	
	}
	if ($shipToNumber > 0) {
		$shipTo = $tran_req->addChild("shipTo");
		$shipTo->addChild("firstName", $variables["delivery_first_name"]);
		$shipTo->addChild("lastName", $variables["delivery_last_name"]);
		if ($variables["delivery_company_name"]) {
			$shipTo->addChild("company", $variables["delivery_company_name"]);
		}
		if ($variables["delivery_address"]) {
			$shipTo->addChild("address", $variables["delivery_address"]);
		}
		if ($variables["delivery_city"]) {
			$shipTo->addChild("city", $variables["delivery_city"]);
		}
		if (strtoupper($variables["delivery_country_code"] == "US")) {
			$shipTo->addChild("state", $variables["delivery_state_code"]);
		} else if ($variables["delivery_state"]) {
			$shipTo->addChild("state", $variables["delivery_state"]);
		}
		if ($variables["delivery_country_code_alpha3"]) {
			$shipTo->addChild("country", $variables["delivery_country_code_alpha3"]);
		} else if ($variables["delivery_country"]) {
			$shipTo->addChild("country", $variables["delivery_country"]);
		}
		if ($variables["delivery_phone"]) {
			$shipTo->addChild("phoneNumber", $variables["delivery_phone"]);
		} else if ($variables["delivery_daytime_phone"]) {
			$shipTo->addChild("phoneNumber", $variables["delivery_daytime_phone"]);
		} else if ($variables["delivery_evening_phone"]) {
			$shipTo->addChild("phoneNumber", $variables["delivery_evening_phone"]);
		} else if ($variables["delivery_cell_phone"]) {
			$shipTo->addChild("phoneNumber", $variables["delivery_cell_phone"]);
		}
		if ($variables["delivery_fax"]) {
			$shipTo->addChild("faxNumber", $variables["delivery_fax"]);
		}	
	}
	// This is an array of settings for the session. Within this element, you must also submit at least one setting. 
	$host_sets = $xml->addChild("hostedPaymentSettings");

	// hostedPaymentReturnOptions - Use these options to control the receipt page, return URLs, and buttons for both the payment form and the receipt page.
	$showReceipt = get_setting_value($payment_parameters, "showReceipt");
	$showReceipt = strtolower($showReceipt);
	$showReceipt = ($showReceipt == 1 || $showReceipt == "true" || $showReceipt == "yes") ? true : false;
	$showReceipt = false;
	$return_url = $secure_url."order_final.php";
	$cancel_url = $secure_url."order_final.php?va_status=cancel";
	$return_data = array(
		"showReceipt" => $showReceipt,
		"url" => $return_url,
		"cancelUrl" => $cancel_url,
	);
	$urlText = get_setting_value($payment_parameters, "continueButton");
	if ($urlText) {
		$return_data["urlText"] = $urlText;
	}
	$cancelUrlText = get_setting_value($payment_parameters, "cancelButton");
	if ($cancelUrlText) {
		$return_data["cancelUrlText"] = $cancelUrlText;
	}
	$setting = $host_sets->addChild("setting");
	$setting->addChild("settingName", "hostedPaymentReturnOptions");
	$setting->addChild("settingValue", json_encode($return_data));
	//$setting->addChild("settingValue", json_encode($return_data, JSON_UNESCAPED_SLASHES));

	// hostedPaymentButtonOptions - Use to set the text on the payment button.
	$payButton = get_setting_value($payment_parameters, "payButton");
	if ($payButton) {
		$pay_data = array("text" => $payButton);
		$setting = $host_sets->addChild("setting");
		$setting->addChild("settingName", "hostedPaymentButtonOptions");
		$setting->addChild("settingValue", json_encode($pay_data));
	}

	// hostedPaymentStyleOptions - Use to set the text on the payment button.
	$bgColor = get_setting_value($payment_parameters, "bgColor");
	if ($bgColor) {
		$style_data = array("bgColor" => $bgColor);
		$setting = $host_sets->addChild("setting");
		$setting->addChild("settingName", "hostedPaymentStyleOptions");
		$setting->addChild("settingValue", json_encode($style_data));
	}

	// hostedPaymentPaymentOptions - Use to control which payment options to display on the hosted payment form.
	$cardCodeRequired = get_setting_value($payment_parameters, "cardCodeRequired");
	$showCreditCard = get_setting_value($payment_parameters, "showCreditCard");
	$showBankAccount = get_setting_value($payment_parameters, "showBankAccount");
	if (strlen($cardCodeRequired) || strlen($showCreditCard) || strlen($showBankAccount)) {
		$payment_options = array();
		if (strlen($cardCodeRequired)) {
			$cardCodeRequired = ($cardCodeRequired == 1 || $cardCodeRequired == "true" || $cardCodeRequired == "yes") ? true : false;
			$payment_options["cardCodeRequired"] = $cardCodeRequired;
		}
		if (strlen($cardCodeRequired)) {
			$showCreditCard = ($showCreditCard == 1 || $showCreditCard == "true" || $showCreditCard == "yes") ? true : false;
			$payment_options["showCreditCard"] = $showCreditCard;
		}
		if (strlen($cardCodeRequired)) {
			$showBankAccount = ($showBankAccount== 1 || $showBankAccount == "true" || $showBankAccount == "yes") ? true : false;
			$payment_options["showBankAccount"] = $showBankAccount;
		}
		$setting = $host_sets->addChild("setting");
		$setting->addChild("settingName", "hostedPaymentPaymentOptions");
		$setting->addChild("settingValue", json_encode($payment_options));
	}

	// hostedPaymentSecurityOptions - Use to enable or disable CAPTCHA security on the form. Defaults to false.
	$captcha = get_setting_value($payment_parameters, "captcha");
	if ($captcha == 1 || $captcha == "yes" || $captcha == "true") {
		$security_options = array("captcha" => true);
		$setting = $host_sets->addChild("setting");
		$setting->addChild("settingName", "hostedPaymentSecurityOptions");
		$setting->addChild("settingValue", json_encode($security_options));
	}

	// hostedPaymentShippingAddressOptions	{"show": false, "required": false}	Use show to enable or disable the shipping address on the form. Use required to require the shipping address. Both show and required default to false.
	// hostedPaymentBillingAddressOptions	{"show": true, "required": false}	Use show to enable or disable the billing address on the form. Defaults to true. Use required to require the billing address. Defaults to false.
	// hostedPaymentCustomerOptions	{"showEmail": false, "requiredEmail": false, "addPaymentProfile": true}	Use showEmail to enable or disable the email address on the form. 
	// hostedPaymentOrderOptions	{"show": true, "merchantName": "G and S Questions Inc."}	Use show to enable or disable the display of merchantName
	// hostedPaymentIFrameCommunicatorUrl	{"url": "https://mysite.com/special"}	Use url to set the URL of a page that can communicate with the merchant's site using JavaScript.

	$xml_data = $xml->asXML();

		//Header('Content-type: text/xml');
		//print($xml_data);

	$headers = array();
	$headers[] = "Content-type: text/xml";
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $api_url);
	curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt ($ch, CURLOPT_POST, 1);
	curl_setopt ($ch, CURLOPT_POSTFIELDS, $xml_data);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
	set_curl_options ($ch, $payment_parameters);
	$authorize_response = curl_exec ($ch);

	// parse as SimpleXMLElement
	$response_data = simplexml_load_string($authorize_response);

	//print_r($response_data);

	if ($response_data->messages->resultCode == "Error") {
		echo "<br>Error code: " . $response_data->messages->message->code;
		echo "<br>Error message: " . $response_data->messages->message->text;
	} else if ($response_data->token) {
		$t = new VA_Template('.'.$settings["templates_dir"]);
		$t->set_file("main","payment.html");
		$t->set_var("CHARSET", "utf-8");
		$t->set_var("payment_url", htmlspecialchars($payment_url));
		$t->set_var("submit_method", "POST");

		// parse token parameter
		$t->set_var("parameter_name", "token");
		$t->set_var("parameter_value", htmlspecialchars($response_data->token));
		$t->parse("parameters", false);
		// parse form and html file
		$t->sparse("submit_payment", false);
		$t->pparse("main");
	} else {
		Header('Content-type: text/xml');
		print($authorize_response);
		exit;
	}

/*
<getHostedPaymentPageResponse xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
<messages>
<resultCode>Ok</resultCode>
<message>
<code>I00001</code>
<text>Successful.</text>
</message>
</messages>
<token>
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.4r7DH4xwa
</token>
</getHostedPaymentPageResponse>
/*

XML Content-Type: text/xml
JSON Content-Type: application/json


<getHostedPaymentPageRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
  <merchantAuthentication>
    <name>API_LOGIN_ID</name>
    <transactionKey>API_TRANSACTION_KEY</transactionKey>
  </merchantAuthentication>
  <transactionRequest>
    <transactionType>authCaptureTransaction</transactionType>
    <amount>20.00</amount>
    <profile>
      <customerProfileId>123456789</customerProfileId>
    </profile>
    <customer>
      <email>ellen@mail.com</email>
    </customer>
    <billTo>
        <firstName>Ellen</firstName>
        <lastName>Johnson</lastName>
        <company>Souveniropolis</company>
        <address>14 Main Street</address>
        <city>Pecan Springs</city>
        <state>TX</state>
        <zip>44628</zip>
        <country>USA</country>
    </billTo>
  </transactionRequest>
  <hostedPaymentSettings>
    <setting>
      <settingName>hostedPaymentReturnOptions</settingName>
      <settingValue>{"showReceipt": true, "url": "https://mysite.com/receipt", "urlText": "Continue", "cancelUrl": "https://mysite.com/cancel", "cancelUrlText": "Cancel"}</settingValue>
    </setting>
    <setting>
      <settingName>hostedPaymentButtonOptions</settingName>
      <settingValue>{"text": "Pay"}</settingValue>
    </setting>
    <setting>
      <settingName>hostedPaymentStyleOptions</settingName>
      <settingValue>{"bgColor": "blue"}</settingValue>
    </setting>
    <setting>
      <settingName>hostedPaymentPaymentOptions</settingName>
      <settingValue>{"cardCodeRequired": false, "showCreditCard": true, "showBankAccount": true}</settingValue>
    </setting>
    <setting>
      <settingName>hostedPaymentSecurityOptions</settingName>
      <settingValue>{"captcha": false}</settingValue>
    </setting>
    <setting>
      <settingName>hostedPaymentShippingAddressOptions</settingName>
      <settingValue>{"show": false, "required": false}</settingValue>
    </setting>
    <setting>
      <settingName>hostedPaymentBillingAddressOptions</settingName>
      <settingValue>{"show": true, "required":false}</settingValue>
    </setting>
    <setting>
      <settingName>hostedPaymentCustomerOptions</settingName>
      <settingValue>{"showEmail": false, "requiredEmail": false, "addPaymentProfile": true}</settingValue>
    </setting>
    <setting>
      <settingName>hostedPaymentOrderOptions</settingName>
      <settingValue>{"show": true, "merchantName": "G and S Questions Inc."}</settingValue>
    </setting>
    <setting>
      <settingName>hostedPaymentIFrameCommunicatorUrl</settingName>
      <settingValue>{"url": "https://mysite.com/special"}</settingValue>
    </setting>
  </hostedPaymentSettings>
</getHostedPaymentPageRequest>

