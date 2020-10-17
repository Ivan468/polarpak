<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_upload.php                                         ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/image_functions.php");
	include_once($root_folder_path . "includes/file_functions.php");
	include_once($root_folder_path . "messages/" . $language_code . "/download_messages.php");
	include_once("./admin_common.php");

	check_admin_security();

	// turn off any cache to make AJAX calls
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");
	header("Content-Type: text/html; charset=" . CHARSET);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_upload.html");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("admin_select_href", "admin_select.php");
	$t->set_var("admin_upload_href", "admin_upload.php");
	$upload_select_message = str_replace("{button_name}", UPLOAD_BUTTON, UPLOAD_SELECT_MSG);
	$t->set_var("upload_select_message", $upload_select_message);

	$errors = "";

	$full_image_url = get_setting_value($settings, "full_image_url", 0);
	$site_url = get_setting_value($settings, "site_url", "");
	$image_site_url = "";
	if ($full_image_url){
		$image_site_url = $site_url;
		$t->set_var("image_site_url", $site_url);					
	} else {
		$t->set_var("image_site_url", "");					
	}

	$filetype = get_param("filetype");
	$image_index = get_param("image_index");
	$t->set_var("image_index", $image_index);
	$operation = get_param("operation");
	$current_index = 0;
	$va_module = get_param("va_module");
	$item_id = get_param("item_id");
	$article_id = get_param("article_id");
	$category_id = get_param("category_id");
	
	$param_site_id = get_session("session_site_id");
	$admin_id = get_session("session_admin_id");

	$downloads_dir = ""; $files_regexp = "";
	if ($filetype == "downloads") {
		$download_info = array();
		$sql = "SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings WHERE setting_type='download_info'";
		if ($multisites_version) {
			$sql .= "AND ( site_id=1 OR  site_id=" . $db->tosql($site_id,INTEGER). ") ";
			$sql .= "ORDER BY site_id ASC ";
		}
		$db->query($sql);
		while ($db->next_record()) {
			$download_info[$db->f("setting_name")] = $db->f("setting_value");
		}
		$downloads_dir = get_setting_value($download_info, "downloads_admins_dir", "../");
		if (!preg_match("/[\/\\\\]$/", $downloads_dir)) { $downloads_dir .= "/"; }
		$downloads_mask = get_setting_value($download_info, "downloads_admins_mask", "");
		if ($downloads_mask) {
			$files_regexp = preg_replace("/\s/", "", $downloads_mask);
			$s = array("\\","^","\$",".","[","]","|","(",")","+","{","}");
			$r = array("\\\\","\\^","\\\$","\\.","\\[","\\]","\\|","\\(","\\)","\\+","\\{","\\}");
			$files_regexp = str_replace($s, $r, $files_regexp);
			$files_regexp = str_replace(array(",", ";", "*", "?"), array(")|(", ")|(", ".*", "."), $files_regexp);
			$files_regexp = "/^((" . $files_regexp . "))$/i";
		}
	}

	$images_root = "../images/";
	$save_root = "images/";
	$tiny_dir_suffix = "tiny/";
	$small_dir_suffix = "small/";
	$big_dir_suffix = "big/";
	$large_dir_suffix = "large/";
	$super_dir_suffix = "super/";
	$tmp_dir_suffix = "tmp/";
	$image_type = ""; $type_subdir = "";

	if ($filetype == "tiny_image") {
		$filepath = $images_root . $tiny_dir_suffix;
	} elseif ($filetype == "option" || $filetype == "property") {
		$filepath = $images_root . "options/";
	} elseif ($filetype == "small_image") {
		$filepath = $images_root . $small_dir_suffix;
	} elseif ($filetype == "big_image") {
		$filepath = $images_root . $big_dir_suffix;
	} elseif ($filetype == "super_image") {
		$filepath = $images_root . $super_dir_suffix;
	} elseif ($filetype == "payment_small") {
		$filepath = $images_root . "payments/" . $small_dir_suffix;
	} elseif ($filetype == "payment_large") {
		$filepath = $images_root . "payments/" . $large_dir_suffix;
	} elseif ($filetype == "shipping_small") {
		$filepath = $images_root . "shipping/" . $small_dir_suffix;
	} elseif ($filetype == "shipping_large") {
		$filepath = $images_root . "shipping/" . $large_dir_suffix;
	} elseif ($filetype == "article_tiny") {
		$type_subdir = "articles/"; $image_type = "article";
		$filepath = $images_root . "articles/" . $tiny_dir_suffix;
	} elseif ($filetype == "article_small") {
		$type_subdir = "articles/"; $image_type = "article";
		$filepath = $images_root . "articles/" . $small_dir_suffix;
	} elseif ($filetype == "article_large") {
		$type_subdir = "articles/"; $image_type = "article";
		$filepath = $images_root . "articles/" . $large_dir_suffix;
	} elseif ($filetype == "article_super") {
		$type_subdir = "articles/"; $image_type = "article";
		$filepath = $images_root . "articles/" . $super_dir_suffix;
	} elseif ($filetype == "preview_video") {
		$filepath = $images_root . "video/preview/";
	} elseif ($filetype == "article_video") {
		$filepath = "../video/article/";
	} elseif ($filetype == "category") {
		$filepath = $images_root . "categories/";
	} elseif ($filetype == "category_tiny") {
		$filepath = $images_root . "categories/" . $tiny_dir_suffix;
	} elseif ($filetype == "category_small") {
		$filepath = $images_root . "categories/" . $small_dir_suffix;
	} elseif ($filetype == "category_large") {
		$filepath = $images_root . "categories/" . $large_dir_suffix;
	} elseif ($filetype == "company_small" || $filetype == "company_large") {
		$filepath = $images_root . "companies/";
	} elseif ($filetype == "document") {
		$filepath = $images_root . "documents/";
	} elseif ($filetype == "ad_small") {
		$filepath = $images_root . "ads/" . $small_dir_suffix;
	} elseif ($filetype == "ad_large") {
		$filepath = $images_root . "ads/" . $large_dir_suffix;
	} elseif ($filetype == "forum_small") {
		$filepath = $images_root . "forum/" . $small_dir_suffix;
	} elseif ($filetype == "forum_large") {
		$filepath = $images_root . "forum/" . $large_dir_suffix;
	} elseif ($filetype == "banner") {
		$filepath = $images_root . "bnrs/";
	} elseif ($filetype == "personal") {
		$filepath = $images_root . "users/";
	} elseif ($filetype == "downloads") {
		$filepath = $downloads_dir;
	} elseif ($filetype == "previews") {
		$filepath = "../previews/";
	} elseif ($filetype == "preview_image") {
		$filepath = "../images/previews/";
	} elseif ($filetype == "manufacturer_small") {
		$filepath = $images_root . "manufacturers/" . $small_dir_suffix;
	} elseif ($filetype == "manufacturer_large") {
		$filepath = $images_root . "manufacturers/" . $large_dir_suffix;
	} elseif ($filetype == "author_tiny") {
		$type_subdir = "authors/"; $image_type = "author";
		$filepath = $images_root . "authors/" . $tiny_dir_suffix;
	} elseif ($filetype == "author_small") {
		$type_subdir = "authors/"; $image_type = "author";
		$filepath = $images_root . "authors/" . $small_dir_suffix;
	} elseif ($filetype == "author_large") {
		$type_subdir = "authors/"; $image_type = "author";
		$filepath = $images_root . "authors/" . $large_dir_suffix;
	} elseif ($filetype == "author_super") {
		$type_subdir = "authors/"; $image_type = "author";
		$filepath = $images_root . "authors/" . $super_dir_suffix;
	} elseif ($filetype == "album_tiny") {
		$type_subdir = "albums/"; $image_type = "album";
		$filepath = $images_root . "albums/" . $tiny_dir_suffix;
	} elseif ($filetype == "album_small") {
		$type_subdir = "albums/"; $image_type = "album";
		$filepath = $images_root . "albums/" . $small_dir_suffix;
	} elseif ($filetype == "album_large") {
		$type_subdir = "albums/"; $image_type = "album";
		$filepath = $images_root . "albums/" . $large_dir_suffix;
	} elseif ($filetype == "album_super") {
		$type_subdir = "albums/"; $image_type = "album";
		$filepath = $images_root . "albums/" . $super_dir_suffix;
	} elseif ($filetype == "contest_tiny") {
		$type_subdir = "contests/"; $image_type = "contest";
		$filepath = $images_root . "contests/" . $tiny_dir_suffix;
	} elseif ($filetype == "contest_small") {
		$type_subdir = "contests/"; $image_type = "contest";
		$filepath = $images_root . "contests/" . $small_dir_suffix;
	} elseif ($filetype == "contest_large") {
		$type_subdir = "contests/"; $image_type = "contest";
		$filepath = $images_root . "contests/" . $large_dir_suffix;
	} elseif ($filetype == "contest_super") {
		$type_subdir = "contests/"; $image_type = "contest";
		$filepath = $images_root . "contests/" . $super_dir_suffix;
	} elseif ($filetype == "icon") {
		$filepath = "../images/icons/";
	} elseif ($filetype == "filter" || $filetype == "filter_image") {
		$filepath = "../images/filters/";
	} elseif ($filetype == "emoticon") {
		$filepath = "../images/emoticons/";
	} elseif ($filetype == "language" || $filetype == "language_active") {
		$filepath = "../images/flags/";
	} elseif ($filetype == "currency" || $filetype == "currency_active") {
		$filepath = "../images/currencies/";
	} elseif ($filetype == "menu_image_active" || $filetype == "menu_image") {
		$filepath = $images_root;
	} elseif ($filetype == "tmp" || $filetype == "product_tmp" || $filetype == "article_tmp" || $filetype == "article_category_tmp") {
		$filepath = "../images/tmp/";
	} else {
		$filepath = $images_root;
	}
	// add slash at the end of file path if it's absent
	if (substr($filepath, -1) != "/") {
		$filepath .= "/";
	}
	$t->set_var("files_dir", str_replace("../", "", $filepath));
	// check if folder exists
	if (!file_exists($filepath)) {
		$errors .= FOLDER_DOESNT_EXIST_MSG." ".$filepath;
	}
	

	$layout_name = "";
	$layout_id = get_param("layout_id");
	$active_layout_id = $layout_id;
	if (!$active_layout_id) { 
		// get layout for selected site
		$sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
		$sql .= " WHERE setting_type='global' AND setting_name='layout_id' AND site_id=" . $db->tosql($param_site_id, INTEGER);
		$active_layout_id = get_db_value($sql);
	}
	if (!$active_layout_id) { 
		$active_layout_id = get_setting_value($settings, "layout_id", "");
	}
	if (($filetype == "menu_image_active" || $filetype == "menu_image") && $active_layout_id) {
		$sql  = " SELECT layout_name FROM " . $table_prefix . "layouts ";
		$sql .= " WHERE layout_id=" . $db->tosql($active_layout_id, INTEGER);
		$layout_name = get_db_value($sql);
	}
	$t->set_var("layout_id", $layout_id);

	// subdir selection for upload
	$subdir_id = get_param("subdir_id");
	$subdirs    = array();
	$subdirs[0] = array(0, "");
	$i = 0;
	$selected_subdir = "";
	// read all folders into tmp array
	$tmp_dirs = array();
	if ($dir = @opendir($filepath)) {
		while ($file = @readdir($dir)) {
			if ($file != "." && $file != ".." && @is_dir($filepath . $file)) {
				$tmp_dirs[] = $file;
			} 
		}
		@closedir($dir);
	}
	if (sizeof($tmp_dirs) > 0) {
		sort($tmp_dirs);
		for($i = 0; $i < sizeof($tmp_dirs); $i++) {	
			$subdirs[$i + 1] = array($i + 1, $tmp_dirs[$i]);
			if (!strlen($subdir_id) && strtolower($tmp_dirs[$i]) == strtolower($layout_name)) {
				$subdir_id = $i + 1;
			}
		}
		set_options($subdirs, $subdir_id, "subdir_id");
		if ($subdir_id && isset($subdirs[$subdir_id][1])) {
			$selected_subdir = $subdirs[$subdir_id][1];
		}
		$t->parse("subdir_id_block");
	}

	if ($operation == "1" || $operation == "ajax")
	{		
		//BEGIN Image Resizing changes
		$filetype = get_param("filetype");
		$tiny_generate = get_param("tiny_generate");
		$small_generate = get_param("small_generate");
		$large_generate = get_param("large_generate");
		$super_generate = get_param("super_generate");
		$image_position = get_param("image_position");
		if (!strlen($image_position)) { $image_position = 2; }

		// update settings
		if ($filetype == "super_image" || $filetype == "big_image" || $filetype == "small_image") {
			$image_settings = array(
				"is_generate_tiny_image" => $tiny_generate,
				"is_generate_small_image" => $small_generate,
				"is_generate_big_image" => $large_generate,
			);
			update_admin_settings($image_settings);
		} else if ($image_type) {
			$image_settings = array(
				$image_type."_tiny_generate" => $tiny_generate,
				$image_type."_small_generate" => $small_generate,
				$image_type."_large_generate" => $large_generate,
			);
			update_admin_settings($image_settings);
		}

		//END Image Resizing changes

		if ($operation == "ajax") {	
			$jsindex = get_param("jsindex");
			$filename = get_param("filename");
			$filesize = get_param("filesize");
			$newname  = get_param("newname");
			$filepart = base64_decode(get_param("filepart"));
			$partsize = get_param("partsize");
			$uploaded_size = get_param("uploaded"); // compare with current size

			$tmp_name = "";
			$upload_error = "";
		} else {
			$tmp_name = $_FILES["newfile"]["tmp_name"];
			$filename = $_FILES["newfile"]["name"];
			$filesize = $_FILES["newfile"]["size"];
			$upload_error = isset($_FILES["newfile"]["error"]) ? $_FILES["newfile"]["error"] : "";
		}

		if ($upload_error == 1) {
			$errors .= FILESIZE_DIRECTIVE_ERROR_MSG . "<br>\n";
		} elseif ($upload_error == 2) {
			$errors .= FILESIZE_PARAMETER_ERROR_MSG . "<br>\n";
		} elseif ($upload_error == 3) {
			$errors .= PARTIAL_UPLOAD_ERROR_MSG . "<br>\n";
		} elseif ($upload_error == 4) {
			$errors .= UPLOAD_SELECT_ERROR . "<br>\n";
		} elseif ($upload_error == 6) {
			$errors .= TEMPORARY_FOLDER_ERROR_MSG . ".<br>\n";
		} elseif ($upload_error == 7) {
			$errors .= FILE_WRITE_ERROR_MSG . "<br>\n";
		} elseif ($operation != "ajax" && ($tmp_name == "none" || !strlen($tmp_name))) {
			$errors .= UPLOAD_SELECT_ERROR . "<br>\n";
		} elseif (strlen($files_regexp)) {
			if (!preg_match($files_regexp, $filename)) {
				$errors .= UPLOAD_FORMAT_ERROR . "<br>\n";
			}
		} elseif (!(preg_match("/((\.gif)|(\.jpg)|(\.jpeg)|(\.bmp)|(\.tiff)|(\.tif)|(\.png)|(\.ico)|(\.doc)|(\.docx)|(\.xls)|(\.xlsx)|(\.txt)|(\.rtf)|(\.pdf)|(\.psd)|(\.ai)|(\.swf)|(\.flv)|(\.avi)|(\.asf)|(\.wmv)|(\.vma)|(\.mpg)|(\.mpeg)|(\.mp4))$/i", $filename)) ) {
			$errors .= UPLOAD_FORMAT_ERROR . "<br>\n";
		} else if (!is_dir($filepath)) {
			$errors .= FOLDER_DOESNT_EXIST_MSG ." ". $filepath ;
		}

		if (!strlen($errors))
		{
			$check_filepaths = array();
			if ($filetype == "tiny_image" || $filetype == "small_image" || $filetype == "big_image" || $filetype == "super_image" || $filetype == "product_tmp") {
				if ($selected_subdir) {
					$check_filepaths[] = $images_root .	$tiny_dir_suffix . $selected_subdir . "/";
					$check_filepaths[] = $images_root .	$small_dir_suffix . $selected_subdir . "/";
					$check_filepaths[] = $images_root .	$big_dir_suffix . $selected_subdir . "/";
					$check_filepaths[] = $images_root .	$super_dir_suffix . $selected_subdir . "/";
					if ($filetype == "product_tmp") {
						$check_filepaths[] = $images_root .	$tmp_dir_suffix . $selected_subdir . "/";
					}
				} else {
					$check_filepaths[] = $images_root .	$tiny_dir_suffix;
					$check_filepaths[] = $images_root .	$small_dir_suffix;
					$check_filepaths[] = $images_root .	$big_dir_suffix;
					$check_filepaths[] = $images_root .	$super_dir_suffix;
					if ($filetype == "product_tmp") {
						$check_filepaths[] = $images_root .	$tmp_dir_suffix;
					}
				}
			} else if ($filetype == "article_tiny" || $filetype == "article_small" || $filetype == "article_large" || $filetype == "article_super" || $filetype == "article_tmp") {
				if ($selected_subdir) {
					$check_filepaths[] = $images_root .	"articles/". $tiny_dir_suffix . $selected_subdir . "/";
					$check_filepaths[] = $images_root .	"articles/". $small_dir_suffix . $selected_subdir . "/";
					$check_filepaths[] = $images_root .	"articles/". $large_dir_suffix . $selected_subdir . "/";
					$check_filepaths[] = $images_root .	"articles/". $super_dir_suffix . $selected_subdir . "/";
					if ($filetype == "article_tmp") {
						$check_filepaths[] = $images_root .	$tmp_dir_suffix . $selected_subdir . "/";
					}
				} else {
					$check_filepaths[] = $images_root .	"articles/". $tiny_dir_suffix;
					$check_filepaths[] = $images_root .	"articles/". $small_dir_suffix;
					$check_filepaths[] = $images_root .	"articles/". $large_dir_suffix;
					$check_filepaths[] = $images_root .	"articles/". $super_dir_suffix;
					if ($filetype == "article_tmp") {
						$check_filepaths[] = $images_root .	$tmp_dir_suffix;
					}
				}

			} else if ($type_subdir == "articles/" || $type_subdir == "authors/" || $type_subdir == "albums/" || $type_subdir == "contests/") {
				if ($selected_subdir) {
					$check_filepaths[] = $images_root .	$type_subdir. $tiny_dir_suffix . $selected_subdir . "/";
					$check_filepaths[] = $images_root .	$type_subdir. $small_dir_suffix . $selected_subdir . "/";
					$check_filepaths[] = $images_root .	$type_subdir. $large_dir_suffix . $selected_subdir . "/";
					$check_filepaths[] = $images_root .	$type_subdir. $super_dir_suffix . $selected_subdir . "/";
				} else {
					$check_filepaths[] = $images_root .	$type_subdir. $tiny_dir_suffix;
					$check_filepaths[] = $images_root .	$type_subdir. $small_dir_suffix;
					$check_filepaths[] = $images_root .	$type_subdir. $large_dir_suffix;
					$check_filepaths[] = $images_root .	$type_subdir. $super_dir_suffix;
				}
			} else {
				if ($selected_subdir) {
					$check_filepaths[] = $filepath . $selected_subdir . "/";
				} else {
					$check_filepaths[] = $filepath;
				}
			}

			// check if we need to create a new folders where we save photos
			foreach ($check_filepaths as $path) {
				mkdir_recursively($path, $errors);
				if (!$errors) {
					if (!file_exists($path)) {
						$errors .= FOLDER_DOESNT_EXIST_MSG." ".$path."\n";
					} elseif (!is_writable($path)) {
						$errors .= str_replace("{folder_name}", $path, FOLDER_PERMISSION_MESSAGE) . "<br>\n";
					}	
				}
			}

			if ($operation == "ajax" && $newname) {
				$uploaded_filename = $newname;
			} else {
				$uploaded_filename = get_new_file_name($check_filepaths, $filename);
				if ($selected_subdir) {
					$uploaded_filename = $selected_subdir . "/" . $uploaded_filename;
				}
			}

			$file_uploaded = false;
			if ($operation == "ajax") {
				// check size if it's a next part
				$current_size = 0;
				if (file_exists($filepath.$uploaded_filename)) {
					$current_size = filesize($filepath.$uploaded_filename);
				}

				if ($current_size != $uploaded_size) {
					$errors = "File size has a wrong value ";
				} 

				if (!$errors) {
					$fp = @fopen($filepath.$uploaded_filename, "ab");
					if (!$fp) {
						$errors .= UPLOAD_CREATE_ERROR ." <b>" . $filepath . $uploaded_filename . "</b><br>\n";
						$response = array(
							"filename" => $uploaded_filename,
							"errors" => $errors,
						);
						echo json_encode($response);
						return;
					}

					fwrite($fp, $filepart);
					fclose($fp);				
					clearstatcache(); // clear cache to get a new file size
					$uploaded_size = filesize($filepath.$uploaded_filename);
					$response = array(
						"jsIndex" => $jsindex,
						"newname" => $uploaded_filename,
						"uploaded" => $uploaded_size,
					);

					if ($uploaded_size != $filesize) {
						// wait for next file part
						echo json_encode($response);
						return;
					}
					$file_uploaded = true;

				} else {
					$response = array(
						"filename" => $uploaded_filename,
						"errors" => preg_replace("/<[^>]+>/", "", $errors),
					);
					echo json_encode($response);
					return;
				}
			} else {
				$file_uploaded = @move_uploaded_file($tmp_name, $filepath . $uploaded_filename);
			}

			if (!$file_uploaded)
			{
				if (!is_dir($filepath)) {
					$errors .= FOLDER_DOESNT_EXIST_MSG . $filepath ;
				} elseif (!is_writable($filepath)) {
					$errors .= str_replace("{folder_name}", $filepath, FOLDER_PERMISSION_MESSAGE) . "<br>\n";
				} else {
					$errors .= UPLOAD_CREATE_ERROR ." <b>" . $filepath . $uploaded_filename . "</b><br>\n";
				}
			}
			else
			{

				@chmod($filepath . $uploaded_filename, 0666);
				$filename_js = str_replace("'", "\\'", $uploaded_filename);
				if ($filetype == "downloads") {
					$downloads_dir_js = str_replace("\\", "\\\\", $downloads_dir);
					$downloads_dir_js = preg_replace("/^\.\.[\/|\\\\]/", "", $downloads_dir_js);
					$filename_js = $downloads_dir_js . $filename_js;
				}				

				$t->set_var("filename", $filename);
				$t->set_var("filename_js", $filename_js);

				$uploaded_file = str_replace("{filename}", $uploaded_filename, UPLOADED_FILE_MSG);

				$t->set_var("UPLOADED_FILE_MSG", $uploaded_file);

				$t->set_var("generate_tiny", $tiny_generate);
				$t->set_var("generate_small", $small_generate);
				$t->set_var("generate_big", $large_generate);
				$t->set_var("generate_super", $super_generate);
				$t->set_var("tiny_generated", $tiny_generate);
				$t->set_var("small_generated", $small_generate);
				$t->set_var("large_generated", $large_generate);

				// global settings for images resizing (TODO: add to global settings new Images tab)
				$image_tiny_resize = get_setting_value($settings, "image_tiny_resize", 1);
				$image_small_resize = get_setting_value($settings, "image_small_resize", 1);
				$image_large_resize = get_setting_value($settings, "image_large_resize", 1);
				$image_super_resize = get_setting_value($settings, "image_super_resize", 1);
				$canvas_bg = get_setting_value($settings, "image_canvas_bg", "#FFFFFF"); // #FFFFFF - default bg color
				$resize_direction = 1; // only reduce

				// products settings
				$resize_tiny_image = get_setting_value($settings, "resize_tiny_image", 0);
				$resize_small_image = get_setting_value($settings, "resize_small_image", 0);
				$resize_big_image = get_setting_value($settings, "resize_big_image", 0);
				$resize_super_image = get_setting_value($settings, "resize_super_image", 0);
				$canvas_bg = get_setting_value($settings, "canvas_bg", "#FFFFFF"); // #FFFFFF - default bg color
				$resize_direction = 1; // only reduce

				$gd_loaded = true; 
				$tiny_uploaded = false; $small_uploaded = false; $large_uploaded = false; $super_uploaded = false;
				// start product images process
				if ($filetype == "tiny_image" || $filetype == "small_image" || $filetype == "big_image" || $filetype == "super_image" || $filetype == "product_tmp") {

					if ($tiny_generate || $resize_tiny_image) {
						$tiny_width = get_setting_value($settings, "tiny_image_max_width", 32);
						$tiny_height = get_setting_value($settings, "tiny_image_max_height", 32);
						$tiny_resize_type = get_setting_value($settings, "tiny_image_resize_type", "");
						$tiny_canvas_bg = get_setting_value($settings, "tiny_image_canvas_bg", $canvas_bg);

						$image_source = $filepath.$uploaded_filename;
						$image_dest = $images_root.$tiny_dir_suffix.$uploaded_filename;
						if (image_resize($image_source, $image_dest, $tiny_width, $tiny_height, $tiny_resize_type, $resize_direction, $tiny_canvas_bg, "file", false, $errors)) {
							$tiny_uploaded = true;
							@chmod($images_root . $tiny_dir_suffix . $uploaded_filename, 0666);
						}
					}
					if ($small_generate || $resize_small_image) {
						$small_width = get_setting_value($settings, "small_image_max_width", 96);
						$small_height = get_setting_value($settings, "small_image_max_height", 96);
						$small_resize_type = get_setting_value($settings, "small_image_resize_type", "");
						$small_canvas_bg = get_setting_value($settings, "small_image_canvas_bg", $canvas_bg);

						$image_source = $filepath.$uploaded_filename;
						$image_dest = $images_root.$small_dir_suffix.$uploaded_filename;
						if (image_resize($image_source, $image_dest, $small_width, $small_height, $small_resize_type, $resize_direction, $small_canvas_bg, "file", false, $errors)) {
							$small_uploaded = true;
							@chmod($images_root . $small_dir_suffix . $uploaded_filename, 0666);
						}
					}
					if ($large_generate || $resize_big_image) {
						$big_width = get_setting_value($settings, "big_image_max_width", 288);
						$big_height = get_setting_value($settings, "big_image_max_height", 288);
						$big_resize_type = get_setting_value($settings, "big_image_resize_type", "");
						$big_canvas_bg = get_setting_value($settings, "big_image_canvas_bg", $canvas_bg);

						$image_source = $filepath.$uploaded_filename;
						$image_dest = $images_root.$big_dir_suffix.$uploaded_filename;
						if (image_resize($image_source, $image_dest, $big_width, $big_height, $big_resize_type, $resize_direction, $big_canvas_bg, "file", false, $errors)) {
							$large_uploaded = true;
							@chmod($images_root.$big_dir_suffix.$uploaded_filename, 0766);
						}
					}
					if ($super_generate || $resize_super_image) {
						$super_width = get_setting_value($settings, "super_image_max_width", 1024);
						$super_height = get_setting_value($settings, "super_image_max_height", 768);
						$super_resize_type = get_setting_value($settings, "super_image_resize_type", "");
						$super_canvas_bg = get_setting_value($settings, "super_image_canvas_bg", $canvas_bg);

						$image_source = $filepath.$uploaded_filename;
						$image_dest = $images_root.$super_dir_suffix.$uploaded_filename;
						if (image_resize($image_source, $image_dest, $super_width, $super_height, $super_resize_type, $resize_direction, $super_canvas_bg, "file", false, $errors)) {
							$super_uploaded = true;
							@chmod($images_root.$super_dir_suffix.$uploaded_filename, 0766);
						}
					}

					// check for resize errors before saving image
					if ($operation == "ajax" && $errors) {
						$response = array(
							"filename" => $filename,
							"errors" => $errors,
						);
						echo json_encode($response);
						return;
					}

					// if it's AJAX operation save new image in items_images table
					if ($operation == "ajax") {
						// remove tmp image
						unlink($filepath.$uploaded_filename);

						// save settings for image position
						update_admin_settings(array("products_image_position" => $image_position));

						// check product default images
						$sql  = " SELECT tiny_image, small_image, big_image, super_image "; 
						$sql .= " FROM " . $table_prefix . "items "; 
						$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER); 
						$db->query($sql);
						$db->next_record();	
          
						$default_tiny = $db->f("tiny_image");
						$default_small = $db->f("small_image");
						$default_large = $db->f("big_image");
						$default_super = $db->f("super_image");

						// check number of images for this product
						$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "items_images ";
						$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
						$item_images = get_db_value($sql);

						include_once($root_folder_path . "includes/record.php");
						$ii = new VA_Record($table_prefix . "items_images");
						$ii->add_where("image_id", INTEGER);
						$ii->add_textbox("item_id", INTEGER);
						$ii->add_textbox("is_default", INTEGER);
						$ii->add_textbox("image_order", INTEGER);
						$ii->add_textbox("image_position", INTEGER);
						$ii->add_textbox("image_title", TEXT);
						$ii->add_textbox("image_description", TEXT);
						$ii->add_textbox("image_tiny", TEXT);
						$ii->add_textbox("image_small", TEXT);
						$ii->change_property("image_small", USE_SQL_NULL, false);
						$ii->add_textbox("image_large", TEXT);
						$ii->change_property("image_large", USE_SQL_NULL, false);
						$ii->add_textbox("image_super", TEXT);

						// calculate image order
						$sql  = " SELECT MAX(image_order) FROM " . $table_prefix . "items_images ";
						$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
						$sql .= " AND image_position=" . $db->tosql($image_position, INTEGER);
						$image_order = intval(get_db_value($sql)) + 1;

						// set values
						$ii->set_value("item_id", $item_id);
						if ($item_images) {
							$ii->set_value("is_default", 0);
						} else {
							// if it's a first image set it as default
							$ii->set_value("is_default", 1);
						}
						$ii->set_value("image_order", $image_order);
						$ii->set_value("image_position", $image_position);
						$ii->set_value("image_title", basename($uploaded_filename));
						$ii->set_value("image_description", "");
						if ($tiny_uploaded) {
							$response["image_tiny"] = $image_site_url.$save_root.$tiny_dir_suffix.$uploaded_filename;
							$ii->set_value("image_tiny", $response["image_tiny"]);
						}
						if ($small_uploaded) {
							$response["image_small"] = $image_site_url.$save_root.$small_dir_suffix.$uploaded_filename;
							$ii->set_value("image_small", $response["image_small"]);
						}
						if ($large_uploaded) {
							$response["image_large"] = $image_site_url.$save_root.$big_dir_suffix.$uploaded_filename;
							$ii->set_value("image_large", $response["image_large"]);
						}
						if ($super_uploaded) {
							$response["image_super"] = $image_site_url.$save_root.$super_dir_suffix.$uploaded_filename;
							$ii->set_value("image_super", $response["image_super"]);
						}
						$ii->insert_record();
						$response["saved"] = true;

						// if product doesn't has default image and it's a first image set it as default
						if (!$item_images && !$default_tiny && !$default_small && !$default_large && !$default_super) {
							$response["default"] = "1";
							$sql = " UPDATE " . $table_prefix . "items ";
							$sql.= " SET tiny_image=" . $db->tosql($response["image_tiny"], TEXT);
							$sql.= ", small_image=" . $db->tosql($response["image_small"], TEXT);
							$sql.= ", big_image=" . $db->tosql($response["image_large"], TEXT);
							$sql.= ", super_image=" . $db->tosql($response["image_super"], TEXT);
							$sql.= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
							$db->query($sql);
						} else {
							$response["default"] = "0";
						}

						echo json_encode($response);
						return;
					}
				}
				// end product images process


				// start article images process
				if ($filetype == "article_tiny" || $filetype == "article_small" || $filetype == "article_large" || $filetype == "article_super" || $filetype == "article_tmp") {

					if ($tiny_generate || $resize_tiny_image) {
						$tiny_width = get_setting_value($settings, "tiny_image_max_width", 32);
						$tiny_height = get_setting_value($settings, "tiny_image_max_height", 32);
						$tiny_resize_type = get_setting_value($settings, "tiny_image_resize_type", "");
						$tiny_canvas_bg = get_setting_value($settings, "tiny_image_canvas_bg", $canvas_bg);

						$image_source = $filepath.$uploaded_filename;
						$image_dest = $images_root."articles/".$tiny_dir_suffix.$uploaded_filename;
						if (image_resize($image_source, $image_dest, $tiny_width, $tiny_height, $tiny_resize_type, $resize_direction, $tiny_canvas_bg, "file", false, $errors)) {
							$tiny_uploaded = true;
							@chmod($image_dest, 0666);
						} else {
							/*
							if ($operation == "ajax") {
								$response = array(
									"filename" => $filename,
									"errors" => $errors,
								);
								echo json_encode($response);
								return;
							}//*/
						}
					}
					if ($small_generate || $resize_small_image) {
						$small_width = get_setting_value($settings, "small_image_max_width", 96);
						$small_height = get_setting_value($settings, "small_image_max_height", 96);
						$small_resize_type = get_setting_value($settings, "small_image_resize_type", "");
						$small_canvas_bg = get_setting_value($settings, "small_image_canvas_bg", $canvas_bg);

						$image_source = $filepath.$uploaded_filename;
						$image_dest = $images_root."articles/".$small_dir_suffix.$uploaded_filename;
						if (image_resize($image_source, $image_dest, $small_width, $small_height, $small_resize_type, $resize_direction, $small_canvas_bg, "file", false, $errors)) {
							$small_uploaded = true;
							@chmod($image_dest, 0766);
						}
					}
					if ($large_generate || $resize_big_image) {
						$big_width = get_setting_value($settings, "big_image_max_width", 288);
						$big_height = get_setting_value($settings, "big_image_max_height", 288);
						$big_resize_type = get_setting_value($settings, "big_image_resize_type", "");
						$big_canvas_bg = get_setting_value($settings, "big_image_canvas_bg", $canvas_bg);
						$image_source = $filepath.$uploaded_filename;
						$image_dest = $images_root."articles/".$large_dir_suffix.$uploaded_filename;
						if (image_resize($image_source, $image_dest, $big_width, $big_height, $big_resize_type, $resize_direction, $big_canvas_bg, "file", false, $errors)) {
							$large_uploaded = true;
							@chmod($image_dest, 0766);
						}
					}
					if ($super_generate || $resize_super_image) {
						$super_width = get_setting_value($settings, "super_image_max_width", 1024);
						$super_height = get_setting_value($settings, "super_image_max_height", 768);
						$super_resize_type = get_setting_value($settings, "super_image_resize_type", "");
						$super_canvas_bg = get_setting_value($settings, "super_image_canvas_bg", $canvas_bg);

						$image_source = $filepath.$uploaded_filename;
						$image_dest = $images_root."articles/".$super_dir_suffix.$uploaded_filename;
						if (image_resize($image_source, $image_dest, $super_width, $super_height, $super_resize_type, $resize_direction, $super_canvas_bg, "file", false, $errors)) {
							$super_uploaded = true;
							@chmod($image_dest, 0766);
						}
					}

					// check for resize errors before saving image
					if ($operation == "ajax" && $errors) {
						$response = array(
							"filename" => $filename,
							"errors" => $errors,
						);
						echo json_encode($response);
						return;
					}

					// if it's AJAX operation save new image in items_images table
					if ($operation == "ajax") {
						// remove tmp image
						unlink($filepath.$uploaded_filename);

						// save settings for image position
						update_admin_settings(array("articles_image_position" => $image_position));

						// check product default images
						$sql  = " SELECT image_tiny, image_small, image_large, image_super "; 
						$sql .= " FROM " . $table_prefix . "articles "; 
						$sql .= " WHERE article_id=" . $db->tosql($article_id, INTEGER); 
						$db->query($sql);
						$db->next_record();	
          
						$default_tiny = $db->f("image_tiny");
						$default_small = $db->f("image_small");
						$default_large = $db->f("image_large");
						$default_super = $db->f("image_super");

						// check number of images for this product
						$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "articles_images ";
						$sql .= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
						$article_images = intval(get_db_value($sql));

						include_once($root_folder_path . "includes/record.php");
						$ii = new VA_Record($table_prefix . "articles_images");
						$ii->add_where("image_id", INTEGER);
						$ii->add_textbox("article_id", INTEGER);
						$ii->add_textbox("is_default", INTEGER);
						$ii->add_textbox("image_order", INTEGER);
						$ii->add_textbox("image_position", INTEGER);
						$ii->add_textbox("image_title", TEXT);
						$ii->add_textbox("image_description", TEXT);
						$ii->add_textbox("image_tiny", TEXT);
						$ii->add_textbox("image_small", TEXT);
						$ii->change_property("image_small", USE_SQL_NULL, false);
						$ii->add_textbox("image_large", TEXT);
						$ii->change_property("image_large", USE_SQL_NULL, false);
						$ii->add_textbox("image_super", TEXT);

						// calculate image order
						$sql  = " SELECT MAX(image_order) FROM " . $table_prefix . "articles_images ";
						$sql .= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
						$sql .= " AND image_position=" . $db->tosql($image_position, INTEGER);
						$image_order = intval(get_db_value($sql)) + 1;

						// set values
						$ii->set_value("article_id", $article_id);
						if ($article_images) {
							$ii->set_value("is_default", 0);
						} else {
							// if it's a first image set it as default
							$ii->set_value("is_default", 1);
						}
						$ii->set_value("image_order", $image_order);
						$ii->set_value("image_position", $image_position);
						$ii->set_value("image_title", basename($uploaded_filename));
						$ii->set_value("image_description", "");
						if ($tiny_uploaded) {
							$response["image_tiny"] = $image_site_url.$save_root."articles/".$tiny_dir_suffix.$uploaded_filename;
							$ii->set_value("image_tiny", $response["image_tiny"]);
						}
						if ($small_uploaded) {
							$response["image_small"] = $image_site_url.$save_root."articles/".$small_dir_suffix.$uploaded_filename;
							$ii->set_value("image_small", $response["image_small"]);
						}
						if ($large_uploaded) {
							$response["image_large"] = $image_site_url.$save_root."articles/".$large_dir_suffix.$uploaded_filename;
							$ii->set_value("image_large", $response["image_large"]);
						}
						if ($super_uploaded) {
							$response["image_super"] = $image_site_url.$save_root."articles/".$super_dir_suffix.$uploaded_filename;
							$ii->set_value("image_super", $response["image_super"]);
						}
						$ii->insert_record();
						$response["saved"] = true;

						// if product doesn't has default image and it's a first image set it as default
						if (!$article_images && !$default_tiny && !$default_small && !$default_large && !$default_super) {
							$response["default"] = "1";
							$sql = " UPDATE " . $table_prefix . "articles ";
							$sql.= " SET image_tiny=" . $db->tosql($response["image_tiny"], TEXT);
							$sql.= ", image_small =" . $db->tosql($response["image_small"], TEXT);
							$sql.= ", image_large=" . $db->tosql($response["image_large"], TEXT);
							$sql.= ", image_super=" . $db->tosql($response["image_super"], TEXT);
							$sql.= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
							$db->query($sql);
						} else {
							$response["default"] = "0";
						}

						echo json_encode($response);
						return;
					}
				}
				// end articles images process


				if ($filetype == "ad_small" || $filetype == "ad_large") {
					$ads_info = get_settings("ads");

					$ads_small_resize = get_setting_value($ads_info, "image_small_resize", 0);
					$ads_large_resize = get_setting_value($ads_info, "image_large_resize", 0);

					if ($small_generate || ($ads_small_resize && $filetype == "ad_small")) {
						$small_width = get_setting_value($ads_info, "image_small_width", 128);
						$small_height = get_setting_value($ads_info, "image_small_height", 128);
						$small_resize_type = get_setting_value($ads_info, "image_small_resize_type", "");
						$small_canvas_bg = get_setting_value($ads_info, "image_small_canvas_bg", $canvas_bg);

						$image_source = $filepath.$uploaded_filename;
						$image_dest = $images_root."ads/".$small_dir_suffix.$uploaded_filename;
						if (image_resize($image_source, $image_dest, $small_width, $small_height, $small_resize_type, $resize_direction, $small_canvas_bg, "file", false, $errors)) {
							$small_uploaded = true;
							@chmod($image_dest, 0666);
						}
					}

					if ($large_image || ($ads_large_resize && $filetype == "ad_large")) {
						$large_width = get_setting_value($ads_info, "image_large_width", 128);
						$large_height = get_setting_value($ads_info, "image_large_height", 128);
						$large_resize_type = get_setting_value($ads_info, "image_large_resize_type", "");
						$large_canvas_bg = get_setting_value($ads_info, "image_large_canvas_bg", $canvas_bg);

						$image_source = $filepath.$uploaded_filename;
						$image_dest = $images_root."ads/".$large_dir_suffix.$uploaded_filename;
						if (image_resize($image_source, $image_dest, $large_width, $large_height, $large_resize_type, $resize_direction, $large_canvas_bg, "file", false, $errors)) {
							$large_uploaded = true;
							@chmod($image_dest, 0666);
						}
					}
				}

				// process authors and albums images
				if ($type_subdir == "authors/" || $type_subdir == "albums/" || $type_subdir == "contests/") {
					//type_subdir : authors/, albums/, contests/
					if ($tiny_generate || $image_tiny_resize) {
						$tiny_width = get_setting_value($settings, "image_tiny_width", 40);
						$tiny_height = get_setting_value($settings, "image_tiny_height", 40);
						$tiny_resize_type = get_setting_value($settings, "image_tiny_resize_type", "");
						$tiny_canvas_bg = get_setting_value($settings, "image_tiny_canvas_bg", $canvas_bg);

						$image_source = $filepath.$uploaded_filename;
						$image_dest = $images_root.$type_subdir.$tiny_dir_suffix.$uploaded_filename;
						if (image_resize($image_source, $image_dest, $tiny_width, $tiny_height, $tiny_resize_type, $resize_direction, $tiny_canvas_bg, "file", false, $errors)) {
							$tiny_uploaded = true;
							@chmod($image_dest, 0666);
						}
					}
					if ($small_generate || $image_small_resize) {
						$small_width = get_setting_value($settings, "image_small_width", 100);
						$small_height = get_setting_value($settings, "image_small_height", 100);
						$small_resize_type = get_setting_value($settings, "image_small_resize_type", "");
						$small_canvas_bg = get_setting_value($settings, "image_small_canvas_bg", $canvas_bg);

						$image_source = $filepath.$uploaded_filename;
						$image_dest = $images_root.$type_subdir.$small_dir_suffix.$uploaded_filename;
						if (image_resize($image_source, $image_dest, $small_width, $small_height, $small_resize_type, $resize_direction, $small_canvas_bg, "file", false, $errors)) {
							$small_uploaded = true;
							@chmod($image_dest, 0666);
						}
					}
					if ($large_generate || $image_large_resize) {
						$large_width = get_setting_value($settings, "image_large_width", 300);
						$large_height = get_setting_value($settings, "image_large_height", 300);
						$large_resize_type = get_setting_value($settings, "image_large_resize_type", "");
						$large_canvas_bg = get_setting_value($settings, "image_large_canvas_bg", $canvas_bg);

						$image_source = $filepath.$uploaded_filename;
						$image_dest = $images_root.$type_subdir.$large_dir_suffix.$uploaded_filename;
						if (image_resize($image_source, $image_dest, $large_width, $large_height, $large_resize_type, $resize_direction, $large_canvas_bg, "file", false, $errors)) {
							$large_uploaded = true;
							@chmod($image_dest, 0766);
						}
					}
					if ($super_generate || $image_super_resize) {
						$super_width = get_setting_value($settings, "image_super_width", 900);
						$super_height = get_setting_value($settings, "image_super_height", 900);
						$super_resize_type = get_setting_value($settings, "image_super_resize_type", "");
						$super_canvas_bg = get_setting_value($settings, "image_super_canvas_bg", $canvas_bg);

						$image_source = $filepath.$uploaded_filename;
						$image_dest = $images_root.$type_subdir.$super_dir_suffix.$uploaded_filename;
						if (image_resize($image_source, $image_dest, $super_width, $super_height, $super_resize_type, $resize_direction, $super_canvas_bg, "file", false, $errors)) {
							$super_uploaded = true;
							@chmod($image_dest, 0766);
						}
					}
				}
				// end albums, authors, contests images process

			}
		} else {
			// for AJAX call return error
			if ($operation == "ajax") {
				$response = array(
					"filename" => $filename,
					"errors" => $errors,
				);
				echo json_encode($response);
				return;
			}
		}
	}

	$t->set_var("filetype", $filetype);

	if ($operation == "1" && !strlen($errors)) {
		$t->set_var("before_upload", "");
		$t->parse("after_upload", false);
	} else {
		if ($filetype == "small_image" || $filetype == "big_image" || $filetype == "super_image" || $filetype == "ad_large") {
			// check old settings
			$sql  = " SELECT is_generate_small_image, is_generate_big_image FROM " . $table_prefix . "admins ";
			$sql .= " WHERE admin_id=" . $db->tosql(get_session("session_admin_id"), INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$is_generate_tiny = 0;
				$is_generate_small = $db->f("is_generate_small_image");
				$is_generate_big = $db->f("is_generate_big_image");
			}
			// check new settings
			$image_settings = get_admin_settings(array("is_generate_tiny_image", "is_generate_small_image", "is_generate_big_image"));
			$tiny_generate = get_setting_value($image_settings, "is_generate_tiny_image", $is_generate_tiny);
			$small_generate = get_setting_value($image_settings, "is_generate_small_image", $is_generate_small);
			$large_generate = get_setting_value($image_settings, "is_generate_big_image", $is_generate_big);

			$tiny_generate_checked = ($tiny_generate) ? "checked" : "";
			$small_generate_checked = ($small_generate) ? "checked" : "";
			$large_generate_checked = ($large_generate) ? "checked" : "";
			if ($filetype == "small_image" || $filetype == "big_image" || $filetype == "super_image") {
				$t->set_var("tiny_generate_checked", $tiny_generate_checked);
				$t->parse("tiny_generate_block", false);
			}
			if ($filetype == "big_image" || $filetype == "super_image"  || $filetype == "ad_large") {
				$t->set_var("small_generate_checked", $small_generate_checked);
				$t->parse("small_generate_block", false);
			}
			if ($filetype == "super_image") {
				$t->set_var("large_generate_checked", $large_generate_checked);
				$t->parse("large_generate_block", false);
			}
		} else if ($image_type) {
			// check author settings
			$image_settings = get_admin_settings(array($image_type."_tiny_generate", $image_type."_small_generate", $image_type."_large_generate"));
			$tiny_generate = get_setting_value($image_settings, $image_type."_tiny_generate", "");
			$small_generate = get_setting_value($image_settings, $image_type."_small_generate", "");
			$large_generate = get_setting_value($image_settings, $image_type."_large_generate", "");

			$tiny_generate_checked = ($tiny_generate) ? "checked" : "";
			$small_generate_checked = ($small_generate) ? "checked" : "";
			$large_generate_checked = ($large_generate) ? "checked" : "";
			if ($filetype == $image_type."_super" || $filetype == $image_type."_large" || $filetype == $image_type."_small") {
				$t->set_var("tiny_generate_checked", $tiny_generate_checked);
				$t->parse("tiny_generate_block", false);
			}
			if ($filetype == $image_type."_super" || $filetype == $image_type."_large") {
				$t->set_var("small_generate_checked", $small_generate_checked);
				$t->parse("small_generate_block", false);
			}
			if ($filetype == $image_type."_super") {
				$t->set_var("large_generate_checked", $large_generate_checked);
				$t->parse("large_generate_block", false);
			}
		} 

		$t->parse("before_upload", false);
		$t->set_var("after_upload", "");
	}

	if (strlen($errors)) {
		$t->set_var("after_upload", "");
		$t->set_var("errors_list", $errors);
		$t->parse("errors", false);
	} else {
		$t->set_var("errors", "");
	}

	$t->pparse("main");

?>