<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  cellpay_validate.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/
/* * Cellpay (www.cellpay.co.in) transaction handler by www.viart.com */	$mtranid        = get_param("MerchantTranID", POST);	$transaction_id = get_param("TranID", POST);	$amount         = get_param("Amt", POST);	$status         = get_param("Status", POST);	$message        = get_param("Msg", POST);	$refId1         = get_param("RefId1", POST);	$refId2         = get_param("RefId2", POST);	$refId3         = get_param("RefId3", POST);	$refId4         = get_param("RefId4", POST);	$refId5         = get_param("RefId5", POST);	$refId6         = get_param("RefId6", POST);	$refId7         = get_param("RefId7", POST);	$refId8         = get_param("RefId8", POST);	$refId9         = get_param("RefId9", POST);	$refId10        = get_param("RefId10", POST);	// check parameters	if($mtranid != $order_id){		$pending_message = CHECKOUT_PENDING_MSG;	} elseif (strtoupper($status) != "SUCCESS") {		$error_message .= "Your payment status is " . $status;		$error_message .= (strlen($message))? " ".$message: "";	}?>