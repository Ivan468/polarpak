<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_file_transfer.php                                  ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/record.php");

	check_admin_security("filemanager");

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_file_transfer.html");
	$date_format_msg = str_replace("{date_format}", join("", $date_edit_format), DATE_FORMAT_MSG);
	$t->set_var("date_format_msg", $date_format_msg);
	$t->set_var("date_edit_format", join("", $date_edit_format));
	$datetime_format_msg = str_replace("{date_format}", join("", $datetime_edit_format), DATE_FORMAT_MSG);
	$t->set_var("datetime_format_msg", $datetime_format_msg);
	$t->set_var("datetime_edit_format", join("", $datetime_edit_format));


	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_file_transfers_href", "admin_file_transfers.php");
	$t->set_var("admin_file_transfer_href", "admin_file_transfer.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", FILE_TRANSFER_MSG, CONFIRM_DELETE_MSG));

	$transfer_types = array(
		array("1", FTP_UPLOAD_MSG),
		array("2", FTP_DOWNLOAD_MSG),
	);
	$ftp_transfer_modes = array(
		array("ascii", FTP_ASCII_MSG),
		array("binary", FTP_BINARY_MSG),
	);

	$r = new VA_Record($table_prefix . "file_transfers");
	$r->return_page = "admin_file_transfers.php";
	$r->add_where("transfer_id", INTEGER);
	$r->add_radio("transfer_type", TEXT, $transfer_types);
	$r->change_property("transfer_type", DEFAULT_VALUE, 1);
	$r->change_property("transfer_type", BEFORE_SHOW_VALUE, "disable_transfer_type");

	$r->add_textbox("transfer_status", TEXT);
	$r->change_property("transfer_status", USE_IN_UPDATE, false);
	$r->add_textbox("transfer_date",   DATETIME, TRANSFER_DATE_MSG);
	$r->change_property("transfer_date", REQUIRED, true);
	$r->change_property("transfer_date", VALUE_MASK, $datetime_edit_format);
	$r->change_property("transfer_date", DEFAULT_VALUE, va_time());
	$r->add_textbox("file_path", TEXT, FILE_PATH_MSG);
	$r->change_property("file_path", REQUIRED, true);
	$r->change_property("file_path", TRIM, true);

	$r->add_checkbox("ftp_passive_mode", INTEGER);
	$r->add_radio("ftp_transfer_mode", TEXT, $ftp_transfer_modes);
	$r->add_textbox("ftp_host", TEXT, FTP_HOST_MSG);
	$r->change_property("ftp_host", TRIM, true);
	$r->add_textbox("ftp_port", TEXT);
	$r->add_textbox("ftp_login", TEXT);
	$r->add_textbox("ftp_password", TEXT);
	$r->add_textbox("ftp_path", TEXT);
	$r->change_property("ftp_path", TRIM, true);

	$r->add_textbox("date_added", DATETIME);
	$r->change_property("date_added", USE_IN_UPDATE, false);
	$r->add_textbox("failed_errors", TEXT);
	$r->change_property("failed_errors", USE_IN_UPDATE, false);

	$r->set_event(BEFORE_INSERT,  "before_insert_transfer");
	$r->set_event(BEFORE_VALIDATE,  "before_validate_transfer");
	$r->set_event(BEFORE_SHOW,  "before_show_transfer");

	$r->process();

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");


function before_insert_transfer()
{
	global $r;
	$r->set_value("transfer_status", "new");
	$r->set_value("date_added", va_time());

}

function before_validate_transfer()
{
	global $r;
	$transfer_type = $r->get_value("transfer_type");
	if ($transfer_type == 1 || $transfer_type == 2) {
		// ftp settings required
		$r->change_property("ftp_host", REQUIRED, true);
	}
}

function before_show_transfer()
{
	global $r, $t;
	$failed_errors = $r->get_value("failed_errors");
	if ($failed_errors) {
		$r->errors = $failed_errors;
	}
}

	function disable_transfer_type($parameters)
	{
		global $r, $t;
		$ftp_download = false;
		if (defined("FTP_DOWNLOAD")) { 
			$ftp_download = FTP_DOWNLOAD;
		}

		$current_value = $parameters["current_value"];
		if ($current_value == "2" && !$ftp_download) {
			$t->set_var("transfer_type_disabled", "disabled");
		} else {
			$t->set_var("transfer_type_disabled", "");
		}
	}


?>
