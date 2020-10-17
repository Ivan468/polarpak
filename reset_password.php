<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  reset_password.php                                       ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$cms_page_code = "reset_password";
	$script_name   = "reset_password.php";

	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/ads_functions.php");
	include_once("./includes/navigator.php");

	$current_page  = get_custom_friendly_url("reset_password.php");
	$tax_rates     = get_tax_rates();
	$auto_meta_title = CHANGE_PASSWORD_MSG;

	include_once("./includes/page_layout.php");

