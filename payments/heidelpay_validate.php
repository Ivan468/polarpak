<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  heidelpay_validate.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * HeidelPay (http://www.heidelpay.de) transaction handler by ViArt Ltd. (www.viart.com).
 */

	$processing_result       = get_param("PROCESSING_RESULT");
	$identification_uniqueid = get_param("IDENTIFICATION_UNIQUEID");
	$processing_status_code  = get_param("PROCESSING_STATUS_CODE");
	$processing_reason       = get_param("PROCESSING_REASON");
	$processing_return       = get_param("PROCESSING_RETURN");

	if(strlen($processing_result)){
		$transaction_id = $identification_uniqueid;
		if($processing_result != "ACK"){
			$error_message = "Status Code: ".$processing_status_code.". ".$processing_reason.", ".$processing_return;
		}else{
			if(strval($processing_status_code) == strval("00")){
				$success_message = "Status Code: ".$processing_status_code.". ".$processing_reason.", ".$processing_return;
			}elseif(strval($processing_status_code) == strval("20") || strval($processing_status_code) == strval("50") || strval($processing_status_code) == strval("60") || strval($processing_status_code) == strval("70")){
				$error_message = "Status Code: ".$processing_status_code.". ".$processing_reason.", ".$processing_return;
			}else{
				$pending_message = "Status Code: ".$processing_status_code.". ".$processing_reason.", ".$processing_return;
			}
		}
	}else{
		$error_message = 'PROCESSING_RESULT is not found.';
	}
?>