<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_fm_upload_files.php                                ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/file_functions.php");
	include_once($root_folder_path . "includes/admin_fm_functions.php");
		//include_once($root_folder_path . "includes/zip_class.php");
	include_once($root_folder_path . "messages/" . $language_code . "/download_messages.php");

	include_once("./admin_common.php");
	include_once("./admin_fm_config.php");

	check_admin_security("filemanager");

	$operation = get_param("operation");
	$folder_structure = get_param("folder_structure");

	// get root dir
	fm_dir($dir, $site_dir, $top_dir);

	// check for possible errors
	$error = "";
	if ($dir == "..") {
		$error = va_constant("FOLDER_WRITE_PERMISSION_MSG");
	} else if (!is_writable ($dir) ) {
		$error = str_replace("{folder_name}", $dir, va_constant("FOLDER_PERMISSION_MESSAGE"));
	}

	if (strlen($error)){
		set_session("fm_error",$error);
		header("Location: admin_fm.php");
		exit;
	}

	$op = terminator(get_param("op"));
	if(strlen($op) == 0) {
		$op = 1;
	}
	
	$t = new VA_Template($settings["admin_templates_dir"]);
	if ($op == 1) {
		$t->set_file("main","admin_fm_upload_files.html");
	}	else {
		$t->set_file("main","admin_fm_upload_archive.html");		
	}
	$t->set_var("admin_select_href", "admin_select.php");
	$t->set_var("admin_upload_href", "admin_fm_upload_files.php");
	$t->set_var("admin_filemanager_href", "admin_fm.php");
	$t->set_var("admin_fm_href", "admin_fm.php");
	$t->set_var("dir", htmlspecialchars($site_dir));
	$t->set_var("dir_url", urlencode($site_dir));
	$t->set_var("destination_folder", htmlspecialchars($site_dir));		


	if ($op == 2) {
		$structure_options = array(
			array("1", va_constant("KEEP_FOLDER_STRUCTURE_MSG")),
			array("0", va_constant("EXTRACT_FILES_TO_FOLDER_MSG")),
		);
		$r = new VA_Record("");
		$r->add_radio("folder_structure", INTEGER, $structure_options);
		$r->change_property("folder_structure", DEFAULT_VALUE, 1);
		if ($operation) {
			$r->get_form_parameters();
		} else {
			$r->set_default_values();
		}
		$r->set_form_parameters();
	}
	
	
	$errors = "";
	$service_message = "";
	$select_errors = 0; // calculate number of missed files
	$tmp_dir = get_setting_value($settings, "tmp_dir", ""); 
	$tmp_sub_dir = "tmp_" . md5(uniqid(mt_rand(), true)); // generate tmp folder name
	$tmp_archive_dir = $tmp_dir.$tmp_sub_dir;

	if(is_array($_FILES)) {
		foreach($_FILES as $key_file => $up_file) {

			$error = "";
			$tmp_name = $up_file["tmp_name"];
			$filename = terminator($up_file["name"]);
			$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
			$filesize = $up_file["size"];
			$upload_error = isset($up_file["error"]) ? $up_file["error"] : "";


			// 1. check general upload errors
			if ($upload_error == 1) {
				$error = FILESIZE_DIRECTIVE_ERROR_MSG;
			} elseif ($upload_error == 2) {
				$error = FILESIZE_PARAMETER_ERROR_MSG;
			} elseif ($upload_error == 3) {
				$error = PARTIAL_UPLOAD_ERROR_MSG;
			} elseif ($upload_error == 4) {
				$error = UPLOAD_SELECT_ERROR;
			} elseif ($upload_error == 6) {
				$error = TEMPORARY_FOLDER_ERROR_MSG;
			} elseif ($upload_error == 7) {
				$error = FILE_WRITE_ERROR_MSG;
			}

			// use different regular expression for different destinations
			if ($top_dir == "images") {
				$file_regexp =  FM_IMAGE_REGEXP;
			} else if ($top_dir == "templates") {
				$file_regexp =  FM_HTML_REGEXP;
			} else if ($top_dir == "styles") {
				$file_regexp =  FM_CSS_REGEXP;
			} else if ($top_dir == "video") {
				$file_regexp =  FM_VIDEO_REGEXP;
			} else {
				$file_regexp =  FM_FILE_REGEXP;
			}

			// 2. check for other errors
			if (!$error) {	
				if ($op == 1 && !preg_match($file_regexp, $filename)) { // check file extension for multi-upload
					$error = va_constant("INVALID_FILE_EXTENSION_MSG").": <b>".$filename."</b>";
				} else if ($op == 2 && !preg_match(FM_ARCHIVE_REGEXP, $filename)) { // check file extension for arhive upload
					$error = va_constant("INVALID_FILE_EXTENSION_MSG").": <b>".$filename."</b>";
				} else if (!is_uploaded_file($tmp_name)) { // check if file was really uploaded
					$error = va_constant("NO_FILE_UPLOADED_MSG").": <b>".$filename."</b>";
				}
			}

			if (!$error) {
				if ($op == 1) {
					// upload file
					if (!@move_uploaded_file($tmp_name, $dir."/".$filename)) {
						if (!is_dir($dir)) {
							$error = va_constant("FOLDER_DOESNT_EXIST_MSG") . $dir;
						} elseif (!is_writable($dir)) {
							$error = str_replace("{folder_name}", $dir, va_constant("FOLDER_PERMISSION_MESSAGE"));
						} else {
							$error = va_constant("UPLOAD_CREATE_ERROR") ." <b>" . $dir . $filename . "</b>";
						}
					}
				} else if ($op == 2) { 
					$files_extracted = 0; $files_ignored = 0; $files_errors = 0;
					$zip = new ZipArchive; 
					if ($zip->open($tmp_name) === true) {
					  for($i = 0; $i < $zip->numFiles; $i++) {
							$archive_file = $zip->getNameIndex($i);
							$archive_filename = basename($archive_file);
							if (preg_match("/(\\\\|\\/)$/", $archive_file)) { 
								// it's a folder name so just ignoring it
							} else if (preg_match($file_regexp, $archive_filename)) { 
								if ($folder_structure == 1) {
									// keep archive folder structure
									if ($zip->extractTo($dir, $archive_file)) {
										$files_extracted++;
									} else {
										$files_errors++;
	                  $error = "Unable to extract the file."; 
									}
								} else {
									if ($zip->extractTo($tmp_archive_dir, $archive_file)) {
										rename($tmp_archive_dir."/".$archive_file, $dir."/".$archive_filename);
										$files_extracted++;
									} else {
										$files_errors++;
	                  $error = "Unable to extract the file."; 
									}

								}
							} else {
								$files_ignored++;
							}
					  }
					} else {
						$error = "Can't open archive: ".$filename;
					}
				  $zip->close();
					rmdir_recursively($tmp_archive_dir); // remove temporary archive folder 
					unlink($tmp_name); // remove archive file
				}
			}

			if ($error) {
				if ($upload_error == 4) {
					$select_errors++;
				}
				if ($upload_error != 4 || $select_errors == 6) {
					$errors .= $error."<br/>\n";	
				}
			} else {
				if ($op == 1) {
					$service_message .= va_constant("FILE_SAVED_MSG")." ".$filename."<br/>";
				} elseif($op == 2) {
					$service_message .= str_replace("{files_extracted}", $files_extracted, va_constant("FILES_EXTRACTED_MSG"))."<br/>";
					if ($files_ignored) {
						$service_message .= str_replace("{files_ignored}", $files_ignored, va_constant("FILES_IGNORED_MSG"))."<br/>";
					}
					if ($files_errors) {
						$service_message .= str_replace("{files_errors}", $files_errors, va_constant("FILES_ERRORS_MSG"))."<br/>";
					}
				}
			}
		}
	}

	if (strlen($errors)) {
		$t->set_var("errors_list", $errors);
		$t->parse("errors_block", true);
	} else {
		$t->set_var("errors_block", "");
	}
	if(strlen($service_message) > 0) {
		$t->set_var("service_message", $service_message);
		$t->parse("service_messages", true);
	} else {
		$t->set_var("service_messages", "");
	}

	include_once("./admin_header.php");
	include_once("./admin_footer.php");
	$t->pparse("main");
