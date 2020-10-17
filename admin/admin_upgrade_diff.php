<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_upgrade_diff.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ($root_folder_path . "messages/" . $language_code . "/install_messages.php");
	include_once("./admin_common.php");

	check_admin_security("system_upgrade");

	// define basic arrays for files search
	global $core_folders, $core_skip, $core_ext, $core_files;
	
	$core_folders = array(
		"admin", "blocks", "editor", "includes", "js",
		"messages", "payments", "shipping", "sms", "styles", "swf", "templates"
	);	
	$core_skip = array("cvs", "svn", ".cvn", ".svn", "..", ".");
	$core_ext  = array("php", "html", "css", "js");
	$core_files = explode(",", get_param("compare_files"));
	
	$root_dir      = "..";
	$tmp_save_path = get_setting_value($settings, "tmp_dir");
	if (!$tmp_save_path) {
		$tmp_save_path = dirname(dirname(__FILE__)) . "/images/";
	}
	$tmp_save_path .= "diff_tmp";
	
	$errors = "";
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_upgrade_diff.html");
	$t->set_var("admin_upgrade_href", "admin_upgrade_diff.php");
	$t->set_var("admin_upgrade_diff_file_href", "admin_upgrade_diff_file.php");

	$t->set_var("tree_name", "corefiles");
	$t->set_var("response_url", "admin_upgrade_diff.php?operation=load_files");
	
	// ajax request parser
	$operation = get_param("operation");
	if ($operation == "load_files") {
		$target_folder = folder_from_js(get_param("branch_id"));
		if ($target_folder && is_dir($root_dir . "/" . $target_folder)) {
			$folders = array();
			$files  = array();
			get_folders_and_files($folders, $files, $root_dir . "/" . $target_folder, 1);
			if ($folders) {
				foreach ($folders AS $folder) {
					$t->set_var("folder",    $folder);
					$t->set_var("folder_js", folder_to_js($target_folder . "/" . $folder));
					$t->parse("folder_block");
				}
			}
			if ($files) {
				foreach ($files AS $file) {
					$t->set_var("file",    $target_folder . "/" . $file);
					$t->set_var("file_js", folder_to_js($target_folder . "/" .  $file));
					$t->parse("file_block");
				}
			}
			$t->parse("files_block");
			echo $t->get_var("files_block");
		}
		exit;
	}
	
	// $last_version is version from http://www.viart.com/viart_shop.xml
	$viart_xml = @fsockopen("www.viart.com", 80, $errno, $errstr, 12);

	if ($viart_xml)
	{
		fputs($viart_xml, "GET /viart_shop.xml HTTP/1.0\r\n");
		fputs($viart_xml, "Host: www.viart.com\r\n");
		fputs($viart_xml, "Referer: http://www.viart.com\r\n");
		fputs($viart_xml, "User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)\r\n\r\n");

		$last_version = "";

		while (!feof($viart_xml)) {
			$line = fgets($viart_xml);
			if (strpos($line, "Program_Version")) {
				for ($i = 0; $i < strlen($line); $i++) if ((is_numeric($line[$i])) or ($line[$i] == ".")) $last_version .= $line[$i];
			} elseif (strpos($line, "Primary_Download_URL")) {
				$download_file = trim(rtrim(strip_tags($line)));
			}
		}

		fclose($viart_xml);
	} else {
		$last_version = VA_RELEASE;
		$t->parse("connection_error", false);
	}	
	// end $last_version find out

	$sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type='version' AND setting_name='number'";
	$db->query($sql);
	if ($db->next_record()) {
		$current_db_version = $db->f("setting_value");
	} elseif (defined("VA_RELEASE")) {
		$current_db_version = VA_RELEASE;
	} else {
		$current_db_version = "2.5";
	}
	
	$t->set_var("version_number", $current_db_version);
	$t->set_var("last_version",   $last_version);
	
	// core folders check
	$folders = array();
	$files  = array();
	get_folders_and_files($folders, $files, $root_dir);
	if ($folders) {
		foreach ($folders AS $folder) {
			$t->set_var("folder",    $folder);
			$t->set_var("folder_js", folder_to_js($folder));
			$t->parse("folder_block");
		}
		if (!$operation) {
			$no_core_folders = array_diff($core_folders, $folders);
			if ($no_core_folders) {
				$errors .= FOLDER_DOESNT_EXIST_MSG . ": " . implode(", ", $no_core_folders);
			}
		}
	}
	if ($files) {
		foreach ($files AS $file) {
			$t->set_var("file",   $file);
			$t->set_var("file_js", folder_to_js($file));
			$t->parse("file_block");
		}
	}
	$t->parse("files_block");
	
	if ($core_files) {
		foreach ($core_files AS $file) {
			$t->set_var("file",   $file);
			$t->set_var("file_js", folder_to_js($file));
			$t->parse("selected_file_block");
		}
	}
	// end core folders check
	
	// diff files
	$diff_errors = "";
	$compare_type = get_param("compare_type");
	$folder_name  = get_param("folder_name");
	$compare_files_type = get_param("compare_files_type");
	if ($operation == "diff") {
		if ($compare_type == 1) {
			if ($download_file) {
				if (!is_dir($tmp_save_path)) {
					if (@mkdir($tmp_save_path)) {
						$diff_errors .= "Couldn`t create temporary folder <b>" . $tmp_save_path . "</b><br/>";
					}
				}
				if (!$diff_errors) {
					$version_file = $tmp_save_path . "/" . $last_version . ".zip";
					
					// download latest file from server
					if (!is_file($version_file)) {
						$destination = @fopen($version_file, "w");
						if ($destination) {
							$source = fopen($download_file, "r");
							while ($a = fread($source, 1024)) fwrite($destination, $a);
							fclose($source);
							fclose($destination);
						} else {
							$diff_errors .= "Couldn`t write current version $last_version file to <b>" . $tmp_save_path . "</b><br/>";
						}
					}
					
					// unzip downloaded file
					if (function_exists("zip_open")) {
						$zip = zip_open($version_file);
						if (is_resource($zip)) {
							while ($zip_entry = zip_read($zip)) {
								$zip_entry_name      = zip_entry_name($zip_entry);
								$zip_entry_name_dest = $tmp_save_path . "/" . $zip_entry_name;
								$zip_entry_filesize  = zip_entry_filesize($zip_entry);

								if ($zip_entry_filesize) {
									if ($destination = @fopen( $zip_entry_name_dest , 'w+')) {
										fwrite($destination, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
										fclose($destination);
									} else {
										$diff_errors .= "Couldn`t write file <b>$zip_entry_name_dest</b><br/>";
										break;
									}
								} else {
									if (!is_dir($zip_entry_name_dest)) {
										@mkdir($zip_entry_name_dest);
									}
								}
							}
							zip_close($zip);
						} else {
							$diff_errors .= "Couldn`t unzip downloaded archive <b>$version_file</b><br/>";
							$diff_errors .= zipFileErrMsg($zip) . "<br/>";
						}
					} else {
						$diff_errors .= "ZIP extansion is not installed, please unzip <b>$version_file</b> manually to continue<br/>";
						$compare_type = 2;
						$folder_name  = $tmp_save_path;
					}
					
					if (!$diff_errors) {
						$compare_type = 2;
						$folder_name  = $tmp_save_path;
					}
				}
			} else {
				$diff_errors .= "No current version $last_version file to download<br/>";
			}
		}
		
		// if unzipped successfully or folder is set up process diff file by file
		if (!$diff_errors) {
			if ($compare_files_type == 1) {
				$core_ext = array("php");
			} elseif ($compare_files_type == 2) {
				$core_ext = array("html");
			} elseif ($compare_files_type == 3) {
				$core_ext = false;
			}
			compare_folders($root_dir, $folder_name);
			$t->parse("results");			
		}
	}
	
	$errors .= $diff_errors;
	$t->set_var("folder_name", $folder_name);
	if ($compare_type == 2) {
		$t->set_var("folder_name_disabled", "");
		$t->set_var("compare_type_2_checked", "checked");
		$t->set_var("compare_type_1_checked", "");
	} else {
		$t->set_var("folder_name_disabled", "disabled");
		$t->set_var("compare_type_1_checked", "checked");
		$t->set_var("compare_type_2_checked", "");		
	}
	
	$t->set_var("compare_files_type_0_checked", "");
	$t->set_var("compare_files_type_1_checked", "");
	$t->set_var("compare_files_type_2_checked", "");
	$t->set_var("compare_files_type_3_checked", "");
	$t->set_var("compare_files_block_style", "style='display:none;'");	
	if ($compare_files_type == 3) {
		$t->set_var("compare_files_type_3_checked", "checked");
		$t->set_var("compare_files_block_style", "");
	} elseif ($compare_files_type == 2) {
		$t->set_var("compare_files_type_2_checked", "checked");
	} elseif ($compare_files_type == 1) {
		$t->set_var("compare_files_type_1_checked", "checked");
	} else {
		$t->set_var("compare_files_type_0_checked", "checked");
	}
	// end diff files
	
	if($errors) { 
		$t->set_var("errors_list", $errors);
		$t->parse("errors", false);
	}

	include_once("./admin_header.php");
	include_once("./admin_footer.php");
	
	$t->pparse("main", false);
	
	
	function compare_folders($local, $remote, $level = 0, $prefix = "") {
		global $t;
		
		$local_files   = array();
		$local_folders = array();
		get_folders_and_files($local_folders, $local_files, $local, $level, $prefix);
		
		$remote_files   = array();
		$remote_folders = array();
		get_folders_and_files($remote_folders, $remote_files, $remote, $level, $prefix);
		
		if ($local_files && $remote_files) {						
			$files = array_diff($local_files, $remote_files);
			if ($files) {
				foreach ($files AS $file) {
					$t->set_var("file", $prefix . $file);
					$t->parse("file_only_local");
				}
				$t->parse("files_only_local");
				$t->set_var("file_only_local", "");
			}
			
			$files = array_diff($remote_files, $local_files);
			if ($files) {
				foreach ($files AS $file) {
					$t->set_var("file", $prefix . $file);
					$t->parse("file_only_remote");
				}
				$t->parse("files_only_remote");
				$t->set_var("file_only_remote", "");
			}
	
			$files = array_intersect($remote_files, $local_files);
			if ($files) {
				foreach ($files AS $file) {
					if (!compare_files($local . "/" . $file, $remote . "/" . $file)) {
						$t->set_var("local",  $local . "/" . $file);
						$t->set_var("remote", $remote . "/" . $file);
						$t->set_var("file", $prefix . $file);
						$t->parse("file_changed");
					}
				}
				$t->parse("files_changed");
				$t->set_var("file_changed", "");
			}
		}
		
		if ($local_folders && $remote_folders) {
			$folders = array_diff($local_folders, $remote_folders);
			if ($folders) {
				foreach ($folders AS $folder) {
					$t->set_var("folder", $prefix . $folder);
					$t->parse("folder_only_local");
				}
				$t->parse("folders_only_local");
				$t->set_var("folder_only_local", "");
			}
			
			$folders = array_diff($remote_folders, $local_folders);
			if ($folders) {
				foreach ($folders AS $folder) {
					$t->set_var("folder", $prefix . $folder);
					$t->parse("folder_only_remote");
				}
				$t->parse("folders_only_remote");
				$t->set_var("folder_only_remote", "");
			}
			$folders = array_intersect($remote_folders, $local_folders);
			if ($folders) {
				foreach ($folders AS $folder) {
					 compare_folders($local . "/" . $folder, $remote . "/" . $folder, $level + 1, $prefix . $folder . "/" );
				}
			}
		}
	}
	
	function compare_files($local, $remote) {
		$local_handle  = @fopen($local, "r");
		$remote_handle = @fopen($remote, "r");
		$ext = get_ext_file($local_handle);
		if ($local_handle && $remote_handle) {
			$local_buffer  = "";
			$remote_buffer = "";
			if ($ext == "php") {
			    while (!feof($local_handle)) {
			        $line = trim(fgets($local_handle, 4096));
			        if ($line && !(strpos("/*", $line) === 0 || (strpos("*", $line) === 0) || (strpos("//", $line) === 0) )
			        ) {
			        	$local_buffer .= $line;
			        };
			    }
			    while (!feof($remote_handle)) {
			        $line = trim(fgets($remote_handle, 4096));
			        if ($line && !(strpos("/*", $line) === 0 || (strpos("*", $line) === 0) || (strpos("//", $line) === 0) )
			        ) {
			        	$remote_buffer .= $line;
			        };
			    }
			} else {
				$local_buffer = fread($local_handle, filesize($local));
				$remote_buffer = fread($remote_handle, filesize($remote));
			}
		    $local_buffer = str_replace(array(" ", "\n", "\t"), "", $local_buffer);
		    $remote_buffer = str_replace(array(" ", "\n", "\t"), "", $remote_buffer);
		    fclose($local_handle);
			fclose($remote_handle);
			return $remote_buffer == $local_buffer;
		} else {
			return true;
		}		
	}	
	
	function get_folders_and_files(&$folders, &$files, $target_folder, $level = 0, $prefix = "") {
		global $core_folders, $core_skip, $core_ext, $core_files;
		
		$d = dir($target_folder);
		while (false !== ($entry = $d->read())) {
			if (in_array(strtolower($entry), $core_skip)) continue;
			if (is_dir($target_folder . "/" . $entry)) {
				if ($level == 0) {
					if (in_array($entry, $core_folders)) {
						$folders[] = $entry;
					}
				} else {
					$folders[] = $entry;
				}
			} elseif (is_file($target_folder . "/" . $entry)) {
				if ($core_ext) {
					$ext = get_ext_file($entry);
					if (in_array($ext, $core_ext)) {
						$files[] = $entry;
					}
				} else {
					if (in_array($prefix . $entry, $core_files)) {
						$files[] = $entry;
					}
				}
			}
		}
		if(is_resource($d)) {
			closedir($d);
		}
	}
	
	function folder_to_js($folder) {
		return $folder;
	}	
	function folder_from_js($folder) {
		return $folder;
	}
	
	function get_ext_file($file_name) {
		$parse_file = explode('.', $file_name);
		$parse_file_small = strtolower($parse_file[count($parse_file)-1]);
		return $parse_file_small;
	}
	
	function zipFileErrMsg($errno) {
		// using constant name as a string to make this function PHP4 compatible
		$zipFileFunctionsErrors = array(
			'ZIPARCHIVE::ER_MULTIDISK' => 'Multi-disk zip archives not supported.',
			'ZIPARCHIVE::ER_RENAME' => 'Renaming temporary file failed.',
			'ZIPARCHIVE::ER_CLOSE' => 'Closing zip archive failed',
			'ZIPARCHIVE::ER_SEEK' => 'Seek error',
			'ZIPARCHIVE::ER_READ' => 'Read error',
			'ZIPARCHIVE::ER_WRITE' => 'Write error',
			'ZIPARCHIVE::ER_CRC' => 'CRC error',
			'ZIPARCHIVE::ER_ZIPCLOSED' => 'Containing zip archive was closed',
			'ZIPARCHIVE::ER_NOENT' => 'No such file.',
			'ZIPARCHIVE::ER_EXISTS' => 'File already exists',
			'ZIPARCHIVE::ER_OPEN' => 'Can\'t open file',
			'ZIPARCHIVE::ER_TMPOPEN' => 'Failure to create temporary file.',
			'ZIPARCHIVE::ER_ZLIB' => 'Zlib error',
			'ZIPARCHIVE::ER_MEMORY' => 'Memory allocation failure',
			'ZIPARCHIVE::ER_CHANGED' => 'Entry has been changed',
			'ZIPARCHIVE::ER_COMPNOTSUPP' => 'Compression method not supported.',
			'ZIPARCHIVE::ER_EOF' => 'Premature EOF',
			'ZIPARCHIVE::ER_INVAL' => 'Invalid argument',
			'ZIPARCHIVE::ER_NOZIP' => 'Not a zip archive',
			'ZIPARCHIVE::ER_INTERNAL' => 'Internal error',
			'ZIPARCHIVE::ER_INCONS' => 'Zip archive inconsistent',
			'ZIPARCHIVE::ER_REMOVE' => 'Can\'t remove file',
			'ZIPARCHIVE::ER_DELETED' => 'Entry has been deleted',
		);
		$errmsg = 'unknown';
		foreach ($zipFileFunctionsErrors as $constName => $errorMessage) {
			if (defined($constName) and constant($constName) === $errno) {
				return 'Zip File Function error: '.$errorMessage;
			}
		}
		return 'Zip File Function error: unknown';
	}
?>