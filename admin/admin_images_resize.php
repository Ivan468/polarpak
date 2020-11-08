<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_images_resize.php                                  ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/image_functions.php");
	include_once($root_folder_path . "messages/" . $language_code . "/download_messages.php");
	include_once("./admin_common.php");

	check_admin_security("filemanager");

	$site_url_path = get_setting_value($settings, "site_url", "");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_images_resize.html");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_images_dir_href", "admin_images_dir.php");


	$r = new VA_Record($table_prefix . "admins");
	$r->return_page = "admin_admins.php";
	$r->add_where("admin_id", INTEGER);

	//$r->add_checkbox("is_hidden", INTEGER);
	$r->add_textbox("source_dir", TEXT, SOURCE_FOLDER_MSG);
	$r->change_property("source_dir", REQUIRED, true);
	$r->add_hidden("source_dir_desc", TEXT);
	$r->add_textbox("destination_dir", TEXT, DESTINATION_FOLDER_MSG);
	$r->change_property("destination_dir", REQUIRED, true);
	$r->add_hidden("destination_dir_desc", TEXT);

	$r->add_textbox("files_mask", TEXT, FILES_MASK_MSG);

	$r->add_textbox("image_width", INTEGER, WIDTH_IN_PIXELS_MSG);
	$r->change_property("image_width", REQUIRED, true);
	$r->add_textbox("image_height", INTEGER, HEIGHT_IN_PIXELS_MSG);
	$r->change_property("image_height", REQUIRED, true);

	$operation = get_param("operation");

	$success_message = "";
	$resize_errors = 0; $resize_success = 0;
	$source_path = ""; $destination_path = "";
	$r->get_form_parameters();
	if ($operation == "resize") {
		// some dir changes
		$source_dir = $r->get_value("source_dir");
		$source_dir = preg_replace("/\.{2,}/", "", $source_dir);
		$source_dir = preg_replace("/[\:\*\?\"\<\>\|]/", "", $source_dir);
		$r->set_value("source_dir", $source_dir);

		$destination_dir = $r->get_value("destination_dir");
		$destination_dir = preg_replace("/\.{2,}/", "", $destination_dir);
		$destination_dir = preg_replace("/[\:\*\?\"\<\>\|]/", "", $destination_dir);
		$r->set_value("destination_dir", $destination_dir);

		// build path to folders
		if (strlen($source_dir)) {
			$source_path = "../images/".$source_dir."/";
		}
		if (strlen($destination_dir)) {
			$destination_path = "../images/".$destination_dir."/";
		}

		$data_valid = $r->validate();
		// check writable permission 
		if ($destination_path && !is_writable($destination_path)) {
			$data_valid = false;
			$r->errors .= str_replace("{folder_name}", $destination_path, FOLDER_PERMISSION_MESSAGE)."<br>";
		}

		if ($data_valid) {

			// build files regexp
			$files_mask = $r->get_value("files_mask");
			$files_regexp = "";
			if (strlen($files_mask)) {
				$files_regexp = preg_quote($files_mask, "/");
				$files_regexp = str_replace("\?", ".", $files_regexp);
				$files_regexp = str_replace("\*", ".*", $files_regexp);
				$files_regexp = "^".$files_regexp."$";
			}

			// start resizing images
			$image_width = $r->get_value("image_width");
			$image_height = $r->get_value("image_height");
			if ($dir = @opendir($source_path)) {
				$dir_index = 0;
				while ($file = readdir($dir)) {
					if ($file != "." && $file != ".." && @is_file($source_path.$file)) {
						if (preg_match("/".$files_regexp."/i", $file)) {
							//$dir_values[$dir_index] = $file;
							//$dir_index++;
							$resized = resize($file, $source_path, $destination_path, $image_width, $image_height, $errors);
							if ($resized) {
								$resize_success++;
							} else {
								$resize_errors++;
							}
							//echo "<br>:".$source_path.$file;
						}
					}
				}
				closedir($dir);
			}

			if ($resize_success) {
				$success_message.= str_replace("{resized_number}", $resize_success,  RESIZED_SUCCESSFULLY_MSG);
			}
			if ($resize_errors) {
				$success_message.= str_replace("{errors_number}", $resize_errors,  RESIZED_ERRORS_MSG);
			}
		}
	}

	// set description for folders
	if ($source_path) {
		$r->set_value("source_dir_desc", $source_path);
	} else {
		$r->set_value("source_dir_desc", SELECT_MSG);
	}
	if ($destination_path) {
		$r->set_value("destination_dir_desc", $destination_path);
	} else {
		$r->set_value("destination_dir_desc", SELECT_MSG);
	}

	if ($success_message) {
		$t->set_var("success_message", $success_message);
		$t->parse("success_block", false);
	}

	$r->set_parameters();


	$t->pparse("main");

?>