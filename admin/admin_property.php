<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_property.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/editgrid.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");
	include_once($root_folder_path."messages/".$language_code."/download_messages.php");
	include_once("./admin_common.php");

	check_admin_security("products_categories");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_property.html");

	$t->set_var("admin_href", $admin_site_url . "admin.php");
	$t->set_var("admin_property_href", "admin_property.php");
	$t->set_var("admin_items_list_href", "admin_items_list.php");
	$t->set_var("admin_item_types_href", "admin_item_types.php");
	$t->set_var("admin_item_type_href", "admin_item_type.php");
	
	$t->set_var("admin_product_href", "admin_product.php");
	$t->set_var("admin_properties_href", "admin_properties.php");
	$t->set_var("admin_files_select_href", "admin_files_select.php");
	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");


	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", OPTION_MSG, CONFIRM_DELETE_MSG));
	$t->set_var("HIDE_OPTION_VALUE_JS", str_replace("'", "\\'", HIDE_OPTION_VALUE_DESC));
	$t->set_var("ACTIVATE_CONTROL_CHECKBOX_JS", str_replace("'", "\\'", ACTIVATE_CONTROL_CHECKBOX_MSG));
	$t->set_var("CHECK_STOCK_USE_JS", str_replace("'", "\\'", CHECK_STOCK_USE_MSG));
	$t->set_var("OPTION_PERCENTAGE_PRICE_JS", str_replace("'", "\\'", OPTION_PERCENTAGE_PRICE_DESC));
	
	$item_id = get_param("item_id");
	if(!strlen($item_id)) $item_id= "0";
	$item_type_id = get_param("item_type_id");
	if(!strlen($item_type_id)) $item_type_id = "0";
	$category_id = get_param("category_id");
	if(!strlen($category_id)) $category_id = "0";
	$property_id = get_param("property_id");
	$sizes_file_delimiter = get_param("sizes_file_delimiter");
	if (!$sizes_file_delimiter) { $sizes_file_delimiter = ","; }

	$t->set_var("item_id", htmlspecialchars($item_id));
	$t->set_var("item_type_id", htmlspecialchars($item_type_id));
	$t->set_var("sizes_file_delimiter", htmlspecialchars($sizes_file_delimiter));


	$parent_properties = array();
	$downloadable_files = 0;
	if ($item_type_id > 0) {
		$sql  = " SELECT item_type_name FROM " . $table_prefix . "item_types ";
		$sql .= " WHERE item_type_id=" . $db->tosql($item_type_id, INTEGER);
		$db->query($sql);
		if($db->next_record()) {
			$t->set_var("item_type_name", get_translation($db->f("item_type_name")));

			// get parent options
			$sql  = " SELECT property_id, property_name FROM " . $table_prefix . "items_properties ";
			$sql .= " WHERE item_type_id=" . $db->tosql($item_type_id, INTEGER);
			$sql .= " AND (property_type_id=1 OR property_type_id=3) ";
			if ($property_id) {
				$sql .= " AND property_id<>" . $db->tosql($property_id, INTEGER);
			}
			$sql .= " ORDER BY property_order ";
			$parent_properties = get_db_values($sql, array(array("", "")));
		} else {
			die(str_replace("{item_type_id}", $item_id, PROD_TYPE_ID_NO_LONGER_EXISTS_MSG));
		}

		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "items_files ";
		$sql .= " WHERE item_type_id=" . $db->tosql($item_type_id, INTEGER);
		$downloadable_files = get_db_value($sql);
	} else {
		$sql  = " SELECT item_type_id, item_name FROM " . $table_prefix . "items ";
		$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$db_type_id = $db->f("item_type_id");
			$t->set_var("item_name", get_translation($db->f("item_name")));

			// get parent options
			$sql  = " SELECT property_id, property_name FROM " . $table_prefix . "items_properties ";
			$sql .= " WHERE (item_id=" . $db->tosql($item_id, INTEGER);
			$sql .= " OR item_type_id=" . $db->tosql($db_type_id, INTEGER) . ") ";
			$sql .= " AND (property_type_id=1 OR property_type_id=3) ";
			if ($property_id) {
				$sql .= " AND property_id<>" . $db->tosql($property_id, INTEGER);
			}
			$sql .= " ORDER BY property_order ";
			$parent_properties = get_db_values($sql, array(array("", "")));
		} else {
			die(str_replace("{item_id}", $item_id, PRODUCT_ID_NO_LONGER_EXISTS_MSG));
		}

		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "items_files ";
		$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
		$downloadable_files = get_db_value($sql);
	}

	$controls = 
		array(			
			array("", ""),  
			array("CHECKBOXLIST", CHECKBOXLIST_MSG),
			array("LABEL",        LABEL_MSG),
			array("LISTBOX",      LISTBOX_MSG),
			array("RADIOBUTTON",  RADIOBUTTON_MSG),
			array("TEXTAREA",     TEXTAREA_MSG),
			array("TEXTBOX",      TEXTBOX_MSG),
			array("TEXTBOXLIST",  TEXTBOXLIST_MSG),
			array("IMAGEUPLOAD",  IMAGEUPLOAD_MSG),
			array("WIDTH_HEIGHT", WIDTH_AND_HEIGHT_MSG),
			array("IMAGE_SELECT", IMAGE_SELECT_MSG),
			);

	$usage_types = 
		array(			
			array("1", AUTO_ADD_TO_ALL_PRODS_MSG),
			array("2", SELECT_OPTION_AND_VALUES_MSG),
			array("3", SELECT_OPTION_ALL_VALUES_MSG),
		);

	$prices_types = 
		array(			
			array("0", NONE_MSG),
			array("1", SINGLE_TOTAL_PRICE_MSG),
			array("2", FOR_SPECIFIED_CONTOL_MSG),
			array("3", FOR_SPECIFIED_LETTER_MSG),
			array("4", FOR_SPECIFIED_NONSPACE_MSG),
		);

	$free_prices_types = 
		array(			
			array("0", NONE_MSG),
			array("1", SUMMARY_DISCOUNT_MSG),
			array("2", FREE_CHARGE_CONTROLS_MSG),
			array("3", FREE_CHARGE_LETTERS_MSG),
			array("4", FREE_CHARGE_NONSPACE_MSG),
		);

	$limit_types = 
		array(			
			array("0", NONE_MSG),
			array("1", MAX_LETTERS_ALL_CONTROLS_MSG),
			array("2", MAX_LETTERS_EACH_CONTROL_MSG),
			array("3", MAX_NONSPACE_ALL_CONTROLS_MSG),
			array("4", MAX_NONSPACE_EACH_CONTROL_MSG),
		);

	$length_types = 
		array(			
			array("mm", "MM"),
			array("cm", "CM"),
			array("in", "INCHES"),
		);

	$percentage_types = 
		array(			
			array("0", ""),
			array("1", FROM_PRODUCT_PRICE_MSG),
			array("2", FROM_OTHER_OPTION_MSG),
			array("3", FROM_PRODUCT_PRICE_MSG." + ".FROM_OTHER_OPTION_MSG),
		);


	// set up html form parameters
	$r = new VA_Record($table_prefix . "items_properties");
	$r->add_where("property_id", INTEGER);
	$r->add_textbox("property_type_id", INTEGER);
	$r->change_property("property_type_id", USE_IN_UPDATE, false);
	$r->add_hidden("item_id", INTEGER);
	$r->change_property("item_id", USE_IN_INSERT, true);
	$r->add_hidden("item_type_id", INTEGER);
	$r->change_property("item_type_id", USE_IN_INSERT, true);
	$r->add_checkbox("show_for_user", INTEGER);
	$r->change_property("show_for_user", DEFAULT_VALUE, 1);
	$r->add_textbox("property_order", INTEGER, OPTION_ORDER_MSG);
	$r->parameters["property_order"][REQUIRED] = true;
	$r->add_textbox("property_code", TEXT, CODE_MSG);
	$r->add_textbox("property_name", TEXT, OPTION_NAME_MSG);
	$r->parameters["property_name"][REQUIRED] = true;
	$r->add_textbox("property_hint", TEXT, OPTION_HINT_MSG);
	$r->add_checkbox("hide_name", INTEGER);
	$r->add_select("usage_type", INTEGER, $usage_types, ASSIGN_OPTION_MSG);
	$r->parameters["usage_type"][REQUIRED] = true;
	$r->add_select("control_type", TEXT, $controls, OPTION_CONTROL_MSG);
	$r->parameters["control_type"][REQUIRED] = true;
	$r->add_select("parent_property_id", INTEGER, $parent_properties, PARENT_OPTION_MSG);
	$r->add_select("parent_value_id", INTEGER, "", PARENT_OPTION_VALUE_MSG);

	$r->add_select("property_price_type", INTEGER, $prices_types, PRICE_TYPE_MSG);
	$r->add_textbox("additional_price", NUMBER, PRICE_MSG);
	$r->add_textbox("trade_additional_price", NUMBER, PROD_TRADE_PRICE_MSG);

	$r->add_select("percentage_price_type", INTEGER, $percentage_types);
	$r->add_select("percentage_property_id", INTEGER, $parent_properties, PERCENTAGE_PRICE_TYPE_MSG." (".OPTION_MSG.")");

	$r->add_select("free_price_type", INTEGER, $free_prices_types, DISCOUNT_PRICE_TYPE_MSG);
	$r->add_textbox("free_price_amount", NUMBER, DISCOUNT_AMOUNT_MSG);

	$r->add_select("max_limit_type", INTEGER, $limit_types, MAX_LIMIT_TYPE_MSG);
	$r->add_textbox("max_limit_length", INTEGER, MAX_LIMIT_LENGTH_MSG);

	$r->add_textbox("property_description", TEXT, OPTION_TEXT_MSG);
	$r->add_textbox("property_class", TEXT);
	$r->add_textbox("property_style", TEXT);
	$r->add_textbox("control_style", TEXT);
	$r->add_checkbox("use_on_list", INTEGER);
	$r->add_checkbox("use_on_details", INTEGER);
	$r->add_checkbox("use_on_table", INTEGER);
	$r->add_checkbox("use_on_grid", INTEGER);
	$r->add_checkbox("use_on_second", INTEGER);
	$r->add_checkbox("required", INTEGER);
	$r->add_textbox("start_html", TEXT);
	$r->add_textbox("middle_html", TEXT);
	$r->add_textbox("before_control_html", TEXT);
	$r->add_textbox("after_control_html", TEXT);
	$r->add_textbox("end_html", TEXT);
	$r->add_textbox("control_code", TEXT);
	$r->add_textbox("onchange_code", TEXT);
	$r->add_textbox("onclick_code", TEXT);

	$r->add_radio("length_units", TEXT, $length_types, MEASUREMENT_UNITS_MSG);
	$r->change_property("length_units", DEFAULT_VALUE, "cm");

	$r->add_checkbox("sites_all", INTEGER);
	$r->change_property("sites_all", DEFAULT_VALUE, 1);

	$r->add_hidden("category_id", INTEGER);
	$r->add_hidden("sort_dir", TEXT);
	$r->add_hidden("sort_ord", TEXT);
	$r->add_hidden("page", TEXT);
	$r->return_page = "admin_properties.php";

	$r->get_form_values();

	$ipv = new VA_Record($table_prefix . "items_properties_values", "properties");
	$ipv->add_where("item_property_id", INTEGER);
	$ipv->change_property("item_property_id", USE_IN_ORDER, true);
	$ipv->add_hidden("property_id", INTEGER);
	$ipv->change_property("property_id", USE_IN_INSERT, true);

	$ipv->add_textbox("property_value", TEXT, DESCRIPTION_MSG);
	$ipv->add_textbox("value_order", INTEGER, SORT_ORDER_MSG);
	$ipv->add_textbox("item_code", TEXT, PROD_CODE_MSG);
	$ipv->add_textbox("manufacturer_code", TEXT, MANUFACTURER_CODE_MSG);
	$ipv->parameters["property_value"][REQUIRED] = true;
	$ipv->change_property("property_value", REQUIRED, true);
	$ipv->add_textbox("additional_price", NUMBER, SELLING_PRICE_MSG);
	$ipv->add_textbox("trade_additional_price", NUMBER, TRADE_SELLING_PRICE_MSG);
	$ipv->add_textbox("percentage_price", NUMBER, PERCENTAGE_PRICE_MSG);
	$ipv->add_textbox("buying_price", NUMBER, PROD_BUYING_PRICE_MSG);
	$ipv->add_textbox("additional_weight", NUMBER, WEIGHT_MSG);
	$ipv->add_textbox("actual_weight", NUMBER, ACTUAL_WEIGHT_MSG);
	$ipv->add_textbox("stock_level", INTEGER, QUANTITY_MSG);
	$ipv->add_checkbox("use_stock_level", INTEGER);
	$ipv->add_checkbox("hide_out_of_stock", INTEGER);

	$ipv->add_textbox("image_tiny", TEXT);
	$ipv->add_textbox("image_small", TEXT);
	$ipv->add_textbox("image_large", TEXT);
	$ipv->add_textbox("image_super", TEXT);

	$ipv->add_textbox("download_files_ids", TEXT);
	$ipv->add_checkbox("hide_value", INTEGER);
	$ipv->add_checkbox("is_default_value", INTEGER);
	
	$more_properties = get_param("more_properties");
	$number_properties = get_param("number_properties");

	$eg = new VA_EditGrid($ipv, "properties");
	$eg->get_form_values($number_properties);

	$eg->set_event(BEFORE_INSERT, "check_value_order");
	$eg->set_event(BEFORE_UPDATE, "check_value_order");
	$eg->set_event(BEFORE_SHOW, "check_downloads_ids");

	$operation = get_param("operation");
	$property_id = get_param("property_id");
	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }

	if (!$item_type_id) {
		$r->change_property("usage_type", SHOW, false);
		$r->set_value("usage_type", 1);
	}
	$return_page = $r->get_return_url();

	if ($sitelist) {
		$selected_sites = array();
		if (strlen($operation)) {
			$sites = get_param("sites");
			if ($sites) {
				$selected_sites = explode(",", $sites);
			}
		} elseif ($property_id) {
			$sql  = "SELECT site_id FROM " . $table_prefix . "items_properties_sites ";
			$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$selected_sites[] = $db->f("site_id");
			}
		}
	}

	// get sizes values
	$sizes = array(); 
	$total_width_cols = get_param("total_width_cols");
	$total_height_rows = get_param("total_height_rows");
	if ($operation) {
		for ($h = 0; $h <= $total_height_rows; $h++) {
			$sizes[$h] = array();
			for ($w = 0; $w <= $total_width_cols; $w++) {
				if ($h == 0 && $w > 0) {
					$value = get_param("size_width_".$w);
					$sizes[$h][$w] = $value;
				}
				if ($w == 0 && $h > 0) {
					$value = get_param("size_height_".$h);
					$sizes[$h][$w] = $value;
				}
				if ($w > 0 && $h > 0) {
					$value = get_param("size_price_".$w."_".$h);
					$sizes[$h][$w] = $value;
				}
			}
		}
	} else if ($property_id) {
		$db_sizes = array();
		$db_height = array();
		$db_width = array();
		$sql  = " SELECT * FROM " . $table_prefix . "items_properties_sizes ";
		$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
		$sql .= " ORDER BY height, width ";
		$db->query($sql);
		while ($db->next_record()) {
			$width = $db->f("width");
			$height = $db->f("height");
			$width = round($width, 4);
			$height = round($height, 4);
			$db_height[$height] = "";
			$db_width[$width] = "";
			if (!isset($db_sizes[$height])) { $db_sizes[$height] = array(); }
			$price = $db->f("price");
			$db_sizes[$height][$width] = round($price, 4);
		}
		$total_width_cols = count($db_width);
		$total_height_rows = count($db_height);
		$w = 0;
		// populate width values first
		$sizes[0] = array();
		foreach ($db_width as $size_width => $value) {
			$w++;
			$sizes[0][$w] = $size_width;
		}			
		$h = 0; 
		foreach ($db_height as $size_height => $value) {
			$h++; $w = 0;
			$sizes[$h] = array();
			$sizes[$h][0] = $size_height;
			foreach ($db_width as $size_width => $value) {
				$w++;
				$sizes[$h][$w] = $db_sizes[$size_height][$size_width];
			}			
		}
	} else {
		$total_width_cols = 5;
		$total_height_rows = 5;
	}

	// check for errors
	$sizes_errors = array(); $filled_rows = false; $filled_cols = false;
	if (count($sizes)) {
		for ($h = 0; $h <= $total_height_rows; $h++) {
			$sizes_errors[$h] = array();
			for ($w = 0; $w <= $total_width_cols; $w++) {
				if ($h == 0 && $w > 0) {
					$size_width = $sizes[$h][$w];
					if (!strlen($size_width)) {
						// check if column has any data
						for ($c = 1; $c <= $total_height_rows; $c++) {
							if (strlen($sizes[$c][$w])) {
								$sizes_errors[0][$w] = true;
							}
						}
					} else if(!is_numeric($size_width)) {
						$sizes_errors[0][$w] = true;
					} else {
						$filled_cols = true;
					}
  
				}
				if ($w == 0 && $h > 0) {
					$size_height = $sizes[$h][$w];
					if (!strlen($size_height)) {
						// check if row has any data
						for ($c = 1; $c <= $total_width_cols; $c++) {
							if (strlen($sizes[$h][$c])) {
								$sizes_errors[$h][0] = true;
							}
						}
					} else if (!is_numeric($size_height)) {
						$sizes_errors[$h][0] = true;
					} else {
						$filled_rows = true;
					}
				}
				if ($w > 0 && $h > 0) {
					$size_price = $sizes[$h][$w];
					if (!strlen($size_price)) {
						$column_data = false; $row_data = false; 
						for ($c = 0; $c <= $total_width_cols; $c++) {
							if (strlen($sizes[$h][$c])) {
								$row_data = true;
							}
						}
						for ($c = 0; $c <= $total_height_rows; $c++) {
							if (strlen($sizes[$c][$w])) {
								$column_data = true;
							}
						}
  
						if ($column_data && $row_data) {
							$sizes_errors[$h][$w] = true;
						}
					} else if (!is_numeric($size_price)) {
						$sizes_errors[$h][$w] = true;
					}
				}
			}
		}
	}

	$is_sizes_errors = false; 
	foreach ($sizes_errors as $h => $cols) {
		foreach ($cols as $w => $is_size_error) {
			if ($is_size_error) {
				$is_sizes_errors = true;
			}
		}
	}


	if(strlen($operation) && !$more_properties)
	{
		$tab = "general";
		if($operation == "cancel")
		{
			header("Location: " . $return_page);
			exit;
		}
		else if($operation == "delete" && $property_id)
		{
			$db->query("DELETE FROM " . $table_prefix . "items_properties WHERE property_id=" . $db->tosql($property_id, INTEGER));		
			$db->query("DELETE FROM " . $table_prefix . "items_properties_values WHERE property_id=" . $db->tosql($property_id, INTEGER));		
			$db->query("DELETE FROM " . $table_prefix . "items_properties_sites WHERE property_id=" . $db->tosql($property_id, INTEGER));
			$db->query("DELETE FROM " . $table_prefix . "items_properties_sizes WHERE property_id=" . $db->tosql($property_id, INTEGER));
			$db->query("UPDATE " . $table_prefix . "items_properties SET percentage_price_type=NULL,percentage_property_id=NULL WHERE percentage_property_id=" . $db->tosql($property_id, INTEGER));
			header("Location: " . $return_page);
			exit;
		}

		$is_valid = $r->validate();
		$is_valid = ($eg->validate() && $is_valid); 
		if ($is_sizes_errors) {
			$is_valid = false;
			$r->errors .= CORRECT_HIGHLIGHTED_FIELDS_MSG."<br/>";
		} else { 
			if (!$filled_rows) {
				$error_message = str_replace("{field_name}", HEIGHT_MSG, REQUIRED_MESSAGE);
				$r->errors .= $error_message ."<br/>";
			} 	
			if (!$filled_cols) {
				$error_message = str_replace("{field_name}", WIDTH_MSG, REQUIRED_MESSAGE);
				$r->errors .= $error_message ."<br/>";
			}
		}


		if($is_valid)
		{

			if ($r->get_value("control_type") != "WIDTH_HEIGHT") {
				$r->set_value("length_units", "");
			}
			if (!$sitelist) {
				$r->set_value("sites_all", 1);
			}
			if ($r->get_value("free_price_type") != 1 && !$r->is_empty("free_price_amount")) {
				$r->set_value("free_price_amount", intval($r->get_value("free_price_amount")));
			}
			$r->set_value("property_type_id", 1);
			if(strlen($property_id))
			{
				$r->update_record();
				$eg->set_values("property_id", $property_id);
				$eg->update_all($number_properties);
			} else {
				$r->set_value("item_id", $item_id);
				$r->set_value("item_type_id", $item_type_id);
				$r->insert_record();
				$property_id = $db->last_insert_id();
				$r->set_value("property_id", $property_id);

				$eg->set_values("property_id", $property_id);
				$eg->insert_all($number_properties);
			}

			// update sites
			if ($sitelist) {
				$db->query("DELETE FROM " . $table_prefix . "items_properties_sites WHERE property_id=" . $db->tosql($property_id, INTEGER));
				for ($st = 0; $st < sizeof($selected_sites); $st++) {
					$site_id = $selected_sites[$st];
					if (strlen($site_id)) {
						$sql  = " INSERT INTO " . $table_prefix . "items_properties_sites (property_id, site_id) VALUES (";
						$sql .= $db->tosql($property_id, INTEGER) . ", ";
						$sql .= $db->tosql($site_id, INTEGER) . ") ";
						$db->query($sql);
					}
				}
			}

			// update property sizes
			$db->query("DELETE FROM " . $table_prefix . "items_properties_sizes WHERE property_id=" . $db->tosql($property_id, INTEGER));
			if ($r->get_value("control_type") == "WIDTH_HEIGHT") {
				foreach ($sizes as $h => $cols) {
					foreach ($cols as $w => $size_price) {
						if ($h > 0 && $w > 0) {
							$size_height = $sizes[$h][0];
							$size_width = $sizes[0][$w];
							if (strlen($size_height) && strlen($size_width)) {
								// insert only data which has header information
								$sql  = " INSERT INTO " . $table_prefix . "items_properties_sizes (property_id, width, height, price) VALUES (";
								$sql .= $db->tosql($property_id, INTEGER) . ", ";
								$sql .= $db->tosql($size_width, NUMBER) . ", ";
								$sql .= $db->tosql($size_height, NUMBER) . ", ";
								$sql .= $db->tosql($size_price, NUMBER) . ") ";
								$db->query($sql);
							}
						}
					}
				}
			}

			header("Location: " . $return_page);
			exit;
		}
	}
	else if(strlen($property_id) && !$more_properties)
	{
		$r->get_db_values();
		if ($r->get_value("free_price_type") != 1 && !$r->is_empty("free_price_amount")) {
			$r->set_value("free_price_amount", intval($r->get_value("free_price_amount")));
		}
		if ($r->get_value("additional_price") == 0) {
			$r->set_value("additional_price", "");
		}
		$eg->set_value("property_id", $property_id);
		$eg->change_property("item_property_id", USE_IN_SELECT, true);
		$eg->change_property("item_property_id", USE_IN_WHERE, false);
		$eg->change_property("property_id", USE_IN_WHERE, true);
		$eg->change_property("property_id", USE_IN_SELECT, true);
		$number_properties = $eg->get_db_values();
		if ($number_properties == 0) {
			$number_properties = 5;
		}
	}
	else if($more_properties)
	{
		$number_properties += 5;
	}
	else // set default values
	{
		$sql  = " SELECT MAX(property_order) FROM " . $table_prefix . "items_properties ";
		if ($item_type_id > 0) {
			$sql .= " WHERE item_type_id=" . $db->tosql($item_type_id, INTEGER);
		} else {
			$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
		}
		$property_order = get_db_value($sql);
		$property_order = ($property_order) ? ($property_order + 1) : 1;
		$r->set_value("show_for_user", 1);
		$r->set_value("property_order", $property_order);
		$r->set_value("use_on_list", 1);
		$r->set_value("use_on_details", 1);
		$r->set_value("use_on_table", 1);
		$r->set_value("use_on_grid", 1);
		$r->set_value("usage_type", 1);
		$r->set_value("sites_all", 1);
		$r->set_value("length_units", "cm");

		$number_properties = 5;
	}
	$t->set_var("number_properties", $number_properties);
	$units_desc = $r->get_value("length_units");
	$price_desc = $currency["left"].$currency["right"];
	$t->set_var("units_desc", htmlspecialchars($units_desc));
	$t->set_var("price_desc", htmlspecialchars($price_desc));


	$parent_values = array(array("", ""));
	if (is_array($parent_properties) && sizeof($parent_properties) > 1) {
		for ($p = 0; $p < sizeof($parent_properties); $p++) {
			$parent_id = $parent_properties[$p][0];
			if ($parent_id) {
				$t->set_var("property_id", $parent_id);
				$t->parse("parent_options", true);
				$sql  = " SELECT item_property_id, property_value FROM " . $table_prefix . "items_properties_values ";
				$sql .= " WHERE property_id=" . $db->tosql($parent_id, INTEGER);
				$db->query($sql);
				while ($db->next_record()) {
					$list_id = $db->f("item_property_id");
					$list_value = $db->f("property_value");
					$t->set_var("value_id", $list_id);
					$t->set_var("value_title", htmlspecialchars($list_value));
					$t->parse("options_values", true);
					if ($r->get_value("parent_property_id") == $parent_id) {
						$parent_values[] = array($list_id, $list_value);
					}
				}
			}
		}
	}

	$r->change_property("parent_value_id", VALUES_LIST, $parent_values);
	$eg->set_parameters_all($number_properties);
	$r->set_parameters();

	if (is_array($parent_properties) && sizeof($parent_properties) > 1) {
		if (is_array($parent_values) && sizeof($parent_values) > 1) {
			$t->set_var("parent_value_style", "display: block;");
			$t->set_var("percentage_property_id_style", "display: block;");
		} else {
			$t->set_var("parent_value_style", "display: none;");
			$t->set_var("percentage_property_id_style", "display: none;");
		}
		$t->parse("parent_property_block", false);
	}

/*
	if ($item_type_id > 0) {
		$t->parse("type_path");
	} else {
		$t->parse("product_path");
	}
*/
	if(strlen($property_id)) {
		$t->set_var("save_button", UPDATE_BUTTON);
		$t->parse("delete", false);	
	} else {
		$t->set_var("save_button", ADD_BUTTON);
		$t->set_var("delete", "");	
	}

	$control_type = $r->get_value("control_type");
	if ($control_type == "CHECKBOXLIST" || $control_type == "LISTBOX" || $control_type == "RADIOBUTTON" || $control_type == "TEXTBOXLIST" || $control_type == "IMAGE_SELECT") {
		$t->set_var("option_values_style", "");
		$t->set_var("option_sizes_style", "display: none;");
	} else if ($control_type == "WIDTH_HEIGHT") {
		$t->set_var("option_sizes_style", "");
		$t->set_var("option_values_style", "display: none;");
	} else {
		$t->set_var("option_values_style", "display: none;");
		$t->set_var("option_sizes_style", "display: none;");
	}

	$percentage_price_type = $r->get_value("percentage_price_type");
	if ($percentage_price_type == "2" || $percentage_price_type == "3") {
		$t->set_var("percentage_property_id_style", "");
	} else {
		$t->set_var("percentage_property_id_style", "display: none;");
	}


	// populate sizes array if it's empty
	if (count($sizes) == 0) {
		for ($h = 0; $h <= $total_height_rows; $h++) {
			$sizes[$h] = array();
			for ($w = 0; $w <= $total_width_cols; $w++) {
				$sizes[$h][$w] = "";
			}
		}
	}

	foreach ($sizes as $h => $cols) {
		$t->set_var("size_cols", "");
		foreach ($cols as $w => $value) {
			$t->set_var("error_class", "");
			if ($h == 0 && $w > 0) {
				$t->set_var("windex", $w);
				$t->set_var("width_value", $value);
				if (isset($sizes_errors[0][$w])) {
					$t->set_var("error_class", "errorCell");
				}
				if (strlen($value)) {
					$t->set_var("width_value", htmlspecialchars($value));
					$t->set_var("input_class", "");
				} else {
					$t->set_var("width_value", htmlspecialchars($units_desc));
					$t->set_var("input_class", "unitsComment");
				}
				$t->parse("width_cell", false);
				$t->parse("size_width", true);
			}

			if ($h > 0 && $w == 0) {
				$t->set_var("hindex", $h);
				if (strlen($value)) {
					$t->set_var("height_value", htmlspecialchars($value));
					$t->set_var("input_class", "");
				} else {
					$t->set_var("height_value", htmlspecialchars($units_desc));
					$t->set_var("input_class", "unitsComment");
				}

				$t->parse("height_cell", false);
			}

			if ($h > 0 && $w > 0) {
				$t->set_var("windex", $w);
				$t->set_var("hindex", $h);
				if (strlen($value)) {
					$t->set_var("price_value", htmlspecialchars($value));
					$t->set_var("input_class", "");
				} else {
					$t->set_var("price_value", htmlspecialchars($price_desc));
					$t->set_var("input_class", "priceComment");
				}
				if (isset($sizes_errors[$h][$w])) {
					$t->set_var("error_class", "errorCell");
				}
				$t->parse("price_cell", false);
				$t->parse("size_cols", true);
			}
		}

		if ($h > 0) {
			if (isset($sizes_errors[$h][0])) {
				$t->set_var("error_class", "errorCell");
			} else {
				$t->set_var("error_class", "");
			}	
			$t->set_var("hindex", $h);
			$t->parse("size_rows", true);
		}
	}
	$t->set_var("total_width_cols", $total_width_cols);
	$t->set_var("total_height_rows", $total_height_rows);

	// parse template for price cell 
	$t->set_var("hidden_id", "price_cell");
	$t->set_var("input_class", "priceComment");
	$t->set_var("price_value", $price_desc);
	$t->set_var("windex", "{windex}");
	$t->set_var("hindex", "{hindex}");
	$t->parse_to("price_cell", "hidden_block", false);
	$t->parse("hidden_blocks", true);
	// parse template for height cell 
	$t->set_var("hidden_id", "height_cell");
	$t->set_var("input_class", "unitsComment");
	$t->set_var("height_value", $units_desc);
	$t->set_var("hindex", "{hindex}");
	$t->parse_to("height_cell", "hidden_block", false);
	$t->parse("hidden_blocks", true);
	// parse template for width cell 
	$t->set_var("hidden_id", "width_cell");
	$t->set_var("input_class", "unitsComment");
	$t->set_var("width_value", $units_desc);
	$t->set_var("windex", "{windex}");
	$t->parse_to("width_cell", "hidden_block", false);
	$t->parse("hidden_blocks", true);

	// parse sites
	if ($sitelist) {
		$sites = array();
		$sql = " SELECT site_id, site_name FROM " . $table_prefix . "sites ";
		$db->query($sql);
		while ($db->next_record())	{
			$site_id   = $db->f("site_id");
			$site_name = $db->f("site_name");
			$sites[$site_id] = $site_name;
			$t->set_var("site_id", $site_id);
			$t->set_var("site_name", $site_name);
			if (in_array($site_id, $selected_sites)) {
				$t->parse("selected_sites", true);
			} else {
				$t->parse("available_sites", true);
			}
		}
	}

	$tabs = array(
		"general" => array("title" => ADMIN_GENERAL_MSG), 
		"html" => array("title" => OPTIONS_APPEARANCE_MSG), 
		"js" => array("title" => JAVASCRIPT_SETTINGS_MSG), 
		"sites" => array("title" => ADMIN_SITES_MSG, "show" => $sitelist),
	);

	parse_admin_tabs($tabs, $tab, 7);
	$t->set_var("tab", $tab);

	$hide_left_menu = true;
	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

	function check_value_order()
	{
		global $eg;
		$value_order = $eg->record->get_value("value_order");
		if (!$value_order) {
			$eg->record->set_value("value_order", 1);
		}
	}

	function check_downloads_ids()
	{
		global $eg, $r, $t, $db, $table_prefix, $downloadable_files;
		$t->set_var("selected_files", "");

		$download_files_ids = $eg->record->get_value("download_files_ids");
		$sql  = " SELECT * FROM " . $table_prefix . "items_files ";
		$sql .= " WHERE file_id IN (" . $db->tosql($download_files_ids, INTEGERS_LIST) . ")" ;
		$db->query($sql);
		while ($db->next_record()) {
			$file_id = $db->f("file_id");
			$file_title = $db->f("download_title");
			if (!$file_title) {
				$file_title = basename($db->f("download_path"));
			}

			$t->set_var("file_id", $file_id);
			$t->set_var("file_title", $file_title);
			$t->set_var("file_title_js", str_replace("\"", "&quot;", $file_title));
		  
			$t->parse("selected_files", true);
			$t->parse("selected_files_js", true);
		}

		if ($downloadable_files) {
			$t->parse("select_file_link", false);
		} else {
			$t->set_var("select_file_link", "");
		}

	}

?>