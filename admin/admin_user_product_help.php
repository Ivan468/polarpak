<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_user_product_help.php                              ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ($root_folder_path . "includes/record.php");
	include_once ($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once ($root_folder_path . "messages/" . $language_code . "/download_messages.php");
	include_once("./admin_common.php");

	check_admin_security("users_groups");

	$type = get_param("type");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_user_product_help.html");
	$t->show_tags = true;

	$t->pparse("main");

?>