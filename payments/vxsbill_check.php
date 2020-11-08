<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  vxsbill_check.php                                        ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * VXSBill (www.vxsbill.com) transaction handler by www.viart.com
 */

	$root_folder_path = (isset($is_admin_path) && $is_admin_path) ? "../" : "./";

	include_once ($root_folder_path ."payments/vxsbill_functions.php");
	vxsbill_payment_check();
	if(isset($is_admin_path) && $is_admin_path){
		if(strlen($pending_message) && !strlen($error_message)){
			$error_message = $pending_message;
		}
	}
?>