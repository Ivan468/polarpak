<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  certitrade_check.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * CertiTrade (http://www.certitrade.net/) transaction handler by www.viart.com
 */

	$r_md5code       = get_param('md5code');
	$r_merchantid    = get_param('merchantid');
	$r_order_id      = get_param('orderid');
	$r_amount        = get_param('amount');
	$r_currency      = get_param('currency');
	$r_result        = get_param('result');
	$r_result_code   = strval(get_param('result_code'));
	$r_bank_code     = get_param('bank_code');
	$transaction_id  = get_param('trnumber');
	$r_authcode      = get_param('authcode');
	$r_lang          = get_param('lang');
	$r_ch_name       = get_param('ch_name');
	$r_ch_address1   = get_param('ch_address1');
	$r_ch_address2   = get_param('ch_address2');
	$r_ch_address3   = get_param('ch_address3');
	$r_ch_zip        = get_param('ch_zip');
	$r_ch_city       = get_param('ch_city');
	$r_ch_phone      = get_param('ch_phone');
	$r_ch_email      = get_param('ch_email');
	$r_cc_descr      = get_param('cc_descr');
	$r_transp1       = get_param('transp1');
	$r_transp2       = get_param('transp2');
	$va_status       = strtolower(get_param('va_status'));

	$md5str  = $payment_parameters['md5key'];
	$md5str .= $r_merchantid;
	$md5str .= $r_order_id;
	$md5str .= $r_amount;
	$md5str .= $r_currency;
	$md5str .= $r_result;
	$md5str .= $r_result_code;
	$md5str .= $r_bank_code;
	$md5str .= $transaction_id;
	$md5str .= $r_authcode;
	$md5str .= $r_lang;
	$md5str .= $r_ch_name;
	$md5str .= $r_ch_address1;
	$md5str .= $r_ch_address2;
	$md5str .= $r_ch_address3;
	$md5str .= $r_ch_zip;
	$md5str .= $r_ch_city;
	$md5str .= $r_ch_phone;
	$md5str .= $r_ch_email;

	$calculated_md5code = md5($md5str);

	if ($r_md5code == $calculated_md5code){
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET name=" . $db->tosql($r_ch_name, TEXT) ;
		$sql .= ", email=" . $db->tosql($r_ch_email, TEXT) ;
		$sql .= ", address1=" . $db->tosql($r_ch_address1, TEXT) ;
		$sql .= ", address2=" . $db->tosql($r_ch_address2, TEXT) ;
		$sql .= ", city=" . $db->tosql($r_ch_city, TEXT) ;
		$sql .= ", zip=" . $db->tosql($r_ch_zip, TEXT) ;
		$sql .= ", authorization_code=" . $db->tosql($r_authcode, TEXT) ;
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
		$db->query($sql);

		if($r_result_code == strval("00")){
			$response_message = 'Approved.';
		}elseif($r_result_code == strval("01")){
			$response_message = 'Declined by the bank.';
		}elseif($r_result_code == strval("02")){
			$response_message = 'No contact with the bank.';
		}elseif($r_result_code == strval("03")){
			$response_message = 'Other technical fault.';
		}elseif($r_result_code == strval("04")){
			$response_message = 'Cancelled by the buyer.';
 		}else{
			$response_message = "result_code: '".$r_result_code."'";
		}

		if($r_result == "OK" && $r_result_code == "00"){
		}else{
			$error_message  = "Your transaction has not been approved. " . $response_message;
		}
	}else{
		$error_message = "'MD5 Code' have a wrong value.";
	}
?>