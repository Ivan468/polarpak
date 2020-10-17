<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  email_open.php                                           ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/



	include_once("./includes/var_definition.php");
	include_once("./includes/constants.php");
	include_once("./includes/common_functions.php");
	include_once("./includes/va_functions.php");
	include_once("./includes/db_query.php");
	include_once("./includes/db_$db_lib.php"); // DB Init
	$db = new VA_SQL($db_host, $db_user, $db_password, $db_name, $db_port, $db_persistent, $db_type); 

	$language_code = get_language("messages.php");
	include_once("./messages/" . $language_code . "/messages.php");
	include_once("./includes/date_functions.php");

	// Database Initialize
	$db = new VA_SQL();
	$db->DBType      = $db_type;
	$db->DBDatabase  = $db_name;
	$db->DBHost      = $db_host;
	$db->DBPort      = $db_port;
	$db->DBUser      = $db_user;
	$db->DBPassword  = $db_password;
	$db->DBPersistent= $db_persistent;

	$eid = get_param("eid");
  if (strlen($eid) && is_numeric($eid)) {
	  $sql  = " UPDATE ".$table_prefix."newsletters_emails ";
		$sql .= " SET is_opened=1 ";
		$sql .= " WHERE email_id=" . $db->tosql($eid, INTEGER);
  	$db->query($sql);
  }

	// create a small transparent image 1x1 for output
	$img = imagecreatetruecolor(1, 1);
	$white = imagecolorallocate($img, 255, 255, 255);
	imagecolortransparent($img, $white);
	imagesetpixel ($img, 0, 0, $white);

	// output image with appropriate headers
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");
	header("Content-type: image/png");
	imagepng($img);    	
	imagedestroy($img);

?>