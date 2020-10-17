<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_export_cron.php                                    ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	@set_time_limit (900);
	if (!isset($script_run_mode) || $script_run_mode == "") { $script_run_mode = "cron"; }
	chdir (dirname(__FILE__));
	global $site_id, $is_admin_path, $is_sub_folder, $script_run_mode, $date_formats, $language_code, $datetime_edit_format, $datetime_show_format, $date_edit_format, $date_show_format, $va_cc_data_export, $va_export_encrypt;
	if (!isset($va_export_encrypt)) { $va_export_encrypt = false; }

	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/file_functions.php");

	check_admin_security("import_export");

	$errors = "";
	$cron_templates = array();
	$sql  = " SELECT * FROM ".$table_prefix."export_templates  ";
	$sql .= " WHERE is_cronjob=1 ";
	$db->query($sql);
	while ($db->next_record()) {
		$template_id = $db->f("template_id");
		$cron_templates[$template_id] = $db->Record;
	}

	foreach ($cron_templates as $template_id => $template_data) {
		$_POST = array(); // clear request array before start export
		$cron_table = $template_data["table_name"];
		$file_path_mask = $template_data["file_path_mask"];
		$file_path_copy = $template_data["file_path_mask_copy"];
		$order_status_update = $template_data["order_status_update"];
		// build file path from the mask
		parse_file_mask($file_path_mask);
		parse_file_mask($file_path_copy);
		if (!$file_path_mask) {
			return;
		}

		mkdir_recursively($file_path_mask, $errors);
		// first save file in tmp folder if it was set
		$tmp_dir = get_setting_value($settings, "tmp_dir", ""); 
		if ($tmp_dir) {
			// generate file in tmp folder with session id in the name
			$tmp_file_path = unique_filename($tmp_dir."tmp_".session_id().basename($file_path_mask));
		} else {
			// there is no tmp folder so save file in final location
			$tmp_file_path = unique_filename($file_path_mask);
		}
		$fp = fopen($tmp_file_path, "w");

		if (!$fp) {
			// can't open file
			continue;
		}

		$_POST["table"] = $cron_table;
		$_POST["csv_delimiter"] = "comma";
		$_POST["related_delimiter"] = "row";
		$_POST["operation"] = "export";
		$_POST["order_status_update"] = $order_status_update;

		$col  = 0;
		$sql  = " SELECT field_title, field_source FROM ".$table_prefix."export_fields ";
		$sql .= " WHERE template_id=" . $db->tosql($template_id, INTEGER);
		$sql .= " ORDER BY field_order ";
		$db->query($sql);
		while ($db->next_record()) {
			$col++;
		  $field_title = $db->f("field_title");
		  $field_source = $db->f("field_source");
			// save parameters in post
			$_POST["column_title_" . $col] = $field_title;
			$_POST["field_source_" . $col] = $field_source;
			$_POST["db_column_" . $col] = "1";
			
		}
		$_POST["total_columns"] = $col;

		// set filters for template
		$sql  = " SELECT filter_parameter, filter_value FROM ".$table_prefix."export_filters ";
		$sql .= " WHERE template_id=" . $db->tosql($template_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
		  $filter_parameter = $db->f("filter_parameter");
		  $filter_value = $db->f("filter_value");
			$_POST[$filter_parameter] = $filter_value;
		}

		// import data
		include("./admin_export.php");

		if ($fp) {
			fclose($fp);
			// if there are no records we delete empty file
			if (isset($total_records) && $total_records == 0) {
				unlink($file_path);
			} else {
				// additional manipulation with created file 
				if ($tmp_dir) {
					$final_file_path = unique_filename($file_path_mask);
					// copy file from tmp folder to original destination
					if (!$va_export_encrypt) {
						copy ($tmp_file_path, $final_file_path);
						unlink($tmp_file_path);
					}
				} else {
					$final_file_path = $tmp_file_path;
				}
				if ($va_export_encrypt) {
					// some code to encrypt your export file
					//file_encrypt($tmp_file_path, $final_file_path);
					//if ($tmp_dir) { unlink($tmp_file_path); }
				}

				// make a copy if it was specified
				if (strlen($file_path_copy)) {
					$file_path_copy = unique_filename($file_path_copy);
					mkdir_recursively($file_path_copy);
					copy ($final_file_path, $file_path_copy);
				}
			}

			$ftp_upload = $template_data["ftp_upload"];
			if ($total_records > 0 && $ftp_upload) {
				// save file for transfer
				$ft = new VA_Record($table_prefix . "file_transfers");
				$ft->add_where("transfer_id", INTEGER);
				$ft->add_textbox("transfer_type", TEXT);
				$ft->add_textbox("transfer_status", TEXT);
				$ft->add_textbox("transfer_date",   DATETIME);
				$ft->add_textbox("file_path", TEXT);
      
				$ft->add_textbox("ftp_passive_mode", INTEGER);
				$ft->add_textbox("ftp_transfer_mode", TEXT);
				$ft->add_textbox("ftp_host", TEXT);
				$ft->add_textbox("ftp_port", TEXT);
				$ft->add_textbox("ftp_login", TEXT);
				$ft->add_textbox("ftp_password", TEXT);
				$ft->add_textbox("ftp_path", TEXT);
      
				$ft->add_textbox("date_added", DATETIME);
      
				// set values
				$ft->set_value("transfer_type", 1);
				$ft->set_value("transfer_status", "new");
				$ft->set_value("transfer_date",   va_time());
				$ft->set_value("file_path", $file_path);
				// set ftp values
				$ft->set_value("ftp_passive_mode", $template_data["ftp_passive_mode"]);
				$ft->set_value("ftp_transfer_mode", $template_data["ftp_transfer_mode"]);
				$ft->set_value("ftp_host", $template_data["ftp_host"]);
				$ft->set_value("ftp_port", $template_data["ftp_port"]);
				$ft->set_value("ftp_login", $template_data["ftp_login"]);
				$ft->set_value("ftp_password", $template_data["ftp_password"]);
				$ft->set_value("ftp_path", $template_data["ftp_path"]);
				// set stats values
				$ft->set_value("date_added", va_time());
				// save transfer
				$ft->insert_record();
			}
		}
	}
	
?>