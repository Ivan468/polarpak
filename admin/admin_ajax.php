<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_ajax.php                                           ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once("./admin_common.php");

	check_admin_security();
	$permissions = get_permissions();

	$operation = get_param("operation");
	$setting_name = get_param("setting_name");
	$setting_value = get_param("setting_value");

	if ($operation == "setting-add" || $operation == "setting-update") {
		update_admin_settings(array($setting_name => $setting_value));
	} else if ($operation == "setting-remove" || $operation == "setting-delete") {
		remove_admin_settings(array($setting_name));
	}

	return;