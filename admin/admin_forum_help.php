<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_forum_help.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once("./admin_common.php");
	include_once($root_folder_path."messages/".$language_code."/forum_messages.php");

	check_admin_security("sales_orders");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_forum_help.html");
	$t->show_tags = true;

	$t->pparse("main");

?>