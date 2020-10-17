var timeId = "";
var chatsBlinkId = "";

function initChat()
{
	var chatMessages = document.getElementById("chatMessages");
	var chatsWaiting = document.getElementById("chatsWaiting");
	if (chatMessages) {
		// move scroll to the bottom
		chatMessages.scrollTop = chatMessages.scrollHeight;
		// check messages every 5 seconds
		checkMessages();
	} else if (chatsWaiting) {
		// check new chats every 10 seconds and update admin status
		checkChats();
	}
}

function openChatWindow(pagename, chatId, operation)
{                 
	var chatWinName = "chat_" + chatId;
	var pageUrl = pagename;
	if (chatId && chatId != "") {
		pageUrl += '?chat_id=' + encodeURIComponent(chatId);
		if (operation && operation != "") {
			pageUrl += "&operation=" + operation;
		}
	}
	var chatWin = window.open (pageUrl, chatWinName, 'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=600,height=400');
	chatWin.focus();
}

function checkMessages()
{
	var formObject = document.chat_message;
	var chatUrl = formObject.action;
	chatUrl += "?ajax=1";
	if (formObject.chat_id) {
		chatUrl += "&chat_id=" + encodeURIComponent(formObject.chat_id.value);
	}
	if (formObject.last_message_id) {
		chatUrl += "&last_message_id=" + encodeURIComponent(formObject.last_message_id.value);
	}

	callAjax(chatUrl, checkResponse, "");
	if (timeId) { clearTimeout(timeId); }
	timeId = setTimeout("checkMessages()", 5000); // 5 seconds
}

function checkChats()
{
	var formObject = document.chats_waiting;
	var chatUrl = formObject.action;
	chatUrl += "?ajax=1";
	callAjax(chatUrl, updateChats, "");
	if (timeId) { clearTimeout(timeId); }
	timeId = setTimeout("checkChats()", 10000); // 10 seconds
}


function updateChats(chatsData)
{
	var chatsWaiting = document.getElementById("chatsWaiting");
	chatsWaiting.innerHTML = chatsData;
	// check number of chats and if window not active to warn administrator about new chats
	var formObject = document.chats_waiting;
	var chatsNumber = parseInt(formObject.chats.value);
	if (chatsNumber > 0 && !document.hasFocus()) {
		if (chatsBlinkId) { clearTimeout(chatsBlinkId); }
		newChatsBlink();
	}
}

function newChatsBlink()
{
	var currentTitle = document.title;
	var formObject = document.chats_waiting;
	var chatsNumber = parseInt(formObject.chats.value);
	var docTitle = formObject.support_live_msg.value;
	var chatsWaitingMsg = formObject.chats_waiting_msg.value;
	var blinkMsg = "................................";
	if (chatsNumber > 0) {
		chatsWaitingMsg = chatsWaitingMsg.replace("\{number\}", chatsNumber);
		if (document.hasFocus()) {
			docTitle = chatsWaitingMsg; 
		} else {
			docTitle = (currentTitle == blinkMsg) ? chatsWaitingMsg : blinkMsg;
			chatsBlinkId = setTimeout("newChatsBlink()", 500);
		}
	}
	document.title = docTitle;
}

function sendMessage()
{
	var formObject = document.chat_message;
	var newMessage = formObject.new_message.value;
	if (newMessage == "") {
		alert("Your message is empty.");
	} else {
		var chatPage = document.chat_message.action;
		postAjax(chatPage, checkResponse, "", formObject);
		formObject.new_message.value = "";
	}
}

function checkResponse(responseData)
{
	var formObject = document.chat_message;
	if (responseData) {

		var chatMessages = document.getElementById("chatMessages");
		var templateObj = document.getElementById("message_template");
		var templateHTML = templateObj.innerHTML;

		var chatEvents = eval('('+responseData+')');
		var lastMessageId =  document.chat_message.last_message_id.value;
		for(var id in chatEvents) {
			var eventInfo = chatEvents[id];
			var eventName = eventInfo["event"];
			var messageId = eventInfo["id"];
			var messageType = eventInfo["message_type"];
			var messageText = eventInfo["message_text"];
			var messageTime = eventInfo["message_time"];
			var isUserMessage = eventInfo["is_user_message"];
			var authorName = eventInfo["author_name"];
			var authorShort = eventInfo["author_short"];
			if (messageId > lastMessageId) { lastMessageId = messageId; }

			// check if message shown on page already
			var messageObj = document.getElementById("message_"+messageId);

			if (!messageObj) {
				// build message HTML
				var messageHTML = templateHTML;
				messageHTML = messageHTML.replace("\{author_name\}", authorName);
				messageHTML = messageHTML.replace("\{author_short\}", authorShort);
				messageHTML = messageHTML.replace("\{message_text\}", messageText);
				messageHTML = messageHTML.replace("\{message_time\}", messageTime);

				messageObj = document.createElement("div");
				messageObj.id = "message_" + messageId;
				if (messageType == "2" || messageType == "3") {
					messageObj.className = "systemMessage";
				} else if (isUserMessage == "1") {
					messageObj.className = "userMessage";
				} else {
					messageObj.className = "adminMessage";
				}

				messageObj.innerHTML = messageHTML;

				chatMessages.appendChild(messageObj);

				// if closed message received disable send button and message textarea
				if (messageType == "3") {
					formObject.new_message.disabled = true;
					formObject.send_message.disabled = true;
				}
			}
		}
		document.chat_message.last_message_id.value =  lastMessageId;
		// move scroll to bottom
		chatMessages.scrollTop = chatMessages.scrollHeight;
	}
}

function closeChat()
{
	var formObject = document.chat_message;
	var chatUrl = formObject.action;
	chatUrl += "?operation=close";
	if (formObject.chat_id) {
		chatUrl += "&chat_id=" + encodeURIComponent(formObject.chat_id.value);
	}
}

function moveToOffline(supportUrl)
{
	if (window.opener) {
		window.opener.location = supportUrl;
	}
	window.close();
}


