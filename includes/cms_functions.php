<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  cms_functions.php                                        ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

function parse_cms_block_properties($properties_string, $property_type = "", $group_properties = false)
{
	$block_properties = array();
	if ($properties_string != "") {
		$properties_strings = explode("#property#", $properties_string);
		for ($p = 0; $p < sizeof($properties_strings); $p++) {
			$property_string = $properties_strings[$p];
			if (!$property_string) { continue; }
			$block_property = get_cms_params($property_string);
			if ($property_type && $block_property["type"] != $property_type) { continue; }
			if ($group_properties) {
				$property_id = $block_property["id"];
				$value = $block_property["value"];
				$block_properties[$property_id][] = $value;
			} else {
				$block_properties[] = $block_property;
			}
		}
	}	
	return $block_properties;
}

function cms_key_name($key_code, $page_code)
{
	global $db, $table_prefix;
	$key_name = "";
	if (strlen($key_code)) {
		// show additional option for custom layouts
		if ($page_code == "custom_page") {
			$sql  = " SELECT page_id, page_title ";
			$sql .= " FROM " . $table_prefix."pages ";
			$sql .= " WHERE page_id=" . $db->tosql($key_code, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$key_name = get_translation($db->f("page_title"));
			}
		} else if ($page_code == "products_list" || $page_code == "ads_list" || $page_code == "articles_list") {
			if ($page_code == "products_list") {
				$sql  = " SELECT category_id, parent_category_id, category_name, friendly_url ";
				$sql .= " FROM " . $table_prefix."categories ";
				$sql .= " WHERE category_id=" . $db->tosql($key_code, INTEGER);
			} else if ($page_code == "ads_list") {
				$sql  = " SELECT category_id, parent_category_id, category_name, friendly_url ";
				$sql .= " FROM " . $table_prefix."ads_categories ";
				$sql .= " WHERE category_id=" . $db->tosql($key_code, INTEGER);
			} else if ($page_code == "articles_list") {
				$sql  = " SELECT category_id, parent_category_id, category_name, friendly_url ";
				$sql .= " FROM " . $table_prefix."articles_categories ";
				$sql .= " WHERE category_id=" . $db->tosql($key_code, INTEGER);
			}
			$db->query($sql);
			if ($db->next_record()) {
				$key_name= get_translation($db->f("category_name"));
			}
		}
	}
	return $key_name;
}

function get_cms_params($params_string)
{
	$params = array();
	$params_pairs = explode("&", $params_string);

	for ($p = 0; $p < sizeof($params_pairs); $p++) {
		$param_pair = $params_pairs[$p];
		$equal_pos = strpos($param_pair, "=");
		if($equal_pos === false) {
			$params[$param_pair] = "";
		} else {
			$param_name = substr($param_pair, 0, $equal_pos);
			$param_value = substr($param_pair, $equal_pos + 1);
			$params[$param_name] = decode_js_value($param_value);
		}
	}
	return $params;
}

function decode_js_value($js_value)
{
	$find = array("%25", "%2B", "%26", "%22", "%27", "%0A", "%0D", "%3D", "%7C", "%23");
	$replace = array("%", "+", "&", "\"", "'", "\n", "\r", "=", "|", "#");
	$js_value = str_replace($find, $replace, $js_value);
	return $js_value;
}

function check_category_layout($cms_page_code, $category_path, $category_id)
{
	global $db, $site_id, $table_prefix;
	$cms_ps_id = "";
	if (strlen($category_path)) {
		$categories_ids = trim($category_path, ",");
		$ids = explode(",", $categories_ids);
		$where = "";
		for ($c = 0; $c < sizeof($ids); $c++) {
			$id = $ids[$c];
			if ($where) { $where .= " OR "; }
			$where .= "key_code=" . $db->tosql($id, TEXT);		
		}
		$layout_types = array();
		$cms_ps_ids = array();
		if ($where) {
			$sql  = " SELECT cps.ps_id, cps.key_code, cps.key_rule ";
			$sql .= " FROM (" . $table_prefix . "cms_pages_settings cps ";
			$sql .= " INNER JOIN " . $table_prefix . "cms_pages cp ON cp.page_id=cps.page_id) ";
			$sql .= " WHERE cp.page_code=" . $db->tosql($cms_page_code, TEXT);
			if (isset($site_id) && $site_id != 1) {
				$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER) . ") ";
			} else {
				$sql .= " AND site_id=1 ";
			}
			$sql .= " AND (" . $where . ") ";
			$sql .= " AND key_type='category' ";
			$sql .= " ORDER BY site_id ASC ";
			$db->query($sql);
			while ($db->next_record()) {
				$row_ps_id = $db->f("ps_id");
				$row_key_code = $db->f("key_code");
				$row_key_rule = $db->f("key_rule");
				$layout_types[$row_key_code] = $row_key_rule;
				$cms_ps_ids[$row_key_code] = $row_ps_id;
			}
		}

		for ($c = (sizeof($ids) - 1); $c >= 0; $c--) {
			$id = $ids[$c];
			if (isset($layout_types[$id])) {
				$key_rule = $layout_types[$id];
				if ($key_rule == "all" || $key_rule == "") {
					$cms_ps_id = $cms_ps_ids[$id];
					break;
				} else if ($key_rule == "category" && $id == $category_id) {
					$cms_ps_id = $cms_ps_ids[$id];
					break;
				}
			}
		}
	}
	return $cms_ps_id;
}


function cms_delete_page_settings($ps_id)
{
	global $db, $dbs, $table_prefix;
	if (!isset($dbs) || !is_object($dbs)) { $dbs = new VA_SQL($db); }

	// delete all previous records for frame, blocks and their settings 
	$sql  = " DELETE FROM " . $table_prefix . "cms_frames_settings ";
	$sql .= " WHERE ps_id=" . $db->tosql($ps_id, INTEGER);
	$dbs->query($sql);
	$sql  = " DELETE FROM " . $table_prefix . "cms_pages_blocks ";
	$sql .= " WHERE ps_id=" . $db->tosql($ps_id, INTEGER);
	$dbs->query($sql);
	$sql  = " DELETE FROM " . $table_prefix . "cms_blocks_settings ";
	$sql .= " WHERE ps_id=0 OR ps_id=" . $db->tosql($ps_id, INTEGER);
	$dbs->query($sql);
	$sql  = " DELETE FROM " . $table_prefix . "cms_blocks_periods ";
	$sql .= " WHERE ps_id=0 OR ps_id=" . $db->tosql($ps_id, INTEGER);
	$dbs->query($sql);
	// remove main page settings record
	$sql  = " DELETE FROM " . $table_prefix . "cms_pages_settings ";
	$sql .= " WHERE ps_id=" . $db->tosql($ps_id, INTEGER);
	$dbs->query($sql);


}
