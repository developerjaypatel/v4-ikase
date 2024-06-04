window.message_listing = Backbone.View.extend({
    initialize:function () {
        this.collection.on("change", this.render, this);
    },
	events: {
		"click #new_message": 				"newMessage",
		"click .message_action": 			"reactMessage",
		"click .delete_message": 			"confirmdeleteMessage",
		"click .delete_yes":				"deleteMessage",
		"click .delete_no":					"canceldeleteMessage",
		"click .read_holders":				"readMessage",
		"click .open_message":				"openMessage",
		/*"click .message_data_row":			"openMessage",*/
		"click #hide_preview_pane":			"shrinkMessage",
		"click .open_messages":				"openDayMessages",
		"click .assign_webmail": 			"assignWebmail",
		"click .print_memo":				"printMemo",
		"click .print_message":				"printMessage",
		"mouseover .message_data_row":		"overRow",
		"mouseout .message_data_row":		"outRow",
		"mouseover #message_preview_panel": "freezePreview",
		"mouseover .message_preview_link": 	"freezePreview",
		"keyup #message_searchList":		"findIt",
		"click #message_clear_search":		"clearSearch",
		"click .read_date":					"showReadDates",
		"click #label_search_messages":		"Vivify",
		"click #message_searchList":		"Vivify",
		"focus #message_searchList":		"Vivify",
		"blur #message_searchList":			"unVivify",
		"click #unread_messages":			"showUnread",
		"click #all_messages":				"showAllMessages",
		"click .btn_review_pending":		"reviewPending",
		"click .btn_accept_pending":		"acceptPending",
		"click .btn_dismiss_pending":		"dismissPending",
		"click .btn_block_pending":			"blockPending",
		"click #return_to_contact":			"returnToContact"
	},
    render:function () {	
		if (typeof this.template != "function") {
			if (typeof this.model.get("holder") == "undefined") {
				this.model.set("holder", "content");
			}
			var view = "message_listing";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
	   }	
		var self = this;
		/*
		if (login_nickname!='AB') {
			$(this.el).html("<p align='center' style='font-size:1.6em; background:red; color:white'>Under repairs</p>");
			return this;
		}
		*/
		if (typeof this.model.get("homepage") == "undefined") {
			this.model.set("homepage", false);
		}
		this.model.set("process_message", false);
		this.model.set("opening_message", false);
		this.collection.bind("reset", this.render, this);
		var  messages = this.collection.toJSON();
		var title = this.model.get("title");
		 _.each( messages, function(message) {
			if (title=="Outbox") {
                if (message.read_status=="Y") {
                    message_read = "yes";
                    message_confirmation = "Seen";
                }
            } 
			
			var attach_indicator = "none";
            var attach_link = "";
			message.from = message.from.replace("|", "");
			
			//sender
			if (message.sender==null) {
				message.sender = message.from;
				//is it an email
				if (message.sender.indexOf("@") > 0) {
					message.sender = "<a href='mailto:" + message.sender + "' class='white_text'>" + message.sender + "</a>";
				}
			}
		
            //NEED TO FIX FOR MULTIPLE ATTACHMENTS
			var arrAttachment = [];
            if (message.attachments!="") {
				message.attachments = message.attachments.replaceAll(";", "|");
            	attach_indicator = "";
				email_attachment = "";
				var arrAttach = message.attachments.split("|");
				var arrayLength = arrAttach.length;
				for (var i = 0; i < arrayLength; i++) {
					var attachment = arrAttach[i];
					attachment = attachment.trim();
					if (message.message_type=="email") {
						var strpos = attachment.indexOf("attachments");
						if (strpos < 0) {
							var attach_link = "https://"+ location.hostname +"/uploads/" + customer_id + "/webmail_previews/" + login_user_id + "/" + attachment;
						} else {
							var attach_link = "https://www.ikase.xyz/ikase/gmail/ui/" + attachment;
						}
						
						email_attachment = '<input type="checkbox" value="' + attachment + '" class="kase_attach" id="kase_attach_' + i + '" />&nbsp;';
						var arrLink = attachment.split("/");
						attachment = arrLink[arrLink.length - 1];
					} else {
						attachment = attachment.replace("https:///uploads", "../uploads");
						// attachment = attachment.replace("../uploads/" + message.customer_id + "/" + message.case_id + "/", "");
						// attachment = attachment.replace("../uploads/" + message.customer_id + "/", "");
						// if (message.case_id=="" || message.case_id=="-1") {
						// 	attach_link = "../uploads/" + message.customer_id + "/" + attachment;
						// } else {
						// 	attach_link = "../uploads/" + message.customer_id + "/" + message.case_id + "/" + attachment;
						// }
						// above code commented and below code (if-else) added by mukesh on 1-6-2023 due to case attachment link doesn't work in outbox email view
						if(attachment.indexOf("/") < 0)	
						{
							attach_link = "../uploads/" + message.customer_id + "/" + attachment;
						}
						else
						{
							attach_link = attachment;
						}
						// end added by mukesh
					}
					attach_link = email_attachment + '<a id="kase_attach_link_' + i + '" href="' + attach_link + '" target="_blank" title="Click to review ' + attachment + '">' + attachment + '</a>';
					arrAttachment.push(attach_link);
				}
            }
			message.attach_link = "";
			if (arrAttachment.length > 0) {
				message.attach_link = "<div id='attach_link_" + message.id + "'>" + arrAttachment.join("<br>") + "</div>";
			}
			message.attach_indicator = attach_indicator;
			
			if (message.read_date=="0000-00-00 00:00:00") {
				if (message.priority=="high") {
					message.subject = "<div style='float:right' class='blink'>HIGH PRIORITY</div>" + message.subject;
				}
			}
			
			//reaction to the message
			message.reaction_indicator = "";
			if (title=="Inbox") {
				if (typeof message.reply_date != "undefined") {
					if (message.reply_date != "") {
						message.reaction_indicator += "&nbsp;<span class='reaction_indicator' title='You replied to this message on " + moment(message.reply_date).format("MM/DD/YY") + "'>R</span>";
					}
				}
				if (typeof message.forward_date != "undefined") {
					if (message.forward_date != "") {
						message.reaction_indicator += "&nbsp;<span class='reaction_indicator' title='You forwarded to this message on " + moment(message.forward_date).format("MM/DD/YY") + "'>F</span>";
					}
				}
			}

			//was it read and when
			if (typeof message.to_user_uuids == "undefined" || message.to_user_uuids == null) {
				message.to_user_uuids = "";
				message.to_user_names = "";
				message.to_nicknames = "";
				message.read_dates = "";
			} else {
				var arrUsers = message.to_user_names.split("|");
				var arrNicks = message.to_nicknames.split("|");
				var arrReadDates = message.read_dates.split("|");
				var arrReadTypes = message.to_types.split("|");
				var arrLength = arrUsers.length;
				var arrReadTitles = [];
				message.read_status = "N";

				for(var i = 0; i < arrLength; i++) {
					var to_user_name = arrUsers[i];
					var to_nickname = arrNicks[i];
					var to_read_date = arrReadDates[i];
					var to_read_type = arrReadTypes[i];
					
					if (typeof to_read_date == "undefined") {
						to_read_date = "0000-00-00 00:00:00";
					}
					var display_nickname = to_nickname;
					if (to_read_type!="bcc" || to_nickname == login_nickname) {
						//normal display	
						if (to_read_date=="0000-00-00 00:00:00") {
							var to_read_title = to_nickname;
						} else {
							var to_read_title = "<span title='" + to_user_name + " read the message on " + moment(to_read_date).format("MM/DD/YY h:mma") + "' style='background:green;color:white'>" + to_nickname + "</span>&nbsp;<span style='font-size:0.7em; display:none' class='read_date_" + message.id + "'>" + moment(to_read_date).format("MM/DD/YY") + "&nbsp;" + moment(to_read_date).format("h:mma") + "</span>";
							
							if (to_nickname == login_nickname) {
								message.read_status = "Y";
							}
						}
						arrReadTitles.push(to_read_title);
					} else {
						//check for read status but exclude from list
						if (to_nickname == login_nickname) {
							message.read_status = "Y";
						}
					}
				}
				message.to_user_names = arrReadTitles.join("; ");
			}
		 });
		 
		 var webmail_inbox = "";
		 if (typeof webmails != "undefined") {
			if (webmails.length  > 0) {
				webmail_inbox = '&nbsp;<a id="webmail_inbox_indicator" href="#webmail" class="white_text" style="background:orange" title="Click to open your webmail">You have ' + webmails.length + ' emails</a>';
			}
		}
		$(this.el).html(this.template({messages: messages, title: this.model.get("title"), first_column_label: this.model.get("first_column_label"), receive_label: this.model.get("receive_label"), homepage: this.model.get("homepage"), webmail_inbox: webmail_inbox}));
		
		setTimeout(function(){
			var size = 100;
			if (self.model.get("homepage")) {
				size = 20;
			}
			tableSortIt("message_listing", size);
		}, 100);
		
		setTimeout(function(){
			$(".pager").hide();
			
			$("#message_list_outer_div").css("height", (window.innerHeight - 140) + "px");
			/*
			$(".pager").css("position","absolute");
			$(".pager").css("top","-10px");
			$(".pager").css("left","200px");
			$(".pager").show();
			*/
			
			if (document.location.hash.indexOf("#contacts")==0) {
				$("#return_to_contact").show();
				$("#unread_messages").hide();
			}
		}, 150);
		
		return this;
    },
	unVivify: function(event) {
		var textbox = $("#message_searchList");
		var label = $("#label_search_messages");
		
		if (textbox.val() == "") {
			label.animate({color: "#999", fontSize: "1em", top: "0px"}, 300);
			
		} else {
			return;
		}
	},
	Vivify: function(event) {
		var textbox = $("#message_searchList");
		var label = $("#label_search_messages");
		
		
		
		if (textbox.val() == "") {
			label.animate({top: "-9px", fontSize: "0.58em", color: "#CCC"}, 250);
			//$('#notes_searchList').focus();
		}
	},
	returnToContact: function(event) {
		event.preventDefault();
		var contact_id = document.location.hash.split("/")[1];
		window.Router.prototype.editContactEmail(contact_id);
	},
	showAllMessages: function(event) {
		$(".message_data_row").show();
		$(".date_row").show();
		$("#unread_messages").show();
		$("#all_messages").hide();
	},
	showUnread: function(event) {
		var span_holders = $(".read_holders").parent();
		var arrLength = span_holders.length;
		for (var i = 0; i < arrLength; i++) {
			var span_holder = span_holders[i];
			if (span_holder.style.display=="none") {
				var row_id = span_holder.id.split("_")[2];
				$(".message_row_" + row_id).hide();
			}
		}
		$(".date_row").hide();
		$("#unread_messages").hide();
		$("#all_messages").show();
	},
	overRow: function(event) {
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		if (id==this.model.get("current_message_id")) {
			return;
		}
		this.model.set("current_background", element.style.background);
		element.style.background = "#000066";
	},
	outRow: function(event) {
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		if (id==this.model.get("current_message_id")) {
			return;
		}
		element.style.background = this.model.get("current_background");
	},
	newMessage: function(event) {
		event.preventDefault();
		composeMessage();
	},
	shrinkMessage: function(event) {
		event.preventDefault();
		$("#preview_pane_holder").fadeOut(function() {
			$("#message_list_outer_div").css("width", "100%");		
		});
		
		$("#messagerow_" + this.model.get("current_message_id")).css("background", "");
		this.model.set("current_message_id", -1);
	},
	openMessage: function(event, draft) {
		var self = this;
		if (this.model.get("opening_message")) {
			return;
		}
		this.model.set("opening_message", true);
		
		setTimeout(function(){
			self.model.set("opening_message", false);
		}, 2000);
		
		if (typeof draft == "undefined") {
			draft = "N";
		}
		var element = event.currentTarget;
		event.preventDefault();
		
		if (this.model.get("title")=="Drafts") {
			var element_id = element.id.replace("open_", "draft_");
			composeMessage(element_id);
			return;
		}
		if ($("#preview_pane").length > 0) {
			this.readMessage(event);
			return;
		}
		
		var id = element.id.split("_")[1];
		if ($(".message_listing #messagerow_" + id).css("display")== "none") {
			$(".message_listing #messagerow_" + id).fadeIn();
			setTimeout(function() { 
				var message_height = $(".message_listing #messagerow_" + id).height();
				$("#pager").css("margin-top", message_height + "px");
			}, 200);
		} else {
			$(".message_listing #messagerow_" + id).fadeOut();
		}
	},
	openDayMessages: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var date_class = element.id.split("_")[2];
		if ($(".message_row_" + date_class).css("display")== "none") {
			$(".message_row_" + date_class).fadeIn();
			setTimeout(function() { 
				var message_height = $(".message_row_" + date_class).height();
				$("#pager").css("margin-top", message_height + "px");
			}, 200);
		} else {
			$(".message_row_" + date_class).fadeOut();
		}
	},
	printMessage: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var message_id = element.id.split("_")[2];
		var url = "report.php#message/" + message_id;
		window.open(url);
	},
	printMemo: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		var message_id = element.id.split("_")[2];
		var url = "report.php#memo/" + message_id;
		window.open(url);
	},
	assignWebmail: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//$("#check_assign_" + id).prop("checked", true);
		composeMessageAssign(id, "webmail");
	},
	reactMessage: function(event) {
		var element = event.currentTarget;
		event.preventDefault();
		composeMessage(element.id);
	},
	expandMessage: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		
		$("#messagerow_" + this.model.get("current_message_id")).css("background", "");
		this.model.set("current_message_id", -1);
		
		//find the message_id		
		//$(".message_listing #openmessage_" + id).hide();
		//$(".message_listing #shrink_" + id).show();
		
		hidePreview();
		
		$("#message_list_outer_div").css("width", "55%");
		$("#preview_pane_holder").css("width", "45%");
		
		this.model.set("current_message_id", id);
		
		this.model.set("current_background", $("#messagerow_" + id).css("background"));
		$("#messagerow_" + id).css("background", "#F90");
		
		var blnPending = (this.model.toJSON().title=="Pending Emails");
			
		$("#preview_pane_holder").fadeIn(function() {
			$("#preview_pane").html(loading_image);
			//then go get the actual body
			var url = 'api/message/' + id;
			//formValues = "id=" + id;
			$.ajax({
				url:url,
				type:'GET',
				dataType:"text",
				data: "",
					success:function (data) {
						//$("#webmail_body_" + id).html(data);
						var $frame = $('<iframe style="width:100%; height:560px;background:white" frameborder="0">');
						//$("#webmail_body_" + id).html( $frame );
						var message_subject = $("#message_subject_" + id).html();
						var message_from = $("#message_from_" + id).html();
						var message_to = $("#message_destination_" + id).val();
						var message_date = $("#message_date_" + id).val();
						var message_attach = $("#attach_link_" + id).html();
						if (typeof message_attach != "undefined") {
							message_attach = message_attach.replaceAll('type="checkbox"', 'style="display:none" type="checkbox"');
						} else {
							message_attach = "";
						}
						var message_case = $("#message_case_" + id).html();
						if (message_case.indexOf("<a") < 0) {
							message_case = "";
						}
						var message_case_id = $("#message_case_id_" + id).val();
						
						var message_links = '<a id="reply_' + id + '" data-toggle="modal" data-target="#myModal4" style="cursor:pointer" class="message_action"><i style="font-size:12px;color:#FCC" class="glyphicon glyphicon-arrow-left" title="Click to Reply"></i>&nbsp;Reply</a><br><a id="forward_' + id + '" data-toggle="modal" data-target="#myModal4" style="cursor:pointer" class="message_action"><i style="font-size:13px;color:#66FF99" class="glyphicon glyphicon-arrow-right" title="Click to Forward"></i>&nbsp;Forward</a><br><a title="Click to delete Message" class="list_edit delete_message" id="delete_' + id + '" style="cursor:pointer"><i style="font-size:13px; color:#FF3737; cursor:pointer" class="glyphicon glyphicon-trash list_edit delete_message" title="Click to delete Message"></i>&nbsp;Delete</a>';
						
						var preview_title = "<div style='width:100%; background:#CCC; color:black; padding:3px'><div style='float:right; background:black; padding-right:5px; padding-left:5px; height:18px; width:19px'><a id='hide_preview_pane' style='cursor:pointer; color:white; font-size:1.2em; position:absolute; margin-top:-3px' title='Click to close preview pane'>&times;</a></div><div style='float:right; width:70px; text-align:left'>" + message_links + "</div><div style='font-size:1.3em; font-weight:bold'>" + message_subject + "</div>";
						if (message_case!="") {
							preview_title += "<div>Kase:" + message_case.replace("color:white", "color:white; background:black") + "</div>";
						}
						preview_title += "<div>From: " + message_from.replace('class="white_text"', 'style="color:black"') + "</div><div>To:" + message_to + "</div><div>Date: " + message_date + "</div>";
						if (typeof message_attach == "undefined") {
							message_attach = "";
						}
						if (message_attach!="") {
							message_attach = message_attach.replaceAll("&nbsp;", " ");
							message_attach = message_attach.replaceAll("/null/", "/");
							// console.log("message_attach :::: ",message_attach)
							let arr = message_attach.split('<br>');
							console.log(arr);

							let newMessageAttach = "";
							for(let i=0;i<arr.length;i++)
							{
								let tArr = arr[i].split('/');
								if(tArr.length === 6)
								{
									newMessageAttach += `${tArr[0]}/${tArr[1]}/${tArr[2]}/${tArr[4]}/${tArr[5]}<br>`
								}else{
									newMessageAttach = message_attach
								}
							}

							// console.log("message_attach:",message_attach)
							preview_title += "<div><i style='font-size:15px;color:#000; display:<%=message.attach_indicator%>' class='glyphicon glyphicon-paperclip'></i> " + newMessageAttach + "</div>";
						}
						if (blnPending && message_case_id == -1) {
							preview_title += '<button title="Click to Assign Message to Kase" id="assign_' + id + '" class="btn btn-xs btn-primary assign_webmail white_text" style="cursor:pointer;" title="Click to assign this email to kase" onClick="parent.composeMessageAssign(' + id + ')">Assign to Kase</button>&nbsp;&nbsp;&nbsp;<button class="btn btn-xs btn-success btn_accept_pending" id="messageaccept_' + id + '" onClick="parent.acceptMessageKase(' + id + ')">Accept as is</button>';
						}
						
						preview_title += "</div>";
						
						$("#preview_title").html(preview_title);
						$("#preview_pane").html( $frame );
						setTimeout( function() {
							var doc = $frame[0].contentWindow.document;
							var $body = $("body",doc);
							data = data.replaceAll("\r\n", "<br>");
							$body.html(data);
						}, 1 );
					},
					error: function(XMLHttpRequest, textStatus, errorThrown) { 
						$("#message_body_" + id).html("Error: " + errorThrown); 
					} 
				});
		});
	},
	reviewPending: function(event) {
		this.model.set("process_message", false);
		this.expandMessage(event);
	},
	acceptPending: function(event) {
		if (this.model.get("process_message")) {
			return;
		}
		blnMessageAssigning = true;
		var self = this;
		this.model.set("process_message", true);
		var element = event.currentTarget;
		
		var arrID = element.parentElement.id.split("_");
		var message_id = arrID[arrID.length - 1];
		
		$("#accept_" + message_id).fadeOut();
		if (!confirm("This email has not yet been assigned to a Kase.  Please click OK to proceed, or Cancel if you want to assign the email to a Kase.")) {
			//cancelled
			$("#review_" + message_id).trigger("click");
			blnMessageAssigning = false;
			return;
		}
		event.preventDefault();
		//mark the message as read
		//mark the row as read
		var url = 'api/messages/read';
		var formValues = "id=" + message_id;

		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//console.log(data);
					//no need for feedback
				}
			}
		});
		
		//change the status to empty
		var url = "api/messages/confirm_email";
		formValues = "id=" + message_id;
					
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//it's been processed now
					self.model.set("process_message", false);
					
					$(".message_row_" + message_id).css("background", "green");
					checkInbox();
					setTimeout(function() {
						$(".message_row_" + message_id).fadeOut();
						if ($("#hide_preview_pane").length > 0) {
							$("#hide_preview_pane").trigger("click");
						}
					}, 2500);
				}
			}
		});
	},
	dismissPending: function(event) {
		var self = this;
		
		event.preventDefault();
		this.model.set("process_message", true);
		var element = event.currentTarget;
		
		var arrID = element.parentElement.id.split("_");
		var message_id = arrID[arrID.length - 1];
		
		//change the status to empty
		var url = "api/messages/delete";
		formValues = "id=" + message_id;
					
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//it's been processed now
					self.model.set("process_message", false);
					//close the preview if any
					if ($("#hide_preview_pane").length > 0) {
						$("#hide_preview_pane").trigger("click");
					}
					$(".message_row_" + message_id).css("background", "red");
					setTimeout(function() {
						$(".message_row_" + message_id).fadeOut();
						if ($("#hide_preview_pane").length > 0) {
							$("#hide_preview_pane").trigger("click");
						}
						checkInbox();
					}, 2500);
				}
			}
		});
	},
	blockPending: function(event) {
		var self = this;
		
		event.preventDefault();
		this.model.set("process_message", true);
		var element = event.currentTarget;
		
		var arrID = element.parentElement.id.split("_");
		var message_id = arrID[arrID.length - 1];
		
		//change the status to empty
		var url = "api/messages/block_email";
		formValues = "id=" + message_id;
					
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//it's been processed now
					self.model.set("process_message", false);
					//close the preview if any
					if ($("#hide_preview_pane").length > 0) {
						$("#hide_preview_pane").trigger("click");
					}
					$(".message_row_" + message_id).css("background", "red");
					setTimeout(function() {
						$(".message_row_" + message_id).fadeOut();
						if ($("#hide_preview_pane").length > 0) {
							$("#hide_preview_pane").trigger("click");
						}
						checkInbox();
					}, 2500);
				}
			}
		});
	},
	readMessage: function(event) {
		if ($("#preview_pane").length > 0) {
			this.expandMessage(event);
		}
		var element = event.currentTarget;
		event.preventDefault();
		var id = element.id.split("_")[1];
		$("#read_holder_" + id).fadeOut(
			function() {
				if ($("#preview_pane").length == 0) {
					$("#messagerow_" + id).fadeIn();
				}
				$("#action_holder_" + id).fadeIn();
				//mark the row as read
				var url = 'api/messages/read';
				formValues = "id=" + id;
		
				$.ajax({
					url:url,
					type:'POST',
					dataType:"json",
					data: formValues,
					success:function (data) {
						if(data.error) {  // If there is an error, show the error messages
							saveFailed(data.error.text);
						} else {
							//console.log(data);
							//refresh the new message indicator
							checkInbox();
						}
					}
				});
			}
		);
	},
	confirmdeleteMessage: function(event) {
		var self = this;
		if (this.model.get("opening_message")) {
			return;
		}
		this.model.set("opening_message", true);
		
		event.preventDefault();
		var element = event.currentTarget;
		var elementArray = element.id.split("_");
		var id = elementArray[1];
		//check for which box we're in
		var boxtype = "in";
		if (element.className.indexOf(" out") > -1) {
			boxtype = "out";
		}
		composeDelete(id, "messages");
		
		setTimeout(function(){
			self.model.set("opening_message", false);
		}, 2000);
	},
	canceldeleteMessage: function(event) {
		event.preventDefault();
		$("#confirm_delete").fadeOut();
	},
	deleteMessage: function(event) {
		event.preventDefault();
		var id = $("#confirm_delete_id").val();
		var blnDeleted = deleteElement(event, id, "messages");
		if (blnDeleted) {
			//hide the delete module now
			this.canceldeleteMessage(event);
			$(".message_row_" + id).css("background", "red");
			setTimeout(function() {
				//hide the processed row, no longer a batch scan
				$(".message_row_" + id).fadeOut();
				
				checkInbox();
			}, 2500);
		}
		/*
		var self = this;
		var element = event.target;
		var id = element.id.split("_")[1];
		event.preventDefault();
		
		var url = "api/messages/delete";
		formValues = "id=" + id + "&table_name=messages";

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
                if(data.error) {  // If there is an error, show the error messages
                    saveFailed(data.error.text);
                }
                else { // If not, send them back to the home page
					self.collection.remove(kases.get(id));
					//mark the row as red
					$(".message_row_" + id).css("background", "red");
					//remove it
					setTimeout(function(){
						$(".message_row_" + id).fadeOut();
					}, 1500);
                }
            }
        });
		*/
	},
	showReadDates: function(event) {
		var element = event.currentTarget;
		var id = element.id.split("_")[2];
		
		$(".read_date_" + id).fadeIn();
	},
	freezePreview: function(event) {
		 freezeMessagePreview(event);
	},
	findIt: function(event) {
		var element = event.currentTarget;
		findIt(element, 'message_listing', 'message');
	}
});
window.message_print_listing = Backbone.View.extend({
	render: function(){
		var mymodel = this.model.toJSON();
		var case_name = "";
		var case_number = "";
		if (typeof mymodel.case_id != "undefined") {
			case_name = mymodel.case_name;
			case_number = mymodel.file_number;
			if (case_number=="" && mymodel.case_number!="") {
				case_number = mymodel.case_number;
			}
		}
		if (typeof mymodel.title == "undefined") {
			this.model.set("title", "");
		}
		var title = this.model.get("title");
		$(this.el).html(this.template({messages: this.collection.toJSON(), case_name: case_name, case_number: case_number, title: title}));
		
		return this;
	}
});
window.reminder_listing = Backbone.View.extend({
	render: function() {
		var self = this;
		
		var mymodel = this.model.toJSON();
		var messages = this.collection.toJSON();
		/*
		_.each( messages, function(message) {
		});
		*/
		try {
			$(this.el).html(this.template({messages: messages}));
		}
		catch(err) {
			var view = "reminder_listing";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		return this;
	},
	events: {
		"click .reminder_row":			"readReminder"
	},
	readReminder: function(event) {
		var self = this;
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var id = arrID[1];
		var case_id = arrID[2];
		
		var url = 'api/messages/read';
		formValues = "id=" + id;
		this.model.set("message_id", id);
		this.model.set("case_id", case_id);
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$(".message_row_" + id).hide();
					//track the activity
					self.trackActivity();
					//open the case page in new window
					window.open("?n=#kase/" + case_id);
					//refresh the new message indicator
					setTimeout(function() {
						checkInbox();
					}, 1001);
				}
			}
		});
	},
	trackActivity: function() {
		var track_id = this.model.get("message_id");
		var case_id = this.model.get("case_id");
		var operation = "read";
		var billing_time = "";
		var initials = login_nickname;
		var activity = "Statute of Limitation notification was read by " + login_username;
		var category = "statute_limitation";
		
		formValues = "track_id=" + track_id + "&case_id=" + case_id + "&operation=" + operation + "&category=" + category + "&initials=" + initials + "&billing_time=" + billing_time + "&activity=" + encodeURIComponent(activity);
		var url = "api/activity/track";
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
				}
			}
		});
	}
});
window.eventreminder_listing = Backbone.View.extend({
	render: function() {
		var self = this;
		
		var mymodel = this.model.toJSON();
		var messages = this.collection.toJSON();
		/*
		_.each( messages, function(message) {
		});
		*/
		try {
			$(this.el).html(this.template({messages: messages}));
		}
		catch(err) {
			var view = "eventreminder_listing";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		return this;
	},
	events: {
		"click .edit_event":			"openEvent",
		"click .reminder_row":			"readReminder"
	},
	openEvent: function(event) {
		var self = this;
		event.preventDefault();
		var element = event.currentTarget;
		composeEvent(element.id);
	},
	readReminder: function(event) {
		var self = this;
		event.preventDefault();
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var id = arrID[1];
		var case_id = arrID[2];
		
		var url = 'api/messages/read';
		formValues = "id=" + id;
		this.model.set("message_id", id);
		this.model.set("case_id", case_id);
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					$(".message_row_" + id).hide();
					//track the activity
					self.trackActivity();
					//refresh the new message indicator
					setTimeout(function() {
						checkInbox();
					}, 1001);
				}
			}
		});
	},
	trackActivity: function() {
		var track_id = this.model.get("message_id");
		var case_id = this.model.get("case_id");
		var operation = "read";
		var billing_time = "";
		var initials = login_nickname;
		var activity = "Event Reminder was read by " + login_username;
		var category = "event_reminder";
		
		formValues = "track_id=" + track_id + "&case_id=" + case_id + "&operation=" + operation + "&category=" + category + "&initials=" + initials + "&billing_time=" + billing_time + "&activity=" + encodeURIComponent(activity);
		var url = "api/activity/track";
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
				}
			}
		});
	}
});
window.pendings_listing = Backbone.View.extend({
    initialize:function () {
        
    },
	events: {
		"click .assign_case":							"assignCase",
		"click .save_icon":								"saveAssign",
		"click #review_pending_emails":					"reviewPendingEmails",
		"click #dismiss_pending_emails":				"dismissMessage",
		"mouseout #pending_email_info_holder":			"partialDisplay",
		"mouseover #pending_email_info_holder":			"fullDisplay"
	},
    render:function () {	
		if (typeof this.template != "function") {
			this.model.set("holder", "email_feedback_text");
			var view = "pendings_listing";
			var extension = "php";
			
			loadTemplate(view, extension, this);
			return "";
		}	
		var self = this;
		var  messages = this.collection.toJSON();
		_.each( messages, function(message) {
       		//clean up
        	var arrFrom = message.from.split("|");
			for (var i = arrFrom.length - 1; i >=0 ; i--) {
				if (arrFrom[i].length=="") {
					arrFrom.shift();
				}
			}
			message.from = arrFrom.join(",");
		});
		
		$(this.el).html(this.template({messages: messages}));
		
		setTimeout(function() {
			self.doTimeouts();
		}, 1000);
		
		return this;
	},
	assignCase: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var element_id = element.id;
		var arrID = element_id.split("_");
		
		var message_id = arrID[arrID.length - 1];
		
		$("#assign_case_" + message_id).fadeOut(function() {
			$("#case_lookup_" + message_id).fadeIn();
			$("#pendings_save_" + message_id).html("Assign to Kase");
		});
	},
	doTimeouts: function() {
		var theme = {
			theme: "event",
			tokenLimit: 1,
			onAdd: function(item) {
				//get the id
				var theid = this[0].id;
				theid = theid.split("_")[2];
				$("#pendings_case_id_" + theid).val(item.id);
				$("#pendings_save_" + theid).fadeIn();
				$("li.token-input-token-event").css("width", "460px");
			}
		};	
		$(".pendings_listing .kase_input").tokenInput("api/kases/tokeninput", theme);		
	},
	saveAssign: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var element_id = element.id;
		var arrID = element_id.split("_");
		
		var message_id = arrID[arrID.length - 1];
		$("#case_lookup_" + message_id).fadeOut();
		var case_id = $("#pendings_case_id_" + message_id).val();
		
		//attach to case
		formValues = "id=" + message_id;
		formValues += "&case_id=" + case_id;
		var url = "api/messages/confirm_email";

		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					
					$(".pendings_row_" + message_id).css("background", "green");
					setTimeout(function() {
						$(".pendings_row_" + message_id).fadeOut();
						
						//if everything is gone
						var rows = $(".pendings_data_row");
						blnFoundOne = false;
						for (var i = 0; i < rows.length; i++) {
							if (rows[i].style.display != "none") {
								blnFoundOne = true;
								break;
							}
						}
						if (!blnFoundOne) {
							//hide the panel
							$("#email_info_holder").fadeOut();
						}
						
						checkInbox();
					}, 2500);
				}
			}
		});
	},
	reviewPendingEmails: function(event) {
		event.preventDefault();
		
		$("#email_feedback_holder .jsglyph-remove").trigger("click");
		document.location.href = "#thread/pendings";
	},
	dismissMessage: function(event) {
		event.preventDefault();
		
		$("#email_feedback_holder .jsglyph-remove").trigger("click");
	},
	partialDisplay: function() {
		setTimeout(function() {
			var email_info_holder = document.getElementById("email_info_holder");
			if (email_info_holder == null) {
				return;
			}
			email_info_holder.style.transition = "opacity 1s linear 0s";
			email_info_holder.style.opacity = 0.3;
		}, 1500);
	},
	fullDisplay: function() {
		var email_info_holder = document.getElementById("email_info_holder");
		email_info_holder.style.transition = "opacity 1s linear 0s";
		email_info_holder.style.opacity = 1;
	}
});