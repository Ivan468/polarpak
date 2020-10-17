<?php

	$default_title = "";

	$html_template = get_setting_value($block, "html_template", "block_download.html"); 
  $t->set_file("block_body", $html_template);

	if ($errors) 
	{
		$t->set_var("download_errors", $errors);
		$t->parse("errors_block", false);

		// send notification about bad download
		$remote_address = get_ip();
		$eol = get_eol();
		$subject = "Download Error";
		$message  = "IP Address: " . $remote_address . $eol;
		$message .= "Download ID: " . $download_id . $eol;
		$message .= "User ID: " . get_session("session_user_id") . $eol;
		$message .= "Error: " . $errors . $eol;
		$email = $settings["admin_email"];
		$email_headers = array();
		$email_headers["from"] = $email;
		//va_mail($email, $subject, $message,	"From: " . $settings["admin_email"]);

		$block_parsed = true;
	} elseif ($download_show_terms == 1 && $terms_agreed != 1) {

		include_once("./includes/page_layout.php");

		$t->set_var("download_href", "download.php");

		$t->set_var("download_id", htmlspecialchars($download_id));
		$t->set_var("release_id", htmlspecialchars($release_id));
		$t->set_var("path_id", htmlspecialchars($path_id));
		$t->set_var("order_item_id", htmlspecialchars($order_item_id));
		$t->set_var("vc", htmlspecialchars($vc_parameter));

		$item_name = get_translation($item_info["item_name"]);
		$t->set_var("item_name", htmlspecialchars($item_name));

		if ($item_info["big_image"]) {
			$item_image = $item_info["big_image"];
			$image_alt = $item_info["big_image_alt"];
			$watermark = get_setting_value($settings, "watermark_big_image", 0);
			$watermark_type = "large";
		} else {
			$item_image = $item_info["small_image"];
			$image_alt = $item_info["small_image_alt"];
			$watermark = get_setting_value($settings, "watermark_small_image", 0);
			$watermark_type = "small";
		}

		if (!$item_image || !image_exists($item_image)) { 
			$image_exists = false;
			$item_image = get_setting_value($settings, "product_no_image_large", ""); 
		} else {
			$image_exists = true;
		}

		$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");
		if ($item_image)
		{
			if (preg_match("/^http(s)?:\/\//", $item_image)) {
				$image_size = "";
			} else {
				$image_size = @getimagesize($item_image);
				if ($image_exists && ($watermark || $restrict_products_images)) { 
					$item_image = "image_show.php?item_id=".$item_id."&type=".$watermark_type."&vc=".md5($item_image); 
				}
			}
			if (!strlen($image_alt)) { $image_alt = $item_info["item_name"]; }
			$t->set_var("image_alt", htmlspecialchars($image_alt));
			$t->set_var("image_src", htmlspecialchars($item_image));
			if (is_array($image_size)) {
				$t->set_var("image_size", $image_size[2]);
			} else {
				$t->set_var("image_size", "");
			}
			$t->sparse("item_image", false);
		} else {
			$t->set_var("item_image", "");
		}

		if (strlen(trim($item_info["full_description"]))) {
			$item_desc = trim($item_info["full_description"]);
			$desc_type = $item_info["full_desc_type"];
		} else {
			$item_desc = trim($item_info["short_description"]);
			$desc_type = 1;
		}

		if ($desc_type != 1) {
			$item_desc = nl2br(htmlspecialchars($item_desc));
		}
		$t->set_var("item_desc", $item_desc);

		if ($operation == "download") {
			$t->set_var("download_errors", DOWNLOAD_TERMS_USER_ERROR);
			$t->parse("errors_block", false);
		}

		$php_in_download_terms = get_setting_value($settings, "php_in_products_download_terms", 0);
		//eval_php_code($download_terms_text);
		$t->set_var("terms_text", $download_terms_text);
		$t->parse("terms_form", false);

		$block_parsed = true;
	} 
