<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  ajax_songs.php                                           ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/articles_functions.php");

	$sw = get_param("sw");
	$_GET["s_tit"] = 1;
	$_GET["s_aut"] = 1;
	$_GET["s_alb"] = 1;

	VA_Articles::keywords_sql($sw, $kw_no_records, $kw_rank, $kw_join, $kw_where);

	$sql_params = array();
	$sql_params["authors"] = true;
	$sql_params["albums"] = true;
	$sql_params["roles"] = true;
	$sql_params["site_ids"] = "4,5"; // search through music sites
	$sql_params["select"][] = "a.article_id, a.article_title, a.youtube_video, aut.author_id, aut.author_name, arol.role_code ";
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
		$youtube_video = $db->f("youtube_video");
		$author_id = $db->f("author_id");
		$author_name = $db->f("author_name");
		$role_code = $db->f("role_code");
		if (!isset($songs[$article_id])) {
			$songs[$article_id] = array(
				"song_name" => $article_title,
				"youtube_video" => $youtube_video,
				"authors" => array(),
			);
		}

		$songs[$article_id]["authors"][$author_id] = array(
			"name" => $author_name, "role" => $role_code,
		);
	}


	foreach ($songs as $song_id => $song_data) {
		$song_authors = $song_data["authors"];
		$authors_first = ""; $authors_default = ""; $authors_hidden = ""; $authors_featured = "";
		foreach ($song_authors as $author_data)  {
			$author_name = $author_data["name"];
			$role_code = $author_data["role"]; 
			if ($role_code == "first") {
				if (strlen($authors_first)) { $authors_first .= " & "; }
				$authors_first .= $author_name;
			} else if ($role_code == "ft" || $role_code == "feat" || $role_code == "featured") {
				if (strlen($authors_featured)) { $authors_featured .= " & "; }
				$authors_featured .= $author_name;
			} else if ($role_code == "hide" || $role_code == "hidden") {
				if (strlen($authors_hidden)) { $authors_hidden .= " & "; }
				$authors_hidden .= $author_name;
			} else {
				if (strlen($authors_default)) { $authors_default .= " & "; }
				$authors_default .= $author_name;
			}
		}
		$authors_names = $authors_first;
		if ($authors_names && $authors_default) { $authors_names .= " & "; }
		$authors_names .= $authors_default;

		$songs[$song_id]["authors_names"] = $authors_names;
		$songs[$song_id]["authors_featured"] = $authors_featured;
		unset($songs[$song_id]["authors"]);
	}


	echo json_encode(array("sw" => $sw, "where" => $kw_where, "found" => count($songs), "songs" => $songs));

