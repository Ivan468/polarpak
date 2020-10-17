<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  barcode_image.php                                        ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/barcode_functions.php");

	$text = isset($_REQUEST['text']) ? $_REQUEST['text'] : '123456789012';  				// parameter: bar code text
	$imgtype = isset($_REQUEST['imgtype']) ? $_REQUEST['imgtype'] : 'png'; 					// parameter: image type (png, gif, jpg)
	$codetype = isset($_REQUEST['codetype']) ? $_REQUEST['codetype'] : 'code128'; 	// parameter: code type (code128, ean13, code39, int25, upca)

	draw_barcode($text, $imgtype, $codetype);

?>