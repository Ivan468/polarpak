<?php

	if (!strlen($external_url) || !strlen($country_code)) {
		return;
	}
	global $is_admin_path, $is_sub_folder, $r, $shipping_errors;
	$root_folder_path = ((isset($is_admin_path) && $is_admin_path) || (isset($is_sub_folder) && $is_sub_folder)) ? "../" : "./";
	include_once($root_folder_path . "shipping/fedex_v10_functions.php");

	$domestic = (strtolower($country_code) == "us");
	
	if ($domestic && !strlen($postal_code)) { return; }

	$fedex_error = "";
	$ratetype = $module_params["RateType"];
	if (!preg_match("/".$ratetype."/"," PAYOR_LIST RATED_LIST RATED_ACCOUNT PAYOR_ACCOUNT")){
		$ratetype = "PAYOR_ACCOUNT";
	}


	// check if we need to show errors
	$show_errors = false;
	$show_errors_param = strtolower(get_setting_value($module_params, "ShowErrors", "y"));
	if ($show_errors_param == "true" || $show_errors_param == "yes" || $show_errors_param == "y") {
		$show_errors = true;
	}

	// check if we need to show notes 
	$show_notes = false;
	$show_notes_param = strtolower(get_setting_value($module_params, "ShowNotes"));
	if ($show_notes_param == "true" || $show_notes_param == "yes" || $show_notes_param == "y") {
		$show_notes = true;
	}

	// check if we need to show warnings
	$show_warnings = false;
	$show_warnings_param = strtolower(get_setting_value($module_params, "ShowWarnings"));
	if ($show_warnings_param == "true" || $show_warnings_param == "yes" || $show_warnings_param == "y") {
		$show_warnings = true;
	}


	// check if we need to show debug information
	$debug = false;
	$debug_param = strtolower(get_setting_value($module_params, "Debug"));
	if ($debug_param == "true" || $debug_param == "yes" || $debug_param == "y") {
		$debug = true;
	}

	$xml_request = fedex_prepare_rate_request($module_params);
	$ch = @curl_init();
	if ($ch){

		if ($debug) {
			$dom = new DOMDocument("1.0");
			$dom->preserveWhiteSpace = false;
			$dom->formatOutput = true;
			$dom->loadXML($xml_request);
			echo "<pre>".htmlspecialchars($dom->saveXML())."</pre><hr>";
		}               	

		$header = array();
		$header[] = "POST /web-services HTTP/1.1";
		$header[] = "Host: ws.fedex.com";
		//$header[] = "Host: gatewaybeta.fedex.com";
		$header[] = "Connection: Keep-Alive";
		$header[] = "User-Agent: PHP-SOAP/5.2.6";
		$header[] = "Content-Type: text/xml; charset=utf-8";
		$header[] = "SOAPAction: \"getRates\"";
		$header[] = "Content-Length: ".strlen($xml_request);
		
		curl_setopt($ch, CURLOPT_URL, $external_url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		$fedex_response = curl_exec($ch);
		curl_close($ch);

		if ($debug) {
			$dom = new DOMDocument("1.0");
			$dom->preserveWhiteSpace = false;
			$dom->formatOutput = true;
			$dom->loadXML($fedex_response);
			echo "<pre>".htmlspecialchars($dom->saveXML())."</pre><hr>";
		}               	

		// check XML body
		if (preg_match("/<SOAP-ENV:BODY[^>]*>(.+)<\/SOAP-ENV:BODY>/is", $fedex_response, $match)) {
			$soap_body = $match[1];
		} else {
			return;
		}
		$tree = GetXMLTree($soap_body);
				
		$error_code = $tree["RATEREPLY"][0]["NOTIFICATIONS"][0]["CODE"][0]["VALUE"];
		$error_message = $tree["RATEREPLY"][0]["NOTIFICATIONS"][0]["MESSAGE"][0]["VALUE"];
		$error_severity = $tree["RATEREPLY"][0]["HIGHESTSEVERITY"][0]["VALUE"];
		
		$fedex_error .= $error_code . " -  " . $error_severity . " : " . $error_message . "<br>\n";
		if (strtoupper($error_severity) != "ERROR" && strtoupper($error_severity) != "FAILURE"){
			if(strtoupper($error_severity) == "NOTE" && $show_notes){
				$shipping_errors .= $fedex_error;
				return;
			}		
			if(strtoupper($error_severity) == "WARNING" && $show_warnings){
				$shipping_errors .= $fedex_error;
				return;
			}		

			$methods = $tree["RATEREPLY"][0]["RATEREPLYDETAILS"];
			for ($i=0; $i < count($methods); $i++){

				$ship_code = $methods[$i]["SERVICETYPE"][0]["VALUE"];
				$type_account = $methods[$i]["RATEDSHIPMENTDETAILS"];
				for ($j=0; $j < count($type_account);$j++){
					$RateTypeTemp = $type_account[$j]["SHIPMENTRATEDETAIL"][0]["RATETYPE"][0]["VALUE"];

					if (preg_match("/".$ratetype."/i", $RateTypeTemp)){
						if (isset($type_account[$j]["SHIPMENTRATEDETAIL"][0]["TOTALNETCHARGE"][0]["AMOUNT"][0]["VALUE"])){
							$fedex_rate = $type_account[$j]["SHIPMENTRATEDETAIL"][0]["TOTALNETCHARGE"][0]["AMOUNT"][0]["VALUE"];
						} else {
							$fedex_rate = false;
						}
						
						if ($fedex_rate){
							foreach ($module_shipping as $module_shipping_id => $shipment_data) {
								$row_shipping_type_code = $shipment_data["code"];
								if ($row_shipping_type_code == $ship_code) {
									$shipment_data["cost"] += $fedex_rate;
									$shipping_types[] = $shipment_data;
									break;
								}
							}
						}
					}
				}
			}
			
		} else {
			if ($show_errors) {
				$shipping_errors .= $fedex_error;
			}
			return;
		}
	} else {
		return;
	}
	
?>