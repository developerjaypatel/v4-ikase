window.multichat = Backbone.View.extend({
    initialize:function () {
        
    },

    events:{
		"keyup #chatInput":					"checkTyping"
    },
    render:function () {
		var self = this;
		$(this.el).html(this.template(this.model.toJSON()));
        /*try {
			$(this.el).html(this.template(this.model.toJSON()));
		}
		catch(err) {
			var view = "multichat";
			var extension = "php";
			var chat_id = self.model.get("chat_id");
			self.model.set("holder", "chat_holder_" + chat_id);
			loadTemplate(view, extension, self);
			
			return "";
		}*/
		
		//var element = $(this.el);
		setTimeout(function() {
			self.doTimeouts();
		}, 1500);
		
        return this;
    },
	doTimeouts: function() {
		var self = this;
		var chat_id = this.model.get("chat_id");
		
		var theme = {
			theme: "chat",
			onAdd: function(item) {
				if (item.id==item.name) {
					this.tokenInput("clear");
					return;
				}
				var chat_id = self.model.get("chat_id");
				$(".chat_" + chat_id + " #chatInput").focus();
			}
		};		
		$(".chat_" + chat_id + " #chat_toInput").tokenInput("api/user", theme);
		$(".chat .token-input-list-chat").css("width", "280px");
		
		var from_id = this.model.get("from_id");
		var from = this.model.get("from");
		if (from_id == null) {
			from_id = "";
			from = "";
		}
		if (from_id !="") {
			$(".chat_" + chat_id + " #chat_toInput").tokenInput("add", {
				id: from_id, 
				name: from,
				tokenLimit:1
			});
		}
		
		var chats = JSON.parse(this.model.get("chat"));
		//$('#chatInput').autoResize();
		$("#chat_messages_" + chat_id).html(new multichat_messages({collection: chats}).render().el);
		
	},
	checkTyping: function(event) {
		if (event.keyCode==13) { 
			var chat_id = this.model.get("chat_id");
			if ($(".chat_" + chat_id + " #chatInput").val() != "") {
				this.saveChat(event); 
			}
		}
	},
	saveChat:function(event) {
		var self = this;
		var theevent = event;
		var url = "api/chat/save";
		var chat_id = this.model.get("chat_id");
		
		formValues = "chat_id=" + chat_id;
		formValues += "&chat_to=" + $(".chat_" + chat_id + " #chat_toInput").val();
		formValues += "&message=" + encodeURIComponent($(".chat_" + chat_id + " #chatInput").val());
	
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					var chats = JSON.parse(data.messages);
					if (chat_id == 0) {
						chat_id = data.success;
						self.model.set("chat_id", chat_id);
						$("#chat_holder_0").attr("id", "chat_holder_" + chat_id);
						$("#chat_holder_" + chat_id).removeClass("chat_0");
						$("#chat_holder_" + chat_id).addClass("chat_" + chat_id);	
						
						$(".chat_" + chat_id + " #chat_id").val(chat_id);
						
						$("#chat_messages_0").attr("id", "chat_messages_" + chat_id);
						$("#chat_content_0").attr("id", "chat_content_" + chat_id);
					}
					clearTimeout(chat_timeout_id);
					chat_timeout_id = setTimeout(function(){
						getChat(chat_id);
					}, 2000);
					$("#chat_messages_" + chat_id).html(new multichat_messages({collection: chats}).render().el);
					$(".chat_" + chat_id + " #chatInput").val("");
				}
			}
		});
	}
});
window.multichat_messages = Backbone.View.extend({
    initialize:function () {
    },

    events:{
       "click #multichat_messages_done":	"doTimeouts"
    },

    render:function () {
		var self = this;
		var chats = this.collection;
		
		var previous_user_name = "";
		_.each( chats, function(chat) {
			var color = "red";
			var text_alignment = "right";
			chat.timestamp = moment(chat.timestamp).format('h:mm a');
			chat.bubble_class = "";
			chat.row = "";
			if (chat.from == login_nickname) {
				chat.from = "<span style='color:blue'>" + chat.from + ":</span>";
				chat.right_cell = chat.from;
				chat.left_cell = chat.timestamp;
				color = "blue";
				chat.text_alignment = "left";

				chat.bubble_class = "bubble white";				
				chat.row = "<table><tr><td align='left' valign='top' width='1%' nowrap>" + chat.from + "<br><span style='font-size:.5em'>" + chat.timestamp + "</span></td><td align='left' valign='top' class='" + chat.bubble_class + "'>" + chat.message + "</td></tr></table>";

			} else {
				chat.from = "<span style='color:red'>" + chat.from + ":</span>";
				chat.right_cell = chat.timestamp;
				chat.left_cell = chat.from;
				color = "red";
				chat.text_alignment = "right";
					
				chat.bubble_class = "bubble bubble-alt green";
				chat.row = "<table width='100%'><tr><td align='left' valign='top' class='" + chat.bubble_class + "'>" + chat.message + "</td><td align='left' valign='top' width='1%' nowrap>" + chat.from + "<br><span style='font-size:.5em'>" + chat.timestamp + "</span></td></tr></table>";
					
			}
			chat.display_user = chat.from;
			/*
			if (previous_user_name!="") {
				if (previous_user_name == chat.from) {
					chat.text_alignment = "left";
					chat.display_user = "<span>" + chat.from + "<span>";
				} else {
					chat.text_alignment = "right";
					chat.display_user = "<span>" + chat.from + "<span>";
					
				}
			}
			*/
			//previous_user_name = chat.user_name;
		});
        try {
			$(this.el).html(this.template({chats: chats}));
		}
		catch(err) {
			var view = "multichat_messages";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		
		var element = $(this.el);
		
        return this;
    },
	doTimeouts:function() {
		$(this.el)[0].parentNode.scrollTop = $(this.el)[0].parentNode.scrollHeight;
	}
});
