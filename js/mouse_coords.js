function mouseCoords(e) {
	var l = 0, t = 0;
	var leftPos = 20;			//Customize left offset
	var topPos = 150*(-1);		//Customize top offset
	var rightPos = 150*(-1);		//Customize right offset
	var divBox = document.getElementById("div_popupBox");
	divBox ? divBox.style.display='block' : divBox.style.display='none';
	
	if (!e) var e = window.event;		// Set your cursor coordinates
	if (e.clientX || e.clientY) {
		l = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
		t = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
	} else {
		l = e.pageX;
		t = e.pageY;
	}
	
	windowWidth = document.documentElement.offsetWidth * 0.60;
	windowHeight = document.documentElement.offsetHeight * 0.70;
	if(l > windowWidth)	{
		divBox.style.left = l + divBox.offsetWidth*(-1) + rightPos+"px"
	} else	{
		divBox.style.left = l + leftPos+"px";
	}
	if(t > windowHeight) {
		divBox.style.top = t + divBox.offsetHeight*(-1)+"px"
	} else	{
		divBox.style.top = t + topPos+"px";
	}
	return true;
	
}

function displayHint(item_id)	{	//Show div popup for product
	var itemHint = document.getElementById(item_id)
	if(itemHint.style.display != 'block')
		itemHint.style.display = 'block';
	else
		itemHint.style.display = 'none';
}