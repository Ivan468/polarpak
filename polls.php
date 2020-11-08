<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  polls.php                                                ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/sorter.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/ads_functions.php");
	include_once("./includes/navigator.php");

	$cms_page_code = "polls";
	$script_name   = "polls.php";
	$current_page  = get_custom_friendly_url("polls.php");
	$tax_rates     = get_tax_rates();
	$auto_meta_title = PREVIOUS_POLLS_MSG;

	include_once("./includes/page_layout.php");

?>