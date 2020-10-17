<?php
function admin_orders_block($block_name, $params = array()) {
	global $t, $db, $table_prefix, $db_type, $datetime_show_format, $settings;

	// Admin Site URL settings
	$admin_folder     = get_admin_dir();
	$site_url         = get_setting_value($settings, "site_url", "");
	$secure_url       = get_setting_value($settings, "secure_url", "");
	$admin_site_url   = $site_url . $admin_folder;
	$admin_secure_url = $secure_url . $admin_folder;

	// orders SSL settings
	$ssl_admin_orders_list = get_setting_value($settings, "ssl_admin_orders_list", 0);
	$ssl_admin_order_details = get_setting_value($settings, "ssl_admin_order_details", 0);
	if ($ssl_admin_orders_list && strlen($secure_url)) {
		$orders_list_site_url = $admin_secure_url;
	} else {
		$orders_list_site_url = $admin_site_url;
	}
	if ($ssl_admin_order_details && strlen($secure_url)) {
		$order_details_site_url = $admin_secure_url;
	} else {
		$order_details_site_url = $admin_site_url;
	}
	
	$t->set_file("block_body", "admin_block_orders.html");
	
	$t->set_var("admin_orders_href",       $orders_list_site_url."admin_orders.php");
	$t->set_var("admin_order_href",        $order_details_site_url."admin_order.php");
	$t->set_var("admin_invoice_html_href", "admin_invoice_html.php");
	$t->set_var("admin_invoice_pdf_href",  "admin_invoice_pdf.php");
	
	$permissions = get_permissions();
	if (!get_permissions($permissions, "sales_orders", 0)) { return; }
	
	$s           = strip_tags(rtrim(trim(get_param("s"))));
	$search      = (strlen($s)) ? true : false;
	
	$t->set_var("s", $s);
	if ($s) {
		$t->parse("s_title", false);
	}
	
	// build SQL and URL
	$admin_orders_url = "admin_orders.php";
	$where    = "";
	if ($s) {
		$sa = explode(" ", $s);
		if (preg_match("/^\d+$/", $s)) {
			$admin_orders_url .= "?s_on=".urlencode($s);
			$where .= " AND o.order_id =" . $db->tosql($s, INTEGER);
		} else {
			$admin_orders_url .= "?s_ne=".urlencode($s);
			for($si = 0; $si < sizeof($sa); $si++) {
				$sa[$si] = str_replace("%","\%",$sa[$si]);
				$where .= " AND (o.email LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
				$where .= " OR o.name       LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
				$where .= " OR o.first_name LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
				$where .= " OR o.last_name  LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
				$where .= ")";
			}
		}
	}
	$t->set_var("admin_orders_url", htmlspecialchars($admin_orders_url));
	
	// select count
	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "orders o";
	$sql .= " WHERE 1=1 " . $where;
	$total_records = get_db_value($sql);
	
	if(!$total_records) return;
	$t->set_var("total_records", $total_records);
	
	// additional arrays
	$currencies = array();
	$sql  = " SELECT * ";
	$sql .= " FROM " . $table_prefix . "currencies ";
	$db->query($sql);
	while ($db->next_record()) {
		$currency = array();
		$currency["code"]      = $db->f("currency_code");
		$currency["value"]     = $db->f("currency_value");
		$currency["left"]      = $db->f("symbol_left");
		$currency["right"]     = $db->f("symbol_right");
		$currency["rate"]      = $db->f("exchange_rate");
		$currency["decimals"]  = $db->f("decimals_number");
		$currency["point"]     = $db->f("decimal_point");
		$currency["separator"] = $db->f("thousands_separator");
		$currencies[$currency["code"]] = $currency;
	}
	
	// display items 
	$sql  = " SELECT o.order_id, o.name, o.first_name, o.last_name, os.status_name, o.order_placed_date, o.order_total, o.currency_code ";
	$sql .= " FROM (" . $table_prefix . "orders o ";
	$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";
	$sql .= " WHERE 1=1 ";
	$sql .= $where;
	$sql .= " GROUP BY o.order_id, o.name, o.first_name, o.last_name";
	$sql .= " ORDER BY o.order_id ";
	$db->RecordsPerPage = isset($params['records_per_page']) ? $params['records_per_page'] : 5;
	$db->query($sql);
	$item_index = 1;
	$t->set_var("items_list", "");
	while ($db->next_record()) {
		$item_index++;
		$t->set_var("order_id",    $db->f("order_id"));		
		$user_name = $db->f("name");
		if(!strlen($user_name)) {
			$user_name = $db->f("first_name") . " " . $db->f("last_name");
		}
		$title = htmlspecialchars($user_name);
		if (is_array($sa)) {
			for ($si = 0; $si < sizeof($sa); $si++) {
				$regexp = "";
				for ($si = 0; $si < sizeof($sa); $si++) {
					if (strlen($regexp)) $regexp .= "|";
					$regexp .= htmlspecialchars(str_replace(
					array( "/", "|",  "$", "^", "?", ".", "{", "}", "[", "]", "(", ")", "*"),
					array("\/","\|","\\$","\^","\?","\.","\{","\}","\[","\]","\(","\)","\*"),$sa[$si]));
				}
				if (strlen($regexp)) {
					$title = preg_replace ("/(" . $regexp . ")/i", "<font color=\"blue\">\\1</font>", $title);
				}
			}
		}
		$t->set_var("title",  $title);	
		
		$t->set_var("status_name", $db->f("status_name"));
		$t->set_var("order_placed_date", va_date($datetime_show_format, $db->f("order_placed_date", DATETIME)));
		
		$order_total   = $db->f("order_total");
		$currency_code = $db->f("currency_code");
		if ($currency_code && isset($currencies[$currency_code])) {
			$order_total = currency_format($order_total, $currencies[$currency_code]);
		}		
		$t->set_var("order_total", $order_total);
		
		$row_style = ($item_index % 2 == 0) ? "row1" : "row2";
		$t->set_var("row_style", $row_style);
		$t->parse("items_list");
	}
	
	$t->parse("block_body", false);
	$t->parse_to("block_body", $block_name, true);
	
	return $total_records;
}
?>