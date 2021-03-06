<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_fm_download_file.php                               ***
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

	// get dir
	fm_dir($dir, $site_dir, $top_dir);
	$dir_path = $dir; // assign variable used in script
	$download_file = terminator(get_param("download_file"));
	
	$error = check_dir_and_file($dir_path, $download_file, "download");

	if (!$error){
		if(file_exists($dir_path . "/" . $download_file)) {
			if( in_array(get_ext_file($download_file), $conf_array_files_ext)) {
				header("Content-Disposition: attachment; filename=" . $download_file); 
				$file = fread(fopen($dir_path . "/" . $download_file, "rb"), filesize($dir_path . "/" . $download_file)); 
				print $file;
				exit();
			}
		}
	} else {
		set_session("fm_error",$error);
		header("Location: " . $host."admin_fm.php?root_dir=".$dir_path);
	}
