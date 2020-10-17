<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_upgrade_diff_file.php                              ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ($root_folder_path . "messages/" . $language_code . "/install_messages.php");
	include_once ($root_folder_path . "includes/class_diff.php");
	include_once("./admin_common.php");
	
	check_admin_security("system_upgrade");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_upgrade_diff_file.html");
	
	$local  = get_param("local");
	$remote = get_param("remote");
	
	$errors = "";
	if (!$local || !$remote) {
		$errors .= "Please specify files to merge<br/>";
	} else {
		$t->set_var("local", $local);
		$t->set_var("remote", $remote);
		
		$local_handle  = @fopen($local, "r");
		$remote_handle = @fopen($remote, "r");
		if ($local_handle && $remote_handle) {
			
			$local_lines  = array();
			$remote_lines = array();
			$local_lines_sq  = array();
			$remote_lines_sq = array();
			
			while (!feof($local_handle)) {
				$line = fgets($local_handle, 4096);
				$local_lines[] = $line;
				$local_lines_sq[] = str_replace(array("\n", "\t", " ", "\r"), "", $line);
			}			
			while (!feof($remote_handle)) {
				$line = fgets($remote_handle, 4096);
				$remote_lines[] = $line;
				$remote_lines_sq[] = str_replace(array("\n", "\t", " ", "\r"), "", $line);
			}
						
		    fclose($local_handle);
			fclose($remote_handle);
			
			$local_index = 0;
			$local_count = count($local_lines);
			$remote_index = 0;
			$remote_count = count($remote_lines);
			
			$i = 0;
			while ($local_index < $local_count && $remote_index < $remote_count && $i<5000) {
				$local_line_sq  = $local_lines_sq[$local_index];
				$remote_line_sq = $remote_lines_sq[$remote_index];
				
				$remote_in_local = array_search($remote_line_sq, $local_lines_sq);
				$local_in_remote = array_search($local_line_sq, $remote_lines_sq);
				
				if ($local_line_sq == $remote_line_sq 
					|| (
						!($remote_line_sq && $remote_in_local && $remote_in_local>$local_index) 
						&& !($local_line_sq && $local_in_remote && $local_in_remote>$remote_index)
					)) {
					$local_line  = htmlentities($local_lines[$local_index]) . "&nbsp";
					$remote_line = htmlentities($remote_lines[$remote_index]) . "&nbsp";
					if (!($local_line_sq == $remote_line_sq)) {
						$t->set_var("style",  "style='color: red'");
					} else {
						$t->set_var("style",  "");
					}
					$t->set_var("local_line",  $local_line);
					$t->set_var("remote_line", $remote_line);
					$t->set_var("local_index",  $local_index);
					$t->set_var("remote_index", $remote_index);
					$t->parse("line_block");
					
					$local_index++;
					$remote_index++;
				} elseif ($remote_line_sq && $remote_in_local && $remote_in_local>$local_index) {
					while ($local_index < $remote_in_local) {
						$local_line  = htmlentities($local_lines[$local_index]) . "&nbsp";
						$t->set_var("style",  "style='color: green'");					
						$t->set_var("local_line",  $local_line);
						$t->set_var("remote_line", "&nbsp;");
						$t->set_var("local_index",  $local_index);
						$t->set_var("remote_index", "");
						$t->parse("line_block");				
						$local_index++;
					}
				
				} elseif ($local_line_sq && $local_in_remote && $local_in_remote>$remote_index) {
					while ($remote_index < $local_in_remote) {
						$remote_line  = htmlentities($remote_lines[$remote_index]) . "&nbsp";
						$t->set_var("style",  "style='color: green'");					
						$t->set_var("local_line",  "&nbsp;");
						$t->set_var("remote_line", $remote_line);
						$t->set_var("local_index",  "");
						$t->set_var("remote_index", $remote_index);
						$t->parse("line_block");				
						$remote_index++;
					}
				} else {
					$local_index++;
				}
				
				$i++;
			}
		} else {
			$errors .= "Couldn`t open files<br/>";
		}
		
		$t->parse("results");
	}
	
	if($errors) { 
		$t->set_var("errors_list", $errors);
		$t->parse("errors", false);
	}
	
	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main", false);
?>