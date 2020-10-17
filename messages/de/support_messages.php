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
	"SUPPORT_TITLE" => "Hilfe-Center",
	"SUPPORT_REQUEST_INF_TITLE" => "Anfrage-Informationen",
	"SUPPORT_REPLY_FORM_TITLE" => "Antwort",
	"SUPPORT_SHORT_TITLE" => "Support",

	"MY_SUPPORT_ISSUES_MSG" => "Meine Anfragen",
	"MY_SUPPORT_ISSUES_DESC" => "Sollten Sie Probleme mit den gekauften Produkten haben, steht Ihnen unser Hilfe-Team gerne zur Verfügung. Klicken Sie auf den Verweis oben um eine Anfrage abzusenden.",
	"NEW_SUPPORT_REQUEST_MSG" => "Neue Anfrage",
	"SUPPORT_REQUEST_ADDED_MSG" => "Vielen Dank<br>Unser Team wird sich nun bemühen, Ihnen so schnell wie möglich zu helfen.",
	"SUPPORT_SELECT_DEP_MSG" => "Abteilung wählen",
	"SUPPORT_SELECT_PROD_MSG" => "Produkt wählen",
	"SUPPORT_SELECT_STATUS_MSG" => "Status wählen",
	"SUPPORT_NOT_VIEWED_MSG" => "Nicht angesehen",
	"SUPPORT_VIEWED_BY_USER_MSG" => "Vom Kunden angesehen",
	"SUPPORT_VIEWED_BY_ADMIN_MSG" => "Vom Administrator angesehen",
	"SUPPORT_STATUS_NEW_MSG" => "Neu",
	"NO_SUPPORT_REQUEST_MSG" => "Nichts gefunden",

	"SUPPORT_SUMMARY_COLUMN" => "Zusammenfassung",
	"SUPPORT_TYPE_COLUMN" => "Typ",
	"SUPPORT_UPDATED_COLUMN" => "Zuletzt aktualisiert",

	"SUPPORT_USER_NAME_FIELD" => "Ihr Name",
	"SUPPORT_USER_EMAIL_FIELD" => "Ihre E-Mail-Adresse",
	"SUPPORT_IDENTIFIER_FIELD" => "Kennung (Rechnung #)",
	"SUPPORT_ENVIRONMENT_FIELD" => "Umgebung (Betriebssystem, Datenbank, Webserver, etc.)",
	"SUPPORT_DEPARTMENT_FIELD" => "Abteilung",
	"SUPPORT_PRODUCT_FIELD" => "Produkt",
	"SUPPORT_TYPE_FIELD" => "Typ",
	"SUPPORT_CURRENT_STATUS_FIELD" => "Aktueller Status",
	"SUPPORT_SUMMARY_FIELD" => "Online-Zusammenfassung",
	"SUPPORT_DESCRIPTION_FIELD" => "Beschreibung",
	"SUPPORT_MESSAGE_FIELD" => "Nachricht",
	"SUPPORT_ADDED_FIELD" => "Hinzugefügt",
	"SUPPORT_ADDED_BY_FIELD" => "Hinzugefügt von",
	"SUPPORT_UPDATED_FIELD" => "Zuletzt aktualisiert",

	"SUPPORT_REQUEST_BUTTON" => "Anfrage absenden",
	                                    
	"SUPPORT_MISS_ID_ERROR" => "Fehlender Parameter <b>Support ID</b>",
	"SUPPORT_MISS_CODE_ERROR" => "Fehlender Parameter <b>Verifizierung</b>",
	"SUPPORT_WRONG_ID_ERROR" => "<b>Support ID</b> Parameter hat falschen Wert",
	"SUPPORT_WRONG_CODE_ERROR" => "<b>Verifizierung</b> Parameter hat falschen Wert",

	"MAIL_DATA_MSG" => "Mail Data",
	"HEADERS_MSG" => "Headers",
	"ORIGINAL_TEXT_MSG" => "Original Text",
	"ORIGINAL_HTML_MSG" => "Original HTML",
	"CLOSE_TICKET_NOT_ALLOWED_MSG" => "Sorry, Sie haben keine Genehmigung das Ticket zu schließen",
	"REPLY_TICKET_NOT_ALLOWED_MSG" => "Sorry, Sie haben keine Genehmigung das Ticket zu beantworten",
	"CREATE_TICKET_NOT_ALLOWED_MSG" => "Sorry, Sie haben keine Genehmigung ein neues Ticket zu erstellen",
	"REMOVE_TICKET_NOT_ALLOWED_MSG" => "Sorry, Sie haben keine Genehmigung das Ticket zu löschen",
	"UPDATE_TICKET_NOT_ALLOWED_MSG" => "Sorry, Sie haben keine Genehmigung das Ticket zu erweitern",
	"NO_TICKETS_FOUND_MSG" => "Keine Tickets gefunden",
	"HIDDEN_TICKETS_MSG" => "Versteckte Tickets",
	"ALL_TICKETS_MSG" => "Alle Tickets",
	"ACTIVE_TICKETS_MSG" => "Aktive Tickets",
	"TICKET_DETAILS_MSG" => "Ticket Details",
	"NOT_ASSIGNED_THIS_DEP_MSG" => "Sie gehören nicht zu diesem Department",
	"NOT_ASSIGNED_ANY_DEP_MSG" => "Sie sind keinem Department zugeordnet",
	"REPLY_TO_NAME_MSG" => "Antwort an {name}",
	"KNOWLEDGE_CATEGORY_MSG" => "Wissens Kategorie",
	"KNOWLEDGE_TITLE_MSG" => "Wissens Titel",
	"KNOWLEDGE_ARTICLE_STATUS_MSG" => "Wissens Atikel Status",
	"SELECT_RESPONSIBLE_MSG" => "Wähle Verantwortlich",
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
