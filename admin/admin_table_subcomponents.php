<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_table_subcomponents.php                            ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$table_name = $table_prefix . "items_properties";
	$table_alias = "p";
	$table_pk = "property_id";
	$table_title = PROD_SUBCOMPONENTS_MSG;
	$min_column_allowed = 3;

	$db_columns = array(
		"property_id" => array(ID_MSG, INTEGER, 1, false),
		"item_id" => array(PROD_NAME_MSG, INTEGER, 2, true),
		"sub_item_id" => array(SUBCOMP_ID_MSG, INTEGER, 2, true),
		"property_name" => array(SUBCOMP_NAME_MSG, TEXT, 2, false),
		"quantity" => array(QUANTITY_MSG, INTEGER, 2, true),
		"quantity_action" => array(CART_QUANTITY_MSG, INTEGER, 2, false),
		"additional_price" => array(SUBCOMP_PRICE_MSG, FLOAT, 2, false),
		"trade_additional_price" => array(PROD_TRADE_PRICE_MSG, FLOAT, 2, false),
	);

	$db_aliases["id"] = "property_id";

?>