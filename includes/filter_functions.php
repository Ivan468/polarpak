<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  filter_functions.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


function filter_sqls(&$from_sql, &$join_sql, &$where_sql)
{
	global $db, $table_prefix, $filter_properties;

	// prepare queries for filters
	$filter = get_param("filter");
	$filters = explode("&", $filter);
	for ($f = 0; $f < sizeof($filters); $f++) {
		$filter_params = $filters[$f];
		$filter_value_id = "";
		$filter_where_sql = "";
		if (preg_match("/^fl(\d+)=(.+)$/", $filter_params, $matches)) {
			$filter_property_id = $matches[1];
			$filter_value_id = $matches[2];
		} else if (preg_match("/^fd(\d+)=(.+)$/", $filter_params, $matches)) {
			$filter_property_id = $matches[1];
			$filter_db_id = $matches[2];
			$sql  = " SELECT list_value_id,filter_where_sql ";
			$sql .= " FROM " . $table_prefix . "filters_properties_values ";
			$sql .= " WHERE value_id=" . $db->tosql($filter_db_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$filter_value_id = $db->f("list_value_id");
				$filter_where_sql = $db->f("filter_where_sql");
			}
		}
		if ($filter_value_id || $filter_where_sql) {
			$filter_from_sql = ""; $filter_join_sql = "";
			if (is_array($filter_properties) && isset($filter_properties[$filter_property_id])) {
				// data available in the filter array
				if (!$filter_where_sql) {
					$filter_where_sql = $filter_properties[$filter_property_id]["filter_where_sql"];
				}
				$filter_from_sql = $filter_properties[$filter_property_id]["filter_from_sql"];
				$filter_join_sql = $filter_properties[$filter_property_id]["filter_join_sql"];
				$property_type = $filter_properties[$filter_property_id]["property_type"];
			} else {
				// get data from database
				$sql  = " SELECT property_type, filter_from_sql, filter_join_sql, filter_where_sql  ";
				$sql .= " FROM " . $table_prefix . "filters_properties ";
				$sql .= " WHERE property_id=" . $db->tosql($filter_property_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					if (!$filter_where_sql) {
						$filter_where_sql = $db->f("filter_where_sql"); 
					}
					$filter_from_sql = $db->f("filter_from_sql");
					$filter_join_sql = $db->f("filter_join_sql");
					$property_type = $db->f("property_type");
				}
			}
			if ($property_type == "manufacturer" || $property_type == "product_type") {
				$value_type = INTEGER;
			} else {
				$value_type = TEXT;
			}
			$filter_where_sql = str_replace("{value_id}", $db->tosql($filter_value_id, $value_type, false), $filter_where_sql);
			$filter_where_sql = str_replace("{table_value}", $db->tosql($filter_value_id, $value_type, false), $filter_where_sql);

			if ($filter_where_sql) {
				// if correct data passed and where condition available
				$from_sql = $filter_from_sql . $from_sql;
				$join_sql .= $filter_join_sql;
				if (strlen($where_sql)) {
					$where_sql .= " AND";
				}
				$where_sql .= " (" . $filter_where_sql . ") ";
			}
		}
	}
}

function get_filter_sql($sql_type, $filter_from_sql, $filter_join_sql, $filter_where_sql, $list_group_field, $show_sub_products, $category_path)
{
	global $db, $table_prefix, $currency;
	global $display_products, $language_code, $site_id;

	$access_level = VIEW_CATEGORIES_ITEMS_PERM;
						
	$user_id         = get_session("session_user_id");
	$user_type_id    = get_session("session_user_type_id");
	$subscription_id = get_session("session_subscription_id");
			
	$sql = "";
	if ($sql_type == "products") {
		$category_id = get_param("category_id");
		$search_category_id = get_param("search_category_id");
		$search_string = trim(get_param("search_string"));
		$pq = get_param("pq");
		$fq = get_param("fq");
		$manf = get_param("manf");
		$user = get_param("user");
		if ($display_products != 2 || strlen($user_id)) {
			$lprice = get_param("lprice");
			$hprice = get_param("hprice");
		} else {
			$lprice = ""; $hprice = "";
		}
		$lweight = get_param("lweight");
		$hweight = get_param("hweight");
		$is_search = (strlen($search_string) || ($pq > 0) || ($fq > 0) || strlen($lprice) || strlen($hprice) || strlen($lweight) || strlen($hweight));
  	$is_manufacturer = strlen($manf);
		$is_user = strlen($user);
		if (strlen($search_category_id)) {
			$category_id = $search_category_id;
		}
		if (!strlen($category_id)) $category_id = "0";

		$price_type = get_session("session_price_type");
		if ($price_type == 1) {
			$price_field = "trade_price";
			$sales_field = "trade_sales";
			$properties_field = "trade_properties_price";
		} else {
			$price_field = "price";
			$sales_field = "sales_price";
			$properties_field = "properties_price";
		}
		$pr_where = ""; $pr_join = "";
		if ($pq > 0) {
			for ($pi = 1; $pi <= $pq; $pi++) {
				$property_name = get_param("pn_" . $pi);
				$property_value = get_param("pv_" . $pi);
				if (strlen($property_name) && strlen($property_value)) {

					if (strlen($pr_where)) $pr_where .= " AND ";
					$pr_where .= " (ip_".$pi.".property_name=" . $db->tosql($property_name, TEXT);
					$pr_where .= " AND (ip_".$pi.".property_description LIKE '%" . $db->tosql($property_value, TEXT, false) . "%' ";
					$pr_where .= " OR ipv_".$pi.".property_value LIKE '%" . $db->tosql($property_value, TEXT, false) . "%') ";
					$pr_where .= " ) OR ( ";
					$pr_where .= " itp_".$pi.".property_name=" . $db->tosql($property_name, TEXT);
					$pr_where .= " AND (itp_".$pi.".property_description LIKE '%" . $db->tosql($property_value, TEXT, false) . "%' ";
					$pr_where .= " OR ipa_".$pi.".property_description LIKE '%" . $db->tosql($property_value, TEXT, false) . "%') ";
					$pr_where .= " ) ";
					
					$pr_join  .= " LEFT JOIN " . $table_prefix . "items_properties ip_".$pi." ON i.item_id = ip_".$pi.".item_id ";
					$pr_join  .= " LEFT JOIN " . $table_prefix . "items_properties_values ipv_".$pi." ON ipv_".$pi.".property_id= ip_".$pi.".property_id ";
					$pr_join  .= " LEFT JOIN " . $table_prefix . "items_properties itp_".$pi." ON (i.item_type_id = itp_".$pi.".item_type_id AND itp_".$pi.".item_id=0) ";
					$pr_join  .= " LEFT JOIN " . $table_prefix . "items_properties_assigned ipa_".$pi." ON (ipa_".$pi.".property_id=itp_".$pi.".property_id AND i.item_id= ipa_".$pi.".item_id) ";
				}
			}
		}
		if ($fq > 0) {
			for ($fi = 1; $fi <= $fq; $fi++) {
				$feature_name = get_param("fn_" . $fi);
				$feature_value = get_param("fv_" . $fi);
				if (strlen($feature_name) && strlen($feature_value)) {
					if (strlen($pr_where)) $pr_where .= " AND ";
					$pr_where .= " f_".$fi.".feature_name=" . $db->tosql($feature_name, TEXT);
					$pr_where .= " AND f_".$fi.".feature_value LIKE '%" . $db->tosql($feature_value, TEXT, false) . "%' ";
					$pr_join  .= " LEFT JOIN " . $table_prefix . "features f_".$fi." ON i.item_id = f_".$fi.".item_id ";
				}
			}
		}
		filter_sqls($pr_brackets, $pr_join, $pr_where);
		// add join sqls for current filter if they don't already present in it
		if ($filter_join_sql && strpos($pr_join, $filter_join_sql) === false) {
			// join query corrections
			$filter_open_brackets = preg_replace("/[^\(]/", "", $filter_join_sql);
			$filter_close_brackets = preg_replace("/[^\)]/", "", $filter_join_sql);
			if (strlen($filter_close_brackets) != strlen($filter_open_brackets)) {
				$filter_join_sql = preg_replace("/[\(\)]/", "", $filter_join_sql);
			}
			$filter_from_sql = preg_replace("/[^\(]/", "", $filter_from_sql);
			$pr_join .= $filter_join_sql;
		}

		if ($db->DBType == "access") {
			$sql  = " SELECT COUNT(*) AS total ";
			if ($list_group_field) {
				$sql .= ", " . $list_group_field;
			}
			$sql .= " FROM (SELECT DISTINCT i.item_id ";
		} else {
			$sql  = " SELECT COUNT(DISTINCT i.item_id) AS total ";
		}
		if ($list_group_field) {
			$sql .= ", " . $list_group_field;
		}
		$sql .= " FROM ((";
		if (isset($site_id)) {
			$sql .= "(";
		}
		if (strlen($user_id)) {
			$sql .= "(";
		}
		if (strlen($subscription_id)) {
			$sql .= "(";
		}
		$sql .= $table_prefix . "items i ";
		$sql .= " INNER JOIN " . $table_prefix . "items_categories ic ON i.item_id=ic.item_id) ";
		if (($is_search || $is_manufacturer || $show_sub_products) && $category_id != 0)	{
			$sql .= "INNER JOIN " . $table_prefix . "categories c ON c.category_id = ic.category_id)";
		} else {
			$sql .= ")";
		}
		if (isset($site_id)) {
			$sql .= " LEFT JOIN " . $table_prefix . "items_sites AS s ON s.item_id=i.item_id)";
		}			
		if (strlen($user_id)) {
			$sql .= " LEFT JOIN " . $table_prefix . "items_user_types AS ut ON ut.item_id=i.item_id)";
		}			
		if (strlen($subscription_id)) {
			$sql .= " LEFT JOIN " . $table_prefix . "items_subscriptions AS sb ON sb.item_id=i.item_id)";
		}
		// add properties join
		$sql .= $pr_join;
		// check keywords search join
		VA_Products::keywords_sql($search_string, $kw_no_records, $kw_rank, $kw_join, $kw_where);
		$sql .= $kw_join;
		if ($kw_no_records) {
			return "";
		}

		$sql_where  = " WHERE i.is_showing=1 AND i.is_approved=1 ";
		$sql_where .= " AND ((i.hide_out_of_stock=1 AND i.stock_level > 0) OR i.hide_out_of_stock=0 OR i.hide_out_of_stock IS NULL)";
		$sql_where .= " AND (i.language_code IS NULL OR i.language_code='' OR i.language_code=" . $db->tosql($language_code, TEXT) . ")";
		
		if (isset($site_id)) {
			$sql_where .= " AND (i.sites_all=1 OR s.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";
		} else {
			$sql_where .= " AND i.sites_all=1 ";
		}
		if (strlen($user_id) && strlen($subscription_id)) {
			$sql_where .= " AND (" . format_binary_for_sql("i.access_level", $access_level);
			$sql_where .= " OR ( " . format_binary_for_sql("ut.access_level", $access_level) . "  AND ut.user_type_id=". $db->tosql($user_type_id, INTEGER, true, false) . ") ";
			$sql_where .= " OR ( " . format_binary_for_sql("sb.access_level", $access_level) . "  AND sb.subscription_id=". $db->tosql($subscription_id, INTEGER, true, false) . ") )";
		} elseif (strlen($user_id)) {
			$sql_where .= " AND (" . format_binary_for_sql("i.access_level", $access_level);
			$sql_where .= " OR ( " . format_binary_for_sql("ut.access_level", $access_level) . " AND ut.user_type_id=". $db->tosql($user_type_id, INTEGER, true, false) . ") )";
		} else {
			$sql_where .= " AND " . format_binary_for_sql("i.guest_access_level", $access_level);
		}
			
		if (($is_search || $is_manufacturer || $show_sub_products) && $category_id != 0)	{
			$sql_where .= " AND (ic.category_id = " . $db->tosql($category_id, INTEGER);
			$sql_where .= " OR c.category_path LIKE '" . $db->tosql($category_path, TEXT, false) . "%')";
		} elseif (!$is_search && !$is_manufacturer && !$is_user) {
			$sql_where .= " AND ic.category_id = " . $db->tosql($category_id, INTEGER);
		}
		if (strlen($manf)) {
			$sql_where .= " AND i.manufacturer_id= " . $db->tosql($manf, INTEGER);
		}
		if (strlen($user)) {
			$sql_where .= " AND i.user_id= " . $db->tosql($user, INTEGER);
		}
		if (strlen($lprice)) {
			$conv_price = $lprice / $currency["rate"];
			$sql_where .= " AND ( ";
			$sql_where .= " (i.is_sales=1 AND (i." . $sales_field . "+i.".$properties_field.")>=" . $db->tosql($conv_price, NUMBER) . ") ";
			$sql_where .= " OR ((i.is_sales<>1 OR i.is_sales IS NULL) AND (i." . $price_field . "+i.".$properties_field.")>= " . $db->tosql($conv_price, NUMBER) . ") ";
			$sql_where .= ") ";
		}
		if (strlen($hprice)) {
			$conv_price = $hprice / $currency["rate"];
			$sql_where .= " AND ( ";
			$sql_where .= " (i.is_sales=1 AND (i." . $sales_field . "+i.".$properties_field.")<=" . $db->tosql($conv_price, NUMBER) . ") ";
			$sql_where .= " OR ((i.is_sales<>1 OR i.is_sales IS NULL) AND (i." . $price_field . "+i.".$properties_field.")<= " . $db->tosql($conv_price, NUMBER) . ") ";
			$sql_where .= ") ";
		}
		if (strlen($lweight)) {
			$sql_where .= " AND i.weight>=" . $db->tosql($lweight, NUMBER);
		}
		if (strlen($hweight)) {
			$sql_where .= " AND i.weight<=" . $db->tosql($hweight, NUMBER);
		}
		// add keywords where
 		if ($kw_where && $sql_where) { $sql_where .= " AND ";	}
		$sql_where .= $kw_where;
		// add properties where
		if (strlen($sql_where) && strlen($pr_where)) { $sql_where .= " AND "; }
		$sql_where .= $pr_where;
		$sql .= $sql_where;
		if ($filter_where_sql) {
			$sql .= " AND (" . $filter_where_sql . ") ";
		}
		if ($list_group_field) {
			if ($db->DBType == "access") {
				$sql .= " ) GROUP BY " . $list_group_field;
			} else {
				$sql .= " GROUP BY " . $list_group_field;
			}
		} else if ($db->DBType == "access") {
			$sql .= " ) ";
		}
	}

	return $sql;
}

?>