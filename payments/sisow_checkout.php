<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  sisow_checkout.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Sisow Payment Gateway handler by http://www.viart.com/
 */

	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/record.php");
	include_once ($root_folder_path ."includes/order_links.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");
	include_once ($root_folder_path ."payments/sisow_functions.php");

	$vc = get_session("session_vc");
	$order_id = get_session("session_order_id");

	$order_errors = check_order($order_id, $vc);
	if($order_errors) {
		echo $order_errors;
		exit;
	}
	
	$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables, "", "no");

	$merchant_id = get_setting_value($payment_parameters, "merchant_id", "");
	$merchant_key = get_setting_value($payment_parameters, "merchant_key", "");
	$shop_id = get_setting_value($payment_parameters, "shop_id", "");
	$entrance_code = get_setting_value($payment_parameters, "entrance_code", "");
	$purchase_id = get_setting_value($payment_parameters, "purchase_id", "");
	$description = get_setting_value($payment_parameters, "description", "");
	$amount = get_setting_value($payment_parameters, "amount", "");
	$notify_url = get_setting_value($payment_parameters, "notify_url", "");
	$callback_url = get_setting_value($payment_parameters, "callback_url", "");
	$return_url = get_setting_value($payment_parameters, "return_url", "");
	$cancel_url = get_setting_value($payment_parameters, "cancel_url", "");
	$currency_param = get_setting_value($payment_parameters, "currency", "");

	$testmode_param = trim(strtolower(get_setting_value($payment_parameters, "test_mode")));
	$test_param = trim(strtolower(get_setting_value($payment_parameters, "test", $testmode_param)));
	$test_mode = ($test_param == "true" || $test_param == "1" || $test_param == "yes") ? true : false;

	// build payment options
	$payment_options = "";
	$payment_ideal = trim(strtolower(get_setting_value($payment_parameters, "payment_ideal", "")));
	$payment_sofort = trim(strtolower(get_setting_value($payment_parameters, "payment_sofort", "")));
	$payment_mistercash = trim(strtolower(get_setting_value($payment_parameters, "payment_mistercash", "")));
	$payment_webshop = trim(strtolower(get_setting_value($payment_parameters, "payment_webshop", "")));
	$payment_podium = trim(strtolower(get_setting_value($payment_parameters, "payment_podium", "")));
	if ($payment_ideal == "1" || $payment_ideal == "yes" || $payment_ideal == "true") {
		$payment_options .= '<option value="">iDEAL</option>';
	}
	if ($payment_sofort == "1" || $payment_sofort == "yes" || $payment_sofort == "true") {
		$payment_options .= '<option value="sofort">DIRECTebanking</option>';
	}
	if ($payment_mistercash == "1" || $payment_mistercash == "yes" || $payment_mistercash == "true") {
		$payment_options .= '<option value="mistercash">MisterCash</option>';
	}
	if ($payment_webshop == "1" || $payment_webshop == "yes" || $payment_webshop == "true") {
		$payment_options .= '<option value="webshop">WebShop GiftCard</option>';
	}
	if ($payment_podium == "1" || $payment_podium == "yes" || $payment_podium == "true") {
		$payment_options .= '<option value="podium">Podium Cadeaukaart</option>';
	}

	// check issuer id
	$issuerid_param = get_param("issuerid");
	$issuerid = trim(get_setting_value($payment_parameters, "issuer_id", $issuerid_param));
	if (!$issuerid) { $issuerid = trim(get_setting_value($payment_parameters, "issuerid")); }

	// check issuer id from bank name if it was selected
	$bank = trim(get_setting_value($payment_parameters, "bank", ""));
	if ($bank && !$issuerid) {
		if (preg_match("/ABN\s*Amro\s*Bank/i", $bank)) {
			$issuerid = "01";
		} else if (preg_match("/ASN\s*Bank/i", $bank)) {
			$issuerid = "02";
		} else if (preg_match("/Friesland\s*Bank/i", $bank)) {
			$issuerid = "04";
		} else if (preg_match("/ING/i", $bank)) {
			$issuerid = "05";
		} else if (preg_match("/Rabobank/i", $bank)) {
			$issuerid = "06";
		} else if (preg_match("/SNS\s*Bank/i", $bank)) {
			$issuerid = "07";
		} else if (preg_match("/RegioBank/i", $bank)) {
			$issuerid = "08";
		} else if (preg_match("/Triodos\s*Bank/i", $bank)) {
			$issuerid = "09";
		} else if (preg_match("/Van\s*Lanschot\s*Bankiers/i", $bank)) {
			$issuerid = "10";
		}
	}
	// if test mode set test bank account
	if ($test_mode) { $issuerid = "99"; }
	

	// check payment if issuer wasn't selected
	$payment_param = get_param("payment");
	$payment = trim(get_setting_value($payment_parameters, "payment", $payment_param));

	$sisow = new Sisow($merchant_id, $merchant_key, $shop_id);
	if ($entrance_code) {
		$sisow->entranceCode = $entrance_code;
	}
	if ($issuerid || $payment) {
		$sisow->purchaseId = $purchase_id;
		$sisow->description = $description;
		$sisow->amount = $amount;
		$sisow->payment = $payment;
		$sisow->issuerId = $issuerid;
		$sisow->notifyUrl= $notify_url;
		$sisow->returnUrl = $return_url;
		$sisow->cancelUrl = $cancel_url;
		$sisow->callbackUrl = $callback_url;
		if (($ex = $sisow->TransactionRequest()) < 0) {
			header("Location: payment.php?ex=" . $ex . "&ec=" . $sisow->errorCode . "&em=" . $sisow->errorMessage);
			exit;
		}
		header("Location: " . $sisow->issuerUrl);
		exit;
	} else {
		$sisow->DirectoryRequest($issuers_select, true, $test_mode);
	}
/*
else if (isset($_GET["trxid"])) {
	$sisow->StatusRequest($_GET["trxid"]);
	// if ($sisow->status == Sisow::statusSuccess) {
	//     echo $sisow->consumerAccount;
	//     echo $sisow->consumerName;
	// }
	header("Location: payment.php?status=" . $sisow->status);
	exit;
}
*/
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Sisow :: Select Issuer</title>
<link rel="stylesheet" href="https://www.sisow.nl/Sisow/css/styleopd.css" type="text/css" />
<link rel="stylesheet" href="https://www.sisow.nl/Sisow/css/style.css" type="text/css" />
<link rel="stylesheet" href="https://www.sisow.nl/Sisow/css/style_table.css" type="text/css" />
<link href="https://www.sisow.nl/Sisow/images/sisow_blauw.ico" rel="shortcut icon" type="image/x-icon" />
<script>

function changePayment()
{
	var paymentIcons = {
		"ideal": "https://www.sisow.nl/Sisow/images/ideal/idealklein.gif",
		"sofort": "https://www.sisow.nl/Sisow/images/ideal/direct100.png",
		"mistercash": "https://www.sisow.nl/Sisow/images/ideal/bcmc.gif",
		"webshop": "https://www.sisow.nl/Sisow/images/ideal/wsgcklein.gif",
		"podium": "https://www.sisow.nl/Sisow/images/ideal/podium.jpg",
	}

	var paymentObj = document.body_form.payment;
	var paymentValue = paymentObj.options[paymentObj.selectedIndex].value.toLowerCase();
	if (!paymentValue || paymentValue == "") {
		paymentValue = "ideal";
	}
	var iconObj = document.getElementById("payment-icon");	
	if (iconObj) {
		if (paymentIcons[paymentValue]) {
			var iconSrc = paymentIcons[paymentValue];
			iconObj.src = iconSrc;
			iconObj.style.display = "inline";
		} else {
			iconObj.style.display = "none";
		}
	}
	var bankObj = document.querySelector(".bank-block");
	if (bankObj) {
		if (paymentValue == "" || paymentValue == "ideal") {
			bankObj.style.display = "table-row";
		} else {
			bankObj.style.display = "none";
		}
	}

}
function sisowPayment()
{
	var paymentObj = document.body_form.payment;
	var paymentValue = paymentObj.options[paymentObj.selectedIndex].value.toLowerCase();
alert(paymentValue);
	if (!paymentValue || paymentValue == "") {
		paymentValue = "ideal";
	}

}
window.addEventListener("load", changePayment, false);
</script>
</head>

<body>


<form name="body_form" method="post" action="sisow_checkout.php" id="body_form">
 
<table cellpadding="0" cellspacing="0" id="body_table" width="980" height="100%" align="center">
 
  <tr>
    <td class="logo" height="200" width="274" background="https://www.sisow.nl/Sisow/images/header/logo.jpg" valign="top" />
    <td class="top_info2" height="200" width="339" background="https://www.sisow.nl/Sisow/images/header/midden.jpg" valign="top">
      <span class="welkom">Welkom</span>
    </td>
    <td class="menu" height="200" width="367" background="https://www.sisow.nl/Sisow/images/header/menu.jpg" valign="top" style="padding-top: 20px; text-align: center; vertical-align: middle;">
      &nbsp;
    </td>
  </tr>
  
  <tr>
    <td colspan="3">
      <h2>Sisow betaling</h2>
      <img src="https://www.sisow.nl/Sisow/images/header/line.jpg" width="980" height="1" /><br />
    </td>
  </tr>
 
  
  <tr>
    <td colspan="3" class="content">
      <br />
      <div id="uplinks">
	
  
      <table cellpadding="0" cellspacing="0" width="525" align="center" class="detail_table">
        <tr>
          <td class="top"><div style="color: #008ed0;">&euro;</div></td>
        </tr>
        <tr>
          <td class="header"><div style="color: #008ed0;">iDEAL betaling</div></td>
        </tr>
        <tr>
          <td class="row" align="left">
          <table cellpadding="0" cellspacing="0" width="93%" align="left" class="detail_row">
            <tr>
              <td>
                <table cellpadding="0" cellspacing="0" width="100%">
                  <tr>
                    <td width="125">Betalingskenmerk </td>
                    <td><?php echo $purchase_id ?></td>
 
                    <td rowspan="8" style="width: 170px; text-align: center; vertical-align: top; ">
											<img id="payment-icon" src="https://www.sisow.nl/Sisow/images/ideal/idealklein.gif" border="0" />
										</td>
 
                  </tr>

                  <tr><td colspan="3">&nbsp;</td></tr>

                  <tr>
                    <td>Omschrijving</td>
                    <td><?php echo $description; ?></td>
                    <td>&nbsp;</td>
                  </tr>

                  <tr><td colspan="3">&nbsp;</td></tr>
 
                 <tr>
                    <td>Bedrag</td>
                    <td><?php echo ($amount); ?></td>
                    <td>&nbsp;</td>
                  </tr>
 
                  <tr><td colspan="3">&nbsp;</td></tr>
 
<?php if ($payment_options) { ?>
                  <tr>
                    <td>Betaalmethode</td>
                    <td>
                      <select name="payment" onchange="changePayment();" style="width: 200px; color: #008ed0">
                    		<?php echo $payment_options ?></td>
                      </select>
                    </td>
                    <td>&nbsp;</td>
                  </tr>
                 	<tr><td colspan="3">&nbsp;</td></tr>
<?php } ?>
 
                  <tr class="bank-block">
                    <td>Bank</td>
                    <td><?php echo $issuers_select ?></td>
                    <td>&nbsp;</td>
                  </tr>
 
                  <tr><td colspan="3">&nbsp;</td></tr>
                </table>
              </td>
            </tr>
          </table>
          </td>
        </tr>
        <tr>
          <td class="footer" valign="top">
            <table cellpadding="0" cellspacing="0" style="width: 500px; font-family: Verdana; font-size: 10px;">
              <tr style="height: 30px;">
                <td style="text-align: right; xwidth: 100px;">
		  						<input type="button" onclick="this.disabled=true;document.body_form.submit()" value="Ga verder" title="Betaal" />
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
 
</div>
    </td>
  </tr>
 
 
  <tr>
    <td class="bg_bottom2" height="19" colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td class="bottom_left" height="25" colspan="2" align="left">&nbsp;</td>
    <td class="bottom_right" height="25" align="right">&copy; Copyright - sisow</td>
  </tr>
 
</table>

</form>

</body>
</html>

