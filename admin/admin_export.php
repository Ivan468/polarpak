<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_export.php                                         ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	@set_time_limit (900);
	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ($root_folder_path . "includes/record.php");
	include_once ($root_folder_path . "includes/url.php");
	include_once ($root_folder_path . "includes/export_functions.php");
	include_once ($root_folder_path . "messages/".$language_code."/cart_messages.php");
	include_once ($root_folder_path . "messages/".$language_code."/reviews_messages.php");
	include_once ($root_folder_path . "messages/".$language_code."/download_messages.php");
	include_once("./admin_common.php");

	check_admin_security("import_export");

	if (!isset($script_run_mode)) { $script_run_mode = ""; }
	if (!isset($va_cc_data_export)) { $va_cc_data_export = false; }

	// special array to keep custom property values
	$user_properties = array();

	// show all errors
	$db->DebugError = true;

	// special connection to export data
	$dbe = new VA_SQL();
	$dbe->DBType       = $db->DBType;
	$dbe->DBDatabase   = $db->DBDatabase;
	$dbe->DBUser       = $db->DBUser;
	$dbe->DBPassword   = $db->DBPassword;
	$dbe->DBHost       = $db->DBHost;
	$dbe->DBPort       = $db->DBPort;
	$dbe->DBPersistent = $db->DBPersistent;
	$dbe->DebugError = true;
	
	$comma_decimal = false;
	if ($comma_decimal) {
		$prices = array('price', 'buying_price', 'trade_price', 'properties_price', 'trade_properties_price',
			'sales_price', 'trade_sales');
	}
	
	$apply_translation = false;
	$eol = get_eol();
	$delimiters_symbols = array(
		"comma" => ",", "tab" => "\t", "semicolon" => ";", "vertical_bar" => "|",
		"row" => "row", "space" => " ", "newline" => $eol);

	$delimiters = array(array("comma", COMMA_MSG), array("tab", TAB_MSG), array("semicolon", SEMICOLON_MSG), array("vertical_bar", VERTICAL_BAR_MSG));
	$related_delimiters = array(array("row", ROWS_MSG), array("comma", COMMA_MSG), array("tab", TAB_MSG), array("space", SPACE_MSG), array("semicolon", SEMICOLON_MSG), array("vertical_bar", VERTICAL_BAR_MSG), array("newline", NEWLINE_MSG));

	
	$errors = "";
	$template_errors = "";
	$template_success = "";
	$sql_where = "";
	$rnd = get_param("rnd");
	$table = get_param("table");
	$csv_delimiter = get_param("csv_delimiter");
	$related_delimiter = get_param("related_delimiter");
	$delimiter_symbol = isset($delimiters_symbols[$csv_delimiter]) ? $delimiters_symbols[$csv_delimiter] : ",";
	$related_delimiter_symbol = isset($delimiters_symbols[$related_delimiter]) ? $delimiters_symbols[$related_delimiter] : "row";
	$operation = get_param("operation");
	$category_id = get_param("category_id");
	$newsletter_id = get_param("newsletter_id");
	$session_rnd = get_session("session_rnd");
	$id = get_param("id");
	$ids = get_param("ids");
	$s_on = get_param("s_on"); // order number / users online
	$s_ne = get_param("s_ne");
	$s_kw = get_param("s_kw");
	$s_sd = get_param("s_sd"); // start date
	$s_ed = get_param("s_ed"); // end date
	$a_sd = get_param("a_sd"); // last activity - start date
	$a_ed = get_param("a_ed"); // last activity - end date
	$s_ad = get_param("s_ad"); // users address
	$s_ut = get_param("s_ut"); // user type
	$s_ap = get_param("s_ap"); // approved
	$s_os = get_param("s_os");
	$s_ci = get_param("s_ci");
	$s_si = get_param("s_si");
	$s_ex = get_param("s_ex");
	$s_pd = get_param("s_pd");
	$s_ps = get_param("s_ps");
	$s_cct = get_param("s_cct");
	$s_sti = get_param("s_sti");	
	$s_rn = get_param("s_rn"); // registration number
	$s_ap = get_param("s_ap"); // approved
	$s_pi = get_param("s_pi"); // product id
	
	$type = get_param("type"); // to separate filtered and all requests)

	$s = trim(get_param("s"));
	$spt = get_param("spt"); // product type - item_type_id 
	$sm = get_param("sm"); // manufacturer_id
	$sc = get_param("sc");
	$sit = get_param("sit");
	$sl = get_param("sl");
	$ss = get_param("ss");
	$ap = get_param("ap"); // is_approved

	// order action parameter
	$order_status_update = get_param("order_status_update");

	if (!isset($t)) {
		$t = new VA_Template($settings["admin_templates_dir"]);
	}

	if ($script_run_mode != "cron" && $script_run_mode != "crontab" && $script_run_mode != "include") {
		$t->set_file("main","admin_export.html");
		include_once("./admin_header.php");
		include_once("./admin_footer.php");
	}

	$t->set_var("admin_select_href",     "admin_select.php");
	$t->set_var("admin_export_href",     "admin_export.php");
	$t->set_var("admin_items_list_href", "admin_items_list.php");
	$t->set_var("admin_users_list_href", "admin_newsletter_users.php");

	// export object to related links
	$admin_export_url = new VA_URL("admin_export.php", false);
	$admin_export_url->add_parameter("category_id", REQUEST, "category_id");
	$admin_export_url->add_parameter("id", REQUEST, "id");
	$admin_export_url->add_parameter("ids", REQUEST, "ids");
	$admin_export_url->add_parameter("s", REQUEST, "s");
	$admin_export_url->add_parameter("sl", REQUEST, "sl");
	$admin_export_url->add_parameter("ss", REQUEST, "ss");
	$admin_export_url->add_parameter("ap", REQUEST, "ap");

	$admin_export_custom_url = new VA_URL("admin_export_custom.php", true, array("table"));
	$admin_export_custom_url->add_parameter("table", CONSTANT, $table);
	$t->set_var("admin_export_custom_url", $admin_export_custom_url->get_url());

	$is_export = true;
	if ($table == "items" || $table == "items_files" || $table == "items_properties_values" || $table == "items_prices"  || $table == "items_serials" || $table == "serials") {

		check_admin_security("products_export");
		if ($table == "items") {
			include("./admin_table_items.php");
		} elseif ($table == "items_files") {
			include("./admin_table_items_files.php");
		} elseif ($table == "items_properties_values") {
			include("./admin_table_items_properties_values.php");
		} elseif ($table == "items_prices") {
			include("./admin_table_items_prices.php");
		} else if ($table == "items_serials" || $table == "serials") {
			include("./admin_table_serials.php");
		}
		$sql_join_before = "";
		$sql_join        = "";

		if ($table == "items_properties_values") {
			$sql_join_before .=	" (( ";
			$sql_join  .= " LEFT JOIN " . $table_prefix . "items_properties ip ON ip.property_id=ipv.property_id) ";
			$sql_join  .= " LEFT JOIN " . $table_prefix . "items i ON i.item_id=ip.item_id)";
		} else if ($table == "items_prices") {
			$sql_join_before .=	" ( ";
			$sql_join  .= " LEFT JOIN " . $table_prefix . "items i ON i.item_id=pc.item_id)";
		}
		
		if (strlen($id)) {
			$sql_where = " WHERE i.item_id=" . $dbe->tosql($id, INTEGER);
		} else if (strlen($ids)) {
			$sql_where = " WHERE i.item_id IN (" . $dbe->tosql($ids, TEXT, false) . ")";
		} else {
			
			$category_id = get_param("category_id");
			$search = (strlen($s) || strlen($sit) || strlen($sl) || strlen($sm) || strlen($spt) || strlen($ss) || strlen($ap) || strlen($s_sti)) ? true : false;
			if ($sc) { $category_id = $sc; }
			
			$sa = "";
			$tree = new VA_Tree("category_id", "category_name", "parent_category_id", $table_prefix . "categories", "tree");
			
			$where = array();	
			if ($search && $category_id != 0) {
				$where[] = " c.category_id = ic.category_id ";
				$where[] = " (ic.category_id = " . $dbe->tosql($category_id, INTEGER)
						 . " OR c.category_path LIKE '" . $dbe->tosql($tree->get_path($category_id), TEXT, false) . "%')";
				$sql_join_before .=	" (( ";
				$sql_join  .= " LEFT JOIN " . $table_prefix . "items_categories ic ON i.item_id=ic.item_id) ";
				$sql_join  .= " LEFT JOIN " . $table_prefix . "categories c ON c.category_id = ic.category_id)";
			} elseif (!$search && strlen($category_id)) {
				$where []= " ic.category_id = " . $dbe->tosql($category_id, INTEGER);
				$sql_join_before .=	" ( ";
				$sql_join  .= " LEFT JOIN " . $table_prefix . "items_categories ic ON i.item_id=ic.item_id) ";
			}

			if (strlen($sm)) {
				$where[] = " i.manufacturer_id= " . $dbe->tosql($sm, INTEGER);
			}
			if (strlen($spt)) {
				$where[] = " i.item_type_id= " . $dbe->tosql($spt, INTEGER);
			}
								
			if ($s) {
				$sa = explode(" ", $s);
				for($si = 0; $si < sizeof($sa); $si++) {
					$sa[$si] = str_replace("%","\%",$sa[$si]);
					$where[] = " (i.item_name LIKE '%" . $dbe->tosql($sa[$si], TEXT, false) . "%'"
							 .  " OR i.item_code LIKE '%" . $dbe->tosql($sa[$si], TEXT, false) . "%' "
							 .  " OR i.manufacturer_code LIKE '%" . $dbe->tosql($sa[$si], TEXT, false) . "%')";
				}
			}
			if ($sit == 2) {
				$where[] = " (tiny_image = '' OR tiny_image IS NULL)";
				$where[] = " (small_image = '' OR small_image IS NULL)";
				$where[] = " (big_image = '' OR big_image IS NULL)";
				$where[] = " (super_image = '' OR super_image IS NULL)";
			}

			if (strlen($sl)) {
				if ($sl == 1) {
					$where[] = " (i.stock_level>0 OR i.stock_level IS NULL) ";
				} else {
					$where[] = " i.stock_level<1 ";
				}
			}
			if (strlen($ss)) {
				if ($ss == 1) {
					$where[] = " i.is_showing=1 ";
				} else {
					$where[] = " i.is_showing=0 ";
				}
			}
			if (strlen($ap)) {
				if ($ap == 1) {
					$where[] = " i.is_approved=1 ";
				} else {
					$where[] = " i.is_approved=0 ";
				}
			}
			if (strlen($s_sti)) {
				if ($s_sti == "all") {
					$where[] = " i.sites_all=1 ";
				} else {
					$sql_join_before .= "(";
					$sql_join .= " LEFT JOIN " . $table_prefix . "items_sites s ON (s.item_id = i.item_id AND i.sites_all = 0 )) ";
					$where[] = " (s.site_id=" . $dbe->tosql($s_sti, INTEGER) . " OR i.sites_all=1) ";
				}
			}
			
			if (count($where)) {
				$sql_where = " WHERE " . implode (" AND ", $where);				
			}
		}
	} else if ($table == "categories") {
		check_admin_security("categories_export");
		include("./admin_table_categories.php");
	} else if ($table == "registrations") {
		check_admin_security("admin_registration");
		include("./admin_table_registrations.php");
		
		$where = "";
		if (strlen($id)) {
			$where = " reg.registration_id=" . $dbe->tosql($id, INTEGER);
		} else if (strlen($ids)) {
			$where = " reg.registration_id IN (" . $dbe->tosql($ids, TEXT, false) . ")";
		} else {
			if ($s_rn) {
				if (preg_match("/^(\d+)(,\d+)*$/", $s_rn))	{
					$where  = " (reg.registration_id IN (" . $s_rn . ") ";
					$where .= " OR reg.invoice_number=" . $dbe->tosql($s_rn, TEXT);
					$where .= " OR reg.serial_number=" . $dbe->tosql($s_rn, TEXT) . ") ";
				} else {
					$where .= " (reg.invoice_number=" . $dbe->tosql($s_rn, TEXT);
					$where .= " OR reg.serial_number=" . $dbe->tosql($s_rn, TEXT) . ") ";
				}
			}
			
			if ($s_pi) {
				if (strlen($where)) { $where .= " AND "; }
				$where .= " reg.item_id=" . $dbe->tosql($s_pi, INTEGER);
			}
			
			if ($s_ne) {
				if (strlen($where)) { $where .= " AND "; }
				$s_ne_sql = $dbe->tosql($s_ne, TEXT, false);
				
				$where .= " (u.name LIKE '%" . $s_ne_sql . "%'";
				$name_parts = explode(" ", $s_ne, 2);
				if (sizeof($name_parts) == 1) {
					$where .= " OR u.first_name LIKE '%" . $s_ne_sql . "%'";
					$where .= " OR u.last_name LIKE '%" . $s_ne_sql . "%'";
				} else {
					$where .= " OR (u.first_name LIKE '%" . $dbe->tosql($name_parts[0], TEXT, false) . "%' ";
					$where .= " AND u.last_name LIKE '%" . $dbe->tosql($name_parts[1], TEXT, false) . "%') ";
				}
				$where .= ") ";	
			}
			
			if ($s_kw) {
				if (strlen($where)) { $where .= " AND "; }
				$s_kw_sql = $dbe->tosql($s_kw, TEXT, false);
				$where .= " (reg.item_name LIKE '%" . $s_kw_sql . "%'";
				$where .= " OR reg.item_code LIKE '%" . $s_kw_sql . "%'";
				$where .= " OR it.item_name  LIKE '%" . $s_kw_sql . "%'";
				$where .= " OR it.item_code  LIKE '%" . $s_kw_sql . "%')";
			}
			
			if ($s_sd) {
				if (strlen($where)) { $where .= " AND "; }
				$where .= " reg.date_added>=" . $dbe->tosql($s_sd, DATE);
			}
			if ($s_ed) {
				if (strlen($where)) { $where .= " AND "; }
				$day_after_end = mktime (0, 0, 0, $s_ed[MONTH], $s_ed[DAY] + 1, $s_ed[YEAR]);
				$where .= " reg.date_added<" . $dbe->tosql($day_after_end, DATE);
			}		
			
			if (strlen($s_ap)) {
				if (strlen($where)) { $where .= " AND "; }
				if ($s_ap) {
					$where .= " reg.is_approved=1";
				} else {
					$where .= " reg.is_approved=0";
				}
			}
		}
		if ($where) {
			$sql_where = " WHERE " . $where;				
		}
			
	} else if ($table == "users") {
		check_admin_security("export_users");
		include("./admin_table_users.php");
		if (strlen($id)) {
			$sql_where = " WHERE user_id>" . $dbe->tosql($id, INTEGER);
		} else if (strlen($ids)) {
			$sql_where = " WHERE user_id IN (" . $dbe->tosql($ids, TEXT, false) . ")";
		} else {
			if (strlen($s_ne)) {
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				$s_ne_sql = $dbe->tosql($s_ne, TEXT, false);
				$sql_where .= " (u.email LIKE '%" . $s_ne_sql . "%'";
				$sql_where .= " OR u.login LIKE '%" . $s_ne_sql . "%'";
				$sql_where .= " OR u.name LIKE '%" . $s_ne_sql . "%'";
				$sql_where .= " OR u.first_name LIKE '%" . $s_ne_sql . "%'";
				$sql_where .= " OR u.last_name LIKE '%" . $s_ne_sql . "%'";
				$sql_where .= " OR u.company_name LIKE '%" . $s_ne_sql . "%')";
			}
	  
			if (strlen($s_ad)) {
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				$sql_where .= " (u.address1 LIKE '%" . $dbe->tosql($s_ad, TEXT, false) . "%'";
				$sql_where .= " OR u.address2 LIKE '%" . $dbe->tosql($s_ad, TEXT, false) . "%'";
				$sql_where .= " OR u.address3 LIKE '%" . $dbe->tosql($s_ad, TEXT, false) . "%'";
				$sql_where .= " OR u.city LIKE '%" . $dbe->tosql($s_ad, TEXT, false) . "%'";
				$sql_where .= " OR u.province LIKE '%" . $dbe->tosql($s_ad, TEXT, false) . "%'";
				$sql_where .= " OR u.state_id LIKE '%" . $dbe->tosql($s_ad, TEXT, false) . "%'";
				$sql_where .= " OR u.zip LIKE '%" . $dbe->tosql($s_ad, TEXT, false) . "%'";
				$sql_where .= " OR u.country_id LIKE '%" . $dbe->tosql($s_ad, TEXT, false) . "%'";
				$sql_where .= " OR s.state_name LIKE '%" . $dbe->tosql($s_ad, TEXT, false) . "%'";
				$sql_where .= " OR c.country_name LIKE '%" . $dbe->tosql($s_ad, TEXT, false) . "%')";
				$sql_join_before = " ((";
				$sql_join  = " LEFT JOIN " . $table_prefix . "countries c ON u.country_id=c.country_id) ";
				$sql_join .= " LEFT JOIN " . $table_prefix . "states s ON u.state_id=s.state_id)";
			}
	  
			if (strlen($s_sd)) {
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				$s_sd_value = parse_date($s_sd, $date_edit_format, $date_errors);
				$sql_where .= " u.registration_date>=" . $dbe->tosql($s_sd_value, DATE);
			}
	  
			if (strlen($s_ed)) {
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				$end_date = parse_date($s_ed, $date_edit_format, $date_errors);
				$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
				$sql_where .= " u.registration_date<" . $dbe->tosql($day_after_end, DATE);
			}

			if (strlen($a_sd)) {
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				$a_sd_value = parse_date($a_sd, $date_edit_format, $date_errors);
				$sql_where .= " u.last_visit_date>=" . $dbe->tosql($a_sd_value, DATE);
			} 
			if (strlen($a_ed)) {
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				$end_date = parse_date($a_ed, $date_edit_format, $date_errors);
				$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
				$sql_where .= " u.last_visit_date<" . $dbe->tosql($day_after_end, DATE);
			}

			if (strlen($s_ut)) {
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				$sql_where .= " u.user_type_id=" . $dbe->tosql($s_ut, INTEGER);
			}
	  
			if (strlen($s_ap)) {
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				$sql_where .= ($s_ap == 1) ? " u.is_approved=1 " : " u.is_approved=0 ";
			}
	  
			if (strlen($s_on)) {
				$current_date = va_time();
				$cyear = $current_date[YEAR]; $cmonth = $current_date[MONTH]; $cday = $current_date[DAY];
				$online_ts = mktime ($current_date[HOUR], $current_date[MINUTE] - $online_time, $current_date[SECOND], $cmonth, $cday, $cyear);
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				if ($s_on == 1) {
					$sql_where .= " u.last_visit_date>=" . $dbe->tosql($online_ts, DATETIME);
				} else {
					$sql_where .= " u.last_visit_date<" . $dbe->tosql($online_ts, DATETIME);
				}
			}
			if ($sql_where) { $sql_where = " WHERE " . $sql_where; }
		}
	} else if ($table == "newsletters_emails") {
		check_admin_security("newsletter");
		include("./admin_table_newsletters_emails.php");
		if (strlen($ids)) {
			$sql_where = " WHERE email_id IN (" . $dbe->tosql($ids, INTEGERS_LIST) . ")";
		} else {
			if (strlen($newsletter_id)) {
				$sql_where = " WHERE newsletter_id=" . $dbe->tosql($newsletter_id, INTEGER);
			}
		}
	} else if ($table == "newsletters_users") {
		check_admin_security("export_users");
		include("./admin_table_emails.php");
		if (strlen($id)) {
			$sql_where = " WHERE email_id>" . $dbe->tosql($id, INTEGER);
		} else if (strlen($ids)) {
			$sql_where = " WHERE email_id IN (" . $dbe->tosql($ids, TEXT, false) . ")";
		}
	} else if ($table == "orders") {
		check_admin_security("sales_orders");
		include_once($root_folder_path."includes/order_items.php");
		include_once($root_folder_path."includes/order_links.php");
		include("./admin_table_orders.php");

		$sql_join  = " INNER JOIN " . $table_prefix . "orders_items oi ON o.order_id=oi.order_id ";
		$sql_join .= " LEFT JOIN " . $table_prefix . "orders_shipments sh ON oi.order_shipping_id=sh.order_shipping_id ";

		if (strlen($id)) {
			$sql_where .= " WHERE o.order_id>" . $dbe->tosql($id, INTEGER);
		} else if (strlen($ids)) {
			$sql_where .= " WHERE o.order_id IN (" . $dbe->tosql($ids, TEXT, false) . ")";
		} else {

			$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type='order_info' ";
			$sql .= " AND (site_id=1 OR site_id=" . $dbe->tosql($site_id,INTEGER) . ") ";
			$sql .= " ORDER BY site_id ASC ";
			$dbe->query($sql);
			while($dbe->next_record()) {
				$order_info[$dbe->f("setting_name")] = $dbe->f("setting_value");
			}

			if (preg_match("/^(\d+)(,\d+)*$/", $s_on))	{
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				$sql_where .= " (o.order_id IN (" . $dbe->tosql($s_on, TEXT, false) . ") ";
				$sql_where .= " OR o.invoice_number=" . $dbe->tosql($s_on, TEXT);
				$sql_where .= " OR o.transaction_id=" . $dbe->tosql($s_on, TEXT) . ") ";
			} else if (strlen($s_on)) {
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				$sql_where .= " (o.invoice_number=" . $dbe->tosql($s_on, TEXT);
				$sql_where .= " OR o.transaction_id=" . $dbe->tosql($s_on, TEXT) . ") ";
			}

			if(strlen($s_ne)) {
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				$s_ne_sql = $dbe->tosql($s_ne, TEXT, false);
				$sql_where .= " (o.email LIKE '%" . $s_ne_sql . "%'";
				$sql_where .= " OR o.name LIKE '%" . $s_ne_sql . "%'";
				$sql_where .= " OR o.first_name LIKE '%" . $s_ne_sql . "%'";
				$sql_where .= " OR o.last_name LIKE '%" . $s_ne_sql . "%')";
			}

			if(strlen($s_kw)) {
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				$sql_where .= " (oi.item_name LIKE '%" . $dbe->tosql($s_kw, TEXT, false) . "%'";
				$sql_where .= " OR oi.item_properties LIKE '%" . $dbe->tosql($s_kw, TEXT, false) . "%'";
				$sql_where .= " OR o.shipping_type_desc LIKE '%" . $dbe->tosql($s_kw, TEXT, false) . "%')";
			}

			if(strlen($s_sd)) {
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				$s_sd_value = parse_date($s_sd, $date_edit_format, $date_errors);
				$sql_where .= " o.order_placed_date>=" . $dbe->tosql($s_sd_value, DATE);
			}

			if(strlen($s_ed)) {
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				$end_date = parse_date($s_ed, $date_edit_format, $date_errors);
				$day_after_end = mktime (0, 0, 0, $end_date[MONTH], $end_date[DAY] + 1, $end_date[YEAR]);
				$sql_where .= " o.order_placed_date<" . $dbe->tosql($day_after_end, DATE);
			}

			if(strlen($s_os)) {
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				$sql_where .= " o.order_status IN (" . $dbe->tosql($s_os, INTEGERS_LIST) . ")";
			}

			if(strlen($s_ci)) {
				if ($order_info["show_delivery_country_id"] == 1) {
					if (strlen($sql_where)) { $sql_where .= " AND "; }
					$sql_where .= " o.delivery_country_id=" . $dbe->tosql($s_ci, INTEGER);
				} else if ($order_info["show_country_id"] == 1) {
					if (strlen($sql_where)) { $sql_where .= " AND "; }
					$sql_where .= " o.country_id=" . $dbe->tosql($s_ci, INTEGER);
				}
			}
			if(strlen($s_si)) {
				if ($order_info["show_delivery_state_id"] == 1) {
					if (strlen($sql_where)) { $sql_where .= " AND "; }
					$sql_where .= " o.delivery_state_id=" . $dbe->tosql($s_si, INTEGER);
				} else if ($order_info["show_state_id"] == 1) {
					if (strlen($sql_where)) { $sql_where .= " AND "; }
					$sql_where .= " o.state_id=" . $dbe->tosql($s_si, INTEGER);
				}
			}
			if(strlen($s_sti)) {
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				$sql_where .= " o.site_id=" . $dbe->tosql($s_sti, INTEGER);				
			}

			if (strlen($s_ex)) {
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				$sql_where .= ($s_ex == 1) ? " o.is_exported=1 " : " (o.is_exported<>1 OR o.is_exported IS NULL) ";
			}

			if (strlen($s_pd)) {
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				$sql_join .= " INNER JOIN " . $table_prefix . "order_statuses os ON os.status_id=o.order_status ";
				$sql_where .= ($s_pd == 1) ? " os.paid_status=1 " : " (os.paid_status=0 OR os.paid_status IS NULL) ";
			}

			if(strlen($s_ps)) {
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				$sql_where .= " o.payment_id=" . $dbe->tosql($s_ps, INTEGER);
			}

			if(strlen($s_cct)) {
				if (strlen($sql_where)) { $sql_where .= " AND "; }
				$sql_where .= " o.cc_type=" . $dbe->tosql($s_cct, INTEGER);
			}
			
			if (!$sql_where && $type == "filtered") {
				$dbe->query("SELECT status_id FROM " . $table_prefix . "order_statuses WHERE (is_list=1 OR is_list IS NULL)");
				if ($dbe->next_record()) {
					$orders_statuses = array();
					do {
						$orders_statuses[] = $dbe->f("status_id");						
					} while ($dbe->next_record());					
					if (strlen($sql_where)) { $sql_where .= " AND "; }
					$sql_where .= " o.order_status IN ( " . $dbe->tosql($orders_statuses, INTEGERS_LIST) . " ) ";
				}			
			}
			if ($sql_where) { $sql_where = " WHERE " . $sql_where; }
		}
	} else if ($table == "tax_rates") {
		include("./admin_table_tax_rates.php");
	} else {
		$table_name = "";
		$table_title = "";
		$errors = CANT_FIND_TABLE_MSG;
	}	

	if (strlen(!$errors)) {
		$admin_export_custom_url->add_parameter("table", CONSTANT, $table);

		$sql  = " SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings ";
		$sql .= " WHERE setting_type=" . $dbe->tosql($table, TEXT);
		$sql .= " AND (site_id=1 OR site_id=" . $dbe->tosql($site_id,INTEGER) . ") ";
		$sql .= " ORDER BY site_id ASC ";
		$dbe->query($sql);
		while ($dbe->next_record()) {
			$custom_field = $dbe->f("setting_name");
			$custom_value = $dbe->f("setting_value");
			$admin_export_custom_url->add_parameter("field", CONSTANT, $custom_field);

			$edit_link = "<a href=\"" . $admin_export_custom_url->get_url() . "\"><font color=blue size=1>".EDIT_BUTTON."</font></a>";
			$db_columns[$custom_field]  = array($custom_field, TEXT, CUSTOM_FIELD, false, $custom_value, $edit_link);
		}
		if(isset($related_columns)) {
			$admin_export_custom_url->add_parameter("table", CONSTANT, $related_table);

			$sql  = " SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type=" . $dbe->tosql($related_table, TEXT);
			$sql .= " AND (site_id=1 OR site_id=" . $dbe->tosql($site_id,INTEGER) . ") ";
			$sql .= " ORDER BY site_id ASC ";
			$dbe->query($sql);
			while ($dbe->next_record()) {
				$custom_field = $dbe->f("setting_name");
				$custom_value = $dbe->f("setting_value");
				$admin_export_custom_url->add_parameter("field", CONSTANT, $custom_field);
				$edit_link = "<a href=\"" . $admin_export_custom_url->get_url() . "\"><font color=blue size=1>".EDIT_BUTTON."</font></a>";
				$related_columns[$custom_field]  = array("title" => $custom_field, "data_type" => TEXT, "field_type" => CUSTOM_FIELD, "required" => false, "custom_value" => $custom_value, "edit_link" => $edit_link);
			}
		}
	}

	$t->set_var("table", $table);
	$t->set_var("table_title", $table_title);

	if ($operation == "save_template") {
		$template_name = get_param("template_name");
	  if (!strlen($template_name)) {
			$template_errors = str_replace("{field_name}", EXPORT_TEMPLATE_MSG, REQUIRED_MESSAGE);
		}

		if(!strlen($errors) && !strlen($template_errors)) {
			// save new export template
			$r = new VA_Record($table_prefix . "export_templates");
			$r->add_where("template_id", INTEGER);
			$r->add_textbox("template_name", TEXT);
			$r->add_textbox("table_name", TEXT);
			$r->add_textbox("admin_id_added_by", INTEGER);
			$r->add_textbox("date_added", DATETIME);
			
			if ($db_type == "postgre") {
				$new_template_id = get_db_value(" SELECT NEXTVAL('seq_" . $table_prefix . "export_templates') ");
				$r->change_property("template_id", USE_IN_INSERT, true);
				$r->set_value("template_id", $new_template_id);
			}

			$r->set_value("template_name", $template_name);
			$r->set_value("table_name", $table);
			$r->set_value("admin_id_added_by", get_session("session_admin_id"));
			$r->set_value("date_added", va_time());
			$r->insert_record();

			if ($db_type == "mysql") {
				$new_template_id = get_db_value(" SELECT LAST_INSERT_ID() ");
				$r->set_value("template_id", $new_template_id);
			} elseif ($db_type == "access") {
				$new_template_id = get_db_value(" SELECT @@IDENTITY ");
				$r->set_value("template_id", $new_template_id);
			} elseif ($db_type == "db2") {
				$new_template_id = get_db_value(" SELECT PREVVAL FOR seq_" . $table_prefix . "export_templates FROM " . $table_prefix . "export_templates");
				$r->set_value("template_id", $new_template_id);
			}

			if (strlen($new_template_id)) {
				// start adding fields
				$fld = new VA_Record($table_prefix . "export_fields");
				$fld->add_where("field_id", INTEGER);
				$fld->add_textbox("template_id", INTEGER);
				$fld->set_value("template_id", $new_template_id);
				$fld->add_textbox("field_order", INTEGER);
				$fld->add_textbox("field_title", TEXT);
				$fld->add_textbox("field_source", TEXT);

				$field_order = 0;
				$total_columns = get_param("total_columns");
				for($col = 1; $col <= $total_columns; $col++) {
					$field_title = get_param("column_title_" . $col);
					$field_source = get_param("field_source_" . $col);
					$column_checked = get_param("db_column_" . $col);
					if($column_checked) { // if there is column title we can save this field even if it source empty
						$field_order++;
						$fld->set_value("field_order", $field_order);
						$fld->set_value("field_title", $field_title);
						$fld->set_value("field_source", $field_source);
						$fld->insert_record();
					}
				}
			}
			$template_success = EXPORT_TEMPLATE_SAVED_MSG;
		}
	} else if($operation == "export")	{
		if(!strlen($errors)) {

			// prepare countries and states for export
			$countries = array(); $states = array();
			$sql = "SELECT country_id,country_name FROM " . $table_prefix . "countries ";
			$dbe->query($sql);
			while ($dbe->next_record()) {
				$country_id = $dbe->f("country_id");
				$country_name = get_translation($dbe->f("country_name"));
				$countries[$country_id] = $country_name;
			}
			$sql = "SELECT state_id,state_name FROM " . $table_prefix . "states ";
			$dbe->query($sql);
			while ($dbe->next_record()) {
				$state_id = $dbe->f("state_id");
				$state_name = get_translation($dbe->f("state_name"));
				$states[$state_id] = $state_name;
			}

			// prepare categories for items table
			$categories = array();
			if ($table == "items") {
				$sql = "SELECT category_id,category_name FROM " . $table_prefix . "categories ";
				$dbe->query($sql);
				while ($dbe->next_record()) {
					$category_id = $dbe->f("category_id");
					$category_name = $dbe->f("category_name");
					if ($apply_translation) {
						$category_name = get_translation($category_name);
					}
					$categories[$category_id] = $category_name;
				}
			}

			// connection for additional operations
			$dbs = new VA_SQL();
			$dbs->DBType       = $dbe->DBType;
			$dbs->DBDatabase   = $dbe->DBDatabase;
			$dbs->DBUser       = $dbe->DBUser;
			$dbs->DBPassword   = $dbe->DBPassword;
			$dbs->DBHost       = $dbe->DBHost;
			$dbs->DBPort       = $dbe->DBPort;
			$dbs->DBPersistent = $dbe->DBPersistent;

			$columns = array();
			$total_columns = get_param("total_columns");
			$columns_selected = 0;
			$db_column        = 0;
			$columns_list     = "";
			$csv_columns_list = "";
			$exported_fields  = "";

			// generate db columns list and get data for custom properties
			foreach ($db_columns as $column_name => $column_info) {
				if (isset($column_info["title"])) { // new format
					$field_type = $column_info["field_type"];
				} else { // old format
					$field_type = $column_info[2];
				}

				if (preg_match("/^user_property_(\d+)$/", $column_name, $matches)) {
					$property_id = substr($column_name, 14);

					// check property settings 
					$control_type = ""; $options_values_sql = "";
					$sql  = " SELECT control_type, options_values_sql FROM " . $table_prefix . "user_profile_properties ";
					$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
					$db->query($sql);
					if ($db->next_record()) {
						$control_type = $db->f("control_type");
						$options_values_sql = $db->f("options_values_sql");
						$user_properties[$property_id] = array(
							"control_type" => $control_type,
							"values" => array(),
						);
					}

					if ($control_type == "CHECKBOXLIST" ||  $control_type == "RADIOBUTTON" || $control_type == "LISTBOX") {
						$property_values = array();
						if ($options_values_sql) {
							$sql = $options_values_sql;
						} else {
							$sql  = " SELECT property_value_id, property_value FROM " . $table_prefix . "user_profile_values ";
							$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER) . " AND hide_value=0";
						}
						$db->query($sql);
						while ($db->next_record()) {
							$value_id = $db->f(0);
							$value_desc = $db->f(1);
							if ($apply_translation) {
								$value_desc = get_translation($value_desc);
							}
							$property_values[$value_id] = $value_desc;
						}
						$user_properties[$property_id]["values"] = $property_values;
					}

				} else if($field_type != RELATED_DB_FIELD && $field_type != CUSTOM_FIELD) {
					if (!preg_match("/^order_property_/", $column_name)
						&& !preg_match("/^items_property_/", $column_name)
						&& !preg_match("/^item_feature_/", $column_name)
						&& !preg_match("/^user_property_/", $column_name)
						&& !preg_match("/^registration_property_/", $column_name)) {
						$db_column++;
						if($db_column > 1) {
							$columns_list .= ", ";
						}				
						$columns_list .= $table_alias . "." . $column_name;
					}
				} else if ($field_type == RELATED_DB_FIELD) {
					// items properties values fields
					if ($column_name == "items_item_name" || $column_name == "items_is_showing" || $column_name == "items_is_approved" || $column_name == "items_item_code" || $column_name == "items_manufacturer_code") {
						$db_column++;
						if($db_column > 1) { $columns_list .= ", "; }				
						$columns_list .= "i." . str_replace("items_", "", $column_name). " AS ".$column_name;
					} else if ($column_name == "properties_property_name") {
						$db_column++;
						if($db_column > 1) { $columns_list .= ", "; }				
						$columns_list .= "ip." . str_replace("properties_", "", $column_name). " AS ".$column_name;
					}
				}
			}

			// generate selected columns
			$related_selected = 0;
			for($col = 1; $col <= $total_columns; $col++) {
				$column_title = get_param("column_title_" . $col);
				$field_source = get_param("field_source_" . $col);
				$column_checked  = get_param("db_column_" . $col);
				if($column_checked) { // get column only if it was checked
					$columns_selected++;
					if($columns_selected > 1) {
						$exported_fields .= "|";
						$csv_columns_list .= $delimiter_symbol;
					}
					$exported_fields .= $column_title;
					if(preg_match("/[,;\"\n\r\t\s]/", $column_title)) {
						$csv_columns_list .= "\"" . str_replace("\"", "\"\"", $column_title) . "\"";
					} else {
						$csv_columns_list .= $column_title;
					}
					$columns[] = $field_source;
					if (preg_match("/^oi_/", $field_source) || preg_match("/\{oi_/", $field_source)) {
						$related_selected++;
						$selected_related_columns[$field_source] = 1;
					}
				}
			}

/* DELETE BLOCK
			$total_related = get_param("total_related");
			for($col = 1; $col <= $total_related; $col++) {
				$column_name = get_param("related_column_" . $col);
				if ($column_name) {
					$related_selected++;
					$columns_selected++;
					if ($related_columns[$column_name][2] == CUSTOM_FIELD) {
						$column_alias = $column_name;
					} else {
						$column_alias = $related_table_alias."_".$column_name;
					}
					if (preg_match("/^order_item_property_/", $column_name)) {
						if ($columns_selected > 1) {
							$csv_columns_list .= $delimiter_symbol;
							$exported_fields .= ",";
						}
					} else {
						if ($columns_selected > 1) {
							//$columns_list .= ",";
							$csv_columns_list .= $delimiter_symbol;
							$exported_fields .= ",";
						}
						//$columns_list .= $related_table_alias.".".$column_name . " AS " . $column_alias;
					}
					if(preg_match("/[,;\"\n\r\t\s]/", $related_columns[$column_name][0])) {
						$csv_columns_list .= "\"" . str_replace("\"", "\"\"", $related_columns[$column_name][0]) . "\"";
					} else {
						$csv_columns_list .= $related_columns[$column_name][0];
					}
					$exported_fields .= $column_alias;
					$columns[] = $column_alias;
					$selected_related_columns[$column_alias] = 1;
				}
			}//*/


			//CUSTOM_FIELD
			if (isset($related_columns)) {
				// generate db columns list
				foreach ($related_columns as $column_name => $column_info) {
					$field_type = $column_info["field_type"];
					if($field_type != RELATED_DB_FIELD && $field_type != CUSTOM_FIELD) {
						$column_alias = $related_table_alias."_".$column_name;
						if (!preg_match("/^order_item_property_/", $column_name)) {
							$columns_list .= ",";
							$columns_list .= $related_table_alias.".".$column_name . " AS " . $column_alias;
						}
					}
				}
			}

			if ($table == "orders") {
				// add shipping fields from orders_shipments table
				$columns_list .= ", sh.shipping_code AS sh_shipping_code, sh.shipping_desc AS sh_shipping_desc, sh.tracking_id AS sh_tracking_id, sh.shipping_cost AS sh_shipping_cost ";
			}

			$exported_fields .= "|csv_delimiter" . $csv_delimiter. "csv_delimiter";
			$exported_fields .= "|related_delimiter" . $related_delimiter . "related_delimiter";
			// update default columns list
			if ($table == "users") {
				$sql  = " UPDATE " . $table_prefix . "admins SET exported_user_fields=" . $dbe->tosql($exported_fields, TEXT);
				$sql .= " WHERE admin_id=" . $dbe->tosql(get_session("session_admin_id"), INTEGER);
				$dbe->query($sql);
			} else if ($table == "newsletters_users") {
				$sql  = " UPDATE " . $table_prefix . "admins SET exported_email_fields=" . $dbe->tosql($exported_fields, TEXT);
				$sql .= " WHERE admin_id=" . $dbe->tosql(get_session("session_admin_id"), INTEGER);
				$dbe->query($sql);
			} else if ($table == "orders") {
				$sql = " UPDATE " . $table_prefix . "admins SET exported_order_fields=" . $dbe->tosql($exported_fields, TEXT);
				$sql .= " WHERE admin_id=" . $dbe->tosql(get_session("session_admin_id"), INTEGER);
				$dbe->query($sql);
			} else if ($table == "items") {
				$sql = " UPDATE " . $table_prefix . "admins SET exported_item_fields=" . $dbe->tosql($exported_fields, TEXT);
				$sql .= " WHERE admin_id=" . $dbe->tosql(get_session("session_admin_id"), INTEGER);
				$dbe->query($sql);
			} else {
				update_admin_settings(array($table."_export_fields" => $exported_fields));
			}

			if (isset($fp) && $fp) {
				fwrite($fp, $csv_columns_list.$eol); 
				if (isset($fp_copy) && isset($file_path_copy) && strlen($file_path_copy)) {
					fwrite($fp_copy, $csv_columns_list.$eol); 
				}
			} else {
				$csv_filename = $table_name . ".csv";
				header("Pragma: private");
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Cache-Control: private", false);
				header("Content-Encoding: UTF-8");
				//header("Content-Type: application/octet-stream; charset=UTF-8");
				header("Content-Type: text/csv; charset=UTF-8");
				header("Content-Disposition: attachment; filename=" . $csv_filename);
				header("Content-Transfer-Encoding: binary");
				echo "\xEF\xBB\xBF"; // UTF-8 BOM
		  
				echo $csv_columns_list . $eol;
			}

			$exported_user_id = 0; $exported_order_id = 0;
			if ($table == "users") {
				$sql  = " SELECT exported_user_id FROM " . $table_prefix . "admins ";
				$sql .= " WHERE admin_id=" . $dbe->tosql(get_session("session_admin_id"), INTEGER);
				$exported_user_id = get_db_value($sql);
			} else if ($table == "orders") {
				$sql  = " SELECT exported_order_id FROM " . $table_prefix . "admins ";
				$sql .= " WHERE admin_id=" . $dbe->tosql(get_session("session_admin_id"), INTEGER);
				$exported_order_id = get_db_value($sql);
			} else if ($table == "newsletters_users") {
				$sql  = " SELECT exported_email_id FROM " . $table_prefix . "admins ";
				$sql .= " WHERE admin_id=" . $dbe->tosql(get_session("session_admin_id"), INTEGER);
				$exported_email_id = get_db_value($sql);
			}

			$max_id = 0;
			// check records number
			$sql = "SELECT COUNT(*) FROM ";
			if (isset($sql_join_before) && $sql_join_before) { $sql .= $sql_join_before; }
			$sql .= $table_name . " " . $table_alias;
			if (isset($sql_join) && $sql_join) { $sql .= $sql_join; }

			$total_records = get_db_value($sql . $sql_where);
			$records_per_page = 1000;
			$total_pages = ceil($total_records / $records_per_page);

			// export data
			$sql = "SELECT " . $columns_list . " FROM ";
			if (isset($sql_join_before) && $sql_join_before) { $sql .= $sql_join_before; }
			$sql .= $table_name . " " . $table_alias;
			if (isset($sql_join) && $sql_join) { $sql .= $sql_join; }
			if (isset($table_pk) && $table_pk) {
				if ($table == "items_properties_values") {
					$order_by = " ORDER BY i.item_id, ipv.property_id, ipv.item_property_id ";
				} else if (strlen($table_alias)) {
					$order_by = " ORDER BY " . $table_alias . "." . $table_pk;
				} else {
					$order_by = " ORDER BY " . $table_pk;
				}
			} else {
				$order_by = "";
			}
			$data_sql = $sql.$sql_where.$order_by;

			// START output data
			$row_data = array(); $record_number = 0; $related_number = 0; $prev_id = "";

			for ($page_number = 1; $page_number <= $total_pages; $page_number++) {

				$dbe->RecordsPerPage = $records_per_page;
				$dbe->PageNumber = $page_number;
				$dbe->query($data_sql);
				while ($dbe->next_record()) {
					if (!strlen($prev_id)) {
						$prev_id = $dbe->f($table_pk);
					}
					$record_number++;
					$row_id = $dbe->f($table_pk);
					if ($row_id > $max_id) { $max_id = $row_id; }
					if ($prev_id != $row_id || ($record_number > 1 && $related_delimiter == "row" && $related_selected > 0)) {
						// output csv
						$csv_row = "";
						for($i = 0; $i < $columns_selected; $i++) {
							$column_name = $columns[$i];
							$field_value = $row_data[$column_name];
							if ($column_name == "oi_item_properties") {
								$field_value = preg_replace("/^\<br\>/", "", $field_value);
								$field_value = preg_replace("/\<br\>/", "; ", $field_value);
							}
							
							if ($comma_decimal && in_array($column_name, $prices)) {
								$field_value = str_replace('.', ',', $field_value);
							}
							
							if (preg_match("/[,;\"\n\r\t\s]/", $field_value)) {
								$field_value = "\"" . str_replace("\"", "\"\"", $field_value) . "\"";
							}
							if($i > 0) {
								$csv_row .= $delimiter_symbol;
							}
							$csv_row .= $field_value;
						}
						if (isset($fp) && $fp) {
							fwrite($fp, $csv_row.$eol); 
							if (isset($fp_copy) && isset($file_path_copy) && strlen($file_path_copy)) {
								fwrite($fp_copy, $csv_row.$eol); 
							}
						} else {
							echo $csv_row.$eol;
							ob_flush();flush();
						}
						// end output
						$related_number = 0;
		  
						// update exported status
						if ($table_name == $table_prefix . "orders") {
							$dbs->query("UPDATE " . $table_prefix . "orders SET is_exported=1 WHERE order_id=" . $prev_id);
							// update order status if such option selected
							if (strlen($order_status_update)) {
								update_order_status($prev_id, $order_status_update, true, "", $status_error);
							}
						}
					}
					$related_number++;
		  
					// collect data for next step
					for($i = 0; $i < $columns_selected; $i++) {
						$column_name = $columns[$i];
		  
						$field_value = "";
						if ($column_name == "item_category") {
							$item_id = $dbe->f("item_id");
							$sql  = " SELECT ic.category_id, c.category_path FROM " . $table_prefix . "items_categories ic ";
							$sql .= " LEFT JOIN " . $table_prefix . "categories c ON ic.category_id=c.category_id ";
							$sql .= " WHERE  ic.item_id=" . $dbe->tosql($item_id, INTEGER);
							$dbs->query($sql);
							while ($dbs->next_record()) {
								$category = "";
								$category_path = $dbs->f("category_path") . $dbs->f("category_id");
								// build full category path if available
								$categories_ids = explode(",", $category_path);
								for ($ci = 0; $ci < sizeof($categories_ids); $ci++) {
									$category_id = $categories_ids[$ci];
									if ($category_id > 0) {
										if (strlen($category)) { $category .= " > "; }
										$category .= $categories[$category_id];
									}
								}
								if (strlen($field_value)) { $field_value .= ";"; }
								// for top category use zero number
								if (!strlen($category)) { $category = 0; }
								$field_value .= $category;
							}
		  
						} else if (preg_match("/^items_property_/", $column_name)) {
							$property_name = substr($column_name, 15);
							$item_id = $dbe->f("item_id");
							$sql  = " SELECT property_id, control_type,property_description FROM " . $table_prefix . "items_properties ";
							$sql .= " WHERE item_id=" . $dbe->tosql($item_id, INTEGER);
							$sql .= " AND property_name=" . $dbe->tosql($property_name, TEXT);
							$dbs->query($sql);
							if ($dbs->next_record()) {
								$property_id = $dbs->f("property_id");
								$control_type = $dbs->f("control_type");
								if ($control_type == "LABEL" || $control_type == "TEXTBOX" || $control_type == "TEXTAREA") {
									if ($apply_translation) {
										$field_value = get_translation($dbs->f("property_description"));
									} else {
										$field_value = $dbs->f("property_description");
									}
								} else {
									$sql  = " SELECT property_value,additional_price FROM " . $table_prefix . "items_properties_values ";
									$sql .= " WHERE property_id=" . $dbe->tosql($property_id, INTEGER);
									$dbs->query($sql);
									while($dbs->next_record()) {
										$option_value = $dbs->f("property_value");
										$additional_price = $dbs->f("additional_price");
										if (strlen($field_value)) { $field_value .= ";"; }
										$field_value .= $option_value;
										if (strlen($additional_price)) {
											$field_value .= "=".$additional_price;
										}
									}
		  
								}
							}
						} else if (preg_match("/^item_feature_(\d+)_(.+)$/", $column_name, $matches)) {
							$group_id = $matches[1];
							$feature_name = $matches[2];
							$item_id = $dbe->f("item_id");
							$sql  = " SELECT fg.group_name, f.feature_name, f.feature_value ";
							$sql .= " FROM (" . $table_prefix . "features f ";
							$sql .= " INNER JOIN " . $table_prefix . "features_groups fg ON f.group_id=fg.group_id) ";
							$sql .= " WHERE f.item_id=" . $dbe->tosql($item_id, INTEGER);
							$sql .= " AND f.group_id=" . $dbe->tosql($group_id, INTEGER);
							$sql .= " AND f.feature_name=" . $dbe->tosql($feature_name, TEXT);
							$dbs->query($sql);
							if ($dbs->next_record()) {
								if ($apply_translation) {
									$field_value = get_translation($dbs->f("feature_value"));
								} else {
									$field_value = $dbs->f("feature_value");
								}
							}
						} else if (preg_match("/^order_property_/", $column_name)) {
							$column_code = substr($column_name, 15);
							$order_id = $dbe->f("order_id");
							$order_properties = array();
							$properties_ids = array();
							if (preg_match("/^\d+$/", $column_code)) {
								$properties_ids[] = $column_code;
							} else { 
								$sql  = " SELECT property_id "; 
								$sql .= " FROM " . $table_prefix . "order_custom_properties ocp ";
								$sql .= " WHERE property_code=" . $db->tosql($column_code, TEXT);
								$dbs->query($sql);
								while ($dbs->next_record()) {
									$property_id = $dbs->f("property_id");       	
									$properties_ids[] = $property_id;
								}
							}
							if (count($properties_ids)) {
								$sql  = " SELECT op.property_id, op.property_type, op.property_name, op.property_value, ";
								$sql .= " op.property_price, op.property_points_amount, op.tax_free ";
								$sql .= " FROM " . $table_prefix . "orders_properties op ";
								$sql .= " WHERE op.order_id=" . $dbe->tosql($order_id, INTEGER);
								$sql .= " AND op.property_id IN (" . $dbe->tosql($properties_ids, INTEGERS_LIST) . ") ";
								$dbs->query($sql);
								while ($dbs->next_record()) {
									$property_value = $dbs->f("property_value");
									if (strlen($field_value)) { $field_value .= "; "; }
									if ($apply_translation) {
										$field_value .= get_translation($property_value);
									} else {
										$field_value .= $property_value;
									}
								}
							}
						} else if (preg_match("/^user_property_(\d+)$/", $column_name, $matches)) {
							$property_id = $matches[1];
							$user_id = $dbe->f("user_id");

							// get user property settings
							$user_property = isset($user_properties[$property_id]) ? $user_properties[$property_id] : array();
							$control_type = isset($user_property["control_type"]) ? $user_property["control_type"] : "";
							$property_values = isset($user_property["values"]) ? $user_property["values"] : array();

							// get values
							$field_values = array();
							$sql  = " SELECT up.property_value ";
							$sql .= " FROM " . $table_prefix . "users_properties up ";
							$sql .= " WHERE up.user_id=" . $dbe->tosql($user_id, INTEGER);
							$sql .= " AND up.property_id=" . $dbe->tosql($property_id, INTEGER);
							$dbs->query($sql);
							while ($dbs->next_record()) {
								$property_value = $dbs->f("property_value");
								if ($apply_translation) {
									$property_value = get_translation($property_value);
								}
								$field_values[] = $property_value;
							}

							if ($control_type == "CHECKBOXLIST" ||  $control_type == "RADIOBUTTON" || $control_type == "LISTBOX") {
								$ids = $field_values;
								$field_values = array();
								foreach ($ids as $value_id) {
									$value_desc = isset($property_values[$value_id]) ? $property_values[$value_id] : $value_id;
									$field_values[] = $value_desc;
								}
							}
							$field_value = implode("; ", $field_values);

						} else if (preg_match("/^registration_property_/", $column_name)) {
							$property_id = substr($column_name, strlen("registration_property_"));
							$registration_id = $dbe->f("registration_id");
							$sql  = " SELECT property_value FROM " . $table_prefix . "registration_properties ";
							$sql .= " WHERE registration_id=" . $dbe->tosql($registration_id, INTEGER);
							$sql .= " AND property_id=" . $dbe->tosql($property_id, INTEGER);
							$dbs->query($sql);
							$field_value_parts = array();
							while ($dbs->next_record()) {
								if ($apply_translation) {
									$field_value_parts[] = get_translation($dbs->f("property_value"));
								} else {
									$field_value_parts[] = $dbs->f("property_value");
								}
							}
							$control_type = $db_columns[$column_name]["control_type"];
							if(($control_type == "CHECKBOXLIST" ||  $control_type == "RADIOBUTTON" || $control_type == "LISTBOX")) {
								$field_value = "";
								foreach ($field_value_parts AS $field_value_part) {
									$sql  = " SELECT property_value FROM " . $table_prefix . "registration_custom_values ";
									$sql .= " WHERE property_value_id=" . $dbe->tosql($field_value_part, INTEGER);
									$dbs->query($sql);
									if ($dbs->next_record()) {
										if ($field_value) $field_value .= " / ";
										$field_value .= $dbs->f("property_value");						
									}
								}
							} else {
								$field_value = implode(" / ", $field_value_parts);
							}
						} else if (preg_match("/^oi_item_properties$/", $column_name)) {
							$order_item_id = $dbe->f("oi_order_item_id");
							$field_value = "";
							$sql  = " SELECT property_name, property_value, additional_price FROM " . $table_prefix . "orders_items_properties ";
							$sql .= " WHERE order_item_id=" . $dbe->tosql($order_item_id, INTEGER);
							$dbs->query($sql);
							while ($dbs->next_record()) {
								if ($apply_translation) {
									$property_name= get_translation($dbs->f("property_name"));
									$property_value = get_translation($dbs->f("property_value"));
								} else {
									$property_name = $dbs->f("property_name");
									$property_value = $dbs->f("property_value");
								}
								$additional_price = $dbs->f("additional_price");
								if ($field_value) { $field_value .= "\n"; }
								$field_value .= $property_name.": ".$property_value;
								if ($additional_price > 0) {
									$field_value .= " (".currency_format($additional_price).")";
								}
							}
						} else if (preg_match("/^oi_order_item_property_/", $column_name)) {
							$property_id = substr($column_name, 23);
							$order_item_id = $dbe->f("oi_order_item_id");
							$sql  = " SELECT property_value FROM " . $table_prefix . "orders_items_properties ";
							$sql .= " WHERE order_item_id=" . $order_item_id;
							$sql .= " AND (property_id=" . $dbe->tosql($property_id, INTEGER, true, false);
							$sql .= " OR property_name=" . $dbe->tosql($property_id, TEXT) . ") ";
							$dbs->query($sql);
							if ($dbs->next_record()) {
								if ($apply_translation) {
									$field_value = get_translation($dbs->f("property_value"));
								} else {
									$field_value = $dbs->f("property_value");
								}
							}
						} else if ($column_name == "manufacturer_name") {
							$manufacturer_id = $dbe->f("manufacturer_id");
							if (strlen($manufacturer_id)) {
								$sql  = " SELECT manufacturer_name FROM " . $table_prefix . "manufacturers ";
								$sql .= " WHERE manufacturer_id=" . $dbe->tosql($manufacturer_id, INTEGER);
								$dbs->query($sql);
								if ($dbs->next_record()) {
									if ($apply_translation) {
										$field_value = get_translation($dbs->f("manufacturer_name"));
									} else {
										$field_value = $dbs->f("manufacturer_name");
									}
								}
							}
						} else if ($column_name == "sites") {
							if ($table == "items") {
								$item_id = $dbe->f("item_id");
								$sql  = " SELECT s.* FROM " . $table_prefix . "items_sites its ";
								$sql .= " LEFT JOIN " . $table_prefix . "sites s ON s.site_id=its.site_id ";
								$sql .= " WHERE  its.item_id=" . $dbe->tosql($item_id, INTEGER);
								$dbs->query($sql);
								while ($dbs->next_record()) {
									$site_name = $dbs->f("short_name");
									if (!strlen($site_name)) { $site_name = $dbs->f("site_name"); }
									if (strlen($site_name)) {
										if (strlen($field_value)) { $field_value .= "; "; }
										$field_value .= $site_name;
									}
								}
							}
						} else if ($column_name == "country_name") {
							$country_id = $dbe->f("country_id");
							$field_value = get_setting_value($countries, $country_id, "");
						} else if ($column_name == "delivery_country_name") {
							$delivery_country_id = $dbe->f("delivery_country_id");
							$field_value = get_setting_value($countries, $delivery_country_id, "");
						} else if ($column_name == "state_name") {
							$state_id = $dbe->f("state_id");
							$field_value = get_setting_value($states, $state_id, "");
						} else if ($column_name == "delivery_state_name") {
							$delivery_state_id = $dbe->f("delivery_state_id");
							$field_value = get_setting_value($states, $delivery_state_id, "");
						} else if ($column_name == "cc_number" && $va_cc_data_export) {
							$field_value = $dbe->f("cc_number");
							$field_value = va_decrypt($field_value);
						} else if ($column_name == "cc_security_code" && $va_cc_data_export) {
							$field_value = $dbe->f("cc_security_code");
							$field_value = va_decrypt($field_value);
						} else if ($column_name == "cc_expiry_date") {
							$cc_expiry_date = $dbe->f("cc_expiry_date", DATETIME);
							if (is_array($cc_expiry_date)) {
								$field_value = va_date(array("MM","YY"), $cc_expiry_date);
							}
						} else if ($column_name == "cc_start_date") {
							$cc_start_date = $dbe->f("cc_start_date", DATETIME);
							if (is_array($cc_start_date)) {
								$field_value = va_date(array("MM","YY"), $cc_start_date);
							}//*/
						} else if ($column_name == "shipping_type_code") {
							$field_value = $dbe->f("shipping_type_code");
							if (!strlen($field_value)) {
								$field_value = $dbe->f("sh_shipping_code");
							}
						} else if ($column_name == "shipping_type_desc") {
							$field_value = $dbe->f("shipping_type_desc");
							if (!strlen($field_value)) {
								$field_value = $dbe->f("sh_shipping_desc");
							}
						} else if ($column_name == "shipping_tracking_id") {
							$field_value = $dbe->f("shipping_tracking_id");
							if (!strlen($field_value)) {
								$field_value = $dbe->f("sh_tracking_id");
							}
						} else if ($column_name == "shipping_cost") {
							$field_value = $dbe->f("shipping_cost");
							if (!strlen($field_value)) {
								$field_value = $dbe->f("sh_shipping_cost");
							}
						} else {
							//TODO: new format
							$column_info = isset($db_columns[$column_name]) ? $db_columns[$column_name] : "";
							$field_type = 0; $data_type = TEXT;
							if (is_array($column_info)) {
								if (isset($column_info["title"])) { // new format
									$field_type = $column_info["field_type"];
									$data_type = $column_info["data_type"];
								} else { // old format
									$field_type = $column_info[2];
									$data_type = $column_info[1];
								}
							}

							if (is_array($column_info) && $field_type == CUSTOM_FIELD) {
								$field_source = $db_columns[$column_name][4];
								$field_value  = get_field_value($field_source);
							} else if ((isset($related_columns) && isset($related_columns[$column_name]) && $related_columns[$column_name]["field_type"] == CUSTOM_FIELD)) {
								$field_source = $related_columns[$column_name]["custom_value"];
								$field_value  = get_field_value($field_source);
							} else {
								$related_column_name = "";
								// if there is no data in the default columns check data type in related fields
								if (!is_array($column_info) && isset($related_table_alias) && $related_table_alias && preg_match("/^".$related_table_alias."_/", $column_name)) {
									$related_column_name = preg_replace("/^".$related_table_alias."_/", "", $column_name);
									if (isset($related_columns[$related_column_name])) {
										$data_type = $related_columns[$related_column_name]["data_type"];
									}
								}
		  
								if ($data_type == DATE) {
									$field_value = $dbe->f($column_name, DATETIME);
									if (is_array($field_value)) {
										$field_value = va_date($date_edit_format, $field_value);
									}
								} else if ($data_type == DATETIME) {
									$field_value = $dbe->f($column_name, DATETIME);
									if (is_array($field_value)) {
										$field_value = va_date($datetime_edit_format, $field_value);
									}
								} else {
									// check if it's a common field and we can get data directly from the record set
									if (isset($db_columns[$column_name]) || ($related_column_name && isset($related_columns[$related_column_name]))) {
										$field_value = $dbe->f($column_name);
									} else {
										// otherwise it's a custom field
										$field_value = get_field_value($column_name);
									}
									if ($apply_translation) {
										$field_value = get_translation($field_value);
									}
								}
							}
						}
						if (
							(isset($selected_related_columns[$column_name]) 
							|| $column_name == "shipping_type_code" || $column_name == "shipping_type_desc" 
							|| $column_name == "shipping_tracking_id" || $column_name == "shipping_cost") 
							&& $related_number > 1) {
							$row_data[$column_name] .= $related_delimiter_symbol . $field_value;
						} else {
							$row_data[$column_name] = $field_value;
						}
					}
					$prev_id = $row_id;
				} // database rows cycle end
			} // pages cycle end

			if ($record_number > 0) {
				// last row output csv
				$csv_row = "";
				for($i = 0; $i < $columns_selected; $i++) {
					$column_name = $columns[$i];
					$field_value = $row_data[$column_name];
					if ($column_name == "oi_item_properties") {
						$field_value = preg_replace("/^\<br\>/", "", $field_value);
						$field_value = preg_replace("/\<br\>/", "; ", $field_value);
					}
					if ($comma_decimal && in_array($column_name, $prices)) {
						$field_value = str_replace('.', ',', $field_value);
					}
					if(preg_match("/[,;\"\n\r\t\s]/", $field_value)) {
						$field_value = "\"" . str_replace("\"", "\"\"", $field_value) . "\"";
					}
					if($i > 0) {
						$csv_row .= $delimiter_symbol;
					}
					$csv_row .= $field_value;
				}
				if (isset($fp) && $fp) {
					fwrite($fp, $csv_row.$eol); 
					if (isset($fp_copy) && isset($file_path_copy) && strlen($file_path_copy)) {
						fwrite($fp_copy, $csv_row.$eol); 
					}
				} else {
					echo $csv_row.$eol;
					ob_flush();flush();
				}
				// end last row output
		  
				// update exported status
				if ($table_name == $table_prefix . "orders") {

					$dbs->query("UPDATE " . $table_prefix . "orders SET is_exported=1 WHERE order_id=" . $prev_id);
					// update order status if such option selected
					if (strlen($order_status_update)) {
						update_order_status($prev_id, $order_status_update, true, "", $status_error);
					}
				}
			}

			// END output data

			if ($table == "users") {
				if ($max_id > $exported_user_id) {
					$sql  = " UPDATE " . $table_prefix . "admins SET exported_user_id=" . $dbe->tosql($max_id, INTEGER);
					$sql .= " WHERE admin_id=" . $dbe->tosql(get_session("session_admin_id"), INTEGER);
					$dbe->query($sql);
				}
			} else if ($table == "newsletters_users") {
				if ($max_id > $exported_email_id) {
					$sql  = " UPDATE " . $table_prefix . "admins SET exported_email_id=" . $dbe->tosql($max_id, INTEGER);
					$sql .= " WHERE admin_id=" . $dbe->tosql(get_session("session_admin_id"), INTEGER);
					$dbe->query($sql);
				}
			} else if ($table == "orders") {
				if ($max_id > $exported_order_id) {
					$sql = " UPDATE " . $table_prefix . "admins SET exported_order_id=" . $dbe->tosql($max_id, INTEGER);
					$sql .= " WHERE admin_id=" . $dbe->tosql(get_session("session_admin_id"), INTEGER);
					$dbe->query($sql);
				}
			}

			return;
		}
	}


	if (strlen($errors)) {
		$t->set_var("errors_list", $errors);
		$t->parse("errors", false);
	} 

	if ($template_errors) {
		$t->set_var("errors_list", $template_errors);
		$t->parse("template_errors", false);
	}

	if ($template_success) {
		$t->set_var("success_message", $template_success);
		$t->parse("template_success", false);
	}

	$t->set_var("category_id", htmlspecialchars($category_id));
	$t->set_var("newsletter_id", htmlspecialchars($newsletter_id));
	$t->set_var("id", htmlspecialchars($id));
	$t->set_var("ids", htmlspecialchars($ids));
	$t->set_var("s_on", htmlspecialchars($s_on));
	$t->set_var("s_ne", htmlspecialchars($s_ne));
	$t->set_var("s_kw", htmlspecialchars($s_kw));
	$t->set_var("s_sd", htmlspecialchars($s_sd));
	$t->set_var("s_ed", htmlspecialchars($s_ed));
	$t->set_var("a_sd", htmlspecialchars($a_sd));
	$t->set_var("a_ed", htmlspecialchars($a_ed));
	$t->set_var("s_os", htmlspecialchars($s_os));
	$t->set_var("s_ad", htmlspecialchars($s_ad));
	$t->set_var("s_ut", htmlspecialchars($s_ut));
	$t->set_var("s_ap", htmlspecialchars($s_ap));
	$t->set_var("s_ci", htmlspecialchars($s_ci));
	$t->set_var("s_si", htmlspecialchars($s_si));
	$t->set_var("s_ex", htmlspecialchars($s_ex));
	$t->set_var("s_pd", htmlspecialchars($s_pd));
	$t->set_var("s_ps", htmlspecialchars($s_ps));
	$t->set_var("s_cct", htmlspecialchars($s_cct));
	$t->set_var("s_rn", htmlspecialchars($s_rn));
	$t->set_var("s_ap", htmlspecialchars($s_ap));
	$t->set_var("s_pi", htmlspecialchars($s_pi));
	$t->set_var("s_sti", htmlspecialchars($s_sti));
	
	$t->set_var("type", htmlspecialchars($type));
	
	$t->set_var("s", htmlspecialchars($s));
	$t->set_var("sc", htmlspecialchars($sc));
	$t->set_var("sit", htmlspecialchars($sit));
	$t->set_var("sl", htmlspecialchars($sl));
	$t->set_var("sm", htmlspecialchars($sm));
	$t->set_var("spt", htmlspecialchars($spt));
	$t->set_var("ss", htmlspecialchars($ss));
	$t->set_var("ap", htmlspecialchars($ap));

	$t->set_var("rnd", va_timestamp());

	if ($table_name == ($table_prefix . "items") || $table_name == ($table_prefix . "categories")) {
		if ($table == "items") {
			$admin_export_url->add_parameter("table", CONSTANT, "items_properties_values");
			$t->set_var("admin_items_properties_values_export_url", $admin_export_url->get_url());

			$admin_export_url->add_parameter("table", CONSTANT, "items_prices");
			$t->set_var("admin_items_prices_export_url", $admin_export_url->get_url());

			$admin_export_url->add_parameter("table", CONSTANT, "items_files");
			$t->set_var("admin_items_files_import_url", $admin_export_url->get_url());

			$admin_export_url->add_parameter("table", CONSTANT, "items_serials");
			$t->set_var("admin_items_serials_export_url", $admin_export_url->get_url());

			$t->parse("products_other_links", false);
		}
	} else if ($table == "orders") {
		$admin_orders_url = new VA_URL("admin_orders.php", false);
		$admin_orders_url->add_parameter("ids", REQUEST, "ids");
		$admin_orders_url->add_parameter("page", REQUEST, "page");
		$admin_orders_url->add_parameter("s_on", REQUEST, "s_on");
		$admin_orders_url->add_parameter("s_ne", REQUEST, "s_ne");
		$admin_orders_url->add_parameter("s_kw", REQUEST, "s_kw");
		$admin_orders_url->add_parameter("s_sd", REQUEST, "s_sd");
		$admin_orders_url->add_parameter("s_ed", REQUEST, "s_ed");
		$admin_orders_url->add_parameter("a_sd", REQUEST, "a_sd");
		$admin_orders_url->add_parameter("a_ed", REQUEST, "a_ed");
		$admin_orders_url->add_parameter("s_os", REQUEST, "s_os");
		$admin_orders_url->add_parameter("s_ci", REQUEST, "s_ci");
		$admin_orders_url->add_parameter("s_si", REQUEST, "s_si");
		$admin_orders_url->add_parameter("s_ex", REQUEST, "s_ex");
		$admin_orders_url->add_parameter("s_pd", REQUEST, "s_pd");
		$admin_orders_url->add_parameter("s_ps", REQUEST, "s_ps");
		$admin_orders_url->add_parameter("s_cct", REQUEST, "s_cct");
		$admin_orders_url->add_parameter("sort_ord", REQUEST, "sort_ord");
		$admin_orders_url->add_parameter("sort_dir", REQUEST, "sort_dir");

		$t->set_var("admin_orders_url", $admin_orders_url->get_url());

		//$t->parse("orders_path", false);
	}

	$default_columns = "";
	if ($table == "users") {
		$sql  = " SELECT exported_user_fields FROM " . $table_prefix . "admins WHERE admin_id=" . $dbe->tosql(get_session("session_admin_id"), INTEGER);
		$default_columns = get_db_value($sql);
	} else if ($table == "newsletters_users") {
		$sql  = " SELECT exported_user_fields FROM " . $table_prefix . "admins WHERE admin_id=" . $dbe->tosql(get_session("session_admin_id"), INTEGER);
		$default_columns = get_db_value($sql);
		//$t->parse("newsletters_path", false);
	} else if ($table == "orders") {
		$sql  = " SELECT exported_order_fields FROM " . $table_prefix . "admins WHERE admin_id=" . $dbe->tosql(get_session("session_admin_id"), INTEGER);
		$default_columns = get_db_value($sql);
	} else if ($table == "items") {
		$sql  = " SELECT exported_item_fields FROM " . $table_prefix . "admins WHERE admin_id=" . $dbe->tosql(get_session("session_admin_id"), INTEGER);
		$default_columns = get_db_value($sql);
	} else {
		$default_columns = get_admin_settings($table."_export_fields");
	}
	$checked_columns = explode("|", $default_columns);

	// get default delimiters
	if(strpos($default_columns, "csv_delimiter")) {
		$start_delimiter = strpos($default_columns, "csv_delimiter");
		$end_delimiter = strpos($default_columns, "csv_delimiter", $start_delimiter + 13);
		$csv_delimiter = substr($default_columns, $start_delimiter + 13, $end_delimiter - $start_delimiter - 13);
	}
	if(strpos($default_columns, "related_delimiter")) {
		$start_delimiter = strpos($default_columns, "related_delimiter");
		$end_delimiter = strpos($default_columns, "related_delimiter", $start_delimiter + 17);
		$related_delimiter = substr($default_columns, $start_delimiter + 17, $end_delimiter - $start_delimiter - 17);
	}

	set_options($delimiters, $csv_delimiter, "delimiter");
	set_options($delimiters, $csv_delimiter, "delimiter_bottom");
	set_options($related_delimiters, $related_delimiter, "related_delimiter");
	set_options($related_delimiters, $related_delimiter, "related_delimiter_bottom");


	$t->set_var("table_name", $table_name);

	$template_id = get_param("template_id");
	$sql  = " SELECT template_id, template_name FROM " . $table_prefix . "export_templates ";
	$sql .= " WHERE table_name=" . $dbe->tosql($table, TEXT);
	$export_templates = get_db_values($sql, array(array("", BASIC_EXPORT_MSG)));
	set_options($export_templates, $template_id, "template_id");
	
	$total_columns = 0;
	$export_columns = array();

	if ($template_id) {
		$sql  = " SELECT field_title, field_source FROM " . $table_prefix . "export_fields " ;
		$sql .= " WHERE template_id=" . $dbe->tosql($template_id, INTEGER);
		$sql .= " ORDER BY field_order ";
		$dbe->query($sql);
		while ($dbe->next_record()) {
			$column_title = $dbe->f("field_title");
			$column_source = $dbe->f("field_source");
			$export_columns[] = array("source" => $column_source, "title" => $column_title, "checked" => "checked");
		}
	} else {

//TODO:TODO:TODO
		foreach($db_columns as $column_name => $column_info) {
			if (isset($column_info["title"])) { // new format
				$column_title = get_translation($column_info["title"]);
				$data_type = $column_info["data_type"];
				$field_type = $column_info["field_type"];
				$field_required = $column_info["required"];
				$default_value = isset($column_info["default"]) ? $column_info["default"] : "";
				$read_only = isset($column_info["read_only"]) ? $column_info["read_only"] : false;
			} else { // old format
				$column_title = get_translation($column_info[0]);
				$data_type = $column_info[1];
				$field_type = $column_info[2];
				$field_required = $column_info[3];
				$default_value = isset($column_info[4]) ? $column_info[4] : "";
				$read_only = false;
			}

			if ($field_type == RELATED_DB_FIELD) {
				if ($table == "items" && $column_name == "property_name") {
					$sql  = " SELECT property_name FROM " . $table_prefix . "items_properties ";
					$sql .= " WHERE item_type_id=0 ";
					$sql .= " GROUP BY property_name ";
					$dbe->query($sql);
					while ($dbe->next_record()) {
						$property_name = $dbe->f("property_name");
						$column_name   = "items_property_" . $property_name;
						if ($apply_translation) {
							$property_name = get_translation($property_name);
						}
						$column_title  = $property_name;
						$column_checked = in_array($column_title, $checked_columns) ? " checked " : "";
						$export_columns[] = array("source" => $column_name, "title" => $column_title, "checked" => $column_checked, "read_only" => $read_only);
					}
				} else if ($table == "items" && $column_name == "feature_name") {
					$sql  = " SELECT fg.group_id, fg.group_name, f.feature_name FROM (" . $table_prefix . "features f ";
					$sql .= " INNER JOIN " . $table_prefix . "features_groups fg ON f.group_id=fg.group_id) ";
					$sql .= " GROUP BY fg.group_id, fg.group_name, f.feature_name ";
					$dbe->query($sql);
					while ($dbe->next_record()) {
						$group_id = $dbe->f("group_id");
						$group_name = $dbe->f("group_name");
						$feature_name = $dbe->f("feature_name");
						$column_name   = "item_feature_" . $group_id . "_" . $feature_name;
						if ($apply_translation) {
							$column_title  = get_translation($group_name) . " > " . get_translation($feature_name);
						} else {
							$column_title  = $group_name . " > " . $feature_name;
						}
						$column_checked = in_array($column_title, $checked_columns) ? " checked " : "";
						$export_columns[] = array("source" => $column_name, "title" => $column_title, "checked" => $column_checked);
					}
				} else if ($table == "items" && $column_name == "category_name") {
					$column_checked = in_array($column_title, $checked_columns) ? " checked " : "";
					$export_columns[] = array("source" => "item_category", "title" => $column_title, "checked" => $column_checked, "read_only" => $read_only);
				} else if ($table == "items" && $column_name == "manufacturer_name") {
					$column_checked = in_array($column_title, $checked_columns) ? " checked " : "";
					$export_columns[] = array("source" => "manufacturer_name", "title" => $column_title, "checked" => $column_checked, "read_only" => $read_only);
				} else if ($table == "orders") {
					$column_checked = in_array($column_title, $checked_columns) ? " checked " : "";
					$export_columns[] = array("source" => $column_name, "title" => $column_title, "checked" => $column_checked, "read_only" => $read_only, "link" => $column_link);
				} else if ($column_name == "sites") {
					$column_checked = in_array($column_title, $checked_columns) ? " checked " : "";
					$export_columns[] = array("source" => "sites", "title" => $column_title, "checked" => $column_checked, "read_only" => $read_only);
				} else if ($table == "items_properties_values" && $column_name == "items_item_name") {
					$column_checked = in_array($column_title, $checked_columns) ? " checked " : "";
					$export_columns[] = array("source" => "items_item_name", "title" => $column_title, "checked" => $column_checked, "read_only" => $read_only);
				} else if ($table == "items_properties_values" && $column_name == "items_is_showing") {
					$column_checked = in_array($column_title, $checked_columns) ? " checked " : "";
					$export_columns[] = array("source" => "items_is_showing", "title" => $column_title, "checked" => $column_checked, "read_only" => $read_only);
				} else if ($table == "items_properties_values" && $column_name == "items_is_approved") {
					$column_checked = in_array($column_title, $checked_columns) ? " checked " : "";
					$export_columns[] = array("source" => "items_is_approved", "title" => $column_title, "checked" => $column_checked, "read_only" => $read_only);
				} else if ($table == "items_properties_values" && $column_name == "items_item_code") {
					$column_checked = in_array($column_title, $checked_columns) ? " checked " : "";
					$export_columns[] = array("source" => "items_item_code", "title" => $column_title, "checked" => $column_checked, "read_only" => $read_only);
				} else if ($table == "items_properties_values" && $column_name == "items_manufacturer_code") {
					$column_checked = in_array($column_title, $checked_columns) ? " checked " : "";
					$export_columns[] = array("source" => "items_manufacturer_code", "title" => $column_title, "checked" => $column_checked, "read_only" => $read_only);
				} else if ($table == "items_properties_values" && $column_name == "properties_property_name") {
					$column_checked = in_array($column_title, $checked_columns) ? " checked " : "";
					$export_columns[] = array("source" => "properties_property_name", "title" => $column_title, "checked" => $column_checked, "read_only" => $read_only);
				}
			} else if ($field_type != HIDE_DB_FIELD) {
				if ($field_type == CUSTOM_FIELD) {
					$column_source = $column_info[4];
					$column_link = $column_info[5];
				} else {
					$column_link = "";
					$column_source = $column_name;
				}
				$column_checked = in_array($column_title, $checked_columns) ? " checked " : "";
				$export_columns[] = array("source" => $column_source, "title" => $column_title, "checked" => $column_checked, "link" => $column_link, "read_only" => $read_only);
			}
		}
	}


	// if available some related data
	$total_related = 0;
	if(!$template_id && isset($related_columns)) {
		foreach ($related_columns as $column_name => $column_info) {
			$column_title = get_translation($column_info["title"]);
			$data_type = $column_info["data_type"];
			$field_type = $column_info["field_type"];

			if($field_type != HIDE_DB_FIELD && $field_type != RELATED_DB_FIELD) {
				if ($field_type == CUSTOM_FIELD) {
					$column_source = $column_info["custom_value"];
					$column_link = $column_info["edit_link"];
				} else {
					$column_source = $related_table_alias."_".$column_name;
					$column_link = "";
				}
				$column_checked = in_array($column_title, $checked_columns) ? " checked " : "";

				$export_columns[] = array("source" => $column_source, "title" => $column_title, "checked" => $column_checked, "link" => $column_link, "read_only" => false);

				$total_related++;
			}
		}
	}

	if(isset($related_columns)) {
		$t->parse("related_delimiter_block", false);
		$t->parse("related_delimiter_bottom_block", false);
	}

	foreach($export_columns as $id => $export_column) {
		$field_source = $export_column["source"];
		$column_title = $export_column["title"];
		$column_checked = $export_column["checked"];
		$column_link = isset($export_column["link"]) ? $export_column["link"] : "";
		$read_only = isset($export_column["read_only"]) ? $export_column["read_only"] : false;
		set_db_column($column_title, $field_source, $column_checked, $column_link, $read_only);
	}

	$t->set_var("total_columns", $total_columns);
	if (!strlen($template_id)) {
		$t->parse("custom_link", false);
		if(isset($related_columns)) {
			$admin_export_custom_url->remove_parameter("field");
			$admin_export_custom_url->add_parameter("table", CONSTANT, $related_table);
			$t->set_var("admin_export_custom_related_url", $admin_export_custom_url->get_url());
			$t->parse("custom_related", false);
		}
	}

	$t->pparse("main");
