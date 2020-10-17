function showReviewForm (buttonObj) {
	var reviewsBlock = vaParent(buttonObj, "data-pb-type");
	if (reviewsBlock) {
	  var reviewFormBlock = reviewsBlock.querySelector(".review-form");
		var regExp = /expand-open/g;
		if (regExp.test(reviewFormBlock.className)) {
			reviewFormBlock.className = reviewFormBlock.className.replace(/expand-open/gi, "").trim();
		} else {
			reviewFormBlock.className = reviewFormBlock.className + " expand-open";
			// move scroller to the review form
			//var nLeft = 0, nTop = 0;
			//for (var oItNode = reviewFormBlock; oItNode; nLeft += oItNode.offsetLeft, nTop += oItNode.offsetTop, oItNode = oItNode.offsetParent);
			//document.documentElement.scrollTop = nTop;
			//document.documentElement.scrollLeft = nLeft;
		}
		// set focus on comments form area
		var reviewFormObj = reviewFormBlock.querySelector("form");
		if (reviewFormObj && reviewFormObj.comments) {
			reviewFormObj.comments.focus();
		}
	}
}

function sendReview(buttonObj)
{
	var isAjax = GetXmlHttpObject();
	var formObj = vaParent(buttonObj, "FORM");
	var pbId = formObj.pb_id.value;
	var pbType = formObj.pb_type.value;
	var pbObj = document.getElementById("pb_"+pbId);
	var reviewFormBlock; 
	if (pbType == "product_questions") {
		reviewFormBlock = document.getElementById("question_form_"+pbId);
	} else {
		reviewFormBlock = document.getElementById("review_form_"+pbId);
	}

	// check ajax parameter
	if (!formObj.ajax) {
		var ajaxObj = document.createElement('input'); ajaxObj.name = "ajax"; ajaxObj.type = "hidden"; formObj.appendChild(ajaxObj);
	}
	if (isAjax) {
		formObj.ajax.value = 1;
		// hide errors block
		var errorsObj = reviewFormBlock.querySelector(".error-block"); 
		errorsObj.className = "error-block";
		// hide message block
		var messageObj = reviewFormBlock.querySelector(".message-block");  
		messageObj.className = "message-block";

		var url = "block.php";

		vaSpin(formObj);
		postAjax(url, reviewFormResponse, formObj, formObj);
	} else {
		formObj.ajax.value = 0;
		formObj.submit();
	}
	return false;
}


function reviewFormResponse(response, formObj)
{
	vaStopSpin(formObj);
	var data; // save here parsed data
	try {
		data = JSON.parse(response);
	} catch(e) {
		alert(e + "=\n" + response); 
		return;
	}
	var pbId = data["pb_id"];
	var pbType = data["pb_type"];
	var itemId = data["item_id"];
	var reviewsId; var validationImage;	
	if (pbType == "product_questions") {
		reviewsId = "questions_" + pbId;
		validationImageSrc = "validation_image.php?id=ptqn-"+itemId;
	} else {
		reviewsId = "reviews_" + pbId;
		validationImageSrc = "validation_image.php?id=ptrw-"+itemId;
	}
	var reviewsObj = document.getElementById(reviewsId);
	var reviewFormBlock = reviewsObj.querySelector(".review-form");
	var validationInput = formObj.querySelector("[name=validation_number]");
	var fields = {"recommended": ".fd-recommended", "rating": ".fd-rating", "user_name": ".fd-user-name", "user_email": ".fd-user-email", "summary": ".fd-summary", "comments": ".fd-comments", "validation_number": ".fd-validation"};
	var errorsObj = reviewFormBlock.querySelector(".error-block"); 
	if (data.errors) {
		var errorsDesc = errorsObj.querySelector(".msg-desc"); 
		// show errors block
		errorsDesc.innerHTML = data.errors;
		errorsObj.className = "error-block show-block";
		// show fiels with errors
		for (fieldName in fields) {
			var fieldClass = fields[fieldName];
			var fdObj = formObj.querySelector(fieldClass);
			if (fdObj) { 
				if (!data[fieldName+"_valid"]) {
					fdObj.className = fdObj.className.replace(/hide-block/gi, "").trim(); // if validation block was hide show it
					fdObj.className = fdObj.className + " fd-error"; 
				} else {
					fdObj.className = fdObj.className.replace(/fd-error/gi, "").trim(); // remove error class 
				}
			}
		}

		// for validation number there are some additional steps are required
		if (!data["validation_number_valid"] && validationInput) {
			validationInput.value="";
			var validationImage = new Image();
			validationImage.src = validationImageSrc;
			var validationObj = formObj.querySelector(".fd-validation");
			var imageObj = validationObj.querySelector(".validation-image");
			imageObj.src = validationImage.src;
		}
	} else if (data.saved) {
		if (!data.errors) {
			errorsObj.className = "error-block"; // hide errors block if it was shown
		}
		// check if updated reviews/questions block was returned 
		if (data.block && data.html_id) {
			replaceBlock(data.block, data.html_id);
		} else {
			if (data.message) {
				var msgBlock = reviewFormBlock.querySelector(".message-block"); 
				var msgDesc = msgBlock.querySelector(".msg-desc"); 
				// show errors block
				msgDesc.innerHTML = data.message;
				msgBlock.className = "message-block show-block";
			}
			// reload validation image if it's available
			var validationObj = formObj.querySelector(".fd-validation");
			if (validationObj) {
				var validationImage = new Image();
				validationImage.src = validationImageSrc;
				var imageObj = validationObj.querySelector(".validation-image");
				imageObj.src = validationImage.src;
			}
		}

		// clear form values
		reviewsObj = document.getElementById(reviewsId); // get once more time if block was replaced
		var formObj = reviewsObj.querySelector("form"); 
		if (formObj.rating) {
			var stars = formObj.querySelectorAll("[data-rating]");
			for (var s = 0; s < stars.length; s++) {
				var rating = parseInt(stars[s].getAttribute("data-rating"));
				stars[s].className = stars[s].className.replace(/star-selected/gi, "").trim();
			}
			formObj.rating.value = "";
		}
		if (formObj.recommended) {
			for (var r = 0; r < formObj.recommended.length; r++) {
				formObj.recommended[r].checked = false;
			}
		}
		if (formObj.summary) { 
			formObj.summary.value = "";
		}
		if (formObj.comments) { 
			formObj.comments.value = "";
		}
		if (formObj.validation_number) { 
			formObj.validation_number.value = "";
			var fdObj = formObj.querySelector(".fd-validation");
			fdObj.className = fdObj.className.replace(/hide-block/gi, "").trim(); // if validation block was hide remove hide class
		}

		// if user can't add more reviews hide form and replace write buttons with appropriate message 
		if (!data.more_reviews) {
			var spanObj = document.createElement('span'); 
			if (pbType == "product_questions") {
				spanObj.className = "already-asked";
				spanObj.innerHTML = data.already_asked_msg;
			} else {
				spanObj.className = "already-reviewed";	
				spanObj.innerHTML = data.already_reviewed_msg;
			}
			var writeLinks = reviewsObj.querySelectorAll(".write-review"); 
			for (var l = 0; l < writeLinks.length; l++) {
				var linkObj = writeLinks[l];
				var parentLinkObj = linkObj.parentNode;
				parentLinkObj.insertBefore(spanObj, linkObj);
				parentLinkObj.removeChild(linkObj);
			}

			// hide form as user can't submit more reviews
			if (formObj.hasAttribute("class")) { 
				formObj.className = formObj.className + " hidden-block";
			} else {
				formObj.className = "hidden-block";
			}
		}
	}
}

function hideReviewError(elementObj)
{
	var reviewForm = vaParent(elementObj, ".review-form");
	var errorBlock = reviewForm.querySelector(".error-block");
	errorBlock.className = "error-block";
}

function hideReviewMessage(elementObj)
{
	var reviewForm = vaParent(elementObj, ".review-form");
	var messageBlock= reviewForm.querySelector(".message-block");
	messageBlock.className = "message-block";
}

function showReplyForm (reviewId) {
	var commentFormBlock = document.getElementById("comment-form-"+reviewId);
	var regExp = /expand-open/g;
	if (commentFormBlock) {
		if (regExp.test(commentFormBlock.className)) {
			commentFormBlock.className = commentFormBlock.className.replace(/expand-open/gi, "").trim();
		} else {
			commentFormBlock.className = commentFormBlock.className + " expand-open";
		}
		// set focus on comments form area
		var commentFormObj = commentFormBlock.querySelector("form");
		if (commentFormObj && commentFormObj.comment_comments) {
			commentFormObj.comment_comments.focus();
		}
	}
	// hide errors block
	var commentErrors = document.getElementById("comment-errors-"+reviewId);
	if (commentErrors) { commentErrors.style.display = "none"; }
	// hide message block
	var messageObj = document.getElementById("comment-message-"+reviewId); 
	messageObj.style.display = "none";
}

function sendComment(buttonObj)
{
	var isAjax = GetXmlHttpObject();
	var formObj = vaParent(buttonObj, "FORM");
	// check ajax parameter
	if (!formObj.ajax) {
		var ajaxObj = document.createElement('input'); ajaxObj.name = "ajax"; ajaxObj.type = "hidden"; formObj.appendChild(ajaxObj);
	}
	if (isAjax) {
		formObj.ajax.value = 1;
		var pbId = formObj.pb_id.value;
		var reviewId = formObj.review_id.value;
		// hide errors block
		var errorObj = document.getElementById("comment-errors-"+reviewId); 
		errorObj.style.display = "none";
		// hide message block
		var messageObj = document.getElementById("comment-message-"+reviewId); 
		messageObj.style.display = "none";

		var url = "block.php";

		vaSpin(formObj);
		//postAjax(url, replyFormResponse, pbId, formObj);
		postAjax(url, replyFormResponse, formObj, formObj);
	} else {
		formObj.ajax.value = 0;
	}
	return false;
}

function replyFormResponse(response, formObj)
{
	vaStopSpin(formObj);
	var data; // save here parsed data
	try {
		data = JSON.parse(response);
	} catch(e) {
		alert(e + "\n" + response); 
		return;
	}
	var reviewId = data["review_id"];
	var commentForm = document.getElementById("comment-form-"+reviewId);
	//var buttonObj = commentForm.querySelector(".bn-comment");
	var validationInput = commentForm.querySelector("[name=comment_validation_number]");
	var fields = {"user_name": ".fr-reply-user-name", "user_email": ".fr-reply-user-email", "comments": ".fr-reply-comments", "validation_number": ".fr-reply-validation"};
	if (data.errors) {
		var errorObj = document.getElementById("comment-errors-"+reviewId); 
		errorObj.innerHTML = data.errors;
		errorObj.style.display = "block";
		for (fieldName in fields) {
			var fieldClass = fields[fieldName];
			if (!data[fieldName+"_valid"]) {
				var fdObj = commentForm.querySelector(fieldClass);
				if (fdObj) { fdObj.className = fdObj.className + " fd-error"; }
			}
		}
		// for validation number there are some additional steps are required
		var fdObj = commentForm.querySelector("[name=comment_validation_number]");
		if (!data["validation_number_valid"] && validationInput) {
			validationInput.value="";
			var validationImage = new Image();
			validationImage.src = "validation_image.php?id=rw-"+reviewId;
			var imageObj = document.getElementById("comment-validation-image-"+ reviewId);
			imageObj.src = validationImage.src;
		}
	} else if (data.saved) {
		if (data.message) {
			var messageObj = document.getElementById("comment-message-"+reviewId); 
			messageObj.innerHTML = data.message;
			messageObj.style.display = "block";
		}
		if (data.show && data.reply) {
			// reply was automatically approved and we can show it
			var divObj = document.createElement('div'); 
			divObj.innerHTML = data.reply.trim(); 
			var replyObj = divObj.firstChild;
			commentForm.parentNode.insertBefore(replyObj, commentForm);
		}

		// clear errors if they were present
		for (fieldName in fields) {
			var fieldClass = fields[fieldName];
			var fdObj = commentForm.querySelector(fieldClass);
			if (fdObj) {
				fdObj.className = fdObj.className.replace(/fd-error/gi, "").trim();
			}
		}
		if (data.more_comments) {
			// clear form so user can add new comments
			var commentsInput = commentForm.querySelector("[name=comment_comments]");
			commentsInput.value = "";
			// show new validation image
			var validationBlock = commentForm.querySelector(".fr-reply-validation");
			if (validationBlock) {
				validationBlock.className = validationBlock.className.replace(/hidden-block/gi, "").trim();
				validationInput.value = "";
				var validationImage = new Image();
				validationImage.src = "validation_image.php?id=rw-"+reviewId;
				var imageObj = document.getElementById("comment-validation-image-"+ reviewId);
				imageObj.src = validationImage.src;
			}
		} else {
			// if user can't add more comments hide form
			var formObj = commentForm.querySelector("form");
			if (formObj.hasAttribute("class")) { 
				formObj.className = formObj.className + " hidden-block";
			} else {
				formObj.className = "hidden-block";
			}
			// hide also a comment button
			var reviewObj = vaParent(commentForm, "data-review-id", reviewId);
			if (reviewObj) {
				var commentButtonObj = reviewObj.querySelector(".ico-comment");
				if (commentButtonObj) {
					commentButtonObj.parentNode.removeChild(commentButtonObj);
				}
			}

		}
	}
}

function reviewEmotion(buttonObj)
{
	var parentBlock = vaParent(buttonObj, "data-review-id");
	var signUser = parentBlock.getAttribute("data-user");
	if (signUser != 1) {
		// forward user to login form if it isn't logged in 
		var signUrl = parentBlock.getAttribute("data-sign-url");
		window.location = signUrl;
	} else {
		var reviewId = parentBlock.getAttribute("data-review-id");
		var pbId = parentBlock.getAttribute("data-pb-id");
		var emotion = buttonObj.getAttribute("data-emotion");
		var url = "block.php?pb_id="+encodeURIComponent(pbId);
		url += "&pb_type=reviews";
		url += "&review_id="+encodeURIComponent(reviewId);
		url += "&emotion="+encodeURIComponent(emotion);
		url += "&operation=emotion&ajax=1";
		vaSpin(buttonObj);
		callAjax(url, emotionResponse, buttonObj);
	}
}

function emotionResponse(response, buttonObj)
{
	vaStopSpin(buttonObj);
	var data; // save here parsed data
	try {
		data = JSON.parse(response);
	} catch(e) {
		alert(e + "\n" + response); 
		return;
	}
	if (data.errors) {
		alert(data.errors);
	} else {
		var pbId = data["pb_id"];
		var reviewId = data["review_id"];
		var emotion = data["emotion"];
		var reviewsBlock = document.getElementById("pb_"+pbId);
		if (reviewsBlock) {
			var reviewObj = reviewsBlock.querySelector("[data-review-id='"+reviewId+"']");
			var likeObj = reviewObj.querySelector(".ico-like");
			var dislikeObj = reviewObj.querySelector(".ico-dislike");
			if (likeObj) { 
				if (emotion == "1") {
					likeObj.className = likeObj.className + " emotion-selected";
				} else {
					likeObj.className = likeObj.className.replace(/emotion-selected/gi, "").trim(); 
				}
			}
			if (dislikeObj) { 
				if (emotion == "-1") {
					dislikeObj.className = dislikeObj.className + " emotion-selected";
				} else {
					dislikeObj.className = dislikeObj.className.replace(/emotion-selected/gi, "").trim(); 
				}
			}
			var likesObj = reviewObj.querySelector(".ico-likes .value");
			var dislikesObj = reviewObj.querySelector(".ico-dislikes .value");
			if (likeObj) { likesObj.innerHTML = data["likes"]; }
			if (dislikesObj) { dislikesObj.innerHTML = data["dislikes"]; }
		}
	}
}
