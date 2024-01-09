<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Speech to Text</title>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.1/css/font-awesome.min.css" />
<style>
#result {
	height:200px;
	border:1px solid black;
	padding:10px;
	font-size:1.2em;
	line-height:25px;
}
.iterim {
	color: #999;
}
</style>
</head>

<body>
	<h4 align="center">Speech to Text</h4>
    <div id="result" style="width:500px; height:50px"></div>
	<div style="font-style:italic">Speak slowly and clearly.  Say "Enter" for new line, "Period", "Comma" for punctuation</div>
    <button id="recording_button" onclick="startConverting()"><i class="fa fa-microphone"></i></button>
    <button id="writing_button" onclick="writeConversion()" style="display:none"><i class="fa fa-file"></i></button>
    <div>
    	<textarea id="text" cols="60" rows="5" style="display:none"></textarea>
    	<span id="feedback"></span>
    </div>
    <script type="text/javascript">
	var r = document.getElementById("result");
	var t = document.getElementById("text");
	var feedback = document.getElementById("feedback");
	var arrLines = [];
	function writeConversion() {
		feedback.innerHTML = "transcribing";
		endSpeech();
		feedback.innerHTML = "done";
		//start listening again
		//startConverting();
		
		document.getElementById("recording_button").style.display = "block";
		document.getElementById("writing_button").style.display = "none";
		r.innerHTML = "";
		
		speechRec.end();
		setTimeout(function() {
			feedback.innerHTML = "";
		}, 2500);
	}
	function startConverting() {
		if ('webkitSpeechRecognition' in window) {
			document.getElementById("recording_button").style.display = "none";
			document.getElementById("writing_button").style.display = "block";
			var speechRec = new webkitSpeechRecognition();
			speechRec.continuous = true;
			speechRec.interimResults = true;
			speechRec.lang = 'en-US';
			speechRec.start();
			
			var finalTrans = '';
			speechRec.onstart =  function(event) {
				r.innerHTML = "";
				feedback.innerHTML = "listening";
			}
			speechRec.onresult = function(event) {
					var interimTrans = '';
					for (var i = event.resultIndex; i < event.results.length; i++) {
						var transcript = event.results[i][0].transcript;
						if (transcript=="\n") {
							//arrLines.push(transcript);
							interimTrans += "<br />";
						}
						
						if (event.results[i].isFinal) {
							finalTrans += transcript;
							if (transcript!="\n" && transcript!="," && transcript!=".") {
								finalTrans += " ";
							}
							arrLines.push(finalTrans);
						} else {
							interimTrans += transcript;
						}
					}
					r.innerHTML = finalTrans + "<span class='iterim'>" + interimTrans + "</span>";
			};
			speechRec.onend =  function(event) {
				
			}
			speechRec.onerror = function(event) {
				feedback.innerHTML = "error";
			};
		} else {
			alert("Speech Recognition is not available on this browser");
		}
	}
	function endSpeech() {
		var joint = "";
		t.value = arrLines.join(joint);
		t.style.display = "block";
	}
	</script>
</body>
</html>