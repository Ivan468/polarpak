<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_table_items_prices.php                             ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$table_name = $table_prefix . "items_prices";
	$table_alias = "pc";
	$table_pk = "price_id";
	$table_title = QUANTITY_PRICES_MSG;
	$min_column_allowed = 1;

	$db_columns = array(
		"price_id"          => array(ID_MSG, INTEGER, 1, false),
		"item_id"           => array(PRODUCT_ID_MSG, INTEGER, 3, false),
		"is_active"         => array(PROD_ACTIVE_MSG, INTEGER, 2, true, 1),
		"min_quantity"      => array(MINIMUM_ITEMS_QTY_MSG, INTEGER, 2, true, 1),
		"max_quantity"      => array(MAXIMUM_ITEMS_QTY_MSG, INTEGER, 2, true, 1),
		"price"             => array(INDIVIDUAL_PRICE_MSG, NUMBER, 2, true, 0),

		"user_type_id"      => array(USER_TYPE_MSG, INTEGER, 2, true, 1),
		"site_id"           => array(ADMIN_SITE_MSG, INTEGER, 2, true, 1),
		"discount_action"   => array(DISCOUNT_SETTINGS_MSG, INTEGER, 2, true, 0),
		"properties_discount" => array(OPTIONS_DISCOUNT_MSG, INTEGER, 2, false),
	);

?>