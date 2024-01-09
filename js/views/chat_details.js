window.chat_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	
	events:{
    	"click .chat .save":	"saveChat",
		"click .chat .close":	"closePanel",
		"change #chat_toInput":	"checkForSave",
		"keyup #chatInput":		"checkTyping"
    },
    render: function () {
		var self = this;
		if (typeof this.template != "function") {
			var view = "chat_view";
			var extension = "php";
			loadTemplate(view, extension, this);
			return "";
	   	}
		
		//make sure we have an id, even if it's empty
		if (typeof this.model.id == "undefined") {
			this.model.set("id", "");		
		}
		mymodel = this.model.toJSON();
		$(this.el).html(this.template(mymodel));
		
		this.model.set("editing", false);
		if(this.model.id=="" || this.model.id==-1){
			//editing mode right away
			this.model.set("editing", false);
			$('#callback_dateInput').datetimepicker();
			setTimeout(function() { 
					$(".chat .edit").trigger("click"); 
					$(".chat .delete").hide();
					$(".chat .reset").hide();
					$(".chat").css("width", "229px");
					$(".chat").css("border", "0px red solid");
					$(".chat").css("margin-left", "5px");
					$("#token-input-chat_toInput").focus();
				}, 700);
		}
		setTimeout(function(){
			var theme = {theme: "chat"};
			/*
			$("#chatInput").cleditor({
				width:205,
				height: 130, 
				controls: // controls to add to the toolbar
                    "bold italic underline | font size " +
                    "style | removeformat"
			});
			var cledit = $("#chatInput").cleditor()[0];
			$(cledit.$frame[0]).attr("id","cleditCool");
			var cleditFrame;
			if(!document.frames){
			   cleditFrame = $("#cleditCool")[0].contentWindow.document;
			} else {
				cleditFrame = document.frames["cleditCool"].document;
			}
			
			$( cleditFrame ).bind('keyup', function(event){
				if (event.keyCode==13) { 
					//self.saveChat(event); 
					addForm(event, "chat");
				}
			});
			*/
			$("#chat_toInput").tokenInput("api/user", theme);
			$(".chat .token-input-list-chat").css("width", "215px");
			//is there a thread
			/*
			var source_chat_id = self.model.get("source_chat_id");
			if (source_chat_id > 0) {
				var source_chat = new Chat({chat_id: source_chat_id});
				//get the source chat info
				source_chat.fetch({
					success: function (data) {
						$("#subjectInput").val("Re:" + data.get("subject"));
						$("#thread_uuid").val(data.get("chat_uuid"));
						//if there is a case
						var case_id = data.get("case_id");
						if (case_id!="") {
							var chat_case = kases.get(case_id);
							$("#case_fileInput").tokenInput("add", {id: case_id, name: chat_case.name()});	
						}
					}
				});	
				var reaction = self.model.get("reaction");
				if (reaction=="reply" || reaction=="replyall") {
					//recipients for a simple reply
					var from_users = new MessageUsers([], {chat_id: source_chat_id, type: "from"});
					from_users.fetch({
						success: function (data) {
							_.each( data.toJSON(), function(chat_user) {
								$("#chat_toInput").tokenInput("add", {id: chat_user.user_id, name: chat_user.user_name});		
							});
						}
					});	
					
					if (reaction=="replyall") {
						var cc_users = new MessageUsers([], {chat_id: source_chat_id, type: "cc"});
						cc_users.fetch({
							success: function (data) {
								_.each( data.toJSON(), function(chat_user) {
									$("#chat_toInput").tokenInput("add", {id: chat_user.user_id, name: chat_user.user_name});		
								});
							}
						});
					}
				}
			}
			
			*/
			/*
			//we need to upload attachments
			$('#chat_attachments').html(new chat_attach({model: self.model}).render().el);
			setTimeout(function() {
				$(".chat_attach_form #queue").css("height", "150px");
				$(".chat_attach_form #queue").css("width", "215px");
				$(".chat_attach_form").css("border","0px #000000 solid");
			}, 200);
			*/
		}, 600);
		setTimeout(function(){
			$("#token-input-chat_toInput").css("border","1px #000000 solid");
		}, 1000);
		
        return this;
    },
	checkTyping: function(event) {
		if (event.keyCode==13) { 
			//self.saveChat(event); 
			addForm(event, "chat");
		}
	},
	saveChat: function(event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		if (event.type!="click") {
			return;
		}
		addForm(event, "chat");
		return;
	},
	closePanel:function(event) {
		closeChat(event);
	},
	deleteMessageView:function (event) {
        var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		deleteForm(event, "chat");
		return;
    }
});

window.chat_attach = Backbone.View.extend({
    initialize:function () {

    },
    events:{
        "click .chat_attach_form #upload_it_five": "uploadIt"
    },
    render:function () {
		var self = this;
        $(this.el).html(this.template());
		setTimeout(function() {
			var timestamp = moment().format('YYYY-MM-DD h:mm:ss a');
			//console.log("timestamp:" + timestamp);
			var token = md5('ikase_system' + timestamp);
			$('#file_upload').uploadifive({
				'auto'             : false,
				'checkScript'      : 'check-exists.php',
				'formData'         : {
									   'timestamp' : timestamp,
									   'token'     : token
									 },
				'queueID'          : 'queue',
				'uploadScript'     : 'api/uploadifive.php',
				'onUploadComplete' : function(file, data) { 
					setTimeout(function() {
						//self.saveFile(data);
						console.log(data);
					}, 50);
				}
			});
		}, 500);
		
        return this;
    },
	uploadIt: function(event) {
		event.preventDefault();
		$('.chat_attach_form #file_upload').uploadifive('upload');
	}
});
window.chatting_view = Backbone.View.extend({

    tagName:"div", // Not required since 'div' is the default if no el or tagName specified
	
	initialize:function () {
		_.bindAll(this);
    },
	
	events:{
    	
    },
    render: function () {
		var self = this;
		mymodel = this.model.toJSON();
		$(this.el).html(this.template(mymodel));
		
        return this;
	}
});
