<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  user_product_registrations.php                           ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./includes/editgrid.php");
	include_once("./includes/navigator.php");
	include_once("./includes/sorter.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	
	check_user_security("access_product_registration");

	$cms_page_code = "user_product_registrations";
	$script_name   = "user_product_registrations.php";
	$current_page  = get_custom_friendly_url("user_product_registrations.php");
	$auto_meta_title = MY_PRODUCT_REGISTRATIONS_MSG;
	
	include_once("./includes/page_layout.php");

?>