var blnCalendarListing = false;
function composeEvent(element_id, user_id) {
	if (typeof user_id == "undefined") {
		user_id = "";
	}
	$("#gifsave").hide();
	$("#modal_save_holder").show();
	var eventArray = element_id.split("_");
	var event_array_id = eventArray[0];
	var occurence = new Occurence ({event_id: event_array_id});
	occurence.set("id", event_array_id);
	var injury_id = "";
	var event_type = "";
	var subject = "";
	if (eventArray.length==5) {
		if (eventArray[4]=="appearance") {
			//leave the type blank, let them flavor it themselves
			//event_type = "Lien Appearance";
			injury_id = eventArray[3];
			eventArray[3] = "";
			subject = "Lien Appearance";
		}
		if (eventArray[4]=="intake") {
			//leave the type blank, let them flavor it themselves
			subject = "Intake";
			occurence.set("event_kind", "intake");
		}
		if (eventArray[4]=="employee") {
			//leave the type blank, let them flavor it themselves
			occurence.set("event_kind", "employee");
		}
	}
	if (occurence.get("event_kind") == "undefined") {
		occurence.set("event_kind", "");
	}
	occurence.set("injury_id", injury_id);
	occurence.set("subject", subject);
	occurence.fetch({
		success: function(data) {
			if (eventArray[1]=="null") {
				eventArray[1] = data.get("case_id");
			}
			if (typeof eventArray[3] != "undefined" && data.get("event_dateandtime")=="") {
				if (eventArray[3]!="") {
					var event_dateandtime = moment.utc(Number[eventArray[3]]);
					var d = new Date(); 
					d.setTime(eventArray[3]);
					moment(d).format("MM/DD/YYYY");
					data.set("event_dateandtime", String(d).replace("00:00:00", "08:30:00"));
				}
			}
			if (event_type=="") {
				data.set("modal_title", "Event");
			} else {
				data.set("modal_title", "Lien Appearance");
			}
			if (occurence.get("event_kind") == "") {
				data.set("event_kind", data.get("event_type"));
			}
			data.set("case_id", eventArray[1]);
			data.set("user_id", user_id);
			/*
			if (data.id < 0) {
				if (eventArray[1] > 0) {
					var kase = kases.findWhere({case_id: eventArray[1]});
					if (typeof kase != "undefined") {
						//default title is name of event
						//data.set("event_title", kase.get("full_name") + " vs " + kase.get("employer"));
						data.set("event_title", kase.get("name"));
						var venue_address = kase.get("venue_street");
						if (kase.get("venue_suite")!="") {
							venue_address += ", " + kase.get("venue_suite");
						}
						venue_address += ", " + kase.get("venue_city");
						data.set("full_address", venue_address);
					}
				}
			}
			*/
			//who created by
			if (data.get("event_from")=="") {
				data.set("event_from", login_username);
			}
			var blnEventCreator = (data.get("event_from")==login_username);
			if (!blnEventCreator) {
				if (user_id!="") {
					if (user_id == login_user_id) {
						blnEventCreator = true;
					}
				}
			}
			//you don't have to be the creator if you have write permission
			if (!blnEventCreator) {
				if (user_id!="") {
					permissions = current_employee_calendars.findWhere({id: user_id}).get("permissions");
					if (permissions.indexOf("write")>-1) {
						blnEventCreator = true;
					}
				}
			}
			//event creator only for some
			if (customer_id!=1075 && customer_id!=1033) {
				//HARD CODE EVENT CREATOR FOR NOW, turn off functionality
				blnEventCreator = true;
			}
			if (customer_id != "1033x") {
				var modal_title;
				if (data.id < 0) {
					var bell = "";
					modal_title = "New Event";
					//calendar name
					if ($(".calendar_title#page_title").length > 0) {
						modal_title += " - " + $(".calendar_title#page_title").html();
					}
					if (subject=="Lien Appearance") {
						modal_title = subject;
						data.set("event_duration", 240);
					}
				} else {
					if (blnEventCreator) {
						var blnDeletePermission = true;
						if (customer_id==1075 || customer_id==1033) {
							blnDeletePermission = false;
							if (blnAdmin) {
								blnDeletePermission = true;
							}
						}
						var delete_display = "none";
						if (blnDeletePermission) {
							delete_display = "";
						}
						modal_title = "Edit Event - ID " + data.get("event_id") + "&nbsp;<a href='javascript:setDeleteEvent()' title='Click to enable deletion of this event' style='font-weight:normal;color:red;font-size:0.7em;cursor:pointer;display:" + delete_display + "'>Delete this Event</a>";
					} else {
						modal_title = "Review Event - ID " + data.get("event_id");
					}
					//var bell = "<a href='javascript:showReminder()' id='reminder_tab' style='font-size:0.7em' class='white_text event_stuff'><i style='font-size:1.5em; color:#FFCC33; cursor:pointer' class='glyphicon glyphicon-bell' id='show_reminders' title='Click to show Reminders'></i></a>&nbsp;&nbsp;";
					var bell = "&nbsp;";
				}
				$("#input_for_checkbox").hide();
				if (blnEventCreator) {
					modal_title = modal_title + "&nbsp;&nbsp;</div><div style='float:right; border:0px solid red'>" + bell + "<a href='javascript:showRecurrent()' id='reminder_tab' style='font-size:0.7em' class='white_text'><i style='font-size:1.5em; color:white; cursor:pointer' class='glyphicon glyphicon-refresh' id='show_recurrent' title='Click to make recurrent'></i></a>&nbsp;&nbsp;<a href='javascript:showEvent()' id='reminder_tab_event' style='font-size:0.7em; display:none' class='white_text reminder_stuff'><i style='font-size:1.5em; color:white; cursor:pointer' class='glyphicon glyphicon-calendar' id='show_event' title='Click to show Event Details'></i></a></div>";
				}
				
				$("#myModalLabel").html(modal_title);
				if (blnPiReady) { 
					var dropdown_template = billing_dropdown_template;
					dropdown_template = dropdown_template.replace("margin-left:200px", "margin-left:350px");
					//$("#modal_billing_holder").html(dropdown_template);
				}
				if (blnEventCreator) {
					$("#modal_save_holder").html('<a title="Save Event" class="interoffice save" onClick="saveEventModal(event)" style="cursor:pointer; width:10px; margin-right:10px"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px"></i></a>');
					
				} else {
					bell = ""
					setTimeout(function() {
						$("#modal_save_holder").html("");
					}, 700);
				}
				
				if (subject!="") {
					data.set("event_title", subject);
				}
				data.set("holder", "myModalBody");
				// if(customer_id != 1033){
					$("#myModalBody").html(new event_view({model: data}).render().el);
				// } else {
				// 	$("#myModalBody").html(new event_view2({model: data}).render().el);
				// }
				$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
				$("#myModalBody").css("background-image", "url('img/glass_edit_header_new.png')");
				$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
				$(".modal-body").css("overflow-x", "hidden");
				$(".modal-dialog").css("width", "620px");
	
				$(".modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");
				//setTimeout("$('.modal-dialog').css('top', '37%');", 500);
				setTimeout(function() {
					//$('.modal-dialog').css('top', '0px');
					//$('.modal-dialog').css('margin-top', '50px')
				}, 700);
				$(".modal-content").css("background-image", "url('img/glass_edit_header_new.png')");
				
				$('#myModal4').modal('show');
			} else {
				var modal_title;
				if (data.id < 0) {
					var bell = "";
					modal_title = "New Event";
					//calendar name
					if ($(".calendar_title#page_title").length > 0) {
						modal_title += " - " + $(".calendar_title#page_title").html();
					}
					if (subject=="Lien Appearance") {
						modal_title = subject;
						data.set("event_duration", 240);
					}
				} else {
					//var modal_title = "";
					if (blnEventCreator) {
						var blnDeletePermission = true;
						if (customer_id==1075) {
							blnDeletePermission = false;
							if (blnAdmin) {
								blnDeletePermission = true;
							}
						}
						var delete_display = "none";
						if (blnDeletePermission) {
							delete_display = "";
						}
						modal_title = "<div style='float:left;' id='dialog_title_section_content'>Edit Event - ID " + data.get("event_id") + "&nbsp;<a href='javascript:setDeleteEvent()' class='delete_event_dialog_link' title='Click to enable deletion of this event' style='font-weight:normal;color:red;font-size:0.7em;cursor:pointer;display:" + delete_display + "'>Delete this Event</a></div>";
					} else {
						modal_title = "Review Event - ID " + data.get("event_id");
					}
					//var bell = "<a href='javascript:showReminder()' id='reminder_tab' style='font-size:0.7em' class='white_text event_stuff'><i style='font-size:1.5em; color:#FFCC33; cursor:pointer' class='glyphicon glyphicon-bell' id='show_reminders' title='Click to show Reminders'></i></a>&nbsp;&nbsp;";
					var bell = "&nbsp;";
				}
				
				$("#input_for_checkbox").hide();
				/*
				if (blnEventCreator) {
					modal_title = modal_title + "&nbsp;&nbsp;<div style='float:right'>" + bell + "<a href='javascript:showRecurrent()' id='reminder_tab' style='font-size:0.7em' class='white_text'><i style='font-size:1.5em; color:white; cursor:pointer' class='glyphicon glyphicon-refresh' id='show_recurrent' title='Click to make recurrent'></i></a>&nbsp;&nbsp;<a href='javascript:showEvent()' id='reminder_tab_event' style='font-size:0.7em; display:none' class='white_text reminder_stuff'><i style='font-size:1.5em; color:white; cursor:pointer' class='glyphicon glyphicon-calendar' id='show_event' title='Click to show Event Details'></i></a></div>";
				}
				
				$("#myModalLabel").html(modal_title);
				if (blnEventCreator) {
					$("#modal_save_holder").html('<a title="Save Event" class="interoffice save" onClick="saveEventModal(event)" style="cursor:pointer"><i class="glyphicon glyphicon-saved" style="color:#00FF00; font-size:20px">&nbsp;</i></a>');
					
				} else {
					bell = ""
					$("#modal_save_holder").html("");	
				}
				$("#myModalBody").html(new event_view({model: data}).render().el);
				*/
				if (subject!="") {
					data.set("event_title", subject);
				}
				data.set("modal_title", modal_title);
				data.set("holder", "dialog_content");
				setTimeout( function() {
				renderEventDialog(current_case_id, modal_title, data);
				}, 500);
				/*
				$(".modal-header").css("background-image", "url('img/glass_edit_header_new.png')");
				$("#myModalBody").css("background-image", "url('img/glass_edit_header_new.png')");
				$(".modal-footer").css("background-image", "url('img/glass_edit_header_new.png')");
				$(".modal-body").css("overflow-x", "hidden");
				$(".modal-dialog").css("width", "620px");
	
				$(".modal-dialog").css("background-image", "url('img/glass_edit_header_new.png')");
				$(".modal-content").css("background-image", "url('img/glass_edit_header_new.png')");
				*/
			}
		}
	});
	var modal_title = "";
	if (eventArray.length > 2) {
		modal_title = "Event";
	}
}
var current_max_track_id = 0;
function checkForChanges(table_name) {
	if (typeof stored_customer_events == "undefined") {
		stored_customer_events = new OccurenceStoredCustomerCollection();
		var fetched_customer_events = new OccurenceCustomerCollection();
		
		stored_customer_events.fetch({
			success: function(stored_customer_events) {
				if (stored_customer_events.length==0) {
					fetched_customer_events.fetch({
						success: function(data) {
							var the_events = data.toJSON();
							_.each( the_events, function(customer_event) {
								var myOccurence = new Occurence({event_id: customer_event.id});
								myOccurence.set(customer_event);
								stored_customer_events.add(myOccurence);
								myOccurence.save();
							});
							checkForChanges(table_name);
						}
					});
				} else {
					checkForChanges(table_name);
				}
			}
		});
		return;
	}
	switch(table_name) {
		case "event":
			var the_model = new EventLastChange();
			break;
	}
	the_model.fetch({
		success: function(data) {
			event_max_track_id = Number(data.get("max_track_id"));
			//if it's more than our current, refresh
			if (current_max_track_id != event_max_track_id) {
				clearStoredEvents(current_max_track_id, event_max_track_id);
			}
			//need the cal sort order for in house
			if (typeof stored_customer_events != "undefined") {
				if (typeof stored_customer_events.toJSON()[0] != "undefined") {
					if (typeof stored_customer_events.toJSON()[0].cal_sort_order == "undefined") {
						clearStoredEvents(current_max_track_id, 0);
					}
				}
			}
		}
	});
	
	setTimeout(function(){
		checkForChanges(table_name);
	}, 61000);
}
function clearStoredEvents(current_max_track_id, event_max_track_id, pure_refresh) {
	if (typeof pure_refresh == "undefined") {
		pure_refresh = false;
	}
	if (typeof stored_customer_events != "undefined") {
		stored_customer_events.fetch({
			success: function(stored_customer_events) {
				_.chain(stored_customer_events.models).clone().each(function(model){
					model.destroy();
				});
				stored_customer_events = undefined;
				refreshEvents(current_max_track_id, event_max_track_id, pure_refresh);
			}
		});
	} else {
		refreshEvents(current_max_track_id, event_max_track_id, pure_refresh);
	}
}
function refreshEvents(max_track_id, new_max_track_id, pure_refresh) {
	//sometimes, i just want to refresh and not do anything else
	if (typeof pure_refresh == "undefined") {
		pure_refresh = false;
	}
	if (typeof stored_customer_events == "undefined") {
		stored_customer_events = new OccurenceStoredCustomerCollection();
		var fetched_customer_events = new OccurenceCustomerCollection();
		
		stored_customer_events.fetch({
			success: function(stored_customer_events) {
				if (stored_customer_events.length==0) {
					fetched_customer_events.fetch({
						success: function(data) {
							var the_events = data.toJSON();
							_.each( the_events, function(customer_event) {
								var myOccurence = new Occurence({event_id: customer_event.id});
								myOccurence.set(customer_event);
								stored_customer_events.add(myOccurence);
								myOccurence.save();
							});
							if (!pure_refresh) {
								refreshEvents(max_track_id, new_max_track_id);
							}
						}
					});
				} else {
					if (!pure_refresh) {
						refreshEvents(max_track_id, new_max_track_id);
					}
				}
			}
		});
		return;
	}
	var changed_customer_events = new OccurenceCustomerChangedCollection({max_track_id: max_track_id});		
	changed_customer_events.fetch({
		success: function (data) {
			var jdata = data.toJSON();
			_.each(jdata, function(customer_event) {
				//determine if this was a delete
				if (customer_event.operation=="delete") {
					//stored_customer_events.remove(customer_event.id);
				} else {
					//stored_customer_events.set(customer_event, {remove: false});
					
					//store in localStorage
					var myOccurence = new Occurence({event_id: customer_event.id});
					myOccurence.set(customer_event);
					stored_customer_events.add(myOccurence);
					myOccurence.save();
				}
			});
			//reset current values
			//reset the current value
			current_max_track_id = new_max_track_id;
			
			//keep track for next restart
			writeCookie('current_max_event_track_id', current_max_track_id, 24*60*60*1000);
			
			//do we need to refresh the calendar
			var arrURL = document.location.href.split("#");
			if (arrURL.length == 2){
				var arrPath = arrURL[1].split("/");
				if (arrPath[0]=="ikalendar" && arrPath[2]=="0") {
					if (!pure_refresh) {
						showCustomerKalendar(global_current_view, global_current_date);
					}
				}
			}
		}
	});
}
function listCustomerEvents(start, end, case_id, thetype, theworker) {
	if (typeof case_id == "undefined") {
		case_id = "";
	} else {
		var kase = kases.findWhere({case_id: case_id});
	}
	if (typeof thetype == "undefined") {
		thetype = "";
	}
	if (typeof theworker == "undefined") {
		theworker = "";
	}
	var the_container = "#content";
	if (case_id != "") {
		the_container = "#kase_content";
	}
	readCookie();
	$(the_container).html(loading_image);
	
	var display_start = moment(start).format("MM/DD/YYYY");
	var display_end = moment(end).format("MM/DD/YYYY");
	
	if (display_start != display_end) {
		display_start += " through " + display_end;
	}
	var blnCaseType = (document.location.hash.indexOf("#firmkalendar/") > -1);
	if (case_id == "") {
		if (blnCaseType) {
			var arrHash = document.location.hash.split("/");
			var case_type = arrHash[1];
			var start = arrHash[2];
			var end = arrHash[3];
			var all_customer_events = new CustomerByCaseTypeEvents({case_type: case_type, type: thetype, start: start, end: end});
			
		} else {
			if (thetype=="") {
				if (theworker!="") {
					var all_customer_events = new CustomerByAssigneeEvents({worker: theworker, start: start, end: end});
				} else {
					var all_customer_events = new OccurenceCustomerCollection({start: start, end: end});
				}
			} else {
				if(theworker!="") {
					var all_customer_events = new CustomerByTypeByAssigneeEvents({worker: theworker, start: start, end: end, type: thetype});
				} else {
					var all_customer_events = new CustomerByTypeEvents({type: thetype, start: start, end: end});
				}
			}
		}
	} else {
		var all_customer_events = new OccurenceCollection({case_id: case_id});
	}
	
	//might be employee calendar
	if (document.location.hash.indexOf("userkalendar") > -1) {
		var all_customer_events = new UserCalendar({user_id: login_user_id, start: start, end: end});
	}
	all_customer_events.fetch({
		success: function (data) {
			//then re-assign to calendar
			var calendar_info = new Backbone.Model;
			var title_worker = "";
			if (theworker!="") {
				title_worker = " for " + theworker;
			}
			var title_type = "";
			if (thetype!="") {
				title_type = " - " + thetype;
			}
			if (case_id == "") {
				
				if (start!=end) {
					var the_title = "Events" + title_worker + title_type + ": " + display_start;
				} else {
					var the_title = "Events" + title_worker + title_type + ": <input type='text' id='dayview_start_date' style='width:100px' value='" + display_start + "' onkeyup='mask(this, mdate);' onblur='mask(this, mdate);' />";
				}
			} else {
				var the_title = "Events" + title_worker + title_type + ": " + kase.name();
			}
			if (blnCaseType) {
				the_title = case_type.toUpperCase() + " " + the_title;
			}
			calendar_info.set("title", the_title);
			calendar_info.set("homepage", false);
			calendar_info.set("event_class", "listing");
			calendar_info.set("worker", theworker);
			calendar_info.set("thetype", thetype);
			calendar_info.set("start", start);
			calendar_info.set("end", end);
			
			$(the_container).removeClass("glass_header_no_padding");
			
			occurencesView = new event_listing({el: $(the_container), collection: all_customer_events, model: calendar_info}).render();
			
			blnCalendarListing = false;
		}
	});
}
function renderCalendar(kase) {
	occurences = new OccurenceCollection({case_id: kase.get("case_id")});
	occurences.fetch({
			success: function(data) {
				//empty the content holder
				$("#kase_content").html("&nbsp;");
				
				//then re-assign to calendar
				occurencesView = new kase_occurences_view({el: $("#kase_content"), collection: occurences, model:kase}).render();
				$("#kase_content").toggleClass("glass_header_no_padding");
				
				//prep the assignees
				setTimeout(function () {
					tokenIt('assignee', 'employee');
				}, 100);
			}
		}
	);
}
function renderCalendarListByDate(kase, start,  end) {
	var occurences = new OccurenceCollection({case_id: kase.get("case_id"), start: start, end: end});
	
	var display_start = "";
	var display_end = "";
	if (start!="") {
		display_start = moment(start).format("MM/DD/YYYY");
		display_end = moment(end).format("MM/DD/YYYY");
		
		if (display_start != display_end) {
			display_start += " through " + display_end;
		}
		//however
		if (start=="1980-01-01") {
			display_start = " through " + display_end;
		}
	}
	occurences.fetch({
			success: function(occurences) {
				//empty the content holder
				$("#kase_content").html("&nbsp;");
				kase.set("title", "Kase Events: " + display_start);
				kase.set("homepage", false);
				kase.set("event_class", "listing");
				kase.set("start", start);
				kase.set("end", end);
				
				//then re-assign to calendar
				occurencesView = new event_listing({el: $("#kase_content"), collection: occurences, model:kase}).render();
			}
		}
	);
}
var saveeventmodal_timeout_id = false;
function saveEventModal(event) {
	clearTimeout(saveeventmodal_timeout_id);
	blnSaving = true;
	//check for a date
	var event_dateandtimeInput = $("#event_dateandtimeInput").val();
	if (event_dateandtimeInput=="") {
		$("#event_dateandtimeInput").css("border", "2px solid red");
		return;
	}
	if ($("#apply_tasks").length > 0) {
		if ($("#apply_tasks").prop("checked")) {
			if ($(".event #callback_dateInput").val()=="") {
				$(".event #callback_dateInput").css("border", "2px red solid");
				$(".event #follow_up_holder").css("background", "red");
				return;
			}
		}
	}
	// do not hide while saving reminders
	if (!blnDoNotCloseModal) {
		$(".event #gifsave").show();
		$("#modal_save_holder").hide();
		$("#reminder_tab").hide();
		$("#gifsave").css("margin-top", "-5px");
	}
	//give it a little delay to let the above show itself
	saveeventmodal_timeout_id = setTimeout(function() {
		saveActualEventModal(event);
		console.log('update');
		// this.render();
		if (typeof current_case_id != "undefined" && current_case_id > 0) {
			var kase = kases.findWhere({case_id: current_case_id});
			$("#kase_content").removeClass("glass_header_no_padding");
			console.log("coming");
			renderCalendarListByDate(kase);
			console.log("coming11");
		}
	}, 300);
	
	resetCurrentContent();
}
function addReminderValues(formValues) {
	//for neal
	var reminder_set = $('#reminder_set').val();
	formValues += "&reminder_set=" + reminder_set;
	//additions to form values to fit api/event/add in events_pack.php
	return formValues;
}
function getMyStuff(stuff) {	
    $('#reminder_set').val(stuff);
}
function saveActualEventModal(event) {
	addForm(event, "event");
	
	if (document.getElementById("apply_tasks")!=null) {
		if (document.getElementById("apply_tasks").checked) {		
			var tasks_url = 'api/task/add';
			var tasksformValues = $("#event_form").serialize();
			tasksformValues = tasksformValues.replaceAll("event", "task");
			tasksformValues = tasksformValues.replace("task_dateandtimeInput=", "ignore_me=");
			tasksformValues = tasksformValues.replace("callback_dateInput=", "task_dateandtimeInput=");
			
			//return;
			$.ajax({
			url:tasks_url,
			type:'POST',
			dataType:"json",
			data: tasksformValues,
				success:function (data) {
					if(data.error) {  // If there is an error, show the error messages
						saveFailed(data.error.text);
					}
					resetCurrentContent();
				}
			});	
		}
	}
}
function daynumberClick(event) {
	var element = event.currentTarget;
	//$('#calendar').fullCalendar( 'changeView', 'agendaDay' );
	//var current_date = $('#calendar').fullCalendar('getDate');
}
function showReminder() {
	$(".event_stuff").fadeOut(function(){
		$("#recurrent_table_screen").fadeOut();
		$(".reminder_stuff").fadeIn();
	});
}
function showRecurrent() {
	$("#reminder_tab_event").fadeIn();
	$(".reminder_stuff").fadeOut();
	$("#show_recurrent").fadeOut(function(){
		$("#event_table_screen").fadeOut();
		$("#reminder_tab_event").fadeIn();
		$("#recurrent_table_screen").fadeIn();
		;
	});
}
function showEvent() {
	$("#reminder_tab_event").fadeOut();
	$("#show_recurrent").fadeIn();
	$(".reminder_stuff").fadeOut(function(){
		$("#recurrent_table_screen").fadeOut();
		$(".event_stuff").fadeIn();
	});
}
var global_current_view = "";
var global_current_date = "";
function showEmployeeKalendar(view) {
	var current_date = moment().format("MM/DD/YYYY");
	var employee_events = new EmployeeCalendar();		
	employee_events.fetch({
		success: function (employee_events) {
			//then re-assign to calendar
			assignEmployeeEventsToCalendar(employee_events, view, current_date);
		}
	});
}
function showPartnerKalendar(view) {
	var current_date = moment().format("MM/DD/YYYY");
	var partner_events = new PartnerCalendar();		
	partner_events.fetch({
		success: function (partner_events) {
			//then re-assign to calendar
			assignPartnerEventsToCalendar(partner_events, view, current_date);
		}
	});
}
function getUserEvents() {
	if (typeof start == "undefined") {
		start = "";
	}
	if (typeof end == "undefined") {
		end = "";
	}
	if (typeof all_user_events == "undefined") {
		all_user_events = new UserCalendar({user_id: login_user_id});
		all_user_events.fetch({
			success: function (data) {
			}
		});
	}
}
function showCustomerKalendar(view, current_date) {
	if (blnAssigning) {
		return;
	}
	
	blnAssigning = true;
	global_current_view = view;
	global_current_date = current_date;
	
	var blnFetch = true;
	
	if (current_calendar_type!="") {
		current_calendar_type = current_calendar_type.toLowerCase();
		var all_customer_events = new CustomerByCaseTypeEvents({case_type: current_calendar_type});		
		
		all_customer_events.fetch({
			success: function (all_customer_events) {
				//then re-assign to calendar
				
				assignEventsToCalendar(all_customer_events, view, current_date);
				blnAssigning = false;
			}
		});
		return;
	}
	
	if (typeof stored_customer_events != "undefined") {
		stored_customer_events.fetch({ 
			reset: true,
			success: function(stored_customer_events) { 
				//console.log(data.toJSON()) 
				blnAssigning = false;
				assignEventsToCalendar(stored_customer_events, view, current_date);
			} ,
			error: (function (e) {
				console.log(' Service request failure: ' + e);
				var all_customer_events = new OccurenceCustomerCollection();		
				all_customer_events.fetch({
					success: function (all_customer_events) {
						//then re-assign to calendar
						console.log("reassigned");
						assignEventsToCalendar(all_customer_events, view, current_date);
						blnAssigning = false;
					},
					error: (function (e) {
						alert("Calendar Loading Error");
					})
				});
			})
		});
		blnFetch = false;
		return;
	} else {
		stored_customer_events = new OccurenceStoredCustomerCollection();
		var fetched_customer_events = new OccurenceCustomerCollection();
		
		stored_customer_events.fetch({
			success: function(stored_customer_events) {
				if (stored_customer_events.length==0) {
					fetched_customer_events.fetch({
						success: function(data) {
							var the_events = data.toJSON();
							_.each( the_events, function(customer_event) {
								var myOccurence = new Occurence({event_id: customer_event.id});
								myOccurence.set(customer_event);
								stored_customer_events.add(myOccurence);
								myOccurence.save();
							});
							showCustomerKalendar(view, current_date);
						}
					});
				} else {
					showCustomerKalendar(view, current_date);
				}
			}
		});
		blnFetch = false;
		return;
	}
	console.log("must fetch");
		
	if (blnFetch) {
		var all_customer_events = new OccurenceCustomerCollection();		
		all_customer_events.fetch({
			success: function (all_customer_events) {
				//then re-assign to calendar
				console.log("reassigned");
				assignEventsToCalendar(all_customer_events, view, current_date);
				blnAssigning = false;
			}
		});
	
	} else {
		console.log("assign events");
		assignEventsToCalendar(all_customer_events, view, current_date);
	}
}

function assignEventsToCalendar(all_customer_events, view, current_date, calendar_id) {
	$("#content").html("");
	var calendar_info = new Backbone.Model;
	calendar_info.set("view", view);
	calendar_info.set("current_date", "");
	var thecalendar = customer_calendars.findWhere({sort_order: "0"});
	var calendar_title = "Firm Kalendar";
	if (typeof thecalendar != "undefined") {
		calendar_title = thecalendar.get("calendar");
	}
	calendar_info.set("calendar_title", calendar_title);
	if (typeof current_date != "undefined") {
		calendar_info.set("current_date", current_date);
	}
	var the_collection = all_customer_events;
	if (current_calendar_type!="") {
		/*
		var type_events = all_customer_events.where({case_type : current_calendar_type});
		var the_collection = new Backbone.Collection(type_events);
		calendar_info.set("calendar_title", current_calendar_type + " Calendar");
		*/
		calendar_info.set("calendar_title", current_calendar_type.toUpperCase() + " Calendar");
		//reset right away
		current_calendar_type = "";
	}
	occurencesView = new kase_cus_occurences_view({el: $("#content"), collection: the_collection, model:calendar_info}).render();			
}
function assignEmployeeEventsToCalendar(employee_events, view, current_date, calendar_id) {
	var calendar_info = new Backbone.Model;
	calendar_info.set("view", view);
	calendar_info.set("current_date", "");
	
	var calendar_title = "Employee Kalendar";
	calendar_info.set("calendar_title", calendar_title);
	if (typeof current_date != "undefined") {
		calendar_info.set("current_date", current_date);
	}
	occurencesView = new employee_occurences_view({el: $("#content"), collection: employee_events, model:calendar_info}).render();			
}
function assignPartnerEventsToCalendar(partner_events, view, current_date, calendar_id) {
	var calendar_info = new Backbone.Model;
	calendar_info.set("view", view);
	calendar_info.set("current_date", "");
	
	var calendar_title = "Partner Kalendar";
	calendar_info.set("calendar_title", calendar_title);
	if (typeof current_date != "undefined") {
		calendar_info.set("current_date", current_date);
	}
	occurencesView = new partner_occurences_view({el: $("#content"), collection: partner_events, model:calendar_info}).render();			
}
function showCustomerInhouseKalendar(view, current_date) {
	global_current_view = view;
	global_current_date = current_date;
	
	var blnFetch = true;
	//first look in local storage, if not there, fetch
	if (typeof stored_customer_events != "undefined") {
		stored_customer_events.fetch({ 
			success: function(stored_customer_events) { 
				//console.log(data.toJSON()) 
				var inhouse_customer_events = stored_customer_events.where({cal_sort_order: "1"}); 
				
				inhouse_customer_events = new Backbone.Collection(inhouse_customer_events);
				assignEventsToCalendar(inhouse_customer_events, view, current_date);
			} 
		});
		blnFetch = false;
		return;
	} else {
		stored_customer_events = new OccurenceStoredCustomerCollection();
		var fetched_customer_events = new OccurenceCustomerCollection();
		
		stored_customer_events.fetch({
			success: function(stored_customer_events) {
				if (stored_customer_events.length==0) {
					fetched_customer_events.fetch({
						success: function(data) {
							var the_events = data.toJSON();
							_.each( the_events, function(customer_event) {
								var myOccurence = new Occurence({event_id: customer_event.id});
								myOccurence.set(customer_event);
								stored_customer_events.add(myOccurence);
								myOccurence.save();
							});
							showCustomerInhouseKalendar(view, current_date);
						}
					});
				} else {
					showCustomerInhouseKalendar(view, current_date);
				}
			}
		});
		blnFetch = false;
		return;
	}
	if (blnFetch) {
		var all_customer_events = new OccurenceCustomerCollection();		
		all_customer_events.fetch({
			success: function (all_customer_events) {
				var inhouse_customer_events = stored_customer_events.where({cal_sort_order: "1"}); 
				//then re-assign to calendar
				assignEventsToCalendar(inhouse_customer_events, view, current_date);
			}
		});
	
	} else {
		var inhouse_customer_events = stored_customer_events.where({cal_sort_order: "1"}); 
		assignEventsToCalendar(inhouse_customer_events, view, current_date);
	}
}
function deleteEvent(event) {
	var self = this;
	event.preventDefault(); // Don't let this button submit the form
	var event_id = $('.event #table_id').val();
	var stored_event = stored_customer_events.findWhere({event_id: event_id});
	if (typeof stored_event != "undefined") {
		stored_customer_events.remove(stored_event);
	}
	
	deleteForm(event, "event");
	$('#myModal4').modal('hide');
	
	return;
}
function setDeleteEvent() {
	if (customer_id == 1033 && document.location.pathname.indexOf('v9') > -1) {
		$("#delete_event_button").fadeIn();
	} else {
		$("#modal_save_holder").html('<a onclick="deleteEvent(event)" title="Click to finalize deletion of this Event"><i style="font-size:15px; color:#FF3737; cursor:pointer" id="delete_event_button" class="glyphicon glyphicon-trash delete_document"></i></a>&nbsp;');
		$("#reminder_tab").hide();
	}
}
function resetCalendarAfterSave() {
	if (typeof current_case_id != "undefined" && current_case_id > 0) {
		var kase = kases.findWhere({case_id: current_case_id});
		$("#kase_content").removeClass("glass_header_no_padding");
		renderCalendarListByDate(kase);
	}
	//are we in firmkalendar mode
	if (window.location.href.indexOf("#ikalendar") > -1 && window.location.href.indexOf("/0") > -1) {
		var all_customer_events = new OccurenceCustomerCollection();
		all_customer_events.fetch({
			success: function (data) {
				//then re-assign to calendar
				$("#content").html("");
				
				var calendar_info = new Backbone.Model;
				calendar_info.set("view", global_current_view);
				calendar_info.set("current_date", "");
				var thecalendar = customer_calendars.findWhere({sort_order: "0"});
				var calendar_title = "Firm Kalendar";
				if (typeof thecalendar != "undefined") {
					calendar_title = thecalendar.get("calendar");
				}
				calendar_info.set("calendar_title", calendar_title);
				if (typeof current_calendar_start != "undefined") {
					calendar_info.set("current_date", current_calendar_start);
				}
				occurencesView = new kase_cus_occurences_view({el: $("#content"), collection: all_customer_events, model:calendar_info}).render();
			}
		});
	}
	//are we in intake mode
	if (window.location.href.indexOf("#ikalendar") > -1 && window.location.href.indexOf("/4") > -1) {
		var all_customer_intakes = new CustomerIntakeCollection();
		all_customer_intakes.fetch({
			success: function (data) {
				//then re-assign to calendar
				$("#content").html("");
				occurencesView = new intake_cus_occurences_view({el: $("#content"), collection: all_customer_intakes}).render();			
			}
		});
	}
	if (window.location.href.indexOf("#employee_kalendar") > -1) {
		showEmployeeKalendar('month');
	}
	if (window.location.href.indexOf("#partner_kalendar") > -1) {
		showEmployeeKalendar('agendaDay');
	}
	if (window.location.href.indexOf("#ikalendar") > -1 && window.location.href.indexOf("/5") > -1) {
	}
	//are we in user mode
	if (window.location.href.indexOf("#userkalendar") > -1) {
		var permissions = "readwrite";
		var user_id = $("#calendar_user_id").val();
		if (login_user_id!=user_id) {
		//get calendar permissions
			permissions = current_employee_calendars.findWhere({id: user_id}).get("permissions");
		}
		//get the employee calendar
		var thecalendar = customer_calendars.findWhere({sort_order: "5"});
		var calendar_id = -1;
		if (typeof thecalendar != "undefined") {
			calendar_id = thecalendar.get("calendar_id");
		}
		current_calendar_id = calendar_id;
		//get the user name
		var user = new User({user_id: user_id})
		user.fetch({
			success: function (user) {
				var all_user_events = new UserCalendar({user_id: user_id});
				all_user_events.fetch({
					success: function (data) {
						//then re-assign to calendar
						var empty_model = new Backbone.Model;				
						empty_model.set("calendar_name", user.get("user_name"));
						empty_model.set("user_id", user_id);
						empty_model.set("permissions", permissions);
						$("#content").html("");
						//do i have write permissions?
						occurencesView = new user_occurences_view({el: $("#content"), collection: all_user_events, model: empty_model}).render();
					}
				});
			}
		});
	}
}



function renderEventDialog(current_case_id, modal_title, occurence) {
	var dialog_width = "610px";
	//alert(case_id);
	if (current_case_id != "" && current_case_id != "undefined" && current_case_id != "-1") { 
		dialog_width = "1050px";
	}
	if (current_case_id == "" || current_case_id == "undefined") { 
		current_case_id = $("#case_id").val();
	}
	//alert(current_case_id);
	var dialog_id;
	var blnMinimized;
	$('<div class="dialog_content_holder event_' + occurence.id + '" style="color:#FFFFFF; font-family:\'Open Sans\', sans-serif; font-size:.85em; opacity:1"></div>')
	  .dialog({
		"title" : "",
		"width" : dialog_width,
		"position": ['center',100],
		"buttons" : [{
			id:"delete_event_button",
			text: "Delete",
			click: function() { 
				deleteEvent(event);
				 //alert('deleted');
				 $('.ui-dialog-titlebar').html("");
				 $('#dialog_title_section_content').css("margin-top", "0px");
				$(this).dialog("close");
			}
		}, 
			{
			id:"save_event_button",
			text: "Save",
			click: function() {
				saveEventModal(event);
				//alert("saved");
				$('.ui-dialog-titlebar').html("");
				$('#dialog_title_section_content').css("margin-top", "0px");
				$(this).dialog("close");
			}
		}],
			open: function(event, ui){
				var self = this;
				this.parentElement.className = this.parentElement.className + " " + this.id;
				//alert($('#dialog_title_section_content').html());
				this.parentElement.className = this.parentElement.className + " event_" + occurence.id;
				
				dialog_id = this.id;
				
				if ($('#dialog_title_section_content').length==0) {
					$('.ui-dialog-titlebar').append(modal_title);
					$('#dialog_title_section_content').css('margin-top', '-30px');
				} else {
					$('.event_' + occurence.id + ' #dialog_title_section_content').remove();
					//alert($('.ui-dialog-titlebar').html());
					$('.event_' + occurence.id + ' .ui-dialog-titlebar').append(modal_title);
					setTimeout( function() {
						$('#dialog_title_section_content').css('margin-top', '-30px');
					}, 100);
				}
				
				$('.ui-dialog-titlebar').css("margin-top", "0px");
				//$('.ui-dialog-titlebar').append(modal_title);
				$('#delete_event_button').css("background", "red");
				$('#delete_event_button').css("color", "white");
				$('#delete_event_button').hide();
				$('#save_event_button').on('click', function(event) {
					 //alert('closed');
					 $('.ui-dialog-titlebar').html("");
					 $('#dialog_title_section_content').html("");
					 $('#dialog_title_section_content').css("margin-top", "0px");
				});
				$('#delete_event_button').on('click', function(event) {
					 //alert('closed');
					 $('.ui-dialog-titlebar').html("");
					 $('#dialog_title_section_content').css("margin-top", "0px");
				});
				$('.ui-icon-close').on('click', function(event) {
					 //alert('closed');
					 $('#dialog_title_section_content').html("");
					 $('#dialog_title_section_content').css("margin-top", "0px");
				});
				
				setTimeout(function() {
					occurence.set("holder", self.id);
					$("#" + self.id).html(new event_view({model: occurence}).render().el);
					//console.log(new event_view({model: occurence}).render().el);
				}, 100);
			}
	   })
	  .dialogExtend({
		"closable" : true,
		"maximizable" : false,
		"minimizable" : true,
		"collapsable" : false,
		"dblclick" : "collapse",
		"titlebar" : "transparent",
		"minimizeLocation" : "left",
		"icons" : {
		  "close" : "ui-icon-close",
		  "maximize" : "ui-icon-circle-plus",
		  "minimize" : "ui-icon-circle-minus",
		  "collapse" : "ui-icon-triangle-1-s",
		  "restore" : "ui-icon-bullet"
		},
		"load" : function(evt, dlg){ $('#event_dateandtimeInput').css("display", ""); },
		"beforeCollapse" : function(evt, dlg){  },
		"beforeMaximize" : function(evt, dlg){  },
		"beforeMinimize" : function(evt, dlg){  },
		"beforeRestore" : function(evt, dlg){  },
		"collapse" : function(evt, dlg){  },
		"maximize" : function(evt, dlg){  },
		"minimize" : function(evt, dlg){ 
			$('.event_' + occurence.id + ' #dialog_title_section_content').css('margin-top', '-10px');
			$(".event_" + occurence.id + " .ui-dialog-titlebar").css("font-size", "0.65em"); 
			$(".event_" + occurence.id + " .delete_event_dialog_link").hide(); 
			blnMinimized = true;
		},
		"restore" : function(evt, dlg){ 
			$(".event_" + occurence.id + " .ui-dialog-titlebar").css("font-size", "1em"); 
			$(".event_" + occurence.id + " .delete_event_dialog_link").show();
		}
	  });
	  $(".dialog_content").css("background", "url('img/glass_edit_header_new_solid.png')");
	  $(".ui-dialog.event_" + occurence.id).css("background", "url('img/glass_edit_header_new_solid.png')");
	  $(".event_" + occurence.id + " .ui-dialog-buttonpane").css("background", "url('img/glass_edit_header_new_solid.png')");
	  $(".event_" + occurence.id + " .ui-dialog-titlebar").css("background", "url('img/glass_edit_header_new_solid.png')");
	  $(".event_" + occurence.id + " .ui-dialog-titlebar").css("color", "#FFFFFF");
	  $(".event_" + occurence.id + " .ui-dialog-titlebar").css("font-size", "1.2em");
	  $(".event_" + occurence.id + " .ui-dialog-titlebar").css("font-weight", "thin");
	  $(".event_" + occurence.id + " .ui-icon-close").css("top", "0px");
	  $(".event_" + occurence.id + " .ui-icon-close").css("left", "0px");
	  $(".event_" + occurence.id + " .dialog_content").css("color", "white");
}

function getPopupReminders(){
	var url = "api/popups";
	// popupPanel();
	
	$.ajax({
		url: url,
		type: "GET",
		dataType: "json",
		success: function(data){
			// console.log(data);
			
			var reminders = data.reminders;
			var count = data.reminders.length;
			if(count > 0){
				
				var arrReminderBuffers = '';
				var panel_id = 1;

				for(var i = 0; i < count; i++) {
					if(reminders[i].reminderbuffer_id == "0"){
						continue;
					}
					if($("#reminder_panel_" + panel_id + " #reminderbuffer_id_" + panel_id).val() == reminders[i].reminderbuffer_id){
						panel_id++;
						continue;
					}
					arrReminderBuffers += reminders[i].reminderbuffer_id + ",";
					var popup_model = new Backbone.Model();

					popupPanel(panel_id);					
					popup_model.set("holder", "popup_content_" + panel_id);
					popup_model.set("reminder", data.reminders[i]);
					popup_model.set("panel_id", panel_id)
					$("#popup_content_" + panel_id).html(new popup_view({model: popup_model}).render().el);
					
					if ((panel_id%3)==0) {
						//increment rows
						top_row_count++;
					}
					// $("#reminder_panel_" + panel_id).css("top", ((top_row_count * 250) + 80) + "px");
					// $("#reminder_panel_" + panel_id).css("margin-right", "10px");
					
					//$("#reminder_panel_" + panel_id).css("top", "80px");
					
					
					// if($(".jsPanel").length > 1){
					// 	panel_id = $(".jsPanel").length;
					// }
					panel_id++;
				}
				arrReminderBuffers = arrReminderBuffers.substring(0, (arrReminderBuffers.length-1));
			}

			//cse_remindersent table gets filled
			if(typeof arrReminderBuffers != 'undefined' && arrReminderBuffers != ""){
				var url_sent = "api/popupssent";
				var formValues = "reminderbuffer_ids=" + arrReminderBuffers;
				$.ajax({
					url: url_sent,
					type: "POST",
					dataType: "json",
					data: formValues,
					success:function(data) {
						var temp = 0;		
					}
				});	
			}
			//all the reminders that are shown are marked sent
			//i found some, display them in welcome holder div in v8.php
		}
	});
	setTimeout(function() {
		//reset globals
		top_row_count = 0;
		row_count = 0;
		panel_count = 0;
		column_count = 0;		
		getPopupReminders();
	}, 60000);
}
var top_row_count = 0;
var row_count = 0;
var panel_count = 0;
var column_count = 0;
function popupPanel(panel_id){
    var title = "Reminder Popup";
	var offset_left = 0;
	var offset_top = 0;

	switch (column_count) {
		case 0:
			offset_left = 10;
			break;
		case 1:
			offset_left = 400;
			break;
		case 2:
			offset_left = 800;
			break;
		default:
			break;
	}

	if(top_row_count > 0) {
		// offset_left = (column_count * 400); //0
		offset_top = ((top_row_count * 250) + 80);
	} else {
		// offset_left = 10;
		offset_top = 80;
	}
	
    $.jsPanel({
        id:			"reminder_panel_" + panel_id,
        title:    	title,
		offset: 	{ top: offset_top, left: offset_left },
        size:		{ width: "380", height: "auto" },        
        selector: 	"#reminder_panel_" + panel_id,
        position: 	"top right",
        controls: {
            maximize: "disable"
        },
        content: 	"<div id='popup_content_" + panel_id + "' style='float:left; border:1px solid black; overflow-y:auto; height:auto; width:380px; padding-right:2px; padding-top:2px; font-size:1.2em; vertical-align:bottom; background-color: black;'></div>",
        theme:    	"primary"
    });	
    //move it over slightly if there is already one open
    //$(".jsPanel").css("margin-right", "-100px");
	if($(".jsPanel").length == 1){
		column_count = 1;
	}

    if ($(".jsPanel").length > 1) {
		var currleft = $("#reminder_panel_" + panel_id).css("left");
		var currtop = $("#reminder_panel_" + panel_id).css("top");
		var panel_count = $(".jsPanel").length;		
		if ((panel_count%3)==0) {
			//increment rows
			row_count++;
			//reset columns
			column_count = 0;
		} else {
			column_count++;
		}
        //$("#reminder_panel_" + panel_id).css("left", (-400 * column_count) + Number(currleft.replace("px", "")) + "px");
        //$("#reminder_panel_" + panel_id).css("top", (row_count * 350) + "px");
    }
	$("#reminder_panel_" + panel_id).css("margin-right", "10px");

//var newleft = ($(".jsPanel").length / 100) - 400;
/*
if ($(".jsPanel").length > 2) {
	if ($(".jsPanel").length > 3) {
		newleft = ($(".jsPanel").length / 100) - 400;
	} else {
		newleft = ($(".jsPanel").length / 100);
	}
}
var newtop = $(".jsPanel").length / 100;
if ($(".jsPanel").length > 2) {
	if ($(".jsPanel").length > 3) {
		newtop = ($(".jsPanel").length / 100) + 320;
	} else {
		newtop = ($(".jsPanel").length / 100) + 320;
	}
}
*/
			        
}