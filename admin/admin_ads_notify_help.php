<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_ads_notify_help.php                                ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once("./admin_common.php");

	check_admin_security("ads");

	$type = get_param("type");
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_ads_notify_help.html");
	$t->show_tags = true;
	if ($type == "request") {
		$t->parse("request_block", false);
	} else {
		$t->set_var("request_block", "");
	}
	if ($type == "tell_friend") {
		$t->parse("tell_friend_block", false);
	} else {
		$t->set_var("tell_friend_block", "");
	}

	$t->pparse("main");

?>