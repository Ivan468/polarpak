<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  nmi_call.php                                             ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * NMI (http://www.nmi.com) transaction handler by ViArt Ltd. (www.viart.com).
 */
	$xml  = '<?xml version="1.0" encoding="UTF-8"?>'."\n"; //<?
	$xml .= '<sale>'."\n";
	$xml .= '	<api-key>'.xml_escape_string($payment_parameters['api-key']).'</api-key>'."\n";
	$xml .= '	<redirect-url>'.xml_escape_string($payment_parameters['redirect-url']).'</redirect-url>'."\n";
	$xml .= '	<amount>'.xml_escape_string($payment_parameters['amount']).'</amount>'."\n";
	$xml .= '	<ip-address>'.xml_escape_string($payment_parameters['ip-address']).'</ip-address>'."\n";
	$xml .= '	<currency>'.xml_escape_string($payment_parameters['currency']).'</currency>'."\n";
	$xml .= '	<order-id>'.xml_escape_string($payment_parameters['order-id']).'</order-id>'."\n";
	$xml .= '	<order-description>'.xml_escape_string($payment_parameters['order-description']).'</order-description>'."\n";
	$xml .= '	<tax-amount>'.xml_escape_string(number_format($variables["tax_cost"], 2, '.', '')).'</tax-amount>'."\n";
	if ($variables["shipping_type_desc"]) {
		$xml .= '	<shipping-amount>'.xml_escape_string(number_format($variables["shipping_cost_incl_tax"], 2, '.', '')).'</shipping-amount>'."\n";
	}
	if ($variables["processing_fee"] != 0) {
		$xml .= '	<duty-amount>'.xml_escape_string(number_format($variables["processing_fee"], 2, '.', '')).'</duty-amount>'."\n";
	}
	if ($variables["total_discount"] != 0) {
		$xml .= '	<discount-amount>'.xml_escape_string(number_format($variables["total_discount_incl_tax"], 2, '.', '')).'</discount-amount>'."\n";
	}
	$xml .= '	<billing>'."\n";
	$xml .= '		<first-name>'.xml_escape_string($payment_parameters['first-name']).'</first-name>'."\n";
	$xml .= '		<last-name>'.xml_escape_string($payment_parameters['last-name']).'</last-name>'."\n";
	$xml .= '		<address1>'.xml_escape_string($payment_parameters['address1']).'</address1>'."\n";
	$xml .= '		<address2>'.xml_escape_string($payment_parameters['address2']).'</address2>'."\n";
	$xml .= '		<city>'.xml_escape_string($payment_parameters['city']).'</city>'."\n";
	$xml .= '		<state>'.xml_escape_string($payment_parameters['state']).'</state>'."\n";
	$xml .= '		<postal>'.xml_escape_string($payment_parameters['postal']).'</postal>'."\n";
	$xml .= '		<country>'.xml_escape_string($payment_parameters['country']).'</country>'."\n";
	$xml .= '		<email>'.xml_escape_string($payment_parameters['email']).'</email>'."\n";
	$xml .= '		<phone>'.xml_escape_string($payment_parameters['phone']).'</phone>'."\n";
	$xml .= '		<company>'.xml_escape_string($payment_parameters['company']).'</company>'."\n";
	$xml .= '		<fax>'.xml_escape_string($payment_parameters['fax']).'</fax>'."\n";
	$xml .= '	</billing>'."\n";
	$xml .= '	<shipping>'."\n";
	$xml .= '		<first-name>'.xml_escape_string($payment_parameters['shipping_first-name']).'</first-name>'."\n";
	$xml .= '		<last-name>'.xml_escape_string($payment_parameters['shipping_last-name']).'</last-name>'."\n";
	$xml .= '		<address1>'.xml_escape_string($payment_parameters['shipping_address1']).'</address1>'."\n";
	$xml .= '		<address2>'.xml_escape_string($payment_parameters['shipping_address2']).'</address2>'."\n";
	$xml .= '		<city>'.xml_escape_string($payment_parameters['shipping_city']).'</city>'."\n";
	$xml .= '		<state>'.xml_escape_string($payment_parameters['shipping_state']).'</state>'."\n";
	$xml .= '		<postal>'.xml_escape_string($payment_parameters['shipping_postal']).'</postal>'."\n";
	$xml .= '		<country>'.xml_escape_string($payment_parameters['shipping_country']).'</country>'."\n";
	$xml .= '		<email>'.xml_escape_string($payment_parameters['shipping_email']).'</email>'."\n";
	$xml .= '		<phone>'.xml_escape_string($payment_parameters['shipping_phone']).'</phone>'."\n";
	$xml .= '		<company>'.xml_escape_string($payment_parameters['shipping_company']).'</company>'."\n";
	$xml .= '		<fax>'.xml_escape_string($payment_parameters['shipping_fax']).'</fax>'."\n";
	$xml .= '	</shipping>'."\n";
	foreach ($variables["items"] as $number => $item) {
		$xml .= '	<product>'."\n";
		$product_code = $item['item_id'].' '.$item['item_code'];
		$xml .= '		<product-code>'.xml_escape_string($product_code).'</product-code>'."\n";
		$xml .= '		<description>'.xml_escape_string($item['item_name']).'</description>'."\n";
		$xml .= '		<commodity-code>'.xml_escape_string($item['manufacturer_code']).'</commodity-code>'."\n";
		$xml .= '		<unit-cost>'.xml_escape_string(number_format($item['price_incl_tax'], 2, '.', '')).'</unit-cost>'."\n";
		$xml .= '		<quantity>'.xml_escape_string($item['quantity']).'</quantity>'."\n";
		$xml .= '		<total-amount>'.xml_escape_string(number_format($item['price_incl_tax_total'], 2, '.', '')).'</total-amount>'."\n";
		$xml .= '		<tax-amount>'.xml_escape_string(number_format($item['item_tax'], 2, '.', '')).'</tax-amount>'."\n";
		$xml .= '		<tax-rate>'.xml_escape_string(number_format($item['tax_percent'], 2, '.', '')).'</tax-rate>'."\n";
		$xml .= '		<discount-amount>'.xml_escape_string(number_format($item['discount_amount'], 2, '.', '')).'</discount-amount>'."\n";
		$xml .= '	</product>'."\n";
	}
 	foreach ($variables["properties"] as $number => $property) {
 		if($property['property_price_incl_tax'] != 0){
			$xml .= '	<product>'."\n";
			$xml .= '		<description>'.xml_escape_string($property['property_name']).'</description>'."\n";
			$xml .= '		<unit-cost>'.xml_escape_string(number_format($property['property_price_incl_tax'], 2, '.', '')).'</unit-cost>'."\n";
			$xml .= '		<quantity>1</quantity>'."\n";
			$xml .= '		<total-amount>'.xml_escape_string(number_format($property['property_price_incl_tax'], 2, '.', '')).'</total-amount>'."\n";
			$xml .= '		<tax-amount>'.xml_escape_string(number_format($property['property_tax'], 2, '.', '')).'</tax-amount>'."\n";
			$xml .= '		<tax-rate>'.xml_escape_string(number_format($property['property_tax_percent'], 2, '.', '')).'</tax-rate>'."\n";
			$xml .= '	</product>'."\n";
		}
	}
	$xml .= '</sale>'."\n";

	$transaction_id = "";
	$error_message = "";
	$ch = curl_init();
	if($ch) {
		$headers = array();
		$headers[] = "Content-type: text/xml";
		curl_setopt($ch, CURLOPT_URL, $advanced_url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_PORT, 443);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		set_curl_options ($ch, $payment_parameters);
		$payment_response = curl_exec($ch);
		if (curl_error($ch)) {
			$error_message = curl_errno($ch)." - ".curl_error($ch);
		}
		curl_close($ch);
		$payment_response = trim($payment_response);
		if(!strlen($error_message)){
			if (strlen($payment_response)) {
				if(preg_match_all("/<response>(.*)\<\/response>/Uis", $payment_response, $matches, PREG_SET_ORDER)){
					$result = (preg_match_all("/<result>(.*)\<\/result>/Uis", $matches[0][1], $value, PREG_SET_ORDER))? $value[0][1]: "";
					$result_text = (preg_match_all("/<result-text>(.*)\<\/result-text>/Uis", $matches[0][1], $value, PREG_SET_ORDER))? $value[0][1]: "";
					$transaction_id = (preg_match_all("/<transaction-id>(.*)\<\/transaction-id>/Uis", $matches[0][1], $value, PREG_SET_ORDER))? $value[0][1]: "";
					$result_code = (preg_match_all("/<result-code>(.*)\<\/result-code>/Uis", $matches[0][1], $value, PREG_SET_ORDER))? $value[0][1]: "";
					$form_url = (preg_match_all("/<form-url>(.*)\<\/form-url>/Uis", $matches[0][1], $value, PREG_SET_ORDER))? $value[0][1]: "";
					if($result != 1 || !strlen($form_url)){
						$error_message = (!strlen($form_url))? "Empty form-url. result-code: " . $result_code . ". " . $result_text: "result-code: " . $result_code . ". " . $result_text;
					}
				}else{
					$error_message = "Not parse response.";
				}
			} else {
				$error_message = "Empty response from gateway. Please check your settings."; 
			}
		}
	} else {
		$error_message = "Can't initialize cURL.";
	}

	if(!strlen($error_message)){
		$pending_message = "A client is redirected to the '".$form_url."' to complete a purchase. transaction-id: ".$transaction_id;
		$sql  = " INSERT INTO " . $table_prefix . "orders_events ";
		$sql .= " (order_id, status_id, event_date, event_name, event_description) ";
		$sql .= " VALUES( ";
		$sql .= $db->tosql($order_id, INTEGER).", ";
		$sql .= $db->tosql($variables["pending_status_id"], INTEGER).", ";
		$sql .= $db->tosql(va_time(), DATETIME).", ";
		$sql .= $db->tosql('A client is redirected to complete a purchase', TEXT).", ";
		$sql .= $db->tosql($pending_message, TEXT);
		$sql .= " ) ";
		$db->query($sql);

		$t = new VA_Template('.'.$settings["templates_dir"]);
		$t->set_file("main","payment.html");
		$payment_name = 'NMI';
		$goto_payment_message = str_replace("{payment_system}", $payment_name, GOTO_PAYMENT_MSG);
		$goto_payment_message = str_replace("{button_name}", CONTINUE_BUTTON, $goto_payment_message);
		$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);
		$t->set_var("payment_url",$form_url);
		$t->set_var("submit_method", "post");
		$t->set_var("parameter_name", "billing-cc-number");
		$t->set_var("parameter_value", $payment_parameters['billing-cc-number']);
		$t->parse("parameters", true);
		$t->set_var("parameter_name", "billing-cc-exp");
		$t->set_var("parameter_value", $payment_parameters['billing-cc-exp']);
		$t->parse("parameters", true);
		$t->set_var("parameter_name", "cvv");
		$t->set_var("parameter_value", $payment_parameters['cvv']);
		$t->parse("parameters", true);
		$t->sparse("submit_payment", false);
		$t->pparse("main");
			
		exit;
	}
?>