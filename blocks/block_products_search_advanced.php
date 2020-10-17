<?php

	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	$default_title = "{SEARCH_TITLE}";

	$html_template = get_setting_value($block, "html_template", "block_products_search_advanced.html"); 
  $t->set_file("block_body", $html_template);

	$t->set_var("search_href", get_custom_friendly_url("search.php"));
	$t->set_var("products_href", get_custom_friendly_url("products_list.php"));
	$t->set_var("products_search_href", get_custom_friendly_url("products_search.php"));

	$currency = get_currency();
	$weight_measure = get_setting_value($settings, "weight_measure", "");
	$keywords_search = get_setting_value($settings, "keywords_search", 0);
	$item_name_index = get_setting_value($settings, "item_name_index", "");
	$manufacturer_code_index = get_setting_value($settings, "manufacturer_code_index", "");
	$item_code_index = get_setting_value($settings, "item_code_index", "");
	$short_description_index = get_setting_value($settings, "short_description_index", "");
	$full_description_index = get_setting_value($settings, "full_description_index", "");

	$category_id = get_param("category_id");
	$search_string = trim(get_param("search_string"));
	$s_tit = get_param("s_tit");
	$s_cod = get_param("s_cod");
	$s_des = get_param("s_des");
	$manf = get_param("manf");
	$lprice = get_param("lprice");
	$hprice = get_param("hprice");
	$lweight = get_param("lweight");
	$hweight = get_param("hweight");
	if (!$s_tit && !$s_cod && !$s_des) {
		$s_tit = "checked";
		$s_cod = "checked";
		$s_des = "checked";
	}
	if ($s_tit == 1) { $s_tit = "checked"; }
	if ($s_cod == 1) { $s_cod = "checked"; }
	if ($s_des == 1) { $s_des = "checked"; }
		
	$t->set_var("search_string", htmlspecialchars($search_string));
	$t->set_var("s_tit", htmlspecialchars($s_tit));
	$t->set_var("s_cod", htmlspecialchars($s_cod));
	$t->set_var("s_des", htmlspecialchars($s_des));
	$t->set_var("lprice", htmlspecialchars($lprice));
	$t->set_var("hprice", htmlspecialchars($hprice));
	$t->set_var("lweight", htmlspecialchars($lweight));
	$t->set_var("hweight", htmlspecialchars($hweight));
	$t->set_var("currency_symbol", $currency["left"].$currency["right"]);
	$t->set_var("weight_measure", htmlspecialchars($weight_measure));

	$search_categories = array(array("", ""));
	$categories_ids = VA_Categories::find_all_ids("c.parent_category_id = 0", VIEW_CATEGORIES_ITEMS_PERM);
	if ($categories_ids) {
		$sql  = " SELECT category_id, category_name ";
		$sql .= " FROM " . $table_prefix . "categories ";
		$sql .= " WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ") ";
		$sql .= " ORDER BY category_order ";
		$search_categories = get_db_values($sql, $search_categories);
	}
	set_options($search_categories, $category_id, "category_id");

	
	
	$manufacturers = get_db_values("SELECT manufacturer_id,manufacturer_name FROM " . $table_prefix . "manufacturers ORDER BY manufacturer_name ", array(array("", "")));
	set_options($manufacturers, $manf, "manufacturer_id");

	if (!$keywords_search || $item_name_index) {
		$t->sparse("s_tit_block", false);
	}
	if (!$keywords_search || $manufacturer_code_index || $item_code_index) {
		$t->sparse("s_cod_block", false);
	}
	if (!$keywords_search || $short_description_index || $full_description_index) {
		$t->sparse("s_des_block", false);
	}


	$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type='search'";
	if (isset($site_id)) {
		$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
		$sql .= " ORDER BY site_id ASC ";
	} else {
		$sql .= " AND site_id=1 ";
	}
	$db->query($sql);
	$tmp_settings = array();
	while ($db->next_record()) {
		$setting_name = $db->f("setting_name");
		$setting_value = $db->f("setting_value");
		$tmp_settings[$setting_name] = $setting_value;
	}
	if ($keywords_search && !$item_name_index && !$manufacturer_code_index && !$item_code_index && !$short_description_index && !$full_description_index) {
		$tmp_settings["search_in"] = 0;
	}
	foreach ($tmp_settings AS $setting_name=>$setting_value) {
		if ($setting_value == 1) {
			$t->set_var($setting_name, get_param($setting_name));
			$t->sparse($setting_name . "_block", false);
		} 
	}

	$predefined_values = array();
	$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type='search_properties_values'";
	if (isset($site_id)) {
		$sql .= " AND site_id=" . $db->tosql($site_id, INTEGER, true, false);
	} else {
		$sql .= " AND site_id=1 ";
	}
	$db->query($sql);
	while ($db->next_record()) {
		$predefined_values[$db->f("setting_name")] = $db->f("setting_value");
	}

	$property_number = 0;
	$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type='search_properties'";
	if (isset($site_id)) {
		$sql .= " AND site_id=" . $db->tosql($site_id, INTEGER, true, false);
	} else {
		$sql .= " AND site_id=1 ";
	}
	$db->query($sql);
	$tmp_settings = array();
	while ($db->next_record()) {
		$setting_name = $db->f("setting_name");
		$setting_value = $db->f("setting_value");
		$tmp_settings[$setting_name] = $setting_value;
	}
	foreach ($tmp_settings AS $setting_name=>$setting_value) {
		if ($setting_value == 1) {
			$property_number++;
			$property_values_list = get_setting_value($predefined_values, $setting_name, "");
			$property_value = get_param("pv_" . $property_number);
			$t->set_var("property_number", $property_number);
			$t->set_var("property_name", htmlspecialchars($setting_name));
			$t->set_var("property_name_translation", get_translation($setting_name));
			$t->set_var("property_select", "");
			$t->set_var("property_text", "");
			if (strlen(trim($property_values_list))) {
				$values_array = explode("\n", $property_values_list);
				$property_values = array();
				$property_values[] = array("", "");
				for ($i = 0; $i < sizeof($values_array); $i++) {
					$option_value = trim($values_array[$i]);
					$option_value = get_translation($option_value);
					if (strlen($option_value)) {
						$property_values[] = array($option_value, $option_value);
					}
				}
				set_options($property_values, $property_value, "property_value");
				$t->parse("property_select", false);
			} else {
				$t->set_var("property_value", htmlspecialchars($property_value));
				$t->parse("property_text", false);
			}

			$t->parse("properties", true);
		}
	}
	$t->set_var("pq", $property_number);

	$predefined_values = array();
	$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type='search_features_values'";
	if (isset($site_id)) {
		$sql .= " AND site_id=" . $db->tosql($site_id, INTEGER, true, false);
	} else {
		$sql .= " AND site_id=1 ";
	}
	$db->query($sql);
	while ($db->next_record()) {
		$predefined_values[$db->f("setting_name")] = $db->f("setting_value");
	}

	$feature_number = 0;
	$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type='search_features'";
	if (isset($site_id)) {
		$sql .= " AND site_id=" . $db->tosql($site_id, INTEGER, true, false);
	} else {
		$sql .= " AND site_id=1 ";
	}
	$db->query($sql);
	$tmp_settings = array();
	while ($db->next_record()) {
		$setting_name = $db->f("setting_name");
		$setting_value = $db->f("setting_value");
		$tmp_settings[$setting_name] = $setting_value;
	}
	foreach ($tmp_settings AS $setting_name=>$setting_value) {
		if ($setting_value == 1) {
			$feature_number++;
			$feature_values_list = get_setting_value($predefined_values, $setting_name, "");
			$feature_value = get_param("fv_" . $feature_number);
			$t->set_var("feature_number", $feature_number);
			$t->set_var("feature_name", htmlspecialchars($setting_name));
			$t->set_var("feature_name_translation", htmlspecialchars(get_translation($setting_name)));
			$t->set_var("feature_select", "");
			$t->set_var("feature_text", "");
			if (strlen(trim($feature_values_list))) {
				$values_array = explode("\n", $feature_values_list);
				$feature_values = array();
				$feature_values[] = array("", "");
				for ($i = 0; $i < sizeof($values_array); $i++) {
					$option_value = trim($values_array[$i]);
					$option_value = get_translation($option_value);
					if (strlen(trim($option_value))) {
						$feature_values[] = array($option_value, $option_value);
					}
				}
				set_options($feature_values, $feature_value, "feature_value");
				$t->parse("feature_select", false);
			} else {
				$t->set_var("feature_value", htmlspecialchars($feature_value));
				$t->parse("feature_text", false);
			}
			$t->parse("features", true);
		}
	}
	$t->set_var("fq", $feature_number);

	$block_parsed = true;

?>