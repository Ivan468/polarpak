<?php

	$default_title = "{MY_ADDRESSES_MSG}: {LIST_MSG}";

	check_user_security("user_addresses");

	$user_id = get_session("session_user_id");
	$sw = trim(get_param("sw"));
	
	$html_template = get_setting_value($block, "html_template", "block_user_addresses.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("user_addresses_href",  "user_addresses.php");
	$t->set_var("user_address_href",   "user_address.php");
	$t->set_var("user_home_href", "user_home.php");
	$t->set_var("sw", htmlspecialchars($sw));

	$s = new VA_Sorter($settings["templates_dir"], "sorter.html", "user_addresses.php");
	$s->set_default_sorting(1, "asc");
	$s->set_sorter(ID_MSG, "sorter_id", "1", "address_id");
	$s->set_sorter(NAME_MSG, "sorter_name", "2", "name");
	$s->set_sorter(ADDRESS_MSG, "sorter_address", "3", "country_code");

	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", "user_addresses.php");

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

	// set up variables for navigator
	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "users_addresses ua ";
	$sql .= " LEFT JOIN " . $table_prefix . "countries c ON c.country_id=ua.country_id ";
	$sql .= " LEFT JOIN " . $table_prefix . "states s ON s.state_id=ua.state_id ";
	$sql .= " WHERE ua.user_id=" . $db->tosql($user_id, INTEGER);
	$sql .= $where;
	$db->query($sql);
	$db->next_record();
	$total_records = $db->f(0);

	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);
		
	$sql  = " SELECT ua.* ";
	$sql .= "	FROM " . $table_prefix . "users_addresses ua ";
	$sql .= " LEFT JOIN " . $table_prefix . "countries c ON c.country_id=ua.country_id ";
	$sql .= " LEFT JOIN " . $table_prefix . "states s ON s.state_id=ua.state_id ";
	$sql .= " WHERE ua.user_id=" . $db->tosql($user_id, INTEGER);
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
			$last_name = $db->f("last_name");
			$company_name = $db->f("company_name");
			if (!strlen($name)) {
				$name = trim($first_name." ".$last_name);
			}
			// prepare address
			$country_code = $db->f("country_code");
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

			$t->set_var("address_id", $address_id);
			$t->set_var("name", htmlspecialchars($name));
			$t->set_var("address", htmlspecialchars($address));

			$t->parse("records",true);
		
		} while($db->next_record());
		
	} else {
		$t->set_var("sorters", "");
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}
	
	$block_parsed = true;

?>