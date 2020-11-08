<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  forum_messages.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	// forum messages
	"FORUM_TITLE" => "フォーラム",
	"TOPIC_INFO_TITLE" => "トピック情報",
	"TOPIC_MESSAGE_TITLE" => "メッセージ",

	"MY_FORUM_TOPICS_MSG" => "マイ フォーラム トピック",
	"ALL_FORUM_TOPICS_MSG" => "すべてのフォーラムトピック",
	"MY_FORUM_TOPICS_DESC" => "現在抱えている問題は、過去に他のユーザーも経験した問題ではないかと思ったことはないですか？ お客様の経験を新しいユーザーと共有したいですか？ フォーラムユーザーになってコミュニティに参加しませんか？",
	"NEW_TOPIC_MSG" => "新しいトピック",
	"NO_TOPICS_MSG" => "トピックはありません",
	"FOUND_TOPICS_MSG" => "<b>{search_string}</b>' と一致するトピックが <b>{found_records}</b> 件見つかりました。",
	"NO_FORUMS_MSG" => "フォーラムが見つかりません",

	"FORUM_NAME_COLUMN" => "フォーラム",
	"FORUM_TOPICS_COLUMN" => "トピック",
	"FORUM_REPLIES_COLUMN" => "返信",
	"FORUM_LAST_POST_COLUMN" => "最終更新",
	"FORUM_MODERATORS_MSG" => "管理人",

	"TOPIC_NAME_COLUMN" => "トピック",
	"TOPIC_AUTHOR_COLUMN" => "著者",
	"TOPIC_VIEWS_COLUMN" => "ビュー",
	"TOPIC_REPLIES_COLUMN" => "返信",
	"TOPIC_UPDATED_COLUMN" => "最終更新",
	"TOPIC_ADDED_MSG" => "ありがとうございます。<br>トピックが追加されました。",

	"TOPIC_ADDED_BY_FIELD" => "Added by",
	"TOPIC_ADDED_DATE_FIELD" => "追加日",
	"TOPIC_UPDATED_FIELD" => "最終更新",
	"TOPIC_NICKNAME_FIELD" => "ニックネーム",
	"TOPIC_EMAIL_FIELD" => "メールアドレス",
	"TOPIC_NAME_FIELD" => "トピック",
	"TOPIC_MESSAGE_FIELD" => "メッセージ",
	"TOPIC_NOTIFY_FIELD" => "すべての返信をメールで受信",

	"ADD_TOPIC_BUTTON" => "トピックを追加",
	"TOPIC_MESSAGE_BUTTON" => "メッセージを追加",

	"TOPIC_MISS_ID_ERROR" => "<b>スレッド ID</b> パラメーターがありません。",
	"TOPIC_WRONG_ID_ERROR" => "<b>スレッド ID</b> パラメーターに正しくない値が含まれています。",
	"FORUM_SEARCH_MESSAGE" => "{search_string}' に一致するメッセージが {search_count} 件見つかりました。",
	"TOPIC_PREVIEW_BUTTON" => "プレビュー",
	"TOPIC_SAVE_BUTTON" => "保存",

	"LAST_POST_ON_SHORT_MSG" => "日時:",
	"LAST_POST_IN_SHORT_MSG" => "場所:",
	"LAST_POST_BY_SHORT_MSG" => "作者:",
	"FORUM_MESSAGE_LAST_MODIFIED_MSG" => "最終変更:",

);
$va_messages = array_merge($va_messages, $messages);
