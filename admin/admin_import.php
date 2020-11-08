<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_import.php                                         ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	@set_time_limit(600);
	@ini_set("auto_detect_line_endings", 1);
	@ini_set("max_input_vars", 10000);
	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/import_functions.php");
	include_once($root_folder_path . "includes/friendly_functions.php");
	include_once($root_folder_path . "includes/profile_functions.php");
	include_once($root_folder_path . "includes/order_recalculate.php");
	include_once($root_folder_path . "includes/order_items.php");
	include_once($root_folder_path . "includes/order_links.php");
	include_once("./admin_common.php");
	// include custom message once more time to override messages above
	if (file_exists($root_folder_path ."messages/".$language_code."/custom_messages.php")) {
		include($root_folder_path ."messages/".$language_code."/custom_messages.php");
	}

	check_admin_security("import_export");

	define("COMMA_DECIMAL", true); // replace comma decimal separator to point

	$errors = "";
	$max_columns = 9;
	$rnd = get_param("rnd");
	$table = get_param("table");
	$file_type = get_param("file_type");
	$total_columns = get_param("total_columns");
	$total_related = get_param("total_related");
	$xml_product_root = get_param("xml_product_root");
	$csv_delimiter = get_param("csv_delimiter");
	$csv_file_path = get_param("csv_file_path");
	$operation = get_param("operation");
	$category_id = get_param("category_id");
	$newsletter_id = get_param("newsletter_id");
	$session_rnd = get_session("session_rnd");
	$delimiter_char = ($csv_delimiter === "tab") ? "\t" : substr($csv_delimiter, 0, 1);
	$tmp_dir = get_setting_value($settings, "tmp_dir", "");
	$features_groups = array();
	// get table additional settings
	$import_settings = get_admin_settings(array("import_properties_number", "import_features_number"));
	if ($operation) {
		$properties_number = get_param("properties_number");
		$features_number = get_param("features_number");
	} else {
		$properties_number = get_setting_value($import_settings, "import_properties_number", 1);
		$features_number = get_setting_value($import_settings, "import_features_number", 1);
	}
	if ($properties_number < 1) { $properties_number = 1;	}
	if ($features_number < 1) { $features_number = 1;	}

	// initiliaze special import object
	$imp = new VA_Import($table);

	// show DB errors
	$db->DebugError = true;

	$eol = get_eol();
	$import_related_table   = get_param("import_related_table");
	$csv_related_delimiter  = get_param("csv_related_delimiter", "comma");
	$delimiters_symbols     = array("comma" => ",", "tab" => "\t", "semicolon" => ";", "vertical_bar" => "|", "row" => "row", "space" => " ", "newline" => $eol);
	if ($csv_related_delimiter) {
		$related_delimiter_char = $delimiters_symbols[$csv_related_delimiter];
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_import.html");
	$t->set_var("rnd", $rnd);

	$admin_header_template = "admin_header_wide.html";
	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_select_href", "admin_select.php");
	$t->set_var("admin_import_href", "admin_import.php");
	$t->set_var("admin_items_list_href", "admin_items_list.php");
	$t->set_var("admin_users_list_href", "admin_newsletter_users.php");
	$t->set_var("header_data", "''"); // empty data if json object wasn't initiliazied
	$t->set_var("keys_chain", "''"); // empty data if json object wasn't initiliazied
	// set some messages
	$upload_select_msg = str_replace("{button_name}", UPLOAD_BUTTON, UPLOAD_SELECT_MSG);
	$t->set_var("UPLOAD_SELECT_MSG", $upload_select_msg); // empty data if json object wasn't initiliazied

	// url object to generate related links
	$admin_import_url = new VA_URL("admin_import.php", false);
	$admin_import_url->add_parameter("category_id", REQUEST, "category_id");
	$admin_import_url->add_parameter("id", REQUEST, "id");
	$admin_import_url->add_parameter("ids", REQUEST, "ids");
	$admin_import_url->add_parameter("s", REQUEST, "s");
	$admin_import_url->add_parameter("sl", REQUEST, "sl");
	$admin_import_url->add_parameter("ss", REQUEST, "ss");
	$admin_import_url->add_parameter("ap", REQUEST, "ap");

	$table_name = "";
	$db_columns = array(); 
	$related_table = "";
	$related_table_name = "";
	$related_columns = array(); 
	$states = va_states();
	$countries = va_countries();
	if (function_exists("va_delivery_countries")) {
		$delivery_countries = va_delivery_countries();
	} else {
		$delivery_countries = va_countries();
	}
	// pass currency code to get fresh values
	$sess_currency = get_currency();
	$currency = get_currency($sess_currency["code"]);

	// prepare site list
	$sites = array(); 
	$sql = " SELECT * FROM " . $table_prefix . "sites ";
	$db->query($sql);
	while ($db->next_record()) {
		$db_site_id = $db->f("site_id");
		$short_name = trim(get_translation($db->f("short_name")));
		$site_name = trim(get_translation($db->f("site_name")));
		$sites[$db_site_id] = array("short_name" => $short_name, "site_name" => $site_name);
	}

	if ($table == "items") {
		check_admin_security("products_import");
		include_once("./admin_table_items.php");		
		// check addition options for table
		$match_item_code = get_setting_value($settings, "match_item_code", 0);
		$match_manufacturer_code = get_setting_value($settings, "match_manufacturer_code", 0);

		// parse links for related import
		$admin_import_url->add_parameter("table", CONSTANT, "items_files");
		$t->set_var("admin_items_files_import_url", $admin_import_url->get_url());

		$admin_import_url->add_parameter("table", CONSTANT, "items_properties_values");
		$t->set_var("admin_items_properties_values_import_url", $admin_import_url->get_url());

		$admin_import_url->add_parameter("table", CONSTANT, "items_prices");
		$t->set_var("admin_items_prices_import_url", $admin_import_url->get_url());

		$admin_import_url->add_parameter("table", CONSTANT, "items_serials");
		$t->set_var("admin_items_serials_export_url", $admin_import_url->get_url());

		$t->parse("products_other_links", false);

	} elseif ($table == "items_files") {
		check_admin_security("products_import");
		include_once("./admin_table_items_files.php");		
	} elseif ($table == "items_properties_values") {
		check_admin_security("products_import");
		include_once("./admin_table_items_properties_values.php");		
	} elseif ($table == "items_prices") {
		check_admin_security("products_import");
		include_once("./admin_table_items_prices.php");		
	} elseif ($table == "categories") {
		check_admin_security("categories_import");
		include_once("./admin_table_categories.php");
	} elseif ($table == "users") {
		check_admin_security("import_users");
		include_once("./admin_table_users.php");
	} elseif ($table == "newsletters_emails") {
		check_admin_security("newsletter");
		include_once("../includes/newsletter_functions.php");
		include_once("./admin_table_newsletters_emails.php");
	} elseif ($table == "newsletters_users") {
		check_admin_security("import_users");
		include_once("./admin_table_emails.php");
	} elseif ($table == "orders") {
		check_admin_security("orders_import");
		include_once("./admin_table_orders.php");
		$match_item_code = get_setting_value($settings, "match_item_code", 0);
		$match_manufacturer_code = get_setting_value($settings, "match_manufacturer_code", 0);
	} else if ($table == "tax_rates") {
		check_admin_security("tax_rates");
		include_once("./admin_table_tax_rates.php");
	} else if ($table == "items_serials" || $table == "serials") {
		check_admin_security("products_import");
		include_once("./admin_table_serials.php");
	} else {
		$table_name = "";
		$table_title = "";
		$errors = CANT_FIND_TABLE_IMPORT_MSG;
	}

	// get translation for DB columns
	foreach ($db_columns as $column_name => $column_info) {
		if (isset($column_info["title"])) { // new format
			parse_value($db_columns[$column_name]["title"]);
		} else { // old format
			parse_value($db_columns[$column_name][0]);
		}
	}
	foreach ($related_columns as $column_name => $column_info) {
		if (isset($column_info["title"])) { // new format
			parse_value($related_columns[$column_name]["title"]);
		} else { // old format
			parse_value($related_columns[$column_name][0]);
		}
	}



	if(!$errors){
		if ($table == "orders") {
			$sql  = " SELECT property_id, property_name FROM " . $table_prefix . "order_custom_properties ";
			$sql .= " WHERE payment_id=0 ";
			$sql .= " GROUP BY property_name, property_id ";
			$db->query($sql);
			while ($db->next_record()) {
				$property_id = $db->f("property_id");
				$property_name = $db->f("property_name");
				//$db_columns["order_property_" . $property_id] = array(get_translation($property_name), TEXT, 5, false, $table_prefix . "order_properties");
			}

			$sql = " SELECT property_name FROM " . $table_prefix . "items_properties GROUP BY property_name";
			$db->query($sql);
			while ($db->next_record()) {
				$property_name = $db->f("property_name");
				//$related_columns["order_item_property_" . $property_name] = array(PRODUCT_OPTION_MSG ." (" . get_translation($property_name) . ")", TEXT, 5, false, $table_prefix . "order_items_properties");
			}
			$sql = " SELECT property_name FROM " . $table_prefix . "orders_items_properties GROUP BY property_name ";
			$db->query($sql);
			while ($db->next_record()) {
				$property_name = $db->f("property_name");
				//$related_columns["order_item_property_" . $property_name] = array(PRODUCT_OPTION_MSG . " (" . get_translation($property_name) . ")", TEXT, 5, false, $table_prefix . "order_items_properties");
			}
		} else if ($table == "users") {
			// update custom properties field type
			foreach ($db_columns as $column_name => $column_info) {
				if (preg_match("/^user_property_/", $column_name)) {
					if (isset($column_info["title"])) { // new format
						$db_columns[$column_name]["field_type"] = 5;
					} else { // old format
						$db_columns[$column_name][2] = 5;
					}
				}
			}
		}


		// check if we need to populate values list
		foreach ($db_columns as $column_name => $column_info) {
			if (isset($column_info["values_sql"]) && $column_info["values_sql"]) {
				$control_type = $column_info["control"];
				$values = array();
				if ($control_type == LISTBOX) {
					// added first empty value
					$values[] = array("", "");
				}
				$sql = $column_info["values_sql"];
				$db->query($sql);
				while ($db->next_record()) {
					$value_id = $db->f(0);
					$value_title = $db->f(1);
					$values[] = array($value_id, $value_title);
				}
				$db_columns[$column_name]["values"] = $values;
			}
		}
	}

	$t->set_var("table", $table);
	$t->set_var("table_title", $table_title);
	$t->set_var("table_title", $table_title);
	$t->set_var("db_columns", json_encode($db_columns));
	$t->set_var("related_columns", json_encode($related_columns));

	if ($operation == "upload") {

		if (strlen($csv_file_path)) {
			if (file_exists($csv_file_path)) {
				$fp = fopen($csv_file_path, "r");
				if (!$fp) {
					$errors = CANT_OPEN_IMPORTED_MSG;
				}
			} else {
				$errors = FILE_DOESNT_EXIST_MSG . "<b>$csv_file_path</b>";
			}
		} else {
			$tmp_name = $_FILES["csv_file"]["tmp_name"];
			$filename = $_FILES["csv_file"]["name"];
			$filesize = $_FILES["csv_file"]["size"];
			$upload_error = isset($_FILES["csv_file"]["error"]) ? $_FILES["csv_file"]["error"] : "";

			if ($upload_error == 1) {
				$errors = FILESIZE_DIRECTIVE_ERROR_MSG;
			} elseif ($upload_error == 2) {
				$errors = FILESIZE_PARAMETER_ERROR_MSG;
			} elseif ($upload_error == 3) {
				$errors = PARTIAL_UPLOAD_ERROR_MSG;
			} elseif ($upload_error == 4) {
				$errors = NO_FILE_UPLOADED_MSG;
			} elseif ($upload_error == 6) {
				$errors = TEMPORARY_FOLDER_ERROR_MSG;
			} elseif ($upload_error == 7) {
				$errors = FILE_WRITE_ERROR_MSG;
			} elseif ($tmp_name == "none" || !strlen($tmp_name)) {
				$errors = NO_FILE_UPLOADED_MSG;
			}

			if (!strlen($errors)) {
				if ($tmp_dir) {
					$tmp_filename = "tmp_" . md5(uniqid(rand(), true)) . ".csv";
					if (@move_uploaded_file($tmp_name, $tmp_dir. $tmp_filename)) {
						$csv_file_path = $tmp_dir . $tmp_filename;
					}
				} else {
					$errors = SPECIFY_TEMP_FOLDER_MSG;
				}

				if (strlen($csv_file_path)) {
					$fp = fopen($csv_file_path, "r");
				}

				if (!$errors && !$fp) {
					$errors = CANT_OPEN_IMPORTED_MSG;
				}
			}
		}

		// TODO: add here check for uploaded file type - CSV / XML
		if (!strlen($errors)) {
			//$csv_data = fgetcsv($fp, 4096, $delimiter_char);
			$data_file = FilesImportStrategy::getParser($file_type, $csv_file_path, $fp, $delimiter_char);

			//$header_data = get_header_data($csv_data);
			$header_data = $data_file->getFieldsHeaders();
			$xml_fields = $data_file->getHeaders();
			//var_dump($header_data);exit;
		}
		fclose($fp);

		if (!strlen($errors)) {
			
			// update import settings
			$import_settings = array();
			if ($properties_number) {
				$import_settings["import_properties_number"] = $properties_number;
			}
			if ($features_number) {
				$import_settings["import_features_number"] = $features_number;
			}
			if (count($import_settings)) {
				update_admin_settings($import_settings);
			}

			$operation = "import";
			$column_number = 0;

			// transform column name to lowercase for comparison and prepare js array
			$header_json = array();
			foreach ($header_data as $key => $column_info) {
				$header_json[] = array(
					"value" => convert_to_utf8($key),
					"title" => convert_to_utf8($column_info["title"]),
				);
			}

			$t->set_var("header_data", json_encode($header_json));

			// calculate fields number
			$fields_total = 0; 
			foreach ($db_columns as $column_name => $column_info) {
				if ($table == "items" && $column_name == "property_name") {
					$field_max_index = $properties_number;
				} else if ($table == "items" && $column_name == "feature_name") {
					$field_max_index = $features_number;
				} else {
					$field_max_index = 1;
				}
				for ($field_index = 1; $field_index <= $field_max_index; $field_index++) {
					$fields_total++;
				}
			}
			$column_fields = ceil($fields_total/3);

			$column_number = 0; $field_index = 0; $field_max_index = 1;
			foreach ($db_columns as $column_name => $column_info) {
				if (isset($column_info["title"])) {
					// new format
					$column_title = get_translation($column_info["title"]);
					$data_type = $column_info["data_type"];
					$field_type = $column_info["field_type"];
					$field_required = $column_info["required"];
					$default_value = isset($column_info["default"]) ? $column_info["default"] : "";
					$field_aliases = isset($column_info["aliases"]) ? $column_info["aliases"] : array();
				} else {
					// old format
					$column_title = get_translation($column_info[0]);
					$data_type = $column_info[1];
					$field_type = $column_info[2];
					$field_required = $column_info[3];
					$default_value = isset($column_info[4]) ? $column_info[4] : "";
					$field_aliases = array();
				}
				// add basic column name and title to check match with data in the file
				$field_aliases[] = $column_name;
				$field_aliases[] = $column_title;
				foreach ($field_aliases as $alias_index => $alias) {
					$alias = get_translation($alias);
					if(function_exists("mb_strtolower")) {
						$alias = trim(mb_strtolower($alias, "UTF-8"));
					} else {
						$alias = trim(strtolower($alias));
					}
					$field_aliases[$alias_index] = $alias;
				}
				// 1 - WHERE_DB_FIELD, 2 - USUAL_DB_FIELD, 3 - FOREIGN_DB_FIELD, 4 - HIDE_DB_FIELD, 5 - RELATED_DB_FIELD, 6 - CUSTOM_FIELD   
				
				if ($table == "items" && $column_name == "property_name") {
					$field_max_index = $properties_number;
				} else if ($table == "items" && $column_name == "feature_name") {
					$field_max_index = $features_number;
				} else {
					$field_max_index = 1;
				}

				for ($field_index = 1; $field_index <= $field_max_index; $field_index++) {
					$column_number++;
					$t->set_var("column_number", htmlspecialchars($column_number));
					$t->set_var("column_name", htmlspecialchars($column_name));
					$t->set_var("column_title", htmlspecialchars($column_title));
					// check if we can find some data source
					$column_source = ""; $column_data = ""; 
					$source_desc = ""; $data_desc = "";
					// check aliases for match with file header data
					$alias_matched = "";
					foreach ($field_aliases as $alias) {
						if (strlen($alias) && isset($header_data[$alias])) {
							$alias_matched = $alias;
							break;
						}
					}

					if (strlen($alias_matched)) {
						$row_class = "source";
						$column_source = strtolower($file_type);
						$source_desc = strtoupper($file_type);
						$column_data = $alias_matched;
						$data_desc = $header_data[$column_data]["title"];
					} else {
						$row_class = "unselected";
						$column_data = "Please Select"; 
			  
						$source_desc = "&ndash;";
						$column_source = "ignore";
						$row_class = "ignore";
						$column_data = "Ignore"; 
						$data_desc = $column_data;
					}
			  
					$t->set_var("column_source", htmlspecialchars($column_source));
					$t->set_var("source_desc", $source_desc);
					$t->set_var("column_data", htmlspecialchars($column_data));
					$t->set_var("data_desc", htmlspecialchars($data_desc));
					$t->set_var("row_class", htmlspecialchars($row_class));

					$t->set_var("column_id", "row_".$column_number);
					$t->set_var("column_type", "main");
					$t->set_var("column_name_param", "column_name_".$column_number);
					$t->set_var("column_source_param", "column_source_".$column_number);
					$t->set_var("column_data_param", "column_data_".$column_number);
			  
					if ($column_number <= $column_fields) {
						$t->parse_to("column_template", "columns_1", true);
					} else if ($column_number <= ($column_fields*2)) {
						$t->parse_to("column_template", "columns_2", true);
					} else {
						$t->parse_to("column_template", "columns_3", true);
					}
				}
			}
			$total_columns = $column_number;
			$t->set_var("total_columns", $total_columns);

			// calculate related fields number to devide them by columns
			$related_total = 0; 
			foreach ($related_columns as $column_name => $column_info) {
				if ($related_table == "orders_items" && $column_name == "property_name") {
					$field_max_index = 1;
				} else {
					$field_max_index = 1;
				}
				$related_total += $field_max_index;
			}
			$column_fields = ceil($related_total/3);

			// show related fields
			$related_number = 0; $field_index = 0; $field_max_index = 1;
			foreach ($related_columns as $column_name => $column_info) {
				if (isset($column_info["title"])) {
					// new format
					$column_title = get_translation($column_info["title"]);
					$data_type = $column_info["data_type"];
					$field_type = $column_info["field_type"];
					$field_required = isset($column_info["required"]) ? $column_info["required"] : "";
					$default_value = isset($column_info["default"]) ? $column_info["default"] : "";
					$field_preview = isset($column_info["preview"]) ? $column_info["preview"] : "";
					$field_aliases = isset($column_info["aliases"]) ? $column_info["aliases"] : array();
				} else {
					// old format
					$column_title = get_translation($column_info[0]);
					$data_type = $column_info[1];
					$field_type = $column_info[2];
					$field_required = $column_info[3];
					$default_value = isset($column_info[4]) ? $column_info[4] : "";
					$field_preview = false;
					$field_aliases = array();
				}
				// add basic column name and title to check match with data in the file
				$field_aliases[] = $column_name;
				$field_aliases[] = $column_title;
				foreach ($field_aliases as $alias_index => $alias) {
					$alias = get_translation($alias);
					if(function_exists("mb_strtolower")) {
						$alias = trim(mb_strtolower($alias, "UTF-8"));
					} else {
						$alias = trim(strtolower($alias));
					}
					$field_aliases[$alias_index] = $alias;
				}
				
				if ($related_table == "orders_items" && $column_name == "property_name") {
					$field_max_index = $properties_number;
				} else {
					$field_max_index = 1;
				}
				for ($field_index = 1; $field_index <= $field_max_index; $field_index++) {
					$related_number++;
					$t->set_var("column_number", htmlspecialchars($related_number));
					$t->set_var("column_name", htmlspecialchars($column_name));
					$t->set_var("column_title", htmlspecialchars($column_title));
					// check if we can find some data source
					$column_source = ""; $column_data = ""; 
					$source_desc = ""; $data_desc = "";
					// check aliases for match with file header data
					$alias_matched = "";
					foreach ($field_aliases as $alias) {
						if (strlen($alias) && isset($header_data[$alias])) {
							$alias_matched = $alias;
							break;
						}
					}

					if (strlen($alias_matched)) {
						$row_class = "source";
						$column_source = strtolower($file_type);
						$source_desc = strtoupper($file_type);
						$column_data = $alias_matched;
						$data_desc = $header_data[$column_data]["title"];
					} else {
						$row_class = "unselected";
						$column_data = "Please Select"; 
			  
						$source_desc = "&ndash;";
						$column_source = "ignore";
						$row_class = "ignore";
						$column_data = "Ignore"; 
						$data_desc = $column_data;
					}
			  
					$t->set_var("column_source", htmlspecialchars($column_source));

					$t->set_var("source_desc", $source_desc);
					$t->set_var("column_data", htmlspecialchars($column_data));
					$t->set_var("data_desc", htmlspecialchars($data_desc));
					$t->set_var("row_class", htmlspecialchars($row_class));
			  
					$t->set_var("column_id", "related_".$related_number);
					$t->set_var("column_type", "related");
					$t->set_var("column_name_param", "related_name_".$related_number);
					$t->set_var("column_source_param", "related_source_".$related_number);
					$t->set_var("column_data_param", "related_data_".$related_number);

					if ($related_number <= $column_fields) {
						$t->parse_to("column_template", "related_columns_1", true);
					} else if ($related_number <= ($column_fields*2)) {
						$t->parse_to("column_template", "related_columns_2", true);
					} else {
						$t->parse_to("column_template", "related_columns_3", true);
					}
				}
			}
			$total_related = $related_number;
			$t->set_var("total_related", $total_related);
		}
	} elseif ($operation == "preview" || $operation == "import") {

		// preview and import data
		$total_columns = get_param("total_columns");
		$total_related = get_param("total_related");
		$total_errors = 0;
		$is_where = false;
		$is_related_where = false;

		// get order settings to show all necessary fields
		$param_prefix = "call_center_"; // use call center settings to check parameters settings
		$order_settings = array();
		if ($table == "orders") {
			$order_settings = get_settings("order_info");
		}

		// check NEW and all order statuses for additional checks
		$order_statuses = array(); $new_order_status_id = "";
		$sql = " SELECT status_id, status_type, status_name FROM " . $table_prefix . "order_statuses ";
		$db->query($sql);
		while ($db->next_record()) {
			$status_id = $db->f("status_id");
			$status_type = strtoupper($db->f("status_type"));
			$status_name = $db->f("status_name");
			parse_value($status_name);
			$order_statuses[$status_id] = $db->Record;
			$order_statuses[$status_id]["status_name"] = $status_name;
			if ($status_type == "NEW") {
				$new_order_status_id = $status_id;
			}
		}

		// array to keep available shipments
		$order_shipments = array(); $order_shipments_codes = array(); $order_shipments_descs = array();

		// get active payment system
		$payment_systems = array(); $default_payment_id = "";
		$sql  = " SELECT ps.* FROM " . $table_prefix . "payment_systems ps ";
		$sql .= " WHERE ps.is_active=1 OR ps.is_call_center=1 " ;
		$db->query($sql);
		while ($db->next_record()) {
			$payment_id = $db->f("payment_id");
			$is_default = $db->f("is_default");
			$payment_name = get_translation($db->f("payment_name"));
			$user_payment_name = get_translation($db->f("user_payment_name"));
			if ($user_payment_name) {
				$payment_name = $user_payment_name;
			}
			$payment_systems[$payment_id] = $db->Record;
			$payment_systems[$payment_id]["payment_name"] = $payment_name;
			if ($is_default) {
				$default_payment_id = $payment_id;
			}
		}

		// main record
		$imp->r = new VA_Record($table_name);
		// related record
		$imp->rr = new VA_Record($related_table_name);
		// special record for order coupons
		$imp->rc = new VA_Record($table_prefix."orders_coupons");
		$imp->rc->add_textbox("order_id", INTEGER);
		$imp->rc->add_textbox("coupon_id", INTEGER);
		$imp->rc->add_textbox("coupon_code", TEXT);
		$imp->rc->add_textbox("coupon_title", TEXT);
		$imp->rc->add_textbox("discount_amount", NUMBER);
		$imp->rc->add_textbox("discount_tax_amount", INTEGER);
		$imp->rc->add_textbox("order_item_id", INTEGER);
		$imp->rc->add_textbox("discount_type", INTEGER);


		/* RELATED
		if ($import_related_table) {
			$sub_r = new VA_Record($related_table_name);
			if ($related_table == "orders_items") {
				$sub_r->add_textbox("item_id", INTEGER);
				$sub_r->change_property("item_id", USE_SQL_NULL, false);
			}
		}//*/
		$r_no = array();

		// initialize all fields available for selected table
		$columns = array(); // save information about all used fields here
		$matched_fields = array(); $form_fields = array();
		for ($col = 1; $col <= $total_columns; $col++) {
			$column_name = get_param("column_name_".$col);
			$column_info = $db_columns[$column_name];
			$column_source = get_param("column_source_".$col);
			$column_data = get_param("column_data_".$col);
			$show_field = 0;
			if ($column_source == "ignore") { 
				// check and show all fields used for order form to use them in insert
				if ($table == "orders") {
					$show_param = $param_prefix."show_".$column_name;
					$show_field = get_setting_value($order_settings, $show_param);
				}
				// if field ignored and go to next field
				if ($show_field) {
					$form_fields[$column_name] = 1;
				} else {
					continue;
				}
			} else {
				$matched_fields[$column_name] = true;
			}

			if (isset($column_info["title"])) {
				// new format
				$column_title = $column_info["title"];
				$data_type = $column_info["data_type"];
				$field_type = $column_info["field_type"];
				$field_required = $column_info["required"];
				$sql_null = isset($column_info["sql_null"]) ? $column_info["sql_null"] : true;
				$default_value = isset($column_info["default"]) ? $column_info["default"] : "";
				$field_related_table = isset($column_info["related_table"]) ? $column_info["related_table"] : "";
				$column_date_format = isset($column_info["date_format"]) ? $column_info["date_format"] : "";
			} else {
				// old format
				$column_title = $column_info[0];
				$data_type = $column_info[1];
				$field_type = $column_info[2];
				$field_required = $column_info[3];
				$default_value = isset($column_info[4]) ? $column_info[4] : "";
				$field_related_table = "";
				$column_date_format = "";
			}

			$columns[] = array(
				"name" => $column_name, "source" => $column_source, "data" => $column_data, 
				"title" => $column_title, "data_type" => $data_type, "date_format" => $column_date_format,
				"field_type" => $field_type, "required" => $field_required, "default" => $default_value, "related_table" => $field_related_table,
			);
			// 1 - WHERE_DB_FIELD 2 - USUAL_DB_FIELD 3 - FOREIGN_DB_FIELD 4 - HIDE_DB_FIELD 5 - RELATED_DB_FIELD 6 - CUSTOM_FIELD   
			if ($field_type == WHERE_DB_FIELD) {
				$imp->r->add_where($column_name, $data_type, $column_title);
				$imp->r->change_property($column_name, USE_IN_INSERT, true);
				$is_where = true;
			} elseif ($field_type == USUAL_DB_FIELD) {
				$imp->r->add_textbox($column_name, $data_type, $column_title);
				$imp->r->change_property($column_name, REQUIRED, $field_required); 
			} else if ($field_type == RELATED_DB_FIELD) {
				$imp->r->add_textbox($column_name, $data_type, $column_title);
				$imp->r->change_property($column_name, USE_IN_INSERT, false); 
				$imp->r->change_property($column_name, USE_IN_UPDATE, false); 
			}

			// if it not where and related field and source available we re-initiliaze field
			if ($field_type != WHERE_DB_FIELD && $field_type != RELATED_DB_FIELD && strlen($column_source)) {
				$imp->r->add_textbox($column_name, $data_type, $column_title);
				if ($column_date_format) {
					$imp->r->change_property($column_name, VALUE_MASK, $column_date_format);
				} else if ($data_type == DATE) {
					$imp->r->change_property($column_name, VALUE_MASK, $date_edit_format);
				} elseif ($data_type == DATETIME) {
					$imp->r->change_property($column_name, VALUE_MASK, $datetime_edit_format);
				}
				$imp->r->change_property($column_name, USE_IN_UPDATE, true);
				if ($column_name == "login") {
					// login field should be unique always
					$imp->r->change_property($column_name, UNIQUE, true); 
					$imp->r->change_property($column_name, MIN_LENGTH, 3); 
				}
				if ($column_name == "serial_number") {
					// serial number field should be unique 
					$imp->r->change_property($column_name, UNIQUE, true); 
				}
				// added additional checks for friendly url field
				if ($column_name == "friendly_url") {
					$imp->r->change_property("friendly_url", REGEXP_MASK, FRIENDLY_URL_REGEXP);
					$imp->r->change_property("friendly_url", REGEXP_ERROR, ALPHANUMERIC_ALLOWED_ERROR);
				}
				if (!$sql_null) {
					$imp->r->change_property($column_name, USE_SQL_NULL, false); 
				}
			}

			if ($column_name == "state_id") {
				$imp->r->add_textbox("state_code", TEXT, va_message("STATE_CODE_MSG"));
			} else if ($column_name == "country_id") {
				$imp->r->add_textbox("country_code", TEXT, va_message("COUNTRY_CODE_MSG"));
			} else if ($column_name == "delivery_state_id") {
				$imp->r->add_textbox("delivery_state_code", TEXT, va_message("DELIVERY_STATE_CODE_MSG"));
			} else if ($column_name == "delivery_country_id") {
				$imp->r->add_textbox("delivery_country_code", TEXT, va_message("DELIVERY_COUNTRY_CODE_MSG"));
			}

			if ($table == "orders") {
				// check if order field is required
				$required_param = $param_prefix.$column_name."_required";
				$field_required = get_setting_value($order_settings, $required_param);
				if ($field_required) {
					$imp->r->change_property($column_name, REQUIRED, true); 
				}
				if ($column_name == "order_status") {
					// update status after adding order
					$imp->r->change_property("order_status", USE_IN_INSERT, false); 
					$imp->r->change_property("order_status", USE_IN_UPDATE, false); 
					// add readable name field for order status
					$imp->r->add_textbox("order_status_name", TEXT, "{ORDER_STATUS_MSG} ({NAME_MSG})");
					$imp->r->change_property("order_status_name", USE_IN_INSERT, false); 
					$imp->r->change_property("order_status_name", USE_IN_UPDATE, false); 
				} else if ($column_name == "order_status_name") {
					$imp->r->change_property("order_status_name", USE_IN_INSERT, false); 
					$imp->r->change_property("order_status_name", USE_IN_UPDATE, false); 
					if (!$imp->r->parameter_exists("order_status")) {
						$imp->r->add_textbox("order_status", TEXT, "{ORDER_STATUS_MSG} ({ID_MSG})");
						$imp->r->change_property("order_status", USE_IN_INSERT, false); 
						$imp->r->change_property("order_status", USE_IN_UPDATE, false); 
					}
				} 	

				if ($imp->r->parameter_exists("site_name") && !$imp->r->parameter_exists("site_id")) {
					$imp->r->add_textbox("site_id", INTEGER, va_message("SITE_ID_MSG"));
				}
				if ($imp->r->parameter_exists("user_login") && !$imp->r->parameter_exists("user_id")) {
					$imp->r->add_textbox("user_id", INTEGER, va_message("USER_ID_MSG"));
				}

				if ($column_name == "shipping_type_id" || $column_name == "shipping_type_code") {
					$imp->r->remove_parameter("shipping_type_desc"); // remove to add after both fields in case both fields available
					$imp->r->add_textbox("shipping_type_desc", TEXT, "SHIPPING_DESCRIPTION_MSG");
				} 
				if ($column_name == "shipping_type_desc") {
					if (!$imp->r->parameter_exists("shipping_type_code")) {
						$imp->r->add_textbox("shipping_type_code", TEXT, "SHIPPING_CODE_MSG");
					}
				}
				if ($column_name == "shipping_type_id" || $column_name == "shipping_type_code" || $column_name == "shipping_type_desc") {
					$imp->r->remove_parameter("shipping_cost"); // always remove to add field after all shipping fields 
					$imp->r->add_textbox("shipping_cost", TEXT, "SHIPPING_COST_MSG");
				}

				if ($column_name == "payment_id") {
					// add readable name field for order status
					$imp->r->add_textbox("payment_name", TEXT, "PAYMENT_NAME_MSG");
					$imp->r->change_property("payment_name", USE_IN_INSERT, false); 
					$imp->r->change_property("payment_name", USE_IN_UPDATE, false); 
				} else if ($column_name == "payment_code") {
					if (!$imp->r->parameter_exists("payment_id")) {
						$imp->r->add_textbox("payment_id", TEXT, "PAYMENT_ID_MSG");
						$imp->r->change_property("payment_id", SHOW, false); 
					}
					$imp->r->remove_parameter("payment_name"); // remove to add after both fields in case both fields available
					$imp->r->add_textbox("payment_name", TEXT, "PAYMENT_NAME_MSG");
					$imp->r->change_property("payment_name", USE_IN_INSERT, false); 
					$imp->r->change_property("payment_name", USE_IN_UPDATE, false); 
				} 	

				if (preg_match("/^order_property_/", $column_name)) {
					$imp->r->change_property($column_name, USE_IN_INSERT, false); 
					$imp->r->change_property($column_name, USE_IN_UPDATE, false); 
				}
			}
		}
		// save information for import about all used columns
		$imp->set_columns($table_name, $columns);
		// end main table initializing

		// initialize fields for related table
		$columns = array(); // save information about all used fields here
		$related_matched = array(); $related_preview = array();
		for ($col = 1; $col <= $total_related; $col++) {
			$column_name = get_param("related_name_".$col);
			$column_info = $related_columns[$column_name];
			$column_source = get_param("related_source_".$col);
			$column_data = get_param("related_data_".$col);

			if (isset($column_info["title"])) {
				// new format
				$column_title = $column_info["title"];
				$data_type = $column_info["data_type"];
				$field_type = $column_info["field_type"];
				$field_required = isset($column_info["required"]) ? $column_info["required"] : "";
				$default_value = isset($column_info["default"]) ? $column_info["default"] : "";
				$field_preview = isset($column_info["preview"]) ? $column_info["preview"] : "";
				$field_related_table = isset($column_info["related_table"]) ? $column_info["related_table"] : "";
				$column_date_format = isset($column_info["date_format"]) ? $column_info["date_format"] : "";
			} else {
				// old format
				$column_title = $column_info[0];
				$data_type = $column_info[1];
				$field_type = $column_info[2];
				$field_required = $column_info[3];
				$default_value = isset($column_info[4]) ? $column_info[4] : "";
				$field_preview = false;
				$field_related_table = "";
				$column_date_format = "";
			}

			$columns[] = array(
				"name" => $column_name, "source" => $column_source, "data" => $column_data,
				"title" => $column_title, "data_type" => $data_type, "date_format" => $column_date_format,
				"field_type" => $field_type, "required" => $field_required, "default" => $default_value, "related_table" => $field_related_table,
			);

			if ($column_source == "ignore") { 
				if ($field_preview) {
					$related_preview[$column_name] = 1;
				} else {
					continue;
				}
			} else {
				$related_matched[$column_name] = true;
			}

			// 1 - WHERE_DB_FIELD 2 - USUAL_DB_FIELD 3 - FOREIGN_DB_FIELD 4 - HIDE_DB_FIELD 5 - RELATED_DB_FIELD 6 - CUSTOM_FIELD   

			if ($field_type == WHERE_DB_FIELD) {
				$imp->rr->add_where($column_name, $data_type, $column_title);
				$imp->rr->change_property($column_name, USE_IN_INSERT, true);
				$is_related_where = true;
			} elseif ($field_type == USUAL_DB_FIELD) {
				$imp->rr->add_textbox($column_name, $data_type, $column_title);
				$imp->rr->change_property($column_name, REQUIRED, $field_required); 
			} else if ($field_type == RELATED_DB_FIELD) {
				$imp->rr->add_textbox($column_name, $data_type, $column_title);
				$imp->rr->change_property($column_name, USE_IN_INSERT, false); 
				$imp->rr->change_property($column_name, USE_IN_UPDATE, false); 
			}

			if ($column_date_format) {
				$imp->rr->change_property($column_name, VALUE_MASK, $column_date_format);
			} else if ($data_type == DATE) {
				$imp->rr->change_property($column_name, VALUE_MASK, $date_edit_format);
			} elseif ($data_type == DATETIME) {
				$imp->rr->change_property($column_name, VALUE_MASK, $datetime_edit_format);
			}

			if ($related_table == "orders_items") {
			}
		}
		// save information for import about all used columns
		$imp->set_columns($related_table_name, $columns);
		// end related table initializing

		// add some additional fields to import
		add_imported_fields();

		if ($operation == "preview") {
			// first column for record status
			$t->set_var("field_title", va_message("STATUS_MSG"));
			$t->parse("preview_fields", true);
			// parse titles for main table 
			foreach ($imp->r->parameters as $key => $parameter) {
				$field_title = $parameter[CONTROL_DESC];
				$field_show = $parameter[SHOW];
				if ($field_show) {
					parse_value($field_title);
					$t->set_var("field_title", htmlspecialchars($field_title));
					$t->parse("preview_fields", true);
				}
			}
			// parse titles for related table 
			foreach ($imp->rr->parameters as $key => $parameter) {
				$field_title = $parameter[CONTROL_DESC];
				$field_show = $parameter[SHOW];
				if ($field_show) {
					parse_value($field_title);
					$t->set_var("field_title", htmlspecialchars($field_title));
					$t->parse("preview_fields", true);
				}
			}
		}

		$t->set_var("total_columns", $total_columns);
		$t->set_var("total_related", $total_related);

		if ($rnd == $session_rnd) {
			$records_error = get_session("session_records_error");
			$records_added = get_session("session_records_added");
			$records_updated = get_session("session_records_updated");
			$records_ignored = get_session("session_records_ignored");

			$operation = "result";
		} else {
			$records_error = 0; $records_added = 0; $records_updated = 0; $records_ignored = 0;

			// get max property_id
			$sql  = " SELECT MAX(property_id) FROM " . $table_prefix . "items_properties ";
			$max_property_id = get_db_value($sql);

			// start processing rows
			$is_next_row = false;

			$fp = fopen($csv_file_path, "r");
			if ($fp) {

				$data_file = FilesImportStrategy::getParser($file_type, $csv_file_path, $fp, $delimiter_char);

				// get header data
				$header_data = $data_file->getFieldsHeaders();
				//$csv_data = fgetcsv($fp, 4096, $delimiter_char);
				//$header_data = get_header_data($csv_data);

				// check first row for data
				$imp->set_data($data_file->getFieldsData());
				//$data = fgetcsv($fp, 65536, $delimiter_char);
				//$is_next_row = is_array($data);
			}
			$prev_item_id = 0;

			//while ($is_next_row) {
			$data_index = 0; 
			// start reading data from file
			foreach($imp->data as $k => $data_assoc) {
				$data_index++;
				$t->set_var("cols", "");
				$column_number = 0;

				// always clear all previous data before read and set a new data 
				$imp->r->errors = "";
				$imp->r->empty_values();
				$imp->rr->errors = "";
				$imp->rr->empty_values();
				// clear state and country data
				if ($table == "users" || $table == "orders") {
					$imp->r->set_value("country_id", "");
					$imp->r->set_value("state_id", "");
					$imp->r->set_value("delivery_country_id", "");
					$imp->r->set_value("delivery_state_id", "");
				}

				if ($imp->r->parameter_exists($table_pk)) {
					$imp->r->set_value($table_pk, ""); // always clearing the PK field as it could absent in parameters list but still could be set in different way 
				}
				// set main table values
				foreach ($imp->columns[$table_name] as $column_index => $column_info) {
					$field_type = $column_info["field_type"];
					$column_name = $column_info["name"];
					$column_source = $column_info["source"];
					$column_data = $column_info["data"];

					$column_title = $column_info["title"];
					$data_type = $column_info["data_type"];
					$field_type = $column_info["field_type"];
					$field_required = $column_info["required"];
					$default_value = $column_info["default"];
					if ($column_source == "ignore") { 
						// if field ignored go to next field
						continue;
					}

					if ($field_type > 1 && $field_required == true) {
						if (is_array($default_value) || strlen($default_value)) {
							$imp->r->set_value($column_name, $default_value);
						}
					}

					// set data from the source
					if (strlen($column_source) && $column_source != "ignore") {
						if ($column_source == "default") {
							$field_value = $column_data;
						} else if ($column_source == "csv") {
							//$csv_column = $header_data[$column_data]["id"];
							//$field_value = isset($data[$csv_column]) ? $data[$csv_column] : "";
							$csv_column = $header_data[$column_data]["title"];
							if(function_exists("mb_strtolower")) {
								$csv_column = mb_strtolower($csv_column, "UTF-8");
							} else {
								$csv_column = strtolower($csv_column);
							}
							$csv_column = str_replace(" ", "_", $csv_column);
							$field_value = get_value_by_key($data_assoc, $csv_column);
						} else if ($column_source == "xml") {
							$xml_column = $header_data[$column_data]["title"];
							if(function_exists("mb_strtolower")) {
								$xml_column = mb_strtolower($xml_column, "UTF-8");
							} else {
								$xml_column = strtolower($xml_column);
							}
							$xml_column = str_replace(" ", "_", $xml_column);
							$field_value = get_value_by_key($data_assoc, $xml_column);
						}
						// replace decimal separator
						if (COMMA_DECIMAL && ($data_type == FLOAT || $data_type == NUMBER)) {
							$field_value = str_replace(",", ".", $field_value);
						}
						$imp->r->set_value($column_name, $field_value);
					} 
				}
				// check if from one name field we can populate other
				if ($table == "users" || $table == "orders") {
					if ($imp->r->parameter_exists("name") && $imp->r->parameter_exists("first_name") && $imp->r->parameter_exists("last_name")) {
						$full_name = $imp->r->get_value("name");
						$first_name = $imp->r->get_value("first_name");
						$last_name = $imp->r->get_value("last_name");
						prepare_user_name($full_name, $first_name, $last_name);
						$imp->r->set_value("name", $full_name);
						$imp->r->set_value("first_name", $first_name);
						$imp->r->set_value("last_name", $last_name);
					}
					if ($imp->r->parameter_exists("delivery_name") && $imp->r->parameter_exists("delivery_first_name") && $imp->r->parameter_exists("delivery_last_name")) {
						$full_name = $imp->r->get_value("delivery_name");
						$first_name = $imp->r->get_value("delivery_first_name");
						$last_name = $imp->r->get_value("delivery_last_name");
						prepare_user_name($full_name, $first_name, $last_name);
						$imp->r->set_value("delivery_name", $full_name);
						$imp->r->set_value("delivery_first_name", $first_name);
						$imp->r->set_value("delivery_last_name", $last_name);
					}
				}
				// populate code and id fields if they weren't set
				if ($table == "users" || $table == "orders") {
					check_country_state();
				}

				// set related table data
				if ($import_related_table) {
					foreach ($imp->columns[$related_table_name] as $column_index => $column_info) {
						$field_type = $column_info["field_type"];
						$column_name = $column_info["name"];
						$column_source = $column_info["source"];
						$column_data = $column_info["data"];
				  
						$column_title = $column_info["title"];
						$data_type = $column_info["data_type"];
						$field_type = $column_info["field_type"];
						$field_required = $column_info["required"];
						$default_value = $column_info["default"];

						if ($column_source == "ignore") { 
							// if field ignored go to next field
							continue;
						}
			  
						// set data from the source
						if (strlen($column_source)) {
							if ($column_source == "default") {
								$field_value = $column_data;
							} else if ($column_source == "csv") {
								$csv_column = $header_data[$column_data]["title"];
								if(function_exists("mb_strtolower")) {
									$csv_column = mb_strtolower($csv_column, "UTF-8");
								} else {
									$csv_column = strtolower($csv_column);
								}
								$csv_column = str_replace(" ", "_", $csv_column);
								$field_value = get_value_by_key($data_assoc, $csv_column);
							} else if ($column_source == "xml") {
								$xml_column = $header_data[$column_data]["title"];
								if(function_exists("mb_strtolower")) {
									$xml_column = mb_strtolower($xml_column, "UTF-8");
								} else {
									$xml_column = strtolower($xml_column);
								}
								$xml_column = str_replace(" ", "_", $xml_column);
								$field_value = get_value_by_key($data_assoc, $xml_column);
							}
							// replace decimal separator
							if (COMMA_DECIMAL && ($data_type == FLOAT || $data_type == NUMBER)) {
								$field_value = str_replace(",", ".", $field_value);
							}
							$imp->rr->set_value($column_name, $field_value);
						} 
					}
				} // end related table data set

				$imp->r->errors = "";
				// before validate record we need to check if it exists or not
				$pk_id = ""; 
				$is_exists = false;
				$existed_data = array();
				$where = "";
				$is_where_set = $imp->r->check_where();
				if ($is_where_set) {
					$where = $imp->r->get_where();
				} else {
					if ($table == "items") {
						$item_code = $imp->r->parameter_exists("item_code") ? $imp->r->get_value("item_code") : "";
						$manufacturer_code = $imp->r->parameter_exists("manufacturer_code") ? $imp->r->get_value("manufacturer_code") : "";
						if (($match_item_code || $match_manufacturer_code) && (strlen($item_code) || strlen($manufacturer_code))) {
							$is_where_set = true;
							if ($match_item_code) {
								$where .= ($where) ? " AND " : " WHERE ";
								if (strlen($item_code)) {
									$where .= " item_code=".$db->tosql($item_code, TEXT);
								} else {
									$where .= " (item_code IS NULL OR item_code='') ";
								}
							}
							if ($match_manufacturer_code) {
								$where .= ($where) ? " AND " : " WHERE ";
								if (strlen($manufacturer_code)) {
									$where .= " manufacturer_code=".$db->tosql($manufacturer_code, TEXT);
								} else {
									$where .= " (manufacturer_code IS NULL OR manufacturer_code='') ";
								}
							}
						}
					}
				}
				if ($is_where_set) {
					$imp->r->where_set = true; // this parameter required for unique validation 
					$sql  = " SELECT " . $table_pk . " FROM " . $table_name . "";
					$sql .= $where;
					$db->query($sql);
					if ($db->next_record()) {
						$pk_id = $db->f($table_pk);
						$existed_data = $db->Record;
						$imp->r->set_value($table_pk, $pk_id);
						$is_exists = true;
					}
				}
				// check related record
				$related_pk_id = "";
				$related_exists = false;
				$related_data = array();
				$related_coupons = array();
				if ($import_related_table) {
					$related_where_set = ($is_related_where && $imp->rr->check_where());
					if ($related_where_set) {
						$sql  = " SELECT " . $related_table_pk . " FROM " . $related_table_name . " ";
						$sql .= $imp->rr->get_where();
						$db->query($sql);
						if ($db->next_record()) {
							$related_pk_id = $db->f($related_table_pk);
							$related_data = $db->Record;
							$related_exists = true;
						}
					}
				}
				// check if we can populate other form fields for order record 
				if ($table == "orders") {
					$form_data = array();
					if ($is_exists) {
						$form_data = $existed_data;
					} else if ($imp->r->parameter_exists("user_id") && $imp->r->get_value("user_id")) {
						$user_id = $imp->r->get_value("user_id");
						$sql = " SELECT * FROM ".$table_prefix."users ";
						$sql .= " WHERE user_id=".$db->tosql($user_id, INTEGER);
						$db->query($sql);
						if ($db->next_record()) {
							$form_data = $db->Record;
							$user_type_id = $db->f("user_type_id");
							$imp->r->set_value("user_type_id", $user_type_id);
						} else {
							$error_desc = str_replace("{field_name}", $imp->r->get_property_value("user_id", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
							$imp->r->parameters["user_id"][IS_VALID] = false;
							$imp->r->parameters["user_id"][ERROR_DESC] = $error_desc;
						}
					} else if ($imp->r->parameter_exists("user_login") && $imp->r->get_value("user_login")) {
						$user_login = $imp->r->get_value("user_login");
						$sql  = " SELECT * FROM ".$table_prefix."users ";
						$sql .= " WHERE login=".$db->tosql($user_login, TEXT);
						$db->query($sql);
						if ($db->next_record()) {
							$form_data = $db->Record;
							$user_id = $db->f("user_id");
							$user_type_id = $db->f("user_type_id");
							$imp->r->set_value("user_id", $user_id);
							$imp->r->set_value("user_type_id", $user_type_id);
						} else {
							$error_desc = str_replace("{field_name}", $imp->r->get_property_value("user_login", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
							$imp->r->parameters["user_login"][IS_VALID] = false;
							$imp->r->parameters["user_login"][ERROR_DESC] = $error_desc;
						}
					}
					if (is_array($form_fields) && count($form_fields)) {
						foreach ($form_fields as $param_name => $param_set) {
							if (isset($form_data[$param_name]) && $imp->r->is_empty($param_name)) {
								$imp->r->set_value($param_name, $form_data[$param_name]);
							}
						}
					}

					if ($import_related_table) {
						$product_data = array();
						$item_id = $imp->rr->parameter_exists("item_id") ? $imp->rr->get_value("item_id") : "";
						$item_code = $imp->rr->parameter_exists("item_code") ? $imp->rr->get_value("item_code") : "";
						$manufacturer_code = $imp->rr->parameter_exists("manufacturer_code") ? $imp->rr->get_value("manufacturer_code") : "";
						if ($related_exists) {
							$product_data = $related_data;
							$imp->rr->set_value("item_id", $related_data["item_id"]);
							$imp->rr->set_value("item_type_id", $related_data["item_type_id"]);
						} else if (strlen($item_id) || (($match_item_code || $match_manufacturer_code) && (strlen($item_code) || strlen($manufacturer_code)))) {
							$sql  = " SELECT * FROM ".$table_prefix."items ";
							$where = "";
							if (strlen($item_id)) {
								$where .= " WHERE item_id=".$db->tosql($item_id, INTEGER);
							} else {
								if ($match_item_code) {
									$where .= ($where) ? " AND " : " WHERE ";
									if (strlen($item_code)) {
										$where .= " item_code=".$db->tosql($item_code, TEXT);
									} else {
										$where .= " (item_code IS NULL OR item_code='') ";
									}
								}
								if ($match_manufacturer_code) {
									$where .= ($where) ? " AND " : " WHERE ";
									if (strlen($manufacturer_code)) {
										$where .= " manufacturer_code=".$db->tosql($manufacturer_code, TEXT);
									} else {
										$where .= " (manufacturer_code IS NULL OR manufacturer_code='') ";
									}
								}
							}
							$sql .= $where;
							$db->query($sql);
							if ($db->next_record()) {
								$item_id = $db->f("item_id");
								$item_type_id = $db->f("item_type_id");
								$item_name = $db->f("item_name");
								$parent_item_id = $db->f("parent_item_id");
								$price = $db->f("price");
								$product_data = $db->Record;
								$product_data["real_price"] = $price;
								$imp->rr->set_value("item_id", $item_id);
								$imp->rr->set_value("item_type_id", $item_type_id);
								// check if product has parent which should be added to the name on start
								if ($parent_item_id) {
									$sql  = " SELECT item_name FROM ".$table_prefix."items ";
									$sql .= " WHERE item_id=".$db->tosql($parent_item_id, INTEGER);
									$db->query($sql);
									if ($db->next_record()) {	
										$parent_name = $db->f("item_name");
										$item_name = $parent_name." - ".$item_name;
										$product_data["item_name"] = $item_name;
									}
								}
							} else {
								if (strlen($item_id)) {
									$error_desc = str_replace("{field_name}", $imp->rr->get_property_value("item_id", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
									$imp->rr->parameters["item_id"][IS_VALID] = false;
									$imp->rr->parameters["item_id"][ERROR_DESC] = $error_desc;
								} else {
									if ($match_item_code) {
										$error_desc = str_replace("{field_name}", $imp->rr->get_property_value("item_code", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
										$imp->rr->parameters["item_code"][IS_VALID] = false;
										$imp->rr->parameters["item_code"][ERROR_DESC] = $error_desc;
									}
									if ($manufacturer_code) {
										$error_desc = str_replace("{field_name}", $imp->rr->get_property_value("manufacturer_code", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
										$imp->rr->parameters["manufacturer_code"][IS_VALID] = false;
										$imp->rr->parameters["manufacturer_code"][ERROR_DESC] = $error_desc;
									}
								}
							}
						}
						// check coupons for new record
						$coupons_ids = $imp->rr->parameter_exists("coupons_ids") ? $imp->rr->get_value("coupons_ids") : "";
						$coupons_codes = $imp->rr->parameter_exists("coupons_codes") ? $imp->rr->get_value("coupons_codes") : "";
						if (!$is_exists && (strlen($coupons_ids) || strlen($coupons_codes))) {
							if ($coupons_ids) {
								$sql  = " SELECT * FROM ".$table_prefix."coupons ";
								$sql .= " WHERE coupon_id IN (".$db->tosql($coupons_ids, INTEGER_LIST).") ";
								$db->query($sql);
								if ($db->next_record()) {
									do {
										$coupon_id = $db->f("coupon_id");
										$related_coupons[$coupon_id] = $db->Record;
									} while ($db->next_record());
								} else {
									$error_desc = str_replace("{field_name}", $imp->rr->get_property_value("coupons_ids", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
									$imp->rr->parameters["coupons_ids"][IS_VALID] = false;
									$imp->rr->parameters["coupons_ids"][ERROR_DESC] = $error_desc;
								}
							}
							if ($coupons_codes) {
								$sql  = " SELECT * FROM ".$table_prefix."coupons ";
								$sql .= " WHERE coupon_code IN (".$db->tosql($coupons_codes, TEXT_LIST).") ";
								$db->query($sql);
								if ($db->next_record()) {
									do {
										$coupon_id = $db->f("coupon_id");
										$related_coupons[$coupon_id] = $db->Record;
									} while ($db->next_record());
								} else {
									$error_desc = str_replace("{field_name}", $imp->rr->get_property_value("coupons_codes", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
									$imp->rr->parameters["coupons_codes"][IS_VALID] = false;
									$imp->rr->parameters["coupons_codes"][ERROR_DESC] = $error_desc;
								}
							}
						}
						if (is_array($related_preview) && count($related_preview)) {
							foreach ($related_preview as $param_name => $param_set) {
								if (isset($product_data[$param_name])) {
									$imp->rr->set_value($param_name, $product_data[$param_name]);
								}
							}
						}
						if (count($related_coupons) > 0) {
							// apply coupons to product
							$base_price = $imp->rr->get_value("real_price");
							$price = $imp->rr->get_value("price");
							foreach ($related_coupons as $coupon_id => $coupon_data) {
								$discount_type = $coupon_data["discount_type"];
								$discount_amount = $coupon_data["discount_amount"];

								if ($discount_type == 1 || $discount_type == 3 || $discount_type == 6) {
									$coupon_discount = round(($base_price / 100) * $discount_amount, 2);
								} else {
									$coupon_discount = $discount_amount;
								}
								$price = $price - $coupon_discount;
								// set calculated coupon discount amount to save it in database
								$related_coupons[$coupon_id]["discount_amount"] = $coupon_discount;
							}
							if ($price < 0) { $price = 0; }
							$discount_amount = $base_price - $price;
							// update price and discount amount
							$imp->rr->set_value("price", $price);
							$imp->rr->set_value("discount_amount", $discount_amount);
						}
					}
				}
				// end populating user data from existed order or from users table

				if ($table == "orders") {
					before_order_save();
				}

				// set validation status to false for all fields which we don't need to update 
				if ($is_exists) {
					foreach ($imp->r->parameters as $key => $value) {				
						if ($imp->r->get_property_value($key, USE_IN_UPDATE) === false) {
							$imp->r->change_property($key, VALIDATION, false);
						}
					}
				}
				$imp->r->validate();
				// back validation status to true for next record
				if ($is_exists) {
					foreach ($imp->r->parameters as $key => $value) {				
						if ($imp->r->get_property_value($key, USE_IN_UPDATE) === false) {
							$imp->r->change_property($key, VALIDATION, true);
						}
					}
				}
				$imp->rr->validate();

				if ($operation == "preview") {
					// show status
					if ($imp->r->errors || $imp->rr->errors) {
						$error_desc = $imp->r->errors.$imp->rr->errors;
						$t->set_var("field_class", "fd-status record-errors");
						$t->set_var("field_value", va_message("ERRORS_MSG"));

						$t->set_var("error_desc", $error_desc);
						$t->parse("field_error", false);
					} else if ($is_exists) {
						$t->set_var("field_class", "fd-status record-exists");
						$t->set_var("field_value", va_message("EXISTS_MSG"));
					} else {
						$t->set_var("field_class", "fd-status record-new");
						$t->set_var("field_value", va_message("NEW_MSG"));
					}
					$t->parse("preview_values", true);

					// preview main data 
					foreach ($imp->r->parameters as $key => $parameter) {
						$field_value = $parameter[CONTROL_VALUE];
						$value_type = $parameter[VALUE_TYPE];
						$value_valid = $parameter[IS_VALID];
						$value_error = $parameter[ERROR_DESC];
						$field_show = $parameter[SHOW];
						$field_hide = $parameter[CONTROL_HIDE];

						if ($field_show && !$field_hide) {
	            // convert date type to string readable value
							if ($value_type == DATETIME || $value_type == DATE || $value_type == TIMESTAMP || $value_type == TIME) {
								$field_value = va_date($datetime_show_format, $field_value);
							}
							// shorten value up to 32 symbols
							$field_value = trim(strip_tags($field_value));
							if (strlen($field_value) > 32) {
								$field_value = substr($field_value, 0, 29)."...";
							}
					  
							if ($value_valid) {
								$t->set_var("field_class", "fd-preview");
							} else {
								$t->set_var("field_class", "fd-preview fd-error");
							}
							$t->set_var("field_value", htmlspecialchars($field_value));
					  
							if ($value_valid) {
								$t->set_var("field_error", "");
							} else {
								$t->set_var("error_desc", $value_error);
								$t->parse("field_error", false);
							}
					  
							$t->parse("preview_values", true);
						}
					}
					// end preview main data block

					// preview related data 
					if ($import_related_table) {
						foreach ($imp->rr->parameters as $key => $parameter) {
							$field_value = $parameter[CONTROL_VALUE];
							$value_type = $parameter[VALUE_TYPE];
							$value_valid = $parameter[IS_VALID];
							$value_error = $parameter[ERROR_DESC];
							$field_show = $parameter[SHOW];
							$field_hide = $parameter[CONTROL_HIDE];
				  
							if ($field_show && !$field_hide) {
	              // convert date type to string readable value
								if ($value_type == DATETIME || $value_type == DATE || $value_type == TIMESTAMP || $value_type == TIME) {
									$field_value = va_date($datetime_show_format, $field_value);
								}
								// shorten value up to 32 symbols
								$field_value = trim(strip_tags($field_value));
								if (strlen($field_value) > 32) {
									$field_value = substr($field_value, 0, 29)."...";
								}
						  
								if ($value_valid) {
									$t->set_var("field_class", "fd-preview");
								} else {
									$t->set_var("field_class", "fd-preview fd-error");
								}
								$t->set_var("field_value", htmlspecialchars($field_value));
						  
								if ($value_valid) {
									$t->set_var("field_error", "");
								} else {
									$t->set_var("error_desc", $value_error);
									$t->parse("field_error", false);
								}
						  
								$t->parse("preview_values", true);
							}
						}
					}
					// end preview related data block
					//if ($imp->r->errors || $imp->rr->errors) {
					// show data only for first 100 records
					if ($data_index <= 100) {
						$t->parse("preview_rows", true);
					}
					$t->set_var("preview_values", "");	
				} else {
					// import data to database
					// start insert / update records	
					if ($imp->r->errors || $imp->rr->errors) {
						$records_ignored++;
					} else {
						$new_item_id = $imp->r->parameter_exists($table_pk) ? $imp->r->get_value($table_pk) : "";
			  
						if ($import_related_table && $new_item_id>0 && ($new_item_id == $prev_item_id)) {
							// do nothing
						} elseif ($is_exists) {
							import_friendly_url(); // function to check duplicated friendly urls
							if ($imp->r->update_record()) {
								$records_updated++;
							} else {
								$records_error++;
			  
								$pk_id = $imp->r->get_value($table_pk); 
								$t->set_var("pk_id", htmlspecialchars($pk_id));
								$t->set_var("error_desc", $db->Error);
								$t->parse("records_errors", true);
							}
						} else {
							import_friendly_url(); // function to check duplicated friendly urls
							if ($imp->r->insert_record()) {
								$records_added++;
								if ($table_pk) {
									if ($imp->r->parameter_exists($table_pk) && strlen($imp->r->get_value($table_pk))) {
										$pk_id = $imp->r->get_value($table_pk);
									} else {
										$pk_id = $db->last_insert_id();
										$new_item_id = $pk_id;
										if ($imp->r->parameter_exists($table_pk)) {
											$imp->r->set_value($table_pk, $pk_id); 
										}
									}
								}
							} else {
								$records_error++;
							}
						}
			  
						$new_item_id = $pk_id;
						if ($import_related_table) {
							if ($related_table_name == $table_prefix . "orders_items") {
								$imp->rr->set_value("order_id", $pk_id);
								$imp->rr->set_value("site_id", $imp->r->get_value("site_id"));
								$imp->rr->set_value("user_id", $imp->r->get_value("user_id"));
								$imp->rr->set_value("user_type_id", $imp->r->get_value("user_type_id"));
							}
							if ($related_exists) {
								if ($imp->rr->update_record()) {
									$related_pk_id = $imp->rr->get_value($related_table_pk); 
								}
							} else {
								if ($imp->rr->insert_record()) {
									if ($imp->rr->parameter_exists($related_table_pk) && strlen($imp->rr->get_value($related_table_pk))) {
										$related_pk_id = $imp->rr->get_value($related_table_pk);
									} else {
										$related_pk_id = $db->last_insert_id();
									}
								}
							}
							if ($related_pk_id) {
								// check if there are related fields which we need to update for related table record
								foreach ($imp->columns[$related_table_name] as $column_index => $column_info) {
									$field_type = $column_info["field_type"];
									if ($field_type == RELATED_DB_FIELD) {
										$column_name = $column_info["name"];
										$column_source = $column_info["source"];
										$column_data = $column_info["data"];
										$column_related_table = $column_info["related_table"];
										// based on source get related value
										if ($column_source == "csv" || $column_source == "xml") {
											$source_target = $header_data[$column_data]["title"];
											if(function_exists("mb_strtolower")) {
												$source_target = mb_strtolower($source_target, "UTF-8");
											} else {
												$source_target = strtolower($source_target);
											}
											$source_target = str_replace(" ", "_", $source_target);
											$related_value = get_value_by_key($data_assoc, $source_target);
										} else {
											$related_value = $column_data;
										}
										if ($column_related_table == $table_prefix."orders_items_properties") {
											update_orders_items_properties($related_pk_id, $pk_id, $related_value);
										}
									}
								}
								// end related fields check
								// check if there are coupons available for related record
								if (count($related_coupons)) {
									foreach ($related_coupons as $coupon_id => $coupon_data) {
										$imp->rc->set_value("order_id", $pk_id);
										$imp->rc->set_value("order_item_id", $related_pk_id);
										$imp->rc->set_value("coupon_id", $coupon_id);
										$imp->rc->set_value("coupon_code", $coupon_data["coupon_code"]);
										$imp->rc->set_value("coupon_title", $coupon_data["coupon_title"]);
										$imp->rc->set_value("discount_amount", $coupon_data["discount_amount"]);
										$imp->rc->set_value("discount_tax_amount", $coupon_data["discount_amount"]);
										$imp->rc->set_value("discount_type", $coupon_data["discount_type"]);
										$imp->rc->insert_record();
										// increase coupon uses by one
										$sql  = " UPDATE " . $table_prefix . "coupons ";
										$sql .= " SET coupon_uses=coupon_uses+1 ";
										$sql .= " WHERE coupon_id=" . $db->tosql($coupon_id, INTEGER);
										$db->query($sql);
									}
								}
								// end coupons update for related record
							}

						}


						if (strlen($pk_id)) {							
							if ($table == "orders") {
								//after_orders_save($pk_id);
								recalculate_order($pk_id);
								$order_status = $imp->r->get_value("order_status");
								if ($order_status) {
									update_order_status($pk_id, $order_status, true, "", $status_error);
								}
							}
						}
			  
			  
						// save order products
						/*
						$sub_r_fields     = array();
						$sub_r_fields_max = 0;

						if ($import_related_table) {
							$sub_r->add_textbox($table_pk, INTEGER);
							$sub_r->set_value($table_pk, $new_item_id);
							if ($new_item_id != $prev_item_id) {
								$sql = " DELETE FROM " . $related_table_name . " WHERE " . $table_pk . "=" . $db->tosql($new_item_id, INTEGER);
								$db->query($sql);
								if ($table == "orders") {
									$sql = " DELETE FROM " . $table_prefix . "orders_items_properties WHERE order_id=" . $db->tosql($new_item_id, INTEGER);
									$db->query($sql);
								}
							}
							$sql = "SELECT MAX(" . $related_aliases["id"] . ") FROM " . $related_table_name;
							$max_sub_id = get_db_value($sql);
							if ($related_delimiter_char == "row" || !(isset($sub_r_fields)) || !($sub_r_fields_max>1)) {
								$max_sub_id++;
								$sub_r->set_value($related_aliases["id"], $max_sub_id);		
								if ($related_table == "orders_items") {
									before_orders_items_save();
								}					
								$sub_r->insert_record();
								if ($related_table == "orders_items") {
									after_orders_items_save();
								}													
								for ($ri = 0; $ri < sizeof($sub_related_fields); $ri++) {
									$related_col   = $sub_related_fields[$ri];
									$column_name   = get_param("db_column_" . $related_col);
									$column_value  = get_param("db_value_" . $related_col);
			  
									$related_prop_table = $related_columns[$column_name][4];
									$related_value = isset($data[$related_col - 1]) ? $data[$related_col - 1] : "";
									if ($related_prop_table == $table_prefix . "order_items_properties") {
										update_orders_items_properties($sub_r->get_value($related_aliases["id"]), $new_item_id, $related_value);
									}
								}
							} else {
								// products in one line separated by \t \s etc
								$sub_r_inserted_ids=array();
								for ($sb=0; $sb < $sub_r_fields_max; $sb++) {
									for ($sf=0; $sf < count($sub_r_fields); $sf++) {
										$column_name = $sub_r_fields[$sf]["name"];
										if (isset($sub_r_fields[$sf]["values"][$sb])) {
											$field_value = $sub_r_fields[$sf]["values"][$sb];
										} elseif (isset($sub_r_fields[$sf]["values"][0])) {
											$field_value = $sub_r_fields[$sf]["values"][0];
										} elseif (isset($related_columns[$column_name][4])) {
											$field_value = $related_columns[$column_name][4];
										} else {
											$field_value = NULL;
										}
										if ($related_columns[$column_name][2] != RELATED_DB_FIELD) {
											$sub_r->set_value($column_name, $field_value);
										}
									}
									$max_sub_id++;
									$sub_r->set_value($related_aliases["id"], $max_sub_id);								
									if ($related_table == "orders_items") {
										before_orders_items_save();
									}							
									$sub_r->insert_record();												
									$sub_r_inserted_ids[] = $sub_r->get_value($related_aliases["id"]);
									if ($related_table == "orders_items") {
										after_orders_items_save();
									}		
								}
			  
								for ($ri = 0; $ri < sizeof($sub_related_fields); $ri++) {
									$related_col   = $sub_related_fields[$ri];
									$column_name   = get_param("db_column_" . $related_col);
									$column_value  = get_param("db_value_" . $related_col);
									$related_prop_table = $related_columns[$column_name][4];
									$col_n = str_replace(" ", "_", strtolower($column_name));
									//$related_value = isset($data[$related_col - 1]) ? $data[$related_col - 1] : "";
									$related_value = get_value_by_key($data_assoc, $col_n);
									if ($related_prop_table == $table_prefix . "order_items_properties") {									
										$field_value_exploded = explode($related_delimiter_char, $related_value);								
										for ($sb=0; $sb<$sub_r_fields_max; $sb++){
											if (isset($field_value_exploded[$sb]) && $field_value_exploded[$sb]) {
												update_orders_items_properties($sub_r_inserted_ids[$sb], $new_item_id, $field_value_exploded[$sb]);
											}
										}
									}
								}
							}
						}//*/
			  
						$prev_item_id = $pk_id;
						// end save order products
			  
						// added data to related tables
						if (strlen($pk_id))
						{
							$property_order = 0; $category_column = false;
							// check related fields
							foreach ($imp->columns[$table_name] as $column_index => $column_info) {
								$field_type = $column_info["field_type"];
								if ($field_type == USUAL_DB_FIELD || $field_type == RELATED_DB_FIELD) {
									$column_name = $column_info["name"];
									$column_source = $column_info["source"];
									$column_data = $column_info["data"];
									$related_prop_table = $column_info["related_table"];
									// based on source get related value
									if ($column_source == "csv" || $column_source == "xml") {
										$source_target = $header_data[$column_data]["title"];
										if(function_exists("mb_strtolower")) {
											$source_target = mb_strtolower($source_target, "UTF-8");
										} else {
											$source_target = strtolower($source_target);
										}
										$source_target = str_replace(" ", "_", $source_target);
										$related_value = get_value_by_key($data_assoc, $source_target);
									} else {
										$related_value = $column_data;
									}
									if ($related_prop_table == $table_prefix . "orders_properties") {
										update_orders_properties($pk_id, $related_value, $db_columns[$column_name]);
									} elseif ($related_prop_table == $table_prefix . "users_properties") {
										update_user_properties($pk_id, $related_value);
									} elseif ($related_prop_table == $table_prefix . "items_properties") {
										update_items_properties($pk_id, $related_value, $source_target);
									} elseif ($related_prop_table == $table_prefix . "categories") {
										$category_column = true;
										update_items_categories($pk_id, $related_value);
									} elseif ($related_prop_table == $table_prefix . "manufacturers") {
										update_manufacturer($pk_id, $related_value);
									} elseif ($related_prop_table == $table_prefix . "features") {
										update_items_features($pk_id, $related_value, $source_target);
									} elseif ($related_prop_table == $table_prefix . "items_sites") {
										update_items_sites($pk_id, $related_value);
									}
								}
							}
							// end related fields check
			  
							// added link to category
							if ($table_name == $table_prefix . "items" && !$category_column && !$is_exists) {
								$sql  = " INSERT INTO " . $table_prefix . "items_categories (item_id, category_id) ";
								$sql .= " VALUES (" . $db->tosql($pk_id, INTEGER) . ", " . $db->tosql($category_id, INTEGER) . ") ";
								$db->query($sql);
							}
							if ($table_name == $table_prefix . "newsletters_emails") {
								count_newsletter_emails();
							}
						}
			  
					} // end of importing data
				}
				// check if there are more rows to process
				//$data = fgetcsv($fp, 65536, $delimiter_char);
				//$is_next_row = is_array($data);
			}
			// end reading file data 
			fclose($fp);

			if ($operation == "import") {

				if ($table_name == $table_prefix . "categories") {
					prepare_categories_list();
					update_categories_tree(0, "");
				}
		  
				set_session("session_records_error", $records_error);
				set_session("session_records_added", $records_added);
				set_session("session_records_updated", $records_updated);
				set_session("session_records_ignored", $records_ignored);
		  
				$operation = "result";
		  
				// it's temporary file which can be deleted
				if (preg_match("/tmp_[0-9a-f]{32}\.csv/", $csv_file_path)) {
					@unlink($csv_file_path);
				}
			}

		}

	} 

	if (strlen($errors))
	{
		//$t->set_var("after_upload", "");
		$t->set_var("errors_list", $errors);
		$t->parse("errors", false);
	}
	else
	{
		$t->set_var("errors", "");
	}

	$t->set_var("rnd", va_timestamp());
	$t->set_var("operation", $operation);
	$t->set_var("table", $table);
	$t->set_var("file_type", $file_type);
	$t->set_var("category_id", htmlspecialchars($category_id));
	$t->set_var("newsletter_id", htmlspecialchars($newsletter_id));
	$t->set_var("total_columns", $total_columns);
	$t->set_var("total_related", $total_related);
	$t->set_var("csv_delimiter", htmlspecialchars($csv_delimiter));
	$t->set_var("csv_file_path", htmlspecialchars($csv_file_path));
	$t->set_var("csv_related_delimiter", htmlspecialchars($csv_related_delimiter));
	$t->set_var("import_related_table", htmlspecialchars($import_related_table));
	$t->set_var("properties_number", htmlspecialchars($properties_number));
	$t->set_var("features_number", htmlspecialchars($features_number));
	if ($xml_product_root) {
		$t->set_var("root_elem", htmlspecialchars($xml_product_root));
		$t->parse("xml_root_single", false);
	}

	$t->set_var("import_block", "");
	$t->set_var("upload_block", "");
	$t->set_var("result_block", "");

	if ($operation == "preview") {
		for ($col = 1; $col <= $total_columns; $col++) {
			$column_name = get_param("column_name_".$col);
			$column_source = get_param("column_source_".$col);
			$column_data = get_param("column_data_".$col);

			$t->set_var("column_name_param", "column_name_".$col);
			$t->set_var("column_source_param", "column_source_".$col);
			$t->set_var("column_data_param", "column_data_".$col);

			$t->set_var("column_name", htmlspecialchars($column_name));
			$t->set_var("column_source", htmlspecialchars($column_source));
			$t->set_var("column_data", htmlspecialchars($column_data));
			$t->parse("import_columns", true);
		}
		for ($col = 1; $col <= $total_related; $col++) {
			$related_name = get_param("related_name_".$col);
			$related_source = get_param("related_source_".$col);
			$related_data = get_param("related_data_".$col);

			$t->set_var("column_name_param", "related_name_".$col);
			$t->set_var("column_source_param", "related_source_".$col);
			$t->set_var("column_data_param", "related_data_".$col);

			$t->set_var("column_name", htmlspecialchars($related_name));
			$t->set_var("column_source", htmlspecialchars($related_source));
			$t->set_var("column_data", htmlspecialchars($related_data));
			$t->parse("import_columns", true);
		}

		$t->set_var("import_operation", va_message("PROD_PREVIEW_MSG"));
		$t->parse("preview_block", false);

	} else if ($operation == "import") {
		if($file_type === "xml"){
			$xml_elems = array(array("-1", ""));
			$xml_fields = $data_file->getHeaderVisual();

			if(count($xml_fields) == 1){
				$t->set_var("root_elem", $xml_fields[0][0]);
				$t->parse("xml_root_single", false);
			}
			else{
				foreach($xml_fields as $v){
					$xml_elems[] = $v;
				}
				set_options($xml_elems, "-1", "xml_root");
				$t->parse("xml_root_select", false);
			}
			$encoded_fields = $data_file->getHeadersEncoded();
			$t->set_var("keys_chain", $encoded_fields);
		}

		$t->set_var("import_operation", va_message("MATCH_FIELDS_MSG"));

		$t->parse("import_block", false);
	} elseif ($operation == "result") {
		$records_error = get_session("session_records_error");
		$records_added = get_session("session_records_added");
		$records_updated = get_session("session_records_updated");
		$records_ignored = get_session("session_records_ignored");

		if ($records_error > 0) {
			$t->set_var("records_error", $records_error);
			$t->parse("db_errors", false);
		}
		$t->set_var("records_added", $records_added);
		$t->set_var("records_updated", $records_updated);
		$t->set_var("records_ignored", $records_ignored);

		if ($records_error || $records_ignored) {
			$t->parse("import_errors", false);
		}

		$t->parse("result_block", false);
	} else {
		$operation = "upload";
		$file_types = array(array("csv", "CSV"), array("xml", "XML"));
		set_options($file_types, $file_type, "file_type");
		$delimiters = array(array(",", COMMA_MSG), array("tab", TAB_MSG), array(";", SEMICOLON_MSG), array("|", VERTICAL_BAR_MSG));
		$related_delimiters = array(array("row", ROWS_MSG));
		set_options($delimiters, $csv_delimiter, "delimiter");
		if ($related_table) {
			set_options($related_delimiters, $csv_related_delimiter, "related_delimiter");
			$t->parse("related_delimiter_block", false);
		}

		if ($table == "items") {
			$t->parse("items_settings", false);
		}
		$t->set_var("import_operation", va_message("UPLOAD_FILE_MSG"));

		$t->parse("upload_block", false);
	}

	$t->pparse("main");

	function before_orders_search() {
		global $imp;
		if ($imp->r->parameter_exists("parent_order_id") && $imp->r->is_empty("parent_order_id")) {
			$imp->r->set_value("parent_order_id", 0);
		}
	}

	function before_order_save()
	{
		global $imp, $related_delimiter_char, $site_id;
		global $r_no, $db, $table_prefix, $new_order_status_id;
		global $sites, $order_statuses, $order_shipments, $order_shipments_codes, $order_shipments_descs;
		global $default_payment_id, $payment_systems, $currency;

		if ($imp->r->parameter_exists("parent_order_id") && $imp->r->is_empty("parent_order_id")) {
			$imp->r->set_value("parent_order_id", 0);
		}

		if ($imp->r->is_empty("currency_code") || $imp->r->is_empty("currency_rate")) {
			$imp->r->set_value("currency_code", $currency["code"]);
			$imp->r->set_value("currency_rate", $currency["rate"]);
		}
		if ($imp->r->is_empty("payment_currency_code") || $imp->r->is_empty("payment_currency_rate")) {
			$imp->r->set_value("payment_currency_code", $currency["code"]);
			$imp->r->set_value("payment_currency_rate", $currency["rate"]);
		}
		// check site ID 
		if ($imp->r->is_empty("site_id") && !$imp->r->is_empty("site_name")) {
			$file_value = trim(strtolower($imp->r->get_value("site_name")));
			foreach ($sites as $db_site_id => $site_data) {
				$db_site_name = trim(strtolower($site_data["site_name"]));
				$db_short_name = trim(strtolower($site_data["short_name"]));
				if ($file_value == $db_site_name || $file_value == $db_short_name) {
					$imp->r->set_value("site_id", $db_site_id);
				}
			}
			if ($imp->r->is_empty("site_id")) {
				$error_desc = str_replace("{field_name}", $imp->r->get_property_value("site_name", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
				$imp->r->parameters["site_name"][IS_VALID] = false;
				$imp->r->parameters["site_name"][ERROR_DESC] = $error_desc;
			}
		}
		if ($imp->r->parameter_exists("site_id") && $imp->r->is_empty("site_id") && $imp->r->is_empty("site_name")) {
			$param_site_id = get_session("session_site_id");
			$imp->r->set_value("site_id", $param_site_id);
		}
		// end Site ID checks

		// if ID field for order status is empty try to get id from status name
		if ($imp->r->is_empty("order_status") && !$imp->r->is_empty("order_status_name")) {
			$order_status_name = trim(strtolower($imp->r->get_value("order_status_name")));
			foreach ($order_statuses as $status_id => $status_data) {
				$status_name = trim(strtolower($status_data["status_name"]));
				if ($order_status_name == $status_name) {
					$imp->r->set_value("order_status", $status_id);
				}
			}
			if ($imp->r->is_empty("order_status")) {
				$error_desc = str_replace("{field_name}", $imp->r->get_property_value("order_status_name", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
				$imp->r->parameters["order_status_name"][IS_VALID] = false;
				$imp->r->parameters["order_status_name"][ERROR_DESC] = $error_desc;
			}
		}
		// if status wasn't set use a New status for order
		if ($imp->r->is_empty("order_status") && $imp->r->is_empty("order_status_name") && strlen($new_order_status_id)) {
			$imp->r->set_value("order_status", $new_order_status_id);
		}
		if (!$imp->r->is_empty("order_status")) {
			$order_status = $imp->r->get_value("order_status");
			if (isset($order_statuses[$order_status])) {
				$imp->r->set_value("order_status_name", $order_statuses[$order_status]["status_name"]);
			}
		}
		// check shipping parameters
		$order_shipment = array(); $shipping_type_id = "";
		//if (!$imp->r->is_empty("shipping_type_id")) {
		if ($imp->r->get_value("shipping_type_id")) {
			$shipping_type_id = $imp->r->get_value("shipping_type_id");
			if (isset($order_shipments[$shipping_type_id])) {
				$order_shipment = $order_shipments[$shipping_type_id];
			} else {
				$sql  = " SELECT * FROM ".$table_prefix."shipping_types ";
				$sql .= " WHERE shipping_type_id=".$db->tosql($shipping_type_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$order_shipment = $db->Record;
				}
			}
			if (count($order_shipment)) {
				if ($imp->r->is_empty("shipping_type_code")) {
					$imp->r->set_value("shipping_type_code", $order_shipment["shipping_type_code"]);
				} else {
					// check if correct code is used
					$db_value = trim(strtolower($order_shipment["shipping_type_code"]));
					$file_value = trim(strtolower($imp->r->get_value("shipping_type_code")));
					if ($db_value != $file_value) {
						$error_desc = str_replace("{field_name}", $imp->r->get_property_value("shipping_type_code", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
						$imp->r->parameters["shipping_type_code"][IS_VALID] = false;
						$imp->r->parameters["shipping_type_code"][ERROR_DESC] = $error_desc;
					}
				}
				if ($imp->r->is_empty("shipping_type_desc")) {
					$imp->r->set_value("shipping_type_desc", $order_shipment["shipping_type_desc"]);
				}
			} else {
				$error_desc = str_replace("{field_name}", $imp->r->get_property_value("shipping_type_id", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
				$imp->r->parameters["shipping_type_id"][IS_VALID] = false;
				$imp->r->parameters["shipping_type_id"][ERROR_DESC] = $error_desc;
			}
			$order_shipments[$shipping_type_id] = $order_shipment;
		}
		// check shipping ID by code
		if ($imp->r->is_empty("shipping_type_id") && !$imp->r->is_empty("shipping_type_code")) {
			$shipping_type_code = trim(strtolower($imp->r->get_value("shipping_type_code")));
			if (isset($order_shipments_codes[$shipping_type_code])) {
				$shipping_type_id = $order_shipments_codes[$shipping_type_code];
				if ($shipping_type_id) {
					$order_shipment = $order_shipments[$shipping_type_id];
				}
			} else {
				$sql  = " SELECT * FROM ".$table_prefix."shipping_types ";
				$sql .= " WHERE shipping_type_code=".$db->tosql($shipping_type_code, TEXT);
				$db->query($sql);
				if ($db->next_record()) {
					$shipping_type_id = $db->f("shipping_type_id");
					$order_shipment = $db->Record;
				}
			}
			if (count($order_shipment)) {
				$imp->r->set_value("shipping_type_id", $order_shipment["shipping_type_id"]);
				if ($imp->r->is_empty("shipping_type_desc")) {
					$imp->r->set_value("shipping_type_desc", $order_shipment["shipping_type_desc"]);
				}
			}
			$order_shipments[$shipping_type_id] = $order_shipment;
			$order_shipments_codes[$shipping_type_code] = $shipping_type_id;
		}
		// check shipping ID by desc 
		if ($imp->r->is_empty("shipping_type_id") && !$imp->r->is_empty("shipping_type_desc")) {
			$shipping_type_desc = trim(strtolower($imp->r->get_value("shipping_type_desc")));
			if (isset($order_shipments_descs[$shipping_type_desc])) {
				$shipping_type_id = $order_shipments_descs[$shipping_type_desc];
				if ($shipping_type_id) {
					$order_shipment = $order_shipments[$shipping_type_id];
				}
			} else {
				$sql  = " SELECT st.* FROM ".$table_prefix."shipping_types st ";
				$sql .= " INNER JOIN ".$table_prefix."shipping_modules sm ON st.shipping_module_id=sm.shipping_module_id";
				$sql .= " WHERE st.shipping_type_desc=".$db->tosql($shipping_type_desc, TEXT);
				$sql .= " AND st.is_active=1 AND sm.is_active=1 ";
				$db->query($sql);
				if ($db->next_record()) {
					$shipping_type_id = $db->f("shipping_type_id");
					$order_shipment = $db->Record;
				}
			}
			if (count($order_shipment)) {
				$imp->r->set_value("shipping_type_id", $order_shipment["shipping_type_id"]);
				$imp->r->set_value("shipping_type_code", $order_shipment["shipping_type_code"]);
			}
			$order_shipments[$shipping_type_id] = $order_shipment;
			$order_shipments_descs[$shipping_type_desc] = $shipping_type_id;
		}
		// calculate shipping cost if it wasn't set
		if ($imp->r->is_empty("shipping_cost") && count($order_shipment)) {
			$cost_per_order = doubleval($order_shipment["cost_per_order"]);
			$cost_per_product = doubleval($order_shipment["cost_per_product"]);
			$cost_per_weight = doubleval($order_shipment["cost_per_weight"]);
			$item_quantity = 0; $item_weight = 0;
			if ($imp->rr->parameter_exists("quantity")) {
				$item_quantity = intval($imp->rr->get_value("quantity"));
			}
			if ($imp->rr->parameter_exists("weight")) {
				$item_weight = doubleval($imp->rr->get_value("weight"));
			}

			$shipping_cost = $cost_per_order + ($cost_per_product*$item_quantity) + ($cost_per_weight*$item_weight);
			$imp->r->set_value("shipping_cost", $shipping_cost);
		}

		// check if correct payment ID was used
		if (!$imp->r->is_empty("payment_id")) {
			$payment_id = $imp->r->get_value("payment_id");
			if (isset($payment_systems[$payment_id])) {
				$payment_name = $payment_systems[$payment_id]["payment_name"];
				$imp->r->set_value("payment_name", $payment_name);
			} else {
  			$error_desc = str_replace("{field_name}", $imp->r->get_property_value("payment_id", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
				$imp->r->parameters["payment_id"][IS_VALID] = false;
				$imp->r->parameters["payment_id"][ERROR_DESC] = $error_desc;
			}
		}
		// if payment id field is empty try to get id from payment code or payment name
		if ($imp->r->is_empty("payment_id") && !$imp->r->is_empty("payment_code")) {
			$imported_code = trim(strtolower($imp->r->get_value("payment_code")));
			foreach ($payment_systems as $payment_id => $payment_data) {
				$payment_code = trim(strtolower($payment_data["payment_code"]));
				if ($imported_code == $payment_code) {
					$imp->r->set_value("payment_id", $payment_id);
					$imp->r->set_value("payment_name", $payment_data["payment_name"]);
				}
			}
			if ($imp->r->is_empty("payment_id")) {
  			$error_desc = str_replace("{field_name}", $imp->r->get_property_value("payment_code", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
				$imp->r->parameters["payment_code"][IS_VALID] = false;
				$imp->r->parameters["payment_code"][ERROR_DESC] = $error_desc;
			}
		}
		if ($imp->r->is_empty("payment_id") && !$imp->r->is_empty("payment_name")) {
			$imported_name = trim(strtolower($imp->r->get_value("payment_name")));
			foreach ($payment_systems as $payment_id => $payment_data) {
				$payment_name = trim(strtolower($payment_data["payment_name"]));
				if ($imported_name == $payment_name) {
					$imp->r->set_value("payment_id", $payment_id);
				}
			}
			if ($imp->r->is_empty("payment_id")) {
  			$error_desc = str_replace("{field_name}", $imp->r->get_property_value("payment_name", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
				$imp->r->parameters["payment_name"][IS_VALID] = false;
				$imp->r->parameters["payment_name"][ERROR_DESC] = $error_desc;
			}
		}
		if ($imp->r->is_empty("payment_id") && $imp->r->is_empty("payment_code") && $imp->r->is_empty("payment_name") && strlen($default_payment_id)) {
			$imp->r->set_value("payment_id", $default_payment_id);
			$payment_name = $payment_systems[$default_payment_id]["payment_name"];
			$imp->r->set_value("payment_name", $payment_name);
		}
		if ($imp->r->is_empty("payment_id")) {
			$error_desc = str_replace("{field_name}", va_message("PAYMENT_GATEWAY_MSG"), va_message("REQUIRED_MESSAGE"));
			$imp->r->parameters["payment_id"][IS_VALID] = false;
			$imp->r->parameters["payment_id"][ERROR_DESC] = $error_desc;
		}


		if (isset($imp->rr)) {
			if ($imp->rr->parameter_exists("quantity") && $imp->rr->is_empty("quantity")) {
				$imp->rr->set_value("quantity", 1);
			}
			if ($imp->rr->parameter_exists("item_type_id") && $imp->rr->is_empty("item_type_id")) {
				$imp->rr->set_value("item_type_id", 1);
			}
		}
		// parse dates, modified in excel
		if (!$imp->r->is_empty("order_placed_date")) {
			$order_placed_date = $imp->r->get_value("order_placed_date");
			$order_placed_date = before_order_save_check_date($order_placed_date);
			$imp->r->set_value("order_placed_date", $order_placed_date);
		} else {
			$imp->r->set_value("order_placed_date", va_time());
		}

		if (!$imp->r->is_empty("modified_date")) {
			$modified_date = $imp->r->get_value("modified_date");
			$modified_date = before_order_save_check_date($modified_date);
			$imp->r->set_value("modified_date", $modified_date);
		} else {
			$imp->r->set_value("modified_date", va_time());
		}

		if (!$imp->r->is_empty("shipping_expecting_date")) {
			$shipping_expecting_date = $imp->r->get_value("shipping_expecting_date");
			$shipping_expecting_date = before_order_save_check_date($shipping_expecting_date);
			$imp->r->set_value("shipping_expecting_date", $shipping_expecting_date);
		}
	}

	function check_country_state()
	{
		global $imp, $countries, $delivery_countries, $states;

		$country_id = $imp->r->get_value("country_id");
		if ($country_id) {
			// check if country id is correct and show error if not
			if (!isset($countries[$country_id]))  {
				$error_desc = str_replace("{field_name}", $imp->r->get_property_value("country_id", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
				$imp->r->parameters["country_id"][IS_VALID] = false;
				$imp->r->parameters["country_id"][ERROR_DESC] = $error_desc;
			}
		} else {
			if ($imp->r->parameter_exists("country_code") && !$imp->r->is_empty("country_code")) {
				$country_code = trim(strtoupper($imp->r->get_value("country_code")));
				foreach ($countries as $check_country_id => $country_data)  {
					if (strtoupper($country_data["country_code"]) == $country_code || 
						strtoupper($country_data["country_code_alpha3"]) == $country_code ||
						strtoupper($country_data["country_name"]) == $country_code) {
						$country_id = $check_country_id;
						break;
					}
				}
			}
			if (!$country_id && $imp->r->parameter_exists("country_name") && !$imp->r->is_empty("country_name")) {
				$country_name = trim(strtoupper($imp->r->get_value("country_name")));
				foreach ($countries as $check_country_id => $country_data)  {
					if (strtoupper($country_data["country_code"]) == $country_name || 
						strtoupper($country_data["country_code_alpha3"]) == $country_name ||
						strtoupper($country_data["country_name"]) == $country_name) {
						$country_id = $check_country_id;
						break;
					}
				}
			}
			if ($country_id) {
				$imp->r->set_value("country_id", $country_id);
			} else if ($imp->r->parameter_exists("country_code") && !$imp->r->is_empty("country_code")) {
				$error_desc = str_replace("{field_name}", $imp->r->get_property_value("country_code", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
				$imp->r->parameters["country_code"][IS_VALID] = false;
				$imp->r->parameters["country_code"][ERROR_DESC] = $error_desc;
			} else if ($imp->r->parameter_exists("country_name") && !$imp->r->is_empty("country_name")) {
				$error_desc = str_replace("{field_name}", $imp->r->get_property_value("country_name", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
				$imp->r->parameters["country_name"][IS_VALID] = false;
				$imp->r->parameters["country_name"][ERROR_DESC] = $error_desc;
			}
		}
		if ($country_id && isset($countries[$country_id]))  {
			if ($imp->r->parameter_exists("country_code")) {
				$imp->r->set_value("country_code", $countries[$country_id]["country_code"]);
			}
			if ($imp->r->parameter_exists("country_name")) {
				$imp->r->set_value("country_name", $countries[$country_id]["country_name"]);
			}
		}

		$state_id = $imp->r->get_value("state_id");
		if ($state_id) {
			// check if country id is correct and show error if not
			if (!isset($states[$state_id]) || $states[$state_id]["country_id"] != $country_id)  {
				$error_desc = str_replace("{field_name}", $imp->r->get_property_value("state_id", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
				$imp->r->parameters["state_id"][IS_VALID] = false;
				$imp->r->parameters["state_id"][ERROR_DESC] = $error_desc;
			}
		} else {
			if ($imp->r->parameter_exists("state_code") && !$imp->r->is_empty("state_code")) {
				$state_code = trim(strtoupper($imp->r->get_value("state_code")));
				foreach ($states as $check_state_id => $state_data)  {
					if (strtoupper($state_data["country_id"]) == $country_id &&
						(strtoupper($state_data["state_code"]) == $state_code || 
						strtoupper($state_data["state_name"]) == $state_code)) {
						$state_id = $check_state_id;
						break;
					}
				}
			}
			if (!$state_id && $imp->r->parameter_exists("state_name") && !$imp->r->is_empty("state_name")) {
				$state_name = trim(strtoupper($imp->r->get_value("state_name")));
				foreach ($states as $check_state_id => $state_data)  {
					if (strtoupper($state_data["country_id"]) == $country_id &&
						(strtoupper($state_data["state_name"]) == $state_name ||
						strtoupper($state_data["state_code"]) == $state_name)) {
						$state_id = $check_state_id;
						break;
					}
				}
			}
			if ($state_id) {
				$imp->r->set_value("state_id", $state_id);
			} else if ($imp->r->parameter_exists("state_code") && !$imp->r->is_empty("state_code")) {
				$error_desc = str_replace("{field_name}", $imp->r->get_property_value("state_code", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
				$imp->r->parameters["state_code"][IS_VALID] = false;
				$imp->r->parameters["state_code"][ERROR_DESC] = $error_desc;
			} else if ($imp->r->parameter_exists("state_name") && !$imp->r->is_empty("state_name")) {
				$error_desc = str_replace("{field_name}", $imp->r->get_property_value("state_name", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
				$imp->r->parameters["state_name"][IS_VALID] = false;
				$imp->r->parameters["state_name"][ERROR_DESC] = $error_desc;
			}
		}
		if ($state_id && isset($states[$state_id]))  {
			if ($imp->r->parameter_exists("state_code")) {
				$imp->r->set_value("state_code", $states[$state_id]["state_code"]);
			}
			if ($imp->r->parameter_exists("state_name")) {
				$imp->r->set_value("state_name", $states[$state_id]["state_name"]);
			}
		}

		$delivery_country_id = $imp->r->get_value("delivery_country_id");
		if ($delivery_country_id) {
			// check if delivery_country id is correct 
			if (!isset($countries[$delivery_country_id]))  {
				$error_desc = str_replace("{field_name}", $imp->r->get_property_value("delivery_country_id", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
				$imp->r->parameters["delivery_country_id"][IS_VALID] = false;
				$imp->r->parameters["delivery_country_id"][ERROR_DESC] = $error_desc;
			}
		} else {
			if ($imp->r->parameter_exists("delivery_country_code") && !$imp->r->is_empty("delivery_country_code")) {
				$delivery_country_code = trim(strtoupper($imp->r->get_value("delivery_country_code")));
				foreach ($countries as $check_country_id => $country_data)  {
					if (strtoupper($country_data["country_code"]) == $delivery_country_code || 
						strtoupper($country_data["country_code_alpha3"]) == $delivery_country_code ||
						strtoupper($country_data["country_name"]) == $delivery_country_code) {
						$delivery_country_id = $check_country_id;
						break;
					}
				}
			}
			if (!$delivery_country_id && $imp->r->parameter_exists("delivery_country_name") && !$imp->r->is_empty("delivery_country_name")) {
				$delivery_country_name = trim(strtoupper($imp->r->get_value("delivery_country_name")));
				foreach ($countries as $check_country_id => $country_data)  {
					if (strtoupper($country_data["country_code"]) == $delivery_country_name || 
						strtoupper($country_data["country_code_alpha3"]) == $delivery_country_name ||
						strtoupper($country_data["country_name"]) == $delivery_country_name) {
						$delivery_country_id = $check_country_id;
						break;
					}
				}
			}
			if ($delivery_country_id) {
				$imp->r->set_value("delivery_country_id", $delivery_country_id);
			} else if ($imp->r->parameter_exists("delivery_country_code") && !$imp->r->is_empty("delivery_country_code")) {
				$error_desc = str_replace("{field_name}", $imp->r->get_property_value("delivery_country_code", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
				$imp->r->parameters["delivery_country_code"][IS_VALID] = false;
				$imp->r->parameters["delivery_country_code"][ERROR_DESC] = $error_desc;
			} else if ($imp->r->parameter_exists("delivery_country_name") && !$imp->r->is_empty("delivery_country_name")) {
				$error_desc = str_replace("{field_name}", $imp->r->get_property_value("delivery_country_name", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
				$imp->r->parameters["delivery_country_name"][IS_VALID] = false;
				$imp->r->parameters["delivery_country_name"][ERROR_DESC] = $error_desc;
			}
		}
		if ($delivery_country_id && isset($countries[$delivery_country_id]))  {
			if ($imp->r->parameter_exists("delivery_country_code")) {
				$imp->r->set_value("delivery_country_code", $countries[$delivery_country_id]["country_code"]);
			}
			if ($imp->r->parameter_exists("delivery_country_name")) {
				$imp->r->set_value("delivery_country_name", $countries[$delivery_country_id]["country_name"]);
			}
		} 

		$delivery_state_id = $imp->r->get_value("delivery_state_id");
		if ($delivery_state_id) {
			// check if country id is correct and show error if not
			if (!isset($states[$delivery_state_id]) || $states[$delivery_state_id]["country_id"] != $delivery_country_id)  {
				$error_desc = str_replace("{field_name}", $imp->r->get_property_value("delivery_state_id", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
				$imp->r->parameters["delivery_state_id"][IS_VALID] = false;
				$imp->r->parameters["delivery_state_id"][ERROR_DESC] = $error_desc;
			}
		} else {
			if ($imp->r->parameter_exists("delivery_state_code") && !$imp->r->is_empty("delivery_state_code")) {
				$delivery_state_code = trim(strtoupper($imp->r->get_value("delivery_state_code")));
				foreach ($states as $check_state_id => $state_data)  {
					if (strtoupper($state_data["country_id"]) == $delivery_country_id &&
						(strtoupper($state_data["state_code"]) == $delivery_state_code || 
						strtoupper($state_data["state_name"]) == $delivery_state_code)) {
						$delivery_state_id = $check_state_id;
						break;
					}
				}
			}
			if (!$delivery_state_id && $imp->r->parameter_exists("delivery_state_name") && !$imp->r->is_empty("delivery_state_name")) {
				$delivery_state_name = trim(strtoupper($imp->r->get_value("delivery_state_name")));
				foreach ($states as $check_state_id => $state_data)  {
					if (strtoupper($state_data["country_id"]) == $delivery_country_id &&
						(strtoupper($state_data["state_name"]) == $delivery_state_name ||
						strtoupper($state_data["state_code"]) == $delivery_state_name)) {
						$delivery_state_id = $check_state_id;
						break;
					}
				}
			}
			if ($delivery_state_id) {
				$imp->r->set_value("delivery_state_id", $delivery_state_id);
			} else if ($imp->r->parameter_exists("delivery_state_code") && !$imp->r->is_empty("delivery_state_code")) {
				$error_desc = str_replace("{field_name}", $imp->r->get_property_value("delivery_state_code", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
				$imp->r->parameters["delivery_state_code"][IS_VALID] = false;
				$imp->r->parameters["delivery_state_code"][ERROR_DESC] = $error_desc;
			} else if ($imp->r->parameter_exists("delivery_state_name") && !$imp->r->is_empty("delivery_state_name")) {
				$error_desc = str_replace("{field_name}", $imp->r->get_property_value("delivery_state_name", CONTROL_DESC), va_message("INCORRECT_VALUE_MESSAGE"));
				$imp->r->parameters["delivery_state_name"][IS_VALID] = false;
				$imp->r->parameters["delivery_state_name"][ERROR_DESC] = $error_desc;
			}
		}
		if ($delivery_state_id && isset($states[$delivery_state_id]))  {
			if ($imp->r->parameter_exists("delivery_state_code")) {
				$imp->r->set_value("delivery_state_code", $states[$delivery_state_id]["state_code"]);
			}
			if ($imp->r->parameter_exists("delivery_state_name")) {
				$imp->r->set_value("delivery_state_name", $states[$delivery_state_id]["state_name"]);
			}
		}

	}

	function before_order_save_check_date($date) {
		if (is_string($date)) {
			$timestamp = strtotime($date);
			if ($timestamp) {
				return date("Y-m-d H:i:s", $timestamp);
			} else {
				return $date;
			}
		} else {
			return $date;
		}
	}

	function before_orders_items_save()
	{
		global $sub_r, $db, $table_prefix;
		$item_id = $sub_r->get_value("item_id");
		$quantity = $sub_r->get_value("quantity");

		if (!$item_id) {
			if (!$sub_r->is_empty("item_code")) {
				$item_code = $sub_r->get_value("item_code");
				$sql  = " SELECT item_id FROM " . $table_prefix . "items ";
				$sql .= " WHERE item_code=" . $db->tosql($item_code, TEXT, true, false);
				$item_id = get_db_value($sql);
			}
			if (!$item_id && !$sub_r->is_empty("manufacturer_code")) {
				$manufacturer_code = $sub_r->get_value("manufacturer_code");
				$sql  = " SELECT item_id FROM " . $table_prefix . "items ";
				$sql .= " WHERE manufacturer_code=" . $db->tosql($manufacturer_code, TEXT, true, false);
				$item_id = get_db_value($sql);
			}
			$sub_r->set_value("item_id", $item_id);
		}
	}

	function after_orders_items_save()
	{
		global $sub_r, $db, $table_prefix;
		$item_id = $sub_r->get_value("item_id");
		$quantity = $sub_r->get_value("quantity");

		if ($quantity && $item_id) {
			$sql  = " SELECT stock_level FROM " . $table_prefix . "items ";
			$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER, true, false);
			$old_stock = get_db_value($sql);

			$sql  = " UPDATE " . $table_prefix . "items SET stock_level=" . $db->tosql($old_stock - $quantity, INTEGER, true, false);
			$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER, true, false);
			$db->query($sql);
		}

		$sub_r->set_value("item_id", 0);
	}

	function update_orders_items_properties($order_item_id, $order_id, $related_value) 
	{
		global $db, $table_prefix;

		$related_value = trim($related_value);
		if (!$related_value) { return; }

		// prepare properties list
		$item_properties = array(); $property_index = 0;
		$delimited_values = preg_split("/[\n\r;]+/", $related_value);
		foreach ($delimited_values as $index => $delimited_value) {
			$property_data = explode(":", $delimited_value, 2);
			if (count($property_data) == 2) {
				$item_properties[$property_index] = array("name" => $property_data[0], "value" =>  $property_data[1]);
				$property_index++;
			} else {
				if ($property_index > 0) {
					// add property data to the previous property value
					$item_properties[($property_index-1)]["value"] .= "; ".$property_data[0];
				} else {
					$item_properties[$property_index] = array("name" => $property_data[0], "value" =>  "");
					$property_index++;
				}
			}
		}

		foreach ($item_properties as $property_data) {
      $property_name = $property_data["name"];
      $property_value = $property_data["value"];
			$property_id         = 0;
			$property_values_ids = array();	
			$additional_price    = 0;
			$additional_weight   = 0;
			
			$sql  = " INSERT INTO " . $table_prefix . "orders_items_properties ";
			$sql .= " (order_id, order_item_id, property_id, property_values_ids, property_name, property_value, additional_price, additional_weight) ";
			$sql .= " VALUES (" . $db->tosql($order_id, INTEGER) . ", " ;
			$sql .= $db->tosql($order_item_id, INTEGER) . ", " ;
			$sql .= $db->tosql($property_id, INTEGER, true, false) . ", " ;
			$sql .= $db->tosql(implode(',', $property_values_ids), TEXT, true, false) . ", " ;
			$sql .= $db->tosql($property_name, TEXT) . ", " ;
			$sql .= $db->tosql($property_value, TEXT) . ", ";
			$sql .= $db->tosql($additional_price,  FLOAT) . ", ";
			$sql .= $db->tosql($additional_weight, FLOAT) . ") ";
			$db->query($sql);
		}

		/*
		$sql  = " SELECT property_id, additional_price FROM " . $table_prefix . "items_properties WHERE property_name=" . $db->tosql($property_name, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$property_id      = $db->f('property_id');
			$additional_price = $db->f('additional_price');
			$tmp = explode (",", $related_value);
			foreach ($tmp AS $property_value) {
				$property_value = trim($property_value);
				$sql  = " SELECT item_property_id, additional_price, additional_weight FROM " . $table_prefix . "items_properties_values ";
				$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER, true, false). " AND property_value=" . $db->tosql($property_value, TEXT);
				$db->query($sql);
				if ($db->next_record()) {
					$property_values_ids[] = $db->f('item_property_id');
					$additional_price  += $db->f('additional_price');
					$additional_weight += $db->f('additional_weight');				
				}
			}
		}	//*/

	}

	function after_orders_save($order_id) {
		global $db, $table_prefix;
		$sql  = " DELETE FROM " . $table_prefix . "orders_properties WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);		
	}

	function update_orders_properties($order_id, $related_value, $property_data) 
	{
		global $db, $table_prefix, $column_name;

		$related_value = trim($related_value);
		if (!strlen($related_value)) { return; }

		$property_id = $property_data["property_id"];
		$property_order = $property_data["property_order"];
		$property_code = $property_data["property_code"];
		$property_name = $property_data["property_name"];
		$property_type = $property_data["property_type"];
		$control_type = $property_data["control_type"];
		$tax_free = $property_data["tax_free"];

		$property_values = explode(";", $related_value);
		foreach ($property_values as $property_value) {
			// before adding a new order property check if it was probably already added to exclude double adding of the same properties when order has more than one product
			$sql  = " SELECT order_property_id FROM ".$table_prefix."orders_properties ";
			$sql .= " WHERE order_id=".$db->tosql($order_id, INTEGER);
			$sql .= " AND property_id=".$db->tosql($property_id, INTEGER);
			$sql .= " AND property_value=".$db->tosql($property_value, TEXT);
			$db->query($sql);
			if (!$db->next_record()) {
				$property_price  = 0;
				$property_weight = 0;
				$actual_weight = 0;
				$property_value_id = "";
				$property_value = trim($property_value);
				if ($control_type == "CHECKBOXLIST" ||  $control_type == "RADIOBUTTON" || $control_type == "LISTBOX") {
					$sql  = " SELECT pv.property_value_id, pv.property_price, pv.property_weight, pv.actual_weight ";
					$sql .= " FROM " . $table_prefix . "order_custom_values pv  ";
					$sql .= " WHERE pv.property_id=" . $db->tosql($property_id, INTEGER);
					$sql .= " AND (pv.property_value=" . $db->tosql($property_value , TEXT);
					$sql .= " OR  pv.property_value_id=" . $db->tosql($property_value , TEXT) . ")";
					$db->query($sql);
					if ($db->next_record()) {
						$property_price    = $db->f("property_price");
						$property_weight   = $db->f("property_weight");
						$actual_weight     = $db->f("actual_weight");
						$property_value_id = $db->f("property_value_id");
					}
				}
		  
				$sql  = " INSERT INTO " . $table_prefix . "orders_properties ";
				$sql .= " (order_id, property_id, property_order, property_type, property_code, property_name, property_value, property_value_id, property_price, property_weight, actual_weight, tax_free) ";
				$sql .= " VALUES (";
				$sql .= $db->tosql($order_id, INTEGER) . ", " ;
				$sql .= $db->tosql($property_id, INTEGER) . ", " ;
				$sql .= $db->tosql($property_order, INTEGER, true, false) . ", " ;
				$sql .= $db->tosql($property_type, INTEGER, true, false) . ", " ;
				$sql .= $db->tosql($property_code, TEXT) . ", " ;
				$sql .= $db->tosql($property_name, TEXT) . ", " ;
				$sql .= $db->tosql($related_value, TEXT) . ", ";
				$sql .= $db->tosql($property_value_id, INTEGER) . ", ";
				$sql .= $db->tosql($property_price,  FLOAT, true, false) . ", ";
				$sql .= $db->tosql($property_weight, FLOAT, true, false) . ", " ;
				$sql .= $db->tosql($actual_weight, FLOAT, true, false) . ", " ;
				$sql .= $db->tosql($tax_free, INTEGER, true, false) . ") ";
				$db->query($sql);
			}
		}
	}

	function update_user_properties($user_id, $related_value) 
	{
		global $db, $table_prefix, $column_name;
		
		if (preg_match("/^user_property_(\d+)$/", $column_name, $matches)) {
			$property_id = $matches[1];
		} else {
			return;
		}

		// check control type first
		$control_type = "";
		$sql  = " SELECT control_type FROM " . $table_prefix . "user_profile_properties ";
		$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$control_type = $db->f("control_type");
		}

		// get property values
		$property_values = array();
		if($control_type == "CHECKBOXLIST" ||  $control_type == "RADIOBUTTON" || $control_type == "LISTBOX") {
			$check_values = explode (";", $related_value);
			foreach ($check_values as $key => $property_value) {
				$property_value = trim($property_value);
				$sql  = " SELECT property_value_id FROM " . $table_prefix . "user_profile_values ";
				$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
				$sql .= " AND property_value=" . $db->tosql($property_value, TEXT);
				$db->query($sql);
				if ($db->next_record()) {
					$property_values[] = $db->f("property_value_id");
				}
			}
		} else if (strlen($related_value)) {
			$property_values[] = $related_value;
		}

		// clear old values 
		$sql  = " DELETE FROM " . $table_prefix . "users_properties ";
		$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
		$sql .= " AND property_id=" . $db->tosql($property_id, INTEGER);
		$db->query($sql);

		// add new values
		foreach ($property_values as $property_value) {
			$sql  = " INSERT INTO " . $table_prefix . "users_properties ";
			$sql .= " (user_id, property_id, property_value) ";
			$sql .= " VALUES (" . $db->tosql($user_id, INTEGER) . ", " ;
			$sql .= $db->tosql($property_id, INTEGER) . ", " ;
			$sql .= $db->tosql($property_value, TEXT) . ") " ;
			$db->query($sql);
		}
	}


	function update_items_categories($item_id, $categories_info)
	{
		global $db, $table_prefix;

		$categories_info = trim($categories_info);
		$sql = " DELETE FROM " . $table_prefix . "items_categories WHERE item_id=" . $db->tosql($item_id, INTEGER);
		$db->query($sql);

		$categories_list = explode(";", $categories_info);
		for ($i = 0; $i < sizeof($categories_list); $i++) {
			$category_info = $categories_list[$i];
			$categories_names = explode(">", $category_info);

			$last_category_id = 0; $category_path = "";
			for ($ci = 0; $ci < sizeof($categories_names); $ci++) {
				$category_name = trim($categories_names[$ci]);
				if (strval($category_name) == "0") {
					$category_path = "0,";
				} elseif (strlen($category_name)) {
					$category_path .= $last_category_id . ",";
					$sql  = " SELECT category_id FROM " . $table_prefix . "categories ";
					$sql .= " WHERE parent_category_id=" . $db->tosql($last_category_id, INTEGER);
					$sql .= " AND category_name=" . $db->tosql($category_name, TEXT);
					$db->query($sql);
					if ($db->next_record()) {
						$last_category_id = $db->f("category_id");
					} else {
						$parent_category_id = $last_category_id;
						$sql  = " SELECT MAX(category_id) FROM " . $table_prefix . "categories ";
						$last_category_id = get_db_value($sql) + 1;

						$sql  = " SELECT MAX(category_order) FROM " . $table_prefix . "categories ";
						$sql .= " WHERE parent_category_id=" . $db->tosql($parent_category_id, INTEGER);
						$category_order = get_db_value($sql) + 1;

						$sql  = " INSERT INTO " . $table_prefix . "categories ";
						$sql .= " (category_id, parent_category_id, category_path, category_name, category_order, is_showing) VALUES (";
						$sql .= $db->tosql($last_category_id, INTEGER) . ", ";
						$sql .= $db->tosql($parent_category_id, INTEGER) . ", ";
						$sql .= $db->tosql($category_path, TEXT) . ", ";
						$sql .= $db->tosql($category_name, TEXT) . ", ";
						$sql .= $db->tosql($category_order, INTEGER) . ", 1) ";
						$db->query($sql);
					}
				}
			}

			if (strlen($category_path)) {
				$sql  = " INSERT INTO " . $table_prefix . "items_categories (item_id, category_id) ";
				$sql .= " VALUES (" . $db->tosql($item_id, INTEGER) . ", " . $db->tosql($last_category_id, INTEGER) . ") ";
				$db->query($sql);
			}
		}
	}

	function update_items_sites($item_id, $sites)
	{
		global $db, $table_prefix;

		$sites = trim($sites);
		$sql = " DELETE FROM " . $table_prefix . "items_sites WHERE item_id=" . $db->tosql($item_id, INTEGER);
		$db->query($sql);

		$sites_names = explode(";", $sites);
		foreach ($sites_names as $site_name) {
			$site_name = trim($site_name);
			$sql  = " SELECT site_id FROM ".$table_prefix."sites ";
			$sql .= " WHERE short_name=".$db->tosql($site_name, TEXT);
			$sql .= " OR site_name=".$db->tosql($site_name, TEXT);
			$db->query($sql);
			if ($db->next_record()) {
				$item_site_id = $db->f("site_id");
				$sql  = " INSERT INTO " . $table_prefix . "items_sites (item_id, site_id) ";
				$sql .= " VALUES (" . $db->tosql($item_id, INTEGER) . ", " . $db->tosql($item_site_id, INTEGER) . ") ";
				$db->query($sql);
			}
		}
	}

	function update_items_properties($item_id, $properties_info, $column_data)
	{
		global $db, $table_prefix, $property_order, $max_property_id;

		$properties_info = trim($properties_info);

		$property_id = "";
		$sql  = " SELECT property_id FROM " . $table_prefix . "items_properties ";
		$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
		$sql .= " AND property_name=" . $db->tosql($column_data, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$property_id = $db->f("property_id");
			$sql = " DELETE FROM " . $table_prefix . "items_properties_values WHERE property_id=" . $db->tosql($property_id, INTEGER);
			$db->query($sql);
		}

		if (strlen($properties_info)) {
			if (strpos($properties_info, ";") === false) {
				$control_type = "LABEL";
				$property_description = $properties_info;
			} else {
				$control_type = "LISTBOX";
				$property_description = "";
			}
			if (strlen($property_id)) {
				$sql  = " UPDATE " . $table_prefix . "items_properties SET ";
				$sql .= " property_description=" . $db->tosql($property_description, TEXT) . ", ";
				$sql .= " control_type=" . $db->tosql($control_type, TEXT);
				$sql .= " WHERE property_id=" . $db->tosql($property_id, INTEGER);
				$db->query($sql);
			} else {
				$property_order++;
				$max_property_id++;
				$property_id = $max_property_id;
				$item_type_id = 0;

				$sql  = " INSERT INTO " . $table_prefix . "items_properties ";
				$sql .= " (property_id, item_id, item_type_id, property_order, property_name, property_description, control_type, required, use_on_list, use_on_details) VALUES (";
				$sql .= $db->tosql($property_id, INTEGER) . ", ";
				$sql .= $db->tosql($item_id, INTEGER) . ", ";
				$sql .= $db->tosql($item_type_id, INTEGER) . ", ";
				$sql .= $db->tosql($property_order, INTEGER) . ", ";
				$sql .= $db->tosql($column_data, TEXT) . ", ";
				$sql .= $db->tosql($property_description, TEXT) . ", ";
				$sql .= $db->tosql($control_type, TEXT) . ", ";
				$sql .= "0, 1, 1, 0) ";
				$db->query($sql);
			}

			if ($control_type == "LISTBOX") {
				$property_values = explode(";", $properties_info);
				for ($pv = 0; $pv < sizeof($property_values); $pv++) {
					$property_value = trim($property_values[$pv]);
					$additional_price = "";
					if (preg_match("/^(.+)=\s*([\-\+]?[\d]*\.?[\d]*)$/", $property_value, $matches)) {
						$property_value = $matches[1];
						$additional_price = $matches[2];
					}
					if (strlen($property_value)) {
						$sql  = " INSERT INTO " . $table_prefix . "items_properties_values ";
						$sql .= " (property_id, property_value, additional_price, hide_out_of_stock, hide_value) VALUES (";
						$sql .= $db->tosql($property_id, INTEGER) . ", ";
						$sql .= $db->tosql($property_value, TEXT) . ", ";
						$sql .= $db->tosql($additional_price, NUMBER) . ", ";
						$sql .= "0, 0) ";
						$db->query($sql);
					}
				}
			}
		} else {
			$sql = " DELETE FROM " . $table_prefix . "items_properties WHERE property_id=" . $db->tosql($property_id, INTEGER);
			$db->query($sql);
		}
	}

	function update_items_features($item_id, $feature_value, $column_data)
	{
		global $db, $db_type, $features_groups, $table_prefix;

		$feature_value = trim($feature_value);
		if (preg_match("/^(.+)>(.+)$/isU", $column_data, $matches)) {
			$group_name = trim($matches[1]);
			$feature_name = trim($matches[2]);
			if (isset($features_groups[$group_name])) {
				$group_id = $features_groups[$group_name];
			} else {
				$sql  = " SELECT group_id FROM " . $table_prefix . "features_groups ";
				$sql .= " WHERE group_name=" . $db->tosql($group_name, TEXT);
				$db->query($sql);
				if ($db->next_record()) {
					$group_id = $db->f("group_id");
				} else {
					// feature group doesn't exists - add new
					$sql = " SELECT MAX(group_order) FROM " . $table_prefix . "features_groups ";
					$group_order = get_db_value($sql);
					$group_order++;

					if ($db_type == "postgre") {
						$group_id = get_db_value(" SELECT NEXTVAL('seq_" . $table_prefix . "features_groups') ");
					}

					$sql  = " INSERT INTO " . $table_prefix . "features_groups (";
					if ($db_type == "postgre") { $sql .= " group_id, "; }
					$sql .= " group_order, group_name) VALUES (";
					if ($db_type == "postgre") { $sql .= $db->tosql($group_id, INTEGER) . ", "; }
					$sql .= $db->tosql($group_order, INTEGER) . ", ";
					$sql .= $db->tosql($group_name, TEXT) . ") ";
					$db->query($sql);

					if ($db_type == "mysql") {
						$group_id = get_db_value(" SELECT LAST_INSERT_ID() ");
					} elseif ($db_type == "access") {
						$group_id = get_db_value(" SELECT @@IDENTITY ");
					} elseif ($db_type == "db2") {
						$group_id = get_db_value(" SELECT PREVVAL FOR seq_" . $table_prefix . "features_groups FROM " . $table_prefix . "features_groups");
					}
				}
				$features_groups[$group_name] = $group_id;
			}

			$feature_id = "";
			$sql  = " SELECT feature_id FROM " . $table_prefix . "features ";
			$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
			$sql .= " AND group_id=" . $db->tosql($group_id, INTEGER);
			$sql .= " AND feature_name=" . $db->tosql($feature_name, TEXT);
			$db->query($sql);
			if ($db->next_record()) {
				$feature_id = $db->f("feature_id");
			}
			if (strlen($feature_value)) {
				if (strlen($feature_id)) {
					$sql  = " UPDATE " . $table_prefix . "features SET ";
					$sql .= " feature_value=" . $db->tosql($feature_value, TEXT);
					$sql .= " WHERE feature_id=" . $db->tosql($feature_id, INTEGER);
					$db->query($sql);
				} else {
					$sql  = " INSERT INTO " . $table_prefix . "features ";
					$sql .= " (item_id, group_id, feature_name, feature_value) VALUES (";
					$sql .= $db->tosql($item_id, INTEGER) . ", ";
					$sql .= $db->tosql($group_id, INTEGER) . ", ";
					$sql .= $db->tosql($feature_name, TEXT) . ", ";
					$sql .= $db->tosql($feature_value, TEXT) . ") ";
					$db->query($sql);
				}
			} else {
				$sql = " DELETE FROM " . $table_prefix . "features WHERE feature_id=" . $db->tosql($feature_id, INTEGER);
				$db->query($sql);
			}
		}
	}

	function update_manufacturer($item_id, $manufacturer_name)
	{
		global $db, $table_prefix;

		$manufacturer_id = "";
		if (strlen($manufacturer_name)) {
			$sql  = " SELECT manufacturer_id FROM " . $table_prefix . "manufacturers ";
			$sql .= " WHERE manufacturer_name=" . $db->tosql($manufacturer_name, TEXT);
			$db->query($sql);
			if ($db->next_record()) {
				$manufacturer_id = $db->f("manufacturer_id");
			} else {
				$sql  = " SELECT MAX(manufacturer_id) FROM " . $table_prefix . "manufacturers ";
				$manufacturer_id = get_db_value($sql) + 1;

				$sql  = " INSERT INTO " . $table_prefix . "manufacturers ";
				$sql .= " (manufacturer_id, manufacturer_name) VALUES (";
				$sql .= $db->tosql($manufacturer_id, INTEGER) . ", ";
				$sql .= $db->tosql($manufacturer_name, TEXT) . ") ";
				$db->query($sql);
			}
		}

		$sql  = " UPDATE " . $table_prefix . "items SET manufacturer_id=" . $db->tosql($manufacturer_id, INTEGER);
		$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
		$db->query($sql);
	}

	function prepare_categories_list()
	{
		global $db, $table_prefix, $categories;

		//-- parent items
		$sql  = " SELECT category_id, parent_category_id FROM " . $table_prefix . "categories ";
		$db->query($sql);
		while ($db->next_record()) {
			$list_id = $db->f("category_id");
			$list_parent_id = $db->f("parent_category_id");
			$categories[$list_parent_id]["subs"][] = $list_id;
		}
	}

	function update_categories_tree($parent_category_id, $category_path)
	{
		global $db, $table_prefix, $categories;

		if (isset($categories[$parent_category_id]["subs"])) {
			$category_path .= $parent_category_id . ",";

			$subs = $categories[$parent_category_id]["subs"];
			for ($s = 0; $s < sizeof($subs); $s++) {
				$sub_id = $subs[$s];

				$sql  = " UPDATE " . $table_prefix . "categories SET ";
				$sql .= " category_path=" . $db->tosql($category_path, TEXT);
				$sql .= " WHERE category_id=" . $db->tosql($sub_id, INTEGER);
				$db->query($sql);

				if (isset($categories[$sub_id]["subs"])) {
					update_categories_tree($sub_id, $category_path);
				}
			}
		}	
	}

	function import_friendly_url()
	{
		global $imp;
		if ($imp->r->parameter_exists("friendly_url")) {
			$friendly_url = trim($imp->r->get_value("friendly_url")); // trim friendly url value
			$imp->r->set_value("friendly_url", $friendly_url); // set trimed value
			if (strlen($friendly_url)) {
				$is_unique = validate_friendly_url("", false); // check if existed value is unique
				if (!$is_unique) {
					$imp->r->set_value("friendly_url", ""); // clear duplicated friendly value
				}
			}
			set_friendly_url(); // generate new friendly value from title
		}
	}

	function add_imported_fields()
	{
		global $imp, $rr, $table, $table_pk, $datetime_edit_format;

		if (!$imp->r->parameter_exists($table_pk)) {
			$imp->r->add_where($table_pk, TEXT);
			$imp->r->change_property($table_pk, SHOW, false); 
		}

		if ($table == "items") {
			// administrative information
			$imp->r->add_textbox("is_keywords", INTEGER);
			$imp->r->add_textbox("admin_id_added_by", INTEGER);
			$imp->r->change_property("admin_id_added_by", USE_IN_UPDATE, false);
			$imp->r->add_textbox("admin_id_modified_by", INTEGER);
			$imp->r->add_textbox("date_added", DATETIME);
			$imp->r->change_property("date_added", USE_IN_UPDATE, false);
			$imp->r->change_property("date_added", VALUE_MASK, $datetime_edit_format);
			$imp->r->add_textbox("date_modified", DATETIME);
			$imp->r->change_property("date_modified", VALUE_MASK, $datetime_edit_format);
			// set values
			$imp->r->set_value("is_keywords", 0);
			$imp->r->set_value("admin_id_added_by", get_session("session_admin_id"));
			$imp->r->set_value("admin_id_modified_by", get_session("session_admin_id"));
			$imp->r->set_value("date_added", va_time());
			$imp->r->set_value("date_modified", va_time());
		} elseif ($table == "orders") {

			if (!$imp->r->parameter_exists("site_id")) {
				$imp->r->add_textbox("site_id", TEXT);
				$imp->r->change_property("site_id", SHOW, false); 
			}
			if (!$imp->r->parameter_exists("user_id")) {
				$imp->r->add_textbox("user_id", TEXT, "USER_ID_MSG");
				$imp->r->change_property("user_id", SHOW, false); 
				$imp->r->change_property("user_id", USE_SQL_NULL, false);
			}
			if (!$imp->r->parameter_exists("user_type_id")) {
				$imp->r->add_textbox("user_type_id", TEXT, "USER_TYPE_ID_MSG");
				$imp->r->change_property("user_type_id", SHOW, false); 
				$imp->r->change_property("user_type_id", USE_SQL_NULL, false);
			}

			if (!$imp->r->parameter_exists("currency_code")) {
				$imp->r->add_textbox("currency_code", TEXT);
				$imp->r->change_property("currency_code", SHOW, false); 
			}
			if (!$imp->r->parameter_exists("currency_rate")) {
				$imp->r->add_textbox("currency_rate", NUMBER);
				$imp->r->change_property("currency_rate", SHOW, false); 
			}
			if (!$imp->r->parameter_exists("payment_currency_code")) {
				$imp->r->add_textbox("payment_currency_code", TEXT);
				$imp->r->change_property("payment_currency_code", SHOW, false); 
			}
			if (!$imp->r->parameter_exists("payment_currency_rate")) {
				$imp->r->add_textbox("payment_currency_rate", NUMBER);
				$imp->r->change_property("payment_currency_rate", SHOW, false); 
			}

			if (!$imp->r->parameter_exists("user_type_id")) {
				$imp->r->add_textbox("user_type_id", INTEGER, va_message("USER_TYPE_ID_MSG"));
				$imp->r->change_property("user_type_id", SHOW, false); 
				$imp->r->change_property("user_type_id", USE_IN_UPDATE, false); 
			}

			// set shipping type as it could be obtained from shipping desc
			if (!$imp->r->parameter_exists("shipping_type_id")) {
				$imp->r->add_textbox("shipping_type_id", INTEGER, va_message("SHIPPING_ID_MSG"));
				$imp->r->change_property("shipping_type_id", SHOW, false); 
			}
			if (!$imp->r->parameter_exists("shipping_type_code")) {
				$imp->r->add_textbox("shipping_type_code", TEXT, va_message("SHIPPING_CODE_MSG"));
				$imp->r->change_property("shipping_type_code", SHOW, false); 
			}

			if (!$imp->r->parameter_exists("order_placed_date")) {
				$imp->r->add_textbox("order_placed_date", DATETIME, va_message("DATE_ADDED_MSG"));
				$imp->r->change_property("order_placed_date", SHOW, false);
				$imp->r->change_property("order_placed_date", USE_SQL_NULL, false);
				$imp->r->change_property("order_placed_date", VALUE_MASK, $datetime_edit_format);
				$imp->r->set_value("order_placed_date", va_time());
			}
			if (!$imp->r->parameter_exists("modified_date")) {
				$imp->r->add_textbox("modified_date", DATETIME, va_message("DATE_MODIFIED_MSG"));
				$imp->r->change_property("modified_date", SHOW, false);
				$imp->r->change_property("modified_date", USE_SQL_NULL, false);
				$imp->r->change_property("modified_date", VALUE_MASK, $datetime_edit_format);
				$imp->r->set_value("modified_date", va_time());
			}
			if (!$imp->r->parameter_exists("affiliate_code")) {
				$imp->r->add_textbox("affiliate_code", TEXT);
				$imp->r->change_property("affiliate_code", SHOW, false);
				$imp->r->change_property("affiliate_code", USE_SQL_NULL, false);
			}
			if (!$imp->r->parameter_exists("order_total")) {
				$imp->r->add_textbox("order_total", NUMBER, ORDER_TOTAL_MSG);
				$imp->r->change_property("order_total", SHOW, false);
				$imp->r->change_property("order_total", USE_SQL_NULL, false);			
			}

			if (!$imp->r->parameter_exists("order_status")) {
				$imp->r->add_textbox("order_status", TEXT, "{ORDER_STATUS_MSG} ({ID_MSG})");
				$imp->r->change_property("order_status", USE_IN_INSERT, false); 
				$imp->r->change_property("order_status", USE_IN_UPDATE, false); 
			}

			if (!$imp->r->parameter_exists("order_status_name")) {
				$imp->r->add_textbox("order_status_name", TEXT, "{ORDER_STATUS_MSG} ({NAME_MSG})");
				$imp->r->change_property("order_status_name", USE_IN_INSERT, false); 
				$imp->r->change_property("order_status_name", USE_IN_UPDATE, false); 
			}

			if ($imp->r->parameter_exists("shipping_type_id") && !$imp->r->parameter_exists("shipping_type_code")) {
				$imp->r->add_textbox("shipping_type_code", TEXT, "SHIPPING_CODE_MSG");
				$imp->r->change_property("shipping_type_code", SHOW, false);
			}
			if (!$imp->r->parameter_exists("shipping_type_id") && $imp->r->parameter_exists("shipping_type_code")) {
				$imp->r->add_textbox("shipping_type_id", TEXT, "SHIPPING_ID_MSG");
				$imp->r->change_property("shipping_type_id", SHOW, false);
			}

			if (!$imp->r->parameter_exists("payment_id")) {
				$imp->r->add_textbox("payment_id", TEXT, "PAYMENT_ID_MSG");
				$imp->r->change_property("payment_id", SHOW, false); 
			}

			if (!$imp->r->parameter_exists("payment_name")) {
				$imp->r->add_textbox("payment_name", TEXT, "PAYMENT_NAME_MSG");
				$imp->r->change_property("payment_name", USE_IN_INSERT, false); 
				$imp->r->change_property("payment_name", USE_IN_UPDATE, false); 
			}

			if (!$imp->rr->parameter_exists("order_id")) {
				$imp->rr->add_textbox("order_id", TEXT, "ORDER_NUMBER_MSG");
				$imp->rr->change_property("order_id", SHOW, false); 
			}
			if (!$imp->rr->parameter_exists("site_id")) {
				$imp->rr->add_textbox("site_id", TEXT);
				$imp->rr->change_property("site_id", SHOW, false); 
			}
			if (!$imp->rr->parameter_exists("user_id")) {
				$imp->rr->add_textbox("user_id", TEXT, "USER_ID_MSG");
				$imp->rr->change_property("user_id", SHOW, false); 
				$imp->rr->change_property("user_id", USE_SQL_NULL, false);
			}
			if (!$imp->rr->parameter_exists("user_type_id")) {
				$imp->rr->add_textbox("user_type_id", TEXT, "USER_TYPE_ID_MSG");
				$imp->rr->change_property("user_type_id", SHOW, false); 
				$imp->rr->change_property("user_type_id", USE_SQL_NULL, false);
			}
			if (!$imp->rr->parameter_exists("item_type_id")) {
				$imp->rr->add_textbox("item_type_id", TEXT, "{PROD_TYPE_MSG} ({ID_MSG})");
				//$imp->rr->change_property("item_type_id", SHOW, false); 
			}
		}

		if ($table == "orders" || $table == "users") {
			if (!$imp->r->parameter_exists("state_id")) {
				$imp->r->add_textbox("state_id", INTEGER);
				$imp->r->change_property("state_id", USE_SQL_NULL, false);
			}
			if (!$imp->r->parameter_exists("country_id")) {
				$imp->r->add_textbox("country_id", INTEGER);
				$imp->r->change_property("country_id", USE_SQL_NULL, false);
			}
			if (!$imp->r->parameter_exists("delivery_state_id")) {
				$imp->r->add_textbox("delivery_state_id", INTEGER);
				$imp->r->change_property("delivery_state_id", USE_SQL_NULL, false);
			}
			if (!$imp->r->parameter_exists("delivery_country_id")) {
				$imp->r->add_textbox("delivery_country_id", INTEGER);
				$imp->r->change_property("delivery_country_id", USE_SQL_NULL, false);	
			}
		}
	}


function get_header_data($csv_data)
{
	$header_data = array();
	foreach ($csv_data as $id => $column_name) {
		if(function_exists("mb_strtolower")) {
			$lowercase_column = trim(mb_strtolower($column_name, "UTF-8"));
		} else {
			$lowercase_column = trim(strtolower($column_name));
		}

		$header_data[$lowercase_column] = array("id" => $id, "title" => $column_name);
	}
	return $header_data;
}


/*used in case xml feed returns multidimensional array*/
/**
 * @param $array xml data array
 * @param $key needle
 * @return searched value
 */
function get_value_by_key($array, $key)
{
	foreach($array as $k => $val){
		if($k == $key){
			return $val;
		}

		if(is_array($val)){
			if($needle = get_value_by_key($val, $key)){
				return $needle;
			}
		}
	}
	return "";
}

/*
CREATE TABLE va_orders_properties (
  `order_property_id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) DEFAULT '0',
  `property_id` INT(11) DEFAULT '0',
  `property_order` INT(11) DEFAULT '1',
  `property_type` INT(11) DEFAULT '0',
  `property_name` VARCHAR(255) NOT NULL,
  `property_value_id` INT(11),
  `property_value` TEXT,
  `property_price` DOUBLE(16,2) DEFAULT '0',
  `property_points_amount` DOUBLE(16,4) DEFAULT '0',
  `property_weight` DOUBLE(16,4) DEFAULT '0',
  `actual_weight` DOUBLE(16,4),
  `tax_free` TINYINT DEFAULT '0'

property_type 
1 - Shopping Cart
2 - Personal Details
3 - Delivery Details
4 - Payment Details
5 - Shipping Module
6 - Shipping Method
*/