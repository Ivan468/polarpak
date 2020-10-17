<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  download_messages.php                                    ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	// download messages
	"DOWNLOAD_WRONG_PARAM" => "ダウンロード パラメーターが不正です。",
	"DOWNLOAD_MISS_PARAM" => "ダウンロードのパラメーターがありません。",
	"DOWNLOAD_INACTIVE" => "ダウンロードは無効です。",
	"DOWNLOAD_EXPIRED" => "このダウンロードは期限が切れています。",
	"DOWNLOAD_LIMITED" => "最大ダウンロード数を超えています。",
	"DOWNLOAD_PATH_ERROR" => "製品へのパスが見つかりません。",
	"DOWNLOAD_RELEASE_ERROR" => "リリースはありませんでした。",
	"DOWNLOAD_USER_ERROR" => "登録ユーザの方のみ、このファイルをダウンロードすることができます。",
	"ACTIVATION_OPTIONS_MSG" => "アクティベート オプション",
	"ACTIVATION_MAX_NUMBER_MSG" => "最大アクティペーション回数",
	"DOWNLOAD_OPTIONS_MSG" => "ダウンロード製品/ソフトウェア オプション",
	"DOWNLOADABLE_MSG" => "ダウンロード製品 (ソフトウェア)",
	"DOWNLOADABLE_DESC" => "ダウンロード製品には、'ダウンロード期間'、'ダウンロードファイルへのパス'、および 'アクティベーションオプション' を指定することもできます。",
	"DOWNLOAD_PERIOD_MSG" => "ダウンロード期間",
	"DOWNLOAD_PATH_MSG" => "ダウンロードファイルへのパス",
	"DOWNLOAD_PATH_DESC" => "セミコロンで区切られた複数のパスを追加することができます。",
	"UPLOAD_SELECT_MSG" => "アップロードするファイルを選択し {button_name} ボタンをクリックしてください。",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "ファイル <b>{filename}</b> がアップロードされました。",
	"UPLOAD_SELECT_ERROR" => "ファイルを最初に選択してください。",
	"UPLOAD_IMAGE_ERROR" => "画像ファイルのみ許可されています。",
	"UPLOAD_FORMAT_ERROR" => "この形式のファイルは許可されていません。",
	"UPLOAD_SIZE_ERROR" => "{filesize} より大きいファイルは許可されていません。",
	"UPLOAD_DIMENSION_ERROR" => "{filesize} より小さいファイルは許可されていません。",
	"UPLOAD_CREATE_ERROR" => "システムによりファイルを作成できません",
	"UPLOAD_ACCESS_ERROR" => "ファイルをアップロードする権限を持っていません",
	"DELETE_FILE_CONFIRM_MSG" => "このファイルを削除してもよろしいですか？",
	"NO_FILES_MSG" => "ファイルは見つかりませんでした。",
	"SERIAL_GENERATE_MSG" => "シリアル番号の生成",
	"SERIAL_DONT_GENERATE_MSG" => "生成しません",
	"SERIAL_RANDOM_GENERATE_MSG" => "ソフトウェア製品用にランダムシリアル番号を生成",
	"SERIAL_FROM_PREDEFINED_MSG" => "事前に定義されたリストからシリアル番号を獲得",
	"SERIAL_PREDEFINED_MSG" => "事前に定義されたシリアル番号",
	"SERIAL_NUMBER_COLUMN" => "シリアル番号",
	"SERIAL_USED_COLUMN" => "使用済み",
	"SERIAL_DELETE_COLUMN" => "削除",
	"SERIAL_MORE_MSG" => "更にシリアル番号を追加しますか？",
	"SERIAL_PERIOD_MSG" => "シリアル番号の有効期限",
	"DOWNLOAD_SHOW_TERMS_MSG" => "ご利用規約を表示",
	"DOWNLOAD_SHOW_TERMS_DESC" => "製品をダウンロードするには、ユーザーはご利用規約を読み同意する必要があります。",
	"DOWNLOAD_TERMS_USER_ERROR" => "製品をダウンロードするには、ご利用規約を読み同意してください。",

	"DOWNLOAD_TITLE_MSG" => "Download Title",
	"DOWNLOADABLE_FILES_MSG" => "Downloadable Files",
	"DOWNLOAD_INTERVAL_MSG" => "Download Interval",
	"DOWNLOAD_LIMIT_MSG" => "Downloads Limit",
	"DOWNLOAD_LIMIT_DESC" => "number of times file can be downloaded",
	"MAXIMUM_DOWNLOADS_MSG" => "Maximum Downloads",
	"PREVIEW_TYPE_MSG" => "Preview Type",
	"PREVIEW_TITLE_MSG" => "Preview Title",
	"PREVIEW_PATH_MSG" => "Path to Preview File",
	"PREVIEW_IMAGE_MSG" => "Preview Image",
	"MORE_FILES_MSG" => "More Files",
	"UPLOAD_MSG" => "Upload",
	"USE_WITH_OPTIONS_MSG" => "Use with options only",
	"PREVIEW_AS_DOWNLOAD_MSG" => "Preview as download",
	"PREVIEW_USE_PLAYER_MSG" => "Use player to preview",
	"PROD_PREVIEWS_MSG" => "Previews",

);
$va_messages = array_merge($va_messages, $messages);
