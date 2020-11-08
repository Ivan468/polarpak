<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_table_newsletters_emails.php                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$table_name = $table_prefix . "newsletters_emails";
	$table_alias = "e";
	$table_pk = "email_id";
	$table_title = NEWSLETTER_MSG." ".EMAILS_MSG;
	$min_column_allowed = 1;

	$db_columns = array(
		"email_id" => array(EMAIL_ID_MSG, INTEGER, 1, false),
		"newsletter_id" => array(NEWSLETTER_MSG." ".ID_MSG, INTEGER, 2, true),
		"user_email" => array(EMAIL_FIELD, TEXT, 2, true),
		"user_name" => array(NAME_MSG, TEXT, 2, false),
	);

	$db_aliases["id"] = "email_id";

?>