<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  securetrading_redirect.php                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/**
 * SecureTrading STPP Shopping Carts
 * Viart 4.1
 * Module Version 2.5.7
 * Last Updated 01 August 2013
 * Written by Peter Barrow for SecureTrading Ltd.
 * http://www.securetrading.com
 */

?><?php

$is_admin_path = true;
$root_folder_path = "../";
include_once ($root_folder_path ."includes/common.php");
include_once ($root_folder_path ."includes/record.php");
include_once ($root_folder_path ."includes/order_links.php");
include_once ($root_folder_path ."includes/order_items.php");
include_once ($root_folder_path ."includes/parameters.php");
include_once ($root_folder_path ."includes/common_functions.php");
include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");

if ($settings["secure_url"]) {
	$retirn_url = $settings["secure_url"];
}
else {
	$retirn_url = $settings["site_url"];
}

$t = new VA_Template('.'.$settings["templates_dir"]);
$t->set_file("main","payment.html");
$goto_payment_message = str_replace("{payment_system}", $retirn_url, GOTO_PAYMENT_MSG);
$goto_payment_message = str_replace("{button_name}", CONTINUE_BUTTON, $goto_payment_message);
$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);
$t->set_var("payment_url",$retirn_url."order_final.php");
$t->set_var("submit_method", "post");
$t->sparse("submit_payment", false);
$t->pparse("main");

?>