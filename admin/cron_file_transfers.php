<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  cron_file_transfers.php                                  ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	@set_time_limit (900);
	chdir (dirname(__FILE__));
	include_once("./admin_config.php");
	include_once("../includes/common.php");
	include_once("../includes/record.php");
	include_once("./admin_common.php");
	include_once("../includes/parameters.php");

	$error_message = ""; $success_message = "";
	check_admin_security("filemanager");

	$current_ts = va_timestamp();
	$files_transferred = 0;
	$files_failed = 0;

	$ftp_download = false;
	if (defined("FTP_DOWNLOAD")) { 
		$ftp_download = FTP_DOWNLOAD;
	}

	// check if there are any files available to transfer
	$transfer_sql  = " SELECT ft.* FROM " . $table_prefix . "file_transfers ft ";
	$transfer_sql .= " WHERE (ft.transfer_status='new' OR ft.transfer_status='failed' OR ft.transfer_status='uploading' OR ft.transfer_status='downloading') ";
	$transfer_sql .= " AND (ft.transfer_date IS NULL OR ft.transfer_date<=" . $db->tosql($current_ts, DATETIME) . ") ";
	$db->RecordsPerPage = 1;
	$db->PageNumber = 1;
	$db->query($transfer_sql);
	if ($db->next_record()) {
		do {

			$transfer_id = $db->f("transfer_id");
			$transfer_type = $db->f("transfer_type");
			$transfer_date = $db->f("transfer_date", DATETIME);
			$failed_attempts = $db->f("failed_attempts");
			$failed_errors = $db->f("failed_errors");

			$transfer_data = $db->Record;

			$file_transferred = false; $transfer_error = "";
			if ($transfer_type == 1) {
				// start ftp upload
				$sql  = " UPDATE " . $table_prefix . "file_transfers ";
				$sql .= " SET transfer_status='uploading' ";
				$sql .= " , transfer_date=" . $db->tosql(va_time(), DATETIME);
				$sql .= " WHERE transfer_id=" . $db->tosql($transfer_id, INTEGER);
				$db->query($sql);

				// get ftp data
				$ftp_host = $transfer_data["ftp_host"];
				$ftp_port = $transfer_data["ftp_port"]; 
				$ftp_login = $transfer_data["ftp_login"];
				$ftp_password = $transfer_data["ftp_password"];
				$ftp_passive_mode = $transfer_data["ftp_passive_mode"];
				$ftp_transfer_mode = $transfer_data["ftp_transfer_mode"];
				$ftp_path = trim($transfer_data["ftp_path"]);
				$file_path = $transfer_data["file_path"];

				if (preg_match("/^sftp:\/\//i", $ftp_host)) {
					// use curl for SFTP connection
					$ch = curl_init();

					$file_name = basename($file_path);
					$remote_url = $ftp_host;
					if (!preg_match("/\/$/i", $ftp_host) && !preg_match("/^\//i", $ftp_path)) {
						$remote_url .= "/";
					}
					$remote_url .= $ftp_path;
					$remote_url .= $file_name;

					curl_setopt($ch, CURLOPT_URL, $remote_url);

					if ($ftp_port) {
						curl_setopt($ch, CURLOPT_PORT, $ftp_port);
					}
					if ($ftp_login || $ftp_password) {
						curl_setopt($ch, CURLOPT_USERPWD, $ftp_login.":".$ftp_password);
					}
					curl_setopt($ch, CURLOPT_UPLOAD, 1);
					curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
					$fp = fopen($file_path, 'r');
					curl_setopt($ch, CURLOPT_INFILE, $fp);
					curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file_path));
					curl_exec ($ch);

					$error_no = curl_errno($ch);
					if ($error_no == 0) {
						$file_transferred = true;
					} else {
						$transfer_error = curl_error($ch);
					}
					curl_close ($ch);
				} else {
					// use standard FTP connection
					if (!$ftp_port) { $ftp_port = 21; }
					// connect
					$ftp_stream = @ftp_connect ($ftp_host, $ftp_port, 90);
					if (!$ftp_stream) {
						$transfer_error = "Connect error: $ftp_host<br>\n";
					} else {
						// login
						if (!@ftp_login($ftp_stream, $ftp_login, $ftp_password)) {
							$transfer_error = "Couldn't connect as $ftp_login<br>\n";
						}
					}
					// set passive mode
					if (!$transfer_error) {
						if ($ftp_passive_mode) {
							ftp_pasv ($ftp_stream, true);
						}
					}		
					// change directory
					if (!$transfer_error && strlen($ftp_path)) {
						if (!@ftp_chdir($ftp_stream, $ftp_path)) {
							$transfer_error = "Couldn't change directory: $ftp_path<br>\n";
						}
					}
					// upload file
					if (!$transfer_error) {
						$transfer_mode = ($ftp_transfer_mode == "ascii") ? FTP_ASCII : FTP_BINARY;
						$file_name = basename($file_path);
						if (@ftp_put($ftp_stream, $file_name, $file_path, $transfer_mode)) {
							$file_transferred = true;
						} else {
							$transfer_error = "There was a problem while uploading $file_name<br>\n";
						}
					}
					// close connection
					if ($ftp_stream) {
						@ftp_close ($ftp_stream);
					}
				}
			} else if ($ftp_download && $transfer_type == 2) {

				// start ftp upload
				$sql  = " UPDATE " . $table_prefix . "file_transfers ";
				$sql .= " SET transfer_status='downloading' ";
				$sql .= " , transfer_date=" . $db->tosql(va_time(), DATETIME);
				$sql .= " WHERE transfer_id=" . $db->tosql($transfer_id, INTEGER);
				$db->query($sql);

				// get ftp data
				$ftp_host = $transfer_data["ftp_host"];
				$ftp_port = $transfer_data["ftp_port"]; 
				$ftp_login = $transfer_data["ftp_login"];
				$ftp_password = $transfer_data["ftp_password"];
				$ftp_passive_mode = $transfer_data["ftp_passive_mode"];
				$ftp_transfer_mode = $transfer_data["ftp_transfer_mode"];
				$ftp_path = trim($transfer_data["ftp_path"]);
				$file_path = $transfer_data["file_path"];

				// download file
				$fp = fopen($file_path, 'w'); // create a new file

				$remote_url = "";
				if (!preg_match("/^\w{3,6}:\/\//i", $ftp_host)) {
					$remote_url = "ftp://"; // use FTP by default
				}
				$remote_url .= $ftp_host;
				if (!preg_match("/\/$/i", $ftp_host) && !preg_match("/^\//i", $ftp_path)) {
					$remote_url .= "/";
				}
				$remote_url .= $ftp_path;

				if (preg_match("/^sftp:\/\//i", $ftp_host)) {


					// use curl for SFTP connection
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $remote_url);

					if ($ftp_port) {
						curl_setopt($ch, CURLOPT_PORT, $ftp_port);
					}
					if ($ftp_login || $ftp_password) {
						curl_setopt($ch, CURLOPT_USERPWD, $ftp_login.":".$ftp_password);
					}
					//curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
					curl_setopt($ch, CURLOPT_FILE, $fp);
					curl_exec ($ch);

					$error_no = curl_errno($ch);
					if ($error_no == 0) {
						$file_transferred = true;
					} else {
						$transfer_error = curl_error($ch);
					}
					curl_close ($ch);
				} else {
					// use curl for FTP connection
					$ch = curl_init();

					curl_setopt($ch, CURLOPT_URL, $remote_url);
					if (!$ftp_port) { $ftp_port = 21; }
					if ($ftp_port) {
						curl_setopt($ch, CURLOPT_PORT, $ftp_port);
					}
					if ($ftp_login || $ftp_password) {
						curl_setopt($ch, CURLOPT_USERPWD, $ftp_login.":".$ftp_password);
					}
					//curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_FTP);
					curl_setopt($ch, CURLOPT_FILE, $fp);
					curl_exec ($ch);

					$error_no = curl_errno($ch);
					if ($error_no == 0) {
						$file_transferred = true;
					} else {
						$transfer_error = curl_error($ch);
					}
					curl_close ($ch);
				}
			}

			if ($file_transferred) {
				$files_transferred++;
			}

			// update table with emails qty
			$sql	= " UPDATE " . $table_prefix . "file_transfers SET ";
			if ($file_transferred) {
				if ($transfer_type == 1) {
					$sql .= " transfer_status='uploaded' ";
					$sql .= " , failed_errors=" . $db->tosql($transfer_error, TEXT);
				} else {
					$sql .= " transfer_status='downloaded' ";
					$sql .= " , failed_errors=" . $db->tosql($transfer_error, TEXT);
				}
				$sql .= " , date_transferred=" . $db->tosql(va_time(), DATETIME);
			} else {
				$failed_attempts++;
				if ($failed_attempts >= 5) {
					$transfer_status = "error";
				} else {
					$transfer_status = "failed";
				}
				// set next attempt transfer date
				$transfer_date = va_timestamp() + intval(3600 * pow(1.9, $failed_attempts));
				$sql .= " transfer_status=" . $db->tosql($transfer_status, TEXT);;
				$sql .= " , date_failed=" . $db->tosql(va_time(), DATETIME);
				$sql .= " , transfer_date=" . $db->tosql($transfer_date, DATETIME);
				$sql .= " , failed_errors=" . $db->tosql($transfer_error, TEXT);
				if ($transfer_error) {
					$error_message .= "\n".$transfer_error;
				}
			}
			$sql .= " WHERE transfer_id=" . $db->tosql($transfer_id, INTEGER);
			$db->query($sql);

			// check for next transfer file
			$db->RecordsPerPage = 1;
			$db->PageNumber = 1;
			$db->query($transfer_sql);
		} while ($db->next_record());

		$success_message = $files_transferred. " files transferred.";
	} else {
		$success_message = "There are no files to transfer";
	}

	// settings for errors notifications 
	$eol = get_eol();
	$recipients     = $settings["admin_email"];
	$email_headers  = "From: ". $settings["admin_email"] . $eol;
	$email_headers .= "Content-Type: text/plain";


?>