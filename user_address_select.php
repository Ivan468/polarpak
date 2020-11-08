<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  user_address_select.php                                  ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/sorter.php");
	include_once("./includes/navigator.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	check_user_security("user_addresses");

	$sw = trim(get_param("sw"));
	$select_type = get_param("select_type");
	$address_type = get_param("address_type");
	if (!$address_type) {
		$address_type = $select_type;
	}

	$address_types_values = array(
		array("1", PERSONAL_DETAILS_MSG),
		array("2", DELIVERY_DETAILS_MSG),
	);

	$t = new VA_Template($settings["templates_dir"]);
	$t->set_file("main","user_address_select.html");
	$t->set_var("user_address_select_href", "user_address_select.php");
	$t->set_var("select_type", htmlspecialchars($select_type));
	// set search parameters 
	$t->set_var("sw", htmlspecialchars($sw));
	set_options($address_types_values, $address_type, "address_type");

	$css_file = "";
	if (isset($settings["style_name"]) && $settings["style_name"]) {
		$css_file = "styles/" . $settings["style_name"];
		if (isset($settings["scheme_name"]) && $settings["scheme_name"]) {
			$css_file .= "_" . $settings["scheme_name"];
		}
		$css_file .= ".css";
	}
	$t->set_var("css_file", $css_file);

	$s = new VA_Sorter($settings["templates_dir"], "sorter.html", "user_address_select.php");
	$s->set_parameters(false, true, true, false);
	$s->set_default_sorting(1, "asc");
	$s->set_sorter(ID_MSG, "sorter_id", "1", "address_id");
	$s->set_sorter(NAME_MSG, "sorter_name", "2", "name");
	$s->set_sorter(ADDRESS_MSG, "sorter_address", "3", "country_code");

	$where = "";
	$sa = array();
	if ($sw) {
		$sa = explode(" ", $sw);
		for($si = 0; $si < sizeof($sa); $si++) {
			$where .= " AND (ua.name LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
			$where .= " OR ua.first_name LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%' ";
			$where .= " OR ua.last_name LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%' ";
			$where .= " OR ua.company_name LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%' ";
			$where .= " OR ua.city LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%' ";
			$where .= " OR ua.province LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%' ";
			$where .= " OR ua.address1 LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%' ";
			$where .= " OR ua.address2 LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%' ";
			$where .= " OR ua.address3 LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%' ";
			$where .= " OR s.state_name LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%' ";
			$where .= " OR c.country_name LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%' ";
			$where .= " OR ua.postal_code LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%')";
		}
	}
	if ($address_type) {
		$where .= " AND (address_type&" . $db->tosql($address_type, INTEGER).")";
	}

	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "users_addresses ua ";
	$sql .= " LEFT JOIN " . $table_prefix . "countries c ON c.country_id=ua.country_id ";
	$sql .= " LEFT JOIN " . $table_prefix . "states s ON s.state_id=ua.state_id ";
	$sql .= " WHERE ua.user_id=" . $db->tosql(get_session("session_user_id"), INTEGER);
	$sql .= $where;
	$db->query($sql);
	$db->next_record();
	$total_records = $db->f(0);

	// set up variables for navigator
	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", "user_product_select.php");
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);

	$sql  = " SELECT ua.* ";
	$sql .= "	FROM " . $table_prefix . "users_addresses ua ";
	$sql .= " LEFT JOIN " . $table_prefix . "countries c ON c.country_id=ua.country_id ";
	$sql .= " LEFT JOIN " . $table_prefix . "states s ON s.state_id=ua.state_id ";
	$sql .= " WHERE ua.user_id=" . $db->tosql(get_session("session_user_id"), INTEGER);
	$sql .= $where;
	$sql .= $s->order_by;
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query($sql);
	if ($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do {
			$address_id = $db->f("address_id");
			// prepare name
			$name = $db->f("name");
			$first_name = $db->f("first_name");
			$middle_name = $db->f("middle_name");
			$last_name = $db->f("last_name");
			$company_id = $db->f("company_id");
			$company_name = $db->f("company_name");
			$email = $db->f("email");
			if (!strlen($name)) {
				$name = $first_name;
				if ($middle_name) { $name .= " ".$middle_name; }
				if ($last_name) { $name .= " ".$last_name; }
				$name = trim($name);
			}
			// prepare address
			$country_id = $db->f("country_id");
			$country_code = $db->f("country_code");
			$state_id = $db->f("state_id");
			$state_code = $db->f("state_code");
			$provice = $db->f("provice");
			$city = $db->f("city");
			$postal_code = $db->f("postal_code");
			$address1 = $db->f("address1");
			$address2 = $db->f("address2");
			$address3 = $db->f("address3");
			$address = $country_code;
			if ($address && $state_code) { $address .= ", "; }
			$address .= $state_code;
			if ($address && $provice) { $address .= ", "; }
			$address .= $provice;
			if ($address && $city) { $address .= ", "; }
			$address .= $city;
			if ($address && $postal_code) { $address .= ", "; }
			$address .= $postal_code;
			if ($address && $address1) { $address .= ", "; }
			$address .= $address1;
			if ($address && $address2) { $address .= ", "; }
			$address .= $address2;
			if ($address && $address3) { $address .= ", "; }
			$address .= $address3;

			// prepare phones
			$phone = $db->f("phone");
			$daytime_phone = $db->f("daytime_phone");
			$evening_phone = $db->f("evening_phone");
			$cell_phone = $db->f("cell_phone");
			$fax = $db->f("fax");

			$t->set_var("address_id", $address_id);
			$t->set_var("name", htmlspecialchars($name));
			$t->set_var("address", htmlspecialchars($address));

			// prepare js values
			$name_js = str_replace("'", "\\'", ($name));
			$first_name_js = str_replace("'", "\\'", ($first_name));
			$middle_name_js = str_replace("'", "\\'", ($middle_name));
			$last_name_js = str_replace("'", "\\'", ($last_name));
			$company_id_js = str_replace("'", "\\'", ($company_id));
			$company_name_js = str_replace("'", "\\'", ($company_name));
			$email_js = str_replace("'", "\\'", ($email));
			$country_id_js = str_replace("'", "\\'", ($country_id));
			$country_code_js = str_replace("'", "\\'", ($country_code));
			$state_id_js = str_replace("'", "\\'", ($state_id));
			$state_code_js = str_replace("'", "\\'", ($state_code));
			$provice_js = str_replace("'", "\\'", ($provice));
			$city_js = str_replace("'", "\\'", ($city));
			$postal_code_js = str_replace("'", "\\'", ($postal_code));
			$address1_js = str_replace("'", "\\'", ($address1));
			$address2_js = str_replace("'", "\\'", ($address2));
			$address3_js = str_replace("'", "\\'", ($address3));
			$phone_js = str_replace("'", "\\'", ($phone));
			$daytime_phone_js = str_replace("'", "\\'", ($daytime_phone));
			$evening_phone_js = str_replace("'", "\\'", ($evening_phone));
			$cell_phone_js = str_replace("'", "\\'", ($cell_phone));
			$fax_js = str_replace("'", "\\'", ($fax));
			// set js values
			$t->set_var("name_js", htmlspecialchars($name_js));
			$t->set_var("first_name_js", htmlspecialchars($first_name_js));
			$t->set_var("middle_name_js", htmlspecialchars($middle_name_js));
			$t->set_var("last_name_js", htmlspecialchars($last_name_js));
			$t->set_var("company_id_js", htmlspecialchars($company_id_js));
			$t->set_var("company_name_js", htmlspecialchars($company_name_js));
			$t->set_var("email_js", htmlspecialchars($email_js));
			$t->set_var("country_id_js", htmlspecialchars($country_id_js));
			$t->set_var("country_code_js", htmlspecialchars($country_code_js));
			$t->set_var("state_id_js", htmlspecialchars($state_id_js));
			$t->set_var("state_code_js", htmlspecialchars($state_code_js));
			$t->set_var("provice_js", htmlspecialchars($provice_js));
			$t->set_var("city_js", htmlspecialchars($city_js));
			$t->set_var("postal_code_js", htmlspecialchars($postal_code_js));
			$t->set_var("address1_js", htmlspecialchars($address1_js));
			$t->set_var("address2_js", htmlspecialchars($address2_js));
			$t->set_var("address3_js", htmlspecialchars($address3_js));
			$t->set_var("phone_js", htmlspecialchars($phone_js));
			$t->set_var("daytime_phone_js", htmlspecialchars($daytime_phone_js));
			$t->set_var("evening_phone_js", htmlspecialchars($evening_phone_js));
			$t->set_var("cell_phone_js", htmlspecialchars($cell_phone_js));
			$t->set_var("fax_js", htmlspecialchars($fax_js));

			$t->parse("records",true);
		
		} while($db->next_record());
		
	} else {
		$t->set_var("sorters", "");
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}


	if (strlen($sw)) {
		$found_message = str_replace("{found_records}", $total_records, FOUND_PRODUCTS_MSG);
		$found_message = str_replace("{search_string}", htmlspecialchars($sw), $found_message);
		$t->set_var("found_message", $found_message);
		$t->parse("search_results", false);
	}

	$t->pparse("main");

?>