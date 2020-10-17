<?php
	define("AP_PARAMETER_REQUIRED_MSG", "Australia Post module error: {param_name} is required.");

	foreach ($module_shipping as $module_shipping_id => $module) {
		$shipping_charge = 0;
		$shipping_day = '';
		$shipping_time = 0;
		$shipping_error_package = '';
		foreach ($shipping_packages as $package) {
			$shipping_parameter_post = '';
			if (isset($module_params["Pickup_Postcode"]) && strlen($module_params["Pickup_Postcode"])) {
				$shipping_parameter_post .= (strlen($shipping_parameter_post))? '&': '';
				$shipping_parameter_post .= 'Pickup_Postcode='.$module_params['Pickup_Postcode'];
			} else {
				$r->errors .= str_replace("{param_name}", "Pickup_Postcode", AP_PARAMETER_REQUIRED_MSG) . "<br>\n";
			}
			if (isset($postal_code) && strlen($postal_code)) {
				$shipping_parameter_post .= (strlen($shipping_parameter_post))? '&': '';
				$shipping_parameter_post .= 'Destination_Postcode='.$postal_code;
			} else {
				$r->errors .= str_replace("{param_name}", "Destination_Postcode", AP_PARAMETER_REQUIRED_MSG) . "<br>\n";
			}
			if (isset($country_code) && strlen($country_code)) {
				$shipping_parameter_post .= (strlen($shipping_parameter_post))? '&': '';
				$shipping_parameter_post .= 'Country='.$country_code;
			} else {
				$r->errors .= str_replace("{param_name}", "Country", AP_PARAMETER_REQUIRED_MSG) . "<br>\n";
			}
			if (isset($module["code"]) && strlen($module["code"])) {
				$shipping_parameter_post .= (strlen($shipping_parameter_post))? '&': '';
				$shipping_parameter_post .= 'Service_Type='.$module["code"];
			} else {
				$r->errors .= str_replace("{param_name}", SHIPPING_CODE_MSG, AP_PARAMETER_REQUIRED_MSG) . "<br>\n";
			}
			if (isset($package["weight"]) && $package["weight"]>0) {
				$shipping_parameter_post .= (strlen($shipping_parameter_post))? '&': '';
				$shipping_parameter_post .= 'Weight='.round($package['weight']*1000);
			} elseif (isset($module_params["Weight"]) && $module_params["Weight"]>0) {
				$shipping_parameter_post .= (strlen($shipping_parameter_post))? '&': '';
				$shipping_parameter_post .= 'Weight='.round($module_params['Weight']*1000);
			} else {
				$r->errors .= str_replace("{param_name}", "Weight", AP_PARAMETER_REQUIRED_MSG) . "<br>\n";
			}
			if (isset($package["length"]) && $package["length"]>0) {
				$shipping_parameter_post .= (strlen($shipping_parameter_post))? '&': '';
				$shipping_parameter_post .= 'Length='.round($package['length']*10);
			} elseif (isset($module_params["Length"]) && $module_params["Length"]>0) {
				$shipping_parameter_post .= (strlen($shipping_parameter_post))? '&': '';
				$shipping_parameter_post .= 'Length='.round($module_params['Length']*10);
			} else {
				$r->errors .= str_replace("{param_name}", "Length", AP_PARAMETER_REQUIRED_MSG) . "<br>\n";
			}
			if (isset($package["width"]) && $package["width"]>0) {
				$shipping_parameter_post .= (strlen($shipping_parameter_post))? '&': '';
				$shipping_parameter_post .= 'Width='.round($package['width']*10);
			} elseif (isset($module_params["Width"]) && $module_params["Width"]>0) {
				$shipping_parameter_post .= (strlen($shipping_parameter_post))? '&': '';
				$shipping_parameter_post .= 'Width='.round($module_params['Width']*10);
			} else {
				$r->errors .= str_replace("{param_name}", "Width", AP_PARAMETER_REQUIRED_MSG) . "<br>\n";
			}
			if (isset($package["height"]) && $package["height"]>0) {
				$shipping_parameter_post .= (strlen($shipping_parameter_post))? '&': '';
				$shipping_parameter_post .= 'Height='.round($package['height']*10);
			} elseif (isset($module_params["Height"]) && $module_params["Height"]>0) {
				$shipping_parameter_post .= (strlen($shipping_parameter_post))? '&': '';
				$shipping_parameter_post .= 'Height='.round($module_params['Height']*10);
			} else {
				$r->errors .= str_replace("{param_name}", "Height", AP_PARAMETER_REQUIRED_MSG) . "<br>\n";
			}
			$shipping_parameter_post .= (strlen($shipping_parameter_post))? '&': '';
			$shipping_parameter_post .= 'Quantity='.intval(ceil($package['quantity']));

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $external_url.'?'.$shipping_parameter_post);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			$post_response = curl_exec($ch);
			curl_close($ch);
			$post_response = str_replace("\r", "", $post_response);
			$rated_shipment = explode("\n", $post_response);

			//$rated_shipment=file($external_url.'?'.$shipping_parameter_post);
			$err_msg = explode ('=', $rated_shipment[2]);
			if(isset($err_msg[1]) && strtoupper(trim($err_msg[1]))=='OK'){
				$charges = explode ('=', $rated_shipment[0]);
				$days = explode ('=', $rated_shipment[1]);
				$shipping_charge += floatval($charges[1]);
				$shipping_day = trim($days[0]);
				if(floatval(trim($days[1])) > $shipping_time){
					$shipping_time = floatval(trim($days[1]));
				}
			}else{
				$shipping_error_package .= trim($err_msg[1]) . "<br>\n";
			}
		}
		if(!strlen($shipping_error_package)){
			$module["cost"] += $shipping_charge;
			$module["desc"] .= ' ('.$shipping_day.' '.$shipping_time.')';
			$shipping_types[] = $module;
		}else{
				$r->errors .= $shipping_error_package;
		}
	}

?>