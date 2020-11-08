<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  preview.php                                              ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

	
	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/sorter.php");
	//session_start();
	
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","preview.html");
	
	$style_name=get_param("style_name");
	
	$t->set_var("css_path", "../styles/".$style_name.".css");
	
	$preview_html = $_SESSION['preview_html'];
	$t->set_var("preview_block", $preview_html);

	$t->pparse("main");
?>
	