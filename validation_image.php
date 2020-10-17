<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  validation_image.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	// turn output buffering on to check image for some utf-8 symbols at the begin
	ob_start();
	include_once("./includes/var_definition.php");
	include_once("./includes/common_functions.php");

	session_start();

	$id = isset($_GET["id"]) ? $_GET["id"] : 0; // set different validation number for different forms so user can submit two and more forms simultaneously
	if (!$id) { $id = 0; } // use zero value as default as no other was set

	$validation_numbers = get_session("session_validation_numbers"); // save here all numbers we need to validate
	if (!is_array($validation_numbers)) { $validation_numbers = array(); } 
	$validation_passed = get_session("session_validation_passed"); // those numbers were validated but form is not yet fully submitted
	if (!is_array($validation_passed)) { $validation_passed = array(); } 

	set_session("session_validation_passed", $validation_passed);

	// use random value for checks
	if (isset($validation_passed[$id]) && $validation_passed[$id]) {
		$random_number = $validation_passed[$id];
	} else {
		$random_number = rand(1000,9999);
	}

	$validation_numbers[$id] = $random_number;
	if (count($validation_numbers) > 100) { // save no more than 100 validation numbers for different forms
		$validation_numbers = array_slice ($validation_numbers, count($validation_numbers) - 100, 0, true);
	}
	set_session("session_validation_numbers", $validation_numbers);

	$random_string = strval($random_number);

	$rand1 = substr($random_string, 0, 1);
	$rand2 = substr($random_string, 1, 1);
	$rand3 = substr($random_string, 2, 1);
	$rand4 = substr($random_string, 3, 1);

	$width = 120;
	$height = 32;
	$image = imagecreate($width, $height);
	$bgColor = imagecolorallocate ($image, rand(0,54)+200, rand(0,54)+200, rand(0,54)+200);

	$sm = rand(0,30);
	if (function_exists("imagettftext")) {
		putenv("GDFONTPATH=" . realpath("./includes/font/"));
		if (time() % 2 == 0) {
			$font = dirname(__FILE__)."/includes/font/comic.ttf";
		} else {
			$font = dirname(__FILE__)."/includes/font/impact.ttf";
		}

		$textColor = imagecolorallocate ($image, rand(0,200), rand(0,200), rand(0,200));
		imagettftext ($image, 18+rand(0,15), -10+rand(0,20), 0+$sm+rand(0,9), 28+rand(0,5), $textColor, $font, $rand1);  
		$textColor = imagecolorallocate ($image, rand(0,200), rand(0,200), rand(0,200));
		imagettftext ($image, 18+rand(0,15), -10+rand(0,20), 20+$sm+rand(0,9), 28+rand(0,5), $textColor, $font, $rand2);
		$textColor = imagecolorallocate ($image, rand(0,200), rand(0,200), rand(0,200));
		imagettftext ($image, 18+rand(0,15), -10+rand(0,20), 40+$sm+rand(0,9), 28+rand(0,5), $textColor, $font, $rand3);
		$textColor = imagecolorallocate ($image, rand(0,200), rand(0,200), rand(0,200));
		imagefttext ($image, 18+rand(0,15), -10+rand(0,20), 60+$sm+rand(0,9), 28+rand(0,5), $textColor, $font, $rand4);

		for ($i = 0; $i < 30; $i++) {
	    $rx1 = rand(0,$width);
  		$rx2 = rand(0,$width);
	    $ry1 = rand(0,$height);
	    $ry2 = rand(0,$height);
	    $rcVal = rand(0,255);
	    $rc1 = imagecolorallocate($image, rand(0,255), rand(0,255), rand(0,255));
			//show lines if needed
  		//imageline ($image, $rx1, $ry1, $rx2, $ry2, $rc1);  
		}                                                       
	} else { 
		// if can't find FreeType library then draw common symbols
		for ($i = 0; $i < 10; $i++) {
	    $rx1 = rand(0,$width);
  		$rx2 = rand(0,$width);
	    $ry1 = rand(0,$height);
	    $ry2 = rand(0,$height);
	    $rcVal = rand(0,255);
	    $rc1 = imagecolorallocate($image, rand(0,255), rand(0,255), rand(0,255));
  		imageline ($image, $rx1, $ry1, $rx2, $ry2, $rc1);  
		}                                                       
		imagestring ($image, 6, 0+$sm+rand(0,5),  5+rand(0,15), $rand1, rand(0,54)+200);
		imagestring ($image, 6, 20+$sm+rand(0,5), 5+rand(0,15), $rand2, rand(0,54)+200);
		imagestring ($image, 6, 40+$sm+rand(0,5), 5+rand(0,15), $rand3, rand(0,54)+200);
		imagestring ($image, 6, 60+$sm+rand(0,5), 5+rand(0,15), $rand4, rand(0,54)+200);
	}

	// prepare image for output
	imagejpeg($image);
	imagedestroy($image);

	$image_content = ob_get_contents();
	ob_end_clean();
	$image_content = preg_replace("/^".chr(0xEF).chr(0xBB).chr(0xBF)."/", "", $image_content);
	$image_content = preg_replace("/^".chr(0xEF).chr(0xBB).chr(0xBF)."/", "", $image_content);

	//*
	header("Expires: Mon, 20 Jul 2006 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

	// HTTP/1.1
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	// HTTP/1.0
	header("Pragma: no-cache");
	header("Content-type: image/jpeg");

	// output clear image content
	echo $image_content;

