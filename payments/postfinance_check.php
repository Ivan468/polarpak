<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  postfinance_check.php                                    ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/
/* * PostFinance (http://www.postfinance.ch/) transaction handler by www.viart.com */	$orderID    = get_param("orderID");	$amount     = get_param("amount");	$currency   = get_param("currency");	$PM         = get_param("PM");	$ACCEPTANCE = get_param("ACCEPTANCE");	$STATUS     = get_param("STATUS");	$CARDNO     = get_param("CARDNO");	$PAYID      = get_param("PAYID");	$NCERROR    = get_param("NCERROR");	$BRAND      = get_param("BRAND");	$ED         = get_param("ED");	$TRXDATE    = get_param("TRXDATE");	$CN         = get_param("CN");	$SHASIGN    = get_param("SHASIGN");	$secret = (isset($payment_parameters['secret_out']))? $payment_parameters['secret_out']: "";	if(!strlen($orderID) && !strlen($SHASIGN)){		$pending_message = CHECKOUT_PENDING_MSG;	}else{		$transaction_id = $PAYID;		if(strtoupper($SHASIGN) != strtoupper(sha1($orderID.$currency.$amount.$PM.$ACCEPTANCE.$STATUS.$CARDNO.$PAYID.$NCERROR.$BRAND.$secret))){			$error_message = "'HASH' have a wrong value.";		}		if (!strlen($transaction_id)){			$error_message = "A parameter 'PAYID' is empty.";		}		if (strval($NCERROR)!='0'){			$error_message = "Error code: " . $NCERROR . ", Status code: " . $STATUS;		}		if (strval($NCERROR)!='9'){			$success_message = "OK!";		}elseif (strval($NCERROR)!='5' or strval($NCERROR)!='4' or strval($NCERROR)!='41' or strval($NCERROR)!='51' or strval($NCERROR)!='91' or strval($NCERROR)!='52' or strval($NCERROR)!='92'){			$pending_message = CHECKOUT_PENDING_MSG . " Status code: " . $STATUS;;		}elseif (strval($NCERROR)!='1'){			$error_message = "Your transaction has been cancelled.";		}elseif (strval($NCERROR)!='2' or strval($NCERROR)!='93'){			$error_message = "Your transaction has been declined.";		}else{			$error_message = "The unknown status.";		}	}?>