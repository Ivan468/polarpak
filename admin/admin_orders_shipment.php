<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_orders_shipment.php                                ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/
                                   	

	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/shopping_cart.php");
	include_once($root_folder_path . "includes/order_items.php");
	include_once($root_folder_path . "includes/order_links.php");
	include_once($root_folder_path . "includes/parameters.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("sales_orders");

	$custom_breadcrumb = array(
		"admin_global_settings.php" => DASHBOARD_MSG,
		$orders_pages_site_url."admin_orders.php" => ORDERS_MSG,
		"admin_orders_shipment.php" => SHIPMENT_MSG,
	);

	$orders_currency = get_setting_value($settings, "orders_currency", 0);

	$permissions = get_permissions();
	$operation  = get_param("operation");
	$ids = get_param("ids");
	$orders_messages = "";

	if (strlen($operation)) {
		$shipments_number = get_param("shipments_number");
		for ($si = 1; $si <= $shipments_number; $si++) {
			$order_id = get_param("order_id_".$si);
			$order_shipping_id = get_param("order_shipping_id_".$si);
			$tracking_id = get_param("tracking_id_".$si);
			$company_id = get_param("company_id_".$si);

			$sql  = " UPDATE " . $table_prefix . "orders_shipments ";
			$sql .= " SET tracking_id=" . $db->tosql($tracking_id, TEXT);
			$sql .= " , shipping_company_id=" . $db->tosql($company_id, INTEGER);
			$sql .= " WHERE order_shipping_id=" . $db->tosql($order_shipping_id, INTEGER);
			$db->query($sql);
		}

		$orders_messages = UPDATED_MSG;
	}


	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_orders_shipment.html");
	$t->set_var("date_edit_format", join("", $date_edit_format));
	$t->set_var("ids", htmlspecialchars($ids));

	// get sites list
	$sites = array();
	$sql = "SELECT site_id, site_name FROM " . $table_prefix . "sites ORDER BY site_id ";
	$db->query($sql);
	if ($db->next_record()) {
		do {
			$rec_site_id = $db->f("site_id");
			$rec_site_name = get_translation($db->f("site_name"));
			$sites[$rec_site_id] = $rec_site_name;
		} while ($db->next_record());
	} else {
		$sites["1"] = "General";
	}

	// get order settings for all sites
	$sites_order_info = array();
	foreach ($sites as $rec_site_id => $rec_site_name) {
		$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings WHERE setting_type='order_info' ";
		$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($rec_site_id,INTEGER) . ") ";
		$sql .= " ORDER BY site_id ASC ";
		$db->query($sql);
		while ($db->next_record()) {
			$sites_order_info[$rec_site_id][$db->f("setting_name")] = $db->f("setting_value");
		}
	}

	// get countries and states
	$countries = array(); $states = array();
	$sql = "SELECT country_id, country_name FROM " . $table_prefix . "countries ORDER BY country_order, country_name ";
	$db->query($sql); 
	while ($db->next_record()) {
		$country_id = $db->f("country_id");
		$country_name = get_translation($db->f("country_name"));
		$countries[$country_id] = $country_name;
	}
	$sql = "SELECT state_id, state_name FROM " . $table_prefix . "states ORDER BY state_name ";
	$db->query($sql); 
	while ($db->next_record()) {
		$state_id = $db->f("state_id");
		$state_name = get_translation($db->f("state_name"));
		$states[$state_id] = $state_name;
	}

	// get shipping companies
	$sql = "SELECT shipping_company_id, company_name FROM " . $table_prefix . "shipping_companies ORDER BY company_name ";
	$shipping_companies = get_db_values($sql, array(array("", "")));


	$t->set_var("admin_orders_href", "admin_orders.php");
	$t->set_var("admin_order_href",  $order_details_site_url . "admin_order.php");
	$t->set_var("admin_invoice_html_href","admin_invoice_html.php");
	$t->set_var("admin_invoice_pdf_href","admin_invoice_pdf.php");
	$t->set_var("admin_href",        "admin.php");
	$t->set_var("admin_import_href", "admin_import.php");
	$t->set_var("admin_export_href", "admin_export.php");
	$t->set_var("admin_invoice_pdf_href", "admin_invoice_pdf.php");
	$t->set_var("admin_packing_pdf_href", "admin_packing_pdf.php");
	$t->set_var("admin_orders_bom_pdf_href", "admin_orders_bom_pdf.php");
	$t->set_var("admin_orders_shipment_href", "admin_orders_shipment.php");


	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// check orders
	$ids = get_param("ids");
	$orders = array();
	if ($ids) {
		$sql  = " SELECT o.order_id, o.order_placed_date, os.status_name, o.goods_total, o.order_total, o.remote_address, ";
		$sql .= " o.name, o.first_name, o.last_name, o.delivery_name, o.delivery_first_name, o.delivery_last_name, ";
		$sql .= " o.middle_name, o.delivery_middle_name, ";
		$sql .= " o.country_id, o.delivery_country_id, o.state_id, o.delivery_state_id, ";
		$sql .= " o.address1, o.delivery_address1, o.address2, o.delivery_address2, o.address3, o.delivery_address3, ";
		$sql .= " o.city, o.delivery_city, o.zip, o.delivery_zip, o.province, o.delivery_province,";
		$sql .= " o.currency_code, o.currency_rate, c.symbol_right, c.symbol_left, c.decimals_number, c.decimal_point, c.thousands_separator, ";
		$sql .= " o.site_id, sti.site_name ";
		$sql .= " FROM (((" . $table_prefix . "orders o ";
		$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "currencies c ON o.currency_code=c.currency_code) ";
		$sql .= " LEFT JOIN " . $table_prefix . "sites sti ON sti.site_id=o.site_id)";
		$sql .= " WHERE o.order_id IN(" . $db->tosql($ids, INTEGERS_LIST) . ")";
		$db->query($sql);
		if ($db->next_record()) {
  
			do
			{
				$order_id = $db->f("order_id");
				$order_site_id = $db->f("site_id");
				$orders[$order_id] = $db->Record;

				$order_info = array();
				if (isset($sites_order_info[$order_site_id])) {
					$order_info = $sites_order_info[$order_site_id];
				} else if (isset($sites_order_info["1"])) {
					$order_info = $sites_order_info["1"];
				}

				$order_total = $db->f("order_total");
				// get order currency
				$order_currency = array();
				$order_currency_code = $db->f("currency_code");
				$order_currency_rate= $db->f("currency_rate");
				$order_currency["code"] = $db->f("currency_code");
				$order_currency["rate"] = $db->f("currency_rate");
				$order_currency["left"] = $db->f("symbol_left");
				$order_currency["right"] = $db->f("symbol_right");
				$order_currency["decimals"] = $db->f("decimals_number");
				$order_currency["point"] = $db->f("decimal_point");
				$order_currency["separator"] = $db->f("thousands_separator");
		  
				if ($orders_currency != 1) {
					$order_currency["left"] = $currency["left"];
					$order_currency["right"] = $currency["right"];
					$order_currency["decimals"] = $currency["decimals"];
					$order_currency["point"] = $currency["point"];
					$order_currency["separator"] = $currency["separator"];
					if (strtolower($currency["code"]) != strtolower($order_currency_code)) {
						$order_currency["rate"] = $currency["rate"];
					}
				}
				$orders[$order_id]["order_currency"] = $order_currency;

				$show_delivery_name = get_setting_value($order_info, "show_delivery_name", 0);
				$show_delivery_first_name = get_setting_value($order_info, "show_delivery_first_name", 0);
				$show_delivery_middle_name = get_setting_value($order_info, "show_delivery_middle_name", 0);
				$show_delivery_last_name = get_setting_value($order_info, "show_delivery_last_name", 0);
				// add user name
				if ($show_delivery_name || $show_delivery_first_name || $show_delivery_middle_name || $show_delivery_last_name) {
					$user_name = $db->f("delivery_name");
					if(!strlen($user_name)) {
						$user_name = $db->f("delivery_first_name");
						if ($db->f("delivery_middle_name")) {
							$user_name .= " ".$db->f("delivery_middle_name");
						}
						if ($db->f("delivery_last_name")) {
							$user_name .= " ".$db->f("delivery_last_name");
						}
					}
				} else {
					$user_name = $db->f("name");
					if(!strlen($user_name)) {
						$user_name = $db->f("first_name");
						if ($db->f("middle_name")) { $user_name .= " ".$db->f("middle_name"); }
						if ($db->f("last_name")) { $user_name .= " ".$db->f("last_name"); }
					}
				}
				$orders[$order_id]["user_name"] = $user_name;

				// add formatted date
				$order_placed_date = $db->f("order_placed_date", DATETIME);
				$order_placed_date = va_date($datetime_show_format, $order_placed_date);
				$orders[$order_id]["order_placed_date"] = $order_placed_date;

				$ship_address = array(); 
				$ship_city_zip = array();
  			// get delivery country and state
				if (get_setting_value($order_info, "show_delivery_country_id", 0) == 1) {
					$ship_country_id = $db->f("delivery_country_id");
					$ship_state_id = $db->f("delivery_state_id");
					$delivery_address1 = $db->f("delivery_address1");
					$delivery_address2 = $db->f("delivery_address2");
					$delivery_address3 = $db->f("delivery_address3");
					if ($delivery_address1) { $ship_address[] = $delivery_address1; }
					if ($delivery_address2) { $ship_address[] = $delivery_address2; }
					if ($delivery_address3) { $ship_address[] = $delivery_address3; }

					$delivery_province = $db->f("delivery_province");
					$delivery_city = $db->f("delivery_city");
					$delivery_zip = $db->f("delivery_zip");
					if ($delivery_province) { $ship_city_zip[] = $delivery_province; }
					if ($delivery_city) { $ship_city_zip[] = $delivery_city; }
					if ($delivery_zip) { $ship_city_zip[] = $delivery_zip; }
				} elseif (get_setting_value($order_info, "show_country_id", 0) == 1) {
					$ship_country_id = $db->f("country_id");
					$ship_state_id = $db->f("state_id");

					$address1 = $db->f("address1");
					$address2 = $db->f("address2");
					$address3 = $db->f("address3");
					if ($address1) { $ship_address[] = $address1; }
					if ($address2) { $ship_address[] = $address2; }
					if ($address3) { $ship_address[] = $address3; }

					$province = $db->f("province");
					$city = $db->f("city");
					$zip = $db->f("zip");
					if ($province) { $ship_city_zip[] = $province; }
					if ($city) { $ship_city_zip[] = $city; }
					if ($zip) { $ship_city_zip[] = $zip; }
				} else {
					$ship_country_id = $settings["country_id"];
					$ship_state_id = get_setting_value($settings, "state_id", "");
				}
				$orders[$order_id]["ship_country_id"] = $ship_country_id;
				$orders[$order_id]["ship_state_id"] = $ship_state_id;
				$orders[$order_id]["ship_address"] = $ship_address;
				$orders[$order_id]["ship_city_zip"] = $ship_city_zip;

				// other data
				$status_name = $db->f("status_name");
				$remote_address = $db->f("remote_address");
				$site_name = $db->f("site_name");

			} while ($db->next_record());
		}
	}




	if (sizeof($orders) > 0)
	{
		$order_index = 0; $shipping_index = 0;
		$t->set_var("no_records", "");
		foreach ($orders as $order_id => $order_data) {
			$order_index++;

			$user_name = $order_data["user_name"];
			$order_placed_date = $order_data["order_placed_date"];
			$ship_country_id = $order_data["ship_country_id"];
			$ship_state_id = $order_data["ship_state_id"];
			$ship_address = $order_data["ship_address"];
			$ship_city_zip = $order_data["ship_city_zip"];
			$status_name = $order_data["status_name"];

			//list($order_id, $order_total, $user_name, $order_placed_date, $status_name, $country_id, $state_id, $admin_order_url, $remote_address, $order_currency, $site_name) = $orders[$i];

			$country_state = "";
			if ($ship_country_id && isset($countries[$ship_country_id])) {
				$country_state = $countries[$ship_country_id];
			}
			if ($ship_state_id && isset($states[$ship_state_id])) {
				if ($country_state) { $country_state .= ", "; }
				$country_state .= $states[$ship_state_id];
			}
			$t->set_var("country_state", $country_state);
			$t->set_var("address", implode("<br/>", $ship_address));
			$t->set_var("city_zip", implode(", ", $ship_city_zip));

			$t->set_var("order_index", $order_index);
			$t->set_var("order_id", $order_id);
			$t->set_var("user_name", htmlspecialchars($user_name));
			$t->set_var("order_placed_date", $order_placed_date);

			$t->set_var("order_status", $status_name);

			$order_total = $order_data["order_total"];
			$order_currency = $order_data["order_currency"];
			$t->set_var("order_total", currency_format($order_total, $order_currency));
			
			if ($sitelist) {
				//$t->set_var("site_name", $site_name);
				//$t->parse("site_name_block", false);
			}

			$sql  = "SELECT ip_address FROM " . $table_prefix . "black_ips WHERE ip_address=" . $db->tosql($remote_address, TEXT);
			$db->query($sql);
			if ($db->next_record()) {
				$row_style = "rowWarn";
			} else {
				$row_style = ($order_index % 2 == 0) ? "row1" : "row2";
			}
			$t->set_var("row_style", $row_style);

			// check shipping row class
			$shipping_row_class = "";
			$sql  = " SELECT sm.admin_order_class AS module_class, st.admin_order_class AS type_class ";
			$sql .= " FROM " . $table_prefix . "shipping_modules sm ";
			$sql .= " INNER JOIN " . $table_prefix . "shipping_types st ON sm.shipping_module_id=st.shipping_module_id ";
			$sql .= " INNER JOIN " . $table_prefix . "orders_shipments os ON st.shipping_type_id=os.shipping_id ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
			while($db->next_record()) {	
				$shipping_row_class .= " ".$db->f("module_class");
				$shipping_row_class .= " ".$db->f("type_class");
			}
			$t->set_var("shipping_row_class", $shipping_row_class);

			$t->set_var("order_items", "");
			$total_quantity = 0;
			$total_price = 0;
			$sql  = " SELECT item_name, quantity, price ";
			$sql .= " FROM " . $table_prefix . "orders_items ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$item_name = get_translation($db->f("item_name"));
				if (strlen($item_name) > 20) {
					$item_name = substr($item_name, 0, 20) . "...";
				}
				$quantity = $db->f("quantity");
				$price = $db->f("price");

				$total_quantity += $quantity;
				$total_price += ($price * $quantity);

				$t->set_var("item_name", $item_name);
				$t->set_var("quantity",  $quantity);
				$t->set_var("price", currency_format($price, $order_currency));
				$t->parse("order_items", true);
			}
			$t->set_var("total_quantity", $total_quantity);
			$t->set_var("total_price", currency_format($total_price, $order_currency));

			// check and set order shipments 
			$t->set_var("order_shipments", "");
			$sql  = " SELECT * FROM " . $table_prefix . "orders_shipments ";
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$shipping_index++;
				$order_shipping_id = $db->f("order_shipping_id");
				$company_id = $db->f("shipping_company_id");
				$tracking_id = $db->f("tracking_id");

				$t->set_var("shipping_index", $shipping_index);
				$t->set_var("order_id", htmlspecialchars($order_id));
				$t->set_var("order_shipping_id", htmlspecialchars($order_shipping_id));
				$t->set_var("tracking_id", htmlspecialchars($tracking_id));
				if (count($shipping_companies) > 1) {
					set_options($shipping_companies, $company_id, "company_id");		
					$t->parse("shipping_company", false);
				}
				$t->parse("order_shipments", true);
			}

			$t->parse("records", true);
		} 
		$t->set_var("orders_number", $order_index);
		$t->set_var("shipments_number", $shipping_index);
	}
	else
	{
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}


	if (isset($orders_errors) && strlen($orders_errors)) {
		$t->set_var("errors_list", $orders_errors);
		$t->parse("orders_errors", false);
	}

	if (strlen($orders_messages)) {
		$t->set_var("messages_list", $orders_messages);
		$t->parse("orders_messages", false);
	}


	$t->pparse("main");

?>