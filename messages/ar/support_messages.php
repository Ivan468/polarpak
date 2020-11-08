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
	"SUPPORT_TITLE" => "مركز الدعم",
	"SUPPORT_REQUEST_INF_TITLE" => "طلب معلومات",
	"SUPPORT_REPLY_FORM_TITLE" => "رد",
	"SUPPORT_SHORT_TITLE" => "Support",

	"MY_SUPPORT_ISSUES_MSG" => "طلباتي للدعم",
	"MY_SUPPORT_ISSUES_DESC" => ".اذا واجهتك اية مشكله في اي من منتجاتنا, لا تتردد في الاتصال بمدير الدعم الفني عبر الضغط على الرابط في الأعلى ثم ارسال طلب دعم",
	"NEW_SUPPORT_REQUEST_MSG" => "طلب جديد",
	"SUPPORT_REQUEST_ADDED_MSG" => "شكراً لك, فريق الدعم الفني سيهتم بمشكلتك و سيقوم بالرد عليك في اقرب فرصه",
	"SUPPORT_SELECT_DEP_MSG" => "اختر الدائره",
	"SUPPORT_SELECT_PROD_MSG" => "اختر المنتج",
	"SUPPORT_SELECT_STATUS_MSG" => "اختر الحاله",
	"SUPPORT_NOT_VIEWED_MSG" => "لم تتم مشاهدته",
	"SUPPORT_VIEWED_BY_USER_MSG" => "تمت مشاهدته من قبل الزبون",
	"SUPPORT_VIEWED_BY_ADMIN_MSG" => "تمت مشاهدته من المدير العام",
	"SUPPORT_STATUS_NEW_MSG" => "جديد",
	"NO_SUPPORT_REQUEST_MSG" => "لا توجد اية طلبات.",

	"SUPPORT_SUMMARY_COLUMN" => "الملخص",
	"SUPPORT_TYPE_COLUMN" => "النوع",
	"SUPPORT_UPDATED_COLUMN" => "آخر تحديث",

	"SUPPORT_USER_NAME_FIELD" => "اسمك",
	"SUPPORT_USER_EMAIL_FIELD" => "بريدك الإلكتروني",
	"SUPPORT_IDENTIFIER_FIELD" => "(المعرف (رقم الفاتوره",
	"SUPPORT_ENVIRONMENT_FIELD" => "البيئه",
	"SUPPORT_DEPARTMENT_FIELD" => "الدائره",
	"SUPPORT_PRODUCT_FIELD" => "المنتج",
	"SUPPORT_TYPE_FIELD" => "النوع",
	"SUPPORT_CURRENT_STATUS_FIELD" => "الحاله الحاليه",
	"SUPPORT_SUMMARY_FIELD" => "ملخص قصير",
	"SUPPORT_DESCRIPTION_FIELD" => "الشرح",
	"SUPPORT_MESSAGE_FIELD" => "الرساله",
	"SUPPORT_ADDED_FIELD" => "مضاف",
	"SUPPORT_ADDED_BY_FIELD" => "مضاف بواسطة",
	"SUPPORT_UPDATED_FIELD" => "آخر تحديث",

	"SUPPORT_REQUEST_BUTTON" => "ارسل الطلب",
	                                    
	"SUPPORT_MISS_ID_ERROR" => "رقم طلب الدعم مفقود",
	"SUPPORT_MISS_CODE_ERROR" => "رقم التأكيد مفقود",
	"SUPPORT_WRONG_ID_ERROR" => "رقم طلب الدعم يحمل قيمه خاطئه",
	"SUPPORT_WRONG_CODE_ERROR" => "رقم التأكيد يحمل قيمه خاطئه",

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
