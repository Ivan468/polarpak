function vaPlayerReadyPlay(event)
{
	event.target.playVideo();
}

function vaPlayerState(event)
{
	if (event.data == -1) {
		vaInitLyrics(event); // load lyrics if it's available before playing video

		// set video name, check previous and next videos
		if (player.playlistId && player.playlistSong) { 
			// set playlist video name 
			var songName = player.playlistSong.getAttribute("data-name");
			var nameObj = document.querySelector(".youtube-name");
			if (nameObj) { nameObj.innerHTML = songName; }
			// check for previous and next videos
			var prevVideo, nextVideo;
			var plObj = document.querySelector("[data-playlist=\""+player.playlistId+"\"]");
			if (plObj) {
				var plVideos = plObj.querySelectorAll("[data-video]");
				if (plVideos && plVideos.length > 0) {
					for (var v = 0; v < plVideos.length; v++) {
						if (player.playlistSong === plVideos[v]) {
							// check prev video
							if (v == 0) {
								prevVideo = plVideos[plVideos.length - 1];
							} else {
								prevVideo = plVideos[v - 1];
							}
							// check next video
							if (v == (plVideos.length - 1)) {
								nextVideo = plVideos[0];
							} else {
								nextVideo = plVideos[v + 1];
							}
							break;
						}
					}
				}
			}
			if (prevVideo) {
				songName = prevVideo.getAttribute("data-name");
				nameObj = document.querySelector(".youtube-prev");
				if (nameObj) { nameObj.title = songName; }
			}
			if (nextVideo) {
				songName = nextVideo.getAttribute("data-name");
				nameObj = document.querySelector(".youtube-next");
				if (nameObj) { nameObj.title = songName; }
			}
			player.playlistPrev = prevVideo;
			player.playlistNext = nextVideo;
		}

	} else if (event.data == YT.PlayerState.PLAYING) {
    lyricsTid = setTimeout(vaShowLyrics, 100);
	} else if (event.data == YT.PlayerState.PAUSED) {
		vaPlayerPause();
		if (lyricsTid) { clearTimeout(lyricsTid); }
	} else if (event.data == YT.PlayerState.ENDED) {
		if (lyricsTid) { clearTimeout(lyricsTid); }
		if (player.playlistNext) { // if playlist has next load it 
			var videoId = player.playlistNext.getAttribute("data-youtube-video");
			lyrics = null; // clear lyrics
			player.loadVideoById(videoId);
			player.playlistSong = player.playlistNext;
		}
	} else {	
		if (lyricsTid) { clearTimeout(lyricsTid); }
	}
}

function vaPlayerError(event)
{
	// check for next video
	vaNextVideo();
}

function vaPrevVideo()
{
	if (player.playlistPrev) { // if playlist has previous video load it 
		var videoId = player.playlistPrev.getAttribute("data-youtube-video");
		lyrics = null; // clear lyrics
		player.loadVideoById(videoId);
		player.playlistSong = player.playlistPrev;
	}
}

function vaNextVideo()
{
	if (player.playlistNext) { // if playlist has next load it 
		var videoId = player.playlistNext.getAttribute("data-youtube-video");
		lyrics = null; // clear lyrics
		player.loadVideoById(videoId);
		player.playlistSong = player.playlistNext;
	}
}

							function vaAdminPlayerState(event)
							{
								vaInitLyrics(event);
								vaPlayerState(event);
							}

							function vaPlayerPause()
							{
								var playTime = player.getCurrentTime();
								if (document.record && document.record.start_value_min && document.record.start_value_sec) {
									var timeMin = Math.floor(playTime/60);
									var timeSec = Math.round((playTime - (60*timeMin))*10)/10;
									document.record.start_value_min.value = timeMin;
									document.record.start_value_sec.value = timeSec;
									if (document.record.stop_value_min && document.record.stop_value_sec) {
										document.record.stop_value_min.value = timeMin;
										document.record.stop_value_sec.value = timeSec;
									}
								}
							}

							function vaShowLyrics()
							{
								var ytVideo = document.querySelector(".youtube-video");
								var frameObj = document.getElementById("youtube-player");
								// check for fullscreen mode 
								var zIndex = window.getComputedStyle(frameObj, null).getPropertyValue("z-index");
								var framePosition = window.getComputedStyle(frameObj, null).getPropertyValue("position");
								if (zIndex != "auto" || framePosition != "static") {
									ytVideo.className = "youtube-video fullscreen";
								} else {
									ytVideo.className = "youtube-video";
								}

								var playTime = player.getCurrentTime();
								var lyricsIndex = ""; var lyricsText = ""; 
								if (lyrics) {
									for (var l = 0; l < lyrics.length; l++) {
										var lyricsData = lyrics[l];
										if (playTime >= lyricsData["start-time"] && playTime <= lyricsData["stop-time"]) {
											lyricsIndex = l;
											lyricsText = lyricsData["text"]
											break;
										}
									}
								}
								var ytText = document.querySelector(".youtube-text");
								ytText.innerHTML = lyricsText;

			          lyricsTid = setTimeout(vaShowLyrics, 100);
							}

							function vaInitLyrics(event)
							{
								var videoId = event.target.getVideoData().video_id;
								//c = [object HTMLIFrameElement] other
								//a = [object HTMLIFrameElement]
								//l = [object HTMLDivElement]
								var videoDiv;
								// check for IFRAME object
								if (event.target) {
									for(var elKey in event.target ) {
										var elObj = event.target[elKey];
										if (elObj.tagName && elObj.tagName == "IFRAME")  {
											videoDiv = elObj.parentNode; break;
										}
									}
								}
								//var videoDiv = (event.target && event.target.c) ? event.target.c.parentNode : document;
								if (!videoDiv) { videoDiv = document.querySelector(".youtube-video"); }
								// check for necessary lyrics containers
								var ytBottom = videoDiv.querySelector(".youtube-bottom");
								if (!ytBottom) {
									ytBottom = document.createElement("div");
									ytBottom.className = "youtube-bottom";
									videoDiv.appendChild(ytBottom);
								}
								var ytText = videoDiv.querySelector(".youtube-text");
								if (!ytText) {
									ytText = document.createElement("div");
									ytText.className = "youtube-text";
									ytBottom.appendChild(ytText);
								}
								// load lyrics into array and check lyrics settings
								var lyricsObj = document.querySelector("[data-video-id='"+videoId+"']");
								if (!lyricsObj) {
									// check default video element
									lyricsObj = document.getElementById("youtube-lyrics");
									if (lyricsObj) {
										if (lyricsObj.hasAttribute("data-video-id")) {
											lyricsVideoId = lyricsObj.getAttribute("data-video-id");
											if (videoId != lyricsVideoId) { return false; }
										} else {
											lyricsObj.setAttribute("data-video-id", videoId);
										}
									}
								}
								// lyrics wasn't found
								if (!lyricsObj) { return false; }
								var dataColor = lyricsObj.getAttribute("data-color");
								var dataBG = lyricsObj.getAttribute("data-bg");
								if (dataColor) { ytText.style.color = dataColor; }
								if (dataBG) { ytText.style.background = dataBG; }

								var lyricsParts = lyricsObj.querySelectorAll("[data-start]");
								if (lyricsParts && lyricsParts.length && lyricsParts.length > 0) {
									lyrics = [];
									var lyricsSort = []; var lyricsKeys = {};
									for (var lp = 0; lp < lyricsParts.length; lp++) {
										var lyricsPart = lyricsParts[lp];
										var dataStart = lyricsPart.getAttribute("data-start");
										var dataStop = lyricsPart.hasAttribute("data-stop") ? lyricsPart.getAttribute("data-stop") : "";
										var dataStarts = dataStart.split(',');
										var dataStops = dataStop.split(',');
										for (var sp = 0; sp < dataStarts.length; sp++) {
											var startTime = parseFloat(dataStarts[sp]);
											var stopTime = (dataStops[sp]) ? parseFloat(dataStops[sp]) : "";
											if (isNaN(stopTime)) { stopTime = ""; }
											// if it's correct start time and it wasn't set before
											if (!isNaN(startTime) && !lyricsKeys[startTime]) { 
												lyricsSort.push(startTime); 
												lyricsKeys[startTime] = {"index": sp, "start-time": startTime, "stop-time": stopTime, "data-start": dataStart, "data-stop": dataStop, "text": lyricsPart.innerHTML};
											}
										}
									}
									// sort data and save to lyrics array
									lyricsSort.sort(function (a, b) { return a - b ; });
									for (var ls = 0; ls < lyricsSort.length; ls++) {
										var startTime = lyricsSort[ls];
										var lyricsData = lyricsKeys[startTime];
										var stopTime = lyricsData["stop-time"];
										if (lyricsSort[ls+1]) {
											nexStartTime = lyricsSort[ls+1];
											if (!stopTime || stopTime > nexStartTime) {
												stopTime = nexStartTime - 0.1;
											}
										} else if (!stopTime) {
											stopTime = player.getDuration();
										}
										lyrics.push({"start-time": startTime, "stop-time": stopTime, "text": lyricsData["text"]});
									}
								}
							}

function vaDisablePopupVideo()
{
	var videoObj = document.querySelector(".playlist-video-popup");
	if (videoObj) {
		videoObj.className = videoObj.className.replace(/play-mode/gi, "").trim();
		var youtubePlayer = document.getElementById("youtube-player");
		if (youtubePlayer) {
			youtubePlayer.parentNode.removeChild(youtubePlayer);
		}
	}
}

function flashplayer(url,image, width, height, start, swf_url)
{
	document.write('	<embed src="' + swf_url + 'swf/flvplayer.swf?file=' + url + '&image=' + image + '&autoplay=' + start + '" \n');
	document.write('		id="flashplayer" \n');
	document.write('		quality="high" \n');
	document.write('		width=' + width + ' \n');
	document.write('		allowscriptaccess="always"\n');
	document.write('		allowfullscreen="true"\n');
	document.write('		height=' + height + '\n');
	document.write('		name="flashplayer" \n');
	document.write('		pluginspage="http://www.macromedia.com/go/getflashplayer" />\n');
	document.write('	</embed>\n');
}

function mediaplayer(url, width, height, start)
{
	document.write('<OBJECT id="mediaPlayer" width="' + width + '" height="' + height + '" align="left"\n');
	document.write('	classid="CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95"\n');
	document.write('	codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701"\n');
	document.write('	standby="Loading Microsoft Windows Media Player components..." type="application/x-oleobject">\n');
	document.write('	<param name="fileName" value="' + url + '">\n');
	document.write('	<param name="animationatStart" value="true">\n');
	document.write('	<param name="transparentatStart" value="false">\n');
	document.write('	<param name="autoStart" value="' + start + '">\n');
	document.write('	<param name="showControls" value="true">\n');
	document.write('	<param name="loop" value="false">\n');
	document.write('	<EMBED type="application/x-mplayer2"\n');
	document.write('		pluginspage="http://microsoft.com/windows/mediaplayer/en/download/"\n');
	document.write('		id="mediaPlayer" name="mediaPlayer"\n');
	document.write('		bgcolor="darkblue" showcontrols="true"\n');
	document.write('		showdisplay="0" animationatStart="true"\n');
	document.write('		transparentatStart="false"\n');
	document.write('		width="' + width + '"\n');
	document.write('		height="' + height + '"\n');
	//document.write('		align="left"\n');
	document.write('		src="' + url + '"\n');
	document.write('		autostart="' + start + '"\n');
	document.write('		loop="false">\n');
	document.write('	</EMBED>\n');
	document.write('</OBJECT>\n');
}
