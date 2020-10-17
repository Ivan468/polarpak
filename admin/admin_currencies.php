<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_currencies.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
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

	// additional connection 
	$dbs = new VA_SQL();
	$dbs->DBType      = $db_type;
	$dbs->DBDatabase  = $db_name;
	$dbs->DBUser      = $db_user;
	$dbs->DBPassword  = $db_password;
	$dbs->DBHost      = $db_host;
	$dbs->DBPort      = $db_port;
	$dbs->DBPersistent= $db_persistent;

	$param_site_id = get_session("session_site_id");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_currencies.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_currency_href", "admin_currency.php");
	$t->set_var("admin_currencies_href", "admin_currencies.php");
	$t->set_var("admin_lookup_tables_href", "admin_lookup_tables.php");

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_currencies.php");
	$s->set_sorter(ID_MSG, "sorter_currency_id", "1", "currency_id", "", "", true);
	$s->set_sorter(CURRENCY_TITLE_MSG, "sorter_currency_title", "2", "currency_title");
	$s->set_sorter(CODE_MSG, "sorter_currency_code", "3", "currency_code");
	$s->set_sorter(EXCHANGE_RATE_MSG, "sorter_exchange_rate", "4", "exchange_rate");
	$s->set_sorter(DEFAULT_MSG, "sorter_is_default", "5", "is_default");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_currencies.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$operation = get_param("operation");

	$error_message = ""; $success_message = "";
	if ($operation == "floatrates") {
		include_once("./admin_floatrates.php");
	} 

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "currencies ");
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query("SELECT * FROM " . $table_prefix . "currencies " . $s->order_by);
	if($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do
		{
			$currency_id = $db->f("currency_id");
			$sites_all = $db->f("sites_all");
			$is_default = $db->f("is_default");
			$is_default_show = $db->f("is_default_show");
			$show_for_user = $db->f("show_for_user");

			$is_site_currency = false;
			if ($sites_all) {
				$is_site_currency = true;
			} else {
				// check if layout available for current site
				$sql  = " SELECT site_id FROM " . $table_prefix . "currencies_sites ";
				$sql .= " WHERE currency_id=" . $db->tosql($currency_id, INTEGER);
				$sql .= " AND site_id=" . $db->tosql($param_site_id, INTEGER, true, false);
				$dbs->query($sql);
				if ($dbs->next_record()) {
					$is_site_currency = true;
				}
			}

			$t->set_var("currency_id", $currency_id);
			$t->set_var("currency_title", htmlspecialchars($db->f("currency_title")));
			$t->set_var("currency_code", htmlspecialchars($db->f("currency_code")));
			$t->set_var("exchange_rate", $db->f("exchange_rate"));

			$is_default = $db->f("is_default");
			if ($is_default) {
				$is_default_desc = "<font color=\"blue\"><b>" . YES_MSG . "</b></font>";
			} else  {
				$is_default_desc = "<font color=\"silver\">" . NO_MSG . "</font>";
			} 
			$t->set_var("is_default", $is_default_desc);

			if ($is_site_currency) {
				if ($is_default) {
					$currency_status = "<span class=\"active\">".ACTIVE_MSG."</span>";
				} else {
					$currency_status = "<span class=\"active\">".ACTIVE_MSG."</span>";
				}
			} else {
				$currency_status = "<span class=\"nonactive\">".NOT_AVAILABLE_MSG."</span>";
			}
			$t->set_var("currency_status", $currency_status);

			$t->parse("records", true);
		} while($db->next_record());
	}
	else
	{
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	if ($error_message) {
		$t->set_var("errors_list", $error_message);
		$t->sparse("errors", false);
	} 

	if ($success_message) {
		$t->set_var("success_message", $success_message);
		$t->sparse("success", false);
	} 

	// multisites
	if ($sitelist) {
		$sites   = get_db_values("SELECT site_id,site_name FROM " . $table_prefix . "sites ORDER BY site_id ", "");
		set_options($sites, $param_site_id, "param_site_id");
		$t->parse("sitelist", false);
	}	

	$t->pparse("main");
?>
