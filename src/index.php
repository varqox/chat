<?php
// echo "<pre>Du bist getrollen\n";
// echo date("Y-m-d H:i:s"), "\n";
// print_r(strtotime(date("Y-m-d H:i:s")));
// echo "\n";
// print_r(date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s"))));
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Chat test</title>
	<script type="text/javascript" src="jquery.js"></script>
	<style>
	div:after{
	content: ".";
	display: block;
	clear: both;
	visibility: hidden;
	line-height: 0;
	height: 0;
	}
	div{
		display: block;
	}
	html[xmlns] div{
		display: block;
	}
	.chat
	{
		font-family: sans-serif;
		font-size: 15px;
		margin-left: auto;
		margin-right: auto;
		border: 2px solid #aaaaaa;
		border-radius: 4px;
		width: 800px;
		height: auto;
	}
	.chat textarea
	{
		font-family: inherit;
		overflow: auto;
		resize: none;
		width: 784px;
		margin: 0 10px 3px 3;
	}
	.chatbox
	{
		padding-left: 10px;
		border-bottom: 2px solid #aaaaaa;
		margin: 0px 0 3px 0;
		overflow-y: scroll;
		height: 500px;
		/*width: 300px;*/
	}
	.chatbox div
	{
		border: 1px solid #b3b3b3;
		border-radius: 5px;
		padding-left: 4px;
		margin: 2px 5px 2px 0;
	}
	.chatbox .user
	{
		font-weight: bold;
		font-size: 17px;
	}
	.chatbox .time
	{
		padding-left: 10px;
		font-size: 12px;
		font-style: italic;
	}
	.chatbox pre
	{
		margin: 0;
		font-family: inherit;
		font-size: 15px;
		padding: 2px 2px 0 0;
		word-break: break-word;
		white-space: pre-wrap; /* css-3 */
		white-space: -moz-pre-wrap; /* Mozilla, since 1999 */
		white-space: -pre-wrap; /* Opera 4-6 */
		white-space: -o-pre-wrap; /* Opera 7 */
		word-wrap: break-word;
		overflow: hidden;
		border: 1px solid #ffffff;
		-webkit-border-radius: 4px;
		-moz-border-radius: 4px;
		border-radius: 4px;
	}
	.code
	{
		background-color:#f8f8f8;
		border: 1px solid #ddd;
		font-size: 13px;
		line-height: 19px;
		overflow: auto;
		padding: 6px 10px;
		-webkit-border-radius: 4px;
		-moz-border-radius: 4px;
		border-radius: 4px;
	}
	.extended{
		height: 140px;
	}
	.emoticon{
		height: 13px;
	}
	</style>
	<script type="text/javascript">

	function fill_to_width(text, size)
	{
		text=new String(text);
		return Array(size-text.length+1).join('0')+text;
	}

	function MessageObj(date, user, text)
	{
		this.date=date;
		this.user=user;
		this.text=text;
	}

	function getCookie(cname)
	{
		var name = cname + "=";
		var ca = document.cookie.split(';');
		for(i=0; i<ca.length; i++)
		{
			var c = ca[i].trim();
			if(c.indexOf(name)==0)
				return c.substring(name.length,c.length);
		}
		return "";
	}

	function setCookie(cname, cvalue, exseconds, path)
	{
		var d = new Date();
		d.setTime(d.getTime()+(exseconds*1000));
		var expires = "expires="+d.toGMTString();
		document.cookie = cname + "=" + cvalue + "; " + expires + "; path=" + path;
	}
	function show_more(i)
	{
		if ($('#'+i+' > pre').hasClass("extended"))
			{$('#'+i+' > button').html("show less");}
		else
			{$('#'+i+' > button').html("show more");}
		$('#'+i+' > pre').toggleClass("extended");
	}
	var from_each = 0, user, currentScroll, refresh_busy=false;
	function refresh()
	{
		// var success,
		// d=Math.floor((new Date()).getTime()/1000);
		// if(d==old_time) return;

		if(refresh_busy)
			return;
		else
			refresh_busy=true;
		$.get("get.php?from_each="+from_each).success(function(responseText, textStatus, XMLHttpRequest)
		{
			$('#h').text((new Date()).toString());
			var NM;
			try {NM=JSON.parse(responseText);}
			catch (e) {
				console.error("Parsing error:", e);
				$('#conn_error').css("display", "block");
				refresh_busy=false;
				return;
			}
			var chatbox=document.getElementsByClassName('chatbox')[0];
			// $('#h').append(chatbox.scrollHeight+' '+chatbox.clientHeight+' '+chatbox.scrollTop);
			var scrollToBottom=(chatbox.scrollHeight-chatbox.clientHeight==chatbox.scrollTop);
			// $('#h').append(' -> '+scrollToBottom);
			if (NM.clear)
			{
				$('.chatbox').empty();
				playBeep('clean.wav');
				from_each=0;
			}
			else if(NM.count)
				playBeep('error.wav');
			for(i=0; i<NM.count; ++i)
			{
				if (NM.chat[i].user!="SYSTEM")
				$('.chatbox').append("<div id='"+from_each++ +"' style='display:none'><span class=\"user\">"+NM.chat[i].user+'</span><span class="time">'+NM.chat[i].date+'</span><br><pre>'+parse(NM.chat[i].text)+"</pre></div>");
				else
				$('.chatbox').append("<div id='"+from_each++ +"' style='display:none'><span class=\"user\">"+NM.chat[i].user+'</span><span class="time">'+NM.chat[i].date+'</span><br><pre>'+NM.chat[i].text+"</pre></div>");
				//console.log($(('#'+(from_each-1))).height());
				if($(('#'+(from_each-1))).height()>150)
				{
					$(('#'+(from_each-1))+' > pre').addClass('extended');
					$(('#'+(from_each-1))).append('<button onclick="show_more('+(from_each-1)+')" style="margin-top: 3px">show more</button>');
				}
				$(('#'+(from_each-1))).fadeIn(1000);
			}
			// if scroll was at bottom we move it to bottom back
			if(scrollToBottom)
				chatbox.scrollTop=chatbox.scrollHeight-chatbox.clientHeight;
			refresh_busy=false;
			$('#conn_error').css("display", "none");
		}).error(function ()
		{
			$('#conn_error').css("display", "block");
			refresh_busy=false;
		});
	}
	setInterval(refresh, 200);

	function addMessage()
	{
		var XHR = new XMLHttpRequest(),
		FD = new FormData();
		// We define what will happen if the data are successfully sent
		/*XHR.addEventListener("load", function(event) {
		  alert(event.target.responseText);
		});*/

		var d = new Date(), text=document.getElementById('addedText');

		FD.append("message", JSON.stringify(new MessageObj(d.getFullYear()+'-'+fill_to_width(d.getMonth()+1, 2)+'-'+fill_to_width(d.getDate(), 2)+' '+fill_to_width(d.getHours(), 2)+':'+fill_to_width(d.getMinutes(), 2)+':'+fill_to_width(d.getSeconds(), 2), user, text.value)));

		// We define what will happen in case of error
		XHR.addEventListener("error", function(event)
		{
		  alert('Oups! Something goes wrong.');
		});
		XHR.open("POST", "get.php", true);
		XHR.send(FD);
		text.value='';
		return false;
	}
	function objToString(obj)
	{
	    var str='';
	    for(var p in obj)
	    {
	        if(obj.hasOwnProperty(p))
	            str+=p+'::'+obj[p]+'\n';
	    }
	    return str;
	}
	function controlArea(event)
	{
		if(event.keyCode===13 && event.shiftKey===false)
		{
			addMessage();
			event.preventDefault();
		}
	}
	function startup()
	{
		var tmp;
		if((tmp=getCookie('chat_name'))==="")
		{
			var name=prompt("Please enter your name", "guest");
			user=name;
			setCookie('chat_name', user, 60*60*4);
		}
		else
			user=tmp;
		$('#user_name').text(user);
		refresh();
	}
	function logout()
	{
		setCookie('chat_name','',0);
		user="";
		$('#user_name').text('');
	}
	function strcmp(s1, pos, s2)
	{
		if(pos + s2.length > s1.length)
			return false;
		for(var i=0; i<s2.length; ++i)
			if(s1[pos+i] != s2[i])
				return false;
		return true;
	}
	function safe_char(c)
	{
		switch(c)
		{
			case '&': return '&amp;';
			case '<': return '&lt;';
			case '>': return '&gt;';
		}
		return c;
	}
	function playBeep(sound)
	{
		document.getElementById('chatbeep').innerHTML='<audio autoplay="autoplay" src="'+sound+'" type="audio/wav"><embed src="'+sound+'" hidden="true" autostart="true" loop="false" /></audio>';
	}
	function parse(text)
	{
		var result = String();
		for(var i=0; i<text.length; ++i)
		{
			if(text[i] == '\\' && i+1<text.length)
				result += text[++i];
			else if(strcmp(text, i, ":-)"))
			{
				result+= '<img src="emoticons/smile.gif" alt=":-)" class="emoticon" />';
				i+=2;
			}
			else if(strcmp(text, i, ":)"))
			{
				result+= '<img src="emoticons/smile.gif" alt=":)" class="emoticon" />';
				i+=1;
			}
			else if(text[i] == '[')
			{
				if(strcmp(text, i+1, "code]"))
				{
					i += 5;
					result += '<pre class="code">';
					while(++i < text.length && !(text[i] == '[' && strcmp(text, i+1, "/code]")))
						result += safe_char(text[i]);
					i += 6;
					result += '</pre>';
				}
				else if(strcmp(text, i+1, "ok]"))
				{
					i+=3;
					result += '<div style="font-size: 30px;background:#00BF00;width:70px">OK</div>';
				}
				else if(strcmp(text, i+1, "fuck]"))
				{
					i+=5;
					result += '<div style="font-size: 30px;background:red;width:70px">Fuck</div>';
				}
				else if(strcmp(text, i+1, "a]"))
				{
					i += 2;
					result += '<a href="';
					var x = String();
					while(++i < text.length && !(text[i] == '[' && strcmp(text, i+1, "/a]")))
						x += safe_char(text[i]);
					i += 3;
					result += x + '">'+x+'</a>';
				}
				else
					result += '[';
			}
			else
				result += safe_char(text[i]);
		}
		return result;
	}
</script>
</head>
<body onload="startup()"style="font-family:'DejaVu Sans'">
<p>Last refresh: <span id='h'></span><p>
<div id='conn_error' style="display:none;width:400px;height:80px;background:rgba(255,0,0,0.8);font-size:55px;border-radius:5px;text-align:center;position:fixed;top: 50%;left: 50%;margin-top: -40px;margin-left: -200px;"><center style="padding-top: 5px;height:80px;">Fuck you</center></div>
<div class="chat">
<span id="chatbeep"></span>
<div class="chatbox">
</div>
<div style="padding: 0 0 0 5px">
<textarea id="addedText" onkeypress="controlArea(event)"></textarea>
</div>
<span>Your name: <span id="user_name">aaa</span></span><br>
<!-- <button id="submit" onclick="addMessage()">Submit</button> -->
<a href='#' onclick="logout()">Logout</a>
</div>
</body>
</html>