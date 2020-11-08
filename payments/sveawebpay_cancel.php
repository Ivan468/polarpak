<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  sveawebpay_cancel.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * SveaWebPay (http://www.sveawebpay.se/) transaction handler by www.viart.com
 */

	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");

	if ($settings["secure_url"]) {
		$retirn_url = $settings["secure_url"];
	} else {
		$retirn_url = $settings["site_url"];
	}

	header("Location: " . $retirn_url."order_final.php?return_action=cancel");
	exit;
?>