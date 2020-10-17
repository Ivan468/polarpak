<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  hsbc_cpi_check.php                                       ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * The Cardholder Payment Interface (CPI) within HSBC Secure ePayments (http://www.hsbc.com/) 
 * transaction handler by www.viart.com
 */
	$root_folder_path = "./";
	include_once($root_folder_path . "payments/hsbc_cpi_functions.php");

	checkOrder();
?>