<?php
function admin_ads_block($block_name, $params = array()) {
	global $t, $db, $table_prefix, $db_type;
	
	$t->set_file("block_body", "admin_block_ads.html");
	
	$t->set_var("admin_ads_href",            "admin_ads.php");
	$t->set_var("admin_ads_edit_href",       "admin_ads_edit.php");
	$t->set_var("admin_ads_properties_href", "admin_ads_properties.php");
	$t->set_var("admin_ads_assign_href",     "admin_ads_assign.php");
	$t->set_var("admin_ads_features_href",   "admin_ads_features.php");
	$t->set_var("admin_ads_images_href",     "admin_ads_images.php");
	
	$permissions = get_permissions();
	if (!get_permissions($permissions, "ads", 0)) return;
	
	$category_id = get_param("category_id");
	$s           = strip_tags(rtrim(trim(get_param("s"))));
	$search      = (strlen($s)) ? true : false;
	
	$t->set_var("s", htmlspecialchars($s));
	if ($s) {
		$t->parse("s_title", false);
	}
	
	$product_category_path = "";
	if (strlen($category_id)) {
		$product_category_name = "<b>Top</b> category";
		if ($category_id) {
			$sql  = " SELECT category_name, category_path FROM " . $table_prefix . "ads_categories";
			$sql .= " WHERE category_id=" . $db->tosql("category_id", INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$product_category_name = "<b>" . get_translation($db->f("category_name")) . "</b> category";
				$product_category_path = $db->f("category_path");
			} else {
				$category_id = 0;
			}
		}
	} else {
		$product_category_name = "<b>All</b> categories";
	}
	$t->set_var("product_category_name", $product_category_name);
	
	// build sqls
	$where = "";
	$join  = "";
	$brackets = "";
	if ($search && $product_category_path) {
		$brackets .= "((";
		$join  .= " LEFT JOIN " . $table_prefix . "ads_assigned ic ON i.item_id=ic.item_id) ";
		$join  .= " LEFT JOIN " . $table_prefix . "ads_categories c ON c.category_id = ic.category_id) ";
		
		$where .= " AND (ic.category_id = " . $db->tosql($category_id, INTEGER);
		$where .= " OR c.category_path LIKE '" . $db->tosql($product_category_path, TEXT, false) . "%')";
	} else {
		$brackets .= "(";
		$join  .= " LEFT JOIN " . $table_prefix . "ads_assigned ic ON i.item_id=ic.item_id) ";
		if (strlen($category_id)) {
			$where .= " AND ic.category_id = " . $db->tosql($category_id, INTEGER);
		}
	}
	if ($s) {
		$sa = explode(" ", $s);
		for($si = 0; $si < sizeof($sa); $si++) {
			$sa[$si] = str_replace("%","\%",$sa[$si]);
			$where .= " AND (i.item_title LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
			if (sizeof($sa) == 1 && preg_match("/^\d+$/", $sa[0])) {
				$where .= " OR i.item_id =" . $db->tosql($sa[0], INTEGER);
			}
			$where .= ")";
		}
	}
	
	$total_records = 0;
	if (strtolower($db_type) == "mysql" || !strlen($join)) {
		$sql  = " SELECT COUNT(DISTINCT i.item_id) ";
	} else {
		$sql  = " SELECT COUNT(*) ";
	}
	$sql .= " FROM " . $brackets . $table_prefix . "ads_items i " . $join;
	$sql .= " WHERE 1=1 ";
	$sql .= $where;
	$total_records = 0;
	if (strtolower($db_type) == "mysql" || !strlen($join)) {
		$db->query($sql);
		$db->next_record();
		$total_records = $db->f(0);
	} else {
		$sql .= " GROUP BY i.item_id";
		$db->query($sql);
		while ($db->next_record()) {
			$total_records++;
		}
	}
	
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
	$sql  = " SELECT i.item_id, i.item_title, i.price, i.quantity, i.is_approved, i.is_paid, i.date_start, i.date_end, ic.category_id, i.currency_code ";
	$sql .= " FROM " . $brackets . $table_prefix . "ads_items i " . $join;
	$sql .= " WHERE 1=1 ";
	$sql .= $where;
	$sql .= " GROUP BY i.item_id, i.item_title, i.price, i.quantity, i.is_approved, i.is_paid, i.date_start, i.date_end, ic.category_id, i.currency_code ";
	$sql .= " ORDER BY i.item_id ";
	$db->RecordsPerPage = isset($params['records_per_page']) ? $params['records_per_page'] : 5;
	$db->query($sql);
	
	$item_index = 1;
	$t->set_var("items_list", "");
	while ($db->next_record()) {
		$item_index++;
		$item_id = $db->f("item_id");
		$product_category_id = $db->f("category_id");
		
		$item_code         = $db->f("item_code");
		$manufacturer_code = $db->f("manufacturer_code");
		$item_name         = get_translation($db->f("item_title"));
		
		$price         = $db->f("price");		
		$currency_code = $db->f("currency_code");
		if ($currency_code && isset($currencies[$currency_code])) {
			$price = currency_format($price, $currencies[$currency_code]);
		}
		
		$stock_level       = $db->f("quantity");
			
		$t->set_var("item_id",        $item_id);
		$t->set_var("item_index",     $item_index);
		$t->set_var("ad_category_id", $product_category_id);
		
		$item_name = htmlspecialchars($item_name);
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
					$item_name = preg_replace ("/(" . $regexp . ")/i", "<font color=\"blue\">\\1</font>", $item_name);
				}
			}
		}
		$t->set_var("item_name", $item_name);
		$t->set_var("price", currency_format($price));
		if ($stock_level < 0) {
			$stock_level = "<font color=red>" . $stock_level . "</font>";
		}
		$t->set_var("stock_level", $stock_level);

		$is_approved = $db->f("is_approved");
		$is_paid = $db->f("is_paid");
		$date_start = $db->f("date_start", DATETIME);
		$date_end = $db->f("date_end", DATETIME);
		$date_start_ts = mktime(0,0,0, $date_start[MONTH], $date_start[DAY], $date_start[YEAR]);
		$date_end_ts = mktime(0,0,0, $date_end[MONTH], $date_end[DAY], $date_end[YEAR]);
		$date_now_ts = va_timestamp();
		if ($is_paid != 1) {
			$status = "<font color=red>".NOT_PAID_MSG."</font>";
		} else if ($is_approved != 1) {
			$status = "<font color=red>".NOT_APPROVED_MSG."</font>";
		} elseif ($date_now_ts >= $date_start_ts && $date_now_ts < $date_end_ts) {
			$status = "<font color=blue>".AD_RUNNING_MSG."</font>";
		} elseif ($date_start_ts == $date_end_ts) {
			$status = "<font color=silver>".AD_CLOSED_MSG."</font>";
		} elseif ($date_now_ts >= $date_end_ts) {
			$status = "<font color=silver>".EXPIRED_MSG."</font>";
		}	elseif ($date_now_ts < $date_start_ts) {
			$status = AD_NOT_STARTED_MSG;
		}
		$t->set_var("status", $status);
			
		$row_style = ($item_index % 2 == 0) ? "row1" : "row2";
		$t->set_var("row_style", $row_style);
		$t->parse("items_list");
	}
	
	$t->parse("block_body", false);
	$t->parse_to("block_body", $block_name, true);
	
	return $total_records;
}
?>