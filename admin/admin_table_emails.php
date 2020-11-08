<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_table_emails.php                                   ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$table_name = $table_prefix . "newsletters_users";
	$table_alias = "e";
	$table_pk = "email_id";
	$table_title = EMAILS_MSG;
	$min_column_allowed = 1;

	$db_columns = array(
		"email_id" => array(va_message("EMAIL_ID_MSG"), INTEGER, 1, false),
		"site_id" => array(va_message("SITE_ID_MSG"), INTEGER, 1, false),
		"email" => array(va_message("EMAIL_FIELD"), TEXT, 2, false),
		"date_added" => array(va_message("DATE_ADDED_MSG"), DATETIME, 2, false),
	);

	$db_aliases["id"] = "email_id";

