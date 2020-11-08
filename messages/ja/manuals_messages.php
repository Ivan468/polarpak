<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  manuals_messages.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	// manuals messages
	"MANUALS_TITLE" => "マニュアル",
	"NO_MANUALS_MSG" => "マニュアルは見つかりません",
	"NO_MANUAL_ARTICLES_MSG" => "記事はありません",
	"MANUALS_PREV_ARTICLE" => "戻る",
	"MANUALS_NEXT_ARTICLE" => "次へ",
	"MANUAL_CONTENT_MSG" => "索引",
	"MANUALS_SEARCH_IN_MSG" => "検索項目",
	"MANUALS_SEARCH_FOR_MSG" => "検索",
	"MANUALS_SEARCH_IN_FIRST_VARIANT" => "すべてのマニュアル",
	"MANUALS_SEARCH_RESULTS_INFO" => "{search_string} に一致する記事が {results_number} つ見つかりました。",
	"MANUALS_SEARCH_RESULT_MSG" => "検索結果",
	"MANUALS_NOT_FOUND_ANYTHING" => "{search_string}' に一致する記事は見つかりませんでした。",
	"MANUAL_ARTICLE_NO_CONTENT_MSG" => "コンテンツはありません",
	"MANUALS_SEARCH_TITLE" => "マニュアル検索",
	"MANUALS_SEARCH_RESULTS_TITLE" => "マニュアル検索結果",

);
$va_messages = array_merge($va_messages, $messages);
