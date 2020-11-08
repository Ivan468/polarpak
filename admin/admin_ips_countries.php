<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_ips_countries.php                                  ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once($root_folder_path . "includes/record.php");

	check_admin_security("static_tables");

	$errors = "";
	$operation = get_param("operation");
	$rnd = get_param("rnd");
	$session_rnd = get_session("session_rnd");
	$s_ip = get_param("s_ip");

	if ($operation == "import" && $rnd != $session_rnd) {

		set_session("session_rnd", $rnd);
		$csv_file = "../db/IPCountry.csv";

		$is_file_path = true;
		if (file_exists($csv_file)) {
			$fp = fopen($csv_file, "r");
			if (!$fp) {
				$errors = CANT_OPEN_IMPORTED_MSG;
			}
		} else {
			$errors = FILE_DOESNT_EXIST_MSG . "<b>$csv_file</b>";
		}

		if (!strlen($errors)) {
			$sql = " DELETE FROM " . $table_prefix . "ips_countries ";
			$db->query($sql);
			while ($data = fgetcsv($fp, 1024, ",")) {
				$ip_start = $data[0];
				$ip_end = $data[1];
				$country_code = $data[2];
				// convert unsigned integer to integer
				if ($ip_start > 2147483647) { $ip_start -= 4294967296; }
				if ($ip_end > 2147483647) { $ip_end -= 4294967296; }

				$sql  = " INSERT INTO " . $table_prefix . "ips_countries (ip_start, ip_end, country_code) VALUES (";
				$sql .= $db->tosql($ip_start, INTEGER) . ", ";
				$sql .= $db->tosql($ip_end, INTEGER) . ", ";
				$sql .= $db->tosql($country_code, TEXT) . ") ";
				$db->query($sql);
			}
			fclose($fp);
			// update UK code to GB accordingly to ISO standards
			$sql = " UPDATE " . $table_prefix . "ips_countries SET country_code='GB' WHERE country_code='UK'";
			$db->query($sql);
		}

	}


  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_ips_countries.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("rnd", va_timestamp());

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_ips_countries.php");
	$s->set_sorter(IP_ADDRESS_MSG, "sorter_ip_start", 1, "ip_start", "", "", true);
	$s->set_sorter(ACTION_MSG, "sorter_ip_end", 2, "ip_end");
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_ips_countries.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");


	$r = new VA_Record($table_prefix . "users");
	$r->add_textbox("s_ip", TEXT, IP_ADDRESS_MSG);
	$r->change_property("s_ip", REGEXP_MASK, "/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/"); // 0-255 [0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5] 
	$r->get_form_parameters();
	$is_valid = $r->validate();
	$r->set_form_parameters();
	$where = "";
	if ($s_ip && $is_valid) {
		$where  = " WHERE ip_start<=" . ip2long($s_ip);
		$where .= " AND ip_end>=" . ip2long($s_ip);
	}

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "ips_countries " . $where);
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query("SELECT * FROM " . $table_prefix . "ips_countries " . $where . $s->order_by);
	if($db->next_record())
	{
		$t->set_var("no_records", "");
		do {
			$address_action = $db->f("address_action");
			if ($address_action == 1) {
				$address_action = BLOCK_ALL_ACTIVITIES_MSG;
			} else {
				$address_action = WARNING_MSG;
			}

			$ip_start = $db->f("ip_start");
			$ip_end = $db->f("ip_end");
			$country_code = $db->f("country_code");
			$t->set_var("ip_start", long2ip($ip_start));
			$t->set_var("ip_end", long2ip($ip_end));
			$t->set_var("country_code", $country_code);

			$t->parse("records", true);
		} while($db->next_record());
	}
	else
	{
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	if ($errors) {
		$t->set_var("errors_list", $errors);
		$t->parse("errors", false);
	}


	$t->pparse("main");

?>
