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
	"SUPPORT_TITLE" => "Supportcenter",
	"SUPPORT_REQUEST_INF_TITLE" => "Fråga efter information",
	"SUPPORT_REPLY_FORM_TITLE" => "Svara",
	"SUPPORT_SHORT_TITLE" => "Support",

	"MY_SUPPORT_ISSUES_MSG" => "Mina Support-frågor",
	"MY_SUPPORT_ISSUES_DESC" => "Om du råkar ut för några problem med en produkt som du har köpt, kan vår support ta hand om dig. Vänligen kontakta oss genom att klicka på länken ovan för att skicka en förfrågan till support. ",
	"NEW_SUPPORT_REQUEST_MSG" => "Ny förfrågan",
	"SUPPORT_REQUEST_ADDED_MSG" => "Tack så mycket!<br>Vår support kommer försöka hjälpa dig så snart som möjligt.",
	"SUPPORT_SELECT_DEP_MSG" => "Välj område",
	"SUPPORT_SELECT_PROD_MSG" => "Välj produkt",
	"SUPPORT_SELECT_STATUS_MSG" => "Välj status",
	"SUPPORT_NOT_VIEWED_MSG" => "Inte visad",
	"SUPPORT_VIEWED_BY_USER_MSG" => "Visad av kund",
	"SUPPORT_VIEWED_BY_ADMIN_MSG" => "Visad av adminstrator",
	"SUPPORT_STATUS_NEW_MSG" => "Ny",
	"NO_SUPPORT_REQUEST_MSG" => "Inga supportfrågor hittades",

	"SUPPORT_SUMMARY_COLUMN" => "Summering",
	"SUPPORT_TYPE_COLUMN" => "Typ",
	"SUPPORT_UPDATED_COLUMN" => "Senast uppdaterad",

	"SUPPORT_USER_NAME_FIELD" => "Ditt namn",
	"SUPPORT_USER_EMAIL_FIELD" => "Din epostadress",
	"SUPPORT_IDENTIFIER_FIELD" => "Fakturanummer",
	"SUPPORT_ENVIRONMENT_FIELD" => "Datamiljö (OS, Databas, Webserver, etc)",
	"SUPPORT_DEPARTMENT_FIELD" => "Avdelning",
	"SUPPORT_PRODUCT_FIELD" => "Produkt",
	"SUPPORT_TYPE_FIELD" => "Typ",
	"SUPPORT_CURRENT_STATUS_FIELD" => "Nuvarande status",
	"SUPPORT_SUMMARY_FIELD" => "Enradssummering",
	"SUPPORT_DESCRIPTION_FIELD" => "Beskrivning",
	"SUPPORT_MESSAGE_FIELD" => "Meddelande",
	"SUPPORT_ADDED_FIELD" => "Inlagd",
	"SUPPORT_ADDED_BY_FIELD" => "Inlagd av",
	"SUPPORT_UPDATED_FIELD" => "Senast uppdaterad",

	"SUPPORT_REQUEST_BUTTON" => "Skicka förfrågan",
	                                    
	"SUPPORT_MISS_ID_ERROR" => "Saknar <b>Support-ID</b>-parameter",
	"SUPPORT_MISS_CODE_ERROR" => "Saknar <b>Verifikations</b>-paramenter",
	"SUPPORT_WRONG_ID_ERROR" => "<b>Support-ID</b>-parametern har fel värde",
	"SUPPORT_WRONG_CODE_ERROR" => "<b>Verifikations</b>-parameter har fel värde",

	"MAIL_DATA_MSG" => "Maildata",
	"HEADERS_MSG" => "Rubriker",
	"ORIGINAL_TEXT_MSG" => "Originaltext",
	"ORIGINAL_HTML_MSG" => "Original-HTML",
	"CLOSE_TICKET_NOT_ALLOWED_MSG" => "Tyvärr, men du har inte tillåtelse att stänga etiketter.<br>",
	"REPLY_TICKET_NOT_ALLOWED_MSG" => "Tyvärr, men du har inte tillåtelse att svara på etiketter.<br>",
	"CREATE_TICKET_NOT_ALLOWED_MSG" => "Tyvärr, men du har inte tillåtelse att skapa nya etiketter.<br>",
	"REMOVE_TICKET_NOT_ALLOWED_MSG" => "Tyvärr, men du har inte tillåtelse att ta bort etiketter.<br>",
	"UPDATE_TICKET_NOT_ALLOWED_MSG" => "Tyvärr, men du har inte tillåtelse att uppdatera etiketter.<br>",
	"NO_TICKETS_FOUND_MSG" => "Inga etiketter hittades.",
	"HIDDEN_TICKETS_MSG" => "Gömda etiketter",
	"ALL_TICKETS_MSG" => "Alla etiketter",
	"ACTIVE_TICKETS_MSG" => "Aktivera etiketter",
	"TICKET_DETAILS_MSG" => "Ticket Details",
	"NOT_ASSIGNED_THIS_DEP_MSG" => "Du har inte tillgång till denna avdelningen.",
	"NOT_ASSIGNED_ANY_DEP_MSG" => "Du har inte tillgång till någon avdelning.",
	"REPLY_TO_NAME_MSG" => "Svara till {name}",
	"KNOWLEDGE_CATEGORY_MSG" => "Kunskapskategori",
	"KNOWLEDGE_TITLE_MSG" => "Kunskapstitel",
	"KNOWLEDGE_ARTICLE_STATUS_MSG" => "Kunskapsartikel status",
	"SELECT_RESPONSIBLE_MSG" => "Välj ansvarsområde",
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
