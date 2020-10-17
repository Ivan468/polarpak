<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  winbank_validate.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/
/* * WINBANK (www.winbank.gr) transaction handler by www.viart.com */	$transaction_id			= get_param("transactionid");	$merchantreference		= get_param("merchantreference");	$responsecode			= get_param("responsecode");	$responsedescription	= get_param("responsedescription");	$retrievalref			= get_param("retrievalref");	$approvalcode			= get_param("approvalcode");	$errorcode				= get_param("errorcode");	$errordescription		= get_param("errordescription");	$amount					= get_param("amount");	$installments			= get_param("Installments");	$cardtype				= get_param("cardtype");	$langid					= get_param("langid");	$parameters				= get_param("parameters");	if(strlen($transaction_id) || strlen($responsecode) || strlen($errorcode)){		$error_message = check_payment($merchantreference, $amount/100);		if(strlen($error_message)){			return;		}		if(strval($responsecode) == strval('00') || strval($responsecode) == strval('08') || strval($responsecode) == strval('10') || strval($responsecode) == strval('11') || strval($responsecode) == strval('16')){			if(!strlen($transaction_id)){				$pending_message = "Can't obtain transaction id. This order will be reviewed manually.";			}			$success_message = 'Response Code:'.$responsecode;			$success_message .= (strlen($responsedescription))? ' '.$responsedescription: '';			$success_message .= (strlen($errordescription))? ' '.$errordescription: '';		}else{			if(strlen($errorcode) || strlen($errordescription)){				$error_message = 'Error code:'.$errorcode;				$error_message .= (strlen($errordescription))? ' '.$errordescription: '';				$error_message .= (strlen($responsecode))? ' Response Code:'.$responsecode: '';				$error_message .= (strlen($responsedescription))? ' '.$responsedescription: '';			}else{				$error_message = CHECKOUT_ERROR_MSG;			}		}	}else{		$pending_message = CHECKOUT_PENDING_MSG;	}?>