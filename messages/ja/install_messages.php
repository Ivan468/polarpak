<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  install_messages.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	// installation messages
	"INSTALL_TITLE" => "ViArt SHOP のインストール",

	"INSTALL_STEP_1_TITLE" => "インストール: ステップ 1",
	"INSTALL_STEP_1_DESC" => "ViArt SHOP をご利用いただきありがとうございます。 インストールを続けるには、以下に必要な情報をご記入ください。 選択したデータベースは既に存在している必要があります。 Microsoft Access などの ODBC を使用するデータベースをインストールする場合、インストールを続ける前に DNS を作成してください。",
	"INSTALL_STEP_2_TITLE" => "インストール: ステップ 2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "インストール: ステップ 3",
	"INSTALL_STEP_3_DESC" => "サイトのレイアウトを選択してください。 後でレイアウトを変更することもできます。",
	"INSTALL_FINAL_TITLE" => "インストール:最終ステップ",
	"SELECT_DATE_TITLE" => "日付フォーマットを選択",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "データベース設定",
	"DB_PROGRESS_MSG" => "データベースシステムのデータ入力を進める",
	"SELECT_PHP_LIB_MSG" => "PHP ライブラリーを選択",
	"SELECT_DB_TYPE_MSG" => "データベースの形式を選択",
	"ADMIN_SETTINGS_MSG" => "管理情報の設定",
	"DATE_SETTINGS_MSG" => "日付フォーマット",
	"NO_DATE_FORMATS_MSG" => "有効な日付フォーマットがありません",
	"INSTALL_FINISHED_MSG" => "インストールは完了しました。 管理セクションの設定を確認し、必要な変更を行ってください。",
	"ACCESS_ADMIN_MSG" => "管理セクションにアクセスするにはここをクリックしてください。",
	"ADMIN_URL_MSG" => "管理 URL",
	"MANUAL_URL_MSG" => "マニュアル URL",
	"THANKS_MSG" => "<b>ViArt SHOP</b> をご利用いただきありがとうございます。",

	"DB_TYPE_FIELD" => "データベースの形式",
	"DB_TYPE_DESC" => "現在、使用している <b>データベース タイプ</b> を選択してください。 SQL Server または Microsoft Access をご使用の場合は、 ODBC を選択してください",
	"DB_PHP_LIB_FIELD" => "PHP ライブラリ",
	"DB_HOST_FIELD" => "ホスト名",
	"DB_HOST_DESC" => "ViArt のデータベースを実行する <b>サーバーの名前</b> または <b>IP アドレス</b> を入力してください。 ローカル PC でデータベースを実行する場合、これを \"<b>localhost</b>\" のままでポートは空欄にすることができます。 ホスティング会社から提供されたデータベースを使用する場合、ホスティング会社のドキュメントのサーバー設定項目を参照してください。",
	"DB_PORT_FIELD" => "ポート",
	"DB_NAME_FIELD" => "データベース名または DSN 名",
	"DB_NAME_DESC" => "MySQL または PostgreSQL をご使用の場合、 ViArt のテーブルを作成したい <b>データベース名</b> を入力してください。 このデータベースは既に存在している必要があります。 テストを行うために ViArt をローカル PC にインストールする場合、システムにより \"<b>test</b>\" データベースが提供されます。 そうではない場合、 \"viart\" のようなデータベースを作成し、それを使用してください。 Microsoft Access または SQL Server を使用する場合、データベース名はコントロール パネルの ODBC セクションで設定した <b>DSN 名</b> です。",
	"DB_USER_FIELD" => "ユーザー名",
	"DB_PASS_FIELD" => "パスワード",
	"DB_USER_PASS_DESC" => "<b>ユーザー名/b>と<b>パスワード</b> - データベースにアクセスするためのユーザー名とパスワードを入力してください。 ローカルテストインストールをご利用の場合、大抵の場合ユーザー名は \"<b>root</b>\" 、パスワードは空白です。 テストでは問題ありませんが、本番のサーバーでは安全性に欠けるため、ご注意ください。",
	"DB_PERSISTENT_FIELD" => "持続接続",
	"DB_PERSISTENT_DESC" => "MySQL または Postgre の持続接続を使用する場合、このチェックボックスをチェックしてください。 わからない場合は、チェックをしないでください。",
	"DB_CREATE_DB_FIELD" => "DB 作成",
	"DB_CREATE_DB_DESC" => "データベースを作成するには、このチェックボックスをチェックしてください。 MySQL および Postgre のみに有効です。",
	"DB_POPULATE_FIELD" => "DB へデータ入力",
	"DB_POPULATE_DESC" => "データベース テーブル ストラクチャーを作成しデータを入力するにはチェックボックスをチェックしてください。",
	"DB_TEST_DATA_FIELD" => "テストデータ",
	"DB_TEST_DATA_DESC" => "テストデータをデータベースに追加するには、チェックボックスをチェックしてください。",
	"ADMIN_EMAIL_FIELD" => "管理者メールアドレス",
	"ADMIN_LOGIN_FIELD" => "管理者ログイン",
	"ADMIN_PASS_FIELD" => "管理者パスワード",
	"ADMIN_CONF_FIELD" => "パスワードの確認",
	"DATETIME_SHOWN_FIELD" => "日時フォーマット (サイト表示用)",
	"DATE_SHOWN_FIELD" => "日付フォーマット (サイト表示用)",
	"DATETIME_EDIT_FIELD" => "日時フォーマット (編集用)",
	"DATE_EDIT_FIELD" => "日付フォーマット (編集用)",
	"DATE_FORMAT_COLUMN" => "日付フォーマット",

	"DB_LIBRARY_ERROR" => "{db_library} 向けにPHP機能が定義されていません。 php.ini 設定ファイルのデータベース設定を確認してください。",
	"DB_CONNECT_ERROR" => "データベースに接続できません。 データベースパラメーターを確認してください。",
	"INSTALL_FINISHED_ERROR" => "インストールが完了しました。",
	"WRITE_FILE_ERROR" => "<b>'includes/var_definition.php'</b> ファイルに書き込み権限がありません。 ファイルのアクセス権限を変更してください。",
	"WRITE_DIR_ERROR" => "<b>'includes/'</b> フォルダに書き込み権限がありません。 フォルダのアクセス権限を変更してください。",
	"DUMP_FILE_ERROR" => "ダンプファイル '{file_name}' は見つかりませんでした。",
	"DB_TABLE_ERROR" => "{table_name}' テーブルは見つかりませんでした。 データーベースに必要なデータを入力してください。",
	"TEST_DATA_ERROR" => "テストデータでテーブルを作成する前に <b>{POPULATE_DB_FIELD}</b> を確認してください。",
	"DB_HOST_ERROR" => "指定されたホストネーム名は見つかりませんでした。",
	"DB_PORT_ERROR" => "指定されたポートでデータベースサーバーに接続できません。",
	"DB_USER_PASS_ERROR" => "指定されたパスワードまたはユーザー名は正しくありません。",
	"DB_NAME_ERROR" => "ログイン設定は正常でしたが、 '{db_name}' データベースは見つかりませんでした。",

	// upgrade messages
	"UPGRADE_TITLE" => "ViArt ショップアップグレード",
	"UPGRADE_NOTE" => "注意 : 先へ進む前にデータベースのバックアップをとってください。",
	"UPGRADE_AVAILABLE_MSG" => "データベースのアップグレードが可能です",
	"UPGRADE_BUTTON" => "今すぐにデータベースを {version_number} にアップグレード",
	"CURRENT_VERSION_MSG" => "現在インストールされているバージョン",
	"LATEST_VERSION_MSG" => "インストール可能なバージョン",
	"UPGRADE_RESULTS_MSG" => "アップグレードの結果",
	"SQL_SUCCESS_MSG" => "SQL クエリーに成功しました",
	"SQL_FAILED_MSG" => "SQL クエリーに失敗しました",
	"SQL_TOTAL_MSG" => "実行された総 SQＬ クエリー数",
	"VERSION_UPGRADED_MSG" => "データベースは次のバージョンにアップグレードされました:",
	"ALREADY_LATEST_MSG" => "最新バージョンになっています",
	"DOWNLOAD_NEW_MSG" => "最新バージョンが検出されました",
	"DOWNLOAD_NOW_MSG" => "今すぐにバージョン {version_number} をダウンロード",
	"DOWNLOAD_FOUND_MSG" => "新しいバージョン {version_number} をダウンロードすることができます。 ダウンロードを開始するには下のリンクをクリックしてください。 ダウンロードを完了後、アップグレードルーチンを再起動してください。",
	"NO_XML_CONNECTION" => "警告! 'http://www.viart.com/' に接続できません。",

	"END_USER_LICENSE_AGREEMENT_MSG" => "End User License Agreement",
	"AGREE_LICENSE_AGREEMENT_MSG" => "I have read and agree to the License Agreement",
	"READ_LICENSE_AGREEMENT_MSG" => "Click here to read license agreement",
	"LICENSE_AGREEMENT_ERROR" => "Please read and agree to the License Agreement before proceeding.",

);
$va_messages = array_merge($va_messages, $messages);
