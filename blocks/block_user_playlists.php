<?php

	$default_title = "{MY_PLAYLISTS_MSG}";

	check_user_security("my_playlists");

	$user_id = get_session("session_user_id");
	$sw = trim(get_param("sw"));
	
	$html_template = get_setting_value($block, "html_template", "block_user_playlists.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("user_playlist_href",  "user_playlist.php");
	$t->set_var("user_playlists_href",   "user_playlists.php");
	$t->set_var("user_playlist_edit_href",  "user_playlist_edit.php");
	$t->set_var("user_playlist_songs_href", "user_playlist_songs.php");
	$t->set_var("user_home_href", "user_home.php");
	$t->set_var("sw", htmlspecialchars($sw));

	$s = new VA_Sorter($settings["templates_dir"], "sorter.html", "user_playlists.php");
	$s->set_default_sorting(1, "asc");
	$s->set_sorter(ID_MSG, "sorter_id", "1", "fl.list_id");
	$s->set_sorter(ORDER_MSG, "sorter_order", "2", "fl.list_order");
	$s->set_sorter(NAME_MSG, "sorter_name", "3", "fl.list_name");
	$s->set_sorter(TYPE_MSG, "sorter_type", "4", "fl.play_type");

	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", "user_playlists.php");

	$where = "";
	$sa = array();
	if ($sw) {
		$sa = explode(" ", $sw);
		for($si = 0; $si < sizeof($sa); $si++) {
			$where .= " AND (fl.list_name LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
			$where .= " OR fl.list_desc LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%')";
		}
	}

	// set up variables for navigator
	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "favorite_lists fl ";
	$sql .= " WHERE fl.user_id=" . $db->tosql($user_id, INTEGER);
	$sql .= " AND fl.list_type=1 "; // 1 - favorite playlist
	$sql .= $where;
	$db->query($sql);
	$db->next_record();
	$total_records = $db->f(0);

	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$user_lists = array(); $lists_ids = array();
	$sql  = " SELECT fl.* ";
	$sql .= "	FROM " . $table_prefix . "favorite_lists fl ";
	$sql .= " WHERE fl.user_id=" . $db->tosql($user_id, INTEGER);
	$sql .= " AND fl.list_type=1 "; // 1 - favorite playlist
	$sql .= $where;
	$sql .= $s->order_by;
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query($sql);
	while ($db->next_record()) {
		$list_id = $db->f("list_id");
		$user_lists[$list_id] = $db->Record;
		$user_lists[$list_id]["songs_number"] = 0;
		$lists_ids[] = $list_id;
	}

	// calculate number of songs in eash list
	if (count($lists_ids) > 0) {
		$sql  = " SELECT fa.list_id, COUNT(fa.article_id) AS songs_number ";
		$sql .= "	FROM " . $table_prefix . "favorite_articles fa ";
		$sql .= " WHERE fa.list_id IN (" . $db->tosql($lists_ids, INTEGERS_LIST).") ";
		$sql .= " GROUP BY fa.list_id ";
		$db->query($sql);
		while ($db->next_record()) {
			$list_id = $db->f("list_id");
			$songs_number = $db->f("songs_number");
			$user_lists[$list_id]["songs_number"] = $songs_number;
		}	
	}


	
	if (count($user_lists) > 0) {

		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		foreach ($user_lists as $list_id => $list_data) {

			// get data
			$list_order = $list_data["list_order"];
			$list_name = $list_data["list_name"];
			$list_desc = $list_data["list_desc"];
			$play_type = $list_data["play_type"]; // 0 - don't play the  list 1 - default play the list
			$lyrics_mode = $list_data["lyrics_mode"]; // 0 - don't show lyrics, 1 - show lyrics
			$shuffle_type = $list_data["shuffle_type"]; // 0 - no shuffle 1 - always shuffle when start playing playlist
			$end_action = $list_data["end_action"]; // 0 - stop playing 1 - rewind to first record 2 - go to next playlist
			$songs_number = $list_data["songs_number"];
			$active_type = ($play_type) ? YES_MSG : NO_MSG;

			$t->set_var("list_id", $list_id);
			$t->set_var("list_order", $list_order);
			$t->set_var("list_name", htmlspecialchars($list_name));
			$t->set_var("list_desc", htmlspecialchars($list_desc));
			$t->set_var("active_type", htmlspecialchars($active_type));
			$t->set_var("songs_number", intval($songs_number));

			$t->parse("records",true);
		
		}

	} else {
		$t->set_var("sorters", "");
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}
	
	$block_parsed = true;
