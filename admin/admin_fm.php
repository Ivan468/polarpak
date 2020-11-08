<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_fm.php                                             ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once($root_folder_path . "includes/file_functions.php");
	include_once($root_folder_path . "includes/admin_fm_functions.php");

	include_once("./admin_common.php");
	include_once("./admin_fm_config.php");

	check_admin_security("filemanager");

	// get root dir
	fm_dir($dir, $site_dir, $top_dir);
	$root_dir = $dir;
	
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_fm.html");

	$t->set_var("admin_href","admin.php");
	$t->set_var("admin_view_file_href", "admin_fm_view_file.php");
	$t->set_var("admin_download_file_href", "admin_fm_download_file.php");
	$t->set_var("admin_filemanager_fm_href", "admin_fm.php");
	$t->set_var("admin_upload_href", "admin_fm_upload_files.php");
	$t->set_var("admin_edit_file_href", "admin_fm_edit_file.php");
	$t->set_var("admin_conf_href", "admin_conf_filemanager.php");
	$t->set_var("admin_newfile_href", "admin_fm_newfile.php");
	$t->set_var("root_dir", htmlspecialchars($root_dir));
	$t->set_var("current_dir", htmlspecialchars($root_dir));
	$t->set_var("site_dir", htmlspecialchars($site_dir));

	
	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_articles_top.php");
	$s->set_sorter(ID_MSG, "sorter_category_id", "1", "category_id");
	$s->set_sorter(ARTICLES_TYPE_MSG, "sorter_category_name", "2", "category_name");
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_articles_top.php");
	
	include_once("./admin_header.php");
	include_once("./admin_footer.php");
	
	$error = get_session("fm_error");

	//delete file or dir
	$del_name = terminator(get_param("del_name"));
	if(strlen($del_name) > 0) {
		$error_del = check_dir_and_file($root_dir, "", "chmod");
		if (!strlen($error_del)){
			if (!@rm($root_dir . "/" . $del_name)){
				$error .= fm_errors(113,$del_name);
			}
		} else {
			$error .= $error_del;
		}
	}

	// rename file
	$old_name = terminator(get_param("old_name"));
	$file_rename = terminator(get_param("file_rename"));
	
	if((strlen($old_name) > 0) && (strlen($file_rename) > 0)) {
		$error_rename = check_dir_and_file($root_dir, $file_rename, "rename", $old_name);
		if (!$error_rename){
			if (in_array(get_ext_file($old_name), $array_text_files)){
				if (in_array(get_ext_file($file_rename), $array_text_files)){
					if (!@rename($root_dir . "/" . $old_name, $root_dir . "/" . $file_rename)){
						$error_rename .= fm_errors(109,$old_name,$file_rename);
					}
				} else {
					$error_rename .= fm_errors(107,$old_name,$file_rename);
				}
			} else if (in_array(get_ext_file($old_name), $array_image_file)){
				if (in_array(get_ext_file($file_rename), $array_image_file)){
					if (!@rename($root_dir . "/" . $old_name, $root_dir . "/" . $file_rename)){
						$error_rename .= fm_errors(109,$old_name,$file_rename);
					}
				} else {
					$error_rename .= fm_errors(107,$old_name,$file_rename);
				}
			} else if (in_array(get_ext_file($old_name), $array_download_file)){
				if (in_array(get_ext_file($file_rename), $array_download_file)){
					if (!@rename($root_dir . "/" . $old_name, $root_dir . "/" . $file_rename)){
						$error_rename .= fm_errors(109,$old_name,$file_rename);
					}
				} else {
					$error_rename .= fm_errors(107,$old_name,$file_rename);
				}
			}
		}
		if ($error_rename){
			$error .= $error_rename;
		}
	}
	
	// create a new folder
	$new_dname = terminator(get_param("new_dname"));
	if(strlen($new_dname) > 0) {
		if (!@mkdir($root_dir . "/" . $new_dname, 0755)){
			$error .= fm_errors(110,$new_dname);
		}
	}
	// change file mode
	$new_mode = terminator(get_param("new_mode"));
	$file_name = terminator(get_param("file_name"));

	if(strlen($new_mode) > 0) {
		if (strlen($file_name)) {
			$error_file = check_dir_and_file($root_dir, $file_name, "view");
			if ($error_file) {
				$error .= $error_file;
			} else {
				if (!@chmod($root_dir . "/" . $file_name, intval($new_mode, 8))){
					$error .= fm_errors(111, $file_name, $root_dir);
				}
			}
		} else if (strlen($root_dir)) {
			$root_dir = check_dir_path($root_dir);
			if (!$root_dir || $root_dir == "..") {
				$error .= fm_errors(102, $root_dir);
			} else {
				if (!@chmod($root_dir, intval($new_mode, 8))){
					$error .= fm_errors(112, $root_dir);
				}
			}
		}
	}
	
	// errors
	if ($error){
		$t->set_var("error",$error);
		$t->parse("Errors",false);
		set_session("fm_error","");
	}
	
	// create a directory tree
	$t->set_var("host", $host);

	// var for current dir
	$curent_dir_name = explode("/", $root_dir);
	$curent_dir_name = $curent_dir_name[(count($curent_dir_name) - 1)];
	$t->set_var("curent_dir_name", $curent_dir_name);
	$curent_dir_perm =  get_perm_file($root_dir);
	$t->set_var("curent_dir_perm", $curent_dir_perm);
	$digit_curent_dir_perm = 0 . chmodnum($curent_dir_perm);
	$t->set_var("digit_curent_dir_perm", $digit_curent_dir_perm);
	$name_sort = terminator(get_param("name_sort"));

	if(strlen($name_sort) == 0) {
		$name_sort = "SORT_ASC";
	}
	$size_sort = terminator(get_param("size_sort"));

	$t->set_var("no_records", "");

/*
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
//*/


	// get folders info
	$array_cont_dir = read_current_dir($root_dir, $conf_array_dirs);

	if(is_array($array_cont_dir)) {
		array_shift($array_cont_dir);
		if(count($array_cont_dir) > 0) {
			foreach($array_cont_dir as $res) {
				 $sortAux[] = $res['dir_name'];
			}
			if($name_sort == "SORT_DESC") {
				array_multisort($sortAux, SORT_DESC, $array_cont_dir);
			}
			else {
				array_multisort($sortAux, SORT_ASC, $array_cont_dir);
			}
		}
			$tmp_cont_dir["dir_name"] =  "..";
			$tmp_cont_dir["dir_delete"] =  "";
			$tmp_cont_dir["dir_path"] = "";
			$tmp_cont_dir["delete"] = "";
			$tmp_cont_dir["rename"] = "";
		array_unshift($array_cont_dir, $tmp_cont_dir);
		foreach($array_cont_dir as $k => $v) {
			if(($k == 0) && ($root_dir != $conf_root_dir)) {
				$array_up_dir = explode("/", $root_dir);
				unset($array_up_dir[(count($array_up_dir) - 1)]);
				$up_dir = implode("/", $array_up_dir);
				$t->set_var("dir_id", "<a href='admin_fm.php?root_dir=" . $up_dir . "'><img src='images/folder.gif' border=0 width=18 height=14 border=0>&nbsp;" . $v["dir_name"] . "</a>");
				foreach($v as $id =>$val) {
					$t->set_var($id, $val);
				}
				$t->parse("records_dir", true);
			}
			elseif($k != 0) {
				$t->set_var("dir_id", "<a href='admin_fm.php?root_dir=" . $root_dir . "/" . $v["dir_name"] . "'><img src='images/folder.gif' border=0 width=18 height=14 border=0>&nbsp;" . $v["dir_name"] . "</a>");
				if(in_array($v["dir_name"], $conf_array_dirs)) {
					$t->set_var("dir_name", $v["dir_name"]);
					$t->set_var("rename", "");
					$t->set_var("delete", "");
					$t->set_var("dir_delete", "");
					$t->set_var("dir_path", $v["dir_path"]);
				}
				else {
					foreach($v as $id =>$val) {
						$t->set_var($id, $val);
					}
				}
				$t->parse("records_dir", true);
			}
		}
	}
	else {
		$t->set_var("records_dir", "");
	}

	// get files info
	$array_cont_file = read_cur_files($root_dir, $conf_array_files_ext, $conf_root_dir);

	if(is_array($array_cont_file) && count($array_cont_file) > 0) {

		if(isset($name_sort) && strlen($size_sort) == 0) {
			foreach($array_cont_file as $res) {
				 $sortfile[] = $res['file_name'];
			}
			if($name_sort == "SORT_DESC") {
				array_multisort($sortfile, SORT_DESC, $array_cont_file);
			}
			else {
				array_multisort($sortfile, SORT_ASC, $array_cont_file);
			}
		}elseif(isset($size_sort)) {

			foreach($array_cont_file as $res) {
				 $sortfile_size[] = $res['byte_size'];
			}

			if($size_sort == "SORT_DESC") {
				array_multisort($sortfile_size, SORT_DESC, $array_cont_file);
			}
			else {
				array_multisort($sortfile_size, SORT_ASC, $array_cont_file);
			}
		}
		foreach($array_cont_file as $k => $v) {
			foreach($v as $id =>$val) {
				$t->set_var($id, $val);
			}
			$t->parse("records_file", true);				
		}
	}
	else {
		$t->set_var("records_file", "");
	}
	
	if ($name_sort == "SORT_ASC") {
		$t->set_var("name_sort", "SORT_DESC");
	} else {
		$t->set_var("name_sort", "SORT_ASC");
	}

	if($size_sort == "SORT_ASC") {
		$t->set_var("size_sort", "SORT_DESC");
	}
	else {
		$t->set_var("size_sort", "SORT_ASC");
	}

	$root_dir = str_replace("\\", "/", $root_dir);
	$dir_bc = explode("/", $root_dir);

	$full_path = "";
	foreach ($dir_bc as $dir_name) {
		if ($full_path) { $full_path .= "/"; }
		$full_path .= $dir_name;
		$dir_url = "admin_fm.php?root_dir=".urlencode($full_path);
		$t->set_var("dir_url", htmlspecialchars($dir_url));
		$t->set_var("dir_name", htmlspecialchars($dir_name));
		if ($dir_name == "..") {
			$t->set_var("dir_class", "home");
		} else {
			$t->set_var("dir_class", "");
		}
		$t->parse("dir_bc", true);
	}
	$t->set_var("root_dir", htmlspecialchars($root_dir));

	if($root_dir == $conf_root_dir) {
		$t->set_var("bottom", "");
		$t->set_var("records_file", "");
	}
	else {
		$t->parse("bottom", false);
	}	
	$t->pparse("main");
