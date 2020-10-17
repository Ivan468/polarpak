<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  e_way_hp_process.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * eWay (www.eway.co.uk) transaction handler by http://www.viart.com/
 */

 	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");

	$vc = get_session("session_vc");
	$order_id = get_session("session_order_id");

	$order_errors = check_order($order_id, $vc);
	if($order_errors) {
		echo $order_errors;
		exit;
	}
	
	$payment_parameters = array();
	$pass_parameters = array();
	$post_parameters = '';
	$pass_data = array();
	$variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);

	$request  = $payment_parameters['request_url'];
	$request .= '?CustomerID='.str_replace(' ', '+', htmlspecialchars($payment_parameters['CustomerID']));
	$request .= '&UserName='.str_replace(' ', '+', htmlspecialchars($payment_parameters['UserName']));
	$request .= '&Amount='.str_replace(' ', '+', htmlspecialchars($payment_parameters['Amount']));
	$request .= '&Currency='.str_replace(' ', '+', htmlspecialchars($payment_parameters['Currency']));
	$request .= '&ReturnURL='.str_replace(' ', '+', htmlspecialchars($payment_parameters['ReturnURL']));
	$request .= '&CancelURL='.str_replace(' ', '+', htmlspecialchars($payment_parameters['CancelURL']));

	$request .= (isset($payment_parameters['PageTitle']))?'&PageTitle='.str_replace(' ', '+', htmlspecialchars($payment_parameters['PageTitle'])):'';
	$request .= (isset($payment_parameters['PageDescription']))?'&PageDescription='.str_replace(' ', '+', htmlspecialchars($payment_parameters['PageDescription'])):'';
	$request .= (isset($payment_parameters['PageFooter']))?'&PageFooter='.str_replace(' ', '+', htmlspecialchars($payment_parameters['PageFooter'])):'';

	$request .= (isset($payment_parameters['CompanyLogo']))?'&CompanyLogo='.str_replace(' ', '+', htmlspecialchars($payment_parameters['CompanyLogo'])):'';
	$request .= (isset($payment_parameters['Pagebanner']))?'&Pagebanner='.str_replace(' ', '+', htmlspecialchars($payment_parameters['Pagebanner'])):'';
	$request .= (isset($payment_parameters['ModifiableCustomerDetails']))?'&ModifiableCustomerDetails='.str_replace(' ', '+', htmlspecialchars($payment_parameters['ModifiableCustomerDetails'])):'';

	$request .= (isset($payment_parameters['Language']))?'&Language='.str_replace(' ', '+', htmlspecialchars($payment_parameters['Language'])):'';
	$request .= (isset($payment_parameters['CompanyName']))?'&CompanyName='.str_replace(' ', '+', htmlspecialchars($payment_parameters['CompanyName'])):'';
	$request .= (isset($payment_parameters['CustomerFirstName']))?'&CustomerFirstName='.str_replace(' ', '+', htmlspecialchars($payment_parameters['CustomerFirstName'])):'';
	$request .= (isset($payment_parameters['CustomerLastName']))?'&CustomerLastName='.str_replace(' ', '+', htmlspecialchars($payment_parameters['CustomerLastName'])):'';
	$request .= (isset($payment_parameters['CustomerAddress']))?'&CustomerAddress='.str_replace(' ', '+', htmlspecialchars($payment_parameters['CustomerAddress'])):'';
	$request .= (isset($payment_parameters['CustomerCity']))?'&CustomerCity='.str_replace(' ', '+', htmlspecialchars($payment_parameters['CustomerCity'])):'';
	$request .= (isset($payment_parameters['CustomerState']))?'&CustomerState='.str_replace(' ', '+', htmlspecialchars($payment_parameters['CustomerState'])):'';
	$request .= (isset($payment_parameters['CustomerPostCode']))?'&CustomerPostCode='.str_replace(' ', '+', htmlspecialchars($payment_parameters['CustomerPostCode'])):'';
	$request .= (isset($payment_parameters['CustomerCountry']))?'&CustomerCountry='.str_replace(' ', '+', htmlspecialchars($payment_parameters['CustomerCountry'])):'';
	$request .= (isset($payment_parameters['CustomerPhone']))?'&CustomerPhone='.str_replace(' ', '+', htmlspecialchars($payment_parameters['CustomerPhone'])):'';
	$request .= (isset($payment_parameters['CustomerEmail']))?'&CustomerEmail='.str_replace(' ', '+', htmlspecialchars($payment_parameters['CustomerEmail'])):'';
	$request .= (isset($payment_parameters['InvoiceDescription']))?'&InvoiceDescription='.str_replace(' ', '+', htmlspecialchars($payment_parameters['InvoiceDescription'])):'';
	$request .= (isset($payment_parameters['MerchantReference']))?'&MerchantReference='.str_replace(' ', '+', htmlspecialchars($payment_parameters['MerchantReference'])):'';
	$request .= (isset($payment_parameters['MerchantInvoice']))?'&MerchantInvoice='.str_replace(' ', '+', htmlspecialchars($payment_parameters['MerchantInvoice'])):'';
	$request .= (isset($payment_parameters['MerchantOption1']))?'&MerchantOption1='.str_replace(' ', '+', htmlspecialchars($payment_parameters['MerchantOption1'])):'';
	$request .= (isset($payment_parameters['MerchantOption2']))?'&MerchantOption2='.str_replace(' ', '+', htmlspecialchars($payment_parameters['MerchantOption2'])):'';
	$request .= (isset($payment_parameters['MerchantOption3']))?'&MerchantOption3='.str_replace(' ', '+', htmlspecialchars($payment_parameters['MerchantOption3'])):'';
	$request .= (isset($payment_parameters['UseAVS']))?'&UseAVS='.str_replace(' ', '+', htmlspecialchars($payment_parameters['UseAVS'])):'';
	$request .= (isset($payment_parameters['UseZIP']))?'&UseZIP='.str_replace(' ', '+', htmlspecialchars($payment_parameters['UseZIP'])):'';

	$error_message = "";

	$ch = curl_init();
	if ($ch){
		
		curl_setopt ($ch, CURLOPT_URL, $request);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($ch, CURLOPT_TIMEOUT,30);
		set_curl_options ($ch, $payment_parameters);
			
		$payment_response = curl_exec ($ch);
		if (curl_errno($ch)){
			$error_message .= curl_errno($ch)." - ".curl_error($ch);
			return;
		}
		curl_close($ch);
		
		if(strlen($payment_response)){
			$matches = array();
			if (preg_match('/\<TransactionRequest\>(.*)\<\/TransactionRequest\>/Uis', $payment_response, $matches)){
				$Result = "";
				$URI = "";
				$Error = "";
				$matches = array();
				if (preg_match('/\<Result\>(.*)\<\/Result\>/Uis', $payment_response, $matches)){
					$Result = $matches[1];
				}
				$matches = array();
				if (preg_match('/\<URI\>(.*)\<\/URI\>/Uis', $payment_response, $matches)){
					$URI = $matches[1];
				}
				$matches = array();
				if (preg_match('/\<Error\>(.*)\<\/Error\>/Uis', $payment_response, $matches)){
					$Error = $matches[1];
				}
				if((strtoupper($Result) != 'TRUE') or strlen($Error) or !strlen($URI)){
					$error_message .= (strlen($Error))? $Error: "Invalid request from eWay.";
				}else{
					header('Location: '.$URI);
					exit;
				}
			}else{
				$error_message  = "Can't obtain transaction request from eWay.";
			}
		}else{
			$error_message .= "Can't obtain data for your transaction.";
		}
	}else{
		$error_message .= "Can't initialize cURL.";
	}

	exit($error_message);
?>