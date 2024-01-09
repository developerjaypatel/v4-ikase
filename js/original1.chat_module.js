var check_chat_id;
function checkChat() {
	clearTimeout(check_chat_id);
	//get messages
	var chat_delay = "";
	if (customer_settings.get("chat_delay") == "") {
		chat_delay = user_settings.get("chat_delay");
	} else {
		chat_delay = customer_settings.get("chat_delay");
	}
	if (chat_delay=="" || typeof chat_delay == "undefined") {
		chat_delay = 7000;
	}
	var new_chats = new NewChats();
	new_chats.fetch({
		success:function(data) {
			var data_count = data.toJSON()[0].count;
			var urgency = data.toJSON()[0].urgency;
			if (data_count > 0) {
				var the_count = maxHundred(data_count);
				$("#new_chat_indicator").html(the_count);
				$("#new_chat_indicator").fadeIn();
				
				//urgent, show chat box right away
				if (urgency == "urgent") {
					answerChat();
				}
			} else {
				$("#new_chat_indicator").html("");
				$("#new_chat_indicator").fadeOut();
			}
		}
	});
	check_chat_id = setTimeout(function() {
		checkChat();
	}, chat_delay);
}
function closeThread() {
	//empty the thread id
	var thread_id = $(".chat #thread_id").val();
	$(".chat #thread_id").val("");
	//empty the to
	$("#chat_toInput").tokenInput("clear");
	$(".chat .token-input-list-chat").show();
	$("#chat_toSpan").html("");
	
	$(".chat #subjectInput")[0].selectedIndex = 0;
	$(".chat #subjectInput").show();
	$("#subjectSpan").html("");
	
	$("#thread_id_holder").html("");
	
	clearTimeout(render_chat_id);
	$("#chattingInput").html("Chat Session " + thread_id + " Closed");
}
var render_chat_id;
function renderChat(thread_id) {
	if (!blnChatting || thread_id == null) {
		return;
	}
	clearTimeout(render_chat_id);
	//get messages
	var new_chats = new ChatCollection([], {thread_id: thread_id});
	new_chats.fetch({
		success:function(data) {
			if (data.length == 0) {
				closeThread();
				return;
			}
			//console.log(data);
			var arrNewRows = [];
			var blnToIdentified = false;
			_.each( data.toJSON(), function(chat) {
				if (chat.chat=="thread closed") {
					closeThread();
				}
				var thestyle = "color:red; font-weight:bold";
				nickname = chat.nickname;
				if (chat.from == login_username) {
					thestyle = "color:blue; font-weight:bold;";
				} else {
					if (!blnToIdentified) {
						//set the To
						$("#chat_toInput").tokenInput("add", {id: chat.user_id, name: chat.from});
						$(".chat .token-input-list-chat").hide();
						$("#chat_toSpan").html(chat.from);
						//set the subject
						$(".chat #subjectInput > option").each(function(i) {
							//console.log(i + " => " + $(this).text() + " : " + $(this).val());
							if ($(this).val() ==  chat.subject) {
								$(".chat #subjectInput")[0].selectedIndex = i;
								$(".chat #subjectInput").hide();
								$("#subjectSpan").html(chat.subject);
								return false;
							}
						});
						blnToIdentified = true;
					}
				}
				arrNewRows[arrNewRows.length] = "<div style='border-bottom:1px solid #CCC; background:white; color:black'><div style='color:black; font-size:0.75em; float:right'>" + moment(chat.dateandtime).format("h:mm:ssa") + "</div><span style='" + thestyle + "'>" + nickname + "</span><div>" + chat.chat + "</div></div>";
			});
			$("#chattingInput").html(arrNewRows.join("\r\n"));
			$("#chattingInput").scrollTop(1E10);
			
			render_chat_id = setTimeout(function() {
				renderChat(thread_id);
			}, 3700);
		}
	});
}
function answerChat() {
	var mythread = new Backbone.Model;
	mythread.url = "../api/chatlatest"
	mythread.set("id", -1);
	mythread.fetch({
		success: function(data) {
			composeChat(data.id);
			setTimeout(function(){
				$("#chatInput").cleditor()[0].focus();
			}, 600);
		}
	});
}
var blnChatting = false;
function composeChat(thread_id) {
	if (typeof thread_id == "undefined") {
		thread_id = -1;
	}
	clearTimeout(render_chat_id);
	//are we replying, replyingall, forwarding
	reaction = "new";
	object_action = "New Chat";
	var chat = new Chat({chat_id: -1, thread_id: thread_id});
	chat.set("reaction", reaction);
	chat.set("holder", "chat_box");
	
	$("#chat_box").html(new chat_view({model: chat}).render().el);
	$("#chat_bottom").html(new chatting_view({model: chat}).render().el);
	$("#chat_holder").addClass("glass_header_no_padding");
	$("#chat_holder").fadeIn();
	
	/*
	setTimeout(function() {
		checkChat();
	}, 15000);
	*/
	setTimeout(function() {
		$("#chat_toInput").focus();
	}, 700);
}
function closeChat(event) {
	var thread_id = $(".chat #thread_id").val();
	//send one last message
	$('#chatInput').val('thread closed').blur();
	addForm(event, "chat");
	
	//mark the thread as read
	if (thread_id > 0) {
		var url = 'api/thread/delete';
		formValues = "id=" + thread_id;
		
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//self.recentKases();
				}
			}
		});
	}
	/*
	$('.left_sidebar').html(new kase_nav_left_view().render().el);								
	$('#kases_recent').html(new kase_list_category_view({model: recent_kases}).render().el);
	$("#left_sidebar_bottom").fadeOut();
	*/
	$("#chat_holder").fadeOut();
	$("#chat_box").html("");
	$("#chat_bottom").html("");
	blnChatting = false;
}
var poll_timestamp = "";
function pollChat() {
	var thetime = new Date();
	var nhours=thetime.getHours();
	var nmins=thetime.getMinutes();
	var nsecn=thetime.getSeconds();
	
	var url = 'api/poll.php';
	var formValues = '?timestamp=' + poll_timestamp + '&user_id=' + login_user_id + '&customer_id=' + customer_id;
	url += formValues;
	
	var thelog = "polling for changes at " + nhours +  ":" + nmins +  ":" + nsecn + " tsp: " + poll_timestamp + "";
	//console.log(thelog);
	
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				alert(data.error.text);
			} else {
				//how long have we been online
				var current_brand_title = $(".navbar-brand").prop("title");
				var arrCurrentBrand = current_brand_title.split(" - ");
				var current_activity_interval = (data.ACTIVITY_INTERVAL / 60).toFixed(0) + " mins";
				if (arrCurrentBrand.length < 5) {
					arrCurrentBrand.push(current_activity_interval);
				} else {
					arrCurrentBrand[4] = current_activity_interval;
				}
				var new_brand_title = arrCurrentBrand.join(" - ");
				$(".navbar-brand").prop("title", new_brand_title);
				
				//reset global variable to current timestamp
				poll_timestamp = data.timestamp;
				var thelog = poll_timestamp + " <> " + data.timestamp;
				var chat_id = data.chat_id;
				if (chat_id!="") {
					var from = data.from;
					var from_id = data.from_id;
					 thelog += " - chat_id: " + chat_id;			
					 
					$("#new_chat_indicator").html("<span id='chat_indicator_" + chat_id + "' style='cursor:pointer' data-from-id='" + from_id + "' data-from='" + from + "' title='Chat Request from " + from + "'>1</span>");
					$("#new_chat_indicator").fadeIn();
					flashTitle("Chat Request from " + from, 50);
				}
				//console.log(thelog);
				setTimeout(function(){
					pollChat()
				}, 20000);
			}
		}
	});
	
	if (blnAfterHours && login_nickname!="MA") {
		var url = 'api/anytime';
		$.ajax({
			url:url,
			type:'GET',
			dataType:"json",
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					alert(data.error.text);
				} else {
					if (data==false) {
						//alert("After Hours");
						var d = new Date()
						var hours = d.getHours();
						if (hours > 17) {
							alert("Off-site Access is unavailable after 6PM.  Please contact your Office Manager to request Anytime Access.");
							fullLogOut();
							document.location.href = "index.php";
						}
					}
				}
			}
		});
	}
}
var chat_timeout_id = false;
var chat_timestamp = "";
function getChat(chat_id) {
	if ($("#chat_messages_" + chat_id).length==0) {
		return false;
	}
	var thetime = new Date();
	var nhours=thetime.getHours();
	var nmins=thetime.getMinutes();
	var nsecn=thetime.getSeconds();
	
	var url = 'api/poll_chat.php';
	var formValues = '?timestamp=' + chat_timestamp + '&chat_id=' + chat_id + '&customer_id=' + customer_id;
	url += formValues;
	
	var thelog = "polling for changes at " + nhours +  ":" + nmins +  ":" + nsecn + " tsp: " + chat_timestamp + "";
	//console.log(thelog);
	
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				alert(data.error.text);
			} else {
				//reset global variable to current timestamp
				chat_timestamp = data.timestamp;
				var thelog = chat_timestamp + " <> " + data.timestamp;
				if (data.messages!="") {
					var chats = JSON.parse(data.messages);
					var mymodel = new Backbone.Model({"handler": "#chat_messages_" + chat_id });
					$("#chat_messages_" + chat_id).html(new multichat_messages({collection: chats}).render().el);
				}
					
				//console.log(thelog);
				chat_timeout_id = setTimeout(function(){
					getChat(chat_id);
				}, 2000);
			}
		}
	});
}