
var check_stack_id;
var check_unassigned_id;
var check_remote_id = false;

function checkRemoteImports() {
	check_remote_id = true;
	checkImports();
}
function pingMail() {
	var self = this;
	
	global_login_email = new Email({user_id: login_user_id, email_id: 'all3'});
	global_login_email.fetch({
		success: function (email) {
			
			var email_json = email.toJSON()[0];
			
			if(email_json==undefined)
			{
				return;
			}
			
			var email_active = email_json.active;
			if (email_active=="N") {
				//is there an account?
				//var blnHasAccount = (email.get("email_address")!="" && email.get("email_server")!="");
				var blnHasAccount = (email_json.email_address !="" && email_json.email_server !="");
				if (blnHasAccount) {
					$(".test_feedback").html("<span title='Email [" + email_json.email_address + "] Account is not active.\r\n\r\nPlease click here to connect to the account \r\nso that incoming emails can be processed by iKase' style='background:aquamarine; color:black; padding:2px; margin-left:20px;'><span id='activate_email' style='cursor:pointer'>Activate Email Account</span></span>");		
				}
				return;
			}
			login_email_name = email_json.email_name;
			if (login_email_name.indexOf("@gmail.com") > -1) {
				blnGMailUser = true;
			}
			//perform an ajax call to track views by current user
			var url = 'api/pingmail';
		
			$.ajax({
				url:url,
				type:'GET',
				dataType:"json",
				data: null,
				success:function (data) {
					$(".test_feedback_divider").show();
					if(data.error) {  // If there is an error, show the error messages
						if (!blnGoogleToken) {
							$(".test_feedback").html("<span style='background:red; color:white; padding:2px; margin-left:20px'>Connection Failed &#9747;</span>");
							if (blnGmail && blnGMailUser && !blnGmailAccessRequested) {
								$(".test_feedback").html("<span style='background:aqua; color:black; padding:2px; margin-left:20px; cursor:pointer' id='request_gmail'>Connect to GMail</span>");				
							}
						}
					} else {
						$(".test_feedback").html("<span title='Found:" + data.found + "' style='background:green; color:white; padding:2px; margin-left:20px'>Email Connected &#10003;</span>");			
					}
				},
				error: function (jqXHR, exception) {
					console.log('jqXHR:',jqXHR)
					console.log('exception:',exception)
				},
			});
		}
	});
}
function checkDOBs() {
	var url = "api/clientemails";
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		data: "",
		success:function (data) {
			if (data.length > 0) {
				//var the_count = maxHundred(data.length);
				var the_count = data.length;
				$(".dob_indicator").html(the_count);
				$(".dob_indicator").fadeIn();
				
				setTimeout(function() {
					if ($("#dob_indicator2").length > 0) {
						$("#dob_indicator2").fadeOut();
					}
				}, 25000);
			} else {
				$(".dob_indicator").fadeOut();
			}
		}
	});
	
	var month = moment().format("M");
	month++;
	if (month > 12) {
		month = 1;
	}
	var url = "api/clientemailsbymonth/" + month;
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		data: "",
		success:function (data) {
			if (data.length > 0) {
				//var the_count = maxHundred(data.length);
				var the_count = data.length;
				$("#next_month_dob_indicator").html(the_count);
				$("#next_month_dob_indicator").fadeIn();
			} else {
				$("#next_month_dob_indicator").fadeOut();
			}
		}
	});
}
function checkIntakes() {
	var url = "api/intakes";
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		data: "",
		success:function (data) {
			if (data.length > 0) {
				var the_count = maxHundred(data.length);
				$("#intake_indicator").html(the_count);
				$("#intake_indicator").fadeIn();
			} else {
				$("#intake_indicator").fadeOut();
			}
		}
	});
}
function checkImports(blnAfterImport) {
	if (typeof blnAfterImport == "undefined") {
		blnAfterImport = false;
	}
	clearTimeout(check_stack_id);
	//var stacks = new NewScans();
	
	var stacks = new StacksByType([], {stack_type: 'batchscan', blnUnassigned: true});
	stacks.fetch({
		success: function(data) {
			if (data.length > 0) {
				var current_number = $("#new_import_indicator").html();
				if (current_number=="") {
					current_number = 0;
				}
				var the_count = maxHundred(data.length);
				$(".new_import_indicator").html(the_count);
				$(".new_import_indicator").fadeIn();
				
				if (check_remote_id) {
					//start/keep flashing if found a different number
					if (current_number != data.length) {
						//start flashing
						flashWarning("new_import_indicator", true);
					}
				}
				//we might still be in import screen
				if ($("#batch_indicator").length > 0 && blnAfterImport) {
					$("#batch_indicator").html("Scan Process is now completed.  Please <a href='#imports' class='white_text' style='text-decoration:underline'>click here to review the imported documents</a><br>&nbsp;");
				}
			} else {
				$(".new_import_indicator").fadeOut();
			}
		}
	});
	
	//notifications
	checkNotifications();
	checkMydocumentCount();
	var import_interval = 150011;
	if (check_remote_id) {
		import_interval = 15011;
	}
	check_stack_id = setTimeout(function() {
		checkImports();
	}, import_interval);
}
function checkOrphanImports() {
	var stacks = new StacksByType([], {stack_type: 'orphan_batchscan'});
	stacks.fetch({
		success: function(data) {
			if (data.length > 0) {
				var the_count = maxHundred(data.length);
				$("#orphan_import_indicator").html(the_count);
				$("#orphan_import_indicator").fadeIn();
			} else {
				$("#orphan_import_indicator").fadeOut();
			}
		}
	});
}
function checkMydocumentCount() {
	
	var batchStacks = new StacksByType([], { stack_type: 'batchscan', blnNotifications: true });
	var unassignedStacks = new MyStacksByType([], { stack_type: 'unassigned', blnNotifications: true });

	// Fetch both collections in parallel
	$.when(batchStacks.fetch(), unassignedStacks.fetch()).done(function() {

		// Add a source_type to each record
		batchStacks.each(function(model) {
			model.set('source_type', 'Batchscan');
		});

		unassignedStacks.each(function(model) {
			model.set('source_type', 'Unassigned');
		});
		
		// Merge both collections
		var combinedStacks = new Backbone.Collection();
		combinedStacks.add(batchStacks.models);
		combinedStacks.add(unassignedStacks.models);
		
		// (Optional) remove duplicates based on model id
		combinedStacks = new Backbone.Collection(
			_.uniq(combinedStacks.models, false, function(m) { return m.id; })
		);
		var totalCount = combinedStacks.length;
		
		$("#new_my_document").css("display","block");
		$("#new_my_document").html(totalCount);
		
	});
}
function checkNotifications() {
	var url = "api/notifications";
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		data: "",
		success:function (data) {
			if (data.length > 0) {
				var the_count = maxHundred(data.length);
				$("#notifications_indicator").html(the_count);
				$("#notifications_indicator").fadeIn();
			} else {
				$("#notifications_indicator").fadeOut();
			}
		}
	});
}
function checkOrphanUnassigneds() {
	var my_stacks = new MyStacksByType([], {stack_type: 'orphan_unassigned'});
	my_stacks.fetch({
		success: function(data) {
			if (data.length > 0) {
				var the_count = maxHundred(data.length);
				$("#orphan_unassigned_indicator").html(the_count);
				$("#orphan_unassigned_indicator").fadeIn();
			} else {
				$("#orphan_unassigned_indicator").fadeOut();
			}
		}
	});
}
function checkUnassigneds() {
	clearTimeout(check_unassigned_id);
	var my_stacks = new MyStacksByType([], {stack_type: 'unassigned'});
	my_stacks.fetch({
		success: function(data) {
			if (data.length > 0) {
				var the_count = maxHundred(data.length);
				$(".unassigned_indicator").html(the_count);
				$(".unassigned_indicator").fadeIn();
			} else {
				$(".unassigned_indicator").fadeOut();
			}
		}
	});
	check_unassigned_id = setTimeout(function() {
		checkUnassigneds();
	}, 177011);
}
function checkClearedChecks() {
	var cleared_checks = new ChecksCollection([], { check_status: "C" });
	cleared_checks.fetch({
		success: function(data) {
			if (data.length > 0) {
				var the_count = maxHundred(data.length);
				$(".cleared_indicator").html(the_count);
				$(".cleared_indicator").fadeIn();
			} else {
				$(".cleared_indicator").fadeOut();
			}
		}
	});
	
	var uncleared_checks = new ChecksCollection([], { check_status: "U" });
	uncleared_checks.fetch({
		success: function(data) {
			if (data.length > 0) {
				var the_count = maxHundred(data.length);
				$(".uncleared_indicator").html(the_count);
				$(".uncleared_indicator").fadeIn();
			} else {
				$(".uncleared_indicator").fadeOut();
			}
		}
	});
	
	var printed_checks = new ChecksCollection([], { check_status: "printed" });
	printed_checks.fetch({
		success: function(data) {
			if (data.length > 0) {
				var the_count = maxHundred(data.length);
				$(".printed_indicator").html(the_count);
				$(".printed_indicator").fadeIn();
			} else {
				$(".printed_indicator").fadeOut();
			}
		}
	});
	
	var unprinted_checks = new ChecksCollection([], { check_status: "unprinted" });
	unprinted_checks.fetch({
		success: function(data) {
			if (data.length > 0) {
				var the_count = maxHundred(data.length);
				$(".unprinted_indicator").html(the_count);
				$(".unprinted_indicator").fadeIn();
			} else {
				$(".unprinted_indicator").fadeOut();
			}
		}
	});
	setTimeout(function() {
		checkClearedChecks();
	}, 197011);	
}
var get_inbox_id;
function getMail() {
	return;
	
	clearTimeout(get_inbox_id);
	//gather the emails in db
	var getmails = new GetMail();	//{eword: eword}
	getmails.fetch({
		success: function(data) {
			console.log("getmails:" + data.length);
			//we're going to get this stuff, there is no need of feedback to the system
			//check again in 5 minutes
			get_inbox_id = setTimeout(function() {
				getMail();
			},300037);
		}
	});
}
var check_inbox_id;
function logoutGmail() {
	if (!blnGmail) {
		return;
	}
	blnGoogleToken = false;
	var url = "https://www.ikase.xyz/ikase/gmail/ui/logout.php?user_id=" + login_user_id;
	//put it in iframe
	document.getElementById("check_gmail_messages").src = url;
}
function refreshGmailToken() {
	if (!blnGmail || !blnGmailAccessRequested) {
		return;
	}
	if (blnCheckingGmail) {
		return;
	}
	var url = "https://www.ikase.xyz/ikase/gmail/ui/refresh_token.php";
	//put it in iframe
	document.getElementById("check_gmail_messages").src = url;
}
function noToken() {
	window.Router.prototype.loginEmail();
}
function listMessagesClearToken() {
	if (!blnGoogleToken) {
		return;
	}
	
	return;
	
	var url = 'api/gmail/cleartoken';
	var formValues = "user_id=" + login_user_id + "&customer_id=" + customer_id;
	formValues += "&origin=check_boxes";
	$.ajax({
		url:url,
		type:'POST',
		dataType:"json",
		data: formValues,
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				//refresh recent kases listing
				listMessagesDone(false);
			}
		}
	});
}
function listMessagesDone(blnValidToken) {
	if (typeof blnValidToken != "undefined") {
		if (!blnValidToken) {
			//reset everything
			blnGoogleToken = false;
			if (blnGmailAccessRequested) {
				window.Router.prototype.loginEmail();
				return;
			}
		}
	}
	refreshPendingMessagesIndicator(true);
}
function checkGmailInbox() {
	if (!blnGmail || login_email_name == "") {
		return;
	}
	//return;
	blnCheckingGmail = true;
	var url = "https://www.ikase.xyz/ikase/gmail/ui/list_messages.php?customer_id=" + customer_id + "&user_id=" + login_user_id + "&email=" + encodeURIComponent(login_email_name);
	//put it in iframe
	document.getElementById("check_gmail_messages").src = url;
	
	setTimeout(function() {
		refreshPendingMessagesIndicator(true);
	}, 15000);
	console.log('382 checkGmailInbox');
	if (!blnGoogleToken) {
		console.log('384 checkGmailInbox');
		setTimeout(function() {
			//check if we already have a token
			var url = "api/gmail/token";
			var gmail_url = "https://www.ikase.xyz/ikase/gmail/ui/index.php";
			gmail_url += "?user_id=" + login_user_id + "&customer_id=" + customer_id + "&user_name=" + login_username + "&email=" + encodeURIComponent(login_email_name) + "&destination=" + encodeURIComponent(login_email_name) + "&hash=" + document.location.hash.substr(1);
			
			//do we have a current token
			$.ajax({
				url:url,
				type:'GET',
				dataType:"json",
				data: "",
				success:function (data) {
				console.log(data);
					blnCheckingGmail = false;
					
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					} else {
						if (data.access_token=="") {
							//var url = "../google-api-php-client-master/examples/index.php";
							//url += "?user_id=" + login_user_id + "&customer_id=" + customer_id + "&user_name=" + login_username + "&email=" + email_json.email_name + "&destination=" + email_json.email_address;
							document.location.href = gmail_url;
						} else {
							blnGoogleToken = true;
							checkInbox(false);
							refreshPendingMessagesIndicator(true);
						}
					}
				}
			});
			
		}, 5000);
	}
}
function refreshPendingMessagesIndicator(blnRefresh) {
	if (typeof blnRefresh == "undefined") {
		blnRefresh = false;
	}
	if (typeof global_threads_pending == "undefined" || blnRefresh) {
		var pending_messages = new ThreadInboxCollection({pending: "y"});
	} else {
		if (global_threads_pending.length > 0) {
			var display_length = global_threads_pending.length;
			if (display_length > 100) {
				display_length = "99+";
			}
			$(".pending_indicator").html(display_length);
			$("#pending_indicator_full").html(global_threads_pending.length);
			
			$(".pending_indicator").fadeIn();
		} else {
			$(".pending_indicator").html(global_threads_pending.length);
			$("#pending_indicator_full").html(global_threads_pending.length);
			$(".pending_indicator").fadeOut();
		}
		return;
	}
	pending_messages.fetch({
		success:function(data) {
			global_threads_pending = data;
			if (pending_messages.length > 0) {
				var display_length = pending_messages.length;
				if (display_length > 100) {
					display_length = "99+";
				}
				$(".pending_indicator").html(display_length);
				$(".pending_indicator").fadeIn();
			} else {
				$(".pending_indicator").html(pending_messages.length);
				$(".pending_indicator").fadeOut();
			}
			if (blnRefresh) {
				if ($("#refresh_thread").length > 0) {
					$("#refresh_thread").html('Synced &#10003;');
					//console.log("here it is");
					
					setTimeout(function() {
						if (document.location.hash=="#thread/pendings") {
							//console.log("in if");
							window.Router.prototype.listThreadInboxPendings();
						} else {						
							$("#refresh_thread").html('Sync Email');
							// console.log("in else reload");
							// location.reload();
						}
					}, 2500);
					// console.log("reload");
					// location.reload();
				}
			}
		}
	});
}
function checkInbox(blnRefresh) {
	//console.log('check_boxes.js');
	if (typeof blnRefresh == "undefined") {
		blnRefresh =  true;
	}
	
	//preload the threads inbox
	preloadInbox();
	console.log(login_email_name);
	if (login_email_name.indexOf("@gmail.com") > -1  && blnRefresh && blnGmailAccessRequested) {
		checkGmailInbox();
		return;
	}
	console.log('485 after gmail.com if');
	$(".new_message_indicator").html("");
	
	clearTimeout(check_inbox_id);
	//get messages
	var inbox_delay = "";
	if (customer_settings.get("inbox_delay") == "") {
		inbox_delay = user_settings.get("inbox_delay");
	} else {
		inbox_delay = customer_settings.get("inbox_delay");
	}
	/*
	if (inbox_delay=="" || typeof inbox_delay == "undefined") {
		inbox_delay = 60031;
	}
	*/
	//overwrite delays
	inbox_delay = 1200031;
	/*
	var customer_setting_delay = new CustomerSetting();
	customer_setting_delay.fetch({
		success:function(data) {
			//console.log(data);		
		}
	});
	*/
	var new_messages = new NewMessages();
	//console.log(new_messages+'511');
	new_messages.fetch({
		success:function(data) {
			if (new_messages.length > 0) {
				//go through them all, if any is high priority, change to blinky
				var messages_list = new_messages.toJSON();
				var blnUrgent = false;
				var StatuteMessages = new Backbone.Collection;
				var ReminderMessages = new Backbone.Collection;
				_.each(messages_list , function(message) {
					//will it blink red
					if (!blnUrgent) {
						if (message.priority=="high") {
							blnUrgent = true;
						}
					}
					//now do we have statute limitation expiring?
					if (message.subject == "Statute of Limitation Expiring") {
						//only show the message if the current user has not read it
						var arrTo = message.message_to.split(";")
						
						arrTo.forEach(
							function(element, index, array) { 
								if (element==login_nickname) {
									var read_date = message.read_dates.split("|")[index];
									if (read_date == "0000-00-00 00:00:00") {
										StatuteMessages.add(message);
									}
								}
							}
						);
					}
					
					//now do we have event reminders?
					if (message.subject == "Event Reminder") {
						//only show the message if the current user has not read it
						var arrTo = message.message_to.split(";")
						
						arrTo.forEach(
							function(element, index, array) { 
								if (element==login_nickname) {
									var read_date = message.read_dates.split("|")[index];
									if (read_date == "0000-00-00 00:00:00") {
										ReminderMessages.add(message);
									}
								}
							}
						);
					}
				});
				var the_count = maxHundred(new_messages.length);
				$(".new_message_indicator").html(the_count);
				if (blnUrgent) {
					//blink it
					flashWarning("new_message_indicator", true);
				} else {
					flashWarning("new_message_indicator", false);
					$(".new_message_indicator").css("background", "#06F");
				}
				if (!blnReceiveWebmail) {
					$(".new_message_indicator").fadeIn();
				}
				
				if ($(document).attr('title')=="Input :: iKase") {
					//only if we are in the inbox
					messages = new InboxCollection();
					messages.fetch({
						success: function (data) {
							var message_listing_info = new Backbone.Model;
							message_listing_info.set("title", "Inbox");
							message_listing_info.set("receive_label", "Received");
							$('#content').html(new message_listing({collection: data, model: message_listing_info}).render().el);
							$("#content").removeClass("glass_header_no_padding");
						}
					});
				}
				
				if (StatuteMessages.length == 0 && ReminderMessages.length == 0) {
					//hide notifications
					$("#site-footer").fadeOut(function() {
						$('#statute_reminders_holder').html("");
						$('#event_reminders_holder').html("");
					});
				}
				//statute warnings
				if (StatuteMessages.length > 0) {
					var message_listing_info = new Backbone.Model;
					message_listing_info.set("title", "Statute of Limitation");
					message_listing_info.set("first_column_label", "From");
					message_listing_info.set("receive_label", "Received");
					message_listing_info.set("holder", "statute_reminders_holder");
					$("#site-footer").fadeIn(function() {
						$('#statute_reminders_holder').html(new reminder_listing({collection: StatuteMessages, model: message_listing_info}).render().el);
					});
				} 
				//statute warnings
				if (ReminderMessages.length > 0) {
					var message_listing_info = new Backbone.Model;
					message_listing_info.set("title", "Event Reminders");
					message_listing_info.set("first_column_label", "From");
					message_listing_info.set("receive_label", "Received");
					message_listing_info.set("holder", "event_reminders_holder");
					$("#site-footer").fadeIn(function() {
						$('#event_reminders_holder').html(new eventreminder_listing({collection: ReminderMessages, model: message_listing_info}).render().el);
					});
				} 
			} else {
				if (!blnReceiveWebmail) {
					$(".new_message_indicator").html("");
					$(".new_message_indicator").fadeOut();
				}
			}
		}
	});
	var options = {};
	if (customer_id == 1109) {
		options = {"showall": true};
	}
	var new_phone_messages = new NewPhoneCalls(options);
	new_phone_messages.fetch({
		success:function(data) {
			if (data.length > 0) {
				var messages_list = data.toJSON();
				var blnUrgent = false;
				_.each(messages_list , function(message) {
					if (message.event_priority=="high") {
						blnUrgent = true;
					}
				});
				var the_count = maxHundred(new_phone_messages.length);
				$("#new_phone_indicator").html(the_count);
				
				if (blnUrgent) {
					//blink it
					flashWarning("new_phone_indicator", true);
				} else {
					flashWarning("new_phone_indicator", false);
					$("#new_phone_indicator").css("background", "#3C9");
				}
				$("#new_phone_indicator").fadeIn();
				
				
				if ($(document).attr('title')=="Input :: iKase") {
					//only if we are in the inbox
					messages = new InboxCollection();
					messages.fetch({
						success: function (data) {
							var message_listing_info = new Backbone.Model;
							message_listing_info.set("title", "Inbox");
							message_listing_info.set("receive_label", "Received");
							$('#content').html(new message_listing({collection: data, model: message_listing_info}).render().el);
							$("#content").removeClass("glass_header_no_padding");
						}
					});
				}
			} else {
				$("#new_phone_indicator").html("");
				$("#new_phone_indicator").fadeOut();
			}
		}
	});
	
	//drafts
	var messages = new DraftsCollection();
	messages.fetch({
			success: function (data) {
				$(".drafts_indicator").html(data.length);
			}
	});
	
	check_inbox_id = setTimeout(function() {
		checkInbox();
	}, (inbox_delay));	// * 10
}
function setInboxIndicator(message_count) {
	if (message_count > 0) {
		$(".new_message_indicator").html(message_count);
		$(".new_message_indicator").fadeIn();
		
		if ($(document).attr('title')=="Input :: iKase") {
			//only if we are in the inbox
			messages = new InboxCollection();
			messages.fetch({
				success: function (data) {
					var message_listing_info = new Backbone.Model;
					message_listing_info.set("title", "Inbox");
					message_listing_info.set("receive_label", "Received");
					$('#content').html(new message_listing({collection: data, model: message_listing_info}).render().el);
					$("#content").removeClass("glass_header_no_padding");
				}
			});
		}
	} else {
		$(".new_message_indicator").html("");
		$(".new_message_indicator").fadeOut();
	}
}

var check_task_inbox_id;
function checkTaskInbox() {
	//not for nick
	if (login_nickname == "ng") {
		return;
	}
	clearTimeout(check_task_inbox_id);
	var task_delay = "";
	if (customer_settings.get("task_delay") == "") {
		task_delay = user_settings.get("task_delay");
	} else {
		task_delay = customer_settings.get("task_delay");
	}
	if (task_delay=="" || typeof task_delay == "undefined") {
		task_delay = 60000;
	}
	//overwrite
	task_delay = 160047;
	//get tasks
	//var new_tasks = new NewTasks();
	//new_tasks.fetch({
	var all_tasks = new TaskInboxCollection();
	all_tasks.fetch({
		success:function(data) {
			if (all_tasks.length > 0) {
				var the_count = maxHundred(all_tasks.length);
				$(".task_count_indicator").html(the_count);
				$(".task_count_indicator").show();
				
				if ($(document).attr('title')=="Input :: iKase") {
					//only if we are in the task_inbox					
					var task_listing_info = new Backbone.Model;
					task_listing_info.set("title", "TaskInbox");
					task_listing_info.set("receive_label", "Received");
					$('#content').html(new task_listing({collection: data, model: task_listing_info}).render().el);
					$("#content").removeClass("glass_header_no_padding");
				
				}
			} else {
				$(".task_count_indicator").html("");
				$(".task_count_indicator").hide();
			}
		}
	});
	
	//todays task
	day = moment().format("YYYY-MM-DD");
	var daily_tasks = new TaskInboxCollection({day: day, single_day: "y"});
	daily_tasks.fetch({
		success: function (data) {
			var the_count = maxHundred(daily_tasks.length);
			if (daily_tasks.length > 0) {
				$(".daily_task_indicator").html(the_count);
				$(".daily_task_indicator").show();
			} else {
				$(".daily_task_indicator").html("");
				$(".daily_task_indicator").hide();
			}
		}
	});
	
	//outbox task
	var url = 'api/taskoutboxcount';
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else { // If not
				var the_count = maxHundred(data.count);
				if (data.count > 0) {
					$(".outbox_task_indicator").html(the_count);
					$(".outbox_task_indicator").show();
				} else {
					$(".outbox_task_indicator").html("");
					$(".outbox_task_indicator").hide();
				}
			}
		}
	});
	
	//upcoming tasks
	var upcoming_tasks = new TaskInboxCollection({day: day});
	upcoming_tasks.fetch({
		success: function (data) {
			var the_count = maxHundred(upcoming_tasks.length);
			if (upcoming_tasks.length > 0) {
				$(".upcoming_task_indicator").html(the_count);
				$(".upcoming_task_indicator").show();
			} else {
				$(".upcoming_task_indicator").html("");
				$(".upcoming_task_indicator").hide();
			}
		}
	});
	check_task_inbox_id = setTimeout(function() {
		checkTaskInbox();
	}, (task_delay * 5));
}
function setTaskInboxIndicator(task_count) {
	if (task_count > 0) {
		var the_count = maxHundred(task_count);
		$(".new_task_indicator").html(the_count);
		$(".new_task_indicator").fadeIn();
		
		if ($(document).attr('title')=="Input :: iKase") {
			//only if we are in the task_inbox
			tasks = new TaskInboxCollection();
			tasks.fetch({
				success: function (data) {
					var task_listing_info = new Backbone.Model;
					task_listing_info.set("title", "TaskInbox");
					task_listing_info.set("receive_label", "Received");
					$('#content').html(new task_listing({collection: data, model: task_listing_info}).render().el);
					$("#content").removeClass("glass_header_no_padding");
				}
			});
		}
	} else {
		$(".new_task_indicator").html("");
		$(".new_task_indicator").fadeOut();
	}
}
function refreshEmailInfo() {
	var pendings = new PendingMessages();
	pendings.fetch({
		success:function(data) {
			var mymodel = new Backbone.Model;
			mymodel.set("holder", "email_feedback_text");
			
			if (data.length==0) {
				$("#email_feedback_text").append("<div style='background:white; color:black'>No new Emails as of" + moment().format("hh:mma") + "</div>");
				
				$("#email_info_holder").fadeOut();
			} else {
				$("#email_info_holder").fadeIn();
			}
			$('#email_feedback_text').html(new pendings_listing({collection: data, model: mymodel}).render().el);
			
			//we're out in 20 seconds
			setTimeout(function() {
				if (document.getElementById("email_info_holder") != null) {
					var email_info_holder = document.getElementById("email_info_holder");
					email_info_holder.style.transition = "opacity 1s linear 0s";
					email_info_holder.style.opacity = 0;
					
					setTimeout(function() {
						$("#email_feedback_holder .jsglyph-remove").trigger("click");
					}, 1200);
				}
			}, 20000);
		}
	});
}
function refreshInternetCalendars() {
	var url = "api/ics";
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		data: "",
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				//done
			}
		}
	});
}
function refreshOutstandingInvoices() {
	
	var url = "api/firminvoicescount";
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		data: "",
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				//done
				if (data.count > 0) {
					var the_count = maxHundred(data.count);
					$("#outstanding_invoices_indicator").html(the_count);
					$("#outstanding_invoices_indicator").show();
				} else {
					$("#outstanding_invoices_indicator").html("");
					$("#outstanding_invoices_indicator").hide();
				}
			}
		}
	});
	/*
	var url = "api/prebillinvoicescount";
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		data: "",
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				//done
				if (data.count > 0) {
					$("#prebill_invoices_indicator").html(data.count);
					$("#prebill_invoices_indicator").show();
				} else {
					$("#prebill_invoices_indicator").html("");
					$("#prebill_invoices_indicator").hide();
				}
			}
		}
	});
	*/
	var url = "api/paidinvoicescount";
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		data: "",
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				//done
				if (data.count > 0) {
					$("#paid_invoices_indicator").html(data.count);
					$("#paid_invoices_indicator").show();
				} else {
					$("#paid_invoices_indicator").html("");
					$("#paid_invoices_indicator").hide();
				}
			}
		}
	});
	
	var url = "api/checkrequests";
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		data: "",
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				//done
				if (data.length > 0) {
					var the_count = maxHundred(data.length);
					$(".checkrequest_indicator").html(the_count);
					$(".checkrequest_indicator").show();
				} else {
					$(".checkrequest_indicator").html("");
					$(".checkrequest_indicator").hide();
				}
			}
		}
	});
	
	var url = "api/accountrequests/trust/P";
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		data: "",
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				//done
				if (data.length > 0) {
					var the_count = maxHundred(data.length);
					$(".checkrequest_trust_indicator").html(the_count);
					$(".checkrequest_trust_indicator").show();
				} else {
					$(".checkrequest_trust_indicator").html("");
					$(".checkrequest_trust_indicator").hide();
				}
			}
		}
	});
	
	var url = "api/accountrequests/operating/P";
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		data: "",
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				//done
				if (data.length > 0) {
					var the_count = maxHundred(data.length);
					$(".checkrequest_operating_indicator").html(the_count);
					$(".checkrequest_operating_indicator").show();
				} else {
					$(".checkrequest_operating_indicator").html("");
					$(".checkrequest_operating_indicator").hide();
				}
			}
		}
	});
	
	var url = "api/checkrequests/mine/pending";
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		data: "",
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				//done
				var the_count = maxHundred(data.length);
				$(".my_checkrequest_indicator").html(the_count);
				$(".my_checkrequest_indicator").show();
			}
		}
	});
	
	var url = "api/checkrequests/mine/late";
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		data: "",
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				//done
				if (data.length > 0) {
					var the_count = maxHundred(data.length);
					$(".my_late_checkrequest_indicator").html(the_count);
					$(".my_late_checkrequest_indicator").show();
				} else {
					$(".my_late_checkrequest_indicator").html("");
					$(".my_late_checkrequest_indicator").hide();
				}
			}
		}
	});
	
	
	
	/*
	var url = "api/kases/billablescount";
	$.ajax({
		url:url,
		type:'GET',
		dataType:"json",
		data: "",
		success:function (data) {
			if(data.error) {  // If there is an error, show the error messages
				saveFailed(data.error.text);
			} else {
				//done
				if (data.count > 0) {
					$("#billables_indicator").html(data.count);
					$("#billables_indicator").show();
				} else {
					$("#billables_indicator").html("");
					$("#billables_indicator").hide();
				}
			}
		}
	});
	*/
	setTimeout(function() {
		refreshOutstandingInvoices();
	}, 3600000);
}
function preloadInbox() {
	var threads = new ThreadInboxCollection();
	threads.fetch({
		success: function (data) {
			global_threads = data;
		}
	});
}
function getOutbox() {
	var messages = new OutboxCollection();
	
	messages.fetch({
		success: function (data) {
			outboxCollection = data;
		}
	});	
}