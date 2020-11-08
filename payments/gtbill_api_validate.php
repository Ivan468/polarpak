<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  gtbill_api_validate.php                                  ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/
/* * GTBill QuickPay API (http://www.gtbill.com/) transaction handler by www.viart.com */	$transaction_id			= get_param("TransactionID");	$merchantreference		= get_param("MerchantReference");	if(!strlen($transaction_id) || $merchantreference != $order_id){		$pending_message = "This order will be reviewed manually.";	}?>