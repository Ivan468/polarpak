<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_countries.php                                      ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once($root_folder_path . "includes/profile_functions.php");
	include_once("./admin_common.php");

	check_admin_security("static_tables");

	$operation = get_param("operation");
	$option = get_param("option");
	$country_id = get_param("country_id");

	if ($operation == "phone-codes") {
		check_phone_codes(); // check if we can update some countries with phone codes
	} else if ($operation == "activate") {
		if ($option == "show_for_user" || $option == "delivery_for_user" || $option == "show_for_admin" || $option == "delivery_for_admin") {
			$sql  = " UPDATE ".$table_prefix."countries ";
			$sql .= " SET ".$option."=1 ";
			$sql .= " WHERE country_id=".$db->tosql($country_id, INTEGER);
			$db->query($sql);
		}
	} else if ($operation == "disable") {
		if ($option == "show_for_user" || $option == "delivery_for_user" || $option == "show_for_admin" || $option == "delivery_for_admin") {
			$sql  = " UPDATE ".$table_prefix."countries ";
			$sql .= " SET ".$option."=0 ";
			$sql .= " WHERE country_id=".$db->tosql($country_id, INTEGER);
			$db->query($sql);
		}
	}
	

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_countries.html");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_lookup_tables_href", "admin_lookup_tables.php");
	$t->set_var("admin_country_href", "admin_country.php");
	$t->set_var("admin_settings_list", "admin_settings_list.php");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_countries.php");
	$s->set_parameters(false, true, true, false);
	$s->set_default_sorting("2", "asc");
	$s->set_sorter(ID_MSG, "sorter_country_id", 1, "country_id");
	$s->set_sorter(ADMIN_ORDER_MSG, "sorter_country_order", 2, "country_order");
	$s->set_sorter(COUNTRY_NAME_MSG, "sorter_country_name", 3, "country_name");
	$s->set_sorter(ISO_NUMBER_MSG, "sorter_country_iso_number", 4, "country_iso_number");
	$s->set_sorter(COUNTRY_CODE_MSG, "sorter_country_code", 5, "country_code");
	$s->set_sorter(COUNTRY_CODE_ALPHA3_MSG, "sorter_country_code_alpha3", 6, "country_code_alpha3");
	$s->set_sorter(PHONE_CODE_MSG, "sorter_phone_code", 7, "phone_code");
	$s->set_sorter(USER_MSG, "sorter_show_for_user", 8, "show_for_user");
	$s->set_sorter(ADMIN_MSG, "sorter_show_for_admin", 8, "show_for_admin");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_countries.php");

	$sp = trim(get_param("sp")); 
	$where = "";
	if (strlen($sp)) {
		$where  = " WHERE country_name LIKE '%" . $db->tosql($sp, TEXT, false) . "%'";
		$where .= " OR country_code=" . $db->tosql($sp, TEXT);
		$where .= " OR country_iso_number=" . $db->tosql($sp, TEXT);
	}

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "countries " . $where);
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);
	$t->set_var("page", $page_number);

	// global list url object
	$list_url = new VA_URL("admin_countries.php", false);
	$list_url->add_parameter("category_id", REQUEST, "category_id");
	$list_url->add_parameter("sp", GET, "sp");
	$list_url->add_parameter("sw", GET, "sw");
	$list_url->add_parameter("page", GET, "page");
	$list_url->add_parameter("sort_ord", GET, "sort_ord");
	$list_url->add_parameter("sort_dir", GET, "sort_dir");
	// url object to add/edit country 
	$edit_url = new VA_URL("admin_country.php", false);
	$edit_url->parameters = $list_url->parameters;
	// url object to update country option from the list
	$update_url = new VA_URL("admin_countries.php", false);
	$update_url->parameters = $list_url->parameters;

	$t->set_var("sort_ord", get_param("sort_ord"));
	$t->set_var("sort_dir", get_param("sort_dir"));
	$t->set_var("page", get_param("page"));
	$t->set_var("sp", htmlspecialchars($sp));
	$t->set_var("sp_url", urlencode($sp));

	$t->set_var("admin_country_new_url", $edit_url->get_url());


	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query("SELECT * FROM " . $table_prefix . "countries " . $where . $s->order_by);
	if ($db->next_record())
	{
		$t->set_var("no_records", "");
		do {
			$country_id = $db->f("country_id");
			$show_for_user = $db->f("show_for_user");
			$delivery_for_user = $db->f("delivery_for_user");
			$show_for_admin = $db->f("show_for_admin");
			$delivery_for_admin = $db->f("delivery_for_admin");

			$t->set_var("country_id", $db->f("country_id"));
			$t->set_var("country_name", get_translation($db->f("country_name")));
			$t->set_var("country_order", $db->f("country_order"));
			$t->set_var("country_iso_number", $db->f("country_iso_number"));
			$t->set_var("country_code", $db->f("country_code"));
			$t->set_var("country_code_alpha3", $db->f("country_code_alpha3"));
			$t->set_var("phone_code", $db->f("phone_code"));

			$edit_url->add_parameter("country_id", CONSTANT, $country_id);
			$t->set_var("admin_country_url", $edit_url->get_url());

			$update_url->add_parameter("country_id", CONSTANT, $country_id);
			$update_url->add_parameter("option", CONSTANT, "show_for_user");
			if ($show_for_user) { 
				$show_for_user_class = "ico-personal";
				$update_url->add_parameter("operation", CONSTANT, "disable");
			} else {
				$show_for_user_class = "ico-no-personal";
				$update_url->add_parameter("operation", CONSTANT, "activate");
			}
			$t->set_var("show_for_user_class", $show_for_user_class);
			$t->set_var("show_for_user_url", $update_url->get_url());
			$t->sparse("show_for_user", false);

			$update_url->add_parameter("option", CONSTANT, "delivery_for_user");
			if ($delivery_for_user) { 
				$delivery_for_user_class = "ico-delivery";
				$update_url->add_parameter("operation", CONSTANT, "disable");
			} else {
				$delivery_for_user_class = "ico-no-delivery";
				$update_url->add_parameter("operation", CONSTANT, "activate");
			}
			$t->set_var("delivery_for_user_class", $delivery_for_user_class);
			$t->set_var("delivery_for_user_url", $update_url->get_url());
			$t->sparse("delivery_for_user", false);

			$update_url->add_parameter("option", CONSTANT, "show_for_admin");
			if ($show_for_admin) { 
				$show_for_admin_class = "ico-personal";
				$update_url->add_parameter("operation", CONSTANT, "disable");
			} else {
				$show_for_admin_class = "ico-no-personal";
				$update_url->add_parameter("operation", CONSTANT, "activate");
			}
			$t->set_var("show_for_admin_class", $show_for_admin_class);
			$t->set_var("show_for_admin_url", $update_url->get_url());
			$t->sparse("show_for_admin", false);

			$update_url->add_parameter("option", CONSTANT, "delivery_for_admin");
			if ($delivery_for_admin) { 
				$delivery_for_admin_class = "ico-delivery";
				$update_url->add_parameter("operation", CONSTANT, "disable");
			} else {
				$delivery_for_admin_class = "ico-no-delivery";
				$update_url->add_parameter("operation", CONSTANT, "activate");
			}
			$t->set_var("delivery_for_admin_class", $delivery_for_admin_class);
			$t->set_var("delivery_for_admin_url", $update_url->get_url());
			$t->sparse("delivery_for_admin", false);

			$t->parse("records", true);
		} while ($db->next_record());
	}
	else
	{
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	$t->pparse("main");


function check_phone_codes() {
	global $db, $table_prefix;

	$phone_codes = array(
		// +1: North American Numbering Plan countries and territories
		"CA"=>"+1", "US"=>"+1", "AG"=>"+1", "AI"=>"+1", "AS"=>"+1", "BB"=>"+1", "BM"=>"+1", "BS"=>"+1", 
		"DM"=>"+1", "DO"=>"+1", "GD"=>"+1", "GU"=>"+1", "JM"=>"+1", "KN"=>"+1", "KY"=>"+1", "LC"=>"+1", 
		"MP"=>"+1", "MS"=>"+1", "PR"=>"+1", "SX"=>"+1", "TC"=>"+1", "TT"=>"+1", "VC"=>"+1", "VG"=>"+1", "VI"=>"+1",
  
		"BS"=>"+1 242", "BB"=>"+1 246", "AI"=>"+1 264", "AG"=>"+1 268", "VG"=>"+1 284",
		"VI"=>"+1 340", "KY"=>"+1 345",
		"BM"=>"+1 441", "GD"=>"+1 473",              
		"TC"=>"+1 649", "MS"=>"+1 664", "MP"=>"+1 670", "GU"=>"+1 671", "AS"=>"+1 684",
		"SX"=>"+1 721", "LC"=>"+1 758", "DM"=>"+1 767", "VC"=>"+1 784", "PR"=>"+1 787",              
		"DO"=>"+1 809", "DO"=>"+1 829", "DO"=>"+1 849", "TT"=>"+1 868", "KN"=>"+1 869", "JM"=>"+1 876",
		"PR"=>"+1 939",
  
		"EG"=>"+20", "SS"=>"+211", "MA"=>"+212", "DZ"=>"+213", "TN"=>"+216", "LY"=>"+218",          
		"GM"=>"+220", "SN"=>"+221", "MR"=>"+222", "ML"=>"+223", "GN"=>"+224", "CI"=>"+225", "BF"=>"+226", "NE"=>"+227", "TG"=>"+228", "BJ"=>"+229",             
		"MU"=>"+230", "LR"=>"+231", "SL"=>"+232", "GH"=>"+233", "NG"=>"+234", "TD"=>"+235", "CF"=>"+236", "CM"=>"+237", "CV"=>"+238", "ST"=>"+239",             
		"GQ"=>"+240", "GA"=>"+241", "CG"=>"+242", "CD"=>"+243", "AO"=>"+244", "GW"=>"+245", "IO"=>"+246", "AC"=>"+247", "SC"=>"+248", "SD"=>"+249", 
		"RW"=>"+250", "ET"=>"+251", "SO"=>"+252", "DJ"=>"+253", "KE"=>"+254", "TZ"=>"+255", "UG"=>"+256", "BI"=>"+257", "MZ"=>"+258",             
		"ZM"=>"+260", "MG"=>"+261", "RE"=>"+262", "YT"=>"+262", "TF"=>"+262", "ZW"=>"+263", "NA"=>"+264", "MW"=>"+265", "LS"=>"+266", "BW"=>"+267", "SZ"=>"+268", "KM"=>"+269", 
		"ZA"=>"+27", "SH"=>"+290", "TA"=>"+290", "ER"=>"+291", "AW"=>"+297", "FO"=>"+298", "GL"=>"+299", 
  
		"GR"=>"+30", "NL"=>"+31", "BE"=>"+32", "FR"=>"+33", "ES"=>"+34", 
		"GI"=>"+350", "PT"=>"+351", "LU"=>"+352", "IE"=>"+353", "IS"=>"+354", "AL"=>"+355", "MT"=>"+356", "CY"=>"+357", "FI"=>"+358", "AX"=>"+358", "BG"=>"+359", 
		"HU"=>"+36", "LT"=>"+370", "LV"=>"+371", "EE"=>"+372", "MD"=>"+373", "AM"=>"+374", "QN"=>"+374", "BY"=>"+375", "AD"=>"+376", "MC"=>"+377", "SM"=>"+378", "VA"=>"+379", 
		"UA"=>"+380", "RS"=>"+381", "ME"=>"+382", "XK"=>"+383", "HR"=>"+385", "SI"=>"+386", "BA"=>"+387", "EU"=>"+388", "MK"=>"+389", 
		"IT"=>"+39", "VA"=>"+39", 
  
		"RO"=>"+40", "CH"=>"+41", "CZ"=>"+420", "SK"=>"+421", "LI"=>"+423", 
		"AT"=>"+43", "GB"=>"+44", "UK"=>"+44", "GG"=>"+44", "IM"=>"+44", "JE"=>"+44", 
		"DK"=>"+45", "SE"=>"+46", "NO"=>"+47", "SJ"=>"+47", "PL"=>"+48", "DE"=>"+49", 
			
		"FK"=>"+500", "GS"=>"+500", "BZ"=>"+501", "GT"=>"+502", "SV"=>"+503", "HN"=>"+504", "NI"=>"+505", "CR"=>"+506", "PA"=>"+507", "PM"=>"+508", "HT"=>"+509", 
		"PE"=>"+51", "MX"=>"+52", "CU"=>"+53", "AR"=>"+54", "BR"=>"+55", "CL"=>"+56", "CO"=>"+57", "VE"=>"+58", 
		"GP"=>"+590", "BL"=>"+590", "MF"=>"+590", "BO"=>"+591", "GY"=>"+592", "EC"=>"+593", "GF"=>"+594", 
		"PY"=>"+595", "MQ"=>"+596", "SR"=>"+597", "UY"=>"+598", "BQ"=>"+599", "CW"=>"+599", 
  
		"MY"=>"+60", "AU"=>"+61", "CX"=>"+61", "CC"=>"+61", "ID"=>"+62", "PH"=>"+63", "NZ"=>"+64", "PN"=>"+64", "SG"=>"+65", "TH"=>"+66", 
		"TL"=>"+670", "NF"=>"+672", "AQ"=>"+672", "BN"=>"+673", "NR"=>"+674", "PG"=>"+675", 
		"TO"=>"+676", "SB"=>"+677", "VU"=>"+678", "FJ"=>"+679",              
		"PW"=>"+680", "WF"=>"+681", "CK"=>"+682", "NU"=>"+683", "WS"=>"+685", "KI"=>"+686", "NC"=>"+687", "TV"=>"+688", "PF"=>"+689", 
		"TK"=>"+690", "FM"=>"+691", "MH"=>"+692", 
  
		"RU"=>"+7", "KZ"=>"+7", 
  
		"JP"=>"+81","KR"=>"+82", "VN"=>"+84", "KP"=>"+850", "HK"=>"+852", "MO"=>"+853", "KH"=>"+855", "LA"=>"+856", 
		"CN"=>"+86", "BD"=>"+880", "TW"=>"+886", 
  
		"TR"=>"+90", "CT"=>"+90", "IN"=>"+91", "PK"=>"+92", "AF"=>"+93", "LK"=>"+94", "MM"=>"+95", 
		"MV"=>"+960", "LB"=>"+961", "JO"=>"+962", "SY"=>"+963", "IQ"=>"+964", "KW"=>"+965", "SA"=>"+966", "YE"=>"+967", "OM"=>"+968", 
		"PS"=>"+970", "AE"=>"+971", "IL"=>"+972", "BH"=>"+973", "QA"=>"+974", "BT"=>"+975", "MN"=>"+976", "NP"=>"+977", 
		"IR"=>"+98", "TJ"=>"+992", "TM"=>"+993", "AZ"=>"+994", "GE"=>"+995", "KG"=>"+996", "UZ"=>"+998", 

		"AN"=>"+599",
		"UM"=>"+1",
		"EH"=>"+212",
		"BV"=>"+47",
		"HM"=>"+672",
	);

	$va_countries = va_countries();
	foreach ($va_countries as $country_id => $country_data) {
		$country_code = $country_data["country_code"];
		$phone_code = trim($country_data["phone_code"]);
		$default_phone_code = trim(get_setting_value($phone_codes, $country_code));
		if (!$phone_code && $default_phone_code) {
			// set default code
			$sql  = " UPDATE ".$table_prefix."countries SET phone_code=".$db->tosql($default_phone_code, TEXT);
			$sql .= " WHERE country_code=".$db->tosql($country_code, TEXT);
			$db->query($sql);
		}
	}

}

?>