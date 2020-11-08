<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_table_users.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$table_name = $table_prefix . "users";
	$table_alias = "u";
	$table_pk = "user_id";
	$table_title = USERS_MSG;
	$min_column_allowed = 2;

	$default_user_type = "";
	$db->query("SELECT type_id FROM " . $table_prefix . "user_types WHERE is_default=1");
	if ($db->next_record()) {
		$default_user_type = $db->f("type_id");
	} else {
		$sql = "SELECT type_id FROM " . $table_prefix . "user_types ";
		$default_user_type = get_db_value($sql);
	}

	$db_columns = array(
		"user_id" => array(
			"title" => "USER_ID_MSG", "data_type" => INTEGER, "field_type" => WHERE_DB_FIELD, "required" => false, 
			"control" => TEXTBOX, 
			"aliases" => array("id"),
		),
		"login" => array(LOGIN_BUTTON, TEXT, 2, true),
		"affiliate_code" => array(AFFILIATE_CODE_FIELD, TEXT, 2, false, ""),
		"password" => array(PASSWORD_FIELD, TEXT, 2, true),
		"nickname" => array(NICKNAME_FIELD, TEXT, 2, false, ""),
		"friendly_url" => array(FRIENDLY_URL_MSG, TEXT, 2, false, ""),

		"name" => array(FULL_NAME_FIELD, TEXT, 2, false, ""),
		"first_name" => array(FIRST_NAME_FIELD, TEXT, 2, false, ""),
		"middle_name" => array(MIDDLE_NAME_FIELD, TEXT, 2, false, ""),
		"last_name" => array(LAST_NAME_FIELD, TEXT, 2, false, ""),
		"company_id" => array(COMPANY_ID_MSG, INTEGER, 3, false),
		"company_name" => array(COMPANY_NAME_FIELD, TEXT, 2, false),
		"email" => array(
			"title" => "EMAIL_FIELD", "data_type" => TEXT, "field_type" => USUAL_DB_FIELD, "required" => false, 
			"control" => TEXTBOX, "sql_null" => false,
		),
		"address1" => array(
			"title" => "{STREET_FIRST_FIELD}", "data_type" => TEXT, "field_type" => USUAL_DB_FIELD, "required" => false, 
			"control" => TEXTBOX, "table" => $table_prefix."countries",
			"aliases" => array("{ADDRESS_MSG} 1", "{ADDRESS_MSG}", "{STREET_ADDRESS_FIRST_LINE_MSG}"),
		),
		"address2" => array(
			"title" => "{STREET_SECOND_FIELD}", "data_type" => TEXT, "field_type" => USUAL_DB_FIELD, "required" => false, 
			"control" => TEXTBOX, "table" => $table_prefix."countries",
			"aliases" => array("{ADDRESS_MSG} 2", "{STREET_ADDRESS_SECOND_LINE_MSG}"),
		),
		"address3" => array(
			"title" => "{STREET_THIRD_FIELD}", "data_type" => TEXT, "field_type" => USUAL_DB_FIELD, "required" => false, 
			"control" => TEXTBOX, "table" => $table_prefix."countries",
			"aliases" => array("{ADDRESS_MSG} 3"),
		),
		"city" => array(CITY_FIELD, TEXT, 2, false),
		"province" => array("PROVINCE_FIELD", TEXT, 2, false),
		"state_id" => array("{STATE_FIELD} ({ID_MSG})", INTEGER, 2, false, ''),
		"state_code" => array("STATE_CODE_MSG", TEXT, 2, false, ''),
		"zip" => array(ZIP_MSG, TEXT, 2, false),
		"country_id" => array("{COUNTRY_FIELD} ({ID_MSG})", INTEGER, 2, false, ''),
		"country_code" => array("COUNTRY_CODE_MSG", TEXT, 2, false, ''),
		"phone" => array(PHONE_FIELD, TEXT, 2, false),
		"daytime_phone" => array(DAYTIME_PHONE_FIELD, TEXT, 2, false),
		"evening_phone" => array(EVENING_PHONE_FIELD, TEXT, 2, false),
		"cell_phone" => array(CELL_PHONE_FIELD, TEXT, 2, false),
		"fax" => array(FAX_FIELD, TEXT, 2, false),

		"birth_year" => array(BIRTH_YEAR_MSG, INTEGER, 2, false),
		"birth_month" => array(BIRTH_MONTH_MSG, INTEGER, 2, false),
		"birth_day" => array(BIRTH_DAY_MSG, INTEGER, 2, false),

		"delivery_name" => array(va_constant("DELIVERY_MSG")." ".va_constant("FULL_NAME_FIELD"), TEXT, 2, false),
		"delivery_first_name" => array(va_constant("DELIVERY_MSG")." ".va_constant("FIRST_NAME_FIELD"), TEXT, 2, false),
		"delivery_middle_name" => array(va_constant("DELIVERY_MSG")." ".va_constant("MIDDLE_NAME_FIELD"), TEXT, 2, false),
		"delivery_last_name" => array(va_constant("DELIVERY_MSG")." ".va_constant("LAST_NAME_FIELD"), TEXT, 2, false),
		"delivery_company_id" => array(DELIVERY_COMPANY_ID_MSG, INTEGER, 3, false),
		"delivery_company_name" => array(DELIVERY_COMPANY_NAME_MSG, TEXT, 2, false),
		"delivery_email" => array("DELIVERY_EMAIL_MSG", TEXT, 2, false),
		"delivery_address1" => array(
			"title" => "{DELIVERY_MSG} {STREET_FIRST_FIELD}", "data_type" => TEXT, "field_type" => USUAL_DB_FIELD, "required" => false, 
			"control" => TEXTBOX, "table" => $table_prefix."countries",
			"aliases" => array("{DELIVERY_MSG} {ADDRESS_MSG} 1", "{DELIVERY_MSG} {ADDRESS_MSG}", "{DELIVERY_ADDRESS_FIRST_MSG}"),
		),
		"delivery_address2" => array(
			"title" => "{DELIVERY_MSG} {STREET_SECOND_FIELD}", "data_type" => TEXT, "field_type" => USUAL_DB_FIELD, "required" => false, 
			"control" => TEXTBOX, "table" => $table_prefix."countries",
			"aliases" => array("{DELIVERY_MSG} {ADDRESS_MSG} 2", "{DELIVERY_ADDRESS_SEC_MSG}"),
		),
		"delivery_address3" => array(
			"title" => "{DELIVERY_MSG} {STREET_THIRD_FIELD}", "data_type" => TEXT, "field_type" => USUAL_DB_FIELD, "required" => false, 
			"control" => TEXTBOX, "table" => $table_prefix."countries",
			"aliases" => array("{DELIVERY_MSG} {ADDRESS_MSG} 3"),
		),
		"delivery_city" => array(DELIVERY_CITY_MSG, TEXT, 2, false),
		"delivery_province" => array(DELIVERY_PROVINCE_MSG, TEXT, 2, false),
		"delivery_state_id" => array("{DELIVERY_STATE_MSG} ({ID_MSG})", INTEGER, 2, false, ''),
		"delivery_state_code" => array("DELIVERY_STATE_CODE_MSG", TEXT, 2, false, ''),
		"delivery_zip" => array(DELIVERY_ZIP_MSG, TEXT, 2, false),
		"delivery_country_id" => array("{DELIVERY_COUNTRY_MSG} ({ID_MSG})", INTEGER, 2, false, ''),
		"delivery_country_code" => array("DELIVERY_COUNTRY_CODE_MSG", TEXT, 2, false, ''),
		"delivery_phone" => array(DELIVERY_PHONE_MSG, TEXT, 2, false),
		"delivery_daytime_phone" => array(DELIVERY_DAY_PHONE_MSG, TEXT, 2, false),
		"delivery_evening_phone" => array(DELIVERY_EVENING_PHONE_MSG, TEXT, 2, false),
		"delivery_cell_phone" => array(DELIVERY_CELL_PHONE_MSG, TEXT, 2, false),
		"delivery_fax" => array(DELIVERY_FAX_MSG, TEXT, 2, false),

		"paypal_account" => array(PAYPAL_ACCOUNT_FIELD, TEXT, 2, false),
		"tax_free" => array(TAX_FREE_MSG, TEXT, 2, false),
		"tax_id" => array(TAX_ID_FIELD, TEXT, 2, false),
		"short_description" => array(SHORT_DESCRIPTION_MSG, TEXT, 2, false),
		"full_description" => array(FULL_DESCRIPTION_MSG, TEXT, 2, false),

		"user_type_id" => array(USER_TYPE_MSG, INTEGER, 2, true, $default_user_type),
		"is_approved" => array(IS_APPROVED_MSG, INTEGER, 2, true, 1),
		"registration_date" => array(REGISTRATION_DATE_MSG, DATETIME, 2, true, va_time()),
		"last_visit_date" => array(LAST_ACTIVITY_MSG, DATETIME, 2, true, va_time()),
		"modified_date" => array(MODIFIED_DATE_MSG, DATETIME, 4, true, va_time())
	);


	$sql  = " SELECT property_id, property_order, property_code, property_name, control_type ";
	$sql .= " FROM " . $table_prefix . "user_profile_properties ";
	$sql .= " ORDER BY property_order, property_id ";
	$db->query($sql);
	while ($db->next_record()) {
		$property_id = $db->f("property_id");
		$property_order = $db->f("property_order");
		$property_code = $db->f("property_code");
		$property_name = $db->f("property_name");
		$control_type = $db->f("control_type");
		$column_name = "user_property_" . $property_id;

		$db_columns[$column_name] = array(
			"title" => get_translation($property_name), "data_type" => TEXT, "field_type" => USUAL_DB_FIELD, "required" => false, 
			"control" => TEXTBOX, "related_table" => $table_prefix."users_properties", 
			"property_id" => $property_id, "property_order" => $property_order, "property_code" => $property_code, 
			"property_name" => $property_name, "control_type" => $control_type,
		);
	}

	$db_aliases["country"] = "country_code";
	$db_aliases["state"] = "state_code";

?>