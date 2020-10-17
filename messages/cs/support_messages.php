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
	"SUPPORT_TITLE" => "Centrum podpory",
	"SUPPORT_REQUEST_INF_TITLE" => "Žádost o informaci",
	"SUPPORT_REPLY_FORM_TITLE" => "Odpovědět",
	"SUPPORT_SHORT_TITLE" => "Support",

	"MY_SUPPORT_ISSUES_MSG" => "Mé žádosti o podporu",
	"MY_SUPPORT_ISSUES_DESC" => "Pokud máte jakékoliv problémy s produktama, které jste u nás zakoupili, náš tím podpory zákazníkům je připraven Vám pomoci. Pro zadání požadavky stačí kliknout na horní link",
	"NEW_SUPPORT_REQUEST_MSG" => "Nová požadavka",
	"SUPPORT_REQUEST_ADDED_MSG" => "Děkujeme<br>Náš tím podpory se pokusí pomoci s Vaší požadavkou tak rychle, jak to jen bude možné.",
	"SUPPORT_SELECT_DEP_MSG" => "Zvolit oddělení",
	"SUPPORT_SELECT_PROD_MSG" => "Zvolit produkt",
	"SUPPORT_SELECT_STATUS_MSG" => "Vybrat stav",
	"SUPPORT_NOT_VIEWED_MSG" => "Nečtené",
	"SUPPORT_VIEWED_BY_USER_MSG" => "Čtené zákazníkem",
	"SUPPORT_VIEWED_BY_ADMIN_MSG" => "Čtené administrátorem",
	"SUPPORT_STATUS_NEW_MSG" => "Nová",
	"NO_SUPPORT_REQUEST_MSG" => "Žádné dotazy nenalezeny",

	"SUPPORT_SUMMARY_COLUMN" => "Sumář",
	"SUPPORT_TYPE_COLUMN" => "Typ",
	"SUPPORT_UPDATED_COLUMN" => "Naposled aktualizováno",

	"SUPPORT_USER_NAME_FIELD" => "Vaše jméno",
	"SUPPORT_USER_EMAIL_FIELD" => "Vaše emailová adresa",
	"SUPPORT_IDENTIFIER_FIELD" => "Identifikátor (číslo faktury)",
	"SUPPORT_ENVIRONMENT_FIELD" => "Prostředí (OS, Databáze, Web Server, atd.)",
	"SUPPORT_DEPARTMENT_FIELD" => "Oddelení",
	"SUPPORT_PRODUCT_FIELD" => "Produkt",
	"SUPPORT_TYPE_FIELD" => "Typ",
	"SUPPORT_CURRENT_STATUS_FIELD" => "Momentální status",
	"SUPPORT_SUMMARY_FIELD" => "Jednořádkový sumář",
	"SUPPORT_DESCRIPTION_FIELD" => "Popis",
	"SUPPORT_MESSAGE_FIELD" => "Zpráva",
	"SUPPORT_ADDED_FIELD" => "Přidaný",
	"SUPPORT_ADDED_BY_FIELD" => "Přidal",
	"SUPPORT_UPDATED_FIELD" => "Naposled aktualizováno",

	"SUPPORT_REQUEST_BUTTON" => "Odeslat požadavku",
	                                    
	"SUPPORT_MISS_ID_ERROR" => "Schází <b>Podpora ID</b> parametr",
	"SUPPORT_MISS_CODE_ERROR" => "Schází <b>kontrolný</b> parametr",
	"SUPPORT_WRONG_ID_ERROR" => "<b>Podpora ID</b> parametr má nesprávnou hodnotu",
	"SUPPORT_WRONG_CODE_ERROR" => "<b>Kontrolný</b> parametr má nesprávnou hodnotu",

	"MAIL_DATA_MSG" => "Mail Data",
	"HEADERS_MSG" => "Headers",
	"ORIGINAL_TEXT_MSG" => "Original Text",
	"ORIGINAL_HTML_MSG" => "Original HTML",
	"CLOSE_TICKET_NOT_ALLOWED_MSG" => "Sorry, but you are not allowed to close tickets.<br>",
	"REPLY_TICKET_NOT_ALLOWED_MSG" => "Sorry, but you are not allowed to reply tickets.<br>",
	"CREATE_TICKET_NOT_ALLOWED_MSG" => "Sorry, but you are not allowed to create new tickets.<br>",
	"REMOVE_TICKET_NOT_ALLOWED_MSG" => "Sorry, but you are not allowed to remove tickets.<br>",
	"UPDATE_TICKET_NOT_ALLOWED_MSG" => "Sorry, but you are not allowed to update tickets.<br>",
	"NO_TICKETS_FOUND_MSG" => "No tickets were found.",
	"HIDDEN_TICKETS_MSG" => "Hidden Tickets",
	"ALL_TICKETS_MSG" => "All Tickets",
	"ACTIVE_TICKETS_MSG" => "Active Tickets",
	"TICKET_DETAILS_MSG" => "Ticket Details",
	"NOT_ASSIGNED_THIS_DEP_MSG" => "You are not assigned to this department.",
	"NOT_ASSIGNED_ANY_DEP_MSG" => "You are not assigned to any department.",
	"REPLY_TO_NAME_MSG" => "Reply to {name}",
	"KNOWLEDGE_CATEGORY_MSG" => "Knowledge Category",
	"KNOWLEDGE_TITLE_MSG" => "Knowledge Title",
	"KNOWLEDGE_ARTICLE_STATUS_MSG" => "Knowledge Article Status",
	"SELECT_RESPONSIBLE_MSG" => "Select Responsible",
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
