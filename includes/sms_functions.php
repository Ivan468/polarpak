<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  sms_functions.php                                        ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
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