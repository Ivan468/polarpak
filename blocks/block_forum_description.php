<?php

	$default_title = "{forum_name}";

	$forum_name = ""; $full_description = ""; 
	$forum_image = ""; $forum_description = "";
	$forum_image_alt = "";

	$desc_image = get_setting_value($vars, "forum_description_image", 3);
	$desc_type  = get_setting_value($vars, "forum_description_type", 2);

	if(!isset($forum_info)) { $forum_info = array(); }
	$forum_id = get_setting_value($forum_info, "forum_id", $forum_id);
	$forum_name = get_translation(get_setting_value($forum_info, "forum_name"));
	if ($desc_image == 3) {
		$forum_image = get_setting_value($forum_info, "large_image");
	} elseif ($desc_image == 2) {
		$forum_image = get_setting_value($forum_info, "small_image");
	}

	if ($desc_type == 2) {
		$forum_description = get_translation(get_setting_value($forum_info, "full_description"));
	} elseif ($desc_type == 1) {
		$forum_description = get_translation(get_setting_value($forum_info, "short_description"));
	}    

	if(strlen($forum_description) || $forum_image)
	{
		$html_template = get_setting_value($block, "html_template", "block_forum_description.html"); 
	  $t->set_file("block_body", $html_template);

		if (strlen($forum_image)) {
			if (preg_match("/^http\:\/\//", $forum_image)) {
				$image_size = "";
			} else {
				$image_size = @GetImageSize($forum_image);
				if (isset($restrict_forum_images) && $restrict_forum_images) { 
					$forum_image = "image_show.php?forum_id=".$forum_id."&type=large"; 
				}
			}
			if (!strlen($forum_image_alt)) { $forum_image_alt = $forum_name; }
				$t->set_var("alt", htmlspecialchars($forum_image_alt));
				$t->set_var("src", htmlspecialchars($forum_image));
			if(is_array($image_size)) {
				$t->set_var("width", "width=\"" . $image_size[0] . "\"");
				$t->set_var("height", "height=\"" . $image_size[1] . "\"");
			} else {
				$t->set_var("width", "");
				$t->set_var("height", "");
			}
			$t->sparse("image_large_block", false);
		} else {
			$t->set_var("image_large_block", "");
		}

		$t->set_var("forum_name", $forum_name);
		$t->set_var("full_description", $forum_description);

		$block_parsed = true;
	}

?>