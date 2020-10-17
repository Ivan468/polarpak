<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_table_items_properties_values.php                  ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/



	$table_name = $table_prefix . "items_properties_values";
	$table_alias = "ipv";
	$table_pk = "item_property_id";
	$table_title = OPTIONS_VALUES_MSG;
	$min_column_allowed = 1;

	$db_columns = array(
		"item_property_id"  => array(VALUE_MSG." (".ID_MSG.")", INTEGER, 1, false),
		"property_id"       => array(OPTION_MSG." (".ID_MSG.")", INTEGER, 3, false),

		"items_item_name" => array(
			"title" => PRODUCT_MSG." (".NAME_MSG.")", "data_type" => TEXT, "field_type" => RELATED_DB_FIELD, "required" => false,
			"read_only" => true, "related_table" => $table_prefix . "items", 
		),
		"items_is_showing" => array(
			"title" => PRODUCT_MSG." (".FOR_SALES_MSG.")", "data_type" => TEXT, "field_type" => RELATED_DB_FIELD, "required" => false,
			"read_only" => true, "related_table" => $table_prefix . "items", 
		),
		"items_is_approved" => array(
			"title" => PRODUCT_MSG." (".IS_APPROVED_MSG.")", "data_type" => TEXT, "field_type" => RELATED_DB_FIELD, "required" => false,
			"read_only" => true, "related_table" => $table_prefix . "items", 
		),
		"items_item_code" => array(
			"title" => PRODUCT_MSG." (".CODE_MSG.")", "data_type" => TEXT, "field_type" => RELATED_DB_FIELD, "required" => false,
			"read_only" => true, "related_table" => $table_prefix . "items", 
		),
		"items_manufacturer_code" => array(
			"title" => PRODUCT_MSG." (".MANUFACTURER_CODE_MSG.")", "data_type" => TEXT, "field_type" => RELATED_DB_FIELD, "required" => false,
			"read_only" => true, "related_table" => $table_prefix . "items", 
		),
		"properties_property_name" => array(
			"title" => OPTION_NAME_MSG, "data_type" => TEXT, "field_type" => RELATED_DB_FIELD, "required" => false,
			"read_only" => true, "related_table" => $table_prefix . "item_properties", 
		),

		"item_code"            => array(OPTION_MSG." (".CODE_MSG.")", TEXT, 2, false),
		"manufacturer_code"    => array(OPTION_MSG." (".MANUFACTURER_CODE_MSG.")", TEXT, 2, false),

		"value_order"       => array(VALUE_MSG." (".ADMIN_ORDER_MSG.")", INTEGER, 2, true, 1),
		"property_value"    => array(OPTION_VALUE_MSG, TEXT, 2, true, 1),

		"download_period"      => array(DOWNLOAD_PERIOD_MSG, INTEGER, 2, false),
		"download_files_ids"   => array(DOWNLOADABLE_FILES_MSG, TEXT, 2, false),

		"buying_price"           => array(OPTION_MSG." (".PROD_BUYING_PRICE_MSG.")", NUMBER, 2, false),
		"additional_price"       => array(OPTION_MSG." (".PRICE_MSG.")", NUMBER, 2, false),
		"trade_additional_price" => array(OPTION_MSG." (".PROD_TRADE_PRICE_MSG.")", NUMBER, 2, false),
		"percentage_price"       => array(PERCENTAGE_PRICE_MSG, NUMBER, 2, false),

		"actual_weight"         => array(ACTUAL_WEIGHT_MSG, NUMBER, 2, false),
		"additional_weight"     => array(ADDITIONAL_WEIGHT_MSG, NUMBER, 2, false),

		"use_stock_level"      => array(USE_STOCK_MSG, INTEGER, 2, false, 0),
		"stock_level"          => array(STOCK_LEVEL_MSG, INTEGER, 2, false),
		"hide_out_of_stock"    => array(HIDE_IF_OUT_STOCK_MSG, INTEGER, 2, false, 0),
		"hide_value"           => array(HIDE_MSG, INTEGER, 2, false, 0),
		"is_default_value"     => array(DEFAULT_MSG, INTEGER, 2, false, 0),

		"sub_item_id"          => array(SUBCOMPONENT_MSG." (".ID_MSG.")", INTEGER, 3, false, 1),
		"quantity"             => array(SUBCOMPONENT_MSG." (".QUANTITY_MSG.")", INTEGER, 2, false),

		"image_tiny"  => array(IMAGE_TINY_MSG, TEXT, 2, false),
		"image_small" => array(IMAGE_SMALL_MSG, TEXT, 2, false),
		"image_large" => array(IMAGE_LARGE_MSG, TEXT, 2, false),
		"image_super" => array(IMAGE_SUPER_MSG, TEXT, 2, false),


	);

?>