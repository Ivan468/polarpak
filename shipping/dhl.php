<?php

	global $r, $settings, $shipping_weight, $state_code, $country_code, $postal_code, $city, $address1;
	global $default_currency_code, $currency;
	global $shipping_packages, $shipping_weight, $shipping_goods_total, $shipping_errors, $sc_errors;

	//$default_currency_code
	$currency = get_currency(); // active currency 

	$currency_code = $default_currency_code;
	if (!$currency_code) { $currency_code = $currency["code"]; }

	// DHL credentials data
	$dhl_site_id = get_setting_value($module_params, "SiteID");
	$dhl_password = get_setting_value($module_params, "Password");
	$dhl_account_number = get_setting_value($module_params, "PaymentAccountNumber");
	$dhl_mode = strtolower(get_setting_value($module_params, "Mode"));
	if ($dhl_mode == "test" || $dhl_mode == "sandbox") {
		$dhl_url = "http://xmlpitest-ea.dhl.com/XMLShippingServlet";
	} else {
		$dhl_url = "https://xmlpi-ea.dhl.com/XMLShippingServlet";
	}
		
	// Origin address of the shipment data
	$origin_country_code = get_setting_value($module_params, "CountryCode");
	$origin_postal_code = get_setting_value($module_params, "PostalCode");
	$origin_postal_code = get_setting_value($module_params, "postalcode", $origin_postal_code);
	$origin_city = get_setting_value($module_params, "City");
	$origin_suburb = get_setting_value($module_params, "Suburb");

	// Details of the shipment
	$weight_unit = get_setting_value($module_params, "WeightUnit", "LB"); // LB, KG
	$dimension_unit = get_setting_value($module_params, "DimensionUnit", "IN"); // IN, CM
	$payment_country_code = get_setting_value($module_params, "PaymentCountryCode", $origin_country_code);
	// check IsDutiable parameter
	$is_dutiable = true;
	$is_dutiable_param = strtoupper(get_setting_value($module_params, "IsDutiable", "Y"));
	if (strtoupper($country_code) == strtoupper($origin_country_code) || $is_dutiable_param == "N") {
		// for domestic delivery change it to N - Non-dutiable/Doc
		$is_dutiable = false;
	}

	$network_type_code = get_setting_value($module_params, "NetworkTypeCode", "AL");
	$package_type_code = get_setting_value($module_params, "PackageTypeCode");

	// check Insurance parameter
	$insurance = false;
	$insurance_param = strtolower(get_setting_value($module_params, "Insurance", "Y"));
	if ($insurance_param == "true" || $insurance_param == "yes" || $insurance_param == "y") {
		$insurance = true;
	}
	
	// check if we need to show errors
	$show_errors = false;
	$show_errors_param = strtolower(get_setting_value($module_params, "ShowErrors"));
	if ($show_errors_param == "true" || $show_errors_param == "yes" || $show_errors_param == "y") {
		$show_errors = true;
	}

	// check if we need to show debug information
	$debug = false;
	$debug_param = strtolower(get_setting_value($module_params, "Debug"));
	if ($debug_param == "true" || $debug_param == "yes" || $debug_param == "y") {
		$debug = true;
	}

	// Destination address of the shipment
	// $country_code, $postal_code, $city

	// Supports generic request criteria.
	$OSINFO = get_setting_value($module_params, "OSINFO"); // OSINFO - find all valid product and service combinations
	$NXTPU  = get_setting_value($module_params, "NXTPU"); // NXTPU - if pickup is not possible on requested pickup day, find next possible pickup
	$FCNTWTYCD = get_setting_value($module_params, "FCNTWTYCD"); // FCNTWTYCD - To specify facility network type - DD, TD, AL
	$CUSTAGRIND = get_setting_value($module_params, "CUSTAGRIND"); // CUSTAGRIND - customer agreement indicator for product and services
	$VLDTRT_DD = get_setting_value($module_params, "VLDTRT_DD"); // VLDTRT_DD - validate ready time against pickup window start on DDI products

	$xml = new SimpleXMLElement('<p:DCTRequest xmlns:p="http://www.dhl.com" xmlns:p1="http://www.dhl.com/datatypes" xmlns:p2="http://www.dhl.com/DCTRequestdatatypes" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com DCT-req.xsd "><GetQuote></GetQuote></p:DCTRequest>');
	// create GetQuote node
	$get_quote_node = $xml->GetQuote;
	// add parent nodes to GetQuote node
	$request_node = $get_quote_node->addChild("Request");
	$from_node = $get_quote_node->addChild("From");
	$bkg_details_node = $get_quote_node->addChild("BkgDetails");
	$to_node = $get_quote_node->addChild("To");

	// add Request node data
	$service_header_node = $request_node->addChild("ServiceHeader");
	$service_header_node->addChild("MessageTime", date("Y-m-d\TH:i:sP"));
	$service_header_node->addChild("MessageReference", md5(time()));
	$service_header_node->addChild("SiteID", $dhl_site_id);
	$service_header_node->addChild("Password", $dhl_password);
	/*
	$meta_date_node = $request_node->addChild("MetaData");
	$meta_date_node->addChild("SoftwareName", "Viart Shop");
	$meta_date_node->addChild("SoftwareVersion", VA_RELEASE);//*/

	// add Origin address of the shipment to From node data
	$from_node->addChild("CountryCode", $origin_country_code);
	$from_node->addChild("Postalcode", $origin_postal_code);
	$from_node->addChild("City", $origin_city);
	$from_node->addChild("Suburb", $origin_suburb);

	// Details of the shipment
	$bkg_details_node->addChild("PaymentCountryCode", $payment_country_code);
	$bkg_details_node->addChild("Date", date("Y-m-d"));
	$bkg_details_node->addChild("ReadyTime", "PT10H00M");
	$bkg_details_node->addChild("ReadyTimeGMTOffset", date("P"));
	$bkg_details_node->addChild("DimensionUnit", $dimension_unit);
	$bkg_details_node->addChild("WeightUnit", $weight_unit);
	$pieces = $bkg_details_node->addChild("Pieces");
	$shipping_packages_price = 0; // calculate total price of all packages
	foreach ($shipping_packages as $package_id => $package) {
		$shipping_packages_price += $package["price"];
		$weight = $package["weight"];
		$width = $package["width"];
		$height = $package["height"];
		$length = $package["length"];

		$piece = $pieces->addChild("Piece");
		$piece->addChild("PieceID", $package_id);
		if ($package_type_code) {
			$piece->addChild("PackageTypeCode", $package_type_code);
		}
		if ($package["width"] > 0 && $package["height"] > 0 && $package["length"] > 0) {
			$piece->addChild("Height", round($height, 3));
			$piece->addChild("Depth", round($length, 3));
			$piece->addChild("Width", round($width, 3));
		}
		$piece->addChild("Weight", round($weight, 3));
	}
	if ($dhl_account_number) {
		$bkg_details_node->addChild("PaymentAccountNumber", $dhl_account_number);
	}
	if ($is_dutiable) {
		$bkg_details_node->addChild("IsDutiable", "Y");
	}
	if ($network_type_code) {
		$bkg_details_node->addChild("NetworkTypeCode", $network_type_code);
	}

	// Insurance
	if ($insurance && $shipping_packages_price > 0) {
		$bkg_details_node->addChild("InsuredValue", doubleval($shipping_packages_price));
		$bkg_details_node->addChild("InsuredCurrency", $currency_code);
	}

	// Destination address of the shipment
	$to_node->addChild("CountryCode", $country_code);
	$to_node->addChild("Postalcode", $postal_code);
	$to_node->addChild("City", $city);
	//$to_node->addChild("Suburb", $province);

	// Dutiable For international shipments, information that defines the types of duties to be levied. Domestic shipments are considered Non-dutiable or Doc.
	// Non-dutiable or Doc: These are shipments with no monetary value and are known as documents or general correspondence.
	// Dutiable or Non Doc: All other shipments classified as dutiable shipments by Customs may be levied
	// customs duties and taxes for entrance into the destination country/region.
	// Please work with your DHL representative if you have questions about shipment dutiable status.
	if ($is_dutiable) {
		$dutiable_node = $get_quote_node->addChild("Dutiable");
		$dutiable_node->addChild("DeclaredCurrency", $currency_code);
		$dutiable_node->addChild("DeclaredValue", doubleval($shipping_packages_price));
	}

	/*
	// Supports generic request criteria.
	if ($OSINFO || $NXTPU || $FCNTWTYCD || $CUSTAGRIND || $VLDTRT_DD) {
		$gen_req_node = $get_quote_node->addChild("GenReq");
		if ($OSINFO) { $gen_req_node->addChild("OSINFO", $OSINFO); }
		if ($NXTPU) { $gen_req_node->addChild("NXTPU", $NXTPU);	}
		if ($FCNTWTYCD) { $gen_req_node->addChild("FCNTWTYCD", $FCNTWTYCD);	}
		if ($CUSTAGRIND) { $gen_req_node->addChild("CUSTAGRIND", $CUSTAGRIND);}
		if ($VLDTRT_DD) { $gen_req_node->addChild("VLDTRT_DD", $VLDTRT_DD); }
	}//*/

	$xml_request = $xml->asXML();
	if ($debug) {
		$dom = new DOMDocument("1.0");
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($xml_request);
		echo "<pre>".htmlspecialchars($dom->saveXML())."</pre><hr>";
	}

	if (strlen($xml_request)) {
		$ch = curl_init ();
		curl_setopt ($ch, CURLOPT_URL, $dhl_url);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt ($ch, CURLOPT_POST, 1); 
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $xml_request);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
	
		$xml_response = curl_exec($ch);
		curl_close($ch);

		if ($debug) {
			$dom = new DOMDocument("1.0");
			$dom->preserveWhiteSpace = false;
			$dom->formatOutput = true;
			$dom->loadXML($xml_response);
			echo "<pre>".htmlspecialchars($dom->saveXML())."</pre><hr>";
		}               	
		                	
	} else {          	
		if ($show_errors) {
			$shipping_errors .= "Empty response from DHL.<br>\r\n";
		}
		return;
	}

	$xml = simplexml_load_string($xml_response);
	if ($xml === false) {
		if ($show_errors) {
			$shipping_errors .= "Can't parse XML response from DHL.<br>\r\n";
		}
		return;
	} else if ($xml->getName() == "ErrorResponse") {
		if ($show_errors) {
			if ($xml->Response->Status->Condition) {
				$shipping_errors .= "DHL: " . $xml->Response->Status->Condition->ConditionData." (".$xml->Response->Status->Condition->ConditionCode.")<br>\r\n";
			} else {
				$shipping_errors .= "Unknown error from DHL.<br>\r\n";
			}
		}
	} else if (isset($xml->GetQuoteResponse->BkgDetails) && isset($xml->GetQuoteResponse->BkgDetails->QtdShp)) {
		foreach($xml->GetQuoteResponse->BkgDetails->QtdShp as $qtd_shp) {

  		$xml_shipping_code = $qtd_shp->GlobalProductCode;
  		$xml_shipping_short_name = $qtd_shp->ProductShortName; 
			$xml_shipping_full_name = $qtd_shp->LocalProductName;
			$xml_shipping_cost = $qtd_shp->ShippingCharge;

			foreach ($module_shipping as $module_shipping_id => $shipment_data) {
				$module_shipping_code = $shipment_data["code"];
				if (strtoupper($module_shipping_code) == strtoupper($xml_shipping_code)) {
					$shipment_data["cost"] += $xml_shipping_cost;
					$shipping_types[] = $shipment_data;
					break;
				}
			}
		}
	} else {
		if ($show_errors) {
			if (isset($xml->GetQuoteResponse) && isset($xml->GetQuoteResponse->Note)) {
				foreach($xml->GetQuoteResponse->Note->Condition as $condition) {
					$shipping_errors .= "DHL: " . $condition->ConditionData." (".$condition->ConditionCode.")<br>\r\n";
				}
			} else {
				$shipping_errors .= "Can't get any services from DHL.<br>\r\n";
			}
		}
	}

