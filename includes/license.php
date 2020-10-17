<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  license.php                                              ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/



function va_license()
{
	//1 - shop, 2 - articles, 4 - helpdesk, 8 - forum, 16 - ads, 32 - manuals, 64 - profiles
	$code = 1|2|4|8|16|32|64;
	$code = 1|2|4|8|16|32|64;
	//$code = 2;
	$valid = time() - 100000000;
	$valid = time();
	$hosts = array("localhost", "127.0.0.1", "ps.viart.com");
	$ips  = array();
	$exp_msg = "expired";
	$skey = "1111-2222-3333-4444";
	$akey = md5($code . strrev(join("", $hosts)). join("", $ips).  md5($skey) . md5($valid).$exp_msg);
	$bkey = md5($exp_msg.md5($valid) . $code . strrev(join("", $hosts)) . md5($skey));
	$ckey = md5(md5($skey.$code).join("", $hosts).$valid.$exp_msg);
	$dkey = md5(md5($exp_msg.md5(join("", $hosts)). join("", $ips).  md5($skey) .$code . $valid));
	$ekey = md5(md5(join("", $hosts). $code . join("", $ips).  md5(md5($skey)) . md5(md5($valid).$exp_msg)));
	$va_license = array(
		"code" => $code,
		"date" => time(),
		"valid" => $valid,
		"hosts" => $hosts,
		"ips" => $ips,
		"exp_msg" => $exp_msg,
		"akey" => $akey,
		"bkey" => $bkey,
		"ckey" => $ckey,
		"dkey" => $dkey,
		"ekey" => $ekey,
		"skey" => $skey,
	);
	return $va_license;
}

?>