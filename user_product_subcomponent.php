<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  user_product_subcomponent.php                            ***
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
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	check_user_security("access_products");

	$cms_page_code = "user_product_subcomponent";
	$script_name   = "user_product_subcomponent.php";
	$current_page  = get_custom_friendly_url("user_product_subcomponent.php");
	$tax_rates = get_tax_rates();
	$auto_meta_title = OPTIONS_AND_COMPONENTS_MSG.": ".EDIT_SUBCOMP_MSG;

	include_once("./includes/page_layout.php");

?>