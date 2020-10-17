<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  order_attachment.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./messages/" . $language_code . "/download_messages.php");

	$vc = get_param("vc");
	$atid = get_param("atid");
	$errors = "";

	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", "");
	$secure_redirect = get_setting_value($settings, "secure_redirect", 0);
	$secure_order_profile = get_setting_value($settings, "secure_order_profile", 0);
	if ($secure_order_profile) {
		$order_attachment_url = $secure_url . "order_attachment.php";
	} else {
		$order_attachment_url = $site_url . "order_attachment.php";
	}
	if (!$is_ssl && $secure_order_profile && $secure_redirect && preg_match("/^https/i", $secure_url)) {
		header("Location: " . $order_attachment_url . "?atid=" . urlencode($atid) . "&vc=" . urlencode($vc));
		exit;
	}

	if (strlen($atid) && strlen($vc)) {
		$sql  = " SELECT sa.file_name, sa.file_path, sa.date_added  ";
		$sql .= " FROM " . $table_prefix . "orders_attachments sa ";
		$sql .= " WHERE sa.attachment_id=" . $db->tosql($atid, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$filename = $db->f("file_name");
			$filepath = $db->f("file_path");
			$date_added = $db->f("date_added", DATETIME);
			if ($vc != md5($atid . $date_added[3].$date_added[4].$date_added[5])) {
				$errors = DOWNLOAD_WRONG_PARAM;
			}
		} else {
			$errors = DOWNLOAD_WRONG_PARAM;
		}
	} else {
		$errors = DOWNLOAD_MISS_PARAM;
	}

	if (!$errors) {
		$fp = @fopen($filepath, "rb");
		if (!$fp) {
			$errors = DOWNLOAD_PATH_ERROR;
		}
	}

	if ($errors) {
		echo $errors;
		exit;
	} else {
		$filesize = filesize ($filepath);
		if (ini_get("zlib.output_compression")) {
			ini_set("zlib.output_compression", "Off");
		}
		header("Pragma: private");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header("Content-Type: application/octet-stream"); 
		if ($filesize) {
			header("Content-Length: " . $filesize); 
		}
		header("Content-Disposition: attachment; filename=\"".$filename."\""); 
		header("Content-Transfer-Encoding: binary"); 

		// print the file to the output 
		while (!feof($fp)){
			//reset time limit for big files
			@set_time_limit(30);
			print(fread($fp,1024*8));
			flush(); 
		}
		fclose($fp);
	}

?>