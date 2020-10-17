<?php

	$default_title = "{MY_PLAYLIST_MSG}: {SONGS_MSG}";

	// if there was set some predefined playlist then it could be accessed by any user 
	//check_user_security("my_playlists");

	include_once("./includes/record.php");
	include_once("./includes/articles_functions.php");
	set_script_tag("js/ajax.js");
	set_script_tag("js/video.js");

	// friendly url settings
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	// get user type settings
	$eol = get_eol();
	$user_id = get_session("session_user_id");
	$user_info = get_session("session_user_info");
	$user_type_id = get_setting_value($user_info, "user_type_id", "");

	$html_template = get_setting_value($block, "html_template", "block_user_playlist_songs.html"); 
	$t->set_file("block_body", $html_template);
	$t->set_var("site_url",        $settings["site_url"]);
	$t->set_var("user_home_href",  "user_home.php");
	$t->set_var("user_playlist_href", "user_playlist.php");
	$t->set_var("user_playlists_href", "user_playlists.php");
	$t->set_var("user_playlist_edit_href", "user_playlist_edit.php");
	$t->set_var("user_playlist_songs_href", "user_playlist_songs.php");
	$t->set_var("ajax_songs_url", "ajax_songs.php");
	$t->set_var("ajax_songs_href", "ajax_songs.php");
	// clear blocks
	$t->set_var("playlist_search", "");
	$t->set_var("playlist_note", "");
	$t->set_var("playlist_songs", "");
	$t->set_var("list_template", "");
	$t->set_var("search_template", "");
	$t->set_var("play_on_load", "");

	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", va_constant("SONG_MSG"), CONFIRM_DELETE_MSG));

	$param_list_id = get_param("list_id");
	$custom_list_id = get_setting_value($vars, "list_id"); // get predfined list id if it was set for block
	$list_id = get_setting_value($vars, "list_id", $param_list_id); // get predfined list id if it was set for block
	$song_id = get_param("song_id");
	$favorite_id = get_param("favorite_id");
	$favorite_order = 0;
	$direction = get_param("direction");
	$direction_id = get_param("direction_id");
	$list_name = get_param("list_name");
	$operation = get_param("operation");
	$rp = get_param("rp");
	if (!$rp) { $rp = "user_playlists.php"; }
	$t->set_var("list_id", $list_id);

	if ($custom_list_id) {
		// if a custom list was selected then owner can't edit his list here
		$user_id = "";
		$user_info = "";
		$user_type_id = "";
	}

	// check if user try to edit his list and get it name
	$owner_user_id = "";
	if ($favorite_id) {
		$sql  = " SELECT fl.user_id, fa.article_id, fa.favorite_order, fl.list_id, fl.list_name ";
		$sql .= " FROM (".$table_prefix."favorite_articles fa  ";
		$sql .= " INNER JOIN ".$table_prefix."favorite_lists fl ON fa.list_id=fl.list_id) ";
		$sql .= " WHERE fa.favorite_id=".$db->tosql($favorite_id, INTEGER);
		//$sql .= " AND fa.user_id=".$db->tosql($user_id, INTEGER);
	} else {
		$sql  = " SELECT fl.user_id, fl.list_id, fl.list_name FROM ".$table_prefix."favorite_lists fl ";
		$sql .= " WHERE fl.list_id=".$db->tosql($list_id, INTEGER);
		//$sql .= " AND fl.user_id=".$db->tosql($user_id, INTEGER);
	}
	$db->query($sql);
	if ($db->next_record()) {
		$owner_user_id = $db->f("user_id");
		$favorite_order = $db->f("favorite_order");
		$list_id = $db->f("list_id");
		$list_name = $db->f("list_name");
		if ($favorite_id) {
			$song_id = $db->f("article_id");
		}
	} 
	if ($operation && $user_id != $owner_user_id) {
		if ($operation == "add") {
			$data = array("error" => "You can't add this song.");
			echo json_encode($data);
		} else if ($operation == "delete") {
			$data = array("error" => "You can't delete this song.", "favorite_id" => $favorite_id);
			echo json_encode($data);
		} else if ($operation == "move") {
			$data = array("error" => "You can't move this song.", "favorite_id" => $favorite_id);
			echo json_encode($data);
		} else {	
			header("Location: user_playlists.php");
		}
		exit;
	}

	if ($operation == "add") {
		// check and calculate next favorite order
		$sql  = " SELECT MAX(favorite_order) FROM ".$table_prefix."favorite_articles ";
		$sql .= " WHERE list_id=".$db->tosql($list_id, INTEGER);
		$db->query($sql);
		$db->next_record();
		$favorite_order = intval($db->f(0));
		$favorite_order++;

		$sql  = " SELECT full_description FROM ".$table_prefix."articles ";
		$sql .= " WHERE article_id=".$db->tosql($song_id, INTEGER);
		$song_lyrics = get_db_value($sql);

		$r = new VA_Record($table_prefix."favorite_articles");
		$r->add_textbox("article_id", INTEGER);
		$r->add_textbox("list_id", INTEGER);
		$r->add_textbox("user_id", INTEGER);
		$r->add_textbox("favorite_order", INTEGER);
		$r->add_textbox("play_type", INTEGER);
		$r->set_value("article_id", $song_id);
		$r->set_value("list_id", $list_id);
		$r->set_value("user_id", $user_id);
		$r->set_value("favorite_order", $favorite_order);
		$r->set_value("play_type", 1); // play by default
		$favorite_added = $r->insert_record();
		if ($favorite_added) {
			$favorite_id = $db->last_insert_id();
			$data = array("result" => "added", "favorite_id" => $favorite_id, "song_id" => $song_id, "song_lyrics" => $song_lyrics, "list_id" => $list_id);
		} else {
			$data = array("result" => "error", "error" => "Song wasn't added to your favorite list.");
		}
		echo json_encode($data);
		exit;
	} else if ($operation == "delete") {
		$favorite_id = get_param("favorite_id");
		$sql  = " DELETE FROM ".$table_prefix."favorite_articles ";
		$sql .= " WHERE favorite_id=".$db->tosql($favorite_id, INTEGER);
		$sql .= " AND user_id=".$db->tosql($user_id, INTEGER);
		$favorite_deleted = $db->query($sql);
		if ($favorite_deleted) {
			// update order for articles 
			$range_sql  = " UPDATE ".$table_prefix."favorite_articles ";
			$range_sql .= " SET favorite_order=favorite_order-1 ";
			$range_sql .= " WHERE list_id=".$db->tosql($list_id, INTEGER);
			$range_sql .= " AND favorite_order>".$db->tosql($favorite_order, INTEGER);
			$db->query($range_sql);

			$data = array("result" => "deleted", "favorite_id" => $favorite_id, "song_id" => $song_id, "list_id" => $list_id);
		} else {
			$data = array("result" => "error", "error" => "Song wasn't removed from your favorite list.", "favorite_id" => $favorite_id);
		}
		echo json_encode($data);
		exit;
	} else if ($operation == "move") {
		$direction_order = 0;
		if ($direction_id) {
			$sql  = " SELECT fa.article_id, fa.favorite_order ";
			$sql .= " FROM ".$table_prefix."favorite_articles fa  ";
			$sql .= " WHERE fa.favorite_id=".$db->tosql($direction_id, INTEGER);
			$sql .= " AND fa.user_id=".$db->tosql($user_id, INTEGER);
			$sql .= " AND fa.list_id=".$db->tosql($list_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$direction_order = $db->f("favorite_order");
			}
		}
		$favorite_moved = false;
		if ($favorite_order && $direction_order && $favorite_order != $direction_order) {
			$range_sql = "";
			if ($direction == "after" || $direction == "below") {
				if ($direction_order > $favorite_order) {
					$new_order = $direction_order;
					$range_sql  = " UPDATE ".$table_prefix."favorite_articles ";
					$range_sql .= " SET favorite_order=favorite_order-1 ";
					$range_sql .= " WHERE list_id=".$db->tosql($list_id, INTEGER);
					$range_sql .= " AND favorite_order>".$db->tosql($favorite_order, INTEGER);
					$range_sql .= " AND favorite_order<=".$db->tosql($direction_order, INTEGER);
				} else { 
					$new_order = $direction_order + 1;
					$range_sql  = " UPDATE ".$table_prefix."favorite_articles ";
					$range_sql .= " SET favorite_order=favorite_order+1 ";
					$range_sql .= " WHERE list_id=".$db->tosql($list_id, INTEGER);
					$range_sql .= " AND favorite_order>".$db->tosql($direction_order, INTEGER);
					$range_sql .= " AND favorite_order<".$db->tosql($favorite_order, INTEGER);
				}
			} else {
				if ($direction_order < $favorite_order) {
					$new_order = $direction_order;
					$range_sql  = " UPDATE ".$table_prefix."favorite_articles ";
					$range_sql .= " SET favorite_order=favorite_order+1 ";
					$range_sql .= " WHERE list_id=".$db->tosql($list_id, INTEGER);
					$range_sql .= " AND favorite_order>=".$db->tosql($direction_order, INTEGER);
					$range_sql .= " AND favorite_order<".$db->tosql($favorite_order, INTEGER);
				} else { 
					$new_order = $direction_order - 1;
					$range_sql  = " UPDATE ".$table_prefix."favorite_articles ";
					$range_sql .= " SET favorite_order=favorite_order-1 ";
					$range_sql .= " WHERE list_id=".$db->tosql($list_id, INTEGER);
					$range_sql .= " AND favorite_order>".$db->tosql($favorite_order, INTEGER);
					$range_sql .= " AND favorite_order<".$db->tosql($direction_order, INTEGER);
				}
			}
			// update order for songs within updated range
			$db->query($range_sql);
			// set new order for selected song
			$sql  = " UPDATE ".$table_prefix."favorite_articles ";
			$sql .= " SET favorite_order=".$db->tosql($new_order, INTEGER);
			$sql .= " WHERE favorite_id=".$db->tosql($favorite_id, INTEGER);
			$favorite_moved = $db->query($sql);
		}

		if ($favorite_moved) {
			$data = array(
				"result" => "moved", "favorite_id" => $favorite_id, "song_id" => $song_id, "list_id" => $list_id,
				"direction" => $direction, "direction_id" => $direction_id, 
			);
		} else {
			$data = array("result" => "error", "error" => "Song wasn't moved.", "favorite_id" => $favorite_id);
		}
		echo json_encode($data);
		exit;
	}

	$t->set_var("list_name", htmlspecialchars($list_name));
	if ($user_id == $owner_user_id) {
		$t->set_var("playlist_mode_class", "owner-mode");
		$t->sparse("playlist_search", false);
		$t->sparse("playlist_note", false);
	} else {
		$default_title = "";
		$t->set_var("playlist_mode_class", "guest-mode");
	}


	// get playlist songs
	$songs = array();
	$sql  = " SELECT fa.favorite_id, fa.favorite_name, fa.play_type, ";
	$sql .= " a.article_id, a.article_title, a.full_description, a.youtube_video, a.friendly_url, aut.author_id, aut.author_name, arol.role_code ";
	$sql .= " FROM ((((".$table_prefix."favorite_articles fa ";
	$sql .= " INNER JOIN " . $table_prefix ."articles a ON a.article_id=fa.article_id) ";
	$sql .= " LEFT JOIN " . $table_prefix ."articles_authors aaut ON aaut.article_id=a.article_id) ";
	$sql .= " LEFT JOIN " . $table_prefix ."authors aut ON aut.author_id=aaut.author_id) ";
	$sql .= " LEFT JOIN " . $table_prefix ."authors_roles arol ON aaut.role_id=arol.role_id) ";
	//$sql .= " LEFT JOIN " . $table_prefix . "authors_sites auts ON auts.author_id=aaut.author_id ";	
	//$sql .= " LEFT JOIN " . $table_prefix . "articles_statuses st ON a.status_id=st.status_id ";
	$sql .= " WHERE fa.list_id=". $db->tosql($list_id, INTEGER);
	$sql .= " ORDER BY fa.favorite_order, fa.favorite_id ";
	$db->query($sql);
	while ($db->next_record()) {
		$favorite_id = $db->f("favorite_id");
		$article_id = $db->f("article_id");
		$article_title = $db->f("article_title");
		$song_lyrics = $db->f("full_description");
		$youtube_video = $db->f("youtube_video");
		$favorite_name = $db->f("favorite_name");
		$friendly_url = $db->f("friendly_url");
		$play_type = $db->f("play_type");
		$author_id = $db->f("author_id");
		$author_name = $db->f("author_name");
		$role_code = $db->f("role_code");
		if ($friendly_urls && $friendly_url) {
			$song_url = $friendly_url . $friendly_extension;
		} else {
			$song_url = "article.php?article_id=" . $article_id;
		}

		if (!isset($songs[$favorite_id])) {
			$songs[$favorite_id] = array(
				"favorite_id" => $favorite_id,
				"article_id" => $article_id,
				"article_title" => $article_title,
				"song_lyrics" => $song_lyrics,
				"youtube_video" => $youtube_video,
				"song_name" => $favorite_name,
				"favorite_name" => $favorite_name,
				"song_url" => $song_url,
				"play_type" => $play_type,
				"authors" => array(),
			);
		}

		$songs[$favorite_id]["authors"][$author_id] = array(
			"name" => $author_name, "role" => $role_code,
		);
	}

	foreach ($songs as $favorite_id => $song_data) {

		$song_data = VA_Articles::article_authors($song_data);
		$article_id = $song_data["article_id"];
		$favorite_name = $song_data["favorite_name"];
		$article_title = $song_data["article_title"];
		$song_url = $song_data["song_url"];
		$song_lyrics = $song_data["song_lyrics"];
		$youtube_video = $song_data["youtube_video"];
		$play_type = $song_data["play_type"]; // 0 - ignore playing record 1 - default playing accordingly to playlist
		if (!$favorite_name) {
			$favorite_name = $article_title;
			$authors_names = $song_data["authors_names"];
			$authors_featured = $song_data["authors_featured"];
			if ($authors_names) { $favorite_name = $authors_names." - ".$favorite_name; }
			if ($authors_featured) { $favorite_name .= " ft. ".$authors_featured; }
		}

		$t->set_var("article_id", htmlspecialchars($article_id));
		$t->set_var("song_id", htmlspecialchars($article_id));
		$t->set_var("song_name", htmlspecialchars($favorite_name));
		$t->set_var("song_lyrics", $song_lyrics);
		$t->set_var("youtube_video", htmlspecialchars($youtube_video));
		$t->set_var("favorite_id", htmlspecialchars($favorite_id));
		$t->set_var("favorite_name", htmlspecialchars($favorite_name));
		$t->set_var("song_url", htmlspecialchars($song_url));
		$t->parse_to("list_template", "playlist_songs", true);
	}

	// parse list template
	$t->set_var("favorite_id", "[favorite_id]");
	$t->set_var("song_id", "[song_id]");
	$t->set_var("song_name", "[song_name]");
	$t->set_var("song_lyrics", "[song_lyrics]");
	$t->set_var("youtube_video", "[youtube_video]");
	$t->parse("list_template", true);

	// parse search template
	$t->parse("search_template", true);

	if (strtolower($operation) == "play") {
		$t->sparse("play_on_load", true);
	}

	$block_parsed = true;


/*
CREATE TABLE va_favorite_lists (
        `list_id` INT(11) NOT NULL AUTO_INCREMENT,
        `user_id` INT(11) default '0',
        `list_type` TINYINT default '0', // 0 - probably general favorite list, 1 - favorite playlist
        `list_order` INT(11) default '1',
        `list_name` VARCHAR(255),
        `list_desc` TEXT,
        `play_type` TINYINT, // 0 - don't play the  list 1 - default play the list
        `lyrics_mode` TINYINT, // 0 - don't show lyrics, 1 - show lyrics
        `shuffle_type` TINYINT, // 0 - no shuffle 1 - always shuffle when start playing playlist
        `end_action` TINYINT // 0 - stop playing 1 - rewind to first record 2 - go to next playlist
        ,PRIMARY KEY (list_id)
        ) DEFAULT CHARACTER SET=utf8 

CREATE TABLE va_favorite_articles (
        `favorite_id` INT(11) NOT NULL AUTO_INCREMENT,
        `article_id` INT(11) default '0',
        `list_id` INT(11) default '0',
        `user_id` INT(11) default '0',
        `favorite_order` INT(11) default '1',
        `favorite_name` VARCHAR(255),
        `play_type` TINYINT default '0' // 0 - ignore playing record 1 - default playing accordingly to playlist
        ,PRIMARY KEY (favorite_id)
        ) DEFAULT CHARACTER SET=utf8 
*/

