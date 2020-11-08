<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_table_serials.php                                  ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$table_name = $table_prefix . "items_serials";
	$table_alias = "sn";
	$table_pk = "serial_id";
	$table_title = ADMIN_SERIAL_NUMBERS_MSG;
	$min_column_allowed = 2;

	$db_columns = array(
		"serial_id" => array(ID_MSG, INTEGER, 1, false),
		"item_id" => array(PRODUCT_ID_MSG, INTEGER, 2, true, 0),
		"serial_number" => array(SERIAL_NUMBER_COLUMN, TEXT, 2, true),
		"used" => array(USED_MSG, INTEGER, 2, false, 0),
	);

	$db_aliases["id"] = "serial_id";

?>