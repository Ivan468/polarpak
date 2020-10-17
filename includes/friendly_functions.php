<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  friendly_functions.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$friendly_tables = array(
		$table_prefix . "categories" => array("category_id", "category_name"), 
		$table_prefix . "items" => array("item_id", "item_name"), 
		$table_prefix . "manufacturers" => array("manufacturer_id", "manufacturer_name"),
		$table_prefix . "articles_categories" => array("category_id", "category_name"), 
		$table_prefix . "articles" => array("article_id", "article_title"),
		$table_prefix . "authors" => array("author_id", "author_name"),
		$table_prefix . "albums" => array("album_id", "album_name"),
		$table_prefix . "tags" => array("tag_id", "tag_name"),
		$table_prefix . "forum_categories" => array("category_id", "category_name"), 
		$table_prefix . "forum_list" => array("forum_id", "forum_name"), 
		$table_prefix . "forum" => array("thread_id", "topic"),
		$table_prefix . "ads_categories" => array("category_id", "category_name"), 
		$table_prefix . "ads_items" => array("item_id", "item_title"),
		$table_prefix . "users" => array("user_id", "login"),
		$table_prefix . "pages" => array("page_id", "page_title"),
		$table_prefix . "manuals_list" => array("manual_id", "manual_title"),
		$table_prefix . "manuals_categories" => array("category_id", "category_name"),
		$table_prefix . "manuals_articles" => array("article_id", "article_title"),
		$table_prefix . "friendly_urls" => array("friendly_id", "script_name"),
		
	);

function set_friendly_url($parameters = array())
{
	global $db, $table_prefix, $settings, $r, $eg, $friendly_tables;

	$is_grid = isset($parameters["is_grid"]) ? $parameters["is_grid"] : false;
	$friendly_auto = get_setting_value($settings, "friendly_auto", 0);
	$friendly_transform = get_setting_value($settings, "friendly_transform", 0);

	if ($is_grid) {
		$friendly_url = $eg->record->get_value("friendly_url");
		$field_updatable = ($eg->record->get_property_value("friendly_url", USE_IN_INSERT) || $eg->record->get_property_value("friendly_url", USE_IN_UPDATE));
	} else {
		$friendly_url = $r->get_value("friendly_url");
		$field_updatable = ($r->get_property_value("friendly_url", USE_IN_INSERT) || $r->get_property_value("friendly_url", USE_IN_UPDATE));
	}          

	if ($field_updatable && ($friendly_auto == 1 || (!strlen($friendly_url) && $friendly_auto == 2))) {
		if ($is_grid) {
			$record_table = $eg->record->table_name;
		} else {
			$record_table = $r->table_name;
		}
		$table_info = $friendly_tables[$record_table];
		$title_field = $table_info[1];
		if ($is_grid) {
			$title_value = $eg->record->get_value($title_field);
		} else {
			$title_value = $r->get_value($title_field);
		}
		if ($is_grid) {
			$excluding_where = $eg->record->check_where() ? $eg->record->get_where(false) : "";
		} else {
			$excluding_where = $r->check_where() ? $r->get_where(false) : "";
		}
		if (strlen($excluding_where)) {
			$excluding_where = " AND NOT (" . $excluding_where . ")";
		}

		$friendly_url = generate_friendly_url($title_value, $record_table, $excluding_where);
		if ($is_grid) {
			$eg->record->set_value("friendly_url", $friendly_url);
		} else {
			$r->set_value("friendly_url", $friendly_url);
		}
	} else if ($field_updatable && $friendly_transform && $friendly_url) {
		$friendly_url = case_transform($friendly_url, $friendly_transform);
		if ($is_grid) {
			$eg->record->set_value("friendly_url", $friendly_url);
		} else {
			$r->set_value("friendly_url", $friendly_url);
		}
	}

}

function validate_friendly_url($parameters, $generate_error = true)
{
	global $db, $table_prefix, $r, $eg, $friendly_tables;

	$eol = get_eol();
	$is_grid = isset($parameters["is_grid"]) ? $parameters["is_grid"] : false;

	if ($is_grid) {
		$friendly_url = $eg->record->get_value("friendly_url");
	} else {
		$friendly_url = $r->get_value("friendly_url");
	}

	$is_unique = true;
	if (strlen($friendly_url)) {
		foreach ($friendly_tables as $check_table => $table_info) {
			$sql  = " SELECT friendly_url FROM " . $check_table;
			$sql .= " WHERE friendly_url=" . $db->tosql($friendly_url, TEXT);
			if ($is_grid) {
				$record_table = $eg->record->table_name;
			} else {
				$record_table = $r->table_name;
			}
			if ($record_table == $check_table) {
				if ($is_grid) {
					$excluding_where = $eg->record->check_where() ? $eg->record->get_where(false) : "";
				} else {
					$excluding_where = $r->check_where() ? $r->get_where(false) : "";
				}
				if (strlen($excluding_where)) {
					$sql .= " AND NOT (" . $excluding_where . ")";
				}
			}
			$db->query($sql);
			if ($db->next_record()) {
				$is_unique = false;
				if ($generate_error) {
					$error_message = str_replace("{field_name}", $r->parameters["friendly_url"][CONTROL_DESC], UNIQUE_MESSAGE);
					if ($is_grid) {
						$eg->record->errors .= $error_message . " Table: $check_table<br/>" . $eol;
					} else {
						$r->errors .= $error_message . " Table: $check_table<br/>" . $eol;
					}
				}
				break;
			}
		}
	}
	return $is_unique;
}

function case_transform($transform_string, $transform_type)
{
	if (function_exists("mb_convert_case")) {
		// use mb conversion for utf-8
		if ($transform_type == 1) {
			$transform_string = mb_convert_case($transform_string, MB_CASE_LOWER, "UTF-8"); 
		} else if ($transform_type == 2) {
			$transform_string = mb_convert_case($transform_string, MB_CASE_UPPER, "UTF-8"); 
		} else if ($transform_type == 3) {
			$transform_string = mb_convert_case($transform_string, MB_CASE_TITLE, "UTF-8"); 
		} else if ($transform_type == 4) {
			$transform_string = mb_convert_case($transform_string[0], MB_CASE_UPPER, "UTF-8").mb_convert_case(substr($transform_string, 1), MB_CASE_LOWER, "UTF-8"); 
		}
	} else {
		if ($transform_type == 1) {
			$transform_string = strtolower($transform_string);
		} else if ($transform_type == 2) {
			$transform_string = strtoupper($transform_string);
		} else if ($transform_type == 3) {
			$transform_string = ucwords($transform_string);
		} else if ($transform_type == 4) {
			$transform_string = ucfirst($transform_string);
		}
	}
	return $transform_string;
}

function generate_friendly_url($item_title, $record_table = "", $excluding_where = "")
{
	global $db, $table_prefix, $settings, $friendly_tables, $language_code;

	$friendly_transform = get_setting_value($settings, "friendly_transform", 0);

	$item_title = trim(get_translation($item_title));
	$item_title = case_transform($item_title, $friendly_transform);

	// apply translit rules
	$friendly_url = english_translit($item_title);
	$friendly_url = str_replace("@"," at ", $friendly_url);
	$friendly_url = preg_replace("/[\s\.]+/", "-", $friendly_url);
	$friendly_url = preg_replace("/[^a-z\d\_\-\s]/i", "", $friendly_url);
	$friendly_url = preg_replace("/_*\-+_*/", "-", $friendly_url);
	$initial_friendly_url = $friendly_url;

	if (strlen($friendly_url)) {
		$index = 0;
		do {
			if ($index) {
				$friendly_url = $initial_friendly_url . "-" . $index;
			}
			$is_exists = false;
			foreach ($friendly_tables as $check_table => $table_info) {
				$sql  = " SELECT friendly_url FROM " . $check_table;
				$sql .= " WHERE friendly_url=" . $db->tosql($friendly_url, TEXT);
				if ($check_table == $record_table) {
					$sql .= $excluding_where;
				}
				$db->query($sql);
				if ($db->next_record()) {
					$is_exists = true;
				}
			} 
			$index++;
		} while ($is_exists); 
	}

	return $friendly_url;
}

function english_translit($title, $language_param = "") 
{
	global $language_code;
	if (!$language_param ) { $language_param = $language_code; } 

	$codes = array(
		'А'=>'a', 'Б'=>'b', 'В'=>'v', 'Г'=>'g', 'Ґ'=>'g',
		'Д'=>'d', 'Е'=>'ye', 'Є' => 'ye','Ё'=>'ye', 'Ж'=>'zh',
		'З'=>'z', 'И'=>'i', 'І' => 'i', 'Ї' => 'yi', 'Й'=>'y', 
		'K'=>'k', 'Л'=>'l', 'М'=>'m', 'Н'=>'n', 'О'=>'o',
		'П'=>'p', 'Р'=>'r', 'С'=>'s', 'Т'=>'t',
		'У'=>'u', 'Ф'=>'f', 'Х'=>'kh', 'Ц'=>'ts',
		'Ч'=>'ch', 'Ш'=>'sh', 'Щ'=>'shch', 'Ъ'=>'',
		'Ы'=>'y', 'Ь'=>'', 'Э'=>'e', 'Ю'=>'yu', 'Я'=>'ya',
		
		'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'ґ' => 'g',
		'д'=>'d', 'е'=>'ye', 'є' => 'ye','ё'=>'ye', 'ж'=>'zh',
		'з'=>'z', 'и'=>'i', 'і' => 'i', 'ї' => 'yi', 'й'=>'y', 'к'=>'k',
		'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o',
		'п'=>'p', 'р'=>'r', 'с'=>'s', 'т'=>'t',
		'у'=>'u', 'ф'=>'f', 'х'=>'kh', 'ц'=>'ts',
		'ч'=>'ch', 'ш'=>'sh', 'щ'=>'shch', 'ъ'=>'',
		'ы'=>'y', 'ь'=>'', 'э'=>'e', 'ю'=>'yu', 'я'=>'ya',
		 
		
		' '=> '-', '.'=> '', '/' => '-', ',' => '',
		"'" => '', '"' => ''
	);
	if ($language_param == "ru") {
		$codes["&"] = "-i-";
		$codes["+"] = "-i-";
	} else if ($language_param == "uk") {
		$codes["&"] = "-ta-";
		$codes["+"] = "-ta-";
		$codes["Г"] = "h"; $codes["г"] = "h";
		$codes["Е"] = "e"; $codes["е"] = "e";
		$codes["И"] = "y"; $codes["и"] = "y";
	} else {
		$codes["&"] = "-and-";
		$codes["+"] = "-and-";
	}
	return strtolower(strtr($title, $codes));
}

function decode_translit($title, $language_code)
{
	$title = strtolower($title);
	if ($language_code == "uk" || $language_code == "ru") {
		$codes = array(
			"a" => "а", 
			"b" => "б", 
			"v" => "в", 
			"h" => "г", 
			"g" => "ґ", 
			"d" => "д", 
			"e" => "е", 
			"ye" => "є", 
			"zh" => "ж", 
			"z" => "з", 
			"y" => "и", 
			"i" => "і", 
			"yi" => "ї", 
			"y" => "й", 
			"k" => "к", 
			"l" => "л", 
			"m" => "м", 
			"n" => "н", 
			"o" => "о", 
			"p" => "п", 
			"r" => "р", 
			"s" => "с", 
			"t" => "т", 
			"u" => "у", 
			"f" => "ф", 
			"kh" => "х", 
			"ts" => "ц", 
			"ch" => "ч", 
			"sh" => "ш", 
			"shch" => "щ", 
			"yu" => "ю", 
			"ya" => "я", 
			"'" => "ь", 
		);
		if ($language_code == "ru") {
			$codes["g"] = "г";
			$codes["ye"] = "е";
			$codes["e"] = "э";
			$codes["i"] = "и";
		}
		$title = strtr($title, $codes);
	}

	return $title;
}

?>