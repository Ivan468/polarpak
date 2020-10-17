<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  support_messages.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	// support messages
	"SUPPORT_TITLE" => "サポートセンター",
	"SUPPORT_REQUEST_INF_TITLE" => "情報をリクエスト",
	"SUPPORT_REPLY_FORM_TITLE" => "返信",
	"SUPPORT_SHORT_TITLE" => "Support",

	"MY_SUPPORT_ISSUES_MSG" => "サポート リクエスト",
	"MY_SUPPORT_ISSUES_DESC" => "購入された製品でご質問がございましたら、弊社サポートが対応いたします。上のリンクをクリックし、サポートへお問い合わせください。",
	"NEW_SUPPORT_REQUEST_MSG" => "新規リクエスト",
	"SUPPORT_REQUEST_ADDED_MSG" => "ありがとうございます。<br>サポートより回答いたしますので、しばらくお待ちください。",
	"SUPPORT_SELECT_DEP_MSG" => "部署を選択",
	"SUPPORT_SELECT_PROD_MSG" => "製品を選択",
	"SUPPORT_SELECT_STATUS_MSG" => "ステータスを選択",
	"SUPPORT_NOT_VIEWED_MSG" => "表示しない",
	"SUPPORT_VIEWED_BY_USER_MSG" => "お客様ごとに表示",
	"SUPPORT_VIEWED_BY_ADMIN_MSG" => "管理者ごとに表示",
	"SUPPORT_STATUS_NEW_MSG" => "新規",
	"NO_SUPPORT_REQUEST_MSG" => "問題はありません。",

	"SUPPORT_SUMMARY_COLUMN" => "概要",
	"SUPPORT_TYPE_COLUMN" => "タイプ",
	"SUPPORT_UPDATED_COLUMN" => "最終更新",

	"SUPPORT_USER_NAME_FIELD" => "名前",
	"SUPPORT_USER_EMAIL_FIELD" => "メールアドレス",
	"SUPPORT_IDENTIFIER_FIELD" => "確認番号 (請求書番号)",
	"SUPPORT_ENVIRONMENT_FIELD" => "環境 (OS、データベース、ウェブ サーバーなど)",
	"SUPPORT_DEPARTMENT_FIELD" => "部署",
	"SUPPORT_PRODUCT_FIELD" => "商品",
	"SUPPORT_TYPE_FIELD" => "タイプ",
	"SUPPORT_CURRENT_STATUS_FIELD" => "現在のステータス",
	"SUPPORT_SUMMARY_FIELD" => "件名",
	"SUPPORT_DESCRIPTION_FIELD" => "詳細内容",
	"SUPPORT_MESSAGE_FIELD" => "メッセージ",
	"SUPPORT_ADDED_FIELD" => "追加済み",
	"SUPPORT_ADDED_BY_FIELD" => "担当者",
	"SUPPORT_UPDATED_FIELD" => "最終更新",

	"SUPPORT_REQUEST_BUTTON" => "送信",
	                                    
	"SUPPORT_MISS_ID_ERROR" => "<b>サポート ID</b> パラメーターが欠けています。",
	"SUPPORT_MISS_CODE_ERROR" => "<b>認証</b> パラメーターが欠けています。",
	"SUPPORT_WRONG_ID_ERROR" => "<b>サポート ID</b> パラメーターに正しくない値が含まれています。",
	"SUPPORT_WRONG_CODE_ERROR" => "<b>認証</b> パラメーターに正しくない値が含まれています。",

	"MAIL_DATA_MSG" => "メール情報",
	"HEADERS_MSG" => "ヘッダー",
	"ORIGINAL_TEXT_MSG" => "オリジナルテキスト",
	"ORIGINAL_HTML_MSG" => "オリジナル HTML",
	"CLOSE_TICKET_NOT_ALLOWED_MSG" => "チケットをクローズできません。<br>",
	"REPLY_TICKET_NOT_ALLOWED_MSG" => "チケットを返信できません。<br>",
	"CREATE_TICKET_NOT_ALLOWED_MSG" => "新規チケットを作成できません。<br>",
	"REMOVE_TICKET_NOT_ALLOWED_MSG" => "チケットを削除できません。<br>",
	"UPDATE_TICKET_NOT_ALLOWED_MSG" => "チケットを更新できません。<br>",
	"NO_TICKETS_FOUND_MSG" => "チケットは見つかりませんでした。",
	"HIDDEN_TICKETS_MSG" => "チケットを非表示にする",
	"ALL_TICKETS_MSG" => "すべてのチケット",
	"ACTIVE_TICKETS_MSG" => "有効なチケット",
	"TICKET_DETAILS_MSG" => "Ticket Details",
	"NOT_ASSIGNED_THIS_DEP_MSG" => "この部門に割り当てられていません。",
	"NOT_ASSIGNED_ANY_DEP_MSG" => "どの部門にも割り当てられていません。",
	"REPLY_TO_NAME_MSG" => "{name} へ返信",
	"KNOWLEDGE_CATEGORY_MSG" => "ナレッジカテゴリー",
	"KNOWLEDGE_TITLE_MSG" => "ナレッジタイトル",
	"KNOWLEDGE_ARTICLE_STATUS_MSG" => "ナレッジアーティクルステータス",
	"SELECT_RESPONSIBLE_MSG" => "Responsible の選択",
	//ticket types
	"TICKET_CORRECTION_MSG" => "Correction",
	"TICKET_BUG_ISSUE_MSG" => "Bug/Issue",
	"TICKET_QUESTION_MSG" => "Question",
	"TICKET_WISH_REQUEST_MSG" => "Wish/Request",
	"TICKET_OTHER_MSG" => "Other",
	"TICKET_PRICE_QUOTE_MSG" => "Quote",
	//ticket statuses and types
	"TICKET_NEW_MSG" => "New",
	"TICKET_ANSWERED_MSG" => "Answered",
	"TICKET_REQUEST_INFO_MSG" => "Request More Information",
	"TICKET_INVESTIGATING_MSG" => "Investigating",
	"TICKET_CLOSED_MSG" => "Closed",
	"TICKET_ASSIGNED_MANAGER_MSG" => "Assigned to Manager",
	"TICKET_USER_REPLY_MSG" => "User Reply",
	"TICKET_MANAGER_REPLY_MSG" => "Manager Reply",
	"TICKET_ASSIGN_MANAGER_MSG" => "Assign Manager",
	"TICKET_FORWARD_MSG" => "Forward Ticket",
	"TICKET_FORWARDED_MSG" => "Forwarded",

	"CHATS_MSG" => "Chats",
	"START_CHATTING_MSG" => "Start Chatting",
	"SUPPORT_LIVE_MSG" => "Live Support",
	"SUPPORT_ONLINE_DESC" => "Hello, we are online, <br />click here to start chatting",
	"SUPPORT_OFFLINE_DESC" => "Sorry, we are offline, <br />click here to send us message",
	"CHATS_WAITING_MSG" => "<b>{number}</b> chats waiting",
	"CHAT_QUESTION_MSG" => "Question",
	"INCOMING_REQUESTS_MSG" => "{number} incomming requests",
	"YOUR_STATUS_MSG" => "Your status:",
	"NO_CHATS_WAITING_MSG" => "There are no users waiting to chat.",
	"WAITING_MSG" => "Waiting",
	"CHATTING_MSG" => "Chatting",
	"EXIT_CHAT_MSG" => "Exit Chat",
	"CLOSE_CHAT_MSG" => "Close Chat",
	"CLOSE_CHAT_QST" => "Whould you like to close this chat?",
	"CHAT_SYSTEM_MSG" => "System",
	"USER_JOINED_CHAT_MSG" => "{name} has joined chat.",
	"USER_CLOSED_CHAT_MSG" => "{name} has closed chat.",
	"CHAT_AUTO_CLOSED_MSG" => "Chat has been closed automatically due to inactivity.",

	"EMAIL_PIPES_MSG" => "Email Pipes",
	"EMAIL_PIPE_MSG" => "Email Pipe",
	"INCOMING_EMAIL_MSG" => "Incoming Email",
	"INCOMING_EMAIL_DESC" => "The email address where messages will be sent",
	"OUTGOING_EMAIL_MSG" => "Outgoing Email",
	"OUTGOING_EMAIL_DESC" => "The email address which will be used in the 'From' field in all help desk email notifications when pipe settings are matched. Leave it blank if you don't want override your settings for 'From' field.",

);
$va_messages = array_merge($va_messages, $messages);
