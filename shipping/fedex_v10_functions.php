<?php

	function fedex_prepare_rate_request($module_params, $domestic = false)
	{
		global $r, $shipping_weight, $state_code, $country_code, $postal_code, $shipping_city, $shipping_street;
		global $goods_total, $currency, $shipping_weight, $shipping_weight_measure, $city, $address1, $fedex_service;
		global $language_code, $shipping_packages;

		// define some parameters
		$errors = "";
		$packaging = isset($module_params["Packaging"]) ? $module_params["Packaging"] : "";
		$origin_state_code = isset($module_params["StateOrProvinceCode"]) ? $module_params["StateOrProvinceCode"] : "";
		$origin_postal_code = isset($module_params["PostalCode"]) ? $module_params["PostalCode"] : "";
		$origin_country_code = isset($module_params["CountryCode"]) ? $module_params["CountryCode"] : "";
		
		$week_day = date("w");
		if ($week_day == 0) {
			$days_off = 1;
		} elseif ($week_day == 6) {
			$days_off = 2;
		} else {
			$days_off = 0;
		}
		
		$ship_date = mktime (0, 0, 0, date("n"), date("j") + $days_off, date("Y"));
		
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; //<?
		$xml.= "<SOAP-ENV:Envelope xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:v10=\"http://fedex.com/ws/rate/v10\">\n";
		$xml.= "<SOAP-ENV:Body>\n";
		$xml.= "	<v10:RateRequest>\n";
		$xml.= "	<v10:WebAuthenticationDetail>\n";
		$xml.= "		<v10:UserCredential>\n";
		$xml.= "			".add_line_fedex("Key",$module_params["Key"]);
		$xml.= "			".add_line_fedex("Password",$module_params["Password"]);
		$xml.= "		</v10:UserCredential>\n";
		$xml.= "	</v10:WebAuthenticationDetail>\n";
		$xml.= "	<v10:ClientDetail>\n";
		$xml.= "		".add_line_fedex("AccountNumber",$module_params["AccountNumber"]);
		$xml.= "		".add_line_fedex("MeterNumber",$module_params["MeterNumber"]);
		$xml.= "	</v10:ClientDetail>\n";
		if ($domestic){
			$xml.= "		<v10:TransactionDetail>\n";
			$xml.= "			<v10:CustomerTransactionId>ExpressUSBasicRate</v10:CustomerTransactionId>\n";
			$xml.= "		</v10:TransactionDetail>\n";
		} else {
			$xml.= "		<v10:TransactionDetail>\n";
			$xml.= "			<v10:CustomerTransactionId>ExpressIntlRate</v10:CustomerTransactionId>\n";
			$xml.= "		</v10:TransactionDetail>\n";
		}
		$xml.= "	<v10:Version>\n";
		$xml.= "		".add_line_fedex("ServiceId",$module_params["ServiceId"]);
		$xml.= "		".add_line_fedex("Major",$module_params["Major"]);
		$xml.= "		".add_line_fedex("Intermediate",$module_params["Intermediate"]);
		$xml.= "		".add_line_fedex("Minor",$module_params["Minor"]);
		$xml.= "	</v10:Version>\n";
//		$xml.= "	<v10:ReturnTransitAndCommit>true</v10:ReturnTransitAndCommit>\n";
		$xml.= "	<v10:RequestedShipment>\n";
		$xml.= "		<v10:ShipTimestamp>".date("Y-m-d", $ship_date)."T00:00:00-00:00</v10:ShipTimestamp>\n";
		$xml.= "		<v10:DropoffType>REGULAR_PICKUP</v10:DropoffType>\n";
		$xml.= "		<v10:PackagingType>YOUR_PACKAGING</v10:PackagingType>\n";
		$xml.= "		<v10:TotalInsuredValue>\n";
		$xml.= "			".add_line_fedex("Currency",$currency["code"]);
		$xml.= "		</v10:TotalInsuredValue>\n";
		$xml.= "		<v10:Shipper>\n";
		$xml.= "			<v10:Address>\n";
		$xml.= "				".add_line_fedex("StreetLines",$module_params["StreetLines"]);
		$xml.= "				".add_line_fedex("City",$module_params["City"]);
		$xml.= "				".add_line_fedex("StateOrProvinceCode",$module_params["StateOrProvinceCode"]);
		$xml.= "				".add_line_fedex("PostalCode",$module_params["PostalCode"]);
		$xml.= "				".add_line_fedex("CountryCode",$module_params["CountryCode"]);
		$xml.= "			</v10:Address>\n";
		$xml.= "		</v10:Shipper>\n";
		$xml.= "		<v10:Recipient>\n";
		$xml.= "			<v10:Address>\n";
		$xml.= "				".add_line_fedex("StreetLines",$address1);
		$xml.= "				".add_line_fedex("City",$city);
		$xml.= "				".add_line_fedex("StateOrProvinceCode",$state_code);
		$xml.= "				".add_line_fedex("PostalCode",$postal_code);
		$xml.= "				".add_line_fedex("CountryCode",$country_code);
		$xml.= "				<v10:Residential>false</v10:Residential>\n";
		$xml.= "			</v10:Address>\n";
		$xml.= "		</v10:Recipient>\n";
		$xml.= "		<v10:ShippingChargesPayment>\n";
		$xml.= "			<v10:PaymentType>SENDER</v10:PaymentType>\n";
		$xml.= "			<v10:Payor>\n";
		$xml.= "				".add_line_fedex("AccountNumber",$module_params["AccountNumber"]);
		$xml.= "				".add_line_fedex("CountryCode",$module_params["CountryCode"]);
		$xml.= "			</v10:Payor>\n";
		$xml.= "		</v10:ShippingChargesPayment>\n";
		$xml.= "		<v10:RateRequestTypes>LIST</v10:RateRequestTypes>\n";

		$xml2 = "";
		$prod_costs = 0;

		$j = 0;
		
		if (!$module_params["WeightUnits"]){
			$module_params["WeightUnits"] = "LB";
		}
		
		if (!$module_params["DimensionsUnit"]){
			$module_params["DimensionsUnit"] = "IN";
		}

		for ($i = 0; $i < count($shipping_packages); $i ++){
			
			$j++;
				
			if ($shipping_packages[$i]["length"] > 0) {
				$length = $shipping_packages[$i]["length"];
			} else if ($module_params["Length"] > 0) {
				$length = $module_params["Length"];
			} else {
				$length = 1;
			}
			
			if ($shipping_packages[$i]["width"] > 0) {
				$width = $shipping_packages[$i]["width"];
			} else if ($module_params["Width"] > 0) {
				$width = $module_params["Width"];
			} else {
				$width = 1;
			}
			
			if ($shipping_packages[$i]["height"] > 0) {
				$height = $shipping_packages[$i]["height"];
			} else if ($module_params["Height"] > 0) {
				$height = $module_params["Height"];
			} else {
				$height = 1;
			}
			
			if ($shipping_packages[$i]["weight"] > 0) {
				$weight = $shipping_packages[$i]["weight"];
			} else if ($module_params["WeightValue"] > 0) {
				$weight = $module_params["WeightValue"];
			} else {
				$weight = 1;
			}
			
			$prod_cost = $shipping_packages[$i]["price"];
			$quantity = $shipping_packages[$i]["quantity"];

			$xml2.= "		<v10:RequestedPackageLineItems>\n";

			$xml2.= "			<v10:SequenceNumber>".$j."</v10:SequenceNumber>\n";
			$xml2.= "			<v10:GroupPackageCount>"."1"."</v10:GroupPackageCount>\n";

			$xml2.= "			<v10:InsuredValue>\n";
			$xml2.= "				".add_line_fedex("Currency",$currency["code"]);
			$xml2.= "				".add_line_fedex("Amount",$prod_cost);
			$xml2.= "			</v10:InsuredValue>\n";
			$xml2.= "			<v10:Weight>\n";
			$xml2.= "				".add_line_fedex("Units",$module_params["WeightUnits"]);
			$xml2.= "				".add_line_fedex("Value",$weight);
			$xml2.= "			</v10:Weight>\n";
			$xml2.= "			<v10:Dimensions>\n";
			$xml2.= "				".add_line_fedex("Length",$length,"v10:","",INTEGER);
			$xml2.= "				".add_line_fedex("Width",$width,"v10:","",INTEGER);
			$xml2.= "				".add_line_fedex("Height",$height,"v10:","",INTEGER);
			$xml2.= "				".add_line_fedex("Units",$module_params["DimensionsUnit"]);
			$xml2.= "			</v10:Dimensions>\n";
			$xml2.= "			<v10:ItemDescription>Item #".$j."</v10:ItemDescription>\n";
			$xml2.= "			<v10:CustomerReferences>\n";
			$xml2.= "				<v10:CustomerReferenceType>CUSTOMER_REFERENCE</v10:CustomerReferenceType>\n";
			$xml2.= "				<v10:Value>Undergraduate application</v10:Value>\n";
			$xml2.= "			</v10:CustomerReferences>\n";

			$xml2.= "		</v10:RequestedPackageLineItems>\n";

		}

		$xml.= "		<v10:PackageCount>".$j."</v10:PackageCount>\n";

//		$xml.= "		<v10:PackageDetail>INDIVIDUAL_PACKAGES</v10:PackageDetail>\n";
//		$xml.= "		<v10:RequestedPackageLineItems>INDIVIDUAL_PACKAGES</v10:RequestedPackageLineItems>\n";
		
		$xml .= $xml2;

		$xml.= "	</v10:RequestedShipment>\n";

		$xml.= "</v10:RateRequest>\n";
		$xml.= "</SOAP-ENV:Body>\n";
		$xml.= "</SOAP-ENV:Envelope>\n";

		return $xml;
	}
	
	function add_line_fedex($parameter,$value,$v3="v10:",$parameter2 = "",$int=""){
		if ($int == INTEGER){
			$value = intval($value);
		}
		if (strlen($parameter2)) {
			$xml_string = "<".$v3.$parameter." ".$parameter2.">".$value."</".$v3.$parameter.">\n";
		} else {
			$xml_string = "<".$v3.$parameter.">".$value."</".$v3.$parameter.">\n";
		}
		return $xml_string;
	}
	
	function GetNextValue($values, &$i) 
    {
        $next_value = array(); 
    
        if (isset($values[$i]['value'])) {
            $next_value['VALUE'] = $values[$i]['value']; 
		}
    
        while (++$i < count($values)) { 
            switch ($values[$i]['type']) {
                case 'cdata': 
                    if (isset($next_value['VALUE'])) {
                        $next_value['VALUE'] .= $values[$i]['value']; 
                    } else {
                        $next_value['VALUE'] = $values[$i]['value']; 
					}
                    break;
    
                case 'complete': 
                    if (isset($values[$i]['attributes'])) {
                        $next_value[$values[$i]['tag']][]['ATTRIBUTES'] = $values[$i]['attributes'];
                        $index = count($next_value[$values[$i]['tag']])-1;
    
                        if (isset($values[$i]['value'])) 
                            $next_value[$values[$i]['tag']][$index]['VALUE'] = $values[$i]['value']; 
                        else
                            $next_value[$values[$i]['tag']][$index]['VALUE'] = ''; 
                    } else {
                        if (isset($values[$i]['value'])) {
                            $next_value[$values[$i]['tag']][]['VALUE'] = $values[$i]['value']; 
                        } else {
                            $next_value[$values[$i]['tag']][]['VALUE'] = ''; 
						}
					}
                    break; 
    
                case 'open': 
                    if (isset($values[$i]['attributes'])) {
                        $next_value[$values[$i]['tag']][]['ATTRIBUTES'] = $values[$i]['attributes'];
                        $index = count($next_value[$values[$i]['tag']])-1;
                        $next_value[$values[$i]['tag']][$index] = array_merge($next_value[$values[$i]['tag']][$index],GetNextValue($values, $i));
                    } else {
                        $next_value[$values[$i]['tag']][] = GetNextValue($values, $i);
                    }
                    break; 
    
                case 'close': 
                    return $next_value; 
            } 
        } 
    } 

    function GetXMLTree($xml_parse) 
    { 
        $data = $xml_parse;
       
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
        xml_parse_into_struct($parser, $data, $values, $index); 
        xml_parser_free($parser);

        $tree = array(); 
        $i = 0; 

        if (isset($values[$i]['attributes'])) {
	    	$tree[$values[$i]['tag']][]['ATTRIBUTES'] = $values[$i]['attributes']; 
	    	$index = count($tree[$values[$i]['tag']])-1;
	    	$tree[$values[$i]['tag']][$index] = array_merge($tree[$values[$i]['tag']][$index], GetNextValue($values, $i));
        }
        else {
            $tree[$values[$i]['tag']][] = GetNextValue($values, $i); 
		}
        
        return $tree; 
    }

?>