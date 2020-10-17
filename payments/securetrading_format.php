<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  securetrading_format.php                                 ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/**
 * SecureTrading STPP Shopping Carts
 * Viart 4.1
 * Module Version 2.5.7
 * Last Updated 01 August 2013
 * Written by Peter Barrow for SecureTrading Ltd.
 * http://www.securetrading.com
 */

?><?php

require_once(dirname(__FILE__) . '/securetrading_stpp_lib/securetrading_stpp/STPPLoader.php');
require_once(dirname(__FILE__) . '/securetrading_stpp_lib/ViArtPPages.class.php');

$is_admin_path = true;
require_once(dirname(dirname(__FILE__)) . '/includes/common.php');
$is_admin_path = false;

$db->query("SELECT payment_id FROM " . $table_prefix . "orders WHERE order_id =" . $db->tosql($_POST['orderreference'], INTEGER));

if ($db->num_rows() !== 1) {
	exit("Invalid number of results.");
}

$db->next_record();
$payment_id = $db->Record[0];
$secureParams = array();

$db->query("SELECT parameter_name, parameter_source FROM " . $table_prefix . "payment_parameters WHERE payment_id=" . $db->tosql($payment_id, INTEGER) . " AND not_passed = 1");

while ($db->next_record()) {
	$secureParams[$db->f("parameter_name")] = $db->f("parameter_source");
}

$_POST['billingtelephonetype'] = !empty($_POST['billingtelephone']) ? 'H' : NULL;
$_POST['customertelephonetype'] = !empty($_POST['billingtelephone']) ? 'H' : NULL;

$requestObject = new stdClass;
$requestObject->version = $secureParams['version'];
$requestObject->sitereference = $secureParams['sitereference'];
$requestObject->settlestatus = $secureParams['settlestatus'];
$requestObject->settleduedate = $secureParams['settleduedate'];

foreach($_POST as $k => $v) {
	$requestObject->$k = $v;
}

$ppages = new ViArtPPages();

if ($secureParams['usesitesecurity']) {
	$ppages->createHash($requestObject, $secureParams['sitesecurity']);
}
$ppages->runPaymentPages($requestObject);

?>