<?php

	include_once("./includes/filter_functions.php");
	$default_title = "";

//function filter_block($block_name, $filter_id, $page_friendly_url, $page_friendly_params, $show_sub_products, $category_path)
	$filter_id = $vars["block_key"];

	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	if (!isset($show_sub_products)) { $show_sub_products = 0; }
	if (!isset($category_path)) { $category_path = ""; }

	$sql  = " SELECT filter_type FROM " . $table_prefix . "filters ";
	$sql .= " WHERE filter_id=" . $db->tosql($filter_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$filter_type = $db->f("filter_type");
	} else {
		return;
	}

	$html_template = get_setting_value($block, "html_template", "block_filter.html"); 
  $t->set_file("block_body", $html_template);

	$filter_properties = array();
	$sql  = " SELECT * FROM " . $table_prefix . "filters_properties ";
	$sql .= " WHERE filter_id=" . $db->tosql($filter_id, INTEGER);
	$sql .= " ORDER BY property_order ";
	$db->query($sql);
	while ($db->next_record()) {
		$pi = $db->f("property_id");
		$property_id = $db->f("property_id");
		$property_name = $db->f("property_name");
		$property_value = $db->f("property_value");
		$property_type = $db->f("property_type");
		$filter_from_sql = $db->f("filter_from_sql");
		$filter_join_sql = $db->f("filter_join_sql");
		$filter_where_sql = $db->f("filter_where_sql");
		$list_table = $db->f("list_table");
		$list_field_id = $db->f("list_field_id");
		$list_field_title = $db->f("list_field_title");
		$list_field_image = $db->f("list_field_image");

		$list_field_total = $db->f("list_field_total");
		$list_sql = $db->f("list_sql");
		$list_group_fields = $db->f("list_group_fields");
		$list_group_where = $db->f("list_group_where");
		if ($property_type == "manufacturer") {
			$list_group_fields = "i.manufacturer_id";
		} else if ($property_type == "property_type") {
			$list_group_fields = "i.item_type_id";
		} else if ($property_type == "product_option") {
			$list_group_fields = "fip_$property_id.property_description, fipv_$property_id.property_value";
			$list_group_where  = " fip_".$property_id.".property_name=" . $db->tosql($property_value, TEXT);
			$list_group_where .= " AND (fipv_".$property_id.".hide_out_of_stock=0 OR fipv_".$property_id.".hide_out_of_stock IS NULL OR fipv_".$property_id.".stock_level>0) ";
		} else if ($property_type == "product_specification") {
			$list_group_fields = "ff_$property_id.feature_value";
			$list_group_where = " ff_".$property_id.".feature_name=" . $db->tosql($property_value, TEXT);
		}

		$filter_property = array(
			"property_name" => $property_name,
			"property_type" => $property_type,
			"property_value" => $property_value,
			"filter_from_sql" => $filter_from_sql,
			"filter_join_sql" => $filter_join_sql,
			"filter_where_sql" => $filter_where_sql,
			"list_group_fields" => $list_group_fields,
			"list_group_where" => $list_group_where,
			"list_table" => $list_table,
			"list_field_id" => $list_field_id,
			"list_field_title" => $list_field_title,
			"list_field_image" => $list_field_image,
			"list_field_total" => $list_field_total,
			"list_sql" => $list_sql,
		);
		$filter_properties[$property_id] = $filter_property;
	}

	// check values for filter properties
	foreach($filter_properties as $property_id => $filter_property) {
		$filter_values = array();
		$list_sql = $filter_property["list_sql"];
		$list_table = $filter_property["list_table"];
		$list_field_id = $filter_property["list_field_id"];
		$list_field_title = $filter_property["list_field_title"];
		$list_field_image = $filter_property["list_field_image"];
		$list_field_total = $filter_property["list_field_total"];
		// check predefined values
		$predefined_total = 0;
		$sql  = " SELECT value_id,list_value_id,list_value_title,list_image,filter_where_sql ";
		$sql .= " FROM " . $table_prefix . "filters_properties_values ";
		$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
		$sql .= " ORDER BY value_order ";
		$db->query($sql);
		while($db->next_record()) {
			$predefined_total++;
			$value_id = $db->f("value_id");
			$list_id = $db->f("list_value_id");
			$value_title = $db->f("list_value_title");
			$value_image = $db->f("list_image");
			$where = $db->f("filter_where_sql");
			$value_key = "fd" . $value_id;
			$filter_values[$value_key] = array(
				"value_id" => $value_id, "list_id" => $list_id, "title" => $value_title, "image" => $value_image, "total" => "", "where" => $where);
		}

		// check data from SQL queries if there is no predefined values
		if (sizeof($filter_values) == 0) {
			if ($list_sql) {
				$db->query($list_sql);
				while($db->next_record()) {
					$list_id = $db->f($list_field_id);
					if ($list_field_total) {
						$value_title = $db->f($list_field_title);
					} else {
						$value_title = $list_id;
					}
					$value_image = "";
					if ($list_field_image) {
						$value_image = $db->f($list_field_image);
					}
					$value_total = "";
					if ($list_field_total) {
						$value_total = $db->f($list_field_total);
					}
					$filter_values["fl" . $list_id] = array(
						"value_id" => "", "list_id" => $list_id, "title" => $value_title, "image" => $value_image, "total" => $value_total, "where" => "");
				}
			} else if ($list_table) {
				$sql = " SELECT " . $list_field_id;	
				if ($list_field_title) { $sql .= "," . $list_field_title;	}
				if ($list_field_image) { $sql .= "," . $list_field_image;	}
				if ($list_field_total) { $sql .= "," . $list_field_total;	}
				$sql .= " FROM " . $list_table;	
				$db->query($sql);
				while($db->next_record()) {
					$list_id = $db->f($list_field_id);
					if ($list_field_title) {
						$value_title = $db->f($list_field_title);
					} else {
						$value_title = $list_id;
					}
					$value_image = "";
					if ($list_field_image) {
						$value_image = $db->f($list_field_image);
					}
					$value_total = "";
					if ($list_field_total) {
						$value_total = $db->f($list_field_total);
					}
					$filter_values["fl" . $list_id] = array(
						"value_id" => "", "list_id" => $list_id, "title" => $value_title, "image" => $value_image, "total" => $value_total, "where" => "");
				}
			}
		}

		// calculate total records for filter values
		$filter_from_sql = $filter_property["filter_from_sql"];
		$filter_join_sql = $filter_property["filter_join_sql"];
		$filter_where_sql = $filter_property["filter_where_sql"];
		$list_group_fields = $filter_property["list_group_fields"];
		$list_group_where = $filter_property["list_group_where"];
		// if there are no any predefined values and group condition exists we check total values
		if (!$predefined_total && $list_group_fields) {
			$values_total = array();
			$group_fields = explode(",", $list_group_fields);
			for ($f = 0; $f < sizeof($group_fields); $f++) {
				$list_group_field = trim($group_fields[$f]);
				$list_field_alias = preg_replace("/^[\w\d_]+\./i", "", $list_group_field);
				$sql = get_filter_sql($filter_type, $filter_from_sql, $filter_join_sql, $list_group_where, $list_group_field, $show_sub_products, $category_path);
				if ($sql) {
					$db->query($sql);
					while ($db->next_record()) {
						$value_id = $db->f($list_field_alias);
						$id = "fl".$value_id;
						if (!isset($filter_values[$id])) {
							// if there is no this value in the list when there is no table name for example we need add it
							$filter_values[$id] = array(
								"value_id" => "", "list_id" => $value_id, "title" => $value_id, "image" => "", "total" => 0, "where" => "");	
						}
						$value_total = $db->f("total");
						if ($value_id && $value_total) {
							$values_ids["fl".$value_id] = $value_id;
							if (isset($values_total["fl".$value_id])) {
								$values_total["fl".$value_id] += $value_total;
							} else {
								$values_total["fl".$value_id] = $value_total;
							}
						}
					}
				}
			}

			foreach ($filter_values as $id => $filter_value) {
				if (isset($values_total[$id])) {
					$total = $values_total[$id];
				} else {
					$total = 0;
				}
				if ($total) {
					$filter_values[$id]["total"] = $total;
				} else {
					unset($filter_values[$id]);
				}
			}
		} else {
			foreach ($filter_values as $id => $filter_value) {
				$total = $filter_value["total"];
				if (!strlen($total)) {
					$list_id = $filter_value["list_id"];
					$where = $filter_value["where"];
					if (!$where) {
						$where = $filter_where_sql;
					}
					$where = str_replace("{value_id}", $list_id, $where);
					$where = str_replace("{table_value}", $list_id, $where);
					$sql  = get_filter_sql($filter_type, $filter_from_sql, $filter_join_sql, $where, "", $show_sub_products, $category_path);
					if ($sql && $where) {
						$total = get_db_value($sql);
					}
				}
				if ($total) {
					$filter_values[$id]["total"] = $total;
				} else {
					unset($filter_values[$id]);
				}
			}
		}

    $filter_properties[$property_id]["values"] = $filter_values;
	}

	if ($friendly_urls && $page_friendly_url) {
		$remove_params = $page_friendly_params;
		$remove_params[] = "filter";
		$current_page = $page_friendly_url . $friendly_extension;
		$transfer_query = transfer_params($remove_params);
	} else {
		$transfer_query = transfer_params(array("filter"));
	}
	if (strlen($transfer_query)) {
		$filter_query = "&filter=";
	} else {
		$filter_query = "?filter=";
	}
	$filter_url = $current_page . $transfer_query;

	// check selected filters
	$filter = get_param("filter");
	$filters = explode("&", $filter);
	for ($f = 0; $f < sizeof($filters); $f++) {
		$filter_params = $filters[$f];
		$filter_value_id = "";
		if (preg_match("/^fl(\d+)=(.+)$/", $filter_params, $matches)) {
			$filter_property_id = $matches[1];
			$filter_list_id = $matches[2];
			if (isset($filter_properties[$filter_property_id]["values"]["fl" . $filter_list_id])) {
				$filter_properties[$filter_property_id]["selected"] = "fl" . $filter_list_id;
				$filter_properties[$filter_property_id]["values"]["fl" . $filter_list_id]["selected"] = true;
			}
		} else if (preg_match("/^fd(\d+)=(.+)$/", $filter_params, $matches)) {
			$filter_property_id = $matches[1];
			$filter_db_id = $matches[2];
			if (isset($filter_properties[$filter_property_id]["values"]["fd" . $filter_db_id])) {
				$filter_properties[$filter_property_id]["selected"] = "fd" . $filter_db_id;
				$filter_properties[$filter_property_id]["values"]["fd" . $filter_db_id]["selected"] = true;
			}
		}
	}

	// parse filters
	$t->set_var("filter_properties_cols", "");
	$t->set_var("filter_properties_rows", "");
	$properties_number = 0;
	$filter_values_limit = get_setting_value($vars, "filter_values_limit", 10);

	foreach($filter_properties as $property_id => $filter_property) {
		$t->set_var("property_id", $property_id);
		$t->set_var("filter_values", "");
		$t->set_var("filter_more_values", "");
		$t->set_var("filter_more_link", "");
		$t->set_var("filter_selected", "");
		// check if property has any values
		if (is_array($filter_property["values"]) && sizeof($filter_property["values"])) {
			$properties_number++;
			$values_number = 0;
			$t->set_var("property_name", htmlspecialchars(get_translation($filter_property["property_name"])));
			$filter_selected = isset($filter_property["selected"]) ? $filter_property["selected"] : "";
			if ($filter_selected) {
	  
				$filter_value = $filter_property["values"][$filter_selected];
				if ($filter_value["value_id"]) {
					$remove_param = "&fd" . $property_id . "=" . $filter_value["value_id"];
				} else {
					$remove_param = "&fl" . $property_id . "=" . $filter_value["list_id"];
				}
				$filter_removed = str_replace($remove_param, "", $filter);
				if ($filter_removed) {
					$value_url = $filter_url . $filter_query . urlencode($filter_removed);
				} else {
					$value_url = $filter_url;
				}
				$t->set_var("value_title", htmlspecialchars(get_translation($filter_value["title"])));
				$t->set_var("filter_url", htmlspecialchars($value_url));
				$filter_image = $filter_value["image"];
				if ($filter_image) {
					$t->set_var("src", htmlspecialchars($filter_image));
					$t->sparse("filter_image", false);
				} else {
					$t->set_var("filter_image", "");
				}
	  
				$t->parse("filter_selected", true);
			} else {
        $filter_values = $filter_property["values"];
				$filter_values_total = sizeof($filter_values);
				if ($filter_values_total == ($filter_values_limit + 1)) {
					$current_values_limit = $filter_values_limit + 1;
				} else {
					$current_values_limit = $filter_values_limit;
				}
				foreach ($filter_values as $id => $filter_value) {
					$values_number++;
					$value_url = $filter_url.$filter_query.urlencode($filter);
					if ($filter_value["value_id"]) {
						$value_url .= urlencode("&fd" . $property_id . "=" . $filter_value["value_id"]);
					} else {
						$value_url .= urlencode("&fl" . $property_id . "=" . $filter_value["list_id"]);
					}
					$t->set_var("value_title", htmlspecialchars(get_translation($filter_value["title"])));
					$t->set_var("value_total", $filter_value["total"]);
					$t->set_var("filter_url", htmlspecialchars($value_url));
					$filter_image = $filter_value["image"];
					if ($filter_image) {
						$t->set_var("src", htmlspecialchars($filter_image));
						$t->sparse("filter_image", false);
					} else {
						$t->set_var("filter_image", "");
					}
	    
					if ($values_number > $current_values_limit) {
						$t->parse("filter_more_values", true);
					} else {
						$t->parse("filter_values", true);
					}
				}
				if ($values_number > $current_values_limit) {
					$t->parse("filter_more_link", false);
				}
			}
			$t->sparse("filter_properties", true);
			$t->sparse("filter_properties_cols", true);
		}
	}
	$t->sparse("filter_properties_rows", false);
	
	if ($properties_number) {
		// parse block if any filters properties is available
		if(!$layout_type) { $layout_type = "aa"; }
		$block_parsed = true;
	}


?>