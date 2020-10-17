<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  table_view_functions.php                                 ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


function set_tv_cols($columns, $category_id)
{
	global $t;
	$category_columns = array();
	if (isset($columns[$category_id])) {
		$category_columns = $columns[$category_id]["cols"];
	} else if (isset($columns[0])) {
		$category_columns =  $columns[0]["cols"];
	}
	$t->set_var("tv_columns", sizeof($category_columns));
}

function parse_title_cols($columns, $category_id)
{
	global $t;
	$t->set_var("title_columns", "");
	$category_columns = array();
	if (isset($columns[$category_id])) {
		$category_columns = $columns[$category_id]["cols"];
	} else if (isset($columns[0])) {
		$category_columns =  $columns[0]["cols"];
	}
	$t->set_var("total_columns", sizeof($category_columns));
	foreach($category_columns as $column_id => $column_data) {
		$column_class = $column_data["class"];
		$column_title = get_translation($column_data["title"]);
		$t->set_var("column_class", $column_class);
		$t->set_var("column_title", $column_title);
		$t->parse("title_columns", true);
	}
}

function parse_data_cols($columns, $category_id, $data)
{
	global $t;
	$table_category_id = "";
	$category_columns = array();
	$column_options = array();
	$column_features = array();
	if (isset($columns[$category_id])) {
		$table_category_id = $category_id;
	} else if (isset($columns[0])) {
		$table_category_id = 0;
	}
	if (strlen($table_category_id)) {
		$category_columns =  $columns[$table_category_id]["cols"];
		if (isset($columns[$table_category_id]["options"])) {
			$column_options = $columns[$table_category_id]["options"];
		}
		if (isset($columns[$table_category_id]["features"])) {
			$column_features = $columns[$table_category_id]["features"];
		}
	}

	$pre_fields = array(
		"image" => "product_image",
		"compare" => "compare",
		"manufacturer" => "manufacturer",
		"manufacturer_code" => "manufacturer_code_block",
		"item_code" => "item_code_block",
		"item_name" => "item_name_block",
		"found_in_category" => "found_in_category",
		"short_description" => "short_description",
		"full_description" => "full_description",
		"description" => "description",
		"options" => "multi_properties",
		"single_properties" => "single_properties",
		"features_groups" => "multi_fs_groups",
		"features" => "multi_features",
		"single_features" => "single_features",
		"price" => "price_block",
		"sales_price" => "sales",
		"save" => "save",
		"stock_level" => "stock_level_block",
		"availability" => "availability",
		"quantity" => "quantity",
		"cart_add" => "cart_add_button",
		"more_button" => "more_button",

		"rating" => "reviews",
	);

	$t->set_var("column_data", "");
	foreach($category_columns as $column_id => $column_data) {
		$column_code = $column_data["code"];
		$column_codes = $column_data["codes"];
		$column_class = $column_data["class"];
		$column_html = $column_data["html"];
		// we need to clear all vars for every columns as they could be parsed many times and with different order
		foreach($pre_fields as $field_code => $field_block) {
			$t->set_var($field_block, "");
		}
		$t->set_var("cart_add_disabled", "");
		if ($column_html) {
			$t->set_block("custom_column", $column_html);
			// parse properties
			$properties = $data["properties"];
			foreach($properties as $property_id => $property) {
				$property_code = $property["code"];
				$t->set_var("property_id", $property_id);
				$t->set_var("property_block_id", $property["block_id"]);
				$t->set_var("property_name", $property["start_html"] . $property["name"]);
				$t->set_var("property_style", $property["style"]);
				$t->set_var("property_control", $property["middle_html"] . $property["control"] . $property["end_html"]);
				if($t->block_exists("option_".$property_code)) {
					$t->parse("option_".$property_code, true);	
				} else if($t->block_exists("properties")) {
					$t->parse("properties", true);	
				}
			}
			$t->parse_to("custom_column", "column_html", false);		
			// clear properties tags
			$t->set_var("properties", "");
			foreach($properties as $property_id => $property) {
				$property_code = $property["code"];
				$t->set_var("option_".$property_code, "");	
			}

		} else {
			for($ci = 0; $ci < sizeof($column_codes); $ci++) {
				$code = $column_codes[$ci];
				if ($code == "properties" || $code == "options") {
					$properties = $data["properties"];
					foreach($properties as $property_id => $property) {
						$property_code = $property["code"];
						if (!in_array($property_code, $column_options)) {
							$t->set_var("property_id", $property_id);
							$t->set_var("property_block_id", $property["block_id"]);
							$t->set_var("property_name", $property["start_html"] . $property["name"]);
							$t->set_var("property_style", $property["style"]);
							$t->set_var("property_control", $property["middle_html"] . $property["control"] . $property["end_html"]);
				    
							$t->parse_to("multi_properties", "column_html", true);
						}
					}
				} else if (preg_match("/^option_(.+)$/", $code, $matches)) {
					$column_option_code = $matches[1];
					$properties = $data["properties"];
					foreach($properties as $property_id => $property) {
						$property_code = $property["code"];
						if ($property_code == $column_option_code) {
							$t->set_var("property_id", $property_id);
							$t->set_var("property_block_id", $property["block_id"]);
							$t->set_var("property_name", $property["start_html"] . $property["name"]);
							$t->set_var("property_style", $property["style"]);
							$t->set_var("property_control", $property["middle_html"] . $property["control"] . $property["end_html"]);
				    
							$t->parse_to("single_properties", "column_html", true);
						}
					}
				} else if ($code == "features") {

					$features = $data["features"];
					$feature_number = 0;
					foreach($features as $feature_id => $feature) {
						$feature_code = $feature["code"];
						if (!in_array($feature_code , $column_features)) {
							$feature_number++;
							$group_id = $feature["group_id"];
							$group_name = get_translation($feature["group_name"]);
							$feature_name = get_translation($feature["name"]);
							$feature_value = get_translation($feature["value"]);
							if ($feature_number == 1) {
								$last_group_id = $group_id;
							}
							if ($group_id != $last_group_id) {
								$t->set_var("group_name", $last_group_name);
								$t->parse_to("multi_fs_groups", "column_html", true);
								$t->set_var("multi_features", "");
							}
            
							$t->set_var("group_name", $group_name);
							$t->set_var("feature_name", $feature_name);
							$t->set_var("feature_value", $feature_value);
							$t->sparse("multi_features", true);
            
							$last_group_id = $group_id;
							$last_group_name = $group_name;
						}
					}
					if ($feature_number > 0) {
						$t->set_var("group_name", $last_group_name);
						$t->parse_to("multi_fs_groups", "column_html", true);
					}

				} else if (preg_match("/^feature_(.+)$/", $code, $matches)) {
					$column_feature_code = $matches[1];

					$features = $data["features"];
					$feature_number = 0;
					foreach($features as $feature_id => $feature) {
						$feature_code = $feature["code"];
						if ($feature_code == $column_feature_code) {
							$feature_number++;
							$group_id = $feature["group_id"];
							$group_name = get_translation($feature["group_name"]);
							$feature_name = get_translation($feature["name"]);
							$feature_value = get_translation($feature["value"]);
            
							$t->set_var("group_name", $group_name);
							$t->set_var("feature_name", $feature_name);
							$t->set_var("feature_value", $feature_value);
							$t->sparse("single_features", true);
						}
					}
				} else if ($code == "sales_price" || $code == "save") {
					global $sales_price, $price, $is_sales;
					if ($sales_price != $price && $is_sales) {
						$t->parse_to($pre_fields[$code], "column_html", true);
					}
				} else if ($code == "image") {
					global $product_image;
					if ($product_image) {
						$t->parse_to($pre_fields[$code], "column_html", true);
					}
				} else if ($code == "compare") {
					// as $is_compared before parse block
					global $is_compared;
					if ($is_compared) {
						$t->parse_to($pre_fields[$code], "column_html", true);
					}
				} else if ($code == "found_in_category") {
					global $is_search, $is_manufacturer, $items_categories, $item_id;
					if (($is_search || $is_manufacturer) && isset($items_categories[$item_id]) && $items_categories[$item_id]) {
						$t->parse_to($pre_fields[$code], "column_html", true);
					}
				} else if ($code == "manufacturer_code") {
					global $show_manufacturer_code, $manufacturer_code;
					if ($show_manufacturer_code && $manufacturer_code) {
						$t->parse_to($pre_fields[$code], "column_html", true);
					}
				} else if ($code == "item_code") {
					global $show_item_code, $item_code;
					if ($show_item_code && $item_code) {
						$t->parse_to($pre_fields[$code], "column_html", true);
					}
				} else if ($code == "add_button") {
					global $hide_add_button, $shop_hide_add_button;
					global $use_stock_level, $stock_level, $disable_out_of_stock;
					if (!$hide_add_button && !$shop_hide_add_button) {
						if ($use_stock_level && $stock_level < 1 && $disable_out_of_stock) {
							$t->parse_to("cart_add_disabled", "column_html", true);
						} else {							if ($use_stock_level && $stock_level < 1) {								$t->set_var("ADD_TO_CART_MSG", PRE_ORDER_MSG);							} else {								$t->set_var("ADD_TO_CART_MSG", ADD_BUTTON);							}							$t->parse_to("cart_add_button", "column_html", true);
						}
					}
				} else if (isset($pre_fields[$code])) {
					$t->parse_to($pre_fields[$code], "column_html", true);
				}
			}
		}
		$t->set_var("column_class", $column_class);
		$t->parse("column_data", true);
		$t->set_var("column_html", "");
	}

}
