<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  verisign_check.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * VeriSign Pro (www.verisign.com) handler by ViArt Ltd (http://www.viart.com/)
 */	
	
	$AUTHCODE = get_param('AUTHCODE', POST);
	$AVSDATA  = get_param('AVSDATA', POST);
	$PNREF    = get_param('PNREF', POST);
	$RESPMSG  = get_param('RESPMSG', POST);
	$RESULT   = get_param('RESULT', POST);
	
	
	$success_message = "";
	$error_message   = "";
	
	if ($RESULT==0) {
		if ($RESPMSG=='Approved') {
			$success_message = "Your order has been accepted.";		
		} elseif ($RESPMSG=='AVSDECLINED') {
			$errors_message = "Zip/Street name are not valid.";
		} elseif ($RESPMSG=='CSCDECLINED') {
			$errors_message = "Card Security Code is not valid.";		
		} else {
			$errors_message = "Unknown responce <br/> " . $RESPMSG;	
		}
	} elseif ($RESULT>0) {
		$errors_message = "Order is declined <br/> " . $RESPMSG;		
	} elseif ($RESULT<0) {
		$errors_message = "Communication error occurred <br/>" . $RESPMSG;
	}
?>