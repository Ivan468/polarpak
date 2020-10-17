<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  export_functions.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	function get_field_value($field_source)
	{
		global $db, $dbs, $dbe, $table_prefix, $db_columns, $related_columns, $related_table_alias, $apply_translation, $date_formats, $date_edit_format, $datetime_edit_format;

		if (preg_match_all("/\{(\w+)\}/i", $field_source, $matches)) {
			$field_value = $field_source;
			for($p = 0; $p < sizeof($matches[1]); $p++) {
				$f_source = $matches[1][$p];
				$f_source_value = "";  
				// get field type
				$column_type = TEXT; $column_name = ""; $column_format = ""; 				
				if (preg_match("/^order_property_/", $f_source)) {
					$column_code = substr($f_source, 15);
					$order_id = $dbe->f("order_id");
					$order_properties = array();
					$properties_ids = array();
					if (preg_match("/^\d+$/", $column_code)) {
						$properties_ids[] = $column_code;
					} else { 
						$sql  = " SELECT property_id "; 
						$sql .= " FROM " . $table_prefix . "order_custom_properties ocp ";
						$sql .= " WHERE property_code=" . $db->tosql($column_code, TEXT);
						$dbs->query($sql);
						while ($dbs->next_record()) {
							$property_id = $dbs->f("property_id");
							$properties_ids[] = $property_id;
						}
					}
					if (count($properties_ids)) {
						$sql  = " SELECT op.property_id, op.property_type, op.property_name, op.property_value, ";
						$sql .= " op.property_price, op.property_points_amount, op.tax_free ";
						$sql .= " FROM " . $table_prefix . "orders_properties op ";
						$sql .= " WHERE op.order_id=" . $dbe->tosql($order_id, INTEGER);
						$sql .= " AND op.property_id IN (" . $dbe->tosql($properties_ids, INTEGERS_LIST) . ") ";
						$dbs->query($sql);
						while ($dbs->next_record()) {
							$property_value = $dbs->f("property_value");
							if (strlen($f_source_value)) { $f_source_value .= "; "; }
							if ($apply_translation) {
								$f_source_value .= get_translation($property_value);
							} else {
								$f_source_value .= $property_value;
							}
						}
					}
				} else if (preg_match("/^oi_order_item_property_/", $f_source)) {
					$property_id = substr($f_source, 23);
					$order_item_id = $dbe->f("oi_order_item_id");
					$sql  = " SELECT property_value FROM " . $table_prefix . "orders_items_properties ";
					$sql .= " WHERE order_item_id=" . $order_item_id;
					$sql .= " AND (property_id=" . $dbe->tosql($property_id, INTEGER, true, false);
					$sql .= " OR property_name=" . $dbe->tosql($property_id, TEXT) . ") ";
					$dbs->query($sql);
					if ($dbs->next_record()) {
						if ($apply_translation) {
							$f_source_value = get_translation($dbs->f("property_value"));
						} else {
							$f_source_value = $dbs->f("property_value");
						}
					}
				} else if (isset($db_columns[$f_source])) {
					$column_type = isset($db_columns[$f_source]["data_type"]) ? $db_columns[$f_source]["data_type"] : $db_columns[$f_source][1];
					$column_name = $f_source;
				} else if (isset($related_table_alias) && $related_table_alias && preg_match("/^".$related_table_alias."_/", $f_source)) {
					$related_column_name = preg_replace("/^".$related_table_alias."_/", "", $f_source);
					if (isset($related_columns[$related_column_name])) {
						$column_type = $related_columns[$related_column_name]["data_type"];
						$column_name = $f_source;
					}
				} else {
					$date_formats_regexp = implode("|", $date_formats);
					if (preg_match("/".$date_formats_regexp."$/", $f_source, $format_match)) {
						$f_source_wf = preg_replace("/_".$format_match[0]."$/", "", $f_source);
						if (isset($db_columns[$f_source_wf])) {
							$check_type = isset($db_columns[$f_source_wf]["data_type"]) ? $db_columns[$f_source_wf]["data_type"] : $db_columns[$f_source_wf][1];
							if (($check_type == DATE || $check_type == DATETIME)) {
								$column_name = $f_source_wf;
								$column_type = isset($db_columns[$column_name]["data_type"]) ? $db_columns[$column_name]["data_type"] : $db_columns[$column_name][1];
								$column_format = $format_match[0];
							}
						}
					}
				}

				if ($column_name) {
					if ($column_type == DATE) {
						$f_source_value = $dbe->f($column_name, DATETIME);
						if (is_array($f_source_value)) {
							if ($column_format) {
								$f_source_value = va_date(array($column_format), $f_source_value);
							} else {
								$f_source_value = va_date($date_edit_format, $f_source_value);
							}
						}
					} else if ($column_type == DATETIME) {
						$f_source_value = $dbe->f($column_name, DATETIME);
						if (is_array($f_source_value)) {
							if ($column_format) {
								$f_source_value = va_date(array($column_format), $f_source_value);
							} else {
								$f_source_value = va_date($datetime_edit_format, $f_source_value);
							}
						}
					} else {
						$f_source_value = $dbe->f($column_name);
						if ($apply_translation) {
							$f_source_value = get_translation($f_source_value);
						}
					}
					$field_value = str_replace("{".$f_source."}", $f_source_value, $field_value);
				} else {
					$field_value = str_replace("{".$f_source."}", $f_source_value, $field_value);
				}
			}
		} else {
			$field_value = $field_source;
		}

		return $field_value;
	}

	function set_db_column($column_title, $field_source, $column_checked, $column_link = "", $read_only = false)
	{
		global $t, $db, $total_columns, $table_alias, $checked_columns;

		$total_columns++;
		//$column_checked = in_array($table_alias.".".$column_name, $checked_columns) ? " checked " : "";
		$t->set_var("col", $total_columns);
		$t->set_var("field_source", htmlspecialchars($field_source));
		$t->set_var("column_link", $column_link);
		$t->set_var("column_checked", $column_checked);
		$t->set_var("column_title", htmlspecialchars($column_title));
		if ($read_only) {
			$t->sparse("read_only", false);
		} else {
			$t->set_var("read_only", "");
		}
		$t->parse("rows", true);
	}

