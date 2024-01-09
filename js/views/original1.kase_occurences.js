var occurence_timeout_id = false;
window.kase_occurences_print_view = Backbone.View.extend({
	render: function(){
		var mymodel = this.collection.toJSON();
		$(this.el).html(this.template({occurences: mymodel}));
		
		return this;
	}
});
var kase_occurences_view = Backbone.View.extend({
	initialize: function(){
		_.bindAll(this); 
		this.collection.bind('reset', this.addAll);
	},
	events: {
		"click #calendar_print": 		"calendarPrintView",
		"change #event_typeFilter":		"filterCalendar",
		"click #calendar_list":			"calendarListView"
	},
	render: function() {
		var self = this;
		var options = {
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			defaultView: 'month',
			aspectRatio: 2,
			theme: false,
			selectable: true,
			selectHelper: true,
			editable: true,
			ignoreTimezone: false,                
			//select: this.select,
			dayClick: this.dayClick,
			eventClick: this.eventClick,
			eventDrop: this.eventDropOrResize,        
			eventResize: this.eventDropOrResize,
			events: {
				textColor: 'black'
			},
			loading: function(isLoading) {
				if(!isLoading && isTouchDevice())
				{
					// Since the draggable events are lazy(bind)loaded, we need to
					// trigger them all so they're all ready for us to drag/drop
					// on the iPad. w00t!
					$('.fc-event-draggable').each(function(){
						var e = jQuery.Event("mouseover", {
							target: this.firstChild,
							_dummyCalledOnStartup: true
						});
						$(this).trigger(e);
					});
				}
			},
			viewRender: function (view, element) {
				current_calendar_view = view.name;
				if (view.name=="month") {
					current_calendar_start = moment(view.title.split(" ")[0] + " 1 " + view.title.split(" ")[1])._d;
					current_calendar_end = view.intervalEnd._d;
				} else {
					current_calendar_start = view.start._d;
					current_calendar_end = view.end._d;
				}
				
				if (blnBlockDays) {
					var start_date = moment(current_calendar_start).format("YYYY-MM-DD");
					var end_date = moment(current_calendar_end).format("YYYY-MM-DD");
					var url = "../api//blockeddates/" + start_date + "/" + end_date;
					$.ajax({
						url:url,
						type:'GET',
						dataType:"json",
						success:function (data) {
							var arrLength = data.length;
							var fcdays = document.getElementsByClassName("fc-day");
							var arrDaysLength = fcdays.length;
							var today = clearTime(new Date());
							$("#blocked_count").html();
							if (arrLength > 0) {
								$("#blocked_count").html("(" + arrLength + ")");
							}
							for(var i = 0; i < arrLength; i++) {
								var blocked_date = data[i];
								if (moment(today).format("X") > moment(blocked_date).format("X")) {
									continue;
								}
								
								//cycle through the days
								for(var j = 0; j < arrDaysLength; j++) {
									var fcday = fcdays[j];
									var data_date = fcday.getAttribute("data-date");
									if (blocked_date == data_date) {
										fcday.style.background = "red";
										var date = new Date(data_date);
										//previous day
										//date.setDate(date.getDate() -1);
										//no new button
										var datestamp = date.getTime();
										if (document.getElementById("newevent_" + datestamp) != null) {
											document.getElementById("newevent_" + datestamp).style.display = "none";
										}
									}
								}
							}
						}
					});
				}
			 }
		};
		/*
		var method = (isTouchDevice()) ? 'eventMouseover' : 'eventClick'; 
		options[method] = function(event, jsEvent, view) { 
			if(jsEvent._dummyCalledOnStartup) 
					{ 
							return; 
					} 
					// Do something here when someone clicks on the event.
					this.eventClick
			}
		*/
		
		this.model.set("current_calendar_view", current_calendar_view);
		this.model.set("current_calendar_start", current_calendar_start);
		this.model.set("current_calendar_end", current_calendar_end);
		
		this.$el.fullCalendar(options);
		this.addAll();
		
		calendar_element = this.$el;
		global_calendar_element = this.$el;
		
		setTimeout(function(){
			$("#kase_content").prepend('<div style="width:100%; margin-left:auto; margin-right:auto; text-align:center; display:none" class="calendar_title" id="page_title">Kase Kalendar</div>');
			
			calendarFadeIn();
			
			//do we need to change view
			if (self.model.get("current_calendar_view")!="") {
				calendar_element.fullCalendar('changeView', self.model.get("current_calendar_view"));
				calendar_element.fullCalendar('gotoDate', self.model.get("current_calendar_start"));
			}
		}, 100);
		
		
		if (typeof customer_settings.get("background") != "undefined") {
			setTimeout(function() {
				$(".fc-view-container").css("background", customer_settings.get("background"))
			}, 500);
		}
	},
	filterCalendar: function(event) {
		var element = event.currentTarget;
		//filterEvents(this.$el, 'api/kase/events/');
		//filterCustomerCalendar(this.$el);
	},
	calendarPrintView: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var theid = element.id.split("_")[2];
		
		//which view are we looking at
		var theview = this.$el.fullCalendar( 'getView' );
		var start = moment(theview.start._d).format("YYYY-MM-DD");
		var end = moment(theview.end._d).format("YYYY-MM-DD");
		window.open("report.php#" + document.location.href.split("#")[1] + "/" + start + "/" + end);
		//" + document.location.href.split("#")[1];
	},
	calendarListView: function(event) {
		event.preventDefault();
		
		var hash = document.location.hash;
		hash = hash.replace("kalendar", "kalendarlist");
		
		document.location.href = hash;
		/*
		var href = Backbone.history.getFragment();
		var arrHref = href.split("/");
		if (arrHref.length > 2) {
			return;
		}
		//reset in case we did it more than once
		href = href.replace("kalendarbydate", "kalendar");
		//now set it
		href = href.replace("kalendar", "kalendarlist");
		
		document.location.href = "#" + href;
		*/
		return;
		Backbone.history.navigate(href);
		var theview = this.$el.fullCalendar( 'getView' );
		var start = moment(theview.start._d).format("YYYY-MM-DD");
		var end = moment(theview.end._d).format("YYYY-MM-DD");
		if (theview.name=="agendaDay") {
			end = start;
		}
		listCustomerEvents(start, end, current_case_id);
	},
	addAll: function() {
		this.$el.fullCalendar('addEventSource', this.collection.toJSON());
	},
	addOne: function(event) {
		this.$el.fullCalendar('renderEvent', event.toJSON());
	},        
	eventClick: function(fcEvent) {
		//id = event_id, also case_id gets passed back to app.js for routing
		//console.log(fcEvent);
		calendar_event = fcEvent;
		var event_id = fcEvent.id;
		var case_id = this.model.get("case_id");
		var start_date = fcEvent.start;
		if (typeof start_date._d != "undefined") {
			var day_date = start_date._d.getTime();
		} else {
			var day_date = start_date.getTime();
		}
		var event_kind = "";
		var element_id = event_id + "_" + case_id + "_" + start_date + "_" + day_date + "_" + event_kind; 
		composeEvent(element_id);
	},
	dayClick: function(view) {
		if (typeof view._d != "undefined") {
			view.add(1, 'days');
			view.subtract(9, 'hours');
		}
		var event_id = "-1";
		var day_date_click = new Date(view);
		var day_date = day_date_click.getTime();
		var today_date = new Date(moment().format("YYYY-MM-DD") + " 00:00:00").getTime();
		if (day_date < today_date) {
			return;
		}
		//alert(day_date);
		var case_id = this.model.get("case_id");
		//window.history.pushState(null, null, "#kases/events/edit/" + case_id + "/" + event_id + "/" + case_number);
		//document.location.href = "#kases/events/edit/" + case_id + "/" + event_id + "/" + day_date;
		var event_id = "-1";
		var day_date_click = new Date(view);
		var day_date = day_date_click.getTime();
		var today_date = new Date(moment().format("YYYY-MM-DD") + " 00:00:00").getTime();
		if (day_date < today_date) {
			return;
		}
		//alert(day_date);
		var case_id = this.model.get("case_id");
		var start_date = view;
		var element_id = event_id + "_" + case_id + "_" + start_date + "_" + day_date + "_"; 
		composeEvent(element_id);
		
	},
	change: function(event) {
		// Look up the underlying event in the calendar and update its details from the model
		var fcEvent = this.$el.fullCalendar('clientEvents', event.get('id'))[0];
		fcEvent.title = event.get('title');
		fcEvent.color = event.get('color');
		fcEvent.start = event.get('datetimepicker');
		this.$el.fullCalendar('updateEvent', fcEvent);         
	},
	
	formatDate: function(thedate) {
		var themonth = String(thedate.getMonth() + 1);
		var theyear = String(thedate.getFullYear());
		var theday = String(thedate.getDate());
		var thehour = String(thedate.getHours());
		var theminute = String(thedate.getMinutes());
		thedate = theyear + "-" + themonth + "-" + theday + " " + thehour + ":" + theminute;
		return thedate;
	},

	eventDropOrResize: function(fcEvent) {
		// Lookup the model that has the ID of the event and update its attributes
		//this.collection.get(fcEvent.id).save({start: fcEvent.start, end: fcEvent.end});   
		//MOVE THE EVENT
		if (!confirm("Press OK to confirm this move")) {
			return;
		}
		var url = 'api/event/move';
		if (typeof fcEvent.datetimepicker == "undefined") {
			var thedate = moment(fcEvent.start).format("YYYY-MM-DD HH:mm");
		} else {
			var thedate = moment(fcEvent.datetimepicker).format("YYYY-MM-DD HH:mm");
		}
		
        var formValues = {
            id: fcEvent.id,
			start:thedate
        };

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
               
                if(data.error) {  // If there is an error, show the error messages
                    $('.alert-error').text(data.error.text).show();
                }
 				clearStoredEvents(current_max_track_id, 0, true);
			}
        });
	},
	destroy: function(event) {
		this.$el.fullCalendar('removeEvents', event.id);         
	}
});
var customer_collection = new OccurenceCustomerCollection();
var blnFiltering = false;
var arrDataFresh = [];
var kase_cus_occurences_view = Backbone.View.extend({
	initialize: function(){
		//_.bindAll(this); 
		//this.collection.bind('reset', this.addAll);
	},
	events: {
		"click #calendar_print": 		"calendarPrintView",
		"change #event_typeFilter":		"filterCalendar",
		"change #attorneyFilter":		"filterAttorneyCalendar",
		"change #workerFilter":			"filterWorkerCalendar",
		"change #assigneeFilter":		"filterAssigneeCalendar",
		"click #calendar_list":			"calendarListView",
		"click #calendar_new":			"newEvent",
		"mousedown .view_day":			"viewDay"
	},
	render: function() {
		var self = this;
		blnFiltering = false;
		customer_collection = this.collection;
		
		var options = {
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			defaultView: this.model.get("view"),
			aspectRatio: 2,
			theme: false,
			selectable: true,
			selectHelper: true,
			editable: true,
			ignoreTimezone: false, 
			views: {
				month: {
					eventLimit: 4 // adjust to 6 only for agendaWeek/agendaDay
				}
			},     
			eventLimitClick : "popover",         
			//select: this.select,
			dayClick: this.dayClick,
			eventClick: this.eventClick,
			eventDrop: this.eventDropOrResize,  
			eventResize: this.eventDropOrResize,
			events: {
				textColor: 'black'
			},
			loading: function(isLoading) {
				if(!isLoading && isTouchDevice())
				{
					// Since the draggable events are lazy(bind)loaded, we need to
					// trigger them all so they're all ready for us to drag/drop
					// on the iPad. w00t!
					$('.fc-event-draggable').each(function(){
						var e = jQuery.Event("mouseover", {
							target: this.firstChild,
							_dummyCalledOnStartup: true
						});
						$(this).trigger(e);
					});
				}
			},
			viewRender: function (view, element) {
				current_calendar_view = view.name;
				if (view.name=="month") {
					current_calendar_start = moment(view.title.split(" ")[0] + " 1 " + view.title.split(" ")[1])._d;
					current_calendar_end = view.intervalEnd._d;
				} else {
					current_calendar_start = view.start._d;
					current_calendar_end = view.end._d;
				}
				
				//is it more than 6 months ago?
				var this_month = new Date().getMonth();
				var this_year = new Date().getFullYear();
				
				var cal_month = current_calendar_start.getMonth();
				var cal_year = current_calendar_start.getFullYear();
				
				var year_month = cal_year + "/" + cal_month;
				
				if (this_year==cal_year) {
					if ((this_month - cal_month) > 5) {
						if (arrDataFresh.indexOf(year_month) < 0) {
							arrDataFresh.push(cal_year + "/" + cal_month);
							var start = moment(current_calendar_start).format("YYYY-MM-DD");
							var end = moment(current_calendar_end).format("YYYY-MM-DD");
							var month_customer_events = new OccurenceCustomerCollection({start: start, end: end, show_all: false});
							//get the events for this month							
							month_customer_events.fetch({
								success: function (data) {
									var jdata = data.toJSON();
									_.each(jdata, function(customer_event) {
										//store in localStorage
										var myOccurence = new Occurence({event_id: customer_event.id});
										myOccurence.set(customer_event);
										stored_customer_events.add(myOccurence);
										myOccurence.save();
									});
									
									showCustomerKalendar("month", current_calendar_start);
									return;
								}
							});
						}
					}
				}
				//look up the blocked dates for company during the month
				/*
				var blockeDates = new BlockedDatesCollection(
					{
						start_date: moment(current_calendar_start).format("YYYY-MM-DD"),
						end_date: moment(current_calendar_end).format("YYYY-MM-DD")
					}
				);
				*/
				if (blnBlockDays) {
					var start_date = moment(current_calendar_start).format("YYYY-MM-DD");
					var end_date = moment(current_calendar_end).format("YYYY-MM-DD");
					var url = "../api//blockeddates/" + start_date + "/" + end_date;
					$.ajax({
						url:url,
						type:'GET',
						dataType:"json",
						success:function (data) {
							var arrLength = data.length;
							var fcdays = document.getElementsByClassName("fc-day");
							var arrDaysLength = fcdays.length;
							var today = clearTime(new Date());
							$("#blocked_count").html();
							if (arrLength > 0) {
								$("#blocked_count").html("(" + arrLength + ")");
							}
							for(var i = 0; i < arrLength; i++) {
								var blocked_date = data[i];
								if (moment(today).format("X") > moment(blocked_date).format("X")) {
									continue;
								}
								
								//cycle through the days
								for(var j = 0; j < arrDaysLength; j++) {
									var fcday = fcdays[j];
									var data_date = fcday.getAttribute("data-date");
									if (blocked_date == data_date) {
										fcday.style.background = "red";
										var date = new Date(data_date);
										//previous day
										//date.setDate(date.getDate() -1);
										//no new button
										var datestamp = date.getTime();
										if (document.getElementById("newevent_" + datestamp) != null) {
											document.getElementById("newevent_" + datestamp).style.display = "none";
										}
									}
								}
							}
						}
					});
				}
			 }
		};
		/*
		var method = (isTouchDevice()) ? 'eventMouseover' : 'eventClick'; 
		options[method] = function(event, jsEvent, view) { 
			if(jsEvent._dummyCalledOnStartup) 
					{ 
							return; 
					} 
					// Do something here when someone clicks on the event.
					this.eventClick
			}
		*/
		
		this.$el.fullCalendar(options);
		this.addAll();
		
		if (this.model.get("current_date")!="") {
			//this.$el.fullCalendar( 'gotoDate', moment(this.model.get("current_date")).format("YYYY"), moment(this.model.get("current_date")).format("MM")-1, moment(this.model.get("current_date")).format("DD") );
			this.$el.fullCalendar( 'gotoDate', moment(this.model.get("current_date")));
		}
		calendar_element = this.$el;
		global_calendar_element = this.$el;
		
		setTimeout(function(){
			if ($(".calendar_title").length == 0) {
				$("#content").prepend('<div style="width:100%; margin-left:auto; margin-right:auto; text-align:center;display:none" class="calendar_title" id="page_title">' + self.model.get("calendar_title") + '</div>');
			}
			calendarFadeIn();
			//preload the event_view if not loaded
			/*
			if (document.location.pathname.indexOf("v9") > -1) {
				var view = "event_view";
				var extension = "php";
				self.model = new Backbone.Model();
				self.model.set("holder", "dialog_content");
				self.model.set("event_title", "");
				loadTemplate(view, extension, self);
			}
			*/
		}, 100);
		
		
		if (typeof customer_settings.get("background") != "undefined") {
			setTimeout(function() {
				$(".fc-view-container").css("background", customer_settings.get("background"))
			}, 500);
		}
	},
	filterAttorneyCalendar: function(event) {
		var self = this;
		if (blnFiltering) {
			return;
		}
		blnFiltering = true;
		$(".calendar_title#page_title").html('<i class="icon-spin4 animate-spin" style="font-size:1.2em; color:white"></i>&nbsp;<span style="font-size:0.6em">loading...</span>');
		
		clearSearchResults();
		var element = event.currentTarget;
		
		var theattorney = element.value;
		if (theattorney!="") {
			var all_customer_events = new CustomerByAttorneyEvents({attorney: theattorney});
		} else {
			var all_customer_events = new OccurenceCustomerCollection();
		}
		all_customer_events.fetch({
			success: function (data) {
				//then re-assign to calendar
				blnFiltering = false;
				self.collection.reset(data.toJSON());
				self.$el.html("");
				self.render();
				
				setTimeout(function() {
					var the_worker = worker_searches.findWhere({nickname:theattorney});
					var theuser = theattorney;
					if (typeof the_worker != "undefined") {
						the_worker = the_worker.toJSON();
						theuser = the_worker.user_name;
					}
					$(".calendar_title#page_title").html(theuser.toLowerCase().capitalizeWords() + " Calendar");
					
					$("#attorneyFilter").val(theattorney);
				}, 700);
			}
		});
	},
	filterAssigneeCalendar: function(event) {
		var self = this;
		
		if (blnFiltering) {
			return;
		}
		blnFiltering = true;
		$(".calendar_title#page_title").html('<i class="icon-spin4 animate-spin" style="font-size:1.2em; color:white"></i>&nbsp;<span style="font-size:0.6em">loading...</span>');
		
		clearSearchResults();
		var element = event.currentTarget;
		
		var theworker = element.value;
		var thetype = $("#event_typeFilter").val();
		
		if (thetype=="" && theworker=="") {
			//no filter
			var all_customer_events = new OccurenceCustomerCollection();
		} else {
			if (thetype=="") {
				var all_customer_events = new CustomerByAssigneeEvents({worker: theworker});
			} else {
				var all_customer_events = new CustomerByTypeByAssigneeEvents({worker: theworker, type: thetype});
			}
		}
		all_customer_events.fetch({
			success: function (data) {
				//then re-assign to calendar
				blnFiltering = false;
				self.collection.reset(data.toJSON());
				self.$el.html("");
				self.render();
				
				setTimeout(function() {
					var the_worker = worker_searches.findWhere({nickname:theworker});
					var theuser = theworker;
					if (typeof the_worker != "undefined") {
						the_worker = the_worker.toJSON();
						theuser = the_worker.user_name;
					}
					$(".calendar_title#page_title").html(theuser.toLowerCase().capitalizeWords() + " Assignments Calendar");
					
					$("#assigneeFilter").val(theworker);
					$("#event_typeFilter").val(thetype);
				}, 700);
			}
		});
	},
	filterWorkerCalendar: function(event) {
		var self = this;
		
		if (blnFiltering) {
			return;
		}
		blnFiltering = true;
		$(".calendar_title#page_title").html('<i class="icon-spin4 animate-spin" style="font-size:1.2em; color:white"></i>&nbsp;<span style="font-size:0.6em">loading...</span>');
		
		clearSearchResults();
		var element = event.currentTarget;
		
		var theworker = element.value;
		if (theworker!="") {
			var all_customer_events = new CustomerByWorkerEvents({worker: theworker});
		} else {
			var all_customer_events = new OccurenceCustomerCollection();
		}
		all_customer_events.fetch({
			success: function (data) {
				//then re-assign to calendar
				blnFiltering = false;
				self.collection.reset(data.toJSON());
				self.$el.html("");
				self.render();
				
				setTimeout(function() {
					var the_worker = worker_searches.findWhere({nickname:theworker});
					var theuser = theworker;
					if (typeof the_worker != "undefined") {
						the_worker = the_worker.toJSON();
						theuser = the_worker.user_name;
					}
					$(".calendar_title#page_title").html(theuser.toLowerCase().capitalizeWords() + " Calendar");
					
					$("#workerFilter").val(theworker);
				}, 700);
			}
		});
	},
	filterCalendar: function(event) {
		var self = this;
		if (blnFiltering) {
			return;
		}
		blnFiltering = true;
		var element = event.currentTarget;
		var thetype = element.value;
		//manage list
		if (thetype=="new_filter") {
			composeEditCalendarFilters();
			return;
		}
		
		$(".calendar_title#page_title").html('<i class="icon-spin4 animate-spin" style="font-size:1.2em; color:white"></i>&nbsp;<span style="font-size:0.6em">loading...</span>');
		clearSearchResults();
		
		if (thetype=="case_type_wc" || thetype=="case_type_pi") {
			if ($("#occurence_listing").length > 0) {
				//filter the listing by wc/pi
				if (thetype=="case_type_wc") {
					$(".occurence_case_type_WC").show();
					$(".occurence_case_type_PI").hide();
				} else {
					$(".occurence_case_type_WC").hide();
					$(".occurence_case_type_PI").show();
				}
			} else {
				var href = "#firmkalendar/pi";
				if (thetype=="case_type_wc") {
					href = "#firmkalendar/wcab";
				}
				document.location.href = href;
			}
			return;
		}
		
		var theworker = $("#assigneeFilter").val();
		
		if (thetype=="" && theworker=="") {
			//no filter
			var all_customer_events = new OccurenceCustomerCollection();
		} else {
			if (thetype=="") {
				var all_customer_events = new CustomerByAssigneeEvents({worker: theworker});
			} else {
				var all_customer_events = new CustomerByTypeByAssigneeEvents({worker: theworker, type: thetype});
			}
		}
		/*
		if (thetype!="") {
			var all_customer_events = new CustomerByTypeEvents({type: thetype});
		} else {
			var all_customer_events = new OccurenceCustomerCollection();
		}
		*/
		all_customer_events.fetch({
			success: function (data) {
				$(".calendar_title#page_title").html("loaded, rendering...");
				//then re-assign to calendar
				blnFiltering = false;
				self.collection.reset(data.toJSON());
				self.$el.html("");
				self.render();
				setTimeout(function() {
					$(".calendar_title#page_title").html(thetype.toLowerCase().capitalizeWords() + " Calendar");
					
					$("#event_typeFilter").val(thetype);
					$("#assigneeFilter").val(theworker);
				}, 700);
			}
		});
		
	},
	newEvent: function() {
		event.preventDefault();
		composeEvent('-1_-1');
	},
	calendarListView: function(event) {
		if (blnCalendarListing) {
			return;
		}
		blnCalendarListing = true;
		clearSearchResults();
		event.preventDefault();
		var href = Backbone.history.getFragment();
		var arrHref = href.split("/");
		if (arrHref.length > 3) {
			return;
		}
		href = href.replace("ikalendar", "listkalendar");
		var current_calendar_id = arrHref[1];
		var theview = this.$el.fullCalendar( 'getView' );
		var start = moment(theview.start._d).format("YYYY-MM-DD");
		var end = moment(theview.end._d).format("YYYY-MM-DD");
		if (theview.name=="agendaDay") {
			end = start;
		}
		if (theview.name=="month") {
			//first and last of the current month
			var arrMonth = $(".fc-center").html().replace("<h2>", "").replace("</h2>", "").split(" ");
			var first_day = new Date(arrMonth[0] + " 1 " + arrMonth[1]);
			var last_day = new Date(first_day.getFullYear(), first_day.getMonth() + 1, 0, 23, 59, 59);
			
			start = moment(first_day).format("YYYY-MM-DD");
			end = moment(last_day).format("YYYY-MM-DD");
		}
		//per thomas 4/10/2017, list_view must start today
		if (current_calendar_id==1) {
			if (moment(start) < moment()) {
				start = moment().format("YYYY-MM-DD");
			}
		}
		Backbone.history.navigate(href + "/" + start + "/" + end);
		
		
		//are we filtering by worker
		var the_worker = $("#assigneeFilter").val();
		var the_type = $("#event_typeFilter").val();
		
		//however, might be intake
		var arrHash = document.location.hash.split("/");
		if (arrHash[1]=="4") {
			the_type = "Intake";
		}
		
		listCustomerEvents(start, end, "", the_type, the_worker);
	},
	calendarPrintView: function(event) {
		clearTimeout(occurence_timeout_id);
		event.preventDefault();
		var element = event.currentTarget;
		var theid = element.id.split("_")[2];
		var the_worker = $("#assigneeFilter").val();
		var the_type = $("#event_typeFilter").val();
		
		//which view are we looking at
		var theview = this.$el.fullCalendar( 'getView' );
		var start = moment(theview.start._d).format("YYYY-MM-DD");
		var end = moment(theview.end._d).format("YYYY-MM-DD");
		
		if (the_worker=="" && the_type=="") {
			occurence_timeout_id = setTimeout(function() {
				window.open("report.php#" + document.location.href.split("#")[1] + "/" + start + "/" + end);
			}, 1000);
		} else {
			if (the_type=="") {
				the_type = " ";
			}
			if (the_worker=="") {
				the_worker = " ";
			}
			occurence_timeout_id = setTimeout(function() {
				window.open("report.php#listkalendar/" + the_type + '/' + the_worker + "/" + start + "/" + end);
			}, 500);
		}
		//" + document.location.href.split("#")[1];
	},
	addAll: function() {
		var self = this;
		this.$el.fullCalendar('addEventSource', this.collection.toJSON());
	},
	addOne: function(event) {
		this.$el.fullCalendar('renderEvent', event.toJSON());
	},        
	/*select: function(startDate, endDate) {
		this.occurenceView.collection = this.collection;
		this.occurenceView.model = new Occurence({start: startDate, end: endDate});
		this.occurenceView.render();            
	},*/
	eventClick: function(fcEvent) {
		/*
		this.occurenceView.model = this.collection.get(fcEvent.id);
		this.occurenceView.collection = this.collection;
		this.occurenceView.model.set("case_number",this.model.get("case_number"));
		this.occurenceView.model.set("case_id",this.model.get("case_id"));
		this.occurenceView.render();
		*/
		//id = event_id, also case_id gets passed back to app.js for routing
		//console.log(fcEvent);
		calendar_event = fcEvent;
		var event_id = fcEvent.id;
		var start_date = fcEvent.start;
		var case_id = customer_collection.findWhere({id: event_id}).get("case_id");
		if (typeof case_id == "undefined") {
			case_id = -1;
		}
		if (typeof start_date._d != "undefined") {
			var day_date = start_date._d.getTime();
		} else {
			var day_date = start_date.getTime();
		}
		
		var event_kind = "";
		var element_id = event_id + "_" + case_id + "_" + start_date + "_" + day_date + "_" + event_kind; 
		//alert("angel");
		//window.history.pushState(null, null, "#kases/events/edit/" + case_id + "/" + event_id + "/" + case_number);
		composeEvent(element_id);
		//document.location.href = "#kases/events/edit/" + case_id + "/" + event_id + "/" + day_date;
	},
	viewDay: function(event) {
		if (blnViewClicked) {
			return;
		}
		var element = event.currentTarget;
		blnViewClicked = true;
		var element_id = element.id;
		var arrID = element_id.split("_");
		var clickedDate = new Date(arrID[1]);
		
		calendar_element.fullCalendar('changeView', 'agendaDay', moment(clickedDate).format('YYYY-MM-DD'));
		event.preventDefault();
		
		setTimeout(function() {
			//reset
			blnViewClicked = false;
		}, 100);
	},
	dayClick: function(view) {
		if (blnViewClicked) {
			//get out
			return;
		}
		if (typeof view._d != "undefined") {
			view.add(1, 'days');
			view.subtract(9, 'hours');
		}
		var event_id = "-1";
		
		var day_date_click = new Date(view);
		
		var day_date = day_date_click.getTime();
		var today_date = new Date(moment().format("YYYY-MM-DD") + " 00:00:00").getTime();
		if (day_date < today_date) {
			return;
		}
		//alert(day_date);
		//window.history.pushState(null, null, "#kases/events/edit/" + case_id + "/" + event_id + "/" + case_number);
		//document.location.href = "#kases/events/edit/" + case_id + "/" + event_id + "/" + day_date;
		var event_id = "-1";
		var day_date_click = new Date(view);
		var day_date = day_date_click.getTime();
		var today_date = new Date(moment().format("YYYY-MM-DD") + " 00:00:00").getTime();
		if (day_date < today_date) {
			return;
		}
		var case_id = -1;
		//alert(day_date);
		var start_date = view;
		var element_id = event_id + "_" + case_id + "_" + start_date + "_" + day_date + "_"; 
		composeEvent(element_id);
		
	},
	change: function(event) {
		// Look up the underlying event in the calendar and update its details from the model
		var fcEvent = this.$el.fullCalendar('clientEvents', event.get('id'))[0];
		fcEvent.title = event.get('title');
		fcEvent.color = event.get('color');
		fcEvent.start = event.get('datetimepicker');
		this.$el.fullCalendar('updateEvent', fcEvent);         
	},
	
	formatDate: function(thedate) {
		var themonth = String(thedate.getMonth() + 1);
		var theyear = String(thedate.getFullYear());
		var theday = String(thedate.getDate());
		var thehour = String(thedate.getHours());
		var theminute = String(thedate.getMinutes());
		thedate = theyear + "-" + themonth + "-" + theday + " " + thehour + ":" + theminute;
		return thedate;
	},

	eventDropOrResize: function(fcEvent) {
		// Lookup the model that has the ID of the event and update its attributes
		//this.collection.get(fcEvent.id).save({start: fcEvent.start, end: fcEvent.end});   
		//am i even allowed to move it?
		var blnEventCreator = (fcEvent.event_from==login_username);
		
		var blnMovePermission = true;
		var blnGetOut = false;
		if (customer_id==1075 || customer_id==1033) {
			blnMovePermission = false;
			if (blnAdmin || blnEventCreator) {
				blnMovePermission = true;
			}
		}
		if (!blnMovePermission) {
			blnGetOut = true;
		}
		if (!blnGetOut) {
			//confirm
			if (!confirm("Press OK to confirm this move")) {
				blnGetOut = true;
			}
		}
		if (blnGetOut) {
			setTimeout(function() {
				//slight delay
				var arrHash = document.location.hash.split("/");
				window.Router.prototype.displayCalendarEvents(arrHash[1], arrHash[2]);
			}, 50);
			return;
		}
		//MOVE THE EVENT
		var url = 'api/event/move';
		if (typeof fcEvent.datetimepicker == "undefined") {
			var thedate = moment(fcEvent.start).format("YYYY-MM-DD HH:mm");
		} else {
			var thedate = moment(fcEvent.datetimepicker).format("YYYY-MM-DD HH:mm");
		}
		
        var formValues = {
            id: fcEvent.id,
			start:thedate
        };

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
               
                if(data.error) {  // If there is an error, show the error messages
                    $('.alert-error').text(data.error.text).show();
                }
				
				clearStoredEvents(current_max_track_id, 0, true);
            }
        });
		//assume it worked, i'm ashamed
		//make sure the collection is updated
		//this.collection.get(fcEvent.id).set({start: fcEvent.start, end: fcEvent.end});   
		
		
	},
	destroy: function(event) {
		this.$el.fullCalendar('removeEvents', event.id);         
	}
});
var intake_cus_occurences_view = Backbone.View.extend({
	initialize: function(){
		_.bindAll(this); 
		this.collection.bind('reset', this.addAll);
	},
	events: {
		"click #calendar_print": 		"calendarPrintView",
		"change #event_typeFilter":		"filterCalendar",
		"change #assigneeFilter":		"filterAssigneeCalendar",
		"click #calendar_list":			"calendarListView"
	},
	calendarPrintView: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var theid = element.id.split("_")[2];
		
		//which view are we looking at
		var theview = this.$el.fullCalendar( 'getView' );
		var start = moment(theview.start._d).format("YYYY-MM-DD");
		var end = moment(theview.end._d).format("YYYY-MM-DD");
		window.open("report.php#" + document.location.href.split("#")[1] + "/" + start + "/" + end);
		//" + document.location.href.split("#")[1];
	},
	render: function() {
		var options = {
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			defaultView: 'month',
			aspectRatio: 2,
			theme: false,
			selectable: true,
			selectHelper: true,
			editable: true,
			ignoreTimezone: false,                
			//select: this.select,
			dayClick: this.dayClick,
			eventClick: this.eventClick,
			eventDrop: this.eventDropOrResize,        
			eventResize: this.eventDropOrResize,
			events: {
				textColor: 'black'
			},
			loading: function(isLoading) {
				if(!isLoading && isTouchDevice())
				{
					// Since the draggable events are lazy(bind)loaded, we need to
					// trigger them all so they're all ready for us to drag/drop
					// on the iPad. w00t!
					$('.fc-event-draggable').each(function(){
						var e = jQuery.Event("mouseover", {
							target: this.firstChild,
							_dummyCalledOnStartup: true
						});
						$(this).trigger(e);
					});
				}
			},
			viewRender: function (view, element) {
				current_calendar_view = view.name;
				if (view.name=="month") {
					current_calendar_start = moment(view.title.split(" ")[0] + " 1 " + view.title.split(" ")[1])._d;
					current_calendar_end = view.intervalEnd._d;
				} else {
					current_calendar_start = view.start._d;
					current_calendar_end = view.end._d;
				}
			 }
		};
		/*
		var method = (isTouchDevice()) ? 'eventMouseover' : 'eventClick'; 
		options[method] = function(event, jsEvent, view) { 
			if(jsEvent._dummyCalledOnStartup) 
					{ 
							return; 
					} 
					// Do something here when someone clicks on the event.
					this.eventClick
			}
		*/
		
		this.$el.fullCalendar(options);
		this.addAll();
		
		calendar_element = this.$el;
		calendar_element = this.$el;
		global_calendar_element = this.$el;
		
		setTimeout(function(){
			$("#content").prepend('<div style="width:100%; margin-left:auto; margin-right:auto; text-align:center;display:none" class="calendar_title" id="page_title">Intake Kalendar</div>');
			
			//no filters for intake
			$("#event_typeFilter").hide();
			$("#assigneeFilter").hide();
			
			calendarFadeIn();
		}, 100);
		
		
		if (typeof customer_settings.get("background") != "undefined") {
			setTimeout(function() {
				$(".fc-view-container").css("background", customer_settings.get("background"))
			}, 500);
		}
	},
	calendarListView: function(event) {
		if (blnCalendarListing) {
			blnCalendarListing = false;
			return;
		}
		blnCalendarListing = true;
		clearSearchResults();
		event.preventDefault();
		var href = Backbone.history.getFragment();
		var arrHref = href.split("/");
		if (arrHref.length > 3) {
			return;
		}
		href = href.replace("ikalendar", "listkalendar");
		var theview = this.$el.fullCalendar( 'getView' );
		var start = moment(theview.start._d).format("YYYY-MM-DD");
		var end = moment(theview.end._d).format("YYYY-MM-DD");
		if (theview.name=="agendaDay") {
			end = start;
		}
		if (theview.name=="month") {
			//first and last of the current month
			var arrMonth = $(".fc-center").html().replace("<h2>", "").replace("</h2>", "").split(" ");
			var first_day = new Date(arrMonth[0] + " 1 " + arrMonth[1]);
			var last_day = new Date(first_day.getFullYear(), first_day.getMonth() + 1, 0, 23, 59, 59);
			
			start = moment(first_day).format("YYYY-MM-DD");
			end = moment(last_day).format("YYYY-MM-DD");
		}
		Backbone.history.navigate(href + "/" + start + "/" + end);
		
		
		//are we filtering by worker
		var the_worker = $("#assigneeFilter").val();
		var the_type = $("#event_typeFilter").val();
		
		//however, might be intake
		var arrHash = document.location.hash.split("/");
		if (arrHash[1]=="4") {
			the_type = "Intake";
		}
		listCustomerEvents(start, end, "", the_type, the_worker);
	},
	filterAssigneeCalendar: function(event) {
		var self = this;
		
		if (blnFiltering) {
			return;
		}
		blnFiltering = true;
		$(".calendar_title#page_title").html('<i class="icon-spin4 animate-spin" style="font-size:1.2em; color:white"></i>&nbsp;<span style="font-size:0.6em">loading...</span>');
		
		clearSearchResults();
		var element = event.currentTarget;
		
		var theworker = element.value;
		if (theworker=="") {
			//no filter
			var all_customer_events = new CustomerIntakeCollection();
		} else {
			var thetype = "Intake";
			var all_customer_events = new CustomerByTypeByAssigneeEvents({worker: theworker, type: thetype});
		}
		all_customer_events.fetch({
			success: function (data) {
				//then re-assign to calendar
				blnFiltering = false;
				self.collection.reset(data.toJSON());
				self.$el.html("");
				self.render();
				
				setTimeout(function() {
					var the_worker = worker_searches.findWhere({nickname:theworker});
					var theuser = theworker;
					if (typeof the_worker != "undefined") {
						the_worker = the_worker.toJSON();
						theuser = the_worker.user_name;
					}
					$(".calendar_title#page_title").html(theuser.toLowerCase().capitalizeWords() + " Intake Calendar");
					
					$("#assigneeFilter").val(theworker);
				}, 700);
			}
		});
	},
	addAll: function() {
		this.$el.fullCalendar('addEventSource', this.collection.toJSON());
		//turn off new events for intake
		//$(".fc-day-new").hide();
	},
	addOne: function(event) {
		this.$el.fullCalendar('renderEvent', event.toJSON());
	},        
	/*select: function(startDate, endDate) {
		this.occurenceView.collection = this.collection;
		this.occurenceView.model = new Occurence({start: startDate, end: endDate});
		this.occurenceView.render();            
	},*/
	eventClick: function(fcEvent) {
		/*
		this.occurenceView.model = this.collection.get(fcEvent.id);
		this.occurenceView.collection = this.collection;
		this.occurenceView.model.set("case_number",this.model.get("case_number"));
		this.occurenceView.model.set("case_id",this.model.get("case_id"));
		this.occurenceView.render();
		*/
		//id = event_id, also case_id gets passed back to app.js for routing
		//console.log(fcEvent);
		calendar_event = fcEvent;
		var event_id = fcEvent.id;
		var start_date = fcEvent.start;
		var case_id = this.collection.findWhere({id: event_id}).get("case_id");
		if (typeof case_id == "undefined") {
			case_id = -1;
		}
		if (typeof start_date._d != "undefined") {
			var day_date = start_date._d.getTime();
		} else {
			var day_date = start_date.getTime();
		}
		var event_kind = "";
		var element_id = event_id + "_" + case_id + "_" + start_date + "_" + day_date + "_" + event_kind; 
		//alert("angel");
		//window.history.pushState(null, null, "#kases/events/edit/" + case_id + "/" + event_id + "/" + case_number);
		composeEvent(element_id);
		//document.location.href = "#kases/events/edit/" + case_id + "/" + event_id + "/" + day_date;
	},
	dayClick: function(view) {
		if (typeof view._d != "undefined") {
			view.add(1, 'days');
			view.subtract(9, 'hours');
		}
		var event_id = "-1";
		var day_date_click = new Date(view);
		var day_date = day_date_click.getTime();
		var today_date = new Date(moment().format("YYYY-MM-DD") + " 00:00:00").getTime();
		if (day_date < today_date) {
			return;
		}

		var event_id = "-1";
		var day_date_click = new Date(view);
		var day_date = day_date_click.getTime();
		var today_date = new Date(moment().format("YYYY-MM-DD") + " 00:00:00").getTime();
		if (day_date < today_date) {
			return;
		}
		//alert(day_date);
		var case_id = "-1";
		var start_date = view;
		var element_id = event_id + "_" + case_id + "_" + start_date + "_" + day_date + "_intake"; 
		composeEvent(element_id);
		
		return;
	},
	change: function(event) {
		// Look up the underlying event in the calendar and update its details from the model
		var fcEvent = this.$el.fullCalendar('clientEvents', event.get('id'))[0];
		fcEvent.title = event.get('title');
		fcEvent.color = event.get('color');
		fcEvent.start = event.get('datetimepicker');
		this.$el.fullCalendar('updateEvent', fcEvent);         
	},
	
	formatDate: function(thedate) {
		var themonth = String(thedate.getMonth() + 1);
		var theyear = String(thedate.getFullYear());
		var theday = String(thedate.getDate());
		var thehour = String(thedate.getHours());
		var theminute = String(thedate.getMinutes());
		thedate = theyear + "-" + themonth + "-" + theday + " " + thehour + ":" + theminute;
		return thedate;
	},

	eventDropOrResize: function(fcEvent) {
		// Lookup the model that has the ID of the event and update its attributes
		//this.collection.get(fcEvent.id).save({start: fcEvent.start, end: fcEvent.end});   
		//MOVE THE EVENT
		if (!confirm("Press OK to confirm this move")) {
			return;
		}
		var url = 'api/event/move';
		if (typeof fcEvent.datetimepicker == "undefined") {
			var thedate = moment(fcEvent.start).format("YYYY-MM-DD HH:mm");
		} else {
			var thedate = moment(fcEvent.datetimepicker).format("YYYY-MM-DD HH:mm");
		}
		
        var formValues = {
            id: fcEvent.id,
			start:thedate
        };

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
               
                 if(data.error) {  // If there is an error, show the error messages
                    $('.alert-error').text(data.error.text).show();
                }
 				clearStoredEvents(current_max_track_id, 0, true);
            }
        });
		//assume it worked, i'm ashamed
		//make sure the collection is updated
		this.collection.get(fcEvent.id).set({start: fcEvent.start, end: fcEvent.end});   
	},
	destroy: function(event) {
		this.$el.fullCalendar('removeEvents', event.id);         
	}
});
var employee_occurences_view = Backbone.View.extend({
	initialize: function(){
		_.bindAll(this); 
		this.collection.bind('reset', this.addAll);
	},
	events: {
		"click #calendar_print": 		"calendarPrintView",
		"change #event_typeFilter":		"filterCalendar",
		"click #calendar_list":			"calendarListView"
	},
	calendarPrintView: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var theid = element.id.split("_")[2];
		
		//which view are we looking at
		var theview = this.$el.fullCalendar( 'getView' );
		var start = moment(theview.start._d).format("YYYY-MM-DD");
		var end = moment(theview.end._d).format("YYYY-MM-DD");
		window.open("report.php#employee_calendar/" + start + "/" + end);
		//" + document.location.href.split("#")[1];
	},
	render: function() {
		var options = {
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			defaultView: 'month',
			aspectRatio: 2,
			theme: false,
			selectable: true,
			selectHelper: true,
			editable: true,
			ignoreTimezone: false,                
			//select: this.select,
			dayClick: this.dayClick,
			eventClick: this.eventClick,
			eventDrop: this.eventDropOrResize,        
			eventResize: this.eventDropOrResize,
			events: {
				textColor: 'black'
			},
			loading: function(isLoading) {
				if(!isLoading && isTouchDevice())
				{
					// Since the draggable events are lazy(bind)loaded, we need to
					// trigger them all so they're all ready for us to drag/drop
					// on the iPad. w00t!
					$('.fc-event-draggable').each(function(){
						var e = jQuery.Event("mouseover", {
							target: this.firstChild,
							_dummyCalledOnStartup: true
						});
						$(this).trigger(e);
					});
				}
			},
			viewRender: function (view, element) {
				current_calendar_view = view.name;
				if (view.name=="month") {
					current_calendar_start = moment(view.title.split(" ")[0] + " 1 " + view.title.split(" ")[1])._d;
					current_calendar_end = view.intervalEnd._d;
				} else {
					current_calendar_start = view.start._d;
					current_calendar_end = view.end._d;
				}
			 }
		};
		/*
		var method = (isTouchDevice()) ? 'eventMouseover' : 'eventClick'; 
		options[method] = function(event, jsEvent, view) { 
			if(jsEvent._dummyCalledOnStartup) 
					{ 
							return; 
					} 
					// Do something here when someone clicks on the event.
					this.eventClick
			}
		*/
		
		this.$el.fullCalendar(options);
		this.addAll();
		
		calendar_element = this.$el;
		global_calendar_element = this.$el;
		
		setTimeout(function(){
			$("#content").prepend('<div style="width:100%; margin-left:auto; margin-right:auto; text-align:center;display:none" class="calendar_title" id="page_title">Employee Kalendar</div>');
			calendarFadeIn();
		}, 100);
		
		
		if (typeof customer_settings.get("background") != "undefined") {
			setTimeout(function() {
				$(".fc-view-container").css("background", customer_settings.get("background"))
			}, 500);
		}
	},
	addAll: function() {
		this.$el.fullCalendar('addEventSource', this.collection.toJSON());
		//turn off new events for intake
		//$(".fc-day-new").hide();
	},
	addOne: function(event) {
		this.$el.fullCalendar('renderEvent', event.toJSON());
	},        
	/*select: function(startDate, endDate) {
		this.occurenceView.collection = this.collection;
		this.occurenceView.model = new Occurence({start: startDate, end: endDate});
		this.occurenceView.render();            
	},*/
	eventClick: function(fcEvent) {
		/*
		this.occurenceView.model = this.collection.get(fcEvent.id);
		this.occurenceView.collection = this.collection;
		this.occurenceView.model.set("case_number",this.model.get("case_number"));
		this.occurenceView.model.set("case_id",this.model.get("case_id"));
		this.occurenceView.render();
		*/
		//id = event_id, also case_id gets passed back to app.js for routing
		//console.log(fcEvent);
		calendar_event = fcEvent;
		var event_id = fcEvent.id;
		var start_date = fcEvent.start;
		var case_id = this.collection.findWhere({id: event_id}).get("case_id");
		if (typeof case_id == "undefined") {
			case_id = -1;
		}
		if (typeof start_date._d != "undefined") {
			var day_date = start_date._d.getTime();
		} else {
			var day_date = start_date.getTime();
		}
		var event_kind = "";
		var element_id = event_id + "_" + case_id + "_" + start_date + "_" + day_date + "_" + event_kind; 
		//alert("angel");
		//window.history.pushState(null, null, "#kases/events/edit/" + case_id + "/" + event_id + "/" + case_number);
		composeEvent(element_id);
		//document.location.href = "#kases/events/edit/" + case_id + "/" + event_id + "/" + day_date;
	},
	dayClick: function(view) {
		if (typeof view._d != "undefined") {
			view.add(1, 'days');
			view.subtract(9, 'hours');
		}
		var event_id = "-1";
		var day_date_click = new Date(view);
		var day_date = day_date_click.getTime();
		var today_date = new Date(moment().format("YYYY-MM-DD") + " 00:00:00").getTime();
		if (day_date < today_date) {
			return;
		}

		var event_id = "-1";
		var day_date_click = new Date(view);
		var day_date = day_date_click.getTime();
		var today_date = new Date(moment().format("YYYY-MM-DD") + " 00:00:00").getTime();
		if (day_date < today_date) {
			return;
		}
		//alert(day_date);
		var case_id = "-1";
		var start_date = view;
		var element_id = event_id + "_" + case_id + "_" + start_date + "_" + day_date + "_employee"; 
		composeEvent(element_id);
		
		return;
	},
	change: function(event) {
		// Look up the underlying event in the calendar and update its details from the model
		var fcEvent = this.$el.fullCalendar('clientEvents', event.get('id'))[0];
		fcEvent.title = event.get('title');
		fcEvent.color = event.get('color');
		fcEvent.start = event.get('datetimepicker');
		this.$el.fullCalendar('updateEvent', fcEvent);         
	},
	
	formatDate: function(thedate) {
		var themonth = String(thedate.getMonth() + 1);
		var theyear = String(thedate.getFullYear());
		var theday = String(thedate.getDate());
		var thehour = String(thedate.getHours());
		var theminute = String(thedate.getMinutes());
		thedate = theyear + "-" + themonth + "-" + theday + " " + thehour + ":" + theminute;
		return thedate;
	},

	eventDropOrResize: function(fcEvent) {
		// Lookup the model that has the ID of the event and update its attributes
		//this.collection.get(fcEvent.id).save({start: fcEvent.start, end: fcEvent.end});   
		//MOVE THE EVENT
		if (!confirm("Press OK to confirm this move")) {
			return;
		}
		var url = 'api/event/move';
		if (typeof fcEvent.datetimepicker == "undefined") {
			var thedate = moment(fcEvent.start).format("YYYY-MM-DD HH:mm");
		} else {
			var thedate = moment(fcEvent.datetimepicker).format("YYYY-MM-DD HH:mm");
		}
		
        var formValues = {
            id: fcEvent.id,
			start:thedate
        };

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
               
                 if(data.error) {  // If there is an error, show the error messages
                    $('.alert-error').text(data.error.text).show();
                }
 				clearStoredEvents(current_max_track_id, 0, true);
            }
        });
		//assume it worked, i'm ashamed
		//make sure the collection is updated
		this.collection.get(fcEvent.id).set({start: fcEvent.start, end: fcEvent.end});   
	},
	destroy: function(event) {
		this.$el.fullCalendar('removeEvents', event.id);         
	}
});
var partner_occurences_view = Backbone.View.extend({
	initialize: function(){
		_.bindAll(this); 
		this.collection.bind('reset', this.addAll);
	},
	events: {
		"click #calendar_print": 		"calendarPrintView",
		"change #event_typeFilter":		"filterCalendar",
		"click #calendar_list":			"calendarListView"
	},
	calendarPrintView: function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		var theid = element.id.split("_")[2];
		
		//which view are we looking at
		var theview = this.$el.fullCalendar( 'getView' );
		var start = moment(theview.start._d).format("YYYY-MM-DD");
		var end = moment(theview.end._d).format("YYYY-MM-DD");
		window.open("report.php#partner_calendar/" + start + "/" + end);
		//" + document.location.href.split("#")[1];
	},
	render: function() {
		var options = {
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			defaultView: 'month',
			aspectRatio: 2,
			theme: false,
			selectable: true,
			selectHelper: true,
			editable: true,
			ignoreTimezone: false,                
			//select: this.select,
			dayClick: this.dayClick,
			eventClick: this.eventClick,
			eventDrop: this.eventDropOrResize,        
			eventResize: this.eventDropOrResize,
			events: {
				textColor: 'black'
			},
			loading: function(isLoading) {
				if(!isLoading && isTouchDevice())
				{
					// Since the draggable events are lazy(bind)loaded, we need to
					// trigger them all so they're all ready for us to drag/drop
					// on the iPad. w00t!
					$('.fc-event-draggable').each(function(){
						var e = jQuery.Event("mouseover", {
							target: this.firstChild,
							_dummyCalledOnStartup: true
						});
						$(this).trigger(e);
					});
				}
			},
			viewRender: function (view, element) {
				current_calendar_view = view.name;
				if (view.name=="month") {
					current_calendar_start = moment(view.title.split(" ")[0] + " 1 " + view.title.split(" ")[1])._d;
					current_calendar_end = view.intervalEnd._d;
				} else {
					current_calendar_start = view.start._d;
					current_calendar_end = view.end._d;
				}
			 }
		};
		/*
		var method = (isTouchDevice()) ? 'eventMouseover' : 'eventClick'; 
		options[method] = function(event, jsEvent, view) { 
			if(jsEvent._dummyCalledOnStartup) 
					{ 
							return; 
					} 
					// Do something here when someone clicks on the event.
					this.eventClick
			}
		*/
		
		this.$el.fullCalendar(options);
		this.addAll();
		
		calendar_element = this.$el;
		setTimeout(function(){
			$("#content").prepend('<div style="width:100%; margin-left:auto; margin-right:auto; text-align:center;display:none" class="calendar_title" id="page_title">Partner Kalendar</div>');
			calendarFadeIn();
		}, 100);
		
		if (typeof customer_settings.get("background") != "undefined") {
			setTimeout(function() {
				$(".fc-view-container").css("background", customer_settings.get("background"))
			}, 500);
		}
	},
	addAll: function() {
		this.$el.fullCalendar('addEventSource', this.collection.toJSON());
		//turn off new events for intake
		//$(".fc-day-new").hide();
	},
	addOne: function(event) {
		this.$el.fullCalendar('renderEvent', event.toJSON());
	},        
	/*select: function(startDate, endDate) {
		this.occurenceView.collection = this.collection;
		this.occurenceView.model = new Occurence({start: startDate, end: endDate});
		this.occurenceView.render();            
	},*/
	eventClick: function(fcEvent) {
		/*
		this.occurenceView.model = this.collection.get(fcEvent.id);
		this.occurenceView.collection = this.collection;
		this.occurenceView.model.set("case_number",this.model.get("case_number"));
		this.occurenceView.model.set("case_id",this.model.get("case_id"));
		this.occurenceView.render();
		*/
		//id = event_id, also case_id gets passed back to app.js for routing
		//console.log(fcEvent);
		calendar_event = fcEvent;
		var event_id = fcEvent.id;
		var start_date = fcEvent.start;
		var case_id = this.collection.findWhere({id: event_id}).get("case_id");
		if (typeof case_id == "undefined") {
			case_id = -1;
		}
		if (typeof start_date._d != "undefined") {
			var day_date = start_date._d.getTime();
		} else {
			var day_date = start_date.getTime();
		}
		var event_kind = "";
		var element_id = event_id + "_" + case_id + "_" + start_date + "_" + day_date + "_" + event_kind; 
		//alert("angel");
		//window.history.pushState(null, null, "#kases/events/edit/" + case_id + "/" + event_id + "/" + case_number);
		composeEvent(element_id);
		//document.location.href = "#kases/events/edit/" + case_id + "/" + event_id + "/" + day_date;
	},
	dayClick: function(view) {
		if (typeof view._d != "undefined") {
			view.add(1, 'days');
			view.subtract(9, 'hours');
		}
		var event_id = "-1";
		var day_date_click = new Date(view);
		var day_date = day_date_click.getTime();
		var today_date = new Date(moment().format("YYYY-MM-DD") + " 00:00:00").getTime();
		if (day_date < today_date) {
			return;
		}

		var event_id = "-1";
		var day_date_click = new Date(view);
		var day_date = day_date_click.getTime();
		var today_date = new Date(moment().format("YYYY-MM-DD") + " 00:00:00").getTime();
		if (day_date < today_date) {
			return;
		}
		//alert(day_date);
		var case_id = "-1";
		var start_date = view;
		var element_id = event_id + "_" + case_id + "_" + start_date + "_" + day_date + "_partner"; 
		composeEvent(element_id);
		
		return;
	},
	change: function(event) {
		// Look up the underlying event in the calendar and update its details from the model
		var fcEvent = this.$el.fullCalendar('clientEvents', event.get('id'))[0];
		fcEvent.title = event.get('title');
		fcEvent.color = event.get('color');
		fcEvent.start = event.get('datetimepicker');
		this.$el.fullCalendar('updateEvent', fcEvent);         
	},
	
	formatDate: function(thedate) {
		var themonth = String(thedate.getMonth() + 1);
		var theyear = String(thedate.getFullYear());
		var theday = String(thedate.getDate());
		var thehour = String(thedate.getHours());
		var theminute = String(thedate.getMinutes());
		thedate = theyear + "-" + themonth + "-" + theday + " " + thehour + ":" + theminute;
		return thedate;
	},

	eventDropOrResize: function(fcEvent) {
		// Lookup the model that has the ID of the event and update its attributes
		//this.collection.get(fcEvent.id).save({start: fcEvent.start, end: fcEvent.end});   
		//MOVE THE EVENT
		if (!confirm("Press OK to confirm this move")) {
			return;
		}
		var url = 'api/event/move';
		if (typeof fcEvent.datetimepicker == "undefined") {
			var thedate = moment(fcEvent.start).format("YYYY-MM-DD HH:mm");
		} else {
			var thedate = moment(fcEvent.datetimepicker).format("YYYY-MM-DD HH:mm");
		}
		
        var formValues = {
            id: fcEvent.id,
			start:thedate
        };

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
               
                 if(data.error) {  // If there is an error, show the error messages
                    $('.alert-error').text(data.error.text).show();
                }
 				clearStoredEvents(current_max_track_id, 0, true);
            }
        });
		//assume it worked, i'm ashamed
		//make sure the collection is updated
		this.collection.get(fcEvent.id).set({start: fcEvent.start, end: fcEvent.end});   
	},
	destroy: function(event) {
		this.$el.fullCalendar('removeEvents', event.id);         
	}
});

var custom_occurences_view = Backbone.View.extend({
	initialize: function(){
		_.bindAll(this); 
		this.collection.bind('reset', this.addAll);
	},
	render: function() {
		var self = this;
		var options = {
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			defaultView: 'month',
			aspectRatio: 2,
			theme: false,
			selectable: true,
			selectHelper: true,
			editable: true,
			ignoreTimezone: false,                
			//select: this.select,
			dayClick: this.dayClick,
			eventClick: this.eventClick,
			eventDrop: this.eventDropOrResize,        
			eventResize: this.eventDropOrResize,
			events: {
				textColor: 'black'
			},
			loading: function(isLoading) {
				if(!isLoading && isTouchDevice())
				{
					// Since the draggable events are lazy(bind)loaded, we need to
					// trigger them all so they're all ready for us to drag/drop
					// on the iPad. w00t!
					$('.fc-event-draggable').each(function(){
						var e = jQuery.Event("mouseover", {
							target: this.firstChild,
							_dummyCalledOnStartup: true
						});
						$(this).trigger(e);
					});
				}
			},
			viewRender: function (view, element) {
				current_calendar_view = view.name;
				if (view.name=="month") {
					current_calendar_start = moment(view.title.split(" ")[0] + " 1 " + view.title.split(" ")[1])._d;
					current_calendar_end = view.intervalEnd._d;
				} else {
					current_calendar_start = view.start._d;
					current_calendar_end = view.end._d;
				}
			 }
		};
		/*
		var method = (isTouchDevice()) ? 'eventMouseover' : 'eventClick'; 
		options[method] = function(event, jsEvent, view) { 
			if(jsEvent._dummyCalledOnStartup) 
					{ 
							return; 
					} 
					// Do something here when someone clicks on the event.
					this.eventClick
			}
		*/
		
		this.$el.fullCalendar(options);
		this.addAll();
		
		calendar_element = this.$el;
		setTimeout(function(){
			$("#content").prepend('<div style="width:100%; margin-left:auto; margin-right:auto; text-align:center; display:none;" class="calendar_title" id="page_title">' + self.model.get("title") + '</div>');
			calendarFadeIn();
		}, 100);
		
		if (typeof customer_settings.get("background") != "undefined") {
			setTimeout(function() {
				$(".fc-view-container").css("background", customer_settings.get("background"))
			}, 500);
		}
	},
	addAll: function() {
		this.$el.fullCalendar('addEventSource', this.collection.toJSON());
	},
	addOne: function(event) {
		this.$el.fullCalendar('renderEvent', event.toJSON());
	},        
	/*select: function(startDate, endDate) {
		this.occurenceView.collection = this.collection;
		this.occurenceView.model = new Occurence({start: startDate, end: endDate});
		this.occurenceView.render();            
	},*/
	eventClick: function(fcEvent) {
		/*
		this.occurenceView.model = this.collection.get(fcEvent.id);
		this.occurenceView.collection = this.collection;
		this.occurenceView.model.set("case_number",this.model.get("case_number"));
		this.occurenceView.model.set("case_id",this.model.get("case_id"));
		this.occurenceView.render();
		*/
		//id = event_id, also case_id gets passed back to app.js for routing
		//console.log(fcEvent);
		calendar_event = fcEvent;
		var event_id = fcEvent.id;
		var start_date = fcEvent.start;
		var case_id = this.collection.findWhere({id: event_id}).get("case_id");
		if (typeof case_id == "undefined") {
			case_id = -1;
		}
		if (typeof start_date._d != "undefined") {
			var day_date = start_date._d.getTime();
		} else {
			var day_date = start_date.getTime();
		}
		var event_kind = "";
		var element_id = event_id + "_" + case_id + "_" + start_date + "_" + day_date + "_" + event_kind; 
		//alert("angel");
		//window.history.pushState(null, null, "#kases/events/edit/" + case_id + "/" + event_id + "/" + case_number);
		composeEvent(element_id);
		//document.location.href = "#kases/events/edit/" + case_id + "/" + event_id + "/" + day_date;
	},
	dayClick: function(view) {
		if (typeof view._d != "undefined") {
			view.add(1, 'days');
			view.subtract(9, 'hours');
		}
		var event_id = "-1";
		var day_date_click = new Date(view);
		var day_date = day_date_click.getTime();
		var today_date = new Date(moment().format("YYYY-MM-DD") + " 00:00:00").getTime();
		if (day_date < today_date) {
			return;
		}
		//alert(day_date);
		//window.history.pushState(null, null, "#kases/events/edit/" + case_id + "/" + event_id + "/" + case_number);
		//document.location.href = "#kases/events/edit/" + case_id + "/" + event_id + "/" + day_date;
		var event_id = "-1";
		var day_date_click = new Date(view);
		var day_date = day_date_click.getTime();
		var today_date = new Date(moment().format("YYYY-MM-DD") + " 00:00:00").getTime();
		if (day_date < today_date) {
			return;
		}
		var case_id = -1;
		//alert(day_date);
		var start_date = view;
		var element_id = event_id + "_" + case_id + "_" + start_date + "_" + day_date + "_"; 
		composeEvent(element_id);
		
	},
	change: function(event) {
		// Look up the underlying event in the calendar and update its details from the model
		var fcEvent = this.$el.fullCalendar('clientEvents', event.get('id'))[0];
		fcEvent.title = event.get('title');
		fcEvent.color = event.get('color');
		fcEvent.start = event.get('datetimepicker');
		this.$el.fullCalendar('updateEvent', fcEvent);         
	},
	
	formatDate: function(thedate) {
		var themonth = String(thedate.getMonth() + 1);
		var theyear = String(thedate.getFullYear());
		var theday = String(thedate.getDate());
		var thehour = String(thedate.getHours());
		var theminute = String(thedate.getMinutes());
		thedate = theyear + "-" + themonth + "-" + theday + " " + thehour + ":" + theminute;
		return thedate;
	},

	eventDropOrResize: function(fcEvent) {
		// Lookup the model that has the ID of the event and update its attributes
		//this.collection.get(fcEvent.id).save({start: fcEvent.start, end: fcEvent.end});   
		//MOVE THE EVENT
		if (!confirm("Press OK to confirm this move")) {
			return;
		}
		var url = 'api/event/move';
		if (typeof fcEvent.datetimepicker == "undefined") {
			var thedate = moment(fcEvent.start).format("YYYY-MM-DD HH:mm");
		} else {
			var thedate = moment(fcEvent.datetimepicker).format("YYYY-MM-DD HH:mm");
		}
		
        var formValues = {
            id: fcEvent.id,
			start:thedate
        };

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
               
                 if(data.error) {  // If there is an error, show the error messages
                    $('.alert-error').text(data.error.text).show();
                }
 				clearStoredEvents(current_max_track_id, 0, true);
            }
        });
		//assume it worked, i'm ashamed
		//make sure the collection is updated
		this.collection.get(fcEvent.id).set({start: fcEvent.start, end: fcEvent.end});   
	},
	destroy: function(event) {
		this.$el.fullCalendar('removeEvents', event.id);         
	}
});
var kase_personal_occurences_view = Backbone.View.extend({
	initialize: function(){
		_.bindAll(this); 
		this.collection.bind('reset', this.addAll);
	},
	render: function() {
		var options = {
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			defaultView: 'month',
			aspectRatio: 2,
			theme: false,
			selectable: true,
			selectHelper: true,
			editable: true,
			ignoreTimezone: false,                
			//select: this.select,
			dayClick: this.dayClick,
			eventClick: this.eventClick,
			eventDrop: this.eventDropOrResize,        
			eventResize: this.eventDropOrResize,
			events: {
				textColor: 'black'
			},
			loading: function(isLoading) {
				if(!isLoading && isTouchDevice())
				{
					// Since the draggable events are lazy(bind)loaded, we need to
					// trigger them all so they're all ready for us to drag/drop
					// on the iPad. w00t!
					$('.fc-event-draggable').each(function(){
						var e = jQuery.Event("mouseover", {
							target: this.firstChild,
							_dummyCalledOnStartup: true
						});
						$(this).trigger(e);
					});
				}
			},
			viewRender: function (view, element) {
				current_calendar_view = view.name;
				if (view.name=="month") {
					current_calendar_start = moment(view.title.split(" ")[0] + " 1 " + view.title.split(" ")[1])._d;
					current_calendar_end = view.intervalEnd._d;
				} else {
					current_calendar_start = view.start._d;
					current_calendar_end = view.end._d;
				}
			 }
		};
		/*
		var method = (isTouchDevice()) ? 'eventMouseover' : 'eventClick'; 
		options[method] = function(event, jsEvent, view) { 
			if(jsEvent._dummyCalledOnStartup) 
					{ 
							return; 
					} 
					// Do something here when someone clicks on the event.
					this.eventClick
			}
		*/
		
		this.$el.fullCalendar(options);
		this.addAll();
		
		calendar_element = this.$el;
		setTimeout(function(){
			$("#content").prepend('<div style="width:100%; margin-left:auto; margin-right:auto; text-align:center; display:none;" class="calendar_title" id="page_title">Personal Kalendar</div>');
			calendarFadeIn();
		}, 100);
		
		
		if (typeof customer_settings.get("background") != "undefined") {
			setTimeout(function() {
				$(".fc-view-container").css("background", customer_settings.get("background"))
			}, 500);
		}
	},
	addAll: function() {
		this.$el.fullCalendar('addEventSource', this.collection.toJSON());
	},
	addOne: function(event) {
		this.$el.fullCalendar('renderEvent', event.toJSON());
	},        
	/*select: function(startDate, endDate) {
		this.occurenceView.collection = this.collection;
		this.occurenceView.model = new Occurence({start: startDate, end: endDate});
		this.occurenceView.render();            
	},*/
	eventClick: function(fcEvent) {
		/*
		this.occurenceView.model = this.collection.get(fcEvent.id);
		this.occurenceView.collection = this.collection;
		this.occurenceView.model.set("case_number",this.model.get("case_number"));
		this.occurenceView.model.set("case_id",this.model.get("case_id"));
		this.occurenceView.render();
		*/
		//id = event_id, also case_id gets passed back to app.js for routing
		//console.log(fcEvent);
		calendar_event = fcEvent;
		var event_id = fcEvent.id;
		var start_date = fcEvent.start;
		var case_id = this.collection.findWhere({id: event_id}).get("case_id");
		if (typeof case_id == "undefined") {
			case_id = -1;
		}
		if (typeof start_date._d != "undefined") {
			var day_date = start_date._d.getTime();
		} else {
			var day_date = start_date.getTime();
		}
		var event_kind = "";
		var element_id = event_id + "_" + case_id + "_" + start_date + "_" + day_date + "_" + event_kind; 
		//alert("angel");
		//window.history.pushState(null, null, "#kases/events/edit/" + case_id + "/" + event_id + "/" + case_number);
		composeEvent(element_id, login_user_id);
		//document.location.href = "#kases/events/edit/" + case_id + "/" + event_id + "/" + day_date;
	},
	dayClick: function(view) {
		if (typeof view._d != "undefined") {
			view.add(1, 'days');
			view.subtract(9, 'hours');
		}
		var event_id = "-1";
		var day_date_click = new Date(view);
		var day_date = day_date_click.getTime();
		var today_date = new Date(moment().format("YYYY-MM-DD") + " 00:00:00").getTime();
		if (day_date < today_date) {
			return;
		}
		//alert(day_date);
		//window.history.pushState(null, null, "#kases/events/edit/" + case_id + "/" + event_id + "/" + case_number);
		//document.location.href = "#kases/events/edit/" + case_id + "/" + event_id + "/" + day_date;
		var event_id = "-1";
		var day_date_click = new Date(view);
		var day_date = day_date_click.getTime();
		var today_date = new Date(moment().format("YYYY-MM-DD") + " 00:00:00").getTime();
		if (day_date < today_date) {
			return;
		}
		var case_id = -1;
		//alert(day_date);
		var start_date = view;
		var element_id = event_id + "_" + case_id + "_" + start_date + "_" + day_date + "_"; 
		composeEvent(element_id, login_user_id);
		
	},
	change: function(event) {
		// Look up the underlying event in the calendar and update its details from the model
		var fcEvent = this.$el.fullCalendar('clientEvents', event.get('id'))[0];
		fcEvent.title = event.get('title');
		fcEvent.color = event.get('color');
		fcEvent.start = event.get('datetimepicker');
		this.$el.fullCalendar('updateEvent', fcEvent);         
	},
	
	formatDate: function(thedate) {
		var themonth = String(thedate.getMonth() + 1);
		var theyear = String(thedate.getFullYear());
		var theday = String(thedate.getDate());
		var thehour = String(thedate.getHours());
		var theminute = String(thedate.getMinutes());
		thedate = theyear + "-" + themonth + "-" + theday + " " + thehour + ":" + theminute;
		return thedate;
	},

	eventDropOrResize: function(fcEvent) {
		// Lookup the model that has the ID of the event and update its attributes
		//this.collection.get(fcEvent.id).save({start: fcEvent.start, end: fcEvent.end});   
		//MOVE THE EVENT
		if (!confirm("Press OK to confirm this move")) {
			return;
		}
		var url = 'api/event/move';
		if (typeof fcEvent.datetimepicker == "undefined") {
			var thedate = moment(fcEvent.start).format("YYYY-MM-DD HH:mm");
		} else {
			var thedate = moment(fcEvent.datetimepicker).format("YYYY-MM-DD HH:mm");
		}
		
        var formValues = {
            id: fcEvent.id,
			start:thedate
        };

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
               
                 if(data.error) {  // If there is an error, show the error messages
                    $('.alert-error').text(data.error.text).show();
                }
 				clearStoredEvents(current_max_track_id, 0, true);
            }
        });
		//assume it worked, i'm ashamed
		//make sure the collection is updated
		this.collection.get(fcEvent.id).set({start: fcEvent.start, end: fcEvent.end});   
	},
	destroy: function(event) {
		this.$el.fullCalendar('removeEvents', event.id);         
	}
});
var user_occurences_view = Backbone.View.extend({
	initialize: function(){
		_.bindAll(this); 
		this.collection.bind('reset', this.addAll);
	},
	events: {
		"click #calendar_list":			"calendarListView"
	},
	render: function() {
		var self = this;
		var options = {
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			defaultView: 'month',
			aspectRatio: 2,
			theme: false,
			selectable: true,
			selectHelper: true,
			editable: true,
			ignoreTimezone: false,                
			//select: this.select,
			dayClick: this.dayClick,
			eventClick: this.eventClick,
			eventDrop: this.eventDropOrResize,        
			eventResize: this.eventDropOrResize,
			events: {
				textColor: 'black'
			},
			loading: function(isLoading) {
				if(!isLoading && isTouchDevice())
				{
					// Since the draggable events are lazy(bind)loaded, we need to
					// trigger them all so they're all ready for us to drag/drop
					// on the iPad. w00t!
					$('.fc-event-draggable').each(function(){
						var e = jQuery.Event("mouseover", {
							target: this.firstChild,
							_dummyCalledOnStartup: true
						});
						$(this).trigger(e);
					});
				}
			},
			viewRender: function (view, element) {
				current_calendar_view = view.name;
				if (view.name=="month") {
					current_calendar_start = moment(view.title.split(" ")[0] + " 1 " + view.title.split(" ")[1])._d;
					current_calendar_end = view.intervalEnd._d;
				} else {
					current_calendar_start = view.start._d;
					current_calendar_end = view.end._d;
				}
			 }
		};
		/*
		var method = (isTouchDevice()) ? 'eventMouseover' : 'eventClick'; 
		options[method] = function(event, jsEvent, view) { 
			if(jsEvent._dummyCalledOnStartup) 
					{ 
							return; 
					} 
					// Do something here when someone clicks on the event.
					this.eventClick
			}
		*/
		
		this.$el.fullCalendar(options);
		this.addAll();
		
		calendar_element = this.$el;
		setTimeout(function(){
			$("#content").prepend('<div style="width:100%; margin-left:auto; margin-right:auto; text-align:center; display:none;" class="calendar_title" id="page_title">' + self.model.get("calendar_name") + ' Personal Kalendar</div><input type="hidden" value="' + self.model.get("user_id") + '" id="calendar_user_id" />');
			calendarFadeIn();
			
			if (self.model.get("permissions").indexOf("write")==-1) {
				$(".fc-day-new").hide();
			}
		}, 100);
		
		if (typeof customer_settings.get("background") != "undefined") {
			setTimeout(function() {
				$(".fc-view-container").css("background", customer_settings.get("background"))
			}, 500);
		}
	},
	calendarListView: function(event) {
		if (blnCalendarListing) {
			blnCalendarListing = false;
			return;
		}
		blnCalendarListing = true;
		clearSearchResults();
		event.preventDefault();
		var href = Backbone.history.getFragment();
		var arrHref = href.split("/");
		if (arrHref.length > 3) {
			return;
		}
		href = href.replace("ikalendar", "listkalendar");
		var theview = this.$el.fullCalendar( 'getView' );
		var start = moment(theview.start._d).format("YYYY-MM-DD");
		var end = moment(theview.end._d).format("YYYY-MM-DD");
		if (theview.name=="agendaDay") {
			end = start;
		}
		if (theview.name=="month") {
			//first and last of the current month
			var arrMonth = $(".fc-center").html().replace("<h2>", "").replace("</h2>", "").split(" ");
			var first_day = new Date(arrMonth[0] + " 1 " + arrMonth[1]);
			var last_day = new Date(first_day.getFullYear(), first_day.getMonth() + 1, 0, 23, 59, 59);
			
			start = moment(first_day).format("YYYY-MM-DD");
			end = moment(last_day).format("YYYY-MM-DD");
		}
		Backbone.history.navigate(href + "/" + start + "/" + end);
		
		
		//are we filtering by worker
		var the_worker = $("#assigneeFilter").val();
		var the_type = $("#event_typeFilter").val();
		
		//however, might be intake
		var arrHash = document.location.hash.split("/");
		if (arrHash[1]=="4") {
			the_type = "Intake";
		}
		listCustomerEvents(start, end, "", the_type, the_worker);
	},
	addAll: function() {
		this.$el.fullCalendar('addEventSource', this.collection.toJSON());
	},
	addOne: function(event) {
		this.$el.fullCalendar('renderEvent', event.toJSON());
	},        
	/*select: function(startDate, endDate) {
		this.occurenceView.collection = this.collection;
		this.occurenceView.model = new Occurence({start: startDate, end: endDate});
		this.occurenceView.render();            
	},*/
	eventClick: function(fcEvent) {
		/*
		this.occurenceView.model = this.collection.get(fcEvent.id);
		this.occurenceView.collection = this.collection;
		this.occurenceView.model.set("case_number",this.model.get("case_number"));
		this.occurenceView.model.set("case_id",this.model.get("case_id"));
		this.occurenceView.render();
		*/
		//id = event_id, also case_id gets passed back to app.js for routing
		//console.log(fcEvent);
		calendar_event = fcEvent;
		var event_id = fcEvent.id;
		var start_date = fcEvent.start;
		var case_id = this.collection.findWhere({id: event_id}).get("case_id");
		if (typeof case_id == "undefined") {
			case_id = -1;
		}
		if (typeof start_date._d != "undefined") {
			var day_date = start_date._d.getTime();
		} else {
			var day_date = start_date.getTime();
		}
		var event_kind = "";
		var element_id = event_id + "_" + case_id + "_" + start_date + "_" + day_date + "_" + event_kind; 
		//alert("angel");
		//window.history.pushState(null, null, "#kases/events/edit/" + case_id + "/" + event_id + "/" + case_number);
		composeEvent(element_id, $("#calendar_user_id").val());
		//document.location.href = "#kases/events/edit/" + case_id + "/" + event_id + "/" + day_date;
	},
	dayClick: function(view) {
		if (typeof view._d != "undefined") {
			view.add(1, 'days');
			view.subtract(9, 'hours');
		}
		if (this.model.get("permissions").indexOf("write")==-1) {
			return;
		}
		var event_id = "-1";
		var day_date_click = new Date(view);
		var day_date = day_date_click.getTime();
		var today_date = new Date(moment().format("YYYY-MM-DD") + " 00:00:00").getTime();
		if (day_date < today_date) {
			return;
		}
		//alert(day_date);
		//window.history.pushState(null, null, "#kases/events/edit/" + case_id + "/" + event_id + "/" + case_number);
		//document.location.href = "#kases/events/edit/" + case_id + "/" + event_id + "/" + day_date;
		var event_id = "-1";
		var day_date_click = new Date(view);
		var day_date = day_date_click.getTime();
		var today_date = new Date(moment().format("YYYY-MM-DD") + " 00:00:00").getTime();
		if (day_date < today_date) {
			return;
		}
		var case_id = -1;
		//alert(day_date);
		var start_date = view;
		var element_id = event_id + "_" + case_id + "_" + start_date + "_" + day_date + "_"; 
		composeEvent(element_id, $("#calendar_user_id").val());
		
	},
	change: function(event) {
		// Look up the underlying event in the calendar and update its details from the model
		var fcEvent = this.$el.fullCalendar('clientEvents', event.get('id'))[0];
		fcEvent.title = event.get('title');
		fcEvent.color = event.get('color');
		fcEvent.start = event.get('datetimepicker');
		this.$el.fullCalendar('updateEvent', fcEvent);         
	},
	
	formatDate: function(thedate) {
		var themonth = String(thedate.getMonth() + 1);
		var theyear = String(thedate.getFullYear());
		var theday = String(thedate.getDate());
		var thehour = String(thedate.getHours());
		var theminute = String(thedate.getMinutes());
		thedate = theyear + "-" + themonth + "-" + theday + " " + thehour + ":" + theminute;
		return thedate;
	},

	eventDropOrResize: function(fcEvent) {
		// Lookup the model that has the ID of the event and update its attributes
		//this.collection.get(fcEvent.id).save({start: fcEvent.start, end: fcEvent.end});   
		//MOVE THE EVENT
		if (!confirm("Press OK to confirm this move")) {
			return;
		}
		var url = 'api/event/move';
		if (typeof fcEvent.datetimepicker == "undefined") {
			var thedate = moment(fcEvent.start).format("YYYY-MM-DD HH:mm");
		} else {
			var thedate = moment(fcEvent.datetimepicker).format("YYYY-MM-DD HH:mm");
		}
		
        var formValues = {
            id: fcEvent.id,
			start:thedate
        };

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
               
                 if(data.error) {  // If there is an error, show the error messages
                    $('.alert-error').text(data.error.text).show();
                }
 				clearStoredEvents(current_max_track_id, 0, true);
            }
        });
		//assume it worked, i'm ashamed
		//make sure the collection is updated
		this.collection.get(fcEvent.id).set({start: fcEvent.start, end: fcEvent.end});   
	},
	destroy: function(event) {
		this.$el.fullCalendar('removeEvents', event.id);         
	}
});
var kaseOccurencesSummaryView = Backbone.View.extend({
	initialize: function(){
		_.bindAll(this); 

		this.collection.bind('reset', this.addAll);
		this.collection.bind('add', this.addOne);
		this.collection.bind('change', this.change);            
		this.collection.bind('destroy', this.destroy);
		
		//this.occurenceView = new kaseOccurenceView({model: this.model});            
		this.occurenceView = new kase_occurence_dialog_view({model: this.model});
	},
	render: function() {
		var options = {
			header: {
				left: 'prev,next',
				center: 'title',
			},
			aspectRatio: 2,
			theme: false,
			selectable: true,
			selectHelper: true,
			editable: true,
			ignoreTimezone: false,                
			select: this.select,
			eventClick: this.eventClick,
			eventDrop: this.eventDropOrResize,        
			eventResize: this.eventDropOrResize,
			events: {
				textColor: 'black'
			},
			loading: function(isLoading) {
				if(!isLoading && isTouchDevice())
				{
					// Since the draggable events are lazy(bind)loaded, we need to
					// trigger them all so they're all ready for us to drag/drop
					// on the iPad. w00t!
					$('.fc-event-draggable').each(function(){
						var e = jQuery.Event("mouseover", {
							target: this.firstChild,
							_dummyCalledOnStartup: true
						});
						$(this).trigger(e);
					});
				}
			},
			viewRender: function (view, element) {
				current_calendar_view = view.name;
				if (view.name=="month") {
					current_calendar_start = moment(view.title.split(" ")[0] + " 1 " + view.title.split(" ")[1])._d;
					current_calendar_end = view.intervalEnd._d;
				} else {
					current_calendar_start = view.start._d;
					current_calendar_end = view.end._d;
				}
			 }
		};
		/*
		var method = (isTouchDevice()) ? 'eventMouseover' : 'eventClick'; 
		options[method] = function(event, jsEvent, view) { 
			if(jsEvent._dummyCalledOnStartup) 
					{ 
							return; 
					} 
					// Do something here when someone clicks on the event.
					this.eventClick
			}
		*/
		this.$el.fullCalendar(options);
		this.addAll();
	},
	addAll: function() {
		this.$el.fullCalendar('addEventSource', this.collection.toJSON());
	},
	addOne: function(event) {
		this.$el.fullCalendar('renderEvent', event.toJSON());
	},        
	select: function(startDate, endDate) {
		this.occurenceView.collection = this.collection;
		this.occurenceView.model = new Occurence({start: startDate, end: endDate});
		this.occurenceView.render();            
	},
	dayClick: function(view) {
		if (typeof view._d != "undefined") {
			view.add(1, 'days');
			view.subtract(9, 'hours');
		}
		var event_id = "-1";
		var day_date_click = new Date(view);
		var day_date = day_date_click.getTime();
		var today_date = new Date(moment().format("YYYY-MM-DD") + " 00:00:00").getTime();
		if (day_date < today_date) {
			return;
		}
		//alert(day_date);
		//window.history.pushState(null, null, "#kases/events/edit/" + case_id + "/" + event_id + "/" + case_number);
		//document.location.href = "#kases/events/edit/" + case_id + "/" + event_id + "/" + day_date;
		var event_id = "-1";
		var day_date_click = new Date(view);
		var day_date = day_date_click.getTime();
		var today_date = new Date(moment().format("YYYY-MM-DD") + " 00:00:00").getTime();
		if (day_date < today_date) {
			return;
		}
		var case_id = -1;
		//alert(day_date);
		var start_date = view;
		var element_id = event_id + "_" + case_id + "_" + start_date + "_" + day_date + "_"; 
		composeEvent(element_id);
		
	},
	eventClick: function(fcEvent) {
		
		this.occurenceView.model = this.collection.get(fcEvent.id);
		this.occurenceView.collection = this.collection;
		this.occurenceView.model.set("case_number",this.model.get("case_number"));
		this.occurenceView.model.set("case_id",this.model.get("case_id"));
		this.occurenceView.render();
	},
	change: function(event) {
		// Look up the underlying event in the calendar and update its details from the model
		var fcEvent = this.$el.fullCalendar('clientEvents', event.get('id'))[0];
		fcEvent.title = event.get('title');
		fcEvent.color = event.get('color');
		fcEvent.start = event.get('datetimepicker');
		this.$el.fullCalendar('updateEvent', fcEvent);         
	},
	
	formatDate: function(thedate) {
		var themonth = String(thedate.getMonth() + 1);
		var theyear = String(thedate.getFullYear());
		var theday = String(thedate.getDate());
		var thehour = String(thedate.getHours());
		var theminute = String(thedate.getMinutes());
		thedate = theyear + "-" + themonth + "-" + theday + " " + thehour + ":" + theminute;
		return thedate;
	},

	eventDropOrResize: function(fcEvent) {
		// Lookup the model that has the ID of the event and update its attributes
		//this.collection.get(fcEvent.id).save({start: fcEvent.start, end: fcEvent.end});   
		//MOVE THE EVENT
		if (!confirm("Press OK to confirm this move")) {
			
			return;
		}
		var url = 'api/event/move';
		if (typeof fcEvent.datetimepicker == "undefined") {
			var thedate = moment(fcEvent.start).format("YYYY-MM-DD HH:mm");
		} else {
			var thedate = moment(fcEvent.datetimepicker).format("YYYY-MM-DD HH:mm");
		}
		
        var formValues = {
            id: fcEvent.id,
			start:thedate
        };

        $.ajax({
            url:url,
            type:'POST',
            dataType:"json",
            data: formValues,
            success:function (data) {
               
                 if(data.error) {  // If there is an error, show the error messages
                    $('.alert-error').text(data.error.text).show();
                }
 				clearStoredEvents(current_max_track_id, 0, true);
            }
        });
		//assume it worked, i'm ashamed
		//make sure the collection is updated
		this.collection.get(fcEvent.id).set({start: fcEvent.start, end: fcEvent.end});   
	},
	destroy: function(event) {
		this.$el.fullCalendar('removeEvents', event.id);         
	}        
});
var kase_occurence_dialog_view = Backbone.View.extend({
	initialize: function() {
		_.bindAll(this);           
	},
	render: function () {
		$(this.el).html(this.template(this.model.toJSON()));
		setTimeout("gridsterIt(3)", 1);
		/*
		var buttons = {'Save': this.save};
		if (!this.model.isNew()) {
			_.extend(buttons, {'Delete': this.destroy});
		}
		*/
		//alert("here");
		/*
		_.extend(buttons, {'Cancel': this.close});            
		this.$el.dialog({
			modal: true,
			title: (this.model.isNew() ? 'New' : 'Edit') + ' Event',
			buttons: buttons,
			open: this.open,
			width:330,
			height:600,
			closeText: "Close"
		});
		
		$(".ui-dialog").addClass("glass_header_no_padding_opaque_darker");
		$(".ui-dialog-titlebar").addClass("glass_header_no_padding_opaque");
		$(".ui-dialog-buttonpane").addClass("glass_header_no_padding_opaque");
		$(".ui-dialog-titlebar-close").html("<span style='margin-top:-3px; font-weight:bold'>&times;</style>");
		
		//setup the datetimepicker
		datepickIt('#start_date');
		*/
		return this;
	},        
	
	setID: function(id) {
		this.model.set({id: id});
		var anevent = this.collection.get(id);
		if (typeof anevent == "undefined") {
			this.collection.add(this.model);
		} else {
			//console.log(this.collection);
			this.collection.remove(anevent);
			this.collection.add(this.model);
		}
		//this.$el.fullCalendar( 'refetchEvents' );  
	},
	
	open: function() {
		this.$('#event_name').val(this.model.get('event_name'));
		this.$('#color').val(this.model.get('color'));
		//var thedate = this.formatUSDate(new Date(this.model.get('start')));
		var thedate = moment(this.model.get('event_dateandtime')).format('MM/DD/YYYY h:mA');
		this.$('#start_date').val(thedate);
	},        
	save: function() {
		var self = this;
		this.model.set({'title': this.$('#event_name').val(), 'start': this.$('#start_date').val(), 'color': this.$('#color').val()});
		var formValues = $('#event_form').serialize();
		
		if (this.model.isNew()) {
			//this.collection.create(this.model, {success: this.close});
			var url = 'api/events/add';
			
		} else {
			var url = 'api/events/update';
		}
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			context: this,
			success:function (data) {
			   
				 if(data.error) {  // If there is an error, show the error messages
                    $('.alert-error').text(data.error.text).show();
                }
 				this.setID(data.id);
				clearStoredEvents(current_max_track_id, 0, true);
			}
		});
		this.close();
	},
	close: function() {
		this.$el.dialog('close');
	},
	destroy: function() {
		this.model.destroy({success: this.close});
	}    
});

var kaseOccurenceView = Backbone.View.extend({
	el: $('#eventDialog'),
	initialize: function() {
		_.bindAll(this);           
	},
	render: function() {
		
		var buttons = {'Save': this.save};
		if (!this.model.isNew()) {
			_.extend(buttons, {'Delete': this.destroy});
		}
		_.extend(buttons, {'Cancel': this.close});            
		this.$el.dialog({
			modal: true,
			title: (this.model.isNew() ? 'New' : 'Edit') + ' Event',
			buttons: buttons,
			open: this.open,
			width:330,
			height:600,
			closeText: "Close"
		});
		
		$(".ui-dialog-titlebar-close").html("<span style='margin-top:-3px; font-weight:bold'>&times;</style>")
		
		//setup the datetimepicker
		datepickIt('#start_date');
		
		//add the case details
		$("#eventDialog #case_number").val(this.model.get("case_number"));
		$("#eventDialog #id").val(this.model.get("case_id"));
		$("#eventDialog #case_id").val(this.model.get("case_id"));
		return this;
	},        
	
	setID: function(id) {
		this.model.set({id: id});
		var anevent = this.collection.get(id);
		if (typeof anevent == "undefined") {
			this.collection.add(this.model);
		} else {
			this.collection.get(id) = this.model;
		}
	},
	
	open: function() {
		this.$('#event_name').val(this.model.get('event_name'));
		this.$('#color').val(this.model.get('color'));
		//var thedate = this.formatUSDate(new Date(this.model.get('start')));
		var thedate = moment(this.model.get('event_dateandtime')).format('MM/DD/YYYY h:mA');
		this.$('#start_date').val(thedate);
	},        
	save: function() {
		var self = this;
		this.model.set({'title': this.$('#event_name').val(), 'start': this.$('#start_date').val(), 'color': this.$('#color').val()});
		var formValues = $('#event_form').serialize();
		
		if (this.model.isNew()) {
			//this.collection.create(this.model, {success: this.close});
			var url = 'api/events/add';
			
		} else {
			var url = 'api/events/update';
		}
		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			context: this,
			success:function (data) {
			   
				 if(data.error) {  // If there is an error, show the error messages
                    $('.alert-error').text(data.error.text).show();
                }
 				this.setID(data.id);
				clearStoredEvents(current_max_track_id, 0, true);
			}
		});
		this.close();
	},
	close: function() {
		this.$el.dialog('close');
	},
	destroy: function() {
		this.model.destroy({success: this.close});
	}        
});
window.calendar_filters_listing = Backbone.View.extend({
	initialize:function () {
    },
	events: {
		"click #select_all_filters":			"selectAll",
		"click .new_default_value":				"showColorPicker",
		"click .calendar_type_cell":			"editCalendarType",
		"click .calendar_type_save":			"updateCalendarType"
	},
    render:function () {		
		var self = this;
		var arrRows = [];
		var filters = this.collection.toJSON();
		_.each( filters, function(filter) {
			var checked = " checked";
			var row_display = "";
			var row_class = "active_filter";
			
			if (filter.deleted!="N") {
				checked = "";
				row_display = "none";
				row_class = "deleted_filter";
			}
			var index = filter.setting_id;
			
			var input = "<input class='hidden' type='text' id='calendar_type_value_" + index + "' value='" + filter.setting + "' />";
			
			var input2 = "<input class='hidden' type='text' id='calendar_type_default_value_" + index + "' value='" + filter.setting_value + "' />&nbsp;<button class='btn btn-xs btn-success calendar_type_save hidden' id='calendar_type_save_" + index + "'>Save</button>";
			
			var therow = "<tr style='display:" + row_display + "' class='" + row_class + "'><td class='calendar_type'><input type='checkbox' class='calendar_filter_checkbox' value='Y' id='calendar_type_" + index + "' name='calendar_type_" + index + "'" + checked + "></td><td class='calendar_type' style='color:black'><span id='calendar_type_span_" + index + "' class='calendar_type_cell' style='cursor:pointer' title='Click to edit this filter'>" + filter.setting + "</span>"  + input + "</td><td class='calendar_type' style='color:black'><span id='calendar_type_value_span_" + index + "'>" + filter.setting_value + "</span>" + input2 + "</td><td class='calendar_type' style='color:black;background:" + filter.default_value + "'>&nbsp;</td></tr>";
			arrRows.push(therow);
		});
		
		var html = "<table id='calendar_filters_table'>" + arrRows.join("") + "</table>";
		try {
			$(this.el).html(this.template({html: html}));
		}
		catch(err) {
			var view = "calendar_filters_listing";
			var extension = "php";
			
			loadTemplate(view, extension, self);
			
			return "";
		}
		
		return this;
	},
	selectAll:function(event) {
		var element = event.currentTarget;
		$(".calendar_filter_checkbox").prop("checked", element.checked);
	},
	showColorPicker: function(event) {
		var element = event.currentTarget;
		var arrID = element.id.split("_");
		var element_id = arrID[arrID.length - 1];
		$(".cp-color-picker").css("zIndex", "9999");
	},
	editCalendarType:function(event) {
		event.preventDefault();
		var element = event.currentTarget;
		
		var arrID = element.id.split("_");
		var settingvalue_id = arrID[arrID.length - 1];
		
		//not open nor closed
		var current = $("#calendar_type_value_" + settingvalue_id).val();
		if (current=="Open" || current=="Closed") {
			return;
		}
		$("#calendar_type_span_" + settingvalue_id).hide();
		$("#calendar_type_value_span_" + settingvalue_id).hide();
		$("#calendar_type_value_" + settingvalue_id).toggleClass("hidden");
		$("#calendar_type_default_value_" + settingvalue_id).toggleClass("hidden");
		$("#calendar_type_save_" + settingvalue_id).toggleClass("hidden");
		//$("#calendar_type_" + settingvalue_id).toggleClass("hidden");
	},
	updateCalendarType:function(event) {
		var self = this;
		event.preventDefault();
		var element = event.currentTarget;
		
		var arrID = element.id.split("_");
		var setting_id = arrID[arrID.length - 1];
		var checkbox = document.getElementById("calendar_type_" + setting_id);
		var deleted = "Y";
		if (checkbox.checked) {
			deleted = "N";
		}
		var settingvalue = $("#calendar_type_value_" + setting_id).val();
		var settingdefault = $("#calendar_type_default_value_" + setting_id).val();
		var mymodel = this.model.toJSON();
		var status_level = mymodel.status_level;
		
		var url = 'api/calendarfilter/update';
		var formValues = "setting_id=" + setting_id + "&setting=" + settingvalue + "&settingdefault=" + settingdefault + "&deleted=" + deleted;

		$.ajax({
			url:url,
			type:'POST',
			dataType:"json",
			data: formValues,
			success:function (data) {
				if(data.error) {  // If there is an error, show the error messages
					saveFailed(data.error.text);
				} else {
					//$("#manage_" + status_level + "status").trigger("click");
					var calfilters = new CalendarFilters();
					calfilters.fetch({
						success: function (data) {
							self.collection = data;
							self.render();
						}
					});
					
					//refresh the options
					refreshCalendarTypeOptions();
				}
			}
		});
		//
	}
});
function refreshCalendarTypeOptions() {
	var url = "api/calendaroptions";
	
	$.ajax({
		url:url,
		type:'GET',
		dataType:"text",
		success:function (data) {
			$("#event_typeFilter").html(data);
		}
	});
}
function newKalendarEvent(day_date) {
	var element_id = "-1_-1_" + day_date + "_" + day_date + "_"; 
	//kase calendar
	if (document.location.hash.indexOf("#kalendar/")==0) {
		var case_id = document.location.hash.split("/")[1];
		 element_id = "-1_" + case_id + "_" + day_date + "_" + day_date + "_"; 
	}
	composeEvent(element_id);
	/*
	if (!blnBlockDays) {
		composeEvent(element_id);
	}
	*/
}
function viewDay (event, day_time) {
	event.preventDefault();
	
	if (blnViewClicked) {
		return;
	}
	blnViewClicked = true;
	
	var element = event.currentTarget;
	var clickedDate = new Date(Number(day_time));
	//console.log(day_time, Number(day_time));
	global_calendar_element.fullCalendar('changeView', 'agendaDay');
	var startdate = moment(clickedDate).format('YYYY-MM-DD');
	var new_date = moment(startdate, "YYYY-MM-DD");
	var new_date = new_date.add(1, 'days').format('YYYY-MM-DD');

	calendar_element.fullCalendar('gotoDate', new_date);
	window.scrollTo(0, 0);

	setTimeout(function() {
		//reset
		blnViewClicked = false;
	}, 100);
}
function blockDay (event, day_time) {
	event.preventDefault();
	
	if (blnViewClicked) {
		return;
	}
	blnViewClicked = true;
	
	var element = event.currentTarget;
	var clickedDate = new Date(Number(day_time));
	//console.log("block", day_time, Number(day_time));
	
	composeBlockedDates(clickedDate);

	setTimeout(function() {
		//reset
		blnViewClicked = false;
	}, 1000);
}