<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  e-consel_validate.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * Consel (http://reserved.e-consel.it/) transaction handler by www.viart.com
 */

	$va_status_return = get_param("va_status_return");
	if(strtolower($va_status_return) == "success"){
		$transaction_id = "OR";
	}elseif(strtolower($va_status_return) == "decline"){
		$error_message = "Your transaction has been declined.";
	}else{
		$pending_message = "This order will be reviewed manually.";
	}

?>
