<?php
	function usps_prepare_rate_request($module_params, $usps_api_name)
	{
		global $r, $db, $table_prefix, $shipping_weight, $state_code, $country_code, $postal_code, $usps_countries, $shipping_packages;

		$xml = "<" . $usps_api_name . "Request";
		// USERID - required, provided during registration
		if (isset($module_params["USERID"]) && strlen($module_params["USERID"])) {
			$xml .= ' USERID="' . $module_params["USERID"] . '"';
		} else {
			$r->errors .= "USPS module error: USERID is required.<br>\n";
		}
		if (isset($module_params["PASSWORD"]) && strlen($module_params["PASSWORD"])) {
			$xml .= ' PASSWORD="' . $module_params["PASSWORD"] . '"';
		}
		$xml .= ">";

		if ($usps_api_name == "RateV4"){
			$xml .= "<Revision/>";
		} else {
			$xml .= "<Revision>2</Revision>";
		}

		foreach($shipping_packages as $package_index => $package) {
			// Pounds, Ounces - required
			$pounds = floor($package["weight"]);
			$ounces = round(($package["weight"] - $pounds) * 16);
			if (!$pounds && !$ounces) {
				$pounds = 0;
				$ounces = 1;
			}

			// Number of package - optional
			$xml .= '<Package ID="'.$package_index.'">';
			if ($usps_api_name == "IntlRateV2"){
				$xml .= "<Pounds>" . $pounds . "</Pounds><Ounces>" . $ounces . "</Ounces>";
				if (isset($module_params["Machinable"]) && strlen($module_params["Machinable"])) {
					$xml .= "<Machinable>" . $module_params["Machinable"] . "</Machinable>";
				}
				if (isset($module_params["MailType"]) && strlen($module_params["MailType"])) {
					$xml .= "<MailType>" . $module_params["MailType"] . "</MailType>";
				} else {
					$r->errors .= "USPS module error: MailType is required.<br>\n";
				}
				$valueofcontents = (isset($module_params["ValueOfContents"]) && intval($module_params["ValueOfContents"]) > 0)? $module_params["ValueOfContents"]: 0;
				$xml .= "<ValueOfContents>" . $valueofcontents . "</ValueOfContents>";
				// Country - required, must be from USPS country list
				if (isset($usps_countries[$country_code])) {
					$country_name = $usps_countries[$country_code];
				} else {
					$sql = "SELECT country_name FROM " . $table_prefix . "countries WHERE country_code = " . $db->tosql($country_code, TEXT);
					$country_name = get_db_value($sql);
				}
				if ($country_name) {
					$xml .= "<Country>" . $country_name . "</Country>";
				} else {
					$r->errors .= "USPS module error: Country is required.<br>\n";
				}
				if (isset($module_params["Container"]) && strlen($module_params["Container"])) {
					$xml .= "<Container>" . $module_params["Container"] . "</Container>";
				}
				if (isset($module_params["Size"]) && strlen($module_params["Size"])) {
					$xml .= "<Size>" . $module_params["Size"] . "</Size>";
				} else {
					$r->errors .= "USPS module error: Size is required.<br>\n";
				}
				/*
				To capture the dimensional weight for Large Priority Mail pieces, RateV3 will require
				three new dimension tags for rectangular Priority Mail pieces: Length, Width, and Height;
				and four new dimension tags for non-rectangular pieces: Length, Width, Height, and Girth.
				Shippers will specify in the existing Container tag whether a Large Priority Mail piece
				is rectangular or non-rectangular.
				*/
				if ($package["width"] > 0) {
					$xml .= "<Width>" . $package["width"] . "</Width>";
				} else {
					if (isset($module_params["Width"]) && strlen($module_params["Width"])) {
						$xml .= "<Width>" . $module_params["Width"] . "</Width>";
					}
				}
				if ($package["length"] > 0) {
					$xml .= "<Length>" . $package["length"] . "</Length>";
				} else {
					if (isset($module_params["Length"]) && strlen($module_params["Length"])) {
						$xml .= "<Length>" . $module_params["Length"] . "</Length>";
					}
				}
				if ($package["height"] > 0) {
					$xml .= "<Height>" . $package["height"] . "</Height>";
				} else {
					if (isset($module_params["Height"]) && strlen($module_params["Height"])) {
						$xml .= "<Height>" . $module_params["Height"] . "</Height>";
					}
				}

				$girth = (isset($module_params["Girth"]) && intval($module_params["Girth"]) > 0)? $module_params["Girth"]: 0;
				$xml .= "<Girth>" . intval($girth) . "</Girth>";

				// United State OriginZip Code will be required to obtain Priority Mail International non-Flat Rate pricing and availability for Canada destinations
				if (isset($module_params["OriginZip"]) && strlen($module_params["OriginZip"])) {
					$xml .= "<OriginZip>" . $module_params["OriginZip"] . "</OriginZip>";
				} else if (isset($module_params["ZipOrigination"]) && strlen($module_params["ZipOrigination"])) {
					$xml .= "<OriginZip>" . $module_params["ZipOrigination"] . "</OriginZip>";
				}
			}
			if ($usps_api_name == "RateV4"){
				// Service - required, one of the following: Express, First Class, Priority, Parcel, BPM, Library, Media, All
				$xml .= "<Service>All</Service>";

				// ZipOrigination - required, valid ZIP code with maximum length of 5 characters
				if (isset($module_params["ZipOrigination"]) && strlen($module_params["ZipOrigination"])) {
					$xml .= "<ZipOrigination>" . $module_params["ZipOrigination"] . "</ZipOrigination>";
				} else {
					$r->errors .= "USPS module error: ZipOrigination is required.<br>\n";
				}

				// ZipDestination - required, valid ZIP code with maximum length of 5 characters
				if (strlen($postal_code)) {
					$xml .= "<ZipDestination>" . $postal_code . "</ZipDestination>";
				} else {
					$r->errors .= "USPS module error: ZipDestination is required.<br>\n";
				}
				$xml .= "<Pounds>" . $pounds . "</Pounds><Ounces>" . $ounces . "</Ounces>";
				if (isset($module_params["Container"]) && strlen($module_params["Container"])) {
					$xml .= "<Container>" . $module_params["Container"] . "</Container>";
				}
				if (isset($module_params["Size"]) && strlen($module_params["Size"])) {
					$xml .= "<Size>" . $module_params["Size"] . "</Size>";
				} else {
					$r->errors .= "USPS module error: Size is required.<br>\n";
				}

				if ($package["width"]) {
					$xml .= "<Width>" . $package["width"] . "</Width>";
				} else {
					if (isset($module_params["Width"]) && strlen($module_params["Width"])) {
						$xml .= "<Width>" . $module_params["Width"] . "</Width>";
					}
				}
				if ($package["length"]) {
					$xml .= "<Length>" . $package["length"] . "</Length>";
				} else {
					if (isset($module_params["Length"]) && strlen($module_params["Length"])) {
						$xml .= "<Length>" . $module_params["Length"] . "</Length>";
					}
				}
				if ($package["height"]) {
					$xml .= "<Height>" . $package["height"] . "</Height>";
				} else {
					if (isset($module_params["Height"]) && strlen($module_params["Height"])) {
						$xml .= "<Height>" . $module_params["Height"] . "</Height>";
					}
				}
				if (isset($module_params["Girth"]) && strlen($module_params["Girth"])) {
					$xml .= "<Girth>" . $module_params["Girth"] . "</Girth>";
				}

				if (isset($module_params["Machinable"]) && strlen($module_params["Machinable"])) {
					$xml .= "<Machinable>" . $module_params["Machinable"] . "</Machinable>";
				}

			}

			$xml .= '</Package>';
		}

		$xml .= '</' . $usps_api_name . 'Request>';

		$xml = str_replace(" ", "%20", $xml);
		return $xml;
	}

	function usps_fill_package($xml_string, $usps_api_name)
	{
		$packages = array();
		preg_match_all("/<Package ID=\"(.*)\">(.*)\<\/Package>/Ui", $xml_string, $packages_raw, PREG_SET_ORDER);
		for ($i = 0; $i < sizeof($packages_raw); $i++) {
			if ($usps_api_name == "RateV4")
			{
				// Parse postages
				$postages = array();
				preg_match_all("/<Postage CLASSID=\"\d+\">(.*)\<\/Postage>/Ui", trim($packages_raw[$i][2]), $postages_raw, PREG_SET_ORDER);
				for ($j = 0; $j < sizeof($postages_raw); $j++) {
					preg_match_all("/<([^>]*?)>([^<]*?)\<\/[^>]*>/", trim($postages_raw[$j][1]), $matches, PREG_SET_ORDER);
					for ($k = 0; $k < sizeof($matches); $k++) {
						$postages[$j][$matches[$k][1]] = ($matches[$k][2]);
					}
				}
				$packages_raw[$i][2] = preg_replace("/<Postage>.*\<\/Postage>/i", "", $packages_raw[$i][2]);
				$packages[$packages_raw[$i][1]]["Postages"] = $postages;
				// convert xml into array
				preg_match_all("/<([^>]*?)>([^<]*?)\<\/[^>]*>/", trim($packages_raw[$i][2]), $matches, PREG_SET_ORDER);
				for ($j = 0; $j < sizeof($matches); $j++) {
					$packages[$packages_raw[$i][1]][$matches[$j][1]] = ($matches[$j][2]);
				}
			}
			elseif ($usps_api_name == "IntlRateV2")
			{
				// Parse services
				$services = array();
				preg_match_all("/<Service ID=\"(.*)\">(.*)\<\/Service>/Ui", trim($packages_raw[$i][2]), $services_raw, PREG_SET_ORDER);
				for ($j = 0; $j < sizeof($services_raw); $j++) {
					preg_match_all("/<([^>]*?)>([^<]*?)\<\/[^>]*>/", trim($services_raw[$j][2]), $matches, PREG_SET_ORDER);
					for ($k = 0; $k < sizeof($matches); $k++) {
						$services[$services_raw[$j][1]][$matches[$k][1]] = ($matches[$k][2]);
					}
				}
				$services_raw[$i][2] = preg_replace("/<Service ID=\".*\">.*\<\/Service>/i", "", $services_raw[$i][2]);
				$packages[$packages_raw[$i][1]]["Services"] = $services;
				// convert xml into array
				preg_match_all("/<([^>]*?)>([^<]*?)\<\/[^>]*>/", trim($packages_raw[$i][2]), $matches, PREG_SET_ORDER);
				for ($j = 0; $j < sizeof($matches); $j++) {
					$packages[$packages_raw[$i][1]][$matches[$j][1]] = ($matches[$j][2]);
				}
			}
		}
		return $packages;
	}

	function usps_check_errors($xml_string)
	{
		$errors = array();
		preg_match_all("/<Error>(.*)\<\/Error>/Ui", $xml_string, $errors_raw, PREG_SET_ORDER);
		for ($i = 0; $i < sizeof($errors_raw); $i++) {
			// convert xml into array
			preg_match_all("/<([^>]*?)>([^<]*?)\<\/[^>]*>/", trim($errors_raw[$i][1]), $matches, PREG_SET_ORDER);
			for ($j = 0; $j < sizeof($matches); $j++) {
				$errors[$i][$matches[$j][1]] = ($matches[$j][2]);
			}
		}
		return $errors;
	}
?>