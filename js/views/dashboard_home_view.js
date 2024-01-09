window.dashboard_home_view = Backbone.View.extend({
    initialize:function () {

    },
    events:{
	"click #home_view_all_done":	"doTimeouts"
  	
    },
    render:function () {
		var self = this;
		console.log('typeof this.template'+typeof this.template);
		
		if (typeof this.template != "function") {
			
			
			var view = "dashboard_home_view";
			var extension = "php";
			this.model.set("holder", "content");
			loadTemplate(view, extension, this);
			return "";
		   }
		   
		   
		
		$(this.el).html(this.template());
		var mymodel = new Backbone.Model();
		mymodel.set("key", "");
		//$('#content').html(new kase_listing_view({collection: kases, model: mymodel}).render().el);
		//put recent messages in subview holder
		if (login_nickname!='RXQ') {
			var messages = new NewPhoneCalls();
			//messages = new InboxCollection();
			messages.fetch({
				success: function (data) {
					if (data.length > 0) {
						var message_listing_info = new Backbone.Model;
						message_listing_info.set("title", "Phone Messages");
						message_listing_info.set("first_column_label", "From");
						message_listing_info.set("receive_label", "On");
						message_listing_info.set("homepage", true);
						message_listing_info.set("event_class", "messages");
						message_listing_info.set("holder", "row_1_col_1");
						$('#row_1_col_1').html(new event_listing({collection: data, model: message_listing_info}).render().el);
						$("#row_1_col_1").removeClass("glass_header_no_padding");
					} else {
						$('#row_1_col_1').html("<span class='large_white_text'>No Phone Messages</span>");
					}
				}
			});
		} else {
			var court_events = new CourtCalendarEvents({byuser: true});
			court_events.fetch({
				success: function (data) {
					if (data.length > 0) {
						var calendar_info = new Backbone.Model;
						var the_title = "Unscheduled Court Calendar Events";				
						var the_container = "#row_1_col_1";
						calendar_info.set("title", the_title);
						calendar_info.set("homepage", false);
						calendar_info.set("event_class", "listing");
						calendar_info.set("worker", login_nickname);
						calendar_info.set("thetype", "");
						calendar_info.set("start", "");
						calendar_info.set("end", "");
						calendar_info.set("import_date", moment(data.toJSON()[0].import_date).format("MM/DD/YYYY"));
						
						$(the_container).removeClass("glass_header_no_padding");
						
						var occurencesView = new event_listing({el: $(the_container), collection: court_events, model: calendar_info}).render();
					}
				}
			});
		}
		//fetch my tasks
		var tasks = new TaskInboxCollection({ day : moment().format("YYYY-MM-DD"), single_day: "y" });
		tasks.fetch({
			success: function (data) {
				if (data.length > 0) {
					var task_listing_info = new Backbone.Model;
					task_listing_info.set("title", "Today's Tasks");
					task_listing_info.set("receive_label", "Due");
					task_listing_info.set("homepage", true);
					$('#row_1_col_2').html(new task_listing({collection: data, model: task_listing_info}).render().el);
					$("#row_1_col_2").removeClass("glass_header_no_padding");
					
				} else {
					var due_count = "";
					if (overdue_tasks_count > -1) {
						if (overdue_tasks_count > 1) {
							if (overdue_tasks_count > 99) {
								overdue_tasks_count = "99+";
							}
							due_count = "<div style='float:left; width:45px;'><a href='#taskoverdue' class='white_text'><div style='background: red; color: white; border:1px solid white; padding-left:2px; padding-right:2px; width:22px; text-align:center'>" + overdue_tasks_count + "</div></a></div>You have Overdue Tasks";
						} else {
							due_count = "You have NO Overdue Tasks";
						}
					}
					$('#row_1_col_2').html("<span class='large_white_text'>No Upcoming Tasks</span>&nbsp;&nbsp;<a title='Click to view assigned tasks' id='show_assigned' style='cursor:pointer' href='#taskoutbox'><i class='glyphicon glyphicon-tasks' style='color:#66FF33'>&nbsp;</i></a>&nbsp;<div style='display:; color:white' id='homepage_overdue_tasks_notification'>" + due_count + "</div>");
				}
			}
		});
		
		//fectch unattended cases
		var unattended = new UnattendedCount();
		unattended.fetch({
			success: function (data) {
				var case_count = data.get("case_count");
				if (case_count > 0) {
					if (case_count > 99) {
						case_count = "99+";
					}
					if ($("#unattended_case_count").length==0) {
						$('#row_2_col_2').append("<div id='unattended_case_count' class='white_text' style=' margin-top:5px; width:650px'><div style='float:left; width:45px;'><a href='#unattendeds' class='white_text'><div style='background:navy; padding-left:2px; padding-right:2px; border:1px solid white; width:30px; text-align:center'>" + case_count + "</div></a></div>You are the Coordinator on Cases that have not been accessed in 35+ days</div>");
					}
				} else {
					if ($("#unattended_case_count").length==0) {
						$('#row_2_col_2').append("<div id='unattended_case_count' class='white_text' style=' margin-top:5px; width:650px'>All your cases assigned to you have been accessed in the last 35 days&nbsp;<span style='background:green;color:white; padding:1px'>&#10003;</span></div>");
					}
				}
			}
		});
		
		//fetch unattended wcab cases
		var inactivewcab = new InactiveTypeCount({type: "wcab"});
		inactivewcab.fetch({
			success: function (data) {
				var case_count = data.get("case_count");
				if (case_count > 0) {
					if (case_count > 99) {
						case_count = "99+";
					}
					if ($("#inactive_wcab_count").length==0) {
						$('#row_2_col_2').append("<div id='inactive_wcab_count' class='white_text' style=' margin-top:5px; width:650px'><div style='float:left; width:45px;'><a href='#inactives/wcab' class='white_text'><div style='background:navy; padding-left:2px; padding-right:2px; border:1px solid white; width:30px; text-align:center'>" + case_count + "</div></a></div>WCAB Cases in iKase with no activity for 35+ days</div>");
					}
				}
			}
		});
		//fetch unattended subout wcab cases
		var inactivewcab = new InactiveTypeSuboutCount({type: "wcab"});
		inactivewcab.fetch({
			success: function (data) {
				if (customer_id==1064) {
					//per azadeh 10/4/2017
					return;
				}
				var case_count = data.get("case_count");
				if (case_count > 0) {
					if (case_count > 99) {
						case_count = "99+";
					}
					if ($("#inactive_subout_wcab_count").length==0) {
						$('#row_2_col_2').append("<div id='inactive_subout_wcab_count' class='white_text' style=' margin-top:5px; width:650px'><div style='float:left; width:45px;'><a href='#inactivesub/wcab' class='white_text' title='Click here to review WC Cases in iKase with no activity for 35+ days'><div style='background:navy; padding-left:2px; padding-right:2px; border:1px solid white; width:30px; text-align:center'>" + case_count + "</div></a></div>WCAB Subout Cases in iKase with no activity for 35+ days</div>");
					}
				}
				//$('#inactiveall_wcab_count').append("<div style='margin-top:15px; color:white; font-style:italic; font-size:0.8em'>Does not include Sub-Outs</div>");
			}
		});
		
		//fectch unattended pi cases
		var inactivepi = new InactiveTypeCount({type: "pi"});
		inactivepi.fetch({
			success: function (data) {
				if (customer_id==1064) {
					//per azadeh 10/4/2017
					return;
				}
				var case_count = data.get("case_count");
				if (case_count > 0) {
					if (case_count > 99) {
						case_count = "99+";
					}
					if ($("#inactiveall_pi_count").length==0) {
						$('#row_2_col_2').append("<div id='inactiveall_pi_count' class='white_text' style=' margin-top:5px; width:650px'><div style='float:left; width:45px;'><a href='#inactives/pi' class='white_text' title='Click here to review PI Cases in iKase with no activity for 35+ days'><div style='background:navy; padding-left:2px; padding-right:2px; border:1px solid white; width:30px; text-align:center'>" + case_count + "</div></a></div>PI Cases with no activity for 35+ days</div>");
					}
				}
				//$('#inactiveall_pi_count').append("<div style='margin-top:15px; color:white; font-style:italic; font-size:0.8em'>Does not include Sub-Outs</div>");
			}
		});
		
		//fetch kases without workers
		var kasesnoworker = new KaseNoWorkersCollection();
		kasesnoworker.fetch({
			success: function(data) {
				var case_count = data.length;
				if (case_count > 0) {
					if (case_count > 99) {
						case_count = "99+";
					}
					if ($("#noworkers_count").length==0) {
						setTimeout(function() {
							$('#row_2_col_2').append("<div id='noworkers_count' class='white_text' style=' margin-top:15px; padding-top:10px; width:650px; border-top:1px solid white'><div style='float:left; width:45px;'><a href='#kasesnoemployees' class='white_text' title='Click here to review Cases in iKase with no assigned employees'><div style='background:red; padding-left:2px; padding-right:2px; border:1px solid white; width:30px; text-align:center'>" + case_count + "</div></a></div> Cases without Assigned Coordinator/Attorney</div><div style='font-style:italic; margin-top:15px' class='white_text'>Some Automatic Notifications and Tasks will not be applied to these Cases</div>");
						}, 2000);
					}
				}
			}
		}); 
		
		//fetch out tasks
		/*
		tasks = new TaskOutboxCollection();
		tasks.fetch({
			success: function (data) {
				if (data.length > 0) {
					var task_listing_info = new Backbone.Model;
					task_listing_info.set("title", "Assigned Tasks");
					task_listing_info.set("homepage", true);
					task_listing_info.set("receive_label", "Due");
					$('#row_2_col_2').html(new task_listing({collection: data, model: task_listing_info}).render().el);
					$("#row_2_col_2").removeClass("glass_header_no_padding");
				} else {
					$('#row_2_col_2').html("<span class='large_white_text'>No Assigned Tasks.</span>");
				}
			}
		});
		*/
		//fetch upcoming events
		/*
		if (typeof stored_customer_events != "undefined") {
			stored_customer_events.fetch({ 
				success: function(stored_customer_events) { 
					//console.log(data.toJSON()) 
					var upcoming_customer_events = stored_customer_events.where({cal_sort_order: "0"}); 
					//filter by date
					upcoming_customer_events = upcoming_customer_events.filter(function(my_event){ 
						var blnReturn;
						var event_date = my_event.get('event_dateandtime');
						blnReturn = (moment(event_date) >= moment());
						return blnReturn;
					});
					occurences = new Backbone.Collection(upcoming_customer_events);
					if (occurences.length > 0) {
						var event_listing_info = new Backbone.Model;
						event_listing_info.set("title", "Upcoming Events");
						event_listing_info.set("homepage", true);
						event_listing_info.set("event_class", "upcoming");
						$('#row_2_col_1').html(new event_listing({collection: occurences, model: event_listing_info}).render().el);
						$("#row_2_col_1").removeClass("glass_header_no_padding");
					} else {
						$('#row_2_col_1').html("<span class='large_white_text'>No Upcoming Events</span>");
					}
				} 
			});
			blnFetch = false;
			return;
		} else {
			*/
			occurences = new UpcomingEvents();
			occurences.fetch({
				success: function (occurences) {
					if (occurences.length > 0) {
						var event_listing_info = new Backbone.Model;
						event_listing_info.set("title", "Upcoming Events");
						event_listing_info.set("homepage", true);
						event_listing_info.set("event_class", "upcoming");
						$('#row_2_col_1').html(new event_listing({collection: occurences, model: event_listing_info}).render().el);
						$("#row_2_col_1").removeClass("glass_header_no_padding");
					} else {
						$('#row_2_col_1').html("<span class='large_white_text'>No Upcoming Events</span>");
					}
				}
			});
			//refreshEvents(current_max_track_id, 0);
		//}
		
		
		/*
		var injury = new Injury({case_id: this.model.id});
		injury.fetch({
			success: function (data) {
				data.set("case_id", self.model.get("id"));
				data.set("case_uuid", self.model.get("uuid"));
				data.set("gridster_me", true);
				data.set("glass", "card_fade_4");
				data.set("grid_it", true);
				setTimeout(function() {
					$('#injury_holder').html(new injury_view({model: data}).render().el);
				}, 1000);
				
				if (data.get("id")==-1) {
					setTimeout(function() {
						$(".bodyparts .edit").trigger("click");	
						$("#bodyparts_buttons").hide();					
					}, 1500);
				}
			}
		});
		*/
		
        return this;
    },
	doTimeouts:function() {
		var self = this;
		//in case we want to do stuff after load...
		if (blnAdmin) {
			this.showOverdueTasks();	
		}
		if (blnShowIntroTitle) {
			//only once
			blnShowIntroTitle = false;
			$("#welcome_to_ikase").fadeIn(function() {
				//$("#welcome_to_ikase").html('<span style="background:yellow; color:black; padding:2px">iKase will be down for maintenance over the Labor Day Weekend.</span>');
				setTimeout(function() {
					if (!blnAdmin) {
						$("#welcome_to_ikase").fadeOut();
					}
				}, 12500);
			});
		} else {
			//we're back, only tasks
			if (blnAdmin) {
				self.showOverdueTasks();	
			}
		}
		$("#row_2_col_1").css("height", (window.innerHeight - 410) + "px");
		$("#row_2_col_2").css("height", (window.innerHeight - 410) + "px");
	},
	showOverdueTasks: function() {
		//utilities.js
		overdueTasksCount(true);
	},
	showAssigned:function() {
		$('#show_assigned').hide();
		$('#row_2_col_2').show();
		$('#hide_assigned').show();
		
	},
	hideAssigned:function() {
		$('#hide_assigned').hide();
		$('#row_2_col_2').hide();
		$('#show_assigned').show();
		
	}
});
