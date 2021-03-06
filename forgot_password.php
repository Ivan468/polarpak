<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  forgot_password.php                                      ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/ads_functions.php");
	include_once("./includes/navigator.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");


	$cms_page_code = "forgot_password";
	$script_name   = "forgot_password.php";
	$current_page  = get_custom_friendly_url("forgot_password.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = FORGOT_PASSWORD_MSG;

	include_once("./includes/page_layout.php");

?>