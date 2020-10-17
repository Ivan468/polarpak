<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  photo.php                                                ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/image_functions.php");

	$id = get_param("id");
	$vc = get_param("vc");
	$type = get_param("type");
	$user_id = get_session("session_user_id");
	$watermark = false; 
	// check if correct type passed
	if (!preg_match("/^(tiny|small|large|super)$/", $type)) { $type = "large"; }
	
	$photo_path = "";
	$sql  = " SELECT * FROM " . $table_prefix . "users_photos ";
	$sql .= " WHERE photo_id=" . $db->tosql($id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$photo_user_id = $db->f("user_id");
		$field_name = $type."_photo";
		$photo_path = $db->f($field_name);
		$photo_vc = md5($photo_path);
		if ($photo_vc != $vc && $photo_user_id != $user_id) {
			$photo_path = "";
		}
	}

	if (strlen($photo_path)) {
		if ($watermark) {
				$watermark_image = get_setting_value($photo_settings, "watermark_image", "");
				$watermark_image_pos = get_setting_value($photo_settings, "watermark_image_pos", "");
				$watermark_image_pct = get_setting_value($photo_settings, "watermark_image_pct", "");
	  
				$watermark_text = get_setting_value($photo_settings, "watermark_text", "");
				$watermark_text_size = get_setting_value($photo_settings, "watermark_text_size", "");
				$watermark_text_color = get_setting_value($photo_settings, "watermark_text_color", "");
				$watermark_text_angle = get_setting_value($photo_settings, "watermark_text_angle", "");
				$watermark_text_pos = get_setting_value($photo_settings, "watermark_text_pos", "");
				$watermark_text_pct = get_setting_value($photo_settings, "watermark_text_pct", "");
			image_watermark($photo_path, $watermark_image, $watermark_image_pos, $watermark_image_pct, $watermark_text, $watermark_text_size, $watermark_text_color, $watermark_text_angle, $watermark_text_pos, $watermark_text_pct);
		} else {
	    $fp = @fopen($photo_path, "rb");
	    if ($fp) {
				if(preg_match("/\.gif$/", $photo_path)) {
					header("Content-Type: image/gif");
				} elseif(preg_match("/\.png$/", $photo_path)) {
					header("Content-Type: image/png");
				} else {
					header("Content-Type: image/jpeg");
				}
			  fpassthru($fp);
			  exit;
	    }
		}
	} else {
		$image_id = imagecreatetruecolor(60, 60); 
		$bgc = imagecolorallocate($image_id, 255, 255, 255);
		$tc  = imagecolorallocate($image_id, 0, 0, 0);
		imagefilledrectangle($image_id, 0, 0, 60, 60, $bgc);
		imagerectangle ($image_id, 0 , 0, 59, 59, $tc);
		imagestring($image_id, 1, 10, 10, "Error", $tc);
		imagestring($image_id, 1, 10, 25, "Loading", $tc);
		imagestring($image_id, 1, 10, 40, "Image", $tc);

		header("Content-Type: image/jpeg");
		imagejpeg($image_id);

		//header("HTTP/1.0 404 Not Found");
		//header("Status: 404 Not Found");
		//exit;
	}

?>