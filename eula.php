<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  eula.php                                                 ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	session_start();

	@ini_set("magic_quotes_runtime", 0);
	include_once("./includes/constants.php");
	include_once("./includes/common_functions.php");
	include_once("./includes/va_functions.php");
	$language_code = get_language("messages.php");
	include_once("./messages/".$language_code."/messages.php");
	include_once("./messages/".$language_code."/install_messages.php");
	include_once("./includes/template.php");

	$t = new VA_Template("./templates/user/");
	$t->set_file("main", "eula.html");
	$t->set_var("css_file", "styles/installation.css");
	$t->set_var("meta_language", $language_code);

	if($t->block_exists("eula_".$language_code)) {
		$t->parse("eula_".$language_code, false);
	} else {
		$t->parse("eula_en", false);
	}

	$t->pparse("main");

?>