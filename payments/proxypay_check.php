<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  proxypay_check.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * ProxyPay (www.clear2pay.com) transaction handler by http://www.viart.com/
 */

	$sql  = " SELECT success_message FROM " . $table_prefix . "orders ";
	$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
	$success_message = get_db_value($sql);

	if (!strlen($success_message)) {
		$pending_message = "There are no answer from payment gateway. This order will be reviewed manually.";
	}

?>