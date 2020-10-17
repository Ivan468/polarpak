<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  user_login.php                                           ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$cms_page_code = "user_login";
	$script_name   = "user_login.php";

	include_once("./includes/common.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/forum_messages.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/navigator.php");

	$current_page  = get_custom_friendly_url("user_login.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = LOGIN_TITLE;

	include_once("./includes/page_layout.php");

