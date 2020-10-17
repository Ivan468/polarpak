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
	//támogatás üzenetek
	"SUPPORT_TITLE" => "Terméktámogatási Központ",
	"SUPPORT_REQUEST_INF_TITLE" => "Kérés Információ",
	"SUPPORT_REPLY_FORM_TITLE" => "Válaszolnak",
	"SUPPORT_SHORT_TITLE" => "Support",

	"MY_SUPPORT_ISSUES_MSG" => "Támogatási kéréseim",
	"MY_SUPPORT_ISSUES_DESC" => "Ha tapasztalsz bármilyen problémát a termékkel amit megvásároltál, a Támogatási Csapatunk készen áll segíteni. A linkre kattintással fel lehet venni a kapcsolatot velünk, és küldhetsz támogatási kérést.",
	"NEW_SUPPORT_REQUEST_MSG" => "Új Kérés",
	"SUPPORT_REQUEST_ADDED_MSG" => "Köszönjük<br>A támogatási csapatunk megpróbál segítséget nyújtani , amilyen gyorsan lehetséges.",
	"SUPPORT_SELECT_DEP_MSG" => "Kiválasztás osztály",
	"SUPPORT_SELECT_PROD_MSG" => "Kiválasztás termék",
	"SUPPORT_SELECT_STATUS_MSG" => "Kiválasztás státusz",
	"SUPPORT_NOT_VIEWED_MSG" => "Nem nézett",
	"SUPPORT_VIEWED_BY_USER_MSG" => "Nézett ügyfél által",
	"SUPPORT_VIEWED_BY_ADMIN_MSG" => "Nézett ügyintéző által",
	"SUPPORT_STATUS_NEW_MSG" => "Új",
	"NO_SUPPORT_REQUEST_MSG" => "Nincs kérdés",

	"SUPPORT_SUMMARY_COLUMN" => "Összefoglaló",
	"SUPPORT_TYPE_COLUMN" => "Tipus",
	"SUPPORT_UPDATED_COLUMN" => "Legutóbb frissített",

	"SUPPORT_USER_NAME_FIELD" => "Neved",
	"SUPPORT_USER_EMAIL_FIELD" => "Email Címed",
	"SUPPORT_IDENTIFIER_FIELD" => "Azonosító (Számla #)",
	"SUPPORT_ENVIRONMENT_FIELD" => "Környezet (OS, Adatbázis, Web szerver, stb.)",
	"SUPPORT_DEPARTMENT_FIELD" => "Osztály",
	"SUPPORT_PRODUCT_FIELD" => "Termék",
	"SUPPORT_TYPE_FIELD" => "Tipus",
	"SUPPORT_CURRENT_STATUS_FIELD" => "Jelenlegi Helyzet",
	"SUPPORT_SUMMARY_FIELD" => "Egysoros összefoglaló",
	"SUPPORT_DESCRIPTION_FIELD" => "Leírás",
	"SUPPORT_MESSAGE_FIELD" => "Üzenet",
	"SUPPORT_ADDED_FIELD" => "Hozzáadott",
	"SUPPORT_ADDED_BY_FIELD" => "Hozzáadta:",
	"SUPPORT_UPDATED_FIELD" => "Legutóbb frissített",

	"SUPPORT_REQUEST_BUTTON" => "Kérés továbbítása",
	                                    
	"SUPPORT_MISS_ID_ERROR" => "Hiányzó <b>Support ID</b> paraméter",
	"SUPPORT_MISS_CODE_ERROR" => "Hiányzó <b>Verification</b> paraméter",
	"SUPPORT_WRONG_ID_ERROR" => "<b>Support ID</b> paraméter az értéke rossz.",
	"SUPPORT_WRONG_CODE_ERROR" => "<b>Verification</b> paraméter az értéke rossz.",

	"MAIL_DATA_MSG" => "Adatok küldése",
	"HEADERS_MSG" => "Fejlécek",
	"ORIGINAL_TEXT_MSG" => "Eredeti szöveg",
	"ORIGINAL_HTML_MSG" => "Eredeti HTML",
	"CLOSE_TICKET_NOT_ALLOWED_MSG" => "Sajnáljuk, de számodra nem engedélyezett a karton lezárása.<br>",
	"REPLY_TICKET_NOT_ALLOWED_MSG" => "Sajnáljuk, de számodra nem engedélyezett a válasz küldés a kartonokra.<br>",
	"CREATE_TICKET_NOT_ALLOWED_MSG" => "Sajnáljuk, de számodra nem engedélyezett az új karton létrehozása.<br>",
	"REMOVE_TICKET_NOT_ALLOWED_MSG" => "Sajnáljuk, de számodra nem engedélyezett a kartonok törlése.<br>",
	"UPDATE_TICKET_NOT_ALLOWED_MSG" => "Sajnáljuk, de számodra nem engedélyezett a kartonok frissítése.<br>",
	"NO_TICKETS_FOUND_MSG" => "Nem találtunk kartonokat.",
	"HIDDEN_TICKETS_MSG" => "Rejtett Kartonok",
	"ALL_TICKETS_MSG" => "Összes Karton",
	"ACTIVE_TICKETS_MSG" => "Aktív Kartonok",
	"TICKET_DETAILS_MSG" => "Ticket Details",
	"NOT_ASSIGNED_THIS_DEP_MSG" => "Nem vagy az Osztály tagja.",
	"NOT_ASSIGNED_ANY_DEP_MSG" => "Nem vagy egyik Osztály tagja sem.",
	"REPLY_TO_NAME_MSG" => "Válasz küldés neki: {name}",
	"KNOWLEDGE_CATEGORY_MSG" => "Tudásbázis Karegória",
	"KNOWLEDGE_TITLE_MSG" => "Tudásbázis Cím",
	"KNOWLEDGE_ARTICLE_STATUS_MSG" => "Tudásbázis Cikk státusz",
	"SELECT_RESPONSIBLE_MSG" => "Válassz Illetékest",
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
