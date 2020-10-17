<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_dump_download.php                                  ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");

	include_once("./admin_common.php");

	check_admin_security("db_management");

	$dump_file = get_param("dump_file");
	if (!$dump_file) { $dump_file = get_param("filename"); }
	if (!$dump_file) { $dump_file = get_param("file_name"); }
	// for security reason remove any slashes
	$dump_file = preg_replace("/[\/\\\\]/", "", $dump_file);
	$download_path = "../db/".$dump_file;

	$fp = @fopen($download_path, "rb");
	if (!$fp) {
		echo "Can't download file: " . htmlspecialchars($download_path);
		exit;
	}

	$filesize = @filesize($download_path);
	// check if partial content requested
	$content_length = $filesize; $seek_position = 0; 
	$range = get_var("HTTP_RANGE");
	if ($range && $filesize) {
		if (preg_match("/^bytes=(\d+)\-(\d+)$/", $range, $matches)) {
			$seek_position = $matches[1];
			$content_length = $matches[2] + 1;
		} elseif (preg_match("/^bytes=(\d+)\-$/", $range, $matches)) {
			$seek_position = $matches[1];
			$content_length = $filesize - $seek_position;
		} elseif (preg_match("/^bytes=\-(\d+)$/", $range, $matches)) {
			$seek_position = $filesize - $matches[0];
			$content_length = $matches[2];
		}
	}

	if ($filesize) {
		if ($filesize != $content_length) {
			header("HTTP/1.1 206 Partial content");
			header("Content-Length: " . $content_length); 
			header("Content-Range: bytes " . $seek_position . "-" . ($content_length - 1) . "/" . $filesize); 
		} else {
			header("Content-Length: " . $filesize); 
		}
	}
	if (ini_get("zlib.output_compression")) {
		ini_set("zlib.output_compression", "Off");
	}
	header("Pragma: private");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private", false);
	header("Accept-Ranges: bytes");
	header("Content-Type: application/octet-stream"); 
	header("Content-Disposition: attachment; filename=\"".$dump_file."\""); 
	header("Content-Transfer-Encoding: binary"); 

	// seek to start of missing part for local files
	if ($seek_position > 0) {
			fseek($fp, $seek_position);
	}
	// start buffered download
	while (!feof($fp)){
		// reset time limit for big files
		@set_time_limit(30);
		print(fread($fp, 8192*8));
		if (function_exists("ob_flush")) { 
			if (ob_get_length()) {
				@ob_flush(); 
			}
		}
		flush();
	}
	fclose($fp);

