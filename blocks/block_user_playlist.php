<?php

	$default_title = "{MY_PLAYLIST_MSG}: {EDIT_MSG}";

	check_user_security("my_playlists");

	include_once("./includes/record.php");

	// get user type settings
	$eol = get_eol();
	$user_id = get_session("session_user_id");
	$user_info = get_session("session_user_info");
	$user_type_id = get_setting_value($user_info, "user_type_id", "");

	$html_template = get_setting_value($block, "html_template", "block_user_playlist.html"); 
	$t->set_file("block_body", $html_template);
	$t->set_var("site_url",        $settings["site_url"]);
	$t->set_var("user_home_href",  "user_home.php");
	$t->set_var("user_playlist_href", "user_playlist.php");
	$t->set_var("user_playlists_href", "user_playlists.php");

	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", PLAYLIST_MSG, CONFIRM_DELETE_MSG));

	$list_id = get_param("list_id");
	$operation = get_param("operation");
	$rp = get_param("rp");
	if (!$rp) { $rp = "user_playlists.php"; }
	$t->set_var("list_id", $list_id);

	$r = new VA_Record($table_prefix . "favorite_lists");
	$r->return_page = $rp;

	// set up html form parameters
	$r->add_hidden("rp", TEXT);
	$r->add_where("list_id", INTEGER);
	$r->add_textbox("user_id", INTEGER);
	$r->change_property("user_id", USE_IN_INSERT, true);
	$r->change_property("user_id", USE_IN_UPDATE, false);

	$r->add_textbox("list_type", INTEGER); // 0 - general favorite list, 1 - favorite playlist
	$r->change_property("list_type", USE_IN_INSERT, true);
	$r->change_property("list_type", USE_IN_UPDATE, false);

	$r->add_textbox("list_order", INTEGER); 
	$r->change_property("list_order", USE_IN_INSERT, true);
	$r->change_property("list_order", USE_IN_UPDATE, false);

	$r->add_textbox("list_name", TEXT); 
	$r->change_property("list_name", REQUIRED, true);
	$r->add_textbox("list_desc", TEXT); 

	$play_types = array(array("0", va_constant("DONT_PLAY_MSG")), array("1", va_constant("PLAY_THE_LIST_MSG")));
	$lyrics_modes = array(array("0", va_constant("DONT_SHOW_MSG")), array("1", va_constant("SHOW_THE_LYRICS_MSG")));
	$shuffle_types = array(array("0", va_constant("NO_SHUFFLE_MSG")), array("1", va_constant("SHUFFLE_ON_START_MSG")));
	$end_actions = array(array("0", va_constant("STOP_PLAYING_MSG")), array("1", va_constant("REWIND_TO_FIRST_MSG")), array("2", va_constant("GOTO_NEXT_PLAYLIST_MSG")));

	$r->add_radio("play_type", INTEGER, $play_types); // 0 - don't play the  list 1 - default play the list
	$r->change_property("play_type", SHOW, false);
	$r->add_radio("lyrics_mode", INTEGER, $lyrics_modes); // 0 - don't show lyrics, 1 - show lyrics
	$r->change_property("lyrics_mode", SHOW, false);
	$r->add_radio("shuffle_type", INTEGER, $shuffle_types); // 0 - no shuffle 1 - always shuffle when start playing playlist
	$r->change_property("shuffle_type", SHOW, false);
	$r->add_radio("end_action", INTEGER, $end_actions); // 0 - stop playing 1 - rewind to first record 2 - go to next playlist
	$r->change_property("end_action", SHOW, false);

	$r->set_event(BEFORE_DELETE, "set_additional_where");
	$r->set_event(BEFORE_UPDATE, "set_additional_where");
	$r->set_event(BEFORE_SELECT, "set_additional_where");

	if (!$list_id) {
		// automatically create a new playlist and redirect user 
		// get an order for new playlist
		$sql  = " SELECT MAX(list_order) FROM ".$table_prefix."favorite_lists ";
		$sql .= " WHERE user_id=".$db->tosql($user_id, INTEGER);
		$list_order = get_db_value($sql) + 1;

		$r->set_value("user_id", get_session("session_user_id"));
		$r->set_value("list_type", 1);
		
		// set default values
		$r->set_value("list_order", $list_order);
		$r->set_value("list_name", va_constant("MY_PLAYLIST_MSG")." #".$list_order);

		$r->set_value("play_type", 1); // 0 - don't play the  list 1 - default play the list
		$r->set_value("lyrics_mode", 1); // 0 - don't show lyrics, 1 - show lyrics
		$r->set_value("shuffle_type", 0); // 0 - no shuffle 1 - always shuffle when start playing playlist
		$r->set_value("end_action", 2); // 0 - stop playing 1 - rewind to first record 2 - go to next playlist

		if ($db_type == "postgre") {
			$list_id = get_db_value(" SELECT NEXTVAL('seq_" . $table_prefix . "favorite_lists') ");
			$r->change_property("list_id", USE_IN_INSERT, true);
			$r->set_value("list_id", $list_id);
		}

		$list_added = $r->insert_record();
		if ($list_added) {
			if ($db_type == "mysql") {
				$list_id = get_db_value(" SELECT LAST_INSERT_ID() ");
			} elseif ($db_type == "access") {
				$list_id = get_db_value(" SELECT @@IDENTITY ");
			}
			$r->set_value("list_id", $list_id);
			header("Location: user_playlist.php?list_id=".urlencode($list_id));
			exit;
		} else {
			header("Location: user_playlists.php?error_code=1010");
			exit;
		}
	} else {

	}

	$r->process();

	$block_parsed = true;

	function set_additional_where()
	{
		global $r;
		$r->change_property("user_id", USE_IN_WHERE, true);
		$r->set_value("user_id", get_session("session_user_id"));
	}

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