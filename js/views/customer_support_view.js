var postevent_timeoutid = false;
window.customer_support_view = Backbone.View.extend({
	render: function () {
		var self = this;
		
		if (this.model.get("case_id") > 0) {
			var kase = kases.findWhere({case_id: this.model.get("case_id")});
			//might not be a valid id...
			if (typeof kase == "undefined") {
				var kase = new Kase({case_id: self.model.get("case_id")});
				kase.fetch({
					success: function (kase) {
						kases.add(kase);
					}
				});
			}
		}
		var event_title = this.model.get("event_title");
		var event_dateandtime = this.model.get("event_dateandtime");
		if (event_dateandtime=="") {
			this.model.set("event_dateandtime", moment().format("MM/DD/YYYY h:mm a"));
		}
		event_title = event_title.replace(" - 00/00/0000", "");
		this.model.set("event_title", event_title);
		
		var event_description = this.model.get("event_description");
		event_description = event_description.replaceTout("\\n", "<br>");
		this.model.set("event_description", event_description);
		
		if (this.model.get("end_date")=="" || this.model.get("end_date")=="0000-00-00 00:00:00") {
			this.model.set("end_date", "");
		} else {
			this.model.set("end_date", moment(this.model.get("end_date")).format("MM/DD/YY h:mm a"));
		}
		if (this.model.get("callback_date")=="" || this.model.get("callback_date")=="0000-00-00 00:00:00") {
			this.model.set("callback_date", "");
		} else {
			this.model.set("callback_date", moment(this.model.get("callback_date")).format("MM/DD/YY h:mm a"));
		}
		if (this.model.get("event_dateandtime")!="") {
			this.model.set("event_dateandtime", moment(this.model.get("event_dateandtime")).format('MM/DD/YYYY hh:mm a'));
		}
		
		if (this.model.get("event_description")=="" && this.model.get("event_name")!="") {
			this.model.set("event_description", this.model.get("event_name"));
		}
		
		var off_calendar_checked = "";
		if (this.model.get("off_calendar")=="Y") {
			off_calendar_checked = "checked";
		}
		this.model.set("off_calendar_checked", off_calendar_checked);
		
		//reminder defaults, avoid nulls
		if (this.model.get("reminder_id1")=="-1") {
			this.model.set("reminder_type1", "");
			this.model.set("reminder_interval1", "");
			this.model.set("reminder_span1", "");
			this.model.set("reminder_datetime1", "");
		} else {
			this.model.set("reminder_datetime1", moment(this.model.get("reminder_datetime1")).format("MM/DD/YYYY hh:mmA"));
		}
		if (this.model.get("reminder_id2")=="-1") {
			this.model.set("reminder_type2", "");
			this.model.set("reminder_interval2", "");
			this.model.set("reminder_span2", "");
			this.model.set("reminder_datetime2", "");
		} else {
			this.model.set("reminder_datetime2", moment(this.model.get("reminder_datetime2")).format("MM/DD/YYYY hh:mmA"));
		}
		
		try {
			$(self.el).html(self.template({occurrence: self.model.toJSON()}));
		}
		catch(err) {
			var view = "customer_support_view";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		if (document.location.hash=="#intake") {
			setTimeout(function() {
				console.log("do timeouts");
				self.doTimeouts();
			}, 1567);
		}
		return this;
	},
	triggerEdit:function() {
		$(".event .edit" ).trigger( "click" );
		
	},
	events:{
		"click .event .edit": 					"toggleDialogEdit",
		"click .event .save": 					"addEvent",
		"click #save_billing_modal": 			"addBill",
		"click .event .reset": 					"resetDialogForm",
		"dblclick .event .gridster_border": 	"editDialogField",
		"click .event .save_field":				"saveDialogViewField",
		"click #delete_event_button":			"deleteEvent",
		"click #show_reminder":					"showReminder",
		"click #show_second_reminder":			"showSecondReminder",
		"click #google_map":					"showMap",
		"click #customer_support_view_all_done":			"doTimeouts",
		"click .event_partie":					"selectPartie",
		"keyup #number_of_days":				"schedulePosting",
		"keyup #event_dateandtimeInput":		"clearErrorWarning",
		"change #calendar_drop_down":			"changeCalendar",
		"change #event_priorityInput":			"bgChange",
		"click .ui-dialog-titlebar":			"lookupBlur",
		"click .xdsoft_time":					"addDays",
		"change .reminder_field":				"newReminderDate",
		"keyup .reminder_field":				"newReminderDate",
		"click #view_event": 					"displayMain",
		"click #view_reminders":				"displayReminders",
		"click #view_billable":  				"displayBillable",
		"click #cancel_billable":				"cancelBillable",
		"click #review_case":					"openCase",
		"click .bing_address":					"selectBingAddress"
    },
	addBill: function(event) {
		var self = this;
		var element = event.currentTarget;
		
		if (blnShowBilling) {
			var billing_date = $("#billing_dateInput").val();
			var duration = $("#durationInput").val();
			var status = $("#billing_form #statusInput").val();
			var billing_rate = $("#billing_rateInput").val();
			var activity_code = $("#activity_codeInput").val();
			var timekeeper = $("#timekeeperInput").val();
			var description = $("#billing_form #descriptionInput").val();
			var table_id = $("#billing_form #table_id").val();
			var action_id = $("#action_id").val();
			var action_type = $("#action_type").val();
				
			var formValues = "case_id=" + current_case_id + "&billing_date=" + billing_date + "&table_id=" + table_id + "&status=" + status;
				formValues += "&duration=" + duration + "&billing_rate=" + billing_rate + "&activity_code=" + activity_code + "&timekeeper=" + timekeeper + "&description=" + description + "&action_id=" + action_id + "&action_type=" + action_type;
			
			var modal_bg = $(".modal-dialog").css('background-image');
			modal_bg = modal_bg.replace('"', "'");
			modal_bg = modal_bg.replace('"', "'");
			//console.log(modal_bg);
			//alert(modal_bg);
			//return;
			$.ajax({
			  method: "POST",
			  url: "api/billing/add",
			  dataType:"json",
			  data: formValues,
			  success:function (data) {
				  if(data.error) {  // If there is an error, show the error tasks
					  alert("error");
				  } else {
					  $("#myModalBody").css('background', "#0C3");
					  //rgb(255, 255, 255)
					  setTimeout(function() {
						  $("#myModalBody").css('background-color', '');
						  $("#myModalBody").css('background-image', modal_bg);
						  setTimeout(function() {
							  //self.displayMain();
						  }, 700);
					  }, 2000);
				  }
			  }
			});
		}
	},
	newReminderDate: function(event) {
		var element = event.currentTarget;
		var element_id = element.id;
		
		//extract the reminder number
		var reminder_number = element_id.substr(element_id.length - 1);
		
		var reminder_span = $("#reminder_span" + reminder_number).val();
		var reminder_interval = $("#reminder_interval" + reminder_number).val();
		if (reminder_interval=="") {
			//no change
			return;
		}
		if (reminder_span=="minutes" || reminder_span=="hours") {
			
			var current_date = $("#event_dateandtimeInput").val();
			
			var formValues = "span=" + reminder_span + "&interval=" + reminder_interval + "&date=" + current_date;
			
			$.ajax({
			  method: "POST",
			  url: "api/reminders/newtime",
			  dataType:"text",
			  data: formValues,
			  success:function (data) {
				  $("#reminder_datetime" + reminder_number).html(data);
			  }
			});
		}
		if (reminder_span=="weeks") {
			reminder_span = "days";
			reminder_interval = reminder_interval * 7;
		}
		if (reminder_span=="days") {
			var current_date = $("#event_dateandtimeInput").val();
			var arrDate = current_date.split(" ");
			current_date = arrDate[0];
			
			var formValues = "days=-" + reminder_interval + "&date=" + current_date;
			
			$.ajax({
			  method: "POST",
			  url: "api/calculator_post.php",
			  dataType:"json",
			  data: formValues,
			  success:function (data) {
				  if(data.error) {  // If there is an error, show the error tasks
					  alert("error");
				  } else {
					  $("#reminder_datetime" + reminder_number).html(data[0].calculated_date + " " + arrDate[1]);
				  }
			  }
			});
		}
	},
	changeCalendar: function (event) {
		if (this.model.id == "-1") {
			var drop_down_val = $("#calendar_drop_down").val();
			var new_modal_label = "New Event - " + drop_down_val + "&nbsp;&nbsp;"
			
			var drop_down_val_id = $(".calendar_drop_down_option." + drop_down_val).attr("id");
			//alert(drop_down_val_id);
			new_modal_label = new_modal_label.replace("_", " ");
			new_modal_label = new_modal_label.replace("_", " ");
			$("#myModalLabel").html(new_modal_label);
			
			$("#calendar_id").val(drop_down_val_id);
			
			var event_type = drop_down_val.toLowerCase();
			event_type = event_type.replace(" ", "_");
			event_type = event_type.replace(" ", "_");
			//alert(event_type);
			if (event_type == "intake") {
				$("#event_kind").val(drop_down_val);
				var new_type = '<select name="event_typeInput" id="event_typeInput" class="event input_class" style="height:25px; width:180px; margin-top:0px; margin-left:0px; border:0px solid red"><option value="intake" selected>Intake</option></select>';
				$("#event_type_drop").html(new_type);
			}
			
		}
	},
	bgChange: function (event) {
		if ($("#event_priorityInput").val() == "high") {
			 $(".ui-dialog-titlebar").css("background", "url('img/glass_urgent.png')");
		}
		if ($("#event_priorityInput").val() == "normal") {
			 $(".ui-dialog-titlebar").css("background", "url('img/glass_edit_header_new_solid.png')");
		}
		if ($("#event_priorityInput").val() == "low") {
			 $(".ui-dialog-titlebar").css("background", "url('img/glass_not_urgent.png')");
		}
	},
	lookupBlur: function (event) {
		$(".token-input-list-event").blur();
	},
	
	addDays: function (event) {
		console.log("here");
		return;
	},
	selectPartie: function (event) {
		var element = event.currentTarget;
		var theid = element.id.split("_")[2];
		
		var thename = $("#event_partie_name_" + theid).html();
		thename = thename.split("<br>")[0].trim();
		var theaddress = $("#event_partie_address_" + theid).html();
		theaddress = theaddress.replace("<br>", "").trim();
		/*
		var current_description = $("#event_descriptionInput").val();
		var new_description = "";
		if (current_description!="") {
			new_description = current_description + "<br>";
		}
		
		$(".event #event_descriptionInput").cleditor({
			width:540,
			height: 130,
			controls:     // controls to add to the toolbar
					  "bold italic underline | font size " +
					  "style | color highlight"
		});
		$(".event #event_descriptionInput").val(new_description + thename + " - " + theaddress).blur();
		*/
	},
	schedulePosting:function() {
		var self = this;
		clearTimeout(postevent_timeoutid);
		postevent_timeoutid = setTimeout( function() {
			self.posting();
		}, 1000);
	},
	posting: function() {
		setTimeout( function() {
			var pre_save_date = $(".original_date").val();
			var current_date = $("#event_dateandtimeInput").val();
			var days_val = $("#number_of_days").val();
			if (days_val=="") {
				//no change
				return;
			}
			var arrDate = current_date.split(" ");
			arrDate.splice(0, 1);
			var formValues = "days=" + days_val + "&date=" + current_date;
			
			$.ajax({
			  method: "POST",
			  url: "api/calculator_post.php",
			  dataType:"json",
			  data: formValues,
			  success:function (data) {
				  if(data.error) {  // If there is an error, show the error tasks
					  alert("error");
				  } else {
					  //alert(data[0].calculated_date + " - " + data[0].display_date + " - " + data[0].days);
					  //$("#reschedule_section").hide()
					  	  if (data[0].days == "") {
						  	  $("#event_dateandtimeInput").val(pre_save_date);
						  	  $("#calculated_dateSpan").html(moment(pre_save_date).format("ddd, MMM Do YYYY"));
						  } else {
							  $("#event_dateandtimeInput").val(data[0].calculated_date + " " + arrDate.join(" "));
							  $("#calculated_dateSpan").html(' ' + data[0].display_date);
						  }
						  $("#number_of_days").val("");
						  $("#event_durationInput").focus();
				  }
			  }
			});
		}, 900);
	},	
	addEventKase: function(item) {
		var self = this;
		var blnIsCreator = false;
		if (self.model.id < 0) {
			blnIsCreator = true;
		}
		var kase = kases.findWhere({case_id: item.id});
		if (typeof kase == "undefined") {
			var kase = new Kase({case_id: item.id});
			kase.fetch({
				success: function (kase) {
					kases.add(kase);
					self.addEventKase(item);
				}
			});
			return;
		}
		var current_id = self.model.get('id');
		var event_id = $("#case_idInput").attr("class").split(" ")[1].split("_")[1];
		if (current_id != event_id) {
			current_id = event_id;
		}
		
		$("#view_billable").fadeIn();
		
		//look up the parties for the case, and then list them in parties_list
		if (blnIsCreator) {
			if ($("#parties_list").length > 0) {
				$(".event #case_id").val(item.id);
				if ($(".activity_bill #case_id").length > 0) {
					$(".activity_bill #case_id").val(item.id);
				}
				
				var parties = new Parties([], { case_id: item.id, panel_title: "Parties" });
				parties.fetch({
					success: function(parties) {
						//add the customer to the list
						parties.add({
							company_name: customer_name,
							type: "in_house",
							address: customer_address,
							partie_id:	0
						}, {at: 0});
						kase.set("event_kind", self.model.get("event_kind"));
						var thetitle = "(Select from list to set Event Location)";
						if (self.model.get("event_kind")=="phone_call") {
							thetitle = "(Select from list to set Phone Call basic information)";
						}
						kase.set("list_title", thetitle);
						
						//capture the venue if it's a lien request
						var venue_id = "";
						if (self.model.get("event_title")=="Lien Appearance") {
							var venue = parties.findWhere({type:"venue"});
							if (typeof venue == "object") {
								venue_id = venue.get("corporation_id");
							}
						}
						kase.set("holder", "parties_list");
						$("#parties_list").html(new partie_listing_event({collection: parties, model: kase}).render().el);
						//see if the dialog for this specific is already open
						//if more than 1, then have to close and open the one that's already opened
						if ($(".ui-dialog").length > 0) {
							//if ($(".ui-dialog").width() != "1041.84") {
							var current_width = $(".ui-dialog.event_" + current_id).css("width").replace("px", "");
							if (current_width != 1050) {
								$(".ui-dialog.event_" + current_id).animate({width:1050, marginLeft:"-250px"}, 1500, 'easeInSine', 
							
							//$('.modal-dialog').animate({width:1000, marginLeft:"-500px"}, 1100, 'easeInSine', 
								function() {
									//run this after animation
									$(".event_" + current_id + " #parties_list").fadeIn(function() {
										$(".event_" + current_id + " #event_partie_C" + venue_id).trigger( "click" );
									});
								});
							} else {
								
								$(".event_" + current_id + " #parties_list").fadeIn(function() {
									$(".event_" + current_id + " #event_partie_C" + venue_id).trigger( "click" );
								});
							}
						} else {
							
							$('.modal-dialog').animate({width:1000, marginLeft:"-500px"}, 1100, 'easeInSine', 
								function() {
									//run this after animation
									$('#parties_list').fadeIn(function() {
										$("#event_partie_C" + venue_id).trigger( "click" );
									});
								}
							);
						}
					}
				});
				// Set the attorney, supervisor_att, and worker when case is loaded
				var kase_json = kase.toJSON();
				var personnel = [];
				if(kase_json.attorney != ""){
					personnel.push(["attorney",kase_json.attorney]);
				}
				if(kase_json.supervising_attorney != ""){
					personnel.push(["supervising_attorney", kase_json.supervising_attorney]);
				}
				if(kase_json.worker != ""){
					personnel.push(["worker", kase_json.worker]);
				}
				if(personnel.length > 0) {
					$("#store_users").val(JSON.stringify(personnel));	
				}
			}
		}
	},
	doTimeouts: function(event) {
		var self = this;
		
		//employee calendar?
		if (this.model.toJSON().event_kind=="employee") {
			$("#calendar_drop_down").val("Employee_Calendar");
			//capture the calendar_id
			$("#calendar_drop_down").trigger("change");
		}
		
		if (!blnBingSearch) {
			initializeGoogleAutocomplete('event');
		} else {
			console.log("do keyup");
			$(".event #full_addressInput").on("keyup", function() {
					console.log("do lookup");
					lookupBingMaps ("event", "bing_results");
			});
		}
		
		var blnIsCreator = false;
		if (self.model.id < 0) {
			blnIsCreator = true;
		}
		if (!blnIsCreator && self.model.id > 0) {
			if (self.model.get("event_from")==login_username) {
				blnIsCreator = true;
			}
		}
		//however, if it's my personal calendar
		if (self.model.get("user_id")!="") {
			if (self.model.get("user_id")==login_user_id) {
				blnIsCreator = true;
			}
		}
		//HARD CODED
		blnIsCreator = true;
		
		if (!blnIsCreator) {
			$("#uneditable_row").show();
			$("#uneditable").html('<i style="font-size:1em;color:#FF7B04;" class="glyphicon glyphicon-warning-sign"></i><span style="font-size:0.8em;">This event cannot be edited because you are not the creator (' + self.model.get("event_from") + ').</span>');
		}
		var event_dateandtime = moment(this.model.get("event_dateandtime")).format("MM/DD/YYYY hh:mma");
		//event_dateandtime = new Date(event_dateandtime);
		$('.event #event_dateandtimeInput').datetimepicker({ 
			onGenerate:function( ct ){
				jQuery(this).find('.xdsoft_date.xdsoft_weekend')
				  .addClass('xdsoft_disabled');
		    },
		    weekends:['01.01.2014','02.01.2014','03.01.2014','04.01.2014','05.01.2014','06.01.2014'],
			validateOnBlur:false, 
			minDate: 0, 
			value: event_dateandtime,
			allowTimes:workingWeekTimes,
			step:30,
			  onChangeDateTime:function(dp,$input){
				  self.clearErrorWarning();
				  self.setupCalculationOptions();
			  }
			});
		//jQuery('#event_dateandtimeInput').datetimepicker({value:'08/21/2016 04:26pm'});
		
		$('.event #end_dateInput').datetimepicker({ validateOnBlur:false, minDate: 0, allowTimes:workingWeekTimes,step:30});
		$('.event #event_closedateInput').datetimepicker({ validateOnBlur:false, minDate: 0, allowTimes:workingWeekTimes,step:30});
		$('.event #callback_dateInput').datetimepicker({ validateOnBlur:false, minDate: 0, allowTimes:workingWeekTimes,step:30});
		
		var theme = {
			theme: "event", 
			onAdd: function(item) {
				if (login_user_id=="1568") {
					self.addEventKase(item);
					return;
				}
				
				var current_id = self.model.get('id');
				var event_id = $("#" + this[0].id).attr("class").split(" ")[1].split("_")[1];
				if (current_id != event_id) {
					current_id = event_id;
				}
				
				$("#view_billable").fadeIn();
				
				//look up the parties for the case, and then list them in parties_list
				if (blnIsCreator) {
					if ($("#parties_list").length > 0) {
						$(".event #case_id").val(item.id);
						if ($(".activity_bill #case_id").length > 0) {
							$(".activity_bill #case_id").val(item.id);
						}
						var kase = kases.findWhere({case_id: item.id});
						var parties = new Parties([], { case_id: item.id, panel_title: "Parties" });
						parties.fetch({
							success: function(parties) {
								//add the customer to the list
								parties.add({
									company_name: customer_name,
									type: "in_house",
									address: customer_address,
									partie_id:	0
								}, {at: 0});
								kase.set("event_kind", self.model.get("event_kind"));
								var thetitle = "(Select from list to set Event Location)";
								if (self.model.get("event_kind")=="phone_call") {
									thetitle = "(Select from list to set Phone Call basic information)";
								}
								kase.set("list_title", thetitle);
								
								//capture the venue if it's a lien request
								var venue_id = "";
								if (self.model.get("event_title")=="Lien Appearance") {
									var venue = parties.findWhere({type:"venue"});
									if (typeof venue == "object") {
										venue_id = venue.get("corporation_id");
									}
								}
								kase.set("holder", "parties_list");
								$("#parties_list").html(new partie_listing_event({collection: parties, model: kase}).render().el);
								//see if the dialog for this specific is already open
								//if more than 1, then have to close and open the one that's already opened
								if ($(".ui-dialog").length > 0) {
									//if ($(".ui-dialog").width() != "1041.84") {
									var current_width = $(".ui-dialog.event_" + current_id).css("width").replace("px", "");
									if (current_width != 1050) {
										$(".ui-dialog.event_" + current_id).animate({width:1050, marginLeft:"-250px"}, 1500, 'easeInSine', 
									
									//$('.modal-dialog').animate({width:1000, marginLeft:"-500px"}, 1100, 'easeInSine', 
										function() {
											//run this after animation
											$(".event_" + current_id + " #parties_list").fadeIn(function() {
												$(".event_" + current_id + " #event_partie_C" + venue_id).trigger( "click" );
											});
										});
									} else {
										
										$(".event_" + current_id + " #parties_list").fadeIn(function() {
											$(".event_" + current_id + " #event_partie_C" + venue_id).trigger( "click" );
										});
									}
								} else {
									
									$('.modal-dialog').animate({width:1000, marginLeft:"-500px"}, 1100, 'easeInSine', 
										function() {
											//run this after animation
											$('#parties_list').fadeIn(function() {
												$("#event_partie_C" + venue_id).trigger( "click" );
											});
										}
									);
								}
							}
						});
						// Set the attorney, supervisor_att, and worker when case is loaded
						var kase_json = kase.toJSON();
						var personnel = [];
						if(kase_json.attorney != ""){
							personnel.push(["attorney",kase_json.attorney]);
						}
						if(kase_json.supervising_attorney != ""){
							personnel.push(["supervising_attorney", kase_json.supervising_attorney]);
						}
						if(kase_json.worker != ""){
							personnel.push(["worker", kase_json.worker]);
						}
						if(personnel.length > 0) {
							$("#store_users").val(JSON.stringify(personnel));	
						}
					}
				}
			}
		};
		$("#case_idInput").tokenInput("api/kases/tokeninput", theme);					
		var theme_3 = {
			theme: "event",
			onAdd: function(item) {
				//is it blocked
				var blocked_ids = self.model.get("blocked_ids");
				if (typeof blocked_ids != "undefined") {
					var arrBlockedIDS = blocked_ids.split(",");
					if (arrBlockedIDS.indexOf(item.id) > -1) {
						alert("This employee is blocked out for this day");
						$(".event #assigneeInput").tokenInput("remove", {id: item.id});
						return;
					}
				}
				//offer a task.					
				var topper = $("#modal_save_holder").html();
				if (topper.indexOf("apply_tasks_holder") < 0) {
					$("#modal_save_holder").prepend('<span id="apply_tasks_holder" class="white_text"><input type="checkbox" id="apply_tasks" onchange="showEventTaskDateBox()">&nbsp;Save as Task</span>&nbsp;&nbsp;');
				}
			}
		};
		$(".event #assigneeInput").tokenInput("api/user", theme_3);

		$(".event #event_descriptionInput").cleditor({
			width:545,
			height: 130,
			controls:     // controls to add to the toolbar
					  "bold italic underline | font size " +
					  "style | color highlight"
		});
		
		//we might have a default value
		if (self.model.get("case_id") > 0) {
			var casing_file = $("#case_fileInput").val();
			
			var kase = kases.findWhere({case_id: self.model.get("case_id")});
			//might not be a valid id...
			if (typeof kase != "undefined") {
				//add the kase
				$("#case_idInput").tokenInput("add", {
					id: self.model.get("case_id"), 
					name: kase.name(),
					tokenLimit:1
				});
				
				$("#event_screen #open_case_holder").fadeIn();
						
				var kase_json = kase.toJSON();
				var personnel = [];
				if(kase_json.attorney != ""){
					personnel.push(["attorney",kase_json.attorney]);
				}
				if(kase_json.supervising_attorney != ""){
					personnel.push(["supervising_attorney", kase_json.supervising_attorney]);
				}
				if(kase_json.worker != ""){
					personnel.push(["worker", kase_json.worker]);
				}
				if(personnel.length > 0) {
					$("#store_users").val(JSON.stringify(personnel));	
				}				
				
				$("#case_id_holder .token-input-list-event").hide();
				$(".case_input .token-input-list-event").hide();
				$("#case_id_holder #case_idSpan").html(kase.name());
			} else {
				var kase = new Kase({case_id: self.model.get("case_id")});
				kase.fetch({
					success: function (kase) {
						//add the kase to kases for faster lookup later on
						kases.add(kase);
						$(".event #case_idInput").tokenInput("add", {
							id: self.model.get("case_id"), 
							name: kase.name(),
							tokenLimit:1
						});
						
						$("#event_screen #open_case_holder").fadeIn();
						$("#case_id_holder .token-input-list-event").hide();
						$(".case_input .token-input-list-event").hide();
						$("#case_id_holder #case_idSpan").html(kase.name());
						return;		
					}
				});
			}
		}
		if (self.model.get("case_id") > 0) {
			$(".event #case_id_row").show();
			if (self.model.get("event_kind")!="phone_call") {
				//$("#case_id_holder .token-input-list-event").hide();
				//$(".event #case_idSpan").html(kase.name());
			}
		} else {
			if (self.model.get("event_kind")!="phone_call") {
				//$("#case_id_holder .token-input-list-event").hide();
			}
		}
		
		if (self.model.get("event_kind")=="phone_call") {
			$('.event #token-input-assigneeInput').focus();			
		} else {
			$('.event #event_descriptionInput').cleditor()[0].focus();			
		}
		
		var assigned_users = new EventUsers([], {event_id: self.model.id, type: "to"});
		assigned_users.fetch({
			success: function (data) {
				_.each( data.toJSON(), function(event_user) {
					$("#assigneeInput").tokenInput("add", {id: event_user.user_id, name: event_user.user_name});		
				});
				
				//maybe not done in import
				if (typeof self.model.get("assignee")!="undefined") {
					if (data.length==0 && self.model.get("assignee")!="") {
						var arrAssignees = self.model.get("assignee").split(";");
						for(var i = 0; i < arrAssignees.length; i++) {
							var theworker = worker_searches.findWhere({"nickname": arrAssignees[i]});
							$("#assigneeInput").tokenInput("add", {id: theworker.id, name: theworker.get("user_name")});		
						}
					}
				}
			}
		});
		if (typeof this.model.get("reminder_count") != "undefined") {
			var reminder_count = this.model.get("reminder_count");
			if (reminder_count > 0) {
				setTimeout(function() {
					$("#show_reminder").trigger("click");
					$("#first_reminder_row").hide();
				}, 1000);
			}
		}
		/*
		if (this.model.get("reminder_id1")!="-1" && this.model.get("reminder_id2")=="-1") {
			//hide the first button
			$("#show_reminder").hide();
			$("#show_second_reminder").show();
		}
		if (this.model.get("reminder_id2")!="-1") {
			//hide both
			$("#show_reminder").hide();
			$("#show_second_reminder").hide();
		}
		*/
		if (this.model.get("reminder_id1")!="-1") {
			setTimeout(function() {
				$("#show_reminder").trigger("click");
				$("#first_reminder_row").hide();
			}, 1000);
		}
		//FOR RECURRING, NOT WORKING YET
		$('select').on('change', function() {
			//alert( $(this).val() ); // or $(this).val()
			if ($(this).val() == "daily") {
				//alert("daily");
				$("#repeat_row_by").hide();
				$("#repeat_row_days").hide();
				$("#summary_span").html("Daily");
			}
			if ($(this).val() == "weekly") {
				//alert("weekly");
				$("#repeat_row_by").hide();
				$("#repeat_row_days").show();
				$("#summary_span").html("Weekly");
			}
			if ($(this).val() == "weekdays") {
				//alert("weekdays");
				$("#repeat_row_by").hide();
				$("#repeat_row").hide();
				$("#repeat_row_days").hide();
				$("#summary_span").html("Monday - Friday");
			}
			if ($(this).val() == "weekday_odd") {
				$("#repeat_row_by").hide();
				$("#repeat_row").hide();
				$("#repeat_row_days").hide();
				$("#summary_span").html("Monday, Wednesday, and Friday");
			}
			if ($(this).val() == "weekday_even") {
				$("#repeat_row_by").hide();
				$("#repeat_row").hide();
				$("#repeat_row_days").hide();
				$("#summary_span").html("Tuesday and Thusrday");
			}
			if ($(this).val() == "monthly") {
				$("#repeat_row_by").show();
				$("#repeat_row_days").hide();
				$("#summary_span").html("Monthly");
				//$("#repeat_row_days").hide();
			}
			if ($(this).val() == "yearly") {
				$("#repeat_row_by").hide();
				$("#repeat_row").show();
				$("#summary_span").html("Annually");
			}
		});
		$('#recurrent_dateandtimeInput').datetimepicker({ validateOnBlur:false, minDate: 0, allowTimes:workingWeekTimes,step:30, closeOnTimeSelect:true});
		
	
		$('#recurrent_interval').on('change', function() {
			summarize();
		});
		$('#recurrent_dateandtimeInput').on('focusout', function() {
			$('#recurrent_dateandtimeInput').blur();
			$('#never').focus();
			summarize();
		});
		$('.end_radio').click(function() {
			summarize();
		});
	
		if ($("#recurrent_repeatInput").val() == "daily") {
			$("#repeat_row_days").hide();
			$("#repeat_row_days").hide();
			$("#summary_span").html("Daily");
		}
	
		if ($("#recurrent_repeatInput").val() == "weekly") {
			$("#repeat_row_by").hide();
			$("#repeat_row_days").show();
			$("#summary_span").html("Weekly");
		}
	
		if ($("#recurrent_repeatInput").val() == "weekdays") {
			$("#repeat_row_by").hide();
			$("#repeat_row").hide();
			$("#repeat_row_days").hide();
			$("#summary_span").html("Monday - Friday");	
		}
	
		if ($("#recurrent_repeatInput").val() == "weekday_odd") {
			$("#repeat_row_by").hide();
			$("#repeat_row").hide();
			$("#repeat_row_days").hide();
			$("#summary_span").html("Monday, Wednesday, and Friday");
		}
	
		if ($("#recurrent_repeatInput").val() == "weekday_even") {
			$("#repeat_row_by").hide();
			$("#repeat_row").hide();
			$("#repeat_row_days").hide();
			$("#summary_span").html("Tuesday and Thusrday");
		}
	
		if ($("#recurrent_repeatInput").val() == "monthly") {
			$("#repeat_row_by").show();
			$("#repeat_row_days").hide();
			$("#summary_span").html("Monthly");
		}
	
		if ($("#recurrent_repeatInput").val() == "yearly") {
			$("#repeat_row_by").hide();
			$("#repeat_row").show();
			$("#summary_span").html("Annually");
		}

		//map icon
		if (this.model.get("full_address") != "") {
			$(".event #full_addressInput").animate(
				{width:445}, 
				700, 
				'easeInSine', 
				function() {
					$('#google_map').css("opacity", 1);
				}
			);
		}
		
		if (customer_id == "1033") {
			//console.log("nick");
		}
		if (this.model.id == "-1") {
			$("div.token-input-dropdown-event").hide();
			
		}
		$('#event_dateandtimeInput').css("display", "");
		/*
		if (this.model.get("holder")=="dialog_content") {
			var modal_title = this.model.get("modal_title");
			renderEventDialog(current_case_id, modal_title);
		}
		*/
		if (blnBlockDays) {
			self.model.set("blocked_ids", "");
			var url = "api/blockeddate/" + moment(self.model.get("event_dateandtime")).format("YYYY-MM-DD");
			$.ajax({
				url:url,
				type:'GET',
				dataType:"json",
				success: function(data) {
					if (typeof data.length!= "undefined") {
						var arrLength = data.length;
						var arrBlocked = [];
						var arrBlockedID = [];
						for (var i = 0; i < arrLength; i++) {
							if (data[i].user_id==-1) {
								arrBlocked = [];
								//arrBlocked.push("<span style='background:orange; padding:2px'>This date is blocked for the whole company</span>");
								$(".token-input-list-event").hide();	
								$("#assigneeSpan").html("<span style='background:orange; padding:2px'>This date is blocked for the whole company</span>");
								break;
							} else {
								arrBlocked.push("<span style='background:orange; padding:2px'>This date is blocked for " + data[i].user_name + "</span>");
								arrBlockedID.push(data[i].user_id);
							}
						}
					} else {
						var i = 1;
						if (typeof data[i] != "undefined") {
							if (data[i].user_id==-1) {
								arrBlocked = [];
								//arrBlocked.push("<span style='background:orange; padding:2px'>This date is blocked for the whole company</span>");
								$(".token-input-list-event").hide();	
								$("#assigneeSpan").html("<span style='background:orange; padding:2px'>This date is blocked for the whole company</span>");
							} else {
								arrBlocked.push("<span style='background:orange; padding:2px'>This date is blocked for " + data[i].user_name + "</span>");
								arrBlockedID.push(data[i].user_id);
							}
						}
					}
					if (arrBlocked.length > 0) {
						var blocked_dates = arrBlocked.join("<br>");
						$("#blocked_dates_holder").html(blocked_dates);
						
						self.model.set("blocked_ids", arrBlockedID.join(","));
					}
				}
			});
		}
	},
	selectBingAddress: function(event) {
		//utilities.js
		selectBingAddress(event, "event", "bing_results");
	},
	openCase: function(event) {
		event.preventDefault();
		//this will only be available if there is a case id
		window.open("v8.php?n=#kase/" + this.model.get("case_id"));
	},
	setupCalculationOptions: function() {
		var days_val = $("#number_of_days").val();
		var date_val = $("#event_dateandtimeInput").val();
		var arrDate = date_val.split(" ");
		var time_date = arrDate[1] + " " + arrDate[2];
		var result_date_real = "";
		var formValues = "date=" + date_val + "&days=" + days_val;
		//arrDays.each( function(days) {
		//{ date: date_val, days: days },
		
		$.ajax({
		  method: "POST",
		  url: "api/calculator_post.php",
		  dataType:"json",
		  data: formValues,
		  success:function (data) {
			  if(data.error) {  // If there is an error, show the error tasks
				  alert("error");
			  } else {
				  //alert(data.result_date + " - " + data.start_date + " - " + data.days);
				  //$("#reschedule_section").hide();
				  var arrOptions = [];
				  var theoption = '<option value="' + date_val + '" selected="selected">Select number of days</option>';  
				  arrOptions.push(theoption);
				  _.each( data, function(calculated_date) {
						//create options, tack on time_date
						// + " " + time_date
						var theoption = "<option value='" + calculated_date.calculated_date + "'>" + calculated_date.days + " days - " + calculated_date.display_date + "</option>";  
						arrOptions.push(theoption);
				  });
				  
				  //update the drop down
				  $("#number_of_days").html(arrOptions.join(""));
			  }
		  }
		});
	},
	clearErrorWarning: function() {
		$("#event_dateandtimeInput").css("border", "0px");
	},
	showMap: function(event) {
		//get the address, and then send to googlemaps
		if ($("#full_addressInput").val()=="") {
			return;
		}
		var arrAddress = $("#full_addressInput").val().split(" - ");
		if (arrAddress.length == 2) {
			theaddress = arrAddress[1];
		} else {
			theaddress = $("#full_addressInput").val();
		}
		window.open("https://www.google.com/maps/place/" + encodeURIComponent(theaddress));
	},
	showReminder:function (event) {

		
		/*
		if (customer_id == 1033) {
			var attendee_ids = $("#assigneeInput").val();
			var event_id = $("#table_id").val();
			var event_date = $("#event_dateandtimeInput").val();
			var url = "../sms/case_worker_reminder.php?event_date=" + event_date + "&users=" + attendee_ids
			if(event_id != "-1"){
				url = "../sms/case_worker_reminder.php?event_date=" + event_date + "&users=" + attendee_ids + "&event_id=" + event_id;
			}
			document.getElementById("reminder_holder").src = url;
			return;
		}
		*/
		/*
		$("#first_reminder_row").fadeIn();
		$("#show_reminder").fadeOut(function() {
			$("#show_second_reminder").fadeIn();
		});
		//set defaults if id is empty
		var reminder_id1 = $("#reminder_id1").val();
		if (reminder_id1=="-1") {
			$("#reminder_type1").val("interoffice");
			$("#reminder_interval1").val("1");
			$("#reminder_span1").val("days");
		}
		*/
	},
	showSecondReminder:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		$("#second_reminder_row").fadeIn();
		$("#show_second_reminder").fadeOut();
		
		//set defaults if id is empty
		var reminder_id2 = $("#reminder_id2").val();
		if (reminder_id2=="-1") {
			$("#reminder_type2").val("email");
			$("#reminder_interval2").val("3");
			$("#reminder_span2").val("hours");
		}
	},
	addEvent:function (event) {
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		addForm(event, "event", "event");
		return;
    },
	editDialogField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget;
		//get rid of Grid, get the pure name, add to the root class
		master_class = "";
		if ($("#" + element.id).hasClass("master_grid")) {
			var field_name = element.id;
			field_name = field_name.replace("Grid", "");
			master_class = ".event_" + field_name;
		}
		editField(element, master_class);
	},	
	saveDialogViewField: function (event) {
		event.preventDefault(); // Don't let this button submit the form
		
		var element = event.currentTarget.id;
		element = element.replace("SaveLink", "");
		
		//get the parent to get the class
		var theclass = event.currentTarget.parentElement.parentElement.parentElement.parentElement.className;
		var arrClass = theclass.split(" ");
		theclass = "." + arrClass[0] + "." + arrClass[1];
		field_name = theclass + " #" + element;
		
		//restore the read look
		editField($(field_name + "Grid"));
		
		var element_value = $(field_name + "Input").val();
		$(field_name + "Span").html(element_value);
		
		this.model.set(element, element_value);
		
		//tell the model you are in editing mode, do not toggle
		this.model.set("editing", true);
		
		if (typeof kases != "undefined") {
			if (this.model.id!="") {
				kases.get(this.model.id).set(this.model.toJSON());
			}
		}
	},
	toggleDialogEdit: function(event) {
		event.preventDefault();
		if (typeof this.model.get("editing") == "undefined") {
			this.model.set("editing", false);
		}
		if (this.model.get("editing")) {
			//we are no longer editing
			this.model.set("editing", false);
			return;
		}
		//get all the editing fields, and toggle them back
		$(".event .editing").toggleClass("hidden");
		$(".event .span_class").removeClass("editing");
		$(".event .input_class").removeClass("editing");
		
		$(".event .span_class").toggleClass("hidden");
		$(".event .input_class").toggleClass("hidden");
		$(".event .input_holder").toggleClass("hidden");
		$(".button_row.event").toggleClass("hidden");
		$(".edit_row.event").toggleClass("hidden");
	},
	resetDialogForm: function(event) {
		//this.toggleDialogEdit(e);
		event.preventDefault();
		this.toggleDialogEdit(event);
	},
	displayMain: function(event){
		$("#iframe_holder").fadeOut();
		if (blnShowBilling) {
			$("#myModalLabel").html(this.model.get("modal_title"));
			
			//hide the button
			$("#view_event").fadeOut(function() {
				var billing_employee = $("#timekeeperInput").val();
				if (billing_employee!="") {
					$("#view_billable").val("Bill Ready ✓");
					$("#view_billable").fadeIn();
					$("#cancel_billable_holder").css("display", "inline");
				} else {
					$("#view_billable").val("Not Billed");
					$("#view_billable").fadeIn();
					
					setTimeout(function() {
						$("#view_billable").val("Bill This");
					}, 2500);
				}
				
				$("#view_reminders").fadeIn();
			});
			$("#billing_holder").fadeOut();
			$("#modal_save_holder").fadeIn();
			var dialog_width = 620;
			if ($("#partie_listing").length==1) {
				dialog_width = 1000;
			}
			$('.modal-dialog').animate({width:dialog_width, marginLeft:"-500px"}, 1100, 'easeInSine');
			$("#iframe_holder").fadeIn();
			$("#event_screen").fadeIn();
		}
	},
	displayReminders: function(event){
		var event_id = $("#table_id").val();
		$("#view_reminders").fadeOut();
		if($("#store_users").val() != "" || $("#assigneeInput").val() != ""){
			var self = this;
			if (event_id > 0) {
				//do not save, just open the iframe
				if (customer_id == 1033 || customer_id == 1096) {
					var assignees = $("#assigneeInput").val();
					var arrPersons = assignees.split(",");
					var arrAssignees = [];
					for(var i = 0; i < arrPersons.length; i++){
						arrAssignees.push(["assignee", arrPersons[i]]);
					}

					var users_json = $("#store_users").val() + JSON.stringify(arrAssignees);
					var event_id = $("#table_id").val();
					var event_date = $("#event_dateandtimeInput").val();
					var event_type = $("#event_typeInput").val();
					var url = "../sms/case_worker_reminder.php?event_date=" + event_date + "&users=" + users_json + "&event_id=" + event_id + "&event_type=" + event_type;
					
					document.getElementById("reminder_holder").src = url;
					if (customer_id == 1033) {
						$("#iframe_holder").fadeIn();
						$("#event_screen").fadeOut();
						$("#billing_holder").fadeOut();
						$("#modal_save_holder").fadeIn();
						$('.modal-dialog').animate({width:1000, marginLeft:"-600px"}, 1100, 'easeInSine');
					}
					// $('.modal-dialog').animate({width:1500, marginLeft:"-750px"}, 1100, 'easeInSine');
					// setTimeout(function() {
					// 	document.getElementById("iframe_holder").style.display = "block";
					// }, 1100);					
				}
			} else {
				blnDoNotCloseModal = true;
				saveEventModal(event);
			}
			$("#show_reminder").fadeOut();
			event.preventDefault(); // Don't let this button submit the form
		} else {
			$("#reminders_warning").fadeIn();
			setTimeout(function() {
				$("#reminders_warning").fadeOut();
			}, 2500);
			event.preventDefault();
		} 
		event.preventDefault(); // Don't let this button submit the form
		// <% if(occurrence.id == "-1") { %> style="display:none" <% } %>		
	},
	cancelBillable: function(event){
		var self = this;
		event.preventDefault(); // Don't let this button submit the form
		
		if (blnShowBilling) {
			$("#billing_holder").html("");
			$("#cancel_billable_holder").fadeOut();
			$("#view_billable").val("Bill This");
		}
	},
	displayBillable: function(event){
		event.preventDefault(); // Don't let this button submit the form
		var event_id = $("#table_id").val();
		
		if (blnShowBilling) {
			this.model.set("modal_title", $("#myModalLabel").html());
			$("#iframe_holder").fadeOut();
			$("#event_screen").fadeOut();
			$("#myModalLabel").html("Bill this Event");
			$("#cancel_billable_holder").css("display", "none");
			//hide the button
			$("#view_billable").fadeOut(function() {
				$("#view_event").val("Return to Form");
				$("#view_event").fadeIn();
				$("#view_reminders").fadeOut();
			});
			
			$('.modal-dialog').animate({width:540, marginLeft:"-230px"}, 700, 'easeInSine');
			$("#billing_holder").fadeIn();
			//$('#billing_dateInput').datetimepicker({ validateOnBlur:false, minDate: 0, allowTimes:workingWeekTimes,step:30});
			$("#modal_save_holder").fadeOut();
			//already in?
			if ($("#billing_holder").html().trim() == "") {
				var bill = new BillingMain({ case_id: current_case_id, action_id: -1, action_type: "event"});
				bill.set("holder", "billing_holder");
				bill.set("billing_date", moment().format("MM/DD/YYYY"));
				bill.set("activity_category", "Event");
				bill.set("activity_id", -1);
				bill.set("case_id", $(".event #case_id").val());
				
				bill.set("activity", this.model.get("modal_title"));
				
				$("#billing_holder").html(new activity_bill_view({model: bill}).render().el);
			}
		}
		$("#show_reminder").fadeOut();
		
	}
});
		
function summarize() {
	var arrSummary = [];
	var interval_value = $('#recurrent_interval').val();
	var end_recurrent_on = "";
	if ($('select').val() == "daily") { 
		if (interval_value == "1") {
			interval_value = "Everyday"
		} else {
			interval_value = "Every " + interval_value + " days";
		}
		var start_value = $('#recurrent_dateandtimeInput').val();
		if (start_value != "") {
			
			start_value = "Starts on " + start_value;
		}
		if ($('#never').is(':checked')) {
			end_recurrent_on = "Never Ends";
		}
		if ($('#after_date').is(':checked')) {
			var occurences_amount = $('#end_after_dateInput').val();
			var occurences_done = "";
			if (occurences_amount == "1") {
				occurences_done = "occurence";
			} else {
				occurences_done = "occurences";
			}
			end_recurrent_on = "After " + occurences_amount + " " + occurences_done;
		}
		if ($('#on_date').is(':checked')) {
			var till_date = $('#end_on_dateInput').val();
			if (till_date != "") {
				till_date = "occurence";
			}
			end_recurrent_on = "Ends on " + till_date;
		}
		arrSummary[arrSummary.length] = "Daily";
		arrSummary[arrSummary.length] = interval_value;
		arrSummary[arrSummary.length] = start_value;
		arrSummary[arrSummary.length] = end_recurrent_on;
		
	}
	/*if ($('#recurrent_interval').val() == "daily") { 
		arrSummary[arrSummary.length] = "Daily";
	}
	if ($('#recurrent_interval').val() == "daily") { 
		arrSummary[arrSummary.length] = "Daily";
	}
	if ($('#recurrent_interval').val() == "daily") { 
		arrSummary[arrSummary.length] = "Daily";
	}*/
	var summary = arrSummary.join(", ");
	$("#summary_span").html(summary);
}
function showEventTaskDateBox() {
	if ($("#apply_tasks").prop("checked")) {
		$("#follow_up_holder").css("background", "orange");
		$("#follow_up_holder").css("font-weight", "bold");
		$("#follow_up_label").html("Task Due Date:");
	} else {
		$("#follow_up_holder").css("background", "none");
		$("#follow_up_holder").css("font-weight", "normal");
		$("#follow_up_label").html("Follow Up Date:");
	}
	$("#callback_dateInput").val($("#event_dateandtimeInput").val());
	$("#follow_up_row").fadeIn();
}