<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  sms_functions.php                                        ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


function sms_send($recipient, $message, $originator, &$sms_errors)
{
	if (!$recipient || !$message) {
		return false;
	}
	
	/*
	 *	ADD CODE FOR SMS SENDING HERE
	 */

	// return true or SMS id in case of success
	return true; 
}

?>