<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_order_attachments.php                              ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
 	include_once($root_folder_path."messages/".$language_code."/download_messages.php");
	include_once("./admin_common.php");

	check_admin_security("sales_orders");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_order_attachments.html");

	$t->set_var("admin_order_attachments_href", "admin_order_attachments.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", ATTACHMENT_MSG, CONFIRM_DELETE_MSG));

	$order_id = get_param("order_id");
	$attachment_type = get_param("attachment_type");
	$operation = get_param("operation");
	$current_index = 0;

	$errors = "";
	if (!$order_id) {
		$errors = "Can't find order_id parameter.";
	}

	if($operation == "upload")
	{
		$tmp_name = $_FILES["newfile"]["tmp_name"];
		$filename = $_FILES["newfile"]["name"];
		$filesize = $_FILES["newfile"]["size"];
		$upload_error = isset($_FILES["newfile"]["error"]) ? $_FILES["newfile"]["error"] : "";

		if ($upload_error == 1) {
			$errors = FILESIZE_DIRECTIVE_ERROR_MSG;
		} else if ($upload_error == 2) {
			$errors = FILESIZE_PARAMETER_ERROR_MSG;
		} else if ($upload_error == 3) {
			$errors = PARTIAL_UPLOAD_ERROR_MSG;
		} else if ($upload_error == 4) {
			$errors = NO_FILE_UPLOADED_MSG;
		} else if ($upload_error == 6) {
			$errors = TEMPORARY_FOLDER_ERROR_MSG;
		} else if ($upload_error == 7) {
			$errors = FILE_WRITE_ERROR_MSG;
		} else if ($tmp_name == "none" || !strlen($tmp_name)) {
			$errors = NO_FILE_UPLOADED_MSG;
		//} else if (!(preg_match("/((.gif)|(.jpg)|(.jpeg)|(.bmp)|(.tiff)|(.tif)|(.png)|(.ico)|(.doc)|(.txt)|(.rtf)|(.pdf)|(.swf))$/i", $filename)) ) {
			//$errors = "The file isn't allowed for uploading.";
		}

		if(!strlen($errors))
		{
			// get attachments dir
			$sql  = "SELECT setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= "WHERE setting_type='order_info' AND setting_name='attachments_dir'";
			$sql .= "AND ( site_id=1 OR  site_id=" . $db->tosql($root_site_id,INTEGER). ") ";
			$sql .= "ORDER BY site_id DESC ";
			$attachments_dir = get_db_value($sql);

			if (!$attachments_dir) { $attachments_dir = "downloads/"; }
			$admin_attachments_dir = $attachments_dir;
			// for admin folder use one level up if path is not absolute
			if (!preg_match("/^[\/\\\\]/", $admin_attachments_dir) && !preg_match("/\:/", $admin_attachments_dir)) {
				$admin_attachments_dir = "../".$attachments_dir;
			}

			$filepath = $admin_attachments_dir;

			$new_filename = $filename;
			$file_index = 0;
			while (file_exists($filepath . $new_filename)) {
				$file_index++;
				$delimiter_pos = strpos($filename, ".");
				if($delimiter_pos) {
					$new_filename = substr($filename, 0, $delimiter_pos) . "_" . $file_index . substr($filename, $delimiter_pos);
				} else {
					$new_filename = $index . "_" . $filename;
				}
			}

			if(!@move_uploaded_file($tmp_name, $filepath . $new_filename)) {
				if (!is_dir($filepath)) {
					$errors = FOLDER_DOESNT_EXIST_MSG . $filepath ;
				} else if (!is_writable($filepath)) {
					$errors = str_replace("{folder_name}", $filepath, FOLDER_PERMISSION_MESSAGE);
				} else {
					$errors = UPLOAD_CREATE_ERROR . $filepath . $filename . "</b>";
				}
			} else {
				chmod($filepath . $new_filename, 0766);

				// save attachment in the database
				$sql  = " INSERT INTO " . $table_prefix . "orders_attachments ";
				$sql .= " (order_id, admin_id, event_id, attachment_type, file_path, file_name, date_added) VALUES (";
				$sql .= $db->tosql($order_id, INTEGER) . ", ";
				$sql .= $db->tosql(get_session("session_admin_id"), INTEGER) . ", ";
				$sql .= "0, ";
				$sql .= $db->tosql($attachment_type, INTEGER) . ", ";
				$sql .= $db->tosql($attachments_dir.$new_filename, TEXT) . ", ";
				$sql .= $db->tosql($filename, TEXT) . ", ";
				$sql .= $db->tosql(va_time(), DATETIME) . ") ";
				$db->query($sql);

				$errors = "";
			}
		}
	} else if ($operation == "remove") {
		$atid = get_param("atid");
		$sql  = " SELECT file_path ";
		$sql .= " FROM " . $table_prefix . "orders_attachments ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$sql .= " AND admin_id=" . $db->tosql(get_session("session_admin_id"), INTEGER);
		$sql .= " AND event_id=0 ";
		$sql .= " AND attachment_id=" . $db->tosql($atid, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$file_path = $db->f("file_path");
			@unlink($file_path);
			$sql  = " DELETE FROM " . $table_prefix . "orders_attachments ";
			$sql .= " WHERE attachment_id=" . $db->tosql($atid, INTEGER);
			$db->query($sql);
		}
	}

	$t->set_var("order_id", $order_id);
	$t->set_var("attachment_type", $attachment_type);
	
	$attachments_files = "";
	$sql  = " SELECT attachment_id, file_name, file_path ";
	$sql .= " FROM " . $table_prefix . "orders_attachments ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$sql .= " AND admin_id=" . $db->tosql(get_session("session_admin_id"), INTEGER);
	$sql .= " AND event_id=0 ";
	$sql .= " AND attachment_type=" . $db->tosql($attachment_type, INTEGER);
	$sql .= " ORDER BY attachment_id ";
	$db->query($sql);
	if ($db->next_record()) {
		do {
			$attachment_id = $db->f("attachment_id");
			$filename = $db->f("file_name");
			$filepath = $db->f("file_path");
			if (!preg_match("/^[\/\\\\]/", $filepath) && !preg_match("/\:/", $filepath)) {
				$filepath = "../".$filepath;
			}

			$filesize = get_nice_bytes(filesize($filepath));
			if ($attachments_files) { $attachments_files .= "; "; }
			$attachments_files .= "<a href=&quot;admin_order_attachment.php?atid=" .$attachment_id. "&quot; target=&quot;_blank&quot;>" . $filename . "</a> (" . $filesize . ")";
  
			$t->set_var("attachment_id", $attachment_id);
			$t->set_var("filename", $filename);
			$t->set_var("filesize", $filesize);
			$t->parse("attachments", true);
		} while ($db->next_record());
		$t->parse("attachments_block", false);
	}

	if(strlen($errors)) {
		$t->set_var("errors_list", $errors);
		$t->parse("errors", false);
	}	else {
		$t->set_var("errors", "");
	}

	$t->set_var("attachments_files", $attachments_files);

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

?>