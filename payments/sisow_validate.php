<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  sisow_validate.php                                       ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Stripe Payment Gateway handler by http://www.viart.com/
 */

	include_once ("./payments/sisow_functions.php");

	$trxid = get_param("trxid");
	$status = get_param("status");

	// check parameters
	if (strtolower($status) == "expired") {
		$error_message = "Your session has been expired.";
	} else if (strtolower($status) == "cancelled") {
		$error_message = "Your transaction has been cancelled.";
	} else if (strtolower($status) == "failure") {
		$error_message = "Your transaction has been failed.";
	} else if (!strlen($trxid)) {
		$error_message = "Can't obtain transaction parameter.";
	}
	if (strlen($error_message)) {
		return;
	}

	$merchant_id = get_setting_value($payment_parameters, "merchant_id", "");
	$merchant_key = get_setting_value($payment_parameters, "merchant_key", "");
	$shop_id = get_setting_value($payment_parameters, "shop_id", "");
	$entrance_code = get_setting_value($payment_parameters, "entrance_code", "");
	$purchase_id = get_setting_value($payment_parameters, "purchase_id", "");
	$description = get_setting_value($payment_parameters, "description", "");
	$amount = get_setting_value($payment_parameters, "amount", "");
	$testmode_param = trim(strtolower(get_setting_value($payment_parameters, "test_mode")));
	$test_param = trim(strtolower(get_setting_value($payment_parameters, "test", $testmode_param)));
	$test_mode = ($test_param == "true" || $test_param == "1" || $test_param == "yes") ? true : false;

	$sisow = new Sisow($merchant_id, $merchant_key, $shop_id);
	if ($entrance_code) {
		$sisow->entranceCode = $entrance_code;
	}

	$status_code = $sisow->StatusRequest($trxid);
	if ($sisow->status == Sisow::statusSuccess) {
		$transaction_id = $trxid;
		$transaction_ts = $sisow->timeStamp;
		$paid_amount = $sisow->amount;
		$consumerAccount = $sisow->consumerAccount;
		$consumerName = $sisow->consumerName;
		$consumerCity = $sisow->consumerCity;
		$purchaseId = $sisow->purchaseId;
		$description = $sisow->description;
		$entranceCode = $sisow->entranceCode;
	} else {
		if ($sisow->errorMessage) {
			$error_message = $sisow->errorMessage;
		} else if ($sisow->errorCode) {
			$error_message = "Sisow error code: ".$sisow->errorCode;
		} else if ($status_code) {
			$error_message = "Sisow request code: ".$sisow->errorCode;
		} else {
			$error_message = "Sisow status: ".$sisow->status;
		}
	}


?>