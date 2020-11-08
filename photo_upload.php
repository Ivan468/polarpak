<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  photo_upload.php                                         ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./messages/" . $language_code . "/download_messages.php");
	include_once("./includes/image_functions.php");
	include_once("./includes/file_functions.php");
	include_once("./includes/record.php");

	check_user_session();

	$photo_id = get_param("photo_id");
	$photo_type = get_param("photo_type");
	$profiles_settings  = get_settings("profiles");
		
	$tiny_generated = 0;
	$small_generated = 0;
	$large_generated = 0;
	$super_generated = 0;

	$t = new VA_Template($settings["templates_dir"]);
	$t->set_file("main", "photo_upload.html");
	$t->set_var("photo_upload_href", "photo_upload.php");
	$upload_msg = str_replace("{button_name}", UPLOAD_BUTTON, UPLOAD_SELECT_MSG);
	$t->set_var("UPLOAD_SELECT_MSG", $upload_msg);

	$css_file = "";
	$style_name = get_setting_value($settings, "style_name", "");
	$scheme_class = get_setting_value($settings, "scheme_name", "");
	if (strlen($style_name)) {
		$css_file = "styles/".$style_name;
		if (!preg_match("/\.css$/", $style_name)) { $css_file .= ".css"; }
	}
	$t->set_var("css_file", $css_file);
	$t->set_var("scheme_class", $scheme_class);

	$user_id = get_session("session_user_id");
	$type = get_session("session_user_type_id");
	$fid = get_param("fid");
	$control_name = get_param("control_name");
	$operation = get_param("operation");

	$files_regexp = "";

	$photo_tiny_path = "./photos/".$user_id."/tiny/";
	$photo_small_path = "./photos/".$user_id."/small/";
	$photo_large_path = "./photos/".$user_id."/large/";
	$photo_super_path = "./photos/".$user_id."/super/";

	$default_settings = array(
		"tiny" => array("width" => "32", "height" => "32", "generate" => 1,),
		"small" => array("width" => "100", "height" => "100", "generate" => 1,),
		"large" => array("width" => "280", "height" => "300", "generate" => 1,),
		"super" => array("width" => "600", "height" => "800", "generate" => 1,),
	);

	$photo_settings = array(); // settings for photo size
	if ($photo_type == "dating" || $photo_type == "profiles" || $photo_type == "1") {
		// general photo paths
		$photos["tiny"] = array("path" => $photo_tiny_path);
		$photos["small"] = array("path" => $photo_small_path);
		$photos["large"] = array("path" => $photo_large_path);
		$photos["super"] = array("path" => $photo_super_path);
		// get profiles settings
		$photo_settings = get_settings("profiles");
	} else {
		echo "Incorrect file type: ". $photo_type;
		exit;
	}

	$errors	= "";
	$check_filepaths = array();
	foreach ($photos as $size_type => $photo_info) {
		$path = $photo_info["path"];
		// check if we need to create a new folders where we save photos
		mkdir_recursively($path, $errors);
		// populate array to check unique file name 
		$check_filepaths[] = $path;
		// check photo size restrictions
		$photo_width = get_setting_value($photo_settings, "photo_".$size_type."_width", $default_settings[$size_type]["width"]);
		$photo_height = get_setting_value($photo_settings, "photo_".$size_type."_height", $default_settings[$size_type]["height"]);
		$photo_generate = get_setting_value($photo_settings, "photo_".$size_type."_generate", $default_settings[$size_type]["generate"]);
		$photos[$size_type]["width"] = $photo_width;
		$photos[$size_type]["height"] = $photo_height;
		$photos[$size_type]["generate"] = $photo_generate;
	}


	$photo_id = ""; 
	if ($operation == "upload") {
		// get information about uploaded file
		$tmp_name = $_FILES["newfile"]["tmp_name"];
		$filename = $_FILES["newfile"]["name"];
		$filesize = $_FILES["newfile"]["size"];
		$upload_error = isset($_FILES["newfile"]["error"]) ? $_FILES["newfile"]["error"] : "";

		if ($upload_error == 1) {
			$errors = "The uploaded file exceeds the max filesize directive.";
		} elseif ($upload_error == 2) {
			$errors = "The uploaded file exceeds the max filesize parameter.";
		} elseif ($upload_error == 3) {
			$errors = "The uploaded file was only partially uploaded.";
		} elseif ($upload_error == 4) {
			$errors = UPLOAD_SELECT_ERROR;
		} elseif ($upload_error == 6) {
			$errors = "Missing a temporary folder.";
		} elseif ($upload_error == 7) {
			$errors = "Failed to write file to disk.";
		} elseif ($tmp_name == "none" || !strlen($tmp_name)) {
			$errors = UPLOAD_SELECT_ERROR;
		} elseif (strlen($files_regexp)) {
			if (!preg_match($files_regexp, $filename)) {
				$errors = UPLOAD_FORMAT_ERROR;
			}
		} elseif (!(preg_match("/((.gif)|(.jpg)|(.jpeg)|(.bmp)|(.png))$/i", $filename)) ) {
			$errors = UPLOAD_FORMAT_ERROR;
		} elseif (!is_uploaded_file($tmp_name)) {
			$errors = "File wasn't correctly upload.";
		}

		if (!strlen($errors)) {
			// check unique name in all folders
			$new_filename = get_new_file_name ($check_filepaths, $filename);
			// check what photos should be generated
			$images_generated = 0;
			foreach ($photos as $size_type => $photo_info) {
				$path = $photo_info["path"];
				$photo_width = $photo_info["width"];
				$photo_height = $photo_info["height"];
				$photo_generate = $photo_info["generate"];
				$resize_type = "ratio"; // ratio | canvas
				$resize_direction = 3; // 1 - reduce, 2 - enlarge, 3 - both
				$resize_bg = "";
				if ($photo_generate) {
					$image_generated = image_resize($tmp_name, $path.$new_filename, $photo_width, $photo_height, $resize_type, $resize_direction, $resize_bg, "file", false, $errors);

					if ($image_generated) {
						$images_generated++;
						$photos[$size_type]["filename"] = $new_filename;
						$photos[$size_type]["filepath"] = $path.$new_filename;
						// set readable permission for generated file
						@chmod($path.$new_filename, 0766);
					} else if (!$errors) {
						$errors = "Image resize error.";
						break;
					}
				}
			}
			// delete all generated files if errors occured
			if ($errors && $images_generated) {
				foreach ($photos as $size_type => $photo_info) {
					$photo_generate = $photo_info["generate"];
					$filepath = $photo_info["filepath"];
					if ($photo_generate && $filepath && file_exists($filepath)) {
						@unlink($filepath);
					}
				}
			}

			if (!$errors && !$images_generated) {
				$errors = "Photos were not generated accordingly to your settings.";
			}

			// if there are no errors then we can save photos in database
			if (!$errors) {
				// initialize photos record
				$r = new VA_Record($table_prefix."users_photos");
				$r->add_where("photo_id", INTEGER);
				$r->add_textbox("user_id", INTEGER);
				$r->add_textbox("photo_type", INTEGER);
				$r->add_textbox("is_shown", INTEGER);
				$r->add_textbox("is_approved", INTEGER);
				$r->add_textbox("tiny_photo", TEXT);
				$r->add_textbox("small_photo", TEXT);
				$r->add_textbox("large_photo", TEXT);
				$r->add_textbox("super_photo", TEXT);
				$r->add_textbox("date_added", DATETIME);
				$r->add_textbox("date_updated", DATETIME);
				// set record values
				$r->set_value("user_id", $user_id);
				$r->set_value("photo_type", 1);
				$r->set_value("is_shown", 1);
				$r->set_value("is_approved", 1);
				// set photos values
				foreach ($photos as $size_type => $photo_info) {
					$filepath = $photo_info["filepath"];
					$photo_generate = $photo_info["generate"];
					if ($photo_generate && $filepath) {
						$r->set_value($size_type . "_photo", $filepath);
					} else {
						$r->set_value($size_type . "_photo", "");
					}
				}
				// set date values
				$r->set_value("date_added", va_time());
				$r->set_value("date_updated", va_time());
				// save photos
				if ($db_type == "postgre") {
					$photo_id = get_db_value(" SELECT NEXTVAL('seq_" . $table_prefix . "users_photos') ");
					$r->change_property("photo_id", USE_IN_INSERT, true);
					$r->set_value("photo_id", $photo_id);
				}
				$r->insert_record();
				if ($db_type == "mysql") {
					$photo_id = get_db_value(" SELECT LAST_INSERT_ID() ");
					$r->set_value("photo_id", $photo_id);
				} elseif ($db_type == "access") {
					$photo_id = get_db_value(" SELECT @@IDENTITY ");
					$r->set_value("photo_id", $photo_id);
				} elseif ($db_type == "db2") {
					$photo_id = get_db_value(" SELECT PREVVAL FOR seq_" . $table_prefix . "users_photos FROM " . $table_prefix . "users_photos ");
					$r->set_value("photo_id", $photo_id);
				}
			}
		}
	}

	if (strlen($errors))
	{
		$t->set_var("after_upload", "");
		$t->set_var("errors_list", $errors);
		$t->parse("errors", false);
	} else {
		$t->set_var("errors", "");
	}

	$t->set_var("photo_id", htmlspecialchars($photo_id));
	$t->set_var("photo_type", htmlspecialchars($photo_type));
	$t->set_var("type", htmlspecialchars($type));
	$t->set_var("fid", htmlspecialchars($fid));
	$t->set_var("control_name", htmlspecialchars($control_name));


	if ($operation == "upload" && !strlen($errors))
	{
		$t->set_var("before_upload", "");
		$t->parse("after_upload", false);
	} else {
		$t->parse("before_upload", false);
		$t->set_var("after_upload", "");
	}

	$t->pparse("main");

?>