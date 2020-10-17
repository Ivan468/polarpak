<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  ipay88_check.php                                         ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * iPay88 (http://www.ipay88.com/) transaction handler by www.viart.com
 */

	$merchantcode = get_param("MerchantCode", POST);
	$paymentid = get_param("PaymentId", POST);
	$refno = get_param("RefNo", POST);
	$amount = get_param("Amount", POST);
	$currency = get_param("Currency", POST);
	$remark = get_param("Remark", POST);
	$transaction_id = get_param("TransId", POST);
	$authcode = get_param("AuthCode", POST);
	$status = get_param("Status", POST);
	$errdesc = get_param("ErrDesc", POST);
	$signature = get_param("Signature", POST);

	if($refno != $order_id){
		$pending_message = CHECKOUT_PENDING_MSG;
	}else{
		if(strlen($authcode)){
			$sql  = " UPDATE " . $table_prefix . "orders ";
			$sql .= " SET authorization_code=" . $db->tosql($authcode, TEXT);
			$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
			$db->query($sql);
		}
		if($status == '1'){
			$source = $payment_parameters['MerchantKey'].$merchantcode.$paymentid.$refno.round($amount*100, 0).$currency.$status;
			$hexsource = sha1($source);
			$bin = '';
			for ($i=0;$i<strlen($hexsource);$i=$i+2){
				$bin .= chr(hexdec(substr($hexsource,$i,2)));
			}
			$our_signature = base64_encode($bin);
			if($signature != $our_signature){
				$error_message = "'Signature' have a wrong value.";
			}

		}else{
			if(strlen($errdesc)){
				$error_message = $errdesc;
			}else{
				$error_message = "Your transaction has been declined.";
			}
		}	
	}
?>