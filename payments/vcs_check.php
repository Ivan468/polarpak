<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  vcs_check.php                                            ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * VCS (https://www.vcs.co.za) transaction handler by http://www.viart.com/
 */

	$p1 = get_param("p1");
	$p2 = get_param("p2");
	$p3 = get_param("p3");
	$p4 = get_param("p4");
	$p5 = get_param("p5");
	$p6 = get_param("p6");
	$p7 = get_param("p7");
	$p8 = get_param("p8");
	$p9 = get_param("p9");
	$p10 = get_param("p10");
	$pam = get_param("pam");

	if(!strlen($p1) && !strlen($p2)){
		$pending_message = "There is no answer from payment gateway. This order will be reviewed manually.";
		return;
	}

	$error_message = '';
	$pOrder_ID = $payment_parameters['p2'];
	$transaction_id = $p2;
	if (strlen($p2) && $p2==$pOrder_ID){
		if ($payment_parameters['pam']!=$pam) {
			$error_message .= "Failed. PAM incorrect!";
		}
		if ($p6!=$order_total) {
			$error_message .= "Failed. Amount incorrect!";
		}
		if(preg_match('/APPROVED/Uis', $p3, $value)){
		}else{
			$error_message .= $p3;
		}
	} else {
		$error_message = "Failed. Order not found";
	}

?>