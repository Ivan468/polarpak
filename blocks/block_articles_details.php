<?php

	include_once("./includes/articles_functions.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");

	$default_title = "{article_title}";

//function articles_details($block_name, $article_id, $category_id, $details_fields, $details_template)
	$article_id = get_param("article_id");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	$html_template = get_setting_value($block, "html_template", "block_articles_details.html"); 
  $t->set_file("block_body", $html_template);
	set_script_tag("js/images.js");

	$tell_friend_href = get_custom_friendly_url("tell_friend.php") . "?item_id=" . urlencode($article_id) . "&type=articles";

	$t->set_var("tell_friend_href", htmlspecialchars($tell_friend_href));
	$t->set_var("articles_print_href", get_custom_friendly_url("article_print.php"));
	$t->set_var("article_id", htmlspecialchars($article_id));
	$t->set_var("item_id", htmlspecialchars($article_id));

	$rp = get_custom_friendly_url("article.php") . "?article_id=" . urlencode($article_id);
	$reviews_href = get_custom_friendly_url("articles_reviews.php") . "?article_id=" . urlencode($article_id);

	$t->set_var("rp_url", urlencode($rp));
	$t->set_var("rp", htmlspecialchars($rp));
	$t->set_var("reviews_href", htmlspecialchars($reviews_href));
	$t->set_var("reviews_url", htmlspecialchars($reviews_href));

	$details_fields = ",," . $details_fields . ",,";
	$article_fields = array(
		"author_name", "author_email", "author_url", "link_url", "download_url",
		"hot_description", "highlights", "short_description", "keywords", "notes"
	);
	$article_date_type = get_setting_value($edit_fields, "article_date_format");
	$date_end_type = get_setting_value($edit_fields, "date_end_format");
	$article_date_format = ($article_date_type == "date") ? $date_show_format : $datetime_show_format;
	$date_end_format = ($date_end_type == "date") ? $date_show_format : $datetime_show_format;
	
	if (!VA_Articles::check_exists($article_id)) {
		$t->set_var("article_item", "");
		$t->set_var("NO_ARTICLE_MSG", NO_ARTICLE_MSG);
		$t->parse("no_article_item", false);		
		$block_parsed = true;
		return;
	}
	
	if (!VA_Articles::check_permissions($article_id, false, VIEW_ITEMS_PERM)) {
		$site_url = get_setting_value($settings, "site_url", "");
		$secure_url = get_setting_value($settings, "secure_url", "");
		$secure_user_login = get_setting_value($settings, "secure_user_login", 0);
		if ($secure_user_login) {
			$user_login_url = $secure_url . get_custom_friendly_url("user_login.php");
		} else {
			$user_login_url = $site_url . get_custom_friendly_url("user_login.php");
		}
		$return_page = get_request_uri();
		header ("Location: " . $user_login_url . "?return_page=" . urlencode($return_page) . "&type_error=2&ssl=".intval($is_ssl));
		exit;
	}

	$set_video_js	= false;
	// retrieve info for article 
	$sql  = " SELECT article_id, friendly_url, article_title, article_date, date_end, ";
	$sql .= " author_name, author_email, author_url, link_url, link_title, download_url, ";
	$sql .= " hot_description, highlights, short_description, is_html, full_description, ";
	$sql .= " image_tiny,  image_tiny_alt, image_small,  image_small_alt, image_large, image_large_alt, image_super, image_super_alt, ";
	$sql .= " youtube_video, youtube_video_width, youtube_video_height, ";
	$sql .= " stream_video, stream_video_width, stream_video_height, stream_video_preview, ";
	$sql .= " meta_title, meta_keywords, meta_description, ";
	$sql .= " total_views, total_votes, total_points, allowed_rate, ";
	$sql .= " keywords, notes, is_remote_rss, details_remote_url ";
	$sql .= " FROM " . $table_prefix . "articles a ";
	$sql .= " WHERE article_id= " . $db->tosql($article_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {

		$article_id   = $db->f("article_id");
		$friendly_url = $db->f("friendly_url");
		$article_title = get_currency_message(get_translation($db->f("article_title")), $currency);
		$hot_description = get_currency_message(get_translation($db->f("hot_description")), $currency);
		$highlights = get_currency_message(get_translation($db->f("highlights")), $currency);
		$short_description = get_currency_message(get_translation($db->f("short_description")), $currency);
		$full_description  = get_currency_message(get_translation($db->f("full_description")), $currency);
		if (!$full_description) { $full_description = $short_description; }
		$allowed_rate = $db->f("allowed_rate");
		$total_views  = $db->f("total_views");		
		$link_url   = $db->f("link_url");		
		$link_title = $db->f("link_title");		
		if (!$link_title) { $link_title = $link_url; }
		$t->set_var("link_title", htmlspecialchars($link_title));

		// video data
		$youtube_video         = $db->f("youtube_video");
		$youtube_video_width   = $db->f("youtube_video_width");
		$youtube_video_height  = $db->f("youtube_video_height");
		$stream_video          = $db->f("stream_video");
		$stream_video_width    = $db->f("stream_video_width");
		$stream_video_height   = $db->f("stream_video_height");
		$stream_video_preview  = $db->f("stream_video_preview");
		
		// meta files
		$meta_title = get_translation($db->f("meta_title"));
		$meta_keywords = get_translation($db->f("meta_keywords"));
		$meta_description = get_translation($db->f("meta_description"));

		$auto_meta_title = $meta_title;
		if (!strlen($auto_meta_title)) { $auto_meta_title = $article_title; }
		$auto_meta_description = $meta_description;
		if (!strlen($auto_meta_description)) {
			if (strlen($short_description)) {
				$auto_meta_description = $short_description;
			} elseif (strlen($full_description)) {
				$auto_meta_description = $full_description;
			}
		}

		if ($friendly_urls && $page_friendly_url) {
			$article_url = $friendly_url.$friendly_extension;
		} else {
			$article_url = "article.php?article_id=".$article_id;
		}

		$t->set_var("article_id", $article_id);
		$t->set_var("article_name", $article_title);
		$t->set_var("article_title", $article_title);
		$t->set_var("article_url", htmlspecialchars($article_url));

		// get fields values
		$article_date_string = ""; $date_end_string = "";
		if (strpos($details_fields, ",article_date,")) {
			$article_date = $db->f("article_date", DATETIME);
			$article_date_string  = va_date($article_date_format, $article_date);

			$t->set_var("article_date", $article_date_string);
			$t->global_parse("article_date_block", false, false, true);
		} else {
			$t->set_var("article_date_block", "");
		}
		if (strpos($details_fields, ",date_end,")) {
			$date_end = $db->f("date_end", DATETIME);
			$date_end_string = va_date($date_end_format, $date_end);
			$t->set_var("date_end", $date_end_string);
			$t->global_parse("date_end_block", false, false, true);
		} else {
			$t->set_var("date_end_block", "");
		}
		if (strlen($article_date_string) || strlen($date_end_string)) {
			$t->global_parse("date_block", false, false, true);
		}

		for ($i = 0; $i < sizeof($article_fields); $i++) {
			$field_name = $article_fields[$i];
			$fields[$field_name] = get_currency_message(get_translation($db->f($field_name)), $currency);
			if (strlen($fields[$field_name]) && strpos($details_fields, "," . $field_name . ",")) {
				$t->set_var($field_name, $fields[$field_name]);
				$t->global_parse($field_name . "_block", false, false, true);
			} else {
				$fields[$field_name] = "";
				$t->set_var($field_name, "");
				$t->set_var($field_name . "_block", "");
			}
		}

		if (strlen($fields["author_name"]) || strlen($fields["author_email"]) || strlen($fields["author_url"])) {
			$t->global_parse("author_block", false, false, true);
		} else {
			$t->set_var("author_block", false);
		}

		if (strpos($details_fields, ",full_description,")) {
			if ($db->f("is_html") != 1) {
				$full_description = nl2br(htmlspecialchars($full_description));
			} else if ($youtube_video) {
				// check if we need to add youtube attributes
				if (!preg_match("/id=\"youtube-lyrics\"/i", $full_description) && !preg_match("/data-video-id=\"".preg_quote($youtube_video,"/")."\"/i", $full_description)) {
					$full_description = "<div data-video-id=\"".htmlspecialchars($youtube_video)."\">".$full_description."</div>";
				}
			}
			$t->set_var("full_description", $full_description);
			$t->parse("full_description_block", false);
		} else {
			$t->set_var("full_description_block", "");
		}
		// set description tags
		$t->set_var("highlights", $highlights);
		$t->set_var("hot_description", $hot_description);
		$t->set_var("full_description", $full_description);
		$t->set_var("short_description", $short_description);

		$image_tiny_default = $db->f("image_tiny");
		$image_tiny_alt_default = $db->f("image_tiny_alt");
		$image_small_default = $db->f("image_small");
		$image_small_alt_default = $db->f("image_small_alt");
		$image_large_default = $db->f("image_large");
		$image_large_alt_default = $db->f("image_large_alt");
		$image_super_default = $db->f("image_super");
		$image_super_alt_default = $db->f("image_super_alt");
		$t->set_var("image_tiny", htmlspecialchars($image_tiny_default));
		$t->set_var("image_small", htmlspecialchars($image_small_default));
		$t->set_var("image_large", htmlspecialchars($image_large_default));
		$t->set_var("image_super", htmlspecialchars($image_super_default));

		$image_block = false;
		if (strpos($details_fields, ",image_tiny,") && strlen($image_tiny_default)) {
			$image_block = true;
			if (!strlen($image_tiny_alt_default)) { $image_tiny_alt_default = $article_title; }
			$t->set_var("alt", htmlspecialchars($image_tiny_alt_default));
			$t->set_var("src", htmlspecialchars($image_tiny_default));
			$t->parse("image_tiny_block", false);
		} else {
			$t->set_var("image_tiny_block", "");
		}
		if (strpos($details_fields, ",image_small,") && strlen($image_small_default)) {
			$image_block = true;
			if (!strlen($image_small_alt_default)) { $image_small_alt_default = $article_title; }
			$t->set_var("alt", htmlspecialchars($image_small_alt_default));
			$t->set_var("src", htmlspecialchars($image_small_default));
			$t->parse("image_small_block", false);
		} else {
			$t->set_var("image_small_block", "");
		}
		if (strpos($details_fields, ",image_large,") && strlen($image_large_default)) {
			$image_block = true;
			if (!strlen($image_large_alt_default)) { $image_large_alt_default = $article_title; }
			$t->set_var("alt", htmlspecialchars($image_large_alt_default));
			$t->set_var("src", htmlspecialchars($image_large_default));
			$t->parse("image_large_block", false);
		} else {
			$t->set_var("image_large_block", "");
		}
		if (strpos($details_fields, ",image_super,") && strlen($image_super_default)) {
			$image_block = true;
			if (!strlen($image_super_alt_default)) { $image_super_alt_default = $article_title; }
			$t->set_var("alt", htmlspecialchars($image_super_alt_default));
			$t->set_var("src", htmlspecialchars($image_super_default));
			$t->parse("image_super_block", false);
		} else {
			$t->set_var("image_super_block", "");
		}

		// update total views for article
		$articles_viewed = get_session("session_articles_viewed");
		if (!isset($articles_viewed[$article_id])) {
			$sql  = " UPDATE " . $table_prefix . "articles SET total_views=" . $db->tosql(($total_views + 1), INTEGER);
			$sql .= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
			$db->query($sql);

			$articles_viewed[$article_id] = true;
			set_session("session_articles_viewed", $articles_viewed);
		}

		$authors_names = "";
		$t->set_var("authors", "");
		$t->set_var("authors_block", "");
		if (strpos($details_fields, "authors")){
			// check article authors
			$sql  = " SELECT a.author_id, a.friendly_url, a.author_name ";
			$sql .= " FROM (" . $table_prefix ."authors a ";
			$sql .= " INNER JOIN " . $table_prefix ."articles_authors aa ON aa.author_id=a.author_id) ";
			$sql .= " WHERE aa.article_id=" . $db->tosql($article_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				do {
					$author_id = $db->f("author_id");
					$author_name = $db->f("author_name");
					if ($authors_names !== "") { $authors_names .= " & "; }
					$authors_names .= $author_name;
					$friendly_url = $db->f("friendly_url");
					if ($friendly_urls && $friendly_url) {
						$author_url = $friendly_url . $friendly_extension;
						$author_lyrics_url = $friendly_url."-lyrics".$friendly_extension;
						$author_articles_url = $friendly_url."-articles".$friendly_extension;
					} else {
						$author_url = "author.php?author_id=" . $author_id;
						$author_lyrics_url = "author_articles.php?author_id=" . $author_id;
						$author_articles_url = "author_articles.php?author_id=" . $author_id;
					}
					$t->set_var("author_url", htmlspecialchars($author_url));
					$t->set_var("author_lyrics_url", htmlspecialchars($author_lyrics_url));
					$t->set_var("author_articles_url", htmlspecialchars($author_articles_url));

					$t->set_var("author_id", $author_id);
					$t->set_var("author_url", htmlspecialchars($author_url));
					$t->set_var("author_name", htmlspecialchars($author_name));
					$t->sparse("authors", true);
				} while ($db->next_record());
				$t->sparse("authors_block", false);
			}
		}
		$t->set_var("authors_names", htmlspecialchars($authors_names));

		$t->set_var("albums", "");
		$t->set_var("albums_block", "");
		if (strpos($details_fields, "albums")){
			// check article authors
			$sql  = " SELECT a.album_id, a.friendly_url, a.album_name ";
			$sql .= " FROM (" . $table_prefix ."albums a ";
			$sql .= " INNER JOIN " . $table_prefix ."articles_albums aa ON aa.album_id=a.album_id) ";
			$sql .= " WHERE aa.article_id=" . $db->tosql($article_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				do {
					$album_id = $db->f("album_id");
					$album_name = $db->f("album_name");
					$friendly_url = $db->f("friendly_url");
					if ($friendly_urls && $friendly_url) {
						$album_url = $friendly_url . $friendly_extension;
					} else {
						$album_url = "album.php?album_id=" . $author_id;
					}
					$t->set_var("album_url", htmlspecialchars($album_url));

					$t->set_var("album_id", htmlspecialchars($album_id));
					$t->set_var("album_url", htmlspecialchars($album_url));
					$t->set_var("album_name", htmlspecialchars($album_name));
					$t->sparse("albums", true);
				} while ($db->next_record());
				$t->sparse("albums_block", false);
			}
		}

		if ($youtube_video && strpos($details_fields, "youtube_video")) {
			$set_video_js = true;
			if (!$youtube_video_width) { $youtube_video_width = 560; }
			if (!$youtube_video_height) { $youtube_video_height = 315; }
			$t->set_var("youtube_video", $youtube_video);
			$t->set_var("youtube_video_width", intval($youtube_video_width));
			$t->set_var("youtube_video_height", intval($youtube_video_height));
			$t->parse("youtube_video_block", false);
		}

		if (strlen($stream_video) && strpos($details_fields, "stream_video")){
			$set_video_js = true;
			$path_parts = pathinfo($stream_video);
			$ext = strtolower($path_parts['extension']);
			if ($ext == "flv" || $ext == "mp4") {
				if (!strlen($stream_video_width) && !strlen($stream_video_height)){
					$stream_video_width = '';
					$stream_video_height = '';
				}
				$t->set_var("stream_video_width", htmlspecialchars($stream_video_width));
				$t->set_var("stream_video_height", htmlspecialchars($stream_video_height));
				$t->set_var("stream_video_preview", htmlspecialchars($stream_video_preview));
				$t->set_var("stream_video", htmlspecialchars($stream_video));

				$t->global_parse("flash_player_block", false, false, true);
			} else {
				if (!strlen($stream_video_width) && !strlen($stream_video_height)){
					$stream_video_width = 230;
					$stream_video_height = 140;
				}
				if ($stream_video_width < 230){
					$stream_video_height = $stream_video_height * 230 / $stream_video_width;
					$stream_video_width = 230;
				}
				$stream_video_height += 70;
				$t->set_var("stream_video_width", htmlspecialchars($stream_video_width));
				$t->set_var("stream_video_height", htmlspecialchars($stream_video_height));
				$t->set_var("stream_video", htmlspecialchars($stream_video));

				$t->global_parse("windows_media_block", false, false, true);
			}
		} else {
			$t->set_var("flash_player_block", "");
			$t->set_var("windows_media_block", "");
		}

		// check article images
		$images_top = 0; $images_gallery = 0; $default_matched = 0;
		$t->set_var("images_top", "");
		$t->set_var("images_gallery", "");
		$sql  = " SELECT * FROM " . $table_prefix ."articles_images ";
		$sql .= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$image_title = $db->f("image_title");
			$image_tiny = $db->f("image_tiny");
			$image_tiny_alt = $db->f("image_tiny_alt");
			$image_small = $db->f("image_small");
			$image_small_alt = $db->f("image_small");
			$image_large = $db->f("image_large");
			$image_large_alt = $db->f("image_large_alt");
			$image_super = $db->f("image_super");
			if (!$image_super) { $image_super = $image_large; }
			if (!$image_small) { $image_small = $image_tiny; }
			if (!$image_small_alt) { $image_small_alt = $image_title; }
			if (!$image_large_alt) { $image_large_alt = $image_title; }
			$image_position = $db->f("image_position");
			$image_description = $db->f("image_description");
			
			$t->set_var("image_title", htmlspecialchars($image_title));
			$t->set_var("image_description", htmlspecialchars($image_description));
			$t->set_var("image_tiny", htmlspecialchars($image_tiny));
			$t->set_var("image_small", htmlspecialchars($image_small));
			$t->set_var("image_small_alt", htmlspecialchars($image_small_alt));

			$t->set_var("image_large", htmlspecialchars($image_large));
			$t->set_var("image_super", htmlspecialchars($image_super));
			if ($image_position == 1) {
				$images_gallery++;
				$t->parse("images_gallery", true);
			} else if ($image_position == 2) {
				$images_top++;
				$t->parse("images_top", true);

				if ($image_small_default == $image_small && $image_large_default == $image_large) {
					$default_matched++;
				}
			}
		}
		if ($images_gallery) {
			$t->parse("images_gallery_title", false);
			$t->parse("images_gallery_block", false);
		}
		if ($images_top && $images_top != $default_matched) {
			$t->parse("images_top_block", false);
		}

		// parse main image block if it's active
		if ($image_block) {
			$t->sparse("image_block", false);
		} else {
			$t->set_var("image_block", "");
		}

		$fb_active = get_setting_value($article_settings, "fb_active", 0);
		$fb_app_id = get_setting_value($article_settings, "fb_app_id");
		if ($fb_active && $fb_app_id) {
			$og_url = $site_url.$article_url;
			$og_type = get_setting_value($article_settings, "fb_og_type", "article");
			$og_title = get_setting_value($article_settings, "fb_og_title", "{article_title}");
			$og_image = get_setting_value($article_settings, "fb_og_image", "{image_small}");
			$og_site_name= get_setting_value($article_settings, "fb_og_site_name", "{site_name}");
			$og_description = get_setting_value($article_settings, "fb_og_description", "{short_description}");
			parse_value($og_title);
			parse_value($og_image);
			parse_value($og_site_name);
			parse_value($og_description);
			$og_image = trim($og_image);
			$og_site_name = trim($og_site_name);
			$og_description = trim(strip_tags($og_description));

			// set tags
			$meta_tags["fb:app_id"] = array("name" => "meta", "attributes" => array("property" => "fb:app_id", "content" => $fb_app_id));
			$meta_tags["og:url"] = array("name" => "meta", "attributes" => array("property" => "og:url", "content" => $og_url));
			$meta_tags["og:type"] = array("name" => "meta", "attributes" => array("property" => "og:type", "content" => $og_type));
			$meta_tags["og:title"] = array("name" => "meta", "attributes" => array("property" => "og:title", "content" => $og_title));
			if ($og_image) {
				if (!preg_match("/^http/", $og_image)) { $og_image = $site_url.$og_image; }
				$meta_tags["og:image"] = array("name" => "meta", "attributes" => array("property" => "og:image", "content" => $og_image));
			}
			if ($og_site_name) {
				$meta_tags["og:site_name"] = array("name" => "meta", "attributes" => array("property" => "og:site_name", "content" => $og_site_name));
			}
			if ($og_description) {
				$meta_tags["og:description"] = array("name" => "meta", "attributes" => array("property" => "og:description", "content" => $og_description));
			}
		}

		$t->set_var("reviews_block", "");
		if ($allowed_rate) {

			// get articles reviews settings
			$articles_reviews_settings = get_settings("articles_reviews");
			$reviews_allowed_view = get_setting_value($articles_reviews_settings, "allowed_view", 0);
			$reviews_allowed_post = get_setting_value($articles_reviews_settings, "allowed_post", 0);
			$reviews_pd_type = get_setting_value($articles_reviews_settings, "pd_type", "");
			$reviews_pd_recs = get_setting_value($articles_reviews_settings, "pd_reviews_recs", 5);

			if ($reviews_allowed_view == 1 || ($reviews_allowed_view == 2 && strlen($user_id))
				|| $reviews_allowed_post == 1 || ($reviews_allowed_post == 2 && strlen($user_id))) {

				// count reviews
				$sql = " SELECT COUNT(*) FROM " . $table_prefix . "articles_reviews WHERE approved=1 AND article_id=" . $db->tosql($article_id, INTEGER);
				$total_votes = get_db_value($sql);
		  
				if ($total_votes)
				{
					// parse summary statistic
					$t->set_var("total_votes", $total_votes);
					$sql = " SELECT COUNT(*) FROM " . $table_prefix . "articles_reviews WHERE approved=1 AND rating <> 0 AND article_id=" . $db->tosql($article_id, INTEGER);
					$total_rating_votes = get_db_value($sql);
		  
					$average_rating_float = 0;
					if ($total_rating_votes)
					{
						$sql = " SELECT SUM(rating) FROM " . $table_prefix . "articles_reviews WHERE approved=1 AND rating <> 0 AND article_id=" . $db->tosql($article_id, INTEGER);
						$average_rating_float = round(get_db_value($sql) / $total_rating_votes, 2);
					}
					$average_rating = round($average_rating_float, 0);
					$average_rating_image = $average_rating ? "rating-" . $average_rating : "not-rated";
					$t->set_var("average_rating_image", $average_rating_image);
					$t->set_var("average_rating_alt", $average_rating_float);
		  
					$based_on_message = str_replace("{total_votes}", $total_votes, BASED_ON_REVIEWS_MSG);
					$t->set_var("BASED_ON_REVIEWS_MSG", $based_on_message);
					$t->parse("summary_statistic", false);
		  
					$is_reviews = false;

					// show reviews only if it allowed to see them
					if ($reviews_allowed_view == 1 || ($reviews_allowed_view == 2 && strlen($user_id))) {
						$t->set_var("reviews", "");
			  
						if ($reviews_pd_type == "1and1") {
							$reviews_data = array(
								"positive" => array("where" => "recommended=1", "order" => "date_added DESC", "recs" => 1),
								"negative" => array("where" => "recommended=-1", "order" => "date_added DESC", "recs" => 1),
							);
						} else {
							$reviews_data = array(
								"latest" => array("where" => "", "order" => "date_added DESC", "recs" => $reviews_pd_recs),
							);
						}
			  
						// show reviews
						foreach ($reviews_data as $review_type => $sql_data) {
							$sql  = " SELECT * FROM " . $table_prefix . "articles_reviews ";
							$sql .= " WHERE approved=1 AND comments IS NOT NULL ";
							if ($sql_data["where"]) {
								$sql .= " AND " . $sql_data["where"];
							}
							$sql .= " AND article_id=" . $db->tosql($article_id, INTEGER);
							if ($sql_data["order"]) {
								$sql .= " ORDER BY " . $sql_data["order"];
							}
							$db->RecordsPerPage = $sql_data["recs"];
							$db->PageNumber = 1;
							$db->query($sql);
							if ($db->next_record()) {
								$is_reviews = true;
								do {
									$review_user_id = $db->f("user_id");
									$review_user_name = htmlspecialchars($db->f("user_name"));
									if (!$review_user_id) {
										$review_user_name .= " (" . GUEST_MSG . ")";
									}
									$review_user_class = $review_user_id ? "forumUser" : "forumGuest";
									$recommended = $db->f("recommended");
									if ($recommended == 1) {
										$recommended_title = POSITIVE_REVIEW_MSG;
										$recommended_class = "commend";
									} else if ($recommended == -1) {
										$recommended_title = NEGATIVE_REVIEW_MSG;
										$recommended_class = "discommend";
									} else {
										$recommended_title = "&nbsp;";
										$recommended_class = "neutral";
									}
									if ($reviews_pd_type != "1and1") {
										$recommended_title = "&nbsp;";
									}
					  
									$rating = round($db->f("rating"), 0);
									$rating_class = $rating ? "rating-" . $rating : "not-rated";
									$t->set_var("rating_class", $rating_class);
									$t->set_var("recommended_class", $recommended_class);
									$t->set_var("recommended_title", $recommended_title);
					  
					  
									$t->set_var("review_user_class", $review_user_class);
									$t->set_var("review_user_name", $review_user_name);
					  
									$date_added = $db->f("date_added", DATETIME);
									$date_added_string = va_date($article_date_format, $date_added);
									$t->set_var("review_date_added", $date_added_string);
									$t->set_var("review_summary", htmlspecialchars($db->f("summary")));
									$t->set_var("review_comments", nl2br(htmlspecialchars($db->f("comments"))));
              
									$t->parse("reviews", true);
								} while ($db->next_record());
							}
						}
					}

					if ($is_reviews) {
						$t->set_var("SEE_ALL_REVIEWS_MSG",  SEE_ALL_REVIEWS_MSG);
						$t->parse("all_reviews_link", false);
					}
				} else {
					$t->parse("not_rated", false);
				}
				$t->parse("reviews_block", false);
			}
		}

		$t->parse("article_item");
		$t->set_var("no_article_item", "");
	}

	if ($set_video_js) {
		set_script_tag("js/video.js");
	}

	$block_parsed = true;

?>