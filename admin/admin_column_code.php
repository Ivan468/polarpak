<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_column_code.php                                    ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ($root_folder_path . "includes/sorter.php");
	include_once ($root_folder_path . "includes/navigator.php");
	include_once ("../messages/".$language_code."/cart_messages.php");

	include_once("./admin_common.php");

	check_admin_security("products_categories");

	$sw = trim(get_param("sw"));
	$column = trim(get_param("column"));
	$options_ord = get_param("options_ord");
	$options_dir = get_param("options_dir");
	$features_ord = get_param("features_ord");
	$features_dir = get_param("features_dir");

	$tab = get_param("tab");
	if (!$tab) { $tab = "fields"; }

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_column_code.html");
	$t->set_var("admin_column_code_href", "admin_column_code.php");
	$t->set_var("sw", htmlspecialchars($sw));
	$t->set_var("column", htmlspecialchars($column));
	$t->set_var("options_ord", htmlspecialchars($options_ord));
	$t->set_var("options_dir", htmlspecialchars($options_dir));
	$t->set_var("features_ord", htmlspecialchars($features_ord));
	$t->set_var("features_dir", htmlspecialchars($features_dir));

	$options_where = "";
	$features_where = "";
	$sa = array();
	if ($sw) {
		$sa = explode(" ", $sw);
		for($si = 0; $si < sizeof($sa); $si++) {
			$options_where .= " AND ";
			$options_where .= " (property_name LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
			$options_where .= " OR property_code LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%')";
			$features_where .= " AND ";
			$features_where .= " (feature_name LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
			$features_where .= " OR feature_code LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%')";
		}
	}

	// parse fields
	$fields = array(
		"image" => IMAGE_MSG,
		"compare" => COMPARE_MSG,
		"manufacturer" => MANUFACTURER_NAME_MSG,
		"manufacturer_code" => MANUFACTURER_CODE_MSG,
		"item_name" => PROD_NAME_MSG,
		"item_code" => PROD_CODE_MSG,
		"found_in_category" => FOUND_IN_MSG,
		"short_description" => SHORT_DESCRIPTION_MSG,
		"rating" => PROD_RATING_MSG,
		"options" => OPTIONS_MSG,
		"features" => ADMIN_FEATURES_MSG,
		"price" => PRICE_MSG,
		"sales_price" => OUR_PRICE_MSG,
		"save" => YOU_SAVE_MSG,
		"quantity" => QUANTITY_MSG,
		"stock_level" => STOCK_LEVEL_MSG,
		"availability" => PROD_AVAILABILITY_MSG,
		"add_button" => ADD_BUTTON,
		"more_button" => READ_MORE_MSG,
	);
	$t->parse("fields_sorters", false);
	$total_records = 0;
	foreach ($fields as $field_code => $field_name) {

		$field_code_js = str_replace("'", "\\'", htmlspecialchars($field_code));
		$field_name = htmlspecialchars($field_name);
		$field_code = htmlspecialchars($field_code);
		$field_found = true;
		if(is_array($sa)) {
			for($si = 0; $si < sizeof($sa); $si++) {
				$search_regexp = preg_quote($sa[$si], "/");
				if (!preg_match("/".$search_regexp."/i", $field_name) && !preg_match("/".$search_regexp."/i", $field_code)) {
					$field_found = false;
				}
			}
			if ($field_found) {
				$replace_regexp = implode("|", $sa);
				$field_name = preg_replace ("/(" . $replace_regexp . ")/i", "<font color=blue><b>\\1</b></font>", $field_name);
				$field_code = preg_replace ("/(" . $replace_regexp . ")/i", "<font color=blue><b>\\1</b></font>", $field_code);
			}
		}

		if ($field_found) {
			$total_records++;
			$t->set_var("field_name", $field_name);
			$t->set_var("field_code", $field_code);
			$t->set_var("field_code_js", htmlspecialchars($field_code_js));
			$t->parse("fields");
		}
	}

	if (strlen($sw)) {
		$found_message = str_replace("{found_records}", $total_records, FOUND_RECORDS_MSG);
		$found_message = str_replace("{search_string}", htmlspecialchars($sw), $found_message);
		$t->set_var("found_message", $found_message);
		$t->parse("fields_results", false);
	}
	// end of fields parse

	// parse options
	$total_records = 0;
	$where = " WHERE property_code IS NOT NULL AND property_code<>'' " . $options_where;
	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "items_properties  " . $where;
	$sql .= " GROUP BY property_code, property_name ";
	$db->query($sql);
	while ($db->next_record()) {
		$total_records++;
	}

	if (strlen($sw)) {
		$found_message = str_replace("{found_records}", $total_records, FOUND_RECORDS_MSG);
		$found_message = str_replace("{search_string}", htmlspecialchars($sw), $found_message);
		$t->set_var("found_message", $found_message);
		$t->parse("options_results", false);
	}

	// set up variables for sorder and navigator
	$pass_parameters = array(
		"sw" => $sw,
		"column" => $column,
		"tab" => "options",
	);

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_column_code.php", "options", "options_page", $pass_parameters);
	$s->set_parameters(false, true, true, false);
	$s->set_default_sorting(2, "asc");
	$s->set_sorter(OPTION_NAME_MSG, "sorter_option_name", "1", "property_name");
	$s->set_sorter(CODE_MSG, "sorter_option_code", "2", "property_code");

	// add sorting parameters for navigation
	$pass_parameters["options_ord"] = get_param("options_ord");
	$pass_parameters["options_dir"] = get_param("options_dir");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_column_code.php");
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("options_navigator", "options_page", MOVING, $pages_number, $records_per_page, $total_records, false, $pass_parameters);

	$sql  = " SELECT property_code, property_name ";
	$sql .= " FROM " . $table_prefix . "items_properties ";
	$sql .= $where;
	$sql .= " GROUP BY property_code, property_name ";
	$sql .= $s->order_by;
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query($sql);
	if ($db->next_record()) {
		$t->parse("options_sorters");
		do {
			$property_code = $db->f("property_code");
			$property_name = $db->f("property_name");
			$property_code_js = "option_".str_replace("'", "\\'", htmlspecialchars($property_code));
			$property_name = htmlspecialchars($property_name);
			$property_code = htmlspecialchars($property_code);

			if(is_array($sa)) {
				$replace_regexp = implode("|", $sa);
				$property_code = preg_replace ("/(" . $replace_regexp . ")/i", "<font color=blue><b>\\1</b></font>", $property_code);
				$property_name = preg_replace ("/(" . $replace_regexp . ")/i", "<font color=blue><b>\\1</b></font>", $property_name);
			}

			$t->set_var("option_name", $property_name);
			$t->set_var("option_code", $property_code);
			$t->set_var("option_code_js", htmlspecialchars($property_code_js));

			$t->parse("options");
		} while ($db->next_record());
	}
	// end of options parse


	// parse features 
	$total_records = 0;
	$where = " WHERE feature_code IS NOT NULL AND feature_code<>'' " . $features_where;
	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "features " . $where;
	$sql .= " GROUP BY feature_code, feature_name ";
	$db->query($sql);
	while ($db->next_record()) {
		$total_records++;
	}

	if (strlen($sw)) {
		$found_message = str_replace("{found_records}", $total_records, FOUND_RECORDS_MSG);
		$found_message = str_replace("{search_string}", htmlspecialchars($sw), $found_message);
		$t->set_var("found_message", $found_message);
		$t->parse("features_results", false);
	}

	// set up variables for sorder and navigator
	$pass_parameters = array(
		"sw" => $sw,
		"column" => $column,
		"tab" => "features",
	);

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_column_code.php", "features", "features_page", $pass_parameters);
	$s->set_parameters(false, true, true, false);
	$s->set_default_sorting(2, "asc");
	$s->set_sorter(FEATURE_NAME_MSG, "sorter_feature_name", "1", "feature_name");
	$s->set_sorter(CODE_MSG, "sorter_feature_code", "2", "feature_code");

	// add sorting parameters for navigation
	$pass_parameters["features_ord"] = get_param("features_ord");
	$pass_parameters["features_dir"] = get_param("features_dir");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_column_code.php");
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("features_navigator", "features_page", MOVING, $pages_number, $records_per_page, $total_records, false, $pass_parameters);

	$sql  = " SELECT feature_code, feature_name ";
	$sql .= " FROM " . $table_prefix . "features ";
	$sql .= $where;
	$sql .= " GROUP BY feature_code, feature_name ";
	$sql .= $s->order_by;
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query($sql);
	if ($db->next_record()) {
		$t->parse("features_sorters");
		do {
			$feature_code = $db->f("feature_code");
			$feature_name = $db->f("feature_name");
			$feature_code_js = "feature_".str_replace("'", "\\'", htmlspecialchars($feature_code));

			if(is_array($sa)) {
				$replace_regexp = implode("|", $sa);
				$feature_code = preg_replace ("/(" . $replace_regexp . ")/i", "<font color=blue><b>\\1</b></font>", htmlspecialchars($feature_code));
				$feature_name = preg_replace ("/(" . $replace_regexp . ")/i", "<font color=blue><b>\\1</b></font>", htmlspecialchars($feature_name));
			}

			$t->set_var("feature_name", $feature_name);
			$t->set_var("feature_code", $feature_code);
			$t->set_var("feature_code_js", htmlspecialchars($feature_code_js));

			$t->parse("features");
		} while ($db->next_record());
	}
	// end of features parse

	// set tabs
	$tabs = array(
		"fields" => array("title" => PREDEFINED_FIELDS_MSG), 
		"options" => array("title" => OPTIONS_MSG), 
		"features" => array("title" => ADMIN_FEATURES_MSG), 
	);

	parse_admin_tabs($tabs, $tab, 5);

	$t->pparse("main");


?>