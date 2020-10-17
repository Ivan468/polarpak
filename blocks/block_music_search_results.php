<?php

	include_once("./includes/articles_functions.php");

	$default_title = "{SEARCH_TITLE}";
	$html_template = get_setting_value($block, "html_template", "block_music_search_results.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("ms_items", "");

	$site_url = get_setting_value($settings, "site_url", "");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	$records_per_page = get_setting_value($vars, "ms_recs", 100); // TODO: add this option

	$user_id = get_session("session_user_id");		
	$user_type_id = get_session("session_user_type_id");
	$sq = trim(get_param("sq"));
	$sw = trim(get_param("sw"));
	if (!$sw) { $sw = $sq; }
	if (!$sq) { $sq = $sw; }

	$_GET["s_tit"] = 1;
	$_GET["s_aut"] = 1;
	$_GET["s_alb"] = 1;

	VA_Articles::keywords_sql($sw, $kw_no_records, $kw_rank, $kw_join, $kw_where);

	$sql_params = array();
	$sql_params["authors"] = true;
	$sql_params["albums"] = true;
	$sql_params["roles"] = true;
	$sql_params["select"][] = "a.article_id, a.article_title, a.friendly_url, a.youtube_video ";
	$sql_params["select"][] = "aut.author_id, aut.author_name, aut.friendly_url as author_friendfly_url, aut.image_tiny, arol.role_code ";
	$sql_params["where"][] = $kw_where;
	//$sql_params["group"][] = "a.article_id";

	$keywords_search = false;
	if ($keywords_search) {
		$sql_params["join"][] = $kw_join;
		$sql_params["select"][] .= $kw_rank . " AS keywords_rank";
		$sql_params["order"][] .= "keywords_rank DESC";
	}

	$songs = array();
	$sql = VA_Articles::sql($sql_params, VIEW_CATEGORIES_ITEMS_PERM);
	$db->RecordsPerPage = 100;
	$db->PageNumber = 1;
	$db->query($sql);
	while ($db->next_record()) {
		$article_id = $db->f("article_id");
		$article_title = $db->f("article_title");
		$friendly_url = $db->f("friendly_url");
		$youtube_video = $db->f("youtube_video");
		$author_id = $db->f("author_id");
		$author_name = $db->f("author_name");
		$author_friendfly_url = $db->f("author_friendfly_url");
		$author_photo = $db->f("image_tiny");

		if ($friendly_urls && $friendly_url) {
			$song_url = $friendly_url.$friendly_extension;
		} else {
			$song_url = "article.php?article_id=" . $article_id;
		}

		$role_code = $db->f("role_code");
		if (!isset($songs[$article_id])) {
			$songs[$article_id] = array(
				"song_name" => $article_title,
				"song_url" => $song_url,
				"youtube_video" => $youtube_video,
				"authors" => array(),
			);
		}
		if ($role_code != "hide" && $role_code != "hidden") {
			if ($role_code == "first") {
				$author_order = 1;
			} else if ($role_code == "ft" || $role_code == "feat" || $role_code == "featured") {
				$author_name = "ft. ".$author_name;
				$author_order = 3;
			} else {
				$author_order = 2;
			}
			if ($friendly_urls && $author_friendfly_url) {
				$author_url = $author_friendfly_url . $friendly_extension;
			} else {
				$author_url = "author.php?author_id=" . $author_id;
			}
			$songs[$article_id]["authors_order"][$author_id] = $author_order;
			$songs[$article_id]["authors"][$author_id] = array(
				"name" => $author_name, "url" => $author_url, "photo" => $author_photo, "role" => $role_code,
			);
		}
	}

	//echo json_encode(array("sw" => $sw, "where" => $kw_where, "found" => count($songs), "songs" => $songs));

	// parse search results
	$html_template = get_setting_value($block, "html_template", "block_music_search_results.html"); 
  $t->set_file("block_body", $html_template);

	$columns = get_setting_value($vars, "songs_cols", 3);

	// clear template block to parse new data
	$t->set_var("no_results", "");
	$t->set_var("songs_cols", "");
	$t->set_var("songs_rows", "");
	$t->set_var("columns_class", "cols-".$columns);

	if (count($songs) > 0) {
		$song_number = 0;
		foreach ($songs as $article_id => $song_data) {
			$song_number++;
			$song_name = $song_data["song_name"];
			$song_url = $song_data["song_url"];
			$authors = $song_data["authors"];
			$authors_order = $song_data["authors_order"];

			array_multisort($authors_order, $authors);

			// set vars
			$t->set_var("song_name", htmlspecialchars($song_name));
			$t->set_var("song_url", htmlspecialchars($song_url));

			// parse authors
			$t->set_var("song_authors", "");
			$t->set_var("image_tiny_block", "");
			$song_author_name = ""; $song_author_photo = ""; $song_author_url = ""; // main song author
			foreach ($authors as $author_id => $author_data) {
				$author_name = $author_data["name"];
				$author_url = $author_data["url"];
				$author_photo = $author_data["photo"];
				$author_role = $author_data["role"];
				if (!$song_author_url) {
					$song_author_name = $author_name;
					$song_author_url = $author_url;
					$song_author_photo = $author_photo;
				}
				$t->set_var("author_name", htmlspecialchars($author_name));
				$t->set_var("author_url", htmlspecialchars($author_url));
				$t->sparse("song_authors", true);
			}
			// set main author name
			$t->set_var("author_name", htmlspecialchars($song_author_name));
			$t->set_var("author_url", htmlspecialchars($song_author_url));
			if ($song_author_photo) {
				$t->set_var("image_tiny_src", htmlspecialchars($song_author_photo));
				$t->set_var("image_tiny_alt", htmlspecialchars($song_author_name));
				$t->sparse("image_tiny_block", false);
			}

			$column_index = ($song_number % $columns) ? ($song_number % $columns) : $columns;
			$t->set_var("column_class", "col-".$column_index);
			$t->parse("songs_cols");
			$is_next_record = $db->next_record();
			if ($song_number % $columns == 0)
			{
				$t->parse("songs_rows");
				$t->set_var("songs_cols", "");
			}
		}

		if ($song_number % $columns != 0) {
			$t->parse("songs_rows");
		}
	} else {
		$t->parse("no_results", false);
	}
	$block_parsed = true;

/*
	<!-- begin no_results -->
	<div class="messagebg">We can't find any songs matching your criterion - <b>{sw}</b>.</div>
	<!-- end no_results -->

	<div class="songs {columns_class}">
		<!-- BEGIN songs_rows -->
		<!-- BEGIN songs_cols -->
		<div class="col {column_class}">

			<div class="article">
				<div class="author-photo">
					<!-- begin image_tiny_block -->
					<a class="link img-tiny" href="{author_url}" title="{author_name} Lyrics"><img class="image img-tiny" src="{image_tiny_src}" alt="{image_tiny_alt}" /></a><!-- end image_tiny_block -->
				</div>

				<a class="link song-name" href="{song_url}" title="{song_name} Lyrics">{song_name}</a><br/>
				by <a class="link author-name" href="{author_url}" title="{author_name} Lyrics">{author_name}</a>

			</div>

		</div>
		<!-- END songs_cols -->
		<!-- END songs_rows -->
		<div class="clear"></div>
	</div>


*/