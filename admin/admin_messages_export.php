<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_messages_export.php                                ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	@set_time_limit (900);
	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/export_functions.php");
	include_once("./admin_common.php");

	check_admin_security("static_messages");

	$delimiters = array(array("comma", COMMA_MSG), array("tab", TAB_MSG), array("semicolon", SEMICOLON_MSG), array("vertical_bar", VERTICAL_BAR_MSG));
	$related_delimiters = array(array("row", ROWS_MSG), array("comma", COMMA_MSG), array("tab", TAB_MSG), array("space", SPACE_MSG), array("semicolon", SEMICOLON_MSG), array("vertical_bar", VERTICAL_BAR_MSG), array("newline", NEWLINE_MSG));


	$language_code = get_param("lang");
	if (!$language_code) { $language_code = get_language(); }
	$language_code = preg_replace("/[^0-9a-z_\-]/i", "", $language_code);
	$section       = get_param("section");
	$section       = preg_replace("/[^0-9a-z_\-\.]/i", "", $section);

	$eol = get_eol();
	$messages_file = "../messages/".$language_code."/".$section.".php";
	if (file_exists($messages_file)) {
		include ($messages_file);
		$csv_filename = $section . ".csv";
		header("Pragma: private");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header("Content-Encoding: UTF-8");
		header("Content-Type: text/csv; charset=UTF-8");
		header("Content-Disposition: attachment; filename=" . $csv_filename);
		header("Content-Transfer-Encoding: binary");
		echo "\xEF\xBB\xBF"; // UTF-8 BOM

		// output column names
		$code_column = va_message("CODE_MSG");
		if(preg_match("/[,;\"\n\r\t\s]/", $code_column)) {
			$code_column = "\"" . str_replace("\"", "\"\"", $code_column) . "\"";
		}
		$message_column = va_message("MESSAGE_MSG");
		if(preg_match("/[,;\"\n\r\t\s]/", $message_column)) {
			$message_column= "\"" . str_replace("\"", "\"\"", $message_column) . "\"";
		}
		echo $code_column.",".$message_column.$eol;

		foreach ($messages as $code => $message) {
			if(preg_match("/[,;\"\n\r\t\s]/", $code)) {
				$code = "\"" . str_replace("\"", "\"\"", $code) . "\"";
			}
			if(preg_match("/[,;\"\n\r\t\s]/", $message)) {
				$message= "\"" . str_replace("\"", "\"\"", $message) . "\"";
			}
			echo $code.",".$message.$eol;
			ob_flush();flush();
		}
	} else {
		echo va_message("FILE_DOESNT_EXIST_MSG").$messages_file;	
		exit;
	}
