<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  support_messages.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	// support messages
	"SUPPORT_TITLE" => "Tugikeskus",
	"SUPPORT_REQUEST_INF_TITLE" => "Päringu info",
	"SUPPORT_REPLY_FORM_TITLE" => "Vasta",
	"SUPPORT_SHORT_TITLE" => "Support",

	"MY_SUPPORT_ISSUES_MSG" => "Minu toe päringud",
	"MY_SUPPORT_ISSUES_DESC" => "Kui Teil esineb mingeid probleeme ostetud toodetega, on meie klienditugi valmis aitama. Toe päringu saatmiseks kliki ülaltoodud lingile.",
	"NEW_SUPPORT_REQUEST_MSG" => "Uus päring",
	"SUPPORT_REQUEST_ADDED_MSG" => "Aitäh<br>Meie klienditugid üritab Teid aidata nii kiiresti kui võimalik.",
	"SUPPORT_SELECT_DEP_MSG" => "Vali osakond",
	"SUPPORT_SELECT_PROD_MSG" => "Vali toode",
	"SUPPORT_SELECT_STATUS_MSG" => "Vali staatus",
	"SUPPORT_NOT_VIEWED_MSG" => "Ei ole vaadatud",
	"SUPPORT_VIEWED_BY_USER_MSG" => "Kasutaja poolt vaadatud",
	"SUPPORT_VIEWED_BY_ADMIN_MSG" => "Administraatori poolt vaadatud",
	"SUPPORT_STATUS_NEW_MSG" => "Uus",
	"NO_SUPPORT_REQUEST_MSG" => "Probleeme ei leitud",

	"SUPPORT_SUMMARY_COLUMN" => "Kokkuvõte",
	"SUPPORT_TYPE_COLUMN" => "Tüüp",
	"SUPPORT_UPDATED_COLUMN" => "Viimati uuendatud",

	"SUPPORT_USER_NAME_FIELD" => "Sinu nimi",
	"SUPPORT_USER_EMAIL_FIELD" => "Sinu e-postiaadress",
	"SUPPORT_IDENTIFIER_FIELD" => "Identifikaator (arve #)",
	"SUPPORT_ENVIRONMENT_FIELD" => "Keskkond (OS, Database, Web Server jne)",
	"SUPPORT_DEPARTMENT_FIELD" => "Osakond",
	"SUPPORT_PRODUCT_FIELD" => "Toode",
	"SUPPORT_TYPE_FIELD" => "Tüüp",
	"SUPPORT_CURRENT_STATUS_FIELD" => "Hetkestaatus",
	"SUPPORT_SUMMARY_FIELD" => "Üherealine kokkuvõte",
	"SUPPORT_DESCRIPTION_FIELD" => "Kirjeldus",
	"SUPPORT_MESSAGE_FIELD" => "Teade",
	"SUPPORT_ADDED_FIELD" => "Lisatud",
	"SUPPORT_ADDED_BY_FIELD" => "Lisanud",
	"SUPPORT_UPDATED_FIELD" => "Viimati uuendatud",

	"SUPPORT_REQUEST_BUTTON" => "Esita päring",
	                                    
	"SUPPORT_MISS_ID_ERROR" => "Puuduv <b>toe ID</b> parameeter",
	"SUPPORT_MISS_CODE_ERROR" => "Puuduv <b>kinnitamise</b> parameeter",
	"SUPPORT_WRONG_ID_ERROR" => "<b>Toe ID</b> parameetril on vale väärtus",
	"SUPPORT_WRONG_CODE_ERROR" => "<b>Kinnitamise</b> parameetril on vale väärtus",

	"MAIL_DATA_MSG" => "E-posti andmed",
	"HEADERS_MSG" => "Päised",
	"ORIGINAL_TEXT_MSG" => "Originaaltekst",
	"ORIGINAL_HTML_MSG" => "Originaal – HTML",
	"CLOSE_TICKET_NOT_ALLOWED_MSG" => "Vabandame, kuid sul ei ole lubatud sulgeda ticket'eid.<br>",
	"REPLY_TICKET_NOT_ALLOWED_MSG" => "Vabandame, kuid sul ei ole lubatud vastata ticket'itele.<br>",
	"CREATE_TICKET_NOT_ALLOWED_MSG" => "Vabandame, kuid sul ei ole lubatud luua uusi ticket'eid.<br>",
	"REMOVE_TICKET_NOT_ALLOWED_MSG" => "Vabandame, kuid sul ei ole lubatud eemaldada ticket'eid.<br>",
	"UPDATE_TICKET_NOT_ALLOWED_MSG" => "Vabandame, kuid sul ei ole lubatud uuendata ticket'eid.<br>",
	"NO_TICKETS_FOUND_MSG" => "Ticket'eid ei leitud.",
	"HIDDEN_TICKETS_MSG" => "Peidetud ticket'id",
	"ALL_TICKETS_MSG" => "Kõik ticket'id",
	"ACTIVE_TICKETS_MSG" => "Aktiivsed ticket'id",
	"TICKET_DETAILS_MSG" => "Ticket Details",
	"NOT_ASSIGNED_THIS_DEP_MSG" => "Sind ei ole määratud sellesse osakonda.",
	"NOT_ASSIGNED_ANY_DEP_MSG" => "Sind ei ole määratud ühessegi osakonda.",
	"REPLY_TO_NAME_MSG" => "Vasta isikule {name}",
	"KNOWLEDGE_CATEGORY_MSG" => "Knowledge kategooria",
	"KNOWLEDGE_TITLE_MSG" => "Knowledge pealkiri",
	"KNOWLEDGE_ARTICLE_STATUS_MSG" => "Knowledge artikli staatus",
	"SELECT_RESPONSIBLE_MSG" => "Vali vastutav",
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
