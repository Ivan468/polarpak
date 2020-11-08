<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_saved_cart_notify_help.php                         ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once("./admin_common.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");

	check_admin_security("products_categories");

	$type = get_param("type");
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_saved_cart_notify_help.html");
	$t->show_tags = true;

	$t->pparse("main");

?>