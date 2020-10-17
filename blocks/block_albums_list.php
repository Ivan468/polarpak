<?php

	$default_title = "{ALBUMS_MSG}";

	// friendly url settings
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	// block settings
	$albums_recs = get_setting_value($vars, "recs", 50);
	$albums_cols = get_setting_value($vars, "cols", 2);
	if ($albums_cols < 0) { $albums_cols = 1; }
	$show_image_tiny = get_setting_value($vars, "image_tiny", 0);
	$show_image_small = get_setting_value($vars, "image_small", 0);
	$show_image_large = get_setting_value($vars, "image_large", 0);
	$show_image_super = get_setting_value($vars, "image_super", 0);
	$show_short_desc = get_setting_value($vars, "short_description", 0);
	$show_full_desc = get_setting_value($vars, "full_description", 0);
	$html_template = get_setting_value($block, "html_template", "block_albums_list.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("albums_rows",  "");
	$t->set_var("albums_cols",  "");
	$t->set_var("columns_class", "cols-".$albums_cols);

	$db->RecordsPerPage = $albums_recs;
	$db->PageNumber = 1;
	$sql  = " SELECT *  ";
	$sql .= " FROM " . $table_prefix . "albums ";
	if (isset($albums_filter) && strlen($albums_filter)) {
		if ($albums_filter == "0-9") {
			$sql .= " WHERE name_first IN ('0','1','2','3','4','5','6','7','8','9') ";
		} else {
			$sql .= " WHERE name_first=" . $db->tosql($albums_filter, TEXT);
		}
	}
	$sql .= " ORDER BY album_name ";
	$db->query($sql);
	if ($db->next_record()) {
		$position = 0; $col_style = "";
		if ($albums_cols > 1) {
			$col_style = "width: ".round(100 / $albums_cols, 2)."%;";
		}
		$t->set_var("col_style", $col_style);
		do {
			// get data
			$position++;

			$album_id = $db->f("album_id");
			$album_name = $db->f("album_name");
			$friendly_url = $db->f("friendly_url");

			$image_tiny = $db->f("image_tiny");
			$image_tiny_alt = $db->f("image_tiny_alt");
			$image_small = $db->f("image_small");
			$image_small_alt = $db->f("image_small_alt");
			$image_large = $db->f("image_large");
			$image_large_alt = $db->f("image_large_alt");
			$image_super = $db->f("image_super");
			$image_super_alt = $db->f("image_super_alt");

			$short_description = get_translation($db->f("short_description"));
			$full_description = get_translation($db->f("full_description"));

			$t->set_var("album_id", $album_id);
			$t->set_var("album_name", htmlspecialchars($album_name));
			if ($friendly_urls && $friendly_url) {
				$album_url = $friendly_url . $friendly_extension;
			} else {
				$album_url = "album.php?album_id=" . $album_id;
			}
			$t->set_var("album_url", htmlspecialchars($album_url));

			if ($show_image_tiny && $image_tiny) {
				$t->set_var("image_tiny_src", $image_tiny);
				$t->set_var("image_tiny_alt", $image_tiny_alt);
				$t->sparse("image_tiny_block", false);
			} else {
				$t->set_var("image_tiny_block", "");
			}
			if ($show_image_small && $image_small) {
				$t->set_var("image_small_src", $image_small);
				$t->set_var("image_small_alt", $image_small_alt);
				$t->sparse("image_small_block", false);
			} else {
				$t->set_var("image_small_block", "");
			}
			if ($show_image_large && $image_large) {
				$t->set_var("image_large_src", $image_large);
				$t->set_var("image_large_alt", $image_large_alt);
				$t->sparse("image_large_block", false);
			} else {
				$t->set_var("image_large_block", "");
			}
			if ($show_image_super && $image_super) {
				$t->set_var("image_super_src", $image_super);
				$t->set_var("image_super_alt", $image_super_alt);
				$t->sparse("image_super_block", false);
			} else {
				$t->set_var("image_super_block", "");
			}
			if ($show_short_desc) {
				$t->set_var("short_description", $short_description);
				$t->sparse("short_description_block", false);
			} else {
				$t->set_var("short_description_block", "");
			}
			if ($show_full_desc) {
				$t->set_var("full_description", $full_description);
				//$t->set_var("full_description", nl2br(htmlspecialchars($full_description)));
				$t->sparse("full_description_block", false);
			} else {
				$t->set_var("full_description_block", "");
			}

			$t->parse("albums_cols", true);
			if ($position % $albums_cols == 0) {
				$t->parse("albums_rows", true);
				$t->set_var("albums_cols", "");
			}
		} while ($db->next_record());
		if ($position % $albums_cols) {
			$t->parse("albums_rows", true);
		}
		$block_parsed = true;
	}


?>