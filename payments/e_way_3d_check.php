<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  e_way_3d_check.php                                       ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * eWay (www.eway.com.au) transaction handler by http://www.viart.com/
 */

	$error_message = '';
	$ewayTrxnStatus    = get_param("ewayTrxnStatus");
	$ewayTrxnNumber = get_param("ewayTrxnNumber");
	$eWAYresponseCode = get_param("eWAYresponseCode");
	$eWAYresponseText = get_param("eWAYresponseText");
	$transaction_id = get_param("ewayTrxnReference");
	$eWAYoption1 = get_param("eWAYoption1");
	$eWAYoption2 = get_param("eWAYoption2");
	$eWAYoption3 = get_param("eWAYoption3");
	$variables["authorization_code"] = get_param("ewayAuthCode");
	$eWAYReturnAmount = get_param("eWAYReturnAmount");

	if(!strlen($ewayTrxnStatus) && !strlen($transaction_id) && !strlen($eWAYresponseCode) && !strlen($eWAYresponseText)){
		$pending_message = CHECKOUT_PENDING_MSG;
	}else{
		if(strtolower($ewayTrxnStatus) != 'true'){
			$error_message  = (strlen($eWAYresponseCode))? 'Error Code: '.$eWAYresponseCode.' ': '';
			$error_message .= (strlen($eWAYresponseText))? $eWAYresponseText: '';
			$error_message  = (strlen($error_message))? $error_message : "Your transaction has been declined.";
		}
	}

?>
