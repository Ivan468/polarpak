var opens=[];
var isSel=0;
var bbtags   = new Array();
var myAgent   = navigator.userAgent.toLowerCase();
var myVersion = parseInt(navigator.appVersion);

var is_ie   = ((myAgent.indexOf("msie") != -1)  && (myAgent.indexOf("opera") == -1));
var is_nav  = ((myAgent.indexOf('mozilla')!=-1) && (myAgent.indexOf('spoofer')==-1)
&& (myAgent.indexOf('compatible') == -1) && (myAgent.indexOf('opera')==-1)
&& (myAgent.indexOf('webtv') ==-1)       && (myAgent.indexOf('hotjava')==-1));

var is_win   =  ((myAgent.indexOf("win")!=-1) || (myAgent.indexOf("16bit")!=-1));
var is_mac    = (myAgent.indexOf("mac")!=-1);

function cstat(fi){
	if (!fi){fi='';}
	var c = stacksize(bbtags);

	if ( (c < 1) || (c == null) ) {
		c = 0;
	}

	if ( ! bbtags[0] ) {
		c = 0;
	}
	eval('document.getElementById("tagcount'+fi+'").value='+c);
}

function stacksize(thearray){
	for (var i = 0 ; i < thearray.length; i++ ) {
		if ( (thearray[i] == "") || (thearray[i] == null) || (thearray == 'undefined') ) {
		return i;
		}
	}

	return thearray.length;
}

function pushstack(thearray,newval){
	arraysize = stacksize(thearray);
	thearray[arraysize] = newval;
}

function popstack(thearray){
	arraysize = stacksize(thearray);
	theval = thearray[arraysize - 1];
	delete thearray[arraysize - 1];
	return theval;
}

function closeall(area_id,editor_id){
	if (bbtags[0]) {
		try {
			while (bbtags[0]) {
				tagRemove = popstack(bbtags)
				document.getElementById(area_id).value += "[/" + tagRemove + "]";
				if ( (tagRemove != 'font') && (tagRemove != 'size') && (tagRemove != 'color') ){
					if (tagRemove=='code'){
						eval("document.getElementById('codes"+editor_id+"').value = ' " + tagRemove + " '");
					} else {
						eval("document.getElementById('"+tagRemove+editor_id+"').value = ' " + tagRemove + " '");
					}
					opens[tagRemove+fi]=0;
				}
			}
		} catch(e){}
	}

	eval('document.getElementById("tagcount'+editor_id+'").value=0');
	bbtags = new Array();
	document.getElementById(area_id).focus();
}


function set_font(tag_val,tag_name,area_id,editor_id){

	if(Insert_tag("[" + tag_name + "=" + tag_val + "]", "[/" + tag_name + "]",true,area_id))
	pushstack(bbtags,tag_name);

	cstat(editor_id);
}

function add_tag(tag_name,field_id,area_id,editor_id){
	if(!editor_id){editor_id = "";}
	var tagOpen;
	tagOpen = opens[tag_name+editor_id];
	var bracket1='[';
	var bracket2=']';
	var doClose = true;
	if (!tagOpen){
		if(Insert_tag(bracket1+tag_name+bracket2, bracket1+"/"+tag_name+bracket2,doClose,area_id)){
			opens[tag_name+editor_id]=1;	
			if (field_id=='code'){
				eval("document.getElementById('codes"+editor_id+"').value += '*'");
			}
			else {                        
				eval("document.getElementById('"+field_id+editor_id+"').value += '*'");
			}
			pushstack(bbtags,tag_name);
			cstat(editor_id);
		}
	} else {
		lastindex = 0;
		for (var i = 0 ; i < bbtags.length; i++ ){
			if ( bbtags[i] == tag_name ){
				lastindex = i;
			}
		}
		while (bbtags[lastindex]){
			tagRemove = popstack(bbtags);
			Insert_tag("[/" + tagRemove + "]", "",false,area_id)
			if ( (tagRemove != 'font') && (tagRemove != 'size') && (tagRemove != 'color') ){
				if (field_id=='code'){
					eval("document.getElementById('codes"+editor_id+"').value = '"+tagRemove+"'");
				}
				else {
					eval("document.getElementById('"+field_id+editor_id+"').value = '"+tagRemove+"'");
				}
				opens[tag_name+editor_id]=0;
			}
		}
		cstat(editor_id);
	}
}

function add_list(area_id){
	var listvalue = "init";
	var thelist = "";
	while ( (listvalue != "") && (listvalue != null) ){
		listvalue = prompt('List item', "");
		if ( (listvalue != "") && (listvalue != null) ){
			thelist = thelist+"[*]"+listvalue+"\n";
		}
	}
	if ( thelist != "" ){Insert_tag( "[list]\n" + thelist + "[/list]\n", "",false,area_id);}
}

function add_url(area_id){
	var enterURL  = prompt('Site address', "http://");
	var enterTITLE=Selected(area_id);
	if (enterTITLE.length==0){
		enterTITLE = prompt('Site name',"My WebPage"); 		
	}
	if (!enterURL || enterURL=='http://'){
		return;
	}else if (!enterTITLE) {
		return;
	}

	Insert_tag("[url="+enterURL+"]"+enterTITLE+"[/url]","",false,area_id);	
}

function add_image(area_id){
	var FoundErrors = '';
	var enterURL   = prompt('Image URL',"http://");

	if (!enterURL || enterURL=='http://' || enterURL.length<20) {
	return;
	}

	Insert_tag("[img]"+enterURL+"[/img]","",false,area_id);
}

function tag_email(area_id) {
	var emailAddress = prompt('E-mail address',"");

	if (!emailAddress) {return;}
	var enterTITLE=Selected(area_id);
	if (enterTITLE.length>0){
		Insert_tad("[email="+emailAddress+"]"+enterTITLE+"[/email]","",false,area_id);	
	}else {
		Insert_tag("[email]"+emailAddress+"[/email]","",false,area_id);	
	}

}

function Insert_tag(tag_beg,tag_end,is_single,area_id){
	var is_close = false;
	var obj_ta = document.getElementById(area_id);

	if ( (myVersion >= 4) && is_ie && is_win){ 
		if(obj_ta.isTextEdit){
			obj_ta.focus();
			var sel = document.selection;
			var rng = sel.createRange();
			rng.colapse;
			if((sel.type == "Text" || sel.type == "None") && rng != null){
				if(tag_end != "" && rng.text.length > 0)
					tag_beg += rng.text + tag_end;
				else if(is_single)
					is_close = true;
				rng.text = tag_beg;
			}
		}else{
			if(is_single)
				is_close = true;
			obj_ta.value += tag_beg;
		}
	}else try {
		var scr = obj_ta.scrollTop;

		var txtStart = obj_ta.selectionStart;
		if(!(txtStart >= 0)) throw 1;
		var txtEnd   = obj_ta.selectionEnd;
		if(tag_end != "" && obj_ta.value.substring(txtStart,txtEnd).length>0) {
			obj_ta.value = obj_ta.value.substring(0,txtStart) + tag_beg + obj_ta.value.substring(txtStart,txtEnd) + tag_end + obj_ta.value.substring(txtEnd,obj_ta.value.length);
		} else {
			if(is_single) is_close = true;  
			if (isSel==1){
				obj_ta.value = obj_ta.value.substring(0,txtStart) + tag_beg + obj_ta.value.substring(txtEnd,obj_ta.value.length);
			}else{
				obj_ta.value = obj_ta.value.substring(0,txtStart) + tag_beg +(is_single==3?tag_end:'')+ obj_ta.value.substring(txtStart,obj_ta.value.length);
			}
		}
		obj_ta.scrollTop=scr;
	} catch(e) {
		if(is_single){is_close = true;}
		obj_ta.value += tag_beg;
	}
	obj_ta.focus();
	return is_close;
}



function Selected(area_id){
	var obj_ta = document.getElementById(area_id);

	if ( (myVersion >= 4) && is_ie && is_win){
		if(obj_ta.isTextEdit){
			obj_ta.focus();
			var sel = document.selection;
			var rng = sel.createRange();
			rng.colapse;
			if((sel.type == "Text" || sel.type == "None") && rng != null){
				if(rng.text.length > 0){
					isSel=1;
					return rng.text;		
				}
			}
		}
		return '';
	}
	try {

		var txtStart = obj_ta.selectionStart;
		if(!(txtStart >= 0)) throw 1;
		var txtEnd   = obj_ta.selectionEnd;
		if(obj_ta.value.substring(txtStart,txtEnd).length>0) {
			isSel=1;
			return obj_ta.value.substring(txtStart,txtEnd);
		}
	} catch(e) {}
	return '';
}