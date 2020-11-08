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
	"SUPPORT_TITLE" => "Centro de Apoio",
	"SUPPORT_REQUEST_INF_TITLE" => "Pedido de Informação",
	"SUPPORT_REPLY_FORM_TITLE" => "Responder",
	"SUPPORT_SHORT_TITLE" => "Support",

	"MY_SUPPORT_ISSUES_MSG" => "As Minhas Dúvidas",
	"MY_SUPPORT_ISSUES_DESC" => "Se tiver algum problema com algum produto comprado, a nossa equipa de apoio estará pronta a ajudá-lo. Contacte-nos sempre que necessitar, clicando no link acima para submeter o seu pedido de apoio.",
	"NEW_SUPPORT_REQUEST_MSG" => "Novo Pedido",
	"SUPPORT_REQUEST_ADDED_MSG" => "Obrigado<br>A nossa Equipa de Apoio tentará ajudá-lo, relativamente ao seu pedido, o mais brevemente possível.",
	"SUPPORT_SELECT_DEP_MSG" => "Seleccionar o Departamento",
	"SUPPORT_SELECT_PROD_MSG" => "Seleccionar o Produto",
	"SUPPORT_SELECT_STATUS_MSG" => "Seleccionar o Status",
	"SUPPORT_NOT_VIEWED_MSG" => "Não visualizado",
	"SUPPORT_VIEWED_BY_USER_MSG" => "Visualizado pelo utilizador",
	"SUPPORT_VIEWED_BY_ADMIN_MSG" => "Visualizado pelo administrador",
	"SUPPORT_STATUS_NEW_MSG" => "Novo",
	"NO_SUPPORT_REQUEST_MSG" => "Não foram encontrados assuntos",

	"SUPPORT_SUMMARY_COLUMN" => "Sumário",
	"SUPPORT_TYPE_COLUMN" => "Tipo",
	"SUPPORT_UPDATED_COLUMN" => "Última actualização",

	"SUPPORT_USER_NAME_FIELD" => "O Seu Nome",
	"SUPPORT_USER_EMAIL_FIELD" => "O Seu Endereço de E-mail",
	"SUPPORT_IDENTIFIER_FIELD" => "Identificador (Factura #)",
	"SUPPORT_ENVIRONMENT_FIELD" => "Ambiente (SO, Base de Dados, Web Server, etc.)",
	"SUPPORT_DEPARTMENT_FIELD" => "Departamento",
	"SUPPORT_PRODUCT_FIELD" => "Produto",
	"SUPPORT_TYPE_FIELD" => "Tipo",
	"SUPPORT_CURRENT_STATUS_FIELD" => "Status actual",
	"SUPPORT_SUMMARY_FIELD" => "Sumário de uma linha",
	"SUPPORT_DESCRIPTION_FIELD" => "Descrição",
	"SUPPORT_MESSAGE_FIELD" => "Mensagem",
	"SUPPORT_ADDED_FIELD" => "Adicionado",
	"SUPPORT_ADDED_BY_FIELD" => "Adicionado por",
	"SUPPORT_UPDATED_FIELD" => "Última Actualização",

	"SUPPORT_REQUEST_BUTTON" => "Submeter pedido",
	                                    
	"SUPPORT_MISS_ID_ERROR" => "Parâmetro <b>Support ID</b> em falta",
	"SUPPORT_MISS_CODE_ERROR" => "Parâmetro <b>Verification</b> em falta",
	"SUPPORT_WRONG_ID_ERROR" => "O parâmetro <b>Support ID</b> tem valor(es) errado(s)",
	"SUPPORT_WRONG_CODE_ERROR" => "O parâmetro <b>Verification</b> tem valor(es) errado(s)",

	"MAIL_DATA_MSG" => "Dados do Correio",
	"HEADERS_MSG" => "Cabeçalhos",
	"ORIGINAL_TEXT_MSG" => "Texto Original",
	"ORIGINAL_HTML_MSG" => "HTML Original",
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
