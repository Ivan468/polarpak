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
	// mesaje suport
	"SUPPORT_TITLE" => "Serviciu clienti",
	"SUPPORT_REQUEST_INF_TITLE" => "Cerere informatii",
	"SUPPORT_REPLY_FORM_TITLE" => "Raspuns",
	"SUPPORT_SHORT_TITLE" => "Support",

	"MY_SUPPORT_ISSUES_MSG" => "Cererile mele de suport",
	"MY_SUPPORT_ISSUES_DESC" => "Daca aveti probleme cu produsele achizitionate echipa noastra de suport este gata sa va ajute. Dati click pe linkul de mai sus pentru a inainta o cerere de suport.",
	"NEW_SUPPORT_REQUEST_MSG" => "Cerere noua",
	"SUPPORT_REQUEST_ADDED_MSG" => "Multumim<br>Echipa de suport va incerca sa va ajute cu cererea dumneavoastra cat mai curand posibil.",
	"SUPPORT_SELECT_DEP_MSG" => "Selecteaza departament",
	"SUPPORT_SELECT_PROD_MSG" => "Selecteaza produs",
	"SUPPORT_SELECT_STATUS_MSG" => "Selecteaza status",
	"SUPPORT_NOT_VIEWED_MSG" => "Nevizionat",
	"SUPPORT_VIEWED_BY_USER_MSG" => "Vizionat de catre utilizator",
	"SUPPORT_VIEWED_BY_ADMIN_MSG" => "Vizionat de catre administrator",
	"SUPPORT_STATUS_NEW_MSG" => "Nou",
	"NO_SUPPORT_REQUEST_MSG" => "Nu au fost gasite probleme",

	"SUPPORT_SUMMARY_COLUMN" => "Rezumat",
	"SUPPORT_TYPE_COLUMN" => "Tip",
	"SUPPORT_UPDATED_COLUMN" => "Ultima actualizare",

	"SUPPORT_USER_NAME_FIELD" => "Numele dumneavoastra",
	"SUPPORT_USER_EMAIL_FIELD" => "Adresa de email",
	"SUPPORT_IDENTIFIER_FIELD" => "Identificator (# Factura)",
	"SUPPORT_ENVIRONMENT_FIELD" => "Mediu de lucru (OS, baza de date, server web, etc)",
	"SUPPORT_DEPARTMENT_FIELD" => "Departament",
	"SUPPORT_PRODUCT_FIELD" => "Produs",
	"SUPPORT_TYPE_FIELD" => "Tip",
	"SUPPORT_CURRENT_STATUS_FIELD" => "Status actual",
	"SUPPORT_SUMMARY_FIELD" => "Rezumat de un rand",
	"SUPPORT_DESCRIPTION_FIELD" => "Descriere",
	"SUPPORT_MESSAGE_FIELD" => "Mesaj",
	"SUPPORT_ADDED_FIELD" => "Adaugat",
	"SUPPORT_ADDED_BY_FIELD" => "Adaugat de catre",
	"SUPPORT_UPDATED_FIELD" => "Ultima actualizare",

	"SUPPORT_REQUEST_BUTTON" => "Trimite cerere",
	                                    
	"SUPPORT_MISS_ID_ERROR" => "Lipseste parametrul <b>Support ID</b>",
	"SUPPORT_MISS_CODE_ERROR" => "Lipseste parametrul <b>Verification</b>",
	"SUPPORT_WRONG_ID_ERROR" => "Parametrul <b>Support ID</b> are o valoare gresita",
	"SUPPORT_WRONG_CODE_ERROR" => "Parametrul <b>Verification</b> are o valoare gresita",

	"MAIL_DATA_MSG" => "Date mesaj",
	"HEADERS_MSG" => "Headers",
	"ORIGINAL_TEXT_MSG" => "Text original",
	"ORIGINAL_HTML_MSG" => "HTML original",
	"CLOSE_TICKET_NOT_ALLOWED_MSG" => "Ne pare rau dar nu aveti permisiunea de a inchide mesaje.<br>",
	"REPLY_TICKET_NOT_ALLOWED_MSG" => "Ne pare rau dar nu aveti permisiunea de a raspunde la mesaje.<br>",
	"CREATE_TICKET_NOT_ALLOWED_MSG" => "Ne pare rau dar nu aveti permisiunea de a creea noi mesaje.<br>",
	"REMOVE_TICKET_NOT_ALLOWED_MSG" => "Ne pare rau dar nu aveti permisiunea de a inlatura mesaje.<br>",
	"UPDATE_TICKET_NOT_ALLOWED_MSG" => "Ne pare rau dar nu aveti permisiunea de a actualiza mesaje.<br>",
	"NO_TICKETS_FOUND_MSG" => "Nu s-au gasit mesaje.",
	"HIDDEN_TICKETS_MSG" => "Mesaje ascunse",
	"ALL_TICKETS_MSG" => "Toate mesajele",
	"ACTIVE_TICKETS_MSG" => "Mesaje active",
	"TICKET_DETAILS_MSG" => "Ticket Details",
	"NOT_ASSIGNED_THIS_DEP_MSG" => "Nu sunteti repartizat in acest departament.",
	"NOT_ASSIGNED_ANY_DEP_MSG" => "Nu sunteti repartizat in nici un departament.",
	"REPLY_TO_NAME_MSG" => "Raspuns catre {name}",
	"KNOWLEDGE_CATEGORY_MSG" => "Categorie cunostinte",
	"KNOWLEDGE_TITLE_MSG" => "Titlu cunostinte",
	"KNOWLEDGE_ARTICLE_STATUS_MSG" => "Status articol cunostinte",
	"SELECT_RESPONSIBLE_MSG" => "Selecteaza responsabil",
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
