<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  subscriptions.php                                        ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/record.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/ads_functions.php");
	include_once("./includes/items_properties.php");
	include_once("./includes/navigator.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	$subscription_page = get_setting_value($settings, "subscription_page", 1);
	if ($subscription_page == 1) {
		// user need to be logged in before accessing subscriptions 
		check_user_session();
	}

	$cms_page_code = "subscriptions";
	$script_name   = "subscriptions.php";
	$current_page  = get_custom_friendly_url("subscriptions.php");
	$auto_meta_title = SUBSCRIPTIONS_MSG;
	$tax_rates = get_tax_rates();

	include_once("./includes/page_layout.php");

?>