<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_table_items.php                                    ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$table_name = $table_prefix . "items";
	$table_alias = "i";
	$table_pk = "item_id";
	$table_title = PRODUCTS_MSG;
	$min_column_allowed = 1;

	$db_columns = array(
		"item_id" => array(
			"title" => PRODUCT_ID_MSG, "data_type" => INTEGER, "field_type" => 1, "required" => false,
			"control" => TEXTBOX, "size" => 10, 
		),
		"user_id" => array(USER_ID_MSG, INTEGER, 2, false, 0),
		"supplier_id" => array(
			"title" => SUPPLIER_MSG, "data_type" => INTEGER, "field_type" => 3, "required" => false, 
			"control" => LISTBOX,  "default" => 0, 
			"values_sql" => "SELECT supplier_id, supplier_name FROM ".$table_prefix."suppliers",
		),
		"is_showing" => array(
			"title" => FOR_SALES_MSG, "data_type" => INTEGER, "field_type" => 2, "required" => true, 
			"control" => RADIOBUTTON, 
			"values" => array(array("1", YES_MSG), array("0", NO_MSG)),
			"default" => 1,
		),
		"is_approved" => array(
			"title" => IS_APPROVED_MSG, "data_type" => INTEGER, "field_type" => 2, "required" => true, 
			"control" => RADIOBUTTON, 
			"values" => array(array("1", YES_MSG), array("0", NO_MSG)),
			"default" => 1,
		),
		"item_code" => array(
			"title" => PROD_CODE_MSG, "data_type" => TEXT, "field_type" => 2, "required" => false,
			"control" => TEXTBOX, "size" => 20, 
		),

		"item_name" => array(PROD_NAME_MSG, TEXT, 2, false),
		"friendly_url" => array(FRIENDLY_URL_MSG, TEXT, 2, false),
		"item_order" => array(PROD_ORDER_MSG, INTEGER, 2, true, 1),

		"item_type_id" => array(
			"title" => PROD_TYPE_MSG, "data_type" => INTEGER, "field_type" => 3, "required" => true, 
			"control" => LISTBOX, "default" => 1,
			"values_sql" => "SELECT item_type_id, item_type_name FROM ".$table_prefix."item_types ",
		),

		"property_name" => array(
			"title" => "PRODUCT_OPTION_MSG", "data_type" => TEXT, "field_type" => RELATED_DB_FIELD, "required" => false, 
			"related_table" => $table_prefix."items_properties",
		),
		"category_name" => array(
			"title" => "CATEGORY_MSG", "data_type" => TEXT, "field_type" => RELATED_DB_FIELD, "required" => false, 
			"related_table" => $table_prefix."categories",
		),
		"manufacturer_id" => array(
			"title" => MANUFACTURER_ID_MSG, "data_type" => INTEGER, "field_type" => 3, "required" => false, 
			"control" => LISTBOX, 
			"values_sql" => "SELECT manufacturer_id, manufacturer_name FROM ".$table_prefix."manufacturers",
		),
		"manufacturer_code" => array(
			"title" => MANUFACTURER_CODE_MSG, "data_type" => TEXT, "field_type" => 2, "required" => false,
			"control" => TEXTBOX, "size" => 20, 
		),
		"manufacturer_name" => array(
			"title" => "MANUFACTURER_NAME_MSG", "data_type" => TEXT, "field_type" => RELATED_DB_FIELD, "required" => false, 
			"related_table" => $table_prefix."manufacturers",
		),
		"issue_date" => array(ISSUE_DATE_MSG, DATE, 2, false),
		"is_compared" => array(PROD_ALLOWED_COMPARISON_MSG, INTEGER, 2, false),
		"tax_free" => array(TAX_FREE_MSG, INTEGER, 2, false),
		"language_code" => array(LANGUAGE_MSG, TEXT, 2, false, ""),
		"price" => array(PRICE_MSG, FLOAT, 2, true, 0),
		"is_price_edit" => array(IS_PRICE_EDIT_MSG, INTEGER, 2, false, 0),
		"properties_price" => array(PROD_OPTIONS_PRICE_MSG, FLOAT, 2, false, 0),
		"trade_properties_price" => array(OPTIONS_TRADE_PRICE_MSG, FLOAT, 2, false, 0),
		"is_sales" => array(IS_DISCOUNT_ACTIVE_MSG, INTEGER, 2, true, 0),
		"sales_price" => array(PROD_DISCOUNT_PRICE_MSG, FLOAT, 2, true, 0),
		"trade_price" => array(PROD_TRADE_PRICE_MSG, FLOAT, 2, true, 0),
		"trade_sales" => array(PROD_DISCOUNT_TRADE_MSG, FLOAT, 2, true, 0),
		"discount_percent" => array(PROD_DISCOUNT_PERCENT_MSG, FLOAT, 2, false),
		"buying_price" => array(PROD_BUYING_PRICE_MSG, FLOAT, 2, false),
		"merchant_fee_type" => array(MERCHANT_FREE_TYPE_MSG, INTEGER, 2, false),
		"merchant_fee_amount" => array(MERCHANT_FEE_AMOUNT_MSG, FLOAT, 2, false),
		"affiliate_commission_type" => array(AFFILIATE_COMMISSION_TYPE_MSG, INTEGER, 2, false),
		"affiliate_commission_amount" => array(AFFILIATE_COMMISSION_AMOUNT_MSG, FLOAT, 2, false),

		"short_description" => array(
			"title" => SHORT_DESCRIPTION_MSG, "data_type" => TEXT, "field_type" => 2, "required" => false,
			"control" => TEXTAREA, 
		),

		"highlights" => array(HIGHLIGHTS_MSG, TEXT, 2, false),
		"full_desc_type" => array(FULL_DESCRIPTION_TYPE_MSG, INTEGER, 2, false),
		"full_description" => array(FULL_DESCRIPTION_MSG, TEXT, 2, false),
		"a_title" => array(A_TITLE_MSG, TEXT, 2, false),
		"meta_title" => array(META_TITLE_MSG, TEXT, 2, false),
		"meta_keywords" => array(META_KEYWORDS_MSG, TEXT, 2, false),
		"meta_description" => array(META_DESCRIPTION_MSG, TEXT, 2, false),
		"is_special_offer" => array(IS_SPECIAL_OFFER_MSG, INTEGER, 2, true, 0),
		"special_offer" => array(SPECIAL_OFFER_MSG, TEXT, 2, false),
		"tiny_image" => array(IMAGE_TINY_MSG, TEXT, 2, false),
		"tiny_image_alt" => array(IMAGE_TINY_ALT_MSG, TEXT, 2, false),
		"small_image" => array(IMAGE_SMALL_MSG, TEXT, 2, false),
		"small_image_alt" => array(IMAGE_SMALL_ALT_MSG, TEXT, 2, false),
		"big_image" => array(IMAGE_LARGE_MSG, TEXT, 2, false),
		"big_image_alt" => array(IMAGE_LARGE_ALT_MSG, TEXT, 2, false),
		"super_image" => array(IMAGE_SUPER_MSG, TEXT, 2, false),
		"template_name" => array(CUSTOM_TEMPLATE_MSG, TEXT, 2, false),
		"preview_url" => array(PROD_PREVIEW_URL_MSG, TEXT, 2, false),
		"packages_number" => array(PACKAGES_NUMBER_MSG, FLOAT, 2, false),
		"weight" => array(WEIGHT_MSG, FLOAT, 2, false),
		"actual_weight" => array(ACTUAL_WEIGHT_MSG, FLOAT, 2, false),
		"width" => array(WIDTH_MSG, FLOAT, 2, false),
		"height" => array(HEIGHT_MSG, FLOAT, 2, false),
		"length" => array(LENGTH_MSG, FLOAT, 2, false),
		"use_stock_level" => array(USE_STOCK_MSG, INTEGER, 2, true, 1),
		"stock_level" => array(STOCK_LEVEL_MSG, INTEGER, 2, false),
		"hide_out_of_stock" => array(HIDE_OUT_STOCK_MSG, INTEGER, 2, true, 0),
		"disable_out_of_stock" => array(DISABLE_OUT_STOCK_MSG, INTEGER, 2, true, 0),
		"min_quantity" => array(MINIMUM_ITEMS_QTY_MSG, INTEGER, 2, false),
		"max_quantity" => array(MAXIMUM_ITEMS_QTY_MSG, INTEGER, 2, false),
		"quantity_increment" => array(QTY_INCREMENT_MSG, INTEGER, 2, false),		
		"generate_serial" => array(SERIAL_GENERATE_MSG, INTEGER, 2, false),
		"serial_period" => array(SERIAL_PERIOD_MSG, INTEGER, 2, false),
		"activations_number" => array(ACTIVATION_MAX_NUMBER_MSG, INTEGER, 2, false),
		"is_recurring" => array(RECURRING_ACTIVATE_MSG, INTEGER, 2, false, 0),
		"recurring_period" => array(RECURRING_PERIOD_MSG, INTEGER, 2, false),
		"recurring_interval" => array(RECURRING_INTERVAL_MSG, INTEGER, 2, false),
		"recurring_payments_total" => array(RECURRING_PAYMENTS_TOTAL_MSG, INTEGER, 2, false),
		"recurring_start_date" => array(RECURRING_START_DATE_MSG, DATE, 2, false),
		"recurring_end_date" => array(RECURRING_END_DATE_MSG, DATE, 2, false),
		"shipping_in_stock" => array(IN_STOCK_AVAILABILITY_MSG, INTEGER, 2, false),
		"shipping_out_stock" => array(OUT_STOCK_AVAILABILITY_MSG, INTEGER, 2, false),
		"shipping_rule_id" => array(SHIPPING_RESTRICTIONS_MSG, INTEGER, 2, false),
		"is_shipping_free" => array(FREE_SHIPPING_MSG, INTEGER, 2, false),
		"shipping_cost" => array(SHIPPING_COST_MSG, FLOAT, 2, false),
		"shipping_modules_default" => array(SHIPPING_MODULES_MSG." (".IS_DEFAULT.")", INTEGER, 2, false, 1),
		"shipping_modules_ids" => array(SHIPPING_MODULES_MSG, TEXT, 2, false),

		"total_views" => array(TOTAL_VIEWS_MSG, INTEGER, 2, false, 0),
		"votes" => array(TOTAL_VOTES_MSG, INTEGER, 2, false),
		"points" => array(TOTAL_POINTS_MSG, INTEGER, 2, false),
		"rating" => array(RATING_MSG, FLOAT, 2, false),
		"notes" => array(NOTES_MSG, TEXT, 2, false),
		"buy_link" => array(DIRECT_BUY_LINK_MSG, TEXT, 2, false),
		"feature_name" => array(
			"title" => "ADMIN_SPECIFICATION_MSG", "data_type" => TEXT, "field_type" => RELATED_DB_FIELD, "required" => false, 
			"related_table" => $table_prefix."features",
		),
		"google_base_type_id" => array(GOOGLE_BASE_PRODUCT_TYPE_MSG, INTEGER, 3, true, 1),

		"sites_all" => array(
			"title" => SITES_ALL_MSG, "data_type" => INTEGER, "field_type" => 2, "required" => false, 
			"control" => CHECKBOX, 
			"default" => 1,
		),
		"sites" => array(
			"title" => SITES_MSG, "data_type" => TEXT, "field_type" => RELATED_DB_FIELD, "required" => false, 
			"control" => SELECT_MULTIPLE, "related_table" => $table_prefix."items_sites",
		),
	);

	$db_aliases["id"] = "item_id";
	$db_aliases["product_id"] = "item_id";
	$db_aliases["item id"] = "item_id";
	$db_aliases["title"] = "item_name";
	$db_aliases["item"] = "item_name";
	$db_aliases["product"] = "item_name";
	$db_aliases["product title"] = "item_name";
	$db_aliases["product name"] = "item_name";
	$db_aliases["item title"] = "item_name";
	$db_aliases["item name"] = "item_name";
	$db_aliases["product_title"] = "item_name";
	$db_aliases["product_name"] = "item_name";
	$db_aliases["item_title"] = "item_name";
	$db_aliases["code"] = "manufacturer_code";
	$db_aliases["product shown"] = "is_showing";
	$db_aliases["product_shown"] = "is_showing";
	$db_aliases["item shown"] = "is_showing";
	$db_aliases["item_shown"] = "is_showing";
	$db_aliases["is show"] = "is_showing";
	$db_aliases["is shown"] = "is_showing";
	$db_aliases["is showning"] = "is_showing";
	$db_aliases["is_show"] = "is_showing";
	$db_aliases["is_shown"] = "is_showing";
	$db_aliases["is_showning"] = "is_showing";
	$db_aliases["item order"] = "item_order";
	$db_aliases["product_order"] = "item_order";
	$db_aliases["type"] = "item_type_id";
	$db_aliases["item_type"] = "item_type_id";
	$db_aliases["item type"] = "item_type_id";
	$db_aliases["quantity"] = "stock_level";

