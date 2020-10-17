<?php

	if (!strlen($country_code)) { return; }
	if (strtoupper($country_code) == "US" && !strlen($postal_code)) { return; }
	if ($shipping_weight > 70) { return; }
	global $usps_countries, $r;
	global $is_admin_path, $is_sub_folder;
	$root_folder_path = ((isset($is_admin_path) && $is_admin_path) || (isset($is_sub_folder) && $is_sub_folder)) ? "../" : "./";
	include_once($root_folder_path . "shipping/usps_functions.php");


	// USPS country names
	$usps_countries = array(
		"AD" => "Andorra",
		"AE" => "United Arab Emirates",
		"AF" => "Afghanistan",
		"AG" => "Antigua and Barbuda",
		"AI" => "Anguilla",
		"AL" => "Albania",
		"AM" => "Armenia",
		"AO" => "Angola",
		"AR" => "Argentina",
		"AT" => "Austria",
		"AU" => "Australia",
		"AW" => "Aruba",
		"AZ" => "Azerbaijan",
		"BB" => "Barbados",
		"BD" => "Bangladesh",
		"BE" => "Belgium",
		"BF" => "Burkina Faso",
		"BG" => "Bulgaria",
		"BH" => "Bahrain",
		"BI" => "Burundi",
		"BJ" => "Benin",
		"BM" => "Bermuda",
		"BN" => "Brunei Darussalam",
		"BO" => "Bolivia",
		"BR" => "Brazil",
		"BS" => "Bahamas",
		"BT" => "Bhutan",
		"BW" => "Botswana",
		"BY" => "Belarus",
		"BZ" => "Belize",
		"CA" => "Canada",
		"CH" => "Switzerland",
		"CK" => "Cook Islands (New Zealand)",
		"CL" => "Chile",
		"CM" => "Cameroon",
		"CN" => "China",
		"CO" => "Colombia",
		"CR" => "Costa Rica",
		"CU" => "Cuba",
		"CV" => "Cape Verde",
		"CY" => "Cyprus",
		"CZ" => "Czech Republic",
		"DE" => "Germany",
		"DJ" => "Djibouti",
		"DK" => "Denmark",
		"DZ" => "Algeria",
		"EC" => "Ecuador",
		"EE" => "Estonia",
		"EG" => "Egypt",
		"ER" => "Eritrea",
		"ES" => "Spain",
		"ET" => "Ethiopia",
		"FI" => "Finland",
		"FJ" => "Fiji",
		"FK" => "Falkland Islands",
		"FM" => "Micronesia, Federated States of",
		"FO" => "Faroe Islands",
		"FR" => "France",
		"FX" => "France",
		"GA" => "Gabon",
		"GB" => "United Kingdom (Great Britain and Northern Ireland)",
		"GD" => "Grenada",
		"GE" => "Georgia, Republic of",
		"GF" => "French Guiana",
		"GG" => "Guernsey, Channel Islands (Great Britain)",
		"GH" => "Ghana",
		"GI" => "Gibraltar",
		"GL" => "Greenland",
		"GM" => "Gambia",
		"GP" => "Guadeloupe",
		"GQ" => "Equatorial Guinea",
		"GR" => "Greece",
		"GT" => "Guatemala",
		"GU" => "Guam (U.S. Possession) See DMM",
		"GW" => "Guinea",
		"GY" => "Guyana",
		"HK" => "Hong Kong",
		"HN" => "Honduras",
		"HR" => "Croatia",
		"HT" => "Haiti",
		"HU" => "Hungary",
		"ID" => "Indonesia",
		"IE" => "Ireland",
		"IL" => "Israel",
		"IM" => "Isle of Man (Great Britain)",
		"IN" => "India",
		"IQ" => "Iraq",
		"IR" => "Iran",
		"IS" => "Iceland",
		"IT" => "Italy",
		"JE" => "Jersey (Channel Islands) (Great Britain)",
		"JM" => "Jamaica",
		"JO" => "Jordan",
		"JP" => "Japan",
		"KE" => "Kenya",
		"KG" => "Kyrgyzstan",
		"KH" => "Cambodia",
		"KI" => "Kiribati",
		"KM" => "Comoros",
		"KR" => "Korea, Republic of (South Korea)",
		"KW" => "Kuwait",
		"KY" => "Cayman Islands",
		"KZ" => "Kazakhstan",
		"LB" => "Lebanon",
		"LC" => "Saint Lucia",
		"LI" => "Liechtenstein",
		"LK" => "Sri Lanka",
		"LR" => "Liberia",
		"LS" => "Lesotho",
		"LT" => "Lithuania",
		"LU" => "Luxembourg",
		"LV" => "Latvia",
		"LY" => "Libya",
		"MA" => "Morocco",
		"MC" => "Monaco (France)",
		"MD" => "Moldova",
		"MG" => "Madagascar",
		"MH" => "Marshall Islands, Republic of the",
		"MK" => "Macedonia",
		"ML" => "Mali",
		"MM" => "Myanmar (Burma)",
		"MN" => "Mongolia",
		"MO" => "Macau (Macao)",
		"MP" => "Northern Mariana Islands, Commonwealth of See DMM",
		"MQ" => "Martinique",
		"MR" => "Mauritania",
		"MS" => "Montserrat",
		"MT" => "Malta",
		"MU" => "Mauritius",
		"MV" => "Maldives",
		"MW" => "Malawi",
		"MX" => "Mexico",
		"MY" => "Malaysia",
		"MZ" => "Mozambique",
		"NA" => "Namibia",
		"NC" => "New Caledonia",
		"NF" => "Norfolk Island (Australia)",
		"NI" => "Nicaragua",
		"NL" => "Netherlands",
		"NO" => "Norway",
		"NP" => "Nepal",
		"NR" => "Nauru",
		"NU" => "Niue (New Zealand)",
		"NZ" => "New Zealand",
		"OM" => "Oman",
		"PA" => "Panama",
		"PE" => "Peru",
		"PF" => "French Polynesia",
		"PG" => "Papua New Guinea",
		"PH" => "Philippines",
		"PK" => "Pakistan",
		"PL" => "Poland",
		"PN" => "Pitcairn Island",
		"PR" => "Puerto Rico See DMM",
		"PT" => "Portugal",
		"PW" => "Palau See DMM",
		"PY" => "Paraguay",
		"QA" => "Qatar",
		"RE" => "Reunion",
		"RO" => "Romania",
		"RU" => "Russia",
		"RW" => "Rwanda",
		"SA" => "Saudi Arabia",
		"SB" => "Solomon Islands",
		"SC" => "Seychelles",
		"SD" => "Sudan",
		"SE" => "Sweden",
		"SG" => "Singapore",
		"SI" => "Slovenia",
		"SL" => "Sierra Leone",
		"SM" => "San Marino",
		"SN" => "Senegal",
		"SO" => "Somalia",
		"SR" => "Suriname",
		"ST" => "Sao Tome and Principe",
		"SV" => "El Salvador",
		"SY" => "Syrian Arab Republic",
		"SZ" => "Swaziland",
		"TC" => "Turks and Caicos Islands",
		"TD" => "Chad",
		"TG" => "Togo",
		"TH" => "Thailand",
		"TJ" => "Tajikistan",
		"TK" => "Tokelau (Union) Group (Western Samoa)",
		"TM" => "Turkmenistan",
		"TN" => "Tunisia",
		"TR" => "Turkey",
		"TT" => "Trinidad and Tobago",
		"TV" => "Tuvalu",
		"TW" => "Taiwan",
		"TZ" => "Tanzania",
		"UA" => "Ukraine",
		"UG" => "Uganda",
		"UY" => "Uruguay",
		"UZ" => "Uzbekistan",
		"VA" => "Vatican City",
		"VC" => "Saint Vincent and the Grenadines",
		"VE" => "Venezuela",
		"VN" => "Vietnam",
		"VU" => "Vanuatu",
		"WS" => "Samoa, American (U.S. Possession) See DMM",
		"YE" => "Yemen",
		"YT" => "Mayotte (France)",
		"ZA" => "South Africa",
		"ZM" => "Zambia",
		"ZW" => "Zimbabwe",
		"AN" => "Netherlands Antilles",
		"AQ" => "Antarctica",
		"AS" => "American Samoa",
		"AX" => "Aland Island (Finland)",
		"BA" => "Bosnia–Herzegovina",
		"BV" => "Bouvet Island",
		"CC" => "Cocos Island (Australia)",
		"CD" => "Congo, Democratic Republic of th",
		"CF" => "Central African Rep.",
		"CG" => "Congo, Republic of the (Brazzaville) ",
		"CI" => "Cote D'Ivoire",
		"CS" => "Serbia–Montenegro",
		"CX" => "Christmas Island",
		"DM" => "Dominica",
		"DO" => "Dominican Republic",
		"EH" => "Western Sahara",
		"GN" => "Guinea",
		"GS" => "South Georgia (Falkland Islands)",
		"HM" => "Heard and Mc Donald Islands",
		"IO" => "British Indian Ocean Territory",
		"KN" => "Saint Kitts (St. Christopher and Nevis)",
		"KP" => "Korea, Democratic People’s Republic of (North Korea)",
		"LA" => "Lao People's Democratic Republic",
		"NE" => "Niger",
		"NG" => "Nigeria",
		"PM" => "Saint Pierre and Miquelon",
		"PS" => "Palestinian Territory, Occupied",
		"SH" => "Saint Helena",
		"SJ" => "Svalbard and Jan Mayen Islands",
		"SK" => "Slovakia (Slovak Republic) EU",
		"TF" => "French Southern Territories",
		"TL" => "Timor-Leste",
		"TO" => "Tonga",
		"UM" => "United States Minor Outlying Islands",
		"US" => "United States",
		"VG" => "British Virgin Islands",
		"VI" => "Virgin Islands (U.S.)",
		"WF" => "Wallis And Futuna Islands"
	);

	if (preg_match("/^(\d{4})\-\d+$/", $postal_code, $matches)) {
		$postal_code = $matches[1];
	}

	if (!$external_url) $external_url = "http://production.shippingapis.com/ShippingAPI.dll";

	$usps_url = parse_url($external_url);
	$usps_server = $usps_url["host"];
	$usps_api_lib = $usps_url["path"];
	
	// To know what tool to use - domestic or international
	if (in_array(strtoupper($country_code), array("US", "PR"))) {
		$usps_api_name = "RateV4";
	} else {
		$usps_api_name = "IntlRateV2";
	}

	$xml = usps_prepare_rate_request($module_params, $usps_api_name);
	$result = "";

	$fp = fsockopen($usps_server, 80, $errno, $errstr, 30);
	if (!$fp) {
		$r->errors .= "An error occurred while opening remote server: $errstr ($errno)<br />\n";
	} else {

		$post_params = "API=".$usps_api_name."&XML=".$xml;

		$ch = curl_init ();
		curl_setopt ($ch, CURLOPT_URL, $external_url);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt ($ch, CURLOPT_POST, 1); 
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $post_params);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
	
		$result = curl_exec($ch);
		curl_close($ch);
	}

	if ($result) {
		$result = str_replace("\r", "", $result);
		$result = str_replace("\n", "", $result);
		$pos = strpos($result, "<?xml");
		$result_xml = substr($result, $pos);
		$pos = strpos($result, "?>"); //<?
		$result = trim(substr($result, $pos + 2));
		$pos = strpos($result, "<");
		$result = trim(substr($result, $pos ));

		$errors = usps_check_errors($result);
		if (sizeof($errors) > 0)
		{
			foreach ($errors as $error) {
				if(isset($r->errors)){
					$r->errors .= sprintf("U.S.P.S. Error occurred: %s - %s \n<br>", $error["Number"], $error["Description"]);
				}else{
					$r->errors = sprintf("U.S.P.S. Error occurred: %s - %s \n<br>", $error["Number"], $error["Description"]);
				}
			}
		}
		else
		{
			$packages = usps_fill_package($result, $usps_api_name);
			$rated_shipment=array();
			$i = 0;
			if ($usps_api_name == "RateV4")
			{
				// calculate number of returned packages
				$total_packages = count($packages);
				foreach ($packages as $package) {
					foreach ($package["Postages"] as $postage) {
						$service_code = $postage["MailService"];
						$monetary_value = $postage["Rate"];
						if ($i != 0) {
							// for second and futher packages add value and number of times particulat method was found in methods returned for first package
							foreach ($rated_shipment as $key => $rated) {
								if ($rated[0] == $service_code){
									// method found so can add it cost and increase number of appearance in different packages
									$rated_shipment[$key][1] += $monetary_value; 
									$rated_shipment[$key][2] += 1; 
								}
							}
						} else {
							// for first package saved returned method with it code, delivery cost and how many times it was returned for different packages
							$rated_shipment[] = array($service_code, $monetary_value, 1);
						}
					}
					$i++;
				}
				// check if number of appearance the same as packages number and remove other methods
				foreach ($rated_shipment as $key => $rated) {
					if ($total_packages != $rated[2]) {
						unset($rated_shipment[$key]);
					}
				}
				$rated_shipment = array_values($rated_shipment);
			}
			// International shipping
			elseif ($usps_api_name == "IntlRateV2")
			{
				foreach ($packages as $package) {
					foreach ($package["Services"] as $service) {
						$service_code = $service["SvcDescription"];
						$monetary_value = $service["Postage"];
						if($i != 0){
							foreach ($rated_shipment as $key => $rated) {
								if($rated[0] == $service_code){
									$rated_shipment[$key][1] += $monetary_value;
								}
							}
						}else{
							$rated_shipment[] = array($service_code,$monetary_value);
						}
					}
					$i++;
				}
			}



			foreach ($module_shipping as $shipping_module_id => $shipment_data) {
				$shipping_code = $shipment_data["code"];
				$shipping_desc = $shipment_data["desc"];

				// Domestic shipping
				foreach ($rated_shipment as $rated) {
					// remove special html symbols to match methods without them
					$rated[0] = str_replace(array("&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt;", "&amp;lt;sup&amp;gt;&amp;amp;trade;&amp;lt;/sup&amp;gt;"), "", $rated[0]);
					$rated[0] = str_replace(array("&amp;lt;sup&amp;gt;", "&amp;lt;/sup&amp;gt;", "&amp;#8482;", "&amp;#174;"), array("", "", "", ""), $rated[0]);
					if (preg_match("/^\/.+\/\w*$/", $shipping_code)) {
						// shipping code is already reular expression and we don't need to change it
						$shipping_regexp = $shipping_code;
					} else {
						$shipping_regexp = "/^" . preg_quote($shipping_code, "/") . "$/Uis";
					}
					if (preg_match($shipping_regexp, $rated[0])) {
						$shipment_data["cost"] += $rated[1];
						$shipping_types[] = $shipment_data;
					}
				}
			}
		}
	}	else {
		$r->errors .= "USPS server returned no answer.<br>\n";
	}
?>