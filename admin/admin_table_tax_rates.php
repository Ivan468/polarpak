<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_table_tax_rates.php                                ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$table_name = $table_prefix . "tax_rates";
	$table_alias = "tr";
	$table_pk = "tax_id";
	$table_title = EMAILS_MSG;
	$min_column_allowed = 2;

	$db_columns = array(
		"tax_id" => array(ID_MSG, INTEGER, 1, false),
		"is_default" => array(DEFAULT_MSG, INTEGER, 2, false, 0),
		"tax_name" => array(TAX_NAME_MSG, TEXT, 2, true),
		"tax_type" => array(TAX_TYPE_MSG, INTEGER, 2, true, 1),
		"show_type" => array(SHOW_TAX_MSG, INTEGER, 2, false, 0),

		"country_id" => array(COUNTRY_FIELD, INTEGER, 2, true),
		"state_id" => array(STATE_FIELD, INTEGER, 2, false),
		"postal_code" => array(ZIP_FIELD, TEXT, 2, false),
		"tax_percent" => array(BASIC_TAX_MSG." (".PERCENTAGE_MSG.")", FLOAT, 2, true, 0),
		"fixed_amount" => array(BASIC_TAX_MSG." (".FIXED_AMOUNT_MSG.")", FLOAT, 2, false),

		"shipping_tax_percent" => array(SHIPPING_TAX_MSG." (".PERCENTAGE_MSG.")", FLOAT, 2, false),
		"shipping_fixed_amount" => array(SHIPPING_TAX_MSG." (".FIXED_AMOUNT_MSG.")", FLOAT, 2, false),
	);

	$db_aliases["id"] = "tax_id";

?>